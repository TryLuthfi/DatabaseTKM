<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$userValidation = (string) $this->session->userdata('validation');
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
$purchaseRequest = isset($purchase_request_meta) ? $purchase_request_meta : $detail_purchase_request[0];
$isEditMode = $type !== 'view';
$supportsVolumePlanning = array_key_exists('volume_planning', $purchaseRequest);

foreach ($detail_purchase_request as $detailIndex => $detailRow) {
    $volumePlanningValue = $supportsVolumePlanning && $detailRow['volume_planning'] !== null && $detailRow['volume_planning'] !== ''
        ? (float) $detailRow['volume_planning']
        : ((float) $detailRow['qty_planning'] > 0 ? (float) $detailRow['qty_planning'] : (float) $detailRow['qty_request']);

    $detail_purchase_request[$detailIndex]['volume_planning_value'] = $volumePlanningValue;
}

$stageIcons = [
    'SM' => 'fas fa-user-shield',
    'RPM' => 'fas fa-clipboard-check',
    'Planning' => 'fas fa-drafting-compass',
    'Manager Konstruksi' => 'fas fa-hard-hat',
    'Finance' => 'fas fa-wallet',
    'GM' => 'fas fa-briefcase',
    'Manager Logistik' => 'fas fa-truck-loading',
    'Direktur' => 'fas fa-user-tie',
];
$approvalStages = [];
foreach (($purchaseRequest['workflow_stages'] ?? []) as $stage) {
    $approvalStages[] = [
        'label' => $stage['label'],
        'approved' => isset($purchaseRequest[$stage['column']]) && (int) $purchaseRequest[$stage['column']] === 1,
        'icon' => isset($stageIcons[$stage['label']]) ? $stageIcons[$stage['label']] : 'fas fa-check-circle',
        'column' => $stage['column'],
    ];
}

$totalBoq = array_sum(array_map('floatval', array_column($detail_purchase_request, 'boq')));
$totalStokArea = array_sum(array_map('floatval', array_column($detail_purchase_request, 'stok_area')));
$totalQtyRequest = array_sum(array_map('floatval', array_column($detail_purchase_request, 'qty_request')));
$totalVolumePlanning = array_sum(array_map('floatval', array_column($detail_purchase_request, 'volume_planning_value')));
$approvalCompleted = count(array_filter($approvalStages, function ($stage) {
    return $stage['approved'];
}));
$currentApprovalKey = '';
$currentApprovalLabel = '';
foreach ($approvalStages as $stage) {
    if (!$stage['approved']) {
        $currentApprovalKey = strtolower(str_replace(' ', '_', $stage['label']));
        $currentApprovalLabel = $stage['label'];
        break;
    }
}
$normalizedValidation = strtolower(str_replace(' ', '_', trim($userValidation)));
$canApproveCurrentStage = !$isEditMode && $currentApprovalKey !== '' && ($isSuperAdmin || ($normalizedValidation !== '' && $currentApprovalKey === $normalizedValidation));
$showSeparateApproveButton = !$isEditMode && $currentApprovalKey !== '' && $currentApprovalKey !== 'planning';
$savePlanningLabel = ($currentApprovalKey === 'planning' || (int) ($purchaseRequest['approved_planning'] ?? 0) === 0)
    ? 'Simpan Review Planning'
    : 'Update Review Planning';
$nodinData = $nodinData ?? null;
$nodinDetailRows = $nodinDetailRows ?? [];
$nodinCandidateItems = $nodinCandidateItems ?? [];
$canManageNodin = !empty($canManageNodin);
$nodinApprovalStages = $nodinApprovalStages ?? [];
$nodinCurrentApprovalKey = $nodinCurrentApprovalKey ?? '';
$nodinCurrentApprovalLabel = $nodinCurrentApprovalLabel ?? '';
$canApproveCurrentNodinStage = !empty($canApproveCurrentNodinStage);
$nodinTotalKebutuhan = array_sum(array_map('floatval', array_column($nodinDetailRows, 'kebutuhan_project')));
$nodinTotalOutstanding = array_sum(array_map('floatval', array_column($nodinDetailRows, 'outstanding_pr')));
$nodinTotalQty = array_sum(array_map('floatval', array_column($nodinDetailRows, 'qty_po_nodin')));
$nodinTotalHargaSatuan = array_sum(array_map('floatval', array_column($nodinDetailRows, 'harga_satuan')));
$nodinTotalNominal = array_sum(array_map(static function ($row) {
    return ((float) ($row['qty_po_nodin'] ?? 0)) * ((float) ($row['harga_satuan'] ?? 0));
}, $nodinDetailRows));
$masterPabrikByName = [];
foreach (($masterPabrikOptions ?? []) as $pabrikOption) {
    $masterPabrikByName[strtolower(trim((string) ($pabrikOption['nama_pabrik'] ?? '')))] = (string) ($pabrikOption['id_pabrik'] ?? '');
}
?>

<style>
    .prd-revamp {
        --prd-ink: #0f172a;
        --prd-muted: #64748b;
        --prd-line: rgba(148, 163, 184, 0.22);
        --prd-surface: rgba(255, 255, 255, 0.96);
        --prd-surface-soft: rgba(248, 250, 252, 0.94);
        --prd-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
    }

    .prd-revamp .content-header {
        padding-bottom: 0;
    }

    .prd-shell {
        padding: 1.15rem;
    }

    .prd-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.22), transparent 28%),
            radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 26%),
            linear-gradient(135deg, #0f172a 0%, #102948 48%, #143a63 100%);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .prd-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .prd-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .prd-hero h1 {
        margin: 1rem 0 0.6rem;
        font-size: 1.9rem;
        font-weight: 800;
        color: #fff;
    }

    .prd-hero p {
        max-width: 44rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .prd-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .prd-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.82rem 1.15rem;
        border: 0;
        border-radius: 14px;
        font-weight: 700;
        transition: transform 0.18s ease;
    }

    .prd-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .prd-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
    }

    .prd-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .prd-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .prd-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .prd-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .prd-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .prd-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .prd-alert {
        margin-top: 1rem;
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1rem;
    }

    .prd-grid {
        display: grid;
        grid-template-columns: 1.15fr 1.85fr;
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .prd-panel {
        border: 1px solid var(--prd-line);
        border-radius: 24px;
        background: var(--prd-surface);
        box-shadow: var(--prd-shadow);
        overflow: hidden;
    }

    .prd-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .prd-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--prd-ink);
    }

    .prd-panel__subtitle {
        margin: 0.28rem 0 0;
        color: var(--prd-muted);
        font-size: 0.92rem;
    }

    .prd-panel__body {
        padding: 1.25rem;
    }

    .prd-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .prd-chip--blue {
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }

    .prd-chip--emerald {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .prd-chip--amber {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .prd-overview {
        display: grid;
        gap: 0.8rem;
    }

    .prd-overview__item {
        padding: 0.95rem 1rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
    }

    .prd-overview__label {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .prd-overview__value {
        display: block;
        margin-top: 0.45rem;
        color: var(--prd-ink);
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.55;
        word-break: break-word;
    }

    .prd-approval-list {
        display: grid;
        gap: 0.75rem;
    }

    .prd-approval-card {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        padding: 0.95rem 1rem;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: #fff;
    }

    .prd-approval-card__icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1rem;
    }

    .prd-approval-card.is-approved .prd-approval-card__icon {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .prd-approval-card.is-waiting .prd-approval-card__icon {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .prd-approval-card__title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--prd-ink);
    }

    .prd-approval-card__text {
        margin: 0.25rem 0 0;
        color: var(--prd-muted);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .prd-table-shell {
        margin-top: 1.2rem;
        border: 1px solid var(--prd-line);
        border-radius: 24px;
        background: var(--prd-surface);
        box-shadow: var(--prd-shadow);
        overflow: hidden;
    }

    .prd-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .prd-table-wrap {
        padding: 1rem 1rem 1.1rem;
    }

    .prd-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .prd-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .prd-table th,
    .prd-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .prd-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.7);
    }

    .prd-table tfoot td {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 800;
    }

    .prd-table .text-number {
        text-align: right;
        white-space: nowrap;
    }

    .prd-modal .select2-container {
        width: 100% !important;
    }

    .prd-modal .select2-container--bootstrap4 .select2-selection {
        min-height: calc(2.25rem + 2px);
        border-radius: 0.375rem;
        border-color: #ced4da;
    }

    .prd-modal .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: calc(2.25rem + 2px);
        padding-left: 0.75rem;
    }

    .prd-modal .select2-container--bootstrap4 .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }

    .prd-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .prd-modal .modal-content {
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 34px 80px rgba(15, 23, 42, 0.26);
    }

    .prd-modal .modal-dialog.modal-xxl {
        width: 78vw;
        max-width: 78vw;
    }

    @media (max-width: 767.98px) {
        .prd-modal .modal-dialog.modal-xxl {
            width: calc(100vw - 1rem);
            max-width: calc(100vw - 1rem);
            margin: 0.5rem auto;
        }
    }

    .prd-modal .modal-header {
        color: #fff;
        border-bottom: 0;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 24%),
            linear-gradient(135deg, #0f172a, #1d4ed8);
    }

    .prd-modal .modal-body {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));
    }

    .prd-modal__panel {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.84);
    }

    .prd-modal__panel label {
        display: block;
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    @media (max-width: 1199.98px) {
        .prd-hero__grid,
        .prd-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .prd-shell {
            padding: 0.8rem;
        }

        .prd-hero__grid {
            padding: 1rem;
        }

        .prd-hero h1 {
            font-size: 1.5rem;
        }

        .prd-metric-grid {
            grid-template-columns: 1fr;
        }

        .prd-panel__head,
        .prd-toolbar,
        .prd-hero__actions,
        .prd-table-head {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="content-wrapper prd-revamp">
    <div class="content-header">
        <div class="container-fluid prd-shell">
            <section class="prd-hero">
                <div class="prd-hero__grid">
                    <div>
                        <span class="prd-hero__eyebrow">
                            <i class="fas fa-file-alt"></i>
                            Purchase Request Detail
                        </span>
                        <h1><?= $purchaseRequest['nomor_purchase_request'] ?></h1>
                        <p>
                            Halaman ini merangkum header dokumen, status approval, dan rincian material PR. Mode view dipakai untuk monitoring lintas fungsi,
                            sedangkan mode planning membantu tim melengkapi BOQ dan qty planning sebelum dokumen bergerak ke approval berikutnya pada jalur <?= strtolower($purchaseRequest['origin_pr_label'] ?? 'pr') ?>.
                        </p>
                        <div class="prd-hero__actions">
                            <a href="<?= base_url('Logistik_Purchase_Request') ?>" class="prd-btn prd-btn--light">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke List PR
                            </a>
                            <button type="button" class="prd-btn prd-btn--ghost btn-upload-hardcopy" data-toggle="modal" data-target="#modal-upload-hardcopy">
                                <i class="fas fa-upload"></i>
                                Upload Hardcopy
                            </button>
                        </div>
                    </div>

                    <div class="prd-metric-grid">
                        <div class="prd-metric">
                            <span class="prd-metric__label">Tahap Approval</span>
                            <span class="prd-metric__value"><?= $approvalCompleted ?>/<?= count($approvalStages) ?></span>
                            <span class="prd-metric__hint">Jumlah approval yang sudah selesai pada flow saat ini.</span>
                        </div>
                        <div class="prd-metric">
                            <span class="prd-metric__label">Total Qty Request</span>
                            <span class="prd-metric__value"><?= number_format($totalQtyRequest, 0, ',', '.') ?></span>
                            <span class="prd-metric__hint">Akumulasi stok request dari semua item PR.</span>
                        </div>
                        <div class="prd-metric">
                            <span class="prd-metric__label">Total Volume Planning</span>
                            <span class="prd-metric__value"><?= number_format($totalVolumePlanning, 0, ',', '.') ?></span>
                            <span class="prd-metric__hint">Volume terakhir hasil planning yang nantinya dipakai sebagai acuan kirim.</span>
                        </div>
                        <div class="prd-metric">
                            <span class="prd-metric__label">Origin PR</span>
                            <span class="prd-metric__value" style="font-size:1.2rem;line-height:1.2;"><?= $purchaseRequest['origin_pr_label'] ?? 'PR' ?></span>
                            <span class="prd-metric__hint">Membedakan jalur area dan planning saat workflow bertambah.</span>
                        </div>
                        <div class="prd-metric">
                            <span class="prd-metric__label">Hardcopy</span>
                            <span class="prd-metric__value"><?= empty($purchaseRequest['hardcopy_file']) ? 'No' : 'Yes' ?></span>
                            <span class="prd-metric__hint">Indikator apakah PR sudah punya lampiran dokumen PDF.</span>
                        </div>
                        <div class="prd-metric">
                            <span class="prd-metric__label">Status NODIN</span>
                            <span class="prd-metric__value" style="font-size:1.15rem;line-height:1.2;"><?= !empty($nodinData['workflow_status_label']) ? $nodinData['workflow_status_label'] : 'Belum dibuat' ?></span>
                            <span class="prd-metric__hint">NODIN wajib approved penuh sebelum PR bisa dilanjutkan ke PO pabrik.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success prd-alert"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger prd-alert"><?= $flashError ?></div>
            <?php endif; ?>

            <form action="<?= base_url('Logistik_Purchase_Request/edit_purchase_request_by_planning') ?>" method="post" id="form-planning" enctype="multipart/form-data">
                <input type="hidden" name="id_purchase_request" value="<?= $purchaseRequest['id_purchase_request'] ?>">

                <div class="prd-grid">
                    <section class="prd-panel">
                        <div class="prd-panel__head">
                            <div>
                                <h2 class="prd-panel__title">Ringkasan Dokumen</h2>
                                <p class="prd-panel__subtitle">Informasi utama PR yang dipakai sebagai konteks review dan approval.</p>
                            </div>
                            <span class="prd-chip prd-chip--blue"><i class="fas fa-id-card"></i> Header PR</span>
                        </div>
                        <div class="prd-panel__body">
                            <div class="prd-overview">
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Origin PR</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['origin_pr_label'] ?? '-' ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Nomor PR</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['nomor_purchase_request'] ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Tanggal PR</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['tanggal_pembuatan'] ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Bowheer</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['id_project'] ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Lokasi Project</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['kota_lokasi_gudang'] ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Nama Project</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['nama_project'] ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Nomor SP</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['nomer_sp'] ?: '-' ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Tanggal SP</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['tanggal_sp'] ?: '-' ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Estimasi Pengiriman</span>
                                    <span class="prd-overview__value"><?= $purchaseRequest['tanggal_estimasi_pengiriman'] ?: '-' ?></span>
                                </div>
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Hardcopy PR</span>
                                    <span class="prd-overview__value">
                                        <?php if (empty($purchaseRequest['hardcopy_file'])): ?>
                                            <span class="prd-chip prd-chip--amber"><i class="fas fa-clock"></i> Belum Upload</span>
                                        <?php else: ?>
                                            <span class="prd-chip prd-chip--emerald"><i class="fas fa-check"></i> Sudah Upload</span>
                                            <br>
                                            <a href="<?= base_url() ?>./uploads/<?= $purchaseRequest['hardcopy_file'] ?>" target="_blank">Lihat Dokumen</a>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="prd-panel">
                        <div class="prd-panel__head">
                            <div>
                                <h2 class="prd-panel__title">Status Approval dan Aksi</h2>
                                <p class="prd-panel__subtitle">Kontrol approval dan aksi planning dalam satu area kerja.</p>
                            </div>
                            <span class="prd-chip <?= $approvalCompleted === count($approvalStages) ? 'prd-chip--emerald' : 'prd-chip--amber' ?>">
                                <i class="fas fa-check-circle"></i>
                                <?= $purchaseRequest['workflow_status_label'] ?? ($approvalCompleted === count($approvalStages) ? 'Approved' : 'Waiting Approval') ?>
                            </span>
                        </div>
                        <div class="prd-panel__body">
                            <div class="prd-approval-list">
                                <?php foreach ($approvalStages as $stage): ?>
                                    <article class="prd-approval-card <?= $stage['approved'] ? 'is-approved' : 'is-waiting' ?>">
                                        <div class="prd-approval-card__icon">
                                            <i class="<?= $stage['icon'] ?>"></i>
                                        </div>
                                        <div>
                                            <h3 class="prd-approval-card__title"><?= $stage['label'] ?></h3>
                                            <p class="prd-approval-card__text">
                                                <?= $stage['approved'] ? 'Tahap approval ini sudah selesai dan dokumen dapat lanjut ke tahap berikutnya.' : 'Tahap ini masih menunggu tindak lanjut agar dokumen bisa terus bergerak di workflow.' ?>
                                            </p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="prd-toolbar mt-4">
                                <a href="javascript:void(0);" class="btn btn-primary"><i class="fa fa-print mr-1"></i> Print</a>
                                <?php if (!$isEditMode): ?>
                                    <?php if ($showSeparateApproveButton): ?>
                                        <a href="#" class="btn btn-success btn-approve <?= $canApproveCurrentStage ? '' : 'disabled' ?>" data-id="<?= $purchaseRequest['id_purchase_request'] ?>" data-tipe="<?= $currentApprovalKey ?>">
                                            <i class="fa fa-check mr-1"></i> Approve <?= $currentApprovalLabel ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="#" class="btn btn-outline-primary btn-upload-hardcopy" data-target="#modal-upload-hardcopy" data-toggle="modal">
                                    <i class="fas fa-upload"></i> Upload Hardcopy
                                </a>
                                <a href="#" class="btn btn-success btn-save-planning <?= (!$isSuperAdmin && $userValidation != 'Planning') ? 'disabled' : '' ?>" <?= $isEditMode ? '' : 'hidden' ?>>
                                    <i class="fa fa-save mr-1"></i> <?= $savePlanningLabel ?>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="prd-table-shell">
                    <div class="prd-table-head">
                        <div>
                            <h2 class="prd-panel__title">Nota Dinas PO</h2>
                            <p class="prd-panel__subtitle">Tahap persetujuan internal HO sebelum PR approved boleh diterbitkan menjadi PO pabrik.</p>
                        </div>
                        <span class="prd-chip <?= !empty($nodinData['is_fully_approved']) ? 'prd-chip--emerald' : 'prd-chip--amber' ?>">
                            <i class="fas fa-file-signature"></i>
                            <?= !empty($nodinData['workflow_status_label']) ? $nodinData['workflow_status_label'] : 'Belum dibuat' ?>
                        </span>
                    </div>
                    <div class="prd-table-wrap">
                        <div class="prd-toolbar mb-3">
                            <?php if (!$isEditMode && !empty($nodinData) && !empty($nodinCurrentApprovalKey)): ?>
                                <a href="#" class="btn btn-success btn-approve-nodin <?= $canApproveCurrentNodinStage ? '' : 'disabled' ?>" data-id="<?= $nodinData['id_nota_dinas_po'] ?>" data-pr="<?= $purchaseRequest['id_purchase_request'] ?>" data-tipe="<?= $nodinCurrentApprovalKey ?>">
                                    <i class="fa fa-check mr-1"></i>
                                    Approve NODIN <?= $nodinCurrentApprovalLabel ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($nodinData)): ?>
                            <div class="prd-overview">
                                <div class="prd-overview__item">
                                    <span class="prd-overview__label">Status</span>
                                    <span class="prd-overview__value">NODIN belum dibuat. Setelah PR fully approved, admin logistik HO membuat NODIN sebagai dasar approval sebelum lanjut ke PO.</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="prd-grid" style="margin-top:0;">
                                <section class="prd-panel">
                                    <div class="prd-panel__body">
                                        <div class="prd-overview">
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Nomor NODIN</span>
                                                <span class="prd-overview__value"><?= $nodinData['nomor_nota_dinas'] ?: '-' ?></span>
                                            </div>
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Tanggal NODIN</span>
                                                <span class="prd-overview__value"><?= $nodinData['tanggal_nota_dinas'] ?: '-' ?></span>
                                            </div>
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Ditujukan Kepada</span>
                                                <span class="prd-overview__value"><?= $nodinData['ditujukan_kepada'] ?: '-' ?></span>
                                            </div>
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Tujuan Penerbitan PO</span>
                                                <span class="prd-overview__value"><?= $nodinData['tujuan_penerbitan_po'] ?: '-' ?></span>
                                            </div>
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Total Qty Usulan PO</span>
                                                <span class="prd-overview__value"><?= number_format($nodinTotalQty, 0, ',', '.') ?></span>
                                            </div>
                                            <div class="prd-overview__item">
                                                <span class="prd-overview__label">Total Nominal</span>
                                                <span class="prd-overview__value"><?= number_format($nodinTotalNominal, 0, ',', '.') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="prd-panel">
                                    <div class="prd-panel__body">
                                        <div class="prd-approval-list">
                                            <?php foreach ($nodinApprovalStages as $stage): ?>
                                                <?php $nodinStageApproved = !empty($nodinData[$stage['column']]); ?>
                                                <article class="prd-approval-card <?= $nodinStageApproved ? 'is-approved' : 'is-waiting' ?>">
                                                    <div class="prd-approval-card__icon">
                                                        <i class="fas <?= $nodinStageApproved ? 'fa-check-circle' : 'fa-hourglass-half' ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="prd-approval-card__title"><?= $stage['label'] ?></h3>
                                                        <p class="prd-approval-card__text">
                                                            <?= $nodinStageApproved ? 'Tahap approval NODIN ini sudah selesai.' : 'Tahap approval NODIN ini masih menunggu tindak lanjut.' ?>
                                                        </p>
                                                    </div>
                                                </article>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table prd-table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Material</th>
                                            <th>Satuan</th>
                                            <th class="text-number">Kebutuhan Project</th>
                                            <th class="text-number">Outstanding PR</th>
                                            <th class="text-number">PO Usulan</th>
                                            <th class="text-number">Harga Satuan</th>
                                            <th class="text-number">Harga Total</th>
                                            <th>Vendor / Pabrik</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nodinDetailRows as $nodinIndex => $nodinDetail): ?>
                                            <tr>
                                                <td><?= $nodinIndex + 1 ?></td>
                                                <td><?= $nodinDetail['nama_item'] ?: '-' ?></td>
                                                <td><?= $nodinDetail['satuan_item'] ?: '-' ?></td>
                                                <td class="text-number"><?= number_format($nodinDetail['kebutuhan_project'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format($nodinDetail['outstanding_pr'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format($nodinDetail['qty_po_nodin'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format($nodinDetail['harga_satuan'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format(((float) ($nodinDetail['qty_po_nodin'] ?? 0)) * ((float) ($nodinDetail['harga_satuan'] ?? 0)), 0, ',', '.') ?></td>
                                                <td><?= $nodinDetail['vendor_pabrik'] ?: '-' ?></td>
                                                <td><?= $nodinDetail['keterangan'] ?: '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">TOTAL</td>
                                            <td class="text-number"><?= number_format($nodinTotalKebutuhan, 0, ',', '.') ?></td>
                                            <td class="text-number"><?= number_format($nodinTotalOutstanding, 0, ',', '.') ?></td>
                                            <td class="text-number"><?= number_format($nodinTotalQty, 0, ',', '.') ?></td>
                                            <td class="text-number"><?= number_format($nodinTotalHargaSatuan, 0, ',', '.') ?></td>
                                            <td class="text-number"><?= number_format($nodinTotalNominal, 0, ',', '.') ?></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="prd-table-shell">
                    <div class="prd-table-head">
                        <div>
                            <h2 class="prd-panel__title">Rincian Material PR</h2>
                            <p class="prd-panel__subtitle">Planning cukup mengisi volume planning. BOQ dan stock area hanya ditampilkan sebagai referensi.</p>
                        </div>
                        <span class="prd-chip prd-chip--blue"><i class="fas fa-box-open"></i> Detail Item</span>
                    </div>
                    <div class="prd-table-wrap">
                        <div class="table-responsive">
                            <table class="table prd-table table-hover" id="table_item_stok">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Material</th>
                                        <th>Satuan</th>
                                        <th>BOQ</th>
                                        <th>Stock Area</th>
                                        <th>Stok Request</th>
                                        <th>Volume Planning</th>
                                        <th>Keterangan</th>
                                        <th>Keterangan Planning</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $number = 1; ?>
                                    <?php foreach ($detail_purchase_request as $key => $value): ?>
                                        <tr>
                                            <td>
                                                <?= $number++ ?>
                                                <input type="hidden" name="id_purchase_request_detail_[<?= $key ?>]" value="<?= $value['id_purchase_request_detail'] ?>">
                                            </td>
                                            <td><?= $value['nama_item'] ?></td>
                                            <td><?= $value['satuan_item'] ?></td>
                                            <td>
                                                <?= $value['boq'] ?>
                                            </td>
                                            <td class="stok_area"><?= $value['stok_area'] ?></td>
                                            <td class="qty_request"><?= $value['qty_request'] ?></td>
                                            <td>
                                                <?php if ($isEditMode): ?>
                                                    <input type="number" class="form-control volume_planning" name="volume_planning_[<?= $key ?>]" autocomplete="off" value="<?= $value['volume_planning_value'] ?>">
                                                <?php else: ?>
                                                    <?= $value['volume_planning_value'] ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $value['keterangan'] ?></td>
                                            <td>
                                                <?php if ($isEditMode): ?>
                                                    <input type="text" class="form-control" name="keterangan_planning_[<?= $key ?>]" autocomplete="off" value="<?= $value['keterangan_planning'] ?>">
                                                <?php else: ?>
                                                    <?= $value['keterangan_planning'] ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if ($isEditMode): ?>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">TOTAL</td>
                                            <td><b id="total_boq">0</b></td>
                                            <td><b id="total_stok_area">0</b></td>
                                            <td><b id="total_qty_request">0</b></td>
                                            <td><b id="total_volume_planning">0</b></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                <?php else: ?>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">TOTAL</td>
                                            <td><b><?= number_format($totalBoq, 0, ',', '.') ?></b></td>
                                            <td><b><?= number_format($totalStokArea, 0, ',', '.') ?></b></td>
                                            <td><b><?= number_format($totalQtyRequest, 0, ',', '.') ?></b></td>
                                            <td><b><?= number_format($totalVolumePlanning, 0, ',', '.') ?></b></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<?php if (!$isEditMode && !empty($purchaseRequest['is_fully_approved']) && $canManageNodin): ?>
    <div class="modal fade prd-modal" id="modal-nodin" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <form action="<?= base_url('Logistik_Purchase_Request/save_nodin') ?>" method="post" id="form-nodin" data-is-update="<?= empty($nodinData) ? '0' : '1' ?>" data-approval-progress="<?= (int) ($nodinData['workflow_progress'] ?? 0) ?>">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title">Nota Dinas - PO</h4>
                            <small>NODIN menjadi persetujuan internal HO sebelum PR approved boleh diterbitkan ke PO pabrik.</small>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_purchase_request" value="<?= $purchaseRequest['id_purchase_request'] ?>">
                        <div class="prd-modal__panel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nomor Nota Dinas</label>
                                        <input type="text" name="nomor_nota_dinas" class="form-control" value="<?= htmlspecialchars($nodinData['nomor_nota_dinas'] ?? '', ENT_QUOTES) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Nota Dinas</label>
                                        <input type="date" name="tanggal_nota_dinas" class="form-control" value="<?= !empty($nodinData['tanggal_nota_dinas']) ? $nodinData['tanggal_nota_dinas'] : date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Ditujukan Kepada</label>
                                        <input type="text" name="ditujukan_kepada" class="form-control" value="<?= htmlspecialchars($nodinData['ditujukan_kepada'] ?? '', ENT_QUOTES) ?>" placeholder="Direktur / approver tujuan">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Dibuat Oleh</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string) $this->session->userdata('nama_user'), ENT_QUOTES) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label>Tujuan Penerbitan PO</label>
                                        <input type="text" name="tujuan_penerbitan_po" class="form-control" value="<?= htmlspecialchars($nodinData['tujuan_penerbitan_po'] ?? ($purchaseRequest['nama_project'] ?: $purchaseRequest['id_project']), ENT_QUOTES) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table prd-table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Material</th>
                                        <th>Satuan</th>
                                        <th class="text-number">Kebutuhan Project</th>
                                        <th class="text-number">Outstanding PR</th>
                                        <th class="text-number">PO Usulan</th>
                                        <th class="text-number">Harga Satuan</th>
                                        <th class="text-number">Harga Total</th>
                                        <th>Vendor / Pabrik</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($nodinCandidateItems)): ?>
                                        <?php foreach ($nodinCandidateItems as $nodinItemIndex => $nodinItem): ?>
                                            <?php
                                            $existingDetail = [];
                                            foreach ($nodinDetailRows as $row) {
                                                if (($row['id_purchase_request_detail'] ?? '') === ($nodinItem['id_purchase_request_detail'] ?? '')) {
                                                    $existingDetail = $row;
                                                    break;
                                                }
                                            }

                                            $selectedPabrikId = (string) ($existingDetail['id_pabrik'] ?? '');
                                            if ($selectedPabrikId === '' && !empty($existingDetail['vendor_pabrik'])) {
                                                $selectedPabrikId = $masterPabrikByName[strtolower(trim((string) $existingDetail['vendor_pabrik']))] ?? '';
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= $nodinItemIndex + 1 ?>
                                                    <input type="hidden" name="id_purchase_request_detail[<?= $nodinItemIndex ?>]" value="<?= $nodinItem['id_purchase_request_detail'] ?>">
                                                    <input type="hidden" name="id_kode_item[<?= $nodinItemIndex ?>]" value="<?= $nodinItem['id_kode_item'] ?>">
                                                    <input type="hidden" name="kebutuhan_project[<?= $nodinItemIndex ?>]" value="<?= $nodinItem['volume_planning_final'] ?>">
                                                    <input type="hidden" name="outstanding_pr[<?= $nodinItemIndex ?>]" value="<?= $nodinItem['qty_outstanding_pr'] ?>">
                                                </td>
                                                <td><?= $nodinItem['nama_item'] ?></td>
                                                <td><?= $nodinItem['satuan_item'] ?: '-' ?></td>
                                                <td class="text-number js-nodin-kebutuhan"><?= number_format($nodinItem['volume_planning_final'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number js-nodin-outstanding"><?= number_format($nodinItem['qty_outstanding_pr'] ?? 0, 0, ',', '.') ?></td>
                                                <td><input type="number" class="form-control text-right js-nodin-qty" name="qty_po_nodin[<?= $nodinItemIndex ?>]" min="0" step="1" value="<?= htmlspecialchars((string) ($existingDetail['qty_po_nodin'] ?? (int) ($nodinItem['qty_outstanding_pr'] ?? 0)), ENT_QUOTES) ?>" required></td>
                                                <td><input type="number" class="form-control text-right js-nodin-harga" name="harga_satuan[<?= $nodinItemIndex ?>]" min="0" step="1" value="<?= htmlspecialchars((string) ($existingDetail['harga_satuan'] ?? '0'), ENT_QUOTES) ?>"></td>
                                                <td class="text-number js-nodin-line-total"><?= number_format(((float) ($existingDetail['qty_po_nodin'] ?? (int) ($nodinItem['qty_outstanding_pr'] ?? 0))) * ((float) ($existingDetail['harga_satuan'] ?? 0)), 0, ',', '.') ?></td>
                                                <td>
                                                    <select class="form-control js-select-pabrik" name="id_pabrik[<?= $nodinItemIndex ?>]" data-placeholder="Pilih Pabrik">
                                                        <option value="">Pilih Pabrik</option>
                                                        <?php foreach (($masterPabrikOptions ?? []) as $pabrikOption): ?>
                                                            <option value="<?= $pabrikOption['id_pabrik'] ?>" <?= $selectedPabrikId === (string) $pabrikOption['id_pabrik'] ? 'selected' : '' ?>>
                                                                <?= $pabrikOption['nama_pabrik'] ?> | <?= $pabrikOption['jenis_pabrik'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control" name="keterangan_nodin[<?= $nodinItemIndex ?>]" value="<?= htmlspecialchars((string) ($existingDetail['keterangan'] ?? ''), ENT_QUOTES) ?>"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Tidak ada item outstanding PR yang bisa dibawa ke NODIN.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($nodinCandidateItems)): ?>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">TOTAL</td>
                                            <td class="text-number" id="nodin-total-kebutuhan">0</td>
                                            <td class="text-number" id="nodin-total-outstanding">0</td>
                                            <td class="text-number" id="nodin-total-qty">0</td>
                                            <td class="text-number" id="nodin-total-harga-satuan">0</td>
                                            <td class="text-number" id="nodin-total-nilai">0</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><?= empty($nodinData) ? 'Simpan NODIN' : 'Update NODIN' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade prd-modal" id="modal-upload-hardcopy" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('Logistik_Purchase_Request/upload_hardcopy') ?>" method="post" id="form-upload-hardcopy" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title">Upload Hardcopy PR</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="prd-modal__panel">
                        <div class="form-group mb-0">
                            <label>Upload Document PDF</label>
                            <div class="custom-file">
                                <input type="file" name="file-hardcopy" id="file-hardcopy" class="custom-file-input" required>
                                <label class="custom-file-label" for="file-hardcopy">Choose file</label>
                                <input type="hidden" name="id_purchase_request" value="<?= $purchaseRequest['id_purchase_request'] ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function formatNodinNumber(value) {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(value || 0);
    }

    function parseNodinNumber(value) {
        if (typeof value === 'number') {
            return value;
        }

        const normalized = String(value || '')
            .replace(/\./g, '')
            .replace(',', '.')
            .replace(/[^0-9.-]/g, '');

        return parseFloat(normalized) || 0;
    }

    function refreshNodinFooter() {
        let totalKebutuhan = 0;
        let totalOutstanding = 0;
        let totalQty = 0;
        let totalHargaSatuan = 0;
        let totalNilai = 0;

        $("#form-nodin tbody tr").each(function() {
            const kebutuhan = parseNodinNumber($(this).find('.js-nodin-kebutuhan').text());
            const outstanding = parseNodinNumber($(this).find('.js-nodin-outstanding').text());
            const qty = parseFloat($(this).find('.js-nodin-qty').val() || 0);
            const harga = parseFloat($(this).find('.js-nodin-harga').val() || 0);
            const lineTotal = qty * harga;

            totalKebutuhan += kebutuhan;
            totalOutstanding += outstanding;
            totalQty += qty;
            totalHargaSatuan += harga;
            totalNilai += lineTotal;

            $(this).find('.js-nodin-line-total').text(formatNodinNumber(lineTotal));
        });

        $('#nodin-total-kebutuhan').text(formatNodinNumber(totalKebutuhan));
        $('#nodin-total-outstanding').text(formatNodinNumber(totalOutstanding));
        $('#nodin-total-qty').text(formatNodinNumber(totalQty));
        $('#nodin-total-harga-satuan').text(formatNodinNumber(totalHargaSatuan));
        $('#nodin-total-nilai').text(formatNodinNumber(totalNilai));
    }

    function initNodinPabrikSelect() {
        if (!$.fn.select2 || !$('#modal-nodin').length) {
            return;
        }

        $('#modal-nodin .js-select-pabrik').each(function() {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: $select.data('placeholder') || 'Pilih Pabrik',
                allowClear: true,
                dropdownParent: $('#modal-nodin')
            });
        });
    }

    function hitungTotal() {
        let totalBoq = 0;
        let totalStokArea = 0;
        let totalQtyRequest = 0;
        let totalVolumePlanning = 0;

        $("#table_item_stok tbody tr").each(function() {
            totalBoq += parseFloat($(this).find('td').eq(3).text()) || 0;
        });

        $(".stok_area").each(function() {
            totalStokArea += parseFloat($(this).text()) || 0;
        });

        $(".qty_request").each(function() {
            totalQtyRequest += parseFloat($(this).text()) || 0;
        });

        $(".volume_planning").each(function() {
            totalVolumePlanning += parseFloat($(this).val()) || 0;
        });

        $("#total_boq").text(totalBoq);
        $("#total_stok_area").text(totalStokArea);
        $("#total_qty_request").text(totalQtyRequest);
        $("#total_volume_planning").text(totalVolumePlanning);
    }

    $(document).ready(function() {
        hitungTotal();
        refreshNodinFooter();
        initNodinPabrikSelect();

        $(".volume_planning").on("input", function() {
            hitungTotal();
        });

        $("#form-nodin").on("input", ".js-nodin-qty, .js-nodin-harga", function() {
            refreshNodinFooter();
        });

        $(".btn-save-planning").click(function() {
            Swal.fire({
                title: "Apakah Anda yakin ingin menyimpan?",
                text: "Pastikan semua data planning sudah benar sebelum menyimpan.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#form-planning").submit();
                }
            });
        });

        $(".btn-approve").click(function(e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) {
                return false;
            }

            let id = $(this).data("id");
            let tipe = $(this).data("tipe");

            Swal.fire({
                title: "Apakah Anda yakin ingin menyetujui?",
                text: "Setelah disetujui, data tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Ya, Setujui!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('Logistik_Purchase_Request/approve_purchase_request') ?>",
                        type: "POST",
                        data: {
                            id_purchase_request: id,
                            tipe: tipe
                        },
                        success: function() {
                            Swal.fire("Berhasil!", "Purchase Request telah disetujui.", "success")
                                .then(() => location.reload());
                        },
                        error: function() {
                            Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi.", "error");
                        }
                    });
                }
            });
        });

        $(".btn-approve-nodin").click(function(e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) {
                return false;
            }

            const idNodin = $(this).data("id");
            const idPurchaseRequest = $(this).data("pr");
            const tipe = $(this).data("tipe");

            Swal.fire({
                title: "Approve NODIN ini?",
                text: "Setelah tahap ini disetujui, NODIN akan maju ke approver berikutnya.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Ya, Setujui!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('Logistik_Purchase_Request/approve_nodin') ?>",
                        type: "POST",
                        data: {
                            id_purchase_request: idPurchaseRequest,
                            id_nota_dinas_po: idNodin,
                            tipe: tipe
                        },
                        success: function() {
                            Swal.fire("Berhasil!", "Approval NODIN berhasil diperbarui.", "success")
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            const responseMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan, coba lagi.';
                            Swal.fire("Gagal!", responseMessage, "error");
                        }
                    });
                }
            });
        });

        $("#form-nodin").submit(function(event) {
            let hasInvalidQty = false;
            $("#form-nodin input[name^='qty_po_nodin']").each(function() {
                const value = parseFloat($(this).val() || 0);
                if (value <= 0) {
                    hasInvalidQty = true;
                }
            });

            if (hasInvalidQty) {
                event.preventDefault();
                Swal.fire('Qty NODIN tidak valid', 'Qty PO usulan pada NODIN harus lebih dari 0.', 'warning');
                return;
            }

            const $form = $(this);
            const isUpdate = $form.data('is-update') === 1 || $form.data('is-update') === '1';
            const approvalProgress = parseInt($form.data('approval-progress') || 0, 10);
            const confirmationMessage = approvalProgress > 0
                ? 'Perubahan NODIN ini akan mengulang proses approval dari awal. Lanjutkan update?'
                : 'Edit NODIN ini akan tetap mengulang proses approval dari awal. Lanjutkan update?';

            if (isUpdate && !$form.data('confirmed')) {
                event.preventDefault();
                Swal.fire({
                    title: 'Update NODIN?',
                    text: confirmationMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Ya, Update NODIN',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $form.data('confirmed', true);
                        $form.trigger('submit');
                    }
                });
            }
        });

        $('.custom-file-input').on('change', function() {
            let file = this.files[0];
            let allowedExtensions = /\.pdf$/i;
            let maxSize = 5120 * 1024;
            let fileName = file ? file.name : "Choose file";

            $(this).siblings('.custom-file-label').text(fileName);

            if (file && !allowedExtensions.test(file.name)) {
                Swal.fire('Format tidak sesuai', 'File harus berupa PDF.', 'warning');
                $(this).val("");
                $(this).siblings('.custom-file-label').text("Choose file");
                return;
            }

            if (file && file.size > maxSize) {
                Swal.fire('File terlalu besar', 'Ukuran file tidak boleh lebih dari 5MB.', 'warning');
                $(this).val("");
                $(this).siblings('.custom-file-label').text("Choose file");
            }
        });
    });
</script>
