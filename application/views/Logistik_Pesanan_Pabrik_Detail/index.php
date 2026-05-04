<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

$totalQtyPo = array_sum(array_map('floatval', array_column($poItems, 'qty_po')));
$totalQtyKirim = array_sum(array_map('floatval', array_column($poItems, 'qty_terkirim')));
$totalOutstanding = array_sum(array_map('floatval', array_column($poItems, 'outstanding_pengiriman')));
$totalNominal = array_sum(array_map('floatval', array_column($poItems, 'total_nominal_detail')));
$statusLabel = strtoupper((string) ($poHeader['status_po'] ?? 'APPROVED'));
?>

<style>
    .pod-revamp {
        --pod-ink: #0f172a;
        --pod-muted: #64748b;
        --pod-line: rgba(148, 163, 184, 0.22);
        --pod-surface: rgba(255, 255, 255, 0.96);
        --pod-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
    }

    .pod-revamp .content-header { padding-bottom: 0; }
    .pod-shell { padding: 1.15rem; }

    .pod-hero {
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

    .pod-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .pod-hero__eyebrow {
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

    .pod-hero h1 {
        margin: 1rem 0 0.6rem;
        font-size: 1.9rem;
        font-weight: 800;
        color: #fff;
    }

    .pod-hero p {
        max-width: 44rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .pod-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .pod-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.82rem 1.15rem;
        border: 0;
        border-radius: 14px;
        font-weight: 700;
        transition: transform 0.18s ease;
    }

    .pod-btn:hover { transform: translateY(-1px); text-decoration: none; }
    .pod-btn--light { background: #f8fafc; color: #0f172a; box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16); }

    .pod-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .pod-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .pod-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .pod-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .pod-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .pod-alert { margin-top: 1rem; border: 0; border-radius: 18px; padding: 0.95rem 1rem; }

    .pod-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .pod-panel, .pod-table-shell {
        border: 1px solid var(--pod-line);
        border-radius: 24px;
        background: var(--pod-surface);
        box-shadow: var(--pod-shadow);
        overflow: hidden;
    }

    .pod-panel__head, .pod-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .pod-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--pod-ink);
    }

    .pod-panel__subtitle {
        margin: 0.28rem 0 0;
        color: var(--pod-muted);
        font-size: 0.92rem;
    }

    .pod-panel__body, .pod-table-wrap {
        padding: 1.25rem;
    }

    .pod-overview {
        display: grid;
        gap: 0.8rem;
    }

    .pod-overview__item {
        padding: 0.95rem 1rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
    }

    .pod-overview__label {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .pod-overview__value {
        display: block;
        margin-top: 0.45rem;
        color: var(--pod-ink);
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.55;
        word-break: break-word;
    }

    .pod-chip {
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

    .pod-chip--blue { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
    .pod-chip--approved { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .pod-chip--waiting { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .pod-chip--slate { background: rgba(15, 23, 42, 0.06); color: #334155; }

    .pod-table-wrap { width: 100%; overflow-x: auto; }
    .pod-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pod-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .pod-table th, .pod-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .pod-table tbody tr:hover { background: rgba(239, 246, 255, 0.7); }
    .pod-table tfoot td { background: #f8fafc; color: #0f172a; font-weight: 800; }

    .pod-empty {
        padding: 1rem;
        border-radius: 16px;
        text-align: center;
        background: rgba(248, 250, 252, 0.8);
        border: 1px dashed rgba(148, 163, 184, 0.36);
        color: #64748b;
    }

    @media (max-width: 1199.98px) {
        .pod-hero__grid, .pod-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .pod-shell { padding: 0.8rem; }
        .pod-hero__grid { padding: 1rem; }
        .pod-hero h1 { font-size: 1.5rem; }
        .pod-metric-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="content-wrapper pod-revamp">
    <div class="content-header">
        <div class="container-fluid pod-shell">
            <section class="pod-hero">
                <div class="pod-hero__grid">
                    <div>
                        <span class="pod-hero__eyebrow">
                            <i class="fas fa-file-invoice-dollar"></i>
                            PO Detail
                        </span>
                        <h1><?= $poHeader['nomor_po_pabrik'] ?></h1>
                        <p>
                            Halaman ini merangkum header PO, rincian item, dan histori pengiriman dari pabrik. Outstanding dihitung dari detail PO
                            terhadap total pengiriman yang sudah tercatat.
                        </p>
                        <div class="pod-hero__actions">
                            <a href="<?= base_url('Logistik_Pesanan_Pabrik') ?>" class="pod-btn pod-btn--light">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke List PO
                            </a>
                            <?php if ($totalOutstanding > 0): ?>
                                <button type="button" class="pod-btn pod-btn--light" data-toggle="modal" data-target="#modalCreateDelivery">
                                    <i class="fas fa-truck-loading"></i>
                                    Input Pengiriman Pabrik
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="pod-metric-grid">
                        <div class="pod-metric">
                            <span class="pod-metric__label">Total Qty PO</span>
                            <span class="pod-metric__value"><?= number_format($totalQtyPo, 0, ',', '.') ?></span>
                            <span class="pod-metric__hint">Akumulasi qty seluruh item dalam dokumen PO.</span>
                        </div>
                        <div class="pod-metric">
                            <span class="pod-metric__label">Total Terkirim</span>
                            <span class="pod-metric__value"><?= number_format($totalQtyKirim, 0, ',', '.') ?></span>
                            <span class="pod-metric__hint">Total qty yang sudah tercatat dalam histori pengiriman.</span>
                        </div>
                        <div class="pod-metric">
                            <span class="pod-metric__label">Outstanding</span>
                            <span class="pod-metric__value"><?= number_format($totalOutstanding, 0, ',', '.') ?></span>
                            <span class="pod-metric__hint">Sisa volume yang belum tertutup oleh pengiriman.</span>
                        </div>
                        <div class="pod-metric">
                            <span class="pod-metric__label">Total Nominal</span>
                            <span class="pod-metric__value"><?= number_format($totalNominal, 0, ',', '.') ?></span>
                            <span class="pod-metric__hint">Penjumlahan nominal seluruh detail item pada PO.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success pod-alert"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger pod-alert"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="pod-grid">
                <section class="pod-panel">
                    <div class="pod-panel__head">
                        <div>
                            <h2 class="pod-panel__title">Ringkasan Dokumen</h2>
                            <p class="pod-panel__subtitle">Informasi utama PO sebagai dasar monitoring dan follow up pengiriman.</p>
                        </div>
                        <span class="pod-chip pod-chip--blue"><i class="fas fa-id-card"></i> Header PO</span>
                    </div>
                    <div class="pod-panel__body">
                        <div class="pod-overview">
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Nomor PO</span>
                                <span class="pod-overview__value"><?= $poHeader['nomor_po_pabrik'] ?></span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Tanggal PO</span>
                                <span class="pod-overview__value"><?= $poHeader['tanggal_po_pabrik'] ?></span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Pabrik</span>
                                <span class="pod-overview__value"><?= $poHeader['nama_pabrik'] ?: '-' ?></span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Referensi PR</span>
                                <span class="pod-overview__value"><?= $poHeader['nomor_purchase_request'] ?: '-' ?></span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Status PO</span>
                                <span class="pod-overview__value">
                                    <span class="pod-chip <?= $totalOutstanding <= 0 ? 'pod-chip--approved' : 'pod-chip--waiting' ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Dokumen PO</span>
                                <span class="pod-overview__value"><?= empty($poHeader['purchase_order_document']) ? '-' : $poHeader['purchase_order_document'] ?></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pod-panel">
                    <div class="pod-panel__head">
                        <div>
                            <h2 class="pod-panel__title">Ringkasan Pengiriman</h2>
                            <p class="pod-panel__subtitle">Baca cepat kondisi PO: siap kirim, partial, atau selesai.</p>
                        </div>
                        <span class="pod-chip <?= $totalOutstanding <= 0 ? 'pod-chip--approved' : 'pod-chip--waiting' ?>">
                            <i class="fas fa-truck"></i>
                            <?= $totalOutstanding <= 0 ? 'Completed' : ($totalQtyKirim > 0 ? 'Partial Delivery' : 'Waiting Delivery') ?>
                        </span>
                    </div>
                    <div class="pod-panel__body">
                        <div class="pod-overview">
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Jumlah Item</span>
                                <span class="pod-overview__value"><?= number_format(count($poItems), 0, ',', '.') ?> item</span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Jumlah Pengiriman</span>
                                <span class="pod-overview__value"><?= number_format(count(array_unique(array_column($poDeliveries, 'id_pengiriman_pabrik'))), 0, ',', '.') ?> dokumen pengiriman</span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Qty Terkirim</span>
                                <span class="pod-overview__value"><?= number_format($totalQtyKirim, 0, ',', '.') ?></span>
                            </div>
                            <div class="pod-overview__item">
                                <span class="pod-overview__label">Outstanding</span>
                                <span class="pod-overview__value"><?= number_format($totalOutstanding, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="pod-table-shell" style="margin-top: 1.2rem;">
                <div class="pod-table-head">
                    <div>
                        <h2 class="pod-panel__title">Rincian Item PO</h2>
                        <p class="pod-panel__subtitle">Item-level monitoring untuk qty PO, qty terkirim, dan outstanding tiap material.</p>
                    </div>
                    <span class="pod-chip pod-chip--blue"><i class="fas fa-boxes"></i> Detail PO</span>
                </div>
                <div class="pod-table-wrap">
                    <?php if (!empty($poItems)): ?>
                        <table class="table pod-table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Item</th>
                                    <th>Satuan</th>
                                    <th>Volume Planning</th>
                                    <th>Qty PO</th>
                                    <th>Qty Terkirim</th>
                                    <th>Outstanding</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $number = 1; ?>
                                <?php foreach ($poItems as $item): ?>
                                    <tr>
                                        <td><?= $number++ ?></td>
                                        <td><?= $item['nama_item'] ?: '-' ?></td>
                                        <td><?= $item['satuan_item'] ?: '-' ?></td>
                                        <td><?= $item['volume_planning_snapshot'] !== null ? number_format($item['volume_planning_snapshot'], 0, ',', '.') : '-' ?></td>
                                        <td><?= number_format($item['qty_po'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($item['qty_terkirim'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($item['outstanding_pengiriman'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($item['harga_item'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($item['total_nominal_detail'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">TOTAL</td>
                                    <td><?= number_format($totalQtyPo, 0, ',', '.') ?></td>
                                    <td><?= number_format($totalQtyKirim, 0, ',', '.') ?></td>
                                    <td><?= number_format($totalOutstanding, 0, ',', '.') ?></td>
                                    <td></td>
                                    <td><?= number_format($totalNominal, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else: ?>
                        <div class="pod-empty">Belum ada item detail untuk PO ini.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="pod-table-shell" style="margin-top: 1.2rem;">
                <div class="pod-table-head">
                    <div>
                        <h2 class="pod-panel__title">Histori Pengiriman</h2>
                        <p class="pod-panel__subtitle">Daftar pengiriman pabrik yang terkait dengan PO ini.</p>
                    </div>
                    <span class="pod-chip pod-chip--blue"><i class="fas fa-truck-loading"></i> Delivery</span>
                </div>
                <div class="pod-table-wrap">
                    <?php if (!empty($poDeliveries)): ?>
                        <table class="table pod-table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Kirim</th>
                                    <th>No Surat Jalan</th>
                                    <th>Lokasi Gudang</th>
                                    <th>Item</th>
                                    <th>Qty Kirim</th>
                                    <th>Qty Diterima</th>
                                    <th>SJ Pabrik</th>
                                    <th>SJ Kantor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $number = 1; ?>
                                <?php foreach ($poDeliveries as $delivery): ?>
                                    <tr>
                                        <td><?= $number++ ?></td>
                                        <td><?= $delivery['tanggal_pengiriman_pabrik'] ?: '-' ?></td>
                                        <td><?= $delivery['no_surat_jalan'] ?: '-' ?></td>
                                        <td><?= $delivery['kota_lokasi_gudang'] ?: '-' ?></td>
                                        <td><?= $delivery['nama_item'] ?: '-' ?></td>
                                        <td><?= number_format($delivery['qty_kirim'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($delivery['qty_diterima'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= $delivery['surat_jalan_pabrik'] ?: '-' ?></td>
                                        <td><?= $delivery['surat_jalan_ho'] ?: '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="pod-empty">Belum ada histori pengiriman untuk PO ini.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php if (!empty($poItems)): ?>
    <div class="modal fade" id="modalCreateDelivery" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:24px;overflow:hidden;">
                <form action="<?= base_url('Logistik_Pesanan_Pabrik_Detail/create_delivery') ?>" method="post" id="form-create-delivery">
                    <div class="modal-header text-white" style="border-bottom:0;background:linear-gradient(135deg, #0f172a, #1d4ed8);">
                        <div>
                            <h4 class="modal-title mb-1">Input Pengiriman Pabrik</h4>
                            <small>Pilih item outstanding yang benar-benar dikirim, lalu sistem akan otomatis menambah stok masuk logistik.</small>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background:linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));">
                        <input type="hidden" name="nomor_po_pabrik" value="<?= $poHeader['nomor_po_pabrik'] ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor Surat Jalan</label>
                                    <input type="text" name="no_surat_jalan" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Pengiriman</label>
                                    <input type="date" name="tanggal_pengiriman_pabrik" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gudang Tujuan</label>
                                    <select name="id_lokasi_gudang" class="form-control" required>
                                        <option value="">Pilih Gudang Tujuan</option>
                                        <?php foreach ($gudangOptions as $gudang): ?>
                                            <option value="<?= $gudang['id_lokasi_gudang'] ?>">
                                                <?= $gudang['regional_lokasi_gudang'] ?> | <?= $gudang['kota_lokasi_gudang'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Referensi SJ Pabrik</label>
                                    <input type="text" name="surat_jalan_pabrik" class="form-control" placeholder="Nama file / nomor">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Referensi SJ HO</label>
                                    <input type="text" name="surat_jalan_ho" class="form-control" placeholder="Nama file / nomor">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Nama Item</th>
                                        <th>Satuan</th>
                                        <th>Qty PO</th>
                                        <th>Qty Terkirim</th>
                                        <th>Outstanding</th>
                                        <th>Qty Kirim Sekarang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($poItems as $index => $item): ?>
                                        <?php $outstandingQty = (float) ($item['outstanding_pengiriman'] ?? 0); ?>
                                        <?php if ($outstandingQty <= 0) {
                                            continue;
                                        } ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_item[]" value="<?= $index ?>" checked>
                                                <input type="hidden" name="id_pesanan_pabrik_detail[<?= $index ?>]" value="<?= $item['id_pesanan_pabrik_detail'] ?>">
                                            </td>
                                            <td><?= $item['nama_item'] ?: '-' ?></td>
                                            <td><?= $item['satuan_item'] ?: '-' ?></td>
                                            <td><?= number_format($item['qty_po'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= number_format($item['qty_terkirim'] ?? 0, 0, ',', '.') ?></td>
                                            <td><strong><?= number_format($outstandingQty, 0, ',', '.') ?></strong></td>
                                            <td>
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    name="qty_kirim[<?= $index ?>]"
                                                    value="<?= (int) $outstandingQty ?>"
                                                    min="0"
                                                    max="<?= (int) $outstandingQty ?>"
                                                    step="1"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Pengiriman</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    $(document).ready(function() {
        $('#form-create-delivery').on('submit', function(e) {
            const checkedItems = $('#form-create-delivery tbody input[type="checkbox"]:checked').length;
            if (!checkedItems) {
                e.preventDefault();
                Swal.fire('Item belum dipilih', 'Pilih minimal satu item outstanding untuk dibuatkan pengiriman.', 'warning');
                return;
            }

            let hasInvalidQty = false;
            $('#form-create-delivery tbody tr').each(function() {
                const checkbox = $(this).find('input[type="checkbox"]');
                if (!checkbox.is(':checked')) {
                    return;
                }

                const qtyInput = $(this).find('input[name^="qty_kirim["]');
                const qtyValue = parseFloat(qtyInput.val() || 0);
                const maxOutstanding = parseFloat(qtyInput.attr('max') || 0);

                if (qtyValue <= 0 || qtyValue > maxOutstanding) {
                    hasInvalidQty = true;
                }
            });

            if (hasInvalidQty) {
                e.preventDefault();
                Swal.fire('Qty kirim tidak valid', 'Qty kirim tiap item harus lebih dari 0 dan tidak boleh melebihi outstanding PO.', 'warning');
            }
        });
    });
</script>
