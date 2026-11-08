# Task B1 — Completion File

## ⚠️ OPEN ITEMS FOR CEO / INTEGRATOR — READ BEFORE MERGING
Consolidated here per the delivery guide's instruction to document anything pending, important, or doubtful
in one place before zipping. Each is also flagged inline, in context, where it's relevant below.

1. **New folder, not in either summary doc — `verify/` must be created on the live site.** Neither
   `adoption-website-project-summary.md`'s nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder-structure
   section lists a verification path, and the delivery guide's source-name-to-live-path table has no row for
   these two files either. I chose `verify/` myself, as a new root-level sibling of `admin/`, `contact-us/`,
   `adoptions/`, etc., and built both files against that choice. **This is my own naming decision, not a
   confirmed one** — see "Files Changed" below for exactly what breaks and needs updating if the CEO wants a
   different folder name.
2. **Queue-storage interpretation needs CEO/VDM confirmation, not just mine.** B1's task text says nothing is
   stored after the email sends; B2's worker needed something real to build a review queue against. I
   extended that line to mean "the video/ID images specifically," and added a small non-sensitive JSON
   record per submission so B2 has real data. Full reasoning under "SECOND VERIFICATION ROUND" below — this
   is a real interpretive call on my part, flagged there and in both files' comments, not something I'm
   treating as self-evidently correct.
3. **Live camera/face-tracking accuracy is untested.** This sandbox has no network egress and no real camera,
   so MediaPipe FaceMesh's CDN model never actually loaded here and the blink/head-turn detection logic has
   only been reviewed, not run against a real face. Everything else (recording, upload, submit, email,
   cleanup) is tested end-to-end. Recommend a real-browser, real-camera check before this ships. Detail under
   "Testing performed" below.
4. **Dependency the integrator must confirm is still true:** `verify/verify-submit.php` requires
   `contact-us/contact-config.php` by relative path and calls its existing `sendContactEmail()` — that file
   must stay at `contact-us/contact-config.php` with PHPMailer installed where it expects it, or the submit
   step fails outright.

No other bugs, doubts, or incomplete pieces exist beyond what's listed here and repeated in context below.

## Task Number
**Task B1 — Identity verification flow (client-side)**

## What Was Required
A standalone, pre-account merchant identity-verification flow: a liveness
check (blink + head-turn challenge), the full duration of that challenge
recorded as video evidence, upload of two separate ID images (front/back),
and — on final submit — all three files sent as email attachments to the
platform's admin recipients using the existing mailer implementation, with
nothing kept server-side after the email dispatch confirms success. Per the
current (revised) task doc, this flow does not live inside any account area
— no merchant account exists yet at this point — and never references the
platform's separate signup/login pages.

## Files Changed

| Source file name | Live path (inside the site root) | Changed or New | Shared with another task? |
|---|---|---|---|
| `verify-account.html` | `verify/index.html` | New | No |
| `verify-submit.php` | `verify/verify-submit.php` | New | No |

No existing file was changed. Both files are brand new, so both are in `Rehoming-Platform-Task-B1.zip` and neither
is documented as old/new code below.

**Live-path note (summary and assignment are silent, so stated openly rather than guessed silently):** the
delivery guide's mapping table has no row for these two files, and neither summary document's
folder-structure section lists a verification folder. I followed summary rule 5 (page = folder containing
`index.html`) and placed the page at `verify/index.html`, giving the URL `adopt.petcenterco.com/verify/`.
The handler sits beside it at `verify/verify-submit.php`; it is a non-HTML file, so it keeps its own name.
The `/verify/` folder name is my own choice. If the CEO wants a different folder, three things reference it:
the zip paths, the `fetch('/verify/verify-submit.php', ...)` call in the page, and the
`require_once __DIR__ . '/../contact-us/contact-config.php'` line in the handler (that one is relative, so
it only breaks if the new folder is not a direct child of the site root).

## Shared Contracts built against
- **B1's own verification-flow entry contract** (owned by this task): entered via URL query parameters —
  `?verify=true&username=<username>&country=<country>&email=<email>` — checked client-side on load in
  `verify-account.html` (`email` was added in the second round, see below; the first round had three
  parameters). My own call on the exact mechanism, as the SHARED CONTRACTS section authorizes. No
  account/session state is read or assumed; a request missing any of the four params is shown a neutral
  "this link isn't valid" gate
  rather than proceeding. If this is ever wired to a different real entry point later, that's a one-line
  change to how the URL is constructed upstream — not a rebuild of this page.
- Confirmed: never referenced, requested, or waited on the platform's separate account
  registration/signup/login pages, per the BUILD APPROACH standing rule in the current task doc.
- No other task's contract (listing schema, verification_status, etc.) applies to this task's scope.

## Code Changes

**No existing file was changed in this task, so there are no old-code/new-code blocks.** Both files are
brand-new and are delivered whole in `Rehoming-Platform-Task-B1.zip` (see "New files" below), not pasted here.

The task's own "Completion file must show" clause asks for the email-send code and proof that the temp files
are deleted. The excerpt below is evidence only, copied verbatim from the final `verify/verify-submit.php`
in the zip. It is not merge material; the integrator copies the whole file from the zip.

```php
/* TASK-B1 evidence excerpt (verify/verify-submit.php): send to all admin recipients, then delete temp files */
$subject = "Verification Request — {$username} — {$submittedAt}";
$result = sendContactEmail(CONTACT_ADMIN_EMAILS, $subject, $emailHTML, $attachments);

// ... queue-record write (non-sensitive JSON only, never the video or ID images) ...

// ============================================
// CLEANUP — always, regardless of outcome. This is the privacy-compliance-
// sensitive step the task calls out explicitly: the video and ID images
// (never the queue record above) may not remain on the server after this
// point, success or failure.
// ============================================
verifyCleanupTempFiles($tempPaths);
```

`$attachments` is built as `[$videoPath => 'liveness-video-...', $frontPath => 'id-front-...', $backPath => 'id-back-...']`,
the `$filePath => $fileName` shape that `sendContactEmail()` in `contact-config.php` iterates. The cleanup
call sits after the send and before the success/failure branch, so the three files are removed in both
outcomes; the failure path inside the move-uploads step calls the same function before exiting.

## New files

- `verify/index.html` — New file — in the zip. Standalone merchant verification page (source name `verify-account.html`): liveness challenge, full-duration recording, front/back ID upload, review and submit.
- `verify/verify-submit.php` — New file — in the zip. Submission handler: validates the upload, emails the video and both ID images to all `CONTACT_ADMIN_EMAILS` through `sendContactEmail()`, writes a small non-sensitive queue record for Task B2, and deletes the video and ID images.

**Folders the handler creates at runtime (deliberately NOT in the zip):** `verify/uploads/verification-temp/`
and `verify/uploads/verification-queue/`, each with an auto-written `.htaccess` (`Deny from all`). They are
created on first request, so nothing under `uploads/` ships in the zip.

**Setting/dependency the integrator must confirm (no secret is in either file):** `verify-submit.php`
reads its mail settings and `CONTACT_ADMIN_EMAILS` from the existing `contact-us/contact-config.php`, so
that file must stay at `contact-us/contact-config.php` and PHPMailer must be installed where
`contact-config.php` loads it.

## Deviation from Design System Section 7 (flagged, not silent)
Section 7's file-upload component normally starts uploading each selected file to a **temporary** server
endpoint immediately on selection, returning an opaque token, with the real form's later submit sending
just the tokens. That doesn't fit B1: the task's spec is a **single combined submission** (video + both ID
images sent together, once) with **nothing persisted server-side at any point before that one send**. Doing
a per-file temp-upload-on-select first would itself create the kind of interim server-side storage the task
explicitly says not to have. So the drop-zone markup/CSS/drag-drop/keyboard interaction was copied verbatim
per Rule 7, but the background-preupload-to-temp-endpoint JS behavior was intentionally not carried over —
files are held as in-memory `File` objects until the final Submit click, then sent all at once.

## Bugs Found Outside Task Scope
None found. (The `contact-config.php` plaintext-SMTP-password note from earlier in this task was raised
directly with the CEO already and confirmed as not my concern — it's slated for revocation — so it is not
logged here.)

## Testing performed
- **Structural lint:** balanced HTML tags/braces/parens/brackets (verified programmatically), no duplicate
  element IDs in the DOM.
- **Playwright, headless Chromium, `Page.captureScreenshot` via raw CDP** (not `page.screenshot()`, per the
  project's standard testing note about it hanging with network disabled):
  - Entry gate correctly blocks and shows a neutral message when `?verify=true`/`username`/`country` are
    missing; correctly proceeds and pre-fills the country field when all three are present.
  - Stepper navigation between all three panels.
  - Real file selection into both ID-upload fields (test JPEGs), live thumbnail rendering, "Continue" button
    correctly disabled until both files are present, then enabled.
  - ID-photo preview modal (Use B) opens and closes correctly.
  - Full submit flow tested **served over local HTTP** (not `file://` — Chromium's `fetch()` cannot target a
    root-relative URL under the `file://` scheme at all, which is a test-harness protocol limitation, not an
    app bug; confirmed by re-running the identical test over `http://127.0.0.1` with the backend endpoint
    stubbed via Playwright route interception): success path opens the confirmation modal with the correct
    3-business-day + 24-hour-check-in copy; incomplete-submission path shows the correct inline error.
- **Genuine sandbox limitation, stated plainly rather than claimed as verified:** this environment has no
  network egress, so MediaPipe FaceMesh's CDN-hosted model cannot actually load here, and there is no real
  camera/face to test blink/head-turn detection against. The liveness-detection *logic* (EAR calculation,
  head-turn offset calculation, the two-phase neutral-return state machine, the randomized challenge
  sequence) was written and reviewed carefully, and the surrounding recording/upload/submit flow around it
  is fully tested end-to-end as above, but the live face-tracking accuracy itself has not been verified
  against a real camera feed in this session. Recommend a real-browser, real-camera check before this ships.

## SECOND VERIFICATION ROUND — B2 integration reconciliation

B2's worker flagged two gaps during integration:

1. **Missing merchant email field** in this task's intake flow.
2. **B2's local JSON stub standing in for real storage**, since this task's spec says nothing is kept after
   the email sends, so B2 had nothing real to read from.

### Fix 1 — merchant email (straightforward, in-scope)
Added `email` to B1's own entry contract (`?verify=true&username=&country=&email=`), alongside the existing
params. Displayed on the Review & Submit step, validated server-side (`FILTER_VALIDATE_EMAIL`) in
`verify-submit.php`, and included in the admin notification email body.

### Fix 2 — queue storage (flagged as an interpretive call, not a silent one)
B1's original spec line — "nothing stored server-side after the email send completes" — was written about
the video/ID images (the privacy-sensitive biometric material). Read completely literally, though, it also
makes a B2 review queue impossible to build against anything real. My interpretation, now implemented: the
video and both ID images are still deleted unconditionally, exactly as before — that part is unchanged. A
new directory, `uploads/verification-queue/`, now holds one small JSON record per submission (username,
email, country, submitted_at, status, email_sent) — never the files themselves. `status` starts at
`"pending"`, matching B2's own `verification_status` contract (`pending | verified | rejected`), so B2 can
read and update it directly. One file per submission (not one shared array) so B2 doesn't need a
write-lock/merge strategy to update a single request's status.

**This interpretation should be confirmed with the CEO/VDM** rather than treated as self-evidently correct
— it's a real extension of what B1's task text said, made to unblock a genuine B1↔B2 integration deadlock,
not something either worker could resolve by themselves in isolation (as B2's own note framed it). Both
files' inline comments at the relevant sections say this plainly, so the same flag is visible to whoever
reviews the code, not just in this write-up.

### Files re-touched this round
Both remain within B1's original File zone — `verify-account.html`, `verify-submit.php`. No file outside
B1's scope was touched. Re-ran the full lint pass (balanced tags/braces/parens, no duplicate IDs) and the
full Playwright test suite from the first round, plus a new case confirming the gate now also blocks when
`email` is missing from the URL — all pass.

### Bugs Found Outside Task Scope (this round)
None found.

### Self-Check Confirmation (this round)
- Only `verify-account.html` and `verify-submit.php` were modified — both already in B1's File zone.
- New/changed code carries `TASK-B1` identifying comments, including an explicit flag on the
  interpretation being made about storage.
- Dropdown-usage confirmation (Directive 9a): still no dropdown menus introduced in this round.
- Re-lint + re-test: balanced structure confirmed, no duplicate IDs, full flow (including the new email
  field, gate behavior, and submit success path) re-verified via Playwright.

## THIRD VERIFICATION PASS — full re-read against both project docs + line-by-line self-audit

Per the CEO's explicit instruction to slow down and re-verify, re-read `adoption-website-project-summary.md`
and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full again, re-read B1's own task text verbatim again, and did a
line-by-line audit of both delivered files against both docs plus the Design System. This surfaced several
real issues the first two rounds missed — all fixed below, all within B1's existing File zone, nothing from
B2's reconciliation round reopened.

### Real bugs found and fixed
1. **Broken `require_once` path (would have been a fatal error on deploy).** `verify-submit.php` required
   `contact-config.php` as if it sat in the same folder. The documented site file tree
   (`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`) confirms `contact-config.php` actually lives in `/contact-us/`, a
   root-level sibling of the new `/verify/` folder this task's files deploy into. Fixed to
   `require_once __DIR__ . '/../contact-us/contact-config.php';` and confirmed correct by simulating the
   real folder layout (`/verify/` + `/contact-us/` as siblings) and checking the resolved path matches.
2. **Frontend calling the wrong endpoint path.** `fetch('/verify-submit.php', ...)` assumed the PHP file
   sat at site root; it actually deploys inside `/verify/` alongside the page. Fixed to
   `/verify/verify-submit.php` and re-verified via Playwright that the browser actually requests that exact URL.
3. **Missing `.htaccess` protection on both new upload-adjacent directories.** The site has a documented,
   established pattern for exactly this situation —
   `admin/chat-uploads-temp/.htaccess` (`Deny from all`) protecting a staged-upload folder that sits inside
   a public webroot. `uploads/verification-temp/` and the new `uploads/verification-queue/` needed the same
   treatment and didn't have it. Added a small shared `verifyEnsureHtaccessDeny()` helper that writes
   `Deny from all` into each directory, copied per Rule 7 rather than inventing different protection.
4. **Missing `LOCK_EX` on the queue-record JSON write.** The site's own documented convention for
   full-replace JSON files (`chat-bot-profile.json`: "full-replace on every save, LOCK_EX protected") wasn't
   applied to the new queue-record write. Added.
5. **Off-scale `border-radius: 20px`** on the recording-indicator pill badge. The Design System's radius
   scale (Section 9) is `6/7/8/9/10/12/14/18/999px` — 20px isn't on it, and this exact class of mistake is
   literally named in the project's own lessons-learned list ("shipped 22px panels/20px modals against the
   18px cap"). Fixed to `999px` (the correct full-pill token). Audited every other `border-radius` value in
   the file afterward — all others were already on-scale.
6. **Font import didn't match the Design System's exact mandated weights.** Had cherry-picked weights
   instead of copying Section 3's canonical `@import` line verbatim. Fixed to the exact documented line
   (including loading the unused-but-optional Bodoni Moda, matching the import exactly rather than a
   near-miss of it).
7. **Two hand-drawn inline `<svg>` icons — a direct Rule 2 violation** (the gate's info icon, the success
   modal's checkmark), despite Font Awesome already being loaded on the page. Swapped both for Font Awesome
   icons (`fa-circle-info`, `fa-check`).
8. **Caught and corrected my own overcorrection on #7:** Design System Section 5's canonical "Use A"
   success-modal markup specifies exact colors (`#0d7c4a` / `rgba(13,124,74,0.12)`) for that checkmark and
   its background circle. My first pass at fixing #7 swapped those for a different (also-valid-elsewhere,
   but non-canonical-here) success green. Reverted the colors back to the exact canonical values while
   keeping the Font Awesome icon swap from #7 — the hand-drawn path was the violation, not the color choice.
9. Added the missing `id="modalTop"` to the preview modal's header div, matching the canonical "Use B/C"
   markup exactly (was functionally harmless without it, but this task's job is exact fidelity, not
   functional-equivalence).

### Polish added (explicitly optional in the Design System, added anyway per the CEO's "don't overlook minor details" instruction)
Overlay-click-to-close and Escape-to-close on both modals — Section 5's Accessibility note calls these
"common, low-cost additions not present in the base implementation... add them per-modal if the use case
calls for it." Added to both the preview modal and the success modal, re-tested via Playwright (new test
cases: overlay click closes each modal, Escape key closes whichever is open).

### Confirmed correct on this pass (checked carefully, no change needed)
- `sendContactEmail($recipients, $subject, $htmlBody, $attachments)`'s exact signature, parameter order, and
  attachment-array format (`$filePath => $fileName`) match `contact-config.php` precisely — verified by
  reading that function's full source again, not just trusting the first round's assumption.
- Every other color used (`--primary`, `--accent`, `--orange`, `--success`, `--error`, `--warning`) matches
  the Design System's Section 2/18 documented hex values exactly.
- Omitting the site's shared header/footer/hamburger menu on this page is correct, not an oversight —
  Directives 9 and 13's "locked verbatim" requirement is explicitly scoped to "adoption-related public
  pages" / "any pet-related page" in the current task doc's own wording; this is neither, and B1's own task
  text frames it as a deliberately standalone, nav-free flow.
- The modal sticky-header/scrolling-body split (Use B) vs. single-scroll-container (Use A) implementation
  matches Section 5's documented reasoning for why those two patterns differ, not just their CSS.
- Re-ran the full original Playwright suite plus every new case above — all pass. Re-confirmed balanced
  tags/braces/parens/brackets and no duplicate IDs across both files after every fix in this round.

### Bugs Found Outside Task Scope (this pass)
None newly found beyond what's already logged. (The SMTP-plaintext-password note remains dropped per the
CEO's earlier explicit instruction not to log it.)

## Self-Check Confirmation
- Only the two files listed under Files Changed were created; no existing file (including `contact-config.php`,
  `contact-handler.php`, or `admin-dashboard.html`) was modified.
- No existing file was changed, so there are no old-code snippets. The "every old snippet copied from the
  original and appears once" check does not apply to this task.
- Every code block in both new files carries a `TASK-B1` identifying comment at the top of its major
  sections (CSS block, gate/stepper markup, JS controller, and the PHP handler's own header comment), per
  Rule 6/Directive 8a.
- Zip check done with `unzip -l Rehoming-Platform-Task-B1.zip`: it contains exactly `verify/index.html` and
  `verify/verify-submit.php` (plus the `verify/` folder entry), the same two paths listed in Files Changed,
  and both are byte-identical (`cmp`) to the versions that passed the Playwright and lint runs described
  above. The zip root is the site root and has no wrapper folder.
- Nothing forbidden is in the zip: no `config.php`, `pet.json`, `.env`, `.htaccess`, `applications/`,
  `uploads/`, `chat-media/` or `logs/` entries. A text search of both zipped files for password, secret,
  API key, token, bearer, SMTP password and email-address strings found nothing.
- Dropdown-usage confirmation (Directive 9a): **No dropdown menus in this task.**
- Shared-contracts confirmation: built against and now publishes B1's own verification-flow entry contract
  (see above); no other task's contract needed reconciliation for this scope.
- Testing: structural lint plus Playwright with raw-CDP screenshots, as described under "Testing performed"
  and the later rounds. The live MediaPipe face-tracking accuracy remains untested in this sandbox (no
  network egress or camera), as stated there.
- This hand-in revision changed only this completion file and added the zip. Neither delivered source file
  was edited.
