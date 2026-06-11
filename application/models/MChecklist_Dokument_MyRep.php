<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MChecklist_Dokument_MyRep extends CI_Model
{
    private $maxWhereInChunk = 500;
    private $cityPicMappingCache = [];
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    private function sanitizeIdList(array $ids)
    {
        $clean = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) {
            return $id > 0;
        })));
        return $clean;
    }

    public function supportsAtpColumns()
    {
        return $this->db->field_exists('status_atp', 'tb_rfs_myrep_cluster');
    }

    private function getProjectOpnameAstriStatuses()
    {
        return [
            'WAITING WASPANG',
            'WAITING PLANNING',
            'WAITING TL',
            'WAITING LOGISTIK',
        ];
    }

    private function isSpecialProjectOpname($scopeType, $sowType, $docName)
    {
        return strtoupper(trim((string) $scopeType)) === 'CLUSTER'
            && strtoupper(trim((string) $sowType)) === 'RFS'
            && strtoupper(trim((string) $docName)) === 'PROJECT OPNAME';
    }

    private function getEffectiveAstriStatus($storedStatus, $actualAtpDate, $scopeType, $sowType, $docName)
    {
        $status = strtoupper(trim((string) $storedStatus));
        if ($status === '') {
            $status = 'NY';
        }

        if (
            $status === 'NY'
            && !empty($actualAtpDate)
            && $this->isSpecialProjectOpname($scopeType, $sowType, $docName)
        ) {
            return 'WAITING WASPANG';
        }

        return $status;
    }

    public function getKesepakatanSourceDocuments($myrepClusterId)
    {
        $myrepClusterId = (int) $myrepClusterId;
        if ($myrepClusterId <= 0) {
            return [];
        }

        $required = [
            'PERJANJIAN DONASI DAN PEMBERIAN IZIN',
            'BUKTI TRANSFER KONTRIBUSI',
            'DOKUMENTASI PENYERAHAN KONTRIBUSI',
            'KTP RT/RW',
        ];

        $rows = $this->db
            ->select('
                i.doc_name,
                f.id_doc_file,
                f.file_name,
                f.file_path,
                f.status_file
            ')
            ->from('tb_myrep_flow_doc_package p')
            ->join('md_myrep_flow_doc_group g', 'g.id_doc_group = p.id_doc_group AND g.is_active = 1', 'inner')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'inner')
            ->where('p.id_myrep_cluster', $myrepClusterId)
            ->where('p.flow_type', 'POST_DONASI')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $docKey = strtoupper(trim((string) ($row['doc_name'] ?? '')));
            if (!in_array($docKey, $required, true) || isset($map[$docKey])) {
                continue;
            }
            $map[$docKey] = $row;
        }

        $result = [];
        foreach ($required as $docKey) {
            if (!isset($map[$docKey])) {
                return [];
            }
            $result[] = [
                'doc_name' => (string) ($map[$docKey]['doc_name'] ?? ''),
                'id_doc_file' => (int) ($map[$docKey]['id_doc_file'] ?? 0),
                'file_name' => (string) ($map[$docKey]['file_name'] ?? ''),
                'file_path' => (string) ($map[$docKey]['file_path'] ?? ''),
                'status_file' => strtoupper(trim((string) ($map[$docKey]['status_file'] ?? ''))),
            ];
        }

        return $result;
    }

    private function getChecklistLinkedDocumentMap($myrepClusterId, $rfsClusterId = 0)
    {
        $myrepClusterId = (int) $myrepClusterId;
        if ($myrepClusterId <= 0) {
            return [];
        }

        $sourceRows = $this->db
            ->select('
                p.flow_type,
                g.group_label,
                i.doc_name,
                f.id_doc_file,
                f.file_name,
                f.file_path,
                f.status_file
            ')
            ->from('tb_myrep_flow_doc_package p')
            ->join('md_myrep_flow_doc_group g', 'g.id_doc_group = p.id_doc_group AND g.is_active = 1', 'inner')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'inner')
            ->where('p.id_myrep_cluster', $myrepClusterId)
            ->where_in('p.flow_type', ['POST_DONASI', 'VALSAL'])
            ->get()
            ->result_array();

        $lookup = [];
        $lookupByFlowDoc = [];
        foreach ($sourceRows as $row) {
            $flowKey = strtoupper(trim((string) ($row['flow_type'] ?? '')));
            $groupKey = strtoupper(trim((string) ($row['group_label'] ?? '')));
            $docKey = strtoupper(trim((string) ($row['doc_name'] ?? '')));
            $key = $flowKey . '|' . $groupKey . '|' . $docKey;
            if (!isset($lookup[$key])) {
                $lookup[$key] = $row;
            }

            $flowDocKey = $flowKey . '|' . $docKey;
            if (!isset($lookupByFlowDoc[$flowDocKey])) {
                $lookupByFlowDoc[$flowDocKey] = $row;
            }
        }

        $targetMappings = [
            'BERITA ACARA OPEN' => ['flow_type' => 'POST_DONASI', 'group_label' => '', 'doc_name' => 'Berita Acara Open'],
            'CLUSTER APPROVAL PROPOSAL' => ['flow_type' => 'POST_DONASI', 'group_label' => '', 'doc_name' => 'Cluster Approval Proposal'],
            'FORM CLUSTER SURVEY' => ['flow_type' => 'POST_DONASI', 'group_label' => '', 'doc_name' => 'Form Cluster Survey'],
            'FORM FREE LAYANAN' => ['flow_type' => 'POST_DONASI', 'group_label' => '', 'doc_name' => 'Form Free Layanan'],
            'LAYOUT SND KASAR' => ['flow_type' => 'VALSAL', 'group_label' => 'VALIDASI SALES', 'doc_name' => 'SND Kasar'],
        ];

        $result = [];
        foreach ($targetMappings as $targetDocName => $sourceMap) {
            $sourceRow = [];
            if (trim((string) ($sourceMap['group_label'] ?? '')) !== '') {
                $lookupKey = strtoupper($sourceMap['flow_type']) . '|' .
                    strtoupper($sourceMap['group_label']) . '|' .
                    strtoupper($sourceMap['doc_name']);
                $sourceRow = $lookup[$lookupKey] ?? [];
            } else {
                $lookupKey = strtoupper($sourceMap['flow_type']) . '|' . strtoupper($sourceMap['doc_name']);
                $sourceRow = $lookupByFlowDoc[$lookupKey] ?? [];
            }

            if (empty($sourceRow['id_doc_file'])) {
                continue;
            }
            $sourceFlow = strtoupper(trim((string) ($sourceRow['flow_type'] ?? '')));
            $previewPath = '';
            if ($sourceFlow === 'POST_DONASI') {
                $previewPath = 'Post_Donasi_MyRep/previewDocument/' . (int) $sourceRow['id_doc_file'];
            } elseif ($sourceFlow === 'VALSAL') {
                $previewPath = 'VALSAL_MyRep/previewDocument/' . (int) $sourceRow['id_doc_file'];
            }

            $result[$targetDocName] = [
                'linked_source_flow_type' => $sourceFlow,
                'linked_source_group_label' => (string) ($sourceRow['group_label'] ?? ''),
                'linked_source_doc_name' => (string) ($sourceRow['doc_name'] ?? ''),
                'linked_source_file_id' => (int) ($sourceRow['id_doc_file'] ?? 0),
                'linked_source_file_name' => (string) ($sourceRow['file_name'] ?? ''),
                'linked_source_file_path' => (string) ($sourceRow['file_path'] ?? ''),
                'linked_source_status' => strtoupper(trim((string) ($sourceRow['status_file'] ?? ''))),
                'linked_source_preview_path' => $previewPath,
            ];
        }

        $kesepakatanDocs = $this->getKesepakatanSourceDocuments($myrepClusterId);
        if (!empty($kesepakatanDocs) && (int) $rfsClusterId > 0) {
            $result['BERITA ACARA KESEPAKATAN'] = [
                'linked_source_flow_type' => 'POST_DONASI',
                'linked_source_group_label' => 'BATCH APPROVAL',
                'linked_source_doc_name' => 'Berita Acara Kesepakatan (Generated)',
                'linked_source_file_id' => 1,
                'linked_source_file_name' => 'BA_KESEPAKATAN_' . (int) $rfsClusterId . '.pdf',
                'linked_source_file_path' => '',
                'linked_source_status' => 'APPROVED',
                'linked_source_preview_path' => 'Checklist_Dokument_MyRep/previewKesepakatanPdf/' . (int) $rfsClusterId,
            ];
        }

        return $result;
    }

    public function ensureClusterPackages($clusterId, $tanggalRfs = null)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return;
        }

        $groups = $this->getDocumentGroups();
        foreach ($groups as $group) {
            $existing = $this->db->get_where('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => (int) $group['id_doc_group'],
            ])->row_array();

            if ($existing) {
                if (!empty($tanggalRfs) && empty($existing['tanggal_rfs'])) {
                    $planAtp = $this->addBusinessDays($tanggalRfs, 7);
                    $this->db
                        ->where('id_doc_package', (int) $existing['id_doc_package'])
                        ->update('tb_rfs_myrep_doc_package', [
                            'tanggal_rfs' => $tanggalRfs,
                            'plan_atp_date' => $planAtp,
                        ]);
                }
                continue;
            }

            $planAtp = !empty($tanggalRfs) ? $this->addBusinessDays($tanggalRfs, 7) : null;
            $this->db->insert('tb_rfs_myrep_doc_package', [
                'cluster_id' => $clusterId,
                'id_doc_group' => (int) $group['id_doc_group'],
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'status_package' => 'NOT STARTED',
                'created_by' => (int) $this->session->userdata('id_user'),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
        }
    }

    public function getCityOptions()
    {
        $query = $this->db
            ->distinct()
            ->select('mt.city_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS');

        if ($this->supportsAtpColumns()) {
            $query
                ->join('(
                    SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                    FROM tb_rfs_myrep_doc_package
                    GROUP BY cluster_id
                ) atp_summary', 'atp_summary.cluster_id = c.id_cluster', 'left')
                ->where("UPPER(COALESCE(c.status_atp, '')) = 'DONE'", null, false)
                ->where('atp_summary.actual_atp_date IS NOT NULL', null, false);
        }

        if (!$this->applyAllowedCityRestriction('mt.city_name')) {
            return [];
        }

        $rows = $query
            ->order_by('mt.city_name', 'ASC')
            ->get()
            ->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $city = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($city !== '') {
                $cities[$city] = $city;
            }
        }

        return array_values($cities);
    }

    public function getRegionalOptions()
    {
        $query = $this->db
            ->distinct()
            ->select('mt.regional_name')
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->where('c.status_rfs', 'FULL RFS');

        if ($this->supportsAtpColumns()) {
            $query
                ->join('(
                    SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                    FROM tb_rfs_myrep_doc_package
                    GROUP BY cluster_id
                ) atp_summary', 'atp_summary.cluster_id = c.id_cluster', 'left')
                ->where("UPPER(COALESCE(c.status_atp, '')) = 'DONE'", null, false)
                ->where('atp_summary.actual_atp_date IS NOT NULL', null, false);
        }

        if (!$this->applyAllowedCityRestriction('mt.city_name')) {
            return [];
        }

        $rows = $query
            ->order_by('mt.regional_name', 'ASC')
            ->get()
            ->result_array();

        $regionals = [];
        foreach ($rows as $row) {
            $regional = strtoupper(trim((string) ($row['regional_name'] ?? '')));
            if ($regional !== '') {
                $regionals[$regional] = $regional;
            }
        }

        return array_values($regionals);
    }

    public function getFullRfsClusters($city = '', $regional = '')
    {
        $this->db->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                mc.status_current,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                latest_claim.rfs_date
            ", false);

        if (!$this->prepareFullRfsClusterQuery($city, $regional)) {
            return [];
        }

        $rows = $this->applyFullRfsClusterOrder()
            ->get()
            ->result_array();

        return $this->enrichClusterRows($rows);
    }

    public function getFullRfsClusterPage($city = '', $regional = '', array $filters = [], $start = 0, $length = 10, array $order = [])
    {
        $start = max(0, (int) $start);
        $length = (int) $length;
        if ($length <= 0) {
            $length = 10;
        }

        $recordsTotal = $this->countFullRfsClusters($city, $regional);
        $recordsFiltered = $this->countFullRfsClusters($city, $regional, $filters);

        $this->db->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                mc.status_current,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.rpm,
                mt.sm,
                mt.spv,
                latest_claim.rfs_date
            ", false);

        if (!$this->prepareFullRfsClusterQuery($city, $regional)) {
            return [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'rows' => [],
            ];
        }

        $this->applyFullRfsClusterFilters($filters);
        $this->applyFullRfsClusterOrder($order);
        $rows = $this->db
            ->limit($length, $start)
            ->get()
            ->result_array();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $this->enrichClusterRows($rows),
        ];
    }

    private function countFullRfsClusters($city = '', $regional = '', array $filters = [])
    {
        $this->db->select('COUNT(*) AS total', false);
        if (!$this->prepareFullRfsClusterQuery($city, $regional)) {
            return 0;
        }

        $this->applyFullRfsClusterFilters($filters);
        $row = $this->db->get()->row_array();

        return (int) ($row['total'] ?? 0);
    }

    private function prepareFullRfsClusterQuery($city = '', $regional = '')
    {
        $this->db
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_myrep_cluster mc', 'mc.rfs_cluster_id = c.id_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.id_cluster', 'left')
            ->where('c.status_rfs', 'FULL RFS');

        if ($this->supportsAtpColumns()) {
            $this->db
                ->join('(
                    SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                    FROM tb_rfs_myrep_doc_package
                    GROUP BY cluster_id
                ) atp_summary', 'atp_summary.cluster_id = c.id_cluster', 'left')
                ->where("UPPER(COALESCE(c.status_atp, '')) = 'DONE'", null, false)
                ->where('atp_summary.actual_atp_date IS NOT NULL', null, false);
        }

        if (!$this->applyAllowedCityRestriction('mt.city_name')) {
            return false;
        }

        if ($city !== '') {
            $this->db->where('UPPER(mt.city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $this->db->where('UPPER(mt.regional_name)', strtoupper($regional));
        }

        return true;
    }

    private function applyFullRfsClusterFilters(array $filters)
    {
        $searchValue = strtoupper(trim((string) ($filters['search'] ?? '')));
        if ($searchValue === '') {
            return;
        }

        $expressions = [
            'UPPER(COALESCE(mt.regional_name, \'\'))',
            'UPPER(COALESCE(mt.city_name, \'\'))',
            'UPPER(COALESCE(c.cluster_name, \'\'))',
            'UPPER(COALESCE(CAST(c.homepass AS CHAR), \'\'))',
            'UPPER(COALESCE(CAST(latest_claim.rfs_date AS CHAR), \'\'))',
            'UPPER(COALESCE(c.status_rfs, \'\'))',
            'UPPER(COALESCE(mc.status_current, \'\'))',
        ];

        $like = $this->db->escape('%' . $this->db->escape_like_str($searchValue) . '%');
        $this->db->group_start();
        foreach ($expressions as $index => $expression) {
            $condition = $expression . " LIKE " . $like . " ESCAPE '!'";
            if ($index === 0) {
                $this->db->where($condition, null, false);
            } else {
                $this->db->or_where($condition, null, false);
            }
        }
        $this->db->group_end();
    }

    private function applyFullRfsClusterOrder(array $order = [])
    {
        $columnMap = [
            1 => 'mt.regional_name',
            2 => 'c.cluster_name',
        ];

        $columnIndex = isset($order['column']) ? (int) $order['column'] : null;
        $direction = strtoupper(trim((string) ($order['dir'] ?? 'ASC'))) === 'DESC' ? 'DESC' : 'ASC';
        if ($columnIndex !== null && isset($columnMap[$columnIndex])) {
            return $this->db
                ->order_by($columnMap[$columnIndex], $direction)
                ->order_by('mt.city_name', 'ASC')
                ->order_by('c.cluster_name', 'ASC');
        }

        return $this->db
            ->order_by('mt.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC');
    }

    public function getClusterDocumentItemRows($city = '', $regional = '')
    {
        $this->db->select("
                c.id_cluster,
                c.cluster_name,
                mc.status_current,
                mt.city_name,
                mt.regional_name,
                mt.id_user_pic_ho,
                g.scope_type,
                g.sow_type,
                g.group_label,
                i.id_doc_item,
                i.doc_name,
                i.doc_requirement_note,
                i.format_file_name,
                i.format_file_path,
                i.verification_team,
                u_ho.nama_karyawan AS ho_pic_name,
                p.actual_atp_date,
                f.id_doc_file,
                f.status_file,
                f.file_name,
                f.file_path,
                f.remark,
                f.uploaded_at,
                f.reviewed_at,
                f.approved_at,
                f.astri_submitted_date,
                f.astri_status,
                f.astri_remark
            ", false);

        if (!$this->prepareClusterDocumentItemQuery($city, $regional)) {
            return [];
        }

        $rows = $this->applyClusterDocumentItemOrder()
            ->get()
            ->result_array();

        return $this->enrichClusterDocumentItemRows($rows);
    }

    public function getClusterDocumentItemPage($city = '', $regional = '', array $filters = [], $start = 0, $length = 10, array $order = [])
    {
        $start = max(0, (int) $start);
        $length = (int) $length;
        if ($length <= 0) {
            $length = 10;
        }

        $recordsTotal = $this->countClusterDocumentItemRows($city, $regional);
        $recordsFiltered = $this->countClusterDocumentItemRows($city, $regional, $filters);

        $this->db->select("
                c.id_cluster,
                c.cluster_name,
                mc.status_current,
                mt.city_name,
                mt.regional_name,
                mt.id_user_pic_ho,
                g.scope_type,
                g.sow_type,
                g.group_label,
                i.id_doc_item,
                i.doc_name,
                i.doc_requirement_note,
                i.format_file_name,
                i.format_file_path,
                i.verification_team,
                u_ho.nama_karyawan AS ho_pic_name,
                p.actual_atp_date,
                f.id_doc_file,
                f.status_file,
                f.file_name,
                f.file_path,
                f.remark,
                f.uploaded_at,
                f.reviewed_at,
                f.approved_at,
                f.astri_submitted_date,
                f.astri_status,
                f.astri_remark
            ", false);

        if (!$this->prepareClusterDocumentItemQuery($city, $regional)) {
            return [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'rows' => [],
            ];
        }

        $this->applyClusterDocumentItemFilters($filters);
        $rows = $this->applyClusterDocumentItemOrder($order)
            ->limit($length, $start)
            ->get()
            ->result_array();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $this->enrichClusterDocumentItemRows($rows),
        ];
    }

    private function countClusterDocumentItemRows($city = '', $regional = '', array $filters = [])
    {
        $this->db->select('COUNT(*) AS total', false);
        if (!$this->prepareClusterDocumentItemQuery($city, $regional)) {
            return 0;
        }

        $this->applyClusterDocumentItemFilters($filters);
        $row = $this->db->get()->row_array();

        return (int) ($row['total'] ?? 0);
    }

    private function prepareClusterDocumentItemQuery($city = '', $regional = '')
    {
        $this->db
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_myrep_cluster mc', 'mc.rfs_cluster_id = c.id_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('md_rfs_myrep_doc_group g', 'g.is_active = 1', 'inner')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1 AND i.is_required = 1', 'inner')
            ->join('tb_master_user_new u_ho', 'u_ho.id = mt.id_user_pic_ho', 'left')
            ->join('tb_rfs_myrep_doc_package p', 'p.cluster_id = c.id_cluster AND p.id_doc_group = g.id_doc_group', 'left')
            ->join('tb_rfs_myrep_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('c.status_rfs', 'FULL RFS')
            ->group_start()
                ->where('mc.status_current IS NULL', null, false)
                ->or_where('UPPER(mc.status_current) <>', 'DONE')
            ->group_end();

        if ($this->supportsAtpColumns()) {
            $this->db
                ->join('(
                    SELECT cluster_id, MAX(actual_atp_date) AS actual_atp_date
                    FROM tb_rfs_myrep_doc_package
                    GROUP BY cluster_id
                ) atp_summary', 'atp_summary.cluster_id = c.id_cluster', 'left')
                ->where("UPPER(COALESCE(c.status_atp, '')) = 'DONE'", null, false)
                ->where('atp_summary.actual_atp_date IS NOT NULL', null, false);
        }

        if (!$this->applyAllowedCityRestriction('mt.city_name')) {
            return false;
        }

        if ($city !== '') {
            $this->db->where('UPPER(mt.city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $this->db->where('UPPER(mt.regional_name)', strtoupper($regional));
        }

        return true;
    }

    private function applyClusterDocumentItemOrder(array $order = [])
    {
        $columnIndex = isset($order['column']) ? (int) $order['column'] : -1;
        $direction = strtolower((string) ($order['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $columns = [
            1 => ['mt.regional_name'],
            2 => ['mt.city_name'],
            3 => ['c.cluster_name'],
            4 => ['g.scope_type'],
            5 => ['g.sow_type'],
            6 => ['i.doc_name'],
            7 => ['i.verification_team', 'u_ho.nama_karyawan'],
            8 => [$this->getInternalStatusLabelSql(), false],
            9 => ['f.remark'],
            10 => [$this->getAstriStatusLabelSql(), false],
            11 => ['f.astri_remark'],
            12 => ['f.uploaded_at'],
            13 => ['f.reviewed_at'],
            14 => ['f.approved_at'],
            15 => ['f.astri_submitted_date'],
        ];

        if (isset($columns[$columnIndex])) {
            $orderColumns = $columns[$columnIndex];
            $escape = true;
            if (end($orderColumns) === false) {
                array_pop($orderColumns);
                $escape = false;
            }

            foreach ($orderColumns as $orderColumn) {
                $this->db->order_by($orderColumn, $direction, $escape);
            }
        }

        return $this->db
            ->order_by('mt.regional_name', 'ASC')
            ->order_by('mt.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC')
            ->order_by('g.sort_no', 'ASC')
            ->order_by('i.sort_no', 'ASC');
    }

    private function enrichClusterDocumentItemRows(array $rows)
    {
        foreach ($rows as &$row) {
            $row['status_file'] = !empty($row['status_file']) ? $row['status_file'] : 'NOT UPLOADED';
            $row['astri_status'] = $this->getEffectiveAstriStatus(
                $row['astri_status'] ?? 'NY',
                $row['actual_atp_date'] ?? null,
                $row['scope_type'] ?? '',
                $row['sow_type'] ?? '',
                $row['doc_name'] ?? ''
            );
            $row['verification_by'] = $this->resolveVerificationDisplayName(
                (string) ($row['verification_team'] ?? ''),
                (string) ($row['ho_pic_name'] ?? ''),
                [
                    'city_name' => (string) ($row['city_name'] ?? ''),
                    'regional_name' => (string) ($row['regional_name'] ?? ''),
                ]
            );
        }
        unset($row);

        return $rows;
    }

    private function applyClusterDocumentItemFilters(array $filters)
    {
        $exactFilters = [
            'item_regional' => 'mt.regional_name',
            'item_city' => 'mt.city_name',
            'item_cluster' => 'c.cluster_name',
            'item_scope' => 'g.scope_type',
            'item_sow' => 'g.sow_type',
            'item_doc' => 'i.doc_name',
        ];

        foreach ($exactFilters as $filterKey => $column) {
            $value = strtoupper(trim((string) ($filters[$filterKey] ?? '')));
            if ($value !== '') {
                $this->db->where('UPPER(' . $column . ')', $value);
            }
        }

        $internalStatus = strtoupper(trim((string) ($filters['internal_status'] ?? '')));
        if ($internalStatus !== '') {
            $this->db->where($this->getInternalStatusLabelSql() . ' = ' . $this->db->escape($internalStatus), null, false);
        }

        $astriStatus = strtoupper(trim((string) ($filters['astri_status'] ?? '')));
        if ($astriStatus !== '') {
            $this->db->where($this->getAstriStatusLabelSql() . ' = ' . $this->db->escape($astriStatus), null, false);
        }

        $quickType = strtolower(trim((string) ($filters['quick_type'] ?? '')));
        $quickValue = strtoupper(trim((string) ($filters['quick_value'] ?? '')));
        if ($quickType === 'project-opname' && $quickValue !== '') {
            $this->db
                ->where("UPPER(TRIM(i.doc_name)) = 'PROJECT OPNAME'", null, false)
                ->where($this->getAstriStatusLabelSql() . ' = ' . $this->db->escape($quickValue), null, false);
        } elseif ($quickType === 'astri' && $quickValue === 'ON REVIEW') {
            $this->db->where($this->getAstriStatusLabelSql() . ' = ' . $this->db->escape('ON REVIEW'), null, false);
        }

        $searchValue = strtoupper(trim((string) ($filters['search'] ?? '')));
        if ($searchValue !== '') {
            $this->applyClusterDocumentItemSearch($searchValue);
        }
    }

    private function applyClusterDocumentItemSearch($searchValue)
    {
        $expressions = [
            'UPPER(COALESCE(mt.regional_name, \'\'))',
            'UPPER(COALESCE(mt.city_name, \'\'))',
            'UPPER(COALESCE(c.cluster_name, \'\'))',
            'UPPER(COALESCE(g.scope_type, \'\'))',
            'UPPER(COALESCE(g.sow_type, \'\'))',
            'UPPER(COALESCE(i.doc_name, \'\'))',
            'UPPER(COALESCE(i.verification_team, \'\'))',
            'UPPER(COALESCE(u_ho.nama_karyawan, \'\'))',
            $this->getInternalStatusLabelSql(),
            'UPPER(COALESCE(f.remark, \'\'))',
            $this->getAstriStatusLabelSql(),
            'UPPER(COALESCE(f.astri_remark, \'\'))',
            'UPPER(COALESCE(CAST(f.uploaded_at AS CHAR), \'\'))',
            'UPPER(COALESCE(CAST(f.reviewed_at AS CHAR), \'\'))',
            'UPPER(COALESCE(CAST(f.approved_at AS CHAR), \'\'))',
            'UPPER(COALESCE(CAST(f.astri_submitted_date AS CHAR), \'\'))',
        ];

        $like = $this->db->escape('%' . $this->db->escape_like_str($searchValue) . '%');
        $this->db->group_start();
        foreach ($expressions as $index => $expression) {
            $condition = $expression . " LIKE " . $like . " ESCAPE '!'";
            if ($index === 0) {
                $this->db->where($condition, null, false);
            } else {
                $this->db->or_where($condition, null, false);
            }
        }
        $this->db->group_end();
    }

    private function getInternalStatusLabelSql()
    {
        return "CASE
            WHEN f.status_file IS NULL OR TRIM(f.status_file) = '' OR UPPER(TRIM(f.status_file)) = 'NY' THEN 'NOT UPLOADED'
            WHEN UPPER(TRIM(f.status_file)) = 'UPLOADED' THEN 'ON REVIEW'
            ELSE UPPER(TRIM(f.status_file))
        END";
    }

    private function getAstriStatusLabelSql()
    {
        $effectiveStatus = $this->getEffectiveAstriStatusSql();

        return "CASE
            WHEN (" . $effectiveStatus . ") = 'NY' THEN 'NOT UPLOADED'
            WHEN (" . $effectiveStatus . ") = 'UPLOADED' THEN 'ON REVIEW'
            ELSE (" . $effectiveStatus . ")
        END";
    }

    private function getEffectiveAstriStatusSql()
    {
        return "CASE
            WHEN COALESCE(NULLIF(UPPER(TRIM(f.astri_status)), ''), 'NY') = 'NY'
                AND p.actual_atp_date IS NOT NULL
                AND CAST(p.actual_atp_date AS CHAR) NOT IN ('', '0000-00-00', '0000-00-00 00:00:00')
                AND UPPER(TRIM(g.scope_type)) = 'CLUSTER'
                AND UPPER(TRIM(g.sow_type)) = 'RFS'
                AND UPPER(TRIM(i.doc_name)) = 'PROJECT OPNAME'
            THEN 'WAITING WASPANG'
            ELSE COALESCE(NULLIF(UPPER(TRIM(f.astri_status)), ''), 'NY')
        END";
    }

    public function getClusterDetail($clusterId)
    {
        $row = $this->db
            ->select("
                c.id_cluster,
                c.cluster_name,
                c.homepass,
                c.status_rfs,
                mc.id_myrep_cluster,
                mt.id_target,
                mt.year_num,
                mt.month_num,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.chief,
                mt.id_user_pic_ho,
                mt.rpm,
                mt.sm,
                mt.spv,
                u_ho.nama_karyawan AS ho_pic_name,
                u_ho.telegram_user_id AS ho_pic_telegram_user_id,
                latest_claim.rfs_date
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_myrep_cluster mc', 'mc.rfs_cluster_id = c.id_cluster', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'inner')
            ->join('tb_master_user_new u_ho', 'u_ho.id = mt.id_user_pic_ho', 'left')
            ->join('(
                SELECT cluster_id, MAX(claim_date) AS rfs_date
                FROM tb_rfs_myrep_claim
                WHERE status_claim = "APPROVED"
                GROUP BY cluster_id
            ) latest_claim', 'latest_claim.cluster_id = c.id_cluster', 'left')
            ->where('c.id_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (!$row) {
            return [];
        }

        if (!$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return [];
        }

        $rows = $this->enrichClusterRows([$row]);
        return empty($rows) ? [] : $rows[0];
    }

    public function getClusterScopeTabs($clusterId, $includeHistory = true)
    {
        $clusterDetail = $this->getClusterDetail($clusterId);
        $groups = $this->getDocumentGroups();
        $items = $this->getDocumentItems();
        $packagesByCluster = $this->getPackagesByClusterIds([(int) $clusterId]);
        $clusterPackages = isset($packagesByCluster[(int) $clusterId]) ? $packagesByCluster[(int) $clusterId] : [];
        $linkedDocMap = $this->getChecklistLinkedDocumentMap((int) ($clusterDetail['id_myrep_cluster'] ?? 0), (int) $clusterId);
        $packageIds = [];

        foreach ($clusterPackages as $package) {
            if (!empty($package['id_doc_package'])) {
                $packageIds[] = (int) $package['id_doc_package'];
            }
        }

        $files = $this->getFilesByPackageIds($packageIds);
        $fileMap = [];
        foreach ($files as $file) {
            $fileMap[(int) $file['id_doc_package']][(int) $file['id_doc_item']] = $file;
        }

        $scopes = [
            'CLUSTER' => [],
            'SUBFEEDER' => [],
        ];

        foreach ($groups as $group) {
            $groupId = (int) $group['id_doc_group'];
            $package = isset($clusterPackages[$groupId]) ? $clusterPackages[$groupId] : [];
            $packageId = isset($package['id_doc_package']) ? (int) $package['id_doc_package'] : 0;
            $requiredDocs = isset($items[$groupId]) ? count($items[$groupId]) : 0;
            $uploadedDocs = 0;
            $approvedDocs = 0;
            $groupItems = [];
            $actualAtp = $this->normalizeDate($package['actual_atp_date'] ?? null);

            if (isset($items[$groupId])) {
                foreach ($items[$groupId] as $item) {
                    $itemFile = ($packageId > 0 && isset($fileMap[$packageId][(int) $item['id_doc_item']]))
                        ? $fileMap[$packageId][(int) $item['id_doc_item']]
                        : [];

                    $docKey = strtoupper(trim((string) ($item['doc_name'] ?? '')));
                    $linkedSource = ($scopeKey = strtoupper(trim((string) ($group['scope_type'] ?? '')))) === 'CLUSTER'
                        && strtoupper(trim((string) ($group['sow_type'] ?? ''))) === 'CW ATP'
                        && isset($linkedDocMap[$docKey])
                        ? $linkedDocMap[$docKey]
                        : [];

                    $effectiveStatus = (string) ($itemFile['status_file'] ?? 'NOT UPLOADED');
                    if (!empty($linkedSource['linked_source_file_id'])) {
                        $sourceStatus = (string) ($linkedSource['linked_source_status'] ?? '');
                        if ($sourceStatus !== '') {
                            $effectiveStatus = $sourceStatus;
                        } elseif ($effectiveStatus === 'NOT UPLOADED') {
                            $effectiveStatus = 'UPLOADED';
                        }
                    }

                    if ($this->isUploadedRow($itemFile) || !empty($linkedSource['linked_source_file_id'])) {
                        $uploadedDocs++;
                    }

                    if (strtoupper(trim($effectiveStatus)) === 'APPROVED') {
                        $approvedDocs++;
                    }

                    $groupItems[] = [
                        'id_doc_file' => (int) ($itemFile['id_doc_file'] ?? 0),
                        'id_doc_package' => $packageId,
                        'id_doc_item' => (int) $item['id_doc_item'],
                        'doc_name' => (string) $item['doc_name'],
                        'doc_requirement_note' => (string) ($item['doc_requirement_note'] ?? ''),
                        'format_file_name' => (string) ($item['format_file_name'] ?? ''),
                        'format_file_path' => (string) ($item['format_file_path'] ?? ''),
                        'verification_team' => (string) ($item['verification_team'] ?? ''),
                        'status_file' => $effectiveStatus,
                        'file_name' => (string) ($itemFile['file_name'] ?? ''),
                        'file_path' => (string) ($itemFile['file_path'] ?? ''),
                        'is_document_not_required' => (int) ($itemFile['is_document_not_required'] ?? 0),
                        'remark' => (string) ($itemFile['remark'] ?? ''),
                        'uploaded_at' => $this->normalizeDateTime($itemFile['uploaded_at'] ?? null),
                        'reviewed_at' => $this->normalizeDateTime($itemFile['reviewed_at'] ?? null),
                        'approved_at' => $this->normalizeDateTime($itemFile['approved_at'] ?? null),
                        'astri_submitted_date' => $this->normalizeDate($itemFile['astri_submitted_date'] ?? null),
                        'astri_status' => $this->getEffectiveAstriStatus(
                            $itemFile['astri_status'] ?? 'NY',
                            $actualAtp,
                            $group['scope_type'] ?? '',
                            $group['sow_type'] ?? '',
                            $item['doc_name'] ?? ''
                        ),
                        'astri_status_updated_at' => $this->normalizeDateTime($itemFile['astri_status_updated_at'] ?? null),
                        'astri_remark' => (string) ($itemFile['astri_remark'] ?? ''),
                        'is_special_project_opname' => $this->isSpecialProjectOpname(
                            $group['scope_type'] ?? '',
                            $group['sow_type'] ?? '',
                            $item['doc_name'] ?? ''
                        ) ? 1 : 0,
                        'verification_by' => $this->resolveVerificationDisplayName(
                            (string) ($item['verification_team'] ?? ''),
                            (string) ($clusterDetail['ho_pic_name'] ?? ''),
                            [
                                'city_name' => (string) ($clusterDetail['city_name'] ?? ''),
                                'province_name' => (string) ($clusterDetail['province_name'] ?? ''),
                                'regional_name' => (string) ($clusterDetail['regional_name'] ?? ''),
                            ]
                        ),
                        'linked_source_flow_type' => (string) ($linkedSource['linked_source_flow_type'] ?? ''),
                        'linked_source_group_label' => (string) ($linkedSource['linked_source_group_label'] ?? ''),
                        'linked_source_doc_name' => (string) ($linkedSource['linked_source_doc_name'] ?? ''),
                        'linked_source_file_id' => (int) ($linkedSource['linked_source_file_id'] ?? 0),
                        'linked_source_file_name' => (string) ($linkedSource['linked_source_file_name'] ?? ''),
                        'linked_source_file_path' => (string) ($linkedSource['linked_source_file_path'] ?? ''),
                        'linked_source_preview_path' => (string) ($linkedSource['linked_source_preview_path'] ?? ''),
                        'history' => [],
                    ];
                }
            }

            $tanggalRfs = $this->normalizeDate($package['tanggal_rfs'] ?? null);
            $planAtp = $this->normalizeDate($package['plan_atp_date'] ?? null);
            $planDoc = $this->normalizeDate($package['plan_submit_doc_date'] ?? null);
            $actualDoc = $this->normalizeDate($package['actual_submit_doc_date'] ?? null);

            if (!$planAtp && $tanggalRfs) {
                $planAtp = $this->addBusinessDays($tanggalRfs, 7);
            }

            if (!$planDoc) {
                if ($actualAtp) {
                    $planDoc = $this->addBusinessDays($actualAtp, 7);
                } elseif ($planAtp) {
                    $planDoc = $this->addBusinessDays($planAtp, 7);
                }
            }

            if (!$actualDoc && $requiredDocs > 0 && $uploadedDocs >= $requiredDocs) {
                $actualDoc = $this->extractLatestUploadedDate($groupItems);
            }

            $scopeKey = strtoupper((string) $group['scope_type']);
            $scopes[$scopeKey][] = [
                'id_doc_package' => $packageId,
                'group_label' => (string) $group['group_label'],
                'sow_type' => (string) $group['sow_type'],
                'required_docs' => $requiredDocs,
                'uploaded_docs' => $uploadedDocs,
                'approved_docs' => $approvedDocs,
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'actual_atp_date' => $actualAtp,
                'plan_submit_doc_date' => $planDoc,
                'actual_submit_doc_date' => $actualDoc,
                'aging_atp_days' => $this->calculateAgingDays($planAtp, $actualAtp),
                'aging_doc_days' => $this->calculateAgingDays($planDoc, $actualDoc),
                'status_package' => (string) ($package['status_package'] ?? $this->derivePackageStatus($uploadedDocs, $requiredDocs)),
                'remarks' => (string) ($package['remarks'] ?? ''),
                'items' => $groupItems,
            ];
        }

        if ($includeHistory) {
            $fileIds = [];
            foreach ($scopes as $scopeRows) {
                foreach ($scopeRows as $groupRow) {
                    foreach ($groupRow['items'] as $itemRow) {
                        if (!empty($itemRow['id_doc_file'])) {
                            $fileIds[] = (int) $itemRow['id_doc_file'];
                        }
                    }
                }
            }

            $historyByFileId = $this->getFileLogsByFileIds(array_values(array_unique($fileIds)));

            foreach ($scopes as &$scopeRows) {
                foreach ($scopeRows as &$groupRow) {
                    foreach ($groupRow['items'] as &$itemRow) {
                        $itemRow['history'] = !empty($itemRow['id_doc_file']) && isset($historyByFileId[(int) $itemRow['id_doc_file']])
                            ? $historyByFileId[(int) $itemRow['id_doc_file']]
                            : [];
                    }
                    unset($itemRow);
                }
                unset($groupRow);
            }
            unset($scopeRows);
        }

        return $scopes;
    }

    public function getCertificateTermRows($clusterId, $myrepClusterId)
    {
        $clusterId = (int) $clusterId;
        $myrepClusterId = (int) $myrepClusterId;
        if ($clusterId <= 0 || $myrepClusterId <= 0) {
            return [];
        }
        if (!$this->db->table_exists('tb_myrep_po_header') || !$this->db->table_exists('tb_myrep_po_termin')) {
            return [];
        }

        $this->ensurePoTerminCertificateColumn();
        $readiness = $this->getCertificateReadinessMap($clusterId);
        $termMap = $this->getCertificateTermMap();

        $select = '
            h.id_po_header,
            h.po_type,
            h.po_category,
            h.po_number,
            h.po_date,
            h.status_po,
            t.id_po_termin,
            t.termin_no,
            t.termin_percent,
            t.termin_value,
            t.status_termin,
            t.invoice_date
        ';
        if ($this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            $select .= ', t.sertifikat_invoice_date';
        }

        $rows = $this->db
            ->select($select, false)
            ->from('tb_myrep_po_header h')
            ->join('tb_myrep_po_termin t', 't.id_po_header = h.id_po_header', 'inner')
            ->where('h.id_myrep_cluster', $myrepClusterId)
            ->where('t.termin_no >=', 2)
            ->where('t.termin_no <=', 5)
            ->order_by('h.po_type', 'ASC')
            ->order_by('h.created_at', 'DESC')
            ->order_by('h.id_po_header', 'DESC')
            ->order_by('t.termin_no', 'ASC')
            ->get()
            ->result_array();

        $term4CertificateByHeader = [];
        foreach ($rows as $row) {
            if ((int) ($row['termin_no'] ?? 0) === 4) {
                $term4CertificateByHeader[(int) ($row['id_po_header'] ?? 0)] = (string) ($row['sertifikat_invoice_date'] ?? '');
            }
        }

        $result = [];
        foreach ($rows as $row) {
            $poType = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
            $terminNo = (int) ($row['termin_no'] ?? 0);
            $map = $termMap[$terminNo] ?? [];
            $sowType = strtoupper(trim((string) ($map['sow_type'] ?? '')));
            $readyKey = $poType . '|' . $sowType;
            $ready = $readiness[$readyKey] ?? [
                'required_docs' => 0,
                'submitted_docs' => 0,
                'approved_docs' => 0,
                'is_ready' => false,
            ];
            if ($terminNo === 5) {
                $ready = $this->buildFacReadiness((string) ($term4CertificateByHeader[(int) ($row['id_po_header'] ?? 0)] ?? ''));
            }

            $row['term_label'] = (string) ($map['label'] ?? ('Term ' . $terminNo));
            $row['sow_type'] = $sowType;
            $row['required_docs'] = (int) ($ready['required_docs'] ?? 0);
            $row['astri_submitted_docs'] = (int) ($ready['submitted_docs'] ?? 0);
            $row['astri_approved_docs'] = (int) ($ready['approved_docs'] ?? 0);
            $row['is_release_ready'] = !empty($ready['is_ready']);
            $row['fac_rfs_certificate_date'] = (string) ($ready['rfs_certificate_date'] ?? '');
            $row['fac_due_date'] = (string) ($ready['due_date'] ?? '');
            $row['fac_days_remaining'] = (int) ($ready['days_remaining'] ?? 0);
            $row['fac_days_since_due'] = (int) ($ready['days_since_due'] ?? 0);
            $row['fac_age_days'] = (int) ($ready['age_days'] ?? 0);
            $row['release_note'] = (string) $this->buildCertificateReleaseNote($terminNo, $row);
            $row['sertifikat_invoice_date'] = (string) ($row['sertifikat_invoice_date'] ?? '');
            $row['sertifikat_release_date'] = $this->normalizeCertificateDateValue((string) ($row['sertifikat_invoice_date'] ?? ''));
            $row['is_certificate_released'] = $row['sertifikat_release_date'] !== '';
            $result[] = $row;
        }

        return $result;
    }

    public function updateTerminCertificate($terminId, $certificateValue, $userId)
    {
        $terminId = (int) $terminId;
        if ($terminId <= 0 || !$this->db->table_exists('tb_myrep_po_termin')) {
            return false;
        }

        $this->ensurePoTerminCertificateColumn();
        $row = $this->db
            ->select('t.id_po_termin, mt.city_name')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header h', 'h.id_po_header = t.id_po_header', 'inner')
            ->join('tb_myrep_cluster mc', 'mc.id_myrep_cluster = h.id_myrep_cluster', 'inner')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = mc.rfs_cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('t.id_po_termin', $terminId)
            ->limit(1)
            ->get()
            ->row_array();
        if (!$row || !$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return false;
        }

        return $this->db
            ->where('id_po_termin', $terminId)
            ->update('tb_myrep_po_termin', [
                'sertifikat_invoice_date' => trim((string) $certificateValue) !== '' ? trim((string) $certificateValue) : null,
                'updated_by' => (int) $userId,
            ]);
    }

    public function normalizeCertificateDateForRelease($value)
    {
        return $this->normalizeCertificateDateValue($value);
    }

    private function getCertificateReadinessMap($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return [];
        }

        $rows = $this->db
            ->select("
                g.scope_type,
                g.sow_type,
                COUNT(i.id_doc_item) AS required_docs,
                SUM(CASE WHEN f.astri_submitted_date IS NOT NULL AND f.astri_submitted_date <> '0000-00-00' AND COALESCE(f.astri_status, 'NY') <> 'NY' THEN 1 ELSE 0 END) AS submitted_docs,
                SUM(CASE WHEN COALESCE(f.astri_status, 'NY') = 'APPROVED' THEN 1 ELSE 0 END) AS approved_docs
            ", false)
            ->from('md_rfs_myrep_doc_group g')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_group = g.id_doc_group AND p.cluster_id = ' . (int) $clusterId, 'left', false)
            ->join('tb_rfs_myrep_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('g.is_active', 1)
            ->where_in('g.scope_type', ['CLUSTER', 'SUBFEEDER'])
            ->where_in('g.sow_type', ['CW ATP', 'FULL OPM', 'RFS'])
            ->group_by(['g.scope_type', 'g.sow_type'])
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $required = (int) ($row['required_docs'] ?? 0);
            $submitted = (int) ($row['submitted_docs'] ?? 0);
            $approved = (int) ($row['approved_docs'] ?? 0);
            $key = strtoupper(trim((string) ($row['scope_type'] ?? ''))) . '|' . strtoupper(trim((string) ($row['sow_type'] ?? '')));
            $map[$key] = [
                'required_docs' => $required,
                'submitted_docs' => $submitted,
                'approved_docs' => $approved,
                'is_ready' => $required > 0 && $submitted >= $required && $approved >= $required,
            ];
        }

        return $map;
    }

    private function getCertificateTermMap()
    {
        return [
            2 => ['sow_type' => 'CW ATP', 'label' => 'Term 2 - CW ATP'],
            3 => ['sow_type' => 'FULL OPM', 'label' => 'Term 3 - FULL OPM'],
            4 => ['sow_type' => 'RFS', 'label' => 'Term 4 - RFS'],
            5 => ['sow_type' => 'FAC', 'label' => 'Term 5 - FAC'],
        ];
    }

    private function buildFacReadiness($rfsCertificateValue)
    {
        $rfsCertificateDate = $this->normalizeCertificateDateValue($rfsCertificateValue);
        if ($rfsCertificateDate === '') {
            return [
                'required_docs' => 0,
                'submitted_docs' => 0,
                'approved_docs' => 0,
                'is_ready' => false,
                'rfs_certificate_date' => '',
                'due_date' => '',
                'days_remaining' => 0,
                'days_since_due' => 0,
                'age_days' => 0,
            ];
        }

        $dueDate = date('Y-m-d', strtotime($rfsCertificateDate . ' +90 days'));
        $today = date('Y-m-d');
        $daysRemaining = (int) ceil((strtotime($dueDate) - strtotime($today)) / 86400);
        $ageDays = max(0, (int) floor((strtotime($today) - strtotime($rfsCertificateDate)) / 86400));

        return [
            'required_docs' => 0,
            'submitted_docs' => 0,
            'approved_docs' => 0,
            'is_ready' => $daysRemaining <= 0,
            'rfs_certificate_date' => $rfsCertificateDate,
            'due_date' => $dueDate,
            'days_remaining' => max(0, $daysRemaining),
            'days_since_due' => max(0, abs($daysRemaining)),
            'age_days' => $ageDays,
        ];
    }

    private function buildCertificateReleaseNote($terminNo, array $row)
    {
        $terminNo = (int) $terminNo;
        if ($terminNo === 5) {
            if ((string) ($row['fac_rfs_certificate_date'] ?? '') === '') {
                return 'NY FAC. Menunggu tanggal sertifikat RFS term 4 yang valid.';
            }
            if (!empty($row['is_release_ready'])) {
                return 'Ready Release. Lewat BJT ' . (int) ($row['fac_days_since_due'] ?? 0) . ' hari. Umur dari sertifikat RFS: ' . (int) ($row['fac_age_days'] ?? 0) . ' hari.';
            }

            return 'BJT pada ' . $this->formatCertificateDate((string) ($row['fac_due_date'] ?? '')) . ' (' . (int) ($row['fac_days_remaining'] ?? 0) . ' hari lagi).';
        }

        $required = (int) ($row['required_docs'] ?? 0);
        $submitted = (int) ($row['astri_submitted_docs'] ?? 0);
        $approved = (int) ($row['astri_approved_docs'] ?? 0);
        if (!empty($row['is_release_ready'])) {
            return 'Siap release sertifikat.';
        }

        return 'Menunggu ASTRI submitted ' . $submitted . '/' . $required . ' dan approved ' . $approved . '/' . $required . '.';
    }

    private function normalizeCertificateDateValue($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', strtotime($value));
        }
        if (preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $value)) {
            $timestamp = strtotime($value);
            return $timestamp ? date('Y-m-d', $timestamp) : '';
        }

        return '';
    }

    private function formatCertificateDate($date)
    {
        $date = $this->normalizeCertificateDateValue($date);
        return $date !== '' ? date('d/m/Y', strtotime($date)) : '-';
    }

    private function ensurePoTerminCertificateColumn()
    {
        if (!$this->db->table_exists('tb_myrep_po_termin')) {
            return;
        }
        if (!$this->db->field_exists('sertifikat_invoice_date', 'tb_myrep_po_termin')) {
            $this->db->query('ALTER TABLE `tb_myrep_po_termin` ADD COLUMN `sertifikat_invoice_date` VARCHAR(150) NULL AFTER `invoice_date`');
            return;
        }

        $field = $this->db
            ->query("
                SELECT DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'tb_myrep_po_termin'
                  AND COLUMN_NAME = 'sertifikat_invoice_date'
                LIMIT 1
            ")
            ->row_array();
        $dataType = strtolower((string) ($field['DATA_TYPE'] ?? ''));
        if ($dataType !== '' && !in_array($dataType, ['varchar', 'char', 'text', 'mediumtext', 'longtext'], true)) {
            $this->db->query('ALTER TABLE `tb_myrep_po_termin` MODIFY COLUMN `sertifikat_invoice_date` VARCHAR(150) NULL');
        }
    }

    public function updatePackageTimeline($packageId, $data)
    {
        $package = $this->db->get_where('tb_rfs_myrep_doc_package', [
            'id_doc_package' => (int) $packageId,
        ])->row_array();

        if (!$package) {
            return false;
        }

        $tanggalRfs = !empty($data['tanggal_rfs']) ? $data['tanggal_rfs'] : $this->normalizeDate($package['tanggal_rfs'] ?? null);
        $actualAtp = !empty($data['actual_atp_date']) ? $data['actual_atp_date'] : null;
        $planAtp = $tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null;
        $planDoc = null;
        if ($actualAtp) {
            $planDoc = $this->addBusinessDays($actualAtp, 7);
        } elseif ($planAtp) {
            $planDoc = $this->addBusinessDays($planAtp, 7);
        }

        $result = $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_rfs_myrep_doc_package', [
                'tanggal_rfs' => $tanggalRfs,
                'plan_atp_date' => $planAtp,
                'actual_atp_date' => $actualAtp,
                'plan_submit_doc_date' => $planDoc,
                'remarks' => $data['remarks'],
                'updated_by' => (int) $data['updated_by'],
            ]);

        if ($result) {
            $this->syncProjectOpnameAstriStatusByPackage((int) $packageId, $actualAtp);
        }

        return $result;
    }

    public function updateClusterTimeline($clusterId, $data)
    {
        $packages = $this->db
            ->get_where('tb_rfs_myrep_doc_package', [
                'cluster_id' => (int) $clusterId,
            ])
            ->result_array();

        if (empty($packages)) {
            return false;
        }

        $actualAtp = !empty($data['actual_atp_date']) ? $data['actual_atp_date'] : null;
        $updatedBy = (int) $data['updated_by'];

        foreach ($packages as $package) {
            $packageId = (int) $package['id_doc_package'];
            $tanggalRfs = $this->normalizeDate($package['tanggal_rfs'] ?? null);
            $planAtp = $tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null;
            $planDoc = null;

            if ($actualAtp) {
                $planDoc = $this->addBusinessDays($actualAtp, 7);
            } elseif ($planAtp) {
                $planDoc = $this->addBusinessDays($planAtp, 7);
            }

            $this->db
                ->where('id_doc_package', $packageId)
                ->update('tb_rfs_myrep_doc_package', [
                    'plan_atp_date' => $planAtp,
                    'actual_atp_date' => $actualAtp,
                    'plan_submit_doc_date' => $planDoc,
                    'updated_by' => $updatedBy,
                ]);

            $this->syncProjectOpnameAstriStatusByPackage($packageId, $actualAtp);
        }

        return true;
    }

    public function saveFileUpload($data)
    {
        if (!$this->isRfsPackageAllowed((int) ($data['id_doc_package'] ?? 0))) {
            return 0;
        }

        $existing = $this->db->get_where('tb_rfs_myrep_doc_file', [
            'id_doc_package' => (int) $data['id_doc_package'],
            'id_doc_item' => (int) $data['id_doc_item'],
        ])->row_array();

        $payload = [
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => $data['status_file'],
            'remark' => $data['remark'],
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
                ->update('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file'];
            $this->createFileLog([
                'id_doc_file' => $fileId,
                'id_doc_package' => (int) $data['id_doc_package'],
                'id_doc_item' => (int) $data['id_doc_item'],
                'action_type' => 'REUPLOADED',
                'status_after' => $data['status_file'],
                'file_name' => $data['file_name'] !== '' ? $data['file_name'] : '[Tanpa Dokumen]',
                'remark' => $data['remark'],
                'action_by' => (int) $data['uploaded_by'],
            ]);
        } else {
            $payload['id_doc_package'] = (int) $data['id_doc_package'];
            $payload['id_doc_item'] = (int) $data['id_doc_item'];
            $this->db->insert('tb_rfs_myrep_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $this->createFileLog([
                'id_doc_file' => $fileId,
                'id_doc_package' => (int) $data['id_doc_package'],
                'id_doc_item' => (int) $data['id_doc_item'],
                'action_type' => 'UPLOADED',
                'status_after' => $data['status_file'],
                'file_name' => $data['file_name'] !== '' ? $data['file_name'] : '[Tanpa Dokumen]',
                'remark' => $data['remark'],
                'action_by' => (int) $data['uploaded_by'],
            ]);
        }

        $this->refreshPackageStatus((int) $data['id_doc_package']);
        return $fileId;
    }

    public function updateFileStatus($fileId, $data)
    {
        $file = $this->db
            ->select('f.*, mt.city_name')
            ->from('tb_rfs_myrep_doc_file f')
            ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = p.cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$file || !$this->isCityAllowedForCurrentUser((string) ($file['city_name'] ?? ''))) {
            return false;
        }

        $payload = [
            'status_file' => $data['status_file'],
            'remark' => $data['remark'],
            'approved_by' => (int) $data['approved_by'],
            'reviewed_at' => date('Y-m-d H:i:s'),
            'approved_at' => $data['status_file'] === 'APPROVED' ? date('Y-m-d H:i:s') : null,
        ];

        $result = $this->db
            ->where('id_doc_file', (int) $fileId)
            ->update('tb_rfs_myrep_doc_file', $payload);

        $this->createFileLog([
            'id_doc_file' => (int) $fileId,
            'id_doc_package' => (int) $file['id_doc_package'],
            'id_doc_item' => (int) $file['id_doc_item'],
            'action_type' => $data['status_file'],
            'status_after' => $data['status_file'],
            'file_name' => (string) ($file['file_name'] ?? ''),
            'remark' => $data['remark'],
            'action_by' => (int) $data['approved_by'],
        ]);

        $this->refreshPackageStatus((int) $file['id_doc_package']);

        if ($result && $data['status_file'] === 'APPROVED') {
            $this->syncProjectOpnameAstriStatusByPackage((int) $file['id_doc_package']);
        }

        return $result;
    }

    public function updateAstriStatus($fileId, $data)
    {
        $file = $this->db
            ->select('f.id_doc_file, mt.city_name')
            ->from('tb_rfs_myrep_doc_file f')
            ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = p.cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$file || !$this->isCityAllowedForCurrentUser((string) ($file['city_name'] ?? ''))) {
            return false;
        }

        $status = (string) $data['astri_status'];
        $submittedDate = !empty($data['astri_submitted_date']) ? $data['astri_submitted_date'] : null;
        $updatedAt = $status === 'NY' ? null : date('Y-m-d H:i:s');

        return $this->db
            ->where('id_doc_file', (int) $fileId)
            ->update('tb_rfs_myrep_doc_file', [
                'astri_submitted_date' => $submittedDate,
                'astri_status' => $status,
                'astri_status_updated_at' => $updatedAt,
                'astri_remark' => (string) $data['astri_remark'],
            ]);
    }

    public function getFileById($fileId)
    {
        $row = $this->db
            ->select('
                f.*,
                p.cluster_id,
                p.actual_atp_date,
                g.scope_type,
                g.sow_type,
                i.doc_name,
                mt.city_name
            ')
            ->from('tb_rfs_myrep_doc_file f')
            ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = i.id_doc_group', 'left')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = p.cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('f.id_doc_file', (int) $fileId)
            ->get()
            ->row_array();

        if (!$row) {
            return [];
        }

        if (!$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return [];
        }

        $row['cluster_actual_atp_date'] = null;
        if (!empty($row['cluster_id'])) {
            $clusterAtp = $this->db
                ->select('MAX(actual_atp_date) AS cluster_actual_atp_date', false)
                ->from('tb_rfs_myrep_doc_package')
                ->where('cluster_id', (int) $row['cluster_id'])
                ->get()
                ->row_array();
            $row['cluster_actual_atp_date'] = $this->normalizeDate($clusterAtp['cluster_actual_atp_date'] ?? null);
        }

        $row['astri_status'] = $this->getEffectiveAstriStatus(
            $row['astri_status'] ?? 'NY',
            $row['cluster_actual_atp_date'] ?? ($row['actual_atp_date'] ?? null),
            $row['scope_type'] ?? '',
            $row['sow_type'] ?? '',
            $row['doc_name'] ?? ''
        );
        $row['is_special_project_opname'] = $this->isSpecialProjectOpname(
            $row['scope_type'] ?? '',
            $row['sow_type'] ?? '',
            $row['doc_name'] ?? ''
        ) ? 1 : 0;

        return $row;
    }

    private function syncProjectOpnameAstriStatusByPackage($packageId, $actualAtpDate = null)
    {
        $package = $this->db
            ->select('p.id_doc_package, p.actual_atp_date, g.scope_type, g.sow_type')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
            ->where('p.id_doc_package', (int) $packageId)
            ->get()
            ->row_array();

        if (!$package || strtoupper((string) $package['scope_type']) !== 'CLUSTER' || strtoupper((string) $package['sow_type']) !== 'RFS') {
            return;
        }

        $actualAtpDate = $actualAtpDate !== null ? $actualAtpDate : $this->normalizeDate($package['actual_atp_date'] ?? null);

        $file = $this->db
            ->select('f.id_doc_file, f.astri_status, f.status_file')
            ->from('tb_rfs_myrep_doc_file f')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_item = f.id_doc_item', 'inner')
            ->where('f.id_doc_package', (int) $packageId)
            ->where('UPPER(i.doc_name)', 'PROJECT OPNAME')
            ->get()
            ->row_array();

        if (!$file) {
            return;
        }

        $currentStatus = strtoupper(trim((string) ($file['astri_status'] ?? 'NY')));
        $currentFileStatus = strtoupper(trim((string) ($file['status_file'] ?? '')));

        if ($actualAtpDate && $currentStatus === 'NY' && $currentFileStatus === 'APPROVED') {
            $this->db
                ->where('id_doc_file', (int) $file['id_doc_file'])
                ->update('tb_rfs_myrep_doc_file', [
                    'astri_status' => 'WAITING WASPANG',
                    'astri_status_updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        if (!$actualAtpDate && $currentStatus === 'WAITING WASPANG') {
            $this->db
                ->where('id_doc_file', (int) $file['id_doc_file'])
                ->update('tb_rfs_myrep_doc_file', [
                    'astri_status' => 'NY',
                    'astri_status_updated_at' => null,
                    'astri_submitted_date' => null,
                ]);
        }
    }

    public function getFileLogsByFileIds($fileIds)
    {
        if (empty($fileIds)) {
            return [];
        }

        $rows = $this->db
            ->select('l.*, u.nama_karyawan AS nama_user')
            ->from('tb_rfs_myrep_doc_file_log l')
            ->join('tb_master_user_new u', 'u.id = l.action_by', 'left')
            ->where_in('l.id_doc_file', $fileIds)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log', 'DESC')
            ->get()
            ->result_array();

        $logs = [];
        foreach ($rows as $row) {
            $logs[(int) $row['id_doc_file']][] = $row;
        }

        return $logs;
    }

    public function getTargetOptions($city = '', $regional = '')
    {
        $query = $this->db
            ->select('MAX(id_target) AS id_target, city_name, regional_name, province_name', false)
            ->from('tb_rfs_myrep_monthly_target');

        if (!$this->applyAllowedCityRestriction('city_name')) {
            return [];
        }

        if ($city !== '') {
            $query->where('UPPER(city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $query->where('UPPER(regional_name)', strtoupper($regional));
        }

        return $query
            ->group_by(['city_name', 'regional_name', 'province_name'])
            ->order_by('regional_name', 'ASC')
            ->order_by('city_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function saveMainfeeder($data)
    {
        $this->db->insert('tb_rfs_myrep_mainfeeder', [
            'id_target' => (int) $data['id_target'],
            'mainfeeder_name' => $data['mainfeeder_name'],
            'length_meter' => (float) $data['length_meter'],
            'atp_date' => $data['atp_date'],
            'created_by' => (int) $data['created_by'],
            'updated_by' => (int) $data['updated_by'],
        ]);

        $mainfeederId = (int) $this->db->insert_id();
        $this->ensureMainfeederPackages($mainfeederId, $data['atp_date']);

        return $mainfeederId;
    }

    public function getMainfeederList($city = '', $regional = '')
    {
        $query = $this->db
            ->select('
                mf.id_mainfeeder,
                mf.id_target,
                mf.mainfeeder_name,
                mf.length_meter,
                mf.atp_date,
                mt.city_name,
                mt.regional_name,
                mt.province_name
            ')
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'inner');

        if (!$this->applyAllowedCityRestriction('mt.city_name')) {
            return [];
        }

        if ($city !== '') {
            $query->where('UPPER(mt.city_name)', strtoupper($city));
        }

        if ($regional !== '') {
            $query->where('UPPER(mt.regional_name)', strtoupper($regional));
        }

        $rows = $query
            ->order_by('mt.city_name', 'ASC')
            ->order_by('mf.mainfeeder_name', 'ASC')
            ->get()
            ->result_array();

        return $this->enrichMainfeederRows($rows);
    }

    public function getMainfeederDetail($mainfeederId)
    {
        $row = $this->db
            ->select('
                mf.id_mainfeeder,
                mf.id_target,
                mf.mainfeeder_name,
                mf.length_meter,
                mf.atp_date,
                mt.city_name,
                mt.regional_name,
                mt.province_name,
                mt.id_user_pic_ho,
                u_ho.nama_karyawan AS ho_pic_name,
                u_ho.telegram_user_id AS ho_pic_telegram_user_id
            ')
            ->from('tb_rfs_myrep_mainfeeder mf')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'inner')
            ->join('tb_master_user_new u_ho', 'u_ho.id = mt.id_user_pic_ho', 'left')
            ->where('mf.id_mainfeeder', (int) $mainfeederId)
            ->get()
            ->row_array();

        if (!$row) {
            return [];
        }

        if (!$this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''))) {
            return [];
        }

        $rows = $this->enrichMainfeederRows([$row]);
        return empty($rows) ? [] : $rows[0];
    }

    public function ensureMainfeederPackages($mainfeederId, $atpDate = null)
    {
        $groups = $this->getMainfeederDocumentGroups();
        foreach ($groups as $group) {
            $existing = $this->db->get_where('tb_rfs_myrep_mainfeeder_doc_package', [
                'id_mainfeeder' => (int) $mainfeederId,
                'id_doc_group_mainfeeder' => (int) $group['id_doc_group_mainfeeder'],
            ])->row_array();

            if ($existing) {
                continue;
            }

            $planDoc = !empty($atpDate) ? $this->addBusinessDays($atpDate, 7) : null;
            $this->db->insert('tb_rfs_myrep_mainfeeder_doc_package', [
                'id_mainfeeder' => (int) $mainfeederId,
                'id_doc_group_mainfeeder' => (int) $group['id_doc_group_mainfeeder'],
                'atp_date' => $atpDate,
                'plan_submit_doc_date' => $planDoc,
                'status_package' => 'NOT STARTED',
                'created_by' => (int) $this->session->userdata('id_user'),
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
        }
    }

    public function getMainfeederGroupRows($mainfeederId)
    {
        $groups = $this->getMainfeederDocumentGroups();
        $items = $this->getMainfeederDocumentItems();
        $packages = $this->getMainfeederPackagesByIds([(int) $mainfeederId]);
        $mainfeederPackages = isset($packages[(int) $mainfeederId]) ? $packages[(int) $mainfeederId] : [];
        $packageIds = [];

        foreach ($mainfeederPackages as $package) {
            $packageIds[] = (int) $package['id_doc_package_mainfeeder'];
        }

        $files = $this->getMainfeederFilesByPackageIds($packageIds);
        $fileMap = [];
        foreach ($files as $file) {
            $fileMap[(int) $file['id_doc_package_mainfeeder']][(int) $file['id_doc_item_mainfeeder']] = $file;
        }

        $fileIds = [];
        $rows = [];

        foreach ($groups as $group) {
            $groupId = (int) $group['id_doc_group_mainfeeder'];
            $package = isset($mainfeederPackages[$groupId]) ? $mainfeederPackages[$groupId] : [];
            $packageId = (int) ($package['id_doc_package_mainfeeder'] ?? 0);
            $groupItems = [];
            $requiredDocs = isset($items[$groupId]) ? count($items[$groupId]) : 0;
            $uploadedDocs = 0;

            foreach ($items[$groupId] ?? [] as $item) {
                $itemFile = ($packageId > 0 && isset($fileMap[$packageId][(int) $item['id_doc_item_mainfeeder']]))
                    ? $fileMap[$packageId][(int) $item['id_doc_item_mainfeeder']]
                    : [];

                if ($this->isUploadedRow($itemFile)) {
                    $uploadedDocs++;
                }

                if (!empty($itemFile['id_doc_file_mainfeeder'])) {
                    $fileIds[] = (int) $itemFile['id_doc_file_mainfeeder'];
                }

                $groupItems[] = [
                    'id_doc_file_mainfeeder' => (int) ($itemFile['id_doc_file_mainfeeder'] ?? 0),
                    'id_doc_package_mainfeeder' => $packageId,
                    'id_doc_item_mainfeeder' => (int) $item['id_doc_item_mainfeeder'],
                    'doc_name' => (string) $item['doc_name'],
                    'status_file' => (string) ($itemFile['status_file'] ?? 'NOT UPLOADED'),
                    'file_name' => (string) ($itemFile['file_name'] ?? ''),
                    'file_path' => (string) ($itemFile['file_path'] ?? ''),
                    'is_document_not_required' => (int) ($itemFile['is_document_not_required'] ?? 0),
                    'remark' => (string) ($itemFile['remark'] ?? ''),
                    'uploaded_at' => $this->normalizeDateTime($itemFile['uploaded_at'] ?? null),
                    'reviewed_at' => $this->normalizeDateTime($itemFile['reviewed_at'] ?? null),
                    'approved_at' => $this->normalizeDateTime($itemFile['approved_at'] ?? null),
                    'astri_submitted_date' => $this->normalizeDate($itemFile['astri_submitted_date'] ?? null),
                    'astri_status' => (string) ($itemFile['astri_status'] ?? 'NY'),
                    'astri_status_updated_at' => $this->normalizeDateTime($itemFile['astri_status_updated_at'] ?? null),
                    'astri_remark' => (string) ($itemFile['astri_remark'] ?? ''),
                    'history' => [],
                ];
            }

            $actualDoc = $this->normalizeDate($package['actual_submit_doc_date'] ?? null);
            if (!$actualDoc && $requiredDocs > 0 && $uploadedDocs >= $requiredDocs) {
                $actualDoc = $this->extractLatestUploadedDate($groupItems);
            }

            $rows[] = [
                'id_doc_package_mainfeeder' => $packageId,
                'group_label' => (string) $group['group_label'],
                'sow_type' => (string) $group['sow_type'],
                'atp_date' => $this->normalizeDate($package['atp_date'] ?? null),
                'plan_submit_doc_date' => $this->normalizeDate($package['plan_submit_doc_date'] ?? null),
                'actual_submit_doc_date' => $actualDoc,
                'aging_doc_days' => $this->calculateAgingDays($package['plan_submit_doc_date'] ?? null, $actualDoc),
                'required_docs' => $requiredDocs,
                'uploaded_docs' => $uploadedDocs,
                'items' => $groupItems,
            ];
        }

        $historyByFileId = $this->getMainfeederFileLogsByFileIds(array_values(array_unique($fileIds)));
        foreach ($rows as &$row) {
            foreach ($row['items'] as &$item) {
                $item['history'] = !empty($item['id_doc_file_mainfeeder']) && isset($historyByFileId[(int) $item['id_doc_file_mainfeeder']])
                    ? $historyByFileId[(int) $item['id_doc_file_mainfeeder']]
                    : [];
            }
            unset($item);
        }
        unset($row);

        return $rows;
    }

    public function saveMainfeederFileUpload($data)
    {
        if (!$this->isMainfeederPackageAllowed((int) ($data['id_doc_package_mainfeeder'] ?? 0))) {
            return 0;
        }

        $existing = $this->db->get_where('tb_rfs_myrep_mainfeeder_doc_file', [
            'id_doc_package_mainfeeder' => (int) $data['id_doc_package_mainfeeder'],
            'id_doc_item_mainfeeder' => (int) $data['id_doc_item_mainfeeder'],
        ])->row_array();

        $payload = [
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'is_document_not_required' => !empty($data['is_document_not_required']) ? 1 : 0,
            'status_file' => $data['status_file'],
            'remark' => $data['remark'],
            'uploaded_by' => (int) $data['uploaded_by'],
            'uploaded_at' => date('Y-m-d H:i:s'),
            'approved_by' => null,
            'reviewed_at' => null,
            'approved_at' => null,
        ];

        if ($existing) {
            $this->deletePhysicalFile($existing['file_path'] ?? '');
            $this->db->where('id_doc_file_mainfeeder', (int) $existing['id_doc_file_mainfeeder'])
                ->update('tb_rfs_myrep_mainfeeder_doc_file', $payload);
            $fileId = (int) $existing['id_doc_file_mainfeeder'];
            $actionType = 'REUPLOADED';
        } else {
            $payload['id_doc_package_mainfeeder'] = (int) $data['id_doc_package_mainfeeder'];
            $payload['id_doc_item_mainfeeder'] = (int) $data['id_doc_item_mainfeeder'];
            $this->db->insert('tb_rfs_myrep_mainfeeder_doc_file', $payload);
            $fileId = (int) $this->db->insert_id();
            $actionType = 'UPLOADED';
        }

        $this->db->insert('tb_rfs_myrep_mainfeeder_doc_file_log', [
            'id_doc_file_mainfeeder' => $fileId,
            'id_doc_package_mainfeeder' => (int) $data['id_doc_package_mainfeeder'],
            'id_doc_item_mainfeeder' => (int) $data['id_doc_item_mainfeeder'],
            'action_type' => $actionType,
            'status_after' => $data['status_file'],
            'file_name' => $data['file_name'] !== '' ? $data['file_name'] : '[Tanpa Dokumen]',
            'remark' => $data['remark'],
            'action_by' => (int) $data['uploaded_by'],
            'action_at' => date('Y-m-d H:i:s'),
        ]);

        $this->refreshMainfeederPackageStatus((int) $data['id_doc_package_mainfeeder']);
        return $fileId;
    }

    public function updateMainfeederFileStatus($fileId, $data)
    {
        $file = $this->db
            ->select('f.*, mt.city_name')
            ->from('tb_rfs_myrep_mainfeeder_doc_file f')
            ->join('tb_rfs_myrep_mainfeeder_doc_package p', 'p.id_doc_package_mainfeeder = f.id_doc_package_mainfeeder', 'left')
            ->join('tb_rfs_myrep_mainfeeder mf', 'mf.id_mainfeeder = p.id_mainfeeder', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('f.id_doc_file_mainfeeder', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$file || !$this->isCityAllowedForCurrentUser((string) ($file['city_name'] ?? ''))) {
            return false;
        }

        $this->db->where('id_doc_file_mainfeeder', (int) $fileId)
            ->update('tb_rfs_myrep_mainfeeder_doc_file', [
                'status_file' => $data['status_file'],
                'remark' => $data['remark'],
                'approved_by' => (int) $data['approved_by'],
                'reviewed_at' => date('Y-m-d H:i:s'),
                'approved_at' => $data['status_file'] === 'APPROVED' ? date('Y-m-d H:i:s') : null,
            ]);

        $this->db->insert('tb_rfs_myrep_mainfeeder_doc_file_log', [
            'id_doc_file_mainfeeder' => (int) $fileId,
            'id_doc_package_mainfeeder' => (int) $file['id_doc_package_mainfeeder'],
            'id_doc_item_mainfeeder' => (int) $file['id_doc_item_mainfeeder'],
            'action_type' => $data['status_file'],
            'status_after' => $data['status_file'],
            'file_name' => (string) ($file['file_name'] ?? ''),
            'remark' => $data['remark'],
            'action_by' => (int) $data['approved_by'],
            'action_at' => date('Y-m-d H:i:s'),
        ]);

        $this->refreshMainfeederPackageStatus((int) $file['id_doc_package_mainfeeder']);
        return true;
    }

    public function updateMainfeederAstriStatus($fileId, $data)
    {
        $file = $this->db
            ->select('f.id_doc_file_mainfeeder, mt.city_name')
            ->from('tb_rfs_myrep_mainfeeder_doc_file f')
            ->join('tb_rfs_myrep_mainfeeder_doc_package p', 'p.id_doc_package_mainfeeder = f.id_doc_package_mainfeeder', 'left')
            ->join('tb_rfs_myrep_mainfeeder mf', 'mf.id_mainfeeder = p.id_mainfeeder', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('f.id_doc_file_mainfeeder', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($file) || !$this->isCityAllowedForCurrentUser((string) ($file['city_name'] ?? ''))) {
            return false;
        }

        return $this->db
            ->where('id_doc_file_mainfeeder', (int) $fileId)
            ->update('tb_rfs_myrep_mainfeeder_doc_file', [
                'astri_submitted_date' => $data['astri_submitted_date'],
                'astri_status' => $data['astri_status'],
                'astri_status_updated_at' => $data['astri_status'] === 'NY' ? null : date('Y-m-d H:i:s'),
                'astri_remark' => $data['astri_remark'],
            ]);
    }

    public function getMainfeederFileById($fileId)
    {
        $row = $this->db
            ->select('f.*, mt.city_name')
            ->from('tb_rfs_myrep_mainfeeder_doc_file f')
            ->join('tb_rfs_myrep_mainfeeder_doc_package p', 'p.id_doc_package_mainfeeder = f.id_doc_package_mainfeeder', 'left')
            ->join('tb_rfs_myrep_mainfeeder mf', 'mf.id_mainfeeder = p.id_mainfeeder', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('f.id_doc_file_mainfeeder', (int) $fileId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? '')) ? $row : [];
    }

    public function getMainfeederFileLogsByFileIds($fileIds)
    {
        if (empty($fileIds)) {
            return [];
        }

        $rows = $this->db
            ->select('l.*, u.nama_karyawan AS nama_user')
            ->from('tb_rfs_myrep_mainfeeder_doc_file_log l')
            ->join('tb_master_user_new u', 'u.id = l.action_by', 'left')
            ->where_in('l.id_doc_file_mainfeeder', $fileIds)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_doc_file_log_mainfeeder', 'DESC')
            ->get()
            ->result_array();

        $logs = [];
        foreach ($rows as $row) {
            $logs[(int) $row['id_doc_file_mainfeeder']][] = $row;
        }

        return $logs;
    }

    private function enrichMainfeederRows($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $ids = array_map(function ($row) {
            return (int) $row['id_mainfeeder'];
        }, $rows);

        $groups = $this->getMainfeederDocumentGroups();
        $packages = $this->getMainfeederPackagesByIds($ids);
        $packageIds = [];
        foreach ($packages as $mfPackages) {
            foreach ($mfPackages as $package) {
                $packageIds[] = (int) $package['id_doc_package_mainfeeder'];
            }
        }

        $fileSummary = $this->getMainfeederFileSummaryByPackageIds($packageIds);
        $fileStatusSummary = $this->getMainfeederFileStatusSummaryByPackageIds($packageIds);
        $result = [];

        foreach ($rows as $row) {
            $mfId = (int) $row['id_mainfeeder'];
            $mfPackages = isset($packages[$mfId]) ? $packages[$mfId] : [];
            $docSummary = [
                'CW ATP' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
                'FULL OPM' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
                'RFS' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
            ];
            $planDocDates = [];
            $actualDocDates = [];
            $astriSubmittedDates = [];
            $astriApprovedDates = [];
            $totalRequired = 0;
            $totalAstriSubmitted = 0;
            $totalAstriApproved = 0;

            foreach ($groups as $group) {
                $groupId = (int) $group['id_doc_group_mainfeeder'];
                $package = isset($mfPackages[$groupId]) ? $mfPackages[$groupId] : [];
                $packageId = (int) ($package['id_doc_package_mainfeeder'] ?? 0);
                $required = (int) $group['required_docs'];
                $uploaded = ($packageId > 0 && isset($fileSummary[$packageId])) ? (int) $fileSummary[$packageId]['uploaded_docs'] : 0;
                $statusSummary = ($packageId > 0 && isset($fileStatusSummary[$packageId])) ? $fileStatusSummary[$packageId] : [
                    'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'existing' => 0,
                    'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_submitted' => 0,
                    'astri_latest_submitted_date' => null, 'astri_latest_approved_date' => null,
                ];
                $sowType = (string) $group['sow_type'];

                $docSummary[$sowType]['required'] += $required;
                $docSummary[$sowType]['uploaded'] += min($uploaded, $required);
                $docSummary[$sowType]['approved'] += (int) $statusSummary['approved'];
                $docSummary[$sowType]['on_review'] += (int) $statusSummary['on_review'];
                $docSummary[$sowType]['rejected'] += (int) $statusSummary['rejected'];
                $docSummary[$sowType]['ny'] += max(0, $required - (int) $statusSummary['existing']);
                $docSummary[$sowType]['astri_submitted'] += (int) $statusSummary['astri_submitted'];
                $docSummary[$sowType]['astri_approved'] += (int) $statusSummary['astri_approved'];
                $docSummary[$sowType]['astri_on_review'] += (int) $statusSummary['astri_on_review'];
                $docSummary[$sowType]['astri_rejected'] += (int) $statusSummary['astri_rejected'];
                $docSummary[$sowType]['astri_ny'] += max(0, $required - (int) $statusSummary['astri_submitted']);

                $totalRequired += $required;
                $totalAstriSubmitted += (int) $statusSummary['astri_submitted'];
                $totalAstriApproved += (int) $statusSummary['astri_approved'];
                if (!empty($package['plan_submit_doc_date'])) {
                    $planDocDates[] = $package['plan_submit_doc_date'];
                }
                if (!empty($package['actual_submit_doc_date'])) {
                    $actualDocDates[] = $package['actual_submit_doc_date'];
                }
                if (!empty($statusSummary['astri_latest_submitted_date'])) {
                    $astriSubmittedDates[] = $statusSummary['astri_latest_submitted_date'];
                }
                if (!empty($statusSummary['astri_latest_approved_date'])) {
                    $astriApprovedDates[] = $statusSummary['astri_latest_approved_date'];
                }
            }

            $row['plan_submit_doc_date'] = !empty($planDocDates) ? max($planDocDates) : ($row['atp_date'] ? $this->addBusinessDays($row['atp_date'], 7) : null);
            $row['actual_submit_doc_date'] = !empty($actualDocDates) ? max($actualDocDates) : null;
            $row['aging_doc_days'] = $this->calculateAgingDays($row['plan_submit_doc_date'], $row['actual_submit_doc_date']);
            $row['submit_astri_date'] = ($totalRequired > 0 && $totalAstriSubmitted >= $totalRequired && !empty($astriSubmittedDates)) ? max($astriSubmittedDates) : null;
            $row['approved_astri_date'] = ($totalRequired > 0 && $totalAstriApproved >= $totalRequired && !empty($astriApprovedDates)) ? max($astriApprovedDates) : null;

            foreach (['cw_atp' => 'CW ATP', 'full_opm' => 'FULL OPM', 'rfs' => 'RFS'] as $prefix => $sowType) {
                $row['doc_' . $prefix . '_uploaded'] = $docSummary[$sowType]['uploaded'];
                $row['doc_' . $prefix . '_required'] = $docSummary[$sowType]['required'];
                $row['doc_' . $prefix . '_approved'] = $docSummary[$sowType]['approved'];
                $row['doc_' . $prefix . '_on_review'] = $docSummary[$sowType]['on_review'];
                $row['doc_' . $prefix . '_rejected'] = $docSummary[$sowType]['rejected'];
                $row['doc_' . $prefix . '_ny'] = $docSummary[$sowType]['ny'];
                $row['astri_doc_' . $prefix . '_submitted'] = $docSummary[$sowType]['astri_submitted'];
                $row['astri_doc_' . $prefix . '_approved'] = $docSummary[$sowType]['astri_approved'];
                $row['astri_doc_' . $prefix . '_on_review'] = $docSummary[$sowType]['astri_on_review'];
                $row['astri_doc_' . $prefix . '_rejected'] = $docSummary[$sowType]['astri_rejected'];
                $row['astri_doc_' . $prefix . '_ny'] = $docSummary[$sowType]['astri_ny'];
            }

            $result[] = $row;
        }

        return $result;
    }

    private function getMainfeederDocumentGroups()
    {
        return $this->db
            ->select('g.id_doc_group_mainfeeder, g.sow_type, g.group_label, g.sort_no, COUNT(i.id_doc_item_mainfeeder) AS required_docs', false)
            ->from('md_rfs_myrep_mainfeeder_doc_group g')
            ->join('md_rfs_myrep_mainfeeder_doc_item i', 'i.id_doc_group_mainfeeder = g.id_doc_group_mainfeeder AND i.is_active = 1 AND i.is_required = 1', 'left')
            ->where('g.is_active', 1)
            ->group_by(['g.id_doc_group_mainfeeder', 'g.sow_type', 'g.group_label', 'g.sort_no'])
            ->order_by('g.sort_no', 'ASC')
            ->get()
            ->result_array();
    }

    private function getMainfeederDocumentItems()
    {
        $rows = $this->db->select('id_doc_item_mainfeeder, id_doc_group_mainfeeder, doc_name, sort_no')
            ->from('md_rfs_myrep_mainfeeder_doc_item')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->order_by('sort_no', 'ASC')
            ->get()
            ->result_array();
        $items = [];
        foreach ($rows as $row) {
            $items[(int) $row['id_doc_group_mainfeeder']][] = $row;
        }
        return $items;
    }

    private function getMainfeederPackagesByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $rows = $this->db->select('p.*, g.sow_type, g.group_label')
            ->from('tb_rfs_myrep_mainfeeder_doc_package p')
            ->join('md_rfs_myrep_mainfeeder_doc_group g', 'g.id_doc_group_mainfeeder = p.id_doc_group_mainfeeder', 'inner')
            ->where_in('p.id_mainfeeder', $ids)
            ->get()
            ->result_array();
        $packages = [];
        foreach ($rows as $row) {
            $packages[(int) $row['id_mainfeeder']][(int) $row['id_doc_group_mainfeeder']] = $row;
        }
        return $packages;
    }

    private function getMainfeederFilesByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }
        return $this->db->select('*')
            ->from('tb_rfs_myrep_mainfeeder_doc_file')
            ->where_in('id_doc_package_mainfeeder', $packageIds)
            ->get()
            ->result_array();
    }

    private function getMainfeederFileSummaryByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }
        $rows = $this->db->select("
                id_doc_package_mainfeeder,
                SUM(CASE WHEN ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1) AND status_file IN ('UPLOADED','APPROVED') THEN 1 ELSE 0 END) AS uploaded_docs
            ", false)
            ->from('tb_rfs_myrep_mainfeeder_doc_file')
            ->where_in('id_doc_package_mainfeeder', $packageIds)
            ->group_by('id_doc_package_mainfeeder')
            ->get()
            ->result_array();
        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package_mainfeeder']] = ['uploaded_docs' => (int) $row['uploaded_docs']];
        }
        return $summary;
    }

    private function getMainfeederFileStatusSummaryByPackageIds($packageIds)
    {
        if (empty($packageIds)) {
            return [];
        }
        $rows = $this->db->select("
                id_doc_package_mainfeeder,
                SUM(CASE WHEN status_file = 'APPROVED' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status_file = 'UPLOADED' THEN 1 ELSE 0 END) AS on_review,
                SUM(CASE WHEN status_file = 'REJECTED' THEN 1 ELSE 0 END) AS rejected,
                COUNT(*) AS existing,
                SUM(CASE WHEN astri_status = 'APPROVED' THEN 1 ELSE 0 END) AS astri_approved,
                SUM(CASE WHEN astri_status = 'ON REVIEW' THEN 1 ELSE 0 END) AS astri_on_review,
                SUM(CASE WHEN astri_status = 'REJECTED' THEN 1 ELSE 0 END) AS astri_rejected,
                SUM(CASE WHEN astri_status IN ('ON REVIEW', 'REJECTED', 'APPROVED') THEN 1 ELSE 0 END) AS astri_submitted,
                MAX(CASE WHEN astri_status IN ('ON REVIEW', 'REJECTED', 'APPROVED') AND astri_submitted_date IS NOT NULL THEN astri_submitted_date ELSE NULL END) AS astri_latest_submitted_date,
                MAX(CASE WHEN astri_status = 'APPROVED' AND astri_status_updated_at IS NOT NULL THEN DATE(astri_status_updated_at) ELSE NULL END) AS astri_latest_approved_date
            ", false)
            ->from('tb_rfs_myrep_mainfeeder_doc_file')
            ->where_in('id_doc_package_mainfeeder', $packageIds)
            ->group_by('id_doc_package_mainfeeder')
            ->get()
            ->result_array();
        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package_mainfeeder']] = [
                'approved' => (int) $row['approved'],
                'on_review' => (int) $row['on_review'],
                'rejected' => (int) $row['rejected'],
                'existing' => (int) $row['existing'],
                'astri_approved' => (int) $row['astri_approved'],
                'astri_on_review' => (int) $row['astri_on_review'],
                'astri_rejected' => (int) $row['astri_rejected'],
                'astri_submitted' => (int) $row['astri_submitted'],
                'astri_latest_submitted_date' => $this->normalizeDate($row['astri_latest_submitted_date'] ?? null),
                'astri_latest_approved_date' => $this->normalizeDate($row['astri_latest_approved_date'] ?? null),
            ];
        }
        return $summary;
    }

    private function refreshMainfeederPackageStatus($packageId)
    {
        $package = $this->db->get_where('tb_rfs_myrep_mainfeeder_doc_package', [
            'id_doc_package_mainfeeder' => (int) $packageId,
        ])->row_array();
        if (!$package) {
            return;
        }
        $required = (int) $this->db->from('md_rfs_myrep_mainfeeder_doc_item')
            ->where('id_doc_group_mainfeeder', (int) $package['id_doc_group_mainfeeder'])
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->count_all_results();
        $uploaded = (int) $this->db->query(
            "SELECT COUNT(*) AS total
             FROM tb_rfs_myrep_mainfeeder_doc_file
             WHERE id_doc_package_mainfeeder = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row()->total;
        $latestUploaded = $this->db->query(
            "SELECT MAX(uploaded_at) AS latest_uploaded
             FROM tb_rfs_myrep_mainfeeder_doc_file
             WHERE id_doc_package_mainfeeder = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row_array();

        $actualSubmit = null;
        if ($required > 0 && $uploaded >= $required && !empty($latestUploaded['latest_uploaded'])) {
            $actualSubmit = substr((string) $latestUploaded['latest_uploaded'], 0, 10);
        }

        $this->db->where('id_doc_package_mainfeeder', (int) $packageId)
            ->update('tb_rfs_myrep_mainfeeder_doc_package', [
                'status_package' => $this->derivePackageStatus($uploaded, $required),
                'actual_submit_doc_date' => $actualSubmit,
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
    }

    private function enrichClusterRows($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $clusterIds = array_map(function ($row) {
            return (int) $row['id_cluster'];
        }, $rows);

        $groups = $this->getDocumentGroups();
        $packagesByCluster = $this->getPackagesByClusterIds($clusterIds);
        $packageIds = [];

        foreach ($packagesByCluster as $clusterPackages) {
            foreach ($clusterPackages as $package) {
                if (!empty($package['id_doc_package'])) {
                    $packageIds[] = (int) $package['id_doc_package'];
                }
            }
        }

        $fileSummary = $this->getFileSummaryByPackageIds($packageIds);
        $fileStatusSummary = $this->getFileStatusSummaryByPackageIds($packageIds);
        $result = [];

        foreach ($rows as $row) {
            $clusterId = (int) $row['id_cluster'];
            $clusterPackages = isset($packagesByCluster[$clusterId]) ? $packagesByCluster[$clusterId] : [];
            $docSummary = [
                'CW ATP' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
                'FULL OPM' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
                'RFS' => ['uploaded' => 0, 'required' => 0, 'approved' => 0, 'on_review' => 0, 'rejected' => 0, 'ny' => 0, 'astri_submitted' => 0, 'astri_approved' => 0, 'astri_on_review' => 0, 'astri_rejected' => 0, 'astri_ny' => 0],
            ];
            $planAtpDates = [];
            $actualAtpDates = [];
            $planDocDates = [];
            $actualDocDates = [];
            $astriSubmittedDates = [];
            $astriApprovedDates = [];
            $totalRequiredDocs = 0;
            $totalAstriSubmittedDocs = 0;
            $totalAstriApprovedDocs = 0;

            foreach ($groups as $group) {
                $groupId = (int) $group['id_doc_group'];
                $package = isset($clusterPackages[$groupId]) ? $clusterPackages[$groupId] : [];
                $packageId = isset($package['id_doc_package']) ? (int) $package['id_doc_package'] : 0;
                $required = (int) $group['required_docs'];
                $uploaded = ($packageId > 0 && isset($fileSummary[$packageId])) ? (int) $fileSummary[$packageId]['uploaded_docs'] : 0;
                $sowType = (string) $group['sow_type'];
                $statusSummary = ($packageId > 0 && isset($fileStatusSummary[$packageId])) ? $fileStatusSummary[$packageId] : [
                    'approved' => 0,
                    'on_review' => 0,
                    'rejected' => 0,
                    'existing' => 0,
                    'astri_approved' => 0,
                    'astri_on_review' => 0,
                    'astri_rejected' => 0,
                    'astri_submitted' => 0,
                    'astri_latest_submitted_date' => null,
                    'astri_latest_approved_date' => null,
                ];

                $docSummary[$sowType]['required'] += $required;
                $docSummary[$sowType]['uploaded'] += min($uploaded, $required);
                $docSummary[$sowType]['approved'] += (int) $statusSummary['approved'];
                $docSummary[$sowType]['on_review'] += (int) $statusSummary['on_review'];
                $docSummary[$sowType]['rejected'] += (int) $statusSummary['rejected'];
                $docSummary[$sowType]['ny'] += max(0, $required - (int) $statusSummary['existing']);
                $docSummary[$sowType]['astri_submitted'] += (int) $statusSummary['astri_submitted'];
                $docSummary[$sowType]['astri_approved'] += (int) $statusSummary['astri_approved'];
                $docSummary[$sowType]['astri_on_review'] += (int) $statusSummary['astri_on_review'];
                $docSummary[$sowType]['astri_rejected'] += (int) $statusSummary['astri_rejected'];
                $docSummary[$sowType]['astri_ny'] += max(0, $required - (int) $statusSummary['astri_submitted']);
                $totalRequiredDocs += $required;
                $totalAstriSubmittedDocs += (int) $statusSummary['astri_submitted'];
                $totalAstriApprovedDocs += (int) $statusSummary['astri_approved'];

                if (!empty($statusSummary['astri_latest_submitted_date'])) {
                    $astriSubmittedDates[] = $statusSummary['astri_latest_submitted_date'];
                }

                if (!empty($statusSummary['astri_latest_approved_date'])) {
                    $astriApprovedDates[] = $statusSummary['astri_latest_approved_date'];
                }

                $planAtp = $this->normalizeDate($package['plan_atp_date'] ?? null);
                $actualAtp = $this->normalizeDate($package['actual_atp_date'] ?? null);
                $planDoc = $this->normalizeDate($package['plan_submit_doc_date'] ?? null);
                $actualDoc = $this->normalizeDate($package['actual_submit_doc_date'] ?? null);

                if ($planAtp) {
                    $planAtpDates[] = $planAtp;
                }
                if ($actualAtp) {
                    $actualAtpDates[] = $actualAtp;
                }
                if ($planDoc) {
                    $planDocDates[] = $planDoc;
                }
                if ($actualDoc) {
                    $actualDocDates[] = $actualDoc;
                }
            }

            $tanggalRfs = $this->normalizeDate($row['rfs_date'] ?? null);
            $summaryPlanAtp = !empty($planAtpDates) ? max($planAtpDates) : ($tanggalRfs ? $this->addBusinessDays($tanggalRfs, 7) : null);
            $summaryActualAtp = !empty($actualAtpDates) ? max($actualAtpDates) : null;
            if (!empty($planDocDates)) {
                $summaryPlanDoc = max($planDocDates);
            } elseif ($summaryActualAtp) {
                $summaryPlanDoc = $this->addBusinessDays($summaryActualAtp, 7);
            } elseif ($summaryPlanAtp) {
                $summaryPlanDoc = $this->addBusinessDays($summaryPlanAtp, 7);
            } else {
                $summaryPlanDoc = null;
            }
            $summaryActualDoc = !empty($actualDocDates) ? max($actualDocDates) : null;
            $summarySubmitAstri = ($totalRequiredDocs > 0 && $totalAstriSubmittedDocs >= $totalRequiredDocs && !empty($astriSubmittedDates))
                ? max($astriSubmittedDates)
                : null;
            $summaryApprovedAstri = ($totalRequiredDocs > 0 && $totalAstriApprovedDocs >= $totalRequiredDocs && !empty($astriApprovedDates))
                ? max($astriApprovedDates)
                : null;

            $row['tanggal_rfs'] = $tanggalRfs;
            $row['plan_atp_date'] = $summaryPlanAtp;
            $row['actual_atp_date'] = $summaryActualAtp;
            $row['plan_submit_doc_date'] = $summaryPlanDoc;
            $row['actual_submit_doc_date'] = $summaryActualDoc;
            $row['submit_astri_date'] = $summarySubmitAstri;
            $row['approved_astri_date'] = $summaryApprovedAstri;
            $row['aging_atp_days'] = $this->calculateAgingDays($summaryPlanAtp, $summaryActualAtp);
            $row['aging_doc_days'] = $this->calculateAgingDays($summaryPlanDoc, $summaryActualDoc);
            $row['doc_cw_atp_uploaded'] = $docSummary['CW ATP']['uploaded'];
            $row['doc_cw_atp_required'] = $docSummary['CW ATP']['required'];
            $row['doc_cw_atp_approved'] = $docSummary['CW ATP']['approved'];
            $row['doc_cw_atp_on_review'] = $docSummary['CW ATP']['on_review'];
            $row['doc_cw_atp_rejected'] = $docSummary['CW ATP']['rejected'];
            $row['doc_cw_atp_ny'] = $docSummary['CW ATP']['ny'];
            $row['astri_doc_cw_atp_submitted'] = $docSummary['CW ATP']['astri_submitted'];
            $row['astri_doc_cw_atp_approved'] = $docSummary['CW ATP']['astri_approved'];
            $row['astri_doc_cw_atp_on_review'] = $docSummary['CW ATP']['astri_on_review'];
            $row['astri_doc_cw_atp_rejected'] = $docSummary['CW ATP']['astri_rejected'];
            $row['astri_doc_cw_atp_ny'] = $docSummary['CW ATP']['astri_ny'];
            $row['doc_full_opm_uploaded'] = $docSummary['FULL OPM']['uploaded'];
            $row['doc_full_opm_required'] = $docSummary['FULL OPM']['required'];
            $row['doc_full_opm_approved'] = $docSummary['FULL OPM']['approved'];
            $row['doc_full_opm_on_review'] = $docSummary['FULL OPM']['on_review'];
            $row['doc_full_opm_rejected'] = $docSummary['FULL OPM']['rejected'];
            $row['doc_full_opm_ny'] = $docSummary['FULL OPM']['ny'];
            $row['astri_doc_full_opm_submitted'] = $docSummary['FULL OPM']['astri_submitted'];
            $row['astri_doc_full_opm_approved'] = $docSummary['FULL OPM']['astri_approved'];
            $row['astri_doc_full_opm_on_review'] = $docSummary['FULL OPM']['astri_on_review'];
            $row['astri_doc_full_opm_rejected'] = $docSummary['FULL OPM']['astri_rejected'];
            $row['astri_doc_full_opm_ny'] = $docSummary['FULL OPM']['astri_ny'];
            $row['doc_rfs_uploaded'] = $docSummary['RFS']['uploaded'];
            $row['doc_rfs_required'] = $docSummary['RFS']['required'];
            $row['doc_rfs_approved'] = $docSummary['RFS']['approved'];
            $row['doc_rfs_on_review'] = $docSummary['RFS']['on_review'];
            $row['doc_rfs_rejected'] = $docSummary['RFS']['rejected'];
            $row['doc_rfs_ny'] = $docSummary['RFS']['ny'];
            $row['astri_doc_rfs_submitted'] = $docSummary['RFS']['astri_submitted'];
            $row['astri_doc_rfs_approved'] = $docSummary['RFS']['astri_approved'];
            $row['astri_doc_rfs_on_review'] = $docSummary['RFS']['astri_on_review'];
            $row['astri_doc_rfs_rejected'] = $docSummary['RFS']['astri_rejected'];
            $row['astri_doc_rfs_ny'] = $docSummary['RFS']['astri_ny'];

            $result[] = $row;
        }

        return $result;
    }

    private function getDocumentGroups()
    {
        return $this->db
            ->select('g.id_doc_group, g.scope_type, g.sow_type, g.group_label, g.sort_no, COUNT(i.id_doc_item) AS required_docs', false)
            ->from('md_rfs_myrep_doc_group g')
            ->join('md_rfs_myrep_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1 AND i.is_required = 1', 'left')
            ->where('g.is_active', 1)
            ->group_by(['g.id_doc_group', 'g.scope_type', 'g.sow_type', 'g.group_label', 'g.sort_no'])
            ->order_by('g.sort_no', 'ASC')
            ->order_by('g.id_doc_group', 'ASC')
            ->get()
            ->result_array();
    }

    private function getDocumentItems()
    {
        $rows = $this->db
            ->select('id_doc_item, id_doc_group, doc_name, doc_requirement_note, format_file_name, format_file_path, verification_team, sort_no')
            ->from('md_rfs_myrep_doc_item')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->order_by('sort_no', 'ASC')
            ->order_by('id_doc_item', 'ASC')
            ->get()
            ->result_array();

        $items = [];
        foreach ($rows as $row) {
            $items[(int) $row['id_doc_group']][] = $row;
        }

        return $items;
    }

    private function getSitacApproverId()
    {
        return 22;
    }

    private function getSitacApproverUser()
    {
        static $user = null;
        if ($user !== null) {
            return $user;
        }

        $user = $this->db
            ->select('id AS id_user, nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('id', $this->getSitacApproverId())
            ->get()
            ->row_array();

        return !empty($user) ? $user : [];
    }

    private function resolveVerificationDisplayName($verificationTeam, $hoPicName, array $locationContext = [])
    {
        $verificationTeam = strtoupper(trim((string) $verificationTeam));
        $cityPicMapping = $this->getCityPicMapping($locationContext);

        // Role mapping berbasis master kota.
        $roleColumnMap = [
            'RPM' => 'rpm_area',
            'SM' => 'sm_area',
            'SPV' => 'spv_area',
            'SND' => 'snd_area',
            'ADMIN' => 'admin_area',
            'SND HO' => 'snd_ho',
            'RFS HO' => 'rfs_ho',
            'SITAC HO' => 'sitac_ho',
            'DC HO' => 'dc_ho',
        ];

        if (isset($roleColumnMap[$verificationTeam])) {
            $mappedNames = [];
            foreach (myrep_pic_nik_list($cityPicMapping[$roleColumnMap[$verificationTeam]] ?? '') as $mappedNik) {
                $mappedUser = $this->getUserByNik($mappedNik);
                if (!empty($mappedUser['nama_user'])) {
                    $mappedNames[] = (string) $mappedUser['nama_user'];
                    continue;
                }

                $mappedNames[] = $mappedNik;
            }
            if (!empty($mappedNames)) {
                return implode(', ', $mappedNames);
            }
        }

        if ($verificationTeam === 'SITAC') {
            $sitacUser = $this->getSitacApproverUser();
            if (!empty($sitacUser['nama_user'])) {
                return (string) $sitacUser['nama_user'];
            }

            return 'TIM SITAC';
        }

        return $hoPicName !== '' ? (string) $hoPicName : 'PIC HO BELUM DISET';
    }

    private function applyAllowedCityRestriction($columnName = 'mt.city_name')
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

    private function isRfsPackageAllowed($packageId)
    {
        $packageId = (int) $packageId;
        if ($packageId <= 0) {
            return false;
        }

        if (!$this->shouldRestrictCityByUser()) {
            return true;
        }

        $row = $this->db
            ->select('mt.city_name')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('tb_rfs_myrep_cluster c', 'c.id_cluster = p.cluster_id', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('p.id_doc_package', $packageId)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) && $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''));
    }

    private function isMainfeederPackageAllowed($packageId)
    {
        $packageId = (int) $packageId;
        if ($packageId <= 0) {
            return false;
        }

        if (!$this->shouldRestrictCityByUser()) {
            return true;
        }

        $row = $this->db
            ->select('mt.city_name')
            ->from('tb_rfs_myrep_mainfeeder_doc_package p')
            ->join('tb_rfs_myrep_mainfeeder mf', 'mf.id_mainfeeder = p.id_mainfeeder', 'left')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = mf.id_target', 'left')
            ->where('p.id_doc_package_mainfeeder', $packageId)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) && $this->isCityAllowedForCurrentUser((string) ($row['city_name'] ?? ''));
    }

    private function getCityPicMapping(array $locationContext = [])
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $city = strtoupper(trim((string) ($locationContext['city_name'] ?? '')));
        $province = strtoupper(trim((string) ($locationContext['province_name'] ?? '')));
        $regional = strtoupper(trim((string) ($locationContext['regional_name'] ?? '')));

        if ($city === '') {
            return [];
        }

        $cacheKey = $regional . '|' . $province . '|' . $city;
        if (isset($this->cityPicMappingCache[$cacheKey])) {
            return $this->cityPicMappingCache[$cacheKey];
        }

        $this->db->from('tb_myrep_pic_mapping_city');
        $this->db->where('UPPER(city_name)', $city);
        if ($province !== '') {
            $this->db->where('UPPER(province_name)', $province);
        }
        if ($regional !== '') {
            $this->db->where('UPPER(regional_name)', $regional);
        }
        $row = $this->db->limit(1)->get()->row_array();
        if (empty($row)) {
            $row = $this->db
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $city)
                ->limit(1)
                ->get()
                ->row_array();
        }

        $this->cityPicMappingCache[$cacheKey] = !empty($row) ? $row : [];
        return $this->cityPicMappingCache[$cacheKey];
    }

    private function getUserByNik($nik)
    {
        $nik = trim((string) $nik);
        if ($nik === '') {
            return [];
        }

        return (array) $this->db
            ->select('id AS id_user, nik, nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();
    }

    public function getDocumentItemFormatById($itemId)
    {
        return $this->db
            ->select('id_doc_item, doc_name, format_file_name, format_file_path')
            ->from('md_rfs_myrep_doc_item')
            ->where('id_doc_item', (int) $itemId)
            ->get()
            ->row_array();
    }

    private function getPackagesByClusterIds($clusterIds)
    {
        $clusterIds = $this->sanitizeIdList((array) $clusterIds);
        if (empty($clusterIds)) {
            return [];
        }

        $rows = [];
        foreach (array_chunk($clusterIds, $this->maxWhereInChunk) as $chunkIds) {
            $chunkRows = $this->db
                ->select('p.*, g.scope_type, g.sow_type, g.group_label')
                ->from('tb_rfs_myrep_doc_package p')
                ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'inner')
                ->where_in('p.cluster_id', $chunkIds)
                ->get()
                ->result_array();
            if (!empty($chunkRows)) {
                $rows = array_merge($rows, $chunkRows);
            }
        }

        $packages = [];
        foreach ($rows as $row) {
            $packages[(int) $row['cluster_id']][(int) $row['id_doc_group']] = $row;
        }

        return $packages;
    }

    private function getFileSummaryByPackageIds($packageIds)
    {
        $packageIds = $this->sanitizeIdList((array) $packageIds);
        if (empty($packageIds)) {
            return [];
        }

        $rows = [];
        foreach (array_chunk($packageIds, $this->maxWhereInChunk) as $chunkIds) {
            $chunkRows = $this->db
                ->select("
                    id_doc_package,
                    SUM(CASE WHEN ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1) AND status_file IN ('UPLOADED','APPROVED') THEN 1 ELSE 0 END) AS uploaded_docs,
                    SUM(CASE WHEN status_file = 'APPROVED' THEN 1 ELSE 0 END) AS approved_docs
                ", false)
                ->from('tb_rfs_myrep_doc_file')
                ->where_in('id_doc_package', $chunkIds)
                ->group_by('id_doc_package')
                ->get()
                ->result_array();
            if (!empty($chunkRows)) {
                $rows = array_merge($rows, $chunkRows);
            }
        }

        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package']] = [
                'uploaded_docs' => (int) $row['uploaded_docs'],
                'approved_docs' => (int) $row['approved_docs'],
            ];
        }

        return $summary;
    }

    private function getFileStatusSummaryByPackageIds($packageIds)
    {
        $packageIds = $this->sanitizeIdList((array) $packageIds);
        if (empty($packageIds)) {
            return [];
        }

        $rows = [];
        foreach (array_chunk($packageIds, $this->maxWhereInChunk) as $chunkIds) {
            $chunkRows = $this->db
                ->select("
                    f.id_doc_package,
                    SUM(CASE WHEN f.status_file = 'APPROVED' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN f.status_file = 'UPLOADED' THEN 1 ELSE 0 END) AS on_review,
                    SUM(CASE WHEN f.status_file = 'REJECTED' THEN 1 ELSE 0 END) AS rejected,
                    COUNT(*) AS existing,
                    SUM(CASE WHEN f.astri_status = 'APPROVED' THEN 1 ELSE 0 END) AS astri_approved,
                    SUM(CASE WHEN f.astri_status = 'REJECTED' THEN 1 ELSE 0 END) AS astri_rejected,
                    SUM(CASE WHEN f.astri_status = 'ON REVIEW' THEN 1 ELSE 0 END) AS astri_on_review,
                    SUM(CASE
                        WHEN f.astri_status IN ('ON REVIEW', 'REJECTED', 'APPROVED', 'WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK')
                            THEN 1
                        WHEN f.astri_status = 'NY'
                            AND p.actual_atp_date IS NOT NULL
                            AND g.scope_type = 'CLUSTER'
                            AND g.sow_type = 'RFS'
                            AND UPPER(i.doc_name) = 'PROJECT OPNAME'
                            THEN 1
                        ELSE 0
                    END) AS astri_submitted,
                    MAX(CASE
                        WHEN (
                            f.astri_status IN ('ON REVIEW', 'REJECTED', 'APPROVED', 'WAITING WASPANG', 'WAITING PLANNING', 'WAITING TL', 'WAITING LOGISTIK')
                            OR (
                                f.astri_status = 'NY'
                                AND p.actual_atp_date IS NOT NULL
                                AND g.scope_type = 'CLUSTER'
                                AND g.sow_type = 'RFS'
                                AND UPPER(i.doc_name) = 'PROJECT OPNAME'
                            )
                        ) AND f.astri_submitted_date IS NOT NULL THEN f.astri_submitted_date ELSE NULL END) AS astri_latest_submitted_date,
                    MAX(CASE WHEN f.astri_status = 'APPROVED' AND f.astri_status_updated_at IS NOT NULL THEN DATE(f.astri_status_updated_at) ELSE NULL END) AS astri_latest_approved_date
                ", false)
                ->from('tb_rfs_myrep_doc_file f')
                ->join('tb_rfs_myrep_doc_package p', 'p.id_doc_package = f.id_doc_package', 'left')
                ->join('md_rfs_myrep_doc_item i', 'i.id_doc_item = f.id_doc_item', 'left')
                ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = i.id_doc_group', 'left')
                ->where_in('f.id_doc_package', $chunkIds)
                ->group_by('f.id_doc_package')
                ->get()
                ->result_array();
            if (!empty($chunkRows)) {
                $rows = array_merge($rows, $chunkRows);
            }
        }

        $summary = [];
        foreach ($rows as $row) {
            $summary[(int) $row['id_doc_package']] = [
                'approved' => (int) $row['approved'],
                'on_review' => (int) $row['on_review'],
                'rejected' => (int) $row['rejected'],
                'existing' => (int) $row['existing'],
                'astri_approved' => (int) $row['astri_approved'],
                'astri_on_review' => (int) $row['astri_on_review'],
                'astri_rejected' => (int) $row['astri_rejected'],
                'astri_submitted' => (int) $row['astri_submitted'],
                'astri_latest_submitted_date' => $this->normalizeDate($row['astri_latest_submitted_date'] ?? null),
                'astri_latest_approved_date' => $this->normalizeDate($row['astri_latest_approved_date'] ?? null),
            ];
        }

        return $summary;
    }

    private function getFilesByPackageIds($packageIds)
    {
        $packageIds = $this->sanitizeIdList((array) $packageIds);
        if (empty($packageIds)) {
            return [];
        }

        $rows = [];
        foreach (array_chunk($packageIds, $this->maxWhereInChunk) as $chunkIds) {
            $chunkRows = $this->db
                ->select('id_doc_file, id_doc_package, id_doc_item, file_name, file_path, is_document_not_required, status_file, remark, uploaded_at, reviewed_at, approved_at, astri_submitted_date, astri_status, astri_status_updated_at, astri_remark')
                ->from('tb_rfs_myrep_doc_file')
                ->where_in('id_doc_package', $chunkIds)
                ->get()
                ->result_array();
            if (!empty($chunkRows)) {
                $rows = array_merge($rows, $chunkRows);
            }
        }

        return $rows;
    }

    private function createFileLog($data)
    {
        $this->db->insert('tb_rfs_myrep_doc_file_log', [
            'id_doc_file' => (int) $data['id_doc_file'],
            'id_doc_package' => (int) $data['id_doc_package'],
            'id_doc_item' => (int) $data['id_doc_item'],
            'action_type' => $data['action_type'],
            'status_after' => $data['status_after'],
            'file_name' => $data['file_name'],
            'remark' => $data['remark'],
            'action_by' => (int) $data['action_by'],
            'action_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function deletePhysicalFile($relativePath)
    {
        if (empty($relativePath)) {
            return;
        }

        $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function refreshPackageStatus($packageId)
    {
        $package = $this->db->get_where('tb_rfs_myrep_doc_package', [
            'id_doc_package' => (int) $packageId,
        ])->row_array();

        if (!$package) {
            return;
        }

        $required = (int) $this->db
            ->from('md_rfs_myrep_doc_item')
            ->where('id_doc_group', (int) $package['id_doc_group'])
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->count_all_results();

        $uploaded = (int) $this->db->query(
            "SELECT COUNT(*) AS total
             FROM tb_rfs_myrep_doc_file
             WHERE id_doc_package = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row()->total;

        $latestUploaded = $this->db->query(
            "SELECT MAX(uploaded_at) AS latest_uploaded
             FROM tb_rfs_myrep_doc_file
             WHERE id_doc_package = ?
             AND ((file_path IS NOT NULL AND file_path <> '') OR is_document_not_required = 1)
             AND status_file IN ('UPLOADED','APPROVED')",
            [(int) $packageId]
        )->row_array();

        $statusPackage = $this->derivePackageStatus($uploaded, $required);
        $actualSubmit = null;
        if ($required > 0 && $uploaded >= $required && !empty($latestUploaded['latest_uploaded'])) {
            $actualSubmit = substr((string) $latestUploaded['latest_uploaded'], 0, 10);
        }

        $this->db
            ->where('id_doc_package', (int) $packageId)
            ->update('tb_rfs_myrep_doc_package', [
                'status_package' => $statusPackage,
                'actual_submit_doc_date' => $actualSubmit,
                'updated_by' => (int) $this->session->userdata('id_user'),
            ]);
    }

    private function normalizeDate($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return null;
        }

        return $date;
    }

    private function normalizeDateTime($dateTime)
    {
        if (empty($dateTime) || $dateTime === '0000-00-00 00:00:00') {
            return null;
        }

        return $dateTime;
    }

    private function addBusinessDays($date, $days)
    {
        if (!$date) {
            return null;
        }

        $dateTime = new DateTime($date);
        $dateTime->modify('+' . (int) $days . ' day');
        return $dateTime->format('Y-m-d');
    }

    private function calculateAgingDays($planDate, $actualDate = null)
    {
        if (!$planDate) {
            return null;
        }

        $endDate = $actualDate ?: date('Y-m-d');
        $start = new DateTime($planDate);
        $end = new DateTime($endDate);
        $invert = $start > $end ? -1 : 1;
        $diff = $start->diff($end);

        return $diff->days * $invert;
    }

    private function derivePackageStatus($uploadedDocs, $requiredDocs)
    {
        if ($requiredDocs <= 0 || $uploadedDocs <= 0) {
            return 'NOT STARTED';
        }

        if ($uploadedDocs >= $requiredDocs) {
            return 'DONE';
        }

        return 'ON PROGRESS';
    }

    private function extractLatestUploadedDate($items)
    {
        $dates = [];
        foreach ($items as $item) {
            if (!empty($item['uploaded_at'])) {
                $dates[] = substr((string) $item['uploaded_at'], 0, 10);
            }
        }

        return empty($dates) ? null : max($dates);
    }

    private function isUploadedRow($row)
    {
        return !empty($row)
            && (
                !empty($row['file_path'])
                || !empty($row['is_document_not_required'])
            )
            && in_array((string) ($row['status_file'] ?? ''), ['UPLOADED', 'APPROVED'], true);
    }
}




