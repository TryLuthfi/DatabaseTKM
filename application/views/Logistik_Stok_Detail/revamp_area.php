<?php
$status = $this->session->flashdata('status');

$slugify = function ($value) {
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
};

$categoryVisuals = [
    'aksesories' => ['icon' => 'fas fa-plug', 'tone' => 'blue'],
    'closure' => ['icon' => 'fas fa-box-open', 'tone' => 'teal'],
    'fat' => ['icon' => 'fas fa-network-wired', 'tone' => 'amber'],
    'fdt' => ['icon' => 'fas fa-project-diagram', 'tone' => 'indigo'],
    'hdpe' => ['icon' => 'fas fa-wave-square', 'tone' => 'rose'],
    'kabel' => ['icon' => 'fas fa-ethernet', 'tone' => 'cyan'],
    'otb' => ['icon' => 'fas fa-server', 'tone' => 'violet'],
    'tiang' => ['icon' => 'fas fa-broadcast-tower', 'tone' => 'emerald'],
];

$summaryCards = [];
foreach ($getSummaryDetailArea as $stokKategory) {
    $normalizedLabel = trim((string) $stokKategory['kategori_item']);
    $slug = $slugify($normalizedLabel);
    $visual = isset($categoryVisuals[$slug]) ? $categoryVisuals[$slug] : ['icon' => 'fas fa-cubes', 'tone' => 'slate'];
    $summaryCards[] = [
        'label' => $normalizedLabel,
        'slug' => $slug,
        'value' => (float) $stokKategory['total_jumlah_stok'],
        'formatted' => number_format((float) $stokKategory['total_jumlah_stok'], 0, ',', '.') . ' ' . $stokKategory['satuan_item'],
        'icon' => $visual['icon'],
        'tone' => $visual['tone'],
    ];
}
?>

<style>
    .stock-detail-revamp {
        --detail-line: rgba(148, 163, 184, 0.18);
        --detail-surface: rgba(255, 255, 255, 0.94);
        --detail-shadow: 0 24px 52px rgba(15, 23, 42, 0.10);
    }

    .stock-detail-shell {
        padding: 1.15rem;
    }

    .stock-detail-hero {
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.20), transparent 25%),
            radial-gradient(circle at bottom right, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #0f172a 0%, #133255 48%, #0f4c81 100%);
        box-shadow: 0 30px 72px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .stock-detail-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1rem;
        padding: 1.4rem;
    }

    .stock-detail-hero__eyebrow {
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

    .stock-detail-hero h1 {
        margin: 0.95rem 0 0.7rem;
        color: #fff;
        font-size: 2rem;
        font-weight: 800;
    }

    .stock-detail-hero p {
        margin: 0;
        max-width: 46rem;
        line-height: 1.7;
        color: rgba(226, 232, 240, 0.84);
    }

    .stock-detail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.2rem;
    }

    .stock-detail-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.82rem 1.1rem;
        border-radius: 14px;
        font-weight: 800;
        border: 0;
        transition: transform 0.18s ease;
    }

    .stock-detail-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .stock-detail-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.14);
    }

    .stock-detail-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .stock-detail-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        align-content: start;
    }

    .stock-detail-metric {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .stock-detail-metric__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.42rem;
    }

    .stock-detail-metric__value {
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .stock-detail-panel {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--detail-line);
        background: var(--detail-surface);
        box-shadow: var(--detail-shadow);
        overflow: hidden;
    }

    .stock-detail-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .stock-detail-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .stock-detail-panel__subtitle {
        margin: 0.28rem 0 0;
        color: #64748b;
        font-size: 0.92rem;
    }

    .stock-detail-panel__body {
        padding: 1.2rem;
    }

    .stock-detail-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.7rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .stock-detail-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .stock-detail-card {
        border: 1px solid rgba(226, 232, 240, 0.94);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
    }

    .stock-detail-card__body {
        padding: 1.15rem;
    }

    .stock-detail-card__icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.05rem;
    }

    .stock-detail-card__label {
        display: block;
        margin-top: 0.95rem;
        color: #475569;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .stock-detail-card__value {
        display: block;
        margin-top: 0.45rem;
        color: #0f172a;
        font-size: 1.4rem;
        line-height: 1.2;
        font-weight: 800;
    }

    .stock-detail-card__link {
        margin-top: 0.9rem;
        padding: 0;
        border: 0;
        background: transparent;
        color: #1d4ed8;
        font-weight: 800;
    }

    .stock-detail-card--blue .stock-detail-card__icon { background: linear-gradient(135deg, #2563eb, #38bdf8); }
    .stock-detail-card--teal .stock-detail-card__icon { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .stock-detail-card--amber .stock-detail-card__icon { background: linear-gradient(135deg, #b45309, #f59e0b); }
    .stock-detail-card--indigo .stock-detail-card__icon { background: linear-gradient(135deg, #4338ca, #818cf8); }
    .stock-detail-card--rose .stock-detail-card__icon { background: linear-gradient(135deg, #be123c, #fb7185); }
    .stock-detail-card--cyan .stock-detail-card__icon { background: linear-gradient(135deg, #0891b2, #22d3ee); }
    .stock-detail-card--violet .stock-detail-card__icon { background: linear-gradient(135deg, #6d28d9, #a78bfa); }
    .stock-detail-card--emerald .stock-detail-card__icon { background: linear-gradient(135deg, #047857, #34d399); }
    .stock-detail-card--slate .stock-detail-card__icon { background: linear-gradient(135deg, #334155, #94a3b8); }

    .stock-detail-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .stock-detail-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .stock-detail-table th,
    .stock-detail-table td {
        padding: 0.8rem 0.72rem;
        border-top: 1px solid rgba(226, 232, 240, 0.72);
        vertical-align: middle;
        white-space: nowrap;
    }

    .stock-detail-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.68);
    }

    .stock-detail-table tfoot th {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 800;
    }

    .stock-detail-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.8rem;
        align-items: center;
    }

    .stock-detail-segment {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .stock-detail-pill {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 999px;
        padding: 0.55rem 0.9rem;
        background: #fff;
        color: #334155;
        font-weight: 700;
    }

    .stock-detail-pill.is-active {
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.96), rgba(219, 234, 254, 0.94));
        color: #1d4ed8;
        border-color: rgba(37, 99, 235, 0.24);
    }

    @media (max-width: 1199.98px) {
        .stock-detail-hero__grid,
        .stock-detail-card-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .stock-detail-shell {
            padding: 0.8rem;
        }

        .stock-detail-hero h1 {
            font-size: 1.55rem;
        }

        .stock-detail-actions,
        .stock-detail-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .stock-detail-metrics {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper stock-detail-revamp">
    <div class="content-header">
        <div class="container-fluid stock-detail-shell">
            <section class="stock-detail-hero">
                <div class="stock-detail-hero__grid">
                    <div>
                        <span class="stock-detail-hero__eyebrow">
                            <i class="fas fa-map-marker-alt"></i>
                            Detail Area
                        </span>
                        <h1><?= strtoupper($lokasi) ?></h1>
                        <p>
                            Tampilan ini merangkum stok per kategori, detail item lintas project,
                            dan histori mutasi material untuk area atau regional yang sedang dibuka.
                        </p>
                        <div class="stock-detail-actions">
                            <a href="<?= base_url('Dashboard_Logistik_Stok/revamp') ?>" class="stock-detail-btn stock-detail-btn--light">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Dashboard Revamp
                            </a>
                            <a href="<?= base_url('Logistik_Stok_Detail/detail/' . rawurlencode($lokasi)) ?>" class="stock-detail-btn stock-detail-btn--ghost">
                                <i class="fas fa-history"></i>
                                Buka Detail Lama
                            </a>
                        </div>
                    </div>
                    <div class="stock-detail-metrics">
                        <div class="stock-detail-metric">
                            <span class="stock-detail-metric__label">Kategori Aktif</span>
                            <span class="stock-detail-metric__value"><?= number_format(count($summaryCards), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-detail-metric">
                            <span class="stock-detail-metric__label">Item Tercatat</span>
                            <span class="stock-detail-metric__value"><?= number_format(count($getStokDetailArea), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-detail-metric">
                            <span class="stock-detail-metric__label">Histori Mutasi</span>
                            <span class="stock-detail-metric__value"><?= number_format(count($getHistoriInOUtLogistikArea), 0, ',', '.') ?></span>
                        </div>
                        <div class="stock-detail-metric">
                            <span class="stock-detail-metric__label">Tipe Halaman</span>
                            <span class="stock-detail-metric__value">Area</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stock-detail-panel">
                <div class="stock-detail-panel__head">
                    <div>
                        <span class="stock-detail-chip"><i class="fas fa-layer-group"></i> Summary Kategori</span>
                        <h2 class="stock-detail-panel__title">Akumulasi stok per kategori</h2>
                        <p class="stock-detail-panel__subtitle">Klik kartu untuk memfilter tabel detail ke kategori yang dipilih.</p>
                    </div>
                </div>
                <div class="stock-detail-panel__body">
                    <div class="stock-detail-card-grid">
                        <?php foreach ($summaryCards as $card): ?>
                            <article class="stock-detail-card stock-detail-card--<?= $card['tone'] ?>">
                                <div class="stock-detail-card__body">
                                    <span class="stock-detail-card__icon"><i class="<?= $card['icon'] ?>"></i></span>
                                    <span class="stock-detail-card__label"><?= $card['label'] ?></span>
                                    <span class="stock-detail-card__value"><?= $card['formatted'] ?></span>
                                    <button type="button" class="stock-detail-card__link detail-category-filter"
                                        data-category="<?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?>">
                                        Lihat pada tabel
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="stock-detail-panel">
                <div class="stock-detail-panel__head">
                    <div>
                        <span class="stock-detail-chip"><i class="fas fa-boxes"></i> Detail Stok</span>
                        <h2 class="stock-detail-panel__title">Rincian stok item per kategori</h2>
                        <p class="stock-detail-panel__subtitle">Seluruh data detail stok area ditampilkan dalam satu tabel yang lebih mudah dipindai.</p>
                    </div>
                </div>
                <div class="stock-detail-panel__body">
                    <div class="stock-detail-toolbar mb-3">
                        <div class="stock-detail-segment">
                            <button type="button" class="stock-detail-pill is-active detail-category-pill" data-category="">Semua Kategori</button>
                            <?php foreach ($summaryCards as $card): ?>
                                <button type="button" class="stock-detail-pill detail-category-pill"
                                    data-category="<?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $card['label'] ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table stock-detail-table" id="revamp_area_detail_table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kategori</th>
                                    <th>Nama Item</th>
                                    <th>Project Item</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Pemilik Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($getStokDetailArea as $index => $data): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= trim($data['kategori_item']) ?></td>
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
                                    <th colspan="4">Total Stok Tampil</th>
                                    <th id="revamp_area_detail_total">0</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <section class="stock-detail-panel">
                <div class="stock-detail-panel__head">
                    <div>
                        <span class="stock-detail-chip"><i class="fas fa-scroll"></i> History In Out</span>
                        <h2 class="stock-detail-panel__title">Mutasi material pada area ini</h2>
                        <p class="stock-detail-panel__subtitle">Histori IN/OUT material untuk area atau regional yang sedang dibuka.</p>
                    </div>
                </div>
                <div class="stock-detail-panel__body">
                    <div class="table-responsive">
                        <table class="table stock-detail-table" id="revamp_area_history_table">
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
                                <?php foreach ($getHistoriInOUtLogistikArea as $index => $data): ?>
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
        const detailTable = $('#revamp_area_detail_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: []
        });

        $('#revamp_area_history_table').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[10, 'desc']]
        });

        function updateAreaDetailTotal() {
            let total = 0;
            detailTable.rows({ search: 'applied' }).data().each(function(row) {
                total += parseFloat(String(row[4]).replace(/\./g, '')) || 0;
            });
            $('#revamp_area_detail_total').text(total.toLocaleString('id-ID'));
        }

        function applyCategoryFilter(category) {
            detailTable.column(1).search(category || '', true, false).draw();
            $('.detail-category-pill').removeClass('is-active');
            $(`.detail-category-pill[data-category="${category}"]`).addClass('is-active');
            if (!category) {
                $('.detail-category-pill[data-category=""]').addClass('is-active');
            }
        }

        updateAreaDetailTotal();
        detailTable.on('draw', updateAreaDetailTotal);

        $('.detail-category-pill, .detail-category-filter').on('click', function() {
            const category = $(this).data('category') || '';
            applyCategoryFilter(category);
            document.getElementById('revamp_area_detail_table').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

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
