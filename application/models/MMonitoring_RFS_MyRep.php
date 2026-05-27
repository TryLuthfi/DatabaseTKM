<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMonitoring_RFS_MyRep extends CI_Model
{
    private $rfsReadyStatuses = ['DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'];

    public function claimSupportsStatusRfs()
    {
        return $this->db->field_exists('status_rfs', 'tb_rfs_myrep_claim');
    }

    public function claimSupportsRpmApproval()
    {
        return $this->db->field_exists('rpm_approval_status', 'tb_rfs_myrep_claim');
    }

    public function getAnnualSummary($year, $startMonth = 1, $endMonth = 12, $city = '')
    {
        $params = [$year, $startMonth, $endMonth];

        $sql = "SELECT
                    COALESCE(SUM(target_myrep), 0) AS target_myrep,
                    COALESCE(SUM(realization_myrep), 0) AS realization_myrep,
                    COALESCE(SUM(target_rkap), 0) AS target_tkm,
                    COALESCE((
                        SELECT SUM(c.claim_qty)
                        FROM tb_rfs_myrep_claim c
                        INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
                        INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = cl.id_target
                        WHERE c.claim_year = ?
                        AND c.claim_month BETWEEN ? AND ?
                        AND c.status_claim = 'APPROVED'
                ";

        if ($city !== '') {
            $sql .= " AND UPPER(mt.city_name) = ? ";
            $params[] = $city;
        }

        $sql .= "), 0) AS realization_tkm
                FROM tb_rfs_myrep_monthly_target
                WHERE year_num = ? AND month_num BETWEEN ? AND ?";

        $params[] = $year;
        $params[] = $startMonth;
        $params[] = $endMonth;

        if ($city !== '') {
            $sql .= " AND UPPER(city_name) = ? ";
            $params[] = $city;
        }

        $row = $this->db->query($sql, $params)->row_array();

        if (!$row) {
            $row = [
                'target_myrep' => 0,
                'realization_myrep' => 0,
                'target_tkm' => 0,
                'realization_tkm' => 0
            ];
        }

        $row['pct_myrep'] = $this->calculatePercent($row['realization_myrep'], $row['target_myrep']);
        $row['pct_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['target_tkm']);
        $row['myrep_vs_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['realization_myrep']);

        return $row;
    }

    public function getMonthlySummary($year, $startMonth, $endMonth, $city = '')
    {
        $this->db
            ->select("city_name,
                MAX(COALESCE(NULLIF(regional_name, ''), '-')) AS regional_name,
                MAX(COALESCE(NULLIF(sm, ''), '-')) AS sm,
                MAX(COALESCE(NULLIF(team_name, ''), '-')) AS team_name,
                SUM(target_myrep) AS target_myrep,
                SUM(target_rkap) AS target_rkap,
                SUM(realization_myrep) AS realization_myrep", false)
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->where('month_num >=', $startMonth)
            ->where('month_num <=', $endMonth);

        if ($city !== '') {
            $this->db->where('UPPER(city_name)', $city);
        }

        $targets = $this->db
            ->group_by('city_name')
            ->get()
            ->result_array();

        $claimSql = "SELECT mt.city_name, COALESCE(SUM(c.claim_qty), 0) AS realization_tkm
             FROM tb_rfs_myrep_claim c
             INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = cl.id_target
             WHERE c.claim_year = ? AND c.claim_month BETWEEN ? AND ? AND c.status_claim = 'APPROVED'";
        $claimParams = [$year, $startMonth, $endMonth];

        if ($city !== '') {
            $claimSql .= " AND UPPER(mt.city_name) = ? ";
            $claimParams[] = $city;
        }

        $claimSql .= " GROUP BY mt.city_name";

        $claims = $this->db->query(
            $claimSql,
            $claimParams
        )->result_array();

        $rows = [];
        foreach ($targets as $target) {
            $cityKey = strtoupper((string) $target['city_name']);
            $rows[$cityKey] = [
                'city_name' => $target['city_name'],
                'regional_name' => $target['regional_name'] ?? '-',
                'sm' => $target['sm'] ?? '-',
                'team_name' => $target['team_name'] ?? '-',
                'target_myrep' => (float) $target['target_myrep'],
                'realization_myrep' => (float) $target['realization_myrep'],
                'target_tkm' => (float) $target['target_rkap'],
                'realization_tkm' => 0
            ];
        }

        foreach ($claims as $claim) {
            $cityKey = strtoupper((string) $claim['city_name']);
            if (!isset($rows[$cityKey])) {
                $rows[$cityKey] = [
                    'city_name' => $claim['city_name'],
                    'regional_name' => '-',
                    'sm' => '-',
                    'team_name' => '-',
                    'target_myrep' => 0,
                    'realization_myrep' => 0,
                    'target_tkm' => 0,
                    'realization_tkm' => 0
                ];
            }

            $rows[$cityKey]['realization_tkm'] = (float) $claim['realization_tkm'];
        }

        foreach ($rows as &$row) {
            $row['pct_myrep'] = $this->calculatePercent($row['realization_myrep'], $row['target_myrep']);
            $row['pct_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['target_tkm']);
            $row['myrep_vs_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['realization_myrep']);
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return strcmp($a['city_name'], $b['city_name']);
        });

        return $rows;
    }

    public function getAnnualCitySummary($year, $startMonth = 1, $endMonth = 12, $city = '')
    {
        $this->db
            ->select("city_name,
                MAX(COALESCE(NULLIF(regional_name, ''), '-')) AS regional_name,
                MAX(COALESCE(NULLIF(sm, ''), '-')) AS sm,
                MAX(COALESCE(NULLIF(team_name, ''), '-')) AS team_name,
                SUM(target_myrep) AS target_myrep,
                SUM(target_rkap) AS target_tkm,
                SUM(realization_myrep) AS realization_myrep", false)
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->where('month_num >=', $startMonth)
            ->where('month_num <=', $endMonth);

        if ($city !== '') {
            $this->db->where('UPPER(city_name)', $city);
        }

        $targets = $this->db->group_by('city_name')->get()->result_array();

        $claimSql = "SELECT mt.city_name, COALESCE(SUM(c.claim_qty), 0) AS realization_tkm
             FROM tb_rfs_myrep_claim c
             INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = cl.id_target
             WHERE c.claim_year = ? AND c.claim_month BETWEEN ? AND ? AND c.status_claim = 'APPROVED'";
        $claimParams = [$year, $startMonth, $endMonth];

        if ($city !== '') {
            $claimSql .= " AND UPPER(mt.city_name) = ? ";
            $claimParams[] = $city;
        }

        $claimSql .= " GROUP BY mt.city_name";

        $claims = $this->db->query($claimSql, $claimParams)->result_array();

        $rows = [];
        foreach ($targets as $target) {
            $cityKey = strtoupper((string) $target['city_name']);
            $rows[$cityKey] = [
                'city_name' => $target['city_name'],
                'regional_name' => $target['regional_name'] ?? '-',
                'sm' => $target['sm'] ?? '-',
                'team_name' => $target['team_name'] ?? '-',
                'target_myrep' => (float) $target['target_myrep'],
                'realization_myrep' => (float) $target['realization_myrep'],
                'target_tkm' => (float) $target['target_tkm'],
                'realization_tkm' => 0
            ];
        }

        foreach ($claims as $claim) {
            $cityKey = strtoupper((string) $claim['city_name']);
            if (!isset($rows[$cityKey])) {
                $rows[$cityKey] = [
                    'city_name' => $claim['city_name'],
                    'regional_name' => '-',
                    'sm' => '-',
                    'team_name' => '-',
                    'target_myrep' => 0,
                    'realization_myrep' => 0,
                    'target_tkm' => 0,
                    'realization_tkm' => 0
                ];
            }

            $rows[$cityKey]['realization_tkm'] = (float) $claim['realization_tkm'];
        }

        foreach ($rows as &$row) {
            $row['pct_myrep'] = $this->calculatePercent($row['realization_myrep'], $row['target_myrep']);
            $row['pct_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['target_tkm']);
            $row['myrep_vs_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['realization_myrep']);
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return strcmp($a['city_name'], $b['city_name']);
        });

        return $rows;
    }

    public function getMonthColumnsInRange($year, $startMonth, $endMonth)
    {
        $monthNames = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER'
        ];

        $columns = [];
        for ($monthNumber = $startMonth; $monthNumber <= $endMonth; $monthNumber++) {
            $columns[] = [
                'year_num' => (int) $year,
                'month_num' => $monthNumber,
                'label' => $monthNames[$monthNumber]
            ];
        }

        return $columns;
    }

    public function getGroupedKpiSummary($year, $startMonth, $endMonth, $groupField, $city = '')
    {
        $allowedFields = ['regional_name', 'sm', 'team_name'];
        if (!in_array($groupField, $allowedFields, true)) {
            return [];
        }

        $fallbackLabel = $groupField === 'team_name' ? 'BELUM ADA TEAM' : 'BELUM DISET';
        $groupExpression = "COALESCE(NULLIF(TRIM($groupField), ''), " . $this->db->escape($fallbackLabel) . ")";
        $rpmSelect = $groupField === 'regional_name'
            ? ", COALESCE(NULLIF(GROUP_CONCAT(DISTINCT NULLIF(TRIM(rpm), '') ORDER BY rpm SEPARATOR ', '), ''), '-') AS rpm_names"
            : ", '-' AS rpm_names";

        $this->db
            ->select("$groupExpression AS group_name,
                SUM(target_myrep) AS target_myrep,
                SUM(target_rkap) AS target_tkm,
                SUM(realization_myrep) AS realization_myrep
                $rpmSelect", false)
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->where('month_num >=', $startMonth)
            ->where('month_num <=', $endMonth);

        if ($city !== '') {
            $this->db->where('UPPER(city_name)', $city);
        }

        $targets = $this->db
            ->group_by($groupExpression, false)
            ->get()
            ->result_array();

        $claimSql = "SELECT $groupExpression AS group_name, COALESCE(SUM(c.claim_qty), 0) AS realization_tkm
             FROM tb_rfs_myrep_claim c
             INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = cl.id_target
             WHERE c.claim_year = ? AND c.claim_month BETWEEN ? AND ? AND c.status_claim = 'APPROVED'";
        $claimParams = [$year, $startMonth, $endMonth];

        if ($city !== '') {
            $claimSql .= " AND UPPER(mt.city_name) = ? ";
            $claimParams[] = $city;
        }

        $claimSql .= " GROUP BY $groupExpression";

        $claims = $this->db->query($claimSql, $claimParams)->result_array();

        $rows = [];
        foreach ($targets as $target) {
            $groupKey = strtoupper((string) $target['group_name']);
            $rows[$groupKey] = [
                'group_name' => $target['group_name'],
                'rpm_names' => $target['rpm_names'] ?? '-',
                'target_myrep' => (float) $target['target_myrep'],
                'realization_myrep' => (float) $target['realization_myrep'],
                'target_tkm' => (float) $target['target_tkm'],
                'realization_tkm' => 0
            ];
        }

        foreach ($claims as $claim) {
            $groupKey = strtoupper((string) $claim['group_name']);
            if (!isset($rows[$groupKey])) {
                $rows[$groupKey] = [
                    'group_name' => $claim['group_name'],
                    'rpm_names' => '-',
                    'target_myrep' => 0,
                    'realization_myrep' => 0,
                    'target_tkm' => 0,
                    'realization_tkm' => 0
                ];
            }

            $rows[$groupKey]['realization_tkm'] = (float) $claim['realization_tkm'];
        }

        $totalRealizationTkm = 0;
        foreach ($rows as $row) {
            $totalRealizationTkm += (float) ($row['realization_tkm'] ?? 0);
        }

        foreach ($rows as &$row) {
            $row['pct_myrep'] = $this->calculatePercent($row['realization_myrep'], $row['target_myrep']);
            $row['pct_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['target_tkm']);
            $row['bobot_realisasi'] = $this->calculatePercent($row['realization_tkm'], $totalRealizationTkm);
            $row['myrep_vs_tkm'] = $this->calculatePercent($row['realization_tkm'], $row['realization_myrep']);
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return strcmp((string) $a['group_name'], (string) $b['group_name']);
        });

        return $rows;
    }

    public function getThreeMonthSummary($year, $startMonth, $endMonth, $city = '')
    {
        $columns = $this->getMonthColumnsInRange($year, $startMonth, $endMonth);
        $filterCity = $city;
        $cities = $filterCity !== '' ? [$filterCity] : $this->getCityOptions();
        $result = [];

        foreach ($cities as $cityName) {
            $result[$cityName] = [
                'city_name' => $cityName,
                'rkap' => [],
                'realistis' => [],
                'pencapaian' => []
            ];
        }

        foreach ($columns as $column) {
            $monthlyTargetRows = $this->db
                ->select('city_name, target_rkap')
                ->from('tb_rfs_myrep_monthly_target')
                ->where('year_num', $column['year_num'])
                ->where('month_num', $column['month_num']);

            if ($filterCity !== '') {
                $this->db->where('UPPER(city_name)', $filterCity);
            }

            $monthlyTargetRows = $this->db->get()->result_array();

            foreach ($monthlyTargetRows as $row) {
                $city = strtoupper((string) $row['city_name']);
                if (!isset($result[$city])) {
                    $result[$city] = [
                        'city_name' => $city,
                        'rkap' => [],
                        'realistis' => [],
                        'pencapaian' => []
                    ];
                }

                $result[$city]['rkap'][$column['month_num']] = (float) $row['target_rkap'];
            }

            $planSql = "SELECT mt.city_name, COALESCE(SUM(p.optimistic_target), 0) AS realistis
                 FROM tb_rfs_myrep_cluster_plan p
                 INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = p.cluster_id
                 INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
                 WHERE p.year_num = ? AND p.month_num = ?";
            $planParams = [$column['year_num'], $column['month_num']];

            if ($filterCity !== '') {
                $planSql .= " AND UPPER(mt.city_name) = ? ";
                $planParams[] = $filterCity;
            }

            $planSql .= " GROUP BY mt.city_name";

            $planRows = $this->db->query(
                $planSql,
                $planParams
            )->result_array();

            foreach ($planRows as $row) {
                $city = strtoupper((string) $row['city_name']);
                if (!isset($result[$city])) {
                    $result[$city] = [
                        'city_name' => $city,
                        'rkap' => [],
                        'realistis' => [],
                        'pencapaian' => []
                    ];
                }

                $result[$city]['realistis'][$column['month_num']] = (float) $row['realistis'];
            }

            $claimSql = "SELECT mt.city_name, COALESCE(SUM(cl.claim_qty), 0) AS pencapaian
                 FROM tb_rfs_myrep_claim cl
                 INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = cl.cluster_id
                 INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
                 WHERE cl.claim_year = ? AND cl.claim_month = ? AND cl.status_claim = 'APPROVED'";
            $claimParams = [$column['year_num'], $column['month_num']];

            if ($filterCity !== '') {
                $claimSql .= " AND UPPER(mt.city_name) = ? ";
                $claimParams[] = $filterCity;
            }

            $claimSql .= " GROUP BY mt.city_name";

            $claimRows = $this->db->query(
                $claimSql,
                $claimParams
            )->result_array();

            foreach ($claimRows as $row) {
                $city = strtoupper((string) $row['city_name']);
                if (!isset($result[$city])) {
                    $result[$city] = [
                        'city_name' => $city,
                        'rkap' => [],
                        'realistis' => [],
                        'pencapaian' => []
                    ];
                }

                $result[$city]['pencapaian'][$column['month_num']] = (float) $row['pencapaian'];
            }
        }

        $final = array_values($result);
        usort($final, function ($a, $b) {
            return strcmp($a['city_name'], $b['city_name']);
        });

        return $final;
    }

    public function getClustersWithPlan($year, $startMonth, $endMonth, $city = '')
    {
        if (!$this->hasMyrepClusterTables() || !$this->hasMyrepDrmDocumentTables()) {
            return [];
        }

        $sql = "SELECT
                c.*,
                mc.id_myrep_cluster,
                mc.status_current AS myrep_status_current,
                md.homepass_drm AS homepass_drm_latest,
                COALESCE(NULLIF(md.homepass_drm, 0), c.homepass) AS homepass_drm_effective,
                mt.id_target,
                mt.year_num,
                mt.month_num,
                mt.regional_name,
                mt.province_name,
                mt.city_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                COALESCE((
                    SELECT COUNT(1)
                    FROM tb_rfs_myrep_claim cl_pending
                    WHERE cl_pending.cluster_id = c.id_cluster
                    AND cl_pending.status_claim IN ('WAITING APPROVAL RPM', 'WAITING APPROVAL HO')
                ), 0) AS pending_claim_count,
                COALESCE(p.optimistic_target, 0) AS optimistic_target,
                COALESCE((
                    SELECT SUM(claim_qty)
                    FROM tb_rfs_myrep_claim cl
                    WHERE cl.cluster_id = c.id_cluster
                    AND cl.status_claim IN ('WAITING APPROVAL RPM', 'WAITING APPROVAL HO', 'APPROVED')
                ), 0) AS claimed_qty
             FROM tb_rfs_myrep_cluster c
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
             INNER JOIN tb_myrep_cluster mc ON mc.rfs_cluster_id = c.id_cluster
             INNER JOIN (
                SELECT d.id_myrep_cluster, d.homepass_drm
                FROM tb_myrep_drm d
                INNER JOIN (
                    SELECT id_myrep_cluster, MAX(id_drm) AS latest_id_drm
                    FROM tb_myrep_drm
                    GROUP BY id_myrep_cluster
                ) latest_drm ON latest_drm.latest_id_drm = d.id_drm
             ) md ON md.id_myrep_cluster = mc.id_myrep_cluster
             LEFT JOIN tb_rfs_myrep_cluster_plan p
               ON p.cluster_id = c.id_cluster
               AND p.year_num = ?
               AND p.month_num = ?
             WHERE UPPER(COALESCE(mc.status_current, '')) IN ('DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE')
               ";

        $params = [$year, $endMonth];

        if ($city !== '') {
            $sql .= " AND UPPER(mt.city_name) = ? ";
            $params[] = $city;
        }

        $sql .= " ORDER BY mt.city_name ASC, c.cluster_name ASC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function syncMyrepCompatibilityBridge($year, $month, $city = '')
    {
        $year = (int) $year;
        $month = (int) $month;

        if ($year <= 0) {
            $year = (int) date('Y');
        }

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $this->syncEligibleMyrepClustersToRfs($year, $month, $city);
    }

    public function getClaims($year, $startMonth, $endMonth, $city = '', $claimStartDate = '', $claimEndDate = '')
    {
        $sql = "SELECT
                cl.*,
                mt.city_name,
                mt.regional_name,
                mt.team_name,
                mt.id_target,
                c.cluster_name,
                c.homepass,
                mt.rpm,
                mt.sm,
                mt.spv,
                su.nama_karyawan AS submitted_name,
                au.nama_karyawan AS approved_name,
                ru.nama_karyawan AS rpm_approved_name
             FROM tb_rfs_myrep_claim cl
             INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = cl.cluster_id
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
             LEFT JOIN tb_master_user_new su ON su.id = cl.submitted_by
             LEFT JOIN tb_master_user_new au ON au.id = cl.approved_by
             LEFT JOIN tb_master_user_new ru ON ru.id = cl.rpm_approved_by
             WHERE 1 = 1";

        $params = [];

        $claimStartDate = $this->normalizeDateFilter($claimStartDate);
        $claimEndDate = $this->normalizeDateFilter($claimEndDate);
        if ($claimStartDate !== '' && $claimEndDate === '') {
            $claimEndDate = $claimStartDate;
        }
        if ($claimEndDate !== '' && $claimStartDate === '') {
            $claimStartDate = $claimEndDate;
        }
        if ($claimStartDate !== '' && $claimEndDate !== '') {
            if (strtotime($claimStartDate) > strtotime($claimEndDate)) {
                $tempDate = $claimStartDate;
                $claimStartDate = $claimEndDate;
                $claimEndDate = $tempDate;
            }
            $sql .= " AND cl.claim_date BETWEEN ? AND ?";
            $params[] = $claimStartDate;
            $params[] = $claimEndDate;
        } else {
            $sql .= " AND cl.claim_year = ? AND cl.claim_month BETWEEN ? AND ?";
            $params[] = $year;
            $params[] = $startMonth;
            $params[] = $endMonth;
        }

        if ($city !== '') {
            $sql .= " AND UPPER(mt.city_name) = ? ";
            $params[] = $city;
        }

        $sql .= " ORDER BY cl.claim_date DESC, cl.id_claim DESC";

        return $this->db->query($sql, $params)->result_array();
    }

    private function normalizeDateFilter($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return '';
        }

        return $value;
    }

    public function getCityOptions()
    {
        $cities = [];

        $targetRows = $this->db->distinct()->select('city_name')->from('tb_rfs_myrep_monthly_target')->order_by('city_name', 'ASC')->get()->result_array();
        foreach ($targetRows as $row) {
            $city = strtoupper(trim((string) $row['city_name']));
            if ($city !== '') {
                $cities[$city] = $city;
            }
        }

        ksort($cities);
        return array_values($cities);
    }

    public function upsertMonthlyTarget($data)
    {
        $existing = $this->db->get_where('tb_rfs_myrep_monthly_target', [
            'year_num' => $data['year_num'],
            'month_num' => $data['month_num'],
            'city_name' => $data['city_name']
        ])->row_array();

        $targetMyrep = array_key_exists('target_myrep', $data)
            ? (float) $data['target_myrep']
            : ($existing ? (float) $existing['target_myrep'] : 0);
        $targetRkap = array_key_exists('target_rkap', $data)
            ? (float) $data['target_rkap']
            : ($existing ? (float) $existing['target_rkap'] : 0);
        $realizationBase = array_key_exists('realization_myrep', $data)
            ? (float) $data['realization_myrep']
            : ($existing ? (float) $existing['realization_myrep'] : 0);
        $realizationAdditional = array_key_exists('realization_myrep_additional', $data)
            ? (float) $data['realization_myrep_additional']
            : 0;

        if ($existing && $realizationAdditional > 0) {
            $realizationMyrep = (float) $existing['realization_myrep'] + $realizationAdditional;
        } else {
            $realizationMyrep = $realizationBase + $realizationAdditional;
        }

        $payload = [
            'regional_name' => array_key_exists('regional_name', $data)
                ? $data['regional_name']
                : ($existing['regional_name'] ?? null),
            'province_name' => array_key_exists('province_name', $data)
                ? $data['province_name']
                : ($existing['province_name'] ?? null),
            'team_name' => array_key_exists('team_name', $data)
                ? $data['team_name']
                : ($existing['team_name'] ?? null),
            'target_myrep' => $targetMyrep,
            'target_rkap' => $targetRkap,
            'realization_myrep' => $realizationMyrep,
            'chief' => array_key_exists('chief', $data)
                ? $data['chief']
                : ($existing['chief'] ?? null),
            'rpm' => array_key_exists('rpm', $data)
                ? $data['rpm']
                : ($existing['rpm'] ?? null),
            'sm' => array_key_exists('sm', $data)
                ? $data['sm']
                : ($existing['sm'] ?? null),
            'spv' => array_key_exists('spv', $data)
                ? $data['spv']
                : ($existing['spv'] ?? null),
            'updated_by' => $data['updated_by'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->where('id_target', $existing['id_target'])->update('tb_rfs_myrep_monthly_target', $payload);
            return $existing['id_target'];
        }

        $payload['year_num'] = $data['year_num'];
        $payload['month_num'] = $data['month_num'];
        $payload['city_name'] = $data['city_name'];
        $payload['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert('tb_rfs_myrep_monthly_target', $payload);
        return $this->db->insert_id();
    }

    public function createCluster($data)
    {
        $payload = [
            'id_target' => $data['id_target'],
            'cluster_name' => $data['cluster_name'],
            'status_rfs' => $data['status_rfs'],
            'homepass' => $data['homepass'],
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tb_rfs_myrep_cluster', $payload);
        return $this->db->insert_id();
    }

    public function upsertClusterPlan($data)
    {
        $existing = $this->db->get_where('tb_rfs_myrep_cluster_plan', [
            'cluster_id' => $data['cluster_id'],
            'year_num' => $data['year_num'],
            'month_num' => $data['month_num']
        ])->row_array();

        $payload = [
            'optimistic_target' => $data['optimistic_target'],
            'updated_by' => $data['updated_by'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->where('id_plan', $existing['id_plan'])->update('tb_rfs_myrep_cluster_plan', $payload);
            return $existing['id_plan'];
        }

        $payload['cluster_id'] = $data['cluster_id'];
        $payload['year_num'] = $data['year_num'];
        $payload['month_num'] = $data['month_num'];
        $payload['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert('tb_rfs_myrep_cluster_plan', $payload);
        return $this->db->insert_id();
    }

    public function createClaim($data)
    {
        $payload = [
            'cluster_id' => $data['cluster_id'],
            'claim_year' => $data['claim_year'],
            'claim_month' => $data['claim_month'],
            'claim_date' => $data['claim_date'],
            'claim_qty' => $data['claim_qty'],
            'photo_path' => $data['photo_path'],
            'claim_note' => $data['claim_note'],
            'status_claim' => $data['status_claim'],
            'submitted_by' => $data['submitted_by'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->claimSupportsStatusRfs()) {
            $payload['status_rfs'] = $data['status_rfs'];
        }

        if ($this->claimSupportsRpmApproval()) {
            $payload['rpm_approval_status'] = $data['rpm_approval_status'];
            $payload['rpm_approval_note'] = $data['rpm_approval_note'] ?? null;
            $payload['rpm_approved_by'] = $data['rpm_approved_by'] ?? null;
            $payload['rpm_approved_at'] = $data['rpm_approved_at'] ?? null;
        }

        $this->db->insert('tb_rfs_myrep_claim', $payload);
        return $this->db->insert_id();
    }

    public function updateClusterStatusRfs($clusterId, $statusRfs)
    {
        $updated = $this->db
            ->where('id_cluster', $clusterId)
            ->update('tb_rfs_myrep_cluster', [
                'status_rfs' => $statusRfs
            ]);

        if ($updated) {
            $this->syncMyrepStatusFromRfsCluster($clusterId, $statusRfs);
        }

        return $updated;
    }

    public function updateClaimStatus($claimId, $data)
    {
        return $this->db
            ->where('id_claim', $claimId)
            ->update('tb_rfs_myrep_claim', [
                'status_claim' => $data['status_claim'],
                'approval_note' => $data['approval_note'],
                'approved_by' => $data['approved_by'],
                'approved_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function updateClaimRpmApproval($claimId, $data)
    {
        $payload = [
            'status_claim' => $data['status_claim'],
        ];

        if ($this->claimSupportsRpmApproval()) {
            $payload['rpm_approval_status'] = $data['rpm_approval_status'];
            $payload['rpm_approval_note'] = $data['rpm_approval_note'];
            $payload['rpm_approved_by'] = $data['rpm_approved_by'];
            $payload['rpm_approved_at'] = date('Y-m-d H:i:s');
        }

        return $this->db
            ->where('id_claim', $claimId)
            ->update('tb_rfs_myrep_claim', $payload);
    }

    public function getClaimById($claimId)
    {
        return $this->db
            ->select('cl.*, mt.rpm, mt.city_name, c.cluster_name')
            ->from('tb_rfs_myrep_claim cl')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = cl.cluster_id', 'inner')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('cl.id_claim', (int) $claimId)
            ->get()
            ->row_array();
    }

    public function clusterExistsForTarget($idTarget, $clusterName)
    {
        $row = $this->db
            ->select('id_cluster')
            ->from('tb_rfs_myrep_cluster')
            ->where('id_target', (int) $idTarget)
            ->where('UPPER(cluster_name)', strtoupper(trim((string) $clusterName)))
            ->get()
            ->row_array();

        return !empty($row);
    }

    public function ensureChecklistPackagesForCluster($clusterId, $tanggalRfs = null, $userId = null)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return;
        }

        $groups = $this->db
            ->select('id_doc_group')
            ->from('md_rfs_myrep_doc_group')
            ->where('is_active', 1)
            ->order_by('sort_no', 'ASC')
            ->order_by('id_doc_group', 'ASC')
            ->get()
            ->result_array();

        foreach ($groups as $group) {
            $groupId = (int) $group['id_doc_group'];
            $existing = $this->db->get_where('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => $groupId,
            ])->row_array();

            $planAtp = $tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null;

            if ($existing) {
                if (!empty($tanggalRfs) && empty($existing['tanggal_rfs'])) {
                    $this->db
                        ->where('id_doc_package', (int) $existing['id_doc_package'])
                        ->update('tb_rfs_myrep_doc_package', [
                            'tanggal_rfs' => $tanggalRfs,
                            'plan_atp_date' => $planAtp,
                            'updated_by' => $userId ? (int) $userId : null,
                        ]);
                }
                continue;
            }

            $this->db->insert('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => $groupId,
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'status_package' => 'NOT STARTED',
                'created_by' => $userId ? (int) $userId : null,
                'updated_by' => $userId ? (int) $userId : null,
            ]);
        }
    }

    public function getClusterById($clusterId)
    {
        return $this->db
            ->select('
                c.*,
                mt.id_target,
                mt.year_num,
                mt.month_num,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                md.homepass_drm AS homepass_drm_latest,
                COALESCE(NULLIF(md.homepass_drm, 0), c.homepass) AS homepass_drm_effective
            ', false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('tb_myrep_cluster mc', 'mc.rfs_cluster_id = c.id_cluster', 'left')
            ->join(
                "(SELECT d.id_myrep_cluster, d.homepass_drm
                  FROM tb_myrep_drm d
                  INNER JOIN (
                    SELECT id_myrep_cluster, MAX(id_drm) AS latest_id_drm
                    FROM tb_myrep_drm
                    GROUP BY id_myrep_cluster
                  ) latest_drm ON latest_drm.latest_id_drm = d.id_drm
                ) md",
                'md.id_myrep_cluster = mc.id_myrep_cluster',
                'left',
                false
            )
            ->where('c.id_cluster', $clusterId)
            ->get()
            ->row_array();
    }

    public function getTargetById($idTarget)
    {
        return $this->db
            ->get_where('tb_rfs_myrep_monthly_target', ['id_target' => $idTarget])
            ->row_array();
    }

    public function getTargetByPeriodCity($year, $month, $cityName)
    {
        return $this->db
            ->get_where('tb_rfs_myrep_monthly_target', [
                'year_num' => (int) $year,
                'month_num' => (int) $month,
                'city_name' => strtoupper(trim((string) $cityName))
            ])
            ->row_array();
    }

    public function getTargetOptions($year, $startMonth, $endMonth, $city = '')
    {
        $this->db
            ->select('id_target, year_num, month_num, regional_name, province_name, city_name, team_name, chief, rpm, sm, spv, target_myrep, realization_myrep, target_rkap')
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->where('month_num >=', $startMonth)
            ->where('month_num <=', $endMonth);

        if ($city !== '') {
            $this->db->where('UPPER(city_name)', $city);
        }

        return $this->db
            ->order_by('month_num', 'ASC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getClusterClaimedQty($clusterId)
    {
        $row = $this->db->query(
            "SELECT COALESCE(SUM(claim_qty), 0) AS total_claim
             FROM tb_rfs_myrep_claim
             WHERE cluster_id = ?
             AND status_claim IN ('WAITING APPROVAL RPM', 'WAITING APPROVAL HO', 'APPROVED')",
            [$clusterId]
        )->row_array();

        return $row ? (float) $row['total_claim'] : 0;
    }

    private function calculatePercent($numerator, $denominator)
    {
        $denominator = (float) $denominator;
        if ($denominator <= 0) {
            return 0;
        }

        return round(((float) $numerator / $denominator) * 100, 2);
    }

    private function addBusinessDays($date, $days)
    {
        if (empty($date)) {
            return null;
        }

        $dateTime = new DateTime($date);
        $dateTime->modify('+' . (int) $days . ' day');
        return $dateTime->format('Y-m-d');
    }

    private function hasMyrepClusterTables()
    {
        return $this->db->table_exists('tb_myrep_cluster');
    }

    private function hasMyrepDrmDocumentTables()
    {
        return $this->db->table_exists('tb_myrep_drm')
            && $this->db->table_exists('md_myrep_flow_doc_group')
            && $this->db->table_exists('md_myrep_flow_doc_item')
            && $this->db->table_exists('tb_myrep_flow_doc_package')
            && $this->db->table_exists('tb_myrep_flow_doc_file');
    }

    private function syncEligibleMyrepClustersToRfs($year, $month, $city = '')
    {
        if (!$this->hasMyrepClusterTables()) {
            return;
        }

        $this->db
            ->select('
                id_myrep_cluster,
                rfs_cluster_id,
                cluster_name,
                regional_name,
                province_name,
                city_name,
                team_name,
                chief,
                rpm,
                sm,
                spv,
                hp_plan,
                status_current,
                created_at,
                updated_at,
                (
                    SELECT d.homepass_drm
                    FROM tb_myrep_drm d
                    WHERE d.id_myrep_cluster = tb_myrep_cluster.id_myrep_cluster
                    ORDER BY d.id_drm DESC
                    LIMIT 1
                ) AS latest_homepass_drm
            ', false)
            ->from('tb_myrep_cluster')
            ->where_in('status_current', $this->rfsReadyStatuses);

        if ($city !== '') {
            $this->db->where('UPPER(city_name)', strtoupper($city));
        }

        $clusters = $this->db->get()->result_array();
        if (empty($clusters)) {
            return;
        }

        foreach ($clusters as $cluster) {
            $idTarget = $this->ensureTargetForMyrepCluster($cluster, $year, $month);
            if ($idTarget <= 0) {
                continue;
            }

            $rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? 0);
            $homepassPlan = (int) round((float) ($cluster['hp_plan'] ?? 0));
            $homepassDrm = (int) round((float) ($cluster['latest_homepass_drm'] ?? 0));
            $homepassBase = $homepassDrm > 0 ? $homepassDrm : $homepassPlan;
            $mappedStatus = $this->mapMyrepStatusToRfs((string) ($cluster['status_current'] ?? ''));

            if ($rfsClusterId > 0) {
                $homepass = $this->resolveRfsHomepassFromClaims($rfsClusterId, $homepassBase);
                $this->db
                    ->where('id_cluster', $rfsClusterId)
                    ->update('tb_rfs_myrep_cluster', [
                        'id_target' => $idTarget,
                        'cluster_name' => $cluster['cluster_name'],
                        'homepass' => $homepass,
                        'status_rfs' => $mappedStatus,
                    ]);
                $this->syncChecklistBridgeForCluster($rfsClusterId, $cluster, $mappedStatus);
                continue;
            }

            $existing = $this->db
                ->select('id_cluster')
                ->from('tb_rfs_myrep_cluster')
                ->where('id_target', $idTarget)
                ->where('UPPER(cluster_name)', strtoupper(trim((string) ($cluster['cluster_name'] ?? ''))))
                ->get()
                ->row_array();

            if ($existing) {
                $rfsClusterId = (int) $existing['id_cluster'];
                $homepass = $this->resolveRfsHomepassFromClaims($rfsClusterId, $homepassBase);
                $this->db
                    ->where('id_cluster', $rfsClusterId)
                    ->update('tb_rfs_myrep_cluster', [
                        'homepass' => $homepass,
                        'status_rfs' => $mappedStatus,
                    ]);
            } else {
                $homepass = $homepassBase;
                $this->db->insert('tb_rfs_myrep_cluster', [
                    'id_target' => $idTarget,
                    'cluster_name' => $cluster['cluster_name'],
                    'homepass' => $homepass,
                    'status_rfs' => $mappedStatus,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $rfsClusterId = (int) $this->db->insert_id();
            }

            if ($rfsClusterId > 0) {
                $this->db
                    ->where('id_myrep_cluster', (int) $cluster['id_myrep_cluster'])
                    ->update('tb_myrep_cluster', [
                        'rfs_cluster_id' => $rfsClusterId,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $this->syncChecklistBridgeForCluster($rfsClusterId, $cluster, $mappedStatus);
            }
        }
    }

    private function ensureTargetForMyrepCluster($cluster, $year, $month)
    {
        $cityName = strtoupper(trim((string) ($cluster['city_name'] ?? '')));
        if ($cityName === '') {
            return 0;
        }

        $existing = $this->db->get_where('tb_rfs_myrep_monthly_target', [
            'year_num' => (int) $year,
            'month_num' => (int) $month,
            'city_name' => $cityName,
        ])->row_array();

        $payload = [
            'regional_name' => strtoupper(trim((string) ($cluster['regional_name'] ?? ''))),
            'province_name' => strtoupper(trim((string) ($cluster['province_name'] ?? ''))),
            'team_name' => trim((string) ($cluster['team_name'] ?? '')),
            'chief' => trim((string) ($cluster['chief'] ?? '')),
            'rpm' => trim((string) ($cluster['rpm'] ?? '')),
            'sm' => trim((string) ($cluster['sm'] ?? '')),
            'spv' => trim((string) ($cluster['spv'] ?? '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('id_target', (int) $existing['id_target'])->update('tb_rfs_myrep_monthly_target', $payload);
            return (int) $existing['id_target'];
        }

        $payload['year_num'] = (int) $year;
        $payload['month_num'] = (int) $month;
        $payload['city_name'] = $cityName;
        $payload['target_myrep'] = 0;
        $payload['realization_myrep'] = 0;
        $payload['target_rkap'] = 0;
        $payload['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert('tb_rfs_myrep_monthly_target', $payload);
        return (int) $this->db->insert_id();
    }

    private function mapMyrepStatusToRfs($statusCurrent)
    {
        $statusCurrent = strtoupper(trim((string) $statusCurrent));
        if (in_array($statusCurrent, ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            return 'FULL RFS';
        }

        return 'NY RFS';
    }

    private function syncMyrepStatusFromRfsCluster($rfsClusterId, $statusRfs)
    {
        if (!$this->hasMyrepClusterTables()) {
            return;
        }

        $statusRfs = strtoupper(trim((string) $statusRfs));
        $payload = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (in_array($statusRfs, ['PARTIAL', 'FULL RFS'], true)) {
            $payload['status_current'] = 'RFS';
        } elseif ($statusRfs === 'REJECTED') {
            $payload['status_current'] = 'DRM';
        }

        if (count($payload) <= 1) {
            return;
        }

        $this->db
            ->where('rfs_cluster_id', (int) $rfsClusterId)
            ->update('tb_myrep_cluster', $payload);
    }

    private function syncChecklistBridgeForCluster($rfsClusterId, $cluster, $mappedStatus)
    {
        if (strtoupper(trim((string) $mappedStatus)) !== 'FULL RFS') {
            return;
        }

        $tanggalRfs = $this->resolveChecklistTanggalRfs($cluster);
        $userId = (int) ($this->session->userdata('id_user') ?? 0);
        $this->ensureChecklistPackagesForCluster((int) $rfsClusterId, $tanggalRfs, $userId > 0 ? $userId : null);
    }

    private function resolveChecklistTanggalRfs($cluster)
    {
        $preferredDates = [
            $cluster['tanggal_rfs'] ?? null,
            $cluster['updated_at'] ?? null,
            $cluster['created_at'] ?? null,
        ];

        foreach ($preferredDates as $value) {
            $value = trim((string) $value);
            if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                continue;
            }

            return substr($value, 0, 10);
        }

        return null;
    }

    private function resolveRfsHomepassFromClaims($rfsClusterId, $fallbackHomepass)
    {
        $rfsClusterId = (int) $rfsClusterId;
        $fallbackHomepass = max(0, (int) $fallbackHomepass);
        if ($rfsClusterId <= 0 || !$this->db->table_exists('tb_rfs_myrep_claim')) {
            return $fallbackHomepass;
        }

        $row = $this->db
            ->select('COALESCE(SUM(claim_qty), 0) AS total_claim', false)
            ->from('tb_rfs_myrep_claim')
            ->where('cluster_id', $rfsClusterId)
            ->where_in('status_claim', ['WAITING APPROVAL RPM', 'WAITING APPROVAL HO', 'APPROVED'])
            ->get()
            ->row_array();

        $totalClaim = (int) round((float) ($row['total_claim'] ?? 0));
        return max($fallbackHomepass, $totalClaim);
    }
}


