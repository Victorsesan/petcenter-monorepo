<?php
/**
 * Upload Handler for Contact Form
 * For: docs.petcenterco.com/contact-us/upload-temp.php
 * 
 * Receives file uploads, validates, stores temporarily, returns token
 * Token used to track image through form submission → final delivery
 */

require_once __DIR__ . '/contact-config.php';

header('Content-Type: application/json');

// ============================================
// SECURITY: Check HTTP method & CORS
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Allow CORS for contact form submission
header('Access-Control-Allow-Origin: https://docs.petcenterco.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================
// VALIDATE REQUEST
// ============================================
if (empty($_FILES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No files provided']);
    exit;
}

$file = $_FILES['file'] ?? null;
if (!$file) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File field missing']);
    exit;
}

// ============================================
// VALIDATE FILE
// ============================================
$result = [
    'success' => false,
    'token' => null,
    'field' => 'file',
    'name' => null,
    'size' => null,
    'message' => ''
];

// Check upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds max size (php.ini)',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form max size',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file selected',
        UPLOAD_ERR_NO_TMP_DIR => 'Temp directory missing',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by PHP extension'
    ];
    $result['message'] = $errors[$file['error']] ?? 'Unknown upload error';
    http_response_code(400);
    echo json_encode($result);
    exit;
}

// Check file size (5 MB max)
$maxSize = UPLOAD_MAX_SIZE_MB * 1024 * 1024;
if ($file['size'] > $maxSize) {
    $result['message'] = 'File too large (' . round($file['size'] / 1024 / 1024, 1) . ' MB). Max: ' . UPLOAD_MAX_SIZE_MB . ' MB';
    http_response_code(413);
    echo json_encode($result);
    exit;
}

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES)) {
    $result['message'] = 'Invalid file type. Allowed: JPG, PNG only. Got: ' . $mimeType;
    http_response_code(415);
    echo json_encode($result);
    exit;
}

// Check file extension
$pathInfo = pathinfo($file['name']);
$ext = strtolower($pathInfo['extension'] ?? '');
if (!in_array($ext, UPLOAD_ALLOWED_EXT)) {
    $result['message'] = 'Invalid file extension. Allowed: ' . implode(', ', UPLOAD_ALLOWED_EXT);
    http_response_code(415);
    echo json_encode($result);
    exit;
}

// Check filename for security
$originalName = basename($file['name']);
if (preg_match('/[^a-zA-Z0-9._-]/', $originalName)) {
    // Sanitize to safe name
    $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
}

// ============================================
// GENERATE TOKEN & STORE TEMP FILE
// ============================================
$token = generateHexToken(32);
$tempDir = UPLOAD_TEMP_DIR;

// Ensure temp dir exists and is secure
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Create .htaccess to block direct access
$htaccess = $tempDir . '.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "deny from all\n");
}

// Store temp file with token name
$tempFileName = $token . '.' . $ext;
$tempFilePath = $tempDir . $tempFileName;

if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
    $result['message'] = 'Failed to save file';
    http_response_code(500);
    echo json_encode($result);
    exit;
}

// ============================================
// STORE METADATA
// ============================================
$metadata = [
    'token' => $token,
    'original_name' => $originalName,
    'mime_type' => $mimeType,
    'size' => $file['size'],
    'extension' => $ext,
    'uploaded_at' => date('Y-m-d H:i:s'),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'expires_at' => date('Y-m-d H:i:s', time() + 7200) // 2 hours
];

$metadataFile = $tempDir . $token . '.json';
file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// ============================================
// SUCCESS RESPONSE
// ============================================
$result['success'] = true;
$result['token'] = $token;
$result['name'] = $originalName;
$result['size'] = $file['size'];
$result['message'] = 'File uploaded successfully';

http_response_code(200);
echo json_encode($result);

// ============================================
// CLEANUP: Remove expired temp files (1% chance per request)
// ============================================
if (rand(1, 100) <= 1) {
    $files = @glob($tempDir . '*.json');
    foreach ($files as $metaFile) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if (!empty($meta['expires_at'])) {
            $expiresAt = strtotime($meta['expires_at']);
            if ($expiresAt < time()) {
                @unlink($metaFile);
                $imgFile = str_replace('.json', '.*', $metaFile);
                array_map('unlink', glob($imgFile));
            }
        }
    }
}
?>
