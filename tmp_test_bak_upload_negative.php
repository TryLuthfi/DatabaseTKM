<?php
declare(strict_types=1);
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$user=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='admin' LIMIT 1")->fetch_assoc();
if(!$user){echo "admin user not found\n";exit(1);} 
$clusterId=10325; $docItemId=22;
$before=(int)$db->query("SELECT COUNT(*) c FROM tb_myrep_flow_doc_file WHERE id_doc_item={$docItemId} AND id_doc_package IN (SELECT id_doc_package FROM tb_myrep_flow_doc_package WHERE id_myrep_cluster={$clusterId})")->fetch_assoc()['c'];

$tmp=__DIR__.'/tmp_malicious_upload.php';
file_put_contents($tmp,"<?php echo 'x'; ?>");
$base='http://localhost/DatabaseTKM/';
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>false,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_COOKIEFILE=>'',CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$method,$url,$body=[]){curl_setopt($ch,CURLOPT_URL,$url);if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,$body);}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}return ['body'=>(string)curl_exec($ch),'status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'err'=>curl_error($ch)];}
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$user['username_user'],'pass'=>$user['password_user']]);

$r1=req($ch,'POST',$base.'BAK_MyRep/uploadDocument',[
 'cluster_id'=>$clusterId,
 'doc_item_id'=>$docItemId,
 'remark'=>'test-missing-file',
]);

$r2=req($ch,'POST',$base.'BAK_MyRep/uploadDocument',[
 'cluster_id'=>$clusterId,
 'doc_item_id'=>$docItemId,
 'remark'=>'test-invalid-ext',
 'file'=>new CURLFile($tmp,'application/x-php','payload.php'),
]);

$after=(int)$db->query("SELECT COUNT(*) c FROM tb_myrep_flow_doc_file WHERE id_doc_item={$docItemId} AND id_doc_package IN (SELECT id_doc_package FROM tb_myrep_flow_doc_package WHERE id_myrep_cluster={$clusterId})")->fetch_assoc()['c'];
@unlink($tmp);
curl_close($ch);

$out=[
 'before_count'=>$before,
 'after_count'=>$after,
 'missing_file'=>['status'=>$r1['status'],'has_error_msg'=>(stripos($r1['body'],'wajib dipilih')!==false || stripos($r1['body'],'error')!==false)],
 'invalid_extension'=>['status'=>$r2['status'],'has_error_msg'=>(stripos($r2['body'],'filetype')!==false || stripos($r2['body'],'type file')!==false || stripos($r2['body'],'tidak diperbolehkan')!==false || stripos($r2['body'],'error')!==false)],
];
file_put_contents('tmp_upload_negative_bak_admin.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode($out),"\n";
?>
