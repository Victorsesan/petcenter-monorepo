# Second-Round Report — WEB-04

**Date:** 2026-11-13 UTC+01:00 (integration and corrective audit follow-up)
**Account:** `WEB-04`
**Assignment:** Rehoming authentication gateway and backend
**Assigned branch:** `qa/rehoming-auth-backend`
**Status:** WEB-04 implementation and reproducible local regression checks are complete on the assigned QA branch; the implementation is not in current `main` or `adopt-deploy`, and live provider/hosting and cross-account integration gates remain open.

## Assignment

Implemented the current WEB-04 scope: a usable sign-in/sign-up gateway, server-side Google OpenID Connect flow, server-authoritative accounts and roles, protected visitor/merchant routes, private adopter-profile persistence, session and CSRF safeguards, and consistent authorization for Rehoming mutations. The corrective audit then found and fixed source-confirmed gaps: storage defaults under the served tree, session logout ordering on revocation failure, an async profile-load data-loss race, pending-merchant listing access, missing `nbf` validation, and staging-login IP aggregation. The current code now fails closed unless account, Rehoming runtime, verification, and staging-test-account storage are explicitly configured outside the web root. Existing routes and API payload/response shapes were preserved where modified. No manual Hostinger deployment, upload, configuration change, production request, or Hostinger-specific deployment-trigger action was performed. The authorized GitHub push was limited to `qa/rehoming-auth-backend`; GitHub deployment-metadata access returned HTTP 403, so remote Hostinger integration settings could not be inspected.

## Starting repository state

The local assignment checkout initially pointed at `42228b6d2d7f31e3721d761b7c76661fdd166ce7`. Before committing, I fetched and fast-forwarded the assigned branch to current `origin/main` at `a50c06109dd82a7bdadc8aa8b0e6d9bfdea8061b`; the only intervening main change was a four-line clarification in the active WEB brief concerning GitHub deployment-metadata access. The implementation was tested and committed on top of that synchronized base. The worktree was clean after the two code commits.

The assigned branch had no remote-tracking branch at the initial sync check. The historical account index is explicitly marked as a historical MANUS-round index; it was not edited. The coordinator should create/update the current-wave aggregate index. `TODO.md` was read at the beginning and end and was not edited on this task branch; shared inbox changes are reserved for the coordinator/main workflow.

## Documents read

The complete current-wave launcher and WEB account operational brief were read, including all of WEB-04's implementation, testing, reporting, and release restrictions. The complete `adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, and `PETCENTER_DESIGN_SYSTEM_v1.0.md` (current v1.25 content) were read before source changes. The WEB-04 mandatory gate was completed, including:

- `docs/second-round/CORE-DOCUMENT-READING-GATE.md` and `docs/second-round/core-docs/Rehoming-Platform-Task-Assignments-9.md`;
- `INTEGRATION-PROGRESS.md`, `ROADBLOCKS-AND-FIXES.md`, `TODO.md`, `WORKER-GIT-SYNC-PROTOCOL.md`, `VDM-GIT-SYNC-COORDINATION-PROTOCOL.md`, and `INTEGRATOR-GIT-SYNC-MERGE-PROTOCOL.md`;
- `docs/second-round/SECOND-ROUND-MANUS-COORDINATION.md`, `WORKER-PACKAGE-INVENTORY.md`, `SECOND-ROUND-ISSUE-REGISTER.md`, and `HOSTINGER-PREDEPLOY-AND-ROLLBACK-CHECKLIST.md`;
- `reports/second-round/README.md`, the historical `ACCOUNT-REPORT-INDEX.md`, the integration build-check report dated 2026-11-06, and relevant current/historical reports: `MANUS-05.md`, `MANUS-06.md`, `MANUS-09.md`, `MANUS-11.md`, and current WEB-02/03/05 reports on their remote branches;
- relevant Rehoming completion evidence for B1, B2, B5, E2, E3, and E5.

The package inventory lists C5 and C6 as the two Rehoming packages without completion files; no C5/C6 completion evidence was available. The A4 tracker contract was reviewed, but no A4-specific completion record was listed in the inventory. Historical evidence was treated as context, not proof of the current implementation. Current WEB-account instructions—not DESIGN-only scope—governed this work.

## Verification results

| Area | Status | Evidence / limitation |
|---|---|---|
| Gateway and return routing | Implemented | `/signin/` now presents real login/signup actions; `mode=signup` is supported; server-returned `next` values are path-validated and role-constrained. |
| Identity and roles | Implemented in code | Google sign-in may create only visitor accounts. Merchant/admin roles are server-assigned through the CLI provisioning tool; merchants must be verified before merchant-only operations. |
| Google OIDC | Structure implemented; live provider validation blocked on configuration | Authorization-code flow with state, nonce, PKCE S256, server-side code exchange, signature/JWKS and claim validation. No live Google credentials were present and no provider callback was executed. |
| Sessions and CSRF | Implemented and isolated-tested | Strict cookie-only sessions, HttpOnly/Secure/SameSite=Lax, ID rotation, 30-minute idle and 12-hour absolute expiry; synchronizer tokens for cookie-authenticated writes. |
| Protected routes | Implemented; Apache behavior needs staging verification | PHP wrappers/`.htaccess` rules protect `/merchant/`, `/account/settings/`, and `/rehoming/list-a-pet/`. Listing-entry access now also requires an active, verified merchant; pending merchants receive a verification message instead of the unusable form. Apache direct-static-file denial and rewrite behavior still require staging verification. |
| Adopter profile | Implemented and browser-tested | Existing field names/values are persisted server-side per account. Controls stay disabled until the authenticated profile read succeeds; load failures expose retry without allowing an empty overwrite. The custom dropdowns support keyboard/ARIA state and RTL-direction smoke testing. The profile is not silently shared with merchants or attached to first enquiries. |
| Rehoming API authorization | Implemented; integration review remains | Listing publish, chat, review, moderation, and lifecycle routes now use server-side roles and CSRF where cookie-authenticated. Existing JSON shapes were retained. |
| Verification submission | Hardened; B2 queue integration blocked | Existing pre-account form and response flow remain. CSRF, per-email/IP and per-IP throttling, upload validation, owner-only temporary media, cleanup, and an owner-only review queue are retained. The endpoint requires `VERIFY_STORAGE_DIR` to be an existing owner-only absolute directory outside the web root; it no longer falls back to a public upload path. The B1 record path/layout does not match the active B2 reader and must be reconciled before the admin queue can display submissions. Host-level request-size/rate controls and actual Apache behavior remain release checks. |
| Public legal routes | Implemented | `/privacy-policy/` gives a factual account-data supplement linking the canonical policy; `/terms-of-service/` aliases the approved canonical terms without inventing legal text. |
| Deployment readiness | Not approved | No production secrets, Apache configuration, Google Console client, or Hostinger behavior was available to validate. This branch is not a release approval. |

### Routes and data-flow map

- `GET /signin/` is the gateway; `?mode=signup` selects the account-creation tab. The shared auth prompt's default signup destination now targets this route.
- `GET /rehoming/auth.php?action=status|csrf|me|profile` exposes safe session state, a CSRF token, and the current visitor profile. `POST` handles CSRF-protected logout/profile updates; `POST action=test-login` is staging-only, requires the same-origin CSRF token, and applies a 20-per-IP plus six-per-username budget per 900 seconds.
- `GET /rehoming/google-oauth.php` starts Google authorization; `GET /rehoming/google-oauth-callback.php` consumes one-time state and completes the server-side exchange. The OAuth-start limiter allows ten starts per IP per 900 seconds; the 11th synthetic request was throttled.
- `/merchant/`, `/account/settings/`, and `/rehoming/list-a-pet/` preserve their routes while passing through role-specific PHP wrappers. The listing-entry wrapper requires verified merchant status. Legacy direct `index.html` access is covered by Apache rules, subject to the staging verification noted above.
- Cookie-authenticated mutations for listing publication, chat, reviews, lifecycle, `/rehoming/auth.php`, and `/verify/verify-submit.php` require the shared same-origin CSRF wrapper/token. Existing bearer-token requests remain a separate non-ambient authentication path.
- The verification form remains pre-account. No new listing image/video upload path was introduced.

### Files inspected

Before and during implementation, inspected the current auth prompt and sign-in page; account settings and listing form; Rehoming platform/session/store helpers, lifecycle, publish, chat, review and moderation endpoints; verification form and upload endpoint; runtime `.htaccess` and `.gitignore`; E5 test-account and WEB-02 verification-admin sources; existing admin authentication/dashboard paths; PHPUnit configuration and existing endpoint tests; and the relevant route/legal pages. Cross-account reviews covered the current WEB-02, WEB-03 and WEB-05 reports and changed-path summaries.

### Files added or modified

No files were deleted or moved. No application data, JSON account file, credential, or runtime record was added.

**Modified (18):** `.gitignore`; `adopt/account/settings/index.html`; `adopt/assets/js/show-auth-prompt.js`; `adopt/rehoming/account-lifecycle.php`; `adopt/rehoming/list-a-pet/index.html`; `adopt/rehoming/merchant-chat-poll.php`; `adopt/rehoming/merchant-chat-send.php`; `adopt/rehoming/merchant-chat-start.php`; `adopt/rehoming/merchant-chat-threads.php`; `adopt/rehoming/merchant-platform-lib.php`; `adopt/rehoming/merchant-publish-listing.php`; `adopt/rehoming/merchant-review-moderate.php`; `adopt/rehoming/merchant-reviews.php`; `adopt/rehoming/runtime/.htaccess`; `adopt/signin/index.html`; `adopt/verify/index.html`; `adopt/verify/verify-submit.php`; `reports/second-round/WEB-04.md`.

**Added (20):** `adopt/account/settings/.htaccess`; `adopt/account/settings/index.php`; `adopt/admin/test-accounts-data/.htaccess`; `adopt/assets/js/web04-csrf.js`; `adopt/merchant/.htaccess`; `adopt/merchant/index.php`; `adopt/privacy-policy/index.php`; `adopt/rehoming/auth.php`; `adopt/rehoming/google-oauth.php`; `adopt/rehoming/google-oauth-callback.php`; `adopt/rehoming/list-a-pet/.htaccess`; `adopt/rehoming/list-a-pet/index.php`; `adopt/rehoming/web04-auth-core.php`; `adopt/terms-of-service/index.php`; `tests/web04/README.md`; `tests/web04/gateway-smoke.py`; `tests/web04/portable-length.php`; `tests/web04/profile-loading-smoke.py`; `tests/web04/run-regressions.php`; `tools/provision-rehoming-account.php`.

The original 32 implementation paths retained their WEB-04 task anchors; the five newly added test artifacts are self-contained and do not alter application runtime. The corrective source changes remain inside the existing WEB-04 integration areas. `git diff --check` passes; the final conflict-marker scan is recorded after synchronization.

## Tests performed

Tests used synthetic identities, generated short-lived RSA keys, and owner-only temporary directories and PHP sessions under `/tmp`; all fixtures are cleaned by the test harness. No existing account, application, pet, upload, chat-media, production config, or log data was opened or changed. Earlier one-off test scripts from the initial pass were temporary and are not retained; the following tests are now reproducible from the branch.

- `php tests/web04/run-regressions.php` — **78 assertions passed**. In addition to the previously recorded storage, profile, merchant-eligibility, throttling, `nbf`, and logout checks, it now covers synthetic OIDC issuer/audience/authorized-party/expiry/nonce/email/signature/key failures, OAuth state mismatch/expiry/replay and PKCE, exact redirect configuration, safe return paths, CSRF origin/token rejection, server-assigned visitor roles, and gateway/legal/status markup. All identities, credentials, and keys are synthetic.
- `python3 tests/web04/profile-loading-smoke.py` — **passed** at 390×844 using a local static server and mocked auth API. It verifies that fields remain disabled through a delayed failed profile load, retry succeeds, saved values and conditional fields are restored, keyboard selection works, and the edited payload is submitted. This is a frontend smoke test, not an authenticated PHP route test.
- `python3 tests/web04/gateway-smoke.py` — **passed** at 390×844 using local static files and mocked auth/OAuth endpoints. It verifies signup selection, keyboard tab navigation, mobile viewport fit, legal links, not-configured and backend-error messaging, and navigation to the server-side OAuth handler without fabricating success.
- `php -d disable_functions=mb_strlen,mb_substr tests/web04/portable-length.php` — **passed** with both mbstring functions disabled; the shared helper correctly counts and slices multi-byte UTF-8 text through the optional iconv/PCRE fallback.
- `php -l` passed for all changed/new PHP files; `node --check` passed for every inline sign-in and settings-page script; the listing endpoint has no direct `mb_strlen()` call; `git diff --check` passed.
- The initial implementation's targeted axe-core 4.14.0 scans reported zero violations in the gateway/settings main regions and expanded profile listbox, with shared navigation/footer/cookie-consent contrast findings. Axe was not rerun after this corrective profile-loading update, so those earlier results are not presented as a final-version scan.
- PHPUnit was not run: neither a `phpunit` executable nor `vendor/bin/phpunit` is installed. No live Google OAuth request, Apache parser/staging run, or Hostinger staging test was performed. `.htaccess`, direct-static-file denial, and rewrite behavior still require controlled Apache staging verification.

## Security, accessibility, RTL, and external research

The auth core rehydrates identity and roles from server-side records; the browser cannot assign roles. Google-created accounts are visitors only. OAuth state is single-use and short-lived; the callback verifies issuer, audience, authorized party, expiry/issued-at, `nbf` (with clock tolerance), nonce, verified email, key ID and RS256 signature. Return paths reject external, scheme-relative, encoded and traversal destinations. Account/profile/runtime writes require explicit existing owner-only external directories; data and lock/token files use restrictive permissions. Verification queue/media storage also fails closed outside the served tree. The shared Rehoming text helpers now follow WEB-05's optional mbstring/iconv/PCRE strategy for character-safe length limits and slicing, with the WEB-05 task anchors retained and one shared implementation. API errors avoid returning secrets or internal filesystem paths.

The gateway, profile controls, and privacy notice use accessible status/label semantics; profile inputs and custom dropdowns remain disabled through profile-load failure, expose a retry action, and enable after a successful load. The dropdowns have keyboard operation, selected-state announcements, and a visible focus treatment. Tested RTL coverage was a directionality smoke test for the dropdown, not a full translated RTL site review. Existing route, form field names, review/listing/chat JSON contracts, and verification multipart flow were retained.

Authoritative references used for design decisions:

- Google, [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server) and live public [Google OIDC signing keys](https://www.googleapis.com/oauth2/v3/certs).
- OpenID Foundation, [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html); IETF [RFC 9700 — Best Current Practice for OAuth 2.0 Security](https://www.rfc-editor.org/rfc/rfc9700.html).
- OWASP Cheat Sheets: [Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html), [CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html), [Unvalidated Redirects and Forwards](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html), [Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html), and [Authentication](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html).

## External configuration required before staging/release

- Set `APP_BASE_URL` to the canonical HTTPS adoption origin and `SESSION_SECRET` to a high-entropy value of at least 32 bytes in the server environment; do not place either value in the repository.
- Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`. The latter must exactly equal `${APP_BASE_URL}/rehoming/google-oauth-callback.php` and be registered in the Google OAuth client. Validate authorized origins/redirects and consent settings in a controlled staging account.
- **Required:** provision `REHOMING_AUTH_DATA_DIR` and `REHOMING_RUNTIME_DIR` as existing, writable, owner-only (no group/other permission bits) absolute directories outside the adoption web root. The application intentionally has no web-root or public-runtime fallback and becomes unavailable when either is missing or unsafe. If set, `PETCENTER_REHOMING_TOKEN_SECRET` must be at least 32 bytes; otherwise the existing library derives its token key from `SESSION_SECRET`.
- **Required for verification submissions:** set `VERIFY_STORAGE_DIR` to an existing, writable, owner-only absolute directory outside the adoption web root. The handler creates private subdirectories and uses owner-only upload/queue files. It returns a service-unavailable response rather than falling back if configuration or permissions fail.
- For staging-only E5 login, set both `APP_ENV=staging` and `WEB04_ENABLE_TEST_AUTH=1`; set `WEB04_TEST_ACCOUNTS_FILE` to an explicitly configured owner-only JSON file outside the web root. Do not enable test auth in production; the former public-tree fallback is removed.
- Runtime requires PHP 8.1+ with OpenSSL, cURL, and JSON support. The listing validator now uses `rh_text_length()` and no longer directly requires `mb_strlen`; the portable fallback is covered by a test with mbstring functions disabled.
- Apache must honor the route `.htaccess` files, rewrite/access-control directives, and deny rules. Validate them on staging; do not infer enforcement from the local PHP server.

## Unresolved issues and handoffs

1. **High — lifecycle storage integration.** `account-lifecycle.php` retains the historical E2 per-record/list/message path while the active Rehoming library uses flat JSON `listings`, `threads`, and `messages` stores. WEB-04 gates lifecycle requests by server identity/role/CSRF and revokes bearer tokens, but it does not claim that legacy lifecycle operations update the active stores. Reconcile the E2 path with the WEB-05/platform owner and test suspend/delete/restore against isolated active-format stores before release.
2. **High — B1/B2 verification-queue contract mismatch.** The WEB-04/B1 intake writes one record at `VERIFY_QUEUE_DIR/{timestamp}_{safeUsername}.json` under the explicitly private `VERIFY_STORAGE_DIR`, with `username`, `email`, `country`, `submitted_at`, `status`, and `email_sent`. The active B2 `verification-store.php` instead reads one `request.json` per username under `dirname(__DIR__) . '/verifications/requests/{username}/'` (under the served adoption tree). Therefore B2's admin queue will not read B1's new records. Do not point the intake back at the served-tree path; the B1/B2 owners must agree on a private external base and the per-request schema, then test create/list/approve/reject together without changing the dashboard response contract.
3. **High — required private-storage provisioning.** This branch intentionally has no account/runtime/test-account/verification fallback under the web root. The staging/production operator must provision owner-only external directories/files through server configuration before authentication or verification intake can be used. Apache `.htaccess`, direct-static-file denial, and rewrite behavior also require controlled staging verification; no real account or PII should be stored before those checks.
4. **High — admin identity bridge.** WEB-02's dashboard login/admin-token contract remains separate from WEB-04's `pc_account` registry and admin role. WEB-04 has a CLI-only trusted role provisioner but no browser role assignment or dashboard-token bridge. Coordinate with WEB-02 before enabling shared admin review/moderation workflows. WEB-02's report also flags hosting-level protection of other private admin files as unverified.
5. **Medium — listing media upload and C5/C6 evidence.** No image/video upload control was added to the existing listing form. WEB-05's current report identifies the upload/storage integration as unresolved. The C5 and C6 completion packages are absent from the package inventory; do not infer their behavior from the other historical records. The shared listing validator's `mb_strlen()` portability fix is applied through `rh_text_length()`; no successful merchant-publish request was run against a live account.
6. **Medium — stale `/rehome/` links.** WEB-03's current report records 53 anchor references across 18 crawled routes and local 404 targets for the retired `/rehome/` path; the adoption project summary also says the route was retired. The current WEB brief uses `/rehoming/` and `/signin/`. WEB-04 fixes its own auth-prompt signup destination, but sitewide link remediation belongs to WEB-03/coordinator review.
7. **Medium — verification upload hosting controls and retention.** The pre-account verification endpoint preserves its form contract and now uses explicit external private storage, but privacy/retention of the lightweight review record still requires an owner decision. Application-level IP throttling occurs after the web server receives the request body; configure hosting request-size and request-rate controls as well.
8. **Medium — profile-to-enquiry sharing is intentionally absent.** The private profile is not automatically attached to first messages. This avoids sharing sensitive fields without a clear consent/data contract; coordinate any future B5-style feature with the Rehoming owner before implementation.
9. **Low — shared chrome contrast.** Earlier full-page axe scans flagged contrast in shared navigation/footer/cookie-consent elements outside targeted WEB-04 content. Axe was not rerun after the final profile loading update; route shared-component findings to the relevant sitewide owner and rerun against the release version.
10. **Access observation — deployment metadata.** The authorized GitHub deployment/webhook metadata request returned HTTP 403 in this environment; no settings were changed and no elevated access was sought. No tracked `.github/workflows` files were found. The current WEB brief explicitly treats this as non-blocking; the account proceeded only on its assigned branch.

No production credentials or protected runtime data were committed. The changed E5 data directory contains only its `.htaccess`, and the modified/untracked path set contained no JSON data file. `TODO.md` was not edited because current accounts cannot push to `main`; the coordinator should route the actionable findings through the authorized shared-inbox/index workflow.

## Coordinator reminder: backups, rollback, and release controls

Before any future production publication, create and verify **both** (a) a repository backup, preferably a Git bundle stored outside the repository with a checksum, and (b) a Hostinger backup covering website files, database/export where applicable, `.htaccess` and hosting configuration, securely stored environment values, and protected runtime data. Verify the backups are non-empty and recoverable, record the last known-good commit and rollback procedure, and obtain the user's explicit approval for the exact release commit and target. WEB-04 created no backup and performed no Hostinger operation. No account may deploy, upload, or trigger an automatic Hostinger deployment.

## Commits and branch

- **Branch:** `qa/rehoming-auth-backend`
- **Synchronized starting base:** `a50c06109dd82a7bdadc8aa8b0e6d9bfdea8061b`
- **Backend/security implementation:** `b8557b3edf61b4f2fb6b312483970c23f34cb60e`
- **Gateway/route implementation:** `6e287c4fe4267a10febdf89eec2d75886a6a9c87`
- **Corrective audit, gateway tests, and report update:** `f4a8bb51f1e7512ef68e337d85faa4258c3302c9` (pushed normally to the assigned branch on 2026-11-13).
- This corrective commit is the current source/test commit; any later report-only update will be a descendant. The delivery message records the final remote branch tip.
- **Publication:** The assigned GitHub branch was pushed normally (non-force). No push to `main` and no manual Hostinger deployment/upload/configuration/trigger action occurred. Deployment metadata remained inaccessible (HTTP 403); no tracked `.github/workflows` files were found.


## Live deployment gap audit — 2026-11-11

This audit used credential-free public GET/HEAD requests against `https://adopt.petcenterco.com`; no login, OAuth flow, form submission, upload, or data mutation was attempted.

- `GET /signin/` returned HTTP 200 but still rendered the legacy **“Coming Soon — List Your Pet for Adoption”** page, not the WEB-04 login/signup gateway. The live response SHA-256 is `5fa7a0e22d21cd64b2cc1e4ca3883e6f43488b82c390df2582e6c35de8a9422e`, exactly matching both `origin/adopt-deploy:signin/index.html` and `origin/main:adopt/signin/index.html`. This ties the visible placeholder to the fetched deployment/source snapshots.
- The following expected WEB-04 file URLs returned HTTP 404: `/assets/js/web04-csrf.js`, `/rehoming/auth.php`, `/rehoming/web04-auth-core.php`, `/rehoming/google-oauth.php`, `/rehoming/google-oauth-callback.php`, `/account/settings/index.php`, `/merchant/index.php`, `/rehoming/list-a-pet/index.php`, `/privacy-policy/index.php`, and `/terms-of-service/index.php`. The corresponding gateway/API and PHP wrapper files are also absent from the fetched `origin/adopt-deploy` tree.
- The static routes `/account/settings/`, `/merchant/`, and `/rehoming/list-a-pet/` returned HTTP 200 while their PHP wrappers returned 404. `/privacy-policy/` and `/terms-of-service/` returned 404. HEAD probes of `/rehoming/merchant-publish-listing.php`, `/rehoming/merchant-chat-send.php`, and `/verify/verify-submit.php` returned HTTP 405, indicating method-restricted PHP endpoints are present; no mutation was attempted.

### Branch state and exact path comparison

Fetched refs at audit time:

- `origin/main`: `0786a3fab7bb60012b01549614dbfee5bdbeda4b` (`Merge task MANUS-09-env-audit`).
- `origin/adopt-deploy`: `3f7f03f4086942de0b609fd45c212e4c8597d32d` (`Revert "Deploy: publish main security and chat configuration fixes"`).
- `origin/qa/rehoming-auth-backend`: `60c7bf094970d3afd40d9cb0a40cbad155b84701`.

The local `main` branch is `42228b6` and is 51 commits behind fetched `origin/main`; no local `adopt-deploy` branch was created. The current checkout is still the assigned QA branch and has eight modified tracked files plus untracked `tests/web04/` artifacts. The corrective changes in that worktree are not part of the fetched QA tip.

`adopt-deploy` is a flattened website-root snapshot, while canonical `main` stores the website under `adopt/`. Comparing all 2,560 files in `origin/main:adopt/` to the flattened deploy root found no missing paths, 13 stale content paths, and one deploy-only `.gitignore`. The 13 stale paths are:

- `admin/.htaccess`, `admin/chat-config.php`, `admin/chat-migrate.php`, `admin/chat-session-token-setup.php`, `admin/chat-setup.php`, and `admin/verification-mail.php`;
- `pets/aquariums/index.html`, `pets/birds/index.html`, `pets/cats/index.html`, `pets/dogs/index.html`, `pets/livestocks/index.html`, `pets/reptiles/index.html`, and `pets/small-mammals/index.html`.

The WEB-04 code is not in current `origin/main`; it remains on the assigned QA branch. Flattening current `main` alone would therefore leave `/signin/` as the same placeholder and would not add the auth API, OIDC handlers, or role wrappers. Comparing the WEB-04 QA adoption tree, flattened to the live root, against `origin/adopt-deploy` found 14 absent and 16 stale paths. The 14 absent paths are:

- `account/settings/.htaccess`, `account/settings/index.php`, `admin/test-accounts-data/.htaccess`, `assets/js/web04-csrf.js`;
- `merchant/.htaccess`, `merchant/index.php`, `privacy-policy/index.php`;
- `rehoming/auth.php`, `rehoming/google-oauth.php`, `rehoming/google-oauth-callback.php`, `rehoming/web04-auth-core.php`, `rehoming/list-a-pet/.htaccess`, `rehoming/list-a-pet/index.php`;
- `terms-of-service/index.php`.

The 16 stale paths are `account/settings/index.html`, `assets/js/show-auth-prompt.js`, `rehoming/account-lifecycle.php`, `rehoming/list-a-pet/index.html`, `rehoming/merchant-chat-poll.php`, `rehoming/merchant-chat-send.php`, `rehoming/merchant-chat-start.php`, `rehoming/merchant-chat-threads.php`, `rehoming/merchant-platform-lib.php`, `rehoming/merchant-publish-listing.php`, `rehoming/merchant-review-moderate.php`, `rehoming/merchant-reviews.php`, `rehoming/runtime/.htaccess`, `signin/index.html`, `verify/index.html`, and `verify/verify-submit.php`.

A raw Git merge is not the deployment procedure: it would not flatten `main/adopt/` to the live document root. The documented publication process is a reviewed path-mapped refresh of the `adopt/` subtree. The QA-to-current-main merge preview also has content conflicts in `adopt/signin/index.html` and `adopt/rehoming/list-a-pet/index.html`; those pages need an explicit integration review before a candidate can be called complete.

### Release blockers; no production action taken

The current main-to-deploy differences are not safe to copy as one unreviewed batch. The MANUS-09 environment audit and deployment reports say the six admin/chat files above are an environment-only database-configuration refactor. Publishing that refactor before `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and related host values are provisioned would fail closed and could disable database-backed chat. The same reports state that a tracked administrative `.env` and previously committed credentials require rotation; no secret value was opened or reproduced in this audit.

WEB-04 also requires privately provisioned `APP_BASE_URL`, `SESSION_SECRET`, Google OAuth client values, and existing owner-only external `REHOMING_AUTH_DATA_DIR`, `REHOMING_RUNTIME_DIR`, and `VERIFY_STORAGE_DIR` directories. The current Hostinger environment and backups were not accessible or verified. The B1/B2 verification queue and E2 lifecycle storage contracts remain incompatible as described above. A release would also need a conflict-resolved, clean, frozen candidate and staging tests.

No commit was created and no branch was pushed during this audit. The public checks establish that the visible sign-in placeholder matches the fetched deploy/source file, but they do not establish the current Hostinger deployment trigger or full production filesystem state. Follow the Hostinger checklist: verify and retain repository and Hostinger backups, provision required configuration privately, complete controlled staging and cross-account checks, and approve the exact commit and target immediately before any production-triggering push.


## 2026-11-13 requirement-by-requirement and live recheck

The active source of scope is `docs/second-round/WEB-ACCOUNTS-OPERATIONAL-BRIEF.md` §9. The gateway/signup requirement is assigned to **WEB-04** in §§9.1–9.3; the page is `adopt/signin/index.html`. It is one `/signin/` gateway with login and account-creation tabs, rather than separate login/signup routes. The account is created after Google verifies an email; only staging has the separate test-login form. This explains why a pushed QA branch did not make signup visible on the website: the integration and deployment steps had not occurred.

| Active requirement | Source/test evidence | Remaining boundary |
|---|---|---|
| §9.1 entry points and public/protected route behavior | `/signin/`, public legal routes, visitor/merchant/listing wrappers, and the existing Rehoming API paths are present on WEB-04's QA source tree. | PHP/Apache rewrite and direct-static-file blocking are not proven by the static-browser test; deployment source does not include the WEB-04 wrappers yet. |
| §9.2 trusted roles and authorization | Server-side registry is authoritative; new Google identities are visitors; merchant/admin roles are provisioned server-side; listing access/publication requires verified merchant status; API ownership is derived/checked server-side. Regression suite asserts role assignment and role-aware return destinations. | WEB-02 admin-token bridge and the B1/B2 approval queue remain separate; no shared-admin production claim is made. |
| §9.3 gateway interface | Browser smoke passed for signup mode, keyboard tabs, 390×844 viewport fit, legal links, missing-Google status, service-error status, and server-side OAuth navigation. A non-2xx auth-status response now reaches the explicit service-error path rather than being mislabeled as ordinary missing configuration. | No assistive-technology session was available; source live regions and labels are checked, but screen-reader announcements are not claimed. |
| §9.4 server-side OIDC | Synthetic suite covers valid RS256 token plus bad issuer/audience/azp/expiry/nonce/email/signature/key, future/malformed `nbf`, PKCE S256, state mismatch/expiry/replay, and exact callback-URI configuration. | No Google Console credentials or real provider exchange were used. An expired authorization code is provider-enforced and was not exercised against Google; no live callback claim is made. |
| §§9.5–9.6 session and CSRF | Synthetic assertions cover same-origin valid-token acceptance, bad-token/cross-origin rejection, login/logout session rotation, failed token-revocation logout invalidation, and protected-role helpers. | The suite does not yet execute the full cookie/session/mutation matrix through Apache HTTP routes; host cookie flags and direct `.html` rules remain staging checks. |
| §9.7 legal routes | Gateway links to `/privacy-policy/` and `/terms-of-service/`; route target source is present and is included in the static assertions/browser smoke. | Legal owner review is still required before publication. |
| §9.8 test matrix | The repository now preserves `tests/web04/run-regressions.php` (78 assertions), `tests/web04/profile-loading-smoke.py`, and `tests/web04/gateway-smoke.py`, with commands and limitations in `tests/web04/README.md`. | Expired-session timing, full valid/invalid HTTP login/logout, route-level visitor/merchant/admin escalation, screen-reader use, and Apache direct-static denial remain incomplete as end-to-end tests. |

### Public deployment check (credential-free, 2026-11-13)

A fresh cache-busted navigation to `https://adopt.petcenterco.com/signin/?v=20261113-0256` returned HTTP 200 but still showed the legacy title **“List a Pet — Pet Center”** and **“Coming Soon — List Your Pet for Adoption”** content. The Sign Up links still pointed at `/signin/` without the account-creation mode. This is direct evidence that the change is not live, not a browser-cache explanation.

Fresh credential-free `HEAD` requests produced these public results:

- **404 (expected WEB-04 files not publicly reachable):** `/assets/js/web04-csrf.js`, `/rehoming/auth.php`, `/rehoming/web04-auth-core.php`, `/rehoming/google-oauth.php`, `/rehoming/google-oauth-callback.php`, `/account/settings/index.php`, `/merchant/index.php`, `/rehoming/list-a-pet/index.php`, `/privacy-policy/index.php`, `/terms-of-service/index.php`.
- **200 but still unwrapped/stale static route response:** `/account/settings/`, `/merchant/`, `/rehoming/list-a-pet/`.
- **405 on HEAD for method-restricted endpoints (not submitted or mutated):** `/rehoming/merchant-publish-listing.php`, `/rehoming/merchant-chat-send.php`, `/verify/verify-submit.php`.

Fetched refs at the initial Nov 13 live recheck were `origin/main=0786a3fab7bb60012b01549614dbfee5bdbeda4b`, `origin/adopt-deploy=3f7f03f4086942de0b609fd45c212e4c8597d32d`, and `origin/qa/rehoming-auth-backend=60c7bf094970d3afd40d9cb0a40cbad155b84701`. `main` contains no WEB-04 gateway; `adopt-deploy` is a flattened snapshot and currently contains the placeholder. After completing the checks, corrective source/tests/report were committed as `f4a8bb51f1e7512ef68e337d85faa4258c3302c9` and pushed only to `qa/rehoming-auth-backend`; the QA remote now points at that commit. The prior comparison records 14 WEB-04 source paths absent and 16 stale in the deploy tree relative to the QA implementation; see the exact path lists in the 2026-11-11 audit above. No `main`, `adopt-deploy`, Hostinger, or live data was modified by these checks.

### Integration/release conclusion

A merge of `main` into `adopt-deploy` by itself cannot publish this task: the gateway is on the WEB-04 QA branch, not `main`. `main` and `adopt-deploy` are separate, non-ancestral representations (nested `adopt/` source versus flattened live root), so publication must use a reviewed path-mapped integration candidate, not a raw Git merge. The candidate must also address the documented conflicts in sign-in/listing pages and the release blockers already recorded above. Before any production-triggering push, verify both repository and Hostinger backups, rotate/provision required secrets and storage safely, complete staging and cross-account checks, and approve the exact release commit and target. This report records findings; it does not authorize or claim a deployment.
