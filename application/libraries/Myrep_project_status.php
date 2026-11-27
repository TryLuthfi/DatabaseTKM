<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Shared read-time workflow projection; never changes stored project state. */
class Myrep_project_status
{
    const ORDER = [
        'DRAFT', 'NTP', 'BA OPEN', 'BAK', 'VALSAL', 'WAITING HO', 'WAITING MYREP',
        'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP',
        'CHECKLIST', 'CHECKLIST DOKUMENT', 'DONE', 'IMPLEMENTASI', 'REJECTED', 'HOLD',
    ];

    public static function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    public static function rank($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'CHECKLIST') {
            $status = 'CHECKLIST DOKUMENT';
        }
        $index = array_search($status, self::ORDER, true);
        return $index === false ? -1 : (int) $index;
    }

    public static function resolve(array $row)
    {
        $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $status = $status !== '' ? $status : 'DRAFT';
        if ($status === 'CHECKLIST') {
            $status = 'CHECKLIST DOKUMENT';
        }
        if ($status === 'DONE') {
            return 'DONE';
        }
        $derived = $status;
        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        $statusRfs = strtoupper(trim((string) ($row['status_rfs'] ?? '')));
        $actualAtpDate = self::normalizeDate($row['actual_atp_date'] ?? null);
        $tanggalRfs = self::normalizeDate($row['tanggal_rfs'] ?? $row['latest_claim_date'] ?? null);
        if ($statusAtp === 'DONE' && $actualAtpDate !== null) {
            $derived = 'CHECKLIST DOKUMENT';
        } elseif ($statusAtp === 'PUNCLIST' || $actualAtpDate !== null) {
            $derived = 'ATP';
        } elseif (in_array($statusRfs, ['PARTIAL', 'PARTIAL RFS', 'FULL RFS'], true) || $tanggalRfs !== null) {
            $derived = 'RFS';
        }
        return self::rank($derived) > self::rank($status) ? $derived : $status;
    }

    public static function display(array $row)
    {
        if (in_array(strtoupper(trim((string) ($row['project_type'] ?? ''))), ['MAINFEEDER', 'FWA'], true)) {
            return strtoupper(trim((string) ($row['status_current'] ?? 'DRM'))) ?: 'DRM';
        }
        $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $statusDrm = strtoupper(trim((string) ($row['status_drm'] ?? '')));
        return $status === 'DONE' && strpos($statusDrm, 'IMPLEMENTASI') !== false ? 'IMPLEMENTASI' : $status;
    }
}
