<?php
/** Customer-facing PIN/password gate for one anonymous chat principal. */
header('Content-Type: application/json');
error_reporting(0); @ini_set('display_errors', 0);
require_once __DIR__ . '/chat-config.php';
require_once __DIR__ . '/includes/chat-auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success'=>false,'error'=>'POST required'],405);
$input = chatJsonInput(); if (!$input) $input = $_POST;
$action = trim((string)($input['action'] ?? 'status'));
$chatId = trim((string)($input['chatId'] ?? ''));
$email = strtolower(trim((string)($input['email'] ?? '')));
if (!preg_match('/^PC-[A-Z0-9]{8}$/', $chatId) || !chatValidEmail($email)) chatRespond(['success'=>false,'error'=>'Chat ID and email are required.'],400);
$db = chatDb();
$stmt = $db->prepare('SELECT chat_id, full_name, email, status FROM chat_threads WHERE chat_id = ? AND LOWER(email) = ? LIMIT 1');
$stmt->execute([$chatId,$email]); $thread = $stmt->fetch();
if (!$thread) chatRespond(['success'=>false,'error'=>'We could not verify those chat details.'],404);
$auth = chatAuthEnsure($chatId);
if ($action === 'status') {
    $trusted = chatAuthTrustedDevice($chatId, $input['deviceToken'] ?? '');
    if ($trusted && $auth['auth_tier'] !== 'none') {
        $token = issue_token($chatId); log_audit($chatId,'chat-auth-gate.php','device_verified');
        chatRespond(['success'=>true,'authRequired'=>false,'sessionToken'=>$token,'fullName'=>$thread['full_name'],'status'=>$thread['status']]);
    }
    chatRespond(['success'=>true,'authRequired'=>$auth['auth_tier'] !== 'none','authTier'=>$auth['auth_tier'],'fullName'=>$thread['full_name'],'status'=>$thread['status']]);
}
if ($action === 'verify') {
    $tier = trim((string)($input['tier'] ?? $auth['auth_tier'])); $secret = (string)($input['secret'] ?? '');
    if ($auth['auth_tier'] !== $tier || $tier === 'none') chatRespond(['success'=>false,'error'=>'This chat protection setting has changed.'],409);
    /* TASK-3 SECURITY START — account throttling is supplemented by a source
       IP ceiling so one hostile client cannot spend all server hash capacity
       against many different chat IDs. */
    $verifyIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!chatCheckRateLimit('auth_gate_ip:' . $verifyIp, 60, 600)) {
        chatRespond(['success'=>false,'error'=>'Too many verification attempts. Please wait a few minutes.'],429);
    }
    /* TASK-3 SECURITY END */
    $throttle = check_pin_throttle($chatId);
    if (!$throttle['allowed']) chatRespond(['success'=>false,'error'=>'Too many attempts. Try again in '.$throttle['delay_seconds'].' seconds.','delaySeconds'=>$throttle['delay_seconds']],429);
    if (!password_verify($secret, (string)$auth['secret_hash'])) {
        $delay = chatAuthRecordFailure($chatId); log_audit($chatId,'chat-auth-gate.php',$tier.'_failed');
        chatRespond(['success'=>false,'error'=>'That '.$tier.' is not correct.','delaySeconds'=>$delay],401);
    }
    chatAuthClearThrottle($chatId);
    if (!empty($input['rememberDevice'])) chatAuthRememberDevice($chatId, $input['deviceToken'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? 'This device');
    $token = issue_token($chatId); log_audit($chatId,'chat-auth-gate.php',$tier.'_verified');
    chatRespond(['success'=>true,'authRequired'=>false,'sessionToken'=>$token,'fullName'=>$thread['full_name'],'status'=>$thread['status']]);
}
if ($action === 'set' || $action === 'change') {
    $token = trim((string)($input['sessionToken'] ?? ''));
    if (!verify_token($token,$chatId)) chatRespond(['success'=>false,'error'=>'Your chat session is locked.'],401);
    $tier = trim((string)($input['tier'] ?? '')); $secret = (string)($input['secret'] ?? '');
    $result = chatAuthSetSecret($chatId,$tier,$secret);
    if (!$result['success']) chatRespond($result,422);
    $newToken = issue_token($chatId);
    chatRespond(['success'=>true,'authTier'=>$tier,'sessionToken'=>$newToken]);
}
if ($action === 'forgetDevices') {
    $token = trim((string)($input['sessionToken'] ?? ''));
    if (!verify_token($token,$chatId)) chatRespond(['success'=>false,'error'=>'Your chat session is locked.'],401);
    chatAuthForgetDevices($chatId); chatRespond(['success'=>true]);
}
chatRespond(['success'=>false,'error'=>'Unsupported action.'],400);
