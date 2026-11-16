<?php
/**
 * TASK-trans-9 START — Site-wide accessibility widget defaults endpoint.
 * Public GET lets each visitor load the admin-selected defaults; POST is
 * restricted to the existing admin token and writes to a protected data folder.
 * The widget keeps its existing pc_admin_settings_defaults localStorage contract.
 * TASK-trans-9 END
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$defaults = ['opacity' => 90, 'widgetOpacity' => 100];
$dataDir = __DIR__ . '/widget-defaults-data';
$dataFile = $dataDir . '/defaults.json';

function pcWidgetDefaultsRespond($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function pcWidgetDefaultsRead($file, $fallback) {
    if (!is_file($file) || !is_readable($file)) return $fallback;
    $raw = file_get_contents($file);
    $saved = json_decode($raw, true);
    if (!is_array($saved)) return $fallback;
    foreach (['opacity', 'widgetOpacity'] as $key) {
        if (!isset($saved[$key]) || !is_numeric($saved[$key])) return $fallback;
        $value = (float)$saved[$key];
        if ($value < 0 || $value > 100) return $fallback;
        $saved[$key] = (int)$value;
    }
    return ['opacity' => $saved['opacity'], 'widgetOpacity' => $saved['widgetOpacity']];
}

function pcWidgetDefaultsAdminAuthorized() {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) return false;
    $env = file_get_contents($envFile);
    if (!preg_match('/^ADMIN_TOKEN=(.+)$/m', $env, $match)) return false;
    $expected = trim($match[1], " \t\n\r\0\x0B\"'");
    $provided = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_POST['token'] ?? '');
    return $expected !== '' && is_string($provided) && $provided !== '' && hash_equals($expected, $provided);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    pcWidgetDefaultsRespond(['success' => true, 'defaults' => pcWidgetDefaultsRead($dataFile, $defaults)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    pcWidgetDefaultsRespond(['success' => false, 'error' => 'Method not allowed'], 405);
}

if (!pcWidgetDefaultsAdminAuthorized()) {
    pcWidgetDefaultsRespond(['success' => false, 'error' => 'Unauthorized'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) pcWidgetDefaultsRespond(['success' => false, 'error' => 'Invalid JSON body'], 400);
$next = [];
foreach (['opacity', 'widgetOpacity'] as $key) {
    if (!isset($input[$key]) || !is_numeric($input[$key])) {
        pcWidgetDefaultsRespond(['success' => false, 'error' => 'Both opacity values are required'], 422);
    }
    $value = (float)$input[$key];
    if ($value < 0 || $value > 100) {
        pcWidgetDefaultsRespond(['success' => false, 'error' => 'Opacity values must be between 0 and 100'], 422);
    }
    $next[$key] = (int)$value;
}

if (!is_dir($dataDir) && !@mkdir($dataDir, 0750, true) && !is_dir($dataDir)) {
    pcWidgetDefaultsRespond(['success' => false, 'error' => 'Unable to create protected settings folder'], 500);
}
$guardFile = $dataDir . '/.htaccess';
if (!is_file($guardFile) && file_put_contents($guardFile, "Require all denied\nDeny from all\n", LOCK_EX) === false) {
    pcWidgetDefaultsRespond(['success' => false, 'error' => 'Unable to protect settings folder'], 500);
}
@chmod($dataDir, 0750);
$tempFile = $dataDir . '/defaults.' . bin2hex(random_bytes(8)) . '.tmp';
$encoded = json_encode($next, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($tempFile, $encoded, LOCK_EX) === false || !@rename($tempFile, $dataFile)) {
    if (is_file($tempFile)) @unlink($tempFile);
    pcWidgetDefaultsRespond(['success' => false, 'error' => 'Unable to save widget defaults'], 500);
}
@chmod($dataFile, 0640);
pcWidgetDefaultsRespond(['success' => true, 'defaults' => $next]);
