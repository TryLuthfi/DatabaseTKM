<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
foreach(['tb_myrep_batch_approval','tb_myrep_batch_approval_pic','tb_myrep_cluster'] as $t){
  echo "-- $t --\n";
  $r=$db->query("SHOW COLUMNS FROM `$t`");
  while($row=$r->fetch_assoc()){
    echo $row['Field'].' | '.$row['Type'].' | null='.$row['Null'].' | def='.(is_null($row['Default'])?'NULL':$row['Default'])."\n";
  }
  $r->free();
  echo "\n";
}
?>
