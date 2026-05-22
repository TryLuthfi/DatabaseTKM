<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$sql="SELECT page_key, action_key, GROUP_CONCAT(role_key ORDER BY role_key SEPARATOR ', ') AS roles FROM tb_myrep_role_permission WHERE is_active=1 AND is_allowed=1 GROUP BY page_key, action_key ORDER BY FIELD(page_key,'BAK_MYREP','VALSAL_MYREP','BATCH_APPROVAL_MYREP','DRM_MYREP','IMPLEMENTASI_BOQ_MYREP','PO_MYREP','MONITORING_RFS_MYREP','ATP_MYREP','CHECKLIST_DOKUMENT_MYREP','MYREPUBLIK_PROJECT'), page_key, action_key";
$r=$db->query($sql);
$out=[];while($row=$r->fetch_assoc()){$out[]=$row;} $r->free();
file_put_contents('tmp_myrep_permission_matrix.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'rows='.count($out)."\n";
foreach($out as $row){echo $row['page_key'].' | '.$row['action_key'].' | '.$row['roles']."\n";}
?>
