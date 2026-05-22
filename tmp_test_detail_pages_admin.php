<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='admin' LIMIT 1")->fetch_assoc();
$clusterId=10325;
$base='http://localhost/DatabaseTKM/';
$paths=[
 'MyRepublik_Project/detail/'.$clusterId,
 'Batch_Approval_MyRep/detail/'.$clusterId,
 'DRM_MyRep/detail/'.$clusterId,
 'Implementasi_BOQ_MyRep/detail/'.$clusterId,
 'PO_MyRep/detail/'.$clusterId,
 'Checklist_Dokument_MyRep/detail/'.$clusterId,
 'Checklist_Dokument_MyRep/mainfeeder',
];
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$method,$url,$body=[]){curl_setopt($ch,CURLOPT_URL,$url);if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($body));}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');} $resp=(string)curl_exec($ch); return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'title_ok'=>(stripos($resp,'<title>')!==false),'len'=>strlen($resp)];}
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$out=[];foreach($paths as $p){$out[$p]=req($ch,'GET',$base.$p);}curl_close($ch);
file_put_contents('tmp_myrep_detail_pages_admin.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $k=>$v){echo $k.' => '.$v['status'].' len='.$v['len']."\n";}
?>
