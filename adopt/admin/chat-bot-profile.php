<?php
/**
 * chat-bot-profile.php
 *
 * Admin CRUD for the single bot profile (v2.0). Unlike chat-admin-profiles.php
 * (which manages multiple real admin identities in a DB table), this manages
 * exactly ONE bot identity stored in a single JSON file — chat-bot-profile.json.
 * No DB table by design: the bot only ever sends pre-written, structured text,
 * so a file is sufficient and there's nothing to query/join/relationally model.
 *
 * Concurrency: chatSaveBotProfile() uses LOCK_EX on write, which is enough
 * to keep two admins saving at the same instant from corrupting the file —
 * one write fully completes before the other's lock is granted. Reads
 * (chatGetBotProfile) are simple file_get_contents calls, safe against a
 * completed write; there's no scenario where many customers reading the bot
 * profile at once causes any issue since reads never modify the file.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$action = $_REQUEST['action'] ?? 'get';

// ── Get bot profile — used by admin dashboard settings form ─────────────
if ($action === 'get') {
    chatRequireAdmin();
    chatRespond(['success' => true, 'profile' => chatGetBotProfile()]);
}

// ── Save bot profile (full replace) ──────────────────────────────────────
if ($action === 'save') {
    chatRequireAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        chatRespond(['success' => false, 'error' => 'POST required'], 405);
    }

    $input = chatJsonInput();
    if (empty($input)) $input = $_POST;

    $name = trim($input['name'] ?? 'Pet Center Bot');
    if ($name === '') $name = 'Pet Center Bot';
    $active = !empty($input['active']);

    // welcomeMessages / reopenMessages arrive as JSON-encoded objects keyed
    // by reason (adoption/shipping/health/general) when sent from the admin
    // dashboard's form via fetch() with JSON body. Also accept them already
    // decoded if this endpoint is ever called with a native array.
    $welcomeMessages = $input['welcomeMessages'] ?? [];
    if (is_string($welcomeMessages)) $welcomeMessages = json_decode($welcomeMessages, true) ?: [];
    $reopenMessages = $input['reopenMessages'] ?? [];
    if (is_string($reopenMessages)) $reopenMessages = json_decode($reopenMessages, true) ?: [];

    // Only keep known reason keys — prevents arbitrary keys from bloating the file
    $validReasons = array_keys(CHAT_REASONS);
    $welcomeMessages = array_intersect_key($welcomeMessages, array_flip($validReasons));
    $reopenMessages  = array_intersect_key($reopenMessages, array_flip($validReasons));

    $existing = chatGetBotProfile();
    $profile = [
        'name'            => mb_substr($name, 0, 100),
        'photoUrl'        => $existing['photoUrl'] ?? null, // photo updated via separate action=uploadPhoto
        'active'          => $active,
        'welcomeMessages' => array_map(fn($m) => mb_substr(trim((string)$m), 0, 1000), $welcomeMessages),
        'welcomeFallback' => mb_substr(trim((string)($input['welcomeFallback'] ?? '')), 0, 1000),
        'firstMessageAck' => mb_substr(trim((string)($input['firstMessageAck'] ?? '')), 0, 1000),
        'reopenMessages'  => array_map(fn($m) => mb_substr(trim((string)$m), 0, 1000), $reopenMessages),
        'reopenFallback'  => mb_substr(trim((string)($input['reopenFallback'] ?? '')), 0, 1000),
        'reopenFirstMessageAck' => mb_substr(trim((string)($input['reopenFirstMessageAck'] ?? '')), 0, 1000),
    ];

    if (!chatSaveBotProfile($profile)) {
        chatRespond(['success' => false, 'error' => 'Failed to save bot profile'], 500);
    }
    chatRespond(['success' => true, 'profile' => $profile]);
}

// ── Upload/replace bot photo ──────────────────────────────────────────────
if ($action === 'uploadPhoto') {
    chatRequireAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
        chatRespond(['success' => false, 'error' => 'photo file required'], 400);
    }

    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        chatRespond(['success' => false, 'error' => 'Upload error'], 400);
    }
    if ($file['size'] > CHAT_IMAGE_MAX_BYTES) {
        chatRespond(['success' => false, 'error' => 'Photo too large (max 5MB)'], 413);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, CHAT_IMAGE_ALLOWED_MIME, true)) {
        chatRespond(['success' => false, 'error' => 'Invalid image type'], 415);
    }

    $photoDir = __DIR__ . '/chat-admin-photos/'; // reuse the same photo directory as admin profiles
    if (!is_dir($photoDir)) @mkdir($photoDir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, CHAT_IMAGE_ALLOWED_EXT, true)) $ext = 'jpg';
    $filename = 'bot_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destPath = $photoDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        chatRespond(['success' => false, 'error' => 'Failed to save photo'], 500);
    }

    $profile = chatGetBotProfile();
    // Delete old photo file if one existed
    if (!empty($profile['photoUrl'])) {
        $oldPath = __DIR__ . '/' . $profile['photoUrl'];
        if (file_exists($oldPath)) @unlink($oldPath);
    }
    $profile['photoUrl'] = 'chat-admin-photos/' . $filename;

    if (!chatSaveBotProfile($profile)) {
        chatRespond(['success' => false, 'error' => 'Failed to save profile after photo upload'], 500);
    }
    chatRespond(['success' => true, 'photoUrl' => $profile['photoUrl']]);
}

chatRespond(['success' => false, 'error' => 'Unknown action'], 400);
