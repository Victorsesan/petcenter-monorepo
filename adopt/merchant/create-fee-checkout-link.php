<?php
/** TASK-MANUS-06 START — G1 Stripe Payment Link endpoint. */
header('Content-Type: application/json; charset=utf-8');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'POST required']);exit;}
$input=json_decode((string)file_get_contents('php://input'),true);$listingId=preg_replace('/[^A-Za-z0-9_-]/','',(string)($input['listing_id']??''));$amount=(float)($input['amount']??0);$currency=strtolower((string)($input['currency']??'usd'));
if($listingId===''||$amount<=0||!in_array(strtoupper($currency),['USD','CAD','GBP','AUD'],true)){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Valid listing, positive amount, and supported currency required']);exit;}
$key=getenv('STRIPE_SECRET_KEY');if(!$key){http_response_code(503);echo json_encode(['success'=>false,'message'=>'Stripe is not configured on this server']);exit;}
$payload=http_build_query(['line_items[0][price_data][currency]'=>$currency,'line_items[0][price_data][product_data][name]'=>'Pet Center adoption fee — '.$listingId,'line_items[0][price_data][unit_amount]'=> (string)round($amount*100),'line_items[0][quantity]'=>'1','after_completion[type]'=>'redirect','after_completion[redirect][url]'=>'https://adopt.petcenterco.com/pets/detail/?pet_id='.rawurlencode($listingId)]);
$ch=curl_init('https://api.stripe.com/v1/payment_links');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key,'Content-Type: application/x-www-form-urlencoded'],CURLOPT_TIMEOUT=>20]);$raw=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$result=json_decode((string)$raw,true);
if($status<200||$status>=300||empty($result['url'])){http_response_code(502);echo json_encode(['success'=>false,'message'=>'Stripe could not create the payment link','stripe_error'=>$result['error']['message']??'upstream error']);exit;}
echo json_encode(['success'=>true,'listing_id'=>$listingId,'amount'=>$amount,'currency'=>strtoupper($currency),'url'=>$result['url'],'stripe_id'=>$result['id']??null]);
