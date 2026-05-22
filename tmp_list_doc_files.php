<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$r=$db->query("SELECT id_doc_file,file_path,file_name FROM tb_myrep_flow_doc_file ORDER BY id_doc_file DESC LIMIT 5");
while($row=$r->fetch_assoc()){
 $full=__DIR__.DIRECTORY_SEPARATOR.str_replace(['/','\\'],DIRECTORY_SEPARATOR,$row['file_path']);
 $row['exists']=is_file($full)?1:0;
 echo json_encode($row,JSON_UNESCAPED_SLASHES),"\n";
}
$r->free();
?>
