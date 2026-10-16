<?php
/** Actual CI query builder + MariaDB, synthetic rows in a dedicated empty schema only.
 * D:/XAMPP/php/php.exe tests/batch_edit_integration.php
 */
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
require BASEPATH . 'core/Common.php';
require BASEPATH . 'database/DB.php';
require BASEPATH . 'core/Model.php';
require APPPATH . 'models/MBatch_edit_approval.php';
include APPPATH . 'config/database.php';
$config = $db['default'];
$config['database'] = 'zeyn_batch_edit_test_20261016';
$config['pconnect'] = false; $config['db_debug'] = false;
if (strpos($config['database'], 'zeyn_batch_edit_test_') !== 0 || !preg_match('/^(127\.0\.0\.1|localhost)(:\d+)?$/', $config['hostname'])) { exit("Unsafe test DB target\n"); }
class TestSession {
    public $id = 990002;
    public function userdata($key) { return $key === 'id_user' ? $this->id : ($key === 'nama_user' ? 'Synthetic reviewer ' . $this->id : null); }
}
$instance = (object) ['db' => DB($config, true), 'session' => new TestSession()];
function &get_instance() { global $instance; return $instance; }
$conn = $instance->db;
$actual = $conn->query('SELECT DATABASE() AS name')->row_array();
if ($actual['name'] !== $config['database']) { exit("Wrong test schema\n"); }
if (($argv[1] ?? '') === '--concurrent-submit') {
    $childService = new MBatch_edit_approval(); $context = $childService->context(990101);
    $batch = $context['batch']; $batch['remark_batch_approval'] = 'Concurrent synthetic edit';
    echo json_encode($childService->submit(990101, 990201, $batch, $context['cluster'], $context['pics'], 'Concurrent QA', $childService->token($context)));
    exit;
}
foreach (explode(';', file_get_contents(dirname(__DIR__) . '/database/myrep_batch_edit_approval.sql')) as $statement) {
    if (trim($statement) !== '' && !$conn->query($statement)) { throw new RuntimeException('Migration failed'); }
}
$checks = 0;
function check($condition, $message) { global $checks; if (!$condition) { throw new RuntimeException('FAIL: ' . $message); } $checks++; echo "PASS: $message\n"; }
function insertSynthetic($table, array $row) {
    global $conn;
    foreach ($conn->query('SHOW COLUMNS FROM `' . $table . '`')->result_array() as $column) {
        if (array_key_exists($column['Field'], $row) || $column['Null'] === 'YES' || $column['Default'] !== null || strpos($column['Extra'], 'auto_increment') !== false) { continue; }
        $type = $column['Type'];
        if (preg_match('/^(int|bigint|tinyint|smallint|mediumint|decimal|float|double|bit)/', $type)) { $value = 0; }
        elseif (strpos($type, 'datetime') === 0 || strpos($type, 'timestamp') === 0) { $value = '2026-10-16 09:00:00'; }
        elseif (strpos($type, 'date') === 0) { $value = '2026-10-16'; }
        elseif (preg_match("/^enum\('([^']*)'/", $type, $match)) { $value = $match[1]; }
        else { $value = ''; }
        $row[$column['Field']] = $value;
    }
    if (!$conn->insert($table, $row)) { throw new RuntimeException('Fixture insert failed: ' . $table . ' ' . json_encode($conn->error())); }
}
// Clean only test rows owned by this suite. No business rows have been copied here.
foreach (['tb_myrep_batch_edit_event', 'tb_myrep_batch_edit_request', 'tb_myrep_batch_approval_pic', 'tb_myrep_batch_approval', 'tb_myrep_cluster', 'tb_myrep_pic_mapping_city', 'tb_master_user_new'] as $table) { $conn->query('DELETE FROM `' . $table . '`'); }
foreach ([990001 => 'QA_SITAC', 990002 => 'QA_AREA', 990003 => 'QA_FINANCE', 990004 => 'QA_WRONG', 990005 => 'QA_SITAC2'] as $id => $nik) {
    insertSynthetic('tb_master_user_new', ['id' => $id, 'nik' => $nik, 'nama_karyawan' => 'Synthetic ' . $nik, 'username_user' => strtolower($nik)]);
}
insertSynthetic('tb_myrep_pic_mapping_city', ['city_name' => 'QA CITY', 'province_name' => 'QA PROVINCE', 'regional_name' => 'QA REGION', 'sitac_ho' => 'QA_SITAC,QA_SITAC2', 'finance_ho' => 'QA_FINANCE', 'admin_area' => 'QA_AREA', 'is_active' => 1]);
$service = new MBatch_edit_approval();
check($service->ready(), 'additive migration ready / all protected tables InnoDB');
function fixture($stage = 'BATCH_APPROVED', $paid = false) {
    global $conn, $service;
    $conn->query('DELETE FROM tb_myrep_batch_edit_event'); $conn->query('DELETE FROM tb_myrep_batch_edit_request');
    $conn->query('DELETE FROM tb_myrep_batch_approval_pic'); $conn->query('DELETE FROM tb_myrep_batch_approval'); $conn->query('DELETE FROM tb_myrep_cluster');
    insertSynthetic('tb_myrep_cluster', ['id_myrep_cluster' => 990101, 'cluster_name' => 'QA SYNTHETIC BATCH', 'cluster_code' => 'QA-BATCH', 'city_name' => 'QA CITY', 'province_name' => 'QA PROVINCE', 'regional_name' => 'QA REGION', 'district_name' => 'QA DISTRICT', 'village_name' => 'QA VILLAGE', 'status_current' => 'WAITING HO']);
    insertSynthetic('tb_myrep_batch_approval', ['id_batch_approval' => 990201, 'id_myrep_cluster' => 990101, 'staging_status' => $stage, 'hp_donasi' => 10, 'nominal_pengajuan_area' => 1000, 'nominal_nego_emr' => 1000, 'nominal_per_homepass' => 100, 'recipient_name' => 'QA Recipient', 'recipient_phone' => '000000', 'bank_name' => 'QA Bank', 'bank_account_number' => 'QA-0001', 'astri_batch_number' => 'QA-2026', 'astri_batch_approved_at' => '2026-10-16 00:00:00', 'nominal_release_finance' => $paid ? 950 : null, 'released_at' => $paid ? '2026-10-15 09:00:00' : null, 'transfer_proof_file_path' => $paid ? 'qa/proof.pdf' : null]);
    insertSynthetic('tb_myrep_batch_approval_pic', ['id_batch_approval' => 990201, 'pic_no' => 1, 'pic_name' => 'QA Recipient', 'pic_phone' => '000000', 'is_primary' => 1]);
    if (in_array($stage, ['DRAFT', 'WAITING_BATCH_APPROVAL'], true)) {
        $conn->where('id_batch_approval', 990201)->update('tb_myrep_batch_approval', ['astri_batch_number' => null, 'astri_batch_approved_at' => null]);
    }
    return $service->context(990101);
}
function submitChange($reason = 'Synthetic correction', $token = null, $field = 'remark_batch_approval', $value = 'Changed by QA', $resubmit = false) {
    global $service;
    $context = $service->context(990101); $batch = $context['batch']; $batch[$field] = $value;
    return $service->submit(990101, 990201, $batch, $context['cluster'], $context['pics'], $reason, $token ?? $service->token($context), $resubmit);
}
function lastRequest() { global $conn; return $conn->order_by('id_request', 'DESC')->get('tb_myrep_batch_edit_request')->row_array(); }
function review($id, $action = 'APPROVE', $note = 'QA review') { global $service; return $service->review(990101, 990201, $id, $action, $note); }
foreach (Batch_edit_policy::FREE_STAGES as $stage) {
    fixture($stage); $result = submitChange(''); check($result['ok'], "$stage saves directly without reason"); check(count($service->history(990201)) === 0, "$stage does not create edit history");
}
fixture('NEED_REVISE'); check(submitChange('', null, 'remark_batch_approval', 'Revision', true)['ok'], 'revision resubmit succeeds'); check($service->context(990101)['batch']['staging_status'] === 'WAITING_BATCH_APPROVAL', 'revision resubmit preserves existing workflow');
fixture(); check(!submitChange('')['ok'], 'protected edit requires reason'); check(!submitChange('reason', 'stale')['ok'], 'stale form rejected');
$context = $service->context(990101); check(!$service->submit(990101, 999999, $context['batch'], $context['cluster'], $context['pics'], 'QA', $service->token($context))['ok'], 'mismatched batch / cluster ID rejected');
check(submitChange()['ok'], 'area submits protected edit'); $pending = lastRequest(); check($pending['status'] === 'PENDING_SITAC', 'area pending SITAC'); check($service->context(990101)['batch']['remark_batch_approval'] !== 'Changed by QA', 'pending does not mutate active data'); check(!submitChange()['ok'], 'second pending request rejected');
$instance->session->id = 990004; check(!review($pending['id_request'])['ok'], 'unmapped reviewer rejected');
$instance->session->id = 990002; check(!review($pending['id_request'])['ok'], 'requester cannot review');
$instance->session->id = 990001; check(review($pending['id_request'])['ok'], 'mapped SITAC approves'); check($service->context(990101)['batch']['remark_batch_approval'] === 'Changed by QA', 'approved values applied'); check(!review($pending['id_request'])['ok'], 'duplicate approval rejected');
fixture(); check(submitChange()['ok'], 'SITAC edits directly'); check(lastRequest()['status'] === 'APPLIED', 'SITAC direct edit history');
$instance->session->id = 990002; fixture(); submitChange(); $pending = lastRequest(); $conn->where('id_batch_approval', 990201)->update('tb_myrep_batch_approval', ['staging_status' => 'HOLD']); $instance->session->id = 990001; check(!review($pending['id_request'])['ok'], 'changed staging prevents approval'); check(lastRequest()['status'] === 'STALE', 'stale request closes with history');
$instance->session->id = 990002; fixture(); submitChange(); $pending = lastRequest(); $instance->session->id = 990001; check(review($pending['id_request'], 'REJECT')['ok'], 'mapped SITAC rejects'); check(lastRequest()['status'] === 'REJECTED', 'rejection closes pending slot');
// A post-payment correction must keep the actual transaction untouched through both reviews.
$instance->session->id = 990002; fixture('RELEASED', true); submitChange('Account correction', null, 'bank_account_number', 'QA-0002'); $pending = lastRequest();
check((int) $pending['finance_required'] === 1, 'paid bank correction flagged Finance'); $instance->session->id = 990001; check(review($pending['id_request'])['ok'], 'SITAC approves paid correction'); check(lastRequest()['status'] === 'PENDING_FINANCE', 'paid correction waits Finance'); check($service->context(990101)['batch']['bank_account_number'] === 'QA-0001', 'Finance pending leaves data active');
$instance->session->id = 990003; check(review($pending['id_request'])['ok'], 'mapped Finance approves'); $after = $service->context(990101)['batch']; check($after['bank_account_number'] === 'QA-0002' && (float) $after['nominal_release_finance'] === 950.0 && $after['released_at'] === '2026-10-15 09:00:00' && $after['transfer_proof_file_path'] === 'qa/proof.pdf', 'correction applied; actual amount/date/proof preserved');
$instance->session->id = 990001; fixture('RELEASED', true); check(submitChange('SITAC bank correction', null, 'bank_account_number', 'QA-0002')['ok'], 'SITAC submits postpaid correction'); check(lastRequest()['status'] === 'PENDING_FINANCE', 'SITAC bypass does not bypass Finance');
fixture('DRAFT', true); check(Batch_edit_policy::protectedStage($service->context(990101)['batch']), 'payment evidence protects reset draft');
foreach (['DRAFT', 'WAITING_BATCH_APPROVAL'] as $rawStage) {
    foreach (['astri_batch_number' => 'QA-APPROVED', 'astri_batch_approved_at' => '2026-10-16 12:34:56'] as $field => $value) {
        fixture($rawStage); $conn->where('id_batch_approval', 990201)->update('tb_myrep_batch_approval', [$field => $value]);
        check(Batch_edit_policy::effectiveStage($service->context(990101)['batch']) === 'BATCH_APPROVED', "$rawStage plus $field follows canonical approved stage");
        check(!submitChange('')['ok'], "$rawStage plus $field requires protected edit reason/history");
        check(submitChange('Canonical stage edit')['ok'], "$rawStage plus $field allows audited SITAC edit");
        $canonicalRequest = lastRequest();
        check($canonicalRequest['staging_at_submit'] === 'BATCH_APPROVED' && $service->context(990101)['batch']['staging_status'] === $rawStage, 'history records effective approval stage without changing raw active stage');
        $context = $service->context(990101); $cleared = $context['batch']; $cleared['astri_batch_number'] = null; $cleared['astri_batch_approved_at'] = null;
        check(!$service->submit(990101, 990201, $cleared, $context['cluster'], $context['pics'], 'Try status downgrade', $service->token($context))['ok'], 'approval evidence cannot be cleared to downgrade effective status');
    }
}
// A real DB error after history insertion must roll back history + active data together.
$instance->session->id = 990001; fixture();
$conn->query('DROP TRIGGER IF EXISTS qa_fail_batch_edit');
$conn->query("CREATE TRIGGER qa_fail_batch_edit BEFORE UPDATE ON tb_myrep_batch_approval FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Synthetic forced rollback'");
check(!submitChange()['ok'], 'injected DB write failure returns failure');
check(count($service->history(990201)) === 0 && $service->context(990101)['batch']['remark_batch_approval'] !== 'Changed by QA', 'DB failure rolls back audit and active edit atomically');
$conn->query('DROP TRIGGER qa_fail_batch_edit');
// Two independent PHP connections contend for the same pending slot.
$instance->session->id = 990002; fixture(); $processes = [];
for ($i = 0; $i < 2; $i++) {
    $process = proc_open([PHP_BINARY, __FILE__, '--concurrent-submit'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
    if (!is_resource($process)) { throw new RuntimeException('Cannot launch concurrency test'); }
    fclose($pipes[0]); $processes[] = [$process, $pipes];
}
$successCount = 0;
foreach ($processes as [$process, $pipes]) {
    $result = json_decode(stream_get_contents($pipes[1]), true); $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]); $exit = proc_close($process);
    check($exit === 0 && is_array($result) && $error === '', 'concurrent worker completed cleanly');
    if ($result['ok']) { $successCount++; }
}
check($successCount === 1 && count($service->history(990201)) === 1, 'concurrent submissions produce exactly one pending request');
// Leave a safe populated pending UI fixture for independent browser QA.
$instance->session->id = 990002; fixture(); submitChange('QA pending proposal for independent review');
echo "TOTAL $checks PASS. Synthetic UI fixture cluster=990101 batch=990201; reviewer=990001.\n";
