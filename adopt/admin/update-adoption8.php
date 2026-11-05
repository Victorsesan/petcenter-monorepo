<?php
session_start();
header('Content-Type: application/json');

function isAuthenticated() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return false;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) return false;
    $provided = $_POST['token'] ?? null;
    if ($provided === $admin_token) { $_SESSION['admin_token'] = $provided; return true; }
    return false;
}

if (!isAuthenticated()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$code            = $_POST['code']            ?? null;
$status          = $_POST['status']          ?? null;
$checkout_url    = $_POST['checkout_url']    ?? null;
$rejection_notes = $_POST['rejection_notes'] ?? null;

if (!$code || !$status) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'code and status are required']));
}

if (!is_scalar($code) || !is_scalar($status) || !preg_match('/^AD-\d{5}-\d{4}$/', (string)$code)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'invalid application code']));
}
$code = (string)$code;
$status = (string)$status;
if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'status must be "APPROVED" or "REJECTED"']));
}

$apps_base = realpath(__DIR__ . '/../adoptions/applications');
$app_dir = $apps_base === false ? false : realpath($apps_base . DIRECTORY_SEPARATOR . $code);
$apps_prefix = $apps_base === false ? '' : rtrim($apps_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if ($app_dir === false || strpos($app_dir, $apps_prefix) !== 0) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Application not found']));
}
$app_file = $app_dir . DIRECTORY_SEPARATOR . 'application.json';
if (!is_file($app_file) || !is_readable($app_file)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Application not found']));
}

$application = json_decode(file_get_contents($app_file), true);
if (!is_array($application)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Application data is invalid']));
}

function getPetCategory($pet_id) {
    $map = ['DOG' => 'dogs', 'CAT' => 'cats', 'BIR' => 'birds', 'REP' => 'reptiles', 'MAM' => 'small-mammals'];
    return $map[strtoupper(substr($pet_id, 0, 3))] ?? null;
}

if ($status === 'APPROVED') {
    $application['status']       = 'APPROVED';
    $application['checkout_url'] = $checkout_url;
    $pet_id   = $application['pet_id'] ?? null;
    $category = $pet_id ? getPetCategory($pet_id) : null;
    if ($category && $pet_id) {
        $pets_base = realpath(__DIR__ . '/../adoptions/pets');
        $safe_pet_id = is_string($pet_id) && preg_match('/^[A-Za-z0-9]+-[A-Za-z0-9_-]+$/', $pet_id);
        $pet_dir = ($pets_base !== false && $safe_pet_id)
            ? realpath($pets_base . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $pet_id)
            : false;
        $pets_prefix = $pets_base === false ? '' : rtrim($pets_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $pet_file = $pet_dir !== false && strpos($pet_dir, $pets_prefix) === 0
            ? $pet_dir . DIRECTORY_SEPARATOR . 'pet.json'
            : false;
        if ($pet_file !== false && is_file($pet_file) && is_readable($pet_file)) {
            $pet = json_decode(file_get_contents($pet_file), true);
            if (is_array($pet)) {
                $pet['status'] = 'Adopted';
                file_put_contents($pet_file, json_encode($pet, JSON_PRETTY_PRINT), LOCK_EX);
            }
        }
    }
} elseif ($status === 'REJECTED') {
    $application['status']          = 'REJECTED';
    $application['rejection_notes'] = $rejection_notes;
}

$application['updated_date'] = date('Y-m-d H:i:s');
file_put_contents($app_file, json_encode($application, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
?>
