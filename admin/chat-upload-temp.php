<?php
/**
 * chat-upload-temp.php
 *
 * Stages a single image or audio file before it's attached to a chat
 * message. Mirrors the existing upload-temp.php token pattern exactly:
 * validate, store under a random token in a locked-down temp dir, write a
 * JSON sidecar with metadata, return the token.
 *
 * This endpoint does NOT handle video. Video messages go through the
 * existing chunked uploader (video-upload-manager.js -> upload-video-chunk.php)
 * exactly like pet videos do — that system already returns a `videoToken`
 * in the same shape this file's tokens take, so chat-send.php / chat-admin-reply.php
 * treat an image token, an audio token, and a video token identically:
 * just a string referencing a staged file somewhere in CHAT_UPLOAD_TEMP_DIR.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

if (empty($_FILES) || !isset($_FILES['file'])) {
    chatRespond(['success' => false, 'error' => 'No file provided'], 400);
}

$file = $_FILES['file'];
$kind = $_POST['kind'] ?? ''; // 'image', 'audio', or 'video'

if (!in_array($kind, ['image', 'audio', 'video'], true)) {
    chatRespond(['success' => false, 'error' => "kind must be 'image', 'audio', or 'video'"], 400);
}

/* TASK-1 START (revised — second verification round)
   This endpoint's original contract never sent a chatId here — a staged
   upload was chat-agnostic until chat-send.php consumed its token, a
   genuine documented design choice (ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
   own note that "the actual security boundary is downstream" for the
   sibling chunked-video endpoint). chat-widget.js has SINCE been updated
   (also this task, after being raised as a blocker and then supplied) to
   send chatId + sessionToken on every call here, so the first branch
   below is the live, normal customer path now, not a someday-ready one.
   Second verification round addition: this file is very likely also used
   by the admin dashboard's chat composer for image/audio attachments
   ahead of chat-admin-reply.php — chat-admin-reply.php's own
   imageTokens[]/audioToken inputs have to come staged from somewhere, and
   no separate admin-only staging endpoint is documented anywhere in the
   file structure, so this is the only candidate. Not explicitly confirmed
   the way chat-upload-video-chunk.php's admin usage is (that one names
   `uploadVideoChunked()` and its X-Admin-Token header directly) — the
   same free win already given to
   chat-upload-video-chunk.php (checking X-Admin-Token, which that
   sibling endpoint is confirmed to already receive from the admin
   dashboard) is added here too. Unlike that sibling file, nothing in the
   project documentation confirms whether the admin dashboard currently
   sends X-Admin-Token to THIS endpoint specifically — so this check is
   purely additive and safe either way: if the header is sent, admin
   uploads get verified for real and are exempted from the anonymous rate
   limit below; if it isn't (today, most likely), nothing changes and the
   existing rate-limited fallback still applies exactly as before.
     1. A valid admin token wins outright, same precedence as
        chat-upload-video-chunk.php.
     2. Otherwise, a chatId + sessionToken pair (now sent by the current
        chat-widget.js) is verified for real.
     3. Otherwise, rate-limited + audit-logged as a fallback for any other
        caller. */
if (chatIsAdminAuthenticated()) {
    log_audit('admin', 'chat-upload-temp', 'staged_' . $kind . '_admin');
} else {
    $uploadChatId = trim($_POST['chatId'] ?? '');
    $uploadSessionToken = trim($_POST['sessionToken'] ?? '');

    if ($uploadChatId !== '') {
        chatRequireSessionToken($uploadChatId, $uploadSessionToken);
        log_audit($uploadChatId, 'chat-upload-temp', 'staged_' . $kind);
    } else {
        $uploadIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!chatCheckRateLimit('chat_upload_temp_ip:' . $uploadIp, 30, 600)) { // 30 staged uploads / 10 min / IP
            chatRespond(['success' => false, 'error' => 'Too many uploads. Please wait a few minutes and try again.'], 429);
        }
        log_audit('unknown', 'chat-upload-temp', 'staged_' . $kind . '_no_session');
    }
}
/* TASK-1 END */

// ── Upload error check ──────────────────────────────────────────────────
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server max upload size',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form max size',
        UPLOAD_ERR_PARTIAL    => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file selected',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temp directory missing',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write file',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server configuration',
    ];
    chatRespond(['success' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error'], 400);
}

// ── Per-kind validation ──────────────────────────────────────────────────
if ($kind === 'image') {
    $allowedMime = CHAT_IMAGE_ALLOWED_MIME;
    $allowedExt  = CHAT_IMAGE_ALLOWED_EXT;
    $maxBytes    = CHAT_IMAGE_MAX_BYTES;
} elseif ($kind === 'video') {
    $allowedMime = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
    $allowedExt  = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mpeg', 'mpg'];
    $maxBytes    = CHAT_VIDEO_MAX_BYTES;
} else { // audio
    $allowedMime = ['audio/webm', 'audio/ogg', 'audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/wav', 'audio/x-wav', 'video/webm'];
    $allowedExt  = ['webm', 'ogg', 'mp3', 'm4a', 'mp4', 'wav'];
    $maxBytes    = CHAT_VOICE_MAX_BYTES;
}

if ($file['size'] > $maxBytes) {
    chatRespond(['success' => false, 'error' => 'File too large (' . round($file['size'] / 1024 / 1024, 1) . 'MB). Max: ' . round($maxBytes / 1024 / 1024) . 'MB'], 413);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMime, true)) {
    chatRespond(['success' => false, 'error' => "Invalid $kind type: $mimeType"], 415);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    // Fall back to a safe default extension rather than reject outright,
    // since MIME already passed — browsers sometimes send odd extensions.
    $ext = ($kind === 'image') ? 'jpg' : ($kind === 'video' ? 'mp4' : 'webm');
}

// ── Stage the file ───────────────────────────────────────────────────────
$token = chatGenerateUploadToken();
$tempFileName = $token . '.' . $ext;
$tempFilePath = CHAT_UPLOAD_TEMP_DIR . $tempFileName;

if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
    chatRespond(['success' => false, 'error' => 'Failed to save file'], 500);
}
@chmod($tempFilePath, 0644);

$metadata = [
    'token'     => $token,
    'kind'      => $kind,
    'original'  => basename($file['name']),
    'mime'      => $mimeType,
    'size'      => $file['size'],
    'extension' => $ext,
    'created_at' => date('Y-m-d H:i:s'),
    'expires_at' => date('Y-m-d H:i:s', time() + 7200), // 2 hours, matches existing temp upload convention
];
@file_put_contents(CHAT_UPLOAD_TEMP_DIR . $token . '.json', json_encode($metadata));

// ── Probabilistic cleanup of expired staged uploads (1% chance per request) ──
// Runs before the response, not after — chatRespond() calls exit internally,
// so anything placed after it would never execute.
if (rand(1, 100) <= 1) {
    foreach (glob(CHAT_UPLOAD_TEMP_DIR . '*.json') ?: [] as $metaFile) {
        $meta = json_decode(@file_get_contents($metaFile), true);
        if (!empty($meta['expires_at']) && strtotime($meta['expires_at']) < time()) {
            $stem = substr($metaFile, 0, -5); // strip ".json"
            foreach (glob($stem . '.*') ?: [] as $f) {
                @unlink($f);
            }
            @unlink($metaFile);
        }
    }
}

chatRespond([
    'success' => true,
    'token'   => $token,
    'kind'    => $kind,
    'name'    => $metadata['original'],
    'size'    => $file['size'],
]);
