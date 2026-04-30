<?php
$status = $this->session->flashdata('status');
$flashSuccess = $this->session->flashdata('stockopname_success');
$flashError = $this->session->flashdata('stockopname_error');

$isView = $mode === '1bda80f2be4d3658e0baa43fbe7ae8c1';
$isInput = $mode === 'a43c1b0aa53a0c908810c06ab1ff3967';
$isEdit = $mode === 'de95b43bceeb4b998aed4aed5cef1ae7';

$periode = $getDetailSoPeriode[0] ?? [];
$periodeLabel = trim((string) (($periode['sop_bulan'] ?? '-') . ' ' . ($periode['sop_tahun'] ?? '')));
$rows = $isInput ? ($getSOItem ?? []) : ($getDetailSoItem ?? []);
$firstRow = $rows[0] ?? [];

$locationId = $id_lokasi_gudang ?? ($firstRow['id_lokasi_gudang'] ?? $firstRow['id_kota_gudang'] ?? '');
$regional = $firstRow['regional_lokasi_gudang'] ?? '-';
$provinsi = $firstRow['provinsi_lokasi_gudang'] ?? '-';
$kota = $firstRow['kota_lokasi_gudang'] ?? '-';
$snapshotTanggal = isset($snapshot_tanggal) ? date('d M Y', strtotime($snapshot_tanggal)) : date('d M Y');
$redirectTarget = current_url() . ($mode ? '?mode=' . rawurlencode($mode) : '');
$soKotaData = $soKota ?? [];
$baData = $existingBA ?? [];
$baItems = $existingBAItems ?? [];
$discrepancyItems = $discrepancyItems ?? [];
$approvalLogs = $approvalLogs ?? [];

$totalStokAplikasi = 0;
$totalStokFisik = 0;
$totalSelisihNominal = 0;
$totalSelisihItem = 0;

foreach ($rows as $row) {
    $stokAplikasi = (float) ($isInput ? ($row['total_jumlah_stok'] ?? 0) : ($row['soi_stok_asli'] ?? 0));
    $stokFisik = $isInput ? null : (float) ($row['soi_stok_opname'] ?? 0);
    $selisih = $stokFisik === null
        ? 0
        : (isset($row['soi_selisih']) && $row['soi_selisih'] !== null
            ? (float) $row['soi_selisih']
            : ($stokAplikasi - $stokFisik));

    $totalStokAplikasi += $stokAplikasi;
    $totalStokFisik += $stokFisik === null ? 0 : $stokFisik;
    $totalSelisihNominal += $selisih;
    if ($stokFisik !== null && $selisih != 0) {
        $totalSelisihItem++;
    }
}

$modeLabel = 'Detail SO';
if ($isInput) {
    $modeLabel = 'Input SO';
} elseif ($isEdit) {
    $modeLabel = 'Edit SO';
}
?>

<style>
    .so-item-revamp {
        --so-item-ink: #0f172a;
        --so-item-muted: #64748b;
        --so-item-line: rgba(148, 163, 184, 0.18);
        --so-item-panel: rgba(255, 255, 255, 0.96);
        --so-item-soft: rgba(248, 250, 252, 0.94);
        --so-item-shadow: 0 24px 52px rgba(15, 23, 42, 0.1);
        --so-item-danger: #b91c1c;
        --so-item-success: #047857;
        --so-item-warning: #b45309;
    }

    .so-item-shell {
        padding: 1.15rem;
    }

    .so-item-hero {
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 28%),
            radial-gradient(circle at bottom right, rgba(249, 115, 22, 0.12), transparent 24%),
            linear-gradient(135deg, #0f172a 0%, #14345a 48%, #7c2d12 100%);
        box-shadow: 0 30px 72px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .so-item-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1rem;
        padding: 1.45rem;
    }

    .so-item-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.38rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .so-item-hero h1 {
        margin: 0.95rem 0 0.72rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .so-item-hero p {
        margin: 0;
        max-width: 48rem;
        color: rgba(226, 232, 240, 0.84);
        line-height: 1.7;
    }

    .so-item-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.2rem;
    }

    .so-item-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.82rem 1.1rem;
        border-radius: 14px;
        border: 0;
        font-weight: 800;
    }

    .so-item-btn:hover {
        text-decoration: none;
    }

    .so-item-btn--light {
        background: #f8fafc;
        color: #0f172a;
    }

    .so-item-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .so-item-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        align-content: start;
    }

    .so-item-metric {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .so-item-metric__label {
        display: block;
        margin-bottom: 0.45rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .so-item-metric__value {
        font-size: 1.72rem;
        font-weight: 800;
        color: #fff;
    }

    .so-item-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .so-item-info {
        border-radius: 22px;
        border: 1px solid var(--so-item-line);
        background: var(--so-item-panel);
        box-shadow: var(--so-item-shadow);
        padding: 1.05rem;
    }

    .so-item-info__label {
        display: block;
        margin-bottom: 0.42rem;
        color: var(--so-item-muted);
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .so-item-info__value {
        color: var(--so-item-ink);
        font-weight: 800;
        font-size: 1.08rem;
    }

    .so-item-flow,
    .so-item-table {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--so-item-line);
        background: var(--so-item-panel);
        box-shadow: var(--so-item-shadow);
        overflow: hidden;
    }

    .so-item-flow {
        padding: 1.2rem;
    }

    .so-item-flow__title,
    .so-item-table__title {
        margin: 0;
        color: var(--so-item-ink);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .so-item-flow__desc,
    .so-item-table__desc {
        margin: 0.38rem 0 0;
        color: var(--so-item-muted);
        line-height: 1.6;
    }

    .so-item-steps {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.9rem;
        margin-top: 1rem;
    }

    .so-item-step {
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: var(--so-item-soft);
        padding: 1rem;
    }

    .so-item-step h3 {
        margin: 0 0 0.45rem;
        color: var(--so-item-ink);
        font-size: 0.95rem;
        font-weight: 800;
    }

    .so-item-step p {
        margin: 0;
        color: var(--so-item-muted);
        line-height: 1.55;
        font-size: 0.9rem;
    }

    .so-item-alert {
        margin-top: 1.2rem;
        padding: 1rem 1.05rem;
        border-radius: 18px;
        border: 1px solid rgba(245, 158, 11, 0.24);
        background: rgba(255, 251, 235, 0.95);
        color: #92400e;
        line-height: 1.65;
    }

    .so-item-ba {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--so-item-line);
        background: var(--so-item-panel);
        box-shadow: var(--so-item-shadow);
        overflow: hidden;
    }

    .so-item-ba__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .so-item-ba__title {
        margin: 0;
        color: var(--so-item-ink);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .so-item-ba__desc {
        margin: 0.38rem 0 0;
        color: var(--so-item-muted);
        line-height: 1.6;
    }

    .so-item-ba__body {
        padding: 1rem 1.2rem 1.2rem;
    }

    .so-item-ba__grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 1rem;
    }

    .so-item-ba__panel {
        border-radius: 18px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: #fff;
        padding: 1rem;
    }

    .so-item-ba__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1rem;
    }

    .so-item-ba__meta-box {
        border-radius: 16px;
        background: var(--so-item-soft);
        padding: 0.85rem;
    }

    .so-item-ba__meta-label {
        display: block;
        color: var(--so-item-muted);
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.35rem;
    }

    .so-item-ba__meta-value {
        color: var(--so-item-ink);
        font-weight: 800;
        font-size: 0.98rem;
    }

    .so-item-ba__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .so-item-ba__table th,
    .so-item-ba__table td {
        font-size: 0.84rem;
        padding: 0.6rem 0.55rem;
    }

    .so-item-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .so-item-status-badge--done {
        background: rgba(16, 185, 129, 0.14);
        color: #047857;
    }

    .so-item-status-badge--warning {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .so-item-status-badge--info {
        background: rgba(59, 130, 246, 0.14);
        color: #1d4ed8;
    }

    .so-item-log-list {
        margin: 0;
        padding-left: 1rem;
    }

    .so-item-log-list li {
        margin-bottom: 0.75rem;
        color: var(--so-item-muted);
        line-height: 1.55;
    }

    .so-item-table__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .so-item-table__body {
        padding: 1rem 1.2rem 1.2rem;
    }

    .so-item-table table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .so-item-table th {
        padding: 0.88rem 0.8rem;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.9);
        white-space: nowrap;
    }

    .so-item-table td {
        padding: 0.88rem 0.8rem;
        color: #0f172a;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        vertical-align: middle;
    }

    .so-item-row--selisih {
        background: rgba(254, 242, 242, 0.76);
    }

    .so-item-row--selisih:hover {
        background: rgba(254, 226, 226, 0.9);
    }

    .so-item-row--sinkron {
        background: rgba(240, 253, 244, 0.72);
    }

    .so-item-discrepancy {
        font-weight: 800;
    }

    .so-item-discrepancy--minus,
    .so-item-discrepancy--plus {
        color: var(--so-item-danger);
    }

    .so-item-discrepancy--zero {
        color: var(--so-item-success);
    }

    .so-item-remarks-required {
        border-color: rgba(239, 68, 68, 0.45) !important;
        box-shadow: 0 0 0 0.12rem rgba(239, 68, 68, 0.08);
    }

    .so-item-table tfoot td {
        font-weight: 800;
        background: #f8fafc;
    }

    .so-item-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--so-item-muted);
    }

    @media (max-width: 1199.98px) {
        .so-item-grid,
        .so-item-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .so-item-hero__grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .so-item-shell {
            padding: 0.85rem;
        }

        .so-item-grid,
        .so-item-steps,
        .so-item-metrics,
        .so-item-ba__meta {
            grid-template-columns: 1fr;
        }

        .so-item-ba__grid,
        .so-item-table__head {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="content-wrapper so-item-revamp">
    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="so-item-shell">
                    <section class="so-item-hero">
                        <div class="so-item-hero__grid">
                            <div>
                                <span class="so-item-hero__eyebrow"><i class="fas fa-box-open"></i> <?= $modeLabel ?></span>
                                <h1><?= $kota ?>, periode <?= $periodeLabel ?></h1>
                                <p>Bandingkan stok aplikasi dengan stok aktual area. Jika ada selisih, remarks item wajib diisi karena akan menjadi dasar BA kronologi sebelum material di-adjust ke ledger.</p>
                                <div class="so-item-actions">
                                    <a href="<?= base_url('StockOpname/revamp/periode/' . $id_sop) ?>" class="so-item-btn so-item-btn--light">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Area
                                    </a>
                                </div>
                            </div>

                            <div class="so-item-metrics">
                                <div class="so-item-metric">
                                    <span class="so-item-metric__label">Total Item</span>
                                    <div class="so-item-metric__value"><?= number_format(count($rows), 0, ',', '.') ?></div>
                                </div>
                                <div class="so-item-metric">
                                    <span class="so-item-metric__label">Selisih Item</span>
                                    <div class="so-item-metric__value" id="metric-selisih-item"><?= number_format($totalSelisihItem, 0, ',', '.') ?></div>
                                </div>
                                <div class="so-item-metric">
                                    <span class="so-item-metric__label">Stok Aplikasi</span>
                                    <div class="so-item-metric__value" id="metric-stok-aplikasi"><?= number_format($totalStokAplikasi, 0, ',', '.') ?></div>
                                </div>
                                <div class="so-item-metric">
                                    <span class="so-item-metric__label">Stok Fisik</span>
                                    <div class="so-item-metric__value" id="metric-stok-fisik"><?= number_format($totalStokFisik, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="so-item-grid">
                        <article class="so-item-info">
                            <span class="so-item-info__label">Regional</span>
                            <div class="so-item-info__value"><?= $regional ?></div>
                        </article>
                        <article class="so-item-info">
                            <span class="so-item-info__label">Provinsi</span>
                            <div class="so-item-info__value"><?= $provinsi ?></div>
                        </article>
                        <article class="so-item-info">
                            <span class="so-item-info__label">Lokasi Gudang</span>
                            <div class="so-item-info__value"><?= $kota ?></div>
                        </article>
                        <article class="so-item-info">
                            <span class="so-item-info__label">Snapshot Pembanding</span>
                            <div class="so-item-info__value"><?= $snapshotTanggal ?></div>
                        </article>
                    </section>

                    <section class="so-item-flow">
                        <h2 class="so-item-flow__title">Flow tindak lanjut setelah input</h2>
                        <p class="so-item-flow__desc">Halaman ini fokus ke tahap hitung fisik dan penjelasan selisih. Setelah data tersimpan, proses berikutnya adalah dokumen BA, upload signed BA, approval, lalu adjustment.</p>
                        <div class="so-item-steps">
                            <article class="so-item-step">
                                <h3>1. Hitung aktual</h3>
                                <p>Area mencatat stok fisik per item berdasarkan kondisi riil gudang saat SO dilakukan.</p>
                            </article>
                            <article class="so-item-step">
                                <h3>2. Tandai selisih</h3>
                                <p>Sistem menandai item yang berbeda dari stok aplikasi untuk memudahkan review.</p>
                            </article>
                            <article class="so-item-step">
                                <h3>3. Isi remarks</h3>
                                <p>Remarks wajib untuk semua item selisih karena akan masuk ke BA kronologi.</p>
                            </article>
                            <article class="so-item-step">
                                <h3>4. Upload BA signed</h3>
                                <p>Setelah BA dicetak dan ditandatangani, file signed menjadi lampiran approval.</p>
                            </article>
                            <article class="so-item-step">
                                <h3>5. Auto-adjust</h3>
                                <p>Adjustment IN atau OUT dijalankan setelah status approval BA dinyatakan selesai.</p>
                            </article>
                        </div>
                    </section>

                    <div class="so-item-alert">
                        <strong>Catatan workflow:</strong>
                        <?php if ($isView) { ?>
                            Halaman ini menampilkan hasil SO yang sudah tersimpan. Jika masih ada selisih, data tersebut seharusnya ditindaklanjuti dengan BA kronologi sebelum adjustment dijalankan.
                        <?php } else { ?>
                            Saat Anda menyimpan data, sistem akan menolak item selisih yang belum memiliki remarks. Ini sengaja dibuat supaya BA kronologi nanti sudah punya dasar penjelasan per item.
                        <?php } ?>
                    </div>

                    <?php if (!empty($soKotaData)) { ?>
                        <?php
                        $statusText = strtoupper(trim((string) ($soKotaData['sok_status'] ?? 'NOT YET')));
                        $statusClass = 'so-item-status-badge--warning';
                        if (in_array($statusText, ['DONE', 'ADJUSTED', 'CLOSED'], true)) {
                            $statusClass = 'so-item-status-badge--done';
                        } elseif (in_array($statusText, ['BA DRAFT', 'WAITING APPROVAL', 'APPROVED'], true)) {
                            $statusClass = 'so-item-status-badge--info';
                        }
                        ?>
                        <section class="so-item-ba">
                            <div class="so-item-ba__head">
                                <div>
                                    <h2 class="so-item-ba__title">BA kronologi, upload signed, dan approval</h2>
                                    <p class="so-item-ba__desc">Panel ini melanjutkan item selisih menjadi dokumen BA, upload file signed, lalu approval yang langsung membuat adjustment otomatis.</p>
                                </div>
                                <span class="so-item-status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                            <div class="so-item-ba__body">
                                <div class="so-item-ba__grid">
                                    <div class="so-item-ba__panel">
                                        <strong>Ringkasan BA</strong>
                                        <div class="so-item-ba__meta">
                                            <div class="so-item-ba__meta-box">
                                                <span class="so-item-ba__meta-label">Item Selisih</span>
                                                <div class="so-item-ba__meta-value"><?= number_format(count($discrepancyItems), 0, ',', '.') ?></div>
                                            </div>
                                            <div class="so-item-ba__meta-box">
                                                <span class="so-item-ba__meta-label">Status BA</span>
                                                <div class="so-item-ba__meta-value"><?= !empty($baData['ba_status']) ? htmlspecialchars((string) $baData['ba_status']) : 'Belum dibuat' ?></div>
                                            </div>
                                            <div class="so-item-ba__meta-box">
                                                <span class="so-item-ba__meta-label">Nomor BA</span>
                                                <div class="so-item-ba__meta-value"><?= !empty($baData['nomor_ba']) ? htmlspecialchars((string) $baData['nomor_ba']) : '-' ?></div>
                                            </div>
                                            <div class="so-item-ba__meta-box">
                                                <span class="so-item-ba__meta-label">File Signed</span>
                                                <div class="so-item-ba__meta-value">
                                                    <?php if (!empty($baData['ba_file_signed'])) { ?>
                                                        <a href="<?= base_url($baData['ba_file_signed']) ?>" target="_blank">Lihat File</a>
                                                    <?php } else { ?>
                                                        Belum diupload
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="so-item-ba__actions">
                                            <?php if (!empty($discrepancyItems)) { ?>
                                                <a href="<?= base_url('StockOpname/generateBA/' . $id_sop . '/' . $locationId) ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-file-alt mr-1"></i> Generate / Refresh BA
                                                </a>
                                            <?php } ?>

                                            <?php if (!empty($baData)) { ?>
                                                <a href="<?= base_url('StockOpname/print_ba/' . $id_sop . '/' . $locationId) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-print mr-1"></i> Print BA
                                                </a>
                                            <?php } ?>
                                        </div>

                                        <?php if (!empty($discrepancyItems)) { ?>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-bordered so-item-ba__table">
                                                    <thead>
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Stok App</th>
                                                            <th>Stok Fisik</th>
                                                            <th>Selisih</th>
                                                            <th>Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($discrepancyItems as $item) { ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars((string) ($item['nama_item'] ?? $item['id_kode_item'])) ?></td>
                                                                <td><?= number_format((float) ($item['soi_stok_asli'] ?? 0), 0, ',', '.') ?></td>
                                                                <td><?= number_format((float) ($item['soi_stok_opname'] ?? 0), 0, ',', '.') ?></td>
                                                                <td><?= number_format((float) ($item['soi_selisih'] ?? 0), 0, ',', '.') ?></td>
                                                                <td><?= htmlspecialchars((string) (($item['soi_remarks'] ?? '') !== '' ? $item['soi_remarks'] : ($item['soi_keterangan'] ?? '-'))) ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="so-item-ba__panel">
                                        <strong>Tindak lanjut dokumen</strong>

                                        <?php if (!empty($baData)) { ?>
                                            <form action="<?= base_url('StockOpname/uploadSignedBA') ?>" method="post" enctype="multipart/form-data" class="mt-3">
                                                <input type="hidden" name="id_so_ba" value="<?= (int) $baData['id_so_ba'] ?>">
                                                <input type="hidden" name="id_sop" value="<?= (int) $id_sop ?>">
                                                <input type="hidden" name="id_lokasi_gudang" value="<?= (int) $locationId ?>">
                                                <input type="hidden" name="redirect_target" value="<?= htmlspecialchars($redirectTarget, ENT_QUOTES, 'UTF-8') ?>">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Upload BA Signed</label>
                                                    <input type="file" name="ba_file_signed" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                                                    <small class="form-text text-muted">Gunakan PDF atau foto hasil scan tanda tangan basah.</small>
                                                </div>
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-upload mr-1"></i> Upload Signed BA
                                                </button>
                                            </form>

                                            <?php if (!empty($baData['ba_file_signed']) && strtoupper((string) ($soKotaData['sok_status'] ?? '')) !== 'ADJUSTED') { ?>
                                                <form action="<?= base_url('StockOpname/approveBA') ?>" method="post" class="mt-4">
                                                    <input type="hidden" name="id_so_ba" value="<?= (int) $baData['id_so_ba'] ?>">
                                                    <input type="hidden" name="id_sop" value="<?= (int) $id_sop ?>">
                                                    <input type="hidden" name="id_lokasi_gudang" value="<?= (int) $locationId ?>">
                                                    <input type="hidden" name="redirect_target" value="<?= htmlspecialchars($redirectTarget, ENT_QUOTES, 'UTF-8') ?>">
                                                    <div class="form-group">
                                                        <label class="font-weight-bold">Catatan Approval</label>
                                                        <textarea name="approval_note" rows="3" class="form-control" placeholder="Isi catatan approval jika diperlukan"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check mr-1"></i> Approve dan Auto Adjust
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <div class="mt-3 text-muted">Generate BA dulu agar area bisa mencetak dokumen, upload file signed, lalu melanjutkan approval.</div>
                                        <?php } ?>

                                        <div class="mt-4">
                                            <strong>Riwayat status</strong>
                                            <?php if (!empty($approvalLogs)) { ?>
                                                <ol class="so-item-log-list mt-3">
                                                    <?php foreach ($approvalLogs as $log) { ?>
                                                        <li>
                                                            <strong><?= htmlspecialchars((string) ($log['status_to'] ?? '-')) ?></strong>
                                                            pada <?= !empty($log['action_at']) ? date('d M Y H:i', strtotime($log['action_at'])) : '-' ?>
                                                            oleh <?= htmlspecialchars((string) ($log['nama_user'] ?? 'System')) ?>
                                                            <?php if (!empty($log['action_note'])) { ?>
                                                                <br><?= htmlspecialchars((string) $log['action_note']) ?>
                                                            <?php } ?>
                                                        </li>
                                                    <?php } ?>
                                                </ol>
                                            <?php } else { ?>
                                                <div class="mt-3 text-muted">Belum ada riwayat status untuk area ini.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php } ?>

                    <section class="so-item-table">
                        <div class="so-item-table__head">
                            <div>
                                <h2 class="so-item-table__title">Ledger stock opname per item</h2>
                                <p class="so-item-table__desc">Stok aplikasi diambil sebagai pembanding, lalu area mengisi stok fisik. Tabel ini tetap mempertahankan flow input lama, hanya tampilannya yang dirapikan.</p>
                            </div>
                        </div>
                        <div class="so-item-table__body">
                            <?php if (!empty($rows)) { ?>
                                <?php if ($isInput || $isEdit) { ?>
                                    <form action="<?= base_url('StockOpname/inputSO') ?>" method="post" id="form-stockopname-revamp">
                                        <input type="hidden" name="id_sop" value="<?= $id_sop ?>">
                                        <input type="hidden" name="id_lokasi_gudang" value="<?= $locationId ?>">
                                        <input type="hidden" name="redirect_target" value="<?= $redirectTarget ?>">
                                        <?php if ($isEdit) { ?>
                                            <input type="hidden" name="is_edit" value="1">
                                        <?php } ?>
                                <?php } ?>

                                <div class="table-responsive">
                                    <table class="table mb-0" id="table-stockopname-item-revamp">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Project</th>
                                                <th>Kode Item</th>
                                                <th>Kategori</th>
                                                <th>Nama Item</th>
                                                <th>Satuan</th>
                                                <th>Stok Aplikasi</th>
                                                <th>Stok Fisik</th>
                                                <th>Selisih</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rows as $index => $row) { ?>
                                                <?php
                                                $stokAplikasi = (float) ($isInput ? ($row['total_jumlah_stok'] ?? 0) : ($row['soi_stok_asli'] ?? 0));
                                                $stokFisik = $isInput ? null : (float) ($row['soi_stok_opname'] ?? 0);
                                                $selisih = $stokFisik === null
                                                    ? 0
                                                    : (isset($row['soi_selisih']) && $row['soi_selisih'] !== null
                                                        ? (float) $row['soi_selisih']
                                                        : ($stokAplikasi - $stokFisik));
                                                $hasSelisih = $stokFisik !== null && $selisih != 0;
                                                $rowClass = $stokFisik === null ? '' : ($hasSelisih ? 'so-item-row--selisih' : 'so-item-row--sinkron');
                                                $remarksValue = (($row['soi_remarks'] ?? '') !== '') ? $row['soi_remarks'] : ($row['soi_keterangan'] ?? '');
                                                ?>
                                                <tr
                                                    class="js-stock-row <?= $rowClass ?>"
                                                    data-stok-aplikasi="<?= $stokAplikasi ?>"
                                                    data-stok-fisik="<?= $stokFisik === null ? '' : $stokFisik ?>">
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= $row['project_item'] ?? '-' ?></td>
                                                    <td>
                                                        <?= $row['id_kode_item'] ?>
                                                        <?php if ($isInput || $isEdit) { ?>
                                                            <input type="hidden" name="id_kode_item[<?= $index ?>]" value="<?= $row['id_kode_item'] ?>">
                                                        <?php } ?>
                                                    </td>
                                                    <td><?= $row['kategori_item'] ?? '-' ?></td>
                                                    <td><?= $row['nama_item'] ?? '-' ?></td>
                                                    <td><?= $row['satuan_item'] ?? '-' ?></td>
                                                    <td>
                                                        <?= number_format($stokAplikasi, 0, ',', '.') ?>
                                                        <?php if ($isInput) { ?>
                                                            <input type="hidden" name="total_jumlah_stok[<?= $index ?>]" value="<?= $stokAplikasi ?>">
                                                        <?php } elseif ($isEdit) { ?>
                                                            <input type="hidden" name="soi_stok_asli[<?= $index ?>]" value="<?= $stokAplikasi ?>">
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($isView) { ?>
                                                            <?= number_format($stokFisik, 0, ',', '.') ?>
                                                        <?php } else { ?>
                                                            <input
                                                                type="number"
                                                                class="form-control js-stok-fisik"
                                                                name="<?= $isInput ? 'stok_so' : 'soi_stok_opname' ?>[<?= $index ?>]"
                                                                value="<?= $isEdit ? (int) $stokFisik : '' ?>"
                                                                min="0"
                                                                placeholder="0">
                                                        <?php } ?>
                                                    </td>
                                                    <td class="so-item-discrepancy js-selisih-cell <?= $hasSelisih ? 'so-item-discrepancy--minus' : 'so-item-discrepancy--zero' ?>">
                                                        <?= $stokFisik === null ? '-' : number_format($selisih, 0, ',', '.') ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($isView) { ?>
                                                            <?= $remarksValue !== '' ? $remarksValue : '-' ?>
                                                        <?php } else { ?>
                                                            <input
                                                                type="text"
                                                                class="form-control js-remarks <?= $hasSelisih ? 'so-item-remarks-required' : '' ?>"
                                                                name="keterangan[<?= $index ?>]"
                                                                value="<?= htmlspecialchars($remarksValue, ENT_QUOTES, 'UTF-8') ?>"
                                                                placeholder="<?= $hasSelisih ? 'Remarks wajib untuk item selisih' : ($isInput ? 'Isi setelah stok fisik terdeteksi selisih' : 'Keterangan opsional') ?>">
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6">TOTAL</td>
                                                <td id="tfoot-stok-aplikasi"><?= number_format($totalStokAplikasi, 0, ',', '.') ?></td>
                                                <td id="tfoot-stok-fisik"><?= number_format($totalStokFisik, 0, ',', '.') ?></td>
                                                <td id="tfoot-selisih"><?= number_format($totalSelisihNominal, 0, ',', '.') ?></td>
                                                <td id="tfoot-selisih-item"><?= number_format($totalSelisihItem, 0, ',', '.') ?> item selisih</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <?php if ($isInput || $isEdit) { ?>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary font-weight-bold" id="btn-submit-stockopname-revamp">
                                            <i class="fas fa-save mr-1"></i>
                                            <?= $isEdit ? 'Simpan Edit Stock Opname' : 'Simpan Stock Opname' ?>
                                        </button>
                                    </div>
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="so-item-empty">
                                    Tidak ada item stok yang bisa ditampilkan untuk area ini. Periksa apakah area memang belum memiliki saldo stok pada snapshot pembanding.
                                </div>
                            <?php } ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successMessage = <?= json_encode($flashSuccess) ?>;
        var errorMessage = <?= json_encode($flashError) ?>;
        var statusFlag = <?= json_encode($status) ?>;
        var noticeKey = <?= json_encode('stockopname-revamp-soitem|' . md5((string) $status . '|' . (string) $flashSuccess . '|' . (string) $flashError . '|' . (string) $id_sop . '|' . (string) $locationId . '|' . (string) $mode)) ?>;

        function showNotice(title, text, icon) {
            try {
                if (window.sessionStorage && text && sessionStorage.getItem(noticeKey) === 'shown') {
                    return;
                }
            } catch (error) {
                console.error('sessionStorage notice item gagal dibaca:', error);
            }

            try {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({ title: title, text: text, icon: icon });
                    if (window.sessionStorage && text) {
                        sessionStorage.setItem(noticeKey, 'shown');
                    }
                    return;
                }
            } catch (error) {
                console.error('Swal.fire StockOpname revamp item gagal dijalankan:', error);
            }

            try {
                if (typeof window.swal === 'function') {
                    window.swal(title, text, icon);
                    if (window.sessionStorage && text) {
                        sessionStorage.setItem(noticeKey, 'shown');
                    }
                    return;
                }
            } catch (error) {
                console.error('swal StockOpname revamp item gagal dijalankan:', error);
            }

            console.warn('SweetAlert tidak tersedia untuk notifikasi StockOpname:', title, text, icon);
        }

        if (successMessage) {
            showNotice('Success!', successMessage, 'success');
        } else if (errorMessage) {
            showNotice('Gagal!', errorMessage, 'warning');
        } else if (statusFlag === 'sukses_edit') {
            showNotice('Success!', 'Data stock opname berhasil diperbarui.', 'success');
        }

        function formatNumber(value) {
            var number = typeof value === 'number' ? value : parseInt(value || 0, 10);
            if (isNaN(number)) {
                number = 0;
            }

            var sign = number < 0 ? '-' : '';
            var absolute = Math.abs(number).toString();
            return sign + absolute.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        var rows = document.querySelectorAll('.js-stock-row');

        function recalculateRows() {
            var totalApp = 0;
            var totalFisik = 0;
            var totalSelisih = 0;
            var totalSelisihItem = 0;

            Array.prototype.forEach.call(rows, function(row) {
                var stokAplikasi = parseInt(row.getAttribute('data-stok-aplikasi') || '0', 10);
                var stokFisikData = row.getAttribute('data-stok-fisik');
                var fisikInput = row.querySelector('.js-stok-fisik');
                var remarksInput = row.querySelector('.js-remarks');
                var selisihCell = row.querySelector('.js-selisih-cell');
                var hasValue = fisikInput ? fisikInput.value.trim() !== '' : stokFisikData !== null && stokFisikData !== '';
                var stokFisik = fisikInput ? parseInt(fisikInput.value || '0', 10) : parseInt(stokFisikData || '0', 10);
                var selisih = hasValue ? (stokAplikasi - stokFisik) : 0;
                var hasSelisih = hasValue && selisih !== 0;

                totalApp += stokAplikasi;
                totalFisik += hasValue ? stokFisik : 0;
                totalSelisih += hasValue ? selisih : 0;
                if (hasSelisih) {
                    totalSelisihItem++;
                }

                if (selisihCell) {
                    selisihCell.textContent = hasValue ? formatNumber(selisih) : '-';
                    selisihCell.classList.remove('so-item-discrepancy--minus', 'so-item-discrepancy--plus', 'so-item-discrepancy--zero');
                    selisihCell.classList.add(hasSelisih ? 'so-item-discrepancy--minus' : 'so-item-discrepancy--zero');
                }

                row.classList.toggle('so-item-row--selisih', hasSelisih);
                row.classList.toggle('so-item-row--sinkron', hasValue && !hasSelisih);

                if (remarksInput) {
                    remarksInput.placeholder = hasSelisih ? 'Remarks wajib untuk item selisih' : (hasValue ? 'Keterangan opsional' : 'Isi setelah stok fisik terdeteksi selisih');
                    remarksInput.classList.toggle('so-item-remarks-required', hasSelisih && !remarksInput.value.trim());
                }
            });

            var metricStokAplikasi = document.getElementById('metric-stok-aplikasi');
            var metricStokFisik = document.getElementById('metric-stok-fisik');
            var metricSelisihItem = document.getElementById('metric-selisih-item');
            var tfootStokAplikasi = document.getElementById('tfoot-stok-aplikasi');
            var tfootStokFisik = document.getElementById('tfoot-stok-fisik');
            var tfootSelisih = document.getElementById('tfoot-selisih');
            var tfootSelisihItem = document.getElementById('tfoot-selisih-item');

            if (metricStokAplikasi) metricStokAplikasi.textContent = formatNumber(totalApp);
            if (metricStokFisik) metricStokFisik.textContent = formatNumber(totalFisik);
            if (metricSelisihItem) metricSelisihItem.textContent = formatNumber(totalSelisihItem);
            if (tfootStokAplikasi) tfootStokAplikasi.textContent = formatNumber(totalApp);
            if (tfootStokFisik) tfootStokFisik.textContent = formatNumber(totalFisik);
            if (tfootSelisih) tfootSelisih.textContent = formatNumber(totalSelisih);
            if (tfootSelisihItem) tfootSelisihItem.textContent = formatNumber(totalSelisihItem) + ' item selisih';
        }

        Array.prototype.forEach.call(document.querySelectorAll('.js-stok-fisik'), function(input) {
            input.addEventListener('input', recalculateRows);
        });

        Array.prototype.forEach.call(document.querySelectorAll('.js-remarks'), function(input) {
            input.addEventListener('input', function() {
                input.classList.toggle('so-item-remarks-required', input.classList.contains('so-item-remarks-required') && !input.value.trim());
                recalculateRows();
            });
        });

        var form = document.getElementById('form-stockopname-revamp');
        var submitButton = document.getElementById('btn-submit-stockopname-revamp');

        function hasInvalidRemarksState() {
            var hasInvalidRemarks = false;

            Array.prototype.forEach.call(rows, function(row) {
                var fisikInput = row.querySelector('.js-stok-fisik');
                var remarksInput = row.querySelector('.js-remarks');
                if (!fisikInput || !remarksInput) {
                    return;
                }

                var stokAplikasi = parseInt(row.getAttribute('data-stok-aplikasi') || '0', 10);
                var hasValue = fisikInput.value.trim() !== '';
                var stokFisik = parseInt(fisikInput.value || '0', 10);
                var selisih = hasValue ? (stokAplikasi - stokFisik) : 0;
                var needsRemarks = hasValue && selisih !== 0;
                var remarks = remarksInput.value.trim();

                remarksInput.classList.toggle('so-item-remarks-required', needsRemarks && remarks === '');
                if (needsRemarks && remarks === '') {
                    hasInvalidRemarks = true;
                }
            });

            if (submitButton) {
                submitButton.disabled = hasInvalidRemarks;
                submitButton.classList.toggle('disabled', hasInvalidRemarks);
            }

            return hasInvalidRemarks;
        }

        if (form) {
            form.addEventListener('submit', function(event) {
                if (hasInvalidRemarksState()) {
                    event.preventDefault();
                    showNotice('Tidak Bisa Disubmit', 'Remarks wajib diisi untuk setiap item yang memiliki selisih stok.', 'warning');
                }
            });
        }

        Array.prototype.forEach.call(document.querySelectorAll('.js-stok-fisik, .js-remarks'), function(input) {
            input.addEventListener('input', hasInvalidRemarksState);
            input.addEventListener('change', hasInvalidRemarksState);
        });

        if (rows.length > 0) {
            recalculateRows();
            hasInvalidRemarksState();
        }
    });
</script>
