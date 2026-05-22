<?php
$env=parse_ini_file(__DIR__.'/.env');
if(!$env){fwrite(STDERR,"env parse failed\n");exit(1);} 
$db=new mysqli($env['HOSTNAME']??'', $env['USERNAME']??'', $env['PASSWORD']??'', $env['DATABASE']??'');
if($db->connect_errno){fwrite(STDERR,$db->connect_error."\n");exit(1);} 
$db->set_charset('utf8mb4');

$meta=[];
$meta['roles']=[];
$r=$db->query("SELECT role_key, COUNT(*) total FROM tb_myrep_role_permission WHERE is_active=1 GROUP BY role_key ORDER BY role_key");
while($row=$r->fetch_assoc()){$meta['roles'][]=$row;}
$r->free();

$meta['page_action']=[];
$r=$db->query("SELECT page_key, action_key, SUM(is_allowed=1) allowed_rows, COUNT(*) total_rows FROM tb_myrep_role_permission WHERE is_active=1 GROUP BY page_key, action_key ORDER BY page_key, action_key");
while($row=$r->fetch_assoc()){$meta['page_action'][]=$row;}
$r->free();

$meta['mapping_columns']=[];
$r=$db->query("SHOW COLUMNS FROM tb_myrep_pic_mapping_city");
while($row=$r->fetch_assoc()){$meta['mapping_columns'][]=$row['Field'];}
$r->free();

$meta['myrep_tables']=[];
$r=$db->query("SHOW TABLES LIKE '%myrep%'");
while($row=$r->fetch_array(MYSQLI_NUM)){$meta['myrep_tables'][]=$row[0];}
$r->free();
file_put_contents(__DIR__.'/tmp_myrep_db_meta.json',json_encode($meta,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

$ts=[];
$sql="SELECT TABLE_NAME,COLUMN_NAME,IS_NULLABLE,COLUMN_DEFAULT,DATA_TYPE FROM information_schema.columns WHERE table_schema=DATABASE() AND (table_name LIKE 'tb_myrep\\_%' OR table_name LIKE 'md_myrep\\_%' OR table_name LIKE 'tb_rfs_myrep\\_%' OR table_name LIKE 'md_rfs_myrep\\_%' OR table_name LIKE 'stg_myrep\\_%') AND column_name IN ('created_at','submitted_at') ORDER BY table_name,column_name";
$r=$db->query($sql);
while($row=$r->fetch_assoc()){$ts[]=$row;}
$r->free();
file_put_contents(__DIR__.'/tmp_myrep_timestamp_columns.json',json_encode($ts,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

$idx=[];
$sql="SELECT table_name,index_name,GROUP_CONCAT(column_name ORDER BY seq_in_index) cols, non_unique FROM information_schema.statistics WHERE table_schema=DATABASE() AND (table_name LIKE 'tb_myrep\\_%' OR table_name LIKE 'tb_rfs_myrep\\_%') GROUP BY table_name,index_name,non_unique ORDER BY table_name,index_name";
$r=$db->query($sql);
while($row=$r->fetch_assoc()){$idx[]=$row;}
$r->free();
file_put_contents(__DIR__.'/tmp_myrep_indexes.json',json_encode($idx,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

$fks=[];
$sql="SELECT table_name,column_name,referenced_table_name,referenced_column_name,constraint_name FROM information_schema.key_column_usage WHERE table_schema=DATABASE() AND referenced_table_name IS NOT NULL AND (table_name LIKE 'tb_myrep\\_%' OR table_name LIKE 'tb_rfs_myrep\\_%' OR table_name LIKE 'md_myrep\\_%' OR table_name LIKE 'md_rfs_myrep\\_%') ORDER BY table_name,constraint_name";
$r=$db->query($sql);
while($row=$r->fetch_assoc()){$fks[]=$row;}
$r->free();
file_put_contents(__DIR__.'/tmp_myrep_foreign_keys.json',json_encode($fks,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

$counts=[];
$tables=array_merge($meta['myrep_tables'], ['tb_master_user_new','tb_myrep_role_permission','tb_myrep_pic_mapping_city']);
$tables=array_values(array_unique($tables));
foreach($tables as $t){
    $safe='`'.str_replace('`','``',$t).'`';
    $q=$db->query("SELECT COUNT(*) c FROM {$safe}");
    if($q){$row=$q->fetch_assoc();$counts[]=['table'=>$t,'rows'=>(int)$row['c']];$q->free();}
}
file_put_contents(__DIR__.'/tmp_myrep_table_counts.json',json_encode($counts,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

echo "collect done\n";
echo "roles=".count($meta['roles']).", page_action=".count($meta['page_action']).", tables=".count($meta['myrep_tables'])."\n";
echo "timestamps=".count($ts).", indexes=".count($idx).", fks=".count($fks)."\n";
?>
