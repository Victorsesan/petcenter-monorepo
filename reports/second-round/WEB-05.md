# WEB-05 — Rehoming Functional QA & Integration

**Status:** QA pass completed; end-to-end acceptance remains blocked by missing gateway coordination and upload integration.
**Assigned branch:** `qa/rehoming-functional-integration`
**Base reviewed:** `origin/main` at `42228b6` (2026-11-08)
**Scope:** Rehoming route/dependency/file map, posting and listing paths, visitor/merchant surfaces, auth/data-flow checks, and integration against the current project contracts. Historical worker evidence was treated as claims to verify, not proof of current behavior, per `WEB-ACCOUNTS-OPERATIONAL-BRIEF.md` §§6 and 8.

## Work completed

- Mapped the current public rehoming, merchant, profile, verification, and messaging routes against tracked files and source references.
- Reviewed all Rehoming Platform assignment phases A–G and the matching completion evidence available in `docs/second-round/completion-evidence/IMPORT-MANIFEST.md`. No C4/C5/C6 completion evidence package is listed; this is distinct from the fact that some C5-named PHP endpoints exist in the current tree.
- Inspected current posting and merchant-chat authorization boundaries without reading protected runtime content. The listing publish route requires a merchant role server-side; the chat handlers authenticate the caller and constrain thread access to participants.
- Fixed one confirmed portability defect in `adopt/rehoming/merchant-publish-listing.php`: validation now calls the established `rh_text_length()` helper rather than calling optional `mb_strlen()` directly. The listing’s length thresholds (20/20/15), error text, route, and JSON contract are unchanged. This follows the optional-mbstring fallback already provided by `merchant-platform-lib.php`.
- No redesign, authentication bypass, API contract change, or production configuration change was made.

## Current route and dependency map

| Surface / route | Current source and integration | QA result |
|---|---|---|
| `/rehoming/` | `adopt/rehoming/index.html`, loading `/assets/js/rehoming-platform-v1.js` | Route renders. The hub explicitly describes itself as a static deployment/demo and its workflows use browser `localStorage` through the JS store. This is not evidence of a durable, shared visitor/merchant backend; do not accept its demo flows as production end-to-end behavior. |
| `/rehoming/list-a-pet/` | `adopt/rehoming/list-a-pet/index.html` | Route renders and has 12 named controls. Its submit handler posts JSON to `/rehoming/merchant-publish-listing.php`. The form has no file input or upload manager. |
| `POST /rehoming/merchant-publish-listing.php` | `adopt/rehoming/merchant-publish-listing.php`, using `merchant-platform-lib.php` | Target file exists. Source checks POST, then `rh_require_role('merchant')`, validates category/listing options and text lengths, and writes a listing record. The response remains `{success, listing}`. The no-mbstring portability bug was fixed and tested (see below). |
| `/merchant/` | `adopt/merchant/index.html` | Route exists. “Post New Listing” resolves to `/rehoming/list-a-pet/` through `A1_POSTING_PANEL_URL`. The existing dashboard has broader test/demo behavior; this route check does not establish live account persistence or production auth integration. |
| `/merchants/?merchant_id=…` | `adopt/merchants/index.html` | Profile route exists. Evidence for B6 says the profile page has historically used stub merchant/listing data; current source existence is not proof of live merchant-backed rendering. |
| `/verify/` and `POST /verify/verify-submit.php` | `adopt/verify/index.html`, `adopt/verify/verify-submit.php` | Both files exist; the page posts to the matching PHP route. A local static render is not a verification-flow or sensitive-upload runtime test. |
| Merchant messaging | `adopt/rehoming/merchant-chat-start.php`, `merchant-chat-send.php`, `merchant-chat-poll.php`, `merchant-chat-threads.php` | All four files exist. Static review found auth calls and participant checks on the thread surfaces; message send is POST-only. C5 completion evidence is missing, and no browser-level visitor-to-merchant conversation was exercised here. |
| Listing photo/video upload | Expected in posting flow; no `merchant-upload-temp.php` or `video-upload-manager.js` exists at the checked expected paths. The current form has no file control. | **Not integrated.** F4’s historical evidence also described a minimum-three-photo rule and a guessed upload route, so this is an acceptance blocker for posting/upload work, not merely a visual gap. Coordinate the upload API and moderation boundary with WEB-04/C4/C5 owners before adding a client-side workaround. |
| Visitor account, saved searches, favorites, notifications, placement | `adopt/account/settings/index.html` and `adopt/assets/js/*` placement/account helpers, plus `adopt/rehoming/account-lifecycle.php` | Files exist, but D1/F1/F3 evidence includes stubs/demo stores and proposed paths; do not infer shared durable state or email delivery from local fixtures. Requires integration against the authoritative account gateway and persistence contract. |

## Cross-project completion matrix

This matrix records assignment/evidence coverage, not a certification that every historical feature is live. Paths for evidence are under `docs/second-round/completion-evidence/` unless noted.

| Assignment | Current integration focus | WEB-05 disposition |
|---|---|---|
| A1 | Merchant listing form, schema fields, detail-page display | Form route and publish endpoint now agree. Current form omits photo upload; reconcile with F4. The original A1 evidence itself called the posting route unconfirmed. |
| A2 | `grouped_with`, `needs_extra_love`, scheduled flag | Evidence covers category-page changes and cron, but several claims depend on listing persistence and category browsing; no full data-backed browser journey was verified. |
| A3 | Faceted category filters, held/non-active exclusions | Evidence reports predicate/syntax checks rather than a complete browser acceptance pass; reconcile against actual listing state and merchant verification data before declaring complete. |
| B1–B2 | Verification submission and admin review | Source routes exist. Review queue, storage, email configuration, protected selfie flow and admin authorization still require staging runtime checks; do not test with real identity documents in this sandbox. |
| B3–B4 | Merchant badges, response metrics, detail/profile display | Response-statistics fixtures pass locally (see test results); profile/detail integration and scheduled persistence require real linked merchant/thread data. Historical evidence notes placeholder merchant data and unintegrated cron. |
| B5–B6 | Adopter profile and public merchant profile | Current routes exist, but historical evidence identifies local/demo profile data and an incomplete merchant-facing enquiry attachment. Verify privacy and account persistence after WEB-04 gateway integration. |
| C1a–C1b | User reports, welfare routing, admin reports queue | Evidence package is combined. Current cross-route moderation/report persistence was not exercised end-to-end. |
| C2–C3 | Fraud detectors, report/code-of-conduct and safe-exchange UI | Historical evidence identifies assumed storage/paths, placeholder endpoint behavior, and incomplete wiring into real upload/chat handlers. Do not represent these as integrated controls until the server handlers invoke them. |
| C4–C6 | Upload moderation, hardened messaging, reviews | **No matching completion packages in the manifest.** C4 depends on upload handling; C5/C6 remain unverified. C5-named endpoint files exist, but their UI/contract and security integration still need acceptance tests. |
| D1–E5 | Placement lifecycle, merchant dashboard, account lifecycle, support, theme and test accounts | Some files exist, but historical evidence includes demo/stub paths and sandbox-only tests. Validate shared lifecycle status and actual dashboard routes after account gateway integration. |
| F1–F7 | Favorites/alerts, compatibility, sharing/digests, guided posting, resources, cookies, locale/currency | Several historical paths are explicitly proposed/unconfirmed; F4 upload/minimum-photo behavior is not present in the current listing form. Verify actual public links, digest delivery/opt-out, and locale route deployment before acceptance. |
| G1 | Merchant links and fee checkout | Historical evidence calls out proposed merchant endpoints and a placeholder token/ownership check. Do not expose checkout-generation flows until auth, listing ownership and a real payment-link adapter are verified. |

## Test and evidence results

All checks ran against the local sandbox checkout only. No production endpoints or Hostinger environment were accessed.

- Existing JS response-statistics suite: `node adopt/rehoming/merchant-response-stats.test-v1.js` — **17 passed, 0 failed**.
- Existing PHP response-statistics suite: `php adopt/rehoming/merchant-response-stats.test.php` — **14 passed, 0 failed**.
- PHP syntax: `php -l` on **20** tracked rehoming, verification, and related admin-verification PHP files — all passed, including `merchant-publish-listing.php`.
- JS syntax: `node --check` over `.js` files in the rehoming, merchant, profile, verification, and shared `assets/js` trees — all passed.
- Portability regression check: PHP 8.3.6 reports `mbstring=no` in this sandbox. Loading the existing library and calling `rh_text_length()` returned 20 and 21 for 20- and 21-character ASCII strings. The patched endpoint now uses that tested helper.
- Static local HTTP smoke: `/rehoming/`, `/rehoming/list-a-pet/`, `/merchant/`, and `/merchants/?merchant_id=MERCH-00001` returned HTTP 200. `/rehoming/merchant-publish-listing.php` returned 200 only because the HTTP smoke used Python’s static file server; this is **not** an executed PHP/API success.
- Chromium headless local render: the hub and posting form loaded with the expected page titles and no matching uncaught/resource-load error strings in captured logs. This did not submit forms, access sessions, test API authorization, inspect a production console, or validate mobile/RTL/accessibility behavior.
- `git diff --check` passed.

## Open integration blockers / coordinator actions

1. **Coordinate with WEB-04 immediately on the authoritative gateway route and account/session contract.** The current remote branch listing has no WEB-04 account branch; no unsaved work can be assessed from Git. The existing posting endpoint enforces server-side merchant role, but the page itself is publicly reachable and has no login/gateway handoff. Re-run visitor/merchant flows once WEB-04 publishes a contract/branch, without relying on query/body fields as role proof.
2. **Resolve upload ownership and route with WEB-04 and C4/C5.** The current posting page has no image inputs; `merchant-upload-temp.php` and `video-upload-manager.js` are absent. F4’s minimum-three-photo requirement and C4 moderation cannot be accepted until the real authenticated upload endpoint, ownership checks, storage path and moderation handoff are integrated and tested.
3. **Reconcile demo/stub stores with the server contract.** The hub’s localStorage data must not be reported as durable cross-account placement, messaging, favorites, or account state. B5/B6, D1/F1/F3 and E1 evidence contain explicit mock/stub or persistence caveats; stage tests need seeded, non-sensitive test accounts and cleanup.
4. **Confirm the missing evidence set.** The import manifest has no C4/C5/C6 completion packages. Current C5 endpoint files do not substitute for the C5 task’s contract, tests, or browser integration evidence.
5. **Before any coordinator merge/staging decision, take and verify both backups:** a repository backup (including current branches/refs) and a Hostinger files **and database** backup. Keep deployment disabled; no WEB-05 account may deploy, upload, or trigger Hostinger auto-deployment. This branch did not deploy or upload anything.

## Files changed

- `adopt/rehoming/merchant-publish-listing.php` — portability fallback fix, with `TASK-WEB-05` anchors.
- `reports/second-round/WEB-05.md` — this report.

## Source documents reviewed

`docs/second-round/CURRENT-WAVE-ACCOUNT-LAUNCHERS.md`; `docs/second-round/WEB-ACCOUNTS-OPERATIONAL-BRIEF.md`; `docs/second-round/core-docs/Rehoming-Platform-Task-Assignments-9.md`; `docs/second-round/CORE-DOCUMENT-READING-GATE.md`; `docs/second-round/HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`; `WORKER-GIT-SYNC-PROTOCOL.md`; `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md`; `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`; `adoption-website-project-summary.md`; `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`; `PETCENTER_DESIGN_SYSTEM_v1.0.md`; `TODO.md`; `ROADBLOCKS-AND-FIXES.md`; `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`; `docs/second-round/WORKER-PACKAGE-INVENTORY.md`; `docs/second-round/SECOND-ROUND-ISSUE-REGISTER.md`; `docs/second-round/INTEGRATION-PROGRESS.md`; `docs/second-round/completion-evidence/IMPORT-MANIFEST.md`; and available Rehoming A–G completion evidence. Protected runtime files/content were not accessed.
