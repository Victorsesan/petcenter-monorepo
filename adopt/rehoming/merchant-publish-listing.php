<?php
/** TASK-MANUS-05 START — Session-bound A1 listing publish endpoint. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
if($_SERVER['REQUEST_METHOD']!=='POST') rh_json_response(['success'=>false,'error'=>'POST required'],405);
/** TASK-WEB-04 START — Listing creation is a verified-merchant, CSRF-protected operation. */
rh_require_csrf();
$account=rh_require_verified_merchant(); $input=rh_input();
/** TASK-WEB-04 END */
$required=['name','category','breed','health_status','temperament','reason_for_rehoming']; foreach($required as $field) if(rh_clean_text($input[$field]??'')==='') rh_json_response(['success'=>false,'error'=>$field.' is required'],422);
$category=rh_clean_id($input['category']??''); if(!in_array($category,['dogs','cats','birds','reptiles','small-mammals','livestocks','aquariums'],true)) rh_json_response(['success'=>false,'error'=>'Invalid category'],422);
$spay=$input['spay_neuter_status']??'unknown'; $type=$input['listing_type']??'permanent'; $currency=$input['currency_code']??'USD';
if(!in_array($spay,['spayed_neutered','not_yet','unknown'],true)||!in_array($type,['permanent','foster'],true)||!in_array($currency,['USD','CAD','GBP','AUD'],true)) rh_json_response(['success'=>false,'error'=>'Invalid listing option'],422);
$health=rh_clean_text($input['health_status'],2000);$temperament=rh_clean_text($input['temperament'],2000);$reason=rh_clean_text($input['reason_for_rehoming'],2000);
if(mb_strlen($health)<20||mb_strlen($temperament)<20||mb_strlen($reason)<15) rh_json_response(['success'=>false,'error'=>'Health and temperament require 20 characters; reason requires 15 characters'],422);
$listings=rh_read_store('listings'); $prefix=['dogs'=>'DOG','cats'=>'CAT','birds'=>'BIRD','reptiles'=>'REP','small-mammals'=>'SM','livestocks'=>'LVS','aquariums'=>'AQU'][$category];
$petId; do{$petId=$prefix.'-'.str_pad((string)(count($listings)+1),5,'0',STR_PAD_LEFT);}while(array_filter($listings,fn($l)=>($l['pet_id']??'')===$petId));
$listing=['pet_id'=>$petId,'merchant_id'=>$account['account_id'],'name'=>rh_clean_text($input['name'],120),'category'=>$category,'breed'=>rh_clean_text($input['breed'],120),'health_status'=>$health,'temperament'=>$temperament,'reason_for_rehoming'=>$reason,'spay_neuter_status'=>$spay,'listing_type'=>$type,'currency_code'=>$currency,'is_urgent'=>filter_var($input['is_urgent']??false,FILTER_VALIDATE_BOOLEAN),'description'=>rh_clean_text($input['description']??'',4000),'status'=>'active','held_for_review'=>false,'created_at'=>rh_now(),'last_inquiry_date'=>null,'grouped_with'=>[],'needs_extra_love'=>false];
$listings[]=$listing; rh_write_store('listings',$listings); rh_json_response(['success'=>true,'listing'=>$listing]);
/* TASK-MANUS-05 END */
?>
