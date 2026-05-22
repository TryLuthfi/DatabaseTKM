<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='9807922' LIMIT 1");$u=$r->fetch_assoc();$r->free();
$base='http://localhost/DatabaseTKM/';
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_COOKIEFILE=>'',CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$m,$u,$b=[]){curl_setopt($ch,CURLOPT_URL,$u);if($m==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($b));}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}return (string)curl_exec($ch);} 
req($ch,'GET',$base.'Auth');req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$body=req($ch,'GET',$base.'Monitoring_RFS_MyRep');
file_put_contents('tmp_monitoring_9807922.html',$body);
curl_close($ch);
echo "saved\n";
?>
