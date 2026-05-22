<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
if($db->connect_errno){fwrite(STDERR,$db->connect_error);exit(1);} 
$db->set_charset('utf8mb4');
$tables=[];
$r=$db->query("SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND (table_name LIKE 'tb_myrep\\_%' OR table_name LIKE 'md_myrep\\_%' OR table_name LIKE 'tb_rfs_myrep\\_%' OR table_name LIKE 'md_rfs_myrep\\_%' OR table_name LIKE 'stg_myrep\\_%') ORDER BY table_name");
while($row=$r->fetch_assoc()){$tables[]=$row['table_name'];}
$r->free();
$has=[];
$r=$db->query("SELECT table_name,column_name FROM information_schema.columns WHERE table_schema=DATABASE() AND (table_name LIKE 'tb_myrep\\_%' OR table_name LIKE 'md_myrep\\_%' OR table_name LIKE 'tb_rfs_myrep\\_%' OR table_name LIKE 'md_rfs_myrep\\_%' OR table_name LIKE 'stg_myrep\\_%') AND column_name IN ('created_at','submitted_at')");
while($row=$r->fetch_assoc()){$has[$row['table_name']][$row['column_name']]=true;}
$r->free();
$missing=[];
foreach($tables as $t){
 $missing[]=['table'=>$t,'has_created_at'=>!empty($has[$t]['created_at']),'has_submitted_at'=>!empty($has[$t]['submitted_at'])];
}
file_put_contents('tmp_myrep_timestamp_coverage.json',json_encode($missing,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$mc=array_filter($missing,fn($x)=>!$x['has_created_at']);
$ms=array_filter($missing,fn($x)=>!$x['has_submitted_at']);
echo 'tables='.count($tables)."\n";
echo 'missing_created_at='.count($mc)."\n";
echo 'missing_submitted_at='.count($ms)."\n";
?>
