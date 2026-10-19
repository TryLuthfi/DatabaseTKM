<?php
/** No database writes or connection: exercise public detail resolver with isolated rows. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
class CI_Model { public $db; }
require APPPATH . 'models/MPO_MyRep.php';
class DetailTermResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function result_array() { return $this->rows; }
}
class DetailTermDb {
    public $headers, $terms;
    private $table, $filters = [];
    function __construct($headers, $terms) { $this->headers = $headers; $this->terms = $terms; }
    function select($fields) { return $this; }
    function from($table) { $this->table = $table; return $this; }
    function where($field, $value) { $this->filters[$field] = [$value]; return $this; }
    function where_in($field, $values) { $this->filters[$field] = $values; return $this; }
    function order_by($field, $direction) { return $this; }
    function get() {
        $rows = $this->table === 'tb_myrep_po_header' ? $this->headers : $this->terms;
        foreach ($this->filters as $field => $values) {
            $rows = array_values(array_filter($rows, function ($row) use ($field, $values) {
                return in_array($row[$field] ?? null, $values);
            }));
        }
        $this->filters = [];
        return new DetailTermResult($rows);
    }
}
class DetailTermModel extends MPO_MyRep {
    public $allowed = true, $ready = true;
    function __construct() {}
    function tablesReady() { return $this->ready; }
    function getClusterById($id) { return $this->allowed ? ['id_myrep_cluster' => $id] : []; }
}
$checks = 0;
function checkDetailTerm($ok, $label) {
    global $checks;
    if (!$ok) { throw new RuntimeException('FAIL ' . $label); }
    $checks++; echo 'PASS ' . $label . PHP_EOL;
}
$initial = ['id_po_header'=>36532, 'id_myrep_cluster'=>13241, 'po_type'=>'SUBFEEDER', 'po_number'=>'7400093997', 'po_category'=>'INITIAL'];
$final = array_replace($initial, ['id_po_header'=>38355, 'po_category'=>'FINAL']);
$headers = [$initial, $final]; $terms = [];
$values = [1=>4178150, 2=>5222688, 3=>3133613, 4=>6267225, 5=>2089074];
$dates = [1=>'2025-10-02', 2=>'2026-01-09', 3=>'2026-02-18'];
foreach ([36532,38355] as $id) {
    foreach ($values as $no=>$value) {
        $terms[] = ['id_po_termin'=>$id*10+$no, 'id_po_header'=>$id, 'termin_no'=>$no,
            'termin_value'=>$value, 'invoice_value'=>null, 'status_termin'=>$id===36532 && $no<=3?'BILLED':'NOT READY',
            'invoice_date'=>$id===36532?($dates[$no]??null):null, 'sertifikat_invoice_date'=>null];
    }
}
// A same-number PO in a different cluster/type, and another PO, must never leak.
foreach ([['id_myrep_cluster'=>999], ['po_type'=>'CLUSTER'], ['po_number'=>'OTHER']] as $i=>$change) {
    $id = 50000+$i;
    $headers[] = array_replace($final, $change, ['id_po_header'=>$id]);
    $terms[] = array_replace($terms[0], ['id_po_header'=>$id, 'id_po_termin'=>$id*10, 'invoice_date'=>'2026-10-19']);
}
$model = new DetailTermModel();
$model->db = new DetailTermDb($headers, $terms);
$resolved = $model->getResolvedTerminRowsByPoHeader($final);
checkDetailTerm(count($resolved)===5, 'five unique terms');
checkDetailTerm(array_column($resolved,'termin_no')===[1,2,3,4,5], 'ordered terms');
checkDetailTerm(array_column($resolved,'id_po_header')===[36532,36532,36532,38355,38355], 'billed INITIAL wins; blank terms prefer FINAL; unrelated POs excluded');
checkDetailTerm(array_column($resolved,'invoice_date')===['2025-10-02','2026-01-09','2026-02-18',null,null], 'three invoice dates preserved');
checkDetailTerm(array_column($resolved,'id_po_termin')===[365321,365322,365323,383554,383555], 'Update retains the displayed source IDs');
checkDetailTerm($model->getTerminRowsByPoId(38355)===array_slice($terms,5,5), 'raw FINAL read remains unchanged for write/sync callers');
checkDetailTerm($model->getResolvedTerminRowsByPoHeader($initial)===$resolved, 'same logical PO resolves identically from either category');
$normalized = array_replace($final,['po_type'=>' subfeeder ', 'po_number'=>' 7400093997 ']);
checkDetailTerm($model->getResolvedTerminRowsByPoHeader($normalized)===$resolved, 'normalized PO identity');
$model->allowed = false;
checkDetailTerm($model->getResolvedTerminRowsByPoHeader($final)===[], 'denied cluster returns no terms');
$model->allowed = true; $model->ready = false;
checkDetailTerm($model->getResolvedTerminRowsByPoHeader($final)===[], 'missing tables return no terms');
$model->ready = true;
checkDetailTerm($model->getResolvedTerminRowsByPoHeader([])===[], 'invalid header returns no terms');
$method = new ReflectionMethod(MPO_MyRep::class, 'resolvePoGroupTerminRows'); $method->setAccessible(true);
$meta = [36532=>$initial,38355=>$final];
$adversarial = [
    array_replace($terms[0], ['termin_no'=>0]),
    array_replace($terms[0], ['termin_no'=>6]),
    array_replace($terms[0], ['id_po_header'=>99999]),
    array_replace($terms[0], ['invoice_date'=>'0000-00-00']),
    array_replace($terms[5], ['invoice_value'=>0]),
];
$picked = $method->invoke($model, $adversarial, $meta);
checkDetailTerm(count($picked)===1 && $picked[1]['id_po_header']===38355 && $picked[1]['invoice_value']===0, 'invalid term/header/date excluded; explicit zero retained');
$paid = array_replace($terms[5], ['invoice_date'=>'2026-03-01', 'status_termin'=>'PAID', 'invoice_value'=>123]);
$picked = $method->invoke($model, [$terms[0],$paid], $meta);
checkDetailTerm($picked[1]===$paid, 'invoiced FINAL retains its full record and value');
$blankA = array_replace($initial,['po_number'=>'']); $blankB = array_replace($final,['po_number'=>'']);
$model->db = new DetailTermDb([$blankA,$blankB],$terms);
$blankTerms = $model->getResolvedTerminRowsByPoHeader($blankB);
checkDetailTerm(array_unique(array_column($blankTerms,'id_po_header'))===[38355], 'blank PO numbers stay isolated by header ID');
checkDetailTerm($terms[0]['id_po_header']===36532 && $terms[5]['invoice_date']===null, 'source fixture not rewritten');
echo 'RESULT ' . $checks . ' PASS' . PHP_EOL;
