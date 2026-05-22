<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT id_route,module_name,event_name,target_type,target_role,target_user_id,is_active FROM tb_myrep_notification_route ORDER BY module_name,event_name,id_route");
$out=[];while($row=$r->fetch_assoc()){$out[]=$row;} $r->free();
file_put_contents('tmp_myrep_notification_routes.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$summary=[];foreach($out as $row){$k=$row['module_name'].'|'.$row['event_name']; if(!isset($summary[$k])){$summary[$k]=['module_event'=>$k,'count'=>0,'types'=>[]];} $summary[$k]['count']++; $summary[$k]['types'][$row['target_type']]=true;}
foreach($summary as $s){echo $s['module_event'].' => '.$s['count'].' route(s) ['.implode(',',array_keys($s['types']))."]\n";}
?>
