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

$clusterTabRows = isset($scopeTabs['CLUSTER']) ? $scopeTabs['CLUSTER'] : [];
$subfeederTabRows = isset($scopeTabs['SUBFEEDER']) ? $scopeTabs['SUBFEEDER'] : [];
$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
?>

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
                <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
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
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>"><?= $item['status_file'] ?></span></td>
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
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url($item['file_path']) ?>" target="_blank" class="btn btn-sm btn-warning">View</a>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/approveDocument/' . (int) $item['id_doc_file'] . '/' . (int) $cluster['id_cluster']) ?>"
                                                                            class="btn btn-sm btn-primary">Approve</a>
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
                                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>"><?= $item['status_file'] ?></span></td>
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
                                                                    <?php if (!empty($item['file_path'])): ?>
                                                                        <a href="<?= base_url($item['file_path']) ?>" target="_blank" class="btn btn-sm btn-warning">View</a>
                                                                    <?php endif; ?>
                                                                    <?php if ($canApprove && (int) $item['id_doc_file'] > 0): ?>
                                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/approveDocument/' . (int) $item['id_doc_file'] . '/' . (int) $cluster['id_cluster']) ?>"
                                                                            class="btn btn-sm btn-primary">Approve</a>
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

<div class="modal fade" id="modalUploadDocument">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title">Upload Dokumen <span id="upload-doc-name"></span></h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="upload-cluster-id">
                    <input type="hidden" name="id_doc_package" id="upload-package-id">
                    <input type="hidden" name="id_doc_item" id="upload-item-id">
                    <input type="hidden" name="doc_name" id="upload-doc-name-input">
                    <div class="form-group">
                        <label>File</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Upload</button>
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
