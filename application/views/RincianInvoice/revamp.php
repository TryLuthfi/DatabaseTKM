<?php
$invoiceRows = $invoiceRows ?? [];
$filterOptions = $filterOptions ?? [];

if (!function_exists('rincian_invoice_money')) {
    function rincian_invoice_money($value)
    {
        return 'RP. ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('rincian_invoice_number')) {
    function rincian_invoice_number($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('rincian_invoice_percent')) {
    function rincian_invoice_percent($target, $achieved)
    {
        $target = (float) $target;
        $achieved = (float) $achieved;
        if ($target == 0 && $achieved > 0) {
            return '100,0 %';
        }
        if ($target == 0) {
            return '0,0 %';
        }
        return number_format(($achieved / $target) * 100, 1, ',', '.') . ' %';
    }
}

$totalTarget = 0;
$totalAchieved = 0;
$projects = [];
$areas = [];
$regionals = [];
$pics = [];
foreach ($invoiceRows as $row) {
    $totalTarget += (float) ($row['qty_target'] ?? 0);
    $totalAchieved += (float) ($row['qty_achiev_target'] ?? 0);
    if (!empty($row['id_bowheer'])) {
        $projects[(string) $row['id_bowheer']] = true;
    }
    if (!empty($row['area_target'])) {
        $areas[(string) $row['area_target']] = true;
    }
    if (!empty($row['regional_target'])) {
        $regionals[(string) $row['regional_target']] = true;
    }
    if (!empty($row['pic_user'])) {
        $pics[(string) $row['pic_user']] = true;
    }
}
$totalOutstanding = max($totalTarget - $totalAchieved, 0);
?>

<style>
    .rincian-invoice-revamp {
        --rinci-ink: #0f172a;
        --rinci-muted: #64748b;
        --rinci-line: rgba(148, 163, 184, 0.22);
        --rinci-surface: rgba(255, 255, 255, 0.96);
        --rinci-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }

    .rincian-shell {
        padding: 1rem;
    }

    .rincian-hero {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.18);
        color: #f8fafc;
    }

    .rincian-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.2rem;
        padding: 1.25rem;
    }

    .rincian-hero__eyebrow,
    .rincian-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .rincian-hero__eyebrow {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .rincian-chip {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
    }

    .rincian-hero h1 {
        margin: 0.9rem 0 0.55rem;
        color: #fff;
        font-size: 1.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .rincian-hero p {
        max-width: 48rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.9);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .rincian-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.05rem;
    }

    .rincian-hero__stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .rincian-hero-stat {
        min-height: 90px;
        padding: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.11);
    }

    .rincian-hero-stat__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .rincian-hero-stat__value {
        display: block;
        margin-top: 0.3rem;
        color: #fff;
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1;
    }

    .rincian-hero-stat__hint {
        display: block;
        margin-top: 0.5rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .rincian-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 38px;
        padding: 0.6rem 0.9rem;
        border: 0;
        border-radius: 8px;
        font-weight: 800;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .rincian-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .rincian-btn--primary {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .rincian-btn--light {
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.24);
    }

    .rincian-panel,
    .rincian-table-shell {
        margin-top: 1rem;
        border: 1px solid var(--rinci-line);
        border-radius: 12px;
        background: var(--rinci-surface);
        box-shadow: var(--rinci-shadow);
        overflow: hidden;
    }

    .rincian-panel__head,
    .rincian-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.05rem 1.15rem 0;
    }

    .rincian-panel__title {
        margin: 0;
        color: var(--rinci-ink);
        font-size: 1rem;
        font-weight: 900;
    }

    .rincian-panel__subtitle {
        margin: 0.25rem 0 0;
        color: var(--rinci-muted);
        font-size: 0.88rem;
    }

    .rincian-panel__body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .rincian-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .rincian-filter-grid label,
    .rincian-modal label {
        margin-bottom: 0.42rem;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .rincian-invoice-revamp .form-control,
    .rincian-modal .form-control {
        min-height: 42px;
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.34);
        background-color: rgba(255, 255, 255, 0.94);
        color: #0f172a;
        font-size: 0.9rem;
        box-shadow: none;
    }

    .rincian-modal .form-control:not([readonly]):not(:disabled),
    .rincian-modal select.form-control:not(:disabled) + .select2-container .select2-selection {
        border-color: rgba(37, 99, 235, 0.42);
        background: #ffffff;
        box-shadow: inset 4px 0 0 rgba(37, 99, 235, 0.52);
    }

    .rincian-modal .form-control[readonly],
    .rincian-modal .form-control:disabled,
    .rincian-modal select.form-control:disabled + .select2-container .select2-selection {
        border-color: rgba(148, 163, 184, 0.24);
        background: #f1f5f9;
        color: #64748b;
        box-shadow: inset 4px 0 0 rgba(100, 116, 139, 0.28);
    }

    .rincian-field-note {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .rincian-invoice-revamp .select2-container,
    .rincian-modal .select2-container {
        width: 100% !important;
    }

    .rincian-invoice-revamp .select2-container--default .select2-selection--single,
    .rincian-invoice-revamp .select2-container--bootstrap4 .select2-selection--single,
    .rincian-modal .select2-container--default .select2-selection--single,
    .rincian-modal .select2-container--bootstrap4 .select2-selection--single {
        min-height: 42px;
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.34);
        background: rgba(255, 255, 255, 0.96);
        display: flex;
        align-items: center;
        box-shadow: none;
    }

    .rincian-invoice-revamp .select2-container--default .select2-selection--single .select2-selection__rendered,
    .rincian-invoice-revamp .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
    .rincian-modal .select2-container--default .select2-selection--single .select2-selection__rendered,
    .rincian-modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #64748b;
        line-height: 40px;
        padding-left: 0.85rem;
        padding-right: 2rem;
        width: 100%;
    }

    .rincian-invoice-revamp .select2-container--default .select2-selection--single .select2-selection__placeholder,
    .rincian-modal .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }

    .rincian-invoice-revamp .select2-container--default .select2-selection--single .select2-selection__arrow,
    .rincian-invoice-revamp .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow,
    .rincian-modal .select2-container--default .select2-selection--single .select2-selection__arrow,
    .rincian-modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 0.45rem;
    }

    .rincian-invoice-revamp .select2-container--default.select2-container--focus .select2-selection--single,
    .rincian-invoice-revamp .select2-container--default.select2-container--open .select2-selection--single,
    .rincian-modal .select2-container--default.select2-container--focus .select2-selection--single,
    .rincian-modal .select2-container--default.select2-container--open .select2-selection--single {
        border-color: rgba(37, 99, 235, 0.46);
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12);
    }

    .select2-dropdown {
        border-color: rgba(148, 163, 184, 0.34);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
    }

    .select2-results__option {
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background-color: #2563eb;
        color: #fff;
    }

    .rincian-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 0.85rem;
    }

    .rincian-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.9rem;
        margin-top: 1rem;
    }

    .rincian-kpi-card {
        display: flex;
        align-items: center;
        gap: 1.05rem;
        min-height: 104px;
        padding: 1.15rem 1.2rem;
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-top: 5px solid #2563eb;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.92));
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .rincian-kpi-card--green {
        border-color: rgba(187, 247, 208, 0.95);
        border-top-color: #16a34a;
    }

    .rincian-kpi-card--red {
        border-color: rgba(254, 202, 202, 0.95);
        border-top-color: #ef4444;
    }

    .rincian-kpi-card__icon {
        width: 64px;
        height: 64px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 64px;
        border-radius: 6px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        font-size: 1.65rem;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
    }

    .rincian-kpi-card--green .rincian-kpi-card__icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    .rincian-kpi-card--red .rincian-kpi-card__icon {
        background: linear-gradient(135deg, #f87171, #dc2626);
    }

    .rincian-kpi-card__label {
        display: block;
        margin-bottom: 0.55rem;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .rincian-kpi-card__value {
        display: block;
        color: var(--rinci-ink);
        font-size: 1.22rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .rincian-kpi-card__value--green {
        color: #059669;
    }

    .rincian-kpi-card__value--red {
        color: #dc2626;
    }

    .rincian-active-filters {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.55rem;
        width: 100%;
        margin-top: 0.85rem;
    }

    .rincian-active-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        padding: 0.5rem 0.82rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
        font-size: 0.86rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .rincian-active-filter--empty {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
    }

    .rincian-table-wrap {
        padding: 1rem 1.1rem 1.1rem;
    }

    .rincian-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .rincian-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
        font-size: 0.74rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rincian-table th,
    .rincian-table td {
        padding: 0.76rem 0.68rem;
        vertical-align: middle;
        white-space: nowrap;
        border-top: 1px solid rgba(226, 232, 240, 0.72);
    }

    .rincian-table tfoot th {
        background: #f8fafc;
        color: var(--rinci-ink);
        font-weight: 900;
    }

    .rincian-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.62rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .rincian-status--success { background: rgba(22, 163, 74, 0.1); color: #15803d; }
    .rincian-status--info { background: rgba(37, 99, 235, 0.1); color: #1d4ed8; }
    .rincian-status--warning { background: rgba(245, 158, 11, 0.14); color: #b45309; }
    .rincian-status--danger { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

    .rincian-modal .modal-content {
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 34px 80px rgba(15, 23, 42, 0.26);
    }

    .rincian-modal .modal-header {
        color: #fff;
        border-bottom: 0;
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
    }

    .rincian-modal .modal-title {
        font-weight: 900;
    }

    .rincian-modal-note {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 0.86rem;
    }

    .rincian-hidden {
        display: none !important;
    }

    @media (max-width: 1199.98px) {
        .rincian-hero__grid,
        .rincian-filter-grid,
        .rincian-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .rincian-shell {
            padding: 0.75rem;
        }

        .rincian-hero__grid,
        .rincian-hero__stats,
        .rincian-filter-grid,
        .rincian-kpi-grid {
            grid-template-columns: 1fr;
        }

        .rincian-panel__head,
        .rincian-table-head,
        .rincian-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="content-wrapper rincian-invoice-revamp">
    <div class="content-header">
        <div class="container-fluid rincian-shell">
            <section class="rincian-hero">
                <div class="rincian-hero__grid">
                    <div>
                        <span class="rincian-hero__eyebrow">
                            <i class="fas fa-receipt"></i>
                            Rincian Invoice Operations
                        </span>
                        <h1>Dashboard rincian invoice PT. TKM</h1>
                        <p>
                            Pantau realisasi invoice per project, PIC, regional, area, bulan, dan week. Halaman ini juga
                            dipakai untuk menambah atau memperbarui realisasi invoice ke target berjalan.
                        </p>
                        <div class="rincian-hero__actions">
                            <button type="button" class="rincian-btn rincian-btn--primary" data-toggle="modal" data-target="#modalRincianTambahInvoice">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Invoice
                            </button>
                            <a href="<?= base_url('RincianInvoice') ?>" class="rincian-btn rincian-btn--light">
                                <i class="fas fa-table"></i>
                                Halaman Lama
                            </a>
                        </div>
                    </div>

                    <div class="rincian-hero__stats">
                        <div class="rincian-hero-stat">
                            <span class="rincian-hero-stat__label">Project Aktif</span>
                            <span class="rincian-hero-stat__value"><?= rincian_invoice_number(count($projects)) ?></span>
                            <span class="rincian-hero-stat__hint">Project dengan data target invoice.</span>
                        </div>
                        <div class="rincian-hero-stat">
                            <span class="rincian-hero-stat__label">Regional</span>
                            <span class="rincian-hero-stat__value"><?= rincian_invoice_number(count($regionals)) ?></span>
                            <span class="rincian-hero-stat__hint">Wilayah yang masuk report.</span>
                        </div>
                        <div class="rincian-hero-stat">
                            <span class="rincian-hero-stat__label">Area / Kota</span>
                            <span class="rincian-hero-stat__value"><?= rincian_invoice_number(count($areas)) ?></span>
                            <span class="rincian-hero-stat__hint">Area invoice yang dimonitor.</span>
                        </div>
                        <div class="rincian-hero-stat">
                            <span class="rincian-hero-stat__label">PIC HO</span>
                            <span class="rincian-hero-stat__value"><?= rincian_invoice_number(count($pics)) ?></span>
                            <span class="rincian-hero-stat__hint">PIC yang terhubung ke project.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rincian-panel">
                <div class="rincian-panel__head">
                    <div>
                        <span class="rincian-chip"><i class="fas fa-sliders-h"></i> Kontrol Data</span>
                        <h2 class="rincian-panel__title">Filter Data</h2>
                        <p class="rincian-panel__subtitle">Filter utama bersifat descendant, pilihan di kanan akan mengikuti pilihan sebelumnya.</p>
                    </div>
                </div>
                <div class="rincian-panel__body">
                    <div class="rincian-filter-grid">
                        <div class="form-group">
                            <label for="rincian_filter_project">Project</label>
                            <select id="rincian_filter_project" class="form-control select2">
                                <option value="">Semua Project</option>
                                <?php foreach (($filterOptions['projects'] ?? []) as $project): ?>
                                    <option value="<?= (int) ($project['id_bowheer'] ?? 0) ?>"><?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rincian_filter_pic">PIC</label>
                            <select id="rincian_filter_pic" class="form-control select2">
                                <option value="">Semua PIC</option>
                                <?php foreach (($filterOptions['pics'] ?? []) as $pic): ?>
                                    <option value="<?= htmlspecialchars($pic['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pic['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rincian_filter_regional">Regional</label>
                            <select id="rincian_filter_regional" class="form-control select2">
                                <option value="">Semua Regional</option>
                                <?php foreach (($filterOptions['regionals'] ?? []) as $regional): ?>
                                    <option value="<?= htmlspecialchars($regional['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($regional['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rincian_filter_area">Area</label>
                            <select id="rincian_filter_area" class="form-control select2">
                                <option value="">Semua Area</option>
                                <?php foreach (($filterOptions['areas'] ?? []) as $area): ?>
                                    <option value="<?= htmlspecialchars($area['area_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($area['area_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rincian_filter_month">Bulan</label>
                            <select id="rincian_filter_month" class="form-control select2">
                                <option value="">Semua Bulan</option>
                                <?php foreach (($filterOptions['months'] ?? []) as $month): ?>
                                    <option value="<?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rincian_filter_week">Week</label>
                            <select id="rincian_filter_week" class="form-control select2">
                                <option value="">Semua Week</option>
                                <?php foreach (($filterOptions['weeks'] ?? []) as $week): ?>
                                    <option value="<?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="rincian-actions">
                        <button type="button" class="rincian-btn rincian-btn--light" id="rincian_reset_filter">
                            <i class="fas fa-undo-alt"></i>
                            Reset
                        </button>
                        <button type="button" class="rincian-btn rincian-btn--primary" id="rincian_apply_filter">
                            <i class="fas fa-search"></i>
                            Terapkan
                        </button>
                    </div>
                </div>
            </section>

            <section class="rincian-kpi-grid" aria-label="Ringkasan Rincian Invoice">
                <article class="rincian-kpi-card">
                    <span class="rincian-kpi-card__icon"><i class="fas fa-bullseye"></i></span>
                    <div>
                        <span class="rincian-kpi-card__label">Target Invoice</span>
                        <span class="rincian-kpi-card__value" id="rincian_kpi_target"><?= rincian_invoice_money($totalTarget) ?></span>
                    </div>
                </article>
                <article class="rincian-kpi-card rincian-kpi-card--green">
                    <span class="rincian-kpi-card__icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <span class="rincian-kpi-card__label">Achieved Invoice</span>
                        <span class="rincian-kpi-card__value rincian-kpi-card__value--green" id="rincian_kpi_achieved"><?= rincian_invoice_money($totalAchieved) ?></span>
                    </div>
                </article>
                <article class="rincian-kpi-card rincian-kpi-card--red">
                    <span class="rincian-kpi-card__icon"><i class="fas fa-hourglass-half"></i></span>
                    <div>
                        <span class="rincian-kpi-card__label">Sisa Invoice</span>
                        <span class="rincian-kpi-card__value rincian-kpi-card__value--red" id="rincian_kpi_outstanding"><?= rincian_invoice_money($totalOutstanding) ?></span>
                    </div>
                </article>
                <article class="rincian-kpi-card">
                    <span class="rincian-kpi-card__icon"><i class="fas fa-percentage"></i></span>
                    <div>
                        <span class="rincian-kpi-card__label">Persentase Invoice</span>
                        <span class="rincian-kpi-card__value rincian-kpi-card__value--green" id="rincian_kpi_percent"><?= rincian_invoice_percent($totalTarget, $totalAchieved) ?></span>
                    </div>
                </article>
            </section>

            <section class="rincian-table-shell">
                <div class="rincian-table-head">
                    <div>
                        <span class="rincian-chip"><i class="fas fa-layer-group"></i> Report Detail</span>
                        <h2 class="rincian-panel__title">Breakdown Rincian Invoice</h2>
                        <p class="rincian-panel__subtitle">Tabel ini dihitung ulang dari data invoice sesuai filter aktif.</p>
                    </div>
                </div>
                <div class="rincian-active-filters" id="rincian_active_filters">
                    <span class="rincian-active-filter rincian-active-filter--empty"><i class="fas fa-filter"></i> Semua data</span>
                </div>
                <div class="rincian-table-wrap table-responsive">
                    <table class="table rincian-table" id="rincian_invoice_table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Regional</th>
                                <th>Area</th>
                                <th>Target</th>
                                <th>Achieved</th>
                                <th>Sisa</th>
                                <th>Achieved %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5">Total</th>
                                <th id="rincian_total_target">RP. 0</th>
                                <th id="rincian_total_achieved">RP. 0</th>
                                <th id="rincian_total_outstanding">RP. 0</th>
                                <th id="rincian_total_percent">0,0 %</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<form id="formRincianTambahInvoice" action="<?= site_url('RincianInvoice/addInvoice') ?>" method="post">
    <div class="modal fade rincian-modal" id="modalRincianTambahInvoice" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Tambah Invoice</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="rincian-modal-note mb-3">
                        Pilih project, area, bulan, dan week. Target serta realisasi saat ini akan dibaca otomatis dari data target invoice.
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="add_rincian_project">Project / Bowheer</label>
                            <select id="add_rincian_project" name="addfilter_bowheer" class="form-control">
                                <option value="">Pilih Project</option>
                                <?php foreach (($filterOptions['projects'] ?? []) as $project): ?>
                                    <option value="<?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-id="<?= (int) ($project['id_bowheer'] ?? 0) ?>"><?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_rincian_area">Area Existing</label>
                            <select id="add_rincian_area" class="form-control">
                                <option value="">Pilih Area</option>
                            </select>
                            <input type="hidden" name="addfilter_area" id="add_rincian_area_value">
                            <button type="button" class="btn btn-link px-0 mt-2" id="add_rincian_show_new_area">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Area Baru
                            </button>
                            <button type="button" class="btn btn-link text-danger px-0 mt-2 rincian-hidden" id="add_rincian_cancel_new_area">
                                <i class="fas fa-times-circle"></i>
                                Batal Area Baru
                            </button>
                        </div>
                        <div class="form-group col-md-6 rincian-new-area-field rincian-hidden">
                            <label for="add_rincian_new_area">Area Baru</label>
                            <input type="text" id="add_rincian_new_area" class="form-control" autocomplete="off" placeholder="Isi jika area belum ada">
                        </div>
                        <div class="form-group col-md-3 rincian-new-area-field rincian-hidden">
                            <label for="add_rincian_regional">Regional</label>
                            <input type="text" id="add_rincian_regional" name="inputRegionalBaru" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group col-md-3 rincian-new-area-field rincian-hidden">
                            <label for="add_rincian_pic_area">PIC Area</label>
                            <input type="text" id="add_rincian_pic_area" name="inputPICBaru" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_rincian_month">Bulan</label>
                            <select id="add_rincian_month" name="addfilter_month" class="form-control">
                                <option value="">Pilih Bulan</option>
                                <?php foreach (($filterOptions['months'] ?? []) as $month): ?>
                                    <option value="<?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="add_rincian_week">Week</label>
                            <select id="add_rincian_week" name="addfilter_week" class="form-control">
                                <option value="">Pilih Week</option>
                                <?php foreach (($filterOptions['weeks'] ?? []) as $week): ?>
                                    <option value="<?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Target Invoice</label>
                            <input type="text" class="form-control" name="target_invoice" readonly value="0">
                            <span class="rincian-field-note"><i class="fas fa-lock"></i> Dibaca otomatis dari target.</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Realisasi Invoice Saat Ini</label>
                            <input type="text" class="form-control" name="achiev_invoice" autocomplete="off" value="0">
                            <span class="rincian-field-note"><i class="fas fa-pen"></i> Bisa diedit.</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tambahan Invoice</label>
                            <input type="text" class="form-control" name="tambahan_invoice" autocomplete="off" value="0">
                            <span class="rincian-field-note"><i class="fas fa-pen"></i> Bisa diedit.</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Total Invoice</label>
                            <input type="text" class="form-control" name="total_invoice" autocomplete="off" readonly value="0">
                            <span class="rincian-field-note"><i class="fas fa-calculator"></i> Dihitung otomatis.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="rincian-btn rincian-btn--light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="rincian-btn rincian-btn--primary">
                        <i class="fas fa-save"></i>
                        Simpan Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    window.addEventListener('load', function () {
        if (!window.jQuery || !$.fn.DataTable) {
            return;
        }

        const invoiceRows = <?php echo json_encode($invoiceRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>.map(function (row) {
            return {
                project: String(row.id_bowheer || ''),
                projectName: String(row.nama_bowheer || '-'),
                pic: String(row.pic_user || ''),
                regional: String(row.regional_target || ''),
                area: String(row.area_target || ''),
                picArea: String(row.pic_target || ''),
                month: String(row.month_target || ''),
                week: String(row.week_target || ''),
                target: Number(row.qty_target || 0),
                achieved: Number(row.qty_achiev_target || 0)
            };
        });
        const monthOrder = { OKTOBER: 1, NOVEMBER: 2, DESEMBER: 3, JANUARI: 4, FEBRUARI: 5, MARET: 6, APRIL: 7, MEI: 8, JUNI: 9, JULI: 10, AGUSTUS: 11, SEPTEMBER: 12 };
        const filterOrder = ['project', 'pic', 'regional', 'area', 'month', 'week'];
        const filterSelects = {
            project: $('#rincian_filter_project'),
            pic: $('#rincian_filter_pic'),
            regional: $('#rincian_filter_regional'),
            area: $('#rincian_filter_area'),
            month: $('#rincian_filter_month'),
            week: $('#rincian_filter_week')
        };

        if ($.fn.select2) {
            $('.select2').select2({ width: '100%', theme: 'bootstrap4' });
            $('#add_rincian_project, #add_rincian_area, #add_rincian_month, #add_rincian_week').select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: $('#modalRincianTambahInvoice')
            });
        }

        const table = $('#rincian_invoice_table').DataTable({
            paging: true,
            pageLength: 10,
            searching: true,
            info: true,
            lengthChange: false,
            autoWidth: false,
            responsive: false,
            dom: 'rt<"row align-items-center mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            order: []
        });

        function escapeHtml(value) {
            return String(value || '-').replace(/[&<>"']/g, function (match) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[match];
            });
        }

        function formatMoney(value) {
            return 'RP. ' + Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        function formatNumber(value) {
            return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        function parseMoney(value) {
            return Number(String(value || '').replace(/[^\d-]/g, '')) || 0;
        }

        function formatPercent(target, achieved) {
            target = Number(target || 0);
            achieved = Number(achieved || 0);
            if (target === 0 && achieved > 0) {
                return '100,0 %';
            }
            if (target === 0) {
                return '0,0 %';
            }
            return ((achieved / target) * 100).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' %';
        }

        function getPercent(target, achieved) {
            target = Number(target || 0);
            achieved = Number(achieved || 0);
            if (target === 0 && achieved > 0) {
                return 100;
            }
            if (target === 0) {
                return 0;
            }
            return (achieved / target) * 100;
        }

        function statusHtml(target, achieved) {
            const percent = getPercent(target, achieved);
            if (percent >= 100) return '<span class="rincian-status rincian-status--success">Tercapai</span>';
            if (percent >= 80) return '<span class="rincian-status rincian-status--info">On Track</span>';
            if (percent >= 50) return '<span class="rincian-status rincian-status--warning">Perlu Dorong</span>';
            return '<span class="rincian-status rincian-status--danger">Prioritas</span>';
        }

        function collectFilters() {
            return {
                project: $('#rincian_filter_project').val() || '',
                pic: $('#rincian_filter_pic').val() || '',
                regional: $('#rincian_filter_regional').val() || '',
                area: $('#rincian_filter_area').val() || '',
                month: $('#rincian_filter_month').val() || '',
                week: $('#rincian_filter_week').val() || ''
            };
        }

        function filterRows(filters) {
            return invoiceRows.filter(function (row) {
                return (!filters.project || row.project === filters.project) &&
                    (!filters.pic || row.pic === filters.pic) &&
                    (!filters.regional || row.regional === filters.regional) &&
                    (!filters.area || row.area === filters.area) &&
                    (!filters.month || row.month === filters.month) &&
                    (!filters.week || row.week === filters.week);
            });
        }

        function groupRows(rows) {
            const groups = new Map();
            rows.forEach(function (row) {
                const key = row.project + '|' + row.regional + '|' + row.area;
                if (!groups.has(key)) {
                    groups.set(key, {
                        project: row.project,
                        projectName: row.projectName,
                        pic: row.pic,
                        regional: row.regional,
                        area: row.area,
                        target: 0,
                        achieved: 0
                    });
                }
                const group = groups.get(key);
                group.target += row.target;
                group.achieved += row.achieved;
            });
            return Array.from(groups.values()).map(function (row) {
                row.outstanding = Math.max(row.target - row.achieved, 0);
                return row;
            }).sort(function (a, b) {
                return b.achieved - a.achieved;
            });
        }

        function updateKpi(rows) {
            const totals = rows.reduce(function (carry, row) {
                carry.target += row.target;
                carry.achieved += row.achieved;
                return carry;
            }, { target: 0, achieved: 0 });
            const outstanding = Math.max(totals.target - totals.achieved, 0);
            $('#rincian_kpi_target, #rincian_total_target').text(formatMoney(totals.target));
            $('#rincian_kpi_achieved, #rincian_total_achieved').text(formatMoney(totals.achieved));
            $('#rincian_kpi_outstanding, #rincian_total_outstanding').text(formatMoney(outstanding));
            $('#rincian_kpi_percent, #rincian_total_percent').text(formatPercent(totals.target, totals.achieved));
        }

        function renderBadges(filters) {
            const labels = { project: 'Project', pic: 'PIC', regional: 'Regional', area: 'Area', month: 'Bulan', week: 'Week' };
            const container = $('#rincian_active_filters');
            container.empty();
            const active = filterOrder.filter(function (key) { return Boolean(filters[key]); });
            if (!active.length) {
                container.append('<span class="rincian-active-filter rincian-active-filter--empty"><i class="fas fa-filter"></i> Semua data</span>');
                return;
            }
            active.forEach(function (key) {
                const text = filterSelects[key].find('option:selected').text();
                container.append('<span class="rincian-active-filter"><i class="fas fa-check-circle"></i> ' + labels[key] + ': ' + escapeHtml(text) + '</span>');
            });
        }

        function renderTable(rows) {
            const groups = groupRows(rows);
            table.clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.projectName),
                    escapeHtml(row.pic),
                    escapeHtml(row.regional),
                    escapeHtml(row.area),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    formatPercent(row.target, row.achieved),
                    statusHtml(row.target, row.achieved)
                ];
            })).draw();
            window.setTimeout(function () {
                table.columns.adjust().draw(false);
            }, 80);
        }

        function renderAll(filters) {
            filters = filters || collectFilters();
            const rows = filterRows(filters);
            renderBadges(filters);
            updateKpi(rows);
            renderTable(rows);
        }

        function uniqueOptions(rows, key) {
            const options = new Map();
            rows.forEach(function (row) {
                const value = row[key];
                if (!value) return;
                options.set(value, key === 'project' ? row.projectName : value);
            });
            return Array.from(options.entries()).sort(function (a, b) {
                if (key === 'month') return (monthOrder[a[0]] || 99) - (monthOrder[b[0]] || 99);
                return String(a[1]).localeCompare(String(b[1]));
            });
        }

        function rebuildSelect(key, rows) {
            const labels = { project: 'Semua Project', pic: 'Semua PIC', regional: 'Semua Regional', area: 'Semua Area', month: 'Semua Bulan', week: 'Semua Week' };
            const select = filterSelects[key];
            const current = select.val() || '';
            const options = uniqueOptions(rows, key);
            let hasCurrent = current === '';
            select.empty().append(new Option(labels[key], '', true, current === ''));
            options.forEach(function (option) {
                const selected = option[0] === current;
                hasCurrent = hasCurrent || selected;
                select.append(new Option(option[1], option[0], false, selected));
            });
            if (!hasCurrent) select.val('');
            select.trigger('change.select2');
        }

        function rebuildAllFilterOptions() {
            filterOrder.forEach(function (key) {
                rebuildSelect(key, invoiceRows);
            });
        }

        function rowsByParentKeys(keys) {
            const selected = collectFilters();
            return invoiceRows.filter(function (row) {
                return keys.every(function (key) {
                    return !selected[key] || row[key] === selected[key];
                });
            });
        }

        function rebuildDescendantFilters(changedKey) {
            const startIndex = Math.max(filterOrder.indexOf(changedKey) + 1, 0);
            for (let i = startIndex; i < filterOrder.length; i++) {
                rebuildSelect(filterOrder[i], rowsByParentKeys(filterOrder.slice(0, i)));
            }
        }

        filterOrder.forEach(function (key) {
            filterSelects[key].on('change', function () {
                rebuildDescendantFilters(key);
            });
        });

        $('#rincian_apply_filter').on('click', function () {
            renderAll(collectFilters());
        });

        $('#rincian_reset_filter').on('click', function () {
            $('#rincian_filter_project, #rincian_filter_pic, #rincian_filter_regional, #rincian_filter_area, #rincian_filter_month, #rincian_filter_week').val('').trigger('change.select2');
            rebuildDescendantFilters('project');
            renderAll({});
        });

        function updateAddAreaOptions() {
            const areaSelect = $('#add_rincian_area');
            const areas = uniqueOptions(invoiceRows, 'area');
            areaSelect.empty().append(new Option('Pilih Area', '', true, false));
            areas.forEach(function (option) {
                areaSelect.append(new Option(option[1], option[0]));
            });
            areaSelect.val('').trigger('change.select2');
        }

        function syncAreaMeta() {
            const projectId = String($('#add_rincian_project option:selected').data('id') || '');
            const area = $('#add_rincian_area').val() || '';
            const row = invoiceRows.find(function (item) {
                return item.project === projectId && item.area === area;
            });
            if (row) {
                $('#add_rincian_regional').val(row.regional);
                $('#add_rincian_pic_area').val(row.picArea);
            }
        }

        function setNewAreaMode(enabled) {
            $('.rincian-new-area-field').toggleClass('rincian-hidden', !enabled);
            $('#add_rincian_show_new_area').toggleClass('rincian-hidden', enabled);
            $('#add_rincian_cancel_new_area').toggleClass('rincian-hidden', !enabled);
            $('#add_rincian_area').prop('disabled', enabled);

            if (enabled) {
                $('#add_rincian_area').val('').trigger('change');
                $('#add_rincian_area_value').val('');
                $('#add_rincian_new_area').focus();
            } else {
                $('#add_rincian_new_area, #add_rincian_regional, #add_rincian_pic_area').val('');
                $('#add_rincian_area').prop('disabled', false).trigger('change');
            }
        }

        function loadTargetInvoice() {
            const bowheer = $('#add_rincian_project').val();
            const area = $('#add_rincian_new_area').val().trim() || $('#add_rincian_area').val();
            const month = $('#add_rincian_month').val();
            const week = $('#add_rincian_week').val();
            $('#add_rincian_area_value').val(area);
            if (!bowheer || !area || !month || !week) return;

            $.post("<?= base_url('RincianInvoice/get_target_invoice') ?>", {
                bowheer: bowheer,
                area: area,
                month: month,
                week: week
            }, function (res) {
                const data = typeof res === 'string' ? JSON.parse(res || '{}') : res;
                const target = Number(data.qty_target || 0);
                const achieved = Number(data.qty_achiev_target || 0);
                $('[name="target_invoice"]').val(formatNumber(target));
                $('[name="achiev_invoice"]').val(formatNumber(achieved));
                $('[name="tambahan_invoice"]').val('0');
                $('[name="total_invoice"]').val(formatNumber(achieved));
            });
        }

        function updateTotalInput() {
            const achieved = parseMoney($('[name="achiev_invoice"]').val());
            const tambahan = parseMoney($('[name="tambahan_invoice"]').val());
            $('[name="total_invoice"]').val(formatNumber(achieved + tambahan));
        }

        $('#add_rincian_project').on('change', function () {
            updateAddAreaOptions();
            loadTargetInvoice();
        });
        $('#add_rincian_area').on('change', function () {
            $('#add_rincian_area_value').val($(this).val());
            syncAreaMeta();
            loadTargetInvoice();
        });
        $('#add_rincian_new_area, #add_rincian_month, #add_rincian_week').on('change keyup', loadTargetInvoice);
        $('[name="achiev_invoice"], [name="tambahan_invoice"]').on('keyup change', updateTotalInput);
        $('#add_rincian_show_new_area').on('click', function () {
            setNewAreaMode(true);
        });
        $('#add_rincian_cancel_new_area').on('click', function () {
            setNewAreaMode(false);
            loadTargetInvoice();
        });

        $('#modalRincianTambahInvoice').on('show.bs.modal', function () {
            $('#formRincianTambahInvoice')[0].reset();
            $('#add_rincian_project, #add_rincian_area, #add_rincian_month, #add_rincian_week').val('').trigger('change.select2');
            $('[name="target_invoice"], [name="achiev_invoice"], [name="tambahan_invoice"], [name="total_invoice"]').val('0');
            $('#add_rincian_area_value').val('');
            setNewAreaMode(false);
        });

        $('#formRincianTambahInvoice').on('submit', function (event) {
            event.preventDefault();
            const area = $('#add_rincian_new_area').val().trim() || $('#add_rincian_area').val();
            $('#add_rincian_area_value').val(area);

            if (!$('#add_rincian_project').val() || !area || !$('#add_rincian_month').val() || !$('#add_rincian_week').val()) {
                Swal.fire('Data belum lengkap', 'Project, area, bulan, dan week wajib diisi.', 'warning');
                return;
            }

            $.post("<?= base_url('RincianInvoice/addInvoice') ?>", $(this).serialize(), function (res) {
                const data = typeof res === 'string' ? JSON.parse(res || '{}') : res;
                if (data.status === 'not_found') {
                    $.post("<?= base_url('RincianInvoice/createNewTargetInvoice') ?>", {
                        id_bowheer: data.id_bowheer,
                        area_target: data.area_target,
                        month: data.month,
                        week: data.week,
                        regional: data.regional || $('#add_rincian_regional').val(),
                        pic: data.pic || $('#add_rincian_pic_area').val(),
                        nilai_update: data.nilai_update
                    }, function (createRes) {
                        const createData = typeof createRes === 'string' ? JSON.parse(createRes || '{}') : createRes;
                        if (createData.status === true) {
                            Swal.fire('Berhasil', 'Invoice berhasil disimpan.', 'success').then(function () {
                                window.location.href = "<?= base_url('RincianInvoice/revamp') ?>";
                            });
                        } else {
                            Swal.fire('Gagal', createData.message || 'Gagal menambahkan invoice.', 'error');
                        }
                    });
                    return;
                }

                if (data.status === true) {
                    Swal.fire('Berhasil', 'Invoice berhasil disimpan.', 'success').then(function () {
                        window.location.href = "<?= base_url('RincianInvoice/revamp') ?>";
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Tidak ada data yang diubah.', 'error');
                }
            });
        });

        rebuildAllFilterOptions();
        rebuildDescendantFilters('project');
        renderAll({});
    });
</script>
