<?php
/**
 * report-review-v2.php
 * Receives a "report this review" click from the v2 testimonials page.
 * Does NOT modify testimonials-v2.json and does NOT auto-moderate anything —
 * it simply appends one line to reports-v2.log for the admin to read later.
 * This mirrors the same safe, low-complexity pattern already used by
 * save-testimonial.php (append-only log, human reviews it afterward).
 *
 * POST fields: id (review id being reported), reason (free text)
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

define('REPORTS_LOG', __DIR__ . '/reports-v2.log');

$id     = trim($_POST['id'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if ($id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing review id']);
    exit;
}

// Keep the reason to a sane length — this is a free-text field from an
// anonymous visitor, so don't let it balloon the log file.
if (strlen($reason) > 500) {
    $reason = substr($reason, 0, 500) . '...';
}
if ($reason === '') {
    $reason = '(no reason given)';
}

$logLine = sprintf(
    "[%s] review_id=%s reason=%s ip=%s\n",
    date('c'),
    $id,
    str_replace(["\n", "\r"], ' ', $reason),
    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
);

$fp = fopen(REPORTS_LOG, 'a');
if (!$fp || !flock($fp, LOCK_EX)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not write report']);
    exit;
}
fwrite($fp, $logLine);
flock($fp, LOCK_UN);
fclose($fp);

http_response_code(200);
echo json_encode(['success' => true]);
