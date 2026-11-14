<?php
/** TASK-MANUS-05 START — Poll a participant's thread with an optional message cursor. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
/** TASK-WEB-04 START — Polling is read-only and scoped to typed account participants. */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') rh_json_response(['success'=>false,'error'=>'GET required'],405);
$account=rh_auth_account();
$threadId=rh_clean_id($_GET['thread_id'] ?? '');
$after=rh_clean_id($_GET['after'] ?? '');
$thread=rh_find_thread(rh_read_store('threads'),$threadId);
if (!$thread || !rh_participant($thread,$account['account_id'],$account['account_type'])) rh_json_response(['success'=>false,'error'=>'Thread not found'],404);
$messages=array_values(array_filter(rh_read_store('messages'),function($m) use($threadId,$after){return ($m['thread_id']??'')===$threadId && ($after==='' || strcmp((string)$m['message_id'],$after)>0);}));
rh_json_response(['success'=>true,'thread'=>$thread,'messages'=>$messages]);
/** TASK-WEB-04 END */
/* TASK-MANUS-05 END */
?>
