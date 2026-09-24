<?php
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
session_start();

// Suppress PHP notices/warnings from corrupting JSON output
error_reporting(0);
@ini_set('display_errors', 0);

header('Content-Type: application/json');

// ── Auth ──────────────────────────────────────────────────────────────────────
function isAuthenticated() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return false;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) return false;
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_SESSION['admin_token'] ?? null));
    if ($provided && $provided === $admin_token) {
        $_SESSION['admin_token'] = $provided;
        return true;
    }
    return false;
}

// Detect oversized POST (files too large — PHP empties $_POST silently)
$content_length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
$post_max       = (int)ini_get('post_max_size') * 1024 * 1024;
if ($content_length > 0 && $post_max > 0 && $content_length > $post_max) {
    http_response_code(413);
    die(json_encode([
        'success' => false,
        'error'   => 'Upload too large. Increase post_max_size in php.ini (currently ' . ini_get('post_max_size') . ')',
    ]));
}

if (!isAuthenticated()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'Unauthorized — token mismatch or missing']));
}

$category = trim($_POST['category'] ?? '');
$pet_id   = trim($_POST['pet_id']   ?? '');

if (!$category || !$pet_id) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'category and pet_id are required']));
}

// Sanitize path components
$category = preg_replace('/[^a-z0-9\-]/', '', strtolower($category));
$pet_id   = preg_replace('/[^A-Za-z0-9\-]/', '', $pet_id);

$pet_folder = __DIR__ . "/../adoptions/pets/{$category}/{$pet_id}";
$pet_json   = $pet_folder . '/pet.json';

if (!is_dir($pet_folder)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => "Pet folder not found: {$category}/{$pet_id}"]));
}

if (!file_exists($pet_json)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => 'pet.json not found for this pet']));
}

// Load existing data
$pet_data = json_decode(file_get_contents($pet_json), true);
if (!is_array($pet_data)) {
    die(json_encode(['success' => false, 'error' => 'Corrupt pet.json — could not parse existing data']));
}

// ── Scalar text fields — always overwrite (even if empty, to allow clearing) ──
$text_fields = [
    'name', 'breed', 'species', 'size', 'gender', 'status',
    'description_short', 'description_full', 'health_info',
    'compatible_kids', 'compatible_dogs', 'compatible_cats', 'compatible_other_pets',
    'house_trained', 'spayed_neutered', 'vaccinated',
];
foreach ($text_fields as $f) {
    if (array_key_exists($f, $_POST)) {
        $pet_data[$f] = trim($_POST[$f]);
    }
}

// Numeric fields
if (array_key_exists('age_years', $_POST)) {
    $pet_data['age_years'] = floatval($_POST['age_years']);
}
if (array_key_exists('adoption_fee', $_POST)) {
    $pet_data['adoption_fee'] = floatval($_POST['adoption_fee']);
}

// Personality: comma string → array
if (array_key_exists('personality', $_POST)) {
    $raw = trim($_POST['personality']);
    $pet_data['personality'] = $raw === ''
        ? []
        : array_values(array_filter(array_map('trim', explode(',', $raw))));
}

// ── Single image ──────────────────────────────────────────────────────────────
$allowed_mime = ['image/jpeg', 'image/jpg', 'image/png'];

if (isset($_FILES['single_image']) && $_FILES['single_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['single_image'];
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($mime, $allowed_mime)) {
        die(json_encode(['success' => false, 'error' => 'Single image: invalid type ' . $mime]));
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) $ext = 'jpg';
    $filename = 'single_image.' . $ext;
    $dest     = $pet_folder . '/' . $filename;

    // Remove all old single_image.* files first
    $old_singles = glob($pet_folder . '/single_image.*');
    if ($old_singles) {
        foreach ($old_singles as $old_file) { @unlink($old_file); }
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        die(json_encode(['success' => false, 'error' => 'Failed to move single_image to: ' . $dest]));
    }

    $pet_data['single_image']     = $filename;
    $pet_data['single_image_url'] = "/adoptions/pets/{$category}/{$pet_id}/{$filename}";

} elseif (isset($_FILES['single_image']) && $_FILES['single_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    // An upload was attempted but failed (not just "no file sent")
    die(json_encode(['success' => false, 'error' => 'Single image upload error code: ' . $_FILES['single_image']['error']]));
}

// ── Video — replace if a new one was uploaded via chunked upload ─────────────
$video_token = trim($_POST['video_token'] ?? '');

if ($video_token) {
    $temp_dir = __DIR__ . '/../adoptions/temp_admin';
    $temp_files = glob($temp_dir . '/' . $video_token . '_*');

    if (empty($temp_files)) {
        die(json_encode(['success' => false, 'error' => 'Video upload expired or not found. Please re-upload the video.']));
    }

    $temp_file = $temp_files[0];
    $ext = strtolower(pathinfo($temp_file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v'])) $ext = 'mp4';
    $filename = 'pet_video.' . $ext;
    $dest     = $pet_folder . '/' . $filename;

    // Remove old video if exists
    $old_videos = glob($pet_folder . '/pet_video.*');
    if ($old_videos) {
        foreach ($old_videos as $old_file) { @unlink($old_file); }
    }

    if (!copy($temp_file, $dest)) {
        die(json_encode(['success' => false, 'error' => 'Failed to move video to: ' . $dest]));
    }
    @unlink($temp_file);
    @unlink($temp_dir . '/' . $video_token . '.json');

    $pet_data['video']     = $filename;
    $pet_data['video_url'] = "/adoptions/pets/{$category}/{$pet_id}/{$filename}";
}

// ── Bulk images — fully replace if any new ones sent ─────────────────────────
$has_bulk_upload = isset($_FILES['bulk_images'])
    && is_array($_FILES['bulk_images']['name'])
    && count($_FILES['bulk_images']['name']) > 0
    && !empty($_FILES['bulk_images']['name'][0]);

if ($has_bulk_upload) {
    $bulk_folder = $pet_folder . '/bulk_images';

    // Create folder if missing
    if (!is_dir($bulk_folder)) {
        if (!mkdir($bulk_folder, 0755, true)) {
            die(json_encode(['success' => false, 'error' => 'Could not create bulk_images folder']));
        }
    }

    // Delete ALL existing bulk images regardless of count
    $existing = glob($bulk_folder . '/*');
    if ($existing) {
        foreach ($existing as $old_file) { @unlink($old_file); }
    }

    $new_bulk  = [];
    $names     = $_FILES['bulk_images']['name'];
    $tmp_names = $_FILES['bulk_images']['tmp_name'];
    $errors    = $_FILES['bulk_images']['error'];
    $counter   = 1;

    for ($i = 0; $i < count($names); $i++) {
        if ($errors[$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($tmp_names[$i]);
        if (!in_array($mime, $allowed_mime)) continue;
        $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) $ext = 'jpg';
        $filename = 'image' . $counter . '.' . $ext;
        $dest     = $bulk_folder . '/' . $filename;
        if (move_uploaded_file($tmp_names[$i], $dest)) {
            $new_bulk[] = $filename;
            $counter++;
        }
    }

    // Update pet_data — even if new_bulk is empty (means all images removed)
    $pet_data['bulk_images']     = $new_bulk;
    $pet_data['bulk_images_url'] = !empty($new_bulk)
        ? "/adoptions/pets/{$category}/{$pet_id}/bulk_images/"
        : '';
}

// ── Write pet.json ─────────────────────────────────────────────────────────────
$pet_data['updated_date'] = date('Y-m-d H:i:s');

$json_str = json_encode($pet_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$written  = file_put_contents($pet_json, $json_str);

if ($written === false) {
    die(json_encode([
        'success' => false,
        'error'   => 'file_put_contents failed — check folder permissions on: ' . $pet_json,
    ]));
}

// ── Rebuild category index ────────────────────────────────────────────────────
$category_folder = __DIR__ . "/../adoptions/pets/{$category}";
$pet_ids = [];
foreach (scandir($category_folder) as $entry) {
    if ($entry === '.' || $entry === '..' || $entry === 'index.json') continue;
    $pf = $category_folder . '/' . $entry . '/pet.json';
    if (file_exists($pf)) {
        $d = json_decode(file_get_contents($pf), true);
        if (!empty($d['pet_id'])) $pet_ids[] = $d['pet_id'];
    }
}
file_put_contents(
    $category_folder . '/index.json',
    json_encode(['pets' => $pet_ids], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo json_encode(['success' => true, 'message' => 'Pet updated successfully', 'bytes_written' => $written]);
