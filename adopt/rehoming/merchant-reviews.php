<?php
/** TASK-MANUS-05 START — Review API: message eligibility, one mutable thumb, optional text. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
$account=rh_auth_account();
$method=$_SERVER['REQUEST_METHOD'];
$input=$method==='POST'?rh_input():$_GET;
$merchantId=rh_clean_id($input['merchant_id']??'');
if($merchantId==='') rh_json_response(['success'=>false,'error'=>'merchant_id is required'],422);
if($method==='GET'){
    $reviews=array_values(array_filter(rh_read_store('reviews'),fn($r)=>($r['merchant_id']??'')===$merchantId && ($r['visibility']??'visible')==='visible'));
    $up=count(array_filter($reviews,fn($r)=>($r['thumb']??'')==='up')); $down=count(array_filter($reviews,fn($r)=>($r['thumb']??'')==='down'));
    rh_json_response(['success'=>true,'counts'=>['up'=>$up,'down'=>$down],'reviews'=>$reviews]);
}
if($method!=='POST') rh_json_response(['success'=>false,'error'=>'GET or POST required'],405);
/** TASK-WEB-04 START — Review writes are CSRF-protected and limited to visitor principals. */
rh_require_csrf();
if($account['account_type']!=='visitor') rh_json_response(['success'=>false,'error'=>'Only visitors can review'],403);
$eligible=false; foreach(rh_read_store('threads') as $thread) if(($thread['merchant_id']??'')===$merchantId && ($thread['visitor_id']??'')===$account['account_id'] && (int)($thread['message_count']??0)>0){$eligible=true;break;}
if(!$eligible) rh_json_response(['success'=>false,'error'=>'A completed enquiry is required before reviewing this merchant'],403);
$rawListingId=$input['listing_id']??'';
if($rawListingId!=='' && !is_string($rawListingId)) rh_json_response(['success'=>false,'error'=>'listing_id is invalid'],422);
$listingId=is_string($rawListingId)?rh_clean_id($rawListingId):'';
if($rawListingId!=='' && $listingId==='') rh_json_response(['success'=>false,'error'=>'listing_id is invalid'],422);
if($listingId!==''){
    $ownedListing=false;
    foreach(rh_read_store('listings') as $listing) if(($listing['pet_id']??'')===$listingId && ($listing['merchant_id']??'')===$merchantId){$ownedListing=true;break;}
    if(!$ownedListing) rh_json_response(['success'=>false,'error'=>'listing_id does not belong to this merchant'],422);
}
$thumb=$input['thumb']??''; $text=rh_clean_text($input['review_text']??'',1000);
if(!in_array($thumb,['up','down'],true)) rh_json_response(['success'=>false,'error'=>'thumb must be up or down'],422);
if($text!=='' && rh_text_length($text)<10) rh_json_response(['success'=>false,'error'=>'Written reviews must be at least 10 characters'],422);
if($account['is_test_account']) rh_json_response(['success'=>true,'recorded'=>false,'message'=>'Thank you for your feedback.']);
$reviews=rh_read_store('reviews'); $found=false;
foreach($reviews as &$review){if(($review['merchant_id']??'')===$merchantId && ($review['reviewer_id']??'')===$account['account_id'] && ($review['visibility']??'visible')!=='deleted'){$review['thumb']=$thumb;$review['review_text']=$text;$review['created_at']=rh_now();$found=true;break;}}
unset($review);
if(!$found)$reviews[]=['review_id'=>rh_id('REV'),'merchant_id'=>$merchantId,'reviewer_id'=>$account['account_id'],'listing_id'=>$listingId?:null,'thumb'=>$thumb,'review_text'=>$text,'created_at'=>rh_now(),'merchant_reply'=>null,'merchant_reply_at'=>null,'visibility'=>'visible','is_seeded'=>false];
rh_write_store('reviews',$reviews); rh_json_response(['success'=>true,'recorded'=>true,'message'=>'Thank you for your feedback.']);
/** TASK-WEB-04 END */
/* TASK-MANUS-05 END */
?>
