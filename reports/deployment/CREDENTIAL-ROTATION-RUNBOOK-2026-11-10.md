# PetCenter Production Credential-Rotation Runbook

**Status:** Required follow-up security operation  
**Scope:** `adopt/admin/.env`, `adopt/admin/chat-config.php`, and SMTP credentials.

## Current findings

- `adopt/admin/.env` is tracked and is read by multiple admin endpoints for `ADMIN_TOKEN`.
- `adopt/admin/chat-config.php` currently defines the chat database host, name, user, and password in PHP source.
- `admin/contact-config.php` and `contact-us/contact-config.php` already read `SMTP_PASSWORD` from the process environment.
- Do not publish replacement code until the hosting-side replacement secrets have been provisioned; otherwise chat or admin operations can fail closed.

## Phase 1 — Prepare and inventory

1. Restrict access to the repository and deployment dashboard while rotating. Treat the current admin token, database password, and any previously committed SMTP password as compromised.
2. Record the current deployment commit and confirm the rollback target. Do not copy secret values into an issue, commit, report, shell transcript, or chat message.
3. Identify every consumer of `ADMIN_TOKEN`; the current repository includes admin authentication, upload, pet, application, verification, and chat-management endpoints. Update all consumers together or retain one compatibility loader during the cutover.
4. Confirm the hosting provider supports server environment variables or a server-only file outside the publicly served document root. Prefer process environment variables; if a file is required, keep it outside the web root with owner-only permissions such as `0600`.

## Phase 2 — Generate and provision replacements

1. Generate a new high-entropy `ADMIN_TOKEN` with a password manager or a cryptographically secure generator. Do not reuse or derive it from the old token.
2. Create a new least-privilege chat database user if Hostinger permits it. Grant only the required privileges on the chat database. A new user enables a safer overlap period than changing the password of the existing user in place.
3. Generate a new database password and provision these server-side variables:

   ```text
   ADMIN_TOKEN=<new random admin token>
   DB_HOST=localhost
   DB_NAME=<chat database name>
   DB_USER=<new least-privilege chat user>
   DB_PASSWORD=<new database password>
   SMTP_PASSWORD=<new SMTP password>
   ```

4. Rotate the SMTP mailbox password in Hostinger, then provision the new `SMTP_PASSWORD` value in the hosting environment. Keep the old SMTP credential available only until delivery verification succeeds.
5. If the host cannot provide environment variables, create a server-only secret file outside the public web root and configure the application to read it. Never add that file to Git.

## Phase 3 — Refactor configuration before cutover

1. Change `chat-config.php` to read `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` from the process environment or the server-only secret loader.
2. Validate that all four values are present and non-empty. Fail closed with a generic server error if any are absent; never fall back to a source-code password.
3. Keep `DB_CHARSET` as a non-secret constant or environment value.
4. Replace the repeated `.env` parsing for `ADMIN_TOKEN` with one shared server-only loader if practical. It must trim one value, reject an empty value, and use constant-time comparison for supplied tokens.
5. Add a tracked `.env.example` containing variable names only, with placeholder values. Add `.env` and any server-secret filename to `.gitignore`.
6. Remove the tracked production `.env` from the repository and remove hardcoded credentials from the entire Git history using an approved history-rewrite procedure. Coordinate force-push and redeployment; rotating the live secret without removing historical exposure is incomplete.

## Phase 4 — Safe cutover

1. Deploy the configuration refactor while the new server-side secrets are already present.
2. Test authenticated admin access with the new token before revoking the old token. Test chat creation, message send/poll, auth-gate, lock/unlock, password reset email capture, and admin list access.
3. Test a database-backed request and confirm that the audit query can read `chat_auth_audit_log`.
4. Test one SMTP message using a controlled recipient or provider sandbox. Confirm the message is accepted and no secret appears in logs.
5. Revoke the old database user/password, old SMTP password, and old admin token only after the new-path checks pass.
6. Update all trusted admin clients or deployment secrets that used the old `ADMIN_TOKEN`. Do not leave the old token in browser local storage, CI variables, shell history, or documentation.

## Phase 5 — Post-rotation verification

- Search the working tree and Git history for credential-shaped values and confirm no production secret remains.
- Request the former admin token against a harmless protected endpoint and confirm rejection.
- Confirm the new admin token succeeds.
- Confirm chat database reads/writes, audit-log writes, password reset mail, and SMTP delivery.
- Check web-server logs for configuration errors, authentication failures, and unexpected database connection failures.
- Confirm secret files are not downloadable over HTTP and have owner-only permissions.
- Record only commit IDs, timestamps, endpoint status, and pass/fail results in the handoff report; never record secret values.

## Rollback plan

If the new configuration fails before the old credentials are revoked, restore the previous application commit and keep the old credentials temporarily active while correcting the secret loader. If the old credentials have already been revoked, do not restore source code that expects them; fix the new server-side variables or provision a new temporary least-privilege database user. After recovery, rotate any credential that appeared in logs or diagnostic output.

This runbook is intentionally separate from the widget deployment. The widget correction is live; credential rotation requires hosting-side secret provisioning and should be scheduled as its own controlled production change.
