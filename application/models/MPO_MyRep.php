<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MPO_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;

    private $defaultTerminPercents = [20, 25, 15, 30, 10];

    private $emrTargetAreaRegionalMap = [
        'AREA 1' => ['REGIONAL 1', 'SUMATERA'],
        'AREA 2' => ['REGIONAL 2', 'JABO', 'JABAR', 'JABO JABAR'],
        'AREA 3' => ['REGIONAL 3', 'JATENG', 'DIY', 'JATENG DIY'],
        'AREA 4' => ['REGIONAL 4', 'JATIM', 'BALNUS', 'JATIM BALNUS'],
        'AREA 5' => ['REGIONAL 5', 'KALIMANTAN', 'SULAWESI', 'KALIMANTAN SULAWESI'],
    ];

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    public function tablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_po_header',
            'tb_myrep_po_termin',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function emrTargetReady()
    {
        return $this->tablesReady()
            && $this->db->field_exists('on_target', 'tb_myrep_po_header');
    }

    public function getCityOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_po_header p', 'p.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.city_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db->get()->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        return array_values(array_unique($cities));
    }

    public function getEligibleClusterOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                d.id_drm,
                d.drm_date,
                d.homepass_drm
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->group_start()
                ->where_in('UPPER(c.status_current)', ['DRM', 'RFS', 'ATP', 'DONE'])
                ->or_where('d.id_drm IS NOT NULL', null, false)
            ->group_end()
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $this->db->get()->result_array();
    }

    public function getRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                c.created_at,
                d.id_drm,
                d.drm_date,
                d.homepass_drm
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        $rows = $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $poMetaMap = $this->getPoMetaMap(array_column($rows, 'id_myrep_cluster'));
        $filtered = [];

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $meta = $poMetaMap[$clusterId] ?? $this->buildEmptyMeta();
            $mergedRow = array_merge($row, $meta);

            if ($status !== '' && strtoupper((string) ($mergedRow['po_stage_status'] ?? 'NOT ISSUED')) !== strtoupper($status)) {
                continue;
            }

            $filtered[] = $mergedRow;
        }

        return $filtered;
    }

    public function getPoListRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                p.id_po_header,
                p.id_myrep_cluster,
                p.po_type,
                p.po_category,
                p.po_number,
                p.po_date,
                p.po_value,
                p.status_po,
                p.po_version_label,
                c.cluster_name,
                c.city_name,
                c.regional_name,
                c.status_current
            ')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $rows = $this->db
            ->order_by('p.po_date', 'DESC')
            ->order_by('p.po_number', 'ASC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $headerIds = array_values(array_filter(array_map('intval', array_column($rows, 'id_po_header'))));
        $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin');
        $this->applyIntWhereInChunks('id_po_header', $headerIds);
        $terminRows = $this->db
            ->get()
            ->result_array();

        $terminMap = [];
        $terminByHeader = [];
        foreach ($terminRows as $termin) {
            $headerId = (int) ($termin['id_po_header'] ?? 0);
            $terminByHeader[$headerId][] = $termin;
            if (!isset($terminMap[$headerId])) {
                $terminMap[$headerId] = [
                    'total' => 0,
                    'progress' => 0,
                    'paid' => 0,
                    'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                    'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                ];
            }
            $terminMap[$headerId]['total']++;
            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            $terminNo = (int) ($termin['termin_no'] ?? 0);
            $terminValue = (float) ($termin['termin_value'] ?? 0);

            if ($terminNo >= 1 && $terminNo <= 5 && !in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                // Plan invoice hanya untuk termin yang belum ditagihkan
                $terminMap[$headerId]['plan_invoice'][$terminNo] = $terminValue;
            }
            if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                $terminMap[$headerId]['progress']++;
                if ($terminNo >= 1 && $terminNo <= 5) {
                    $terminMap[$headerId]['done_invoice'][$terminNo] = $terminValue;
                }
            }
            if ($statusTermin === 'PAID') {
                $terminMap[$headerId]['paid']++;
            }
        }

        foreach ($rows as &$row) {
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $meta = $terminMap[$headerId] ?? [
                'total' => 0,
                'progress' => 0,
                'paid' => 0,
                'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ];
            $row['termin_total_count'] = (int) $meta['total'];
            $row['termin_progress_count'] = (int) $meta['progress'];
            $row['termin_paid_count'] = (int) $meta['paid'];
            $row['plan_invoice_per_termin'] = $meta['plan_invoice'];
            $row['done_invoice_per_termin'] = $meta['done_invoice'];
            $row['plan_invoice_total'] = array_sum($meta['plan_invoice']);
            $row['done_invoice_total'] = array_sum($meta['done_invoice']);
            $row['total_invoiced'] = $row['done_invoice_total'];
            // Samakan definisi dengan modal breakdown:
            // Outstanding Total = total termin yang belum BILLED/PAID.
            $row['outstanding_total'] = (float) $row['plan_invoice_total'];
            $terminsForHeader = $terminByHeader[$headerId] ?? [];
            $row['po_stage_status'] = $this->resolveStageStatus($terminsForHeader);
        }
        unset($row);

        return $rows;
    }

    public function getEmrTargetCityOptions($regional = '')
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $areaNumbers = $this->resolveEmrTargetAreaNumbers($regional);
        $regional = $this->resolveEmrTargetAreaRegionalValues($regional);
        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('p.on_target', 1)
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.city_name', 'ASC');

        if (!empty($regional)) {
            if ($this->supportsEmrTargetAreaColumn() && !empty($areaNumbers)) {
                $this->joinEmrTargetAreaMap($rows);
                $rows->group_start();
                $this->applyUpperInFilter($rows, 'area_map.area_number', $areaNumbers);
                $rows->or_where('UPPER(c.regional_name) IN (' . $this->buildEscapedSqlList($regional) . ')', null, false);
                $rows->group_end();
            } else {
                $this->applyUpperInFilter($rows, 'c.regional_name', $regional);
            }
        }

        $rows = $rows->get()->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        return array_values(array_unique($cities));
    }

    public function getEmrTargetRegionalOptions($city = '')
    {
        return $this->getEmrTargetAreaOptions($city);
    }

    public function getEmrTargetAreaOptions($city = '')
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $city = $this->normalizeUpperList($city);
        $rows = $this->db
            ->distinct()
            ->select('c.regional_name')
            ->select($this->supportsEmrTargetAreaColumn() ? 'area_map.area_number' : "'' AS area_number", false)
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('p.on_target', 1)
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where("TRIM(c.regional_name) !=", '')
            ->order_by('c.regional_name', 'ASC');
        $this->joinEmrTargetAreaMap($rows);

        if (!empty($city)) {
            $this->applyUpperInFilter($rows, 'c.city_name', $city);
        }

        $rows = $rows->get()->result_array();

        $areaSet = [];
        foreach ($rows as $row) {
            $areaName = $this->resolveEmrTargetAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? ''));
            if ($areaName !== '') {
                $areaSet[$areaName] = true;
            }
        }

        if (empty($city)) {
            return array_keys($this->emrTargetAreaRegionalMap);
        }

        return array_values(array_filter(array_keys($this->emrTargetAreaRegionalMap), static function ($areaName) use ($areaSet) {
            return !empty($areaSet[$areaName]);
        }));
    }

    public function getEmrTargetCityOptionsByRegional()
    {
        return $this->getEmrTargetCityOptionsByArea();
    }

    public function getEmrTargetCityOptionsByArea()
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.regional_name, c.city_name')
            ->select($this->supportsEmrTargetAreaColumn() ? 'area_map.area_number' : "'' AS area_number", false)
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('p.on_target', 1)
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.regional_name) !=", '')
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.regional_name', 'ASC')
            ->order_by('c.city_name', 'ASC')
        ;
        $this->joinEmrTargetAreaMap($rows);
        $rows = $rows->get()->result_array();

        $map = [];
        foreach ($rows as $row) {
            $areaName = $this->resolveEmrTargetAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? ''));
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($areaName === '' || $cityName === '') {
                continue;
            }
            if (!isset($map[$areaName])) {
                $map[$areaName] = [];
            }
            if (!in_array($cityName, $map[$areaName], true)) {
                $map[$areaName][] = $cityName;
            }
        }

        return $map;
    }

    public function getEmrTargetRegionalOptionsByCity()
    {
        return $this->getEmrTargetAreaOptionsByCity();
    }

    public function getEmrTargetAreaOptionsByCity()
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name, c.regional_name')
            ->select($this->supportsEmrTargetAreaColumn() ? 'area_map.area_number' : "'' AS area_number", false)
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('p.on_target', 1)
            ->where('c.city_name IS NOT NULL', null, false)
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->where("TRIM(c.regional_name) !=", '')
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.regional_name', 'ASC')
        ;
        $this->joinEmrTargetAreaMap($rows);
        $rows = $rows->get()->result_array();

        $map = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            $areaName = $this->resolveEmrTargetAreaLabel((string) ($row['area_number'] ?? ''), (string) ($row['regional_name'] ?? ''));
            if ($cityName === '' || $areaName === '') {
                continue;
            }
            if (!isset($map[$cityName])) {
                $map[$cityName] = [];
            }
            if (!in_array($areaName, $map[$cityName], true)) {
                $map[$cityName][] = $areaName;
            }
        }

        return $map;
    }

    public function getEmrTargetPoListRows($city = '', $stageStatus = '', $regional = '')
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $city = $this->normalizeUpperList($city);
        $regional = $this->normalizeUpperList($regional);
        $this->db
            ->select('
                p.id_po_header,
                p.id_myrep_cluster,
                p.po_type,
                p.po_category,
                p.po_number,
                p.po_date,
                p.po_value,
                p.status_po,
                p.on_target,
                p.po_version_label,
                p.remark_po,
                c.cluster_name,
                c.cluster_code,
                c.city_name,
                c.regional_name,
                c.team_name,
                c.status_current
            ')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('p.on_target', 1);

        if (!empty($city)) {
            $this->applyUpperInFilter($this->db, 'c.city_name', $city);
        }
        if (!empty($regional)) {
            $this->applyUpperInFilter($this->db, 'c.regional_name', $regional);
        }

        $rows = $this->db
            ->order_by('p.po_date', 'DESC')
            ->order_by('p.po_number', 'ASC')
            ->get()
            ->result_array();

        return $this->decoratePoRowsWithTerminMeta($rows, $stageStatus);
    }

    public function getEmrTargetAggregateData($city = '', $stageStatus = '', $regional = '')
    {
        $emptyBreakdown = [
            'CLUSTER' => [
                'po_type' => 'CLUSTER',
                'po_count' => 0,
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
            'SUBFEEDER' => [
                'po_type' => 'SUBFEEDER',
                'po_count' => 0,
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
        ];

        if (!$this->emrTargetReady()) {
            return [
                'summary' => [
                    'total_po' => 0,
                    'total_cluster' => 0,
                    'total_po_value' => 0,
                    'total_outstanding' => 0,
                    'total_invoiced' => 0,
                ],
                'terminBreakdownRows' => array_values($emptyBreakdown),
            ];
        }

        $fromSql = $this->getEmrTargetPoFromSql();
        $whereSql = $this->buildEmrTargetWhereSql($city, $stageStatus, $regional);

        $summary = $this->db->query("
            SELECT
                COUNT(*) AS total_po,
                COUNT(DISTINCT p.id_myrep_cluster) AS total_cluster,
                COALESCE(SUM(p.po_value), 0) AS total_po_value,
                COALESCE(SUM(COALESCE(tm.plan_invoice_total, 0)), 0) AS total_outstanding,
                COALESCE(SUM(COALESCE(tm.done_invoice_total, 0)), 0) AS total_invoiced
            {$fromSql}
            {$whereSql}
        ")->row_array();

        $breakdownRows = $this->db->query("
            SELECT
                CASE WHEN UPPER(TRIM(COALESCE(p.po_type, 'CLUSTER'))) = 'SUBFEEDER' THEN 'SUBFEEDER' ELSE 'CLUSTER' END AS po_type,
                COUNT(*) AS po_count,
                COALESCE(SUM(p.po_value), 0) AS total_po_value,
                COALESCE(SUM(COALESCE(tm.termin_progress_count, 0)), 0) AS term_done_count,
                COALESCE(SUM(COALESCE(tm.plan_1, 0)), 0) AS termin_1,
                COALESCE(SUM(COALESCE(tm.plan_2, 0)), 0) AS termin_2,
                COALESCE(SUM(COALESCE(tm.plan_3, 0)), 0) AS termin_3,
                COALESCE(SUM(COALESCE(tm.plan_4, 0)), 0) AS termin_4,
                COALESCE(SUM(COALESCE(tm.plan_5, 0)), 0) AS termin_5,
                COALESCE(SUM(COALESCE(tm.done_invoice_total, 0)), 0) AS total_invoiced_value,
                COALESCE(SUM(COALESCE(tm.plan_invoice_total, 0)), 0) AS outstanding_value
            {$fromSql}
            {$whereSql}
            GROUP BY CASE WHEN UPPER(TRIM(COALESCE(p.po_type, 'CLUSTER'))) = 'SUBFEEDER' THEN 'SUBFEEDER' ELSE 'CLUSTER' END
        ")->result_array();

        foreach ($breakdownRows as $row) {
            $type = (string) ($row['po_type'] ?? 'CLUSTER');
            if (!isset($emptyBreakdown[$type])) {
                continue;
            }

            $emptyBreakdown[$type]['po_count'] = (int) ($row['po_count'] ?? 0);
            $emptyBreakdown[$type]['total_po_value'] = (float) ($row['total_po_value'] ?? 0);
            $emptyBreakdown[$type]['term_done_count'] = (int) ($row['term_done_count'] ?? 0);
            $emptyBreakdown[$type]['termin_values'] = [
                1 => (float) ($row['termin_1'] ?? 0),
                2 => (float) ($row['termin_2'] ?? 0),
                3 => (float) ($row['termin_3'] ?? 0),
                4 => (float) ($row['termin_4'] ?? 0),
                5 => (float) ($row['termin_5'] ?? 0),
            ];
            $emptyBreakdown[$type]['total_invoiced_value'] = (float) ($row['total_invoiced_value'] ?? 0);
            $emptyBreakdown[$type]['outstanding_value'] = (float) ($row['outstanding_value'] ?? 0);
        }

        return [
            'summary' => [
                'total_po' => (int) ($summary['total_po'] ?? 0),
                'total_cluster' => (int) ($summary['total_cluster'] ?? 0),
                'total_po_value' => (float) ($summary['total_po_value'] ?? 0),
                'total_outstanding' => (float) ($summary['total_outstanding'] ?? 0),
                'total_invoiced' => (float) ($summary['total_invoiced'] ?? 0),
            ],
            'terminBreakdownRows' => array_values($emptyBreakdown),
        ];
    }

    public function getEmrTargetTerminPicSummary($city = '', $stageStatus = '', $regional = '')
    {
        $summary = $this->buildEmptyTerminPicSummary();
        if (!$this->emrTargetReady()) {
            return array_values($summary);
        }

        $rfsClusterSelect = $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
            ? 'c.rfs_cluster_id'
            : '0 AS rfs_cluster_id';
        $rows = $this->db->query("
            SELECT
                p.id_po_header,
                p.po_type,
                c.id_myrep_cluster,
                c.status_current,
                {$this->getEmrTargetAreaSelectSql()},
                {$this->getEmrTargetDrmStatusSelectSql()},
                {$this->getEmrTargetAtpSelectSql()},
                {$rfsClusterSelect},
                t.id_po_termin,
                t.termin_no,
                t.termin_value,
                t.status_termin,
                t.invoice_date,
                {$this->getEmrTargetTerminSertifikatSelectSql()}
            {$this->getEmrTargetPoFromSql()}
            LEFT JOIN tb_myrep_po_termin t ON t.id_po_header = p.id_po_header
                AND t.termin_no BETWEEN 1 AND 5
            {$this->buildEmrTargetWhereSql($city, $stageStatus, $regional)}
        ")->result_array();

        if (empty($rows)) {
            return array_values($summary);
        }

        $rfsClusterIds = [];
        foreach ($rows as $row) {
            $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
            if ($rfsClusterId > 0) {
                $rfsClusterIds[] = $rfsClusterId;
            }
        }
        $checklistStateMap = $this->getTerminChecklistStateMap($rfsClusterIds);
        $terminContextMap = $this->buildEmrTargetTerminContextMap($rows);

        foreach ($rows as $row) {
            $terminNo = (int) ($row['termin_no'] ?? 0);
            if ($terminNo < 1 || $terminNo > 5) {
                continue;
            }

            $headerId = (int) ($row['id_po_header'] ?? 0);
            $row = array_merge($row, $terminContextMap[$headerId] ?? []);
            $pic = $this->resolveTerminCurrentPic($row, $checklistStateMap);
            if (!isset($summary[$terminNo]['pic'][$pic])) {
                $summary[$terminNo]['pic'][$pic] = ['count' => 0, 'value' => 0];
            }

            $terminValue = (float) ($row['termin_value'] ?? 0);
            $summary[$terminNo]['total_count']++;
            $summary[$terminNo]['total_value'] += $terminValue;
            $summary[$terminNo]['pic'][$pic]['count']++;
            $summary[$terminNo]['pic'][$pic]['value'] += $terminValue;

        }

        return array_values($summary);
    }

    public function getEmrTargetPoDataTable($city = '', $stageStatus = '', $regional = '', $start = 0, $length = 10, $search = '', $orderColumn = 4, $orderDir = 'desc', $pic = '', $termStage = '', $nroStatus = '')
    {
        if (!$this->emrTargetReady()) {
            return ['recordsTotal' => 0, 'recordsFiltered' => 0, 'rows' => []];
        }

        $picValues = $this->normalizeUpperList($pic);
        $termStageValues = $this->normalizeUpperList($termStage);
        $nroStatusValues = $this->normalizeUpperList($nroStatus);
        $termStageValue = $termStageValues[0] ?? '';
        $hasComputedFilter = !empty($picValues) || $termStageValue !== '' || !empty($nroStatusValues);
        $fromSql = $this->getEmrTargetPoFromSql();
        $whereSql = $this->buildEmrTargetWhereSql($city, $stageStatus, $regional);
        $searchSql = $this->buildEmrTargetSearchSql($search, [
            'p.po_number',
            'p.po_type',
            'p.po_category',
            'p.status_po',
            'c.cluster_name',
            'c.cluster_code',
            'c.city_name',
            'c.regional_name',
            'c.status_current',
            $this->getEmrTargetStageExpression(),
        ]);

        $recordsTotal = (int) ($this->db->query("SELECT COUNT(*) AS total {$fromSql} {$whereSql}")->row_array()['total'] ?? 0);
        $recordsFiltered = (int) ($this->db->query("SELECT COUNT(*) AS total {$fromSql} {$whereSql} {$searchSql}")->row_array()['total'] ?? 0);

        $orderMap = [
            1 => 'p.po_number',
            2 => 'p.po_type',
            3 => 'p.po_category',
            4 => 'p.po_date',
            5 => 'c.cluster_name',
            6 => 'c.city_name',
            7 => 'c.regional_name',
            8 => 'c.status_current',
            9 => $this->getEmrTargetStageExpression(),
            12 => 'p.po_value',
            13 => 'COALESCE(tm.termin_progress_count, 0)',
            14 => 'COALESCE(tm.plan_invoice_total, 0)',
        ];
        $orderSql = $this->buildDataTableOrderSql($orderMap, $orderColumn, $orderDir, 'p.po_date DESC, p.po_number ASC');
        $limitSql = $this->buildLimitSql($start, $length);
        $rfsClusterSelect = $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
            ? 'c.rfs_cluster_id'
            : '0 AS rfs_cluster_id';
        $selectSql = "
            SELECT
                p.id_po_header,
                p.id_myrep_cluster,
                p.po_type,
                p.po_category,
                p.po_number,
                p.po_date,
                p.po_value,
                p.status_po,
                p.on_target,
                p.po_version_label,
                p.remark_po,
                c.cluster_name,
                c.cluster_code,
                c.city_name,
                c.regional_name,
                c.team_name,
                c.status_current,
                {$this->getEmrTargetAreaSelectSql()},
                {$this->getEmrTargetDrmStatusSelectSql()},
                {$this->getEmrTargetAtpSelectSql()},
                {$rfsClusterSelect},
                COALESCE(tm.termin_total_count, 0) AS termin_total_count,
                COALESCE(tm.termin_progress_count, 0) AS termin_progress_count,
                COALESCE(tm.termin_paid_count, 0) AS termin_paid_count,
                COALESCE(tm.plan_invoice_total, 0) AS outstanding_total,
                COALESCE(tm.done_invoice_total, 0) AS total_invoiced,
                {$this->getEmrTargetStageExpression()} AS po_stage_status
            {$fromSql}
            {$whereSql}
        ";

        $footerSummary = [
            'count' => 0,
            'current_termin_value' => 0,
            'po_value' => 0,
            'termin_progress_count' => 0,
            'termin_total_count' => 0,
            'outstanding_total' => 0,
            'total_invoiced' => 0,
        ];

        if ($hasComputedFilter) {
            $totalRows = $this->decoratePoDataTableRowsWithCurrentPic(
                $this->db->query($selectSql)->result_array(),
                $termStageValue
            );
            $totalRows = $this->filterPoRowsByCurrentPic($totalRows, $picValues, $nroStatusValues);
            $recordsTotal = count($totalRows);

            $filteredRows = $this->decoratePoDataTableRowsWithCurrentPic(
                $this->db->query("{$selectSql} {$searchSql} {$orderSql}")->result_array(),
                $termStageValue
            );
            $filteredRows = $this->filterPoRowsByCurrentPic($filteredRows, $picValues, $nroStatusValues);
            $recordsFiltered = count($filteredRows);
            $footerSummary = $this->buildPoDataTableFooterSummary($filteredRows);
            $rows = array_slice($filteredRows, max(0, (int) $start), min(max(0, (int) $length), 100));
        } else {
            $footerRows = $this->decoratePoDataTableRowsWithCurrentPic(
                $this->db->query("{$selectSql} {$searchSql}")->result_array()
            );
            $footerSummary = $this->buildPoDataTableFooterSummary($footerRows);

            $rows = $this->db->query("
                {$selectSql}
                {$searchSql}
                {$orderSql}
                {$limitSql}
            ")->result_array();
            $rows = $this->decoratePoDataTableRowsWithCurrentPic($rows);
        }

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $rows,
            'footer' => $footerSummary,
        ];
    }

    public function getEmrTargetPoTerminReportRows($city = '', $termStage = '', $regional = '', $pic = '', $nroStatus = '')
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $termStageValues = $this->normalizeUpperList($termStage);
        $picValues = $this->normalizeUpperList($pic);
        $nroStatusValues = $this->normalizeUpperList($nroStatus);
        $fromSql = $this->getEmrTargetPoFromSql();
        $whereSql = $this->buildEmrTargetWhereSql($city, '', $regional);
        $rfsClusterSelect = $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
            ? 'c.rfs_cluster_id'
            : '0 AS rfs_cluster_id';

        $rows = $this->db->query("
            SELECT
                p.id_po_header,
                p.id_myrep_cluster,
                p.po_type,
                p.po_category,
                p.po_number,
                p.po_date,
                p.po_value,
                p.status_po,
                p.on_target,
                p.po_version_label,
                p.remark_po,
                c.cluster_name,
                c.cluster_code,
                c.city_name,
                c.regional_name,
                c.team_name,
                c.status_current,
                {$this->getEmrTargetAreaSelectSql()},
                {$this->getEmrTargetDrmStatusSelectSql()},
                {$this->getEmrTargetAtpSelectSql()},
                {$rfsClusterSelect},
                t.id_po_termin,
                t.termin_no,
                t.termin_value,
                t.status_termin,
                t.invoice_date,
                {$this->getEmrTargetTerminSertifikatSelectSql()},
                COALESCE(tm.termin_total_count, 0) AS termin_total_count,
                COALESCE(tm.termin_progress_count, 0) AS termin_progress_count,
                COALESCE(tm.termin_paid_count, 0) AS termin_paid_count,
                COALESCE(tm.plan_invoice_total, 0) AS outstanding_total,
                COALESCE(tm.done_invoice_total, 0) AS total_invoiced
            {$fromSql}
            INNER JOIN tb_myrep_po_termin t ON t.id_po_header = p.id_po_header
                AND t.termin_no BETWEEN 1 AND 5
            {$whereSql}
            ORDER BY p.po_date DESC, p.po_number ASC, t.termin_no ASC
        ")->result_array();

        if (empty($rows)) {
            return [];
        }

        $rfsClusterIds = [];
        foreach ($rows as $row) {
            $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
            if ($rfsClusterId > 0) {
                $rfsClusterIds[] = $rfsClusterId;
            }
        }
        $checklistStateMap = $this->getTerminChecklistStateMap($rfsClusterIds);
        $terminContextMap = $this->buildEmrTargetTerminContextMap($rows);

        $reportRows = [];
        foreach ($rows as $row) {
            $terminNo = (int) ($row['termin_no'] ?? 0);
            $stageLabel = $this->getStageFromTerminNo($terminNo);
            if ($stageLabel === '') {
                continue;
            }
            if (!empty($termStageValues) && !in_array($stageLabel, $termStageValues, true)) {
                continue;
            }

            $row['po_stage_status'] = $stageLabel;
            $row['current_termin_value'] = (float) ($row['termin_value'] ?? 0);
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $row = array_merge($row, $terminContextMap[$headerId] ?? []);
            $row['current_pic'] = $this->resolveTerminCurrentPic($row, $checklistStateMap);
            $row['current_nro_status'] = $this->resolveTerminNroFlowStatus($row, $checklistStateMap);

            if ($row['current_pic'] === 'CLOSED') {
                continue;
            }
            if (!empty($picValues) && !in_array((string) $row['current_pic'], $picValues, true)) {
                continue;
            }
            if (!empty($nroStatusValues) && !in_array((string) $row['current_nro_status'], $nroStatusValues, true)) {
                continue;
            }

            $reportRows[] = $row;
        }

        return $reportRows;
    }

    public function getEmrTargetClusterDataTable($city = '', $stageStatus = '', $regional = '', $start = 0, $length = 10, $search = '', $orderColumn = 1, $orderDir = 'asc')
    {
        if (!$this->emrTargetReady()) {
            return ['recordsTotal' => 0, 'recordsFiltered' => 0, 'rows' => []];
        }

        $fromSql = $this->getEmrTargetPoFromSql();
        $whereSql = $this->buildEmrTargetWhereSql($city, $stageStatus, $regional);
        $searchSql = $this->buildEmrTargetSearchSql($search, [
            'c.cluster_name',
            'c.cluster_code',
            'c.team_name',
            'c.city_name',
            'c.regional_name',
            'c.status_current',
        ]);
        $groupBySql = 'GROUP BY c.id_myrep_cluster, c.cluster_name, c.team_name, c.city_name, c.regional_name, c.status_current';

        $recordsTotal = (int) ($this->db->query("
            SELECT COUNT(*) AS total FROM (
                SELECT c.id_myrep_cluster
                {$fromSql}
                {$whereSql}
                {$groupBySql}
            ) grouped_rows
        ")->row_array()['total'] ?? 0);
        $recordsFiltered = (int) ($this->db->query("
            SELECT COUNT(*) AS total FROM (
                SELECT c.id_myrep_cluster
                {$fromSql}
                {$whereSql}
                {$searchSql}
                {$groupBySql}
            ) grouped_rows
        ")->row_array()['total'] ?? 0);

        $orderMap = [
            1 => 'c.cluster_name',
            2 => 'c.city_name',
            3 => 'c.regional_name',
            4 => 'c.status_current',
            5 => 'COUNT(p.id_po_header)',
            6 => 'COALESCE(SUM(p.po_value), 0)',
            7 => 'COALESCE(SUM(COALESCE(tm.termin_progress_count, 0)), 0)',
            8 => 'MAX(p.po_date)',
        ];
        $orderSql = $this->buildDataTableOrderSql($orderMap, $orderColumn, $orderDir, 'c.cluster_name ASC');
        $limitSql = $this->buildLimitSql($start, $length);

        $rows = $this->db->query("
            SELECT
                c.id_myrep_cluster,
                c.cluster_name,
                c.team_name,
                c.city_name,
                c.regional_name,
                c.status_current,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(p.po_type, 'CLUSTER'))) = 'SUBFEEDER' THEN 0 ELSE 1 END) AS po_cluster_count,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(p.po_type, 'CLUSTER'))) = 'SUBFEEDER' THEN 1 ELSE 0 END) AS po_subfeeder_count,
                COALESCE(SUM(p.po_value), 0) AS po_total_value,
                COALESCE(SUM(COALESCE(tm.termin_total_count, 0)), 0) AS termin_total_count,
                COALESCE(SUM(COALESCE(tm.termin_progress_count, 0)), 0) AS termin_progress_count,
                COALESCE(SUM(COALESCE(tm.termin_paid_count, 0)), 0) AS termin_paid_count,
                MAX(p.po_date) AS last_po_date
            {$fromSql}
            {$whereSql}
            {$searchSql}
            {$groupBySql}
            {$orderSql}
            {$limitSql}
        ")->result_array();

        $latestStageMap = $this->getEmrTargetLatestStageMap(array_column($rows, 'id_myrep_cluster'), $city, $stageStatus, $regional);
        foreach ($rows as &$row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $row['po_stage_status'] = $latestStageMap[$clusterId] ?? 'NOT ISSUED';
        }
        unset($row);

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $rows,
        ];
    }

    public function getEmrTargetClusterById($clusterId)
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return [];
        }

        $row = $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_po_header p', 'p.id_myrep_cluster = c.id_myrep_cluster AND p.on_target = 1', 'inner')
            ->where('c.id_myrep_cluster', $clusterId)
            ->group_by('c.id_myrep_cluster')
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $targetRows = $this->getEmrTargetPoListRows('', '');
        $clusterTargetRows = [];
        foreach ($targetRows as $targetRow) {
            if ((int) ($targetRow['id_myrep_cluster'] ?? 0) === $clusterId) {
                $clusterTargetRows[] = $targetRow;
            }
        }

        $row['po_count'] = count($clusterTargetRows);
        $row['po_total_value'] = array_sum(array_map(static function ($targetRow) {
            return (float) ($targetRow['po_value'] ?? 0);
        }, $clusterTargetRows));

        return $row;
    }

    public function getEmrTargetPoHeadersByClusterId($clusterId)
    {
        if (!$this->emrTargetReady()) {
            return [];
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || empty($this->getEmrTargetClusterById($clusterId))) {
            return [];
        }

        return $this->db
            ->select('*')
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', $clusterId)
            ->where('on_target', 1)
            ->order_by('po_type', 'ASC')
            ->order_by('po_date', 'DESC')
            ->order_by('po_number', 'ASC')
            ->get()
            ->result_array();
    }

    private function decoratePoRowsWithTerminMeta(array $rows, $stageStatus = '')
    {
        if (empty($rows)) {
            return [];
        }

        $headerIds = array_values(array_filter(array_map('intval', array_column($rows, 'id_po_header'))));
        if (empty($headerIds)) {
            return [];
        }

        $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin');
        $this->applyIntWhereInChunks('id_po_header', $headerIds);
        $terminRows = $this->db
            ->get()
            ->result_array();

        $terminMap = [];
        $terminByHeader = [];
        foreach ($terminRows as $termin) {
            $headerId = (int) ($termin['id_po_header'] ?? 0);
            $terminByHeader[$headerId][] = $termin;
            if (!isset($terminMap[$headerId])) {
                $terminMap[$headerId] = [
                    'total' => 0,
                    'progress' => 0,
                    'paid' => 0,
                    'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                    'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                ];
            }

            $terminMap[$headerId]['total']++;
            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            $terminNo = (int) ($termin['termin_no'] ?? 0);
            $terminValue = (float) ($termin['termin_value'] ?? 0);

            if ($terminNo >= 1 && $terminNo <= 5 && !in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                $terminMap[$headerId]['plan_invoice'][$terminNo] = $terminValue;
            }
            if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                $terminMap[$headerId]['progress']++;
                if ($terminNo >= 1 && $terminNo <= 5) {
                    $terminMap[$headerId]['done_invoice'][$terminNo] = $terminValue;
                }
            }
            if ($statusTermin === 'PAID') {
                $terminMap[$headerId]['paid']++;
            }
        }

        $filtered = [];
        $stageStatuses = $this->normalizeUpperList($stageStatus);
        foreach ($rows as $row) {
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $meta = $terminMap[$headerId] ?? [
                'total' => 0,
                'progress' => 0,
                'paid' => 0,
                'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ];
            $row['termin_total_count'] = (int) $meta['total'];
            $row['termin_progress_count'] = (int) $meta['progress'];
            $row['termin_paid_count'] = (int) $meta['paid'];
            $row['plan_invoice_per_termin'] = $meta['plan_invoice'];
            $row['done_invoice_per_termin'] = $meta['done_invoice'];
            $row['plan_invoice_total'] = array_sum($meta['plan_invoice']);
            $row['done_invoice_total'] = array_sum($meta['done_invoice']);
            $row['total_invoiced'] = $row['done_invoice_total'];
            $row['outstanding_total'] = (float) $row['plan_invoice_total'];
            $row['po_stage_status'] = $this->resolveStageStatus($terminByHeader[$headerId] ?? []);

            if (!empty($stageStatuses) && !in_array(strtoupper((string) ($row['po_stage_status'] ?? '')), $stageStatuses, true)) {
                continue;
            }

            $filtered[] = $row;
        }

        return $filtered;
    }

    private function normalizeUpperList($value)
    {
        $items = is_array($value) ? $value : [$value];
        $normalized = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ($this->normalizeUpperList($item) as $nestedItem) {
                    if (!in_array($nestedItem, $normalized, true)) {
                        $normalized[] = $nestedItem;
                    }
                }
                continue;
            }

            $item = strtoupper(trim((string) $item));
            if ($item !== '' && !in_array($item, $normalized, true)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    private function applyUpperInFilter($builder, $column, array $values)
    {
        if (empty($values)) {
            return;
        }

        $escapedValues = [];
        foreach ($values as $value) {
            $escapedValues[] = $this->db->escape($value);
        }

        $builder->where('UPPER(' . $column . ') IN (' . implode(',', $escapedValues) . ')', null, false);
    }

    private function getEmrTargetPoFromSql()
    {
        $sql = "
            FROM tb_myrep_po_header p
            INNER JOIN tb_myrep_cluster c ON c.id_myrep_cluster = p.id_myrep_cluster
            LEFT JOIN ({$this->getEmrTargetTerminAggregateSql()}) tm ON tm.id_po_header = p.id_po_header
        ";

        if ($this->supportsEmrTargetAreaColumn()) {
            $sql .= "
                LEFT JOIN ({$this->getEmrTargetAreaMapSubquery()}) area_map
                    ON area_map.city_key COLLATE utf8mb4_unicode_ci = UPPER(TRIM(c.city_name)) COLLATE utf8mb4_unicode_ci
                    AND (
                        area_map.regional_key = ''
                        OR area_map.regional_key COLLATE utf8mb4_unicode_ci = UPPER(TRIM(COALESCE(c.regional_name, ''))) COLLATE utf8mb4_unicode_ci
                    )
            ";
        }

        if ($this->db->table_exists('tb_myrep_drm')) {
            $sql .= "
                LEFT JOIN tb_myrep_drm d ON d.id_myrep_cluster = c.id_myrep_cluster
            ";
        }

        if ($this->supportsEmrTargetAtpStageData()) {
            $sql .= "
                LEFT JOIN tb_rfs_myrep_cluster rfs ON rfs.id_cluster = c.rfs_cluster_id
                LEFT JOIN (
                    SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                    FROM tb_rfs_myrep_doc_package
                    GROUP BY cluster_id
                ) atp_summary ON atp_summary.cluster_id = rfs.id_cluster
            ";
        }

        return $sql;
    }

    private function getEmrTargetAreaSelectSql()
    {
        return $this->supportsEmrTargetAreaColumn()
            ? "COALESCE(area_map.area_number, '') AS area_number"
            : "'' AS area_number";
    }

    private function getEmrTargetTerminSertifikatSelectSql()
    {
        return $this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')
            ? 't.sertifikat_invoice_date'
            : "'' AS sertifikat_invoice_date";
    }

    private function getEmrTargetAreaMapSubquery()
    {
        $activeCondition = $this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')
            ? 'AND COALESCE(is_active, 1) = 1'
            : '';

        return "
            SELECT
                UPPER(TRIM(city_name)) AS city_key,
                UPPER(TRIM(COALESCE(regional_name, ''))) AS regional_key,
                MAX(
                    CASE
                        WHEN UPPER(TRIM(COALESCE(area, ''))) LIKE 'AREA %'
                            THEN TRIM(SUBSTRING(UPPER(TRIM(COALESCE(area, ''))), 6))
                        ELSE TRIM(COALESCE(area, ''))
                    END
                ) AS area_number
            FROM tb_myrep_pic_mapping_city
            WHERE city_name IS NOT NULL
                AND TRIM(city_name) != ''
                AND area IS NOT NULL
                AND TRIM(area) != ''
                {$activeCondition}
            GROUP BY UPPER(TRIM(city_name)), UPPER(TRIM(COALESCE(regional_name, '')))
        ";
    }

    private function joinEmrTargetAreaMap($builder)
    {
        if (!$this->supportsEmrTargetAreaColumn()) {
            return;
        }

        $builder->join(
            '(' . $this->getEmrTargetAreaMapSubquery() . ') area_map',
            "area_map.city_key COLLATE utf8mb4_unicode_ci = UPPER(TRIM(c.city_name)) COLLATE utf8mb4_unicode_ci
                AND (
                    area_map.regional_key = ''
                    OR area_map.regional_key COLLATE utf8mb4_unicode_ci = UPPER(TRIM(COALESCE(c.regional_name, ''))) COLLATE utf8mb4_unicode_ci
                )",
            'left',
            false
        );
    }

    private function supportsEmrTargetAreaColumn()
    {
        return $this->db->table_exists('tb_myrep_pic_mapping_city')
            && $this->db->field_exists('city_name', 'tb_myrep_pic_mapping_city')
            && $this->db->field_exists('area', 'tb_myrep_pic_mapping_city');
    }

    private function getEmrTargetDrmStatusSelectSql()
    {
        return $this->db->table_exists('tb_myrep_drm')
            ? "COALESCE(d.status_drm, '') AS status_drm"
            : "'' AS status_drm";
    }

    private function getEmrTargetAtpSelectSql()
    {
        if (!$this->supportsEmrTargetAtpStageData()) {
            return "
                NULL AS email_atp_date,
                NULL AS actual_atp_date,
                '' AS status_atp,
                '' AS stage_atp
            ";
        }

        return "
            rfs.email_atp_date,
            atp_summary.actual_atp_date,
            COALESCE(rfs.status_atp, '') AS status_atp,
            {$this->getEmrTargetAtpStageExpression()} AS stage_atp
        ";
    }

    private function getEmrTargetAtpStageExpression()
    {
        return "
            CASE
                WHEN UPPER(TRIM(COALESCE(rfs.status_atp, ''))) = 'DONE' THEN 'ATP DONE'
                WHEN UPPER(TRIM(COALESCE(rfs.status_atp, ''))) = 'PUNCLIST' THEN 'ATP PUNCLIST'
                WHEN rfs.email_atp_date IS NULL OR rfs.email_atp_date IN ('0000-00-00', '0000-00-00 00:00:00') THEN 'WAITING EMAIL'
                WHEN atp_summary.actual_atp_date IS NULL OR atp_summary.actual_atp_date IN ('0000-00-00', '0000-00-00 00:00:00') THEN 'WAITING JADWAL ATP'
                WHEN CURDATE() < DATE(atp_summary.actual_atp_date) THEN 'WAITING ATP'
                WHEN CURDATE() = DATE(atp_summary.actual_atp_date) THEN 'PROSES ATP'
                ELSE 'WAITING STATUS ATP'
            END
        ";
    }

    private function supportsEmrTargetAtpStageData()
    {
        return $this->db->table_exists('tb_rfs_myrep_cluster')
            && $this->db->table_exists('tb_rfs_myrep_doc_package')
            && $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
            && $this->db->field_exists('email_atp_date', 'tb_rfs_myrep_cluster')
            && $this->db->field_exists('status_atp', 'tb_rfs_myrep_cluster')
            && $this->db->field_exists('actual_atp_date', 'tb_rfs_myrep_doc_package');
    }

    private function getEmrTargetTerminAggregateSql()
    {
        return "
            SELECT
                id_po_header,
                COUNT(*) AS termin_total_count,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS termin_progress_count,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) = 'PAID' THEN 1 ELSE 0 END) AS termin_paid_count,
                SUM(CASE WHEN termin_no = 1 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS term_1_done,
                SUM(CASE WHEN termin_no = 2 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS term_2_done,
                SUM(CASE WHEN termin_no = 3 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS term_3_done,
                SUM(CASE WHEN termin_no = 4 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS term_4_done,
                SUM(CASE WHEN termin_no = 5 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN 1 ELSE 0 END) AS term_5_done,
                SUM(CASE WHEN termin_no = 1 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_1,
                SUM(CASE WHEN termin_no = 2 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_2,
                SUM(CASE WHEN termin_no = 3 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_3,
                SUM(CASE WHEN termin_no = 4 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_4,
                SUM(CASE WHEN termin_no = 5 AND UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_5,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) NOT IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS plan_invoice_total,
                SUM(CASE WHEN UPPER(TRIM(COALESCE(status_termin, 'NOT READY'))) IN ('BILLED', 'PAID') THEN COALESCE(termin_value, 0) ELSE 0 END) AS done_invoice_total
            FROM tb_myrep_po_termin
            GROUP BY id_po_header
        ";
    }

    private function getEmrTargetStageExpression()
    {
        return "
            CASE
                WHEN COALESCE(tm.termin_total_count, 0) = 0 THEN 'NOT ISSUED'
                WHEN COALESCE(tm.term_1_done, 0) = 0 THEN 'DP'
                WHEN COALESCE(tm.term_2_done, 0) = 0 THEN 'ATP CW'
                WHEN COALESCE(tm.term_3_done, 0) = 0 THEN 'FULL OPM'
                WHEN COALESCE(tm.term_4_done, 0) = 0 THEN 'RFS'
                WHEN COALESCE(tm.term_5_done, 0) = 0 THEN 'FAC'
                ELSE 'CLOSED'
            END
        ";
    }

    private function buildEmrTargetWhereSql($city = '', $stageStatus = '', $regional = '', array $extraConditions = [])
    {
        $conditions = ['p.on_target = 1'];
        $city = $this->normalizeUpperList($city);
        $areaNumbers = $this->resolveEmrTargetAreaNumbers($regional);
        $regional = $this->resolveEmrTargetAreaRegionalValues($regional);
        $stageStatus = $this->normalizeUpperList($stageStatus);

        if (!empty($city)) {
            $conditions[] = 'UPPER(c.city_name) IN (' . $this->buildEscapedSqlList($city) . ')';
        }
        if (!empty($regional)) {
            if ($this->supportsEmrTargetAreaColumn() && !empty($areaNumbers)) {
                $conditions[] = '(
                    UPPER(area_map.area_number) IN (' . $this->buildEscapedSqlList($areaNumbers) . ')
                    OR UPPER(c.regional_name) IN (' . $this->buildEscapedSqlList($regional) . ')
                )';
            } else {
                $conditions[] = 'UPPER(c.regional_name) IN (' . $this->buildEscapedSqlList($regional) . ')';
            }
        }
        if (!empty($stageStatus)) {
            $conditions[] = '(' . $this->getEmrTargetStageExpression() . ') IN (' . $this->buildEscapedSqlList($stageStatus) . ')';
        }

        foreach ($extraConditions as $condition) {
            if (trim((string) $condition) !== '') {
                $conditions[] = (string) $condition;
            }
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    public function getEmrTargetAreaFromRegionalName($regionalName)
    {
        return $this->resolveEmrTargetAreaFromRegional($regionalName);
    }

    public function getEmrTargetAreaLabel($areaNumber = '', $regionalName = '')
    {
        return $this->resolveEmrTargetAreaLabel($areaNumber, $regionalName);
    }

    private function resolveEmrTargetAreaNumbers($areaValues)
    {
        $values = $this->normalizeUpperList($areaValues);
        if (empty($values)) {
            return [];
        }

        $numbers = [];
        foreach ($values as $value) {
            $number = $this->normalizeEmrTargetAreaNumber($value);
            if ($number !== '') {
                $numbers[] = $number;
            }
        }

        return array_values(array_unique($numbers));
    }

    private function resolveEmrTargetAreaRegionalValues($areaValues)
    {
        $values = $this->normalizeUpperList($areaValues);
        if (empty($values)) {
            return [];
        }

        $regionals = [];
        foreach ($values as $value) {
            if (isset($this->emrTargetAreaRegionalMap[$value])) {
                $regionals = array_merge($regionals, $this->emrTargetAreaRegionalMap[$value]);
                continue;
            }

            $regionals[] = $value;
        }

        return array_values(array_unique($regionals));
    }

    private function resolveEmrTargetAreaFromRegional($regionalName)
    {
        $regionalName = strtoupper(trim((string) $regionalName));
        if ($regionalName === '') {
            return '';
        }

        foreach ($this->emrTargetAreaRegionalMap as $areaName => $regionals) {
            if (in_array($regionalName, $regionals, true)) {
                return $areaName;
            }
        }

        return '';
    }

    private function resolveEmrTargetAreaLabel($areaNumber = '', $regionalName = '')
    {
        $number = $this->normalizeEmrTargetAreaNumber($areaNumber);
        if ($number !== '') {
            return 'AREA ' . $number;
        }

        return $this->resolveEmrTargetAreaFromRegional($regionalName);
    }

    private function normalizeEmrTargetAreaNumber($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return '';
        }

        if (strpos($value, 'AREA ') === 0) {
            $value = trim(substr($value, 5));
        }

        if (strpos($value, 'REGIONAL ') === 0) {
            $value = trim(substr($value, 9));
        }

        return in_array($value, ['1', '2', '3', '4', '5'], true) ? $value : '';
    }

    private function buildEmrTargetSearchSql($search, array $columns)
    {
        $search = strtoupper(trim((string) $search));
        if ($search === '') {
            return '';
        }

        $like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%');
        $conditions = [];
        foreach ($columns as $column) {
            $column = trim((string) $column);
            if ($column !== '') {
                $conditions[] = 'UPPER(' . $column . ') LIKE ' . $like;
            }
        }

        return empty($conditions) ? '' : 'AND (' . implode(' OR ', $conditions) . ')';
    }

    private function buildEscapedSqlList(array $values)
    {
        $escapedValues = [];
        foreach ($values as $value) {
            $escapedValues[] = $this->db->escape($value);
        }

        return implode(',', $escapedValues);
    }

    private function buildDataTableOrderSql(array $orderMap, $orderColumn, $orderDir, $defaultOrder)
    {
        $orderColumn = (int) $orderColumn;
        $orderDir = strtolower((string) $orderDir) === 'asc' ? 'ASC' : 'DESC';
        $orderBy = $orderMap[$orderColumn] ?? '';

        if ($orderBy === '') {
            return 'ORDER BY ' . $defaultOrder;
        }

        return 'ORDER BY ' . $orderBy . ' ' . $orderDir;
    }

    private function buildLimitSql($start, $length)
    {
        $start = max(0, (int) $start);
        $length = (int) $length;
        if ($length <= 0) {
            return '';
        }

        $length = min($length, 100);
        return 'LIMIT ' . $start . ', ' . $length;
    }

    private function getEmrTargetLatestStageMap(array $clusterIds, $city = '', $stageStatus = '', $regional = '')
    {
        $clusterIds = array_values(array_filter(array_map('intval', $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $extraConditions = ['p.id_myrep_cluster IN (' . implode(',', $clusterIds) . ')'];
        $rows = $this->db->query("
            SELECT
                p.id_myrep_cluster,
                p.po_date,
                p.id_po_header,
                {$this->getEmrTargetStageExpression()} AS po_stage_status
            {$this->getEmrTargetPoFromSql()}
            {$this->buildEmrTargetWhereSql($city, $stageStatus, $regional, $extraConditions)}
            ORDER BY p.id_myrep_cluster ASC, p.po_date DESC, p.id_po_header DESC
        ")->result_array();

        $map = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if ($clusterId > 0 && !isset($map[$clusterId])) {
                $map[$clusterId] = (string) ($row['po_stage_status'] ?? 'NOT ISSUED');
            }
        }

        return $map;
    }

    public function getDashboardSummary($rows)
    {
        $summary = [
            'total_cluster' => 0,
            'not_issued' => 0,
            'issued' => 0,
            'partial_payment' => 0,
            'fully_paid' => 0,
            'closed' => 0,
        ];

        foreach ($rows as $row) {
            $summary['total_cluster']++;
            $status = strtoupper(trim((string) ($row['po_summary_status'] ?? 'NOT ISSUED')));
            if ($status === 'ISSUED') {
                $summary['issued']++;
            } elseif ($status === 'PARTIAL PAYMENT') {
                $summary['partial_payment']++;
            } elseif ($status === 'FULLY PAID') {
                $summary['fully_paid']++;
            } elseif ($status === 'CLOSED') {
                $summary['closed']++;
            } else {
                $summary['not_issued']++;
            }
        }

        return $summary;
    }

    public function getTerminBreakdownByType($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('p.id_po_header, p.po_type, p.po_value')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $headerRows = $this->db->get()->result_array();
        if (empty($headerRows)) {
            return [];
        }

        $headerIds = array_values(array_filter(array_map('intval', array_column($headerRows, 'id_po_header'))));
        if (empty($headerIds)) {
            return [];
        }

        $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin');
        $this->applyIntWhereInChunks('id_po_header', $headerIds);
        $terminRows = $this->db
            ->get()
            ->result_array();

        $headerMeta = [];
        foreach ($headerRows as $headerRow) {
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $headerMeta[$headerId] = [
                'po_type' => strtoupper(trim((string) ($headerRow['po_type'] ?? 'CLUSTER'))),
                'po_value' => (float) ($headerRow['po_value'] ?? 0),
            ];
        }

        $result = [
            'CLUSTER' => [
                'po_type' => 'CLUSTER',
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
            'SUBFEEDER' => [
                'po_type' => 'SUBFEEDER',
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
        ];

        foreach ($headerMeta as $meta) {
            $type = $meta['po_type'] === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            $result[$type]['total_po_value'] += (float) $meta['po_value'];
        }

        foreach ($terminRows as $terminRow) {
            $headerId = (int) ($terminRow['id_po_header'] ?? 0);
            if (!isset($headerMeta[$headerId])) {
                continue;
            }

            $type = $headerMeta[$headerId]['po_type'] === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            $terminNo = (int) ($terminRow['termin_no'] ?? 0);
            $terminValue = (float) ($terminRow['termin_value'] ?? 0);
            $terminStatus = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));

            if (in_array($terminStatus, ['BILLED', 'PAID'], true)) {
                $result[$type]['term_done_count']++;
                $result[$type]['total_invoiced_value'] += $terminValue;
            } elseif ($terminNo >= 1 && $terminNo <= 5) {
                // Outstanding per termin: hanya yang belum billed/paid.
                $result[$type]['termin_values'][$terminNo] += $terminValue;
                $result[$type]['outstanding_value'] += $terminValue;
            }
        }

        return array_values($result);
    }

    public function getTerminBreakdownDetailRows($city = '', $status = '', $poType = 'CLUSTER', $metric = '', $termNo = 0)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $poType = strtoupper(trim((string) $poType));
        if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $poType = 'CLUSTER';
        }

        $this->db
            ->select('
                p.id_po_header,
                p.po_number,
                p.po_date,
                p.po_type,
                p.po_value,
                p.status_po,
                c.id_myrep_cluster,
                c.cluster_name,
                c.city_name,
                c.regional_name
            ')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('UPPER(p.po_type)', $poType);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $headerRows = $this->db
            ->order_by('p.po_date', 'DESC')
            ->order_by('p.po_number', 'ASC')
            ->get()
            ->result_array();

        if (empty($headerRows)) {
            return [];
        }

        $headerMap = [];
        $headerIds = [];
        foreach ($headerRows as $headerRow) {
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $headerIds[] = $headerId;
            $headerMap[$headerId] = $headerRow;
        }

        $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin');
        $this->applyIntWhereInChunks('id_po_header', $headerIds);
        $terminRows = $this->db
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();

        $detailRows = [];
        $metric = strtolower(trim((string) $metric));
        $termNo = (int) $termNo;

        if ($metric === 'po_qty' || $metric === 'total_po') {
            foreach ($headerRows as $headerRow) {
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => null,
                    'status_termin' => null,
                    'amount' => $metric === 'po_qty' ? 1 : (float) ($headerRow['po_value'] ?? 0),
                ];
            }
            return $detailRows;
        }

        if ($metric === 'term_done') {
            foreach ($terminRows as $terminRow) {
                $headerId = (int) ($terminRow['id_po_header'] ?? 0);
                if (!isset($headerMap[$headerId])) {
                    continue;
                }
                $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
                if (!in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                    continue;
                }
                $headerRow = $headerMap[$headerId];
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => (int) ($terminRow['termin_no'] ?? 0),
                    'status_termin' => $statusTermin,
                    'amount' => 1,
                ];
            }
            return $detailRows;
        }

        if ($metric === 'outstanding_total') {
            $outstandingPerPo = [];
            foreach ($terminRows as $terminRow) {
                $headerId = (int) ($terminRow['id_po_header'] ?? 0);
                $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
                if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                    continue;
                }
                if (!isset($outstandingPerPo[$headerId])) {
                    $outstandingPerPo[$headerId] = 0;
                }
                $outstandingPerPo[$headerId] += (float) ($terminRow['termin_value'] ?? 0);
            }
            foreach ($outstandingPerPo as $headerId => $amount) {
                if ($amount == 0 || !isset($headerMap[$headerId])) {
                    continue;
                }
                $headerRow = $headerMap[$headerId];
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => null,
                    'status_termin' => 'OUTSTANDING',
                    'amount' => (float) $amount,
                ];
            }
            return $detailRows;
        }

        foreach ($terminRows as $terminRow) {
            $headerId = (int) ($terminRow['id_po_header'] ?? 0);
            if (!isset($headerMap[$headerId])) {
                continue;
            }
            $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
            $terminNoRow = (int) ($terminRow['termin_no'] ?? 0);
            $include = false;

            if ($metric === 'total_invoiced') {
                $include = in_array($statusTermin, ['BILLED', 'PAID'], true);
            } elseif ($metric === 'outstanding_term' && $termNo >= 1 && $termNo <= 5) {
                $include = ($terminNoRow === $termNo && !in_array($statusTermin, ['BILLED', 'PAID'], true));
            }

            if (!$include) {
                continue;
            }

            $headerRow = $headerMap[$headerId];
            $detailRows[] = [
                'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                'po_date' => (string) ($headerRow['po_date'] ?? ''),
                'termin_no' => $terminNoRow,
                'status_termin' => $statusTermin,
                'amount' => (float) ($terminRow['termin_value'] ?? 0),
            ];
        }

        return $detailRows;
    }

    public function getClusterById($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $row = $this->db->get()->row_array();

        if (empty($row)) {
            return [];
        }

        return array_merge($row, $this->getPoMetaMap([(int) $clusterId])[(int) $clusterId] ?? $this->buildEmptyMeta());
    }

    public function getPoHeadersByClusterId($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        if (empty($this->getClusterById((int) $clusterId))) {
            return [];
        }

        return $this->db
            ->select('*')
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->order_by('po_type', 'ASC')
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();
    }

    public function getTerminRowsByPoId($poId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        return $this->db
            ->select('*')
            ->from('tb_myrep_po_termin')
            ->where('id_po_header', (int) $poId)
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getTerminById($terminId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('t.*, p.id_myrep_cluster, p.po_number, p.po_type, p.po_category')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header p', 'p.id_po_header = t.id_po_header', 'inner')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('t.id_po_termin', (int) $terminId);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $this->db->get()->row_array();
    }

    public function createPoHeader($clusterId, $payload)
    {
        if (!$this->tablesReady()) {
            return 0;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return 0;
        }

        if (empty($this->getClusterById($clusterId))) {
            return 0;
        }

        $poValue = (float) ($payload['po_value'] ?? 0);

        $this->db->trans_start();

        $headerPayload = [
            'id_myrep_cluster' => $clusterId,
            'parent_po_header_id' => !empty($payload['parent_po_header_id']) ? (int) $payload['parent_po_header_id'] : null,
            'po_type' => (string) $payload['po_type'],
            'po_category' => (string) $payload['po_category'],
            'po_number' => (string) $payload['po_number'],
            'po_date' => $payload['po_date'],
            'po_value' => $poValue,
            'status_po' => (string) $payload['status_po'],
            'po_version_label' => $payload['po_version_label'] !== '' ? (string) $payload['po_version_label'] : null,
            'remark_po' => $payload['remark_po'] !== '' ? (string) $payload['remark_po'] : null,
            'created_by' => (int) $payload['created_by'],
            'updated_by' => (int) $payload['updated_by'],
        ];
        if ($this->db->field_exists('on_target', 'tb_myrep_po_header')) {
            $headerPayload['on_target'] = array_key_exists('on_target', $payload) ? (int) $payload['on_target'] : 1;
        }

        $this->db->insert('tb_myrep_po_header', $headerPayload);

        $poHeaderId = (int) $this->db->insert_id();
        foreach ($this->defaultTerminPercents as $index => $percent) {
            $terminNo = $index + 1;
            $terminValue = round(($poValue * $percent) / 100, 2);
            $this->db->insert('tb_myrep_po_termin', [
                'id_po_header' => $poHeaderId,
                'termin_no' => $terminNo,
                'termin_percent' => $percent,
                'termin_value' => $terminValue,
                'status_termin' => 'NOT READY',
                'created_by' => (int) $payload['created_by'],
                'updated_by' => (int) $payload['updated_by'],
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $poHeaderId : 0;
    }

    public function updateTermin($terminId, $payload)
    {
        if (!$this->tablesReady()) {
            return false;
        }

        $this->db
            ->where('id_po_termin', (int) $terminId)
            ->update('tb_myrep_po_termin', [
                'status_termin' => (string) $payload['status_termin'],
                'invoice_number' => $payload['invoice_number'] !== '' ? (string) $payload['invoice_number'] : null,
                'invoice_date' => $payload['invoice_date'],
                'bast_date' => $payload['bast_date'],
                'payment_date' => $payload['payment_date'],
                'remark_termin' => $payload['remark_termin'] !== '' ? (string) $payload['remark_termin'] : null,
                'updated_by' => (int) $payload['updated_by'],
            ]);

        $termin = $this->getTerminById((int) $terminId);
        if (!empty($termin['id_po_header'])) {
            $this->syncPoStatus((int) $termin['id_po_header']);
        }

        return $this->db->affected_rows() >= 0;
    }

    private function syncPoStatus($poHeaderId)
    {
        $termins = $this->getTerminRowsByPoId((int) $poHeaderId);
        if (empty($termins)) {
            return;
        }

        $statuses = array_map(static function ($row) {
            return strtoupper(trim((string) ($row['status_termin'] ?? 'NOT READY')));
        }, $termins);

        $poStatus = 'NOT ISSUED';
        if (count(array_filter($statuses, static function ($status) {
            return $status === 'PAID';
        })) === count($statuses)) {
            $poStatus = 'FULLY PAID';
        } elseif (count(array_filter($statuses, static function ($status) {
            return in_array($status, ['BILLED', 'PAID'], true);
        })) > 0) {
            $poStatus = 'PARTIAL PAYMENT';
        } elseif (count(array_filter($statuses, static function ($status) {
            return in_array($status, ['READY BILLING', 'BILLED', 'PAID'], true);
        })) > 0) {
            $poStatus = 'ISSUED';
        }

        $this->db
            ->where('id_po_header', (int) $poHeaderId)
            ->update('tb_myrep_po_header', [
                'status_po' => $poStatus,
            ]);
    }

    private function buildEmptyMeta()
    {
        return [
            'po_count' => 0,
            'po_cluster_count' => 0,
            'po_subfeeder_count' => 0,
            'po_total_value' => 0,
            'termin_total_count' => 0,
            'termin_progress_count' => 0,
            'termin_paid_count' => 0,
            'last_po_date' => null,
            'po_summary_status' => 'NOT ISSUED',
            'po_stage_status' => 'NOT ISSUED',
        ];
    }

    private function getPoMetaMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $this->db
            ->select('id_po_header, id_myrep_cluster, po_type, po_value, status_po, po_date')
            ->from('tb_myrep_po_header');
        $this->applyIntWhereInChunks('id_myrep_cluster', $clusterIds);
        $headerRows = $this->db
            ->get()
            ->result_array();

        $headerIds = array_column($headerRows, 'id_po_header');
        $terminRows = [];
        if (!empty($headerIds)) {
            $this->db
                ->select('id_po_header, termin_no, status_termin')
                ->from('tb_myrep_po_termin');
            $this->applyIntWhereInChunks('id_po_header', $headerIds);
            $terminRows = $this->db
                ->get()
                ->result_array();
        }

        $terminGrouped = [];
        foreach ($terminRows as $terminRow) {
            $terminGrouped[(int) ($terminRow['id_po_header'] ?? 0)][] = [
                'termin_no' => (int) ($terminRow['termin_no'] ?? 0),
                'status_termin' => strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY'))),
            ];
        }

        $metaMap = [];
        foreach ($clusterIds as $clusterId) {
            $metaMap[$clusterId] = $this->buildEmptyMeta();
        }

        foreach ($headerRows as $headerRow) {
            $clusterId = (int) ($headerRow['id_myrep_cluster'] ?? 0);
            if (!isset($metaMap[$clusterId])) {
                $metaMap[$clusterId] = $this->buildEmptyMeta();
            }

            $metaMap[$clusterId]['po_count']++;
            $metaMap[$clusterId]['po_total_value'] += (float) ($headerRow['po_value'] ?? 0);
            $poType = strtoupper(trim((string) ($headerRow['po_type'] ?? '')));
            if ($poType === 'CLUSTER') {
                $metaMap[$clusterId]['po_cluster_count']++;
            } elseif ($poType === 'SUBFEEDER') {
                $metaMap[$clusterId]['po_subfeeder_count']++;
            }

            if (!empty($headerRow['po_date'])) {
                if (empty($metaMap[$clusterId]['last_po_date']) || $headerRow['po_date'] > $metaMap[$clusterId]['last_po_date']) {
                    $metaMap[$clusterId]['last_po_date'] = $headerRow['po_date'];
                }
            }

            $termins = $terminGrouped[(int) ($headerRow['id_po_header'] ?? 0)] ?? [];
            $metaMap[$clusterId]['termin_total_count'] += count($termins);
            $metaMap[$clusterId]['termin_progress_count'] += count(array_filter($termins, static function ($termin) {
                return in_array((string) ($termin['status_termin'] ?? 'NOT READY'), ['BILLED', 'PAID'], true);
            }));
            $metaMap[$clusterId]['termin_paid_count'] += count(array_filter($termins, static function ($termin) {
                return (string) ($termin['status_termin'] ?? 'NOT READY') === 'PAID';
            }));
        }

        // Stage status diambil dari PO terbaru per cluster.
        $latestHeaderByCluster = [];
        foreach ($headerRows as $headerRow) {
            $clusterId = (int) ($headerRow['id_myrep_cluster'] ?? 0);
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $poDate = (string) ($headerRow['po_date'] ?? '');
            if (!isset($latestHeaderByCluster[$clusterId])) {
                $latestHeaderByCluster[$clusterId] = ['id_po_header' => $headerId, 'po_date' => $poDate];
                continue;
            }
            $current = $latestHeaderByCluster[$clusterId];
            if ($poDate > (string) ($current['po_date'] ?? '') || ($poDate === (string) ($current['po_date'] ?? '') && $headerId > (int) ($current['id_po_header'] ?? 0))) {
                $latestHeaderByCluster[$clusterId] = ['id_po_header' => $headerId, 'po_date' => $poDate];
            }
        }

        foreach ($metaMap as $clusterId => &$meta) {
            if ($meta['po_count'] === 0) {
                $meta['po_summary_status'] = 'NOT ISSUED';
            } elseif ($meta['termin_total_count'] > 0 && $meta['termin_paid_count'] === $meta['termin_total_count']) {
                $meta['po_summary_status'] = 'FULLY PAID';
            } elseif ($meta['termin_progress_count'] > 0) {
                $meta['po_summary_status'] = 'PARTIAL PAYMENT';
            } else {
                $meta['po_summary_status'] = 'ISSUED';
            }

            $latestHeaderId = (int) ($latestHeaderByCluster[$clusterId]['id_po_header'] ?? 0);
            $latestTermins = $latestHeaderId > 0 ? ($terminGrouped[$latestHeaderId] ?? []) : [];
            $meta['po_stage_status'] = $this->resolveStageStatus($latestTermins);
        }
        unset($meta);

        return $metaMap;
    }

    private function resolveStageStatus(array $termins)
    {
        if (empty($termins)) {
            return 'NOT ISSUED';
        }

        $statusByTermin = [];
        foreach ($termins as $termin) {
            $no = (int) ($termin['termin_no'] ?? 0);
            if ($no < 1 || $no > 5) {
                continue;
            }
            $statusByTermin[$no] = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
        }

        $labels = [
            1 => 'DP',
            2 => 'ATP CW',
            3 => 'FULL OPM',
            4 => 'RFS',
            5 => 'FAC',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $status = $statusByTermin[$i] ?? 'NOT READY';
            if (!in_array($status, ['BILLED', 'PAID'], true)) {
                return $labels[$i];
            }
        }

        return 'CLOSED';
    }

    private function buildEmptyTerminPicSummary()
    {
        $picLabels = [
            'TKM - AREA',
            'TKM - HO',
            'EMMR - AREA',
            'EMMR - DC',
            'EMMR - DOKUMEN PERMIT',
            'EMMR - LOGISTIK',
            'EMMR - PLANNING',
            'EMMR - TEAM LEADER',
            'EMMR - WASPANG',
            'WAITING CW ATP',
            'WAITING FAC',
            'FAC BELUM JATUH TEMPO',
            'TKM - FINANCE',
            'CLOSED',
        ];
        $stageLabels = [
            1 => 'DP',
            2 => 'ATP CW',
            3 => 'FULL OPM',
            4 => 'RFS',
            5 => 'FAC',
        ];

        $summary = [];
        foreach ($stageLabels as $terminNo => $stageLabel) {
            $summary[$terminNo] = [
                'termin_no' => $terminNo,
                'stage' => $stageLabel,
                'total_count' => 0,
                'total_value' => 0,
                'pic' => [],
            ];
            foreach ($picLabels as $picLabel) {
                $summary[$terminNo]['pic'][$picLabel] = ['count' => 0, 'value' => 0];
            }
        }

        return $summary;
    }

    private function buildEmptyNroFlowSummary()
    {
        $summary = [];
        foreach (['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'] as $status) {
            $summary[$status] = ['count' => 0, 'value' => 0];
        }

        return $summary;
    }

    private function resolveTerminCurrentPic(array $row, array $checklistStateMap)
    {
        $statusTermin = strtoupper(trim((string) ($row['status_termin'] ?? 'NOT READY')));
        if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
            return 'CLOSED';
        }

        $terminNo = (int) ($row['termin_no'] ?? 0);
        if ($terminNo === 1) {
            return 'TKM - FINANCE';
        }

        if ($terminNo === 5) {
            return $this->resolveFacTerminPic($row);
        }

        if ($this->isTerminPermitDocument($row)) {
            return 'EMMR - DOKUMEN PERMIT';
        }

        if ($this->isWaitingCwAtpTermin($row)) {
            return 'WAITING CW ATP';
        }

        if ($this->isTerminCertificateReleasedWithoutInvoice($row)) {
            return 'TKM - FINANCE';
        }

        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        if ($terminNo === 4 && !in_array($statusCurrent, ['RFS', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            return 'TKM - AREA';
        }

        $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
        if ($rfsClusterId <= 0) {
            return $this->resolvePicFromClusterAndAtpStage($row);
        }

        $scopeType = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER'))) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
        $sowType = $this->getTerminSowType($terminNo);
        if ($sowType === '') {
            return $this->resolvePicFromClusterAndAtpStage($row);
        }

        $stateKey = $this->buildTerminChecklistStateKey($rfsClusterId, $scopeType, $sowType);
        $state = $checklistStateMap[$stateKey] ?? [];
        $required = (int) ($state['required'] ?? 0);
        if ($required <= 0) {
            return $this->resolvePicFromClusterAndAtpStage($row);
        }

        $uploaded = (int) ($state['uploaded'] ?? 0);
        $approved = (int) ($state['approved'] ?? 0);
        $astriApproved = (int) ($state['astri_approved'] ?? 0);

        if ($uploaded < $required) {
            return 'EMMR - AREA';
        }
        if ($approved < $required) {
            return $sowType === 'RFS' ? 'EMMR - WASPANG' : 'TKM - AREA';
        }
        if ($sowType === 'RFS' && !empty($state['has_project_opname_nro_flow'])) {
            $nroPic = $this->mapNroFlowStatusToPic((string) ($state['project_opname_nro_status'] ?? ''));
            if ($nroPic !== '') {
                return $nroPic;
            }

            return 'EMMR - WASPANG';
        }
        if ($astriApproved >= $required) {
            return 'TKM - HO';
        }

        return 'EMMR - DC';
    }

    private function resolvePicFromClusterAndAtpStage(array $row)
    {
        $terminNo = (int) ($row['termin_no'] ?? 0);
        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        if ($terminNo === 4 && !in_array($statusCurrent, ['RFS', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
            return 'TKM - AREA';
        }

        $displayStatus = $this->resolveClusterDetailedStatus($row);
        if ($displayStatus === 'IMPLEMENTASI') {
            return 'EMMR - AREA';
        }
        if (in_array($displayStatus, ['ATP PUNCLIST', 'PUNCLIST'], true)) {
            return 'TKM - AREA';
        }
        if ($displayStatus === 'WAITING EMAIL') {
            return 'TKM - HO';
        }
        if (in_array($displayStatus, ['WAITING JADWAL', 'WAITING JADWAL ATP', 'WAITING ATP', 'PROSES ATP', 'ON PROSES ATP', 'WAITING STATUS ATP'], true)) {
            return 'EMMR - AREA';
        }

        $stageAtp = strtoupper(trim((string) ($row['stage_atp'] ?? '')));
        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));
        if ($statusAtp === 'PUNCLIST' || in_array($stageAtp, ['ATP PUNCLIST', 'PUNCLIST'], true)) {
            return 'TKM - AREA';
        }

        if ($stageAtp === 'WAITING EMAIL') {
            return 'TKM - HO';
        }

        if (in_array($stageAtp, ['WAITING JADWAL', 'WAITING JADWAL ATP', 'WAITING ATP', 'PROSES ATP', 'ON PROSES ATP', 'WAITING STATUS ATP'], true)) {
            return 'EMMR - AREA';
        }

        return 'EMMR - AREA';
    }

    private function resolveFacTerminPic(array $row)
    {
        if ($this->normalizeEmrTargetDateValue((string) ($row['term_4_sertifikat_invoice_date'] ?? '')) === '') {
            return 'WAITING FAC';
        }

        if (!$this->isFacTerminDue($row)) {
            return 'FAC BELUM JATUH TEMPO';
        }

        return 'TKM - FINANCE';
    }

    private function isTerminCertificateReleasedWithoutInvoice(array $row)
    {
        $sertifikatDate = $this->normalizeEmrTargetDateValue((string) ($row['sertifikat_invoice_date'] ?? ''));
        if ($sertifikatDate === '') {
            return false;
        }

        return $this->normalizeEmrTargetDateValue((string) ($row['invoice_date'] ?? '')) === '';
    }

    private function isTerminPermitDocument(array $row)
    {
        $terminNo = (int) ($row['termin_no'] ?? 0);
        if ($terminNo < 2 || $terminNo > 5) {
            return false;
        }

        $sertifikatValue = strtoupper(trim((string) ($row['sertifikat_invoice_date'] ?? '')));
        if (strpos($sertifikatValue, 'PERMIT') !== false) {
            return true;
        }

        if ($terminNo === 3) {
            $term2SertifikatValue = strtoupper(trim((string) ($row['term_2_sertifikat_invoice_date'] ?? '')));
            return strpos($term2SertifikatValue, 'PERMIT') !== false;
        }

        return false;
    }

    private function isWaitingCwAtpTermin(array $row)
    {
        if ((int) ($row['termin_no'] ?? 0) !== 3) {
            return false;
        }

        $term3SertifikatDate = $this->normalizeEmrTargetDateValue((string) ($row['term_3_sertifikat_invoice_date'] ?? ''));
        if ($term3SertifikatDate === '') {
            return false;
        }

        return $this->normalizeEmrTargetDateValue((string) ($row['term_2_invoice_date'] ?? '')) === '';
    }

    private function buildEmrTargetTerminContextMap(array $rows)
    {
        $contextMap = [];
        foreach ($rows as $row) {
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $terminNo = (int) ($row['termin_no'] ?? 0);
            if ($headerId <= 0 || $terminNo < 1 || $terminNo > 5) {
                continue;
            }

            if (!isset($contextMap[$headerId])) {
                $contextMap[$headerId] = [];
            }

            $contextMap[$headerId]['term_' . $terminNo . '_invoice_date'] = (string) ($row['invoice_date'] ?? '');
            $contextMap[$headerId]['term_' . $terminNo . '_sertifikat_invoice_date'] = (string) ($row['sertifikat_invoice_date'] ?? '');
        }

        return $contextMap;
    }

    private function isFacTerminDue(array $row)
    {
        $term4Date = $this->normalizeEmrTargetDateValue((string) ($row['term_4_sertifikat_invoice_date'] ?? ''));
        if ($term4Date === '') {
            return false;
        }

        $threshold = date('Y-m-d', strtotime($term4Date . ' +90 days'));
        return date('Y-m-d') >= $threshold;
    }

    private function normalizeEmrTargetDateValue($value)
    {
        $value = trim((string) $value);
        if (!$this->isValidEmrTargetDateValue($value)) {
            return '';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    private function isValidEmrTargetDateValue($value)
    {
        $value = trim((string) $value);
        if ($value === '' || in_array($value, ['0000-00-00', '0000-00-00 00:00:00', '-'], true)) {
            return false;
        }

        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}(?:\s+\d{2}:\d{2}:\d{2})?$/', $value)
            || (bool) preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $value);
    }

    public function getEmrTargetDetailedStatusLabel(array $row)
    {
        $status = $this->resolveClusterDetailedStatus($row);
        return $status !== '' ? $status : '-';
    }

    private function resolveClusterDetailedStatus(array $row)
    {
        $displayStatus = $this->resolveClusterDisplayStatusForPic($row);
        $stageAtp = strtoupper(trim((string) ($row['stage_atp'] ?? '')));
        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));

        if ($displayStatus === 'IMPLEMENTASI') {
            return 'IMPLEMENTASI';
        }

        if ($statusAtp === 'PUNCLIST' || in_array($stageAtp, ['ATP PUNCLIST', 'PUNCLIST'], true)) {
            return 'PUNCLIST';
        }

        if (in_array($stageAtp, ['WAITING EMAIL', 'WAITING JADWAL ATP', 'WAITING JADWAL', 'WAITING ATP', 'PROSES ATP', 'ON PROSES ATP', 'WAITING STATUS ATP'], true)) {
            return $stageAtp === 'PROSES ATP' ? 'ON PROSES ATP' : $stageAtp;
        }

        return $displayStatus;
    }

    private function resolveClusterDisplayStatusForPic(array $row)
    {
        $statusCurrent = strtoupper(trim((string) ($row['status_current'] ?? '')));
        $statusDrm = strtoupper(trim((string) ($row['status_drm'] ?? '')));

        if ($statusCurrent === 'DONE' && strpos($statusDrm, 'IMPLEMENTASI') !== false) {
            return 'IMPLEMENTASI';
        }

        return $statusCurrent;
    }

    private function mapNroFlowStatusToPic($nroStatus)
    {
        $map = [
            'WAITING WASPANG' => 'EMMR - WASPANG',
            'WAITING PLANNING' => 'EMMR - PLANNING',
            'WAITING TL' => 'EMMR - TEAM LEADER',
            'WAITING LOGISTIK' => 'EMMR - LOGISTIK',
        ];

        return $map[strtoupper(trim((string) $nroStatus))] ?? '';
    }

    private function resolveTerminNroFlowStatus(array $row, array $checklistStateMap)
    {
        $terminNo = (int) ($row['termin_no'] ?? 0);
        if ($terminNo !== 4) {
            return '';
        }

        $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
        if ($rfsClusterId <= 0) {
            return '';
        }

        $scopeType = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER'))) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
        $stateKey = $this->buildTerminChecklistStateKey($rfsClusterId, $scopeType, 'RFS');
        $state = $checklistStateMap[$stateKey] ?? [];
        $nroStatus = strtoupper(trim((string) ($state['project_opname_nro_status'] ?? '')));

        return in_array($nroStatus, ['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'], true)
            ? $nroStatus
            : '';
    }

    private function decoratePoDataTableRowsWithCurrentPic(array $rows, $termStage = '')
    {
        if (empty($rows)) {
            return [];
        }

        $headerIds = array_values(array_unique(array_filter(array_map('intval', array_column($rows, 'id_po_header')))));
        $terminMap = [];
        if (!empty($headerIds)) {
            $terminSelect = 'id_po_header, termin_no, termin_value, status_termin, invoice_date';
            if ($this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
                $terminSelect .= ', sertifikat_invoice_date';
            } else {
                $terminSelect .= ", '' AS sertifikat_invoice_date";
            }

            $this->db
                ->select($terminSelect, false)
                ->from('tb_myrep_po_termin');
            $this->applyIntWhereInChunks('id_po_header', $headerIds);
            $terminRows = $this->db
                ->where('termin_no >=', 1)
                ->where('termin_no <=', 5)
                ->get()
                ->result_array();

            foreach ($terminRows as $terminRow) {
                $terminMap[(int) ($terminRow['id_po_header'] ?? 0)][(int) ($terminRow['termin_no'] ?? 0)] = $terminRow;
            }
        }

        $rfsClusterIds = [];
        foreach ($rows as $row) {
            $rfsClusterId = (int) ($row['rfs_cluster_id'] ?? 0);
            if ($rfsClusterId > 0) {
                $rfsClusterIds[] = $rfsClusterId;
            }
        }
        $checklistStateMap = $this->getTerminChecklistStateMap($rfsClusterIds);

        foreach ($rows as &$row) {
            $stage = strtoupper(trim((string) $termStage));
            if ($stage === '') {
                $stage = strtoupper(trim((string) ($row['po_stage_status'] ?? '')));
            } else {
                $row['po_stage_status'] = $stage;
            }

            if ($stage === 'CLOSED') {
                $row['current_pic'] = 'CLOSED';
                $row['current_termin_value'] = 0;
                continue;
            }

            $terminNo = $this->getTerminNoFromStage($stage);
            if ($terminNo <= 0) {
                $row['current_pic'] = 'TKM - AREA';
                $row['current_termin_value'] = 0;
                continue;
            }

            $headerId = (int) ($row['id_po_header'] ?? 0);
            $terminRow = $terminMap[$headerId][$terminNo] ?? [];
            $row['current_termin_value'] = (float) ($terminRow['termin_value'] ?? 0);
            $row['current_pic'] = $this->resolveTerminCurrentPic(array_merge($row, [
                'termin_no' => $terminNo,
                'termin_value' => (float) ($row['current_termin_value'] ?? 0),
                'status_termin' => (string) ($terminRow['status_termin'] ?? 'NOT READY'),
                'invoice_date' => (string) ($terminRow['invoice_date'] ?? ''),
                'sertifikat_invoice_date' => (string) ($terminRow['sertifikat_invoice_date'] ?? ''),
                'term_2_invoice_date' => (string) ($terminMap[$headerId][2]['invoice_date'] ?? ''),
                'term_2_sertifikat_invoice_date' => (string) ($terminMap[$headerId][2]['sertifikat_invoice_date'] ?? ''),
                'term_3_sertifikat_invoice_date' => (string) ($terminMap[$headerId][3]['sertifikat_invoice_date'] ?? ''),
                'term_4_invoice_date' => (string) ($terminMap[$headerId][4]['invoice_date'] ?? ''),
                'term_4_sertifikat_invoice_date' => (string) ($terminMap[$headerId][4]['sertifikat_invoice_date'] ?? ''),
            ]), $checklistStateMap);
            $row['current_nro_status'] = $this->resolveTerminNroFlowStatus(array_merge($row, [
                'termin_no' => $terminNo,
            ]), $checklistStateMap);
        }
        unset($row);

        return $rows;
    }

    private function filterPoRowsByCurrentPic(array $rows, array $picValues, array $nroStatusValues = [])
    {
        if (empty($picValues) && empty($nroStatusValues)) {
            return $rows;
        }

        $filtered = [];
        foreach ($rows as $row) {
            $currentPic = strtoupper(trim((string) ($row['current_pic'] ?? '')));
            if (!empty($picValues) && !in_array($currentPic, $picValues, true)) {
                continue;
            }

            $currentNroStatus = strtoupper(trim((string) ($row['current_nro_status'] ?? '')));
            if (!empty($nroStatusValues) && !in_array($currentNroStatus, $nroStatusValues, true)) {
                continue;
            }

            $filtered[] = $row;
        }

        return $filtered;
    }

    private function buildPoDataTableFooterSummary(array $rows)
    {
        $summary = [
            'count' => count($rows),
            'current_termin_value' => 0,
            'po_value' => 0,
            'termin_progress_count' => 0,
            'termin_total_count' => 0,
            'outstanding_total' => 0,
            'total_invoiced' => 0,
        ];

        foreach ($rows as $row) {
            $summary['current_termin_value'] += (float) ($row['current_termin_value'] ?? 0);
            $summary['po_value'] += (float) ($row['po_value'] ?? 0);
            $summary['termin_progress_count'] += (int) ($row['termin_progress_count'] ?? 0);
            $summary['termin_total_count'] += (int) ($row['termin_total_count'] ?? 0);
            $summary['outstanding_total'] += (float) ($row['outstanding_total'] ?? 0);
            $summary['total_invoiced'] += (float) ($row['total_invoiced'] ?? 0);
        }

        return $summary;
    }

    private function getTerminNoFromStage($stage)
    {
        $map = [
            'DP' => 1,
            'ATP CW' => 2,
            'FULL OPM' => 3,
            'RFS' => 4,
            'FAC' => 5,
        ];

        return $map[strtoupper(trim((string) $stage))] ?? 0;
    }

    private function getStageFromTerminNo($terminNo)
    {
        $map = [
            1 => 'DP',
            2 => 'ATP CW',
            3 => 'FULL OPM',
            4 => 'RFS',
            5 => 'FAC',
        ];

        return $map[(int) $terminNo] ?? '';
    }

    private function isTerminChecklistStageReady($statusCurrent)
    {
        $statusCurrent = strtoupper(trim((string) $statusCurrent));
        return in_array($statusCurrent, ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true);
    }

    private function getTerminSowType($terminNo)
    {
        $map = [
            2 => 'CW ATP',
            3 => 'FULL OPM',
            4 => 'RFS',
        ];

        return $map[(int) $terminNo] ?? '';
    }

    private function getTerminChecklistStateMap(array $rfsClusterIds)
    {
        $rfsClusterIds = array_values(array_unique(array_filter(array_map('intval', $rfsClusterIds))));
        if (empty($rfsClusterIds) || !$this->checklistTablesReadyForTerminPic()) {
            return [];
        }

        $groups = $this->db
            ->select('id_doc_group, scope_type, sow_type')
            ->from('md_rfs_myrep_doc_group')
            ->where('is_active', 1)
            ->get()
            ->result_array();
        $groupById = [];
        foreach ($groups as $group) {
            $groupById[(int) ($group['id_doc_group'] ?? 0)] = [
                'scope_type' => strtoupper(trim((string) ($group['scope_type'] ?? ''))),
                'sow_type' => strtoupper(trim((string) ($group['sow_type'] ?? ''))),
            ];
        }

        $itemRows = $this->db
            ->select('id_doc_item, id_doc_group, doc_name')
            ->from('md_rfs_myrep_doc_item')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->get()
            ->result_array();
        $itemsByGroup = [];
        foreach ($itemRows as $item) {
            $itemsByGroup[(int) ($item['id_doc_group'] ?? 0)][] = $item;
        }

        $this->db
            ->select('id_doc_package, cluster_id, id_doc_group, actual_atp_date')
            ->from('tb_rfs_myrep_doc_package');
        $this->applyIntWhereInChunks('cluster_id', $rfsClusterIds);
        $packageRows = $this->db
            ->get()
            ->result_array();
        if (empty($packageRows)) {
            return [];
        }

        $packageIds = array_values(array_filter(array_map('intval', array_column($packageRows, 'id_doc_package'))));
        $filesByPackageItem = [];
        if (!empty($packageIds)) {
            $this->db
                ->select('id_doc_package, id_doc_item, file_path, is_document_not_required, status_file, astri_status')
                ->from('tb_rfs_myrep_doc_file');
            $this->applyIntWhereInChunks('id_doc_package', $packageIds);
            $fileRows = $this->db
                ->get()
                ->result_array();

            foreach ($fileRows as $file) {
                $filesByPackageItem[(int) ($file['id_doc_package'] ?? 0)][(int) ($file['id_doc_item'] ?? 0)] = $file;
            }
        }

        $stateMap = [];
        foreach ($packageRows as $package) {
            $groupId = (int) ($package['id_doc_group'] ?? 0);
            if (empty($groupById[$groupId])) {
                continue;
            }

            $rfsClusterId = (int) ($package['cluster_id'] ?? 0);
            $scopeType = (string) $groupById[$groupId]['scope_type'];
            $sowType = (string) $groupById[$groupId]['sow_type'];
            if (!in_array($sowType, ['CW ATP', 'FULL OPM', 'RFS'], true)) {
                continue;
            }

            $stateKey = $this->buildTerminChecklistStateKey($rfsClusterId, $scopeType, $sowType);
            if (!isset($stateMap[$stateKey])) {
                $stateMap[$stateKey] = [
                    'required' => 0,
                    'uploaded' => 0,
                    'approved' => 0,
                    'astri_approved' => 0,
                    'has_project_opname_nro_flow' => false,
                    'project_opname_nro_status' => '',
                ];
            }

            $packageId = (int) ($package['id_doc_package'] ?? 0);
            $actualAtpDate = trim((string) ($package['actual_atp_date'] ?? ''));
            $hasActualAtpDate = $actualAtpDate !== '' && !in_array($actualAtpDate, ['0000-00-00', '0000-00-00 00:00:00'], true);
            foreach (($itemsByGroup[$groupId] ?? []) as $item) {
                $file = $filesByPackageItem[$packageId][(int) ($item['id_doc_item'] ?? 0)] ?? [];
                $statusFile = strtoupper(trim((string) ($file['status_file'] ?? '')));
                $astriStatus = strtoupper(trim((string) ($file['astri_status'] ?? 'NY')));
                $docName = strtoupper(trim((string) ($item['doc_name'] ?? '')));
                if (
                    $astriStatus === 'NY'
                    && $hasActualAtpDate
                    && $scopeType === 'CLUSTER'
                    && $sowType === 'RFS'
                    && $docName === 'PROJECT OPNAME'
                ) {
                    $astriStatus = 'WAITING WASPANG';
                }
                $hasDocument = !empty($file)
                    && (
                        trim((string) ($file['file_path'] ?? '')) !== ''
                        || (int) ($file['is_document_not_required'] ?? 0) === 1
                    );

                $stateMap[$stateKey]['required']++;
                if ($hasDocument && in_array($statusFile, ['UPLOADED', 'APPROVED'], true)) {
                    $stateMap[$stateKey]['uploaded']++;
                }
                if ($statusFile === 'APPROVED') {
                    $stateMap[$stateKey]['approved']++;
                }
                if ($astriStatus === 'APPROVED') {
                    $stateMap[$stateKey]['astri_approved']++;
                }
                if (
                    $sowType === 'RFS'
                    && $scopeType === 'CLUSTER'
                    && $docName === 'PROJECT OPNAME'
                    && in_array($astriStatus, ['WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK'], true)
                ) {
                    $stateMap[$stateKey]['has_project_opname_nro_flow'] = true;
                    $stateMap[$stateKey]['project_opname_nro_status'] = $astriStatus;
                }
            }
        }

        return $stateMap;
    }

    private function buildTerminChecklistStateKey($rfsClusterId, $scopeType, $sowType)
    {
        return (int) $rfsClusterId . '|' . strtoupper(trim((string) $scopeType)) . '|' . strtoupper(trim((string) $sowType));
    }

    private function applyIntWhereInChunks($column, array $values, $chunkSize = 500)
    {
        $values = array_values(array_unique(array_filter(array_map('intval', $values))));
        if (empty($values)) {
            $this->db->where('1 = 0', null, false);
            return;
        }

        $chunks = array_chunk($values, max(1, (int) $chunkSize));
        $this->db->group_start();
        foreach ($chunks as $index => $chunk) {
            if ($index === 0) {
                $this->db->where_in($column, $chunk);
            } else {
                $this->db->or_where_in($column, $chunk);
            }
        }
        $this->db->group_end();
    }

    private function checklistTablesReadyForTerminPic()
    {
        foreach (['md_rfs_myrep_doc_group', 'md_rfs_myrep_doc_item', 'tb_rfs_myrep_doc_package', 'tb_rfs_myrep_doc_file'] as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    private function applyAllowedCityRestriction($columnName = 'c.city_name')
    {
        if (!$this->shouldRestrictCityByUser()) {
            return true;
        }

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if (empty($allowedCitySet)) {
            if (method_exists($this->db, 'reset_query')) {
                $this->db->reset_query();
            }
            return false;
        }

        $escapedCities = array_map([$this->db, 'escape'], array_keys($allowedCitySet));
        $this->db->where('UPPER(' . $columnName . ') IN (' . implode(',', $escapedCities) . ')', null, false);

        return true;
    }

    private function getCurrentUserAllowedCitySet()
    {
        if ($this->currentUserAllowedCitySet !== null) {
            return $this->currentUserAllowedCitySet;
        }

        $this->currentUserAllowedCitySet = [];
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0) {
            return $this->currentUserAllowedCitySet;
        }

        if ((string) $this->session->userdata('nama_level') === 'Super Admin') {
            return $this->currentUserAllowedCitySet;
        }

        if (!$this->db->table_exists('tb_master_user_new') || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return $this->currentUserAllowedCitySet;
        }

        $user = (array) $this->db
            ->select('nik')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '') {
            return $this->currentUserAllowedCitySet;
        }

        $roleColumns = [
            'rpm_area',
            'sm_area',
            'spv_area',
            'snd_area',
            'admin_area',
            'snd_ho',
            'atp_ho',
            'rfs_ho',
            'sitac_ho',
            'dc_ho',
            'qa_ho',
        ];

        $existingRoleColumns = [];
        foreach ($roleColumns as $columnName) {
            if ($this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                $existingRoleColumns[] = $columnName;
            }
        }
        if (empty($existingRoleColumns)) {
            return $this->currentUserAllowedCitySet;
        }

        $whereParts = [];
        foreach ($existingRoleColumns as $columnName) {
            $whereParts[] = myrep_pic_column_contains_sql($this->db, '`' . $columnName . '`', $nik);
        }

        $sql = 'SELECT city_name FROM tb_myrep_pic_mapping_city WHERE ';
        if ($this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) {
            $sql .= 'is_active = 1 AND ';
        }
        $sql .= '(' . implode(' OR ', $whereParts) . ')';

        $rows = (array) $this->db->query($sql)->result_array();
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $this->currentUserAllowedCitySet[$cityName] = true;
            }
        }

        return $this->currentUserAllowedCitySet;
    }

    private function shouldRestrictCityByUser()
    {
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0) {
            return false;
        }

        $idLevel = (int) $this->session->userdata('id_level');
        $levelName = strtolower(trim((string) $this->session->userdata('nama_level')));
        if ($idLevel === 1 || $levelName === 'super admin') {
            return false;
        }
        if ($idLevel === 2 || $levelName === 'admin') {
            return false;
        }

        return true;
    }
}
