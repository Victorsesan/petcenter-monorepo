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
