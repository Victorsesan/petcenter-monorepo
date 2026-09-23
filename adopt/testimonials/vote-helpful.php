<?php
/**
 * vote-helpful.php
 * Persists a thumbs up/down "helpful" vote against a specific review id,
 * writing directly into testimonials.json (unlike save-testimonial.php,
 * which only logs new reviews to testimonials.log — votes against EXISTING
 * admin-curated reviews are a different concern and are meant to persist
 * for real).
 *
 * POST fields: id (int), vote ("yes" | "no")
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

define('TESTIMONIALS_FILE', __DIR__ . '/testimonials.json');

$id   = intval($_POST['id'] ?? 0);
$vote = trim($_POST['vote'] ?? '');

if (!$id || !in_array($vote, ['yes', 'no'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid vote payload']);
    exit;
}

if (!file_exists(TESTIMONIALS_FILE)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Testimonials data file not found']);
    exit;
}

// Open for read+write and lock immediately to avoid race conditions
// if two people vote at nearly the same moment.
$fp = fopen(TESTIMONIALS_FILE, 'r+');
if (!$fp || !flock($fp, LOCK_EX)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not lock testimonials file']);
    exit;
}

$raw  = stream_get_contents($fp);
$data = json_decode($raw, true);

if (!$data || !isset($data['testimonials'])) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Invalid testimonials format']);
    exit;
}

$found = false;
$updatedYes = 0;
$updatedNo  = 0;

foreach ($data['testimonials'] as &$t) {
    if ((int)$t['id'] === $id) {
        $t['helpfulYes'] = (int)($t['helpfulYes'] ?? 0);
        $t['helpfulNo']  = (int)($t['helpfulNo']  ?? 0);

        if ($vote === 'yes') {
            $t['helpfulYes'] += 1;
        } else {
            $t['helpfulNo'] += 1;
        }

        $updatedYes = $t['helpfulYes'];
        $updatedNo  = $t['helpfulNo'];
        $found = true;
        break;
    }
}
unset($t);

if (!$found) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Review not found']);
    exit;
}

$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, $encoded);
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

http_response_code(200);
echo json_encode([
    'success'    => true,
    'id'         => $id,
    'helpfulYes' => $updatedYes,
    'helpfulNo'  => $updatedNo
]);
