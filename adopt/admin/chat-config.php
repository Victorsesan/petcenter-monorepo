<?php
/**
 * Live Chat Widget — Shared Configuration
 *
 * MySQL connection (via PDO), shared constants, and helper functions used
 * by every other chat-*.php endpoint in this feature.
 *
 * Per P2P-CHAT-WIDGET-PLAN.md Section 2 — this is the ONLY part of the admin
 * dashboard ecosystem backed by MySQL. Pets and applications remain on the
 * existing JSON-file system and are untouched by this file.
 */

error_reporting(E_ALL);

// ============================================
// DATABASE CONFIGURATION — REPLACE PLACEHOLDERS
// ============================================
// Create the MySQL database in Hostinger first, then replace these four
// values with the real credentials. Nothing in this feature will connect
// until DB_NAME / DB_USER / DB_PASSWORD are real.
define('DB_HOST', 'localhost');
define('DB_NAME', 'u127878628_chat_widget_db');
define('DB_USER', 'u127878628_chat_widget_us');
define('DB_PASSWORD', 'Godisgood8z$');
define('DB_CHARSET', 'utf8mb4');


// ============================================
// PDO CONNECTION (lazy singleton)
// ============================================
function chatDb() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Chat DB connection failed: ' . $e->getMessage());
        http_response_code(500);
        die(json_encode(['success' => false, 'error' => 'Database connection failed. Check chat-config.php credentials.']));
    }
}

// ============================================
// PATHS
// ============================================
define('CHAT_MEDIA_DIR', __DIR__ . '/chat-media/');           // chat-media/{chatId}/...
define('CHAT_UPLOAD_TEMP_DIR', __DIR__ . '/chat-uploads-temp/'); // staged tokens before a message attaches them

if (!is_dir(CHAT_MEDIA_DIR)) {
    @mkdir(CHAT_MEDIA_DIR, 0755, true);
}
if (!is_dir(CHAT_UPLOAD_TEMP_DIR)) {
    @mkdir(CHAT_UPLOAD_TEMP_DIR, 0755, true);
    @file_put_contents(CHAT_UPLOAD_TEMP_DIR . '.htaccess', "Deny from all\n");
}

// ============================================
// LIMITS (per P2P-CHAT-WIDGET-PLAN.md Section 8)
// ============================================
define('CHAT_VOICE_MAX_SECONDS', 600);        // 10 minutes
define('CHAT_VOICE_MAX_BYTES', 20 * 1024 * 1024); // 20MB
define('CHAT_IMAGE_MAX_BYTES', 5 * 1024 * 1024);  // matches existing site-wide image constraint (contact-config.php UPLOAD_MAX_SIZE_MB = 5)
define('CHAT_IMAGE_ALLOWED_MIME', ['image/jpeg', 'image/png']); // matches contact-config.php UPLOAD_ALLOWED_TYPES exactly
define('CHAT_IMAGE_ALLOWED_EXT', ['jpg', 'jpeg', 'png']);        // matches contact-config.php UPLOAD_ALLOWED_EXT exactly
define('CHAT_VIDEO_MAX_BYTES', 50 * 1024 * 1024); // matches existing pet-video ceiling

define('CHAT_NO_ANSWER_MINUTES', 15);          // "we typically reply within a few hours" threshold
define('CHAT_STALE_CLAIM_HOURS', 24);          // staleness resurfacing threshold
define('CHAT_EDIT_DELETE_WINDOW_MINUTES', 0);  // 0 = unlimited, no time restriction on editing/deleting own messages

// ============================================
// REASON CATEGORIES + AUTOMATED FIRST RESPONSE
// Editable here without touching any endpoint logic.
// ============================================
define('CHAT_REASONS', [
    'adoption' => 'Adoption inquiry',
    'shipping' => 'Shipping question',
    'health'   => 'Health / medical',
    'general'  => 'General / other',
]);

define('CHAT_AUTO_RESPONSES', [
    'adoption' => "Thanks for reaching out about adopting! Hold tight — one of our adoption team will be with you shortly.",
    'shipping' => "Thanks for your patience — for shipping questions, most are resolved within 24 hours. An admin will follow up shortly.",
    'health'   => "Thanks for letting us know — health-related messages get priority. Someone will be with you as soon as possible.",
    'general'  => "Thanks for reaching out! We'll be with you shortly.",
]);

define('CHAT_NO_ANSWER_MESSAGE', "Our team typically replies within a few hours. We'll get back to you as soon as we can.");

// ============================================
// BOT PROFILE (v2.0) — lightweight JSON-file config, no DB table.
// Stores: bot name, photo, and per-reason/per-scenario message templates.
// Messages may contain the literal placeholder {name} which is substituted
// with the customer's actual first name at SEND time (not at config time),
// via chatBotSubstitute() below.
// ============================================
define('CHAT_BOT_PROFILE_FILE', __DIR__ . '/chat-bot-profile.json');

function chatGetBotProfile() {
    if (!file_exists(CHAT_BOT_PROFILE_FILE)) {
        return [
            'name' => 'Pet Center Bot',
            'photoUrl' => null,
            'active' => true,
            'welcomeMessages' => [], // keyed by reason: adoption/shipping/health/general
            'welcomeFallback' => '',
            'firstMessageAck' => '', // sent once, right after the customer's first-ever message in a thread
            'reopenMessages' => [],  // keyed by reason
            'reopenFallback' => '',
            'reopenFirstMessageAck' => '', // Scenario B's own separate first-message-ack, fired when a done thread is reopened by a message
        ];
    }
    // Concurrent-read safe: file_get_contents on a small JSON file is atomic
    // enough in practice for this use case (reads never partially-write).
    $data = json_decode(file_get_contents(CHAT_BOT_PROFILE_FILE), true);
    return $data ?: chatGetBotProfile(); // fall back to defaults if corrupt
}

function chatSaveBotProfile($profile) {
    // Concurrent-write safe: LOCK_EX ensures two admins saving at the same
    // moment don't interleave and corrupt the file — one write fully
    // completes before the other's lock is granted.
    $json = json_encode($profile, JSON_PRETTY_PRINT);
    return file_put_contents(CHAT_BOT_PROFILE_FILE, $json, LOCK_EX) !== false;
}

/**
 * Substitutes the {name} placeholder in a bot message template with the
 * customer's actual first name. Called at SEND time (when the message is
 * about to be inserted for a specific customer), never at config-save time,
 * since the same template is reused across every customer who triggers it.
 * Falls back to "there" if no name is available (e.g. malformed data),
 * so a message never renders literally as "{name}" to a real customer.
 */
function chatBotSubstitute($template, $fullName) {
    $firstName = trim(explode(' ', trim($fullName))[0] ?? '');
    if ($firstName === '') $firstName = 'there';
    return str_replace('{name}', $firstName, $template);
}

// ============================================
// AUTH — mirrors the existing admin dashboard's isAuthenticated() pattern
// (token stored in .env as ADMIN_TOKEN, compared against POST/header/session)
// ============================================
function chatIsAdminAuthenticated() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return false;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) return false;
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null);
    return $provided && hash_equals($admin_token, $provided);
}

function chatRequireAdmin() {
    if (!chatIsAdminAuthenticated()) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Unauthorized']));
    }
}

// Admin identity (per-tab selection, Section 8) is NOT validated as a security
// boundary here — it's an attribution control. We only confirm the chosen
// admin_id exists and is active, so a stale/deleted profile can't silently
// keep claiming chats.
function chatValidateAdminId($adminId) {
    if (!$adminId) return false;
    $stmt = chatDb()->prepare("SELECT id FROM chat_admin_profiles WHERE id = ? AND active = 1");
    $stmt->execute([$adminId]);
    return (bool)$stmt->fetch();
}

// ============================================
// HELPERS
// ============================================

// Unique, human-shareable chat ID with collision retry (Section 4 note)
function chatGenerateChatId() {
    $db = chatDb();
    do {
        $id = 'PC-' . strtoupper(bin2hex(random_bytes(4))); // PC-XXXXXXXX, 8 hex chars
        $stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ?");
        $stmt->execute([$id]);
        $exists = (bool)$stmt->fetch();
    } while ($exists);
    return $id;
}

// Token for staged temp uploads (images/audio/video pre-message)
function chatGenerateUploadToken() {
    return bin2hex(random_bytes(16));
}

// Group ID shared by messages sent together in one Send action (grouped multi-media send)
function chatGenerateGroupId() {
    return bin2hex(random_bytes(12));
}

function chatJsonInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function chatRespond($payload, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($payload);
    exit;
}

// Move a staged temp file into its permanent location. Tries the fast path
// (rename, same filesystem) first; falls back to copy+unlink if that fails,
// since some hosting setups don't guarantee temp/final dirs share a
// filesystem and rename() can silently fail across mount points.
function chatMoveStagedFile($from, $to) {
    if (@rename($from, $to)) {
        return true;
    }
    if (@copy($from, $to)) {
        @unlink($from);
        return true;
    }
    return false;
}

function chatValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Resolve a staged upload token to its temp file path. Used by both
// chat-send.php and chat-admin-reply.php — kept here, shared, rather than
// duplicated in each, so the two can't silently drift apart.
function chatResolveStagedToken($token) {
    if ($token === '') return null;
    $matches = glob(CHAT_UPLOAD_TEMP_DIR . $token . '.*');
    // exclude the .json sidecar itself
    $matches = array_values(array_filter($matches, fn($f) => !str_ends_with($f, '.json')));
    return $matches[0] ?? null;
}

function chatValidReason($reason) {
    return array_key_exists($reason, CHAT_REASONS);
}

// ============================================
// RATE LIMITING — sliding window backed by chat_rate_limits
// ============================================
function chatCheckRateLimit($key, $maxAttempts, $windowSeconds) {
    $db = chatDb();
    $stmt = $db->prepare("SELECT attempts, window_start FROM chat_rate_limits WHERE rate_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    $now = time();

    if (!$row) {
        $stmt = $db->prepare("INSERT INTO chat_rate_limits (rate_key, attempts, window_start) VALUES (?, 1, NOW())");
        $stmt->execute([$key]);
        return true;
    }

    $windowStart = strtotime($row['window_start']);
    if (($now - $windowStart) > $windowSeconds) {
        // window expired, reset
        $stmt = $db->prepare("UPDATE chat_rate_limits SET attempts = 1, window_start = NOW() WHERE rate_key = ?");
        $stmt->execute([$key]);
        return true;
    }

    if ((int)$row['attempts'] >= $maxAttempts) {
        return false;
    }

    $stmt = $db->prepare("UPDATE chat_rate_limits SET attempts = attempts + 1 WHERE rate_key = ?");
    $stmt->execute([$key]);
    return true;
}
