<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MATP_MyRep extends CI_Model
{
    public function supportsAtpFileTable()
    {
        return $this->db->table_exists('tb_rfs_myrep_atp_file');
    }

    public function supportsAtpColumns()
    {
        return $this->db->field_exists('email_atp_date', 'tb_rfs_myrep_cluster')
            && $this->db->field_exists('status_atp', 'tb_rfs_myrep_cluster');
    }

    public function supportsMainfeederAtp()
    {
        return $this->db->table_exists('tb_rfs_myrep_mainfeeder')
            && $this->db->table_exists('tb_myrep_mainfeeder_atp_file');
    }

    public function getStageOptions()
    {
        return [
            'Waiting Email',
            'Waiting Jadwal ATP',
            'Waiting ATP',
            'PROSES ATP',
            'ATP PUNCLIST',
            'ATP DONE',
            'Waiting Status ATP',
        ];
    }

    public function getCityOptions()
    {
        $rows = $this->db
            ->distinct()
            ->select('mt.city_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS')
            ->order_by('mt.city_name', 'ASC')
            ->get()
            ->result_array();

        $cities = $this->extractDistinctUpperValues($rows, 'city_name');
        foreach ($this->getMainfeederRegionalCityRows() as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        $cities = array_values(array_unique($cities));
        sort($cities);
        return $cities;
    }

    public function getRegionalOptions()
    {
        $rows = $this->db
            ->distinct()
            ->select('mt.regional_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS')
            ->order_by('mt.regional_name', 'ASC')
            ->get()
            ->result_array();

        $regionals = $this->extractDistinctUpperValues($rows, 'regional_name');
        foreach ($this->getMainfeederRegionalCityRows() as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regionalName !== '') {
                $regionals[] = $regionalName;
            }
        }

        $regionals = array_values(array_unique($regionals));
        sort($regionals);
        return $regionals;
    }

    public function getClusterById($clusterId)
    {
        $rows = $this->fetchBaseRows((int) $clusterId, '', '');
        if (empty($rows)) {
            return [];
        }

        $row = $rows[0];
        $row['stage_atp'] = $this->deriveStage($row);
        return $row;
    }

    public function getClusterRows($city = '', $regional = '', $stage = '', $projectType = '')
    {
        $projectType = strtoupper(trim((string) $projectType));
        if (!in_array($projectType, ['CLUSTER', 'MAINFEEDER', 'FWA'], true)) {
            $projectType = '';
        }

        $rows = in_array($projectType, ['MAINFEEDER', 'FWA'], true)
            ? $this->fetchMainfeederRows($city, $regional, $projectType)
            : $this->fetchBaseRows(0, $city, $regional);

        if ($projectType === '') {
            $rows = array_merge($rows, $this->fetchMainfeederRows($city, $regional));
        }

        $filteredRows = [];

        foreach ($rows as $row) {
            $row['stage_atp'] = $this->deriveStage($row);

            if ($stage !== '' && $row['stage_atp'] !== $stage) {
                continue;
            }

            $filteredRows[] = $row;
        }

        return $filteredRows;
    }

    public function updateClusterAtpMetadata($clusterId, $emailAtpDate, $statusAtp)
    {
        if (!$this->supportsAtpColumns()) {
            return false;
        }

        return $this->db
            ->where('id_cluster', (int) $clusterId)
            ->update('tb_rfs_myrep_cluster', [
                'email_atp_date' => $emailAtpDate,
                'status_atp' => $statusAtp !== '' ? $statusAtp : null,
            ]);
    }

    public function syncMyrepCurrentStatusFromAtp($rfsClusterId, $statusAtp, $actualAtpDate = null)
    {
        if (!$this->db->table_exists('tb_myrep_cluster')) {
            return false;
        }

        $rfsClusterId = (int) $rfsClusterId;
        if ($rfsClusterId <= 0) {
            return false;
        }

        $statusAtp = strtoupper(trim((string) $statusAtp));
        $hasActualAtp = $this->normalizeDate($actualAtpDate) !== null;
        $nextStatus = '';
        if ($statusAtp === 'DONE' && $hasActualAtp) {
            $nextStatus = 'CHECKLIST DOKUMENT';
        } elseif ($statusAtp === 'PUNCLIST' || $hasActualAtp) {
            $nextStatus = 'ATP';
        }

        if ($nextStatus === '') {
            return false;
        }

        $protectedStatuses = $nextStatus === 'ATP'
            ? ['ATP', 'CHECKLIST', 'CHECKLIST DOKUMENT', 'DONE']
            : ['CHECKLIST', 'CHECKLIST DOKUMENT', 'DONE'];

        $this->db
            ->where('rfs_cluster_id', $rfsClusterId)
            ->group_start()
                ->where('status_current IS NULL', null, false)
                ->or_where_not_in('status_current', $protectedStatuses)
            ->group_end()
            ->update('tb_myrep_cluster', [
                'status_current' => $nextStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->db->affected_rows() > 0;
    }

    public function getAtpFileById($fileId)
    {
        if (!$this->supportsAtpFileTable()) {
            return [];
        }

        return $this->db
            ->from('tb_rfs_myrep_atp_file')
            ->where('id_atp_file', (int) $fileId)
            ->get()
            ->row_array();
    }

    public function hasAtpDocument($clusterId, $docType)
    {
        if (!$this->supportsAtpFileTable()) {
            return false;
        }

        return $this->db
            ->from('tb_rfs_myrep_atp_file')
            ->where('cluster_id', (int) $clusterId)
            ->where('doc_type', strtoupper(trim((string) $docType)))
            ->limit(1)
            ->count_all_results() > 0;
    }

    public function saveAtpFileUpload($data)
    {
        if (!$this->supportsAtpFileTable()) {
            return false;
        }

        return $this->db->insert('tb_rfs_myrep_atp_file', [
            'cluster_id' => (int) $data['cluster_id'],
            'doc_type' => strtoupper(trim((string) $data['doc_type'])),
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'remark' => $data['remark'],
            'uploaded_by' => (int) $data['uploaded_by'],
        ]);
    }

    public function getMainfeederAtpFileById($fileId)
    {
        if (!$this->supportsMainfeederAtp()) {
            return [];
        }

        return $this->db
            ->from('tb_myrep_mainfeeder_atp_file')
            ->where('id_atp_file_mainfeeder', (int) $fileId)
            ->get()
            ->row_array();
    }

    public function deriveStage($row, $today = null)
    {
        $today = $today ?: date('Y-m-d');
        $emailAtpDate = $this->normalizeDate($row['email_atp_date'] ?? null);
        $actualAtpDate = $this->normalizeDate($row['actual_atp_date'] ?? null);
        $statusAtp = strtoupper(trim((string) ($row['status_atp'] ?? '')));

        // Prioritize explicit ATP status even when actual ATP date is still empty.
        if ($statusAtp === 'DONE') {
            return 'ATP DONE';
        }

        if ($statusAtp === 'PUNCLIST') {
            return 'ATP PUNCLIST';
        }

        if ($emailAtpDate === null) {
            return 'Waiting Email';
        }

        if ($actualAtpDate === null) {
            return 'Waiting Jadwal ATP';
        }

        if ($today < $actualAtpDate) {
            return 'Waiting ATP';
        }

        if ($today === $actualAtpDate) {
            return 'PROSES ATP';
        }

        return 'Waiting Status ATP';
    }

    private function fetchBaseRows($clusterId = 0, $city = '', $regional = '')
    {
        $query = $this->db
            ->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                c.email_atp_date,
                c.status_atp,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                latest_claim.rfs_date,
                atp_summary.actual_atp_date
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.id_cluster', 'left')
            ->join('(
                SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                FROM tb_rfs_myrep_doc_package
                GROUP BY cluster_id
            ) atp_summary', 'atp_summary.cluster_id = c.id_cluster', 'left')
            ->where('c.status_rfs', 'FULL RFS');

        if ($clusterId > 0) {
            $query->where('c.id_cluster', $clusterId);
        }

        if ($city !== '') {
            $query->where('UPPER(mt.city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $query->where('UPPER(mt.regional_name)', strtoupper($regional));
        }

        $rows = $query
            ->order_by('mt.regional_name', 'ASC')
            ->order_by('mt.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $fileMap = $this->getLatestAtpFilesByClusterIds(array_column($rows, 'id_cluster'));

        foreach ($rows as &$row) {
            $clusterIdKey = (int) ($row['id_cluster'] ?? 0);
            $punclistFile = $fileMap[$clusterIdKey]['RECORD_PUNCLIST'] ?? [];
            $rectificationFile = $fileMap[$clusterIdKey]['BA_RECTIFICATION'] ?? [];
            $row['project_type'] = 'CLUSTER';
            $row['email_atp_date'] = $this->normalizeDate($row['email_atp_date'] ?? null);
            $row['actual_atp_date'] = $this->normalizeDate($row['actual_atp_date'] ?? null);
            $row['tanggal_rfs'] = $this->normalizeDate($row['rfs_date'] ?? null);
            $row['status_atp'] = strtoupper(trim((string) ($row['status_atp'] ?? '')));
            $row['record_punclist_file_id'] = (int) ($punclistFile['id_atp_file'] ?? 0);
            $row['record_punclist_file_name'] = (string) ($punclistFile['file_name'] ?? '');
            $row['record_punclist_uploaded_at'] = (string) ($punclistFile['uploaded_at'] ?? '');
            $row['ba_rectification_file_id'] = (int) ($rectificationFile['id_atp_file'] ?? 0);
            $row['ba_rectification_file_name'] = (string) ($rectificationFile['file_name'] ?? '');
            $row['ba_rectification_uploaded_at'] = (string) ($rectificationFile['uploaded_at'] ?? '');
        }
        unset($row);

        return $rows;
    }

    private function fetchMainfeederRows($city = '', $regional = '', $projectType = '')
    {
        if (!$this->supportsMainfeederAtp()) {
            return [];
        }

        $projectType = strtoupper(trim((string) $projectType));
        if (!in_array($projectType, ['MAINFEEDER', 'FWA'], true)) {
            $projectType = '';
        }
        $projectTypeSql = $this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')
            ? "COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER')"
            : "'MAINFEEDER'";
        $regionalExpr = $this->mainfeederRegionalFallbackSql('mf', 'mt');

        $query = $this->db
            ->select("
                mf.id_mainfeeder,
                0 AS id_cluster,
                {$projectTypeSql} AS project_type,
                mf.mainfeeder_name AS cluster_name,
                mf.length_meter AS homepass,
                mf.current_status AS status_rfs,
                mf.email_atp_date,
                mf.status_atp,
                COALESCE(mf.city_name, mt.city_name) AS city_name,
                {$regionalExpr} AS regional_name,
                COALESCE(mf.province_name, mt.province_name) AS province_name,
                COALESCE(mf.chief, mt.chief) AS chief,
                COALESCE(mf.rpm, mt.rpm) AS rpm,
                COALESCE(mf.sm, mt.sm) AS sm,
                COALESCE(mf.spv, mt.spv) AS spv,
                NULL AS rfs_date,
                mf.atp_date AS actual_atp_date
            ", false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left');

        if ($projectType !== '') {
            if ($this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
                $query->where($projectTypeSql . ' = ' . $this->db->escape($projectType), null, false);
            } elseif ($projectType === 'FWA') {
                return [];
            }
        }

        if ($city !== '') {
            $query->where($this->collatedUpperInSql('COALESCE(mf.city_name, mt.city_name)', [$city]), null, false);
        }

        if ($regional !== '') {
            $query->where($this->collatedUpperInSql($regionalExpr, [$regional]), null, false);
        }

        $rows = $query
            ->order_by('regional_name', 'ASC')
            ->order_by('city_name', 'ASC')
            ->order_by('mf.mainfeeder_name', 'ASC')
            ->get()
            ->result_array();

        $fileMap = $this->getLatestAtpFilesByMainfeederIds(array_column($rows, 'id_mainfeeder'));

        foreach ($rows as &$row) {
            $mainfeederIdKey = (int) ($row['id_mainfeeder'] ?? 0);
            $punclistFile = $fileMap[$mainfeederIdKey]['RECORD_PUNCLIST'] ?? [];
            $rectificationFile = $fileMap[$mainfeederIdKey]['BA_RECTIFICATION'] ?? [];
            $row['project_type'] = strtoupper(trim((string) ($row['project_type'] ?? 'MAINFEEDER'))) ?: 'MAINFEEDER';
            $row['email_atp_date'] = $this->normalizeDate($row['email_atp_date'] ?? null);
            $row['actual_atp_date'] = $this->normalizeDate($row['actual_atp_date'] ?? null);
            $row['tanggal_rfs'] = null;
            $row['status_atp'] = strtoupper(trim((string) ($row['status_atp'] ?? '')));
            $row['record_punclist_file_id'] = (int) ($punclistFile['id_atp_file_mainfeeder'] ?? 0);
            $row['record_punclist_file_name'] = (string) ($punclistFile['file_name'] ?? '');
            $row['record_punclist_uploaded_at'] = (string) ($punclistFile['uploaded_at'] ?? '');
            $row['ba_rectification_file_id'] = (int) ($rectificationFile['id_atp_file_mainfeeder'] ?? 0);
            $row['ba_rectification_file_name'] = (string) ($rectificationFile['file_name'] ?? '');
            $row['ba_rectification_uploaded_at'] = (string) ($rectificationFile['uploaded_at'] ?? '');
        }
        unset($row);

        return $rows;
    }

    private function getLatestAtpFilesByClusterIds($clusterIds)
    {
        if (empty($clusterIds) || !$this->supportsAtpFileTable()) {
            return [];
        }

        $rows = $this->db
            ->from('tb_rfs_myrep_atp_file')
            ->where_in('cluster_id', $clusterIds)
            ->order_by('id_atp_file', 'DESC')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['cluster_id'] ?? 0);
            $docType = strtoupper(trim((string) ($row['doc_type'] ?? '')));
            if ($clusterId <= 0 || $docType === '') {
                continue;
            }

            if (!isset($result[$clusterId][$docType])) {
                $result[$clusterId][$docType] = $row;
            }
        }

        return $result;
    }

    private function getLatestAtpFilesByMainfeederIds($mainfeederIds)
    {
        if (empty($mainfeederIds) || !$this->supportsMainfeederAtp()) {
            return [];
        }

        $mainfeederIds = array_values(array_filter(array_map('intval', (array) $mainfeederIds)));
        if (empty($mainfeederIds)) {
            return [];
        }

        $rows = $this->db
            ->from('tb_myrep_mainfeeder_atp_file')
            ->where_in('id_mainfeeder', $mainfeederIds)
            ->order_by('id_atp_file_mainfeeder', 'DESC')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $mainfeederId = (int) ($row['id_mainfeeder'] ?? 0);
            $docType = strtoupper(trim((string) ($row['doc_type'] ?? '')));
            if ($mainfeederId <= 0 || $docType === '') {
                continue;
            }

            if (!isset($result[$mainfeederId][$docType])) {
                $result[$mainfeederId][$docType] = $row;
            }
        }

        return $result;
    }

    private function getMainfeederRegionalCityRows()
    {
        if (!$this->supportsMainfeederAtp()) {
            return [];
        }

        return $this->db
            ->distinct()
            ->select($this->mainfeederRegionalFallbackSql('mf', 'mt') . ' AS regional_name, COALESCE(mf.city_name, mt.city_name) AS city_name', false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('COALESCE(mf.city_name, mt.city_name) IS NOT NULL', null, false)
            ->where('CHAR_LENGTH(TRIM(COALESCE(mf.city_name, mt.city_name))) > 0', null, false)
            ->order_by('regional_name', 'ASC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();
    }

    private function collatedUpperInSql($expression, array $values)
    {
        $normalizedValues = array_values(array_unique(array_filter(array_map(static function ($value) {
            return strtoupper(trim((string) $value));
        }, $values))));

        if (empty($normalizedValues)) {
            return '1 = 0';
        }

        $escapedValues = array_map(function ($value) {
            return 'CONVERT(' . $this->db->escape($value) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci';
        }, $normalizedValues);

        return 'CONVERT(UPPER(' . $expression . ') USING utf8mb4) COLLATE utf8mb4_unicode_ci IN (' . implode(',', $escapedValues) . ')';
    }

    private function mainfeederRegionalFallbackSql($mainfeederAlias = 'mf', $targetAlias = 'mt')
    {
        $mainfeederAlias = preg_replace('/[^A-Za-z0-9_]/', '', (string) $mainfeederAlias) ?: 'mf';
        $targetAlias = preg_replace('/[^A-Za-z0-9_]/', '', (string) $targetAlias) ?: 'mt';
        if (
            !$this->db->table_exists('tb_myrep_pic_mapping_city')
            || !$this->db->field_exists('regional_name', 'tb_myrep_pic_mapping_city')
        ) {
            return "COALESCE(NULLIF({$mainfeederAlias}.regional_name, ''), {$targetAlias}.regional_name)";
        }

        return "COALESCE(NULLIF({$mainfeederAlias}.regional_name, ''), (
            SELECT cm_fallback.regional_name
            FROM tb_myrep_pic_mapping_city cm_fallback
            WHERE CONVERT(UPPER(cm_fallback.city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(UPPER({$mainfeederAlias}.city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              AND cm_fallback.regional_name IS NOT NULL
              AND TRIM(cm_fallback.regional_name) != ''
            ORDER BY cm_fallback.id DESC
            LIMIT 1
        ), {$targetAlias}.regional_name)";
    }

    private function normalizeDate($date)
    {
        $date = trim((string) $date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }

        return $date;
    }

    private function extractDistinctUpperValues($rows, $column)
    {
        $values = [];
        foreach ($rows as $row) {
            $value = strtoupper(trim((string) ($row[$column] ?? '')));
            if ($value !== '') {
                $values[$value] = $value;
            }
        }

        return array_values($values);
    }
}
