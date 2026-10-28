<?php
/** Render the actual correction/history/SLA view fragments. No framework, database or network. */
define('BASEPATH', dirname(__DIR__) . '/system/');
require dirname(__DIR__) . '/application/libraries/Batch_workflow_policy.php';
function base_url($path) { return 'https://fixture.invalid/' . $path; }
function verifyView($condition, $label) {
    if (!$condition) { throw new RuntimeException('FAIL: ' . $label); }
    $GLOBALS['checks']++;
}
$checks = 0;
$source = file_get_contents(dirname(__DIR__) . '/application/views/Batch_Approval_MyRep/detail.php');
$helpersStart = strpos($source, "if (!function_exists('batchDetailBadgeClass'))");
$helpersEnd = strpos($source, '$displayStageStatus =');
eval(substr($source, $helpersStart, $helpersEnd - $helpersStart));
$correctionStart = strpos($source, '<?php if (!empty($canCorrectBatchWorkflow) && !empty($batchEditReady)): ?>', strpos($source, '<div class="modal fade" id="modal-reject-batch-data"'));
if ($correctionStart === false) {
    // The first occurrence is the button; the second is the actual modal.
    $correctionStart = strrpos(substr($source, 0, strpos($source, 'id="modal-correct-batch-workflow"')), '<?php if (!empty($canCorrectBatchWorkflow) && !empty($batchEditReady)): ?>');
}
$correctionEnd = strpos($source, '<?php if ($canEdit): ?>', $correctionStart);
$correctionSource = substr($source, $correctionStart, $correctionEnd - $correctionStart);
function renderFragment($source, array $data) { extract($data); ob_start(); eval('?>' . $source); return ob_get_clean(); }
$data = ['canCorrectBatchWorkflow' => true, 'batchEditReady' => true, 'cluster' => ['id_myrep_cluster' => 15112, 'id_batch_approval' => 13239], 'batchEditCsrf' => 'csrf"<unsafe>', 'batchWorkflowBaselineHash' => 'baseline"<unsafe>', 'currentStage' => 'RELEASED', 'batchWorkflowStages' => array_combine(Batch_workflow_policy::STAGES, Batch_workflow_policy::STAGES)];
$html = renderFragment($correctionSource, $data);
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
verifyView($xpath->query('//form[@action="https://fixture.invalid/Batch_Approval_MyRep/correctWorkflow"]')->length === 1, 'dedicated POST endpoint rendered');
verifyView($xpath->query('//form[@method="post"]')->length === 1, 'form uses POST');
verifyView(strpos($html, 'Nomor/tanggal Batch Approval yang sudah tercatat tetap berlaku; pilihan tahap awal yang bertentangan akan ditolak.') !== false, 'stage help explains preserved batch approval and contradictory early-stage rejection');
foreach (['cluster_id', 'id_batch_approval', 'batch_edit_csrf', 'baseline_hash', 'target_stage', 'saku_action', 'correction_reason', 'correction_evidence'] as $field) {
    verifyView($xpath->query('//*[@name="' . $field . '"]')->length === 1, 'single field ' . $field);
}
verifyView($xpath->query('//textarea[@name="correction_reason"][@required]')->length === 1, 'reason always required');
verifyView($xpath->query('//select[@name="saku_action"]/option[1]')->item(0)->getAttribute('value') === 'KEEP', 'default preserves Saku');
verifyView($xpath->query('//input[@name="batch_edit_csrf"]')->item(0)->getAttribute('value') === 'csrf"<unsafe>', 'CSRF rendered as escaped attribute');
verifyView($xpath->query('//input[@name="baseline_hash"]')->item(0)->getAttribute('value') === 'baseline"<unsafe>', 'baseline rendered as escaped attribute');
foreach (Batch_workflow_policy::STAGES as $stage) {
    $rendered = renderFragment($correctionSource, array_replace($data, ['currentStage' => $stage]));
    $stageDom = new DOMDocument(); @$stageDom->loadHTML($rendered); $stageXpath = new DOMXPath($stageDom);
    $selected = $stageXpath->query('//select[@name="target_stage"]/option[@selected]');
    verifyView($selected->length === 1 && $selected->item(0)->getAttribute('value') === $stage, 'current stage preserved: ' . $stage);
}
verifyView(trim(renderFragment($correctionSource, array_replace($data, ['canCorrectBatchWorkflow' => false]))) === '', 'non-admin has no correction form');
verifyView(trim(renderFragment($correctionSource, array_replace($data, ['batchEditReady' => false]))) === '', 'unready audit storage has no correction form');

$sakuSetupStart = strpos($source, '$currentDonationStage =');
$sakuSetupEnd = strpos($source, '?>', $sakuSetupStart);
$sakuCardStart = strpos($source, '<?php if ($isSakuFlowVisible): ?>', $sakuSetupEnd);
$sakuCardEnd = strpos($source, '            <div class="card shadow-sm batch-sla-card">', $sakuCardStart);
foreach ([['RELEASED', 'NY', 'Belum Tercatat'], ['DRAFT', 'APPROVED', 'Disetujui Finance']] as $case) {
    $cluster = ['staging_status' => $case[0], 'saku_finance_approval_status' => $case[1]];
    $batchSakuPresentation = Batch_workflow_policy::sakuPresentation($cluster);
    eval(substr($source, $sakuSetupStart, $sakuSetupEnd - $sakuSetupStart));
    $sakuHtml = renderFragment(substr($source, $sakuCardStart, $sakuCardEnd - $sakuCardStart), compact('cluster', 'batchSakuPresentation', 'sakuFinanceStatus', 'isSakuFlowVisible', 'sakuFinanceLabel', 'sakuFinanceBadgeClass', 'isSakuWaitingFinanceApproval') + ['displayStageStatus' => $case[0], 'canFinanceApprovalAction' => false]);
    verifyView(strpos($sakuHtml, $case[2]) !== false, 'actual Saku badge: ' . $case[0] . ' / ' . $case[1]);
    verifyView(strpos($sakuHtml, 'Approval perubahan data batch') !== false, 'Saku card distinguishes data-edit approval');
    verifyView(strpos($sakuHtml, '<form') === false, 'read-only user has no Saku review form');
}

// Exercise the actual SLA definition and iteration with missing Saku metadata and real later dates.
$slaStart = strpos($source, '$slaStartDate =');
$slaEnd = strpos($source, '$postDonasiUploadableRows =', $slaStart);
$cluster = ['submission_date' => '2026-10-01', 'submitted_to_finance_at' => '2026-10-26', 'released_at' => '2026-10-27'];
$preZeynDocumentRows = []; $postZeynDocumentRows = [];
eval(substr($source, $slaStart, $slaEnd - $slaStart));
$sla = array_column($slaRows, null, 'name');
verifyView($sla['Pengajuan Saku']['actual_date'] === '' && $sla['Approval Saku Finance']['actual_date'] === '', 'missing Saku dates never borrowed from Finance/payment');
verifyView($sla['Pembayaran Donasi']['actual_date'] === '2026-10-27', 'real later payment date survives missing earlier actual');
verifyView($sla['Pembayaran Donasi']['target_date'] === '' && $sla['Pembayaran Donasi']['work_days'] === '-', 'unknown predecessor has no invented SLA target/duration');
verifyView(strpos($sla['Pembayaran Donasi']['status']['label'], 'Tercatat') === 0, 'real actual with unknown SLA is labeled recorded');

class ViewSession { public function userdata($key) { return $key === 'id_user' ? 77 : null; } }
class HistoryViewHarness {
    public $session;
    public function __construct() { $this->session = new ViewSession(); }
    public function render($data) { extract($data); ob_start(); include dirname(__DIR__) . '/application/views/Batch_Approval_MyRep/edit_history.php'; return ob_get_clean(); }
}
$event = ['event_type' => 'WORKFLOW_CORRECTED', 'actor_name' => '<script>bad()</script>', 'actor_id' => 77, 'created_at' => '2026-10-28 12:00:00', 'staging_status' => 'WAITING_FINANCE_RELEASE', 'note' => 'Referensi bukti: DOC-123'];
$history = ['id_request' => 1, 'status' => 'APPLIED', 'submitted_at' => $event['created_at'], 'submitted_by' => 77, 'staging_at_submit' => 'RELEASED', 'reason' => '<img src=x onerror=bad()>', 'before' => ['workflow' => ['staging_status' => 'RELEASED', 'saku_finance_approval_status' => 'NY']], 'after' => ['workflow' => ['staging_status' => 'WAITING_FINANCE_RELEASE', 'saku_finance_approval_status' => 'APPROVED']], 'events' => [$event]];
$harness = new HistoryViewHarness();
$historyHtml = $harness->render(['batchEditReady' => true, 'batchEditHistory' => [$history], 'batchEditSitac' => true, 'batchEditFinance' => true]);
verifyView(strpos($historyHtml, 'Koreksi staging / approval Saku') !== false, 'workflow history distinct heading');
verifyView(strpos($historyHtml, 'Workflow · Status Approval Saku') !== false && strpos($historyHtml, 'APPROVED') !== false && strpos($historyHtml, 'NY') !== false, 'workflow before/after shown');
verifyView(strpos($historyHtml, 'DOC-123') !== false && strpos($historyHtml, '#77') !== false, 'evidence actor and timestamp shown');
verifyView(strpos($historyHtml, '<script>bad()') === false && strpos($historyHtml, '<img src=x') === false, 'history untrusted text escaped');
verifyView(strpos($historyHtml, '<form') === false, 'applied correction cannot be reviewed as pending data edit');
$legacy = array_replace($history, ['before' => ['batch' => ['hp_donasi' => 10]], 'after' => ['batch' => ['hp_donasi' => 20]], 'events' => [array_replace($event, ['event_type' => 'FINANCE_APPROVED'])]]);
$legacyHtml = $harness->render(['batchEditReady' => true, 'batchEditHistory' => [$legacy]]);
verifyView(strpos($legacyHtml, 'Perubahan data batch disetujui Finance (bukan approval Saku)') !== false, 'legacy Finance event clearly distinct from Saku');
verifyView(strpos($legacyHtml, 'HP Donasi Astri') !== false && strpos($legacyHtml, '>10<') !== false && strpos($legacyHtml, '>20<') !== false, 'legacy data history preserved without optional groups');
echo json_encode(['ok' => true, 'checks' => $checks, 'coverage' => 'Actual PHP fragment render, 30 stage choices, permission/readiness, escaping, old/new history, real SLA chronology; no browser pixels or live submission.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
