<?php
/**
 * delete-pet-match-request.php
 *
 * TASK-6b (Project 5: Pet-Match Request feature, Admin Detail Modal &
 * Actions, DETAIL-MODAL zone).
 *
 * New file — direct sibling of delete-application.php (see
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md Section 10). Permanent delete only,
 * no archive option — same precedent Section 10 documents for adoption
 * applications ("archive exists only for chat threads; applications were
 * explicitly kept as delete-only per instruction"), reused here since a
 * Pet Match Request has the same shape of record and no archive concept
 * exists anywhere else in this dashboard for it either.
 *
 * Single-item delete only (not a bulk endpoint like delete-application.php
 * supports) — Task 6a's own listing left #pmBulkActionBar present but
 * wired with no action button yet ("no bulk action button is added yet
 * since Task 6a's scope is the listing/filter structure only"), and Task
 * 6b's own scope is the detail modal, not adding a bulk-delete button to
 * the listing — so this endpoint matches the one action Task 6b's modal
 * actually calls (single-request Remove), rather than building bulk-
 * delete support nothing in either task's scope asks for yet.
 */

header('Content-Type: application/json');

function pmdGetAdminToken(): ?string {
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

function pmdRemoveDir(string $dir): bool {
    if (!is_dir($dir)) {
        return false;
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            pmdRemoveDir($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

$storedToken = pmdGetAdminToken();
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

if (!in_array($category, $validCategories, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing category']);
    exit;
}
if ($requestCode === '') {
    echo json_encode(['success' => false, 'message' => 'Missing request_code']);
    exit;
}

// Reject path-traversal attempts in request_code before it touches the
// filesystem (a request_code should only ever be the PM-00000-0000
// pattern documented in Task-5b-Completion.md, but this endpoint deletes
// a folder, so it validates that shape defensively rather than trusting
// the client).
if (!preg_match('/^[A-Za-z0-9\-]+$/', $requestCode)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request_code format']);
    exit;
}

// BUGFIX-MANUS-04-3-START — /admin/ and /adoptions/ are sibling folders;
// the previous path incorrectly resolved to /admin/adoptions/.
$requestDir = __DIR__ . '/../adoptions/pet-match-requests/' . $category . '/' . $requestCode;
// BUGFIX-MANUS-04-3-END
if (!is_dir($requestDir)) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

if (!pmdRemoveDir($requestDir)) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete request']);
    exit;
}

echo json_encode(['success' => true]);
