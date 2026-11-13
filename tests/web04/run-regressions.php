<?php
declare(strict_types=1);

$repoRoot = realpath(__DIR__ . '/../..');
if ($repoRoot === false) throw new RuntimeException('Repository root could not be resolved');
$webRoot = realpath($repoRoot . '/adopt');
if ($webRoot === false) throw new RuntimeException('Adoption web root could not be resolved');
$testRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'web04-regression-' . bin2hex(random_bytes(8));
$authDir = $testRoot . DIRECTORY_SEPARATOR . 'auth';
$runtimeDir = $testRoot . DIRECTORY_SEPARATOR . 'runtime';
$sessionDir = $testRoot . DIRECTORY_SEPARATOR . 'sessions';
foreach ([$testRoot, $authDir, $runtimeDir, $sessionDir] as $dir) {
    if (!mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Unable to create private test directory');
    chmod($dir, 0700);
}

$assertions = 0;
function check(bool $condition, string $message): void {
    global $assertions;
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $assertions++;
}
function expectRuntimeException(callable $callback, string $message): void {
    try { $callback(); }
    catch (RuntimeException $error) { check(true, $message); return; }
    throw new RuntimeException('FAIL: expected RuntimeException: ' . $message);
}
function testBase64Url(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
function signedTestJwt(OpenSSLAsymmetricKey $privateKey, array $claims): string {
    $header = ['alg' => 'RS256', 'kid' => 'web04-test-key', 'typ' => 'JWT'];
    $input = testBase64Url(json_encode($header, JSON_THROW_ON_ERROR)) . '.' . testBase64Url(json_encode($claims, JSON_THROW_ON_ERROR));
    if (!openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256)) throw new RuntimeException('Could not sign synthetic JWT');
    return $input . '.' . testBase64Url($signature);
}
function removeTree(string $path): void {
    if (is_link($path) || is_file($path)) { @unlink($path); return; }
    if (!is_dir($path)) return;
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        removeTree($path . DIRECTORY_SEPARATOR . $entry);
    }
    @rmdir($path);
}

register_shutdown_function(static function () use ($testRoot): void {
    if (session_status() === PHP_SESSION_ACTIVE) @session_write_close();
    removeTree($testRoot);
});

putenv('APP_BASE_URL=https://web04-test.invalid');
putenv('SESSION_SECRET=' . bin2hex(random_bytes(32)));
putenv('REHOMING_AUTH_DATA_DIR=' . $authDir);
putenv('REHOMING_RUNTIME_DIR=' . $runtimeDir);
putenv('APP_ENV=staging');
putenv('WEB04_ENABLE_TEST_AUTH=1');
$_ENV['APP_BASE_URL'] = 'https://web04-test.invalid';
$_ENV['SESSION_SECRET'] = getenv('SESSION_SECRET');
$_ENV['REHOMING_AUTH_DATA_DIR'] = $authDir;
$_ENV['REHOMING_RUNTIME_DIR'] = $runtimeDir;
$_ENV['APP_ENV'] = 'staging';
$_ENV['WEB04_ENABLE_TEST_AUTH'] = '1';

session_save_path($sessionDir);
require_once $repoRoot . '/adopt/rehoming/web04-auth-core.php';

check(web04_auth_ready(), 'configured external auth/runtime stores are accepted');
check((fileperms($authDir) & 0077) === 0 && (fileperms($runtimeDir) & 0077) === 0, 'private storage roots are owner-only');

$originalAuthDir = $authDir;
putenv('REHOMING_AUTH_DATA_DIR=');
unset($_ENV['REHOMING_AUTH_DATA_DIR']);
expectRuntimeException(static fn() => web04_auth_store_dir(), 'auth store fails closed without explicit configuration');
$_ENV['REHOMING_AUTH_DATA_DIR'] = $originalAuthDir;
putenv('REHOMING_AUTH_DATA_DIR=' . $originalAuthDir);
putenv('REHOMING_AUTH_DATA_DIR=' . $webRoot);
$_ENV['REHOMING_AUTH_DATA_DIR'] = $webRoot;
expectRuntimeException(static fn() => web04_auth_store_dir(), 'auth store rejects a path inside the served tree');
$_ENV['REHOMING_AUTH_DATA_DIR'] = $originalAuthDir;
putenv('REHOMING_AUTH_DATA_DIR=' . $originalAuthDir);

$visitor = web04_provision_account('visitor@example.test', 'visitor', 'Test Visitor');
$pendingMerchant = web04_provision_account('merchant@example.test', 'merchant', 'Pending Merchant');
check(!web04_account_has_listing_eligibility($pendingMerchant), 'pending merchant cannot publish listings');
$verified = web04_store_update('accounts', static function (array &$store) use ($pendingMerchant): array {
    foreach ($store['accounts'] as &$account) {
        if (($account['account_id'] ?? '') === $pendingMerchant['account_id']) {
            $account['verification_status'] = 'verified';
            return $account;
        }
    }
    unset($account);
    throw new RuntimeException('Synthetic merchant record missing');
});
check(web04_account_has_listing_eligibility($verified), 'active verified merchant can publish listings');
$profile = web04_save_profile($visitor['account_id'], [
    'householdType' => 'apartment',
    'experienceLevel' => 'some',
    'otherPets' => 'yes',
    'otherPetsSpecies' => 'one cat',
    'kidsInHome' => 'no',
    'whyThisPet' => 'A synthetic regression profile.',
]);
check(is_array($profile) && $profile['profile']['householdType'] === 'apartment', 'visitor profile persists through the private account store');
check((fileperms(web04_store_path('accounts')) & 0077) === 0, 'account file is owner-only');

$testAccountPath = $testRoot . DIRECTORY_SEPARATOR . 'test-accounts.json';
$testAccount = [
    'account_id' => 'test-visitor-01',
    'account_type' => 'visitor',
    'status' => 'active',
    'verification_status' => null,
    'username' => 'staging-visitor',
    'email' => 'staging@example.test',
    'display_name' => 'Synthetic Staging Visitor',
    'is_test_account' => true,
    'password_hash' => password_hash('synthetic-password', PASSWORD_DEFAULT),
];
file_put_contents($testAccountPath, json_encode(['accounts' => [$testAccount]], JSON_THROW_ON_ERROR), LOCK_EX);
chmod($testAccountPath, 0600);
putenv('WEB04_TEST_ACCOUNTS_FILE=' . $testAccountPath);
$_ENV['WEB04_TEST_ACCOUNTS_FILE'] = $testAccountPath;
check(web04_verify_test_login('staging-visitor', 'synthetic-password') !== null, 'explicit external staging test account is accepted');
check(web04_verify_test_login('staging-visitor', 'wrong-password') === null, 'incorrect staging password is rejected');
putenv('WEB04_TEST_ACCOUNTS_FILE=' . $webRoot . '/admin/test-accounts-data/accounts.json');
$_ENV['WEB04_TEST_ACCOUNTS_FILE'] = $webRoot . '/admin/test-accounts-data/accounts.json';
expectRuntimeException(static fn() => web04_external_test_accounts(), 'staging test-account file rejects a web-root path');
putenv('WEB04_TEST_ACCOUNTS_FILE=' . $testAccountPath);
$_ENV['WEB04_TEST_ACCOUNTS_FILE'] = $testAccountPath;

$_SERVER['REMOTE_ADDR'] = '192.0.2.10';
for ($i = 0; $i < 20; $i++) check(web04_rate_limit('test-login-ip', '', 20, 900), 'per-IP staging-login attempt ' . ($i + 1) . ' is allowed');
check(!web04_rate_limit('test-login-ip', '', 20, 900), '21st staging-login attempt from one IP is throttled');
$_SERVER['REMOTE_ADDR'] = '198.51.100.20';
check(web04_rate_limit('test-login-ip', '', 20, 900), 'a separate IP has an independent staging-login budget');

if (!function_exists('openssl_pkey_new')) throw new RuntimeException('OpenSSL is required for the WEB-04 regression suite');
$privateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
if (!$privateKey instanceof OpenSSLAsymmetricKey) throw new RuntimeException('Could not create synthetic RSA key');
$keyDetails = openssl_pkey_get_details($privateKey);
if (!is_array($keyDetails) || !isset($keyDetails['rsa']['n'], $keyDetails['rsa']['e'])) throw new RuntimeException('Could not read synthetic RSA key details');
$jwks = ['keys' => [[
    'kid' => 'web04-test-key',
    'kty' => 'RSA',
    'alg' => 'RS256',
    'n' => testBase64Url($keyDetails['rsa']['n']),
    'e' => testBase64Url($keyDetails['rsa']['e']),
]]];
$now = time();
$claims = [
    'iss' => 'https://accounts.google.com',
    'aud' => 'synthetic-client-id',
    'exp' => $now + 3600,
    'iat' => $now,
    'nbf' => $now - 1,
    'sub' => 'synthetic-subject',
    'nonce' => 'synthetic-nonce',
    'email' => 'oidc@example.test',
    'email_verified' => true,
];
$validJwt = signedTestJwt($privateKey, $claims);
check(web04_verify_google_id_token($validJwt, 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) !== null, 'valid synthetic Google ID token is accepted');
$claims['nbf'] = $now + 60;
check(web04_verify_google_id_token(signedTestJwt($privateKey, $claims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'not-yet-valid ID token is rejected');
$claims['nbf'] = 'not-a-time';
check(web04_verify_google_id_token(signedTestJwt($privateKey, $claims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'malformed nbf claim is rejected');

$validClaims = $claims;
$validClaims['nbf'] = $now - 1;
$invalidClaims = $validClaims;
$invalidClaims['iss'] = 'https://attacker.invalid';
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'wrong OIDC issuer is rejected');
$invalidClaims = $validClaims;
$invalidClaims['aud'] = 'different-client-id';
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'wrong OIDC audience is rejected');
$invalidClaims = $validClaims;
$invalidClaims['aud'] = ['synthetic-client-id', 'another-client-id'];
$invalidClaims['azp'] = 'another-client-id';
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'wrong authorized party is rejected for multiple audiences');
check(web04_verify_google_id_token(signedTestJwt($privateKey, $validClaims), 'different-nonce', 'synthetic-client-id', $jwks, $now) === null, 'nonce mismatch is rejected');
$invalidClaims = $validClaims;
$invalidClaims['exp'] = $now - 60;
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'expired OIDC ID token is rejected');
$invalidClaims = $validClaims;
$invalidClaims['email_verified'] = false;
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'unverified Google email is rejected');
$invalidClaims = $validClaims;
$invalidClaims['email'] = 'not-an-email';
check(web04_verify_google_id_token(signedTestJwt($privateKey, $invalidClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'invalid email claim is rejected');
check(web04_verify_google_id_token($validJwt, 'synthetic-nonce', 'synthetic-client-id', ['keys' => []], $now) === null, 'unknown signing key is rejected');
$otherPrivateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
if (!$otherPrivateKey instanceof OpenSSLAsymmetricKey) throw new RuntimeException('Could not create second synthetic RSA key');
check(web04_verify_google_id_token(signedTestJwt($otherPrivateKey, $validClaims), 'synthetic-nonce', 'synthetic-client-id', $jwks, $now) === null, 'invalid ID-token signature is rejected');

putenv('GOOGLE_CLIENT_ID=synthetic-client-id');
putenv('GOOGLE_CLIENT_SECRET=synthetic-client-secret');
putenv('GOOGLE_REDIRECT_URI=https://web04-test.invalid/rehoming/google-oauth-callback.php');
$_ENV['GOOGLE_CLIENT_ID'] = 'synthetic-client-id';
$_ENV['GOOGLE_CLIENT_SECRET'] = 'synthetic-client-secret';
$_ENV['GOOGLE_REDIRECT_URI'] = 'https://web04-test.invalid/rehoming/google-oauth-callback.php';
check(web04_oauth_configured(), 'synthetic exact OAuth callback URI is accepted');
putenv('GOOGLE_REDIRECT_URI=https://attacker.invalid/callback');
$_ENV['GOOGLE_REDIRECT_URI'] = 'https://attacker.invalid/callback';
check(!web04_oauth_configured(), 'mismatched OAuth callback URI disables sign-in');
putenv('GOOGLE_REDIRECT_URI=https://web04-test.invalid/rehoming/google-oauth-callback.php');
$_ENV['GOOGLE_REDIRECT_URI'] = 'https://web04-test.invalid/rehoming/google-oauth-callback.php';
putenv('GOOGLE_CLIENT_SECRET');
unset($_ENV['GOOGLE_CLIENT_SECRET']);
check(!web04_oauth_configured(), 'missing Google client secret disables sign-in');

check(web04_safe_return_path('/rehoming/list-a-pet/') === '/rehoming/list-a-pet/', 'allowed internal return route is preserved');
check(web04_safe_return_path('https://attacker.invalid/') === '/rehoming/', 'absolute external return URL is rejected');
check(web04_safe_return_path('//attacker.invalid/') === '/rehoming/', 'scheme-relative return URL is rejected');
check(web04_safe_return_path('/rehoming/%2e%2e/account/') === '/rehoming/', 'encoded traversal return URL is rejected');
$oauthTransaction = web04_oauth_start('/rehoming/list-a-pet/');
check(strlen($oauthTransaction['state']) >= 40 && strlen($oauthTransaction['nonce']) >= 40, 'OAuth state and nonce are generated with high entropy');
check(hash_equals($oauthTransaction['code_challenge'], web04_base64url_encode(hash('sha256', $oauthTransaction['code_verifier'], true))), 'OAuth transaction uses an S256 PKCE challenge');
check(web04_oauth_consume_state('incorrect-state') === null, 'OAuth state mismatch is rejected');
check(web04_oauth_consume_state($oauthTransaction['state']) === null, 'mismatched OAuth state cannot be replayed');
$oauthTransaction = web04_oauth_start('/rehoming/');
$_SESSION['web04_oauth']['created_at'] = time() - WEB04_OAUTH_STATE_TTL - 1;
check(web04_oauth_consume_state($oauthTransaction['state']) === null, 'expired OAuth state is rejected');
$oauthTransaction = web04_oauth_start('/rehoming/');
check(is_array(web04_oauth_consume_state($oauthTransaction['state'])), 'matching current OAuth state is accepted once');
check(web04_oauth_consume_state($oauthTransaction['state']) === null, 'consumed OAuth state cannot be replayed');

$serverCreatedAccount = web04_upsert_google_account([
    'sub' => 'synthetic-new-subject',
    'email' => 'new-visitor@example.test',
    'email_verified' => true,
    'display_name' => 'New Visitor',
    'account_type' => 'admin',
    'verification_status' => 'verified',
]);
check(is_array($serverCreatedAccount) && $serverCreatedAccount['account_type'] === 'visitor' && $serverCreatedAccount['verification_status'] === null, 'new Google account receives only the server-assigned visitor role');
check(web04_return_for_role($visitor, '/merchant/') === '/account/settings/', 'visitor cannot return to a merchant route');
check(web04_return_for_role($verified, '/account/settings/') === '/merchant/', 'merchant cannot return to visitor settings');

web04_session_start();
$csrf = web04_csrf_token();
$_SERVER['HTTP_ORIGIN'] = 'https://web04-test.invalid';
$_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
$_SERVER['HTTP_X_CSRF_TOKEN'] = $csrf;
check(web04_csrf_valid(true), 'same-origin request with valid synchronizer token passes CSRF validation');
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'incorrect-token';
check(!web04_csrf_valid(true), 'incorrect synchronizer token fails CSRF validation');
$_SERVER['HTTP_X_CSRF_TOKEN'] = $csrf;
$_SERVER['HTTP_ORIGIN'] = 'https://attacker.invalid';
check(!web04_csrf_valid(true), 'cross-origin request fails CSRF validation');
unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_SEC_FETCH_SITE'], $_SERVER['HTTP_X_CSRF_TOKEN']);

web04_finish_login($visitor);
check(web04_current_user() !== null, 'synthetic visitor has an active session before logout');
$oldSessionId = session_id();
file_put_contents($runtimeDir . DIRECTORY_SEPARATOR . 'tokens.json', '{malformed', LOCK_EX);
chmod($runtimeDir . DIRECTORY_SEPARATOR . 'tokens.json', 0600);
$revocationFailed = false;
try { web04_logout(); }
catch (RuntimeException $error) { $revocationFailed = true; }
check($revocationFailed, 'malformed bearer-token store causes a revocation error');
check(session_status() === PHP_SESSION_NONE, 'logout closes and persists the PHP session before token revocation');
$newSessionId = session_id();
check($newSessionId !== '' && $newSessionId !== $oldSessionId, 'logout rotates the session identifier even when revocation fails');
if (!session_start()) throw new RuntimeException('Could not reopen the rotated signed-out session');
check(!isset($_SESSION['pc_account']), 'rotated session remains unauthenticated after token-revocation failure');
check(is_string($_SESSION['web04_csrf'] ?? null), 'rotated signed-out session retains a fresh CSRF token');
session_write_close();

$settingsHtml = file_get_contents($repoRoot . '/adopt/account/settings/index.html');
if ($settingsHtml === false) throw new RuntimeException('Could not read settings page');
check(str_contains($settingsHtml, 'id="profileLoading"') && str_contains($settingsHtml, 'id="retryProfileLoad"'), 'profile load state and retry control are present');
check(str_contains($settingsHtml, 'if (!profileLoaded) return;') && str_contains($settingsHtml, 'saveButton.disabled = !enabled;'), 'profile saves and edits are gated on successful load');
check(substr_count($settingsHtml, 'href="/signin/?mode=signup"') >= 3, 'settings-page signup links select the signup mode');
$signinHtml = file_get_contents($repoRoot . '/adopt/signin/index.html');
if ($signinHtml === false) throw new RuntimeException('Could not read sign-in gateway');
check(str_contains($signinHtml, 'id="signupTab"') && str_contains($signinHtml, 'id="googleSignupButton"'), 'sign-in page exposes account-creation and Google sign-in controls');
check(str_contains($signinHtml, 'id="googleAuthStatus" role="status"') && str_contains($signinHtml, 'id="authMessage" role="alert"'), 'gateway loading and error messages use live regions');
check(str_contains($signinHtml, 'href="/privacy-policy/"') && str_contains($signinHtml, 'href="/terms-of-service/"'), 'gateway links to both legal routes');
$listingWrapper = file_get_contents($repoRoot . '/adopt/rehoming/list-a-pet/index.php');
if ($listingWrapper === false) throw new RuntimeException('Could not read listing wrapper');
check(str_contains($listingWrapper, 'web04_account_has_listing_eligibility($user)'), 'listing-entry route checks verified-merchant eligibility');

printf("PASS: %d WEB-04 regression assertions\n", $assertions);
