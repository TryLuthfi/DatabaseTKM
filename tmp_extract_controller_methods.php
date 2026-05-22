<?php
$controllers=[
'BAK_MyRep.php','VALSAL_MyRep.php','Batch_Approval_MyRep.php','DRM_MyRep.php','Implementasi_BOQ_MyRep.php','PO_MyRep.php','Monitoring_RFS_MyRep.php','ATP_MyRep.php','Checklist_Dokument_MyRep.php','MyRepublik_Project.php','SuperAdmin_MyRep_Config.php','SuperAdmin_MyRep_CityMapping.php','Post_Donasi_MyRep.php'
];
$out=[];
foreach($controllers as $f){
  $path=__DIR__.'/application/controllers/'.$f;
  if(!is_file($path)) continue;
  $src=file_get_contents($path);
  preg_match_all('/public function\s+([A-Za-z0-9_]+)\s*\(/',$src,$m);
  $out[$f]=$m[1];
}
file_put_contents('tmp_myrep_controller_public_methods.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
foreach($out as $f=>$methods){echo $f.' => '.count($methods)." methods\n";}
?>
