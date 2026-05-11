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

        $this->db
            ->distinct()
            ->select('c.city_name')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false);
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

        $rows = $this->db
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

        $this->db
            ->select('c.id_myrep_cluster, c.cluster_name, c.cluster_code, c.regional_name, c.city_name, c.status_current, c.created_at, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm, b.id_boq_baseline, b.approved_at AS boq_approved_at')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false)
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

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

        $this->db
            ->select('c.*, d.id_drm, d.drm_date, d.homepass_drm, d.status_drm, d.remark_drm, b.id_boq_baseline, b.approved_at AS boq_approved_at')
            ->from('tb_myrep_cluster c')
            ->join('tb_myrep_boq_baseline b', 'b.id_myrep_cluster = c.id_myrep_cluster AND b.status_baseline = \'ACTIVE\'', 'inner', false)
            ->join('tb_myrep_drm d', 'd.id_myrep_cluster = c.id_myrep_cluster', 'left');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

        $row = $this->db
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

        $this->db
            ->select('bi.id_boq_baseline_item, bi.id_boq_baseline, bi.id_boq_item, bi.qty_boq, bi.jumlah_foto, bi.remarks_rule, bi.target_foto_required, bi.item_note, m.excel_item_name, m.item_name, m.item_type, m.photo_type, m.sort_no')
            ->from('tb_myrep_boq_baseline_item bi')
            ->join('tb_myrep_boq_baseline b', 'b.id_boq_baseline = bi.id_boq_baseline', 'inner')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = bi.id_boq_item', 'inner')
            ->where('b.id_myrep_cluster', (int) $clusterId)
            ->where('b.status_baseline', 'ACTIVE');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

        $rows = $this->db
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
                'uploaded_harian_photos' => 0,
                'uploaded_comply_photos' => 0,
                'comply_label_count' => 0,
                'approved_comply_photos' => 0,
                'approved_comply_label_count' => 0,
                'entry_count' => 0,
                'last_progress_date' => null,
            ];
            $complyRule = $this->resolveComplyRuleMeta($row);

            $qtyBoq = (float) ($row['qty_boq'] ?? 0);
            $progressQty = (float) ($aggregate['progress_qty'] ?? 0);
            $targetPhoto = (int) ($row['target_foto_required'] ?? 0) + (int) $this->calculateTargetComplyPhotos($qtyBoq, $complyRule);
            $uploadedPhotos = (int) ($aggregate['uploaded_photos'] ?? 0);

            $row['progress_qty'] = $progressQty;
            $row['remaining_qty'] = max($qtyBoq - $progressQty, 0);
            $row['uploaded_photos'] = $uploadedPhotos;
            $row['uploaded_harian_photos'] = (int) ($aggregate['uploaded_harian_photos'] ?? 0);
            $row['uploaded_comply_photos'] = (int) ($aggregate['uploaded_comply_photos'] ?? 0);
            $row['comply_label_count'] = (int) ($aggregate['comply_label_count'] ?? 0);
            $row['target_comply_photo_required'] = (int) $this->calculateTargetComplyPhotos($qtyBoq, $complyRule);
            $row['remaining_photos'] = max($targetPhoto - $uploadedPhotos, 0);
            $row['entry_count'] = (int) ($aggregate['entry_count'] ?? 0);
            $row['last_progress_date'] = $aggregate['last_progress_date'] ?? null;
            $row['completion_percent'] = $qtyBoq > 0 ? min(100, round(($progressQty / $qtyBoq) * 100, 2)) : 0;
            $row = array_merge($row, $complyRule);
            $row['implementation_status'] = $this->resolveItemStatus($qtyBoq, $progressQty, $row, $aggregate);
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

    public function getApprovedComplyPrintGroups($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('
                photo.id_progress_photo,
                photo.file_name,
                photo.file_path,
                photo.caption,
                photo.comply_label,
                photo.status_photo,
                progress.progress_date,
                baseline_item.id_boq_baseline_item,
                boq_item.item_name,
                boq_item.item_type,
                boq_item.excel_item_name,
                boq_item.sort_no
            ')
            ->from('tb_myrep_boq_progress_photo photo')
            ->join('tb_myrep_boq_progress_item progress', 'progress.id_progress_item = photo.id_progress_item', 'inner')
            ->join('tb_myrep_boq_baseline_item baseline_item', 'baseline_item.id_boq_baseline_item = progress.id_boq_baseline_item', 'inner')
            ->join('md_myrep_boq_item boq_item', 'boq_item.id_boq_item = baseline_item.id_boq_item', 'left')
            ->where('progress.id_myrep_cluster', (int) $clusterId)
            ->where("UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'COMPLY'", null, false)
            ->where("UPPER(COALESCE(photo.status_photo, 'UPLOADED')) = 'APPROVED'", null, false)
            ->order_by('boq_item.sort_no', 'ASC')
            ->order_by('boq_item.item_name', 'ASC')
            ->order_by('photo.comply_label', 'ASC')
            ->order_by('photo.id_progress_photo', 'ASC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $sectionTitle = $this->resolveComplyPrintSectionTitle($row);
            if (!isset($groups[$sectionTitle])) {
                $groups[$sectionTitle] = [];
            }

            $groups[$sectionTitle][] = [
                'id_progress_photo' => (int) ($row['id_progress_photo'] ?? 0),
                'item_name' => (string) ($row['item_name'] ?? '-'),
                'item_type' => (string) ($row['item_type'] ?? '-'),
                'comply_label' => (string) ($row['comply_label'] ?? ''),
                'caption' => (string) ($row['caption'] ?? ''),
                'file_name' => (string) ($row['file_name'] ?? 'Foto Comply'),
                'file_path' => (string) ($row['file_path'] ?? ''),
                'progress_date' => (string) ($row['progress_date'] ?? ''),
            ];
        }

        return $groups;
    }

    public function createProgressEntry($clusterId, $baselineItemId, $payload, $photoRows = [])
    {
        $clusterId = (int) $clusterId;
        $baselineItemId = (int) $baselineItemId;
        if ($clusterId <= 0 || $baselineItemId <= 0) {
            return 0;
        }

        $this->db
            ->select('bi.id_boq_baseline_item, bi.id_boq_baseline, b.id_myrep_cluster')
            ->from('tb_myrep_boq_baseline_item bi')
            ->join('tb_myrep_boq_baseline b', 'b.id_boq_baseline = bi.id_boq_baseline', 'inner')
            ->where('bi.id_boq_baseline_item', $baselineItemId)
            ->where('b.id_myrep_cluster', $clusterId)
            ->where('b.status_baseline', 'ACTIVE');
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

        $baselineItem = $this->db->get()->row_array();

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
                'photo_category' => !empty($photo['photo_category']) ? (string) $photo['photo_category'] : 'HARIAN',
                'comply_label' => !empty($photo['comply_label']) ? (string) $photo['comply_label'] : null,
                'status_photo' => !empty($photo['status_photo']) ? (string) $photo['status_photo'] : 'APPROVED',
                'review_remark' => !empty($photo['review_remark']) ? (string) $photo['review_remark'] : null,
                'reviewed_by' => !empty($photo['reviewed_by']) ? (int) $photo['reviewed_by'] : null,
                'reviewed_at' => !empty($photo['reviewed_at']) ? (string) $photo['reviewed_at'] : null,
                'approved_at' => !empty($photo['approved_at']) ? (string) $photo['approved_at'] : null,
                'uploaded_by' => (int) $payload['created_by'],
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $progressItemId : 0;
    }

    public function deleteProgressEntry($clusterId, $progressItemId)
    {
        if (!$this->tablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $progressItemId = (int) $progressItemId;
        if ($clusterId <= 0 || $progressItemId <= 0) {
            return false;
        }

        $progressRow = $this->db
            ->from('tb_myrep_boq_progress_item')
            ->where('id_progress_item', $progressItemId)
            ->where('id_myrep_cluster', $clusterId)
            ->get()
            ->row_array();

        if (empty($progressRow)) {
            return false;
        }

        $photoRows = $this->db
            ->from('tb_myrep_boq_progress_photo')
            ->where('id_progress_item', $progressItemId)
            ->get()
            ->result_array();

        $this->db->trans_start();
        $this->db->where('id_progress_item', $progressItemId)->delete('tb_myrep_boq_progress_photo');
        $this->db->where('id_progress_item', $progressItemId)->where('id_myrep_cluster', $clusterId)->delete('tb_myrep_boq_progress_item');
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return false;
        }

        foreach ($photoRows as $photoRow) {
            $this->deletePhysicalFile((string) ($photoRow['file_path'] ?? ''));
        }

        return true;
    }

    public function getProgressPhotoById($photoId)
    {
        $photoId = (int) $photoId;
        if ($photoId <= 0 || !$this->tablesReady()) {
            return [];
        }

        return $this->db
            ->select('photo.*, progress.id_myrep_cluster, progress.id_boq_baseline_item, boq_item.item_name, boq_item.item_type')
            ->from('tb_myrep_boq_progress_photo photo')
            ->join('tb_myrep_boq_progress_item progress', 'progress.id_progress_item = photo.id_progress_item', 'inner')
            ->join('tb_myrep_boq_baseline_item baseline_item', 'baseline_item.id_boq_baseline_item = progress.id_boq_baseline_item', 'left')
            ->join('md_myrep_boq_item boq_item', 'boq_item.id_boq_item = baseline_item.id_boq_item', 'left')
            ->where('photo.id_progress_photo', $photoId)
            ->get()
            ->row_array() ?: [];
    }

    public function updateProgressPhotoReviewStatus($photoId, $statusPhoto, $reviewedBy, $reviewRemark = '')
    {
        $photo = $this->getProgressPhotoById($photoId);
        if (empty($photo)) {
            return false;
        }

        $statusPhoto = strtoupper(trim((string) $statusPhoto));
        if (!in_array($statusPhoto, ['APPROVED', 'REJECTED'], true)) {
            return false;
        }

        return $this->db
            ->where('id_progress_photo', (int) $photoId)
            ->update('tb_myrep_boq_progress_photo', [
                'status_photo' => $statusPhoto,
                'review_remark' => trim((string) $reviewRemark) !== '' ? trim((string) $reviewRemark) : null,
                'reviewed_by' => (int) $reviewedBy,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'approved_at' => $statusPhoto === 'APPROVED' ? date('Y-m-d H:i:s') : null,
            ]);
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

        $this->db
            ->select('b.id_myrep_cluster, bi.id_boq_baseline_item, bi.qty_boq, bi.target_foto_required, m.item_name, m.excel_item_name, m.item_type')
            ->from('tb_myrep_boq_baseline b')
            ->join('tb_myrep_boq_baseline_item bi', 'bi.id_boq_baseline = b.id_boq_baseline', 'inner')
            ->join('md_myrep_boq_item m', 'm.id_boq_item = bi.id_boq_item', 'left')
            ->where('b.status_baseline', 'ACTIVE')
            ->where_in('b.id_myrep_cluster', $clusterIds);
        if ($this->db->field_exists('scope_type', 'tb_myrep_boq_baseline')) {
            $this->db->where('b.scope_type', 'CLUSTER');
        }

        $baselineRows = $this->db->get()->result_array();

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
                'uploaded_harian_photos' => 0,
                'uploaded_comply_photos' => 0,
                'comply_label_count' => 0,
                'entry_count' => 0,
                'last_progress_date' => null,
            ];
            $complyRule = $this->resolveComplyRuleMeta($row);
            $targetComplyPhotos = (int) $this->calculateTargetComplyPhotos((float) ($row['qty_boq'] ?? 0), $complyRule);

            $meta = &$result[$clusterId];
            $meta['total_item']++;
            $meta['target_qty_total'] += (float) ($row['qty_boq'] ?? 0);
            $meta['actual_qty_total'] += (float) ($aggregate['progress_qty'] ?? 0);
            $meta['target_photo_total'] += (int) ($row['target_foto_required'] ?? 0) + $targetComplyPhotos;
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
                array_merge($row, $complyRule, [
                    'target_comply_photo_required' => $targetComplyPhotos,
                ]),
                $aggregate
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
                'uploaded_harian_photos' => 0,
                'uploaded_comply_photos' => 0,
                'comply_label_count' => 0,
                'approved_comply_photos' => 0,
                'approved_comply_label_count' => 0,
                'entry_count' => (int) ($row['entry_count'] ?? 0),
                'last_progress_date' => $row['last_progress_date'] ?? null,
            ];
        }

        $photoRows = $this->db
            ->select("
                p.id_boq_baseline_item,
                COUNT(photo.id_progress_photo) AS uploaded_photos,
                SUM(CASE WHEN UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'HARIAN' THEN 1 ELSE 0 END) AS uploaded_harian_photos,
                SUM(CASE WHEN UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'COMPLY' THEN 1 ELSE 0 END) AS uploaded_comply_photos,
                COUNT(DISTINCT CASE WHEN UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'COMPLY' AND TRIM(COALESCE(photo.comply_label, '')) <> '' THEN photo.comply_label ELSE NULL END) AS comply_label_count,
                SUM(CASE WHEN UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'COMPLY' AND UPPER(COALESCE(photo.status_photo, 'UPLOADED')) = 'APPROVED' THEN 1 ELSE 0 END) AS approved_comply_photos,
                COUNT(DISTINCT CASE WHEN UPPER(COALESCE(photo.photo_category, 'HARIAN')) = 'COMPLY' AND UPPER(COALESCE(photo.status_photo, 'UPLOADED')) = 'APPROVED' AND TRIM(COALESCE(photo.comply_label, '')) <> '' THEN photo.comply_label ELSE NULL END) AS approved_comply_label_count
            ", false)
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
                    'uploaded_harian_photos' => 0,
                    'uploaded_comply_photos' => 0,
                    'comply_label_count' => 0,
                    'approved_comply_photos' => 0,
                    'approved_comply_label_count' => 0,
                    'entry_count' => 0,
                    'last_progress_date' => null,
                ];
            }
            $map[$baselineItemId]['uploaded_photos'] = (int) ($row['uploaded_photos'] ?? 0);
            $map[$baselineItemId]['uploaded_harian_photos'] = (int) ($row['uploaded_harian_photos'] ?? 0);
            $map[$baselineItemId]['uploaded_comply_photos'] = (int) ($row['uploaded_comply_photos'] ?? 0);
            $map[$baselineItemId]['comply_label_count'] = (int) ($row['comply_label_count'] ?? 0);
            $map[$baselineItemId]['approved_comply_photos'] = (int) ($row['approved_comply_photos'] ?? 0);
            $map[$baselineItemId]['approved_comply_label_count'] = (int) ($row['approved_comply_label_count'] ?? 0);
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

    private function resolveItemStatus($qtyBoq, $progressQty, $itemRow, $aggregate)
    {
        $qtyBoq = (float) $qtyBoq;
        $progressQty = (float) $progressQty;
        $targetDailyPhoto = (int) ($itemRow['target_foto_required'] ?? 0);
        $uploadedDailyPhotos = (int) ($aggregate['uploaded_harian_photos'] ?? 0);
        $approvedComplyPhotos = (int) ($aggregate['approved_comply_photos'] ?? 0);
        $approvedComplyLabelCount = (int) ($aggregate['approved_comply_label_count'] ?? 0);
        $complyRule = [
            'comply_enabled' => !empty($itemRow['comply_enabled']),
            'comply_photo_per_label' => (int) ($itemRow['comply_photo_per_label'] ?? 0),
            'comply_entry_limit_mode' => (string) ($itemRow['comply_entry_limit_mode'] ?? 'NONE'),
        ];

        $qtyDone = $qtyBoq > 0 && $progressQty >= $qtyBoq;
        $dailyPhotoDone = $targetDailyPhoto <= 0 || $uploadedDailyPhotos >= $targetDailyPhoto;
        $complyDone = $this->isComplySatisfied($progressQty, $complyRule, $approvedComplyPhotos, $approvedComplyLabelCount);

        if ($qtyDone && $dailyPhotoDone && $complyDone) {
            return 'DONE';
        }

        if ($progressQty > 0 || $uploadedDailyPhotos > 0 || (int) ($aggregate['uploaded_comply_photos'] ?? 0) > 0) {
            return 'ON PROGRESS';
        }

        return 'NOT STARTED';
    }

    private function resolveComplyRuleMeta($row)
    {
        $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
        $excelItemName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
        $combined = trim($itemName . ' ' . $excelItemName);

        $meta = [
            'comply_enabled' => 0,
            'comply_photo_per_label' => 0,
            'comply_entry_limit_mode' => 'NONE',
            'comply_label_prefix' => (string) ($row['item_name'] ?? 'Item'),
            'comply_label_placeholder' => 'Nama / nomor item comply',
            'comply_requirement_text' => 'Foto comply tidak diwajibkan untuk item ini.',
        ];

        if (strpos($combined, 'TIANG EKSISTING') !== false || strpos($combined, 'POLE EKSISTING') !== false) {
            return [
                'comply_enabled' => 1,
                'comply_photo_per_label' => 1,
                'comply_entry_limit_mode' => 'FLEXIBLE',
                'comply_label_prefix' => 'Pole Eksisting',
                'comply_label_placeholder' => 'Contoh: Pole Eksisting 001',
                'comply_requirement_text' => 'Setiap nomor / nama pole eksisting wajib punya 1 foto comply. Jumlah entry tidak dibatasi.',
            ];
        }

        if (strpos($combined, 'TIANG') !== false) {
            return [
                'comply_enabled' => 1,
                'comply_photo_per_label' => 1,
                'comply_entry_limit_mode' => 'MATCH_QTY',
                'comply_label_prefix' => 'New Tiang',
                'comply_label_placeholder' => 'Contoh: New Tiang 001',
                'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap tiang wajib punya 1 foto comply.',
            ];
        }

        if (strpos($combined, 'FDT') !== false) {
            return [
                'comply_enabled' => 1,
                'comply_photo_per_label' => 2,
                'comply_entry_limit_mode' => 'MATCH_QTY',
                'comply_label_prefix' => 'FDT',
                'comply_label_placeholder' => 'Contoh: FDT 001',
                'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap FDT wajib 2 foto: terbuka dan tertutup.',
            ];
        }

        if (strpos($combined, 'FAT') !== false) {
            return [
                'comply_enabled' => 1,
                'comply_photo_per_label' => 2,
                'comply_entry_limit_mode' => 'MATCH_QTY',
                'comply_label_prefix' => 'FAT',
                'comply_label_placeholder' => 'Contoh: FAT 001',
                'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap FAT wajib 2 foto: terbuka dan tertutup.',
            ];
        }

        if (strpos($combined, 'SPLITTER') !== false) {
            return [
                'comply_enabled' => 1,
                'comply_photo_per_label' => 2,
                'comply_entry_limit_mode' => 'MATCH_QTY',
                'comply_label_prefix' => 'Splitter',
                'comply_label_placeholder' => 'Contoh: Splitter 001',
                'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap splitter wajib 2 foto comply.',
            ];
        }

        return $meta;
    }

    private function calculateTargetComplyPhotos($qtyTarget, $complyRule)
    {
        if (empty($complyRule['comply_enabled'])) {
            return 0;
        }

        if (($complyRule['comply_entry_limit_mode'] ?? 'NONE') !== 'MATCH_QTY') {
            return 0;
        }

        $qtyTarget = (float) $qtyTarget;
        return (int) ceil(max($qtyTarget, 0)) * (int) ($complyRule['comply_photo_per_label'] ?? 0);
    }

    private function isComplySatisfied($progressQty, $complyRule, $uploadedComplyPhotos, $complyLabelCount)
    {
        if (empty($complyRule['comply_enabled'])) {
            return true;
        }

        $photoPerLabel = (int) ($complyRule['comply_photo_per_label'] ?? 0);
        $mode = strtoupper((string) ($complyRule['comply_entry_limit_mode'] ?? 'NONE'));
        $progressQty = (float) $progressQty;
        $uploadedComplyPhotos = (int) $uploadedComplyPhotos;
        $complyLabelCount = (int) $complyLabelCount;

        if ($progressQty <= 0) {
            return true;
        }

        if ($mode === 'MATCH_QTY') {
            $requiredLabels = (int) ceil($progressQty);
            $requiredPhotos = $requiredLabels * $photoPerLabel;
            return $complyLabelCount >= $requiredLabels && $uploadedComplyPhotos >= $requiredPhotos;
        }

        if ($mode === 'FLEXIBLE') {
            if ($complyLabelCount <= 0) {
                return false;
            }
            return $uploadedComplyPhotos >= ($complyLabelCount * max($photoPerLabel, 1));
        }

        return true;
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

    private function resolveComplyPrintSectionTitle($row)
    {
        $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
        $excelItemName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
        $combined = trim($itemName . ' ' . $excelItemName);

        if (strpos($combined, 'TIANG') !== false || strpos($combined, 'POLE') !== false) {
            return 'POLE';
        }

        if (strpos($combined, 'FDT') !== false) {
            return 'FDT';
        }

        if (strpos($combined, 'FAT') !== false) {
            return 'FAT';
        }

        if (strpos($combined, 'SPLITTER') !== false) {
            return 'SPLITTER';
        }

        return $itemName !== '' ? $itemName : 'COMPLY';
    }
}
