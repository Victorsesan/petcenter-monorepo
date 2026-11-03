<?php
/** Single-use temporary password recovery for protected chat conversations. */
header('Content-Type: application/json');
error_reporting(0); @ini_set('display_errors', 0);
require_once __DIR__ . '/chat-config.php'; require_once __DIR__ . '/chat-mail.php'; require_once __DIR__ . '/includes/chat-auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success'=>false,'error'=>'POST required'],405);
$input=chatJsonInput(); if(!$input)$input=$_POST; $action=trim((string)($input['action']??'request')); $email=strtolower(trim((string)($input['email']??'')));
if(!chatValidEmail($email)) chatRespond(['success'=>false,'error'=>'A valid email is required.'],400);
$db=chatDb(); $message='If a protected chat matches that email, a temporary password has been sent.';
if($action==='request'){
  /* TASK-6 SECURITY START — throttle both source and target. A throttled
     request deliberately returns the same generic success message so it does
     not become an account-existence oracle. */
  $resetIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $resetEmailKey = hash('sha256', $email);
  $requestAllowed = chatCheckRateLimit('pw_reset_ip:' . $resetIp, 5, 900)
      && chatCheckRateLimit('pw_reset_email:' . $resetEmailKey, 3, 900);
  usleep(750000);
  if (!$requestAllowed) chatRespond(['success'=>true,'message'=>$message]);
  $stmt=$db->prepare("SELECT t.chat_id,t.email,a.auth_tier FROM chat_threads t JOIN chat_auth a ON a.principal_id=t.chat_id WHERE LOWER(t.email)=? AND a.auth_tier='password' ORDER BY t.created_at DESC LIMIT 1"); $stmt->execute([$email]); $row=$stmt->fetch();
  if($row){
    $temp=bin2hex(random_bytes(8)); $hash=password_hash($temp,defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT);
    $db->prepare('UPDATE chat_password_resets SET used_at=NOW() WHERE principal_id=? AND used_at IS NULL')->execute([$row['chat_id']]);
    $db->prepare('INSERT INTO chat_password_resets(principal_id,contact_email,temp_hash,expires_at) VALUES(?,?,?,?)')->execute([$row['chat_id'],$email,$hash,date('Y-m-d H:i:s',time()+1800)]);
    $html='<p>Use this temporary password to recover your Pet Center chat:</p><p style="font-size:22px;font-family:monospace;font-weight:700;letter-spacing:.12em">'.htmlspecialchars($temp,ENT_QUOTES,'UTF-8').'</p><p>This temporary password expires in 30 minutes and can be used once. You will be required to choose a new password immediately.</p>';
    chatMailSendAndLog($email,'PetCenter chat password reset',$html,'chat password reset');
  }
  chatRespond(['success'=>true,'message'=>$message]);
}
if($action==='temporaryLogin'){
  /* TASK-6 SECURITY START — bound online guessing of the temporary secret by
     both IP and email before invoking the deliberately expensive hash check. */
  $loginIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $loginEmailKey = hash('sha256', $email);
  if (!chatCheckRateLimit('pw_temp_ip:' . $loginIp, 20, 900)
      || !chatCheckRateLimit('pw_temp_email:' . $loginEmailKey, 10, 900)) {
      chatRespond(['success'=>false,'error'=>'Too many recovery attempts. Please request a new reset email later.'],429);
  }
  $temp=(string)($input['temporaryPassword']??''); $stmt=$db->prepare('SELECT * FROM chat_password_resets WHERE LOWER(contact_email)=? AND used_at IS NULL AND expires_at>NOW() ORDER BY created_at DESC LIMIT 1'); $stmt->execute([$email]); $row=$stmt->fetch();
  if(!$row || !password_verify($temp,$row['temp_hash'])) chatRespond(['success'=>false,'error'=>'The temporary password is invalid or expired.'],401);
  $db->prepare('UPDATE chat_password_resets SET used_at=NOW() WHERE id=?')->execute([$row['id']]);
  chatAuthClearThrottle($row['principal_id']); $resetToken=bin2hex(random_bytes(32));
  $db->prepare('INSERT INTO chat_session_token(token_hash,principal_id,issued_at,expires_at) VALUES(?,?,NOW(),?)')->execute([hash('sha256',$resetToken),$row['principal_id'],date('Y-m-d H:i:s',time()+900)]);
  chatRespond(['success'=>true,'requiresPasswordChange'=>true,'chatId'=>$row['principal_id'],'sessionToken'=>$resetToken]);
}
if($action==='change'){
  $chatId=trim((string)($input['chatId']??'')); $token=trim((string)($input['sessionToken']??'')); $new=(string)($input['newPassword']??'');
  if(!verify_token($token,$chatId)) chatRespond(['success'=>false,'error'=>'Reset session expired.'],401);
  $result=chatAuthSetSecret($chatId,'password',$new); if(!$result['success']) chatRespond($result,422); chatRespond(['success'=>true,'message'=>'Password updated. Please start the chat again.']);
}
chatRespond(['success'=>false,'error'=>'Unsupported action.'],400);
