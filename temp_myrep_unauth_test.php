<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');
$base='http://localhost/DatabaseTKM/';
$tests=[
 ['GET','BAK_MyRep'],
 ['GET','VALSAL_MyRep'],
 ['GET','Batch_Approval_MyRep'],
 ['GET','DRM_MyRep'],
 ['GET','Implementasi_BOQ_MyRep'],
 ['GET','PO_MyRep'],
 ['GET','Monitoring_RFS_MyRep'],
 ['GET','ATP_MyRep'],
 ['GET','Checklist_Dokument_MyRep'],
 ['POST','Checklist_Dokument_MyRep/itemTableData',['draw'=>1,'start'=>0,'length'=>5]],
 ['GET','BAK_MyRep/getDistrictOptions?city_name=PRABUMULIH'],
 ['GET','BAK_MyRep/previewDocument/313'],
 ['GET','VALSAL_MyRep/previewDocument/1'],
 ['GET','Batch_Approval_MyRep/previewDocument/1'],
 ['GET','DRM_MyRep/previewDocument/1'],
 ['GET','Checklist_Dokument_MyRep/previewDocument/1'],
 ['POST','BAK_MyRep/uploadDocument',['cluster_id'=>10325,'doc_item_id'=>22,'is_document_not_required'=>1]],
 ['POST','VALSAL_MyRep/uploadDocument',['cluster_id'=>10325,'doc_item_id'=>22,'is_document_not_required'=>1]],
 ['POST','Batch_Approval_MyRep/uploadDocument',['cluster_id'=>10325,'doc_item_id'=>22,'is_document_not_required'=>1]],
 ['POST','DRM_MyRep/uploadDocument',['cluster_id'=>10325,'doc_item_id'=>22,'is_document_not_required'=>1]],
 ['POST','Checklist_Dokument_MyRep/uploadDocument',['cluster_id'=>10325,'id_doc_item'=>22,'id_doc_package'=>106,'is_document_not_required'=>1]],
];

$ch=curl_init();
curl_setopt_array($ch,[
 CURLOPT_RETURNTRANSFER=>true,
 CURLOPT_HEADER=>false,
 CURLOPT_FOLLOWLOCATION=>false,
 CURLOPT_CONNECTTIMEOUT=>20,
 CURLOPT_TIMEOUT=>30,
 CURLOPT_COOKIEFILE=>'',
 CURLOPT_SSL_VERIFYPEER=>false,
 CURLOPT_SSL_VERIFYHOST=>false,
]);

$out=[];
foreach($tests as $t){
 $method=$t[0];$path=$t[1];$body=$t[2]??[];
 curl_setopt($ch,CURLOPT_URL,$base.$path);
 if($method==='POST'){
   curl_setopt($ch,CURLOPT_POST,true);
   curl_setopt($ch,CURLOPT_HTTPGET,false);
   curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($body));
 } else {
   curl_setopt($ch,CURLOPT_POST,false);
   curl_setopt($ch,CURLOPT_HTTPGET,true);
   curl_setopt($ch,CURLOPT_POSTFIELDS,'');
 }
 $resp=(string)curl_exec($ch);
 $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
 $isBlocked=in_array($status,[301,302,303,307,401,403],true) || stripos($resp,'name="username"')!==false || stripos($resp,'Auth')!==false;
 $out[]=[
   'method'=>$method,
   'path'=>$path,
   'status'=>$status,
   'blocked'=>$isBlocked,
   'body_head'=>substr(preg_replace('/\s+/',' ',trim($resp)),0,160),
 ];
}
curl_close($ch);
$summary=['total'=>count($out),'blocked'=>count(array_filter($out,fn($r)=>$r['blocked'])),'unblocked'=>count(array_filter($out,fn($r)=>!$r['blocked']))];
file_put_contents(__DIR__.'/tmp_myrep_unauth_test_result.json',json_encode(['summary'=>$summary,'results'=>$out],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode($summary),"\n";
?>
