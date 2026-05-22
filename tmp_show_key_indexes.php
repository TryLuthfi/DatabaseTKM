<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$tables=['tb_myrep_role_permission','tb_myrep_pic_mapping_city','tb_rfs_myrep_monthly_target','tb_rfs_myrep_cluster','tb_rfs_myrep_claim','tb_myrep_cluster'];
foreach($tables as $t){
 echo "-- {$t} --\n";
 $r=$db->query("SHOW INDEX FROM `{$t}`");
 while($row=$r->fetch_assoc()){
  echo $row['Key_name'].'|'.$row['Seq_in_index'].'|'.$row['Column_name'].'|non_unique='.$row['Non_unique']."\n";
 }
 $r->free();
 echo "\n";
}
?>
