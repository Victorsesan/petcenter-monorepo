<?php
// upload-temp.php
// Receives a single file upload immediately when the user selects it.
// Saves to a temp directory and returns a token the main submit can reference.
// Files are stored at full original quality — zero compression.
// Tokens expire after 2 hours (cleaned up here on each request).

ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server error.']);
    }
});

function out($ok, $msg, $extra = []) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => (bool)$ok, 'message' => $msg], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'POST required.');

// Load config for UPLOAD_BASE
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

$UPLOAD_BASE = defined('UPLOAD_BASE') ? UPLOAD_BASE : __DIR__ . '/uploads/adoption-applications/';
$TEMP_DIR    = rtrim($UPLOAD_BASE, '/\\') . DIRECTORY_SEPARATOR . 'temp';

$ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'pdf'];
$ALLOWED_MIME = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
$MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per individual file

// Ensure temp dir exists
if (!is_dir($TEMP_DIR)) {
    if (!mkdir($TEMP_DIR, 0755, true)) out(false, 'Server error: cannot create temp folder.');
    // Protect temp dir from direct web access
    file_put_contents($TEMP_DIR . '/.htaccess', "Deny from all\n");
}

// Clean up expired temp files (older than 2 hours) — runs probabilistically, 1 in 10 requests
if (rand(1, 10) === 1) {
    foreach (glob($TEMP_DIR . DIRECTORY_SEPARATOR . '*.json') ?: [] as $meta_file) {
        if (filemtime($meta_file) < time() - 7200) {
            $meta = json_decode(file_get_contents($meta_file), true);
            if ($meta && !empty($meta['path']) && file_exists($meta['path'])) {
                @unlink($meta['path']);
            }
            @unlink($meta_file);
        }
    }
}

// Validate file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $code = isset($_FILES['file']['error']) ? $_FILES['file']['error'] : -1;
    out(false, 'File upload error (code ' . $code . ').');
}

$f    = $_FILES['file'];
$field = isset($_POST['field']) ? preg_replace('/[^a-z_]/', '', $_POST['field']) : 'unknown';

if ($f['size'] > $MAX_FILE_SIZE) out(false, 'File too large. Max 10MB per file.');

$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $ALLOWED_EXT)) out(false, 'File type not allowed. Use JPG, PNG or PDF.');

$mime = function_exists('mime_content_type') ? @mime_content_type($f['tmp_name']) : $f['type'];
if (!in_array($mime, $ALLOWED_MIME)) out(false, 'Invalid file content.');

// Generate token and save
try   { $token = bin2hex(random_bytes(16)); }
catch (Exception $e) { $token = md5(uniqid('', true) . rand()); }

$safe_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', mb_substr(pathinfo($f['name'], PATHINFO_FILENAME), 0, 100));
$filename  = $token . '_' . $safe_name . '.' . $ext;
$dest      = $TEMP_DIR . DIRECTORY_SEPARATOR . $filename;

if (!@move_uploaded_file($f['tmp_name'], $dest)) {
    out(false, 'Server error: could not save file. Check permissions on uploads/adoption-applications/temp/');
}
@chmod($dest, 0644);

// Store metadata alongside the file (same token prefix, .json extension)
$meta = [
    'token'    => $token,
    'field'    => $field,
    'original' => $f['name'],
    'path'     => $dest,
    'mime'     => $mime,
    'size'     => $f['size'],
    'created'  => time(),
];
file_put_contents($TEMP_DIR . DIRECTORY_SEPARATOR . $token . '.json', json_encode($meta));

error_log("upload-temp.php: saved {$field} file -> {$filename} (token={$token})");

out(true, 'Uploaded.', ['token' => $token, 'field' => $field, 'name' => $f['name'], 'size' => $f['size']]);
