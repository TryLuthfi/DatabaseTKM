<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMonitoring_RFS_MyRep extends CI_Model
{
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
            ->select('city_name, SUM(target_myrep) AS target_myrep, SUM(target_rkap) AS target_rkap, SUM(realization_myrep) AS realization_myrep', false)
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
            ->select('city_name, SUM(target_myrep) AS target_myrep, SUM(target_rkap) AS target_tkm, SUM(realization_myrep) AS realization_myrep', false)
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
        $sql = "SELECT
                c.*,
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
                COALESCE(p.optimistic_target, 0) AS optimistic_target,
                COALESCE((
                    SELECT SUM(claim_qty)
                    FROM tb_rfs_myrep_claim cl
                    WHERE cl.cluster_id = c.id_cluster
                    AND cl.claim_year = ?
                    AND cl.claim_month BETWEEN ? AND ?
                    AND cl.status_claim IN ('PENDING', 'APPROVED')
                ), 0) AS claimed_qty
             FROM tb_rfs_myrep_cluster c
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
             LEFT JOIN tb_rfs_myrep_cluster_plan p
               ON p.cluster_id = c.id_cluster
               AND p.year_num = ?
               AND p.month_num = ?
             WHERE 1=1";

        $params = [$year, $startMonth, $endMonth, $year, $endMonth];

        if ($city !== '') {
            $sql .= " AND UPPER(mt.city_name) = ? ";
            $params[] = $city;
        }

        $sql .= " ORDER BY mt.city_name ASC, c.cluster_name ASC";

        return $this->db->query($sql, $params)->result_array();
    }

    public function getClaims($year, $startMonth, $endMonth, $city = '')
    {
        $sql = "SELECT
                cl.*,
                mt.city_name,
                c.cluster_name,
                c.homepass,
                mt.rpm,
                mt.sm,
                mt.spv,
                su.nama_user AS submitted_name,
                au.nama_user AS approved_name
             FROM tb_rfs_myrep_claim cl
             INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = cl.cluster_id
             INNER JOIN tb_rfs_myrep_monthly_target mt ON mt.id_target = c.id_target
             LEFT JOIN tb_master_user su ON su.id_user = cl.submitted_by
             LEFT JOIN tb_master_user au ON au.id_user = cl.approved_by
             WHERE cl.claim_year = ? AND cl.claim_month BETWEEN ? AND ?";

        $params = [$year, $startMonth, $endMonth];

        if ($city !== '') {
            $sql .= " AND UPPER(mt.city_name) = ? ";
            $params[] = $city;
        }

        $sql .= " ORDER BY cl.id_claim DESC";

        return $this->db->query($sql, $params)->result_array();
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

        $this->db->insert('tb_rfs_myrep_claim', $payload);
        return $this->db->insert_id();
    }

    public function updateClusterStatusRfs($clusterId, $statusRfs)
    {
        return $this->db
            ->where('id_cluster', $clusterId)
            ->update('tb_rfs_myrep_cluster', [
                'status_rfs' => $statusRfs
            ]);
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

    public function getClusterById($clusterId)
    {
        return $this->db
            ->select('c.*, mt.year_num, mt.month_num, mt.city_name, mt.regional_name, mt.province_name, mt.chief, mt.rpm, mt.sm, mt.spv')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
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
            ->select('id_target, year_num, month_num, regional_name, province_name, city_name, chief, rpm, sm, spv, target_myrep, realization_myrep, target_rkap')
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
             AND status_claim IN ('PENDING', 'APPROVED')",
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
}
