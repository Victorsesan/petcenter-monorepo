<?php
/** TASK-MANUS-06 START — F1 saved-search registration endpoint. */
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'POST required']);exit;}
$input=json_decode((string)file_get_contents('php://input'),true);$email=filter_var($input['email']??'',FILTER_VALIDATE_EMAIL);$name=trim((string)($input['name']??''));$filters=is_array($input['filters']??null)?$input['filters']:[];
if(!$email){http_response_code(422);echo json_encode(['success'=>false,'message'=>'A valid email is required']);exit;}
$file=__DIR__.'/visitor-saved-searches.json';$rows=json_decode((string)@file_get_contents($file),true);$rows=is_array($rows)?$rows:[];$found=false;
foreach($rows as &$row){if(strcasecmp((string)($row['email']??''),$email)===0&&strcasecmp((string)($row['name']??''),$name)===0){$row['filters']=$filters;$row['updated_at']=gmdate('c');$found=true;break;}}
if(!$found)$rows[]=['email'=>$email,'name'=>$name?:'Saved search','filters'=>$filters,'digest_opt_out'=>false,'created_at'=>gmdate('c'),'updated_at'=>gmdate('c')];
if(@file_put_contents($file,json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)===false){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Storage unavailable']);exit;}echo json_encode(['success'=>true,'message'=>'Saved search registered']);
