<?php
// TASK-WEB-02-ADMIN-BASELINE START — restore the dashboard's missing delete-pet route safely.
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

/** Return one JSON response without exposing server paths. */
function petDeleteRespond(array $payload, int $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

/** Remove a verified pet directory without following symlinks. */
function petDeleteTree(string $directory): bool
{
    try {
        $items = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
        foreach ($items as $item) {
            $path = $item->getPathname();
            if ($item->isLink() || $item->isFile()) {
                if (!@unlink($path)) return false;
            } elseif ($item->isDir()) {
                if (!petDeleteTree($path)) return false;
            } else {
                return false;
            }
        }
    } catch (UnexpectedValueException $exception) {
        return false;
    }

    return @rmdir($directory);
}

/** Require the existing shared admin token for this state-changing action. */
function petDeleteIsAuthorized(): bool
{
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) return false;
    $envContent = file_get_contents($envFile);
    if (!is_string($envContent) || !preg_match('/^ADMIN_TOKEN=(.+)$/m', $envContent, $matches)) return false;

    $expected = trim($matches[1]);
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_SESSION['admin_token'] ?? ''));
    return $expected !== '' && is_string($provided) && hash_equals($expected, $provided);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    petDeleteRespond(['success' => false, 'message' => 'POST required'], 405);
}

if (!petDeleteIsAuthorized()) {
    petDeleteRespond(['success' => false, 'message' => 'Unauthorized'], 401);
}

$petId = $_POST['pet_id'] ?? null;
if (!is_string($petId) || !preg_match('/^([A-Z]+)-(\d{5})$/i', trim($petId), $idParts)) {
    petDeleteRespond(['success' => false, 'message' => 'Invalid pet ID'], 400);
}
$petId = strtoupper($idParts[1]) . '-' . $idParts[2];

// Canonical IDs plus the two documented historical prefixes remain deletable.
$categoryByPrefix = [
    'DOG' => 'dogs',
    'CAT' => 'cats',
    'BIRD' => 'birds',
    'REP' => 'reptiles',
    'REPTILE' => 'reptiles',
    'SM' => 'small-mammals',
    'MAM' => 'small-mammals',
    'LVS' => 'livestocks',
    'AQU' => 'aquariums',
];
$prefix = strtoupper($idParts[1]);
if (!isset($categoryByPrefix[$prefix])) {
    petDeleteRespond(['success' => false, 'message' => 'Unknown pet ID format'], 400);
}
$category = $categoryByPrefix[$prefix];

$petsRootPath = __DIR__ . '/../adoptions/pets';
$petsRoot = realpath($petsRootPath);
if ($petsRoot === false || is_link($petsRootPath) || !is_dir($petsRoot)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet storage is unavailable'], 500);
}
$rootPrefix = rtrim($petsRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$categoryCandidate = $petsRoot . DIRECTORY_SEPARATOR . $category;
$categoryPath = realpath($categoryCandidate);
if (is_link($categoryCandidate) || $categoryPath === false || $categoryPath !== $categoryCandidate || strpos($categoryPath, $rootPrefix) !== 0 || !is_dir($categoryPath)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet not found'], 404);
}
$categoryPrefix = rtrim($categoryPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$targetCandidate = $categoryPath . DIRECTORY_SEPARATOR . $petId;
if (is_link($targetCandidate)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet not found'], 404);
}
$targetPath = realpath($targetCandidate);
if ($targetPath === false || strpos($targetPath, $categoryPrefix) !== 0 || !is_dir($targetPath)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet not found'], 404);
}

if (!petDeleteTree($targetPath)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet could not be deleted'], 500);
}

// Rebuild the existing {pets: [...]} category index from remaining safe folder names.
$categoryPrefixes = [
    'dogs' => ['DOG'],
    'cats' => ['CAT'],
    'birds' => ['BIRD'],
    'reptiles' => ['REP', 'REPTILE'],
    'small-mammals' => ['SM', 'MAM'],
    'livestocks' => ['LVS'],
    'aquariums' => ['AQU'],
];
$petIds = [];
foreach (new FilesystemIterator($categoryPath, FilesystemIterator::SKIP_DOTS) as $item) {
    if (!$item->isDir() || $item->isLink()) continue;
    $entry = $item->getFilename();
    foreach ($categoryPrefixes[$category] as $allowedPrefix) {
        if (preg_match('/^' . preg_quote($allowedPrefix, '/') . '-\d{5}$/i', $entry)) {
            $petIds[] = $entry;
            break;
        }
    }
}
sort($petIds, SORT_NATURAL | SORT_FLAG_CASE);
$indexJson = json_encode(['pets' => $petIds], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($indexJson)) {
    petDeleteRespond(['success' => false, 'message' => 'Pet was deleted but the category index could not be prepared'], 500);
}
try {
    $temporaryIndex = $categoryPath . DIRECTORY_SEPARATOR . '.index-' . bin2hex(random_bytes(8)) . '.tmp';
} catch (Throwable $exception) {
    petDeleteRespond(['success' => false, 'message' => 'Pet was deleted but the category index could not be prepared'], 500);
}
if (file_put_contents($temporaryIndex, $indexJson, LOCK_EX) === false || !@rename($temporaryIndex, $categoryPath . DIRECTORY_SEPARATOR . 'index.json')) {
    @unlink($temporaryIndex);
    petDeleteRespond(['success' => false, 'message' => 'Pet was deleted but the category index could not be updated'], 500);
}

petDeleteRespond(['success' => true, 'message' => 'Pet deleted successfully']);
// TASK-WEB-02-ADMIN-BASELINE END
?>
