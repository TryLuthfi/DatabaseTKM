<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('drmDetailBadgeClass')) {
    function drmDetailBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'DONE':
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

if (!function_exists('drmDocumentLabel')) {
    function drmDocumentLabel($row)
    {
        if ((int) ($row['is_document_not_required'] ?? 0) === 1) {
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status !== '' ? $status : 'BELUM UPLOAD';
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail DRM MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('DRM_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
                    <form method="post" action="<?= base_url('DRM_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta DRM dan seluruh flow MyRep terkait?');">
                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                        <button type="submit" class="btn btn-outline-danger">Hapus Cluster</button>
                    </form>
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

            <style>
                .drm-header-card .card-header {
                    background: linear-gradient(135deg, #f8fbff, #eef6ff);
                    border-bottom: 1px solid #dbeafe;
                }

                .drm-edit-btn {
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

                .drm-edit-btn:hover,
                .drm-edit-btn:focus {
                    color: #fff;
                    background: linear-gradient(135deg, #1e40af, #1d4ed8);
                }

                .drm-info-grid strong {
                    display: block;
                    margin-bottom: .2rem;
                    color: #334155;
                }

                .drm-info-grid > div {
                    margin-bottom: 1rem;
                }

                .drm-doc-card .card-header,
                .drm-boq-card .table thead th {
                    background: #eff6ff;
                    color: #1e3a8a;
                }

                .drm-boq-card .table thead th {
                    white-space: nowrap;
                }

                .drm-boq-status {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .75rem;
                    margin-bottom: 1rem;
                }

                .drm-boq-status__item {
                    padding: .65rem .9rem;
                    border-radius: 12px;
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    min-width: 180px;
                }

                .drm-boq-status__label {
                    font-size: .8rem;
                    color: #64748b;
                    margin-bottom: .2rem;
                }

                .drm-boq-status__value {
                    font-weight: 700;
                    color: #111827;
                }

                .drm-dropzone {
                    position: relative;
                    background: linear-gradient(135deg, #f8fbff, #eff6ff);
                    border: 2px dashed #93c5fd;
                    border-radius: 16px;
                    padding: 1rem;
                    transition: all .2s ease;
                    cursor: pointer;
                }

                .drm-dropzone.dragover {
                    border-color: #2563eb;
                    background: linear-gradient(135deg, #dbeafe, #eff6ff);
                }

                .drm-dropzone input[type="file"] {
                    position: absolute;
                    inset: 0;
                    opacity: 0;
                    cursor: pointer;
                }

                .drm-dropzone-content {
                    pointer-events: none;
                    text-align: center;
                }

                .drm-dropzone-icon {
                    font-size: 1.8rem;
                    color: #2563eb;
                    margin-bottom: .5rem;
                }

                .drm-dropzone-title {
                    font-weight: 700;
                    color: #1d4ed8;
                    margin-bottom: .25rem;
                }

                .drm-dropzone-text {
                    color: #64748b;
                    font-size: .9rem;
                    margin-bottom: .35rem;
                }

                .drm-dropzone-file {
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

                .drm-modal .modal-content {
                    border: 0;
                    border-radius: 18px;
                    overflow: hidden;
                    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
                }

                .drm-modal .modal-body {
                    background: #f6f8fb;
                    padding: 1.25rem;
                }

                .drm-modal .modal-footer {
                    border-top: 0;
                    background: #eef2f7;
                }

                .drm-form-box {
                    background: #fff;
                    border: 1px solid #e5edf6;
                    border-radius: 14px;
                    padding: 1rem 1.1rem;
                    margin-bottom: 1rem;
                }

                .drm-form-box:last-child {
                    margin-bottom: 0;
                }

                .drm-form-box__title {
                    font-weight: 700;
                    color: #1f2937;
                    margin-bottom: .85rem;
                }
            </style>

            <div class="card card-primary shadow-sm drm-header-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Header DRM</h3>
                    <button type="button" class="btn btn-sm drm-edit-btn" data-toggle="modal" data-target="#modal-drm-edit">
                        <i class="fas fa-pen"></i>
                        Edit DRM
                    </button>
                </div>
                <div class="card-body">
                    <div class="row drm-info-grid">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Province</strong><div><?= !empty($cluster['province_name']) ? htmlspecialchars((string) $cluster['province_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Status Flow</strong><div><?= !empty($cluster['status_current']) ? htmlspecialchars((string) $cluster['status_current']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-2"><strong>Cluster Code</strong><div><?= !empty($cluster['cluster_code']) ? htmlspecialchars((string) $cluster['cluster_code']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Team</strong><div><?= !empty($cluster['team_name']) ? htmlspecialchars((string) $cluster['team_name']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>Chief</strong><div><?= !empty($cluster['chief']) ? htmlspecialchars((string) $cluster['chief']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>RPM</strong><div><?= !empty($cluster['rpm']) ? htmlspecialchars((string) $cluster['rpm']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>SM</strong><div><?= !empty($cluster['sm']) ? htmlspecialchars((string) $cluster['sm']) : '-' ?></div></div>
                        <div class="col-md-2"><strong>SPV</strong><div><?= !empty($cluster['spv']) ? htmlspecialchars((string) $cluster['spv']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-3"><strong>PIC Project</strong><div><?= !empty($cluster['pic_project']) ? htmlspecialchars((string) $cluster['pic_project']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Name</strong><div><?= !empty($cluster['ntp_name']) ? htmlspecialchars((string) $cluster['ntp_name']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Date</strong><div><?= !empty($cluster['ntp_date']) ? htmlspecialchars((string) $cluster['ntp_date']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>NTP Year</strong><div><?= !empty($cluster['ntp_year']) ? htmlspecialchars((string) $cluster['ntp_year']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-2"><strong>HP Plan</strong><div><?= number_format((float) ($cluster['hp_plan'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>HP Donasi</strong><div><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>HP DRM</strong><div><?= !is_null($cluster['homepass_drm'] ?? null) ? number_format((float) $cluster['homepass_drm'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Tanggal DRM</strong><div><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Status DRM</strong><div><?= !empty($cluster['display_status_drm']) ? htmlspecialchars((string) $cluster['display_status_drm']) : (!empty($cluster['status_drm']) ? htmlspecialchars((string) $cluster['status_drm']) : 'WAITING INPUT') ?></div></div>
                    </div>
                    <hr>
                    <div class="row drm-info-grid">
                        <div class="col-md-4"><strong>Released At</strong><div><?= !empty($cluster['released_at']) ? htmlspecialchars((string) $cluster['released_at']) : '-' ?></div></div>
                        <div class="col-md-4"><strong>Remark DRM</strong><div><?= !empty($cluster['remark_drm']) ? nl2br(htmlspecialchars((string) $cluster['remark_drm'])) : '-' ?></div></div>
                        <div class="col-md-4"><strong>Outstanding Progress</strong><div><?= !empty($cluster['outstanding_progress']) ? nl2br(htmlspecialchars((string) $cluster['outstanding_progress'])) : '-' ?></div></div>
                    </div>
                </div>
            </div>

            <?php if ($boqReady): ?>
                <?php
                $boqReviewStatus = strtoupper(trim((string) ($boqHeader['review_status'] ?? 'DRAFT')));
                $isBoqLocked = $boqReviewStatus === 'APPROVED';
                $hasApdBoqFile = !empty($apdBoqFile['id_doc_file']);
                ?>
                <?php if (!empty($boqBaselineItems)): ?>
                    <div class="card card-outline card-success shadow-sm drm-boq-card">
                        <div class="card-header">
                            <h3 class="card-title">Baseline BOQ Implementasi</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqBaselineItems as $index => $item): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td><?= number_format((float) ($item['qty_boq'] ?? 0), 2, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen DRM belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm drm-doc-card">
                    <div class="card-header">
                        <h3 class="card-title">Dokumen DRM</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Catatan</th>
                                        <th>Status</th>
                                        <th>File</th>
                                        <th>Upload / Update</th>
                                        <?php if ($canApprove): ?><th>Review</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentRows as $row): ?>
                                        <?php
                                        $docStatus = drmDocumentLabel($row);
                                        $docRawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                        $docCanUpload = $docStatus === 'BELUM UPLOAD' || $docRawStatus === 'REJECTED';
                                        $docCanReview = $canApprove && !empty($row['id_doc_file']) && $docRawStatus === 'UPLOADED';
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                            <td><?= htmlspecialchars((string) ($row['doc_requirement_note'] ?? '-')) ?></td>
                                            <td><span class="badge badge-<?= drmDetailBadgeClass($docStatus) ?>"><?= htmlspecialchars($docStatus) ?></span></td>
                                            <td>
                                                <?php if (!empty($row['file_name'])): ?>
                                                    <div><?= htmlspecialchars((string) $row['file_name']) ?></div>
                                                    <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Preview</a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-dark mt-1 js-doc-history"
                                                        data-toggle="modal"
                                                        data-target="#modal-doc-history"
                                                        data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-history='<?= htmlspecialchars(json_encode(!empty($row['id_doc_file']) ? $this->MDRM_MyRep->getDrmFileLogs((int) $row['id_doc_file']) : []), ENT_QUOTES) ?>'>
                                                        History
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:280px;">
                                                <?php if (($row['doc_name'] ?? '') === 'APD BOQ' && $boqReady): ?>
                                                    <?php if (!$isBoqLocked): ?>
                                                        <button type="button" class="btn btn-sm btn-primary js-open-apd-boq-modal" data-toggle="modal" data-target="#modal-apd-boq-package">Upload / Update</button>
                                                    <?php else: ?>
                                                        <span class="text-success small font-weight-bold">BOQ sudah approved</span>
                                                    <?php endif; ?>
                                                    <div class="small text-muted mt-2">
                                                        Status BOQ:
                                                        <span class="badge badge-<?= drmDetailBadgeClass($boqReviewStatus) ?>"><?= htmlspecialchars($boqReviewStatus !== '' ? $boqReviewStatus : 'DRAFT') ?></span>
                                                    </div>
                                                    <?php if ($canApprove && !empty($boqHeader['id_drm_boq']) && in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success mt-2" data-toggle="modal" data-target="#modal-boq-review">Review BOQ</button>
                                                    <?php endif; ?>
                                                    <?php if (!empty($boqHeader['ho_review_remark'])): ?>
                                                        <div class="small text-info mt-1">Catatan HO: <?= htmlspecialchars((string) $boqHeader['ho_review_remark']) ?></div>
                                                    <?php endif; ?>
                                                <?php elseif ($docCanUpload): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary js-open-drm-upload-modal"
                                                        data-toggle="modal"
                                                        data-target="#modal-drm-upload"
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
                                                <td style="min-width:240px;">
                                                    <?php if ($docCanReview): ?>
                                                        <span class="text-info small font-weight-bold">Review mengikuti approval BOQ</span>
                                                    <?php elseif ($docRawStatus === 'APPROVED'): ?>
                                                        <span class="text-success small font-weight-bold">Sudah approved</span>
                                                    <?php elseif ($docRawStatus === 'REJECTED'): ?>
                                                        <span class="text-danger small font-weight-bold">Sudah rejected</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada file untuk direview</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($documentRows)): ?>
                                        <tr><td colspan="<?= $canApprove ? '6' : '5' ?>" class="text-center text-muted">Belum ada dokumen DRM.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-drm-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url(!empty($cluster['id_drm']) ? 'DRM_MyRep/updateDrm' : 'DRM_MyRep/saveDrm') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_drm" value="<?= (int) ($cluster['id_drm'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><?= !empty($cluster['id_drm']) ? 'Edit Header DRM' : 'Lengkapi Header DRM' ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Kota</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Regional</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?>" readonly></div></div>
                        </div>
                    </div>
                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Update DRM</div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>Tanggal DRM</label><input type="date" name="drm_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['drm_date'] ?? '')) ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>HP DRM</label><input type="number" name="homepass_drm" class="form-control" min="1" value="<?= htmlspecialchars((string) ($cluster['homepass_drm'] ?? '')) ?>" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Status DRM</label><input type="text" name="status_drm" class="form-control" value="<?= htmlspecialchars((string) ($cluster['display_status_drm'] ?? 'WAITING DOC')) ?>" readonly></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Nama OLT</label><input type="text" name="nama_olt" class="form-control" value="<?= htmlspecialchars((string) ($cluster['nama_olt'] ?? '')) ?>" placeholder="Isi nama OLT"></div></div>
                            <div class="col-md-12"><div class="form-group mb-0"><label>Remark DRM</label><textarea name="remark_drm" rows="3" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_drm'] ?? '')) ?></textarea></div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><?= !empty($cluster['id_drm']) ? 'Update DRM' : 'Simpan DRM' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-drm-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_item" id="drm_upload_doc_item_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Upload Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="drm_upload_doc_name">-</span></div>
                    <div class="mb-3"><strong>File Saat Ini:</strong> <span id="drm_upload_current_file">-</span></div>
                    <div class="drm-dropzone js-dropzone">
                        <input type="file" name="file" class="js-dropzone-input">
                        <div class="drm-dropzone-content">
                            <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="drm-dropzone-title">Drag & drop dokumen di sini</div>
                            <div class="drm-dropzone-text">Atau klik area ini untuk memilih file</div>
                            <div class="drm-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>Remark Upload</label>
                        <textarea name="remark" id="drm_upload_remark" class="form-control" rows="3" placeholder="Catatan upload dokumen"></textarea>
                    </div>
                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="drm_upload_not_required" name="is_document_not_required" value="1">
                        <label class="form-check-label" for="drm_upload_not_required">Tidak dibutuhkan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-drm-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/approveDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="drm_approve_file_id">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="drm_approve_doc_name">-</span></div>
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

<div class="modal fade" id="modal-drm-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/rejectDocument') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="drm_reject_file_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="drm_reject_doc_name">-</span></div>
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

<div class="modal fade" id="modal-doc-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">History Dokumen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><strong>Dokumen:</strong> <span id="history_doc_label">-</span></div>
                <ul class="doc-history-list" id="history_doc_items">
                    <li class="text-muted">Belum ada history.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if ($boqReady): ?>
    <div class="modal fade" id="modal-apd-boq-package" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content drm-modal">
                <form method="post" action="<?= base_url('DRM_MyRep/saveApdBoqPackage') ?>" enctype="multipart/form-data" id="form-apd-boq-package">
                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">APD BOQ dan Manual BOQ</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label>Nama Cluster</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>Kota</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Status BOQ</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($boqReviewStatus !== '' ? $boqReviewStatus : 'DRAFT') ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($boqHeader['ho_review_remark'])): ?>
                                <div class="small text-info mt-3">Catatan HO: <?= htmlspecialchars((string) $boqHeader['ho_review_remark']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Input Jumlah Item Manual</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item di Excel</th>
                                            <th>Nama Item</th>
                                            <th>Jenis Item</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqItems as $index => $item): ?>
                                            <?php $qtyValue = (float) ($item['qty_boq'] ?? 0); ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['excel_item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="boq_qty[<?= (int) $item['id_boq_item'] ?>]" class="form-control form-control-sm js-modal-boq-qty" value="<?= rtrim(rtrim(number_format($qtyValue, 2, '.', ''), '0'), '.') ?>" <?= $isBoqLocked ? 'readonly' : '' ?>>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Upload File APD BOQ</div>
                            <div class="mb-2">
                                <?php if ($hasApdBoqFile): ?>
                                    <div class="small text-muted">File saat ini: <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $apdBoqFile['id_doc_file']) ?>" target="_blank"><?= htmlspecialchars((string) ($apdBoqFile['file_name'] ?? '-')) ?></a></div>
                                <?php else: ?>
                                    <div class="small text-muted">Belum ada file APD BOQ.</div>
                                <?php endif; ?>
                            </div>
                            <div class="drm-dropzone js-dropzone">
                                <input type="file" name="apd_boq_file" id="apd_boq_file_input" class="js-dropzone-input">
                                <div class="drm-dropzone-content">
                                    <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="drm-dropzone-title">Drag & drop file APD BOQ di sini</div>
                                    <div class="drm-dropzone-text">Atau klik area ini untuk memilih file</div>
                                    <div class="drm-dropzone-file js-dropzone-label" id="apd_boq_file_label">Belum ada file baru dipilih</div>
                                </div>
                            </div>
                            <div class="form-group mt-3 mb-0">
                                <label>Remark Upload File</label>
                                <textarea name="apd_boq_remark" rows="2" class="form-control" placeholder="Catatan upload APD BOQ jika diperlukan"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="text-muted small">File APD BOQ dan BOQ manual harus sama-sama terisi sebelum disimpan.</div>
                        <div>
                            <?php if (!$isBoqLocked): ?>
                                <button type="submit" class="btn btn-outline-primary">Simpan Draft</button>
                                <button type="submit" class="btn btn-primary" name="submit_to_ho" value="1">Submit ke HO</button>
                            <?php else: ?>
                                <span class="text-muted small">BOQ sudah approved, upload dan edit dinonaktifkan.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php if ($boqReady && $canApprove && !empty($boqHeader['id_drm_boq']) && in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true)): ?>
    <div class="modal fade" id="modal-boq-review" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content drm-modal">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Review BOQ DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Informasi Review</div>
                        <div class="small text-muted">Approve BOQ akan sekaligus meng-approve dokumen DRM yang sudah berstatus upload.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" action="<?= base_url('DRM_MyRep/approveBoq') ?>">
                                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                <div class="drm-form-box mb-0">
                                    <div class="drm-form-box__title">Approve BOQ</div>
                                    <div class="form-group">
                                        <label>Remark Approve BOQ</label>
                                        <textarea name="remark" rows="3" class="form-control" placeholder="Catatan approval jika diperlukan"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">Approve BOQ dan Dokumen</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="post" action="<?= base_url('DRM_MyRep/rejectBoq') ?>">
                                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                <div class="drm-form-box mb-0">
                                    <div class="drm-form-box__title">Reject BOQ</div>
                                    <div class="form-group">
                                        <label>Alasan Reject BOQ</label>
                                        <textarea name="remark" rows="3" class="form-control" required placeholder="Wajib diisi jika BOQ manual tidak sesuai file APD BOQ"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Reject BOQ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    (function () {
        var fileInput = document.getElementById('apd_boq_file_input');
        var fileLabel = document.getElementById('apd_boq_file_label');
        var modal = document.getElementById('modal-apd-boq-package');
        var drmUploadModal = document.getElementById('modal-drm-upload');

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
                        label.textContent = event.dataTransfer.files[0].name;
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = input.files && input.files.length > 0
                        ? input.files[0].name
                        : 'Belum ada file dipilih';
                });
            });
        }

        function syncApdFileLabel() {
            if (!fileLabel || !fileInput) {
                return;
            }

            fileLabel.textContent = fileInput.files && fileInput.files.length
                ? fileInput.files[0].name
                : 'Belum ada file baru dipilih';
        }

        bindDropzones();

        document.addEventListener('click', function (event) {
            var uploadButton = event.target.closest('.js-open-drm-upload-modal');
            if (uploadButton) {
                var currentLabel = document.querySelector('#modal-drm-upload .js-dropzone-label');
                var currentInput = document.querySelector('#modal-drm-upload .js-dropzone-input');
                document.getElementById('drm_upload_doc_item_id').value = uploadButton.getAttribute('data-doc-item-id') || '';
                document.getElementById('drm_upload_doc_name').textContent = uploadButton.getAttribute('data-doc-name') || '-';
                document.getElementById('drm_upload_current_file').textContent = uploadButton.getAttribute('data-file-name') || '-';
                document.getElementById('drm_upload_remark').value = uploadButton.getAttribute('data-remark') || '';
                document.getElementById('drm_upload_not_required').checked = false;
                if (currentInput) {
                    currentInput.value = '';
                }
                if (currentLabel) {
                    currentLabel.textContent = 'Belum ada file dipilih';
                }
                return;
            }

            var historyButton = event.target.closest('.js-doc-history');
            if (historyButton) {
                var history = [];
                try {
                    history = historyButton.getAttribute('data-history')
                        ? JSON.parse(historyButton.getAttribute('data-history'))
                        : [];
                } catch (e) {
                    history = [];
                }

                document.getElementById('history_doc_label').textContent = historyButton.getAttribute('data-doc-name') || '-';

                if (!history.length) {
                    document.getElementById('history_doc_items').innerHTML = '<li class="text-muted">Belum ada history.</li>';
                } else {
                    var html = '';
                    history.forEach(function (entry) {
                        html += '<li class="doc-history-item">' +
                            '<div class="doc-history-title">' + (entry.action_type || '-') + '</div>' +
                            '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                            '<div><strong>File:</strong> ' + (entry.file_name || '-') + '</div>' +
                            '<div><strong>Remark:</strong> ' + (entry.remark || '-') + '</div>' +
                        '</li>';
                    });
                    document.getElementById('history_doc_items').innerHTML = html;
                }
                return;
            }

            var reviewButton = event.target.closest('.js-open-drm-review-modal');
            if (reviewButton) {
                var fileId = reviewButton.getAttribute('data-file-id') || '';
                var docName = reviewButton.getAttribute('data-doc-name') || '-';
                document.getElementById('drm_approve_file_id').value = fileId;
                document.getElementById('drm_reject_file_id').value = fileId;
                document.getElementById('drm_approve_doc_name').textContent = docName;
                document.getElementById('drm_reject_doc_name').textContent = docName;
                return;
            }

            var button = event.target.closest('.js-open-apd-boq-modal');
            if (button) {
                return;
            }
        });

        if (window.jQuery && modal) {
            window.jQuery(modal).on('shown.bs.modal', function () {
                syncApdFileLabel();
            });
        }

        if (window.jQuery && drmUploadModal) {
            window.jQuery(drmUploadModal).on('shown.bs.modal', function () {
                var currentLabel = drmUploadModal.querySelector('.js-dropzone-label');
                if (currentLabel && currentLabel.textContent.trim() === '') {
                    currentLabel.textContent = 'Belum ada file dipilih';
                }
            });
        }

        document.addEventListener('submit', function (event) {
            if (event.target.closest('#modal-drm-upload')) {
                var uploadCheckbox = document.getElementById('drm_upload_not_required');
                var uploadInput = document.querySelector('#modal-drm-upload .js-dropzone-input');
                var noDocument = uploadCheckbox && uploadCheckbox.checked;
                var hasFile = uploadInput && uploadInput.files && uploadInput.files.length > 0;

                if (!noDocument && !hasFile) {
                    event.preventDefault();
                    alert('File dokumen DRM wajib dipilih atau centang "Tidak dibutuhkan".');
                }
                return;
            }

            if (event.target.id !== 'form-apd-boq-package') {
                return;
            }

            var hasExistingFile = <?= !empty($apdBoqFile['id_doc_file']) ? 'true' : 'false' ?>;
            var hasNewFile = fileInput && fileInput.files && fileInput.files.length > 0;
            var hasQty = false;
            var qtyInputs = event.target.querySelectorAll('.js-modal-boq-qty');

            qtyInputs.forEach(function (input) {
                if (parseFloat(input.value || '0') > 0) {
                    hasQty = true;
                }
            });

            if (!hasQty || (!hasExistingFile && !hasNewFile)) {
                event.preventDefault();
                alert('File APD BOQ dan BOQ manual wajib sama-sama terisi sebelum disimpan.');
            }
        });
    })();
</script>
