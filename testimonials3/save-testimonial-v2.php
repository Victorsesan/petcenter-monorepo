<?php
/**
 * save-testimonial.php
 * Receives POST from the review submission form.
 * Validates, generates metadata (including a live ISO postedAt timestamp),
 * and appends a line to testimonials-v2.log as an audit trail.
 *
 * NOTE: This intentionally does NOT write to testimonials-v2.json.
 * testimonials-v2.json is admin-curated only; newly submitted reviews appear
 * on the page for the current session (pushed into the in-memory array)
 * but disappear on a hard refresh, per the site's existing architecture.
 *
 * POST fields: name, email, rating (1-5), text
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

define('TESTIMONIALS_FILE', __DIR__ . '/testimonials-v2.json');

$name   = trim(strip_tags($_POST['name'] ?? ''));
$email  = trim(strip_tags($_POST['email'] ?? ''));
$rating = intval($_POST['rating'] ?? 0);
$text   = trim(strip_tags($_POST['text'] ?? ''));

if (empty($name) || strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
    exit;
}
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}
if (empty($text) || strlen($text) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Testimonial must be at least 10 characters']);
    exit;
}
if (strlen($text) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Testimonial must be under 1000 characters']);
    exit;
}

if (!file_exists(TESTIMONIALS_FILE)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Testimonials data file not found']);
    exit;
}

$json_content = file_get_contents(TESTIMONIALS_FILE);
$data = json_decode($json_content, true);
if (!$data || !isset($data['testimonials'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Invalid testimonials format']);
    exit;
}

$name_parts    = explode(' ', trim($name));
$first_initial = strtoupper(substr($name_parts[0], 0, 1));
$last_initial  = strtoupper(substr(end($name_parts), 0, 1));
$initials      = $first_initial . $last_initial;

$colors = [5 => '#10b981', 4 => '#0ea5e9', 3 => '#9ca3af', 2 => '#ef4444', 1 => '#dc2626'];
$color  = $colors[$rating] ?? '#6b7280';

$existing_ids = array_column($data['testimonials'], 'id');
$next_id      = empty($existing_ids) ? 1 : (max($existing_ids) + 1);

$new_testimonial = [
    'id'         => $next_id,
    'name'       => $name,
    'email'      => $email,
    'postedAt'   => date('c'),   // ISO 8601 with timezone offset, e.g. 2026-07-06T23:22:13-07:00
    'rating'     => $rating,
    'text'       => $text,
    'initials'   => $initials,
    'color'      => $color,
    'helpfulYes' => 0,
    'helpfulNo'  => 0,
    'reply'      => null
];

// Save to .log file only (audit trail) — do NOT write to testimonials-v2.json
// testimonials-v2.json is managed by admin only (curated content)
$log_file = __DIR__ . '/testimonials-v2.log';
$log_entry = date('Y-m-d H:i:s') . ' | Name: ' . $name . ' | Email: ' . $email . ' | Rating: ' . $rating . ' | Text: ' . substr($text, 0, 100) . '...' . PHP_EOL;

$result = file_put_contents($log_file, $log_entry, FILE_APPEND);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save. Check file permissions.']);
    exit;
}

http_response_code(201);
echo json_encode([
    'success'     => true,
    'message'     => 'Thank you for your testimonial!',
    'testimonial' => $new_testimonial
]);
?>
