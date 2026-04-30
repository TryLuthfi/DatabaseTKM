<?php
$status = $this->session->flashdata('status');
$flashSuccess = $this->session->flashdata('stockopname_success');
$flashError = $this->session->flashdata('stockopname_error');

$bulanArr = [
    'JANUARI',
    'FEBRUARI',
    'MARET',
    'APRIL',
    'MEI',
    'JUNI',
    'JULI',
    'AGUSTUS',
    'SEPTEMBER',
    'OKTOBER',
    'NOVEMBER',
    'DESEMBER'
];

$periodeRows = isset($getSOPeriode) ? $getSOPeriode : [];
$totalPeriode = count($periodeRows);
$periodeDone = 0;
$periodeNeedFollowUp = 0;
$totalCoverage = 0.0;
$totalCoverageRows = 0;

foreach ($periodeRows as $row) {
    $statusPeriode = strtoupper(trim((string) ($row['sop_status'] ?? '')));
    if ($statusPeriode === 'DONE' || $statusPeriode === 'CLOSED') {
        $periodeDone++;
    }
    if ($statusPeriode === 'NEED BA' || $statusPeriode === 'REVIEW' || $statusPeriode === 'APPROVED') {
        $periodeNeedFollowUp++;
    }

    $persentase = (string) ($row['persentasi_so_kota'] ?? '0 / 0');
    $parts = array_map('trim', explode('/', $persentase));
    $doneValue = (float) ($parts[0] ?? 0);
    $totalValue = (float) ($parts[1] ?? 0);
    if ($totalValue > 0) {
        $totalCoverage += ($doneValue / $totalValue) * 100;
        $totalCoverageRows++;
    }
}

$avgCoverage = $totalCoverageRows > 0 ? $totalCoverage / $totalCoverageRows : 0;
$currentMonthIndex = (int) date('n') - 1;
$currentYear = (int) date('Y');
?>

<style>
    .so-revamp {
        --so-ink: #0f172a;
        --so-muted: #64748b;
        --so-line: rgba(148, 163, 184, 0.2);
        --so-panel: rgba(255, 255, 255, 0.95);
        --so-soft: rgba(248, 250, 252, 0.94);
        --so-shadow: 0 24px 52px rgba(15, 23, 42, 0.1);
    }

    .so-revamp .content-header {
        padding-bottom: 0;
    }

    .so-shell {
        padding: 1.15rem;
    }

    .so-hero {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.14), transparent 24%),
            linear-gradient(135deg, #0f172a 0%, #113355 48%, #0f4c81 100%);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.22);
        color: #f8fafc;
    }

    .so-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.1rem;
        padding: 1.45rem;
    }

    .so-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.38rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .so-hero h1 {
        margin: 0.95rem 0 0.7rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .so-hero p {
        margin: 0;
        max-width: 46rem;
        line-height: 1.7;
        color: rgba(226, 232, 240, 0.86);
    }

    .so-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .so-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.82rem 1.1rem;
        border-radius: 14px;
        border: 0;
        font-weight: 800;
        transition: transform 0.18s ease;
    }

    .so-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .so-btn--light {
        background: #f8fafc;
        color: #0f172a;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.14);
    }

    .so-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .so-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        align-content: start;
    }

    .so-metric {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .so-metric__label {
        display: block;
        margin-bottom: 0.45rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .so-metric__value {
        font-size: 1.72rem;
        font-weight: 800;
        color: #fff;
    }

    .so-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .so-card {
        border-radius: 22px;
        border: 1px solid var(--so-line);
        background: var(--so-panel);
        box-shadow: var(--so-shadow);
        padding: 1.1rem;
    }

    .so-card__label {
        display: block;
        margin-bottom: 0.45rem;
        color: var(--so-muted);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .so-card__value {
        color: var(--so-ink);
        font-size: 1.8rem;
        font-weight: 800;
    }

    .so-flow {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--so-line);
        background: var(--so-panel);
        box-shadow: var(--so-shadow);
        padding: 1.2rem;
    }

    .so-flow__title,
    .so-table__title {
        margin: 0;
        color: var(--so-ink);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .so-flow__desc {
        margin: 0.4rem 0 0;
        color: var(--so-muted);
        line-height: 1.6;
    }

    .so-flow__steps {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.85rem;
        margin-top: 1rem;
    }

    .so-step {
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: var(--so-soft);
        padding: 0.95rem;
    }

    .so-step__number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
        font-weight: 800;
        margin-bottom: 0.7rem;
    }

    .so-step__title {
        margin: 0;
        color: var(--so-ink);
        font-size: 0.95rem;
        font-weight: 800;
    }

    .so-step__text {
        margin: 0.45rem 0 0;
        color: var(--so-muted);
        font-size: 0.9rem;
        line-height: 1.55;
    }

    .so-table {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--so-line);
        background: var(--so-panel);
        box-shadow: var(--so-shadow);
        overflow: hidden;
    }

    .so-table__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .so-table__desc {
        margin: 0.38rem 0 0;
        color: var(--so-muted);
        line-height: 1.6;
    }

    .so-table__body {
        padding: 1rem 1.2rem 1.2rem;
    }

    .so-table table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .so-table th {
        padding: 0.9rem 0.85rem;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.9);
    }

    .so-table td {
        padding: 0.95rem 0.85rem;
        color: #0f172a;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        vertical-align: middle;
    }

    .so-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.7);
    }

    .so-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        padding: 0.42rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .so-badge--done {
        background: rgba(16, 185, 129, 0.14);
        color: #047857;
    }

    .so-badge--pending {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .so-badge--review {
        background: rgba(59, 130, 246, 0.14);
        color: #1d4ed8;
    }

    .so-badge--plain {
        background: rgba(148, 163, 184, 0.14);
        color: #475569;
    }

    .so-action {
        display: inline-flex;
        align-items: center;
        gap: 0.38rem;
        margin-right: 0.45rem;
        padding: 0.62rem 0.82rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.86rem;
    }

    .so-action:hover {
        text-decoration: none;
    }

    .so-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--so-muted);
    }

    .so-help {
        font-size: 0.88rem;
        color: var(--so-muted);
    }

    @media (max-width: 1199.98px) {
        .so-grid,
        .so-flow__steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .so-hero__grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .so-shell {
            padding: 0.85rem;
        }

        .so-grid,
        .so-flow__steps,
        .so-metrics {
            grid-template-columns: 1fr;
        }

        .so-table__head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="content-wrapper so-revamp">
    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="so-shell">
                    <section class="so-hero">
                        <div class="so-hero__grid">
                            <div>
                                <span class="so-hero__eyebrow"><i class="fas fa-clipboard-check"></i> Stock Opname Revamp</span>
                                <h1>Periode SO bulanan, BA kronologi, dan adjustment dalam satu alur.</h1>
                                <p>Halaman ini merapikan proses stock opname supaya area bisa mulai dari draft per periode, lanjut input fisik, wajib isi remarks saat ada selisih, lalu ditindaklanjuti ke BA signed dan approval adjustment.</p>
                                <div class="so-hero__actions">
                                    <?php if ($this->session->userdata('nama_level') == 'Super Admin') { ?>
                                        <button type="button" class="so-btn so-btn--light" data-toggle="modal" data-target="#modal-tambah-periode-so">
                                            <i class="fas fa-plus"></i> Tambah Periode
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="so-metrics">
                                <div class="so-metric">
                                    <span class="so-metric__label">Total Periode</span>
                                    <div class="so-metric__value"><?= number_format($totalPeriode, 0, ',', '.') ?></div>
                                </div>
                                <div class="so-metric">
                                    <span class="so-metric__label">Rata-rata Coverage</span>
                                    <div class="so-metric__value"><?= number_format($avgCoverage, 0, ',', '.') ?>%</div>
                                </div>
                                <div class="so-metric">
                                    <span class="so-metric__label">Periode Selesai</span>
                                    <div class="so-metric__value"><?= number_format($periodeDone, 0, ',', '.') ?></div>
                                </div>
                                <div class="so-metric">
                                    <span class="so-metric__label">Perlu Tindak Lanjut</span>
                                    <div class="so-metric__value"><?= number_format($periodeNeedFollowUp, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="so-grid">
                        <article class="so-card">
                            <span class="so-card__label">Target Pelaksanaan</span>
                            <div class="so-card__value">Tanggal 1</div>
                            <p class="so-help mb-0">Periode tetap dibuka per bulan agar area punya acuan SO yang seragam.</p>
                        </article>
                        <article class="so-card">
                            <span class="so-card__label">Toleransi Submit</span>
                            <div class="so-card__value">Tanggal 2-4</div>
                            <p class="so-help mb-0">Kondisi lapangan tetap terakomodasi tanpa memecah periode menjadi periode baru.</p>
                        </article>
                        <article class="so-card">
                            <span class="so-card__label">Wajib Remarks</span>
                            <div class="so-card__value">Per Item Selisih</div>
                            <p class="so-help mb-0">Remarks jadi syarat sebelum BA kronologi bisa dicetak dan ditandatangani basah.</p>
                        </article>
                        <article class="so-card">
                            <span class="so-card__label">Adjustment</span>
                            <div class="so-card__value">Setelah Approve</div>
                            <p class="so-help mb-0">Sinkronisasi stok dilakukan setelah BA signed diupload dan approval selesai.</p>
                        </article>
                    </section>

                    <section class="so-flow">
                        <h2 class="so-flow__title">Flow kerja Stock Opname</h2>
                        <p class="so-flow__desc">Flow baru tetap mempertahankan proses lama, tetapi sekarang lebih jelas titik kontrolnya dari area sampai approval pusat.</p>
                        <div class="so-flow__steps">
                            <article class="so-step">
                                <span class="so-step__number">1</span>
                                <h3 class="so-step__title">Buka Periode</h3>
                                <p class="so-step__text">HO membuka periode bulanan dan area masuk ke daftar gudang yang harus menyelesaikan SO.</p>
                            </article>
                            <article class="so-step">
                                <span class="so-step__number">2</span>
                                <h3 class="so-step__title">Input Fisik</h3>
                                <p class="so-step__text">Area mengisi hasil hitung aktual dan sistem membandingkannya dengan stok aplikasi.</p>
                            </article>
                            <article class="so-step">
                                <span class="so-step__number">3</span>
                                <h3 class="so-step__title">Remarks Selisih</h3>
                                <p class="so-step__text">Setiap item yang selisih wajib memiliki remarks sebelum dokumen BA diproses.</p>
                            </article>
                            <article class="so-step">
                                <span class="so-step__number">4</span>
                                <h3 class="so-step__title">Print BA</h3>
                                <p class="so-step__text">BA kronologi dicetak dari data selisih yang sudah lengkap untuk sign basah area.</p>
                            </article>
                            <article class="so-step">
                                <span class="so-step__number">5</span>
                                <h3 class="so-step__title">Upload & Approve</h3>
                                <p class="so-step__text">BA signed diupload, diverifikasi, lalu menunggu approval sebelum adjustment dijalankan.</p>
                            </article>
                            <article class="so-step">
                                <span class="so-step__number">6</span>
                                <h3 class="so-step__title">Auto Adjust</h3>
                                <p class="so-step__text">Setelah approved, ledger bisa membentuk adjustment IN/OUT yang terhubung ke BA dan item SO.</p>
                            </article>
                        </div>
                    </section>

                    <section class="so-table">
                        <div class="so-table__head">
                            <div>
                                <h2 class="so-table__title">Daftar periode stock opname</h2>
                                <p class="so-table__desc">Masuk ke setiap periode untuk melihat coverage area, status submit, dan proses lanjutan BA kronologi.</p>
                            </div>
                            <?php if ($this->session->userdata('nama_level') == 'Super Admin') { ?>
                                <button type="button" class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#modal-tambah-periode-so">
                                    <i class="fas fa-plus mr-1"></i> Tambah Periode
                                </button>
                            <?php } ?>
                        </div>
                        <div class="so-table__body">
                            <div class="table-responsive">
                                <table class="table mb-0" id="table-stockopname-periode-revamp">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th>Coverage Area</th>
                                            <th>Tindak Lanjut</th>
                                            <th style="min-width: 220px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($periodeRows)) { ?>
                                            <?php foreach ($periodeRows as $index => $row) { ?>
                                                <?php
                                                $statusPeriode = strtoupper(trim((string) ($row['sop_status'] ?? 'NOT YET')));
                                                $badgeClass = 'so-badge--plain';
                                                if ($statusPeriode === 'DONE' || $statusPeriode === 'CLOSED') {
                                                    $badgeClass = 'so-badge--done';
                                                } elseif ($statusPeriode === 'REVIEW' || $statusPeriode === 'APPROVED') {
                                                    $badgeClass = 'so-badge--review';
                                                } else {
                                                    $badgeClass = 'so-badge--pending';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= $row['sop_bulan'] ?> <?= $row['sop_tahun'] ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="so-badge <?= $badgeClass ?>"><?= $statusPeriode ?></span>
                                                    </td>
                                                    <td><?= $row['persentasi_so_kota'] ?></td>
                                                    <td>
                                                        <?php if ($statusPeriode === 'DONE' || $statusPeriode === 'CLOSED') { ?>
                                                            Seluruh area yang submit sudah tercatat.
                                                        <?php } elseif ($statusPeriode === 'NOT YET') { ?>
                                                            Area belum mulai submit SO.
                                                        <?php } else { ?>
                                                            Ada proses review, BA, atau approval yang perlu dipantau.
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('StockOpname/revamp/periode/' . $row['id_sop']) ?>" class="btn btn-success btn-sm so-action">
                                                            <i class="fas fa-share"></i> Detail
                                                        </a>
                                                        <?php if ($this->session->userdata('nama_level') == 'Super Admin') { ?>
                                                            <a href="<?= base_url('StockOpname/hapusPeriode/' . $row['id_sop']) ?>" class="btn btn-danger btn-sm so-action">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="6" class="so-empty">Belum ada periode stock opname. Tambahkan periode pertama untuk memulai workflow SO.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
</div>

<form id="form-tambah-periode-revamp" action="<?= base_url('StockOpname/tambahPeriode') ?>" method="post">
    <div class="modal fade" id="modal-tambah-periode-so" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title font-weight-bold">Tambah Periode Stock Opname</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Periode ini akan menjadi payung proses SO bulanan. Target pelaksanaan tetap tanggal 1 dengan toleransi submit area sampai tanggal 4.</p>
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Bulan</label>
                        <select class="form-control" id="so_bulan" name="so_bulan" required>
                            <option value="">Pilih Bulan</option>
                            <?php foreach ($bulanArr as $index => $bulan) { ?>
                                <option value="<?= $bulan ?>" <?= $index === $currentMonthIndex ? 'selected' : '' ?>><?= $bulan ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Pilih Tahun</label>
                        <select class="form-control" id="so_tahun" name="so_tahun" required>
                            <?php for ($tahun = $currentYear + 1; $tahun >= $currentYear - 1; $tahun--) { ?>
                                <option value="<?= $tahun ?>" <?= $tahun === $currentYear ? 'selected' : '' ?>><?= $tahun ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary font-weight-bold" id="btn-submit-periode-revamp">
                        <i class="fas fa-save mr-1"></i> Simpan Periode
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successMessage = <?= json_encode($flashSuccess) ?>;
        var errorMessage = <?= json_encode($flashError) ?>;
        var statusFlag = <?= json_encode($status) ?>;
        var noticeKey = <?= json_encode('stockopname-revamp-periode|' . md5((string) $status . '|' . (string) $flashSuccess . '|' . (string) $flashError)) ?>;
        var submitButton = document.getElementById('btn-submit-periode-revamp');
        var bulanSelect = document.getElementById('so_bulan');
        var tahunSelect = document.getElementById('so_tahun');
        var form = document.getElementById('form-tambah-periode-revamp');

        function showNotice(title, text, icon) {
            try {
                if (window.sessionStorage && text && sessionStorage.getItem(noticeKey) === 'shown') {
                    return;
                }
            } catch (error) {
                console.error('sessionStorage notice periode gagal dibaca:', error);
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
                console.error('Swal.fire StockOpname revamp periode gagal dijalankan:', error);
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
                console.error('swal StockOpname revamp periode gagal dijalankan:', error);
            }

            console.warn('SweetAlert tidak tersedia untuk notifikasi StockOpname:', title, text, icon);
        }

        if (successMessage) {
            showNotice('Success!', successMessage, 'success');
        } else if (errorMessage) {
            showNotice('Gagal!', errorMessage, 'warning');
        } else if (statusFlag === 'sukses_tambah') {
            showNotice('Success!', 'Periode berhasil ditambahkan.', 'success');
        } else if (statusFlag === 'sukses_hapus') {
            showNotice('Success!', 'Periode berhasil dihapus.', 'success');
        }

        try {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                $('#table-stockopname-periode-revamp').DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10
                });
            }
        } catch (error) {
            console.error('DataTable StockOpname revamp periode gagal diinisialisasi:', error);
        }

        if (!submitButton || !bulanSelect || !tahunSelect || !form) {
            return;
        }

        submitButton.addEventListener('click', function() {
            var selectedBulan = bulanSelect.value;
            var selectedTahun = tahunSelect.value;

                if (!selectedBulan || !selectedTahun) {
                showNotice('Perhatian!', 'Bulan dan tahun periode wajib dipilih.', 'warning');
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', "<?= site_url('StockOpname/cekPeriode'); ?>", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) {
                    return;
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    var response = {};
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (error) {
                        response = {};
                    }

                    if (response.status === 'exists') {
                        showNotice('Periode Sudah Ada', 'Periode ' + selectedBulan + ' ' + selectedTahun + ' sudah tersedia. Pilih periode lain.', 'warning');
                        return;
                    }

                    form.submit();
                    return;
                }

                showNotice('Gagal!', 'Terjadi kesalahan saat mengecek periode. Coba lagi.', 'warning');
            };
            xhr.onerror = function() {
                showNotice('Gagal!', 'Terjadi kesalahan saat mengecek periode. Coba lagi.', 'warning');
            };
            xhr.send(
                'selectedBulan=' + encodeURIComponent(selectedBulan) +
                '&selectedTahun=' + encodeURIComponent(selectedTahun)
            );
        });
    });
</script>
