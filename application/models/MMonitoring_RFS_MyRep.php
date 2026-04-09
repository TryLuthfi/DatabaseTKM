<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMonitoring_RFS_MyRep extends CI_Model
{
    public function getAnnualSummary($year)
    {
        $sql = "SELECT
                    COALESCE(SUM(target_myrep), 0) AS target_myrep,
                    COALESCE(SUM(realization_myrep), 0) AS realization_myrep,
                    COALESCE(SUM(target_rkap), 0) AS target_tkm,
                    COALESCE((
                        SELECT SUM(c.claim_qty)
                        FROM tb_rfs_myrep_claim c
                        WHERE c.claim_year = ?
                        AND c.status_claim = 'APPROVED'
                    ), 0) AS realization_tkm
                FROM tb_rfs_myrep_monthly_target
                WHERE year_num = ?";

        $row = $this->db->query($sql, [$year, $year])->row_array();

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

    public function getMonthlySummary($year, $month)
    {
        $targets = $this->db
            ->select('city_name, target_myrep, target_rkap, realization_myrep')
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->where('month_num', $month)
            ->get()
            ->result_array();

        $claims = $this->db->query(
            "SELECT cl.city_name, COALESCE(SUM(c.claim_qty), 0) AS realization_tkm
             FROM tb_rfs_myrep_claim c
             INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
             WHERE c.claim_year = ? AND c.claim_month = ? AND c.status_claim = 'APPROVED'
             GROUP BY cl.city_name",
            [$year, $month]
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

    public function getAnnualCitySummary($year)
    {
        $targets = $this->db
            ->select('city_name, SUM(target_myrep) AS target_myrep, SUM(target_rkap) AS target_tkm, SUM(realization_myrep) AS realization_myrep', false)
            ->from('tb_rfs_myrep_monthly_target')
            ->where('year_num', $year)
            ->group_by('city_name')
            ->get()
            ->result_array();

        $claims = $this->db->query(
            "SELECT cl.city_name, COALESCE(SUM(c.claim_qty), 0) AS realization_tkm
             FROM tb_rfs_myrep_claim c
             INNER JOIN tb_rfs_myrep_cluster cl ON cl.id_cluster = c.cluster_id
             WHERE c.claim_year = ? AND c.status_claim = 'APPROVED'
             GROUP BY cl.city_name",
            [$year]
        )->result_array();

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

    public function getThreeMonthColumns($year, $month)
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
        for ($i = 2; $i >= 0; $i--) {
            $date = strtotime(sprintf('%04d-%02d-01', $year, $month) . ' -' . $i . ' month');
            $monthNumber = (int) date('n', $date);
            $columns[] = [
                'year_num' => (int) date('Y', $date),
                'month_num' => $monthNumber,
                'label' => $monthNames[$monthNumber]
            ];
        }

        return $columns;
    }

    public function getThreeMonthSummary($year, $month)
    {
        $columns = $this->getThreeMonthColumns($year, $month);
        $cities = $this->getCityOptions();
        $result = [];

        foreach ($cities as $city) {
            $result[$city] = [
                'city_name' => $city,
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
                ->where('month_num', $column['month_num'])
                ->get()
                ->result_array();

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

            $planRows = $this->db->query(
                "SELECT c.city_name, COALESCE(SUM(p.optimistic_target), 0) AS realistis
                 FROM tb_rfs_myrep_cluster_plan p
                 INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = p.cluster_id
                 WHERE p.year_num = ? AND p.month_num = ?
                 GROUP BY c.city_name",
                [$column['year_num'], $column['month_num']]
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

            $claimRows = $this->db->query(
                "SELECT c.city_name, COALESCE(SUM(cl.claim_qty), 0) AS pencapaian
                 FROM tb_rfs_myrep_claim cl
                 INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = cl.cluster_id
                 WHERE cl.claim_year = ? AND cl.claim_month = ? AND cl.status_claim = 'APPROVED'
                 GROUP BY c.city_name",
                [$column['year_num'], $column['month_num']]
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

    public function getClustersWithPlan($year, $month)
    {
        return $this->db->query(
            "SELECT
                c.*,
                COALESCE(p.optimistic_target, 0) AS optimistic_target,
                COALESCE((
                    SELECT SUM(claim_qty)
                    FROM tb_rfs_myrep_claim cl
                    WHERE cl.cluster_id = c.id_cluster
                    AND cl.status_claim IN ('PENDING', 'APPROVED')
                ), 0) AS claimed_qty
             FROM tb_rfs_myrep_cluster c
             LEFT JOIN tb_rfs_myrep_cluster_plan p
               ON p.cluster_id = c.id_cluster
               AND p.year_num = ?
               AND p.month_num = ?
             ORDER BY c.city_name ASC, c.cluster_name ASC",
            [$year, $month]
        )->result_array();
    }

    public function getClaims($year, $month)
    {
        return $this->db->query(
            "SELECT
                cl.*,
                c.city_name,
                c.cluster_name,
                c.homepass,
                c.pic_1,
                c.pic_2,
                su.nama_user AS submitted_name,
                au.nama_user AS approved_name
             FROM tb_rfs_myrep_claim cl
             INNER JOIN tb_rfs_myrep_cluster c ON c.id_cluster = cl.cluster_id
             LEFT JOIN tb_master_user su ON su.id_user = cl.submitted_by
             LEFT JOIN tb_master_user au ON au.id_user = cl.approved_by
             WHERE cl.claim_year = ? AND cl.claim_month = ?
             ORDER BY cl.id_claim DESC",
            [$year, $month]
        )->result_array();
    }

    public function getCityOptions()
    {
        $cities = [];

        $targetRows = $this->db->distinct()->select('city_name')->from('tb_rfs_myrep_monthly_target')->order_by('city_name', 'ASC')->get()->result_array();
        $clusterRows = $this->db->distinct()->select('city_name')->from('tb_rfs_myrep_cluster')->order_by('city_name', 'ASC')->get()->result_array();

        foreach (array_merge($targetRows, $clusterRows) as $row) {
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

        $payload = [
            'target_myrep' => $data['target_myrep'],
            'target_rkap' => $data['target_rkap'],
            'realization_myrep' => $data['realization_myrep'],
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
            'city_name' => $data['city_name'],
            'cluster_name' => $data['cluster_name'],
            'pic_1' => $data['pic_1'],
            'pic_2' => $data['pic_2'],
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
        return $this->db->get_where('tb_rfs_myrep_cluster', ['id_cluster' => $clusterId])->row_array();
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
