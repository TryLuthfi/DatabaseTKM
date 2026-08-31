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
if (!function_exists('mfModuleHasDate')) {
    function mfModuleHasDate($date)
    {
        return !empty($date) && $date !== '0000-00-00' && $date !== '0000-00-00 00:00:00';
    }
}
if (!function_exists('mfModuleTerminInvoiceValue')) {
    function mfModuleTerminInvoiceValue($termin)
    {
        if (isset($termin['invoice_value']) && $termin['invoice_value'] !== null && $termin['invoice_value'] !== '') {
            return (float) $termin['invoice_value'];
        }

        return (float) ($termin['termin_value'] ?? 0);
    }
}
if (!function_exists('mfModuleNormalizeNumber')) {
    function mfModuleNormalizeNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        $value = preg_replace('/\s+/', '', $value);
        $dotPos = strrpos($value, '.');
        $commaPos = strrpos($value, ',');
        if ($dotPos !== false && $commaPos !== false) {
            $value = $dotPos > $commaPos
                ? str_replace(',', '', $value)
                : str_replace(',', '.', str_replace('.', '', $value));
        } elseif ($commaPos !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($dotPos !== false && preg_match('/^\d{1,3}(?:\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        }
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        return is_numeric($value) ? (float) $value : 0;
    }
}
if (!function_exists('mfModuleTerminPlanValue')) {
    function mfModuleTerminPlanValue($termin)
    {
        $remark = (string) ($termin['remark_termin'] ?? '');
        if (preg_match('/Plan\s+Invoice\s*:\s*([^\r\n;]+)/i', $remark, $matches)) {
            return mfModuleNormalizeNumber($matches[1]);
        }

        return (float) ($termin['termin_value'] ?? 0);
    }
}
if (!function_exists('mfModuleCertificateDate')) {
    function mfModuleCertificateDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', strtotime($value));
        }
        if (preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $value)) {
            $timestamp = strtotime($value);
            return $timestamp ? date('Y-m-d', $timestamp) : '';
        }

        return '';
    }
}
if (!function_exists('mfModuleStatusBadge')) {
    function mfModuleStatusBadge($status)
    {
        $status = strtoupper(trim((string) $status));
        if (in_array($status, ['APPROVED', 'DONE', 'BILLED', 'PAID'], true)) {
            return 'success';
        }
        if ($status === 'REJECTED') {
            return 'danger';
        }
        if (in_array($status, ['READY BILLING', 'UPLOADED', 'ON REVIEW', 'ON PROGRESS'], true)) {
            return 'warning';
        }

        return 'secondary';
    }
}
$section = strtolower((string) ($section ?? ''));
$mainfeederId = (int) ($mainfeeder['id_mainfeeder'] ?? 0);
$returnUrl = current_url();
$projectType = strtoupper(trim((string) ($mainfeeder['project_type'] ?? 'MAINFEEDER'))) ?: 'MAINFEEDER';
$projectLabel = $projectType === 'FWA' ? 'FWA' : 'Mainfeeder';
$moduleTitle = (string) ($moduleTitle ?? $projectLabel);
$canTambahPo = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('PO_MyRep', 'TAMBAH') : true;
$canEditPo = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT') : true;
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
$canViewCertificateSection = strtoupper(trim((string) $this->session->userdata('homebase'))) === 'HO' || $isSuperAdmin;
$canReleaseCertificate = $canEditPo && (strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO' || $isSuperAdmin);
$certificateTerms = isset($certificateTerms) && is_array($certificateTerms) ? $certificateTerms : [];
$poHeaders = isset($poHeaders) && is_array($poHeaders) ? $poHeaders : [];
$poTotalCount = count($poHeaders);
$poTotalValue = 0;
foreach ($poHeaders as $poHeaderSummary) {
    $poTotalValue += (float) ($poHeaderSummary['po_value'] ?? 0);
}
$sectionMeta = [
    'drm' => ['icon' => 'fas fa-clipboard-check', 'label' => 'DRM', 'accent' => 'primary'],
    'implementasi' => ['icon' => 'fas fa-tools', 'label' => 'Implementasi', 'accent' => 'success'],
    'atp' => ['icon' => 'fas fa-check-double', 'label' => 'ATP', 'accent' => 'info'],
    'po' => ['icon' => 'fas fa-file-invoice-dollar', 'label' => 'PO', 'accent' => 'dark'],
];
$meta = $sectionMeta[$section] ?? ['icon' => 'fas fa-project-diagram', 'label' => 'Mainfeeder', 'accent' => 'primary'];
$stageLinks = [
    ['key' => 'drm', 'label' => 'DRM', 'url' => 'DRM_MyRep/mainfeeder/' . $mainfeederId],
    ['key' => 'atp', 'label' => 'ATP', 'url' => 'ATP_MyRep/mainfeeder/' . $mainfeederId],
    ['key' => 'checklist', 'label' => 'Checklist', 'url' => 'Checklist_Dokument_MyRep/detailMainfeeder/' . $mainfeederId],
    ['key' => 'po', 'label' => 'PO', 'url' => 'PO_MyRep/mainfeeder/' . $mainfeederId],
];
if ($projectType !== 'FWA') {
    array_splice($stageLinks, 1, 0, [[
        'key' => 'implementasi',
        'label' => 'Implementasi',
        'url' => 'Implementasi_BOQ_MyRep/mainfeeder/' . $mainfeederId,
    ]]);
}
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

    .mf-po-hero {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, .18), transparent 32%),
            linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0f766e);
        border-radius: 20px;
        color: #fff;
        margin-bottom: 1.25rem;
        padding: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }

    .mf-po-hero__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .mf-po-hero__label {
        color: rgba(255,255,255,.72);
        font-size: .82rem;
        letter-spacing: 0;
        margin-bottom: .2rem;
        text-transform: uppercase;
    }

    .mf-po-hero__value {
        font-size: 1.15rem;
        font-weight: 800;
    }

    .mf-po-card {
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .mf-po-card__head {
        align-items: center;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.1rem;
    }

    .mf-po-card__title,
    .mf-po-header-box__title {
        color: #0f172a;
        font-weight: 800;
    }

    .mf-po-header-box {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 1rem;
        padding: 1rem;
    }

    .mf-po-termin-table th {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .mf-po-termin-table td {
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .mf-section-header {
            align-items: stretch;
            flex-direction: column;
        }

        .mf-po-card__head {
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
                    <a href="<?= base_url('MyRepublik_Project') ?>" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid mf-detail-shell">
            <?php if (!empty($this->session->flashdata('success'))): ?><div class="alert alert-success"><?= mfModuleDetailHtml($this->session->flashdata('success')) ?></div><?php endif; ?>
            <?php if (!empty($this->session->flashdata('error'))): ?><div class="alert alert-danger"><?= mfModuleDetailHtml($this->session->flashdata('error')) ?></div><?php endif; ?>

            <?php if ($section === 'po'): ?>
                <div class="mf-po-hero">
                    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:1rem;">
                        <div>
                            <div class="h4 font-weight-bold mb-1"><?= mfModuleDetailHtml($mainfeeder['mainfeeder_name'] ?? '-') ?></div>
                            <div class="text-white-50"><?= mfModuleDetailHtml($mainfeeder['regional_name'] ?? '-') ?> &bull; <?= mfModuleDetailHtml($mainfeeder['city_name'] ?? '-') ?></div>
                        </div>
                        <?php if ($canTambahPo): ?>
                            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modal-mf-create-po">Tambah PO</button>
                        <?php endif; ?>
                    </div>

                    <div class="mf-po-hero__grid">
                        <div>
                            <div class="mf-po-hero__label">Project Type</div>
                            <div class="mf-po-hero__value"><?= mfModuleDetailHtml($projectType) ?></div>
                        </div>
                        <div>
                            <div class="mf-po-hero__label">Status Flow</div>
                            <div class="mf-po-hero__value"><?= mfModuleDetailHtml($mainfeeder['current_status'] ?? '-') ?></div>
                        </div>
                        <div>
                            <div class="mf-po-hero__label">Cluster Code</div>
                            <div class="mf-po-hero__value"><?= mfModuleDetailHtml($mainfeeder['cluster_code'] ?? '-') ?></div>
                        </div>
                        <div>
                            <div class="mf-po-hero__label">DRM Date</div>
                            <div class="mf-po-hero__value"><?= mfModuleDetailDate($mainfeeder['drm_date'] ?? '') ?></div>
                        </div>
                        <div>
                            <div class="mf-po-hero__label">PO Count</div>
                            <div class="mf-po-hero__value"><?= (int) $poTotalCount ?></div>
                        </div>
                        <div>
                            <div class="mf-po-hero__label">Total PO Value</div>
                            <div class="mf-po-hero__value"><?= mfModuleDetailNum($poTotalValue) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-outline card-<?= mfModuleDetailHtml($meta['accent']) ?> shadow-sm mf-project-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h4 class="mb-2"><?= mfModuleDetailHtml($mainfeeder['mainfeeder_name'] ?? '-') ?></h4>
                                <span class="badge badge-dark"><?= mfModuleDetailHtml($projectType) ?></span>
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
            <?php endif; ?>

            <nav class="mf-stage-nav">
                <?php foreach ($stageLinks as $stageLink): ?>
                    <a href="<?= base_url($stageLink['url']) ?>" class="<?= $section === $stageLink['key'] ? 'active' : '' ?>">
                        <?= mfModuleDetailHtml($stageLink['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($section === 'drm'): ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">DRM <?= mfModuleDetailHtml($projectLabel) ?></h3></div>
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
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">BOQ DRM <?= mfModuleDetailHtml($projectLabel) ?></h3></div>
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
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">Implementasi <?= mfModuleDetailHtml($projectLabel) ?></h3></div>
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
                    <div class="card-header mf-section-header"><h3 class="card-title mb-1">ATP <?= mfModuleDetailHtml($projectLabel) ?></h3></div>
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
                <div class="mf-po-card mb-4">
                    <div class="mf-po-card__head">
                        <div>
                            <div class="mf-po-card__title">PO <?= mfModuleDetailHtml($projectLabel) ?></div>
                            <div class="small text-muted">Header PO, referensi PO Monitor, dan termin invoice.</div>
                        </div>
                        <span class="badge badge-dark"><?= mfModuleDetailHtml($projectType) ?></span>
                    </div>
                    <div class="card-body">
                        <?php if ($canViewCertificateSection): ?>
                            <div class="mf-po-header-box mb-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:1rem;">
                                    <div class="mf-po-header-box__title mb-0">Sertifikat Claim Invoice</div>
                                    <span class="badge badge-info">Term 2-5</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>PO</th>
                                                    <th>Term</th>
                                                    <th>Syarat Release</th>
                                                    <th>Status</th>
                                                    <th>Sertifikat</th>
                                                    <th>Invoice</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($certificateTerms)): ?>
                                                    <tr><td colspan="7" class="text-center text-muted">Belum ada term sertifikat untuk <?= mfModuleDetailHtml($projectLabel) ?> ini.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($certificateTerms as $term): ?>
                                                        <?php
                                                        $termNo = (int) ($term['termin_no'] ?? 0);
                                                        $certValue = trim((string) ($term['sertifikat_invoice_date'] ?? ''));
                                                        $isReleased = !empty($term['is_certificate_released']);
                                                        $isReady = !empty($term['is_release_ready']);
                                                        $isFacTerm = $termNo === 5;
                                                        $statusBadge = $isReleased ? 'success' : ($isReady ? 'primary' : 'warning');
                                                        $statusText = $isReleased ? 'Released' : ($isReady ? 'Ready Release' : ($isFacTerm ? (!empty($term['fac_rfs_certificate_date']) ? 'BJT' : 'NY FAC') : 'Waiting ASTRI'));
                                                        $canClaimCertificate = $canReleaseCertificate && ($isReady || $isReleased);
                                                        ?>
                                                        <tr>
                                                            <td><strong><?= mfModuleDetailHtml($term['po_number'] ?? '-') ?></strong><div class="small text-muted"><?= mfModuleDetailHtml($term['po_category'] ?? '-') ?></div></td>
                                                            <td><strong><?= mfModuleDetailHtml($term['term_label'] ?? ('Term ' . $termNo)) ?></strong><div class="small text-muted"><?= mfModuleDetailNum($term['termin_value'] ?? 0) ?></div></td>
                                                            <td>
                                                                <?php if ($isFacTerm): ?>
                                                                    <div>RFS Cert <?= mfModuleDetailDate($term['fac_rfs_certificate_date'] ?? '') ?></div>
                                                                    <div>BJT <?= mfModuleDetailDate($term['fac_due_date'] ?? '') ?></div>
                                                                    <?php if (!empty($term['fac_rfs_certificate_date'])): ?>
                                                                        <div>Umur <?= (int) ($term['fac_age_days'] ?? 0) ?> hari</div>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <div>Submit <?= (int) ($term['astri_submitted_docs'] ?? 0) ?>/<?= (int) ($term['required_docs'] ?? 0) ?></div>
                                                                    <div>Approved <?= (int) ($term['astri_approved_docs'] ?? 0) ?>/<?= (int) ($term['required_docs'] ?? 0) ?></div>
                                                                <?php endif; ?>
                                                                <div class="small text-muted"><?= mfModuleDetailHtml($term['release_note'] ?? '') ?></div>
                                                            </td>
                                                            <td><span class="badge badge-<?= $statusBadge ?>"><?= mfModuleDetailHtml($statusText) ?></span></td>
                                                            <td><?= $certValue !== '' ? mfModuleDetailHtml($certValue) : '-' ?></td>
                                                            <td><span class="badge badge-<?= mfModuleStatusBadge($term['status_termin'] ?? '') ?>"><?= mfModuleDetailHtml($term['status_termin'] ?? '-') ?></span><div class="small text-muted"><?= mfModuleDetailDate($term['invoice_date'] ?? '') ?></div></td>
                                                            <td>
                                                                <?php if ($canReleaseCertificate): ?>
                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                        <button type="button" class="btn btn-dark js-mf-cert-modal" data-toggle="modal" data-target="#modal-mf-certificate" data-mode="claim" data-termin-id="<?= (int) ($term['id_po_termin'] ?? 0) ?>" data-po-number="<?= mfModuleDetailHtml($term['po_number'] ?? '-') ?>" data-term-label="<?= mfModuleDetailHtml($term['term_label'] ?? ('Term ' . $termNo)) ?>" data-certificate="<?= mfModuleDetailHtml($certValue) ?>" <?= $canClaimCertificate ? '' : 'disabled' ?>>Claim</button>
                                                                        <button type="button" class="btn btn-outline-secondary js-mf-cert-modal" data-toggle="modal" data-target="#modal-mf-certificate" data-mode="status" data-termin-id="<?= (int) ($term['id_po_termin'] ?? 0) ?>" data-po-number="<?= mfModuleDetailHtml($term['po_number'] ?? '-') ?>" data-term-label="<?= mfModuleDetailHtml($term['term_label'] ?? ('Term ' . $termNo)) ?>" data-certificate="<?= mfModuleDetailHtml($certValue) ?>">Status</button>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Read only</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($poHeaders as $po): ?>
                            <div class="mf-po-header-box">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:1rem;">
                                    <div class="mf-po-header-box__title mb-0">
                                        <?= mfModuleDetailHtml($po['po_number'] ?? '-') ?>
                                        <span class="badge badge-primary ml-2"><?= mfModuleDetailHtml($po['po_category'] ?? '-') ?></span>
                                        <span class="badge badge-info ml-1"><?= mfModuleDetailHtml($po['status_po'] ?? '-') ?></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3"><strong>Tanggal PO</strong><div><?= mfModuleDetailDate($po['po_date'] ?? '') ?></div></div>
                                    <div class="col-md-3"><strong>Nilai PO</strong><div><?= mfModuleDetailNum($po['po_value'] ?? 0) ?></div></div>
                                    <div class="col-md-3"><strong>Versi</strong><div><?= !empty($po['po_version_label']) ? mfModuleDetailHtml($po['po_version_label']) : '-' ?></div></div>
                                    <div class="col-md-3"><strong>Remark</strong><div><?= !empty($po['remark_po']) ? mfModuleDetailHtml($po['remark_po']) : '-' ?></div></div>
                                </div>
                                <div class="mt-3">
                                    <?php if ($canEditPo): ?>
                                        <form method="post" action="<?= base_url('PO_MyRep/setMainfeederPoNyRef/' . $mainfeederId) ?>" class="form-inline">
                                            <input type="hidden" name="id_po_header" value="<?= (int) ($po['id_po_header'] ?? 0) ?>">
                                            <label class="mr-2 mb-2 mb-sm-0"><strong>NY PO REF</strong></label>
                                            <input
                                                type="text"
                                                name="ny_po_ref"
                                                class="form-control form-control-sm mr-2"
                                                placeholder="NY-123"
                                                value="<?= mfModuleDetailHtml($po['po_monitor_ny_ref'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Update Ref</button>
                                        </form>
                                    <?php else: ?>
                                        <strong>NY PO REF</strong>
                                        <div><?= !empty($po['po_monitor_ny_ref']) ? mfModuleDetailHtml($po['po_monitor_ny_ref']) : '-' ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="table-responsive mt-3">
                                        <table class="table table-bordered table-sm mf-po-termin-table">
                                            <thead><tr><th>Term</th><th>%</th><th>Invoice</th><th>Outstanding</th><th>Status</th><th>Sertifikat</th><th>No Invoice</th><th>Tgl Invoice</th><th>BAST</th><th>Payment</th><th>Remark</th><th>Aksi</th></tr></thead>
                                            <tbody>
                                                <?php $invoiceTotal = 0; $outstandingTotal = 0; ?>
                                                <?php foreach (($po['termin_rows'] ?? []) as $term): ?>
                                                    <?php
                                                    $hasInvoice = mfModuleHasDate($term['invoice_date'] ?? '');
                                                    $invoiceValue = $hasInvoice ? mfModuleTerminInvoiceValue($term) : 0;
                                                    $outstandingValue = $hasInvoice ? 0 : mfModuleTerminPlanValue($term);
                                                    $invoiceTotal += $invoiceValue;
                                                    $outstandingTotal += $outstandingValue;
                                                    $termCertValue = trim((string) ($term['sertifikat_invoice_date'] ?? ''));
                                                    $termCertDate = mfModuleCertificateDate($termCertValue);
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?= (int) ($term['termin_no'] ?? 0) ?></td>
                                                        <td class="text-center"><?= mfModuleDetailNum($term['termin_percent'] ?? 0) ?>%</td>
                                                        <td class="text-right"><?= $invoiceValue > 0 ? mfModuleDetailNum($invoiceValue) : '-' ?></td>
                                                        <td class="text-right"><?= $outstandingValue > 0 ? mfModuleDetailNum($outstandingValue) : '-' ?></td>
                                                        <td class="text-center"><span class="badge badge-<?= mfModuleStatusBadge($term['status_termin'] ?? '') ?>"><?= mfModuleDetailHtml($term['status_termin'] ?? '-') ?></span></td>
                                                        <td class="text-center">
                                                            <?php if ((int) ($term['termin_no'] ?? 0) >= 2): ?>
                                                                <?php if ($termCertDate !== ''): ?><span class="badge badge-success"><?= mfModuleDetailHtml($termCertValue) ?></span>
                                                                <?php elseif ($termCertValue !== ''): ?><span class="badge badge-secondary"><?= mfModuleDetailHtml($termCertValue) ?></span>
                                                                <?php else: ?><span class="badge badge-warning">Waiting</span><?php endif; ?>
                                                            <?php else: ?>-<?php endif; ?>
                                                        </td>
                                                        <td><?= mfModuleDetailHtml($term['invoice_number'] ?? '-') ?></td>
                                                        <td class="text-center"><?= mfModuleDetailDate($term['invoice_date'] ?? '') ?></td>
                                                        <td class="text-center"><?= mfModuleDetailDate($term['bast_date'] ?? '') ?></td>
                                                        <td class="text-center"><?= mfModuleDetailDate($term['payment_date'] ?? '') ?></td>
                                                        <td><?= !empty($term['remark_termin']) ? mfModuleDetailHtml($term['remark_termin']) : '-' ?></td>
                                                        <td class="text-center">
                                                            <?php if ($canEditPo): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-primary js-mf-termin-modal" data-toggle="modal" data-target="#modal-mf-termin" data-termin-id="<?= (int) ($term['id_po_termin'] ?? 0) ?>" data-po-number="<?= mfModuleDetailHtml($po['po_number'] ?? '') ?>" data-termin-no="<?= (int) ($term['termin_no'] ?? 0) ?>" data-status="<?= mfModuleDetailHtml($term['status_termin'] ?? '') ?>" data-invoice-number="<?= mfModuleDetailHtml($term['invoice_number'] ?? '') ?>" data-invoice-value="<?= mfModuleDetailHtml($term['invoice_value'] ?? '') ?>" data-invoice-date="<?= mfModuleDetailHtml($term['invoice_date'] ?? '') ?>" data-bast-date="<?= mfModuleDetailHtml($term['bast_date'] ?? '') ?>" data-payment-date="<?= mfModuleDetailHtml($term['payment_date'] ?? '') ?>" data-sertifikat="<?= mfModuleDetailHtml($termCertValue) ?>" data-remark="<?= mfModuleDetailHtml($term['remark_termin'] ?? '') ?>">Update</button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot><tr><th colspan="2" class="text-right">TOTAL</th><th class="text-right"><?= $invoiceTotal > 0 ? mfModuleDetailNum($invoiceTotal) : '-' ?></th><th class="text-right"><?= $outstandingTotal > 0 ? mfModuleDetailNum($outstandingTotal) : '-' ?></th><th colspan="8"></th></tr></tfoot>
                                        </table>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($poHeaders)): ?>
                            <div class="text-center text-muted py-4">Belum ada PO <?= mfModuleDetailHtml($projectLabel) ?>.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($section === 'po' && $canTambahPo): ?>
<div class="modal fade" id="modal-mf-create-po" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Mainfeeder_MyRep/savePo/' . $mainfeederId) ?>">
                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah PO <?= mfModuleDetailHtml($projectLabel) ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label><?= mfModuleDetailHtml($projectLabel) ?></label><input type="text" class="form-control" value="<?= mfModuleDetailHtml($mainfeeder['mainfeeder_name'] ?? '-') ?>" readonly></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Status Flow</label><input type="text" class="form-control" value="<?= mfModuleDetailHtml($mainfeeder['current_status'] ?? '-') ?>" readonly></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Kategori PO</label><select name="po_category" class="form-control"><?php foreach (($poCategoryOptions ?? []) as $value => $label): ?><option value="<?= mfModuleDetailHtml($value) ?>"><?= mfModuleDetailHtml($label) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Status PO</label><select name="status_po" class="form-control"><?php foreach (($poStatusOptions ?? []) as $value => $label): ?><option value="<?= mfModuleDetailHtml($value) ?>" <?= $value === 'ISSUED' ? 'selected' : '' ?>><?= mfModuleDetailHtml($label) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>NY PO REF</label><input type="text" name="ny_po_ref" class="form-control" placeholder="NY-123 (opsional)"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Nomor PO</label><input type="text" name="po_number" class="form-control" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Tanggal PO</label><input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Nilai PO</label><input type="text" name="po_value" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Versi</label><input type="text" name="po_version_label" class="form-control" placeholder="FINAL 01 / AMANDMENT 01"></div></div>
                        <div class="col-md-8"><div class="form-group"><label>Parent PO</label><select name="parent_po_header_id" class="form-control"><option value="">PO Baru</option><?php foreach ($poHeaders as $existingPo): ?><option value="<?= (int) ($existingPo['id_po_header'] ?? 0) ?>"><?= mfModuleDetailHtml($existingPo['po_number'] ?? '-') ?> - <?= mfModuleDetailHtml($existingPo['po_category'] ?? '-') ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-12"><div class="form-group mb-0"><label>Remark</label><textarea name="remark_po" class="form-control" rows="3"></textarea></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-muted small mr-auto">5 termin dibuat otomatis mengikuti nilai PO.</div>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan PO</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($section === 'po' && $canEditPo): ?>
<div class="modal fade" id="modal-mf-termin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Mainfeeder_MyRep/updateTermin/' . $mainfeederId) ?>">
                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                <input type="hidden" name="id_po_termin" id="mf_termin_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Update Termin PO</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>PO:</strong> <span id="mf_termin_po_number">-</span> |
                        <strong>Termin:</strong> <span id="mf_termin_no">-</span> |
                        <strong>Sertifikat:</strong> <span id="mf_termin_sertifikat">-</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Status Termin</label><select name="status_termin" id="mf_termin_status" class="form-control"><?php foreach (($terminStatusOptions ?? []) as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Nomor Invoice</label><input type="text" name="invoice_number" id="mf_termin_invoice_number" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Nilai Invoice</label><input type="text" name="invoice_value" id="mf_termin_invoice_value" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tanggal Invoice</label><input type="date" name="invoice_date" id="mf_termin_invoice_date" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tanggal BAST</label><input type="date" name="bast_date" id="mf_termin_bast_date" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tanggal Payment</label><input type="date" name="payment_date" id="mf_termin_payment_date" class="form-control"></div></div>
                        <div class="col-md-12"><div class="form-group mb-0"><label>Remark</label><textarea name="remark_termin" id="mf_termin_remark" class="form-control" rows="3"></textarea></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Update Termin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($section === 'po' && $canReleaseCertificate): ?>
<div class="modal fade" id="modal-mf-certificate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Mainfeeder_MyRep/saveTerminCertificate/' . $mainfeederId) ?>">
                <input type="hidden" name="return_url" value="<?= mfModuleDetailHtml($returnUrl) ?>">
                <input type="hidden" name="id_po_termin" id="mf_certificate_termin_id">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="mf_certificate_title">Update Sertifikat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>PO:</strong> <span id="mf_certificate_po_number">-</span> |
                        <strong>Term:</strong> <span id="mf_certificate_term_label">-</span>
                    </div>
                    <div class="form-group">
                        <label id="mf_certificate_label">Sertifikat</label>
                        <input type="text" name="sertifikat_invoice" id="mf_certificate_value" class="form-control">
                        <small class="form-text text-muted" id="mf_certificate_help"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark" id="mf_certificate_submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($section === 'po'): ?>
<script>
    (function () {
        $(document).on('click', '.js-mf-termin-modal', function () {
            var $button = $(this);
            $('#mf_termin_id').val($button.data('termin-id') || '');
            $('#mf_termin_po_number').text($button.data('po-number') || '-');
            $('#mf_termin_no').text($button.data('termin-no') || '-');
            $('#mf_termin_status').val($button.data('status') || 'NOT READY');
            $('#mf_termin_invoice_number').val($button.data('invoice-number') || '');
            $('#mf_termin_invoice_value').val($button.data('invoice-value') || '');
            $('#mf_termin_invoice_date').val($button.data('invoice-date') || '');
            $('#mf_termin_bast_date').val($button.data('bast-date') || '');
            $('#mf_termin_payment_date').val($button.data('payment-date') || '');
            $('#mf_termin_sertifikat').text($button.data('sertifikat') || '-');
            $('#mf_termin_remark').val($button.data('remark') || '');
        });

        $(document).on('click', '.js-mf-cert-modal', function () {
            var $button = $(this);
            var mode = String($button.data('mode') || 'claim');
            var certificateValue = String($button.data('certificate') || '');
            $('#mf_certificate_termin_id').val($button.data('termin-id') || '');
            $('#mf_certificate_po_number').text($button.data('po-number') || '-');
            $('#mf_certificate_term_label').text($button.data('term-label') || '-');

            if (mode === 'status') {
                $('#mf_certificate_title').text('Update Status Sertifikat');
                $('#mf_certificate_label').text('Status Sertifikat');
                $('#mf_certificate_value').attr('type', 'text').val(certificateValue);
                $('#mf_certificate_help').text('Status text bebas. Tanggal valid akan tetap divalidasi sebagai claim release.');
                $('#mf_certificate_submit').text('Simpan Status').removeClass('btn-dark').addClass('btn-secondary');
                return;
            }

            $('#mf_certificate_title').text('Claim Sertifikat');
            $('#mf_certificate_label').text('Tanggal Release Sertifikat');
            $('#mf_certificate_value')
                .attr('type', 'date')
                .val(/^\d{4}-\d{2}-\d{2}$/.test(certificateValue) ? certificateValue : '');
            $('#mf_certificate_help').text('Hanya tanggal valid. Bisa disimpan setelah syarat ASTRI/FAC terpenuhi.');
            $('#mf_certificate_submit').text('Claim Sertifikat').removeClass('btn-secondary').addClass('btn-dark');
        });
    })();
</script>
<?php endif; ?>
