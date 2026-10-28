<?php
/**
 * chat-upload-video-chunk.php
 *
 * Chunked video upload endpoint for the chat widget (both customer and admin
 * sides). Mirrors upload-video-chunk.php's mechanism but saves into the
 * chat-uploads-temp/ staging area using the token pattern, so chat-send.php
 * and chat-admin-reply.php can find and move it to chat-media/{chatId}/ on send.
 *
 * The VideoUploadManager.js handles chunking on the admin side.
 * The chat widget's built-in uploadVideoChunked() handles it on the public side.
 */

error_reporting(0);
@ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$action    = $_POST['action'] ?? 'upload';
$uploadId  = $_POST['uploadId'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';

if (!$uploadId) {
    echo json_encode(['success' => false, 'error' => 'Missing uploadId']);
    exit;
}

/* TASK-1 START — same structural limit as chat-upload-temp.php's TASK-1
   block above (see that file for the full reasoning on why full
   enforcement isn't possible here without an out-of-scope chat-widget.js
   change) — with one additional, genuinely free improvement specific to
   this file: ADMIN_DASHBOARD_PROJECT_SUMMARY.md documents that the admin
   dashboard ALREADY sends an X-Admin-Token header to this endpoint today,
   even though nothing here has ever checked it. That means the admin path
   can be fully, correctly hardened right now with zero frontend changes —
   only the customer (chat widget) path needs the same soft/rate-limited
   fallback as chat-upload-temp.php, since chat-widget.js doesn't send a
   chatId here today.
   Precedence: a valid admin token wins outright (this endpoint is
   genuinely shared by both sides, unlike every other file in this task).
   Otherwise, an explicit chatId+sessionToken pair is verified for real if
   supplied (ready for whenever chat-widget.js is updated to send them).
   Otherwise, rate-limited + audit-logged, matching the customer widget's
   actual behavior today. */
if (chatIsAdminAuthenticated()) {
    log_audit('admin', 'chat-upload-video-chunk', $action . '_admin');
} else {
    $uploadChatId = trim($_POST['chatId'] ?? '');
    $uploadSessionToken = trim($_POST['sessionToken'] ?? '');
    if ($uploadChatId !== '') {
        chatRequireSessionToken($uploadChatId, $uploadSessionToken);
        log_audit($uploadChatId, 'chat-upload-video-chunk', $action);
    } else {
        $uploadIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!chatCheckRateLimit('chat_upload_video_ip:' . $uploadIp, 60, 600)) { // 60 chunk calls / 10 min / IP — chunked uploads mean many small calls per real video, so this ceiling is higher than the single-shot chat-upload-temp.php limit
            echo json_encode(['success' => false, 'error' => 'Too many uploads. Please wait a few minutes and try again.']);
            exit;
        }
        log_audit('unknown', 'chat-upload-video-chunk', $action . '_no_session');
    }
}
/* TASK-1 END */

$chunkDir = CHAT_UPLOAD_TEMP_DIR . 'chunks/';
if (!is_dir($chunkDir)) @mkdir($chunkDir, 0755, true);

$sessionDir = $chunkDir . preg_replace('/[^A-Za-z0-9_\-]/', '', $sessionId ?: $uploadId);
if (!is_dir($sessionDir)) @mkdir($sessionDir, 0755, true);

// ── UPLOAD CHUNK ─────────────────────────────────────────────────────────────
if ($action === 'upload') {
    if (!isset($_FILES['chunk'])) {
        echo json_encode(['success' => false, 'error' => 'No chunk provided']);
        exit;
    }

    $chunkIndex  = (int)($_POST['chunkIndex'] ?? -1);
    $totalChunks = (int)($_POST['totalChunks'] ?? 0);
    $fileName    = basename($_POST['fileName'] ?? 'video.mp4');
    $fileSize    = (int)($_POST['fileSize'] ?? 0);

    if ($chunkIndex < 0 || $totalChunks <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid chunk parameters']);
        exit;
    }

    // Store filename for finalize
    file_put_contents($sessionDir . '/filename.txt', $fileName);

    $chunk = $_FILES['chunk'];
    if ($chunk['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Chunk upload error: ' . $chunk['error']]);
        exit;
    }

    if ($chunk['size'] > 6 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Chunk too large (max 6MB per chunk)']);
        exit;
    }

    $chunkFile = $sessionDir . '/chunk_' . str_pad($chunkIndex, 4, '0', STR_PAD_LEFT);
    if (!move_uploaded_file($chunk['tmp_name'], $chunkFile)) {
        echo json_encode(['success' => false, 'error' => 'Failed to save chunk']);
        exit;
    }

    $uploaded = count(glob($sessionDir . '/chunk_*'));
    echo json_encode([
        'success'        => true,
        'chunkIndex'     => $chunkIndex,
        'chunksReceived' => $uploaded,
        'totalChunks'    => $totalChunks,
    ]);
    exit;
}

// ── FINALIZE — assemble chunks into a staged token file ──────────────────────
if ($action === 'finalize') {
    $totalChunks = (int)($_POST['totalChunks'] ?? 0);
    $fileName    = basename(file_exists($sessionDir . '/filename.txt')
        ? trim(file_get_contents($sessionDir . '/filename.txt'))
        : ($_POST['fileName'] ?? 'video.mp4'));

    if ($totalChunks <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid totalChunks']);
        exit;
    }

    // Verify all chunks present
    for ($i = 0; $i < $totalChunks; $i++) {
        $f = $sessionDir . '/chunk_' . str_pad($i, 4, '0', STR_PAD_LEFT);
        if (!file_exists($f)) {
            echo json_encode(['success' => false, 'error' => 'Missing chunk ' . $i]);
            exit;
        }
    }

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'ogg'])) $ext = 'mp4';

    $token    = chatGenerateUploadToken();
    $destFile = CHAT_UPLOAD_TEMP_DIR . $token . '.' . $ext;

    $fp = fopen($destFile, 'wb');
    if (!$fp) {
        echo json_encode(['success' => false, 'error' => 'Cannot create assembled file']);
        exit;
    }

    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkFile = $sessionDir . '/chunk_' . str_pad($i, 4, '0', STR_PAD_LEFT);
        $data = file_get_contents($chunkFile);
        fwrite($fp, $data);
        @unlink($chunkFile);
    }
    fclose($fp);

    @unlink($sessionDir . '/filename.txt');
    @rmdir($sessionDir);
    @chmod($destFile, 0644);

    // Write sidecar JSON (same pattern as chat-upload-temp.php)
    $meta = [
        'token'      => $token,
        'kind'       => 'video',
        'original'   => $fileName,
        'mime'       => 'video/' . $ext,
        'extension'  => $ext,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', time() + 7200),
    ];
    file_put_contents(CHAT_UPLOAD_TEMP_DIR . $token . '.json', json_encode($meta));

    echo json_encode([
        'success'   => true,
        'token'     => $token,
        'kind'      => 'video',
        'name'      => $fileName,
        'extension' => $ext,
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
