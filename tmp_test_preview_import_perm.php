<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='9314325' LIMIT 1")->fetch_assoc();
if(!$u){echo "user missing\n"; exit(1);} 
$base='http://localhost/DatabaseTKM/';
$endpoints=['BAK_MyRep/previewClusterImport','VALSAL_MyRep/previewValsalImport','Batch_Approval_MyRep/previewBatchImport','DRM_MyRep/previewDrmImport','Monitoring_RFS_MyRep/previewClusterImport','MyRepublik_Project/previewCutoffImport'];
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$url,$post=[]){curl_setopt($ch,CURLOPT_URL,$url);curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($post));$body=(string)curl_exec($ch);return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'head'=>substr($body,0,80)];}
req($ch,$base.'Auth',[]);
req($ch,$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$out=[];
foreach($endpoints as $ep){$out[$ep]=req($ch,$base.$ep,['year'=>2026,'month'=>5]);}
curl_close($ch);
file_put_contents('tmp_preview_import_permission_user9314325.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $k=>$v){echo $k.' => '.$v['status']."\n";}
?>
