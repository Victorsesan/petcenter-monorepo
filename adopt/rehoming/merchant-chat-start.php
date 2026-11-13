<?php
/** TASK-MANUS-05 START — Create one merchant/visitor thread and issue account-scoped token. */
declare(strict_types=1);
require_once __DIR__ . '/merchant-platform-lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rh_json_response(['success'=>false,'error'=>'POST required'],405);
$account = rh_auth_account();
/** TASK-WEB-04 START — Enquiry creation requires a visitor role, CSRF and valid target ownership. */
rh_require_csrf();
$input = rh_input();
$merchantId = rh_clean_id($input['merchant_id'] ?? '');
$listingId = rh_clean_id($input['listing_id'] ?? '');
$petName = rh_clean_text($input['pet_name'] ?? '', 180);
$species = rh_clean_text($input['species'] ?? '', 80);
if ($account['account_type'] !== 'visitor') rh_json_response(['success'=>false,'error'=>'Only visitors can start an enquiry'],403);
if ($merchantId === '') rh_json_response(['success'=>false,'error'=>'merchant_id is required'],422);
$merchant = web04_find_account_by_id($merchantId);
if (!$merchant || ($merchant['status'] ?? '') !== 'active' || ($merchant['account_type'] ?? '') !== 'merchant') rh_json_response(['success'=>false,'error'=>'Merchant not found'],404);
if ($listingId !== '') {
    $listing = null;
    foreach (rh_read_store('listings') as $candidate) if (($candidate['pet_id'] ?? '') === $listingId) { $listing = $candidate; break; }
    if (!$listing || ($listing['merchant_id'] ?? '') !== $merchantId) rh_json_response(['success'=>false,'error'=>'Listing not found'],404);
}
/** TASK-WEB-04 END */
$threads = rh_read_store('threads');
foreach ($threads as $thread) {
    if (($thread['merchant_id'] ?? '') === $merchantId && ($thread['visitor_id'] ?? '') === $account['account_id'] && ($thread['listing_id'] ?? '') === $listingId) {
        $token = rh_issue_token($account);
        rh_json_response(['success'=>true,'thread'=>$thread,'token'=>$token,'existing'=>true]);
    }
}
$thread = ['thread_id'=>rh_id('THR'),'merchant_id'=>$merchantId,'visitor_id'=>$account['account_id'],'listing_id'=>$listingId,'pet_name'=>$petName,'species'=>$species,'status'=>'open','created_at'=>rh_now(),'updated_at'=>rh_now(),'message_count'=>0];
$threads[] = $thread;
rh_write_store('threads',$threads);
$token = rh_issue_token($account);
rh_json_response(['success'=>true,'thread'=>$thread,'token'=>$token,'draft'=>trim("Hello, I am interested in {$petName} ({$species}). Could you tell me more about this pet?")]);
/* TASK-MANUS-05 END */
?>
