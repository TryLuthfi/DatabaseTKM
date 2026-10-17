<?php
/** Rollback-only fixture; --baseline points to the pre-change model source. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php'; require BASEPATH . 'database/DB.php'; require BASEPATH . 'core/Model.php';
require APPPATH . 'models/MPO_Monitor.php';
$opts = getopt('', ['baseline:']);
if (empty($opts['baseline']) || !is_file($opts['baseline'])) { exit("Provide --baseline=<original-model.php>\n"); }
$source = file_get_contents($opts['baseline']);
eval(substr(str_replace('class MPO_Monitor extends', 'class MPO_Monitor_LazyDataBaseline extends', $source), 5));
include APPPATH . 'config/database.php';
$config = $db['default']; $config['database'] = 'zeyn_po_invoice_test_20261017'; $config['pconnect'] = false; $config['db_debug'] = false;
if (!preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $config['hostname'])) { exit('Unsafe test host'); }
$conn = DB($config, true);
if ($conn->query('SELECT DATABASE() AS name')->row_array()['name'] !== 'zeyn_po_invoice_test_20261017') { exit('Wrong schema'); }
class LazyDataFixtureDb {
    public $db, $writes = [], $missing = false;
    function __construct($db) { $this->db = $db; }
    function __call($method, $args) { $result = $this->db->$method(...$args); return $result === $this->db ? $this : $result; }
    function table_exists($table) { return $this->missing && $table === 'tb_po_comparison_target_lock' ? false : $this->db->table_exists($table); }
    function query($sql, $binds = false, $return = null) {
        if (preg_match('/^\s*INSERT IGNORE INTO tb_po_comparison_target_lock/i', $sql)) { $binds[6] = '[clock]'; $this->writes[] = $binds; return true; }
        return $this->db->query($sql, $binds, $return);
    }
}
$proxy = new LazyDataFixtureDb($conn); $instance = (object) ['db' => $proxy];
function &get_instance() { global $instance; return $instance; }
function fixture($table, $row) {
    global $conn;
    foreach ($conn->query('SHOW COLUMNS FROM `' . $table . '`')->result_array() as $c) {
        if (array_key_exists($c['Field'], $row) || $c['Null'] === 'YES' || $c['Default'] !== null || strpos($c['Extra'], 'auto_increment') !== false) { continue; }
        if (preg_match('/^(int|bigint|tinyint|smallint|mediumint|decimal|float|double|bit)/', $c['Type'])) { $value = 0; }
        elseif (preg_match('/^(datetime|timestamp)/', $c['Type'])) { $value = '2026-10-17 12:00:00'; }
        elseif (strpos($c['Type'], 'date') === 0) { $value = '2026-10-17'; }
        elseif (preg_match("/^enum\('([^']*)'/", $c['Type'], $match)) { $value = $match[1]; }
        else { $value = ''; }
        $row[$c['Field']] = $value;
    }
    if (!$conn->insert($table, $row)) { throw new RuntimeException('Fixture failed: ' . $table . ' ' . json_encode($conn->error())); }
    return $conn->insert_id();
}
$checks = 0;
function verify($ok, $name) { global $checks; if (!$ok) { throw new RuntimeException('FAIL ' . $name); } $checks++; echo "PASS $name\n"; }

class CI_Controller { public function __get($key) { $instance =& get_instance(); return $instance->$key; } }
require APPPATH . 'controllers/PO_Monitor.php';
$controller = (new ReflectionClass('PO_Monitor'))->newInstanceWithoutConstructor();
function renderOrGroup($method, ...$args) {
    global $controller;
    $m = new ReflectionMethod($controller, $method); $m->setAccessible(true);
    return $m->invoke($controller, ...$args);
}
$conn->trans_begin();
try {
    foreach (['tb_po_comparison_target_lock','tb_po_term_claim','tb_po_term_allocation','tb_po_term','tb_po_target_pipeline','tb_po','tb_bowheer_po','tb_myrep_po_header','tb_myrep_cluster','tb_rfs_myrep_mainfeeder'] as $table) {
        $conn->query('DELETE FROM `' . $table . '`');
    }
    $project = fixture('tb_bowheer_po', ['bowheer'=>'QA NRO', 'bowheer_key'=>'qa-nro', 'pic'=>'QA', 'no_urut'=>1]);
    $cluster = fixture('tb_myrep_cluster', ['cluster_name'=>'QA Cluster', 'regional_name'=>'Regional 3', 'city_name'=>'QA City']);
    $feeder = fixture('tb_rfs_myrep_mainfeeder', ['mainfeeder_name'=>'QA Feeder', 'regional_name'=>'Regional 2', 'city_name'=>'QA Area']);
    $numbers = [' QA-Café ', 'QA-AMANDMENT', 'QA-OTHER', 'QA-MISSING', '', '   '];
    foreach ($numbers as $i=>$number) {
        $po = fixture('tb_po', ['id_bowheer'=>$project, 'po_number'=>$number, 'po_date'=>'2026-01-01', 'type_project'=>'REGIONAL 1 - AREA QA', 'dashboard_bowheer'=>$i%2 ? ' QA NRO ' : '']);
        if ($i < 3) {
            $normalized = $i===0 ? 'qa-CAFE' : $number;
            $cats = $i===0 ? ['FINAL','FINAL','AMANDMENT',''] : ($i===1 ? ['AMANDMENT','AMANDMENT',''] : ['','']);
            foreach ($cats as $cat) {
                fixture('tb_myrep_po_header', ['po_number'=>$normalized, 'po_category'=>$cat, 'po_type'=>$cat==='FINAL'?'CLUSTER':'MAINFEEDER', 'id_myrep_cluster'=>$cat==='FINAL'?$cluster:null, 'id_mainfeeder'=>$cat==='FINAL'?null:$feeder]);
            }
        }
        foreach ([['2025-12-28','2026-01-03'],['2026-09-27','2026-10-03'],['2026-10-04','2026-10-10'],['2026-10-11','2026-10-17'],['2026-12-27','2027-01-02']] as $j=>$dates) {
            $term = fixture('tb_po_term', ['id_po'=>$po,'term_index'=>$j+1,'value'=>500,'target_status'=>'TARGET_WEEK','target_week_start'=>$dates[0],'target_week_end'=>$dates[1],'plan_amount'=>$j===0?-25:100]);
            $allocation = null;
            if ($j%2) {
                foreach ([1,2] as $n) {
                    $allocation = fixture('tb_po_term_allocation', ['id_term'=>$term,'source_row_no'=>$n,'target_status'=>'TARGET_WEEK','target_week_start'=>$dates[0],'target_week_end'=>$dates[1],'plan_amount'=>$n===1?0:40,'allocation_value'=>60,'no_po_sub'=>$n===1?'':'SUB-2','regional'=>$i%2?'':'  ','kota_po'=>$n===1?'':'City','detail_po'=>$n===1?'':'Detail','remarks'=>$n===1?'':'Remarks']);
                }
            }
            foreach ([['2026-01-01',-10],['2026-09-30',0],['2026-10-04',100],['2026-10-13',25],['2026-12-31',80],['2027-01-02',10]] as $k=>$claim) {
                fixture('tb_po_term_claim', ['id_term'=>$term,'id_allocation'=>$k%2?$allocation:null,'invoice_date'=>$claim[0],'invoice_amount'=>$claim[1],'claim_source'=>$k%2?'IMPORT':'MANUAL']);
            }
        }
    }
    // Empty normalized PO headers match both exact blank and space-only POs.
    fixture('tb_myrep_po_header',['po_number'=>'   ','po_category'=>'FINAL','po_type'=>'CLUSTER','id_myrep_cluster'=>$cluster]);
    fixture('tb_po_target_pipeline',['id_bowheer'=>$project,'target_status'=>'TARGET_WEEK','target_week_start'=>'2026-10-11','target_week_end'=>'2026-10-17','plan_amount'=>-12,'detail_po'=>'Pipeline','regional'=>'Regional 3']);
    $before=new MPO_Monitor_LazyDataBaseline(); $after=new MPO_Monitor();
    foreach (['target','actual_target','deviasi_by_po','cumulative','effective_target','effective_deviasi_by_po'] as $type) {
        foreach ([['month','2026-01'],['month','2026-10'],['week','2026-W42'],['week','2027-W01'],['month','__total__']] as $case) {
            $proxy->writes=[];
            $a=$before->getComparisonDetail($project,$case[1],$case[0],$type,'2025-12','2027-01');
            $expectedWrites=$proxy->writes; $proxy->writes=[];
            $b=$after->getComparisonDetail($project,$case[1],$case[0],$type,'2025-12','2027-01');
            verify($a===$b,'target/cumulative exact rows '.$type.' '.implode(':',$case));
            verify($expectedWrites===$proxy->writes,'target/cumulative lock writes '.$type.' '.implode(':',$case));
        }
    }
    $cacheProperty=new ReflectionProperty(MPO_Monitor::class,'comparisonWeekRequest'); $cacheProperty->setAccessible(true);
    verify($cacheProperty->getValue($after)===null,'detail cache discarded after operation');
    $termToRefresh=$conn->query('SELECT id_term FROM tb_po_term ORDER BY id_term LIMIT 1')->row_array()['id_term'];
    $conn->where('id_term',$termToRefresh)->update('tb_po_term',['plan_amount'=>123456]);
    verify($before->getComparisonDetail($project,'__total__','month','target','2025-12','2027-01')===$after->getComparisonDetail($project,'__total__','month','target','2025-12','2027-01'),'next detail reads updated source');
    foreach ([['month','2026-01'],['month','2026-10'],['month','2026-12'],['month','__total__'],['week','2026-W40'],['week','2026-W41'],['week','2026-W42'],['week','2027-W01'],['week','__total__']] as $case) {
        $a=$before->getComparisonDetail($project,$case[1],$case[0],'achieved','2025-12','2027-01');
        $b=$after->getComparisonDetail($project,$case[1],$case[0],'achieved','2025-12','2027-01');
        verify($a===$b,'achieved exact rows/types/order '.implode(':',$case));
        verify(renderOrGroup('renderComparisonDetailHtml',$a,'achieved')===renderOrGroup('renderComparisonDetailHtml',$b,'achieved'),'achieved full HTML '.implode(':',$case));
    }
    $filters=['project'=>[],'pic'=>[],'regional'=>[],'area'=>[],'month'=>[],'week'=>[],'invoicedOnly'=>false];
    foreach ([[null,null],['2026-10','2026-10'],['2025-12','2027-01']] as $bounds) {
        $a=$before->getBreakdownTargetInvoiceRows(...$bounds); $b=$after->getBreakdownTargetInvoiceRows(...$bounds);
        verify($a===$b,'breakdown full exact rows '.json_encode($bounds));
        verify(renderOrGroup('renderBreakdownDetailHtml',$a)===renderOrGroup('renderBreakdownDetailHtml',$b),'breakdown full HTML '.json_encode($bounds));
        foreach (['project','pic','regional','area','period','date'] as $mode) {
            verify(renderOrGroup('groupBreakdownRows',$a,$mode)===renderOrGroup('groupBreakdownRows',$b,$mode),'all groups '.$mode.' '.json_encode($bounds));
        }
        foreach (['','project','pic','regional','area','month','week','invoicedOnly'] as $except) {
            $f=$filters; $f['regional']=['Regional 3']; $f['month']=['2026-10']; $f['invoicedOnly']=true;
            verify(renderOrGroup('buildBreakdownFilterOptions',$a,$f,$except)===renderOrGroup('buildBreakdownFilterOptions',$b,$f,$except),'dependent options except='.$except.' '.json_encode($bounds));
        }
    }
    echo "RESULT $checks PASS\n";
} finally { $conn->trans_rollback(); }
