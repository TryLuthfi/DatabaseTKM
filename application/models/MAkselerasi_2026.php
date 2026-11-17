<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Read-only projection of the Target 250 M claim ledger. No schema/claim mutations. */
class MAkselerasi_2026 extends CI_Model
{
    const START_DATE = '2026-11-16';

    /** Locked baseline; keep KOMITEL separate here even while its display is combined. */
    public static function definitions()
    {
        return [
            ['morep_nro', 'MOREP', 'NRO', 12168587129, 12352185138, 'WARDANI'],
            ['morep_donasi', 'MOREP', 'DONASI', 193098089, 868052999, 'WARDANI'],
            ['morep_sop', 'MOREP', 'SOP', 7617887800, 3353131745, 'SLAMET'],
            ['iforte_fttt', 'IFORTE', 'FTTT', 1260345310, 2075873358, 'DONNY'],
            ['iforte_ftth', 'IFORTE', 'FTTH', 699189480, 0, 'DONNY'],
            ['iforte_reguler', 'IFORTE', 'REGULER', 415514500, 48474500, 'DONNY'],
            ['iforte_lbs', 'IFORTE', 'LBS', 640594000, 490902500, 'HENDRY'],
            ['komitel_rektif', 'KOMITEL', 'REKTIF', 0, 1690406865, 'HENDRY'],
            ['ta_star', 'TA', 'STAR', 0, 543932635, 'HENDRY'],
            ['ta_osp', 'TA', 'OSP', 494647710, 1619628719, 'ZAENUL'],
            ['pst_osp', 'PST/MTEL', 'OSP', 1082666569, 0, 'ZAENUL'],
            ['xlsmart_osp', 'XLSMART', 'OSP', 0, 930987250, 'ZAENUL'],
            ['tbg_permit', 'TBG', 'PERMIT', 745357000, 654174000, 'ZAENUL'],
            ['mti_osp', 'MTI', 'OSP', 171369000, 0, 'ZAENUL'],
            ['vgreen_evcs', 'VGREEN', 'EVCS', 3301274326, 3325946236, 'WENDY'],
            ['vgreen_bss', 'VGREEN', 'BSS', 735941656, 591078646, 'WENDY'],
        ];
    }

    private static function normalize($value)
    {
        return trim(preg_replace('/\s+/', ' ', strtoupper((string) $value)));
    }

    public static function resolveKey($bowheer, $type)
    {
        $name = self::normalize($bowheer);
        $type = self::normalize($type);
        if ($name === 'KOMITEL' && $type === 'REKTIF') {
            return 'komitel_rektif';
        }
        $aliases = [
            'PT EMR - NRO' => 'morep_nro',
            'PT EMR - DONASI' => 'morep_donasi',
            'PT EMR - PU ( NON PPN )' => 'morep_sop',
            'PT IFORTE - FIBERIZATION' => 'iforte_fttt',
            'PT IFORTE - FTTH IOH' => 'iforte_ftth',
            'PT IFORTE - FTTH XL' => 'iforte_ftth',
            'PT IFORTE - REGULAR & CONN' => 'iforte_reguler',
            'PT IFORTE - LBS RECTIFIKASI' => 'iforte_lbs',
            'PT KOMITEL' => 'komitel_rektif',
            'PT KOMITEL - REKTIF' => 'komitel_rektif',
            'PT KOMITEL - LBS RECTIFIKASI' => 'komitel_rektif',
            'PT TELKOM AKSES - STAR' => 'ta_star',
            'PT TELKOM AKSES' => 'ta_osp',
            'PT PERSADA SOKKA TAMA' => 'pst_osp',
            'PT XL SMART' => 'xlsmart_osp',
            'PT TBG ( PERMIT )' => 'tbg_permit',
            'PT MORATEL' => 'mti_osp',
            'PT VGREEN ( EVCS )' => 'vgreen_evcs',
            'PT VGREEN ( BSS )' => 'vgreen_bss',
        ];
        if (isset($aliases[$name])) {
            return $aliases[$name];
        }
        if ($name === 'PT VGREEN') {
            if ($type === 'EVCS' || strpos($type, 'DC') === 0) {
                return 'vgreen_evcs';
            }
            if ($type === 'BSS' || preg_match('/^[0-9]+S[0-9]+P$/', $type)) {
                return 'vgreen_bss';
            }
        }
        return null;
    }

    public static function validEndDate($date)
    {
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            return false;
        }
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date && $date >= self::START_DATE && $date < '9999-12-31';
    }

    /** Pure projection, also used by focused regression tests. */
    public static function buildDashboard($claims, $masters, $endDate)
    {
        if (!self::validEndDate($endDate)) {
            throw new InvalidArgumentException('Tanggal akhir tidak valid.');
        }
        $splitKomitel = false;
        foreach ($masters as $master) {
            if (self::normalize($master['bowheer']) === 'KOMITEL' || self::resolveKey($master['bowheer'], '') === 'komitel_rektif') {
                $splitKomitel = true;
            }
        }
        $rows = [];
        foreach (self::definitions() as $def) {
            list($key, $bowheer, $type, $regular, $acceleration, $pic) = $def;
            if (!$splitKomitel && $key === 'komitel_rektif') {
                $rows['iforte_lbs']['acceleration'] += $acceleration;
                $rows['iforte_lbs']['bowheer'] = 'IFORTE / KOMITEL';
                $rows['iforte_lbs']['type'] = 'LBS / REKTIF';
                continue;
            }
            $rows[$key] = compact('key', 'bowheer', 'type', 'regular', 'acceleration', 'pic') + ['achievement' => 0, 'claim_count' => 0];
        }
        $unmapped = [];
        $sourceTotal = 0;
        foreach ($claims as $claim) {
            $amount = (float) $claim['achievement'];
            $sourceTotal += $amount;
            $key = self::resolveKey($claim['bowheer'], $claim['type']);
            if (!$splitKomitel && $key === 'komitel_rektif') {
                $key = 'iforte_lbs';
            }
            if ($key === null) {
                $unmapped[] = $claim;
                continue;
            }
            $rows[$key]['achievement'] += $amount;
            $rows[$key]['claim_count'] += (int) $claim['claim_count'];
        }
        foreach ($rows as &$row) {
            $row['achievement'] = round($row['achievement'], 2);
            $row['total'] = $row['regular'] + $row['acceleration'];
            // Negative corrections reverse regular achievement; signed net is preserved.
            $row['regular_achievement'] = min($row['achievement'], $row['regular']);
            $row['acceleration_achievement'] = max(0, $row['achievement'] - $row['regular']);
            $row['remaining'] = max(0, $row['total'] - $row['achievement']);
            $row['percentage'] = $row['total'] > 0 ? $row['achievement'] / $row['total'] * 100 : 0;
        }
        unset($row);
        return ['rows' => array_values($rows), 'unmapped' => $unmapped, 'source_total' => round($sourceTotal, 2),
            'from' => self::START_DATE, 'to' => $endDate, 'komitel_separate' => $splitKomitel];
    }

    public function loadDashboard($endDate)
    {
        if (!self::validEndDate($endDate)) {
            throw new InvalidArgumentException('Tanggal akhir tidak valid.');
        }
        $nextDate = (new DateTime($endDate))->modify('+1 day')->format('Y-m-d');
        // Exactly one join path per claim. Do not join allocations (would multiply values),
        // and do not use all-time term SUM/MAX date snapshots for a bounded date report.
        $sql = "SELECT COALESCE(NULLIF(TRIM(p.dashboard_bowheer), ''), bp.bowheer, 'Tanpa Bowheer') AS bowheer,
                COALESCE(p.type_project, '') AS type,
                SUM(tc.invoice_amount) AS achievement, COUNT(*) AS claim_count
            FROM tb_po_term_claim tc
            JOIN tb_po_term t ON t.id_term = tc.id_term
            JOIN tb_po p ON p.id_po = t.id_po
            LEFT JOIN tb_bowheer_po bp ON bp.id_bowheer = p.id_bowheer
            WHERE tc.invoice_date >= ? AND tc.invoice_date < ?
            GROUP BY COALESCE(NULLIF(TRIM(p.dashboard_bowheer), ''), bp.bowheer, 'Tanpa Bowheer'),
                COALESCE(p.type_project, '')";
        $query = $this->db->query($sql, [self::START_DATE, $nextDate]);
        if (!$query) {
            throw new RuntimeException('Achievement tidak dapat dimuat.');
        }
        $masters = $this->db->select('bowheer')->get('tb_bowheer_po');
        if (!$masters) {
            throw new RuntimeException('Master bowheer tidak dapat dimuat.');
        }
        return self::buildDashboard($query->result_array(), $masters->result_array(), $endDate);
    }
}
