<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$userValidation = (string) $this->session->userdata('validation');
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
$isHoUser = $this->session->userdata('lokasi_user') == 'HO';

$totalArea = count($list_purchase_request_area);
$totalHo = count($list_purchase_request_ho);
$totalAll = $totalArea + $totalHo;
$totalApproved = 0;
$totalWaiting = 0;
$totalHardcopy = 0;

foreach (array_merge($list_purchase_request_area, $list_purchase_request_ho) as $purchaseRequest) {
    if (!empty($purchaseRequest['is_fully_approved'])) {
        $totalApproved++;
    } else {
        $totalWaiting++;
    }

    if (!empty($purchaseRequest['hardcopy_file'])) {
        $totalHardcopy++;
    }
}
?>

<style>
    .pr-revamp {
        --pr-ink: #0f172a;
        --pr-muted: #64748b;
        --pr-line: rgba(148, 163, 184, 0.22);
        --pr-surface: rgba(255, 255, 255, 0.96);
        --pr-surface-soft: rgba(248, 250, 252, 0.94);
        --pr-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        --pr-blue: #1d4ed8;
        --pr-cyan: #0891b2;
        --pr-emerald: #047857;
        --pr-amber: #b45309;
        --pr-rose: #be123c;
    }

    .pr-revamp .content-header {
        padding-bottom: 0;
    }

    .pr-shell {
        padding: 1.15rem;
    }

    .pr-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(14, 165, 233, 0.22), transparent 28%),
            radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.18), transparent 26%),
            linear-gradient(135deg, #0f172a 0%, #102948 48%, #143a63 100%);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .pr-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .pr-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .pr-hero h1 {
        margin: 1rem 0 0.75rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .pr-hero p {
        max-width: 44rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .pr-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.3rem;
    }

    .pr-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.82rem 1.15rem;
        border: 0;
        border-radius: 14px;
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .pr-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .pr-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
    }

    .pr-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .pr-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .pr-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .pr-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .pr-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .pr-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .pr-grid {
        display: grid;
        grid-template-columns: 1.2fr 2fr;
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .pr-panel {
        border: 1px solid var(--pr-line);
        border-radius: 24px;
        background: var(--pr-surface);
        box-shadow: var(--pr-shadow);
        overflow: hidden;
    }

    .pr-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .pr-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--pr-ink);
    }

    .pr-panel__subtitle {
        margin: 0.28rem 0 0;
        color: var(--pr-muted);
        font-size: 0.92rem;
    }

    .pr-panel__body {
        padding: 1.25rem;
    }

    .pr-flow {
        display: grid;
        gap: 0.9rem;
    }

    .pr-flow-card {
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
    }

    .pr-flow-card__top {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        align-items: center;
    }

    .pr-flow-card__step {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--pr-blue);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .pr-flow-card__title {
        margin: 0.55rem 0 0.35rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--pr-ink);
    }

    .pr-flow-card__text {
        margin: 0;
        color: var(--pr-muted);
        line-height: 1.65;
        font-size: 0.92rem;
    }

    .pr-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .pr-chip--blue {
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }

    .pr-chip--emerald {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .pr-chip--amber {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .pr-chip--slate {
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
    }

    .pr-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .pr-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.8rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: #fff;
        color: var(--pr-ink);
        font-weight: 800;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .pr-tab:hover {
        transform: translateY(-1px);
    }

    .pr-tab.is-active {
        border-color: rgba(37, 99, 235, 0.28);
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.95), rgba(219, 234, 254, 0.92));
        box-shadow: 0 18px 36px rgba(37, 99, 235, 0.14);
    }

    .pr-tab-pane {
        display: none;
    }

    .pr-tab-pane.is-active {
        display: block;
    }

    .pr-table-shell {
        border: 1px solid var(--pr-line);
        border-radius: 22px;
        background: var(--pr-surface);
        box-shadow: var(--pr-shadow);
        overflow: hidden;
    }

    .pr-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem 0;
    }

    .pr-table-wrap {
        padding: 1rem 1rem 1.1rem;
        width: 100%;
        overflow-x: auto;
    }

    .pr-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pr-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .pr-table th,
    .pr-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .pr-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.7);
    }

    .pr-table-wrap .dataTables_wrapper,
    .pr-table-wrap .dataTables_scroll,
    .pr-table-wrap .dataTables_scrollHead,
    .pr-table-wrap .dataTables_scrollBody,
    .pr-table-wrap .dataTables_scrollHeadInner {
        width: 100% !important;
    }

    .pr-table-wrap table.dataTable {
        width: 100% !important;
    }

    .pr-select2-option {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .pr-select2-option__meta {
        color: #64748b;
        font-size: 0.82rem;
    }

    .pr-doc-id {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .pr-doc-id strong {
        color: var(--pr-ink);
    }

    .pr-doc-id span {
        color: var(--pr-muted);
        font-size: 0.85rem;
    }

    .pr-status {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .pr-status--approved {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .pr-status--waiting {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .pr-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .pr-action-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 0;
    }

    .pr-action-btn--danger {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
    }

    .pr-action-btn--view {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .pr-action-btn--print {
        background: rgba(15, 23, 42, 0.08);
        color: #334155;
    }

    .pr-action-btn--planning {
        width: auto;
        padding: 0 0.95rem;
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
        font-weight: 800;
    }

    .pr-action-btn[hidden] {
        display: none !important;
    }

    .pr-alert {
        margin-top: 1rem;
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1rem;
    }

    .pr-modal .modal-dialog {
        max-width: 1200px;
    }

    .pr-modal .modal-content {
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 34px 80px rgba(15, 23, 42, 0.26);
    }

    .pr-modal .modal-header {
        padding: 1rem 1.2rem;
        color: #fff;
        border-bottom: 0;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 24%),
            linear-gradient(135deg, #0f172a, #1d4ed8);
    }

    .pr-modal .modal-title {
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .pr-modal .modal-body {
        padding: 1.2rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));
    }

    .pr-modal__section {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.84);
    }

    .pr-modal__section + .pr-modal__section {
        margin-top: 1rem;
    }

    .pr-modal__section-title {
        margin: 0 0 0.95rem;
        font-size: 0.96rem;
        font-weight: 800;
        color: #0f172a;
    }

    .pr-modal label {
        display: block;
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .pr-item-table tfoot td {
        background: #f8fafc;
        font-weight: 800;
    }

    .pr-empty {
        padding: 1rem;
        border-radius: 16px;
        text-align: center;
        background: rgba(248, 250, 252, 0.8);
        border: 1px dashed rgba(148, 163, 184, 0.36);
        color: #64748b;
    }

    @media (max-width: 1199.98px) {
        .pr-hero__grid,
        .pr-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .pr-shell {
            padding: 0.8rem;
        }

        .pr-hero__grid {
            padding: 1rem;
        }

        .pr-hero h1 {
            font-size: 1.5rem;
        }

        .pr-metric-grid {
            grid-template-columns: 1fr;
        }

        .pr-table-head,
        .pr-panel__head,
        .pr-hero__actions,
        .pr-tabs,
        .pr-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="content-wrapper pr-revamp">
    <div class="content-header">
        <div class="container-fluid pr-shell">
            <section class="pr-hero">
                <div class="pr-hero__grid">
                    <div>
                        <span class="pr-hero__eyebrow">
                            <i class="fas fa-file-signature"></i>
                            Purchase Request Workflow
                        </span>
                        <h1>Dashboard Purchase Request Logistik</h1>
                        <p>
                            Halaman ini merangkum PR dari area dan head office, memudahkan tim melihat dokumen yang masih menunggu approval,
                            progres review planning, serta kesiapan PR untuk diteruskan menjadi PO.
                        </p>
                        <div class="pr-hero__actions">
                            <button type="button" class="pr-btn pr-btn--light btn_tambah_pr" data-toggle="modal" data-target="#modal_tambah_pr">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Purchase Request
                            </button>
                        </div>
                    </div>

                    <div class="pr-metric-grid">
                        <div class="pr-metric">
                            <span class="pr-metric__label">Total Dokumen</span>
                            <span class="pr-metric__value"><?= number_format($totalAll, 0, ',', '.') ?></span>
                            <span class="pr-metric__hint">Gabungan PR area dan head office yang sudah tercatat.</span>
                        </div>
                        <div class="pr-metric">
                            <span class="pr-metric__label">Waiting Approval</span>
                            <span class="pr-metric__value"><?= number_format($totalWaiting, 0, ',', '.') ?></span>
                            <span class="pr-metric__hint">Dokumen yang masih bergerak di alur approval.</span>
                        </div>
                        <div class="pr-metric">
                            <span class="pr-metric__label">Approved</span>
                            <span class="pr-metric__value"><?= number_format($totalApproved, 0, ',', '.') ?></span>
                            <span class="pr-metric__hint">Siap ditindaklanjuti ke proses PO oleh tim logistik.</span>
                        </div>
                        <div class="pr-metric">
                            <span class="pr-metric__label">Hardcopy Uploaded</span>
                            <span class="pr-metric__value"><?= number_format($totalHardcopy, 0, ',', '.') ?></span>
                            <span class="pr-metric__hint">Dokumen PR yang sudah punya lampiran PDF di sistem.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success pr-alert"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger pr-alert"><?= $flashError ?></div>
            <?php endif; ?>


            <section class="pr-table-shell" style="margin-top: 1.2rem;">
                <div class="pr-table-head">
                    <div>
                        <h2 class="pr-panel__title">Daftar Purchase Request</h2>
                        <p class="pr-panel__subtitle">List utama dengan visual status yang lebih jelas untuk tindak lanjut dokumen.</p>
                    </div>
                    <span class="pr-chip pr-chip--blue"><i class="fas fa-table"></i> Monitoring PR</span>
                </div>
                <div class="pr-table-wrap">
                    <div class="pr-tabs">
                        <?php if ($isHoUser): ?>
                            <button type="button" class="pr-tab is-active" data-pane="ho">
                                <i class="fas fa-city"></i>
                                PR Head Office
                                <span class="pr-chip pr-chip--slate"><?= number_format($totalHo, 0, ',', '.') ?></span>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="pr-tab" data-pane="area">
                            <i class="fas fa-map-marked-alt"></i>
                            PR Area
                            <span class="pr-chip pr-chip--slate"><?= number_format($totalArea, 0, ',', '.') ?></span>
                        </button>
                    </div>

                    <div class="pr-tab-pane" data-pane="area">
                        <table id="tabel_purchase_request_area" class="table pr-table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Dokumen PR</th>
                                    <th>Origin</th>
                                    <th>Bowheer</th>
                                    <th>Lokasi</th>
                                    <th>Nama Project</th>
                                    <th>Pembuat</th>
                                    <th>Status</th>
                                    <th>Status NODIN</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $numbering = 1; ?>
                                <?php foreach ($list_purchase_request_area as $value): ?>
                                    <tr>
                                        <td><?= $numbering++ ?></td>
                                        <td>
                                            <div class="pr-doc-id">
                                                <strong><?= $value['nomor_purchase_request'] ?></strong>
                                                <span><?= $value['tanggal_pembuatan'] ?></span>
                                            </div>
                                        </td>
                                        <td><span class="pr-chip pr-chip--slate"><?= $value['origin_pr_label'] ?></span></td>
                                        <td><?= $value['nama_project'] ?></td>
                                        <td><?= $value['kota_lokasi_gudang'] ?></td>
                                        <td><?= $value['nama_projects'] ?></td>
                                        <td><?= $value['nama_pembuat'] ?></td>
                                        <td>
                                            <span class="pr-status pr-status--<?= $value['workflow_status_tone'] ?>">
                                                <i class="fas fa-circle"></i>
                                                <?= $value['workflow_status_label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="pr-status pr-status--<?= $value['nodin_status_tone'] ?>">
                                                <i class="fas fa-file-signature"></i>
                                                <?= $value['nodin_status_label'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="pr-actions">
                                                <a class="pr-action-btn pr-action-btn--danger btn-delete-purchase-request" href="javascript:void(0);" data-id="<?= $value['id_purchase_request'] ?>" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <a class="pr-action-btn pr-action-btn--view" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') . '/' . $value['id_purchase_request'] ?>" title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (!empty($value['is_fully_approved'])): ?>
                                                    <a class="pr-action-btn pr-action-btn--print" href="<?= base_url('Logistik_Purchase_Request/print_purchase_request/' . $value['id_purchase_request']) ?>" target="_blank" title="Print">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a class="pr-action-btn pr-action-btn--planning" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') . '/' . $value['id_purchase_request'] ?>" <?= ((!$isSuperAdmin && $userValidation != 'Planning') || (int) ($value['approved_planning'] ?? 0) === 1) ? 'hidden' : '' ?>>
                                                    Planning
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($isHoUser): ?>
                        <div class="pr-tab-pane is-active" data-pane="ho">
                            <table id="tabel_purchase_request_ho" class="table pr-table table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Dokumen PR</th>
                                        <th>Origin</th>
                                        <th>Bowheer</th>
                                        <th>Lokasi</th>
                                        <th>Nama Project</th>
                                        <th>Pembuat</th>
                                        <th>Status</th>
                                        <th>Status NODIN</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $numbering = 1; ?>
                                    <?php foreach ($list_purchase_request_ho as $value): ?>
                                        <tr>
                                            <td><?= $numbering++ ?></td>
                                            <td>
                                                <div class="pr-doc-id">
                                                    <strong><?= $value['nomor_purchase_request'] ?></strong>
                                                    <span><?= $value['tanggal_pembuatan'] ?></span>
                                                </div>
                                            </td>
                                            <td><span class="pr-chip pr-chip--slate"><?= $value['origin_pr_label'] ?></span></td>
                                            <td><?= $value['nama_project'] ?></td>
                                            <td><?= $value['kota_lokasi_gudang'] ?></td>
                                            <td><?= $value['nama_projects'] ?></td>
                                            <td><?= $value['nama_pembuat'] ?></td>
                                            <td>
                                                <span class="pr-status pr-status--<?= $value['workflow_status_tone'] ?>">
                                                    <i class="fas fa-circle"></i>
                                                    <?= $value['workflow_status_label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="pr-status pr-status--<?= $value['nodin_status_tone'] ?>">
                                                    <i class="fas fa-file-signature"></i>
                                                    <?= $value['nodin_status_label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="pr-actions">
                                                    <a class="pr-action-btn pr-action-btn--danger btn-delete-purchase-request" href="javascript:void(0);" data-id="<?= $value['id_purchase_request'] ?>" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a class="pr-action-btn pr-action-btn--view" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') . '/' . $value['id_purchase_request'] ?>" title="Lihat detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (!empty($value['is_fully_approved'])): ?>
                                                        <a class="pr-action-btn pr-action-btn--print" href="<?= base_url('Logistik_Purchase_Request/print_purchase_request/' . $value['id_purchase_request']) ?>" target="_blank" title="Print">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a class="pr-action-btn pr-action-btn--planning" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') . '/' . $value['id_purchase_request'] ?>" <?= ((!$isSuperAdmin && $userValidation != 'Planning') || (int) ($value['approved_planning'] ?? 0) === 1) ? 'hidden' : '' ?>>
                                                        Planning
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade pr-modal" id="modal_tambah_pr" data-backdrop="static">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <form action="<?= base_url('Logistik_Purchase_Request/add_purchase_request') ?>" method="post" id="tambah_purchase_reqeust" enctype="multipart/form-data">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title">Tambah Purchase Request</h4>
                        <small>Susun header dokumen lalu lengkapi material yang diajukan.</small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <section class="pr-modal__section">
                        <h5 class="pr-modal__section-title">Informasi Dokumen</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor PR</label>
                                    <input type="text" class="form-control" name="nomor_pr" id="nomor_pr" placeholder="TEC.001/TKM-SK/PR/I/2025" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal PR</label>
                                    <input type="date" class="form-control" name="tanggal_upload_pr" value="<?= (new \DateTime())->format('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bowheer</label>
                                    <select name="nama_bowher" id="nama_bowher" class="form-control">
                                        <option value="">Pilih Salah Satu</option>
                                        <?php foreach ($get_master_project as $value): ?>
                                            <option value="<?= $value['nama_bowheer'] ?>" data-id-bowheer="<?= $value['nama_bowheer'] ?>">
                                                <?= $value['nama_bowheer'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lokasi Project</label>
                                    <select name="lokasi_project" id="lokasi_project" class="form-control">
                                        <option value="">Pilih Salah Satu</option>
                                        <?php foreach ($list_master_gudang as $value): ?>
                                            <option value="<?= $value['id_lokasi_gudang'] ?>"><?= $value['kota_lokasi_gudang'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pr-modal__section">
                        <h5 class="pr-modal__section-title">Informasi Project dan Pengiriman</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Project / Cluster</label>
                                    <input type="text" class="form-control" name="nama_project" id="nama_project" placeholder="MF RING 03 / CLUSTER MELATI JAYA">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor SP</label>
                                    <input type="text" class="form-control" name="nomor_sp" id="nomor_sp">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal SP</label>
                                    <input type="date" class="form-control" name="tanggal_sp">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Estimasi Pengiriman</label>
                                    <input type="date" class="form-control" name="tanggal_pengiriman" value="<?= (new \DateTime())->format('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label>Pilih Material</label>
                                    <select name="nama_material" id="nama_material" class="form-control">
                                        <option value="">Pilih Salah Satu</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="pr-modal__section">
                        <h5 class="pr-modal__section-title">Detail Material Request</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered pr-item-table" id="table_item_purchase_request">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 30%;">Nama</th>
                                        <th>Kepemilikan</th>
                                        <th>Satuan</th>
                                        <th style="width: 15%;">BOQ</th>
                                        <th style="width: 15%;">Stock Area</th>
                                        <th style="width: 15%;">Stok Request</th>
                                        <th>Keterangan</th>
                                        <th>Hapus</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">TOTAL</td>
                                        <td><b id="pr_total_boq">0</b></td>
                                        <td><b id="pr_total_stok_area">0</b></td>
                                        <td><b id="pr_total_qty_request">0</b></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="pr-empty mt-3" id="pr_item_hint">
                            Pilih material dari daftar di atas untuk mulai menyusun isi purchase request.
                        </div>
                    </section>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Purchase Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let counter = 1;

        function parsePrNumber(value) {
            const normalized = String(value || '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
            return parseFloat(normalized) || 0;
        }

        function formatPrNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0
            }).format(parsePrNumber(value));
        }

        function normalizePrLiveNumbers(scope) {
            $(scope || document).find('.js-live-number').each(function () {
                const $input = $(this);
                if ($input.val() === '') {
                    return;
                }

                $input.val(formatPrNumber($input.val()));
            });
        }

        function formatMaterialOption(option) {
            if (!option.id) {
                return option.text;
            }

            const ownership = $(option.element).data('kepemilikan-item') || '';
            const category = $(option.element).data('kategori-item') || '';
            const stock = $(option.element).data('stok-area');
            const stockLabel = stock !== undefined && stock !== null ? `Stok: ${stock}` : '';
            const metaParts = [ownership, category, stockLabel].filter(Boolean);

            return $(`
                <div class="pr-select2-option">
                    <span>${option.text}</span>
                    <span class="pr-select2-option__meta">${metaParts.join(' | ')}</span>
                </div>
            `);
        }

        const areaTable = $('#tabel_purchase_request_area').DataTable({
            responsive: true,
            pageLength: 10,
            autoWidth: false,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        const hoTableElement = $('#tabel_purchase_request_ho');
        if (hoTableElement.length) {
            hoTableElement.DataTable({
                responsive: true,
                pageLength: 10,
                autoWidth: false,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                }
            });
        }

        $('#nama_bowher, #lokasi_project').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Salah Satu',
            width: '100%',
            dropdownParent: $('#modal_tambah_pr')
        });

        $('#nama_material').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Salah Satu',
            width: '100%',
            dropdownParent: $('#modal_tambah_pr'),
            templateResult: formatMaterialOption,
            templateSelection: function(option) {
                return option.text || 'Pilih Salah Satu';
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        $('.pr-tab').on('click', function () {
            const pane = $(this).data('pane');
            $('.pr-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $('.pr-tab-pane').removeClass('is-active');
            $('.pr-tab-pane[data-pane="' + pane + '"]').addClass('is-active');
        });

        function updateItemHint() {
            const hasRows = $('#table_item_purchase_request tbody tr').length > 0;
            $('#pr_item_hint').toggle(!hasRows);
        }

        function updateTotalKeseluruhan() {
            let totalBoq = 0;
            let totalStokArea = 0;
            let totalSemua = 0;

            $('[name^="boq_"]').each(function () {
                const rawValue = ($(this).val() || '').toString();
                const total = parsePrNumber(rawValue);
                totalBoq += total;
            });

            $('[name^="stok_area_"]').each(function () {
                const rawValue = ($(this).val() || '').toString();
                const total = parsePrNumber(rawValue);
                totalStokArea += total;
            });

            $('[name^="stok_request_"]').each(function () {
                const rawValue = ($(this).val() || '').toString();
                const total = parsePrNumber(rawValue);
                totalSemua += total;
            });

            $('#pr_total_boq').text(totalBoq || 0);
            $('#pr_total_stok_area').text(totalStokArea || 0);
            $('#pr_total_qty_request').text(totalSemua || 0);
            updateItemHint();
        }

        function resetMaterialBuilder() {
            $('#table_item_purchase_request tbody').empty();
            $('#nama_material').empty().append('<option value="">Pilih Jenis Material</option>').trigger('change');
            counter = 1;
            updateTotalKeseluruhan();
        }

        function loadMaterialOptions() {
            const idBowheer = $('#nama_bowher').find(':selected').data('id-bowheer');
            const idLokasiGudang = $('#lokasi_project').val();

            if (!idBowheer) {
                resetMaterialBuilder();
                return;
            }

            $.ajax({
                url: "<?= base_url('Logistik_Purchase_Request/get_material_options') ?>",
                type: "GET",
                data: {
                    id_bowheer: idBowheer.toString(),
                    id_lokasi_gudang: idLokasiGudang
                },
                dataType: "json",
                success: function (response) {
                    $('#nama_material').empty().append('<option value="">Pilih Jenis Material</option>');

                    $.each(response, function (index, project) {
                        const ownershipLabel = project.nama_kepemilikan_item || '-';
                        const optionLabel = `${project.nama_item} - ${ownershipLabel}`;

                        $('#nama_material').append(
                            `<option 
                                value="${project.id_kode_item}" 
                                data-satuan-item="${project.satuan_item || ''}"
                                data-kepemilikan-item="${ownershipLabel}"
                                data-stok-area="${project.stok_area_tersedia || 0}"
                                data-kategori-item="${project.kategori_item || ''}"
                            >${optionLabel}</option>`
                        );
                    });

                    $('#nama_material').trigger('change');
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        $('#nama_bowher').change(function () {
            resetMaterialBuilder();
            loadMaterialOptions();
        });

        $('#lokasi_project').change(function () {
            resetMaterialBuilder();
            loadMaterialOptions();
        });

        $('#nama_material').on('change', function () {
            if ($(this).val() === "") {
                return;
            }

            const selectedValue = $('#nama_material').val();
            const selectedText = $('#nama_material option:selected').text();
            const selectedSatuan = $('#nama_material option:selected').data('satuan-item');
            const selectedKepemilikan = $('#nama_material option:selected').data('kepemilikan-item');
            const selectedStokArea = $('#nama_material option:selected').data('stok-area') || 0;
            const selectedKategori = $('#nama_material option:selected').data('kategori-item') || '';

            $('#table_item_purchase_request tbody').append(`
                <tr>
                    <td>${counter}</td>
                    <td>
                        <input type="hidden" name="id_kode_item_[${counter}]" value="${selectedValue}">
                        <strong>${selectedText}</strong>
                        <div class="text-muted small">${selectedKategori}</div>
                    </td>
                    <td>${selectedKepemilikan || '-'}</td>
                    <td>${selectedSatuan || '-'}</td>
                    <td><input type="text" inputmode="numeric" class="form-control js-live-number" name="boq_[${counter}]" autocomplete="off" placeholder="0"></td>
                    <td><input type="text" inputmode="numeric" class="form-control js-live-number" name="stok_area_[${counter}]" autocomplete="off" value="${formatPrNumber(selectedStokArea)}" readonly></td>
                    <td><input type="text" inputmode="numeric" class="form-control js-live-number" name="stok_request_[${counter}]" autocomplete="off" placeholder="5.000" required></td>
                    <td><input type="text" class="form-control" name="keterangan_[${counter}]" autocomplete="off" placeholder="Keterangan"></td>
                    <td><button class="btn btn-danger hapus-item" type="button"><i class="fa fa-trash"></i></button></td>
                </tr>
            `);

            counter++;
            $('#nama_material').val("").trigger('change');
            normalizePrLiveNumbers('#table_item_purchase_request tbody tr:last');
            updateTotalKeseluruhan();
        });

        $(document).on('click', '.hapus-item', function () {
            $(this).closest('tr').remove();

            $('#table_item_purchase_request tbody tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });

            counter = $('#table_item_purchase_request tbody tr').length + 1;
            updateTotalKeseluruhan();
        });

        $('#modal_tambah_pr').on('hidden.bs.modal', function () {
            $('#table_item_purchase_request tbody').empty();
            $('#nama_project').val('');
            $('#nama_bowher').val('').trigger('change');
            $('#nama_material').empty().append('<option value="">Pilih Salah Satu</option>').trigger('change');
            $('#nomor_pr').val('');
            $('#nomor_sp').val('');
            $('#lokasi_project').val('').trigger('change');
            updateTotalKeseluruhan();
            counter = 1;
        });

        $('.btn_tambah_pr').on('click', function () {
            updateTotalKeseluruhan();
        });

        $(document).on('input', '[name^="stok_request_["]', function () {
            $(this).val(formatPrNumber($(this).val()));
            updateTotalKeseluruhan();
        });

        $(document).on('input', '[name^="boq_["]', function () {
            $(this).val(formatPrNumber($(this).val()));
            updateTotalKeseluruhan();
        });

        $(document).on('input', '[name^="stok_area_["]', function () {
            $(this).val(formatPrNumber($(this).val()));
            updateTotalKeseluruhan();
        });

        $("#tambah_purchase_reqeust").submit(function (event) {
            const errorMessage = [];

            if (!$("input[name='tanggal_upload_pr']").val()) {
                errorMessage.push("Tanggal PR harus diisi.");
            }

            const requiredFields = {
                "#nama_bowher": "Nama Bowheer",
                "#lokasi_project": "Lokasi Proyek"
            };

            $.each(requiredFields, function (selector, fieldName) {
                if ($(selector).val() === "") {
                    errorMessage.push(fieldName + " harus diisi.");
                }
            });

            if ($("#table_item_purchase_request tbody tr").length === 0) {
                errorMessage.push("Minimal harus ada satu item dalam purchase request.");
            }

            if (errorMessage.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum lengkap',
                    html: errorMessage.join('<br>')
                });
                event.preventDefault();
                return;
            }

            $('#tambah_purchase_reqeust .js-live-number').each(function () {
                $(this).val(parsePrNumber($(this).val() || 0));
            });
        });

        $(".btn-delete-purchase-request").click(function () {
            let id = $(this).data("id");

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "<?= base_url('Logistik_Purchase_Request/delete_purchase_request') ?>/" + id;
                }
            });
        });

        updateTotalKeseluruhan();
        normalizePrLiveNumbers('#modal_tambah_pr');
    });
</script>
