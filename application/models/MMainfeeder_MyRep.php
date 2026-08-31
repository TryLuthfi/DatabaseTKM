<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMainfeeder_MyRep extends CI_Model
{
    private $defaultTerminPercents = [20, 25, 15, 30, 10];

    public function standaloneProjectTypes()
    {
        return ['MAINFEEDER', 'FWA'];
    }

    public function normalizeStandaloneProjectType($projectType)
    {
        $projectType = strtoupper(trim((string) $projectType));
        return in_array($projectType, $this->standaloneProjectTypes(), true) ? $projectType : 'MAINFEEDER';
    }

    private function standaloneProjectTypeSql($alias = '')
    {
        $prefix = $alias !== '' ? rtrim((string) $alias, '.') . '.' : '';
        if ($this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
            return "COALESCE(NULLIF(UPPER(TRIM({$prefix}project_type)), ''), 'MAINFEEDER')";
        }
        return "'MAINFEEDER'";
    }

    public function getProjectTypeById($mainfeederId)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0 || !$this->db->table_exists('tb_rfs_myrep_mainfeeder')) {
            return 'MAINFEEDER';
        }
        if (!$this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
            return 'MAINFEEDER';
        }

        $row = $this->db
            ->select('project_type')
            ->from('tb_rfs_myrep_mainfeeder')
            ->where('id_mainfeeder', $mainfeederId)
            ->limit(1)
            ->get()
            ->row_array();

        return $this->normalizeStandaloneProjectType($row['project_type'] ?? 'MAINFEEDER');
    }

    public function tablesReady()
    {
        return $this->db->table_exists('tb_rfs_myrep_mainfeeder')
            && $this->db->field_exists('cluster_code', 'tb_rfs_myrep_mainfeeder')
            && $this->db->table_exists('tb_myrep_mainfeeder_drm')
            && $this->db->table_exists('tb_myrep_mainfeeder_drm_boq')
            && $this->db->table_exists('tb_myrep_mainfeeder_boq_baseline')
            && $this->db->table_exists('tb_myrep_mainfeeder_impl_daily_activity')
            && $this->db->table_exists('tb_myrep_mainfeeder_atp_file')
            && $this->db->field_exists('id_mainfeeder', 'tb_myrep_po_header');
    }

    public function ensurePoHeaderNyRefColumn()
    {
        if (!$this->db->table_exists('tb_myrep_po_header')) {
            return false;
        }

        if (!$this->db->field_exists('po_monitor_ny_ref', 'tb_myrep_po_header')) {
            $afterColumn = $this->db->field_exists('remark_po', 'tb_myrep_po_header') ? ' AFTER `remark_po`' : '';
            $this->db->query('ALTER TABLE `tb_myrep_po_header` ADD COLUMN `po_monitor_ny_ref` VARCHAR(50) NULL' . $afterColumn);
        }

        return $this->db->field_exists('po_monitor_ny_ref', 'tb_myrep_po_header');
    }

    public function getStatusOptions()
    {
        return ['DRM', 'IMPLEMENTASI', 'ATP', 'CHECKLIST', 'DONE'];
    }

    public function getCityOptions()
    {
        if (!$this->db->table_exists('tb_rfs_myrep_mainfeeder')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('city_name')
            ->from('tb_rfs_myrep_mainfeeder')
            ->where('city_name IS NOT NULL', null, false)
            ->where('CHAR_LENGTH(TRIM(city_name)) > 0', null, false)
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();

        return $this->extractUpperValues($rows, 'city_name');
    }

    public function getRows($city = '', $status = '', $projectType = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $projectType = strtoupper(trim((string) $projectType));
        if ($projectType !== '' && !in_array($projectType, $this->standaloneProjectTypes(), true)) {
            $projectType = '';
        }
        $projectTypeSelect = $this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')
            ? "COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER')"
            : "'MAINFEEDER'";
        $poJoinTypeSql = $this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')
            ? "((COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER') = 'FWA' AND UPPER(COALESCE(po.po_type, '')) = 'FWA') OR (COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER') <> 'FWA' AND UPPER(COALESCE(po.po_type, '')) = 'MAINFEEDER'))"
            : "UPPER(COALESCE(po.po_type, '')) = 'MAINFEEDER'";
        $regionalFallbackSql = $this->cityMappingFallbackSql('regional_name');
        $provinceFallbackSql = $this->cityMappingFallbackSql('province_name');

        $query = $this->db
            ->select("
                mf.*,
                {$projectTypeSelect} AS project_type,
                COALESCE(NULLIF(mf.city_name, ''), mt.city_name) AS city_name,
                COALESCE(NULLIF(mf.regional_name, ''), {$regionalFallbackSql}, mt.regional_name) AS regional_name,
                COALESCE(NULLIF(mf.province_name, ''), {$provinceFallbackSql}, mt.province_name) AS province_name,
                COALESCE(mf.team_name, mt.team_name) AS team_name,
                COALESCE(mf.chief, mt.chief) AS chief,
                COALESCE(mf.rpm, mt.rpm) AS rpm,
                COALESCE(mf.sm, mt.sm) AS sm,
                COALESCE(mf.spv, mt.spv) AS spv,
                drm.drm_date,
                drm.status_drm,
                boq.review_status AS boq_review_status,
                COUNT(DISTINCT po.id_po_header) AS po_count,
                COALESCE(SUM(po.po_value), 0) AS po_total_value
            ", false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->join('tb_myrep_mainfeeder_drm drm', 'drm.id_mainfeeder = mf.id_mainfeeder', 'left')
            ->join('tb_myrep_mainfeeder_drm_boq boq', 'boq.id_mainfeeder = mf.id_mainfeeder', 'left')
            ->join('tb_myrep_po_header po', "po.id_mainfeeder = mf.id_mainfeeder AND {$poJoinTypeSql}", 'left', false)
            ->group_by('mf.id_mainfeeder');

        if ($city !== '') {
            $query->where(
                'CONVERT(UPPER(COALESCE(NULLIF(mf.city_name, \'\'), mt.city_name)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(' . $this->db->escape(strtoupper($city)) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci',
                null,
                false
            );
        }
        if ($status !== '') {
            $query->where('UPPER(mf.current_status)', strtoupper($status));
        }
        if ($projectType !== '' && $this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
            $query->where("COALESCE(NULLIF(UPPER(TRIM(mf.project_type)), ''), 'MAINFEEDER') = " . $this->db->escape($projectType), null, false);
        } elseif ($projectType === 'FWA') {
            return [];
        }

        return $query
            ->order_by('mf.updated_at', 'DESC')
            ->order_by('mf.mainfeeder_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getById($mainfeederId)
    {
        if (!$this->tablesReady()) {
            return [];
        }
        $regionalFallbackSql = $this->cityMappingFallbackSql('regional_name');
        $provinceFallbackSql = $this->cityMappingFallbackSql('province_name');

        return $this->db
            ->select("
                mf.*,
                COALESCE(NULLIF(mf.city_name, ''), mt.city_name) AS city_name,
                COALESCE(NULLIF(mf.regional_name, ''), {$regionalFallbackSql}, mt.regional_name) AS regional_name,
                COALESCE(NULLIF(mf.province_name, ''), {$provinceFallbackSql}, mt.province_name) AS province_name,
                COALESCE(mf.team_name, mt.team_name) AS team_name,
                COALESCE(mf.chief, mt.chief) AS chief,
                COALESCE(mf.rpm, mt.rpm) AS rpm,
                COALESCE(mf.sm, mt.sm) AS sm,
                COALESCE(mf.spv, mt.spv) AS spv,
                drm.id_mainfeeder_drm,
                drm.drm_date,
                drm.nama_olt,
                drm.status_drm,
                drm.remark_drm,
                boq.id_mainfeeder_drm_boq,
                boq.review_status AS boq_review_status,
                boq.approved_at AS boq_approved_at,
                boq.ho_review_remark
            ", false)
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->join('tb_myrep_mainfeeder_drm drm', 'drm.id_mainfeeder = mf.id_mainfeeder', 'left')
            ->join('tb_myrep_mainfeeder_drm_boq boq', 'boq.id_mainfeeder = mf.id_mainfeeder', 'left')
            ->where('mf.id_mainfeeder', (int) $mainfeederId)
            ->get()
            ->row_array();
    }

    public function saveMainfeeder(array $payload)
    {
        if (!$this->tablesReady()) {
            return 0;
        }

        $clusterCode = strtoupper(trim((string) ($payload['cluster_code'] ?? '')));
        $name = trim((string) ($payload['mainfeeder_name'] ?? ''));
        $projectType = $this->normalizeStandaloneProjectType($payload['project_type'] ?? 'MAINFEEDER');
        if ($clusterCode === '' || $name === '') {
            return 0;
        }
        $clusterCodeGenerated = !empty($payload['_cluster_code_generated']);

        $existing = [];
        if ($clusterCode !== '') {
            $existing = $this->db
                ->from('tb_rfs_myrep_mainfeeder')
                ->where('UPPER(cluster_code)', $clusterCode)
                ->get()
                ->row_array();
        }
        if (empty($existing)) {
            $existing = $this->db
                ->from('tb_rfs_myrep_mainfeeder')
                ->where(
                    'CONVERT(UPPER(city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(' . $this->db->escape(strtoupper(trim((string) ($payload['city_name'] ?? '')))) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci',
                    null,
                    false
                )
                ->where('UPPER(mainfeeder_name)', strtoupper($name))
                ->get()
                ->row_array();
        }

        $rowClusterCode = $clusterCode;
        if (!empty($existing['id_mainfeeder']) && $clusterCodeGenerated && trim((string) ($existing['cluster_code'] ?? '')) !== '') {
            $rowClusterCode = strtoupper(trim((string) $existing['cluster_code']));
        }

        $cityName = strtoupper(trim((string) ($payload['city_name'] ?? '')));
        $regionalName = strtoupper(trim((string) ($payload['regional_name'] ?? '')));
        $provinceName = strtoupper(trim((string) ($payload['province_name'] ?? '')));
        if ($cityName !== '' && ($regionalName === '' || $provinceName === '')) {
            $locationFallback = $this->getLocationFallbackByCity($cityName);
            if ($regionalName === '') {
                $regionalName = strtoupper(trim((string) ($locationFallback['regional_name'] ?? '')));
            }
            if ($provinceName === '') {
                $provinceName = strtoupper(trim((string) ($locationFallback['province_name'] ?? '')));
            }
        }

        $row = [
            'id_target' => !empty($payload['id_target']) ? (int) $payload['id_target'] : null,
            'cluster_code' => $rowClusterCode,
            'mainfeeder_name' => $name,
            'current_status' => $this->normalizeStatus($payload['current_status'] ?? 'DRM'),
            'year_num' => !empty($payload['year_num']) ? (int) $payload['year_num'] : null,
            'month_num' => !empty($payload['month_num']) ? (int) $payload['month_num'] : null,
            'regional_name' => $regionalName,
            'province_name' => $provinceName,
            'city_name' => $cityName,
            'team_name' => trim((string) ($payload['team_name'] ?? '')),
            'chief' => trim((string) ($payload['chief'] ?? '')),
            'rpm' => trim((string) ($payload['rpm'] ?? '')),
            'sm' => trim((string) ($payload['sm'] ?? '')),
            'spv' => trim((string) ($payload['spv'] ?? '')),
            'vendor_name' => trim((string) ($payload['vendor_name'] ?? '')),
            'length_meter' => (float) ($payload['length_meter'] ?? 0),
            'atp_date' => !empty($payload['atp_date']) ? (string) $payload['atp_date'] : null,
            'email_atp_date' => !empty($payload['email_atp_date']) ? (string) $payload['email_atp_date'] : null,
            'status_atp' => !empty($payload['status_atp']) ? (string) $payload['status_atp'] : null,
            'remark_mainfeeder' => trim((string) ($payload['remark_mainfeeder'] ?? '')),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];
        if ($this->db->field_exists('project_type', 'tb_rfs_myrep_mainfeeder')) {
            $row['project_type'] = $projectType;
        }

        if (!empty($existing['id_mainfeeder'])) {
            $this->db->where('id_mainfeeder', (int) $existing['id_mainfeeder'])->update('tb_rfs_myrep_mainfeeder', $row);
            return (int) $existing['id_mainfeeder'];
        }

        $row['created_by'] = (int) ($payload['created_by'] ?? 0);
        $this->db->insert('tb_rfs_myrep_mainfeeder', $row);
        return (int) $this->db->insert_id();
    }

    private function cityMappingFallbackSql($columnName)
    {
        $columnName = (string) $columnName;
        if (
            !in_array($columnName, ['regional_name', 'province_name'], true)
            || !$this->db->table_exists('tb_myrep_pic_mapping_city')
            || !$this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')
        ) {
            return 'NULL';
        }

        return "(
            SELECT cm_fallback.{$columnName}
            FROM tb_myrep_pic_mapping_city cm_fallback
            WHERE CONVERT(UPPER(cm_fallback.city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(UPPER(mf.city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              AND cm_fallback.{$columnName} IS NOT NULL
              AND TRIM(cm_fallback.{$columnName}) != ''
            ORDER BY cm_fallback.id DESC
            LIMIT 1
        )";
    }

    private function getLocationFallbackByCity($cityName)
    {
        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '') {
            return [];
        }

        if ($this->db->table_exists('tb_myrep_pic_mapping_city')) {
            $row = $this->db
                ->select('regional_name, province_name')
                ->from('tb_myrep_pic_mapping_city')
                ->where(
                    'CONVERT(UPPER(city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(' . $this->db->escape($cityName) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci',
                    null,
                    false
                )
                ->where('regional_name IS NOT NULL', null, false)
                ->where("TRIM(regional_name) !=", '')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($row)) {
                return $row;
            }
        }

        if (!$this->db->table_exists('tb_rfs_myrep_monthly_target')) {
            return [];
        }

        return $this->db
            ->select('regional_name, province_name')
            ->from('tb_rfs_myrep_monthly_target')
            ->where(
                'CONVERT(UPPER(city_name) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(' . $this->db->escape($cityName) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci',
                null,
                false
            )
            ->where('regional_name IS NOT NULL', null, false)
            ->where("TRIM(regional_name) !=", '')
            ->order_by('year_num', 'DESC')
            ->order_by('month_num', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function upsertPoHeader($mainfeederId, array $payload)
    {
        $mainfeederId = (int) $mainfeederId;
        $poNumber = trim((string) ($payload['po_number'] ?? ''));
        if ($mainfeederId <= 0 || $poNumber === '' || !$this->db->field_exists('id_mainfeeder', 'tb_myrep_po_header')) {
            return 0;
        }
        $poType = $this->normalizeStandaloneProjectType($payload['po_type'] ?? $payload['project_type'] ?? $this->getProjectTypeById($mainfeederId));

        $existing = $this->db
            ->from('tb_myrep_po_header')
            ->where('id_mainfeeder', $mainfeederId)
            ->where('UPPER(po_type)', $poType)
            ->where('UPPER(po_number)', strtoupper($poNumber))
            ->get()
            ->row_array();

        $poValue = (float) ($payload['po_value'] ?? 0);
        $header = [
            'id_myrep_cluster' => null,
            'project_type' => $poType,
            'id_mainfeeder' => $mainfeederId,
            'parent_po_header_id' => !empty($payload['parent_po_header_id']) ? (int) $payload['parent_po_header_id'] : null,
            'po_type' => $poType,
            'po_category' => (string) ($payload['po_category'] ?? 'INITIAL'),
            'po_number' => $poNumber,
            'po_date' => $payload['po_date'] ?? null,
            'po_value' => $poValue,
            'status_po' => (string) ($payload['status_po'] ?? 'ISSUED'),
            'po_version_label' => !empty($payload['po_version_label']) ? (string) $payload['po_version_label'] : null,
            'remark_po' => !empty($payload['remark_po']) ? (string) $payload['remark_po'] : null,
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];
        if ($this->db->field_exists('on_target', 'tb_myrep_po_header')) {
            $header['on_target'] = (int) ($payload['on_target'] ?? 1);
        }
        if ($this->ensurePoHeaderNyRefColumn()) {
            $nyRef = strtoupper(trim((string) ($payload['po_monitor_ny_ref'] ?? '')));
            $header['po_monitor_ny_ref'] = $nyRef !== '' ? $nyRef : null;
        }

        if (!empty($existing['id_po_header'])) {
            $poId = (int) $existing['id_po_header'];
            $this->db->where('id_po_header', $poId)->update('tb_myrep_po_header', $header);
            $this->ensurePoTerminRows($poId, $poValue, (int) ($payload['updated_by'] ?? 0));
            return $poId;
        }

        $header['created_by'] = (int) ($payload['created_by'] ?? 0);
        $this->db->insert('tb_myrep_po_header', $header);
        $poId = (int) $this->db->insert_id();
        $this->ensurePoTerminRows($poId, $poValue, (int) ($payload['created_by'] ?? 0));
        return $poId;
    }

    public function ensurePoTerminRows($poId, $poValue, $userId)
    {
        $poId = (int) $poId;
        if ($poId <= 0) {
            return 0;
        }

        $changed = 0;
        foreach ($this->defaultTerminPercents as $index => $percent) {
            $terminNo = $index + 1;
            $existing = $this->db->get_where('tb_myrep_po_termin', [
                'id_po_header' => $poId,
                'termin_no' => $terminNo,
            ])->row_array();
            $payload = [
                'termin_percent' => $percent,
                'termin_value' => round(((float) $poValue * $percent) / 100, 2),
                'updated_by' => (int) $userId,
            ];
            if (!empty($existing['id_po_termin'])) {
                $this->db->where('id_po_termin', (int) $existing['id_po_termin'])->update('tb_myrep_po_termin', $payload);
            } else {
                $payload['id_po_header'] = $poId;
                $payload['termin_no'] = $terminNo;
                $payload['status_termin'] = 'NOT READY';
                $payload['created_by'] = (int) $userId;
                $this->db->insert('tb_myrep_po_termin', $payload);
            }
            $changed++;
        }
        return $changed;
    }

    public function updateTerminByPoAndNo($poId, $terminNo, array $payload)
    {
        $termin = $this->db->get_where('tb_myrep_po_termin', [
            'id_po_header' => (int) $poId,
            'termin_no' => (int) $terminNo,
        ])->row_array();
        if (empty($termin['id_po_termin'])) {
            return false;
        }

        $update = [
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];
        foreach (['termin_value', 'status_termin', 'invoice_date', 'bast_date', 'payment_date', 'remark_termin'] as $field) {
            if (array_key_exists($field, $payload)) {
                $update[$field] = $payload[$field];
            }
        }
        if ($this->db->field_exists('invoice_value', 'tb_myrep_po_termin') && array_key_exists('invoice_value', $payload)) {
            $update['invoice_value'] = $payload['invoice_value'];
        }
        if ($this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin') && array_key_exists('sertifikat_invoice_date', $payload)) {
            $update['sertifikat_invoice_date'] = $payload['sertifikat_invoice_date'];
        }
        if (count($update) <= 1) {
            return false;
        }

        return $this->db->where('id_po_termin', (int) $termin['id_po_termin'])->update('tb_myrep_po_termin', $update);
    }

    public function saveDrmMetadata($mainfeederId, array $payload)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0) {
            return false;
        }

        $row = [
            'drm_date' => !empty($payload['drm_date']) ? (string) $payload['drm_date'] : null,
            'nama_olt' => trim((string) ($payload['nama_olt'] ?? '')),
            'status_drm' => strtoupper(trim((string) ($payload['status_drm'] ?? 'SUBMITTED'))),
            'remark_drm' => trim((string) ($payload['remark_drm'] ?? '')),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];
        if (!in_array($row['status_drm'], ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'], true)) {
            $row['status_drm'] = 'SUBMITTED';
        }

        $existing = $this->getDrm($mainfeederId);
        if (!empty($existing['id_mainfeeder_drm'])) {
            return $this->db->where('id_mainfeeder_drm', (int) $existing['id_mainfeeder_drm'])->update('tb_myrep_mainfeeder_drm', $row);
        }

        $row['id_mainfeeder'] = $mainfeederId;
        $row['created_by'] = (int) ($payload['created_by'] ?? 0);
        return $this->db->insert('tb_myrep_mainfeeder_drm', $row);
    }

    public function getDrm($mainfeederId)
    {
        return $this->db
            ->from('tb_myrep_mainfeeder_drm')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->get()
            ->row_array();
    }

    public function getDrmDocumentRows($mainfeederId)
    {
        if (!$this->db->table_exists('md_myrep_flow_doc_group')) {
            return [];
        }

        $this->ensureDrmPackages((int) $mainfeederId, (int) $this->session->userdata('id_user'));

        return $this->db
            ->select('g.group_label, i.id_doc_item, i.doc_name, i.doc_requirement_note, i.verification_team, p.id_doc_package_mainfeeder_flow, p.status_package, f.id_doc_file_mainfeeder_flow, f.file_name, f.file_path, f.status_file, f.is_document_not_required, f.remark, f.uploaded_at, f.reviewed_at, f.approved_at')
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_mainfeeder_doc_package p', 'p.id_mainfeeder = ' . (int) $mainfeederId . ' AND p.flow_type = "DRM" AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_mainfeeder_doc_file f', 'f.id_doc_package_mainfeeder_flow = p.id_doc_package_mainfeeder_flow AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'DRM')
            ->where('g.is_active', 1)
            ->order_by('g.sort_no', 'ASC')
            ->order_by('i.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function saveDrmFileUpload($mainfeederId, $docItemId, array $data)
    {
        $context = $this->getDrmDocumentContext($mainfeederId, $docItemId);
        if (empty($context['id_doc_group'])) {
            return 0;
        }

        $packageId = $this->ensureDrmPackage((int) $mainfeederId, (int) $context['id_doc_group'], (int) ($data['uploaded_by'] ?? 0));
        $existing = $this->db->get_where('tb_myrep_mainfeeder_doc_file', [
            'id_doc_package_mainfeeder_flow' => $packageId,
            'id_doc_item' => (int) $docItemId,
        ])->row_array();

        $payload = [
            'file_name' => (string) ($data['file_name'] ?? ''),
            'file_path' => (string) ($data['file_path'] ?? ''),
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => (string) ($data['status_file'] ?? 'UPLOADED'),
            'remark' => trim((string) ($data['remark'] ?? '')),
            'uploaded_by' => (int) ($data['uploaded_by'] ?? 0),
            'uploaded_at' => date('Y-m-d H:i:s'),
            'approved_by' => null,
            'reviewed_at' => null,
            'approved_at' => null,
        ];

        if ($existing) {
            $this->db->where('id_doc_file_mainfeeder_flow', (int) $existing['id_doc_file_mainfeeder_flow'])->update('tb_myrep_mainfeeder_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file_mainfeeder_flow'];
            $action = 'REUPLOAD';
        } else {
            $payload['id_doc_package_mainfeeder_flow'] = $packageId;
            $payload['id_doc_item'] = (int) $docItemId;
            $this->db->insert('tb_myrep_mainfeeder_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $action = 'UPLOAD';
        }

        $this->createDocLog($fileId, $packageId, (int) $docItemId, $action, $payload['status_file'], $payload['file_name'], $payload['remark'], (int) $payload['uploaded_by']);
        $this->refreshDocPackageStatus($packageId);
        return $fileId;
    }

    public function updateDrmFileStatus($fileId, array $data)
    {
        $file = $this->db
            ->from('tb_myrep_mainfeeder_doc_file')
            ->where('id_doc_file_mainfeeder_flow', (int) $fileId)
            ->get()
            ->row_array();
        if (empty($file)) {
            return false;
        }

        $status = strtoupper(trim((string) ($data['status_file'] ?? '')));
        if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
            return false;
        }

        $payload = [
            'status_file' => $status,
            'remark' => trim((string) ($data['remark'] ?? '')),
            'approved_by' => (int) ($data['approved_by'] ?? 0),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => $status === 'APPROVED' ? date('Y-m-d H:i:s') : null,
        ];
        $ok = $this->db->where('id_doc_file_mainfeeder_flow', (int) $fileId)->update('tb_myrep_mainfeeder_doc_file', $payload);
        $this->createDocLog((int) $fileId, (int) $file['id_doc_package_mainfeeder_flow'], (int) $file['id_doc_item'], $status === 'APPROVED' ? 'APPROVE' : 'REJECT', $status, (string) ($file['file_name'] ?? ''), $payload['remark'], $payload['approved_by']);
        $this->refreshDocPackageStatus((int) $file['id_doc_package_mainfeeder_flow']);
        return $ok;
    }

    public function getDrmDocumentContext($mainfeederId, $docItemId)
    {
        return $this->db
            ->select('g.id_doc_group, g.group_label, i.id_doc_item, i.doc_name, f.id_doc_file_mainfeeder_flow, f.file_path')
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_mainfeeder_doc_package p', 'p.id_mainfeeder = ' . (int) $mainfeederId . ' AND p.flow_type = "DRM" AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_mainfeeder_doc_file f', 'f.id_doc_package_mainfeeder_flow = p.id_doc_package_mainfeeder_flow AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'DRM')
            ->where('i.id_doc_item', (int) $docItemId)
            ->get()
            ->row_array();
    }

    public function saveDrmBoqDraft($mainfeederId, $sourceDocFileId, array $items, $userId, $submitToHo = true)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0 || empty($items)) {
            return false;
        }

        $drm = $this->getDrm($mainfeederId);
        $existing = $this->getDrmBoqHeader($mainfeederId);
        if (strtoupper((string) ($existing['review_status'] ?? '')) === 'APPROVED') {
            return false;
        }

        $this->db->trans_start();
        $header = [
            'id_mainfeeder_drm' => !empty($drm['id_mainfeeder_drm']) ? (int) $drm['id_mainfeeder_drm'] : null,
            'source_doc_file_id' => $sourceDocFileId > 0 ? (int) $sourceDocFileId : null,
            'review_status' => $submitToHo ? 'WAITING HO' : 'DRAFT',
            'submitted_at' => date('Y-m-d H:i:s'),
            'updated_by' => (int) $userId,
            'approved_at' => null,
            'rejected_at' => null,
            'approved_by' => null,
            'ho_review_remark' => null,
        ];

        if (!empty($existing['id_mainfeeder_drm_boq'])) {
            $this->db->where('id_mainfeeder_drm_boq', (int) $existing['id_mainfeeder_drm_boq'])->update('tb_myrep_mainfeeder_drm_boq', $header);
            $boqId = (int) $existing['id_mainfeeder_drm_boq'];
        } else {
            $header['id_mainfeeder'] = $mainfeederId;
            $header['created_by'] = (int) $userId;
            $this->db->insert('tb_myrep_mainfeeder_drm_boq', $header);
            $boqId = (int) $this->db->insert_id();
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
            $existingItem = $this->db->get_where('tb_myrep_mainfeeder_drm_boq_item', [
                'id_mainfeeder_drm_boq' => $boqId,
                'id_boq_item' => $boqItemId,
            ])->row_array();
            if ($existingItem) {
                $this->db->where('id_mainfeeder_drm_boq_item', (int) $existingItem['id_mainfeeder_drm_boq_item'])->update('tb_myrep_mainfeeder_drm_boq_item', $payload);
            } else {
                $payload['id_mainfeeder_drm_boq'] = $boqId;
                $payload['id_boq_item'] = $boqItemId;
                $this->db->insert('tb_myrep_mainfeeder_drm_boq_item', $payload);
            }
        }

        $this->db->where('id_mainfeeder', $mainfeederId)->update('tb_rfs_myrep_mainfeeder', ['current_status' => 'DRM', 'updated_by' => (int) $userId]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function approveDrmBoq($mainfeederId, $userId, $remark = '')
    {
        $header = $this->getDrmBoqHeader($mainfeederId);
        $items = $this->getDrmBoqItems($mainfeederId);
        if (empty($header['id_mainfeeder_drm_boq']) || empty($items)) {
            return false;
        }

        $approvedAt = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->where('id_mainfeeder_drm_boq', (int) $header['id_mainfeeder_drm_boq'])->update('tb_myrep_mainfeeder_drm_boq', [
            'review_status' => 'APPROVED',
            'approved_at' => $approvedAt,
            'rejected_at' => null,
            'approved_by' => (int) $userId,
            'ho_review_remark' => trim((string) $remark) !== '' ? trim((string) $remark) : null,
            'updated_by' => (int) $userId,
        ]);
        $this->archiveMainfeederBaselines((int) $mainfeederId);
        $this->db->insert('tb_myrep_mainfeeder_boq_baseline', [
            'id_mainfeeder' => (int) $mainfeederId,
            'id_mainfeeder_drm_boq' => (int) $header['id_mainfeeder_drm_boq'],
            'status_baseline' => 'ACTIVE',
            'approved_at' => $approvedAt,
            'approved_by' => (int) $userId,
        ]);
        $baselineId = (int) $this->db->insert_id();
        foreach ($items as $item) {
            $this->db->insert('tb_myrep_mainfeeder_boq_baseline_item', [
                'id_mainfeeder_boq_baseline' => $baselineId,
                'id_boq_item' => (int) $item['id_boq_item'],
                'qty_boq' => (float) ($item['qty_boq'] ?? 0),
                'jumlah_foto' => (int) ($item['jumlah_foto'] ?? 0),
                'remarks_rule' => (string) ($item['remarks_rule'] ?? 'SESUAI ITEM'),
                'target_foto_required' => (int) ($item['target_foto_required'] ?? 0),
                'item_note' => !empty($item['item_note']) ? (string) $item['item_note'] : null,
            ]);
        }
        $this->db->where('id_mainfeeder', (int) $mainfeederId)->update('tb_myrep_mainfeeder_drm', ['status_drm' => 'DONE', 'updated_by' => (int) $userId]);
        $this->db->where('id_mainfeeder', (int) $mainfeederId)->update('tb_rfs_myrep_mainfeeder', ['current_status' => 'IMPLEMENTASI', 'updated_by' => (int) $userId]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function rejectDrmBoq($mainfeederId, $userId, $remark)
    {
        $header = $this->getDrmBoqHeader($mainfeederId);
        if (empty($header['id_mainfeeder_drm_boq'])) {
            return false;
        }
        return $this->db->where('id_mainfeeder_drm_boq', (int) $header['id_mainfeeder_drm_boq'])->update('tb_myrep_mainfeeder_drm_boq', [
            'review_status' => 'REJECTED',
            'rejected_at' => date('Y-m-d H:i:s'),
            'approved_by' => (int) $userId,
            'ho_review_remark' => trim((string) $remark),
            'updated_by' => (int) $userId,
        ]);
    }

    public function getDrmBoqHeader($mainfeederId)
    {
        return $this->db->from('tb_myrep_mainfeeder_drm_boq')->where('id_mainfeeder', (int) $mainfeederId)->get()->row_array();
    }

    public function getDrmBoqItems($mainfeederId)
    {
        $header = $this->getDrmBoqHeader($mainfeederId);
        if (empty($header['id_mainfeeder_drm_boq'])) {
            return [];
        }
        return $this->db
            ->select('i.*, m.item_name, m.excel_item_name, m.item_type, m.sort_no')
            ->from('tb_myrep_mainfeeder_drm_boq_item i')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = i.id_boq_item', 'left')
            ->where('i.id_mainfeeder_drm_boq', (int) $header['id_mainfeeder_drm_boq'])
            ->order_by('m.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBaselineCompareRows($mainfeederId)
    {
        $baseline = $this->getActiveBaseline($mainfeederId);
        if (empty($baseline['id_mainfeeder_boq_baseline'])) {
            return [];
        }

        $rows = $this->db
            ->select('bi.id_mainfeeder_boq_baseline_item, bi.id_mainfeeder_boq_baseline, bi.id_boq_item, bi.qty_boq, bi.jumlah_foto, bi.target_foto_required, bi.remarks_rule, m.item_name, m.excel_item_name, m.item_type, m.sort_no')
            ->from('tb_myrep_mainfeeder_boq_baseline_item bi')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = bi.id_boq_item', 'left')
            ->where('bi.id_mainfeeder_boq_baseline', (int) $baseline['id_mainfeeder_boq_baseline'])
            ->order_by('m.sort_no', 'ASC')
            ->get()
            ->result_array();

        $progressMap = $this->getProgressQtyMap((int) $mainfeederId);
        foreach ($rows as &$row) {
            $baselineItemId = (int) ($row['id_mainfeeder_boq_baseline_item'] ?? 0);
            $actual = (float) ($progressMap[$baselineItemId] ?? 0);
            $plan = (float) ($row['qty_boq'] ?? 0);
            $row['qty_progress'] = $actual;
            $row['remaining_qty'] = max(0, $plan - $actual);
            $row['progress_percent'] = $plan > 0 ? min(100, round(($actual / $plan) * 100, 2)) : 0;
        }
        unset($row);

        return $rows;
    }

    public function createDailyActivity($mainfeederId, array $payload, array $photos)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0) {
            return 0;
        }

        $this->db->trans_start();
        $this->db->insert('tb_myrep_mainfeeder_impl_daily_activity', [
            'id_mainfeeder' => $mainfeederId,
            'activity_date' => (string) ($payload['activity_date'] ?? date('Y-m-d')),
            'activity_code' => (string) ($payload['activity_code'] ?? ''),
            'activity_name' => (string) ($payload['activity_name'] ?? ''),
            'activity_detail' => !empty($payload['activity_detail']) ? (string) $payload['activity_detail'] : null,
            'boq_type' => (string) ($payload['boq_type'] ?? ''),
            'qty_activity' => (float) ($payload['qty_activity'] ?? 0),
            'unit_activity' => (string) ($payload['unit_activity'] ?? ''),
            'team_count' => (int) ($payload['team_count'] ?? 0),
            'worker_count' => (int) ($payload['worker_count'] ?? 0),
            'remark_activity' => !empty($payload['remark_activity']) ? (string) $payload['remark_activity'] : null,
            'created_by' => (int) ($payload['created_by'] ?? 0),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ]);
        $activityId = (int) $this->db->insert_id();
        foreach ($photos as $photo) {
            $this->db->insert('tb_myrep_mainfeeder_impl_daily_activity_photo', [
                'id_daily_activity_mainfeeder' => $activityId,
                'file_name' => (string) ($photo['file_name'] ?? ''),
                'file_path' => (string) ($photo['file_path'] ?? ''),
                'caption' => !empty($photo['caption']) ? (string) $photo['caption'] : null,
                'uploaded_by' => (int) ($payload['created_by'] ?? 0),
            ]);
        }
        $this->allocateProgress($mainfeederId, (string) $payload['activity_date'], (string) $payload['activity_code'], (float) $payload['qty_activity'], (int) $payload['created_by']);
        $this->db->where('id_mainfeeder', $mainfeederId)->update('tb_rfs_myrep_mainfeeder', ['current_status' => 'IMPLEMENTASI', 'updated_by' => (int) ($payload['updated_by'] ?? 0)]);
        $this->db->trans_complete();
        return $this->db->trans_status() ? $activityId : 0;
    }

    public function getDailyActivities($mainfeederId)
    {
        return $this->db
            ->select('a.*, COUNT(p.id_activity_photo_mainfeeder) AS photo_count', false)
            ->from('tb_myrep_mainfeeder_impl_daily_activity a')
            ->join('tb_myrep_mainfeeder_impl_daily_activity_photo p', 'p.id_daily_activity_mainfeeder = a.id_daily_activity_mainfeeder', 'left')
            ->where('a.id_mainfeeder', (int) $mainfeederId)
            ->group_by('a.id_daily_activity_mainfeeder')
            ->order_by('a.activity_date', 'DESC')
            ->order_by('a.id_daily_activity_mainfeeder', 'DESC')
            ->get()
            ->result_array();
    }

    public function updateAtp($mainfeederId, array $payload)
    {
        $status = strtoupper(trim((string) ($payload['status_atp'] ?? '')));
        if ($status !== '' && !in_array($status, ['PUNCLIST', 'DONE'], true)) {
            return false;
        }

        $row = [
            'email_atp_date' => !empty($payload['email_atp_date']) ? (string) $payload['email_atp_date'] : null,
            'atp_date' => !empty($payload['atp_date']) ? (string) $payload['atp_date'] : null,
            'status_atp' => $status !== '' ? $status : null,
            'current_status' => $status === 'DONE' ? 'CHECKLIST' : 'ATP',
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];

        return $this->db->where('id_mainfeeder', (int) $mainfeederId)->update('tb_rfs_myrep_mainfeeder', $row);
    }

    public function saveAtpFile($mainfeederId, array $payload)
    {
        return $this->db->insert('tb_myrep_mainfeeder_atp_file', [
            'id_mainfeeder' => (int) $mainfeederId,
            'doc_type' => strtoupper(trim((string) ($payload['doc_type'] ?? ''))),
            'file_name' => (string) ($payload['file_name'] ?? ''),
            'file_path' => (string) ($payload['file_path'] ?? ''),
            'remark' => trim((string) ($payload['remark'] ?? '')),
            'uploaded_by' => (int) ($payload['uploaded_by'] ?? 0),
        ]);
    }

    public function hasAtpDocument($mainfeederId, $docType)
    {
        return $this->db
            ->from('tb_myrep_mainfeeder_atp_file')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->where('doc_type', strtoupper(trim((string) $docType)))
            ->count_all_results() > 0;
    }

    public function getAtpFiles($mainfeederId)
    {
        return $this->db
            ->from('tb_myrep_mainfeeder_atp_file')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->order_by('id_atp_file_mainfeeder', 'DESC')
            ->get()
            ->result_array();
    }

    public function markDone($mainfeederId, $userId)
    {
        return $this->db->where('id_mainfeeder', (int) $mainfeederId)->update('tb_rfs_myrep_mainfeeder', [
            'current_status' => 'DONE',
            'updated_by' => (int) $userId,
        ]);
    }

    public function getPoHeaders($mainfeederId)
    {
        $poType = $this->getProjectTypeById($mainfeederId);
        return $this->db
            ->from('tb_myrep_po_header')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->where('UPPER(po_type)', $poType)
            ->order_by('po_date', 'DESC')
            ->order_by('id_po_header', 'DESC')
            ->get()
            ->result_array();
    }

    public function poHeaderExists($mainfeederId, $poType, $poCategory, $poNumber, $excludePoHeaderId = 0)
    {
        $mainfeederId = (int) $mainfeederId;
        $poType = $this->normalizeStandaloneProjectType($poType);
        $poCategory = strtoupper(trim((string) $poCategory));
        $poNumber = strtoupper(trim((string) $poNumber));
        if ($mainfeederId <= 0 || $poNumber === '') {
            return false;
        }

        $query = $this->db
            ->from('tb_myrep_po_header')
            ->where('id_mainfeeder', $mainfeederId)
            ->where('UPPER(TRIM(po_type))', $poType)
            ->where("UPPER(TRIM(COALESCE(po_category, 'INITIAL'))) = " . $this->db->escape($poCategory), null, false)
            ->where('UPPER(TRIM(po_number))', $poNumber);
        if ((int) $excludePoHeaderId > 0) {
            $query->where('id_po_header !=', (int) $excludePoHeaderId);
        }

        return $query->limit(1)->count_all_results() > 0;
    }

    public function createPoHeader($mainfeederId, array $payload)
    {
        $mainfeederId = (int) $mainfeederId;
        if ($mainfeederId <= 0 || !$this->db->field_exists('id_mainfeeder', 'tb_myrep_po_header')) {
            return 0;
        }

        $poType = $this->getProjectTypeById($mainfeederId);
        $poValue = (float) ($payload['po_value'] ?? 0);
        $hasNyRefColumn = $this->ensurePoHeaderNyRefColumn();
        $this->db->trans_start();
        $header = [
            'id_myrep_cluster' => null,
            'project_type' => $poType,
            'id_mainfeeder' => $mainfeederId,
            'parent_po_header_id' => !empty($payload['parent_po_header_id']) ? (int) $payload['parent_po_header_id'] : null,
            'po_type' => $poType,
            'po_category' => (string) ($payload['po_category'] ?? 'INITIAL'),
            'po_number' => (string) ($payload['po_number'] ?? ''),
            'po_date' => $payload['po_date'] ?? null,
            'po_value' => $poValue,
            'status_po' => (string) ($payload['status_po'] ?? 'ISSUED'),
            'po_version_label' => !empty($payload['po_version_label']) ? (string) $payload['po_version_label'] : null,
            'remark_po' => !empty($payload['remark_po']) ? (string) $payload['remark_po'] : null,
            'created_by' => (int) ($payload['created_by'] ?? 0),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ];
        if ($this->db->field_exists('on_target', 'tb_myrep_po_header')) {
            $header['on_target'] = 1;
        }
        if ($hasNyRefColumn) {
            $nyRef = strtoupper(trim((string) ($payload['po_monitor_ny_ref'] ?? '')));
            $header['po_monitor_ny_ref'] = $nyRef !== '' ? $nyRef : null;
        }
        $this->db->insert('tb_myrep_po_header', $header);
        $poHeaderId = (int) $this->db->insert_id();
        foreach ($this->defaultTerminPercents as $index => $percent) {
            $this->db->insert('tb_myrep_po_termin', [
                'id_po_header' => $poHeaderId,
                'termin_no' => $index + 1,
                'termin_percent' => $percent,
                'termin_value' => round(($poValue * $percent) / 100, 2),
                'status_termin' => 'NOT READY',
                'created_by' => (int) ($payload['created_by'] ?? 0),
                'updated_by' => (int) ($payload['updated_by'] ?? 0),
            ]);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $poHeaderId : 0;
    }

    public function getTerminRowsByPoId($poId)
    {
        return $this->db->from('tb_myrep_po_termin')->where('id_po_header', (int) $poId)->order_by('termin_no', 'ASC')->get()->result_array();
    }

    public function getTerminById($terminId)
    {
        return $this->db
            ->select('t.*, p.id_mainfeeder, p.po_number, p.po_type, p.po_category')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header p', 'p.id_po_header = t.id_po_header', 'inner')
            ->where('t.id_po_termin', (int) $terminId)
            ->get()
            ->row_array();
    }

    public function updateTermin($terminId, array $payload)
    {
        return $this->db->where('id_po_termin', (int) $terminId)->update('tb_myrep_po_termin', [
            'status_termin' => (string) ($payload['status_termin'] ?? 'NOT READY'),
            'invoice_number' => trim((string) ($payload['invoice_number'] ?? '')),
            'invoice_date' => !empty($payload['invoice_date']) ? (string) $payload['invoice_date'] : null,
            'invoice_value' => array_key_exists('invoice_value', $payload) && $payload['invoice_value'] !== null ? (float) $payload['invoice_value'] : null,
            'bast_date' => !empty($payload['bast_date']) ? (string) $payload['bast_date'] : null,
            'payment_date' => !empty($payload['payment_date']) ? (string) $payload['payment_date'] : null,
            'remark_termin' => trim((string) ($payload['remark_termin'] ?? '')),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ]);
    }

    public function updateTerminCertificate($terminId, $value, $userId)
    {
        if (!$this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            return false;
        }
        return $this->db->where('id_po_termin', (int) $terminId)->update('tb_myrep_po_termin', [
            'sertifikat_invoice_date' => trim((string) $value),
            'updated_by' => (int) $userId,
        ]);
    }

    public function getMasterBoqItems()
    {
        if (!$this->db->table_exists('md_myrep_boq_item')) {
            return [];
        }
        return $this->db
            ->from('md_myrep_boq_item')
            ->where('is_active', 1)
            ->order_by('sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    private function ensureDrmPackages($mainfeederId, $userId)
    {
        $groups = $this->db->from('md_myrep_flow_doc_group')->where('flow_type', 'DRM')->where('is_active', 1)->get()->result_array();
        foreach ($groups as $group) {
            $this->ensureDrmPackage((int) $mainfeederId, (int) $group['id_doc_group'], (int) $userId);
        }
    }

    private function ensureDrmPackage($mainfeederId, $docGroupId, $userId)
    {
        $existing = $this->db->get_where('tb_myrep_mainfeeder_doc_package', [
            'id_mainfeeder' => (int) $mainfeederId,
            'flow_type' => 'DRM',
            'id_doc_group' => (int) $docGroupId,
        ])->row_array();
        if ($existing) {
            return (int) $existing['id_doc_package_mainfeeder_flow'];
        }
        $this->db->insert('tb_myrep_mainfeeder_doc_package', [
            'id_mainfeeder' => (int) $mainfeederId,
            'flow_type' => 'DRM',
            'id_doc_group' => (int) $docGroupId,
            'status_package' => 'NOT STARTED',
            'created_by' => (int) $userId,
            'updated_by' => (int) $userId,
        ]);
        return (int) $this->db->insert_id();
    }

    private function createDocLog($fileId, $packageId, $docItemId, $actionType, $statusAfter, $fileName, $remark, $userId)
    {
        $this->db->insert('tb_myrep_mainfeeder_doc_file_log', [
            'id_doc_file_mainfeeder_flow' => (int) $fileId,
            'id_doc_package_mainfeeder_flow' => (int) $packageId,
            'id_doc_item' => (int) $docItemId,
            'action_type' => (string) $actionType,
            'status_after' => (string) $statusAfter,
            'file_name' => (string) $fileName,
            'remark' => (string) $remark,
            'action_by' => (int) $userId,
        ]);
    }

    private function refreshDocPackageStatus($packageId)
    {
        $required = (int) $this->db
            ->from('md_myrep_flow_doc_item i')
            ->join('tb_myrep_mainfeeder_doc_package p', 'p.id_doc_group = i.id_doc_group', 'inner')
            ->where('p.id_doc_package_mainfeeder_flow', (int) $packageId)
            ->where('i.is_active', 1)
            ->where('i.is_required', 1)
            ->count_all_results();
        $approved = (int) $this->db
            ->from('tb_myrep_mainfeeder_doc_file')
            ->where('id_doc_package_mainfeeder_flow', (int) $packageId)
            ->where('status_file', 'APPROVED')
            ->count_all_results();
        $uploaded = (int) $this->db
            ->from('tb_myrep_mainfeeder_doc_file')
            ->where('id_doc_package_mainfeeder_flow', (int) $packageId)
            ->count_all_results();
        $status = $uploaded <= 0 ? 'NOT STARTED' : (($required > 0 && $approved >= $required) ? 'DONE' : 'ON PROGRESS');
        $this->db->where('id_doc_package_mainfeeder_flow', (int) $packageId)->update('tb_myrep_mainfeeder_doc_package', ['status_package' => $status]);
    }

    private function archiveMainfeederBaselines($mainfeederId)
    {
        $this->db->where('id_mainfeeder', (int) $mainfeederId)->where('status_baseline', 'ACTIVE')->update('tb_myrep_mainfeeder_boq_baseline', ['status_baseline' => 'ARCHIVED']);
    }

    private function getActiveBaseline($mainfeederId)
    {
        return $this->db
            ->from('tb_myrep_mainfeeder_boq_baseline')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->where('status_baseline', 'ACTIVE')
            ->order_by('id_mainfeeder_boq_baseline', 'DESC')
            ->get()
            ->row_array();
    }

    private function getProgressQtyMap($mainfeederId)
    {
        $rows = $this->db
            ->select('id_mainfeeder_boq_baseline_item, SUM(qty_progress) AS qty_progress', false)
            ->from('tb_myrep_mainfeeder_boq_progress_item')
            ->where('id_mainfeeder', (int) $mainfeederId)
            ->where_in('status_progress', ['SUBMITTED', 'APPROVED'])
            ->group_by('id_mainfeeder_boq_baseline_item')
            ->get()
            ->result_array();
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_mainfeeder_boq_baseline_item']] = (float) $row['qty_progress'];
        }
        return $map;
    }

    private function allocateProgress($mainfeederId, $progressDate, $activityCode, $qtyActivity, $userId)
    {
        $rows = $this->getBaselineCompareRows($mainfeederId);
        if (empty($rows) || (float) $qtyActivity <= 0) {
            return;
        }

        $targetType = $this->resolveActivityBoqType($activityCode);
        $targets = [];
        foreach ($rows as $row) {
            $type = strtoupper(trim((string) ($row['item_type'] ?? '')));
            if ($targetType === '' || $type === $targetType || strpos($type, $targetType) !== false) {
                $targets[] = $row;
            }
        }
        if (empty($targets)) {
            $targets = $rows;
        }

        $remaining = (float) $qtyActivity;
        foreach ($targets as $row) {
            if ($remaining <= 0) {
                break;
            }
            $remainingQty = (float) ($row['remaining_qty'] ?? 0);
            if ($remainingQty <= 0) {
                continue;
            }
            $allocated = min($remaining, $remainingQty);
            $this->db->insert('tb_myrep_mainfeeder_boq_progress_item', [
                'id_mainfeeder' => (int) $mainfeederId,
                'id_mainfeeder_boq_baseline' => (int) $row['id_mainfeeder_boq_baseline'],
                'id_mainfeeder_boq_baseline_item' => (int) $row['id_mainfeeder_boq_baseline_item'],
                'progress_date' => (string) $progressDate,
                'qty_progress' => $allocated,
                'status_progress' => 'APPROVED',
                'remark_progress' => '[AUTO] Aktivitas Harian Mainfeeder (' . strtoupper((string) $activityCode) . ')',
                'created_by' => (int) $userId,
                'updated_by' => (int) $userId,
            ]);
            $remaining -= $allocated;
        }
    }

    private function resolveActivityBoqType($activityCode)
    {
        $activityCode = strtoupper(trim((string) $activityCode));
        if ($activityCode === 'PULLING_CABLE') {
            return 'CABLE';
        }
        if (in_array($activityCode, ['DIGGING_HOLE', 'TANAM_TIANG', 'COR_FONDATION'], true)) {
            return 'TIANG';
        }
        if ($activityCode === 'SLING_WIRE') {
            return 'SLING WIRE';
        }
        if ($activityCode === 'INSTALASI_FAT_FDT') {
            return 'FDT';
        }
        if ($activityCode === 'SPLICING_FO') {
            return 'SPLICING';
        }
        return '';
    }

    private function normalizeStatus($status)
    {
        $status = strtoupper(trim((string) $status));
        return in_array($status, $this->getStatusOptions(), true) ? $status : 'DRM';
    }

    private function extractUpperValues(array $rows, $column)
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
