<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT id_doc_package,id_myrep_cluster,flow_type,ref_process_id,id_doc_group,status_package FROM tb_myrep_flow_doc_package ORDER BY id_doc_package LIMIT 30");while($row=$r->fetch_assoc()){echo json_encode($row,JSON_UNESCAPED_SLASHES),"\n";} $r->free();
?>
