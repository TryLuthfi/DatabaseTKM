<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$r=$db->query("SHOW COLUMNS FROM tb_myrep_flow_doc_file");while($row=$r->fetch_assoc()){echo $row['Field'],"\n";} $r->free();
?>
