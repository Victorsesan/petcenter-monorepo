<?php
/** TASK-MANUS-05 START — List account-owned rehoming threads; never expose another account's inbox. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
$account=rh_auth_account();
$threads=array_values(array_filter(rh_read_store('threads'),fn($t)=>rh_participant($t,$account['account_id'])));
rh_json_response(['success'=>true,'threads'=>$threads]);
/* TASK-MANUS-05 END */
?>
