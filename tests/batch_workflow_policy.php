<?php
define('BASEPATH', dirname(__DIR__) . '/system/');
require dirname(__DIR__) . '/application/libraries/Batch_workflow_policy.php';
$checks = 0;
function verify($ok, $name) { global $checks; if (!$ok) { throw new RuntimeException($name); } $checks++; }
function denied(callable $fn, $name) { try { $fn(); } catch (RuntimeException $e) { verify(true, $name); return; } throw new RuntimeException('Allowed: ' . $name); }
$batch = ['staging_status' => 'RELEASED', 'saku_finance_approval_status' => 'NY', 'released_at' => null, 'nominal_release_finance' => 0, 'transfer_proof_file_path' => null];
$p = Batch_workflow_policy::sakuPresentation($batch, 'PRE_ZEYN_DOC_ON_REVIEW');
verify($p['label'] === 'Belum Tercatat' && $p['warning'] !== '' && $p['visible'], 'raw advanced stage cannot hide missing approval');
foreach (['APPROVED', 'REJECTED', 'ON REVIEW'] as $status) {
    $p = Batch_workflow_policy::sakuPresentation(array_replace($batch, ['staging_status' => 'DRAFT', 'saku_finance_approval_status' => $status]));
    verify($p['status'] === $status && $p['visible'], 'persisted review remains visible on regression');
}
$p = Batch_workflow_policy::sakuPresentation(['staging_status' => 'DRAFT']);
verify($p['label'] === 'Belum Diajukan' && $p['warning'] === '' && !$p['visible'], 'early unsubmitted label unchanged');
$p = Batch_workflow_policy::sakuPresentation(['staging_status' => 'PRE_ZEYN_FINANCE_APPROVED', 'saku_finance_approval_status' => 'NY']);
verify($p['label'] === 'Belum Diajukan' && $p['warning'] === '' && $p['visible'], 'pre Saku stage normally not yet submitted');
$p = Batch_workflow_policy::sakuPresentation(['staging_status' => 'DRAFT', 'saku_finance_approval_status' => 'NY', 'saku_finance_reviewed_by' => 12]);
verify($p['visible'] && $p['warning'] !== '', 'orphan metadata remains visible on early stage');
$p = Batch_workflow_policy::sakuPresentation(['staging_status' => 'DRAFT', 'saku_finance_approval_status' => 'APPROVED']);
verify($p['visible'] && $p['warning'] !== '' && $p['status'] === 'APPROVED', 'approved missing metadata flagged without rewriting');
$now = '2026-10-28 12:00:00';
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'KEEP', 'APPROVE', 99, $now); }, 'stage alone cannot prove release');
$plan = Batch_workflow_policy::plan($batch, 'WAITING_FINANCE_RELEASE', 'APPROVE', 99, $now);
verify($plan['saku_finance_reviewed_by'] === 99 && $plan['saku_finance_reviewed_at'] === $now, 'reconciliation records actual actor/time');
verify(!isset($plan['released_at'], $plan['nominal_release_finance'], $plan['transfer_proof_file_path'], $plan['saku_finance_requested_at']), 'no fake payment or historical request');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'WAITING_FINANCE_RELEASE', 'KEEP', 99, $now); }, 'release wait needs approval');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'WAITING FINANCE', 'KEEP', 99, $now); }, 'legacy release wait needs approval');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'WAITING DOC', 'APPROVE', 99, $now); }, 'legacy waiting doc needs payment');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'WAITING_SAKU_FINANCE_APPROVAL', 'KEEP', 99, $now); }, 'review wait needs request');
$plan = Batch_workflow_policy::plan($batch, 'WAITING_SAKU_FINANCE_APPROVAL', 'REQUEST', 99, $now);
verify($plan['saku_finance_approval_status'] === 'ON REVIEW' && $plan['saku_finance_requested_by'] === 99 && $plan['saku_finance_reviewed_at'] === null, 'explicit request resets review metadata');
$paid = array_replace($batch, ['saku_finance_approval_status' => 'APPROVED', 'released_at' => $now, 'nominal_release_finance' => 100, 'transfer_proof_file_path' => 'qa/proof.png']);
foreach (['RESET', 'REJECT', 'REQUEST'] as $action) {
    denied(function () use ($paid, $now, $action) { Batch_workflow_policy::plan($paid, 'HOLD', $action, 99, $now); }, 'payment protects ' . $action);
}
denied(function () use ($paid, $now) { Batch_workflow_policy::plan($paid, 'DRAFT', 'KEEP', 99, $now); }, 'paid stage cannot regress');
$plan = Batch_workflow_policy::plan($paid, 'HOLD', 'KEEP', 99, $now);
verify($plan === ['staging_status' => 'HOLD'], 'hold preserves paid evidence');
foreach (['released_at', 'nominal_release_finance', 'transfer_proof_file_path'] as $field) {
    $partial = $paid; $partial[$field] = null;
    denied(function () use ($partial, $now) { Batch_workflow_policy::plan($partial, 'WAITING_POST_ZEYN_DOC', 'KEEP', 99, $now); }, 'each payment evidence required ' . $field);
}
foreach (['0000-00-00 00:00:00', '2026-02-30 10:00:00', 'bad-date'] as $value) {
    $partial = array_replace($paid, ['released_at' => $value]);
    denied(function () use ($partial, $now) { Batch_workflow_policy::plan($partial, 'WAITING_POST_ZEYN_DOC', 'KEEP', 99, $now); }, 'invalid payment timestamp denied');
}
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'EVIL', 'KEEP', 99, $now); }, 'unknown stage denied');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'DRAFT', 'FORGE', 99, $now); }, 'unknown action denied');
denied(function () use ($batch, $now) { Batch_workflow_policy::plan($batch, 'DRAFT', 'KEEP', 0, $now); }, 'invalid actor denied');
denied(function () use ($now) { Batch_workflow_policy::plan(['staging_status' => 'DRAFT'], 'DRAFT', 'KEEP', 99, $now); }, 'noop denied');
foreach (['DRAFT', 'WAITING HO', 'WAITING_BATCH_APPROVAL'] as $target) {
    foreach (['astri_batch_number' => 'QA-APPROVED', 'astri_batch_approved_at' => '2026-10-28 12:00:00'] as $field => $value) {
        $approved = array_replace($batch, ['id_batch_approval' => 123, $field => $value]);
        denied(function () use ($approved, $target, $now) { Batch_workflow_policy::plan($approved, $target, 'KEEP', 99, $now); }, 'existing approval cannot be silently downgraded ' . $target . ' ' . $field);
    }
}
denied(function () use ($batch, $now) { Batch_workflow_policy::plan(array_replace($batch, ['id_batch_approval' => 123]), 'WAITING HO', 'KEEP', 99, $now); }, 'legacy waiting HO resolves waiting batch and is not exact correction target');
echo "PASS $checks workflow policy checks\n";
