<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPost_Donasi_MyRep extends CI_Model
{
    private $autoLinkedPostDonasiDocuments = [
        'SURAT IJIN RT / RW' => [
            'flow_type' => 'BAK',
            'doc_name' => 'Surat Ijin',
            'group_label' => 'BA OPEN',
        ],
        'FORM CLUSTER SURVEY' => [
            'flow_type' => 'BAK',
            'doc_name' => 'Form Survey',
            'group_label' => 'BA OPEN',
        ],
        'LAYOUT SND KASAR' => [
            'flow_type' => 'VALSAL',
            'doc_name' => 'SND Kasar',
            'group_label' => 'VALIDASI SALES',
        ],
    ];

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
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
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
            ->where_in('UPPER(c.status_current)', ['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'])
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

    public function getDocumentSummary($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return ['total' => 0, 'uploaded' => 0, 'approved' => 0];
        }

        $summaryMap = $this->getDocumentSummaryMap([$clusterId]);
        return $summaryMap[$clusterId] ?? ['total' => 0, 'uploaded' => 0, 'approved' => 0];
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

    public function saveLinkedReviewDecision($clusterId, $docItemId, $data)
    {
        if (!$this->documentTablesReady()) {
            return 0;
        }

        $context = $this->getDocumentDetail($clusterId, $docItemId);
        if (empty($context['id_doc_group'])) {
            return 0;
        }

        $userId = (int) ($data['approved_by'] ?? 0);
        $statusFile = strtoupper((string) ($data['status_file'] ?? ''));
        if ($userId <= 0 || !in_array($statusFile, ['APPROVED', 'REJECTED'], true)) {
            return 0;
        }

        $packageId = $this->ensurePackage($clusterId, (int) $context['id_doc_group'], $userId);
        if ($packageId <= 0) {
            return 0;
        }

        $existing = $this->db->get_where('tb_myrep_flow_doc_file', [
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $docItemId,
        ])->row_array();

        $payload = [
            'file_name' => trim((string) ($data['file_name'] ?? '')) !== '' ? trim((string) $data['file_name']) : '[LINKED] ' . (string) ($context['doc_name'] ?? 'POST_DONASI'),
            'file_path' => '',
            'is_document_not_required' => 0,
            'status_file' => $statusFile,
            'remark' => (string) ($data['remark'] ?? ''),
            'uploaded_by' => $userId,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'approved_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => $statusFile === 'APPROVED' ? date('Y-m-d H:i:s') : null,
        ];

        if ($existing) {
            $this->db->where('id_doc_file', (int) $existing['id_doc_file'])->update('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
        } else {
            $payload['id_doc_package'] = $packageId;
            $payload['id_doc_item'] = (int) $docItemId;
            $this->db->insert('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
        }

        if ($fileId <= 0) {
            return 0;
        }

        $this->createFileLog([
            'id_doc_file' => $fileId,
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $docItemId,
            'action_type' => $statusFile === 'APPROVED' ? 'APPROVE' : 'REJECT',
            'status_after' => $statusFile,
            'file_name' => (string) $payload['file_name'],
            'remark' => (string) ($data['remark'] ?? ''),
            'action_by' => $userId,
        ]);

        $this->refreshPackageStatus($packageId);
        return $fileId;
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

    public function getFileLogs($fileId)
    {
        if (!$this->documentTablesReady()) {
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
        if (empty($clusterIds) || !$this->documentTablesReady()) {
            return [];
        }

        $linkedMap = $this->getAutoLinkedSupportDocumentMapByClusterIds($clusterIds);

        $rows = $this->db
            ->select('
                c.id_myrep_cluster,
                i.doc_name,
                f.id_doc_file,
                f.status_file
            ')
            ->from('tb_myrep_cluster c')
            ->join("md_myrep_flow_doc_group g", "g.flow_type = 'POST_DONASI' AND g.is_active = 1", 'inner', false)
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package p', 'p.id_myrep_cluster = c.id_myrep_cluster AND p.flow_type = \'POST_DONASI\' AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.flow_type', 'POST_DONASI')
            ->where_in('c.id_myrep_cluster', $clusterIds)
            ->order_by('i.sort_no', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($clusterIds as $clusterId) {
            $map[$clusterId] = [
                'total' => 0,
                'uploaded' => 0,
                'approved' => 0,
            ];
        }

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $docName = strtoupper(trim((string) ($row['doc_name'] ?? '')));
            $hasActualFile = (int) ($row['id_doc_file'] ?? 0) > 0;
            $actualStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
            $hasLinkedFile = !empty($linkedMap[$clusterId][$docName]['linked_source_file_id']);

            $map[$clusterId]['total']++;

            if ($hasActualFile || $hasLinkedFile) {
                $map[$clusterId]['uploaded']++;
            }

            if ($hasActualFile) {
                if ($actualStatus === 'APPROVED') {
                    $map[$clusterId]['approved']++;
                }
                continue;
            }

        }

        return $map;
    }

    private function getAutoLinkedSupportDocumentMapByClusterIds($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->documentTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('
                doc_package.id_myrep_cluster,
                doc_package.flow_type,
                doc_group.group_label,
                doc_item.doc_name,
                doc_file.id_doc_file,
                doc_file.file_name,
                doc_file.file_path,
                doc_file.status_file
            ')
            ->from('tb_myrep_flow_doc_package doc_package')
            ->join('md_myrep_flow_doc_group doc_group', 'doc_group.id_doc_group = doc_package.id_doc_group', 'inner')
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'inner')
            ->where_in('doc_package.id_myrep_cluster', $clusterIds)
            ->where_in('doc_package.flow_type', ['BAK', 'VALSAL'])
            ->where('doc_group.is_active', 1)
            ->get()
            ->result_array();

        $sourceLookup = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $key = strtoupper(trim((string) ($row['flow_type'] ?? ''))) . '|' .
                strtoupper(trim((string) ($row['group_label'] ?? ''))) . '|' .
                strtoupper(trim((string) ($row['doc_name'] ?? '')));

            if (!isset($sourceLookup[$clusterId])) {
                $sourceLookup[$clusterId] = [];
            }

            if (!isset($sourceLookup[$clusterId][$key])) {
                $sourceLookup[$clusterId][$key] = $row;
            }
        }

        $result = [];
        foreach ($clusterIds as $clusterId) {
            $result[$clusterId] = [];

            foreach ($this->autoLinkedPostDonasiDocuments as $postDocName => $mapping) {
                $lookupKey = strtoupper($mapping['flow_type']) . '|' .
                    strtoupper($mapping['group_label']) . '|' .
                    strtoupper($mapping['doc_name']);

                if (empty($sourceLookup[$clusterId][$lookupKey]['id_doc_file'])) {
                    continue;
                }

                $sourceRow = $sourceLookup[$clusterId][$lookupKey];
                $result[$clusterId][$postDocName] = [
                    'linked_source_file_id' => (int) $sourceRow['id_doc_file'],
                    'linked_source_file_name' => (string) ($sourceRow['file_name'] ?? ''),
                    'linked_source_file_path' => (string) ($sourceRow['file_path'] ?? ''),
                    'linked_source_status' => (string) ($sourceRow['status_file'] ?? ''),
                ];
            }
        }

        return $result;
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
