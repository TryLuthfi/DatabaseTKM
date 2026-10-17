<?php
/** Local rollback-only report parity fixture with >100 metadata lookups per allocated/standalone phase. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php'; require BASEPATH . 'database/DB.php'; require BASEPATH . 'core/Model.php';
require APPPATH . 'models/MPO_Monitor.php';
$opts=getopt('', ['baseline:']);
if (empty($opts['baseline']) || !is_file($opts['baseline'])) { exit("Provide --baseline=<original-model.php>\n"); }
$source=preg_replace('/^<\?php\s*/','',file_get_contents($opts['baseline']));
eval(str_replace('class MPO_Monitor extends','class MPO_Monitor_ExportBaseline extends',$source));
include APPPATH . 'config/database.php';
$config=$db['default']; $config['database']='zeyn_po_invoice_test_20261017'; $config['pconnect']=false; $config['db_debug']=false;
if (!preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/',$config['hostname'])) { exit('Unsafe test host'); }
$conn=DB($config,true);
if ($conn->query('SELECT DATABASE() AS name')->row_array()['name']!=='zeyn_po_invoice_test_20261017') { exit('Wrong schema'); }
$instance=(object)['db'=>$conn];
function &get_instance() { global $instance; return $instance; }
function exportFixture($table,$row) {
    global $conn;
    foreach ($conn->query('SHOW COLUMNS FROM `'.$table.'`')->result_array() as $c) {
        if (array_key_exists($c['Field'],$row)||$c['Null']==='YES'||$c['Default']!==null||strpos($c['Extra'],'auto_increment')!==false) {continue;}
        if (preg_match('/^(int|bigint|tinyint|smallint|mediumint|decimal|float|double|bit)/',$c['Type'])) {$v=0;}
        elseif (preg_match('/^(datetime|timestamp)/',$c['Type'])) {$v='2026-10-17 12:00:00';}
        elseif (strpos($c['Type'],'date')===0) {$v='2026-10-17';}
        elseif (preg_match("/^enum\('([^']*)'/",$c['Type'],$m)) {$v=$m[1];} else {$v='';}
        $row[$c['Field']]=$v;
    }
    if (!$conn->insert($table,$row)) {throw new RuntimeException('Fixture '.$table.' '.json_encode($conn->error()));}
    return $conn->insert_id();
}
$checks=0;
function exportCheck($ok,$name) {global $checks; if(!$ok){throw new RuntimeException('FAIL '.$name);} $checks++; echo "PASS $name\n";}
$tables=['tb_po_comparison_target_lock','tb_po_term_claim','tb_po_term_allocation','tb_po_term','tb_po_target_pipeline','tb_po_amend','tb_po','tb_bowheer_po','tb_myrep_po_header','tb_myrep_cluster','tb_rfs_myrep_mainfeeder'];
foreach ($tables as $table) {
    $engine=$conn->query('SHOW TABLE STATUS LIKE ?',[$table])->row_array();
    if (($engine['Engine']??'')!=='InnoDB') {throw new RuntimeException('Rollback unsafe '.$table);}
}
$conn->trans_begin();
try {
    foreach($tables as $table){$conn->query('DELETE FROM `'.$table.'`');}
    $project=exportFixture('tb_bowheer_po',['bowheer'=>'QA Export','bowheer_key'=>'qa-export','pic'=>'QA']);
    $clusters=[];
    foreach(['Zeta','Alpha','Beta'] as $name){$clusters[]=exportFixture('tb_myrep_cluster',['cluster_name'=>$name,'regional_name'=>'Region '.$name,'city_name'=>'City '.$name]);}
    $feeder=exportFixture('tb_rfs_myrep_mainfeeder',['mainfeeder_name'=>'Feeder','regional_name'=>'Region Feeder','city_name'=>'City Feeder']);
    $terms=[];
    for($i=0;$i<213;$i++) {
        $number=$i===0?' QA-Café ':($i===1?'qa-CAFE':sprintf('QA-%03d',$i));
        $po=exportFixture('tb_po',['id_bowheer'=>$project,'po_number'=>$number,'po_date'=>'2026-01-01','total_value'=>1000]);
        $term=exportFixture('tb_po_term',['id_po'=>$po,'term_index'=>1,'value'=>1000,'plan_amount'=>950,'invoice_date'=>'2026-10-04']);
        $terms[]=$term;
        exportFixture('tb_po_term_claim',['id_term'=>$term,'invoice_date'=>'2026-10-04','invoice_amount'=>100,'claim_source'=>'IMPORT']);
        if ($i%2===0) {
            exportFixture('tb_po_term_allocation',['id_term'=>$term,'source_row_no'=>1,'allocation_value'=>1000,'regional'=>$i===2?'Keep regional':'','kota_po'=>$i===4?'Keep city':'','detail_po'=>$i===6?'Keep detail':'']);
        }
        if ($i===212){continue;}
        foreach(($i>=7 && $i<207 ? [0] : [2,0,1,2]) as $j=>$clusterIndex) {
            exportFixture('tb_myrep_po_header',['po_number'=>$i===0?'qa-cafe':$number,'po_category'=>$j===0?'FINAL':'AMANDMENT','po_type'=>$j===2?'':'CLUSTER','id_myrep_cluster'=>$clusters[$clusterIndex],'id_mainfeeder'=>$j===0?$feeder:null]);
        }
    }
    $before=new MPO_Monitor_ExportBaseline(); $after=new MPO_Monitor();
    $a=$before->getImportReportRows(); $b=$after->getImportReportRows();
    exportCheck(count($a)===213&&count($b)===213,'213 report rows crossing both phases metadata chunk boundary');
    if ($a!==$b) {
        foreach($a as $i=>$row){if($row!==$b[$i]){echo json_encode(['index'=>$i,'number'=>$row['NO PO'],'baseline'=>$row,'candidate'=>$b[$i]],JSON_UNESCAPED_UNICODE)."\n";break;}}
    }
    exportCheck($a===$b,'all report fields, types and concatenation order identical');
    exportCheck($before->getImportReportHeaders()===$after->getImportReportHeaders(),'headers identical');
    $byNumber=[]; foreach ($b as $row) {$byNumber[trim($row['NO PO'])]=$row;}
    exportCheck($byNumber['QA-002']['REGIONAL']==='Keep regional' && $byNumber['QA-004']['KOTA PO']==='Keep city' && $byNumber['QA-006']['DETAIL PO']==='Keep detail','existing nonempty allocation metadata preserved');
    exportCheck($byNumber['QA-212']['REGIONAL']==='' && $byNumber['QA-212']['KOTA PO']==='' && $byNumber['QA-212']['DETAIL PO']==='','missing MyRep header keeps empty fallback fields');
    exportCheck($byNumber['QA-Café']['KOTA PO']===$byNumber['qa-CAFE']['KOTA PO'],'accent/case/trim normalized SQL matching retained');
    $prop=new ReflectionProperty(MPO_Monitor::class,'importReportMetaRequest');$prop->setAccessible(true);
    exportCheck($prop->getValue($after)===null,'metadata scope discarded after report');
    $conn->where('id_myrep_cluster',$clusters[0])->update('tb_myrep_cluster',['city_name'=>'Updated city']);
    $fresh=$after->getImportReportRows();
    exportCheck($fresh!==$b&&strpos(json_encode($fresh),'Updated city')!==false,'next report reads updated metadata');
    exportCheck($prop->getValue($after)===null,'fresh report discards metadata scope');
    echo "RESULT $checks PASS\n";
} finally {$conn->trans_rollback();}
