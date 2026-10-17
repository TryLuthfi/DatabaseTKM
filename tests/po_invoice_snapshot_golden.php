<?php
/** Offline regression against a private frozen reconciliation snapshot, never committed here.
 * php tests/po_invoice_snapshot_golden.php /absolute/path/snapshot.json
 */
define('BASEPATH', dirname(__DIR__) . '/system/');
require BASEPATH . 'core/Model.php';
require dirname(__DIR__) . '/application/models/MPO_Monitor.php';
$path = $argv[1] ?? '';
if (!is_file($path)) { fwrite(STDERR, "Supply frozen snapshot.json path\n"); exit(2); }
$snapshot = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$model = new MPO_Monitor();
$apply = new ReflectionMethod($model, 'applyImportReportTerm'); $apply->setAccessible(true);
$groups = [];
foreach ($snapshot['exporter'] as $item) {
    $key = $item['id_po'];
    if (!isset($groups[$key])) { $groups[$key] = array_fill_keys($model->getImportReportHeaders(), ''); }
    $item += ['plan_amount' => 0, 'percent' => 0, 'target_status' => '', 'submit_raw' => '', 'target_week' => 0];
    $row = &$groups[$key]; $args = [&$row, $item]; $apply->invokeArgs($model, $args); unset($row);
}
$checks = 0; $affectedCents = 0; $snapshotCents = 0;
foreach ($snapshot['perpo'] as $po) {
    $expected = (int) round((float) $po['report'] * 100); $snapshotCents += $expected;
    if (!isset($groups[$po['id_po']])) { continue; }
    $actual = (int) round((float) ($groups[$po['id_po']]['INVOICE 2026'] ?: 0) * 100);
    if ($actual !== $expected) { throw new RuntimeException('Frozen PO invoice mismatch: ' . $po['id_po']); }
    $affectedCents += $actual; $checks++;
}
if ($checks !== 371 || count($snapshot['exporter']) !== 1855 || $affectedCents !== 1165121421000 || $snapshotCents !== 8707721515925) {
    throw new RuntimeException('Unexpected frozen snapshot or totals');
}
echo "PASS: $checks affected POs / 1855 source terms preserve report output; full snapshot invoice 2026 = 87077215159.25\n";
