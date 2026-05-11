<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MDRM_MyRep extends CI_Model
{
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

    public function getCityOptions()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'])
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

        return $cities;
    }

    public function getEligibleClusterOptions()
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, ba.hp_donasi, ba.released_at, v.homepass_valsal, d.id_drm, t.year_num, t.month_num')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->where('d.id_drm IS NULL', null, false)
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getDrmRows($city = '', $status = '')
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, ba.hp_donasi, ba.released_at, d.id_drm, d.drm_date, d.homepass_drm, d.nama_olt, d.status_drm, d.remark_drm, t.year_num, t.month_num')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED');

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        $filterByDisplayStatus = false;
        if ($status !== '') {
            if (in_array($status, ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'], true)) {
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
        foreach ($rows as &$row) {
            $summary = $docSummaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0, 'rejected' => 0];
            $row['doc_total'] = $summary['total'];
            $row['doc_uploaded'] = $summary['uploaded'];
            $row['doc_approved'] = $summary['approved'];
            $row['doc_rejected'] = $summary['rejected'];
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

    public function getDrmCandidateById($clusterId)
    {
        if (!$this->drmTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, ba.id_batch_approval, ba.hp_donasi, ba.released_at, d.id_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where('UPPER(ba.staging_status)', 'RELEASED')
            ->get()
            ->row_array();
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
            ->select('m.id_boq_item, m.excel_item_name, m.item_name, m.item_type, m.default_photo_qty, m.photo_type, m.remarks_rule AS master_remarks_rule, m.sort_no, h.id_drm_boq, h.review_status, i.id_drm_boq_item, i.qty_boq, i.jumlah_foto, i.remarks_rule, i.target_foto_required, i.item_note')
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
        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', (int) $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $drmPayload['id_myrep_cluster'] = (int) $clusterId;
        $this->db->insert('tb_myrep_drm', $drmPayload);
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

        $file = $this->db->get_where('tb_myrep_flow_doc_file', ['id_doc_file' => (int) $fileId])->row_array();
        if (!$file) {
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
            $this->db
                ->where('id_drm_boq', (int) $existing['id_drm_boq'])
                ->update('tb_myrep_drm_boq', [
                    'id_drm' => $drmId > 0 ? $drmId : null,
                    'source_doc_file_id' => $sourceDocFileId > 0 ? (int) $sourceDocFileId : null,
                    'review_status' => $reviewStatus,
                    'submitted_at' => $submitToHo ? date('Y-m-d H:i:s') : ($existing['submitted_at'] ?? null),
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
                'submitted_at' => $submitToHo ? date('Y-m-d H:i:s') : null,
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
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->where('p.flow_type', $flowType)
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
        if (empty($clusterHeader['id_drm_boq']) || empty($subfeederHeader['id_drm_boq'])) {
            return;
        }

        $clusterStatus = strtoupper(trim((string) ($clusterHeader['review_status'] ?? '')));
        $subfeederStatus = strtoupper(trim((string) ($subfeederHeader['review_status'] ?? '')));
        if ($clusterStatus !== 'APPROVED' || $subfeederStatus !== 'APPROVED') {
            return;
        }

        $clusterItems = $this->getDrmBoqItems($clusterId, 'CLUSTER');
        $subfeederItems = $this->getDrmBoqItems($clusterId, 'SUBFEEDER');
        $mergedItems = $this->mergeBoqItemsForBaseline($clusterItems, $subfeederItems);
        if (empty($mergedItems)) {
            return;
        }

        // Replace previous active baseline(s) before creating the new combined baseline.
        $this->db
            ->where('id_myrep_cluster', (int) $clusterId)
            ->where('status_baseline', 'ACTIVE')
            ->update('tb_myrep_boq_baseline', [
                'status_baseline' => 'REPLACED',
            ]);

        $baselinePayload = [
            'id_myrep_cluster' => (int) $clusterId,
            'id_drm_boq' => (int) ($clusterHeader['id_drm_boq'] ?? 0),
            'status_baseline' => 'ACTIVE',
            'approved_at' => $approvedAt,
            'approved_by' => (int) $userId,
        ];
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            // Implementasi currently reads CLUSTER scope; combined baseline is stored in this scope.
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

    public function rejectDrmBoq($clusterId, $userId, $remark, $scopeType = 'CLUSTER')
    {
        if (!$this->drmBoqTablesReady()) {
            return false;
        }

        $header = $this->getDrmBoqHeader($clusterId, $scopeType);
        if (empty($header['id_drm_boq'])) {
            return false;
        }

        return $this->db
            ->where('id_drm_boq', (int) $header['id_drm_boq'])
            ->update('tb_myrep_drm_boq', [
                'review_status' => 'REJECTED',
                'rejected_at' => date('Y-m-d H:i:s'),
                'approved_at' => null,
                'approved_by' => (int) $userId,
                'ho_review_remark' => $remark !== '' ? $remark : null,
                'updated_by' => (int) $userId,
            ]);
    }

    public function getDrmFileById($fileId)
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('f.*, p.id_myrep_cluster, c.cluster_name, i.doc_name')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->get()
            ->row_array();
    }

    public function getDrmFileLogs($fileId)
    {
        if (!$this->drmDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('l.*, u.nama_user')
            ->from('tb_myrep_flow_doc_file_log l')
            ->join('tb_master_user u', 'u.id_user = l.action_by', 'left')
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

    private function normalizeDrmScopeType($scopeType)
    {
        return strtoupper(trim((string) $scopeType)) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
    }

    private function resolveDrmDocumentFlowType($scopeType)
    {
        return $this->normalizeDrmScopeType($scopeType) === 'SUBFEEDER' ? 'DRM_SUBFEEDER' : 'DRM';
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
