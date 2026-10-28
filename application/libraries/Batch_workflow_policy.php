<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/Batch_edit_policy.php';

/** Explicit administrator correction rules; stage alone never proves approval/payment. */
class Batch_workflow_policy
{
    const STAGES = ['DRAFT', 'WAITING_BATCH_APPROVAL', 'BATCH_APPROVED', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'WAITING DOC', 'WAITING_PRE_ZEYN_DOC', 'NEED_REVISE', 'PRE_ZEYN_DOC_ON_REVIEW', 'PRE_ZEYN_DOC_APPROVED', 'PRE_ZEYN_FINANCE_ON_REVIEW', 'PRE_ZEYN_FINANCE_APPROVED', 'WAITING_SAKU_FINANCE_APPROVAL', 'WAITING_FINANCE_RELEASE', 'RELEASED', 'WAITING_POST_ZEYN_DOC', 'POST_ZEYN_DOC_ON_REVIEW', 'POST_ZEYN_DOC_APPROVED', 'POST_ZEYN_FINANCE_ON_REVIEW', 'WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW', 'NEED_REVISE_ASTRI', 'ASTRI_APPROVED', 'PO_DONASI', 'INVOICE', 'DONE BATCH APPROVAL', 'COMPLETED', 'HOLD', 'REJECTED'];
    const PAID_STAGES = ['RELEASED', 'WAITING DOC', 'WAITING_POST_ZEYN_DOC', 'POST_ZEYN_DOC_ON_REVIEW', 'POST_ZEYN_DOC_APPROVED', 'POST_ZEYN_FINANCE_ON_REVIEW', 'WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW', 'NEED_REVISE_ASTRI', 'ASTRI_APPROVED', 'PO_DONASI', 'INVOICE', 'DONE BATCH APPROVAL', 'COMPLETED'];
    const SAKU_FIELDS = ['saku_finance_approval_status', 'saku_finance_request_remark', 'saku_finance_requested_at', 'saku_finance_requested_by', 'saku_finance_review_remark', 'saku_finance_reviewed_at', 'saku_finance_reviewed_by'];

    public static function snapshot(array $batch)
    {
        $result = [];
        foreach (array_merge(['staging_status'], self::SAKU_FIELDS) as $field) { $result[$field] = isset($batch[$field]) ? (string) $batch[$field] : null; }
        return $result;
    }

    public static function sakuPresentation(array $batch, $displayStage = null)
    {
        $status = strtoupper(trim((string) ($batch['saku_finance_approval_status'] ?? 'NY')));
        if ($status === '') { $status = 'NY'; }
        $stage = strtoupper(trim((string) ($displayStage === null ? ($batch['staging_status'] ?? '') : $displayStage)));
        $rawStage = strtoupper(trim((string) ($batch['staging_status'] ?? '')));
        $advanced = array_merge(['WAITING_SAKU_FINANCE_APPROVAL', 'WAITING_FINANCE_RELEASE', 'WAITING FINANCE'], self::PAID_STAGES);
        $inconsistent = in_array($stage, $advanced, true) || in_array($rawStage, $advanced, true);
        $labels = ['NY' => 'Belum Diajukan', 'ON REVIEW' => 'Menunggu Approval Finance', 'APPROVED' => 'Disetujui Finance', 'REJECTED' => 'Ditolak Finance'];
        $badges = ['NY' => 'secondary', 'ON REVIEW' => 'warning', 'APPROVED' => 'success', 'REJECTED' => 'danger'];
        $hasMetadata = false;
        foreach (self::SAKU_FIELDS as $field) { if ($field !== 'saku_finance_approval_status' && !empty($batch[$field])) { $hasMetadata = true; break; } }
        $warning = '';
        if ($status === 'NY' && ($inconsistent || $hasMetadata)) {
            $labels['NY'] = 'Belum Tercatat';
            $warning = 'Approval Saku belum tercatat meskipun staging sudah lanjut. Approval perubahan data batch bukan approval Saku. Super Admin dapat melakukan koreksi berdasarkan bukti.';
        } elseif ($status === 'APPROVED' && (!self::validDateTime($batch['saku_finance_reviewed_at'] ?? '') || (int) ($batch['saku_finance_reviewed_by'] ?? 0) <= 0)) {
            $warning = 'Status Saku tersimpan disetujui, tetapi metadata reviewer/tanggal belum lengkap. Periksa bukti; tidak ada reviewer atau tanggal historis yang dibuat otomatis.';
        } elseif (!isset($labels[$status])) {
            $warning = 'Status Approval Saku tidak dikenal. Periksa history dan lakukan koreksi berdasarkan bukti.';
        }
        return ['status' => $status, 'label' => $labels[$status] ?? 'Perlu Pemeriksaan', 'badge' => $badges[$status] ?? 'warning', 'warning' => $warning, 'visible' => $status !== 'NY' || $inconsistent || $hasMetadata || $stage === 'PRE_ZEYN_FINANCE_APPROVED'];
    }

    private static function validDateTime($value)
    {
        $text = trim((string) $value);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $text);
        return $date !== false && $date->format('Y-m-d H:i:s') === $text;
    }

    public static function plan(array $batch, $targetStage, $sakuAction, $actorId, $now)
    {
        if ((int) $actorId <= 0) { throw new RuntimeException('Akun administrator tidak valid.'); }
        $current = strtoupper(trim((string) ($batch['staging_status'] ?? 'DRAFT')));
        $target = strtoupper(trim((string) $targetStage));
        if ($target === '' || $target === 'KEEP') { $target = $current; }
        if (!in_array($target, self::STAGES, true)) { throw new RuntimeException('Target staging tidak valid.'); }
        $action = strtoupper(trim((string) $sakuAction));
        if (!in_array($action, ['KEEP', 'REQUEST', 'APPROVE', 'REJECT', 'RESET'], true)) { throw new RuntimeException('Aksi koreksi Saku tidak valid.'); }
        $status = strtoupper(trim((string) ($batch['saku_finance_approval_status'] ?? 'NY')));
        $payload = [];
        if ($target !== $current) { $payload['staging_status'] = $target; }
        if ($action === 'REQUEST') {
            if ($status === 'ON REVIEW') { throw new RuntimeException('Pengajuan Saku sudah menunggu review.'); }
            $payload += ['saku_finance_approval_status' => 'ON REVIEW', 'saku_finance_requested_at' => $now, 'saku_finance_requested_by' => (int) $actorId, 'saku_finance_review_remark' => null, 'saku_finance_reviewed_at' => null, 'saku_finance_reviewed_by' => null];
        } elseif ($action === 'APPROVE' || $action === 'REJECT') {
            $desired = $action === 'APPROVE' ? 'APPROVED' : 'REJECTED';
            if ($status === $desired && self::validDateTime($batch['saku_finance_reviewed_at'] ?? '') && (int) ($batch['saku_finance_reviewed_by'] ?? 0) > 0) { throw new RuntimeException('Status review Saku sudah sama.'); }
            $payload += ['saku_finance_approval_status' => $desired, 'saku_finance_reviewed_at' => $now, 'saku_finance_reviewed_by' => (int) $actorId];
        } elseif ($action === 'RESET') {
            foreach (self::SAKU_FIELDS as $field) { $payload[$field] = $field === 'saku_finance_approval_status' ? 'NY' : null; }
        }
        $candidate = array_replace($batch, $payload);
        if (in_array($target, ['DRAFT', 'WAITING HO', 'WAITING_BATCH_APPROVAL'], true) && Batch_edit_policy::effectiveStage($candidate) !== $target) {
            throw new RuntimeException('Target staging tidak sesuai bukti approval batch yang sudah tersimpan. Nomor dan tanggal approval tidak dihapus oleh koreksi workflow.');
        }
        $hasPayment = !empty($batch['released_at']) || (float) ($batch['nominal_release_finance'] ?? 0) > 0 || !empty($batch['transfer_proof_file_path']);
        $completePayment = self::validDateTime($batch['released_at'] ?? '') && (float) ($batch['nominal_release_finance'] ?? 0) > 0 && trim((string) ($batch['transfer_proof_file_path'] ?? '')) !== '';
        $approval = strtoupper(trim((string) ($candidate['saku_finance_approval_status'] ?? 'NY')));
        if ($hasPayment && (!in_array($target, array_merge(self::PAID_STAGES, ['HOLD', 'REJECTED']), true) || in_array($action, ['RESET', 'REQUEST', 'REJECT'], true))) { throw new RuntimeException('Ada bukti pembayaran. Staging tidak dapat dikembalikan sebelum pembayaran atau approval Saku dihapus.'); }
        if (in_array($target, self::PAID_STAGES, true) && !$completePayment) { throw new RuntimeException('Staging setelah pembayaran memerlukan tanggal, nominal positif dan bukti transfer yang sudah tersimpan.'); }
        if ((in_array($target, ['WAITING_FINANCE_RELEASE', 'WAITING FINANCE'], true) || in_array($target, self::PAID_STAGES, true)) && $approval !== 'APPROVED') { throw new RuntimeException('Staging pembayaran memerlukan Approval Saku yang tercatat disetujui.'); }
        if ($target === 'WAITING_SAKU_FINANCE_APPROVAL' && $approval !== 'ON REVIEW') { throw new RuntimeException('Staging menunggu Saku memerlukan status Saku ON REVIEW.'); }
        $changed = false;
        foreach ($payload as $key => $value) { if (($batch[$key] ?? null) !== $value) { $changed = true; break; } }
        if (!$changed) { throw new RuntimeException('Tidak ada perubahan workflow.'); }
        return $payload;
    }
}
