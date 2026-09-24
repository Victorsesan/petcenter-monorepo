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

if (DB_NAME === 'REPLACE_WITH_YOUR_DB_NAME') {
    die("Set real DB credentials in chat-config.php first.\n");
}

$pdo = chatDb();
echo "Running chat migrations...\n\n";

$migrations = [
    "Add ticket_closed to chat_events ENUM" =>
        "ALTER TABLE chat_events MODIFY event_type ENUM('admin_joined','admin_left_done','ticket_closed') NOT NULL",

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
];

foreach ($migrations as $desc => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK]   $desc\n";
    } catch (PDOException $e) {
        echo "[SKIP] $desc — " . $e->getMessage() . "\n";
    }
}

echo "\nDone. Delete this file now.\n";
