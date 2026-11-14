<?php
/** TASK-MANUS-05 START — List account-owned rehoming threads; never expose another account's inbox. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
/** TASK-WEB-04 START — Private inbox reads are GET-only and principal-type scoped. */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') rh_json_response(['success'=>false,'error'=>'GET required'],405);
$account=rh_auth_account();
$threads=array_values(array_filter(rh_read_store('threads'),fn($t)=>rh_participant($t,$account['account_id'],$account['account_type'])));
rh_json_response(['success'=>true,'threads'=>$threads]);
/** TASK-WEB-04 END */
/* TASK-MANUS-05 END */
?>
