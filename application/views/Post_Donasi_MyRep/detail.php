<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('postDonasiDocumentLabel')) {
    function postDonasiDocumentLabel($row)
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

if (!function_exists('postDonasiDocumentBadge')) {
    function postDonasiDocumentBadge($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'ON REVIEW':
            case 'UPLOADED':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Post Donasi</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('Post_Donasi_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
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
                    <h3 class="card-title">Informasi Cluster</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4"><strong>Cluster</strong><div><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Kota</strong><div><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Regional</strong><div><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Status Flow</strong><div><?= htmlspecialchars((string) ($cluster['status_current'] ?? '-')) ?></div></div>
                        <div class="col-md-2"><strong>Released At</strong><div><?= !empty($cluster['released_at']) ? htmlspecialchars((string) $cluster['released_at']) : '-' ?></div></div>
                    </div>
                </div>
            </div>

            <?php if (!$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen post donasi belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">12 Dokumen Post Donasi</h3>
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
                                            <td><span class="badge badge-<?= postDonasiDocumentBadge(postDonasiDocumentLabel($row)) ?>"><?= htmlspecialchars(postDonasiDocumentLabel($row)) ?></span></td>
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
                                                    <div class="form-group mb-2"><input type="file" name="file" class="form-control-file"></div>
                                                    <div class="form-group mb-2"><input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark upload"></div>
                                                    <div class="form-group form-check mb-2">
                                                        <input type="checkbox" class="form-check-input" id="post_doc_not_required_<?= (int) $row['id_doc_item'] ?>" name="is_document_not_required" value="1">
                                                        <label class="form-check-label" for="post_doc_not_required_<?= (int) $row['id_doc_item'] ?>">Tidak dibutuhkan</label>
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
                                                            <input type="text" name="remark" class="form-control form-control-sm mb-2" placeholder="Remark approve">
                                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                        </form>
                                                        <form method="post" action="<?= base_url('Post_Donasi_MyRep/rejectDocument') ?>">
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
