<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Pure rules shared by forms, locked persistence and tests. */
class Batch_edit_policy
{
    const FREE_STAGES = ['DRAFT', 'WAITING_BATCH_APPROVAL', 'NEED_REVISE'];
    const BATCH_FIELDS = ['submission_date', 'hp_donasi', 'nominal_pengajuan_area', 'nominal_nego_emr', 'nominal_per_homepass', 'bank_name', 'bank_account_number', 'recipient_name', 'recipient_phone', 'recipient_position', 'recipient_period', 'free_wifi_qty', 'free_wifi_period_month', 'astri_batch_number', 'astri_batch_approved_at', 'remark_batch_approval'];
    const LOCATION_FIELDS = ['regency_id', 'district_id', 'district_name', 'village_id', 'village_name'];
    const PIC_FIELDS = ['pic_no', 'pic_name', 'pic_phone', 'pic_position', 'pic_period', 'is_primary'];
    const PAID_STAGES = ['RELEASED', 'WAITING_POST_ZEYN_DOC', 'POST_ZEYN_DOC_ON_REVIEW', 'POST_ZEYN_DOC_APPROVED', 'POST_ZEYN_FINANCE_ON_REVIEW', 'WAITING_ASTRI_SUBMISSION', 'ASTRI_ON_REVIEW', 'NEED_REVISE_ASTRI', 'ASTRI_APPROVED', 'PO_DONASI', 'INVOICE', 'DONE BATCH APPROVAL', 'COMPLETED'];

    public static function paid(array $row)
    {
        return !empty($row['released_at']) || !empty($row['transfer_proof_file_path']) || (float) ($row['nominal_release_finance'] ?? 0) > 0
            || in_array(strtoupper(trim((string) ($row['staging_status'] ?? ''))), self::PAID_STAGES, true);
    }

    public static function protectedStage(array $row)
    {
        // Match the application's canonical stored-stage interpretation, never posted staging.
        return self::paid($row) || !in_array(self::effectiveStage($row), self::FREE_STAGES, true);
    }

    public static function effectiveStage(array $row)
    {
        $stage = strtoupper(trim((string) ($row['staging_status'] ?? '')));
        if ((int) ($row['id_batch_approval'] ?? 0) > 0
            && in_array($stage, ['', 'DRAFT', 'WAITING HO', 'WAITING_BATCH_APPROVAL'], true)
            && (trim((string) ($row['astri_batch_number'] ?? '')) !== '' || trim((string) ($row['astri_batch_approved_at'] ?? '')) !== '')) {
            return 'BATCH_APPROVED';
        }
        if ((int) ($row['id_batch_approval'] ?? 0) > 0 && in_array($stage, ['', 'WAITING HO'], true)) { return 'WAITING_BATCH_APPROVAL'; }
        return $stage !== '' ? $stage : 'DRAFT';
    }

    private static function fields(array $row, array $fields)
    {
        $result = [];
        $numeric = ['hp_donasi', 'nominal_pengajuan_area', 'nominal_nego_emr', 'nominal_per_homepass', 'free_wifi_qty', 'free_wifi_period_month', 'pic_no', 'is_primary'];
        foreach ($fields as $key) {
            $value = $row[$key] ?? null;
            $result[$key] = $value === null || $value === '' ? null : (in_array($key, $numeric, true) ? (string) (0 + $value) : (string) $value);
        }
        return $result;
    }

    public static function snapshot(array $batch, array $cluster, array $pics)
    {
        $normalizedPics = [];
        foreach (array_values($pics) as $i => $pic) {
            $pic['pic_no'] = $i + 1;
            $normalizedPics[] = self::fields($pic, self::PIC_FIELDS);
        }
        return ['batch' => self::fields($batch, self::BATCH_FIELDS), 'location' => self::fields($cluster, self::LOCATION_FIELDS), 'pics' => $normalizedPics];
    }

    public static function fingerprint(array $batch, array $cluster, array $pics)
    {
        // Includes workflow/payment metadata: an approval must not overwrite a newer workflow.
        ksort($batch); ksort($cluster);
        return hash('sha256', json_encode([$batch, $cluster, self::snapshot($batch, $cluster, $pics)['pics']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function financeRequired(array $batch, array $before, array $after)
    {
        if (!self::paid($batch)) { return false; }
        foreach (['hp_donasi', 'nominal_pengajuan_area', 'nominal_nego_emr', 'nominal_per_homepass', 'bank_name', 'bank_account_number', 'recipient_name', 'recipient_phone', 'recipient_position', 'recipient_period'] as $key) {
            if ($before['batch'][$key] !== $after['batch'][$key]) { return true; }
        }
        return $before['pics'] !== $after['pics'];
    }
}
