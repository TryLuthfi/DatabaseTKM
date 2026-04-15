<?php
if (!function_exists('checklist_doc_detail_date')) {
    function checklist_doc_detail_date($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}

if (!function_exists('checklist_doc_status_badge')) {
    function checklist_doc_status_badge($status)
    {
        switch ($status) {
            case 'APPROVED':
            case 'DONE':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'ON REVIEW':
            case 'ON PROGRESS':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('checklist_doc_status_label')) {
    function checklist_doc_status_label($status)
    {
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status;
    }
}

if (!function_exists('checklist_doc_percent')) {
    function checklist_doc_percent($uploaded, $required)
    {
        $required = (int) $required;
        if ($required <= 0) {
            return 0;
        }

        return min(100, round((((int) $uploaded) / $required) * 100));
    }
}

if (!function_exists('checklist_doc_history_label')) {
    function checklist_doc_history_label($actionType)
    {
        switch ($actionType) {
            case 'UPLOADED':
                return 'Uploaded';
            case 'REUPLOADED':
                return 'Re-uploaded';
            case 'REJECTED':
                return 'Rejected';
            case 'APPROVED':
                return 'Approved';
            default:
                return $actionType;
        }
    }
}

$clusterTabRows = isset($scopeTabs['CLUSTER']) ? $scopeTabs['CLUSTER'] : [];
$subfeederTabRows = isset($scopeTabs['SUBFEEDER']) ? $scopeTabs['SUBFEEDER'] : [];
$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
$clusterProgressPercent = checklist_doc_percent(
    ((int) $cluster['doc_cw_atp_uploaded']) + ((int) $cluster['doc_full_opm_uploaded']) + ((int) $cluster['doc_rfs_uploaded']),
    ((int) $cluster['doc_cw_atp_required']) + ((int) $cluster['doc_full_opm_required']) + ((int) $cluster['doc_rfs_required'])
);
?>

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

    .doc-modal-bulk .modal-header {
        background: linear-gradient(135deg, #138496, #0ea5a8);
    }

    .doc-modal-upload .modal-header {
        background: linear-gradient(135deg, #198754, #34c38f);
    }

    .doc-modal-timeline .modal-header {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
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

    .doc-upload-highlight {
        background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        border: 1px dashed #61c093;
        border-radius: 14px;
        padding: 1rem;
    }

    .doc-upload-name {
        font-size: 1rem;
        font-weight: 700;
        color: #116149;
        margin-bottom: .25rem;
        word-break: break-word;
    }

    .doc-upload-note {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: 0;
    }

    .doc-bulk-table thead th {
        background: #eaf4f7;
        border-bottom: 0;
        color: #0f4c5c;
    }

    .doc-bulk-table tbody tr {
        background: #fff;
    }

    .doc-bulk-table td,
    .doc-bulk-table th {
        vertical-align: middle;
    }

    .doc-bulk-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: .25rem;
    }

    .doc-bulk-help {
        color: #6b7280;
        font-size: .82rem;
    }

    .doc-bulk-input {
        min-width: 220px;
    }

    .doc-progress-wrap {
        margin-top: .75rem;
    }

    .doc-progress-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .35rem;
        font-size: .88rem;
        color: #4b5563;
        font-weight: 600;
    }

    .doc-progress {
        width: 100%;
        height: 12px;
        background: #e9eef5;
        border-radius: 999px;
        overflow: hidden;
    }

    .doc-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #17a2b8, #28a745);
    }

    .doc-progress-bar.warning {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .doc-progress-bar.success {
        background: linear-gradient(90deg, #065f46, #10b981);
    }

    .doc-progress-summary-box {
        background: linear-gradient(135deg, #1f2937, #111827) !important;
        color: #fff !important;
    }

    .doc-progress-summary-box h4,
    .doc-progress-summary-box p,
    .doc-progress-summary-box .icon {
        color: #fff !important;
    }

    .doc-progress-summary-box .icon {
        opacity: .18;
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

    .doc-flag-chip {
        display: inline-flex;
        align-items: center;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        background: #dbeafe;
        color: #1d4ed8;
        margin-top: .35rem;
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

    .timeline-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .timeline-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }

    .timeline-summary-card {
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: .9rem 1rem;
    }

    .timeline-summary-label {
        color: #6b7280;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .35rem;
    }

    .timeline-summary-value {
        color: #1e3a8a;
        font-size: 1rem;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .timeline-grid,
        .timeline-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">DETAIL CHECKLIST DOKUMENT</h1>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php
            $this->session->unset_userdata('success');
            $this->session->unset_userdata('error');
            ?>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Dashboard Cluster</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Regional</strong>
                            <div><?= !empty($cluster['regional_name']) ? $cluster['regional_name'] : '-' ?></div>
                        </div>
                        <div class="col-md-2">
                            <strong>Provinsi</strong>
                            <div><?= !empty($cluster['province_name']) ? $cluster['province_name'] : '-' ?></div>
                        </div>
                        <div class="col-md-2">
                            <strong>Kota</strong>
                            <div><?= $cluster['city_name'] ?></div>
                        </div>
                        <div class="col-md-4">
                            <strong>Cluster</strong>
                            <div><?= $cluster['cluster_name'] ?></div>
                        </div>
                        <div class="col-md-2">
                            <strong>Homepass</strong>
                            <div><?= number_format((float) $cluster['homepass'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <strong>Status RFS</strong>
                            <div><span class="badge badge-success"><?= $cluster['status_rfs'] ?></span></div>
                        </div>
                        <div class="col-md-2">
                            <strong>RFS Date</strong>
                            <div><?= checklist_doc_detail_date($cluster['tanggal_rfs']) ?></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>RPM</strong>
                            <div><?= !empty($cluster['rpm']) ? $cluster['rpm'] : '-' ?></div>
                        </div>
                        <div class="col-md-4">
                            <strong>SM</strong>
                            <div><?= !empty($cluster['sm']) ? $cluster['sm'] : '-' ?></div>
                        </div>
                        <div class="col-md-4">
                            <strong>SPV</strong>
                            <div><?= !empty($cluster['spv']) ? $cluster['spv'] : '-' ?></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_cw_atp_uploaded'] ?>/<?= (int) $cluster['doc_cw_atp_required'] ?></h4>
                                    <p>Summary CW ATP</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $cluster['doc_cw_atp_uploaded'], (int) $cluster['doc_cw_atp_required']) >= 100 ? 'success' : 'warning' ?>"
                                                style="width: <?= checklist_doc_percent((int) $cluster['doc_cw_atp_uploaded'], (int) $cluster['doc_cw_atp_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_full_opm_uploaded'] ?>/<?= (int) $cluster['doc_full_opm_required'] ?></h4>
                                    <p>Summary Full OPM</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $cluster['doc_full_opm_uploaded'], (int) $cluster['doc_full_opm_required']) >= 100 ? 'success' : 'warning' ?>"
                                                style="width: <?= checklist_doc_percent((int) $cluster['doc_full_opm_uploaded'], (int) $cluster['doc_full_opm_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_rfs_uploaded'] ?>/<?= (int) $cluster['doc_rfs_required'] ?></h4>
                                    <p>Summary RFS</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $cluster['doc_rfs_uploaded'], (int) $cluster['doc_rfs_required']) >= 100 ? 'success' : 'warning' ?>"
                                                style="width: <?= checklist_doc_percent((int) $cluster['doc_rfs_uploaded'], (int) $cluster['doc_rfs_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="doc-modal-panel mt-3">
                        <div class="doc-progress-meta">
                            <span>Progress Checklist Cluster</span>
                            <span><?= $clusterProgressPercent ?>%</span>
                        </div>
                        <div class="doc-progress">
                            <div class="doc-progress-bar <?= $clusterProgressPercent >= 100 ? 'success' : 'warning' ?>" style="width: <?= $clusterProgressPercent ?>%"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4><?= checklist_doc_detail_date($cluster['plan_atp_date']) ?></h4>
                                    <p>Plan ATP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h4><?= checklist_doc_detail_date($cluster['actual_atp_date']) ?></h4>
                                    <p>Realisasi ATP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4><?= ($cluster['aging_atp_days'] === null) ? '-' : ((int) $cluster['aging_atp_days'] . ' H') ?></h4>
                                    <p>Aging ATP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4><?= checklist_doc_detail_date($cluster['plan_submit_doc_date']) ?></h4>
                                    <p>Plan Dokument</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h4><?= checklist_doc_detail_date($cluster['actual_submit_doc_date']) ?></h4>
                                    <p>Realisasi Dokument</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4><?= ($cluster['aging_doc_days'] === null) ? '-' : ((int) $cluster['aging_doc_days'] . ' H') ?></h4>
                                    <p>Aging Dokument</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-dark card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="pt-2 px-3">
                            <h3 class="card-title">DETAIL DOKUMENT</h3>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-cluster" role="tab">Cluster</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-subfeeder" role="tab">Subfeeder</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-cluster" role="tabpanel">
                            <?php foreach ($clusterTabRows as $group): ?>
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="card-title mb-0"><?= $group['group_label'] ?></h3>
                                            <div>
                                                <span class="badge badge-<?= checklist_doc_status_badge($group['status_package']) ?> mr-2">
                                                    <?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>
                                                </span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-edit-timeline"
                                                    data-toggle="modal"
                                                    data-target="#modalTimeline"
                                                    data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                    data-package-id="<?= (int) $group['id_doc_package'] ?>"
                                                    data-group-label="<?= $group['group_label'] ?>"
                                                    data-tanggal-rfs="<?= $group['tanggal_rfs'] ?>"
                                                    data-actual-atp-date="<?= $group['actual_atp_date'] ?>"
                                                    data-remarks="<?= htmlspecialchars($group['remarks'], ENT_QUOTES) ?>">
                                                    Edit Timeline
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    data-toggle="modal"
                                                    data-target="#modalBulkUpload-<?= (int) $group['id_doc_package'] ?>">
                                                    Bulk Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php $groupPercent = checklist_doc_percent((int) $group['uploaded_docs'], (int) $group['required_docs']); ?>
                                        <div class="doc-progress-wrap mb-3">
                                            <div class="doc-progress-meta">
                                                <span>Progress Grup</span>
                                                <span><?= $groupPercent ?>% (<?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>)</span>
                                            </div>
                                            <div class="doc-progress">
                                                <div class="doc-progress-bar <?= $groupPercent >= 100 ? 'success' : 'warning' ?>" style="width: <?= $groupPercent ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-2"><strong>RFS</strong><br><?= checklist_doc_detail_date($group['tanggal_rfs']) ?></div>
                                            <div class="col-md-2"><strong>Plan ATP</strong><br><?= checklist_doc_detail_date($group['plan_atp_date']) ?></div>
                                            <div class="col-md-2"><strong>Actual ATP</strong><br><?= checklist_doc_detail_date($group['actual_atp_date']) ?></div>
                                            <div class="col-md-2"><strong>Plan Doc</strong><br><?= checklist_doc_detail_date($group['plan_submit_doc_date']) ?></div>
                                            <div class="col-md-2"><strong>Actual Doc</strong><br><?= checklist_doc_detail_date($group['actual_submit_doc_date']) ?></div>
                                            <div class="col-md-2"><strong>Aging Doc</strong><br><?= ($group['aging_doc_days'] === null) ? '-' : ((int) $group['aging_doc_days'] . ' hari') ?></div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Dokumen</th>
                                <th>Status</th>
                                <th>File</th>
                                <th>Uploaded At</th>
                                <th>Reviewed At</th>
                                <th>Approved At</th>
                                <th>Submit Astri</th>
                                <th>Status Astri</th>
                                <th>Remark</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($group['items'])): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Belum ada master dokumen.</td>
                                </tr>
                            <?php else: ?>
                                                        <?php $no = 1; ?>
                                                        <?php foreach ($group['items'] as $item): ?>
                                                            <tr>
                                                                <td><?= $no++ ?></td>
                                                                <td><?= $item['doc_name'] ?></td>
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>"><?= checklist_doc_status_label($item['status_file']) ?></span></td>
                                                                <td>
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url($item['file_path']) ?>" target="_blank">
                                                                            <?= !empty($item['file_name']) ? $item['file_name'] : basename($item['file_path']) ?>
                                                                        </a>
                                                                    <?php elseif (!empty($item['is_document_not_required'])): ?>
                                                                        <span class="text-muted">Tanpa file</span>
                                                                        <div class="doc-flag-chip">Tidak dibutuhkan dokument</div>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= checklist_doc_detail_date($item['uploaded_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['reviewed_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['approved_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['astri_submitted_date']) ?></td>
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['astri_status']) ?>"><?= $item['astri_status'] !== '' ? $item['astri_status'] : 'NY' ?></span></td>
                                                                <td>
                                                                    <div><strong>Internal:</strong> <?= $item['remark'] !== '' ? $item['remark'] : '-' ?></div>
                                                                    <div><strong>ASTRI:</strong> <?= $item['astri_remark'] !== '' ? $item['astri_remark'] : '-' ?></div>
                                                                </td>
                                                                <td>
                                                                    <?php if (in_array($item['status_file'], ['NOT UPLOADED', 'REJECTED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-success btn-upload-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalUploadDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-package-id="<?= (int) $group['id_doc_package'] ?>"
                                                                            data-item-id="<?= (int) $item['id_doc_item'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Upload
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/previewDocument/' . (int) $item['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-warning">View</a>
                                                                    <?php endif; ?>
                                                                    <?php if ((int) $item['id_doc_file'] > 0): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-info btn-history-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalHistoryDocument"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                            data-history='<?= htmlspecialchars(json_encode($item["history"]), ENT_QUOTES, "UTF-8") ?>'>
                                                                            Detail
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && $item['status_file'] === 'APPROVED'): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-secondary btn-astri-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalAstriDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                            data-astri-submitted-date="<?= htmlspecialchars((string) $item['astri_submitted_date'], ENT_QUOTES) ?>"
                                                                            data-astri-status="<?= htmlspecialchars((string) $item['astri_status'], ENT_QUOTES) ?>"
                                                                            data-astri-remark="<?= htmlspecialchars((string) $item['astri_remark'], ENT_QUOTES) ?>">
                                                                            ASTRI
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'REJECTED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary btn-approve-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalApproveDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Approve
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'APPROVED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger btn-reject-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalRejectDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Reject
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade doc-modal doc-modal-bulk" id="modalBulkUpload-<?= (int) $group['id_doc_package'] ?>">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/bulkUploadDocuments') ?>" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <div>
                                                        <h4 class="modal-title mb-1">Bulk Upload <?= $group['group_label'] ?></h4>
                                                        <p class="mb-0" style="opacity:.9;">Upload beberapa file sekaligus untuk satu grup dokumen.</p>
                                                    </div>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_cluster'] ?>">
                                                    <input type="hidden" name="id_doc_package" value="<?= (int) $group['id_doc_package'] ?>">
                                                    <div class="doc-modal-panel">
                                                        <div class="doc-modal-title">Ringkasan Grup</div>
                                                        <p class="doc-modal-subtitle">
                                                            Progress saat ini <?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?> dokumen.
                                                            Isi hanya file yang ingin diupload atau diganti.
                                                        </p>
                                                    </div>
                                                    <div class="table-responsive doc-modal-panel mb-0">
                                                        <table class="table table-bordered doc-bulk-table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Dokumen</th>
                                                                    <th>Status Saat Ini</th>
                                                                    <th>Upload File</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $bulkNo = 1; ?>
                                                                <?php foreach ($group['items'] as $item): ?>
                                                                    <tr>
                                                                        <td><?= $bulkNo++ ?></td>
                                                                        <td>
                                                                            <div class="doc-bulk-name"><?= $item['doc_name'] ?></div>
                                                                            <div class="doc-bulk-help">Pilih file baru jika dokumen ini ingin diupload atau diperbarui.</div>
                                                                            <input type="hidden" name="id_doc_item[]" value="<?= (int) $item['id_doc_item'] ?>">
                                                                            <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>">
                                                                                <?= $item['status_file'] ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <input type="file" name="bulk_file_<?= (int) $item['id_doc_item'] ?>" class="form-control doc-bulk-input">
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-success">Upload Semua Yang Diisi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="tab-pane fade" id="tab-subfeeder" role="tabpanel">
                            <?php foreach ($subfeederTabRows as $group): ?>
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="card-title mb-0"><?= $group['group_label'] ?></h3>
                                            <div>
                                                <span class="badge badge-<?= checklist_doc_status_badge($group['status_package']) ?> mr-2">
                                                    <?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>
                                                </span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success btn-edit-timeline"
                                                    data-toggle="modal"
                                                    data-target="#modalTimeline"
                                                    data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                    data-package-id="<?= (int) $group['id_doc_package'] ?>"
                                                    data-group-label="<?= $group['group_label'] ?>"
                                                    data-tanggal-rfs="<?= $group['tanggal_rfs'] ?>"
                                                    data-actual-atp-date="<?= $group['actual_atp_date'] ?>"
                                                    data-remarks="<?= htmlspecialchars($group['remarks'], ENT_QUOTES) ?>">
                                                    Edit Timeline
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    data-toggle="modal"
                                                    data-target="#modalBulkUpload-<?= (int) $group['id_doc_package'] ?>">
                                                    Bulk Upload
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php $groupPercent = checklist_doc_percent((int) $group['uploaded_docs'], (int) $group['required_docs']); ?>
                                        <div class="doc-progress-wrap mb-3">
                                            <div class="doc-progress-meta">
                                                <span>Progress Grup</span>
                                                <span><?= $groupPercent ?>% (<?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>)</span>
                                            </div>
                                            <div class="doc-progress">
                                                <div class="doc-progress-bar <?= $groupPercent >= 100 ? 'success' : 'warning' ?>" style="width: <?= $groupPercent ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-2"><strong>RFS</strong><br><?= checklist_doc_detail_date($group['tanggal_rfs']) ?></div>
                                            <div class="col-md-2"><strong>Plan ATP</strong><br><?= checklist_doc_detail_date($group['plan_atp_date']) ?></div>
                                            <div class="col-md-2"><strong>Actual ATP</strong><br><?= checklist_doc_detail_date($group['actual_atp_date']) ?></div>
                                            <div class="col-md-2"><strong>Plan Doc</strong><br><?= checklist_doc_detail_date($group['plan_submit_doc_date']) ?></div>
                                            <div class="col-md-2"><strong>Actual Doc</strong><br><?= checklist_doc_detail_date($group['actual_submit_doc_date']) ?></div>
                                            <div class="col-md-2"><strong>Aging Doc</strong><br><?= ($group['aging_doc_days'] === null) ? '-' : ((int) $group['aging_doc_days'] . ' hari') ?></div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Dokumen</th>
                                <th>Status</th>
                                <th>File</th>
                                <th>Uploaded At</th>
                                <th>Reviewed At</th>
                                <th>Approved At</th>
                                <th>Submit Astri</th>
                                <th>Status Astri</th>
                                <th>Remark</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($group['items'])): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Belum ada master dokumen.</td>
                                </tr>
                            <?php else: ?>
                                                        <?php $no = 1; ?>
                                                        <?php foreach ($group['items'] as $item): ?>
                                                            <tr>
                                                                <td><?= $no++ ?></td>
                                                                <td><?= $item['doc_name'] ?></td>
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>"><?= checklist_doc_status_label($item['status_file']) ?></span></td>
                                                                <td>
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url($item['file_path']) ?>" target="_blank">
                                                                            <?= !empty($item['file_name']) ? $item['file_name'] : basename($item['file_path']) ?>
                                                                        </a>
                                                                    <?php elseif (!empty($item['is_document_not_required'])): ?>
                                                                        <span class="text-muted">Tanpa file</span>
                                                                        <div class="doc-flag-chip">Tidak dibutuhkan dokument</div>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= checklist_doc_detail_date($item['uploaded_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['reviewed_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['approved_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['astri_submitted_date']) ?></td>
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['astri_status']) ?>"><?= $item['astri_status'] !== '' ? $item['astri_status'] : 'NY' ?></span></td>
                                                                <td>
                                                                    <div><strong>Internal:</strong> <?= $item['remark'] !== '' ? $item['remark'] : '-' ?></div>
                                                                    <div><strong>ASTRI:</strong> <?= $item['astri_remark'] !== '' ? $item['astri_remark'] : '-' ?></div>
                                                                </td>
                                                                <td>
                                                                    <?php if (in_array($item['status_file'], ['NOT UPLOADED', 'REJECTED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-success btn-upload-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalUploadDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-package-id="<?= (int) $group['id_doc_package'] ?>"
                                                                            data-item-id="<?= (int) $item['id_doc_item'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Upload
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/previewDocument/' . (int) $item['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-warning">View</a>
                                                                    <?php endif; ?>
                                                                    <?php if ((int) $item['id_doc_file'] > 0): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-info btn-history-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalHistoryDocument"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                            data-history='<?= htmlspecialchars(json_encode($item["history"]), ENT_QUOTES, "UTF-8") ?>'>
                                                                            Detail
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && $item['status_file'] === 'APPROVED'): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-secondary btn-astri-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalAstriDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                            data-astri-submitted-date="<?= htmlspecialchars((string) $item['astri_submitted_date'], ENT_QUOTES) ?>"
                                                                            data-astri-status="<?= htmlspecialchars((string) $item['astri_status'], ENT_QUOTES) ?>"
                                                                            data-astri-remark="<?= htmlspecialchars((string) $item['astri_remark'], ENT_QUOTES) ?>">
                                                                            ASTRI
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'REJECTED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary btn-approve-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalApproveDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Approve
                                                                        </button>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'APPROVED'], true)): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger btn-reject-doc"
                                                                            data-toggle="modal"
                                                                            data-target="#modalRejectDocument"
                                                                            data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                                            data-file-id="<?= (int) $item['id_doc_file'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                            Reject
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade doc-modal doc-modal-bulk" id="modalBulkUpload-<?= (int) $group['id_doc_package'] ?>">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/bulkUploadDocuments') ?>" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <div>
                                                        <h4 class="modal-title mb-1">Bulk Upload <?= $group['group_label'] ?></h4>
                                                        <p class="mb-0" style="opacity:.9;">Upload beberapa file sekaligus untuk satu grup dokumen.</p>
                                                    </div>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_cluster'] ?>">
                                                    <input type="hidden" name="id_doc_package" value="<?= (int) $group['id_doc_package'] ?>">
                                                    <div class="doc-modal-panel">
                                                        <div class="doc-modal-title">Ringkasan Grup</div>
                                                        <p class="doc-modal-subtitle">
                                                            Progress saat ini <?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?> dokumen.
                                                            Isi hanya file yang ingin diupload atau diganti.
                                                        </p>
                                                    </div>
                                                    <div class="table-responsive doc-modal-panel mb-0">
                                                        <table class="table table-bordered doc-bulk-table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Dokumen</th>
                                                                    <th>Status Saat Ini</th>
                                                                    <th>Upload File</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $bulkNo = 1; ?>
                                                                <?php foreach ($group['items'] as $item): ?>
                                                                    <tr>
                                                                        <td><?= $bulkNo++ ?></td>
                                                                        <td>
                                                                            <div class="doc-bulk-name"><?= $item['doc_name'] ?></div>
                                                                            <div class="doc-bulk-help">Pilih file baru jika dokumen ini ingin diupload atau diperbarui.</div>
                                                                            <input type="hidden" name="id_doc_item[]" value="<?= (int) $item['id_doc_item'] ?>">
                                                                            <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>">
                                                                                <?= $item['status_file'] ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <input type="file" name="bulk_file_<?= (int) $item['id_doc_item'] ?>" class="form-control doc-bulk-input">
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-success">Upload Semua Yang Diisi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade doc-modal doc-modal-timeline" id="modalTimeline">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/saveTimeline') ?>">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title mb-1">Edit Timeline</h4>
                        <p class="mb-0" style="opacity:.9;" id="timeline-group-label"></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="timeline-cluster-id">
                    <input type="hidden" name="id_doc_package" id="timeline-package-id">
                    <div class="doc-modal-panel">
                        <div class="doc-modal-title">Ringkasan SLA</div>
                        <p class="doc-modal-subtitle">Isi tanggal real ATP bila sudah selesai. Plan dokumen akan dihitung otomatis sesuai rule SLA.</p>
                        <div class="timeline-summary mt-3">
                            <div class="timeline-summary-card">
                                <div class="timeline-summary-label">Rule 1</div>
                                <div class="timeline-summary-value">RFS + 7 Hari</div>
                            </div>
                            <div class="timeline-summary-card">
                                <div class="timeline-summary-label">Rule 2</div>
                                <div class="timeline-summary-value">ATP + 7 Hari</div>
                            </div>
                            <div class="timeline-summary-card">
                                <div class="timeline-summary-label">Output</div>
                                <div class="timeline-summary-value">Plan Dokument Auto</div>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-grid">
                        <div class="doc-modal-panel">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Tanggal RFS</label>
                                <input type="date" name="tanggal_rfs" id="timeline-tanggal-rfs" class="form-control">
                                <small class="form-text text-muted">Menjadi dasar perhitungan `Plan ATP`.</small>
                            </div>
                        </div>
                        <div class="doc-modal-panel">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Realisasi ATP</label>
                                <input type="date" name="actual_atp_date" id="timeline-actual-atp" class="form-control">
                                <small class="form-text text-muted">Jika kosong, sistem tetap menghitung target dokumen dari `Plan ATP`.</small>
                            </div>
                        </div>
                    </div>
                    <div class="doc-modal-panel">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remarks</label>
                            <textarea name="remarks" id="timeline-remarks" class="form-control" rows="3" placeholder="Tambahkan catatan timeline jika diperlukan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade doc-modal doc-modal-upload" id="modalUploadDocument">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title mb-1">Upload Dokumen</h4>
                        <p class="mb-0" style="opacity:.9;">Pilih satu file untuk item checklist yang sedang aktif.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="upload-cluster-id">
                    <input type="hidden" name="id_doc_package" id="upload-package-id">
                    <input type="hidden" name="id_doc_item" id="upload-item-id">
                    <input type="hidden" name="doc_name" id="upload-doc-name-input">
                    <div class="doc-upload-highlight mb-3">
                        <div class="doc-upload-name" id="upload-doc-name"></div>
                        <p class="doc-upload-note">File yang diupload akan masuk ke dokumen ini dan menggantikan file sebelumnya jika sudah ada.</p>
                    </div>
                    <div class="doc-modal-panel">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Pilih File</label>
                            <div class="upload-dropzone" id="upload-dropzone">
                                <input type="file" name="file" id="upload-file-input" required>
                                <div class="upload-dropzone-content">
                                    <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                    <div class="upload-dropzone-text">atau klik area ini untuk memilih file dari perangkat</div>
                                    <div class="upload-dropzone-file" id="upload-file-name">Belum ada file dipilih</div>
                                </div>
                            </div>
                            <small class="form-text text-muted">Format yang didukung: PDF, Word, Excel, JPG, JPEG, PNG.</small>
                        </div>
                    </div>
                    <div class="doc-modal-panel">
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is-document-not-required" name="is_document_not_required" value="1">
                                <label class="custom-control-label font-weight-bold" for="is-document-not-required">
                                    Tidak dibutuhkan dokument
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika dicentang, item tetap dihitung submitted dan tetap melalui proses reject/approve HO.
                            </small>
                        </div>
                    </div>
                    <div class="doc-modal-panel">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remark</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="Tambahkan catatan singkat jika diperlukan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">Upload Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRejectDocument">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/rejectDocument') ?>">
                <div class="modal-header">
                    <h4 class="modal-title">Reject Dokumen <span id="reject-doc-name"></span></h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="reject-cluster-id">
                    <input type="hidden" name="id_doc_file" id="reject-file-id">
                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" id="reject-remark" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade doc-modal" id="modalHistoryDocument">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div>
                    <h4 class="modal-title mb-1">History Dokumen</h4>
                    <p class="mb-0" style="opacity:.9;">Riwayat upload, reject, dan approve untuk dokumen terpilih.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="doc-modal-panel">
                    <div class="doc-modal-title" id="history-doc-title">-</div>
                    <p class="doc-modal-subtitle">File lama otomatis dihapus dari storage. History hanya menyimpan jejak prosesnya.</p>
                </div>
                <div class="doc-modal-panel mb-0">
                    <ul class="doc-history-list" id="history-doc-list">
                        <li class="text-muted">Belum ada history.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade doc-modal" id="modalApproveDocument">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/approveDocument') ?>">
                <div class="modal-header" style="background: linear-gradient(135deg, #15803d, #16a34a);">
                    <div>
                        <h4 class="modal-title mb-1">Approve Dokumen</h4>
                        <p class="mb-0" style="opacity:.9;" id="approve-doc-name"></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="approve-cluster-id">
                    <input type="hidden" name="id_doc_file" id="approve-file-id">
                    <div class="doc-modal-panel">
                        <div class="doc-modal-title">Konfirmasi Approval</div>
                        <p class="doc-modal-subtitle">Remarks bersifat opsional. Bisa diisi jika ingin memberi catatan saat approve.</p>
                    </div>
                    <div class="doc-modal-panel mb-0">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remarks</label>
                            <textarea name="remark" id="approve-remark" class="form-control" rows="3" placeholder="Isi remarks approval jika diperlukan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">Approve Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade doc-modal" id="modalAstriDocument">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/saveAstriStatus') ?>">
                <div class="modal-header" style="background: linear-gradient(135deg, #374151, #111827);">
                    <div>
                        <h4 class="modal-title mb-1">Update Status ASTRI</h4>
                        <p class="mb-0" style="opacity:.9;" id="astri-doc-name"></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="astri-cluster-id">
                    <input type="hidden" name="id_doc_file" id="astri-file-id">
                    <div class="doc-modal-panel">
                        <div class="doc-modal-title">Sinkronisasi Submit ke ASTRI</div>
                        <p class="doc-modal-subtitle">Isi tanggal submit saat dokumen sudah dikirim ke ASTRI, lalu update status sesuai review di sana.</p>
                    </div>
                    <div class="timeline-grid">
                        <div class="doc-modal-panel">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Tanggal Submit ASTRI</label>
                                <input type="date" name="astri_submitted_date" id="astri-submitted-date" class="form-control">
                                <small class="form-text text-muted">Wajib diisi jika status ASTRI bukan `NY`.</small>
                            </div>
                        </div>
                        <div class="doc-modal-panel">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Status ASTRI</label>
                                <select name="astri_status" id="astri-status" class="form-control">
                                    <option value="NY">NY</option>
                                    <option value="ON REVIEW">ON REVIEW</option>
                                    <option value="REJECTED">REJECTED</option>
                                    <option value="APPROVED">APPROVED</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="doc-modal-panel mb-0">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remark ASTRI</label>
                            <textarea name="astri_remark" id="astri-remark" class="form-control" rows="3" placeholder="Catatan submit / review ASTRI jika diperlukan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark">Simpan ASTRI</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bindDropzone(dropzoneSelector, inputSelector, labelSelector) {
        var dropzone = document.querySelector(dropzoneSelector);
        var input = document.querySelector(inputSelector);
        var label = document.querySelector(labelSelector);

        if (!dropzone || !input || !label) {
            return;
        }

        ['dragenter', 'dragover'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function(eventName) {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                label.textContent = e.dataTransfer.files[0].name;
            }
        });

        input.addEventListener('change', function() {
            label.textContent = (input.files && input.files.length > 0) ? input.files[0].name : 'Belum ada file dipilih';
        });
    }

    $(document).on('click', '.btn-edit-timeline', function() {
        $('#timeline-group-label').text($(this).data('group-label'));
        $('#timeline-cluster-id').val($(this).data('cluster-id'));
        $('#timeline-package-id').val($(this).data('package-id'));
        $('#timeline-tanggal-rfs').val($(this).data('tanggal-rfs'));
        $('#timeline-actual-atp').val($(this).data('actual-atp-date'));
        $('#timeline-remarks').val($(this).data('remarks'));
    });

    $(document).on('click', '.btn-upload-doc', function() {
        $('#upload-doc-name').text($(this).data('doc-name'));
        $('#upload-cluster-id').val($(this).data('cluster-id'));
        $('#upload-package-id').val($(this).data('package-id'));
        $('#upload-item-id').val($(this).data('item-id'));
        $('#upload-doc-name-input').val($(this).data('doc-name'));
        $('#upload-file-name').text('Belum ada file dipilih');
        $('#upload-file-input').val('');
        $('#is-document-not-required').prop('checked', false);
        $('#upload-file-input').prop('disabled', false).prop('required', true);
    });

    $(document).on('click', '.btn-reject-doc', function() {
        $('#reject-doc-name').text($(this).data('doc-name'));
        $('#reject-cluster-id').val($(this).data('cluster-id'));
        $('#reject-file-id').val($(this).data('file-id'));
        $('#reject-remark').val('');
    });

    $(document).on('click', '.btn-approve-doc', function() {
        $('#approve-doc-name').text($(this).data('doc-name'));
        $('#approve-cluster-id').val($(this).data('cluster-id'));
        $('#approve-file-id').val($(this).data('file-id'));
        $('#approve-remark').val('');
    });

    $(document).on('click', '.btn-astri-doc', function() {
        var astriStatus = $(this).data('astri-status') || 'NY';
        $('#astri-doc-name').text($(this).data('doc-name'));
        $('#astri-cluster-id').val($(this).data('cluster-id'));
        $('#astri-file-id').val($(this).data('file-id'));
        $('#astri-submitted-date').val($(this).data('astri-submitted-date'));
        $('#astri-status').val(astriStatus);
        $('#astri-remark').val($(this).data('astri-remark') || '');
        $('#astri-submitted-date').prop('required', astriStatus !== 'NY');
    });

    $(document).on('click', '.btn-history-doc', function() {
        var docName = $(this).data('doc-name');
        var rawHistory = $(this).attr('data-history');
        var history = [];

        try {
            history = rawHistory ? JSON.parse(rawHistory) : [];
        } catch (e) {
            history = [];
        }

        $('#history-doc-title').text(docName);

        if (!history.length) {
            $('#history-doc-list').html('<li class="text-muted">Belum ada history.</li>');
            return;
        }

        var html = '';
        history.forEach(function(entry) {
            var actionLabel = entry.action_type || '-';
            if (actionLabel === 'UPLOADED') actionLabel = 'Uploaded';
            if (actionLabel === 'REUPLOADED') actionLabel = 'Re-uploaded';
            if (actionLabel === 'REJECTED') actionLabel = 'Rejected';
            if (actionLabel === 'APPROVED') actionLabel = 'Approved';

            var actionAt = entry.action_at || '-';
            var actor = entry.nama_user || 'System';
            var fileName = entry.file_name || '-';
            var remark = entry.remark ? entry.remark : '-';

            html += '<li class="doc-history-item">' +
                '<span class="doc-history-dot"></span>' +
                '<div class="doc-history-title">' + actionLabel + '</div>' +
                '<div class="doc-history-meta">' + actionAt + ' | ' + actor + '</div>' +
                '<p class="doc-history-note"><strong>File:</strong> ' + fileName + '</p>' +
                '<p class="doc-history-note"><strong>Remark:</strong> ' + remark + '</p>' +
                '</li>';
        });

        $('#history-doc-list').html(html);
    });

    $(document).on('change', '#is-document-not-required', function() {
        var checked = $(this).is(':checked');
        $('#upload-file-input').prop('disabled', checked);
        $('#upload-file-input').prop('required', !checked);
        if (checked) {
            $('#upload-file-input').val('');
            $('#upload-file-name').text('File tidak diperlukan untuk item ini');
        } else {
            $('#upload-file-name').text('Belum ada file dipilih');
        }
    });

    $(document).on('change', '#astri-status', function() {
        var isNy = $(this).val() === 'NY';
        $('#astri-submitted-date').prop('required', !isNy);
        if (isNy) {
            $('#astri-submitted-date').val('');
        }
    });

    bindDropzone('#upload-dropzone', '#upload-file-input', '#upload-file-name');
</script>
