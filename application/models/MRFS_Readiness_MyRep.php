<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MRFS_Readiness_MyRep extends CI_Model
{
    private $aspectFields = [
        'material' => 'material_status',
        'olt' => 'olt_status',
        'tenaga_kerja' => 'tenaga_kerja_status',
        'operasional' => 'operasional_status',
        'accessories' => 'accessories_status',
    ];

    public function tablesReady()
    {
        foreach ([
            'tb_myrep_rfs_readiness_period',
            'tb_myrep_rfs_readiness_item',
            'tb_myrep_rfs_readiness_change_request',
            'tb_myrep_rfs_readiness_evidence',
            'tb_myrep_rfs_readiness_history',
            'tb_myrep_rfs_readiness_notification',
            'tb_myrep_cluster',
            'tb_myrep_drm',
        ] as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function getAspectFields()
    {
        return $this->aspectFields;
    }

    public function getPeriodOptions()
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_period')) {
            return [];
        }

        return $this->db
            ->from('tb_myrep_rfs_readiness_period')
            ->order_by('year_num', 'DESC')
            ->order_by('month_num', 'DESC')
            ->get()
            ->result_array();
    }

    public function getOrCreateCurrentPeriod($userId)
    {
        $year = (int) date('Y');
        $month = (int) date('n');
        $period = $this->getPeriodByYearMonth($year, $month);
        if (!empty($period)) {
            return $period;
        }

        $id = $this->createPeriod($year, $month, null, '', $userId);
        return $this->getPeriodById($id);
    }

    public function createPeriod($year, $month, $meetingDate, $remark, $userId)
    {
        $year = (int) $year;
        $month = (int) $month;
        if ($year <= 0 || $month < 1 || $month > 12) {
            return 0;
        }

        $existing = $this->getPeriodByYearMonth($year, $month);
        if (!empty($existing)) {
            return (int) $existing['id_period'];
        }

        $this->db->insert('tb_myrep_rfs_readiness_period', [
            'year_num' => $year,
            'month_num' => $month,
            'meeting_date' => $meetingDate ?: null,
            'remark' => $remark !== '' ? $remark : null,
            'created_by' => (int) $userId,
        ]);

        return (int) $this->db->insert_id();
    }

    public function getPeriodById($periodId)
    {
        if ((int) $periodId <= 0 || !$this->db->table_exists('tb_myrep_rfs_readiness_period')) {
            return [];
        }

        return (array) $this->db
            ->from('tb_myrep_rfs_readiness_period')
            ->where('id_period', (int) $periodId)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getPeriodByYearMonth($year, $month)
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_period')) {
            return [];
        }

        return (array) $this->db
            ->from('tb_myrep_rfs_readiness_period')
            ->where('year_num', (int) $year)
            ->where('month_num', (int) $month)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getPeriodSummary($periodId, $city = '', $regional = '', $priority = '', $finalStatus = '')
    {
        $summary = [
            'total_cluster' => 0,
            'total_hp' => 0,
            'priority_1_hp' => 0,
            'priority_2_hp' => 0,
            'rfs_hp' => 0,
            'carry_over_hp' => 0,
            'waiting_approval' => 0,
            'shifted_out_hp' => 0,
            'shifted_out_count' => 0,
            'slipped_hp' => 0,
            'slipped_count' => 0,
            'aspects' => [],
        ];
        foreach ($this->aspectFields as $key => $field) {
            $summary['aspects'][$key] = ['ready' => 0, 'not_ready' => 0];
        }

        $rows = $this->getItems($periodId, $city, $priority, $finalStatus, $regional);
        foreach ($rows as $row) {
            $hp = (float) ($row['homepass_drm_snapshot'] ?? 0);
            $summary['total_cluster']++;
            $summary['total_hp'] += $hp;
            if (($row['priority_level'] ?? '') === 'PRIORITAS 1') {
                $summary['priority_1_hp'] += $hp;
            } else {
                $summary['priority_2_hp'] += $hp;
            }
            if (($row['final_status'] ?? '') === 'RFS') {
                $summary['rfs_hp'] += $hp;
            }
            if (in_array(($row['final_status'] ?? ''), ['CARRY_OVER', 'SHIFTED_OUT'], true)) {
                $summary['carry_over_hp'] += $hp;
            }
            if (($row['final_status'] ?? '') === 'SHIFTED_OUT') {
                $summary['shifted_out_count']++;
                $summary['shifted_out_hp'] += $hp;
            }
            if ((int) ($row['baseline_week'] ?? 0) > 0 && (int) ($row['current_week'] ?? 0) > (int) ($row['baseline_week'] ?? 0)) {
                $summary['slipped_count']++;
                $summary['slipped_hp'] += $hp;
            }
            if (!empty($row['pending_request_id'])) {
                $summary['waiting_approval']++;
            }
            foreach ($this->aspectFields as $key => $field) {
                $status = strtoupper((string) ($row[$field] ?? 'NOT READY'));
                if ($status === 'READY') {
                    $summary['aspects'][$key]['ready'] += $hp;
                } else {
                    $summary['aspects'][$key]['not_ready'] += $hp;
                }
            }
        }

        return $summary;
    }

    public function getAreaSummaries($periodId, $groupBy = 'city')
    {
        $groupBy = strtolower(trim((string) $groupBy));
        $labelField = $groupBy === 'regional' ? 'regional_name' : 'city_name';
        $rows = $this->getItems($periodId);
        $summary = [];
        foreach ($rows as $row) {
            $label = strtoupper(trim((string) ($row[$labelField] ?? '')));
            if ($label === '') {
                $label = '-';
            }
            if (!isset($summary[$label])) {
                $summary[$label] = [
                    'label' => $label,
                    'province_name' => strtoupper(trim((string) ($row['province_name'] ?? ''))),
                    'regional_name' => strtoupper(trim((string) ($row['regional_name'] ?? ''))),
                    'total_cluster' => 0,
                    'total_hp' => 0,
                    'fix_count' => 0,
                    'belum_count' => 0,
                    'priority_1_count' => 0,
                    'priority_2_count' => 0,
                    'priority_1_hp' => 0,
                    'priority_2_hp' => 0,
                    'rfs_count' => 0,
                    'waiting_cr' => 0,
                    'aspects' => [],
                ];
                foreach ($this->aspectFields as $key => $field) {
                    $summary[$label]['aspects'][$key] = ['ready_hp' => 0, 'not_ready_hp' => 0];
                }
            }
            if ($summary[$label]['province_name'] === '' && !empty($row['province_name'])) {
                $summary[$label]['province_name'] = strtoupper(trim((string) $row['province_name']));
            }
            if (($summary[$label]['regional_name'] ?? '') === '' && !empty($row['regional_name'])) {
                $summary[$label]['regional_name'] = strtoupper(trim((string) $row['regional_name']));
            }
            $hp = (float) ($row['homepass_drm_snapshot'] ?? 0);
            $summary[$label]['total_cluster']++;
            $summary[$label]['total_hp'] += $hp;
            if (!empty($row['checklist_completed_at'])) {
                $summary[$label]['fix_count']++;
            } else {
                $summary[$label]['belum_count']++;
            }
            if (($row['priority_level'] ?? '') === 'PRIORITAS 1') {
                $summary[$label]['priority_1_count']++;
                $summary[$label]['priority_1_hp'] += $hp;
            } else {
                $summary[$label]['priority_2_count']++;
                $summary[$label]['priority_2_hp'] += $hp;
            }
            if (($row['final_status'] ?? '') === 'RFS') {
                $summary[$label]['rfs_count']++;
            }
            if (!empty($row['pending_request_id'])) {
                $summary[$label]['waiting_cr']++;
            }
            foreach ($this->aspectFields as $key => $field) {
                if (($row[$field] ?? 'NOT READY') === 'READY') {
                    $summary[$label]['aspects'][$key]['ready_hp'] += $hp;
                } else {
                    $summary[$label]['aspects'][$key]['not_ready_hp'] += $hp;
                }
            }
        }

        foreach ($summary as &$row) {
            $total = max(1, (int) $row['total_cluster']);
            $row['fix_percent'] = round(((int) $row['fix_count'] / $total) * 100, 1);
            $row['priority_1_percent'] = round(((int) $row['priority_1_count'] / $total) * 100, 1);
        }
        unset($row);
        uasort($summary, static function ($left, $right) {
            return strnatcasecmp((string) $left['label'], (string) $right['label']);
        });

        return array_values($summary);
    }

    public function getWeeklyTargetRealization($periodId, $city = '', $regional = '')
    {
        $period = $this->getPeriodById($periodId);
        if (empty($period)) {
            return ['weeks' => [], 'target_total' => 0, 'actual_total' => 0];
        }

        $weeks = [];
        $daysInMonth = (int) date('t', strtotime((int) $period['year_num'] . '-' . (int) $period['month_num'] . '-01'));
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', (int) $period['year_num'], (int) $period['month_num'], $day);
            $week = 'W' . date('W', strtotime($date));
            $weeks[$week] = ['week' => $week, 'target_hp' => 0, 'actual_hp' => 0];
        }

        foreach ($this->getItems($periodId, $city, '', '', $regional) as $row) {
            $hp = (float) ($row['homepass_drm_snapshot'] ?? 0);
            if (!empty($row['current_planned_rfs_date']) && $this->dateInPeriod((string) $row['current_planned_rfs_date'], (int) $period['year_num'], (int) $period['month_num'])) {
                $week = 'W' . date('W', strtotime((string) $row['current_planned_rfs_date']));
                if (!isset($weeks[$week])) {
                    $weeks[$week] = ['week' => $week, 'target_hp' => 0, 'actual_hp' => 0];
                }
                $weeks[$week]['target_hp'] += $hp;
            }
            if (!empty($row['actual_rfs_date']) && $this->dateInPeriod((string) $row['actual_rfs_date'], (int) $period['year_num'], (int) $period['month_num'])) {
                $week = 'W' . date('W', strtotime((string) $row['actual_rfs_date']));
                if (!isset($weeks[$week])) {
                    $weeks[$week] = ['week' => $week, 'target_hp' => 0, 'actual_hp' => 0];
                }
                $weeks[$week]['actual_hp'] += $hp;
            }
        }

        uksort($weeks, static function ($left, $right) {
            return (int) substr((string) $left, 1) <=> (int) substr((string) $right, 1);
        });

        $targetTotal = 0;
        $actualTotal = 0;
        foreach ($weeks as $row) {
            $targetTotal += (float) $row['target_hp'];
            $actualTotal += (float) $row['actual_hp'];
        }

        return [
            'weeks' => array_values($weeks),
            'target_total' => $targetTotal,
            'actual_total' => $actualTotal,
        ];
    }

    public function getAreaWeeklySummaries($periodId, $groupBy = 'city')
    {
        $groupBy = strtolower(trim((string) $groupBy));
        $labelField = $groupBy === 'regional' ? 'regional_name' : 'city_name';
        $periodWeekly = $this->getWeeklyTargetRealization($periodId);
        $weekKeys = array_map(static function ($row) {
            return (string) ($row['week'] ?? '');
        }, (array) ($periodWeekly['weeks'] ?? []));
        $rows = $this->getItems($periodId);
        $summary = [];
        foreach ($rows as $row) {
            $label = strtoupper(trim((string) ($row[$labelField] ?? '')));
            if ($label === '') {
                $label = '-';
            }
            if (!isset($summary[$label])) {
                $weekly = [];
                foreach ($weekKeys as $week) {
                    if ($week !== '') {
                        $weekly[$week] = ['target_hp' => 0, 'actual_hp' => 0];
                    }
                }
                $summary[$label] = [
                    'label' => $label,
                    'province_name' => strtoupper(trim((string) ($row['province_name'] ?? ''))),
                    'regional_name' => strtoupper(trim((string) ($row['regional_name'] ?? ''))),
                    'weeks' => $weekly,
                    'target_total' => 0,
                    'actual_total' => 0,
                    'remaining_total' => 0,
                    'progress_percent' => 0,
                ];
            }
            if ($summary[$label]['province_name'] === '' && !empty($row['province_name'])) {
                $summary[$label]['province_name'] = strtoupper(trim((string) $row['province_name']));
            }
            if (($summary[$label]['regional_name'] ?? '') === '' && !empty($row['regional_name'])) {
                $summary[$label]['regional_name'] = strtoupper(trim((string) $row['regional_name']));
            }
            $hp = (float) ($row['homepass_drm_snapshot'] ?? 0);
            $period = $this->getPeriodById($periodId);
            $periodYear = (int) ($period['year_num'] ?? 0);
            $periodMonth = (int) ($period['month_num'] ?? 0);
            if (!empty($row['current_planned_rfs_date']) && $this->dateInPeriod((string) $row['current_planned_rfs_date'], $periodYear, $periodMonth)) {
                $week = 'W' . date('W', strtotime((string) $row['current_planned_rfs_date']));
                if (!isset($summary[$label]['weeks'][$week])) {
                    $summary[$label]['weeks'][$week] = ['target_hp' => 0, 'actual_hp' => 0];
                }
                $summary[$label]['weeks'][$week]['target_hp'] += $hp;
                $summary[$label]['target_total'] += $hp;
            }
            if (!empty($row['actual_rfs_date']) && $this->dateInPeriod((string) $row['actual_rfs_date'], $periodYear, $periodMonth)) {
                $week = 'W' . date('W', strtotime((string) $row['actual_rfs_date']));
                if (!isset($summary[$label]['weeks'][$week])) {
                    $summary[$label]['weeks'][$week] = ['target_hp' => 0, 'actual_hp' => 0];
                }
                $summary[$label]['weeks'][$week]['actual_hp'] += $hp;
                $summary[$label]['actual_total'] += $hp;
            }
        }

        foreach ($summary as &$row) {
            uksort($row['weeks'], static function ($left, $right) {
                return (int) substr((string) $left, 1) <=> (int) substr((string) $right, 1);
            });
            $row['remaining_total'] = max(0, (float) $row['target_total'] - (float) $row['actual_total']);
            $row['progress_percent'] = (float) $row['target_total'] > 0
                ? round(((float) $row['actual_total'] / (float) $row['target_total']) * 100, 1)
                : 0;
        }
        unset($row);
        uasort($summary, static function ($left, $right) {
            return strnatcasecmp((string) $left['label'], (string) $right['label']);
        });

        return array_values($summary);
    }

    public function getCityOptions($periodId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $periodId = (int) $periodId;
        $sql = "
            SELECT DISTINCT UPPER(TRIM(c.city_name)) AS city_name
            FROM tb_myrep_cluster c
            INNER JOIN tb_myrep_rfs_readiness_item i ON i.id_myrep_cluster = c.id_myrep_cluster
            WHERE i.id_period = ?
              AND TRIM(COALESCE(c.city_name, '')) <> ''
            UNION
            SELECT DISTINCT UPPER(TRIM(c.city_name)) AS city_name
            FROM tb_myrep_cluster c
            INNER JOIN tb_myrep_drm d ON d.id_myrep_cluster = c.id_myrep_cluster
            LEFT JOIN tb_myrep_rfs_readiness_item i ON i.id_period = ? AND i.id_myrep_cluster = c.id_myrep_cluster
            WHERE i.id_item IS NULL
              AND COALESCE(d.homepass_drm, 0) > 0
              AND UPPER(COALESCE(c.status_current, '')) NOT IN ('RFS','ATP','CHECKLIST DOKUMENT','DONE')
              AND TRIM(COALESCE(c.city_name, '')) <> ''
            ORDER BY city_name ASC
        ";

        $rows = $this->db->query($sql, [$periodId, $periodId])->result_array();
        return array_values(array_map(static function ($row) {
            return (string) ($row['city_name'] ?? '');
        }, $rows));
    }

    public function getItems($periodId, $city = '', $priority = '', $finalStatus = '', $regional = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $pendingStatusSql = "'WAITING_SM','WAITING_RPM','WAITING_RFS_HO'";
        $hasBatchApproval = $this->db->table_exists('tb_myrep_batch_approval');
        $hasBoqReadiness = $this->db->table_exists('tb_myrep_boq_cluster_readiness');
        $batchSelect = $hasBatchApproval
            ? ', ba.id_batch_approval AS readiness_batch_id, ba.staging_status AS batch_approval_status'
            : ', NULL AS readiness_batch_id, NULL AS batch_approval_status';
        $boqSelect = $hasBoqReadiness
            ? ", COALESCE(br.cable_status, 'NY BOQ') AS boq_cable_status, COALESCE(br.fat_status, 'NY BOQ') AS boq_fat_status, COALESCE(br.tiang_status, 'NY BOQ') AS boq_tiang_status, COALESCE(br.progress_status, 'NY BOQ') AS boq_progress_status"
            : ", 'NY BOQ' AS boq_cable_status, 'NY BOQ' AS boq_fat_status, 'NY BOQ' AS boq_tiang_status, 'NY BOQ' AS boq_progress_status";
        $this->db
            ->select("i.*, c.cluster_name, c.cluster_code, c.regional_name, c.province_name, c.city_name, c.status_current, d.drm_date, d.nama_olt, cr.id_change_request AS pending_request_id, cr.status_request AS pending_request_status" . $batchSelect . $boqSelect, false)
            ->from('tb_myrep_rfs_readiness_item i')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = i.id_myrep_cluster', 'inner')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = i.id_myrep_cluster', 'left')
            ->join('tb_myrep_rfs_readiness_change_request cr', "cr.id_item = i.id_item AND cr.status_request IN ($pendingStatusSql)", 'left', false)
            ->where('i.id_period', (int) $periodId);
        if ($hasBatchApproval) {
            $this->db->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = i.id_myrep_cluster', 'left');
        }
        if ($hasBoqReadiness) {
            $this->db->join('tb_myrep_boq_cluster_readiness br', 'br.id_myrep_cluster = i.id_myrep_cluster', 'left');
        }

        $city = strtoupper(trim((string) $city));
        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', $city);
        }

        $regional = strtoupper(trim((string) $regional));
        if ($regional !== '') {
            $this->db->where('UPPER(c.regional_name)', $regional);
        }

        $priority = strtoupper(trim((string) $priority));
        if (in_array($priority, ['PRIORITAS 1', 'PRIORITAS 2'], true)) {
            $this->db->where('i.priority_level', $priority);
        }

        $finalStatus = strtoupper(trim((string) $finalStatus));
        if ($finalStatus !== '') {
            $this->db->where('i.final_status', $finalStatus);
        }

        return $this->db
            ->order_by('i.current_week IS NULL', 'ASC', false)
            ->order_by('i.current_week', 'ASC')
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getItemsPage($periodId, $city = '', $priority = '', $finalStatus = '', $start = 0, $length = 10, $search = '', array $order = [], $regional = '')
    {
        $rows = $this->getItems($periodId, $city, $priority, $finalStatus, $regional);
        $recordsTotal = count($rows);
        $search = strtoupper(trim((string) $search));
        if ($search !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($search) {
                $haystack = strtoupper(implode(' ', [
                    $row['cluster_name'] ?? '',
                    $row['cluster_code'] ?? '',
                    $row['city_name'] ?? '',
                    $row['province_name'] ?? '',
                    $row['regional_name'] ?? '',
                    $row['batch_approval_status'] ?? '',
                    empty($row['checklist_completed_at']) ? 'BELUM' : 'CONFIRMED',
                    $row['current_week'] ?? '',
                    $row['priority_level'] ?? '',
                    $row['final_status'] ?? '',
                    $row['material_status'] ?? '',
                    $row['olt_status'] ?? '',
                    $row['tenaga_kerja_status'] ?? '',
                    $row['operasional_status'] ?? '',
                    $row['accessories_status'] ?? '',
                    $row['boq_cable_status'] ?? '',
                    $row['boq_fat_status'] ?? '',
                    $row['boq_tiang_status'] ?? '',
                    $row['boq_progress_status'] ?? '',
                ]));
                return strpos($haystack, $search) !== false;
            }));
        }

        $recordsFiltered = count($rows);
        $columnMap = [
            1 => 'cluster_name',
            2 => 'regional_name',
            3 => 'city_name',
            4 => 'batch_approval_status',
            6 => 'homepass_drm_snapshot',
            7 => 'current_week',
            8 => 'material_status',
            9 => 'olt_status',
            10 => 'tenaga_kerja_status',
            11 => 'operasional_status',
            12 => 'accessories_status',
            13 => 'boq_cable_status',
            14 => 'boq_fat_status',
            15 => 'boq_tiang_status',
            16 => 'boq_progress_status',
            17 => 'priority_level',
            18 => 'final_status',
        ];
        $orderColumn = $columnMap[(int) ($order['column'] ?? -1)] ?? '';
        if ($orderColumn !== '') {
            $direction = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? -1 : 1;
            usort($rows, static function ($left, $right) use ($orderColumn, $direction) {
                $a = $left[$orderColumn] ?? '';
                $b = $right[$orderColumn] ?? '';
                if (is_numeric($a) && is_numeric($b)) {
                    return ((float) $a <=> (float) $b) * $direction;
                }
                return strnatcasecmp((string) $a, (string) $b) * $direction;
            });
        }

        $length = (int) $length;
        if ($length < 1) {
            $length = 10;
        }
        $pageRows = array_slice($rows, max(0, (int) $start), $length);

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $pageRows,
        ];
    }

    public function getItemById($itemId)
    {
        if ((int) $itemId <= 0) {
            return [];
        }

        $rows = $this->db
            ->select('i.*, c.cluster_name, c.city_name, c.regional_name, c.province_name')
            ->from('tb_myrep_rfs_readiness_item i')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = i.id_myrep_cluster', 'inner')
            ->where('i.id_item', (int) $itemId)
            ->limit(1)
            ->get()
            ->result_array();

        return !empty($rows) ? (array) $rows[0] : [];
    }

    public function getCandidateClusters($periodId, $city = '', $regional = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $hasRfsBridge = $this->db->table_exists('tb_rfs_myrep_cluster')
            && $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster');

        $this->db
            ->select("c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.province_name, c.city_name, c.status_current, d.drm_date, d.homepass_drm, d.nama_olt", false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_rfs_readiness_item i', 'i.id_period = ' . (int) $periodId . ' AND i.id_myrep_cluster = c.id_myrep_cluster', 'left', false)
            ->where('i.id_item IS NULL', null, false)
            ->where('COALESCE(d.homepass_drm, 0) >', 0)
            ->where("UPPER(COALESCE(c.status_current, '')) NOT IN ('RFS','ATP','CHECKLIST DOKUMENT','DONE')", null, false);

        if ($hasRfsBridge) {
            $this->db
                ->select('r.status_rfs')
                ->join('tb_rfs_myrep_cluster r', 'r.id_cluster = c.rfs_cluster_id', 'left')
                ->group_start()
                    ->where('r.id_cluster IS NULL', null, false)
                    ->or_where("UPPER(COALESCE(r.status_rfs, 'NY RFS')) <>", 'FULL RFS')
                ->group_end();
        } else {
            $this->db->select('NULL AS status_rfs', false);
        }

        $city = strtoupper(trim((string) $city));
        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', $city);
        }

        $regional = strtoupper(trim((string) $regional));
        if ($regional !== '') {
            $this->db->where('UPPER(c.regional_name)', $regional);
        }

        return $this->db
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getCandidateClustersPage($periodId, $city = '', $start = 0, $length = 10, $search = '', array $order = [], $regional = '')
    {
        $rows = $this->getCandidateClusters($periodId, $city, $regional);
        $recordsTotal = count($rows);
        $search = strtoupper(trim((string) $search));
        if ($search !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($search) {
                $haystack = strtoupper(implode(' ', [
                    $row['cluster_name'] ?? '',
                    $row['cluster_code'] ?? '',
                    $row['city_name'] ?? '',
                    $row['province_name'] ?? '',
                    $row['regional_name'] ?? '',
                    $row['homepass_drm'] ?? '',
                    $row['nama_olt'] ?? '',
                ]));
                return strpos($haystack, $search) !== false;
            }));
        }

        $recordsFiltered = count($rows);
        $columnMap = [
            1 => 'cluster_name',
            2 => 'city_name',
            3 => 'homepass_drm',
            4 => 'drm_date',
            5 => 'nama_olt',
        ];
        $orderColumn = $columnMap[(int) ($order['column'] ?? -1)] ?? '';
        if ($orderColumn !== '') {
            $direction = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? -1 : 1;
            usort($rows, static function ($left, $right) use ($orderColumn, $direction) {
                $a = $left[$orderColumn] ?? '';
                $b = $right[$orderColumn] ?? '';
                if (is_numeric($a) && is_numeric($b)) {
                    return ((float) $a <=> (float) $b) * $direction;
                }
                return strnatcasecmp((string) $a, (string) $b) * $direction;
            });
        }

        $length = (int) $length;
        if ($length < 1) {
            $length = 10;
        }

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => array_slice($rows, max(0, (int) $start), $length),
        ];
    }

    public function generateCandidates($periodId, $userId, $sourceType = 'NEW', $city = '', $regional = '', array $clusterIds = [])
    {
        $period = $this->getPeriodById($periodId);
        if (empty($period)) {
            return 0;
        }

        $clusterIds = array_values(array_unique(array_filter(array_map('intval', $clusterIds))));
        $clusterIdMap = [];
        foreach ($clusterIds as $clusterId) {
            $clusterIdMap[$clusterId] = true;
        }

        $count = 0;
        foreach ($this->getCandidateClusters($periodId, $city, $regional) as $cluster) {
            $clusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
            if (!empty($clusterIdMap) && empty($clusterIdMap[$clusterId])) {
                continue;
            }
            $priority = $this->resolvePriority(['NOT READY', 'NOT READY', 'NOT READY', 'NOT READY', 'NOT READY']);
            $insert = [
                'id_period' => (int) $periodId,
                'id_myrep_cluster' => $clusterId,
                'source_type' => $sourceType,
                'homepass_drm_snapshot' => (float) ($cluster['homepass_drm'] ?? 0),
                'material_status' => 'NOT READY',
                'olt_status' => 'NOT READY',
                'tenaga_kerja_status' => 'NOT READY',
                'operasional_status' => 'NOT READY',
                'accessories_status' => 'NOT READY',
                'priority_level' => $priority,
                'created_by' => (int) $userId,
                'updated_by' => (int) $userId,
            ];
            if ($this->db->insert('tb_myrep_rfs_readiness_item', $insert)) {
                $itemId = (int) $this->db->insert_id();
                $this->addHistory($itemId, null, 'GENERATE_CANDIDATE', '', json_encode($insert), 'Generated dari DRM done belum RFS.', $userId);
                $count++;
            }
        }

        return $count;
    }

    public function saveHoBaseline($periodId, array $payloadRows, $userId)
    {
        $period = $this->getPeriodById($periodId);
        $periodStatus = strtoupper((string) ($period['status_period'] ?? ''));
        $saved = 0;
        foreach ($payloadRows as $itemId => $payload) {
            $item = $this->getItemById($itemId);
            if (empty($item) || (int) $item['id_period'] !== (int) $periodId) {
                continue;
            }
            if ($periodStatus === 'CLOSED') {
                continue;
            }
            if (
                $periodStatus === 'LOCKED'
                && !(
                    strtoupper((string) ($item['source_type'] ?? '')) === 'LATE_ADDITION'
                    && empty($item['baseline_week'])
                )
            ) {
                continue;
            }

            $oldPayload = $this->historyPayload($item);
            $new = $this->normalizeReadinessPayload($payload, $item);
            $new['baseline_week'] = (int) ($item['baseline_week'] ?: $new['current_week']);
            $new['baseline_planned_rfs_date'] = $item['baseline_planned_rfs_date'] ?: $new['current_planned_rfs_date'];
            $new['priority_level'] = $this->resolvePriority([
                $new['material_status'],
                $new['olt_status'],
                $new['tenaga_kerja_status'],
                $new['operasional_status'],
                $new['accessories_status'],
            ]);
            $new['updated_by'] = (int) $userId;
            if (array_key_exists('checklist_is_fixed', $payload)) {
                $isFixed = (string) ($payload['checklist_is_fixed'] ?? '') === '1';
                $new['checklist_completed_at'] = $isFixed ? ($item['checklist_completed_at'] ?: date('Y-m-d H:i:s')) : null;
                $new['checklist_completed_by'] = $isFixed ? (int) ($item['checklist_completed_by'] ?: $userId) : null;
            }

            $this->db->where('id_item', (int) $itemId)->update('tb_myrep_rfs_readiness_item', $new);
            $updated = $this->getItemById($itemId);
            $this->addHistory((int) $itemId, null, 'HO_BASELINE_SAVE', json_encode($oldPayload), json_encode($this->historyPayload($updated)), 'Baseline/checklist HO disimpan.', $userId);
            $this->syncShiftedTargetPeriod($updated, $userId);
            $saved++;
        }

        return $saved;
    }

    public function createChangeRequest($itemId, array $payload, $fileData, $userId)
    {
        $item = $this->getItemById($itemId);
        if (empty($item)) {
            return ['status' => false, 'message' => 'Item readiness tidak ditemukan.'];
        }

        $new = $this->normalizeReadinessPayload($payload, $item);
        $needsApproval = $this->needsApproval($item, $new);
        if (!$needsApproval) {
            $oldPayload = $this->historyPayload($item);
            $new['priority_level'] = $this->resolvePriority([
                $new['material_status'],
                $new['olt_status'],
                $new['tenaga_kerja_status'],
                $new['operasional_status'],
                $new['accessories_status'],
            ]);
            $new['updated_by'] = (int) $userId;
            $this->db->where('id_item', (int) $itemId)->update('tb_myrep_rfs_readiness_item', $new);
            $updated = $this->getItemById($itemId);
            $this->addHistory((int) $itemId, null, 'POSITIVE_UPDATE', json_encode($oldPayload), json_encode($this->historyPayload($updated)), (string) ($payload['remark'] ?? 'Perubahan positif tanpa approval.'), $userId);
            $this->syncShiftedTargetPeriod($updated, $userId);
            return ['status' => true, 'message' => 'Perubahan positif disimpan ke history.'];
        }

        if (trim((string) ($payload['remark'] ?? '')) === '') {
            return ['status' => false, 'message' => 'Remark wajib diisi untuk perubahan yang membutuhkan approval.'];
        }
        if (empty($fileData['name'])) {
            return ['status' => false, 'message' => 'Evidence PDF wajib diupload.'];
        }

        $requestType = ((int) ($item['current_week'] ?? 0) !== (int) ($new['current_week'] ?? 0)) ? 'BOTH' : 'READINESS_CHANGE';
        $request = [
            'id_item' => (int) $itemId,
            'request_type' => $requestType,
            'old_week' => $item['current_week'] ?: null,
            'new_week' => $new['current_week'] ?: null,
            'old_planned_rfs_date' => $item['current_planned_rfs_date'] ?: null,
            'new_planned_rfs_date' => $new['current_planned_rfs_date'] ?: null,
            'old_material_status' => $item['material_status'],
            'new_material_status' => $new['material_status'],
            'old_olt_status' => $item['olt_status'],
            'new_olt_status' => $new['olt_status'],
            'old_tenaga_kerja_status' => $item['tenaga_kerja_status'],
            'new_tenaga_kerja_status' => $new['tenaga_kerja_status'],
            'old_operasional_status' => $item['operasional_status'],
            'new_operasional_status' => $new['operasional_status'],
            'old_accessories_status' => $item['accessories_status'],
            'new_accessories_status' => $new['accessories_status'],
            'reason_category' => trim((string) ($payload['reason_category'] ?? '')),
            'remark' => trim((string) ($payload['remark'] ?? '')),
            'requested_by' => (int) $userId,
        ];
        $this->db->insert('tb_myrep_rfs_readiness_change_request', $request);
        $requestId = (int) $this->db->insert_id();
        $this->addHistory((int) $itemId, $requestId, 'CHANGE_REQUEST_SUBMITTED', json_encode($this->historyPayload($item)), json_encode($request), $request['remark'], $userId);

        return ['status' => true, 'message' => 'Change request berhasil dibuat.', 'id_change_request' => $requestId];
    }

    public function saveEvidence($requestId, array $uploadResult, $userId)
    {
        if ((int) $requestId <= 0 || empty($uploadResult['file_name']) || empty($uploadResult['file_path'])) {
            return false;
        }

        return $this->db->insert('tb_myrep_rfs_readiness_evidence', [
            'id_change_request' => (int) $requestId,
            'file_name' => $uploadResult['file_name'],
            'file_path' => $uploadResult['file_path'],
            'uploaded_by' => (int) $userId,
        ]);
    }

    public function getChangeRequestById($requestId)
    {
        return (array) $this->db
            ->select('cr.*, i.id_period, i.id_myrep_cluster, c.city_name, c.cluster_name')
            ->from('tb_myrep_rfs_readiness_change_request cr')
            ->join('tb_myrep_rfs_readiness_item i', 'i.id_item = cr.id_item', 'inner')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = i.id_myrep_cluster', 'inner')
            ->where('cr.id_change_request', (int) $requestId)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getUserNotifications($userId, $limit = 10)
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_notification')) {
            return [];
        }

        return $this->db
            ->from('tb_myrep_rfs_readiness_notification')
            ->where('target_user_id', (int) $userId)
            ->order_by('status_notification = "READ"', 'ASC', false)
            ->order_by('created_at', 'DESC')
            ->limit(max(1, (int) $limit))
            ->get()
            ->result_array();
    }

    public function markNotificationRead($notificationId, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_notification')) {
            return false;
        }

        return $this->db
            ->where('id_notification', (int) $notificationId)
            ->where('target_user_id', (int) $userId)
            ->update('tb_myrep_rfs_readiness_notification', [
                'status_notification' => 'READ',
                'read_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function notifyRoleForRequest($requestId, $roleKey, $message)
    {
        $request = $this->getChangeRequestById($requestId);
        if (empty($request)) {
            return 0;
        }

        return $this->createNotificationsForCityRole(
            (string) ($request['city_name'] ?? ''),
            (string) $roleKey,
            (int) $requestId,
            (string) $message
        );
    }

    public function notifyRequester($requestId, $message)
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_notification')) {
            return 0;
        }

        $request = $this->getChangeRequestById($requestId);
        $targetUserId = (int) ($request['requested_by'] ?? 0);
        if ($targetUserId <= 0) {
            return 0;
        }

        $this->db->insert('tb_myrep_rfs_readiness_notification', [
            'id_change_request' => (int) $requestId,
            'target_user_id' => $targetUserId,
            'target_role' => 'REQUESTER',
            'message' => (string) $message,
        ]);

        return 1;
    }

    public function getPendingRequests($periodId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        return $this->db
            ->select('cr.*, c.cluster_name, c.city_name, c.regional_name, e.id_evidence, e.file_name, e.file_path')
            ->from('tb_myrep_rfs_readiness_change_request cr')
            ->join('tb_myrep_rfs_readiness_item i', 'i.id_item = cr.id_item', 'inner')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = i.id_myrep_cluster', 'inner')
            ->join('tb_myrep_rfs_readiness_evidence e', 'e.id_change_request = cr.id_change_request', 'left')
            ->where('i.id_period', (int) $periodId)
            ->where_in('cr.status_request', ['WAITING_SM', 'WAITING_RPM', 'WAITING_RFS_HO'])
            ->order_by('cr.requested_at', 'ASC')
            ->get()
            ->result_array();
    }

    public function getChecklistStatusSummary($periodId)
    {
        if (!$this->tablesReady()) {
            return ['fix' => 0, 'belum' => 0, 'total' => 0];
        }

        $row = (array) $this->db
            ->select('COUNT(*) AS total, SUM(CASE WHEN checklist_completed_at IS NOT NULL THEN 1 ELSE 0 END) AS fix_count', false)
            ->from('tb_myrep_rfs_readiness_item')
            ->where('id_period', (int) $periodId)
            ->get()
            ->row_array();
        $total = (int) ($row['total'] ?? 0);
        $fix = (int) ($row['fix_count'] ?? 0);

        return [
            'fix' => $fix,
            'belum' => max(0, $total - $fix),
            'total' => $total,
        ];
    }

    public function getItemHistory($itemId)
    {
        if ((int) $itemId <= 0 || !$this->db->table_exists('tb_myrep_rfs_readiness_history')) {
            return [];
        }

        return $this->db
            ->select('h.*, u.nama_karyawan AS created_by_name')
            ->from('tb_myrep_rfs_readiness_history h')
            ->join('tb_master_user_new u', 'u.id = h.created_by', 'left')
            ->where('h.id_item', (int) $itemId)
            ->order_by('h.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->result_array();
    }

    public function reviewChangeRequest($requestId, $decision, $note, $userId)
    {
        $request = $this->getChangeRequestById($requestId);
        if (empty($request)) {
            return false;
        }

        $decision = strtoupper(trim((string) $decision));
        $status = (string) ($request['status_request'] ?? '');
        if ($decision === 'REJECT') {
            $this->db->where('id_change_request', (int) $requestId)->update('tb_myrep_rfs_readiness_change_request', [
                'status_request' => 'REJECTED',
                'rejected_by' => (int) $userId,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_note' => $note,
            ]);
            $this->addHistory((int) $request['id_item'], (int) $requestId, 'CHANGE_REQUEST_REJECTED', '', '', $note, $userId);
            return true;
        }

        if ($status === 'WAITING_SM') {
            $this->db->where('id_change_request', (int) $requestId)->update('tb_myrep_rfs_readiness_change_request', [
                'status_request' => 'WAITING_RPM',
                'sm_approved_by' => (int) $userId,
                'sm_approved_at' => date('Y-m-d H:i:s'),
                'sm_approval_note' => $note,
            ]);
            $this->addHistory((int) $request['id_item'], (int) $requestId, 'SM_APPROVED', '', '', $note, $userId);
            return true;
        }

        if ($status === 'WAITING_RPM') {
            $this->db->where('id_change_request', (int) $requestId)->update('tb_myrep_rfs_readiness_change_request', [
                'status_request' => 'WAITING_RFS_HO',
                'rpm_approved_by' => (int) $userId,
                'rpm_approved_at' => date('Y-m-d H:i:s'),
                'rpm_approval_note' => $note,
            ]);
            $this->addHistory((int) $request['id_item'], (int) $requestId, 'RPM_APPROVED', '', '', $note, $userId);
            return true;
        }

        if ($status === 'WAITING_RFS_HO') {
            $item = $this->getItemById((int) $request['id_item']);
            $oldPayload = $this->historyPayload($item);
            $update = [
                'current_week' => $request['new_week'] ?: null,
                'current_planned_rfs_date' => $request['new_planned_rfs_date'] ?: null,
                'material_status' => $request['new_material_status'] ?: $item['material_status'],
                'olt_status' => $request['new_olt_status'] ?: $item['olt_status'],
                'tenaga_kerja_status' => $request['new_tenaga_kerja_status'] ?: $item['tenaga_kerja_status'],
                'operasional_status' => $request['new_operasional_status'] ?: $item['operasional_status'],
                'accessories_status' => $request['new_accessories_status'] ?: $item['accessories_status'],
                'updated_by' => (int) $userId,
            ];
            $update['priority_level'] = $this->resolvePriority([
                $update['material_status'],
                $update['olt_status'],
                $update['tenaga_kerja_status'],
                $update['operasional_status'],
                $update['accessories_status'],
            ]);
            $this->db->where('id_item', (int) $request['id_item'])->update('tb_myrep_rfs_readiness_item', $update);
            $this->db->where('id_change_request', (int) $requestId)->update('tb_myrep_rfs_readiness_change_request', [
                'status_request' => 'APPROVED',
                'rfs_ho_approved_by' => (int) $userId,
                'rfs_ho_approved_at' => date('Y-m-d H:i:s'),
                'rfs_ho_approval_note' => $note,
            ]);
            $updated = $this->getItemById((int) $request['id_item']);
            $this->addHistory((int) $request['id_item'], (int) $requestId, 'RFS_HO_APPROVED_APPLIED', json_encode($oldPayload), json_encode($this->historyPayload($updated)), $note, $userId);
            $this->syncShiftedTargetPeriod($updated, $userId);
            return true;
        }

        return false;
    }

    public function lockPeriod($periodId, $userId)
    {
        return $this->db
            ->where('id_period', (int) $periodId)
            ->where('status_period', 'DRAFT')
            ->update('tb_myrep_rfs_readiness_period', [
                'status_period' => 'LOCKED',
                'locked_by' => (int) $userId,
                'locked_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function unlockPeriod($periodId, $remark, $userId)
    {
        $period = $this->getPeriodById($periodId);
        if (empty($period)) {
            return ['status' => false, 'message' => 'Period tidak ditemukan.'];
        }
        if ((string) ($period['status_period'] ?? '') !== 'LOCKED') {
            return ['status' => false, 'message' => 'Hanya period LOCKED yang bisa di-unlock.'];
        }
        $remark = trim((string) $remark);
        if ($remark === '') {
            return ['status' => false, 'message' => 'Remark unlock wajib diisi.'];
        }

        $blockedCr = (array) $this->db
            ->select('COUNT(*) AS total', false)
            ->from('tb_myrep_rfs_readiness_change_request cr')
            ->join('tb_myrep_rfs_readiness_item i', 'i.id_item = cr.id_item', 'inner')
            ->where('i.id_period', (int) $periodId)
            ->where_in('cr.status_request', ['WAITING_SM', 'WAITING_RPM', 'WAITING_RFS_HO', 'APPROVED'])
            ->get()
            ->row_array();
        if ((int) ($blockedCr['total'] ?? 0) > 0) {
            return ['status' => false, 'message' => 'Period tidak bisa di-unlock karena sudah ada change request waiting/approved.'];
        }

        $blockedFinal = (array) $this->db
            ->select('COUNT(*) AS total', false)
            ->from('tb_myrep_rfs_readiness_item')
            ->where('id_period', (int) $periodId)
            ->where('final_status <>', 'OPEN')
            ->get()
            ->row_array();
        if ((int) ($blockedFinal['total'] ?? 0) > 0) {
            return ['status' => false, 'message' => 'Period tidak bisa di-unlock karena sudah ada item yang RFS/closing/carry over.'];
        }

        $this->db->trans_start();
        $this->db
            ->where('id_period', (int) $periodId)
            ->where('status_period', 'LOCKED')
            ->update('tb_myrep_rfs_readiness_period', [
                'status_period' => 'DRAFT',
                'locked_by' => null,
                'locked_at' => null,
            ]);

        $items = $this->db
            ->select('id_item')
            ->from('tb_myrep_rfs_readiness_item')
            ->where('id_period', (int) $periodId)
            ->get()
            ->result_array();
        foreach ($items as $item) {
            $this->addHistory((int) $item['id_item'], null, 'PERIOD_UNLOCK', json_encode(['status_period' => 'LOCKED']), json_encode(['status_period' => 'DRAFT']), $remark, $userId);
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return ['status' => false, 'message' => 'Unlock period gagal disimpan.'];
        }

        return ['status' => true, 'message' => 'Period berhasil di-unlock ke DRAFT.'];
    }

    public function closePeriod($periodId, array $finalStatuses, $nextYear, $nextMonth, $userId)
    {
        $period = $this->getPeriodById($periodId);
        if (empty($period) || $period['status_period'] === 'CLOSED') {
            return ['closed' => false, 'carry_over' => 0];
        }

        $this->syncActualRfsForPeriod($periodId, $userId);
        $items = $this->getItems($periodId);
        $carryOverCount = 0;
        $nextPeriodId = 0;

        foreach ($items as $item) {
            if (($item['final_status'] ?? '') === 'RFS') {
                continue;
            }
            $itemId = (int) $item['id_item'];
            $finalStatus = strtoupper(trim((string) ($finalStatuses[$itemId] ?? 'CARRY_OVER')));
            if (!in_array($finalStatus, ['CARRY_OVER', 'SHIFTED_OUT', 'IMPOSSIBLE', 'DROPPED'], true)) {
                $finalStatus = 'CARRY_OVER';
            }
            $this->db->where('id_item', $itemId)->update('tb_myrep_rfs_readiness_item', [
                'final_status' => $finalStatus,
                'updated_by' => (int) $userId,
            ]);
            $this->addHistory($itemId, null, 'PERIOD_CLOSING_' . $finalStatus, '', '', 'Closing period.', $userId);

            if (in_array($finalStatus, ['CARRY_OVER', 'SHIFTED_OUT'], true) && (int) $nextYear > 0 && (int) $nextMonth > 0) {
                if ($nextPeriodId <= 0) {
                    $nextPeriodId = $this->createPeriod((int) $nextYear, (int) $nextMonth, null, 'Auto carry over dari period ' . $periodId, $userId);
                }
                if ($nextPeriodId > 0) {
                    $this->createCarryOverItem($nextPeriodId, $item, $finalStatus === 'SHIFTED_OUT' ? 'SHIFTED_IN' : 'CARRY_OVER', $userId);
                    $carryOverCount++;
                }
            }
        }

        $this->db->where('id_period', (int) $periodId)->update('tb_myrep_rfs_readiness_period', [
            'status_period' => 'CLOSED',
            'closed_by' => (int) $userId,
            'closed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['closed' => true, 'carry_over' => $carryOverCount];
    }

    public function syncActualRfsForPeriod($periodId, $userId)
    {
        if (
            !$this->db->table_exists('tb_rfs_myrep_claim')
            || !$this->db->table_exists('tb_rfs_myrep_cluster')
            || !$this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
        ) {
            return 0;
        }

        $period = $this->getPeriodById($periodId);
        if (empty($period)) {
            return 0;
        }

        $rows = $this->db
            ->select('i.id_item, i.id_myrep_cluster, i.final_status, c.rfs_cluster_id, MIN(cl.claim_date) AS actual_rfs_date', false)
            ->from('tb_myrep_rfs_readiness_item i')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = i.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_claim cl', 'cl.cluster_id = c.rfs_cluster_id AND cl.status_claim = "APPROVED"', 'inner')
            ->where('i.id_period', (int) $periodId)
            ->where('cl.claim_year', (int) $period['year_num'])
            ->where('cl.claim_month', (int) $period['month_num'])
            ->group_by('i.id_item, i.id_myrep_cluster, i.final_status, c.rfs_cluster_id')
            ->get()
            ->result_array();

        $count = 0;
        foreach ($rows as $row) {
            $this->db->where('id_item', (int) $row['id_item'])->update('tb_myrep_rfs_readiness_item', [
                'final_status' => 'RFS',
                'actual_rfs_date' => $row['actual_rfs_date'],
                'updated_by' => (int) $userId,
            ]);
            $this->addHistory((int) $row['id_item'], null, 'SYNC_ACTUAL_RFS', '', json_encode($row), 'Actual RFS dari Monitoring RFS.', $userId);
            $this->cancelFutureCarryOver((int) $row['id_myrep_cluster'], (int) $period['year_num'], (int) $period['month_num'], $userId);
            $count++;
        }

        return $count;
    }

    public function userHasCityRole($userId, $cityName, $roleKey)
    {
        if ((string) $this->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return false;
        }
        $user = (array) $this->db
            ->select('nik')
            ->from('tb_master_user_new')
            ->where('id', (int) $userId)
            ->limit(1)
            ->get()
            ->row_array();
        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '') {
            return false;
        }
        $column = strtolower(trim((string) $roleKey));
        if (!$this->db->field_exists($column, 'tb_myrep_pic_mapping_city')) {
            return false;
        }
        $row = (array) $this->db
            ->select('1 AS hit', false)
            ->from('tb_myrep_pic_mapping_city')
            ->where('UPPER(city_name)', strtoupper(trim((string) $cityName)))
            ->where(myrep_pic_column_contains_sql($this->db, '`' . $column . '`', $nik), null, false)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row);
    }

    private function createNotificationsForCityRole($cityName, $roleKey, $requestId, $message)
    {
        if (!$this->db->table_exists('tb_myrep_rfs_readiness_notification')) {
            return 0;
        }

        $userIds = $this->getCityRoleUserIds($cityName, $roleKey);
        $count = 0;
        foreach ($userIds as $userId) {
            if ((int) $userId <= 0) {
                continue;
            }
            $exists = (array) $this->db
                ->select('id_notification')
                ->from('tb_myrep_rfs_readiness_notification')
                ->where('id_change_request', (int) $requestId)
                ->where('target_user_id', (int) $userId)
                ->where('target_role', strtoupper((string) $roleKey))
                ->where('status_notification', 'UNREAD')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($exists)) {
                continue;
            }

            $this->db->insert('tb_myrep_rfs_readiness_notification', [
                'id_change_request' => (int) $requestId,
                'target_user_id' => (int) $userId,
                'target_role' => strtoupper((string) $roleKey),
                'message' => (string) $message,
            ]);
            $count++;
        }

        return $count;
    }

    private function getCityRoleUserIds($cityName, $roleKey)
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city') || !$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $column = strtolower(trim((string) $roleKey));
        if (!$this->db->field_exists($column, 'tb_myrep_pic_mapping_city')) {
            return [];
        }

        $mapping = (array) $this->db
            ->select($column)
            ->from('tb_myrep_pic_mapping_city')
            ->where('UPPER(city_name)', strtoupper(trim((string) $cityName)))
            ->limit(1)
            ->get()
            ->row_array();
        $niks = myrep_pic_nik_list($mapping[$column] ?? '');
        if (empty($niks)) {
            return [];
        }

        $rows = $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where_in('nik', $niks)
            ->get()
            ->result_array();

        return array_values(array_unique(array_filter(array_map(static function ($row) {
            return (int) ($row['id'] ?? 0);
        }, $rows))));
    }

    private function createCarryOverItem($nextPeriodId, array $sourceItem, $sourceType, $userId)
    {
        $exists = (array) $this->db
            ->from('tb_myrep_rfs_readiness_item')
            ->where('id_period', (int) $nextPeriodId)
            ->where('id_myrep_cluster', (int) $sourceItem['id_myrep_cluster'])
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($exists)) {
            return 0;
        }

        $insert = [
            'id_period' => (int) $nextPeriodId,
            'id_myrep_cluster' => (int) $sourceItem['id_myrep_cluster'],
            'source_type' => $sourceType,
            'source_item_id' => (int) $sourceItem['id_item'],
            'homepass_drm_snapshot' => (float) $sourceItem['homepass_drm_snapshot'],
            'baseline_week' => $sourceItem['current_week'] ?: null,
            'baseline_planned_rfs_date' => $sourceItem['current_planned_rfs_date'] ?: null,
            'current_week' => $sourceItem['current_week'] ?: null,
            'current_planned_rfs_date' => $sourceItem['current_planned_rfs_date'] ?: null,
            'material_status' => $sourceItem['material_status'],
            'olt_status' => $sourceItem['olt_status'],
            'tenaga_kerja_status' => $sourceItem['tenaga_kerja_status'],
            'operasional_status' => $sourceItem['operasional_status'],
            'accessories_status' => $sourceItem['accessories_status'],
            'morep_owner' => $sourceItem['morep_owner'] ?? '',
            'tkm_owner' => $sourceItem['tkm_owner'] ?? '',
            'priority_level' => $sourceItem['priority_level'],
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ];
        $this->db->insert('tb_myrep_rfs_readiness_item', $insert);
        $itemId = (int) $this->db->insert_id();
        $this->addHistory($itemId, null, 'CARRY_OVER_CREATED', '', json_encode($insert), 'Carry over dari item ' . (int) $sourceItem['id_item'], $userId);
        return $itemId;
    }

    private function syncShiftedTargetPeriod(array $sourceItem, $userId)
    {
        if (empty($sourceItem['current_planned_rfs_date']) || empty($sourceItem['id_period'])) {
            return 0;
        }
        if (($sourceItem['final_status'] ?? '') === 'RFS') {
            return 0;
        }

        $sourcePeriod = $this->getPeriodById((int) $sourceItem['id_period']);
        if (empty($sourcePeriod)) {
            return 0;
        }

        $targetYear = (int) date('Y', strtotime((string) $sourceItem['current_planned_rfs_date']));
        $targetMonth = (int) date('n', strtotime((string) $sourceItem['current_planned_rfs_date']));
        $sourceYear = (int) ($sourcePeriod['year_num'] ?? 0);
        $sourceMonth = (int) ($sourcePeriod['month_num'] ?? 0);
        if ($targetYear === $sourceYear && $targetMonth === $sourceMonth) {
            if (($sourceItem['final_status'] ?? '') === 'SHIFTED_OUT') {
                $this->db->where('id_item', (int) $sourceItem['id_item'])->update('tb_myrep_rfs_readiness_item', [
                    'final_status' => 'OPEN',
                    'updated_by' => (int) $userId,
                ]);
                $this->addHistory((int) $sourceItem['id_item'], null, 'AUTO_REOPEN_IN_PERIOD', '', '', 'Target kembali ke period asal.', $userId);
            }
            return 0;
        }

        $targetPeriod = $this->getPeriodByYearMonth($targetYear, $targetMonth);
        $targetPeriodId = (int) ($targetPeriod['id_period'] ?? 0);
        if ($targetPeriodId <= 0) {
            $targetPeriodId = $this->createPeriod($targetYear, $targetMonth, null, 'Auto shifted target dari period ' . (int) $sourceItem['id_period'], $userId);
        }
        if ($targetPeriodId <= 0) {
            return 0;
        }

        if (($sourceItem['final_status'] ?? '') === 'OPEN') {
            $this->db->where('id_item', (int) $sourceItem['id_item'])->update('tb_myrep_rfs_readiness_item', [
                'final_status' => 'SHIFTED_OUT',
                'updated_by' => (int) $userId,
            ]);
            $this->addHistory((int) $sourceItem['id_item'], null, 'AUTO_SHIFTED_OUT', '', '', 'Target pindah ke period ' . $targetYear . '-' . str_pad((string) $targetMonth, 2, '0', STR_PAD_LEFT), $userId);
        }

        return $this->createShiftedTargetItem($targetPeriodId, $sourceItem, $userId);
    }

    private function createShiftedTargetItem($targetPeriodId, array $sourceItem, $userId)
    {
        $exists = (array) $this->db
            ->from('tb_myrep_rfs_readiness_item')
            ->where('id_period', (int) $targetPeriodId)
            ->where('id_myrep_cluster', (int) $sourceItem['id_myrep_cluster'])
            ->limit(1)
            ->get()
            ->row_array();

        $payload = [
            'current_week' => $sourceItem['current_week'] ?: null,
            'current_planned_rfs_date' => $sourceItem['current_planned_rfs_date'] ?: null,
            'material_status' => $sourceItem['material_status'],
            'olt_status' => $sourceItem['olt_status'],
            'tenaga_kerja_status' => $sourceItem['tenaga_kerja_status'],
            'operasional_status' => $sourceItem['operasional_status'],
            'accessories_status' => $sourceItem['accessories_status'],
            'morep_owner' => $sourceItem['morep_owner'] ?? '',
            'tkm_owner' => $sourceItem['tkm_owner'] ?? '',
            'priority_level' => $sourceItem['priority_level'],
            'updated_by' => (int) $userId,
        ];

        if (!empty($exists)) {
            $this->db->where('id_item', (int) $exists['id_item'])->update('tb_myrep_rfs_readiness_item', $payload);
            $this->addHistory((int) $exists['id_item'], null, 'SHIFTED_IN_UPDATED', '', json_encode($this->historyPayload(array_merge($exists, $payload))), 'Update target shift dari item ' . (int) $sourceItem['id_item'], $userId);
            return (int) $exists['id_item'];
        }

        $insert = array_merge($payload, [
            'id_period' => (int) $targetPeriodId,
            'id_myrep_cluster' => (int) $sourceItem['id_myrep_cluster'],
            'source_type' => 'SHIFTED_IN',
            'source_item_id' => (int) $sourceItem['id_item'],
            'homepass_drm_snapshot' => (float) $sourceItem['homepass_drm_snapshot'],
            'baseline_week' => $sourceItem['current_week'] ?: null,
            'baseline_planned_rfs_date' => $sourceItem['current_planned_rfs_date'] ?: null,
            'created_by' => (int) $userId,
        ]);
        $this->db->insert('tb_myrep_rfs_readiness_item', $insert);
        $itemId = (int) $this->db->insert_id();
        $this->addHistory($itemId, null, 'SHIFTED_IN_CREATED', '', json_encode($this->historyPayload($insert)), 'Target shift dari item ' . (int) $sourceItem['id_item'], $userId);
        return $itemId;
    }

    private function cancelFutureCarryOver($clusterId, $year, $month, $userId)
    {
        $rows = $this->db
            ->select('i.id_item')
            ->from('tb_myrep_rfs_readiness_item i')
            ->join('tb_myrep_rfs_readiness_period p', 'p.id_period = i.id_period', 'inner')
            ->where('i.id_myrep_cluster', (int) $clusterId)
            ->where("(p.year_num > " . (int) $year . " OR (p.year_num = " . (int) $year . " AND p.month_num > " . (int) $month . "))", null, false)
            ->where_in('i.final_status', ['OPEN', 'CARRY_OVER'])
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            $this->db->where('id_item', (int) $row['id_item'])->update('tb_myrep_rfs_readiness_item', [
                'final_status' => 'CANCELLED_BY_LATE_RFS',
                'updated_by' => (int) $userId,
            ]);
            $this->addHistory((int) $row['id_item'], null, 'CANCELLED_BY_LATE_RFS', '', '', 'Actual RFS periode sebelumnya masuk terlambat.', $userId);
        }
    }

    private function normalizeReadinessPayload(array $payload, array $item)
    {
        $plannedDate = $this->normalizeDate($payload['planned_rfs_date'] ?? ($item['current_planned_rfs_date'] ?? null));
        $week = (int) ($payload['target_week'] ?? 0);
        if ($plannedDate !== '') {
            $week = (int) date('W', strtotime($plannedDate));
        }
        if ($week < 1 || $week > 53) {
            $week = (int) ($item['current_week'] ?? 0);
        }

        $data = [
            'current_week' => $week > 0 ? $week : null,
            'current_planned_rfs_date' => $plannedDate !== '' ? $plannedDate : null,
            'remark' => array_key_exists('remark', $payload) ? trim((string) $payload['remark']) : ($item['remark'] ?? null),
        ];
        foreach ($this->aspectFields as $key => $field) {
            $data[$field] = $this->normalizeStatus($payload[$key . '_status'] ?? ($item[$field] ?? 'NOT READY'));
        }
        $data['morep_owner'] = array_key_exists('morep_owner', $payload)
            ? trim((string) $payload['morep_owner'])
            : ($item['morep_owner'] ?? null);
        $data['tkm_owner'] = array_key_exists('tkm_owner', $payload)
            ? trim((string) $payload['tkm_owner'])
            : ($item['tkm_owner'] ?? null);

        return $data;
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    private function dateInPeriod($date, $year, $month)
    {
        $timestamp = strtotime((string) $date);
        if (!$timestamp || (int) $year <= 0 || (int) $month <= 0) {
            return false;
        }
        return (int) date('Y', $timestamp) === (int) $year
            && (int) date('n', $timestamp) === (int) $month;
    }

    private function normalizeStatus($value)
    {
        $value = strtoupper(trim((string) $value));
        return in_array($value, ['READY', 'NOT READY'], true) ? $value : 'NOT READY';
    }

    private function resolvePriority(array $statuses)
    {
        foreach ($statuses as $status) {
            if (strtoupper(trim((string) $status)) !== 'READY') {
                return 'PRIORITAS 2';
            }
        }

        return 'PRIORITAS 1';
    }

    private function needsApproval(array $item, array $new)
    {
        foreach ($this->aspectFields as $field) {
            $column = is_string($field) ? $field : '';
            if (($item[$column] ?? 'NOT READY') === 'READY' && ($new[$column] ?? 'NOT READY') !== 'READY') {
                return true;
            }
        }
        $oldWeek = (int) ($item['current_week'] ?? 0);
        $newWeek = (int) ($new['current_week'] ?? 0);
        return $oldWeek > 0 && $newWeek > $oldWeek;
    }

    private function historyPayload(array $item)
    {
        $payload = [
            'current_week' => $item['current_week'] ?? null,
            'current_planned_rfs_date' => $item['current_planned_rfs_date'] ?? null,
            'priority_level' => $item['priority_level'] ?? null,
        ];
        foreach ($this->aspectFields as $key => $field) {
            $payload[$field] = $item[$field] ?? null;
        }
        $payload['morep_owner'] = $item['morep_owner'] ?? null;
        $payload['tkm_owner'] = $item['tkm_owner'] ?? null;
        return $payload;
    }

    private function addHistory($itemId, $requestId, $eventType, $oldPayload, $newPayload, $remark, $userId)
    {
        return $this->db->insert('tb_myrep_rfs_readiness_history', [
            'id_item' => (int) $itemId,
            'id_change_request' => $requestId ? (int) $requestId : null,
            'event_type' => $eventType,
            'old_payload' => $oldPayload !== '' ? $oldPayload : null,
            'new_payload' => $newPayload !== '' ? $newPayload : null,
            'remark' => $remark !== '' ? $remark : null,
            'created_by' => (int) $userId,
        ]);
    }
}
