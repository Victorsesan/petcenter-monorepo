# MANUS-09 — Second-Round Security and PHP/HTML Quality Review

## Assignment
Cross-cutting security, PHP quality, HTML quality, upload safety, authentication, and security issue-register findings. Starting commit: `f26629e82aa3a5d5b47eb0864311d759cce9f840`.

## Branch and delivery
Branch: `task/second-round-MANUS-09`.
This pass made no application-code changes: every confirmed finding was either in another account's owning file zone, a shared contract, or dependent on protected/server-only data. Therefore there are no new `BUGFIX-MANUS-09-*` anchors or application commit hash; this report is the scoped verification and handoff artifact.

## Documents read
Mandatory order: `INTEGRATION-PROGRESS.md`; `TODO.md`; `docs/second-round/README.md`; `docs/second-round/SECOND-ROUND-REQUEST.md`; `docs/second-round/SECOND-ROUND-SETUP-AND-SKILLS.md`; `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`; `docs/second-round/WORKER-PACKAGE-INVENTORY.md`; `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`; `docs/second-round/MANUS-ACCOUNT-PROMPTS.md` (MANUS-09 block).

Relevant core documents: `docs/second-round/core-docs/Admin-Code-Integrator-5.md`; `Admin-Project-1-Worker-Task-Assignments-2.md`; `ADMIN_PROJECTS_2_3_4_TASK_ASSIGNMENTS-2.md`; `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`; `adoption-website-project-summary.md`; `Rehoming-Platform-Task-Assignments-9.md`; `ROADBLOCKS-AND-FIXES-2.md`; `TODO-5.md`; `Task-AccessibilityWidget-Completion-4.md`; `Task-WidgetPositioning-Completion-2.md`; `Web-Code-Integrator-5.md`.

Root workflow/design/tracker documents: `WORKER-GIT-SYNC-PROTOCOL.md`; `ROADBLOCKS-AND-FIXES.md`; `Admin-Project-1-Task-Assignments.md`; `Web-Projects-3-4-5.md`; `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`; `PETCENTER_DESIGN_SYSTEM_v1.0.md`; `reports/second-round/ACCOUNT-REPORT-INDEX.md`. No prior worker completion files were present in this checkout; `reports/` contained only the second-round index and README.

## Issue/status matrix

| Register finding | Verification against current `main` | Classification / handoff |
|---|---|---|
| `Admin-Project-1-Task-1`: `chat-rate.php` accepts any syntactically valid submitted email | Confirmed at `adopt/admin/chat-rate.php:26-53`; session-token enforcement exists, but the submitted email is not cross-checked against the thread record. | Needs My Review by the Admin live-chat owner (MANUS-01); do not duplicate the shared auth change here. |
| `Admin-Project-1-Task-3`: lock can be enabled without a PIN | The named `chat-lock.php` endpoint was not present in the checkout and no current `chat_requires_pin`/`set_preference` implementation was found under `adopt/admin`. | Blocked / Needs My Review; MANUS-01 must reconcile the missing endpoint or current path before any patch. |
| Web Project 5b: client-only pet-match upload/backend hardening | `adopt/pet-match/pet-match-submit.php` and `pet-match-upload-temp.php` are absent. | Blocked; hand to MANUS-04/owner. Client-side checks cannot be treated as server-side security. |
| Rehoming E2: private merchant/thread JSON under web root | `adopt/rehoming/merchants/` exists and contains a merchant record directory; contents were not opened because this review must avoid private data. | Needs My Review; hand to MANUS-05/06/integrator to add server-level denial or move storage outside web root. |
| Unified/Shipping Tier 1: `update-adoption.php` path traversal and unlocked writes | Confirmed at `adopt/admin/update-adoption.php:21-42,92-93`: raw `code` is interpolated into the application path and writes use plain `file_put_contents`. | Needs My Review; hand to MANUS-07/unified-status owner because this is an existing shared status handler and touches protected application storage. |
| Rehoming F6: malformed testimonials chat-widget URL | Confirmed in `adopt/testimonials/index.html:494`, `adopt/testimonials3/index.html:763`, and `adopt/testimonials3/index8.html:750`: `adopt.petcenterco.com/admin/chat-widget.js` lacks a scheme/root slash. Playwright requested `/testimonials/adopt.petcenterco.com/admin/chat-widget.js` and failed. | Needs My Review; hand to the page owner/integrator (MANUS-05/06). Not patched to avoid duplicating the shared-page ownership chain. |
| Admin dashboard `showToast` undefined | False alarm/already resolved: `adopt/admin/index.html:3733` defines `function showToast(msg, type) { PC.toast(msg, type); }`; current calls at lines 4216, 4231, and 4308 therefore have a definition. | False Alarm / Already Resolved. |
| Admin dashboard duplicate `editCategory` ID | False alarm/already resolved in current checkout: BeautifulSoup scan found zero duplicate IDs across 46 non-protected HTML files. | False Alarm / Already Resolved. |
| Admin upload safety | `adopt/admin/admin-upload-temp.php:31-113` has admin-token checking, extension/MIME/size checks, random tokens, sanitized original-name storage, and a `.htaccess` deny file. `publish-pet.php` still consumes token strings through `glob()` without independently validating token shape. | Needs My Review; hand to the upload-flow owner. Do not alter without reconciling the publish/upload shared contract and protected storage paths. |
| Public/contact upload safety | `adopt/apply/upload-temp.php` and `adopt/contact-us/upload-temp.php` perform size, MIME, extension, basename/sanitization, random-token, and temp-directory protection checks. Live server configuration and protected upload contents were not inspected. | Needs live/server review, not patched in this branch. |
| Chat upload safety/auth | `adopt/admin/chat-upload-temp.php` checks method, kind, session/admin authentication or IP rate limit, size, detected MIME, safe extension fallback, random token, and locked temp storage. | No confirmed MANUS-09 code defect from static review; live auth/rate-limit exercise remains a handoff to MANUS-01/10. |

## Files inspected
`adopt/admin/update-adoption.php`, `publish-pet.php`, `chat-rate.php`, `admin-upload-temp.php`, `chat-upload-temp.php`, `admin/index.html`; `adopt/apply/upload-temp.php`; `adopt/contact-us/upload-temp.php`; testimonial page script references; rehoming directory names; and representative public/admin route files. Protected `config.php`, `pet.json`, `applications/`, `uploads/`, `chat-media/`, and `logs/` were not opened, copied, modified, or committed.

## Tests and results
- PHP: `php -l` on 1,277 non-protected PHP files — 1,277 passed, 0 parse failures.
- JavaScript: `node --check` on 26 non-protected JS files — 0 failures.
- HTML structure: BeautifulSoup scan of 46 non-protected HTML files — 0 duplicate-ID files.
- Browser: Playwright with system Chromium at 1440×900 and 390×844 loaded `/`, `/pets/cats/`, `/testimonials/`, and `/admin/` from a local static server. The testimonial malformed URL failed exactly as described above. Other local 404s were expected sandbox limitations (PHP endpoints and omitted media/assets); no claim of live backend verification is made.
- Playwright's bundled browser was unavailable, so the installed `/usr/bin/chromium` executable was used. No dedicated security-audit/PHPStan skill was installed; the documented OWASP/PHP/HTML checklist was applied directly.

## Unresolved blockers and handoffs
Protected server-only data and credentials are unavailable by policy; live database/session/upload tests require the deployed environment. MANUS-01 owns chat auth/lock/rating contracts, MANUS-04 owns Web Projects 3–5, MANUS-05/06 own rehoming/page storage, MANUS-07 owns unified status handlers, and MANUS-10 owns full browser/integration QA. No TODO.md entry was added because the confirmed items already have register ownership and adding entries would alter the shared main-only inbox from this worker branch.

## Self-check
Only this report was added. No protected paths, secrets, tokens, passwords, keys, applicant data, ZIPs, or application payloads were added. The branch remains based on the clean starting commit and contains no application-code changes or unreviewed shared-contract changes.
