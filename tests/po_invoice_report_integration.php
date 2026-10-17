<?php
/** Synthetic integration suite. Never connects to the business schema. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php'; require BASEPATH . 'database/DB.php'; require BASEPATH . 'core/Model.php';
require APPPATH . 'models/MPO_Monitor.php';
include APPPATH . 'config/database.php';
$config = $db['default']; $config['database'] = 'zeyn_po_invoice_test_20261017';
$config['pconnect'] = false; $config['db_debug'] = false;
if ($config['database'] !== 'zeyn_po_invoice_test_20261017' || !preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $config['hostname'])) { exit('Unsafe test target'); }
$instance = (object) ['db' => DB($config, true)];
function &get_instance() { global $instance; return $instance; }
$conn = $instance->db;
if ($conn->query('SELECT DATABASE() AS name')->row_array()['name'] !== $config['database']) { exit('Wrong schema'); }
$checks = 0;
function check($condition, $message) { global $checks; if (!$condition) { throw new RuntimeException('FAIL: ' . $message); } $checks++; echo "PASS: $message\n"; }
function cents($value) { return (int) round((float) str_replace(',', '', (string) $value) * 100); }
function insertRow($table, $row) {
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
    if (!$conn->insert($table, $row)) { throw new RuntimeException('Insert failed: ' . $table . ' ' . json_encode($conn->error())); }
    return $conn->insert_id();
}
function po($name = 'PT EMR - NRO', $status = 'ON PO', $type = '') {
    static $n = 0; $n++;
    return insertRow('tb_po', ['po_number' => 'QA-INVOICE-' . $n, 'po_date' => '2026-01-01', 'dashboard_bowheer' => $name, 'status_po' => $status, 'type_project' => $type, 'total_value' => 1000, 'dashboard_invoice_2026' => 999999, 'dashboard_all_invoice' => 888, 'dashboard_outs_2026' => 123, 'dashboard_co_2027' => 45]);
}
function term($po, $index = 1, $date = '2026-03-01') { return insertRow('tb_po_term', ['id_po' => $po, 'term_index' => $index, 'invoice_date' => $date, 'value' => 100, 'plan_amount' => 100, 'target_status' => 'TARGET_WEEK']); }
function allocation($term, $row = 1, $date = null) { return insertRow('tb_po_term_allocation', ['id_term' => $term, 'source_row_no' => $row, 'allocation_value' => 100, 'invoice_date' => $date, 'regional' => 'QA', 'kota_po' => 'QA', 'detail_po' => 'QA']); }
function claim($term, $allocation, $amount, $source = 'IMPORT', $date = '2026-03-01') { return insertRow('tb_po_term_claim', ['id_term' => $term, 'id_allocation' => $allocation, 'invoice_amount' => $amount, 'invoice_date' => $date, 'claim_source' => $source]); }
function rowByName($rows, $name) { foreach ($rows as $r) { if ($r['bowheer'] === $name) { return $r; } } return null; }
$tables = ['tb_po_term_claim', 'tb_po_term_allocation', 'tb_po_term', 'tb_po_amend', 'tb_po_target_pipeline', 'tb_po', 'tb_po_dashboard_cache', 'tb_bowheer_po'];
// Optional production-like legacy collation regression. DDL is confined to this
// empty disposable schema, applied before fixture transaction and restored after it.
$restoreColumns = [];
if (in_array('--mixed-collation', $argv, true)) {
    foreach ($tables as $table) {
        if ((int) $conn->query('SELECT COUNT(*) AS n FROM `' . $table . '`')->row_array()['n'] !== 0) {
            exit("Mixed-collation mode requires an empty test schema\n");
        }
    }
    foreach (['tb_po' => ['dashboard_bowheer', 'type_project'], 'tb_bowheer_po' => ['bowheer']] as $table => $columns) {
        foreach ($columns as $column) {
            $original = $conn->query("SHOW FULL COLUMNS FROM `$table` WHERE Field = " . $conn->escape($column))->row_array();
            $definition = $original['Type'] . ' CHARACTER SET utf8mb4 COLLATE ' . $original['Collation'];
            $definition .= $original['Null'] === 'YES' ? ' NULL' : ' NOT NULL';
            if ($original['Default'] !== null) { $definition .= ' DEFAULT ' . $conn->escape($original['Default']); }
            elseif ($original['Null'] === 'YES') { $definition .= ' DEFAULT NULL'; }
            $restoreColumns[] = "ALTER TABLE `$table` MODIFY `$column` $definition";
            $collation = $table === 'tb_po' ? 'utf8mb4_general_ci' : 'utf8mb4_unicode_ci';
            $conn->query("ALTER TABLE `$table` MODIFY `$column` " . $original['Type'] . " CHARACTER SET utf8mb4 COLLATE $collation " . ($original['Null'] === 'YES' ? 'NULL' : 'NOT NULL'));
        }
    }
    $conn->query("SET collation_connection = 'utf8mb4_general_ci'");
    check(true, 'heterogeneous legacy PO / bowheer / connection collations configured');
}
// Dedicated schema only. Transaction rollback restores its previous fixtures, even on failure.
$conn->trans_begin();
try {
    foreach ($tables as $table) { $conn->query('DELETE FROM `' . $table . '`'); }
    $p = po();
    $t = term($p); $a = allocation($t); claim($t, $a, '100.25'); claim($t, null, '101.25', 'MYREP_SYNC');
    $t = term($p, 2); $a = allocation($t, 2); claim($t, $a, 0); claim($t, null, 500, 'MYREP_SYNC'); // zero is NOT NULL fallback
    $t = term($p, 3); allocation($t, 3); claim($t, null, '25.50', 'MANUAL'); // unallocated fallback
    $t = term($p, 4); $a = allocation($t, 4); claim($t, $a, -40); // negative excluded
    $t = term($p, 5); $a = allocation($t, 5); claim($t, $a, 10, 'IMPORT', '2025-03-01'); claim($t, $a, 20); // SUM all years, MAX date 2026
    $t = term($p, 6); $a = allocation($t, 6); claim($t, $a, 999); // outside report terms
    $p2 = po(); $t = term($p2); claim($t, null, '50.25', 'MYREP_SYNC');
    $t = term($p2, 2, '2025-01-01'); claim($t, null, 1000); // standalone uses term date, not claim date
    $t = term($p2, 3); claim($t, null, -200);
    $t = term($p2, 4); claim($t, null, 5, 'MANUAL');
    $t = term($p2, 5); allocation($t, 7); allocation($t, 8); claim($t, null, 7); // fallback repeated exactly like report
    $p3 = po('QA CANCELLED ONLY', 'CANCELLED'); $t = term($p3); claim($t, null, '42.75');
    foreach (['EVCS' => ['PT VGREEN ( EVCS )', 11], 'DC 30' => ['PT VGREEN ( EVCS )', 12], 'BSS' => ['PT VGREEN ( BSS )', 13], '12S3P' => ['PT VGREEN ( BSS )', 14], 'OTHER' => ['PT VGREEN', 15]] as $type => $v) { $id = po('PT VGREEN', 'ON PO', $type); $t = term($id); claim($t, null, $v[1]); }
    $casePo = po('pt emr - nro ', 'ON PO', 'CASE VARIANT'); $t = term($casePo); claim($t, null, '60.75');
    $casePo = po('pt vgreen', 'ON PO', 'EVCS'); $t = term($casePo); claim($t, null, 6);
    $casePo = po('pt vgreen', 'ON PO', 'BSS'); $t = term($casePo); claim($t, null, 4);
    $accentPo = po("QA CAF\u{00C9}", 'ON PO', 'ACCENT'); $t = term($accentPo); claim($t, null, 8);
    $accentPo = po('qa cafe', 'ON PO', 'PLAIN'); $t = term($accentPo); claim($t, null, 9);
    $datePo = po('QA DATE FALLBACK');
    $t = term($datePo, 1, '2025-01-01'); $a = allocation($t, 20, '2026-01-01'); claim($t, $a, 18, 'IMPORT', null);
    $t = term($datePo, 2); $a = allocation($t, 21); claim($t, $a, 19, 'IMPORT', null);
    $t = term($datePo, 3, null); claim($t, null, 20); // standalone NULL term date excludes the invoice
    $empty = po('QA ZERO HEADER');
    insertRow('tb_po_dashboard_cache', ['bowheer' => 'PT EMR - NRO', 'pic' => 'QA', 'done_inv_2026' => 987654, 'all_invoice' => 555, 'outs_2026_on_target' => 10, 'ny_po_on_target_2026' => 20, 'has_data' => 1, 'sort_order' => 1]);
    insertRow('tb_po_dashboard_cache', ['bowheer' => 'QA ZERO HEADER', 'pic' => 'QA', 'done_inv_2026' => 777, 'has_data' => 1, 'sort_order' => 99]);
    $model = new MPO_Monitor();
    $before = [];
    foreach ($tables as $table) { $before[$table] = $conn->query('CHECKSUM TABLE `' . $table . '`')->row_array()['Checksum']; }
    $report = $model->getImportReportRows(); $reportTotal = 0; $nro = 0;
    foreach ($report as $r) { $reportTotal += cents($r['INVOICE 2026']); if (strtoupper(trim($r['BOWHEER'])) === 'PT EMR - NRO') { $nro += cents($r['INVOICE 2026']); } }
    check($nro === 28575, 'report preserved: allocated, fallback, zero NULL semantics, negative, term range and year policy');
    $summary = $model->getDashboardSummary(false);
    check(cents(rowByName($summary['rows'], 'PT EMR - NRO')['done_inv_2026']) === $nro, 'current NRO equals Excel invoice 2026');
    check(count(array_filter($summary['rows'], function ($r) { return strtoupper(trim($r['bowheer'])) === 'PT EMR - NRO'; })) === 1, 'case and trailing-space variants share one dashboard row');
    $accentRows = array_values(array_filter($summary['rows'], function ($r) { return stripos($r['bowheer'], 'QA CAF') === 0; }));
    check(count($accentRows) === 1 && cents($accentRows[0]['done_inv_2026']) === 1700, 'database accent-insensitive identity retained');
    check(cents(rowByName($summary['rows'], 'QA DATE FALLBACK')['done_inv_2026']) === 3700, 'allocated date falls back to allocation then term; standalone NULL term date excluded');
    check(cents($summary['totals']['done_inv_2026']) === $reportTotal, 'current grand total equals full Excel');
    check(cents(rowByName($summary['rows'], 'QA CANCELLED ONLY')['done_inv_2026']) === 4275, 'cancelled-only bowheer included for invoice');
    check(cents(rowByName($summary['rows'], 'QA CANCELLED ONLY')['all_po']) === 0, 'cancelled invoice does not inflate PO metric');
    check(cents(rowByName($summary['rows'], 'PT VGREEN ( EVCS )')['done_inv_2026']) === 2900, 'VGREEN EVCS/DC grouping preserved');
    check(cents(rowByName($summary['rows'], 'PT VGREEN ( BSS )')['done_inv_2026']) === 3100, 'VGREEN BSS/pattern grouping preserved');
    check(cents(rowByName($summary['rows'], 'QA ZERO HEADER')['done_inv_2026']) === 0, 'header invoice ignored when no report invoice');
    $cached = $model->getDashboardSummary(true);
    check(cents(rowByName($cached['rows'], 'PT EMR - NRO')['done_inv_2026']) === $nro, 'stale cache invoice ignored');
    check(cents(rowByName($cached['rows'], 'QA ZERO HEADER')['done_inv_2026']) === 0, 'zero report resets stale cache invoice');
    check(cents($cached['totals']['done_inv_2026']) === $reportTotal, 'cached grand total includes all invoice-only groups');
    check(cents(rowByName($cached['rows'], 'PT EMR - NRO')['all_invoice']) === 55500, 'cached unrelated metrics untouched');
    $table = $model->getDashboardDatatable(['length' => -1, 'search' => ['value' => 'PT EMR - NRO'], 'order' => [['column' => 4, 'dir' => 'desc']]]);
    check(count($table['data']) === 1 && cents($table['filteredTotals']['done_inv_2026']) === $nro, 'datatable filtered row and total agree');
    $table = $model->getDashboardDatatable(['length' => 2, 'order' => [['column' => 4, 'dir' => 'desc']]]);
    check(cents($table['filteredTotals']['done_inv_2026']) === $reportTotal && count($table['data']) === 2, 'pagination does not truncate corrected totals');
    check($table['data'][0]['done_inv_2026'] >= $table['data'][1]['done_inv_2026'], 'sorting uses corrected invoice');
    $initial = $model->getDashboardDatatable(['dashboard_mode' => 'initial', 'length' => -1]);
    $manual = 30.50; // selected fallback 25.50 plus standalone 5; all MANUAL claims existing behavior
    check(cents($initial['data'][0]['done_inv_2026']) === cents(987654 - $manual), 'Data awal retains historical raw cache minus MANUAL');
    check(cents($model->getDashboardInitialTotals()['done_inv_2026']) === cents(987654 + 777 - $manual), 'initial card remains historical');
    $breakdown = $model->getDashboardTargetInvoiceBreakdownRows();
    $nroBreakdown = null; foreach ($breakdown as $r) { if ($r['project'] === 'PT EMR - NRO') { $nroBreakdown = $r; } }
    check($nroBreakdown !== null && cents($nroBreakdown['achieved']) === $nro, 'target breakdown uses corrected invoice');
    foreach ($before as $name => $checksum) { check($conn->query('CHECKSUM TABLE `' . $name . '`')->row_array()['Checksum'] === $checksum, 'read paths preserve ' . $name); }
    $t = term($p2, 1); claim($t, null, 1);
    check(cents(rowByName($model->getDashboardSummary()['rows'], 'PT EMR - NRO')['done_inv_2026']) === $nro + 100, 'reused model reads fresh invoice changes without stale request cache');
    echo "TOTAL: $checks passed\n";
} finally {
    $conn->trans_rollback();
    foreach ($restoreColumns as $sql) { $conn->query($sql); }
}
