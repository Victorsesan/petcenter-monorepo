<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$env_file = __DIR__ . '/.env';
if (!file_exists($env_file)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => '.env file not found at: ' . $env_file]));
}

$env_content = file_get_contents($env_file);
preg_match('/ADMIN_TOKEN=(.+)/', $env_content, $matches);
$admin_token = isset($matches[1]) ? trim($matches[1]) : null;

if (!$admin_token) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'ADMIN_TOKEN not set in .env']));
}

$provided_token = $_POST['token'] ?? null;
if (!$provided_token) {
    $body           = json_decode(file_get_contents('php://input'), true);
    $provided_token = $body['token'] ?? null;
}

if (!$provided_token) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No token provided in request']));
}

if ($provided_token !== $admin_token) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Invalid token']));
}

// Valid — set session and trigger login log via security.php
$_SESSION['admin_token']   = $provided_token;
$_SESSION['last_activity'] = time();

// Fire login activity log asynchronously via include (same process, no extra HTTP request)
$_POST['action'] = 'login';
// We include security.php logic inline to avoid output buffering issues
// Instead: write log directly here to keep it simple and reliable

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
    $context = stream_context_create(['http' => ['timeout' => 4]]);
    $raw = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,continent,city,regionName,lat,lon,isp", false, $context);
    if ($raw) {
        $d = json_decode($raw, true);
        if (!empty($d) && ($d['status'] ?? '') === 'success') {
            return [
                'country'   => $d['country']    ?? 'Unknown',
                'continent' => $d['continent']  ?? 'Unknown',
                'city'      => $d['city']       ?? 'Unknown',
                'region'    => $d['regionName'] ?? 'Unknown',
                'latitude'  => $d['lat']        ?? 0,
                'longitude' => $d['lon']        ?? 0,
                'isp'       => $d['isp']        ?? 'Unknown',
                'maps_link' => "https://maps.google.com/?q={$d['lat']},{$d['lon']}",
            ];
        }
    }
    return ['country'=>'Unknown','continent'=>'Unknown','city'=>'Unknown','region'=>'Unknown','latitude'=>0,'longitude'=>0,'isp'=>'Unknown','maps_link'=>'https://maps.google.com/?q=0,0'];
}

function getDeviceType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Mobile|Android|iPhone|iPod/', $ua)) return 'Mobile';
    if (preg_match('/iPad|Tablet/', $ua))               return 'Tablet';
    if (preg_match('/Windows/', $ua))                   return 'Windows Desktop';
    if (preg_match('/Macintosh|Mac OS/', $ua))          return 'Mac Desktop';
    return 'Unknown';
}

function getBrowserType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Edg\//', $ua))    return 'Edge';
    if (preg_match('/OPR\//', $ua))    return 'Opera';
    if (preg_match('/Chrome\//', $ua)) return 'Chrome';
    if (preg_match('/Firefox\//', $ua))return 'Firefox';
    if (preg_match('/Safari\//', $ua)) return 'Safari';
    return 'Unknown';
}

$log_file = __DIR__ . '/admin-activity.log';
$ip       = getClientIP();
$location = getLocationFromIP($ip);

$entry = [
    'timestamp'  => date('Y-m-d H:i:s'),
    'token_hash' => substr(hash('sha256', $provided_token), 0, 8),
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
// Deduplicate: don't log if same IP logged within last 60 seconds
$now      = time();
$last_ip  = end($logs)['ip_address'] ?? '';
$last_ts  = end($logs)['timestamp']  ?? '';
$seconds_ago = $last_ts ? ($now - strtotime($last_ts)) : 999;
if ($last_ip !== $ip || $seconds_ago > 60) {
    $logs[] = $entry;
    if (count($logs) > 10) array_shift($logs);
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    @chmod($log_file, 0600);
}

echo json_encode(['success' => true, 'message' => 'Authenticated']);
