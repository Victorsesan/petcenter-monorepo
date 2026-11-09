<?php
session_start();
header('Content-Type: application/json');

// Suppress errors to keep JSON clean
error_reporting(0);
@ini_set('display_errors', 0);

// ── Auth ────────────────────────────────────────────────────────────────────
function isAuthenticated() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return false;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) return false;
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_SESSION['admin_token'] ?? null));
    return $provided && $provided === $admin_token;
}

if (!isAuthenticated()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$action = $_POST['action'] ?? 'upload';
$uploadId = $_POST['uploadId'] ?? '';
$sessionId = $_POST['sessionId'] ?? '';

if (!$uploadId) {
    die(json_encode(['success' => false, 'error' => 'Missing uploadId']));
}

// Chunk temp directory
$chunk_dir = __DIR__ . '/video_uploads_temp';
if (!is_dir($chunk_dir)) mkdir($chunk_dir, 0755, true);

$session_dir = $chunk_dir . '/' . $sessionId;
if (!is_dir($session_dir)) mkdir($session_dir, 0755, true);

// Clean up expired staged videos and abandoned chunk sessions — runs probabilistically,
// 1 in 10 requests, same pattern as admin-upload-temp.php. This covers admins who only
// ever upload video (and never hit admin-upload-temp.php, which also sweeps this folder).
if (rand(1, 10) === 1) {
    // 1) Stale staged videos in temp_admin (assembled but never claimed by publish/update)
    $temp_admin_dir = __DIR__ . '/../adoptions/temp_admin';
    foreach (glob($temp_admin_dir . '/*.json') ?: [] as $meta_file) {
        if (filemtime($meta_file) < time() - 7200) {
            $meta = json_decode(file_get_contents($meta_file), true);
            if ($meta && !empty($meta['path']) && file_exists($meta['path'])) {
                @unlink($meta['path']);
            }
            @unlink($meta_file);
        }
    }

    // 2) Abandoned chunk session directories — upload started, never finalized (e.g. user
    //    closed the tab or lost connection permanently mid-upload). 2 hour threshold.
    foreach (glob($chunk_dir . '/*', GLOB_ONLYDIR) ?: [] as $stale_session) {
        if (filemtime($stale_session) < time() - 7200) {
            foreach (glob($stale_session . '/chunk_*') ?: [] as $stale_chunk) {
                @unlink($stale_chunk);
            }
            @rmdir($stale_session);
        }
    }
}

// ── UPLOAD CHUNK ────────────────────────────────────────────────────────────
if ($action === 'upload') {
    if (!isset($_FILES['chunk'])) {
        die(json_encode(['success' => false, 'error' => 'No chunk provided']));
    }

    $chunkIndex = (int)($_POST['chunkIndex'] ?? -1);
    $totalChunks = (int)($_POST['totalChunks'] ?? 0);
    $fileName = $_POST['fileName'] ?? 'video.mp4';
    $fileSize = (int)($_POST['fileSize'] ?? 0);

    if ($chunkIndex < 0 || $totalChunks <= 0) {
        die(json_encode(['success' => false, 'error' => 'Invalid chunk parameters']));
    }

    // Enforce max total file size server-side (50MB), matching the dashboard UI limit
    $MAX_VIDEO_SIZE = 50 * 1024 * 1024;
    if ($fileSize > 0 && $fileSize > $MAX_VIDEO_SIZE) {
        die(json_encode(['success' => false, 'error' => 'Video exceeds max size of 50MB']));
    }

    $allowed_ext = ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'];
    $ext_check = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext_check, $allowed_ext)) {
        die(json_encode(['success' => false, 'error' => 'Unsupported video type: .' . $ext_check]));
    }

    $chunk = $_FILES['chunk'];
    if ($chunk['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'error' => 'Upload error: ' . $chunk['error']]));
    }

    // Check chunk size (max 5MB + buffer)
    if ($chunk['size'] > 6 * 1024 * 1024) {
        die(json_encode(['success' => false, 'error' => 'Chunk too large']));
    }

    // Save chunk with index
    $chunkFile = $session_dir . '/chunk_' . str_pad($chunkIndex, 4, '0', STR_PAD_LEFT);
    if (!move_uploaded_file($chunk['tmp_name'], $chunkFile)) {
        die(json_encode(['success' => false, 'error' => 'Failed to save chunk']));
    }

    // Verify chunk count
    $uploaded_count = count(glob($session_dir . '/chunk_*'));
    echo json_encode([
        'success' => true,
        'chunkIndex' => $chunkIndex,
        'chunksReceived' => $uploaded_count,
        'totalChunks' => $totalChunks,
        'message' => 'Chunk uploaded',
    ]);
    exit;
}

// ── FINALIZE (ASSEMBLE) ─────────────────────────────────────────────────────
if ($action === 'finalize') {
    $totalChunks = (int)($_POST['totalChunks'] ?? 0);
    $fileName = $_POST['fileName'] ?? 'video.mp4';

    if ($totalChunks <= 0) {
        die(json_encode(['success' => false, 'error' => 'Invalid total chunks']));
    }

    // Check all chunks exist
    $chunks = [];
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkFile = $session_dir . '/chunk_' . str_pad($i, 4, '0', STR_PAD_LEFT);
        if (!file_exists($chunkFile)) {
            die(json_encode(['success' => false, 'error' => 'Missing chunk ' . $i]));
        }
        $chunks[] = $chunkFile;
    }

    // Get extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'])) $ext = 'mp4';

    // Stage assembled video in the same temp_admin dir used by admin-upload-temp.php,
    // with a token + metadata sidecar — publish-pet.php / update-pet.php pick it up
    // from there the same way they already pick up staged images.
    try   { $token = bin2hex(random_bytes(16)); }
    catch (Exception $e) { $token = md5(uniqid('', true) . rand()); }

    $temp_dir = __DIR__ . '/../adoptions/temp_admin';
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0755, true);
        @file_put_contents($temp_dir . '/.htaccess', "Deny from all\n");
    }

    $originalBaseName = pathinfo($fileName, PATHINFO_FILENAME);
    $shortBaseName = function_exists('mb_substr')
        ? mb_substr($originalBaseName, 0, 100, 'UTF-8')
        : substr($originalBaseName, 0, 100);
    $safe_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $shortBaseName);
    $stagedName = $token . '_' . $safe_name . '.' . $ext;
    $stagedPath = $temp_dir . '/' . $stagedName;

    // Assemble chunks directly into the staged file
    $fp = fopen($stagedPath, 'wb');
    if (!$fp) {
        die(json_encode(['success' => false, 'error' => 'Cannot create staged file']));
    }

    foreach ($chunks as $chunkFile) {
        $chunk_data = file_get_contents($chunkFile);
        if ($chunk_data === false) {
            fclose($fp);
            @unlink($stagedPath);
            die(json_encode(['success' => false, 'error' => 'Error reading chunk']));
        }
        fwrite($fp, $chunk_data);
    }
    fclose($fp);
    @chmod($stagedPath, 0644);

    // Clean up chunks
    foreach ($chunks as $chunkFile) {
        @unlink($chunkFile);
    }
    @rmdir($session_dir);

    // Metadata sidecar (matches admin-upload-temp.php's format)
    $meta = [
        'token'    => $token,
        'field'    => 'video',
        'original' => $fileName,
        'path'     => $stagedPath,
        'mime'     => 'video/' . $ext,
        'size'     => filesize($stagedPath),
        'created'  => time(),
    ];
    @file_put_contents($temp_dir . '/' . $token . '.json', json_encode($meta));

    // Return token info
    echo json_encode([
        'success' => true,
        'videoToken' => $token,
        'fileName' => $fileName,
        'message' => 'Upload completed and staged',
    ]);
    exit;
}

die(json_encode(['success' => false, 'error' => 'Invalid action']));
