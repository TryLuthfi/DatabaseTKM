<?php
$status = $this->session->flashdata('status');

$isFiltered = isset($rincianFilterDashboardLogistik) || isset($getRincianDashboardFilteredBowheer) || isset($getInOutHistoryFiltered);
$categoryLabel = isset($kategori_item) ? strtoupper(trim((string) $kategori_item)) : strtoupper(trim((string) $title));

$summaryRows = $isFiltered ? ($getRincianDashboardFilteredBowheer ?? []) : ($getStokPerBowheer ?? []);
$distributionRows = $isFiltered ? ($rincianFilterDashboardLogistik ?? []) : ($getDistribusiPerBowheer ?? []);
$historyRows = $isFiltered ? ($getInOutHistoryFiltered ?? []) : ($getHistoriInOUtLogistikKategori ?? []);

$summaryRows = array_values(array_filter($summaryRows, function ($row) use ($categoryLabel) {
    return strtoupper(trim((string) $row['kategori_item'])) === $categoryLabel;
}));

$distributionRows = array_values(array_filter($distributionRows, function ($row) use ($categoryLabel) {
    return strtoupper(trim((string) $row['kategori_item'])) === $categoryLabel;
}));

$historyRows = array_values(array_filter($historyRows, function ($row) use ($categoryLabel) {
    return strtoupper(trim((string) $row['kategori_item'])) === $categoryLabel;
}));

$summaryTotal = 0;
foreach ($summaryRows as $row) {
    $summaryTotal += (float) $row['jumlah_stok'];
}

$distributionTotal = 0;
foreach ($distributionRows as $row) {
    $distributionTotal += (float) $row['jumlah_stok'];
}
?>

<style>
    .stock-category-revamp {
        --cat-line: rgba(148, 163, 184, 0.18);
        --cat-surface: rgba(255, 255, 255, 0.94);
        --cat-shadow: 0 24px 52px rgba(15, 23, 42, 0.10);
    }

    .stock-category-shell {
        padding: 1.15rem;
    }

    .stock-category-hero {
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background:
            radial-gradient(circle at top left, rgba(129, 140, 248, 0.22), transparent 24%),
            radial-gradient(circle at bottom right, rgba(56, 189, 248, 0.18), transparent 24%),
            linear-gradient(135deg, #111827 0%, #1e1b4b 45%, #0f4c81 100%);
        box-shadow: 0 30px 72px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .stock-category-hero__grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 1rem;
        padding: 1.4rem;
    }

    .stock-category-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.38rem 0.78rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .stock-category-hero h1 {
        margin: 0.95rem 0 0.7rem;
        color: #fff;
        font-size: 2rem;
        font-weight: 800;
    }

    .stock-category-hero p {
        margin: 0;
        max-width: 46rem;
        line-height: 1.7;
        color: rgba(226, 232, 240, 0.84);
    }

    .stock-category-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.2rem;
    }

    .stock-category-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.82rem 1.1rem;
        border-radius: 14px;
        font-weight: 800;
        border: 0;
        transition: transform 0.18s ease;
    }

    .stock-category-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .stock-category-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.14);
    }

    .stock-category-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .stock-category-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        align-content: start;
    }

    .stock-category-metric {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stock-category-metric__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.42rem;
    }

    .stock-category-metric__value {
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .stock-category-panel {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--cat-line);
        background: var(--cat-surface);
        box-shadow: var(--cat-shadow);
        overflow: hidden;
    }

    .stock-category-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .stock-category-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .stock-category-panel__subtitle {
        margin: 0.28rem 0 0;
        color: #64748b;
        font-size: 0.92rem;
    }

    .stock-category-panel__body {
        padding: 1.2rem;
    }

    .stock-category-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.7rem;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.1);
        color: #4338ca;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stock-category-highlight {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .stock-category-box {
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 22px;
        padding: 1.1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
    }

    .stock-category-box__label {
        display: block;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stock-category-box__value {
        display: block;
        margin-top: 0.45rem;
        color: #0f172a;
        font-size: 1.65rem;
        font-weight: 800;
    }

    .stock-category-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .stock-category-table thead th {
        background: #eef2ff;
        color: #312e81;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(199, 210, 254, 0.85);
    }

    .stock-category-table th,
    .stock-category-table td {
        padding: 0.8rem 0.72rem;
        border-top: 1px solid rgba(226, 232, 240, 0.72);
        vertical-align: middle;
        white-space: nowrap;
    }

    .stock-category-table tbody tr:hover {
        background: rgba(238, 242, 255, 0.7);
    }

    @media (max-width: 1199.98px) {
        .stock-category-hero__grid,
        .stock-category-highlight {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .stock-category-shell {
            padding: 0.8rem;
        }

        .stock-category-hero h1 {
            font-size: 1.55rem;
        }

        .stock-category-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .stock-category-metrics {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper stock-category-revamp">
    <div class="content-header">
        <div class="container-fluid stock-category-shell">
            <section class="stock-category-hero">
                <div class="stock-category-hero__grid">
                    <div>
                        <span class="stock-category-hero__eyebrow">
                            <i class="fas fa-tags"></i>
                            Detail Kategori
                        </span>
                        <h1><?= $categoryLabel ?></h1>
                        <p>
                            Halaman ini merangkum stok kategori per project, distribusi ke area, dan histori IN/OUT.
                            <?php if ($isFiltered): ?>
                                Data yang tampil berasal dari hasil filter dashboard revamp.
                            <?php else: ?>
                                Data yang tampil adalah detail kategori penuh dari modul logistik.
                            <?php endif; ?>
                        </p>
                        <div class="stock-category-actions">
                            <a href="<?= base_url('Dashboard_Logistik_Stok/revamp') ?>" class="stock-category-btn stock-category-btn--light">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="stock-category-metrics">
                        <div class="stock-category-metric">
                            <span class="stock-category-metric__label">Project Aktif</span>
                            <span class="stock-category-metric__value"><?= number_format(count($summaryRows), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-category-metric">
                            <span class="stock-category-metric__label">Distribusi Area</span>
                            <span class="stock-category-metric__value"><?= number_format(count($distributionRows), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-category-metric">
                            <span class="stock-category-metric__label">History Rows</span>
                            <span class="stock-category-metric__value"><?= number_format(count($historyRows), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-category-metric">
                            <span class="stock-category-metric__label">Mode</span>
                            <span class="stock-category-metric__value"><?= $isFiltered ? 'Filtered' : 'Full' ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stock-category-panel">
                <div class="stock-category-panel__head">
                    <div>
                        <span class="stock-category-chip"><i class="fas fa-chart-pie"></i> Snapshot</span>
                        <h2 class="stock-category-panel__title">Ringkasan cepat kategori</h2>
                        <p class="stock-category-panel__subtitle">Sorotan total stok dan distribusi item untuk kategori yang dipilih.</p>
                    </div>
                </div>
                <div class="stock-category-panel__body">
                    <div class="stock-category-highlight">
                        <div class="stock-category-box">
                            <span class="stock-category-box__label">Total Stok Per Project</span>
                            <span class="stock-category-box__value"><?= number_format($summaryTotal, 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-category-box">
                            <span class="stock-category-box__label">Total Distribusi Area</span>
                            <span class="stock-category-box__value"><?= number_format($distributionTotal, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stock-category-panel">
                <div class="stock-category-panel__head">
                    <div>
                        <span class="stock-category-chip"><i class="fas fa-briefcase"></i> Summary Project</span>
                        <h2 class="stock-category-panel__title">Stok kategori per project / bowheer</h2>
                        <p class="stock-category-panel__subtitle">Ringkasan level project untuk kategori yang sedang dibuka.</p>
                    </div>
                </div>
                <div class="stock-category-panel__body">
                    <div class="table-responsive">
                        <table class="table stock-category-table" id="revamp_category_summary_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori Item</th>
                                    <th>Project Item</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Pemilik Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summaryRows as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= trim($data['kategori_item']) ?></td>
                                        <td><?= $data['project_item'] ?></td>
                                        <td><?= number_format((float) $data['jumlah_stok'], 0, ',', '.') ?></td>
                                        <td><?= $data['satuan_item'] ?></td>
                                        <td><?= $data['nama_bowheer'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th id="revamp_category_summary_total">0</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <section class="stock-category-panel">
                <div class="stock-category-panel__head">
                    <div>
                        <span class="stock-category-chip"><i class="fas fa-map-signs"></i> Distribusi</span>
                        <h2 class="stock-category-panel__title">Distribusi stok kategori ke area</h2>
                        <p class="stock-category-panel__subtitle">Sebaran stok kategori ini per regional dan kota.</p>
                    </div>
                </div>
                <div class="stock-category-panel__body">
                    <div class="table-responsive">
                        <table class="table stock-category-table" id="revamp_category_distribution_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Kota</th>
                                    <th>Nama Item</th>
                                    <th>Project Item</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Pemilik Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distributionRows as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $data['regional_lokasi_gudang'] ?></td>
                                        <td><?= $data['kota_lokasi_gudang'] ?></td>
                                        <td><?= $data['nama_item'] ?></td>
                                        <td><?= $data['project_item'] ?></td>
                                        <td><?= number_format((float) $data['jumlah_stok'], 0, ',', '.') ?></td>
                                        <td><?= $data['satuan_item'] ?></td>
                                        <td><?= $data['nama_bowheer'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5">Total</th>
                                    <th id="revamp_category_distribution_total">0</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <section class="stock-category-panel">
                <div class="stock-category-panel__head">
                    <div>
                        <span class="stock-category-chip"><i class="fas fa-scroll"></i> History In Out</span>
                        <h2 class="stock-category-panel__title">Histori mutasi material kategori</h2>
                        <p class="stock-category-panel__subtitle">Log transaksi IN/OUT yang terkait dengan kategori ini.</p>
                    </div>
                </div>
                <div class="stock-category-panel__body">
                    <div class="table-responsive">
                        <table class="table stock-category-table" id="revamp_category_history_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Lokasi</th>
                                    <th>Project</th>
                                    <th>Kategori</th>
                                    <th>Item</th>
                                    <th>Status</th>
                                    <th>Tipe</th>
                                    <th>QTY</th>
                                    <th>PIC</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyRows as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $data['regional_lokasi_gudang'] ?></td>
                                        <td><?= $data['kota_lokasi_gudang'] ?></td>
                                        <td><?= $data['nama_bowheer'] ?></td>
                                        <td><?= trim($data['kategori_item']) ?></td>
                                        <td><?= $data['nama_item'] ?></td>
                                        <td><?= $data['nama_sumber_material'] ?></td>
                                        <td><?= $data['status_sumber_material'] ?></td>
                                        <td><?= number_format((float) $data['jumlah_stok'], 0, ',', '.') ?></td>
                                        <td><?= $data['nama_user'] ?></td>
                                        <td><?= $data['tanggal_upload_stok'] ?></td>
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

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<script>
    $(function() {
        const summaryTable = $('#revamp_category_summary_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: []
        });

        const distributionTable = $('#revamp_category_distribution_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: []
        });

        $('#revamp_category_history_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[10, 'desc']]
        });

        function updateTotals() {
            let summaryTotal = 0;
            let distributionTotal = 0;

            summaryTable.rows({ search: 'applied' }).data().each(function(row) {
                summaryTotal += parseFloat(String(row[3]).replace(/\./g, '')) || 0;
            });

            distributionTable.rows({ search: 'applied' }).data().each(function(row) {
                distributionTotal += parseFloat(String(row[5]).replace(/\./g, '')) || 0;
            });

            $('#revamp_category_summary_total').text(summaryTotal.toLocaleString('id-ID'));
            $('#revamp_category_distribution_total').text(distributionTotal.toLocaleString('id-ID'));
        }

        updateTotals();
        summaryTable.on('draw', updateTotals);
        distributionTable.on('draw', updateTotals);

        <?php if ($status == 'sukses_tambah') { ?>
            swal("Success!", "Berhasil Ditambah!", "success");
        <?php } else if ($status == 'sukses_hapus') { ?>
            swal("Success!", "Berhasil Dihapus!", "success");
        <?php } else if ($status == 'sukses_edit') { ?>
            swal("Success!", "Berhasil Edit Data!", "success");
        <?php } else if ($status == 'gagal_tambah') { ?>
            swal("Gagal!", "Gagal Menambah Data!", "warning");
        <?php } else if ($status == 'gagal_edit') { ?>
            swal("Gagal!", "Gagal Mengedit Data!", "warning");
        <?php } else if ($status == 'gagal_hapus') { ?>
            swal("Gagal!", "Gagal Menghapus Data!", "warning");
        <?php } ?>
    });
</script>
