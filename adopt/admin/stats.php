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
    if ($provided === $admin_token) {
        $_SESSION['admin_token'] = $provided;
        return true;
    }
    return false;
}

if (!isAuthenticated()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// TASK-2 START — Livestocks & Aquariums added so their pets are counted in dashboard stats
$categories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];
// TASK-2 END
$pets_base  = __DIR__ . '/../adoptions/pets';
$apps_base  = __DIR__ . '/../adoptions/applications';
$pets_root_path = $pets_base;
$pets_root_real = realpath($pets_base);
$apps_root_real = realpath($apps_base);

$total_available = 0;
$total_adopted   = 0;

// TASK-9 START
// BUG FIX (Task 9a): index.json only ever holds a list of pet IDs
// (see ADMIN_DASHBOARD_PROJECT_SUMMARY.md, File Structure — "index.json
// (list of dog pet IDs)"), never full pet objects. The old code did
// `foreach ($data['pets'] as $pet) { $pet['status'] }` against that ID
// list, so $pet was always a bare ID string and ['status'] never
// existed — total_available/total_adopted were silently stuck at 0
// on every load. Fix: use index.json only for the list of pet IDs,
// then read each pet's own pet.json (same source get-pets.php already
// reads from) to get its real, current status.
// TASK-WEB-02-ADMIN-BASELINE START — constrain indexed IDs and filesystem reads to their canonical roots.
$category_prefixes = [
    'dogs' => ['DOG'], 'cats' => ['CAT'], 'birds' => ['BIRD'],
    'reptiles' => ['REP', 'REPTILE'], 'small-mammals' => ['SM', 'MAM'],
    'livestocks' => ['LVS'], 'aquariums' => ['AQU'],
];
foreach ($categories as $cat) {
    if ($pets_root_real === false || is_link($pets_root_path)) continue;
    $category_candidate = $pets_root_real . DIRECTORY_SEPARATOR . $cat;
    if (is_link($category_candidate)) continue;
    $category_real = realpath($category_candidate);
    if ($category_real === false || $category_real !== $category_candidate
        || strpos($category_real, rtrim($pets_root_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) !== 0) continue;
    $index = $category_real . DIRECTORY_SEPARATOR . 'index.json';
    $index_real = realpath($index);
    if (is_link($index) || $index_real === false
        || strpos($index_real, rtrim($category_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) !== 0) continue;
    $data = json_decode(file_get_contents($index_real), true);
    if (!is_array($data)) continue;
    $petIds = $data['pets'] ?? [];
    if (!is_array($petIds)) continue;
    foreach ($petIds as $petId) {
        if (!is_string($petId) || !preg_match('/^([A-Z]+)-\d{5}$/i', $petId, $idParts)
            || !in_array(strtoupper($idParts[1]), $category_prefixes[$cat], true)) continue;
        $pet_candidate = $category_real . DIRECTORY_SEPARATOR . $petId;
        if (is_link($pet_candidate)) continue;
        $pet_real = realpath($pet_candidate);
        $category_prefix = rtrim($category_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($pet_real === false || strpos($pet_real, $category_prefix) !== 0) continue;
        $petFile = $pet_real . DIRECTORY_SEPARATOR . 'pet.json';
        if (is_link($petFile)) continue;
        $petFileReal = realpath($petFile);
        if ($petFileReal === false || strpos($petFileReal, $category_prefix) !== 0 || !is_file($petFileReal)) continue;
        $pet = json_decode(file_get_contents($petFileReal), true);
        if (!is_array($pet)) continue;
        $status = $pet['status'] ?? '';
        if ($status === 'Available') $total_available++;
        if ($status === 'Adopted')   $total_adopted++;
    }
}
// TASK-9 END

$pending_count  = 0;
$approved_count = 0;
$rejected_count = 0;
$adopted_month  = 0;

if ($apps_root_real !== false) {
    $apps_prefix = rtrim($apps_root_real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    foreach (scandir($apps_root_real) as $dir) {
        if ($dir === '.' || $dir === '..' || !preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,63}$/', $dir)) continue;
        $app_candidate = $apps_root_real . DIRECTORY_SEPARATOR . $dir;
        if (is_link($app_candidate)) continue;
        $app_real = realpath($app_candidate);
        if ($app_real === false || strpos($app_real, $apps_prefix) !== 0) continue;
        $app_file = $app_real . DIRECTORY_SEPARATOR . 'application.json';
        if (is_link($app_file)) continue;
        $app_file_real = realpath($app_file);
        if ($app_file_real === false || strpos($app_file_real, $apps_prefix) !== 0 || !is_file($app_file_real)) continue;
        $app = json_decode(file_get_contents($app_file_real), true);
        if (!is_array($app)) continue;
        $status = $app['status'] ?? 'PENDING';
        if ($status === 'PENDING')   $pending_count++;
        if ($status === 'APPROVED')  $approved_count++;
        if ($status === 'REJECTED')  $rejected_count++;
        if ($status === 'APPROVED' && isset($app['updated_date'])) {
            if (date('Y-m', strtotime($app['updated_date'])) === date('Y-m')) {
                $adopted_month++;
            }
        }
    }
}

// TASK-9 START
// BUG FIX (Task 9a): this endpoint was nesting every field under a
// 'stats' key ({success, stats: {total_available, ...}}), but
// admin-dashboard.html's loadDashboardStats() reads the fields flat
// (stats.total_available, stats.pending_applications, etc.) — matching
// both the documented output shape in ADMIN_DASHBOARD_PROJECT_SUMMARY.md
// (Section 11) and the front-end's actual parsing code. Flattened here
// so the dashboard stat boxes actually populate instead of always
// falling back to their `|| 0` default.
echo json_encode([
    'success'                => true,
    'total_available'        => $total_available,
    'total_adopted'          => $total_adopted,
    'pending_applications'   => $pending_count,
    'approved_applications'  => $approved_count,
    'rejected_applications'  => $rejected_count,
    'adopted_this_month'     => $adopted_month,
]);
// TASK-9 END
?>
