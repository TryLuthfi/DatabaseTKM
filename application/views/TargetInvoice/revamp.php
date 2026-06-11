<?php
$overview = $overview ?? [];
$projectRows = $projectRows ?? [];
$picRows = $picRows ?? [];
$regionalRows = $regionalRows ?? [];
$cityRows = $cityRows ?? [];
$periodRows = $periodRows ?? [];
$filterOptions = $filterOptions ?? [];

if (!function_exists('target_invoice_money')) {
    function target_invoice_money($value)
    {
        return 'RP. ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('target_invoice_number')) {
    function target_invoice_number($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('target_invoice_percent')) {
    function target_invoice_percent($value, $decimals = 1)
    {
        return number_format((float) $value, $decimals, ',', '.') . ' %';
    }
}

if (!function_exists('target_invoice_status')) {
    function target_invoice_status($percent)
    {
        $percent = (float) $percent;
        if ($percent >= 100) {
            return ['class' => 'invoice-status--success', 'label' => 'Tercapai'];
        }
        if ($percent >= 80) {
            return ['class' => 'invoice-status--info', 'label' => 'On Track'];
        }
        if ($percent >= 50) {
            return ['class' => 'invoice-status--warning', 'label' => 'Perlu Dorong'];
        }
        return ['class' => 'invoice-status--danger', 'label' => 'Prioritas'];
    }
}

$totalTarget = (float) ($overview['total_target'] ?? 0);
$totalAchieved = (float) ($overview['total_achieved'] ?? 0);
$outstanding = (float) ($overview['outstanding'] ?? 0);
$achievementPercent = (float) ($overview['persen_achieved'] ?? 0);
$achievementStatus = $achievementPercent >= 100 ? 'Naik' : 'Belum tercapai';

$topOutstandingRows = $projectRows;
usort($topOutstandingRows, function ($a, $b) {
    return (float) ($b['deviasi'] ?? 0) <=> (float) ($a['deviasi'] ?? 0);
});
$topOutstandingRows = array_slice($topOutstandingRows, 0, 5);

$topAchievementRows = $projectRows;
usort($topAchievementRows, function ($a, $b) {
    return (float) ($b['total_achiev'] ?? 0) <=> (float) ($a['total_achiev'] ?? 0);
});
$topAchievementRows = array_slice($topAchievementRows, 0, 5);
?>

<style>
    .target-invoice-revamp {
        --invoice-ink: #0f172a;
        --invoice-muted: #64748b;
        --invoice-line: rgba(148, 163, 184, 0.22);
        --invoice-surface: rgba(255, 255, 255, 0.96);
        --invoice-soft: rgba(248, 250, 252, 0.94);
        --invoice-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        --invoice-blue: #2563eb;
        --invoice-green: #16a34a;
        --invoice-red: #ef4444;
        --invoice-slate: #334155;
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }

    .target-invoice-revamp .content-header {
        padding-bottom: 0;
    }

    .invoice-shell {
        padding: 1rem;
    }

    .invoice-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.18);
        color: #f8fafc;
    }

    .invoice-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.2rem;
        padding: 1.25rem;
    }

    .invoice-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .invoice-hero h1 {
        margin: 0.9rem 0 0.55rem;
        color: #fff;
        font-size: 1.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .invoice-hero p {
        max-width: 48rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.9);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .invoice-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.05rem;
    }

    .invoice-hero__stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        align-content: start;
    }

    .invoice-hero-stat {
        min-height: 90px;
        padding: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.11);
        backdrop-filter: blur(8px);
    }

    .invoice-hero-stat__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .invoice-hero-stat__value {
        display: block;
        margin-top: 0.3rem;
        color: #fff;
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1;
    }

    .invoice-hero-stat__hint {
        display: block;
        margin-top: 0.5rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .invoice-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .invoice-kpi-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1.05rem;
        min-height: 104px;
        padding: 1.15rem 1.2rem;
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-top: 5px solid var(--invoice-blue);
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.92));
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .invoice-kpi-card--green {
        border-color: rgba(187, 247, 208, 0.95);
        border-top-color: var(--invoice-green);
    }

    .invoice-kpi-card--red {
        border-color: rgba(254, 202, 202, 0.95);
        border-top-color: var(--invoice-red);
    }

    .invoice-kpi-card__icon {
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

    .invoice-kpi-card--green .invoice-kpi-card__icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 12px 24px rgba(22, 163, 74, 0.22);
    }

    .invoice-kpi-card--red .invoice-kpi-card__icon {
        background: linear-gradient(135deg, #f87171, #dc2626);
        box-shadow: 0 12px 24px rgba(220, 38, 38, 0.18);
    }

    .invoice-kpi-card__label {
        display: block;
        margin-bottom: 0.55rem;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .invoice-kpi-card__value {
        display: block;
        color: var(--invoice-ink);
        font-size: 1.22rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .invoice-kpi-card__value--green {
        color: #059669;
        font-size: 1.32rem;
    }

    .invoice-kpi-card__value--red {
        color: #dc2626;
    }

    .invoice-kpi-card__trend {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-left: 0.45rem;
        color: #059669;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .invoice-panel,
    .invoice-table-shell {
        margin-top: 1rem;
        border: 1px solid var(--invoice-line);
        border-radius: 12px;
        background: var(--invoice-surface);
        box-shadow: var(--invoice-shadow);
        overflow: hidden;
    }

    .invoice-shell > .invoice-panel:first-child {
        margin-top: 0;
    }

    .invoice-panel + .invoice-kpi-grid {
        margin-top: 1rem;
    }

    .invoice-hero + .invoice-panel {
        margin-top: 1rem;
    }

    .invoice-panel__head,
    .invoice-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.05rem 1.15rem 0;
    }

    .invoice-table-title-block {
        width: 100%;
        text-align: left;
    }

    .invoice-panel__title {
        margin: 0;
        color: var(--invoice-ink);
        font-size: 1rem;
        font-weight: 900;
    }

    .invoice-panel__subtitle {
        margin: 0.25rem 0 0;
        color: var(--invoice-muted);
        font-size: 0.88rem;
    }

    .invoice-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        margin-bottom: 0.45rem;
        padding: 0.33rem 0.68rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .invoice-active-filters {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.55rem;
        width: 100%;
        max-width: 100%;
        margin-top: 0.85rem;
    }

    .invoice-active-filter {
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

    .invoice-active-filter--empty {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
    }

    .invoice-table-controls {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 170px;
        gap: 0.75rem;
        width: 100%;
        margin-top: 0.9rem;
    }

    .invoice-table-control {
        position: relative;
    }

    .invoice-table-control .invoice-control-icon {
        position: absolute;
        top: 50%;
        left: 0.85rem;
        z-index: 2;
        color: #64748b;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .invoice-table-control .form-control,
    .invoice-table-control .custom-select {
        padding-left: 2.35rem;
    }

    .invoice-panel__body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .invoice-filter-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .invoice-filter-grid .form-group {
        margin-bottom: 0;
    }

    .invoice-filter-grid label {
        margin-bottom: 0.42rem;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .target-invoice-revamp .form-control,
    .target-invoice-revamp .custom-select {
        min-height: 42px;
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.34);
        background-color: rgba(255, 255, 255, 0.94);
        color: #0f172a;
        font-size: 0.9rem;
        box-shadow: none;
    }

    .target-invoice-revamp .form-control:focus,
    .target-invoice-revamp .custom-select:focus {
        border-color: rgba(37, 99, 235, 0.46);
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12) !important;
    }

    .target-invoice-revamp .select2-container--default .select2-selection--single,
    .target-invoice-revamp .select2-container--bootstrap4 .select2-selection--single {
        min-height: 42px;
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.34);
        background-color: rgba(255, 255, 255, 0.94);
        display: flex;
        align-items: center;
    }

    .target-invoice-revamp .select2-container--default .select2-selection--single .select2-selection__rendered,
    .target-invoice-revamp .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #64748b;
        line-height: 42px;
        padding-left: 0.85rem;
    }

    .target-invoice-revamp .select2-container--default .select2-selection--single .select2-selection__arrow,
    .target-invoice-revamp .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 0.45rem;
    }

    .invoice-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 0.85rem;
    }

    .invoice-btn {
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

    .invoice-btn:hover {
        transform: translateY(-1px);
    }

    .invoice-btn--primary {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .invoice-btn--light {
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.24);
    }

    .invoice-insight-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .invoice-insight {
        min-height: 106px;
        padding: 1rem;
        border: 1px solid var(--invoice-line);
        border-radius: 8px;
        background: var(--invoice-surface);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
    }

    .invoice-insight__label {
        display: block;
        color: var(--invoice-muted);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .invoice-insight__value {
        display: block;
        margin-top: 0.55rem;
        color: var(--invoice-ink);
        font-size: 1.55rem;
        font-weight: 900;
        line-height: 1;
    }

    .invoice-insight__hint {
        display: block;
        margin-top: 0.55rem;
        color: #64748b;
        font-size: 0.85rem;
    }

    .invoice-grid-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .invoice-mini-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .invoice-mini-list li {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.75rem;
        padding: 0.72rem 0;
        border-bottom: 1px solid rgba(226, 232, 240, 0.85);
    }

    .invoice-mini-list li:last-child {
        border-bottom: 0;
    }

    .invoice-mini-list strong {
        display: block;
        color: var(--invoice-ink);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .invoice-mini-list span {
        display: block;
        margin-top: 0.2rem;
        color: var(--invoice-muted);
        font-size: 0.82rem;
    }

    .invoice-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin: 1rem 1.1rem 0;
        padding: 0.35rem;
        border-radius: 8px;
        background: #f1f5f9;
    }

    .invoice-tabs .nav-link {
        border: 0;
        border-radius: 7px;
        color: #475569;
        font-weight: 800;
    }

    .invoice-tabs .nav-link.active {
        color: #fff;
        background: #1d4ed8;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .invoice-table-wrap {
        padding: 1rem 1.1rem 1.1rem;
    }

    .target-invoice-revamp .dataTables_wrapper {
        width: 100%;
    }

    .target-invoice-revamp .dataTables_wrapper .row {
        margin-left: 0;
        margin-right: 0;
    }

    .target-invoice-revamp .dataTables_wrapper .row > [class*="col-"] {
        padding-left: 0;
        padding-right: 0;
    }

    .target-invoice-revamp .dataTables_wrapper .dataTables_info {
        color: #64748b;
        font-size: 0.84rem;
        padding-top: 0.75rem;
    }

    .target-invoice-revamp .dataTables_wrapper .dataTables_paginate {
        padding-top: 0.65rem;
    }

    .invoice-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .invoice-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
        font-size: 0.74rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 0.76rem 0.68rem;
        vertical-align: middle;
        white-space: nowrap;
        border-top: 1px solid rgba(226, 232, 240, 0.72);
    }

    .invoice-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.72);
    }

    .invoice-table tfoot th {
        background: #f8fafc;
        color: var(--invoice-ink);
        font-weight: 900;
    }

    .invoice-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.62rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .invoice-status--success {
        background: rgba(22, 163, 74, 0.1);
        color: #15803d;
    }

    .invoice-status--info {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
    }

    .invoice-status--warning {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .invoice-status--danger {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
    }

    .invoice-action-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .invoice-action-btn:hover {
        color: #fff;
        background: #1d4ed8;
    }

    .invoice-progress {
        min-width: 120px;
    }

    .invoice-progress__track {
        height: 7px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .invoice-progress__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #22c55e);
    }

    .invoice-progress__text {
        display: block;
        margin-top: 0.3rem;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .invoice-modal .modal-content {
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 34px 80px rgba(15, 23, 42, 0.26);
    }

    .invoice-modal .modal-header {
        color: #fff;
        border-bottom: 0;
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
    }

    .invoice-modal .modal-title {
        font-weight: 900;
    }

    .invoice-modal-note {
        padding: 0.85rem 1rem;
        border-radius: 12px;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
        font-size: 0.86rem;
    }

    .invoice-modal label {
        margin-bottom: 0.42rem;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .invoice-modal .form-control:not([readonly]):not(:disabled),
    .invoice-modal select.form-control:not(:disabled) + .select2-container .select2-selection {
        border-color: rgba(37, 99, 235, 0.42);
        background: #ffffff;
        box-shadow: inset 4px 0 0 rgba(37, 99, 235, 0.52);
    }

    .invoice-modal .form-control[readonly],
    .invoice-modal .form-control:disabled,
    .invoice-modal select.form-control:disabled + .select2-container .select2-selection {
        border-color: rgba(148, 163, 184, 0.24);
        background: #f1f5f9;
        color: #64748b;
        box-shadow: inset 4px 0 0 rgba(100, 116, 139, 0.28);
    }

    .invoice-field-note {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .invoice-hidden {
        display: none !important;
    }

    @media (max-width: 1199.98px) {
        .invoice-kpi-grid,
        .invoice-filter-grid,
        .invoice-insight-grid,
        .invoice-grid-two,
        .invoice-hero__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .invoice-shell {
            padding: 0.75rem;
        }

        .invoice-kpi-grid,
        .invoice-filter-grid,
        .invoice-insight-grid,
        .invoice-grid-two,
        .invoice-hero__grid,
        .invoice-hero__stats {
            grid-template-columns: 1fr;
        }

        .invoice-panel__head,
        .invoice-table-head,
        .invoice-table-controls,
        .invoice-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .invoice-table-controls {
            display: grid;
            grid-template-columns: 1fr;
        }

        .invoice-active-filters {
            justify-content: flex-start;
            max-width: 100%;
        }

        .invoice-kpi-card__value {
            font-size: 1rem;
        }
    }
</style>

<div class="content-wrapper target-invoice-revamp">
    <div class="content-header">
        <div class="container-fluid invoice-shell">
            <section class="invoice-hero">
                <div class="invoice-hero__grid">
                    <div>
                        <span class="invoice-hero__eyebrow">
                            <i class="fas fa-file-invoice-dollar"></i>
                            Target Invoice Intelligence
                        </span>
                        <h1>Dashboard monitoring target invoice PT. TKM</h1>
                        <p>
                            Dashboard ini memantau pencapaian invoice terhadap target project, PIC, regional, area, dan periode.
                            Gunakan report ini untuk melihat outstanding terbesar, project yang sudah tercapai, dan area yang perlu
                            diprioritaskan.
                        </p>
                        <div class="invoice-hero__actions">
                            <button type="button" class="invoice-btn invoice-btn--primary" data-toggle="modal" data-target="#modalTargetTambahInvoice">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Invoice
                            </button>
                            <a href="<?= base_url('TargetInvoice') ?>" class="invoice-btn invoice-btn--light">
                                <i class="fas fa-table"></i>
                                Halaman Lama
                            </a>
                        </div>
                    </div>

                    <div class="invoice-hero__stats">
                        <div class="invoice-hero-stat">
                            <span class="invoice-hero-stat__label">Project Aktif</span>
                            <span class="invoice-hero-stat__value"><?= target_invoice_number($overview['total_project'] ?? 0) ?></span>
                            <span class="invoice-hero-stat__hint">Project dengan target invoice aktif.</span>
                        </div>
                        <div class="invoice-hero-stat">
                            <span class="invoice-hero-stat__label">Regional</span>
                            <span class="invoice-hero-stat__value"><?= target_invoice_number($overview['total_regional'] ?? 0) ?></span>
                            <span class="invoice-hero-stat__hint">Wilayah yang masuk pemantauan.</span>
                        </div>
                        <div class="invoice-hero-stat">
                            <span class="invoice-hero-stat__label">Area / Kota</span>
                            <span class="invoice-hero-stat__value"><?= target_invoice_number($overview['total_area'] ?? 0) ?></span>
                            <span class="invoice-hero-stat__hint">Area invoice yang memiliki target.</span>
                        </div>
                        <div class="invoice-hero-stat">
                            <span class="invoice-hero-stat__label">PIC Area</span>
                            <span class="invoice-hero-stat__value"><?= target_invoice_number($overview['total_pic_area'] ?? 0) ?></span>
                            <span class="invoice-hero-stat__hint">PIC area yang terhubung ke target.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="invoice-panel">
                <div class="invoice-panel__head">
                    <div>
                        <span class="invoice-chip"><i class="fas fa-sliders-h"></i> Kontrol Data</span>
                        <h1 class="invoice-panel__title">Filter Data</h1>
                        <p class="invoice-panel__subtitle">Pilih project, PIC, regional, area, bulan, dan week untuk membaca report sesuai kebutuhan.</p>
                    </div>
                </div>
                <div class="invoice-panel__body">
                    <div class="invoice-filter-grid">
                        <div class="form-group">
                            <label for="invoice_filter_project">Project</label>
                            <select id="invoice_filter_project" class="form-control select2" data-placeholder="Semua project">
                                <option value="">Semua Project</option>
                                <?php foreach (($filterOptions['projects'] ?? []) as $project): ?>
                                    <option value="<?= (int) ($project['id_bowheer'] ?? 0) ?>"><?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="invoice_filter_pic">PIC</label>
                            <select id="invoice_filter_pic" class="form-control select2" data-placeholder="Semua PIC">
                                <option value="">Semua PIC</option>
                                <?php foreach (($filterOptions['pics'] ?? []) as $pic): ?>
                                    <option value="<?= htmlspecialchars($pic['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pic['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="invoice_filter_regional">Regional</label>
                            <select id="invoice_filter_regional" class="form-control select2" data-placeholder="Semua regional">
                                <option value="">Semua Regional</option>
                                <?php foreach (($filterOptions['regionals'] ?? []) as $regional): ?>
                                    <option value="<?= htmlspecialchars($regional['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($regional['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="invoice_filter_area">Area</label>
                            <select id="invoice_filter_area" class="form-control select2" data-placeholder="Semua area">
                                <option value="">Semua Area</option>
                                <?php foreach (($filterOptions['areas'] ?? []) as $area): ?>
                                    <option value="<?= htmlspecialchars($area['area_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($area['area_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="invoice_filter_month">Bulan</label>
                            <select id="invoice_filter_month" class="form-control select2" data-placeholder="Semua bulan">
                                <option value="">Semua Bulan</option>
                                <?php foreach (($filterOptions['months'] ?? []) as $month): ?>
                                    <option value="<?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="invoice_filter_week">Week</label>
                            <select id="invoice_filter_week" class="form-control select2" data-placeholder="Semua week">
                                <option value="">Semua Week</option>
                                <?php foreach (($filterOptions['weeks'] ?? []) as $week): ?>
                                    <option value="<?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="invoice-actions">
                        <button type="button" class="invoice-btn invoice-btn--light" id="invoice_reset_filter">
                            <i class="fas fa-undo-alt"></i>
                            Reset
                        </button>
                        <button type="button" class="invoice-btn invoice-btn--primary" id="invoice_apply_filter">
                            <i class="fas fa-search"></i>
                            Terapkan
                        </button>
                    </div>
                </div>
            </section>

            <section class="invoice-kpi-grid" aria-label="Ringkasan Target Invoice">
                <article class="invoice-kpi-card">
                    <span class="invoice-kpi-card__icon"><i class="fas fa-bullseye"></i></span>
                    <div>
                        <span class="invoice-kpi-card__label">Target Invoice</span>
                        <span class="invoice-kpi-card__value" id="invoice_kpi_target"><?= target_invoice_money($totalTarget) ?></span>
                    </div>
                </article>
                <article class="invoice-kpi-card invoice-kpi-card--green">
                    <span class="invoice-kpi-card__icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <span class="invoice-kpi-card__label">Achieved Invoice</span>
                        <span class="invoice-kpi-card__value invoice-kpi-card__value--green" id="invoice_kpi_achieved"><?= target_invoice_money($totalAchieved) ?></span>
                    </div>
                </article>
                <article class="invoice-kpi-card invoice-kpi-card--red">
                    <span class="invoice-kpi-card__icon"><i class="fas fa-hourglass-half"></i></span>
                    <div>
                        <span class="invoice-kpi-card__label">Sisa Invoice</span>
                        <span class="invoice-kpi-card__value invoice-kpi-card__value--red" id="invoice_kpi_outstanding"><?= target_invoice_money($outstanding) ?></span>
                    </div>
                </article>
                <article class="invoice-kpi-card">
                    <span class="invoice-kpi-card__icon"><i class="fas fa-percentage"></i></span>
                    <div>
                        <span class="invoice-kpi-card__label">Persentase Invoice</span>
                        <span class="invoice-kpi-card__value invoice-kpi-card__value--green" id="invoice_kpi_percent">
                            <?= target_invoice_percent($achievementPercent) ?>
                            <span class="invoice-kpi-card__trend"><i class="fas fa-arrow-up"></i><?= $achievementStatus ?></span>
                        </span>
                    </div>
                </article>
            </section>

            <section class="invoice-grid-two">
                <div class="invoice-panel">
                    <div class="invoice-panel__head">
                        <div>
                            <span class="invoice-chip"><i class="fas fa-exclamation-circle"></i> Prioritas</span>
                            <h2 class="invoice-panel__title">Top Outstanding Project</h2>
                            <p class="invoice-panel__subtitle">Project dengan sisa invoice terbesar.</p>
                        </div>
                    </div>
                    <div class="invoice-panel__body">
                        <ul class="invoice-mini-list" id="invoice_top_outstanding_list">
                            <?php foreach ($topOutstandingRows as $row): ?>
                                <li>
                                    <div>
                                        <strong><?= htmlspecialchars($row['nama_bowheer'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars($row['pic_user'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <strong><?= target_invoice_money($row['deviasi'] ?? 0) ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="invoice-panel">
                    <div class="invoice-panel__head">
                        <div>
                            <span class="invoice-chip"><i class="fas fa-chart-line"></i> Nominal Tertinggi</span>
                            <h2 class="invoice-panel__title">Top Invoice Project</h2>
                            <p class="invoice-panel__subtitle">Project dengan achieved invoice terbesar.</p>
                        </div>
                    </div>
                    <div class="invoice-panel__body">
                        <ul class="invoice-mini-list" id="invoice_top_project_list">
                            <?php foreach ($topAchievementRows as $row): ?>
                                <li>
                                    <div>
                                        <strong><?= htmlspecialchars($row['nama_bowheer'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= target_invoice_percent($row['persen_achiev'] ?? 0) ?></span>
                                    </div>
                                    <strong><?= target_invoice_money($row['total_achiev'] ?? 0) ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="invoice-table-shell" id="invoice_report_detail">
                <div class="invoice-table-head">
                    <div class="invoice-table-title-block">
                        <span class="invoice-chip"><i class="fas fa-layer-group"></i> Report Detail</span>
                        <h2 class="invoice-panel__title">Breakdown Target Invoice</h2>
                        <p class="invoice-panel__subtitle">Gunakan tab untuk melihat sudut pandang report yang berbeda.</p>
                        <div class="invoice-active-filters" id="invoice_active_filters">
                            <span class="invoice-active-filter invoice-active-filter--empty">
                                <i class="fas fa-filter"></i>
                                Semua data
                            </span>
                        </div>
                        <div class="invoice-table-controls">
                            <div class="invoice-table-control">
                                <i class="fas fa-search invoice-control-icon"></i>
                                <input type="search" class="form-control" id="invoice_table_search" placeholder="Cari breakdown invoice">
                            </div>
                            <div class="invoice-table-control">
                                <i class="fas fa-list-ol invoice-control-icon"></i>
                                <select class="custom-select" id="invoice_table_limit">
                                    <option value="10">10 row</option>
                                    <option value="25">25 row</option>
                                    <option value="50">50 row</option>
                                    <option value="100">100 row</option>
                                    <option value="-1">Semua row</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav invoice-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" href="#invoice_tab_project" role="tab">Project</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#invoice_tab_pic" role="tab">PIC</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#invoice_tab_regional" role="tab">Regional</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#invoice_tab_city" role="tab">Kota / Area</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#invoice_tab_period" role="tab">Periode</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="invoice_tab_project" role="tabpanel">
                        <div class="invoice-table-wrap table-responsive">
                            <table class="table invoice-table" id="invoice_project_table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Project</th>
                                        <th>PIC</th>
                                        <th>Target</th>
                                        <th>Achieved</th>
                                        <th>Outstanding</th>
                                        <th>Achieved %</th>
                                        <th>Status</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projectRows as $index => $row): ?>
                                        <?php
                                        $percent = (float) ($row['persen_achiev'] ?? 0);
                                        $status = target_invoice_status($percent);
                                        ?>
                                        <tr data-project="<?= (int) ($row['id_bowheer'] ?? 0) ?>" data-pic="<?= htmlspecialchars($row['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-target="<?= (float) ($row['total_target'] ?? 0) ?>" data-achieved="<?= (float) ($row['total_achiev'] ?? 0) ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['nama_bowheer'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($row['pic_user'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= target_invoice_money($row['total_target'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['total_achiev'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['deviasi'] ?? 0) ?></td>
                                            <td>
                                                <div class="invoice-progress">
                                                    <div class="invoice-progress__track">
                                                        <div class="invoice-progress__bar" style="width: <?= min($percent, 100) ?>%"></div>
                                                    </div>
                                                    <span class="invoice-progress__text"><?= target_invoice_percent($percent) ?></span>
                                                </div>
                                            </td>
                                            <td><span class="invoice-status <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                            <td>
                                                <a href="<?= site_url('TargetInvoice/detailBowheer/' . (int) ($row['id_bowheer'] ?? 0)) ?>" class="invoice-action-btn">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th id="invoice_project_total_target">RP. 0</th>
                                        <th id="invoice_project_total_achieved">RP. 0</th>
                                        <th id="invoice_project_total_outstanding">RP. 0</th>
                                        <th id="invoice_project_total_percent">0,0 %</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="invoice_tab_pic" role="tabpanel">
                        <div class="invoice-table-wrap table-responsive">
                            <table class="table invoice-table" id="invoice_pic_table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>PIC</th>
                                        <th>Target</th>
                                        <th>Achieved</th>
                                        <th>Outstanding</th>
                                        <th>Achieved %</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($picRows as $index => $row): ?>
                                        <?php
                                        $percent = (float) ($row['persen_achiev'] ?? 0);
                                        $status = target_invoice_status($percent);
                                        ?>
                                        <tr data-pic="<?= htmlspecialchars($row['pic_user'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-target="<?= (float) ($row['total_target'] ?? 0) ?>" data-achieved="<?= (float) ($row['total_achiev'] ?? 0) ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['pic_user'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= target_invoice_money($row['total_target'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['total_achiev'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['deviasi'] ?? 0) ?></td>
                                            <td><?= target_invoice_percent($percent) ?></td>
                                            <td><span class="invoice-status <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th id="invoice_pic_total_target">RP. 0</th>
                                        <th id="invoice_pic_total_achieved">RP. 0</th>
                                        <th id="invoice_pic_total_outstanding">RP. 0</th>
                                        <th id="invoice_pic_total_percent">0,0 %</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="invoice_tab_regional" role="tabpanel">
                        <div class="invoice-table-wrap table-responsive">
                            <table class="table invoice-table" id="invoice_regional_table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Target</th>
                                        <th>Achieved</th>
                                        <th>Outstanding</th>
                                        <th>Achieved %</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($regionalRows as $index => $row): ?>
                                        <?php
                                        $percent = (float) ($row['persen_achiev'] ?? 0);
                                        $status = target_invoice_status($percent);
                                        ?>
                                        <tr data-regional="<?= htmlspecialchars($row['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-target="<?= (float) ($row['total_target'] ?? 0) ?>" data-achieved="<?= (float) ($row['total_achiev'] ?? 0) ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['regional_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= target_invoice_money($row['total_target'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['total_achiev'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['deviasi'] ?? 0) ?></td>
                                            <td><?= target_invoice_percent($percent) ?></td>
                                            <td><span class="invoice-status <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th id="invoice_regional_total_target">RP. 0</th>
                                        <th id="invoice_regional_total_achieved">RP. 0</th>
                                        <th id="invoice_regional_total_outstanding">RP. 0</th>
                                        <th id="invoice_regional_total_percent">0,0 %</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="invoice_tab_city" role="tabpanel">
                        <div class="invoice-table-wrap table-responsive">
                            <table class="table invoice-table" id="invoice_city_table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Kota / Area</th>
                                        <th>PIC Area</th>
                                        <th>Target</th>
                                        <th>Achieved</th>
                                        <th>Outstanding</th>
                                        <th>Achieved %</th>
                                        <th>Status</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cityRows as $index => $row): ?>
                                        <?php
                                        $percent = (float) ($row['persen_achiev'] ?? 0);
                                        $status = target_invoice_status($percent);
                                        ?>
                                        <tr data-regional="<?= htmlspecialchars($row['regional_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-area="<?= htmlspecialchars($row['area_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-pic="<?= htmlspecialchars($row['pic_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-target="<?= (float) ($row['total_target'] ?? 0) ?>" data-achieved="<?= (float) ($row['total_achiev'] ?? 0) ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['regional_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($row['area_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($row['pic_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= target_invoice_money($row['total_target'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['total_achiev'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['deviasi'] ?? 0) ?></td>
                                            <td><?= target_invoice_percent($percent) ?></td>
                                            <td><span class="invoice-status <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                            <td>
                                                <a href="<?= site_url('TargetInvoice/detailKota/' . urlencode($row['area_target'] ?? '')) ?>" class="invoice-action-btn">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4">Total</th>
                                        <th id="invoice_city_total_target">RP. 0</th>
                                        <th id="invoice_city_total_achieved">RP. 0</th>
                                        <th id="invoice_city_total_outstanding">RP. 0</th>
                                        <th id="invoice_city_total_percent">0,0 %</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="invoice_tab_period" role="tabpanel">
                        <div class="invoice-table-wrap table-responsive">
                            <table class="table invoice-table" id="invoice_period_table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bulan</th>
                                        <th>Week</th>
                                        <th>Target</th>
                                        <th>Achieved</th>
                                        <th>Outstanding</th>
                                        <th>Achieved %</th>
                                        <th>Project</th>
                                        <th>Area</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($periodRows as $index => $row): ?>
                                        <?php
                                        $percent = (float) ($row['persen_achieved'] ?? 0);
                                        $status = target_invoice_status($percent);
                                        ?>
                                        <tr data-month="<?= htmlspecialchars($row['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-week="<?= htmlspecialchars($row['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-target="<?= (float) ($row['total_target'] ?? 0) ?>" data-achieved="<?= (float) ($row['total_achieved'] ?? 0) ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['month_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($row['week_target'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= target_invoice_money($row['total_target'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['total_achieved'] ?? 0) ?></td>
                                            <td><?= target_invoice_money($row['outstanding'] ?? 0) ?></td>
                                            <td><?= target_invoice_percent($percent) ?></td>
                                            <td><?= target_invoice_number($row['total_project'] ?? 0) ?></td>
                                            <td><?= target_invoice_number($row['total_area'] ?? 0) ?></td>
                                            <td><span class="invoice-status <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th id="invoice_period_total_target">RP. 0</th>
                                        <th id="invoice_period_total_achieved">RP. 0</th>
                                        <th id="invoice_period_total_outstanding">RP. 0</th>
                                        <th id="invoice_period_total_percent">0,0 %</th>
                                        <th id="invoice_period_total_project">0</th>
                                        <th id="invoice_period_total_area">0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<form id="formTargetTambahInvoice" action="<?= site_url('RincianInvoice/addInvoice') ?>" method="post">
    <div class="modal fade invoice-modal" id="modalTargetTambahInvoice" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Tambah Invoice</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="invoice-modal-note mb-3">
                        Tambah atau perbarui realisasi invoice langsung dari dashboard target. Target dan realisasi saat ini akan dibaca otomatis.
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="target_add_project">Project / Bowheer</label>
                            <select id="target_add_project" name="addfilter_bowheer" class="form-control">
                                <option value="">Pilih Project</option>
                                <?php foreach (($filterOptions['projects'] ?? []) as $project): ?>
                                    <option value="<?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-id="<?= (int) ($project['id_bowheer'] ?? 0) ?>"><?= htmlspecialchars($project['nama_bowheer'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="target_add_area">Area Existing</label>
                            <select id="target_add_area" class="form-control">
                                <option value="">Pilih Area</option>
                            </select>
                            <input type="hidden" name="addfilter_area" id="target_add_area_value">
                            <button type="button" class="btn btn-link px-0 mt-2" id="target_add_show_new_area">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Area Baru
                            </button>
                            <button type="button" class="btn btn-link text-danger px-0 mt-2 invoice-hidden" id="target_add_cancel_new_area">
                                <i class="fas fa-times-circle"></i>
                                Batal Area Baru
                            </button>
                        </div>
                        <div class="form-group col-md-6 target-new-area-field invoice-hidden">
                            <label for="target_add_new_area">Area Baru</label>
                            <input type="text" id="target_add_new_area" class="form-control" autocomplete="off" placeholder="Isi jika area belum ada">
                        </div>
                        <div class="form-group col-md-3 target-new-area-field invoice-hidden">
                            <label for="target_add_regional">Regional</label>
                            <input type="text" id="target_add_regional" name="inputRegionalBaru" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group col-md-3 target-new-area-field invoice-hidden">
                            <label for="target_add_pic_area">PIC Area</label>
                            <input type="text" id="target_add_pic_area" name="inputPICBaru" class="form-control" autocomplete="off">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="target_add_month">Bulan</label>
                            <select id="target_add_month" name="addfilter_month" class="form-control">
                                <option value="">Pilih Bulan</option>
                                <?php foreach (($filterOptions['months'] ?? []) as $month): ?>
                                    <option value="<?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($month['month_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="target_add_week">Week</label>
                            <select id="target_add_week" name="addfilter_week" class="form-control">
                                <option value="">Pilih Week</option>
                                <?php foreach (($filterOptions['weeks'] ?? []) as $week): ?>
                                    <option value="<?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($week['week_target'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Target Invoice</label>
                            <input type="text" class="form-control" name="target_invoice" readonly value="0">
                            <span class="invoice-field-note"><i class="fas fa-lock"></i> Dibaca otomatis dari target.</span>
                        </div>
                        <div class="form-group col-md-6" id="target_add_achieved_group">
                            <label>Realisasi Invoice Saat Ini</label>
                            <input type="text" class="form-control" name="achiev_invoice" autocomplete="off" value="0">
                            <span class="invoice-field-note"><i class="fas fa-pen"></i> Bisa diedit.</span>
                        </div>
                        <div class="form-group col-md-6 invoice-hidden" id="target_add_extra_group">
                            <label>Tambahan Invoice</label>
                            <input type="text" class="form-control" name="tambahan_invoice" autocomplete="off" value="0">
                            <span class="invoice-field-note"><i class="fas fa-pen"></i> Bisa diedit.</span>
                        </div>
                        <div class="form-group col-md-6 invoice-hidden" id="target_add_total_group">
                            <label>Total Invoice</label>
                            <input type="text" class="form-control" name="total_invoice" autocomplete="off" readonly value="0">
                            <span class="invoice-field-note"><i class="fas fa-calculator"></i> Dihitung otomatis.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="invoice-btn invoice-btn--light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="invoice-btn invoice-btn--primary">
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

        const invoiceRows = <?php echo json_encode($invoiceRows ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>.map(function (row) {
            return {
                id: String(row.id_target_invoice || ''),
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

        const tableIds = [
            '#invoice_project_table',
            '#invoice_pic_table',
            '#invoice_regional_table',
            '#invoice_city_table',
            '#invoice_period_table'
        ];
        const tables = {};
        const monthOrder = {
            OKTOBER: 1,
            NOVEMBER: 2,
            DESEMBER: 3,
            JANUARI: 4,
            FEBRUARI: 5,
            MARET: 6,
            APRIL: 7,
            MEI: 8,
            JUNI: 9,
            JULI: 10,
            AGUSTUS: 11,
            SEPTEMBER: 12
        };
        const filterOrder = ['project', 'pic', 'regional', 'area', 'month', 'week'];
        const filterSelects = {
            project: $('#invoice_filter_project'),
            pic: $('#invoice_filter_pic'),
            regional: $('#invoice_filter_regional'),
            area: $('#invoice_filter_area'),
            month: $('#invoice_filter_month'),
            week: $('#invoice_filter_week')
        };

        tableIds.forEach(function (id) {
            tables[id] = $(id).DataTable({
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
        });

        const tableSearchInput = $('#invoice_table_search');
        const tableLimitSelect = $('#invoice_table_limit');
        let tableSearchTimer = null;

        function applyTableSearch(searchValue) {
            tableIds.forEach(function (id) {
                if (tables[id]) {
                    tables[id].search(searchValue || '').draw();
                }
            });
        }

        tableSearchInput.on('input', function () {
            const searchValue = this.value;
            window.clearTimeout(tableSearchTimer);
            tableSearchTimer = window.setTimeout(function () {
                applyTableSearch(searchValue);
                adjustActiveTable();
            }, 180);
        });

        tableLimitSelect.on('change', function () {
            const pageLength = parseInt(this.value, 10);
            tableIds.forEach(function (id) {
                if (tables[id]) {
                    tables[id].page.len(pageLength).draw(false);
                }
            });
            adjustActiveTable();
        });

        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
            $('#target_add_project, #target_add_area, #target_add_month, #target_add_week').select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: $('#modalTargetTambahInvoice')
            });
        }

        function escapeHtml(value) {
            return String(value || '-').replace(/[&<>"']/g, function (match) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[match];
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

        function formatPercent(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 1,
                minimumFractionDigits: 1
            }) + ' %';
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

        function getStatus(percent) {
            if (percent >= 100) {
                return { className: 'invoice-status--success', label: 'Tercapai' };
            }
            if (percent >= 80) {
                return { className: 'invoice-status--info', label: 'On Track' };
            }
            if (percent >= 50) {
                return { className: 'invoice-status--warning', label: 'Perlu Dorong' };
            }
            return { className: 'invoice-status--danger', label: 'Prioritas' };
        }

        function statusHtml(percent) {
            const status = getStatus(percent);
            return '<span class="invoice-status ' + status.className + '">' + status.label + '</span>';
        }

        function progressHtml(percent) {
            const width = Math.max(0, Math.min(percent, 100));
            return '<div class="invoice-progress">' +
                '<div class="invoice-progress__track">' +
                '<div class="invoice-progress__bar" style="width: ' + width + '%"></div>' +
                '</div>' +
                '<span class="invoice-progress__text">' + formatPercent(percent) + '</span>' +
                '</div>';
        }

        function updateKpiFromRows(rows) {
            let target = 0;
            let achieved = 0;

            rows.forEach(function (row) {
                target += row.target;
                achieved += row.achieved;
            });

            const outstanding = Math.max(target - achieved, 0);
            const percent = target > 0 ? (achieved / target) * 100 : 0;

            $('#invoice_kpi_target').text(formatMoney(target));
            $('#invoice_kpi_achieved').text(formatMoney(achieved));
            $('#invoice_kpi_outstanding').text(formatMoney(outstanding));
            $('#invoice_kpi_percent').html(
                formatPercent(percent) +
                ' <span class="invoice-kpi-card__trend"><i class="fas fa-arrow-up"></i>' +
                (percent >= 100 ? 'Naik' : 'Belum tercapai') +
                '</span>'
            );
        }

        function collectFilters() {
            return {
                project: $('#invoice_filter_project').val() || '',
                pic: $('#invoice_filter_pic').val() || '',
                regional: $('#invoice_filter_regional').val() || '',
                area: $('#invoice_filter_area').val() || '',
                month: $('#invoice_filter_month').val() || '',
                week: $('#invoice_filter_week').val() || ''
            };
        }

        function getFilterLabel(key) {
            const labels = {
                project: 'Project',
                pic: 'PIC',
                regional: 'Regional',
                area: 'Area',
                month: 'Bulan',
                week: 'Week'
            };

            return labels[key] || key;
        }

        function getSelectedText(key) {
            const selected = filterSelects[key].find('option:selected');
            return selected.length ? selected.text() : '';
        }

        function renderActiveFilterBadges(filters) {
            const container = $('#invoice_active_filters');
            container.empty();

            const activeKeys = filterOrder.filter(function (key) {
                return Boolean(filters[key]);
            });

            if (!activeKeys.length) {
                container.append(
                    '<span class="invoice-active-filter invoice-active-filter--empty">' +
                    '<i class="fas fa-filter"></i> Semua data' +
                    '</span>'
                );
                return;
            }

            activeKeys.forEach(function (key) {
                container.append(
                    '<span class="invoice-active-filter">' +
                    '<i class="fas fa-check-circle"></i> ' +
                    escapeHtml(getFilterLabel(key)) + ': ' + escapeHtml(getSelectedText(key)) +
                    '</span>'
                );
            });
        }

        function matchesFilters(row, filters) {
            return (!filters.project || row.project === filters.project) &&
                (!filters.pic || row.pic === filters.pic) &&
                (!filters.regional || row.regional === filters.regional) &&
                (!filters.area || row.area === filters.area) &&
                (!filters.month || row.month === filters.month) &&
                (!filters.week || row.week === filters.week);
        }

        function filterRows(filters) {
            return invoiceRows.filter(function (row) {
                return matchesFilters(row, filters);
            });
        }

        function groupRows(rows, keyFn, seedFn) {
            const groups = new Map();

            rows.forEach(function (row) {
                const key = keyFn(row);
                if (!key) {
                    return;
                }

                if (!groups.has(key)) {
                    groups.set(key, Object.assign({
                        target: 0,
                        achieved: 0,
                        projectSet: new Set(),
                        areaSet: new Set()
                    }, seedFn(row)));
                }

                const group = groups.get(key);
                group.target += row.target;
                group.achieved += row.achieved;
                if (row.project) {
                    group.projectSet.add(row.project);
                }
                if (row.area) {
                    group.areaSet.add(row.area);
                }
            });

            return Array.from(groups.values()).map(function (group) {
                group.outstanding = Math.max(group.target - group.achieved, 0);
                group.percent = getPercent(group.target, group.achieved);
                group.projectCount = group.projectSet.size;
                group.areaCount = group.areaSet.size;
                return group;
            });
        }

        function getTotals(rows) {
            return rows.reduce(function (totals, row) {
                totals.target += Number(row.target || 0);
                totals.achieved += Number(row.achieved || 0);
                totals.outstanding += Number(row.outstanding || 0);
                return totals;
            }, {
                target: 0,
                achieved: 0,
                outstanding: 0
            });
        }

        function updateMoneyFooter(prefix, rows) {
            const totals = getTotals(rows);
            $('#' + prefix + '_total_target').text(formatMoney(totals.target));
            $('#' + prefix + '_total_achieved').text(formatMoney(totals.achieved));
            $('#' + prefix + '_total_outstanding').text(formatMoney(totals.outstanding));
            $('#' + prefix + '_total_percent').text(formatPercent(getPercent(totals.target, totals.achieved)));
        }

        function getProjectGroups(rows) {
            return groupRows(rows, function (row) {
                return row.project;
            }, function (row) {
                return {
                    project: row.project,
                    projectName: row.projectName,
                    pic: row.pic
                };
            }).sort(function (a, b) {
                return b.target - a.target;
            });
        }

        function renderProjectTable(rows) {
            const groups = getProjectGroups(rows);

            tables['#invoice_project_table'].clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.projectName),
                    escapeHtml(row.pic),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    progressHtml(row.percent),
                    statusHtml(row.percent),
                    '<a href="<?= site_url('TargetInvoice/detailBowheer/') ?>' + encodeURIComponent(row.project) + '" class="invoice-action-btn"><i class="fas fa-eye"></i></a>'
                ];
            })).draw();
            updateMoneyFooter('invoice_project', groups);
        }

        function renderTopLists(rows) {
            const projectGroups = getProjectGroups(rows);
            const outstandingRows = projectGroups.slice().sort(function (a, b) {
                return b.outstanding - a.outstanding;
            }).slice(0, 5);
            const topInvoiceRows = projectGroups.slice().sort(function (a, b) {
                return b.achieved - a.achieved;
            }).slice(0, 5);

            const renderList = function (selector, list, valueFn, hintFn) {
                const target = $(selector);
                target.empty();

                if (!list.length) {
                    target.append('<li><div><strong>Tidak ada data</strong><span>Filter tidak menemukan invoice.</span></div><strong>-</strong></li>');
                    return;
                }

                list.forEach(function (row) {
                    target.append(
                        '<li>' +
                        '<div><strong>' + escapeHtml(row.projectName) + '</strong><span>' + hintFn(row) + '</span></div>' +
                        '<strong>' + valueFn(row) + '</strong>' +
                        '</li>'
                    );
                });
            };

            renderList('#invoice_top_outstanding_list', outstandingRows, function (row) {
                return formatMoney(row.outstanding);
            }, function (row) {
                return escapeHtml(row.pic || '-');
            });

            renderList('#invoice_top_project_list', topInvoiceRows, function (row) {
                return formatMoney(row.achieved);
            }, function (row) {
                return formatPercent(row.percent);
            });
        }

        function renderPicTable(rows) {
            const groups = groupRows(rows, function (row) {
                return row.pic;
            }, function (row) {
                return { pic: row.pic || '-' };
            }).sort(function (a, b) {
                return b.target - a.target;
            });

            tables['#invoice_pic_table'].clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.pic),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    formatPercent(row.percent),
                    statusHtml(row.percent)
                ];
            })).draw();
            updateMoneyFooter('invoice_pic', groups);
        }

        function renderRegionalTable(rows) {
            const groups = groupRows(rows, function (row) {
                return row.regional;
            }, function (row) {
                return { regional: row.regional || '-' };
            }).sort(function (a, b) {
                return String(a.regional).localeCompare(String(b.regional));
            });

            tables['#invoice_regional_table'].clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.regional),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    formatPercent(row.percent),
                    statusHtml(row.percent)
                ];
            })).draw();
            updateMoneyFooter('invoice_regional', groups);
        }

        function renderCityTable(rows) {
            const groups = groupRows(rows, function (row) {
                return row.regional + '|' + row.area;
            }, function (row) {
                return {
                    regional: row.regional || '-',
                    area: row.area || '-',
                    picArea: row.picArea || '-'
                };
            }).sort(function (a, b) {
                return b.target - a.target;
            });

            tables['#invoice_city_table'].clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.regional),
                    escapeHtml(row.area),
                    escapeHtml(row.picArea),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    formatPercent(row.percent),
                    statusHtml(row.percent),
                    '<a href="<?= site_url('TargetInvoice/detailKota/') ?>' + encodeURIComponent(row.area) + '" class="invoice-action-btn"><i class="fas fa-eye"></i></a>'
                ];
            })).draw();
            updateMoneyFooter('invoice_city', groups);
        }

        function renderPeriodTable(rows) {
            const groups = groupRows(rows, function (row) {
                return row.month + '|' + row.week;
            }, function (row) {
                return {
                    month: row.month || '-',
                    week: row.week || '-'
                };
            }).sort(function (a, b) {
                const monthCompare = (monthOrder[a.month] || 99) - (monthOrder[b.month] || 99);
                if (monthCompare !== 0) {
                    return monthCompare;
                }
                return String(a.week).localeCompare(String(b.week));
            });

            tables['#invoice_period_table'].clear().rows.add(groups.map(function (row, index) {
                return [
                    index + 1,
                    escapeHtml(row.month),
                    escapeHtml(row.week),
                    formatMoney(row.target),
                    formatMoney(row.achieved),
                    formatMoney(row.outstanding),
                    formatPercent(row.percent),
                    formatNumber(row.projectCount),
                    formatNumber(row.areaCount),
                    statusHtml(row.percent)
                ];
            })).draw();
            updateMoneyFooter('invoice_period', groups);
            $('#invoice_period_total_project').text(formatNumber(new Set(rows.map(function (row) {
                return row.project;
            }).filter(Boolean)).size));
            $('#invoice_period_total_area').text(formatNumber(new Set(rows.map(function (row) {
                return row.area;
            }).filter(Boolean)).size));
        }

        function renderAll(filters) {
            filters = filters || collectFilters();
            const rows = filterRows(filters);
            renderActiveFilterBadges(filters);
            updateKpiFromRows(rows);
            renderTopLists(rows);
            renderProjectTable(rows);
            renderPicTable(rows);
            renderRegionalTable(rows);
            renderCityTable(rows);
            renderPeriodTable(rows);
            adjustActiveTable();
        }

        function adjustActiveTable() {
            window.setTimeout(function () {
                $('.tab-pane.active table.invoice-table').each(function () {
                    const tableId = '#' + this.id;
                    if (tables[tableId]) {
                        tables[tableId].columns.adjust().draw(false);
                    }
                });
            }, 80);
        }

        function filterRowsByKeys(keys) {
            const selected = collectFilters();
            return invoiceRows.filter(function (row) {
                return keys.every(function (key) {
                    return !selected[key] || row[key] === selected[key];
                });
            });
        }

        function uniqueOptions(rows, key) {
            const options = new Map();
            rows.forEach(function (row) {
                const value = row[key];
                if (!value) {
                    return;
                }

                const label = key === 'project' ? row.projectName : value;
                options.set(value, label);
            });

            return Array.from(options.entries()).sort(function (a, b) {
                if (key === 'month') {
                    return (monthOrder[a[0]] || 99) - (monthOrder[b[0]] || 99);
                }
                return String(a[1]).localeCompare(String(b[1]));
            });
        }

        function rebuildSelect(key, rows) {
            const select = filterSelects[key];
            const currentValue = select.val() || '';
            const labels = {
                project: 'Semua Project',
                pic: 'Semua PIC',
                regional: 'Semua Regional',
                area: 'Semua Area',
                month: 'Semua Bulan',
                week: 'Semua Week'
            };
            const options = uniqueOptions(rows, key);
            let hasCurrentValue = currentValue === '';

            select.empty();
            select.append(new Option(labels[key], '', true, currentValue === ''));

            options.forEach(function (option) {
                const selected = option[0] === currentValue;
                hasCurrentValue = hasCurrentValue || selected;
                select.append(new Option(option[1], option[0], false, selected));
            });

            if (!hasCurrentValue) {
                select.val('');
            }

            select.trigger('change.select2');
        }

        function rebuildDescendantFilters(changedKey) {
            const startIndex = Math.max(filterOrder.indexOf(changedKey) + 1, 0);

            for (let i = startIndex; i < filterOrder.length; i++) {
                const key = filterOrder[i];
                const parentKeys = filterOrder.slice(0, i);
                rebuildSelect(key, filterRowsByKeys(parentKeys));
            }
        }

        filterOrder.forEach(function (key) {
            filterSelects[key].on('change', function () {
                rebuildDescendantFilters(key);
            });
        });

        $('#invoice_apply_filter').on('click', function () {
            renderAll(collectFilters());
        });

        $('#invoice_reset_filter').on('click', function () {
            $('#invoice_filter_project, #invoice_filter_pic, #invoice_filter_regional, #invoice_filter_area, #invoice_filter_month, #invoice_filter_week')
                .val('')
                .trigger('change.select2');
            rebuildDescendantFilters('project');
            renderAll({});
        });

        $('a[data-toggle="pill"]').on('shown.bs.tab', function () {
            adjustActiveTable();
        });

        function updateTargetAddAreaOptions() {
            const areaSelect = $('#target_add_area');
            const areas = uniqueOptions(invoiceRows, 'area');
            areaSelect.empty().append(new Option('Pilih Area', '', true, false));
            areas.forEach(function (option) {
                areaSelect.append(new Option(option[1], option[0]));
            });
            areaSelect.val('').trigger('change.select2');
        }

        function syncTargetAddAreaMeta() {
            const projectId = String($('#target_add_project option:selected').data('id') || '');
            const area = $('#target_add_area').val() || '';
            const row = invoiceRows.find(function (item) {
                return item.project === projectId && item.area === area;
            });

            if (row) {
                $('#target_add_regional').val(row.regional);
                $('#target_add_pic_area').val(row.picArea);
            }
        }

        function setTargetNewAreaMode(enabled) {
            $('.target-new-area-field').toggleClass('invoice-hidden', !enabled);
            $('#target_add_show_new_area').toggleClass('invoice-hidden', enabled);
            $('#target_add_cancel_new_area').toggleClass('invoice-hidden', !enabled);
            $('#target_add_area').prop('disabled', enabled);

            if (enabled) {
                $('#target_add_area').val('').trigger('change');
                $('#target_add_area_value').val('');
                $('#target_add_new_area').focus();
            } else {
                $('#target_add_new_area, #target_add_regional, #target_add_pic_area').val('');
                $('#target_add_area').prop('disabled', false).trigger('change');
            }

            updateTargetModalStepState();
        }

        function hasTargetAreaValue() {
            return Boolean($('#target_add_new_area').val().trim() || $('#target_add_area').val());
        }

        function updateTargetModalStepState() {
            const hasProject = Boolean($('#target_add_project').val());
            const hasArea = hasProject && hasTargetAreaValue();
            const hasMonth = hasArea && Boolean($('#target_add_month').val());
            const hasWeek = hasMonth && Boolean($('#target_add_week').val());
            const isNewAreaMode = !$('.target-new-area-field').first().hasClass('invoice-hidden');

            $('#target_add_area').prop('disabled', !hasProject || isNewAreaMode);
            $('#target_add_show_new_area').prop('disabled', !hasProject);
            $('#target_add_new_area, #target_add_regional, #target_add_pic_area').prop('disabled', !hasProject || !isNewAreaMode);
            $('#target_add_month').prop('disabled', !hasArea);
            $('#target_add_week').prop('disabled', !hasMonth);
            $('#formTargetTambahInvoice [name="achiev_invoice"]').prop('disabled', !hasWeek);
            $('#formTargetTambahInvoice [name="tambahan_invoice"]').prop('disabled', !hasWeek || $('#target_add_extra_group').hasClass('invoice-hidden'));
            $('#formTargetTambahInvoice button[type="submit"]').prop('disabled', !hasWeek);

            $('#target_add_area, #target_add_month, #target_add_week').trigger('change.select2');
        }

        function loadTargetInvoiceForModal() {
            const bowheer = $('#target_add_project').val();
            const area = $('#target_add_new_area').val().trim() || $('#target_add_area').val();
            const month = $('#target_add_month').val();
            const week = $('#target_add_week').val();
            $('#target_add_area_value').val(area);

            if (!bowheer || !area || !month || !week) {
                return;
            }

            $.post("<?= base_url('RincianInvoice/get_target_invoice') ?>", {
                bowheer: bowheer,
                area: area,
                month: month,
                week: week
            }, function (res) {
                const data = typeof res === 'string' ? JSON.parse(res || '{}') : res;
                const target = Number(data.qty_target || 0);
                const achieved = Number(data.qty_achiev_target || 0);
                $('#formTargetTambahInvoice [name="target_invoice"]').val(formatNumber(target));
                $('#formTargetTambahInvoice [name="achiev_invoice"]').val(formatNumber(achieved));
                setTargetInvoiceAmountMode(achieved);
                updateTargetModalStepState();
            });
        }

        function updateTargetTotalInput() {
            const achieved = parseMoney($('#formTargetTambahInvoice [name="achiev_invoice"]').val());
            const tambahan = parseMoney($('#formTargetTambahInvoice [name="tambahan_invoice"]').val());
            $('#formTargetTambahInvoice [name="total_invoice"]').val(formatNumber(achieved + tambahan));
        }

        function setTargetInvoiceAmountMode(existingAchieved) {
            existingAchieved = Number(existingAchieved || 0);
            const hasExistingAchieved = existingAchieved > 0;

            $('#target_add_extra_group, #target_add_total_group').toggleClass('invoice-hidden', !hasExistingAchieved);
            $('#formTargetTambahInvoice [name="achiev_invoice"]').prop('readonly', hasExistingAchieved);
            $('#formTargetTambahInvoice [name="tambahan_invoice"]').prop('disabled', !hasExistingAchieved);

            if (hasExistingAchieved) {
                $('#formTargetTambahInvoice [name="tambahan_invoice"]').val('0');
                $('#formTargetTambahInvoice [name="total_invoice"]').val(formatNumber(existingAchieved));
            } else {
                $('#formTargetTambahInvoice [name="tambahan_invoice"]').val('0');
                $('#formTargetTambahInvoice [name="total_invoice"]').val('0');
            }
        }

        $('#target_add_project').on('change', function () {
            updateTargetAddAreaOptions();
            $('#target_add_area, #target_add_month, #target_add_week').val('').trigger('change.select2');
            $('#target_add_new_area, #target_add_regional, #target_add_pic_area').val('');
            $('#formTargetTambahInvoice [name="target_invoice"], #formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"], #formTargetTambahInvoice [name="total_invoice"]').val('0');
            setTargetInvoiceAmountMode(0);
            setTargetNewAreaMode(false);
            updateTargetModalStepState();
            loadTargetInvoiceForModal();
        });
        $('#target_add_area').on('change', function () {
            $('#target_add_area_value').val($(this).val());
            syncTargetAddAreaMeta();
            $('#target_add_month, #target_add_week').val('').trigger('change.select2');
            $('#formTargetTambahInvoice [name="target_invoice"], #formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"], #formTargetTambahInvoice [name="total_invoice"]').val('0');
            setTargetInvoiceAmountMode(0);
            updateTargetModalStepState();
            loadTargetInvoiceForModal();
        });
        $('#target_add_new_area').on('keyup change', function () {
            $('#target_add_area_value').val($(this).val().trim());
            $('#target_add_month, #target_add_week').val('').trigger('change.select2');
            $('#formTargetTambahInvoice [name="target_invoice"], #formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"], #formTargetTambahInvoice [name="total_invoice"]').val('0');
            setTargetInvoiceAmountMode(0);
            updateTargetModalStepState();
            loadTargetInvoiceForModal();
        });
        $('#target_add_month').on('change', function () {
            $('#target_add_week').val('').trigger('change.select2');
            $('#formTargetTambahInvoice [name="target_invoice"], #formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"], #formTargetTambahInvoice [name="total_invoice"]').val('0');
            setTargetInvoiceAmountMode(0);
            updateTargetModalStepState();
            loadTargetInvoiceForModal();
        });
        $('#target_add_week').on('change', function () {
            updateTargetModalStepState();
            loadTargetInvoiceForModal();
        });
        $('#formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"]').on('keyup change', updateTargetTotalInput);
        $('#target_add_show_new_area').on('click', function () {
            setTargetNewAreaMode(true);
        });
        $('#target_add_cancel_new_area').on('click', function () {
            setTargetNewAreaMode(false);
            loadTargetInvoiceForModal();
        });

        $('#modalTargetTambahInvoice').on('show.bs.modal', function () {
            $('#formTargetTambahInvoice')[0].reset();
            $('#target_add_project, #target_add_area, #target_add_month, #target_add_week').val('').trigger('change.select2');
            $('#formTargetTambahInvoice [name="target_invoice"], #formTargetTambahInvoice [name="achiev_invoice"], #formTargetTambahInvoice [name="tambahan_invoice"], #formTargetTambahInvoice [name="total_invoice"]').val('0');
            $('#target_add_area_value').val('');
            updateTargetAddAreaOptions();
            setTargetInvoiceAmountMode(0);
            setTargetNewAreaMode(false);
            updateTargetModalStepState();
        });

        $('#formTargetTambahInvoice').on('submit', function (event) {
            event.preventDefault();
            const area = $('#target_add_new_area').val().trim() || $('#target_add_area').val();
            $('#target_add_area_value').val(area);

            if (!$('#target_add_project').val() || !area || !$('#target_add_month').val() || !$('#target_add_week').val()) {
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
                        regional: data.regional || $('#target_add_regional').val(),
                        pic: data.pic || $('#target_add_pic_area').val(),
                        nilai_update: data.nilai_update
                    }, function (createRes) {
                        const createData = typeof createRes === 'string' ? JSON.parse(createRes || '{}') : createRes;
                        if (createData.status === true) {
                            Swal.fire('Berhasil', 'Invoice berhasil disimpan.', 'success').then(function () {
                                window.location.href = "<?= base_url('TargetInvoice/revamp') ?>";
                            });
                        } else {
                            Swal.fire('Gagal', createData.message || 'Gagal menambahkan invoice.', 'error');
                        }
                    });
                    return;
                }

                if (data.status === true) {
                    Swal.fire('Berhasil', 'Invoice berhasil disimpan.', 'success').then(function () {
                        window.location.href = "<?= base_url('TargetInvoice/revamp') ?>";
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Tidak ada data yang diubah.', 'error');
                }
            });
        });

        rebuildDescendantFilters('project');
        renderAll({});
        adjustActiveTable();
    });
</script>
