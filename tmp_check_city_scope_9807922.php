<?php
declare(strict_types=1);
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$user='9807922';
$r=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='".$db->real_escape_string($user)."' LIMIT 1");
$row=$r?$r->fetch_assoc():null; if($r){$r->free();}
if(!$row){echo "user not found\n";exit(1);} 
$base='http://localhost/DatabaseTKM/';
$clusterNeedle='CLUSTER BAKUNG REGENCY';
$cityNeedle='BLITAR';

function req($ch,$method,$url,$body=[]){
 curl_setopt($ch,CURLOPT_URL,$url);
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
 return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body'=>$resp,'error'=>curl_error($ch)];
}

$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>false,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_COOKIEFILE=>'',CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$row['username_user'],'pass'=>$row['password_user']]);
$dash=req($ch,'GET',$base.'Dashboard');
$loginOk=$dash['status']===200 && stripos($dash['body'],'Dashboard')!==false;

$pages=['MyRepublik_Project','BAK_MyRep','VALSAL_MyRep','Batch_Approval_MyRep','DRM_MyRep','Implementasi_BOQ_MyRep','PO_MyRep','Monitoring_RFS_MyRep','ATP_MyRep','Checklist_Dokument_MyRep'];
$out=['login_ok'=>$loginOk,'checks'=>[]];
foreach($pages as $p){
 $res=req($ch,'GET',$base.$p);
 $u=strtoupper($res['body']);
 $out['checks'][]=[
  'type'=>'page','path'=>$p,'status'=>$res['status'],
  'contains_cluster'=>(strpos($u,strtoupper($clusterNeedle))!==false),
  'contains_city'=>(strpos($u,$cityNeedle)!==false),
  'no_access'=>(strpos($u,'YOU ARE NOT ALLOWED TO ENTER HERE')!==false)
 ];
}

$apis=[
 ['Checklist_Dokument_MyRep/itemTableData','POST',['draw'=>1,'start'=>0,'length'=>10,'selected_city'=>'','selected_regional'=>'']],
 ['BAK_MyRep/getDistrictOptions?city_name=BLITAR','GET',[]],
 ['BAK_MyRep/getVillageOptions?city_name=BLITAR&district_name=BAKUNG','GET',[]],
];
foreach($apis as $a){
 $res=req($ch,$a[1],$base.$a[0],$a[2]);
 $u=strtoupper($res['body']);
 $out['checks'][]=[
  'type'=>'api','path'=>$a[0],'status'=>$res['status'],
  'contains_cluster'=>(strpos($u,strtoupper($clusterNeedle))!==false),
  'contains_city'=>(strpos($u,$cityNeedle)!==false),
  'body_head'=>substr(preg_replace('/\s+/',' ',trim($res['body'])),0,220)
 ];
}

curl_close($ch);
file_put_contents('tmp_city_scope_user9807922.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode(['login_ok'=>$loginOk,'checks'=>count($out['checks'])]),"\n";
?>
