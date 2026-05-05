<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

$slugify = function ($value) {
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
};

$categoryVisuals = [
    'aksesories' => ['icon' => 'fas fa-plug', 'tone' => 'blue', 'label' => 'AKSESORIES'],
    'closure' => ['icon' => 'fas fa-box-open', 'tone' => 'teal', 'label' => 'CLOSURE'],
    'fat-odp' => ['icon' => 'fas fa-network-wired', 'tone' => 'amber', 'label' => 'FAT'],
    'fdt-odc' => ['icon' => 'fas fa-project-diagram', 'tone' => 'indigo', 'label' => 'FDT'],
    'hdpe' => ['icon' => 'fas fa-wave-square', 'tone' => 'rose', 'label' => 'HDPE'],
    'kabel' => ['icon' => 'fas fa-ethernet', 'tone' => 'cyan', 'label' => 'KABEL'],
    'otb' => ['icon' => 'fas fa-server', 'tone' => 'violet', 'label' => 'OTB'],
    'tiang' => ['icon' => 'fas fa-broadcast-tower', 'tone' => 'emerald', 'label' => 'TIANG'],
];

$buildCategoryCards = static function (array $rawRows, array $categoryVisuals, callable $slugify, string $valueKey) {
    $prepared = [];
    foreach ($categoryVisuals as $slug => $visual) {
        $prepared[$slug] = [
            'label' => $visual['label'],
            'formatted' => '0',
            'value' => 0,
            'unit' => '',
            'icon' => $visual['icon'],
            'tone' => $visual['tone'],
        ];
    }

    foreach ($rawRows as $row) {
        $slug = $slugify($row['kategori_item'] ?? '');
        if (!isset($prepared[$slug])) {
            continue;
        }

        $value = (float) ($row[$valueKey] ?? 0);
        $unit = trim((string) ($row['satuan_item'] ?? ''));
        $prepared[$slug]['value'] = $value;
        $prepared[$slug]['unit'] = $unit;
        $prepared[$slug]['formatted'] = trim(number_format($value, 0, ',', '.') . ' ' . $unit);
    }

    return array_values($prepared);
};

$shipmentRows = $shipmentRows ?? [];
$transitRows = $transitRows ?? [];
$transitCategoryCards = $transitCategoryCards ?? [];
$historyCategoryCards = $historyCategoryCards ?? [];
$preparedTransitCards = $buildCategoryCards($transitCategoryCards, $categoryVisuals, $slugify, 'total_qty_outstanding');
$preparedHistoryCards = $buildCategoryCards($historyCategoryCards, $categoryVisuals, $slugify, 'total_qty_kirim');

$shipmentStats = [
    'total_transit_document' => count($transitRows),
    'total_history_document' => count($shipmentRows),
    'total_outstanding' => array_sum(array_map('floatval', array_column($transitRows, 'total_qty_outstanding'))),
    'total_qty_kirim' => array_sum(array_map('floatval', array_column($shipmentRows, 'total_qty_kirim'))),
];
?>

<style>
    .transit-shell {
        padding: 1.15rem;
    }

    .transit-hero {
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

    .transit-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .transit-hero__eyebrow {
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

    .transit-hero h1 {
        margin: 1rem 0 0.7rem;
        font-size: 1.95rem;
        font-weight: 800;
        color: #fff;
    }

    .transit-hero p {
        margin: 0;
        max-width: 44rem;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .transit-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .transit-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .transit-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .transit-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .transit-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .transit-panel {
        margin-top: 1.2rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        overflow: hidden;
    }

    .transit-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .transit-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .transit-panel__subtitle {
        margin: 0.28rem 0 0;
        color: #64748b;
        font-size: 0.92rem;
    }

    .transit-panel__body {
        padding: 1.1rem 1.25rem 1.25rem;
    }

    .transit-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.9rem;
    }

    .transit-card {
        position: relative;
        padding: 1rem;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    }

    .transit-card__icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1rem;
        margin-bottom: 0.9rem;
    }

    .transit-card__label {
        display: block;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 800;
    }

    .transit-card__value {
        display: block;
        margin-top: 0.4rem;
        font-size: 1.3rem;
        font-weight: 800;
        color: #0f172a;
    }

    .transit-card__meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.95rem;
        font-size: 0.84rem;
    }

    .transit-card__hint {
        color: #94a3b8;
        line-height: 1.5;
    }

    .transit-card__link {
        color: #0f172a;
        font-weight: 800;
        white-space: nowrap;
    }

    .transit-card--blue .transit-card__icon { background: rgba(59,130,246,0.12); color: #1d4ed8; }
    .transit-card--teal .transit-card__icon { background: rgba(20,184,166,0.12); color: #0f766e; }
    .transit-card--amber .transit-card__icon { background: rgba(245,158,11,0.12); color: #b45309; }
    .transit-card--indigo .transit-card__icon { background: rgba(99,102,241,0.12); color: #4338ca; }
    .transit-card--rose .transit-card__icon { background: rgba(244,63,94,0.12); color: #be123c; }
    .transit-card--cyan .transit-card__icon { background: rgba(6,182,212,0.12); color: #0f766e; }
    .transit-card--violet .transit-card__icon { background: rgba(139,92,246,0.12); color: #6d28d9; }
    .transit-card--emerald .transit-card__icon { background: rgba(16,185,129,0.12); color: #047857; }
    .transit-card--slate .transit-card__icon { background: rgba(148,163,184,0.16); color: #334155; }

    .transit-nav {
        gap: 0.65rem;
        border-bottom: 0;
    }

    .transit-nav .nav-link {
        border: 0;
        border-radius: 999px;
        padding: 0.7rem 1rem;
        font-weight: 700;
        color: #475569;
        background: rgba(226, 232, 240, 0.72);
    }

    .transit-nav .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
    }

    .transit-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .transit-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .transit-table th,
    .transit-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .transit-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.72);
    }

    .transit-table tfoot td {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 800;
    }

    .transit-table .text-number {
        text-align: right;
        white-space: nowrap;
    }

    .transit-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .transit-badge--shipping { background: rgba(245, 158, 11, 0.14); color: #b45309; }
    .transit-badge--partial { background: rgba(59, 130, 246, 0.14); color: #1d4ed8; }
    .transit-badge--delivered { background: rgba(16, 185, 129, 0.14); color: #047857; }
    .transit-badge--source { background: rgba(15, 23, 42, 0.08); color: #334155; }

    .transit-alert {
        margin-top: 1rem;
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1rem;
    }

    .transit-empty {
        padding: 1.4rem;
        border-radius: 18px;
        text-align: center;
        color: #64748b;
        background: rgba(248, 250, 252, 0.94);
        border: 1px dashed rgba(148, 163, 184, 0.28);
    }

    @media (max-width: 1199.98px) {
        .transit-hero__grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .transit-shell {
            padding: 0.8rem;
        }

        .transit-hero__grid {
            padding: 1rem;
        }

        .transit-hero h1 {
            font-size: 1.55rem;
        }

        .transit-metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid transit-shell">
            <section class="transit-hero">
                <div class="transit-hero__grid">
                    <div>
                        <span class="transit-hero__eyebrow">
                            <i class="fas fa-route"></i>
                            Transit & History
                        </span>
                        <h1>Monitoring pengiriman HO ke area lain berdasarkan surat jalan internal.</h1>
                        <p>
                            Halaman ini khusus dipakai untuk memantau mutasi material dari gudang HO ke area lain. Fokus utamanya tetap per surat jalan,
                            status pengiriman, jenis material, dan detail item yang ikut keluar dari HO.
                        </p>
                    </div>
                    <div class="transit-metric-grid">
                        <div class="transit-metric">
                            <span class="transit-metric__label">Dokumen Transit</span>
                            <span class="transit-metric__value"><?= number_format($shipmentStats['total_transit_document'], 0, ',', '.') ?></span>
                            <span class="transit-metric__hint">Surat jalan HO yang masih punya outstanding penerimaan.</span>
                        </div>
                        <div class="transit-metric">
                            <span class="transit-metric__label">Full History</span>
                            <span class="transit-metric__value"><?= number_format($shipmentStats['total_history_document'], 0, ',', '.') ?></span>
                            <span class="transit-metric__hint">Total seluruh surat jalan HO yang sudah tercatat.</span>
                        </div>
                        <div class="transit-metric">
                            <span class="transit-metric__label">Outstanding Transit</span>
                            <span class="transit-metric__value"><?= number_format($shipmentStats['total_outstanding'], 0, ',', '.') ?></span>
                            <span class="transit-metric__hint">Qty yang masih menunggu diterima / closing penerimaan.</span>
                        </div>
                        <div class="transit-metric">
                            <span class="transit-metric__label">Total Qty Kirim</span>
                            <span class="transit-metric__value"><?= number_format($shipmentStats['total_qty_kirim'], 0, ',', '.') ?></span>
                            <span class="transit-metric__hint">Akumulasi qty kirim seluruh histori surat jalan.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success transit-alert"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger transit-alert"><?= $flashError ?></div>
            <?php endif; ?>

            <section class="transit-panel">
                <div class="transit-panel__head">
                    <div>
                        <h2 class="transit-panel__title">Surat Jalan dan Status Pengiriman</h2>
                        <p class="transit-panel__subtitle">Tab pertama untuk pengiriman HO yang belum selesai diterima, tab kedua untuk seluruh history mutasi HO.</p>
                    </div>
                </div>
                <div class="transit-panel__body">
                    <ul class="nav nav-pills transit-nav" id="transit-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="transit-open-tab" data-toggle="pill" href="#transit-open" role="tab" aria-controls="transit-open" aria-selected="true">
                                Transit Belum Selesai
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="transit-history-tab" data-toggle="pill" href="#transit-history" role="tab" aria-controls="transit-history" aria-selected="false">
                                Full History
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="transit-tabs-content">
                        <div class="tab-pane fade show active" id="transit-open" role="tabpanel" aria-labelledby="transit-open-tab">
                            <div class="transit-card-grid mb-4">
                                <?php foreach ($preparedTransitCards as $card): ?>
                                    <article class="transit-card transit-card--<?= $card['tone'] ?>">
                                        <div class="transit-card__icon">
                                            <i class="<?= $card['icon'] ?>"></i>
                                        </div>
                                        <span class="transit-card__label"><?= $card['label'] ?></span>
                                        <span class="transit-card__value"><?= $card['formatted'] ?></span>
                                        <div class="transit-card__meta">
                                            <span class="transit-card__hint">Akumulasi material transit per kategori.</span>
                                            <span class="transit-card__link">Lihat detail</span>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <?php if (empty($transitRows)): ?>
                                <div class="transit-empty">Tidak ada surat jalan transit yang outstanding saat ini.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table transit-table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Sumber</th>
                                                <th>No Surat Jalan</th>
                                                <th>Tanggal</th>
                                                <th>Rute</th>
                                                <th>Jenis Material</th>
                                                <th>Bowheer</th>
                                                <th>Referensi</th>
                                                <th class="text-number">Qty Kirim</th>
                                                <th class="text-number">Qty Diterima</th>
                                                <th class="text-number">Outstanding</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($transitRows as $index => $row): ?>
                                                <?php
                                                $statusClass = 'transit-badge--shipping';
                                                if (($row['status_pengiriman'] ?? '') === 'PARTIAL DELIVERED') {
                                                    $statusClass = 'transit-badge--partial';
                                                } elseif (($row['status_pengiriman'] ?? '') === 'FULL DELIVERED') {
                                                    $statusClass = 'transit-badge--delivered';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><span class="transit-badge transit-badge--source">HO -> Area</span></td>
                                                    <td><?= $row['no_surat_jalan'] ?: '-' ?></td>
                                                    <td><?= !empty($row['tanggal_pengiriman']) ? date('d M Y', strtotime($row['tanggal_pengiriman'])) : '-' ?></td>
                                                    <td><?= trim(($row['asal_gudang'] ?: '-') . ' -> ' . ($row['tujuan_gudang'] ?: '-')) ?></td>
                                                    <td><?= $row['kategori_refs'] ?: '-' ?><br><small class="text-muted"><?= $row['item_refs'] ?: '-' ?></small></td>
                                                    <td><?= $row['bowheer_refs'] ?: '-' ?></td>
                                                    <td>
                                                        <strong>PO:</strong> <?= $row['po_refs'] ?: '-' ?><br>
                                                        <strong>PR:</strong> <?= $row['pr_refs'] ?: '-' ?>
                                                    </td>
                                                    <td class="text-number"><?= number_format($row['total_qty_kirim'] ?? 0, 0, ',', '.') ?></td>
                                                    <td class="text-number"><?= number_format($row['total_qty_diterima'] ?? 0, 0, ',', '.') ?></td>
                                                    <td class="text-number"><?= number_format($row['total_qty_outstanding'] ?? 0, 0, ',', '.') ?></td>
                                                    <td><span class="transit-badge <?= $statusClass ?>"><?= $row['status_pengiriman'] ?: '-' ?></span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-transit-detail" data-surat-jalan="<?= htmlspecialchars((string) ($row['no_surat_jalan'] ?? ''), ENT_QUOTES) ?>">
                                                            Detail
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="8">TOTAL</td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($transitRows, 'total_qty_kirim'))), 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($transitRows, 'total_qty_diterima'))), 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($transitRows, 'total_qty_outstanding'))), 0, ',', '.') ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tab-pane fade" id="transit-history" role="tabpanel" aria-labelledby="transit-history-tab">
                            <div class="transit-card-grid mb-4">
                                <?php foreach ($preparedHistoryCards as $card): ?>
                                    <article class="transit-card transit-card--<?= $card['tone'] ?>">
                                        <div class="transit-card__icon">
                                            <i class="<?= $card['icon'] ?>"></i>
                                        </div>
                                        <span class="transit-card__label"><?= $card['label'] ?></span>
                                        <span class="transit-card__value"><?= $card['formatted'] ?></span>
                                        <div class="transit-card__meta">
                                            <span class="transit-card__hint">Akumulasi qty kirim per kategori.</span>
                                            <span class="transit-card__link">Lihat detail</span>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <?php if (empty($shipmentRows)): ?>
                                <div class="transit-empty">Belum ada histori pengiriman yang tersimpan.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table transit-table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Sumber</th>
                                                <th>No Surat Jalan</th>
                                                <th>Tanggal</th>
                                                <th>Rute</th>
                                                <th>Jenis Material</th>
                                                <th>Bowheer</th>
                                                <th>Referensi</th>
                                                <th class="text-number">Qty Kirim</th>
                                                <th class="text-number">Qty Diterima</th>
                                                <th class="text-number">Outstanding</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($shipmentRows as $index => $row): ?>
                                                <?php
                                                $statusClass = 'transit-badge--shipping';
                                                if (($row['status_pengiriman'] ?? '') === 'PARTIAL DELIVERED') {
                                                    $statusClass = 'transit-badge--partial';
                                                } elseif (($row['status_pengiriman'] ?? '') === 'FULL DELIVERED') {
                                                    $statusClass = 'transit-badge--delivered';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><span class="transit-badge transit-badge--source">HO -> Area</span></td>
                                                    <td><?= $row['no_surat_jalan'] ?: '-' ?></td>
                                                    <td><?= !empty($row['tanggal_pengiriman']) ? date('d M Y', strtotime($row['tanggal_pengiriman'])) : '-' ?></td>
                                                    <td><?= trim(($row['asal_gudang'] ?: '-') . ' -> ' . ($row['tujuan_gudang'] ?: '-')) ?></td>
                                                    <td><?= $row['kategori_refs'] ?: '-' ?><br><small class="text-muted"><?= $row['item_refs'] ?: '-' ?></small></td>
                                                    <td><?= $row['bowheer_refs'] ?: '-' ?></td>
                                                    <td>
                                                        <strong>PO:</strong> <?= $row['po_refs'] ?: '-' ?><br>
                                                        <strong>PR:</strong> <?= $row['pr_refs'] ?: '-' ?>
                                                    </td>
                                                    <td class="text-number"><?= number_format($row['total_qty_kirim'] ?? 0, 0, ',', '.') ?></td>
                                                    <td class="text-number"><?= number_format($row['total_qty_diterima'] ?? 0, 0, ',', '.') ?></td>
                                                    <td class="text-number"><?= number_format($row['total_qty_outstanding'] ?? 0, 0, ',', '.') ?></td>
                                                    <td><span class="transit-badge <?= $statusClass ?>"><?= $row['status_pengiriman'] ?: '-' ?></span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-transit-detail" data-surat-jalan="<?= htmlspecialchars((string) ($row['no_surat_jalan'] ?? ''), ENT_QUOTES) ?>">
                                                            Detail
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="8">TOTAL</td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($shipmentRows, 'total_qty_kirim'))), 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($shipmentRows, 'total_qty_diterima'))), 0, ',', '.') ?></td>
                                                <td class="text-number"><?= number_format(array_sum(array_map('floatval', array_column($shipmentRows, 'total_qty_outstanding'))), 0, ',', '.') ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-transit-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document" style="width:78vw;max-width:78vw;">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;">
                <div>
                    <h5 class="modal-title mb-0">Detail Pengiriman HO</h5>
                    <small id="transit-detail-subtitle">Memuat detail surat jalan.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>No Surat Jalan</strong><br><span id="transit-detail-sj">-</span></div>
                    <div class="col-md-3"><strong>Tanggal</strong><br><span id="transit-detail-date">-</span></div>
                    <div class="col-md-3"><strong>Rute</strong><br><span id="transit-detail-route">-</span></div>
                    <div class="col-md-3"><strong>Referensi PO</strong><br><span id="transit-detail-po">-</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Dokumen Surat Jalan</strong><br><span id="transit-detail-doc-sj">-</span></div>
                    <div class="col-md-6"><strong>Evidence</strong><br><span id="transit-detail-doc-evidence">-</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table transit-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Nama Item</th>
                                <th>Bowheer</th>
                                <th>Merk</th>
                                <th>No Haspel</th>
                                <th>No Ref</th>
                                <th class="text-number">Qty Kirim</th>
                                <th class="text-number">Qty Diterima</th>
                                <th class="text-number">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody id="transit-detail-body">
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">TOTAL</td>
                                <td class="text-number" id="transit-detail-total-kirim">0</td>
                                <td class="text-number" id="transit-detail-total-diterima">0</td>
                                <td class="text-number" id="transit-detail-total-outstanding">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function formatTransitNumber(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value || 0);
    }

    function buildTransitDocLink(pathValue, label) {
        if (!pathValue) {
            return '-';
        }

        const normalized = String(pathValue).replace(/^\.?\//, '');
        return `<a href="<?= base_url() ?>${normalized}" target="_blank">${label}</a>`;
    }

    $(document).ready(function() {
        $(document).on('click', '.btn-transit-detail', function() {
            const suratJalan = $(this).data('surat-jalan') || '';
            if (!suratJalan) {
                return;
            }

            $('#modal-transit-detail').modal('show');
            $('#transit-detail-subtitle').text('Mengambil detail surat jalan ' + suratJalan + '...');
            $('#transit-detail-body').html('<tr><td colspan="10" class="text-center text-muted">Memuat detail...</td></tr>');

            $.ajax({
                url: "<?= base_url('Dashboard_Logistik_Stok/getTransitHistoryDetail') ?>",
                type: 'GET',
                dataType: 'json',
                data: { nomor_surat_jalan: suratJalan },
                success: function(response) {
                    const header = response.header || null;
                    const items = response.items || [];

                    if (!header || items.length === 0) {
                        $('#transit-detail-subtitle').text(response.message || 'Detail pengiriman tidak ditemukan.');
                        $('#transit-detail-body').html('<tr><td colspan="10" class="text-center text-muted">' + (response.message || 'Detail pengiriman tidak ditemukan.') + '</td></tr>');
                        $('#transit-detail-sj, #transit-detail-date, #transit-detail-route, #transit-detail-po, #transit-detail-doc-sj, #transit-detail-doc-evidence').text('-');
                        $('#transit-detail-total-kirim, #transit-detail-total-diterima, #transit-detail-total-outstanding').text('0');
                        return;
                    }

                    let totalKirim = 0;
                    let totalDiterima = 0;
                    let totalOutstanding = 0;
                    const rows = items.map(function(item, index) {
                        const qtyKirim = parseFloat(item.qty_kirim || 0);
                        const qtyDiterima = parseFloat(item.qty_diterima || 0);
                        const qtyOutstanding = parseFloat(item.qty_outstanding || 0);
                        totalKirim += qtyKirim;
                        totalDiterima += qtyDiterima;
                        totalOutstanding += qtyOutstanding;

                        return `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.kategori_item || '-'}</td>
                                <td>${item.nama_item || '-'}</td>
                                <td>${item.nama_bowheer || '-'}</td>
                                <td>${item.merk_stok || '-'}</td>
                                <td>${item.no_haspel_stok || '-'}</td>
                                <td>${item.no_ref_stok || '-'}</td>
                                <td class="text-number">${formatTransitNumber(qtyKirim)}</td>
                                <td class="text-number">${formatTransitNumber(qtyDiterima)}</td>
                                <td class="text-number">${formatTransitNumber(qtyOutstanding)}</td>
                            </tr>
                        `;
                    }).join('');

                    $('#transit-detail-subtitle').text('Detail item pengiriman HO berdasarkan surat jalan internal.');
                    $('#transit-detail-body').html(rows);
                    $('#transit-detail-sj').text(header.no_surat_jalan || '-');
                    $('#transit-detail-date').text(header.tanggal_pengiriman || '-');
                    $('#transit-detail-route').text((header.asal_gudang || '-') + ' -> ' + (header.tujuan_gudang || '-'));
                    $('#transit-detail-po').text(header.no_po_logistik || '-');
                    $('#transit-detail-doc-sj').html(buildTransitDocLink(header.surat_jalan, 'Lihat Surat Jalan'));
                    $('#transit-detail-doc-evidence').html(buildTransitDocLink(header.evidence, 'Lihat Evidence'));
                    $('#transit-detail-total-kirim').text(formatTransitNumber(totalKirim));
                    $('#transit-detail-total-diterima').text(formatTransitNumber(totalDiterima));
                    $('#transit-detail-total-outstanding').text(formatTransitNumber(totalOutstanding));
                },
                error: function() {
                    $('#transit-detail-subtitle').text('Gagal memuat detail pengiriman.');
                    $('#transit-detail-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat detail pengiriman.</td></tr>');
                }
            });
        });
    });
</script>
