<?php
/**
 * chat-migrate.php
 *
 * Safe one-time migration script. Run this ONCE to add the ticket_closed
 * event type to your existing chat_events table without losing any data.
 * Delete this file after running.
 */
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);

require_once __DIR__ . '/chat-config.php';

// Migrations are an administrative operation; never expose them as a public URL.
chatRequireAdmin();

if (DB_NAME === 'REPLACE_WITH_YOUR_DB_NAME') {
    die("Set real DB credentials in chat-config.php first.\n");
}

$pdo = chatDb();
echo "Running chat migrations...\n\n";

$migrations = [
    "Add ticket_closed to chat_events ENUM" =>
        "ALTER TABLE chat_events MODIFY event_type ENUM('admin_joined','admin_left_done','ticket_closed') NOT NULL",

    /* TASK-admin234-1a-EXTRA START — MySQL does not support ADD COLUMN IF NOT EXISTS.
       The existing migration catch makes a plain ADD safe to rerun after duplicate-column errors. */
    "Add archived column to chat_threads" =>
        "ALTER TABLE chat_threads ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0",
    /* TASK-admin234-1a-EXTRA END */

    "Add 'bot' to chat_messages from_type ENUM" =>
        "ALTER TABLE chat_messages MODIFY from_type ENUM('user','admin','system','bot') NOT NULL",

    "Add chat_ratings table" =>
        "CREATE TABLE IF NOT EXISTS chat_ratings (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            chat_id       VARCHAR(20) NOT NULL,
            email         VARCHAR(255) NOT NULL,
            admin_id      INT NULL,
            admin_name    VARCHAR(255) NULL,
            rating        TINYINT NOT NULL,
            comment       TEXT NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (chat_id), INDEX (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // BUGFIX-1 START — discovered while integrating Task 8b (chat-typing.php /
    // chat-poll.php / chat-admin-poll.php / chat-widget.js / admin-dashboard.html):
    // those files now send/expect who='user_recording' and who='admin_recording'
    // in addition to the original 'user'/'admin' typing values, but chat_typing.who
    // is a strict MySQL ENUM. Without this migration, every recording-ping INSERT
    // against an existing database (i.e. every database that isn't brand new) either
    // throws (chatDb() uses PDO::ERRMODE_EXCEPTION) or gets silently coerced
    // depending on SQL mode — either way the "customer/admin is recording a voice
    // message..." indicator never actually works. Same fix pattern already used for
    // 'bot' being added to chat_messages.from_type above.
    "Widen chat_typing.who ENUM to include recording-ping values (Task 8b)" =>
        "ALTER TABLE chat_typing MODIFY who ENUM('user','admin','user_recording','admin_recording') NOT NULL",
    // BUGFIX-1 END

    /* TASK-admin234-1a START — MySQL/MariaDB-compatible transcript cache columns. */
    "Add transcript column to chat_messages (Admin Project 2 Tasks 1a/1b)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript TEXT NULL",
    "Add transcript_status column to chat_messages (Admin Project 2 Tasks 1a/1b)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_status ENUM('none','pending','done','failed') NOT NULL DEFAULT 'none'",
    "Add transcript_confidence column to chat_messages (Admin Project 2 Tasks 1a/1b)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_confidence DECIMAL(4,3) NULL",
    /* TASK-admin234-1b START — persist detected source language for read-aloud. */
    "Add transcript_language column to chat_messages (Admin Project 2 Task 1b)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_language VARCHAR(16) NULL",
    /* TASK-admin234-1b END */
    "Add transcript_started_at column to chat_messages (Admin Project 2 Tasks 1a/1b)" =>
        "ALTER TABLE chat_messages ADD COLUMN transcript_started_at TIMESTAMP NULL",
    /* TASK-admin234-1a END */
];

foreach ($migrations as $desc => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK]   $desc\n";
    } catch (PDOException $e) {
        echo "[SKIP] $desc — " . $e->getMessage() . "\n";
    }
}

// AP4-TASK-3: idempotent metadata check is portable across MySQL/MariaDB;
// don't use ADD COLUMN IF NOT EXISTS, which isn't supported by all MySQL versions.
try {
    $column = $pdo->query("SHOW COLUMNS FROM chat_threads LIKE 'preferred_chat_language'")->fetch(PDO::FETCH_ASSOC);
    if (!$column) {
        $pdo->exec('ALTER TABLE chat_threads ADD COLUMN preferred_chat_language VARCHAR(10) NULL');
        echo "[OK]   Add preferred_chat_language column to chat_threads\n";
    } else {
        echo "[SKIP] preferred_chat_language column already exists\n";
    }
} catch (PDOException $e) {
    echo "[FAIL] preferred_chat_language migration — " . $e->getMessage() . "\n";
}

echo "\nDone. Delete this file now.\n";
