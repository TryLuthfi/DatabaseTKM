<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBatch_Approval_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;

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

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

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

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->where_in('UPPER(v.status_valsal)', ['DONE', 'APPROVED'])
            ->group_start()
                ->where('ba.id_batch_approval IS NOT NULL', null, false)
                ->or_where('UPPER(c.status_current)', 'VALSAL')
            ->group_end()
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

        $clusterLocationSelect = $this->buildClusterLocationSelect('c');

        $query = $this->db
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
                ' . $clusterLocationSelect . ',
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
            ->order_by('c.cluster_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $query->get()->result_array();
    }

    public function getBatchRows($city = '', $status = '')
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $clusterLocationSelect = $this->buildClusterLocationSelect('c');

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
                ' . $clusterLocationSelect . ',
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
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $this->db
            ->where_in('UPPER(v.status_valsal)', ['DONE', 'APPROVED'])
            ->group_start()
                ->where('ba.id_batch_approval IS NOT NULL', null, false)
                ->or_where('UPPER(c.status_current)', 'VALSAL')
            ->group_end();

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

        $rows = $this->db
            ->group_by('c.id_myrep_cluster')
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $summaryMap = $this->getPostDonasiSummaryMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $summary = $summaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ['total' => $this->getPostDonasiRequiredDocTotal(), 'uploaded' => 0, 'approved' => 0];
            $row['post_doc_total'] = $summary['total'];
            $row['post_doc_uploaded'] = $summary['uploaded'];
            $row['post_doc_approved'] = $summary['approved'];
            $row['display_staging_status'] = $this->resolveDisplayStagingStatus(
                (string) ($row['staging_status'] ?? 'DRAFT'),
                (int) $summary['total'],
                (int) $summary['approved']
            );
        }
        unset($row);

        if (in_array(strtoupper($status), ['WAITING DOC', 'COMPLETED'], true)) {
            $rows = array_values(array_filter($rows, static function ($row) use ($status) {
                return strtoupper((string) ($row['display_staging_status'] ?? '')) === strtoupper($status);
            }));
        }

        return $rows;
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

    public function getClusterForBatchImportById($clusterId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        return $this->db
            ->select('c.*, b.id_bak, b.status_bak, b.bak_date, v.id_valsal, v.status_valsal, v.valsal_date, ba.id_batch_approval')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();
    }

    public function getClusterForBatchImportByName($clusterName, $cityName = '', $targetId = 0)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $clusterName = strtoupper(trim((string) $clusterName));
        $cityName = strtoupper(trim((string) $cityName));
        $targetId = (int) $targetId;
        if ($clusterName === '') {
            return [];
        }

        $this->db
            ->select('c.*, b.id_bak, b.status_bak, b.bak_date, v.id_valsal, v.status_valsal, v.valsal_date, ba.id_batch_approval')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('UPPER(c.cluster_name)', $clusterName);

        if ($cityName !== '') {
            $this->db->where('UPPER(c.city_name)', $cityName);
        }

        if ($targetId > 0) {
            $this->db->where('c.id_target', $targetId);
        }

        return $this->db
            ->order_by('c.created_at', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function createClusterForBatchImport($targetId, $clusterName, $clusterCode, $homepassPlan, $userId)
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

    public function upsertBakDoneForBatchImport($clusterId, $homepassBak, $bakDate, $userId, $remarkBak = '')
    {
        $clusterId = (int) $clusterId;
        $homepassBak = (int) $homepassBak;
        $userId = (int) $userId;
        $bakDate = trim((string) $bakDate) !== '' ? (string) $bakDate : date('Y-m-d');
        $remarkBak = trim((string) $remarkBak);

        if ($clusterId <= 0 || $homepassBak <= 0 || !$this->db->table_exists('tb_myrep_bak')) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', [
            'hp_plan' => $homepassBak,
            'status_current' => 'BAK',
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        $payload = [
            'ba_open_date' => $bakDate,
            'bak_date' => $bakDate,
            'homepass_bak' => $homepassBak,
            'status_bak' => 'DONE',
            'remark_bak' => $remarkBak !== '' ? $remarkBak : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ];

        $existing = $this->db->get_where('tb_myrep_bak', ['id_myrep_cluster' => $clusterId])->row_array();
        if ($existing) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_bak', $payload);
        } else {
            $payload['id_myrep_cluster'] = $clusterId;
            $payload['created_by'] = $userId > 0 ? $userId : null;
            $this->db->insert('tb_myrep_bak', $payload);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function upsertValsalDoneForBatchImport($clusterId, $homepassValsal, $valsalDate, $userId, $remarkValsal = '')
    {
        $clusterId = (int) $clusterId;
        $homepassValsal = (int) $homepassValsal;
        $userId = (int) $userId;
        $valsalDate = trim((string) $valsalDate) !== '' ? (string) $valsalDate : date('Y-m-d');
        $remarkValsal = trim((string) $remarkValsal);

        if ($clusterId <= 0 || $homepassValsal <= 0) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', [
            'status_current' => 'VALSAL',
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        $payload = [
            'valsal_date' => $valsalDate,
            'homepass_valsal' => $homepassValsal,
            'status_valsal' => 'DONE',
            'remark_valsal' => $remarkValsal !== '' ? $remarkValsal : null,
            'updated_by' => $userId > 0 ? $userId : null,
        ];

        $existing = $this->db->get_where('tb_myrep_valsal', ['id_myrep_cluster' => $clusterId])->row_array();
        if ($existing) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_valsal', $payload);
        } else {
            $payload['id_myrep_cluster'] = $clusterId;
            $payload['created_by'] = $userId > 0 ? $userId : null;
            $this->db->insert('tb_myrep_valsal', $payload);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    
    public function getBatchByClusterId($clusterId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $clusterLocationSelect = $this->buildClusterLocationSelect('c');

        $row = $this->db
            ->select('c.*,' . $clusterLocationSelect . ', v.id_valsal, v.valsal_date, v.homepass_valsal, v.status_valsal, ba.*', false)
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        if (!$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return [];
        }

        $summaryMap = $this->getPostDonasiSummaryMap([(int) $clusterId]);
        $summary = $summaryMap[(int) $clusterId] ?? ['total' => $this->getPostDonasiRequiredDocTotal(), 'uploaded' => 0, 'approved' => 0];
        $row['post_doc_total'] = $summary['total'];
        $row['post_doc_uploaded'] = $summary['uploaded'];
        $row['post_doc_approved'] = $summary['approved'];
        $row['display_staging_status'] = $this->resolveDisplayStagingStatus(
            (string) ($row['staging_status'] ?? 'DRAFT'),
            (int) $summary['total'],
            (int) $summary['approved']
        );

        return $row;
    }

    private function buildClusterLocationSelect($alias = 'c')
    {
        $clusterTable = 'tb_myrep_cluster';
        $alias = trim((string) $alias) !== '' ? trim((string) $alias) : 'c';
        $fields = [
            'district_name',
            'village_name',
        ];

        $selectParts = [];
        foreach ($fields as $field) {
            if ($this->db->field_exists($field, $clusterTable)) {
                $selectParts[] = $alias . '.' . $field;
            } else {
                $selectParts[] = 'NULL AS ' . $field;
            }
        }

        return implode(",\n                ", $selectParts);
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
        $cluster = $this->getBatchByClusterId((int) $clusterId);
        if (empty($cluster['id_myrep_cluster'])) {
            return 0;
        }

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

        $cluster = $this->db
            ->select('city_name')
            ->from('tb_myrep_cluster')
            ->where('id_myrep_cluster', (int) ($batch['id_myrep_cluster'] ?? 0))
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($cluster) || !$this->isCityAllowedForCurrentUser((string) ($cluster['city_name'] ?? ''))) {
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

        $row = $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.city_name,
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

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getAutoLinkedSupportDocumentMap($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->batchDocumentTablesReady()) {
            return [];
        }

        $map = $this->getAutoLinkedSupportDocumentMapByClusterIds([$clusterId]);
        return $map[$clusterId] ?? [];
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

    public function getBatchFileById($fileId)
    {
        if (!$this->batchDocumentTablesReady()) {
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

    public function getBatchFileLogs($fileId)
    {
        if (!$this->batchDocumentTablesReady()) {
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

    public function getBatchFileByClusterId($clusterId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
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

    private function getPostDonasiRequiredDocTotal()
    {
        if (!$this->batchDocumentTablesReady()) {
            return 0;
        }

        return (int) $this->db
            ->from('md_myrep_flow_doc_group g')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->where('g.flow_type', 'POST_DONASI')
            ->where('g.is_active', 1)
            ->count_all_results();
    }

    private function getPostDonasiSummaryMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->batchDocumentTablesReady()) {
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
            ->where_in('c.id_myrep_cluster', $clusterIds)
            ->order_by('i.sort_no', 'ASC')
            ->get()
            ->result_array();

        $total = $this->getPostDonasiRequiredDocTotal();
        $map = [];
        foreach ($clusterIds as $clusterId) {
            $map[$clusterId] = [
                'total' => $total,
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

            if ($hasActualFile || $hasLinkedFile) {
                $map[$clusterId]['uploaded']++;
            }

            if ($hasActualFile) {
                if ($actualStatus === 'APPROVED') {
                    $map[$clusterId]['approved']++;
                }
                continue;
            }

            if ($hasLinkedFile) {
                $map[$clusterId]['approved']++;
            }
        }

        return $map;
    }

    private function getAutoLinkedSupportDocumentMapByClusterIds($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->batchDocumentTablesReady()) {
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
                doc_file.status_file,
                doc_file.reviewed_at,
                doc_file.approved_at
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
                $sourceFlowType = strtoupper(trim((string) ($sourceRow['flow_type'] ?? '')));
                $previewPath = '';

                if ($sourceFlowType === 'BAK') {
                    $previewPath = 'BAK_MyRep/previewDocument/' . (int) $sourceRow['id_doc_file'];
                } elseif ($sourceFlowType === 'VALSAL') {
                    $previewPath = 'VALSAL_MyRep/previewDocument/' . (int) $sourceRow['id_doc_file'];
                }

                $result[$clusterId][$postDocName] = [
                    'linked_source_flow_type' => $sourceFlowType,
                    'linked_source_group_label' => (string) ($sourceRow['group_label'] ?? ''),
                    'linked_source_doc_name' => (string) ($sourceRow['doc_name'] ?? ''),
                    'linked_source_file_id' => (int) $sourceRow['id_doc_file'],
                    'linked_source_file_name' => (string) ($sourceRow['file_name'] ?? ''),
                    'linked_source_file_path' => (string) ($sourceRow['file_path'] ?? ''),
                    'linked_source_status' => (string) ($sourceRow['status_file'] ?? ''),
                    'linked_source_preview_path' => $previewPath,
                ];
            }
        }

        return $result;
    }

    private function applyAllowedCityRestriction($columnName = 'c.city_name')
    {
        if (!$this->shouldRestrictCityByUser()) {
            return true;
        }

        $allowedCitySet = $this->getCurrentUserAllowedCitySet();
        if (empty($allowedCitySet)) {
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
        $params = [];
        foreach ($existingRoleColumns as $columnName) {
            $whereParts[] = '`' . $columnName . '` = ?';
            $params[] = $nik;
        }

        $sql = 'SELECT city_name FROM tb_myrep_pic_mapping_city WHERE ';
        if ($this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) {
            $sql .= 'is_active = 1 AND ';
        }
        $sql .= '(' . implode(' OR ', $whereParts) . ')';

        $rows = (array) $this->db->query($sql, $params)->result_array();
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

        return (string) $this->session->userdata('nama_level') !== 'Super Admin';
    }

    private function resolveDisplayStagingStatus($stagingStatus, $postDocTotal, $postDocApproved)
    {
        $stagingStatus = strtoupper(trim((string) $stagingStatus));
        $postDocTotal = (int) $postDocTotal;
        $postDocApproved = (int) $postDocApproved;

        if (in_array($stagingStatus, ['RELEASED', 'DONE BATCH APPROVAL'], true) && $postDocTotal > 0) {
            return $postDocApproved >= $postDocTotal ? 'COMPLETED' : 'WAITING DOC';
        }

        return $stagingStatus !== '' ? $stagingStatus : 'DRAFT';
    }
}


