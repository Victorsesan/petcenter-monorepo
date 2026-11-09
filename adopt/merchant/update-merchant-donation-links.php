<?php
/** TASK-MANUS-06 START — G1 merchant donation/wishlist link endpoint. */
header('Content-Type: application/json; charset=utf-8');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'POST required']);exit;}
$input=json_decode((string)file_get_contents('php://input'),true);$merchantId=preg_replace('/[^A-Za-z0-9_-]/','',(string)($input['merchant_id']??''));
if($merchantId===''){http_response_code(422);echo json_encode(['success'=>false,'message'=>'merchant_id required']);exit;}
$valid=function($v){$v=trim((string)$v);if($v==='')return '';return filter_var($v,FILTER_VALIDATE_URL)&&preg_match('/^https:\/\//i',$v)?$v:'';};
$path=dirname(__DIR__).'/rehoming/merchants/'.$merchantId.'/profile.json';$dir=realpath(dirname($path));$base=realpath(dirname(__DIR__).'/rehoming/merchants');
if(!$base||!$dir||!($dir===$base||str_starts_with($dir,$base.DIRECTORY_SEPARATOR))){http_response_code(404);echo json_encode(['success'=>false,'message'=>'Merchant not found']);exit;}
$profile=json_decode((string)@file_get_contents($path),true);if(!is_array($profile))$profile=[];$profile['donation_link']=$valid($input['donation_link']??'');$profile['wishlist_link']=$valid($input['wishlist_link']??'');$profile['updated_at']=gmdate('c');
if(@file_put_contents($path,json_encode($profile,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)===false){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Unable to save merchant profile']);exit;}echo json_encode(['success'=>true,'profile'=>$profile]);
