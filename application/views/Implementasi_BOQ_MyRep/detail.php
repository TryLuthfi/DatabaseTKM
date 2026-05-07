<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

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

$historyTypePlan = [];
$historyDateRows = [];
$historyTypeOrder = [];

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

    $rowHistory = $historyMap[(int) ($row['id_boq_baseline_item'] ?? 0)] ?? [];
    foreach ($rowHistory as $entry) {
        $progressDate = (string) ($entry['progress_date'] ?? '');
        if ($progressDate === '') {
            continue;
        }

        if (!isset($historyDateRows[$progressDate])) {
            $historyDateRows[$progressDate] = [
                'progress_date' => $progressDate,
                'remark' => [],
                'achieve' => [],
            ];
        }

        if (!isset($historyDateRows[$progressDate]['achieve'][$itemType])) {
            $historyDateRows[$progressDate]['achieve'][$itemType] = 0;
        }
        $historyDateRows[$progressDate]['achieve'][$itemType] += (float) ($entry['qty_progress'] ?? 0);

        $remarkValue = trim((string) ($entry['remark_progress'] ?? ''));
        if ($remarkValue !== '') {
            $historyDateRows[$progressDate]['remark'][] = $remarkValue;
        }
    }
}

ksort($historyDateRows);
$historyRows = [];
$historyRunningAchieve = array_fill_keys($historyTypeOrder, 0);
$historyFinalAchieve = array_fill_keys($historyTypeOrder, 0);
$galleryRows = [];
$implementationGalleryGroups = [];
$complyGalleryGroups = [];
$complySelectableItems = [];

if (!empty($historyTypeOrder)) {
    $initialRow = [
        'progress_date' => !empty($cluster['drm_date']) ? (string) $cluster['drm_date'] : (!empty($cluster['boq_approved_at']) ? substr((string) $cluster['boq_approved_at'], 0, 10) : '-'),
        'remark' => 'BOQ Awal',
        'achieve' => array_fill_keys($historyTypeOrder, 0),
    ];
    $historyRows[] = $initialRow;
}

foreach ($historyDateRows as $progressDate => $entry) {
    foreach ($historyTypeOrder as $itemType) {
        $dailyAchieve = (float) ($entry['achieve'][$itemType] ?? 0);
        $historyRunningAchieve[$itemType] += $dailyAchieve;
        $historyFinalAchieve[$itemType] = $historyRunningAchieve[$itemType];
    }

    $historyRows[] = [
        'progress_date' => $progressDate,
        'remark' => !empty($entry['remark']) ? implode(' | ', array_unique($entry['remark'])) : 'Progress Harian',
        'achieve' => $entry['achieve'],
    ];
}

foreach ($compareRows as $row) {
    if (!empty($row['comply_enabled'])) {
        $complySelectableItems[] = $row;
    }

    $baselineItemId = (int) ($row['id_boq_baseline_item'] ?? 0);
    $itemHistoryRows = $historyMap[$baselineItemId] ?? [];
    foreach ($itemHistoryRows as $entry) {
        foreach (($entry['photos'] ?? []) as $photo) {
            $galleryRows[] = [
                'item_name' => (string) ($row['item_name'] ?? '-'),
                'item_type' => (string) ($row['item_type'] ?? '-'),
                'photo_type' => (string) ($row['photo_type'] ?? ''),
                'progress_date' => (string) ($entry['progress_date'] ?? '-'),
                'remark_progress' => (string) ($entry['remark_progress'] ?? ''),
                'file_name' => (string) ($photo['file_name'] ?? 'Foto Progress'),
                'file_path' => (string) ($photo['file_path'] ?? ''),
                'caption' => (string) ($photo['caption'] ?? ''),
                'photo_category' => (string) ($photo['photo_category'] ?? 'HARIAN'),
                'comply_label' => (string) ($photo['comply_label'] ?? ''),
                'id_progress_photo' => (int) ($photo['id_progress_photo'] ?? 0),
                'status_photo' => (string) ($photo['status_photo'] ?? ''),
                'review_remark' => (string) ($photo['review_remark'] ?? ''),
            ];
        }
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

$qtyTargetTotal = (float) ($cluster['target_qty_total'] ?? 0);
$qtyActualTotal = (float) ($cluster['actual_qty_total'] ?? 0);
$photoTargetTotal = (int) ($cluster['target_photo_total'] ?? 0);
$photoUploadedTotal = (int) ($cluster['uploaded_photo_total'] ?? 0);
$itemTotal = (int) ($cluster['total_item'] ?? 0);
$itemDone = 0;

foreach ($compareRows as $row) {
    $rowStatus = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
    if ($rowStatus === 'DONE') {
        $itemDone++;
    }
}

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

    .impl-gallery-section + .impl-gallery-section {
        margin-top: 1.25rem;
    }

    .impl-gallery-section__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .65rem;
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

    .impl-lightbox__body {
        padding: 1rem;
        overflow: auto;
        text-align: center;
        background: #f8fafc;
    }

    .impl-lightbox__stage {
        min-height: 72vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: auto;
    }

    .impl-lightbox__image {
        max-width: none;
        max-height: none;
        width: auto;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
        transition: transform .18s ease;
        transform-origin: center center;
    }

    .impl-lightbox__caption {
        margin-top: .85rem;
        color: #475569;
        font-size: .9rem;
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
                                <div class="impl-progress-card__meta">
                                    <span>Done <?= implHistoryNumber($itemDone, false) ?></span>
                                    <span>Total <?= implHistoryNumber($itemTotal, false) ?></span>
                                </div>
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
                    <h3 class="card-title">Perbandingan BOQ vs Implementasi</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs impl-tabs" id="implCompareTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="impl-history-tab" data-toggle="tab" href="#impl-history-pane" role="tab">History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="impl-breakdown-tab" data-toggle="tab" href="#impl-breakdown-pane" role="tab">Breakdown</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="impl-gallery-tab" data-toggle="tab" href="#impl-gallery-pane" role="tab">Foto Implementasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="impl-comply-tab" data-toggle="tab" href="#impl-comply-pane" role="tab">FOTO COMPLY</a>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <div class="tab-pane fade show active" id="impl-history-pane" role="tabpanel">
                            <div class="impl-history-panel">
                                <div class="impl-history-panel__head">
                                    <div class="impl-history-panel__title">History Progress Cluster</div>
                                    <div class="impl-history-panel__note">Ringkasan plan vs achievement per jenis item dan per tanggal progress.</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered impl-history-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">No</th>
                                                <th rowspan="2">HP DRM</th>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th colspan="2"><?= htmlspecialchars($itemType) ?></th>
                                                <?php endforeach; ?>
                                                <th rowspan="2">Tanggal Progress</th>
                                                <th rowspan="2">Keterangan</th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($historyTypeOrder as $itemType): ?>
                                                    <th>PLAN</th>
                                                    <th>ACHIEV</th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historyRows as $index => $historyRow): ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= $index === 0 ? implHistoryNumber((float) ($cluster['homepass_drm'] ?? 0), false) : '-' ?></td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td><?= $index === 0 ? implHistoryNumber((float) ($historyTypePlan[$itemType] ?? 0)) : '-' ?></td>
                                                        <td><?= implHistoryNumber((float) ($historyRow['achieve'][$itemType] ?? 0)) ?></td>
                                                    <?php endforeach; ?>
                                                    <td><?= htmlspecialchars((string) ($historyRow['progress_date'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($historyRow['remark'] ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($historyRows)): ?>
                                                <tr><td colspan="<?= 4 + (count($historyTypeOrder) * 2) ?>" class="text-center text-muted">Belum ada history implementasi.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <?php if (!empty($historyTypeOrder)): ?>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">Total</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td><?= implHistoryNumber((float) ($historyTypePlan[$itemType] ?? 0)) ?></td>
                                                        <td><?= implHistoryNumber((float) ($historyFinalAchieve[$itemType] ?? 0)) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">Selisih</td>
                                                    <?php foreach ($historyTypeOrder as $itemType): ?>
                                                        <td colspan="2"><?= implHistoryNumber((float) (($historyTypePlan[$itemType] ?? 0) - ($historyFinalAchieve[$itemType] ?? 0))) ?></td>
                                                    <?php endforeach; ?>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="impl-breakdown-pane" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                <div>
                                    <div class="font-weight-bold text-dark">Breakdown Implementasi per Item</div>
                                    <div class="small text-muted">Input beberapa item sekaligus dalam satu tanggal progress untuk mempercepat pelaporan lapangan.</div>
                                </div>
                                <button type="button" class="btn btn-primary js-open-progress-modal" data-toggle="modal" data-target="#modal-progress">
                                    <i class="fas fa-layer-group mr-1"></i>Input Progress Sekaligus
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>Qty BOQ</th>
                                            <th>Qty Actual</th>
                                            <th>Sisa Qty</th>
                                            <th>Target Foto</th>
                                            <th>Foto Upload</th>
                                            <th>Status</th>
                                            <th>Last Progress</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($compareRows as $index => $row): ?>
                                            <?php
                                            $status = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
                                            $badgeClass = $status === 'DONE' ? 'success' : ($status === 'ON PROGRESS' ? 'warning' : 'secondary');
                                            $historyRows = $historyMap[(int) ($row['id_boq_baseline_item'] ?? 0)] ?? [];
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($row['item_name'] ?? '-')) ?></strong>
                                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['excel_item_name'] ?? '-')) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($row['item_type'] ?? '-')) ?></td>
                                                <td><?= implHistoryNumber((float) ($row['qty_boq'] ?? 0)) ?></td>
                                                <td><?= implHistoryNumber((float) ($row['progress_qty'] ?? 0)) ?></td>
                                                <td><?= implHistoryNumber((float) ($row['remaining_qty'] ?? 0)) ?></td>
                                                <td>
                                                    <?= implHistoryNumber((int) (($row['target_foto_required'] ?? 0) + ($row['target_comply_photo_required'] ?? 0))) ?>
                                                    <div class="small text-muted">
                                                        Harian <?= implHistoryNumber((int) ($row['target_foto_required'] ?? 0)) ?>
                                                        <?php if ((int) ($row['target_comply_photo_required'] ?? 0) > 0): ?>
                                                            | Comply <?= implHistoryNumber((int) ($row['target_comply_photo_required'] ?? 0)) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?= implHistoryNumber((int) ($row['uploaded_photos'] ?? 0)) ?></td>
                                                <td><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                                                <td><?= !empty($row['last_progress_date']) ? htmlspecialchars((string) $row['last_progress_date']) : '-' ?></td>
                                                <td style="min-width: 220px;">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary js-open-progress-modal"
                                                        data-toggle="modal"
                                                        data-target="#modal-progress"
                                                        data-baseline-item-id="<?= (int) $row['id_boq_baseline_item'] ?>"
                                                        data-item-name="<?= htmlspecialchars((string) ($row['item_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-item-type="<?= htmlspecialchars((string) ($row['item_type'] ?? ''), ENT_QUOTES) ?>"
                                                        data-qty-target="<?= htmlspecialchars((string) implHistoryNumber((float) ($row['qty_boq'] ?? 0)), ENT_QUOTES) ?>"
                                                        data-photo-target="<?= (int) ($row['target_foto_required'] ?? 0) ?>"
                                                        data-comply-enabled="<?= !empty($row['comply_enabled']) ? '1' : '0' ?>"
                                                        data-comply-mode="<?= htmlspecialchars((string) ($row['comply_entry_limit_mode'] ?? 'NONE'), ENT_QUOTES) ?>"
                                                        data-comply-photo-per-label="<?= (int) ($row['comply_photo_per_label'] ?? 0) ?>"
                                                        data-comply-label-prefix="<?= htmlspecialchars((string) ($row['comply_label_prefix'] ?? ($row['item_name'] ?? 'Item')), ENT_QUOTES) ?>"
                                                        data-comply-label-placeholder="<?= htmlspecialchars((string) ($row['comply_label_placeholder'] ?? 'Nama / nomor item comply'), ENT_QUOTES) ?>"
                                                        data-comply-requirement-text="<?= htmlspecialchars((string) ($row['comply_requirement_text'] ?? ''), ENT_QUOTES) ?>">
                                                        Input Progress
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-dark js-open-history-modal"
                                                        data-toggle="modal"
                                                        data-target="#modal-history"
                                                        data-item-name="<?= htmlspecialchars((string) ($row['item_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-history='<?= htmlspecialchars(json_encode($historyRows), ENT_QUOTES) ?>'>
                                                        History
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($compareRows)): ?>
                                            <tr><td colspan="11" class="text-center text-muted">Belum ada baseline BOQ aktif.</td></tr>
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
                                                                        <a href="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" class="impl-gallery-photo-card js-open-lightbox" data-image="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" data-title="<?= htmlspecialchars((string) ($galleryItem['item_name'] ?? '-'), ENT_QUOTES) ?>" data-caption="<?= htmlspecialchars((string) (($photo['caption'] ?? '') !== '' ? $photo['caption'] : ($photo['file_name'] ?? 'Foto Progress')), ENT_QUOTES) ?>">
                                                                            <img src="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" alt="<?= htmlspecialchars((string) ($photo['file_name'] ?? 'Foto Progress')) ?>">
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
                                            <div class="impl-comply-upload-card__note">Pilih item, isi nama / nomor, lalu upload foto comply untuk approval HO.</div>
                                        </div>
                                        <a href="<?= base_url('Implementasi_BOQ_MyRep/printComplyPdf/' . (int) ($cluster['id_myrep_cluster'] ?? 0)) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                                            <i class="fas fa-file-pdf mr-1"></i>Preview PDF Foto Comply
                                        </a>
                                    </div>
                                    <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/uploadComplyPhoto') ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Item Comply</label>
                                                    <select name="baseline_item_id" class="form-control js-comply-upload-item" required>
                                                        <option value="">Pilih item comply</option>
                                                        <?php foreach ($complySelectableItems as $row): ?>
                                                            <option
                                                                value="<?= (int) ($row['id_boq_baseline_item'] ?? 0) ?>"
                                                                data-comply-photo-per-label="<?= (int) ($row['comply_photo_per_label'] ?? 1) ?>"
                                                                data-comply-label-prefix="<?= htmlspecialchars((string) ($row['comply_label_prefix'] ?? ($row['item_name'] ?? 'Item')), ENT_QUOTES) ?>"
                                                                data-comply-label-placeholder="<?= htmlspecialchars((string) ($row['comply_label_placeholder'] ?? 'Nama / nomor item comply'), ENT_QUOTES) ?>"
                                                                data-comply-requirement-text="<?= htmlspecialchars((string) ($row['comply_requirement_text'] ?? ''), ENT_QUOTES) ?>">
                                                                <?= htmlspecialchars((string) ($row['item_name'] ?? '-')) ?><?= !empty($row['item_type']) ? ' - ' . htmlspecialchars((string) ($row['item_type'] ?? '-')) : '' ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Nama / Nomor</label>
                                                    <input type="text" name="comply_label" class="form-control js-comply-upload-label" placeholder="Pilih item terlebih dahulu" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Aturan Foto</label>
                                                    <div class="impl-comply-hint js-comply-upload-hint">Pilih item dulu untuk melihat requirement comply.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Foto Comply</label>
                                            <div class="impl-dropzone js-dropzone">
                                                <input type="file" name="comply_photos_single[]" class="js-dropzone-input" multiple accept=".jpg,.jpeg,.png,.webp" required>
                                                <div class="impl-dropzone-content">
                                                    <div class="mb-2"><i class="fas fa-camera-retro fa-2x text-primary"></i></div>
                                                    <div class="font-weight-bold">Upload foto comply</div>
                                                    <div class="text-muted small">Pilih beberapa foto sesuai aturan item comply</div>
                                                    <div class="impl-dropzone-file js-dropzone-label">Belum ada foto comply dipilih</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary btn-sm">Upload Foto Comply</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <?php if (!empty($complyGalleryGroups)): ?>
                                <?php foreach ($complyGalleryGroups as $galleryType => $galleryItems): ?>
                                    <div class="impl-gallery-section">
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
                                                        ?>
                                                        <tr>
                                                            <td class="impl-gallery-table__no"><?= $galleryIndex++ ?></td>
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
                                                                        ?>
                                                                        <div class="impl-gallery-photo-card--shell">
                                                                            <a href="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" class="impl-gallery-photo-card js-open-lightbox" data-image="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" data-title="<?= htmlspecialchars((string) (($galleryItem['item_name'] ?? '-') . ' - ' . ($galleryItem['comply_label'] ?? '-')), ENT_QUOTES) ?>" data-caption="<?= htmlspecialchars($photoCaption, ENT_QUOTES) ?>">
                                                                                <img src="<?= base_url() . ltrim((string) ($photo['file_path'] ?? ''), '/') ?>" alt="<?= htmlspecialchars((string) ($photo['file_name'] ?? 'Foto Comply')) ?>">
                                                                            </a>
                                                                            <div class="impl-gallery-photo-card__meta">
                                                                                <div class="small font-weight-bold text-dark mb-2"><?= htmlspecialchars($photoCaption) ?></div>
                                                                                <div class="mb-2">
                                                                                    <span class="badge badge-<?= $photoBadgeClass ?>"><?= htmlspecialchars($photoStatus) ?></span>
                                                                                </div>
                                                                                <?php if (!empty($photo['review_remark'])): ?>
                                                                                    <div class="small text-muted mb-2">Review: <?= htmlspecialchars((string) $photo['review_remark']) ?></div>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($canApprove)): ?>
                                                                                    <div>
                                                                                        <?php if ($photoStatus === 'UPLOADED' || $photoStatus === 'REJECTED'): ?>
                                                                                            <button
                                                                                                type="button"
                                                                                                class="btn btn-sm btn-outline-success mr-1 js-open-comply-approve"
                                                                                                data-toggle="modal"
                                                                                                data-target="#modal-comply-approve"
                                                                                                data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>"
                                                                                                data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>">
                                                                                                Approve
                                                                                            </button>
                                                                                        <?php endif; ?>
                                                                                        <?php if ($photoStatus === 'UPLOADED' || $photoStatus === 'APPROVED'): ?>
                                                                                            <button
                                                                                                type="button"
                                                                                                class="btn btn-sm btn-outline-danger js-open-comply-reject"
                                                                                                data-toggle="modal"
                                                                                                data-target="#modal-comply-reject"
                                                                                                data-photo-id="<?= (int) ($photo['id_progress_photo'] ?? 0) ?>"
                                                                                                data-photo-label="<?= htmlspecialchars($photoLabelForAction, ENT_QUOTES) ?>">
                                                                                                Reject
                                                                                            </button>
                                                                                        <?php endif; ?>
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
                                                    data-comply-enabled="<?= !empty($row['comply_enabled']) ? '1' : '0' ?>"
                                                    data-comply-mode="<?= htmlspecialchars((string) ($row['comply_entry_limit_mode'] ?? 'NONE'), ENT_QUOTES) ?>"
                                                    data-comply-photo-per-label="<?= (int) ($row['comply_photo_per_label'] ?? 0) ?>"
                                                    data-comply-label-prefix="<?= htmlspecialchars((string) ($row['comply_label_prefix'] ?? ($row['item_name'] ?? 'Item')), ENT_QUOTES) ?>"
                                                    data-comply-label-placeholder="<?= htmlspecialchars((string) ($row['comply_label_placeholder'] ?? 'Nama / nomor item comply'), ENT_QUOTES) ?>"
                                                    data-comply-requirement-text="<?= htmlspecialchars((string) ($row['comply_requirement_text'] ?? ''), ENT_QUOTES) ?>"
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
                            <div class="alert alert-light border mt-3 mb-0 js-progress-empty-state">Belum ada item dipilih. Tambahkan item seperti Tiang, FAT, ODC, atau item lainnya lalu isi qty dan foto masing-masing.</div>
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
                                <div class="impl-comply-box mt-3 js-comply-box d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="font-weight-bold text-dark">Foto Comply</div>
                                            <div class="small text-muted js-comply-requirement-text">Aturan comply akan muncul di sini.</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary js-add-comply-entry d-none">
                                            <i class="fas fa-plus-circle mr-1"></i>Tambah Entry Comply
                                        </button>
                                    </div>
                                    <div class="js-comply-entry-list"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template id="comply-entry-template">
                        <div class="border rounded p-3 mb-3 js-comply-entry-card" data-entry-index="">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label class="js-comply-label-title">Nomor / Nama Tiang</label>
                                        <input type="text" class="form-control js-comply-label-input" value="">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group mb-0">
                                        <label class="js-comply-photo-label">Foto Comply</label>
                                        <div class="impl-dropzone js-dropzone">
                                            <input type="file" class="js-dropzone-input js-comply-photo-input" multiple accept=".jpg,.jpeg,.png,.webp">
                                            <div class="impl-dropzone-content">
                                                <div class="mb-2"><i class="fas fa-camera-retro fa-2x text-primary"></i></div>
                                                <div class="font-weight-bold">Upload foto comply</div>
                                                <div class="text-muted small">Pilih satu atau beberapa foto sesuai requirement item</div>
                                                <div class="impl-dropzone-file js-dropzone-label">Belum ada foto comply dipilih</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-link text-danger p-0 js-remove-comply-entry">Hapus Entry</button>
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

<div class="modal fade" id="modal-history" tabindex="-1" role="dialog" aria-hidden="true">
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

<?php if (!empty($canApprove)): ?>
    <div class="modal fade" id="modal-comply-approve" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/approveComplyPhoto') ?>">
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
                <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/rejectComplyPhoto') ?>">
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

<form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/deleteProgress') ?>" id="form-delete-progress" class="d-none">
    <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
    <input type="hidden" name="progress_item_id" id="delete_progress_item_id" value="">
</form>

<div class="impl-lightbox" id="impl-lightbox" aria-hidden="true">
    <div class="impl-lightbox__dialog">
        <div class="impl-lightbox__head">
            <div class="impl-lightbox__title" id="impl-lightbox-title">Preview Foto</div>
            <div class="impl-lightbox__toolbar">
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-prev" aria-label="Sebelumnya">&#8249;</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-zoom-out" aria-label="Zoom Out">-</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-zoom-in" aria-label="Zoom In">+</button>
                <button type="button" class="impl-lightbox__action" id="impl-lightbox-next" aria-label="Berikutnya">&#8250;</button>
                <button type="button" class="impl-lightbox__close" id="impl-lightbox-close" aria-label="Tutup">&times;</button>
            </div>
        </div>
        <div class="impl-lightbox__body">
            <div class="impl-lightbox__stage">
                <img src="" alt="Preview Foto" class="impl-lightbox__image" id="impl-lightbox-image">
            </div>
            <div class="impl-lightbox__caption" id="impl-lightbox-caption">-</div>
        </div>
    </div>
</div>

<script>
    (function () {
        var canApproveComplyPhoto = <?= !empty($canApprove) ? 'true' : 'false' ?>;
        var progressModal = document.getElementById('modal-progress');
        var progressSelector = document.querySelector('.js-progress-item-selector');
        var progressAddButton = document.querySelector('.js-add-progress-item');
        var progressList = document.querySelector('.js-progress-item-list');
        var progressEmptyState = document.querySelector('.js-progress-empty-state');
        var progressCardTemplate = document.getElementById('progress-item-card-template');
        var complyEntryTemplate = document.getElementById('comply-entry-template');
        var deleteProgressForm = document.getElementById('form-delete-progress');
        var deleteProgressInput = document.getElementById('delete_progress_item_id');
        var complyUploadItem = document.querySelector('.js-comply-upload-item');
        var complyUploadLabel = document.querySelector('.js-comply-upload-label');
        var complyUploadHint = document.querySelector('.js-comply-upload-hint');

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

        function syncStandaloneComplyForm() {
            if (!complyUploadItem || !complyUploadLabel || !complyUploadHint) {
                return;
            }

            var selectedOption = complyUploadItem.options[complyUploadItem.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                complyUploadLabel.placeholder = 'Pilih item terlebih dahulu';
                complyUploadHint.textContent = 'Pilih item dulu untuk melihat requirement comply.';
                return;
            }

            complyUploadLabel.placeholder = selectedOption.getAttribute('data-comply-label-placeholder') || 'Nama / nomor item comply';
            complyUploadHint.textContent = selectedOption.getAttribute('data-comply-requirement-text') || 'Upload foto comply sesuai aturan item.';
        }

        function toggleProgressEmptyState() {
            if (!progressEmptyState || !progressList) {
                return;
            }

            progressEmptyState.style.display = progressList.querySelectorAll('.js-progress-item-card').length > 0 ? 'none' : '';
        }

        function padComplyNumber(value) {
            return String(value).padStart(3, '0');
        }

        function createComplyEntry(card, entryIndex, presetLabel) {
            if (!complyEntryTemplate || !card) {
                return null;
            }

            var baselineItemId = card.getAttribute('data-baseline-item-id') || '';
            var complyPrefix = card.getAttribute('data-comply-label-prefix') || 'Item';
            var complyPlaceholder = card.getAttribute('data-comply-label-placeholder') || 'Nama / nomor item comply';
            var complyLabelTitle = complyPrefix.indexOf('Tiang') !== -1 || complyPrefix.indexOf('Pole') !== -1
                ? 'Nomor / Nama Tiang'
                : 'Nomor / Nama Item';
            var entryCard = complyEntryTemplate.content.firstElementChild.cloneNode(true);
            var labelInput = entryCard.querySelector('.js-comply-label-input');
            var photoInput = entryCard.querySelector('.js-comply-photo-input');

            entryCard.setAttribute('data-entry-index', String(entryIndex));
            entryCard.querySelector('.js-comply-label-title').textContent = complyLabelTitle;
            labelInput.name = 'comply_labels[' + baselineItemId + '][' + entryIndex + ']';
            labelInput.placeholder = complyPlaceholder;
            labelInput.value = presetLabel || (complyPrefix + ' ' + padComplyNumber(entryIndex + 1));
            photoInput.name = 'comply_photos[' + baselineItemId + '][' + entryIndex + '][]';

            return entryCard;
        }

        function syncComplyEntryIndexes(card) {
            if (!card) {
                return;
            }

            var baselineItemId = card.getAttribute('data-baseline-item-id') || '';
            var complyPrefix = card.getAttribute('data-comply-label-prefix') || 'Item';
            var entryCards = card.querySelectorAll('.js-comply-entry-card');
            Array.prototype.forEach.call(entryCards, function (entryCard, index) {
                entryCard.setAttribute('data-entry-index', String(index));
                var labelInput = entryCard.querySelector('.js-comply-label-input');
                var photoInput = entryCard.querySelector('.js-comply-photo-input');
                if (labelInput) {
                    labelInput.name = 'comply_labels[' + baselineItemId + '][' + index + ']';
                    if (!labelInput.value) {
                        labelInput.value = complyPrefix + ' ' + padComplyNumber(index + 1);
                    }
                }
                if (photoInput) {
                    photoInput.name = 'comply_photos[' + baselineItemId + '][' + index + '][]';
                }
            });
        }

        function syncComplyEntriesForQty(card) {
            if (!card || card.getAttribute('data-comply-enabled') !== '1') {
                return;
            }

            var mode = (card.getAttribute('data-comply-mode') || 'NONE').toUpperCase();
            if (mode !== 'MATCH_QTY') {
                return;
            }

            var qtyInput = card.querySelector('.js-progress-qty-input');
            var entryList = card.querySelector('.js-comply-entry-list');
            if (!qtyInput || !entryList) {
                return;
            }

            var requiredEntries = Math.max(0, Math.ceil(parseFloat(qtyInput.value || '0')));
            var currentEntries = entryList.querySelectorAll('.js-comply-entry-card').length;

            while (currentEntries < requiredEntries) {
                var newEntry = createComplyEntry(card, currentEntries);
                if (newEntry) {
                    entryList.appendChild(newEntry);
                    currentEntries++;
                } else {
                    break;
                }
            }

            while (currentEntries > requiredEntries) {
                var lastEntry = entryList.querySelector('.js-comply-entry-card:last-child');
                if (lastEntry) {
                    lastEntry.remove();
                    currentEntries--;
                } else {
                    break;
                }
            }

            syncComplyEntryIndexes(card);
            bindDropzones();
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
            var complyEnabled = option.getAttribute('data-comply-enabled') === '1';
            var complyMode = option.getAttribute('data-comply-mode') || 'NONE';
            var complyPhotoPerLabel = option.getAttribute('data-comply-photo-per-label') || '0';
            var complyLabelPrefix = option.getAttribute('data-comply-label-prefix') || itemName;
            var complyLabelPlaceholder = option.getAttribute('data-comply-label-placeholder') || 'Nama / nomor item comply';
            var complyRequirementText = option.getAttribute('data-comply-requirement-text') || '';

            card.setAttribute('data-baseline-item-id', baselineItemId);
            card.setAttribute('data-comply-enabled', complyEnabled ? '1' : '0');
            card.setAttribute('data-comply-mode', complyMode);
            card.setAttribute('data-comply-photo-per-label', complyPhotoPerLabel);
            card.setAttribute('data-comply-label-prefix', complyLabelPrefix);
            card.setAttribute('data-comply-label-placeholder', complyLabelPlaceholder);
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

            var complyBox = card.querySelector('.js-comply-box');
            var complyEntryList = card.querySelector('.js-comply-entry-list');
            var complyText = card.querySelector('.js-comply-requirement-text');
            var complyAddButton = card.querySelector('.js-add-comply-entry');
            if (complyEnabled && complyBox && complyEntryList && complyText) {
                complyBox.classList.remove('d-none');
                complyText.textContent = complyRequirementText;
                if (String(complyMode).toUpperCase() === 'FLEXIBLE' && complyAddButton) {
                    complyAddButton.classList.remove('d-none');
                    var initialEntry = createComplyEntry(card, 0);
                    if (initialEntry) {
                        complyEntryList.appendChild(initialEntry);
                    }
                } else if (complyAddButton) {
                    complyAddButton.classList.add('d-none');
                }
            }

            progressList.appendChild(card);
            syncComplyEntriesForQty(card);
            bindDropzones();
            toggleProgressEmptyState();
        }

        bindDropzones();
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
        var lightboxItems = [];
        var lightboxIndex = -1;
        var lightboxScale = 1;

        function escapeAttr(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function syncLightboxButtons() {
            if (!lightboxPrev || !lightboxNext) {
                return;
            }

            var hasMultiple = lightboxItems.length > 1;
            lightboxPrev.disabled = !hasMultiple;
            lightboxNext.disabled = !hasMultiple;
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
            lightboxImage.src = activeItem.image || '';
            lightboxTitle.textContent = activeItem.title || 'Preview Foto';
            lightboxCaption.textContent = activeItem.caption || '-';
            lightboxScale = 1;
            lightboxImage.style.transform = 'scale(1)';
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
                    image: node.getAttribute('data-image') || '',
                    title: node.getAttribute('data-title') || 'Preview Foto',
                    caption: node.getAttribute('data-caption') || '-',
                });
            });

            if (!lightboxItems.length) {
                lightboxItems.push({
                    image: imageUrl || '',
                    title: title || 'Preview Foto',
                    caption: caption || '-',
                });
            }

            var foundIndex = 0;
            for (var i = 0; i < lightboxItems.length; i++) {
                if (lightboxItems[i].image === (imageUrl || '') && lightboxItems[i].caption === (caption || '-')) {
                    foundIndex = i;
                    break;
                }
            }

            renderLightbox(foundIndex);
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lightbox || !lightboxImage) {
                return;
            }

            lightbox.classList.remove('is-open');
            lightboxImage.src = '';
            document.body.style.overflow = '';
            lightboxItems = [];
            lightboxIndex = -1;
            lightboxScale = 1;
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
                lightboxScale = Math.min(3, lightboxScale + 0.25);
                lightboxImage.style.transform = 'scale(' + lightboxScale + ')';
            });
        }

        if (lightboxZoomOut) {
            lightboxZoomOut.addEventListener('click', function () {
                if (!lightboxImage || !lightbox.classList.contains('is-open')) {
                    return;
                }
                lightboxScale = Math.max(0.5, lightboxScale - 0.25);
                lightboxImage.style.transform = 'scale(' + lightboxScale + ')';
            });
        }

        if (lightbox) {
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && lightbox && lightbox.classList.contains('is-open')) {
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
                lightboxScale = Math.min(3, lightboxScale + 0.25);
                lightboxImage.style.transform = 'scale(' + lightboxScale + ')';
            } else if (event.key === '-') {
                event.preventDefault();
                lightboxScale = Math.max(0.5, lightboxScale - 0.25);
                lightboxImage.style.transform = 'scale(' + lightboxScale + ')';
            }
        });

        document.addEventListener('click', function (event) {
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
                        var photoStatus = (photo.status_photo || ((photo.photo_category || 'HARIAN').toUpperCase() === 'COMPLY' ? 'UPLOADED' : 'APPROVED'));
                        if (photo.comply_label) {
                            photoCaption = photoCategory + ' - ' + photo.comply_label;
                        } else {
                            photoCaption = photoCategory + ' - ' + photoCaption;
                        }
                        html += '<div class="impl-history-modal-photo">';
                        html += '<a href="<?= base_url() ?>' + (photo.file_path || '') + '" class="js-open-lightbox d-block" data-image="<?= base_url() ?>' + (photo.file_path || '') + '" data-title="' + escapeAttr(historyButton.getAttribute('data-item-name') || 'Preview Foto') + '" data-caption="' + escapeAttr(photoCaption) + '">';
                        html += '<img src="<?= base_url() ?>' + (photo.file_path || '') + '" alt="' + (photo.file_name || 'Foto Progress') + '">';
                        html += '<div>' + photoCaption + '</div>';
                        html += '</a>';
                        html += '<div class="small mt-1"><span class="badge badge-' + (photoStatus === 'APPROVED' ? 'success' : (photoStatus === 'REJECTED' ? 'danger' : 'warning')) + '">' + photoStatus + '</span></div>';
                        if (photo.review_remark) {
                            html += '<div class="small text-muted mt-1">Review: ' + photo.review_remark + '</div>';
                        }
                        if (canApproveComplyPhoto && (photo.photo_category || '').toUpperCase() === 'COMPLY') {
                            html += '<div class="mt-2">';
                            if (photoStatus === 'UPLOADED' || photoStatus === 'REJECTED') {
                                html += '<button type="button" class="btn btn-sm btn-outline-success mr-1 js-open-comply-approve" data-toggle="modal" data-target="#modal-comply-approve" data-photo-id="' + (photo.id_progress_photo || 0) + '" data-photo-label="' + escapeAttr(photoCaption) + '">Approve</button>';
                            }
                            if (photoStatus === 'UPLOADED' || photoStatus === 'APPROVED') {
                                html += '<button type="button" class="btn btn-sm btn-outline-danger js-open-comply-reject" data-toggle="modal" data-target="#modal-comply-reject" data-photo-id="' + (photo.id_progress_photo || 0) + '" data-photo-label="' + escapeAttr(photoCaption) + '">Reject</button>';
                            }
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
        });

        document.addEventListener('click', function (event) {
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

                var addComplyButton = event.target.closest('.js-add-comply-entry');
                if (addComplyButton) {
                    var complyCard = addComplyButton.closest('.js-progress-item-card');
                    var complyList = complyCard ? complyCard.querySelector('.js-comply-entry-list') : null;
                    if (complyCard && complyList) {
                        var entryIndex = complyList.querySelectorAll('.js-comply-entry-card').length;
                        var newEntry = createComplyEntry(complyCard, entryIndex);
                        if (newEntry) {
                            complyList.appendChild(newEntry);
                            syncComplyEntryIndexes(complyCard);
                            bindDropzones();
                        }
                    }
                    return;
                }

                var removeComplyButton = event.target.closest('.js-remove-comply-entry');
                if (removeComplyButton) {
                    var complyEntryCard = removeComplyButton.closest('.js-comply-entry-card');
                    var complyParentCard = removeComplyButton.closest('.js-progress-item-card');
                    if (complyEntryCard) {
                        complyEntryCard.remove();
                    }
                    if (complyParentCard) {
                        syncComplyEntryIndexes(complyParentCard);
                    }
                }
            });

            progressList.addEventListener('input', function (event) {
                var qtyInput = event.target.closest('.js-progress-qty-input');
                if (!qtyInput) {
                    return;
                }

                var parentCard = qtyInput.closest('.js-progress-item-card');
                if (parentCard) {
                    syncComplyEntriesForQty(parentCard);
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
                var complyEnabled = card.getAttribute('data-comply-enabled') === '1';
                var complyMode = (card.getAttribute('data-comply-mode') || 'NONE').toUpperCase();
                var complyPhotoPerLabel = parseInt(card.getAttribute('data-comply-photo-per-label') || '0', 10);
                var complyEntries = card.querySelectorAll('.js-comply-entry-card');
                var complyValidEntries = 0;

                if (qtyValue <= 0 || !hasPhotos) {
                    isValid = false;
                    return;
                }

                if (complyEnabled) {
                    Array.prototype.forEach.call(complyEntries, function (entryCard) {
                        var labelInput = entryCard.querySelector('.js-comply-label-input');
                        var complyPhotoInput = entryCard.querySelector('.js-comply-photo-input');
                        var hasComplyFiles = complyPhotoInput && complyPhotoInput.files && complyPhotoInput.files.length > 0;
                        var labelValue = labelInput ? String(labelInput.value || '').trim() : '';

                        if (!labelValue && !hasComplyFiles) {
                            return;
                        }

                        if (!labelValue || !hasComplyFiles || complyPhotoInput.files.length < Math.max(complyPhotoPerLabel, 1)) {
                            isValid = false;
                            return;
                        }

                        complyValidEntries++;
                    });

                    if (complyMode === 'MATCH_QTY' && complyValidEntries !== Math.ceil(qtyValue)) {
                        isValid = false;
                    }

                    if (complyMode === 'FLEXIBLE' && complyValidEntries <= 0) {
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Setiap item wajib memiliki qty progress, foto implementasi harian, dan foto comply sesuai aturan item.');
            }
        });

        if (complyUploadItem) {
            complyUploadItem.addEventListener('change', syncStandaloneComplyForm);
            syncStandaloneComplyForm();
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
                }
            });

            if (window.location.hash) {
                var tabTrigger = document.querySelector('a[data-toggle="tab"][href="' + window.location.hash + '"]');
                if (tabTrigger) {
                    window.jQuery(tabTrigger).tab('show');
                }
            }
        }

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
