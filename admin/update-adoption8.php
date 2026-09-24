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

$app_file = __DIR__ . "/../adoptions/applications/{$code}/application.json";
if (!file_exists($app_file)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Application not found']));
}

$application = json_decode(file_get_contents($app_file), true);

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
        $pet_file = __DIR__ . "/../adoptions/pets/{$category}/{$pet_id}/pet.json";
        if (file_exists($pet_file)) {
            $pet = json_decode(file_get_contents($pet_file), true);
            $pet['status'] = 'Adopted';
            file_put_contents($pet_file, json_encode($pet, JSON_PRETTY_PRINT));
        }
    }
} elseif ($status === 'REJECTED') {
    $application['status']          = 'REJECTED';
    $application['rejection_notes'] = $rejection_notes;
} else {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'status must be "APPROVED" or "REJECTED"']));
}

$application['updated_date'] = date('Y-m-d H:i:s');
file_put_contents($app_file, json_encode($application, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
?>
