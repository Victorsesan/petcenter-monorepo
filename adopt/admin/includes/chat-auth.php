<?php
/**
 * includes/chat-auth.php
 *
 * Session-token authentication layer for the live chat system.
 * Admin Project #1 — Task 1: Session-Token Base Layer + Endpoint Hardening.
 *
 * THE PROBLEM THIS CLOSES: reopening a chat used to work on email + chat ID
 * alone, and neither was ever treated as a secret — both are guessable or
 * enumerable, so either one leaking was enough to expose a full
 * conversation. From this file forward, email + chat ID is a LOOKUP
 * mechanism only (chat-start.php still uses it to find/create a thread) —
 * it is never, by itself, sufficient to read or write anything in an
 * EXISTING thread. Every read/write endpoint that touches an existing
 * thread now requires a valid session token proving the caller already
 * completed that lookup once and was issued a token for it.
 *
 * principal_id, throughout this file and every caller of it, is always a
 * chat_threads.chat_id (one anonymous chat conversation) — see the "Reuse
 * notes for future adopters" in the Shared Contracts section of
 * Admin-Project-1-Worker-Task-Assignments.md for how a system with
 * persistent accounts should reinterpret principal_id instead.
 *
 * Depends on chat-config.php already being require_once'd by the caller
 * (chatDb(), chatRespond(), etc.) — every chat-*.php endpoint already does
 * this first, per the project's existing convention (rule 7: don't
 * reinvent what already exists).
 *
 * Function signatures below match the Shared Contract exactly:
 *   issue_token(principal_id): token_string
 *   verify_token(token_string, principal_id): bool
 *   invalidate_all_tokens(principal_id): void
 *   log_audit(principal_id, endpoint, result): void
 * check_pin_throttle() is implemented below by Task 3 and is shared by the
 * PIN and password gate. It uses bounded progressive delays, not a permanent
 * account lockout, so forgotten-PIN and recovery flows remain available.
 *
 * chat_session_token TABLE — created by the standalone one-time setup
 * script chat-session-token-setup.php (same CREATE TABLE IF NOT EXISTS /
 * delete-after-running pattern as the existing chat-setup.php), not by
 * this file. This file only ever reads/writes rows, exactly like
 * chat-config.php never creates chat_threads itself.
 */

// TASK-1 START — token lifetime constants
// Two independent expiries, per the Task 1 Recommended Enhancements
// (adopted — see Task-1-Completion.md "Enhancement Decisions"): a sliding
// idle expiry that resets on every successful verify_token() call, capped
// by a hard absolute ceiling from the moment of issuance that NO amount of
// activity can extend past. This means a token that's actively polled
// forever still dies eventually, while a token that's merely idle for a
// while (customer reading, not sending) doesn't die mid-session.
define('CHAT_TOKEN_TTL_IDLE_SECONDS', 60 * 60 * 4);       // 4 hours of inactivity
define('CHAT_TOKEN_TTL_ABSOLUTE_SECONDS', 60 * 60 * 24);  // 24 hour hard ceiling from issuance
// TASK-1 END

// TASK-1 START — issue_token()
/**
 * Issues a new opaque, cryptographically random session token for a
 * principal and stores only its SHA-256 hash server-side. The raw token is
 * returned ONCE for the client to store (e.g. localStorage, alongside the
 * existing pc_chat_active_id key) — it is never persisted anywhere in
 * recoverable form, matching how chatIsAdminAuthenticated() already
 * compares tokens without ever storing the admin token itself outside
 * .env.
 *
 * Deliberately opaque + DB-backed rather than a JWT (Recommended
 * Enhancement, adopted): a DB-backed token gets real revocation for free
 * (invalidate_all_tokens() just deletes rows) instead of needing a second
 * subsystem (a JWT blocklist) to fake revocation on top of a format that's
 * valid-until-expiry by design.
 */
function issue_token($principal_id) {
    $db = chatDb();
    $raw = bin2hex(random_bytes(32)); // 64 hex chars — 256 bits of entropy, opaque and unguessable
    $hash = hash('sha256', $raw);     // see "Why SHA-256, not bcrypt/Argon2" note below verify_token()
    $expiresAt = date('Y-m-d H:i:s', time() + CHAT_TOKEN_TTL_ABSOLUTE_SECONDS);

    $stmt = $db->prepare(
        "INSERT INTO chat_session_token (token_hash, principal_id, issued_at, expires_at)
         VALUES (?, ?, NOW(), ?)"
    );
    $stmt->execute([$hash, $principal_id, $expiresAt]);

    log_audit($principal_id, 'issue_token', 'issued');

    return $raw;
}
// TASK-1 END

// TASK-1 START — verify_token()
/**
 * Why SHA-256 here and NOT the same slow/salted algorithm Task 5a will use
 * for passwords (this was a Recommended Enhancement — "store as a hash
 * using the same hash/verify helper Task 5a will use for passwords" —
 * that this implementation deliberately MODIFIES; see Task-1-Completion.md
 * "Enhancement Decisions" for the full reasoning, summarized here):
 * a session token is already a 256-bit cryptographically random secret
 * with no brute-forceable structure, looked up by an exact WHERE
 * token_hash = ? match on every single poll request (every 2.5s per open
 * chat panel) — SHA-256 is the correct tool for that: fast, deterministic,
 * and the token's own entropy (not hash slowness) is what makes it
 * unguessable. A password is the opposite case: low-entropy, human-chosen,
 * and exactly why it needs a deliberately SLOW, salted algorithm
 * (password_hash()'s bcrypt/Argon2i, confirmed still current best practice
 * via web search) to resist offline brute force. Sharing one "helper"
 * between these two would either make every poll request pathologically
 * slow (running bcrypt every 2.5 seconds) or make password storage
 * brute-forceable (running fast SHA-256 on a low-entropy secret) —
 * genuinely the wrong tool for one of the two jobs no matter which
 * algorithm was picked. Task 5a should use PHP's own password_hash()/
 * password_verify() independently, not this function.
 *
 * Validates that a token exists, is unexpired, and belongs to the given
 * principal. On a successful verification, also refreshes the token's
 * idle window (capped by the absolute ceiling from original issuance) so
 * an actively-used token doesn't die out from under an open conversation,
 * while a genuinely abandoned one still expires on schedule.
 */
function verify_token($token_string, $principal_id) {
    if ($token_string === '' || $token_string === null || $principal_id === '' || $principal_id === null) {
        log_audit($principal_id ?: 'unknown', 'verify_token', 'missing_input');
        return false;
    }

    $db = chatDb();
    /* TASK-4 START — a client-side countdown is only a convenience. Re-check
       the authoritative close/lock timestamps before honoring any token so a
       second tab cannot remain valid after the configured lock point. */
    if (chatAuthEnforceLock($principal_id)) {
        log_audit($principal_id, 'verify_token', 'locked');
        return false;
    }
    /* TASK-4 END */
    $hash = hash('sha256', $token_string);

    $stmt = $db->prepare(
        "SELECT id, issued_at FROM chat_session_token
         WHERE token_hash = ? AND principal_id = ? AND expires_at > NOW()
         LIMIT 1"
    );
    $stmt->execute([$hash, $principal_id]);
    $row = $stmt->fetch();

    if (!$row) {
        log_audit($principal_id, 'verify_token', 'invalid_or_expired');
        return false;
    }

    // Sliding idle expiry, never extended past the absolute ceiling computed
    // from the token's original issued_at.
    $absoluteCeiling = strtotime($row['issued_at']) + CHAT_TOKEN_TTL_ABSOLUTE_SECONDS;
    $newExpiry = min(time() + CHAT_TOKEN_TTL_IDLE_SECONDS, $absoluteCeiling);
    $db->prepare("UPDATE chat_session_token SET expires_at = ? WHERE id = ?")
       ->execute([date('Y-m-d H:i:s', $newExpiry), $row['id']]);

    log_audit($principal_id, 'verify_token', 'verified');
    return true;
}
// TASK-1 END

// TASK-1 START — invalidate_all_tokens()
/**
 * Revokes every outstanding token for a principal. Required by Task 4 (on
 * lock trigger), Task 5a (on password set/change), and Task 6 (on
 * successful password reset) — without this, an already-open second tab
 * or a leaked token stays valid past a lock/password-change, which
 * defeats the entire point of those actions.
 *
 * Implemented as a real DELETE, not a soft "revoked_at" flag: the Shared
 * Contract's chat_session_token shape is exactly token_hash, principal_id,
 * issued_at, expires_at — no revoked_at column — so a row's mere
 * existence in an unexpired state IS its validity. Deleting the row is
 * therefore the correct (and only) way to revoke under the published
 * shape, with zero schema deviation to flag.
 */
function invalidate_all_tokens($principal_id) {
    $db = chatDb();
    $db->prepare("DELETE FROM chat_session_token WHERE principal_id = ?")
       ->execute([$principal_id]);
    log_audit($principal_id, 'invalidate_all_tokens', 'revoked_all');
}
// TASK-1 END

// TASK-1 START — log_audit()
/**
 * Writes one audit row per verification attempt (called automatically by
 * issue_token()/verify_token()/invalidate_all_tokens() above — no other
 * file needs to call those three events manually), and is also available
 * for any later task to log its own auth-state changes through this same
 * function (e.g. Task 3 logging a 'pin_set' result, Task 5a logging a
 * 'password_set' result, Task 4 logging a 'locked' result) so every
 * auth-related event in this project lands in one place.
 *
 * This is what makes Task 7's admin-facing auth-state badge (none / PIN
 * set / password set / currently locked / verified-this-session) possible
 * at all — it's explicitly scoped as read-only display work sourced
 * entirely from this table, with no logging of its own.
 *
 * Required, non-optional per the task brief: every downstream lock/
 * password/reset action depends on invalidate_all_tokens() existing, and
 * Task 7's whole feature depends on this table existing and being
 * populated from day one — not something to add later once it's "needed."
 */
function log_audit($principal_id, $endpoint, $result) {
    $db = chatDb();
    $db->prepare(
        "INSERT INTO chat_auth_audit_log (principal_id, endpoint, result, created_at) VALUES (?, ?, ?, NOW())"
    )->execute([$principal_id !== null && $principal_id !== '' ? $principal_id : 'unknown', $endpoint, $result]);
}
// TASK-1 END

// TASK-1 START — chatRequireSessionToken()
/**
 * The single gate every hardened customer-facing chat endpoint below
 * calls, mirroring how chatRequireAdmin() (chat-config.php) is the single
 * gate every admin endpoint already calls — same pattern, same shape,
 * different tier. On failure, responds 401 and halts via chatRespond(),
 * exactly like chatRequireAdmin() does.
 *
 * $providedToken is passed in explicitly by the calling endpoint (pulled
 * from wherever that endpoint already parses its input — chatJsonInput(),
 * $_POST, or $_GET, depending on the file) rather than this function
 * guessing at superglobals itself, since different endpoints in this
 * project take GET vs POST vs JSON-body input inconsistently already.
 * Falls back to the X-Chat-Token header if no token was explicitly passed,
 * mirroring chatIsAdminAuthenticated()'s own dual POST-field/header
 * pattern for the admin token.
 */
function chatRequireSessionToken($principal_id, $providedToken = null) {
    $token = ($providedToken !== null && $providedToken !== '')
        ? $providedToken
        : ($_SERVER['HTTP_X_CHAT_TOKEN'] ?? '');

    if ($token === '' || !verify_token($token, $principal_id)) {
        chatRespond([
            'success' => false,
            'error'   => 'A valid chat session is required. Please reopen this chat from the start screen.',
            'code'    => 'SESSION_REQUIRED',
        ], 401);
    }
}
// TASK-1 END

/* TASK-3 START — shared chat protection helpers
   These helpers keep PIN/password storage, progressive throttling, trusted
   device checks, and the server-side idle-lock decision in one place. The
   customer-facing endpoints below call these helpers instead of duplicating
   authentication rules. */

// Loads the one protection record for an anonymous chat principal.
function chatAuthGet($principalId) {
    $stmt = chatDb()->prepare("SELECT * FROM chat_auth WHERE principal_id = ? LIMIT 1");
    $stmt->execute([$principalId]);
    return $stmt->fetch() ?: null;
}

// Creates an explicitly unprotected record so every chat has one stable auth row.
function chatAuthEnsure($principalId) {
    $existing = chatAuthGet($principalId);
    if ($existing) return $existing;
    $stmt = chatDb()->prepare(
        "INSERT INTO chat_auth (principal_id, auth_tier, secret_hash, salt, failed_attempts, locked_until)
         VALUES (?, 'none', NULL, NULL, 0, NULL)"
    );
    $stmt->execute([$principalId]);
    return chatAuthGet($principalId);
}

// Applies the settled password/PIN policy without imposing composition rules.
function chatAuthValidateSecret($tier, $secret) {
    $secret = (string)$secret;
    if ($tier === 'pin') {
        if (!preg_match('/^\d{6}$/', $secret)) return 'PIN must be exactly 6 digits.';
        return null;
    }
    if ($tier === 'password') {
        if (mb_strlen($secret, 'UTF-8') < 12) return 'Password must be at least 12 characters.';
        if (mb_strlen($secret, 'UTF-8') > 1024) return 'Password is too long.';
        $blocked = [
            'password','password123','123456789012','1234567890','qwertyuiop12',
            'letmein12345','welcome12345','iloveyou1234','adminadmin12','petcenter123',
        ];
        if (in_array(mb_strtolower($secret, 'UTF-8'), $blocked, true)) {
            return 'Choose a password that is not common or previously exposed.';
        }
        return null;
    }
    return 'Unsupported authentication tier.';
}

// Stores a PIN or password with PHP's adaptive password hashing implementation.
function chatAuthSetSecret($principalId, $tier, $secret) {
    $error = chatAuthValidateSecret($tier, $secret);
    if ($error !== null) return ['success' => false, 'error' => $error];
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash((string)$secret, $algo);
    if ($hash === false) return ['success' => false, 'error' => 'Could not protect the secret.'];
    $salt = bin2hex(random_bytes(16));
    $stmt = chatDb()->prepare(
        "INSERT INTO chat_auth (principal_id, auth_tier, secret_hash, salt, failed_attempts, locked_until)
         VALUES (?, ?, ?, ?, 0, NULL)
         ON DUPLICATE KEY UPDATE auth_tier = VALUES(auth_tier), secret_hash = VALUES(secret_hash),
           salt = VALUES(salt), failed_attempts = 0, locked_until = NULL, updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$principalId, $tier, $hash, $salt]);
    invalidate_all_tokens($principalId);
    log_audit($principalId, $tier . '_set', $tier . '_set');
    return ['success' => true];
}

// Returns the progressive delay without creating a hard lockout.
function check_pin_throttle($principalId) {
    $auth = chatAuthGet($principalId);
    if (!$auth || empty($auth['locked_until'])) return ['allowed' => true, 'delay_seconds' => 0];
    $remaining = strtotime($auth['locked_until']) - time();
    if ($remaining <= 0) {
        chatDb()->prepare("UPDATE chat_auth SET locked_until = NULL WHERE principal_id = ?")->execute([$principalId]);
        return ['allowed' => true, 'delay_seconds' => 0];
    }
    return ['allowed' => false, 'delay_seconds' => $remaining];
}

// Adds a bounded progressive delay after an incorrect PIN/password attempt.
function chatAuthRecordFailure($principalId) {
    $db = chatDb();
    $stmt = $db->prepare("SELECT failed_attempts FROM chat_auth WHERE principal_id = ? FOR UPDATE");
    $stmt->execute([$principalId]);
    $attempts = (int)$stmt->fetchColumn() + 1;
    $delay = min(300, 2 ** min(8, max(0, $attempts - 1)));
    $db->prepare("UPDATE chat_auth SET failed_attempts = ?, locked_until = ? WHERE principal_id = ?")
       ->execute([$attempts, date('Y-m-d H:i:s', time() + $delay), $principalId]);
    return $delay;
}

// Clears only the progressive credential throttle after a successful check.
function chatAuthClearThrottle($principalId) {
    chatDb()->prepare("UPDATE chat_auth SET failed_attempts = 0, locked_until = NULL WHERE principal_id = ?")
       ->execute([$principalId]);
}

// Computes a server-side verifier for the client-held remembered-device secret.
function chatAuthDeviceHash($deviceToken) {
    return hash('sha256', (string)$deviceToken);
}

// Checks whether a remembered device is still valid and updates its last-use time.
function chatAuthTrustedDevice($principalId, $deviceToken) {
    if (!is_string($deviceToken) || !preg_match('/^[A-Fa-f0-9]{32,128}$/', $deviceToken)) return false;
    $hash = chatAuthDeviceHash($deviceToken);
    $stmt = chatDb()->prepare("SELECT id FROM chat_remember_device WHERE principal_id = ? AND device_fingerprint = ? LIMIT 1");
    $stmt->execute([$principalId, $hash]);
    $id = $stmt->fetchColumn();
    if (!$id) return false;
    chatDb()->prepare("UPDATE chat_remember_device SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);
    return true;
}

// Saves a trusted device only after the customer has successfully verified a tier.
function chatAuthRememberDevice($principalId, $deviceToken, $label) {
    if (!is_string($deviceToken) || !preg_match('/^[A-Fa-f0-9]{32,128}$/', $deviceToken)) return false;
    $hash = chatAuthDeviceHash($deviceToken);
    $label = mb_substr(trim((string)$label), 0, 120);
    chatDb()->prepare(
        "INSERT INTO chat_remember_device (principal_id, device_fingerprint, label, last_used_at)
         VALUES (?, ?, ?, CURRENT_TIMESTAMP)
         ON DUPLICATE KEY UPDATE label = VALUES(label), last_used_at = CURRENT_TIMESTAMP"
    )->execute([$principalId, $hash, $label ?: 'This device']);
    return true;
}

// Invalidates every remembered device for a principal, without exposing tokens.
function chatAuthForgetDevices($principalId) {
    chatDb()->prepare("DELETE FROM chat_remember_device WHERE principal_id = ?")->execute([$principalId]);
}

// Enforces delayed/aggressive lock settings on the server before token use.
function chatAuthEnforceLock($principalId) {
    $stmt = chatDb()->prepare("SELECT * FROM chat_lock_settings WHERE principal_id = ? LIMIT 1");
    $stmt->execute([$principalId]);
    $lock = $stmt->fetch();
    if (!$lock || empty($lock['last_closed_at']) || !empty($lock['locked_at'])) return false;
    $dueAt = strtotime($lock['last_closed_at']) + (int)$lock['delay_seconds'];
    if (!$lock['aggressive'] && time() < $dueAt) return false;
    chatDb()->prepare("UPDATE chat_lock_settings SET locked_at = CURRENT_TIMESTAMP WHERE principal_id = ? AND locked_at IS NULL")
       ->execute([$principalId]);
    chatDb()->prepare("DELETE FROM chat_session_token WHERE principal_id = ?")->execute([$principalId]);
    log_audit($principalId, 'chat-lock.php', 'locked');
    return true;
}

// Provides the current lock setting while keeping the lock decision server-owned.
function chatAuthLockStatus($principalId) {
    chatAuthEnforceLock($principalId);
    $stmt = chatDb()->prepare("SELECT delay_seconds, aggressive, last_closed_at, locked_at FROM chat_lock_settings WHERE principal_id = ? LIMIT 1");
    $stmt->execute([$principalId]);
    return $stmt->fetch() ?: ['delay_seconds' => 300, 'aggressive' => 0, 'last_closed_at' => null, 'locked_at' => null];
}
/* TASK-3 END */
