<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('PO_MyRep', 'TAMBAH') : true;
$canEdit = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('PO_MyRep', 'EDIT') : true;
$isHomebaseHo = strtoupper(trim((string) $this->session->userdata('homebase'))) === 'HO';
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
$canViewCertificateSection = $isHomebaseHo || $isSuperAdmin;
$canReleaseCertificate = $canEdit && (strtoupper(trim((string) $this->session->userdata('lokasi_user'))) === 'HO' || $isSuperAdmin);
$certificateTerms = isset($certificateTerms) && is_array($certificateTerms) ? $certificateTerms : [];
$certificateTermsByType = ['CLUSTER' => [], 'SUBFEEDER' => []];
foreach ($certificateTerms as $certificateTermRow) {
    $certificatePoType = strtoupper(trim((string) ($certificateTermRow['po_type'] ?? 'CLUSTER')));
    if (!isset($certificateTermsByType[$certificatePoType])) {
        $certificateTermsByType[$certificatePoType] = [];
    }
    $certificateTermsByType[$certificatePoType][] = $certificateTermRow;
}

if (!function_exists('poMyRepValue')) {
    function poMyRepValue($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
if (!function_exists('poMyRepCertificateReleaseDate')) {
    function poMyRepCertificateReleaseDate($value)
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
if (!function_exists('poMyRepDate')) {
    function poMyRepDate($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}
if (!function_exists('poMyRepHasInvoiceDate')) {
    function poMyRepHasInvoiceDate($date)
    {
        return !empty($date) && $date !== '0000-00-00' && $date !== '0000-00-00 00:00:00';
    }
}
if (!function_exists('poMyRepTerminInvoiceValue')) {
    function poMyRepTerminInvoiceValue($termin)
    {
        if (isset($termin['invoice_value']) && $termin['invoice_value'] !== null && $termin['invoice_value'] !== '') {
            return (float) $termin['invoice_value'];
        }

        return (float) ($termin['termin_value'] ?? 0);
    }
}
if (!function_exists('poMyRepNormalizeNumber')) {
    function poMyRepNormalizeNumber($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/\s+/', '', $value);
        $negative = false;
        if (preg_match("/^\('?[-]?([0-9.,]+)\)?$/", $value, $matches)) {
            $negative = strpos($value, '-') !== false || strpos($value, '(') === 0;
            $value = $matches[1];
        }

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

        if (!is_numeric($value)) {
            return 0;
        }

        $number = (float) $value;
        return $negative ? -abs($number) : $number;
    }
}
if (!function_exists('poMyRepTerminPlanInvoiceValue')) {
    function poMyRepTerminPlanInvoiceValue($termin)
    {
        $terminValue = (float) ($termin['termin_value'] ?? 0);
        if ($terminValue > 0) {
            return $terminValue;
        }

        $remark = (string) ($termin['remark_termin'] ?? '');
        if (preg_match('/Plan\s+Invoice\s*:\s*([^\r\n;]+)/i', $remark, $matches)) {
            return (float) poMyRepNormalizeNumber($matches[1]);
        }

        return 0;
    }
}
if (!function_exists('poMyRepStatusBadge')) {
    function poMyRepStatusBadge($status)
    {
        $status = strtoupper(trim((string) $status));
        if (in_array($status, ['APPROVED', 'DONE', 'BILLED', 'PAID'], true)) {
            return 'success';
        }
        if (in_array($status, ['REJECTED'], true)) {
            return 'danger';
        }
        if (in_array($status, ['READY BILLING', 'UPLOADED', 'ON REVIEW', 'ON PROGRESS'], true)) {
            return 'warning';
        }

        return 'secondary';
    }
}
?>

<style>
    .po-detail-hero {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, .18), transparent 32%),
            linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0f766e);
        border-radius: 20px;
        padding: 1.25rem;
        color: #fff;
        margin-bottom: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }

    .po-detail-hero__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .po-detail-hero__label {
        color: rgba(255,255,255,.72);
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .2rem;
    }

    .po-detail-hero__value {
        font-size: 1.15rem;
        font-weight: 800;
    }

    .po-card {
        border: 1px solid #dbeafe;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        background: #fff;
    }

    .po-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.1rem;
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .po-card__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .po-header-box {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .po-header-box__title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .7rem;
    }

    .po-termin-table th {
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }

    .po-termin-table td {
        vertical-align: middle;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail PO MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('PO_MyRep') ?>" class="btn btn-outline-secondary">Kembali</a>
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

            <div class="po-detail-hero">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:1rem;">
                    <div>
                        <div class="h4 font-weight-bold mb-1"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div>
                        <div class="text-white-50"><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?> • <?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div>
                    </div>
                    <?php if ($canTambah): ?>
                        <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modal-create-po">Tambah PO</button>
                    <?php endif; ?>
                </div>

                <div class="po-detail-hero__grid">
                    <div>
                        <div class="po-detail-hero__label">Status Flow</div>
                        <div class="po-detail-hero__value"><?= htmlspecialchars((string) ($cluster['status_current'] ?? '-')) ?></div>
                    </div>
                    <div>
                        <div class="po-detail-hero__label">DRM Date</div>
                        <div class="po-detail-hero__value"><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div>
                    </div>
                    <div>
                        <div class="po-detail-hero__label">HP DRM</div>
                        <div class="po-detail-hero__value"><?= poMyRepValue((float) ($cluster['homepass_drm'] ?? 0)) ?></div>
                    </div>
                    <div>
                        <div class="po-detail-hero__label">RPM / SPV</div>
                        <div class="po-detail-hero__value"><?= htmlspecialchars((string) ($cluster['rpm'] ?? '-')) ?> / <?= htmlspecialchars((string) ($cluster['spv'] ?? '-')) ?></div>
                    </div>
                    <div>
                        <div class="po-detail-hero__label">PO Count</div>
                        <div class="po-detail-hero__value"><?= (int) ($cluster['po_count'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="po-detail-hero__label">Total PO Value</div>
                        <div class="po-detail-hero__value"><?= poMyRepValue((float) ($cluster['po_total_value'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>

            <?php if ($canViewCertificateSection): ?>
                <div class="po-card mb-4">
                    <div class="po-card__head">
                        <div>
                            <div class="po-card__title">Sertifikat Claim Invoice</div>
                            <div class="small text-muted">Release sertifikat mengikuti status ASTRI per SOW dan tersimpan ke termin PO terkait.</div>
                        </div>
                        <span class="badge badge-info">Term 2-5</span>
                    </div>
                    <div class="card-body">
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
                                    <?php foreach (['CLUSTER' => 'PO Cluster', 'SUBFEEDER' => 'PO Subfeeder'] as $certificatePoType => $certificatePoLabel): ?>
                                        <?php $typedCertificateTerms = $certificateTermsByType[$certificatePoType] ?? []; ?>
                                        <tr class="table-secondary">
                                            <td colspan="7" class="font-weight-bold" style="border-top: 3px solid #111827;">
                                                <?= htmlspecialchars($certificatePoLabel, ENT_QUOTES) ?>
                                                <span class="badge badge-light border ml-2"><?= count($typedCertificateTerms) ?> term</span>
                                            </td>
                                        </tr>
                                        <?php if (empty($typedCertificateTerms)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Belum ada term PO 2-5 untuk <?= htmlspecialchars($certificatePoLabel, ENT_QUOTES) ?>.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($typedCertificateTerms as $term): ?>
                                                <?php
                                                $termNo = (int) ($term['termin_no'] ?? 0);
                                                $certValue = trim((string) ($term['sertifikat_invoice_date'] ?? ''));
                                                $isReleased = !empty($term['is_certificate_released']);
                                                $isReady = !empty($term['is_release_ready']);
                                                $isManualTerm = $termNo === 5;
                                                $hasFacRfsCertificate = !empty($term['fac_rfs_certificate_date']);
                                                $statusBadge = $isReleased ? 'success' : ($isReady ? 'primary' : 'warning');
                                                $statusText = $isReleased ? 'Released' : ($isReady ? 'Ready Release' : ($isManualTerm ? ($hasFacRfsCertificate ? 'BJT' : 'NY FAC') : 'Waiting ASTRI'));
                                                $canSaveCertificate = $canReleaseCertificate;
                                                $canClaimCertificate = $canReleaseCertificate && ($isReady || $isReleased);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($term['po_number'] ?? '-'), ENT_QUOTES) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars((string) ($term['po_category'] ?? '-'), ENT_QUOTES) ?></div>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($term['term_label'] ?? ('Term ' . $termNo)), ENT_QUOTES) ?></strong>
                                                        <div class="small text-muted"><?= poMyRepValue((float) ($term['termin_value'] ?? 0)) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($isManualTerm): ?>
                                                            <div>RFS Cert <?= !empty($term['fac_rfs_certificate_date']) ? poMyRepDate($term['fac_rfs_certificate_date']) : '-' ?></div>
                                                            <div>BJT <?= !empty($term['fac_due_date']) ? poMyRepDate($term['fac_due_date']) : '-' ?></div>
                                                            <?php if (!empty($term['fac_rfs_certificate_date'])): ?>
                                                                <div>Umur <?= (int) ($term['fac_age_days'] ?? 0) ?> hari</div>
                                                                <?php if ($isReady && !$isReleased): ?>
                                                                    <div>Lewat BJT <?= (int) ($term['fac_days_since_due'] ?? 0) ?> hari</div>
                                                                <?php elseif (!$isReleased): ?>
                                                                    <div>Sisa <?= (int) ($term['fac_days_remaining'] ?? 0) ?> hari</div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div>Submit <?= (int) ($term['astri_submitted_docs'] ?? 0) ?>/<?= (int) ($term['required_docs'] ?? 0) ?></div>
                                                            <div>Approved <?= (int) ($term['astri_approved_docs'] ?? 0) ?>/<?= (int) ($term['required_docs'] ?? 0) ?></div>
                                                        <?php endif; ?>
                                                        <div class="small text-muted"><?= htmlspecialchars((string) ($term['release_note'] ?? ''), ENT_QUOTES) ?></div>
                                                    </td>
                                                    <td><span class="badge badge-<?= $statusBadge ?>"><?= $statusText ?></span></td>
                                                    <td><?= $certValue !== '' ? htmlspecialchars($certValue, ENT_QUOTES) : '-' ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= poMyRepStatusBadge((string) ($term['status_termin'] ?? '')) ?>"><?= htmlspecialchars((string) ($term['status_termin'] ?? '-'), ENT_QUOTES) ?></span>
                                                        <div class="small text-muted"><?= poMyRepDate($term['invoice_date'] ?? null) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($canReleaseCertificate): ?>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-dark js-open-certificate-modal"
                                                                    data-toggle="modal"
                                                                    data-target="#modal-certificate-action"
                                                                    data-mode="claim"
                                                                    data-cluster-id="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>"
                                                                    data-termin-id="<?= (int) ($term['id_po_termin'] ?? 0) ?>"
                                                                    data-po-number="<?= htmlspecialchars((string) ($term['po_number'] ?? '-'), ENT_QUOTES) ?>"
                                                                    data-term-label="<?= htmlspecialchars((string) ($term['term_label'] ?? ('Term ' . $termNo)), ENT_QUOTES) ?>"
                                                                    data-certificate="<?= htmlspecialchars($certValue, ENT_QUOTES) ?>"
                                                                    data-can-claim="<?= $canClaimCertificate ? '1' : '0' ?>"
                                                                    <?= $canClaimCertificate ? '' : 'disabled' ?>>
                                                                    Claim Sertifikat
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-outline-secondary js-open-certificate-modal"
                                                                    data-toggle="modal"
                                                                    data-target="#modal-certificate-action"
                                                                    data-mode="status"
                                                                    data-cluster-id="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>"
                                                                    data-termin-id="<?= (int) ($term['id_po_termin'] ?? 0) ?>"
                                                                    data-po-number="<?= htmlspecialchars((string) ($term['po_number'] ?? '-'), ENT_QUOTES) ?>"
                                                                    data-term-label="<?= htmlspecialchars((string) ($term['term_label'] ?? ('Term ' . $termNo)), ENT_QUOTES) ?>"
                                                                    data-certificate="<?= htmlspecialchars($certValue, ENT_QUOTES) ?>">
                                                                    Update Status
                                                                </button>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Read only</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($poGroups as $groupKey => $groupRows): ?>
                <div class="po-card mb-4">
                    <div class="po-card__head">
                        <div class="po-card__title"><?= $groupKey === 'SUBFEEDER' ? 'PO Subfeeder' : 'PO Cluster' ?></div>
                        <div class="small text-muted"><?= count($groupRows) ?> PO</div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($groupRows)): ?>
                            <div class="text-muted">Belum ada data <?= $groupKey === 'SUBFEEDER' ? 'PO Subfeeder' : 'PO Cluster' ?>.</div>
                        <?php else: ?>
                            <?php foreach ($groupRows as $header): ?>
                                <div class="po-header-box">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                        <div class="po-header-box__title mb-0">
                                            <?= htmlspecialchars((string) ($header['po_number'] ?? '-')) ?>
                                            <span class="badge badge-primary ml-2"><?= htmlspecialchars((string) ($header['po_category'] ?? '-')) ?></span>
                                            <span class="badge badge-info ml-1"><?= htmlspecialchars((string) ($header['status_po'] ?? '-')) ?></span>
                                        </div>
                                        <?php if ($canEdit): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-open-po-header-modal"
                                                data-toggle="modal"
                                                data-target="#modal-edit-po-header"
                                                data-po-header-id="<?= (int) ($header['id_po_header'] ?? 0) ?>"
                                                data-parent-po-header-id="<?= (int) ($header['parent_po_header_id'] ?? 0) ?>"
                                                data-po-type="<?= htmlspecialchars((string) ($header['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>"
                                                data-po-category="<?= htmlspecialchars((string) ($header['po_category'] ?? 'INITIAL'), ENT_QUOTES) ?>"
                                                data-status-po="<?= htmlspecialchars((string) ($header['status_po'] ?? 'ISSUED'), ENT_QUOTES) ?>"
                                                data-po-number="<?= htmlspecialchars((string) ($header['po_number'] ?? ''), ENT_QUOTES) ?>"
                                                data-po-date="<?= htmlspecialchars((string) ($header['po_date'] ?? ''), ENT_QUOTES) ?>"
                                                data-po-value="<?= htmlspecialchars((string) ($header['po_value'] ?? ''), ENT_QUOTES) ?>"
                                                data-ny-po-ref="<?= htmlspecialchars((string) ($header['po_monitor_ny_ref'] ?? ''), ENT_QUOTES) ?>"
                                                data-po-version-label="<?= htmlspecialchars((string) ($header['po_version_label'] ?? ''), ENT_QUOTES) ?>"
                                                data-remark-po="<?= htmlspecialchars((string) ($header['remark_po'] ?? ''), ENT_QUOTES) ?>">
                                                Edit Header
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3"><strong>Tanggal PO</strong><div><?= !empty($header['po_date']) ? htmlspecialchars((string) $header['po_date']) : '-' ?></div></div>
                                        <div class="col-md-3"><strong>Nilai PO</strong><div><?= poMyRepValue((float) ($header['po_value'] ?? 0)) ?></div></div>
                                        <div class="col-md-3"><strong>Versi</strong><div><?= !empty($header['po_version_label']) ? htmlspecialchars((string) $header['po_version_label']) : '-' ?></div></div>
                                        <div class="col-md-3"><strong>Remark</strong><div><?= !empty($header['remark_po']) ? htmlspecialchars((string) $header['remark_po']) : '-' ?></div></div>
                                    </div>
                                    <div class="mt-3">
                                        <?php if ($canEdit): ?>
                                            <form method="post" action="<?= base_url('PO_MyRep/setPoNyRef') ?>" class="form-inline">
                                                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                <input type="hidden" name="id_po_header" value="<?= (int) ($header['id_po_header'] ?? 0) ?>">
                                                <label class="mr-2 mb-2 mb-sm-0"><strong>NY PO REF</strong></label>
                                                <input
                                                    type="text"
                                                    name="ny_po_ref"
                                                    class="form-control form-control-sm mr-2"
                                                    placeholder="NY-123"
                                                    value="<?= htmlspecialchars((string) ($header['po_monitor_ny_ref'] ?? ''), ENT_QUOTES) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Update Ref</button>
                                            </form>
                                        <?php else: ?>
                                            <strong>NY PO REF</strong>
                                            <div><?= !empty($header['po_monitor_ny_ref']) ? htmlspecialchars((string) $header['po_monitor_ny_ref']) : '-' ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered table-sm po-termin-table">
                                            <thead>
                                                <tr>
                                                    <th>Termin</th>
                                                    <th>%</th>
                                                    <th>Invoice</th>
                                                    <th>Outstanding</th>
                                                    <th>Status</th>
                                                    <th>Sertifikat</th>
                                                    <th>No Invoice</th>
                                                    <th>Tgl Invoice</th>
                                                    <th>Tgl BAST</th>
                                                    <th>Tgl Payment</th>
                                                    <th>Remark</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $terminInvoiceTotal = 0;
                                                $terminOutstandingTotal = 0;
                                                ?>
                                                <?php foreach (($header['termin_rows'] ?? []) as $termin): ?>
                                                    <?php
                                                    $hasInvoiceDate = poMyRepHasInvoiceDate($termin['invoice_date'] ?? null);
                                                    $terminInvoiceValue = $hasInvoiceDate ? poMyRepTerminInvoiceValue($termin) : 0;
                                                    $terminOutstandingValue = $hasInvoiceDate ? 0 : poMyRepTerminPlanInvoiceValue($termin);
                                                    $terminInvoiceTotal += $terminInvoiceValue;
                                                    $terminOutstandingTotal += $terminOutstandingValue;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?= (int) ($termin['termin_no'] ?? 0) ?></td>
                                                        <td class="text-center"><?= poMyRepValue((float) ($termin['termin_percent'] ?? 0)) ?>%</td>
                                                        <td class="text-right"><?= abs($terminInvoiceValue) > 0.000001 ? poMyRepValue($terminInvoiceValue) : '-' ?></td>
                                                        <td class="text-right"><?= $terminOutstandingValue > 0 ? poMyRepValue($terminOutstandingValue) : '-' ?></td>
                                                        <td class="text-center"><span class="badge badge-secondary"><?= htmlspecialchars((string) ($termin['status_termin'] ?? '-')) ?></span></td>
                                                        <td class="text-center">
                                                            <?php if ((int) ($termin['termin_no'] ?? 0) >= 2): ?>
                                                                <?php
                                                                $terminCertificateValue = trim((string) ($termin['sertifikat_invoice_date'] ?? ''));
                                                                $terminCertificateReleaseDate = poMyRepCertificateReleaseDate($terminCertificateValue);
                                                                ?>
                                                                <?php if ($terminCertificateReleaseDate !== ''): ?>
                                                                    <span class="badge badge-success"><?= htmlspecialchars($terminCertificateValue, ENT_QUOTES) ?></span>
                                                                <?php elseif ($terminCertificateValue !== ''): ?>
                                                                    <span class="badge badge-secondary"><?= htmlspecialchars($terminCertificateValue, ENT_QUOTES) ?></span>
                                                                    <div class="small text-muted">Text</div>
                                                                <?php else: ?>
                                                                    <span class="badge badge-warning">Waiting</span>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= !empty($termin['invoice_number']) ? htmlspecialchars((string) $termin['invoice_number']) : '-' ?></td>
                                                        <td class="text-center"><?= !empty($termin['invoice_date']) ? htmlspecialchars((string) $termin['invoice_date']) : '-' ?></td>
                                                        <td class="text-center"><?= !empty($termin['bast_date']) ? htmlspecialchars((string) $termin['bast_date']) : '-' ?></td>
                                                        <td class="text-center"><?= !empty($termin['payment_date']) ? htmlspecialchars((string) $termin['payment_date']) : '-' ?></td>
                                                        <td><?= !empty($termin['remark_termin']) ? htmlspecialchars((string) $termin['remark_termin']) : '-' ?></td>
                                                        <td class="text-center">
                                                            <?php if ($canEdit): ?>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-primary js-open-termin-modal"
                                                                    data-toggle="modal"
                                                                    data-target="#modal-termin"
                                                                    data-termin-id="<?= (int) ($termin['id_po_termin'] ?? 0) ?>"
                                                                    data-po-number="<?= htmlspecialchars((string) ($header['po_number'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-termin-no="<?= (int) ($termin['termin_no'] ?? 0) ?>"
                                                                    data-status="<?= htmlspecialchars((string) ($termin['status_termin'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-invoice-number="<?= htmlspecialchars((string) ($termin['invoice_number'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-invoice-value="<?= htmlspecialchars((string) ($termin['invoice_value'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-invoice-date="<?= htmlspecialchars((string) ($termin['invoice_date'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-bast-date="<?= htmlspecialchars((string) ($termin['bast_date'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-payment-date="<?= htmlspecialchars((string) ($termin['payment_date'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-sertifikat="<?= htmlspecialchars((string) ($termin['sertifikat_invoice_date'] ?? ''), ENT_QUOTES) ?>"
                                                                    data-remark="<?= htmlspecialchars((string) ($termin['remark_termin'] ?? ''), ENT_QUOTES) ?>">
                                                                    Update
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2" class="text-right">TOTAL</th>
                                                    <th class="text-right"><?= $terminInvoiceTotal > 0 ? poMyRepValue($terminInvoiceTotal) : '-' ?></th>
                                                    <th class="text-right"><?= $terminOutstandingTotal > 0 ? poMyRepValue($terminOutstandingTotal) : '-' ?></th>
                                                    <th colspan="8"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php if ($canTambah): ?>
<div class="modal fade" id="modal-create-po" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('PO_MyRep/savePo') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah PO MyRep</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Status Flow</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['status_current'] ?? '-')) ?>" readonly></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tipe PO</label><select name="po_type" class="form-control"><?php foreach ($poTypeOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Kategori PO</label><select name="po_category" class="form-control"><?php foreach ($poCategoryOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Status PO</label><select name="status_po" class="form-control"><?php foreach ($poStatusOptions as $value => $label): ?><option value="<?= $value ?>" <?= $value === 'ISSUED' ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Nomor PO</label><input type="text" name="po_number" class="form-control" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Tanggal PO</label><input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Nilai PO</label><input type="text" name="po_value" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>NY PO REF</label><input type="text" name="ny_po_ref" class="form-control" placeholder="NY-123 (opsional)"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Versi</label><input type="text" name="po_version_label" class="form-control" placeholder="Contoh: FINAL 01 / AMANDMENT 01"></div></div>
                        <div class="col-md-8"><div class="form-group"><label>Parent PO</label><select name="parent_po_header_id" class="form-control"><option value="">PO Baru</option><?php foreach (array_merge($poGroups['CLUSTER'], $poGroups['SUBFEEDER']) as $existingPo): ?><option value="<?= (int) ($existingPo['id_po_header'] ?? 0) ?>"><?= htmlspecialchars((string) ($existingPo['po_number'] ?? '-')) ?> - <?= htmlspecialchars((string) ($existingPo['po_category'] ?? '-')) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-12"><div class="form-group mb-0"><label>Remark</label><textarea name="remark_po" class="form-control" rows="3"></textarea></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-muted small mr-auto">5 termin dibuat otomatis dan estimasi mengikuti PO initial/final.</div>
                    <button type="submit" class="btn btn-primary">Simpan PO</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<div class="modal fade" id="modal-edit-po-header" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('PO_MyRep/updatePoHeader') ?>">
                <input type="hidden" name="id_po_header" id="edit_po_header_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Header PO MyRep</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Cluster</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Status Flow</label><input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['status_current'] ?? '-')) ?>" readonly></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tipe PO</label><select name="po_type" id="edit_po_type" class="form-control"><?php foreach ($poTypeOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Kategori PO</label><select name="po_category" id="edit_po_category" class="form-control"><?php foreach ($poCategoryOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Status PO</label><select name="status_po" id="edit_status_po" class="form-control"><?php foreach ($poStatusOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Nomor PO</label><input type="text" name="po_number" id="edit_po_number" class="form-control" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Tanggal PO</label><input type="date" name="po_date" id="edit_po_date" class="form-control" required></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Nilai PO</label><input type="text" name="po_value" id="edit_po_value" class="form-control" required></div></div>
                        <div class="col-md-4"><div class="form-group"><label>NY PO REF</label><input type="text" name="ny_po_ref" id="edit_ny_po_ref" class="form-control" placeholder="NY-123 (opsional)"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Versi</label><input type="text" name="po_version_label" id="edit_po_version_label" class="form-control"></div></div>
                        <div class="col-md-8"><div class="form-group"><label>Parent PO</label><select name="parent_po_header_id" id="edit_parent_po_header_id" class="form-control"><option value="">PO Baru</option><?php foreach (array_merge($poGroups['CLUSTER'], $poGroups['SUBFEEDER']) as $existingPo): ?><option value="<?= (int) ($existingPo['id_po_header'] ?? 0) ?>"><?= htmlspecialchars((string) ($existingPo['po_number'] ?? '-')) ?> - <?= htmlspecialchars((string) ($existingPo['po_category'] ?? '-')) ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-12"><div class="form-group mb-0"><label>Remark</label><textarea name="remark_po" id="edit_remark_po" class="form-control" rows="3"></textarea></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger mr-auto" id="btn-delete-po-header">Hapus PO</button>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Header</button>
                </div>
            </form>
        </div>
    </div>
</div>
<form method="post" action="<?= base_url('PO_MyRep/deletePoHeader') ?>" id="form-delete-po-header" class="d-none">
    <input type="hidden" name="id_po_header" id="delete_po_header_id">
</form>

<div class="modal fade" id="modal-termin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('PO_MyRep/updateTermin') ?>">
                <input type="hidden" name="id_po_termin" id="po_termin_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Update Termin PO</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>PO:</strong> <span id="po_termin_po_number">-</span> |
                        <strong>Termin:</strong> <span id="po_termin_no">-</span> |
                        <strong>Sertifikat:</strong> <span id="po_termin_sertifikat">-</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Status Termin</label><select name="status_termin" id="po_termin_status" class="form-control"><?php foreach ($terminStatusOptions as $value => $label): ?><option value="<?= $value ?>"><?= $label ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Nomor Invoice</label><input type="text" name="invoice_number" id="po_termin_invoice_number" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Nilai Invoice</label><input type="text" name="invoice_value" id="po_termin_invoice_value" class="form-control"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Tanggal Invoice</label><input type="date" name="invoice_date" id="po_termin_invoice_date" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Tanggal BAST</label><input type="date" name="bast_date" id="po_termin_bast_date" class="form-control"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Tanggal Payment</label><input type="date" name="payment_date" id="po_termin_payment_date" class="form-control"></div></div>
                        <div class="col-md-12"><div class="form-group mb-0"><label>Remark</label><textarea name="remark_termin" id="po_termin_remark" class="form-control" rows="3"></textarea></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Update Termin</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canReleaseCertificate): ?>
<div class="modal fade" id="modal-certificate-action" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('PO_MyRep/saveTerminCertificate') ?>">
                <input type="hidden" name="cluster_id" id="certificate_action_cluster_id">
                <input type="hidden" name="id_po_termin" id="certificate_action_termin_id">
                <input type="hidden" name="certificate_mode" id="certificate_action_mode">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="certificate_action_title">Update Sertifikat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>PO:</strong> <span id="certificate_action_po_number">-</span> |
                        <strong>Term:</strong> <span id="certificate_action_term_label">-</span>
                    </div>
                    <div class="form-group">
                        <label id="certificate_action_label">Sertifikat</label>
                        <input type="text" name="sertifikat_invoice" id="certificate_action_value" class="form-control">
                        <small class="form-text text-muted" id="certificate_action_help"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark" id="certificate_action_submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    (function () {
        $(document).on('click', '.js-open-po-header-modal', function () {
            var $button = $(this);
            var headerId = String($button.data('po-header-id') || '');
            var parentHeaderId = String($button.data('parent-po-header-id') || '');

            $('#edit_po_header_id').val(headerId);
            $('#edit_po_type').val($button.data('po-type') || 'CLUSTER');
            $('#edit_po_category').val($button.data('po-category') || 'INITIAL');
            $('#edit_status_po').val($button.data('status-po') || 'ISSUED');
            $('#edit_po_number').val($button.data('po-number') || '');
            $('#edit_po_date').val($button.data('po-date') || '');
            $('#edit_po_value').val($button.data('po-value') || '');
            $('#edit_ny_po_ref').val($button.data('ny-po-ref') || '');
            $('#edit_po_version_label').val($button.data('po-version-label') || '');
            $('#edit_remark_po').val($button.data('remark-po') || '');
            $('#edit_parent_po_header_id option').prop('disabled', false);
            $('#edit_parent_po_header_id option[value="' + headerId + '"]').prop('disabled', true);
            $('#edit_parent_po_header_id').val(parentHeaderId !== '0' && parentHeaderId !== headerId ? parentHeaderId : '');
            $('#delete_po_header_id').val(headerId);
            $('#btn-delete-po-header').data('po-number', $button.data('po-number') || '');
        });

        $(document).on('click', '#btn-delete-po-header', function () {
            var headerId = String($('#edit_po_header_id').val() || '');
            var poNumber = String($(this).data('po-number') || $('#edit_po_number').val() || '-');
            if (!headerId) {
                return;
            }

            var submitDelete = function () {
                $('#delete_po_header_id').val(headerId);
                $('#form-delete-po-header').trigger('submit');
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                Swal.fire({
                    title: 'Hapus PO?',
                    text: 'PO ' + poNumber + ' akan dihapus beserta termin dan mirror PO Monitor terkait.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitDelete();
                    }
                });
                return;
            }

            if (window.confirm('Hapus PO ' + poNumber + '?')) {
                submitDelete();
            }
        });

        $(document).on('click', '.js-open-termin-modal', function () {
            var $button = $(this);
            $('#po_termin_id').val($button.data('termin-id') || '');
            $('#po_termin_po_number').text($button.data('po-number') || '-');
            $('#po_termin_no').text($button.data('termin-no') || '-');
            $('#po_termin_status').val($button.data('status') || 'NOT READY');
            $('#po_termin_invoice_number').val($button.data('invoice-number') || '');
            $('#po_termin_invoice_value').val($button.data('invoice-value') || '');
            $('#po_termin_invoice_date').val($button.data('invoice-date') || '');
            $('#po_termin_bast_date').val($button.data('bast-date') || '');
            $('#po_termin_payment_date').val($button.data('payment-date') || '');
            $('#po_termin_sertifikat').text($button.data('sertifikat') || '-');
            $('#po_termin_remark').val($button.data('remark') || '');
        });

        $(document).on('click', '.js-open-certificate-modal', function () {
            var $button = $(this);
            var mode = String($button.data('mode') || 'claim');
            var certificateValue = String($button.data('certificate') || '');

            $('#certificate_action_cluster_id').val($button.data('cluster-id') || '');
            $('#certificate_action_termin_id').val($button.data('termin-id') || '');
            $('#certificate_action_mode').val(mode);
            $('#certificate_action_po_number').text($button.data('po-number') || '-');
            $('#certificate_action_term_label').text($button.data('term-label') || '-');

            if (mode === 'status') {
                $('#certificate_action_title').text('Update Status Sertifikat');
                $('#certificate_action_label').text('Status Sertifikat');
                $('#certificate_action_value')
                    .attr('type', 'text')
                    .prop('required', false)
                    .attr('placeholder', 'Kosongkan untuk hapus status / isi text status')
                    .val(certificateValue);
                $('#certificate_action_help').text('Status text bebas dan boleh dikosongkan. Input tanggal di mode ini akan disimpan sebagai text status.');
                $('#certificate_action_submit').text('Simpan Status').removeClass('btn-dark').addClass('btn-secondary');
                return;
            }

            $('#certificate_action_title').text('Claim Sertifikat');
            $('#certificate_action_label').text('Tanggal Release Sertifikat');
            $('#certificate_action_value')
                .attr('type', 'date')
                .prop('required', true)
                .attr('placeholder', '')
                .val(/^\d{4}-\d{2}-\d{2}$/.test(certificateValue) ? certificateValue : '');
            $('#certificate_action_help').text('Hanya tanggal valid. Bisa disimpan setelah syarat submitted dan approved terpenuhi.');
            $('#certificate_action_submit').text('Claim Sertifikat').removeClass('btn-secondary').addClass('btn-dark');
        });
    })();
</script>
