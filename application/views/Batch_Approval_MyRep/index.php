<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
if (!function_exists('batchStageLabel')) {
    function batchStageLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        $labels = [
            'DRAFT' => 'Draft',
            'WAITING HO' => 'Menunggu Review HO',
            'WAITING MYREP' => 'Menunggu Review EMR',
            'WAITING FINANCE' => 'Menunggu Finance',
            'WAITING_BATCH_APPROVAL' => 'Menunggu Nomor Batch Approval',
            'BATCH_APPROVED' => 'Batch Approval Disetujui',
            'HOLD' => 'Ditahan',
            'WAITING_PRE_ZEYN_DOC' => 'NY Dokumen Tahap 1',
            'PRE_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 1',
            'PRE_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 1',
            'PRE_ZEYN_FINANCE_APPROVED' => 'Approved Finance Dokumen Tahap 1',
            'WAITING_FINANCE_RELEASE' => 'Menunggu Pembayaran Finance',
            'RELEASED' => 'Donasi Dibayarkan',
            'WAITING_POST_ZEYN_DOC' => 'NY Dokumen Tahap 2',
            'POST_ZEYN_DOC_ON_REVIEW' => 'On Review Dokumen Tahap 2',
            'POST_ZEYN_DOC_APPROVED' => 'Approved Dokumen Tahap 2',
            'POST_ZEYN_FINANCE_ON_REVIEW' => 'On Review Finance Dokumen Tahap 2',
            'WAITING_ASTRI_SUBMISSION' => 'Menunggu Submit Astri',
            'ASTRI_ON_REVIEW' => 'On Review Astri',
            'ASTRI_APPROVED' => 'Approved Astri',
            'PO_DONASI' => 'PO Donasi',
            'INVOICE' => 'Invoice',
            'DONE BATCH APPROVAL' => 'Batch Approval Selesai',
            'WAITING DOC' => 'Menunggu Dokumen Post Donasi',
            'COMPLETED' => 'Done',
            'REJECTED' => 'Ditolak',
            'WAITING INPUT' => 'Menunggu Pengajuan',
        ];

        return $labels[$status] ?? ($status !== '' ? ucwords(strtolower(str_replace('_', ' ', $status))) : 'Draft');
    }
}
$statusOptions = [
    'DRAFT' => batchStageLabel('DRAFT'),
    'BATCH_APPROVED' => batchStageLabel('BATCH_APPROVED'),
    'HOLD' => batchStageLabel('HOLD'),
    'WAITING_PRE_ZEYN_DOC' => batchStageLabel('WAITING_PRE_ZEYN_DOC'),
    'PRE_ZEYN_DOC_ON_REVIEW' => batchStageLabel('PRE_ZEYN_DOC_ON_REVIEW'),
    'PRE_ZEYN_DOC_APPROVED' => batchStageLabel('PRE_ZEYN_DOC_APPROVED'),
    'PRE_ZEYN_FINANCE_ON_REVIEW' => batchStageLabel('PRE_ZEYN_FINANCE_ON_REVIEW'),
    'PRE_ZEYN_FINANCE_APPROVED' => batchStageLabel('PRE_ZEYN_FINANCE_APPROVED'),
    'WAITING_FINANCE_RELEASE' => batchStageLabel('WAITING_FINANCE_RELEASE'),
    'RELEASED' => batchStageLabel('RELEASED'),
    'WAITING_POST_ZEYN_DOC' => batchStageLabel('WAITING_POST_ZEYN_DOC'),
    'POST_ZEYN_DOC_ON_REVIEW' => batchStageLabel('POST_ZEYN_DOC_ON_REVIEW'),
    'POST_ZEYN_DOC_APPROVED' => batchStageLabel('POST_ZEYN_DOC_APPROVED'),
    'POST_ZEYN_FINANCE_ON_REVIEW' => batchStageLabel('POST_ZEYN_FINANCE_ON_REVIEW'),
    'WAITING_ASTRI_SUBMISSION' => batchStageLabel('WAITING_ASTRI_SUBMISSION'),
    'ASTRI_ON_REVIEW' => batchStageLabel('ASTRI_ON_REVIEW'),
    'ASTRI_APPROVED' => batchStageLabel('ASTRI_APPROVED'),
    'PO_DONASI' => batchStageLabel('PO_DONASI'),
    'INVOICE' => batchStageLabel('INVOICE'),
    'REJECTED' => batchStageLabel('REJECTED'),
];
$summaryNyBatch = 0;
$summaryOnProses = 0;
$summaryDone = 0;
$summaryRejected = 0;
$createCityOptions = [];
$nyDrmRows = [];
$donationStageSummary = [];
$donationStageOrder = [
    'BATCH_APPROVED' => batchStageLabel('BATCH_APPROVED'),
    'WAITING_PRE_ZEYN_DOC' => batchStageLabel('WAITING_PRE_ZEYN_DOC'),
    'PRE_ZEYN_DOC_ON_REVIEW' => batchStageLabel('PRE_ZEYN_DOC_ON_REVIEW'),
    'PRE_ZEYN_DOC_APPROVED' => batchStageLabel('PRE_ZEYN_DOC_APPROVED'),
    'PRE_ZEYN_FINANCE_ON_REVIEW' => batchStageLabel('PRE_ZEYN_FINANCE_ON_REVIEW'),
    'PRE_ZEYN_FINANCE_APPROVED' => batchStageLabel('PRE_ZEYN_FINANCE_APPROVED'),
    'WAITING_FINANCE_RELEASE' => batchStageLabel('WAITING_FINANCE_RELEASE'),
    'RELEASED' => batchStageLabel('RELEASED'),
    'WAITING_POST_ZEYN_DOC' => batchStageLabel('WAITING_POST_ZEYN_DOC'),
    'POST_ZEYN_DOC_ON_REVIEW' => batchStageLabel('POST_ZEYN_DOC_ON_REVIEW'),
    'POST_ZEYN_DOC_APPROVED' => batchStageLabel('POST_ZEYN_DOC_APPROVED'),
    'POST_ZEYN_FINANCE_ON_REVIEW' => batchStageLabel('POST_ZEYN_FINANCE_ON_REVIEW'),
    'WAITING_ASTRI_SUBMISSION' => batchStageLabel('WAITING_ASTRI_SUBMISSION'),
    'ASTRI_ON_REVIEW' => batchStageLabel('ASTRI_ON_REVIEW'),
    'ASTRI_APPROVED' => batchStageLabel('ASTRI_APPROVED'),
    'PO_DONASI' => batchStageLabel('PO_DONASI'),
    'INVOICE' => batchStageLabel('INVOICE'),
    'HOLD' => batchStageLabel('HOLD'),
    'REJECTED' => batchStageLabel('REJECTED'),
];
$postBatchStatuses = [
    'DRM',
    'RFS',
    'ATP',
    'DONE',
];
$donationDoneStatuses = ['COMPLETED', 'INVOICE'];
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'TAMBAH') : true;
$canEdit = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'EDIT') : true;
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'HAPUS') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Batch_Approval_MyRep', 'APPROVAL') : true;
$clusterReviewPicMap = isset($clusterReviewPicMap) && is_array($clusterReviewPicMap) ? $clusterReviewPicMap : [];

foreach ($eligibleClusterOptions as $clusterOption) {
    $cityName = trim((string) ($clusterOption['city_name'] ?? ''));
    if ($cityName !== '') {
        $createCityOptions[strtoupper($cityName)] = $cityName;
    }
}

asort($createCityOptions);

foreach ($clusterRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
    $batchStatus = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
    $hasBatch = (int) ($row['id_batch_approval'] ?? 0) > 0;

    if (
        !in_array($currentStatus, $postBatchStatuses, true)
        && (
            !$hasBatch
            || (!in_array($batchStatus, $donationDoneStatuses, true) && $batchStatus !== 'REJECTED')
        )
    ) {
        $nyDrmRows[] = $row;
    }

    if (!$hasBatch && $currentStatus === 'VALSAL') {
        $summaryNyBatch++;
    }

    if ($hasBatch && !in_array($batchStatus, array_merge($donationDoneStatuses, ['REJECTED']), true)) {
        $summaryOnProses++;
    }

    if (
        $hasBatch
        && in_array($batchStatus, $donationDoneStatuses, true)
        && !in_array($currentStatus, $postBatchStatuses, true)
    ) {
        $summaryDone++;
    }

    if ($hasBatch && ($batchStatus === 'REJECTED' || $currentStatus === 'REJECTED')) {
        $summaryRejected++;
    }

    if ($hasBatch) {
        if (!isset($donationStageSummary[$batchStatus])) {
            $donationStageSummary[$batchStatus] = [
                'label' => $donationStageOrder[$batchStatus] ?? str_replace('_', ' ', $batchStatus),
                'count' => 0,
                'hp' => 0,
                'nominal_pengajuan' => 0,
                'nominal_release' => 0,
            ];
        }

        $donationStageSummary[$batchStatus]['count']++;
        $donationStageSummary[$batchStatus]['hp'] += (float) ($row['hp_donasi'] ?? 0);
        $donationStageSummary[$batchStatus]['nominal_pengajuan'] += (float) ($row['nominal_pengajuan_area'] ?? 0);
        $donationStageSummary[$batchStatus]['nominal_release'] += (float) ($row['nominal_release_finance'] ?? 0);
    }
}

$orderedDonationStageSummary = [];
foreach ($donationStageOrder as $stageCode => $stageLabel) {
    if (isset($donationStageSummary[$stageCode])) {
        $orderedDonationStageSummary[$stageCode] = $donationStageSummary[$stageCode];
    }
}
foreach ($donationStageSummary as $stageCode => $stageData) {
    if (!isset($orderedDonationStageSummary[$stageCode])) {
        $orderedDonationStageSummary[$stageCode] = $stageData;
    }
}

if (!function_exists('batchBadgeClass')) {
    function batchBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
            case 'COMPLETED':
            case 'BATCH_APPROVED':
            case 'PRE_ZEYN_DOC_APPROVED':
            case 'POST_ZEYN_DOC_APPROVED':
            case 'ASTRI_APPROVED':
            case 'PO_DONASI':
            case 'INVOICE':
                return 'success';
            case 'WAITING INPUT':
            case 'WAITING_BATCH_APPROVAL':
            case 'WAITING_ASTRI_SUBMISSION':
                return 'info';
            case 'HOLD':
            case 'WAITING DOC':
            case 'WAITING_PRE_ZEYN_DOC':
            case 'PRE_ZEYN_DOC_ON_REVIEW':
            case 'WAITING_FINANCE_RELEASE':
            case 'WAITING_POST_ZEYN_DOC':
            case 'POST_ZEYN_DOC_ON_REVIEW':
            case 'ASTRI_ON_REVIEW':
                return 'warning';
            case 'REJECTED':
                return 'danger';
            case 'WAITING HO':
            case 'WAITING MYREP':
                return 'info';
            case 'WAITING FINANCE':
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('batchDocLabel')) {
    function batchDocLabel($row)
    {
        if ((int) ($row['batch_doc_not_required'] ?? 0) === 1) {
            return 'TIDAK BUTUH DOKUMENT';
        }

        $status = strtoupper(trim((string) ($row['batch_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($row['batch_doc_file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }
}

if (!function_exists('batchStatusLabel')) {
    function batchStatusLabel($status)
    {
        return batchStageLabel($status);
    }
}

if (!function_exists('batchMoneyCompact')) {
    function batchMoneyCompact($value)
    {
        $value = (float) $value;
        if (abs($value) >= 1000000000) {
            return 'Rp ' . number_format($value / 1000000000, 1, ',', '.') . ' M';
        }
        if (abs($value) >= 1000000) {
            return 'Rp ' . number_format($value / 1000000, 1, ',', '.') . ' Jt';
        }

        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('batchAgingBadgeClass')) {
    function batchAgingBadgeClass($agingDays)
    {
        if ($agingDays === null) {
            return 'secondary';
        }

        return (int) $agingDays > 17 ? 'danger' : 'success';
    }
}

if (!function_exists('batchCountCalendarDays')) {
    function batchCountCalendarDays($startDateString, $endDateString = null)
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

        return (int) $start->diff($end)->days;
    }
}

if (!function_exists('batchSlaInfo')) {
    function batchSlaInfo($row)
    {
        $approvedValsalDate = trim((string) ($row['valsal_approved_at'] ?? ''));
        if ($approvedValsalDate === '') {
            $approvedValsalDate = trim((string) ($row['valsal_date'] ?? ''));
        }

        if ($approvedValsalDate === '' || $approvedValsalDate === '0000-00-00') {
            return [
                'start_date' => null,
                'aging_days' => null,
            ];
        }

        $approvalEmrDate = trim((string) ($row['submitted_to_finance_at'] ?? ''));
        return [
            'start_date' => substr($approvedValsalDate, 0, 10),
            'aging_days' => batchCountCalendarDays($approvedValsalDate, $approvalEmrDate !== '' ? $approvalEmrDate : date('Y-m-d')),
        ];
    }
}

if (!function_exists('batchSlaBadgeClass')) {
    function batchSlaBadgeClass($slaInfo)
    {
        if (($slaInfo['aging_days'] ?? null) === null) {
            return 'secondary';
        }

        return (int) $slaInfo['aging_days'] > 17 ? 'danger' : 'success';
    }
}

$renderBatchTableRows = static function (array $rows, $docReady, $batchModel) use ($canTambah, $canEdit, $canHapus, $clusterReviewPicMap) {
    foreach ($rows as $index => $row) {
        $slaInfo = batchSlaInfo($row);
        $hasBatch = (int) ($row['id_batch_approval'] ?? 0) > 0;
        $batchStageCode = strtoupper(trim((string) ($row['display_staging_status'] ?? $row['staging_status'] ?? 'DRAFT')));
        $batchStageLabel = $hasBatch ? batchStatusLabel($batchStageCode) : batchStatusLabel('WAITING INPUT');
        $isWaitingInputStage = !$hasBatch || $batchStageCode === 'WAITING INPUT';
        $canStartBatchInput = !$hasBatch;
        $batchDocLabel = $hasBatch ? batchDocLabel($row) : 'BELUM ADA DOC';
        $uploadBy = trim((string) ($row['batch_doc_uploaded_by_name'] ?? ''));
        $picApproval = trim((string) ($clusterReviewPicMap[(int) ($row['id_myrep_cluster'] ?? 0)] ?? ''));
        $nominalRelease = $row['nominal_release_finance'] ?? null;
        $hasReleaseNominal = $nominalRelease !== null && $nominalRelease !== '';
        $useReleaseNominal = in_array($batchStageCode, ['RELEASED', 'DONE BATCH APPROVAL', 'COMPLETED'], true) && $hasReleaseNominal;
        $displayNominalDonasi = $useReleaseNominal ? (float) $nominalRelease : (float) ($row['nominal_pengajuan_area'] ?? 0);
        $hpDonasi = (float) ($row['hp_donasi'] ?? 0);
        $displayNominalPerHomepass = $hpDonasi > 0 ? $displayNominalDonasi / $hpDonasi : null;
        ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td>
                <?php if (!empty($row['id_myrep_cluster']) && !$isWaitingInputStage): ?>
                    <a href="<?= base_url('Batch_Approval_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="font-weight-bold">
                        <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?>
                    </a>
                <?php else: ?>
                    <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                <?php endif; ?>
                <?php if (!empty($row['cluster_code'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars((string) $row['cluster_code']) ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
            <td class="text-right"><?= number_format($hpDonasi, 0, ',', '.') ?></td>
            <td class="text-right"><?= number_format($displayNominalDonasi, 0, ',', '.') ?></td>
            <td class="text-right"><?= $displayNominalPerHomepass !== null ? number_format($displayNominalPerHomepass, 0, ',', '.') : '-' ?></td>
            <td>
                <div class="batch-sla-aging-cell">
                    <span class="badge badge-<?= batchSlaBadgeClass($slaInfo) ?>">SLA 17 Hari</span>
                    <span class="badge badge-<?= batchAgingBadgeClass($slaInfo['aging_days']) ?>">
                        <?php if ($slaInfo['aging_days'] === null): ?>
                            Aging -
                        <?php else: ?>
                            Aging <?= (int) $slaInfo['aging_days'] ?> Hari
                        <?php endif; ?>
                    </span>
                </div>
            </td>
            <td><span class="badge badge-<?= batchBadgeClass($batchStageLabel) ?>"><?= htmlspecialchars($batchStageLabel) ?></span></td>
            <td>
                <div class="batch-doc-status-stack">
                    <div class="batch-doc-status-stack__item">
                        <span class="batch-doc-name">RAR :</span>
                        <span class="badge badge-<?= batchBadgeClass($batchDocLabel) ?> batch-doc-status-badge">
                            <?= htmlspecialchars($batchDocLabel) ?>
                        </span>
                    </div>
                </div>
            </td>
            <td>
                <div>Upload by : <?= htmlspecialchars($uploadBy !== '' ? $uploadBy : '-') ?></div>
                <div>PIC approval : <?= htmlspecialchars($picApproval !== '' ? $picApproval : '-') ?></div>
            </td>
            <td><span class="badge badge-<?= batchBadgeClass($row['status_current'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'DRAFT')) ?></span></td>
            <td>
                <?php if ($hasBatch): ?>
                    <?php if ($canEdit): ?>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary js-edit-batch"
                            data-toggle="modal"
                            data-target="#modal-batch-edit"
                            data-id_myrep_cluster="<?= (int) $row['id_myrep_cluster'] ?>"
                            data-id_batch_approval="<?= (int) ($row['id_batch_approval'] ?? 0) ?>"
                            data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                            data-regional_name="<?= htmlspecialchars((string) ($row['regional_name'] ?? ''), ENT_QUOTES) ?>"
                            data-province_name="<?= htmlspecialchars((string) ($row['province_name'] ?? ''), ENT_QUOTES) ?>"
                            data-city_name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>"
                            data-district_name="<?= htmlspecialchars((string) ($row['district_name'] ?? ''), ENT_QUOTES) ?>"
                            data-village_name="<?= htmlspecialchars((string) ($row['village_name'] ?? ''), ENT_QUOTES) ?>"
                            data-submission_date="<?= htmlspecialchars((string) ($row['submission_date'] ?? ''), ENT_QUOTES) ?>"
                            data-homepass_valsal="<?= (int) ($row['homepass_valsal'] ?? 0) ?>"
                            data-hp_donasi="<?= (int) ($row['hp_donasi'] ?? 0) ?>"
                            data-nominal_pengajuan_area="<?= htmlspecialchars((string) ($row['nominal_pengajuan_area'] ?? ''), ENT_QUOTES) ?>"
                            data-nominal_nego_emr="<?= htmlspecialchars((string) ($row['nominal_nego_emr'] ?? ''), ENT_QUOTES) ?>"
                            data-nominal_release_finance="<?= htmlspecialchars((string) ($row['nominal_release_finance'] ?? ''), ENT_QUOTES) ?>"
                            data-bank_name="<?= htmlspecialchars((string) ($row['bank_name'] ?? ''), ENT_QUOTES) ?>"
                            data-bank_account_number="<?= htmlspecialchars((string) ($row['bank_account_number'] ?? ''), ENT_QUOTES) ?>"
                            data-recipient_name="<?= htmlspecialchars((string) ($row['recipient_name'] ?? ''), ENT_QUOTES) ?>"
                            data-recipient_phone="<?= htmlspecialchars((string) ($row['recipient_phone'] ?? ''), ENT_QUOTES) ?>"
                            data-recipient_position="<?= htmlspecialchars((string) ($row['recipient_position'] ?? ''), ENT_QUOTES) ?>"
                            data-recipient_period="<?= htmlspecialchars((string) ($row['recipient_period'] ?? ''), ENT_QUOTES) ?>"
                            data-free_wifi_qty="<?= htmlspecialchars((string) ($row['free_wifi_qty'] ?? ''), ENT_QUOTES) ?>"
                            data-free_wifi_period_month="<?= htmlspecialchars((string) ($row['free_wifi_period_month'] ?? ''), ENT_QUOTES) ?>"
                            data-astri_batch_number="<?= htmlspecialchars((string) ($row['astri_batch_number'] ?? ''), ENT_QUOTES) ?>"
                            data-staging_status="<?= htmlspecialchars((string) ($row['staging_status'] ?? 'DRAFT'), ENT_QUOTES) ?>"
                            data-remark_batch_approval="<?= htmlspecialchars((string) ($row['remark_batch_approval'] ?? ''), ENT_QUOTES) ?>"
                            data-pics='<?= htmlspecialchars(json_encode($batchModel->getBatchPics((int) ($row["id_batch_approval"] ?? 0))), ENT_QUOTES) ?>'>
                            Edit
                        </button>
                    <?php endif; ?>
                    <a href="<?= base_url('Batch_Approval_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-secondary mt-1">
                        Detail
                    </a>
                <?php elseif ($canStartBatchInput): ?>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary js-start-batch"
                        data-toggle="modal"
                        data-target="#modal-batch-create"
                        data-cluster_id="<?= (int) $row['id_myrep_cluster'] ?>"
                        data-city_name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>">
                        Input Batch
                    </button>
                <?php else: ?>
                    <span class="text-muted small">Menunggu proses tahap berikutnya</span>
                <?php endif; ?>

                <?php if ($hasBatch && $canHapus): ?>
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta Batch Approval dan seluruh flow MyRep terkait?');">
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
                    <h1 class="m-0 text-dark">Batch Approval MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">
                    Tabel flow baru MyRep belum tersedia. Jalankan query database `tb_myrep_*` terlebih dahulu sebelum memakai modul Batch Approval.
                </div>
            <?php endif; ?>

            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">
                    Tabel dokumen flow Batch Approval belum tersedia. Form Batch Approval tetap bisa dipakai, tetapi upload `RAR` belum aktif.
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm batch-filter-card">
                        <div class="card-header batch-section-header">
                            <div>
                                <h3 class="card-title mb-1">Filter Data Batch Approval</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('Batch_Approval_MyRep') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="batch-field-label">Kota</label>
                                            <select name="city" class="form-control batch-input">
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
                                            <label class="batch-field-label">Status</label>
                                            <select name="status" class="form-control batch-input">
                                                <option value="">Semua Status</option>
                                                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                                    <option value="<?= $statusValue ?>" <?= $selectedStatus === $statusValue ? 'selected' : '' ?>>
                                                        <?= $statusLabel ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group mb-0 w-100 d-flex justify-content-between batch-filter-actions">
                                            <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="btn budget-btn budget-btn--ghost">Reset</a>
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
                    <div class="small-box bg-info shadow-sm batch-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryNyBatch, 0, ',', '.') ?></h3>
                            <p>NY BATCH</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary shadow-sm batch-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryOnProses, 0, ',', '.') ?></h3>
                            <p>On Proses</p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm batch-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryDone, 0, ',', '.') ?></h3>
                            <p>Done Batch</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger shadow-sm batch-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryRejected, 0, ',', '.') ?></h3>
                            <p>Rejected</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($orderedDonationStageSummary)): ?>
                <div class="card card-outline card-info shadow-sm batch-stage-summary-card">
                    <div class="card-header batch-section-header">
                        <div>
                            <h3 class="card-title mb-1">Summary Staging Donasi</h3>
                        </div>
                        <div class="batch-stage-summary-card__actions">
                            <a href="<?= base_url('Batch_Approval_MyRep/downloadStageSummaryReport' . (!empty($selectedCity) || !empty($selectedStatus) ? '?' . http_build_query(array_filter(['city' => $selectedCity, 'status' => $selectedStatus])) : '')) ?>" class="btn btn-sm btn-outline-secondary mr-1">
                                <i class="fas fa-table mr-1"></i> Summary CSV
                            </a>
                            <a href="<?= base_url('Batch_Approval_MyRep/downloadReport' . (!empty($selectedCity) || !empty($selectedStatus) ? '?' . http_build_query(array_filter(['city' => $selectedCity, 'status' => $selectedStatus])) : '')) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i> Report Filter
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="batch-stage-summary-grid">
                            <?php foreach ($orderedDonationStageSummary as $stageCode => $stageData): ?>
                                <?php
                                $stageFilterQuery = array_filter([
                                    'city' => $selectedCity,
                                    'status' => $stageCode,
                                ]);
                                $stageUrl = base_url('Batch_Approval_MyRep' . (!empty($stageFilterQuery) ? '?' . http_build_query($stageFilterQuery) : ''));
                                ?>
                                <a href="<?= $stageUrl ?>" class="batch-stage-summary-item batch-stage-summary-item--<?= batchBadgeClass($stageCode) ?>">
                                    <span class="batch-stage-summary-item__label"><?= htmlspecialchars((string) ($stageData['label'] ?? $stageCode)) ?></span>
                                    <span class="batch-stage-summary-item__count"><?= number_format((int) ($stageData['count'] ?? 0), 0, ',', '.') ?></span>
                                    <span class="batch-stage-summary-item__meta">
                                        HP <?= number_format((float) ($stageData['hp'] ?? 0), 0, ',', '.') ?>
                                    </span>
                                    <span class="batch-stage-summary-item__money">
                                        Pengajuan <?= batchMoneyCompact($stageData['nominal_pengajuan'] ?? 0) ?>
                                    </span>
                                    <span class="batch-stage-summary-item__money">
                                        Release <?= batchMoneyCompact($stageData['nominal_release'] ?? 0) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="batch-toolbar">
                        <?php if ($isReady && $canTambah): ?>
                            <button type="button" class="btn budget-btn budget-btn--primary" data-toggle="modal" data-target="#modal-batch-create">
                                <i class="fas fa-plus mr-1"></i> Input Batch Approval
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--ghost ml-2" data-toggle="modal" data-target="#modal-batch-import">
                                <i class="fas fa-file-import mr-1"></i> Import Batch Approval
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--success ml-2" data-toggle="modal" data-target="#modal-batch-download-report">
                                <i class="fas fa-download mr-1"></i> Download Report Batch
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm batch-table-card">
                        <div class="card-header batch-section-header">
                            <div>
                                <h3 class="card-title mb-1">Monitoring Batch Approval</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs batch-monitor-tabs" id="batch-monitor-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="batch-all-tab" data-toggle="tab" href="#batch-all-pane" role="tab" aria-controls="batch-all-pane" aria-selected="true">
                                        All Batch Approval
                                        <span class="batch-monitor-tabs__count"><?= number_format(count($clusterRows), 0, ',', '.') ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="batch-ny-drm-tab" data-toggle="tab" href="#batch-ny-drm-pane" role="tab" aria-controls="batch-ny-drm-pane" aria-selected="false">
                                        Status NY DRM
                                        <span class="batch-monitor-tabs__count"><?= number_format(count($nyDrmRows), 0, ',', '.') ?></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content batch-monitor-tabs__content" id="batch-monitor-tab-content">
                                <div class="tab-pane fade show active" id="batch-all-pane" role="tabpanel" aria-labelledby="batch-all-tab">
                                    <div class="table-responsive">
                                        <table id="table_batch_all" class="table table-bordered table-hover batch-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Regional</th>
                                                    <th>Kota</th>
                                                    <th>HP Donasi</th>
                                                    <th>Nominal Donasi</th>
                                                    <th>Nominal / Homepass</th>
                                                    <th>SLA &amp; Aging</th>
                                                    <th>Staging</th>
                                                    <th>Dokumen RAR</th>
                                                    <th>Review Dokumen</th>
                                                    <th>Status Flow</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderBatchTableRows($clusterRows, $docReady, $this->MBatch_Approval_MyRep); ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">TOTAL</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th colspan="6"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="batch-ny-drm-pane" role="tabpanel" aria-labelledby="batch-ny-drm-tab">
                                    <div class="table-responsive">
                                        <table id="table_batch_ny_drm" class="table table-bordered table-hover batch-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Regional</th>
                                                    <th>Kota</th>
                                                    <th>HP Donasi</th>
                                                    <th>Nominal Donasi</th>
                                                    <th>Nominal / Homepass</th>
                                                    <th>SLA &amp; Aging</th>
                                                    <th>Staging</th>
                                                    <th>Dokumen RAR</th>
                                                    <th>Review Dokumen</th>
                                                    <th>Status Flow</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderBatchTableRows($nyDrmRows, $docReady, $this->MBatch_Approval_MyRep); ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-right">TOTAL</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th class="text-right">0</th>
                                                    <th colspan="6"></th>
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
    <div class="modal fade" id="modal-batch-import" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <form id="batch-import-preview-form" enctype="multipart/form-data">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <div class="budget-modal__eyebrow">MyRep Batch Approval</div>
                            <h5 class="modal-title mb-1">Import Batch Approval (Excel/CSV)</h5>
                            <p class="budget-modal__subtitle mb-0">Sistem auto-create cluster (jika belum ada), auto BAK DONE, auto VALSAL DONE, lalu input Batch Approval.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-form-section">
                            <a href="<?= base_url('Batch_Approval_MyRep/downloadBatchImportTemplate') ?>" class="btn budget-btn budget-btn--success">
                                <i class="fas fa-download mr-1"></i> Download Format CSV (Lengkap)
                            </a>
                            <p class="text-muted mt-2 mb-0">Template berisi semua kebutuhan import Batch termasuk data cluster, VALSAL, finansial, penerima, bank, dan PIC.</p>
                        </div>
                        <div class="batch-form-section">
                            <div class="batch-dropzone" id="batch-import-dropzone">
                                <input type="file" id="batch-import-file-input" name="file_excel" accept=".xls,.xlsx,.csv">
                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="batch-dropzone-title">Drag & drop file import di sini</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                <div class="batch-dropzone-file" id="batch-import-file-name">Belum ada file dipilih</div>
                            </div>
                        </div>
                        <div class="batch-form-section mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="batch-form-section__title mb-0">Preview Import</div>
                                <small id="batch-import-summary" class="text-muted">Belum ada file dipreview</small>
                            </div>
                            <div class="table-responsive" style="max-height:320px;">
                                <table class="table table-bordered table-sm mb-0" id="table_batch_import_preview">
                                    <thead>
                                        <tr>
                                            <th>Row</th>
                                            <th>Cluster</th>
                                            <th>Kota</th>
                                            <th>HP Donasi</th>
                                            <th>Nominal Area</th>
                                            <th>Recipient</th>
                                            <th>Bank</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="9" class="text-center text-muted">Belum ada data preview</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn budget-btn budget-btn--primary" id="batch-save-import-btn" disabled>Simpan Hasil Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-batch-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Batch_Approval_MyRep/saveBatchApproval') ?>" enctype="multipart/form-data" id="create-batch-approval-form">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <div class="budget-modal__eyebrow">MyRep Batch Approval</div>
                            <h5 class="modal-title mb-1">Input Batch Approval</h5>
                            <p class="budget-modal__subtitle mb-0">Pilih cluster eligible, lengkapi nominal, PIC, dan dokumen RAR dalam satu workflow.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Pilih Cluster</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <select class="form-control js-batch-city-selector">
                                            <option value="">Pilih kota</option>
                                            <?php foreach ($createCityOptions as $cityValue => $cityLabel): ?>
                                                <option value="<?= htmlspecialchars($cityValue, ENT_QUOTES) ?>"><?= htmlspecialchars($cityLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label>Nama Cluster</label>
                                        <select name="cluster_id" class="form-control js-batch-cluster-selector js-batch-cluster-select" required>
                                            <option value=""><?= empty($eligibleClusterOptions) ? 'BELUM ADA CLUSTER YANG DONE VALSAL' : 'Pilih cluster yang sudah VALSAL' ?></option>
                                            <?php foreach ($eligibleClusterOptions as $clusterOption): ?>
                                                <option
                                                    value="<?= (int) $clusterOption['id_myrep_cluster'] ?>"
                                                    data-city-filter="<?= htmlspecialchars(strtoupper((string) ($clusterOption['city_name'] ?? '')), ENT_QUOTES) ?>"
                                                    data-cluster-name="<?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-regional-name="<?= htmlspecialchars((string) ($clusterOption['regional_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-province-name="<?= htmlspecialchars((string) ($clusterOption['province_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-city-name="<?= htmlspecialchars((string) ($clusterOption['city_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-district-name="<?= htmlspecialchars((string) ($clusterOption['district_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-village-name="<?= htmlspecialchars((string) ($clusterOption['village_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-homepass-valsal="<?= (int) ($clusterOption['homepass_valsal'] ?? 0) ?>"
                                                    data-valsal-date="<?= htmlspecialchars((string) ($clusterOption['valsal_date'] ?? ''), ENT_QUOTES) ?>">
                                                    <?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? '-')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-cluster-regional" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control js-cluster-province" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kab / Kota</label><input type="text" class="form-control js-cluster-city" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kecamatan</label><input type="text" class="form-control js-cluster-district" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Desa / Kelurahan</label><input type="text" class="form-control js-cluster-village" readonly></div></div>
                                <div class="col-md-8"><div class="form-group mb-md-0"><label>Nama Cluster</label><input type="text" class="form-control js-cluster-name" readonly></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" class="form-control js-valsal-date" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Data Pengajuan</div>
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>HP VALSAL</label><input type="text" class="form-control js-homepass-valsal js-number-format" data-decimals="0" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="text" name="hp_donasi" id="create_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan Astri</label><input type="date" name="submission_date" id="create_submission_date" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>No Batch Astri</label><input type="text" name="astri_batch_number" class="form-control" placeholder="Batch 2026-XX" required></div></div>
                                <div class="col-md-6"><div class="form-group mb-md-0"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="create_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" required><input type="hidden" name="nominal_nego_emr" id="create_nominal_nego_emr"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Tanggal Batch Approval</label><input type="date" name="astri_batch_approved_at" id="create_astri_batch_approved_at" class="form-control" value="<?= date('Y-m-d') ?>" required></div></div>
                                <div class="col-md-3"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="create_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-freewifi-section">
                            <div class="batch-form-section__title">Free Wifi</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0" placeholder="12"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" class="form-control" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Nama Penerima Dana</label><input type="text" name="recipient_name" id="create_recipient_name" class="form-control js-recipient-source" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="create_recipient_phone" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Jabatan Penerima</label><input type="text" name="recipient_position" id="create_recipient_position" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="create_recipient_period" class="form-control js-recipient-source" placeholder="2023 - 2026"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-finance-fields" data-stage-scope="create" style="display:none;">
                            <div class="batch-form-section__title">Release Finance</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__head">
                                <div>
                                    <div class="batch-form-section__title mb-1">PIC Approval</div>
                                    <p class="batch-form-section__subtitle mb-0">PIC 1 mengikuti data penerima dana. Tambahkan baris jika ada PIC lanjutan.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm js-add-pic-row" data-target="#create_pic_rows" data-prefix="create">Tambah PIC</button>
                            </div>
                            <div class="batch-pic-list" id="create_pic_rows"></div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Upload RAR</div>
                            <div class="alert alert-light border mb-3" style="border-radius: 14px; border-color: #dbeafe !important; background: linear-gradient(135deg, #f8fbff, #eef6ff);">
                                <div class="font-weight-bold text-primary mb-2">Isi dokumen RAR yang harus diupload</div>
                                <div class="small text-muted mb-1">1. Foto Lingkungan : 2 foto</div>
                                <div class="small text-muted mb-1">2. Foto Jalan : 2 foto</div>
                                <div class="small text-muted mb-0">3. Foto Rumah : 2 foto</div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="batch-dropzone js-dropzone">
                                    <input type="file" name="batch_rar_file" id="create_batch_rar_file" class="js-dropzone-input">
                                    <div class="batch-dropzone-content">
                                        <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="batch-dropzone-title">Drag & drop file RAR di sini</div>
                                        <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                        <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="create_rar_not_required" name="is_document_not_required" value="1">
                                    <label class="custom-control-label" for="create_rar_not_required">Tidak membutuhkan dokument</label>
                                </div>
                                <small class="text-muted d-block mt-2">Jika dicentang, batch tetap bisa disubmit tanpa melampirkan file RAR.</small>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Remark</div>
                            <div class="form-group mb-0"><textarea name="remark_batch_approval" rows="3" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Simpan Batch Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-batch-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateBatchApproval') ?>">
                    <input type="hidden" name="cluster_id" id="edit_id_myrep_cluster">
                    <input type="hidden" name="id_batch_approval" id="edit_id_batch_approval">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <div class="budget-modal__eyebrow">MyRep Batch Approval</div>
                            <h5 class="modal-title mb-1">Edit Batch Approval</h5>
                            <p class="budget-modal__subtitle mb-0">Sesuaikan data pengajuan, staging, PIC approval, dan nominal release dari cluster yang sudah berjalan.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" id="edit_regional_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" id="edit_province_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kab / Kota</label><input type="text" id="edit_city_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kecamatan</label><input type="text" id="edit_district_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Desa / Kelurahan</label><input type="text" id="edit_village_name" class="form-control" readonly></div></div>
                                <div class="col-md-8"><div class="form-group mb-md-0"><label>Cluster</label><input type="text" id="edit_cluster_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" id="edit_valsal_date" class="form-control" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Data Pengajuan</div>
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>HP VALSAL</label><input type="text" id="edit_homepass_valsal" class="form-control js-number-format" data-decimals="0" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="text" name="hp_donasi" id="edit_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan Astri</label><input type="date" name="submission_date" id="edit_submission_date" class="form-control"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Staging</label><select name="staging_status" id="edit_staging_status" class="form-control"><?php foreach ($statusOptions as $statusValue => $statusLabel): ?><option value="<?= $statusValue ?>"><?= $statusLabel ?></option><?php endforeach; ?></select></div></div>
                                <div class="col-md-6"><div class="form-group"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="edit_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="edit_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-freewifi-section">
                            <div class="batch-form-section__title">Free Wifi</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" id="edit_free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" id="edit_free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" id="edit_bank_name" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" id="edit_bank_account_number" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Penerima Dana</label><input type="text" name="recipient_name" id="edit_recipient_name" class="form-control js-recipient-source" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="edit_recipient_phone" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Jabatan</label><input type="text" name="recipient_position" id="edit_recipient_position" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="edit_recipient_period" class="form-control js-recipient-source"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-emr-fields" data-stage-scope="edit" style="display:none;">
                            <div class="batch-form-section__title">Approval EMR</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Approval EMR</label><input type="text" name="nominal_nego_emr" id="edit_nominal_nego_emr" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-finance-fields" data-stage-scope="edit" style="display:none;">
                            <div class="batch-form-section__title">Release Finance</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" id="edit_nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__head">
                                <div>
                                    <div class="batch-form-section__title mb-1">PIC Approval</div>
                                    <p class="batch-form-section__subtitle mb-0">PIC 1 mengikuti data penerima dana. Tambahkan PIC lain bila diperlukan.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm js-add-pic-row" data-target="#edit_pic_rows" data-prefix="edit">Tambah PIC</button>
                            </div>
                            <div class="batch-pic-list" id="edit_pic_rows"></div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Remark</div>
                            <div class="form-group mb-0"><textarea name="remark_batch_approval" id="edit_remark_batch_approval" rows="3" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Update Batch Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($docReady): ?>
        <div class="modal fade doc-modal" id="modal-batch-upload-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="batch-upload-document-form">
                        <input type="hidden" name="cluster_id" id="upload_cluster_id">
                        <div class="modal-header budget-modal__header budget-modal__header--success">
                            <div>
                                <h4 class="modal-title mb-1">Upload Dokumen RAR</h4>
                                <p class="mb-0" style="opacity:.9;" id="upload_doc_cluster_caption"></p>
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
                                <label class="font-weight-bold d-block">File RAR</label>
                                <div class="alert alert-light border mb-3" style="border-radius: 14px; border-color: #dbeafe !important; background: linear-gradient(135deg, #f8fbff, #eef6ff);">
                                    <div class="font-weight-bold text-primary mb-2">Isi dokumen RAR yang harus diupload</div>
                                    <div class="small text-muted mb-1">1. Foto Lingkungan: 2 foto</div>
                                    <div class="small text-muted mb-1">2. Foto Jalan: 2 foto</div>
                                    <div class="small text-muted mb-0">3. Foto Rumah: 2 foto</div>
                                </div>
                                <div class="upload-dropzone" id="batch-upload-dropzone">
                                    <input type="file" name="file" id="batch-upload-file-input">
                                    <div class="upload-dropzone-content">
                                        <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                        <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                        <div class="upload-dropzone-file" id="batch-upload-file-name">Belum ada file dipilih</div>
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
                            <div class="upload-progress-panel" id="batch-upload-progress-panel">
                                <div class="upload-progress-meta">
                                    <span>Upload Progress</span>
                                    <span id="batch-upload-progress-percent">0%</span>
                                </div>
                                <div class="upload-progress-bar-wrap">
                                    <div class="upload-progress-bar" id="batch-upload-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer budget-modal__footer">
                            <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn budget-btn budget-btn--success" id="batch-upload-document-submit">Upload Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($canApprove && $canApprovalAction): ?>
            <div class="modal fade doc-modal" id="modal-batch-approve-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="approve_id_doc_file">
                            <div class="modal-header budget-modal__header budget-modal__header--success">
                                <div>
                                    <h4 class="modal-title mb-1">Approve Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="approve_doc_name">RAR</p>
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

            <div class="modal fade doc-modal" id="modal-batch-reject-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/rejectDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="reject_id_doc_file">
                            <div class="modal-header budget-modal__header budget-modal__header--danger">
                                <div>
                                    <h4 class="modal-title mb-1">Reject Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="reject_doc_name">RAR</p>
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

        <div class="modal fade doc-modal" id="modal-batch-history-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <h4 class="modal-title mb-1">History Dokumen</h4>
                            <p class="mb-0" style="opacity:.9;" id="history_doc_name">RAR</p>
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

        <div class="modal fade doc-modal" id="modal-batch-transfer" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadTransferProof') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="cluster_id" id="transfer_cluster_id">
                        <input type="hidden" name="id_batch_approval" id="transfer_batch_id">
                        <div class="modal-header budget-modal__header budget-modal__header--dark">
                            <div>
                                <h4 class="modal-title mb-1">Upload Bukti Transfer</h4>
                                <p class="mb-0" style="opacity:.9;" id="transfer_cluster_name_caption"></p>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="doc-modal-panel">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">File Bukti Transfer</label>
                                    <input type="file" name="transfer_proof" class="form-control-file" required>
                                    <small class="text-muted d-block mt-2">Format: pdf, jpg, jpeg, png.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer budget-modal__footer">
                            <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn budget-btn budget-btn--dark">Upload Bukti Transfer</button>
                        </div>
                    </form>
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
<div class="modal fade" id="modal-batch-download-report" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalBatchDownloadReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content budget-modal batch-modal-shell">
            <div class="modal-header budget-modal__header">
                <div>
                    <span class="budget-modal__eyebrow">Batch Approval MyRep</span>
                    <h5 class="modal-title mb-1" id="modalBatchDownloadReportLabel">Download Report Batch Approval</h5>
                    <p class="mb-0 budget-modal__subtitle">Ekspor report batch approval dengan filter regional, kota, dan tanggal submission.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="budget-form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Regional</label>
                                <select id="batch_download_regional" class="form-control" multiple>
                                    <?php foreach ($regionalOptions as $regionalOption): ?>
                                        <option value="<?= htmlspecialchars((string) $regionalOption, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $regionalOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kota</label>
                                <select id="batch_download_city" class="form-control" multiple>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= htmlspecialchars((string) $cityOption, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $cityOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Submission Start</label>
                                <input type="date" id="batch_download_date_start" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label>Tanggal Submission End</label>
                                <input type="date" id="batch_download_date_end" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer budget-modal__footer">
                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn budget-btn budget-btn--success" id="batch-download-report-submit-btn">
                    <i class="fas fa-download mr-1"></i> Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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

    .batch-filter-card,
    .batch-table-card {
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .batch-filter-card .card-header,
    .batch-table-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
        padding: 1.15rem 1.35rem;
    }

    .batch-filter-card .card-body,
    .batch-table-card .card-body {
        padding: 1.35rem;
    }

    .batch-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .batch-section-subtitle {
        color: #64748b;
        font-size: .92rem;
        margin-top: .2rem;
    }

    .batch-field-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
    }

    .batch-input,
    #modal-batch-create .form-control,
    #modal-batch-edit .form-control,
    .doc-modal .form-control,
    .doc-modal .form-control-file,
    .doc-modal select.form-control {
        min-height: 44px;
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .batch-input:focus,
    #modal-batch-create .form-control:focus,
    #modal-batch-edit .form-control:focus,
    .doc-modal .form-control:focus,
    .doc-modal select.form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 .2rem rgba(96, 165, 250, 0.16);
    }

    #modal-batch-create .form-control[readonly],
    #modal-batch-edit .form-control[readonly],
    #modal-batch-create .form-control:disabled,
    #modal-batch-edit .form-control:disabled,
    .doc-modal .form-control[readonly],
    .doc-modal .form-control:disabled {
        background: #eef4fb;
        border-color: #d7e3f1;
        color: #64748b;
        cursor: not-allowed;
    }

    .batch-summary-box {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
    }

    .batch-stage-summary-card {
        border-radius: 12px;
        overflow: hidden;
    }

    .batch-stage-summary-card__actions {
        margin-left: auto;
    }

    .batch-stage-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: .75rem;
    }

    .batch-stage-summary-item {
        display: grid;
        min-height: 128px;
        padding: .85rem .9rem;
        border: 1px solid #dbe7f3;
        border-left: 5px solid #64748b;
        border-radius: 8px;
        background: #fff;
        color: #0f172a;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    }

    .batch-stage-summary-item:hover {
        color: #0f172a;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.10);
    }

    .batch-stage-summary-item--success {
        border-left-color: #16a34a;
    }

    .batch-stage-summary-item--info {
        border-left-color: #0284c7;
    }

    .batch-stage-summary-item--warning {
        border-left-color: #f59e0b;
    }

    .batch-stage-summary-item--danger {
        border-left-color: #dc2626;
    }

    .batch-stage-summary-item__label {
        min-height: 34px;
        color: #475569;
        font-size: .76rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .batch-stage-summary-item__count {
        display: block;
        margin-top: .15rem;
        font-size: 1.55rem;
        font-weight: 900;
        line-height: 1;
    }

    .batch-stage-summary-item__meta,
    .batch-stage-summary-item__money {
        display: block;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .batch-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .batch-monitor-tabs {
        border-bottom: 0;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .batch-monitor-tabs .nav-link {
        border: 1px solid #d9e6f2;
        border-radius: 999px;
        color: #45627b;
        font-weight: 700;
        padding: .65rem 1rem;
        background: #f7fbff;
    }

    .batch-monitor-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #1e88cf, #2ca58d);
        border-color: transparent;
        box-shadow: 0 12px 28px rgba(30, 136, 207, 0.24);
    }

    .batch-monitor-tabs__count {
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

    .batch-monitor-tabs .nav-link:not(.active) .batch-monitor-tabs__count {
        background: #e2edf7;
        color: #2d6287;
    }

    .batch-monitor-tabs__content {
        padding-top: .25rem;
    }

    .budget-btn {
        border: 0;
        border-radius: 999px;
        padding: .72rem 1.2rem;
        font-weight: 700;
        letter-spacing: .01em;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
    }

    .budget-btn:hover {
        transform: translateY(-1px);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    .budget-btn--success {
        background: linear-gradient(135deg, #198754, #34c38f);
        color: #fff;
    }

    .budget-btn--danger {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
        color: #fff;
    }

    .budget-btn--dark {
        background: linear-gradient(135deg, #111827, #374151);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #334155;
        border: 1px solid #d7e0ea;
        box-shadow: none;
    }

    .batch-monitor-table thead th,
    .batch-table-card .table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
        white-space: nowrap;
        border-bottom: 1px solid #dbeafe;
    }

    .batch-monitor-table tbody tr:hover {
        background: #f8fbff;
    }

    .batch-doc-status-stack {
        min-width: 135px;
    }

    .batch-doc-status-stack__item {
        display: grid;
        grid-template-columns: 48px max-content;
        align-items: center;
        column-gap: .4rem;
        margin-bottom: .25rem;
        white-space: nowrap;
    }

    .batch-doc-name {
        color: #111827;
        font-size: .83rem;
        font-weight: 700;
    }

    .batch-doc-status-badge {
        justify-self: start;
        min-width: 74px;
        text-align: center;
    }

    .batch-sla-aging-cell {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .25rem;
        min-width: 90px;
    }

    .budget-modal__header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
        padding: 1.25rem 1.35rem;
        border-bottom: 0;
    }

    .budget-modal__header--success {
        background: linear-gradient(135deg, #198754, #34c38f);
    }

    .budget-modal__header--danger {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
    }

    .budget-modal__header--dark {
        background: linear-gradient(135deg, #111827, #374151);
    }

    .budget-modal__eyebrow {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .14em;
        font-weight: 800;
        opacity: .82;
        margin-bottom: .35rem;
    }

    .budget-modal__subtitle {
        color: rgba(255, 255, 255, 0.86);
        font-size: .92rem;
        line-height: 1.5;
    }

    .budget-modal__footer {
        border-top: 1px solid #e7ecf3;
        background: #fff;
        padding: 1rem 1.25rem 1.15rem;
        gap: .75rem;
    }

    #modal-batch-create .modal-content,
    #modal-batch-edit .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    #modal-batch-create .modal-body,
    #modal-batch-edit .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .modal-xxl {
        max-width: 78vw;
    }

    .batch-form-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .batch-form-section--last {
        margin-bottom: 0;
    }

    .batch-form-section__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: .9rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-form-section__title {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .9rem;
    }

    .batch-form-section__subtitle {
        color: #6b7280;
        font-size: .9rem;
    }

    .batch-pic-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .batch-pic-card {
        border: 1px dashed #cdd8e5;
        border-radius: 12px;
        padding: 1rem;
        background: #fbfdff;
    }

    .batch-pic-card--primary {
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border-style: solid;
    }

    .batch-pic-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-pic-card__title {
        font-weight: 700;
        color: #1f2937;
    }

    .batch-pic-card__note {
        color: #6b7280;
        font-size: .85rem;
    }

    .batch-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1rem;
        transition: all .2s ease;
        cursor: pointer;
    }

    .batch-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
        transform: scale(1.01);
    }

    .batch-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .batch-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .batch-dropzone-icon {
        font-size: 1.8rem;
        color: #198754;
        margin-bottom: .5rem;
    }

    .batch-dropzone-title {
        font-weight: 700;
        color: #166534;
        margin-bottom: .2rem;
    }

    .batch-dropzone-text {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: .3rem;
    }

    .batch-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .js-add-pic-row {
        position: relative;
        z-index: 2;
        cursor: pointer;
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

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 44px;
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        padding: .35rem .55rem;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #60a5fa;
        box-shadow: 0 0 0 .2rem rgba(96, 165, 250, 0.16);
    }

    .select2-dropdown {
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        overflow: hidden;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
    }

    @media (max-width: 767.98px) {
        .batch-form-section__head,
        .batch-pic-card__head {
            flex-direction: column;
        }

        .batch-toolbar {
            justify-content: stretch;
        }

        .batch-toolbar .budget-btn {
            width: 100%;
        }

        .modal-xxl {
            max-width: calc(100vw - 1rem);
            margin: .5rem auto;
        }
    }
</style>

<script>
    (function () {
        var MAX_PIC_ROWS = 5;
        var batchPreviewImportUrl = '<?= base_url('Batch_Approval_MyRep/previewBatchImport') ?>';
        var batchSaveImportUrl = '<?= base_url('Batch_Approval_MyRep/saveImportedBatch') ?>';
        var batchDownloadReportUrl = '<?= base_url('Batch_Approval_MyRep/downloadReport') ?>';
        var batchCityOptionsByRegional = <?= json_encode($cityOptionsByRegional, JSON_UNESCAPED_UNICODE) ?>;
        var batchRegionalOptionsByCity = <?= json_encode($regionalOptionsByCity, JSON_UNESCAPED_UNICODE) ?>;
        var batchSelectedStatus = '<?= htmlspecialchars((string) $selectedStatus, ENT_QUOTES) ?>';
        var importedBatchRows = [];

        function initBatchCreateSelects() {
            var $modal = $('#modal-batch-create');
            if (!$.fn.select2 || !$modal.length) {
                return;
            }

            $modal.find('.js-batch-city-selector, .js-batch-cluster-select').each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    placeholder: $select.hasClass('js-batch-city-selector') ? 'Pilih kota' : 'Pilih cluster',
                    allowClear: true,
                    dropdownParent: $modal
                });
            });
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
                    label.textContent = e.dataTransfer.files[0].name;
                }
            });

            input.addEventListener('change', function () {
                label.textContent = (input.files && input.files.length > 0)
                    ? input.files[0].name
                    : 'Belum ada file dipilih';
            });
        }

        function resetBatchImportPreview() {
            importedBatchRows = [];
            $('#batch-import-summary').text('Belum ada file dipreview');
            $('#batch-save-import-btn').prop('disabled', true);
            $('#table_batch_import_preview tbody').html('<tr><td colspan="9" class="text-center text-muted">Belum ada data preview</td></tr>');
        }

        function renderBatchImportPreview(rows) {
            if (!rows || !rows.length) {
                $('#table_batch_import_preview tbody').html('<tr><td colspan="9" class="text-center text-muted">Belum ada data preview</td></tr>');
                return;
            }

            var html = rows.map(function (row) {
                var badgeClass = String(row.status || '').toLowerCase() === 'valid' ? 'success' : 'danger';
                return '<tr>' +
                    '<td>' + Number(row.row_number || 0) + '</td>' +
                    '<td>' + (row.cluster_name || '-') + '</td>' +
                    '<td>' + (row.city_name || '-') + '</td>' +
                    '<td class="text-right">' + Number(row.hp_donasi || 0).toLocaleString('id-ID') + '</td>' +
                    '<td class="text-right">' + Number(row.nominal_pengajuan_area || 0).toLocaleString('id-ID') + '</td>' +
                    '<td>' + (row.recipient_name || '-') + '</td>' +
                    '<td>' + (row.bank_name || '-') + '</td>' +
                    '<td><span class="badge badge-' + badgeClass + '">' + (row.status || '-') + '</span></td>' +
                    '<td>' + (row.message || '-') + '</td>' +
                '</tr>';
            }).join('');

            $('#table_batch_import_preview tbody').html(html);
        }

        function bindInlineDropzones() {
            $('.js-dropzone').each(function () {
                var dropzone = this;
                var input = dropzone.querySelector('.js-dropzone-input');
                var label = dropzone.querySelector('.js-dropzone-label');

                if (!input || !label || dropzone.dataset.bound === '1') {
                    return;
                }

                dropzone.dataset.bound = '1';

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
                        label.textContent = e.dataTransfer.files[0].name;
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = (input.files && input.files.length > 0)
                        ? input.files[0].name
                        : 'Belum ada file dipilih';
                });
            });
        }

        function syncClusterMeta(target) {
            var $container = $(target).closest('#modal-batch-create, #modal-batch-edit');
            if (!$container.length) {
                $container = $(target);
            }

            var select = $container.find('.js-batch-cluster-selector').get(0);
            if (!select) {
                return;
            }

            var selectedOption = select.selectedOptions && select.selectedOptions.length
                ? select.selectedOptions[0]
                : select.options[select.selectedIndex];
            var hasValue = !!(selectedOption && selectedOption.value);

            function optionData(name) {
                if (!selectedOption || !hasValue) {
                    return '';
                }

                return selectedOption.getAttribute('data-' + name) || '';
            }

            $container.find('.js-cluster-regional').val(optionData('regional-name'));
            $container.find('.js-cluster-province').val(optionData('province-name'));
            $container.find('.js-cluster-city').val(optionData('city-name'));
            $container.find('.js-cluster-district').val(optionData('district-name'));
            $container.find('.js-cluster-village').val(optionData('village-name'));
            $container.find('.js-cluster-name').val(optionData('cluster-name'));
            $container.find('.js-valsal-date').val(optionData('valsal-date'));
            if ($container.find('.js-homepass-valsal').length) {
                $container.find('.js-homepass-valsal').val(optionData('homepass-valsal'));
                $container.find('.js-homepass-valsal').each(function () {
                    applyNumberFormatting($(this));
                });
            }

            if ($container.attr('id') === 'modal-batch-create') {
                $('#create_hp_donasi').val(optionData('homepass-valsal'));
                applyNumberFormatting($('#create_hp_donasi'));
                updateNominalPerHomepass('create');
            }
        }

        function syncCityFromCluster(target) {
            var $container = $(target).closest('#modal-batch-create, #modal-batch-edit');
            if (!$container.length) {
                $container = $(target);
            }

            var $clusterSelect = $container.find('.js-batch-cluster-selector');
            var $citySelect = $container.find('.js-batch-city-selector');
            if (!$clusterSelect.length || !$citySelect.length) {
                return;
            }

            var selectedOption = $clusterSelect.find('option:selected');
            var cityValue = ((selectedOption.data('city-filter') || '') + '').toUpperCase();
            if (cityValue === '') {
                return;
            }

            $citySelect.val(cityValue).trigger('change.select2');
        }

        function filterBatchClusterOptions($modal) {
            var selectedCity = (($modal.find('.js-batch-city-selector').val() || '') + '').toUpperCase();
            var $clusterSelect = $modal.find('.js-batch-cluster-selector');

            $clusterSelect.find('option').each(function () {
                var $option = $(this);
                var optionValue = $option.attr('value');

                if (!optionValue) {
                    $option.prop('hidden', false).prop('disabled', false);
                    return;
                }

                var optionCity = (($option.data('city-filter') || '') + '').toUpperCase();
                var shouldShow = selectedCity === '' || optionCity === selectedCity;
                $option.prop('hidden', !shouldShow).prop('disabled', !shouldShow);
            });

            if (selectedCity !== '') {
                var currentOption = $clusterSelect.find('option:selected');
                var currentCity = ((currentOption.data('city-filter') || '') + '').toUpperCase();
                if (currentCity !== selectedCity) {
                    $clusterSelect.val('');
                }
            }

            $clusterSelect.trigger('change.select2');
            syncClusterMeta($modal);
        }

        function applyCreateBatchPreset($modal) {
            var presetClusterId = ($modal.attr('data-preset-cluster-id') || '').toString();
            var presetCity = ($modal.attr('data-preset-city') || '').toString().toUpperCase();

            if (presetCity !== '') {
                $modal.find('.js-batch-city-selector').val(presetCity).trigger('change.select2');
            }

            if (presetClusterId !== '') {
                $modal.find('.js-batch-cluster-selector').val(presetClusterId).trigger('change');
            }
        }

        function toggleStageFields(prefix) {
            var stageValue = $('#' + prefix + '_staging_status').val() || 'WAITING HO';
            var showEmr = ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;
            var showFinance = ['WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;

            $('[data-stage-scope="' + prefix + '"].js-emr-fields').toggle(showEmr);
            $('[data-stage-scope="' + prefix + '"].js-finance-fields').toggle(showFinance);
        }

        function normalizeFormattedNumber(value) {
            var normalized = String(value || '').replace(/[^\d,.\-]/g, '');
            if (normalized === '') {
                return 0;
            }

            var hasComma = normalized.indexOf(',') !== -1;
            var dotCount = (normalized.match(/\./g) || []).length;

            if (hasComma) {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            } else if (dotCount > 1) {
                normalized = normalized.replace(/\./g, '');
            } else if (dotCount === 1) {
                var parts = normalized.split('.');
                var decimalLength = parts[1] ? parts[1].length : 0;
                if (decimalLength === 3) {
                    normalized = parts[0] + parts[1];
                }
            }

            var number = parseFloat(normalized);
            return isNaN(number) ? 0 : number;
        }

        function formatNumberValue(value, decimals) {
            var number = typeof value === 'number' ? value : normalizeFormattedNumber(value);
            if (!isFinite(number)) {
                number = 0;
            }

            var fixed = Number(number).toFixed(decimals);
            var parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            if (decimals > 0) {
                parts[1] = (parts[1] || '').replace(/0+$/, '');
                return parts[1] ? parts[0] + ',' + parts[1] : parts[0];
            }

            return parts[0];
        }

        function applyNumberFormatting($input) {
            var decimals = parseInt($input.data('decimals'), 10);
            if (isNaN(decimals)) {
                decimals = 0;
            }

            if ($input.val() === '') {
                return;
            }

            $input.val(formatNumberValue($input.val(), decimals));
        }

        function updateNominalPerHomepass(prefix) {
            var hpDonasi = normalizeFormattedNumber($('#' + prefix + '_hp_donasi').val());
            var nominalDonasi = normalizeFormattedNumber($('#' + prefix + '_nominal_pengajuan_area').val());
            var result = hpDonasi > 0 ? (nominalDonasi / hpDonasi) : 0;
            $('#' + prefix + '_nominal_per_homepass').val(result > 0 ? formatNumberValue(result, 2) : '');
            if (prefix === 'create') {
                $('#create_nominal_nego_emr').val(nominalDonasi > 0 ? String(Math.round(nominalDonasi)) : '');
            }
        }

        function fillCreateDefaults() {
            var today = new Date();
            var month = String(today.getMonth() + 1).padStart(2, '0');
            var day = String(today.getDate()).padStart(2, '0');
            $('#create_submission_date').val(today.getFullYear() + '-' + month + '-' + day);
            $('#create_astri_batch_approved_at').val(today.getFullYear() + '-' + month + '-' + day);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildPicRow(prefix, index, pic) {
            var isPrimary = index === 1;
            var rowClass = isPrimary ? 'batch-pic-card batch-pic-card--primary' : 'batch-pic-card';
            var note = isPrimary
                ? '<div class="batch-pic-card__note">Otomatis mengikuti penerima dana di atas.</div>'
                : '';
            var removeButton = isPrimary
                ? ''
                : '<button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>';
            var readOnly = isPrimary ? 'readonly' : '';

            return '' +
                '<div class="' + rowClass + '" data-pic-index="' + index + '">' +
                    '<div class="batch-pic-card__head">' +
                        '<div>' +
                            '<div class="batch-pic-card__title">PIC ' + index + '</div>' +
                            note +
                        '</div>' +
                        removeButton +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name" value="' + escapeHtml(pic.pic_name || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone" value="' + escapeHtml(pic.pic_phone || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position" value="' + escapeHtml(pic.pic_position || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group mb-0"><label>Periode PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period" value="' + escapeHtml(pic.pic_period || '') + '" ' + readOnly + '></div></div>' +
                    '</div>' +
                '</div>';
        }

        function getRecipientValues(prefix) {
            return {
                pic_name: $('#' + prefix + '_recipient_name').val() || '',
                pic_phone: $('#' + prefix + '_recipient_phone').val() || '',
                pic_position: $('#' + prefix + '_recipient_position').val() || '',
                pic_period: $('#' + prefix + '_recipient_period').val() || ''
            };
        }

        function normalizePicRows($target) {
            $target.children('.batch-pic-card').each(function (idx) {
                var index = idx + 1;
                $(this).attr('data-pic-index', index);
                $(this).find('.batch-pic-card__title').text('PIC ' + index);
            });
        }

        function syncPrimaryPic(prefix) {
            var $primary = $('#' + prefix + '_pic_rows').find('.batch-pic-card').first();
            if (!$primary.length) {
                return;
            }

            var values = getRecipientValues(prefix);
            $primary.find('.js-pic-name').val(values.pic_name);
            $primary.find('.js-pic-phone').val(values.pic_phone);
            $primary.find('.js-pic-position').val(values.pic_position);
            $primary.find('.js-pic-period').val(values.pic_period);
        }

        function renderPicRows(prefix, pics) {
            var $target = $('#' + prefix + '_pic_rows');
            var rows = Array.isArray(pics) ? pics.slice(0, MAX_PIC_ROWS) : [];
            var primaryValues = getRecipientValues(prefix);
            var primary = rows.length ? rows[0] : primaryValues;
            var html = buildPicRow(prefix, 1, {
                pic_name: primary.pic_name || primaryValues.pic_name,
                pic_phone: primary.pic_phone || primaryValues.pic_phone,
                pic_position: primary.pic_position || primaryValues.pic_position,
                pic_period: primary.pic_period || primaryValues.pic_period
            });

            for (var i = 1; i < rows.length && i < MAX_PIC_ROWS; i++) {
                html += buildPicRow(prefix, i + 1, rows[i]);
            }

            $target.html(html);
            syncPrimaryPic(prefix);
        }

        function addPicRow(prefix) {
            var $target = $('#' + prefix + '_pic_rows');
            var currentCount = $target.children('.batch-pic-card').length;
            if (currentCount >= MAX_PIC_ROWS) {
                return;
            }

            $target.append(buildPicRow(prefix, currentCount + 1, {}));
            normalizePicRows($target);
        }

        $(function () {
            $(document).on('change', '.js-batch-cluster-selector', function () {
                var $container = $(this).closest('#modal-batch-create, #modal-batch-edit');
                syncCityFromCluster($container);
                filterBatchClusterOptions($container);
                syncClusterMeta(this);
            });

            $(document).on('change', '.js-batch-city-selector', function () {
                filterBatchClusterOptions($(this).closest('.modal-body, .modal-content'));
            });

            $('#modal-batch-create').on('shown.bs.modal', function () {
                initBatchCreateSelects();
                $(this).find('.js-batch-city-selector').val('').trigger('change');
                $(this).find('.js-batch-cluster-selector').val('').trigger('change');
                filterBatchClusterOptions($(this));
                applyCreateBatchPreset($(this));
                syncClusterMeta(this);
                renderPicRows('create', []);
                toggleStageFields('create');
                fillCreateDefaults();
                $(this).find('.js-dropzone-label').text('Belum ada file dipilih');
                $(this).find('.js-number-format').each(function () {
                    applyNumberFormatting($(this));
                });
                updateNominalPerHomepass('create');
            });

            $('#modal-batch-create').on('hidden.bs.modal', function () {
                this.querySelector('form').reset();
                $(this).removeAttr('data-preset-cluster-id').removeAttr('data-preset-city');
                var $citySelect = $(this).find('.js-batch-city-selector');
                var $clusterSelect = $(this).find('.js-batch-cluster-select');
                if ($citySelect.hasClass('select2-hidden-accessible')) {
                    $citySelect.select2('close');
                }
                if ($clusterSelect.hasClass('select2-hidden-accessible')) {
                    $clusterSelect.select2('close');
                }
                syncClusterMeta(this);
                renderPicRows('create', []);
                toggleStageFields('create');
                fillCreateDefaults();
                $(this).find('.js-dropzone-label').text('Belum ada file dipilih');
                updateNominalPerHomepass('create');
            });

            $(document).on('click', '.js-start-batch', function () {
                var $button = $(this);
                $('#modal-batch-create')
                    .attr('data-preset-cluster-id', ($button.data('cluster_id') || '').toString())
                    .attr('data-preset-city', ($button.data('city_name') || '').toString().toUpperCase());
            });

            $(document).on('change', '#create_rar_not_required', function () {
                var isChecked = $(this).is(':checked');
                var fileInput = $('#create_batch_rar_file').get(0);
                var dropzone = $('#modal-batch-create .js-dropzone').get(0);

                if (isChecked && fileInput) {
                    fileInput.value = '';
                }

                if (dropzone) {
                    var label = dropzone.querySelector('.js-dropzone-label');
                    if (label && isChecked) {
                        label.textContent = 'Dokumen ditandai tidak dibutuhkan';
                    } else if (label && fileInput && (!fileInput.files || !fileInput.files.length)) {
                        label.textContent = 'Belum ada file dipilih';
                    }
                }
            });

            $('#create-batch-approval-form').on('submit', function (e) {
                var noDocumentRequired = $('#create_rar_not_required').is(':checked');
                var fileInput = $('#create_batch_rar_file').get(0);
                var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);

                if (!noDocumentRequired && !hasFile) {
                    e.preventDefault();
                    alert('Upload RAR wajib diisi. Centang "Tidak membutuhkan dokument" jika dokumen memang tidak diperlukan.');
                }
            });

            $(document).on('click', '.js-edit-batch', function () {
                var $button = $(this);
                var $modal = $('#modal-batch-edit');

                $modal.find('#edit_id_myrep_cluster').val($button.data('id_myrep_cluster'));
                $modal.find('#edit_id_batch_approval').val($button.data('id_batch_approval'));
                $modal.find('#edit_cluster_name').val($button.data('cluster_name'));
                $modal.find('#edit_regional_name').val($button.data('regional_name'));
                $modal.find('#edit_province_name').val($button.data('province_name'));
                $modal.find('#edit_city_name').val($button.data('city_name'));
                $modal.find('#edit_district_name').val($button.data('district_name'));
                $modal.find('#edit_village_name').val($button.data('village_name'));
                $modal.find('#edit_submission_date').val($button.data('submission_date'));
                $modal.find('#edit_hp_donasi').val($button.data('hp_donasi'));
                $modal.find('#edit_nominal_pengajuan_area').val($button.data('nominal_pengajuan_area'));
                $modal.find('#edit_nominal_nego_emr').val($button.data('nominal_nego_emr'));
                $modal.find('#edit_nominal_release_finance').val($button.data('nominal_release_finance'));
                $modal.find('#edit_bank_name').val($button.data('bank_name'));
                $modal.find('#edit_bank_account_number').val($button.data('bank_account_number'));
                $modal.find('#edit_recipient_name').val($button.data('recipient_name'));
                $modal.find('#edit_recipient_phone').val($button.data('recipient_phone'));
                $modal.find('#edit_recipient_position').val($button.data('recipient_position'));
                $modal.find('#edit_recipient_period').val($button.data('recipient_period'));
                $modal.find('#edit_free_wifi_qty').val($button.data('free_wifi_qty'));
                $modal.find('#edit_free_wifi_period_month').val($button.data('free_wifi_period_month'));
                $modal.find('#edit_astri_batch_number').val($button.data('astri_batch_number'));
                $modal.find('#edit_staging_status').val($button.data('staging_status'));
                $modal.find('#edit_homepass_valsal').val($button.data('homepass_valsal'));
                $modal.find('#edit_valsal_date').val($button.data('valsal_date'));
                $modal.find('#edit_remark_batch_approval').val($button.data('remark_batch_approval'));
                var pics = [];
                try {
                    pics = $button.attr('data-pics') ? JSON.parse($button.attr('data-pics')) : [];
                } catch (e) {
                    pics = [];
                }
                renderPicRows('edit', pics);
                toggleStageFields('edit');
                $modal.find('.js-number-format').each(function () {
                    applyNumberFormatting($(this));
                });
                updateNominalPerHomepass('edit');
            });

            $(document).on('click', '.js-add-pic-row', function (e) {
                e.preventDefault();
                addPicRow($(this).data('prefix'));
            });

            $(document).on('click', '.js-remove-pic-row', function () {
                var $target = $(this).closest('.batch-pic-list');
                $(this).closest('.batch-pic-card').remove();
                normalizePicRows($target);
            });

            $(document).on('input change', '#create_recipient_name, #create_recipient_phone, #create_recipient_position, #create_recipient_period', function () {
                syncPrimaryPic('create');
            });

            $(document).on('input change', '#edit_recipient_name, #edit_recipient_phone, #edit_recipient_position, #edit_recipient_period', function () {
                syncPrimaryPic('edit');
            });

            $(document).on('change', '#edit_staging_status', function () {
                toggleStageFields('edit');
            });

            $(document).on('input', '#create_hp_donasi, #create_nominal_pengajuan_area', function () {
                updateNominalPerHomepass('create');
            });

            $(document).on('input', '#edit_hp_donasi, #edit_nominal_pengajuan_area', function () {
                updateNominalPerHomepass('edit');
            });

            $(document).on('focus', '.js-number-format', function () {
                var value = $(this).val();
                if (value !== '') {
                    $(this).val(String(value).replace(/\./g, '').replace(',', '.'));
                }
            });

            $(document).on('blur', '.js-number-format', function () {
                applyNumberFormatting($(this));
                updateNominalPerHomepass('create');
                updateNominalPerHomepass('edit');
            });

            $(document).on('click', '.js-upload-doc', function () {
                var $button = $(this);
                $('#upload_cluster_id').val($button.data('cluster_id'));
                $('#upload_cluster_name').val($button.data('cluster_name'));
                $('#upload_doc_cluster_caption').text($button.data('cluster_name'));
                $('#upload_doc_status').val($button.data('doc_status'));
                $('#upload_doc_remark').val($button.data('doc_remark'));
                $('#upload_doc_not_required').prop('checked', false);
                $('#batch-upload-file-input').val('').prop('disabled', false).prop('required', true);
                $('#batch-upload-file-name').text('Belum ada file dipilih');
                $('#batch-upload-progress-panel').hide();
                $('#batch-upload-progress-bar').removeClass('success').css('width', '0%');
                $('#batch-upload-progress-percent').text('0%');
                $('#batch-upload-document-submit').prop('disabled', false).text('Upload Dokumen');
            });

            $(document).on('click', '.js-approve-doc', function () {
                var $button = $(this);
                $('#approve_id_doc_file').val($button.data('id_doc_file'));
                $('#approve_cluster_name').val($button.data('cluster_name'));
                $('#approve_doc_name').text('RAR');
            });

            $(document).on('click', '.js-reject-doc', function () {
                var $button = $(this);
                $('#reject_id_doc_file').val($button.data('id_doc_file'));
                $('#reject_cluster_name').val($button.data('cluster_name'));
                $('#reject_doc_name').text('RAR');
            });

            $(document).on('click', '.js-transfer-proof', function () {
                var $button = $(this);
                $('#transfer_cluster_id').val($button.data('cluster_id'));
                $('#transfer_batch_id').val($button.data('id_batch_approval'));
                $('#transfer_cluster_name_caption').text($button.data('cluster_name'));
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

            $(document).on('change', '#upload_doc_not_required', function () {
                var checked = $(this).is(':checked');
                $('#batch-upload-file-input').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#batch-upload-file-input').val('');
                    $('#batch-upload-file-name').text('File tidak diperlukan untuk item ini');
                } else {
                    $('#batch-upload-file-name').text('Belum ada file dipilih');
                }
            });

            $('#batch-upload-document-form').on('submit', function (e) {
                e.preventDefault();

                var form = this;
                var submitButton = $('#batch-upload-document-submit');
                var progressPanel = $('#batch-upload-progress-panel');
                var progressBar = $('#batch-upload-progress-bar');
                var progressPercent = $('#batch-upload-progress-percent');
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

            $('#modal-batch-import').on('shown.bs.modal', function () {
                resetBatchImportPreview();
                $('#batch-import-file-input').val('');
                $('#batch-import-file-name').text('Belum ada file dipilih');
            });

            $('#batch-import-file-input').on('change', function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) {
                    return;
                }

                var formData = new FormData($('#batch-import-preview-form')[0]);
                formData.set('file_excel', file);
                $('#batch-import-summary').text('Memproses preview...');

                $.ajax({
                    url: batchPreviewImportUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (!response || !response.status) {
                            resetBatchImportPreview();
                            alert(response && response.message ? response.message : 'Preview import Batch gagal.');
                            return;
                        }

                        importedBatchRows = response.valid_rows || [];
                        $('#batch-import-summary').text(response.message || 'Preview selesai');
                        $('#batch-save-import-btn').prop('disabled', !importedBatchRows.length);
                        renderBatchImportPreview(response.rows || []);
                    },
                    error: function () {
                        resetBatchImportPreview();
                        alert('Terjadi kesalahan saat preview import Batch.');
                    }
                });
            });

            $('#batch-save-import-btn').on('click', function () {
                if (!importedBatchRows.length) {
                    alert('Belum ada data valid untuk disimpan.');
                    return;
                }

                $.ajax({
                    url: batchSaveImportUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { rows_json: JSON.stringify(importedBatchRows) },
                    success: function (response) {
                        if (response && response.status) {
                            alert(response.message || 'Import Batch berhasil.');
                            window.location.reload();
                            return;
                        }
                        alert(response && response.message ? response.message : 'Gagal menyimpan import Batch.');
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat menyimpan import Batch.');
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
                return toUpperUnique(Object.keys(batchCityOptionsByRegional || {}));
            }

            function allCityOptions() {
                var cities = [];
                Object.keys(batchCityOptionsByRegional || {}).forEach(function (regional) {
                    cities = cities.concat(batchCityOptionsByRegional[regional] || []);
                });
                return toUpperUnique(cities);
            }

            function allowedCitiesByRegionals(regionals) {
                if (!regionals.length) return allCityOptions();
                var cities = [];
                regionals.forEach(function (regional) {
                    cities = cities.concat(batchCityOptionsByRegional[regional] || []);
                });
                return toUpperUnique(cities);
            }

            function allowedRegionalsByCities(cities) {
                if (!cities.length) return allRegionalOptions();
                var regionals = [];
                cities.forEach(function (city) {
                    regionals = regionals.concat(batchRegionalOptionsByCity[city] || []);
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

            function syncBatchRegionCityFilters(changedFrom) {
                var $regional = $('#batch_download_regional');
                var $city = $('#batch_download_city');
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
                if (changedFrom === 'regional' && selectedRegionals.length) finalAllowedCities = allowedCitiesByRegionals(selectedRegionals);
                if (changedFrom === 'city' && selectedCities.length) finalAllowedRegionals = allowedRegionalsByCities(selectedCities);

                renderSelectOptions($regional, finalAllowedRegionals, selectedRegionals);
                renderSelectOptions($city, finalAllowedCities, selectedCities);
            }

            $('#modal-batch-download-report').on('shown.bs.modal', function () {
                $('#batch_download_date_start').val('');
                $('#batch_download_date_end').val('');

                $('#batch_download_regional, #batch_download_city').select2({
                    width: '100%',
                    dropdownParent: $('#modal-batch-download-report'),
                    placeholder: 'Pilih satu atau lebih',
                    allowClear: true
                });

                renderSelectOptions($('#batch_download_regional'), allRegionalOptions(), []);
                renderSelectOptions($('#batch_download_city'), allCityOptions(), []);
            });

            $('#batch_download_regional').on('change', function () {
                syncBatchRegionCityFilters('regional');
            });
            $('#batch_download_city').on('change', function () {
                syncBatchRegionCityFilters('city');
            });

            $('#batch-download-report-submit-btn').on('click', function () {
                var regionalValues = getSelectedArray($('#batch_download_regional'));
                var cityValues = getSelectedArray($('#batch_download_city'));
                var dateStart = ($('#batch_download_date_start').val() || '').trim();
                var dateEnd = ($('#batch_download_date_end').val() || '').trim();

                if (dateStart && dateEnd && dateStart > dateEnd) {
                    alert('Tanggal submission start tidak boleh lebih besar dari end.');
                    return;
                }

                var params = new URLSearchParams();
                if (batchSelectedStatus) params.set('status', batchSelectedStatus);
                regionalValues.forEach(function (regional) { params.append('regional[]', regional); });
                cityValues.forEach(function (city) { params.append('city[]', city); });
                if (dateStart) params.set('submission_date_start', dateStart);
                if (dateEnd) params.set('submission_date_end', dateEnd);
                window.location.href = batchDownloadReportUrl + '?' + params.toString();
            });

            bindDropzone('#batch-upload-dropzone', '#batch-upload-file-input', '#batch-upload-file-name');
            bindDropzone('#batch-import-dropzone', '#batch-import-file-input', '#batch-import-file-name');
            bindInlineDropzones();
            renderPicRows('create', []);
            toggleStageFields('create');
            fillCreateDefaults();
            initBatchCreateSelects();
            $('.js-number-format').each(function () {
                applyNumberFormatting($(this));
            });

            if ($.fn.DataTable) {
                try {
                    var batchTables = [];
                    ['#table_batch_ny_drm', '#table_batch_all'].forEach(function (selector) {
                        batchTables.push($(selector).DataTable({
                            responsive: false,
                            scrollX: true,
                            autoWidth: false,
                            order: [[0, 'asc']],
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

                                var numericCols = [4, 5, 6];
                                numericCols.forEach(function (colIdx) {
                                    var total = api
                                        .column(colIdx, { search: 'applied' })
                                        .data()
                                        .reduce(function (sum, val) {
                                            return sum + parseNumber(val);
                                        }, 0);

                                    $(api.column(colIdx).footer()).html(
                                        total.toLocaleString('id-ID', { maximumFractionDigits: 0 })
                                    );
                                });
                            },
                            language: {
                                emptyTable: 'Belum ada pengajuan Batch Approval.'
                            }
                        }));
                    });

                    $('a[data-toggle="tab"][href^="#batch-"]').on('shown.bs.tab', function () {
                        batchTables.forEach(function (table) {
                            table.columns.adjust();
                        });
                    });
                } catch (error) {
                    console.error('DataTable Batch Approval gagal diinisialisasi:', error);
                }
            }
        });
    })();
</script>
