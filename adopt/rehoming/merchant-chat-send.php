<?php
/** TASK-MANUS-05 START — Send one text or single-image message in a rehoming thread. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rh_json_response(['success'=>false,'error'=>'POST required'],405);
$account = rh_auth_account();
$input = rh_input();
$threadId = rh_clean_id($input['thread_id'] ?? '');
$text = rh_clean_text($input['text'] ?? '');
$imageUrl = rh_clean_text($input['image_url'] ?? '', 500);
if ($threadId === '' || ($text === '' && $imageUrl === '')) rh_json_response(['success'=>false,'error'=>'thread_id and message content are required'],422);
$threads = rh_read_store('threads');
$threadIndex = -1; $thread = null;
foreach ($threads as $i => $candidate) if (($candidate['thread_id'] ?? '') === $threadId) { $threadIndex=$i; $thread=$candidate; break; }
if (!$thread || !rh_participant($thread,$account['account_id'])) rh_json_response(['success'=>false,'error'=>'Thread not found'],404);
$messages = rh_read_store('messages');
$threadMessageCount = 0; foreach ($messages as $m) if (($m['thread_id'] ?? '') === $threadId) $threadMessageCount++;
$message = ['message_id'=>rh_id('MSG'),'thread_id'=>$threadId,'sender_id'=>$account['account_id'],'sender_type'=>$account['account_type'],'text'=>$text,'image_url'=>$imageUrl ?: null,'created_at'=>rh_now(),'status'=>'sent'];
$messages[]=$message;
$thread['updated_at']=rh_now(); $thread['message_count']=$threadMessageCount+1; $threads[$threadIndex]=$thread;
rh_write_store('messages',$messages); rh_write_store('threads',$threads);
// C2 consumes this stable hook when merged; detection never blocks a legitimate message.
if ($threadMessageCount < 5 && preg_match('/(?:\+?\d[\d\s().-]{7,}\d|[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}|whatsapp|text me at|call me|telegram)/i',$text)) {
    $flags=rh_read_store('flags'); $flags[]= ['flag_id'=>rh_id('FLG'),'source'=>'system_flag','category'=>'off_platform_contact','thread_id'=>$threadId,'target_type'=>'merchant','target_id'=>$thread['merchant_id'],'description'=>'Possible off-platform contact details in an early enquiry message.','created_at'=>rh_now()]; rh_write_store('flags',$flags);
}
rh_json_response(['success'=>true,'message'=>$message]);
/* TASK-MANUS-05 END */
?>
