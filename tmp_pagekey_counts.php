<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT page_key,COUNT(*) c FROM tb_myrep_role_permission WHERE is_active=1 GROUP BY page_key ORDER BY page_key");
while($row=$r->fetch_assoc()){echo $row['page_key'].':'.$row['c']."\n";} $r->free();
?>
