<?php
/** CIKO independent offline report projection QC. No database or business writes. */
define('BASEPATH', dirname(__DIR__) . '/system/');
class CI_Model { public $db; }
require dirname(__DIR__) . '/application/models/MPO_Monitor.php';
$checks=0;
function qc($ok,$label){global$checks;++$checks;if(!$ok)throw new RuntimeException($label);}
function centsQc($value){return (int)round((float)str_replace(',','',(string)$value)*100);}
function callQc($model,$method,$args=[]){$r=new ReflectionMethod($model,$method);$r->setAccessible(true);return$r->invokeArgs($model,$args);}
function fixtureQc($id,$group,$term,$plan,$invoice,$release,$extra=[]){return $extra+[
    'id_po'=>$id,'po_number'=>$id===1?'7400124474':'QC-PO-'.$id,'bowheer'=>'QC BOWHEER',
    'current_release_value'=>$release,'total_value'=>11895223,'status_po'=>'ON PO','type_project'=>'NRO',
    'po_date'=>'2026-11-01','ny_po_ref'=>'','term_index'=>$term,'percent'=>50,
    'no_po_sub'=>'SUB-'.$group,'regional'=>'R','kota_po'=>'C','detail_po'=>'DETAIL-'.$group,'remarks'=>'',
    'source_row_no'=>$group,'plan_amount'=>$plan,'invoice_amount'=>$invoice,'invoice_date'=>'2026-11-17',
    'target_status'=>'TARGET_WEEK','target_week'=>47,'submit_raw'=>'W47'];}
class QcResult{private$rows;public function __construct($rows){$this->rows=$rows;}public function result_array(){return$this->rows;}}
class QcDb{
    public$allocated=[];public$standalone=[];public$pipeline=[];public$queries=[];
    public function table_exists($table){return$table==='tb_po_target_pipeline';}
    public function query($sql,$params=[]){
        qc(preg_match('/^\s*SELECT\b/i',$sql)===1,'Only readonly sourceSELECT');$this->queries[]=$sql;
        if(strpos($sql,'FROM tb_po_term t')!==false)return new QcResult($this->standalone);
        if(strpos($sql,'FROM tb_po_term_allocation a')!==false)return new QcResult($this->allocated);
        if(strpos($sql,'FROM tb_po_target_pipeline pl')!==false)return new QcResult($this->pipeline);
        throw new RuntimeException('Unexpected query in offline QC');
    }
}
$model=new MPO_Monitor();$model->db=$db=new QcDb();
$db->allocated=[
    fixtureQc(1,1,1,10036000,10036000,11895223),
    fixtureQc(2,10,1,30,7,100.01),fixtureQc(2,10,2,20,2,100.01),
    fixtureQc(2,11,1,25,-1,100.01),
    fixtureQc(3,12,1,0,1,10.01),fixtureQc(3,13,1,0,2,10.01),
    fixtureQc(4,14,1,10,1,0),fixtureQc(5,15,1,20,3,-10),
];
$db->standalone=[fixtureQc(2,null,3,25,4,100.01),fixtureQc(3,null,2,0,3,10.01)];
$db->pipeline=[[
    'id_pipeline'=>11,'id_bowheer'=>1,'dashboard_bowheer'=>'QC NY','bowheer'=>'QC NY','regional'=>'R','kota_po'=>'C',
    'detail_po'=>'NY DETAIL','remarks'=>'','type_project'=>'QC','po_date'=>'2026-11-01','po_term'=>'100',
    'term_index'=>1,'plan_amount'=>123.45,'ny_po_2026_amount'=>123.45,'ny_po_2027_amount'=>0,
    'target_status'=>'TARGET_WEEK','target_week'=>47,'submit_raw'=>'W47','invoice_amount'=>0,'source_row_no'=>1
]];
$baseline=array_merge(callQc($model,'getAllocatedOnPoImportReportRows'),callQc($model,'getStandaloneOnPoImportReportRows'));
$baselineNy=callQc($model,'getNyPoImportReportRows');
$baselineByGroup=[];foreach($baseline as$r){$baselineByGroup[$r['NO PO'].'|'.$r['NO PO SUB']]=$r;}
$out=$model->getImportReportRows();$headers=$model->getImportReportHeaders();
$byPo=[];$ny=[];
foreach($out as$r){
    qc(array_keys($r)===$headers,'Public report header shape unchanged, no internal keys');
    foreach(array_keys($r)as$key)qc(strpos($key,'_PO_')!==0,'No internal metadata leaked');
    if($r['STATUS PO']==='NY PO'){$ny[]=$r;continue;}
    $key=$r['NO PO'].'|'.$r['NO PO SUB'];$before=$baselineByGroup[$key];
    unset($before['_PO_ID'],$before['_PO_RELEASE_VALUE']);$after=$r;
    unset($before['PO VALUE'],$before['OUTSTANDING TOTAL'],$after['PO VALUE'],$after['OUTSTANDING TOTAL']);
    qc($before===$after,'All original fields except value/outstanding untouched:'.$key.' '.json_encode(array_diff_assoc($before,$after)));
    $byPo[$r['NO PO']][]=$r;
}
qc($ny===$baselineNy,'NY PO rows byte-for-byte unchanged');
qc(count($out)===10,'All allocated,standalone,NY output rows retained');
$expected=['7400124474'=>1189522300,'QC-PO-2'=>10001,'QC-PO-3'=>1001,'QC-PO-4'=>0,'QC-PO-5'=>-1000];
foreach($expected as$po=>$value){qc(array_sum(array_map(fn($r)=>centsQc($r['PO VALUE']),$byPo[$po]))===$value,'PerPO latestrelease sum exact:'.$po);}
$example=$byPo['7400124474'][0];
qc(centsQc($example['PO VALUE'])===1189522300,'Reported example value11895223');
qc(centsQc($example['INVOICE ALL'])===1003600000,'Example claim10036000 preserved');
qc(centsQc($example['INVOICE 2026'])===1003600000,'Example2026invoice preserved');
qc(centsQc($example['OUTSTANDING TOTAL'])===185922300,'Example outstanding1859223');
$shares=array_map(fn($r)=>centsQc($r['PO VALUE']),$byPo['QC-PO-2']);sort($shares);
qc($shares===[2500,2500,5001],'Global mixed-row weights25/25/50 with exactcent remainder');
$equal=array_map(fn($r)=>centsQc($r['PO VALUE']),$byPo['QC-PO-3']);sort($equal);
qc($equal===[333,334,334],'Zero-plan equalshares preserve10.01');
foreach($db->queries as$sql){if(strpos($sql,'FROM tb_po_term t')!==false||strpos($sql,'FROM tb_po_term_allocation a')!==false){
    qc(strpos($sql,'ORDER BY am.amend_no DESC LIMIT 1')!==false,'Latest amendment by number');
    qc(strpos($sql,'p.total_value) AS current_release_value')!==false,'Missing amendment falls back header');
}}

// Deterministic rounding stress over independent input-row weights.
for($count=1;$count<=23;$count++){
    foreach([0,1,1001,-1001,1189522300]as$release){
        $input=[];for($i=0;$i<$count;$i++)$input[]=['_PO_ID'=>99,'_PO_RELEASE_VALUE'=>$release/100,'PO VALUE'=>(string)(($i*17+3)%11/100),'INVOICE ALL'=>'0.01','PLAN 1'=>'unchanged','NILAI 1'=>'signed-source'];
        $copy=$input;$result=callQc($model,'applyImportReportReleaseValues',[$input]);
        qc($input===$copy,'Input arrays not mutated');
        qc(array_sum(array_map(fn($r)=>centsQc($r['PO VALUE']),$result))===$release,'Cent-conserving proration');
        foreach($result as$r){qc($r['PLAN 1']==='unchanged'&&$r['NILAI 1']==='signed-source'&&$r['INVOICE ALL']==='0.01','Sourcefields untouched stress');qc(!isset($r['_PO_ID'],$r['_PO_RELEASE_VALUE']),'Metadata removed stress');}
    }
}
// Explicit header fallback/null/zero/negative metadata all remain distinct.
foreach([[null,42,42],[0,42,0],[-2,42,-2]]as$case){
    $item=fixtureQc(8,5,1,1,0,$case[0],['total_value'=>$case[1]]);
    $r=callQc($model,'baseOnPoImportReportRow',[$item]);
    qc($r['_PO_RELEASE_VALUE']===$case[2]*1.0,'Release fallback distinguishes null/zero/negative');
}
echo "PASS $checks independent release-report QC assertions\n";
