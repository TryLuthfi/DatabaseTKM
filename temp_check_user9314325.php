<?php
$env = parse_ini_file('.env');
$mysqli = new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
if ($mysqli->connect_error) { echo $mysqli->connect_error; exit(1);} 
$mysqli->set_charset('utf8mb4');
$nik='9314325';
$res=$mysqli->query("SELECT id,nik,nama_karyawan,nama_level,lokasi_user,status_user FROM tb_master_user_new WHERE nik='{$nik}' LIMIT 1");
$row=$res?$res->fetch_assoc():null;
var_export($row);
?>
