<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MImplementasi_BOQ_MyRep extends CI_Model
{
    public function tablesReady()
    {
        $requiredTables = [
            'tb_myrep_cluster',
            'tb_myrep_boq_baseline',
            'tb_myrep_boq_baseline_item',
            'tb_myrep_boq_progress_item',
            'tb_myrep_boq_progress_photo',
            'md_myrep_boq_item',
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
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false)
            ->where('c.city_name IS NOT NULL', null, false)
            ->where("TRIM(c.city_name) !=", '')
            ->order_by('c.city_name', 'ASC')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $city = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($city !== '') {
                $result[] = $city;
            }
        }

        return $result;
    }

    public function getRows($city = '', $status = '')
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, c.created_at, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm, b.id_boq_baseline, b.approved_at AS boq_approved_at')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false)
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ;

        if ($city !== '') {
            $this->db->where('UPPER(c.city_name)', strtoupper($city));
        }

        $rows = $this->db
            ->order_by('c.created_at', 'DESC')
            ->order_by('c.cluster_name', 'ASC')
            ->get()
            ->result_array();

        $metaMap = $this->getClusterProgressMetaMap(array_column($rows, 'id_myrep_cluster'));
        $filtered = [];

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $meta = $metaMap[$clusterId] ?? $this->buildEmptyClusterMeta();
            $row = array_merge($row, $meta);

            if ($status !== '' && strtoupper((string) $meta['implementation_status']) !== strtoupper($status)) {
                continue;
            }

            $filtered[] = $row;
        }

        return $filtered;
    }

    public function getClusterById($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $row = $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm, d.remark_drm, b.id_boq_baseline, b.approved_at AS boq_approved_at')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false)
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left')
            ->where('c.id_myrep_cluster', (int) $clusterId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return array_merge($row, $this->getClusterProgressMetaMap([(int) $clusterId])[(int) $clusterId] ?? $this->buildEmptyClusterMeta());
    }

    public function getBaselineCompareRows($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('bi.id_boq_baseline_item, bi.id_boq_baseline, bi.id_boq_item, bi.qty_boq, bi.jumlah_foto, bi.remarks_rule, bi.target_foto_required, bi.item_note, m.excel_item_name, m.item_name, m.item_type, m.photo_type, m.sort_no')
            ->from('tb_myrep_boq_baseline_item bi')
            ->join('tb_myrep_boq_baseline b', 'b.id_boq_baseline = bi.id_boq_baseline', 'inner')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = bi.id_boq_item', 'inner')
            ->where('b.id_myrep_cluster', (int) $clusterId)
            ->where('b.status_baseline', 'ACTIVE')
            ->order_by('m.sort_no', 'ASC')
            ->order_by('bi.id_boq_baseline_item', 'ASC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $baselineItemIds = array_column($rows, 'id_boq_baseline_item');
        $progressMap = $this->getProgressAggregateMap($baselineItemIds);

        foreach ($rows as &$row) {
            $aggregate = $progressMap[(int) $row['id_boq_baseline_item']] ?? [
                'progress_qty' => 0,
                'uploaded_photos' => 0,
                'entry_count' => 0,
                'last_progress_date' => null,
            ];

            $qtyBoq = (float) ($row['qty_boq'] ?? 0);
            $progressQty = (float) ($aggregate['progress_qty'] ?? 0);
            $targetPhoto = (int) ($row['target_foto_required'] ?? 0);
            $uploadedPhotos = (int) ($aggregate['uploaded_photos'] ?? 0);

            $row['progress_qty'] = $progressQty;
            $row['remaining_qty'] = max($qtyBoq - $progressQty, 0);
            $row['uploaded_photos'] = $uploadedPhotos;
            $row['remaining_photos'] = max($targetPhoto - $uploadedPhotos, 0);
            $row['entry_count'] = (int) ($aggregate['entry_count'] ?? 0);
            $row['last_progress_date'] = $aggregate['last_progress_date'] ?? null;
            $row['completion_percent'] = $qtyBoq > 0 ? min(100, round(($progressQty / $qtyBoq) * 100, 2)) : 0;
            $row['implementation_status'] = $this->resolveItemStatus($qtyBoq, $progressQty, $targetPhoto, $uploadedPhotos);
        }
        unset($row);

        return $rows;
    }

    public function getProgressHistoryMap($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('p.id_progress_item, p.id_boq_baseline_item, p.progress_date, p.qty_progress, p.status_progress, p.remark_progress, p.created_at, u.nama_user')
            ->from('tb_myrep_boq_progress_item p')
            ->join('tb_master_user u', 'u.id_user = p.created_by', 'left')
            ->where('p.id_myrep_cluster', (int) $clusterId)
            ->order_by('p.progress_date', 'DESC')
            ->order_by('p.id_progress_item', 'DESC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $progressIds = array_column($rows, 'id_progress_item');
        $photoMap = $this->getPhotoMap($progressIds);
        $historyMap = [];

        foreach ($rows as $row) {
            $row['photos'] = $photoMap[(int) ($row['id_progress_item'] ?? 0)] ?? [];
            $historyMap[(int) ($row['id_boq_baseline_item'] ?? 0)][] = $row;
        }

        return $historyMap;
    }

    public function createProgressEntry($clusterId, $baselineItemId, $payload, $photoRows = [])
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        if ($clusterId <= 0 || $baselineItemId <= 0) {
            return 0;
        }

        $baselineItem = $this->db
            ->select('bi.id_boq_baseline_item, bi.id_boq_baseline, b.id_myrep_cluster')
            ->from('tb_myrep_boq_baseline_item bi')
            ->join('tb_myrep_boq_baseline b', 'b.id_boq_baseline = bi.id_boq_baseline', 'inner')
            ->where('bi.id_boq_baseline_item', $baselineItemId)
            ->where('b.id_myrep_cluster', $clusterId)
            ->where('b.status_baseline', 'ACTIVE')
            ->get()
            ->row_array();

        if (empty($baselineItem)) {
            return 0;
        }

        $this->db->trans_start();

        $this->db->insert('tb_myrep_boq_progress_item', [
            'id_myrep_cluster' => $clusterId,
            'id_boq_baseline' => (int) $baselineItem['id_boq_baseline'],
            'id_boq_baseline_item' => $baselineItemId,
            'progress_date' => (string) $payload['progress_date'],
            'qty_progress' => (float) $payload['qty_progress'],
            'status_progress' => (string) $payload['status_progress'],
            'remark_progress' => $payload['remark_progress'] !== '' ? (string) $payload['remark_progress'] : null,
            'created_by' => (int) $payload['created_by'],
            'updated_by' => (int) $payload['updated_by'],
        ]);

        $progressItemId = (int) $this->db->insert_id();

        foreach ($photoRows as $photo) {
            $this->db->insert('tb_myrep_boq_progress_photo', [
                'id_progress_item' => $progressItemId,
                'file_name' => (string) $photo['file_name'],
                'file_path' => (string) $photo['file_path'],
                'caption' => $photo['caption'] !== '' ? (string) $photo['caption'] : null,
                'uploaded_by' => (int) $payload['created_by'],
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $progressItemId : 0;
    }

    public function getDashboardSummary($rows)
    {
        $summary = [
            'total_cluster' => 0,
            'not_started' => 0,
            'on_progress' => 0,
            'done' => 0,
        ];

        foreach ($rows as $row) {
            $summary['total_cluster']++;
            $status = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
            if ($status === 'DONE') {
                $summary['done']++;
            } elseif ($status === 'ON PROGRESS') {
                $summary['on_progress']++;
            } else {
                $summary['not_started']++;
            }
        }

        return $summary;
    }

    private function getClusterProgressMetaMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $baselineRows = $this->db
            ->select('b.id_myrep_cluster, bi.id_boq_baseline_item, bi.qty_boq, bi.target_foto_required')
            ->from('tb_myrep_boq_baseline b')
            ->join('tb_myrep_boq_baseline_item bi', 'bi.id_boq_baseline = b.id_boq_baseline', 'inner')
            ->where('b.status_baseline', 'ACTIVE')
            ->where_in('b.id_myrep_cluster', $clusterIds)
            ->get()
            ->result_array();

        $baselineItemIds = array_column($baselineRows, 'id_boq_baseline_item');
        $progressMap = $this->getProgressAggregateMap($baselineItemIds);

        $result = [];
        foreach ($clusterIds as $clusterId) {
            $result[$clusterId] = $this->buildEmptyClusterMeta();
        }

        foreach ($baselineRows as $row) {
            $clusterId = (int) $row['id_myrep_cluster'];
            $aggregate = $progressMap[(int) $row['id_boq_baseline_item']] ?? [
                'progress_qty' => 0,
                'uploaded_photos' => 0,
                'entry_count' => 0,
                'last_progress_date' => null,
            ];

            $meta = &$result[$clusterId];
            $meta['total_item']++;
            $meta['target_qty_total'] += (float) ($row['qty_boq'] ?? 0);
            $meta['actual_qty_total'] += (float) ($aggregate['progress_qty'] ?? 0);
            $meta['target_photo_total'] += (int) ($row['target_foto_required'] ?? 0);
            $meta['uploaded_photo_total'] += (int) ($aggregate['uploaded_photos'] ?? 0);
            $meta['progress_entry_total'] += (int) ($aggregate['entry_count'] ?? 0);

            if (!empty($aggregate['last_progress_date'])) {
                if (empty($meta['last_progress_date']) || $aggregate['last_progress_date'] > $meta['last_progress_date']) {
                    $meta['last_progress_date'] = $aggregate['last_progress_date'];
                }
            }

            $itemStatus = $this->resolveItemStatus(
                (float) ($row['qty_boq'] ?? 0),
                (float) ($aggregate['progress_qty'] ?? 0),
                (int) ($row['target_foto_required'] ?? 0),
                (int) ($aggregate['uploaded_photos'] ?? 0)
            );

            if ($itemStatus === 'DONE') {
                $meta['done_item']++;
            } elseif ($itemStatus === 'ON PROGRESS') {
                $meta['progress_item']++;
            } else {
                $meta['not_started_item']++;
            }
        }

        foreach ($result as &$meta) {
            if ($meta['total_item'] > 0 && $meta['done_item'] === $meta['total_item']) {
                $meta['implementation_status'] = 'DONE';
            } elseif ($meta['progress_entry_total'] > 0 || $meta['progress_item'] > 0 || $meta['done_item'] > 0) {
                $meta['implementation_status'] = 'ON PROGRESS';
            } else {
                $meta['implementation_status'] = 'NOT STARTED';
            }
        }
        unset($meta);

        return $result;
    }

    private function getProgressAggregateMap($baselineItemIds)
    {
        $baselineItemIds = array_values(array_filter(array_map('intval', (array) $baselineItemIds)));
        if (empty($baselineItemIds)) {
            return [];
        }

        $progressRows = $this->db
            ->select('p.id_boq_baseline_item, COALESCE(SUM(p.qty_progress), 0) AS progress_qty, COUNT(*) AS entry_count, MAX(p.progress_date) AS last_progress_date', false)
            ->from('tb_myrep_boq_progress_item p')
            ->where_in('p.id_boq_baseline_item', $baselineItemIds)
            ->group_by('p.id_boq_baseline_item')
            ->get()
            ->result_array();

        $map = [];
        foreach ($progressRows as $row) {
            $map[(int) $row['id_boq_baseline_item']] = [
                'progress_qty' => (float) ($row['progress_qty'] ?? 0),
                'uploaded_photos' => 0,
                'entry_count' => (int) ($row['entry_count'] ?? 0),
                'last_progress_date' => $row['last_progress_date'] ?? null,
            ];
        }

        $photoRows = $this->db
            ->select('p.id_boq_baseline_item, COUNT(photo.id_progress_photo) AS uploaded_photos', false)
            ->from('tb_myrep_boq_progress_item p')
            ->join('tb_myrep_boq_progress_photo photo', 'photo.id_progress_item = p.id_progress_item', 'inner')
            ->where_in('p.id_boq_baseline_item', $baselineItemIds)
            ->group_by('p.id_boq_baseline_item')
            ->get()
            ->result_array();

        foreach ($photoRows as $row) {
            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            if (!isset($map[$baselineItemId])) {
                $map[$baselineItemId] = [
                    'progress_qty' => 0,
                    'uploaded_photos' => 0,
                    'entry_count' => 0,
                    'last_progress_date' => null,
                ];
            }
            $map[$baselineItemId]['uploaded_photos'] = (int) ($row['uploaded_photos'] ?? 0);
        }

        return $map;
    }

    private function getPhotoMap($progressIds)
    {
        $progressIds = array_values(array_filter(array_map('intval', (array) $progressIds)));
        if (empty($progressIds)) {
            return [];
        }

        $rows = $this->db
            ->from('tb_myrep_boq_progress_photo')
            ->where_in('id_progress_item', $progressIds)
            ->order_by('id_progress_photo', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['id_progress_item'] ?? 0)][] = $row;
        }

        return $map;
    }

    private function resolveItemStatus($qtyBoq, $progressQty, $targetPhoto, $uploadedPhotos)
    {
        $qtyBoq = (float) $qtyBoq;
        $progressQty = (float) $progressQty;
        $targetPhoto = (int) $targetPhoto;
        $uploadedPhotos = (int) $uploadedPhotos;

        $qtyDone = $qtyBoq > 0 && $progressQty >= $qtyBoq;
        $photoDone = $targetPhoto <= 0 || $uploadedPhotos >= $targetPhoto;

        if ($qtyDone && $photoDone) {
            return 'DONE';
        }

        if ($progressQty > 0 || $uploadedPhotos > 0) {
            return 'ON PROGRESS';
        }

        return 'NOT STARTED';
    }

    private function buildEmptyClusterMeta()
    {
        return [
            'total_item' => 0,
            'done_item' => 0,
            'progress_item' => 0,
            'not_started_item' => 0,
            'target_qty_total' => 0,
            'actual_qty_total' => 0,
            'target_photo_total' => 0,
            'uploaded_photo_total' => 0,
            'progress_entry_total' => 0,
            'last_progress_date' => null,
            'implementation_status' => 'NOT STARTED',
        ];
    }
}
