<?php
/**
 * delete-application.php
 *
 * Permanently deletes one or more adoption application folders. No archive
 * option for applications — permanent delete only, per explicit instruction.
 * Uses the same admin auth pattern as the rest of the pet-management endpoints.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php'; // reuses chatRequireAdmin() for consistent auth

chatRequireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

$input = chatJsonInput();
if (empty($input)) $input = $_POST;

// Accept either a single applicationId or an array of them (bulk delete)
$ids = $input['applicationIds'] ?? ($input['applicationId'] ?? null);
if (is_string($ids)) $ids = [$ids];
if (!is_array($ids) || empty($ids)) {
    chatRespond(['success' => false, 'error' => 'applicationIds (array) or applicationId (string) required'], 400);
}

$appsBaseDir = realpath(__DIR__ . '/../adoptions/applications');
if (!$appsBaseDir) {
    chatRespond(['success' => false, 'error' => 'Applications directory not found on server'], 500);
}
$appsPrefix = rtrim($appsBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

function rrmdir($dir) {
    if (!is_dir($dir)) return false;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? rrmdir($path) : @unlink($path);
    }
    return @rmdir($dir);
}

$deleted = [];
$failed = [];

foreach ($ids as $id) {
    if (!is_scalar($id)) { $failed[] = $id; continue; }
    $safeId = preg_replace('/[^A-Za-z0-9\-]/', '', trim($id));
    if ($safeId === '') { $failed[] = $id; continue; }

    $targetDir = $appsBaseDir . '/' . $safeId;
    $realTarget = realpath($targetDir);

    if (!$realTarget || strpos($realTarget, $appsPrefix) !== 0) {
        $failed[] = $id;
        continue;
    }

    if (rrmdir($realTarget)) {
        $deleted[] = $safeId;
    } else {
        $failed[] = $id;
    }
}

chatRespond([
    'success' => count($failed) === 0,
    'deleted' => $deleted,
    'failed'  => $failed,
]);
