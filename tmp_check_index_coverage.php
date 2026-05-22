<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$targets=[
 ['tb_myrep_cluster',['city_name','status_current','created_at','regional_name','province_name']],
 ['tb_myrep_flow_doc_file',['id_doc_package','id_doc_item','status_file','uploaded_by','approved_by','created_at']],
 ['tb_myrep_flow_doc_package',['id_myrep_cluster','flow_type','status_package','id_doc_group']],
 ['tb_rfs_myrep_cluster',['city_name','status_cluster','status_rfs','created_at']],
 ['tb_rfs_myrep_doc_file',['id_doc_package','id_doc_item','status_file','uploaded_by','approved_by','created_at']],
 ['tb_myrep_pic_mapping_city',['city_name','rpm_area','sm_area','spv_area','snd_area','admin_area','snd_ho','rfs_ho','sitac_ho','dc_ho']],
 ['tb_myrep_role_permission',['page_key','action_key','role_key','is_allowed','is_active']],
];
$idx=[];
$r=$db->query("SELECT table_name,index_name,column_name,seq_in_index FROM information_schema.statistics WHERE table_schema=DATABASE() ORDER BY table_name,index_name,seq_in_index");
while($row=$r->fetch_assoc()){$idx[$row['table_name']][$row['column_name']][]=$row['index_name'];}
$r->free();
$out=[];
foreach($targets as [$table,$cols]){
 foreach($cols as $c){
   $out[]=['table'=>$table,'column'=>$c,'indexed'=>isset($idx[$table][$c]),'indexes'=>isset($idx[$table][$c])?array_values(array_unique($idx[$table][$c])):[]];
 }
}
file_put_contents('tmp_myrep_index_coverage_targets.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$missing=array_values(array_filter($out,fn($r)=>!$r['indexed']));
echo 'target_checks='.count($out)."\n";
echo 'missing_target_indexes='.count($missing)."\n";
foreach($missing as $m){echo $m['table'].'.'.$m['column']."\n";}
?>
