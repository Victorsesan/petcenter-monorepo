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

$category = $_POST['category'] ?? $_GET['category'] ?? null;
$pet_id   = $_POST['pet_id']   ?? $_GET['pet_id']   ?? null;

// TASK-WEB-02-ADMIN-BASELINE START — constrain request selectors before constructing storage paths.
$allowed_categories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];
if (!is_string($category) || trim($category) === '') {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'category is required']));
}
$category = trim($category);
if (!in_array($category, $allowed_categories, true)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid category']));
}
$category_prefixes = [
    'dogs' => ['DOG'], 'cats' => ['CAT'], 'birds' => ['BIRD'],
    'reptiles' => ['REP', 'REPTILE'], 'small-mammals' => ['SM', 'MAM'],
    'livestocks' => ['LVS'], 'aquariums' => ['AQU'],
];
if ($pet_id !== null) {
    if (!is_string($pet_id) || !preg_match('/^([A-Z]+)-\d{5}$/i', trim($pet_id), $pet_id_parts)
        || !in_array(strtoupper($pet_id_parts[1]), $category_prefixes[$category], true)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Invalid pet ID']));
    }
    $pet_id = strtoupper($pet_id_parts[1]) . '-' . substr(trim($pet_id), strrpos(trim($pet_id), '-') + 1);
}

$pets_base = __DIR__ . '/../adoptions/pets';
$pets_real = realpath($pets_base);
$category_candidate = $pets_real !== false ? $pets_real . DIRECTORY_SEPARATOR . $category : '';
$category_folder = $category_candidate !== '' ? realpath($category_candidate) : false;
if ($pets_real === false || is_link($pets_base) || $category_candidate === '' || is_link($category_candidate)
    || ($category_folder !== false && $category_folder !== $category_candidate)) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Pet category not found']));
}
if ($category_folder === false || !is_dir($category_folder)) {
    if ($pet_id) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Pet not found']));
    }
    echo json_encode([]);
    exit;
}
$category_prefix = rtrim($category_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
// TASK-WEB-02-ADMIN-BASELINE END

// Return single pet
if ($pet_id) {
    $pet_directory = $category_folder . DIRECTORY_SEPARATOR . $pet_id;
    $pet_file = $pet_directory . DIRECTORY_SEPARATOR . 'pet.json';
    $real_pet_directory = realpath($pet_directory);
    $real_pet_file = realpath($pet_file);
    if (is_link($pet_directory) || is_link($pet_file) || $real_pet_directory === false
        || strpos($real_pet_directory, $category_prefix) !== 0 || $real_pet_file === false
        || strpos($real_pet_file, $category_prefix) !== 0 || !is_file($real_pet_file)) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Pet not found']));
    }
    $pet = json_decode(file_get_contents($real_pet_file), true);
    // Ensure single_image_url is set
    if (!isset($pet['single_image_url']) && isset($pet['single_image']) && $pet['single_image']) {
        $pet['single_image_url'] = "/adoptions/pets/{$category}/{$pet_id}/" . $pet['single_image'];
    }
    echo json_encode(['success' => true, 'pet' => $pet]);
    exit;
}

// Return list of pets for category
$category_folder = $pets_base . "/{$category}";
$pets = [];
foreach (scandir($category_folder) as $folder) {
    if ($folder === '.' || $folder === '..' || $folder === 'index.json') continue;
    $pet_directory = $category_folder . DIRECTORY_SEPARATOR . $folder;
    $pet_file = $pet_directory . DIRECTORY_SEPARATOR . 'pet.json';
    $real_pet_directory = realpath($pet_directory);
    $real_pet_file = realpath($pet_file);
    if (is_link($pet_directory) || is_link($pet_file) || $real_pet_directory === false
        || strpos($real_pet_directory, $category_prefix) !== 0 || $real_pet_file === false
        || strpos($real_pet_file, $category_prefix) !== 0 || !is_file($real_pet_file)) continue;
    $pet_data = json_decode(file_get_contents($real_pet_file), true);
    if ($pet_data) {
        $pets[] = $pet_data;
        }
}

// Ensure all pets have pet_id and single_image_url
foreach ($pets as &$pet) {
    if (!isset($pet['pet_id']) && isset($pet['id'])) $pet['pet_id'] = $pet['id'];
    $pid = $pet['pet_id'] ?? $pet['id'] ?? '';
    if (!isset($pet['single_image_url']) && isset($pet['single_image']) && $pet['single_image'] && $pid) {
        $pet['single_image_url'] = "/adoptions/pets/{$category}/{$pid}/" . $pet['single_image'];
    }
}

// Return just the array (not wrapped) - matches dashboard expectation
echo json_encode($pets);
