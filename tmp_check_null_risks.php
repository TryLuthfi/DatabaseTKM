<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$checks=[
"SELECT 'mapping_city_blank' k, COUNT(*) c FROM tb_myrep_pic_mapping_city WHERE city_name IS NULL OR TRIM(city_name)=''",
"SELECT 'mapping_inactive' k, COUNT(*) c FROM tb_myrep_pic_mapping_city WHERE IFNULL(is_active,1)=0",
"SELECT 'user_active_blank_username' k, COUNT(*) c FROM tb_master_user_new WHERE UPPER(IFNULL(status_user,''))='ACTIVE' AND (username_user IS NULL OR TRIM(username_user)='')",
"SELECT 'cluster_city_blank' k, COUNT(*) c FROM tb_myrep_cluster WHERE city_name IS NULL OR TRIM(city_name)=''",
"SELECT 'target_city_blank' k, COUNT(*) c FROM tb_rfs_myrep_monthly_target WHERE city_name IS NULL OR TRIM(city_name)=''",
"SELECT 'notif_route_city_role_missing_role' k, COUNT(*) c FROM tb_myrep_notification_route WHERE target_type='CITY_ROLE' AND (target_role IS NULL OR TRIM(target_role)='')",
"SELECT 'notif_route_fixed_missing_user' k, COUNT(*) c FROM tb_myrep_notification_route WHERE target_type='FIXED_USER' AND (target_user_id IS NULL OR target_user_id=0)",
"SELECT 'notif_route_cluster_pic_missing' k, COUNT(*) c FROM tb_myrep_notification_route WHERE target_type='CLUSTER_PIC' AND target_user_id IS NOT NULL AND target_user_id<>0"
];
$out=[];
foreach($checks as $sql){$r=$db->query($sql);$out[]=$r->fetch_assoc();$r->free();}
file_put_contents('tmp_myrep_null_risk_checks.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $row){echo $row['k'].':'.$row['c']."\n";}
?>
