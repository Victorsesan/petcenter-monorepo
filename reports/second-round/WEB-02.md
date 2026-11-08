# WEB-02 — Admin Dashboard Baseline QA Report

**Date:** 2026-11-08 08:12 UTC
**Assigned branch:** `qa/admin-dashboard`
**Base:** `origin/main` at `42228b6`
**Implementation commit:** `eb5f886` (`WEB-02: harden admin dashboard baseline`)
**Status:** Implementation and isolated verification complete; external push is held for the Hostinger-trigger safety check described below.

## Scope and safety boundary

Work followed the WEB-02 Admin Dashboard baseline assignment. The required project summaries, current WEB account instructions, applicable admin assignments/evidence, coordination/synchronization materials, issue register, and Hostinger checklist were reviewed before implementation. The redesign account brief was not used for this WEB assignment.

No Hostinger login, deployment, upload, production request, or automatic deployment action was performed. No production `.env`, `config.php`, application record, pet record, upload, chat-media file, or log was read or executed. Endpoint tests used disposable temporary directories, generated test-only credentials, synthetic data, and a stub chat-admin helper; those fixtures were removed by test teardown.

## Completed work

| Area | Work completed | Evidence |
|---|---|---|
| Dashboard rendering | Replaced pet-card interpolation of pet-controlled fields and inline handlers with text nodes and event listeners. Image URLs are accepted only when same-origin and under `/adoptions/pets/`; a placeholder is used otherwise. | `adopt/admin/index.html:4836–4902` |
| Accessibility | Associated login, bot-profile, generated pet-form, and generated scenario labels with controls; added an assertive login-error region, polite upload status announcements, a distinct completed state, keyboard-operable upload drop zones, names, and visible focus styling. | `adopt/admin/index.html:1129, 1775–1785, 2426–2460, 4146–4157, 4679–4710, 6963–6975` |
| Missing pet-delete route | Added the route already called by the dashboard. It preserves the JSON success/message response pattern, requires POST and the existing admin token, constrains IDs to known prefixes, uses canonical path containment, rejects symlink targets, removes trees without following links, and rebuilds the existing `{pets:[...]}` index. | `adopt/admin/delete-pet.php:1–143`; UI request at `adopt/admin/index.html:5153` |
| Application deletion | Hardened recursive cleanup so symlinks are unlinked rather than traversed, rejected top-level symlink targets, and stopped sanitizing invalid identifiers into a different record. | `adopt/admin/delete-application.php:38–77` |
| Pet paths and publishing | Added category/ID allow-lists and canonical path containment to retrieval, update, and statistics paths; added category and staged-upload-token validation before publishing side effects; retained the documented empty optional video token; removed raw internal filesystem paths from client-facing errors. | `get-pets.php:24–63`; `publish-pet.php:89–121`; `update-pet.php:52–86, 196–240`; `stats.php:45 onward` |
| Upload authentication | Temporary uploads now fail closed when admin-token configuration is absent, compare tokens with `hash_equals`, and no longer log bearer upload tokens. | `adopt/admin/admin-upload-temp.php:40–50, 122–127` |
| Login/session | The existing login contract and response fields remain; token comparison is type-safe and constant-time, fresh authentication rotates the session ID, and the session cookie preserves its configured path/domain while setting HttpOnly, SameSite=Lax, and Secure when HTTPS is detected. | `adopt/admin/admin-auth.php:2–17, 45–58` |
| Chat/application compatibility | Removed the duplicate email-resume language update that referenced undefined `$thread`; adoption codes are normalized to uppercase before filesystem lookup, and documented historical pet prefixes remain mapped. | `adopt/admin/chat-start.php:188`; `adopt/admin/update-adoption.php:32–45, 85 onward` |
| One-time setup route | Added the existing `chatRequireAdmin()` guard before the session-token schema setup script can reveal configuration state or execute DDL. | `adopt/admin/chat-session-token-setup.php:33–36` |
| Apache file | Replaced the tracked bare `Facebook` line in `adopt/admin/.htaccess` (not a standard Apache directive) with comments, eliminating that apparent parse-error risk. The file still contains no access-control rules; see residual risks. | `adopt/admin/.htaccess:1–4` |
| Regression coverage | Added isolated tests for authenticated deletion, index rebuilding, traversal rejection, and pet/application symlink safety; registered the new test file. Corrected the existing endpoint test helper to prefer emitted HTTP status where available and documented the endpoint's actual invalid-code message. | `tests/Web02AdminBaselineTest.php`; `tests/Manus09EndpointTest.php`; `phpunit.xml.dist` |

API request/response shapes used by existing UI flows were preserved; lower-case valid adoption codes are now accepted as an additive compatibility improvement. No visual redesign or imagery was introduced.

## Verification performed

- `php -l` passed for all changed PHP endpoints and test files.
- `node --check` passed for the single inline JavaScript block extracted from `adopt/admin/index.html`.
- `phpunit --configuration phpunit.xml.dist --colors=never --do-not-cache-result` passed: **15 tests, 57 assertions**.
- `git diff --check` passed.
- Headless Chromium, with external networking disabled and a clean profile, verified the login form, associated token label, live error region, and initially hidden dashboard. A second browser probe generated the pet form and verified its field label associations and accessible, keyboard-operable video/primary-image/bulk-image drop zones.
- Tests and browser probes did not log into the dashboard or contact production services. The admin login endpoint was syntax-checked but not executed: it writes the admin activity log and performs a third-party GeoIP lookup.
- An Apache parser was unavailable, so `.htaccess` syntax was verified by inspection only. Hostinger’s PHP/Apache versions and enabled extensions were not verified.

## Additional findings and remaining review items

1. **Medium — application collection over-returns applicant details.** `adopt/admin/get-applications.php:73–98` includes phone, full address, employment/residence/landlord details, veterinarian details, and additional information in the collection response. The collection renderer in `adopt/admin/index.html:7092–7105, 7135 onward` uses only the code, applicant name/email, pet summary, status, and application date; selecting an application performs a separate code-specific fetch at `index.html:7380–7397`. Recommend minimizing the collection response while retaining details only for the selected application. Not changed because removing response fields could affect undocumented consumers and requires contract-owner review.
2. **Medium — broad CORS and cleartext GeoIP remain.** `adopt/admin/admin-auth.php:17` still emits `Access-Control-Allow-Origin: *`. The same endpoint sends the client IP to `http://ip-api.com` at line 81. Confirm whether cross-origin clients are required; otherwise restrict CORS, and review whether the third-party IP/location lookup should be removed or replaced with an approved encrypted service. Not changed because this alters integration/privacy behavior.
3. **High — hosting-level protection of private admin files is unverified.** The corrected `adopt/admin/.htaccess` intentionally has comments only, not deny rules. Confirm through the approved hosting configuration that `.env`, `admin-activity.log`, `chat-config.php`, and other private/runtime files cannot be fetched over HTTP. No production URL was probed. Do not infer protection from dot-file naming.
4. **Medium — one-time setup lifecycle.** The source comment in `chat-session-token-setup.php:24–27` instructs the operator to delete or move the script outside the web root after successful setup. It now requires admin authorization, but this task did not access the database or execute the script; the coordinator should verify its post-setup removal during an approved release.
5. **Hostinger automation status is not fully observable here.** No tracked `.github/workflows` files were found. GitHub API requests for repository hooks, deployments, and Actions workflow metadata returned HTTP 403 (integration permission denied), so external webhooks and Hostinger’s configured deployment branch could not be verified. Confirm the assigned branch is excluded from Hostinger auto-deployment, or disable the trigger, before pushing if that connection might observe this branch. No deployment was initiated.
6. **Responsive/RTL end-to-end coverage remains limited.** Browser checks covered the clean login screen and generated form semantics, not authenticated production workflows, RTL navigation, or a full mobile/tablet visual review; those require a controlled staging account and approved test data.

## Coordinator reminder: backups and release controls

Before any future production change, create and verify both (a) a repository backup (including a Git bundle stored outside the repository with its checksum) and (b) Hostinger backups of website files, database/export where applicable, `.htaccess` and hosting configuration, securely stored environment values, and protected runtime data. Verify the archives/exports are non-empty and recoverable. Record the last known-good commit and rollback procedure. No backup or production change was performed by WEB-02.

The assigned code commit is local as of this report. `TODO.md` was read but not edited: its own rules prohibit edits on a task branch and direct workers to update it on `main`, while the current WEB instructions prohibit worker pushes to `main`. The coordinator should resolve that shared-inbox workflow when integrating this report; WEB-02 made no change to `main`.
