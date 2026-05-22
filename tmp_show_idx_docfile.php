<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$r=$db->query("SHOW INDEX FROM tb_myrep_flow_doc_file");while($row=$r->fetch_assoc()){echo $row['Key_name'].'|'.$row['Seq_in_index'].'|'.$row['Column_name'].'|non_unique='.$row['Non_unique']."\n";} $r->free();
?>
