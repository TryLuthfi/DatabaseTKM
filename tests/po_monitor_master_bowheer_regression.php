<?php
/** Isolated master-list coverage: no database connection or writes. */
define('BASEPATH', dirname(__DIR__) . '/system/');
class CI_Model {}
require dirname(__DIR__) . '/application/models/MPO_Monitor.php';
class MasterBowheerFixture extends MPO_Monitor {
    public $masters = [];
    public function getBowheerOptionsForHeaderEdit() { return $this->masters; }
}
$model = new MasterBowheerFixture();
$method = new ReflectionMethod(MPO_Monitor::class, 'appendMissingMasterBowheerRows');
$method->setAccessible(true);
$checks = 0;
function checkMaster($ok, $label) {
    global $checks;
    if (!$ok) { throw new RuntimeException('FAIL ' . $label); }
    $checks++; echo 'PASS ' . $label . PHP_EOL;
}
$rows = [['bowheer'=>'PT ADT','pic'=>'LOGISTIK','all_po'=>123.45,'done_inv_2026'=>12,'custom'=>'keep'],
    ['bowheer'=>'Transaction-only','pic'=>'Keep','all_po'=>-10]];
$model->masters = [['id_bowheer'=>23,'bowheer'=>'PT ADT','pic'=>'Changed master'],
    ['id_bowheer'=>28,'bowheer'=>'PT ASIANET','pic'=>'-'],
    ['id_bowheer'=>29,'bowheer'=>'PT GILANG SAKTI KANAKA','pic'=>null],
    ['id_bowheer'=>30,'bowheer'=>' PT ASIANET ','pic'=>'duplicate'],
    ['id_bowheer'=>31,'bowheer'=>'   ','pic'=>'invalid']];
$out = $method->invoke($model, $rows);
checkMaster(array_slice($out,0,2)===$rows, 'existing rows including PIC, custom and negative amount untouched');
checkMaster(count($out)===4, 'new masters appended once; blank names ignored');
checkMaster(array_column($out,'bowheer')===['PT ADT','Transaction-only','PT ASIANET','PT GILANG SAKTI KANAKA'], 'transaction-only entry retained');
foreach (array_slice($out,2) as $row) {
    foreach (['all_po','all_invoice','done_inv_2026','outs_2026_on_target','ny_po_on_target_2026','grandtotal_target','ny_po_total','co_to_2027','total_outs'] as $key) {
        checkMaster($row[$key]===0, 'master-only metric zero ' . $row['bowheer'] . ' ' . $key);
    }
    checkMaster($row['pic']==='-' && $row['has_data']===0 && $row['sort_order']===1000, 'safe missing-data display defaults ' . $row['bowheer']);
}
checkMaster($method->invoke($model,$out)===$out, 'repeated inclusion is idempotent');
$model->masters = [];
checkMaster($method->invoke($model,$rows)===$rows, 'empty master leaves existing rows untouched');
checkMaster($rows[0]['all_po']===123.45, 'input unchanged');
echo 'RESULT ' . $checks . ' PASS' . PHP_EOL;
