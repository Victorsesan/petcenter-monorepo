<?php
/**
 * Public Pet Match reference-image staging endpoint.
 *
 * The browser uploads one optional image before the request is submitted.
 * This endpoint deliberately stores the image outside the public page tree
 * and returns only an opaque token; the submit endpoint persists that token
 * with the request record. No client-supplied MIME type or filename is trusted.
 */

ob_start();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function pmUploadResponse(bool $ok, string $message, array $extra = [], int $status = 200): void {
    ob_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $ok, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    pmUploadResponse(false, 'POST required.', [], 405);
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$baseDir = __DIR__ . '/adoptions/pet-match-uploads-temp';
if (!is_dir($baseDir) && !mkdir($baseDir, 0750, true) && !is_dir($baseDir)) {
    pmUploadResponse(false, 'Server error: could not create upload storage.', [], 500);
}
$denyFile = $baseDir . '/.htaccess';
if (!is_file($denyFile)) {
    @file_put_contents($denyFile, "Require all denied\nDeny from all\n");
}

// A small file-backed limiter keeps this public endpoint independent of the
// chat MySQL database. The lock makes concurrent uploads count atomically.
$rateFile = $baseDir . '/.rate-' . hash('sha256', $ip) . '.json';
$rateHandle = @fopen($rateFile, 'c+');
if ($rateHandle === false) {
    pmUploadResponse(false, 'Server error: upload limiter unavailable.', [], 500);
}
flock($rateHandle, LOCK_EX);
$rateRaw = stream_get_contents($rateHandle);
$rate = json_decode($rateRaw ?: '', true);
if (!is_array($rate) || !isset($rate['window_start'], $rate['attempts']) || (int)$rate['window_start'] < time() - 600) {
    $rate = ['window_start' => time(), 'attempts' => 0];
}
$rate['attempts']++;
ftruncate($rateHandle, 0);
rewind($rateHandle);
fwrite($rateHandle, json_encode($rate));
fflush($rateHandle);
$rateExceeded = $rate['attempts'] > 10;
flock($rateHandle, LOCK_UN);
fclose($rateHandle);
if ($rateExceeded) {
    pmUploadResponse(false, 'Too many uploads. Please wait a few minutes and try again.', [], 429);
}

if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
    pmUploadResponse(false, 'No file received.', [], 400);
}
$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds the form upload limit.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file received.',
    ];
    pmUploadResponse(false, $messages[$file['error']] ?? 'File upload failed.', [], 400);
}

const PM_MAX_UPLOAD_BYTES = 5242880;
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    pmUploadResponse(false, 'Invalid upload.', [], 400);
}
if ((int)$file['size'] <= 0 || (int)$file['size'] > PM_MAX_UPLOAD_BYTES) {
    pmUploadResponse(false, 'File too large. Maximum size is 5MB.', [], 413);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($allowedMimes[$mime])) {
    pmUploadResponse(false, 'File type not allowed. Use JPG, PNG, or WEBP.', [], 415);
}
$originalName = (string)($file['name'] ?? 'reference-image');
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$extensionAliases = ['jpeg' => 'jpg'];
$extension = $extensionAliases[$extension] ?? $extension;
if ($extension !== $allowedMimes[$mime]) {
    pmUploadResponse(false, 'File extension does not match its content.', [], 415);
}

try {
    $token = bin2hex(random_bytes(24));
} catch (Throwable $e) {
    pmUploadResponse(false, 'Server error: could not create upload token.', [], 500);
}
$storedName = $token . '.' . $allowedMimes[$mime];
$storedPath = $baseDir . '/' . $storedName;
if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
    pmUploadResponse(false, 'Server error: could not save the upload.', [], 500);
}
@chmod($storedPath, 0640);
$metadata = [
    'token' => $token,
    'path' => $storedPath,
    'mime' => $mime,
    'size' => (int)$file['size'],
    'original_name' => $originalName,
    'created_at' => date('c'),
    'expires_at' => date('c', time() + 7200),
];
if (@file_put_contents($baseDir . '/' . $token . '.json', json_encode($metadata, JSON_PRETTY_PRINT), LOCK_EX) === false) {
    @unlink($storedPath);
    pmUploadResponse(false, 'Server error: could not record the upload.', [], 500);
}

// Opportunistic cleanup of staged files older than two hours. Never delete
// the current upload and never recurse outside this fixed directory.
if (random_int(1, 10) === 1) {
    $cutoff = time() - 7200;
    foreach (glob($baseDir . '/*') ?: [] as $candidate) {
        $name = basename($candidate);
        if ($name === '.htaccess' || strpos($name, '.rate-') === 0) continue;
        if (is_file($candidate) && @filemtime($candidate) < $cutoff) @unlink($candidate);
    }
}

pmUploadResponse(true, 'Uploaded.', [
    'token' => $token,
    'name' => $originalName,
    'size' => (int)$file['size'],
]);
