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
                    <h3 class="card-title">Header DRM</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url(!empty($cluster['id_drm']) ? 'DRM_MyRep/updateDrm' : 'DRM_MyRep/saveDrm') ?>">
                        <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                        <input type="hidden" name="id_drm" value="<?= (int) ($cluster['id_drm'] ?? 0) ?>">
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Kota</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Tanggal DRM</label><input type="date" name="drm_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['drm_date'] ?? '')) ?>"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>HP Donasi</label><input type="text" class="form-control" value="<?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?>" readonly></div></div>
                            <div class="col-md-1"><div class="form-group"><label>HP DRM</label><input type="number" name="homepass_drm" class="form-control" min="1" value="<?= htmlspecialchars((string) ($cluster['homepass_drm'] ?? '')) ?>" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Status DRM</label><select name="status_drm" class="form-control"><?php foreach (['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE'] as $statusOption): ?><option value="<?= $statusOption ?>" <?= strtoupper((string) ($cluster['status_drm'] ?? 'DRAFT')) === $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Remark</label><textarea name="remark_drm" rows="2" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_drm'] ?? '')) ?></textarea></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= !empty($cluster['id_drm']) ? 'Update DRM' : 'Simpan DRM' ?></button>
                    </form>
                </div>
            </div>

            <?php if (!$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen DRM belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
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
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></strong></td>
                                            <td><?= htmlspecialchars((string) ($row['doc_requirement_note'] ?? '-')) ?></td>
                                            <td><span class="badge badge-<?= drmDetailBadgeClass(drmDocumentLabel($row)) ?>"><?= htmlspecialchars(drmDocumentLabel($row)) ?></span></td>
                                            <td>
                                                <?php if (!empty($row['file_name'])): ?>
                                                    <div><?= htmlspecialchars((string) $row['file_name']) ?></div>
                                                    <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Preview</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="min-width:280px;">
                                                <form method="post" action="<?= base_url('DRM_MyRep/uploadDocument') ?>" enctype="multipart/form-data">
                                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                    <input type="hidden" name="id_doc_item" value="<?= (int) $row['id_doc_item'] ?>">
                                                    <div class="form-group mb-2"><input type="file" name="file" class="form-control-file"></div>
                                                    <div class="form-group mb-2"><input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                                                    <div class="form-group form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" id="doc_not_required_<?= (int) $row['id_doc_item'] ?>" name="is_document_not_required" value="1">
                                                        <label class="form-check-label" for="doc_not_required_<?= (int) $row['id_doc_item'] ?>">Tidak dibutuhkan</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                                </form>
                                            </td>
                                            <?php if ($canApprove): ?>
                                                <td style="min-width:240px;">
                                                    <?php if (!empty($row['id_doc_file'])): ?>
                                                        <form method="post" action="<?= base_url('DRM_MyRep/approveDocument') ?>" class="mb-2">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                            <input type="hidden" name="id_doc_file" value="<?= (int) $row['id_doc_file'] ?>">
                                                            <input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Remark approve">
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form method="post" action="<?= base_url('DRM_MyRep/rejectDocument') ?>">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                                            <input type="hidden" name="id_doc_file" value="<?= (int) $row['id_doc_file'] ?>">
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
