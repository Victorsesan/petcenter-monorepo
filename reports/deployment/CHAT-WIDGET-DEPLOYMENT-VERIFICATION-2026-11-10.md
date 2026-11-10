# PetCenter Chat and Accessibility Widget Deployment Verification

**Date:** 2026-11-10  
**Scope:** Public adoption-site widget integration, live asset verification, admin-endpoint health probes, and static PHP security review.

## Executive summary

The live pages were not consistently using the same widget composition as the current deployment source. Fifteen public templates still contained the former inline `pc-settings-widget` implementation alongside the shared accessibility asset. Several other public templates loaded only one of the shared widgets. That created duplicate accessibility controls on some routes and missing controls on others.

The correction removes the legacy inline settings widget from 15 public templates and normalizes the public HTML templates so each page loads exactly one copy of:

- `/admin/chat-widget.js`
- `/assets/js/accessibility-widget.js` with `defer`

The admin dashboard was intentionally excluded because it has its own internal live-chat UI rather than the customer-facing launcher.

## Files and behavior changed

- 53 public HTML templates were normalized.
- 15 legacy inline accessibility/settings blocks were removed.
- The shared widget tags were placed immediately before each public document's closing `</body>` tag.
- Missing body closures caused by the old block owning the document close were restored.
- No PHP endpoint, chat-auth implementation, database schema, or admin dashboard behavior was changed by this widget correction.

## Validation completed

- `node --check adopt/admin/chat-widget.js`: passed.
- `node --check adopt/assets/js/accessibility-widget.js`: passed.
- PHP lint across all 51 files in `adopt/admin`: passed with zero failures.
- Public-template structural validation: 53 pages checked; zero pages missing a valid closing `</body>`/`</html>` pair; zero pages with duplicate or missing shared widget script tags.
- Legacy-widget scan: zero remaining public HTML files contain `pc-settings-widget` or `pcSettingsWidget`.
- `git diff --check`: passed.

## Live verification

The following production assets returned HTTP 200:

- `/admin/chat-widget.js`
- `/assets/js/accessibility-widget.js`

The following protected endpoints returned HTTP 405 to unauthenticated GET probes, confirming that they are not serving an unauthenticated success response:

- `/admin/chat-auth-gate.php`
- `/admin/chat-lock.php`
- `/admin/chat-password-reset.php`

An authenticated admin-list health probe returned `success: true` and an empty thread list. This confirms the endpoint and its audit-backed list query are reachable. The response contained no thread rows, so no individual `auth_badge` row was available to inspect in that probe. No claim is made here that a specific `chat_auth_audit_log` row was enumerated through the public admin-list response.

## Security-scan findings

The static scan did not find PHP shell-command execution in the flagged files. The reported `PDO::exec()` calls are database schema statements, and the reported `curl_exec()` call is the outbound transcription HTTP request. The reviewed upload endpoints perform token/session checks and MIME validation before moving uploaded files.

Two credential-management issues remain high priority for production operations and should be handled as a separate credential-rotation change, not silently bundled into a widget-only deploy:

1. The repository tracks `adopt/admin/.env`, which contains an administrative token.
2. `adopt/admin/chat-config.php` contains a database password in source rather than reading it from a server-side secret.

The correct remediation is to rotate the affected administrative/database credentials, provision the replacements in the hosting environment or an untracked server-only secret file, remove the tracked secret material from the repository history, and then change the PHP configuration to fail closed when the required environment variables are absent. Deploying that refactor before provisioning replacement secrets would interrupt chat and admin operations, so it is not included in this widget publication.

The SMTP configurations already read `SMTP_PASSWORD` from the process environment; production should still be checked to ensure that variable is actually provisioned and that the old credential has been rotated.

## Deployment and rollback

Publish the widget correction from the integration branch into the production deployment branch only after reviewing the final diff. The rollback point is the previous deployment commit. If a page renders without either shared widget after publication, revert the widget commit and inspect the host's HTML-cache/deploy status before retrying.

After publication, verify at minimum:

1. Homepage, pets index, pet detail, application, services, contact, rehoming, and testimonials routes.
2. One nested route and one duplicate/legacy route such as `index8.html`.
3. Exactly one visible customer chat launcher and one accessibility control per public page.
4. No `pcSettingsWidget`/`pc-settings-widget` marker in the served HTML.
5. Protected chat endpoints still reject unauthenticated requests.

## Handoff

The widget integration is complete and mechanically validated. Credential rotation and secret removal are a separate high-priority production-security operation requiring hosting-side secret provisioning before code publication. 
