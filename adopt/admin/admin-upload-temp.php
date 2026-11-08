<?php
// admin-upload-temp.php
// Admin dashboard image upload handler - identical to upload-temp.php
// Receives single file uploads immediately when user selects it.
// Saves to temp directory and returns a token the main submit can reference.
// Files are stored at full original quality — zero compression.

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

// Check authentication for admin
session_start();
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $matches);
    $admin_token = isset($matches[1]) ? trim($matches[1]) : null;
} else {
    $admin_token = null;
}
// TASK-WEB-02-ADMIN-BASELINE START — image staging is admin-only and must fail closed when auth is unconfigured.
if (!$admin_token) {
    http_response_code(500);
    out(false, 'Admin authentication is not configured.');
}
$provided = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_SESSION['admin_token'] ?? null);
if (!is_string($provided) || !hash_equals($admin_token, $provided)) {
    http_response_code(401);
    out(false, 'Unauthorized');
}
// TASK-WEB-02-ADMIN-BASELINE END

// Base upload directory for admin pet images
$UPLOAD_BASE = __DIR__ . '/../adoptions/';
$TEMP_DIR    = $UPLOAD_BASE . 'temp_admin';

$ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ALLOWED_MIME = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB per file for admin (higher than adoption form)

// Ensure temp dir exists
if (!is_dir($TEMP_DIR)) {
    if (!mkdir($TEMP_DIR, 0755, true)) out(false, 'Server error: cannot create temp folder.');
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

if ($f['size'] > $MAX_FILE_SIZE) out(false, 'File too large. Max 20MB per file.');

$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $ALLOWED_EXT)) out(false, 'File type not allowed. Use JPG, PNG, GIF or WebP.');

$mime = function_exists('mime_content_type') ? @mime_content_type($f['tmp_name']) : $f['type'];
if (!in_array($mime, $ALLOWED_MIME)) out(false, 'Invalid file content.');

// Generate token and save
try   { $token = bin2hex(random_bytes(16)); }
catch (Exception $e) { $token = md5(uniqid('', true) . rand()); }

$originalBaseName = pathinfo($f['name'], PATHINFO_FILENAME);
$shortBaseName = function_exists('mb_substr')
    ? mb_substr($originalBaseName, 0, 100, 'UTF-8')
    : substr($originalBaseName, 0, 100);
$safe_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $shortBaseName);
$filename  = $token . '_' . $safe_name . '.' . $ext;
$dest      = $TEMP_DIR . DIRECTORY_SEPARATOR . $filename;

if (!@move_uploaded_file($f['tmp_name'], $dest)) {
    out(false, 'Server error: could not save file. Check permissions on adoptions/temp_admin/');
}
@chmod($dest, 0644);

// Store metadata alongside the file
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

// Do not write the bearer upload token to logs; it can authorize later attachment of the staged file.
error_log("admin-upload-temp.php: saved {$field} file -> {$filename}");

out(true, 'Uploaded.', ['token' => $token, 'field' => $field, 'name' => $f['name'], 'size' => $f['size']]);
?>
