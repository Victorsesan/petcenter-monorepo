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

$action    = $_POST['action'] ?? 'upload';
$uploadId  = $_POST['uploadId'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';

if (!$uploadId) {
    echo json_encode(['success' => false, 'error' => 'Missing uploadId']);
    exit;
}

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
