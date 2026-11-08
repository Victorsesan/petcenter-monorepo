<?php
/** TASK-MANUS-05 START — Admin-only hide/unhide for reviews; records are never deleted. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
$admin=rh_require_role('admin');
if($_SERVER['REQUEST_METHOD']!=='POST') rh_json_response(['success'=>false,'error'=>'POST required'],405);
$input=rh_input(); $id=rh_clean_id($input['review_id']??''); $visibility=$input['visibility']??'';
if($id==='' || !in_array($visibility,['visible','hidden'],true)) rh_json_response(['success'=>false,'error'=>'review_id and valid visibility are required'],422);
$reviews=rh_read_store('reviews'); $updated=false;
foreach($reviews as &$review) if(($review['review_id']??'')===$id){$review['visibility']=$visibility;$review['moderated_at']=rh_now();$review['moderated_by']=$admin['account_id'];$updated=true;break;}
unset($review);
if(!$updated) rh_json_response(['success'=>false,'error'=>'Review not found'],404);
rh_write_store('reviews',$reviews); rh_json_response(['success'=>true,'review_id'=>$id,'visibility'=>$visibility]);
/* TASK-MANUS-05 END */
?>
