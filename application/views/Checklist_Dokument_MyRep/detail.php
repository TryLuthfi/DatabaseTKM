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

$clusterTabRows = isset($scopeTabs['CLUSTER']) ? $scopeTabs['CLUSTER'] : [];
$subfeederTabRows = isset($scopeTabs['SUBFEEDER']) ? $scopeTabs['SUBFEEDER'] : [];
$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
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

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Dashboard Cluster</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Kota</strong>
                            <div><?= $cluster['city_name'] ?></div>
                        </div>
                        <div class="col-md-3">
                            <strong>Cluster</strong>
                            <div><?= $cluster['cluster_name'] ?></div>
                        </div>
                        <div class="col-md-2">
                            <strong>Homepass</strong>
                            <div><?= number_format((float) $cluster['homepass'], 0, ',', '.') ?></div>
                        </div>
                        <div class="col-md-2">
                            <strong>Status RFS</strong>
                            <div><span class="badge badge-success"><?= $cluster['status_rfs'] ?></span></div>
                        </div>
                        <div class="col-md-2">
                            <strong>RFS Date</strong>
                            <div><?= checklist_doc_detail_date($cluster['tanggal_rfs']) ?></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_cw_atp_uploaded'] ?>/<?= (int) $cluster['doc_cw_atp_required'] ?></h4>
                                    <p>Summary CW ATP</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_full_opm_uploaded'] ?>/<?= (int) $cluster['doc_full_opm_required'] ?></h4>
                                    <p>Summary Full OPM</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4><?= (int) $cluster['doc_rfs_uploaded'] ?>/<?= (int) $cluster['doc_rfs_required'] ?></h4>
                                    <p>Summary RFS</p>
                                </div>
                            </div>
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
                                                        <th>Approved At</th>
                                                        <th>Remark</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($group['items'])): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center">Belum ada master dokumen.</td>
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
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= checklist_doc_detail_date($item['uploaded_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['approved_at']) ?></td>
                                                                <td><?= $item['remark'] !== '' ? $item['remark'] : '-' ?></td>
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
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'REJECTED'], true)): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/approveDocument/' . (int) $item['id_doc_file'] . '/' . (int) $cluster['id_cluster']) ?>"
                                                                            class="btn btn-sm btn-primary">Approve</a>
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
                                                        <th>Approved At</th>
                                                        <th>Remark</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($group['items'])): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center">Belum ada master dokumen.</td>
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
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= checklist_doc_detail_date($item['uploaded_at']) ?></td>
                                                                <td><?= checklist_doc_detail_date($item['approved_at']) ?></td>
                                                                <td><?= $item['remark'] !== '' ? $item['remark'] : '-' ?></td>
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
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0 && in_array($item['status_file'], ['UPLOADED', 'REJECTED'], true)): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/approveDocument/' . (int) $item['id_doc_file'] . '/' . (int) $cluster['id_cluster']) ?>"
                                                                            class="btn btn-sm btn-primary">Approve</a>
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

<div class="modal fade" id="modalTimeline">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/saveTimeline') ?>">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Timeline <span id="timeline-group-label"></span></h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="timeline-cluster-id">
                    <input type="hidden" name="id_doc_package" id="timeline-package-id">
                    <div class="form-group">
                        <label>Tanggal RFS</label>
                        <input type="date" name="tanggal_rfs" id="timeline-tanggal-rfs" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Realisasi ATP</label>
                        <input type="date" name="actual_atp_date" id="timeline-actual-atp" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" id="timeline-remarks" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
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
                            <input type="file" name="file" class="form-control" required>
                            <small class="form-text text-muted">Format yang didukung: PDF, Word, Excel, JPG, JPEG, PNG.</small>
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

<script>
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
    });

    $(document).on('click', '.btn-reject-doc', function() {
        $('#reject-doc-name').text($(this).data('doc-name'));
        $('#reject-cluster-id').val($(this).data('cluster-id'));
        $('#reject-file-id').val($(this).data('file-id'));
        $('#reject-remark').val('');
    });
</script>
