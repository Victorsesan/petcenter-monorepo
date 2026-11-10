# Admin Endpoint Audit — 2026-11-10

## Scope

This audit covered the first-party PHP handlers under `adopt/admin/`, the live flattened `adopt-deploy` publication, the public adoption/status browser flow, and the shared chat configuration consumers.

## Verified findings

- The three schema-maintenance handlers `chat-setup.php`, `chat-migrate.php`, and `chat-session-token-setup.php` return HTTP 401 before database work when no admin token is supplied.
- The public adoption browser flow advanced through the Applicant, Living, and Pets & Care steps using synthetic values without submitting a production application.
- The unknown adoption-status lookup returned the expected generic not-found response.
- Fifty-five first-party admin PHP files lint clean on the source branch.
- Every active PHP consumer that requires `chat-config.php` remains compatible with the new environment-backed constants; no consumer defines duplicate `DB_*` constants.
- A configured environment smoke test populated `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `DB_CHARSET` correctly.
- Missing DB configuration fails closed with a generic error and does not print the former credential or database identifiers.

## Safe fixes prepared

1. `adopt/admin/chat-config.php` no longer contains a database password. DB settings are read from server-side environment variables and missing required values fail closed.
2. The active chat setup/migration scripts now validate the server-side DB variables instead of checking obsolete source placeholders.
3. `adopt/admin/verification-mail.php` now loads the existing sibling SMTP configuration at `../contact-us/contact-config.php`.
4. `adopt/admin/.htaccess` blocks direct HTTP access to internal configuration/library/maintenance scripts while preserving PHP includes.

## Deployment boundary

The DB configuration refactor must not be published until the hosting environment contains `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. Publishing first would correctly fail closed, but could disable database-backed chat. The existing production credential must be treated as compromised and rotated through the credential-rotation runbook; no secret values are recorded here.

After provisioning, the cutover test must cover admin authentication, chat creation, message send/poll, auth-gate, lock/unlock, password reset, admin list access, one database-backed audit-log write, and controlled SMTP delivery. Only then should the old DB and admin credentials be revoked.
