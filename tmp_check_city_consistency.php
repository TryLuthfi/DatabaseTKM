<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$map=[];$target=[];$cluster=[];
$r=$db->query("SELECT DISTINCT UPPER(TRIM(city_name)) city FROM tb_myrep_pic_mapping_city WHERE city_name IS NOT NULL AND TRIM(city_name)<>''");while($row=$r->fetch_assoc()){$map[$row['city']]=1;} $r->free();
$r=$db->query("SELECT DISTINCT UPPER(TRIM(city_name)) city FROM tb_rfs_myrep_monthly_target WHERE city_name IS NOT NULL AND TRIM(city_name)<>''");while($row=$r->fetch_assoc()){$target[$row['city']]=1;} $r->free();
$r=$db->query("SELECT DISTINCT UPPER(TRIM(city_name)) city FROM tb_myrep_cluster WHERE city_name IS NOT NULL AND TRIM(city_name)<>''");while($row=$r->fetch_assoc()){$cluster[$row['city']]=1;} $r->free();
$mapNotTarget=array_values(array_diff(array_keys($map),array_keys($target)));
$targetNotMap=array_values(array_diff(array_keys($target),array_keys($map)));
$clusterNotMap=array_values(array_diff(array_keys($cluster),array_keys($map)));
$out=['map_count'=>count($map),'target_count'=>count($target),'cluster_count'=>count($cluster),'map_not_target'=>$mapNotTarget,'target_not_map'=>$targetNotMap,'cluster_not_map'=>$clusterNotMap];
file_put_contents('tmp_city_mapping_consistency.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'map='.count($map).' target='.count($target).' cluster='.count($cluster)."\n";
echo 'map_not_target='.count($mapNotTarget)."\n";
echo 'target_not_map='.count($targetNotMap)."\n";
echo 'cluster_not_map='.count($clusterNotMap)."\n";
?>
