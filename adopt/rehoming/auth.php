<?php
/** TASK-WEB-04 START — Session status, CSRF, logout and private adopter profile API. */
declare(strict_types=1);
require_once __DIR__ . '/web04-auth-core.php';
web04_session_start();
web04_security_headers();

function web04_auth_request_data(): array {
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) return [];
    $json = json_decode($raw ?: '', true);
    return is_array($json) ? $json : $_POST;
}

$action = is_string($_GET['action'] ?? null) ? (string)$_GET['action'] : '';
$method = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    if ($action === 'status' && $method === 'GET') {
        $user = web04_auth_ready() ? web04_current_user() : null;
        web04_json_response([
            'success' => true,
            'auth_configured' => web04_auth_ready(),
            'google_configured' => web04_oauth_configured(),
            'test_auth_enabled' => web04_test_auth_enabled(),
            'authenticated' => $user !== null,
            'user' => $user ? ['account_id' => $user['account_id'], 'account_type' => $user['account_type'], 'display_name' => $user['display_name'], 'email' => $user['email']] : null,
            'next' => $user ? web04_return_for_role($user, web04_safe_return_path($_GET['return_to'] ?? '/rehoming/')) : null,
        ]);
    }

    if ($action === 'csrf' && $method === 'GET') {
        if (!web04_auth_ready()) web04_json_response(['success' => false, 'auth_configured' => false, 'google_configured' => web04_oauth_configured(), 'message' => 'Sign-in is not configured for this environment.'], 503);
        web04_json_response(['success' => true, 'auth_configured' => true, 'google_configured' => web04_oauth_configured(), 'test_auth_enabled' => web04_test_auth_enabled(), 'csrf_token' => web04_csrf_token()]);
    }

    if ($action === 'me' && $method === 'GET') {
        $user = web04_auth_ready() ? web04_current_user() : null;
        web04_json_response(['success' => true, 'authenticated' => $user !== null, 'user' => $user]);
    }

    if ($action === 'logout' && $method === 'POST') {
        if (!web04_auth_ready()) web04_json_response(['success' => false, 'message' => 'Sign-in is not configured for this environment.'], 503);
        if (!web04_csrf_valid(true)) web04_json_response(['success' => false, 'message' => 'Your request could not be verified. Refresh the page and try again.'], 403);
        web04_logout();
        web04_json_response(['success' => true, 'message' => 'You have been signed out.']);
    }

    if ($action === 'test-login' && $method === 'POST') {
        if (!web04_auth_ready()) web04_json_response(['success' => false, 'message' => 'Sign-in is not configured for this environment.'], 503);
        if (!web04_test_auth_enabled()) web04_json_response(['success' => false, 'message' => 'Test-account sign-in is not available in this environment.'], 404);
        if (!web04_csrf_valid(true)) web04_json_response(['success' => false, 'message' => 'Your request could not be verified. Refresh the page and try again.'], 403);
        $data = web04_auth_request_data();
        $username = is_string($data['username'] ?? null) ? trim($data['username']) : '';
        $password = is_string($data['password'] ?? null) ? $data['password'] : '';
        if (!web04_rate_limit('test-login-ip', '', 20, 900)
            || !web04_rate_limit('test-login-account', $username, 6, 900)) {
            web04_json_response(['success' => false, 'message' => 'Too many attempts. Wait a few minutes and try again.'], 429);
        }
        $account = web04_verify_test_login($username, $password);
        if (!$account) web04_json_response(['success' => false, 'message' => 'The test-account credentials were not accepted.'], 401);
        web04_finish_login($account);
        $destination = web04_return_for_role(web04_safe_account($account), (string)($data['return_to'] ?? '/rehoming/'));
        web04_json_response(['success' => true, 'test_mode' => true, 'next' => $destination, 'message' => 'Signed in with a staging-only test account.']);
    }

    if ($action === 'profile' && $method === 'GET') {
        if (!web04_auth_ready()) web04_json_response(['success' => false, 'message' => 'Sign-in is not configured for this environment.'], 503);
        $user = web04_current_user();
        if (!$user) web04_json_response(['success' => false, 'message' => 'Authentication required.'], 401);
        if (!web04_user_has_role($user, 'visitor')) web04_json_response(['success' => false, 'message' => 'This account cannot access adopter profile settings.'], 403);
        web04_json_response(['success' => true, 'profile' => $user['profile']]);
    }

    if ($action === 'profile' && $method === 'POST') {
        if (!web04_auth_ready()) web04_json_response(['success' => false, 'message' => 'Sign-in is not configured for this environment.'], 503);
        if (!web04_csrf_valid(true)) web04_json_response(['success' => false, 'message' => 'Your request could not be verified. Refresh the page and try again.'], 403);
        $user = web04_current_user();
        if (!$user) web04_json_response(['success' => false, 'message' => 'Authentication required.'], 401);
        if (!web04_user_has_role($user, 'visitor')) web04_json_response(['success' => false, 'message' => 'This account cannot update adopter profile settings.'], 403);
        $saved = web04_save_profile($user['account_id'], web04_auth_request_data());
        if (!$saved) web04_json_response(['success' => false, 'message' => 'Please review the profile fields and try again.'], 422);
        web04_json_response(['success' => true, 'profile' => $saved['profile'], 'message' => 'Your profile has been saved securely.']);
    }

    web04_json_response(['success' => false, 'message' => 'Unsupported authentication request.'], 404);
} catch (Throwable $error) {
    // Never send exception text, paths, or account-store details to a browser.
    web04_json_response(['success' => false, 'message' => 'The authentication service is temporarily unavailable.'], 503);
}
/* TASK-WEB-04 END — Session status, CSRF, logout and private adopter profile API. */
