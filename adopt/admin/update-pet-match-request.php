<?php
/**
 * update-pet-match-request.php
 *
 * TASK-6b (Project 5: Pet-Match Request feature, Admin Detail Modal &
 * Actions, DETAIL-MODAL zone).
 *
 * New file — direct sibling of update-adoption.php (see
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md Section 9), reusing that endpoint's
 * exact shape (Rule 7): same token-validation style as
 * get-pet-match-requests.php (Task 6a), same read-modify-write-JSON flow
 * update-adoption.php uses for application.json/metadata.json, adapted to
 * this feature's single request.json file and its own status vocabulary
 * (FULFILLED/CLOSED in place of APPROVED/REJECTED — a Pet Match Request
 * has no adoption/checkout step, so those exact status names would be
 * misleading here; see the HTML comment on #viewPetMatchModal in
 * admin-dashboard.html for the full reasoning).
 *
 * Mirrors update-adoption.php's server-generated-timestamp lesson learned
 * (Section 9: "this file never wrote approved_at until this fix") by
 * writing fulfilled_at / closed_at itself, server-side, rather than
 * trusting a client-supplied date — avoiding that exact same class of bug
 * from the start instead of needing a later fix for it.
 */

header('Content-Type: application/json');

function pmuGetAdminToken(): ?string {
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) {
        return null;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'ADMIN_TOKEN=') === 0) {
            return substr(trim($line), strlen('ADMIN_TOKEN='));
        }
    }
    return null;
}

$storedToken = pmuGetAdminToken();
if ($storedToken === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '.env file missing or ADMIN_TOKEN not set']);
    exit;
}

$incomingToken = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null);
if ($incomingToken === null || !hash_equals($storedToken, $incomingToken)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$validCategories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];
$category = $_POST['category'] ?? '';
$requestCode = $_POST['request_code'] ?? '';
$status = strtoupper($_POST['status'] ?? '');

if (!in_array($category, $validCategories, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing category']);
    exit;
}
if ($requestCode === '') {
    echo json_encode(['success' => false, 'message' => 'Missing request_code']);
    exit;
}
if (!in_array($status, ['FULFILLED', 'CLOSED'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing status']);
    exit;
}

// BUGFIX-MANUS-04-2-START — /admin/ and /adoptions/ are sibling folders;
// the previous path incorrectly resolved to /admin/adoptions/.
$requestFile = __DIR__ . '/../adoptions/pet-match-requests/' . $category . '/' . $requestCode . '/request.json';
// BUGFIX-MANUS-04-2-END
if (!file_exists($requestFile)) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

$raw = file_get_contents($requestFile);
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Stored request data is corrupted']);
    exit;
}

$data['status'] = $status;

if ($status === 'FULFILLED') {
    $data['fulfilled_by'] = trim($_POST['fulfilled_by'] ?? '');
    $data['fulfilled_by_position'] = trim($_POST['fulfilled_by_position'] ?? '');
    $data['fulfilled_pet_note'] = trim($_POST['fulfilled_pet_note'] ?? '');
    $data['fulfilled_at'] = date('c');
} elseif ($status === 'CLOSED') {
    $data['closed_notes'] = trim($_POST['closed_notes'] ?? '');
    $data['closed_at'] = date('c');
}

$written = file_put_contents($requestFile, json_encode($data, JSON_PRETTY_PRINT));
if ($written === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to save updated request']);
    exit;
}

echo json_encode(['success' => true]);
