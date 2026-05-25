<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPO_MyRep extends CI_Model
{
    /** @var array<string,bool>|null */
    private $currentUserAllowedCitySet = null;

    private $defaultTerminPercents = [20, 25, 15, 30, 10];

    public function __construct()
    {
        parent::__construct();
        if ($this->shouldRestrictCityByUser()) {
            $this->getCurrentUserAllowedCitySet();
        }
    }

    public function tablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_po_header',
            'tb_myrep_po_termin',
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
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_po_header p', 'p.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.city_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $rows = $this->db->get()->result_array();

        $cities = [];
        foreach ($rows as $row) {
            $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($cityName !== '') {
                $cities[] = $cityName;
            }
        }

        return array_values(array_unique($cities));
    }

    public function getEligibleClusterOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                d.id_drm,
                d.drm_date,
                d.homepass_drm
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->group_start()
                ->where_in('UPPER(c.status_current)', ['DRM', 'RFS', 'ATP', 'DONE'])
                ->or_where('d.id_drm IS NOT NULL', null, false)
            ->group_end()
            ->order_by('c.city_name', 'ASC')
            ->order_by('c.cluster_name', 'ASC');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $this->db->get()->result_array();
    }

    public function getRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                c.id_myrep_cluster,
                c.cluster_name,
                c.cluster_code,
                c.regional_name,
                c.province_name,
                c.city_name,
                c.team_name,
                c.rpm,
                c.sm,
                c.spv,
                c.status_current,
                c.created_at,
                d.id_drm,
                d.drm_date,
                d.homepass_drm
            ')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        $rows = $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $poMetaMap = $this->getPoMetaMap(array_column($rows, 'id_myrep_cluster'));
        $filtered = [];

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $meta = $poMetaMap[$clusterId] ?? $this->buildEmptyMeta();
            $mergedRow = array_merge($row, $meta);

            if ($status !== '' && strtoupper((string) ($mergedRow['po_stage_status'] ?? 'NOT ISSUED')) !== strtoupper($status)) {
                continue;
            }

            $filtered[] = $mergedRow;
        }

        return $filtered;
    }

    public function getPoListRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('
                p.id_po_header,
                p.id_myrep_cluster,
                p.po_type,
                p.po_category,
                p.po_number,
                p.po_date,
                p.po_value,
                p.status_po,
                p.po_version_label,
                c.cluster_name,
                c.city_name,
                c.regional_name,
                c.status_current
            ')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $rows = $this->db
            ->order_by('p.po_date', 'DESC')
            ->order_by('p.po_number', 'ASC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $headerIds = array_values(array_filter(array_map('intval', array_column($rows, 'id_po_header'))));
        $terminRows = $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin')
            ->where_in('id_po_header', $headerIds)
            ->get()
            ->result_array();

        $terminMap = [];
        $terminByHeader = [];
        foreach ($terminRows as $termin) {
            $headerId = (int) ($termin['id_po_header'] ?? 0);
            $terminByHeader[$headerId][] = $termin;
            if (!isset($terminMap[$headerId])) {
                $terminMap[$headerId] = [
                    'total' => 0,
                    'progress' => 0,
                    'paid' => 0,
                    'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                    'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                ];
            }
            $terminMap[$headerId]['total']++;
            $statusTermin = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
            $terminNo = (int) ($termin['termin_no'] ?? 0);
            $terminValue = (float) ($termin['termin_value'] ?? 0);

            if ($terminNo >= 1 && $terminNo <= 5 && !in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                // Plan invoice hanya untuk termin yang belum ditagihkan
                $terminMap[$headerId]['plan_invoice'][$terminNo] = $terminValue;
            }
            if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                $terminMap[$headerId]['progress']++;
                if ($terminNo >= 1 && $terminNo <= 5) {
                    $terminMap[$headerId]['done_invoice'][$terminNo] = $terminValue;
                }
            }
            if ($statusTermin === 'PAID') {
                $terminMap[$headerId]['paid']++;
            }
        }

        foreach ($rows as &$row) {
            $headerId = (int) ($row['id_po_header'] ?? 0);
            $meta = $terminMap[$headerId] ?? [
                'total' => 0,
                'progress' => 0,
                'paid' => 0,
                'plan_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'done_invoice' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ];
            $row['termin_total_count'] = (int) $meta['total'];
            $row['termin_progress_count'] = (int) $meta['progress'];
            $row['termin_paid_count'] = (int) $meta['paid'];
            $row['plan_invoice_per_termin'] = $meta['plan_invoice'];
            $row['done_invoice_per_termin'] = $meta['done_invoice'];
            $row['plan_invoice_total'] = array_sum($meta['plan_invoice']);
            $row['done_invoice_total'] = array_sum($meta['done_invoice']);
            $row['total_invoiced'] = $row['done_invoice_total'];
            // Samakan definisi dengan modal breakdown:
            // Outstanding Total = total termin yang belum BILLED/PAID.
            $row['outstanding_total'] = (float) $row['plan_invoice_total'];
            $terminsForHeader = $terminByHeader[$headerId] ?? [];
            $row['po_stage_status'] = $this->resolveStageStatus($terminsForHeader);
        }
        unset($row);

        return $rows;
    }

    public function getDashboardSummary($rows)
    {
        $summary = [
            'total_cluster' => 0,
            'not_issued' => 0,
            'issued' => 0,
            'partial_payment' => 0,
            'fully_paid' => 0,
            'closed' => 0,
        ];

        foreach ($rows as $row) {
            $summary['total_cluster']++;
            $status = strtoupper(trim((string) ($row['po_summary_status'] ?? 'NOT ISSUED')));
            if ($status === 'ISSUED') {
                $summary['issued']++;
            } elseif ($status === 'PARTIAL PAYMENT') {
                $summary['partial_payment']++;
            } elseif ($status === 'FULLY PAID') {
                $summary['fully_paid']++;
            } elseif ($status === 'CLOSED') {
                $summary['closed']++;
            } else {
                $summary['not_issued']++;
            }
        }

        return $summary;
    }

    public function getTerminBreakdownByType($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('p.id_po_header, p.po_type, p.po_value')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner');

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $headerRows = $this->db->get()->result_array();
        if (empty($headerRows)) {
            return [];
        }

        $headerIds = array_values(array_filter(array_map('intval', array_column($headerRows, 'id_po_header'))));
        if (empty($headerIds)) {
            return [];
        }

        $terminRows = $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin')
            ->where_in('id_po_header', $headerIds)
            ->get()
            ->result_array();

        $headerMeta = [];
        foreach ($headerRows as $headerRow) {
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $headerMeta[$headerId] = [
                'po_type' => strtoupper(trim((string) ($headerRow['po_type'] ?? 'CLUSTER'))),
                'po_value' => (float) ($headerRow['po_value'] ?? 0),
            ];
        }

        $result = [
            'CLUSTER' => [
                'po_type' => 'CLUSTER',
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
            'SUBFEEDER' => [
                'po_type' => 'SUBFEEDER',
                'total_po_value' => 0,
                'term_done_count' => 0,
                'termin_values' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'total_invoiced_value' => 0,
                'outstanding_value' => 0,
            ],
        ];

        foreach ($headerMeta as $meta) {
            $type = $meta['po_type'] === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            $result[$type]['total_po_value'] += (float) $meta['po_value'];
        }

        foreach ($terminRows as $terminRow) {
            $headerId = (int) ($terminRow['id_po_header'] ?? 0);
            if (!isset($headerMeta[$headerId])) {
                continue;
            }

            $type = $headerMeta[$headerId]['po_type'] === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            $terminNo = (int) ($terminRow['termin_no'] ?? 0);
            $terminValue = (float) ($terminRow['termin_value'] ?? 0);
            $terminStatus = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));

            if (in_array($terminStatus, ['BILLED', 'PAID'], true)) {
                $result[$type]['term_done_count']++;
                $result[$type]['total_invoiced_value'] += $terminValue;
            } elseif ($terminNo >= 1 && $terminNo <= 5) {
                // Outstanding per termin: hanya yang belum billed/paid.
                $result[$type]['termin_values'][$terminNo] += $terminValue;
                $result[$type]['outstanding_value'] += $terminValue;
            }
        }

        return array_values($result);
    }

    public function getTerminBreakdownDetailRows($city = '', $status = '', $poType = 'CLUSTER', $metric = '', $termNo = 0)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $poType = strtoupper(trim((string) $poType));
        if (!in_array($poType, ['CLUSTER', 'SUBFEEDER'], true)) {
            $poType = 'CLUSTER';
        }

        $this->db
            ->select('
                p.id_po_header,
                p.po_number,
                p.po_date,
                p.po_type,
                p.po_value,
                p.status_po,
                c.id_myrep_cluster,
                c.cluster_name,
                c.city_name,
                c.regional_name
            ')
            ->from('tb_myrep_po_header p')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('UPPER(p.po_type)', $poType);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }
        if ($status !== '') {
            $this->db->where('UPPER(p.status_po)', strtoupper($status));
        }

        $headerRows = $this->db
            ->order_by('p.po_date', 'DESC')
            ->order_by('p.po_number', 'ASC')
            ->get()
            ->result_array();

        if (empty($headerRows)) {
            return [];
        }

        $headerMap = [];
        $headerIds = [];
        foreach ($headerRows as $headerRow) {
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $headerIds[] = $headerId;
            $headerMap[$headerId] = $headerRow;
        }

        $terminRows = $this->db
            ->select('id_po_header, termin_no, termin_value, status_termin')
            ->from('tb_myrep_po_termin')
            ->where_in('id_po_header', $headerIds)
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();

        $detailRows = [];
        $metric = strtolower(trim((string) $metric));
        $termNo = (int) $termNo;

        if ($metric === 'po_qty' || $metric === 'total_po') {
            foreach ($headerRows as $headerRow) {
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => null,
                    'status_termin' => null,
                    'amount' => $metric === 'po_qty' ? 1 : (float) ($headerRow['po_value'] ?? 0),
                ];
            }
            return $detailRows;
        }

        if ($metric === 'term_done') {
            foreach ($terminRows as $terminRow) {
                $headerId = (int) ($terminRow['id_po_header'] ?? 0);
                if (!isset($headerMap[$headerId])) {
                    continue;
                }
                $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
                if (!in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                    continue;
                }
                $headerRow = $headerMap[$headerId];
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => (int) ($terminRow['termin_no'] ?? 0),
                    'status_termin' => $statusTermin,
                    'amount' => 1,
                ];
            }
            return $detailRows;
        }

        if ($metric === 'outstanding_total') {
            $outstandingPerPo = [];
            foreach ($terminRows as $terminRow) {
                $headerId = (int) ($terminRow['id_po_header'] ?? 0);
                $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
                if (in_array($statusTermin, ['BILLED', 'PAID'], true)) {
                    continue;
                }
                if (!isset($outstandingPerPo[$headerId])) {
                    $outstandingPerPo[$headerId] = 0;
                }
                $outstandingPerPo[$headerId] += (float) ($terminRow['termin_value'] ?? 0);
            }
            foreach ($outstandingPerPo as $headerId => $amount) {
                if ($amount == 0 || !isset($headerMap[$headerId])) {
                    continue;
                }
                $headerRow = $headerMap[$headerId];
                $detailRows[] = [
                    'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                    'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                    'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                    'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                    'po_date' => (string) ($headerRow['po_date'] ?? ''),
                    'termin_no' => null,
                    'status_termin' => 'OUTSTANDING',
                    'amount' => (float) $amount,
                ];
            }
            return $detailRows;
        }

        foreach ($terminRows as $terminRow) {
            $headerId = (int) ($terminRow['id_po_header'] ?? 0);
            if (!isset($headerMap[$headerId])) {
                continue;
            }
            $statusTermin = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
            $terminNoRow = (int) ($terminRow['termin_no'] ?? 0);
            $include = false;

            if ($metric === 'total_invoiced') {
                $include = in_array($statusTermin, ['BILLED', 'PAID'], true);
            } elseif ($metric === 'outstanding_term' && $termNo >= 1 && $termNo <= 5) {
                $include = ($terminNoRow === $termNo && !in_array($statusTermin, ['BILLED', 'PAID'], true));
            }

            if (!$include) {
                continue;
            }

            $headerRow = $headerMap[$headerId];
            $detailRows[] = [
                'cluster_name' => (string) ($headerRow['cluster_name'] ?? '-'),
                'city_name' => (string) ($headerRow['city_name'] ?? '-'),
                'regional_name' => (string) ($headerRow['regional_name'] ?? '-'),
                'po_number' => (string) ($headerRow['po_number'] ?? '-'),
                'po_date' => (string) ($headerRow['po_date'] ?? ''),
                'termin_no' => $terminNoRow,
                'status_termin' => $statusTermin,
                'amount' => (float) ($terminRow['termin_value'] ?? 0),
            ];
        }

        return $detailRows;
    }

    public function getClusterById($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        $row = $this->db->get()->row_array();

        if (empty($row)) {
            return [];
        }

        return array_merge($row, $this->getPoMetaMap([(int) $clusterId])[(int) $clusterId] ?? $this->buildEmptyMeta());
    }

    public function getPoHeadersByClusterId($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        if (empty($this->getClusterById((int) $clusterId))) {
            return [];
        }

        return $this->db
            ->select('*')
            ->from('tb_myrep_po_header')
            ->where('id_myrep_cluster', (int) $clusterId)
            ->order_by('po_type', 'ASC')
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();
    }

    public function getTerminRowsByPoId($poId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        return $this->db
            ->select('*')
            ->from('tb_myrep_po_termin')
            ->where('id_po_header', (int) $poId)
            ->order_by('termin_no', 'ASC')
            ->get()
            ->result_array();
    }

    public function getTerminById($terminId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('t.*, p.id_myrep_cluster, p.po_number, p.po_type, p.po_category')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header p', 'p.id_po_header = t.id_po_header', 'inner')
            ->join('tb_myrep_cluster c', 'c.id_myrep_cluster = p.id_myrep_cluster', 'inner')
            ->where('t.id_po_termin', (int) $terminId);

        if (!$this->applyAllowedCityRestriction('c.city_name')) {
            return [];
        }

        return $this->db->get()->row_array();
    }

    public function createPoHeader($clusterId, $payload)
    {
        if (!$this->tablesReady()) {
            return 0;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return 0;
        }

        if (empty($this->getClusterById($clusterId))) {
            return 0;
        }

        $poValue = (float) ($payload['po_value'] ?? 0);

        $this->db->trans_start();

        $this->db->insert('tb_myrep_po_header', [
            'id_myrep_cluster' => $clusterId,
            'parent_po_header_id' => !empty($payload['parent_po_header_id']) ? (int) $payload['parent_po_header_id'] : null,
            'po_type' => (string) $payload['po_type'],
            'po_category' => (string) $payload['po_category'],
            'po_number' => (string) $payload['po_number'],
            'po_date' => $payload['po_date'],
            'po_value' => $poValue,
            'status_po' => (string) $payload['status_po'],
            'po_version_label' => $payload['po_version_label'] !== '' ? (string) $payload['po_version_label'] : null,
            'remark_po' => $payload['remark_po'] !== '' ? (string) $payload['remark_po'] : null,
            'created_by' => (int) $payload['created_by'],
            'updated_by' => (int) $payload['updated_by'],
        ]);

        $poHeaderId = (int) $this->db->insert_id();
        foreach ($this->defaultTerminPercents as $index => $percent) {
            $terminNo = $index + 1;
            $terminValue = round(($poValue * $percent) / 100, 2);
            $this->db->insert('tb_myrep_po_termin', [
                'id_po_header' => $poHeaderId,
                'termin_no' => $terminNo,
                'termin_percent' => $percent,
                'termin_value' => $terminValue,
                'status_termin' => 'NOT READY',
                'created_by' => (int) $payload['created_by'],
                'updated_by' => (int) $payload['updated_by'],
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $poHeaderId : 0;
    }

    public function updateTermin($terminId, $payload)
    {
        if (!$this->tablesReady()) {
            return false;
        }

        $this->db
            ->where('id_po_termin', (int) $terminId)
            ->update('tb_myrep_po_termin', [
                'status_termin' => (string) $payload['status_termin'],
                'invoice_number' => $payload['invoice_number'] !== '' ? (string) $payload['invoice_number'] : null,
                'invoice_date' => $payload['invoice_date'],
                'bast_date' => $payload['bast_date'],
                'payment_date' => $payload['payment_date'],
                'remark_termin' => $payload['remark_termin'] !== '' ? (string) $payload['remark_termin'] : null,
                'updated_by' => (int) $payload['updated_by'],
            ]);

        $termin = $this->getTerminById((int) $terminId);
        if (!empty($termin['id_po_header'])) {
            $this->syncPoStatus((int) $termin['id_po_header']);
        }

        return $this->db->affected_rows() >= 0;
    }

    private function syncPoStatus($poHeaderId)
    {
        $termins = $this->getTerminRowsByPoId((int) $poHeaderId);
        if (empty($termins)) {
            return;
        }

        $statuses = array_map(static function ($row) {
            return strtoupper(trim((string) ($row['status_termin'] ?? 'NOT READY')));
        }, $termins);

        $poStatus = 'NOT ISSUED';
        if (count(array_filter($statuses, static function ($status) {
            return $status === 'PAID';
        })) === count($statuses)) {
            $poStatus = 'FULLY PAID';
        } elseif (count(array_filter($statuses, static function ($status) {
            return in_array($status, ['BILLED', 'PAID'], true);
        })) > 0) {
            $poStatus = 'PARTIAL PAYMENT';
        } elseif (count(array_filter($statuses, static function ($status) {
            return in_array($status, ['READY BILLING', 'BILLED', 'PAID'], true);
        })) > 0) {
            $poStatus = 'ISSUED';
        }

        $this->db
            ->where('id_po_header', (int) $poHeaderId)
            ->update('tb_myrep_po_header', [
                'status_po' => $poStatus,
            ]);
    }

    private function buildEmptyMeta()
    {
        return [
            'po_count' => 0,
            'po_cluster_count' => 0,
            'po_subfeeder_count' => 0,
            'po_total_value' => 0,
            'termin_total_count' => 0,
            'termin_progress_count' => 0,
            'termin_paid_count' => 0,
            'last_po_date' => null,
            'po_summary_status' => 'NOT ISSUED',
            'po_stage_status' => 'NOT ISSUED',
        ];
    }

    private function getPoMetaMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $headerRows = $this->db
            ->select('id_po_header, id_myrep_cluster, po_type, po_value, status_po, po_date')
            ->from('tb_myrep_po_header')
            ->where_in('id_myrep_cluster', $clusterIds)
            ->get()
            ->result_array();

        $headerIds = array_column($headerRows, 'id_po_header');
        $terminRows = [];
        if (!empty($headerIds)) {
            $terminRows = $this->db
                ->select('id_po_header, termin_no, status_termin')
                ->from('tb_myrep_po_termin')
                ->where_in('id_po_header', $headerIds)
                ->get()
                ->result_array();
        }

        $terminGrouped = [];
        foreach ($terminRows as $terminRow) {
            $terminGrouped[(int) ($terminRow['id_po_header'] ?? 0)][] = [
                'termin_no' => (int) ($terminRow['termin_no'] ?? 0),
                'status_termin' => strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY'))),
            ];
        }

        $metaMap = [];
        foreach ($clusterIds as $clusterId) {
            $metaMap[$clusterId] = $this->buildEmptyMeta();
        }

        foreach ($headerRows as $headerRow) {
            $clusterId = (int) ($headerRow['id_myrep_cluster'] ?? 0);
            if (!isset($metaMap[$clusterId])) {
                $metaMap[$clusterId] = $this->buildEmptyMeta();
            }

            $metaMap[$clusterId]['po_count']++;
            $metaMap[$clusterId]['po_total_value'] += (float) ($headerRow['po_value'] ?? 0);
            $poType = strtoupper(trim((string) ($headerRow['po_type'] ?? '')));
            if ($poType === 'CLUSTER') {
                $metaMap[$clusterId]['po_cluster_count']++;
            } elseif ($poType === 'SUBFEEDER') {
                $metaMap[$clusterId]['po_subfeeder_count']++;
            }

            if (!empty($headerRow['po_date'])) {
                if (empty($metaMap[$clusterId]['last_po_date']) || $headerRow['po_date'] > $metaMap[$clusterId]['last_po_date']) {
                    $metaMap[$clusterId]['last_po_date'] = $headerRow['po_date'];
                }
            }

            $termins = $terminGrouped[(int) ($headerRow['id_po_header'] ?? 0)] ?? [];
            $metaMap[$clusterId]['termin_total_count'] += count($termins);
            $metaMap[$clusterId]['termin_progress_count'] += count(array_filter($termins, static function ($termin) {
                return in_array((string) ($termin['status_termin'] ?? 'NOT READY'), ['BILLED', 'PAID'], true);
            }));
            $metaMap[$clusterId]['termin_paid_count'] += count(array_filter($termins, static function ($termin) {
                return (string) ($termin['status_termin'] ?? 'NOT READY') === 'PAID';
            }));
        }

        // Stage status diambil dari PO terbaru per cluster.
        $latestHeaderByCluster = [];
        foreach ($headerRows as $headerRow) {
            $clusterId = (int) ($headerRow['id_myrep_cluster'] ?? 0);
            $headerId = (int) ($headerRow['id_po_header'] ?? 0);
            $poDate = (string) ($headerRow['po_date'] ?? '');
            if (!isset($latestHeaderByCluster[$clusterId])) {
                $latestHeaderByCluster[$clusterId] = ['id_po_header' => $headerId, 'po_date' => $poDate];
                continue;
            }
            $current = $latestHeaderByCluster[$clusterId];
            if ($poDate > (string) ($current['po_date'] ?? '') || ($poDate === (string) ($current['po_date'] ?? '') && $headerId > (int) ($current['id_po_header'] ?? 0))) {
                $latestHeaderByCluster[$clusterId] = ['id_po_header' => $headerId, 'po_date' => $poDate];
            }
        }

        foreach ($metaMap as $clusterId => &$meta) {
            if ($meta['po_count'] === 0) {
                $meta['po_summary_status'] = 'NOT ISSUED';
            } elseif ($meta['termin_total_count'] > 0 && $meta['termin_paid_count'] === $meta['termin_total_count']) {
                $meta['po_summary_status'] = 'FULLY PAID';
            } elseif ($meta['termin_progress_count'] > 0) {
                $meta['po_summary_status'] = 'PARTIAL PAYMENT';
            } else {
                $meta['po_summary_status'] = 'ISSUED';
            }

            $latestHeaderId = (int) ($latestHeaderByCluster[$clusterId]['id_po_header'] ?? 0);
            $latestTermins = $latestHeaderId > 0 ? ($terminGrouped[$latestHeaderId] ?? []) : [];
            $meta['po_stage_status'] = $this->resolveStageStatus($latestTermins);
        }
        unset($meta);

        return $metaMap;
    }

    private function resolveStageStatus(array $termins)
    {
        if (empty($termins)) {
            return 'NOT ISSUED';
        }

        $statusByTermin = [];
        foreach ($termins as $termin) {
            $no = (int) ($termin['termin_no'] ?? 0);
            if ($no < 1 || $no > 5) {
                continue;
            }
            $statusByTermin[$no] = strtoupper(trim((string) ($termin['status_termin'] ?? 'NOT READY')));
        }

        $labels = [
            1 => 'DP',
            2 => 'ATP CW',
            3 => 'FULL OPM',
            4 => 'RFS',
            5 => 'FAC',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $status = $statusByTermin[$i] ?? 'NOT READY';
            if (!in_array($status, ['BILLED', 'PAID'], true)) {
                return $labels[$i];
            }
        }

        return 'FAC';
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
}
