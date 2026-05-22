<?php
declare(strict_types=1);
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$user='9807922';
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='".$db->real_escape_string($user)."' LIMIT 1")->fetch_assoc();
if(!$u){echo "user missing\n";exit(1);} 
$fileId=314;
$before=$db->query("SELECT status_file,remark,approved_by,reviewed_at,approved_at,updated_at FROM tb_myrep_flow_doc_file WHERE id_doc_file={$fileId}")->fetch_assoc();
$base='http://localhost/DatabaseTKM/';
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>10,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>45,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$m,$url,$b=[]){curl_setopt($ch,CURLOPT_URL,$url);if($m==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($b));}else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}return ['body'=>(string)curl_exec($ch),'status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE)];}
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$a=req($ch,'POST',$base.'BAK_MyRep/approveDocument',['id_doc_file'=>$fileId,'remark'=>'unauthorized-approve-test']);
$r=req($ch,'POST',$base.'BAK_MyRep/rejectDocument',['id_doc_file'=>$fileId,'remark'=>'unauthorized-reject-test']);
curl_close($ch);
$after=$db->query("SELECT status_file,remark,approved_by,reviewed_at,approved_at,updated_at FROM tb_myrep_flow_doc_file WHERE id_doc_file={$fileId}")->fetch_assoc();
$out=['before'=>$before,'after'=>$after,'approve_http'=>$a['status'],'approve_contains_no_access'=>(stripos(strtoupper($a['body']),'TIDAK MEMILIKI AKSES')!==false),'reject_http'=>$r['status'],'reject_contains_no_access'=>(stripos(strtoupper($r['body']),'TIDAK MEMILIKI AKSES')!==false),'unchanged'=>$before==$after];
file_put_contents('tmp_unauthorized_approve_reject_bak.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode($out),"\n";
?>
