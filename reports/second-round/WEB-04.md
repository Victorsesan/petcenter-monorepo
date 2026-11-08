# Second-Round Report — WEB-04

**Date:** 2026-11-08 11:03 UTC+01:00
**Account:** `WEB-04`
**Assignment:** Rehoming authentication gateway and backend
**Assigned branch:** `qa/rehoming-auth-backend`
**Status:** Code-level implementation and isolated verification complete; provider/hosting and cross-account integration gates remain open.

## Assignment

Implemented the current WEB-04 scope: a usable sign-in/sign-up gateway, server-side Google OpenID Connect flow, server-authoritative accounts and roles, protected visitor/merchant routes, private adopter-profile persistence, session and CSRF safeguards, and consistent authorization for Rehoming mutations. Existing routes and API payload/response shapes were preserved where modified. No manual Hostinger deployment, upload, configuration change, production request, or Hostinger-specific deployment-trigger action was performed. The authorized GitHub push was limited to `qa/rehoming-auth-backend`; GitHub deployment-metadata access returned HTTP 403, so remote Hostinger integration settings could not be inspected.

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
| Protected routes | Implemented; Apache behavior needs staging verification | PHP wrappers/`.htaccess` rules protect `/merchant/`, `/account/settings/`, and `/rehoming/list-a-pet/`. The PHP built-in server ignores Apache rewrite/access rules, so direct-static-file denial and rewrite behavior were not certified. |
| Adopter profile | Implemented and browser-tested | Existing field names/values are persisted server-side per account. The custom dropdowns support keyboard/ARIA state and RTL-direction smoke testing. The profile is not silently shared with merchants or attached to first enquiries. |
| Rehoming API authorization | Implemented; integration review remains | Listing publish, chat, review, moderation, and lifecycle routes now use server-side roles and CSRF where cookie-authenticated. Existing JSON shapes were retained. |
| Verification submission | Hardened, with hosting-dependent residual risk | Existing pre-account form and response flow remain. CSRF, per-email/IP and per-IP throttling, upload validation, fail-closed private directory setup, cleanup and Apache deny rules were added. Actual hosting enforcement and moving PII storage outside the public root remain release checks. |
| Public legal routes | Implemented | `/privacy-policy/` gives a factual account-data supplement linking the canonical policy; `/terms-of-service/` aliases the approved canonical terms without inventing legal text. |
| Deployment readiness | Not approved | No production secrets, Apache configuration, Google Console client, or Hostinger behavior was available to validate. This branch is not a release approval. |

### Routes and data-flow map

- `GET /signin/` is the gateway; `?mode=signup` selects the account-creation tab. The shared auth prompt's default signup destination now targets this route.
- `GET /rehoming/auth.php?action=status|csrf|profile` exposes safe session state, a CSRF token, and the current visitor profile. `POST` handles logout and validated profile updates.
- `GET /rehoming/google-oauth.php` starts Google authorization; `GET /rehoming/google-oauth-callback.php` consumes one-time state and completes the server-side exchange. The OAuth-start limiter allows ten starts per IP per 900 seconds; the 11th synthetic request was throttled.
- `/merchant/`, `/account/settings/`, and `/rehoming/list-a-pet/` preserve their routes while passing through role-specific PHP wrappers. Legacy direct `index.html` access is covered by Apache rules, subject to the staging verification noted above.
- Cookie-authenticated mutations for listing publication, chat, reviews, lifecycle, and `/verify/verify-submit.php` require the shared same-origin CSRF wrapper/token. Existing bearer-token requests remain a separate non-ambient authentication path.
- The verification form remains pre-account. No new listing image/video upload path was introduced.

### Files inspected

Before and during implementation, inspected the current auth prompt and sign-in page; account settings and listing form; Rehoming platform/session/store helpers, lifecycle, publish, chat, review and moderation endpoints; verification form and upload endpoint; runtime `.htaccess` and `.gitignore`; E5 test-account and WEB-02 verification-admin sources; existing admin authentication/dashboard paths; PHPUnit configuration and existing endpoint tests; and the relevant route/legal pages. Cross-account reviews covered the current WEB-02, WEB-03 and WEB-05 reports and changed-path summaries.

### Files added or modified

No files were deleted or moved. No application data, JSON account file, credential, or runtime record was added.

**Modified (17):** `.gitignore`; `adopt/account/settings/index.html`; `adopt/assets/js/show-auth-prompt.js`; `adopt/rehoming/account-lifecycle.php`; `adopt/rehoming/list-a-pet/index.html`; `adopt/rehoming/merchant-chat-poll.php`; `adopt/rehoming/merchant-chat-send.php`; `adopt/rehoming/merchant-chat-start.php`; `adopt/rehoming/merchant-chat-threads.php`; `adopt/rehoming/merchant-platform-lib.php`; `adopt/rehoming/merchant-publish-listing.php`; `adopt/rehoming/merchant-review-moderate.php`; `adopt/rehoming/merchant-reviews.php`; `adopt/rehoming/runtime/.htaccess`; `adopt/signin/index.html`; `adopt/verify/index.html`; `adopt/verify/verify-submit.php`.

**Added (15):** `adopt/account/settings/.htaccess`; `adopt/account/settings/index.php`; `adopt/admin/test-accounts-data/.htaccess`; `adopt/assets/js/web04-csrf.js`; `adopt/merchant/.htaccess`; `adopt/merchant/index.php`; `adopt/privacy-policy/index.php`; `adopt/rehoming/auth.php`; `adopt/rehoming/google-oauth.php`; `adopt/rehoming/google-oauth-callback.php`; `adopt/rehoming/list-a-pet/.htaccess`; `adopt/rehoming/list-a-pet/index.php`; `adopt/rehoming/web04-auth-core.php`; `adopt/terms-of-service/index.php`; `tools/provision-rehoming-account.php`.

All 32 changed code/configuration paths contain WEB-04 task anchors. `git diff --check` passed and the conflict-marker scan found none.

## Tests performed

Tests used synthetic identities, generated short-lived test secrets/keys, an in-memory session handler, and private temporary directories under `/tmp`; temporary account fixtures were removed. No existing account, application, pet, upload, chat-media, production config, or log data was opened or changed.

- `php /tmp/test_web04_auth.php` — **65 isolated auth/security assertions passed**, covering safe/role-aware return paths, visitor-only public account creation, trusted provisioning, profile validation and persistence, CSRF/origin checks, OAuth state replay/expiry, generated RSA-signed ID-token verification and invalid claims/signatures, throttling, session rotation, and idle/absolute expiry.
- Synthetic E5 adapter test — **7 assertions passed**: explicit staging gate, hashed-password verification, invalid password rejection, pending merchant denial, verified merchant acceptance, admin test-account denial, and production-mode disablement. Test records lived only in `/tmp`.
- `python3 /tmp/test_web04_http.py` — loopback HTTP checks passed for signed-out route redirects, status/CSRF/profile behavior, disabled staging login, anonymous mutation denials, verification CSRF, review/moderation denial, local legal routes, and safe unconfigured-OAuth handling. No external authorization redirect was followed.
- `python3 /tmp/test_web04_browser.py` — serial Playwright checks passed for gateway login/signup state, legal links and not-configured message; 390×844 mobile overflow; visitor-profile listbox ArrowDown/Enter/Escape and ARIA updates; and an RTL-direction listbox smoke check. No JavaScript exceptions occurred; the signed-out profile API's handled 401 was expected. An initial concurrent browser run was timing-sensitive; the serial rerun passed.
- axe-core **4.14.0**, WCAG 2.1 A/AA: the gateway/settings main regions and expanded profile listbox had **zero violations**. The standalone privacy-page main region also had zero violations. Full-page scans still reported contrast findings in shared navigation/footer/cookie-consent components outside the WEB-04 main content; those shared components were not changed in this task.
- OAuth rate-limit check — repeated loopback starts were throttled after the configured threshold without contacting Google or printing state/nonce/challenge values.
- `php -l` passed for all 20 changed PHP files; `node --check` passed for both changed JavaScript files and the inline scripts in signin, settings, listing, and verification pages. `git diff --check` passed.
- PHPUnit was not run: neither a `phpunit` executable nor `vendor/bin/phpunit` is installed in this environment. The repository's existing tests were not modified.
- No Apache parser or Hostinger staging instance was available. `.htaccess`, direct-static-file denial, and rewrite behavior require a controlled Apache staging check. No full-site performance benchmark or live Google OAuth run was performed.

## Security, accessibility, RTL, and external research

The auth core rehydrates identity and roles from server-side records; the browser cannot assign roles. Google-created accounts are visitors only. OAuth state is single-use and short-lived; the callback verifies issuer, audience, authorized party, expiry/issued-at, nonce, verified email, key ID and RS256 signature. Return paths reject external, scheme-relative, encoded and traversal destinations. Account/profile/runtime writes use restrictive permissions and private-store checks. API errors avoid returning secrets or internal filesystem paths.

The gateway, profile controls, and privacy notice use accessible status/label semantics; the profile dropdowns have keyboard operation, selected-state announcements, and a visible focus treatment. Tested RTL coverage was a directionality smoke test for the dropdown, not a full translated RTL site review. The existing route, form field names, review/listing/chat JSON contracts, and verification multipart flow were retained.

Authoritative references used for design decisions:

- Google, [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server) and live public [Google OIDC signing keys](https://www.googleapis.com/oauth2/v3/certs).
- OpenID Foundation, [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html); IETF [RFC 9700 — Best Current Practice for OAuth 2.0 Security](https://www.rfc-editor.org/rfc/rfc9700.html).
- OWASP Cheat Sheets: [Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html), [CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html), [Unvalidated Redirects and Forwards](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html), [Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html), and [Authentication](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html).

## External configuration required before staging/release

- Set `APP_BASE_URL` to the canonical HTTPS adoption origin and `SESSION_SECRET` to a high-entropy value of at least 32 bytes in the server environment; do not place either value in the repository.
- Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI`. The latter must exactly equal `${APP_BASE_URL}/rehoming/google-oauth-callback.php` and be registered in the Google OAuth client. Validate authorized origins/redirects and consent settings in a controlled staging account.
- Keep `REHOMING_AUTH_DATA_DIR` and, if overriding the existing Rehoming store, `REHOMING_RUNTIME_DIR` in absolute, writable, non-symlink private directories outside the web root. If set, `PETCENTER_REHOMING_TOKEN_SECRET` must be at least 32 bytes; otherwise the existing library derives its token key from `SESSION_SECRET`.
- For staging-only E5 login, set both `APP_ENV=staging` and `WEB04_ENABLE_TEST_AUTH=1`; point `WEB04_TEST_ACCOUNTS_FILE` to the E5 test-account JSON file outside the web root. Do not enable test auth in production. The default E5 path remains under the public document tree and has a deny `.htaccess`; production must verify that Apache enforces it or configure an external private path.
- Runtime requires PHP 8.1+ with OpenSSL, cURL, and JSON support. This branch's current listing validator still calls `mb_strlen`; keep PHP `mbstring` enabled until WEB-05's portable `rh_text_length()` change is integrated.
- Apache must honor the route `.htaccess` files, rewrite/access-control directives, and deny rules. Validate them on staging; do not infer enforcement from the local PHP server.

## Unresolved issues and handoffs

1. **High — lifecycle storage integration.** `account-lifecycle.php` retains the historical E2 per-record/list/message path while the active Rehoming library uses flat JSON `listings`, `threads`, and `messages` stores. WEB-04 now gates lifecycle requests by server identity/role/CSRF and revokes bearer tokens, but it does not claim that legacy lifecycle operations update the active stores. Reconcile the E2 path with the WEB-05/platform owner and test suspend/delete/restore against isolated active-format stores before release.
2. **High — private data still depends on Apache enforcement.** Account, E5 test-account, and verification data paths have restrictive filesystem handling and deny rules, but some defaults remain beneath the public document root. Prefer external private storage and verify Apache `AllowOverride`/deny behavior in staging before storing real accounts or verification PII.
3. **High — admin identity bridge.** WEB-02's dashboard login/admin-token contract remains separate from WEB-04's `pc_account` registry and admin role. WEB-04 has a CLI-only trusted role provisioner but no browser role assignment or dashboard-token bridge. Coordinate with WEB-02 before enabling shared admin review/moderation workflows. WEB-02's report also flags hosting-level protection of other private admin files as unverified.
4. **Medium — WEB-05 shared publish-file overlap.** The active `origin/qa/rehoming-functional-integration` branch changes the same listing validator from `mb_strlen()` to the portable `rh_text_length()` helper. This WEB-04 working branch retains `mb_strlen()` because that exact line is owned/edited by WEB-05; the current sandbox has mbstring, but integration should preserve WEB-05's portability fix and avoid reverting it. No successful merchant-publish request was run against a live account.
5. **Medium — listing media upload and C5/C6 evidence.** No image/video upload control was added to the existing listing form. WEB-05's current report identifies the upload/storage integration as unresolved. The C5 and C6 completion packages are absent from the package inventory; do not infer their behavior from the other historical records.
6. **Medium — stale `/rehome/` links.** WEB-03's current report records 53 anchor references across 18 crawled routes and local 404 targets for the retired `/rehome/` path; the adoption project summary also says the route was retired. The current WEB brief uses `/rehoming/` and `/signin/`. WEB-04 fixes its own auth-prompt signup destination, but sitewide link remediation belongs to WEB-03/coordinator review.
7. **Medium — verification upload surface.** The pre-account verification endpoint retains its form contract and now fails closed if private-directory/deny-rule setup fails; however uploads/queue data still need a hosting-level privacy/retention review and preferably external storage. Application-level IP throttling occurs after the web server receives the request body; configure hosting request-size/rate controls as well.
8. **Medium — profile-to-enquiry sharing is intentionally absent.** The private profile is not automatically attached to first messages. This avoids sharing sensitive fields without a clear consent/data contract; coordinate any future B5-style feature with the Rehoming owner before implementation.
9. **Low — shared chrome contrast.** Full-page axe scans flag contrast in shared navigation/footer/cookie-consent elements even though targeted WEB-04 content passes. Route the shared-component findings to the relevant sitewide owner.
10. **Access observation — deployment metadata.** The authorized GitHub deployment/webhook metadata request returned HTTP 403 in this environment; no settings were changed and no elevated access was sought. No tracked `.github/workflows` files were found. The current WEB brief explicitly treats this as non-blocking; the account proceeded only on its assigned branch.

No production credentials or protected runtime data were committed. The changed E5 data directory contains only its `.htaccess`, and the modified/untracked path set contained no JSON data file. `TODO.md` was not edited because current accounts cannot push to `main`; the coordinator should route the actionable findings through the authorized shared-inbox/index workflow.

## Coordinator reminder: backups, rollback, and release controls

Before any future production publication, create and verify **both** (a) a repository backup, preferably a Git bundle stored outside the repository with a checksum, and (b) a Hostinger backup covering website files, database/export where applicable, `.htaccess` and hosting configuration, securely stored environment values, and protected runtime data. Verify the backups are non-empty and recoverable, record the last known-good commit and rollback procedure, and obtain the user's explicit approval for the exact release commit and target. WEB-04 created no backup and performed no Hostinger operation. No account may deploy, upload, or trigger an automatic Hostinger deployment.

## Commits and branch

- **Branch:** `qa/rehoming-auth-backend`
- **Synchronized starting base:** `a50c06109dd82a7bdadc8aa8b0e6d9bfdea8061b`
- **Backend/security implementation:** `b8557b3edf61b4f2fb6b312483970c23f34cb60e`
- **Gateway/route implementation (ending code commit):** `6e287c4fe4267a10febdf89eec2d75886a6a9c87`
- The report is published on the same assigned branch; the final branch tip after this clarification is recorded in the delivery message and Git history.
- **Publication:** The assigned GitHub branch was pushed normally (non-force). No push to `main` and no manual Hostinger deployment/upload/configuration/trigger action occurred. Deployment metadata remained inaccessible (HTTP 403); no tracked `.github/workflows` files were found.
