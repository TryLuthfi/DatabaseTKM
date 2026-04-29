<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('batchDetailBadgeClass')) {
    function batchDetailBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'WAITING HO':
            case 'WAITING MYREP':
                return 'info';
            case 'WAITING FINANCE':
            case 'ON REVIEW':
            case 'UPLOADED':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('batchDetailStatusLabel')) {
    function batchDetailStatusLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'WAITING MYREP') {
            return 'WAITING EMR';
        }
        return $status !== '' ? $status : 'DRAFT';
    }
}

if (!function_exists('batchDetailDocumentLabel')) {
    function batchDetailDocumentLabel($row)
    {
        if ((int) ($row['is_document_not_required'] ?? $row['batch_doc_not_required'] ?? 0) === 1) {
            return 'TIDAK BUTUH DOKUMENT';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? $row['batch_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status !== '' ? $status : 'BELUM UPLOAD';
    }
}

if (!function_exists('batchDetailStageMeta')) {
    function batchDetailStageMeta($status)
    {
        $status = strtoupper(trim((string) $status));
        switch ($status) {
            case 'WAITING HO':
                return ['percent' => 25, 'class' => 'bg-info', 'label' => 'Menunggu review HO'];
            case 'WAITING MYREP':
                return ['percent' => 50, 'class' => 'bg-primary', 'label' => 'Menunggu approval EMR'];
            case 'WAITING FINANCE':
                return ['percent' => 75, 'class' => 'bg-warning', 'label' => 'Menunggu release finance'];
            case 'WAITING DOC':
                return ['percent' => 90, 'class' => 'bg-warning', 'label' => 'Menunggu upload 12 dokumen post donasi'];
            case 'COMPLETED':
                return ['percent' => 100, 'class' => 'bg-success', 'label' => 'Dokumen post donasi lengkap'];
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
                return ['percent' => 100, 'class' => 'bg-success', 'label' => 'Batch approval selesai'];
            case 'REJECTED':
                return ['percent' => 100, 'class' => 'bg-danger', 'label' => 'Batch approval ditolak'];
            default:
                return ['percent' => 10, 'class' => 'bg-secondary', 'label' => 'Draft'];
        }
    }
}

$displayStageStatus = (string) ($cluster['display_staging_status'] ?? $cluster['staging_status'] ?? 'DRAFT');
$stageMeta = batchDetailStageMeta($displayStageStatus);
$batchDocumentStatus = batchDetailDocumentLabel($batchDocument);
$batchDocumentRawStatus = strtoupper(trim((string) ($batchDocument['status_file'] ?? '')));
$batchDocumentCanUpload = in_array($batchDocumentStatus, ['BELUM UPLOAD', 'REJECTED'], true);
$batchDocumentCanReview = $canApprove && !empty($batchDocument['id_doc_file']) && $batchDocumentRawStatus === 'UPLOADED';
$transferProofPath = (string) ($cluster['transfer_proof_file_path'] ?? '');
$transferProofExtension = strtolower(pathinfo($transferProofPath, PATHINFO_EXTENSION));
$isTransferProofImage = $transferProofPath !== '' && in_array($transferProofExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
$initialPics = !empty($batchPics) ? $batchPics : [[
    'pic_no' => 1,
    'pic_name' => $cluster['recipient_name'] ?? '',
    'pic_phone' => $cluster['recipient_phone'] ?? '',
    'pic_position' => $cluster['recipient_position'] ?? '',
    'pic_period' => $cluster['recipient_period'] ?? '',
    'is_primary' => 1,
]];
$statusOptions = [
    'DRAFT' => 'DRAFT',
    'WAITING HO' => 'WAITING HO',
    'WAITING MYREP' => 'WAITING MYREP',
    'WAITING FINANCE' => 'WAITING FINANCE',
    'RELEASED' => 'RELEASED',
    'DONE BATCH APPROVAL' => 'DONE BATCH APPROVAL',
    'REJECTED' => 'REJECTED',
];
$currentStage = strtoupper(trim((string) ($cluster['staging_status'] ?? 'DRAFT')));
$stageButtonTarget = '';
$stageButtonLabel = '';
if ($canApprove) {
    if ($currentStage === 'WAITING HO') {
        $stageButtonTarget = '#modal-stage-to-myrep';
        $stageButtonLabel = 'Edit Staging';
    } elseif ($currentStage === 'WAITING MYREP') {
        $stageButtonTarget = '#modal-stage-to-finance';
        $stageButtonLabel = 'Edit Staging';
    } elseif ($currentStage === 'WAITING FINANCE') {
        $stageButtonTarget = '#modal-stage-to-released';
        $stageButtonLabel = 'Edit Staging';
    }
}
?>

<style>
    .batch-info-card .card-header,
    .batch-doc-card .card-header,
    .batch-post-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .batch-progress-wrap {
        background: #f6f8fb;
        border: 1px solid #e7ecf3;
        border-radius: 16px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
    }

    .batch-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .6rem;
        font-weight: 700;
        color: #1f2937;
    }

    .batch-progress-caption {
        font-size: .9rem;
        color: #6b7280;
    }

    .batch-progress {
        height: 14px;
        border-radius: 999px;
        background: #e7ecf3;
        overflow: hidden;
    }

    .batch-progress .progress-bar {
        font-weight: 700;
        font-size: .7rem;
        line-height: 14px;
    }

    .batch-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .55rem .95rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.65);
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
    }

    .batch-edit-btn:hover,
    .batch-edit-btn:focus {
        color: #fff;
        background: linear-gradient(135deg, #1e40af, #1d4ed8);
    }

    .batch-info-grid strong {
        display: block;
        margin-bottom: .2rem;
        color: #334155;
    }

    .batch-info-grid > div {
        margin-bottom: 1rem;
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

    .doc-history-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .doc-history-item {
        border-left: 3px solid #d8e3ee;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .doc-history-item:last-child {
        margin-bottom: 0;
    }

    .doc-history-title {
        font-weight: 700;
        color: #1f2937;
    }

    .doc-history-meta {
        color: #6b7280;
        font-size: .86rem;
        margin-bottom: .2rem;
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

    .batch-review-card {
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        background: #fbfdff;
    }

    .batch-review-card__title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .3rem;
    }

    .batch-review-card__text {
        color: #6b7280;
        font-size: .9rem;
        margin-bottom: .8rem;
    }

    .batch-transfer-preview {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e7ecf3;
    }

    .batch-transfer-preview__image {
        width: 100%;
        max-height: 300px;
        object-fit: contain;
        border-radius: 12px;
        border: 1px solid #dbe3ee;
        background: #fff;
        padding: .35rem;
    }

    .batch-pic-detail {
        display: grid;
        gap: .35rem;
        margin-top: .7rem;
    }

    .batch-pic-detail div {
        font-size: .92rem;
        color: #374151;
    }

    .batch-pic-detail strong {
        display: inline-block;
        min-width: 74px;
        color: #111827;
    }

    .batch-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .batch-modal .modal-header {
        border-bottom: 0;
    }

    .batch-modal .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .batch-modal .modal-footer {
        border-top: 0;
        background: #eef2f7;
    }

    .batch-edit-header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    .batch-stage-note {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        padding: .9rem 1rem;
        color: #475569;
        font-size: .92rem;
        line-height: 1.55;
        margin-bottom: 1rem;
    }

    .batch-stage-cluster-box {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .batch-stage-cluster-box__title {
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
        margin-bottom: .85rem;
    }

    .batch-stage-cluster-box strong {
        display: block;
        color: #334155;
        margin-bottom: .18rem;
    }

    .modal-xxl {
        max-width: 78vw;
    }

    @media (max-width: 767.98px) {
        .batch-form-section__head,
        .batch-pic-card__head,
        .batch-progress-meta {
            flex-direction: column;
        }

        .modal-xxl {
            max-width: calc(100vw - 1rem);
            margin: .5rem auto;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Batch Approval</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
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

            <div class="card card-primary shadow-sm batch-info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Informasi Cluster & Batch</h3>
                    <button type="button" class="btn btn-sm batch-edit-btn" data-toggle="modal" data-target="#modal-batch-edit-detail">
                        <i class="fas fa-pen"></i>
                        Edit Batch Approval
                    </button>
                </div>
                <div class="card-body">
                    <div class="batch-progress-wrap">
                        <div class="batch-progress-meta">
                            <div>Progress Batch Approval</div>
                            <div class="d-flex align-items-center" style="gap:.75rem;">
                                <div><?= htmlspecialchars(batchDetailStatusLabel($displayStageStatus)) ?> · <?= (int) $stageMeta['percent'] ?>%</div>
                                <?php if ($stageButtonTarget !== ''): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="<?= htmlspecialchars($stageButtonTarget) ?>">
                                        <i class="fas fa-edit"></i> <?= htmlspecialchars($stageButtonLabel) ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="batch-progress-caption mb-2"><?= htmlspecialchars($stageMeta['label']) ?></div>
                        <div class="progress batch-progress">
                            <div class="progress-bar <?= htmlspecialchars($stageMeta['class']) ?>" role="progressbar" style="width: <?= (int) $stageMeta['percent'] ?>%;" aria-valuenow="<?= (int) $stageMeta['percent'] ?>" aria-valuemin="0" aria-valuemax="100"><?= (int) $stageMeta['percent'] ?>%</div>
                        </div>
                    </div>

                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>HP Donasi</strong><div><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>Tanggal Pengajuan</strong><div><?= !empty($cluster['submission_date']) ? htmlspecialchars((string) $cluster['submission_date']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-3"><strong>Nominal Donasi</strong><div><?= number_format((float) ($cluster['nominal_pengajuan_area'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-3"><strong>Nominal / Homepass</strong><div><?= !is_null($cluster['nominal_per_homepass'] ?? null) ? number_format((float) $cluster['nominal_per_homepass'], 2, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Nominal Approval EMR</strong><div><?= !is_null($cluster['nominal_nego_emr'] ?? null) ? number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Nominal Release</strong><div><?= !is_null($cluster['nominal_release_finance'] ?? null) ? number_format((float) $cluster['nominal_release_finance'], 0, ',', '.') : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Penerima Dana</strong><div><?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>No HP</strong><div><?= !empty($cluster['recipient_phone']) ? htmlspecialchars((string) $cluster['recipient_phone']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Jabatan</strong><div><?= !empty($cluster['recipient_position']) ? htmlspecialchars((string) $cluster['recipient_position']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Periode</strong><div><?= !empty($cluster['recipient_period']) ? htmlspecialchars((string) $cluster['recipient_period']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row batch-info-grid">
                        <div class="col-md-4"><strong>Bank</strong><div><?= htmlspecialchars((string) ($cluster['bank_name'] ?? '-')) ?></div></div>
                        <div class="col-md-4"><strong>No Rekening</strong><div><?= htmlspecialchars((string) ($cluster['bank_account_number'] ?? '-')) ?></div></div>
                        <div class="col-md-4"><strong>No Batch Astri</strong><div><?= !empty($cluster['astri_batch_number']) ? htmlspecialchars((string) $cluster['astri_batch_number']) : '-' ?></div></div>
                    </div>
                    <?php if (!empty($batchPics)): ?>
                        <hr>
                        <div class="row">
                            <?php foreach ($batchPics as $pic): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <strong>PIC <?= (int) ($pic['pic_no'] ?? 0) ?></strong>
                                        <div class="batch-pic-detail">
                                            <div><strong>Nama</strong> <?= htmlspecialchars((string) ($pic['pic_name'] ?? '-')) ?></div>
                                            <div><strong>No HP</strong> <?= !empty($pic['pic_phone']) ? htmlspecialchars((string) $pic['pic_phone']) : '-' ?></div>
                                            <div><strong>Jabatan</strong> <?= !empty($pic['pic_position']) ? htmlspecialchars((string) $pic['pic_position']) : '-' ?></div>
                                            <div><strong>Periode</strong> <?= !empty($pic['pic_period']) ? htmlspecialchars((string) $pic['pic_period']) : '-' ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm batch-doc-card">
                <div class="card-header">
                    <h3 class="card-title">Dokumen Batch Approval</h3>
                </div>
                <div class="card-body">
                    <?php if (!$docReady): ?>
                        <div class="alert alert-warning mb-0">Tabel dokumen Batch Approval belum tersedia.</div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-lg-7 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <strong>RAR</strong>
                                            <div class="text-muted small">Status dokumen utama batch approval</div>
                                        </div>
                                        <span class="badge badge-<?= batchDetailBadgeClass($batchDocumentStatus) ?>"><?= htmlspecialchars($batchDocumentStatus) ?></span>
                                    </div>
                                    <div class="mb-2"><strong>File:</strong> <?= !empty($batchDocument['file_name']) ? htmlspecialchars((string) $batchDocument['file_name']) : '-' ?></div>
                                    <div class="mb-3"><strong>Remark:</strong> <?= !empty($batchDocument['remark']) ? htmlspecialchars((string) $batchDocument['remark']) : '-' ?></div>
                                    <button
                                        type="button"
                                        class="btn btn-sm <?= $batchDocumentCanUpload ? 'btn-primary' : 'btn-outline-primary' ?> js-open-batch-rar-modal"
                                        data-toggle="modal"
                                        data-target="#modal-batch-rar"
                                        data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>"
                                        data-file-path="<?= htmlspecialchars((string) ($batchDocument['file_path'] ?? ''), ENT_QUOTES) ?>"
                                        data-remark="<?= htmlspecialchars((string) ($batchDocument['remark'] ?? ''), ENT_QUOTES) ?>"
                                        data-status-label="<?= htmlspecialchars($batchDocumentStatus, ENT_QUOTES) ?>"
                                        data-can-upload="<?= $batchDocumentCanUpload ? '1' : '0' ?>">
                                        <?= $batchDocumentCanUpload ? 'Upload RAR' : 'Lihat RAR' ?>
                                    </button>
                                    <?php if (!empty($cluster['transfer_proof_file_path']) && $isTransferProofImage): ?>
                                        <div class="batch-transfer-preview">
                                            <div class="small text-muted mb-2">Preview bukti transfer</div>
                                            <img src="<?= base_url($transferProofPath) ?>" alt="Bukti Transfer" class="batch-transfer-preview__image">
                                            <div class="mt-2">
                                                <a href="<?= base_url($transferProofPath) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Gambar</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-5 mb-3">
                                <div class="batch-review-card h-100">
                                    <div class="batch-review-card__title">Review Dokumen Batch</div>
                                    <div class="batch-review-card__text">Review hanya muncul saat file sudah masuk dan masih menunggu keputusan.</div>
                                    <?php if ($batchDocumentCanReview): ?>
                                        <button type="button" class="btn btn-success btn-sm js-open-batch-review-modal" data-toggle="modal" data-target="#modal-batch-approve" data-file-id="<?= (int) ($batchDocument['id_doc_file'] ?? 0) ?>" data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm js-open-batch-review-modal" data-toggle="modal" data-target="#modal-batch-reject" data-file-id="<?= (int) ($batchDocument['id_doc_file'] ?? 0) ?>" data-file-name="<?= htmlspecialchars((string) ($batchDocument['file_name'] ?? ''), ENT_QUOTES) ?>">Reject</button>
                                    <?php else: ?>
                                        <span class="text-muted small">Belum ada dokumen yang perlu direview.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <strong>History Dokumen</strong>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                            <th>File</th>
                                            <th>Remark</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($batchDocumentLogs as $log): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($log['action_at'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($log['action_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($log['file_name'] ?? '-')) ?></td>
                                                <td><?= !empty($log['remark']) ? htmlspecialchars((string) $log['remark']) : '-' ?></td>
                                                <td><?= !empty($log['nama_user']) ? htmlspecialchars((string) $log['nama_user']) : 'System' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($batchDocumentLogs)): ?>
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada history dokumen.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm batch-post-card">
                <div class="card-header">
                    <h3 class="card-title">Post Donasi di Detail Batch Approval</h3>
                </div>
                <div class="card-body">
                    <?php if (!$postDonasiDocReady): ?>
                        <div class="alert alert-warning mb-0">Tabel dokumen post donasi belum tersedia.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Catatan</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Upload</th>
                                        <?php if ($canApprove): ?><th>Review</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postDonasiRows as $row): ?>
                                        <?php
                                        $postStatus = batchDetailDocumentLabel($row);
                                        $postRawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                        $postCanUpload = $postStatus === 'BELUM UPLOAD' || $postRawStatus === 'REJECTED';
                                        $postCanReview = $canApprove && !empty($row['id_doc_file']) && $postRawStatus === 'UPLOADED';
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                            <td><?= htmlspecialchars((string) ($row['doc_requirement_note'] ?? '-')) ?></td>
                                            <td><span class="badge badge-<?= batchDetailBadgeClass($postStatus) ?>"><?= htmlspecialchars($postStatus) ?></span></td>
                                            <td>
                                                <?php if (!empty($row['file_name'])): ?>
                                                    <div><?= htmlspecialchars((string) $row['file_name']) ?></div>
                                                    <a href="<?= base_url('Post_Donasi_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Preview</a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-dark mt-1 js-doc-history"
                                                        data-toggle="modal"
                                                        data-target="#modal-doc-history"
                                                        data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-history='<?= htmlspecialchars(json_encode(!empty($row['id_doc_file']) ? $this->MPost_Donasi_MyRep->getFileLogs((int) $row['id_doc_file']) : []), ENT_QUOTES) ?>'>
                                                        History
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:220px;">
                                                <?php if ($postCanUpload): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary js-open-post-upload-modal"
                                                        data-toggle="modal"
                                                        data-target="#modal-post-upload"
                                                        data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                        data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                        Upload
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small">Upload tidak tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($canApprove): ?>
                                                <td style="min-width:220px;">
                                                    <?php if ($postCanReview): ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success js-open-post-review-modal"
                                                            data-toggle="modal"
                                                            data-target="#modal-post-approve"
                                                            data-file-id="<?= (int) $row['id_doc_file'] ?>"
                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>">
                                                            Approve
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-danger js-open-post-review-modal"
                                                            data-toggle="modal"
                                                            data-target="#modal-post-reject"
                                                            data-file-id="<?= (int) $row['id_doc_file'] ?>"
                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>">
                                                            Reject
                                                        </button>
                                                    <?php elseif ($postRawStatus === 'APPROVED'): ?>
                                                        <span class="text-success small font-weight-bold">Sudah approved</span>
                                                    <?php elseif ($postRawStatus === 'REJECTED'): ?>
                                                        <span class="text-danger small font-weight-bold">Sudah rejected</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada file untuk direview</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($postDonasiRows)): ?>
                                        <tr><td colspan="<?= $canApprove ? '6' : '5' ?>" class="text-center text-muted">Belum ada dokumen post donasi.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-batch-edit-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateBatchApproval') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header batch-edit-header">
                    <h5 class="modal-title">Edit Batch Approval</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-12"><div class="form-group"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['regional_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['province_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '')) ?>" readonly></div></div>
                            <div class="col-md-12"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['valsal_date'] ?? '')) ?>" readonly></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Data Pengajuan</div>
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>HP VALSAL</label><input type="text" id="detail_edit_homepass_valsal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['homepass_valsal'] ?? null) ? htmlspecialchars(number_format((float) $cluster['homepass_valsal'], 0, ',', '.')) : '' ?>" readonly></div></div>
                            <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="text" name="hp_donasi" id="detail_edit_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['hp_donasi'] ?? null) ? htmlspecialchars(number_format((float) $cluster['hp_donasi'], 0, ',', '.')) : '' ?>" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan</label><input type="date" name="submission_date" id="detail_edit_submission_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['submission_date'] ?? '')) ?>"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Staging</label><select name="staging_status" id="detail_edit_staging_status" class="form-control"><?php foreach ($statusOptions as $statusValue => $statusLabel): ?><option value="<?= $statusValue ?>" <?= strtoupper((string) ($cluster['staging_status'] ?? '')) === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="detail_edit_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" required></div></div>
                            <div class="col-md-6"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="detail_edit_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" value="<?= !is_null($cluster['nominal_per_homepass'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_per_homepass'], 2, ',', '.')) : '' ?>" readonly></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Free Wifi</div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['free_wifi_qty'] ?? null) ? htmlspecialchars(number_format((float) $cluster['free_wifi_qty'], 0, ',', '.')) : '' ?>"></div></div>
                            <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['free_wifi_period_month'] ?? null) ? htmlspecialchars(number_format((float) $cluster['free_wifi_period_month'], 0, ',', '.')) : '' ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars((string) ($cluster['bank_name'] ?? '')) ?>" required></div></div>
                            <div class="col-md-6"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars((string) ($cluster['bank_account_number'] ?? '')) ?>" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Nama Penerima Dana</label><input type="text" name="recipient_name" id="detail_edit_recipient_name" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '')) ?>" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="detail_edit_recipient_phone" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_phone'] ?? '')) ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Jabatan Penerima</label><input type="text" name="recipient_position" id="detail_edit_recipient_position" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_position'] ?? '')) ?>"></div></div>
                            <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="detail_edit_recipient_period" class="form-control js-recipient-source" value="<?= htmlspecialchars((string) ($cluster['recipient_period'] ?? '')) ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section js-emr-fields" data-stage-scope="detail-edit" style="display:none;">
                        <div class="batch-form-section__title">Approval EMR</div>
                        <div class="row">
                            <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Approval EMR</label><input type="text" name="nominal_nego_emr" id="detail_edit_nominal_nego_emr" inputmode="decimal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_nego_emr'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.')) : '' ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section js-finance-fields" data-stage-scope="detail-edit" style="display:none;">
                        <div class="batch-form-section__title">Release Finance</div>
                        <div class="row">
                            <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" id="detail_edit_nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_release_finance'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_release_finance'], 0, ',', '.')) : '' ?>"></div></div>
                        </div>
                    </div>

                    <div class="batch-form-section">
                        <div class="batch-form-section__head">
                            <div>
                                <div class="batch-form-section__title mb-1">PIC Approval</div>
                                <p class="batch-form-section__subtitle mb-0">PIC 1 otomatis mengikuti data penerima dana, lalu bisa ditambah bila diperlukan.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="detail_edit_add_pic">Tambah PIC</button>
                        </div>
                        <div class="batch-pic-list" id="detail_edit_pic_rows">
                            <?php foreach ($initialPics as $picIndex => $pic): ?>
                                <?php $picNo = $picIndex + 1; ?>
                                <div class="batch-pic-card <?= $picNo === 1 ? 'batch-pic-card--primary' : '' ?>" data-pic-row="<?= $picNo ?>">
                                    <div class="batch-pic-card__head">
                                        <div>
                                            <div class="batch-pic-card__title">PIC <?= $picNo ?></div>
                                            <div class="batch-pic-card__note"><?= $picNo === 1 ? 'Otomatis mengikuti penerima dana' : 'PIC tambahan' ?></div>
                                        </div>
                                        <?php if ($picNo > 1): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_name'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_phone'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_position'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                        <div class="col-md-3"><div class="form-group mb-0"><label>Masa Jabatan PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period <?= $picNo === 1 ? 'js-primary-pic-field' : '' ?>" value="<?= htmlspecialchars((string) ($pic['pic_period'] ?? '')) ?>" <?= $picNo === 1 ? 'readonly' : '' ?>></div></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="batch-form-section batch-form-section--last">
                        <div class="batch-form-section__title">Remark</div>
                        <div class="form-group mb-0"><textarea name="remark_batch_approval" rows="3" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_batch_approval'] ?? '')) ?></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update Batch Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-doc-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">History Dokumen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Dokumen:</strong>
                    <span id="history_doc_label">-</span>
                </div>
                <ul class="doc-history-list" id="history_doc_items">
                    <li class="text-muted">Belum ada history.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-batch-rar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Dokumen RAR Batch Approval</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Status:</strong> <span id="batch_rar_status_label"><?= htmlspecialchars($batchDocumentStatus) ?></span></div>
                    <div class="mb-2"><strong>File saat ini:</strong> <span id="batch_rar_current_file">-</span></div>
                    <div id="batch_rar_upload_section">
                        <div class="form-group">
                            <div class="batch-dropzone js-dropzone">
                                <input type="file" name="file" class="js-dropzone-input">
                                <div class="batch-dropzone-content">
                                    <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="batch-dropzone-title">Drag & drop file RAR</div>
                                    <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                    <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"><input type="text" name="remark" id="batch_rar_remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                    </div>
                    <div class="alert alert-light border d-none" id="batch_rar_readonly_note">File sudah diproses. Upload baru hanya tersedia saat status `REJECTED` atau `BELUM UPLOAD`.</div>
                    <div class="form-group mb-0">
                        <a href="#" target="_blank" id="batch_rar_preview_link" class="btn btn-sm btn-outline-secondary d-none">Preview File Saat Ini</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="batch_rar_submit_btn" class="btn btn-primary btn-sm">Simpan RAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-myrep" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="WAITING MYREP">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Staging ke WAITING MYREP</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah jika telah input batch approval ke system ASTRI.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Staging Saat Ini</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars(batchDetailStatusLabel($displayStageStatus)) ?>" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Input ke Astri</label>
                        <input type="date" name="submitted_to_astri_at" class="form-control" value="<?= !empty($cluster['submitted_to_astri_at']) ? htmlspecialchars(substr((string) $cluster['submitted_to_astri_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Ubah ke WAITING MYREP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-finance" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="WAITING FINANCE">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Staging ke WAITING FINANCE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah saat ASTRI APPROVED dan akan dilakukan pengajuan ke finance.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nominal Pengajuan Donasi</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nomor Batch</label>
                        <input type="text" name="astri_batch_number" class="form-control" value="<?= htmlspecialchars((string) ($cluster['astri_batch_number'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal Approval dari MYREP</label>
                        <input type="text" name="nominal_nego_emr" class="form-control js-number-format" data-decimals="0" value="<?= htmlspecialchars((string) ($cluster['nominal_nego_emr'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Approved MYREP</label>
                        <input type="date" name="submitted_to_finance_at" class="form-control" value="<?= !empty($cluster['submitted_to_finance_at']) ? htmlspecialchars(substr((string) $cluster['submitted_to_finance_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Ubah ke WAITING FINANCE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-stage-to-released" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateStagingProgress') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                <input type="hidden" name="target_stage" value="RELEASED">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Edit Staging ke RELEASED</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="batch-stage-note">
                        Stagging dirubah saat sudah pencarian dari finance terkait donasi.
                    </div>
                    <div class="batch-stage-cluster-box">
                        <div class="batch-stage-cluster-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                            <div class="col-md-3"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nominal Pengajuan Donasi</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_pengajuan_area'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_pengajuan_area'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nominal Approval EMR</label>
                        <input type="text" class="form-control js-number-format" data-decimals="0" value="<?= !is_null($cluster['nominal_nego_emr'] ?? null) ? htmlspecialchars(number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.')) : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Pencairan</label>
                        <input type="date" name="released_at" class="form-control" value="<?= !empty($cluster['released_at']) ? htmlspecialchars(substr((string) $cluster['released_at'], 0, 10)) : date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal Cair</label>
                        <input type="text" name="nominal_release_finance" class="form-control js-number-format" data-decimals="0" value="<?= htmlspecialchars((string) ($cluster['nominal_release_finance'] ?? '')) ?>" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Foto Transfer</label>
                        <div class="batch-dropzone js-dropzone">
                            <input type="file" name="transfer_proof" class="js-dropzone-input" required>
                            <div class="batch-dropzone-content">
                                <div class="batch-dropzone-icon"><i class="fas fa-file-upload"></i></div>
                                <div class="batch-dropzone-title">Drag & drop foto transfer</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark">Ubah ke RELEASED</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-post-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_item" id="post_upload_doc_item_id">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Upload Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Dokumen:</strong> <span id="post_upload_doc_name">-</span></div>
                    <div class="mb-2"><strong>File saat ini:</strong> <span id="post_upload_file_name">-</span></div>
                    <div class="form-group">
                        <div class="batch-dropzone js-dropzone">
                            <input type="file" name="file" class="js-dropzone-input">
                            <div class="batch-dropzone-content">
                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="batch-dropzone-title">Drag & drop dokumen</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group"><input type="text" name="remark" id="post_upload_remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="post_upload_not_required" name="is_document_not_required" value="1">
                        <label class="form-check-label" for="post_upload_not_required">Tidak dibutuhkan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-batch-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="batch_approve_file_id">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Dokumen Batch</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>File:</strong> <span id="batch_approve_file_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Remark approve"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-batch-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Batch_Approval_MyRep/rejectDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="batch_reject_file_id">
                <input type="hidden" name="redirect_to_detail" value="1">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Dokumen Batch</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>File:</strong> <span id="batch_reject_file_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Alasan Reject</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Alasan reject" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-post-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/approveDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="post_approve_file_id">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Dokumen Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="post_approve_doc_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Remark approve"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-post-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content batch-modal">
            <form method="post" action="<?= base_url('Post_Donasi_MyRep/rejectDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="post_reject_file_id">
                <input type="hidden" name="redirect_to_batch_detail" value="1">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Dokumen Post Donasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="post_reject_doc_name">-</span></div>
                    <div class="form-group mb-0">
                        <label>Alasan Reject</label>
                        <textarea name="remark" class="form-control" rows="5" placeholder="Alasan reject" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var MAX_PIC_ROWS = 5;

        function bindDropzones() {
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

        function normalizeFormattedNumber(value, decimals) {
            var normalized = String(value || '').replace(/[^\d,.\-]/g, '');
            if (normalized === '') {
                return 0;
            }

            if (typeof decimals === 'number' && decimals === 0) {
                normalized = normalized.replace(/[.,]/g, '');
                var integerNumber = parseFloat(normalized);
                return isNaN(integerNumber) ? 0 : integerNumber;
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
            var number = typeof value === 'number' ? value : normalizeFormattedNumber(value, decimals);
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

        function updateNominalPerHomepass() {
            var hpDonasi = normalizeFormattedNumber($('#detail_edit_hp_donasi').val(), 0);
            var nominalDonasi = normalizeFormattedNumber($('#detail_edit_nominal_pengajuan_area').val(), 0);
            var result = hpDonasi > 0 ? (nominalDonasi / hpDonasi) : 0;
            $('#detail_edit_nominal_per_homepass').val(result > 0 ? formatNumberValue(result, 2) : '');
        }

        function toggleStageFields() {
            var stageValue = $('#detail_edit_staging_status').val() || 'WAITING HO';
            var showEmr = ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;
            var showFinance = ['WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;

            $('[data-stage-scope="detail-edit"].js-emr-fields').toggle(showEmr);
            $('[data-stage-scope="detail-edit"].js-finance-fields').toggle(showFinance);
        }

        function renumberPicRows() {
            $('#detail_edit_pic_rows .batch-pic-card').each(function (index) {
                var rowNumber = index + 1;
                $(this).attr('data-pic-row', rowNumber)
                    .toggleClass('batch-pic-card--primary', rowNumber === 1);
                $(this).find('.batch-pic-card__title').text('PIC ' + rowNumber);
                $(this).find('.batch-pic-card__note').text(rowNumber === 1 ? 'Otomatis mengikuti penerima dana' : 'PIC tambahan');
                $(this).find('.js-remove-pic-row').toggle(rowNumber > 1);
            });
        }

        function syncPrimaryPic() {
            var firstRow = $('#detail_edit_pic_rows .batch-pic-card').first();
            if (!firstRow.length) {
                return;
            }

            firstRow.find('.js-pic-name').val($('#detail_edit_recipient_name').val());
            firstRow.find('.js-pic-phone').val($('#detail_edit_recipient_phone').val());
            firstRow.find('.js-pic-position').val($('#detail_edit_recipient_position').val());
            firstRow.find('.js-pic-period').val($('#detail_edit_recipient_period').val());
        }

        function createPicRow(rowNumber) {
            return '' +
                '<div class="batch-pic-card" data-pic-row="' + rowNumber + '">' +
                    '<div class="batch-pic-card__head">' +
                        '<div>' +
                            '<div class="batch-pic-card__title">PIC ' + rowNumber + '</div>' +
                            '<div class="batch-pic-card__note">PIC tambahan</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position"></div></div>' +
                        '<div class="col-md-3"><div class="form-group mb-0"><label>Masa Jabatan PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period"></div></div>' +
                    '</div>' +
                '</div>';
        }

        $(function () {
            bindDropzones();

            $('.js-number-format').each(function () {
                applyNumberFormatting($(this));
            });

            updateNominalPerHomepass();
            toggleStageFields();
            syncPrimaryPic();
            renumberPicRows();

            $(document).on('input blur', '.js-number-format', function () {
                applyNumberFormatting($(this));
            });

            $('#detail_edit_hp_donasi, #detail_edit_nominal_pengajuan_area').on('input blur', function () {
                updateNominalPerHomepass();
            });

            $('#detail_edit_staging_status').on('change', function () {
                toggleStageFields();
            });

            $('#detail_edit_recipient_name, #detail_edit_recipient_phone, #detail_edit_recipient_position, #detail_edit_recipient_period').on('input', function () {
                syncPrimaryPic();
            });

            $('#detail_edit_add_pic').on('click', function () {
                var currentCount = $('#detail_edit_pic_rows .batch-pic-card').length;
                if (currentCount >= MAX_PIC_ROWS) {
                    return;
                }

                $('#detail_edit_pic_rows').append(createPicRow(currentCount + 1));
                renumberPicRows();
            });

            $(document).on('click', '.js-remove-pic-row', function () {
                $(this).closest('.batch-pic-card').remove();
                renumberPicRows();
            });

            $(document).on('click', '.js-doc-history', function () {
                var $button = $(this);
                var history = [];

                try {
                    history = $button.attr('data-history') ? JSON.parse($button.attr('data-history')) : [];
                } catch (e) {
                    history = [];
                }

                $('#history_doc_label').text($button.data('doc-name') || '-');

                if (!history.length) {
                    $('#history_doc_items').html('<li class="text-muted">Belum ada history.</li>');
                    return;
                }

                var html = '';
                history.forEach(function (entry) {
                    html += '<li class="doc-history-item">' +
                        '<div class="doc-history-title">' + (entry.action_type || '-') + '</div>' +
                        '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                        '<div><strong>File:</strong> ' + (entry.file_name || '-') + '</div>' +
                        '<div><strong>Remark:</strong> ' + (entry.remark || '-') + '</div>' +
                    '</li>';
                });

                $('#history_doc_items').html(html);
            });

            $(document).on('click', '.js-open-post-upload-modal', function () {
                var $button = $(this);
                $('#post_upload_doc_item_id').val($button.data('doc-item-id'));
                $('#post_upload_doc_name').text($button.data('doc-name') || '-');
                $('#post_upload_file_name').text($button.data('file-name') || '-');
                $('#post_upload_remark').val($button.data('remark') || '');
                $('#post_upload_not_required').prop('checked', false);
            });

            $(document).on('click', '.js-open-batch-rar-modal', function () {
                var $button = $(this);
                var path = $button.data('file-path') || '';
                var canUpload = String($button.data('can-upload')) === '1';

                $('#batch_rar_status_label').text($button.data('status-label') || '-');
                $('#batch_rar_current_file').text($button.data('file-name') || '-');
                $('#batch_rar_remark').val($button.data('remark') || '');
                $('#batch_rar_preview_link').toggleClass('d-none', !path).attr('href', path ? '<?= base_url() ?>' + path : '#');
                $('#batch_rar_upload_section').toggle(canUpload);
                $('#batch_rar_submit_btn').toggle(canUpload);
                $('#batch_rar_readonly_note').toggleClass('d-none', canUpload);
            });

            $(document).on('click', '.js-open-batch-review-modal', function () {
                var $button = $(this);
                $('#batch_approve_file_id, #batch_reject_file_id').val($button.data('file-id') || '');
                $('#batch_approve_file_name, #batch_reject_file_name').text($button.data('file-name') || '-');
            });

            $(document).on('click', '.js-open-post-review-modal', function () {
                var $button = $(this);
                $('#post_approve_file_id, #post_reject_file_id').val($button.data('file-id') || '');
                $('#post_approve_doc_name, #post_reject_doc_name').text($button.data('doc-name') || '-');
            });
        });
    })();
</script>
