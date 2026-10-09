<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canTambah = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'TAMBAH') : true;
$canEdit = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'EDIT') : true;
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'HAPUS') : true;
$canApprovalAction = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('DRM_MyRep', 'APPROVAL') : true;
$currentRoleKeys = isset($this->myrepAccess) && method_exists($this->myrepAccess, 'getCurrentRoleKeys') ? (array) $this->myrepAccess->getCurrentRoleKeys() : [];
$canRejectApprovedDocument = $this->session->userdata('nama_level') === 'Super Admin' || in_array('SND_HO', $currentRoleKeys, true);
$rabDetail = (array) ($rabDetail ?? []);
$rabStatus = strtoupper(trim((string) ($rabDetail['rab_status'] ?? $cluster['rab_status'] ?? '')));
$isRabDone = $rabStatus === 'RAB DONE';
$spkRows = (array) ($spkRows ?? []);
$spkCluster = (array) ($spkRows['CLUSTER'] ?? []);
$spkSubfeeder = (array) ($spkRows['SUBFEEDER'] ?? []);
$isSpkClusterDone = strtoupper(trim((string) ($spkCluster['spk_status'] ?? ''))) === 'SPK DONE';
$isSpkSubfeederDone = strtoupper(trim((string) ($spkSubfeeder['spk_status'] ?? ''))) === 'SPK DONE';
$subfeederRequirementForSpk = strtoupper(trim((string) ($drmScopes['SUBFEEDER']['requirement']['requirement_status'] ?? 'REQUIRED')));
$isSubfeederAvailableForSpk = $subfeederRequirementForSpk !== 'NOT_REQUIRED_APPROVED';
$canManageSpk = !empty($spkReady)
    && !empty($canChecklistRabDone)
    && $isRabDone;
$canInputSpkCluster = $canManageSpk && !$isSpkClusterDone;
$canInputSpkSubfeeder = $canManageSpk && $isSubfeederAvailableForSpk && !$isSpkSubfeederDone;
$canInputSpkGabungan = $canInputSpkCluster && $canInputSpkSubfeeder;
$canShowSpkDoneButton = $canInputSpkCluster || $canInputSpkSubfeeder;
$canShowSpkRollbackButton = $canManageSpk && ($isSpkClusterDone || $isSpkSubfeederDone);
$clusterBoqHeaderForRab = (array) ($drmScopes['CLUSTER']['boqHeader'] ?? []);
$canShowRabDoneButton = !empty($rabReady)
    && !empty($canChecklistRabDone)
    && !$isRabDone
    && strtoupper(trim((string) ($clusterBoqHeaderForRab['review_status'] ?? ''))) === 'APPROVED';
$canShowRabRollbackButton = !empty($rabReady)
    && !empty($canChecklistRabDone)
    && $isRabDone;

if (!function_exists('drmDetailBadgeClass')) {
    function drmDetailBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'DONE':
            case 'SPK DONE':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'ON REVIEW':
            case 'WAITING HO':
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

if (!function_exists('drmScopeText')) {
    function drmScopeText($scopeKey)
    {
        return strtoupper(trim((string) $scopeKey)) === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
    }
}

if (!function_exists('drmScopeRequirementLabel')) {
    function drmScopeRequirementLabel($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'NOT_REQUIRED_PENDING':
                return 'Tidak Dibutuhkan - Menunggu Approval';
            case 'NOT_REQUIRED_APPROVED':
                return 'Tidak Dibutuhkan - Approved';
            case 'NOT_REQUIRED_REJECTED':
                return 'Tidak Dibutuhkan - Rejected';
            default:
                return 'Dibutuhkan';
        }
    }
}

if (!function_exists('drmScopeRequirementBadgeClass')) {
    function drmScopeRequirementBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'NOT_REQUIRED_PENDING':
                return 'warning';
            case 'NOT_REQUIRED_APPROVED':
                return 'success';
            case 'NOT_REQUIRED_REJECTED':
                return 'danger';
            default:
                return 'primary';
        }
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
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $flashSuccess ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $flashError ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>

            <style>
                .drm-header-card .card-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.15rem;
                    background: linear-gradient(135deg, #f8fbff, #eef6ff);
                    border-bottom: 1px solid #dbeafe;
                }

                .drm-header-title {
                    min-width: 0;
                }

                .drm-header-eyebrow {
                    display: block;
                    margin-bottom: .2rem;
                    color: #64748b;
                    font-size: .68rem;
                    font-weight: 900;
                    letter-spacing: .08em;
                    text-transform: uppercase;
                }

                .drm-header-title .card-title {
                    color: #0f172a;
                    font-size: 1.05rem;
                    font-weight: 900;
                }

                .drm-header-actions {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    gap: .8rem;
                    margin-left: auto;
                }

                .drm-header-action-copy {
                    text-align: right;
                    line-height: 1.25;
                }

                .drm-header-action-copy span {
                    display: block;
                    color: #0f172a;
                    font-size: .78rem;
                    font-weight: 900;
                }

                .drm-header-action-copy small {
                    display: block;
                    color: #64748b;
                    font-size: .72rem;
                    font-weight: 700;
                }

                .drm-edit-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: .45rem;
                    min-height: 38px;
                    padding: .55rem 1rem;
                    border-radius: 999px;
                    border: 1px solid rgba(30, 64, 175, 0.12);
                    background: linear-gradient(135deg, #103b5a, #1f6da1);
                    color: #fff;
                    font-size: .82rem;
                    font-weight: 900;
                    box-shadow: 0 12px 24px rgba(15, 59, 90, 0.22);
                }

                .drm-edit-btn:hover,
                .drm-edit-btn:focus {
                    color: #fff;
                    background: linear-gradient(135deg, #0f2f49, #185f90);
                    box-shadow: 0 14px 28px rgba(15, 59, 90, 0.28);
                }

                .drm-info-grid strong {
                    display: block;
                    margin-bottom: .2rem;
                    color: #334155;
                }

                .drm-info-grid > div {
                    margin-bottom: 1rem;
                }

                .drm-detail-sections {
                    display: grid;
                    gap: 1rem;
                }

                .drm-detail-section {
                    border: 1px solid #d7e1ec;
                    border-radius: 8px;
                    background: #fff;
                    overflow: hidden;
                    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
                }

                .drm-detail-section__head {
                    position: relative;
                    display: flex;
                    align-items: center;
                    gap: .7rem;
                    padding: .95rem 1rem .95rem 1.15rem;
                    border-bottom: 1px solid #cbd5e1;
                    background: #eaf2fb;
                }

                .drm-detail-section__head::before {
                    content: "";
                    position: absolute;
                    left: 0;
                    top: 0;
                    bottom: 0;
                    width: 5px;
                    background: #2563eb;
                }

                .drm-detail-section__icon {
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex: 0 0 38px;
                    border-radius: 8px;
                    background: #1d4ed8;
                    color: #fff;
                    box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
                }

                .drm-detail-section__title {
                    margin: 0;
                    color: #0f172a;
                    font-size: 1.05rem;
                    font-weight: 800;
                }

                .drm-detail-section__subtitle {
                    margin: .15rem 0 0;
                    color: #334155;
                    font-size: .84rem;
                    font-weight: 600;
                }

                .drm-detail-fields {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 0;
                }

                .drm-detail-field {
                    min-height: 74px;
                    padding: .9rem 1rem;
                    border-right: 1px solid #eef2f7;
                    border-bottom: 1px solid #eef2f7;
                }

                .drm-detail-field:nth-child(4n) {
                    border-right: 0;
                }

                .drm-detail-field__label {
                    display: block;
                    margin-bottom: .32rem;
                    color: #64748b;
                    font-size: .76rem;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 0;
                }

                .drm-detail-field__value {
                    color: #0f172a;
                    font-size: .96rem;
                    font-weight: 600;
                    line-height: 1.35;
                    overflow-wrap: anywhere;
                }

                .drm-detail-field--wide {
                    grid-column: span 2;
                }

                .drm-detail-field--highlight .drm-detail-field__value {
                    font-size: 1.08rem;
                    font-weight: 800;
                }

                .drm-detail-preview {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    max-width: 240px;
                    min-height: 96px;
                    padding: .4rem;
                    border: 1px solid #dbeafe;
                    border-radius: 8px;
                    background: #f8fafc;
                }

                .drm-detail-preview img {
                    max-width: 220px;
                    max-height: 140px;
                    border-radius: 6px;
                    object-fit: contain;
                }

                .drm-rab-detail {
                    white-space: pre-wrap;
                }

                .drm-rab-card {
                    border: 1px solid rgba(148, 163, 184, .22);
                    border-radius: 18px;
                    overflow: hidden;
                    background: #fff;
                    box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
                }

                .drm-rab-card .card-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.2rem;
                    border-bottom: 1px solid #e5edf6;
                    background: #fff;
                }

                .drm-rab-heading {
                    display: flex;
                    align-items: center;
                    min-width: 0;
                    gap: .85rem;
                }

                .drm-rab-heading__icon {
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex: 0 0 38px;
                    border-radius: 10px;
                    background: #ecfdf5;
                    color: #047857;
                }

                .drm-rab-heading__title {
                    margin: 0;
                    color: #0f172a;
                    font-size: 1rem;
                    font-weight: 850;
                }

                .drm-rab-heading__subtitle {
                    margin: .18rem 0 0;
                    color: #475569;
                    font-size: .86rem;
                    font-weight: 600;
                }

                .drm-rab-card .card-body {
                    padding: 1.15rem 1.2rem 1.2rem;
                    background: #fff;
                }

                .drm-rab-content {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) auto;
                    align-items: center;
                    gap: 1rem;
                }

                .drm-rab-detail-box {
                    min-height: 88px;
                    padding: .95rem 1rem;
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    background: #f8fafc;
                }

                .drm-rab-detail-label {
                    display: block;
                    margin-bottom: .35rem;
                    color: #64748b;
                    font-size: .72rem;
                    font-weight: 900;
                    letter-spacing: .06em;
                    text-transform: uppercase;
                }

                .drm-rab-detail {
                    color: #0f172a;
                    font-weight: 700;
                    line-height: 1.5;
                }

                .drm-rab-meta {
                    margin-top: .6rem;
                    color: #64748b;
                    font-size: .78rem;
                    font-weight: 700;
                }

                .drm-rab-actions {
                    min-width: 220px;
                    text-align: right;
                }

                .drm-rab-action-btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: .45rem;
                    min-height: 40px;
                    padding: .58rem 1rem;
                    border-radius: 999px;
                    font-size: .82rem;
                    font-weight: 900;
                    box-shadow: 0 12px 24px rgba(22, 163, 74, .2);
                }

                .drm-rab-done-note {
                    color: #047857;
                    font-weight: 900;
                    margin-bottom: .55rem;
                }

                .drm-rab-help {
                    display: block;
                    max-width: 300px;
                    margin-left: auto;
                    color: #64748b;
                    font-size: .8rem;
                    font-weight: 700;
                    line-height: 1.4;
                }

                .drm-rab-alert {
                    display: flex;
                    align-items: flex-start;
                    gap: .75rem;
                    padding: .85rem .95rem;
                    border: 1px solid #bfdbfe;
                    border-radius: 14px;
                    background: #eff6ff;
                    color: #1e3a8a;
                    font-size: .86rem;
                    font-weight: 700;
                    line-height: 1.45;
                }

                .drm-rab-alert i {
                    margin-top: .16rem;
                    color: #1d4ed8;
                }

                .drm-spk-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: .85rem;
                }

                .drm-spk-scope-card {
                    border: 1px solid #dbeafe;
                    border-radius: 12px;
                    background: linear-gradient(135deg, #ffffff, #f8fbff);
                    padding: .9rem;
                    min-height: 118px;
                }

                .drm-spk-scope-card__head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: .75rem;
                    margin-bottom: .55rem;
                }

                .drm-spk-scope-card__title {
                    color: #1e3a8a;
                    font-size: .92rem;
                    font-weight: 800;
                    text-transform: uppercase;
                }

                .drm-spk-number {
                    color: #0f172a;
                    font-size: 1rem;
                    font-weight: 800;
                    overflow-wrap: anywhere;
                }

                .drm-spk-meta {
                    color: #64748b;
                    font-size: .78rem;
                    font-weight: 600;
                    margin-top: .35rem;
                }

                .drm-spk-option-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                    gap: .75rem;
                }

                .drm-spk-option {
                    position: relative;
                    display: block;
                    margin: 0;
                    cursor: pointer;
                }

                .drm-spk-option input {
                    position: absolute;
                    inset: 0;
                    z-index: 2;
                    width: 100%;
                    height: 100%;
                    margin: 0;
                    opacity: 0;
                    cursor: pointer;
                    appearance: none;
                }

                .drm-spk-option__body {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    min-height: 106px;
                    height: 100%;
                    border: 1px solid #cbd5e1;
                    border-radius: 12px;
                    background: #fff;
                    padding: .85rem;
                    transition: all .18s ease;
                }

                .drm-spk-option input:checked + .drm-spk-option__body {
                    border-color: #2563eb;
                    background: #eff6ff;
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
                }

                .drm-spk-option__title {
                    display: block;
                    color: #0f172a;
                    font-weight: 800;
                    margin-bottom: .25rem;
                    overflow-wrap: anywhere;
                }

                .drm-spk-option__text {
                    display: block;
                    color: #64748b;
                    font-size: .82rem;
                    line-height: 1.35;
                    overflow-wrap: anywhere;
                }

                @media (max-width: 767.98px) {
                    .drm-rab-content {
                        grid-template-columns: 1fr;
                    }

                    .drm-rab-actions,
                    .drm-rab-help {
                        max-width: none;
                        margin-left: 0;
                        text-align: left;
                    }

                    .drm-rab-action-btn {
                        width: 100%;
                    }

                    .drm-spk-grid,
                    .drm-spk-option-grid {
                        grid-template-columns: 1fr;
                    }
                }

                .drm-scope-tabs .nav-link {
                    border-radius: 12px 12px 0 0;
                    font-weight: 700;
                }

                .drm-scope-tabs .nav-link.active {
                    background: #2563eb;
                    color: #fff;
                }

                .drm-doc-card .card-header,
                .drm-boq-card .card-header {
                    background: #fff;
                    color: #0f172a;
                    border-bottom: 1px solid #e5edf6;
                }

                .drm-doc-card {
                    border: 1px solid rgba(148, 163, 184, .22);
                    border-radius: 18px;
                    overflow: hidden;
                    background: #fff;
                    box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
                }

                .drm-doc-card .card-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.2rem;
                }

                .drm-doc-card .card-body {
                    padding: 1.15rem 1.2rem 1.2rem;
                    background: #fff;
                }

                .drm-doc-heading {
                    display: flex;
                    align-items: center;
                    min-width: 0;
                    gap: .85rem;
                }

                .drm-doc-heading__icon {
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex: 0 0 38px;
                    border-radius: 10px;
                    background: #eff6ff;
                    color: #1d4ed8;
                }

                .drm-doc-heading__title {
                    margin: 0;
                    color: #0f172a;
                    font-size: 1rem;
                    font-weight: 800;
                }

                .drm-doc-heading__subtitle {
                    margin: .18rem 0 0;
                    color: #475569;
                    font-size: .86rem;
                    font-weight: 600;
                }

                .drm-doc-toolbar {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: .75rem;
                    margin-bottom: 1rem;
                    padding: .78rem .85rem;
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    background: #f8fafc;
                }

                .drm-doc-toolbar__hint {
                    color: #64748b;
                    font-size: .82rem;
                    font-weight: 700;
                }

                .drm-doc-toolbar__actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .45rem;
                }

                .drm-doc-table-wrap {
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    overflow: hidden;
                    background: #fff;
                }

                .drm-doc-table {
                    min-width: 1180px;
                    margin-bottom: 0 !important;
                    color: #1f2937;
                    font-size: .82rem;
                    border-collapse: separate !important;
                    border-spacing: 0;
                }

                .drm-doc-table thead th,
                .drm-boq-card .table thead th {
                    padding: .72rem .7rem;
                    background: linear-gradient(180deg, #f8fbff 0%, #eaf2fb 100%);
                    color: #334155;
                    border-top: 0;
                    border-bottom: 1px solid #cbd5e1;
                    font-size: .68rem;
                    font-weight: 900;
                    letter-spacing: .05em;
                    text-transform: uppercase;
                    white-space: nowrap;
                    vertical-align: middle;
                }

                .drm-doc-table tbody td {
                    padding: .68rem .7rem;
                    border-top: 1px solid #e5edf6;
                    vertical-align: middle;
                    line-height: 1.35;
                }

                .drm-doc-table tbody tr:nth-child(even) {
                    background: #f8fafc;
                }

                .drm-doc-table tbody tr:hover {
                    background: #eff6ff;
                }

                .drm-doc-name-cell {
                    display: block;
                    color: #0f172a;
                    font-weight: 850;
                    overflow-wrap: anywhere;
                }

                .drm-doc-note-cell,
                .drm-doc-remark-cell {
                    color: #475569;
                    font-weight: 600;
                    overflow-wrap: anywhere;
                }

                .drm-doc-muted {
                    color: #64748b;
                    font-size: .78rem;
                    font-weight: 700;
                }

                .drm-doc-table .badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 98px;
                    min-height: 25px;
                    padding: .35rem .58rem;
                    border: 1px solid transparent;
                    border-radius: 999px;
                    font-size: .68rem;
                    font-weight: 800;
                    line-height: 1.2;
                    text-align: center;
                    white-space: normal;
                }

                .drm-doc-table .badge-info,
                .drm-doc-table .badge-primary {
                    background: #dbeafe;
                    border-color: #bfdbfe;
                    color: #1e40af;
                }

                .drm-doc-table .badge-warning {
                    background: #fef3c7;
                    border-color: #fde68a;
                    color: #92400e;
                }

                .drm-doc-table .badge-success {
                    background: #dcfce7;
                    border-color: #bbf7d0;
                    color: #166534;
                }

                .drm-doc-table .badge-danger {
                    background: #fee2e2;
                    border-color: #fecaca;
                    color: #991b1b;
                }

                .drm-doc-table .badge-secondary {
                    background: #f1f5f9;
                    border-color: #e2e8f0;
                    color: #475569;
                }

                .drm-doc-table .btn-sm,
                .drm-doc-toolbar .btn-sm {
                    border-radius: 999px;
                    font-size: .72rem;
                    font-weight: 700;
                    padding: .32rem .62rem;
                }

                .drm-doc-file-link {
                    max-width: 260px;
                    overflow-wrap: anywhere;
                    font-weight: 700;
                }

                .drm-doc-file-actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .25rem;
                    margin-top: .35rem;
                }

                .drm-doc-file-actions .btn {
                    padding: .18rem .45rem;
                    font-size: .78rem;
                    line-height: 1.25;
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

                .modal-content.drm-modal,
                .drm-modal .modal-content {
                    border: 0;
                    border-radius: 18px;
                    overflow: hidden;
                    box-shadow: 0 22px 46px rgba(15, 23, 42, 0.18);
                }

                .drm-modal__header {
                    border-bottom: 0;
                    padding: 1.15rem 1.25rem;
                    background:
                        radial-gradient(circle at top right, rgba(255, 255, 255, .18), transparent 30%),
                        linear-gradient(135deg, #103b5a 0%, #1f6da1 58%, #53a9d8 100%);
                    color: #fff;
                }

                .drm-modal__eyebrow {
                    display: inline-block;
                    margin-bottom: .35rem;
                    color: rgba(255, 255, 255, .76);
                    font-size: .72rem;
                    font-weight: 900;
                    letter-spacing: .08em;
                    text-transform: uppercase;
                }

                .drm-modal__subtitle {
                    max-width: 720px;
                    color: rgba(255, 255, 255, .84);
                    font-size: .86rem;
                    font-weight: 600;
                    line-height: 1.4;
                }

                .drm-modal .modal-body {
                    background: #f8fafc;
                    padding: 1.25rem;
                }

                .drm-modal .modal-footer {
                    border-top: 0;
                    background: #f8fafc;
                    gap: .65rem;
                }

                .drm-form-box {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    padding: 1rem 1.1rem;
                    margin-bottom: 1rem;
                    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
                }

                .drm-form-box:last-child {
                    margin-bottom: 0;
                }

                .drm-form-box__title {
                    font-size: .94rem;
                    font-weight: 800;
                    color: #0f172a;
                    margin-bottom: .85rem;
                }

                .drm-form-box label {
                    color: #475569;
                    font-size: .72rem;
                    font-weight: 900;
                    letter-spacing: .06em;
                    text-transform: uppercase;
                }

                .drm-form-box .form-control {
                    min-height: 42px;
                    border: 1px solid #d7e0ea;
                    border-radius: 10px;
                    background-color: #fbfdff;
                    color: #0f172a;
                    font-weight: 600;
                    box-shadow: none;
                    transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
                }

                .drm-form-box .form-control:not([readonly]):not(:disabled),
                .drm-form-box select.form-control:not(:disabled) {
                    background-color: #ffffff;
                    border-color: #b9d4f2;
                    box-shadow: inset 3px 0 0 #3b82f6;
                }

                .drm-form-box .form-control:not([readonly]):not(:disabled):focus,
                .drm-form-box select.form-control:not(:disabled):focus {
                    border-color: #2563eb;
                    background-color: #ffffff;
                    box-shadow: inset 3px 0 0 #2563eb, 0 0 0 3px rgba(37, 99, 235, .12);
                }

                .drm-form-box .form-control[readonly],
                .drm-form-box .form-control:disabled,
                .drm-form-box select.form-control:disabled {
                    background-color: #f1f5f9;
                    border-color: #e2e8f0;
                    color: #64748b;
                    box-shadow: inset 3px 0 0 #cbd5e1;
                    cursor: not-allowed;
                }

                .drm-form-box textarea.form-control {
                    min-height: 92px;
                    line-height: 1.45;
                }

                .drm-boq-summary-card {
                    border: 1px solid rgba(148, 163, 184, .22);
                    border-radius: 18px;
                    overflow: hidden;
                    background: #fff;
                    box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
                }

                .drm-boq-summary-card .card-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.2rem;
                    border-bottom: 1px solid #e5edf6;
                    background: #fff;
                }

                .drm-boq-summary-heading {
                    display: flex;
                    align-items: center;
                    min-width: 0;
                    gap: .85rem;
                }

                .drm-boq-summary-heading__icon {
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex: 0 0 38px;
                    border-radius: 10px;
                    background: #eff6ff;
                    color: #1d4ed8;
                }

                .drm-boq-summary-title {
                    margin: 0;
                    color: #0f172a;
                    font-size: 1rem;
                    font-weight: 800;
                }

                .drm-boq-summary-subtitle {
                    margin: .18rem 0 0;
                    color: #475569;
                    font-size: .86rem;
                    font-weight: 600;
                }

                .drm-boq-summary-card .card-body {
                    padding: 1.15rem 1.2rem 1.2rem;
                    background: #fff;
                }

                .drm-boq-summary-table-wrap {
                    border: 1px solid #e2e8f0;
                    border-radius: 14px;
                    overflow: hidden;
                    background: #fff;
                }

                .drm-boq-summary-table {
                    min-width: 880px;
                    margin-bottom: 0 !important;
                    color: #1f2937;
                    font-size: .82rem;
                    border-collapse: separate !important;
                    border-spacing: 0;
                }

                .drm-boq-summary-table thead th {
                    padding: .72rem .7rem;
                    background: linear-gradient(180deg, #f8fbff 0%, #eaf2fb 100%);
                    color: #334155;
                    border-top: 0;
                    border-bottom: 1px solid #cbd5e1;
                    font-size: .68rem;
                    font-weight: 900;
                    letter-spacing: .05em;
                    text-transform: uppercase;
                    white-space: nowrap;
                    vertical-align: middle;
                }

                .drm-boq-summary-table tbody td,
                .drm-boq-summary-table tfoot td {
                    padding: .65rem .7rem;
                    border-top: 1px solid #e5edf6;
                    vertical-align: middle;
                    line-height: 1.35;
                }

                .drm-boq-summary-table tbody tr:nth-child(even) {
                    background: #f8fafc;
                }

                .drm-boq-summary-table tbody tr:hover {
                    background: #eff6ff;
                }

                .drm-boq-summary-table td:nth-child(4),
                .drm-boq-summary-table td:nth-child(5),
                .drm-boq-summary-table td:nth-child(6) {
                    color: #0f172a;
                    font-weight: 800;
                    text-align: right;
                    font-variant-numeric: tabular-nums;
                }

                .drm-boq-summary-table thead th:nth-child(4),
                .drm-boq-summary-table thead th:nth-child(5),
                .drm-boq-summary-table thead th:nth-child(6) {
                    text-align: right;
                }

                .drm-boq-summary-table .drm-boq-total-row td {
                    background: #f1f5f9;
                    color: #0f172a;
                    font-size: .8rem;
                    font-weight: 900;
                    border-top: 1px solid #cbd5e1;
                }

                .drm-boq-summary-table .drm-boq-total-row td:not(:first-child) {
                    text-align: right;
                    font-variant-numeric: tabular-nums;
                }

                .drm-boq-summary-table .boq-zero-cell {
                    background: transparent !important;
                    color: #94a3b8;
                    font-weight: 800;
                }

                .drm-boq-empty-state {
                    padding: 1.15rem;
                    border: 1px dashed #cbd5e1;
                    border-radius: 14px;
                    background: #f8fafc;
                    color: #64748b;
                    font-weight: 700;
                    text-align: center;
                }
                .boq-zero-cell {
                    background-color: #fdecec !important;
                    color: #9f1239;
                    font-weight: 600;
                }
                .doc-history-list {
                    display: grid;
                    gap: .75rem;
                    list-style: none;
                    margin: 0;
                    padding: 0;
                }

                .doc-history-item {
                    border: 1px solid #dbe7f3;
                    border-left: 4px solid #1f6da1;
                    border-radius: 12px;
                    padding: .9rem 1rem;
                    background: #fff;
                    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
                }

                .doc-history-item:last-child {
                    margin-bottom: 0;
                }

                .doc-history-title {
                    display: inline-flex;
                    align-items: center;
                    padding: .2rem .55rem;
                    border-radius: 999px;
                    background: #eaf4fb;
                    color: #103b5a;
                    font-size: .76rem;
                    font-weight: 900;
                    text-transform: uppercase;
                }

                .doc-history-meta {
                    color: #64748b;
                    font-size: .82rem;
                    font-weight: 700;
                    margin: .55rem 0 .35rem;
                }

                .doc-history-detail {
                    color: #334155;
                    font-size: .86rem;
                    line-height: 1.45;
                }

                .doc-history-detail strong {
                    color: #0f172a;
                    font-weight: 900;
                }

                .doc-history-empty {
                    padding: 1rem;
                    border: 1px dashed #cbd5e1;
                    border-radius: 12px;
                    background: #fff;
                    color: #64748b;
                    font-weight: 800;
                    text-align: center;
                }

                .drm-bulk-summary {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 1rem;
                    padding: 1rem 1.1rem;
                    border: 1px solid #dbeafe;
                    border-radius: 18px;
                    background: linear-gradient(135deg, #f8fbff, #eef6ff);
                    margin-bottom: 1rem;
                }

                .drm-bulk-summary__title {
                    font-size: 1rem;
                    font-weight: 800;
                    color: #0f172a;
                    margin-bottom: .2rem;
                }

                .drm-bulk-summary__text {
                    margin: 0;
                    color: #64748b;
                    font-size: .92rem;
                }

                .drm-bulk-summary__badge {
                    flex-shrink: 0;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 52px;
                    min-height: 52px;
                    padding: .4rem .85rem;
                    border-radius: 16px;
                    background: #1d4ed8;
                    color: #fff;
                    font-size: 1.05rem;
                    font-weight: 800;
                    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.22);
                }

                .drm-bulk-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
                    gap: 1rem;
                }

                .drm-bulk-card {
                    border: 1px solid #dbe4f0;
                    border-radius: 20px;
                    background: #fff;
                    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
                    overflow: hidden;
                }

                .drm-bulk-card__header {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: .9rem;
                    padding: 1rem 1.1rem .85rem;
                    background: linear-gradient(135deg, #fbfdff, #f4f8fc);
                    border-bottom: 1px solid #e5edf6;
                }

                .drm-bulk-card__eyebrow {
                    font-size: .72rem;
                    font-weight: 800;
                    letter-spacing: .08em;
                    text-transform: uppercase;
                    color: #64748b;
                }

                .drm-bulk-card__title {
                    margin: .2rem 0 0;
                    font-size: 1rem;
                    font-weight: 700;
                    color: #0f172a;
                }

                .drm-bulk-card__body {
                    padding: 1rem 1.1rem 1.1rem;
                }

                @media (max-width: 767.98px) {
                    .drm-header-card .card-header,
                    .drm-header-actions {
                        align-items: stretch;
                        flex-direction: column;
                    }

                    .drm-header-action-copy {
                        text-align: left;
                    }

                    .drm-edit-btn {
                        justify-content: center;
                        width: 100%;
                    }

                    .drm-detail-fields {
                        grid-template-columns: 1fr;
                    }

                    .drm-detail-field,
                    .drm-detail-field:nth-child(4n) {
                        border-right: 0;
                    }

                    .drm-detail-field--wide {
                        grid-column: span 1;
                    }

                    .drm-detail-section__head {
                        align-items: flex-start;
                    }
                }

                @media (min-width: 768px) and (max-width: 1199.98px) {
                    .drm-detail-fields {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }

                    .drm-detail-field:nth-child(4n) {
                        border-right: 1px solid #eef2f7;
                    }

                    .drm-detail-field:nth-child(2n) {
                        border-right: 0;
                    }
                }
            </style>

            <div class="card card-primary shadow-sm drm-header-card">
                <div class="card-header">
                    <div class="drm-header-title">
                        <span class="drm-header-eyebrow">Detail Cluster</span>
                        <h3 class="card-title mb-0">Header DRM</h3>
                    </div>
                    <?php if ($canEdit): ?>
                        <div class="drm-header-actions">
                            <div class="drm-header-action-copy">
                                <span>Perbarui data DRM</span>
                                <small>Header, homepass, OLT, dan catatan</small>
                            </div>
                            <button type="button" class="btn drm-edit-btn" data-toggle="modal" data-target="#modal-drm-edit">
                                <i class="fas fa-pen"></i>
                                Edit DRM
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="drm-detail-sections">
                        <section class="drm-detail-section">
                            <div class="drm-detail-section__head">
                                <span class="drm-detail-section__icon"><i class="fas fa-map-marker-alt"></i></span>
                                <div>
                                    <h4 class="drm-detail-section__title">Lokasi Cluster</h4>
                                    <p class="drm-detail-section__subtitle">Identitas cluster dan posisi area project.</p>
                                </div>
                            </div>
                            <div class="drm-detail-fields">
                                <div class="drm-detail-field drm-detail-field--wide">
                                    <span class="drm-detail-field__label">Cluster</span>
                                    <div class="drm-detail-field__value"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Cluster Code</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['cluster_code']) ? htmlspecialchars((string) $cluster['cluster_code']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Status Flow</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['status_current']) ? htmlspecialchars((string) $cluster['status_current']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Kota</span>
                                    <div class="drm-detail-field__value"><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Provinsi</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['province_name']) ? htmlspecialchars((string) $cluster['province_name']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Regional</span>
                                    <div class="drm-detail-field__value"><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></div>
                                </div>
                            </div>
                        </section>

                        <section class="drm-detail-section">
                            <div class="drm-detail-section__head">
                                <span class="drm-detail-section__icon"><i class="fas fa-users-cog"></i></span>
                                <div>
                                    <h4 class="drm-detail-section__title">Tim Project</h4>
                                    <p class="drm-detail-section__subtitle">PIC dan struktur tim yang bertanggung jawab.</p>
                                </div>
                            </div>
                            <div class="drm-detail-fields">
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Team</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['team_name']) ? htmlspecialchars((string) $cluster['team_name']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">PIC Project</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['pic_project']) ? htmlspecialchars((string) $cluster['pic_project']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Chief</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['chief']) ? htmlspecialchars((string) $cluster['chief']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">RPM</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['rpm']) ? htmlspecialchars((string) $cluster['rpm']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">SM</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['sm']) ? htmlspecialchars((string) $cluster['sm']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">SPV</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['spv']) ? htmlspecialchars((string) $cluster['spv']) : '-' ?></div>
                                </div>
                            </div>
                        </section>

                        <section class="drm-detail-section">
                            <div class="drm-detail-section__head">
                                <span class="drm-detail-section__icon"><i class="fas fa-clipboard-check"></i></span>
                                <div>
                                    <h4 class="drm-detail-section__title">Progress DRM</h4>
                                    <p class="drm-detail-section__subtitle">Status DRM, RAB, NTP, dan pencapaian homepass.</p>
                                </div>
                            </div>
                            <div class="drm-detail-fields">
                                <div class="drm-detail-field drm-detail-field--highlight">
                                    <span class="drm-detail-field__label">HP DRM</span>
                                    <div class="drm-detail-field__value"><?= !is_null($cluster['homepass_drm'] ?? null) ? number_format((float) $cluster['homepass_drm'], 0, ',', '.') : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">HP Plan</span>
                                    <div class="drm-detail-field__value"><?= number_format((float) ($cluster['hp_plan'] ?? 0), 0, ',', '.') ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">HP Donasi</span>
                                    <div class="drm-detail-field__value"><?= number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.') ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Tanggal DRM</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Status DRM</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['display_status_drm']) ? htmlspecialchars((string) $cluster['display_status_drm']) : (!empty($cluster['status_drm']) ? htmlspecialchars((string) $cluster['status_drm']) : 'WAITING INPUT') ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Status RAB</span>
                                    <div class="drm-detail-field__value">
                                        <span class="badge badge-<?= drmDetailBadgeClass($isRabDone ? 'APPROVED' : '') ?>"><?= htmlspecialchars($isRabDone ? 'RAB DONE' : 'BELUM RAB DONE') ?></span>
                                        <?php if ($isRabDone && !empty($rabDetail['rab_done_at'])): ?>
                                            <div class="small text-muted mt-1"><?= htmlspecialchars((string) $rabDetail['rab_done_at']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Status SPK</span>
                                    <div class="drm-detail-field__value">
                                        <div>Cluster: <span class="badge badge-<?= drmDetailBadgeClass($isSpkClusterDone ? 'SPK DONE' : '') ?>"><?= htmlspecialchars($isSpkClusterDone ? 'SPK DONE' : 'BELUM SPK') ?></span></div>
                                        <?php if ($isSubfeederAvailableForSpk): ?>
                                            <div class="mt-1">Subfeeder: <span class="badge badge-<?= drmDetailBadgeClass($isSpkSubfeederDone ? 'SPK DONE' : '') ?>"><?= htmlspecialchars($isSpkSubfeederDone ? 'SPK DONE' : 'BELUM SPK') ?></span></div>
                                        <?php else: ?>
                                            <div class="mt-1">Subfeeder: <span class="badge badge-success">TIDAK DIBUTUHKAN</span></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">NTP Name</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['ntp_name']) ? htmlspecialchars((string) $cluster['ntp_name']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">NTP Date / Year</span>
                                    <div class="drm-detail-field__value">
                                        <?= !empty($cluster['ntp_date']) ? htmlspecialchars((string) $cluster['ntp_date']) : '-' ?>
                                        <?php if (!empty($cluster['ntp_year'])): ?>
                                            <div class="small text-muted mt-1">Tahun <?= htmlspecialchars((string) $cluster['ntp_year']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="drm-detail-field drm-detail-field--wide">
                                    <span class="drm-detail-field__label">Screenshot Astri</span>
                                    <div class="drm-detail-field__value">
                                        <?php if (!empty($cluster['screenshot_astri_path'])): ?>
                                            <a href="<?= base_url((string) $cluster['screenshot_astri_path']) ?>" target="_blank" class="drm-detail-preview">
                                                <img src="<?= base_url((string) $cluster['screenshot_astri_path']) ?>" alt="Screenshot Astri">
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="drm-detail-section">
                            <div class="drm-detail-section__head">
                                <span class="drm-detail-section__icon"><i class="fas fa-sticky-note"></i></span>
                                <div>
                                    <h4 class="drm-detail-section__title">Catatan & Outstanding</h4>
                                    <p class="drm-detail-section__subtitle">Informasi tambahan untuk tindak lanjut proses DRM.</p>
                                </div>
                            </div>
                            <div class="drm-detail-fields">
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Released At</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['released_at']) ? htmlspecialchars((string) $cluster['released_at']) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field drm-detail-field--wide">
                                    <span class="drm-detail-field__label">Remark DRM</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['remark_drm']) ? nl2br(htmlspecialchars((string) $cluster['remark_drm'])) : '-' ?></div>
                                </div>
                                <div class="drm-detail-field">
                                    <span class="drm-detail-field__label">Outstanding Progress</span>
                                    <div class="drm-detail-field__value"><?= !empty($cluster['outstanding_progress']) ? nl2br(htmlspecialchars((string) $cluster['outstanding_progress'])) : '-' ?></div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <?php
            $boqClusterTotal = 0;
            $boqSubfeederTotal = 0;
            $boqClusterItems = (array) (($drmScopes['CLUSTER']['boqItems'] ?? []));
            $boqSubfeederItems = (array) (($drmScopes['SUBFEEDER']['boqItems'] ?? []));
            foreach ($boqClusterItems as $clusterBoqItem) {
                $boqClusterTotal += (float) ($clusterBoqItem['qty_boq'] ?? 0);
            }
            foreach ($boqSubfeederItems as $subfeederBoqItem) {
                $boqSubfeederTotal += (float) ($subfeederBoqItem['qty_boq'] ?? 0);
            }
            $boqTotal = $boqClusterTotal + $boqSubfeederTotal;

            $boqCombinedRowsMap = [];
            foreach ($boqClusterItems as $clusterBoqItem) {
                $boqItemId = (int) ($clusterBoqItem['id_boq_item'] ?? 0);
                if ($boqItemId <= 0) {
                    continue;
                }
                if (!isset($boqCombinedRowsMap[$boqItemId])) {
                    $boqCombinedRowsMap[$boqItemId] = [
                        'id_boq_item' => $boqItemId,
                        'item_name' => (string) ($clusterBoqItem['item_name'] ?? '-'),
                        'item_type' => (string) ($clusterBoqItem['item_type'] ?? '-'),
                        'sort_no' => (int) ($clusterBoqItem['sort_no'] ?? 0),
                        'qty_cluster' => 0,
                        'qty_subfeeder' => 0,
                    ];
                }
                $boqCombinedRowsMap[$boqItemId]['qty_cluster'] = (float) ($clusterBoqItem['qty_boq'] ?? 0);
            }
            foreach ($boqSubfeederItems as $subfeederBoqItem) {
                $boqItemId = (int) ($subfeederBoqItem['id_boq_item'] ?? 0);
                if ($boqItemId <= 0) {
                    continue;
                }
                if (!isset($boqCombinedRowsMap[$boqItemId])) {
                    $boqCombinedRowsMap[$boqItemId] = [
                        'id_boq_item' => $boqItemId,
                        'item_name' => (string) ($subfeederBoqItem['item_name'] ?? '-'),
                        'item_type' => (string) ($subfeederBoqItem['item_type'] ?? '-'),
                        'sort_no' => (int) ($subfeederBoqItem['sort_no'] ?? 0),
                        'qty_cluster' => 0,
                        'qty_subfeeder' => 0,
                    ];
                }
                $boqCombinedRowsMap[$boqItemId]['qty_subfeeder'] = (float) ($subfeederBoqItem['qty_boq'] ?? 0);
            }

            $boqCombinedRows = array_values(array_filter($boqCombinedRowsMap, static function ($row) {
                $qtyCluster = (float) ($row['qty_cluster'] ?? 0);
                $qtySubfeeder = (float) ($row['qty_subfeeder'] ?? 0);
                return ($qtyCluster + $qtySubfeeder) > 0;
            }));
            usort($boqCombinedRows, static function ($a, $b) {
                $sortA = (int) ($a['sort_no'] ?? 0);
                $sortB = (int) ($b['sort_no'] ?? 0);
                if ($sortA === $sortB) {
                    return (int) ($a['id_boq_item'] ?? 0) <=> (int) ($b['id_boq_item'] ?? 0);
                }
                return $sortA <=> $sortB;
            });
            ?>

            <div class="card drm-boq-summary-card">
                <div class="card-header">
                    <div class="drm-boq-summary-heading">
                        <span class="drm-boq-summary-heading__icon"><i class="fas fa-layer-group"></i></span>
                        <div>
                            <h3 class="drm-boq-summary-title">Ringkasan BOQ</h3>
                            <p class="drm-boq-summary-subtitle">Rekap item BOQ implementasi dari scope cluster dan subfeeder.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($boqCombinedRows)): ?>
                        <div class="drm-form-box mb-0">
                            <div class="drm-form-box__title">Baseline BOQ Implementasi Cluster</div>
                            <div class="table-responsive drm-boq-summary-table-wrap">
                                <table class="table table-hover drm-boq-summary-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>BOQ Cluster</th>
                                            <th>BOQ Subfeeder</th>
                                            <th>Total BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqCombinedRows as $index => $item): ?>
                                            <?php
                                            $qtyCluster = (float) ($item['qty_cluster'] ?? 0);
                                            $qtySubfeeder = (float) ($item['qty_subfeeder'] ?? 0);
                                            $qtyItemTotal = $qtyCluster + $qtySubfeeder;
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td class="<?= abs($qtyCluster) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtyCluster) < 0.00001 ? '-' : number_format($qtyCluster, 0, ',', '.') ?>
                                                </td>
                                                <td class="<?= abs($qtySubfeeder) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtySubfeeder) < 0.00001 ? '-' : number_format($qtySubfeeder, 0, ',', '.') ?>
                                                </td>
                                                <td class="<?= abs($qtyItemTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                    <?= abs($qtyItemTotal) < 0.00001 ? '-' : number_format($qtyItemTotal, 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="drm-boq-total-row">
                                            <td colspan="3" class="text-right">TOTAL</td>
                                            <td class="<?= abs($boqClusterTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqClusterTotal) < 0.00001 ? '-' : number_format($boqClusterTotal, 0, ',', '.') ?>
                                            </td>
                                            <td class="<?= abs($boqSubfeederTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqSubfeederTotal) < 0.00001 ? '-' : number_format($boqSubfeederTotal, 0, ',', '.') ?>
                                            </td>
                                            <td class="<?= abs($boqTotal) < 0.00001 ? 'boq-zero-cell' : '' ?>">
                                                <?= abs($boqTotal) < 0.00001 ? '-' : number_format($boqTotal, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="drm-boq-empty-state">Belum ada item BOQ yang bisa diringkas.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs drm-scope-tabs px-3 pt-2 border-bottom-0" role="tablist">
                        <?php $tabIndex = 0; ?>
                        <?php foreach ($drmScopes as $scopeKey => $scope): ?>
                            <?php
                            $scopeRequirement = (array) ($scope['requirement'] ?? []);
                            $scopeRequirementStatus = strtoupper(trim((string) ($scopeRequirement['requirement_status'] ?? 'REQUIRED')));
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>" data-toggle="tab" href="#tab-drm-<?= strtolower($scopeKey) ?>" role="tab">
                                    <?= htmlspecialchars((string) ($scope['label'] ?? drmScopeText($scopeKey))) ?>
                                    <?php if ($scopeKey === 'SUBFEEDER' && $scopeRequirementStatus !== 'REQUIRED'): ?>
                                        <span class="badge badge-<?= drmScopeRequirementBadgeClass($scopeRequirementStatus) ?> ml-1"><?= htmlspecialchars(drmScopeRequirementLabel($scopeRequirementStatus)) ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php $tabIndex++; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php $tabIndex = 0; ?>
                        <?php foreach ($drmScopes as $scopeKey => $scope): ?>
                            <?php
                            $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
                            $scopeRequirement = (array) ($scope['requirement'] ?? []);
                            $scopeRequirementStatus = strtoupper(trim((string) ($scopeRequirement['requirement_status'] ?? 'REQUIRED')));
                            $isSubfeederScope = $scopeKey === 'SUBFEEDER';
                            $isSubfeederNotRequiredPending = $isSubfeederScope && $scopeRequirementStatus === 'NOT_REQUIRED_PENDING';
                            $isSubfeederNotRequiredApproved = $isSubfeederScope && $scopeRequirementStatus === 'NOT_REQUIRED_APPROVED';
                            $isSubfeederWorkflowLocked = $isSubfeederNotRequiredPending || $isSubfeederNotRequiredApproved;
                            $documentRows = (array) ($scope['documentRows'] ?? []);
                            $boqHeader = (array) ($scope['boqHeader'] ?? []);
                            $boqItems = (array) ($scope['boqItems'] ?? []);
                            $boqBaselineItems = (array) ($scope['boqBaselineItems'] ?? []);
                            $apdBoqFile = (array) ($scope['apdBoqFile'] ?? []);
                            $scopeReady = !empty($scope['isReady']);
                            $boqReviewStatus = strtoupper(trim((string) ($boqHeader['review_status'] ?? 'DRAFT')));
                            $isBoqLocked = $boqReviewStatus === 'APPROVED';
                            $hasApdBoqFile = !empty($apdBoqFile['id_doc_file']);
                            $bulkUploadableRows = [];
                            $reviewableRows = [];
                            $downloadableRows = [];
                            foreach ($documentRows as $docRow) {
                                $docNameUpper = strtoupper(trim((string) ($docRow['doc_name'] ?? '')));
                                $docStatus = drmDocumentLabel($docRow);
                                $docRawStatus = strtoupper(trim((string) ($docRow['status_file'] ?? '')));
                                $isApdBoqDoc = $docNameUpper === 'APD BOQ';

                                if (!$isSubfeederWorkflowLocked && !$isApdBoqDoc && (in_array($docStatus, ['BELUM UPLOAD'], true) || $docRawStatus === 'REJECTED')) {
                                    $bulkUploadableRows[] = $docRow;
                                }

                                if (!$isSubfeederWorkflowLocked && !$isApdBoqDoc && !empty($docRow['id_doc_file']) && in_array($docRawStatus, ['UPLOADED', 'REJECTED'], true)) {
                                    $reviewableRows[] = $docRow;
                                }

                                if (!empty($docRow['file_path'])) {
                                    $downloadableRows[] = $docRow;
                                }
                            }
                            ?>
                            <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" id="tab-drm-<?= strtolower($scopeKey) ?>" role="tabpanel">
                                <?php if (!$scopeReady): ?>
                                    <div class="alert alert-warning mb-0">
                                        Struktur <?= htmlspecialchars($scopeLabel) ?> belum siap. Jalankan patch database DRM subfeeder dulu.
                                    </div>
                                <?php else: ?>
                                    <div class="card drm-doc-card">
                                        <div class="card-header">
                                            <div class="drm-doc-heading">
                                                <span class="drm-doc-heading__icon"><i class="fas fa-folder-open"></i></span>
                                                <div>
                                                    <h3 class="drm-doc-heading__title">Dokumen <?= htmlspecialchars($scopeLabel) ?></h3>
                                                    <p class="drm-doc-heading__subtitle">Upload, review, dan pantau dokumen scope <?= htmlspecialchars($scopeLabel) ?>.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($isSubfeederScope && !empty($scopeRequirementReady)): ?>
                                                <div class="alert alert-<?= drmScopeRequirementBadgeClass($scopeRequirementStatus) ?> mb-3">
                                                    <div>
                                                        <div>
                                                            <strong>Status Subfeeder:</strong>
                                                            <?= htmlspecialchars(drmScopeRequirementLabel($scopeRequirementStatus)) ?>
                                                            <?php if (!empty($scopeRequirement['request_remark'])): ?>
                                                                <div class="small mt-1">Alasan request: <?= nl2br(htmlspecialchars((string) $scopeRequirement['request_remark'])) ?></div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($scopeRequirement['review_remark'])): ?>
                                                                <div class="small mt-1">Catatan review: <?= nl2br(htmlspecialchars((string) $scopeRequirement['review_remark'])) ?></div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($scopeRequirement['reopen_remark'])): ?>
                                                                <div class="small mt-1">Catatan aktif kembali: <?= nl2br(htmlspecialchars((string) $scopeRequirement['reopen_remark'])) ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if ($canApprove && $canApprovalAction && $isSubfeederNotRequiredPending): ?>
                                                    <div class="text-center mb-3">
                                                        <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#modal-subfeeder-not-required-approve">
                                                            Approve Tidak Dibutuhkan
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-subfeeder-not-required-reject">
                                                            Reject
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($canApprove && $canApprovalAction && $isSubfeederNotRequiredApproved): ?>
                                                    <div class="text-center mb-3">
                                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-subfeeder-reopen">
                                                            Aktifkan Kembali Subfeeder
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (!$isSubfeederWorkflowLocked): ?>
                                            <div class="drm-doc-toolbar">
                                                <div class="drm-doc-toolbar__hint">
                                                    Bulk upload & approve all tidak mencakup <strong>APD BOQ</strong> dan <strong>Manual BOQ</strong>.
                                                </div>
                                                <div class="drm-doc-toolbar__actions">
                                                    <?php if (!empty($downloadableRows)): ?>
                                                        <a href="<?= base_url('DRM_MyRep/downloadDocumentBundle/' . (int) $cluster['id_myrep_cluster'] . '/' . urlencode((string) $scopeKey)) ?>" class="btn btn-sm btn-outline-dark">
                                                            <i class="fas fa-file-archive mr-1"></i> Download RAR
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($canTambah && !empty($bulkUploadableRows)): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modal-drm-bulk-upload-<?= strtolower($scopeKey) ?>">
                                                            <i class="fas fa-upload mr-1"></i> Bulk Upload
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($canApprove && $canApprovalAction && !empty($reviewableRows)): ?>
                                                        <form method="post" action="<?= base_url('DRM_MyRep/approveAllDocuments') ?>" class="d-inline">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                                                            <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve semua dokumen upload/reject untuk scope ini? APD BOQ dan Manual BOQ tidak ikut.');">
                                                                <i class="fas fa-check-double mr-1"></i> Approve All
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="table-responsive drm-doc-table-wrap">
                                                <table class="table table-hover drm-doc-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Dokumen</th>
                                                            <th>Catatan</th>
                                                            <th>Status</th>
                                                            <th>File</th>
                                                            <th>Upload / Update</th>
                                                            <th>Remarks</th>
                                                            <?php if ($canApprove && $canApprovalAction): ?><th>Review</th><?php endif; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($documentRows as $row): ?>
                                                            <?php
                                                            $docStatus = drmDocumentLabel($row);
                                                            $docRawStatus = strtoupper(trim((string) ($row['status_file'] ?? '')));
                                                            $docCanUpload = !$isSubfeederWorkflowLocked && ($docStatus === 'BELUM UPLOAD' || $docRawStatus === 'REJECTED');
                                                            ?>
                                                            <tr>
                                                                <td><span class="drm-doc-name-cell"><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></span></td>
                                                                <td><span class="drm-doc-note-cell"><?= !empty($row['doc_requirement_note']) ? htmlspecialchars((string) $row['doc_requirement_note']) : '-' ?></span></td>
                                                                <td><span class="badge badge-<?= drmDetailBadgeClass($docStatus) ?>"><?= htmlspecialchars($docStatus) ?></span></td>
                                                                <td>
                                                                    <?php if (!empty($row['file_name'])): ?>
                                                                        <div class="drm-doc-file-link">
                                                                            <a href="<?= base_url('DRM_MyRep/previewDocument/' . (int) $row['id_doc_file']) ?>" target="_blank">
                                                                                <?= htmlspecialchars((string) $row['file_name']) ?>
                                                                            </a>
                                                                        </div>
                                                                        <div class="drm-doc-file-actions">
                                                                            <a href="<?= base_url('DRM_MyRep/downloadDocument/' . (int) $row['id_doc_file']) ?>" class="btn btn-sm btn-outline-primary">
                                                                                <i class="fas fa-download"></i> Download
                                                                            </a>
                                                                            <button
                                                                                type="button"
                                                                                class="btn btn-sm btn-outline-info js-doc-history"
                                                                                data-toggle="modal"
                                                                                data-target="#modal-doc-history"
                                                                                data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                                data-history='<?= htmlspecialchars(json_encode(!empty($row['id_doc_file']) ? $this->MDRM_MyRep->getDrmFileLogs((int) $row['id_doc_file']) : []), ENT_QUOTES) ?>'>
                                                                                <i class="fas fa-history"></i> History
                                                                            </button>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="drm-doc-muted">Belum ada file</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td style="min-width:320px;">
                                                                    <?php if (($row['doc_name'] ?? '') === 'APD BOQ' && $boqReady): ?>
                                                                        <?php if ($canTambah && !$isBoqLocked && !$isSubfeederWorkflowLocked): ?>
                                                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-apd-boq-package-<?= strtolower($scopeKey) ?>">Kelola APD BOQ & BOQ Manual</button>
                                                                        <?php elseif ($isBoqLocked): ?>
                                                                            <span class="text-success small font-weight-bold">BOQ sudah approved</span>
                                                                        <?php endif; ?>
                                                                        <div class="small text-muted mt-2">
                                                                            Status BOQ:
                                                                            <span class="badge badge-<?= drmDetailBadgeClass($boqReviewStatus) ?>"><?= htmlspecialchars($boqReviewStatus !== '' ? $boqReviewStatus : 'DRAFT') ?></span>
                                                                        </div>
                                                                        <?php if ($canApprove && $canApprovalAction && !empty($boqHeader['id_drm_boq']) && (in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true) || ($boqReviewStatus === 'APPROVED' && $canRejectApprovedDocument))): ?>
                                                                            <button type="button" class="btn btn-sm btn-outline-success mt-2" data-toggle="modal" data-target="#modal-boq-review-<?= strtolower($scopeKey) ?>">Review BOQ</button>
                                                                        <?php endif; ?>
                                                                        <?php if (!empty($boqHeader['ho_review_remark'])): ?>
                                                                            <div class="small text-info mt-1">Catatan HO: <?= htmlspecialchars((string) $boqHeader['ho_review_remark']) ?></div>
                                                                        <?php endif; ?>
                                                                    <?php elseif ($canTambah && $docCanUpload): ?>
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-sm btn-primary js-open-drm-upload-modal"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-drm-upload"
                                                                            data-scope-type="<?= htmlspecialchars($scopeKey, ENT_QUOTES) ?>"
                                                                            data-doc-item-id="<?= (int) $row['id_doc_item'] ?>"
                                                                            data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                            data-file-name="<?= htmlspecialchars((string) ($row['file_name'] ?? ''), ENT_QUOTES) ?>"
                                                                            data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                            Upload
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="drm-doc-muted">Upload tidak tersedia</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td style="min-width:220px;">
                                                                    <span class="drm-doc-remark-cell"><?= !empty($row['remark']) ? nl2br(htmlspecialchars((string) $row['remark'])) : '-' ?></span>
                                                                </td>
                                                                <?php if ($canApprove && $canApprovalAction): ?>
                                                                    <td style="min-width:220px;">
                                                                        <?php if (($row['doc_name'] ?? '') === 'APD BOQ'): ?>
                                                                            <span class="text-info small font-weight-bold">Review mengikuti approval BOQ</span>
                                                                        <?php elseif (!empty($row['id_doc_file']) && $docRawStatus === 'UPLOADED'): ?>
                                                                            <div class="d-flex flex-wrap" style="gap:.35rem;">
                                                                                <button
                                                                                    type="button"
                                                                                    class="btn btn-sm btn-success js-open-drm-review-modal"
                                                                                    data-toggle="modal"
                                                                                    data-target="#modal-drm-review"
                                                                                    data-review-action="approve"
                                                                                    data-doc-file-id="<?= (int) ($row['id_doc_file'] ?? 0) ?>"
                                                                                    data-scope-type="<?= htmlspecialchars((string) $scopeKey, ENT_QUOTES) ?>"
                                                                                    data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                                    data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                                    Approve
                                                                                </button>
                                                                                <button
                                                                                    type="button"
                                                                                    class="btn btn-sm btn-danger js-open-drm-review-modal"
                                                                                    data-toggle="modal"
                                                                                    data-target="#modal-drm-review"
                                                                                    data-review-action="reject"
                                                                                    data-doc-file-id="<?= (int) ($row['id_doc_file'] ?? 0) ?>"
                                                                                    data-scope-type="<?= htmlspecialchars((string) $scopeKey, ENT_QUOTES) ?>"
                                                                                    data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                                    data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                                    Reject
                                                                                </button>
                                                                            </div>
                                                                        <?php elseif ($docRawStatus === 'APPROVED'): ?>
                                                                            <?php if ($canRejectApprovedDocument): ?>
                                                                                <button
                                                                                    type="button"
                                                                                    class="btn btn-sm btn-outline-danger js-open-drm-review-modal"
                                                                                    data-toggle="modal"
                                                                                    data-target="#modal-drm-review"
                                                                                    data-review-action="reject"
                                                                                    data-doc-file-id="<?= (int) ($row['id_doc_file'] ?? 0) ?>"
                                                                                    data-scope-type="<?= htmlspecialchars((string) $scopeKey, ENT_QUOTES) ?>"
                                                                                    data-doc-name="<?= htmlspecialchars((string) ($row['doc_name'] ?? ''), ENT_QUOTES) ?>"
                                                                                    data-remark="<?= htmlspecialchars((string) ($row['remark'] ?? ''), ENT_QUOTES) ?>">
                                                                                    Reject Approved
                                                                                </button>
                                                                            <?php else: ?>
                                                                                <span class="text-success small font-weight-bold">Sudah approved</span>
                                                                            <?php endif; ?>
                                                                        <?php elseif ($docRawStatus === 'REJECTED'): ?>
                                                                            <span class="text-danger small font-weight-bold">Sudah rejected</span>
                                                                        <?php else: ?>
                                                                            <span class="drm-doc-muted">Belum ada review langsung</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endif; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($documentRows)): ?>
                                                            <tr><td colspan="<?= ($canApprove && $canApprovalAction) ? '7' : '6' ?>" class="text-center text-muted">Belum ada dokumen <?= htmlspecialchars($scopeLabel) ?>.</td></tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php if ($isSubfeederScope && !empty($scopeRequirementReady) && !$isSubfeederWorkflowLocked): ?>
                                                <div class="mt-3 text-center">
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-subfeeder-not-required">
                                                        Ajukan Subfeeder Tidak Dibutuhkan
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($scopeKey === 'CLUSTER'): ?>
                                        <div class="card drm-rab-card mt-3">
                                            <div class="card-header">
                                                <div class="drm-rab-heading">
                                                    <span class="drm-rab-heading__icon"><i class="fas fa-clipboard-check"></i></span>
                                                    <div>
                                                        <h3 class="drm-rab-heading__title">RAB Cluster</h3>
                                                        <p class="drm-rab-heading__subtitle">Gate final setelah BOQ cluster/APD disetujui.</p>
                                                    </div>
                                                </div>
                                                <span class="badge badge-<?= drmDetailBadgeClass($isRabDone ? 'APPROVED' : '') ?>"><?= htmlspecialchars($isRabDone ? 'RAB DONE' : 'BELUM RAB DONE') ?></span>
                                            </div>
                                            <div class="card-body">
                                                <div class="drm-rab-content">
                                                    <div class="drm-rab-detail-box">
                                                        <span class="drm-rab-detail-label">Detail RAB</span>
                                                        <div class="drm-rab-detail"><?= $isRabDone && !empty($rabDetail['detail_rab']) ? nl2br(htmlspecialchars((string) $rabDetail['detail_rab'])) : '-' ?></div>
                                                        <?php if ($isRabDone && !empty($rabDetail['rab_done_at'])): ?>
                                                            <div class="drm-rab-meta">Checklist: <?= htmlspecialchars((string) $rabDetail['rab_done_at']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="drm-rab-actions">
                                                        <?php if ($canShowRabDoneButton): ?>
                                                            <button type="button" class="btn btn-success drm-rab-action-btn" data-toggle="modal" data-target="#modal-rab-done">
                                                                <i class="fas fa-check-circle"></i>
                                                                Checklist RAB DONE
                                                            </button>
                                                        <?php elseif ($isRabDone): ?>
                                                            <div class="drm-rab-done-note"><i class="fas fa-check-circle mr-1"></i> RAB sudah selesai.</div>
                                                            <?php if ($canShowRabRollbackButton): ?>
                                                                <button type="button" class="btn btn-outline-danger drm-rab-action-btn" data-toggle="modal" data-target="#modal-rab-rollback">
                                                                    <i class="fas fa-undo-alt"></i>
                                                                    Rollback RAB
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="drm-rab-help">Checklist RAB DONE tersedia setelah APD BOQ/BOQ Cluster approved dan user memiliki akses Planning HO.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card card-outline card-primary shadow-sm mt-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h3 class="card-title mb-0">SPK Cluster</h3>
                                                <div>
                                                    <span class="badge badge-<?= drmDetailBadgeClass($isSpkClusterDone ? 'SPK DONE' : '') ?>">Cluster <?= htmlspecialchars($isSpkClusterDone ? 'SPK DONE' : 'BELUM SPK') ?></span>
                                                    <?php if ($isSubfeederAvailableForSpk): ?>
                                                        <span class="badge badge-<?= drmDetailBadgeClass($isSpkSubfeederDone ? 'SPK DONE' : '') ?> ml-1">Subfeeder <?= htmlspecialchars($isSpkSubfeederDone ? 'SPK DONE' : 'BELUM SPK') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success ml-1">Subfeeder Tidak Dibutuhkan</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <div class="drm-spk-grid">
                                                            <div class="drm-spk-scope-card">
                                                                <div class="drm-spk-scope-card__head">
                                                                    <span class="drm-spk-scope-card__title">SPK Cluster</span>
                                                                    <span class="badge badge-<?= drmDetailBadgeClass($isSpkClusterDone ? 'SPK DONE' : '') ?>"><?= htmlspecialchars($isSpkClusterDone ? 'SPK DONE' : 'BELUM SPK') ?></span>
                                                                </div>
                                                                <div class="drm-spk-number"><?= $isSpkClusterDone ? htmlspecialchars((string) ($spkCluster['spk_number'] ?? '-')) : '-' ?></div>
                                                                <div class="drm-spk-meta"><?= $isSpkClusterDone && !empty($spkCluster['spk_done_at']) ? 'Input: ' . htmlspecialchars((string) $spkCluster['spk_done_at']) : 'Menunggu input nomor SPK' ?></div>
                                                            </div>
                                                            <div class="drm-spk-scope-card">
                                                                <div class="drm-spk-scope-card__head">
                                                                    <span class="drm-spk-scope-card__title">SPK Subfeeder</span>
                                                                    <?php if ($isSubfeederAvailableForSpk): ?>
                                                                        <span class="badge badge-<?= drmDetailBadgeClass($isSpkSubfeederDone ? 'SPK DONE' : '') ?>"><?= htmlspecialchars($isSpkSubfeederDone ? 'SPK DONE' : 'BELUM SPK') ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-success">TIDAK DIBUTUHKAN</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php if ($isSubfeederAvailableForSpk): ?>
                                                                    <div class="drm-spk-number"><?= $isSpkSubfeederDone ? htmlspecialchars((string) ($spkSubfeeder['spk_number'] ?? '-')) : '-' ?></div>
                                                                    <div class="drm-spk-meta"><?= $isSpkSubfeederDone && !empty($spkSubfeeder['spk_done_at']) ? 'Input: ' . htmlspecialchars((string) $spkSubfeeder['spk_done_at']) : 'Menunggu input nomor SPK' ?></div>
                                                                <?php else: ?>
                                                                    <div class="drm-spk-number">Tidak dibutuhkan</div>
                                                                    <div class="drm-spk-meta">Scope subfeeder sudah diset tidak dibutuhkan.</div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                                        <?php if ($canShowSpkDoneButton): ?>
                                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-spk-done">Input SPK</button>
                                                            <?php if ($canShowSpkRollbackButton): ?>
                                                                <button type="button" class="btn btn-outline-danger mt-2" data-toggle="modal" data-target="#modal-spk-rollback">Rollback SPK</button>
                                                            <?php endif; ?>
                                                        <?php elseif ($canManageSpk && ($isSpkClusterDone || $isSpkSubfeederDone)): ?>
                                                            <div class="text-success font-weight-bold mb-2">SPK sudah lengkap untuk scope aktif.</div>
                                                            <?php if ($canShowSpkRollbackButton): ?>
                                                                <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#modal-spk-rollback">Rollback SPK</button>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Input SPK tersedia setelah RAB DONE dan user memiliki akses Planning HO.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php $tabIndex++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-subfeeder-not-required" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/requestSubfeederNotRequired') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Ajukan Subfeeder Tidak Dibutuhkan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Remarks</label>
                        <textarea name="remark" rows="4" class="form-control" required placeholder="Alasan pengajuan, contoh: cluster tidak memiliki jalur subfeeder"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Ajukan Subfeeder tidak dibutuhkan ke HO?');">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-subfeeder-reopen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/reopenSubfeederRequirement') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Aktifkan Kembali Subfeeder</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Remarks</label>
                        <textarea name="remark" rows="4" class="form-control" placeholder="Catatan perubahan, contoh: desain berubah dan subfeeder dibutuhkan kembali"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Aktifkan kembali Subfeeder? Baseline cluster-only aktif akan diganti sampai BOQ Subfeeder approved.');">Aktifkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-subfeeder-not-required-approve" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/reviewSubfeederNotRequired') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <input type="hidden" name="action_type" value="approve">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Subfeeder Tidak Dibutuhkan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Remarks</label>
                        <textarea name="remark" rows="4" class="form-control" placeholder="Catatan approve HO"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve Subfeeder tidak dibutuhkan?');">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-subfeeder-not-required-reject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/reviewSubfeederNotRequired') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <input type="hidden" name="action_type" value="reject">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Subfeeder Tidak Dibutuhkan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Remarks</label>
                        <textarea name="remark" rows="4" class="form-control" required placeholder="Alasan reject"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject pengajuan Subfeeder tidak dibutuhkan?');">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($canShowRabDoneButton): ?>
<div class="modal fade" id="modal-rab-done" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/checklistRabDone') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header drm-modal__header">
                    <div>
                        <span class="drm-modal__eyebrow">RAB Cluster</span>
                        <h5 class="modal-title mb-1">Checklist RAB DONE</h5>
                        <p class="drm-modal__subtitle mb-0">Simpan status RAB DONE setelah gate BOQ cluster/APD sudah approved.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-rab-alert mb-3">
                        <i class="fas fa-info-circle"></i>
                        <span>Status RAB dapat disimpan karena APD BOQ/BOQ Cluster sudah approved. Subfeeder tidak menjadi syarat.</span>
                    </div>
                    <div class="drm-form-box mb-0">
                        <div class="form-group mb-0">
                            <label>Detail RAB</label>
                            <textarea name="detail_rab" rows="5" class="form-control" required placeholder="Isi detail RAB"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Simpan checklist RAB DONE untuk cluster ini?');">
                        <i class="fas fa-check-circle mr-1"></i>
                        Simpan RAB DONE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canShowRabRollbackButton): ?>
<div class="modal fade" id="modal-rab-rollback" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/rollbackRabDone') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header drm-modal__header">
                    <div>
                        <span class="drm-modal__eyebrow">RAB Cluster</span>
                        <h5 class="modal-title mb-1">Rollback RAB DONE</h5>
                        <p class="drm-modal__subtitle mb-0">Kembalikan status RAB menjadi BELUM RAB DONE jika data perlu diperbaiki.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-rab-alert mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Status RAB akan dikembalikan menjadi BELUM RAB DONE. Cluster yang belum memenuhi gate RAB tidak akan tampil lagi di list Batch Approval tahap awal.</span>
                    </div>
                    <div class="drm-form-box mb-0">
                        <div class="form-group mb-0">
                            <label>Alasan Rollback <span class="text-muted font-weight-normal">(opsional)</span></label>
                            <textarea name="reason" rows="4" class="form-control" placeholder="Contoh: detail RAB perlu diperbaiki"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rollback status RAB DONE untuk cluster ini?');">Rollback RAB</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canShowSpkDoneButton): ?>
<div class="modal fade" id="modal-spk-done" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/saveSpkDone') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Input SPK</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        SPK dapat diinput karena RAB sudah DONE. Nomor SPK wajib diisi.
                    </div>
                    <div class="form-group">
                        <label>Tipe SPK</label>
                        <div class="drm-spk-option-grid">
                            <?php if ($canInputSpkCluster): ?>
                                <label class="drm-spk-option">
                                    <input type="radio" name="spk_mode" value="CLUSTER_ONLY" required checked>
                                    <span class="drm-spk-option__body">
                                        <span class="drm-spk-option__title">SPK Cluster Only</span>
                                        <span class="drm-spk-option__text">Mengisi nomor SPK hanya untuk scope cluster.</span>
                                    </span>
                                </label>
                            <?php endif; ?>
                            <?php if ($canInputSpkSubfeeder): ?>
                                <label class="drm-spk-option">
                                    <input type="radio" name="spk_mode" value="SUBFEEDER_ONLY" required <?= !$canInputSpkCluster ? 'checked' : '' ?>>
                                    <span class="drm-spk-option__body">
                                        <span class="drm-spk-option__title">SPK Subfeeder Only</span>
                                        <span class="drm-spk-option__text">Mengisi nomor SPK hanya untuk scope subfeeder.</span>
                                    </span>
                                </label>
                            <?php endif; ?>
                            <?php if ($canInputSpkGabungan): ?>
                                <label class="drm-spk-option">
                                    <input type="radio" name="spk_mode" value="GABUNGAN" required>
                                    <span class="drm-spk-option__body">
                                        <span class="drm-spk-option__title">SPK Gabungan</span>
                                        <span class="drm-spk-option__text">Nomor SPK yang sama untuk cluster dan subfeeder.</span>
                                    </span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Nomor SPK</label>
                        <input type="text" name="spk_number" class="form-control" required placeholder="Isi nomor SPK">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Simpan SPK untuk cluster ini?');">Simpan SPK</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canShowSpkRollbackButton): ?>
<div class="modal fade" id="modal-spk-rollback" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/rollbackSpkDone') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rollback SPK</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        Pilih scope SPK yang ingin dikembalikan menjadi BELUM SPK.
                    </div>
                    <div class="form-group">
                        <label>Scope Rollback</label>
                        <div class="drm-spk-option-grid">
                            <?php if ($isSpkClusterDone): ?>
                                <label class="drm-spk-option">
                                    <input type="checkbox" name="rollback_scopes[]" value="CLUSTER" checked>
                                    <span class="drm-spk-option__body">
                                        <span class="drm-spk-option__title">SPK Cluster</span>
                                        <span class="drm-spk-option__text"><?= htmlspecialchars((string) ($spkCluster['spk_number'] ?? '-')) ?></span>
                                    </span>
                                </label>
                            <?php endif; ?>
                            <?php if ($isSpkSubfeederDone): ?>
                                <label class="drm-spk-option">
                                    <input type="checkbox" name="rollback_scopes[]" value="SUBFEEDER" checked>
                                    <span class="drm-spk-option__body">
                                        <span class="drm-spk-option__title">SPK Subfeeder</span>
                                        <span class="drm-spk-option__text"><?= htmlspecialchars((string) ($spkSubfeeder['spk_number'] ?? '-')) ?></span>
                                    </span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Alasan Rollback <span class="text-muted font-weight-normal">(opsional)</span></label>
                        <textarea name="reason" rows="4" class="form-control" placeholder="Contoh: nomor SPK perlu diperbaiki"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rollback SPK untuk scope yang dipilih?');">Rollback SPK</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canEdit): ?>
<div class="modal fade" id="modal-drm-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url(!empty($cluster['id_drm']) ? 'DRM_MyRep/updateDrm' : 'DRM_MyRep/saveDrm') ?>">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_drm" value="<?= (int) ($cluster['id_drm'] ?? 0) ?>">
                <div class="modal-header drm-modal__header">
                    <div>
                        <span class="drm-modal__eyebrow">DRM MyRep</span>
                        <h5 class="modal-title mb-1"><?= !empty($cluster['id_drm']) ? 'Edit Header DRM' : 'Lengkapi Header DRM' ?></h5>
                        <p class="drm-modal__subtitle mb-0">Lengkapi tanggal, homepass, OLT, status, dan catatan DRM untuk cluster ini.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Informasi Cluster</div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-md-0">
                                    <label>Nama Cluster</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Kota</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="drm-form-box">
                        <div class="drm-form-box__title">Header DRM</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal DRM</label>
                                    <input type="date" name="drm_date" class="form-control" value="<?= htmlspecialchars((string) ($cluster['drm_date'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>HP DRM</label>
                                    <input type="number" min="0" step="1" name="homepass_drm" class="form-control" value="<?= htmlspecialchars((string) ($cluster['homepass_drm'] ?? '')) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama OLT</label>
                                    <input type="text" name="nama_olt" class="form-control" value="<?= htmlspecialchars((string) ($cluster['nama_olt'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Status DRM</label>
                                    <select name="status_drm" class="form-control">
                                        <?php
                                        $statusOptions = ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED'];
                                        $currentStatusDrm = strtoupper(trim((string) ($cluster['status_drm'] ?? 'WAITING DOC')));
                                        foreach ($statusOptions as $statusOption):
                                        ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= $currentStatusDrm === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-0">
                                    <label>Remark DRM</label>
                                    <textarea name="remark_drm" rows="3" class="form-control"><?= htmlspecialchars((string) ($cluster['remark_drm'] ?? '')) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><?= !empty($cluster['id_drm']) ? 'Update DRM' : 'Simpan DRM' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canTambah): ?>
<div class="modal fade" id="modal-drm-upload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="form-drm-upload">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="scope_type" id="drm_upload_scope_type" value="CLUSTER">
                <input type="hidden" name="id_doc_item" id="drm_upload_doc_item_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Upload Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Scope:</strong> <span id="drm_upload_scope_label">Cluster</span></div>
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
<?php endif; ?>

<?php if ($canApprove && $canApprovalAction): ?>
<div class="modal fade" id="modal-drm-review" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content drm-modal">
            <form method="post" action="<?= base_url('DRM_MyRep/approveDocument') ?>" id="form-drm-review">
                <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                <input type="hidden" name="id_doc_file" id="drm_review_doc_file_id">
                <input type="hidden" name="scope_type" id="drm_review_scope_type" value="CLUSTER">
                <div class="modal-header bg-success text-white" id="drm_review_header">
                    <h5 class="modal-title" id="drm_review_title">Approve Dokumen DRM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Scope:</strong> <span id="drm_review_scope_label">Cluster</span></div>
                    <div class="mb-3"><strong>Dokumen:</strong> <span id="drm_review_doc_name">-</span></div>
                    <div class="form-group mb-0">
                        <label for="drm_review_remark">Remarks</label>
                        <textarea name="remark" id="drm_review_remark" class="form-control" rows="4" placeholder="Catatan review dokumen"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm" id="drm_review_submit">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-doc-history" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content drm-modal">
            <div class="modal-header drm-modal__header">
                <div>
                    <span class="drm-modal__eyebrow">Audit Dokumen</span>
                    <h5 class="modal-title mb-1">History Dokumen</h5>
                    <p class="drm-modal__subtitle mb-0">Riwayat perubahan dan review dokumen cluster DRM.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="drm-form-box mb-3">
                    <div class="drm-form-box__title">Dokumen</div>
                    <div class="doc-history-detail" id="history_doc_label">-</div>
                </div>
                <ul class="doc-history-list" id="history_doc_items">
                    <li class="doc-history-empty">Belum ada history.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php foreach ($drmScopes as $scopeKey => $scope): ?>
    <?php
    $scopeReady = !empty($scope['isReady']);
    if (!$scopeReady || !$boqReady) {
        continue;
    }
    $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
    $boqHeader = (array) ($scope['boqHeader'] ?? []);
    $boqItems = (array) ($scope['boqItems'] ?? []);
    $boqReviewStatus = strtoupper(trim((string) ($boqHeader['review_status'] ?? 'DRAFT')));
    $isBoqLocked = $boqReviewStatus === 'APPROVED';
    $apdBoqFile = (array) ($scope['apdBoqFile'] ?? []);
    $hasApdBoqFile = !empty($apdBoqFile['id_doc_file']);
    ?>
    <?php if ($canTambah): ?>
    <div class="modal fade" id="modal-apd-boq-package-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content drm-modal">
                <form method="post" action="<?= base_url('DRM_MyRep/saveApdBoqPackage') ?>" enctype="multipart/form-data" class="js-apd-boq-form" data-existing-file="<?= $hasApdBoqFile ? '1' : '0' ?>" data-preview-url="<?= base_url('DRM_MyRep/previewApdBoqParse') ?>">
                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">APD BOQ dan Manual BOQ - <?= htmlspecialchars($scopeLabel) ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Informasi <?= htmlspecialchars($scopeLabel) ?></div>
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

                        <div class="drm-form-box js-manual-boq-section" style="display:none;">
                            <div class="drm-form-box__title">Input Jumlah Item Manual</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item di Excel</th>
                                            <th>Nama Item</th>
                                            <th>Jenis Item</th>
                                            <th>Satuan</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqItems as $index => $item): ?>
                                            <?php $qtyValue = (float) ($item['qty_boq'] ?? 0); ?>
                                            <tr class="<?= $qtyValue > 0 ? 'table-success' : 'table-light' ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['excel_item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_satuan'] ?? '-')) ?></td>
                                                <td>
                                                    <input type="number" step="any" min="0" name="boq_qty[<?= (int) $item['id_boq_item'] ?>]" class="form-control form-control-sm js-modal-boq-qty" value="<?= rtrim(rtrim(number_format($qtyValue, 3, '.', ''), '0'), '.') ?>" <?= $isBoqLocked ? 'readonly' : '' ?>>
                                                    <div class="small text-danger mt-1 js-modal-boq-warning" style="display:none;"></div>
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
                                <input type="file" name="apd_boq_file" class="js-dropzone-input">
                                <div class="drm-dropzone-content">
                                    <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="drm-dropzone-title">Drag & drop file APD BOQ di sini</div>
                                    <div class="drm-dropzone-text">Atau klik area ini untuk memilih file</div>
                                    <div class="drm-dropzone-file js-dropzone-label">Belum ada file baru dipilih</div>
                                </div>
                            </div>
                            <div class="form-group mt-3 mb-0">
                                <label>Remark Upload File</label>
                                <textarea name="apd_boq_remark" rows="2" class="form-control" placeholder="Catatan upload APD BOQ jika diperlukan"></textarea>
                            </div>
                            <div class="mt-3 js-apd-boq-alert" style="display:none;"></div>
                            <div class="mt-3 p-2 border rounded bg-light js-apd-boq-preview" style="display:none;">
                                <div class="js-apd-boq-loading" style="display:none;">
                                    <div class="small text-primary mb-2">Sedang parsing APD BOQ, mohon tunggu...</div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID Item</th>
                                                <th>Item di Excel</th>
                                                <th>Nama Item</th>
                                                <th>Jenis</th>
                                                <th>Satuan</th>
                                                <th>Qty Hasil Parse</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-apd-boq-preview-body">
                                            <tr><td colspan="6" class="text-muted text-center">Belum ada hasil parsing.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="text-muted small">Upload APD BOQ dulu, lalu cek hasil parsing sebelum simpan.</div>
                        <div>
                            <?php if (!$isBoqLocked): ?>
                                <button type="submit" class="btn btn-outline-primary">Simpan Draft</button>
                                <button type="submit" class="btn btn-primary" name="submit_to_ho" value="1">Submit ke HO</button>
                            <?php else: ?>
                                <span class="text-muted small">BOQ sudah approved, reject dulu agar bisa upload ulang.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canApprove && $canApprovalAction && !empty($boqHeader['id_drm_boq']) && (in_array($boqReviewStatus, ['WAITING HO', 'REJECTED'], true) || ($boqReviewStatus === 'APPROVED' && $canRejectApprovedDocument))): ?>
        <div class="modal fade" id="modal-boq-review-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content drm-modal">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Review BOQ DRM - <?= htmlspecialchars($scopeLabel) ?></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Informasi Review</div>
                            <div class="small text-muted">Approve BOQ akan sekaligus meng-approve dokumen <?= htmlspecialchars($scopeLabel) ?> yang sudah berstatus upload.</div>
                        </div>
                        <div class="drm-form-box">
                            <div class="drm-form-box__title">Detail Item BOQ</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item di Excel</th>
                                            <th>Nama Item</th>
                                            <th>Jenis</th>
                                            <th>Satuan</th>
                                            <th>Qty BOQ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boqItems as $index => $item): ?>
                                            <?php $qtyValue = (float) ($item['qty_boq'] ?? 0); ?>
                                            <tr class="<?= $qtyValue > 0 ? 'table-success' : 'table-light' ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($item['excel_item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_type'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($item['item_satuan'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars(rtrim(rtrim(number_format($qtyValue, 3, '.', ''), '0'), '.')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($boqItems)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada item BOQ.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <?php if ($boqReviewStatus !== 'APPROVED'): ?>
                            <div class="col-md-6">
                                <form method="post" action="<?= base_url('DRM_MyRep/approveBoq') ?>">
                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
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
                            <?php endif; ?>
                            <div class="<?= $boqReviewStatus === 'APPROVED' ? 'col-md-12' : 'col-md-6' ?>">
                                <form method="post" action="<?= base_url('DRM_MyRep/rejectBoq') ?>">
                                    <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>">
                                    <input type="hidden" name="scope_type" value="<?= htmlspecialchars($scopeKey) ?>">
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
<?php endforeach; ?>

<?php foreach ($drmScopes as $scopeKey => $scope): ?>
    <?php
    $scopeReady = !empty($scope['isReady']);
    if (!$scopeReady) {
        continue;
    }

    $scopeLabel = (string) ($scope['label'] ?? drmScopeText($scopeKey));
    $bulkRows = [];
    foreach ((array) ($scope['documentRows'] ?? []) as $docRow) {
        $docNameUpper = strtoupper(trim((string) ($docRow['doc_name'] ?? '')));
        $docStatus = drmDocumentLabel($docRow);
        $docRawStatus = strtoupper(trim((string) ($docRow['status_file'] ?? '')));
        if ($docNameUpper === 'APD BOQ') {
            continue;
        }
        if (in_array($docStatus, ['BELUM UPLOAD'], true) || $docRawStatus === 'REJECTED') {
            $bulkRows[] = $docRow;
        }
    }
    ?>
    <?php if ($canTambah && !empty($bulkRows)): ?>
        <div class="modal fade" id="modal-drm-bulk-upload-<?= strtolower($scopeKey) ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content drm-modal">
                    <form method="post" action="<?= base_url('DRM_MyRep/uploadBulkDocuments') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="cluster_id" value="<?= (int) ($cluster['id_myrep_cluster'] ?? 0) ?>">
                        <input type="hidden" name="scope_type" value="<?= htmlspecialchars((string) $scopeKey) ?>">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Bulk Upload Dokumen DRM - <?= htmlspecialchars($scopeLabel) ?></h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="drm-bulk-summary">
                                <div>
                                    <div class="drm-bulk-summary__title">Upload beberapa dokumen DRM sekaligus</div>
                                    <p class="drm-bulk-summary__text">Pilih file per kartu dokumen, atau centang <strong>Tidak dibutuhkan</strong> jika item memang tidak wajib. APD BOQ dan Manual BOQ tidak termasuk di bulk upload.</p>
                                </div>
                                <div class="drm-bulk-summary__badge"><?= count($bulkRows) ?></div>
                            </div>
                            <div class="drm-bulk-grid">
                                <?php foreach ($bulkRows as $index => $row): ?>
                                    <div class="drm-bulk-card">
                                        <input type="hidden" name="bulk_doc_item_ids[]" value="<?= (int) ($row['id_doc_item'] ?? 0) ?>">
                                        <div class="drm-bulk-card__header">
                                            <div>
                                                <div class="drm-bulk-card__eyebrow">Dokumen <?= $index + 1 ?></div>
                                                <h6 class="drm-bulk-card__title"><?= htmlspecialchars((string) ($row['doc_name'] ?? '-')) ?></h6>
                                            </div>
                                            <span class="badge badge-<?= drmDetailBadgeClass(drmDocumentLabel($row)) ?>"><?= htmlspecialchars(drmDocumentLabel($row)) ?></span>
                                        </div>
                                        <div class="drm-bulk-card__body">
                                            <div class="small text-muted mb-2">
                                                <?= !empty($row['doc_requirement_note']) ? htmlspecialchars((string) $row['doc_requirement_note']) : 'Tidak ada catatan khusus.' ?>
                                            </div>
                                            <div class="small mb-2">
                                                <strong>File saat ini:</strong>
                                                <?= !empty($row['file_name']) ? htmlspecialchars((string) $row['file_name']) : 'Belum ada file aktif.' ?>
                                            </div>
                                            <div class="drm-dropzone js-dropzone">
                                                <input type="file" name="bulk_file_<?= (int) ($row['id_doc_item'] ?? 0) ?>" class="js-dropzone-input">
                                                <div class="drm-dropzone-content">
                                                    <div class="drm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                    <div class="drm-dropzone-title">Pilih file untuk <?= htmlspecialchars((string) ($row['doc_name'] ?? 'dokumen')) ?></div>
                                                    <div class="drm-dropzone-text">Drag & drop file di sini atau klik area ini</div>
                                                    <div class="drm-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                                </div>
                                            </div>
                                            <div class="form-group mt-3 mb-2">
                                                <label class="mb-1 font-weight-bold">Remark Upload</label>
                                                <textarea name="bulk_remark_<?= (int) ($row['id_doc_item'] ?? 0) ?>" class="form-control" rows="2" placeholder="Remark upload jika diperlukan"><?= htmlspecialchars((string) ($row['remark'] ?? '')) ?></textarea>
                                            </div>
                                            <div class="form-group form-check mb-0">
                                                <input type="checkbox" class="form-check-input" id="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>" name="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>" value="1">
                                                <label class="form-check-label" for="bulk_not_required_<?= (int) ($row['id_doc_item'] ?? 0) ?>">Tidak dibutuhkan</label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan Bulk Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script>
    (function () {
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
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                input.addEventListener('change', function () {
                    label.textContent = input.files && input.files.length > 0
                        ? input.files[0].name
                        : 'Belum ada file dipilih';
                });
            });
        }

        function getQtyInputMap(form) {
            var map = {};
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var name = input.getAttribute('name') || '';
                var match = name.match(/^boq_qty\[(\d+)\]$/);
                if (match) {
                    map[match[1]] = input;
                }
            });
            return map;
        }

        function renderApdBoqPreview(form, payload) {
            var wrapper = form.querySelector('.js-apd-boq-preview');
            var manualSection = form.querySelector('.js-manual-boq-section');
            var loadingEl = form.querySelector('.js-apd-boq-loading');
            var bodyEl = form.querySelector('.js-apd-boq-preview-body');
            if (!wrapper || !bodyEl) {
                return;
            }

            wrapper.style.display = '';
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }

            var items = Array.isArray(payload.items) ? payload.items : [];
            if (!items.length) {
                if (manualSection) {
                    manualSection.style.display = 'none';
                }
                bodyEl.innerHTML = '<tr><td colspan="6" class="text-muted text-center">Tidak ada item yang berhasil diparsing.</td></tr>';
                return;
            }
            if (manualSection) {
                manualSection.style.display = '';
            }

            var itemInfoMap = {};
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var name = input.getAttribute('name') || '';
                var match = name.match(/^boq_qty\[(\d+)\]$/);
                if (!match) {
                    return;
                }
                var id = match[1];
                var row = input.closest('tr');
                if (!row) {
                    return;
                }
                var cells = row.querySelectorAll('td');
                itemInfoMap[id] = {
                    excelName: cells[1] ? (cells[1].textContent || '').trim() : '-',
                    itemName: cells[2] ? (cells[2].textContent || '').trim() : '-',
                    itemType: cells[3] ? (cells[3].textContent || '').trim() : '-',
                    itemSatuan: cells[4] ? (cells[4].textContent || '').trim() : '-'
                };
            });

            var rows = '';
            items.forEach(function (item) {
                var id = String(item.id_boq_item || '-');
                var info = itemInfoMap[id] || { excelName: '-', itemName: '-', itemType: '-', itemSatuan: '-' };
                rows += '<tr>' +
                    '<td>' + id + '</td>' +
                    '<td>' + info.excelName + '</td>' +
                    '<td>' + info.itemName + '</td>' +
                    '<td>' + info.itemType + '</td>' +
                    '<td>' + info.itemSatuan + '</td>' +
                    '<td>' + (item.qty_boq || 0) + '</td>' +
                '</tr>';
            });
            bodyEl.innerHTML = rows;
        }

        function renderApdBoqNotice(form, type, message) {
            var alertHost = form.querySelector('.js-apd-boq-alert');
            if (!alertHost) {
                return;
            }
            if (!message) {
                alertHost.style.display = 'none';
                alertHost.innerHTML = '';
                return;
            }
            var alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
            alertHost.style.display = '';
            alertHost.innerHTML = '<div class="alert ' + alertClass + ' mb-0 py-2 px-3 small">' + message + '</div>';
        }

        function fillManualQtyFromParsed(form, items) {
            var map = getQtyInputMap(form);
            Object.keys(map).forEach(function (id) {
                map[id].value = '0';
                var row = map[id].closest('tr');
                if (row) {
                    row.dataset.qtyMismatch = '0';
                    var warningEl = row.querySelector('.js-modal-boq-warning');
                    if (warningEl) {
                        warningEl.style.display = 'none';
                        warningEl.textContent = '';
                    }
                }
            });

            items.forEach(function (item) {
                var id = String(item.id_boq_item || '');
                var input = map[id];
                if (!input) {
                    return;
                }
                var qty = parseFloat(item.qty_boq || 0);
                input.value = qty > 0 ? String(qty) : '';
                var row = input.closest('tr');
                var warnings = Array.isArray(item.warnings) ? item.warnings.filter(function (warning) {
                    return String(warning || '').trim() !== '';
                }) : [];
                if (row) {
                    row.dataset.qtyMismatch = warnings.length ? '1' : '0';
                    var warningEl = row.querySelector('.js-modal-boq-warning');
                    if (warningEl) {
                        warningEl.style.display = warnings.length ? '' : 'none';
                        warningEl.textContent = warnings.join(' ');
                    }
                }
            });
            refreshManualBoqRowColors(form);
        }

        function refreshManualBoqRowColors(form) {
            var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
            Array.prototype.forEach.call(qtyInputs, function (input) {
                var row = input.closest('tr');
                if (!row) {
                    return;
                }
                var qty = parseFloat(input.value || '0');
                row.classList.remove('table-success', 'table-light', 'table-danger');
                if (row.dataset.qtyMismatch === '1') {
                    row.classList.add('table-danger');
                } else if (qty > 0) {
                    row.classList.add('table-success');
                } else {
                    row.classList.add('table-light');
                }
            });
        }

        function bindApdBoqPreview() {
            var forms = document.querySelectorAll('.js-apd-boq-form');
            Array.prototype.forEach.call(forms, function (form) {
                if (form.dataset.previewBound === '1') {
                    return;
                }
                form.dataset.previewBound = '1';

                var fileInput = form.querySelector('input[name="apd_boq_file"]');
                if (!fileInput) {
                    return;
                }
                refreshManualBoqRowColors(form);

                var qtyInputs = form.querySelectorAll('.js-modal-boq-qty');
                Array.prototype.forEach.call(qtyInputs, function (qtyInput) {
                    qtyInput.addEventListener('input', function () {
                        refreshManualBoqRowColors(form);
                    });
                    qtyInput.addEventListener('change', function () {
                        refreshManualBoqRowColors(form);
                    });
                });

                fileInput.addEventListener('change', function () {
                    var file = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;
                    if (!file) {
                        console.log('[APD BOQ][Preview] Tidak ada file terpilih.');
                        return;
                    }
                    console.log('[APD BOQ][Preview] File dipilih:', {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    });

                    var previewUrl = form.getAttribute('data-preview-url') || '';
                    if (previewUrl === '') {
                        console.warn('[APD BOQ][Preview] URL preview kosong.');
                        return;
                    }
                    console.log('[APD BOQ][Preview] Mulai request parsing ke:', previewUrl);

                    renderApdBoqPreview(form, {
                        status: false,
                        message: 'Memproses parsing file APD BOQ...',
                        items: [],
                        warnings: []
                    });
                    renderApdBoqNotice(form, '', '');
                    var loadingEl = form.querySelector('.js-apd-boq-loading');
                    if (loadingEl) {
                        loadingEl.style.display = '';
                    }

                    var fd = new FormData();
                    fd.append('apd_boq_file', file);
                    fd.append('scope_type', form.querySelector('input[name="scope_type"]') ? form.querySelector('input[name="scope_type"]').value : 'CLUSTER');

                    fetch(previewUrl, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    })
                    .then(function (response) {
                        console.log('[APD BOQ][Preview] HTTP status:', response.status);
                        return response.text().then(function (text) {
                            var payload = null;
                            try {
                                payload = JSON.parse(text);
                            } catch (e) {
                                var firstBrace = text.indexOf('{');
                                var lastBrace = text.lastIndexOf('}');
                                if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                                    var candidate = text.substring(firstBrace, lastBrace + 1);
                                    try {
                                        payload = JSON.parse(candidate);
                                    } catch (e2) {
                                        console.error('[APD BOQ][Preview] Response bukan JSON:', text);
                                        throw new Error('Response preview bukan JSON valid.');
                                    }
                                } else {
                                    console.error('[APD BOQ][Preview] Response bukan JSON:', text);
                                    throw new Error('Response preview bukan JSON valid.');
                                }
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        console.log('[APD BOQ][Preview] Response payload:', payload);
                        renderApdBoqPreview(form, payload || {});
                        if (payload && Array.isArray(payload.items) && payload.items.length > 0) {
                            console.log('[APD BOQ][Preview] Parsing berhasil. Item terpetakan:', payload.items.length, '| warning:', (payload.warnings || []).length);
                            fillManualQtyFromParsed(form, payload.items);
                            console.log('[APD BOQ][Preview] Auto-fill qty manual selesai.');
                            var hasQtyMismatch = payload.items.some(function (item) {
                                return Array.isArray(item.warnings) && item.warnings.length > 0;
                            });
                            if (hasQtyMismatch) {
                                renderApdBoqNotice(form, '', '');
                            } else {
                                renderApdBoqNotice(form, 'success', 'Parsing APD BOQ berhasil. ' + payload.items.length + ' item terpetakan.');
                            }
                        } else {
                            console.warn('[APD BOQ][Preview] Parsing tidak menghasilkan item terpetakan.');
                            renderApdBoqNotice(form, 'warning', 'Parsing APD BOQ selesai, tetapi tidak ada item yang berhasil terpetakan.');
                        }
                    })
                    .catch(function (error) {
                        console.error('[APD BOQ][Preview] Gagal request preview parsing:', error);
                        renderApdBoqPreview(form, {
                            status: false,
                            message: 'Gagal preview parsing file APD BOQ.',
                            items: [],
                            warnings: []
                        });
                        var loadingEl = form.querySelector('.js-apd-boq-loading');
                        if (loadingEl) {
                            loadingEl.style.display = 'none';
                        }
                        renderApdBoqNotice(form, 'error', 'Parsing APD BOQ gagal karena error sistem. Coba ulangi.');
                    });
                });
            });
        }

        function renderHistory(history) {
            if (!history.length) {
                return '<li class="doc-history-empty">Belum ada history.</li>';
            }

            var html = '';
            history.forEach(function (entry) {
                html += '<li class="doc-history-item">' +
                    '<div class="doc-history-title">' + (entry.action_type || '-') + '</div>' +
                    '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                    '<div class="doc-history-detail"><strong>File:</strong> ' + (entry.file_name || '-') + '</div>' +
                    '<div class="doc-history-detail"><strong>Remark:</strong> ' + (entry.remark || '-') + '</div>' +
                '</li>';
            });
            return html;
        }

        bindDropzones();
        bindApdBoqPreview();

        document.addEventListener('click', function (event) {
            var reviewButton = event.target.closest('.js-open-drm-review-modal');
            if (reviewButton) {
                var actionType = reviewButton.getAttribute('data-review-action') || 'approve';
                var isReject = actionType === 'reject';
                var reviewForm = document.getElementById('form-drm-review');
                var reviewHeader = document.getElementById('drm_review_header');
                var reviewSubmit = document.getElementById('drm_review_submit');
                var reviewRemark = document.getElementById('drm_review_remark');

                if (reviewForm) {
                    reviewForm.setAttribute('action', isReject ? '<?= base_url('DRM_MyRep/rejectDocument') ?>' : '<?= base_url('DRM_MyRep/approveDocument') ?>');
                }
                if (reviewHeader) {
                    reviewHeader.className = 'modal-header ' + (isReject ? 'bg-danger' : 'bg-success') + ' text-white';
                }
                if (reviewSubmit) {
                    reviewSubmit.className = 'btn ' + (isReject ? 'btn-danger' : 'btn-success') + ' btn-sm';
                    reviewSubmit.textContent = isReject ? 'Reject' : 'Approve';
                }

                document.getElementById('drm_review_title').textContent = (isReject ? 'Reject' : 'Approve') + ' Dokumen DRM';
                document.getElementById('drm_review_doc_file_id').value = reviewButton.getAttribute('data-doc-file-id') || '';
                document.getElementById('drm_review_scope_type').value = reviewButton.getAttribute('data-scope-type') || 'CLUSTER';
                document.getElementById('drm_review_scope_label').textContent = (reviewButton.getAttribute('data-scope-type') || 'CLUSTER') === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
                document.getElementById('drm_review_doc_name').textContent = reviewButton.getAttribute('data-doc-name') || '-';
                if (reviewRemark) {
                    reviewRemark.value = reviewButton.getAttribute('data-remark') || '';
                    reviewRemark.required = isReject;
                    reviewRemark.placeholder = isReject ? 'Alasan reject dokumen' : 'Catatan approve dokumen';
                }
                return;
            }

            var uploadButton = event.target.closest('.js-open-drm-upload-modal');
            if (uploadButton) {
                var currentLabel = document.querySelector('#modal-drm-upload .js-dropzone-label');
                var currentInput = document.querySelector('#modal-drm-upload .js-dropzone-input');
                document.getElementById('drm_upload_scope_type').value = uploadButton.getAttribute('data-scope-type') || 'CLUSTER';
                document.getElementById('drm_upload_scope_label').textContent = (uploadButton.getAttribute('data-scope-type') || 'CLUSTER') === 'SUBFEEDER' ? 'Subfeeder' : 'Cluster';
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
                document.getElementById('history_doc_items').innerHTML = renderHistory(history);
            }
        });

        document.addEventListener('submit', function (event) {
            if (event.target.id === 'form-drm-upload') {
                var uploadCheckbox = document.getElementById('drm_upload_not_required');
                var uploadInput = event.target.querySelector('.js-dropzone-input');
                var noDocument = uploadCheckbox && uploadCheckbox.checked;
                var hasFile = uploadInput && uploadInput.files && uploadInput.files.length > 0;

                if (!noDocument && !hasFile) {
                    event.preventDefault();
                    alert('File dokumen DRM wajib dipilih atau centang "Tidak dibutuhkan".');
                }
                return;
            }

            if (!event.target.classList.contains('js-apd-boq-form')) {
                return;
            }

            var hasExistingFile = event.target.getAttribute('data-existing-file') === '1';
            var uploadInput = event.target.querySelector('input[name="apd_boq_file"]');
            var hasNewFile = uploadInput && uploadInput.files && uploadInput.files.length > 0;
            var manualSection = event.target.querySelector('.js-manual-boq-section');
            var hasQty = false;
            var qtyInputs = event.target.querySelectorAll('.js-modal-boq-qty');

            Array.prototype.forEach.call(qtyInputs, function (input) {
                if (parseFloat(input.value || '0') > 0) {
                    hasQty = true;
                }
            });

            var manualVisible = manualSection && manualSection.style.display !== 'none';
            if (!manualVisible || !hasQty || (!hasExistingFile && !hasNewFile)) {
                event.preventDefault();
                alert('Upload APD BOQ dan pastikan parsing berhasil dulu sebelum disimpan.');
            }
        });
    })();
</script>
