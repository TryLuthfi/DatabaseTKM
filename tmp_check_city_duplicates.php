<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT UPPER(TRIM(city_name)) city, COUNT(*) c FROM tb_myrep_pic_mapping_city GROUP BY UPPER(TRIM(city_name)) HAVING COUNT(*)>1 ORDER BY c DESC, city ASC");
$rows=[];while($row=$r->fetch_assoc()){$rows[]=$row;} $r->free();
file_put_contents('tmp_city_mapping_duplicates.json',json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'duplicates='.count($rows)."\n";
foreach(array_slice($rows,0,10) as $row){echo $row['city'].':'.$row['c']."\n";}
?>
