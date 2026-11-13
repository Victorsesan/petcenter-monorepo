<?php
/**
 * chat-session-token-setup.php
 *
 * One-time database setup for Task 1 (Session-Token Base Layer). Creates
 * the two new tables includes/chat-auth.php depends on:
 *   - chat_session_token  (the Shared Contract's table, verbatim shape)
 *   - chat_auth_audit_log (this task's own audit-log table backing
 *     log_audit() — not part of the published Shared Contract tables,
 *     since no shape for it was pre-published; this is Task 1's own
 *     implementation detail, and its column shape is documented inline
 *     below for whoever builds Task 7's badge against it)
 *
 * Same exact pattern as the existing chat-setup.php: CREATE TABLE IF NOT
 * EXISTS everywhere, so re-running this is always safe and never destroys
 * data. Run this file once after the server-side DB environment variables
 * have been provisioned, same as chat-setup.php itself.
 *
 * A separate new file rather than adding to chat-setup.php directly,
 * because chat-setup.php was not one of the 12 files in this task's scope
 * (Scope Lock, rule 8b) — flagged as a deliberate, necessary addition in
 * Task-1-Completion.md rather than a silent extra file.
 *
 * AFTER RUNNING SUCCESSFULLY: delete this file or move it outside the web
 * root, for the same reason chat-setup.php/chat-migrate.php already give —
 * leaving a schema-creation script reachable indefinitely is unnecessary
 * surface area once the tables exist.
 */

error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/chat-config.php';

// Schema setup is an administrative operation; never expose it as a public URL.
chatRequireAdmin();

if (DB_NAME === '' || DB_USER === '' || DB_PASSWORD === '') {
    die("Chat database environment variables are not configured.\n" .
        "Provision DB_HOST / DB_NAME / DB_USER / DB_PASSWORD on the server, then reload this page.\n");
}

$pdo = chatDb();

$statements = [

    // Shared Contract shape, verbatim: token_hash, principal_id, issued_at,
    // expires_at. principal_id is a chat_threads.chat_id (VARCHAR(20), same
    // width as chat_threads.chat_id itself) in this project. No revoked_at
    // column by design — invalidate_all_tokens() deletes rows outright
    // rather than flagging them, so a row's mere unexpired existence IS its
    // validity; see includes/chat-auth.php's own comment on this for the
    // full reasoning.
    'chat_session_token' => "
        CREATE TABLE IF NOT EXISTS chat_session_token (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            token_hash    CHAR(64) NOT NULL,
            principal_id  VARCHAR(20) NOT NULL,
            issued_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at    TIMESTAMP NOT NULL,
            INDEX (token_hash),
            INDEX (principal_id),
            INDEX (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    // Backs log_audit(principal_id, endpoint, result). One row per
    // verification attempt (issue/verify/invalidate, logged automatically
    // by includes/chat-auth.php) plus, going forward, one row per
    // auth-state change any later task chooses to log through the same
    // function (e.g. 'pin_set', 'password_set', 'locked') — this table is
    // intentionally generic, not scoped to token events only, since Task 7's
    // admin-facing auth-state badge is specified to read entirely from
    // log_audit()'s output with no logging of its own.
    'chat_auth_audit_log' => "
        CREATE TABLE IF NOT EXISTS chat_auth_audit_log (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            principal_id  VARCHAR(20) NOT NULL,
            endpoint      VARCHAR(100) NOT NULL,
            result        VARCHAR(50) NOT NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (principal_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    /* TASK-3 START — final shared auth shape. The PIN/password tier is
       mutually exclusive by contract; password fields reuse the same secret
       columns activated here rather than requiring a later schema rewrite. */
    'chat_auth' => "
        CREATE TABLE IF NOT EXISTS chat_auth (
            principal_id   VARCHAR(20) PRIMARY KEY,
            auth_tier      ENUM('none','pin','password') NOT NULL DEFAULT 'none',
            secret_hash    VARCHAR(255) NULL,
            salt           VARCHAR(64) NULL,
            failed_attempts INT NOT NULL DEFAULT 0,
            locked_until   DATETIME NULL,
            created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (auth_tier), INDEX (locked_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    /* TASK-5 START — remembered devices store only a one-way verifier, never
       the browser token itself. */
    'chat_remember_device' => "
        CREATE TABLE IF NOT EXISTS chat_remember_device (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            principal_id        VARCHAR(20) NOT NULL,
            device_fingerprint  CHAR(64) NOT NULL,
            label               VARCHAR(120) NOT NULL,
            last_used_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_chat_device (principal_id, device_fingerprint),
            INDEX (principal_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    /* TASK-4 START — close/lock settings are separate from credential
       throttling so a delayed lock cannot block lookup or recovery. */
    'chat_lock_settings' => "
        CREATE TABLE IF NOT EXISTS chat_lock_settings (
            principal_id     VARCHAR(20) PRIMARY KEY,
            delay_seconds    INT NOT NULL DEFAULT 300,
            aggressive       TINYINT(1) NOT NULL DEFAULT 0,
            last_closed_at   DATETIME NULL,
            locked_at        DATETIME NULL,
            updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (locked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    /* TASK-6 START — temporary reset credentials are single-use records with
       hashed secrets and a short expiry. */
    'chat_password_resets' => "
        CREATE TABLE IF NOT EXISTS chat_password_resets (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            principal_id   VARCHAR(20) NOT NULL,
            contact_email  VARCHAR(255) NOT NULL,
            temp_hash      VARCHAR(255) NOT NULL,
            expires_at     DATETIME NOT NULL,
            used_at        DATETIME NULL,
            created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (principal_id), INDEX (contact_email), INDEX (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    /* TASK-3 END */

];

echo "Live Chat — Session Token / Audit Log Setup (Task 1)\n";
echo "=====================================================\n\n";

$allOk = true;

foreach ($statements as $table => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK]   $table\n";
    } catch (PDOException $e) {
        $allOk = false;
        echo "[FAIL] $table — " . $e->getMessage() . "\n";
    }
}

echo "\n";

if ($allOk) {
    echo "Both tables created successfully (or already existed).\n";
    echo "Next: delete this file, or move it outside the web root.\n";
} else {
    echo "One or more tables failed. Fix the error above and reload this page — it is safe to re-run.\n";
}
