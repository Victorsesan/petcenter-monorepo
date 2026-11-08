<?php
/** TASK-WEB-04 START — Start Google OpenID Connect login without exposing credentials. */
declare(strict_types=1);
require_once __DIR__ . '/web04-auth-core.php';
web04_session_start();
web04_security_headers();

function web04_oauth_start_error(string $reason): never {
    header('Location: /signin/?mode=login&oauth_error=' . rawurlencode($reason), true, 302);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}
if (!web04_oauth_configured()) web04_oauth_start_error('not_configured');
/** TASK-WEB-04 START — Bound repeated OAuth starts per source IP to reduce automated account-store growth. */
if (!web04_rate_limit('google-oauth-start-ip', '', 10, 900)) web04_oauth_start_error('rate_limited');
/** TASK-WEB-04 END */
$returnTo = web04_safe_return_path($_GET['return_to'] ?? '/rehoming/');
$transaction = web04_oauth_start($returnTo);
$query = http_build_query([
    'client_id' => web04_env('GOOGLE_CLIENT_ID'),
    'redirect_uri' => web04_env('GOOGLE_REDIRECT_URI'),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $transaction['state'],
    'nonce' => $transaction['nonce'],
    'code_challenge' => $transaction['code_challenge'],
    'code_challenge_method' => 'S256',
    'prompt' => 'select_account',
], '', '&', PHP_QUERY_RFC3986);
header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $query, true, 302);
exit;
/* TASK-WEB-04 END — Start Google OpenID Connect login without exposing credentials. */
