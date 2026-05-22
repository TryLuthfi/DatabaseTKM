<?php
$env=parse_ini_file('.env');
$db=new mysqli($env['HOSTNAME'],$env['USERNAME'],$env['PASSWORD'],$env['DATABASE']);
$db->set_charset('utf8mb4');
$tables=['tb_myrep_flow_doc_file','tb_rfs_myrep_doc_file','tb_rfs_myrep_mainfeeder_doc_file','tb_rfs_myrep_atp_file','tb_myrep_boq_progress_photo','tb_myrep_impl_daily_activity_photo'];
$out=[];
foreach($tables as $t){
 if(!$db->query("SHOW TABLES LIKE '{$t}'")->num_rows){continue;}
 $q=$db->query("SELECT * FROM `{$t}`");
 $empty=0;$missing=0;$total=0;
 while($row=$q->fetch_assoc()){
  $total++;
  $path='';
  foreach(['file_path','photo_path'] as $pc){ if(isset($row[$pc])){$path=trim((string)$row[$pc]); if($path!=='') break;} }
  if($path===''){ $empty++; continue; }
  $full=__DIR__.DIRECTORY_SEPARATOR.str_replace(['/','\\'],DIRECTORY_SEPARATOR,$path);
  if(!is_file($full)){ $missing++; }
 }
 $q->free();
 $out[]=['table'=>$t,'total'=>$total,'empty_path'=>$empty,'missing_file'=>$missing];
}
file_put_contents('tmp_myrep_file_integrity.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $r){echo $r['table'].' total='.$r['total'].' empty='.$r['empty_path'].' missing='.$r['missing_file']."\n";}
?>
