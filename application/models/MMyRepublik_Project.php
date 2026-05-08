<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMyRepublik_Project extends CI_Model
{
    private $statusOrder = [
        'DRAFT',
        'BA OPEN',
        'BAK',
        'VALSAL',
        'WAITING HO',
        'WAITING MYREP',
        'WAITING FINANCE',
        'RELEASED',
        'DONE BATCH APPROVAL',
        'DRM',
        'RFS',
        'ATP',
        'DONE',
        'REJECTED',
        'HOLD',
    ];

    public function tablesReady()
    {
        return $this->hasMyrepTables() || $this->hasLegacyRfsTables();
    }

    public function getCityOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $cities = [];

        if ($this->hasMyrepTables()) {
            $rows = $this->db
                ->distinct()
                ->select('city_name')
                ->from('tb_myrep_cluster')
                ->where('city_name IS NOT NULL', null, false)
                ->where("TRIM(city_name) !=", '')
                ->order_by('city_name', 'ASC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityName !== '') {
                    $cities[$cityName] = $cityName;
                }
            }
        }

        if ($this->hasLegacyRfsTables()) {
            $rows = $this->db
                ->distinct()
                ->select('city_name')
                ->from('tb_rfs_myrep_monthly_target')
                ->where('city_name IS NOT NULL', null, false)
                ->where("TRIM(city_name) !=", '')
                ->order_by('city_name', 'ASC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
                if ($cityName !== '') {
                    $cities[$cityName] = $cityName;
                }
            }
        }

        ksort($cities);
        return array_values($cities);
    }

    public function getClusterRows($selectedCity = '', $selectedStatus = '', $metricMode = 'HP')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->getNewFlowRows($selectedCity, $selectedStatus);

        $poMap = $this->getPoMap(array_column($rows, 'id_myrep_cluster'));
        foreach ($rows as &$row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $row['po_total_value'] = (float) ($poMap[$clusterId]['po_total_value'] ?? 0);
            $row['po_count'] = (int) ($poMap[$clusterId]['po_count'] ?? 0);
            $row['metric_value'] = $this->resolveMetricValue($row, $metricMode);
        }
        unset($row);

        $legacyRows = $this->getLegacyRfsRows($selectedCity, $selectedStatus);
        $mappedRfsIds = [];
        $existingKeys = [];
        foreach ($rows as $row) {
            $mappedRfsId = (int) ($row['rfs_cluster_id'] ?? 0);
            if ($mappedRfsId > 0) {
                $mappedRfsIds[$mappedRfsId] = true;
            }

            $existingKeys[$this->buildClusterKey($row['city_name'] ?? '', $row['cluster_name'] ?? '')] = true;
        }

        foreach ($legacyRows as $legacyRow) {
            $legacyClusterId = (int) ($legacyRow['legacy_rfs_cluster_id'] ?? 0);
            $clusterKey = $this->buildClusterKey($legacyRow['city_name'] ?? '', $legacyRow['cluster_name'] ?? '');

            if (($legacyClusterId > 0 && isset($mappedRfsIds[$legacyClusterId])) || isset($existingKeys[$clusterKey])) {
                continue;
            }

            $legacyRow['metric_value'] = $this->resolveMetricValue($legacyRow, $metricMode);
            $rows[] = $legacyRow;
        }

        usort($rows, static function ($a, $b) {
            $dateA = strtotime((string) ($a['created_at'] ?? '')) ?: 0;
            $dateB = strtotime((string) ($b['created_at'] ?? '')) ?: 0;
            if ($dateA === $dateB) {
                return strcmp((string) ($a['cluster_name'] ?? ''), (string) ($b['cluster_name'] ?? ''));
            }

            return $dateB <=> $dateA;
        });

        return $rows;
    }

    public function getStatusCards($rows, $metricMode = 'HP')
    {
        $cards = [];
        foreach ($this->statusOrder as $status) {
            $cards[$status] = [
                'status' => $status,
                'cluster_count' => 0,
                'metric_total' => 0,
            ];
        }

        foreach ($rows as $row) {
            $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
            if (!isset($cards[$status])) {
                $cards[$status] = [
                    'status' => $status,
                    'cluster_count' => 0,
                    'metric_total' => 0,
                ];
            }

            $cards[$status]['cluster_count']++;
            $cards[$status]['metric_total'] += (float) ($row['metric_value'] ?? 0);
        }

        return array_values(array_filter($cards, static function ($card) {
            return $card['cluster_count'] > 0;
        }));
    }

    public function getOverview($rows)
    {
        $overview = [
            'total_cluster' => 0,
            'total_hp' => 0,
            'total_po' => 0,
            'total_released' => 0,
            'total_rfs' => 0,
            'total_atp' => 0,
        ];

        foreach ($rows as $row) {
            $overview['total_cluster']++;
            $overview['total_hp'] += (float) $this->resolveMetricValue($row, 'HP');
            $overview['total_po'] += (float) ($row['po_total_value'] ?? 0);

            $status = strtoupper(trim((string) ($row['status_current'] ?? '')));
            if ($status === 'RELEASED') {
                $overview['total_released']++;
            } elseif ($status === 'RFS') {
                $overview['total_rfs']++;
            } elseif ($status === 'ATP') {
                $overview['total_atp']++;
            }
        }

        return $overview;
    }

    public function getStatusOptions()
    {
        return $this->statusOrder;
    }

    public function getClusterDetail($clusterId)
    {
        if (!$this->hasMyrepTables()) {
            return [];
        }

        $row = $this->db
            ->select('
                c.*,
                t.year_num,
                t.month_num,
                t.target_myrep,
                t.realization_myrep,
                t.target_rkap,
                b.id_bak,
                b.ba_open_date,
                b.bak_date,
                b.homepass_bak,
                b.status_bak,
                b.remark_bak,
                v.id_valsal,
                v.valsal_date,
                v.homepass_valsal,
                v.status_valsal,
                v.remark_valsal,
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
                d.id_drm,
                d.drm_date,
                d.homepass_drm,
                d.nama_olt,
                d.status_drm,
                d.remark_drm
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target t', 't.id_target = c.id_target', 'left')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
        $poMeta = $this->getPoMap([$clusterId])[$clusterId] ?? ['po_count' => 0, 'po_total_value' => 0];
        $rfsMeta = $this->getLegacyBridgeSummaryMap([(int) ($row['rfs_cluster_id'] ?? 0)])[(int) ($row['rfs_cluster_id'] ?? 0)] ?? [];

        return array_merge($row, $poMeta, $rfsMeta);
    }

    public function getLegacyClusterDetail($rfsClusterId)
    {
        if (!$this->hasLegacyRfsTables()) {
            return [];
        }

        $row = $this->db
            ->select("
                NULL AS id_myrep_cluster,
                c.id_cluster AS legacy_rfs_cluster_id,
                c.id_cluster AS rfs_cluster_id,
                c.id_target,
                c.cluster_name,
                NULL AS cluster_code,
                COALESCE(mt.regional_name, '-') AS regional_name,
                COALESCE(mt.province_name, '-') AS province_name,
                COALESCE(mt.city_name, '-') AS city_name,
                COALESCE(mt.team_name, '-') AS team_name,
                COALESCE(mt.chief, '-') AS chief,
                COALESCE(mt.rpm, '-') AS rpm,
                COALESCE(mt.sm, '-') AS sm,
                COALESCE(mt.spv, '-') AS spv,
                c.homepass AS hp_plan,
                c.status_rfs,
                c.status_atp,
                c.created_at,
                mt.year_num,
                mt.month_num,
                mt.target_myrep,
                mt.realization_myrep,
                mt.target_rkap,
                (
                    SELECT MAX(dp.tanggal_rfs)
                    FROM tb_rfs_myrep_doc_package dp
                    WHERE dp.cluster_id = c.id_cluster
                ) AS tanggal_rfs,
                (
                    SELECT MAX(dp.plan_atp_date)
                    FROM tb_rfs_myrep_doc_package dp
                    WHERE dp.cluster_id = c.id_cluster
                ) AS plan_atp_date,
                (
                    SELECT MAX(dp.actual_atp_date)
                    FROM tb_rfs_myrep_doc_package dp
                    WHERE dp.cluster_id = c.id_cluster
                ) AS actual_atp_date
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left')
            ->where('c.id_cluster', (int) $rfsClusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        $bridgeMap = $this->getLegacyBridgeSummaryMap([(int) $rfsClusterId]);
        return array_merge($row, $bridgeMap[(int) $rfsClusterId] ?? []);
    }

    public function buildStageTimeline($cluster, $isLegacy = false)
    {
        $timeline = [];

        $timeline[] = [
            'stage' => 'Draft Cluster',
            'status' => (string) ($cluster['status_current'] ?? ($isLegacy ? 'DRM/RFS LEGACY' : 'DRAFT')),
            'date' => $cluster['created_at'] ?? null,
            'summary' => 'Cluster terbentuk dan masuk monitoring utama.',
        ];

        if (!$isLegacy) {
            $timeline[] = [
                'stage' => 'BAK',
                'status' => (string) ($cluster['status_bak'] ?? 'NOT STARTED'),
                'date' => $cluster['bak_date'] ?? $cluster['ba_open_date'] ?? null,
                'summary' => 'BA Open, Form Survey, dan Surat Ijin diproses.',
            ];

            $timeline[] = [
                'stage' => 'VALSAL',
                'status' => (string) ($cluster['status_valsal'] ?? 'NOT STARTED'),
                'date' => $cluster['valsal_date'] ?? null,
                'summary' => 'Validasi sales dan dokumen boundary/SND.',
            ];

            $timeline[] = [
                'stage' => 'Batch Approval',
                'status' => (string) ($cluster['staging_status'] ?? 'NOT STARTED'),
                'date' => $cluster['submission_date'] ?? null,
                'summary' => 'Pengajuan donasi, review HO/MyRep/Finance, sampai released.',
            ];

            $timeline[] = [
                'stage' => 'DRM',
                'status' => (string) ($cluster['status_drm'] ?? ($cluster['status_current'] ?? 'NOT STARTED')),
                'date' => $cluster['drm_date'] ?? null,
                'summary' => 'DRM, APD/BOQ, dan baseline implementasi.',
            ];
        }

        $timeline[] = [
            'stage' => 'RFS',
            'status' => (string) ($cluster['status_rfs'] ?? ((string) ($cluster['status_current'] ?? '') === 'RFS' ? 'FULL RFS' : 'NY RFS')),
            'date' => $cluster['tanggal_rfs'] ?? null,
            'summary' => 'Claim RFS dan progress menuju ATP.',
        ];

        $timeline[] = [
            'stage' => 'ATP',
            'status' => (string) ($cluster['status_atp'] ?? ((string) ($cluster['status_current'] ?? '') === 'ATP' ? 'DONE' : 'NOT STARTED')),
            'date' => $cluster['actual_atp_date'] ?? $cluster['plan_atp_date'] ?? null,
            'summary' => 'Checklist ATP, actual ATP date, dan closing akhir.',
        ];

        return $timeline;
    }

    public function getFlowDocumentSummaries($clusterId)
    {
        if (!$this->myrepDocumentTablesReady()) {
            return [];
        }

        $flowTypes = ['BAK', 'VALSAL', 'BATCH_APPROVAL', 'POST_DONASI', 'DRM'];
        $rows = $this->db
            ->select("
                p.flow_type,
                COUNT(i.id_doc_item) AS total_doc,
                SUM(CASE WHEN f.id_doc_file IS NOT NULL AND f.file_path IS NOT NULL AND TRIM(f.file_path) != '' THEN 1 ELSE 0 END) AS uploaded_doc,
                SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) IN ('APPROVED', 'DONE') THEN 1 ELSE 0 END) AS approved_doc,
                SUM(CASE WHEN UPPER(COALESCE(f.status_file, '')) = 'REJECTED' THEN 1 ELSE 0 END) AS rejected_doc
            ", false)
            ->from('tb_myrep_flow_doc_package p')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = p.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->where_in('p.flow_type', $flowTypes)
            ->group_by('p.flow_type')
            ->get()
            ->result_array();

        $map = [];
        foreach ($flowTypes as $flowType) {
            $map[$flowType] = [
                'flow_type' => $flowType,
                'label' => $this->resolveFlowLabel($flowType),
                'total_doc' => 0,
                'uploaded_doc' => 0,
                'approved_doc' => 0,
                'rejected_doc' => 0,
            ];
        }

        foreach ($rows as $row) {
            $flowType = strtoupper((string) ($row['flow_type'] ?? ''));
            $map[$flowType] = [
                'flow_type' => $flowType,
                'label' => $this->resolveFlowLabel($flowType),
                'total_doc' => (int) ($row['total_doc'] ?? 0),
                'uploaded_doc' => (int) ($row['uploaded_doc'] ?? 0),
                'approved_doc' => (int) ($row['approved_doc'] ?? 0),
                'rejected_doc' => (int) ($row['rejected_doc'] ?? 0),
            ];
        }

        return array_values($map);
    }

    public function getAllFlowDocuments($clusterId)
    {
        if (!$this->myrepDocumentTablesReady()) {
            return [];
        }

        $flowTypes = ['BAK', 'VALSAL', 'BATCH_APPROVAL', 'POST_DONASI', 'DRM'];
        $rows = $this->db
            ->select("
                p.flow_type,
                g.group_label,
                i.doc_name,
                i.sort_no,
                f.id_doc_file,
                f.file_name,
                f.file_path,
                f.status_file,
                f.is_document_not_required,
                f.remark,
                f.reviewed_at,
                f.approved_at,
                f.updated_at
            ")
            ->from('tb_myrep_flow_doc_package p')
            ->join('md_myrep_flow_doc_group g', 'g.id_doc_group = p.id_doc_group', 'left')
            ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = p.id_doc_group AND i.is_active = 1', 'inner')
            ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->where_in('p.flow_type', $flowTypes)
            ->order_by('FIELD(p.flow_type, "BAK", "VALSAL", "BATCH_APPROVAL", "POST_DONASI", "DRM")', '', false)
            ->order_by('i.sort_no', 'ASC')
            ->order_by('i.id_doc_item', 'ASC')
            ->get()
            ->result_array();

        $grouped = [];
        foreach ($rows as $row) {
            $flowType = strtoupper((string) ($row['flow_type'] ?? ''));
            if (!isset($grouped[$flowType])) {
                $grouped[$flowType] = [
                    'flow_type' => $flowType,
                    'label' => $this->resolveFlowLabel($flowType),
                    'rows' => [],
                ];
            }
            $grouped[$flowType]['rows'][] = $row;
        }

        return array_values($grouped);
    }

    public function getBatchPics($clusterId)
    {
        if (!$this->db->table_exists('tb_myrep_batch_approval_pic') || !$this->db->table_exists('tb_myrep_batch_approval')) {
            return [];
        }

        return $this->db
            ->select('pic.*')
            ->from('tb_myrep_batch_approval_pic pic')
            ->join('tb_myrep_batch_approval ba', 'ba.id_batch_approval = pic.id_batch_approval', 'inner')
            ->where('ba.id_myrep_cluster', (int) $clusterId)
            ->order_by('pic.pic_no', 'ASC')
            ->order_by('pic.id_batch_pic', 'ASC')
            ->get()
            ->result_array();
    }

    public function getPoHeadersWithTermins($clusterId)
    {
        if (!$this->db->table_exists('tb_myrep_po_header')) {
            return [];
        }

        $headers = $this->db
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->order_by('po_type', 'ASC')
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();

        if (empty($headers) || !$this->db->table_exists('tb_myrep_po_termin')) {
            return $headers;
        }

        foreach ($headers as &$header) {
            $header['termin_rows'] = $this->db
                ->from('tb_myrep_po_termin')
                ->where('id_po_header', (int) ($header['id_po_header'] ?? 0))
                ->order_by('termin_no', 'ASC')
                ->get()
                ->result_array();
        }
        unset($header);

        return $headers;
    }

    public function getRfsClaims($rfsClusterId)
    {
        if (!$this->db->table_exists('tb_rfs_myrep_claim')) {
            return [];
        }

        $select = 'id_claim, claim_year, claim_month, claim_date, claim_qty, claim_note, status_claim, approval_note, submitted_by, approved_by, approved_at, created_at';
        if ($this->db->field_exists('rpm_approval_status', 'tb_rfs_myrep_claim')) {
            $select .= ', rpm_approval_status, rpm_approval_note, rpm_approved_by, rpm_approved_at';
        }

        return $this->db
            ->select($select)
            ->from('tb_rfs_myrep_claim')
            ->where('cluster_id', (int) $rfsClusterId)
            ->order_by('claim_year', 'DESC')
            ->order_by('claim_month', 'DESC')
            ->order_by('id_claim', 'DESC')
            ->get()
            ->result_array();
    }

    public function getRfsPackages($rfsClusterId)
    {
        if (
            !$this->db->table_exists('tb_rfs_myrep_doc_package')
            || !$this->db->table_exists('md_rfs_myrep_doc_group')
        ) {
            return [];
        }

        return $this->db
            ->select('
                p.id_doc_package,
                g.group_label,
                g.scope_type,
                g.sow_type,
                p.status_package,
                p.tanggal_rfs,
                p.plan_atp_date,
                p.actual_atp_date,
                p.updated_at
            ')
            ->from('tb_rfs_myrep_doc_package p')
            ->join('md_rfs_myrep_doc_group g', 'g.id_doc_group = p.id_doc_group', 'left')
            ->where('p.cluster_id', (int) $rfsClusterId)
            ->order_by('g.sort_no', 'ASC')
            ->order_by('p.id_doc_package', 'ASC')
            ->get()
            ->result_array();
    }

    private function resolveMetricValue($row, $metricMode)
    {
        $metricMode = strtoupper(trim((string) $metricMode));
        if ($metricMode === 'PO') {
            return (float) ($row['po_total_value'] ?? 0);
        }

        $status = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        $hpPlan = (float) ($row['hp_plan'] ?? 0);
        $hpBak = (float) ($row['homepass_bak'] ?? 0);
        $hpValsal = (float) ($row['homepass_valsal'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);
        $hpDrm = (float) ($row['homepass_drm'] ?? 0);

        if (in_array($status, ['DRAFT', 'BA OPEN', 'BAK'], true)) {
            return $hpBak > 0 ? $hpBak : $hpPlan;
        }

        if ($status === 'VALSAL') {
            return $hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan);
        }

        if (in_array($status, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'], true)) {
            return $hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan));
        }

        if (in_array($status, ['DRM', 'RFS', 'ATP', 'DONE'], true)) {
            return $hpDrm > 0 ? $hpDrm : ($hpDonasi > 0 ? $hpDonasi : ($hpValsal > 0 ? $hpValsal : ($hpBak > 0 ? $hpBak : $hpPlan)));
        }

        return $hpPlan;
    }

    private function getLegacyBridgeSummaryMap($rfsClusterIds)
    {
        $rfsClusterIds = array_values(array_filter(array_map('intval', (array) $rfsClusterIds)));
        if (empty($rfsClusterIds)) {
            return [];
        }

        $map = [];

        if ($this->db->table_exists('tb_rfs_myrep_claim')) {
            $claimRows = $this->db
                ->select("
                    cluster_id,
                    COUNT(id_claim) AS claim_count,
                    COALESCE(SUM(claim_qty), 0) AS claim_total_hp,
                    MAX(claim_date) AS latest_claim_date
                ", false)
                ->from('tb_rfs_myrep_claim')
                ->where_in('cluster_id', $rfsClusterIds)
                ->group_by('cluster_id')
                ->get()
                ->result_array();

            foreach ($claimRows as $row) {
                $map[(int) ($row['cluster_id'] ?? 0)] = [
                    'claim_count' => (int) ($row['claim_count'] ?? 0),
                    'claim_total_hp' => (float) ($row['claim_total_hp'] ?? 0),
                    'latest_claim_date' => $row['latest_claim_date'] ?? null,
                ];
            }
        }

        if ($this->db->table_exists('tb_rfs_myrep_doc_package')) {
            $packageRows = $this->db
                ->select("
                    cluster_id,
                    COUNT(id_doc_package) AS package_count,
                    MAX(tanggal_rfs) AS tanggal_rfs,
                    MAX(plan_atp_date) AS plan_atp_date,
                    MAX(actual_atp_date) AS actual_atp_date
                ", false)
                ->from('tb_rfs_myrep_doc_package')
                ->where_in('cluster_id', $rfsClusterIds)
                ->group_by('cluster_id')
                ->get()
                ->result_array();

            foreach ($packageRows as $row) {
                $clusterId = (int) ($row['cluster_id'] ?? 0);
                $map[$clusterId] = array_merge($map[$clusterId] ?? [], [
                    'package_count' => (int) ($row['package_count'] ?? 0),
                    'tanggal_rfs' => $row['tanggal_rfs'] ?? null,
                    'plan_atp_date' => $row['plan_atp_date'] ?? null,
                    'actual_atp_date' => $row['actual_atp_date'] ?? null,
                ]);
            }
        }

        return $map;
    }

    private function getNewFlowRows($selectedCity = '', $selectedStatus = '')
    {
        if (!$this->hasMyrepTables()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.rfs_cluster_id,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.hp_plan,
                c.status_current,
                c.created_at,
                b.homepass_bak,
                v.homepass_valsal,
                ba.hp_donasi,
                ba.staging_status,
                d.homepass_drm,
                d.drm_date
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_bak b', 'b.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_valsal v', 'v.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_batch_approval ba', 'ba.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');

        if ($selectedCity !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($selectedCity));
        }

        if ($selectedStatus !== '') {
            $this->db->where('UPPER(c.status_current)', strtoupper($selectedStatus));
        }

        return $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
    }

    private function getLegacyRfsRows($selectedCity = '', $selectedStatus = '')
    {
        if (!$this->hasLegacyRfsTables()) {
            return [];
        }

        $actualAtpSelect = 'NULL';
        if ($this->db->table_exists('tb_rfs_myrep_doc_package')) {
            $actualAtpSelect = '(SELECT MAX(dp.actual_atp_date) FROM tb_rfs_myrep_doc_package dp WHERE dp.cluster_id = c.id_cluster)';
        }

        $supportsAtpStatus = $this->db->field_exists('status_atp', 'tb_rfs_myrep_cluster');
        $doneClause = $supportsAtpStatus
            ? "WHEN {$actualAtpSelect} IS NOT NULL AND {$actualAtpSelect} < CURDATE() AND UPPER(COALESCE(c.status_atp, '')) = 'DONE' THEN 'DONE'"
            : '';

        $statusExpr = "CASE
                {$doneClause}
                WHEN {$actualAtpSelect} IS NOT NULL THEN 'ATP'
                WHEN UPPER(COALESCE(c.status_rfs, '')) IN ('PARTIAL', 'FULL RFS') THEN 'RFS'
                ELSE 'DRM'
            END";

        $this->db
            ->select("
                NULL AS id_myrep_cluster,
                c.id_cluster AS legacy_rfs_cluster_id,
                c.id_cluster AS rfs_cluster_id,
                c.cluster_name,
                NULL AS cluster_code,
                COALESCE(mt.regional_name, '-') AS regional_name,
                COALESCE(mt.province_name, '-') AS province_name,
                COALESCE(mt.city_name, '-') AS city_name,
                COALESCE(mt.team_name, '-') AS team_name,
                COALESCE(mt.rpm, '-') AS rpm,
                COALESCE(mt.sm, '-') AS sm,
                COALESCE(mt.spv, '-') AS spv,
                c.homepass AS hp_plan,
                {$statusExpr} AS status_current,
                c.created_at,
                NULL AS homepass_bak,
                NULL AS homepass_valsal,
                NULL AS hp_donasi,
                NULL AS staging_status,
                c.homepass AS homepass_drm,
                NULL AS drm_date,
                0 AS po_total_value,
                0 AS po_count
            ", false)
            ->from('tb_rfs_myrep_cluster c')
            ->join('tb_rfs_myrep_monthly_target mt', 'mt.id_target = c.id_target', 'left');

        if ($selectedCity !== '') {
            $this->db->where('UPPER(COALESCE(mt.city_name, c.city_name))', strtoupper($selectedCity));
        }

        $rows = $this->db->get()->result_array();

        if ($selectedStatus !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($selectedStatus) {
                return strtoupper(trim((string) ($row['status_current'] ?? ''))) === strtoupper($selectedStatus);
            }));
        }

        return $rows;
    }

    private function buildClusterKey($cityName, $clusterName)
    {
        return strtoupper(trim((string) $cityName)) . '|' . strtoupper(trim((string) $clusterName));
    }

    private function hasMyrepTables()
    {
        return $this->db->table_exists('tb_myrep_cluster');
    }

    private function hasLegacyRfsTables()
    {
        return $this->db->table_exists('tb_rfs_myrep_cluster') && $this->db->table_exists('tb_rfs_myrep_monthly_target');
    }

    private function myrepDocumentTablesReady()
    {
        return $this->db->table_exists('md_myrep_flow_doc_group')
            && $this->db->table_exists('md_myrep_flow_doc_item')
            && $this->db->table_exists('tb_myrep_flow_doc_package')
            && $this->db->table_exists('tb_myrep_flow_doc_file');
    }

    private function getPoMap($clusterIds)
    {
        if (!$this->db->table_exists('tb_myrep_po_header')) {
            return [];
        }

        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $rows = $this->db
            ->select('id_myrep_cluster, COUNT(id_po_header) AS po_count, COALESCE(SUM(po_value),0) AS po_total_value')
            ->from('tb_myrep_po_header')
            ->where_in('id_myrep_cluster', $clusterIds)
            ->group_by('id_myrep_cluster')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['id_myrep_cluster'] ?? 0)] = [
                'po_count' => (int) ($row['po_count'] ?? 0),
                'po_total_value' => (float) ($row['po_total_value'] ?? 0),
            ];
        }

        return $map;
    }

    private function resolveFlowLabel($flowType)
    {
        $labels = [
            'BAK' => 'BAK',
            'VALSAL' => 'VALSAL',
            'BATCH_APPROVAL' => 'Batch Approval',
            'POST_DONASI' => 'Post Donasi',
            'DRM' => 'DRM',
        ];

        $flowType = strtoupper(trim((string) $flowType));
        return $labels[$flowType] ?? $flowType;
    }
}
