<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBatch_Approval_MyRep extends CI_Model
{
    public function batchTablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_valsal',
            'tb_myrep_batch_approval',
            'tb_myrep_batch_approval_pic',
            'tb_rfs_myrep_monthly_target',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function batchDocumentTablesReady()
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

    public function getCityOptions()
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
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
        if (!$this->batchTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                c.id_myrep_cluster,
                c.id_target,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.chief,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                v.id_valsal,
                v.valsal_date,
                v.homepass_valsal,
                v.status_valsal,
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where_in('UPPER(v.status_valsal)', ['DONE', 'APPROVED'])
            ->where('UPPER(c.status_current)', 'VALSAL')
            ->where('ba.id_batch_approval IS NULL', null, false)
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBatchRows($city = '', $status = '')
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.id_target,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.chief,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                c.created_at,
                v.id_valsal,
                v.valsal_date,
                v.homepass_valsal,
                ba.id_batch_approval,
                ba.submission_date,
                ba.hp_donasi,
                ba.nominal_pengajuan_area,
                ba.nominal_nego_emr,
                ba.nominal_release_finance,
                ba.nominal_per_homepass,
                ba.bank_name,
                ba.bank_account_number,
                ba.recipient_name,
                ba.recipient_phone,
                ba.recipient_position,
                ba.recipient_period,
                ba.free_wifi_qty,
                ba.free_wifi_period_month,
                ba.astri_batch_number,
                ba.staging_status,
                ba.submitted_to_ho_at,
                ba.submitted_to_astri_at,
                ba.submitted_to_finance_at,
                ba.released_at,
                ba.transfer_proof_file_name,
                ba.transfer_proof_file_path,
                ba.remark_batch_approval,
                doc_group.id_doc_group AS batch_doc_group_id,
                doc_item.id_doc_item AS batch_doc_item_id,
                doc_package.id_doc_package AS batch_doc_package_id,
                doc_package.status_package AS batch_doc_package_status,
                doc_file.id_doc_file AS batch_doc_file_id,
                doc_file.file_name AS batch_doc_file_name,
                doc_file.file_path AS batch_doc_file_path,
                doc_file.status_file AS batch_doc_status,
                doc_file.is_document_not_required AS batch_doc_not_required,
                doc_file.remark AS batch_doc_remark,
                doc_file.approved_at AS batch_doc_approved_at,
                doc_file.reviewed_at AS batch_doc_reviewed_at,
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if ($this->batchDocumentTablesReady()) {
            $this->db
                ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'BATCH_APPROVAL' AND doc_group.group_label = 'RAR' AND doc_group.is_active = 1", 'left', false)
                ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
                ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'BATCH_APPROVAL\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
                ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left');
        } else {
            $this->db->select("
                NULL AS batch_doc_group_id,
                NULL AS batch_doc_item_id,
                NULL AS batch_doc_package_id,
                NULL AS batch_doc_package_status,
                NULL AS batch_doc_file_id,
                NULL AS batch_doc_file_name,
                NULL AS batch_doc_file_path,
                NULL AS batch_doc_status,
                NULL AS batch_doc_not_required,
                NULL AS batch_doc_remark
            ", false);
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        if ($status !== '') {
            $normalizedStatus = strtoupper($status);
            if (in_array($normalizedStatus, ['DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'REJECTED'], true)) {
                $this->db->where('ba.staging_status', $normalizedStatus);
            } else {
                $this->db->where('c.status_current', $normalizedStatus);
            }
        }

        return $this->db
            ->group_by('c.id_myrep_cluster')
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getBatchCandidateById($clusterId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, v.id_valsal, v.valsal_date, v.homepass_valsal, v.status_valsal, ba.id_batch_approval')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where_in('UPPER(v.status_valsal)', ['DONE', 'APPROVED'])
            ->get()
            ->row_array();
    }

    public function getBatchByClusterId($clusterId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, v.id_valsal, v.valsal_date, v.homepass_valsal, v.status_valsal, ba.*')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();
    }

    public function getBatchPics($batchId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        return $this->db
            ->from('tb_myrep_batch_approval_pic')
            ->where('id_batch_approval', (int) $batchId)
            ->order_by('pic_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function createBatchApproval($clusterId, $batchPayload, $clusterPayload, $pics = [])
    {
        $this->db->trans_start();

        $this->db->where('id_myrep_cluster', (int) $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $batchPayload['id_myrep_cluster'] = (int) $clusterId;
        $this->db->insert('tb_myrep_batch_approval', $batchPayload);
        $batchId = (int) $this->db->insert_id();

        foreach ($pics as $pic) {
            $pic['id_batch_approval'] = $batchId;
            $this->db->insert('tb_myrep_batch_approval_pic', $pic);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $batchId : 0;
    }

    public function updateBatchApproval($clusterId, $batchId, $batchPayload, $clusterPayload, $pics = [])
    {
        $clusterId = (int) $clusterId;
        $batchId = (int) $batchId;
        if ($clusterId <= 0 || $batchId <= 0) {
            return false;
        }

        $existing = $this->getBatchByClusterId($clusterId);
        if (empty($existing)) {
            return false;
        }

        $clusterPayload['status_current'] = $this->resolveSafeCurrentStatus(
            (string) ($existing['status_current'] ?? ''),
            (string) ($clusterPayload['status_current'] ?? 'DRAFT')
        );

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $this->db->where('id_batch_approval', $batchId)->update('tb_myrep_batch_approval', $batchPayload);
        $this->db->where('id_batch_approval', $batchId)->delete('tb_myrep_batch_approval_pic');
        foreach ($pics as $pic) {
            $pic['id_batch_approval'] = $batchId;
            $this->db->insert('tb_myrep_batch_approval_pic', $pic);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateTransferProof($batchId, $batchPayload, $clusterPayload)
    {
        $batchId = (int) $batchId;
        if ($batchId <= 0) {
            return false;
        }

        $batch = $this->db->get_where('tb_myrep_batch_approval', ['id_batch_approval' => $batchId])->row_array();
        if (!$batch) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id_batch_approval', $batchId)->update('tb_myrep_batch_approval', $batchPayload);
        $this->db->where('id_myrep_cluster', (int) $batch['id_myrep_cluster'])->update('tb_myrep_cluster', $clusterPayload);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateBatchStage($clusterId, $batchId, $batchPayload, $clusterPayload)
    {
        $clusterId = (int) $clusterId;
        $batchId = (int) $batchId;
        if ($clusterId <= 0 || $batchId <= 0) {
            return false;
        }

        $existing = $this->getBatchByClusterId($clusterId);
        if (empty($existing) || (int) ($existing['id_batch_approval'] ?? 0) !== $batchId) {
            return false;
        }

        $clusterPayload['status_current'] = $this->resolveSafeCurrentStatus(
            (string) ($existing['status_current'] ?? ''),
            (string) ($clusterPayload['status_current'] ?? 'VALSAL')
        );

        $this->db->trans_start();
        $this->db->where('id_batch_approval', $batchId)->update('tb_myrep_batch_approval', $batchPayload);
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function getBatchDocumentContext($clusterId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                doc_group.id_doc_group,
                doc_item.id_doc_item,
                doc_item.doc_name,
                doc_package.id_doc_package,
                doc_package.status_package,
                doc_file.id_doc_file,
                doc_file.file_name,
                doc_file.file_path,
                doc_file.status_file,
                doc_file.is_document_not_required,
                doc_file.remark,
                doc_file.approved_at,
                doc_file.reviewed_at
            ')
            ->from('tb_myrep_cluster c')
            ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'BATCH_APPROVAL' AND doc_group.group_label = 'RAR' AND doc_group.is_active = 1", 'left', false)
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'BATCH_APPROVAL\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();
    }

    public function saveBatchFileUpload($clusterId, $data)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->batchDocumentTablesReady()) {
            return 0;
        }

        $context = $this->getBatchDocumentContext($clusterId);
        if (empty($context['id_doc_group']) || empty($context['id_doc_item'])) {
            return 0;
        }

        $packageId = $this->ensurePackage($clusterId, 'BATCH_APPROVAL', (int) $context['id_doc_group'], (int) $data['uploaded_by']);
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

    public function updateBatchFileStatus($fileId, $data)
    {
        if (!$this->batchDocumentTablesReady()) {
            return false;
        }

        $file = $this->db->get_where('tb_myrep_flow_doc_file', [
            'id_doc_file' => (int) $fileId,
        ])->row_array();

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

    public function getBatchFileById($fileId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                f.*,
                p.id_myrep_cluster,
                c.cluster_name,
                i.doc_name
            ')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->get()
            ->row_array();
    }

    public function getBatchFileLogs($fileId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                l.*,
                u.nama_user
            ')
            ->from('tb_myrep_flow_doc_file_log l')
            ->join('tb_master_user u', 'u.id_user = l.action_by', 'left')
            ->where('l.id_doc_file', (int) $fileId)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log', 'DESC')
            ->get()
            ->result_array();
    }

    public function getBatchFileByClusterId($clusterId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        return $this->db
            ->select('
                f.*,
                p.id_myrep_cluster,
                p.status_package,
                c.cluster_name,
                i.doc_name
            ')
            ->from('tb_myrep_flow_doc_package p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = p.id_doc_group AND i.is_active = 1', 'left')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->where('p.flow_type', 'BATCH_APPROVAL')
            ->order_by('i.sort_no', 'ASC')
            ->get()
            ->row_array();
    }

    private function ensurePackage($clusterId, $flowType, $docGroupId, $userId)
    {
        $existing = $this->db->get_where('tb_myrep_flow_doc_package', [
            'id_myrep_cluster' => (int) $clusterId,
            'flow_type' => (string) $flowType,
            'id_doc_group' => (int) $docGroupId,
        ])->row_array();

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
