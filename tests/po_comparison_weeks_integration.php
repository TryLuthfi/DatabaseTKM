<?php
/** Rollback-only fixture; --baseline points to the pre-change model source. */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php'; require BASEPATH . 'database/DB.php'; require BASEPATH . 'core/Model.php';
require APPPATH . 'models/MPO_Monitor.php';
$opts = getopt('', ['baseline:', 'missing-lock-table', 'zero-lock']);
if (empty($opts['baseline']) || !is_file($opts['baseline'])) { exit("Provide --baseline=<original-model.php>\n"); }
$source = file_get_contents($opts['baseline']);
eval(substr(str_replace('class MPO_Monitor extends', 'class MPO_Monitor_WeeksBaseline extends', $source), 5));
include APPPATH . 'config/database.php';
$config = $db['default']; $config['database'] = 'zeyn_po_invoice_test_20261017'; $config['pconnect'] = false; $config['db_debug'] = false;
if (!preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $config['hostname'])) { exit('Unsafe test host'); }
$conn = DB($config, true);
if ($conn->query('SELECT DATABASE() AS name')->row_array()['name'] !== 'zeyn_po_invoice_test_20261017') { exit('Wrong schema'); }
class WeeksFixtureDb {
    public $db, $writes = [], $missing = false, $detailReads = 0;
    function __construct($db) { $this->db = $db; }
    function __call($method, $args) { $result = $this->db->$method(...$args); return $result === $this->db ? $this : $result; }
    function table_exists($table) { return $this->missing && $table === 'tb_po_comparison_target_lock' ? false : $this->db->table_exists($table); }
    function query($sql, $binds = false, $return = null) {
        if (strpos($sql, 'ORDER BY target_week_start ASC, po_number ASC, term_index ASC') !== false) { $this->detailReads++; }
        if (preg_match('/^\s*INSERT IGNORE INTO tb_po_comparison_target_lock/i', $sql)) { $binds[6] = '[clock]'; $this->writes[] = $binds; return true; }
        return $this->db->query($sql, $binds, $return);
    }
}
$proxy = new WeeksFixtureDb($conn); $proxy->missing = isset($opts['missing-lock-table']); $instance = (object) ['db' => $proxy];
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
$conn->trans_begin();
try {
    $tables = ['tb_po_comparison_target_lock', 'tb_po_term_claim', 'tb_po_term_allocation', 'tb_po_term', 'tb_po_target_pipeline', 'tb_po', 'tb_bowheer_po', 'tb_myrep_po_header', 'tb_myrep_cluster', 'tb_rfs_myrep_mainfeeder'];
    foreach ($tables as $table) { $conn->query('DELETE FROM `' . $table . '`'); }
    $project = fixture('tb_bowheer_po', ['bowheer' => 'QA WEEKS', 'bowheer_key' => 'qa-weeks', 'pic' => 'QA', 'no_urut' => 1]);
    $po = fixture('tb_po', ['id_bowheer' => $project, 'po_number' => 'QA-WEEKS-1', 'po_date' => '2026-01-01']);
    // Deliberately duplicated header join rows must remain duplicated in detail-based sums.
    fixture('tb_myrep_po_header', ['po_number' => 'QA-WEEKS-1', 'po_category' => 'FINAL']);
    fixture('tb_myrep_po_header', ['po_number' => 'QA-WEEKS-1', 'po_category' => 'AMANDMENT']);
    foreach ([['2025-12-28','2026-01-03'],['2026-01-04','2026-01-10'],['2026-09-27','2026-10-03'],['2026-10-04','2026-10-10'],['2026-10-11','2026-10-17'],['2026-10-18','2026-10-24'],['2026-12-27','2027-01-02']] as $i => $dates) {
        $term = fixture('tb_po_term', ['id_po' => $po, 'term_index' => $i + 1, 'target_status' => 'TARGET_WEEK', 'target_week_start' => $dates[0], 'target_week_end' => $dates[1], 'plan_amount' => $i === 1 ? 0 : ($i === 2 ? -30 : 100 + $i), 'value' => 250]);
        if ($i === 3) {
            foreach ([40,60] as $amount) { fixture('tb_po_term_allocation', ['id_term' => $term, 'target_status' => 'TARGET_WEEK', 'target_week_start' => $dates[0], 'target_week_end' => $dates[1], 'plan_amount' => 0, 'allocation_value' => $amount]); }
        }
        if ($i % 2 === 0) { fixture('tb_po_term_claim', ['id_term' => $term, 'invoice_date' => $i === 0 ? '2026-01-01' : '2026-10-13', 'invoice_amount' => $i === 2 ? -15 : 25, 'claim_source' => 'MANUAL']); }
    }
    fixture('tb_po_target_pipeline', ['id_bowheer' => $project, 'target_status' => 'TARGET_WEEK', 'target_week_start' => '2026-10-11', 'target_week_end' => '2026-10-17', 'plan_amount' => 75]);
    if (isset($opts['zero-lock'])) {
        fixture('tb_po_comparison_target_lock', ['id_bowheer' => $project, 'group_by' => 'week', 'period_key' => '2026-W42', 'locked_amount' => 0, 'raw_amount' => 999, 'deviasi_amount' => 999]);
        fixture('tb_po_comparison_target_lock', ['id_bowheer' => $project, 'group_by' => 'month', 'period_key' => '2026-10', 'locked_amount' => 0]);
    }
    $before = new MPO_Monitor_WeeksBaseline(); $after = new MPO_Monitor();
    foreach ([['2026-01','2026-01'],['2026-10','2026-10'],['2026-12','2026-12'],['2026-09','2026-12'],['2025-12','2027-01']] as $bounds) {
        $proxy->writes = [];
        $expected = ['week' => $before->getComparisonMatrix($bounds[0], $bounds[1], 'week', false), 'month' => $before->getComparisonMatrix($bounds[0], $bounds[1], 'month', false)];
        $expectedWrites = $proxy->writes; $proxy->writes = [];
        $proxy->detailReads = 0;
        $actual = $after->getComparisonWeekMatrices(...$bounds);
        verify($actual === $expected, 'full matrix exact: ' . implode('..', $bounds));
        verify($proxy->writes === $expectedWrites, 'lock write arguments and order exact: ' . implode('..', $bounds));
        verify($proxy->detailReads <= 1, 'week and month share one project source: ' . implode('..', $bounds));
        $property = new ReflectionProperty(MPO_Monitor::class, 'comparisonWeekRequest'); $property->setAccessible(true);
        verify($property->getValue($after) === null, 'temporary source reuse cleared');
    }
    verify($before->getComparisonMatrix('2026-10','2026-10','month',false) === $after->getComparisonMatrix('2026-10','2026-10','month',false), 'initial monthly matrix unchanged');
    echo "RESULT $checks PASS\n";
} finally { $conn->trans_rollback(); }
