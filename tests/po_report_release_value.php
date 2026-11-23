<?php
/** Pure report projection regression: no database and no business submissions. */
define('BASEPATH', dirname(__DIR__) . '/system/');
class CI_Model {}
require dirname(__DIR__) . '/application/models/MPO_Monitor.php';
$model = new MPO_Monitor();
$methods = [];
foreach (['baseOnPoImportReportRow', 'applyImportReportTerm', 'finalizedImportReportRows', 'applyImportReportReleaseValues'] as $name) {
    $methods[$name] = new ReflectionMethod($model, $name); $methods[$name]->setAccessible(true);
}
$checks = 0;
function ok($test, $message) { global $checks; ++$checks; if (!$test) { throw new RuntimeException($message); } }
function cents($value) { return (int) round((float) str_replace(',', '', (string) $value) * 100); }
function reportRow($id, $release, $plan, $invoice = 0, $term = 1, $original = 90000000) {
    global $model, $methods;
    $item = ['id_po'=>$id,'total_value'=>$original,'current_release_value'=>$release,'po_number'=>'QA-'.$id,
        'regional'=>'Region','kota_po'=>'City','detail_po'=>'Detail','bowheer'=>'QA','status_po'=>'ON PO',
        'type_project'=>'QA','po_date'=>'2026-01-01','percent'=>100,'term_index'=>$term,'plan_amount'=>$plan,
        'invoice_amount'=>$invoice,'invoice_date'=>'2026-06-11','submit_raw'=>'2026-06-11','target_status'=>'DONE'];
    $row = $methods['baseOnPoImportReportRow']->invoke($model, $item);
    $args = [&$row, $item]; $methods['applyImportReportTerm']->invokeArgs($model, $args);
    return $methods['finalizedImportReportRows']->invoke($model, [$row])[0];
}
function reconcile($rows) { global $model, $methods; return $methods['applyImportReportReleaseValues']->invoke($model, $rows); }
function unchanged($before, $after) {
    foreach ($before as $i => $row) {
        unset($row['PO VALUE'], $row['OUTSTANDING TOTAL'], $row['_PO_ID'], $row['_PO_RELEASE_VALUE']);
        $copy=$after[$i]; unset($copy['PO VALUE'], $copy['OUTSTANDING TOTAL']);
        ok($row === $copy, 'Only PO VALUE and derived outstanding may change on row '.$i);
    }
}
$before=[reportRow(27910,11895223,10036000,10036000)];$after=reconcile($before);
ok(cents($after[0]['PO VALUE'])===1189522300,'PO7400124474 follows release');
ok(cents($after[0]['INVOICE ALL'])===1003600000,'PO7400124474 invoice remains recorded claim');
ok(cents($after[0]['OUTSTANDING TOTAL'])===185922300,'PO7400124474 derived outstanding');
unchanged($before,$after);

// Allocated rows and a standalone row share one id_po: latest release counted once.
$before=[reportRow(1,125,30,12),reportRow(1,125,50,35),reportRow(1,125,20,-2),reportRow(2,80,100,10)];
$after=reconcile($before);
ok(array_map(function($r){return cents($r['PO VALUE']);},$after)===[3750,6250,2500,8000],'Mixed allocated/standalone shares and separate PO');
unchanged($before,$after);

$before=[reportRow(3,0,100,10),reportRow(4,null,100,10,1,75),reportRow(5,50,100,65)];$after=reconcile($before);
ok(cents($after[0]['PO VALUE'])===0,'Zero release overrides positive header');
ok(cents($after[1]['PO VALUE'])===7500,'Missing release falls back to header');
ok(cents($after[2]['OUTSTANDING TOTAL'])===0,'Overclaim does not produce negative remaining');
unchanged($before,$after);

$rows=[];for($i=0;$i<4;$i++)$rows[]=reportRow(9,0.02,0,0);$result=reconcile($rows);
ok(array_sum(array_map(function($r){return cents($r['PO VALUE']);},$result))===2,'Zero-plan release remains cent-exact');
ok(min(array_map(function($r){return cents($r['PO VALUE']);},$result))>=0,'Rounding never invents negative release share');

// Conservation across varied shares, including zero-weight rows and cent residuals.
mt_srand(740012447);
for($trial=0;$trial<100;$trial++) {
    $release=mt_rand(0,99999999)/100; $rows=[]; $count=mt_rand(1,12);
    for($i=0;$i<$count;$i++)$rows[]=reportRow(50,$release,mt_rand(0,100000)/100,mt_rand(-5000,5000)/100);
    $out=reconcile($rows);
    ok(array_sum(array_map(function($r){return cents($r['PO VALUE']);},$out))===cents($release),'Release conserved '.$trial);
    ok(min(array_map(function($r){return cents($r['PO VALUE']);},$out))>=0,'Nonnegative shares '.$trial);
    unchanged($rows,$out);
}
$ny=['STATUS PO'=>'NY PO','PO VALUE'=>'123','INVOICE ALL'=>'','OUTSTANDING TOTAL'=>'123'];
ok(reconcile([$ny])[0]===$ny,'No-release pipeline row is untouched');
ok(reconcile([])===[],'Empty report');
echo "PASS $checks report release assertions\n";
