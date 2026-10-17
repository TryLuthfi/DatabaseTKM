<?php
/** No database connection: compare seed effects and operation counts against --baseline. */
define('BASEPATH', dirname(__DIR__) . '/system/');
class CI_Model { public $db; }
require dirname(__DIR__) . '/application/models/MPO_Monitor.php';
$opts = getopt('', ['baseline:']);
if (empty($opts['baseline']) || !is_file($opts['baseline'])) { exit("Provide --baseline=<original-model.php>\n"); }
$source = preg_replace('/^<\?php\s*/', '', file_get_contents($opts['baseline']));
eval(str_replace('class MPO_Monitor extends', 'class MPO_Monitor_SeedBaseline extends', $source));
class SeedResult {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function row_array() { return $this->rows[0] ?? null; }
    function result_array() { return $this->rows; }
}
class SeedDb {
    public $rows, $reads = 0, $updates = 0, $inserts = 0;
    private $ids = [], $id;
    function __construct($rows) { $this->rows = $rows; }
    function where_in($field, $ids) { $this->ids = $ids; return $this; }
    function get($table) { $this->reads++; return new SeedResult(array_values(array_intersect_key($this->rows, array_flip($this->ids)))); }
    function get_where($table, $where) { $this->reads++; return new SeedResult(isset($this->rows[$where['id_bowheer']]) ? [$this->rows[$where['id_bowheer']]] : []); }
    function where($field, $id) { $this->id = $id; return $this; }
    function update($table, $payload) { $this->updates++; $this->rows[$this->id] = array_replace($this->rows[$this->id], $payload); return true; }
    function insert($table, $payload) { $this->inserts++; $this->rows[$payload['id_bowheer']] = $payload; return true; }
}
function seed($class, $rows) {
    $model = new $class(); $model->db = new SeedDb($rows);
    $method = new ReflectionMethod($model, 'seedBowheerPo'); $method->setAccessible(true); $method->invoke($model);
    ksort($model->db->rows); return $model->db;
}
$checks = 0;
function checkSeed($ok, $label) { global $checks; if (!$ok) { throw new RuntimeException('FAIL ' . $label); } $checks++; echo 'PASS ' . $label . PHP_EOL; }
$fresh = seed('MPO_Monitor_SeedBaseline', []);
$changed = $fresh->rows;
$changed[9]['pic'] = 'Different'; $changed[9]['custom_field'] = 'preserve';
$changed[3]['no_urut'] = null; unset($changed[27]);
$changed[999] = ['id_bowheer'=>999, 'bowheer'=>'Extra', 'pic'=>'Keep'];
foreach (['fresh'=>[], 'unchanged'=>$fresh->rows, 'drift/missing/extra'=>$changed] as $label=>$rows) {
    $a = seed('MPO_Monitor_SeedBaseline', $rows); $b = seed('MPO_Monitor', $rows);
    checkSeed($a->rows === $b->rows, 'identical resulting master: ' . $label);
    checkSeed($b->reads === 1, 'one master read: ' . $label);
    checkSeed($b->inserts === $a->inserts, 'same missing-master inserts: ' . $label);
    if ($label === 'unchanged') { checkSeed($b->updates === 0 && $a->updates === 22, 'unchanged seed skips 22 writes'); }
    if ($label === 'drift/missing/extra') { checkSeed($b->updates === 2 && $b->inserts === 1, 'only drifted/missing seed entries written'); }
}
echo 'RESULT ' . $checks . ' PASS' . PHP_EOL;
