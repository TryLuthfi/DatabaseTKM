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
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($row['status_file'] ?? $row['batch_doc_status'] ?? '')));
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

            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Informasi Cluster & Batch</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>HP Donasi</strong><div><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-2"><strong>Tanggal Pengajuan</strong><div><?= !empty($cluster['submission_date']) ? htmlspecialchars((string) $cluster['submission_date']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3"><strong>Nominal Pengajuan</strong><div><?= number_format((float) ($cluster['nominal_pengajuan_area'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-md-3"><strong>Nominal Approval EMR</strong><div><?= !is_null($cluster['nominal_nego_emr'] ?? null) ? number_format((float) $cluster['nominal_nego_emr'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Nominal Release</strong><div><?= !is_null($cluster['nominal_release_finance'] ?? null) ? number_format((float) $cluster['nominal_release_finance'], 0, ',', '.') : '-' ?></div></div>
                        <div class="col-md-3"><strong>Staging</strong><div><span class="badge badge-<?= batchDetailBadgeClass($cluster['staging_status'] ?? 'DRAFT') ?>"><?= htmlspecialchars(batchDetailStatusLabel($cluster['staging_status'] ?? 'DRAFT')) ?></span></div></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4"><strong>Penerima Dana</strong><div><?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>No HP</strong><div><?= !empty($cluster['recipient_phone']) ? htmlspecialchars((string) $cluster['recipient_phone']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Jabatan</strong><div><?= !empty($cluster['recipient_position']) ? htmlspecialchars((string) $cluster['recipient_position']) : '-' ?></div></div>
                        <div class="col-md-3"><strong>Periode</strong><div><?= !empty($cluster['recipient_period']) ? htmlspecialchars((string) $cluster['recipient_period']) : '-' ?></div></div>
                    </div>
                    <hr>
                    <div class="row">
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
                                        <div><?= htmlspecialchars((string) ($pic['pic_name'] ?? '-')) ?></div>
                                        <div class="text-muted small"><?= !empty($pic['pic_phone']) ? htmlspecialchars((string) $pic['pic_phone']) : '-' ?></div>
                                        <div class="small"><?= !empty($pic['pic_position']) ? htmlspecialchars((string) $pic['pic_position']) : '-' ?></div>
                                        <div class="small"><?= !empty($pic['pic_period']) ? htmlspecialchars((string) $pic['pic_period']) : '-' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Dokumen Batch Approval</h3>
                </div>
                <div class="card-body">
                    <?php if (!$docReady): ?>
                        <div class="alert alert-warning mb-0">Tabel dokumen Batch Approval belum tersedia.</div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-lg-5 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <strong>RAR</strong>
                                            <div class="text-muted small">Status dokumen utama batch approval</div>
                                        </div>
                                        <span class="badge badge-<?= batchDetailBadgeClass(batchDetailDocumentLabel($batchDocument)) ?>"><?= htmlspecialchars(batchDetailDocumentLabel($batchDocument)) ?></span>
                                    </div>
                                    <div class="mb-2"><strong>File:</strong> <?= !empty($batchDocument['file_name']) ? htmlspecialchars((string) $batchDocument['file_name']) : '-' ?></div>
                                    <div class="mb-3"><strong>Remark:</strong> <?= !empty($batchDocument['remark']) ? htmlspecialchars((string) $batchDocument['remark']) : '-' ?></div>
                                    <?php if (!empty($batchDocument['id_doc_file']) && !empty($batchDocument['file_path'])): ?>
                                        <a href="<?= base_url('Batch_Approval_MyRep/previewDocument/' . (int) $batchDocument['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview RAR</a>
                                    <?php endif; ?>
                                    <?php if (!empty($cluster['transfer_proof_file_path'])): ?>
                                        <div class="mt-3 small">
                                            <strong>Bukti Transfer:</strong>
                                            <a href="<?= base_url(htmlspecialchars((string) $cluster['transfer_proof_file_path'])) ?>" target="_blank">Lihat file</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-7 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDocument') ?>" enctype="multipart/form-data" class="mb-3">
                                        <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                        <input type="hidden" name="redirect_to_detail" value="1">
                                        <div class="form-group">
                                            <label>Upload / Update RAR</label>
                                            <input type="file" name="file" class="form-control-file">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark upload">
                                        </div>
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" id="batch_doc_not_required_detail" name="is_document_not_required" value="1">
                                            <label class="form-check-label" for="batch_doc_not_required_detail">Dokumen tidak dibutuhkan</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan Dokumen</button>
                                    </form>

                                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadTransferProof') ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                        <input type="hidden" name="id_batch_approval" value="<?= (int) $cluster['id_batch_approval'] ?>">
                                        <input type="hidden" name="redirect_to_detail" value="1">
                                        <div class="form-group">
                                            <label>Upload Bukti Transfer</label>
                                            <input type="file" name="transfer_proof" class="form-control-file" required>
                                        </div>
                                        <button type="submit" class="btn btn-dark btn-sm">Upload Bukti Transfer</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php if ($canApprove && !empty($batchDocument['id_doc_file'])): ?>
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <strong>Approve Dokumen</strong>
                                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDocument') ?>" class="mt-3">
                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                            <input type="hidden" name="id_doc_file" value="<?= (int) $batchDocument['id_doc_file'] ?>">
                                            <input type="hidden" name="redirect_to_detail" value="1">
                                            <div class="form-group">
                                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark approve">
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm">Approve RAR</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <strong>Reject Dokumen</strong>
                                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/rejectDocument') ?>" class="mt-3">
                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                            <input type="hidden" name="id_doc_file" value="<?= (int) $batchDocument['id_doc_file'] ?>">
                                            <input type="hidden" name="redirect_to_detail" value="1">
                                            <div class="form-group">
                                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="Alasan reject" required>
                                            </div>
                                            <button type="submit" class="btn btn-danger btn-sm">Reject RAR</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

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

            <div class="card card-outline card-primary shadow-sm">
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
                                        <th>Upload / Update</th>
                                        <?php if ($canApprove): ?><th>Review</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postDonasiRows as $row): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                            <td><?= htmlspecialchars((string) ($row['doc_requirement_note'] ?? '-')) ?></td>
                                            <td><span class="badge badge-<?= batchDetailBadgeClass(batchDetailDocumentLabel($row)) ?>"><?= htmlspecialchars(batchDetailDocumentLabel($row)) ?></span></td>
                                            <td>
                                                <?php if (!empty($row['file_name'])): ?>
                                                    <div><?= htmlspecialchars((string) $row['file_name']) ?></div>
                                                    <a href="<?= base_url('Post_Donasi_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Preview</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:280px;">
                                                <form method="post" action="<?= base_url('Post_Donasi_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                    <input type="hidden" name="id_doc_item" value="<?= (int) $row['id_doc_item'] ?>">
                                                    <input type="hidden" name="redirect_to_batch_detail" value="1">
                                                    <div class="form-group mb-2"><input type="file" name="file" class="form-control-file"></div>
                                                    <div class="form-group mb-2"><input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                                                    <div class="form-group form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" id="post_doc_not_required_detail_<?= (int) $row['id_doc_item'] ?>" name="is_document_not_required" value="1">
                                                        <label class="form-check-label" for="post_doc_not_required_detail_<?= (int) $row['id_doc_item'] ?>">Tidak dibutuhkan</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                                </form>
                                            </td>
                                            <?php if ($canApprove): ?>
                                                <td style="min-width:240px;">
                                                    <?php if (!empty($row['id_doc_file'])): ?>
                                                        <form method="post" action="<?= base_url('Post_Donasi_MyRep/approveDocument') ?>" class="mb-2">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                            <input type="hidden" name="id_doc_file" value="<?= (int) $row['id_doc_file'] ?>">
                                                            <input type="hidden" name="redirect_to_batch_detail" value="1">
                                                            <input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Remark approve">
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form method="post" action="<?= base_url('Post_Donasi_MyRep/rejectDocument') ?>">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                            <input type="hidden" name="id_doc_file" value="<?= (int) $row['id_doc_file'] ?>">
                                                            <input type="hidden" name="redirect_to_batch_detail" value="1">
                                                            <input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Alasan reject" required>
                                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum ada file</span>
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
