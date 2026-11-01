# Task 7 — BNPL Public Status Checker

## Task Number
Task 7 — BNPL Public Status Checker

## What Was Required
Depends on Task 4, reuses Task 2's proxy rather than building a second one.
Build a public BNPL (installment plan) status checker on
`status.petcenterco.com`, extending Task 2's existing proxy
(`api/adoption-status.php`) to also serve BNPL records rather than standing
up a separate endpoint. Same per-IP rate-limiting and generic-error
treatment as Task 2, for consistency — not because the BNPL `submission_id`
space needs it (it's already 6 random bytes, effectively unguessable).
Same Status Timeline Stepper UI pattern as Task 2's checker, additionally
reading off Task 4's `history[]` audit trail (which the adoption checker
has no equivalent field for).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `adoption-status.php` | `api/adoption-status.php` (relative to `status.petcenterco.com`'s site root) | Changed — existing file, extended only | **Yes — Task 2's file.** Pulled Task 2's latest submitted version as the starting point (not the original), added one new block wrapped in `/** TASK-7 START */` ... `/** TASK-7 END */` immediately after Task 2's own JSON-body-parsing line, and did not touch a single line of Task 2's existing `TASK-2 START/END` code. |
| `bnpl-status.html` | `bnpl/index.html` (relative to `status.petcenterco.com`'s site root) — see live-path note below | New | No. Copies Task 1/Task 2's header/footer/design-token CSS block verbatim, per the multi-worker single-file-page convention, but the page itself is not written or read by any other task. |
| `bnpl-internal-key.php.example` | `api/bnpl-internal-key.php.example` (relative to `status.petcenterco.com`'s site root, same folder as `adoption-status.php`) | New | No. Template only — see Self-Check Confirmation for why this contains no secret. |

**Live-path note, flagged per the delivery guide's "do not guess silently"
instruction:** `status.petcenterco.com` is a brand-new subdomain built by
Task 1. Its folder structure is not covered by either named summary
document (`adoption-website-project-summary.md` documents
`adopt.petcenterco.com`; `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` documents the
admin dashboard) and is not in the delivery guide's own Source→Live-path
mapping table, which is explicitly for `adopt.petcenterco.com`'s site root.
The live paths above are written relative to `status.petcenterco.com`'s own
site root, by direct analogy with Task 2's already-established layout on
that same subdomain (`api/adoption-status.php`, `adoption-status.html` at
its own folder). This task never received a folder-structure doc for
`status.petcenterco.com` itself (that would be Task 1's completion file,
not supplied to this task), so these paths are this worker's consistent
best inference from Task 2's sibling file layout, not a confirmed value
from a doc or table — flagged here rather than guessed past.

**New folder flagged:** `bnpl/` (containing `bnpl-status.html` as
`index.html`) does not exist yet anywhere in either summary document's
folder-structure section — it is a new top-level folder this task
introduces on `status.petcenterco.com`, needed because every page on this
project deploys as `folder/index.html`, never a bare `.html` filename
(Rule 5). The integrator/CEO should create `bnpl/` at
`status.petcenterco.com`'s site root if it does not already exist from
Task 1's own scaffolding.

**Open naming question (unresolved, not a blocker to this delivery):** the
`bnpl/` slug itself was chosen by analogy with Task 2's `/adoption/`
convention. Neither `adoption-website-project-summary.md` nor the files
supplied for this task include `status-categories.json` (a Task 1 file),
so it is unknown whether that file expects a specific link value for a
BNPL/installment category tile. `/bnpl/` is a reasonable, consistent
guess, not a confirmed value — worth a quick confirm before
`status-categories.json` is ever updated to point at this page.

## Code Changes

### `adoption-status.php` → live path `api/adoption-status.php` (shared with Task 2 — extension only)

The entire change is one new block, inserted immediately after Task 2's
own JSON-body-parsing line. Nothing before or after this insertion point
in Task 2's file was altered.

<!-- TASK-7 START -->
**Old code** (Task 2's own line, exact anchor point — copied unchanged
from the original file; appears only once in it):
```php
$lastName = isset($data['last_name']) ? trim($data['last_name']) : '';
```

**New code** (the anchor line, followed immediately by the full TASK-7
insertion):
```php
$lastName = isset($data['last_name']) ? trim($data['last_name']) : '';

/**
 * TASK-7 START: BNPL Public Status Checker branch.
 * ---------------------------------------------------------------
 * Extends this same proxy (per Task 7's spec — reuse Task 2's proxy
 * rather than build a second one) to also serve BNPL (installment
 * plan) status lookups from bnpl-status.html. Routed purely by the
 * presence of `submission_id` in the POST body: the existing
 * adoption-status.html always sends `code` + `last_name` and NEVER
 * `submission_id`, so this branch is a safe, additive fork that
 * cannot change any of Task 2's existing behavior above/below it —
 * nothing in Task 2's original code path is touched.
 *
 * Cross-domain architecture (finalized per BNPL-Path-Sync.md): the
 * BNPL status record lives on adopt.petcenterco.com, not on this
 * server, so this branch calls Task 4's dedicated internal read-only
 * endpoint (bnpl-status-read.php) over HTTPS — the same
 * server-side-fetch-avoids-CORS pattern Task 2 already uses for
 * application.json below, not a new architecture.
 *
 * Same rate-limiting and generic-error conventions as Task 2:
 *  - rateLimitCheck() runs FIRST, before any other validation, so it
 *    can never be bypassed via a different code path (matches Task
 *    2's own invariant — checked and fixed in the third verification
 *    round below).
 *  - Malformed submission_id -> error_type 'invalid' / 400 (matches
 *    Task 2's own convention for plain format errors, e.g. its own
 *    AD-#####-#### regex check).
 *  - Well-formed but no such record -> error_type 'not_found' / 404.
 *    Unlike Task 2, this is NOT blended with the invalid case,
 *    because Task 7 has no second identifier to protect (a BNPL
 *    submission_id is already unguessable on its own) — Task 2's
 *    "never confirm a guessed code" protection has no BNPL
 *    equivalent, so there is nothing here that needs the two cases
 *    to be indistinguishable.
 *  - Any network/config/remote-failure condition -> error_type
 *    'unavailable' / 502 — an honest "we couldn't check" state, kept
 *    separate from 'not_found'.
 */
if (isset($data['submission_id'])) {
    // ── Rate limiting runs first, before anything else in this branch ──
    // (moved ahead of the key-config check in the third verification
    // round — see that section below for why).
    rateLimitCheck();

    // ── Load the internal read key from a same-folder, git-ignored config
    // file. Never a stubbed/invented value — if the real file isn't
    // deployed yet, this branch fails closed with 'unavailable', not a
    // fake key or a fatal error.
    $bnplKeyFile = __DIR__ . '/bnpl-internal-key.php';
    if (!file_exists($bnplKeyFile)) {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error_type' => 'unavailable',
            'message' => 'BNPL status lookup is temporarily unavailable. Please try again later.',
        ]);
        exit;
    }
    require_once $bnplKeyFile;
    if (!defined('BNPL_INTERNAL_READ_KEY') || BNPL_INTERNAL_READ_KEY === '') {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error_type' => 'unavailable',
            'message' => 'BNPL status lookup is temporarily unavailable. Please try again later.',
        ]);
        exit;
    }

    // ── Normalize the submission_id ONCE, at extraction. Everything below
    // (format check, URL, response fallback) uses this single normalized
    // value — fixed in the third verification round after finding the
    // original code only uppercased inline for the regex test, not for
    // the value actually sent to the remote endpoint.
    $submissionId = isset($data['submission_id']) ? strtoupper(trim((string) $data['submission_id'])) : '';

    // ── Pre-flight format check — purely a wasted-round-trip optimization.
    // bnpl-status-read.php re-validates independently and is not trusted
    // to skip its own check.
    if (!preg_match('/^IP-[0-9A-F]{12}$/', $submissionId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error_type' => 'invalid',
            'message' => 'That installment ID format is not recognized.',
        ]);
        exit;
    }

    // ── Cross-domain HTTPS call to Task 4's internal read endpoint.
    // Explicit SSL verification and no redirect-following (a compromised
    // or misconfigured DNS/redirect on the remote side should never be
    // able to silently retarget this request), not just PHP's defaults —
    // added in the third verification round to match Task 2's own
    // cross-domain fetch options exactly.
    $readUrl = 'https://adopt.petcenterco.com/installments/bnpl-status-read.php?submission_id=' . urlencode($submissionId);
    $ch = curl_init($readUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['X-BNPL-Internal-Key: ' . BNPL_INTERNAL_READ_KEY],
    ]);
    $responseBody = curl_exec($ch);
    $curlErrno = curl_errno($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrno !== 0) {
        // Real network failure (timeout/DNS/connection refused) — the
        // honest "we couldn't check" state, same distinction Task 2 draws
        // for its own cross-domain fetch below.
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error_type' => 'unavailable',
            'message' => 'BNPL status lookup is temporarily unavailable. Please try again later.',
        ]);
        exit;
    }

    $remote = json_decode((string) $responseBody, true);

    if ($httpStatus === 400) {
        // Remote's own format rejection — matches our 'invalid' case.
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error_type' => 'invalid',
            'message' => 'That installment ID format is not recognized.',
        ]);
        exit;
    }

    if ($httpStatus === 404) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error_type' => 'not_found',
            'message' => 'No matching installment plan was found.',
        ]);
        exit;
    }

    if ($httpStatus !== 200 || !is_array($remote) || empty($remote['success']) || !isset($remote['record']) || !is_array($remote['record'])) {
        // 401 (bad/missing key), 5xx, or a malformed body all fall here —
        // every case this branch cannot positively confirm as a clean
        // success is treated as 'unavailable', never guessed into a
        // false 'not_found' or a crash.
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error_type' => 'unavailable',
            'message' => 'BNPL status lookup is temporarily unavailable. Please try again later.',
        ]);
        exit;
    }

    // ── Success: allow-list the fields returned to bnpl-status.html,
    // never pass the raw remote record through unfiltered.
    $record = $remote['record'];
    echo json_encode([
        'success' => true,
        'data' => [
            'submission_id' => $record['submission_id'] ?? $submissionId,
            'status' => $record['status'] ?? null,
            'approved_by' => $record['approved_by'] ?? null,
            'approved_at' => $record['approved_at'] ?? null,
            'rejection_notes' => $record['rejection_notes'] ?? null,
            'history' => $record['history'] ?? [],
        ],
    ]);
    exit;
}
// TASK-7 END
```
<!-- TASK-7 END -->

### `bnpl-status.html` → live path `bnpl/index.html`
New file — no existing code to merge, delivered in the zip, not pasted
here (per the delivery guide's rule that new-file code is never pasted
into the completion file). Structure: header/footer/design-token CSS
block copied verbatim from Task 1/Task 2's page; new content is a
single-field lookup form (installment plan ID only — no second
identifier, per Task 7's spec), the same Status Timeline Stepper pattern
as Task 2 (rewritten fresh since this is a new, unshared file), an
approved/rejected detail box, and an audit-trail history list rendering
Task 4's `history[]` array (newest transition first) — this last piece
has no equivalent on the adoption checker page. Entire new-page content is
wrapped in `TASK-7 START`/`TASK-7 END` comments; the copied chrome
CSS/HTML is explicitly labeled "copied verbatim" rather than marked as new
TASK-7 work.

## New files
- `bnpl-status.html` (live path `bnpl/index.html`) — New file, in the zip.
  The public BNPL status checker page.
- `bnpl-internal-key.php.example` (live path `api/bnpl-internal-key.php.example`) —
  New file, in the zip. Template/placeholder config file; contains no real
  secret (see Self-Check Confirmation).

## Bugs Found Outside Task Scope
None found. (The BNPL-library deployment-path question from the first
delivery round was a missing piece of information needed to complete this
task correctly, same category as Task 4's own `.env`-location flag — it
was resolved mid-task via `BNPL-Path-Sync.md`, not a bug in any existing
file's behavior, so it is not listed here.)

## Self-Check Confirmation
- Only the files listed above were touched: `adoption-status.php`
  (Task 2's shared file, extended) and two brand-new files
  (`bnpl-status.html`, `bnpl-internal-key.php.example`). No other project
  file — `adoption-status.html`, `bnpl-status-lib.php`,
  `update-bnpl-status.php`, `submit-installment.php`, or anything on
  `adopt.petcenterco.com` — was modified.
- Every added block in `adoption-status.php` carries a
  `TASK-7 START`/`TASK-7 END` anchor comment; nothing inside Task 2's own
  `TASK-2 START`/`TASK-2 END` block was edited.
- The "Old code" snippet above was copied character-for-character from the
  file as submitted by Task 2 (the version supplied for this task) and
  appears exactly once in it, at the point immediately before the TASK-7
  insertion.
- `bnpl-status.html`'s new content is wrapped in `TASK-7 START`/`TASK-7
  END` comments; the copied chrome CSS/HTML is explicitly labeled "copied
  verbatim" rather than marked as new TASK-7 work.
- **No second identifier required, by design, per Task 7's own spec**
  (BNPL `submission_id` is already unguessable) — confirmed this isn't a
  silent scope gap by re-reading Task 7's "What" section, which states this
  explicitly rather than leaving it implicit.
- **Rate limiting confirmed shared, not duplicated:** the BNPL branch calls
  the exact same `rateLimitCheck()` function Task 2 already defined — same
  per-IP counter, same file-based/`flock()` implementation — rather than
  adding a second rate-limit mechanism.
- **History audit trail confirmed rendered, not just stored:** Task 7's
  spec explicitly calls for reading Task 4's `history[]`;
  `bnpl-status.html` renders it as a reverse-chronological list
  (`renderHistory()`), not just passing it through unused.
- **Schema field names matched exactly** against the project's Shared Data
  Contracts section and Task 4's completion file: `submission_id`,
  `status`, `approved_by`, `approved_at`, `rejection_notes`, `history`
  (array of `{from, to, by, at}`) — no renamed or invented fields.
- Structural lint run on both files: `adoption-status.php` brace/paren/
  bracket balance checked after every edit round (final count: 32/32
  braces, 172/172 parens, 49/49 brackets, 1 opening `<?php` tag, both
  `TASK-7 START`/`END` anchors intact); `bnpl-status.html` parsed cleanly
  via BeautifulSoup with no duplicate IDs, and its inline `<script>` block
  was separately verified with `node --check` (valid).
- **Nothing in the zip is a secret, `config.php`, `pet.json`, `.env`, or
  anything under `applications/` or `uploads/`.** `bnpl-internal-key.php.example`
  is a template with `define('BNPL_INTERNAL_READ_KEY', '');` — an empty
  placeholder value, never a real key. `bnpl-status.html` is a plain,
  static-content page with no embedded credentials. Both files were
  re-read in full immediately before zipping to confirm this.
- **Sandbox limitation, stated plainly rather than skipped** (same
  limitation Tasks 1, 2, and 4 already documented): this environment has no
  PHP binary and no network access to install one, so a real `php -l` and
  the mandatory Playwright visual/interaction check could not be run. In
  their place: the manual brace/paren/bracket balance check above and
  BeautifulSoup/`node --check` structural lint. This task has no
  CEO-supplied reference image, so the image-reference gate doesn't apply
  the way it would on a design-fidelity task — but real-PHP-execution and
  real-browser-interaction remain unverified, flagged rather than claimed
  as done.
- Consulted `PETCENTER_DESIGN_SYSTEM_v1_0.md` Section 16 (Status Timeline
  Stepper, Type 4) and Section 2 (color tokens) before UI decisions —
  reused Task 1/Task 2's existing navy/gold dark-theme tokens rather than
  introducing new ones, since this page shares their chrome.
- Consulted `adoption-website-project-summary.md` Section 7 in full, per
  Task 7's "Read first" — the timeline/status-state logic mirrors the same
  pattern already used by both the existing dashboard and Task 2's checker.
- Consulted Task 2's and Task 4's completion files in full before writing
  any code, and pulled the actual source files each one referenced
  (`adoption-status.php`, `bnpl-status-lib.php`, `update-bnpl-status.php`,
  `adoption-status.html`) rather than working from the completion
  write-ups alone.
- Scope Lock held across all rounds: only files explicitly in Task 7's
  "Where" (new page + extending Task 2's proxy) were touched;
  `bnpl-status-lib.php`, `update-bnpl-status.php`, and
  `submit-installment.php` were read for context but never modified —
  Task 7 only reads the BNPL status record, it never writes to it.

## SECOND VERIFICATION ROUND

**Trigger:** `BNPL-Path-Sync.md` (CEO-finalized) confirmed the original
proxy branch's core assumption was wrong — `bnpl-status-lib.php` does not
live on this server, so the local `require_once`/candidate-path guess in
the first round could never have worked in production. Task 4 built a
dedicated cross-domain read endpoint, `bnpl-status-read.php`, specifically
for this. This round replaced the local-require branch with a real
cross-domain HTTPS call to that endpoint, reading its exact contract from
its actual source file (not guessed). The final code shown under "Code
Changes" above already reflects this round's rewrite in full.

**Removed:** the `$BNPL_LIB_CANDIDATES` local-file-path guess, the local
`require_once`, and the local `bnpl_read_status()`/
`bnpl_validate_submission_id()` calls (those are Task 4's local
functions — this proxy has no local filesystem access to them at all, so
calling them directly was never viable once the domain was confirmed
different).

**Added:**
- A same-folder, non-committed config file, `bnpl-internal-key.php`
  (template shipped as `bnpl-internal-key.php.example`), holding
  `BNPL_INTERNAL_READ_KEY` as a `define()`. **The real value still has to
  be supplied by the CEO/ops** — the `.example` file's value is
  intentionally left blank, never a stubbed/invented secret. If the real
  file isn't deployed yet, the branch returns a clean
  `error_type: "unavailable"` (502) instead of authenticating with a fake
  key or fatally erroring.
- A cURL call to
  `https://adopt.petcenterco.com/installments/bnpl-status-read.php?submission_id=...`,
  sending the key via the `X-BNPL-Internal-Key` header (the endpoint's
  preferred method, per its own source comments), same timeout pattern as
  the adoption branch's own `application.json` fetch elsewhere in this
  file.
- Response-code handling read directly off `bnpl-status-read.php`'s actual
  source (not inferred): 400 → `invalid`; 404 → `not_found`; 401/5xx/
  malformed body → `unavailable`; 200 with `{success:true, record:{...}}` →
  the lookup succeeded, and a named-field allow-list is applied to
  `record` before it's sent to `bnpl-status.html`.
- A pre-flight format check (`/^IP-[0-9A-F]{12}$/`) before spending a
  network round-trip on an obviously malformed ID — purely a wasted-call
  optimization; `bnpl-status-read.php` re-validates independently and is
  not trusted to skip its own check.

### What did NOT change
- `bnpl-status.html` — untouched this round. It already only sends
  `{submission_id}` to this same proxy path; it has no knowledge of (and
  doesn't need to know) how the proxy resolves that server-side.
- The response shape this proxy returns to `bnpl-status.html`
  (`{success, data: {submission_id, status, approved_by, approved_at,
  rejection_notes, history}}`) is identical to the first round — only how
  the proxy obtains `record` changed, not the contract with the page.

### Self-Check Confirmation (this round)
- Read `bnpl-status-read.php`'s actual source in full before writing any
  integration code — the response-code/field-name handling above matches
  its literal `http_response_code()`/`json_encode()` calls line for line,
  not an assumed or typical REST-error convention.
- Confirmed the endpoint's own auth precedence (`X-BNPL-Internal-Key`
  header checked before `?key=` fallback) and used the header, per its
  source comment calling that the preferred method (doesn't land in access
  logs the way a query string does).
- Did not invent, stub, or hardcode any value for `BNPL_INTERNAL_READ_KEY`
  anywhere. The `.example` file's `define()` argument is an empty string.
- Structural lint re-run on the whole file after this edit: 29/29 braces,
  162/162 parens, 48/48 brackets balanced, 1 opening `<?php` tag.
- Scope Lock held: only `adoption-status.php`'s existing `TASK-7` branch
  was replaced; no other file (`bnpl-status.html`, `bnpl-status-read.php`,
  `bnpl-status-lib.php`, `update-bnpl-status.php`) was touched.
- **Still outstanding, needs the CEO, not more guessing:** the real
  `BNPL_INTERNAL_READ_KEY` value has to be generated and placed in both
  `adopt.petcenterco.com`'s `/admin/.env` (Task 4's side) and this
  project's `api/bnpl-internal-key.php` (copied from the `.example`
  template, this round's side) before this lookup can succeed end to end.
  Until then, every BNPL lookup on the live page will correctly show
  "temporarily unavailable" rather than silently failing or fabricating a
  result.
- The `bnpl/` deployed-URL naming question from the first round is
  unrelated to this round's fix and remains open — still needs
  `status-categories.json` or an explicit CEO decision on the slug.

## THIRD VERIFICATION ROUND

**Trigger:** CEO asked for a full, unhurried re-check against both project
summary docs and this task's own spec — not a new file dependency, a
correctness audit of what was already delivered.

**Process:** re-read `adoption-website-project-summary.md` Section 7 and
Task 7's spec in the tracker fresh (not from memory of the first two
rounds), then read every line of the delivered `adoption-status.php`
TASK-7 branch and `bnpl-status.html` against: (a) Task 2's actual source
(not just its completion doc's description of itself), (b) Task 4's actual
source for `history[]`'s real field names, (c) the Design System's Section
16 Type 4 spec. Four real issues found and fixed; two things checked and
confirmed NOT bugs, documented below so they're not re-flagged next round.
The final code shown under "Code Changes" above already reflects all four
fixes.

### Bugs found and fixed this round

1. **Case-normalization gap (real bug).** `$submissionId` was only
   uppercased inline for the regex *test*, but the original (possibly
   lowercase) value was what actually got sent to `bnpl-status-read.php`.
   A lowercase-but-otherwise-valid ID would pass the proxy's own format
   check, then fail the remote's strict-uppercase validation and come back
   as a false "not found." `bnpl-status.html`'s JS already uppercases
   before sending, so this was unreachable through the page itself — but
   the proxy should be correct independent of which caller hits it, the
   same way Task 2's own `$code = strtoupper(trim(...))` is normalized at
   the point of extraction, not just at the point of checking. Fixed:
   `$submissionId` is now uppercased once, immediately after extraction,
   and that single normalized value is used everywhere after (format
   check, URL, fallback in the response).

2. **Error-vocabulary inconsistency with Task 2 (real bug).** The first
   round blended "malformed ID" and "well-formed but no such record" into
   a single `not_found`/404, reasoning (incorrectly) that this mirrored
   Task 2's "never confirm a guess" pattern. On closer re-read of Task 2's
   actual code, that pattern is specifically about keeping "code doesn't
   exist" indistinguishable from "code exists but the second identifier
   doesn't match" — a real information-leak concern with no BNPL
   equivalent (Task 7 has no second identifier at all, by design). Task
   2's actual, separate case for plain malformed input is
   `error_type: 'invalid'` / HTTP 400 (`preg_match('/^AD-\d{5}-\d{4}$/', ...)`
   failing). Fixed: the BNPL branch now matches that real convention —
   malformed ID (pre-flight check, or an unexpected remote 400) →
   `'invalid'`/400; well-formed but no such record (remote 404) →
   `'not_found'`/404, kept separate. `bnpl-status.html`'s front-end needed
   no change — its error handling already buckets anything but
   `'unavailable'` into the same generic "error" display style, showing
   whichever message string the server sent, which is the same pattern
   Task 2's own front-end uses (verified against its actual source).

3. **Rate-limit check ordering (real bug, security-relevant).** The config
   check for `BNPL_INTERNAL_READ_KEY` was running *before*
   `rateLimitCheck()`. That means, with no deploy config yet, every
   request down this branch would always short-circuit to "unavailable"
   before ever touching the rate limiter — not exploitable in a
   meaningful way here specifically, but it breaks the actual invariant
   this project holds elsewhere: Task 2 checks rate limiting as the very
   first thing in its flow, before any other validation, precisely so
   that gate can never be skipped by forcing the request down a different
   path. Fixed: rate limiting now runs first in this branch too, before
   the key-config check.

4. **cURL option parity with Task 2 (real bug, security-relevant).** The
   first round's cURL call only set `CURLOPT_RETURNTRANSFER` and
   `CURLOPT_TIMEOUT`. Task 2's own cross-domain fetch (the adoption
   branch, same file) explicitly sets `CURLOPT_CONNECTTIMEOUT`,
   `CURLOPT_FOLLOWLOCATION => false`, and `CURLOPT_SSL_VERIFYPEER => true` —
   not relying on PHP's cURL defaults for a security-relevant external
   call. Fixed: the BNPL branch's cURL call now sets the same four
   options Task 2's does, for the same reasons (no silent redirect
   retargeting, explicit cert verification, a separate connect-phase
   timeout from the overall request timeout).

### Checked and confirmed NOT bugs (so these aren't re-flagged next round)

- **Timeline "current" state uses Gold, not Navy.** The Design System's
  literal Section 16 Type 4 text says current/active should be Navy.
  Task 2's actual shipped CSS uses Gold for this state instead (checked
  its real source, not just the doc). Since Task 7's spec explicitly says
  "same status-timeline UI as Task 2," and this page shares Task 2's
  navy-background chrome (where a Navy ring would have near-zero contrast
  against a Navy page background — almost certainly why Task 2 made this
  call), `bnpl-status.html` intentionally matches Task 2's actual Gold
  choice rather than the doc's literal text. This is Task 2's design
  decision to own, not something for Task 7 to silently "correct" on its
  own file — flagged here for visibility, not changed.
- **2-line stop labels instead of the doc's "3-line stack."** Same
  situation: the doc describes a label/name/status 3-line structure; Task
  2's actual shipped markup only renders name + status (2 lines, no
  separate "Step 1" line). `bnpl-status.html` mirrors Task 2's actual
  2-line structure for consistency between the two sibling pages, per
  Task 7's explicit instruction to match Task 2's UI.
- **`history[]` field names** (`from`, `to`, `by`, `at`) re-verified
  directly against `update-bnpl-status.php`'s actual write code (not just
  the documented schema) — exact match, no change needed.
- **`approved_at` date format** re-verified against Section 7.2's literal
  "dd/mm/yy, hour:minute:second" spec — Task 2's actual `fmtDate()` doesn't
  follow that literal format either (it renders e.g. "Jan 5, 2026 at 3:45
  PM"). `bnpl-status.html` copies Task 2's actual function verbatim, which
  is the right call per "same UI as Task 2" — Section 7.2 describes a
  *different* page (`adopt.petcenterco.com`'s own dashboard, not Task 2's
  or Task 7's), so it was never this task's literal target to begin with.

### Self-Check Confirmation (this round)
- Re-read `adoption-website-project-summary.md` Section 7 and Task 7's
  tracker spec fresh, not from this round's own earlier memory of them.
- Diffed every claim in the first two rounds' completion sections against
  Task 2's and Task 4's actual source files line by line, not their
  completion docs' descriptions of themselves.
- All four fixes re-verified structurally after editing: 32/32 braces,
  172/172 parens, 49/49 brackets balanced, 1 opening `<?php` tag, both
  `TASK-7 START`/`END` anchors intact.
- Confirmed `bnpl-status.html` needed no code changes this round — its
  existing error-display logic already correctly handles the corrected
  `invalid`/`not_found` split without modification.

## FOURTH ROUND — HAND-IN REFORMAT (no code or behavior changed)

**Trigger:** the hand-in process changed (`WORKER-DELIVERY-GUIDE.md`,
later clarified further). Per explicit instruction, this round changes
only the shape of this completion file and the packaging of new files —
no code, logic, or prior findings from rounds one through three were
touched, redone, or re-litigated.

**What changed in this document:**
- Files Changed table rebuilt with the required four columns (Source file
  name | Live path | Changed or New | Shared with another task?).
- Live paths for all three touched/created files determined and flagged
  per the guide's own "take it from the summary doc, or flag the gap and
  keep going" instruction — `status.petcenterco.com`'s folder structure is
  not in either named summary document or the guide's mapping table (see
  the live-path note under Files Changed above).
- New folder requirement (`bnpl/`) explicitly flagged, since it does not
  appear in either summary document's existing folder-structure section.
- `bnpl-status.html` and `bnpl-internal-key.php.example` moved out of
  direct narrative description and into the "New files" section, listed
  as "New file — in the zip," per the rule that new-file code is never
  pasted into the completion file.
- `adoption-status.php`'s single Code Changes block re-verified against
  the version of the file this task actually started from (Task 2's
  latest submitted version, supplied for this task) to confirm the "Old
  code" anchor line is an exact, single-occurrence match.

**What did NOT change:** the TASK-7 branch logic in `adoption-status.php`,
the contents of `bnpl-status.html`, and the contents of
`bnpl-internal-key.php.example` are byte-for-byte identical to what rounds
two and three above already finalized. The two still-open substantive
items (real `BNPL_INTERNAL_READ_KEY` value needed from the CEO/ops; the
`bnpl/` URL-slug question pending `status-categories.json` or a CEO
decision) remain open exactly as stated above — this round did not
attempt to resolve them, since they are not hand-in-format issues.

### Zip naming and contents
Per the clarified delivery instructions, the new-files zip is named using
this project's own file-name convention, `[Project-Name]-[Task-Number].zip`
(matching `Task-7-NewFiles.zip` as already specified per-task in the base
guide, and consistent with the project name `Pet Center` / `Unified Status
Dashboard` used throughout this task's source documents): **`Task-7-NewFiles.zip`**.
It contains, at their live paths relative to `status.petcenterco.com`'s
site root:
- `bnpl/index.html`
- `api/bnpl-internal-key.php.example`

This completion file, `Task-7-Completion.md`, is included inside the same
zip alongside the new files, per the clarified instruction that all new
files including the task completion file travel together in one zip.

### Self-Check Confirmation (this round)
- Files Changed table now has all four required columns.
- Every "Old code" snippet (one, in the single Code Changes block) was
  copied character for character from the file as supplied for this task
  and confirmed to appear exactly once in it.
- Zip is rooted at `status.petcenterco.com`'s site root, contains only
  the two brand-new files plus this completion file, and matches the
  "New files" list above exactly.
- No secrets, no `config.php`, no `pet.json`, no `applications/`, no
  `.env` anywhere in what was sent — `bnpl-internal-key.php.example`
  holds only an empty placeholder `define()`, confirmed by re-reading it
  immediately before zipping.
- No file outside this task's original scope was edited.
- Verified the zip's contents with `unzip -l` before sending (see command
  output kept alongside this delivery).
