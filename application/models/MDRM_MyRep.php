<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MDRM_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;
    /** @var array<string,bool>|null */
    private $drmStatusEnumSet = null;

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    public function drmTablesReady()
    {
        $requiredTables = ['tb_myrep_cluster', 'tb_myrep_batch_approval', 'tb_myrep_drm', 'tb_rfs_myrep_monthly_target'];
        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function mainfeederDrmTablesReady()
    {
        return $this->db->table_exists('tb_rfs_myrep_mainfeeder')
            && $this->db->table_exists('tb_myrep_mainfeeder_drm')
            && $this->db->table_exists('tb_myrep_mainfeeder_drm_boq');
    }

    public function drmBoqTablesReady()
    {
        $requiredTables = [
            'md_myrep_boq_item',
            'tb_myrep_drm_boq',
            'tb_myrep_drm_boq_item',
            'tb_myrep_boq_baseline',
            'tb_myrep_boq_baseline_item',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function drmDocumentTablesReady()
    {
        $requiredTables = ['md_myrep_flow_doc_group', 'md_myrep_flow_doc_item', 'tb_myrep_flow_doc_package', 'tb_myrep_flow_doc_file', 'tb_myrep_flow_doc_file_log'];
        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function drmSubfeederReady()
    {
        return $this->drmDocumentTablesReady()
            && $this->drmBoqTablesReady()
            && $this->db->field_exists('scope_type', 'tb_myrep_drm_boq')
            && $this->db->field_exists('scope_type', 'tb_myrep_boq_baseline');
    }

    public function drmScopeRequirementTablesReady()
    {
        return $this->db->table_exists('tb_myrep_scope_requirement')
            && $this->db->table_exists('tb_myrep_scope_requirement_log');
    }

    public function getCityOptions()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.city_name', 'ASC')
            ->get()
            ->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        foreach ($this->getMainfeederCityOptions() as $cityName) {
            $cities[] = $cityName;
        }

        $cities = array_values(array_unique($cities));
        sort($cities);
        return $cities;
    }

    public function getEligibleClusterOptions()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $query = $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, ba.hp_donasi, ba.released_at, v.homepass_valsal, d.id_drm, t.year_num, t.month_num')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->where('d.id_drm IS NULL', null, false)
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $query->get()->result_array();
    }

    public function getDrmRows($city = '', $status = '', $regional = '', array $cityList = [], array $regionalList = [], $drmDateStart = '', $drmDateEnd = '', $projectType = '')
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $projectType = strtoupper(trim((string) $projectType));
        if (!in_array($projectType, ['CLUSTER', 'MAINFEEDER', 'FWA'], true)) {
            $projectType = '';
        }

        if (in_array($projectType, ['MAINFEEDER', 'FWA'], true)) {
            return $this->getMainfeederDrmRows($city, $status, $regional, $cityList, $regionalList, $drmDateStart, $drmDateEnd, $projectType);
        }

        $rfsClusterSelect = $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')
            ? 'c.rfs_cluster_id'
            : 'NULL AS rfs_cluster_id';

        $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, ' . $rfsClusterSelect . ', ba.hp_donasi, ba.released_at, d.id_drm, d.drm_date, d.homepass_drm, d.nama_olt, d.status_drm, d.screenshot_astri_path, d.screenshot_astri_name, d.remark_drm, t.year_num, t.month_num', false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED');

        if ($this->db->table_exists('tb_rfs_myrep_cluster') && $this->db->field_exists('status_atp', 'tb_rfs_myrep_cluster') && $this->db->field_exists('rfs_cluster_id', 'tb_myrep_cluster')) {
            $this->db
                ->select('rfs.status_atp AS stage_atp_status')
                ->join('tb_rfs_myrep_cluster rfs', 'rfs.id_cluster = c.rfs_cluster_id', 'left');
        } else {
            $this->db->select('NULL AS stage_atp_status', false);
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($regional !== '') {
            $this->db->where('UPPER(c.regional_name)', strtoupper($regional));
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
        if (!empty($regionalList)) {
            $normalizedRegionals = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $regionalList))));
            if (!empty($normalizedRegionals)) {
                $escapedRegionals = array_map([$this->db, 'escape'], $normalizedRegionals);
                $this->db->where('UPPER(c.regional_name) IN (' . implode(',', $escapedRegionals) . ')', null, false);
            }
        }
        if ($drmDateStart !== '') {
            $this->db->where('d.drm_date >=', $drmDateStart);
        }
        if ($drmDateEnd !== '') {
            $this->db->where('d.drm_date <=', $drmDateEnd);
        }

        $filterByDisplayStatus = false;
        if ($status !== '') {
            if (in_array($status, ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
                $this->db->where('UPPER(c.status_current)', $status);
            } else {
                $filterByDisplayStatus = true;
            }
        }

        $rows = $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $docSummaryMap = $this->getDocumentSummaryMap(array_column($rows, 'id_myrep_cluster'));
        $boqStatusMap = $this->getDrmBoqStatusMap(array_column($rows, 'id_myrep_cluster'));
        $scopeRequirementStatusMap = $this->getScopeRequirementStatusMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $row['project_type'] = 'CLUSTER';
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $summary = $docSummaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0, 'rejected' => 0];
            $row['doc_total'] = $summary['total'];
            $row['doc_uploaded'] = $summary['uploaded'];
            $row['doc_approved'] = $summary['approved'];
            $row['doc_rejected'] = $summary['rejected'];
            $row['drm_cluster_status'] = $boqStatusMap[$clusterId]['CLUSTER'] ?? '';
            $row['drm_subfeeder_status'] = $boqStatusMap[$clusterId]['SUBFEEDER'] ?? '';
            if (!empty($scopeRequirementStatusMap[$clusterId]['SUBFEEDER'])) {
                $row['drm_subfeeder_status'] = $scopeRequirementStatusMap[$clusterId]['SUBFEEDER'];
            }
            $row['display_status_drm'] = $this->resolveDisplayDrmStatus($row, $summary);
        }
        unset($row);

        if ($filterByDisplayStatus) {
            $rows = array_values(array_filter($rows, function ($row) use ($status) {
                return strtoupper(trim((string) ($row['display_status_drm'] ?? ''))) === strtoupper($status);
            }));
        }

        if ($projectType === '') {
            $rows = array_merge($rows, $this->getMainfeederDrmRows($city, $status, $regional, $cityList, $regionalList, $drmDateStart, $drmDateEnd));
        }

        return $rows;
    }

    private function getMainfeederDrmRows($city = '', $status = '', $regional = '', array $cityList = [], array $regionalList = [], $drmDateStart = '', $drmDateEnd = '', $projectType = '')
    {
        if (!$this->mainfeederDrmTablesReady()) {
            return [];
        }

        $projectType = strtoupper(trim((string) $projectType));
        if (!in_array($projectType, ['MAINFEEDER', 'FWA'], true)) {
            $projectType = '';
        }
        $projectTypeSql = $this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')
            ? "COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER')"
            : "'MAINFEEDER'";

        $this->db
            ->select("
                mf.id_mainfeeder,
                0 AS id_myrep_cluster,
                {$projectTypeSql} AS project_type,
                mf.mainfeeder_name AS cluster_name,
                mf.cluster_code,
                COALESCE(mf.regional_name, mt.regional_name) AS regional_name,
                COALESCE(mf.city_name, mt.city_name) AS city_name,
                mf.current_status AS status_current,
                mf.current_status AS stage_atp_status,
                mf.length_meter AS hp_donasi,
                NULL AS released_at,
                drm.id_mainfeeder_drm AS id_drm,
                drm.id_mainfeeder_drm,
                drm.drm_date,
                mf.length_meter AS homepass_drm,
                drm.nama_olt,
                drm.status_drm,
                NULL AS screenshot_astri_path,
                NULL AS screenshot_astri_name,
                drm.remark_drm,
                mf.year_num,
                mf.month_num,
                boq.review_status AS drm_cluster_status
            ", false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->join('tb_myrep_mainfeeder_drm drm', 'drm.id_mainfeeder = mf.id_mainfeeder', 'left')
            ->join('tb_myrep_mainfeeder_drm_boq boq', 'boq.id_mainfeeder = mf.id_mainfeeder', 'left');

        if ($projectType !== '') {
            if ($this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
                $this->db->where($projectTypeSql . ' = ' . $this->db->escape($projectType), null, false);
            } elseif ($projectType === 'FWA') {
                return [];
            }
        }

        if (!$this->applyAllowedCityRestriction('COALESCE(mf.city_name, mt.city_name)')) {
            return [];
        }
        if ($city !== '') {
            $this->db->where('UPPER(COALESCE(mf.city_name, mt.city_name)) = ' . $this->db->escape(strtoupper($city)), null, false);
        }
        if ($regional !== '') {
            $this->db->where('UPPER(COALESCE(mf.regional_name, mt.regional_name)) = ' . $this->db->escape(strtoupper($regional)), null, false);
        }
        if (!empty($cityList)) {
            $normalizedCities = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $cityList))));
            if (!empty($normalizedCities)) {
                $escapedCities = array_map([$this->db, 'escape'], $normalizedCities);
                $this->db->where('UPPER(COALESCE(mf.city_name, mt.city_name)) IN (' . implode(',', $escapedCities) . ')', null, false);
            }
        }
        if (!empty($regionalList)) {
            $normalizedRegionals = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $regionalList))));
            if (!empty($normalizedRegionals)) {
                $escapedRegionals = array_map([$this->db, 'escape'], $normalizedRegionals);
                $this->db->where('UPPER(COALESCE(mf.regional_name, mt.regional_name)) IN (' . implode(',', $escapedRegionals) . ')', null, false);
            }
        }
        if ($drmDateStart !== '') {
            $this->db->where('drm.drm_date >=', $drmDateStart);
        }
        if ($drmDateEnd !== '') {
            $this->db->where('drm.drm_date <=', $drmDateEnd);
        }

        $filterByDisplayStatus = false;
        if ($status !== '') {
            if (in_array($status, ['DRM', 'IMPLEMENTASI', 'ATP', 'CHECKLIST', 'DONE'], true)) {
                $this->db->where('UPPER(mf.current_status)', $status);
            } else {
                $filterByDisplayStatus = true;
            }
        }

        $rows = $this->db
            ->order_by('mf.updated_at', 'DESC')
            ->order_by('mf.mainfeeder_name', 'ASC')
            ->get()
            ->result_array();

        $docSummaryMap = $this->getMainfeederDocumentSummaryMap(array_column($rows, 'id_mainfeeder'));
        foreach ($rows as &$row) {
            $mainfeederId = (int) ($row['id_mainfeeder'] ?? 0);
            $summary = $docSummaryMap[$mainfeederId] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0, 'rejected' => 0];
            $row['project_type'] = strtoupper(trim((string) ($row['project_type'] ?? 'MAINFEEDER'))) ?: 'MAINFEEDER';
            $row['doc_total'] = $summary['total'];
            $row['doc_uploaded'] = $summary['uploaded'];
            $row['doc_approved'] = $summary['approved'];
            $row['doc_rejected'] = $summary['rejected'];
            $row['drm_cluster_status'] = strtoupper(trim((string) ($row['drm_cluster_status'] ?? '')));
            $row['drm_subfeeder_status'] = '';
            $row['display_status_drm'] = $this->resolveDisplayDrmStatus($row, $summary);
        }
        unset($row);

        if ($filterByDisplayStatus) {
            $rows = array_values(array_filter($rows, function ($row) use ($status) {
                return strtoupper(trim((string) ($row['display_status_drm'] ?? ''))) === strtoupper($status);
            }));
        }

        return $rows;
    }

    private function getMainfeederCityOptions()
    {
        $rows = $this->getMainfeederRegionalCityRows();
        $cities = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        return array_values(array_unique($cities));
    }

    private function getMainfeederRegionalOptions()
    {
        $rows = $this->getMainfeederRegionalCityRows();
        $regionals = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regionalName !== '') {
                $regionals[] = $regionalName;
            }
        }

        return array_values(array_unique($regionals));
    }

    private function getMainfeederRegionalCityRows()
    {
        if (!$this->mainfeederDrmTablesReady()) {
            return [];
        }

        $this->db
            ->distinct()
            ->select('COALESCE(mf.regional_name, mt.regional_name) AS regional_name, COALESCE(mf.city_name, mt.city_name) AS city_name', false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('COALESCE(mf.city_name, mt.city_name) IS NOT NULL', null, false)
            ->where('CHAR_LENGTH(TRIM(COALESCE(mf.city_name, mt.city_name))) > 0', null, false);

        if (!$this->applyAllowedCityRestriction('COALESCE(mf.city_name, mt.city_name)')) {
            return [];
        }

        return $this->db
            ->order_by('regional_name', 'ASC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getRegionalOptions()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.regional_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where("TRIM(c.regional_name) !=", '')
            ->order_by('c.regional_name', 'ASC')
            ->get()
            ->result_array();

        $regionals = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regionalName !== '') {
                $regionals[] = $regionalName;
            }
        }
        foreach ($this->getMainfeederRegionalOptions() as $regionalName) {
            $regionals[] = $regionalName;
        }
        sort($regionals);
        return array_values(array_unique($regionals));
    }

    public function getCityOptionsByRegional()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.regional_name, c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.regional_name) !=", '')
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.regional_name', 'ASC')
            ->order_by('c.city_name', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($regionalName === '' || $cityName === '') continue;
            if (!isset($map[$regionalName])) $map[$regionalName] = [];
            $map[$regionalName][] = $cityName;
        }
        foreach ($this->getMainfeederRegionalCityRows() as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($regionalName === '' || $cityName === '') continue;
            if (!isset($map[$regionalName])) $map[$regionalName] = [];
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
        $byRegional = $this->getCityOptionsByRegional();
        $map = [];
        foreach ($byRegional as $regional => $cities) {
            foreach ($cities as $city) {
                if (!isset($map[$city])) $map[$city] = [];
                $map[$city][] = $regional;
            }
        }
        foreach ($map as $city => $regionals) {
            $regionals = array_values(array_unique($regionals));
            sort($regionals);
            $map[$city] = $regionals;
        }
        return $map;
    }

    public function getDrmCandidateById($clusterId)
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, ba.id_batch_approval, ba.hp_donasi, ba.released_at, d.id_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getDrmByClusterId($clusterId)
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('
                c.*,
                ba.id_batch_approval,
                ba.hp_donasi,
                ba.released_at,
                d.id_drm,
                d.drm_date,
                d.homepass_drm,
                d.nama_olt,
                d.status_drm,
                d.screenshot_astri_path,
                d.screenshot_astri_name,
                d.remark_drm,
                d.created_by AS drm_created_by,
                d.updated_by AS drm_updated_by,
                d.created_at AS drm_created_at,
                d.updated_at AS drm_updated_at
            ', false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (!$row) {
            return [];
        }

        if (!$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return [];
        }

        $summary = $this->getDocumentSummaryMap([(int) $clusterId])[(int) $clusterId] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0, 'rejected' => 0];
        $row['doc_total'] = $summary['total'];
        $row['doc_uploaded'] = $summary['uploaded'];
        $row['doc_approved'] = $summary['approved'];
        $row['doc_rejected'] = $summary['rejected'];
        $row['display_status_drm'] = $this->resolveDisplayDrmStatus($row, $summary);

        return $row;
    }

    public function getBoqMasterItems()
    {
        if (!$this->drmBoqTablesReady()) {
            return [];
        }

        return $this->db
            ->from('md_myrep_boq_item')
            ->where('is_active', 1)
            ->order_by('sort_no', 'ASC')
            ->order_by('id_boq_item', 'ASC')
            ->get()
            ->result_array();
    }

    public function getDrmBoqHeader($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return [];
        }

        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $this->db->from('tb_myrep_drm_boq')
            ->where('id_myrep_cluster', (int) $clusterId);
        if ($this->db->field_exists('scope_type', 'tb_myrep_drm_boq')) {
            $this->db->where('scope_type', $scopeType);
        }

        return $this->db->get()->row_array();
    }

    public function getDrmBoqItems($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return [];
        }

        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $joinHeader = 'h.id_myrep_cluster = ' . (int) $clusterId;
        if ($this->db->field_exists('scope_type', 'tb_myrep_drm_boq')) {
            $joinHeader .= " AND h.scope_type = " . $this->db->escape($scopeType);
        }

        return $this->db
            ->select('m.id_boq_item, m.excel_item_name, m.item_name, m.item_type, m.item_satuan, m.default_photo_qty, m.photo_type, m.remarks_rule AS master_remarks_rule, m.sort_no, h.id_drm_boq, h.review_status, i.id_drm_boq_item, i.qty_boq, i.jumlah_foto, i.remarks_rule, i.target_foto_required, i.item_note')
            ->from('md_myrep_boq_item m')
            ->join('tb_myrep_drm_boq h', $joinHeader, 'left', false)
            ->join('tb_myrep_drm_boq_item i', 'i.id_drm_boq = h.id_drm_boq AND i.id_boq_item = m.id_boq_item', 'left')
            ->where('m.is_active', 1)
            ->order_by('m.sort_no', 'ASC')
            ->order_by('m.id_boq_item', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBoqBaselineHeader($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return [];
        }

        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $this->db->from('tb_myrep_boq_baseline')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->where('status_baseline', 'ACTIVE');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('scope_type', $scopeType);
        }

        return $this->db->get()->row_array();
    }

    public function getBoqBaselineItems($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return [];
        }

        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $this->db
            ->select('b.id_boq_baseline, i.id_boq_baseline_item, i.qty_boq, i.jumlah_foto, i.remarks_rule, i.target_foto_required, i.item_note, m.excel_item_name, m.item_name, m.item_type, m.photo_type, m.sort_no')
            ->from('tb_myrep_boq_baseline b')
            ->join('tb_myrep_boq_baseline_item i', 'i.id_boq_baseline = b.id_boq_baseline', 'inner')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = i.id_boq_item', 'inner')
            ->where('b.id_myrep_cluster', (int) $clusterId)
            ->where('b.status_baseline', 'ACTIVE');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', $scopeType);
        }

        return $this->db
            ->order_by('m.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getScopeRequirement($clusterId, $scopeType = 'SUBFEEDER')
    {
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $default = [
            'id_scope_requirement' => 0,
            'id_myrep_cluster' => (int) $clusterId,
            'scope_type' => $scopeType,
            'requirement_status' => 'REQUIRED',
            'request_remark' => null,
            'review_remark' => null,
            'requested_by' => null,
            'requested_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'reopened_by' => null,
            'reopened_at' => null,
            'reopen_remark' => null,
        ];

        if (!$this->drmScopeRequirementTablesReady()) {
            return $default;
        }

        $row = $this->db
            ->from('tb_myrep_scope_requirement')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->where('scope_type', $scopeType)
            ->get()
            ->row_array();

        if (empty($row)) {
            return $default;
        }

        $row['requirement_status'] = strtoupper(trim((string) ($row['requirement_status'] ?? 'REQUIRED')));
        return array_merge($default, $row);
    }

    public function requestScopeNotRequired($clusterId, $userId, $remark, $scopeType = 'SUBFEEDER')
    {
        if (!$this->drmScopeRequirementTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $userId = (int) $userId;
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $remark = trim((string) $remark);
        if ($clusterId <= 0 || $scopeType !== 'SUBFEEDER' || $remark === '') {
            return false;
        }

        $existing = $this->getScopeRequirement($clusterId, $scopeType);
        $status = strtoupper(trim((string) ($existing['requirement_status'] ?? 'REQUIRED')));
        if ($status === 'NOT_REQUIRED_PENDING' || $status === 'NOT_REQUIRED_APPROVED') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'id_myrep_cluster' => $clusterId,
            'scope_type' => $scopeType,
            'requirement_status' => 'NOT_REQUIRED_PENDING',
            'request_remark' => $remark,
            'review_remark' => null,
            'requested_by' => $userId,
            'requested_at' => $now,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'reopened_by' => null,
            'reopened_at' => null,
            'reopen_remark' => null,
            'updated_by' => $userId,
        ];

        $this->db->trans_start();
        if (!empty($existing['id_scope_requirement'])) {
            $this->db
                ->where('id_scope_requirement', (int) $existing['id_scope_requirement'])
                ->update('tb_myrep_scope_requirement', $payload);
            $requirementId = (int) $existing['id_scope_requirement'];
        } else {
            $payload['created_by'] = $userId;
            $this->db->insert('tb_myrep_scope_requirement', $payload);
            $requirementId = (int) $this->db->insert_id();
        }

        $this->createScopeRequirementLog($requirementId, $clusterId, $scopeType, 'REQUEST_NOT_REQUIRED', 'NOT_REQUIRED_PENDING', $remark, $userId);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function reviewScopeNotRequired($clusterId, $userId, $approved, $remark = '', $scopeType = 'SUBFEEDER')
    {
        if (!$this->drmScopeRequirementTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $userId = (int) $userId;
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        if ($clusterId <= 0 || $scopeType !== 'SUBFEEDER') {
            return false;
        }

        $existing = $this->getScopeRequirement($clusterId, $scopeType);
        if (empty($existing['id_scope_requirement']) || strtoupper((string) ($existing['requirement_status'] ?? '')) !== 'NOT_REQUIRED_PENDING') {
            return false;
        }

        $newStatus = $approved ? 'NOT_REQUIRED_APPROVED' : 'NOT_REQUIRED_REJECTED';
        $actionType = $approved ? 'APPROVE_NOT_REQUIRED' : 'REJECT_NOT_REQUIRED';
        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();
        $this->db
            ->where('id_scope_requirement', (int) $existing['id_scope_requirement'])
            ->update('tb_myrep_scope_requirement', [
                'requirement_status' => $newStatus,
                'review_remark' => trim((string) $remark) !== '' ? trim((string) $remark) : null,
                'reviewed_by' => $userId,
                'reviewed_at' => $now,
                'updated_by' => $userId,
            ]);

        $this->createScopeRequirementLog((int) $existing['id_scope_requirement'], $clusterId, $scopeType, $actionType, $newStatus, trim((string) $remark), $userId);
        if ($approved) {
            $this->rebuildCombinedBaselineIfReady($clusterId, $now, $userId);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function reopenScopeRequirement($clusterId, $userId, $remark = '', $scopeType = 'SUBFEEDER')
    {
        if (!$this->drmScopeRequirementTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $userId = (int) $userId;
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        if ($clusterId <= 0 || $scopeType !== 'SUBFEEDER') {
            return false;
        }

        $existing = $this->getScopeRequirement($clusterId, $scopeType);
        if (empty($existing['id_scope_requirement']) || strtoupper((string) ($existing['requirement_status'] ?? '')) !== 'NOT_REQUIRED_APPROVED') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db
            ->where('id_scope_requirement', (int) $existing['id_scope_requirement'])
            ->update('tb_myrep_scope_requirement', [
                'requirement_status' => 'REQUIRED',
                'reopened_by' => $userId,
                'reopened_at' => $now,
                'reopen_remark' => trim((string) $remark) !== '' ? trim((string) $remark) : null,
                'updated_by' => $userId,
            ]);

        $this->createScopeRequirementLog((int) $existing['id_scope_requirement'], $clusterId, $scopeType, 'REOPEN_REQUIRED', 'REQUIRED', trim((string) $remark), $userId);

        $subfeederHeader = $this->getDrmBoqHeader($clusterId, 'SUBFEEDER');
        $subfeederApproved = !empty($subfeederHeader['id_drm_boq']) && strtoupper(trim((string) ($subfeederHeader['review_status'] ?? ''))) === 'APPROVED';
        if (!$subfeederApproved && $this->drmBoqTablesReady()) {
            $this->replaceActiveBaselines($clusterId, 'CLUSTER');
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function createScopeRequirementLog($requirementId, $clusterId, $scopeType, $actionType, $statusAfter, $remark, $userId)
    {
        if (!$this->db->table_exists('tb_myrep_scope_requirement_log')) {
            return;
        }

        $this->db->insert('tb_myrep_scope_requirement_log', [
            'id_scope_requirement' => (int) $requirementId,
            'id_myrep_cluster' => (int) $clusterId,
            'scope_type' => $this->normalizeDrmScopeType($scopeType),
            'action_type' => (string) $actionType,
            'status_after' => (string) $statusAfter,
            'remark' => trim((string) $remark) !== '' ? trim((string) $remark) : null,
            'action_by' => (int) $userId,
            'action_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getApdBoqDocumentFile($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        $flowType = $this->resolveDrmDocumentFlowType($scopeType);
        return $this->db
            ->select('doc_file.id_doc_file, doc_file.file_name, doc_file.file_path, doc_file.status_file, doc_file.reviewed_at, doc_file.approved_at')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = ' . $this->db->escape($flowType) . ' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', $flowType)
            ->where('doc_item.doc_name', 'APD BOQ')
            ->get()
            ->row_array();
    }

    public function createDrm($clusterId, $drmPayload, $clusterPayload)
    {
        $drmPayload = $this->normalizeDrmPayloadForStorage($drmPayload);

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', (int) $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $drmPayload['id_myrep_cluster'] = (int) $clusterId;
        $this->db->insert('tb_myrep_drm', $drmPayload);
        $this->db->trans_complete();

        return $this->db->trans_status();
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

    public function getTargetById($targetId)
    {
        $targetId = (int) $targetId;
        if ($targetId <= 0 || !$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        return $this->db
            ->from('tb_rfs_myrep_monthly_target')
            ->where('id_target', $targetId)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getClusterForDrmImportById($clusterId)
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, b.id_bak, b.status_bak, v.id_valsal, v.status_valsal, ba.id_batch_approval, ba.staging_status, d.id_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getClusterForDrmImportByName($clusterName, $cityName = '', $targetId = 0)
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $clusterName = strtoupper(trim((string) $clusterName));
        $cityName = strtoupper(trim((string) $cityName));
        $targetId = (int) $targetId;
        if ($clusterName === '') {
            return [];
        }

        $this->db
            ->select('c.*, b.id_bak, b.status_bak, v.id_valsal, v.status_valsal, ba.id_batch_approval, ba.staging_status, d.id_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('UPPER(c.cluster_name)', $clusterName);

        if ($cityName !== '') {
            $this->db->where('UPPER(c.city_name)', $cityName);
        }
        if ($targetId > 0) {
            $this->db->where('c.id_target', $targetId);
        }

        $row = $this->db->order_by('c.created_at', 'DESC')->limit(1)->get()->row_array();
        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function createClusterForDrmImport($targetId, $clusterName, $clusterCode, $homepassPlan, $userId)
    {
        $targetId = (int) $targetId;
        $clusterName = trim((string) $clusterName);
        $clusterCode = trim((string) $clusterCode);
        $homepassPlan = (int) $homepassPlan;
        $userId = (int) $userId;
        if ($targetId <= 0 || $clusterName === '' || $homepassPlan <= 0) {
            return 0;
        }

        $target = $this->getTargetById($targetId);
        if (empty($target)) {
            return 0;
        }

        $this->db->insert('tb_myrep_cluster', [
            'id_target' => $targetId,
            'cluster_name' => $clusterName,
            'cluster_code' => $clusterCode !== '' ? $clusterCode : null,
            'regional_name' => $target['regional_name'] ?? null,
            'province_name' => $target['province_name'] ?? null,
            'city_name' => $target['city_name'] ?? null,
            'regency_id' => null,
            'district_id' => null,
            'district_name' => null,
            'village_id' => null,
            'village_name' => null,
            'team_name' => $target['team_name'] ?? null,
            'chief' => $target['chief'] ?? null,
            'rpm' => $target['rpm'] ?? null,
            'sm' => $target['sm'] ?? null,
            'spv' => $target['spv'] ?? null,
            'hp_plan' => $homepassPlan,
            'status_current' => 'BAK',
            'remark_general' => null,
            'created_by' => $userId > 0 ? $userId : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        return (int) $this->db->insert_id();
    }

    public function upsertBakValsalBatchForDrmImport($clusterId, $homepass, $activityDate, $userId, $remark = '')
    {
        $clusterId = (int) $clusterId;
        $homepass = (int) $homepass;
        $activityDate = trim((string) $activityDate) !== '' ? (string) $activityDate : date('Y-m-d');
        $userId = (int) $userId;
        $remark = trim((string) $remark);
        if ($clusterId <= 0 || $homepass <= 0) {
            return false;
        }

        $this->db->trans_start();

        if ($this->db->table_exists('tb_myrep_bak')) {
            $bakPayload = [
                'ba_open_date' => $activityDate,
                'bak_date' => $activityDate,
                'homepass_bak' => $homepass,
                'status_bak' => 'DONE',
                'remark_bak' => $remark !== '' ? $remark : null,
                'updated_by' => $userId > 0 ? $userId : null,
            ];
            $existingBak = $this->db->get_where('tb_myrep_bak', ['id_myrep_cluster' => $clusterId])->row_array();
            if ($existingBak) {
                $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_bak', $bakPayload);
            } else {
                $bakPayload['id_myrep_cluster'] = $clusterId;
                $bakPayload['created_by'] = $userId > 0 ? $userId : null;
                $this->db->insert('tb_myrep_bak', $bakPayload);
            }
        }

        if ($this->db->table_exists('tb_myrep_valsal')) {
            $valsalPayload = [
                'valsal_date' => $activityDate,
                'homepass_valsal' => $homepass,
                'status_valsal' => 'DONE',
                'remark_valsal' => $remark !== '' ? $remark : null,
                'updated_by' => $userId > 0 ? $userId : null,
            ];
            $existingValsal = $this->db->get_where('tb_myrep_valsal', ['id_myrep_cluster' => $clusterId])->row_array();
            if ($existingValsal) {
                $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_valsal', $valsalPayload);
            } else {
                $valsalPayload['id_myrep_cluster'] = $clusterId;
                $valsalPayload['created_by'] = $userId > 0 ? $userId : null;
                $this->db->insert('tb_myrep_valsal', $valsalPayload);
            }
        }

        if ($this->db->table_exists('tb_myrep_batch_approval')) {
            $batchPayload = [
                'submission_date' => $activityDate,
                'hp_donasi' => $homepass,
                'nominal_pengajuan_area' => 0,
                'nominal_per_homepass' => 0,
                'bank_name' => '-',
                'bank_account_number' => '-',
                'recipient_name' => 'AUTO IMPORT DRM',
                'staging_status' => 'RELEASED',
                'released_at' => date('Y-m-d H:i:s'),
                'remark_batch_approval' => $remark !== '' ? $remark : null,
                'updated_by' => $userId > 0 ? $userId : null,
            ];
            $existingBatch = $this->db->get_where('tb_myrep_batch_approval', ['id_myrep_cluster' => $clusterId])->row_array();
            if ($existingBatch) {
                $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_batch_approval', $batchPayload);
            } else {
                $batchPayload['id_myrep_cluster'] = $clusterId;
                $batchPayload['created_by'] = $userId > 0 ? $userId : null;
                $this->db->insert('tb_myrep_batch_approval', $batchPayload);
            }
        }

        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', [
            'hp_plan' => $homepass,
            'status_current' => 'RELEASED',
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function updateDrm($clusterId, $drmId, $drmPayload, $clusterPayload)
    {
        $clusterId = (int) $clusterId;
        $drmId = (int) $drmId;
        if ($clusterId <= 0 || $drmId <= 0) {
            return false;
        }

        $existing = $this->getDrmByClusterId($clusterId);
        if (empty($existing)) {
            return false;
        }

        $clusterPayload['status_current'] = $this->resolveSafeCurrentStatus((string) ($existing['status_current'] ?? ''), (string) ($clusterPayload['status_current'] ?? 'RELEASED'));
        $drmPayload = $this->normalizeDrmPayloadForStorage($drmPayload);

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $this->db->where('id_drm', $drmId)->update('tb_myrep_drm', $drmPayload);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function getDrmDocumentRows($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        if (empty($this->getDrmByClusterId((int) $clusterId))) {
            return [];
        }

        $flowType = $this->resolveDrmDocumentFlowType($scopeType);
        return $this->db
            ->select('doc_group.group_label, doc_item.id_doc_item, doc_item.doc_name, doc_item.doc_requirement_note, doc_item.verification_team, doc_package.id_doc_package, doc_package.status_package, doc_file.id_doc_file, doc_file.file_name, doc_file.file_path, doc_file.status_file, doc_file.is_document_not_required, doc_file.remark, doc_file.uploaded_at, doc_file.reviewed_at, doc_file.approved_at')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = ' . $this->db->escape($flowType) . ' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', $flowType)
            ->where('doc_group.is_active', 1)
            ->order_by('doc_group.sort_no', 'ASC')
            ->order_by('doc_item.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getDrmDocumentDetail($clusterId, $docItemId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        if (empty($this->getDrmByClusterId((int) $clusterId))) {
            return [];
        }

        $flowType = $this->resolveDrmDocumentFlowType($scopeType);
        return $this->db
            ->select('doc_group.id_doc_group, doc_group.group_label, doc_item.id_doc_item, doc_item.doc_name, doc_package.id_doc_package, doc_file.id_doc_file, doc_file.file_path')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = ' . $this->db->escape($flowType) . ' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', $flowType)
            ->where('doc_group.is_active', 1)
            ->where('doc_item.id_doc_item', (int) $docItemId)
            ->get()
            ->row_array();
    }

    public function getDrmDocumentDetailByName($clusterId, $docName, $scopeType = 'CLUSTER')
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        if (empty($this->getDrmByClusterId((int) $clusterId))) {
            return [];
        }

        $flowType = $this->resolveDrmDocumentFlowType($scopeType);
        return $this->db
            ->select('doc_group.id_doc_group, doc_group.group_label, doc_item.id_doc_item, doc_item.doc_name, doc_package.id_doc_package, doc_file.id_doc_file, doc_file.file_name, doc_file.file_path, doc_file.status_file')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = ' . $this->db->escape($flowType) . ' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', $flowType)
            ->where('doc_item.doc_name', (string) $docName)
            ->get()
            ->row_array();
    }

    public function saveDrmFileUpload($clusterId, $docItemId, $data, $scopeType = 'CLUSTER')
    {
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $context = $this->getDrmDocumentDetail($clusterId, $docItemId, $scopeType);
        if (empty($context['id_doc_group']) || empty($context['id_doc_item'])) {
            return 0;
        }

        $packageId = $this->ensurePackage($clusterId, $this->resolveDrmDocumentFlowType($scopeType), (int) $context['id_doc_group'], (int) $data['uploaded_by']);
        if ($packageId <= 0) {
            return 0;
        }

        $existing = $this->db->get_where('tb_myrep_flow_doc_file', ['id_doc_package' => $packageId, 'id_doc_item' => (int) $docItemId])->row_array();
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
            $this->db->where('id_doc_file', (int) $existing['id_doc_file'])->update('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $actionType = 'REUPLOAD';
        } else {
            $payload['id_doc_package'] = $packageId;
            $payload['id_doc_item'] = (int) $docItemId;
            $this->db->insert('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $actionType = 'UPLOAD';
        }

        $this->createFileLog([
            'id_doc_file' => $fileId,
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $docItemId,
            'action_type' => $actionType,
            'status_after' => (string) $data['status_file'],
            'file_name' => $data['file_name'] !== '' ? (string) $data['file_name'] : '[Tanpa Dokumen]',
            'remark' => (string) $data['remark'],
            'action_by' => (int) $data['uploaded_by'],
        ]);

        $this->refreshPackageStatus($packageId);
        return $fileId;
    }

    public function updateDrmFileStatus($fileId, $data)
    {
        if (!$this->drmDocumentTablesReady()) {
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

        $result = $this->db->where('id_doc_file', (int) $fileId)->update('tb_myrep_flow_doc_file', $payload);
        $this->createFileLog([
            'id_doc_file' => (int) $fileId,
            'id_doc_package' => (int) $file['id_doc_package'],
            'id_doc_item' => (int) $file['id_doc_item'],
            'action_type' => $statusFile === 'APPROVED' ? 'APPROVE' : 'REJECT',
            'status_after' => $statusFile,
            'file_name' => (string) ($file['file_name'] ?? ''),
            'remark' => (string) ($data['remark'] ?? ''),
            'action_by' => (int) ($data['approved_by'] ?? 0),
        ]);

        $this->refreshPackageStatus((int) $file['id_doc_package']);
        return $result;
    }

    public function saveDrmBoqDraft($clusterId, $drmId, $sourceDocFileId, $items, $userId, $submitToHo = false, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $drmId = (int) $drmId;
        $userId = (int) $userId;
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        if ($clusterId <= 0 || empty($items)) {
            return false;
        }

        $existing = $this->getDrmBoqHeader($clusterId, $scopeType);
        if (!empty($existing) && strtoupper((string) ($existing['review_status'] ?? '')) === 'APPROVED') {
            return false;
        }

        $reviewStatus = $submitToHo ? 'WAITING HO' : (($existing['review_status'] ?? '') === 'REJECTED' ? 'REJECTED' : 'DRAFT');

        $this->db->trans_start();

        if (!empty($existing['id_drm_boq'])) {
            $submittedAt = !empty($existing['submitted_at'])
                ? (string) $existing['submitted_at']
                : date('Y-m-d H:i:s');
            if ($submitToHo) {
                $submittedAt = date('Y-m-d H:i:s');
            }
            $this->db
                ->where('id_drm_boq', (int) $existing['id_drm_boq'])
                ->update('tb_myrep_drm_boq', [
                    'id_drm' => $drmId > 0 ? $drmId : null,
                    'source_doc_file_id' => $sourceDocFileId > 0 ? (int) $sourceDocFileId : null,
                    'review_status' => $reviewStatus,
                    'submitted_at' => $submittedAt,
                    'updated_by' => $userId,
                    'approved_at' => null,
                    'rejected_at' => null,
                    'approved_by' => null,
                    'ho_review_remark' => null,
                ]);
            $drmBoqId = (int) $existing['id_drm_boq'];
        } else {
            $payload = [
                'id_myrep_cluster' => $clusterId,
                'id_drm' => $drmId > 0 ? $drmId : null,
                'source_doc_file_id' => $sourceDocFileId > 0 ? (int) $sourceDocFileId : null,
                'review_status' => $reviewStatus,
                'submitted_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
            if ($this->db->field_exists('scope_type', 'tb_myrep_drm_boq')) {
                $payload['scope_type'] = $scopeType;
            }
            $this->db->insert('tb_myrep_drm_boq', $payload);
            $drmBoqId = (int) $this->db->insert_id();
        }

        foreach ($items as $item) {
            $boqItemId = (int) ($item['id_boq_item'] ?? 0);
            if ($boqItemId <= 0) {
                continue;
            }

            $payload = [
                'qty_boq' => (float) ($item['qty_boq'] ?? 0),
                'jumlah_foto' => (int) ($item['jumlah_foto'] ?? 0),
                'remarks_rule' => (string) ($item['remarks_rule'] ?? 'SESUAI ITEM'),
                'target_foto_required' => (int) ($item['target_foto_required'] ?? 0),
                'item_note' => !empty($item['item_note']) ? (string) $item['item_note'] : null,
            ];

            $existingItem = $this->db->get_where('tb_myrep_drm_boq_item', [
                'id_drm_boq' => $drmBoqId,
                'id_boq_item' => $boqItemId,
            ])->row_array();

            if ($existingItem) {
                $this->db
                    ->where('id_drm_boq_item', (int) $existingItem['id_drm_boq_item'])
                    ->update('tb_myrep_drm_boq_item', $payload);
            } else {
                $payload['id_drm_boq'] = $drmBoqId;
                $payload['id_boq_item'] = $boqItemId;
                $this->db->insert('tb_myrep_drm_boq_item', $payload);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function approveDrmBoq($clusterId, $userId, $remark = '', $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady() || !$this->drmDocumentTablesReady()) {
            return false;
        }

        $scopeType = $this->normalizeDrmScopeType($scopeType);
        $header = $this->getDrmBoqHeader($clusterId, $scopeType);
        $items = $this->getDrmBoqItems($clusterId, $scopeType);
        if (empty($header['id_drm_boq']) || empty($items)) {
            return false;
        }

        $userId = (int) $userId;
        $approvedAt = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db
            ->where('id_drm_boq', (int) $header['id_drm_boq'])
            ->update('tb_myrep_drm_boq', [
                'review_status' => 'APPROVED',
                'approved_at' => $approvedAt,
                'rejected_at' => null,
                'approved_by' => $userId,
                'ho_review_remark' => $remark !== '' ? $remark : null,
                'updated_by' => $userId,
            ]);

        $this->rebuildCombinedBaselineIfReady((int) $clusterId, $approvedAt, $userId);

        $flowType = $this->resolveDrmDocumentFlowType($scopeType);
        $documentFiles = $this->db
            ->select('f.id_doc_file, f.id_doc_package, f.id_doc_item, f.file_name, f.status_file')
            ->from('tb_myrep_flow_doc_package p')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package', 'inner')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'inner')
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->where('p.flow_type', $flowType)
            ->where('UPPER(i.doc_name)', 'APD BOQ')
            ->get()
            ->result_array();

        $packageIds = [];
        foreach ($documentFiles as $file) {
            $packageIds[(int) $file['id_doc_package']] = true;
            if (strtoupper((string) ($file['status_file'] ?? '')) !== 'UPLOADED') {
                continue;
            }

            $this->db
                ->where('id_doc_file', (int) $file['id_doc_file'])
                ->update('tb_myrep_flow_doc_file', [
                    'status_file' => 'APPROVED',
                    'remark' => $remark !== '' ? $remark : 'Approved via BOQ review',
                    'approved_by' => $userId,
                    'reviewed_at' => $approvedAt,
                    'approved_at' => $approvedAt,
                ]);

            $this->createFileLog([
                'id_doc_file' => (int) $file['id_doc_file'],
                'id_doc_package' => (int) $file['id_doc_package'],
                'id_doc_item' => (int) $file['id_doc_item'],
                'action_type' => 'APPROVE',
                'status_after' => 'APPROVED',
                'file_name' => (string) ($file['file_name'] ?? ''),
                'remark' => $remark !== '' ? $remark : 'Approved via BOQ review',
                'action_by' => $userId,
            ]);
        }

        foreach (array_keys($packageIds) as $packageId) {
            $this->refreshPackageStatus((int) $packageId);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function rebuildCombinedBaselineIfReady($clusterId, $approvedAt, $userId)
    {
        $clusterHeader = $this->getDrmBoqHeader($clusterId, 'CLUSTER');
        $subfeederHeader = $this->getDrmBoqHeader($clusterId, 'SUBFEEDER');
        if (empty($clusterHeader['id_drm_boq'])) {
            return;
        }

        $clusterStatus = strtoupper(trim((string) ($clusterHeader['review_status'] ?? '')));
        if ($clusterStatus !== 'APPROVED') {
            return;
        }

        $clusterItems = $this->getDrmBoqItems($clusterId, 'CLUSTER');
        $subfeederStatus = strtoupper(trim((string) ($subfeederHeader['review_status'] ?? '')));
        $subfeederItems = [];
        $subfeederApproved = !empty($subfeederHeader['id_drm_boq']) && $subfeederStatus === 'APPROVED';
        $subfeederNotRequired = $this->isScopeNotRequiredApproved($clusterId, 'SUBFEEDER');
        if ($this->drmScopeRequirementTablesReady() && !$subfeederApproved && !$subfeederNotRequired) {
            return;
        }

        if ($subfeederApproved) {
            $subfeederItems = $this->getDrmBoqItems($clusterId, 'SUBFEEDER');
        }

        $mergedItems = $this->mergeBoqItemsForBaseline($clusterItems, $subfeederItems);
        if (empty($mergedItems)) {
            return;
        }

        // Replace previous active baseline(s) before creating the new combined baseline.
        $this->replaceActiveBaselines($clusterId, 'CLUSTER');

        $baselinePayload = [
            'id_myrep_cluster' => (int) $clusterId,
            'id_drm_boq' => (int) ($clusterHeader['id_drm_boq'] ?? 0),
            'status_baseline' => 'ACTIVE',
            'approved_at' => $approvedAt,
            'approved_by' => (int) $userId,
        ];
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            // Implementasi reads CLUSTER scope. This can be CLUSTER-only first, then auto-merged
            // with SUBFEEDER when SUBFEEDER BOQ is approved later.
            $baselinePayload['scope_type'] = 'CLUSTER';
        }
        $this->db->insert('tb_myrep_boq_baseline', $baselinePayload);
        $baselineId = (int) $this->db->insert_id();

        foreach ($mergedItems as $item) {
            $this->db->insert('tb_myrep_boq_baseline_item', [
                'id_boq_baseline' => $baselineId,
                'id_boq_item' => (int) $item['id_boq_item'],
                'qty_boq' => (float) ($item['qty_boq'] ?? 0),
                'jumlah_foto' => (int) ($item['jumlah_foto'] ?? 0),
                'remarks_rule' => (string) ($item['remarks_rule'] ?? 'SESUAI ITEM'),
                'target_foto_required' => (int) ($item['target_foto_required'] ?? 0),
                'item_note' => !empty($item['item_note']) ? (string) $item['item_note'] : null,
            ]);
        }
    }

    private function mergeBoqItemsForBaseline(array $clusterItems, array $subfeederItems)
    {
        $sources = [$clusterItems, $subfeederItems];
        $map = [];

        foreach ($sources as $items) {
            foreach ($items as $item) {
                $boqItemId = (int) ($item['id_boq_item'] ?? 0);
                if ($boqItemId <= 0) {
                    continue;
                }

                if (!isset($map[$boqItemId])) {
                    $map[$boqItemId] = [
                        'id_boq_item' => $boqItemId,
                        'qty_boq' => 0,
                        'jumlah_foto' => 0,
                        'remarks_rule' => (string) (!empty($item['remarks_rule']) ? $item['remarks_rule'] : ($item['master_remarks_rule'] ?? 'SESUAI ITEM')),
                        'target_foto_required' => 0,
                        'item_note' => null,
                    ];
                }

                $map[$boqItemId]['qty_boq'] += (float) ($item['qty_boq'] ?? 0);
                $map[$boqItemId]['target_foto_required'] += (int) ($item['target_foto_required'] ?? 0);
                $map[$boqItemId]['jumlah_foto'] = max(
                    (int) $map[$boqItemId]['jumlah_foto'],
                    (int) (!empty($item['jumlah_foto']) ? $item['jumlah_foto'] : ($item['default_photo_qty'] ?? 0))
                );

                $itemNote = trim((string) ($item['item_note'] ?? ''));
                if ($itemNote !== '') {
                    $map[$boqItemId]['item_note'] = $itemNote;
                }
            }
        }

        return array_values(array_filter($map, static function ($item) {
            return (float) ($item['qty_boq'] ?? 0) > 0;
        }));
    }

    private function isScopeNotRequiredApproved($clusterId, $scopeType = 'SUBFEEDER')
    {
        $requirement = $this->getScopeRequirement((int) $clusterId, $scopeType);
        return strtoupper(trim((string) ($requirement['requirement_status'] ?? 'REQUIRED'))) === 'NOT_REQUIRED_APPROVED';
    }

    private function replaceActiveBaselines($clusterId, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return;
        }

        $clusterId = (int) $clusterId;
        $scopeType = $this->normalizeDrmScopeType($scopeType);
        if ($clusterId <= 0) {
            return;
        }

        if ($this->baselineLegacyUniqueIndexExists()) {
            $this->db
                ->where('id_myrep_cluster', $clusterId)
                ->where('status_baseline', 'REPLACED');
            if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
                $this->db->where('scope_type', $scopeType);
            }
            $this->db->delete('tb_myrep_boq_baseline');
        }

        $this->db
            ->where('id_myrep_cluster', $clusterId)
            ->where('status_baseline', 'ACTIVE');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('scope_type', $scopeType);
        }
        $this->db->update('tb_myrep_boq_baseline', [
            'status_baseline' => 'REPLACED',
        ]);
    }

    private function baselineLegacyUniqueIndexExists()
    {
        if (!$this->db->table_exists('tb_myrep_boq_baseline')) {
            return false;
        }

        $query = $this->db->query("SHOW INDEX FROM `tb_myrep_boq_baseline` WHERE `Key_name` = 'uniq_myrep_boq_baseline_cluster_scope' AND `Non_unique` = 0");
        return $query && $query->num_rows() > 0;
    }

    public function rejectDrmBoq($clusterId, $userId, $remark, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return false;
        }

        $header = $this->getDrmBoqHeader($clusterId, $scopeType);
        if (empty($header['id_drm_boq'])) {
            return false;
        }

        $this->db->trans_start();

        $result = $this->db
            ->where('id_drm_boq', (int) $header['id_drm_boq'])
            ->update('tb_myrep_drm_boq', [
                'review_status' => 'REJECTED',
                'rejected_at' => date('Y-m-d H:i:s'),
                'approved_at' => null,
                'approved_by' => (int) $userId,
                'ho_review_remark' => $remark !== '' ? $remark : null,
                'updated_by' => (int) $userId,
            ]);

        if ($result && $this->drmDocumentTablesReady()) {
            $flowType = $this->resolveDrmDocumentFlowType($scopeType);
            $documentFiles = $this->db
                ->select('f.id_doc_file, f.id_doc_package, f.id_doc_item, f.file_name, f.status_file')
                ->from('tb_myrep_flow_doc_package p')
                ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package', 'inner')
                ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'inner')
                ->where('p.id_myrep_cluster', (int) $clusterId)
                ->where('p.flow_type', $flowType)
                ->where('UPPER(i.doc_name)', 'APD BOQ')
                ->get()
                ->result_array();

            $packageIds = [];
            $rejectedAt = date('Y-m-d H:i:s');
            foreach ($documentFiles as $file) {
                $packageIds[(int) $file['id_doc_package']] = true;
                if (strtoupper((string) ($file['status_file'] ?? '')) !== 'UPLOADED') {
                    continue;
                }

                $this->db
                    ->where('id_doc_file', (int) $file['id_doc_file'])
                    ->update('tb_myrep_flow_doc_file', [
                        'status_file' => 'REJECTED',
                        'remark' => $remark !== '' ? $remark : 'Rejected via BOQ review',
                        'approved_by' => (int) $userId,
                        'reviewed_at' => $rejectedAt,
                    ]);

                $this->createFileLog([
                    'id_doc_file' => (int) $file['id_doc_file'],
                    'id_doc_package' => (int) $file['id_doc_package'],
                    'id_doc_item' => (int) $file['id_doc_item'],
                    'action_type' => 'REJECT',
                    'status_after' => 'REJECTED',
                    'file_name' => (string) ($file['file_name'] ?? ''),
                    'remark' => $remark !== '' ? $remark : 'Rejected via BOQ review',
                    'action_by' => (int) $userId,
                ]);
            }

            foreach (array_keys($packageIds) as $packageId) {
                $this->refreshPackageStatus((int) $packageId);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getDrmFileById($fileId)
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('f.*, p.id_myrep_cluster, c.cluster_name, c.city_name, i.doc_name')
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

    public function getDrmFileLogs($fileId)
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('l.*, u.nama_karyawan AS nama_user')
            ->from('tb_myrep_flow_doc_file_log l')
            ->join('tb_master_user_new u', 'u.id = l.action_by', 'left')
            ->where('l.id_doc_file', (int) $fileId)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log', 'DESC')
            ->get()
            ->result_array();
    }

    private function getDocumentSummaryMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->drmDocumentTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select("p.id_myrep_cluster, COUNT(i.id_doc_item) AS total_doc, SUM(CASE WHEN f.id_doc_file IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc, SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'APPROVED' THEN 1 ELSE 0 END) AS approved_doc, SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'REJECTED' THEN 1 ELSE 0 END) AS rejected_doc", false)
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package p', 'p.flow_type = \'DRM\' AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'DRM')
            ->where_in('p.id_myrep_cluster', $clusterIds)
            ->group_by('p.id_myrep_cluster')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_myrep_cluster']] = [
                'total' => (int) $row['total_doc'],
                'uploaded' => (int) $row['uploaded_doc'],
                'approved' => (int) $row['approved_doc'],
                'rejected' => (int) $row['rejected_doc'],
            ];
        }

        return $map;
    }

    private function getMainfeederDocumentSummaryMap($mainfeederIds)
    {
        $mainfeederIds = array_values(array_filter(array_map('intval', (array) $mainfeederIds)));
        if (
            empty($mainfeederIds)
            || !$this->db->table_exists('md_myrep_flow_doc_group')
            || !$this->db->table_exists('md_myrep_flow_doc_item')
            || !$this->db->table_exists('tb_myrep_mainfeeder_doc_package')
            || !$this->db->table_exists('tb_myrep_mainfeeder_doc_file')
        ) {
            return [];
        }

        $rows = $this->db
            ->select("p.id_mainfeeder, COUNT(i.id_doc_item) AS total_doc, SUM(CASE WHEN f.id_doc_file_mainfeeder_flow IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc, SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'APPROVED' THEN 1 ELSE 0 END) AS approved_doc, SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'REJECTED' THEN 1 ELSE 0 END) AS rejected_doc", false)
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_mainfeeder_doc_package p', "p.flow_type = 'DRM' AND p.id_doc_group = g.id_doc_group", 'left', false)
            ->join('tb_myrep_mainfeeder_doc_file f', 'f.id_doc_package_mainfeeder_flow = p.id_doc_package_mainfeeder_flow AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'DRM')
            ->where_in('p.id_mainfeeder', $mainfeederIds)
            ->group_by('p.id_mainfeeder')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_mainfeeder']] = [
                'total' => (int) $row['total_doc'],
                'uploaded' => (int) $row['uploaded_doc'],
                'approved' => (int) $row['approved_doc'],
                'rejected' => (int) $row['rejected_doc'],
            ];
        }

        return $map;
    }

    private function getDrmBoqStatusMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->drmBoqTablesReady()) {
            return [];
        }

        $this->db
            ->select('id_myrep_cluster, review_status')
            ->from('tb_myrep_drm_boq')
            ->where_in('id_myrep_cluster', $clusterIds);
        if ($this->db->field_exists('scope_type', 'tb_myrep_drm_boq')) {
            $this->db->select('scope_type');
        } else {
            $this->db->select("'CLUSTER' AS scope_type", false);
        }

        $rows = $this->db->get()->result_array();
        $map = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $scopeType = strtoupper(trim((string) ($row['scope_type'] ?? 'CLUSTER')));
            if ($clusterId <= 0 || !in_array($scopeType, ['CLUSTER', 'SUBFEEDER'], true)) {
                continue;
            }

            $map[$clusterId][$scopeType] = strtoupper(trim((string) ($row['review_status'] ?? '')));
        }

        return $map;
    }

    private function getScopeRequirementStatusMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->drmScopeRequirementTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('id_myrep_cluster, scope_type, requirement_status')
            ->from('tb_myrep_scope_requirement')
            ->where_in('id_myrep_cluster', $clusterIds)
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $scopeType = strtoupper(trim((string) ($row['scope_type'] ?? '')));
            $status = strtoupper(trim((string) ($row['requirement_status'] ?? '')));
            if ($clusterId <= 0 || $scopeType !== 'SUBFEEDER') {
                continue;
            }

            if ($status === 'NOT_REQUIRED_APPROVED') {
                $map[$clusterId][$scopeType] = 'TIDAK DIBUTUHKAN';
            } elseif ($status === 'NOT_REQUIRED_PENDING') {
                $map[$clusterId][$scopeType] = 'WAITING HO';
            } elseif ($status === 'NOT_REQUIRED_REJECTED') {
                $map[$clusterId][$scopeType] = 'REJECTED';
            }
        }

        return $map;
    }

    private function ensurePackage($clusterId, $flowType, $docGroupId, $userId)
    {
        $existing = $this->db->get_where('tb_myrep_flow_doc_package', ['id_myrep_cluster' => (int) $clusterId, 'flow_type' => (string) $flowType, 'id_doc_group' => (int) $docGroupId])->row_array();
        if ($existing) {
            return (int) $existing['id_doc_package'];
        }

        $this->db->insert('tb_myrep_flow_doc_package', [
            'id_myrep_cluster' => (int) $clusterId,
            'flow_type' => (string) $flowType,
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
        $files = $this->db->select('status_file')->from('tb_myrep_flow_doc_file')->where('id_doc_package', (int) $packageId)->get()->result_array();
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

        $this->db->where('id_doc_package', (int) $packageId)->update('tb_myrep_flow_doc_package', [
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

    private function normalizeDrmScopeType($scopeType)
    {
        return strtoupper(trim((string) $scopeType)) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
    }

    private function resolveDrmDocumentFlowType($scopeType)
    {
        return $this->normalizeDrmScopeType($scopeType) === 'SUBFEEDER' ? 'DRM_SUBFEEDER' : 'DRM';
    }

    private function normalizeDrmPayloadForStorage(array $payload)
    {
        if (array_key_exists('status_drm', $payload)) {
            $payload['status_drm'] = $this->normalizeStoredDrmStatus($payload['status_drm']);
        }

        return $payload;
    }

    private function normalizeStoredDrmStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        $enumSet = $this->getDrmStatusEnumSet();
        if ($status !== '' && (empty($enumSet) || isset($enumSet[$status]))) {
            return $status;
        }

        $displayToStored = [
            'WAITING DOC' => 'DRAFT',
            'WAITING APPROVE' => 'ON REVIEW',
            'WAITING APPROVAL' => 'ON REVIEW',
            'COMPLETE' => 'APPROVED',
            'COMPLETED' => 'APPROVED',
        ];
        $mappedStatus = $displayToStored[$status] ?? $status;
        if ($mappedStatus !== '' && (empty($enumSet) || isset($enumSet[$mappedStatus]))) {
            return $mappedStatus;
        }

        foreach (['DRAFT', 'ON REVIEW', 'SUBMITTED', 'APPROVED', 'DONE', 'REJECTED'] as $fallbackStatus) {
            if (isset($enumSet[$fallbackStatus])) {
                return $fallbackStatus;
            }
        }

        return $status !== '' ? $status : 'DRAFT';
    }

    private function getDrmStatusEnumSet()
    {
        if ($this->drmStatusEnumSet !== null) {
            return $this->drmStatusEnumSet;
        }

        $this->drmStatusEnumSet = [];
        if (!$this->db->table_exists('tb_myrep_drm') || !$this->db->field_exists('status_drm', 'tb_myrep_drm')) {
            return $this->drmStatusEnumSet;
        }

        $row = $this->db->query(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_myrep_drm' AND COLUMN_NAME = 'status_drm' LIMIT 1"
        )->row_array();
        $columnType = (string) ($row['COLUMN_TYPE'] ?? '');
        if (preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $columnType, $matches)) {
            foreach ($matches[1] as $option) {
                $this->drmStatusEnumSet[strtoupper(stripslashes($option))] = true;
            }
        }

        return $this->drmStatusEnumSet;
    }

    private function resolveSafeCurrentStatus($existingStatus, $requestedStatus)
    {
        $existingStatus = strtoupper(trim((string) $existingStatus));
        $requestedStatus = strtoupper(trim((string) $requestedStatus));
        if (in_array($existingStatus, ['RFS', 'ATP', 'DONE'], true)) {
            return $existingStatus;
        }

        return $requestedStatus !== '' ? $requestedStatus : 'RELEASED';
    }

    private function resolveDisplayDrmStatus($row, $summary)
    {
        $hasDrm = (int) ($row['id_drm'] ?? 0) > 0;
        if (!$hasDrm) {
            return 'WAITING INPUT';
        }

        $rawStatus = strtoupper(trim((string) ($row['status_drm'] ?? '')));
        if ($rawStatus === 'REJECTED' || (int) ($summary['rejected'] ?? 0) > 0) {
            return 'REJECTED';
        }

        $total = (int) ($summary['total'] ?? 0);
        $uploaded = (int) ($summary['uploaded'] ?? 0);
        $approved = (int) ($summary['approved'] ?? 0);

        if ($total > 0 && $approved >= $total) {
            return 'COMPLETE';
        }

        if ($total > 0 && $uploaded >= $total) {
            return 'WAITING APPROVE';
        }

        return 'WAITING DOC';
    }
}


