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

$code             = $_POST['code']             ?? null;
$status           = $_POST['status']           ?? null;
$checkout_url     = $_POST['checkout_url']     ?? null;
$rejection_notes  = $_POST['rejection_notes']  ?? null;
// TASK-10 START: staff name + role entered on the admin Approve modal, stored alongside
// the application and read back by status.html to render "Approved by / Approved on".
$approved_by       = $_POST['approved_by']       ?? null;
$approved_position = $_POST['approved_position'] ?? null;
// TASK-10 END

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

/* BUGFIX-4 START — second companion fix, same root cause as delete-pet.php's.
   This function is used at approval time (below) to locate and mark a pet
   'Adopted' — it had the identical fixed-first-3-characters heuristic as
   delete-pet.php did, which would now silently return null for small-
   mammal pet IDs after the SM- prefix correction (see publish-pet.php),
   meaning an approved small-mammal adoption would stop updating the pet's
   status at all, with no error surfaced anywhere. This map was also never
   updated for Task 2's Livestocks/Aquariums categories in the first place,
   so those were already broken here before this fix too. Same root-cause
   fix as delete-pet.php: split on the actual dash to get the real, full
   prefix, and match full prefixes exactly against publish-pet.php's
   prefix_map (inverted), covering every category consistently instead of
   a subset that happened to survive a 3-character truncation. */
function getPetCategory($pet_id) {
    $map = ['DOG' => 'dogs', 'CAT' => 'cats', 'BIRD' => 'birds', 'REP' => 'reptiles', 'SM' => 'small-mammals', 'LVS' => 'livestocks', 'AQU' => 'aquariums'];
    $dashPos = strpos($pet_id, '-');
    $prefix = strtoupper($dashPos !== false ? substr($pet_id, 0, $dashPos) : substr($pet_id, 0, 3));
    return $map[$prefix] ?? null;
}
/* BUGFIX-4 END */

if ($status === 'APPROVED') {
    $application['status']       = 'APPROVED';
    $application['checkout_url'] = $checkout_url;
    // TASK-10 START: record who approved this application and when, in the exact keys
    // status.html expects (approved_by, approved_position, approved_at).
    $application['approved_by']       = $approved_by;
    $application['approved_position'] = $approved_position;
    $application['approved_at']       = date('Y-m-d H:i:s');
    // TASK-10 END
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
