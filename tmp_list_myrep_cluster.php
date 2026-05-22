<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT id_myrep_cluster,cluster_name,city_name,status_current,created_at FROM tb_myrep_cluster ORDER BY id_myrep_cluster DESC LIMIT 20");
while($row=$r->fetch_assoc()){echo json_encode($row,JSON_UNESCAPED_SLASHES),"\n";} $r->free();
?>
