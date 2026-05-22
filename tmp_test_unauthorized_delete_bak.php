<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='9807922' LIMIT 1")->fetch_assoc();
$before=(int)$db->query("SELECT COUNT(*) c FROM tb_myrep_cluster WHERE id_myrep_cluster=10325")->fetch_assoc()['c'];
$base='http://localhost/DatabaseTKM/';
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$m,$url,$b=[]){curl_setopt($ch,CURLOPT_URL,$url);if($m==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($b));}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}$resp=(string)curl_exec($ch);return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body'=>$resp];}
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$res=req($ch,'POST',$base.'BAK_MyRep/deleteCluster',['cluster_id'=>10325]);
curl_close($ch);
$after=(int)$db->query("SELECT COUNT(*) c FROM tb_myrep_cluster WHERE id_myrep_cluster=10325")->fetch_assoc()['c'];
$out=['http_status'=>$res['status'],'deleted'=>(($before===1)&&($after===0)),'row_before'=>$before,'row_after'=>$after];
file_put_contents('tmp_unauthorized_delete_bak.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode($out),"\n";
?>
