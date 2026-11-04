<?php
/** Server-authoritative idle/aggressive lock controls for a live chat. */
header('Content-Type: application/json');
error_reporting(0); @ini_set('display_errors', 0);
require_once __DIR__ . '/chat-config.php'; require_once __DIR__ . '/includes/chat-auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success'=>false,'error'=>'POST required'],405);
$input=chatJsonInput(); if(!$input)$input=$_POST; $chatId=trim((string)($input['chatId']??'')); $token=trim((string)($input['sessionToken']??''));
if (!preg_match('/^PC-[A-Z0-9]{8}$/',$chatId) || !verify_token($token,$chatId)) chatRespond(['success'=>false,'error'=>'Chat session is not authenticated.'],401);
$db=chatDb(); $action=trim((string)($input['action']??'status'));
if($action==='status'){ chatRespond(['success'=>true,'lock'=>chatAuthLockStatus($chatId)]); }
if($action==='set'){
  /* BUGFIX-MANUS-01-2 START — a lock without a credential creates a dead-end:
     closing the panel invalidates the token, while no PIN/password exists to
     satisfy the next AuthGate. Refuse the preference change until the chat has
     an actual protection tier. */
  $auth = chatAuthGet($chatId);
  if (!$auth || $auth['auth_tier'] === 'none' || empty($auth['secret_hash'])) {
    chatRespond(['success'=>false,'error'=>'Set a PIN or password before enabling chat lock.'],422);
  }
  /* BUGFIX-MANUS-01-2 END */
  $delay=max(0,min(3600,(int)($input['delaySeconds']??300))); $aggressive=!empty($input['aggressive'])?1:0;
  $db->prepare("INSERT INTO chat_lock_settings(principal_id,delay_seconds,aggressive,locked_at) VALUES(?,?,?,NULL) ON DUPLICATE KEY UPDATE delay_seconds=VALUES(delay_seconds),aggressive=VALUES(aggressive),locked_at=NULL,updated_at=CURRENT_TIMESTAMP")->execute([$chatId,$delay,$aggressive]);
  log_audit($chatId,'chat-lock.php','settings_updated'); chatRespond(['success'=>true,'lock'=>chatAuthLockStatus($chatId)]);
}
if($action==='closed'){
  $lock=chatAuthLockStatus($chatId);
  $db->prepare("INSERT INTO chat_lock_settings(principal_id,delay_seconds,aggressive,last_closed_at,locked_at) VALUES(?,?,?,CURRENT_TIMESTAMP,?) ON DUPLICATE KEY UPDATE last_closed_at=CURRENT_TIMESTAMP,locked_at=VALUES(locked_at),updated_at=CURRENT_TIMESTAMP")->execute([$chatId,(int)$lock['delay_seconds'],(int)$lock['aggressive'],$lock['aggressive']?date('Y-m-d H:i:s'):null]);
  if($lock['aggressive']) invalidate_all_tokens($chatId);
  chatRespond(['success'=>true]);
}
chatRespond(['success'=>false,'error'=>'Unsupported action.'],400);
