<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$r=$db->query("SHOW COLUMNS FROM tb_myrep_notification_route");while($row=$r->fetch_assoc()){echo $row['Field']."\n";} $r->free();
?>
