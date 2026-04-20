<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPost_Donasi_MyRep extends CI_Model
{
    public function tablesReady()
    {
        $requiredTables = ['tb_myrep_cluster', 'tb_myrep_batch_approval', 'tb_rfs_myrep_monthly_target'];
        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function documentTablesReady()
    {
        $requiredTables = ['md_myrep_flow_doc_group', 'md_myrep_flow_doc_item', 'tb_myrep_flow_doc_package', 'tb_myrep_flow_doc_file', 'tb_myrep_flow_doc_file_log'];
        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
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
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED')
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

    public function getRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, ba.released_at, t.year_num, t.month_num')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'])
            ->where('UPPER(ba.staging_status)', 'RELEASED');

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        if ($status !== '') {
            $this->db->where('UPPER(c.status_current)', strtoupper($status));
        }

        $rows = $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $summaryMap = $this->getDocumentSummaryMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $summary = $summaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0];
            $row['doc_total'] = $summary['total'];
            $row['doc_uploaded'] = $summary['uploaded'];
            $row['doc_approved'] = $summary['approved'];
        }
        unset($row);

        return $rows;
    }

    public function getClusterById($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, ba.released_at')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        return $row ?: [];
    }

    public function getDocumentRows($clusterId)
    {
        if (!$this->documentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('doc_group.group_label, doc_item.id_doc_item, doc_item.doc_name, doc_item.doc_requirement_note, doc_file.id_doc_file, doc_file.file_name, doc_file.file_path, doc_file.status_file, doc_file.is_document_not_required, doc_file.remark, doc_file.uploaded_at, doc_file.reviewed_at, doc_file.approved_at')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = \'POST_DONASI\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', 'POST_DONASI')
            ->where('doc_group.is_active', 1)
            ->order_by('doc_item.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getDocumentDetail($clusterId, $docItemId)
    {
        if (!$this->documentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('doc_group.id_doc_group, doc_item.id_doc_item, doc_item.doc_name, doc_package.id_doc_package, doc_file.id_doc_file, doc_file.file_path')
            ->from('md_myrep_flow_doc_group doc_group')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = ' . (int) $clusterId . ' AND doc_package.flow_type = \'POST_DONASI\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('doc_group.flow_type', 'POST_DONASI')
            ->where('doc_item.id_doc_item', (int) $docItemId)
            ->get()
            ->row_array();
    }

    public function saveFileUpload($clusterId, $docItemId, $data)
    {
        $context = $this->getDocumentDetail($clusterId, $docItemId);
        if (empty($context['id_doc_group'])) {
            return 0;
        }

        $packageId = $this->ensurePackage($clusterId, (int) $context['id_doc_group'], (int) $data['uploaded_by']);
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

    public function updateFileStatus($fileId, $data)
    {
        if (!$this->documentTablesReady()) {
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

    public function getFileById($fileId)
    {
        if (!$this->documentTablesReady()) {
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

    private function getDocumentSummaryMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->documentTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select("p.id_myrep_cluster, COUNT(i.id_doc_item) AS total_doc, SUM(CASE WHEN f.id_doc_file IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc, SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'APPROVED' THEN 1 ELSE 0 END) AS approved_doc", false)
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package p', 'p.flow_type = \'POST_DONASI\' AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'POST_DONASI')
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
            ];
        }

        return $map;
    }

    private function ensurePackage($clusterId, $docGroupId, $userId)
    {
        $existing = $this->db->get_where('tb_myrep_flow_doc_package', ['id_myrep_cluster' => (int) $clusterId, 'flow_type' => 'POST_DONASI', 'id_doc_group' => (int) $docGroupId])->row_array();
        if ($existing) {
            return (int) $existing['id_doc_package'];
        }

        $this->db->insert('tb_myrep_flow_doc_package', [
            'id_myrep_cluster' => (int) $clusterId,
            'flow_type' => 'POST_DONASI',
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
}
