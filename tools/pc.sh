#!/usr/bin/env bash
# =====================================================================
# pc — Pet Center CEO helper for Termux (or any Linux/Mac shell with git)
# One small tool so departments do not spend Claude tokens on git chores.
#
#   pc setup            one-time: light clone (about 25 MB of files, seconds)
#   pc status           who is doing what: branches, open TODO items, is live current?
#   pc merge N          merge the approved branch task/N into main (asks before pushing)
#   pc deploy           publish main's adopt/ folder to the live site (asks before pushing)
#   pc import ID FILES  put a worker's files (zip or folder) on branch task/ID when that worker cannot reach GitHub
#   pc live [path]      HTTP status of the live site, e.g.  pc live /pets/
#
# Needs: git, bash, curl.   Termux:  pkg install git curl
# =====================================================================
set -u
REPO="Victorsesan/petcenter-monorepo"
DIR="${PC_DIR:-$HOME/petcenter}"
SITE="${PC_SITE:-https://adopt.petcenterco.com}"
DEPLOY_BRANCH="${PC_DEPLOY_BRANCH:-adopt-deploy}"
APP_DIR="${PC_APP_DIR:-adopt}"

say() { printf '%s\n' "$*"; }
die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }
ask() { printf '%s [y/N] ' "$1"; read -r a; [ "$a" = "y" ] || [ "$a" = "Y" ]; }
g()   { git -C "$DIR" "$@"; }

cmd_setup() {
  [ -d "$DIR/.git" ] && die "$DIR already exists. Use: pc status"
  TOKEN="${PC_TOKEN:-}"
  if [ -z "$TOKEN" ]; then printf 'Paste the GitHub token (it will not be shown): '; read -rs TOKEN; echo; fi
  # Light clone: no big files are downloaded until asked for (the repo has ~600 MB of videos and photos).
  git clone --filter=blob:limit=1m --no-checkout "https://Victorsesan:${TOKEN}@github.com/${REPO}.git" "$DIR" || die "clone failed"
  g config user.name  "Pet Center CEO"
  g config user.email "ceo@petcenterco.com"
  # Check out code only, skip videos, photos and fonts (saves data and time).
  g sparse-checkout init --no-cone
  printf '/*\n!*.mp4\n!*.jpg\n!*.jpeg\n!*.png\n!*.webp\n!*.ttf\n!*.afm\n!*.ufm\n' | g sparse-checkout set --no-cone --stdin
  g checkout -q main || die "checkout failed"
  say "Done. Folder: $DIR      Next: pc status"
}

need() { [ -d "$DIR/.git" ] || die "Not set up yet. Run: pc setup"; g fetch -q --prune origin || die "fetch failed (check internet / token)"; }

# The tree that the live deploy branch SHOULD have: main's adopt/ folder at the root, plus main's .gitignore.
# Built directly from git objects, so no files are downloaded or checked out.
want_tree() {
  { g ls-tree "origin/main:${APP_DIR}"; g ls-tree origin/main | awk -F'\t' '$2==".gitignore"'; } | g mktree
}

cmd_status() {
  need
  say "=== MAIN ==="; g log -1 --format='  %h  %ad  %an  %s' --date=format:'%d %b %H:%M' origin/main
  say; say "=== WORK BRANCHES (who is doing what) ==="
  found=0
  for b in $(g for-each-ref --format='%(refname:short)' 'refs/remotes/origin/task' 'refs/remotes/origin/bugfix'); do
    found=1
    ahead=$(g rev-list --count "origin/main..$b"); behind=$(g rev-list --count "$b..origin/main")
    files=$(g diff --name-only "origin/main...$b" | wc -l | tr -d ' ')
    merged=""; [ "$ahead" = "0" ] && merged="  (already in main)"
    rep=$(g ls-tree -r --name-only "$b" -- reports 2>/dev/null | grep -c "$(basename "$b" | tr -d ' ')\.md" )
    g log -1 --format="  ${b#origin/}  |  %ad  |  %an  |  %s" --date=format:'%d %b %H:%M' "$b"
    say "      ahead $ahead / behind main $behind, $files files changed, report: $([ "$rep" -gt 0 ] && echo yes || echo NO)$merged"
  done
  [ $found = 0 ] && say "  (no task branches)"
  say; say "=== TODO.md — entries not DONE ==="
  g show origin/main:TODO.md | grep -E '^\| [0-9]+ \|' | grep -vE '\| (DONE|WON.T DO) \|' | sed 's/^/  /' || true
  say; say "=== LIVE SITE vs MAIN ==="
  if g rev-parse -q --verify "origin/${DEPLOY_BRANCH}" >/dev/null; then
    want=$(want_tree); have=$(g rev-parse "origin/${DEPLOY_BRANCH}^{tree}")
    if [ "$want" = "$have" ]; then say "  Live branch is CURRENT with main (nothing to deploy)."
    else n=$(g diff-tree -r --name-only "$have" "$want" | wc -l | tr -d ' '); say "  Live branch is BEHIND main: $n file(s) differ. Run: pc deploy"; fi
  else say "  No ${DEPLOY_BRANCH} branch found."; fi
}

cmd_merge() {
  n="${1:-}"; [ -n "$n" ] || die "Usage: pc merge N   (merges origin/task/N)"
  need
  g rev-parse -q --verify "origin/task/$n" >/dev/null || die "No branch task/$n on GitHub"
  [ -z "$(g status --porcelain)" ] || die "Your local copy has uncommitted changes. Stop and ask for help."
  g switch -q main && g merge -q --ff-only origin/main || die "local main could not be updated"
  say "Changes in task/$n compared with main:"; g diff --stat "origin/main...origin/task/$n" | tail -15
  ask "Merge task/$n into main and push?" || { say "Cancelled."; exit 0; }
  g merge --no-ff -m "Merge task $n" "origin/task/$n" || die "Merge has conflicts. Run: git -C $DIR merge --abort   and send it back to the worker."
  [ "${PC_DRY:-0}" = 1 ] && { say "(dry run: not pushed)"; exit 0; }
  g push origin main && say "Merged and pushed. Next: pc deploy"
}

cmd_deploy() {
  need
  want=$(want_tree) || die "could not build the deploy tree"
  have=$(g rev-parse "origin/${DEPLOY_BRANCH}^{tree}")
  [ "$want" = "$have" ] && { say "Live branch already matches main. Nothing to do."; exit 0; }
  say "Files that will change on the live site:"; g diff-tree -r --name-status "$have" "$want" | head -40
  ask "Publish to ${DEPLOY_BRANCH} (goes live in about 45 seconds)?" || { say "Cancelled."; exit 0; }
  commit=$(g commit-tree "$want" -p "origin/${DEPLOY_BRANCH}" -m "Deploy: main $(g rev-parse --short origin/main)") || die "commit failed"
  [ "${PC_DRY:-0}" = 1 ] && { say "(dry run: would push $commit)"; exit 0; }
  g push origin "$commit:refs/heads/${DEPLOY_BRANCH}" && say "Pushed. Check in ~45 s with: pc live /"
}

# For a worker whose chat cannot reach GitHub at all: the worker gives the CEO the finished files
# (a .zip or a folder laid out exactly like the repo, plus reports/task-[id].md) and the CEO puts
# them on that worker's branch with one command.
cmd_import() {
  id="${1:-}"; src="${2:-}"
  [ -n "$id" ] && [ -n "$src" ] || die "Usage: pc import [branch-id] [zip-or-folder]   e.g. pc import status-4 ~/storage/downloads/worker.zip"
  need
  [ -z "$(g status --porcelain)" ] || die "Your local copy has uncommitted changes. Stop and ask for help."
  tmp=$(mktemp -d)
  if [ -d "$src" ]; then cp -a "$src"/. "$tmp"/
  else command -v unzip >/dev/null || die "Run first: pkg install unzip"; unzip -q "$src" -d "$tmp" || die "could not unzip $src"; fi
  # If everything sits inside one wrapper folder (for example worker/), step into it.
  while [ "$(ls -A "$tmp" | wc -l | tr -d ' ')" = "1" ] && [ -d "$tmp/$(ls -A "$tmp")" ] && \
        ! echo "$(ls -A "$tmp")" | grep -qxE 'adopt|docs|petpay|reports'; do tmp="$tmp/$(ls -A "$tmp")"; done
  list=$(cd "$tmp" && find . -type f | sed 's#^\./##' | grep -v '^__MACOSX/' | sort)
  [ -n "$list" ] || die "No files found in $src"
  bad=$(echo "$list" | grep -vE '^(adopt|docs|petpay|reports)/' ; echo "$list" | grep -E '(^|/)(config\.php|pet\.json|\.env)$|/applications/|\.\./' )
  [ -z "$bad" ] || { say "Refused. These paths are not allowed (only adopt/, docs/, petpay/, reports/; never config.php, pet.json, applications/, .env):"; echo "$bad" | sort -u | head -20; exit 1; }
  br="task/$id"
  if g rev-parse -q --verify "origin/$br" >/dev/null; then g switch -q -C "$br" "origin/$br" || die "could not switch to $br"
  else g switch -q -C "$br" origin/main || die "could not create $br"; fi
  echo "$list" | while IFS= read -r f; do mkdir -p "$DIR/$(dirname "$f")"; cp "$tmp/$f" "$DIR/$f"; done
  echo "$list" | tr '\n' '\0' | xargs -0 git -C "$DIR" add --sparse --
  say "Branch $br will get these changes:"; g status --short | head -40
  ask "Commit and push to $br?" || { g switch -q main; say "Cancelled (nothing pushed)."; exit 0; }
  g commit -q -m "Task $id: files delivered through the CEO (worker had no GitHub access)" || die "nothing to commit"
  if [ "${PC_DRY:-0}" = 1 ]; then say "(dry run: committed locally on $br, not pushed)"; g switch -q main; exit 0; fi
  g push -u origin "$br" && say "Pushed $br. Tell the VDM the branch is ready for review."
  g switch -q main
}

cmd_live() {
  p="${1:-/}"; code=$(curl -s -o /dev/null -w '%{http_code}' "${SITE}${p}?v=$(date +%s)")
  say "${SITE}${p}  ->  HTTP ${code}   (200 = live, 404 = not there)"
}

case "${1:-help}" in
  setup)  cmd_setup ;;
  status) cmd_status ;;
  merge)  shift; cmd_merge "$@" ;;
  deploy) cmd_deploy ;;
  import) shift; cmd_import "$@" ;;
  live)   shift; cmd_live "$@" ;;
  *) sed -n 2,16p "$0" ;;
esac
