<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Implementasi_BOQ_MyRep', 'TAMBAH') : true;
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Implementasi_BOQ_MyRep', 'HAPUS') : true;
$canApprovalDailyAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Implementasi_BOQ_MyRep', 'APPROVAL_DAILY') : true;
$canApprovalComplyAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Implementasi_BOQ_MyRep', 'APPROVAL_FOTO_COMPLY') : true;
$canSavePhotoRotation = $canTambah;
$currentUserId = (int) $this->session->userdata('id_user');
$implLazyPhotoPlaceholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="160" height="110" viewBox="0 0 160 110"%3E%3Crect width="160" height="110" fill="%23eef2f7"/%3E%3Cpath d="M30 78l26-28 19 19 14-15 41 24H30z" fill="%23cbd5e1"/%3E%3Ccircle cx="112" cy="35" r="12" fill="%23dbe3ef"/%3E%3C/svg%3E';

if (!function_exists('implHistoryNumber')) {
    function implHistoryNumber($value, $zeroAsDash = true)
    {
        $number = (float) $value;
        if ($zeroAsDash && abs($number) < 0.00001) {
            return '-';
        }

        return number_format($number, 0, ',', '.');
    }
}

if (!function_exists('implProgressPhotoPreviewUrl')) {
    function implProgressPhotoPreviewUrl($photoId, $size = 'thumb', $filePath = '')
    {
        $url = base_url('Implementasi_BOQ_MyRep/progressPhotoPreview/' . (int) $photoId . '/' . rawurlencode((string) $size));
        $relativePath = ltrim(str_replace('\\', '/', (string) $filePath), '/');
        if ($relativePath !== '' && strpos($relativePath, '..') === false) {
            $fullPath = FCPATH . $relativePath;
            if (is_file($fullPath)) {
                $url .= '?v=' . rawurlencode((string) filemtime($fullPath));
            }
        }
        return $url;
    }
}

if (!function_exists('implPhotoMimeFromPath')) {
    function implPhotoMimeFromPath($filePath)
    {
        $extension = strtolower(pathinfo((string) $filePath, PATHINFO_EXTENSION));
        if ($extension === 'png') {
            return 'image/png';
        }
        if ($extension === 'webp') {
            return 'image/webp';
        }
        return 'image/jpeg';
    }
}

if (!function_exists('implAddWorkingDays')) {
    function implAddWorkingDays($dateString, $workingDays)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $date = new DateTime($dateString);
        } catch (Exception $e) {
            return null;
        }

        $added = 0;
        while ($added < (int) $workingDays) {
            $date->modify('+1 day');
            $dayOfWeek = (int) $date->format('N');
            if ($dayOfWeek < 6) {
                $added++;
            }
        }

        return $date;
    }
}

if (!function_exists('implCountWorkingDays')) {
    function implCountWorkingDays($startDateString, $endDateString = 'today')
    {
        if (empty($startDateString)) {
            return 0;
        }

        try {
            $start = new DateTime($startDateString);
            $end = new DateTime($endDateString);
        } catch (Exception $e) {
            return 0;
        }

        if ($start > $end) {
            return 0;
        }

        $workingDays = 0;
        while ($start <= $end) {
            $dayOfWeek = (int) $start->format('N');
            if ($dayOfWeek < 6) {
                $workingDays++;
            }
            $start->modify('+1 day');
        }

        return $workingDays;
    }
}

if (!function_exists('implDetectHistoryScope')) {
    function implDetectHistoryScope($scopeValue, $remarkValue = '')
    {
        $scope = strtoupper(trim((string) $scopeValue));
        if ($scope === 'CLUSTER' || $scope === 'SUBFEEDER') {
            return $scope;
        }

        $remark = strtoupper(trim((string) $remarkValue));
        if (strpos($remark, 'SUBFEEDER') !== false) {
            return 'SUBFEEDER';
        }
        if (strpos($remark, 'CLUSTER') !== false) {
            return 'CLUSTER';
        }

        return 'CLUSTER';
    }
}

if (!function_exists('implBuildHistoryRowsByScope')) {
    function implBuildHistoryRowsByScope($historyDateRowsByScope, $historyTypeOrder, $nonBoqLabelOrder, $initialDate)
    {
        if (!is_array($historyDateRowsByScope)) {
            $historyDateRowsByScope = [];
        }
        ksort($historyDateRowsByScope);

        $historyRows = [];
        $historyRunningAchieve = array_fill_keys((array) $historyTypeOrder, 0);
        $historyFinalAchieve = array_fill_keys((array) $historyTypeOrder, 0);

        if (!empty($historyTypeOrder)) {
            $historyRows[] = [
                'progress_date' => $initialDate,
                'remark' => 'BOQ Awal',
                'achieve' => array_fill_keys($historyTypeOrder, 0),
            ];
        }

        foreach ($historyDateRowsByScope as $progressDate => $entry) {
            $dateAchieveTotal = 0.0;
            foreach ($historyTypeOrder as $itemType) {
                $dailyAchieve = (float) ($entry['achieve'][$itemType] ?? 0);
                $historyRunningAchieve[$itemType] += $dailyAchieve;
                $historyFinalAchieve[$itemType] = $historyRunningAchieve[$itemType];
                $dateAchieveTotal += abs($dailyAchieve);
            }

            $remarkPool = array_values(array_filter(array_unique((array) ($entry['remark'] ?? [])), static function ($text) {
                return trim((string) $text) !== '';
            }));
            $manualRemarkPool = array_values(array_filter($remarkPool, static function ($text) {
                $upper = strtoupper(trim((string) $text));
                return strpos($upper, '[AUTO]') !== 0
                    && strpos($upper, '[DAILY]') !== 0
                    && strpos($upper, 'UPLOAD FOTO COMPLY -') !== 0;
            }));
            $dailyRemarkPool = array_values(array_filter(array_unique((array) ($entry['daily_progress_remarks'] ?? [])), static function ($text) {
                return trim((string) $text) !== '';
            }));
            $dailyNonBoqRemark = '';
            if (!empty($entry['daily_non_boq_labels']) && is_array($entry['daily_non_boq_labels'])) {
                $labels = array_keys($entry['daily_non_boq_labels']);
                usort($labels, static function ($a, $b) use ($nonBoqLabelOrder) {
                    $indexA = array_search($a, $nonBoqLabelOrder, true);
                    $indexB = array_search($b, $nonBoqLabelOrder, true);
                    $indexA = $indexA === false ? 999 : $indexA;
                    $indexB = $indexB === false ? 999 : $indexB;
                    return $indexA <=> $indexB;
                });
                $dailyNonBoqRemark = implode(' / ', $labels);
            }

            if (
                $dateAchieveTotal < 0.00001
                && empty($dailyRemarkPool)
                && empty($manualRemarkPool)
                && $dailyNonBoqRemark === ''
            ) {
                continue;
            }

            $finalRemark = !empty($dailyRemarkPool)
                ? implode(' | ', $dailyRemarkPool)
                : (!empty($manualRemarkPool)
                ? implode(' | ', $manualRemarkPool)
                : ($dailyNonBoqRemark !== '' ? $dailyNonBoqRemark
                : 'Progress Harian'));

            $historyRows[] = [
                'progress_date' => $progressDate,
                'remark' => $finalRemark,
                'achieve' => $entry['achieve'],
            ];
        }

        return [
            'rows' => $historyRows,
            'final_achieve' => $historyFinalAchieve,
        ];
    }
}

if (!function_exists('implBuildTiangTanamBreakdownMap')) {
    function implBuildTiangTanamBreakdownMap($breakdownRows, $dailyActivities)
    {
        $rows = array_values((array) $breakdownRows);
        $map = [];
        if (empty($rows)) {
            return $map;
        }

        foreach ($rows as $index => $row) {
            $map[$index] = 0.0;
        }

        $unmatchedQty = 0.0;
        foreach ((array) $dailyActivities as $activity) {
            $code = strtoupper(trim((string) ($activity['activity_code'] ?? '')));
            if ($code !== 'TANAM_TIANG') {
                continue;
            }

            $qty = (float) ($activity['qty_activity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $detail = strtoupper(trim((string) ($activity['activity_detail'] ?? '')));
            if ($detail === '' || $detail === '-') {
                $unmatchedQty += $qty;
                continue;
            }

            $matchedIndexes = [];
            foreach ($rows as $rowIndex => $row) {
                $itemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
                $excelName = strtoupper(trim((string) ($row['excel_item_name'] ?? '')));
                $haystack = trim($itemName . ' ' . $excelName);
                if ($haystack === '') {
                    continue;
                }

                if (strpos($haystack, $detail) !== false || strpos($detail, $itemName) !== false || strpos($detail, $excelName) !== false) {
                    $matchedIndexes[] = $rowIndex;
                }
            }

            if (!empty($matchedIndexes)) {
                $firstMatch = (int) $matchedIndexes[0];
                $map[$firstMatch] += $qty;
            } else {
                $unmatchedQty += $qty;
            }
        }

        if ($unmatchedQty > 0) {
            $totalPlan = 0.0;
            foreach ($rows as $row) {
                $totalPlan += max((float) ($row['qty_plan'] ?? 0), 0);
            }

            if ($totalPlan > 0) {
                $distributed = 0.0;
                $lastIndex = count($rows) - 1;
                foreach ($rows as $rowIndex => $row) {
                    $plan = max((float) ($row['qty_plan'] ?? 0), 0);
                    if ($plan <= 0) {
                        continue;
                    }

                    if ($rowIndex === $lastIndex) {
                        $allocation = max($unmatchedQty - $distributed, 0);
                    } else {
                        $allocation = ($plan / $totalPlan) * $unmatchedQty;
                        $distributed += $allocation;
                    }
                    $map[$rowIndex] += $allocation;
                }
            } else {
                $map[0] += $unmatchedQty;
            }
        }

        return $map;
    }
}

if (!function_exists('implIsPoleExtComplyPhoto')) {
    function implIsPoleExtComplyPhoto($photo)
    {
        $label = strtoupper(trim((string) ($photo['comply_label'] ?? '')));
        $caption = strtoupper(trim((string) ($photo['caption'] ?? '')));

        return strpos($label, 'POLE EXT') === 0 || strpos($caption, 'POLE EXT') === 0;
    }
}

$historyTypePlan = [];
$historyTypePlanCluster = [];
$historyTypePlanSubfeeder = [];
$historyDateRows = [];
$historyDateRowsCluster = [];
$historyDateRowsSubfeeder = [];
$historyTypeOrder = [];
$boqTypeBreakdown = [];
$dailyActivitiesByDate = [];
$nonBoqLabelOrder = ['DIGGING HOLE', 'TANAM TIANG', 'PERAPIHAN'];

foreach ((array) $dailyActivities as $dailyActivity) {
    $dailyDateKey = (string) ($dailyActivity['activity_date'] ?? '');
    if ($dailyDateKey === '') {
        continue;
    }

    if (!isset($dailyActivitiesByDate[$dailyDateKey])) {
        $dailyActivitiesByDate[$dailyDateKey] = [];
    }
    $dailyActivitiesByDate[$dailyDateKey][] = $dailyActivity;

    $dailyCode = strtoupper(trim((string) ($dailyActivity['activity_code'] ?? '')));
    $dailyActivityRemark = trim((string) ($dailyActivity['activity_detail'] ?? ''));
    if ($dailyActivityRemark === '' || $dailyActivityRemark === '-') {
        $dailyActivityRemark = trim((string) ($dailyActivity['activity_name'] ?? ''));
    }
    if ($dailyActivityRemark === '' || $dailyActivityRemark === '-') {
        $dailyActivityRemark = str_replace('_', ' ', $dailyCode);
    }
    $dailyActivityRemark = strtoupper($dailyActivityRemark);

    $label = '';
    if ($dailyCode === 'DIGGING_HOLE') {
        $label = 'DIGGING HOLE';
    } elseif ($dailyCode === 'TANAM_TIANG') {
        $label = 'TANAM TIANG';
    } elseif (strpos($dailyCode, 'RAPIH_') === 0) {
        $label = 'PERAPIHAN';
    }

    if ($label !== '') {
        $dailyScope = implDetectHistoryScope($dailyActivity['scope_type'] ?? '', '');
        if ($dailyScope === 'SUBFEEDER') {
            if (!isset($historyDateRowsSubfeeder[$dailyDateKey])) {
                $historyDateRowsSubfeeder[$dailyDateKey] = [
                    'progress_date' => $dailyDateKey,
                    'remark' => [],
                    'achieve' => [],
                    'daily_non_boq_labels' => [],
                    'daily_progress_remarks' => [],
                ];
            }
            if (!isset($historyDateRowsSubfeeder[$dailyDateKey]['daily_non_boq_labels'])) {
                $historyDateRowsSubfeeder[$dailyDateKey]['daily_non_boq_labels'] = [];
            }
            if (!isset($historyDateRowsSubfeeder[$dailyDateKey]['daily_progress_remarks'])) {
                $historyDateRowsSubfeeder[$dailyDateKey]['daily_progress_remarks'] = [];
            }
            $historyDateRowsSubfeeder[$dailyDateKey]['daily_non_boq_labels'][$label] = true;
            $historyDateRowsSubfeeder[$dailyDateKey]['daily_progress_remarks'][] = $dailyActivityRemark;
        } else {
            if (!isset($historyDateRowsCluster[$dailyDateKey])) {
                $historyDateRowsCluster[$dailyDateKey] = [
                    'progress_date' => $dailyDateKey,
                    'remark' => [],
                    'achieve' => [],
                    'daily_non_boq_labels' => [],
                    'daily_progress_remarks' => [],
                ];
            }
            if (!isset($historyDateRowsCluster[$dailyDateKey]['daily_non_boq_labels'])) {
                $historyDateRowsCluster[$dailyDateKey]['daily_non_boq_labels'] = [];
            }
            if (!isset($historyDateRowsCluster[$dailyDateKey]['daily_progress_remarks'])) {
                $historyDateRowsCluster[$dailyDateKey]['daily_progress_remarks'] = [];
            }
            $historyDateRowsCluster[$dailyDateKey]['daily_non_boq_labels'][$label] = true;
            $historyDateRowsCluster[$dailyDateKey]['daily_progress_remarks'][] = $dailyActivityRemark;
        }
    } else {
        $dailyScope = implDetectHistoryScope($dailyActivity['scope_type'] ?? '', '');
        $targetHistoryRows = $dailyScope === 'SUBFEEDER' ? 'historyDateRowsSubfeeder' : 'historyDateRowsCluster';
        if (!isset($$targetHistoryRows[$dailyDateKey])) {
            $$targetHistoryRows[$dailyDateKey] = [
                'progress_date' => $dailyDateKey,
                'remark' => [],
                'achieve' => [],
                'daily_non_boq_labels' => [],
                'daily_progress_remarks' => [],
            ];
        }
        if (!isset($$targetHistoryRows[$dailyDateKey]['daily_progress_remarks'])) {
            $$targetHistoryRows[$dailyDateKey]['daily_progress_remarks'] = [];
        }
        $$targetHistoryRows[$dailyDateKey]['daily_progress_remarks'][] = $dailyActivityRemark;
    }
}

foreach ($compareRows as $row) {
    $itemType = strtoupper(trim((string) ($row['item_type'] ?? 'LAINNYA')));
    if ($itemType === '') {
        $itemType = 'LAINNYA';
    }

    if (!isset($historyTypePlan[$itemType])) {
        $historyTypePlan[$itemType] = 0;
        $historyTypeOrder[] = $itemType;
    }
    $historyTypePlan[$itemType] += (float) ($row['qty_boq'] ?? 0);
    if (!isset($historyTypePlanCluster[$itemType])) {
        $historyTypePlanCluster[$itemType] = 0;
    }
    if (!isset($historyTypePlanSubfeeder[$itemType])) {
        $historyTypePlanSubfeeder[$itemType] = 0;
    }
    $historyTypePlanCluster[$itemType] += (float) ($row['qty_cluster'] ?? 0);
    $historyTypePlanSubfeeder[$itemType] += (float) ($row['qty_subfeeder'] ?? 0);

    if (!isset($boqTypeBreakdown[$itemType])) {
        $boqTypeBreakdown[$itemType] = [];
    }
    $boqTypeBreakdown[$itemType][] = [
        'item_name' => (string) ($row['item_name'] ?? '-'),
        'excel_item_name' => (string) ($row['excel_item_name'] ?? ''),
        'qty_cluster' => (float) ($row['qty_cluster'] ?? 0),
        'qty_subfeeder' => (float) ($row['qty_subfeeder'] ?? 0),
        'qty_plan' => (float) ($row['qty_boq'] ?? 0),
        'qty_achiev' => (float) ($row['progress_qty'] ?? 0),
        'qty_remaining' => (float) ($row['remaining_qty'] ?? 0),
        // Breakdown foto tampilkan total upload/target (harian + comply)
        'photo_target' => (int) (($row['target_foto_required'] ?? 0) + ($row['target_comply_photo_required'] ?? 0)),
        'photo_uploaded' => (int) ($row['uploaded_photos'] ?? 0),
    ];

    $rowHistory = $historyMap[(int) ($row['id_boq_baseline_item'] ?? 0)] ?? [];
    foreach ($rowHistory as $entry) {
        $progressDate = (string) ($entry['progress_date'] ?? '');
        if ($progressDate === '') {
            continue;
        }

        $entryScope = implDetectHistoryScope($entry['scope_type'] ?? '', $entry['remark_progress'] ?? '');
        $targetHistoryRows = $entryScope === 'SUBFEEDER' ? 'historyDateRowsSubfeeder' : 'historyDateRowsCluster';
        if (!isset($$targetHistoryRows[$progressDate])) {
            $$targetHistoryRows[$progressDate] = [
                'progress_date' => $progressDate,
                'remark' => [],
                'achieve' => [],
            ];
        }

        if (!isset($$targetHistoryRows[$progressDate]['achieve'][$itemType])) {
            $$targetHistoryRows[$progressDate]['achieve'][$itemType] = 0;
        }
        $$targetHistoryRows[$progressDate]['achieve'][$itemType] += (float) ($entry['qty_progress'] ?? 0);

        $remarkValue = trim((string) ($entry['remark_progress'] ?? ''));
        if ($remarkValue !== '') {
            $$targetHistoryRows[$progressDate]['remark'][] = $remarkValue;
        }
    }
}
$tiangTanamMap = implBuildTiangTanamBreakdownMap((array) ($boqTypeBreakdown['TIANG'] ?? []), (array) $dailyActivities);
foreach ($boqTypeBreakdown as $breakdownType => &$breakdownRows) {
    foreach ($breakdownRows as $index => &$breakdownRow) {
        $achievComply = (float) ($breakdownRow['qty_achiev'] ?? 0);
        $achievTanam = strtoupper((string) $breakdownType) === 'TIANG'
            ? (float) ($tiangTanamMap[$index] ?? 0)
            : 0.0;
        $breakdownRow['qty_achiev_tanam'] = $achievTanam;
        $breakdownRow['qty_achiev_comply'] = $achievComply;
        $breakdownRow['qty_gap_tanam_comply'] = $achievTanam - $achievComply;
    }
    unset($breakdownRow);
}
unset($breakdownRows);
$historyDateRows = $historyDateRowsCluster;
$historyRows = [];
$historyFinalAchieve = array_fill_keys($historyTypeOrder, 0);
$galleryRows = [];
$implementationGalleryGroups = [];
$complyGalleryGroups = [];
$complySelectableItems = [];
$complyBuilderItems = [];
$baselineByType = [];
$baselineByName = [];
$activityDetailOptions = [
    'PULLING_CABLE' => [],
    'DIGGING_HOLE' => [],
    'TANAM_TIANG' => [],
    'COR_FONDATION' => [],
    'SLING_WIRE' => [],
    'INSTALASI_FAT_FDT' => [],
    'SPLICING_FO' => [],
    'RAPIH_AKSESORIS' => ['AKSESORIS'],
    'RAPIH_LABEL_TIANG' => ['LABEL TIANG'],
    'RAPIH_LABEL_KABEL' => ['LABEL KABEL'],
];

foreach ($compareRows as $row) {
    $itemTypeForOption = strtoupper(trim((string) ($row['item_type'] ?? '')));
    $itemNameForOption = trim((string) ($row['item_name'] ?? ''));
    if ($itemTypeForOption !== '' && $itemNameForOption !== '') {
        if ($itemTypeForOption === 'CABLE') {
            $activityDetailOptions['PULLING_CABLE'][$itemNameForOption] = true;
        }
        if ($itemTypeForOption === 'TIANG') {
            $activityDetailOptions['TANAM_TIANG'][$itemNameForOption] = true;
        }
        if ($itemTypeForOption === 'SLING WIRE') {
            $activityDetailOptions['SLING_WIRE'][$itemNameForOption] = true;
        }
        if (in_array($itemTypeForOption, ['FAT', 'FDT'], true)) {
            $activityDetailOptions['INSTALASI_FAT_FDT'][$itemNameForOption] = true;
        }
        if ($itemTypeForOption === 'SPLICING') {
            $activityDetailOptions['SPLICING_FO'][$itemNameForOption] = true;
        }
    }

    if (!empty($row['comply_enabled'])) {
        $complySelectableItems[] = $row;
    }

    $rowItemType = strtoupper(trim((string) ($row['item_type'] ?? '')));
    $rowItemName = strtoupper(trim((string) ($row['item_name'] ?? '')));
    if ($rowItemType !== '' && empty($baselineByType[$rowItemType])) {
        $baselineByType[$rowItemType] = (int) ($row['id_boq_baseline_item'] ?? 0);
    }
    if ($rowItemName !== '') {
        $baselineByName[$rowItemName] = (int) ($row['id_boq_baseline_item'] ?? 0);
    }

    $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
    $itemHistoryRows = $historyMap[$baselineItemId] ?? [];
    foreach ($itemHistoryRows as $entry) {
        foreach (($entry['photos'] ?? []) as $photo) {
            $isPoleExtComply = strtoupper(trim((string) ($photo['photo_category'] ?? 'HARIAN'))) === 'COMPLY'
                && implIsPoleExtComplyPhoto($photo);
            $galleryRows[] = [
                'item_name' => $isPoleExtComply ? 'Tiang Eksisting' : (string) ($row['item_name'] ?? '-'),
                'item_type' => $isPoleExtComply ? 'TIANG EKSISTING' : (string) ($row['item_type'] ?? '-'),
                'photo_type' => (string) ($row['photo_type'] ?? ''),
                'progress_date' => (string) ($entry['progress_date'] ?? '-'),
                'remark_progress' => (string) ($entry['remark_progress'] ?? ''),
                'file_name' => (string) ($photo['file_name'] ?? 'Foto Progress'),
                'file_path' => (string) ($photo['file_path'] ?? ''),
                'caption' => (string) ($photo['caption'] ?? ''),
                'photo_category' => (string) ($photo['photo_category'] ?? 'HARIAN'),
                'comply_label' => (string) ($photo['comply_label'] ?? ''),
                'id_progress_photo' => (int) ($photo['id_progress_photo'] ?? 0),
                'uploaded_by' => (int) ($photo['uploaded_by'] ?? 0),
                'status_photo' => (string) ($photo['status_photo'] ?? ''),
                'review_remark' => (string) ($photo['review_remark'] ?? ''),
            ];
        }
    }
}

foreach ((array) ($masterBoqItems ?? []) as $masterItem) {
    $masterItemName = trim((string) ($masterItem['item_name'] ?? ''));
    $masterItemType = strtoupper(trim((string) ($masterItem['item_type'] ?? '')));
    if ($masterItemName === '' || $masterItemType === '') {
        continue;
    }

    if ($masterItemType === 'CABLE') {
        $activityDetailOptions['PULLING_CABLE'][$masterItemName] = true;
    } elseif ($masterItemType === 'TIANG') {
        $activityDetailOptions['TANAM_TIANG'][$masterItemName] = true;
        $activityDetailOptions['DIGGING_HOLE'][$masterItemName] = true;
        $activityDetailOptions['COR_FONDATION'][$masterItemName] = true;
    } elseif ($masterItemType === 'SLING WIRE') {
        $activityDetailOptions['SLING_WIRE'][$masterItemName] = true;
    } elseif (in_array($masterItemType, ['FAT', 'FDT'], true)) {
        $activityDetailOptions['INSTALASI_FAT_FDT'][$masterItemName] = true;
    } elseif ($masterItemType === 'SPLICING') {
        $activityDetailOptions['SPLICING_FO'][$masterItemName] = true;
    }

    $lookupName = strtoupper($masterItemName);
    $baselineItemId = (int) ($baselineByName[$lookupName] ?? 0);
    if ($baselineItemId <= 0) {
        $baselineItemId = (int) ($baselineByType[$masterItemType] ?? 0);
    }
    if ($baselineItemId <= 0) {
        continue;
    }

    $complySource = null;
    foreach ($complySelectableItems as $complyItem) {
        if ((int) ($complyItem['id_boq_baseline_item'] ?? 0) === $baselineItemId) {
            $complySource = $complyItem;
            break;
        }
    }
    if (empty($complySource)) {
        continue;
    }

    $catalogKey = strtoupper($masterItemType . '|' . $masterItemName);
    if (isset($complyBuilderItems[$catalogKey])) {
        continue;
    }
    $complyBuilderItems[$catalogKey] = [
        'id_boq_baseline_item' => $baselineItemId,
        'item_name' => $masterItemName,
        'item_type' => $masterItemType,
        'comply_photo_per_label' => (int) ($complySource['comply_photo_per_label'] ?? 1),
        'comply_label_prefix' => (string) ($complySource['comply_label_prefix'] ?? $masterItemName),
        'comply_label_placeholder' => (string) ($complySource['comply_label_placeholder'] ?? 'Nama / nomor item comply'),
        'comply_requirement_text' => (string) ($complySource['comply_requirement_text'] ?? ''),
    ];
}
$complyBuilderItems = array_values($complyBuilderItems);

$historyInitialDate = !empty($cluster['drm_date'])
    ? (string) $cluster['drm_date']
    : (!empty($cluster['boq_approved_at']) ? substr((string) $cluster['boq_approved_at'], 0, 10) : '-');

$historyBuildCluster = implBuildHistoryRowsByScope($historyDateRowsCluster, $historyTypeOrder, $nonBoqLabelOrder, $historyInitialDate);
$historyBuildSubfeeder = implBuildHistoryRowsByScope($historyDateRowsSubfeeder, $historyTypeOrder, $nonBoqLabelOrder, $historyInitialDate);

$historyRowsCluster = (array) ($historyBuildCluster['rows'] ?? []);
$historyRowsSubfeeder = (array) ($historyBuildSubfeeder['rows'] ?? []);
$historyFinalAchieveCluster = (array) ($historyBuildCluster['final_achieve'] ?? array_fill_keys($historyTypeOrder, 0));
$historyFinalAchieveSubfeeder = (array) ($historyBuildSubfeeder['final_achieve'] ?? array_fill_keys($historyTypeOrder, 0));
$hasSubfeederBoqPlan = abs(array_sum((array) $historyTypePlanSubfeeder)) > 0.00001;

// Backward compatibility for sections that still consume single history dataset.
$historyRows = $historyRowsCluster;
$historyFinalAchieve = $historyFinalAchieveCluster;
foreach ($activityDetailOptions as $activityCode => $optionValues) {
    if (array_values($optionValues) !== $optionValues) {
        $activityDetailOptions[$activityCode] = array_keys($optionValues);
    }
}

foreach ($galleryRows as $galleryRow) {
    $galleryType = strtoupper(trim((string) ($galleryRow['item_type'] ?? 'LAINNYA')));
    if ($galleryType === '') {
        $galleryType = 'LAINNYA';
    }

    $galleryCategory = strtoupper(trim((string) ($galleryRow['photo_category'] ?? 'HARIAN')));
    $galleryBucket = $galleryCategory === 'COMPLY' ? 'comply' : 'implementation';
    $galleryKey = $galleryType . '||' . $galleryCategory . '||' . (string) ($galleryRow['item_name'] ?? '-') . '||' . (string) ($galleryRow['comply_label'] ?? '');
    $targetGroups = $galleryBucket === 'comply' ? $complyGalleryGroups : $implementationGalleryGroups;

    if (!isset($targetGroups[$galleryType])) {
        $targetGroups[$galleryType] = [];
    }

    if (!isset($targetGroups[$galleryType][$galleryKey])) {
        $targetGroups[$galleryType][$galleryKey] = [
            'item_name' => (string) ($galleryRow['item_name'] ?? '-'),
            'item_type' => $galleryType,
            'photo_category' => $galleryCategory,
            'photo_type' => (string) ($galleryRow['photo_type'] ?? ''),
            'comply_label' => (string) ($galleryRow['comply_label'] ?? ''),
            'dates' => [],
            'remarks' => [],
            'photos' => [],
        ];
    }

    if (!empty($galleryRow['progress_date']) && $galleryRow['progress_date'] !== '-') {
        $targetGroups[$galleryType][$galleryKey]['dates'][] = (string) $galleryRow['progress_date'];
    }

    $remarkText = trim((string) (($galleryRow['caption'] ?? '') !== '' ? $galleryRow['caption'] : ($galleryRow['remark_progress'] ?? '')));
    if ($remarkText !== '') {
        $targetGroups[$galleryType][$galleryKey]['remarks'][] = $remarkText;
    }

    $targetGroups[$galleryType][$galleryKey]['photos'][] = [
        'id_progress_photo' => (int) ($galleryRow['id_progress_photo'] ?? 0),
        'uploaded_by' => (int) ($galleryRow['uploaded_by'] ?? 0),
        'file_name' => (string) ($galleryRow['file_name'] ?? 'Foto Progress'),
        'file_path' => (string) ($galleryRow['file_path'] ?? ''),
        'caption' => (string) ($galleryRow['caption'] ?? ''),
        'photo_category' => $galleryCategory,
        'comply_label' => (string) ($galleryRow['comply_label'] ?? ''),
        'status_photo' => (string) ($galleryRow['status_photo'] ?? ''),
        'review_remark' => (string) ($galleryRow['review_remark'] ?? ''),
    ];

    if ($galleryBucket === 'comply') {
        $complyGalleryGroups = $targetGroups;
    } else {
        $implementationGalleryGroups = $targetGroups;
    }
}

$poleExtLabelCounters = [
    'CLUSTER' => 0,
    'SUBFEEDER' => 0,
];
foreach ($galleryRows as $galleryRow) {
    if (strtoupper(trim((string) ($galleryRow['photo_category'] ?? ''))) !== 'COMPLY') {
        continue;
    }

    $captionText = strtoupper(trim((string) ($galleryRow['caption'] ?? '')));
    $labelText = strtoupper(trim((string) ($galleryRow['comply_label'] ?? '')));
    if (strpos($captionText, 'POLE EXT') !== 0 && strpos($labelText, 'POLE EXT') !== 0) {
        continue;
    }

    $scopeText = strpos($captionText, 'SUBFEEDER') !== false ? 'SUBFEEDER' : 'CLUSTER';
    $sequenceNumber = 0;
    if (preg_match('/\b(\d{3,})\s*$/', $labelText, $matches)) {
        $sequenceNumber = (int) $matches[1];
    } elseif (preg_match('/\b(\d{3,})\s*$/', $captionText, $matches)) {
        $sequenceNumber = (int) $matches[1];
    }

    $poleExtLabelCounters[$scopeText] = max((int) ($poleExtLabelCounters[$scopeText] ?? 0), $sequenceNumber);
    if ($sequenceNumber <= 0) {
        $poleExtLabelCounters[$scopeText]++;
    }
}

$qtyTargetTotal = (float) ($cluster['target_qty_total'] ?? 0);
$qtyActualTotal = (float) ($cluster['actual_qty_total'] ?? 0);
$photoTargetTotal = (int) ($cluster['target_photo_total'] ?? 0);
$photoUploadedTotal = (int) ($cluster['uploaded_photo_total'] ?? 0);
$itemTotal = (int) ($cluster['total_item'] ?? 0);
$itemDone = 0;
$itemOnProgress = 0;

foreach ($compareRows as $row) {
    $rowStatus = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
    if ($rowStatus === 'DONE') {
        $itemDone++;
    } elseif ($rowStatus === 'ON PROGRESS') {
        $itemOnProgress++;
    }
}

$itemActive = $itemDone + $itemOnProgress;
$qtyPercent = $qtyTargetTotal > 0 ? min(100, round(($qtyActualTotal / $qtyTargetTotal) * 100)) : 0;
$photoPercent = $photoTargetTotal > 0 ? min(100, round(($photoUploadedTotal / $photoTargetTotal) * 100)) : 0;
$itemPercent = $itemTotal > 0 ? min(100, round(($itemDone / $itemTotal) * 100)) : 0;
$overallPercent = (int) round(($qtyPercent + $photoPercent + $itemPercent) / 3);
$agingWorkingDays = !empty($cluster['drm_date']) ? implCountWorkingDays((string) $cluster['drm_date']) : 0;
$agingTargetDate = !empty($cluster['drm_date']) ? implAddWorkingDays((string) $cluster['drm_date'], 23) : null;
$agingPercent = min(100, round(($agingWorkingDays / 23) * 100));

if (!function_exists('implPhotoReviewBadgeClass')) {
    function implPhotoReviewBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
?>

<style>
    .impl-card .card-header,
    .impl-table-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .impl-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .impl-cluster-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .impl-cluster-overview__card {
        padding: 1rem 1.1rem;
        border-radius: 20px;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, .08), transparent 34%),
            linear-gradient(135deg, #ffffff, #f8fbff);
        border: 1px solid #dbeafe;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .07);
        position: relative;
        overflow: hidden;
    }

    .impl-cluster-overview__card::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 4px;
        background: linear-gradient(90deg, #2563eb, #14b8a6);
    }

    .impl-cluster-overview__label {
        color: #64748b;
        font-size: .85rem;
        margin-bottom: .3rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 700;
    }

    .impl-cluster-overview__value {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .impl-cluster-overview__value--hero {
        font-size: 1.6rem;
    }

    .impl-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .impl-meta-box {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1rem;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
        min-height: 112px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .impl-meta-box__label {
        color: #64748b;
        font-size: .82rem;
        margin-bottom: .25rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
    }

    .impl-meta-box__value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .impl-meta-box__sub {
        margin-top: .25rem;
        font-size: .8rem;
        color: #64748b;
        line-height: 1.45;
    }

    .impl-meta-box--aging {
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, .12), transparent 30%),
            linear-gradient(180deg, #fffdf7, #fffaf0);
        border-color: #fde68a;
    }

    .impl-meta-box__pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        width: fit-content;
        margin-top: .55rem;
        padding: .28rem .62rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .74rem;
        font-weight: 700;
    }

    .impl-meta-box__progress {
        margin-top: .7rem;
    }

    .impl-meta-box__progress-track {
        height: 10px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .impl-meta-box__progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #f59e0b, #f97316);
    }

    .impl-meta-box__progress-note {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        margin-top: .35rem;
        font-size: .76rem;
        color: #64748b;
    }

    .impl-hero {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, .18), transparent 32%),
            linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0f766e);
        border-radius: 20px;
        padding: 1.25rem;
        color: #fff;
        margin-bottom: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }

    .impl-hero__top {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1rem;
    }

    .impl-hero__title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: .2rem;
    }

    .impl-hero__subtitle {
        color: rgba(255, 255, 255, .78);
        font-size: .92rem;
    }

    .impl-hero__badge {
        min-width: 130px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 16px;
        padding: .9rem 1rem;
        text-align: center;
        backdrop-filter: blur(8px);
    }

    .impl-hero__badge-label {
        font-size: .8rem;
        color: rgba(255, 255, 255, .75);
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .impl-hero__badge-value {
        font-size: 2rem;
        line-height: 1.1;
        font-weight: 800;
        margin-top: .15rem;
    }

    .impl-hero__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .impl-daily-modal .modal-dialog {
        max-width: 78vw;
    }

    .impl-daily-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 28px 60px rgba(15, 23, 42, .28);
        background: linear-gradient(180deg, #f8fbff 0%, #f1f7ff 100%);
    }

    .impl-daily-modal .modal-header {
        border-bottom: 0;
        background: linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0369a1);
    }

    .impl-daily-modal .modal-title {
        font-weight: 800;
        letter-spacing: .02em;
    }

    .impl-daily-modal__sub {
        margin-top: .2rem;
        font-size: .84rem;
        opacity: .86;
    }

    .impl-daily-modal .modal-body {
        padding: 1.25rem;
    }

    .impl-daily-shell {
        border: 1px solid #dbe7f5;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .08);
        padding: 1rem 1.05rem;
        margin-bottom: 1rem;
    }

    .impl-daily-shell__title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .8rem;
    }

    .impl-daily-modal .form-control,
    .impl-daily-modal .form-control-file {
        border-radius: 12px;
        border-color: #d6e1ef;
    }

    .impl-daily-modal .table th {
        white-space: nowrap;
        background: #f8fbff;
    }

    .impl-daily-modal .modal-footer {
        border-top: 0;
        background: #eef4fb;
        padding: .9rem 1.25rem 1.15rem;
    }

    .impl-daily-detail-modal .modal-dialog {
        max-width: 78vw;
    }

    .impl-daily-detail-modal .modal-content {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 28px 60px rgba(15, 23, 42, .28);
        background: linear-gradient(180deg, #f8fbff 0%, #f1f7ff 100%);
    }

    .impl-daily-detail-modal .modal-header {
        border-bottom: 0;
        background: linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0369a1);
    }

    .impl-daily-detail-modal .modal-body {
        padding: 1.25rem;
    }

    .impl-progress-card {
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 16px;
        padding: 1rem;
        backdrop-filter: blur(8px);
    }

    .impl-progress-card__head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: .75rem;
        margin-bottom: .6rem;
    }

    .impl-progress-card__label {
        font-size: .9rem;
        font-weight: 700;
    }

    .impl-progress-card__percent {
        font-size: 1.15rem;
        font-weight: 800;
    }

    .impl-progress-track {
        height: 12px;
        background: rgba(255, 255, 255, .12);
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: .7rem;
    }

    .impl-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .impl-progress-track--qty span {
        background: linear-gradient(90deg, #38bdf8, #60a5fa);
    }

    .impl-progress-track--photo span {
        background: linear-gradient(90deg, #34d399, #22c55e);
    }

    .impl-progress-track--item span {
        background: linear-gradient(90deg, #fbbf24, #f97316);
    }

    .impl-progress-card__meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .84rem;
        color: rgba(255, 255, 255, .82);
    }

    .impl-progress-card__meta--item {
        flex-direction: column;
        align-items: flex-start;
        gap: .2rem;
    }

    .impl-progress-card__hint {
        font-size: .78rem;
        color: rgba(255, 255, 255, .72);
    }

    .impl-summary-box {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #e5edf6;
        border-radius: 16px;
        padding: 1rem;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
        position: relative;
        overflow: hidden;
    }

    .impl-summary-box::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #2563eb, #14b8a6);
    }

    .impl-summary-box__label {
        color: #64748b;
        font-size: .85rem;
        margin-bottom: .25rem;
    }

    .impl-summary-box__value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }

    .impl-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1rem;
        transition: all .2s ease;
        cursor: pointer;
    }

    .impl-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
    }

    .impl-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .impl-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .impl-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .impl-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: .75rem;
    }

    .impl-photo-grid img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #dbe3ee;
    }

    .impl-history-item {
        border-left: 3px solid #dbe3ee;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .impl-history-item:last-child {
        margin-bottom: 0;
    }

    .impl-tabs .nav-link {
        font-weight: 700;
        color: #475569;
    }

    .impl-tabs .nav-link.active {
        color: #1d4ed8;
    }

    .impl-history-table thead th {
        background: #1f2937;
        color: #fff;
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
    }

    .impl-history-table tbody tr:first-child {
        background: #d9eef7;
    }

    .impl-history-table tfoot td {
        font-weight: 700;
        background: #f8fafc;
    }

    .impl-history-table td {
        text-align: center;
        vertical-align: middle;
    }

    .impl-history-panel {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .impl-history-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .95rem 1rem;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .impl-history-panel__title {
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
    }

    .impl-history-panel__note {
        font-size: .8rem;
        color: #64748b;
    }

    .impl-history-panel .table {
        margin-bottom: 0;
    }

    .impl-history-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .75rem;
        padding: .75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .impl-history-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .75rem;
        padding: .75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }

    .impl-history-summary__card {
        border: 1px solid #dbeafe;
        border-radius: 10px;
        padding: .55rem .7rem;
        background: #f8fbff;
    }

    .impl-history-summary__label {
        font-size: .72rem;
        color: #475569;
        font-weight: 700;
    }

    .impl-history-summary__value {
        font-size: .9rem;
        color: #0f172a;
        font-weight: 800;
    }


    .impl-gallery-section + .impl-gallery-section {
        margin-top: 1.25rem;
    }

    .impl-gallery-section__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .65rem;
    }

    .impl-gallery-filter {
        display: flex;
        align-items: flex-end;
        gap: .75rem;
        padding: .8rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        margin-bottom: 1rem;
    }

    .impl-gallery-filter__group {
        min-width: 180px;
    }

    .impl-gallery-filter__search {
        flex: 1 1 260px;
    }

    .impl-gallery-filter label {
        font-size: .72rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        margin-bottom: .25rem;
    }

    .impl-gallery-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
        text-align: center;
        padding: 1.5rem;
    }

    .impl-gallery-table {
        margin-bottom: 0;
    }

    .impl-gallery-table thead th {
        background: #ffffff;
        color: #0f172a;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        border-width: 2px;
    }

    .impl-gallery-table td {
        vertical-align: top;
        border-width: 2px;
        background: #fff;
    }

    .impl-gallery-table__no,
    .impl-gallery-table__item,
    .impl-gallery-table__date {
        text-align: center;
        vertical-align: middle !important;
        font-weight: 700;
    }

    .impl-gallery-photo-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .75rem;
        align-items: start;
    }

    .impl-gallery-photo-card {
        display: block;
        border: 4px solid #111827;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
    }

    .impl-gallery-photo-card--shell {
        border: 1px solid #dbe7f3;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .impl-gallery-photo-card__meta {
        padding: .7rem .8rem .8rem;
        border-top: 1px solid #e2e8f0;
    }

    .impl-photo-action-menu .dropdown-toggle {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 999px;
        font-weight: 800;
        line-height: 1;
    }

    .impl-photo-action-menu .dropdown-toggle::after {
        display: none;
    }

    .impl-photo-action-menu .dropdown-menu {
        min-width: 8.5rem;
        font-size: .82rem;
    }

    .impl-gallery-photo-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        display: block;
    }

    .impl-gallery-photo-card div {
        padding: .45rem .55rem;
        font-size: .72rem;
        color: #475569;
        line-height: 1.4;
    }

    .impl-gallery-table__remark {
        font-size: .9rem;
        color: #0f172a;
        line-height: 1.55;
        min-width: 160px;
        vertical-align: middle !important;
        text-align: center;
    }

    .impl-gallery-table__photo {
        min-width: 520px;
        vertical-align: middle !important;
    }

    .impl-gallery-table__date {
        min-width: 110px;
        font-size: .92rem;
    }

    .impl-comply-upload-card {
        border: 1px solid #dbe7f3;
        border-radius: 16px;
        background: linear-gradient(135deg, #fffdf5, #ffffff);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .05);
    }

    .impl-comply-upload-card__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .impl-comply-upload-card__note {
        font-size: .83rem;
        color: #64748b;
    }

    .impl-comply-hint {
        min-height: 44px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: .75rem .9rem;
        color: #475569;
        font-size: .84rem;
    }

    @media (max-width: 767.98px) {
        .impl-gallery-photo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .impl-gallery-photo-card img {
            height: 120px;
        }

        .impl-gallery-table__photo {
            min-width: 320px;
        }
    }

    .impl-history-modal-table thead th {
        background: #1f2937;
        color: #fff;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .impl-history-modal-table td {
        vertical-align: middle;
    }

    .impl-history-modal-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: .75rem;
    }

    .impl-history-modal-photo {
        display: block;
        border: 1px solid #dbe7f3;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .impl-history-modal-photo img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }

    .impl-history-modal-photo div {
        padding: .45rem .55rem;
        font-size: .72rem;
        color: #475569;
    }

    .impl-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .82);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2050;
        padding: 2rem;
    }

    .impl-lightbox.is-open {
        display: flex;
    }

    .impl-lightbox__dialog {
        width: min(88vw, 1380px);
        max-height: 92vh;
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .32);
        display: flex;
        flex-direction: column;
    }

    .impl-lightbox__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .9rem 1rem;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .impl-lightbox__toolbar {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .impl-lightbox__title {
        font-weight: 800;
        color: #0f172a;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .impl-lightbox__close {
        border: 0;
        background: #0f172a;
        color: #fff;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        font-size: 1.2rem;
        line-height: 1;
        cursor: pointer;
    }

    .impl-lightbox__action {
        border: 0;
        background: #e2e8f0;
        color: #0f172a;
        width: 38px;
        height: 38px;
        border-radius: 999px;
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        font-weight: 800;
    }

    .impl-lightbox__action:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .impl-lightbox__save {
        width: auto;
        min-width: 82px;
        padding: 0 .8rem;
        border-radius: 999px;
        background: #0f766e;
        color: #fff;
        font-size: .82rem;
    }

    .impl-lightbox__body {
        padding: 1rem;
        overflow: hidden;
        text-align: center;
        background: #f8fafc;
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
    }

    .impl-lightbox__stage {
        flex: 1 1 auto;
        height: calc(92vh - 132px);
        min-height: 0;
        display: block;
        overflow: auto;
        cursor: grab;
        user-select: none;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .impl-lightbox__stage.is-dragging {
        cursor: grabbing;
    }

    .impl-lightbox__image {
        display: block;
        margin: 0 auto;
        max-width: none;
        max-height: none;
        width: auto;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
        user-select: none;
        -webkit-user-drag: none;
    }

    .impl-lightbox__caption {
        flex: 0 0 auto;
        margin-top: .85rem;
        color: #475569;
        font-size: .9rem;
    }

    .js-lazy-photo {
        background: #eef2f7;
        transition: opacity .18s ease;
    }

    .js-lazy-photo:not(.is-loaded) {
        opacity: .72;
        filter: saturate(.8);
    }

    @media (max-width: 767.98px) {
        .impl-cluster-overview {
            grid-template-columns: 1fr;
        }

        .impl-hero {
            padding: 1rem;
        }

        .impl-hero__badge {
            width: 100%;
        }

        .impl-lightbox {
            padding: 1rem;
        }

        .impl-lightbox__dialog {
            width: 100%;
        }

        .impl-gallery-filter {
            align-items: stretch;
            flex-direction: column;
        }

        .impl-gallery-filter__group,
        .impl-gallery-filter__search {
            min-width: 0;
            width: 100%;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Implementasi BOQ MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('Implementasi_BOQ_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <?php if (!empty($activityReady)): ?>
<?php if ($canApprovalDailyAction): ?>
<div class="modal fade impl-daily-modal" id="modal-daily-activity" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true" data-keyboard="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/saveDailyActivity') ?>" enctype="multipart/form-data" id="form-daily-activity-builder">
                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                <div class="modal-header bg-primary text-white">
                                    <div>
                                        <h5 class="modal-title mb-0">Input Progress Harian Aktivitas</h5>
                                        <div class="impl-daily-modal__sub">Tambahkan beberapa kategori aktivitas sekaligus dalam satu hari pelaporan.</div>
                                    </div>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="impl-daily-shell">
                                        <div class="impl-daily-shell__title">Detail Cluster</div>
                                        <div class="row mb-3">
                                            <div class="col-md-5">
                                                <label class="small text-muted mb-1">Cluster</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small text-muted mb-1">Regional</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small text-muted mb-1">Kota</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small text-muted mb-1">Status</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['implementation_status'] ?? '-')) ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="impl-daily-shell__title">Header Aktivitas</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Tanggal</label>
                                                    <input type="date" name="activity_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Jenis</label>
                                                    <select name="scope_type" class="form-control" required>
                                                        <option value="CLUSTER">CLUSTER</option>
                                                        <option value="SUBFEEDER">SUBFEEDER</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4"></div>
                                        </div>
                                    </div>

                                    <div class="impl-daily-shell">
                                        <div class="impl-daily-shell__title">Tim Pelaksana</div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Jumlah Team</label>
                                                    <input type="number" name="team_count" min="0" step="1" class="form-control" value="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Jumlah Orang</label>
                                                    <input type="number" name="worker_count" min="0" step="1" class="form-control" value="0" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="impl-daily-shell">
                                        <div class="impl-daily-shell__title">Builder Aktivitas</div>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Kategori Aktivitas</label>
                                                    <select class="form-control js-activity-code-selector">
                                                        <option value="">Pilih Aktivitas</option>
                                                        <?php foreach ((array) $activityDefinitions as $activityDef): ?>
                                                            <option
                                                                value="<?= htmlspecialchars((string) ($activityDef['activity_code'] ?? '')) ?>"
                                                                data-default-unit="<?= htmlspecialchars((string) ($activityDef['default_unit'] ?? ''), ENT_QUOTES) ?>"
                                                                data-activity-name="<?= htmlspecialchars((string) ($activityDef['activity_name'] ?? ''), ENT_QUOTES) ?>"
                                                                data-boq-type="<?= htmlspecialchars((string) ($activityDef['boq_type'] ?? ''), ENT_QUOTES) ?>">
                                                                <?= htmlspecialchars((string) ($activityDef['activity_name'] ?? '-')) ?> (<?= htmlspecialchars((string) ($activityDef['boq_type'] ?? '-')) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Detail</label>
                                                    <select class="form-control js-activity-detail-selector"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Unit</label>
                                                    <input type="text" class="form-control js-activity-unit" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>Qty</label>
                                                    <input type="number" step="0.01" min="0.01" class="form-control js-activity-qty">
                                                </div>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-primary btn-block js-add-daily-activity-row mb-3">Tambah</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="impl-daily-shell">
                                        <div class="impl-daily-shell__title">Daftar Aktivitas Hari Ini</div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm mb-2">
                                                <thead>
                                                    <tr>
                                                        <th>Kategori</th>
                                                        <th>Detail</th>
                                                        <th>BOQ Type</th>
                                                        <th>Unit</th>
                                                        <th>Qty</th>
                                                        <th>Foto</th>
                                                        <th>Remark</th>
                                                        <th style="width:70px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="js-daily-activity-items">
                                                    <tr class="js-empty-row"><td colspan="8" class="text-center text-muted">Belum ada item aktivitas.</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <template id="daily-activity-item-template">
                                        <tr class="js-daily-activity-item">
                                            <td>
                                                <input type="hidden" data-name="activity_code" value="">
                                                <span class="js-col-activity-name">-</span>
                                            </td>
                                            <td>
                                                <input type="hidden" data-name="activity_detail" value="">
                                                <span class="js-col-activity-detail">-</span>
                                            </td>
                                            <td><span class="js-col-boq-type">-</span></td>
                                            <td><span class="js-col-unit">-</span></td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm js-col-qty-input" data-name="qty_activity" step="0.01" min="0.01" value="0.01" required>
                                            </td>
                                            <td>
                                                <input type="file" class="form-control-file mb-2" data-photo-input multiple accept=".jpg,.jpeg,.png,.webp" required>
                                                <div class="small text-muted js-photo-preview-empty">Belum ada foto dipilih</div>
                                                <div class="d-flex flex-wrap js-photo-preview-list" style="gap:.35rem;"></div>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" data-remark-input placeholder="Catatan aktivitas"></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-daily-activity-row">Hapus</button></td>
                                        </tr>
                                    </template>
                                    <div class="small text-muted mt-2">
                                        Pilih kategori + isi qty lalu klik Tambah. Pilih kategori lain untuk menambah baris baru di tabel bawah.
                                    </div>

                                    <div class="impl-daily-shell">
                                        <div class="impl-daily-shell__title">Remarks BOQ Tracker</div>
                                        <div class="form-group mb-0">
                                            <label>Remarks</label>
                                            <textarea name="tracker_remark" class="form-control" rows="3" placeholder="Remarks ini akan tampil di kolom remark BOQ Tracker untuk auto progress dari daily activity"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Progress Harian</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card card-primary shadow-sm impl-card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Cluster Implementasi</h3>
                </div>
                <div class="card-body">
                    <div class="impl-hero">
                        <div class="impl-hero__top">
                            <div>
                                <div class="impl-hero__title">Progress Implementasi Cluster</div>
                                <div class="impl-hero__subtitle">
                                    Pantau pencapaian quantity, kelengkapan foto, dan penyelesaian item dalam satu ringkasan.
                                </div>
                            </div>
                            <div class="impl-hero__badge">
                                <div class="impl-hero__badge-label">Overall Progress</div>
                                <div class="impl-hero__badge-value"><?= $overallPercent ?>%</div>
                            </div>
                        </div>

                        <div class="impl-hero__grid">
                            <div class="impl-progress-card">
                                <div class="impl-progress-card__head">
                                    <div class="impl-progress-card__label">Progress Quantity</div>
                                    <div class="impl-progress-card__percent"><?= $qtyPercent ?>%</div>
                                </div>
                                <div class="impl-progress-track impl-progress-track--qty">
                                    <span style="width: <?= $qtyPercent ?>%;"></span>
                                </div>
                                <div class="impl-progress-card__meta">
                                    <span>Actual <?= implHistoryNumber($qtyActualTotal) ?></span>
                                    <span>Target <?= implHistoryNumber($qtyTargetTotal, false) ?></span>
                                </div>
                            </div>

                            <div class="impl-progress-card">
                                <div class="impl-progress-card__head">
                                    <div class="impl-progress-card__label">Progress Foto</div>
                                    <div class="impl-progress-card__percent"><?= $photoPercent ?>%</div>
                                </div>
                                <div class="impl-progress-track impl-progress-track--photo">
                                    <span style="width: <?= $photoPercent ?>%;"></span>
                                </div>
                                <div class="impl-progress-card__meta">
                                    <span>Uploaded <?= implHistoryNumber($photoUploadedTotal) ?></span>
                                    <span>Target <?= implHistoryNumber($photoTargetTotal, false) ?></span>
                                </div>
                            </div>

                            <div class="impl-progress-card">
                                <div class="impl-progress-card__head">
                                    <div class="impl-progress-card__label">Progress Item</div>
                                    <div class="impl-progress-card__percent"><?= $itemPercent ?>%</div>
                                </div>
                                <div class="impl-progress-track impl-progress-track--item">
                                    <span style="width: <?= $itemPercent ?>%;"></span>
                                </div>
                                <div class="impl-progress-card__meta impl-progress-card__meta--item">
                                    <span>Done <?= implHistoryNumber($itemDone, false) ?> dari Total <?= implHistoryNumber($itemTotal, false) ?></span>
                                    <span>On Progress <?= implHistoryNumber($itemOnProgress, false) ?> (belum dihitung ke %)</span>
                                    <span>Total Item <?= implHistoryNumber($itemTotal, false) ?></span>
                                </div>
                                <div class="impl-progress-card__hint">Rumus: Done / Total Item</div>
                            </div>
                        </div>
                    </div>

                    <div class="impl-cluster-overview">
                        <div class="impl-cluster-overview__card">
                            <div class="impl-cluster-overview__label">Nama Cluster</div>
                            <div class="impl-cluster-overview__value impl-cluster-overview__value--hero"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div>
                        </div>
                        <div class="impl-cluster-overview__card">
                            <div class="impl-cluster-overview__label">Regional</div>
                            <div class="impl-cluster-overview__value"><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div>
                        </div>
                        <div class="impl-cluster-overview__card">
                            <div class="impl-cluster-overview__label">Kota</div>
                            <div class="impl-cluster-overview__value"><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div>
                        </div>
                    </div>

                    <div class="impl-meta-grid">
                        <div class="impl-meta-box">
                            <div class="impl-meta-box__label">RPM</div>
                            <div class="impl-meta-box__value"><?= htmlspecialchars((string) ($cluster['rpm'] ?? '-')) ?></div>
                            <div class="impl-meta-box__pill">Koordinasi Regional</div>
                        </div>
                        <div class="impl-meta-box">
                            <div class="impl-meta-box__label">SPV</div>
                            <div class="impl-meta-box__value"><?= htmlspecialchars((string) ($cluster['spv'] ?? '-')) ?></div>
                            <div class="impl-meta-box__pill">Supervisi Lapangan</div>
                        </div>
                        <div class="impl-meta-box">
                            <div class="impl-meta-box__label">Team</div>
                            <div class="impl-meta-box__value"><?= htmlspecialchars((string) ($cluster['team_name'] ?? '-')) ?></div>
                            <div class="impl-meta-box__pill">Eksekusi Implementasi</div>
                        </div>
                        <div class="impl-meta-box">
                            <div class="impl-meta-box__label">HP DRM</div>
                            <div class="impl-meta-box__value"><?= implHistoryNumber((float) ($cluster['homepass_drm'] ?? 0), false) ?></div>
                            <div class="impl-meta-box__pill">Basis Perhitungan Progress</div>
                        </div>
                        <div class="impl-meta-box">
                            <div class="impl-meta-box__label">Tanggal DRM</div>
                            <div class="impl-meta-box__value"><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div>
                            <div class="impl-meta-box__pill">Start Aging Implementasi</div>
                        </div>
                        <div class="impl-meta-box impl-meta-box--aging">
                            <div class="impl-meta-box__label">Aging ke RFS</div>
                            <div class="impl-meta-box__value"><?= $agingWorkingDays ?> / 23 Hari Kerja</div>
                            <div class="impl-meta-box__sub">
                                Target RFS <?= $agingTargetDate instanceof DateTime ? htmlspecialchars($agingTargetDate->format('Y-m-d')) : '-' ?>
                                <?php if (!empty($cluster['status_drm'])): ?>
                                    | Status DRM <?= htmlspecialchars((string) $cluster['status_drm']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="impl-meta-box__progress">
                                <div class="impl-meta-box__progress-track">
                                    <span style="width: <?= $agingPercent ?>%;"></span>
                                </div>
                                <div class="impl-meta-box__progress-note">
                                    <span>Progress Aging</span>
                                    <span><?= $agingPercent ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="impl-summary">
                        <div class="impl-summary-box">
                            <div class="impl-summary-box__label">Status Implementasi</div>
                            <div class="impl-summary-box__value"><?= htmlspecialchars((string) ($cluster['implementation_status'] ?? 'NOT STARTED')) ?></div>
                        </div>
                        <div class="impl-summary-box">
                            <div class="impl-summary-box__label">Total Item</div>
                            <div class="impl-summary-box__value"><?= (int) ($cluster['total_item'] ?? 0) ?></div>
                        </div>
                        <div class="impl-summary-box">
                            <div class="impl-summary-box__label">Qty BOQ / Actual</div>
                            <div class="impl-summary-box__value"><?= implHistoryNumber((float) ($cluster['actual_qty_total'] ?? 0)) ?> / <?= implHistoryNumber((float) ($cluster['target_qty_total'] ?? 0), false) ?></div>
                        </div>
                        <div class="impl-summary-box">
                            <div class="impl-summary-box__label">Foto Upload / Target</div>
                            <div class="impl-summary-box__value"><?= implHistoryNumber((int) ($cluster['uploaded_photo_total'] ?? 0)) ?> / <?= implHistoryNumber((int) ($cluster['target_photo_total'] ?? 0), false) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm impl-table-card">
                <div class="card-header">
                    <h3 class="card-title">BOQ Tracker & Daily Progress</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs impl-tabs" id="implCompareTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="impl-history-tab" data-toggle="tab" href="#impl-history-pane" role="tab">BOQ Tracker</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="impl-breakdown-tab" data-toggle="tab" href="#impl-breakdown-pane" role="tab">Daily Progress</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="nav-link" id="impl-gallery-tab" data-toggle="tab" href="#impl-gallery-pane" role="tab">Foto Implementasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="impl-comply-tab" data-toggle="tab" href="#impl-comply-pane" role="tab">Foto Comply</a>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <div class="tab-pane fade show active" id="impl-history-pane" role="tabpanel">
                            <div class="impl-history-panel">
                                <div class="impl-history-panel__head">
                                    <div class="impl-history-panel__title">History Progress Cluster</div>
                                    <div class="impl-history-panel__note">Ringkasan plan vs achievement per jenis item dan per tanggal progress.</div>
                                </div>
                                <div class="impl-history-toolbar">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Pilih Scope">
                                        <button type="button" class="btn btn-primary js-history-scope-toggle" data-scope="CLUSTER">Cluster</button>
                                        <button type="button" class="btn btn-outline-primary js-history-scope-toggle" data-scope="SUBFEEDER">Subfeeder</button>
                                    </div>
                                    <a
                                        href="<?= base_url('Implementasi_BOQ_MyRep/previewProgressReportPdf/' . (int) ($cluster['id_myrep_cluster'] ?? 0) . '?scope=CLUSTER') ?>"
                                        target="_blank"
                                        class="btn btn-outline-dark btn-sm"
                                        id="history_progress_report_link"
                                        data-base-url="<?= htmlspecialchars(base_url('Implementasi_BOQ_MyRep/previewProgressReportPdf/' . (int) ($cluster['id_myrep_cluster'] ?? 0)), ENT_QUOTES) ?>">
                                        <i class="fas fa-print mr-1"></i>Print Report
                                    </a>
                                </div>
                                <div class="impl-history-summary">
                                    <div class="impl-history-summary__card">
                                        <div class="impl-history-summary__label">Scope Aktif</div>
                                        <div class="impl-history-summary__value" id="history_scope_label">Cluster</div>
                                    </div>
                                    <div class="impl-history-summary__card">
                                        <div class="impl-history-summary__label">Total Plan</div>
                                        <div class="impl-history-summary__value" id="history_total_plan">-</div>
                                    </div>
                                    <div class="impl-history-summary__card">
                                        <div class="impl-history-summary__label">Total Achievement</div>
                                        <div class="impl-history-summary__value" id="history_total_achiev">-</div>
                                    </div>
                                    <div class="impl-history-summary__card">
                                        <div class="impl-history-summary__label">Selisih</div>
                                        <div class="impl-history-summary__value" id="history_total_gap">-</div>
                                    </div>
                                    <div class="impl-history-summary__card">
                                        <div class="impl-history-summary__label">Persentase</div>
                                        <div class="impl-history-summary__value" id="history_total_percent">-</div>
                                    </div>
                                </div>

                                <div class="font-weight-bold text-dark mb-2 px-3 pt-2 js-history-scope-title" data-scope-title="CLUSTER">Scope: Cluster</div>
                                <div class="table-responsive mb-4 js-history-scope-table" data-scope-table="CLUSTER" data-total-plan="<?= htmlspecialchars((string) round(array_sum((array) $historyTypePlanCluster)), ENT_QUOTES) ?>" data-total-achiev="<?= htmlspecialchars((string) round(array_sum((array) $historyFinalAchieveCluster)), ENT_QUOTES) ?>">
                                    <table class="table table-bordered impl-history-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">No</th>
                                                <th rowspan="2">HP DRM</th>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th colspan="2" data-item-group="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($itemType) ?>
                                                        <?php if (!empty($boqTypeBreakdown[$itemType])): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-link btn-sm p-0 ml-1 js-open-boq-breakdown"
                                                                data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>"
                                                                data-breakdown="<?= htmlspecialchars(json_encode((array) ($boqTypeBreakdown[$itemType] ?? [])), ENT_QUOTES) ?>">
                                                                Detail
                                                            </button>
                                                        <?php endif; ?>
                                                    </th>
                                                <?php endforeach; ?>
                                                <th rowspan="2">Tanggal Progress</th>
                                                <th rowspan="2">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>">PLAN</th>
                                                    <th data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>">ACHIEV</th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historyRowsCluster as $index => $historyRow): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= $index === 0 ? implHistoryNumber((float) ($cluster['homepass_drm'] ?? 0), false) : '-' ?></td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <?php
                                                        $planCluster = (float) ($historyTypePlanCluster[$itemType] ?? 0);
                                                        $achieveCluster = (float) ($historyRow['achieve'][$itemType] ?? 0);
                                                        ?>
                                                        <td data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>" data-role="plan"><?= $index === 0 ? implHistoryNumber($planCluster) : '-' ?></td>
                                                        <td data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>" data-role="achiev"><?= implHistoryNumber($achieveCluster) ?></td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <?php
                                                        $historyDateText = (string) ($historyRow['progress_date'] ?? '-');
                                                        $historyDailyActivities = $dailyActivitiesByDate[$historyDateText] ?? [];
                                                        ?>
                                                        <?php if (!empty($historyDailyActivities) && $historyDateText !== '-'): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-link p-0 align-baseline js-open-daily-detail"
                                                                data-daily-date="<?= htmlspecialchars($historyDateText, ENT_QUOTES) ?>"
                                                                data-daily-global-remark="<?= htmlspecialchars((string) ($historyRow['remark'] ?? '-'), ENT_QUOTES) ?>"
                                                                data-daily-activities="<?= htmlspecialchars(json_encode(array_values($historyDailyActivities)), ENT_QUOTES) ?>">
                                                                <?= htmlspecialchars($historyDateText) ?>
                                                            </button>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($historyDateText) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($historyRow['remark'] ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($historyRowsCluster)): ?>
                                                <tr><td colspan="<?= 4 + (count($historyTypeOrder) * 2) ?>" class="text-center text-muted">Belum ada history implementasi.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <?php if (!empty($historyTypeOrder)): ?>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">Total</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td><?= implHistoryNumber((float) ($historyTypePlanCluster[$itemType] ?? 0)) ?></td>
                                                        <td><?= implHistoryNumber((float) ($historyFinalAchieveCluster[$itemType] ?? 0)) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">Selisih</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td colspan="2"><?= implHistoryNumber((float) (($historyTypePlanCluster[$itemType] ?? 0) - ($historyFinalAchieveCluster[$itemType] ?? 0))) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>

                                <div class="font-weight-bold text-dark mb-2 px-3 pt-2 d-none js-history-scope-title" data-scope-title="SUBFEEDER">Scope: Subfeeder</div>
                                <div class="table-responsive d-none js-history-scope-table" data-scope-table="SUBFEEDER" data-total-plan="<?= htmlspecialchars((string) round(array_sum((array) $historyTypePlanSubfeeder)), ENT_QUOTES) ?>" data-total-achiev="<?= htmlspecialchars((string) round(array_sum((array) $historyFinalAchieveSubfeeder)), ENT_QUOTES) ?>">
                                    <?php if (!$hasSubfeederBoqPlan): ?>
                                        <div class="alert alert-info mb-0">
                                            Subfeeder belum proses upload DRM. BOQ Tracker Subfeeder akan tersedia setelah APD BOQ Subfeeder disubmit dan di-approve di DRM MyRep.
                                        </div>
                                    <?php else: ?>
                                    <table class="table table-bordered impl-history-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">No</th>
                                                <th rowspan="2">HP DRM</th>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th colspan="2" data-item-group="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>"><?= htmlspecialchars($itemType) ?></th>
                                                <?php endforeach; ?>
                                                <th rowspan="2">Tanggal Progress</th>
                                                <th rowspan="2">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>">PLAN</th>
                                                    <th data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>">ACHIEV</th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historyRowsSubfeeder as $index => $historyRow): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= $index === 0 ? implHistoryNumber((float) ($cluster['homepass_drm'] ?? 0), false) : '-' ?></td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <?php
                                                        $planSubfeeder = (float) ($historyTypePlanSubfeeder[$itemType] ?? 0);
                                                        $achieveSubfeeder = (float) ($historyRow['achieve'][$itemType] ?? 0);
                                                        ?>
                                                        <td data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>" data-role="plan"><?= $index === 0 ? implHistoryNumber($planSubfeeder) : '-' ?></td>
                                                        <td data-item-type="<?= htmlspecialchars($itemType, ENT_QUOTES) ?>" data-role="achiev"><?= implHistoryNumber($achieveSubfeeder) ?></td>
                                                    <?php endforeach; ?>
                                                    <td><?= htmlspecialchars((string) ($historyRow['progress_date'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($historyRow['remark'] ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($historyRowsSubfeeder)): ?>
                                                <tr><td colspan="<?= 4 + (count($historyTypeOrder) * 2) ?>" class="text-center text-muted">Belum ada history implementasi.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <?php if (!empty($historyTypeOrder)): ?>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">Total</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td><?= implHistoryNumber((float) ($historyTypePlanSubfeeder[$itemType] ?? 0)) ?></td>
                                                        <td><?= implHistoryNumber((float) ($historyFinalAchieveSubfeeder[$itemType] ?? 0)) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">Selisih</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td colspan="2"><?= implHistoryNumber((float) (($historyTypePlanSubfeeder[$itemType] ?? 0) - ($historyFinalAchieveSubfeeder[$itemType] ?? 0))) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="impl-breakdown-pane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                <div>
                                    <div class="font-weight-bold text-dark">Daily Progress Aktivitas</div>
                                    <div class="small text-muted">Input progress harian berbasis aktivitas. Foto dibedakan per scope CLUSTER/SUBFEEDER.</div>
                                </div>
                                <?php if ($canApprovalDailyAction): ?>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-daily-activity">
                                        <i class="fas fa-plus-circle mr-1"></i>Input Daily Progress
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive">
                                <?php
                                $dailyGrouped = [];
                                $historyRemarkByDate = [];
                                foreach ((array) $historyRows as $historyRowEntry) {
                                    $entryDate = (string) ($historyRowEntry['progress_date'] ?? '');
                                    if ($entryDate === '' || $entryDate === '-') {
                                        continue;
                                    }
                                    $entryRemark = trim((string) ($historyRowEntry['remark'] ?? ''));
                                    if ($entryRemark !== '' && strtoupper($entryRemark) !== 'BOQ AWAL') {
                                        $historyRemarkByDate[$entryDate] = $entryRemark;
                                    }
                                }

                                foreach ((array) $dailyActivities as $activity) {
                                    $groupDate = (string) ($activity['activity_date'] ?? '-');
                                    if (!isset($dailyGrouped[$groupDate])) {
                                        $dailyGrouped[$groupDate] = [
                                            'date' => $groupDate,
                                            'activities' => [],
                                            'total_qty' => 0,
                                            'photo_count' => 0,
                                            'team_count' => 0,
                                            'worker_count' => 0,
                                            'pic' => [],
                                            'scope' => [],
                                            'global_remark' => '',
                                        ];
                                    }

                                    $dailyGrouped[$groupDate]['activities'][] = $activity;
                                    $dailyGrouped[$groupDate]['total_qty'] += (float) ($activity['qty_activity'] ?? 0);
                                    $dailyGrouped[$groupDate]['photo_count'] += count((array) ($activity['photos'] ?? []));
                                    $dailyGrouped[$groupDate]['team_count'] = max($dailyGrouped[$groupDate]['team_count'], (int) ($activity['team_count'] ?? 0));
                                    $dailyGrouped[$groupDate]['worker_count'] = max($dailyGrouped[$groupDate]['worker_count'], (int) ($activity['worker_count'] ?? 0));

                                    $picName = trim((string) ($activity['nama_user'] ?? ''));
                                    if ($picName !== '') {
                                        $dailyGrouped[$groupDate]['pic'][$picName] = true;
                                    }

                                    $scopeType = strtoupper(trim((string) ($activity['scope_type'] ?? '')));
                                    if ($scopeType !== '') {
                                        $dailyGrouped[$groupDate]['scope'][$scopeType] = true;
                                    }
                                }
                                foreach ($dailyGrouped as $dateKey => $groupValue) {
                                    $dailyGrouped[$dateKey]['global_remark'] = (string) ($historyRemarkByDate[$dateKey] ?? '-');
                                }
                                krsort($dailyGrouped);
                                ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Total Aktivitas</th>
                                            <th>Team/Orang</th>
                                            <th>Total Qty</th>
                                            <th>Total Foto</th>
                                            <th>PIC</th>
                                            <th>Scope</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $dailyRowNo = 1; ?>
                                        <?php foreach ($dailyGrouped as $group): ?>
                                            <?php $dailyJson = htmlspecialchars(json_encode((array) ($group['activities'] ?? [])), ENT_QUOTES); ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($group['date'] ?? '-')) ?></td>
                                                <td><?= count((array) ($group['activities'] ?? [])) ?> aktivitas</td>
                                                <td><?= (int) ($group['team_count'] ?? 0) ?> Team / <?= (int) ($group['worker_count'] ?? 0) ?> Orang</td>
                                                <td><?= number_format((float) ($group['total_qty'] ?? 0), 0, ',', '.') ?></td>
                                                <td><?= (int) ($group['photo_count'] ?? 0) ?> foto</td>
                                                <td><?= !empty($group['pic']) ? htmlspecialchars(implode(', ', array_keys($group['pic']))) : '-' ?></td>
                                                <td><?= !empty($group['scope']) ? htmlspecialchars(implode(', ', array_keys($group['scope']))) : '-' ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-primary js-open-daily-detail mr-1" type="button" data-daily-date="<?= htmlspecialchars((string) ($group['date'] ?? '-'), ENT_QUOTES) ?>" data-daily-global-remark="<?= htmlspecialchars((string) ($group['global_remark'] ?? '-'), ENT_QUOTES) ?>" data-daily-activities="<?= $dailyJson ?>">
                                                        Lihat Detail
                                                    </button>
                                                    <?php if ($canHapus): ?>
                                                        <button class="btn btn-sm btn-outline-danger js-delete-daily-row" type="button" data-daily-date="<?= htmlspecialchars((string) ($group['date'] ?? '-'), ENT_QUOTES) ?>">
                                                            Hapus
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php $dailyRowNo++; ?>
                                        <?php endforeach; ?>
                                        <?php if (empty($dailyGrouped)): ?>
                                            <tr><td colspan="8" class="text-center text-muted">Belum ada progress harian aktivitas.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="impl-gallery-pane" role="tabpanel">
                            <?php if (!empty($implementationGalleryGroups)): ?>
                                <?php foreach ($implementationGalleryGroups as $galleryType => $galleryItems): ?>
                                    <div class="impl-gallery-section">
                                        <div class="impl-gallery-section__title">Kategori <?= htmlspecialchars((string) $galleryType) ?></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered impl-gallery-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:60px;">No</th>
                                                        <th style="width:120px;">Item</th>
                                                        <th>Dokumentasi</th>
                                                        <th style="width:220px;">Remarks</th>
                                                        <th style="width:130px;">Tgl Upload</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $galleryIndex = 1; ?>
                                                    <?php foreach ($galleryItems as $galleryItem): ?>
                                                        <?php
                                                        $dates = array_values(array_unique($galleryItem['dates']));
                                                        sort($dates);
                                                        $remarks = array_values(array_unique($galleryItem['remarks']));
                                                        ?>
                                                        <tr>
                                                            <td class="impl-gallery-table__no"><?= $galleryIndex++ ?></td>
                                                            <td class="impl-gallery-table__item">
                                                                <?= htmlspecialchars((string) ($galleryItem['item_name'] ?? '-')) ?>
                                                                <div class="small text-muted">
                                                                    <?= htmlspecialchars((string) (($galleryItem['photo_category'] ?? 'HARIAN') === 'COMPLY' ? 'Foto Comply' : 'Foto Implementasi Harian')) ?>
                                                                </div>
                                                            </td>
                                                            <td class="impl-gallery-table__photo">
                                                                <div class="impl-gallery-photo-grid" data-lightbox-group="gallery-<?= md5((string) (($galleryType ?? '') . '|' . ($galleryItem['item_name'] ?? '') . '|' . $galleryIndex)) ?>">
                                                                    <?php foreach (($galleryItem['photos'] ?? []) as $photo): ?>
                                                                        <a href="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'preview', (string) ($photo['file_path'] ?? '')) ?>" class="impl-gallery-photo-card js-open-lightbox" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-image="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'preview', (string) ($photo['file_path'] ?? '')) ?>" data-mime="<?= htmlspecialchars(implPhotoMimeFromPath((string) ($photo['file_path'] ?? '')), ENT_QUOTES) ?>" data-title="<?= htmlspecialchars((string) ($galleryItem['item_name'] ?? '-'), ENT_QUOTES) ?>" data-caption="<?= htmlspecialchars((string) (($photo['caption'] ?? '') !== '' ? $photo['caption'] : ($photo['file_name'] ?? 'Foto Progress')), ENT_QUOTES) ?>">
                                                                            <img src="<?= $implLazyPhotoPlaceholder ?>" data-src="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'thumb', (string) ($photo['file_path'] ?? '')) ?>" class="js-lazy-photo" alt="<?= htmlspecialchars((string) ($photo['file_name'] ?? 'Foto Progress')) ?>" loading="lazy" decoding="async">
                                                                        </a>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </td>
                                                            <td class="impl-gallery-table__remark">
                                                                <?= !empty($remarks) ? htmlspecialchars(implode(' | ', $remarks)) : '-' ?>
                                                            </td>
                                                            <td class="impl-gallery-table__date">
                                                                <?= !empty($dates) ? nl2br(htmlspecialchars(implode("\n", $dates))) : '-' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">Belum ada foto implementasi yang diupload.</div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane fade" id="impl-comply-pane" role="tabpanel">
                            <div class="card border-0 mb-3 impl-comply-upload-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                        <div>
                                            <div class="impl-comply-upload-card__title">Upload Foto Comply</div>
                                            <div class="impl-comply-upload-card__note">Input comply sekarang lewat modal agar fokus dan konsisten dengan daily progress.</div>
                                        </div>
                                        <div class="d-flex" style="gap:.5rem;">
                                            <?php if ($canTambah): ?>
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-comply-builder">
                                                    <i class="fas fa-plus-circle mr-1"></i>Input Foto Comply
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?= base_url('Implementasi_BOQ_MyRep/previewComplyPdf/' . (int) ($cluster['id_myrep_cluster'] ?? 0)) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                                <i class="fas fa-file-pdf mr-1"></i>Preview Daily & Comply
                                            </a>
                                        </div>
                                    </div>
                                    <div class="small text-muted">Klik tombol <strong>Input Foto Comply</strong> untuk mulai upload multi-entry.</div>
                                </div>
                            </div>

                            <?php if (!empty($complyGalleryGroups)): ?>
                                <div class="impl-gallery-filter" id="impl-comply-gallery-filter">
                                    <div class="impl-gallery-filter__group">
                                        <label for="impl-comply-category-filter">Kategori</label>
                                        <select class="form-control form-control-sm" id="impl-comply-category-filter">
                                            <option value="">Semua Kategori</option>
                                            <?php foreach (array_keys((array) $complyGalleryGroups) as $complyFilterType): ?>
                                                <option value="<?= htmlspecialchars(strtoupper(trim((string) $complyFilterType)), ENT_QUOTES) ?>"><?= htmlspecialchars((string) $complyFilterType) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="impl-gallery-filter__search">
                                        <label for="impl-comply-search-filter">Search Nama</label>
                                        <input type="search" class="form-control form-control-sm" id="impl-comply-search-filter" placeholder="Cari item, nama/nomor, remark, status...">
                                    </div>
                                </div>
                                <div class="impl-gallery-empty d-none" id="impl-comply-filter-empty">Tidak ada foto comply sesuai filter.</div>
                                <?php foreach ($complyGalleryGroups as $galleryType => $galleryItems): ?>
                                    <div class="impl-gallery-section js-comply-gallery-section" data-gallery-category="<?= htmlspecialchars(strtoupper(trim((string) $galleryType)), ENT_QUOTES) ?>">
                                        <div class="impl-gallery-section__title">Kategori <?= htmlspecialchars((string) $galleryType) ?></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered impl-gallery-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:60px;">No</th>
                                                        <th style="width:140px;">Item</th>
                                                        <th style="width:180px;">Nama / Nomor</th>
                                                        <th>Dokumentasi</th>
                                                        <th style="width:220px;">Remarks</th>
                                                        <th style="width:130px;">Tgl Upload</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $galleryIndex = 1; ?>
                                                    <?php foreach ($galleryItems as $galleryItem): ?>
                                                        <?php
                                                        $dates = array_values(array_unique($galleryItem['dates']));
                                                        sort($dates);
                                                        $remarks = array_values(array_unique($galleryItem['remarks']));
                                                        $searchText = implode(' ', [
                                                            (string) $galleryType,
                                                            (string) ($galleryItem['item_name'] ?? ''),
                                                            (string) ($galleryItem['comply_label'] ?? ''),
                                                            implode(' ', $remarks),
                                                            implode(' ', $dates),
                                                        ]);
                                                        ?>
                                                        <tr class="js-comply-gallery-row" data-search="<?= htmlspecialchars(strtolower($searchText), ENT_QUOTES) ?>">
                                                            <td class="impl-gallery-table__no js-comply-gallery-no"><?= $galleryIndex++ ?></td>
                                                            <td class="impl-gallery-table__item"><?= htmlspecialchars((string) ($galleryItem['item_name'] ?? '-')) ?></td>
                                                            <td class="impl-gallery-table__item"><?= htmlspecialchars((string) ($galleryItem['comply_label'] ?? '-')) ?></td>
                                                            <td class="impl-gallery-table__photo">
                                                                <div class="impl-gallery-photo-grid" data-lightbox-group="comply-gallery-<?= md5((string) (($galleryType ?? '') . '|' . ($galleryItem['item_name'] ?? '') . '|' . ($galleryItem['comply_label'] ?? '') . '|' . $galleryIndex)) ?>">
                                                                    <?php foreach (($galleryItem['photos'] ?? []) as $photo): ?>
                                                                        <?php
                                                                        $photoStatus = strtoupper(trim((string) ($photo['status_photo'] ?? 'UPLOADED')));
                                                                        $photoBadgeClass = $photoStatus === 'APPROVED' ? 'success' : ($photoStatus === 'REJECTED' ? 'danger' : 'warning');
                                                                        $photoCaption = (string) (($photo['caption'] ?? '') !== '' ? $photo['caption'] : ($photo['file_name'] ?? 'Foto Comply'));
                                                                        $photoLabelForAction = (string) (($galleryItem['item_name'] ?? '-') . ' - ' . ($galleryItem['comply_label'] ?? '-') . ' - ' . $photoCaption);
                                                                        $canDeleteThisPhoto = (int) ($photo['uploaded_by'] ?? 0) === $currentUserId;
                                                                        ?>
                                                                        <div class="impl-gallery-photo-card--shell js-comply-photo-review-card" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>" data-can-delete-photo="<?= $canDeleteThisPhoto ? '1' : '0' ?>">
                                                                            <a href="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'preview', (string) ($photo['file_path'] ?? '')) ?>" class="impl-gallery-photo-card js-open-lightbox" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-image="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'preview', (string) ($photo['file_path'] ?? '')) ?>" data-mime="<?= htmlspecialchars(implPhotoMimeFromPath((string) ($photo['file_path'] ?? '')), ENT_QUOTES) ?>" data-title="<?= htmlspecialchars((string) (($galleryItem['item_name'] ?? '-') . ' - ' . ($galleryItem['comply_label'] ?? '-')), ENT_QUOTES) ?>" data-caption="<?= htmlspecialchars($photoCaption, ENT_QUOTES) ?>">
                                                                                <img src="<?= $implLazyPhotoPlaceholder ?>" data-src="<?= implProgressPhotoPreviewUrl((int) ($photo['id_progress_photo'] ?? 0), 'thumb', (string) ($photo['file_path'] ?? '')) ?>" class="js-lazy-photo" alt="<?= htmlspecialchars((string) ($photo['file_name'] ?? 'Foto Comply')) ?>" loading="lazy" decoding="async">
                                                                            </a>
                                                                            <div class="impl-gallery-photo-card__meta">
                                                                                <div class="small font-weight-bold text-dark mb-2"><?= htmlspecialchars($photoCaption) ?></div>
                                                                                <div class="mb-2">
                                                                                    <span class="badge badge-<?= $photoBadgeClass ?> js-comply-photo-status"><?= htmlspecialchars($photoStatus) ?></span>
                                                                                </div>
                                                                                <div class="small text-muted mb-2 js-comply-review-remark <?= empty($photo['review_remark']) ? 'd-none' : '' ?>">Review: <span><?= htmlspecialchars((string) ($photo['review_remark'] ?? '')) ?></span></div>
                                                                                <?php if ((!empty($canApprove) && $canApprovalComplyAction) || $canDeleteThisPhoto): ?>
                                                                                    <div class="js-comply-photo-actions">
                                                                                        <div class="dropdown impl-photo-action-menu">
                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menu aksi foto">&#8942;</button>
                                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                                                <?php if (!empty($canApprove) && $canApprovalComplyAction && ($photoStatus === 'UPLOADED' || $photoStatus === 'REJECTED')): ?>
                                                                                                    <button type="button" class="dropdown-item text-success js-open-comply-approve" data-toggle="modal" data-target="#modal-comply-approve" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>">Approve</button>
                                                                                                <?php endif; ?>
                                                                                                <?php if (!empty($canApprove) && $canApprovalComplyAction && ($photoStatus === 'UPLOADED' || $photoStatus === 'APPROVED')): ?>
                                                                                                    <button type="button" class="dropdown-item text-danger js-open-comply-reject" data-toggle="modal" data-target="#modal-comply-reject" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>">Reject</button>
                                                                                                <?php endif; ?>
                                                                                                <?php if ($canDeleteThisPhoto): ?>
                                                                                                    <?php if (!empty($canApprove) && $canApprovalComplyAction): ?>
                                                                                                        <div class="dropdown-divider"></div>
                                                                                                    <?php endif; ?>
                                                                                                    <button type="button" class="dropdown-item text-danger js-delete-progress-photo" data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>" data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>">Hapus</button>
                                                                                                <?php endif; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </td>
                                                            <td class="impl-gallery-table__remark">
                                                                <?= !empty($remarks) ? htmlspecialchars(implode(' | ', $remarks)) : '-' ?>
                                                            </td>
                                                            <td class="impl-gallery-table__date">
                                                                <?= !empty($dates) ? nl2br(htmlspecialchars(implode("\n", $dates))) : '-' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">Belum ada foto comply yang diupload.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php endif; ?>

<?php if ($canTambah): ?>
<div class="modal fade" id="modal-progress" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/saveProgress') ?>" enctype="multipart/form-data" id="form-bulk-progress">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Input Progress Implementasi Sekaligus</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cluster</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Progress</label>
                                <input type="date" name="progress_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status Progress</label>
                                <select name="status_progress" class="form-control">
                                    <option value="ON PROGRESS">ON PROGRESS</option>
                                    <option value="DONE">DONE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Remark Progress</label>
                        <textarea name="remark_progress" class="form-control" rows="3" placeholder="Catatan progress harian untuk seluruh input pada tanggal ini"></textarea>
                    </div>
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-9">
                                    <div class="form-group mb-md-0">
                                        <label>Pilih Item Breakdown</label>
                                        <select class="form-control js-progress-item-selector">
                                            <option value="">Pilih item implementasi</option>
                                            <?php foreach ($compareRows as $row): ?>
                                                <option
                                                    value="<?= (int) $row['id_boq_baseline_item'] ?>"
                                                    data-item-name="<?= htmlspecialchars((string) ($row['item_name'] ?? '-'), ENT_QUOTES) ?>"
                                                    data-item-type="<?= htmlspecialchars((string) ($row['item_type'] ?? '-'), ENT_QUOTES) ?>"
                                                    data-qty-target="<?= htmlspecialchars((string) implHistoryNumber((float) ($row['qty_boq'] ?? 0)), ENT_QUOTES) ?>"
                                                    data-photo-target="<?= (int) ($row['target_foto_required'] ?? 0) ?>"
                                                >
                                                    <?= htmlspecialchars((string) ($row['item_name'] ?? '-')) ?><?= !empty($row['item_type']) ? ' - ' . htmlspecialchars((string) ($row['item_type'] ?? '-')) : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-right">
                                    <button type="button" class="btn btn-outline-primary btn-block js-add-progress-item">
                                        <i class="fas fa-plus-circle mr-1"></i>Tambah Item
                                    </button>
                                </div>
                            </div>
                            <div class="alert alert-light border mt-3 mb-0 js-progress-empty-state">Belum ada item dipilih. Tambahkan item seperti Tiang, FAT, ODC, atau item lainnya lalu isi qty dan foto implementasi harian masing-masing.</div>
                        </div>
                    </div>
                    <div class="js-progress-item-list"></div>
                    <template id="progress-item-card-template">
                        <div class="card border-0 shadow-sm mb-3 js-progress-item-card" data-baseline-item-id="">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <div class="font-weight-bold text-dark js-progress-item-title"></div>
                                        <div class="small text-muted js-progress-item-meta"></div>
                                    </div>
                                    <button type="button" class="btn btn-link text-danger p-0 js-remove-progress-item">Hapus</button>
                                </div>
                                <input type="hidden" class="js-progress-item-id-input" value="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="js-progress-qty-label">Qty Progress</label>
                                            <input type="number" step="0.01" min="0.01" class="form-control js-progress-qty-input" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Target Qty BOQ</label>
                                            <input type="text" class="form-control js-progress-target-qty" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Target Foto Harian</label>
                                            <input type="text" class="form-control js-progress-target-photo" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="impl-dropzone js-dropzone">
                                    <input type="file" class="js-dropzone-input js-progress-photo-input" multiple accept=".jpg,.jpeg,.png,.webp">
                                    <div class="impl-dropzone-content">
                                        <div class="mb-2"><i class="fas fa-images fa-2x text-success"></i></div>
                                        <div class="font-weight-bold js-progress-photo-title">Upload foto implementasi harian</div>
                                        <div class="text-muted small">Atau klik area ini untuk memilih beberapa foto</div>
                                        <div class="impl-dropzone-file js-dropzone-label">Belum ada foto dipilih</div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    Foto comply tidak diinput dari form progress ini. Jika item membutuhkan comply, upload terpisah melalui tab <strong>FOTO COMPLY</strong>.
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan dan Submit Sekaligus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-history" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true" data-keyboard="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">History Progress Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><strong>Item:</strong> <span id="history_item_name">-</span></div>
                <div id="history_item_rows" class="text-muted">Belum ada history.</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade impl-daily-detail-modal" id="modal-daily-detail" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detail Daily Progress - <span id="daily_detail_date">-</span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="daily_detail_rows" class="text-muted">Belum ada detail.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade impl-daily-detail-modal" id="modal-boq-breakdown" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true" data-keyboard="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Breakdown BOQ - <span id="boq_breakdown_type">-</span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="boq_breakdown_rows" class="text-muted">Belum ada data breakdown.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canTambah): ?>
<div class="modal fade impl-daily-modal" id="modal-comply-builder" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/uploadComplyPhoto') ?>" enctype="multipart/form-data" id="form-comply-builder">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title mb-0">Input Foto Comply</h5>
                        <div class="impl-daily-modal__sub">Builder upload comply multi entry dalam satu submit.</div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="impl-daily-shell">
                        <div class="impl-daily-shell__title">Detail Cluster</div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="small text-muted mb-1">Cluster</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Regional</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Kota</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Status</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['implementation_status'] ?? '-')) ?>" readonly>
                            </div>
                        </div>

                        <div class="impl-daily-shell__title">Builder Comply</div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Scope</label>
                                <select class="form-control form-control-sm js-comply-scope">
                                    <option value="CLUSTER">CLUSTER</option>
                                    <option value="SUBFEEDER">SUBFEEDER</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Item</label>
                                <select class="form-control form-control-sm js-comply-category">
                                    <option value="">Pilih Item</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Jenis</label>
                                <select class="form-control form-control-sm js-comply-kind">
                                    <option value="">Pilih Jenis</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Nama / Nomor</label>
                                <input type="text" class="form-control form-control-sm js-comply-label-builder" placeholder="Contoh: FAT 01">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-block js-add-comply-row"><i class="fas fa-plus mr-1"></i>Tambah</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted">Jika jenis wajib 2 foto, form remarks otomatis diberi 2 baris: FAT terbuka & FAT tertutup.</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width:110px;">Scope</th>
                                        <th style="width:140px;">Item</th>
                                        <th style="width:240px;">Jenis</th>
                                        <th style="width:180px;">Nama / Nomor</th>
                                        <th style="width:200px;">Keterangan</th>
                                        <th>Foto</th>
                                        <th style="width:220px;">Remarks Per Foto</th>
                                        <th style="width:70px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="js-comply-rows"></tbody>
                            </table>
                        </div>
                        <div class="small text-muted">Remarks per foto: 1 baris untuk 1 file, urut sesuai urutan file dipilih.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm">Upload Foto Comply</button>
                </div>
            </form>
            <template id="comply-row-template">
                <tr class="js-comply-row">
                    <td>
                        <input type="hidden" class="js-comply-item-id" required>
                        <input type="hidden" class="js-comply-scope-hidden">
                        <input type="text" class="form-control form-control-sm js-comply-scope-text" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm js-comply-category-text" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm js-comply-kind-text" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm js-comply-label" placeholder="Nama / nomor comply" required>
                    </td>
                    <td>
                        <div class="small text-muted js-comply-short-note">-</div>
                    </td>
                    <td>
                        <input type="file" class="form-control-file js-comply-photos mb-2" multiple accept=".jpg,.jpeg,.png,.webp" required>
                        <div class="small text-muted js-comply-file-note">Belum ada foto dipilih</div>
                        <div class="d-flex flex-wrap js-comply-photo-preview" style="gap:.35rem;"></div>
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm js-comply-remarks" rows="4" placeholder="1 baris remark untuk 1 foto" required></textarea>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-comply-row">Hapus</button>
                    </td>
                </tr>
            </template>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($canApprove) && $canApprovalComplyAction): ?>
    <div class="modal fade" id="modal-comply-approve" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/approveComplyPhoto') ?>" class="js-comply-review-form" data-review-status="APPROVED">
                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                    <input type="hidden" name="photo_id" id="comply_approve_photo_id">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Approve Foto Comply</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><strong>Foto:</strong> <span id="comply_approve_photo_label">-</span></div>
                        <div class="form-group mb-0">
                            <label>Remark Approve</label>
                            <textarea name="review_remark" class="form-control" rows="3" placeholder="Remark approve jika diperlukan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm">Approve Foto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-comply-reject" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/rejectComplyPhoto') ?>" class="js-comply-review-form" data-review-status="REJECTED">
                    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                    <input type="hidden" name="photo_id" id="comply_reject_photo_id">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Foto Comply</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><strong>Foto:</strong> <span id="comply_reject_photo_label">-</span></div>
                        <div class="form-group mb-0">
                            <label>Alasan Reject</label>
                            <textarea name="review_remark" class="form-control" rows="3" required placeholder="Wajib diisi"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger btn-sm">Reject Foto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($canHapus): ?>
<form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/deleteProgress') ?>" id="form-delete-progress" class="d-none">
    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
    <input type="hidden" name="progress_item_id" id="delete_progress_item_id" value="">
</form>

<form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/deleteDailyActivity') ?>" id="form-delete-daily-activity" class="d-none">
    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
    <input type="hidden" name="activity_date" id="delete_daily_activity_date" value="">
</form>
<?php endif; ?>

<div class="impl-lightbox" id="impl-lightbox" aria-hidden="true">
    <div class="impl-lightbox__dialog">
        <div class="impl-lightbox__head">
            <div class="impl-lightbox__title" id="impl-lightbox-title">Preview Foto</div>
            <div class="impl-lightbox__toolbar">
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-prev" aria-label="Sebelumnya">&#8249;</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-zoom-out" aria-label="Zoom Out">-</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-zoom-in" aria-label="Zoom In">+</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-rotate-left" aria-label="Rotate Kiri">&#8634;</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-rotate-right" aria-label="Rotate Kanan">&#8635;</button>
                <?php if ($canSavePhotoRotation): ?>
                    <button type="button" class="impl-lightbox__action impl-lightbox__save" id="impl-lightbox-save-rotation" aria-label="Simpan Rotasi" disabled>Simpan</button>
                <?php endif; ?>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-next" aria-label="Berikutnya">&#8250;</button>
                <button type="button" class="impl-lightbox__close" id="impl-lightbox-close" aria-label="Tutup">&times;</button>
            </div>
        </div>
        <div class="impl-lightbox__body">
            <div class="impl-lightbox__stage">
                <img src="" alt="Preview Foto" class="impl-lightbox__image" id="impl-lightbox-image" decoding="async">
            </div>
            <div class="impl-lightbox__caption" id="impl-lightbox-caption">-</div>
        </div>
    </div>
</div>

<script>
    (function () {
        var canApproveComplyPhoto = <?= (!empty($canApprove) && $canApprovalComplyAction) ? 'true' : 'false' ?>;
        var canSavePhotoRotation = <?= $canSavePhotoRotation ? 'true' : 'false' ?>;
        var clusterId = <?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>;
        var rotatePhotoUrl = '<?= base_url('Implementasi_BOQ_MyRep/rotateProgressPhoto') ?>';
        var deletePhotoUrl = '<?= base_url('Implementasi_BOQ_MyRep/deleteProgressPhoto') ?>';
        var photoPreviewBaseUrl = '<?= base_url('Implementasi_BOQ_MyRep/progressPhotoPreview') ?>';
        var lazyPhotoPlaceholder = '<?= $implLazyPhotoPlaceholder ?>';
        var progressModal = document.getElementById('modal-progress');
        var progressSelector = document.querySelector('.js-progress-item-selector');
        var progressAddButton = document.querySelector('.js-add-progress-item');
        var progressList = document.querySelector('.js-progress-item-list');
        var progressEmptyState = document.querySelector('.js-progress-empty-state');
        var progressCardTemplate = document.getElementById('progress-item-card-template');
        var deleteProgressForm = document.getElementById('form-delete-progress');
        var deleteProgressInput = document.getElementById('delete_progress_item_id');
        var deleteDailyActivityForm = document.getElementById('form-delete-daily-activity');
        var deleteDailyActivityInput = document.getElementById('delete_daily_activity_date');
        var complyForm = document.getElementById('form-comply-builder');
        var complyRowsBody = complyForm ? complyForm.querySelector('.js-comply-rows') : null;
        var complyAddButton = complyForm ? complyForm.querySelector('.js-add-comply-row') : null;
        var complyRowTemplate = document.getElementById('comply-row-template');
        var complyScopeSelect = complyForm ? complyForm.querySelector('.js-comply-scope') : null;
        var complyCategorySelect = complyForm ? complyForm.querySelector('.js-comply-category') : null;
        var complyKindSelect = complyForm ? complyForm.querySelector('.js-comply-kind') : null;
        var complyLabelBuilder = complyForm ? complyForm.querySelector('.js-comply-label-builder') : null;
        var complyOptionSource = <?= json_encode(array_map(static function ($row) {
            return [
                'id' => (int) ($row['id_boq_baseline_item'] ?? 0),
                'item_name' => (string) ($row['item_name'] ?? '-'),
                'item_type' => strtoupper(trim((string) ($row['item_type'] ?? '-'))),
                'photo_per_label' => (int) ($row['comply_photo_per_label'] ?? 1),
                'placeholder' => (string) ($row['comply_label_placeholder'] ?? 'Nama / nomor item comply'),
                'requirement' => (string) ($row['comply_requirement_text'] ?? ''),
            ];
        }, (array) $complyBuilderItems)) ?>;
        complyOptionSource = buildComplyOptionSource(complyOptionSource);
        var complyAutoLabelCounters = <?= json_encode($poleExtLabelCounters) ?>;

        function bindDropzones() {
            var dropzones = document.querySelectorAll('.js-dropzone');
            Array.prototype.forEach.call(dropzones, function (dropzone) {
                if (dropzone.dataset.bound === '1') {
                    return;
                }

                var input = dropzone.querySelector('.js-dropzone-input');
                var label = dropzone.querySelector('.js-dropzone-label');
                if (!input || !label) {
                    return;
                }

                dropzone.dataset.bound = '1';

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        dropzone.classList.add('dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        dropzone.classList.remove('dragover');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                        input.files = event.dataTransfer.files;
                        label.textContent = event.dataTransfer.files.length + ' foto dipilih';
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = input.files && input.files.length > 0
                        ? input.files.length + ' foto dipilih'
                        : 'Belum ada foto dipilih';
                });
            });
        }

        function buildShortRequirement(itemData) {
            if (itemData && itemData.short_requirement) {
                return itemData.short_requirement;
            }

            var requirement = String((itemData && itemData.requirement) || '').toUpperCase();
            if ((itemData && itemData.photo_per_label) === 2 && (String(itemData.item_type || '').indexOf('FAT') !== -1 || String(itemData.item_type || '').indexOf('FDT') !== -1)) {
                return 'Wajib 2 foto: FAT/FDT terbuka & tertutup';
            }
            if (requirement.indexOf('FULL LABEL') !== -1) {
                return 'Wajib full label';
            }
            if ((itemData && itemData.photo_per_label) > 1) {
                return 'Wajib ' + itemData.photo_per_label + ' foto per entry';
            }
            return 'Wajib mengikuti aturan item';
        }

        function buildComplyOptionSource(source) {
            var items = Array.isArray(source) ? source.slice() : [];
            items.forEach(function (item) {
                item.option_id = 'base:' + item.id;
                item.display_name = item.item_name || '-';
            });

            var tiangSource = null;
            items.forEach(function (item) {
                var type = String(item.item_type || '').toUpperCase();
                var name = String(item.item_name || '').toUpperCase();
                if (type !== 'TIANG') {
                    return;
                }
                if (!tiangSource || name.indexOf('EKSISTING') !== -1 || name.indexOf('EXISTING') !== -1) {
                    tiangSource = item;
                }
            });

            if (tiangSource) {
                var synthetic = {};
                Object.keys(tiangSource).forEach(function (key) {
                    synthetic[key] = tiangSource[key];
                });
                synthetic.option_id = 'tiang-eksisting:' + tiangSource.id;
                synthetic.item_type = 'TIANG';
                synthetic.display_name = 'Eksisting';
                synthetic.default_label_prefix = 'POLE EXT';
                synthetic.default_remark_prefix = 'POLE EXT';
                synthetic.short_requirement = 'Wajib 1 foto POLE EXT per entry';
                synthetic.requirement = 'Foto comply pole existing cluster / subfeeder.';
                synthetic.photo_per_label = 1;
                items.push(synthetic);
            }

            return items;
        }

        function populateComplyCategory() {
            if (!complyCategorySelect) return;
            var map = {};
            complyOptionSource.forEach(function (item) {
                var type = String(item.item_type || 'LAINNYA').toUpperCase();
                map[type] = true;
            });
            var keys = Object.keys(map).sort();
            complyCategorySelect.innerHTML = '<option value="">Pilih Item</option>';
            keys.forEach(function (key) {
                complyCategorySelect.innerHTML += '<option value="' + key + '">' + key + '</option>';
            });
        }

        function populateComplyKind() {
            if (!complyKindSelect || !complyCategorySelect) return;
            var category = complyCategorySelect.value;
            complyKindSelect.innerHTML = '<option value="">Pilih Jenis</option>';
            complyOptionSource.forEach(function (item) {
                if (category && String(item.item_type).toUpperCase() !== category.toUpperCase()) return;
                complyKindSelect.innerHTML += '<option value="' + escapeAttr(item.option_id || item.id) + '">' + escapeAttr(item.display_name || item.item_name || '-') + '</option>';
            });
            syncComplyBuilderDefaults();
        }

        function findComplyItemById(id) {
            var targetOption = String(id || '');
            var target = parseInt(targetOption.replace(/^.*:/, '') || 0, 10);
            for (var i = 0; i < complyOptionSource.length; i++) {
                if (String(complyOptionSource[i].option_id || '') === targetOption) return complyOptionSource[i];
            }
            for (var j = 0; j < complyOptionSource.length; j++) {
                if (parseInt(complyOptionSource[j].id, 10) === target) return complyOptionSource[j];
            }
            return null;
        }

        function syncComplyBuilderDefaults() {
            if (!complyKindSelect || !complyLabelBuilder) {
                return;
            }

            var itemData = findComplyItemById(complyKindSelect.value || '');
            if (!itemData) {
                complyLabelBuilder.placeholder = 'Contoh: FAT 01';
                return;
            }

            if (itemData.default_label_prefix) {
                complyLabelBuilder.value = buildNextAutoLabel(itemData.default_label_prefix, complyScopeSelect ? complyScopeSelect.value : 'CLUSTER');
            } else if (complyLabelBuilder.value.indexOf('POLE EXT') === 0) {
                complyLabelBuilder.value = '';
            }
            complyLabelBuilder.placeholder = itemData.placeholder || 'Nama / nomor comply';
        }

        function formatComplySequenceNumber(value) {
            var number = parseInt(value || 0, 10) || 0;
            return String(number).padStart(3, '0');
        }

        function buildNextAutoLabel(prefix, scopeValue) {
            return prefix + ' ' + formatComplySequenceNumber(getNextAutoLabelStart(null, scopeValue));
        }

        function getAutoRemarkRowFileCount(row) {
            var photoInput = row ? row.querySelector('.js-comply-photos') : null;
            if (!photoInput || !photoInput.files || photoInput.files.length <= 0) {
                return 1;
            }

            return photoInput.files.length;
        }

        function buildScopedComplyRemarks(prefix, scopeValue, count) {
            var lines = [];
            var scopeLabel = scopeValue === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            var total = Math.max(parseInt(count || 0, 10) || 0, 1);
            for (var i = 0; i < total; i++) {
                lines.push(prefix + ' ' + scopeLabel);
            }

            return lines.join('\n');
        }

        function getNextAutoLabelStart(row, scopeValue) {
            var scopeLabel = scopeValue === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            var nextNumber = (parseInt(complyAutoLabelCounters[scopeLabel] || 0, 10) || 0) + 1;
            if (!complyRowsBody) {
                return nextNumber;
            }

            var rows = Array.prototype.slice.call(complyRowsBody.querySelectorAll('.js-comply-row'));
            for (var i = 0; i < rows.length; i++) {
                var currentRow = rows[i];
                if (currentRow === row) {
                    break;
                }
                if ((currentRow.getAttribute('data-auto-label-prefix') || '') === '' || (currentRow.getAttribute('data-auto-label-scope') || '') !== scopeLabel) {
                    continue;
                }
                nextNumber++;
            }

            return nextNumber;
        }

        function syncAutoNumberedLabel(row) {
            if (!row) {
                return;
            }

            var prefix = row.getAttribute('data-auto-label-prefix') || '';
            var scopeValue = row.getAttribute('data-auto-label-scope') || 'CLUSTER';
            var labelInput = row.querySelector('.js-comply-label');
            if (!prefix || !labelInput) {
                return;
            }

            labelInput.value = prefix + ' ' + formatComplySequenceNumber(getNextAutoLabelStart(row, scopeValue));
        }

        function syncAutoScopedRemarks(row) {
            if (!row) {
                return;
            }

            var prefix = row.getAttribute('data-auto-remark-prefix') || '';
            var scopeValue = row.getAttribute('data-auto-remark-scope') || 'CLUSTER';
            var remarksInput = row.querySelector('.js-comply-remarks');
            if (!prefix || !remarksInput) {
                return;
            }

            remarksInput.value = buildScopedComplyRemarks(prefix, scopeValue, getAutoRemarkRowFileCount(row));
        }

        function syncAllAutoComplyFields() {
            if (!complyRowsBody) {
                return;
            }

            Array.prototype.forEach.call(complyRowsBody.querySelectorAll('.js-comply-row'), function (row) {
                syncAutoNumberedLabel(row);
                syncAutoScopedRemarks(row);
            });
        }

        function buildDefaultComplyRemarks(itemData, scopeValue, row) {
            if (!itemData) {
                return '';
            }

            if (itemData.default_remark_prefix) {
                return buildScopedComplyRemarks(
                    itemData.default_remark_prefix,
                    scopeValue,
                    getAutoRemarkRowFileCount(row)
                );
            }

            if ((itemData.photo_per_label || 1) === 2) {
                return 'FAT terbuka\nFAT tertutup';
            }

            return '';
        }

        function previewComplyPhotos(row) {
            if (!row) {
                return;
            }
            var photoInput = row.querySelector('.js-comply-photos');
            var note = row.querySelector('.js-comply-file-note');
            var preview = row.querySelector('.js-comply-photo-preview');
            if (!photoInput || !note || !preview) {
                return;
            }
            Array.prototype.forEach.call(preview.querySelectorAll('img[data-object-url]'), function (img) {
                if (window.URL && typeof window.URL.revokeObjectURL === 'function') {
                    window.URL.revokeObjectURL(img.getAttribute('data-object-url'));
                }
            });
            preview.innerHTML = '';
            var files = photoInput.files ? Array.prototype.slice.call(photoInput.files) : [];
            if (!files.length) {
                note.textContent = 'Belum ada foto dipilih';
                syncAutoScopedRemarks(row);
                return;
            }
            note.textContent = files.length + ' foto dipilih';
            files.slice(0, 12).forEach(function (file) {
                var card = document.createElement('div');
                card.style.width = '72px';
                var img = document.createElement('img');
                img.style.width = '72px';
                img.style.height = '52px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                img.style.border = '1px solid #dbe3ef';
                card.appendChild(img);
                preview.appendChild(card);
                if (window.URL && typeof window.URL.createObjectURL === 'function') {
                    var objectUrl = window.URL.createObjectURL(file);
                    img.setAttribute('data-object-url', objectUrl);
                    img.onload = function () {
                        if (img.getAttribute('data-object-url')) {
                            window.URL.revokeObjectURL(objectUrl);
                            img.removeAttribute('data-object-url');
                        }
                    };
                    img.src = objectUrl;
                } else {
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        img.src = event.target && event.target.result ? event.target.result : '';
                    };
                    reader.readAsDataURL(file);
                }
            });
            if (files.length > 12) {
                var more = document.createElement('div');
                more.style.width = '72px';
                more.style.minHeight = '52px';
                more.style.border = '1px dashed #cbd5e1';
                more.style.borderRadius = '6px';
                more.style.display = 'flex';
                more.style.alignItems = 'center';
                more.style.justifyContent = 'center';
                more.style.textAlign = 'center';
                more.style.fontSize = '.68rem';
                more.style.color = '#64748b';
                more.textContent = '+' + (files.length - 12) + ' foto';
                preview.appendChild(more);
            }
            syncAllAutoComplyFields();
        }

        function reindexComplyRows() {
            if (!complyRowsBody) {
                return;
            }
            var rows = complyRowsBody.querySelectorAll('.js-comply-row');
            Array.prototype.forEach.call(rows, function (row, index) {
                var itemInput = row.querySelector('.js-comply-item-id');
                var scopeInput = row.querySelector('.js-comply-scope-hidden');
                var labelInput = row.querySelector('.js-comply-label');
                var photoInput = row.querySelector('.js-comply-photos');
                var remarksInput = row.querySelector('.js-comply-remarks');
                if (itemInput) itemInput.name = 'comply_entries[' + index + '][baseline_item_id]';
                if (scopeInput) scopeInput.name = 'comply_entries[' + index + '][scope_type]';
                if (labelInput) labelInput.name = 'comply_entries[' + index + '][comply_label]';
                if (remarksInput) remarksInput.name = 'comply_entries[' + index + '][comply_photo_remarks]';
                if (photoInput) photoInput.name = 'comply_entry_photos_' + index + '[]';
            });
        }

        function addComplyRow() {
            if (!complyRowsBody || !complyRowTemplate || !complyKindSelect) return;
            var itemId = complyKindSelect.value || '';
            var itemData = findComplyItemById(itemId);
            if (!itemData) {
                alert('Pilih jenis comply terlebih dahulu.');
                return;
            }
            var labelText = (complyLabelBuilder && complyLabelBuilder.value ? complyLabelBuilder.value : '').trim();
            if (!labelText) {
                alert('Isi nama / nomor terlebih dahulu.');
                return;
            }

            var fragment = complyRowTemplate.content.cloneNode(true);
            var row = fragment.querySelector('.js-comply-row');
            row.querySelector('.js-comply-item-id').value = String(itemData.id);
            var scopeValue = complyScopeSelect ? complyScopeSelect.value : 'CLUSTER';
            row.querySelector('.js-comply-scope-text').value = scopeValue;
            row.querySelector('.js-comply-scope-hidden').value = scopeValue;
            row.querySelector('.js-comply-category-text').value = (complyCategorySelect ? complyCategorySelect.value : itemData.item_type) || '-';
            row.querySelector('.js-comply-kind-text').value = itemData.display_name || itemData.item_name || '-';
            row.querySelector('.js-comply-label').value = labelText;
            row.querySelector('.js-comply-label').placeholder = itemData.placeholder || 'Nama / nomor comply';
            row.querySelector('.js-comply-short-note').textContent = buildShortRequirement(itemData);
            if (itemData.default_label_prefix) {
                row.setAttribute('data-auto-label-prefix', itemData.default_label_prefix);
                row.setAttribute('data-auto-label-scope', scopeValue === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER');
            }
            if (itemData.default_remark_prefix) {
                row.setAttribute('data-auto-remark-prefix', itemData.default_remark_prefix);
                row.setAttribute('data-auto-remark-scope', scopeValue === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER');
            }

            row.querySelector('.js-comply-remarks').value = buildDefaultComplyRemarks(itemData, scopeValue, row);

            complyRowsBody.appendChild(fragment);
            reindexComplyRows();
            syncAllAutoComplyFields();
            if (complyLabelBuilder) {
                complyLabelBuilder.value = itemData.default_label_prefix
                    ? buildNextAutoLabel(itemData.default_label_prefix, scopeValue)
                    : '';
            }
        }

        function toggleProgressEmptyState() {
            if (!progressEmptyState || !progressList) {
                return;
            }

            progressEmptyState.style.display = progressList.querySelectorAll('.js-progress-item-card').length > 0 ? 'none' : '';
        }

        function addProgressItemCard(option) {
            if (!progressCardTemplate || !progressList || !option || !option.value) {
                return;
            }

            var baselineItemId = option.value;
            if (progressList.querySelector('.js-progress-item-card[data-baseline-item-id="' + baselineItemId + '"]')) {
                alert('Item ini sudah ada di daftar input.');
                return;
            }

            var card = progressCardTemplate.content.firstElementChild.cloneNode(true);
            var itemName = option.getAttribute('data-item-name') || option.textContent.trim();
            var itemType = option.getAttribute('data-item-type') || '-';
            var qtyTarget = option.getAttribute('data-qty-target') || '0';
            var photoTarget = option.getAttribute('data-photo-target') || '0';
            card.setAttribute('data-baseline-item-id', baselineItemId);
            card.querySelector('.js-progress-item-title').textContent = itemName;
            card.querySelector('.js-progress-item-meta').textContent = itemType !== '-' ? itemType : 'Jenis item belum tersedia';
            card.querySelector('.js-progress-qty-label').textContent = 'Qty ' + itemName;
            card.querySelector('.js-progress-target-qty').value = qtyTarget;
            card.querySelector('.js-progress-target-photo').value = photoTarget + ' foto';
            card.querySelector('.js-progress-photo-title').textContent = 'Upload foto implementasi harian ' + itemName;

            var hiddenInput = card.querySelector('.js-progress-item-id-input');
            hiddenInput.name = 'progress_items[' + baselineItemId + '][id_boq_baseline_item]';
            hiddenInput.value = baselineItemId;

            var qtyInput = card.querySelector('.js-progress-qty-input');
            qtyInput.name = 'progress_items[' + baselineItemId + '][qty_progress]';

            var photoInput = card.querySelector('.js-progress-photo-input');
            photoInput.name = 'progress_photos[' + baselineItemId + '][]';

            progressList.appendChild(card);
            bindDropzones();
            toggleProgressEmptyState();
        }

        bindDropzones();
        bindComplyReviewForms();
        toggleProgressEmptyState();

        if (progressAddButton && progressSelector) {
            progressAddButton.addEventListener('click', function () {
                if (!progressSelector.value) {
                    alert('Pilih item implementasi terlebih dahulu.');
                    return;
                }

                addProgressItemCard(progressSelector.options[progressSelector.selectedIndex]);
                progressSelector.value = '';
            });
        }

        var lightbox = document.getElementById('impl-lightbox');
        var lightboxImage = document.getElementById('impl-lightbox-image');
        var lightboxTitle = document.getElementById('impl-lightbox-title');
        var lightboxCaption = document.getElementById('impl-lightbox-caption');
        var lightboxClose = document.getElementById('impl-lightbox-close');
        var lightboxPrev = document.getElementById('impl-lightbox-prev');
        var lightboxNext = document.getElementById('impl-lightbox-next');
        var lightboxZoomIn = document.getElementById('impl-lightbox-zoom-in');
        var lightboxZoomOut = document.getElementById('impl-lightbox-zoom-out');
        var lightboxRotateLeft = document.getElementById('impl-lightbox-rotate-left');
        var lightboxRotateRight = document.getElementById('impl-lightbox-rotate-right');
        var lightboxSaveRotation = document.getElementById('impl-lightbox-save-rotation');
        var lightboxStage = document.querySelector('#impl-lightbox .impl-lightbox__stage');
        var lightboxItems = [];
        var lightboxIndex = -1;
        var lightboxScale = 1;
        var lightboxMinScale = 0.5;
        var lightboxRotation = 0;
        var lightboxIsSavingRotation = false;
        var lazyPhotoObserver = null;
        var lightboxImageCache = {};
        var photoPreviewVersionMap = {};
        var complyCategoryFilter = document.getElementById('impl-comply-category-filter');
        var complySearchFilter = document.getElementById('impl-comply-search-filter');
        var complyFilterEmpty = document.getElementById('impl-comply-filter-empty');

        function escapeAttr(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function getComplyBadgeClass(status) {
            status = String(status || '').toUpperCase();
            if (status === 'APPROVED') {
                return 'success';
            }
            if (status === 'REJECTED') {
                return 'danger';
            }
            return 'warning';
        }

        function buildComplyReviewButtons(photoId, photoLabel, status, canDeletePhoto) {
            if (!canApproveComplyPhoto && !canDeletePhoto) {
                return '';
            }

            status = String(status || 'UPLOADED').toUpperCase();
            var safeId = escapeAttr(photoId);
            var safeLabel = escapeAttr(photoLabel || '-');
            var html = '<div class="dropdown impl-photo-action-menu">';
            html += '<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menu aksi foto">&#8942;</button>';
            html += '<div class="dropdown-menu dropdown-menu-right">';

            if (canApproveComplyPhoto && (status === 'UPLOADED' || status === 'REJECTED')) {
                html += '<button type="button" class="dropdown-item text-success js-open-comply-approve" data-toggle="modal" data-target="#modal-comply-approve" data-photo-id="' + safeId + '" data-photo-label="' + safeLabel + '">Approve</button>';
            }
            if (canApproveComplyPhoto && (status === 'UPLOADED' || status === 'APPROVED')) {
                html += '<button type="button" class="dropdown-item text-danger js-open-comply-reject" data-toggle="modal" data-target="#modal-comply-reject" data-photo-id="' + safeId + '" data-photo-label="' + safeLabel + '">Reject</button>';
            }
            if (canDeletePhoto) {
                if (canApproveComplyPhoto) {
                    html += '<div class="dropdown-divider"></div>';
                }
                html += '<button type="button" class="dropdown-item text-danger js-delete-progress-photo" data-photo-id="' + safeId + '" data-photo-label="' + safeLabel + '">Hapus</button>';
            }

            html += '</div></div>';

            return html;
        }

        function syncComplyPhotoReviewCard(photo) {
            if (!photo || !photo.id_progress_photo) {
                return;
            }

            var photoId = parseInt(photo.id_progress_photo, 10) || 0;
            if (photoId <= 0) {
                return;
            }

            var status = String(photo.status_photo || 'UPLOADED').toUpperCase();
            var badgeClass = photo.badge_class || getComplyBadgeClass(status);
            var remark = String(photo.review_remark || '');
            var cards = document.querySelectorAll('.js-comply-photo-review-card[data-photo-id="' + photoId + '"]');

            Array.prototype.forEach.call(cards, function (card) {
                var label = card.getAttribute('data-photo-label') || '-';
                var canDeletePhoto = card.getAttribute('data-can-delete-photo') === '1';
                var badge = card.querySelector('.js-comply-photo-status');
                var remarkWrap = card.querySelector('.js-comply-review-remark');
                var remarkText = remarkWrap ? remarkWrap.querySelector('span') : null;
                var actions = card.querySelector('.js-comply-photo-actions');

                if (badge) {
                    badge.className = badge.className.replace(/\bbadge-(success|danger|warning|secondary|info|primary)\b/g, '').replace(/\s+/g, ' ').trim();
                    badge.classList.add('badge-' + badgeClass);
                    badge.textContent = status;
                }

                if (remarkWrap && remarkText) {
                    remarkText.textContent = remark;
                    remarkWrap.classList.toggle('d-none', remark === '');
                }

                if (actions) {
                    actions.innerHTML = buildComplyReviewButtons(photoId, label, status, canDeletePhoto);
                }
            });
        }

        function showComplyReviewMessage(message, isSuccess) {
            var container = document.querySelector('.content .container-fluid');
            if (!container) {
                alert(message || (isSuccess ? 'Berhasil menyimpan review foto comply.' : 'Gagal menyimpan review foto comply.'));
                return;
            }

            var oldAlert = container.querySelector('.js-comply-review-alert');
            if (oldAlert) {
                oldAlert.remove();
            }

            var alertNode = document.createElement('div');
            alertNode.className = 'alert js-comply-review-alert ' + (isSuccess ? 'alert-success' : 'alert-danger');
            alertNode.textContent = message || (isSuccess ? 'Berhasil menyimpan review foto comply.' : 'Gagal menyimpan review foto comply.');
            container.insertBefore(alertNode, container.firstChild);

            window.setTimeout(function () {
                if (alertNode && alertNode.parentNode) {
                    alertNode.parentNode.removeChild(alertNode);
                }
            }, 4500);
        }

        function setComplyReviewSubmitting(form, isSubmitting) {
            var submitButton = form ? form.querySelector('button[type="submit"]') : null;
            if (!submitButton) {
                return;
            }

            if (isSubmitting) {
                submitButton.dataset.originalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses...';
                return;
            }

            submitButton.disabled = false;
            submitButton.textContent = submitButton.dataset.originalText || submitButton.textContent;
        }

        function removeDeletedPhotoCard(photoId) {
            photoId = parseInt(photoId || 0, 10) || 0;
            if (photoId <= 0) {
                return;
            }

            Array.prototype.forEach.call(document.querySelectorAll('.js-comply-photo-review-card[data-photo-id="' + photoId + '"]'), function (card) {
                var row = card.closest('.js-comply-gallery-row');
                var grid = card.parentNode;
                card.remove();
                if (grid && grid.querySelectorAll('.js-comply-photo-review-card').length === 0 && row) {
                    row.remove();
                }
            });

            applyComplyGalleryFilter();
        }

        function deleteProgressPhoto(photoId, photoLabel) {
            photoId = parseInt(photoId || 0, 10) || 0;
            if (photoId <= 0) {
                alert('Data foto tidak valid.');
                return;
            }

            if (!window.confirm('Hapus foto ini?\n' + (photoLabel || 'Foto'))) {
                return;
            }

            var formData = new FormData();
            formData.append('cluster_id', clusterId);
            formData.append('photo_id', photoId);

            fetch(deletePhotoUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.text().then(function (text) {
                        var data = {};
                        try {
                            data = text ? JSON.parse(text) : {};
                        } catch (e) {
                            data = { status: false, message: 'Response server tidak valid.' };
                        }
                        if (!response.ok && data.status !== true) {
                            data.status = false;
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (!data.status) {
                        alert(data.message || 'Gagal menghapus foto.');
                        return;
                    }

                    removeDeletedPhotoCard(photoId);
                    showComplyReviewMessage(data.message || 'Foto berhasil dihapus.', true);
                })
                .catch(function () {
                    alert('Gagal menghapus foto. Coba lagi beberapa saat.');
                });
        }

        function bindComplyReviewForms() {
            var forms = document.querySelectorAll('.js-comply-review-form');
            Array.prototype.forEach.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (!window.fetch || !window.FormData) {
                        return;
                    }

                    event.preventDefault();
                    setComplyReviewSubmitting(form, true);

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            return response.text().then(function (text) {
                                var data = {};
                                try {
                                    data = text ? JSON.parse(text) : {};
                                } catch (e) {
                                    data = { status: false, message: 'Response server tidak valid.' };
                                }
                                if (!response.ok && data.status !== true) {
                                    data.status = false;
                                }
                                return data;
                            });
                        })
                        .then(function (data) {
                            if (!data.status) {
                                showComplyReviewMessage(data.message || 'Gagal menyimpan review foto comply.', false);
                                return;
                            }

                            syncComplyPhotoReviewCard(data.photo);
                            showComplyReviewMessage(data.message || 'Review foto comply berhasil disimpan.', true);
                            if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
                                window.jQuery(form.closest('.modal')).modal('hide');
                            }
                            form.reset();
                        })
                        .catch(function () {
                            showComplyReviewMessage('Gagal menyimpan review foto comply. Coba lagi beberapa saat.', false);
                        })
                        .finally(function () {
                            setComplyReviewSubmitting(form, false);
                        });
                });
            });
        }

        function formatDailyQty(value) {
            var number = parseFloat(value || 0);
            if (isNaN(number)) {
                number = 0;
            }
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number);
        }

        function syncLightboxButtons() {
            if (!lightboxPrev || !lightboxNext) {
                return;
            }

            var hasMultiple = lightboxItems.length > 1;
            lightboxPrev.disabled = !hasMultiple;
            lightboxNext.disabled = !hasMultiple;
            syncLightboxSaveButton();
        }

        function loadLazyPhoto(img) {
            if (!img || img.classList.contains('is-loaded')) {
                return;
            }

            var src = img.getAttribute('data-src') || '';
            if (!src) {
                return;
            }

            img.src = src;
            img.classList.add('is-loaded');
            img.removeAttribute('data-src');
            if (lazyPhotoObserver) {
                lazyPhotoObserver.unobserve(img);
            }
        }

        function observeLazyPhotos(root) {
            var scope = root || document;
            var photos = scope.querySelectorAll ? scope.querySelectorAll('img.js-lazy-photo[data-src]') : [];
            if (!photos.length) {
                return;
            }

            if ('IntersectionObserver' in window) {
                if (!lazyPhotoObserver) {
                    lazyPhotoObserver = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting || entry.intersectionRatio > 0) {
                                loadLazyPhoto(entry.target);
                            }
                        });
                    }, {
                        root: null,
                        rootMargin: '260px 0px',
                        threshold: 0.01
                    });
                }

                Array.prototype.forEach.call(photos, function (img) {
                    lazyPhotoObserver.observe(img);
                });
                return;
            }

            Array.prototype.slice.call(photos, 0, 12).forEach(loadLazyPhoto);
        }

        function normalizeFilterText(value) {
            return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
        }

        function applyComplyGalleryFilter() {
            var selectedCategory = complyCategoryFilter ? normalizeFilterText(complyCategoryFilter.value) : '';
            var keyword = complySearchFilter ? normalizeFilterText(complySearchFilter.value) : '';
            var sections = document.querySelectorAll('.js-comply-gallery-section');
            var visibleSectionCount = 0;

            Array.prototype.forEach.call(sections, function (section) {
                var sectionCategory = normalizeFilterText(section.getAttribute('data-gallery-category') || '');
                var categoryMatch = selectedCategory === '' || sectionCategory === selectedCategory;
                var visibleRowCount = 0;
                var rows = section.querySelectorAll('.js-comply-gallery-row');

                Array.prototype.forEach.call(rows, function (row) {
                    var searchable = normalizeFilterText(row.getAttribute('data-search') || row.textContent || '');
                    var keywordMatch = keyword === '' || searchable.indexOf(keyword) !== -1;
                    var visible = categoryMatch && keywordMatch;
                    row.classList.toggle('d-none', !visible);
                    if (visible) {
                        visibleRowCount++;
                        var noNode = row.querySelector('.js-comply-gallery-no');
                        if (noNode) {
                            noNode.textContent = visibleRowCount;
                        }
                    }
                });

                section.classList.toggle('d-none', visibleRowCount === 0);
                if (visibleRowCount > 0) {
                    visibleSectionCount++;
                }
            });

            if (complyFilterEmpty) {
                complyFilterEmpty.classList.toggle('d-none', visibleSectionCount > 0);
            }

            observeLazyPhotos(document.getElementById('impl-comply-pane'));
        }

        function syncLightboxSaveButton() {
            if (!lightboxSaveRotation) {
                return;
            }

            var activeItem = lightboxItems[lightboxIndex] || {};
            var photoId = parseInt(activeItem.photoId || 0, 10) || 0;
            lightboxSaveRotation.disabled = !canSavePhotoRotation || lightboxIsSavingRotation || photoId <= 0 || lightboxRotation === 0;
            lightboxSaveRotation.textContent = lightboxIsSavingRotation ? 'Menyimpan...' : 'Simpan';
        }

        function applyLightboxTransform() {
            if (!lightboxImage) {
                return;
            }

            var naturalWidth = lightboxImage.naturalWidth || 0;
            var naturalHeight = lightboxImage.naturalHeight || 0;

            if (!naturalWidth || !naturalHeight) {
                lightboxImage.style.width = '';
                lightboxImage.style.height = 'auto';
                lightboxImage.style.margin = '';
                lightboxImage.style.transform = '';
                return;
            }

            var displayWidth = Math.round(naturalWidth * lightboxScale);
            var displayHeight = Math.round(naturalHeight * lightboxScale);
            var normalizedRotation = ((lightboxRotation % 360) + 360) % 360;
            var isSideways = normalizedRotation === 90 || normalizedRotation === 270;
            var verticalMargin = isSideways ? Math.max(0, (displayWidth - displayHeight) / 2) : 0;
            var horizontalMargin = isSideways ? Math.max(0, (displayHeight - displayWidth) / 2) : 0;

            lightboxImage.style.width = displayWidth + 'px';
            lightboxImage.style.height = 'auto';
            lightboxImage.style.marginTop = Math.ceil(verticalMargin) + 'px';
            lightboxImage.style.marginBottom = Math.ceil(verticalMargin) + 'px';
            lightboxImage.style.marginLeft = Math.ceil(horizontalMargin) + 'px';
            lightboxImage.style.marginRight = Math.ceil(horizontalMargin) + 'px';
            lightboxImage.style.transform = 'rotate(' + normalizedRotation + 'deg)';
            lightboxImage.style.transformOrigin = 'center center';
            syncLightboxSaveButton();
        }

        function calculateLightboxFitScale() {
            if (!lightboxImage || !lightboxStage) {
                return 0.5;
            }

            var naturalWidth = lightboxImage.naturalWidth || 0;
            var naturalHeight = lightboxImage.naturalHeight || 0;
            if (!naturalWidth || !naturalHeight) {
                return 0.5;
            }

            var normalizedRotation = ((lightboxRotation % 360) + 360) % 360;
            var isSideways = normalizedRotation === 90 || normalizedRotation === 270;
            var imageWidth = isSideways ? naturalHeight : naturalWidth;
            var imageHeight = isSideways ? naturalWidth : naturalHeight;
            var stageWidth = Math.max((lightboxStage.clientWidth || 0) - 24, 1);
            var stageHeight = Math.max((lightboxStage.clientHeight || 0) - 24, 1);
            var fitScale = Math.min(stageWidth / imageWidth, stageHeight / imageHeight, 1);

            return Math.max(0.05, Math.min(1, fitScale));
        }

        function resetLightboxToFit() {
            lightboxMinScale = calculateLightboxFitScale();
            lightboxScale = lightboxMinScale;
            applyLightboxTransform();
            if (lightboxStage) {
                lightboxStage.scrollLeft = 0;
                lightboxStage.scrollTop = 0;
            }
        }

        function zoomLightboxTo(nextScale, originEvent) {
            if (!lightbox || !lightboxImage || !lightbox.classList.contains('is-open')) {
                return;
            }

            var targetScale = Math.max(lightboxMinScale, Math.min(3, nextScale));
            if (targetScale === lightboxScale) {
                return;
            }

            var stageRect = lightboxStage ? lightboxStage.getBoundingClientRect() : null;
            var originX = stageRect ? stageRect.width / 2 : 0;
            var originY = stageRect ? stageRect.height / 2 : 0;
            var contentX = 0;
            var contentY = 0;
            var scaleRatio = targetScale / lightboxScale;

            if (lightboxStage && stageRect) {
                originX = originEvent ? originEvent.clientX - stageRect.left : originX;
                originY = originEvent ? originEvent.clientY - stageRect.top : originY;
                contentX = lightboxStage.scrollLeft + originX;
                contentY = lightboxStage.scrollTop + originY;
            }

            lightboxScale = targetScale;
            applyLightboxTransform();

            if (lightboxStage) {
                lightboxStage.scrollLeft = (contentX * scaleRatio) - originX;
                lightboxStage.scrollTop = (contentY * scaleRatio) - originY;
            }
        }

        function initLightboxDragScroll(container) {
            if (!container || container.dataset.dragScrollReady === '1') {
                return;
            }

            container.dataset.dragScrollReady = '1';

            var isDragging = false;
            var startX = 0;
            var startY = 0;
            var startScrollLeft = 0;
            var startScrollTop = 0;
            var activePointerId = null;

            container.addEventListener('pointerdown', function (event) {
                if (event.button !== undefined && event.button !== 0) {
                    return;
                }

                isDragging = true;
                activePointerId = event.pointerId;
                startX = event.clientX;
                startY = event.clientY;
                startScrollLeft = container.scrollLeft;
                startScrollTop = container.scrollTop;
                container.classList.add('is-dragging');

                if (container.setPointerCapture && activePointerId !== null) {
                    container.setPointerCapture(activePointerId);
                }

                event.preventDefault();
            });

            container.addEventListener('pointermove', function (event) {
                if (!isDragging) {
                    return;
                }

                container.scrollLeft = startScrollLeft - (event.clientX - startX);
                container.scrollTop = startScrollTop - (event.clientY - startY);
                event.preventDefault();
            });

            ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (eventName) {
                container.addEventListener(eventName, function () {
                    isDragging = false;
                    activePointerId = null;
                    container.classList.remove('is-dragging');
                });
            });

            container.addEventListener('dragstart', function (event) {
                event.preventDefault();
            });

            container.addEventListener('wheel', function (event) {
                if (!lightbox || !lightbox.classList.contains('is-open')) {
                    return;
                }

                event.preventDefault();
                zoomLightboxTo(lightboxScale + (event.deltaY < 0 ? 0.15 : -0.15), event);
            }, { passive: false });
        }

        initLightboxDragScroll(lightboxStage);

        if (lightboxImage) {
            lightboxImage.addEventListener('load', function () {
                if (lightboxImage.src) {
                    lightboxImageCache[lightboxImage.src] = true;
                }
                resetLightboxToFit();
            });
        }

        function rotateLightbox(deltaDegrees) {
            if (!lightbox || !lightboxImage || !lightbox.classList.contains('is-open')) {
                return;
            }

            lightboxRotation = ((lightboxRotation + deltaDegrees) % 360 + 360) % 360;
            resetLightboxToFit();
        }

        function stripCacheQuery(url) {
            return String(url || '').split('?')[0];
        }

        function getProgressPhotoPreviewUrl(photoId, size) {
            photoId = parseInt(photoId || 0, 10) || 0;
            if (photoId <= 0) {
                return '';
            }
            var url = photoPreviewBaseUrl + '/' + encodeURIComponent(photoId) + '/' + encodeURIComponent(size || 'thumb');
            if (photoPreviewVersionMap[photoId]) {
                url += '?v=' + encodeURIComponent(photoPreviewVersionMap[photoId]);
            }
            return url;
        }

        function getUrlQueryParam(url, key) {
            var query = String(url || '').split('?')[1] || '';
            var parts = query.split('&');
            for (var i = 0; i < parts.length; i++) {
                var pair = parts[i].split('=');
                if (decodeURIComponent(pair[0] || '') === key) {
                    return decodeURIComponent(pair[1] || '');
                }
            }
            return '';
        }

        function refreshPhotoImageUrl(photoId, imageUrl, thumbUrl) {
            photoId = parseInt(photoId || 0, 10) || 0;
            if (photoId <= 0 || !imageUrl) {
                return;
            }
            thumbUrl = thumbUrl || imageUrl;
            var nextVersion = getUrlQueryParam(imageUrl, 'v') || getUrlQueryParam(thumbUrl, 'v');
            if (nextVersion) {
                photoPreviewVersionMap[photoId] = nextVersion;
            }

            Array.prototype.forEach.call(document.querySelectorAll('.js-open-lightbox[data-photo-id="' + photoId + '"]'), function (node) {
                node.setAttribute('href', imageUrl);
                node.setAttribute('data-image', imageUrl);
                var img = node.querySelector('img');
                if (img) {
                    if (img.classList.contains('is-loaded')) {
                        img.src = thumbUrl;
                    } else {
                        img.setAttribute('data-src', thumbUrl);
                        img.src = lazyPhotoPlaceholder;
                    }
                }
            });

            lightboxItems.forEach(function (item) {
                if ((parseInt(item.photoId || 0, 10) || 0) === photoId) {
                    item.image = imageUrl;
                }
            });
        }

        function getImageMimeFromUrl(url) {
            var cleanUrl = stripCacheQuery(url).toLowerCase();
            if (cleanUrl.indexOf('.png') !== -1) {
                return 'image/png';
            }
            if (cleanUrl.indexOf('.webp') !== -1) {
                return 'image/webp';
            }
            return 'image/jpeg';
        }

        function getImageMimeForLightboxItem(item) {
            var mime = String((item && item.mime) || '').toLowerCase();
            if (mime === 'image/png' || mime === 'image/webp' || mime === 'image/jpeg') {
                return mime;
            }
            return getImageMimeFromUrl((item && item.image) || '');
        }

        function buildRotatedPhotoBlob(rotationDegrees, mimeType) {
            return new Promise(function (resolve, reject) {
                if (!lightboxImage || !lightboxImage.complete || !lightboxImage.naturalWidth || !lightboxImage.naturalHeight) {
                    reject(new Error('Foto belum selesai dimuat.'));
                    return;
                }

                var normalizedRotation = ((rotationDegrees % 360) + 360) % 360;
                var sourceWidth = lightboxImage.naturalWidth;
                var sourceHeight = lightboxImage.naturalHeight;
                var isSideways = normalizedRotation === 90 || normalizedRotation === 270;
                var canvas = document.createElement('canvas');
                canvas.width = isSideways ? sourceHeight : sourceWidth;
                canvas.height = isSideways ? sourceWidth : sourceHeight;

                var context = canvas.getContext('2d');
                if (!context) {
                    reject(new Error('Browser tidak mendukung canvas.'));
                    return;
                }

                context.translate(canvas.width / 2, canvas.height / 2);
                context.rotate(normalizedRotation * Math.PI / 180);
                context.drawImage(lightboxImage, -sourceWidth / 2, -sourceHeight / 2);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('Gagal membuat file hasil rotasi.'));
                        return;
                    }
                    resolve(blob);
                }, mimeType, 0.9);
            });
        }

        function saveLightboxRotation() {
            if (!lightbox || !lightbox.classList.contains('is-open') || lightboxIndex < 0 || lightboxIsSavingRotation) {
                return;
            }

            var activeItem = lightboxItems[lightboxIndex] || {};
            var photoId = parseInt(activeItem.photoId || 0, 10) || 0;
            if (photoId <= 0 || lightboxRotation === 0) {
                return;
            }

            var formData = new FormData();
            formData.append('cluster_id', clusterId);
            formData.append('photo_id', photoId);
            formData.append('rotation', lightboxRotation);
            var pendingRotation = lightboxRotation;
            lightboxIsSavingRotation = true;
            syncLightboxSaveButton();

            buildRotatedPhotoBlob(pendingRotation, getImageMimeForLightboxItem(activeItem))
                .then(function (blob) {
                    formData.append('rotated_photo', blob, 'rotated_photo');
                    return fetch(rotatePhotoUrl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                })
                .then(function (response) {
                    return response.text().then(function (text) {
                        var data = {};
                        try {
                            data = text ? JSON.parse(text) : {};
                        } catch (e) {
                            data = { status: false, message: 'Response server tidak valid.' };
                        }
                        if (!response.ok && data.status !== true) {
                            data.status = false;
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (!data.status) {
                        alert(data.message || 'Gagal menyimpan rotasi foto.');
                        return;
                    }

                    refreshPhotoImageUrl(photoId, data.image_url || activeItem.image, data.thumb_url || data.image_url || activeItem.image);
                    lightboxRotation = 0;
                    lightboxImage.src = data.image_url || activeItem.image;
                })
                .catch(function (error) {
                    alert(error && error.message ? error.message : 'Gagal menyimpan rotasi foto. Coba lagi beberapa saat.');
                })
                .finally(function () {
                    lightboxIsSavingRotation = false;
                    syncLightboxSaveButton();
                });
        }

        function renderLightbox(index) {
            if (!lightboxItems.length || !lightboxImage) {
                return;
            }

            if (index < 0) {
                index = lightboxItems.length - 1;
            }
            if (index >= lightboxItems.length) {
                index = 0;
            }

            lightboxIndex = index;
            var activeItem = lightboxItems[index] || {};
            var nextImage = activeItem.image || '';
            if (lightboxImage.getAttribute('src') !== nextImage) {
                lightboxImage.src = nextImage;
            }
            lightboxTitle.textContent = activeItem.title || 'Preview Foto';
            lightboxCaption.textContent = activeItem.caption || '-';
            lightboxScale = 1;
            lightboxMinScale = 0.5;
            lightboxRotation = 0;
            lightboxImage.style.width = '';
            lightboxImage.style.height = 'auto';
            lightboxImage.style.margin = '';
            lightboxImage.style.transform = '';
            if (lightboxStage) {
                lightboxStage.scrollLeft = 0;
                lightboxStage.scrollTop = 0;
            }
            if (lightboxImage.complete) {
                resetLightboxToFit();
            }
            syncLightboxButtons();
        }

        function openLightbox(imageUrl, title, caption, triggerElement) {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightboxItems = [];
            var groupSelector = '.js-open-lightbox';
            var groupOwner = triggerElement && triggerElement.closest('[data-lightbox-group]');
            if (groupOwner) {
                groupSelector = '[data-lightbox-group="' + groupOwner.getAttribute('data-lightbox-group') + '"] .js-open-lightbox';
            }

            var groupedTriggers = document.querySelectorAll(groupSelector);
            Array.prototype.forEach.call(groupedTriggers, function (node) {
                lightboxItems.push({
                    photoId: node.getAttribute('data-photo-id') || '0',
                    image: node.getAttribute('data-image') || '',
                    mime: node.getAttribute('data-mime') || '',
                    title: node.getAttribute('data-title') || 'Preview Foto',
                    caption: node.getAttribute('data-caption') || '-',
                });
            });

            if (!lightboxItems.length) {
                lightboxItems.push({
                    photoId: triggerElement ? (triggerElement.getAttribute('data-photo-id') || '0') : '0',
                    image: imageUrl || '',
                    mime: triggerElement ? (triggerElement.getAttribute('data-mime') || '') : '',
                    title: title || 'Preview Foto',
                    caption: caption || '-',
                });
            }

            var foundIndex = 0;
            for (var i = 0; i < lightboxItems.length; i++) {
                if (stripCacheQuery(lightboxItems[i].image) === stripCacheQuery(imageUrl || '') && lightboxItems[i].caption === (caption || '-')) {
                    foundIndex = i;
                    break;
                }
            }

            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            renderLightbox(foundIndex);
        }

        function closeLightbox() {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightbox.classList.remove('is-open');
            lightboxImage.style.width = '';
            lightboxImage.style.height = 'auto';
            lightboxImage.style.margin = '';
            lightboxImage.style.transform = '';
            document.body.style.overflow = '';
            lightboxItems = [];
            lightboxIndex = -1;
            lightboxScale = 1;
            lightboxMinScale = 0.5;
            lightboxRotation = 0;
        }

        if (lightboxClose) {
            lightboxClose.addEventListener('click', closeLightbox);
        }

        if (lightboxPrev) {
            lightboxPrev.addEventListener('click', function () {
                if (lightboxIndex !== -1) {
                    renderLightbox(lightboxIndex - 1);
                }
            });
        }

        if (lightboxNext) {
            lightboxNext.addEventListener('click', function () {
                if (lightboxIndex !== -1) {
                    renderLightbox(lightboxIndex + 1);
                }
            });
        }

        if (lightboxZoomIn) {
            lightboxZoomIn.addEventListener('click', function () {
                if (!lightboxImage || !lightbox.classList.contains('is-open')) {
                    return;
                }
                zoomLightboxTo(lightboxScale + 0.25);
            });
        }

        if (lightboxZoomOut) {
            lightboxZoomOut.addEventListener('click', function () {
                if (!lightboxImage || !lightbox.classList.contains('is-open')) {
                    return;
                }
                zoomLightboxTo(lightboxScale - 0.25);
            });
        }

        if (lightboxRotateLeft) {
            lightboxRotateLeft.addEventListener('click', function () {
                rotateLightbox(-90);
            });
        }

        if (lightboxRotateRight) {
            lightboxRotateRight.addEventListener('click', function () {
                rotateLightbox(90);
            });
        }

        if (lightboxSaveRotation) {
            lightboxSaveRotation.addEventListener('click', saveLightboxRotation);
        }

        if (lightbox) {
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });

            lightbox.addEventListener('mousedown', function (event) {
                var dialog = event.target.closest('.impl-lightbox__dialog');
                if (!dialog) {
                    closeLightbox();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            var isEscape = event.key === 'Escape' || event.key === 'Esc' || event.keyCode === 27;
            if (isEscape && lightbox && lightbox.classList.contains('is-open')) {
                closeLightbox();
                return;
            }

            if (!lightbox || !lightbox.classList.contains('is-open')) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                if (lightboxIndex !== -1) {
                    renderLightbox(lightboxIndex - 1);
                }
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                if (lightboxIndex !== -1) {
                    renderLightbox(lightboxIndex + 1);
                }
            } else if (event.key === '+' || event.key === '=') {
                event.preventDefault();
                zoomLightboxTo(lightboxScale + 0.25);
            } else if (event.key === '-') {
                event.preventDefault();
                zoomLightboxTo(lightboxScale - 0.25);
            } else if (event.key === '[') {
                event.preventDefault();
                rotateLightbox(-90);
            } else if (event.key === ']' || event.key === 'r' || event.key === 'R') {
                event.preventDefault();
                rotateLightbox(90);
            }
        });

        document.addEventListener('click', function (event) {
            var boqBreakdownButton = event.target.closest('.js-open-boq-breakdown');
            if (boqBreakdownButton) {
                event.preventDefault();
                var boqType = boqBreakdownButton.getAttribute('data-item-type') || '-';
                var breakdownRows = [];
                try {
                    breakdownRows = boqBreakdownButton.getAttribute('data-breakdown')
                        ? JSON.parse(boqBreakdownButton.getAttribute('data-breakdown'))
                        : [];
                } catch (e) {
                    breakdownRows = [];
                }

                var boqTypeNode = document.getElementById('boq_breakdown_type');
                var boqRowsNode = document.getElementById('boq_breakdown_rows');
                if (!boqTypeNode || !boqRowsNode) {
                    return;
                }
                boqTypeNode.textContent = boqType;

                if (!breakdownRows.length) {
                    boqRowsNode.innerHTML = '<div class="text-muted">Belum ada data breakdown.</div>';
                } else {
                    var isTiangBreakdown = String(boqType || '').toUpperCase() === 'TIANG';
                    var totalPlan = 0;
                    var totalCluster = 0;
                    var totalSubfeeder = 0;
                    var totalAchiev = 0;
                    var totalAchievTanam = 0;
                    var totalAchievComply = 0;
                    var totalGapTanamComply = 0;
                    var totalRemain = 0;
                    var totalPhotoTarget = 0;
                    var totalPhotoUploaded = 0;

                    var htmlBreakdown = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                    if (isTiangBreakdown) {
                        htmlBreakdown += '<thead><tr><th style="width:60px;">No</th><th>Jenis</th><th style="width:120px;">Vol Cluster</th><th style="width:130px;">Vol Subfeeder</th><th style="width:120px;">Plan</th><th style="width:130px;">Achiev Tanam</th><th style="width:130px;">Achiev Cor</th><th style="width:120px;">Selisih</th><th style="width:120px;">Remaining</th><th style="width:150px;">Foto (Upload/Target)</th></tr></thead><tbody>';
                    } else {
                        htmlBreakdown += '<thead><tr><th style="width:60px;">No</th><th>Jenis</th><th style="width:120px;">Vol Cluster</th><th style="width:130px;">Vol Subfeeder</th><th style="width:120px;">Plan</th><th style="width:120px;">Achiev</th><th style="width:120px;">Remaining</th><th style="width:150px;">Foto (Upload/Target)</th></tr></thead><tbody>';
                    }
                    breakdownRows.forEach(function (row, index) {
                        var qtyCluster = parseFloat(row.qty_cluster || 0);
                        var qtySubfeeder = parseFloat(row.qty_subfeeder || 0);
                        var plan = parseFloat(row.qty_plan || 0);
                        var achiev = parseFloat(row.qty_achiev || 0);
                        var achievTanam = parseFloat(row.qty_achiev_tanam || 0);
                        var achievComply = parseFloat(row.qty_achiev_comply || row.qty_achiev || 0);
                        var gapTanamComply = parseFloat(row.qty_gap_tanam_comply || (achievTanam - achievComply));
                        var remain = parseFloat(row.qty_remaining || 0);
                        var photoTarget = parseInt(row.photo_target || 0, 10);
                        var photoUploaded = parseInt(row.photo_uploaded || 0, 10);
                        totalCluster += qtyCluster;
                        totalSubfeeder += qtySubfeeder;
                        totalPlan += plan;
                        totalAchiev += achiev;
                        totalAchievTanam += achievTanam;
                        totalAchievComply += achievComply;
                        totalGapTanamComply += gapTanamComply;
                        totalRemain += remain;
                        totalPhotoTarget += photoTarget;
                        totalPhotoUploaded += photoUploaded;

                        var jenis = row.item_name || row.excel_item_name || '-';
                        htmlBreakdown += '<tr>';
                        htmlBreakdown += '<td class="text-center">' + (index + 1) + '</td>';
                        htmlBreakdown += '<td>' + escapeAttr(jenis) + '</td>';
                        htmlBreakdown += '<td class="text-right">' + Math.round(qtyCluster).toLocaleString('id-ID') + '</td>';
                        htmlBreakdown += '<td class="text-right">' + Math.round(qtySubfeeder).toLocaleString('id-ID') + '</td>';
                        htmlBreakdown += '<td class="text-right">' + Math.round(plan).toLocaleString('id-ID') + '</td>';
                        if (isTiangBreakdown) {
                            htmlBreakdown += '<td class="text-right">' + Math.round(achievTanam).toLocaleString('id-ID') + '</td>';
                            htmlBreakdown += '<td class="text-right">' + Math.round(achievComply).toLocaleString('id-ID') + '</td>';
                            htmlBreakdown += '<td class="text-right">' + Math.round(gapTanamComply).toLocaleString('id-ID') + '</td>';
                        } else {
                            htmlBreakdown += '<td class="text-right">' + Math.round(achiev).toLocaleString('id-ID') + '</td>';
                        }
                        htmlBreakdown += '<td class="text-right">' + Math.round(remain).toLocaleString('id-ID') + '</td>';
                        htmlBreakdown += '<td class="text-center">' + photoUploaded + ' / ' + photoTarget + '</td>';
                        htmlBreakdown += '</tr>';
                    });
                    htmlBreakdown += '</tbody>';
                    if (isTiangBreakdown) {
                        htmlBreakdown += '<tfoot><tr class="font-weight-bold"><td colspan="2" class="text-right">Total</td><td class="text-right">' + Math.round(totalCluster).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalSubfeeder).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalPlan).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalAchievTanam).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalAchievComply).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalGapTanamComply).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalRemain).toLocaleString('id-ID') + '</td><td class="text-center">' + totalPhotoUploaded + ' / ' + totalPhotoTarget + '</td></tr></tfoot>';
                    } else {
                        htmlBreakdown += '<tfoot><tr class="font-weight-bold"><td colspan="2" class="text-right">Total</td><td class="text-right">' + Math.round(totalCluster).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalSubfeeder).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalPlan).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalAchiev).toLocaleString('id-ID') + '</td><td class="text-right">' + Math.round(totalRemain).toLocaleString('id-ID') + '</td><td class="text-center">' + totalPhotoUploaded + ' / ' + totalPhotoTarget + '</td></tr></tfoot>';
                    }
                    htmlBreakdown += '</table></div>';
                    boqRowsNode.innerHTML = htmlBreakdown;
                }

                if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
                    window.jQuery('#modal-boq-breakdown').modal('show');
                }
                return;
            }

            var dailyDetailButton = event.target.closest('.js-open-daily-detail');
            if (dailyDetailButton) {
                event.preventDefault();
                var dailyDate = dailyDetailButton.getAttribute('data-daily-date') || '-';
                var dailyGlobalRemark = dailyDetailButton.getAttribute('data-daily-global-remark') || '-';
                var dailyActivities = [];
                try {
                    dailyActivities = dailyDetailButton.getAttribute('data-daily-activities')
                        ? JSON.parse(dailyDetailButton.getAttribute('data-daily-activities'))
                        : [];
                } catch (e) {
                    dailyActivities = [];
                }

                var detailDateNode = document.getElementById('daily_detail_date');
                var detailRowsNode = document.getElementById('daily_detail_rows');
                if (!detailDateNode || !detailRowsNode) {
                    return;
                }
                detailDateNode.textContent = dailyDate;

                if (!dailyActivities.length) {
                    detailRowsNode.innerHTML = '<div class="text-muted">Belum ada detail aktivitas.</div>';
                } else {
                    var totalQty = 0;
                    var totalPhotos = 0;
                    var maxTeamCount = 0;
                    var maxWorkerCount = 0;
                    var scopeSet = {};
                    dailyActivities.forEach(function (activity) {
                        totalQty += parseFloat(activity.qty_activity || 0);
                        var photoCount = (activity.photos || []).length;
                        totalPhotos += photoCount;
                        maxTeamCount = Math.max(maxTeamCount, parseInt(activity.team_count || 0, 10) || 0);
                        maxWorkerCount = Math.max(maxWorkerCount, parseInt(activity.worker_count || 0, 10) || 0);
                        if (activity.scope_type) {
                            scopeSet[String(activity.scope_type).toUpperCase()] = true;
                        }
                    });
                    var scopeList = Object.keys(scopeSet);

                    var htmlDaily = '';
                    htmlDaily += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-3">';
                    htmlDaily += '<div class="row">';
                    htmlDaily += '<div class="col-md-4"><div class="small text-muted">Cluster</div><div class="font-weight-bold"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-'), ENT_QUOTES) ?></div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Regional</div><div class="font-weight-bold"><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-'), ENT_QUOTES) ?></div></div>';
                    htmlDaily += '<div class="col-md-2"><div class="small text-muted">Kota</div><div class="font-weight-bold"><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-'), ENT_QUOTES) ?></div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Status</div><div class="font-weight-bold"><?= htmlspecialchars((string) ($cluster['implementation_status'] ?? '-'), ENT_QUOTES) ?></div></div>';
                    htmlDaily += '</div></div></div>';

                    htmlDaily += '<div class="card border-0 shadow-sm mb-3"><div class="card-body py-3">';
                    htmlDaily += '<div class="row">';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Tanggal</div><div class="font-weight-bold">' + escapeAttr(dailyDate) + '</div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Total Aktivitas</div><div class="font-weight-bold">' + dailyActivities.length + '</div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Total Qty</div><div class="font-weight-bold">' + formatDailyQty(totalQty) + '</div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Total Foto / Scope</div><div class="font-weight-bold">' + totalPhotos + ' foto / ' + escapeAttr(scopeList.join(', ') || '-') + '</div></div>';
                    htmlDaily += '</div><hr class="my-2">';
                    htmlDaily += '<div class="row">';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Jumlah Team</div><div class="font-weight-bold">' + escapeAttr(String(maxTeamCount)) + '</div></div>';
                    htmlDaily += '<div class="col-md-3"><div class="small text-muted">Jumlah Orang</div><div class="font-weight-bold">' + escapeAttr(String(maxWorkerCount)) + '</div></div>';
                    htmlDaily += '<div class="col-md-6"><div class="small text-muted">Global Remarks</div><div class="font-weight-bold">' + escapeAttr(dailyGlobalRemark || '-') + '</div></div>';
                    htmlDaily += '</div></div></div>';

                    htmlDaily += '<div class="table-responsive"><table class="table table-bordered table-sm">';
                    htmlDaily += '<thead><tr><th>Aktivitas</th><th>Detail</th><th>BOQ Type</th><th>Scope</th><th>Qty</th><th>Foto</th><th>PIC</th><th>Remark</th></tr></thead><tbody>';
                    dailyActivities.forEach(function (activity) {
                        htmlDaily += '<tr>';
                        htmlDaily += '<td>' + escapeAttr(activity.activity_name || '-') + '</td>';
                        htmlDaily += '<td>' + escapeAttr(activity.activity_detail || '-') + '</td>';
                        htmlDaily += '<td>' + escapeAttr(activity.boq_type || '-') + '</td>';
                        htmlDaily += '<td><span class="badge badge-secondary">' + escapeAttr(activity.scope_type || '-') + '</span></td>';
                        htmlDaily += '<td>' + formatDailyQty(activity.qty_activity || 0) + ' ' + escapeAttr(activity.unit_activity || '') + '</td>';
                        htmlDaily += '<td>';
                        var photos = activity.photos || [];
                        if (!photos.length) {
                            htmlDaily += '<span class="text-muted">-</span>';
                        } else {
                            htmlDaily += '<div class="d-flex flex-wrap" data-lightbox-group="daily-detail-' + escapeAttr(activity.id_daily_activity || 0) + '">';
                            photos.forEach(function (photo) {
                                var photoId = parseInt(photo.id_progress_photo || 0, 10) || 0;
                                var imagePath = (photo.file_path || '').replace(/^\/+/, '');
                                var imageUrl = photoId > 0 ? getProgressPhotoPreviewUrl(photoId, 'preview') : ('<?= base_url() ?>' + imagePath);
                                var thumbUrl = photoId > 0 ? getProgressPhotoPreviewUrl(photoId, 'thumb') : imageUrl;
                                var photoCaption = photo.caption || photo.file_name || 'Foto';
                                htmlDaily += '<a href="' + imageUrl + '" class="mr-2 mb-2 js-open-lightbox" data-photo-id="' + escapeAttr(photo.id_progress_photo || 0) + '" data-image="' + imageUrl + '" data-mime="' + escapeAttr(getImageMimeFromUrl(photo.file_path || '')) + '" data-title="' + escapeAttr(activity.activity_name || 'Daily Progress') + '" data-caption="' + escapeAttr(photoCaption) + '">';
                                htmlDaily += '<img src="' + lazyPhotoPlaceholder + '" data-src="' + thumbUrl + '" class="js-lazy-photo" alt="' + escapeAttr(photo.file_name || 'Foto') + '" loading="lazy" decoding="async" style="width:72px;height:54px;object-fit:cover;border-radius:8px;border:1px solid #dbe3ef;">';
                                htmlDaily += '</a>';
                            });
                            htmlDaily += '</div>';
                        }
                        htmlDaily += '</td>';
                        htmlDaily += '<td>' + escapeAttr(activity.nama_user || '-') + '</td>';
                        htmlDaily += '<td>' + escapeAttr(activity.remark_activity || '-') + '</td>';
                        htmlDaily += '</tr>';
                    });
                    htmlDaily += '</tbody></table></div>';
                    detailRowsNode.innerHTML = htmlDaily;
                    observeLazyPhotos(detailRowsNode);
                }

                if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.modal === 'function') {
                    window.jQuery('#modal-daily-detail').modal('show');
                }
                return;
            }

            var lightboxTrigger = event.target.closest('.js-open-lightbox');
            if (lightboxTrigger) {
                event.preventDefault();
                openLightbox(
                    lightboxTrigger.getAttribute('data-image') || '',
                    lightboxTrigger.getAttribute('data-title') || 'Preview Foto',
                    lightboxTrigger.getAttribute('data-caption') || '-',
                    lightboxTrigger
                );
                return;
            }

            var progressButton = event.target.closest('.js-open-progress-modal');
            if (progressButton) {
                if (!progressSelector) {
                    return;
                }

                var baselineItemId = progressButton.getAttribute('data-baseline-item-id') || '';
                if (!baselineItemId) {
                    return;
                }

                var option = progressSelector.querySelector('option[value="' + baselineItemId + '"]');
                if (option) {
                    addProgressItemCard(option);
                }
                return;
            }

            var historyButton = event.target.closest('.js-open-history-modal');
            if (!historyButton) {
                var approvePhotoButton = event.target.closest('.js-open-comply-approve');
                if (approvePhotoButton) {
                    document.getElementById('comply_approve_photo_id').value = approvePhotoButton.getAttribute('data-photo-id') || '0';
                    document.getElementById('comply_approve_photo_label').textContent = approvePhotoButton.getAttribute('data-photo-label') || '-';
                    return;
                }

                var rejectPhotoButton = event.target.closest('.js-open-comply-reject');
                if (rejectPhotoButton) {
                    document.getElementById('comply_reject_photo_id').value = rejectPhotoButton.getAttribute('data-photo-id') || '0';
                    document.getElementById('comply_reject_photo_label').textContent = rejectPhotoButton.getAttribute('data-photo-label') || '-';
                    return;
                }

                var deletePhotoButton = event.target.closest('.js-delete-progress-photo');
                if (deletePhotoButton) {
                    deleteProgressPhoto(
                        deletePhotoButton.getAttribute('data-photo-id') || '0',
                        deletePhotoButton.getAttribute('data-photo-label') || 'Foto'
                    );
                    return;
                }

                return;
            }

            document.getElementById('history_item_name').textContent = historyButton.getAttribute('data-item-name') || '-';

            var history = [];
            try {
                history = historyButton.getAttribute('data-history')
                    ? JSON.parse(historyButton.getAttribute('data-history'))
                    : [];
            } catch (e) {
                history = [];
            }

            if (!history.length) {
                document.getElementById('history_item_rows').innerHTML = '<div class="text-muted">Belum ada history.</div>';
                return;
            }

            var html = '<div class="table-responsive"><table class="table table-bordered impl-history-modal-table">';
            html += '<thead><tr><th style="width:60px;">No</th><th style="width:120px;">Tanggal</th><th style="width:90px;">Qty</th><th style="width:140px;">Status</th><th style="width:140px;">User</th><th>Remark</th><th style="min-width:240px;">Foto</th><th style="width:110px;">Aksi</th></tr></thead><tbody>';
            history.forEach(function (entry, index) {
                html += '<tr>';
                html += '<td class="text-center">' + (index + 1) + '</td>';
                html += '<td class="text-center">' + (entry.progress_date || '-') + '</td>';
                html += '<td class="text-center">' + (entry.qty_progress || '0') + '</td>';
                html += '<td class="text-center">' + (entry.status_progress || '-') + '</td>';
                html += '<td class="text-center">' + (entry.nama_user || 'System') + '</td>';
                html += '<td>' + (entry.remark_progress || '-') + '</td>';
                html += '<td>';

                if (entry.photos && entry.photos.length) {
                    html += '<div class="impl-history-modal-photo-grid" data-lightbox-group="history-' + index + '-' + escapeAttr(historyButton.getAttribute('data-item-name') || 'item') + '">';
                    entry.photos.forEach(function (photo) {
                        var photoCaption = photo.caption || photo.file_name || 'Foto Progress';
                        var photoCategory = (photo.photo_category || 'HARIAN').toUpperCase() === 'COMPLY' ? 'Foto Comply' : 'Foto Harian';
                        var photoStatus = String(photo.status_photo || ((photo.photo_category || 'HARIAN').toUpperCase() === 'COMPLY' ? 'UPLOADED' : 'APPROVED')).toUpperCase();
                        if (photo.comply_label) {
                            photoCaption = photoCategory + ' - ' + photo.comply_label;
                        } else {
                            photoCaption = photoCategory + ' - ' + photoCaption;
                        }
                        var isComplyPhoto = (photo.photo_category || '').toUpperCase() === 'COMPLY';
                        var photoId = parseInt(photo.id_progress_photo || 0, 10) || 0;
                        var canDeletePhoto = (parseInt(photo.uploaded_by || 0, 10) || 0) === <?= (int) $currentUserId ?>;
                        var photoOriginalUrl = '<?= base_url() ?>' + (photo.file_path || '');
                        var photoPreviewUrl = photoId > 0 ? getProgressPhotoPreviewUrl(photoId, 'preview') : photoOriginalUrl;
                        var photoThumbUrl = photoId > 0 ? getProgressPhotoPreviewUrl(photoId, 'thumb') : photoOriginalUrl;
                        html += '<div class="impl-history-modal-photo' + (isComplyPhoto ? ' js-comply-photo-review-card' : '') + '" data-photo-id="' + escapeAttr(photo.id_progress_photo || 0) + '" data-photo-label="' + escapeAttr(photoCaption) + '" data-can-delete-photo="' + (canDeletePhoto ? '1' : '0') + '">';
                        html += '<a href="' + photoPreviewUrl + '" class="js-open-lightbox d-block" data-photo-id="' + escapeAttr(photo.id_progress_photo || 0) + '" data-image="' + photoPreviewUrl + '" data-mime="' + escapeAttr(getImageMimeFromUrl(photo.file_path || '')) + '" data-title="' + escapeAttr(historyButton.getAttribute('data-item-name') || 'Preview Foto') + '" data-caption="' + escapeAttr(photoCaption) + '">';
                        html += '<img src="' + lazyPhotoPlaceholder + '" data-src="' + photoThumbUrl + '" class="js-lazy-photo" alt="' + escapeAttr(photo.file_name || 'Foto Progress') + '" loading="lazy" decoding="async">';
                        html += '<div>' + escapeAttr(photoCaption) + '</div>';
                        html += '</a>';
                        html += '<div class="small mt-1"><span class="badge badge-' + getComplyBadgeClass(photoStatus) + (isComplyPhoto ? ' js-comply-photo-status' : '') + '">' + escapeAttr(photoStatus) + '</span></div>';
                        html += '<div class="small text-muted mt-1 js-comply-review-remark' + (photo.review_remark ? '' : ' d-none') + '">Review: <span>' + escapeAttr(photo.review_remark || '') + '</span></div>';
                        if (isComplyPhoto && (canApproveComplyPhoto || canDeletePhoto)) {
                            html += '<div class="mt-2 js-comply-photo-actions">';
                            html += buildComplyReviewButtons(photo.id_progress_photo || 0, photoCaption, photoStatus, canDeletePhoto);
                            html += '</div>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                } else {
                    html += '<span class="text-muted">-</span>';
                }

                html += '</td>';
                html += '<td class="text-center">';
                html += '<button type="button" class="btn btn-sm btn-danger js-delete-history-entry" data-progress-item-id="' + (entry.id_progress_item || 0) + '" data-progress-date="' + escapeAttr(entry.progress_date || '-') + '" data-item-name="' + escapeAttr(historyButton.getAttribute('data-item-name') || 'Item') + '">Hapus</button>';
                html += '</td></tr>';
            });
            html += '</tbody></table></div>';

            document.getElementById('history_item_rows').innerHTML = html;
            observeLazyPhotos(document.getElementById('history_item_rows'));
        });

        document.addEventListener('click', function (event) {
            var deleteDailyButton = event.target.closest('.js-delete-daily-row');
            if (deleteDailyButton && deleteDailyActivityForm && deleteDailyActivityInput) {
                var dailyDate = (deleteDailyButton.getAttribute('data-daily-date') || '').trim();
                if (!dailyDate) {
                    alert('Data daily progress tidak valid.');
                    return;
                }

                var confirmDeleteDaily = 'Hapus 1 row daily progress tanggal ' + dailyDate + '? Semua aktivitas dan foto pada tanggal ini akan terhapus.';
                if (!window.confirm(confirmDeleteDaily)) {
                    return;
                }

                deleteDailyActivityInput.value = dailyDate;
                deleteDailyActivityForm.submit();
                return;
            }

            var deleteButton = event.target.closest('.js-delete-history-entry');
            if (!deleteButton || !deleteProgressForm || !deleteProgressInput) {
                return;
            }

            var progressItemId = parseInt(deleteButton.getAttribute('data-progress-item-id') || '0', 10);
            if (progressItemId <= 0) {
                alert('Data history progress tidak valid.');
                return;
            }

            var itemName = deleteButton.getAttribute('data-item-name') || 'item ini';
            var progressDate = deleteButton.getAttribute('data-progress-date') || '-';
            var confirmMessage = 'Hapus history progress "' + itemName + '" tanggal ' + progressDate + ' beserta semua foto evidencenya?';

            if (!window.confirm(confirmMessage)) {
                return;
            }

            deleteProgressInput.value = String(progressItemId);
            deleteProgressForm.submit();
        });

        if (progressList) {
            progressList.addEventListener('click', function (event) {
                var removeButton = event.target.closest('.js-remove-progress-item');
                if (removeButton) {
                    var card = removeButton.closest('.js-progress-item-card');
                    if (card) {
                        card.remove();
                        toggleProgressEmptyState();
                    }
                    return;
                }
            });
        }

        document.addEventListener('submit', function (event) {
            if (event.target.id !== 'form-bulk-progress') {
                return;
            }

            var cards = event.target.querySelectorAll('.js-progress-item-card');
            if (!cards.length) {
                event.preventDefault();
                alert('Tambahkan minimal 1 item implementasi terlebih dahulu.');
                return;
            }

            var isValid = true;
            Array.prototype.forEach.call(cards, function (card) {
                var qtyInput = card.querySelector('.js-progress-qty-input');
                var photoInput = card.querySelector('.js-progress-photo-input');
                var qtyValue = parseFloat(qtyInput && qtyInput.value ? qtyInput.value : '0');
                var hasPhotos = photoInput && photoInput.files && photoInput.files.length > 0;

                if (qtyValue <= 0 || !hasPhotos) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Setiap item wajib memiliki qty progress dan foto implementasi harian.');
            }
        });

        if (complyForm && complyRowsBody && complyRowTemplate) {
            populateComplyCategory();
            populateComplyKind();
            if (complyCategorySelect) {
                complyCategorySelect.addEventListener('change', populateComplyKind);
            }
            if (complyKindSelect) {
                complyKindSelect.addEventListener('change', syncComplyBuilderDefaults);
            }
            if (complyScopeSelect) {
                complyScopeSelect.addEventListener('change', syncComplyBuilderDefaults);
            }
            if (complyAddButton) {
                complyAddButton.addEventListener('click', addComplyRow);
            }
            complyRowsBody.addEventListener('change', function (event) {
                var row = event.target.closest('.js-comply-row');
                if (!row) {
                    return;
                }
                if (event.target.classList.contains('js-comply-photos')) {
                    previewComplyPhotos(row);
                }
            });
            complyRowsBody.addEventListener('click', function (event) {
                var removeButton = event.target.closest('.js-remove-comply-row');
                if (!removeButton) {
                    return;
                }
                var row = removeButton.closest('.js-comply-row');
                if (row) {
                    row.remove();
                    reindexComplyRows();
                    syncAllAutoComplyFields();
                }
                if (!complyRowsBody.querySelector('.js-comply-row')) {
                    addComplyRow();
                }
            });
            complyForm.addEventListener('submit', function (event) {
                var rows = complyRowsBody.querySelectorAll('.js-comply-row');
                if (!rows.length) {
                    event.preventDefault();
                    alert('Tambahkan minimal 1 entry comply.');
                    return;
                }
                var valid = true;
                Array.prototype.forEach.call(rows, function (row) {
                    var itemSelect = row.querySelector('.js-comply-item-id');
                    var labelInput = row.querySelector('.js-comply-label');
                    var photoInput = row.querySelector('.js-comply-photos');
                    var remarksInput = row.querySelector('.js-comply-remarks');
                    if (!itemSelect || !itemSelect.value || !labelInput || !labelInput.value.trim()) {
                        valid = false;
                        return;
                    }
                    var files = photoInput && photoInput.files ? photoInput.files : [];
                    if (!files.length) {
                        valid = false;
                        return;
                    }
                    var remarkLines = remarksInput && remarksInput.value
                        ? remarksInput.value.split(/\r\n|\r|\n/).map(function (line) { return line.trim(); }).filter(function (line) { return line !== ''; })
                        : [];
                    if (remarkLines.length !== files.length) {
                        valid = false;
                        return;
                    }
                });
                if (!valid) {
                    event.preventDefault();
                    alert('Setiap entry comply wajib: pilih item, isi nama/nomor, upload foto, dan jumlah remark harus sama dengan jumlah foto.');
                    return;
                }
                reindexComplyRows();
            });
        }

        if (window.jQuery) {
            window.jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
                var target = event.target.getAttribute('href') || '';
                if (target.charAt(0) === '#') {
                    if (window.history && typeof window.history.replaceState === 'function') {
                        window.history.replaceState(null, '', target);
                    } else {
                        window.location.hash = target;
                    }
                    observeLazyPhotos(document.querySelector(target));
                    if (target === '#impl-comply-pane') {
                        applyComplyGalleryFilter();
                    }
                }
            });

            if (window.location.hash) {
                var tabTrigger = document.querySelector('a[data-toggle="tab"][href="' + window.location.hash + '"]');
                if (tabTrigger) {
                    window.jQuery(tabTrigger).tab('show');
                }
            }
        }

        if (complyCategoryFilter) {
            complyCategoryFilter.addEventListener('change', applyComplyGalleryFilter);
        }

        if (complySearchFilter) {
            complySearchFilter.addEventListener('input', applyComplyGalleryFilter);
        }

        applyComplyGalleryFilter();
        observeLazyPhotos(document);

        if (window.jQuery && progressModal) {
            window.jQuery(progressModal).on('hidden.bs.modal', function () {
                var form = progressModal.querySelector('form');
                if (form) {
                    form.reset();
                }
                if (progressList) {
                    progressList.innerHTML = '';
                }
                toggleProgressEmptyState();
                bindDropzones();
            });
        }
    })();
</script>
<script>
    (function () {
        var form = document.getElementById('form-daily-activity-builder');
        if (!form) {
            return;
        }

        var selector = form.querySelector('.js-activity-code-selector');
        var detailSelector = form.querySelector('.js-activity-detail-selector');
        var unitInput = form.querySelector('.js-activity-unit');
        var qtyInput = form.querySelector('.js-activity-qty');
        var addButton = form.querySelector('.js-add-daily-activity-row');
        var tbody = form.querySelector('.js-daily-activity-items');
        var template = document.getElementById('daily-activity-item-template');
        var detailOptionsMap = <?= json_encode($activityDetailOptions) ?>;

        function syncUnit() {
            var selected = selector.options[selector.selectedIndex];
            unitInput.value = selected ? (selected.getAttribute('data-default-unit') || '') : '';
            syncDetailOptions();
        }

        function syncDetailOptions() {
            var selected = selector.options[selector.selectedIndex];
            var activityCode = selected ? (selected.value || '') : '';
            var options = detailOptionsMap[activityCode] || [];
            detailSelector.innerHTML = '';
            var forceAutoDetailCodes = ['DIGGING_HOLE', 'COR_FONDATION'];

            if (!activityCode) {
                detailSelector.innerHTML = '<option value="">Pilih kategori dulu</option>';
                return;
            }

            if (forceAutoDetailCodes.indexOf(activityCode) !== -1) {
                detailSelector.innerHTML = '<option value="-">Otomatis (Aktivitas Harian)</option>';
                detailSelector.value = '-';
                return;
            }

            if (!options.length) {
                detailSelector.innerHTML = '<option value="-">Otomatis</option>';
                detailSelector.value = '-';
                return;
            }

            if (options.length === 1) {
                var autoValue = options[0];
                detailSelector.innerHTML = '<option value="' + autoValue.replace(/"/g, '&quot;') + '">' + autoValue + ' (Auto)</option>';
                detailSelector.value = autoValue;
                return;
            }

            var html = '<option value="">Pilih detail</option>';
            options.forEach(function (opt) {
                html += '<option value="' + String(opt).replace(/"/g, '&quot;') + '">' + opt + '</option>';
            });
            detailSelector.innerHTML = html;
        }

        function removeEmptyRow() {
            var emptyRow = tbody.querySelector('.js-empty-row');
            if (emptyRow) {
                emptyRow.remove();
            }
        }

        function ensureEmptyRow() {
            var rows = tbody.querySelectorAll('.js-daily-activity-item');
            if (rows.length > 0 || tbody.querySelector('.js-empty-row')) {
                return;
            }
            var row = document.createElement('tr');
            row.className = 'js-empty-row';
            row.innerHTML = '<td colspan="8" class="text-center text-muted">Belum ada item aktivitas.</td>';
            tbody.appendChild(row);
        }

        function addRow() {
            var selected = selector.options[selector.selectedIndex];
            var activityCode = selected ? (selected.value || '') : '';
            var activityName = selected ? (selected.getAttribute('data-activity-name') || '') : '';
            var boqType = selected ? (selected.getAttribute('data-boq-type') || '') : '';
            var unit = selected ? (selected.getAttribute('data-default-unit') || '') : '';
            var activityDetail = detailSelector ? (detailSelector.value || '') : '';
            var qty = parseFloat(qtyInput.value || '0');

            if (activityCode === '' || qty <= 0) {
                alert('Pilih kategori aktivitas dan isi qty terlebih dahulu.');
                return;
            }
            if (activityDetail === '') {
                alert('Pilih detail aktivitas terlebih dahulu.');
                return;
            }

            removeEmptyRow();
            var fragment = template.content.cloneNode(true);
            var row = fragment.querySelector('.js-daily-activity-item');
            row.querySelector('[data-name="activity_code"]').value = activityCode;
            row.querySelector('[data-name="activity_detail"]').value = activityDetail;
            row.querySelector('[data-name="qty_activity"]').value = qty.toString();
            row.querySelector('.js-col-activity-name').textContent = activityName || activityCode;
            row.querySelector('.js-col-activity-detail').textContent = activityDetail;
            row.querySelector('.js-col-boq-type').textContent = boqType || '-';
            row.querySelector('.js-col-unit').textContent = unit || '-';
            tbody.appendChild(fragment);

            qtyInput.value = '';
        }

        selector.addEventListener('change', syncUnit);
        addButton.addEventListener('click', addRow);

        tbody.addEventListener('click', function (event) {
            var button = event.target.closest('.js-remove-daily-activity-row');
            if (!button) {
                return;
            }
            var row = button.closest('.js-daily-activity-item');
            if (row) {
                row.remove();
            }
            ensureEmptyRow();
        });

        tbody.addEventListener('change', function (event) {
            var fileInput = event.target.closest('[data-photo-input]');
            if (!fileInput) {
                return;
            }

            var row = fileInput.closest('.js-daily-activity-item');
            if (!row) {
                return;
            }

            var previewList = row.querySelector('.js-photo-preview-list');
            var emptyText = row.querySelector('.js-photo-preview-empty');
            if (!previewList || !emptyText) {
                return;
            }

            Array.prototype.forEach.call(previewList.querySelectorAll('img[data-object-url]'), function (img) {
                if (window.URL && typeof window.URL.revokeObjectURL === 'function') {
                    window.URL.revokeObjectURL(img.getAttribute('data-object-url'));
                }
            });
            previewList.innerHTML = '';
            var files = fileInput.files ? Array.prototype.slice.call(fileInput.files) : [];
            if (!files.length) {
                emptyText.classList.remove('d-none');
                return;
            }

            emptyText.classList.add('d-none');
            files.slice(0, 12).forEach(function (file) {
                var item = document.createElement('div');
                item.style.width = '76px';
                item.style.textAlign = 'center';
                item.style.fontSize = '.68rem';
                item.style.color = '#64748b';
                item.style.lineHeight = '1.2';

                var img = document.createElement('img');
                img.style.width = '76px';
                img.style.height = '56px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid #d9e2ef';
                img.style.display = 'block';
                img.style.marginBottom = '.2rem';

                var label = document.createElement('div');
                label.textContent = file.name.length > 18 ? (file.name.slice(0, 15) + '...') : file.name;
                label.title = file.name;

                if (window.URL && typeof window.URL.createObjectURL === 'function') {
                    var objectUrl = window.URL.createObjectURL(file);
                    img.setAttribute('data-object-url', objectUrl);
                    img.onload = function () {
                        if (img.getAttribute('data-object-url')) {
                            window.URL.revokeObjectURL(objectUrl);
                            img.removeAttribute('data-object-url');
                        }
                    };
                    img.src = objectUrl;
                } else {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }

                item.appendChild(img);
                item.appendChild(label);
                previewList.appendChild(item);
            });
            if (files.length > 12) {
                var moreItem = document.createElement('div');
                moreItem.style.width = '76px';
                moreItem.style.minHeight = '56px';
                moreItem.style.border = '1px dashed #cbd5e1';
                moreItem.style.borderRadius = '8px';
                moreItem.style.display = 'flex';
                moreItem.style.alignItems = 'center';
                moreItem.style.justifyContent = 'center';
                moreItem.style.textAlign = 'center';
                moreItem.style.fontSize = '.68rem';
                moreItem.style.color = '#64748b';
                moreItem.textContent = '+' + (files.length - 12) + ' foto';
                previewList.appendChild(moreItem);
            }
        });

        form.addEventListener('submit', function (event) {
            var rows = tbody.querySelectorAll('.js-daily-activity-item');
            if (!rows.length) {
                event.preventDefault();
                alert('Tambahkan minimal 1 item aktivitas ke tabel.');
                return;
            }

            rows.forEach(function (row, index) {
                var codeInput = row.querySelector('[data-name="activity_code"]');
                var detailHidden = row.querySelector('[data-name="activity_detail"]');
                var qtyHidden = row.querySelector('[data-name="qty_activity"]');
                var remarkInput = row.querySelector('[data-remark-input]');
                var fileInput = row.querySelector('[data-photo-input]');

                codeInput.name = 'activity_items[' + index + '][activity_code]';
                detailHidden.name = 'activity_items[' + index + '][activity_detail]';
                qtyHidden.name = 'activity_items[' + index + '][qty_activity]';
                remarkInput.name = 'activity_items[' + index + '][remark_activity]';
                fileInput.name = 'activity_item_photos_' + index + '[]';
            });
        });

        syncUnit();
    })();

    (function () {
        function formatIdNumber(value) {
            var num = parseFloat(value || 0);
            if (!isFinite(num)) {
                num = 0;
            }
            return Math.round(num).toLocaleString('id-ID');
        }

        function formatPercent(plan, achiev) {
            var planNum = parseFloat(plan || 0);
            var achievNum = parseFloat(achiev || 0);
            if (!isFinite(planNum) || planNum <= 0 || !isFinite(achievNum)) {
                return '-';
            }

            var percent = (achievNum / planNum) * 100;
            return percent.toLocaleString('id-ID', {
                minimumFractionDigits: percent % 1 === 0 ? 0 : 1,
                maximumFractionDigits: 1
            }) + '%';
        }

        var scopeButtons = Array.prototype.slice.call(document.querySelectorAll('.js-history-scope-toggle'));
        var scopeTitles = Array.prototype.slice.call(document.querySelectorAll('.js-history-scope-title'));
        var scopeTables = Array.prototype.slice.call(document.querySelectorAll('.js-history-scope-table'));
        var scopeLabel = document.getElementById('history_scope_label');
        var totalPlanNode = document.getElementById('history_total_plan');
        var totalAchievNode = document.getElementById('history_total_achiev');
        var totalGapNode = document.getElementById('history_total_gap');
        var totalPercentNode = document.getElementById('history_total_percent');
        var progressReportLink = document.getElementById('history_progress_report_link');
        var activeScope = 'CLUSTER';

        if (!scopeButtons.length || !scopeTables.length) {
            return;
        }

        function getScopeTable(scope) {
            for (var i = 0; i < scopeTables.length; i++) {
                if (scopeTables[i].getAttribute('data-scope-table') === scope) {
                    return scopeTables[i];
                }
            }
            return null;
        }

        function syncSummary(scope) {
            var tableWrap = getScopeTable(scope);
            if (!tableWrap) {
                return;
            }

            var totalPlan = parseFloat(tableWrap.getAttribute('data-total-plan') || 0);
            var totalAchiev = parseFloat(tableWrap.getAttribute('data-total-achiev') || 0);
            var gap = totalPlan - totalAchiev;

            if (scopeLabel) {
                scopeLabel.textContent = scope === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
            }
            if (totalPlanNode) {
                totalPlanNode.textContent = formatIdNumber(totalPlan);
            }
            if (totalAchievNode) {
                totalAchievNode.textContent = formatIdNumber(totalAchiev);
            }
            if (totalGapNode) {
                totalGapNode.textContent = formatIdNumber(gap);
            }
            if (totalPercentNode) {
                totalPercentNode.textContent = formatPercent(totalPlan, totalAchiev);
            }
        }

        function setScope(scope) {
            activeScope = scope === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';

            scopeButtons.forEach(function (button) {
                var buttonScope = button.getAttribute('data-scope');
                var active = buttonScope === activeScope;
                button.classList.toggle('btn-primary', active);
                button.classList.toggle('btn-outline-primary', !active);
            });

            scopeTitles.forEach(function (title) {
                title.classList.toggle('d-none', title.getAttribute('data-scope-title') !== activeScope);
            });

            scopeTables.forEach(function (tableWrap) {
                tableWrap.classList.toggle('d-none', tableWrap.getAttribute('data-scope-table') !== activeScope);
            });

            if (progressReportLink) {
                var reportBaseUrl = progressReportLink.getAttribute('data-base-url') || progressReportLink.getAttribute('href') || '';
                progressReportLink.setAttribute('href', reportBaseUrl + '?scope=' + encodeURIComponent(activeScope));
            }

            syncSummary(activeScope);
        }

        scopeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setScope(button.getAttribute('data-scope') || 'CLUSTER');
            });
        });

        setScope('CLUSTER');
    })();
</script>
