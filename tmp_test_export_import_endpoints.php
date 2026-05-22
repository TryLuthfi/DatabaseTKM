<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$u=$db->query("SELECT username_user,password_user FROM tb_master_user_new WHERE username_user='admin' LIMIT 1")->fetch_assoc();
$base='http://localhost/DatabaseTKM/';
$endpoints=[
 'Checklist_Dokument_MyRep/exportItemExcel',
 'BAK_MyRep/downloadReport',
 'VALSAL_MyRep/downloadReport',
 'MyRepublik_Project/downloadCutoffImportTemplate',
 'Monitoring_RFS_MyRep/downloadClusterImportTemplate',
 'BAK_MyRep/downloadClusterImportTemplate',
 'VALSAL_MyRep/downloadValsalImportTemplate',
 'Batch_Approval_MyRep/downloadBatchImportTemplate',
 'DRM_MyRep/downloadDrmImportTemplate'
];
$ch=curl_init();
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_COOKIEFILE=>'',CURLOPT_CONNECTTIMEOUT=>20,CURLOPT_TIMEOUT=>90,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false]);
function req($ch,$method,$url,$body=[]){
 curl_setopt($ch,CURLOPT_URL,$url);
 if($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_HTTPGET,false);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($body));}
 else{curl_setopt($ch,CURLOPT_POST,false);curl_setopt($ch,CURLOPT_HTTPGET,true);curl_setopt($ch,CURLOPT_POSTFIELDS,'');}
 $resp=(string)curl_exec($ch);
 $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
 $headerSize=(int)curl_getinfo($ch,CURLINFO_HEADER_SIZE);
 $headers=substr($resp,0,$headerSize);
 $bodyResp=substr($resp,$headerSize);
 $ctype='';
 if(preg_match('/Content-Type:\s*([^\r\n]+)/i',$headers,$m)){$ctype=trim($m[1]);}
 return ['status'=>$status,'content_type'=>$ctype,'body_len'=>strlen($bodyResp)];
}
req($ch,'GET',$base.'Auth');
req($ch,'POST',$base.'Auth',['username'=>$u['username_user'],'pass'=>$u['password_user']]);
$out=[];foreach($endpoints as $ep){$out[$ep]=req($ch,'GET',$base.$ep);}curl_close($ch);
file_put_contents('tmp_myrep_export_import_endpoints.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $k=>$v){echo $k.' => '.$v['status'].' | '.$v['content_type'].' | len='.$v['body_len']."\n";}
?>
