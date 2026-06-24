<?php
if (!function_exists('mfModuleDetailHtml')) {
    function mfModuleDetailHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('mfModuleDetailDate')) {
    function mfModuleDetailDate($value)
    {
        $value = trim((string) $value);
        return $value !== '' && $value !== '0000-00-00' ? date('d/m/Y', strtotime($value)) : '-';
    }
}
if (!function_exists('mfModuleDetailNum')) {
    function mfModuleDetailNum($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
$section = strtolower((string) ($section ?? ''));
$mainfeederId = (int) ($mainfeeder['id_mainfeeder'] ?? 0);
$returnUrl = current_url();
$moduleTitle = (string) ($moduleTitle ?? 'Mainfeeder');
$sectionMeta = [
    'drm' => ['icon' => 'fas fa-clipboard-check', 'label' => 'DRM', 'accent' => 'primary'],
    'implementasi' => ['icon' => 'fas fa-tools', 'label' => 'Implementasi', 'accent' => 'success'],
    'atp' => ['icon' => 'fas fa-check-double', 'label' => 'ATP', 'accent' => 'info'],
    'po' => ['icon' => 'fas fa-file-invoice-dollar', 'label' => 'PO', 'accent' => 'dark'],
];
$meta = $sectionMeta[$section] ?? ['icon' => 'fas fa-project-diagram', 'label' => 'Mainfeeder', 'accent' => 'primary'];
$stageLinks = [
    ['key' => 'drm', 'label' => 'DRM', 'url' => 'DRM_MyRep/mainfeeder/' . $mainfeederId],
    ['key' => 'implementasi', 'label' => 'Implementasi', 'url' => 'Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId],
    ['key' => 'atp', 'label' => 'ATP', 'url' => 'ATP_MyRep/mainfeeder/' . $mainfeederId],
    ['key' => 'checklist', 'label' => 'Checklist', 'url' => 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId],
    ['key' => 'po', 'label' => 'PO', 'url' => 'PO_MyRep/mainfeeder/' . $mainfeederId],
];
$apdBoqDocItemId = 0;
foreach (($drmDocuments ?? []) as $docRow) {
    if (strtoupper(trim((string) ($docRow['doc_name'] ?? ''))) === 'APD BOQ') {
        $apdBoqDocItemId = (int) ($docRow['id_doc_item'] ?? 0);
        break;
    }
}
?>

<style>
    .mf-detail-page .content-header {
        padding-bottom: 0;
    }

    .mf-detail-shell {
        padding: 0 0.5rem 1rem;
    }

    .mf-detail-page .card {
        border-radius: 12px;
    }

    .mf-detail-page .card-header {
        border-radius: 12px 12px 0 0;
    }

    .mf-section-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .mf-section-header .card-title {
        color: #0f172a;
        font-weight: 850;
    }

    .mf-project-card h4 {
        color: #0f172a;
        font-weight: 850;
    }

    .mf-project-meta strong,
    .mf-detail-page label {
        color: #475569;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .mf-detail-page .form-control {
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: none;
    }

    .mf-detail-page .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.12);
    }

    .mf-stage-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .mf-stage-nav a {
        align-items: center;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 999px;
        color: #334155;
        display: inline-flex;
        font-weight: 800;
        gap: 0.35rem;
        padding: 0.42rem 0.85rem;
    }

    .mf-stage-nav a.active {
        background: #0f172a;
        border-color: #0f172a;
        color: #fff;
    }

    .mf-detail-table thead th {
        background: #0f172a;
        border-color: #0f172a;
        color: #f8fafc;
        font-size: 0.78rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .mf-detail-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .mf-inline-form {
        display: flex;
        gap: 0.45rem;
        min-width: 360px;
    }

    .mf-inline-form .form-control {
        min-width: 120px;
    }

    .mf-action-btn {
        border-radius: 10px;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        .mf-section-header {
            align-items: stretch;
            flex-direction: column;
        }

        .mf-inline-form {
            flex-direction: column;
            min-width: 240px;
        }

        .mf-detail-page .text-right {
            text-align: left !important;
        }
    }
</style>

<div class="content-wrapper mf-detail-page">
    <section class="content-header">
        <div class="container-fluid mf-detail-shell">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?= mfModuleDetailHtml($moduleTitle) ?></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('MyRepublik_Project') ?>" class="btn btn-outline-secondary">List Project</a>
                    <a href="<?= base_url('MyRepublik_Project') ?>" class="btn btn-dark">List Project</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid mf-detail-shell">
            <?php if (!empty($this->session->flashdata('success'))): ?><div class="alert alert-success"><?= mfModuleDetailHtml($this->session->flashdata('success')) ?></div><?php endif; ?>
            <?php if (!empty($this->session->flashdata('error'))): ?><div class="alert alert-danger"><?= mfModuleDetailHtml($this->session->flashdata('error')) ?></div><?php endif; ?>

            <div class="card card-outline card-<?= mfModuleDetailHtml($meta['accent']) ?> shadow-sm mf-project-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h4 class="mb-2"><?= mfModuleDetailHtml($mainfeeder['mainfeeder_name'] ?? '-') ?></h4>
                            <span class="badge badge-dark">MAINFEEDER</span>
                            <span class="badge badge-info"><?= mfModuleDetailHtml($mainfeeder['current_status'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-7 mf-project-meta">
                            <div class="row">
                                <div class="col-md-4"><strong>Cluster Code</strong><div><?= mfModuleDetailHtml($mainfeeder['cluster_code'] ?? '-') ?></div></div>
                                <div class="col-md-4"><strong>Kota</strong><div><?= mfModuleDetailHtml($mainfeeder['city_name'] ?? '-') ?></div></div>
                                <div class="col-md-4"><strong>Regional</strong><div><?= mfModuleDetailHtml($mainfeeder['regional_name'] ?? '-') ?></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="mf-stage-nav">
                <?php foreach ($stageLinks as $stageLink): ?>
                    <a href="<?= base_url($stageLink['url']) ?>" class="<?= $section === $stageLink['key'] ? 'active' : '' ?>">
                        <?= mfModuleDetailHtml($stageLink['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($section === 'drm'): ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">DRM Mainfeeder</h3></div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('Mainfeeder_MyRep/saveDrm/' . $mainfeederId) ?>" class="row mb-4">
                            <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                            <div class="col-md-3"><div class="form-group"><label>Tanggal DRM</label><input type="date" name="drm_date" value="<?= mfModuleDetailHtml($mainfeeder['drm_date'] ?? '') ?>" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Nama OLT</label><input type="text" name="nama_olt" value="<?= mfModuleDetailHtml($mainfeeder['nama_olt'] ?? '') ?>" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Status DRM</label><select name="status_drm" class="form-control"><?php foreach (['DRAFT','SUBMITTED','ON REVIEW','APPROVED','REJECTED','DONE'] as $st): ?><option value="<?= $st ?>" <?= strtoupper((string)($mainfeeder['status_drm'] ?? '')) === $st ? 'selected' : '' ?>><?= $st ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Remark</label><input type="text" name="remark_drm" value="<?= mfModuleDetailHtml($mainfeeder['remark_drm'] ?? '') ?>" class="form-control"></div></div>
                            <div class="col-md-12"><button type="submit" class="btn btn-primary mf-action-btn">Simpan DRM</button></div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mf-detail-table">
                                <thead><tr><th>Group</th><th>Dokumen</th><th>Status</th><th>File</th><th>Upload</th><th>Approval</th></tr></thead>
                                <tbody>
                                    <?php foreach (($drmDocuments ?? []) as $doc): ?>
                                        <tr>
                                            <td><?= mfModuleDetailHtml($doc['group_label'] ?? '-') ?></td>
                                            <td><?= mfModuleDetailHtml($doc['doc_name'] ?? '-') ?></td>
                                            <td><span class="badge badge-secondary"><?= mfModuleDetailHtml($doc['status_file'] ?? 'BELUM UPLOAD') ?></span></td>
                                            <td><?= !empty($doc['file_path']) ? '<a target="_blank" href="' . base_url($doc['file_path']) . '">' . mfModuleDetailHtml($doc['file_name'] ?? 'File') . '</a>' : '-' ?></td>
                                            <td>
                                                <form method="post" action="<?= base_url('Mainfeeder_MyRep/uploadDrmDocument/' . $mainfeederId) ?>" enctype="multipart/form-data" class="mf-inline-form">
                                                    <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                                                    <input type="hidden" name="id_doc_item" value="<?= (int) ($doc['id_doc_item'] ?? 0) ?>">
                                                    <input type="hidden" name="doc_name" value="<?= mfModuleDetailHtml($doc['doc_name'] ?? '') ?>">
                                                    <input type="file" name="file" class="form-control form-control-sm">
                                                    <button type="submit" class="btn btn-sm btn-success mf-action-btn">Upload</button>
                                                </form>
                                            </td>
                                            <td>
                                                <?php if (!empty($canApprove) && !empty($doc['id_doc_file_mainfeeder_flow'])): ?>
                                                    <form method="post" action="<?= base_url('Mainfeeder_MyRep/reviewDrmDocument/' . $mainfeederId) ?>" class="mf-inline-form">
                                                        <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                                                        <input type="hidden" name="id_doc_file_mainfeeder_flow" value="<?= (int) $doc['id_doc_file_mainfeeder_flow'] ?>">
                                                        <select name="status_file" class="form-control form-control-sm"><option value="APPROVED">APPROVED</option><option value="REJECTED">REJECTED</option></select>
                                                        <input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark">
                                                        <button type="submit" class="btn btn-sm btn-primary mf-action-btn">Simpan</button>
                                                    </form>
                                                <?php else: ?>-<?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">BOQ DRM Mainfeeder</h3></div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('Mainfeeder_MyRep/uploadDrmBoq/' . $mainfeederId) ?>" enctype="multipart/form-data" class="form-inline mb-3">
                            <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                            <input type="hidden" name="id_doc_item" value="<?= $apdBoqDocItemId ?>">
                            <input type="file" name="boq_file" class="form-control mr-2" accept=".xls,.xlsx" required>
                            <button type="submit" class="btn btn-warning mf-action-btn">Submit BOQ</button>
                        </form>
                        <div class="mb-2"><strong>Status Review:</strong> <span class="badge badge-info"><?= mfModuleDetailHtml($boqHeader['review_status'] ?? '-') ?></span></div>
                        <?php if (!empty($canApprove) && !empty($boqItems) && strtoupper((string) ($boqHeader['review_status'] ?? '')) !== 'APPROVED'): ?>
                            <form method="post" action="<?= base_url('Mainfeeder_MyRep/reviewDrmBoq/' . $mainfeederId) ?>" class="form-inline mb-3">
                                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                                <select name="action_review" class="form-control mr-2"><option value="APPROVE">APPROVE</option><option value="REJECT">REJECT</option></select>
                                <input type="text" name="remark" class="form-control mr-2" placeholder="Remark HO">
                                <button type="submit" class="btn btn-primary mf-action-btn">Simpan Review BOQ</button>
                            </form>
                        <?php endif; ?>
                        <div class="table-responsive"><table class="table table-bordered table-hover mf-detail-table"><thead><tr><th>Item</th><th>Type</th><th class="text-right">Qty BOQ</th></tr></thead><tbody>
                            <?php if (empty($boqItems)): ?><tr><td colspan="3" class="text-center text-muted">Belum ada BOQ.</td></tr><?php else: foreach ($boqItems as $item): ?>
                                <tr><td><?= mfModuleDetailHtml($item['item_name'] ?? '-') ?></td><td><?= mfModuleDetailHtml($item['item_type'] ?? '-') ?></td><td class="text-right"><?= mfModuleDetailNum($item['qty_boq'] ?? 0) ?></td></tr>
                            <?php endforeach; endif; ?>
                        </tbody></table></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section === 'implementasi'): ?>
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">Implementasi Mainfeeder</h3></div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('Mainfeeder_MyRep/saveDailyActivity/' . $mainfeederId) ?>" enctype="multipart/form-data" class="row">
                            <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                            <div class="col-md-3"><div class="form-group"><label>Tanggal</label><input type="date" name="activity_date" class="form-control" required></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Activity</label><select name="activity_code" class="form-control"><?php foreach (($activityDefinitions ?? []) as $def): ?><option value="<?= mfModuleDetailHtml($def['activity_code']) ?>"><?= mfModuleDetailHtml($def['activity_name']) ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Qty</label><input type="text" name="qty_activity" class="form-control" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Team</label><input type="number" name="team_count" class="form-control" min="0"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Worker</label><input type="number" name="worker_count" class="form-control" min="0"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Detail</label><input type="text" name="activity_detail" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Foto</label><input type="file" name="activity_photos[]" class="form-control" multiple required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Remark</label><input type="text" name="remark_activity" class="form-control"></div></div>
                            <div class="col-md-12"><button type="submit" class="btn btn-success mf-action-btn">Simpan Aktivitas</button></div>
                        </form>
                        <hr>
                        <div class="table-responsive"><table class="table table-bordered table-hover mf-detail-table"><thead><tr><th>Item</th><th class="text-right">Plan</th><th class="text-right">Actual</th><th class="text-right">%</th></tr></thead><tbody>
                            <?php if (empty($compareRows)): ?><tr><td colspan="4" class="text-center text-muted">Baseline belum tersedia.</td></tr><?php else: foreach ($compareRows as $row): ?>
                                <tr><td><?= mfModuleDetailHtml($row['item_name'] ?? '-') ?></td><td class="text-right"><?= mfModuleDetailNum($row['qty_boq'] ?? 0) ?></td><td class="text-right"><?= mfModuleDetailNum($row['qty_progress'] ?? 0) ?></td><td class="text-right"><?= mfModuleDetailHtml($row['progress_percent'] ?? 0) ?>%</td></tr>
                            <?php endforeach; endif; ?>
                        </tbody></table></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section === 'atp'): ?>
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">ATP Mainfeeder</h3></div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('Mainfeeder_MyRep/saveAtp/' . $mainfeederId) ?>" enctype="multipart/form-data" class="row">
                            <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                            <div class="col-md-3"><div class="form-group"><label>Email ATP</label><input type="date" name="email_atp_date" value="<?= mfModuleDetailHtml($mainfeeder['email_atp_date'] ?? '') ?>" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Tanggal ATP</label><input type="date" name="atp_date" value="<?= mfModuleDetailHtml($mainfeeder['atp_date'] ?? '') ?>" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Status ATP</label><select name="status_atp" class="form-control"><option value="">-</option><option value="PUNCLIST" <?= strtoupper((string)($mainfeeder['status_atp'] ?? '')) === 'PUNCLIST' ? 'selected' : '' ?>>PUNCLIST</option><option value="DONE" <?= strtoupper((string)($mainfeeder['status_atp'] ?? '')) === 'DONE' ? 'selected' : '' ?>>DONE</option></select></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Record Punclist</label><input type="file" name="record_punclist_file" class="form-control"></div></div>
                            <div class="col-md-6"><div class="form-group"><label>Remark Punclist</label><input type="text" name="record_punclist_remark" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>BA Rectification</label><input type="file" name="ba_rectification_file" class="form-control"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Remark BA</label><input type="text" name="ba_rectification_remark" class="form-control"></div></div>
                            <div class="col-md-12"><button type="submit" class="btn btn-info mf-action-btn">Simpan ATP</button></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section === 'po'): ?>
                <div class="card card-outline card-dark shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">PO Mainfeeder</h3></div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('Mainfeeder_MyRep/savePo/' . $mainfeederId) ?>" class="row">
                            <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                            <div class="col-md-3"><div class="form-group"><label>Nomor PO</label><input type="text" name="po_number" class="form-control" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Tanggal PO</label><input type="date" name="po_date" class="form-control" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Nilai PO</label><input type="text" name="po_value" class="form-control" required></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Kategori</label><select name="po_category" class="form-control"><?php foreach (($poCategoryOptions ?? []) as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status_po" class="form-control"><?php foreach (($poStatusOptions ?? []) as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                            <div class="col-md-12"><button type="submit" class="btn btn-dark mf-action-btn">Simpan PO</button></div>
                        </form>
                        <hr>
                        <?php foreach (($poHeaders ?? []) as $po): ?>
                            <h5><strong><?= mfModuleDetailHtml($po['po_number'] ?? '-') ?></strong> <span class="badge badge-secondary"><?= mfModuleDetailHtml($po['po_category'] ?? '-') ?></span></h5>
                            <div class="table-responsive mb-4"><table class="table table-bordered table-hover mf-detail-table"><thead><tr><th>Term</th><th>%</th><th>Value</th><th>Status</th><th>Invoice</th><th>Sertifikat</th><th>Action</th></tr></thead><tbody>
                                <?php foreach (($po['termin_rows'] ?? []) as $term): ?>
                                    <tr>
                                        <td><?= (int) ($term['termin_no'] ?? 0) ?></td>
                                        <td><?= mfModuleDetailNum($term['termin_percent'] ?? 0) ?>%</td>
                                        <td><?= mfModuleDetailNum($term['termin_value'] ?? 0) ?></td>
                                        <td><?= mfModuleDetailHtml($term['status_termin'] ?? '-') ?></td>
                                        <td><?= mfModuleDetailDate($term['invoice_date'] ?? '') ?> / <?= mfModuleDetailHtml($term['invoice_number'] ?? '-') ?></td>
                                        <td><?= mfModuleDetailHtml($term['sertifikat_invoice_date'] ?? '-') ?></td>
                                        <td>
                                            <form method="post" action="<?= base_url('Mainfeeder_MyRep/updateTermin/' . $mainfeederId) ?>" class="mb-1">
                                                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                                                <input type="hidden" name="id_po_termin" value="<?= (int) $term['id_po_termin'] ?>">
                                                <div class="input-group input-group-sm">
                                                    <select name="status_termin" class="form-control"><?php foreach (($terminStatusOptions ?? []) as $value => $label): ?><option value="<?= $value ?>" <?= strtoupper((string)($term['status_termin'] ?? '')) === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select>
                                                    <input type="date" name="invoice_date" value="<?= mfModuleDetailHtml($term['invoice_date'] ?? '') ?>" class="form-control">
                                                    <input type="text" name="invoice_number" value="<?= mfModuleDetailHtml($term['invoice_number'] ?? '') ?>" class="form-control" placeholder="Invoice">
                                                    <button type="submit" class="btn btn-primary mf-action-btn">Update</button>
                                                </div>
                                            </form>
                                            <form method="post" action="<?= base_url('Mainfeeder_MyRep/saveTerminCertificate/' . $mainfeederId) ?>">
                                                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                                                <input type="hidden" name="id_po_termin" value="<?= (int) $term['id_po_termin'] ?>">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="sertifikat_invoice" value="<?= mfModuleDetailHtml($term['sertifikat_invoice_date'] ?? '') ?>" class="form-control" placeholder="Tanggal/status sertifikat">
                                                    <button type="submit" class="btn btn-secondary mf-action-btn">Simpan</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody></table></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
