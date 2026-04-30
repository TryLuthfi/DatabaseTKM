<?php
$status = $this->session->flashdata('status');
$flashSuccess = $this->session->flashdata('stockopname_success');
$flashError = $this->session->flashdata('stockopname_error');

$periode = $getDetailSoPeriode[0] ?? [];
$periodeLabel = trim((string) (($periode['sop_bulan'] ?? '-') . ' ' . ($periode['sop_tahun'] ?? '')));
$rows = isset($getSOKota) ? $getSOKota : [];

$doneCount = 0;
$pendingCount = 0;
$reviewCount = 0;

foreach ($rows as $row) {
    $statusArea = strtoupper(trim((string) ($row['sok_status'] ?? '')));
    if (in_array($statusArea, ['DONE', 'ADJUSTED', 'CLOSED'], true)) {
        $doneCount++;
    } elseif (in_array($statusArea, ['REVIEW', 'APPROVED', 'NEED BA', 'BA DRAFT', 'WAITING APPROVAL'], true)) {
        $reviewCount++;
    } else {
        $pendingCount++;
    }
}

$coverage = count($rows) > 0 ? ($doneCount / count($rows)) * 100 : 0;
?>

<style>
    .so-area-revamp {
        --so-area-ink: #0f172a;
        --so-area-muted: #64748b;
        --so-area-line: rgba(148, 163, 184, 0.18);
        --so-area-panel: rgba(255, 255, 255, 0.95);
        --so-area-soft: rgba(248, 250, 252, 0.94);
        --so-area-shadow: 0 24px 52px rgba(15, 23, 42, 0.1);
    }

    .so-area-shell {
        padding: 1.15rem;
    }

    .so-area-hero {
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background:
            radial-gradient(circle at top left, rgba(6, 182, 212, 0.18), transparent 26%),
            radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.12), transparent 22%),
            linear-gradient(135deg, #0f172a 0%, #103353 50%, #134e4a 100%);
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.2);
        color: #f8fafc;
    }

    .so-area-hero__grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 1rem;
        padding: 1.45rem;
    }

    .so-area-hero__eyebrow {
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

    .so-area-hero h1 {
        margin: 0.95rem 0 0.7rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .so-area-hero p {
        margin: 0;
        max-width: 46rem;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .so-area-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.2rem;
    }

    .so-area-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.82rem 1.1rem;
        border-radius: 14px;
        border: 0;
        font-weight: 800;
    }

    .so-area-btn:hover {
        text-decoration: none;
    }

    .so-area-btn--light {
        background: #f8fafc;
        color: #0f172a;
    }

    .so-area-btn--ghost {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .so-area-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        align-content: start;
    }

    .so-area-metric {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .so-area-metric__label {
        display: block;
        margin-bottom: 0.45rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .so-area-metric__value {
        font-size: 1.72rem;
        font-weight: 800;
        color: #fff;
    }

    .so-area-flow,
    .so-area-list {
        margin-top: 1.2rem;
        border-radius: 24px;
        border: 1px solid var(--so-area-line);
        background: var(--so-area-panel);
        box-shadow: var(--so-area-shadow);
        overflow: hidden;
    }

    .so-area-flow {
        padding: 1.2rem;
    }

    .so-area-flow__title,
    .so-area-list__title {
        margin: 0;
        color: var(--so-area-ink);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .so-area-flow__desc,
    .so-area-list__desc {
        margin: 0.38rem 0 0;
        color: var(--so-area-muted);
        line-height: 1.6;
    }

    .so-area-points {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
        margin-top: 1rem;
    }

    .so-area-point {
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: var(--so-area-soft);
        padding: 1rem;
    }

    .so-area-point h3 {
        margin: 0 0 0.45rem;
        color: var(--so-area-ink);
        font-size: 0.96rem;
        font-weight: 800;
    }

    .so-area-point p {
        margin: 0;
        color: var(--so-area-muted);
        line-height: 1.55;
        font-size: 0.9rem;
    }

    .so-area-list__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.9rem;
        padding: 1.15rem 1.2rem 0;
    }

    .so-area-list__body {
        padding: 1rem 1.2rem 1.2rem;
    }

    .so-area-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .so-area-card {
        border-radius: 22px;
        border: 1px solid rgba(226, 232, 240, 0.94);
        background: #fff;
        padding: 1.05rem;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.06);
    }

    .so-area-card__top {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 0.85rem;
    }

    .so-area-card__title {
        margin: 0;
        color: var(--so-area-ink);
        font-size: 1rem;
        font-weight: 800;
    }

    .so-area-card__meta {
        color: var(--so-area-muted);
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .so-area-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 1rem;
    }

    .so-area-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        padding: 0.42rem 0.8rem;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .so-area-chip--done {
        background: rgba(16, 185, 129, 0.14);
        color: #047857;
    }

    .so-area-chip--pending {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .so-area-chip--review {
        background: rgba(59, 130, 246, 0.14);
        color: #1d4ed8;
    }

    .so-area-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--so-area-muted);
    }

    @media (max-width: 1199.98px) {
        .so-area-grid,
        .so-area-points {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .so-area-hero__grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .so-area-shell {
            padding: 0.85rem;
        }

        .so-area-grid,
        .so-area-points,
        .so-area-metrics {
            grid-template-columns: 1fr;
        }

        .so-area-list__head {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="content-wrapper so-area-revamp">
    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="so-area-shell">
                    <section class="so-area-hero">
                        <div class="so-area-hero__grid">
                            <div>
                                <span class="so-area-hero__eyebrow"><i class="fas fa-map-marked-alt"></i> Detail Periode</span>
                                <h1>Monitoring area untuk periode <?= $periodeLabel ?></h1>
                                <p>Gunakan halaman ini untuk melihat area mana yang belum submit SO, area yang sudah selesai, dan area yang perlu tindak lanjut BA kronologi setelah ditemukan selisih stok.</p>
                                <div class="so-area-actions">
                                    <a href="<?= base_url('StockOpname/revamp') ?>" class="so-area-btn so-area-btn--light">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Periode
                                    </a>
                                </div>
                            </div>

                            <div class="so-area-metrics">
                                <div class="so-area-metric">
                                    <span class="so-area-metric__label">Total Area</span>
                                    <div class="so-area-metric__value"><?= number_format(count($rows), 0, ',', '.') ?></div>
                                </div>
                                <div class="so-area-metric">
                                    <span class="so-area-metric__label">Coverage Submit</span>
                                    <div class="so-area-metric__value"><?= number_format($coverage, 0, ',', '.') ?>%</div>
                                </div>
                                <div class="so-area-metric">
                                    <span class="so-area-metric__label">Sudah Selesai</span>
                                    <div class="so-area-metric__value"><?= number_format($doneCount, 0, ',', '.') ?></div>
                                </div>
                                <div class="so-area-metric">
                                    <span class="so-area-metric__label">Perlu Follow Up</span>
                                    <div class="so-area-metric__value"><?= number_format($pendingCount + $reviewCount, 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="so-area-flow">
                        <h2 class="so-area-flow__title">Aturan kerja per area</h2>
                        <p class="so-area-flow__desc">Masuk ke area untuk input atau review hasil SO. Jika ada selisih, area wajib melengkapi remarks per item sebelum BA bisa dilanjutkan.</p>
                        <div class="so-area-points">
                            <article class="so-area-point">
                                <h3>Submit dalam periode yang sama</h3>
                                <p>SO tetap diperlakukan sebagai periode bulanan yang sama walaupun area menyelesaikannya di tanggal 2 sampai 4.</p>
                            </article>
                            <article class="so-area-point">
                                <h3>Selisih wajib punya kronologi</h3>
                                <p>Remarks per item selisih menjadi bahan BA kronologi, jadi tidak boleh ada item selisih tanpa penjelasan.</p>
                            </article>
                            <article class="so-area-point">
                                <h3>Adjustment tunggu approval</h3>
                                <p>Material baru boleh disinkronkan ke ledger setelah BA signed diupload dan status approval sudah selesai.</p>
                            </article>
                        </div>
                    </section>

                    <section class="so-area-list">
                        <div class="so-area-list__head">
                            <div>
                                <h2 class="so-area-list__title">Daftar area dan status pengerjaan</h2>
                                <p class="so-area-list__desc">Tiap kartu mewakili satu gudang/area. Tombol aksi tetap sama seperti flow lama: tambah, detail, dan edit.</p>
                            </div>
                        </div>
                        <div class="so-area-list__body">
                            <?php if (!empty($rows)) { ?>
                                <div class="so-area-grid">
                                    <?php foreach ($rows as $row) { ?>
                                        <?php
                                        $statusArea = strtoupper(trim((string) ($row['sok_status'] ?? 'NOT YET')));
                                        $chipClass = 'so-area-chip--pending';
                                        if (in_array($statusArea, ['DONE', 'ADJUSTED', 'CLOSED'], true)) {
                                            $chipClass = 'so-area-chip--done';
                                        } elseif (in_array($statusArea, ['NEED BA', 'REVIEW', 'APPROVED', 'BA DRAFT', 'WAITING APPROVAL'], true)) {
                                            $chipClass = 'so-area-chip--review';
                                        }
                                        $detailUrl = base_url('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $row['id_lokasi_gudang'] . '?mode=1bda80f2be4d3658e0baa43fbe7ae8c1');
                                        $editUrl = base_url('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $row['id_lokasi_gudang'] . '?mode=de95b43bceeb4b998aed4aed5cef1ae7');
                                        $inputUrl = base_url('StockOpname/revamp/periode/' . $id_sop . '/lokasi/' . $row['id_lokasi_gudang'] . '?mode=a43c1b0aa53a0c908810c06ab1ff3967');
                                        ?>
                                        <article class="so-area-card">
                                            <div class="so-area-card__top">
                                                <div>
                                                    <h3 class="so-area-card__title"><?= $row['kota_lokasi_gudang'] ?></h3>
                                                    <div class="so-area-card__meta">
                                                        <?= $row['regional_lokasi_gudang'] ?><br>
                                                        <?= $row['provinsi_lokasi_gudang'] ?>
                                                    </div>
                                                </div>
                                                <span class="so-area-chip <?= $chipClass ?>"><?= $statusArea !== '' ? $statusArea : 'NOT YET' ?></span>
                                            </div>

                                            <div class="so-area-card__meta">
                                                <?php if (!empty($row['id_so_kota'])) { ?>
                                                    Data SO area ini sudah tersimpan. Anda bisa membuka detail atau mengedit hasil opname yang ada.
                                                <?php } else { ?>
                                                    Area ini belum submit SO untuk periode <?= $periodeLabel ?>.
                                                <?php } ?>
                                            </div>

                                            <div class="so-area-card__actions">
                                                <?php if (!empty($row['id_so_kota'])) { ?>
                                                    <a href="<?= $detailUrl ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-share mr-1"></i> Detail
                                                    </a>
                                                    <a href="<?= $editUrl ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit mr-1"></i> Edit
                                                    </a>
                                                    <?php if ($this->session->userdata('nama_level') == 'Super Admin') { ?>
                                                        <a href="<?= base_url('StockOpname/hapusKota/' . $row['id_so_kota']) ?>" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash mr-1"></i> Delete
                                                        </a>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <a href="<?= $inputUrl ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus mr-1"></i> Tambah SO
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </article>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="so-area-empty">Belum ada data lokasi gudang untuk periode ini.</div>
                            <?php } ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    (function() {
        var successMessage = <?= json_encode($flashSuccess) ?>;
        var errorMessage = <?= json_encode($flashError) ?>;
        var statusFlag = <?= json_encode($status) ?>;
        var noticeKey = <?= json_encode('stockopname-revamp-sokota|' . md5((string) $status . '|' . (string) $flashSuccess . '|' . (string) $flashError . '|' . (string) $id_sop)) ?>;

        function showNotice(title, text, icon) {
            try {
                if (window.sessionStorage && text && sessionStorage.getItem(noticeKey) === 'shown') {
                    return;
                }
            } catch (error) {
                console.error('sessionStorage notice area gagal dibaca:', error);
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
                console.error('Swal.fire StockOpname revamp area gagal dijalankan:', error);
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
                console.error('swal StockOpname revamp area gagal dijalankan:', error);
            }

            console.warn('SweetAlert tidak tersedia untuk notifikasi StockOpname:', title, text, icon);
        }

        if (successMessage) {
            showNotice('Success!', successMessage, 'success');
        } else if (errorMessage) {
            showNotice('Gagal!', errorMessage, 'warning');
        } else if (statusFlag === 'sukses_hapus') {
            showNotice('Success!', 'Data area berhasil dihapus.', 'success');
        }
    })();
</script>
