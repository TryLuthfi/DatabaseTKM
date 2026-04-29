<?php
if (!function_exists('dashboard_money')) {
    function dashboard_money($value)
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('dashboard_compact_money')) {
    function dashboard_compact_money($value)
    {
        $value = (float) $value;
        $abs = abs($value);

        if ($abs >= 1000000000) {
            return 'Rp ' . number_format($value / 1000000000, 1, ',', '.') . ' M';
        }

        if ($abs >= 1000000) {
            return 'Rp ' . number_format($value / 1000000, 1, ',', '.') . ' Jt';
        }

        return dashboard_money($value);
    }
}

if (!function_exists('dashboard_percent')) {
    function dashboard_percent($value)
    {
        return number_format((float) $value, 1, ',', '.') . '%';
    }
}

$invoiceTarget = (float) ($invoiceTarget ?? 0);
$portfolioSummary = $portfolioSummary ?? [];
$bilcoSummary = $bilcoSummary ?? [];
$budgetSummary = $budgetSummary ?? [];
$emrSummary = $emrSummary ?? [];
?>

<div class="content-wrapper dashboard-premium">
    <div class="content-header">
        <div class="container-fluid">
            <div class="dashboard-hero">
                <div class="dashboard-hero__copy">
                    <span class="dashboard-hero__eyebrow">Executive Dashboard</span>
                    <h1 class="dashboard-hero__title">Project Summary EMR, BILCO, dan Budgeting</h1>
                    <p class="dashboard-hero__subtitle">Satu halaman ringkas untuk membaca performa invoice, approval EMR, serta penyerapan budget tahun <?= (int) ($dashboardYear ?? date('Y')) ?>.</p>
                </div>
                <div class="dashboard-hero__panel">
                    <div class="hero-kpi-label">Target Invoice Utama</div>
                    <div class="hero-kpi-value"><?= dashboard_compact_money($invoiceTarget) ?></div>
                    <div class="hero-kpi-meta">Progress saat ini <?= dashboard_percent($portfolioSummary['invoice_progress'] ?? 0) ?></div>
                    <div class="hero-progress">
                        <div class="hero-progress__bar" style="width: <?= min((float) ($portfolioSummary['invoice_progress'] ?? 0), 100) ?>%;"></div>
                    </div>
                    <div class="hero-kpi-grid">
                        <div>
                            <span class="hero-kpi-grid__label">Actual</span>
                            <strong><?= dashboard_compact_money($portfolioSummary['invoice_actual'] ?? 0) ?></strong>
                        </div>
                        <div>
                            <span class="hero-kpi-grid__label">Gap</span>
                            <strong><?= dashboard_compact_money($portfolioSummary['invoice_gap'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="dashboard-card dashboard-card--spotlight">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="dashboard-card__eyebrow">Portfolio Pulse</span>
                                <h3 class="dashboard-card__title">Cross Project Snapshot</h3>
                            </div>
                        </div>
                        <div class="spotlight-grid">
                            <div class="spotlight-item spotlight-item--invoice">
                                <span class="spotlight-item__label">Invoice Progress</span>
                                <strong><?= dashboard_percent($portfolioSummary['invoice_progress'] ?? 0) ?></strong>
                                <small>Terhadap target <?= dashboard_compact_money($invoiceTarget) ?></small>
                            </div>
                            <div class="spotlight-item spotlight-item--budget">
                                <span class="spotlight-item__label">Budget Absorption</span>
                                <strong><?= dashboard_percent($portfolioSummary['budget_absorption'] ?? 0) ?></strong>
                                <small>Realisasi vs annual budget</small>
                            </div>
                            <div class="spotlight-item spotlight-item--emr">
                                <span class="spotlight-item__label">EMR Release Ratio</span>
                                <strong><?= dashboard_percent($portfolioSummary['emr_release_ratio'] ?? 0) ?></strong>
                                <small>Release finance vs nominal EMR</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="dashboard-card dashboard-card--target">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="dashboard-card__eyebrow">Quick Target</span>
                                <h3 class="dashboard-card__title">110M Invoice Tracker</h3>
                            </div>
                        </div>
                        <div class="target-ring">
                            <div class="target-ring__inner">
                                <span><?= dashboard_percent($portfolioSummary['invoice_progress'] ?? 0) ?></span>
                            </div>
                        </div>
                        <div class="target-meta">
                            <div><span>Outstanding BILCO</span><strong><?= dashboard_compact_money($bilcoSummary['total_invoice'] ?? 0) ?></strong></div>
                            <div><span>Sisa ke Target</span><strong><?= dashboard_compact_money($portfolioSummary['invoice_gap'] ?? 0) ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-4">
                    <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="dashboard-link-card">
                        <div class="dashboard-card summary-card summary-card--emr">
                            <div class="summary-card__icon"><i class="fas fa-network-wired"></i></div>
                            <div class="summary-card__body">
                                <span class="summary-card__eyebrow">Project EMR</span>
                                <h3 class="summary-card__title">Approval & Release</h3>
                                <div class="summary-card__value"><?= dashboard_compact_money($emrSummary['nominal_emr'] ?? 0) ?></div>
                                <p class="summary-card__desc">Nominal approval EMR dari <?= (int) ($emrSummary['clusters'] ?? 0) ?> cluster batch.</p>
                                <div class="summary-mini-grid">
                                    <div><span>Released</span><strong><?= dashboard_compact_money($emrSummary['nominal_release'] ?? 0) ?></strong></div>
                                    <div><span>HP Donasi</span><strong><?= number_format((float) ($emrSummary['hp_donasi'] ?? 0), 0, ',', '.') ?></strong></div>
                                    <div><span>Waiting</span><strong><?= (int) ($emrSummary['waiting'] ?? 0) ?></strong></div>
                                    <div><span>Released Batch</span><strong><?= (int) ($emrSummary['released'] ?? 0) ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-lg-4">
                    <a href="<?= base_url('BillingPayment') ?>" class="dashboard-link-card">
                        <div class="dashboard-card summary-card summary-card--bilco">
                            <div class="summary-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="summary-card__body">
                                <span class="summary-card__eyebrow">Project BILCO</span>
                                <h3 class="summary-card__title">Outstanding Invoice</h3>
                                <div class="summary-card__value"><?= dashboard_compact_money($bilcoSummary['total_invoice'] ?? 0) ?></div>
                                <p class="summary-card__desc">Akumulasi invoice open dan partial yang masih perlu dimonitor.</p>
                                <div class="summary-mini-grid">
                                    <div><span>P1</span><strong><?= dashboard_compact_money($bilcoSummary['p1'] ?? 0) ?></strong></div>
                                    <div><span>P2</span><strong><?= dashboard_compact_money($bilcoSummary['p2'] ?? 0) ?></strong></div>
                                    <div><span>P3</span><strong><?= dashboard_compact_money($bilcoSummary['p3'] ?? 0) ?></strong></div>
                                    <div><span>BJT</span><strong><?= dashboard_compact_money($bilcoSummary['bjt'] ?? 0) ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-lg-4">
                    <a href="<?= base_url('Budget_Report') ?>" class="dashboard-link-card">
                        <div class="dashboard-card summary-card summary-card--budgeting">
                            <div class="summary-card__icon"><i class="fas fa-wallet"></i></div>
                            <div class="summary-card__body">
                                <span class="summary-card__eyebrow">Budgeting</span>
                                <h3 class="summary-card__title">Annual Budget Summary</h3>
                                <div class="summary-card__value"><?= dashboard_compact_money($budgetSummary['annual_budget'] ?? 0) ?></div>
                                <p class="summary-card__desc">Ringkasan budget tahunan dan realisasi cashflow aktif.</p>
                                <div class="summary-mini-grid">
                                    <div><span>Realisasi</span><strong><?= dashboard_compact_money($budgetSummary['realisasi'] ?? 0) ?></strong></div>
                                    <div><span>Sisa</span><strong><?= dashboard_compact_money($budgetSummary['sisa'] ?? 0) ?></strong></div>
                                    <div><span>Total Project</span><strong><?= (int) ($budgetSummary['total_project'] ?? 0) ?></strong></div>
                                    <div><span>TEC</span><strong><?= (int) ($budgetSummary['total_tec'] ?? 0) ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-xl-7">
                    <div class="dashboard-card dashboard-card--table">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="dashboard-card__eyebrow">Priority Lens</span>
                                <h3 class="dashboard-card__title">Fokus Operasional Hari Ini</h3>
                            </div>
                        </div>
                        <div class="priority-list">
                            <div class="priority-list__item">
                                <div>
                                    <span class="priority-list__label">Urgent BILCO</span>
                                    <strong><?= dashboard_compact_money(($bilcoSummary['urgent_total'] ?? 0)) ?></strong>
                                </div>
                                <small>P1 + P2 outstanding yang perlu percepatan follow up.</small>
                            </div>
                            <div class="priority-list__item">
                                <div>
                                    <span class="priority-list__label">Healthy Pipeline</span>
                                    <strong><?= dashboard_compact_money(($bilcoSummary['healthy_total'] ?? 0)) ?></strong>
                                </div>
                                <small>P3 + BJT yang masih dalam jendela monitor aman.</small>
                            </div>
                            <div class="priority-list__item">
                                <div>
                                    <span class="priority-list__label">Budget Items Overrun</span>
                                    <strong><?= (int) ($budgetSummary['overbudget_items'] ?? 0) ?></strong>
                                </div>
                                <small>Jumlah item annual budget yang sudah melewati pagu.</small>
                            </div>
                            <div class="priority-list__item">
                                <div>
                                    <span class="priority-list__label">EMR Pending Approval</span>
                                    <strong><?= (int) ($emrSummary['waiting'] ?? 0) ?></strong>
                                </div>
                                <small>Cluster yang masih berada di waiting HO, EMR, atau finance.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="dashboard-card dashboard-card--actions">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="dashboard-card__eyebrow">Shortcut</span>
                                <h3 class="dashboard-card__title">Akses Cepat Modul</h3>
                            </div>
                        </div>
                        <div class="dashboard-action-grid">
                            <a href="<?= base_url('BillingPayment') ?>" class="dashboard-action-tile">
                                <i class="fas fa-arrow-right"></i>
                                <span>Billing Payment</span>
                            </a>
                            <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="dashboard-action-tile">
                                <i class="fas fa-arrow-right"></i>
                                <span>Batch Approval EMR</span>
                            </a>
                            <a href="<?= base_url('Budget_Report') ?>" class="dashboard-action-tile">
                                <i class="fas fa-arrow-right"></i>
                                <span>Budget Report</span>
                            </a>
                            <a href="<?= base_url('Budget_Cashflow') ?>" class="dashboard-action-tile">
                                <i class="fas fa-arrow-right"></i>
                                <span>Budget Cashflow</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .dashboard-premium {
        background:
            radial-gradient(circle at top left, rgba(115, 170, 214, 0.18), transparent 28%),
            linear-gradient(180deg, #f5f9fc 0%, #ecf4f9 100%);
    }

    .dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.9fr);
        gap: 20px;
        padding: 1.5rem 0 1rem;
        align-items: stretch;
    }

    .dashboard-hero__copy,
    .dashboard-hero__panel,
    .dashboard-card {
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 22px 46px rgba(15, 40, 61, 0.08);
        border: 1px solid rgba(205, 225, 238, 0.9);
    }

    .dashboard-hero__copy {
        padding: 2rem 2.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1e678f 55%, #5eb1dc 100%);
        color: #fff;
    }

    .dashboard-hero__eyebrow,
    .dashboard-card__eyebrow {
        display: inline-block;
        margin-bottom: 0.6rem;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .dashboard-hero__eyebrow {
        color: rgba(255, 255, 255, 0.78);
    }

    .dashboard-hero__title {
        margin: 0;
        font-size: 2.15rem;
        line-height: 1.15;
        font-weight: 800;
    }

    .dashboard-hero__subtitle {
        max-width: 760px;
        margin: 0.95rem 0 0;
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.86);
    }

    .dashboard-hero__panel {
        padding: 1.6rem;
    }

    .hero-kpi-label {
        color: #67839b;
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .hero-kpi-value {
        margin-top: 0.5rem;
        font-size: 2rem;
        font-weight: 800;
        color: #113c59;
    }

    .hero-kpi-meta {
        margin-top: 0.35rem;
        color: #6d879b;
        font-weight: 600;
    }

    .hero-progress {
        height: 12px;
        margin: 1rem 0 1.1rem;
        border-radius: 999px;
        background: #e6f0f7;
        overflow: hidden;
    }

    .hero-progress__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(135deg, #0f8b72 0%, #30c29e 100%);
    }

    .hero-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .hero-kpi-grid div {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: linear-gradient(180deg, #f6fbff 0%, #edf5fb 100%);
        border: 1px solid #dceaf4;
    }

    .hero-kpi-grid__label {
        display: block;
        margin-bottom: 0.3rem;
        color: #6d879b;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .hero-kpi-grid strong {
        color: #163f5b;
        font-size: 1rem;
    }

    .dashboard-card {
        padding: 1.3rem 1.35rem;
        margin-bottom: 20px;
    }

    .dashboard-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 1rem;
    }

    .dashboard-card__eyebrow {
        color: #6b879d;
    }

    .dashboard-card__title {
        margin: 0;
        font-size: 1.18rem;
        font-weight: 800;
        color: #133f5d;
    }

    .spotlight-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .spotlight-item {
        padding: 1.1rem 1rem;
        border-radius: 18px;
        color: #fff;
    }

    .spotlight-item--invoice {
        background: linear-gradient(135deg, #0f766e 0%, #22a79c 100%);
    }

    .spotlight-item--budget {
        background: linear-gradient(135deg, #9a3412 0%, #ea580c 100%);
    }

    .spotlight-item--emr {
        background: linear-gradient(135deg, #1d4ed8 0%, #4f8ef7 100%);
    }

    .spotlight-item__label {
        display: block;
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        opacity: 0.85;
    }

    .spotlight-item strong {
        display: block;
        margin: 0.6rem 0 0.2rem;
        font-size: 1.55rem;
        line-height: 1.1;
    }

    .spotlight-item small {
        font-size: 0.88rem;
        opacity: 0.92;
    }

    .dashboard-card--target {
        text-align: center;
    }

    .target-ring {
        width: 190px;
        height: 190px;
        margin: 0.6rem auto 1rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(circle at center, #fff 56%, transparent 57%),
            conic-gradient(#103b5a 0deg, #26b190 <?= max(min((float) ($portfolioSummary['invoice_progress'] ?? 0), 100), 0) * 3.6 ?>deg, #e5eff6 <?= max(min((float) ($portfolioSummary['invoice_progress'] ?? 0), 100), 0) * 3.6 ?>deg 360deg);
        box-shadow: inset 0 0 0 12px rgba(255, 255, 255, 0.75);
    }

    .target-ring__inner {
        width: 116px;
        height: 116px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #f9fcff 0%, #eff6fb 100%);
        color: #123d59;
        font-size: 1.45rem;
        font-weight: 800;
    }

    .target-meta {
        display: grid;
        gap: 10px;
    }

    .target-meta div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        background: #f5f9fc;
        border: 1px solid #dceaf4;
    }

    .target-meta span {
        color: #6d879b;
        font-weight: 700;
    }

    .target-meta strong {
        color: #173e5b;
    }

    .dashboard-link-card {
        display: block;
        color: inherit;
    }

    .dashboard-link-card:hover {
        color: inherit;
        text-decoration: none;
    }

    .summary-card {
        display: flex;
        gap: 16px;
        min-height: 100%;
        transition: transform 0.24s ease, box-shadow 0.24s ease;
    }

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 26px 52px rgba(15, 40, 61, 0.12);
    }

    .summary-card__icon {
        width: 64px;
        min-width: 64px;
        height: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.35rem;
    }

    .summary-card--emr .summary-card__icon {
        background: linear-gradient(135deg, #1d4ed8 0%, #5c8fff 100%);
    }

    .summary-card--bilco .summary-card__icon {
        background: linear-gradient(135deg, #0f766e 0%, #22a79c 100%);
    }

    .summary-card--budgeting .summary-card__icon {
        background: linear-gradient(135deg, #c2410c 0%, #fb923c 100%);
    }

    .summary-card__body {
        flex: 1;
        min-width: 0;
    }

    .summary-card__eyebrow {
        display: block;
        color: #688299;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .summary-card__title {
        margin: 0.3rem 0 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: #143d58;
    }

    .summary-card__value {
        margin-top: 0.9rem;
        font-size: 1.7rem;
        line-height: 1.1;
        font-weight: 800;
        color: #123c58;
    }

    .summary-card__desc {
        margin: 0.7rem 0 1rem;
        color: #698399;
        font-size: 0.92rem;
    }

    .summary-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .summary-mini-grid div {
        padding: 0.82rem 0.9rem;
        border-radius: 14px;
        background: linear-gradient(180deg, #f8fbfe 0%, #edf5fb 100%);
        border: 1px solid #dceaf4;
    }

    .summary-mini-grid span {
        display: block;
        margin-bottom: 0.25rem;
        color: #6c879b;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .summary-mini-grid strong {
        color: #173e5b;
        font-size: 0.94rem;
    }

    .priority-list {
        display: grid;
        gap: 12px;
    }

    .priority-list__item {
        padding: 1rem 1.05rem;
        border-radius: 16px;
        background: linear-gradient(180deg, #f9fcff 0%, #edf5fb 100%);
        border: 1px solid #dceaf4;
    }

    .priority-list__item > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 0.35rem;
    }

    .priority-list__label {
        color: #173e5b;
        font-weight: 800;
    }

    .priority-list__item strong {
        color: #0d6d64;
        font-size: 1rem;
    }

    .priority-list__item small {
        color: #6d879b;
        font-size: 0.88rem;
    }

    .dashboard-action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .dashboard-action-tile {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 1rem 1.05rem;
        border-radius: 16px;
        border: 1px solid #dceaf4;
        background: linear-gradient(180deg, #f9fcff 0%, #edf5fb 100%);
        color: #173e5b;
        font-weight: 800;
    }

    .dashboard-action-tile:hover {
        color: #173e5b;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(15, 40, 61, 0.08);
    }

    .dashboard-action-tile i {
        color: #0d6d64;
    }

    @media (max-width: 1199.98px) {
        .dashboard-hero {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-hero__copy,
        .dashboard-hero__panel,
        .dashboard-card {
            border-radius: 20px;
        }

        .dashboard-hero__copy,
        .dashboard-hero__panel,
        .dashboard-card {
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }

        .dashboard-hero__title {
            font-size: 1.75rem;
        }

        .spotlight-grid,
        .summary-mini-grid,
        .dashboard-action-grid,
        .hero-kpi-grid {
            grid-template-columns: 1fr;
        }

        .target-ring {
            width: 160px;
            height: 160px;
        }

        .target-ring__inner {
            width: 100px;
            height: 100px;
            font-size: 1.2rem;
        }
    }
</style>
