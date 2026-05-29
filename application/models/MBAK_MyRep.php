<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MBAK_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;
    private $cityPicRoleColumns = [
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
    private $cityPicApprovalColumn = 'sitac_ho';
    private $cityPicApprovalNameCache = [];
    private $masterUserNameByNikCache = [];

    private $defaultBakDocumentItems = [
        ['doc_name' => 'Surat Ijin', 'sort_no' => 1],
        ['doc_name' => 'Form Survey', 'sort_no' => 2],
        ['doc_name' => 'BA Open', 'sort_no' => 3],
    ];

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    public function getBakClusterReviewPicMap(array $clusterRows)
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $map = [];
        foreach ($clusterRows as $clusterRow) {
            $clusterId = (int) ($clusterRow['id_myrep_cluster'] ?? 0);
            if ($clusterId <= 0) {
                continue;
            }

            $city = strtoupper(trim((string) ($clusterRow['city_name'] ?? '')));
            if ($city === '') {
                continue;
            }

            if (isset($map[$clusterId])) {
                continue;
            }

            $map[$clusterId] = $this->getCityPicApprovalName(
                $city,
                strtoupper(trim((string) ($clusterRow['province_name'] ?? ''))),
                strtoupper(trim((string) ($clusterRow['regional_name'] ?? '')))
            );
        }

        return $map;
    }

    private function getDefaultBakDocumentNames()
    {
        return array_map(static function ($item) {
            return (string) $item['doc_name'];
        }, $this->defaultBakDocumentItems);
    }

    private function getCityPicRoleColumns()
    {
        $availableColumns = [];
        foreach ($this->cityPicRoleColumns as $columnName) {
            if ($this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                $availableColumns[] = $columnName;
            }
        }

        return $availableColumns;
    }

    private function getCityPicApprovalName($cityName, $provinceName = '', $regionalName = '')
    {
        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '') {
            return '';
        }

        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return '';
        }

        $cacheKey = $cityName . '|' . strtoupper(trim((string) $provinceName)) . '|' . strtoupper(trim((string) $regionalName));
        if (array_key_exists($cacheKey, $this->cityPicApprovalNameCache)) {
            return $this->cityPicApprovalNameCache[$cacheKey];
        }

        $this->db
            ->from('tb_myrep_pic_mapping_city')
            ->where('UPPER(city_name)', $cityName);
        if ($provinceName !== '') {
            $this->db->where('UPPER(province_name)', strtoupper(trim((string) $provinceName)));
        }
        if ($regionalName !== '') {
            $this->db->where('UPPER(regional_name)', strtoupper(trim((string) $regionalName)));
        }

        $mappingRow = (array) $this->db->limit(1)->get()->row_array();
        if (empty($mappingRow)) {
            $mappingRow = (array) $this->db
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $cityName)
                ->limit(1)
                ->get()
                ->row_array();
        }

        $picName = '';
        if (!empty($mappingRow) && is_array($mappingRow) && $this->db->field_exists($this->cityPicApprovalColumn, 'tb_myrep_pic_mapping_city')) {
            $picNames = [];
            foreach (myrep_pic_nik_list($mappingRow[$this->cityPicApprovalColumn] ?? '') as $nik) {
                $mappedName = $this->getMasterUserNameByNik($nik);
                $picNames[] = $mappedName !== '' ? $mappedName : $nik;
            }
            $picName = implode(', ', $picNames);
        }

        $this->cityPicApprovalNameCache[$cacheKey] = $picName;
        return $picName;
    }

    private function getMasterUserNameByNik($nik)
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return '';
        }

        $nik = trim((string) $nik);
        if ($nik === '') {
            return '';
        }

        if (array_key_exists($nik, $this->masterUserNameByNikCache)) {
            return $this->masterUserNameByNikCache[$nik];
        }

        $row = (array) $this->db
            ->select('nama_karyawan')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();

        $name = trim((string) ($row['nama_karyawan'] ?? ''));
        $this->masterUserNameByNikCache[$nik] = $name;
        return $name;
    }

    public function bakTablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_bak',
            'tb_rfs_myrep_monthly_target',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function wilayahTablesReady()
    {
        $requiredTables = [
            'md_provinsi_indonesia',
            'md_kokab_indonesia',
            'md_kec_indonesia',
            'md_dusun_indonesia',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function bakDocumentTablesReady()
    {
        $requiredTables = [
            'md_myrep_flow_doc_group',
            'md_myrep_flow_doc_item',
            'tb_myrep_flow_doc_package',
            'tb_myrep_flow_doc_file',
            'tb_myrep_flow_doc_file_log',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function getTargetOptions()
    {
        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        $rows = $this->db
            ->select('id_target, year_num, month_num, regional_name, province_name, city_name, team_name, chief, rpm, sm, spv')
            ->from('tb_rfs_myrep_monthly_target')
            ->order_by('year_num', 'DESC')
            ->order_by('month_num', 'DESC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();

        return $this->filterRowsByCurrentUserAllowedCities($rows, 'city_name');
    }

    public function getCreateTargetOptions()
    {
        $rows = $this->getTargetOptions();
        if (empty($rows)) {
            return [];
        }

        if (!$this->wilayahTablesReady()) {
            $options = [];
            foreach ($rows as $row) {
                $cityKey = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityKey === '' || isset($options[$cityKey])) {
                    continue;
                }

                $row['display_city_name'] = (string) ($row['city_name'] ?? '');
                $row['match_city_name'] = (string) ($row['city_name'] ?? '');
                $options[$cityKey] = $row;
            }

            return array_values($options);
        }

        $provinceRows = $this->db
            ->select('id, name')
            ->from('md_provinsi_indonesia')
            ->get()
            ->result_array();
        $regencies = $this->db
            ->select('id, province_id, name')
            ->from('md_kokab_indonesia')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $options = [];
        foreach ($rows as $row) {
            $cityKey = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityKey === '') {
                continue;
            }

            if (!isset($options[$cityKey . '|BASE'])) {
                $baseRow = $row;
                $baseRow['display_city_name'] = (string) ($row['city_name'] ?? '');
                $baseRow['match_city_name'] = (string) ($row['city_name'] ?? '');
                $options[$cityKey . '|BASE'] = $baseRow;
            }

            $provinceNormalized = $this->normalizeWilayahName((string) ($row['province_name'] ?? ''));
            if ($provinceNormalized === '' || $cityKey !== $provinceNormalized) {
                continue;
            }

            $matchedRegencies = $this->matchRegenciesByTargetRow($row, $regencies, $provinceRows);
            foreach ($matchedRegencies as $regencyRow) {
                $regencyName = trim((string) ($regencyRow['name'] ?? ''));
                $regencyId = trim((string) ($regencyRow['id'] ?? ''));
                if ($regencyName === '' || $regencyId === '') {
                    continue;
                }

                $optionKey = $cityKey . '|REGENCY|' . $regencyId;
                if (isset($options[$optionKey])) {
                    continue;
                }

                $expandedRow = $row;
                $expandedRow['display_city_name'] = $regencyName;
                $expandedRow['match_city_name'] = $regencyName;
                $expandedRow['regency_id'] = $regencyId;
                $options[$optionKey] = $expandedRow;
            }
        }

        return array_values($options);
    }

    public function getCityOptions()
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('city_name')
            ->from('tb_myrep_cluster')
            ->where('city_name IS NOT NULL', null, false)
            ->where("TRIM(city_name) !=", '')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();

        $cities = [];
        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && empty($allowedCitySet)) {
            return [];
        }
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '' && (empty($allowedCitySet) || isset($allowedCitySet[$cityName]))) {
                $cities[] = $cityName;
            }
        }

        return $cities;
    }

    public function getBakRows($city = '', $status = '', $regional = '', array $cityList = [], array $regionalList = [], $bakDateStart = '', $bakDateEnd = '')
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $clusterLocationSelect = $this->buildClusterLocationSelect();

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.rfs_cluster_id,
                c.id_target,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name' . $clusterLocationSelect . ',
                c.team_name,
                c.chief,
                c.rpm,
                c.sm,
                c.spv,
                c.hp_plan,
                c.ntp_name,
                c.ntp_date,
                c.ntp_year,
                c.status_current,
                c.outstanding_progress,
                c.remark_general,
                c.created_at,
                b.id_bak,
                b.ba_open_date,
                b.bak_date,
                b.homepass_bak,
                b.status_bak,
                b.remark_bak,
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser()) {
            if (empty($allowedCitySet)) {
                return [];
            }
            $escapedCities = array_map([$this->db, 'escape'], array_keys($allowedCitySet));
            $this->db->where('UPPER(c.city_name) IN (' . implode(',', $escapedCities) . ')', null, false);
        }

        if ($regional !== '') {
            $this->db->where('UPPER(c.regional_name)', strtoupper($regional));
        }
        if (!empty($regionalList)) {
            $normalizedRegionals = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $regionalList))));
            if (!empty($normalizedRegionals)) {
                $escapedRegionals = array_map([$this->db, 'escape'], $normalizedRegionals);
                $this->db->where('UPPER(c.regional_name) IN (' . implode(',', $escapedRegionals) . ')', null, false);
            }
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if (!empty($cityList)) {
            $normalizedCities = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $cityList))));
            if (!empty($normalizedCities)) {
                $escapedCities = array_map([$this->db, 'escape'], $normalizedCities);
                $this->db->where('UPPER(c.city_name) IN (' . implode(',', $escapedCities) . ')', null, false);
            }
        }

        if ($bakDateStart !== '') {
            $this->db->where('b.bak_date >=', $bakDateStart);
        }
        if ($bakDateEnd !== '') {
            $this->db->where('b.bak_date <=', $bakDateEnd);
        }

        if ($status !== '') {
            $normalizedStatus = strtoupper($status);
            if (in_array($normalizedStatus, ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'], true)) {
                $this->db->where('b.status_bak', $normalizedStatus);
            } else {
                $this->db->where('c.status_current', $normalizedStatus);
            }
        }

        return $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBakRowsPage($city = '', $status = '', $tab = 'all', $approvalStatus = '', $start = 0, $length = 10, $search = '', array $order = [])
    {
        if (!$this->bakTablesReady()) {
            return [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'rows' => [],
            ];
        }

        $start = max(0, (int) $start);
        $length = (int) $length;
        if ($length <= 0) {
            $length = 10;
        }

        $recordsTotal = $this->countBakRows($city, $status, $tab, $approvalStatus);
        $recordsFiltered = $this->countBakRows($city, $status, $tab, $approvalStatus, $search);

        $this->prepareBakRowsQuery($city, $status, $tab, $approvalStatus, $search);
        $this->applyBakRowsOrder($order);
        $rows = $this->db
            ->limit($length, $start)
            ->get()
            ->result_array();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $rows,
        ];
    }

    public function getBakSummary($city = '', $status = '')
    {
        if (!$this->bakTablesReady()) {
            return [
                'totalCount' => 0,
                'totalHp' => 0,
                'baOpenCount' => 0,
                'baOpenHp' => 0,
                'doneCount' => 0,
                'doneHp' => 0,
                'rejectedCount' => 0,
                'rejectedHp' => 0,
                'onProcessCount' => 0,
                'nyValsalCount' => 0,
                'allCount' => 0,
            ];
        }

        $postBakStatuses = [
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
        ];
        $escapedPostBakStatuses = implode(',', array_map([$this->db, 'escape'], $postBakStatuses));

        $this->db
            ->select('COUNT(1) AS total_count', false)
            ->select('COALESCE(SUM(COALESCE(b.homepass_bak, 0)), 0) AS total_hp', false)
            ->select("SUM(CASE WHEN UPPER(COALESCE(c.status_current, 'DRAFT')) = 'BA OPEN' THEN 1 ELSE 0 END) AS ba_open_count", false)
            ->select("COALESCE(SUM(CASE WHEN UPPER(COALESCE(c.status_current, 'DRAFT')) = 'BA OPEN' THEN COALESCE(b.homepass_bak, 0) ELSE 0 END), 0) AS ba_open_hp", false)
            ->select("SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'DONE' AND UPPER(COALESCE(c.status_current, 'DRAFT')) NOT IN (" . $escapedPostBakStatuses . ") THEN 1 ELSE 0 END) AS done_count", false)
            ->select("COALESCE(SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'DONE' AND UPPER(COALESCE(c.status_current, 'DRAFT')) NOT IN (" . $escapedPostBakStatuses . ") THEN COALESCE(b.homepass_bak, 0) ELSE 0 END), 0) AS done_hp", false)
            ->select("SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'REJECTED' OR UPPER(COALESCE(c.status_current, 'DRAFT')) = 'REJECTED' THEN 1 ELSE 0 END) AS rejected_count", false)
            ->select("COALESCE(SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'REJECTED' OR UPPER(COALESCE(c.status_current, 'DRAFT')) = 'REJECTED' THEN COALESCE(b.homepass_bak, 0) ELSE 0 END), 0) AS rejected_hp", false)
            ->select("SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) NOT IN ('DONE', 'APPROVED') OR UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'REJECTED' THEN 1 ELSE 0 END) AS on_process_count", false)
            ->select("SUM(CASE WHEN UPPER(COALESCE(b.status_bak, 'DRAFT')) IN ('DONE', 'APPROVED') AND UPPER(COALESCE(c.status_current, 'DRAFT')) = 'BAK' THEN 1 ELSE 0 END) AS ny_valsal_count", false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if (!$this->applyBakBaseFilters($city, $status)) {
            return [
                'totalCount' => 0,
                'totalHp' => 0,
                'baOpenCount' => 0,
                'baOpenHp' => 0,
                'doneCount' => 0,
                'doneHp' => 0,
                'rejectedCount' => 0,
                'rejectedHp' => 0,
                'onProcessCount' => 0,
                'nyValsalCount' => 0,
                'allCount' => 0,
            ];
        }

        $row = (array) $this->db->get()->row_array();

        return [
            'totalCount' => (int) ($row['total_count'] ?? 0),
            'totalHp' => (float) ($row['total_hp'] ?? 0),
            'baOpenCount' => (int) ($row['ba_open_count'] ?? 0),
            'baOpenHp' => (float) ($row['ba_open_hp'] ?? 0),
            'doneCount' => (int) ($row['done_count'] ?? 0),
            'doneHp' => (float) ($row['done_hp'] ?? 0),
            'rejectedCount' => (int) ($row['rejected_count'] ?? 0),
            'rejectedHp' => (float) ($row['rejected_hp'] ?? 0),
            'onProcessCount' => (int) ($row['on_process_count'] ?? 0),
            'nyValsalCount' => (int) ($row['ny_valsal_count'] ?? 0),
            'allCount' => (int) ($row['total_count'] ?? 0),
        ];
    }

    public function getBakApprovalStatusSummary($city = '', $status = '', $tab = 'all')
    {
        $emptySummary = [
            'onReviewCount' => 0,
            'rejectedCount' => 0,
        ];

        if (!$this->bakTablesReady()) {
            return $emptySummary;
        }

        $onReviewSql = $this->buildBakApprovalStatusSql('on_review');
        $rejectedSql = $this->buildBakApprovalStatusSql('rejected');

        $this->db
            ->select("SUM(CASE WHEN {$onReviewSql} THEN 1 ELSE 0 END) AS on_review_count", false)
            ->select("SUM(CASE WHEN {$rejectedSql} THEN 1 ELSE 0 END) AS rejected_count", false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if (!$this->applyBakBaseFilters($city, $status)) {
            return $emptySummary;
        }

        $this->applyBakTabFilter($tab);

        $row = (array) $this->db->get()->row_array();

        return [
            'onReviewCount' => (int) ($row['on_review_count'] ?? 0),
            'rejectedCount' => (int) ($row['rejected_count'] ?? 0),
        ];
    }

    private function countBakRows($city = '', $status = '', $tab = 'all', $approvalStatus = '', $search = '')
    {
        $this->prepareBakRowsQuery($city, $status, $tab, $approvalStatus, $search, true);
        $row = (array) $this->db->get()->row_array();

        return (int) ($row['total'] ?? 0);
    }

    private function prepareBakRowsQuery($city = '', $status = '', $tab = 'all', $approvalStatus = '', $search = '', $countOnly = false)
    {
        if ($countOnly) {
            $this->db->select('COUNT(1) AS total', false);
        } else {
            $clusterLocationSelect = $this->buildClusterLocationSelect();
            $this->db->select('
                c.id_myrep_cluster,
                c.rfs_cluster_id,
                c.id_target,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name' . $clusterLocationSelect . ',
                c.team_name,
                c.chief,
                c.rpm,
                c.sm,
                c.spv,
                c.hp_plan,
                c.ntp_name,
                c.ntp_date,
                c.ntp_year,
                c.status_current,
                c.outstanding_progress,
                c.remark_general,
                c.created_at,
                b.id_bak,
                b.ba_open_date,
                b.bak_date,
                b.homepass_bak,
                b.status_bak,
                b.remark_bak,
                t.year_num,
                t.month_num
            ');
        }

        $this->db
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if (!$this->applyBakBaseFilters($city, $status)) {
            $this->db->where('1 = 0', null, false);
        }

        $this->applyBakTabFilter($tab);
        $this->applyBakApprovalStatusFilter($approvalStatus);
        $this->applyBakSearchFilter($search);
    }

    private function applyBakBaseFilters($city = '', $status = '')
    {
        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser()) {
            if (empty($allowedCitySet)) {
                return false;
            }
            $escapedCities = array_map([$this->db, 'escape'], array_keys($allowedCitySet));
            $this->db->where('UPPER(c.city_name) IN (' . implode(',', $escapedCities) . ')', null, false);
        }

        $city = strtoupper(trim((string) $city));
        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', $city);
        }

        $status = strtoupper(trim((string) $status));
        if ($status !== '') {
            if (in_array($status, ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'], true)) {
                $this->db->where("UPPER(COALESCE(b.status_bak, 'DRAFT')) = " . $this->db->escape($status), null, false);
            } else {
                $this->db->where('UPPER(c.status_current)', $status);
            }
        }

        return true;
    }

    private function applyBakTabFilter($tab)
    {
        $tab = strtolower(trim((string) $tab));
        if ($tab === 'on_process') {
            $this->db->where("UPPER(COALESCE(b.status_bak, 'DRAFT')) NOT IN ('DONE', 'APPROVED')", null, false);
            return;
        }

        if ($tab === 'ny_valsal') {
            $this->db->where("UPPER(COALESCE(b.status_bak, 'DRAFT')) IN ('DONE', 'APPROVED')", null, false);
            $this->db->where("UPPER(COALESCE(c.status_current, 'DRAFT')) = 'BAK'", null, false);
        }
    }

    private function applyBakApprovalStatusFilter($approvalStatus)
    {
        $condition = $this->buildBakApprovalStatusSql($approvalStatus);
        if ($condition !== '') {
            $this->db->where($condition, null, false);
        }
    }

    private function buildBakApprovalStatusSql($approvalStatus)
    {
        $approvalStatus = strtolower(trim((string) $approvalStatus));
        $rejectedSql = $this->buildBakRejectedSql();

        if ($approvalStatus === 'on_review') {
            return "(UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'ON REVIEW' AND NOT ({$rejectedSql}))";
        }

        if ($approvalStatus === 'rejected') {
            return "({$rejectedSql})";
        }

        return '';
    }

    private function buildBakRejectedSql()
    {
        $docRejectedSql = $this->buildBakDocStatusExistsSql(['REJECTED']);
        return "(UPPER(COALESCE(b.status_bak, 'DRAFT')) = 'REJECTED' OR UPPER(COALESCE(c.status_current, 'DRAFT')) = 'REJECTED' OR {$docRejectedSql})";
    }

    private function buildBakDocStatusExistsSql(array $statuses)
    {
        if (!$this->bakDocumentTablesReady()) {
            return '0 = 1';
        }

        $normalizedStatuses = array_values(array_unique(array_filter(array_map(static function ($status) {
            return strtoupper(trim((string) $status));
        }, $statuses))));
        if (empty($normalizedStatuses)) {
            return '0 = 1';
        }

        $escapedStatuses = implode(',', array_map([$this->db, 'escape'], $normalizedStatuses));
        $escapedDocNames = implode(',', array_map([$this->db, 'escape'], $this->getDefaultBakDocumentNames()));

        return "EXISTS (
            SELECT 1
            FROM tb_myrep_flow_doc_package doc_package_filter
            JOIN tb_myrep_flow_doc_file doc_file_filter
                ON doc_file_filter.id_doc_package = doc_package_filter.id_doc_package
            JOIN md_myrep_flow_doc_group doc_group_filter
                ON doc_group_filter.id_doc_group = doc_package_filter.id_doc_group
                AND doc_group_filter.flow_type = 'BAK'
                AND doc_group_filter.group_label = 'BA OPEN'
                AND doc_group_filter.is_active = 1
            JOIN md_myrep_flow_doc_item doc_item_filter
                ON doc_item_filter.id_doc_item = doc_file_filter.id_doc_item
                AND doc_item_filter.id_doc_group = doc_group_filter.id_doc_group
                AND doc_item_filter.is_active = 1
            WHERE doc_package_filter.id_myrep_cluster = c.id_myrep_cluster
                AND doc_package_filter.flow_type = 'BAK'
                AND doc_item_filter.doc_name IN ({$escapedDocNames})
                AND UPPER(COALESCE(doc_file_filter.status_file, '')) IN ({$escapedStatuses})
        )";
    }

    private function applyBakSearchFilter($search = '')
    {
        $search = strtoupper(trim((string) $search));
        if ($search === '') {
            return;
        }

        $like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%');
        $this->db->where("(
            UPPER(c.cluster_name) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.cluster_code) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.regional_name) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.city_name) LIKE " . $like . " ESCAPE '!'
            OR UPPER(COALESCE(b.status_bak, 'DRAFT')) LIKE " . $like . " ESCAPE '!'
            OR UPPER(COALESCE(c.status_current, 'DRAFT')) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.rpm) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.sm) LIKE " . $like . " ESCAPE '!'
            OR UPPER(c.spv) LIKE " . $like . " ESCAPE '!'
        )", null, false);
    }

    private function applyBakRowsOrder(array $order = [])
    {
        $columnMap = [
            1 => 'c.cluster_name',
            2 => 'c.regional_name',
            3 => 'c.city_name',
            4 => 'b.homepass_bak',
            5 => 'b.ba_open_date',
            7 => 'b.bak_date',
            8 => 'b.status_bak',
            11 => 'c.status_current',
        ];

        $column = isset($order['column']) ? (int) $order['column'] : 0;
        $dir = strtoupper(trim((string) ($order['dir'] ?? 'ASC'))) === 'DESC' ? 'DESC' : 'ASC';

        if (isset($columnMap[$column])) {
            $this->db->order_by($columnMap[$column], $dir);
        } else {
            $this->db->order_by('c.created_at', 'DESC');
        }

        $this->db->order_by('c.cluster_name', 'ASC');
    }

    public function getRegionalOptions()
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('regional_name')
            ->from('tb_myrep_cluster')
            ->where('regional_name IS NOT NULL', null, false)
            ->where("TRIM(regional_name) !=", '')
            ->order_by('regional_name', 'ASC')
            ->get()
            ->result_array();

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && empty($allowedCitySet)) {
            return [];
        }

        $regionals = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regionalName === '') {
                continue;
            }

            if (!$this->shouldRestrictCityByUser()) {
                $regionals[] = $regionalName;
                continue;
            }

            $hasAllowedCity = $this->db
                ->select('id_myrep_cluster')
                ->from('tb_myrep_cluster')
                ->where('UPPER(regional_name)', $regionalName)
                ->where_in('UPPER(city_name)', array_keys($allowedCitySet))
                ->limit(1)
                ->get()
                ->num_rows() > 0;

            if ($hasAllowedCity) {
                $regionals[] = $regionalName;
            }
        }

        return array_values(array_unique($regionals));
    }

    public function getCityOptionsByRegional()
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('regional_name, city_name')
            ->from('tb_myrep_cluster')
            ->where('regional_name IS NOT NULL', null, false)
            ->where('city_name IS NOT NULL', null, false)
            ->where("TRIM(regional_name) !=", '')
            ->where("TRIM(city_name) !=", '')
            ->order_by('regional_name', 'ASC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && empty($allowedCitySet)) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($regionalName === '' || $cityName === '') {
                continue;
            }

            if ($this->shouldRestrictCityByUser() && !isset($allowedCitySet[$cityName])) {
                continue;
            }

            if (!isset($map[$regionalName])) {
                $map[$regionalName] = [];
            }
            $map[$regionalName][] = $cityName;
        }

        foreach ($map as $regional => $cities) {
            $cities = array_values(array_unique($cities));
            sort($cities);
            $map[$regional] = $cities;
        }

        return $map;
    }

    public function getRegionalOptionsByCity()
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('regional_name, city_name')
            ->from('tb_myrep_cluster')
            ->where('regional_name IS NOT NULL', null, false)
            ->where('city_name IS NOT NULL', null, false)
            ->where("TRIM(regional_name) !=", '')
            ->where("TRIM(city_name) !=", '')
            ->order_by('city_name', 'ASC')
            ->order_by('regional_name', 'ASC')
            ->get()
            ->result_array();

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && empty($allowedCitySet)) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($regionalName === '' || $cityName === '') {
                continue;
            }

            if ($this->shouldRestrictCityByUser() && !isset($allowedCitySet[$cityName])) {
                continue;
            }

            if (!isset($map[$cityName])) {
                $map[$cityName] = [];
            }
            $map[$cityName][] = $regionalName;
        }

        foreach ($map as $city => $regionals) {
            $regionals = array_values(array_unique($regionals));
            sort($regionals);
            $map[$city] = $regionals;
        }

        return $map;
    }

    public function ensureBakDocumentSetup()
    {
        if (!$this->bakDocumentTablesReady()) {
            return false;
        }

        $group = $this->db
            ->get_where('md_myrep_flow_doc_group', [
                'flow_type' => 'BAK',
                'group_label' => 'BA OPEN',
                'is_active' => 1,
            ])
            ->row_array();

        if (empty($group['id_doc_group'])) {
            return false;
        }

        $groupId = (int) $group['id_doc_group'];
        $existingRows = $this->db
            ->select('id_doc_item, doc_name')
            ->from('md_myrep_flow_doc_item')
            ->where('id_doc_group', $groupId)
            ->where('is_active', 1)
            ->get()
            ->result_array();

        $existingMap = [];
        foreach ($existingRows as $row) {
            $existingMap[strtoupper(trim((string) ($row['doc_name'] ?? '')))] = (int) $row['id_doc_item'];
        }

        foreach ($this->defaultBakDocumentItems as $item) {
            $docName = (string) $item['doc_name'];
            $lookupKey = strtoupper($docName);
            if (isset($existingMap[$lookupKey])) {
                $this->db
                    ->where('id_doc_item', $existingMap[$lookupKey])
                    ->update('md_myrep_flow_doc_item', [
                        'doc_name' => $docName,
                        'sort_no' => (int) $item['sort_no'],
                    ]);
                continue;
            }

            if ($lookupKey === 'BA OPEN' && isset($existingMap['BA OPEN'])) {
                $this->db
                    ->where('id_doc_item', $existingMap['BA OPEN'])
                    ->update('md_myrep_flow_doc_item', [
                        'doc_name' => $docName,
                        'sort_no' => (int) $item['sort_no'],
                    ]);
                continue;
            }

            $this->db->insert('md_myrep_flow_doc_item', [
                'id_doc_group' => $groupId,
                'doc_name' => $docName,
                'sort_no' => (int) $item['sort_no'],
                'is_active' => 1,
            ]);
        }

        return true;
    }

    public function getBakDocumentDefinitions()
    {
        if (!$this->bakDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('doc_group.id_doc_group, doc_item.id_doc_item, doc_item.doc_name, doc_item.sort_no')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->where('doc_group.flow_type', 'BAK')
            ->where('doc_group.group_label', 'BA OPEN')
            ->where('doc_group.is_active', 1)
            ->where_in('doc_item.doc_name', $this->getDefaultBakDocumentNames())
            ->order_by('doc_item.sort_no', 'ASC')
            ->order_by('doc_item.id_doc_item', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBakDocumentItemsByClusterIds($clusterIds)
    {
        if (!$this->bakDocumentTablesReady() || empty($clusterIds)) {
            return [];
        }

        if ($this->shouldRestrictCityByUser()) {
            $allowedCitySet = $this->getCurrentUserAllowedCitySet();
            if (empty($allowedCitySet)) {
                return [];
            }
            $escapedCities = array_map([$this->db, 'escape'], array_keys($allowedCitySet));
            $this->db->where('UPPER(c.city_name) IN (' . implode(',', $escapedCities) . ')', null, false);
        }

        $rows = $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.province_name,
                c.regional_name,
                c.city_name,
                c.district_name,
                c.village_name,
                b.homepass_bak,
                doc_group.id_doc_group,
                doc_item.id_doc_item,
                doc_item.doc_name,
                doc_item.sort_no,
                doc_package.id_doc_package,
                doc_package.status_package,
                doc_file.id_doc_file,
                doc_file.file_name,
                doc_file.file_path,
                doc_file.status_file,
                doc_file.is_document_not_required,
                doc_file.remark,
                doc_file.uploaded_at,
                doc_file.reviewed_at,
                doc_file.approved_at,
                doc_file.uploaded_by,
                doc_file.approved_by,
                u_upload.nama_karyawan AS uploaded_by_name,
                u_approve.nama_karyawan AS approved_by_name
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'BAK' AND doc_group.group_label = 'BA OPEN' AND doc_group.is_active = 1", 'inner', false)
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'BAK\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->join('tb_master_user_new u_upload', 'u_upload.id = doc_file.uploaded_by', 'left')
            ->join('tb_master_user_new u_approve', 'u_approve.id = doc_file.approved_by', 'left')
            ->where_in('c.id_myrep_cluster', array_map('intval', $clusterIds))
            ->where_in('doc_item.doc_name', $this->getDefaultBakDocumentNames())
            ->order_by('doc_item.sort_no', 'ASC')
            ->order_by('doc_item.id_doc_item', 'ASC')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $clusterId = (int) $row['id_myrep_cluster'];
            $fileId = (int) ($row['id_doc_file'] ?? 0);
            $row['city_pic_approval_name'] = $this->getCityPicApprovalName(
                (string) ($row['city_name'] ?? ''),
                (string) ($row['province_name'] ?? ''),
                (string) ($row['regional_name'] ?? '')
            );
            $row['history'] = $fileId > 0 ? $this->getBakFileLogs($fileId) : [];
            $result[$clusterId][] = $row;
        }

        return $result;
    }

    public function getClusterById($clusterId)
    {
        if (!$this->bakTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, b.id_bak, b.ba_open_date, b.bak_date, b.homepass_bak, b.status_bak, b.remark_bak')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getTargetById($targetId)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        $row = $this->getTargetByIdRaw((int) $targetId);
        if (empty($row)) {
            return [];
        }

        $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && (empty($allowedCitySet) || $cityName === '' || !isset($allowedCitySet[$cityName]))) {
            return [];
        }

        return $row;
    }

    private function getTargetByIdRaw($targetId)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        return (array) $this->db
            ->get_where('tb_rfs_myrep_monthly_target', ['id_target' => (int) $targetId])
            ->row_array();
    }

    public function getTargetByCity($cityName)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '') {
            return [];
        }

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && (empty($allowedCitySet) || !isset($allowedCitySet[$cityName]))) {
            return [];
        }

        return $this->db
            ->from('tb_rfs_myrep_monthly_target')
            ->where('UPPER(city_name)', $cityName)
            ->order_by('year_num', 'DESC')
            ->order_by('month_num', 'DESC')
            ->order_by('id_target', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    private function filterRowsByCurrentUserAllowedCities(array $rows, $cityKey)
    {
        if (!$this->shouldRestrictCityByUser()) {
            return $rows;
        }

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if (empty($allowedCitySet)) {
            return [];
        }

        $filtered = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row[$cityKey] ?? '')));
            if ($cityName === '' || !isset($allowedCitySet[$cityName])) {
                continue;
            }
            $filtered[] = $row;
        }

        return $filtered;
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

    public function clusterExists($clusterName, $targetId)
    {
        return $this->db
            ->select('id_myrep_cluster')
            ->from('tb_myrep_cluster')
            ->where('UPPER(cluster_name)', strtoupper(trim((string) $clusterName)))
            ->where('id_target', (int) $targetId)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function createClusterAndBak($clusterPayload, $bakPayload)
    {
        $this->db->trans_start();

        $this->db->insert('tb_myrep_cluster', $clusterPayload);
        $clusterId = (int) $this->db->insert_id();

        $bakPayload['id_myrep_cluster'] = $clusterId;
        $this->db->insert('tb_myrep_bak', $bakPayload);

        $this->db->trans_complete();

        return $this->db->trans_status() ? $clusterId : 0;
    }

    public function deleteClusterAndBak($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->delete('tb_myrep_bak');
        $this->db->where('id_myrep_cluster', $clusterId)->delete('tb_myrep_cluster');
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateClusterAndBak($clusterId, $clusterPayload, $bakPayload)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $existing = $this->getClusterById($clusterId);
        if (empty($existing)) {
            return false;
        }

        $clusterPayload['status_current'] = $this->resolveSafeCurrentStatus(
            (string) ($existing['status_current'] ?? ''),
            (string) ($clusterPayload['status_current'] ?? 'DRAFT')
        );

        $this->db->trans_start();

        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);

        $bak = $this->db
            ->get_where('tb_myrep_bak', ['id_myrep_cluster' => $clusterId])
            ->row_array();

        if (!empty($bak)) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_bak', $bakPayload);
        } else {
            $bakPayload['id_myrep_cluster'] = $clusterId;
            $this->db->insert('tb_myrep_bak', $bakPayload);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function getBakDocumentContext($clusterId, $docItemId = 0)
    {
        if (!$this->bakDocumentTablesReady()) {
            return [];
        }

        if ($docItemId > 0) {
            $this->db->where('doc_item.id_doc_item', (int) $docItemId);
        }

        $row = $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.province_name,
                c.regional_name,
                c.city_name,
                c.district_name,
                c.village_name,
                b.homepass_bak,
                doc_group.id_doc_group,
                doc_item.id_doc_item,
                doc_item.doc_name,
                doc_item.sort_no,
                doc_package.id_doc_package,
                doc_package.status_package,
                doc_file.id_doc_file,
                doc_file.file_name,
                doc_file.file_path,
                doc_file.status_file,
                doc_file.is_document_not_required,
                doc_file.remark,
                doc_file.approved_at,
                doc_file.reviewed_at,
                doc_file.uploaded_by,
                doc_file.approved_by,
                u_upload.nama_karyawan AS uploaded_by_name,
                u_approve.nama_karyawan AS approved_by_name
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'BAK' AND doc_group.group_label = 'BA OPEN' AND doc_group.is_active = 1", 'left', false)
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'BAK\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->join('tb_master_user_new u_upload', 'u_upload.id = doc_file.uploaded_by', 'left')
            ->join('tb_master_user_new u_approve', 'u_approve.id = doc_file.approved_by', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where_in('doc_item.doc_name', $this->getDefaultBakDocumentNames())
            ->order_by('doc_item.sort_no', 'ASC')
            ->order_by('doc_item.id_doc_item', 'ASC')
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $row['city_pic_approval_name'] = $this->getCityPicApprovalName(
            (string) ($row['city_name'] ?? ''),
            (string) ($row['province_name'] ?? ''),
            (string) ($row['regional_name'] ?? '')
        );

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function saveBakFileUpload($clusterId, $docItemId, $data)
    {
        $clusterId = (int) $clusterId;
        $docItemId = (int) $docItemId;
        if ($clusterId <= 0 || $docItemId <= 0 || !$this->bakDocumentTablesReady()) {
            return 0;
        }

        $context = $this->getBakDocumentContext($clusterId, $docItemId);
        if (empty($context['id_doc_group']) || empty($context['id_doc_item'])) {
            return 0;
        }

        $packageId = $this->ensureBakPackage($clusterId, (int) $context['id_doc_group'], (int) $data['uploaded_by']);
        if ($packageId <= 0) {
            return 0;
        }

        $existing = $this->db->get_where('tb_myrep_flow_doc_file', [
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $context['id_doc_item'],
        ])->row_array();

        $payload = [
            'file_name' => (string) $data['file_name'],
            'file_path' => (string) $data['file_path'],
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => (string) $data['status_file'],
            'remark' => (string) $data['remark'],
            'uploaded_by' => (int) $data['uploaded_by'],
            'uploaded_at' => date('Y-m-d H:i:s'),
            'approved_by' => null,
            'reviewed_at' => null,
            'approved_at' => null,
        ];

        if ($existing) {
            $this->deletePhysicalFile($existing['file_path'] ?? '');
            $this->db
                ->where('id_doc_file', (int) $existing['id_doc_file'])
                ->update('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $actionType = 'REUPLOAD';
        } else {
            $payload['id_doc_package'] = $packageId;
            $payload['id_doc_item'] = (int) $context['id_doc_item'];
            $this->db->insert('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $actionType = 'UPLOAD';
        }

        $this->createFileLog([
            'id_doc_file' => $fileId,
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $context['id_doc_item'],
            'action_type' => $actionType,
            'status_after' => (string) $data['status_file'],
            'file_name' => $data['file_name'] !== '' ? (string) $data['file_name'] : '[Tanpa Dokumen]',
            'remark' => (string) $data['remark'],
            'action_by' => (int) $data['uploaded_by'],
        ]);

        $this->refreshPackageStatus($packageId);
        return $fileId;
    }

    public function getDistrictById($districtId)
    {
        if (!$this->wilayahTablesReady() || trim((string) $districtId) === '') {
            return [];
        }

        return $this->db
            ->get_where('md_kec_indonesia', ['id' => trim((string) $districtId)])
            ->row_array();
    }

    public function getVillageById($villageId)
    {
        if (!$this->wilayahTablesReady() || trim((string) $villageId) === '') {
            return [];
        }

        return $this->db
            ->get_where('md_dusun_indonesia', ['id' => trim((string) $villageId)])
            ->row_array();
    }

    public function getDistrictByNameAndTarget($districtName, $targetId = 0, $cityNameOverride = '')
    {
        $districtName = trim((string) $districtName);
        if (!$this->wilayahTablesReady() || $districtName === '') {
            return [];
        }

        $rows = $this->searchDistrictOptionsByTarget((int) $targetId, $districtName, 200, (string) $cityNameOverride);
        if (empty($rows)) {
            return [];
        }

        $normalizedTarget = $this->normalizeWilayahName($districtName);
        foreach ($rows as $row) {
            if ($this->normalizeWilayahName((string) ($row['name'] ?? '')) === $normalizedTarget) {
                return $row;
            }
        }

        return $rows[0];
    }

    public function getVillageByNameAndDistrict($villageName, $districtId)
    {
        $villageName = trim((string) $villageName);
        $districtId = trim((string) $districtId);
        if (!$this->wilayahTablesReady() || $villageName === '' || $districtId === '') {
            return [];
        }

        $rows = $this->searchVillageOptionsByDistrict($districtId, $villageName, 200);
        if (empty($rows)) {
            return [];
        }

        $normalizedTarget = $this->normalizeWilayahName($villageName);
        foreach ($rows as $row) {
            if ($this->normalizeWilayahName((string) ($row['name'] ?? '')) === $normalizedTarget) {
                return $row;
            }
        }

        return $rows[0];
    }

    public function searchDistrictOptionsByTarget($targetId, $keyword = '', $limit = 50, $cityNameOverride = '')
    {
        $target = $this->getTargetByIdRaw((int) $targetId);
        if (!$this->wilayahTablesReady() || empty($target)) {
            return [];
        }

        $cityNameOverride = trim((string) $cityNameOverride);
        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if ($this->shouldRestrictCityByUser() && !empty($allowedCitySet)) {
            $baseCity = strtoupper(trim((string) ($target['city_name'] ?? '')));
            $overrideCity = strtoupper($cityNameOverride);
            $isAllowed = ($baseCity !== '' && isset($allowedCitySet[$baseCity]))
                || ($overrideCity !== '' && isset($allowedCitySet[$overrideCity]));
            if (!$isAllowed) {
                return [];
            }
        }

        if ($cityNameOverride !== '') {
            $target['city_name'] = $cityNameOverride;
        }

        $provinceRows = $this->db
            ->select('id, name')
            ->from('md_provinsi_indonesia')
            ->get()
            ->result_array();

        $regencies = $this->db
            ->select('id, province_id, name')
            ->from('md_kokab_indonesia')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $matchedRegencies = $this->matchRegenciesByTargetRow($target, $regencies, $provinceRows);
        $regencyIds = array_values(array_filter(array_map(static function ($row) {
            return (string) ($row['id'] ?? '');
        }, $matchedRegencies)));

        if (empty($regencyIds)) {
            return [];
        }

        $this->db
            ->select('d.id, d.regency_id, d.name, r.name AS regency_name')
            ->from('md_kec_indonesia d')
            ->join('md_kokab_indonesia r', 'r.id = d.regency_id', 'left')
            ->where_in('d.regency_id', $regencyIds);

        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $this->db->like('d.name', $keyword);
        }

        return $this->db
            ->order_by('d.name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array();
    }

    public function searchVillageOptionsByDistrict($districtId, $keyword = '', $limit = 50)
    {
        if (!$this->wilayahTablesReady() || trim((string) $districtId) === '') {
            return [];
        }

        $this->db
            ->select('id, district_id, name')
            ->from('md_dusun_indonesia')
            ->where('district_id', trim((string) $districtId));

        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            $this->db->like('name', $keyword);
        }

        return $this->db
            ->order_by('name', 'ASC')
            ->limit((int) $limit)
            ->get()
            ->result_array();
    }

    private function matchRegenciesByTargetRow($target, $regencies, $provinceRows)
    {
        if (empty($target) || !$this->wilayahTablesReady()) {
            return [];
        }

        $provinceName = trim((string) ($target['province_name'] ?? ''));
        $cityName = trim((string) ($target['city_name'] ?? ''));
        if ($provinceName === '' || $cityName === '') {
            return [];
        }

        $provinceId = '';
        $normalizedProvince = $this->normalizeWilayahName($provinceName);
        foreach ($provinceRows as $provinceRow) {
            if ($this->normalizeWilayahName((string) ($provinceRow['name'] ?? '')) === $normalizedProvince) {
                $provinceId = (string) ($provinceRow['id'] ?? '');
                break;
            }
        }

        if ($provinceId === '') {
            return $this->matchRegenciesByNormalizedCity($cityName, $regencies);
        }

        $targetNormalized = $this->normalizeWilayahName($cityName);
        if ($targetNormalized === $normalizedProvince) {
            return array_values(array_filter($regencies, static function ($regencyRow) use ($provinceId) {
                return (string) ($regencyRow['province_id'] ?? '') === (string) $provinceId;
            }));
        }

        $matched = [];
        foreach ($regencies as $regencyRow) {
            if ((string) ($regencyRow['province_id'] ?? '') !== $provinceId) {
                continue;
            }
            $regencyNormalized = $this->normalizeWilayahName((string) ($regencyRow['name'] ?? ''));
            if ($this->isWilayahMatch($targetNormalized, $regencyNormalized)) {
                $matched[] = $regencyRow;
            }
        }

        if (empty($matched)) {
            return $this->matchRegenciesByNormalizedCity($cityName, $regencies);
        }

        return $matched;
    }

    private function matchRegenciesByNormalizedCity($cityName, $regencies)
    {
        $targetNormalized = $this->normalizeWilayahName($cityName);
        if ($targetNormalized === '') {
            return [];
        }

        $matched = [];
        foreach ($regencies as $regencyRow) {
            $regencyNormalized = $this->normalizeWilayahName((string) ($regencyRow['name'] ?? ''));
            if ($this->isWilayahMatch($targetNormalized, $regencyNormalized)) {
                $matched[] = $regencyRow;
            }
        }

        return $matched;
    }

    private function normalizeWilayahName($value)
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/\b(KABUPATEN|KOTA|KAB\.?|ADM\.?|ADMINISTRASI)\b/u', ' ', $value);
        $value = preg_replace('/[^A-Z0-9]+/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value));
        return $value;
    }

    private function isWilayahMatch($targetNormalized, $candidateNormalized)
    {
        $targetNormalized = trim((string) $targetNormalized);
        $candidateNormalized = trim((string) $candidateNormalized);

        if ($targetNormalized === '' || $candidateNormalized === '') {
            return false;
        }

        if ($targetNormalized === $candidateNormalized) {
            return true;
        }

        $targetTokens = preg_split('/\s+/', $targetNormalized);
        $candidateTokens = preg_split('/\s+/', $candidateNormalized);
        if (empty($targetTokens) || empty($candidateTokens)) {
            return false;
        }

        if (count($candidateTokens) >= count($targetTokens)) {
            $candidateTail = array_slice($candidateTokens, -count($targetTokens));
            if (implode(' ', $candidateTail) === implode(' ', $targetTokens)) {
                return true;
            }
        }

        if (count($targetTokens) >= count($candidateTokens)) {
            $targetTail = array_slice($targetTokens, -count($candidateTokens));
            if (implode(' ', $targetTail) === implode(' ', $candidateTokens)) {
                return true;
            }
        }

        return false;
    }

    private function buildClusterLocationSelect()
    {
        $locationColumns = [
            'regency_id',
            'district_id',
            'district_name',
            'village_id',
            'village_name',
        ];

        $selectParts = [];
        foreach ($locationColumns as $columnName) {
            if ($this->db->field_exists($columnName, 'tb_myrep_cluster')) {
                $selectParts[] = 'c.' . $columnName;
            } else {
                $selectParts[] = 'NULL AS ' . $columnName;
            }
        }

        return ', ' . implode(', ', $selectParts);
    }

    private function isCityAllowedForCurrentUser($cityName)
    {
        if (!$this->shouldRestrictCityByUser()) {
            return true;
        }

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if (empty($allowedCitySet)) {
            return false;
        }

        $cityName = strtoupper(trim((string) $cityName));
        return $cityName !== '' && isset($allowedCitySet[$cityName]);
    }

    public function updateBakFileStatus($fileId, $data)
    {
        if (!$this->bakDocumentTablesReady()) {
            return false;
        }

        $file = $this->db
            ->select('f.*, c.city_name')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$file || !$this->isCityAllowedForCurrentUser((string) ($file['city_name'] ?? ''))) {
            return false;
        }

        $statusFile = strtoupper((string) ($data['status_file'] ?? ''));
        $payload = [
            'status_file' => $statusFile,
            'remark' => (string) ($data['remark'] ?? ''),
            'approved_by' => (int) ($data['approved_by'] ?? 0),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => $statusFile === 'APPROVED' ? date('Y-m-d H:i:s') : null,
        ];

        $result = $this->db
            ->where('id_doc_file', (int) $fileId)
            ->update('tb_myrep_flow_doc_file', $payload);

        $actionType = $statusFile === 'APPROVED' ? 'APPROVE' : ($statusFile === 'REJECTED' ? 'REJECT' : 'UPLOAD');
        $this->createFileLog([
            'id_doc_file' => (int) $fileId,
            'id_doc_package' => (int) $file['id_doc_package'],
            'id_doc_item' => (int) $file['id_doc_item'],
            'action_type' => $actionType,
            'status_after' => $statusFile,
            'file_name' => (string) ($file['file_name'] ?? ''),
            'remark' => (string) ($data['remark'] ?? ''),
            'action_by' => (int) ($data['approved_by'] ?? 0),
        ]);

        $this->refreshPackageStatus((int) $file['id_doc_package']);
        return $result;
    }

    public function getBakFileById($fileId)
    {
        if (!$this->bakDocumentTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('
                f.*,
                p.id_myrep_cluster,
                c.cluster_name,
                c.city_name,
                i.doc_name
            ')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getBakFileLogs($fileId)
    {
        if (!$this->bakDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                l.*,
                u.nama_karyawan AS nama_user
            ')
            ->from('tb_myrep_flow_doc_file_log l')
            ->join('tb_master_user_new u', 'u.id = l.action_by', 'left')
            ->where('l.id_doc_file', (int) $fileId)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log', 'DESC')
            ->get()
            ->result_array();
    }

    public function updateBakStatusByCluster($clusterId, $statusBak, $statusCurrent, $userId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $existing = $this->getClusterById($clusterId);
        if (empty($existing)) {
            return false;
        }

        $statusBak = strtoupper(trim((string) $statusBak));
        $statusCurrent = strtoupper(trim((string) $statusCurrent));
        $userId = (int) $userId;

        $this->db->trans_start();
        $this->db
            ->where('id_myrep_cluster', $clusterId)
            ->update('tb_myrep_cluster', [
                'status_current' => $this->resolveSafeCurrentStatus(
                    (string) ($existing['status_current'] ?? ''),
                    $statusCurrent
                ),
                'updated_by' => $userId,
            ]);

        $this->db
            ->where('id_myrep_cluster', $clusterId)
            ->update('tb_myrep_bak', [
                'status_bak' => $statusBak,
                'updated_by' => $userId,
            ]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function syncBakStatusByCluster($clusterId, $userId)
    {
        $clusterId = (int) $clusterId;
        $userId = (int) $userId;
        if ($clusterId <= 0) {
            return false;
        }

        $definitions = $this->getBakDocumentDefinitions();
        if (empty($definitions)) {
            return $this->updateBakStatusByCluster($clusterId, 'ON REVIEW', 'BA OPEN', $userId);
        }

        $contextRows = $this->db
            ->select('doc_item.id_doc_item, doc_file.status_file')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . $clusterId . ' AND doc_package.flow_type = \'BAK\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', 'BAK')
            ->where('doc_group.group_label', 'BA OPEN')
            ->where('doc_group.is_active', 1)
            ->where_in('doc_item.doc_name', $this->getDefaultBakDocumentNames())
            ->order_by('doc_item.sort_no', 'ASC')
            ->get()
            ->result_array();

        $approvedCount = 0;
        $hasSubmitted = false;
        $hasRejected = false;
        foreach ($contextRows as $row) {
            $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
            if ($status === 'REJECTED') {
                $hasRejected = true;
            }
            if (in_array($status, ['UPLOADED', 'APPROVED'], true)) {
                $hasSubmitted = true;
            }
            if ($status === 'APPROVED') {
                $approvedCount++;
            }
        }

        if ($hasRejected) {
            return $this->updateBakStatusByCluster($clusterId, 'REJECTED', 'REJECTED', $userId);
        }

        if ($approvedCount >= count($definitions)) {
            return $this->updateBakStatusByCluster($clusterId, 'DONE', 'BAK', $userId);
        }

        if ($hasSubmitted) {
            return $this->updateBakStatusByCluster($clusterId, 'ON REVIEW', 'BA OPEN', $userId);
        }

        return $this->updateBakStatusByCluster($clusterId, 'DRAFT', 'BA OPEN', $userId);
    }

    private function ensureBakPackage($clusterId, $docGroupId, $userId)
    {
        $existing = $this->db->get_where('tb_myrep_flow_doc_package', [
            'id_myrep_cluster' => (int) $clusterId,
            'flow_type' => 'BAK',
            'id_doc_group' => (int) $docGroupId,
        ])->row_array();

        if ($existing) {
            return (int) $existing['id_doc_package'];
        }

        $this->db->insert('tb_myrep_flow_doc_package', [
            'id_myrep_cluster' => (int) $clusterId,
            'flow_type' => 'BAK',
            'ref_process_id' => null,
            'id_doc_group' => (int) $docGroupId,
            'status_package' => 'NOT STARTED',
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ]);

        return (int) $this->db->insert_id();
    }

    private function refreshPackageStatus($packageId)
    {
        $files = $this->db
            ->select('status_file')
            ->from('tb_myrep_flow_doc_file')
            ->where('id_doc_package', (int) $packageId)
            ->get()
            ->result_array();

        $statusPackage = 'NOT STARTED';
        if (!empty($files)) {
            $statusPackage = 'ON PROGRESS';
            $allApproved = true;
            foreach ($files as $file) {
                if (strtoupper((string) ($file['status_file'] ?? '')) !== 'APPROVED') {
                    $allApproved = false;
                    break;
                }
            }

            if ($allApproved) {
                $statusPackage = 'DONE';
            }
        }

        $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_myrep_flow_doc_package', [
                'status_package' => $statusPackage,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function createFileLog($data)
    {
        $this->db->insert('tb_myrep_flow_doc_file_log', [
            'id_doc_file' => (int) $data['id_doc_file'],
            'id_doc_package' => (int) $data['id_doc_package'],
            'id_doc_item' => (int) $data['id_doc_item'],
            'action_type' => (string) $data['action_type'],
            'status_after' => (string) $data['status_after'],
            'file_name' => (string) $data['file_name'],
            'remark' => (string) $data['remark'],
            'action_by' => (int) $data['action_by'],
            'action_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function deletePhysicalFile($filePath)
    {
        $filePath = trim((string) $filePath);
        if ($filePath === '') {
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function resolveSafeCurrentStatus($existingStatus, $requestedStatus)
    {
        $existingStatus = strtoupper(trim((string) $existingStatus));
        $requestedStatus = strtoupper(trim((string) $requestedStatus));

        $lockedStatuses = [
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
        ];

        if (in_array($existingStatus, $lockedStatuses, true)) {
            return $existingStatus;
        }

        return $requestedStatus !== '' ? $requestedStatus : 'DRAFT';
    }
}


