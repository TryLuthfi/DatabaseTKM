<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$queries=[
"SELECT flow_type,COUNT(*) c FROM tb_myrep_flow_doc_package GROUP BY flow_type",
"SELECT COUNT(*) c FROM tb_myrep_flow_doc_file",
"SELECT COUNT(*) c FROM tb_rfs_myrep_doc_file",
"SELECT COUNT(*) c FROM tb_rfs_myrep_mainfeeder_doc_file",
"SELECT COUNT(*) c FROM tb_myrep_batch_approval",
"SELECT COUNT(*) c FROM tb_myrep_drm",
"SELECT COUNT(*) c FROM tb_myrep_valsal",
"SELECT COUNT(*) c FROM tb_myrep_bak"
];
foreach($queries as $q){echo "SQL: $q\n"; $r=$db->query($q); while($row=$r->fetch_assoc()){echo json_encode($row),"\n";} $r->free(); echo "\n";}
?>
