<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MImplementasi_BOQ_MyRep extends CI_Model
{
    public function activityTablesReady()
    {
        return $this->db->table_exists('tb_myrep_impl_daily_activity')
            && $this->db->table_exists('tb_myrep_impl_daily_activity_photo');
    }

    public function getDailyActivityDefinitions()
    {
        return [
            ['activity_code' => 'PULLING_CABLE', 'activity_name' => 'Pulling Cable', 'boq_type' => 'CABLE', 'default_unit' => 'METER'],
            ['activity_code' => 'DIGGING_HOLE', 'activity_name' => 'Digging Hole', 'boq_type' => 'TIANG', 'default_unit' => 'HOLE'],
            ['activity_code' => 'TANAM_TIANG', 'activity_name' => 'Tanam Tiang', 'boq_type' => 'TIANG', 'default_unit' => 'BATANG'],
            ['activity_code' => 'COR_FONDATION', 'activity_name' => 'Cor Fondation', 'boq_type' => 'TIANG', 'default_unit' => 'BATANG'],
            ['activity_code' => 'SLING_WIRE', 'activity_name' => 'Sling Wire', 'boq_type' => 'SLING WIRE', 'default_unit' => 'SPAN'],
            ['activity_code' => 'INSTALASI_FAT_FDT', 'activity_name' => 'Instalasi FAT / FDT', 'boq_type' => 'FAT/FDT', 'default_unit' => 'UNIT'],
            ['activity_code' => 'SPLICING_FO', 'activity_name' => 'Splicing FO', 'boq_type' => 'SPLICING', 'default_unit' => 'TITIK'],
            ['activity_code' => 'RAPIH_AKSESORIS', 'activity_name' => 'Perapihan Aksesoris', 'boq_type' => 'PERAPIHAN', 'default_unit' => 'TITIK'],
            ['activity_code' => 'RAPIH_LABEL_TIANG', 'activity_name' => 'Perapihan Label Tiang', 'boq_type' => 'PERAPIHAN', 'default_unit' => 'LABEL'],
            ['activity_code' => 'RAPIH_LABEL_KABEL', 'activity_name' => 'Perapihan Label Kabel', 'boq_type' => 'PERAPIHAN', 'default_unit' => 'LABEL'],
        ];
    }

    public function getMasterBoqItems()
    {
        if (!$this->db->table_exists('md_myrep_boq_item')) {
            return [];
        }

        $this->db
            ->select('id_boq_item, item_name, excel_item_name, item_type, sort_no')
            ->from('md_myrep_boq_item');

        if ($this->db->field_exists('is_active', 'md_myrep_boq_item')) {
            $this->db->where('is_active', 1);
        }

        return $this->db
            ->order_by('sort_no', 'ASC')
            ->order_by('item_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function createDailyActivity($clusterId, array $payload, array $photos = [])
    {
        if (!$this->activityTablesReady()) {
            return 0;
        }

        $clusterId = (int) $clusterId;
        if ($clusterId <= 0) {
            return 0;
        }

        $this->db->trans_start();
        $this->db->insert('tb_myrep_impl_daily_activity', [
            'id_myrep_cluster' => $clusterId,
            'activity_date' => (string) ($payload['activity_date'] ?? date('Y-m-d')),
            'activity_code' => (string) ($payload['activity_code'] ?? ''),
            'activity_name' => (string) ($payload['activity_name'] ?? ''),
            'activity_detail' => !empty($payload['activity_detail']) ? (string) $payload['activity_detail'] : null,
            'boq_type' => (string) ($payload['boq_type'] ?? ''),
            'scope_type' => (string) ($payload['scope_type'] ?? 'CLUSTER'),
            'qty_activity' => (float) ($payload['qty_activity'] ?? 0),
            'unit_activity' => (string) ($payload['unit_activity'] ?? ''),
            'team_count' => (int) ($payload['team_count'] ?? 0),
            'worker_count' => (int) ($payload['worker_count'] ?? 0),
            'remark_activity' => !empty($payload['remark_activity']) ? (string) $payload['remark_activity'] : null,
            'created_by' => (int) ($payload['created_by'] ?? 0),
            'updated_by' => (int) ($payload['updated_by'] ?? 0),
        ]);
        $activityId = (int) $this->db->insert_id();

        foreach ($photos as $photo) {
            $this->db->insert('tb_myrep_impl_daily_activity_photo', [
                'id_daily_activity' => $activityId,
                'file_name' => (string) ($photo['file_name'] ?? ''),
                'file_path' => (string) ($photo['file_path'] ?? ''),
                'caption' => !empty($photo['caption']) ? (string) $photo['caption'] : null,
                'uploaded_by' => (int) ($payload['created_by'] ?? 0),
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $activityId : 0;
    }

    public function applyDailyActivityToBoqProgress($clusterId, $progressDate, $activityCode, $activityDetail, $qtyActivity, $userId, $scopeType = 'CLUSTER', $trackerRemark = '')
    {
        $clusterId = (int) $clusterId;
        $userId = (int) $userId;
        $qtyActivity = (float) $qtyActivity;
        $activityCode = strtoupper(trim((string) $activityCode));
        $activityDetail = strtoupper(trim((string) $activityDetail));
        $scopeType = strtoupper(trim((string) $scopeType)) === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';

        if ($clusterId <= 0 || $userId <= 0 || $qtyActivity <= 0) {
            return 0;
        }

        $rows = $this->getBaselineCompareRows($clusterId);
        if (empty($rows)) {
            return 0;
        }

        $targetRows = [];
        $remark = trim((string) $trackerRemark);
        $allowOverBoq = false;
        if ($remark === '') {
            $remark = '[AUTO] Aktivitas Harian (' . $activityCode . ') (' . $scopeType . ')';
        }

        if ($activityCode === 'COR_FONDATION') {
            $targetRows = array_values(array_filter($rows, function ($row) use ($activityDetail) {
                $type = strtoupper(trim((string) ($row['item_type'] ?? '')));
                if ($type !== 'TIANG') {
                    return false;
                }
                if ($activityDetail === '' || $activityDetail === '-') {
                    return true;
                }
                $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
                $excelName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
                return strpos($itemName . ' ' . $excelName, $activityDetail) !== false;
            }));
            // Jika detail tiang yang diinput tidak ada di baseline (contoh: BOQ hanya Tiang 7,
            // implementasi input Tiang 9), fallback ke seluruh bucket TIANG agar tetap tercatat
            // sebagai progress TIANG dan bisa over BOQ sesuai kebutuhan lapangan.
            if (empty($targetRows)) {
                $targetRows = array_values(array_filter($rows, static function ($row) {
                    return strtoupper(trim((string) ($row['item_type'] ?? ''))) === 'TIANG';
                }));
            }
            $allowOverBoq = true;
            if (trim((string) $trackerRemark) === '') {
                $remark = '[AUTO] Aktivitas Cor Fondation (' . $scopeType . ')';
            }
        } elseif ($activityCode === 'PULLING_CABLE') {
            $targetRows = array_values(array_filter($rows, function ($row) use ($activityDetail) {
                $type = strtoupper(trim((string) ($row['item_type'] ?? '')));
                if ($type !== 'CABLE') {
                    return false;
                }
                if ($activityDetail === '' || $activityDetail === '-') {
                    return true;
                }
                $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
                $excelName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
                return strpos($itemName . ' ' . $excelName, $activityDetail) !== false;
            }));
            if (trim((string) $trackerRemark) === '') {
                $remark = '[AUTO] Aktivitas Pulling Cable (' . $scopeType . ')';
            }
        } elseif ($activityCode === 'INSTALASI_FAT_FDT') {
            $targetRows = array_values(array_filter($rows, function ($row) use ($activityDetail) {
                $type = strtoupper(trim((string) ($row['item_type'] ?? '')));
                if (!in_array($type, ['FAT', 'FDT'], true)) {
                    return false;
                }
                if ($activityDetail === '' || $activityDetail === '-') {
                    return true;
                }
                $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
                $excelName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
                return strpos($itemName . ' ' . $excelName, $activityDetail) !== false;
            }));
            if (trim((string) $trackerRemark) === '') {
                $remark = '[AUTO] Aktivitas Instalasi FAT/FDT (' . $scopeType . ')';
            }
        } elseif ($activityCode === 'SLING_WIRE') {
            $targetRows = array_values(array_filter($rows, static function ($row) {
                return strtoupper(trim((string) ($row['item_type'] ?? ''))) === 'SLING WIRE';
            }));
            if (trim((string) $trackerRemark) === '') {
                $remark = '[AUTO] Aktivitas Sling Wire (' . $scopeType . ')';
            }
        } elseif ($activityCode === 'SPLICING_FO') {
            $targetRows = array_values(array_filter($rows, function ($row) use ($activityDetail) {
                $type = strtoupper(trim((string) ($row['item_type'] ?? '')));
                if ($type !== 'SPLICING') {
                    return false;
                }
                if ($activityDetail === '' || $activityDetail === '-') {
                    return true;
                }
                $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
                $excelName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
                return strpos($itemName . ' ' . $excelName, $activityDetail) !== false;
            }));
            if (trim((string) $trackerRemark) === '') {
                $remark = '[AUTO] Aktivitas Splicing FO (' . $scopeType . ')';
            }
        }

        if (empty($targetRows)) {
            return 0;
        }

        $remainingToAllocate = $qtyActivity;
        $allocatedTotal = 0;
        $firstTargetBaselineItemId = (int) (($targetRows[0]['id_boq_baseline_item'] ?? 0));

        foreach ($targetRows as $targetRow) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $baselineItemId = (int) ($targetRow['id_boq_baseline_item'] ?? 0);
            $remainingQty = (float) ($targetRow['remaining_qty'] ?? 0);
            if ($baselineItemId <= 0) {
                continue;
            }
            if (!$allowOverBoq && $remainingQty <= 0) {
                continue;
            }

            $qtyToInsert = $allowOverBoq
                ? ($remainingQty > 0 ? min($remainingToAllocate, $remainingQty) : 0)
                : min($remainingToAllocate, $remainingQty);
            if ($qtyToInsert <= 0) {
                continue;
            }

            $created = $this->createProgressEntry($clusterId, $baselineItemId, [
                'progress_date' => (string) $progressDate,
                'qty_progress' => $qtyToInsert,
                'status_progress' => 'ON PROGRESS',
                'remark_progress' => $remark,
                'created_by' => $userId,
                'updated_by' => $userId,
            ], []);

            if ($created > 0) {
                $allocatedTotal += $qtyToInsert;
                $remainingToAllocate -= $qtyToInsert;
            }
        }

        // Khusus COR FONDATION: jika qty implementasi masih tersisa walau plan BOQ sudah habis,
        // tetap catat ke BOQ tracker sebagai over-BOQ.
        if ($allowOverBoq && $remainingToAllocate > 0 && $firstTargetBaselineItemId > 0) {
            $created = $this->createProgressEntry($clusterId, $firstTargetBaselineItemId, [
                'progress_date' => (string) $progressDate,
                'qty_progress' => $remainingToAllocate,
                'status_progress' => 'ON PROGRESS',
                'remark_progress' => $remark . ' [OVER BOQ]',
                'created_by' => $userId,
                'updated_by' => $userId,
            ], []);
            if ($created > 0) {
                $allocatedTotal += $remainingToAllocate;
                $remainingToAllocate = 0;
            }
        }

        return $allocatedTotal;
    }

    public function getDailyActivities($clusterId)
    {
        if (!$this->activityTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('a.*, u.nama_karyawan AS nama_user')
            ->from('tb_myrep_impl_daily_activity a')
            ->join('tb_master_user_new u', 'u.id = a.created_by', 'left')
            ->where('a.id_myrep_cluster', (int) $clusterId)
            ->order_by('a.activity_date', 'DESC')
            ->order_by('a.id_daily_activity', 'DESC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return [];
        }

        $activityIds = array_column($rows, 'id_daily_activity');
        $photoRows = $this->db
            ->from('tb_myrep_impl_daily_activity_photo')
            ->where_in('id_daily_activity', $activityIds)
            ->order_by('id_activity_photo', 'ASC')
            ->get()
            ->result_array();

        $photoMap = [];
        foreach ($photoRows as $photoRow) {
            $photoMap[(int) ($photoRow['id_daily_activity'] ?? 0)][] = $photoRow;
        }

        foreach ($rows as &$row) {
            $row['photos'] = $photoMap[(int) ($row['id_daily_activity'] ?? 0)] ?? [];
        }
        unset($row);

        return $rows;
    }

    public function deleteDailyActivity($clusterId, $dailyActivityId)
    {
        if (!$this->activityTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $dailyActivityId = (int) $dailyActivityId;
        if ($clusterId <= 0 || $dailyActivityId <= 0) {
            return false;
        }

        $activityRow = $this->db
            ->from('tb_myrep_impl_daily_activity')
            ->where('id_daily_activity', $dailyActivityId)
            ->where('id_myrep_cluster', $clusterId)
            ->get()
            ->row_array();

        if (empty($activityRow)) {
            return false;
        }

        $photoRows = $this->db
            ->from('tb_myrep_impl_daily_activity_photo')
            ->where('id_daily_activity', $dailyActivityId)
            ->get()
            ->result_array();

        $this->db->trans_start();
        $this->db->where('id_daily_activity', $dailyActivityId)->delete('tb_myrep_impl_daily_activity_photo');
        $this->db->where('id_daily_activity', $dailyActivityId)->where('id_myrep_cluster', $clusterId)->delete('tb_myrep_impl_daily_activity');
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return false;
        }

        foreach ($photoRows as $photoRow) {
            $this->deletePhysicalFile((string) ($photoRow['file_path'] ?? ''));
        }

        return true;
    }

    public function deleteDailyActivitiesByDate($clusterId, $activityDate)
    {
        if (!$this->activityTablesReady()) {
            return false;
        }

        $clusterId = (int) $clusterId;
        $activityDate = trim((string) $activityDate);
        if ($clusterId <= 0 || $activityDate === '') {
            return false;
        }

        $activityRows = $this->db
            ->select('id_daily_activity')
            ->from('tb_myrep_impl_daily_activity')
            ->where('id_myrep_cluster', $clusterId)
            ->where('activity_date', $activityDate)
            ->get()
            ->result_array();

        if (empty($activityRows)) {
            return false;
        }

        $dailyIds = array_map('intval', array_column($activityRows, 'id_daily_activity'));
        $photoRows = $this->db
            ->from('tb_myrep_impl_daily_activity_photo')
            ->where_in('id_daily_activity', $dailyIds)
            ->get()
            ->result_array();

        $autoProgressRows = [];
        $autoProgressPhotoRows = [];
        if ($this->db->table_exists('tb_myrep_boq_progress_item')) {
            $autoProgressRows = $this->db
                ->select('id_progress_item')
                ->from('tb_myrep_boq_progress_item')
                ->where('id_myrep_cluster', $clusterId)
                ->where('progress_date', $activityDate)
                ->like('remark_progress', '[AUTO] Aktivitas', 'after')
                ->get()
                ->result_array();

            $autoProgressIds = array_values(array_filter(array_map('intval', array_column($autoProgressRows, 'id_progress_item'))));
            if (!empty($autoProgressIds) && $this->db->table_exists('tb_myrep_boq_progress_photo')) {
                $autoProgressPhotoRows = $this->db
                    ->from('tb_myrep_boq_progress_photo')
                    ->where_in('id_progress_item', $autoProgressIds)
                    ->get()
                    ->result_array();
            }
        }

        $this->db->trans_start();
        $this->db->where_in('id_daily_activity', $dailyIds)->delete('tb_myrep_impl_daily_activity_photo');
        $this->db->where('id_myrep_cluster', $clusterId)->where('activity_date', $activityDate)->delete('tb_myrep_impl_daily_activity');
        if (!empty($autoProgressRows) && $this->db->table_exists('tb_myrep_boq_progress_item')) {
            $autoProgressIds = array_values(array_filter(array_map('intval', array_column($autoProgressRows, 'id_progress_item'))));
            if (!empty($autoProgressIds)) {
                if ($this->db->table_exists('tb_myrep_boq_progress_photo')) {
                    $this->db->where_in('id_progress_item', $autoProgressIds)->delete('tb_myrep_boq_progress_photo');
                }
                $this->db->where('id_myrep_cluster', $clusterId)->where_in('id_progress_item', $autoProgressIds)->delete('tb_myrep_boq_progress_item');
            }
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return false;
        }

        foreach ($photoRows as $photoRow) {
            $this->deletePhysicalFile((string) ($photoRow['file_path'] ?? ''));
        }
        foreach ($autoProgressPhotoRows as $photoRow) {
            $this->deletePhysicalFile((string) ($photoRow['file_path'] ?? ''));
        }

        return true;
    }

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
            ->select('c.id_myrep_cluster, c.city_name')
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

        $eligibilityMap = $this->getFullUploadEligibilityMap(array_column($rows, 'id_myrep_cluster'));
        $resultMap = [];
        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if (empty($eligibilityMap[$clusterId])) {
                continue;
            }

            $city = strtoupper(trim((string) ($row['city_name'] ?? '')));
            if ($city !== '') {
                $resultMap[$city] = true;
            }
        }

        return array_keys($resultMap);
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

        $eligibilityMap = $this->getFullUploadEligibilityMap(array_column($rows, 'id_myrep_cluster'));
        $metaMap = $this->getClusterProgressMetaMap(array_column($rows, 'id_myrep_cluster'));
        $filtered = [];

        foreach ($rows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            if (empty($eligibilityMap[$clusterId])) {
                continue;
            }

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

        $eligibilityMap = $this->getFullUploadEligibilityMap([(int) $clusterId]);
        if (empty($eligibilityMap[(int) $clusterId])) {
            return [];
        }

        return array_merge($row, $this->getClusterProgressMetaMap([(int) $clusterId])[(int) $clusterId] ?? $this->buildEmptyClusterMeta());
    }

    private function getFullUploadEligibilityMap($clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }

        $requiredFlowTypes = ['DRM', 'DRM_SUBFEEDER'];
        $statusMap = [];

        foreach ($requiredFlowTypes as $flowType) {
            $requiredRows = $this->db
                ->select('COUNT(i.id_doc_item) AS total_doc', false)
                ->from('md_myrep_flow_doc_group g')
                ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
                ->where('g.flow_type', $flowType)
                ->where('g.is_active', 1)
                ->get()
                ->row_array();

            $totalDoc = (int) ($requiredRows['total_doc'] ?? 0);
            if ($totalDoc <= 0) {
                foreach ($clusterIds as $clusterId) {
                    if (!isset($statusMap[$clusterId])) {
                        $statusMap[$clusterId] = [];
                    }
                    $statusMap[$clusterId][$flowType] = false;
                }
                continue;
            }

            $uploadedRows = $this->db
                ->select('p.id_myrep_cluster, SUM(CASE WHEN f.id_doc_file IS NOT NULL THEN 1 ELSE 0 END) AS uploaded_doc', false)
                ->from('tb_myrep_flow_doc_package p')
                ->join('md_myrep_flow_doc_group g', 'g.id_doc_group = p.id_doc_group AND g.flow_type = ' . $this->db->escape($flowType) . ' AND g.is_active = 1', 'inner', false)
                ->join('md_myrep_flow_doc_item i', 'i.id_doc_group = g.id_doc_group AND i.is_active = 1', 'inner')
                ->join('tb_myrep_flow_doc_file f', 'f.id_doc_package = p.id_doc_package AND f.id_doc_item = i.id_doc_item', 'left')
                ->where('p.flow_type', $flowType)
                ->where_in('p.id_myrep_cluster', $clusterIds)
                ->group_by('p.id_myrep_cluster')
                ->get()
                ->result_array();

            $uploadedMap = [];
            foreach ($uploadedRows as $uploadedRow) {
                $uploadedMap[(int) ($uploadedRow['id_myrep_cluster'] ?? 0)] = (int) ($uploadedRow['uploaded_doc'] ?? 0);
            }

            foreach ($clusterIds as $clusterId) {
                if (!isset($statusMap[$clusterId])) {
                    $statusMap[$clusterId] = [];
                }

                $statusMap[$clusterId][$flowType] = ((int) ($uploadedMap[$clusterId] ?? 0) >= $totalDoc);
            }
        }

        $eligibilityMap = [];
        foreach ($clusterIds as $clusterId) {
            $eligibilityMap[$clusterId] = !empty($statusMap[$clusterId]['DRM']) && !empty($statusMap[$clusterId]['DRM_SUBFEEDER']);
        }

        return $eligibilityMap;
    }

    public function getBaselineCompareRows($clusterId)
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $this->db
            ->select('b.id_myrep_cluster, bi.id_boq_baseline_item, bi.id_boq_baseline, bi.id_boq_item, bi.qty_boq, bi.jumlah_foto, bi.remarks_rule, bi.target_foto_required, bi.item_note, m.excel_item_name, m.item_name, m.item_type, m.photo_type, m.sort_no')
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

        $scopeQtyMap = $this->getBoqScopeQtyMapByItem((int) $clusterId);
        $baselineItemIds = array_column($rows, 'id_boq_baseline_item');
        $progressMap = $this->getProgressAggregateMap($baselineItemIds);
        $tiangAdjustedProgressMap = $this->buildTiangAdjustedProgressMap($rows, $progressMap, [$clusterId]);

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
            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            $progressQty = array_key_exists($baselineItemId, $tiangAdjustedProgressMap)
                ? (float) $tiangAdjustedProgressMap[$baselineItemId]
                : (float) ($aggregate['progress_qty'] ?? 0);
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
            $boqItemId = (int) ($row['id_boq_item'] ?? 0);
            $row['qty_cluster'] = (float) (($scopeQtyMap[$boqItemId]['CLUSTER'] ?? 0));
            $row['qty_subfeeder'] = (float) (($scopeQtyMap[$boqItemId]['SUBFEEDER'] ?? 0));
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
            ->select('p.id_progress_item, p.id_boq_baseline_item, p.progress_date, p.qty_progress, p.status_progress, p.remark_progress, p.created_at, u.nama_karyawan AS nama_user')
            ->from('tb_myrep_boq_progress_item p')
            ->join('tb_master_user_new u', 'u.id = p.created_by', 'left')
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
        $tiangAdjustedProgressMap = $this->buildTiangAdjustedProgressMap($baselineRows, $progressMap, $clusterIds);

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
            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            $progressQty = array_key_exists($baselineItemId, $tiangAdjustedProgressMap)
                ? (float) $tiangAdjustedProgressMap[$baselineItemId]
                : (float) ($aggregate['progress_qty'] ?? 0);

            $meta = &$result[$clusterId];
            $meta['total_item']++;
            $meta['target_qty_total'] += (float) ($row['qty_boq'] ?? 0);
            $meta['actual_qty_total'] += $progressQty;
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
                $progressQty,
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
            if (strpos($combined, 'OPM FDT') !== false) {
                return [
                    'comply_enabled' => 1,
                    'comply_photo_per_label' => 9,
                    'comply_entry_limit_mode' => 'MATCH_QTY',
                    'comply_label_prefix' => 'OPM FDT',
                    'comply_label_placeholder' => 'Contoh: OPM FDT 001',
                    'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap OPM FDT wajib 9 foto dengan remark masing-masing foto.',
                ];
            }

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
            if (strpos($combined, 'OPM FAT') !== false) {
                return [
                    'comply_enabled' => 1,
                    'comply_photo_per_label' => 8,
                    'comply_entry_limit_mode' => 'MATCH_QTY',
                    'comply_label_prefix' => 'OPM FAT',
                    'comply_label_placeholder' => 'Contoh: OPM FAT 001',
                    'comply_requirement_text' => 'Jumlah entry comply mengikuti qty implementasi. Setiap OPM FAT wajib 8 foto dengan remark masing-masing foto.',
                ];
            }

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

    private function buildTiangAdjustedProgressMap(array $baselineRows, array $progressMap, array $clusterIds = [])
    {
        if (empty($baselineRows)) {
            return [];
        }

        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            $clusterIds = array_values(array_unique(array_map(static function ($row) {
                return (int) ($row['id_myrep_cluster'] ?? 0);
            }, $baselineRows)));
        }

        $corQtyByCluster = $this->getCorFondationQtyByCluster($clusterIds);
        $tiangRawTotalByCluster = [];

        foreach ($baselineRows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $itemType = strtoupper(trim((string) ($row['item_type'] ?? '')));
            if ($clusterId <= 0 || $itemType !== 'TIANG') {
                continue;
            }

            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            $rawProgress = (float) (($progressMap[$baselineItemId]['progress_qty'] ?? 0));
            if (!isset($tiangRawTotalByCluster[$clusterId])) {
                $tiangRawTotalByCluster[$clusterId] = 0;
            }
            $tiangRawTotalByCluster[$clusterId] += max($rawProgress, 0);
        }

        $ratioByCluster = [];
        foreach ($tiangRawTotalByCluster as $clusterId => $rawTotal) {
            $corQty = (float) ($corQtyByCluster[$clusterId] ?? 0);
            if ($rawTotal <= 0) {
                $ratioByCluster[$clusterId] = 0;
                continue;
            }
            $effectiveTotal = min($rawTotal, max($corQty, 0));
            $ratioByCluster[$clusterId] = $effectiveTotal / $rawTotal;
        }

        $adjusted = [];
        foreach ($baselineRows as $row) {
            $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
            $itemType = strtoupper(trim((string) ($row['item_type'] ?? '')));
            if ($clusterId <= 0 || $itemType !== 'TIANG') {
                continue;
            }

            $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
            $qtyBoq = (float) ($row['qty_boq'] ?? 0);
            $rawProgress = (float) (($progressMap[$baselineItemId]['progress_qty'] ?? 0));
            $ratio = (float) ($ratioByCluster[$clusterId] ?? 0);
            $effective = max($rawProgress * $ratio, 0);
            $adjusted[$baselineItemId] = min($effective, max($qtyBoq, 0));
        }

        return $adjusted;
    }

    private function getCorFondationQtyByCluster(array $clusterIds)
    {
        $clusterIds = array_values(array_filter(array_map('intval', (array) $clusterIds)));
        if (empty($clusterIds)) {
            return [];
        }
        if (!$this->activityTablesReady()) {
            return [];
        }

        $rows = $this->db
            ->select('id_myrep_cluster, COALESCE(SUM(qty_activity), 0) AS cor_qty', false)
            ->from('tb_myrep_impl_daily_activity')
            ->where_in('id_myrep_cluster', $clusterIds)
            ->where('activity_code', 'COR_FONDATION')
            ->group_by('id_myrep_cluster')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['id_myrep_cluster'] ?? 0)] = (float) ($row['cor_qty'] ?? 0);
        }
        return $map;
    }

    private function getBoqScopeQtyMapByItem($clusterId)
    {
        $clusterId = (int) $clusterId;
        if ($clusterId <= 0 || !$this->db->table_exists('tb_myrep_drm_boq') || !$this->db->table_exists('tb_myrep_drm_boq_item')) {
            return [];
        }

        $this->db
            ->select('i.id_boq_item, h.scope_type, COALESCE(i.qty_boq, 0) AS qty_boq', false)
            ->from('tb_myrep_drm_boq h')
            ->join('tb_myrep_drm_boq_item i', 'i.id_drm_boq = h.id_drm_boq', 'inner')
            ->where('h.id_myrep_cluster', $clusterId)
            ->where("UPPER(COALESCE(h.review_status, '')) = 'APPROVED'", null, false);

        if ($this->db->field_exists('scope_type', 'tb_myrep_drm_boq')) {
            $this->db->where_in('h.scope_type', ['CLUSTER', 'SUBFEEDER']);
        }

        $rows = $this->db->get()->result_array();
        $map = [];
        foreach ($rows as $row) {
            $boqItemId = (int) ($row['id_boq_item'] ?? 0);
            $scope = strtoupper(trim((string) ($row['scope_type'] ?? 'CLUSTER')));
            if ($boqItemId <= 0 || !in_array($scope, ['CLUSTER', 'SUBFEEDER'], true)) {
                continue;
            }
            if (!isset($map[$boqItemId])) {
                $map[$boqItemId] = ['CLUSTER' => 0, 'SUBFEEDER' => 0];
            }
            $map[$boqItemId][$scope] += (float) ($row['qty_boq'] ?? 0);
        }

        return $map;
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
        $complyLabel = strtoupper(trim((string) ($row['comply_label'] ?? '')));
        $caption = strtoupper(trim((string) ($row['caption'] ?? '')));
        $labelCombined = trim($complyLabel . ' ' . $caption);
        $masterCombined = trim($itemName . ' ' . $excelItemName);

        // Prioritas 1: tentukan kategori dari label/description foto.
        if (strpos($labelCombined, 'FDT') !== false) {
            return 'FDT';
        }
        if (strpos($labelCombined, 'FAT') !== false) {
            return 'FAT';
        }
        if (
            strpos($labelCombined, 'TIANG') !== false
            || strpos($labelCombined, 'POLE') !== false
            || preg_match('/\bT\d+\b/', $labelCombined)
        ) {
            return 'TIANG';
        }

        // Prioritas 2 (fallback): pakai item master jika label tidak memberi sinyal.
        if (strpos($masterCombined, 'FDT') !== false) {
            return 'FDT';
        }

        if (strpos($masterCombined, 'FAT') !== false) {
            return 'FAT';
        }

        if (strpos($masterCombined, 'TIANG') !== false || strpos($masterCombined, 'POLE') !== false) {
            return 'TIANG';
        }

        if (strpos($labelCombined, 'SPLITTER') !== false || strpos($masterCombined, 'SPLITTER') !== false) {
            return 'SPLITTER';
        }

        if ($complyLabel !== '') {
            return $complyLabel;
        }

        return $itemName !== '' ? $itemName : 'COMPLY';
    }
}


