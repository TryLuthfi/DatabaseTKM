<?php
// Independent CIKO query, date-boundary and render contract checks; no real DB.
define('BASEPATH', __DIR__ . '/../system/');
class CI_Model { public $db; }
require __DIR__ . '/../application/models/MAkselerasi_2026.php';
$n = 0;
function qc($condition, $message) { global $n; ++$n; if (!$condition) { throw new RuntimeException($message); } }
$expectedOwners = [
    'morep_nro'=>'WARDANI', 'morep_donasi'=>'WARDANI', 'morep_sop'=>'SLAMET',
    'iforte_fttt'=>'DONNY', 'iforte_ftth'=>'DONNY', 'iforte_reguler'=>'DONNY',
    'iforte_lbs'=>'HENDRY', 'komitel_rektif'=>'HENDRY', 'ta_star'=>'HENDRY',
    'ta_osp'=>'ZAENUL', 'pst_osp'=>'ZAENUL', 'xlsmart_osp'=>'ZAENUL',
    'tbg_permit'=>'ZAENUL', 'mti_osp'=>'ZAENUL', 'vgreen_evcs'=>'WENDY', 'vgreen_bss'=>'WENDY'
];
qc(count(MAkselerasi_2026::definitions())===count($expectedOwners),'Exactly16 source owners');
foreach(MAkselerasi_2026::definitions()as$def){qc(isset($expectedOwners[$def[0]])&&$expectedOwners[$def[0]]===$def[5],'Owner mapping '.$def[0]);}
class QcResult { private $rows; public function __construct($rows) { $this->rows=$rows; } public function result_array() { return $this->rows; } }
class QcDb {
    public $queries=[]; public $selected=''; public $failure=false; public $masterFailure=false;
    public function query($sql,$bindings) {
        $this->queries[]=[$sql,$bindings]; if($this->failure) return false;
        return new QcResult([['bowheer'=>'PT EMR - NRO','type'=>'NRO','achievement'=>'100.25','claim_count'=>1]]);
    }
    public function select($what) { $this->selected=$what; return $this; }
    public function get($table) { qc($table==='tb_bowheer_po','Only master read allowed'); return $this->masterFailure?false:new QcResult([]); }
}
$model=new MAkselerasi_2026(); $db=new QcDb(); $model->db=$db;
$out=$model->loadDashboard('2026-11-17');
qc(count($db->queries)===1,'One bounded query, no schema ensure/write');
list($sql,$bindings)=$db->queries[0];
qc($bindings===['2026-11-16','2026-11-18'],'Inclusive selected cutoff via exclusive next day');
qc(strpos($sql,'tc.invoice_date >= ? AND tc.invoice_date < ?')!==false,'Claim-date bounds');
qc(strpos($sql,'tb_po_term_allocation')===false,'No multiplying allocation join');
qc(strpos($sql,'invoice_amount > 0')===false,'Signed negative corrections retained');
qc(stripos($sql,'MAX(')===false,'No legacy maximum-date projection');
qc(stripos($sql,'SUM(tc.invoice_amount)')!==false,'Claim ledger net SUM');
qc($out['rows'][0]['achievement']===100.25,'Decimal ledger amount preserved');
$db->queries=[]; $model->loadDashboard('2026-12-31');
qc($db->queries[0][1]===['2026-11-16','2027-01-01'],'Year-crossing cutoff rollover');
foreach (['2026-11-15','2026-11-17\' OR 1=1','2026-02-31',['2026-11-17']] as $bad) {
    $db->queries=[]; $thrown=false; try {$model->loadDashboard($bad);}catch(InvalidArgumentException $e){$thrown=true;}
    qc($thrown&&$db->queries===[],'Invalid date rejected before SQL');
}
$db->failure=true; $thrown=false; try{$model->loadDashboard('2026-11-17');}catch(RuntimeException $e){$thrown=true;} qc($thrown,'Source read failure propagated');
$db->failure=false; $db->masterFailure=true; $thrown=false; try{$model->loadDashboard('2026-11-17');}catch(RuntimeException $e){$thrown=true;} qc($thrown,'Master read failure propagated');

function base_url($path='') { return 'http://localhost:8917/'.$path; }
function site_url($path='') { return base_url($path); }
$dashboard=$out; $dashboard['rows'][0]['pic']='</script><script>alert(1)</script>';
$dashboard['to']='"><img src=x onerror=alert(1)>';
$error='<img src=x onerror=alert(1)>';
ob_start(); include __DIR__.'/../application/views/Akselerasi_2026/index.php'; $html=ob_get_clean();
qc(strpos($html,'value="&quot;&gt;&lt;img')!==false,'Date value HTML escaped');
qc(strpos($html,'role="alert">&lt;img')!==false,'Backend errors HTML escaped');
preg_match('/<script id="aks-dashboard-data" type="application\/json">(.*?)<\/script>/s',$html,$match);
$payload=json_decode($match[1],true);
qc($payload['dashboard']['rows'][0]['pic']===$dashboard['rows'][0]['pic'],'JSON retains exact source value');
qc(strpos($match[1],'<script>')===false,'Script termination escaped');
qc(strpos($html,'<th scope="col">PIC</th><th scope="col">Bowheer</th><th scope="col">Type</th>')!==false,'PIC before Bowheer and Type');
preg_match('/<table class="aks-table aks-summary-table">(.*?)<\/table>/s',$html,$summary);
qc(strpos($summary[1],'Total Target')!==false&&strpos($summary[1],'Target Reguler')===false&&strpos($summary[1],'Akselerasi CO')===false,'PIC summary has total target only');
qc(stripos($html,'mengikuti filter')===false,'Removed following-filter label');
echo "PASS $n independent query/render assertions\n";
