<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMyRepublik_Project extends CI_Model
{
    private $statusOrder = [
        'DRAFT',
        'BA OPEN',
        'BAK',
        'VALSAL',
        'WAITING HO',
        'WAITING MYREP',
        'WAITING FINANCE',
        'RELEASED',
        'DONE BATCH APPROVAL',
        'DRM',
        'RFS',
        'ATP',
        'DONE',
        'REJECTED',
        'HOLD',
    ];

    public function tablesReady()
    {
        return $this->hasMyrepTables() || $this->hasLegacyRfsTables();
    }

    public function getCityOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $cities = [];

        if ($this->hasMyrepTables()) {
            $rows = $this->db
                ->distinct()
                ->select('city_name')
                ->from('tb_myrep_cluster')
                ->where('city_name IS NOT NULL', null, false)
                ->where("TRIM(city_name) !=", '')
                ->order_by('city_name', 'ASC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityName !== '') {
                    $cities[$cityName] = $cityName;
                }
            }
        }

        if ($this->hasLegacyRfsTables()) {
            $rows = $this->db
                ->distinct()
                ->select('city_name')
                ->from('tb_rfs_myrep_monthly_target')
                ->where('city_name IS NOT NULL', null, false)
                ->where("TRIM(city_name) !=", '')
                ->order_by('city_name', 'ASC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityName !== '') {
                    $cities[$cityName] = $cityName;
                }
            }
        }

        ksort($cities);
        return array_values($cities);
    }

    public function getClusterRows($selectedCity = '', $selectedStatus = '', $metricMode = 'HP')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->getNewFlowRows($selectedCity, $selectedStatus);

        $poMap = $this->getPoMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $row['po_total_value'] = (float) ($poMap[$clusterId]['po_total_value'] ?? 0);
            $row['po_count'] = (int) ($poMap[$clusterId]['po_count'] ?? 0);
            $row['metric_value'] = $this->resolveMetricValue($row, $metricMode);
        }
        unset($row);

        $legacyRows = $this->getLegacyRfsRows($selectedCity, $selectedStatus);
        $mappedRfsIds = [];
        $existingKeys = [];
        foreach ($rows as $row) {
            $mappedRfsId = (int) ($row['rfs_cluster_id'] ?? 0);
            if ($mappedRfsId > 0) {
                $mappedRfsIds[$mappedRfsId] = true;
            }

            $existingKeys[$this->buildClusterKey($row['city_name'] ?? '', $row['cluster_name'] ?? '')] = true;
        }

        foreach ($legacyRows as $legacyRow) {
            $legacyClusterId = (int) ($legacyRow['legacy_rfs_cluster_id'] ?? 0);
            $clusterKey = $this->buildClusterKey($legacyRow['city_name'] ?? '', $legacyRow['cluster_name'] ?? '');

            if (($legacyClusterId > 0 && isset($mappedRfsIds[$legacyClusterId])) || isset($existingKeys[$clusterKey])) {
                continue;
            }

            $legacyRow['metric_value'] = $this->resolveMetricValue($legacyRow, $metricMode);
            $rows[] = $legacyRow;
        }

        usort($rows, static function ($a, $b) {
            $dateA = strtotime((string) ($a['created_at'] ?? '')) ?: 0;
            $dateB = strtotime((string) ($b['created_at'] ?? '')) ?: 0;
            if ($dateA === $dateB) {
                return strcmp((string) ($a['cluster_name'] ?? ''), (string) ($b['cluster_name'] ?? ''));
            }

            return $dateB <=> $dateA;
        });

        return $rows;
    }

    public function getStatusCards($rows, $metricMode = 'HP')
    {
        $cards = [];
        foreach ($this->statusOrder as $status) {
            $cards[$status] = [
                'status' => $status,
                'cluster_count' => 0,
                'metric_total' => 0,
            ];
        }

        foreach ($rows as $row) {
            $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
            if (!isset($cards[$status])) {
                $cards[$status] = [
                    'status' => $status,
                    'cluster_count' => 0,
                    'metric_total' => 0,
                ];
            }

            $cards[$status]['cluster_count']++;
            $cards[$status]['metric_total'] += (float) ($row['metric_value'] ?? 0);
        }

        return array_values(array_filter($cards, static function ($card) {
            return $card['cluster_count'] > 0;
        }));
    }

    public function getOverview($rows)
    {
        $overview = [
            'total_cluster' => 0,
            'total_hp' => 0,
            'total_po' => 0,
            'total_released' => 0,
            'total_rfs' => 0,
            'total_atp' => 0,
        ];

        foreach ($rows as $row) {
            $overview['total_cluster']++;
            $overview['total_hp'] += (float) $this->resolveMetricValue($row, 'HP');
            $overview['total_po'] += (float) ($row['po_total_value'] ?? 0);

            $status = strtoupper(trim((string) ($row['status_current'] ?? '')));
            if ($status === 'RELEASED') {
                $overview['total_released']++;
            } elseif ($status === 'RFS') {
                $overview['total_rfs']++;
            } elseif ($status === 'ATP') {
                $overview['total_atp']++;
            }
        }

        return $overview;
    }

    public function getStatusOptions()
    {
        return $this->statusOrder;
    }

    private function resolveMetricValue($row, $metricMode)
    {
        $metricMode = strtoupper(trim((string) $metricMode));
        if ($metricMode === 'PO') {
            return (float) ($row['po_total_value'] ?? 0);
        }

        $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $hpPlan = (float) ($row['hp_plan'] ?? 0);
        $hpBak = (float) ($row['homepass_bak'] ?? 0);
        $hpValsal = (float) ($row['homepass_valsal'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);
        $hpDrm = (float) ($row['homepass_drm'] ?? 0);

        if (in_array($status, ['DRAFT', 'BA OPEN', 'BAK'], true)) {
            return $hpBak > 0 ? $hpBak : $hpPlan;
        }

        if ($status === 'VALSAL') {
            return $hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan);
        }

        if (in_array($status, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'], true)) {
            return $hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan));
        }

        if (in_array($status, ['DRM', 'RFS', 'ATP', 'DONE'], true)) {
            return $hpDrm > 0 ? $hpDrm : ($hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan)));
        }

        return $hpPlan;
    }

    private function getNewFlowRows($selectedCity = '', $selectedStatus = '')
    {
        if (!$this->hasMyrepTables()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.rfs_cluster_id,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.hp_plan,
                c.status_current,
                c.created_at,
                b.homepass_bak,
                v.homepass_valsal,
                ba.hp_donasi,
                ba.staging_status,
                d.homepass_drm,
                d.drm_date
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');

        if ($selectedCity !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($selectedCity));
        }

        if ($selectedStatus !== '') {
            $this->db->where('UPPER(c.status_current)', strtoupper($selectedStatus));
        }

        return $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    private function getLegacyRfsRows($selectedCity = '', $selectedStatus = '')
    {
        if (!$this->hasLegacyRfsTables()) {
            return [];
        }

        $actualAtpSelect = 'NULL';
        if ($this->db->table_exists('tb_rfs_myrep_doc_package')) {
            $actualAtpSelect = '(SELECT MAX(dp.actual_atp_date) FROM tb_rfs_myrep_doc_package dp WHERE dp.cluster_id = c.id_cluster)';
        }

        $statusExpr = "CASE
                WHEN {$actualAtpSelect} IS NOT NULL THEN 'ATP'
                WHEN UPPER(COALESCE(c.status_rfs, '')) IN ('PARTIAL', 'FULL RFS') THEN 'RFS'
                ELSE 'DRM'
            END";

        $this->db
            ->select("
                NULL AS id_myrep_cluster,
                c.id_cluster AS legacy_rfs_cluster_id,
                c.id_cluster AS rfs_cluster_id,
                c.cluster_name,
                NULL AS cluster_code,
                COALESCE(mt.regional_name, '-') AS regional_name,
                COALESCE(mt.province_name, '-') AS province_name,
                COALESCE(mt.city_name, '-') AS city_name,
                COALESCE(mt.team_name, '-') AS team_name,
                COALESCE(mt.rpm, '-') AS rpm,
                COALESCE(mt.sm, '-') AS sm,
                COALESCE(mt.spv, '-') AS spv,
                c.homepass AS hp_plan,
                {$statusExpr} AS status_current,
                c.created_at,
                NULL AS homepass_bak,
                NULL AS homepass_valsal,
                NULL AS hp_donasi,
                NULL AS staging_status,
                c.homepass AS homepass_drm,
                NULL AS drm_date,
                0 AS po_total_value,
                0 AS po_count
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left');

        if ($selectedCity !== '') {
            $this->db->where('UPPER(COALESCE(mt.city_name, c.city_name))', strtoupper($selectedCity));
        }

        $rows = $this->db->get()->result_array();

        if ($selectedStatus !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($selectedStatus) {
                return strtoupper(trim((string) ($row['status_current'] ?? ''))) === strtoupper($selectedStatus);
            }));
        }

        return $rows;
    }

    private function buildClusterKey($cityName, $clusterName)
    {
        return strtoupper(trim((string) $cityName)) . '|' . strtoupper(trim((string) $clusterName));
    }

    private function hasMyrepTables()
    {
        return $this->db->table_exists('tb_myrep_cluster');
    }

    private function hasLegacyRfsTables()
    {
        return $this->db->table_exists('tb_rfs_myrep_cluster') && $this->db->table_exists('tb_rfs_myrep_monthly_target');
    }

    private function getPoMap($clusterIds)
    {
        if (!$this->db->table_exists('tb_myrep_po_header')) {
            return [];
        }

        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $rows = $this->db
            ->select('id_myrep_cluster, COUNT(id_po_header) AS po_count, COALESCE(SUM(po_value),0) AS po_total_value')
            ->from('tb_myrep_po_header')
            ->where_in('id_myrep_cluster', $clusterIds)
            ->group_by('id_myrep_cluster')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['id_myrep_cluster'] ?? 0)] = [
                'po_count' => (int) ($row['po_count'] ?? 0),
                'po_total_value' => (float) ($row['po_total_value'] ?? 0),
            ];
        }

        return $map;
    }
}
