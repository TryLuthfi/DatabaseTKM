<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

$slugify = function ($value) {
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
};

$categoryVisuals = [
    'aksesories' => ['icon' => 'fas fa-plug', 'tone' => 'blue'],
    'closure' => ['icon' => 'fas fa-box-open', 'tone' => 'teal'],
    'fat-odp' => ['icon' => 'fas fa-network-wired', 'tone' => 'amber'],
    'fdt-odc' => ['icon' => 'fas fa-project-diagram', 'tone' => 'indigo'],
    'hdpe' => ['icon' => 'fas fa-wave-square', 'tone' => 'rose'],
    'kabel' => ['icon' => 'fas fa-ethernet', 'tone' => 'cyan'],
    'otb' => ['icon' => 'fas fa-server', 'tone' => 'violet'],
    'tiang' => ['icon' => 'fas fa-broadcast-tower', 'tone' => 'emerald'],
];

$categoryCards = [];
$dashboardTotalsMap = [];
foreach ($getAllStokByKategory as $stokKategory) {
    $slug = $slugify($stokKategory['kategori_item']);
    $visual = isset($categoryVisuals[$slug]) ? $categoryVisuals[$slug] : ['icon' => 'fas fa-cubes', 'tone' => 'slate'];
    $formattedTotal = number_format((float) $stokKategory['total_jumlah_stok'], 0, ',', '.');
    $unit = isset($stokKategory['satuan_item']) ? trim((string) $stokKategory['satuan_item']) : '';

    $categoryCards[] = [
        'key' => $slug,
        'label' => $stokKategory['kategori_item'],
        'value' => (float) $stokKategory['total_jumlah_stok'],
        'formatted' => trim($formattedTotal . ' ' . $unit),
        'unit' => $unit,
        'icon' => $visual['icon'],
        'tone' => $visual['tone'],
    ];
    $dashboardTotalsMap[$slug] = [
        'value' => (float) $stokKategory['total_jumlah_stok'],
        'formatted' => trim($formattedTotal . ' ' . $unit),
        'unit' => $unit,
        'label' => $stokKategory['kategori_item'],
    ];
}

$regionalTotals = [
    'jumlah_Aksesories' => 0,
    'jumlah_Closure' => 0,
    'jumlah_FAT' => 0,
    'jumlah_FDT' => 0,
    'jumlah_HDPE' => 0,
    'jumlah_Kabel' => 0,
    'jumlah_OTB' => 0,
    'jumlah_Tiang' => 0,
];
foreach ($getAllStokByKategoryFilterRegional as $row) {
    foreach ($regionalTotals as $key => $value) {
        $regionalTotals[$key] += (float) $row[$key];
    }
}

$areaTotals = [
    'jumlah_Aksesories' => 0,
    'jumlah_Closure' => 0,
    'jumlah_FAT' => 0,
    'jumlah_FDT' => 0,
    'jumlah_HDPE' => 0,
    'jumlah_Kabel' => 0,
    'jumlah_OTB' => 0,
    'jumlah_Tiang' => 0,
];
foreach ($getAllStokByKategoryFilterCity as $row) {
    foreach ($areaTotals as $key => $value) {
        $areaTotals[$key] += (float) $row[$key];
    }
}

$uniqueGudangCount = count(array_unique(array_filter(array_column($getListGudangLokasiUser, 'kota_lokasi_gudang'))));
$historyDocumentCount = count($getAllStokLogistik);
$projectCount = count($getUniqueProjectLogistik);
$itemCount = count($getUniqueItemLogistik);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
    .stock-revamp {
        --revamp-ink: #0f172a;
        --revamp-muted: #64748b;
        --revamp-line: rgba(148, 163, 184, 0.22);
        --revamp-surface: rgba(255, 255, 255, 0.94);
        --revamp-surface-soft: rgba(248, 250, 252, 0.92);
        --revamp-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        --revamp-blue: #1d4ed8;
        --revamp-teal: #0f766e;
        --revamp-amber: #b45309;
        --revamp-indigo: #4338ca;
        --revamp-rose: #be123c;
        --revamp-cyan: #0f766e;
        --revamp-violet: #6d28d9;
        --revamp-emerald: #047857;
    }

    .stock-revamp .content-header {
        padding-bottom: 0;
    }

    .stock-shell {
        padding: 1.15rem;
    }

    .stock-hero {
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

    .stock-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .stock-hero__eyebrow {
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

    .stock-hero h1 {
        margin: 1rem 0 0.75rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .stock-hero p {
        max-width: 44rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .stock-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.3rem;
    }

    .stock-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.82rem 1.15rem;
        border: 0;
        border-radius: 14px;
        font-weight: 700;
        transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
    }

    .stock-btn:hover {
        transform: translateY(-1px);
    }

    .stock-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
    }

    .stock-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .stock-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .stock-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .stock-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .stock-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .stock-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .stock-panel {
        margin-top: 1.2rem;
        border: 1px solid var(--revamp-line);
        border-radius: 24px;
        background: var(--revamp-surface);
        box-shadow: var(--revamp-shadow);
        overflow: hidden;
    }

    .stock-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .stock-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--revamp-ink);
    }

    .stock-panel__subtitle {
        margin: 0.28rem 0 0;
        color: var(--revamp-muted);
        font-size: 0.92rem;
    }

    .stock-panel__body {
        padding: 1.25rem;
    }

    .stock-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.95rem;
    }

    .stock-filter-grid .form-group {
        margin-bottom: 0;
    }

    .stock-filter-grid label,
    .stock-modal__section label {
        display: block;
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stock-filter-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .stock-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .stock-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .stock-card {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--revamp-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
        min-height: 190px;
    }

    .stock-card__body {
        padding: 1.2rem;
    }

    .stock-card__icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.1rem;
        color: #fff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14);
    }

    .stock-card__label {
        display: block;
        margin-top: 1rem;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stock-card__value {
        display: block;
        margin-top: 0.55rem;
        font-size: 1.55rem;
        line-height: 1.2;
        color: var(--revamp-ink);
        font-weight: 800;
    }

    .stock-card__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.1rem;
    }

    .stock-card__hint {
        color: #64748b;
        font-size: 0.9rem;
    }

    .stock-card__link {
        font-weight: 800;
        color: #0f172a;
        text-decoration: none;
    }

    .stock-card__link:hover {
        color: var(--revamp-blue);
        text-decoration: none;
    }

    .stock-card--blue .stock-card__icon { background: linear-gradient(135deg, #2563eb, #38bdf8); }
    .stock-card--teal .stock-card__icon { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .stock-card--amber .stock-card__icon { background: linear-gradient(135deg, #b45309, #f59e0b); }
    .stock-card--indigo .stock-card__icon { background: linear-gradient(135deg, #4338ca, #818cf8); }
    .stock-card--rose .stock-card__icon { background: linear-gradient(135deg, #be123c, #fb7185); }
    .stock-card--cyan .stock-card__icon { background: linear-gradient(135deg, #0891b2, #22d3ee); }
    .stock-card--violet .stock-card__icon { background: linear-gradient(135deg, #6d28d9, #a78bfa); }
    .stock-card--emerald .stock-card__icon { background: linear-gradient(135deg, #047857, #34d399); }
    .stock-card--slate .stock-card__icon { background: linear-gradient(135deg, #334155, #94a3b8); }

    .stock-grid-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .stock-table-shell {
        border: 1px solid var(--revamp-line);
        border-radius: 22px;
        background: var(--revamp-surface);
        box-shadow: var(--revamp-shadow);
        overflow: hidden;
    }

    .stock-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem 0;
    }

    .stock-table-wrap {
        padding: 1rem 1rem 1.1rem;
    }

    .stock-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .stock-table thead th {
        position: sticky;
        top: 0;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .stock-table th,
    .stock-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        white-space: nowrap;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .stock-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.7);
    }

    .stock-table tfoot th {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 800;
    }

    .stock-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 0.45rem;
        background: #3b82f6;
        box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.12);
    }

    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .stock-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }

    .stock-range {
        min-width: 250px;
    }

    .stock-history-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        justify-content: flex-end;
    }

    .stock-action-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 0;
    }

    .stock-action-btn--view {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .stock-action-btn--delete {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
    }

    .stock-modal .modal-dialog {
        max-width: 1180px;
    }

    .stock-modal .modal-content {
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 34px 80px rgba(15, 23, 42, 0.26);
    }

    .stock-modal .modal-header {
        padding: 1rem 1.2rem;
        color: #fff;
        border-bottom: 0;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 24%),
            linear-gradient(135deg, #0f172a, #1d4ed8);
    }

    .stock-modal .modal-title {
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .stock-modal .modal-body {
        padding: 1.2rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));
    }

    .stock-modal__section {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.84);
    }

    .stock-modal__section + .stock-modal__section {
        margin-top: 1rem;
    }

    .stock-modal__section-title {
        margin: 0 0 0.95rem;
        font-size: 0.96rem;
        font-weight: 800;
        color: #0f172a;
    }

    .stock-modal__tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .stock-tab-btn {
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 16px;
        padding: 0.95rem 1rem;
        background: #fff;
        text-align: left;
        font-weight: 800;
        color: #0f172a;
    }

    .stock-tab-btn span {
        display: block;
        margin-top: 0.3rem;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .stock-tab-btn.is-active {
        border-color: rgba(37, 99, 235, 0.28);
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.95), rgba(219, 234, 254, 0.92));
        box-shadow: 0 18px 36px rgba(37, 99, 235, 0.14);
    }

    .stock-export-pane {
        display: none;
    }

    .stock-export-pane.is-active {
        display: block;
    }

    .stock-hidden {
        display: none !important;
    }

    .stock-note {
        color: #64748b;
        font-size: 0.88rem;
    }

    .stock-empty {
        padding: 1.2rem;
        border-radius: 16px;
        text-align: center;
        background: rgba(248, 250, 252, 0.8);
        border: 1px dashed rgba(148, 163, 184, 0.36);
        color: #64748b;
    }

    @media (max-width: 1199.98px) {
        .stock-hero__grid,
        .stock-grid-two,
        .stock-filter-grid,
        .stock-cards {
            grid-template-columns: 1fr;
        }

        .stock-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .stock-shell {
            padding: 0.8rem;
        }

        .stock-hero__grid {
            padding: 1rem;
        }

        .stock-hero h1 {
            font-size: 1.5rem;
        }

        .stock-panel__head,
        .stock-toolbar,
        .stock-history-actions,
        .stock-filter-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .stock-metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper stock-revamp">
    <div class="content-header">
        <div class="container-fluid stock-shell">
            <section class="stock-hero">
                <div class="stock-hero__grid">
                    <div>
                        <span class="stock-hero__eyebrow">
                            <i class="fas fa-warehouse"></i>
                            Logistik Stock Intelligence
                        </span>
                        <h1>Dashboard stok dengan tampilan baru, flow tetap utuh.</h1>
                        <p>
                            Versi revamp ini tetap memakai sumber data, proses tambah stok, detail surat jalan, filter,
                            dan export yang sama. Fokusnya ada di pembacaan data yang lebih cepat, visual yang lebih rapi,
                            dan workflow harian yang lebih nyaman.
                        </p>
                        <div class="stock-hero__actions">
                            <button type="button" class="stock-btn stock-btn--light" data-toggle="modal"
                                data-target="#modalRevampTambahStok">
                                <i class="fas fa-plus-circle"></i>
                                Tambah Report Stok
                            </button>
                            <button type="button" class="stock-btn stock-btn--ghost" data-toggle="modal"
                                data-target="#modalRevampDownload">
                                <i class="fas fa-file-export"></i>
                                Download Report
                            </button>
                            <a href="<?= base_url('Dashboard_Logistik_Stok') ?>" class="stock-btn stock-btn--ghost">
                                <i class="fas fa-history"></i>
                                Buka Dashboard Lama
                            </a>
                        </div>
                    </div>

                    <div class="stock-metric-grid">
                        <div class="stock-metric">
                            <span class="stock-metric__label">Dokumen Stok</span>
                            <span class="stock-metric__value"><?= number_format($historyDocumentCount, 0, ',', '.') ?></span>
                            <span class="stock-metric__hint">Ringkasan surat jalan yang sudah masuk sistem.</span>
                        </div>
                        <div class="stock-metric">
                            <span class="stock-metric__label">Gudang Aktif</span>
                            <span class="stock-metric__value"><?= number_format($uniqueGudangCount, 0, ',', '.') ?></span>
                            <span class="stock-metric__hint">Lokasi gudang yang terhubung dengan akun saat ini.</span>
                        </div>
                        <div class="stock-metric">
                            <span class="stock-metric__label">Project</span>
                            <span class="stock-metric__value"><?= number_format($projectCount, 0, ',', '.') ?></span>
                            <span class="stock-metric__hint">Jumlah project yang terlibat dalam mutasi material.</span>
                        </div>
                        <div class="stock-metric">
                            <span class="stock-metric__label">Master Item</span>
                            <span class="stock-metric__value"><?= number_format($itemCount, 0, ',', '.') ?></span>
                            <span class="stock-metric__hint">Item material yang tersedia untuk transaksi stok.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success mt-3 mb-0"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-danger mt-3 mb-0"><?= $flashError ?></div>
            <?php endif; ?>

            <section class="stock-panel">
                <div class="stock-panel__head">
                    <div>
                        <span class="stock-chip"><i class="fas fa-sliders-h"></i> Filter Data</span>
                        <h2 class="stock-panel__title">Kontrol dashboard dan ringkasan area</h2>
                        <p class="stock-panel__subtitle">Pilih gudang, project, item, status stok, dan tanggal snapshot.</p>
                    </div>
                    <div class="stock-note">Filter ini mempengaruhi kartu total dan tabel regional-area.</div>
                </div>
                <div class="stock-panel__body">
                    <div class="stock-filter-grid">
                        <div class="form-group">
                            <label for="revamp_filter_lokasi">Lokasi Gudang</label>
                            <select id="revamp_filter_lokasi" class="form-control select2" multiple="multiple"
                                data-placeholder="Pilih gudang">
                                <?php foreach ($getListGudangLokasiUser as $data): ?>
                                    <option value="<?= $data['id_lokasi_gudang'] ?>"><?= $data['kota_lokasi_gudang'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="revamp_filter_bowheer">Project Bowheer</label>
                            <select id="revamp_filter_bowheer" class="form-control select2" multiple="multiple"
                                data-placeholder="Pilih project">
                                <?php foreach ($getUniqueProjectLogistik as $data): ?>
                                    <option value="<?= $data['nama_bowheer'] ?>"><?= $data['nama_bowheer'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="revamp_filter_item">Jenis Item</label>
                            <select id="revamp_filter_item" class="form-control select2" multiple="multiple"
                                data-placeholder="Pilih item">
                                <?php foreach ($getUniqueItemLogistik as $data): ?>
                                    <option value="<?= $data['nama_item'] ?>"><?= $data['nama_item'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="revamp_filter_status">Status Stok</label>
                            <select id="revamp_filter_status" class="form-control select2" multiple="multiple"
                                data-placeholder="Pilih status">
                                <?php foreach ($getUniqueSumberMaterial as $data): ?>
                                    <option value="<?= $data['nama_sumber_material'] ?>"><?= $data['nama_sumber_material'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="revamp_filter_tanggal">Tanggal Stok</label>
                            <input type="date" id="revamp_filter_tanggal" class="form-control">
                        </div>
                    </div>
                    <div class="stock-filter-actions">
                        <button type="button" id="revamp_reset_filter" class="stock-btn stock-btn--ghost text-dark">
                            <i class="fas fa-undo-alt"></i>
                            Reset Filter
                        </button>
                        <button type="button" id="revamp_apply_filter" class="stock-btn stock-btn--light">
                            <i class="fas fa-search"></i>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </section>

            <section class="stock-cards">
                <?php foreach ($categoryCards as $card): ?>
                    <article class="stock-card stock-card--<?= $card['tone'] ?>">
                        <div class="stock-card__body">
                            <span class="stock-card__icon"><i class="<?= $card['icon'] ?>"></i></span>
                            <span class="stock-card__label"><?= $card['label'] ?></span>
                            <span class="stock-card__value" id="stock-card-value-<?= $card['key'] ?>"><?= $card['formatted'] ?></span>
                            <div class="stock-card__footer">
                                <span class="stock-card__hint">Akumulasi stok aktif per kategori.</span>
                                <a href="#" class="stock-card__link revamp-category-link"
                                    data-category="<?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?>">
                                    Lihat detail
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="stock-grid-two">
                <div class="stock-table-shell">
                    <div class="stock-table-head">
                        <div>
                            <span class="stock-chip"><i class="fas fa-map-marked-alt"></i> Regional</span>
                            <h2 class="stock-panel__title">Distribusi stok per regional</h2>
                            <p class="stock-panel__subtitle">Ringkasan akumulasi stok per wilayah regional gudang.</p>
                        </div>
                    </div>
                    <div class="stock-table-wrap table-responsive">
                        <table class="stock-table table" id="revamp_table_regional">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Aksesories</th>
                                    <th>Closure</th>
                                    <th>FAT</th>
                                    <th>FDT</th>
                                    <th>HDPE</th>
                                    <th>Kabel</th>
                                    <th>OTB</th>
                                    <th>Tiang</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($getAllStokByKategoryFilterRegional as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><span class="stock-dot"></span><?= $data['regional_lokasi_gudang'] ?></td>
                                        <td><?= $data['jumlah_Aksesories'] == 0 ? '-' : number_format((float) $data['jumlah_Aksesories'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Closure'] == 0 ? '-' : number_format((float) $data['jumlah_Closure'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_FAT'] == 0 ? '-' : number_format((float) $data['jumlah_FAT'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_FDT'] == 0 ? '-' : number_format((float) $data['jumlah_FDT'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_HDPE'] == 0 ? '-' : number_format((float) $data['jumlah_HDPE'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Kabel'] == 0 ? '-' : number_format((float) $data['jumlah_Kabel'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_OTB'] == 0 ? '-' : number_format((float) $data['jumlah_OTB'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Tiang'] == 0 ? '-' : number_format((float) $data['jumlah_Tiang'], 0, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= site_url('Logistik_Stok_Detail/detail/' . $data['regional_lokasi_gudang']) ?>"
                                                class="stock-action-btn stock-action-btn--view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th><?= number_format($regionalTotals['jumlah_Aksesories'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_Closure'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_FAT'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_FDT'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_HDPE'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_Kabel'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_OTB'], 0, ',', '.') ?></th>
                                    <th><?= number_format($regionalTotals['jumlah_Tiang'], 0, ',', '.') ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="stock-table-shell">
                    <div class="stock-table-head">
                        <div>
                            <span class="stock-chip"><i class="fas fa-city"></i> Area</span>
                            <h2 class="stock-panel__title">Sebaran stok per area gudang</h2>
                            <p class="stock-panel__subtitle">Perbandingan stok antarkota untuk membantu pembacaan distribusi.</p>
                        </div>
                    </div>
                    <div class="stock-table-wrap table-responsive">
                        <table class="stock-table table" id="revamp_table_area">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Lokasi Gudang</th>
                                    <th>Aksesories</th>
                                    <th>Closure</th>
                                    <th>FAT</th>
                                    <th>FDT</th>
                                    <th>HDPE</th>
                                    <th>Kabel</th>
                                    <th>OTB</th>
                                    <th>Tiang</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($getAllStokByKategoryFilterCity as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $data['regional_lokasi_gudang'] ?></td>
                                        <td><?= $data['kota_lokasi_gudang'] ?></td>
                                        <td><?= $data['jumlah_Aksesories'] == 0 ? '-' : number_format((float) $data['jumlah_Aksesories'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Closure'] == 0 ? '-' : number_format((float) $data['jumlah_Closure'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_FAT'] == 0 ? '-' : number_format((float) $data['jumlah_FAT'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_FDT'] == 0 ? '-' : number_format((float) $data['jumlah_FDT'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_HDPE'] == 0 ? '-' : number_format((float) $data['jumlah_HDPE'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Kabel'] == 0 ? '-' : number_format((float) $data['jumlah_Kabel'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_OTB'] == 0 ? '-' : number_format((float) $data['jumlah_OTB'], 0, ',', '.') ?></td>
                                        <td><?= $data['jumlah_Tiang'] == 0 ? '-' : number_format((float) $data['jumlah_Tiang'], 0, ',', '.') ?></td>
                                        <td>
                                            <a href="<?= site_url('Logistik_Stok_Detail/detail/' . $data['kota_lokasi_gudang']) ?>"
                                                class="stock-action-btn stock-action-btn--view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th id="revamp_total_area_aksesories"><?= number_format($areaTotals['jumlah_Aksesories'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_closure"><?= number_format($areaTotals['jumlah_Closure'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_fat"><?= number_format($areaTotals['jumlah_FAT'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_fdt"><?= number_format($areaTotals['jumlah_FDT'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_hdpe"><?= number_format($areaTotals['jumlah_HDPE'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_kabel"><?= number_format($areaTotals['jumlah_Kabel'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_otb"><?= number_format($areaTotals['jumlah_OTB'], 0, ',', '.') ?></th>
                                    <th id="revamp_total_area_tiang"><?= number_format($areaTotals['jumlah_Tiang'], 0, ',', '.') ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <section class="stock-panel">
                <div class="stock-panel__head">
                    <div>
                        <span class="stock-chip"><i class="fas fa-scroll"></i> Histori</span>
                        <h2 class="stock-panel__title">Ledger surat jalan dan mutasi stok</h2>
                        <p class="stock-panel__subtitle">Gunakan date range untuk menelusuri histori laporan stok yang sudah masuk.</p>
                    </div>
                    <div class="stock-history-actions">
                        <button type="button" class="stock-btn stock-btn--ghost text-dark" id="revamp_reset_history_date">
                            <i class="fas fa-undo-alt"></i>
                            Hapus Range
                        </button>
                        <button type="button" class="stock-btn stock-btn--light" data-toggle="modal"
                            data-target="#modalRevampTambahStok">
                            <i class="fas fa-plus"></i>
                            Tambah Data
                        </button>
                    </div>
                </div>
                <div class="stock-panel__body">
                    <div class="stock-toolbar mb-3">
                        <div class="stock-range">
                            <label for="revamp_date_range">Rentang Tanggal Histori</label>
                            <input type="text" class="form-control" id="revamp_date_range"
                                placeholder="Pilih rentang tanggal">
                        </div>
                        <span class="stock-note">Date range ini memfilter tabel histori tanpa mengubah ringkasan dashboard.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table stock-table" id="revamp_table_history">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Lokasi</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>No SJ</th>
                                    <th>QTY</th>
                                    <th>PIC</th>
                                    <th>Tanggal Surat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($getAllStokLogistik as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $data['regional_lokasi_gudang'] ?></td>
                                        <td><?= $data['kota_lokasi_gudang'] ?></td>
                                        <td><?= $data['nama_bowheer'] ?></td>
                                        <td><span class="stock-badge"><?= $data['nama_sumber_material'] ?></span></td>
                                        <td><?= $data['no_surat_jalan'] ?></td>
                                        <td><?= number_format((float) $data['total_jumlah_stok'], 0, ',', '.') ?></td>
                                        <td><?= $data['nama_user'] ?></td>
                                        <td><?= $data['tanggal_upload_stok'] ?></td>
                                        <td>
                                            <div class="stock-history-actions">
                                                <?php if ($this->session->userdata('nama_level') == 'Super Admin'): ?>
                                                    <a href="<?= site_url('Dashboard_Logistik_Stok/hapusReportStokLogistik/' . urlencode($data['no_surat_jalan']) . '?id_lokasi_gudang=' . urlencode($data['id_lokasi_gudang'])) ?>"
                                                        class="stock-action-btn stock-action-btn--delete revamp-delete-history">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button"
                                                    class="stock-action-btn stock-action-btn--view revamp-detail-trigger"
                                                    data-suratjalan="<?= htmlspecialchars($data['surat_jalan'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<form method="post" id="revamp_form_tambah_stok"
    action="<?= site_url('Dashboard_Logistik_Stok/tambahReportStokLogistik') ?>" enctype="multipart/form-data">
    <div class="modal fade stock-modal" id="modalRevampTambahStok" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Report Stok Logistik</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="stock-modal__section">
                        <h5 class="stock-modal__section-title">Informasi dokumen</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="revamp_nomor_surat_jalan">Nomor Surat Jalan</label>
                                <input type="text" class="form-control" name="nomor_surat_jalan"
                                    id="revamp_nomor_surat_jalan" autocomplete="off"
                                    placeholder="TEC.005/TKM-04/SJ/II/2025" required>
                            </div>
                            <div class="col-md-4">
                                <label for="revamp_tanggal_upload_stok">Tanggal Surat</label>
                                <input type="date" class="form-control" name="tanggal_upload_stok"
                                    id="revamp_tanggal_upload_stok" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="revamp_tanggal_pembuatan_stok">Tanggal Input</label>
                                <input type="date" class="form-control" name="tanggal_pembuatan_stok"
                                    id="revamp_tanggal_pembuatan_stok" value="<?= date('Y-m-d') ?>" readonly>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="revamp_id_lokasi_gudang">Area Gudang</label>
                                <select name="id_lokasi_gudang" id="revamp_id_lokasi_gudang" class="form-control">
                                    <option value="">Pilih salah satu</option>
                                    <?php foreach ($getListGudangLokasiUser as $data2): ?>
                                        <option value="<?= $data2['id_lokasi_gudang'] ?>"><?= $data2['kota_lokasi_gudang'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="revamp_id_project">Project</label>
                                <select name="id_bowheer" id="revamp_id_project" class="form-control">
                                    <option value="">Pilih salah satu</option>
                                    <?php foreach ($getMasterProject as $data2): ?>
                                        <option value="<?= $data2['id_bowheer'] ?>"
                                            data-id-bowheer="<?= $data2['nama_bowheer'] ?>"><?= $data2['nama_bowheer'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="revamp_id_sumber_material">Sumber Material</label>
                                <select name="id_sumber_material" id="revamp_id_sumber_material" class="form-control">
                                    <option value="">Pilih salah satu</option>
                                    <?php foreach ($getMasterSumberMaterial as $data2): ?>
                                        <option value="<?= $data2['id_sumber_material'] ?>">
                                            <?= $data2['status_sumber_material'] ?> - <?= $data2['nama_sumber_material'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3 stock-hidden" id="revamp_ho_in_nomor_po">
                                <label for="revamp_no_po_logistik">Nomor PO</label>
                                <input type="text" class="form-control" name="no_po_logistik"
                                    id="revamp_no_po_logistik" autocomplete="off"
                                    placeholder="TEC.005/TKM-04/PO/MDN/II/2025">
                            </div>
                            <div class="col-md-6 mt-3 stock-hidden" id="revamp_ho_out_nomor_pr">
                                <label for="revamp_no_pr_logistik">Nomor PR</label>
                                <input type="text" class="form-control" name="no_pr_logistik"
                                    id="revamp_no_pr_logistik" autocomplete="off"
                                    placeholder="TEC.005/TKM-04/PR/MDN/II/2025">
                            </div>
                            <div class="col-md-6 mt-3 stock-hidden" id="revamp_ho_out_lokasi_pengiriman">
                                <label for="revamp_id_lokasi_gudang_pengiriman">Lokasi Gudang Pengiriman</label>
                                <select name="id_lokasi_gudang_pengiriman" id="revamp_id_lokasi_gudang_pengiriman"
                                    class="form-control">
                                    <option value="">Pilih salah satu</option>
                                    <?php foreach ($getListGudangLokasiUser as $data2): ?>
                                        <option value="<?= $data2['id_lokasi_gudang'] ?>"><?= $data2['kota_lokasi_gudang'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="stock-modal__section">
                        <h5 class="stock-modal__section-title">Material dan rincian item</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="revamp_id_kode_item">Jenis Material</label>
                                <select class="form-control" id="revamp_id_kode_item">
                                    <option value="">Pilih jenis material</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-3 table-responsive">
                                <table class="table stock-table" id="revamp_table_item_stok">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Qty</th>
                                            <th>Satuan</th>
                                            <th>Merk Item</th>
                                            <th>No Haspel</th>
                                            <th>No Ref</th>
                                            <th>Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="revamp_keterangan_stok_item">Keterangan</label>
                                <textarea class="form-control" name="keterangan_stok" id="revamp_keterangan_stok_item"
                                    rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="stock-modal__section">
                        <h5 class="stock-modal__section-title">Dokumen pendukung</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="revamp_file_sj">Upload Surat Jalan</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="revamp_file_sj" name="file-sj" required>
                                    <label class="custom-file-label" for="revamp_file_sj">Choose file</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="revamp_file_evidence">Upload Evidence</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="revamp_file_evidence" name="file-evidence" required>
                                    <label class="custom-file-label" for="revamp_file_evidence">Choose file</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="stock-btn stock-btn--ghost text-dark" data-dismiss="modal">Batal</button>
                    <button type="submit" class="stock-btn stock-btn--light" id="revamp_btn_submit_stok">
                        <i class="fas fa-save"></i>
                        Simpan Report Stok
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade stock-modal" id="modalRevampDetailStok" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Report Stok Logistik</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="stock-modal__section">
                    <h5 class="stock-modal__section-title">Ringkasan dokumen</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Nomor Surat Jalan</label>
                            <input type="text" class="form-control" id="revamp_detail_no_surat_jalan" disabled>
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal Surat</label>
                            <input type="date" class="form-control" id="revamp_detail_tanggal_upload_stok" disabled>
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal Input</label>
                            <input type="date" class="form-control" id="revamp_detail_tanggal_pembuatan_stok" disabled>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label>Area Gudang</label>
                            <input type="text" class="form-control" id="revamp_detail_area_gudang" disabled>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label>Project</label>
                            <input type="text" class="form-control" id="revamp_detail_nama_project" disabled>
                        </div>
                        <div class="col-md-4 mt-3">
                            <label>Sumber Material</label>
                            <input type="text" class="form-control" id="revamp_detail_sumber_material" disabled>
                        </div>
                        <div class="col-md-6 mt-3 stock-hidden" id="revamp_detail_ho_in_nomor_po">
                            <label>Nomor PO</label>
                            <input type="text" class="form-control" id="revamp_detail_no_po_logistik" disabled>
                        </div>
                        <div class="col-md-6 mt-3 stock-hidden" id="revamp_detail_ho_out_nomor_pr">
                            <label>Nomor PR</label>
                            <input type="text" class="form-control" id="revamp_detail_no_pr_logistik" disabled>
                        </div>
                    </div>
                </div>

                <div class="stock-modal__section">
                    <h5 class="stock-modal__section-title">Rincian item</h5>
                    <div class="table-responsive">
                        <table class="table stock-table" id="revamp_table_detail_item">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Merk</th>
                                    <th>No Haspel</th>
                                    <th>No Ref</th>
                                </tr>
                            </thead>
                            <tbody id="revamp_detail_items_body"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th id="revamp_detail_total_qty">0</th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="stock-modal__section">
                    <h5 class="stock-modal__section-title">Dokumen dan catatan</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Keterangan</label>
                            <textarea class="form-control" id="revamp_detail_keterangan_stok_item" rows="4" disabled></textarea>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label>Surat Jalan</label>
                            <div class="stock-empty text-left">
                                <strong id="revamp_detail_nama_file_sj">No file</strong><br>
                                <a href="#" id="revamp_view_detail_surat_jalan" target="_blank">Buka file</a>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3" id="revamp_container_detail_evidence">
                            <label>Evidence</label>
                            <div class="stock-empty text-left">
                                <strong id="revamp_detail_nama_file_evidence">No file</strong><br>
                                <a href="#" id="revamp_view_detail_evidence" target="_blank">Buka file</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="stock-btn stock-btn--light" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade stock-modal" id="modalRevampDownload" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Download Report Logistik</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="stock-modal__tabs">
                    <button type="button" class="stock-tab-btn is-active" data-pane="inout">
                        Report IN OUT
                        <span>Ekspor histori mutasi material lengkap.</span>
                    </button>
                    <button type="button" class="stock-tab-btn" data-pane="stok">
                        Report STOK
                        <span>Ekspor snapshot stok berdasarkan tanggal.</span>
                    </button>
                </div>

                <div class="stock-export-pane is-active" data-pane="inout">
                    <div class="stock-modal__section">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="revamp_report_in_out_regional_gudang">Regional Gudang</label>
                                <select id="revamp_report_in_out_regional_gudang" class="form-control">
                                    <option value="">Pilih regional</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="revamp_report_in_out_lokasi_gudang">Lokasi Gudang</label>
                                <select id="revamp_report_in_out_lokasi_gudang" class="form-control">
                                    <option value="">Pilih kota</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_in_out_nama_bowheer">Bowheer</label>
                                <select id="revamp_report_in_out_nama_bowheer" class="form-control">
                                    <option value="">Pilih bowheer</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_in_out_kategori_item">Kategori Item</label>
                                <select id="revamp_report_in_out_kategori_item" class="form-control">
                                    <option value="">Pilih kategori</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_in_out_nama_item">Nama Item</label>
                                <select id="revamp_report_in_out_nama_item" class="form-control">
                                    <option value="">Pilih item</option>
                                </select>
                            </div>
                            <div class="col-md-3 mt-3">
                                <label for="revamp_report_in_out_date_start">Tanggal Start</label>
                                <input type="date" id="revamp_report_in_out_date_start" class="form-control">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label for="revamp_report_in_out_date_end">Tanggal End</label>
                                <input type="date" id="revamp_report_in_out_date_end" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="stock-filter-actions">
                        <button type="button" class="stock-btn stock-btn--light" id="revamp_download_in_out">
                            <i class="fas fa-download"></i>
                            Download Excel
                        </button>
                    </div>
                </div>

                <div class="stock-export-pane" data-pane="stok">
                    <div class="stock-modal__section">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="revamp_report_stok_regional_gudang">Regional Gudang</label>
                                <select id="revamp_report_stok_regional_gudang" class="form-control">
                                    <option value="">Pilih regional</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="revamp_report_stok_lokasi_gudang">Lokasi Gudang</label>
                                <select id="revamp_report_stok_lokasi_gudang" class="form-control">
                                    <option value="">Pilih kota</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_stok_nama_bowheer">Bowheer</label>
                                <select id="revamp_report_stok_nama_bowheer" class="form-control">
                                    <option value="">Pilih bowheer</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_stok_kategori_item">Kategori Item</label>
                                <select id="revamp_report_stok_kategori_item" class="form-control">
                                    <option value="">Pilih kategori</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_stok_nama_item">Nama Item</label>
                                <select id="revamp_report_stok_nama_item" class="form-control">
                                    <option value="">Pilih item</option>
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="revamp_report_stok_date">Tanggal Stok</label>
                                <input type="date" id="revamp_report_stok_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="stock-filter-actions">
                        <button type="button" class="stock-btn stock-btn--light" id="revamp_download_stok">
                            <i class="fas fa-download"></i>
                            Download Report
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="stock-btn stock-btn--ghost text-dark" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<script>
    (function() {
        const categoryCards = <?= json_encode($categoryCards) ?>;
        const categoryTotalsMap = <?= json_encode($dashboardTotalsMap) ?>;
        const initialAreaRows = <?= json_encode($getAllStokByKategoryFilterCity) ?>;
        const initialRegionalRows = <?= json_encode($getAllStokByKategoryFilterRegional) ?>;
        const initialHistoryRows = <?= json_encode($getAllStokLogistik) ?>;
        const inOutReportRows = <?= json_encode($getReportInOutMaterial, JSON_PRETTY_PRINT) ?>;
        let stokReportRows = <?= json_encode($getReportStokMaterial, JSON_PRETTY_PRINT) ?>;

        let historyTable = null;
        let regionalTable = null;
        let areaTable = null;
        let currentAreaRows = initialAreaRows.slice();
        let currentRegionalRows = initialRegionalRows.slice();
        let currentDrilldown = {
            rincian: [],
            rincianBowheer: [],
            history: []
        };
        let currentHistoryDateFilter = {
            start: null,
            end: null
        };
        let currentTambahCounter = 1;
        let isRevampFilterActive = false;

        const categoryColumnMap = {
            jumlah_Aksesories: 'aksesories',
            jumlah_Closure: 'closure',
            jumlah_FAT: 'fat-odp',
            jumlah_FDT: 'fdt-odc',
            jumlah_HDPE: 'hdpe',
            jumlah_Kabel: 'kabel',
            jumlah_OTB: 'otb',
            jumlah_Tiang: 'tiang'
        };

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
        }

        function formatCardValue(value, unit) {
            const suffix = unit ? ` ${unit}` : '';
            return `${formatNumber(value)}${suffix}`;
        }

        function sanitizeText(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function toSqlList(values) {
            return values.length ? `"${values.join('", "')}"` : '';
        }

        function getRegionalHref(regionName) {
            return `<?= site_url('Logistik_Stok_Detail/detail_revamp/') ?>${encodeURIComponent(regionName)}`;
        }

        function getAreaHref(cityName) {
            return `<?= site_url('Logistik_Stok_Detail/detail_revamp/') ?>${encodeURIComponent(cityName)}`;
        }

        function parseNumericCell(value) {
            if (value === null || value === undefined || value === '' || value === '-') {
                return 0;
            }
            return Number(String(value).replace(/\./g, '').replace(/[^0-9-]/g, '')) || 0;
        }

        function aggregateRegionalRows(areaRows) {
            const grouped = {};

            areaRows.forEach((row) => {
                const region = row.regional_lokasi_gudang || '-';
                if (!grouped[region]) {
                    grouped[region] = {
                        regional_lokasi_gudang: region,
                        jumlah_Aksesories: 0,
                        jumlah_Closure: 0,
                        jumlah_FAT: 0,
                        jumlah_FDT: 0,
                        jumlah_HDPE: 0,
                        jumlah_Kabel: 0,
                        jumlah_OTB: 0,
                        jumlah_Tiang: 0
                    };
                }

                Object.keys(categoryColumnMap).forEach((key) => {
                    grouped[region][key] += Number(row[key] || 0);
                });
            });

            return Object.values(grouped).sort((a, b) => String(a.regional_lokasi_gudang).localeCompare(String(b.regional_lokasi_gudang)));
        }

        function updateCategoryCardsFromMap(totalMap) {
            categoryCards.forEach((card) => {
                const source = totalMap[card.key] || {
                    value: 0,
                    unit: card.unit || ''
                };
                $(`#stock-card-value-${card.key}`).text(formatCardValue(source.value || 0, source.unit || card.unit || ''));
            });
        }

        function updateCategoryCardsFromDashboardRows(rows) {
            const totals = {
                'aksesories': 0,
                'closure': 0,
                'fat-odp': 0,
                'fdt-odc': 0,
                'hdpe': 0,
                'kabel': 0,
                'otb': 0,
                'tiang': 0
            };

            rows.forEach((row) => {
                totals['aksesories'] += Number(row.jumlah_Aksesories || 0);
                totals['closure'] += Number(row.jumlah_Closure || 0);
                totals['fat-odp'] += Number(row.jumlah_FAT || 0);
                totals['fdt-odc'] += Number(row.jumlah_FDT || 0);
                totals['hdpe'] += Number(row.jumlah_HDPE || 0);
                totals['kabel'] += Number(row.jumlah_Kabel || 0);
                totals['otb'] += Number(row.jumlah_OTB || 0);
                totals['tiang'] += Number(row.jumlah_Tiang || 0);
            });

            categoryCards.forEach((card) => {
                $(`#stock-card-value-${card.key}`).text(formatCardValue(totals[card.key] || 0, card.unit || ''));
            });
        }

        function rebuildRegionalTable(rows) {
            if ($.fn.DataTable.isDataTable('#revamp_table_regional')) {
                $('#revamp_table_regional').DataTable().destroy();
            }

            const tbody = $('#revamp_table_regional tbody');
            const tfoot = $('#revamp_table_regional tfoot');
            tbody.empty();

            let totals = {
                jumlah_Aksesories: 0,
                jumlah_Closure: 0,
                jumlah_FAT: 0,
                jumlah_FDT: 0,
                jumlah_HDPE: 0,
                jumlah_Kabel: 0,
                jumlah_OTB: 0,
                jumlah_Tiang: 0
            };

            if (!rows.length) {
                tbody.append('<tr><td colspan="11" class="text-center">Tidak ada data regional.</td></tr>');
            } else {
                rows.forEach((row, index) => {
                    Object.keys(totals).forEach((key) => {
                        totals[key] += Number(row[key] || 0);
                    });

                    tbody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td><span class="stock-dot"></span>${sanitizeText(row.regional_lokasi_gudang || '-')}</td>
                            <td>${Number(row.jumlah_Aksesories || 0) === 0 ? '-' : formatNumber(row.jumlah_Aksesories)}</td>
                            <td>${Number(row.jumlah_Closure || 0) === 0 ? '-' : formatNumber(row.jumlah_Closure)}</td>
                            <td>${Number(row.jumlah_FAT || 0) === 0 ? '-' : formatNumber(row.jumlah_FAT)}</td>
                            <td>${Number(row.jumlah_FDT || 0) === 0 ? '-' : formatNumber(row.jumlah_FDT)}</td>
                            <td>${Number(row.jumlah_HDPE || 0) === 0 ? '-' : formatNumber(row.jumlah_HDPE)}</td>
                            <td>${Number(row.jumlah_Kabel || 0) === 0 ? '-' : formatNumber(row.jumlah_Kabel)}</td>
                            <td>${Number(row.jumlah_OTB || 0) === 0 ? '-' : formatNumber(row.jumlah_OTB)}</td>
                            <td>${Number(row.jumlah_Tiang || 0) === 0 ? '-' : formatNumber(row.jumlah_Tiang)}</td>
                            <td>
                                <a href="${getRegionalHref(row.regional_lokasi_gudang || '')}" class="stock-action-btn stock-action-btn--view">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `);
                });
            }

            tfoot.html(`
                <tr>
                    <th colspan="2">Total</th>
                    <th>${formatNumber(totals.jumlah_Aksesories)}</th>
                    <th>${formatNumber(totals.jumlah_Closure)}</th>
                    <th>${formatNumber(totals.jumlah_FAT)}</th>
                    <th>${formatNumber(totals.jumlah_FDT)}</th>
                    <th>${formatNumber(totals.jumlah_HDPE)}</th>
                    <th>${formatNumber(totals.jumlah_Kabel)}</th>
                    <th>${formatNumber(totals.jumlah_OTB)}</th>
                    <th>${formatNumber(totals.jumlah_Tiang)}</th>
                    <th></th>
                </tr>
            `);

            regionalTable = $('#revamp_table_regional').DataTable({
                paging: true,
                pageLength: 8,
                searching: true,
                info: false,
                lengthChange: false,
                order: []
            });
        }

        function rebuildAreaTable(rows) {
            if ($.fn.DataTable.isDataTable('#revamp_table_area')) {
                $('#revamp_table_area').DataTable().destroy();
            }

            const tbody = $('#revamp_table_area tbody');
            tbody.empty();

            let totals = {
                jumlah_Aksesories: 0,
                jumlah_Closure: 0,
                jumlah_FAT: 0,
                jumlah_FDT: 0,
                jumlah_HDPE: 0,
                jumlah_Kabel: 0,
                jumlah_OTB: 0,
                jumlah_Tiang: 0
            };

            if (!rows.length) {
                tbody.append('<tr><td colspan="12" class="text-center">Tidak ada data area.</td></tr>');
            } else {
                rows.forEach((row, index) => {
                    Object.keys(totals).forEach((key) => {
                        totals[key] += Number(row[key] || 0);
                    });

                    tbody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${sanitizeText(row.regional_lokasi_gudang || '-')}</td>
                            <td>${sanitizeText(row.kota_lokasi_gudang || '-')}</td>
                            <td>${Number(row.jumlah_Aksesories || 0) === 0 ? '-' : formatNumber(row.jumlah_Aksesories)}</td>
                            <td>${Number(row.jumlah_Closure || 0) === 0 ? '-' : formatNumber(row.jumlah_Closure)}</td>
                            <td>${Number(row.jumlah_FAT || 0) === 0 ? '-' : formatNumber(row.jumlah_FAT)}</td>
                            <td>${Number(row.jumlah_FDT || 0) === 0 ? '-' : formatNumber(row.jumlah_FDT)}</td>
                            <td>${Number(row.jumlah_HDPE || 0) === 0 ? '-' : formatNumber(row.jumlah_HDPE)}</td>
                            <td>${Number(row.jumlah_Kabel || 0) === 0 ? '-' : formatNumber(row.jumlah_Kabel)}</td>
                            <td>${Number(row.jumlah_OTB || 0) === 0 ? '-' : formatNumber(row.jumlah_OTB)}</td>
                            <td>${Number(row.jumlah_Tiang || 0) === 0 ? '-' : formatNumber(row.jumlah_Tiang)}</td>
                            <td>
                                <a href="${getAreaHref(row.kota_lokasi_gudang || '')}" class="stock-action-btn stock-action-btn--view">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `);
                });
            }

            $('#revamp_total_area_aksesories').text(formatNumber(totals.jumlah_Aksesories));
            $('#revamp_total_area_closure').text(formatNumber(totals.jumlah_Closure));
            $('#revamp_total_area_fat').text(formatNumber(totals.jumlah_FAT));
            $('#revamp_total_area_fdt').text(formatNumber(totals.jumlah_FDT));
            $('#revamp_total_area_hdpe').text(formatNumber(totals.jumlah_HDPE));
            $('#revamp_total_area_kabel').text(formatNumber(totals.jumlah_Kabel));
            $('#revamp_total_area_otb').text(formatNumber(totals.jumlah_OTB));
            $('#revamp_total_area_tiang').text(formatNumber(totals.jumlah_Tiang));

            areaTable = $('#revamp_table_area').DataTable({
                paging: true,
                pageLength: 8,
                searching: true,
                info: false,
                lengthChange: false,
                order: []
            });
        }

        function initHistoryTable() {
            $.fn.dataTable.ext.search.push(function(settings, data) {
                if (settings.nTable.id !== 'revamp_table_history') {
                    return true;
                }

                if (!currentHistoryDateFilter.start || !currentHistoryDateFilter.end) {
                    return true;
                }

                const dateText = data[8] || '';
                const rowMoment = moment(dateText, 'YYYY-MM-DD HH:mm:ss');
                if (!rowMoment.isValid()) {
                    return true;
                }

                return rowMoment.isBetween(currentHistoryDateFilter.start, currentHistoryDateFilter.end, undefined, '[]');
            });

            historyTable = $('#revamp_table_history').DataTable({
                responsive: false,
                pageLength: 10,
                order: [[8, 'desc']]
            });
        }

        function applyHistoryFilterOnly() {
            if (!historyTable) {
                return;
            }

            const lokasiTexts = $('#revamp_filter_lokasi').select2('data').map((item) => item.text.trim());
            const selectBowheer = $('#revamp_filter_bowheer').val() || [];
            const selectStatus = $('#revamp_filter_status').val() || [];

            historyTable
                .column(2).search(lokasiTexts.length ? lokasiTexts.join('|') : '', true, false)
                .column(3).search(selectBowheer.length ? selectBowheer.join('|') : '', true, false)
                .column(4).search(selectStatus.length ? selectStatus.join('|') : '', true, false)
                .draw();
        }

        function resetDashboardVisuals() {
            isRevampFilterActive = false;
            currentAreaRows = initialAreaRows.slice();
            currentRegionalRows = initialRegionalRows.slice();
            currentDrilldown = {
                rincian: [],
                rincianBowheer: [],
                history: []
            };

            updateCategoryCardsFromMap(categoryTotalsMap);
            rebuildRegionalTable(currentRegionalRows);
            rebuildAreaTable(currentAreaRows);
        }

        function applyDashboardFilter() {
            const lokasiValues = $('#revamp_filter_lokasi').val() || [];
            const lokasiTexts = $('#revamp_filter_lokasi').select2('data').map((item) => item.text.trim());
            const bowheerValues = $('#revamp_filter_bowheer').val() || [];
            const itemValues = $('#revamp_filter_item').val() || [];
            const statusValues = $('#revamp_filter_status').val() || [];
            const tanggal = $('#revamp_filter_tanggal').val();

            applyHistoryFilterOnly();

            if (!lokasiValues.length && !bowheerValues.length && !itemValues.length && !statusValues.length && !tanggal) {
                resetDashboardVisuals();
                return;
            }

            Swal.fire({
                title: 'Loading...',
                text: 'Mohon tunggu, data sedang diproses.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "<?= base_url('Dashboard_Logistik_Stok/filterDashboardLogistik') ?>",
                method: "POST",
                dataType: "json",
                data: {
                    lokasi: JSON.stringify(toSqlList(lokasiValues)),
                    bowheer: JSON.stringify(toSqlList(bowheerValues)),
                    item: JSON.stringify(toSqlList(itemValues)),
                    status: JSON.stringify(toSqlList(statusValues)),
                    tanggal: tanggal
                },
                success: function(response) {
                    Swal.close();
                    isRevampFilterActive = true;

                    currentDrilldown = {
                        rincian: response.getRincianDashboardFiltered || [],
                        rincianBowheer: response.getRincianDashboardFilteredBowheer || [],
                        history: response.getInOutHistoryFiltered || []
                    };

                    currentAreaRows = response.getAllStokByKategoryFilterCityFiltered || [];
                    currentRegionalRows = aggregateRegionalRows(currentAreaRows);

                    updateCategoryCardsFromDashboardRows(response.getDashboardFiltered || []);
                    rebuildRegionalTable(currentRegionalRows);
                    rebuildAreaTable(currentAreaRows);
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal memuat filter',
                        text: 'Terjadi kesalahan saat mengambil data dashboard.'
                    });
                    console.error(xhr.responseText);
                }
            });
        }

        function resetRevampFilters() {
            $('#revamp_filter_lokasi, #revamp_filter_bowheer, #revamp_filter_item, #revamp_filter_status').val(null).trigger('change');
            $('#revamp_filter_tanggal').val('');
            applyHistoryFilterOnly();
            resetDashboardVisuals();
        }

        function bindCategoryLinks() {
            $('.revamp-category-link').on('click', function(e) {
                e.preventDefault();
                const kategoriItem = $(this).data('category');

                if (!isRevampFilterActive) {
                    window.location.href = "<?= base_url('Logistik_Stok_Detail/detail_kategori_revamp/') ?>" + encodeURIComponent(kategoriItem);
                    return;
                }

                $.post("<?= base_url('Logistik_Stok_Detail/filter_kategori') ?>", {
                    rincianData: JSON.stringify(currentDrilldown.rincian || []),
                    rincianData2: JSON.stringify(currentDrilldown.rincianBowheer || []),
                    rincianData3: JSON.stringify(currentDrilldown.history || []),
                    kategori_item: kategoriItem
                }, function() {
                    window.location.href = "<?= base_url('Logistik_Stok_Detail/filter_kategori_rdr_revamp') ?>?kategori=" + encodeURIComponent(kategoriItem);
                });
            });
        }

        function initTambahModal() {
            $('#modalRevampTambahStok').on('shown.bs.modal', function() {
                $('#revamp_id_kode_item').select2({
                    placeholder: "Pilih jenis material",
                    allowClear: true,
                    dropdownParent: $('#modalRevampTambahStok')
                });
            });

            $('#modalRevampTambahStok').on('hidden.bs.modal', function() {
                $('#revamp_form_tambah_stok')[0].reset();
                $('#revamp_table_item_stok tbody').empty();
                $('#revamp_id_project, #revamp_id_lokasi_gudang, #revamp_id_sumber_material').val('');
                $('#revamp_id_kode_item').empty().append('<option value="">Pilih jenis material</option>').trigger('change');
                $('#revamp_btn_submit_stok').prop('disabled', false);
                $('.custom-file-label').text('Choose file');
                currentTambahCounter = 1;
                toggleHoFields();
            });

            $('#revamp_id_project').on('change', function() {
                $('#revamp_table_item_stok tbody').empty();
                currentTambahCounter = 1;

                const bowheerName = $(this).find(':selected').data('id-bowheer');
                if (!bowheerName) {
                    $('#revamp_id_kode_item').empty().append('<option value="">Pilih jenis material</option>').trigger('change');
                    return;
                }

                $.ajax({
                    url: "<?= base_url('Dashboard_Logistik_Stok/getProjectByBowheer') ?>",
                    type: "GET",
                    dataType: "json",
                    data: { id_bowheer: bowheerName.toString() },
                    success: function(response) {
                        const $select = $('#revamp_id_kode_item');
                        $select.empty().append('<option value="">Pilih jenis material</option>');

                        $.each(response, function(_, project) {
                            $select.append(`<option value="${project.id_kode_item}" data-satuan-item="${project.satuan_item}">${sanitizeText(project.nama_item)} - ${sanitizeText(project.nama_bowheer)}</option>`);
                        });
                        $select.trigger('change');
                    }
                });
            });

            $('#revamp_id_kode_item').on('change', function() {
                const selectedValue = $(this).val();
                if (!selectedValue) {
                    return;
                }

                const selectedText = $('#revamp_id_kode_item option:selected').text();
                const selectedSatuan = $('#revamp_id_kode_item option:selected').data('satuan-item') || '';

                $('#revamp_table_item_stok tbody').append(`
                    <tr>
                        <td>${currentTambahCounter}</td>
                        <td>
                            <input type="hidden" name="id_kode_item[${currentTambahCounter}]" value="${selectedValue}">
                            ${sanitizeText(selectedText)}
                        </td>
                        <td><input type="text" class="form-control revamp-qty-input" name="jumlah_stok[${currentTambahCounter}]" placeholder="1.000" required></td>
                        <td><input type="text" class="form-control" name="satuan_stok[${currentTambahCounter}]" value="${sanitizeText(selectedSatuan)}" readonly></td>
                        <td><input type="text" class="form-control" name="merk_item[${currentTambahCounter}]" placeholder="Merk item"></td>
                        <td><input type="text" class="form-control" name="no_haspel_item[${currentTambahCounter}]" placeholder="No haspel"></td>
                        <td><input type="text" class="form-control" name="no_ref_item[${currentTambahCounter}]" placeholder="No ref"></td>
                        <td><button type="button" class="stock-action-btn stock-action-btn--delete revamp-hapus-item"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `);

                currentTambahCounter++;
                $(this).val('').trigger('change');
            });

            $(document).on('click', '.revamp-hapus-item', function() {
                $(this).closest('tr').remove();
                $('#revamp_table_item_stok tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            });

            $(document).on('input', '.revamp-qty-input', function() {
                const value = $(this).val().replace(/[^\d]/g, '');
                $(this).val(value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            });

            $('.custom-file-input').on('change', function() {
                const file = this.files[0];
                const fileName = file ? file.name : 'Choose file';
                $(this).siblings('.custom-file-label').text(fileName);

                if (!file) {
                    return;
                }

                const allowedExtensions = /\.pdf$/i;
                const maxSize = 5120 * 1024;

                if (!allowedExtensions.test(file.name)) {
                    Swal.fire({ icon: 'warning', title: 'Format file salah', text: 'File harus berupa PDF.' });
                    $(this).val('');
                    $(this).siblings('.custom-file-label').text('Choose file');
                    return;
                }

                if (file.size > maxSize) {
                    Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: 'Ukuran file maksimal 5MB.' });
                    $(this).val('');
                    $(this).siblings('.custom-file-label').text('Choose file');
                }
            });

            $('#revamp_form_tambah_stok').on('submit', function(event) {
                const errors = [];

                if (!$('#revamp_tanggal_upload_stok').val()) {
                    errors.push('Tanggal surat harus diisi.');
                }
                if (!$('#revamp_id_lokasi_gudang').val()) {
                    errors.push('Area gudang harus dipilih.');
                }
                if (!$('#revamp_id_project').val()) {
                    errors.push('Project harus dipilih.');
                }
                if (!$('#revamp_id_sumber_material').val()) {
                    errors.push('Sumber material harus dipilih.');
                }
                if ($('#revamp_table_item_stok tbody tr').length === 0) {
                    errors.push('Minimal harus ada satu item stok.');
                }
                if (!$('#revamp_file_sj').val()) {
                    errors.push('File surat jalan wajib diunggah.');
                }
                if (!$('#revamp_file_evidence').val()) {
                    errors.push('File evidence wajib diunggah.');
                }

                if (errors.length) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        html: errors.join('<br>')
                    });
                }
            });

            $('#revamp_nomor_surat_jalan, #revamp_id_lokasi_gudang, #revamp_id_sumber_material').on('blur change', function() {
                checkNomorSuratJalan();
                toggleHoFields();
            });
        }

        function toggleHoFields() {
            const selectedKota = $('#revamp_id_lokasi_gudang option:selected').text().trim();
            const selectedSumberMaterial = $('#revamp_id_sumber_material option:selected').text().trim();

            if (selectedKota === 'HO') {
                if (selectedSumberMaterial.includes('IN')) {
                    $('#revamp_ho_in_nomor_po').removeClass('stock-hidden');
                    $('#revamp_ho_out_nomor_pr, #revamp_ho_out_lokasi_pengiriman').addClass('stock-hidden');
                    $('#revamp_no_pr_logistik, #revamp_id_lokasi_gudang_pengiriman').val('');
                    return;
                }

                if (selectedSumberMaterial.includes('OUT')) {
                    $('#revamp_ho_out_nomor_pr, #revamp_ho_out_lokasi_pengiriman').removeClass('stock-hidden');
                    $('#revamp_ho_in_nomor_po').addClass('stock-hidden');
                    $('#revamp_no_po_logistik').val('');
                    return;
                }
            }

            $('#revamp_ho_in_nomor_po, #revamp_ho_out_nomor_pr, #revamp_ho_out_lokasi_pengiriman').addClass('stock-hidden');
            $('#revamp_no_po_logistik, #revamp_no_pr_logistik, #revamp_id_lokasi_gudang_pengiriman').val('');
        }

        function checkNomorSuratJalan() {
            const nomorSurat = $('#revamp_nomor_surat_jalan').val();
            const idGudang = $('#revamp_id_lokasi_gudang').val();

            if (!nomorSurat || !idGudang) {
                return;
            }

            $.ajax({
                url: "<?= site_url('Dashboard_Logistik_Stok/cekNomorSuratJalan') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    nomor_surat_jalan: nomorSurat,
                    id_lokasi_gudang: idGudang
                },
                success: function(response) {
                    if (response.status === 'exists') {
                        $('#revamp_nomor_surat_jalan').addClass('is-invalid');
                        $('#revamp_btn_submit_stok').prop('disabled', true);
                    } else {
                        $('#revamp_nomor_surat_jalan').removeClass('is-invalid');
                        $('#revamp_btn_submit_stok').prop('disabled', false);
                    }
                }
            });
        }

        function initDetailModal() {
            $(document).on('click', '.revamp-detail-trigger', function() {
                const suratJalanPath = $(this).data('suratjalan');

                Swal.fire({
                    title: 'Loading...',
                    text: 'Mengambil detail surat jalan.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "<?= base_url('Dashboard_Logistik_Stok/filterDetailSuratJalan') ?>",
                    method: "POST",
                    dataType: "json",
                    data: { no_surat_jalan: suratJalanPath },
                    success: function(response) {
                        Swal.close();
                        const rows = response.getDetailAreaBySJ || [];
                        if (!rows.length) {
                            Swal.fire({ icon: 'warning', title: 'Data kosong', text: 'Detail surat jalan tidak ditemukan.' });
                            return;
                        }

                        const first = rows[0];
                        const tbody = $('#revamp_detail_items_body');
                        tbody.empty();

                        let totalQty = 0;
                        rows.forEach((item, index) => {
                            totalQty += Number(item.jumlah_stok || 0);
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${sanitizeText(item.kategori_item || '-')}</td>
                                    <td>${sanitizeText(item.nama_item || '-')}</td>
                                    <td>${formatNumber(item.jumlah_stok || 0)}</td>
                                    <td>${sanitizeText(item.satuan_item || '-')}</td>
                                    <td>${sanitizeText(item.merk_stok || '-')}</td>
                                    <td>${sanitizeText(item.no_haspel_stok || '-')}</td>
                                    <td>${sanitizeText(item.no_ref_stok || '-')}</td>
                                </tr>
                            `);
                        });

                        $('#revamp_detail_total_qty').text(formatNumber(totalQty));
                        $('#revamp_detail_no_surat_jalan').val(first.no_surat_jalan || '');
                        $('#revamp_detail_tanggal_upload_stok').val((first.tanggal_upload_stok || '').split(' ')[0] || '');
                        $('#revamp_detail_tanggal_pembuatan_stok').val(((first.CREATED_AT || first.tanggal_upload_stok || '').split(' ')[0]) || '');
                        $('#revamp_detail_area_gudang').val(first.kota_lokasi_gudang || '');
                        $('#revamp_detail_nama_project').val(first.project_item || '');
                        $('#revamp_detail_sumber_material').val(first.nama_sumber_material || '');
                        $('#revamp_detail_keterangan_stok_item').val(first.keterangan_stok || '');
                        $('#revamp_detail_no_po_logistik').val(first.no_po_logistik || '');
                        $('#revamp_detail_no_pr_logistik').val(first.no_pr_logistik || '');

                        const baseUrl = "<?= base_url() ?>";
                        const filePathSj = first.surat_jalan || '';
                        const filePathEvidence = first.evidence || '';
                        const fileNameSj = filePathSj.replace(/^.*[\\/]/, '');
                        const fileNameEvidence = filePathEvidence.replace(/^.*[\\/]/, '');

                        $('#revamp_detail_nama_file_sj').text(fileNameSj || 'No file');
                        $('#revamp_view_detail_surat_jalan').attr('href', filePathSj ? baseUrl + filePathSj : '#');

                        if (filePathEvidence) {
                            $('#revamp_container_detail_evidence').removeClass('stock-hidden');
                            $('#revamp_detail_nama_file_evidence').text(fileNameEvidence || 'No file');
                            $('#revamp_view_detail_evidence').attr('href', baseUrl + filePathEvidence);
                        } else {
                            $('#revamp_container_detail_evidence').addClass('stock-hidden');
                        }

                        if ((first.kota_lokasi_gudang || '') === 'HO') {
                            if ((first.status_sumber_material || '').includes('IN')) {
                                $('#revamp_detail_ho_in_nomor_po').removeClass('stock-hidden');
                                $('#revamp_detail_ho_out_nomor_pr').addClass('stock-hidden');
                            } else if ((first.status_sumber_material || '').includes('OUT')) {
                                $('#revamp_detail_ho_out_nomor_pr').removeClass('stock-hidden');
                                $('#revamp_detail_ho_in_nomor_po').addClass('stock-hidden');
                            } else {
                                $('#revamp_detail_ho_in_nomor_po, #revamp_detail_ho_out_nomor_pr').addClass('stock-hidden');
                            }
                        } else {
                            $('#revamp_detail_ho_in_nomor_po, #revamp_detail_ho_out_nomor_pr').addClass('stock-hidden');
                        }

                        $('#modalRevampDetailStok').modal('show');
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan saat mengambil detail.' });
                        console.error(xhr.responseText);
                    }
                });
            });
        }

        function initDeleteConfirmation() {
            $(document).on('click', '.revamp-delete-history', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');

                Swal.fire({
                    title: 'Hapus data ini?',
                    text: 'Data stok yang terhubung dengan surat jalan ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        }

        function populateSimpleSelect(selectElement, values, placeholder) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
            values.forEach((value) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                selectElement.appendChild(option);
            });
        }

        function uniqueValues(rows, key) {
            return [...new Set(rows.map((row) => row[key]).filter((value) => value !== null && value !== undefined && value !== ''))];
        }

        function initExportPanes() {
            $('.stock-tab-btn').on('click', function() {
                const pane = $(this).data('pane');
                $('.stock-tab-btn').removeClass('is-active');
                $(this).addClass('is-active');
                $('.stock-export-pane').removeClass('is-active');
                $(`.stock-export-pane[data-pane="${pane}"]`).addClass('is-active');
            });
        }

        function initInOutExport() {
            const selectRegional = document.getElementById('revamp_report_in_out_regional_gudang');
            const selectKota = document.getElementById('revamp_report_in_out_lokasi_gudang');
            const selectBowheer = document.getElementById('revamp_report_in_out_nama_bowheer');
            const selectKategori = document.getElementById('revamp_report_in_out_kategori_item');
            const selectItem = document.getElementById('revamp_report_in_out_nama_item');

            function syncDropdowns(rows) {
                populateSimpleSelect(selectRegional, uniqueValues(rows, 'regional_lokasi_gudang'), 'Pilih regional');
                populateSimpleSelect(selectKota, uniqueValues(rows, 'kota_lokasi_gudang'), 'Pilih kota');
                populateSimpleSelect(selectBowheer, uniqueValues(rows, 'nama_bowheer'), 'Pilih bowheer');
                populateSimpleSelect(selectKategori, uniqueValues(rows, 'kategori_item'), 'Pilih kategori');
                populateSimpleSelect(selectItem, uniqueValues(rows, 'nama_item'), 'Pilih item');
            }

            syncDropdowns(inOutReportRows);

            selectRegional.addEventListener('change', function() {
                const filtered = inOutReportRows.filter((row) => !this.value || row.regional_lokasi_gudang === this.value);
                populateSimpleSelect(selectKota, uniqueValues(filtered, 'kota_lokasi_gudang'), 'Pilih kota');
                populateSimpleSelect(selectBowheer, uniqueValues(filtered, 'nama_bowheer'), 'Pilih bowheer');
            });

            selectKota.addEventListener('change', function() {
                const filtered = inOutReportRows.filter((row) =>
                    (!selectRegional.value || row.regional_lokasi_gudang === selectRegional.value) &&
                    (!this.value || row.kota_lokasi_gudang === this.value)
                );
                populateSimpleSelect(selectBowheer, uniqueValues(filtered, 'nama_bowheer'), 'Pilih bowheer');
            });

            selectBowheer.addEventListener('change', function() {
                const filtered = inOutReportRows.filter((row) => !this.value || row.nama_bowheer === this.value);
                populateSimpleSelect(selectKategori, uniqueValues(filtered, 'kategori_item'), 'Pilih kategori');
            });

            selectKategori.addEventListener('change', function() {
                const filtered = inOutReportRows.filter((row) => !this.value || row.kategori_item === this.value);
                populateSimpleSelect(selectItem, uniqueValues(filtered, 'nama_item'), 'Pilih item');
            });

            $('#revamp_download_in_out').on('click', function() {
                const selectedRegional = selectRegional.value || '';
                const selectedKota = selectKota.value || '';
                const selectedBowheer = selectBowheer.value || '';
                const selectedKategori = selectKategori.value || '';
                const selectedItem = selectItem.value || '';
                const dateStart = $('#revamp_report_in_out_date_start').val() || '';
                const dateEnd = $('#revamp_report_in_out_date_end').val() || '';

                const filteredData = inOutReportRows.filter((row) => {
                    const uploadDate = String(row.tanggal_upload_stok || '').split(' ')[0];
                    return (!selectedRegional || row.regional_lokasi_gudang === selectedRegional) &&
                        (!selectedKota || row.kota_lokasi_gudang === selectedKota) &&
                        (!selectedBowheer || row.nama_bowheer === selectedBowheer) &&
                        (!selectedKategori || row.kategori_item === selectedKategori) &&
                        (!selectedItem || row.nama_item === selectedItem) &&
                        (!dateStart || uploadDate >= dateStart) &&
                        (!dateEnd || uploadDate <= dateEnd);
                });

                exportJsonToExcel(filteredData, 'Report IN OUT Logistik');
            });
        }

        function initStokExport() {
            const selectRegional = document.getElementById('revamp_report_stok_regional_gudang');
            const selectKota = document.getElementById('revamp_report_stok_lokasi_gudang');
            const selectBowheer = document.getElementById('revamp_report_stok_nama_bowheer');
            const selectKategori = document.getElementById('revamp_report_stok_kategori_item');
            const selectItem = document.getElementById('revamp_report_stok_nama_item');

            function repopulate(rows) {
                populateSimpleSelect(selectRegional, uniqueValues(rows, 'regional_lokasi_gudang'), 'Pilih regional');
                populateSimpleSelect(selectKota, uniqueValues(rows, 'kota_lokasi_gudang'), 'Pilih kota');
                populateSimpleSelect(selectBowheer, uniqueValues(rows, 'nama_bowheer'), 'Pilih bowheer');
                populateSimpleSelect(selectKategori, uniqueValues(rows, 'kategori_item'), 'Pilih kategori');
                populateSimpleSelect(selectItem, uniqueValues(rows, 'nama_item'), 'Pilih item');
            }

            repopulate(stokReportRows);

            selectRegional.addEventListener('change', function() {
                const filtered = stokReportRows.filter((row) => !this.value || row.regional_lokasi_gudang === this.value);
                populateSimpleSelect(selectKota, uniqueValues(filtered, 'kota_lokasi_gudang'), 'Pilih kota');
                populateSimpleSelect(selectBowheer, uniqueValues(filtered, 'nama_bowheer'), 'Pilih bowheer');
            });

            selectKota.addEventListener('change', function() {
                const filtered = stokReportRows.filter((row) =>
                    (!selectRegional.value || row.regional_lokasi_gudang === selectRegional.value) &&
                    (!this.value || row.kota_lokasi_gudang === this.value)
                );
                populateSimpleSelect(selectBowheer, uniqueValues(filtered, 'nama_bowheer'), 'Pilih bowheer');
            });

            selectBowheer.addEventListener('change', function() {
                const filtered = stokReportRows.filter((row) => !this.value || row.nama_bowheer === this.value);
                populateSimpleSelect(selectKategori, uniqueValues(filtered, 'kategori_item'), 'Pilih kategori');
            });

            selectKategori.addEventListener('change', function() {
                const filtered = stokReportRows.filter((row) => !this.value || row.kategori_item === this.value);
                populateSimpleSelect(selectItem, uniqueValues(filtered, 'nama_item'), 'Pilih item');
            });

            $('#revamp_report_stok_date').on('change', function() {
                const dateStart = this.value || '';
                if (!dateStart) {
                    return;
                }

                fetch("<?= site_url('Dashboard_Logistik_Stok/getReportStokByData?dateStart=') ?>" + encodeURIComponent(dateStart))
                    .then((response) => response.json())
                    .then((data) => {
                        stokReportRows = data;
                        repopulate(stokReportRows);
                    })
                    .catch((error) => console.error(error));
            });

            $('#revamp_download_stok').on('click', function() {
                const filteredData = stokReportRows.filter((row) =>
                    (!selectRegional.value || row.regional_lokasi_gudang === selectRegional.value) &&
                    (!selectKota.value || row.kota_lokasi_gudang === selectKota.value) &&
                    (!selectBowheer.value || row.nama_bowheer === selectBowheer.value) &&
                    (!selectKategori.value || row.kategori_item === selectKategori.value) &&
                    (!selectItem.value || row.nama_item === selectItem.value)
                );

                exportJsonToExcel(filteredData, 'Report STOK Logistik');
            });
        }

        function exportJsonToExcel(rows, filePrefix) {
            if (!rows.length) {
                Swal.fire({ icon: 'warning', title: 'Tidak ada data', text: 'Tidak ada data yang sesuai untuk diunduh.' });
                return;
            }

            const formattedRows = rows.map((row) => {
                const newRow = {};
                Object.keys(row).forEach((key) => {
                    const prettyKey = key.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
                    newRow[prettyKey] = row[key];
                });
                return newRow;
            });

            const worksheet = XLSX.utils.json_to_sheet(formattedRows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Report Logistik');
            const timestamp = moment().format('YYYY-MM-DD HH.mm.ss');
            XLSX.writeFile(workbook, `${filePrefix} ${timestamp}.xlsx`);
        }

        function initDateRangeFilter() {
            $('#revamp_date_range').daterangepicker({
                autoUpdateInput: false,
                locale: { format: 'MM/DD/YYYY' }
            });

            $('#revamp_date_range').on('apply.daterangepicker', function(ev, picker) {
                currentHistoryDateFilter.start = picker.startDate.clone().startOf('day');
                currentHistoryDateFilter.end = picker.endDate.clone().endOf('day');
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                historyTable.draw();
            });

            $('#revamp_reset_history_date').on('click', function() {
                currentHistoryDateFilter.start = null;
                currentHistoryDateFilter.end = null;
                const picker = $('#revamp_date_range').data('daterangepicker');
                picker.setStartDate(moment());
                picker.setEndDate(moment());
                $('#revamp_date_range').val('');
                historyTable.draw();
            });
        }

        $(function() {
            $('.select2').select2({
                width: '100%',
                allowClear: true
            });

            updateCategoryCardsFromMap(categoryTotalsMap);
            rebuildRegionalTable(initialRegionalRows);
            rebuildAreaTable(initialAreaRows);
            initHistoryTable();
            initDateRangeFilter();
            bindCategoryLinks();
            initTambahModal();
            initDetailModal();
            initDeleteConfirmation();
            initExportPanes();
            initInOutExport();
            initStokExport();
            toggleHoFields();

            $('#revamp_apply_filter').on('click', applyDashboardFilter);
            $('#revamp_reset_filter').on('click', resetRevampFilters);
        });
    })();
</script>
