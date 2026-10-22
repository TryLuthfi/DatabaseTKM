<?php
/** Read-only helper regression and optional actual model city-scope check.
 * php tests/myrep_pic_access_readonly.php [--helper=/path/helper.php] [--user=206]
 * Model constructors are skipped: no schema setup, sync, or session writes.
 */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php';
require BASEPATH . 'database/DB.php';
require BASEPATH . 'core/Model.php';
$options = getopt('', ['helper:', 'user:']);
require ($options['helper'] ?? APPPATH . 'helpers/myrep_pic_helper.php');
include APPPATH . 'config/database.php';
$config = $db['default'];
$config['pconnect'] = false;
$config['db_debug'] = false;
$config['cache_on'] = false;
class PicAccessSession {
    public $values;
    public function userdata($key) { return $this->values[$key] ?? null; }
}
$instance = (object) ['db' => DB($config, true), 'session' => new PicAccessSession()];
function &get_instance() { global $instance; return $instance; }
$conn = $instance->db;
$checks = 0;
function checkPicAccess($ok, $message) {
    global $checks;
    if (!$ok) { throw new RuntimeException('FAIL: ' . $message); }
    $checks++;
    echo 'PASS: ' . $message . PHP_EOL;
}
function picRead($sql, array $binds = []) {
    global $conn;
    $result = $conn->query($sql, $binds);
    if (!$result) { throw new RuntimeException('Read-only query failed'); }
    return $result;
}
picRead('START TRANSACTION READ ONLY');
try {
    $cases = [
        ['Meilandha Vransisca', 'Meilandha Vransisca', true],
        ['Meilandha Vransisca', '8109226, Meilandha Vransisca ,8818124', true],
        ['Meilandha Vransisca', '  Meilandha Vransisca  ', true],
        ['Meilandha Vransisca', "8109226,\tMeilandha Vransisca\t,8818124", true],
        ['Meilandha Vransisca', 'MeilandhaVransisca', false],
        ['MeilandhaVransisca', 'Meilandha Vransisca', false],
        ['Meilandha', 'Meilandha Vransisca', false],
        ['8109226', '8109226,8818124', true],
        ['8818124', '8109226 ,   8818124  ', true],
        ['810922', '8109226,8818124', false],
        ['8109226', null, false],
        ['8109226', '', false],
        ['', '8109226', false],
        ["O'Neil User", "8109226,O'Neil User", true],
    ];
    foreach ($cases as $i => [$nik, $csv, $expected]) {
        $sql = myrep_pic_column_contains_sql($conn, $conn->escape($csv), $nik);
        $row = picRead('SELECT (' . $sql . ') AS hit')->row_array();
        checkPicAccess((bool) $row['hit'] === $expected, 'identifier membership case ' . ($i + 1));
    }
    if (isset($options['user'])) {
        $userId = (int) $options['user'];
        $user = picRead('SELECT id, nik, id_level FROM tb_master_user_new WHERE id = ?', [$userId])->row_array();
        checkPicAccess(!empty($user), 'target user exists');
        $instance->session->values = ['id_user' => $userId, 'id_level' => (int) $user['id_level'], 'nama_level' => 'User'];
        foreach (['MBAK_MyRep', 'MDRM_MyRep', 'MVALSAL_MyRep', 'MChecklist_Dokument_MyRep', 'MBatch_Approval_MyRep', 'MPO_MyRep'] as $class) {
            require_once APPPATH . 'models/' . $class . '.php';
            $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $method = new ReflectionMethod($class, 'getCurrentUserAllowedCitySet');
            $method->setAccessible(true);
            $cities = array_keys($method->invoke($model));
            sort($cities);
            checkPicAccess($cities === ['PRABUMULIH'], $class . ' actual city resolver = PRABUMULIH only');
            if ($class === 'MBAK_MyRep') {
                $restriction = new ReflectionMethod($class, 'shouldRestrictCityByUser');
                $restriction->setAccessible(true);
                checkPicAccess($restriction->invoke($model) === true, 'BAK retains city restriction');
                $filter = new ReflectionMethod($class, 'filterRowsByCurrentUserAllowedCities');
                $filter->setAccessible(true);
                $rows = [['city_name' => 'PRABUMULIH'], ['city_name' => 'PALEMBANG']];
                checkPicAccess($filter->invoke($model, $rows, 'city_name') === [$rows[0]], 'BAK includes mapped city and excludes unrelated city');
                $bakRows = $model->getBakRows();
                checkPicAccess(count($bakRows) > 0, 'BAK real list is populated');
                checkPicAccess(array_unique(array_column($bakRows, 'city_name')) === ['PRABUMULIH'], 'BAK real list contains Prabumulih only');
                $page = $model->getBakRowsPage();
                checkPicAccess($page['recordsTotal'] === count($bakRows) && count($page['rows']) > 0, 'BAK DataTable total and page match real list');
                checkPicAccess($model->getBakRows('PALEMBANG') === [], 'BAK rejects explicit unrelated city filter');
                echo 'BAK visible list rows: ' . count($bakRows) . PHP_EOL;
            }
        }
        require_once APPPATH . 'models/MRFS_Readiness_MyRep.php';
        $rfs = (new ReflectionClass('MRFS_Readiness_MyRep'))->newInstanceWithoutConstructor();
        checkPicAccess($rfs->userHasCityRole($userId, 'PRABUMULIH', 'admin_area'), 'RFS respects mapped admin_area role');
        checkPicAccess(!$rfs->userHasCityRole($userId, 'PRABUMULIH', 'sitac_ho'), 'RFS does not grant unmapped sitac_ho role');
        $count = picRead("SELECT COUNT(*) AS total FROM tb_myrep_cluster WHERE UPPER(TRIM(city_name)) = 'PRABUMULIH'")->row_array();
        echo 'Mapped-city source clusters: ' . (int) $count['total'] . PHP_EOL;
    }
    echo 'PASS: ' . $checks . ' checks; database transaction read only' . PHP_EOL;
} finally {
    $conn->query('ROLLBACK');
}
