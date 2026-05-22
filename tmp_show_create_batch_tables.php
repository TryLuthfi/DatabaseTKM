<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
foreach(['tb_myrep_batch_approval','tb_myrep_batch_approval_pic'] as $t){
  $r=$db->query("SHOW CREATE TABLE `$t`");
  $row=$r->fetch_assoc();
  echo "-- $t --\n";
  echo ($row['Create Table'] ?? ''),"\n\n";
  $r->free();
}
?>
