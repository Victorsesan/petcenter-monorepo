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
 * data. Visit this file's URL once after chat-config.php has real (non-
 * placeholder) database credentials, same as chat-setup.php itself.
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

if (DB_NAME === 'REPLACE_WITH_YOUR_DB_NAME' || DB_USER === 'REPLACE_WITH_YOUR_DB_USER') {
    die("chat-config.php still has placeholder database credentials.\n" .
        "Open chat-config.php, replace DB_HOST / DB_NAME / DB_USER / DB_PASSWORD with your real Hostinger MySQL details, then reload this page.\n");
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
