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

if (!function_exists('checklist_doc_status_label')) {
    function checklist_doc_status_label($status)
    {
        return $status === 'UPLOADED' ? 'ON REVIEW' : $status;
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

if (!function_exists('checklist_doc_input_date')) {
    function checklist_doc_input_date($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '';
        }

        return substr((string) $date, 0, 10);
    }
}

if (!function_exists('checklist_doc_can_show_mf_astri_action')) {
    function checklist_doc_can_show_mf_astri_action(array $item, $canTambah)
    {
        return (bool) $canTambah
            && (int) ($item['id_doc_file_mainfeeder'] ?? 0) > 0
            && (string) ($item['status_file'] ?? '') === 'APPROVED';
    }
}

if (!function_exists('checklist_doc_render_mf_bulk_astri_modal')) {
    function checklist_doc_render_mf_bulk_astri_modal(array $mainfeeder, array $group, $canTambah)
    {
        $eligibleItems = [];
        foreach (($group['items'] ?? []) as $item) {
            if (checklist_doc_can_show_mf_astri_action($item, $canTambah)) {
                $eligibleItems[] = $item;
            }
        }
        ?>
        <div class="modal fade" id="modalBulkAstriMainfeeder-<?= (int) $group['id_doc_package_mainfeeder'] ?>">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/bulkEditMainfeederAstriStatus') ?>" class="mf-ajax-action-form">
                        <div class="modal-header bg-dark">
                            <h4 class="modal-title">Bulk ASTRI <?= htmlspecialchars((string) $group['group_label'], ENT_QUOTES) ?></h4>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="mainfeeder_id" value="<?= (int) $mainfeeder['id_mainfeeder'] ?>">
                            <?php if (empty($eligibleItems)): ?>
                                <div class="text-muted">Belum ada dokumen yang bisa diupdate ASTRI.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Dokumen</th>
                                                <th>Tanggal Submit ASTRI</th>
                                                <th>Status ASTRI</th>
                                                <th>Remark ASTRI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $bulkAstriNo = 1; foreach ($eligibleItems as $item): ?>
                                                <?php
                                                $fileId = (int) $item['id_doc_file_mainfeeder'];
                                                $currentAstriStatus = strtoupper(trim((string) ($item['astri_status'] ?? 'NY'))) ?: 'NY';
                                                $dateInputId = 'mf-bulk-astri-date-' . (int) $group['id_doc_package_mainfeeder'] . '-' . $fileId;
                                                ?>
                                                <tr>
                                                    <td><?= $bulkAstriNo++ ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) $item['doc_name'], ENT_QUOTES) ?></strong>
                                                        <input type="hidden" name="id_doc_file_mainfeeder[]" value="<?= $fileId ?>">
                                                    </td>
                                                    <td>
                                                        <input type="date" name="astri_submitted_date[<?= $fileId ?>]" id="<?= $dateInputId ?>" class="form-control" value="<?= htmlspecialchars(checklist_doc_input_date($item['astri_submitted_date'] ?? ''), ENT_QUOTES) ?>" <?= $currentAstriStatus !== 'NY' ? 'required' : '' ?>>
                                                    </td>
                                                    <td>
                                                        <select name="astri_status[<?= $fileId ?>]" class="form-control bulk-astri-status" data-date-input="#<?= $dateInputId ?>">
                                                            <?php foreach (['NY', 'ON REVIEW', 'REJECTED', 'APPROVED'] as $option): ?>
                                                                <option value="<?= $option ?>" <?= $currentAstriStatus === $option ? 'selected' : '' ?>><?= $option ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><textarea name="astri_remark[<?= $fileId ?>]" class="form-control" rows="2"><?= htmlspecialchars((string) ($item['astri_remark'] ?? ''), ENT_QUOTES) ?></textarea></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-dark" <?= empty($eligibleItems) ? 'disabled' : '' ?>>Simpan Bulk ASTRI</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Checklist_Dokument_MyRep', 'TAMBAH') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Checklist_Dokument_MyRep', 'APPROVAL') : true;
$mainfeederProgressPercent = checklist_doc_percent(
    ((int) $mainfeeder['doc_cw_atp_uploaded']) + ((int) $mainfeeder['doc_full_opm_uploaded']) + ((int) $mainfeeder['doc_rfs_uploaded']),
    ((int) $mainfeeder['doc_cw_atp_required']) + ((int) $mainfeeder['doc_full_opm_required']) + ((int) $mainfeeder['doc_rfs_required'])
);
?>

<style>
    .doc-progress-wrap { margin-top: .75rem; }
    .doc-progress { width: 100%; height: 12px; background: #e9eef5; border-radius: 999px; overflow: hidden; }
    .doc-progress-bar { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #17a2b8, #28a745); }
    .doc-progress-bar.warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .doc-progress-bar.success { background: linear-gradient(90deg, #065f46, #10b981); }
    .doc-progress-summary-box { background: linear-gradient(135deg, #1f2937, #111827) !important; color: #fff !important; }
    .doc-progress-summary-box h4, .doc-progress-summary-box p { color: #fff !important; }
    .upload-progress-panel { display: none; background: linear-gradient(135deg, #eff6ff, #f8fbff); border: 1px solid #dbeafe; border-radius: 14px; padding: 1rem; margin-top: 1rem; }
    .upload-progress-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; font-weight: 700; color: #1e3a8a; }
    .bulk-upload-progress-panel { display: none; margin-top: .9rem; }
    .doc-history-list { margin: 0; padding: 0; list-style: none; }
    .doc-history-item { border-left: 3px solid #dbeafe; padding: 0 0 1rem 1rem; margin-left: .35rem; }
    .doc-history-item:last-child { padding-bottom: 0; }
    .doc-history-title { font-weight: 700; color: #1f2937; }
    .doc-history-meta { color: #6b7280; font-size: .86rem; margin-bottom: .25rem; }
    .doc-history-note { color: #374151; font-size: .9rem; margin-bottom: 0; }
    .doc-flag-chip { display: inline-block; margin-top: .35rem; padding: .15rem .45rem; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: .78rem; font-weight: 700; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">DETAIL CHECKLIST MAINFEEDER</h1>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="mb-3">
                <div class="btn-group">
                    <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-outline-dark">Monitoring Cluster</a>
                    <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-dark">Monitoring Mainfeeder</a>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div id="mf-checklist-live-content">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Dashboard Mainfeeder</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Regional</strong><div><?= $mainfeeder['regional_name'] ?></div></div>
                        <div class="col-md-3"><strong>Provinsi</strong><div><?= $mainfeeder['province_name'] ?></div></div>
                        <div class="col-md-3"><strong>Kota</strong><div><?= $mainfeeder['city_name'] ?></div></div>
                        <div class="col-md-3"><strong>Target ID</strong><div><?= (int) $mainfeeder['id_target'] ?></div></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-5"><strong>Nama Mainfeeder</strong><div><?= $mainfeeder['mainfeeder_name'] ?></div></div>
                        <div class="col-md-3"><strong>Panjang</strong><div><?= number_format((float) $mainfeeder['length_meter'], 0, ',', '.') ?> m</div></div>
                        <div class="col-md-2"><strong>Tanggal ATP</strong><div><?= checklist_doc_detail_date($mainfeeder['atp_date']) ?></div></div>
                        <div class="col-md-2"><strong>Plan Dokument</strong><div><?= checklist_doc_detail_date($mainfeeder['plan_submit_doc_date']) ?></div></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $mainfeeder['doc_cw_atp_uploaded'] ?>/<?= (int) $mainfeeder['doc_cw_atp_required'] ?></h4>
                                    <p>Summary CW ATP</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $mainfeeder['doc_cw_atp_uploaded'], (int) $mainfeeder['doc_cw_atp_required']) >= 100 ? 'success' : 'warning' ?>" style="width: <?= checklist_doc_percent((int) $mainfeeder['doc_cw_atp_uploaded'], (int) $mainfeeder['doc_cw_atp_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $mainfeeder['doc_full_opm_uploaded'] ?>/<?= (int) $mainfeeder['doc_full_opm_required'] ?></h4>
                                    <p>Summary Full OPM</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $mainfeeder['doc_full_opm_uploaded'], (int) $mainfeeder['doc_full_opm_required']) >= 100 ? 'success' : 'warning' ?>" style="width: <?= checklist_doc_percent((int) $mainfeeder['doc_full_opm_uploaded'], (int) $mainfeeder['doc_full_opm_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box doc-progress-summary-box">
                                <div class="inner">
                                    <h4><?= (int) $mainfeeder['doc_rfs_uploaded'] ?>/<?= (int) $mainfeeder['doc_rfs_required'] ?></h4>
                                    <p>Summary RFS</p>
                                    <div class="doc-progress-wrap">
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar <?= checklist_doc_percent((int) $mainfeeder['doc_rfs_uploaded'], (int) $mainfeeder['doc_rfs_required']) >= 100 ? 'success' : 'warning' ?>" style="width: <?= checklist_doc_percent((int) $mainfeeder['doc_rfs_uploaded'], (int) $mainfeeder['doc_rfs_required']) ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body mt-2">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Progress Checklist Mainfeeder</strong>
                            <strong><?= $mainfeederProgressPercent ?>%</strong>
                        </div>
                        <div class="doc-progress">
                            <div class="doc-progress-bar <?= $mainfeederProgressPercent >= 100 ? 'success' : 'warning' ?>" style="width: <?= $mainfeederProgressPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($groupRows as $group): ?>
                <?php
                $groupHasApprovalItems = false;
                $groupHasAstriItems = false;
                foreach (($group['items'] ?? []) as $groupItem) {
                    $groupFileId = (int) ($groupItem['id_doc_file_mainfeeder'] ?? 0);
                    $groupStatus = (string) ($groupItem['status_file'] ?? '');
                    if ($groupFileId > 0 && in_array($groupStatus, ['UPLOADED', 'REJECTED'], true)) {
                        $groupHasApprovalItems = true;
                    }
                    if (checklist_doc_can_show_mf_astri_action($groupItem, $canTambah)) {
                        $groupHasAstriItems = true;
                    }
                }
                $groupPercent = checklist_doc_percent((int) $group['uploaded_docs'], (int) $group['required_docs']);
                ?>
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><?= $group['group_label'] ?></h3>
                            <div>
                                <span class="badge badge-<?= checklist_doc_status_badge($groupPercent >= 100 ? 'APPROVED' : 'UPLOADED') ?> mr-2">
                                    <?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>
                                </span>
                                <?php if ($canApprove && $canApprovalAction && $groupHasApprovalItems): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-mf-approve-all"
                                        data-mainfeeder-id="<?= (int) $mainfeeder['id_mainfeeder'] ?>"
                                        data-package-id="<?= (int) $group['id_doc_package_mainfeeder'] ?>">
                                        <i class="fas fa-check-double mr-1"></i>Approve All
                                    </button>
                                <?php endif; ?>
                                <?php if ($canTambah): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#modalBulkUploadMainfeeder-<?= (int) $group['id_doc_package_mainfeeder'] ?>">
                                        Bulk Upload
                                    </button>
                                <?php endif; ?>
                                <?php if ($groupHasAstriItems): ?>
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#modalBulkAstriMainfeeder-<?= (int) $group['id_doc_package_mainfeeder'] ?>">
                                        Bulk ASTRI
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="doc-progress-wrap mb-3">
                            <div class="upload-progress-meta">
                                <span>Progress Grup</span>
                                <span><?= $groupPercent ?>% (<?= $group['uploaded_docs'] ?>/<?= $group['required_docs'] ?>)</span>
                            </div>
                            <div class="doc-progress">
                                <div class="doc-progress-bar <?= $groupPercent >= 100 ? 'success' : 'warning' ?>" style="width: <?= $groupPercent ?>%"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>ATP</strong><br><?= checklist_doc_detail_date($group['atp_date']) ?></div>
                            <div class="col-md-3"><strong>Plan Doc</strong><br><?= checklist_doc_detail_date($group['plan_submit_doc_date']) ?></div>
                            <div class="col-md-3"><strong>Actual Doc</strong><br><?= checklist_doc_detail_date($group['actual_submit_doc_date']) ?></div>
                            <div class="col-md-3"><strong>Aging Doc</strong><br><?= $group['aging_doc_days'] === null ? '-' : ((int) $group['aging_doc_days'] . ' hari') ?></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Verification By</th>
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
                                        <tr><td colspan="12" class="text-center">Belum ada master dokumen.</td></tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($group['items'] as $item): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $item['doc_name'] ?></td>
                                                <td><?= !empty($item['verification_by']) ? $item['verification_by'] : '-' ?></td>
                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['status_file']) ?>"><?= checklist_doc_status_label($item['status_file']) ?></span></td>
                                                <td>
                                                    <?php if (!empty($item['file_path'])): ?>
                                                        <a href="<?= base_url('Checklist_Dokument_MyRep/previewMainfeederDocument/' . (int) $item['id_doc_file_mainfeeder']) ?>" target="_blank"><?= $item['file_name'] ?></a>
                                                        <div class="mt-1">
                                                            <a href="<?= base_url('Checklist_Dokument_MyRep/downloadMainfeederDocument/' . (int) $item['id_doc_file_mainfeeder']) ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-info btn-mf-history"
                                                                data-toggle="modal"
                                                                data-target="#modalHistoryMainfeederDocument"
                                                                data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                data-file-id="<?= (int) $item['id_doc_file_mainfeeder'] ?>">
                                                                <i class="fas fa-history"></i> History
                                                            </button>
                                                        </div>
                                                    <?php elseif (!empty($item['is_document_not_required'])): ?>
                                                        <span class="text-muted">Tanpa file</span>
                                                        <div class="doc-flag-chip">Tidak dibutuhkan dokumen</div>
                                                        <?php if ((int) $item['id_doc_file_mainfeeder'] > 0): ?>
                                                            <div class="mt-1">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-info btn-mf-history"
                                                                    data-toggle="modal"
                                                                    data-target="#modalHistoryMainfeederDocument"
                                                                    data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                                    data-file-id="<?= (int) $item['id_doc_file_mainfeeder'] ?>">
                                                                    <i class="fas fa-history"></i> History
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= checklist_doc_detail_date($item['uploaded_at']) ?></td>
                                                <td><?= checklist_doc_detail_date($item['reviewed_at']) ?></td>
                                                <td><?= checklist_doc_detail_date($item['approved_at']) ?></td>
                                                <td><?= checklist_doc_detail_date($item['astri_submitted_date']) ?></td>
                                                <td><span class="badge badge-<?= checklist_doc_status_badge($item['astri_status']) ?>"><?= $item['astri_status'] ?></span></td>
                                                <td>
                                                    <div><strong>Internal:</strong> <?= $item['remark'] !== '' ? $item['remark'] : '-' ?></div>
                                                    <div><strong>ASTRI:</strong> <?= $item['astri_remark'] !== '' ? $item['astri_remark'] : '-' ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($canTambah && in_array($item['status_file'], ['NOT UPLOADED', 'REJECTED'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-success btn-mf-upload"
                                                            data-toggle="modal" data-target="#modalUploadMainfeeder"
                                                            data-mainfeeder-id="<?= (int) $mainfeeder['id_mainfeeder'] ?>"
                                                            data-package-id="<?= (int) $group['id_doc_package_mainfeeder'] ?>"
                                                            data-item-id="<?= (int) $item['id_doc_item_mainfeeder'] ?>"
                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">Upload</button>
                                                    <?php endif; ?>
                                                    <?php if ($canApprove && $canApprovalAction && (int) $item['id_doc_file_mainfeeder'] > 0 && in_array($item['status_file'], ['UPLOADED', 'REJECTED'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-primary btn-mf-approve"
                                                            data-toggle="modal" data-target="#modalApproveMainfeeder"
                                                            data-mainfeeder-id="<?= (int) $mainfeeder['id_mainfeeder'] ?>"
                                                            data-file-id="<?= (int) $item['id_doc_file_mainfeeder'] ?>"
                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">Approve</button>
                                                    <?php endif; ?>
                                                    <?php if ($canApprove && $canApprovalAction && (int) $item['id_doc_file_mainfeeder'] > 0 && in_array($item['status_file'], ['UPLOADED', 'APPROVED'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-danger btn-mf-reject"
                                                            data-toggle="modal" data-target="#modalRejectMainfeeder"
                                                            data-mainfeeder-id="<?= (int) $mainfeeder['id_mainfeeder'] ?>"
                                                            data-file-id="<?= (int) $item['id_doc_file_mainfeeder'] ?>"
                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">Reject</button>
                                                    <?php endif; ?>
                                                    <?php if ($canTambah && (int) $item['id_doc_file_mainfeeder'] > 0 && $item['status_file'] === 'APPROVED'): ?>
                                                        <button type="button" class="btn btn-sm btn-secondary btn-mf-astri"
                                                            data-toggle="modal" data-target="#modalAstriMainfeeder"
                                                            data-mainfeeder-id="<?= (int) $mainfeeder['id_mainfeeder'] ?>"
                                                            data-file-id="<?= (int) $item['id_doc_file_mainfeeder'] ?>"
                                                            data-doc-name="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>"
                                                            data-astri-status="<?= htmlspecialchars($item['astri_status'], ENT_QUOTES) ?>"
                                                            data-astri-submitted-date="<?= htmlspecialchars((string) $item['astri_submitted_date'], ENT_QUOTES) ?>"
                                                            data-astri-remark="<?= htmlspecialchars((string) $item['astri_remark'], ENT_QUOTES) ?>">ASTRI</button>
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
                <?php checklist_doc_render_mf_bulk_astri_modal($mainfeeder, $group, $canTambah); ?>
                <div class="modal fade" id="modalBulkUploadMainfeeder-<?= (int) $group['id_doc_package_mainfeeder'] ?>">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/bulkUploadMainfeederDocuments') ?>" enctype="multipart/form-data" class="mf-bulk-upload-form">
                                <div class="modal-header bg-success">
                                    <h4 class="modal-title">Bulk Upload <?= $group['group_label'] ?></h4>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="mainfeeder_id" value="<?= (int) $mainfeeder['id_mainfeeder'] ?>">
                                    <input type="hidden" name="id_doc_package_mainfeeder" value="<?= (int) $group['id_doc_package_mainfeeder'] ?>">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Dokumen</th>
                                                    <th>Tidak Dibutuhkan</th>
                                                    <th>File</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $bulkNo = 1; foreach ($group['items'] as $item): ?>
                                                    <?php $bulkInputId = 'mf-bulk-file-' . (int) $group['id_doc_package_mainfeeder'] . '-' . (int) $item['id_doc_item_mainfeeder']; ?>
                                                    <?php $bulkNotRequiredId = 'mf-bulk-not-required-' . (int) $group['id_doc_package_mainfeeder'] . '-' . (int) $item['id_doc_item_mainfeeder']; ?>
                                                    <tr>
                                                        <td><?= $bulkNo++ ?></td>
                                                        <td>
                                                            <strong><?= $item['doc_name'] ?></strong>
                                                            <div class="text-muted small">Status: <?= checklist_doc_status_label($item['status_file']) ?></div>
                                                            <input type="hidden" name="id_doc_item_mainfeeder[]" value="<?= (int) $item['id_doc_item_mainfeeder'] ?>">
                                                            <input type="hidden" name="doc_name[]" value="<?= htmlspecialchars($item['doc_name'], ENT_QUOTES) ?>">
                                                        </td>
                                                        <td>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input bulk-not-required-checkbox" id="<?= $bulkNotRequiredId ?>" name="bulk_not_required[]" value="<?= (int) $item['id_doc_item_mainfeeder'] ?>" data-file-input="#<?= $bulkInputId ?>">
                                                                <label class="custom-control-label" for="<?= $bulkNotRequiredId ?>">Ya</label>
                                                            </div>
                                                        </td>
                                                        <td><input type="file" name="bulk_file_<?= (int) $item['id_doc_item_mainfeeder'] ?>" id="<?= $bulkInputId ?>" class="form-control bulk-file-input"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="bulk-upload-progress-panel">
                                        <div class="upload-progress-meta">
                                            <span>Progress Upload</span>
                                            <span class="bulk-upload-progress-percent">0%</span>
                                        </div>
                                        <div class="doc-progress">
                                            <div class="doc-progress-bar warning bulk-upload-progress-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-success bulk-upload-submit">Upload Semua Yang Diisi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalUploadMainfeeder">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/uploadMainfeederDocument') ?>" enctype="multipart/form-data" id="upload-mainfeeder-form">
                <div class="modal-header bg-success"><h4 class="modal-title">Upload Dokumen Mainfeeder</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <input type="hidden" name="mainfeeder_id" id="mf-upload-mainfeeder-id">
                    <input type="hidden" name="id_doc_package_mainfeeder" id="mf-upload-package-id">
                    <input type="hidden" name="id_doc_item_mainfeeder" id="mf-upload-item-id">
                    <input type="hidden" name="doc_name" id="mf-upload-doc-name-input">
                    <div class="form-group"><label>Dokumen</label><input type="text" id="mf-upload-doc-name" class="form-control" readonly></div>
                    <div class="form-group">
                        <label>File</label>
                        <input type="file" name="file" id="mf-upload-file-input" class="form-control" required>
                        <small class="form-text text-muted">Maksimal dokumen 100 MB.</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="mf-is-document-not-required" name="is_document_not_required" value="1">
                            <label class="custom-control-label" for="mf-is-document-not-required">Tidak dibutuhkan dokumen</label>
                        </div>
                    </div>
                    <div class="form-group mb-0"><label>Remark</label><textarea name="remark" class="form-control" rows="3"></textarea></div>
                    <div class="upload-progress-panel" id="mf-upload-progress-panel">
                        <div class="upload-progress-meta">
                            <span>Progress Upload</span>
                            <span id="mf-upload-progress-percent">0%</span>
                        </div>
                        <div class="doc-progress">
                            <div class="doc-progress-bar warning" id="mf-upload-progress-bar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-success" id="mf-upload-submit">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistoryMainfeederDocument">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">History Dokumen</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong id="mf-history-doc-title">-</strong>
                    <div class="text-muted small"><?= htmlspecialchars((string) ($mainfeeder['mainfeeder_name'] ?? '-'), ENT_QUOTES) ?></div>
                </div>
                <ul class="doc-history-list" id="mf-history-doc-list">
                    <li class="text-muted">Belum ada history.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalApproveMainfeeder">
    <div class="modal-dialog"><div class="modal-content"><form method="post" action="<?= base_url('Checklist_Dokument_MyRep/approveMainfeederDocument') ?>">
        <div class="modal-header bg-primary"><h4 class="modal-title">Approve Dokumen</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <input type="hidden" name="mainfeeder_id" id="mf-approve-mainfeeder-id">
            <input type="hidden" name="id_doc_file_mainfeeder" id="mf-approve-file-id">
            <div class="form-group mb-0"><label>Remark</label><textarea name="remark" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Approve</button></div>
    </form></div></div>
</div>

<div class="modal fade" id="modalRejectMainfeeder">
    <div class="modal-dialog"><div class="modal-content"><form method="post" action="<?= base_url('Checklist_Dokument_MyRep/rejectMainfeederDocument') ?>">
        <div class="modal-header bg-danger"><h4 class="modal-title">Reject Dokumen</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <input type="hidden" name="mainfeeder_id" id="mf-reject-mainfeeder-id">
            <input type="hidden" name="id_doc_file_mainfeeder" id="mf-reject-file-id">
            <div class="form-group mb-0"><label>Remark</label><textarea name="remark" class="form-control" rows="3" required></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-danger">Reject</button></div>
    </form></div></div>
</div>

<div class="modal fade" id="modalAstriMainfeeder">
    <div class="modal-dialog"><div class="modal-content"><form method="post" action="<?= base_url('Checklist_Dokument_MyRep/saveMainfeederAstriStatus') ?>">
        <div class="modal-header bg-dark"><h4 class="modal-title">Update Status ASTRI</h4><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <input type="hidden" name="mainfeeder_id" id="mf-astri-mainfeeder-id">
            <input type="hidden" name="id_doc_file_mainfeeder" id="mf-astri-file-id">
            <div class="form-group"><label>Tanggal Submit ASTRI</label><input type="date" name="astri_submitted_date" id="mf-astri-submitted-date" class="form-control"></div>
            <div class="form-group">
                <label>Status ASTRI</label>
                <select name="astri_status" id="mf-astri-status" class="form-control">
                    <option value="NY">NY</option>
                    <option value="ON REVIEW">ON REVIEW</option>
                    <option value="REJECTED">REJECTED</option>
                    <option value="APPROVED">APPROVED</option>
                </select>
            </div>
            <div class="form-group mb-0"><label>Remark ASTRI</label><textarea name="astri_remark" id="mf-astri-remark" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button><button type="submit" class="btn btn-dark">Simpan</button></div>
    </form></div></div>
</div>

<script>
    $(document).on('click', '.btn-mf-upload', function() {
        $('#mf-upload-mainfeeder-id').val($(this).data('mainfeeder-id'));
        $('#mf-upload-package-id').val($(this).data('package-id'));
        $('#mf-upload-item-id').val($(this).data('item-id'));
        $('#mf-upload-doc-name').val($(this).data('doc-name'));
        $('#mf-upload-doc-name-input').val($(this).data('doc-name'));
        $('#mf-is-document-not-required').prop('checked', false);
        $('#mf-upload-file-input').prop('disabled', false).prop('required', true).val('');
        $('#mf-upload-progress-panel').hide();
        $('#mf-upload-progress-bar').css('width', '0%');
        $('#mf-upload-progress-percent').text('0%');
        $('#mf-upload-submit').prop('disabled', false).text('Upload');
    });

    $(document).on('change', '#mf-is-document-not-required', function() {
        var checked = $(this).is(':checked');
        $('#mf-upload-file-input').prop('disabled', checked).prop('required', !checked);
        if (checked) {
            $('#mf-upload-file-input').val('');
        }
    });

    $(document).on('change', '.bulk-not-required-checkbox', function() {
        var checked = $(this).is(':checked');
        var fileInput = $($(this).data('file-input'));
        fileInput.prop('disabled', checked);
        if (checked) {
            fileInput.val('');
        }
    });

    $(document).on('change', '.bulk-astri-status', function() {
        var dateInput = $($(this).data('date-input'));
        var isNy = $(this).val() === 'NY';
        dateInput.prop('required', !isNy);
        if (isNy) {
            dateInput.val('');
        }
    });

    $(document).on('click', '.btn-mf-approve', function() {
        $('#mf-approve-mainfeeder-id').val($(this).data('mainfeeder-id'));
        $('#mf-approve-file-id').val($(this).data('file-id'));
    });

    $(document).on('click', '.btn-mf-reject', function() {
        $('#mf-reject-mainfeeder-id').val($(this).data('mainfeeder-id'));
        $('#mf-reject-file-id').val($(this).data('file-id'));
    });

    $(document).on('click', '.btn-mf-astri', function() {
        var status = $(this).data('astri-status') || 'NY';
        $('#mf-astri-mainfeeder-id').val($(this).data('mainfeeder-id'));
        $('#mf-astri-file-id').val($(this).data('file-id'));
        $('#mf-astri-submitted-date').val($(this).data('astri-submitted-date'));
        $('#mf-astri-status').val(status);
        $('#mf-astri-remark').val($(this).data('astri-remark') || '');
    });

    $('#upload-mainfeeder-form').on('submit', function(e) {
        e.preventDefault();

        var form = this;
        var submitButton = $('#mf-upload-submit');
        var progressPanel = $('#mf-upload-progress-panel');
        var progressBar = $('#mf-upload-progress-bar');
        var progressPercent = $('#mf-upload-progress-percent');
        var formData = new FormData(form);

        submitButton.prop('disabled', true).text('Uploading...');
        progressPanel.show();
        progressBar.removeClass('success').addClass('warning').css('width', '0%');
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
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percent = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.css('width', percent + '%');
                            progressPercent.text(percent + '%');
                        }
                    }, false);
                }
                return xhr;
            },
            success: function(response) {
                progressBar.removeClass('warning').addClass('success').css('width', '100%');
                progressPercent.text('100%');
                if (response && response.status) {
                    $('#modalUploadMainfeeder').modal('hide');
                    window.setTimeout(cleanupMainfeederModalBackdrop, 150);
                    form.reset();
                    refreshMainfeederChecklistStatus(response.message || 'Dokumen mainfeeder berhasil diupload.');
                    return;
                }

                showMainfeederUploadMessage(response && response.message ? response.message : 'Upload gagal.', false);
                submitButton.prop('disabled', false).text('Upload');
            },
            error: function() {
                showMainfeederUploadMessage('Upload gagal. Silakan coba lagi.', false);
                submitButton.prop('disabled', false).text('Upload');
            }
        });
    });

    $(document).on('submit', '.mf-bulk-upload-form', function(e) {
        e.preventDefault();

        var form = this;
        var formData = new FormData(form);
        var submitButton = $(form).find('.bulk-upload-submit');
        var progressPanel = $(form).find('.bulk-upload-progress-panel');
        var progressBar = $(form).find('.bulk-upload-progress-bar');
        var progressPercent = $(form).find('.bulk-upload-progress-percent');

        submitButton.prop('disabled', true).text('Uploading...');
        progressPanel.show();
        progressBar.removeClass('success').addClass('warning').css('width', '0%');
        progressPercent.text('0%');

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percent = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.css('width', percent + '%');
                            progressPercent.text(percent + '%');
                        }
                    }, false);
                }
                return xhr;
            },
            success: function(response) {
                progressBar.removeClass('warning').addClass('success').css('width', '100%');
                progressPercent.text('100%');
                if (response && response.status) {
                    $('.modal.show').modal('hide');
                    window.setTimeout(cleanupMainfeederModalBackdrop, 150);
                    form.reset();
                    refreshMainfeederChecklistStatus(response.message || 'Dokumen berhasil disubmit.');
                    return;
                }
                showMainfeederUploadMessage(response && response.message ? response.message : 'Bulk upload gagal.', false);
                submitButton.prop('disabled', false).text('Upload Semua Yang Diisi');
            },
            error: function() {
                showMainfeederUploadMessage('Bulk upload gagal. Silakan coba lagi.', false);
                submitButton.prop('disabled', false).text('Upload Semua Yang Diisi');
            }
        });
    });

    $(document).on('click', '.btn-mf-approve-all', function() {
        var button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Approving...');
        $.ajax({
            url: '<?= base_url('Checklist_Dokument_MyRep/approveAllMainfeederDocuments') ?>',
            type: 'POST',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: {
                mainfeeder_id: button.data('mainfeeder-id'),
                id_doc_package_mainfeeder: button.data('package-id')
            }
        }).done(function(response) {
            if (response && response.status) {
                refreshMainfeederChecklistStatus(response.message || 'Approve all berhasil.');
                return;
            }
            showMainfeederUploadMessage(response && response.message ? response.message : 'Approve all gagal.', false);
            button.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i>Approve All');
        }).fail(function() {
            showMainfeederUploadMessage('Approve all gagal. Silakan coba lagi.', false);
            button.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i>Approve All');
        });
    });

    $(document).on('submit', '#modalApproveMainfeeder form, #modalRejectMainfeeder form, #modalAstriMainfeeder form, .mf-ajax-action-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        submitButton.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function(response) {
            if (response && response.status) {
                $('.modal.show').modal('hide');
                window.setTimeout(cleanupMainfeederModalBackdrop, 150);
                refreshMainfeederChecklistStatus(response.message || 'Data berhasil diperbarui.');
                return;
            }
            showMainfeederUploadMessage(response && response.message ? response.message : 'Proses gagal.', false);
            submitButton.prop('disabled', false).text(originalText);
        }).fail(function() {
            showMainfeederUploadMessage('Proses gagal. Silakan coba lagi.', false);
            submitButton.prop('disabled', false).text(originalText);
        });
    });

    function escapeHistoryText(value) {
        return $('<div>').text(value || '').html();
    }

    function renderMainfeederDocumentHistory(history) {
        history = $.isArray(history) ? history : [];
        if (!history.length) {
            $('#mf-history-doc-list').html('<li class="text-muted">Belum ada history.</li>');
            return;
        }

        var html = '';
        history.forEach(function(entry) {
            var actionLabel = entry.action_type || '-';
            if (actionLabel === 'UPLOADED') actionLabel = 'Uploaded';
            if (actionLabel === 'REUPLOADED') actionLabel = 'Re-uploaded';
            if (actionLabel === 'REJECTED') actionLabel = 'Rejected';
            if (actionLabel === 'APPROVED') actionLabel = 'Approved';
            html += '<li class="doc-history-item">' +
                '<div class="doc-history-title">' + escapeHistoryText(actionLabel) + '</div>' +
                '<div class="doc-history-meta">' + escapeHistoryText(entry.action_at || '-') + ' | ' + escapeHistoryText(entry.nama_user || '-') + '</div>' +
                '<p class="doc-history-note"><strong>File:</strong> ' + escapeHistoryText(entry.file_name || '-') + '</p>' +
                '<p class="doc-history-note"><strong>Remark:</strong> ' + escapeHistoryText(entry.remark || '-') + '</p>' +
                '</li>';
        });
        $('#mf-history-doc-list').html(html);
    }

    $(document).on('click', '.btn-mf-history', function() {
        var docName = $(this).data('doc-name') || '-';
        var fileId = parseInt($(this).data('file-id'), 10) || 0;
        $('#mf-history-doc-title').text(docName);
        $('#mf-history-doc-list').html('<li class="text-muted">Memuat history...</li>');
        if (!fileId) {
            renderMainfeederDocumentHistory([]);
            return;
        }

        $.ajax({
            url: '<?= base_url('Checklist_Dokument_MyRep/mainfeederDocumentHistoryData') ?>/' + fileId,
            type: 'GET',
            dataType: 'json'
        }).done(function(response) {
            renderMainfeederDocumentHistory(response && response.status ? response.history : []);
        }).fail(function() {
            $('#mf-history-doc-list').html('<li class="text-danger">Gagal memuat history dokumen.</li>');
        });
    });

    function showMainfeederUploadMessage(message, isSuccess) {
        if (window.Swal && typeof Swal.fire === 'function') {
            Swal.fire({
                title: isSuccess ? 'Success' : 'Gagal',
                text: message,
                type: isSuccess ? 'success' : 'error',
                timer: isSuccess ? 1400 : undefined,
                showConfirmButton: !isSuccess
            });
            return;
        }

        if (message) {
            alert(message);
        }
    }

    function cleanupMainfeederModalBackdrop() {
        $('body').removeClass('modal-open').css('padding-right', '');
        $('.modal-backdrop').remove();
    }

    function refreshMainfeederChecklistStatus(message) {
        var container = $('#mf-checklist-live-content');
        cleanupMainfeederModalBackdrop();
        container.css('opacity', 0.55);
        container.load(window.location.href + ' #mf-checklist-live-content > *', function(responseText, status) {
            container.css('opacity', 1);
            cleanupMainfeederModalBackdrop();
            $('#mf-upload-submit').prop('disabled', false).text('Upload');
            $('#mf-upload-progress-panel').hide();
            $('#mf-upload-progress-bar').removeClass('success').addClass('warning').css('width', '0%');
            $('#mf-upload-progress-percent').text('0%');

            if (status !== 'success') {
                showMainfeederUploadMessage('Upload berhasil, tapi refresh status gagal. Silakan refresh halaman jika status belum berubah.', false);
                return;
            }

            showMainfeederUploadMessage(message || 'Status dokumen berhasil diperbarui.', true);
        });
    }
</script>
