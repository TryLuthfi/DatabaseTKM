<?php
/** Actual CI/MariaDB transaction tests in an exclusively created disposable local schema. */
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
if (!preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $config['hostname'])) { exit("Local DB required\n"); }
$schema = 'zeyn_workflow_test_' . bin2hex(random_bytes(6));
$config['database'] = ''; $config['pconnect'] = false; $config['db_debug'] = false;
$admin = DB($config, true);
if (!$admin->query('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8mb4')) { exit("Cannot create isolated test schema\n"); }
class WorkflowTestSession {
    public $id = 99; public $role = 'SUPER ADMIN';
    public function userdata($key) { return ['id_user' => $this->id, 'nama_level' => $this->role, 'nama_user' => 'Synthetic administrator'][$key] ?? null; }
}
$config['database'] = $schema;
$instance = (object) ['db' => DB($config, true), 'session' => new WorkflowTestSession()];
function &get_instance() { global $instance; return $instance; }
$conn = $instance->db; $checks = 0;
function checkWorkflow($ok, $message) { global $checks; if (!$ok) { throw new RuntimeException('FAIL ' . $message); } $checks++; }
try {
    $conn->query('CREATE TABLE tb_myrep_cluster (id_myrep_cluster INT PRIMARY KEY, status_current VARCHAR(50), updated_by INT NULL) ENGINE=InnoDB');
    $conn->query('CREATE TABLE tb_myrep_batch_approval (id_batch_approval INT PRIMARY KEY, id_myrep_cluster INT, staging_status VARCHAR(60), saku_finance_approval_status VARCHAR(50) DEFAULT "NY", saku_finance_request_remark TEXT NULL, saku_finance_requested_at DATETIME NULL, saku_finance_requested_by INT NULL, saku_finance_review_remark TEXT NULL, saku_finance_reviewed_at DATETIME NULL, saku_finance_reviewed_by INT NULL, released_at DATETIME NULL, nominal_release_finance DECIMAL(20,2) NULL, transfer_proof_file_path TEXT NULL, updated_by INT NULL) ENGINE=InnoDB');
    $conn->query('CREATE TABLE tb_myrep_batch_approval_pic (id_batch_approval INT, pic_no INT, pic_name VARCHAR(60)) ENGINE=InnoDB');
    foreach (explode(';', file_get_contents(dirname(__DIR__) . '/database/myrep_batch_edit_approval.sql')) as $sql) { if (trim($sql) !== '' && !$conn->query($sql)) { throw new RuntimeException('Audit schema creation failed'); } }
    $conn->insert('tb_myrep_cluster', ['id_myrep_cluster' => 1, 'status_current' => 'BAK/VALSAL']);
    $conn->insert('tb_myrep_batch_approval', ['id_batch_approval' => 2, 'id_myrep_cluster' => 1, 'staging_status' => 'RELEASED']);
    $conn->insert('tb_myrep_batch_approval_pic', ['id_batch_approval' => 2, 'pic_no' => 1, 'pic_name' => 'Unchanged']);
    $svc = new MBatch_edit_approval();
    $call = function ($stage = 'WAITING_FINANCE_RELEASE', $action = 'APPROVE', $reason = 'Reconcile verified record', $proof = 'QA reference', $token = null, $validate = null) use ($svc) {
        return $svc->correctWorkflow(1, 2, $stage, $action, $reason, $proof, $token ?? $svc->token($svc->context(1)), $validate ?? function () { return ''; });
    };
    foreach (['AREA', 'FINANCE HO', 'SITAC HO', 'SUPERADMIN', 'SUPER ADMIN X'] as $role) { $instance->session->role = $role; checkWorkflow(!$call()['ok'], 'service denies role ' . $role); }
    $instance->session->role = 'SUPER ADMIN'; $instance->session->id = 0; checkWorkflow(!$call()['ok'], 'login required'); $instance->session->id = 99;
    checkWorkflow(!$call('DRAFT', 'KEEP', '')['ok'], 'reason required');
    checkWorkflow(!$call('DRAFT', 'APPROVE', 'Reason', '')['ok'], 'Saku evidence required');
    checkWorkflow(!$call('DRAFT', 'KEEP', str_repeat('x', 4001))['ok'], 'bounded reason');
    checkWorkflow(!$call('DRAFT', 'KEEP', 'Reason', str_repeat('x', 4001))['ok'], 'bounded evidence');
    checkWorkflow(!$call('DRAFT', 'KEEP', 'Reason', '', 'stale')['ok'], 'stale token denied');
    checkWorkflow(!$call('DRAFT', 'KEEP', 'Reason', '', null, function () { return 'Document gate rejected'; })['ok'], 'locked document validator enforced');
    checkWorkflow(!$call('DRAFT', 'KEEP', 'Reason', '', null, function () { return true; })['ok'], 'invalid validator denied');
    $before = $svc->context(1);
    checkWorkflow(!$svc->correctWorkflow(1, 999, 'HOLD', 'KEEP', 'QA reason', '', $svc->token($before), function () { return ''; })['ok'], 'forged batch ID rejected');
    checkWorkflow($call()['ok'], 'correction applied');
    $after = $svc->context(1); $history = $svc->history(2);
    checkWorkflow($after['batch']['staging_status'] === 'WAITING_FINANCE_RELEASE' && $after['batch']['saku_finance_approval_status'] === 'APPROVED', 'stage and Saku saved');
    checkWorkflow((int) $after['batch']['saku_finance_reviewed_by'] === 99 && $after['batch']['saku_finance_reviewed_at'] !== null && $after['batch']['saku_finance_requested_at'] === null, 'actor current, historical metadata not fabricated');
    checkWorkflow($after['cluster'] === $before['cluster'] && $after['pics'] === $before['pics'], 'cluster and PIC unchanged');
    checkWorkflow($after['batch']['released_at'] === null && $after['batch']['nominal_release_finance'] === null && $after['batch']['transfer_proof_file_path'] === null, 'payment not fabricated');
    checkWorkflow(count($history) === 1 && $history[0]['status'] === 'APPLIED' && $history[0]['pending_batch_id'] === null, 'separate applied history');
    checkWorkflow($history[0]['before']['workflow'] === Batch_workflow_policy::snapshot($before['batch']) && $history[0]['after']['workflow'] === Batch_workflow_policy::snapshot($after['batch']), 'exact before after audit');
    checkWorkflow($history[0]['events'][0]['event_type'] === 'WORKFLOW_CORRECTED' && (int) $history[0]['events'][0]['actor_id'] === 99 && strpos($history[0]['events'][0]['note'], 'QA reference') !== false, 'actual event actor and reason evidence');
    checkWorkflow(!$call('KEEP', 'KEEP')['ok'], 'duplicate noop denied');
    $conn->where('id_request', $history[0]['id_request'])->update('tb_myrep_batch_edit_request', ['pending_batch_id' => 2]);
    checkWorkflow(!$call('HOLD', 'KEEP')['ok'], 'pending correction blocked');
    $conn->where('id_request', $history[0]['id_request'])->update('tb_myrep_batch_edit_request', ['pending_batch_id' => null]);
    $conn->query("CREATE TRIGGER qa_workflow_fail BEFORE INSERT ON tb_myrep_batch_edit_event FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic audit failure'");
    checkWorkflow(!$call('HOLD', 'KEEP')['ok'], 'audit write failure rejected');
    checkWorkflow($svc->context(1) === $after && count($svc->history(2)) === 1, 'audit failure rolls back active data and request');
    $conn->query('DROP TRIGGER qa_workflow_fail');
    $conn->query("CREATE TRIGGER qa_workflow_fail BEFORE UPDATE ON tb_myrep_batch_approval FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic data failure'");
    checkWorkflow(!$call('HOLD', 'KEEP')['ok'], 'data write failure rejected');
    checkWorkflow($svc->context(1) === $after && count($svc->history(2)) === 1, 'data failure rolls back history');
    $conn->query('DROP TRIGGER qa_workflow_fail');
    checkWorkflow($call('WAITING_SAKU_FINANCE_APPROVAL', 'REQUEST')['ok'], 'explicit new request applied');
    $requested = $svc->context(1)['batch'];
    checkWorkflow($requested['saku_finance_approval_status'] === 'ON REVIEW' && (int) $requested['saku_finance_requested_by'] === 99 && $requested['saku_finance_reviewed_by'] === null, 'request actor and cleared prior review saved');
    checkWorkflow($call('PRE_ZEYN_FINANCE_APPROVED', 'REJECT')['ok'], 'explicit rejection applied');
    checkWorkflow($svc->context(1)['batch']['saku_finance_approval_status'] === 'REJECTED', 'rejection persists');
    checkWorkflow($call('DRAFT', 'RESET')['ok'], 'explicit unpaid reset applied');
    $reset = $svc->context(1)['batch'];
    foreach (Batch_workflow_policy::SAKU_FIELDS as $field) { checkWorkflow($reset[$field] === ($field === 'saku_finance_approval_status' ? 'NY' : null), 'reset clears only explicit Saku metadata ' . $field); }
    checkWorkflow(count($svc->history(2)) === 4, 'reset preserves all previous workflow history');
    echo "PASS $checks workflow integration checks\n";
} finally {
    $conn->close();
    if (!preg_match('/^zeyn_workflow_test_[a-f0-9]{12}$/', $schema)) { throw new RuntimeException('Unsafe test cleanup'); }
    $admin->query('DROP DATABASE `' . $schema . '`');
    $admin->close();
}
