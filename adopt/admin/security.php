<?php
session_start();
header('Content-Type: application/json');

/**
 * SECURITY.PHP - Token Validation & Login Activity Logging
 *
 * action=validate  → heartbeat check (NO geolocation, fast)
 * action=login     → full login log with IP + geolocation
 * (no action)      → same as validate (legacy support)
 */

// ── Helpers ───────────────────────────────────────────────────────────────────

function getClientIP() {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

function getLocationFromIP($ip) {
    $url     = "http://ip-api.com/json/{$ip}?fields=status,country,continent,city,regionName,lat,lon,isp,query";
    $context = stream_context_create(['http' => ['timeout' => 4]]);
    $raw     = @file_get_contents($url, false, $context);
    if ($raw) {
        $d = json_decode($raw, true);
        if (!empty($d) && ($d['status'] ?? '') === 'success') {
            return [
                'country'    => $d['country']     ?? 'Unknown',
                'continent'  => $d['continent']   ?? 'Unknown',
                'city'       => $d['city']        ?? 'Unknown',
                'region'     => $d['regionName']  ?? 'Unknown',
                'latitude'   => $d['lat']         ?? 0,
                'longitude'  => $d['lon']         ?? 0,
                'isp'        => $d['isp']         ?? 'Unknown',
                'maps_link'  => "https://maps.google.com/?q={$d['lat']},{$d['lon']}",
            ];
        }
    }
    return [
        'country' => 'Unknown', 'continent' => 'Unknown', 'city' => 'Unknown',
        'region' => 'Unknown', 'latitude' => 0, 'longitude' => 0,
        'isp' => 'Unknown', 'maps_link' => 'https://maps.google.com/?q=0,0',
    ];
}

function getDeviceType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Mobile|Android|iPhone|iPod/', $ua)) return 'Mobile';
    if (preg_match('/iPad|Tablet/', $ua))               return 'Tablet';
    if (preg_match('/Windows/', $ua))                   return 'Windows Desktop';
    if (preg_match('/Macintosh|Mac OS/', $ua))          return 'Mac Desktop';
    if (preg_match('/Linux/', $ua))                     return 'Linux Desktop';
    return 'Unknown Device';
}

function getBrowserType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Edg\//', $ua))                              return 'Edge';
    if (preg_match('/OPR\/|Opera/', $ua))                       return 'Opera';
    if (preg_match('/Chrome\//', $ua))                          return 'Chrome';
    if (preg_match('/Safari\//', $ua) && !preg_match('/Chrome/', $ua)) return 'Safari';
    if (preg_match('/Firefox\//', $ua))                         return 'Firefox';
    if (preg_match('/MSIE|Trident/', $ua))                      return 'Internet Explorer';
    return 'Unknown Browser';
}

function logLoginActivity($token) {
    $log_file = __DIR__ . '/admin-activity.log';
    $ip       = getClientIP();
    $location = getLocationFromIP($ip);

    $entry = [
        'timestamp'  => date('Y-m-d H:i:s'),
        'token_hash' => substr(hash('sha256', $token), 0, 8),
        'ip_address' => $ip,
        'country'    => $location['country'],
        'continent'  => $location['continent'],
        'city'       => $location['city'],
        'region'     => $location['region'],
        'latitude'   => $location['latitude'],
        'longitude'  => $location['longitude'],
        'isp'        => $location['isp'],
        'maps_link'  => $location['maps_link'],
        'device'     => getDeviceType(),
        'browser'    => getBrowserType(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    ];

    $logs = [];
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?? [];
    }
    $logs[] = $entry;
    if (count($logs) > 10) array_shift($logs);
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    @chmod($log_file, 0600);
}

function readEnvToken() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) return null;
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    return isset($m[1]) ? trim($m[1]) : null;
}

// ── Router ────────────────────────────────────────────────────────────────────

$action         = $_POST['action'] ?? '';
$provided_token = $_POST['token']  ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null);
$current_token  = readEnvToken();

if (!$current_token) {
    http_response_code(500);
    die(json_encode(['valid' => false, 'message' => '.env not configured', 'code' => 'CONFIG_ERROR']));
}

if (!$provided_token) {
    http_response_code(401);
    die(json_encode(['valid' => false, 'message' => 'No token provided', 'code' => 'NO_TOKEN']));
}

if ($provided_token !== $current_token) {
    http_response_code(401);
    die(json_encode([
        'valid'   => false,
        'message' => 'Token mismatch — session expired. Please log in again.',
        'code'    => 'SESSION_EXPIRED',
        'action'  => 'logout',
    ]));
}

// Token is valid — decide what to do based on action
if ($action === 'login') {
    // Full login: write activity log (geolocation included — only on actual login)
    logLoginActivity($provided_token);
    $_SESSION['admin_token']    = $provided_token;
    $_SESSION['last_activity']  = time();
    http_response_code(200);
    die(json_encode(['valid' => true, 'message' => 'Login logged', 'code' => 'OK']));
}

// action=validate or any other: fast heartbeat, NO geolocation, NO file write
$_SESSION['admin_token']   = $provided_token;
$_SESSION['last_activity'] = time();
http_response_code(200);
echo json_encode(['valid' => true, 'message' => 'OK', 'code' => 'OK']);
