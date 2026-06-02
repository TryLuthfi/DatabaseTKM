<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$statusOptions = ['DRAFT', 'ON REVIEW', 'REJECTED', 'DONE'];
$today = date('Y-m-d');
$summaryNyValsal = 0;
$summaryWaiting = 0;
$summaryDone = 0;
$summaryRejected = 0;
$summaryNyValsalHp = 0;
$summaryWaitingHp = 0;
$summaryDoneHp = 0;
$summaryRejectedHp = 0;
$createCityOptions = [];
$valsalOnProcessRows = [];
$nyBatchApprovalNyDrmRows = [];
$allValsalRows = $clusterRows;
$valsalDocumentDefinitions = isset($valsalDocumentDefinitions) && is_array($valsalDocumentDefinitions) ? $valsalDocumentDefinitions : [];
$valsalDocumentMap = isset($valsalDocumentMap) && is_array($valsalDocumentMap) ? $valsalDocumentMap : [];
$clusterReviewPicMap = isset($clusterReviewPicMap) && is_array($clusterReviewPicMap) ? $clusterReviewPicMap : [];
$postValsalStatuses = [
    'DRM',
    'RFS',
    'ATP',
    'DONE',
];
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('VALSAL_MyRep', 'TAMBAH') : true;
$canEdit = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('VALSAL_MyRep', 'EDIT') : true;
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('VALSAL_MyRep', 'HAPUS') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('VALSAL_MyRep', 'APPROVAL') : true;

foreach ($eligibleClusterOptions as $clusterOption) {
    $cityName = trim((string) ($clusterOption['city_name'] ?? ''));
    if ($cityName !== '') {
        $createCityOptions[strtoupper($cityName)] = $cityName;
    }
}

asort($createCityOptions);

foreach ($clusterRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
    $valsalStatus = strtoupper(trim((string) ($row['status_valsal'] ?? 'DRAFT')));
    $hasValsal = (int) ($row['id_valsal'] ?? 0) > 0;
    $homepassBak = (float) ($row['homepass_bak'] ?? 0);
    $homepassValsal = (float) ($row['homepass_valsal'] ?? 0);
    $summaryHomepass = $homepassValsal > 0 ? $homepassValsal : $homepassBak;
    $isBakApproved = in_array(strtoupper(trim((string) ($row['status_bak'] ?? ''))), ['DONE', 'APPROVED'], true);
    $isValsalApproved = $hasValsal && in_array($valsalStatus, ['DONE', 'APPROVED'], true);
    $isValsalOnProcess = $isBakApproved && (!$hasValsal || !in_array($valsalStatus, ['DONE', 'APPROVED'], true));
    $isNyBatchApprovalNyDrm = $isValsalApproved && !in_array($currentStatus, $postValsalStatuses, true);

    if ($isValsalOnProcess) {
        $valsalOnProcessRows[] = $row;
    }

    if ($isNyBatchApprovalNyDrm) {
        $nyBatchApprovalNyDrmRows[] = $row;
    }

    if (!$hasValsal && $currentStatus === 'BAK') {
        $summaryNyValsal++;
        $summaryNyValsalHp += $homepassBak;
    }

    if ($hasValsal && !in_array($valsalStatus, ['DONE', 'APPROVED', 'REJECTED'], true)) {
        $summaryWaiting++;
        $summaryWaitingHp += $summaryHomepass;
    }

    if (
        $hasValsal
        && in_array($valsalStatus, ['DONE', 'APPROVED'], true)
        && !in_array($currentStatus, $postValsalStatuses, true)
    ) {
        $summaryDone++;
        $summaryDoneHp += $summaryHomepass;
    }

    if ($hasValsal && ($valsalStatus === 'REJECTED' || $currentStatus === 'REJECTED')) {
        $summaryRejected++;
        $summaryRejectedHp += $summaryHomepass;
    }
}

$buildValsalStatusSummary = static function (array $rows) {
    $summary = [
        'waitingInputCount' => 0,
        'onReviewCount' => 0,
        'rejectedCount' => 0,
    ];

    foreach ($rows as $row) {
        $hasValsal = (int) ($row['id_valsal'] ?? 0) > 0;
        $valsalStatus = strtoupper(trim((string) ($row['status_valsal'] ?? 'DRAFT')));
        $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
        if (!$hasValsal) {
            $summary['waitingInputCount']++;
            continue;
        }

        if ($valsalStatus === 'ON REVIEW') {
            $summary['onReviewCount']++;
        }

        if ($valsalStatus === 'REJECTED' || $currentStatus === 'REJECTED') {
            $summary['rejectedCount']++;
        }
    }

    return $summary;
};
$valsalStatusSummaryByTab = [
    'on_process' => $buildValsalStatusSummary($valsalOnProcessRows),
    'ny_batch' => $buildValsalStatusSummary($nyBatchApprovalNyDrmRows),
    'all' => $buildValsalStatusSummary($allValsalRows),
];
$valsalActiveStatusSummary = $valsalStatusSummaryByTab['on_process'];
$valsalWaitingInputCount = (int) ($valsalActiveStatusSummary['waitingInputCount'] ?? 0);
$valsalOnReviewCount = (int) ($valsalActiveStatusSummary['onReviewCount'] ?? 0);
$valsalStatusRejectedCount = (int) ($valsalActiveStatusSummary['rejectedCount'] ?? 0);

if (!function_exists('valsalBadgeClass')) {
    function valsalBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'DONE':
            case 'APPROVED':
            case 'VALSAL':
                return 'success';
            case 'WAITING INPUT':
                return 'info';
            case 'REJECTED':
                return 'danger';
            case 'BAK':
                return 'info';
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('valsalDocLabel')) {
    function valsalDocLabel($row)
    {
        if ((int) ($row['is_document_not_required'] ?? 0) === 1) {
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($row['file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }
}

if (!function_exists('valsalAddWorkingDays')) {
    function valsalAddWorkingDays($dateString, $workingDays)
    {
        if (empty($dateString) || $dateString === '0000-00-00') {
            return null;
        }

        try {
            $date = new DateTimeImmutable(substr((string) $dateString, 0, 10));
        } catch (Exception $e) {
            return null;
        }

        $added = 0;
        while ($added < (int) $workingDays) {
            $date = $date->modify('+1 day');
            if ((int) $date->format('N') < 6) {
                $added++;
            }
        }

        return $date;
    }
}

if (!function_exists('valsalCountWorkingDays')) {
    function valsalCountWorkingDays($startDateString, $endDateString = null)
    {
        if (empty($startDateString) || $startDateString === '0000-00-00') {
            return null;
        }

        $endDateString = $endDateString ?: date('Y-m-d');
        if (empty($endDateString) || $endDateString === '0000-00-00') {
            $endDateString = date('Y-m-d');
        }

        try {
            $start = new DateTimeImmutable(substr((string) $startDateString, 0, 10));
            $end = new DateTimeImmutable(substr((string) $endDateString, 0, 10));
        } catch (Exception $e) {
            return null;
        }

        if ($start > $end) {
            return 0;
        }

        $workingDays = 0;
        $cursor = $start->modify('+1 day');
        while ($cursor <= $end) {
            if ((int) $cursor->format('N') < 6) {
                $workingDays++;
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $workingDays;
    }
}

if (!function_exists('valsalSlaInfo')) {
    function valsalSlaInfo($row)
    {
        $approvedBakDate = trim((string) ($row['bak_approved_at'] ?? ''));
        if ($approvedBakDate === '') {
            $approvedBakDate = trim((string) ($row['bak_date'] ?? ''));
        }

        $valsalDate = trim((string) ($row['valsal_date'] ?? ''));
        if ($approvedBakDate === '' || $approvedBakDate === '0000-00-00') {
            return [
                'start_date' => null,
                'aging_days' => null,
            ];
        }

        $hasValsalDate = $valsalDate !== '' && $valsalDate !== '0000-00-00';
        return [
            'start_date' => substr($approvedBakDate, 0, 10),
            'aging_days' => valsalCountWorkingDays($approvedBakDate, $hasValsalDate ? $valsalDate : date('Y-m-d')),
        ];
    }
}

if (!function_exists('valsalSlaBadgeClass')) {
    function valsalSlaBadgeClass($slaInfo)
    {
        if (($slaInfo['aging_days'] ?? null) === null) {
            return 'secondary';
        }

        return (int) $slaInfo['aging_days'] > 5 ? 'danger' : 'success';
    }
}

if (!function_exists('valsalAgingBadgeClass')) {
    function valsalAgingBadgeClass($agingDays)
    {
        if ($agingDays === null) {
            return 'secondary';
        }

        return (int) $agingDays > 5 ? 'danger' : 'success';
    }
}

$renderValsalTableRows = static function (array $rows, $docReady, $canApprove, $documentDefinitions, $documentMap, $clusterReviewPicMap) use ($canTambah, $canEdit, $canHapus) {
    foreach ($rows as $index => $row) {
        $slaInfo = valsalSlaInfo($row);
        $hasValsal = (int) ($row['id_valsal'] ?? 0) > 0;
        $statusValsalLabel = $hasValsal
            ? (string) ($row['status_valsal'] ?? 'DRAFT')
            : 'WAITING INPUT';
        $clusterId = (int) ($row['id_myrep_cluster'] ?? 0);
        $clusterDocs = $documentMap[$clusterId] ?? [];
        $docsById = [];
        foreach ($clusterDocs as $clusterDoc) {
            $docsById[(int) ($clusterDoc['id_doc_item'] ?? 0)] = $clusterDoc;
        }
        ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td>
                <?php if ($docReady && $hasValsal): ?>
                    <button
                        type="button"
                        class="btn btn-link p-0 border-0 align-baseline font-weight-bold js-valsal-doc-detail"
                        data-toggle="modal"
                        data-target="#modal-valsal-doc-detail"
                        data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                        data-documents='<?= htmlspecialchars(json_encode(array_values($clusterDocs)), ENT_QUOTES) ?>'>
                        <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?>
                    </button>
                <?php else: ?>
                    <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                <?php endif; ?>
                <?php if (!empty($row['cluster_code'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars((string) $row['cluster_code']) ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
            <td class="text-right"><?= number_format((float) ($row['homepass_bak'] ?? 0), 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format((float) ($row['homepass_valsal'] ?? 0), 0, ',', '.') ?></td>
            <td><?= !empty($slaInfo['start_date']) ? htmlspecialchars((string) $slaInfo['start_date']) : '-' ?></td>
            <td>
                <div class="valsal-sla-aging-cell">
                    <span class="badge badge-<?= valsalSlaBadgeClass($slaInfo) ?>">SLA 5 HK</span>
                    <span class="badge badge-<?= valsalAgingBadgeClass($slaInfo['aging_days']) ?>">
                        <?php if ($slaInfo['aging_days'] === null): ?>
                            Aging -
                        <?php else: ?>
                            Aging <?= (int) $slaInfo['aging_days'] ?> HK
                        <?php endif; ?>
                    </span>
                </div>
            </td>
            <td><?= !empty($row['valsal_date']) ? htmlspecialchars((string) $row['valsal_date']) : '-' ?></td>
            <td><span class="badge badge-<?= valsalBadgeClass($statusValsalLabel) ?>"><?= htmlspecialchars($statusValsalLabel) ?></span></td>
            <td>
                <?php if ($hasValsal && !empty($documentDefinitions)): ?>
                    <div class="valsal-doc-status-stack">
                        <?php foreach ($documentDefinitions as $documentDefinition): ?>
                            <?php
                            $docRow = $docsById[(int) $documentDefinition['id_doc_item']] ?? [];
                            $docStatusRaw = (string) valsalDocLabel($docRow);
                            $docStatusDisplay = ucwords(strtolower($docStatusRaw));
                            $docName = ucwords(strtolower((string) ($documentDefinition['doc_name'] ?? '-')));
                            ?>
                            <div class="valsal-doc-status-stack__item">
                                <span class="valsal-doc-name"><?= htmlspecialchars($docName) ?> :</span>
                                <span class="badge badge-<?= valsalBadgeClass($docStatusRaw) ?> valsal-doc-status-badge">
                                    <?= htmlspecialchars($docStatusDisplay) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($canTambah): ?>
                    <span class="badge badge-secondary">BELUM ADA DOC</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!$hasValsal): ?>
                    <span class="text-muted small">Belum ada pengajuan</span>
                <?php else: ?>
                    <?php
                    $uploadBy = '-';
                    $picApproval = '';
                    $clusterMappedPic = trim((string) ($clusterReviewPicMap[$clusterId] ?? ''));
                    $documentMappedPic = trim((string) ($clusterDocs[0]['city_pic_approval_name'] ?? ''));

                    foreach ($clusterDocs as $docRow) {
                        $uploadCandidate = trim((string) ($docRow['uploaded_by_name'] ?? ''));
                        if ($uploadBy === '-' && $uploadCandidate !== '') {
                            $uploadBy = $uploadCandidate;
                        }

                        $approvedCandidate = trim((string) ($docRow['approved_by_name'] ?? ''));
                        if ($approvedCandidate !== '') {
                            $picApproval = $approvedCandidate;
                            break;
                        }
                    }

                    if ($picApproval === '') {
                        $picApproval = $clusterMappedPic !== '' ? $clusterMappedPic : $documentMappedPic;
                    }

                    if ($picApproval === '') {
                        $picApproval = '-';
                    }
                    ?>
                    <div class="small text-muted">
                        <div>Upload by : <?= htmlspecialchars($uploadBy) ?></div>
                        <div>PIC approval : <?= htmlspecialchars($picApproval) ?></div>
                    </div>
                <?php endif; ?>
            </td>
            <td><span class="badge badge-<?= valsalBadgeClass($row['status_current'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'DRAFT')) ?></span></td>
            <td>
                <?php if ($hasValsal): ?>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary js-edit-valsal"
                        data-toggle="modal"
                        data-target="#modal-valsal-edit"
                        data-id_myrep_cluster="<?= (int) $row['id_myrep_cluster'] ?>"
                        data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                        data-regional_name="<?= htmlspecialchars((string) ($row['regional_name'] ?? ''), ENT_QUOTES) ?>"
                        data-province_name="<?= htmlspecialchars((string) ($row['province_name'] ?? ''), ENT_QUOTES) ?>"
                        data-city_name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>"
                        data-homepass_bak="<?= (int) ($row['homepass_bak'] ?? 0) ?>"
                        data-homepass_valsal="<?= (int) ($row['homepass_valsal'] ?? 0) ?>"
                        data-bak_date="<?= htmlspecialchars((string) ($row['bak_date'] ?? ''), ENT_QUOTES) ?>"
                        data-valsal_date="<?= htmlspecialchars((string) ($row['valsal_date'] ?? ''), ENT_QUOTES) ?>"
                        data-status_valsal="<?= htmlspecialchars((string) ($row['status_valsal'] ?? 'DRAFT'), ENT_QUOTES) ?>"
                        data-remark_valsal="<?= htmlspecialchars((string) ($row['remark_valsal'] ?? ''), ENT_QUOTES) ?>">
                        Edit
                    </button>
                <?php else: ?>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary js-start-valsal"
                        data-toggle="modal"
                        data-target="#modal-valsal-create"
                        data-cluster-id="<?= (int) $row['id_myrep_cluster'] ?>"
                        data-city-name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>">
                        Input VALSAL
                    </button>
                <?php endif; ?>
                <?php if ($docReady): ?>
                    <?php if ($hasValsal): ?>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark js-valsal-doc-detail mt-1"
                            data-toggle="modal"
                            data-target="#modal-valsal-doc-detail"
                            data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                            data-documents='<?= htmlspecialchars(json_encode(array_values($clusterDocs)), ENT_QUOTES) ?>'>
                            Detail Dokumen
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($canHapus): ?>
                    <form method="post" action="<?= base_url('VALSAL_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta flow VALSAL dan seluruh tahap MyRep sebelumnya/sesudahnya?');">
                        <input type="hidden" name="cluster_id" value="<?= (int) $row['id_myrep_cluster'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger mt-1">Hapus Cluster</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
};
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">VALSAL MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">
                    Tabel flow baru MyRep belum tersedia. Jalankan query database `tb_myrep_*` terlebih dahulu sebelum memakai modul VALSAL.
                </div>
            <?php endif; ?>

            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">
                    Tabel dokumen flow VALSAL belum tersedia. Form VALSAL tetap bisa dipakai, tetapi upload dokumen VALSAL belum aktif.
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show js-valsal-flash-alert" role="alert" data-flash-key="<?= htmlspecialchars('valsal_flash_success_' . md5((string) $flashSuccess), ENT_QUOTES) ?>">
                    <?= $flashSuccess ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show js-valsal-flash-alert" role="alert" data-flash-key="<?= htmlspecialchars('valsal_flash_error_' . md5((string) $flashError), ENT_QUOTES) ?>">
                    <?= $flashError ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm valsal-filter-card">
                        <div class="card-header valsal-section-header">
                            <div>
                                <h3 class="card-title mb-1">Filter Data VALSAL</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('VALSAL_MyRep') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="valsal-field-label">Kota</label>
                                            <select name="city" class="form-control valsal-input">
                                                <option value="">Semua Kota</option>
                                                <?php foreach ($cityOptions as $cityOption): ?>
                                                    <option value="<?= htmlspecialchars($cityOption) ?>" <?= $selectedCity === strtoupper($cityOption) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cityOption) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="valsal-field-label">Status</label>
                                            <select name="status" class="form-control valsal-input">
                                                <option value="">Semua Status</option>
                                                <option value="BAK" <?= $selectedStatus === 'BAK' ? 'selected' : '' ?>>BAK</option>
                                                <?php foreach ($statusOptions as $statusOption): ?>
                                                    <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>>
                                                        <?= $statusOption ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group mb-0 w-100 d-flex justify-content-between valsal-filter-actions">
                                            <a href="<?= base_url('VALSAL_MyRep') ?>" class="btn budget-btn budget-btn--ghost">Reset</a>
                                            <?php if ($isReady): ?>
                                                <button type="submit" class="btn budget-btn budget-btn--primary">Terapkan Filter</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info shadow-sm valsal-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryNyValsal, 0, ',', '.') ?></h3>
                            <p>NY VALSAL</p>
                            <p class="valsal-summary-box__meta mb-0">HP <?= number_format($summaryNyValsalHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary shadow-sm valsal-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryWaiting, 0, ',', '.') ?></h3>
                            <p>Waiting VALSAL</p>
                            <p class="valsal-summary-box__meta mb-0">HP <?= number_format($summaryWaitingHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm valsal-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryDone, 0, ',', '.') ?></h3>
                            <p>Done VALSAL</p>
                            <p class="valsal-summary-box__meta mb-0">HP <?= number_format($summaryDoneHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger shadow-sm valsal-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryRejected, 0, ',', '.') ?></h3>
                            <p>Rejected</p>
                            <p class="valsal-summary-box__meta mb-0">HP <?= number_format($summaryRejectedHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="valsal-toolbar">
                        <?php if ($isReady && $canTambah): ?>
                            <button type="button" class="btn budget-btn budget-btn--primary" data-toggle="modal" data-target="#modal-valsal-create">
                                <i class="fas fa-plus mr-1"></i> Input VALSAL
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--ghost ml-2" data-toggle="modal" data-target="#modal-valsal-import">
                                <i class="fas fa-file-import mr-1"></i> Import VALSAL Batch
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--success" data-toggle="modal" data-target="#modal-valsal-download-report">
                                <i class="fas fa-download mr-1"></i> Download Report Valsal
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm valsal-table-card">
                        <div class="card-header valsal-section-header d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="card-title mb-1">Monitoring VALSAL Cluster</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="valsal-tab-stack">
                                <div class="valsal-tab-section">
                                    <div class="valsal-tab-section__label">Flow</div>
                                    <ul class="nav nav-tabs valsal-monitor-tabs" id="valsal-monitor-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="valsal-on-process-tab" data-toggle="tab" href="#valsal-on-process-pane" role="tab" aria-controls="valsal-on-process-pane" aria-selected="true">
                                                On Proses
                                                <span class="valsal-monitor-tabs__count"><?= number_format(count($valsalOnProcessRows), 0, ',', '.') ?></span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="valsal-ny-batch-tab" data-toggle="tab" href="#valsal-ny-batch-pane" role="tab" aria-controls="valsal-ny-batch-pane" aria-selected="false">
                                                Status NY Batch Approval &amp; NY DRM
                                                <span class="valsal-monitor-tabs__count"><?= number_format(count($nyBatchApprovalNyDrmRows), 0, ',', '.') ?></span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="valsal-all-tab" data-toggle="tab" href="#valsal-all-pane" role="tab" aria-controls="valsal-all-pane" aria-selected="false">
                                                ALL VALSAL
                                                <span class="valsal-monitor-tabs__count"><?= number_format(count($allValsalRows), 0, ',', '.') ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="valsal-status-filter-row">
                                    <div class="valsal-tab-section__label">Filter Status</div>
                                    <div class="valsal-status-pillbar" aria-label="Filter status VALSAL">
                                        <button type="button" class="valsal-status-pill js-valsal-status-filter" data-valsal-status="waiting_input">
                                            <span>Waiting Input</span>
                                            <span class="valsal-status-pill__count" data-valsal-status-count="waitingInputCount"><?= number_format($valsalWaitingInputCount, 0, ',', '.') ?></span>
                                        </button>
                                        <button type="button" class="valsal-status-pill js-valsal-status-filter" data-valsal-status="on_review">
                                            <span>On Review</span>
                                            <span class="valsal-status-pill__count" data-valsal-status-count="onReviewCount"><?= number_format($valsalOnReviewCount, 0, ',', '.') ?></span>
                                        </button>
                                        <button type="button" class="valsal-status-pill js-valsal-status-filter" data-valsal-status="rejected">
                                            <span>Rejected</span>
                                            <span class="valsal-status-pill__count" data-valsal-status-count="rejectedCount"><?= number_format($valsalStatusRejectedCount, 0, ',', '.') ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-content valsal-monitor-tabs__content" id="valsal-monitor-tab-content">
                                <div class="tab-pane fade show active" id="valsal-on-process-pane" role="tabpanel" aria-labelledby="valsal-on-process-tab">
                                    <div class="table-responsive">
                                        <table id="table_valsal_on_process" class="table table-bordered table-hover valsal-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Regional</th>
                                                    <th>Kota</th>
                                                    <th>HP BAK</th>
                                                    <th>HP VALSAL</th>
                                                    <th>Approved BAK</th>
                                                    <th>SLA &amp; Aging</th>
                                                    <th>Tanggal VALSAL</th>
                                                    <th>Status VALSAL</th>
                                                    <th>Dokumen VALSAL</th>
                                                    <th>Review Dokumen</th>
                                                    <th>Status Flow</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderValsalTableRows($valsalOnProcessRows, $docReady, $canApprove, $valsalDocumentDefinitions, $valsalDocumentMap, $clusterReviewPicMap); ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">TOTAL</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th colspan="8"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="valsal-ny-batch-pane" role="tabpanel" aria-labelledby="valsal-ny-batch-tab">
                                    <div class="table-responsive">
                                        <table id="table_valsal_ny_batch_drm" class="table table-bordered table-hover valsal-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Regional</th>
                                                    <th>Kota</th>
                                                    <th>HP BAK</th>
                                                    <th>HP VALSAL</th>
                                                    <th>Approved BAK</th>
                                                    <th>SLA &amp; Aging</th>
                                                    <th>Tanggal VALSAL</th>
                                                    <th>Status VALSAL</th>
                                                    <th>Dokumen VALSAL</th>
                                                    <th>Review Dokumen</th>
                                                    <th>Status Flow</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderValsalTableRows($nyBatchApprovalNyDrmRows, $docReady, $canApprove, $valsalDocumentDefinitions, $valsalDocumentMap, $clusterReviewPicMap); ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">TOTAL</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th colspan="8"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="valsal-all-pane" role="tabpanel" aria-labelledby="valsal-all-tab">
                                    <div class="table-responsive">
                                        <table id="table_valsal_all" class="table table-bordered table-hover valsal-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Regional</th>
                                                    <th>Kota</th>
                                                    <th>HP BAK</th>
                                                    <th>HP VALSAL</th>
                                                    <th>Approved BAK</th>
                                                    <th>SLA &amp; Aging</th>
                                                    <th>Tanggal VALSAL</th>
                                                    <th>Status VALSAL</th>
                                                    <th>Dokumen VALSAL</th>
                                                    <th>Review Dokumen</th>
                                                    <th>Status Flow</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderValsalTableRows($allValsalRows, $docReady, $canApprove, $valsalDocumentDefinitions, $valsalDocumentMap, $clusterReviewPicMap); ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">TOTAL</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th colspan="8"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($isReady): ?>
    <div class="modal fade" id="modal-valsal-import" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content budget-modal valsal-modal-shell">
                <form id="valsal-import-preview-form" enctype="multipart/form-data">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <span class="budget-modal__eyebrow">VALSAL MyRep</span>
                            <h5 class="modal-title mb-1">Import VALSAL Batch (Excel/CSV)</h5>
                            <p class="mb-0 budget-modal__subtitle">Import massal VALSAL. Saat import, data BAK otomatis dibuat/diupdate menjadi DONE.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="doc-modal-panel mb-3">
                            <a href="<?= base_url('VALSAL_MyRep/downloadValsalImportTemplate') ?>" class="btn budget-btn budget-btn--success">
                                <i class="fas fa-download mr-1"></i> Download Format CSV
                            </a>
                            <p class="doc-modal-subtitle mt-2 mb-0">
                                Header utama: <code>cluster_id</code> atau <code>cluster_name</code>, <code>homepass_valsal</code>, <code>status_valsal</code>. Versi lengkap support <code>id_target</code>, <code>city_name</code>, <code>cluster_code</code>, <code>valsal_date</code>, <code>remark_valsal</code>.
                            </p>
                        </div>
                        <div class="doc-modal-panel">
                            <div class="upload-dropzone" id="valsal-import-dropzone">
                                <input type="file" id="valsal-import-file-input" name="file_excel" accept=".xls,.xlsx,.csv">
                                <div class="upload-dropzone-content">
                                    <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="upload-dropzone-title">Drag & drop file import di sini</div>
                                    <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                    <div class="upload-dropzone-file" id="valsal-import-file-name">Belum ada file dipilih</div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Format: xls, xlsx, csv. Maksimal 4 MB.</small>
                        </div>
                        <div class="doc-modal-panel mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="doc-modal-title mb-0">Preview Import</div>
                                <small id="valsal-import-summary" class="text-muted">Belum ada file dipreview</small>
                            </div>
                            <div class="table-responsive" style="max-height: 320px;">
                                <table class="table table-bordered table-sm mb-0" id="table_valsal_import_preview">
                                    <thead>
                                        <tr>
                                            <th>Row</th>
                                            <th>Cluster ID</th>
                                            <th>Kota</th>
                                            <th>Cluster</th>
                                            <th>Kode Cluster</th>
                                            <th>HP VALSAL</th>
                                            <th>Tanggal VALSAL</th>
                                            <th>Status VALSAL</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="10" class="text-center text-muted">Belum ada data preview</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn budget-btn budget-btn--primary" id="valsal-save-import-btn" disabled>Simpan Hasil Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-valsal-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content budget-modal valsal-modal-shell">
                <form method="post" action="<?= base_url('VALSAL_MyRep/saveValsal') ?>" enctype="multipart/form-data" id="valsal-create-form">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <span class="budget-modal__eyebrow">VALSAL MyRep</span>
                            <h5 class="modal-title mb-1">Input Cluster VALSAL Baru</h5>
                            <p class="mb-0 budget-modal__subtitle">Pilih cluster yang sudah selesai BAK, isi data VALSAL, dan upload 3 dokumen dalam satu workflow yang lebih rapi.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="budget-form-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select class="form-control js-valsal-city-selector" id="create_valsal_city">
                                        <option value="">Pilih kota</option>
                                        <?php foreach ($createCityOptions as $cityValue => $cityLabel): ?>
                                            <option value="<?= htmlspecialchars($cityValue, ENT_QUOTES) ?>"><?= htmlspecialchars($cityLabel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama Cluster</label>
                                    <select name="cluster_id" class="form-control js-valsal-cluster-selector js-valsal-cluster-select" required>
                                        <option value="">Pilih cluster yang sudah BAK</option>
                                        <?php foreach ($eligibleClusterOptions as $clusterOption): ?>
                                            <option
                                                value="<?= (int) $clusterOption['id_myrep_cluster'] ?>"
                                                data-city-filter="<?= htmlspecialchars(strtoupper((string) ($clusterOption['city_name'] ?? '')), ENT_QUOTES) ?>"
                                                data-cluster_name="<?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-regional_name="<?= htmlspecialchars((string) ($clusterOption['regional_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-province_name="<?= htmlspecialchars((string) ($clusterOption['province_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-city_name="<?= htmlspecialchars((string) ($clusterOption['city_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-homepass_bak="<?= (int) ($clusterOption['homepass_bak'] ?? 0) ?>"
                                                data-bak_date="<?= htmlspecialchars((string) ($clusterOption['bak_date'] ?? ''), ENT_QUOTES) ?>">
                                                <?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? '-')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-cluster-regional" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control js-cluster-province" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" class="form-control js-cluster-city" readonly></div></div>
                            <div class="col-md-8"><div class="form-group"><label>Nama Cluster</label><input type="text" class="form-control js-cluster-name" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BAK</label><input type="text" class="form-control js-bak-date" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass BAK</label><input type="number" class="form-control js-homepass-bak" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass VALSAL</label><input type="number" name="homepass_valsal" min="1" class="form-control js-homepass-valsal" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal VALSAL</label><input type="date" name="valsal_date" class="form-control" value="<?= $today ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Status VALSAL</label><input type="text" class="form-control" value="ON REVIEW" readonly></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Remark</label><textarea name="remark_valsal" rows="3" class="form-control"></textarea></div></div>
                            <?php if ($docReady): ?>
                                <div class="col-md-12">
                                    <div class="doc-modal-panel">
                                        <div class="doc-modal-title">Upload 3 Dokumen VALSAL</div>
                                        <p class="doc-modal-subtitle">Saat input VALSAL baru, lengkapi dokumen SND Kasar, Form SND, dan Boundary KMZ. Status VALSAL akan tetap `ON REVIEW` sampai seluruh dokumen di-approve HO.</p>
                                    </div>
                                </div>
                                <?php foreach ($valsalDocumentDefinitions as $documentDefinition): ?>
                                    <?php $docItemId = (int) $documentDefinition['id_doc_item']; ?>
                                    <div class="col-md-12">
                                        <div class="doc-modal-panel">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold d-block"><?= htmlspecialchars((string) ($documentDefinition['doc_name'] ?? '-')) ?></label>
                                                <div class="upload-dropzone create-doc-dropzone" id="valsal-create-dropzone-<?= $docItemId ?>">
                                                    <input type="file" name="create_file_<?= $docItemId ?>" class="create-doc-input" id="valsal-create-file-<?= $docItemId ?>" data-doc-name="<?= htmlspecialchars((string) ($documentDefinition['doc_name'] ?? '-'), ENT_QUOTES) ?>" data-doc-item-id="<?= $docItemId ?>" required>
                                                    <div class="upload-dropzone-content">
                                                        <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                        <div class="upload-dropzone-title">Drag & drop <?= htmlspecialchars((string) ($documentDefinition['doc_name'] ?? 'dokumen')) ?></div>
                                                        <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                                        <div class="upload-dropzone-file create-doc-file-name" id="valsal-create-file-name-<?= $docItemId ?>">Belum ada file dipilih</div>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-2">Format: pdf, doc, docx, xls, xlsx, jpg, jpeg, png. Maksimal 30 MB.</small>
                                            </div>
                                            <div class="form-group form-check mb-3">
                                                <input type="checkbox" class="form-check-input js-create-doc-not-required" id="valsal-create-not-required-<?= $docItemId ?>" name="create_is_document_not_required_<?= $docItemId ?>" value="1" data-doc-item-id="<?= $docItemId ?>">
                                                <label class="form-check-label" for="valsal-create-not-required-<?= $docItemId ?>">Tidak butuh dokument</label>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold">Remark <?= htmlspecialchars((string) ($documentDefinition['doc_name'] ?? '-')) ?></label>
                                                <textarea name="create_doc_remark_<?= $docItemId ?>" rows="2" class="form-control" placeholder="Catatan upload jika diperlukan"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary" id="valsal-create-submit" <?= $docReady ? 'disabled' : '' ?>>Simpan VALSAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-valsal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content budget-modal valsal-modal-shell">
                <form method="post" action="<?= base_url('VALSAL_MyRep/updateValsal') ?>">
                    <input type="hidden" name="cluster_id" id="edit_id_myrep_cluster">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <span class="budget-modal__eyebrow">VALSAL MyRep</span>
                            <h5 class="modal-title mb-1">Edit Data VALSAL</h5>
                            <p class="mb-0 budget-modal__subtitle">Perbarui informasi cluster, homepass, timeline BAK ke VALSAL, dan remark dengan tampilan yang lebih bersih.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="budget-form-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Cluster</label>
                                    <input type="text" id="edit_cluster_name" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" id="edit_regional_name" class="form-control" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" id="edit_province_name" class="form-control" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" id="edit_city_name" class="form-control" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass BAK</label><input type="number" id="edit_homepass_bak" class="form-control" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass VALSAL</label><input type="number" name="homepass_valsal" id="edit_homepass_valsal" min="1" class="form-control" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BAK</label><input type="text" id="edit_bak_date" class="form-control" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal VALSAL</label><input type="date" name="valsal_date" id="edit_valsal_date" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Status VALSAL</label><input type="text" id="edit_status_valsal" class="form-control" readonly></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Remark</label><textarea name="remark_valsal" id="edit_remark_valsal" rows="3" class="form-control"></textarea></div></div>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Update VALSAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($docReady): ?>
        <div class="modal fade doc-modal" id="modal-valsal-doc-detail" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xxl" role="document">
                <div class="modal-content budget-modal valsal-modal-shell">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <h4 class="modal-title mb-0">Detail Dokumen Cluster</h4>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="doc-modal-panel mb-3">
                            <div class="valsal-doc-cluster-detail">
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Cluster</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-cluster-name">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Kode Cluster</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-cluster-code">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">HP BAK</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-homepass-bak">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">HP VALSAL</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-homepass-valsal">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Regional</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-regional-name">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Kota</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-city-name">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Kecamatan</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-district-name">-</span>
                                </div>
                                <div class="valsal-doc-cluster-detail__item">
                                    <span class="valsal-doc-cluster-detail__label">Kelurahan</span>
                                    <span class="valsal-doc-cluster-detail__value" id="valsal-doc-detail-village-name">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="doc-modal-panel mb-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Dokumen</th>
                                            <th>Status</th>
                                            <th>File</th>
                                            <th>Review</th>
                                            <th>Uploaded At</th>
                                            <th>Reviewed At</th>
                                            <th>Approved At</th>
                                            <th>Remark</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="valsal-doc-detail-body">
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Belum ada dokumen.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <a href="#" target="_blank" class="btn budget-btn budget-btn--ghost d-none" id="valsal-doc-download-bundle-btn">
                            <i class="fas fa-file-archive mr-1"></i> Download Gabungan
                        </a>
                        <?php if ($canApprove && $canApprovalAction): ?>
                            <button type="button" class="btn budget-btn budget-btn--success d-none" id="valsal-doc-approve-all-btn">
                                <i class="fas fa-check-double mr-1"></i> Approve All
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade doc-modal" id="modal-valsal-upload-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content budget-modal valsal-modal-shell">
                    <form method="post" action="<?= base_url('VALSAL_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="valsal-upload-document-form">
                        <input type="hidden" name="cluster_id" id="upload_cluster_id">
                        <input type="hidden" name="doc_item_id" id="upload_doc_item_id">
                        <div class="modal-header budget-modal__header">
                            <div>
                                <span class="budget-modal__eyebrow">Dokumen VALSAL</span>
                                <h4 class="modal-title mb-1">Upload Dokumen VALSAL</h4>
                                <p class="mb-0 budget-modal__subtitle" id="upload_doc_cluster_caption"></p>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="doc-modal-panel">
                                <div class="doc-modal-title">Panduan Upload</div>
                                <p class="doc-modal-subtitle">Gunakan drag-and-drop atau klik area upload. Setelah file masuk, status dokumen akan menjadi `ON REVIEW` sampai HO melakukan approval.</p>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="font-weight-bold">Dokumen</label>
                                        <input type="text" id="upload_doc_name" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Cluster</label>
                                            <input type="text" id="upload_cluster_name" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Status Saat Ini</label>
                                            <input type="text" id="upload_doc_status" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-modal-panel">
                                <label class="font-weight-bold d-block">File Dokumen</label>
                                <div class="upload-dropzone" id="valsal-upload-dropzone">
                                    <input type="file" name="file" id="valsal-upload-file-input">
                                    <div class="upload-dropzone-content">
                                        <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                        <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                        <div class="upload-dropzone-file" id="valsal-upload-file-name">Belum ada file dipilih</div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Format: pdf, doc, docx, xls, xlsx, jpg, jpeg, png. Maksimal 30 MB.</small>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Remark Upload</label>
                                    <textarea name="remark" id="upload_doc_remark" rows="3" class="form-control" placeholder="Catatan upload jika diperlukan"></textarea>
                                </div>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="form-group form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="upload_doc_not_required" name="is_document_not_required" value="1">
                                    <label class="form-check-label" for="upload_doc_not_required">Tandai dokumen tidak dibutuhkan</label>
                                </div>
                            </div>
                            <div class="upload-progress-panel" id="valsal-upload-progress-panel">
                                <div class="upload-progress-meta">
                                    <span>Upload Progress</span>
                                    <span id="valsal-upload-progress-percent">0%</span>
                                </div>
                                <div class="upload-progress-bar-wrap">
                                    <div class="upload-progress-bar" id="valsal-upload-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer budget-modal__footer">
                            <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn budget-btn budget-btn--success" id="valsal-upload-document-submit">Upload Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($canApprove && $canApprovalAction): ?>
            <div class="modal fade doc-modal" id="modal-valsal-approve-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content budget-modal valsal-modal-shell">
                        <form method="post" action="<?= base_url('VALSAL_MyRep/approveDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="approve_id_doc_file">
                            <div class="modal-header budget-modal__header">
                                <div>
                                    <span class="budget-modal__eyebrow">Dokumen VALSAL</span>
                                    <h4 class="modal-title mb-1">Approve Dokumen</h4>
                                    <p class="mb-0 budget-modal__subtitle" id="approve_doc_name">SND KASAR</p>
                                </div>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="doc-modal-panel">
                                    <div class="doc-modal-title">Konfirmasi Approval</div>
                                    <p class="doc-modal-subtitle">Remarks bersifat opsional. Bisa diisi jika ingin memberi catatan saat approve.</p>
                                </div>
                                <div class="doc-modal-panel mb-0">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Cluster</label>
                                        <input type="text" id="approve_cluster_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Remarks</label>
                                        <textarea name="remark" rows="3" class="form-control" placeholder="Isi remarks approval jika diperlukan"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer budget-modal__footer">
                                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn budget-btn budget-btn--success">Approve Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade doc-modal" id="modal-valsal-reject-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content budget-modal valsal-modal-shell">
                        <form method="post" action="<?= base_url('VALSAL_MyRep/rejectDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="reject_id_doc_file">
                            <div class="modal-header budget-modal__header">
                                <div>
                                    <span class="budget-modal__eyebrow">Dokumen VALSAL</span>
                                    <h4 class="modal-title mb-1">Reject Dokumen</h4>
                                    <p class="mb-0 budget-modal__subtitle" id="reject_doc_name">SND KASAR</p>
                                </div>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="doc-modal-panel">
                                    <div class="doc-modal-title">Konfirmasi Reject</div>
                                    <p class="doc-modal-subtitle">Isi alasan reject agar area bisa tahu apa yang perlu diperbaiki sebelum upload ulang.</p>
                                </div>
                                <div class="doc-modal-panel mb-0">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Cluster</label>
                                        <input type="text" id="reject_cluster_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Alasan Reject</label>
                                        <textarea name="remark" rows="3" class="form-control" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer budget-modal__footer">
                                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn budget-btn budget-btn--danger">Reject Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="modal fade doc-modal" id="modal-valsal-history-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content budget-modal valsal-modal-shell">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <span class="budget-modal__eyebrow">Dokumen VALSAL</span>
                            <h4 class="modal-title mb-1">History Dokumen</h4>
                            <p class="mb-0 budget-modal__subtitle" id="history_doc_name">SND KASAR</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="doc-modal-panel">
                            <div class="doc-modal-title mb-1">Cluster</div>
                            <p class="doc-modal-subtitle mb-0" id="history_cluster_name">-</p>
                        </div>
                        <div class="doc-modal-panel mb-0">
                            <ul class="doc-history-list" id="history_doc_list">
                                <li class="text-muted">Belum ada history.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$regionalOptions = isset($regionalOptions) && is_array($regionalOptions) ? $regionalOptions : [];
$cityOptionsByRegional = isset($cityOptionsByRegional) && is_array($cityOptionsByRegional) ? $cityOptionsByRegional : [];
$regionalOptionsByCity = isset($regionalOptionsByCity) && is_array($regionalOptionsByCity) ? $regionalOptionsByCity : [];
?>
<div class="modal fade" id="modal-valsal-download-report" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalValsalDownloadReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content budget-modal valsal-modal-shell">
            <div class="modal-header budget-modal__header">
                <div>
                    <span class="budget-modal__eyebrow">VALSAL MyRep</span>
                    <h5 class="modal-title mb-1" id="modalValsalDownloadReportLabel">Download Report VALSAL</h5>
                    <p class="mb-0 budget-modal__subtitle">Ekspor report VALSAL dengan filter regional, kota, dan rentang tanggal VALSAL.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="doc-modal-panel mb-3">
                    <div class="doc-modal-title mb-1">Report VALSAL</div>
                    <p class="doc-modal-subtitle mb-0">Jika regional dan kota tidak dipilih, sistem akan download semua data sesuai filter status di halaman.</p>
                </div>
                <div class="budget-form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Regional</label>
                                <select id="valsal_download_regional" class="form-control" multiple>
                                    <?php foreach ($regionalOptions as $regionalOption): ?>
                                        <option value="<?= htmlspecialchars((string) $regionalOption, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $regionalOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kota</label>
                                <select id="valsal_download_city" class="form-control" multiple>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= htmlspecialchars((string) $cityOption, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $cityOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal VALSAL Start</label>
                                <input type="date" id="valsal_download_date_start" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Tanggal VALSAL End</label>
                                <input type="date" id="valsal_download_date_end" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer budget-modal__footer">
                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn budget-btn budget-btn--success" id="valsal-download-report-submit-btn">
                    <i class="fas fa-download mr-1"></i> Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .valsal-filter-card,
    .valsal-table-card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .valsal-section-header {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
        border-bottom: 0;
        padding: 1rem 1.25rem;
    }

    .valsal-section-header .card-title {
        font-weight: 700;
    }

    .valsal-section-subtitle {
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
        max-width: 760px;
    }

    .valsal-field-label {
        display: block;
        margin-bottom: 0.55rem;
        font-size: 0.83rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .valsal-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .valsal-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .valsal-filter-actions {
        gap: 10px;
    }

    .budget-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .budget-btn:hover,
    .budget-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .budget-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .budget-btn--danger {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: #fff;
    }

    .valsal-summary-box {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1);
    }

    .valsal-summary-box .inner h3 {
        font-weight: 800;
    }

    .valsal-summary-box__meta {
        font-size: .88rem;
        font-weight: 600;
        opacity: .92;
    }

    .valsal-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0.85rem;
    }

    .valsal-monitor-table thead th {
        background: linear-gradient(180deg, #eef6fb 0%, #dcecf8 100%);
        color: #1f5e8a;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
        border-top: 0;
    }

    .valsal-monitor-table {
        min-width: 1660px;
    }

    .valsal-monitor-table tbody td {
        white-space: nowrap;
        vertical-align: top;
    }

    .valsal-table-card .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    .valsal-monitor-table tbody tr:hover {
        background: rgba(219, 236, 247, 0.22);
    }

    .valsal-sla-aging-cell .badge {
        display: block;
        text-align: left;
    }

    .valsal-sla-aging-cell .badge + .badge {
        margin-top: 0.2rem;
    }

    .valsal-doc-status-stack {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .valsal-doc-status-stack__item {
        display: grid;
        grid-template-columns: 92px max-content;
        column-gap: 0.35rem;
        align-items: center;
        color: #000;
        font-size: 0.76rem;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .valsal-doc-name {
        color: #111;
        font-weight: 600;
    }

    .valsal-doc-status-badge {
        font-size: 0.73rem;
        justify-self: start;
    }

    .valsal-doc-cluster-detail {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .valsal-doc-cluster-detail__item {
        min-width: 0;
    }

    .valsal-doc-cluster-detail__label {
        display: block;
        color: #6b7280;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .valsal-doc-cluster-detail__value {
        display: block;
        color: #111827;
        font-size: 0.92rem;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .valsal-doc-cluster-detail {
            grid-template-columns: 1fr;
        }
    }

    .modal-xxl {
        max-width: 78vw;
    }

    .budget-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .budget-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .budget-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .budget-modal__subtitle {
        max-width: 88%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .budget-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .budget-modal__footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: transparent;
    }

    .budget-form-section {
        margin-bottom: 0;
        padding: 1rem 1rem 0.9rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .doc-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .doc-modal .modal-header {
        border-bottom: 0;
        padding: 1rem 1.25rem;
        color: #fff;
    }

    .doc-modal .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .doc-modal .modal-footer {
        border-top: 0;
        background: #eef2f7;
    }

    .doc-modal-panel {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .doc-modal-panel:last-child {
        margin-bottom: 0;
    }

    .doc-modal-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: .35rem;
        color: #1f2937;
    }

    .doc-modal-subtitle {
        margin: 0;
        color: #6b7280;
        font-size: .9rem;
    }

    .upload-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1.1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .upload-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
        transform: scale(1.01);
    }

    .upload-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .upload-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .upload-dropzone-icon {
        font-size: 2rem;
        color: #198754;
        margin-bottom: .5rem;
    }

    .upload-dropzone-title {
        font-weight: 700;
        color: #166534;
        margin-bottom: .25rem;
    }

    .upload-dropzone-text {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: .35rem;
    }

    .upload-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .upload-progress-panel {
        display: none;
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 1rem;
    }

    .upload-progress-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .5rem;
        font-weight: 700;
        color: #1e3a8a;
    }

    .upload-progress-bar-wrap {
        width: 100%;
        height: 12px;
        background: #e9eef5;
        border-radius: 999px;
        overflow: hidden;
    }

    .upload-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        width: 0%;
    }

    .upload-progress-bar.success {
        background: linear-gradient(90deg, #065f46, #10b981);
    }

    .doc-history-list {
        position: relative;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .doc-history-item {
        position: relative;
        padding-left: 1.5rem;
        padding-bottom: 1rem;
        border-left: 2px solid #d8e3ee;
        margin-left: .5rem;
    }

    .doc-history-item:last-child {
        padding-bottom: 0;
    }

    .doc-history-dot {
        position: absolute;
        left: -8px;
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 999px;
        background: #17a2b8;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #d8e3ee;
    }

    .doc-history-title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .2rem;
    }

    .doc-history-meta {
        color: #6b7280;
        font-size: .86rem;
        margin-bottom: .25rem;
    }

    .doc-history-note {
        color: #374151;
        font-size: .9rem;
        margin-bottom: 0;
    }

    .small-box.shadow-sm {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        border-radius: 14px;
    }

    .badge {
        font-size: 11px;
        padding: 6px 10px;
        border-radius: 999px;
    }

    .select2-container--open {
        z-index: 1065;
    }

    .valsal-modal-shell .form-group label,
    .doc-modal .form-group label {
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .valsal-modal-shell .form-control,
    .doc-modal .form-control,
    .doc-modal .select2-container--bootstrap4 .select2-selection {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
        background: #fff;
    }

    .valsal-modal-shell textarea.form-control,
    .doc-modal textarea.form-control {
        min-height: auto;
    }

    .valsal-modal-shell .form-control:focus,
    .doc-modal .form-control:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .valsal-modal-shell .select2-container {
        width: 100% !important;
    }

    .valsal-modal-shell .select2-container .select2-selection--single {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        background: #fff;
        display: flex;
        align-items: center;
        padding: 0 .9rem;
        box-shadow: none;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1f2937;
        line-height: 1.4;
        padding-left: 0;
        padding-right: 1.8rem;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #7b8794;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        right: 10px;
    }

    .valsal-modal-shell .select2-container--default.select2-container--focus .select2-selection--single,
    .valsal-modal-shell .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .valsal-modal-shell .select2-container--default .select2-selection--multiple {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        background: #fff;
        padding: 4px .9rem;
        box-shadow: none;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin-top: 4px;
        font-size: 0.95rem;
        height: 28px;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--multiple .select2-search__field::placeholder {
        color: #7b8794;
    }

    .valsal-modal-shell .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
        background: #e8f3fb;
        border: 1px solid #bdd8ea;
        color: #1d4f73;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .valsal-modal-shell .select2-container--default.select2-container--focus .select2-selection--multiple,
    .valsal-modal-shell .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .valsal-tab-stack {
        display: grid;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .valsal-tab-section {
        display: grid;
        gap: .4rem;
    }

    .valsal-tab-section__label {
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b7f90;
    }

    .valsal-status-filter-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .65rem .85rem;
        padding: .7rem .85rem;
        border: 1px solid #e3edf6;
        border-radius: 12px;
        background: #f8fbfe;
    }

    .valsal-status-filter-row .valsal-tab-section__label {
        margin-right: .15rem;
        color: #51697e;
    }

    .valsal-status-pillbar {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .valsal-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-height: 32px;
        border: 1px solid #d7e3ee;
        border-radius: 999px;
        padding: .38rem .68rem;
        background: #fff;
        color: #2f5f84;
        font-size: .76rem;
        font-weight: 800;
        box-shadow: none;
        transition: all .16s ease;
    }

    .valsal-status-pill:hover,
    .valsal-status-pill:focus {
        border-color: #9bc8eb;
        background: #fafdff;
        outline: none;
    }

    .valsal-status-pill.is-active {
        color: #fff;
        background: #2277a8;
        border-color: #2277a8;
        box-shadow: 0 8px 18px rgba(34, 119, 168, 0.18);
    }

    .valsal-status-pill[data-valsal-status="rejected"].is-active {
        background: #dc3545;
        border-color: #dc3545;
        box-shadow: 0 8px 18px rgba(220, 53, 69, 0.18);
    }

    .valsal-status-pill__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 18px;
        padding: 0 .4rem;
        border-radius: 999px;
        background: #eef4fa;
        color: #315f84;
        font-size: .68rem;
        font-weight: 900;
    }

    .valsal-status-pill.is-active .valsal-status-pill__count {
        background: rgba(255, 255, 255, .24);
        color: #fff;
    }

    .valsal-monitor-tabs {
        border-bottom: 0;
        gap: .75rem;
        margin-bottom: 0;
    }

    .valsal-monitor-tabs .nav-link {
        border: 1px solid #d9e6f2;
        border-radius: 999px;
        color: #45627b;
        font-weight: 700;
        padding: .65rem 1rem;
        background: #f7fbff;
    }

    .valsal-monitor-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #1e88cf, #2ca58d);
        border-color: transparent;
        box-shadow: 0 12px 28px rgba(30, 136, 207, 0.24);
    }

    .valsal-monitor-tabs__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        margin-left: .45rem;
        padding: .15rem .5rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        font-size: .8rem;
    }

    .valsal-monitor-tabs .nav-link:not(.active) .valsal-monitor-tabs__count {
        background: #e2edf7;
        color: #2d6287;
    }

    .valsal-monitor-tabs__content {
        padding-top: .25rem;
    }

    .valsal-modal-shell .form-control[readonly],
    .valsal-modal-shell .form-control:disabled,
    .doc-modal .form-control[readonly],
    .doc-modal .form-control:disabled {
        background: linear-gradient(180deg, #eef4f8 0%, #e3edf5 100%);
        border-color: #c8d9e7;
        color: #5f7488;
        cursor: not-allowed;
    }

    .valsal-modal-shell textarea.form-control[readonly],
    .valsal-modal-shell textarea.form-control:disabled,
    .doc-modal textarea.form-control[readonly],
    .doc-modal textarea.form-control:disabled {
        background: linear-gradient(180deg, #eef4f8 0%, #e3edf5 100%);
    }

    @media (max-width: 991.98px) {
        .modal-xxl {
            max-width: 94vw;
        }
    }

    @media (max-width: 767.98px) {
        .budget-modal__footer {
            flex-direction: column;
        }

        .budget-modal__footer .btn {
            width: 100%;
        }

        .valsal-filter-actions {
            flex-direction: column;
        }

        .valsal-filter-actions .btn {
            width: 100%;
        }

        .valsal-status-filter-row {
            align-items: stretch;
        }

        .valsal-status-filter-row .valsal-tab-section__label {
            width: 100%;
        }

        .valsal-status-pillbar,
        .valsal-status-pill {
            width: 100%;
        }

        .valsal-status-pill {
            justify-content: space-between;
        }

    }
</style>

<script>
    (function () {
        var valsalApproveUrl = '<?= base_url('VALSAL_MyRep/approveDocument') ?>';
        var valsalApproveAllUrl = '<?= base_url('VALSAL_MyRep/approveAllDocuments') ?>';
        var valsalRejectUrl = '<?= base_url('VALSAL_MyRep/rejectDocument') ?>';
        var valsalPreviewBaseUrl = '<?= base_url('VALSAL_MyRep/previewDocument/') ?>';
        var valsalDownloadBaseUrl = '<?= base_url('VALSAL_MyRep/downloadDocument/') ?>';
        var valsalDownloadBundleBaseUrl = '<?= base_url('VALSAL_MyRep/downloadDocumentBundle/') ?>';
        var valsalPreviewImportUrl = '<?= base_url('VALSAL_MyRep/previewValsalImport') ?>';
        var valsalSaveImportUrl = '<?= base_url('VALSAL_MyRep/saveImportedValsal') ?>';
        var valsalDownloadReportUrl = '<?= base_url('VALSAL_MyRep/downloadReport') ?>';
        var valsalCityOptionsByRegional = <?= json_encode($cityOptionsByRegional, JSON_UNESCAPED_UNICODE) ?>;
        var valsalRegionalOptionsByCity = <?= json_encode($regionalOptionsByCity, JSON_UNESCAPED_UNICODE) ?>;
        var valsalSelectedStatus = '<?= htmlspecialchars((string) $selectedStatus, ENT_QUOTES) ?>';
        var valsalStatusSummaryByTab = <?= json_encode($valsalStatusSummaryByTab, JSON_UNESCAPED_UNICODE) ?>;
        var currentValsalDetailClusterId = 0;
        var importedValsalRows = [];
        var valsalSyncingSelection = false;

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getValsalStatusBadgeClass(statusLabel) {
            var value = String(statusLabel || '').toUpperCase().trim();
            if (value === 'DONE' || value === 'APPROVED' || value === 'VALSAL') return 'success';
            if (value === 'REJECTED') return 'danger';
            if (value === 'ON REVIEW' || value === 'UPLOADED') return 'warning';
            if (value === 'BAK') return 'info';
            if (value === 'TIDAK DIBUTUHKAN') return 'dark';
            return 'secondary';
        }

        function updateValsalDocDetailClusterInfo(documents, fallbackClusterName) {
            var firstDoc = documents && documents.length ? documents[0] : {};
            $('#valsal-doc-detail-cluster-name').text(String(firstDoc.cluster_name || fallbackClusterName || '-').trim() || '-');
            $('#valsal-doc-detail-cluster-code').text(String(firstDoc.cluster_code || '-').trim() || '-');
            $('#valsal-doc-detail-homepass-bak').text(Number(firstDoc.homepass_bak || 0) > 0 ? Number(firstDoc.homepass_bak || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '-');
            $('#valsal-doc-detail-homepass-valsal').text(Number(firstDoc.homepass_valsal || 0) > 0 ? Number(firstDoc.homepass_valsal || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '-');
            $('#valsal-doc-detail-regional-name').text(String(firstDoc.regional_name || '-').trim() || '-');
            $('#valsal-doc-detail-city-name').text(String(firstDoc.city_name || '-').trim() || '-');
            $('#valsal-doc-detail-district-name').text(String(firstDoc.district_name || '-').trim() || '-');
            $('#valsal-doc-detail-village-name').text(String(firstDoc.village_name || '-').trim() || '-');
        }

        function renderValsalDocDetailRows(documents) {
            if (!documents || !documents.length) {
                return '<tr><td colspan="9" class="text-center text-muted">Belum ada dokumen.</td></tr>';
            }

            return documents.map(function (doc) {
                var docName = escapeHtml(doc.doc_name || '-');
                var docStatusRaw = String(doc.status_file || '').toUpperCase().trim();
                var statusLabel = escapeHtml(doc.is_document_not_required == 1
                    ? 'Tidak Dibutuhkan'
                    : (docStatusRaw === 'UPLOADED'
                        ? 'ON REVIEW'
                        : (doc.status_file || (doc.file_name ? 'UPLOADED' : 'BELUM UPLOAD'))));
                var statusClass = getValsalStatusBadgeClass(statusLabel);
                var uploadBy = escapeHtml(String(doc.uploaded_by_name || '').trim() || '-');
                var picApproval = escapeHtml(String(doc.approved_by_name || doc.city_pic_approval_name || '').trim() || '-');
                var reviewLabel = 'Upload by : ' + uploadBy + '<br>PIC approval : ' + picApproval;
                var uploadedAt = escapeHtml(String(doc.uploaded_at || '').trim() || '-');
                var reviewedAt = escapeHtml(String(doc.reviewed_at || '').trim() || '-');
                var approvedAt = escapeHtml(String(doc.approved_at || '').trim() || '-');
                var remarkValue = escapeHtml(doc.remark || '');
                var fileSection = '<span class="text-muted small">Belum ada file</span>';
                var actionParts = [];

                if (doc.id_doc_file && doc.file_path) {
                    fileSection =
                        '<div class="small text-muted mb-1">' + escapeHtml(doc.file_name || '-') + '</div>' +
                        '<a href="' + valsalPreviewBaseUrl + Number(doc.id_doc_file) + '" target="_blank" class="btn btn-sm btn-outline-secondary mr-1">Preview</a>' +
                        '<a href="' + valsalDownloadBaseUrl + Number(doc.id_doc_file) + '" class="btn btn-sm btn-outline-primary mr-1">Download</a>' +
                        '<button type="button" class="btn btn-sm btn-outline-dark js-history-doc" data-toggle="modal" data-target="#modal-valsal-history-doc" data-cluster_name="' + escapeHtml(doc.cluster_name || '') + '" data-doc_name="' + docName + '" data-history="' + escapeHtml(JSON.stringify(doc.history || [])) + '">History</button>';
                }

                if (docStatusRaw === 'REJECTED' && <?= $canTambah ? 'true' : 'false' ?>) {
                    actionParts.push(
                        '<button type="button" class="btn btn-sm btn-outline-info btn-block js-detail-reupload-doc" ' +
                            'data-cluster_id="' + Number(doc.id_myrep_cluster || 0) + '" ' +
                            'data-cluster_name="' + escapeHtml(doc.cluster_name || '') + '" ' +
                            'data-doc_item_id="' + Number(doc.id_doc_item || 0) + '" ' +
                            'data-doc_name="' + docName + '" ' +
                            'data-doc_status="' + statusLabel + '" ' +
                            'data-doc_remark="' + remarkValue + '">' +
                            'Re-Upload' +
                        '</button>'
                    );
                }

                <?php if ($canApprove && $canApprovalAction): ?>
                if (doc.id_doc_file) {
                    actionParts.push(
                        '<form method="post" action="' + valsalApproveUrl + '" class="mb-2 js-valsal-inline-approve-form">' +
                            '<input type="hidden" name="id_doc_file" value="' + Number(doc.id_doc_file) + '">' +
                            '<input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Remark approve (opsional)">' +
                            ((docStatusRaw === 'UPLOADED' || docStatusRaw === 'REJECTED')
                                ? '<button type="submit" class="btn btn-sm btn-outline-success btn-block">Approve</button>'
                                : '<button type="submit" class="btn btn-sm btn-outline-success btn-block" disabled>Approve</button>') +
                        '</form>'
                    );
                    actionParts.push(
                        '<form method="post" action="' + valsalRejectUrl + '" class="js-valsal-inline-reject-form">' +
                            '<input type="hidden" name="id_doc_file" value="' + Number(doc.id_doc_file) + '">' +
                            '<input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Alasan reject" required>' +
                            '<button type="submit" class="btn btn-sm btn-outline-danger btn-block">Reject</button>' +
                        '</form>'
                    );
                }
                <?php endif; ?>

                var actionSection = actionParts.length
                    ? actionParts.join('')
                    : '<span class="text-muted small">Tidak ada aksi</span>';

                return '<tr>' +
                    '<td>' + docName + '</td>' +
                    '<td><span class="badge badge-' + statusClass + '">' + statusLabel + '</span></td>' +
                    '<td>' + fileSection + '</td>' +
                    '<td>' + reviewLabel + '</td>' +
                    '<td>' + uploadedAt + '</td>' +
                    '<td>' + reviewedAt + '</td>' +
                    '<td>' + approvedAt + '</td>' +
                    '<td>' + remarkValue + '</td>' +
                    '<td style="min-width:220px;">' + actionSection + '</td>' +
                '</tr>';
            }).join('');
        }

        function initValsalSelect(selector, modalSelector, placeholderText) {
            var $modal = $(modalSelector);
            var $select = $modal.find(selector);

            if (!$select.length || !$.fn.select2) {
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                width: '100%',
                placeholder: placeholderText,
                allowClear: true,
                dropdownParent: $modal
            });
        }

        function handleValsalFlashAlerts() {
            $('.js-valsal-flash-alert').each(function () {
                var $alert = $(this);
                var flashKey = $alert.data('flash-key');

                if (!flashKey || !window.sessionStorage) {
                    return;
                }

                if (window.sessionStorage.getItem(flashKey) === '1') {
                    $alert.remove();
                    return;
                }

                window.sessionStorage.setItem(flashKey, '1');
            });

            window.setTimeout(function () {
                $('.js-valsal-flash-alert').alert('close');
            }, 4000);
        }

        function bindDropzone(dropzoneSelector, inputSelector, labelSelector) {
            var dropzone = document.querySelector(dropzoneSelector);
            var input = document.querySelector(inputSelector);
            var label = document.querySelector(labelSelector);

            if (!dropzone || !input || !label) {
                return;
            }

            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            input.addEventListener('change', function () {
                label.textContent = (input.files && input.files.length > 0)
                    ? input.files[0].name
                    : 'Belum ada file dipilih';
            });
        }

        function resetValsalImportPreview() {
            importedValsalRows = [];
            $('#valsal-import-summary').text('Belum ada file dipreview');
            $('#valsal-save-import-btn').prop('disabled', true);
            $('#table_valsal_import_preview tbody').html('<tr><td colspan="10" class="text-center text-muted">Belum ada data preview</td></tr>');
        }

        function renderValsalImportPreview(rows) {
            if (!rows || !rows.length) {
                $('#table_valsal_import_preview tbody').html('<tr><td colspan="10" class="text-center text-muted">Belum ada data preview</td></tr>');
                return;
            }

            var html = rows.map(function (row) {
                var badgeClass = String(row.status || '').toLowerCase() === 'valid' ? 'success' : 'danger';
                return '<tr>' +
                    '<td>' + Number(row.row_number || 0) + '</td>' +
                    '<td>' + Number(row.cluster_id || 0) + '</td>' +
                    '<td>' + escapeHtml(row.city_name || '-') + '</td>' +
                    '<td>' + escapeHtml(row.cluster_name || '-') + '</td>' +
                    '<td>' + escapeHtml(row.cluster_code || '-') + '</td>' +
                    '<td class="text-right">' + Number(row.homepass_valsal || 0).toLocaleString('id-ID') + '</td>' +
                    '<td>' + escapeHtml(row.valsal_date || '-') + '</td>' +
                    '<td>' + escapeHtml(row.status_valsal || '-') + '</td>' +
                    '<td><span class="badge badge-' + badgeClass + '">' + escapeHtml(row.status || '-') + '</span></td>' +
                    '<td>' + escapeHtml(row.message || '-') + '</td>' +
                '</tr>';
            }).join('');

            $('#table_valsal_import_preview tbody').html(html);
        }

        function updateValsalCreateSubmitState() {
            var form = document.getElementById('valsal-create-form');
            var submitButton = document.getElementById('valsal-create-submit');

            if (!form || !submitButton) {
                return;
            }

            var inputs = form.querySelectorAll('.create-doc-input');
            if (!inputs.length) {
                submitButton.disabled = false;
                return;
            }

            var allReady = true;
            inputs.forEach(function (input) {
                var docItemId = input.getAttribute('data-doc-item-id') || '';
                var checkbox = docItemId ? form.querySelector('.js-create-doc-not-required[data-doc-item-id="' + docItemId + '"]') : null;
                var isNotRequired = checkbox ? checkbox.checked : false;
                if (!isNotRequired && (!input.files || !input.files.length)) {
                    allReady = false;
                }
            });

            submitButton.disabled = !allReady;
        }

        function syncValsalCreateNoDocumentState(docItemId) {
            var checkbox = document.querySelector('.js-create-doc-not-required[data-doc-item-id="' + docItemId + '"]');
            var input = document.getElementById('valsal-create-file-' + docItemId);
            var label = document.getElementById('valsal-create-file-name-' + docItemId);

            if (!checkbox || !input || !label) {
                return;
            }

            if (checkbox.checked) {
                input.value = '';
                input.disabled = true;
                input.required = false;
                label.textContent = 'File tidak diperlukan untuk item ini';
            } else {
                input.disabled = false;
                input.required = true;
                label.textContent = (input.files && input.files.length > 0)
                    ? input.files[0].name
                    : 'Belum ada file dipilih';
            }
        }

        function syncValsalDetailFooterButtons(clusterId, documents) {
            var hasPhysicalFiles = clusterId > 0 && (documents || []).some(function (doc) {
                return !!doc.file_path;
            });
            $('#valsal-doc-download-bundle-btn')
                .attr('href', valsalDownloadBundleBaseUrl + clusterId)
                .toggleClass('d-none', !hasPhysicalFiles);

            var canApproveAll = clusterId > 0 && (documents || []).some(function (doc) {
                var status = String(doc.status_file || '').toUpperCase().trim();
                return !!doc.id_doc_file && (status === 'UPLOADED' || status === 'REJECTED');
            });
            $('#valsal-doc-approve-all-btn')
                .data('cluster-id', clusterId)
                .toggleClass('d-none', !canApproveAll);
        }

        function syncClusterMeta($container) {
            var $select = $container.find('.js-valsal-cluster-selector').first();
            var $selected = $select.find('option:selected');
            $container.find('.js-cluster-regional').val($selected.data('regional_name') || '');
            $container.find('.js-cluster-province').val($selected.data('province_name') || '');
            $container.find('.js-cluster-city').val($selected.data('city_name') || '');
            $container.find('.js-cluster-name').val($selected.data('cluster_name') || '');
            $container.find('.js-bak-date').val($selected.data('bak_date') || '');
            $container.find('.js-homepass-bak').val($selected.data('homepass_bak') || '');
            if ($container.find('.js-homepass-valsal').length) {
                $container.find('.js-homepass-valsal').val($selected.data('homepass_bak') || '');
            }
        }

        function syncCityFromCluster($container) {
            var $clusterSelect = $container.find('.js-valsal-cluster-selector').first();
            var $citySelect = $container.find('.js-valsal-city-selector').first();
            var $selected = $clusterSelect.find('option:selected');

            if (!$clusterSelect.length || !$citySelect.length || !$selected.length || !$selected.val()) {
                return;
            }

            var clusterCity = ($selected.data('city-filter') || '').toString().toUpperCase();
            if (!clusterCity) {
                return;
            }

            if (($citySelect.val() || '').toString().toUpperCase() !== clusterCity) {
                valsalSyncingSelection = true;
                $citySelect.val(clusterCity);
                if ($citySelect.hasClass('select2-hidden-accessible')) {
                    $citySelect.trigger('change.select2');
                }
                valsalSyncingSelection = false;
            }
        }

        function filterValsalClusterOptions($modal) {
            var selectedCity = ($modal.find('.js-valsal-city-selector').val() || '').toUpperCase();
            var $clusterSelect = $modal.find('.js-valsal-cluster-selector');
            if (!$clusterSelect.length) {
                return;
            }

            if (selectedCity !== '') {
                var currentOption = $clusterSelect.find('option:selected');
                var currentCity = (currentOption.data('city-filter') || '').toString().toUpperCase();
                if (currentCity !== selectedCity) {
                    $clusterSelect.val('');
                }
            }

            // Hindari loop event city -> cluster -> city yang bisa memicu error Select2 (nodeName null).
            syncClusterMeta($modal);
        }

        function applyCreateClusterPreset($modal) {
            var presetClusterId = ($modal.attr('data-preset-cluster-id') || '').toString();
            var presetCity = ($modal.attr('data-preset-city') || '').toString().toUpperCase();
            var $citySelect = $modal.find('.js-valsal-city-selector');
            var $clusterSelect = $modal.find('.js-valsal-cluster-selector');

            if (presetCity !== '') {
                $citySelect.val(presetCity);
                if ($citySelect.hasClass('select2-hidden-accessible')) {
                    $citySelect.trigger('change.select2');
                }
                filterValsalClusterOptions($modal);
            }

            if (presetClusterId !== '') {
                $clusterSelect.val(presetClusterId);
                if ($clusterSelect.hasClass('select2-hidden-accessible')) {
                    $clusterSelect.trigger('change.select2');
                }
                syncClusterMeta($modal);
            }
        }

        $(function () {
            var valsalTables = {};
            var valsalActiveFlowTab = 'on_process';
            var valsalStatusFilter = '';
            var valsalTableConfigs = {
                '#table_valsal_on_process': { tab: 'on_process' },
                '#table_valsal_ny_batch_drm': { tab: 'ny_batch' },
                '#table_valsal_all': { tab: 'all' }
            };

            function getActiveValsalTableSelector() {
                var href = $('#valsal-monitor-tab .nav-link.active').attr('href') || '#valsal-on-process-pane';
                if (href === '#valsal-ny-batch-pane') {
                    return '#table_valsal_ny_batch_drm';
                }
                if (href === '#valsal-all-pane') {
                    return '#table_valsal_all';
                }
                return '#table_valsal_on_process';
            }

            function updateValsalStatusCounts(tab) {
                var summary = valsalStatusSummaryByTab[tab] || {};
                $('[data-valsal-status-count]').each(function () {
                    var key = String($(this).data('valsal-status-count') || '');
                    var value = Number(summary[key] || 0);
                    $(this).text(value.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                });
            }

            function applyValsalStatusFilterToTable(table) {
                if (!table) {
                    return;
                }

                var keyword = '';
                if (valsalStatusFilter === 'waiting_input') {
                    keyword = 'WAITING INPUT';
                } else if (valsalStatusFilter === 'on_review') {
                    keyword = 'ON REVIEW';
                } else if (valsalStatusFilter === 'rejected') {
                    keyword = 'REJECTED';
                }

                table.column(9).search(keyword, false, false).draw();
            }

            function syncValsalStatusFilterButtons() {
                $('.js-valsal-status-filter').each(function () {
                    $(this).toggleClass('is-active', String($(this).data('valsal-status') || '').trim() === valsalStatusFilter);
                });
            }

            handleValsalFlashAlerts();

            if ($.fn.DataTable) {
                Object.keys(valsalTableConfigs).forEach(function (selector) {
                    if (!$(selector).length) {
                        return;
                    }

                    try {
                        valsalTables[selector] = $(selector).DataTable({
                            responsive: false,
                            scrollX: true,
                            scrollCollapse: true,
                            autoWidth: false,
                            order: [[0, 'asc']],
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                            footerCallback: function (row, data, start, end, display) {
                                var api = this.api();
                                var parseNumber = function (value) {
                                    if (typeof value === 'string') {
                                        value = value.replace(/<[^>]*>/g, '');
                                        value = value.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
                                    }
                                    var parsed = parseFloat(value);
                                    return isNaN(parsed) ? 0 : parsed;
                                };

                                var totalHpBak = api
                                    .column(4, { search: 'applied' })
                                    .data()
                                    .reduce(function (sum, val) {
                                        return sum + parseNumber(val);
                                    }, 0);

                                var totalHpValsal = api
                                    .column(5, { search: 'applied' })
                                    .data()
                                    .reduce(function (sum, val) {
                                        return sum + parseNumber(val);
                                    }, 0);

                                $(api.column(4).footer()).html(
                                    totalHpBak.toLocaleString('id-ID', { maximumFractionDigits: 0 })
                                );
                                $(api.column(5).footer()).html(
                                    totalHpValsal.toLocaleString('id-ID', { maximumFractionDigits: 0 })
                                );
                            },
                            language: {
                                emptyTable: 'Belum ada data untuk tab ini.'
                            }
                        });
                    } catch (err) {
                        console.error('DataTable init failed for', selector, err);
                    }
                });

                $('a[data-toggle="tab"][href^="#valsal-"]').on('shown.bs.tab', function () {
                    var tableSelector = getActiveValsalTableSelector();
                    valsalActiveFlowTab = valsalTableConfigs[tableSelector] ? valsalTableConfigs[tableSelector].tab : 'on_process';
                    updateValsalStatusCounts(valsalActiveFlowTab);

                    Object.keys(valsalTables).forEach(function (selector) {
                        valsalTables[selector].columns.adjust();
                    });
                    applyValsalStatusFilterToTable(valsalTables[tableSelector]);
                });

                $('.js-valsal-status-filter').on('click', function () {
                    var nextStatus = String($(this).data('valsal-status') || '').trim();
                    valsalStatusFilter = valsalStatusFilter === nextStatus ? '' : nextStatus;
                    syncValsalStatusFilterButtons();
                    applyValsalStatusFilterToTable(valsalTables[getActiveValsalTableSelector()]);
                });
            }

            initValsalSelect('.js-valsal-city-selector', '#modal-valsal-create', 'Pilih kota');
            initValsalSelect('.js-valsal-cluster-select', '#modal-valsal-create', 'Pilih cluster');

            $(document).on('change', '.js-valsal-cluster-selector', function () {
                if (valsalSyncingSelection) {
                    return;
                }
                var $container = $(this).closest('.modal-body, .modal-content');
                syncCityFromCluster($container);
                syncClusterMeta($container);
            });

            $(document).on('change', '.js-valsal-city-selector', function () {
                if (valsalSyncingSelection) {
                    return;
                }
                filterValsalClusterOptions($(this).closest('.modal-body, .modal-content'));
            });

            $('#modal-valsal-create').on('shown.bs.modal', function () {
                $(this).find('.js-valsal-city-selector').val('');
                $(this).find('.js-valsal-cluster-selector').val('');
                $(this).find('.js-valsal-city-selector').trigger('change.select2');
                $(this).find('.js-valsal-cluster-selector').trigger('change.select2');
                filterValsalClusterOptions($(this));
                applyCreateClusterPreset($(this));
                syncClusterMeta($(this));
                $(this).find('.create-doc-input').val('');
                $(this).find('.create-doc-file-name').text('Belum ada file dipilih');
                $(this).find('.js-create-doc-not-required').prop('checked', false);
                $(this).find('.create-doc-input').prop('disabled', false).prop('required', true);
                updateValsalCreateSubmitState();
            }).on('hidden.bs.modal', function () {
                var $citySelect = $(this).find('.js-valsal-city-selector');
                var $clusterSelect = $(this).find('.js-valsal-cluster-select');
                $(this).removeAttr('data-preset-cluster-id').removeAttr('data-preset-city');
                if ($citySelect.hasClass('select2-hidden-accessible')) {
                    $citySelect.select2('close');
                }
                if ($clusterSelect.hasClass('select2-hidden-accessible')) {
                    $clusterSelect.select2('close');
                }
            });

            $(document).on('change', '.create-doc-input', function () {
                updateValsalCreateSubmitState();
            });

            $(document).on('change', '.js-create-doc-not-required', function () {
                syncValsalCreateNoDocumentState($(this).data('doc-item-id'));
                updateValsalCreateSubmitState();
            });

            $(document).on('click', '.js-start-valsal', function () {
                var $button = $(this);
                var presetClusterId = ($button.attr('data-cluster-id') || $button.data('clusterId') || '').toString();
                var presetCity = ($button.attr('data-city-name') || $button.data('cityName') || '').toString().toUpperCase();
                $('#modal-valsal-create')
                    .attr('data-preset-cluster-id', presetClusterId)
                    .attr('data-preset-city', presetCity);
            });

            $(document).on('click', '.js-edit-valsal', function () {
                var $button = $(this);
                var $modal = $('#modal-valsal-edit');

                $modal.find('#edit_id_myrep_cluster').val($button.data('id_myrep_cluster'));
                $modal.find('#edit_cluster_name').val($button.data('cluster_name'));
                $modal.find('#edit_regional_name').val($button.data('regional_name'));
                $modal.find('#edit_province_name').val($button.data('province_name'));
                $modal.find('#edit_city_name').val($button.data('city_name'));
                $modal.find('#edit_homepass_bak').val($button.data('homepass_bak'));
                $modal.find('#edit_homepass_valsal').val($button.data('homepass_valsal'));
                $modal.find('#edit_bak_date').val($button.data('bak_date'));
                $modal.find('#edit_valsal_date').val($button.data('valsal_date'));
                $modal.find('#edit_status_valsal').val($button.data('status_valsal'));
                $modal.find('#edit_remark_valsal').val($button.data('remark_valsal'));
            });

            $(document).on('click', '.js-upload-doc', function () {
                var $button = $(this);
                $('#upload_cluster_id').val($button.data('cluster_id'));
                $('#upload_doc_item_id').val($button.data('doc_item_id'));
                $('#upload_cluster_name').val($button.data('cluster_name'));
                $('#upload_doc_cluster_caption').text($button.data('cluster_name'));
                $('#upload_doc_name').val($button.data('doc_name'));
                $('#upload_doc_status').val($button.data('doc_status'));
                $('#upload_doc_remark').val($button.data('doc_remark'));
                $('#upload_doc_not_required').prop('checked', false);
                $('#valsal-upload-file-input').val('').prop('disabled', false).prop('required', true);
                $('#valsal-upload-file-name').text('Belum ada file dipilih');
                $('#valsal-upload-progress-panel').hide();
                $('#valsal-upload-progress-bar').removeClass('success').css('width', '0%');
                $('#valsal-upload-progress-percent').text('0%');
                $('#valsal-upload-document-submit').prop('disabled', false).text('Upload Dokumen');
            });

            $(document).on('click', '.js-detail-reupload-doc', function () {
                var $button = $(this);
                $('#modal-valsal-doc-detail').modal('hide');
                $('#upload_cluster_id').val($button.data('cluster_id'));
                $('#upload_doc_item_id').val($button.data('doc_item_id'));
                $('#upload_cluster_name').val($button.data('cluster_name'));
                $('#upload_doc_cluster_caption').text($button.data('cluster_name'));
                $('#upload_doc_name').val($button.data('doc_name'));
                $('#upload_doc_status').val($button.data('doc_status'));
                $('#upload_doc_remark').val($button.data('doc_remark'));
                $('#upload_doc_not_required').prop('checked', false);
                $('#valsal-upload-file-input').val('').prop('disabled', false).prop('required', true);
                $('#valsal-upload-file-name').text('Belum ada file dipilih');
                $('#valsal-upload-progress-panel').hide();
                $('#valsal-upload-progress-bar').removeClass('success').css('width', '0%');
                $('#valsal-upload-progress-percent').text('0%');
                $('#valsal-upload-document-submit').prop('disabled', false).text('Upload Dokumen');
                window.setTimeout(function () {
                    $('#modal-valsal-upload-doc').modal('show');
                }, 180);
            });

            $(document).on('click', '.js-valsal-doc-detail', function () {
                var $button = $(this);
                var rawDocuments = $button.attr('data-documents');
                var documents = [];

                try {
                    documents = rawDocuments ? JSON.parse(rawDocuments) : [];
                } catch (e) {
                    documents = [];
                }

                currentValsalDetailClusterId = Number(documents.length ? (documents[0].id_myrep_cluster || 0) : 0);
                updateValsalDocDetailClusterInfo(documents, $button.data('cluster_name') || '-');
                $('#valsal-doc-detail-body').html(renderValsalDocDetailRows(documents));
                syncValsalDetailFooterButtons(currentValsalDetailClusterId, documents);
            });

            $(document).on('click', '.js-history-doc', function () {
                var $button = $(this);
                var rawHistory = $button.attr('data-history');
                var history = [];

                try {
                    history = rawHistory ? JSON.parse(rawHistory) : [];
                } catch (e) {
                    history = [];
                }

                $('#history_doc_name').text($button.data('doc_name'));
                $('#history_cluster_name').text($button.data('cluster_name'));

                if (!history.length) {
                    $('#history_doc_list').html('<li class="text-muted">Belum ada history.</li>');
                    return;
                }

                var html = '';
                history.forEach(function (entry) {
                    var actionLabel = entry.action_type || '-';
                    if (actionLabel === 'UPLOAD') actionLabel = 'Uploaded';
                    if (actionLabel === 'REUPLOAD') actionLabel = 'Re-uploaded';
                    if (actionLabel === 'APPROVE') actionLabel = 'Approved';
                    if (actionLabel === 'REJECT') actionLabel = 'Rejected';

                    html += '<li class="doc-history-item">' +
                        '<span class="doc-history-dot"></span>' +
                        '<div class="doc-history-title">' + actionLabel + '</div>' +
                        '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                        '<p class="doc-history-note"><strong>File:</strong> ' + (entry.file_name || '-') + '</p>' +
                        '<p class="doc-history-note"><strong>Remark:</strong> ' + (entry.remark || '-') + '</p>' +
                        '</li>';
                });

                $('#history_doc_list').html(html);
            });

            $(document).on('click', '#valsal-doc-approve-all-btn', function () {
                var clusterId = Number($(this).data('cluster-id') || currentValsalDetailClusterId || 0);
                var $button = $(this);

                if (clusterId <= 0) {
                    alert('Cluster dokumen VALSAL tidak valid.');
                    return;
                }

                if (!window.confirm('Approve semua dokumen yang masih menunggu review untuk cluster ini?')) {
                    return;
                }

                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Approving...');

                $.ajax({
                    url: valsalApproveAllUrl,
                    type: 'POST',
                    data: { cluster_id: clusterId },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response && response.status && response.data) {
                            currentValsalDetailClusterId = Number(response.data.cluster_id || clusterId);
                            updateValsalDocDetailClusterInfo(response.data.documents || [], response.data.cluster_name || '-');
                            $('#valsal-doc-detail-body').html(renderValsalDocDetailRows(response.data.documents || []));
                            syncValsalDetailFooterButtons(currentValsalDetailClusterId, response.data.documents || []);
                            return;
                        }

                        alert(response && response.message ? response.message : 'Approve semua dokumen gagal.');
                        $button.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Approve All');
                    },
                    error: function () {
                        alert('Approve semua dokumen gagal. Silakan coba lagi.');
                        $button.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Approve All');
                    }
                });
            });

            $(document).on('change', '#upload_doc_not_required', function () {
                var checked = $(this).is(':checked');
                $('#valsal-upload-file-input').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#valsal-upload-file-input').val('');
                    $('#valsal-upload-file-name').text('File tidak diperlukan untuk item ini');
                } else {
                    $('#valsal-upload-file-name').text('Belum ada file dipilih');
                }
            });

            $('#modal-valsal-create form').on('submit', function (e) {
                var missingDocName = '';
                $(this).find('.create-doc-input').each(function () {
                    if (missingDocName) {
                        return;
                    }

                    var docItemId = $(this).data('doc-item-id');
                    var isNotRequired = $('.js-create-doc-not-required[data-doc-item-id="' + docItemId + '"]').is(':checked');
                    if (isNotRequired) {
                        return;
                    }

                    var fileInput = this;
                    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                    if (!hasFile) {
                        missingDocName = $(this).data('doc-name') || 'dokumen';
                    }
                });

                if (missingDocName) {
                    e.preventDefault();
                    alert('File ' + missingDocName + ' wajib diupload atau tandai tidak dibutuhkan saat input VALSAL.');
                }
            });

            $('#valsal-upload-document-form').on('submit', function (e) {
                e.preventDefault();

                var form = this;
                var submitButton = $('#valsal-upload-document-submit');
                var progressPanel = $('#valsal-upload-progress-panel');
                var progressBar = $('#valsal-upload-progress-bar');
                var progressPercent = $('#valsal-upload-progress-percent');
                var formData = new FormData(form);

                submitButton.prop('disabled', true).text('Uploading...');
                progressPanel.show();
                progressBar.removeClass('success').css('width', '0%');
                progressPercent.text('0%');

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (evt) {
                                if (evt.lengthComputable) {
                                    var percent = Math.round((evt.loaded / evt.total) * 100);
                                    progressBar.css('width', percent + '%');
                                    progressPercent.text(percent + '%');
                                }
                            }, false);
                        }
                        return xhr;
                    },
                    success: function (response) {
                        progressBar.addClass('success').css('width', '100%');
                        progressPercent.text('100%');

                        if (response && response.status) {
                            window.location.href = response.redirect_url || window.location.href;
                            return;
                        }

                        alert(response && response.message ? response.message : 'Upload gagal.');
                        submitButton.prop('disabled', false).text('Upload Dokumen');
                    },
                    error: function () {
                        alert('Upload gagal. Silakan coba lagi.');
                        submitButton.prop('disabled', false).text('Upload Dokumen');
                    }
                });
            });

            $(document).on('submit', '.js-valsal-inline-approve-form, .js-valsal-inline-reject-form', function (e) {
                e.preventDefault();

                var form = this;
                var $form = $(form);
                var isReject = $form.hasClass('js-valsal-inline-reject-form');
                var $submitButton = $form.find('button[type="submit"]');
                var $remarkInput = $form.find('input[name="remark"]');

                if (isReject && !$remarkInput.val().trim()) {
                    alert('Alasan reject wajib diisi.');
                    $remarkInput.focus();
                    return;
                }

                if (!window.confirm(isReject ? 'Reject dokumen ini?' : 'Approve dokumen ini?')) {
                    return;
                }

                $submitButton.prop('disabled', true).text(isReject ? 'Rejecting...' : 'Approving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response && response.status && response.data) {
                            currentValsalDetailClusterId = Number(response.data.cluster_id || currentValsalDetailClusterId || 0);
                            updateValsalDocDetailClusterInfo(response.data.documents || [], response.data.cluster_name || '-');
                            $('#valsal-doc-detail-body').html(renderValsalDocDetailRows(response.data.documents || []));
                            syncValsalDetailFooterButtons(currentValsalDetailClusterId, response.data.documents || []);
                            return;
                        }

                        alert(response && response.message ? response.message : 'Proses dokumen gagal.');
                        $submitButton.prop('disabled', false).text(isReject ? 'Reject' : 'Approve');
                    },
                    error: function () {
                        alert('Proses dokumen gagal. Silakan coba lagi.');
                        $submitButton.prop('disabled', false).text(isReject ? 'Reject' : 'Approve');
                    }
                });
            });

            bindDropzone('#valsal-upload-dropzone', '#valsal-upload-file-input', '#valsal-upload-file-name');
            bindDropzone('#valsal-import-dropzone', '#valsal-import-file-input', '#valsal-import-file-name');
            $('.create-doc-input').each(function () {
                var inputId = this.id;
                if (!inputId) {
                    return;
                }

                var suffix = inputId.replace('valsal-create-file-', '');
                bindDropzone('#valsal-create-dropzone-' + suffix, '#' + inputId, '#valsal-create-file-name-' + suffix);
            });
            updateValsalCreateSubmitState();

            $('#modal-valsal-import').on('shown.bs.modal', function () {
                resetValsalImportPreview();
                $('#valsal-import-file-input').val('');
                $('#valsal-import-file-name').text('Belum ada file dipilih');
            });

            $('#valsal-import-file-input').on('change', function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) {
                    return;
                }

                var formData = new FormData($('#valsal-import-preview-form')[0]);
                formData.set('file_excel', file);
                $('#valsal-import-summary').text('Memproses preview...');

                $.ajax({
                    url: valsalPreviewImportUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (!response || !response.status) {
                            resetValsalImportPreview();
                            alert(response && response.message ? response.message : 'Preview import VALSAL gagal.');
                            return;
                        }

                        importedValsalRows = response.valid_rows || [];
                        $('#valsal-import-summary').text(response.message || 'Preview selesai');
                        $('#valsal-save-import-btn').prop('disabled', !importedValsalRows.length);
                        renderValsalImportPreview(response.rows || []);
                    },
                    error: function () {
                        resetValsalImportPreview();
                        alert('Terjadi kesalahan saat preview import VALSAL.');
                    }
                });
            });

            $('#valsal-save-import-btn').on('click', function () {
                if (!importedValsalRows.length) {
                    alert('Belum ada data valid untuk disimpan.');
                    return;
                }

                $.ajax({
                    url: valsalSaveImportUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { rows_json: JSON.stringify(importedValsalRows) },
                    success: function (response) {
                        if (response && response.status) {
                            alert(response.message || 'Import VALSAL berhasil.');
                            window.location.reload();
                            return;
                        }

                        alert(response && response.message ? response.message : 'Gagal menyimpan import VALSAL.');
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat menyimpan import VALSAL.');
                    }
                });
            });

            function getSelectedArray($el) {
                var values = $el.val();
                return Array.isArray(values) ? values.filter(Boolean) : [];
            }

            function toUpperUnique(values) {
                return Array.from(new Set((values || []).map(function (v) { return String(v || '').toUpperCase().trim(); }).filter(Boolean)));
            }

            function allRegionalOptions() {
                return toUpperUnique(Object.keys(valsalCityOptionsByRegional || {}));
            }

            function allCityOptions() {
                var cities = [];
                Object.keys(valsalCityOptionsByRegional || {}).forEach(function (regional) {
                    cities = cities.concat(valsalCityOptionsByRegional[regional] || []);
                });
                return toUpperUnique(cities);
            }

            function allowedCitiesByRegionals(regionals) {
                if (!regionals.length) return allCityOptions();
                var cities = [];
                regionals.forEach(function (regional) {
                    cities = cities.concat(valsalCityOptionsByRegional[regional] || []);
                });
                return toUpperUnique(cities);
            }

            function allowedRegionalsByCities(cities) {
                if (!cities.length) return allRegionalOptions();
                var regionals = [];
                cities.forEach(function (city) {
                    regionals = regionals.concat(valsalRegionalOptionsByCity[city] || []);
                });
                return toUpperUnique(regionals);
            }

            function renderSelectOptions($el, availableValues, selectedValues) {
                var selectedSet = {};
                selectedValues.forEach(function (v) { selectedSet[String(v).toUpperCase()] = true; });
                var html = '';
                availableValues.forEach(function (value) {
                    var selectedAttr = selectedSet[String(value).toUpperCase()] ? ' selected' : '';
                    html += '<option value="' + escapeHtml(value) + '"' + selectedAttr + '>' + escapeHtml(value) + '</option>';
                });
                $el.html(html).trigger('change.select2');
            }

            function syncValsalRegionCityFilters(changedFrom) {
                var $regional = $('#valsal_download_regional');
                var $city = $('#valsal_download_city');
                var selectedRegionals = toUpperUnique(getSelectedArray($regional));
                var selectedCities = toUpperUnique(getSelectedArray($city));
                var changed = true;
                var guard = 0;

                while (changed && guard < 5) {
                    guard++;
                    changed = false;

                    var allowedCities = allowedCitiesByRegionals(selectedRegionals);
                    var nextSelectedCities = selectedCities.filter(function (city) { return allowedCities.indexOf(city) !== -1; });
                    if (nextSelectedCities.length !== selectedCities.length) {
                        selectedCities = nextSelectedCities;
                        changed = true;
                    }

                    var allowedRegionals = allowedRegionalsByCities(selectedCities);
                    var nextSelectedRegionals = selectedRegionals.filter(function (regional) { return allowedRegionals.indexOf(regional) !== -1; });
                    if (nextSelectedRegionals.length !== selectedRegionals.length) {
                        selectedRegionals = nextSelectedRegionals;
                        changed = true;
                    }
                }

                var finalAllowedRegionals = allowedRegionalsByCities(selectedCities);
                var finalAllowedCities = allowedCitiesByRegionals(selectedRegionals);

                if (changedFrom === 'regional' && selectedRegionals.length) {
                    finalAllowedCities = allowedCitiesByRegionals(selectedRegionals);
                }
                if (changedFrom === 'city' && selectedCities.length) {
                    finalAllowedRegionals = allowedRegionalsByCities(selectedCities);
                }

                renderSelectOptions($regional, finalAllowedRegionals, selectedRegionals);
                renderSelectOptions($city, finalAllowedCities, selectedCities);
            }

            $('#modal-valsal-download-report').on('shown.bs.modal', function () {
                $('#valsal_download_date_start').val('');
                $('#valsal_download_date_end').val('');

                $('#valsal_download_regional, #valsal_download_city').select2({
                    width: '100%',
                    dropdownParent: $('#modal-valsal-download-report'),
                    placeholder: 'Pilih satu atau lebih',
                    allowClear: true
                });

                renderSelectOptions($('#valsal_download_regional'), allRegionalOptions(), []);
                renderSelectOptions($('#valsal_download_city'), allCityOptions(), []);
            });

            $('#valsal_download_regional').on('change', function () {
                syncValsalRegionCityFilters('regional');
            });

            $('#valsal_download_city').on('change', function () {
                syncValsalRegionCityFilters('city');
            });

            $('#valsal-download-report-submit-btn').on('click', function () {
                var regionalValues = getSelectedArray($('#valsal_download_regional'));
                var cityValues = getSelectedArray($('#valsal_download_city'));
                var valsalDateStart = ($('#valsal_download_date_start').val() || '').trim();
                var valsalDateEnd = ($('#valsal_download_date_end').val() || '').trim();

                if (valsalDateStart && valsalDateEnd && valsalDateStart > valsalDateEnd) {
                    alert('Tanggal VALSAL start tidak boleh lebih besar dari end.');
                    return;
                }

                var params = new URLSearchParams();
                if (valsalSelectedStatus) {
                    params.set('status', valsalSelectedStatus);
                }
                regionalValues.forEach(function (regional) { params.append('regional[]', regional); });
                cityValues.forEach(function (city) { params.append('city[]', city); });
                if (valsalDateStart) params.set('valsal_date_start', valsalDateStart);
                if (valsalDateEnd) params.set('valsal_date_end', valsalDateEnd);

                window.location.href = valsalDownloadReportUrl + '?' + params.toString();
            });
        });
    })();
</script>
