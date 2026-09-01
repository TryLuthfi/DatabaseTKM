<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MBatch_Approval_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;
    /** @var string */
    private $lastErrorMessage = '';
    /** @var array<string,array<string,bool>> */
    private $tableFieldSetCache = [];
    private $cityPicApprovalColumn = 'sitac_ho';
    private $cityPicApprovalNameCache = [];
    private $masterUserNameByNikCache = [];

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
    private $donationDocGroups = [
        'PRE_ZEYN' => 'PRE ZEYN DOCUMENT',
        'POST_ZEYN' => 'POST PAYMENT ZEYN DOCUMENT',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureFinanceApprovalSchema();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    private function ensureFinanceApprovalSchema()
    {
        if ($this->db->table_exists('tb_myrep_flow_doc_file')) {
            $addedFinanceStatus = $this->addColumnIfMissing('tb_myrep_flow_doc_file', 'finance_status', "ALTER TABLE `tb_myrep_flow_doc_file` ADD COLUMN `finance_status` VARCHAR(50) NOT NULL DEFAULT 'NY' AFTER `status_file`");
            $this->addColumnIfMissing('tb_myrep_flow_doc_file', 'finance_remark', "ALTER TABLE `tb_myrep_flow_doc_file` ADD COLUMN `finance_remark` TEXT NULL AFTER `finance_status`");
            $this->addColumnIfMissing('tb_myrep_flow_doc_file', 'finance_reviewed_at', "ALTER TABLE `tb_myrep_flow_doc_file` ADD COLUMN `finance_reviewed_at` DATETIME NULL AFTER `finance_remark`");
            $this->addColumnIfMissing('tb_myrep_flow_doc_file', 'finance_approved_by', "ALTER TABLE `tb_myrep_flow_doc_file` ADD COLUMN `finance_approved_by` INT(11) NULL AFTER `finance_reviewed_at`");
            $this->addColumnIfMissing('tb_myrep_flow_doc_file', 'finance_approved_at', "ALTER TABLE `tb_myrep_flow_doc_file` ADD COLUMN `finance_approved_at` DATETIME NULL AFTER `finance_approved_by`");
            if ($addedFinanceStatus) {
                $this->backfillExistingSitacApprovedFinanceStatus();
            }
        }

        if ($this->db->table_exists('tb_myrep_pic_mapping_city')) {
            $this->addColumnIfMissing('tb_myrep_pic_mapping_city', 'finance_ho', "ALTER TABLE `tb_myrep_pic_mapping_city` ADD COLUMN `finance_ho` VARCHAR(255) NULL AFTER `sitac_ho`");
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

    public function getLastErrorMessage()
    {
        return (string) $this->lastErrorMessage;
    }

    public function getBatchClusterReviewPicMap(array $clusterRows)
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $map = [];
        foreach ($clusterRows as $clusterRow) {
            $clusterId = (int) ($clusterRow['id_myrep_cluster'] ?? 0);
            if ($clusterId <= 0 || isset($map[$clusterId])) {
                continue;
            }

            $map[$clusterId] = $this->getCityPicApprovalName(
                (string) ($clusterRow['city_name'] ?? ''),
                (string) ($clusterRow['province_name'] ?? ''),
                (string) ($clusterRow['regional_name'] ?? '')
            );
        }

        return $map;
    }

    private function getValsalApprovedAtSubquery()
    {
        $escapedNames = array_map(function ($docName) {
            return 'CONVERT(' . $this->db->escape($docName) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci';
        }, $this->getDefaultValsalDocumentNamesForSla());
        return "(SELECT MAX(valsal_doc_file.approved_at)
            FROM tb_myrep_flow_doc_package valsal_doc_package
            JOIN md_myrep_flow_doc_group valsal_doc_group ON valsal_doc_group.id_doc_group = valsal_doc_package.id_doc_group
            JOIN md_myrep_flow_doc_item valsal_doc_item ON valsal_doc_item.id_doc_group = valsal_doc_group.id_doc_group
            JOIN tb_myrep_flow_doc_file valsal_doc_file ON valsal_doc_file.id_doc_package = valsal_doc_package.id_doc_package AND valsal_doc_file.id_doc_item = valsal_doc_item.id_doc_item
            WHERE valsal_doc_package.id_myrep_cluster = c.id_myrep_cluster
                AND valsal_doc_package.flow_type = 'VALSAL'
                AND valsal_doc_group.flow_type = 'VALSAL'
                AND valsal_doc_group.group_label = 'VALIDASI SALES'
                AND valsal_doc_file.status_file = 'APPROVED'
                AND CONVERT(valsal_doc_item.doc_name USING utf8mb4) COLLATE utf8mb4_unicode_ci IN (" . implode(',', $escapedNames) . ")) AS valsal_approved_at";
    }

    private function getDefaultValsalDocumentNamesForSla()
    {
        return ['SND Kasar', 'Form SND', 'Boundary KMZ'];
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
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
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
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
            ->where('UPPER(c.status_current)', 'VALSAL')
            ->where('ba.id_batch_approval IS NULL', null, false)
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $query->get()->result_array();
    }

    public function getBatchRows($city = '', $status = '', $regional = '', array $cityList = [], array $regionalList = [], $submissionDateStart = '', $submissionDateEnd = '')
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
                t.year_num,
                t.month_num
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left');

        $optionalBatchColumns = [
            'astri_initial_submitted_at',
            'astri_batch_approved_at',
            'hold_at',
            'hold_remark',
            'rejected_at',
            'rejected_remark',
            'pre_zeyn_doc_approved_at',
            'finance_submitted_at',
            'post_zeyn_doc_approved_at',
            'final_astri_submitted_at',
            'final_astri_approved_at',
            'po_donasi_number',
            'po_donasi_date',
            'po_donasi_value',
            'po_donasi_status',
            'invoice_donasi_number',
            'invoice_donasi_date',
            'invoice_donasi_value',
            'invoice_donasi_status',
            'invoice_donasi_remark',
        ];
        foreach ($optionalBatchColumns as $optionalBatchColumn) {
            if ($this->tableHasField('tb_myrep_batch_approval', $optionalBatchColumn)) {
                $this->db->select('ba.' . $optionalBatchColumn);
            } else {
                $this->db->select('NULL AS ' . $optionalBatchColumn, false);
            }
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $this->db
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
            ->group_start()
                ->where('ba.id_batch_approval IS NOT NULL', null, false)
                ->or_where('UPPER(c.status_current)', 'VALSAL')
            ->group_end();

        if ($this->batchDocumentTablesReady()) {
            $this->db->select("
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
                doc_file.uploaded_by AS batch_doc_uploaded_by,
                u_upload.nama_karyawan AS batch_doc_uploaded_by_name,
                doc_file.approved_at AS batch_doc_approved_at,
                doc_file.reviewed_at AS batch_doc_reviewed_at
            ", false);
            $this->db->select($this->getValsalApprovedAtSubquery(), false);
            $this->db
                ->join("md_myrep_flow_doc_group doc_group", "doc_group.flow_type = 'BATCH_APPROVAL' AND doc_group.group_label = 'RAR' AND doc_group.is_active = 1", 'left', false)
                ->join('md_myrep_flow_doc_item doc_item', 'doc_item.id_doc_group = doc_group.id_doc_group AND doc_item.is_active = 1', 'left')
                ->join('tb_myrep_flow_doc_package doc_package', 'doc_package.id_myrep_cluster = c.id_myrep_cluster AND doc_package.flow_type = \'BATCH_APPROVAL\' AND doc_package.id_doc_group = doc_group.id_doc_group', 'left', false)
                ->join('tb_myrep_flow_doc_file doc_file', 'doc_file.id_doc_package = doc_package.id_doc_package AND doc_file.id_doc_item = doc_item.id_doc_item', 'left')
                ->join('tb_master_user_new u_upload', 'u_upload.id = doc_file.uploaded_by', 'left');
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
                NULL AS batch_doc_remark,
                NULL AS batch_doc_uploaded_by,
                NULL AS batch_doc_uploaded_by_name,
                NULL AS batch_doc_approved_at,
                NULL AS batch_doc_reviewed_at,
                NULL AS valsal_approved_at
            ", false);
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
                $this->db->where($this->collatedUpperInSql('c.city_name', $normalizedCities), null, false);
            }
        }
        if (!empty($regionalList)) {
            $normalizedRegionals = array_values(array_unique(array_filter(array_map(static function ($value) {
                return strtoupper(trim((string) $value));
            }, $regionalList))));
            if (!empty($normalizedRegionals)) {
                $this->db->where($this->collatedUpperInSql('c.regional_name', $normalizedRegionals), null, false);
            }
        }
        if ($submissionDateStart !== '') {
            $this->db->where('ba.submission_date >=', $submissionDateStart);
        }
        if ($submissionDateEnd !== '') {
            $this->db->where('ba.submission_date <=', $submissionDateEnd);
        }

        if ($status !== '') {
            $normalizedStatus = strtoupper($status);
            $displayOnlyStatuses = ['WAITING DOC', 'COMPLETED', 'WAITING_PRE_ZEYN_DOC', 'WAITING_POST_ZEYN_DOC'];
            if (in_array($normalizedStatus, $displayOnlyStatuses, true)) {
                // Filtered after document summaries are calculated.
            } elseif (in_array($normalizedStatus, [
                'DRAFT',
                'WAITING HO',
                'WAITING MYREP',
                'WAITING FINANCE',
                'WAITING_BATCH_APPROVAL',
                'BATCH_APPROVED',
                'HOLD',
                'PRE_ZEYN_DOC_APPROVED',
                'WAITING_FINANCE_RELEASE',
                'RELEASED',
                'POST_ZEYN_DOC_APPROVED',
                'WAITING_ASTRI_SUBMISSION',
                'ASTRI_ON_REVIEW',
                'ASTRI_APPROVED',
                'PO_DONASI',
                'INVOICE',
                'DONE BATCH APPROVAL',
                'REJECTED',
            ], true)) {
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
        $donationSummaryMap = $this->getDonationDocumentSummaryMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $summary = $summaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ['total' => $this->getPostDonasiRequiredDocTotal(), 'uploaded' => 0, 'approved' => 0];
            $donationSummary = $donationSummaryMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? $this->buildEmptyDonationDocumentSummary();
            $row['staging_status'] = $this->resolveStoredBatchStagingStatus($row);
            $row['post_doc_total'] = $summary['total'];
            $row['post_doc_uploaded'] = $summary['uploaded'];
            $row['post_doc_approved'] = $summary['approved'];
            $row['pre_zeyn_doc_total'] = $donationSummary['PRE_ZEYN']['required'];
            $row['pre_zeyn_doc_uploaded'] = $donationSummary['PRE_ZEYN']['uploaded'];
            $row['pre_zeyn_doc_approved'] = $donationSummary['PRE_ZEYN']['approved'];
            $row['pre_zeyn_finance_required'] = $donationSummary['PRE_ZEYN']['finance_required'];
            $row['pre_zeyn_finance_approved'] = $donationSummary['PRE_ZEYN']['finance_approved'];
            $row['post_zeyn_doc_total'] = $donationSummary['POST_ZEYN']['required'];
            $row['post_zeyn_doc_uploaded'] = $donationSummary['POST_ZEYN']['uploaded'];
            $row['post_zeyn_doc_approved'] = $donationSummary['POST_ZEYN']['approved'];
            $row['post_zeyn_finance_required'] = $donationSummary['POST_ZEYN']['finance_required'];
            $row['post_zeyn_finance_approved'] = $donationSummary['POST_ZEYN']['finance_approved'];
            $row['astri_final_total'] = $donationSummary['POST_ZEYN']['required'];
            $row['astri_final_submitted'] = $donationSummary['POST_ZEYN']['astri_submitted'];
            $row['astri_final_approved'] = $donationSummary['POST_ZEYN']['astri_approved'];
            $row['display_staging_status'] = $this->resolveDisplayStagingStatus(
                (string) $row['staging_status'],
                (int) $summary['total'],
                (int) $summary['approved'],
                $donationSummary
            );
        }
        unset($row);

        if (in_array(strtoupper($status), ['WAITING DOC', 'COMPLETED', 'WAITING_PRE_ZEYN_DOC', 'WAITING_POST_ZEYN_DOC'], true)) {
            $rows = array_values(array_filter($rows, static function ($row) use ($status) {
                return strtoupper((string) ($row['display_staging_status'] ?? '')) === strtoupper($status);
            }));
        }

        return $rows;
    }

    public function getRegionalOptions()
    {
        if (!$this->batchTablesReady()) {
            return [];
        }
        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.regional_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
            ->group_start()
                ->where('ba.id_batch_approval IS NOT NULL', null, false)
                ->or_where('UPPER(c.status_current)', 'VALSAL')
            ->group_end()
            ->where('c.regional_name IS NOT NULL', null, false)
            ->where("TRIM(c.regional_name) !=", '')
            ->order_by('c.regional_name', 'ASC')
            ->get()
            ->result_array();

        $regionals = [];
        foreach ($rows as $row) {
            $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regionalName !== '') $regionals[] = $regionalName;
        }
        return array_values(array_unique($regionals));
    }

    public function getCityOptionsByRegional()
    {
        if (!$this->batchTablesReady()) return [];
        if (!$this->applyAllowedCityRestriction('c.city_name')) return [];

        $rows = $this->db
            ->distinct()
            ->select('c.regional_name, c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
            ->group_start()
                ->where('ba.id_batch_approval IS NOT NULL', null, false)
                ->or_where('UPPER(c.status_current)', 'VALSAL')
            ->group_end()
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

    public function getBatchCandidateById($clusterId)
    {
        if (!$this->batchTablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, v.id_valsal, v.valsal_date, v.homepass_valsal, v.status_valsal, ba.id_batch_approval')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'inner')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->where($this->collatedUpperInSql('v.status_valsal', ['DONE', 'APPROVED']), null, false)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
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
        $donationSummary = $this->getDonationDocumentSummary((int) $clusterId);
        $row['staging_status'] = $this->resolveStoredBatchStagingStatus($row);
        $row['post_doc_total'] = $summary['total'];
        $row['post_doc_uploaded'] = $summary['uploaded'];
        $row['post_doc_approved'] = $summary['approved'];
        $row['pre_zeyn_doc_total'] = $donationSummary['PRE_ZEYN']['required'];
        $row['pre_zeyn_doc_uploaded'] = $donationSummary['PRE_ZEYN']['uploaded'];
        $row['pre_zeyn_doc_approved'] = $donationSummary['PRE_ZEYN']['approved'];
        $row['pre_zeyn_finance_required'] = $donationSummary['PRE_ZEYN']['finance_required'];
        $row['pre_zeyn_finance_approved'] = $donationSummary['PRE_ZEYN']['finance_approved'];
        $row['post_zeyn_doc_total'] = $donationSummary['POST_ZEYN']['required'];
        $row['post_zeyn_doc_uploaded'] = $donationSummary['POST_ZEYN']['uploaded'];
        $row['post_zeyn_doc_approved'] = $donationSummary['POST_ZEYN']['approved'];
        $row['post_zeyn_finance_required'] = $donationSummary['POST_ZEYN']['finance_required'];
        $row['post_zeyn_finance_approved'] = $donationSummary['POST_ZEYN']['finance_approved'];
        $row['astri_final_total'] = $donationSummary['POST_ZEYN']['required'];
        $row['astri_final_submitted'] = $donationSummary['POST_ZEYN']['astri_submitted'];
        $row['astri_final_approved'] = $donationSummary['POST_ZEYN']['astri_approved'];
        $row['display_staging_status'] = $this->resolveDisplayStagingStatus(
            (string) $row['staging_status'],
            (int) $summary['total'],
            (int) $summary['approved'],
            $donationSummary
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

    public function createBatchApproval($clusterId, $batchPayload, $clusterPayload, $pics = [], $clusterContext = [])
    {
        $this->lastErrorMessage = '';
        $clusterId = (int) $clusterId;
        $clusterContext = (array) $clusterContext;

        if (!empty($clusterContext['id_myrep_cluster']) && (int) $clusterContext['id_myrep_cluster'] === $clusterId) {
            $cluster = $clusterContext;
        } else {
            $cluster = $this->getBatchByClusterId($clusterId);
        }

        if (empty($cluster['id_myrep_cluster'])) {
            $this->setLastError('Cluster tidak ditemukan atau tidak termasuk city mapping user.');
            return 0;
        }

        if (!empty($cluster['id_batch_approval'])) {
            $this->setLastError('Cluster sudah memiliki data Batch Approval.');
            return 0;
        }

        $clusterPayload = $this->sanitizePayloadForTable('tb_myrep_cluster', (array) $clusterPayload);
        $batchPayload = $this->sanitizePayloadForTable('tb_myrep_batch_approval', (array) $batchPayload);
        if (trim((string) ($batchPayload['staging_status'] ?? '')) === '') {
            $batchPayload['staging_status'] = 'BATCH_APPROVED';
        }
        if ($this->tableHasField('tb_myrep_batch_approval', 'id_myrep_cluster')) {
            $batchPayload['id_myrep_cluster'] = $clusterId;
        } else {
            $this->setLastError('Kolom id_myrep_cluster tidak ditemukan pada tb_myrep_batch_approval.');
            return 0;
        }

        $this->db->trans_begin();

        if (!empty($clusterPayload)) {
            $this->db->where('id_myrep_cluster', $clusterId)->update('tb_myrep_cluster', $clusterPayload);
            if ($this->hasDbError('update tb_myrep_cluster saat create batch approval')) {
                $this->db->trans_rollback();
                return 0;
            }
        }

        $this->db->insert('tb_myrep_batch_approval', $batchPayload);
        if ($this->hasDbError('insert tb_myrep_batch_approval')) {
            $this->db->trans_rollback();
            return 0;
        }

        $batchId = (int) $this->db->insert_id();
        if ($batchId <= 0) {
            $this->setLastError('Insert tb_myrep_batch_approval tidak menghasilkan ID baru.');
            $this->db->trans_rollback();
            return 0;
        }

        foreach ((array) $pics as $index => $picRow) {
            $pic = $this->sanitizePayloadForTable('tb_myrep_batch_approval_pic', (array) $picRow);
            if ($this->tableHasField('tb_myrep_batch_approval_pic', 'id_batch_approval')) {
                $pic['id_batch_approval'] = $batchId;
            }
            if ($this->tableHasField('tb_myrep_batch_approval_pic', 'pic_no') && !isset($pic['pic_no'])) {
                $pic['pic_no'] = ((int) $index) + 1;
            }

            if (empty($pic)) {
                continue;
            }

            $this->db->insert('tb_myrep_batch_approval_pic', $pic);
            if ($this->hasDbError('insert tb_myrep_batch_approval_pic')) {
                $this->db->trans_rollback();
                return 0;
            }
        }

        if (!$this->db->trans_status()) {
            $this->setLastError('Transaksi Batch Approval gagal saat commit.');
            $this->db->trans_rollback();
            return 0;
        }

        $this->db->trans_commit();
        return $batchId;
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

    public function deleteBatchApprovalOnly($clusterId)
    {
        $this->lastErrorMessage = '';
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->batchTablesReady()) {
            $this->setLastError('Data Batch Approval tidak valid.');
            return false;
        }

        $batch = $this->getBatchByClusterId($clusterId);
        if (empty($batch['id_batch_approval'])) {
            $this->setLastError('Data Batch Approval tidak ditemukan atau tidak termasuk city mapping user.');
            return false;
        }

        $batchId = (int) $batch['id_batch_approval'];
        $packageIds = [];
        $fileIds = [];
        $pathsToDelete = [];

        if ($this->db->table_exists('tb_myrep_flow_doc_package')) {
            $packageRows = $this->db
                ->select('id_doc_package')
                ->from('tb_myrep_flow_doc_package')
                ->where('id_myrep_cluster', $clusterId)
                ->where('flow_type', 'BATCH_APPROVAL')
                ->get()
                ->result_array();

            foreach ($packageRows as $row) {
                $packageId = (int) ($row['id_doc_package'] ?? 0);
                if ($packageId > 0) {
                    $packageIds[] = $packageId;
                }
            }
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_flow_doc_file')) {
            $fileRows = $this->db
                ->select('id_doc_file, file_path')
                ->from('tb_myrep_flow_doc_file')
                ->where_in('id_doc_package', $packageIds)
                ->get()
                ->result_array();

            foreach ($fileRows as $row) {
                $fileId = (int) ($row['id_doc_file'] ?? 0);
                if ($fileId > 0) {
                    $fileIds[] = $fileId;
                }

                $filePath = trim((string) ($row['file_path'] ?? ''));
                if ($filePath !== '') {
                    $pathsToDelete[] = $filePath;
                }
            }
        }

        $this->db->trans_begin();

        if (!empty($fileIds) && $this->db->table_exists('tb_myrep_flow_doc_file_log')) {
            if ($this->tableHasField('tb_myrep_flow_doc_file_log', 'id_doc_file')) {
                $this->db->where_in('id_doc_file', $fileIds)->delete('tb_myrep_flow_doc_file_log');
            } elseif ($this->tableHasField('tb_myrep_flow_doc_file_log', 'id_doc_package')) {
                $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_file_log');
            }
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_flow_doc_file')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_file');
        }

        if (!empty($packageIds) && $this->db->table_exists('tb_myrep_flow_doc_package')) {
            $this->db->where_in('id_doc_package', $packageIds)->delete('tb_myrep_flow_doc_package');
        }

        $this->db->where('id_batch_approval', $batchId)->delete('tb_myrep_batch_approval_pic');
        $this->db->where('id_batch_approval', $batchId)->delete('tb_myrep_batch_approval');

        if (!$this->db->trans_status()) {
            $this->setLastError('Transaksi hapus Batch Approval gagal.');
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        foreach (array_unique($pathsToDelete) as $filePath) {
            $this->deletePhysicalFile($filePath);
        }

        return true;
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
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'finance_status')) {
            $payload['finance_status'] = 'NY';
            $payload['finance_remark'] = null;
            $payload['finance_reviewed_at'] = null;
            $payload['finance_approved_by'] = null;
            $payload['finance_approved_at'] = null;
        }

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

    public function getDonationDocumentRows($clusterId, $groupKey = '')
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return [];
        }

        $groupLabels = $this->donationDocGroups;
        $normalizedGroupKey = strtoupper(trim((string) $groupKey));
        if ($normalizedGroupKey !== '' && isset($groupLabels[$normalizedGroupKey])) {
            $groupLabels = [$normalizedGroupKey => $groupLabels[$normalizedGroupKey]];
        }

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $select = '
            g.group_label,
            i.id_doc_group,
            i.id_doc_item,
            i.doc_name,
            i.doc_requirement_note,
            i.is_required,
            p.id_doc_package,
            p.status_package,
            f.id_doc_file,
            f.file_name,
            f.file_path,
            f.status_file,
            f.is_document_not_required,
            f.remark,
            f.uploaded_by,
            f.uploaded_at,
            f.reviewed_at,
            f.approved_by,
            f.approved_at
        ';
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'finance_status')) {
            $select .= ',
                f.finance_status,
                f.finance_remark,
                f.finance_reviewed_at,
                f.finance_approved_by,
                f.finance_approved_at
            ';
        } else {
            $select .= ",
                'NY' AS finance_status,
                NULL AS finance_remark,
                NULL AS finance_reviewed_at,
                NULL AS finance_approved_by,
                NULL AS finance_approved_at
            ";
        }
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_status')) {
            $select .= ',
                f.astri_submitted_date,
                ' . ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_approved_date') ? 'f.astri_approved_date' : 'NULL AS astri_approved_date') . ',
                f.astri_status,
                f.astri_status_updated_at,
                f.astri_remark
            ';
        } else {
            $select .= ",
                NULL AS astri_submitted_date,
                NULL AS astri_approved_date,
                'NY' AS astri_status,
                NULL AS astri_status_updated_at,
                NULL AS astri_remark
            ";
        }

        return $this->db
            ->select($select, false)
            ->from('tb_myrep_cluster c')
            ->join('md_myrep_flow_doc_group g', "g.flow_type = 'BATCH_APPROVAL' AND g.is_active = 1", 'inner', false)
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package p', 'p.id_myrep_cluster = c.id_myrep_cluster AND p.flow_type = \'BATCH_APPROVAL\' AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('c.id_myrep_cluster', $clusterId)
            ->where_in('g.group_label', array_values($groupLabels))
            ->order_by('g.sort_no', 'ASC')
            ->order_by('i.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getDonationDocumentDetail($clusterId, $docItemId)
    {
        $rows = $this->getDonationDocumentRows((int) $clusterId);
        foreach ($rows as $row) {
            if ((int) ($row['id_doc_item'] ?? 0) === (int) $docItemId) {
                return $row;
            }
        }
        return [];
    }

    public function getDonationFileContext($fileId)
    {
        if (!$this->batchDocumentTablesReady()) {
            return [];
        }

        $groupLabels = array_values($this->donationDocGroups);
        return $this->db
            ->select('f.*, p.id_myrep_cluster, p.id_doc_group, g.group_label, i.doc_name, i.is_required, c.city_name')
            ->from('tb_myrep_flow_doc_file f')
            ->join('tb_myrep_flow_doc_package p', 'p.id_doc_package = f.id_doc_package', 'inner')
            ->join('md_myrep_flow_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->where('p.flow_type', 'BATCH_APPROVAL')
            ->where_in('g.group_label', $groupLabels)
            ->get()
            ->row_array() ?: [];
    }

    public function saveDonationFileUpload($clusterId, $docItemId, array $data)
    {
        if (!$this->batchDocumentTablesReady()) {
            return 0;
        }

        $context = $this->getDonationDocumentDetail((int) $clusterId, (int) $docItemId);
        if (empty($context['id_doc_group']) || empty($context['id_doc_item'])) {
            return 0;
        }

        $packageId = $this->ensurePackage((int) $clusterId, 'BATCH_APPROVAL', (int) $context['id_doc_group'], (int) $data['uploaded_by']);
        if ($packageId <= 0) {
            return 0;
        }

        $existing = $this->db->get_where('tb_myrep_flow_doc_file', [
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $docItemId,
        ])->row_array();

        $incomingFileName = (string) ($data['file_name'] ?? '');
        $incomingFilePath = (string) ($data['file_path'] ?? '');
        $preserveExistingFile = !empty($data['preserve_existing_file']) && $incomingFileName === '' && $incomingFilePath === '' && $existing;

        $payload = [
            'file_name' => $preserveExistingFile ? (string) ($existing['file_name'] ?? '') : $incomingFileName,
            'file_path' => $preserveExistingFile ? (string) ($existing['file_path'] ?? '') : $incomingFilePath,
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => (string) $data['status_file'],
            'remark' => (string) ($data['remark'] ?? ''),
            'uploaded_by' => (int) $data['uploaded_by'],
            'uploaded_at' => !empty($data['uploaded_at']) ? (string) $data['uploaded_at'] : date('Y-m-d H:i:s'),
            'approved_by' => null,
            'reviewed_at' => null,
            'approved_at' => null,
        ];
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'finance_status')) {
            $payload['finance_status'] = 'NY';
            $payload['finance_remark'] = null;
            $payload['finance_reviewed_at'] = null;
            $payload['finance_approved_by'] = null;
            $payload['finance_approved_at'] = null;
        }
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_status')) {
            $payload['astri_status'] = 'NY';
            $payload['astri_submitted_date'] = null;
            if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_approved_date')) {
                $payload['astri_approved_date'] = null;
            }
            $payload['astri_status_updated_at'] = null;
            $payload['astri_remark'] = null;
        }

        if ($existing) {
            if (!$preserveExistingFile) {
                $this->deletePhysicalFile($existing['file_path'] ?? '');
            }
            $this->db->where('id_doc_file', (int) $existing['id_doc_file'])->update('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $actionType = $preserveExistingFile ? 'STATUS_IMPORT' : 'REUPLOAD';
        } else {
            $payload['id_doc_package'] = $packageId;
            $payload['id_doc_item'] = (int) $docItemId;
            $this->db->insert('tb_myrep_flow_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $actionType = 'UPLOAD';
        }

        if ($fileId <= 0) {
            return 0;
        }

        $this->createFileLog([
            'id_doc_file' => $fileId,
            'id_doc_package' => $packageId,
            'id_doc_item' => (int) $docItemId,
            'action_type' => $actionType,
            'status_after' => (string) $data['status_file'],
            'file_name' => (string) ($payload['file_name'] ?? ''),
            'remark' => (string) ($data['remark'] ?? ''),
            'action_by' => (int) $data['uploaded_by'],
        ]);

        $this->refreshPackageStatus($packageId);
        return $fileId;
    }

    public function approveAllDonationDocuments($clusterId, $groupKey, $userId, $remark = '')
    {
        $rows = $this->getDonationDocumentRows((int) $clusterId, (string) $groupKey);
        $updated = 0;
        foreach ($rows as $row) {
            $fileId = (int) ($row['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
            if ($fileId <= 0 || $status !== 'UPLOADED') {
                continue;
            }
            if ($this->updateBatchFileStatus($fileId, [
                'status_file' => 'APPROVED',
                'remark' => (string) $remark,
                'approved_by' => (int) $userId,
            ])) {
                $updated++;
            }
        }

        return $updated;
    }

    public function updateDonationFinanceStatus($fileId, $status, $userId, $remark = '', $reviewedAt = null)
    {
        if (!$this->batchDocumentTablesReady() || !$this->tableHasField('tb_myrep_flow_doc_file', 'finance_status')) {
            return false;
        }

        $file = $this->getDonationFileContext((int) $fileId);
        if (empty($file) || (int) ($file['is_required'] ?? 1) !== 1) {
            return false;
        }

        $status = strtoupper(trim((string) $status));
        if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
            return false;
        }

        if (strtoupper(trim((string) ($file['status_file'] ?? ''))) !== 'APPROVED') {
            return false;
        }

        $now = $reviewedAt ?: date('Y-m-d H:i:s');
        $payload = [
            'finance_status' => $status,
            'finance_remark' => (string) $remark,
            'finance_reviewed_at' => $now,
            'finance_approved_by' => (int) $userId,
            'finance_approved_at' => $status === 'APPROVED' ? $now : null,
        ];
        if ($status === 'REJECTED') {
            $payload['status_file'] = 'REJECTED';
            $payload['remark'] = (string) $remark;
            $payload['approved_by'] = null;
            $payload['approved_at'] = null;
            $payload['reviewed_at'] = $now;
            if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_status')) {
                $payload['astri_status'] = 'NY';
                $payload['astri_submitted_date'] = null;
                if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_approved_date')) {
                    $payload['astri_approved_date'] = null;
                }
                $payload['astri_status_updated_at'] = null;
                $payload['astri_remark'] = null;
            }
        }

        $result = $this->db
            ->where('id_doc_file', (int) $fileId)
            ->update('tb_myrep_flow_doc_file', $payload);

        if ($result) {
            $this->createFileLog([
                'id_doc_file' => (int) $fileId,
                'id_doc_package' => (int) ($file['id_doc_package'] ?? 0),
                'id_doc_item' => (int) ($file['id_doc_item'] ?? 0),
                'action_type' => $status === 'APPROVED' ? 'APPROVE' : 'REJECT',
                'status_after' => 'FINANCE_' . $status,
                'file_name' => (string) ($file['file_name'] ?? ''),
                'remark' => (string) $remark,
                'action_by' => (int) $userId,
            ]);
            $this->refreshPackageStatus((int) ($file['id_doc_package'] ?? 0));
        }

        return $result;
    }

    public function approveAllDonationFinanceDocuments($clusterId, $groupKey, $userId, $remark = '')
    {
        $rows = $this->getDonationDocumentRows((int) $clusterId, (string) $groupKey);
        $updated = 0;
        foreach ($rows as $row) {
            $fileId = (int) ($row['id_doc_file'] ?? 0);
            $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
            $financeStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));
            if ($fileId <= 0 || (int) ($row['is_required'] ?? 1) !== 1 || $status !== 'APPROVED' || $financeStatus === 'APPROVED') {
                continue;
            }
            if ($this->updateDonationFinanceStatus($fileId, 'APPROVED', (int) $userId, (string) $remark)) {
                $updated++;
            }
        }

        return $updated;
    }

    public function updateDonationAstriStatus($fileId, array $data)
    {
        if (!$this->batchDocumentTablesReady() || !$this->tableHasField('tb_myrep_flow_doc_file', 'astri_status')) {
            return false;
        }

        $file = $this->getBatchFileById((int) $fileId);
        if (empty($file)) {
            return false;
        }

        $status = strtoupper(trim((string) ($data['astri_status'] ?? 'NY')));
        if (!in_array($status, ['NY', 'ON REVIEW', 'APPROVED', 'REJECTED'], true)) {
            $status = 'NY';
        }

        $payload = [
            'astri_status' => $status,
            'astri_submitted_date' => $status === 'NY' ? null : ($data['astri_submitted_date'] ?? date('Y-m-d')),
            'astri_status_updated_at' => $status === 'NY' ? null : date('Y-m-d H:i:s'),
            'astri_remark' => (string) ($data['astri_remark'] ?? ''),
        ];
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_approved_date')) {
            $approvedDate = $data['astri_approved_date'] ?? null;
            $payload['astri_approved_date'] = $status === 'APPROVED' ? ($approvedDate ?: date('Y-m-d')) : null;
        }

        $result = $this->db->where('id_doc_file', (int) $fileId)->update('tb_myrep_flow_doc_file', $payload);
        if ($result) {
            $this->createFileLog([
                'id_doc_file' => (int) $fileId,
                'id_doc_package' => (int) ($file['id_doc_package'] ?? 0),
                'id_doc_item' => (int) ($file['id_doc_item'] ?? 0),
                'action_type' => 'ASTRI_' . str_replace(' ', '_', $status),
                'status_after' => $status,
                'file_name' => (string) ($file['file_name'] ?? ''),
                'remark' => (string) ($data['astri_remark'] ?? ''),
                'action_by' => (int) ($data['updated_by'] ?? 0),
            ]);
        }

        return $result;
    }

    public function areDonationRequiredDocumentsApproved($clusterId, $groupKey)
    {
        $summary = $this->getDonationDocumentSummary((int) $clusterId);
        $key = strtoupper(trim((string) $groupKey));
        if (empty($summary[$key])) {
            return false;
        }
        return (int) $summary[$key]['required'] > 0
            && (int) $summary[$key]['approved'] >= (int) $summary[$key]['required'];
    }

    public function areDonationRequiredDocumentsFinanceApproved($clusterId, $groupKey)
    {
        $summary = $this->getDonationDocumentSummary((int) $clusterId);
        $key = strtoupper(trim((string) $groupKey));
        if (empty($summary[$key])) {
            return false;
        }

        return (int) ($summary[$key]['finance_required'] ?? 0) > 0
            && (int) ($summary[$key]['finance_approved'] ?? 0) >= (int) ($summary[$key]['finance_required'] ?? 0);
    }

    public function arePostDonationDocumentsAstriApproved($clusterId)
    {
        $summary = $this->getDonationDocumentSummary((int) $clusterId);
        return (int) ($summary['POST_ZEYN']['required'] ?? 0) > 0
            && (int) ($summary['POST_ZEYN']['astri_approved'] ?? 0) >= (int) ($summary['POST_ZEYN']['required'] ?? 0);
    }

    public function getDonationDocumentSummary($clusterId)
    {
        $map = $this->getDonationDocumentSummaryMap([(int) $clusterId]);
        return $map[(int) $clusterId] ?? $this->buildEmptyDonationDocumentSummary();
    }

    public function saveDonationPoInvoice($clusterId, $batchId, array $batchPayload, array $poPayload = [], array $terminPayload = [])
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

        $this->db->trans_start();
        if (!empty($batchPayload)) {
            $this->db->where('id_batch_approval', $batchId)->update('tb_myrep_batch_approval', $this->sanitizePayloadForTable('tb_myrep_batch_approval', $batchPayload));
        }

        if (!empty($poPayload) && $this->db->table_exists('tb_myrep_po_header') && $this->db->table_exists('tb_myrep_po_termin')) {
            $poNumber = trim((string) ($poPayload['po_number'] ?? ''));
            if ($poNumber !== '') {
                $poRow = $this->db
                    ->from('tb_myrep_po_header')
                    ->where('id_myrep_cluster', $clusterId)
                    ->where('UPPER(TRIM(po_type))', 'DONASI')
                    ->limit(1)
                    ->get()
                    ->row_array();
                $poPayload = $this->sanitizePayloadForTable('tb_myrep_po_header', $poPayload);
                $poPayload['id_myrep_cluster'] = $clusterId;
                $poPayload['po_type'] = 'DONASI';
                $poPayload['po_category'] = 'INITIAL';

                if ($poRow) {
                    $this->db->where('id_po_header', (int) $poRow['id_po_header'])->update('tb_myrep_po_header', $poPayload);
                    $poHeaderId = (int) $poRow['id_po_header'];
                } else {
                    $this->db->insert('tb_myrep_po_header', $poPayload);
                    $poHeaderId = (int) $this->db->insert_id();
                }

                if ($poHeaderId > 0) {
                    $termin = $this->db
                        ->from('tb_myrep_po_termin')
                        ->where('id_po_header', $poHeaderId)
                        ->where('termin_no', 1)
                        ->limit(1)
                        ->get()
                        ->row_array();
                    $terminPayload = $this->sanitizePayloadForTable('tb_myrep_po_termin', $terminPayload);
                    $terminPayload['id_po_header'] = $poHeaderId;
                    $terminPayload['termin_no'] = 1;
                    $terminPayload['termin_percent'] = 100;
                    if ($termin) {
                        $this->db->where('id_po_termin', (int) $termin['id_po_termin'])->update('tb_myrep_po_termin', $terminPayload);
                    } else {
                        $this->db->insert('tb_myrep_po_termin', $terminPayload);
                    }
                }
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
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
            ->where($this->collatedUpperInSql('doc_package.flow_type', ['BAK', 'VALSAL']), null, false)
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

    private function buildEmptyDonationDocumentSummary()
    {
        return [
            'PRE_ZEYN' => [
                'required' => 0,
                'uploaded' => 0,
                'approved' => 0,
                'rejected' => 0,
                'finance_required' => 0,
                'finance_approved' => 0,
                'finance_rejected' => 0,
                'finance_on_review' => 0,
                'astri_submitted' => 0,
                'astri_approved' => 0,
                'astri_rejected' => 0,
            ],
            'POST_ZEYN' => [
                'required' => 0,
                'uploaded' => 0,
                'approved' => 0,
                'rejected' => 0,
                'finance_required' => 0,
                'finance_approved' => 0,
                'finance_rejected' => 0,
                'finance_on_review' => 0,
                'astri_submitted' => 0,
                'astri_approved' => 0,
                'astri_rejected' => 0,
            ],
        ];
    }

    private function getDonationDocumentSummaryMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds) || !$this->batchDocumentTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('
                c.id_myrep_cluster,
                g.group_label,
                i.id_doc_item,
                i.is_required,
                f.id_doc_file,
                f.status_file
            ', false)
            ->from('tb_myrep_cluster c')
            ->join('md_myrep_flow_doc_group g', "g.flow_type = 'BATCH_APPROVAL' AND g.is_active = 1", 'inner', false)
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_package p', 'p.id_myrep_cluster = c.id_myrep_cluster AND p.flow_type = \'BATCH_APPROVAL\' AND p.id_doc_group = g.id_doc_group', 'left', false)
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where_in('c.id_myrep_cluster', $clusterIds)
            ->where_in('g.group_label', array_values($this->donationDocGroups));
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'astri_status')) {
            $this->db->select('f.astri_status, f.astri_submitted_date');
        } else {
            $this->db->select("'NY' AS astri_status, NULL AS astri_submitted_date", false);
        }
        if ($this->tableHasField('tb_myrep_flow_doc_file', 'finance_status')) {
            $this->db->select('f.finance_status');
        } else {
            $this->db->select("'NY' AS finance_status", false);
        }

        $rows = $this->db->get()->result_array();
        $map = [];
        foreach ($clusterIds as $clusterId) {
            $map[$clusterId] = $this->buildEmptyDonationDocumentSummary();
        }

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $groupLabel = strtoupper(trim((string) ($row['group_label'] ?? '')));
            $key = $groupLabel === $this->donationDocGroups['POST_ZEYN'] ? 'POST_ZEYN' : 'PRE_ZEYN';
            if (!isset($map[$clusterId][$key])) {
                continue;
            }

            if ((int) ($row['is_required'] ?? 1) === 1) {
                $map[$clusterId][$key]['required']++;
            }

            $hasFile = (int) ($row['id_doc_file'] ?? 0) > 0;
            $statusFile = strtoupper(trim((string) ($row['status_file'] ?? '')));
            $astriStatus = strtoupper(trim((string) ($row['astri_status'] ?? 'NY')));
            $financeStatus = strtoupper(trim((string) ($row['finance_status'] ?? 'NY')));
            $isRequired = (int) ($row['is_required'] ?? 1) === 1;
            if ($isRequired) {
                $map[$clusterId][$key]['finance_required']++;
            }
            if ($hasFile) {
                $map[$clusterId][$key]['uploaded']++;
            }
            if ($hasFile && $statusFile === 'APPROVED' && $isRequired) {
                $map[$clusterId][$key]['approved']++;
            }
            if ($hasFile && $statusFile === 'REJECTED') {
                $map[$clusterId][$key]['rejected']++;
            }
            if ($hasFile && $statusFile === 'APPROVED' && $isRequired && $financeStatus === 'APPROVED') {
                $map[$clusterId][$key]['finance_approved']++;
            }
            if ($hasFile && $isRequired && $financeStatus === 'REJECTED') {
                $map[$clusterId][$key]['finance_rejected']++;
            }
            if ($hasFile && $statusFile === 'APPROVED' && $isRequired && !in_array($financeStatus, ['APPROVED', 'REJECTED'], true)) {
                $map[$clusterId][$key]['finance_on_review']++;
            }
            if ($hasFile && in_array($astriStatus, ['ON REVIEW', 'APPROVED', 'REJECTED'], true) && $isRequired) {
                $map[$clusterId][$key]['astri_submitted']++;
            }
            if ($hasFile && $astriStatus === 'APPROVED' && $isRequired) {
                $map[$clusterId][$key]['astri_approved']++;
            }
            if ($hasFile && $astriStatus === 'REJECTED') {
                $map[$clusterId][$key]['astri_rejected']++;
            }
        }

        return $map;
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

        $escapedCities = array_map(function ($cityName) {
            return 'CONVERT(' . $this->db->escape($cityName) . ' USING utf8mb4) COLLATE utf8mb4_unicode_ci';
        }, array_keys($allowedCitySet));
        $columnSql = 'CONVERT(UPPER(' . $columnName . ') USING utf8mb4) COLLATE utf8mb4_unicode_ci';
        $this->db->where($columnSql . ' IN (' . implode(',', $escapedCities) . ')', null, false);

        return true;
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

    private function getCityPicApprovalName($cityName, $provinceName = '', $regionalName = '')
    {
        $cityName = strtoupper(trim((string) $cityName));
        if ($cityName === '' || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return '';
        }

        $cacheKey = $cityName . '|' . strtoupper(trim((string) $provinceName)) . '|' . strtoupper(trim((string) $regionalName));
        if (array_key_exists($cacheKey, $this->cityPicApprovalNameCache)) {
            return $this->cityPicApprovalNameCache[$cacheKey];
        }

        $this->db
            ->from('tb_myrep_pic_mapping_city')
            ->where('UPPER(city_name)', $cityName);
        if (trim((string) $provinceName) !== '') {
            $this->db->where('UPPER(province_name)', strtoupper(trim((string) $provinceName)));
        }
        if (trim((string) $regionalName) !== '') {
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
        if (!empty($mappingRow) && $this->db->field_exists($this->cityPicApprovalColumn, 'tb_myrep_pic_mapping_city')) {
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
        if ($this->isCurrentUserFinanceHo()) {
            return false;
        }

        return true;
    }

    private function isCurrentUserFinanceHo()
    {
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0 || !$this->db->table_exists('tb_master_user_new') || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return false;
        }
        if (!$this->db->field_exists('finance_ho', 'tb_myrep_pic_mapping_city')) {
            return false;
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
            return false;
        }

        $this->db->from('tb_myrep_pic_mapping_city');
        $this->db->where(myrep_pic_column_contains_sql($this->db, '`finance_ho`', $nik), null, false);
        if ($this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) {
            $this->db->where('is_active', 1);
        }

        return (int) $this->db->count_all_results() > 0;
    }

    private function getTableFieldSet($tableName)
    {
        $tableName = trim((string) $tableName);
        if ($tableName === '') {
            return [];
        }

        if (isset($this->tableFieldSetCache[$tableName])) {
            return $this->tableFieldSetCache[$tableName];
        }

        if (!$this->db->table_exists($tableName)) {
            $this->tableFieldSetCache[$tableName] = [];
            return $this->tableFieldSetCache[$tableName];
        }

        $fieldSet = [];
        foreach ($this->db->list_fields($tableName) as $fieldName) {
            $fieldSet[(string) $fieldName] = true;
        }

        $this->tableFieldSetCache[$tableName] = $fieldSet;
        return $this->tableFieldSetCache[$tableName];
    }

    private function tableHasField($tableName, $fieldName)
    {
        $fieldName = trim((string) $fieldName);
        if ($fieldName === '') {
            return false;
        }

        $fieldSet = $this->getTableFieldSet($tableName);
        return isset($fieldSet[$fieldName]);
    }

    private function addColumnIfMissing($tableName, $columnName, $alterSql)
    {
        $tableName = trim((string) $tableName);
        $columnName = trim((string) $columnName);
        if ($tableName === '' || $columnName === '' || !$this->db->table_exists($tableName) || $this->db->field_exists($columnName, $tableName)) {
            return false;
        }

        $this->db->query((string) $alterSql);
        unset($this->tableFieldSetCache[$tableName]);
        return true;
    }

    private function backfillExistingSitacApprovedFinanceStatus()
    {
        if (
            !$this->db->table_exists('tb_myrep_flow_doc_file')
            || !$this->db->table_exists('tb_myrep_flow_doc_package')
            || !$this->db->table_exists('md_myrep_flow_doc_group')
            || !$this->db->table_exists('md_myrep_flow_doc_item')
        ) {
            return;
        }

        $this->db->query("
            UPDATE tb_myrep_flow_doc_file f
            JOIN tb_myrep_flow_doc_package p ON p.id_doc_package = f.id_doc_package
            JOIN md_myrep_flow_doc_group g ON g.id_doc_group = p.id_doc_group
            JOIN md_myrep_flow_doc_item i ON i.id_doc_item = f.id_doc_item
            SET
                f.finance_status = 'APPROVED',
                f.finance_reviewed_at = COALESCE(f.approved_at, f.reviewed_at, f.uploaded_at, NOW()),
                f.finance_approved_at = COALESCE(f.approved_at, f.reviewed_at, f.uploaded_at, NOW())
            WHERE UPPER(TRIM(g.group_label)) IN ('PRE ZEYN DOCUMENT', 'POST PAYMENT ZEYN DOCUMENT')
                AND COALESCE(i.is_required, 1) = 1
                AND UPPER(TRIM(f.status_file)) = 'APPROVED'
                AND (f.finance_status IS NULL OR TRIM(f.finance_status) = '' OR UPPER(TRIM(f.finance_status)) = 'NY')
        ");
    }

    private function sanitizePayloadForTable($tableName, array $payload)
    {
        if (empty($payload)) {
            return [];
        }

        $fieldSet = $this->getTableFieldSet($tableName);
        if (empty($fieldSet)) {
            return [];
        }

        $sanitized = [];
        $dropped = [];
        foreach ($payload as $key => $value) {
            $columnName = (string) $key;
            if (isset($fieldSet[$columnName])) {
                $sanitized[$columnName] = $value;
            } else {
                $dropped[] = $columnName;
            }
        }

        if (!empty($dropped)) {
            log_message(
                'debug',
                'MBatch_Approval_MyRep: kolom payload diabaikan karena tidak ada di '
                . $tableName . ' => ' . implode(', ', $dropped)
            );
        }

        return $sanitized;
    }

    private function hasDbError($contextMessage)
    {
        $dbError = (array) $this->db->error();
        $errorCode = (int) ($dbError['code'] ?? 0);
        if ($errorCode === 0) {
            return false;
        }

        $errorText = trim((string) ($dbError['message'] ?? 'Unknown database error'));
        $this->setLastError($contextMessage . ' [' . $errorCode . ']: ' . $errorText);
        return true;
    }

    private function setLastError($message)
    {
        $this->lastErrorMessage = trim((string) $message);
        if ($this->lastErrorMessage !== '') {
            log_message('error', 'MBatch_Approval_MyRep: ' . $this->lastErrorMessage);
        }
    }

    private function resolveDisplayStagingStatus($stagingStatus, $postDocTotal, $postDocApproved, array $donationSummary = [])
    {
        $stagingStatus = strtoupper(trim((string) $stagingStatus));
        $postDocTotal = (int) $postDocTotal;
        $postDocApproved = (int) $postDocApproved;

        if ($stagingStatus === 'BATCH_APPROVED') {
            $pre = $donationSummary['PRE_ZEYN'] ?? [];
            $preRequired = (int) ($pre['required'] ?? 0);
            if ($preRequired > 0) {
                if ((int) ($pre['approved'] ?? 0) < $preRequired) {
                    if ((int) ($pre['uploaded'] ?? 0) >= $preRequired) {
                        return 'PRE_ZEYN_DOC_ON_REVIEW';
                    }
                    return 'WAITING_PRE_ZEYN_DOC';
                }
                if ((int) ($pre['finance_approved'] ?? 0) < (int) ($pre['finance_required'] ?? $preRequired)) {
                    return 'PRE_ZEYN_FINANCE_ON_REVIEW';
                }
                return 'PRE_ZEYN_FINANCE_APPROVED';
            }
        }

        if ($stagingStatus === 'PRE_ZEYN_DOC_APPROVED' || $stagingStatus === 'PRE_ZEYN_FINANCE_ON_REVIEW') {
            $pre = $donationSummary['PRE_ZEYN'] ?? [];
            $preFinanceRequired = (int) ($pre['finance_required'] ?? $pre['required'] ?? 0);
            if ($preFinanceRequired > 0 && (int) ($pre['finance_approved'] ?? 0) >= $preFinanceRequired) {
                return 'PRE_ZEYN_FINANCE_APPROVED';
            }
            if ($preFinanceRequired > 0) {
                return 'PRE_ZEYN_FINANCE_ON_REVIEW';
            }
        }

        if ($stagingStatus === 'RELEASED') {
            $post = $donationSummary['POST_ZEYN'] ?? [];
            $postRequired = (int) ($post['required'] ?? 0);
            if ($postRequired > 0) {
                if ((int) ($post['approved'] ?? 0) < $postRequired) {
                    if ((int) ($post['uploaded'] ?? 0) >= $postRequired) {
                        return 'POST_ZEYN_DOC_ON_REVIEW';
                    }
                    return 'WAITING_POST_ZEYN_DOC';
                }
                if ((int) ($post['finance_approved'] ?? 0) < (int) ($post['finance_required'] ?? $postRequired)) {
                    return 'POST_ZEYN_FINANCE_ON_REVIEW';
                }
                return 'WAITING_ASTRI_SUBMISSION';
            }
        }

        if ($stagingStatus === 'POST_ZEYN_DOC_APPROVED' || $stagingStatus === 'POST_ZEYN_FINANCE_ON_REVIEW') {
            $post = $donationSummary['POST_ZEYN'] ?? [];
            $postFinanceRequired = (int) ($post['finance_required'] ?? $post['required'] ?? 0);
            if ($postFinanceRequired > 0 && (int) ($post['finance_approved'] ?? 0) >= $postFinanceRequired) {
                return 'WAITING_ASTRI_SUBMISSION';
            }
            if ($postFinanceRequired > 0) {
                return 'POST_ZEYN_FINANCE_ON_REVIEW';
            }
        }

        if ($stagingStatus === 'WAITING_ASTRI_SUBMISSION' || $stagingStatus === 'ASTRI_ON_REVIEW') {
            $post = $donationSummary['POST_ZEYN'] ?? [];
            if ((int) ($post['required'] ?? 0) > 0 && (int) ($post['astri_approved'] ?? 0) >= (int) ($post['required'] ?? 0)) {
                return 'ASTRI_APPROVED';
            }
        }

        if (in_array($stagingStatus, ['RELEASED', 'DONE BATCH APPROVAL'], true) && $postDocTotal > 0) {
            return $postDocApproved >= $postDocTotal ? 'COMPLETED' : 'WAITING DOC';
        }

        return $stagingStatus !== '' ? $stagingStatus : 'DRAFT';
    }

    private function resolveStoredBatchStagingStatus(array $row)
    {
        $hasBatch = (int) ($row['id_batch_approval'] ?? 0) > 0;
        $stagingStatus = strtoupper(trim((string) ($row['staging_status'] ?? '')));

        if ($hasBatch
            && in_array($stagingStatus, ['', 'DRAFT', 'WAITING_BATCH_APPROVAL'], true)
            && (trim((string) ($row['astri_batch_number'] ?? '')) !== '' || trim((string) ($row['astri_batch_approved_at'] ?? '')) !== '')) {
            return 'BATCH_APPROVED';
        }

        if ($hasBatch && ($stagingStatus === '' || $stagingStatus === 'DRAFT')) {
            return 'WAITING_BATCH_APPROVAL';
        }

        return $stagingStatus !== '' ? $stagingStatus : 'DRAFT';
    }
}


