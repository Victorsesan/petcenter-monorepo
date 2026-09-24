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
foreach ($categories as $cat) {
    $index = $pets_base . '/' . $cat . '/index.json';
    if (!file_exists($index)) continue;
    $data = json_decode(file_get_contents($index), true);
    $petIds = $data['pets'] ?? [];
    foreach ($petIds as $petId) {
        // $petId is a plain ID string (e.g. "DOG-00001") — sanitize before
        // building a filesystem path with it.
        $petId = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $petId);
        if ($petId === '') continue;
        $petFile = $pets_base . '/' . $cat . '/' . $petId . '/pet.json';
        if (!file_exists($petFile)) continue;
        $pet = json_decode(file_get_contents($petFile), true);
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

if (is_dir($apps_base)) {
    foreach (scandir($apps_base) as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        $app_file = $apps_base . '/' . $dir . '/application.json';
        if (!file_exists($app_file)) continue;
        $app = json_decode(file_get_contents($app_file), true);
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
