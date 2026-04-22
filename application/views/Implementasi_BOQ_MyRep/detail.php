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
$galleryGroups = [];

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
            ];
        }
    }
}

foreach ($galleryRows as $galleryRow) {
    $galleryType = strtoupper(trim((string) ($galleryRow['item_type'] ?? 'LAINNYA')));
    if ($galleryType === '') {
        $galleryType = 'LAINNYA';
    }

    $galleryKey = $galleryType . '||' . (string) ($galleryRow['item_name'] ?? '-');
    if (!isset($galleryGroups[$galleryType])) {
        $galleryGroups[$galleryType] = [];
    }

    if (!isset($galleryGroups[$galleryType][$galleryKey])) {
        $galleryGroups[$galleryType][$galleryKey] = [
            'item_name' => (string) ($galleryRow['item_name'] ?? '-'),
            'item_type' => $galleryType,
            'photo_type' => (string) ($galleryRow['photo_type'] ?? ''),
            'dates' => [],
            'remarks' => [],
            'photos' => [],
        ];
    }

    if (!empty($galleryRow['progress_date']) && $galleryRow['progress_date'] !== '-') {
        $galleryGroups[$galleryType][$galleryKey]['dates'][] = (string) $galleryRow['progress_date'];
    }

    $remarkText = trim((string) (($galleryRow['caption'] ?? '') !== '' ? $galleryRow['caption'] : ($galleryRow['remark_progress'] ?? '')));
    if ($remarkText !== '') {
        $galleryGroups[$galleryType][$galleryKey]['remarks'][] = $remarkText;
    }

    $galleryGroups[$galleryType][$galleryKey]['photos'][] = [
        'file_name' => (string) ($galleryRow['file_name'] ?? 'Foto Progress'),
        'file_path' => (string) ($galleryRow['file_path'] ?? ''),
        'caption' => (string) ($galleryRow['caption'] ?? ''),
    ];
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
        background: #f8fafc;
        border: 1px solid #e5edf6;
        border-radius: 14px;
        padding: 1rem;
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

                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Tanggal DRM</strong><div><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Status DRM</strong><div><?= !empty($cluster['status_drm']) ? htmlspecialchars((string) $cluster['status_drm']) : '-' ?></div></div>
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
                            <a class="nav-link" id="impl-gallery-tab" data-toggle="tab" href="#impl-gallery-pane" role="tab">Galeri Foto</a>
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
                                                <td><?= implHistoryNumber((int) ($row['target_foto_required'] ?? 0)) ?></td>
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
                                                        data-item-type="<?= htmlspecialchars((string) ($row['item_type'] ?? ''), ENT_QUOTES) ?>">
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
                            <?php if (!empty($galleryGroups)): ?>
                                <?php foreach ($galleryGroups as $galleryType => $galleryItems): ?>
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
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-progress" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Implementasi_BOQ_MyRep/saveProgress') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_boq_baseline_item" id="progress_baseline_item_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Input Progress Implementasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Cluster</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Item</label>
                                <input type="text" class="form-control" id="progress_item_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jenis</label>
                                <input type="text" class="form-control" id="progress_item_type" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Progress</label>
                                <input type="date" name="progress_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Qty Progress</label>
                                <input type="number" step="0.01" min="0.01" name="qty_progress" class="form-control" required>
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jumlah Foto</label>
                                <input type="text" class="form-control" value="Bisa upload multiple" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Remark Progress</label>
                        <textarea name="remark_progress" class="form-control" rows="3" placeholder="Catatan progress harian"></textarea>
                    </div>
                    <div class="impl-dropzone js-dropzone">
                        <input type="file" name="photos[]" class="js-dropzone-input" multiple accept=".jpg,.jpeg,.png,.webp">
                        <div class="impl-dropzone-content">
                            <div class="mb-2"><i class="fas fa-images fa-2x text-success"></i></div>
                            <div class="font-weight-bold">Drag & drop foto progress di sini</div>
                            <div class="text-muted small">Atau klik area ini untuk memilih beberapa foto</div>
                            <div class="impl-dropzone-file js-dropzone-label">Belum ada foto dipilih</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Progress</button>
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

        bindDropzones();

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
                document.getElementById('progress_baseline_item_id').value = progressButton.getAttribute('data-baseline-item-id') || '';
                document.getElementById('progress_item_name').value = progressButton.getAttribute('data-item-name') || '';
                document.getElementById('progress_item_type').value = progressButton.getAttribute('data-item-type') || '';
                return;
            }

            var historyButton = event.target.closest('.js-open-history-modal');
            if (!historyButton) {
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
            html += '<thead><tr><th style="width:60px;">No</th><th style="width:120px;">Tanggal</th><th style="width:90px;">Qty</th><th style="width:140px;">Status</th><th style="width:140px;">User</th><th>Remark</th><th style="min-width:240px;">Foto</th></tr></thead><tbody>';
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
                        html += '<a href="<?= base_url() ?>' + (photo.file_path || '') + '" class="impl-history-modal-photo js-open-lightbox" data-image="<?= base_url() ?>' + (photo.file_path || '') + '" data-title="' + escapeAttr(historyButton.getAttribute('data-item-name') || 'Preview Foto') + '" data-caption="' + escapeAttr(photo.caption || photo.file_name || 'Foto Progress') + '">';
                        html += '<img src="<?= base_url() ?>' + (photo.file_path || '') + '" alt="' + (photo.file_name || 'Foto Progress') + '">';
                        html += '<div>' + (photo.caption || photo.file_name || 'Foto Progress') + '</div>';
                        html += '</a>';
                    });
                    html += '</div>';
                } else {
                    html += '<span class="text-muted">-</span>';
                }

                html += '</td></tr>';
            });
            html += '</tbody></table></div>';

            document.getElementById('history_item_rows').innerHTML = html;
        });
    })();
</script>
