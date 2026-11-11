# WEB-02 — Admin Dashboard Baseline QA and Completion-Evidence Audit

**Account:** WEB-02
**Date:** 2026-11-11
**Assigned branch:** `qa/admin-dashboard`
**Starting base:** `origin/main` at `42228b6`
**Starting assigned-branch head:** `aca56ce`
**Current synchronized head before this follow-up:** `c5e775c` (merged `origin/main` at `a50c061`; no history rewrite)
**Latest fetched refs:** `origin/main` `0786a3f`; `origin/adopt-deploy` `3f7f03f`.
**Status:** Follow-up implementation and verification are complete locally. Publish only `qa/admin-dashboard`; no Hostinger deployment, upload, or deployment-trigger action was performed.

## Executive summary

A second, evidence-led pass was performed after the initial branch publication. It found additional implementation gaps in historical completion claims, so the work was not treated as finished merely because the earlier report and tests passed. The follow-up repaired category-symlink handling in pet publication and category indexes, protected chat resume from explicit-ID/email mismatch, preserved and updated chat-language preferences, corrected customer-audio transcript routing, restored the accepted Task 5b remembered-device list/forget UI and authenticated endpoints, improved password-reset timing behavior, and added a content-hash cache key to 51 tracked customer-widget script references. Regression tests and local Chromium smoke tests were rerun after the changes.

One historical claim remains only partially achievable with the current schema: per-device removal deletes the remembered-device verifier, but active session tokens are principal-scoped and have no device association, so revoking only that device's already-issued sessions is not possible without a reviewed schema/API migration. This limitation is explicit below; the global revoke-all control remains available.

## Reading gate and safety boundary

Read the current account launcher and complete WEB accounts operational brief, the adoption website and admin dashboard project summaries, the design-system reference, WEB/admin coordination and synchronization materials, the issue register, the applicable Admin Project 1 and Admin Projects 2/3/4 assignments and completion evidence, the account report index, `TODO.md`, and `docs/second-round/HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`. The redesign brief was not used: the current launcher assigns WEB-02 to the admin-dashboard workstream and reserves that redesign brief for DESIGN-01/02.

No production login, database connection, deployment, upload, or Hostinger action was performed. No production `.env`, `config.php`, application/pet record, upload, chat-media file, or log was read or executed. PHP endpoint tests used disposable temporary directories, runtime-generated synthetic tokens, fake database/auth dependencies, and fabricated records; test teardown removes the fixtures. Browser checks used loopback-only local files, no credentials, and blocked all non-local network requests. No secrets or protected runtime data were added to the repository. No conflict markers remain in the worktree.

## Task and completion-evidence matrix

| Workstream / historical evidence | Current result | Evidence and qualification |
|---|---|---|
| WEB-02 admin dashboard baseline | Implemented and rechecked | Safer pet rendering, upload validation, admin session hardening, missing pet-delete route, symlink-safe deletion/path handling, form-label/accessibility updates, and isolated regression suite. See source rows below. |
| Admin Project 1 Tasks 1–4 | Core routes and AuthGate/lock integration are present; static checks pass | Current `chat-auth-gate.php`, `chat-lock.php`, and shared `includes/chat-auth.php` are integrated. No production DB or live lock behavior was exercised. |
| Admin Project 1 Task 5a | Password flow is present for chat principals; three historical caveats remain | New chat can set a password at the pre-chat gate; existing chat protection is session-token gated and token invalidation occurs on set/change. Direct password creation for a user identified only by email/phone is not implemented; the implementation uses random browser-held remember-device tokens rather than the previously required existing 2FA fingerprinting implementation; the HIBP lookup fails open when the service is unavailable. |
| Admin Project 1 Task 5b (accepted optional enhancement) | Device list and per-device forget actions are now present and tested | New actions live in the existing authenticated `chat-auth-gate.php`; the list returns display fields only and delete is constrained by both device ID and chat principal. Active-token revocation for only one device is blocked by the current principal-only token schema; see residual risks. |
| Admin Project 1 Task 6 | Reset request/login/change flow is present and updated | Temporary password expires after 30 minutes (within the assignment's 30–60 minute range), is one-use, and successful password change uses the existing invalidation path. Generic request response is padded to a 1.5-second minimum; on PHP-FPM, response completion occurs before email sending. The hosting SAPI and real mail path were not tested. |
| Admin Project 2 Task 1b (customer → admin transcript and two-way read-aloud) | Customer-authored audio now selects the customer transcription branch; read-aloud controls cover visible text and transcripts | `index.html` now selects `side` from `msg.from_type`; `chat-widget.js` provides the customer view for admin-audio transcripts and native on-device speech. No real customer audio sample was available. |
| Admin Project 4 Task 3 (language persistence) | Explicit-ID and email-based chat resumes preserve stored language when omitted and persist a valid explicit selection | `chat-start.php` uses the existing supported-language allow-list and updates the thread only when a valid preference was submitted. |
| Historical dashboard theme variants | Not reintroduced | Current dashboard source explicitly marks the Console/Calm Panel variants and switcher as superseded/deleted; the remaining approved theme is the Layered Suite. |

## Implemented changes and route map

| Area | Implementation | Evidence |
|---|---|---|
| Dashboard rendering and accessibility | Pet-controlled values are rendered with safe DOM text/listeners, image URLs are constrained to the expected same-origin pet path, form labels are associated, status regions are announced, and upload controls support keyboard interaction/focus. | `adopt/admin/index.html` (pet renderer; login, generated pet/bot fields and upload controls). |
| Admin session/authentication | Type-safe constant-time token check, session-ID rotation after fresh authentication, and safer session-cookie flags while retaining existing request/response contracts. | `adopt/admin/admin-auth.php`. |
| Pet APIs and deletion | Existing dashboard routes preserved; category and pet IDs are allow-listed; canonical root/category/pet paths are checked; symlink aliases/targets are rejected; recursive deletion unlinks rather than traverses links; publishing preflights categories, validates staged upload tokens, refuses unsafe pet-folder creation, and rebuilds indexes without reading symlinked/malformed pet entries. | `get-pets.php`, `publish-pet.php`, `update-pet.php`, `delete-pet.php`, `stats.php`. The dashboard continues to call `delete-pet.php` with its existing JSON pattern. |
| Application deletion | Reject invalid IDs rather than normalizing them into another record; do not recurse through linked directories. | `adopt/admin/delete-application.php`. |
| Chat resume/auth | Explicit chat IDs are treated as lookup hints, not proof of ownership; a mismatch falls through to the submitted email's own recovery lookup. Missing language preserves the stored preference; explicit supported language updates it. | `adopt/admin/chat-start.php`. |
| Remembered devices | Authenticated `listDevices` and `forgetDevice` actions expose ID/label/timestamps only; deletion is bound to `(device ID, principal ID)`. Existing revoke-all remains available. UI renders text safely and uses native confirmation. | `adopt/admin/chat-auth-gate.php`, `adopt/admin/chat-widget.js`. |
| Transcription/read-aloud | Admin transcript requests choose `side=customer` for customer-authored media and `side=admin` for admin-authored media. Customer view uses the existing authenticated transcription route for admin audio. Native browser speech handles visible chat text/transcripts; no translation endpoint was added. | `adopt/admin/index.html`, `adopt/admin/chat-widget.js`, existing `chat-transcribe.php`. |
| Widget cache-busting | The 51 direct public-page script references present on this task branch now use `chat-widget.js?v=3e75a04d6956`, derived from the current widget Git blob, to bypass the stale unversioned URL cache. | 51 tracked HTML pages under `adopt/`; three newer references in current `origin/main` are absent from this branch and listed in the live reconciliation section. |
| Supporting fixes | Corrected the chat email-resume undefined-variable reference; normalized adoption-code handling; protected chat setup with the admin guard; corrected the invalid Apache directive. | `chat-start.php`, `update-adoption.php`, `chat-session-token-setup.php`, `.htaccess`. |
| Tests | Expanded temporary-fixture regression tests for authenticated pet deletion, successful publish/index rebuild, category traversal/symlink rejection, pet/application symlink safety, chat-ID/email ownership, language persistence, device-list auth/data minimization, device removal, and transcript direction. | `tests/Web02AdminBaselineTest.php`, existing `tests/Manus09EndpointTest.php`, `phpunit.xml.dist`. |

### Routes and data flows preserved

- Admin UI: `/adopt/admin/index.html` continues to use the existing same-directory PHP routes; no parallel admin/auth tree was added.
- Pet management: `get-pets.php`, `publish-pet.php`, `update-pet.php`, `delete-pet.php`, and `stats.php` retain their established form/JSON request-response patterns. The missing `delete-pet.php` route now exists.
- Customer chat: `chat-widget.js` continues using `chat-start.php`, `chat-auth-gate.php`, `chat-password-reset.php`, `chat-transcribe.php`, and existing session-token plumbing. Device management is added as two actions to the existing auth-gate route, not a new public route.
- Chat transcript: admin side submits the sender-derived `side` value; customer side requests an admin-message transcript through the existing session-token route.
- No API keys, secrets, or new external translation service were added. The existing HIBP password-check endpoint is not invoked by these tests.

## Verification performed

- PHP syntax checks passed for all changed PHP handlers and tests, including `chat-password-reset.php`, the new pet-delete handler, shared auth, and the chat routes.
- `node --check adopt/admin/chat-widget.js` passed; the one inline JavaScript block in `adopt/admin/index.html` was extracted and passed `node --check`.
- `phpunit --configuration phpunit.xml.dist --colors=never --do-not-cache-result`: **28 tests, 122 assertions, all passed**. Tests use synthetic fixtures only.
- `git diff --check` passed.
- Headless Chromium at **390×844** with non-local networking blocked verified customer widget initialization, opening “Chat Live,” the preferred-language selector and notice, and no JavaScript exceptions.
- Headless Chromium at **1280×900** with non-local networking blocked verified the admin login surface and password/submit controls, without submitting credentials and without JavaScript exceptions.
- A **390×844** localhost static preview of `/pets/cats/index.html` loaded the versioned widget URL with HTTP 200, initialized one launcher, and produced no page errors; all non-local requests were blocked. The widget version key was `3e75a04d6956`.
- Earlier isolated browser probe also exercised dynamically generated pet fields/drop zones and keyboard semantics. Authenticated production workflows, RTL end-to-end flows, live DB, actual speech output, actual email delivery, and real multilingual audio were not exercised.
- Hostinger PHP/Apache version, extensions, SAPI, and access rules remain unverified. `.htaccess` was inspected but no Apache parser or hosting access-control test was available.

## Live public-source and branch reconciliation — 2026-11-11

Only public read-only GET/HEAD checks were made. No login, form submission, PHP POST, upload, Hostinger panel access, or deployment occurred. The public adoption homepage and admin login shell returned successfully. The live `/admin/index.html` bytes match both `origin/main:adopt/admin/index.html` and `origin/adopt-deploy:admin/index.html` exactly by SHA-256.

The seven public category pages (`cats`, `dogs`, `birds`, `reptiles`, `small-mammals`, `livestocks`, and `aquariums`) each returned HTTP 200 and match the versions in `origin/adopt-deploy`; they do not match the newer category-page versions in `origin/main`. This is direct evidence that these main-branch page updates were not what the live routes served at the time of the check.

The repository-to-deployment path comparison maps `origin/main`'s `adopt/` subtree to the repository-root tree of `origin/adopt-deploy`, consistent with the documented site-root mapping. All 2,560 paths in the main `adopt/` subtree exist by path in the deployment branch root, so the comparison finds zero main-only path additions and therefore no general missing-file list from that mapping. One deployment-only path and 13 common-path content differences were found. Excluding one protected runtime/configuration difference whose path and content were not inspected, the 12 differing source paths are `admin/.htaccess`, `admin/chat-migrate.php`, `admin/chat-session-token-setup.php`, `admin/chat-setup.php`, `admin/verification-mail.php`, and the seven category `index.html` files above. `origin/main` and `origin/adopt-deploy` are also history-diverged by 210 and 25 commits respectively. The latest deployment-branch commit `3f7f03f` explicitly reverts `996097a` (“Deploy: publish main security and chat configuration fixes”); a wholesale merge must be reviewed against that reversal, not assumed safe.

One exact missing live route was confirmed: `adopt/admin/delete-pet.php` is absent from both `origin/main` and `origin/adopt-deploy`, exists on the assigned WEB-02 branch, and `https://adopt.petcenterco.com/admin/delete-pet.php` returned HTTP 404. Other report-identified routes checked here exist in main/deploy and responded with method/auth status codes rather than 404. This confirms the route gap, but not a complete Hostinger file inventory; only the hosting operator can verify every file in the document root.

The unversioned `https://adopt.petcenterco.com/admin/chat-widget.js` returned HTTP 200 with a stale 138,892-byte response (SHA-256 `903172f1…dfb0eb`); adding `Cache-Control: no-cache` did not change that response. A unique query-string request returned 180,778 bytes with SHA-256 `15bc5f7e…5b9722`, exactly matching both main and adopt-deploy. This is evidence of URL-keyed intermediary/browser caching, not a missing asset. Accordingly, all 51 direct widget script references present on this task branch were updated to `?v=3e75a04d6956`; a local browser preview confirmed the versioned URL loads and initializes. Three references added to current `origin/main` after this branch's synchronization are absent from this branch—`adopt/merchant/post-listing/index.html`, `adopt/rehoming/index.html`, and `adopt/rehoming/list-a-pet/index.html`—and still need the same versioning when the integration owner brings the latest main pages into the candidate. No live page changed in this task; a production cache purge may still be needed for cached HTML or edge configuration.

The `status.petcenterco.com` hostname did not resolve from this environment (curl DNS error 6); this is only an observation from this resolver and does not prove that a status application or its files are absent.

The separate release candidate `origin/release/second-round-2026-11-08` is at `d80b011`, 39 commits behind and 6 ahead of current main. Its `RELEASE-CANDIDATE-2026-11-09.md` reports local QA and explicitly says it was not a production release. It is not current-main parity or production approval.

## Additional findings and unresolved risks

1. **Medium — admin application list over-returns applicant details.** `get-applications.php` returns phone, address, employment/residence/landlord and veterinarian details in the collection response, while the list renderer appears to use only summary fields and loads a selected application separately. Recommend data minimization after the API contract owner confirms no other consumer depends on the current response. Not changed in this branch.
2. **Medium — broad CORS and cleartext GeoIP remain.** `admin-auth.php` still emits `Access-Control-Allow-Origin: *` and calls `http://ip-api.com` with the client IP. Confirm cross-origin need and whether the third-party lookup is approved; restrict/remove only with integration-owner approval.
3. **High — hosting-level private-file protection is not verified.** The tracked `adopt/admin/.htaccess` has explanatory comments, not deny rules. Hosting must confirm that `.env`, runtime config, activity logs and other private files cannot be fetched over HTTP. No protected-file URL was probed.
4. **Medium — Task 5a scope deviations remain.** Password protection is attached to chat IDs after a chat is started; there is no independent email/phone principal path for users who have not created a chat. The historical completion says the existing 2FA fingerprinting code was unavailable; the integrated implementation uses a random remember-device token instead, so the explicit reuse requirement is not met. `chatAuthPasswordBreached()` uses HIBP k-anonymity but fails open on service/network failure; this was not exercised against a real password or service. Review/approve these deviations before release.
5. **Medium — selective device-session revocation needs a contract/schema change.** The new Forget action removes that device's remembered verifier, which prevents future trusted-device bypass. Existing session tokens are keyed only by `principal_id`, not by device; the system cannot invalidate only that device's already-issued session without adding device association to token issuance/storage and an approved migration. The action therefore does not claim to terminate an already-open session. Existing Revoke All remains the immediate global option.
6. **Medium — reset flow depends on hosting SAPI for its strongest timing behavior.** Request responses have a 1.5-second minimum and PHP-FPM uses `fastcgi_finish_request()` before mail delivery. The fallback SAPI sends mail synchronously after padding, so a slow mail backend could extend response time. Confirm PHP-FPM availability and real SMTP latency in staging; no reset email or production user was used here.
7. **Medium — one-time setup lifecycle.** `chat-session-token-setup.php` now requires admin authorization, but its own source still instructs operators to remove/move it after setup. Database setup was not executed; coordinator must verify cleanup during an approved release.
8. **Accessibility/RTL/release coverage remains limited.** Local login and customer widget were smoke-tested; authenticated workflows, screen-reader testing with an assistive technology, full RTL navigation and device-specific visual QA require a controlled staging environment and approved test data.
9. **Access observation — GitHub deployment metadata returned HTTP 403.** The authorized metadata request was denied; the coordinator clarified it is not a blocker. No deployment settings were inspected or changed. The assigned branch was pushed as instructed; no Hostinger deployment/upload/trigger was performed.

## Synchronization, commits, and publication

Before the follow-up, `git fetch origin --prune` showed the assigned branch at `aca56ce` and `origin/main` at `a50c061`. The only main-only change at that synchronization was a four-line update to `WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`; it was merged normally into `qa/admin-dashboard` as `c5e775c`. A later fetch found `origin/main` at `0786a3f` and `origin/adopt-deploy` at `3f7f03f`. The verified follow-up implementation and report are recorded on `qa/admin-dashboard`; the branch is not a production release. No published history was rewritten and no force push was used. Neither `main` nor `adopt-deploy` was modified.

## Coordinator reminder: backups and release controls

Before any future production change, create and verify both (a) a repository backup (including a Git bundle stored outside the repository with checksum) and (b) Hostinger backups of website files, database/export where applicable, `.htaccess` and hosting configuration, securely stored environment values, and protected runtime data. Verify archives/exports are non-empty and recoverable; record the last known-good commit and rollback procedure. Obtain the user's explicit permission for the exact release commit and target. No backup, deployment, upload, or production change was performed by WEB-02.

`TODO.md` was read at the start and again at closeout but not edited: repository rules prohibit task-branch edits and require main-branch inbox updates, while WEB workers must not push `main`. The coordinator should reconcile the shared-inbox workflow when integrating this report.
