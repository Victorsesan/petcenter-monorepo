-- PetCenter live-chat security schema
-- Derived exactly from adopt/admin/chat-setup.php and
-- adopt/admin/chat-session-token-setup.php.
--
-- Run this while connected to the intended application database.
-- All statements are CREATE TABLE IF NOT EXISTS and do not delete data.
-- DDL may implicitly commit in MySQL; take a backup before production use.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS chat_session_token (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    token_hash    CHAR(64) NOT NULL,
    principal_id  VARCHAR(20) NOT NULL,
    issued_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at    TIMESTAMP NOT NULL,
    INDEX (token_hash),
    INDEX (principal_id),
    INDEX (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_auth_audit_log (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    principal_id  VARCHAR(20) NOT NULL,
    endpoint      VARCHAR(100) NOT NULL,
    result        VARCHAR(50) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (principal_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_auth (
    principal_id    VARCHAR(20) PRIMARY KEY,
    auth_tier       ENUM('none','pin','password') NOT NULL DEFAULT 'none',
    secret_hash     VARCHAR(255) NULL,
    salt            VARCHAR(64) NULL,
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until    DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (auth_tier),
    INDEX (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_remember_device (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    principal_id       VARCHAR(20) NOT NULL,
    device_fingerprint CHAR(64) NOT NULL,
    label              VARCHAR(120) NOT NULL,
    last_used_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chat_device (principal_id, device_fingerprint),
    INDEX (principal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_lock_settings (
    principal_id   VARCHAR(20) PRIMARY KEY,
    delay_seconds  INT NOT NULL DEFAULT 300,
    aggressive     TINYINT(1) NOT NULL DEFAULT 0,
    last_closed_at DATETIME NULL,
    locked_at      DATETIME NULL,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (locked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_password_resets (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    principal_id VARCHAR(20) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    temp_hash    VARCHAR(255) NOT NULL,
    expires_at   DATETIME NOT NULL,
    used_at      DATETIME NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (principal_id),
    INDEX (contact_email),
    INDEX (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional post-migration verification:
-- SHOW TABLES LIKE 'chat_%';
-- DESCRIBE chat_session_token;
-- DESCRIBE chat_auth_audit_log;
-- DESCRIBE chat_auth;
-- DESCRIBE chat_remember_device;
-- DESCRIBE chat_lock_settings;
-- DESCRIBE chat_password_resets;
