<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MVALSAL_MyRep extends CI_Model
{
    public function valsalTablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_bak',
            'tb_myrep_valsal',
            'tb_rfs_myrep_monthly_target',
        ];

        foreach ($requiredTables as $tableName) {
            if (!$this->db->table_exists($tableName)) {
                return false;
            }
        }

        return true;
    }

    public function valsalDocumentTablesReady()
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
        if (!$this->valsalTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
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
        if (!$this->valsalTablesReady()) {
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
                b.id_bak,
                b.bak_date,
                b.ba_open_date,
                b.homepass_bak,
                b.status_bak,
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where_in('UPPER(b.status_bak)', ['DONE', 'APPROVED'])
            ->where('UPPER(c.status_current)', 'BAK')
            ->where('v.id_valsal IS NULL', null, false)
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function getValsalRows($city = '', $status = '')
    {
        if (!$this->valsalTablesReady()) {
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
                b.id_bak,
                b.bak_date,
                b.homepass_bak,
                b.status_bak,
                v.id_valsal,
                v.valsal_date,
                v.homepass_valsal,
                v.status_valsal,
                v.remark_valsal,
                doc_group.id_doc_group AS valsal_doc_group_id,
                doc_item.id_doc_item AS valsal_doc_item_id,
                doc_package.id_doc_package AS valsal_doc_package_id,
                doc_package.status_package AS valsal_doc_package_status,
                doc_file.id_doc_file AS valsal_doc_file_id,
                doc_file.file_name AS valsal_doc_file_name,
                doc_file.file_path AS valsal_doc_file_path,
                doc_file.status_file AS valsal_doc_status,
                doc_file.is_document_not_required AS valsal_doc_not_required,
                doc_file.remark AS valsal_doc_remark,
                doc_file.approved_at AS valsal_doc_approved_at,
                doc_file.reviewed_at AS valsal_doc_reviewed_at,
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if ($this->valsalDocumentTablesReady()) {
            $this->db
                ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'VALSAL' AND doc_group.group_label = 'VALIDASI SALES' AND doc_group.is_active = 1", 'left', false)
                ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
                ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'VALSAL\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
                ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left');
        } else {
            $this->db->select("
                NULL AS valsal_doc_group_id,
                NULL AS valsal_doc_item_id,
                NULL AS valsal_doc_package_id,
                NULL AS valsal_doc_package_status,
                NULL AS valsal_doc_file_id,
                NULL AS valsal_doc_file_name,
                NULL AS valsal_doc_file_path,
                NULL AS valsal_doc_status,
                NULL AS valsal_doc_not_required,
                NULL AS valsal_doc_remark
            ", false);
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        if ($status !== '') {
            $normalizedStatus = strtoupper($status);
            if (in_array($normalizedStatus, ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'], true)) {
                $this->db->where('v.status_valsal', $normalizedStatus);
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

    public function getValsalCandidateById($clusterId)
    {
        if (!$this->valsalTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, b.id_bak, b.ba_open_date, b.bak_date, b.homepass_bak, b.status_bak, v.id_valsal')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where_in('UPPER(b.status_bak)', ['DONE', 'APPROVED'])
            ->get()
            ->row_array();
    }

    public function getValsalByClusterId($clusterId)
    {
        if (!$this->valsalTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, b.id_bak, b.bak_date, b.homepass_bak, b.status_bak, v.id_valsal, v.valsal_date, v.homepass_valsal, v.status_valsal, v.remark_valsal')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();
    }

    public function createValsal($clusterId, $valsalPayload, $clusterPayload)
    {
        $this->db->trans_start();

        $this->db->where('id_myrep_cluster', (int) $clusterId)->update('tb_myrep_cluster', $clusterPayload);
        $valsalPayload['id_myrep_cluster'] = (int) $clusterId;
        $this->db->insert('tb_myrep_valsal', $valsalPayload);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function deleteValsalByCluster($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        return $this->db->where('id_myrep_cluster', $clusterId)->delete('tb_myrep_valsal');
    }

    public function updateValsal($clusterId, $valsalPayload, $clusterPayload)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $existing = $this->getValsalByClusterId($clusterId);
        if (empty($existing)) {
            return false;
        }

        $clusterPayload['status_current'] = $this->resolveSafeCurrentStatus(
            (string) ($existing['status_current'] ?? ''),
            (string) ($clusterPayload['status_current'] ?? 'DRAFT')
        );

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);

        $valsal = $this->db
            ->get_where('tb_myrep_valsal', ['id_myrep_cluster' => $clusterId])
            ->row_array();

        if (!empty($valsal)) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_valsal', $valsalPayload);
        } else {
            $valsalPayload['id_myrep_cluster'] = $clusterId;
            $this->db->insert('tb_myrep_valsal', $valsalPayload);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function getValsalDocumentContext($clusterId)
    {
        if (!$this->valsalDocumentTablesReady()) {
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
            ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'VALSAL' AND doc_group.group_label = 'VALIDASI SALES' AND doc_group.is_active = 1", 'left', false)
            ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
            ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'VALSAL\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();
    }

    public function saveValsalFileUpload($clusterId, $data)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->valsalDocumentTablesReady()) {
            return 0;
        }

        $context = $this->getValsalDocumentContext($clusterId);
        if (empty($context['id_doc_group']) || empty($context['id_doc_item'])) {
            return 0;
        }

        $packageId = $this->ensurePackage($clusterId, 'VALSAL', (int) $context['id_doc_group'], (int) $data['uploaded_by']);
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

    public function updateValsalFileStatus($fileId, $data)
    {
        if (!$this->valsalDocumentTablesReady()) {
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

    public function getValsalFileById($fileId)
    {
        if (!$this->valsalDocumentTablesReady()) {
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

    public function getValsalFileLogs($fileId)
    {
        if (!$this->valsalDocumentTablesReady()) {
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

    public function updateValsalStatusByCluster($clusterId, $statusValsal, $statusCurrent, $userId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $existing = $this->getValsalByClusterId($clusterId);
        if (empty($existing)) {
            return false;
        }

        $statusValsal = strtoupper(trim((string) $statusValsal));
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
            ->update('tb_myrep_valsal', [
                'status_valsal' => $statusValsal,
                'updated_by' => $userId,
            ]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateClusterStatusOnly($clusterId, $statusCurrent, $userId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return false;
        }

        $cluster = $this->db->get_where('tb_myrep_cluster', [
            'id_myrep_cluster' => $clusterId,
        ])->row_array();

        if (empty($cluster)) {
            return false;
        }

        return $this->db
            ->where('id_myrep_cluster', $clusterId)
            ->update('tb_myrep_cluster', [
                'status_current' => $this->resolveSafeCurrentStatus(
                    (string) ($cluster['status_current'] ?? ''),
                    strtoupper(trim((string) $statusCurrent))
                ),
                'updated_by' => (int) $userId,
            ]);
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
