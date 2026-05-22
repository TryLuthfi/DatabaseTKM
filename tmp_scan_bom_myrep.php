<?php
$paths=[];
$iters=[
 new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/application/controllers')),
 new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/application/models')),
 new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/application/views')),
 new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/application/libraries')),
];
foreach($iters as $it){
 foreach($it as $f){
  if(!$f->isFile()) continue;
  $p=$f->getPathname();
  $name=strtolower($p);
  if(strpos($name,'myrep')===false && strpos($name,'checklist_dokument_myrep')===false && strpos($name,'02_menu.php')===false && strpos($name,'myrep_notification_service.php')===false) continue;
  if(!preg_match('/\.(php|js|css|json|sql)$/i',$p)) continue;
  $h=fopen($p,'rb');
  if(!$h) continue;
  $b=fread($h,3);
  fclose($h);
  $hasBom=($b==="\xEF\xBB\xBF");
  $paths[]=['file'=>str_replace('\\','/',$p),'bom'=>$hasBom];
 }
}
$withBom=array_values(array_filter($paths,fn($r)=>$r['bom']));
file_put_contents(__DIR__.'/tmp_myrep_bom_scan.json',json_encode(['total'=>count($paths),'bom_count'=>count($withBom),'bom_files'=>$withBom],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo 'scanned='.count($paths)."\n";
echo 'bom_count='.count($withBom)."\n";
foreach($withBom as $r){echo $r['file']."\n";}
?>
