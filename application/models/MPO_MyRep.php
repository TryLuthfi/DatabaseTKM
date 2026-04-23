<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPO_MyRep extends CI_Model
{
    private $defaultTerminPercents = [20, 25, 15, 30, 10];

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

        return array_values(array_unique($cities));
    }

    public function getEligibleClusterOptions()
    {
        if (!$this->tablesReady()) {
            return [];
        }

        return $this->db
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
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();
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

            if ($status !== '' && strtoupper((string) $mergedRow['po_summary_status']) !== strtoupper($status)) {
                continue;
            }

            $filtered[] = $mergedRow;
        }

        return $filtered;
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

    public function getClusterById($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

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

        return $this->db
            ->select('t.*, p.id_myrep_cluster, p.po_number, p.po_type, p.po_category')
            ->from('tb_myrep_po_termin t')
            ->join('tb_myrep_po_header p', 'p.id_po_header = t.id_po_header', 'inner')
            ->where('t.id_po_termin', (int) $terminId)
            ->get()
            ->row_array();
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
            'termin_paid_count' => 0,
            'last_po_date' => null,
            'po_summary_status' => 'NOT ISSUED',
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
                ->select('id_po_header, status_termin')
                ->from('tb_myrep_po_termin')
                ->where_in('id_po_header', $headerIds)
                ->get()
                ->result_array();
        }

        $terminGrouped = [];
        foreach ($terminRows as $terminRow) {
            $terminGrouped[(int) ($terminRow['id_po_header'] ?? 0)][] = strtoupper(trim((string) ($terminRow['status_termin'] ?? 'NOT READY')));
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

            $statuses = $terminGrouped[(int) ($headerRow['id_po_header'] ?? 0)] ?? [];
            $metaMap[$clusterId]['termin_total_count'] += count($statuses);
            $metaMap[$clusterId]['termin_paid_count'] += count(array_filter($statuses, static function ($status) {
                return $status === 'PAID';
            }));
        }

        foreach ($metaMap as $clusterId => &$meta) {
            if ($meta['po_count'] === 0) {
                $meta['po_summary_status'] = 'NOT ISSUED';
            } elseif ($meta['termin_total_count'] > 0 && $meta['termin_paid_count'] === $meta['termin_total_count']) {
                $meta['po_summary_status'] = 'FULLY PAID';
            } elseif ($meta['termin_paid_count'] > 0) {
                $meta['po_summary_status'] = 'PARTIAL PAYMENT';
            } else {
                $meta['po_summary_status'] = 'ISSUED';
            }
        }
        unset($meta);

        return $metaMap;
    }
}
