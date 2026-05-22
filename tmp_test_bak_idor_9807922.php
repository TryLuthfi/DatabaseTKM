<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='9807922' LIMIT 1")->fetch_assoc();
$base='http://localhost/DatabaseTKM/';
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$method,$url,$body=[]){curl_setopt($ch,CURLOPT_URL,$url);if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($body));}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');} $resp=(string)curl_exec($ch); return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body_head'=>substr($resp,0,160)];}
req($ch,'GET',$base.'Auth'); req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$tests=['BAK_MyRep/previewDocument/314','BAK_MyRep/downloadDocument/314','BAK_MyRep/downloadDocumentBundle/10325'];
$out=[];foreach($tests as $t){$out[$t]=req($ch,'GET',$base.$t);} curl_close($ch);
file_put_contents('tmp_city_idor_bak_user9807922.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $k=>$v){echo $k.' => '.$v['status']."\n";}
?>
