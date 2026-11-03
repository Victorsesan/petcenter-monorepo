<?php
/**
 * Live Chat Widget — One-Time Database Setup
 *
 * Visit this file's URL ONCE after filling in real MySQL credentials in
 * chat-config.php. It creates every table this feature needs using
 * CREATE TABLE IF NOT EXISTS, so re-running it is safe and won't destroy
 * existing data.
 *
 * AFTER RUNNING SUCCESSFULLY: delete this file or move it outside the web
 * root. Leaving a setup script reachable indefinitely is unnecessary
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

    'chat_threads' => "
        CREATE TABLE IF NOT EXISTS chat_threads (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            chat_id             VARCHAR(20) UNIQUE NOT NULL,
            email               VARCHAR(255) NOT NULL,
            full_name           VARCHAR(255) NOT NULL,
            reason              VARCHAR(50),
            pet_context         JSON NULL,
            status              ENUM('waiting','active','done') DEFAULT 'waiting',
            assigned_admin_id   INT NULL,
            previous_chat_id    VARCHAR(20) NULL,
            created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            unread_for_admin    TINYINT(1) DEFAULT 1,
            unread_for_user     TINYINT(1) DEFAULT 0,
            archived            TINYINT(1) NOT NULL DEFAULT 0,
            preferred_chat_language VARCHAR(10) NULL, /* AP4-TASK-3 */
            INDEX (email), INDEX (status), INDEX (chat_id), INDEX (archived)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_messages' => "
        CREATE TABLE IF NOT EXISTS chat_messages (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            thread_id     INT NOT NULL,
            from_type     ENUM('user','admin','system','bot') NOT NULL,
            admin_id      INT NULL,
            msg_type      ENUM('text','image','audio','video') NOT NULL,
            body          TEXT NULL,
            media_path    VARCHAR(500) NULL,
            status        ENUM('sent','delivered','seen') DEFAULT 'sent',
            reply_to_id   INT NULL,
            reaction      VARCHAR(20) NULL,
            group_id      VARCHAR(40) NULL,
            edited_at     TIMESTAMP NULL,
            original_body TEXT NULL,
            deleted_at    TIMESTAMP NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE,
            INDEX (thread_id, created_at),
            INDEX (group_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_events' => "
        CREATE TABLE IF NOT EXISTS chat_events (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            thread_id     INT NOT NULL,
            event_type    ENUM('admin_joined','admin_left_done','ticket_closed') NOT NULL,
            admin_id      INT NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (thread_id) REFERENCES chat_threads(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_admin_profiles' => "
        CREATE TABLE IF NOT EXISTS chat_admin_profiles (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            name          VARCHAR(255) NOT NULL,
            position      VARCHAR(255) NOT NULL,
            photo_url     VARCHAR(500) NULL,
            blue_check    TINYINT(1) DEFAULT 0,
            active        TINYINT(1) DEFAULT 1,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_typing' => "
        CREATE TABLE IF NOT EXISTS chat_typing (
            thread_id     INT NOT NULL,
            /* BUGFIX-1 START — Task 8b's recording-in-progress ping (chat-typing.php,
               chat-poll.php, chat-admin-poll.php, chat-widget.js, admin-dashboard.html)
               sends who='user_recording'/'admin_recording' in addition to the original
               'user'/'admin' typing values. Widened here for any fresh install so the
               feature works out of the box; see chat-migrate.php for the matching ALTER
               that fixes this on a database that already has this table (that's every
               currently-deployed database, so the migration is the one that actually
               matters in practice — this CREATE TABLE only ever runs on a brand-new DB). */
            who           ENUM('user','admin','user_recording','admin_recording') NOT NULL,
            /* BUGFIX-1 END */
            expires_at    TIMESTAMP NOT NULL,
            PRIMARY KEY (thread_id, who)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_rate_limits' => "
        CREATE TABLE IF NOT EXISTS chat_rate_limits (
            rate_key      VARCHAR(255) PRIMARY KEY,
            attempts      INT DEFAULT 1,
            window_start  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_ratings' => "
        CREATE TABLE IF NOT EXISTS chat_ratings (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            chat_id       VARCHAR(20) NOT NULL,
            email         VARCHAR(255) NOT NULL,
            admin_id      INT NULL,
            admin_name    VARCHAR(255) NULL,
            rating        TINYINT NOT NULL COMMENT '1=bad 2=poor 3=okay 4=good 5=perfect',
            comment       TEXT NULL,
            created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (chat_id), INDEX (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'chat_drafts' => "
        CREATE TABLE IF NOT EXISTS chat_drafts (
            draft_key     VARCHAR(64) PRIMARY KEY,
            body          TEXT NOT NULL,
            updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

];

echo "Live Chat Widget — Database Setup\n";
echo "==================================\n\n";

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
    echo "All tables created successfully (or already existed).\n";
    echo "Next: delete this file, or move it outside the web root.\n";
} else {
    echo "One or more tables failed. Fix the error above and reload this page — it is safe to re-run.\n";
}
