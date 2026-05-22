<?php
declare(strict_types=1);
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$users=[];
foreach(['admin','9807922'] as $uname){
 $r=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='".$db->real_escape_string($uname)."' LIMIT 1");
 $u=$r?$r->fetch_assoc():null; if($r){$r->free();}
 if($u){$users[$uname]=$u;}
}
$pages=['MyRepublik_Project','BAK_MyRep','VALSAL_MyRep','Batch_Approval_MyRep','DRM_MyRep','Implementasi_BOQ_MyRep','PO_MyRep','Monitoring_RFS_MyRep','ATP_MyRep','Checklist_Dokument_MyRep'];
$base='http://localhost/DatabaseTKM/';
$out=[];
foreach($users as $key=>$u){
 $ch=curl_init();
 curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>90,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
 $req=function($method,$url,$body=[]) use ($ch){
  curl_setopt($ch,CURLOPT_URL,$url);
  if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($body));}
  else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}
  $t0=microtime(true);
  $bodyResp=(string)curl_exec($ch);
  $ms=(microtime(true)-$t0)*1000;
  return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'ms'=>round($ms,2),'size'=>strlen($bodyResp)];
 };
 $req('GET',$base.'Auth');
 $req('POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
 foreach($pages as $p){
  $out[]=['user'=>$key,'path'=>$p]+$req('GET',$base.$p);
 }
 curl_close($ch);
}
file_put_contents('tmp_myrep_perf_baseline.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $r){echo $r['user'].' | '.$r['path'].' | '.$r['status'].' | '.$r['ms'].'ms | '.$r['size']."\n";}
?>
