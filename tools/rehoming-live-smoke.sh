#!/usr/bin/env bash
# Rehoming live smoke test. Never prints the token or any cookie contents.
# Public checks run by default. Add RH_COOKIE_JAR and RH_RUN_WRITES=1 for the
# authenticated visitor flow; use a dedicated test account only.
set -euo pipefail

BASE_URL="${BASE_URL:-https://adopt.petcenterco.com}"
BASE_URL="${BASE_URL%/}"
COOKIE_JAR="${RH_COOKIE_JAR:-}"
COOKIE_HEADER="${RH_COOKIE_HEADER:-}"
RUN_WRITES="${RH_RUN_WRITES:-0}"
MERCHANT_ID="${RH_MERCHANT_ID:-SMOKE-MERCHANT}"
ADMIN_COOKIE_JAR="${RH_ADMIN_COOKIE_JAR:-}"
REVIEW_ID="${RH_REVIEW_ID:-}"
RUN_REVIEW_WRITE="${RH_RUN_REVIEW_WRITE:-0}"
RUN_LISTING_WRITE="${RH_RUN_LISTING_WRITE:-0}"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

pass=0
fail=0
skip=0

say() { printf '%s\n' "$*"; }
fail() { say "FAIL $*"; fail=$((fail+1)); }
pass() { say "PASS $*"; pass=$((pass+1)); }
skip() { say "SKIP $*"; skip=$((skip+1)); }

request() {
  local method="$1" path="$2" body="${3:-}" auth="${4:-}" jar="${5:-}"
  local out="$TMP_DIR/body"
  local args=(-sS --max-time 30 -X "$method" -H 'Accept: application/json' -o "$out" -w '%{http_code}')
  [ -n "$body" ] && args+=(-H 'Content-Type: application/json' --data "$body")
  [ -n "$auth" ] && args+=(-H "Authorization: Bearer $auth")
  if [ "$jar" = '__header__' ]; then
    args+=(-H "Cookie: $COOKIE_HEADER")
  elif [ -n "$jar" ]; then
    args+=(-b "$jar" -c "$jar")
  fi
  HTTP_CODE="$(curl "${args[@]}" "$BASE_URL$path")"
  BODY="$(cat "$out")"
}

json_field() {
  python3 - "$1" "$2" <<'PY'
import json,sys
try:
    value=json.loads(sys.argv[1])
except Exception:
    raise SystemExit(1)
for part in sys.argv[2].split('.'):
    if not isinstance(value,dict) or part not in value:
        raise SystemExit(1)
    value=value[part]
if isinstance(value,(dict,list)):
    print(json.dumps(value,separators=(',',':')))
else:
    print(value)
PY
}

expect_code() {
  local label="$1" expected="$2" actual="$3" body="$4"
  if [ "$actual" = "$expected" ]; then pass "$label ($actual)"; else fail "$label: expected $expected, got $actual — ${body:0:180}"; fi
}

say "Testing $BASE_URL"
say "No secret, bearer token, or cookie value will be printed."

# Route and unauthenticated guards.
request GET /rehoming/list-a-pet/
expect_code 'listing page reachable' 200 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-chat-start.php
expect_code 'chat start method guard' 405 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-chat-send.php
expect_code 'chat send method guard' 405 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-publish-listing.php
expect_code 'listing publish method guard' 405 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-chat-poll.php
expect_code 'chat poll auth guard' 401 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-chat-threads.php
expect_code 'chat threads auth guard' 401 "$HTTP_CODE" "$BODY"
request GET "/rehoming/merchant-reviews.php?merchant_id=$MERCHANT_ID"
expect_code 'review read auth guard' 401 "$HTTP_CODE" "$BODY"
request GET /rehoming/merchant-review-moderate.php
expect_code 'review moderation auth guard' 401 "$HTTP_CODE" "$BODY"

# A dummy bearer token distinguishes a configured secret (401 invalid token)
# from a missing secret (503 configuration error).
DUMMY_TOKEN="$(printf '0%.0s' {1..64})"
request GET /rehoming/merchant-chat-threads.php '' "$DUMMY_TOKEN"
expect_code 'token secret is loaded (dummy token rejected)' 401 "$HTTP_CODE" "$BODY"

if [ -z "$COOKIE_JAR" ] && [ -z "$COOKIE_HEADER" ]; then
  skip 'authenticated visitor flow (set RH_COOKIE_JAR or RH_COOKIE_HEADER to a dedicated logged-in test-account session)'
else
  if [ "$RUN_WRITES" != 1 ]; then
    skip 'authenticated flow is write-enabled; set RH_RUN_WRITES=1 explicitly'
  else
    SESSION_AUTH_SOURCE="$COOKIE_JAR"
    [ -n "$COOKIE_HEADER" ] && SESSION_AUTH_SOURCE='__header__'
    unique="SMOKE-$(date -u +%Y%m%d%H%M%S)-$$"
    start_body=$(python3 - "$MERCHANT_ID" "$unique" <<'PY'
import json,sys
print(json.dumps({'merchant_id':sys.argv[1],'listing_id':sys.argv[2],'pet_name':'Smoke Test Pet','species':'Dog'}))
PY
)
    request POST /rehoming/merchant-chat-start.php "$start_body" '' "$SESSION_AUTH_SOURCE"
    expect_code 'visitor starts thread and receives token' 200 "$HTTP_CODE" "$BODY"
    TOKEN="$(json_field "$BODY" token 2>/dev/null || true)"
    THREAD_ID="$(json_field "$BODY" thread.thread_id 2>/dev/null || true)"
    if [ -z "$TOKEN" ] || [ -z "$THREAD_ID" ]; then
      fail 'start response did not contain token and thread.thread_id'
    else
      send_body=$(python3 - "$THREAD_ID" <<'PY'
import json,sys
print(json.dumps({'thread_id':sys.argv[1],'text':'Automated live smoke-test message.'}))
PY
)
      request POST /rehoming/merchant-chat-send.php "$send_body" "$TOKEN"
      expect_code 'authenticated message send' 200 "$HTTP_CODE" "$BODY"
      request GET "/rehoming/merchant-chat-poll.php?thread_id=$THREAD_ID" '' "$TOKEN"
      expect_code 'authenticated message poll' 200 "$HTTP_CODE" "$BODY"
      request GET /rehoming/merchant-chat-threads.php '' "$TOKEN"
      expect_code 'authenticated thread listing' 200 "$HTTP_CODE" "$BODY"
      request GET "/rehoming/merchant-reviews.php?merchant_id=$MERCHANT_ID" '' "$TOKEN"
      expect_code 'authenticated review read' 200 "$HTTP_CODE" "$BODY"
      request GET "/rehoming/merchant-reviews.php?merchant_id=UNELIGIBLE-$unique" '' "$TOKEN"
      expect_code 'review endpoint remains authenticated for an unreviewed merchant' 200 "$HTTP_CODE" "$BODY"

      if [ "$RUN_REVIEW_WRITE" = 1 ]; then
        review_body=$(python3 - "$MERCHANT_ID" "$unique" <<'PY'
import json,sys
print(json.dumps({'merchant_id':sys.argv[1],'listing_id':sys.argv[2],'thumb':'up','review_text':'Automated smoke review text.'}))
PY
)
        request POST /rehoming/merchant-reviews.php "$review_body" "$TOKEN"
        expect_code 'authenticated review submission' 200 "$HTTP_CODE" "$BODY"
      else
        skip 'review submission (set RH_RUN_REVIEW_WRITE=1; use a dedicated test account because this may persist a review)'
      fi
    fi
  fi
fi

if [ -n "$ADMIN_COOKIE_JAR" ] && [ -n "$REVIEW_ID" ] && [ "$RUN_WRITES" = 1 ]; then
  moderation_body=$(python3 - "$REVIEW_ID" <<'PY'
import json,sys
print(json.dumps({'review_id':sys.argv[1],'visibility':'hidden'}))
PY
)
  request POST /rehoming/merchant-review-moderate.php "$moderation_body" '' "$ADMIN_COOKIE_JAR"
  expect_code 'admin review hide' 200 "$HTTP_CODE" "$BODY"
else
  skip 'admin review moderation (set RH_ADMIN_COOKIE_JAR, RH_REVIEW_ID, and RH_RUN_WRITES=1)'
fi

if [ "$RUN_LISTING_WRITE" = 1 ]; then
  if [ -z "$COOKIE_JAR" ] || [ "$RUN_WRITES" != 1 ]; then
    fail 'listing write requested but authenticated visitor cookie/write mode is unavailable'
  else
    skip 'listing publish requires a separate merchant cookie; not run with visitor cookie'
  fi
else
  skip 'listing publish write (set a separate merchant-session test harness before enabling)'
fi

say "Result: $pass passed, $fail failed, $skip skipped."
[ "$fail" -eq 0 ]
