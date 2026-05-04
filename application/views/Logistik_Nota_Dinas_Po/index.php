<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$activeNodin = $activeNodin ?? null;
$activeNodinDetailRows = $activeNodinDetailRows ?? [];
$activeNodinPurchaseRequestIds = $activeNodinPurchaseRequestIds ?? [];
$activeNodinCandidateItems = $activeNodinCandidateItems ?? [];
$activeNodinEditMode = !empty($activeNodinEditMode);
$activeNodinReadOnly = !empty($activeNodinReadOnly);
$masterPabrikOptions = $masterPabrikOptions ?? [];
$approvedPurchaseRequests = $approvedPurchaseRequests ?? [];

$totalQty = array_sum(array_map('floatval', array_column($activeNodinDetailRows, 'qty_po_nodin')));
$totalNominal = array_sum(array_map(static function ($row) {
    return ((float) ($row['qty_po_nodin'] ?? 0)) * ((float) ($row['harga_satuan'] ?? 0));
}, $activeNodinDetailRows));

$detailByPrDetail = [];
foreach ($activeNodinDetailRows as $row) {
    $detailKey = (string) ($row['id_purchase_request_detail'] ?? '');
    if ($detailKey === '') {
        continue;
    }

    if (!isset($detailByPrDetail[$detailKey])) {
        $detailByPrDetail[$detailKey] = [];
    }

    $detailByPrDetail[$detailKey][] = $row;
}
$masterPabrikByName = [];
foreach ($masterPabrikOptions as $pabrikOption) {
    $masterPabrikByName[strtolower(trim((string) ($pabrikOption['nama_pabrik'] ?? '')))] = (string) ($pabrikOption['id_pabrik'] ?? '');
}
?>

<style>
    .nodin-page { --ink:#0f172a; --muted:#64748b; --line:rgba(148,163,184,.22); --surface:rgba(255,255,255,.97); --shadow:0 24px 48px rgba(15,23,42,.10); }
    .nodin-page .content-header { padding-bottom: 0; }
    .nodin-shell { padding: 1.15rem; }
    .nodin-card, .nodin-table-shell { border:1px solid var(--line); border-radius:24px; background:var(--surface); box-shadow:var(--shadow); overflow:hidden; }
    .nodin-hero { border:1px solid rgba(148,163,184,.20); border-radius:28px; color:#fff; background:linear-gradient(135deg,#0f172a 0%,#102948 46%,#143a63 100%); box-shadow:0 30px 70px rgba(15,23,42,.22); overflow:hidden; }
    .nodin-hero__grid { display:grid; grid-template-columns:1.5fr 1fr; gap:1.2rem; padding:1.5rem; }
    .nodin-hero__eyebrow { display:inline-flex; align-items:center; gap:.5rem; padding:.4rem .8rem; border-radius:999px; background:rgba(255,255,255,.12); font-size:.78rem; letter-spacing:.12em; text-transform:uppercase; font-weight:700; }
    .nodin-hero h1 { margin:1rem 0 .75rem; font-size:2rem; font-weight:800; color:#fff; }
    .nodin-hero p { max-width:46rem; margin:0; color:rgba(226,232,240,.88); line-height:1.7; }
    .nodin-metrics { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.9rem; }
    .nodin-metric { border-radius:20px; padding:1rem; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.1); }
    .nodin-metric__label { display:block; font-size:.82rem; color:rgba(226,232,240,.74); text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem; }
    .nodin-metric__value { font-size:1.8rem; font-weight:800; color:#fff; line-height:1; }
    .nodin-alert { margin-top:1rem; border:0; border-radius:18px; padding:.95rem 1rem; }
    .nodin-table-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1.2rem 1.25rem 0; }
    .nodin-panel__title { margin:0; font-size:1.05rem; font-weight:800; color:var(--ink); }
    .nodin-panel__subtitle { margin:.28rem 0 0; color:var(--muted); font-size:.92rem; }
    .nodin-table-wrap { padding:1.25rem; width:100%; overflow-x:auto; }
    .nodin-table { width:100%; margin-bottom:0; border-collapse:separate; border-spacing:0; }
    .nodin-table thead th { background:#eff6ff; color:#1e3a8a; font-size:.76rem; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid rgba(191,219,254,.8); }
    .nodin-table th, .nodin-table td { padding:.8rem .72rem; vertical-align:middle; border-top:1px solid rgba(226,232,240,.7); }
    .nodin-table tbody tr:hover { background:rgba(239,246,255,.7); }
    .nodin-table tfoot td { background:#f8fafc; color:#0f172a; font-weight:800; }
    .text-number { text-align:right; white-space:nowrap; }
    .nodin-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .7rem; border-radius:999px; font-size:.76rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .nodin-chip--approved { background:rgba(16,185,129,.12); color:#047857; }
    .nodin-chip--waiting { background:rgba(245,158,11,.12); color:#b45309; }
    .nodin-chip--blue { background:rgba(59,130,246,.1); color:#1d4ed8; }
    .nodin-modal .modal-dialog.modal-xxl { width:84vw; max-width:84vw; }
    .nodin-modal .select2-container { width:100% !important; }
    @media (max-width: 1199.98px) { .nodin-hero__grid { grid-template-columns:1fr; } }
    @media (max-width: 767.98px) {
        .nodin-shell { padding:.8rem; }
        .nodin-hero__grid { padding:1rem; }
        .nodin-hero h1 { font-size:1.5rem; }
        .nodin-metrics { grid-template-columns:1fr; }
        .nodin-modal .modal-dialog.modal-xxl { width:calc(100vw - 1rem); max-width:calc(100vw - 1rem); margin:.5rem auto; }
    }
</style>

<div class="content-wrapper nodin-page">
    <div class="content-header">
        <div class="container-fluid nodin-shell">
            <section class="nodin-hero">
                <div class="nodin-hero__grid">
                    <div>
                        <span class="nodin-hero__eyebrow"><i class="fas fa-file-signature"></i> Nota Dinas PO</span>
                        <h1>Kelola NODIN sebagai approval internal HO sebelum item diterbitkan menjadi PO pabrik.</h1>
                        <p>Modul ini sudah dipisah dari PR, sehingga satu NODIN bisa menampung beberapa PR approved dan nantinya bisa dipecah ke banyak PO sesuai pabrik.</p>
                        <div style="margin-top:1.25rem;">
                            <button type="button" class="btn btn-light font-weight-bold" data-toggle="modal" data-target="#modalNodin">
                                <i class="fas fa-plus-circle mr-1"></i> <?= empty($activeNodin) ? 'Buat NODIN Baru' : ($activeNodinEditMode ? 'Mode Edit NODIN' : 'Kelola NODIN') ?>
                            </button>
                        </div>
                    </div>
                    <div class="nodin-metrics">
                        <div class="nodin-metric">
                            <span class="nodin-metric__label">Total NODIN</span>
                            <span class="nodin-metric__value"><?= number_format(count($nodinRows), 0, ',', '.') ?></span>
                        </div>
                        <div class="nodin-metric">
                            <span class="nodin-metric__label">PR Kandidat</span>
                            <span class="nodin-metric__value"><?= number_format(count($approvedPurchaseRequests), 0, ',', '.') ?></span>
                        </div>
                        <div class="nodin-metric">
                            <span class="nodin-metric__label">Qty Draft Aktif</span>
                            <span class="nodin-metric__value"><?= number_format($totalQty, 0, ',', '.') ?></span>
                        </div>
                        <div class="nodin-metric">
                            <span class="nodin-metric__label">Nominal Draft Aktif</span>
                            <span class="nodin-metric__value"><?= number_format($totalNominal, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?><div class="alert alert-success nodin-alert"><?= $flashSuccess ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="alert alert-danger nodin-alert"><?= $flashError ?></div><?php endif; ?>

            <section class="nodin-table-shell" style="margin-top:1.2rem;">
                <div class="nodin-table-head">
                    <div>
                        <h2 class="nodin-panel__title">Daftar Nota Dinas PO</h2>
                        <p class="nodin-panel__subtitle">Ringkasan NODIN lintas PR beserta progres approval dan nominal usulan PO.</p>
                    </div>
                    <span class="nodin-chip nodin-chip--blue"><i class="fas fa-layer-group"></i> Multi PR</span>
                </div>
                <div class="nodin-table-wrap">
                    <?php if (!empty($nodinRows)): ?>
                        <table id="table_nodin" class="table nodin-table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Dokumen NODIN</th>
                                    <th>Referensi PR</th>
                                    <th>Project</th>
                                    <th class="text-number">Item</th>
                                    <th class="text-number">Qty NODIN</th>
                                    <th class="text-number">Nominal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rowNumber = 1; ?>
                                <?php foreach ($nodinRows as $row): ?>
                                    <tr>
                                        <td><?= $rowNumber++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($row['nomor_nota_dinas'] ?? '-'), ENT_QUOTES) ?></strong>
                                            <div class="text-muted small"><?= htmlspecialchars((string) ($row['tanggal_nota_dinas'] ?? '-'), ENT_QUOTES) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['nomor_purchase_request_refs'] ?? '-'), ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['nama_project_refs'] ?? '-'), ENT_QUOTES) ?></td>
                                        <td class="text-number"><?= number_format($row['total_item'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="text-number"><?= number_format($row['total_qty_nodin'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="text-number"><?= number_format($row['total_nominal_nodin'] ?? 0, 0, ',', '.') ?></td>
                                        <td><span class="nodin-chip nodin-chip--<?= htmlspecialchars((string) ($row['workflow_status_tone'] ?? 'waiting'), ENT_QUOTES) ?>"><?= htmlspecialchars((string) ($row['workflow_status_label'] ?? 'Waiting'), ENT_QUOTES) ?></span></td>
                                        <td>
                                            <div class="d-flex flex-wrap" style="gap:.4rem;">
                                                <a href="<?= base_url('Logistik_Nota_Dinas_Po?id=' . rawurlencode((string) $row['id_nota_dinas_po'])) ?>" class="btn btn-sm btn-outline-primary">Kelola</a>
                                                <a href="<?= base_url('Logistik_Nota_Dinas_Po?id=' . rawurlencode((string) $row['id_nota_dinas_po']) . '&edit=1') ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                                <a href="<?= base_url('Logistik_Nota_Dinas_Po/delete_nodin/' . rawurlencode((string) $row['id_nota_dinas_po'])) ?>" class="btn btn-sm btn-outline-danger btn-delete-nodin" data-nomor="<?= htmlspecialchars((string) ($row['nomor_nota_dinas'] ?? ''), ENT_QUOTES) ?>" data-status="<?= htmlspecialchars((string) ($row['workflow_status_label'] ?? ''), ENT_QUOTES) ?>">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-muted">Belum ada data NODIN.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade nodin-modal" id="modalNodin" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content" style="border-radius:24px;overflow:hidden;">
            <form action="<?= base_url('Logistik_Nota_Dinas_Po/save_nodin') ?>" method="post" id="form-nodin" data-is-update="<?= empty($activeNodin) ? '0' : '1' ?>" data-approval-progress="<?= (int) ($activeNodin['workflow_progress'] ?? 0) ?>">
                <input type="hidden" name="id_nota_dinas_po" value="<?= htmlspecialchars((string) ($activeNodin['id_nota_dinas_po'] ?? ''), ENT_QUOTES) ?>">
                <div class="modal-header text-white" style="border-bottom:0;background:linear-gradient(135deg,#0f172a,#1d4ed8);">
                    <div>
                        <h4 class="modal-title mb-1"><?= empty($activeNodin) ? 'Buat NODIN Baru' : ($activeNodinEditMode ? 'Edit NODIN' : 'Kelola NODIN') ?></h4>
                        <small><?= $activeNodinReadOnly ? 'Mode kelola bersifat read-only. Gunakan tombol Edit jika ingin mengubah data dan mengulangi approval.' : 'Pilih satu atau beberapa PR approved, lalu susun item usulan PO per pabrik.' ?></small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="background:linear-gradient(180deg,rgba(248,250,252,.98),rgba(241,245,249,.95));">
                    <?php if ($activeNodinReadOnly): ?>
                        <div class="alert alert-warning mb-3">
                            NODIN ini sedang dibuka dalam mode baca. Data tidak bisa diedit langsung dari menu kelola.
                            <?php if ($canManageNodin): ?>
                                Gunakan tombol <strong>Edit</strong> untuk mengubah data. Saat disimpan, proses approval akan diulang dari awal.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Pilih PR Approved</label>
                                <select name="selected_purchase_request[]" id="nodin_selected_purchase_request" class="form-control" multiple required <?= $activeNodinReadOnly ? 'disabled' : '' ?>>
                                    <?php foreach ($approvedPurchaseRequests as $pr): ?>
                                        <?php $selectedPr = in_array((string) $pr['id_purchase_request'], array_map('strval', $activeNodinPurchaseRequestIds), true); ?>
                                        <option value="<?= $pr['id_purchase_request'] ?>" <?= $selectedPr ? 'selected' : '' ?>>
                                            <?= $pr['nomor_purchase_request'] ?> | <?= $pr['nama_project'] ?: '-' ?> | Outstanding <?= number_format($pr['total_qty_outstanding_pr'] ?? 0, 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Nota Dinas</label>
                                <input type="text" name="nomor_nota_dinas" class="form-control" value="<?= htmlspecialchars((string) ($activeNodin['nomor_nota_dinas'] ?? ''), ENT_QUOTES) ?>" required <?= $activeNodinReadOnly ? 'readonly' : '' ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Nota Dinas</label>
                                <input type="date" name="tanggal_nota_dinas" class="form-control" value="<?= !empty($activeNodin['tanggal_nota_dinas']) ? $activeNodin['tanggal_nota_dinas'] : date('Y-m-d') ?>" required <?= $activeNodinReadOnly ? 'readonly' : '' ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ditujukan Kepada</label>
                                <input type="text" name="ditujukan_kepada" class="form-control" value="<?= htmlspecialchars((string) ($activeNodin['ditujukan_kepada'] ?? ''), ENT_QUOTES) ?>" <?= $activeNodinReadOnly ? 'readonly' : '' ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Dibuat Oleh</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars((string) $this->session->userdata('nama_user'), ENT_QUOTES) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label>Tujuan Penerbitan PO</label>
                                <input type="text" name="tujuan_penerbitan_po" class="form-control" value="<?= htmlspecialchars((string) ($activeNodin['tujuan_penerbitan_po'] ?? ''), ENT_QUOTES) ?>" required <?= $activeNodinReadOnly ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($activeNodin) && !empty($activeNodinCurrentApprovalKey)): ?>
                        <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between" style="gap:.75rem;">
                            <span class="nodin-chip nodin-chip--waiting">Current: <?= htmlspecialchars((string) $activeNodinCurrentApprovalLabel, ENT_QUOTES) ?></span>
                            <a href="#" class="btn btn-success btn-sm btn-approve-nodin <?= $canApproveCurrentNodinStage ? '' : 'disabled' ?>" data-id="<?= htmlspecialchars((string) $activeNodin['id_nota_dinas_po'], ENT_QUOTES) ?>" data-tipe="<?= htmlspecialchars((string) $activeNodinCurrentApprovalKey, ENT_QUOTES) ?>">
                                <i class="fa fa-check mr-1"></i> Approve Tahap Ini
                            </a>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered nodin-table" id="table_nodin_items">
                            <thead>
                                <tr>
                                    <th>PR</th>
                                    <th>Nama Material</th>
                                    <th>Satuan</th>
                                    <th class="text-number">Kebutuhan Project</th>
                                    <th class="text-number">Outstanding PR</th>
                                    <th class="text-number">PO Usulan</th>
                                    <th class="text-number">Harga Satuan</th>
                                    <th class="text-number">Harga Total</th>
                                    <th>Vendor / Pabrik</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($activeNodinCandidateItems)): ?>
                                    <?php $rowIndex = 0; ?>
                                    <?php foreach ($activeNodinCandidateItems as $item): ?>
                                        <?php
                                        $existingDetails = $detailByPrDetail[(string) ($item['id_purchase_request_detail'] ?? '')] ?? [];
                                        if (empty($existingDetails)) {
                                            $existingDetails = [[]];
                                        }
                                        ?>
                                        <?php foreach ($existingDetails as $existingDetail): ?>
                                            <?php
                                            $selectedPabrikId = (string) ($existingDetail['id_pabrik'] ?? '');
                                            if ($selectedPabrikId === '' && !empty($existingDetail['vendor_pabrik'])) {
                                                $selectedPabrikId = $masterPabrikByName[strtolower(trim((string) $existingDetail['vendor_pabrik']))] ?? '';
                                            }
                                            ?>
                                            <tr class="js-nodin-row" data-detail-id="<?= htmlspecialchars((string) ($item['id_purchase_request_detail'] ?? ''), ENT_QUOTES) ?>">
                                                <td>
                                                    <span class="js-nodin-pr-label"><?= htmlspecialchars((string) ($item['nomor_purchase_request'] ?? '-'), ENT_QUOTES) ?></span>
                                                    <input type="hidden" name="id_purchase_request_detail[<?= $rowIndex ?>]" value="<?= htmlspecialchars((string) ($item['id_purchase_request_detail'] ?? ''), ENT_QUOTES) ?>">
                                                    <input type="hidden" name="id_kode_item[<?= $rowIndex ?>]" value="<?= htmlspecialchars((string) ($item['id_kode_item'] ?? ''), ENT_QUOTES) ?>">
                                                    <input type="hidden" name="kebutuhan_project[<?= $rowIndex ?>]" value="<?= htmlspecialchars((string) ($item['volume_planning_final'] ?? 0), ENT_QUOTES) ?>">
                                                    <input type="hidden" name="outstanding_pr[<?= $rowIndex ?>]" value="<?= htmlspecialchars((string) ($item['qty_outstanding_pr'] ?? 0), ENT_QUOTES) ?>">
                                                </td>
                                                <td><span class="js-nodin-item-label"><?= htmlspecialchars((string) ($item['nama_item'] ?? '-'), ENT_QUOTES) ?></span></td>
                                                <td><span class="js-nodin-satuan-label"><?= htmlspecialchars((string) ($item['satuan_item'] ?? '-'), ENT_QUOTES) ?></span></td>
                                                <td class="text-number js-nodin-kebutuhan"><?= number_format($item['volume_planning_final'] ?? 0, 0, ',', '.') ?></td>
                                                <td class="text-number js-nodin-outstanding"><?= number_format($item['qty_outstanding_pr'] ?? 0, 0, ',', '.') ?></td>
                                                <td><input type="number" class="form-control text-right js-nodin-qty" name="qty_po_nodin[<?= $rowIndex ?>]" min="0" step="1" value="<?= htmlspecialchars((string) ($existingDetail['qty_po_nodin'] ?? (int) ($item['qty_outstanding_pr'] ?? 0)), ENT_QUOTES) ?>" required <?= $activeNodinReadOnly ? 'readonly' : '' ?>></td>
                                                <td><input type="number" class="form-control text-right js-nodin-harga" name="harga_satuan[<?= $rowIndex ?>]" min="0" step="1" value="<?= htmlspecialchars((string) ($existingDetail['harga_satuan'] ?? '0'), ENT_QUOTES) ?>" <?= $activeNodinReadOnly ? 'readonly' : '' ?>></td>
                                                <td class="text-number js-nodin-line-total"><?= number_format(((float) ($existingDetail['qty_po_nodin'] ?? (int) ($item['qty_outstanding_pr'] ?? 0))) * ((float) ($existingDetail['harga_satuan'] ?? 0)), 0, ',', '.') ?></td>
                                                <td>
                                                    <select class="form-control js-select-pabrik" name="id_pabrik[<?= $rowIndex ?>]" <?= $activeNodinReadOnly ? 'disabled' : '' ?>>
                                                        <option value="">Pilih Pabrik</option>
                                                        <?php foreach ($masterPabrikOptions as $pabrik): ?>
                                                            <option value="<?= $pabrik['id_pabrik'] ?>" <?= $selectedPabrikId === (string) $pabrik['id_pabrik'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars((string) $pabrik['nama_pabrik'], ENT_QUOTES) ?> | <?= htmlspecialchars((string) $pabrik['jenis_pabrik'], ENT_QUOTES) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control" name="keterangan_nodin[<?= $rowIndex ?>]" value="<?= htmlspecialchars((string) ($existingDetail['keterangan'] ?? ''), ENT_QUOTES) ?>" <?= $activeNodinReadOnly ? 'readonly' : '' ?>></td>
                                                <td>
                                                    <?php if (!$activeNodinReadOnly): ?>
                                                        <div class="d-flex flex-column" style="gap:.35rem;min-width:72px;">
                                                            <button type="button" class="btn btn-sm btn-outline-primary js-nodin-add-split">Split</button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger js-nodin-remove-split">Hapus</button>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php $rowIndex++; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="11" class="text-center text-muted">Pilih PR approved untuk memuat item NODIN.</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">TOTAL</td>
                                    <td class="text-number" id="nodin-total-kebutuhan">0</td>
                                    <td class="text-number" id="nodin-total-outstanding">0</td>
                                    <td class="text-number" id="nodin-total-qty">0</td>
                                    <td class="text-number" id="nodin-total-harga-satuan">0</td>
                                    <td class="text-number" id="nodin-total-nilai">0</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <?php if (!$activeNodinReadOnly): ?>
                        <button type="submit" class="btn btn-primary"><?= empty($activeNodin) ? 'Simpan NODIN' : 'Update NODIN' ?></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let nodinRowSequence = <?= !empty($activeNodinCandidateItems) ? (int) (array_sum(array_map(static function ($rows) {
        return is_array($rows) ? count($rows) : 0;
    }, $detailByPrDetail)) ?: count($activeNodinCandidateItems)) : 0 ?>;

    function formatNodinNumber(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value || 0);
    }

    function parseNodinNumber(value) {
        const normalized = String(value || '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
        return parseFloat(normalized) || 0;
    }

    function refreshNodinFooter() {
        let totalKebutuhan = 0;
        let totalOutstanding = 0;
        let totalQty = 0;
        let totalHargaSatuan = 0;
        let totalNilai = 0;

        $('#table_nodin_items tbody tr').each(function() {
            const kebutuhan = parseNodinNumber($(this).find('.js-nodin-kebutuhan').text());
            const outstanding = parseNodinNumber($(this).find('.js-nodin-outstanding').text());
            const qty = parseFloat($(this).find('.js-nodin-qty').val() || 0);
            const harga = parseFloat($(this).find('.js-nodin-harga').val() || 0);
            const total = qty * harga;
            totalKebutuhan += kebutuhan;
            totalOutstanding += outstanding;
            totalQty += qty;
            totalHargaSatuan += harga;
            totalNilai += total;
            $(this).find('.js-nodin-line-total').text(formatNodinNumber(total));
        });

        $('#nodin-total-kebutuhan').text(formatNodinNumber(totalKebutuhan));
        $('#nodin-total-outstanding').text(formatNodinNumber(totalOutstanding));
        $('#nodin-total-qty').text(formatNodinNumber(totalQty));
        $('#nodin-total-harga-satuan').text(formatNodinNumber(totalHargaSatuan));
        $('#nodin-total-nilai').text(formatNodinNumber(totalNilai));
    }

    function getPabrikOptionHtml() {
        return <?= json_encode(array_map(static function ($pabrik) {
            return [
                'id' => (string) $pabrik['id_pabrik'],
                'label' => (string) ($pabrik['nama_pabrik'] . ' | ' . $pabrik['jenis_pabrik']),
            ];
        }, $masterPabrikOptions)) ?>.map(function(option) {
            return '<option value="' + option.id + '">' + option.label + '</option>';
        }).join('');
    }

    function buildNodinSplitRowHtml(sourceRow) {
        const rowIndex = nodinRowSequence++;
        const detailId = sourceRow.data('detail-id') || '';
        const prLabel = sourceRow.find('.js-nodin-pr-label').text().trim();
        const itemLabel = sourceRow.find('.js-nodin-item-label').text().trim();
        const satuanLabel = sourceRow.find('.js-nodin-satuan-label').text().trim();
        const kebutuhanText = sourceRow.find('.js-nodin-kebutuhan').text().trim();
        const outstandingText = sourceRow.find('.js-nodin-outstanding').text().trim();
        const kebutuhanValue = sourceRow.find('input[name^="kebutuhan_project["]').val() || 0;
        const outstandingValue = sourceRow.find('input[name^="outstanding_pr["]').val() || 0;
        const kodeItem = sourceRow.find('input[name^="id_kode_item["]').val() || '';
        const optionHtml = getPabrikOptionHtml();

        return '' +
            '<tr class="js-nodin-row" data-detail-id="' + detailId + '">' +
                '<td>' +
                    '<span class="js-nodin-pr-label">' + prLabel + '</span>' +
                    '<input type="hidden" name="id_purchase_request_detail[' + rowIndex + ']" value="' + detailId + '">' +
                    '<input type="hidden" name="id_kode_item[' + rowIndex + ']" value="' + kodeItem + '">' +
                    '<input type="hidden" name="kebutuhan_project[' + rowIndex + ']" value="' + kebutuhanValue + '">' +
                    '<input type="hidden" name="outstanding_pr[' + rowIndex + ']" value="' + outstandingValue + '">' +
                '</td>' +
                '<td><span class="js-nodin-item-label">' + itemLabel + '</span></td>' +
                '<td><span class="js-nodin-satuan-label">' + satuanLabel + '</span></td>' +
                '<td class="text-number js-nodin-kebutuhan">' + kebutuhanText + '</td>' +
                '<td class="text-number js-nodin-outstanding">' + outstandingText + '</td>' +
                '<td><input type="number" class="form-control text-right js-nodin-qty" name="qty_po_nodin[' + rowIndex + ']" min="0" step="1" value="0" required></td>' +
                '<td><input type="number" class="form-control text-right js-nodin-harga" name="harga_satuan[' + rowIndex + ']" min="0" step="1" value="0"></td>' +
                '<td class="text-number js-nodin-line-total">0</td>' +
                '<td><select class="form-control js-select-pabrik" name="id_pabrik[' + rowIndex + ']"><option value="">Pilih Pabrik</option>' + optionHtml + '</select></td>' +
                '<td><input type="text" class="form-control" name="keterangan_nodin[' + rowIndex + ']" value=""></td>' +
                '<td><div class="d-flex flex-column" style="gap:.35rem;min-width:72px;"><button type="button" class="btn btn-sm btn-outline-primary js-nodin-add-split">Split</button><button type="button" class="btn btn-sm btn-outline-danger js-nodin-remove-split">Hapus</button></div></td>' +
            '</tr>';
    }

    function initPurchaseRequestSelect() {
        if (!$.fn.select2) {
            return;
        }

        const $select = $('#nodin_selected_purchase_request');
        if ($select.hasClass('select2-hidden-accessible')) {
            return;
        }

        $select.select2({
            width: '100%',
            dropdownParent: $('#modalNodin'),
            placeholder: 'Pilih satu atau beberapa PR'
        });
    }

    function initPabrikSelects() {
        if (!$.fn.select2) {
            return;
        }

        $('.js-select-pabrik').each(function() {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalNodin'),
                placeholder: 'Pilih Pabrik',
                allowClear: true
            });
        });
    }

    function renderNodinItems(rows) {
        const tbody = $('#table_nodin_items tbody');
        tbody.empty();
        nodinRowSequence = 0;

        if (!rows.length) {
            tbody.append('<tr><td colspan="11" class="text-center text-muted">Tidak ada item outstanding dari PR yang dipilih.</td></tr>');
            refreshNodinFooter();
            return;
        }

        rows.forEach(function(item, index) {
            const kebutuhan = parseFloat(item.volume_planning_final || 0);
            const outstanding = parseFloat(item.qty_outstanding_pr || 0);
            const optionHtml = getPabrikOptionHtml();
            const rowIndex = nodinRowSequence++;

            tbody.append(
                '<tr class="js-nodin-row" data-detail-id="' + (item.id_purchase_request_detail || '') + '">' +
                    '<td>' + (item.nomor_purchase_request || '-') +
                        '<span class="js-nodin-pr-label" style="display:none;">' + (item.nomor_purchase_request || '-') + '</span>' +
                        '<input type="hidden" name="id_purchase_request_detail[' + rowIndex + ']" value="' + (item.id_purchase_request_detail || '') + '">' +
                        '<input type="hidden" name="id_kode_item[' + rowIndex + ']" value="' + (item.id_kode_item || '') + '">' +
                        '<input type="hidden" name="kebutuhan_project[' + rowIndex + ']" value="' + kebutuhan + '">' +
                        '<input type="hidden" name="outstanding_pr[' + rowIndex + ']" value="' + outstanding + '">' +
                    '</td>' +
                    '<td><span class="js-nodin-item-label">' + (item.nama_item || '-') + '</span></td>' +
                    '<td><span class="js-nodin-satuan-label">' + (item.satuan_item || '-') + '</span></td>' +
                    '<td class="text-number js-nodin-kebutuhan">' + formatNodinNumber(kebutuhan) + '</td>' +
                    '<td class="text-number js-nodin-outstanding">' + formatNodinNumber(outstanding) + '</td>' +
                    '<td><input type="number" class="form-control text-right js-nodin-qty" name="qty_po_nodin[' + rowIndex + ']" min="0" step="1" value="' + outstanding + '" required></td>' +
                    '<td><input type="number" class="form-control text-right js-nodin-harga" name="harga_satuan[' + rowIndex + ']" min="0" step="1" value="0"></td>' +
                    '<td class="text-number js-nodin-line-total">0</td>' +
                    '<td><select class="form-control js-select-pabrik" name="id_pabrik[' + rowIndex + ']"><option value="">Pilih Pabrik</option>' + optionHtml + '</select></td>' +
                    '<td><input type="text" class="form-control" name="keterangan_nodin[' + rowIndex + ']" value=""></td>' +
                    '<td><div class="d-flex flex-column" style="gap:.35rem;min-width:72px;"><button type="button" class="btn btn-sm btn-outline-primary js-nodin-add-split">Split</button><button type="button" class="btn btn-sm btn-outline-danger js-nodin-remove-split">Hapus</button></div></td>' +
                '</tr>'
            );
        });

        initPabrikSelects();
        refreshNodinFooter();
    }

    $(document).ready(function() {
        let nodinItemsRequest = null;
        let nodinItemsRequestToken = 0;

        if ($('#table_nodin').length) {
            $('#table_nodin').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: { previous: 'Prev', next: 'Next' }
                }
            });
        }

        initPurchaseRequestSelect();
        initPabrikSelects();
        refreshNodinFooter();

        $('#nodin_selected_purchase_request').on('change', function() {
            const values = $(this).val() || [];
            if (!values.length) {
                if (nodinItemsRequest && nodinItemsRequest.readyState !== 4) {
                    nodinItemsRequest.abort();
                }
                renderNodinItems([]);
                return;
            }

            const currentToken = ++nodinItemsRequestToken;
            const tbody = $('#table_nodin_items tbody');
            tbody.html('<tr><td colspan="10" class="text-center text-muted">Memuat item PR terpilih...</td></tr>');

            if (nodinItemsRequest && nodinItemsRequest.readyState !== 4) {
                nodinItemsRequest.abort();
            }

            nodinItemsRequest = $.ajax({
                url: "<?= base_url('Logistik_Nota_Dinas_Po/get_purchase_request_items') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    id_purchase_request_csv: values.join(',')
                },
                success: function(response) {
                    if (currentToken !== nodinItemsRequestToken) {
                        return;
                    }
                    renderNodinItems(response || []);
                },
                error: function(xhr, status) {
                    if (status === 'abort' || currentToken !== nodinItemsRequestToken) {
                        return;
                    }
                    renderNodinItems([]);
                    Swal.fire('Gagal', 'Tidak bisa memuat item PR untuk NODIN.', 'error');
                }
            });
        });

        $('#table_nodin_items').on('input', '.js-nodin-qty, .js-nodin-harga', function() {
            refreshNodinFooter();
        });

        $('#table_nodin_items').on('click', '.js-nodin-add-split', function() {
            const $sourceRow = $(this).closest('tr');
            const html = buildNodinSplitRowHtml($sourceRow);
            $sourceRow.after(html);
            initPabrikSelects();
            refreshNodinFooter();
        });

        $('#table_nodin_items').on('click', '.js-nodin-remove-split', function() {
            const $rows = $('#table_nodin_items tbody .js-nodin-row');
            if ($rows.length <= 1) {
                $(this).closest('tr').find('.js-nodin-qty').val(0);
                $(this).closest('tr').find('.js-nodin-harga').val(0);
                $(this).closest('tr').find('.js-select-pabrik').val('').trigger('change.select2');
                $(this).closest('tr').find('input[name^="keterangan_nodin["]').val('');
            } else {
                $(this).closest('tr').remove();
            }
            refreshNodinFooter();
        });

        $('.btn-approve-nodin').click(function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) {
                return false;
            }

            const idNodin = $(this).data('id');
            const tipe = $(this).data('tipe');
            Swal.fire({
                title: 'Approve NODIN ini?',
                text: 'Setelah tahap ini disetujui, NODIN akan maju ke approver berikutnya.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "<?= base_url('Logistik_Nota_Dinas_Po/approve_nodin') ?>",
                    type: 'POST',
                    data: { id_nota_dinas_po: idNodin, tipe: tipe },
                    success: function() {
                        Swal.fire('Berhasil!', 'Approval NODIN berhasil diperbarui.', 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        const responseMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan, coba lagi.';
                        Swal.fire('Gagal!', responseMessage, 'error');
                    }
                });
            });
        });

        $('.btn-delete-nodin').click(function(e) {
            e.preventDefault();

            const url = $(this).attr('href');
            const nomorNodin = $(this).data('nomor') || 'NODIN ini';
            const statusNodin = ($(this).data('status') || '').toString().toLowerCase();
            const warningText = statusNodin === 'approved'
                ? nomorNodin + ' sudah full approved. Jika dihapus, approval dan seluruh item detailnya akan hilang.'
                : nomorNodin + ' akan dihapus beserta seluruh item detailnya.';

            Swal.fire({
                title: 'Hapus NODIN?',
                text: warningText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });

        $('#form-nodin').submit(function(event) {
            let hasInvalidQty = false;
            $('#form-nodin input[name^="qty_po_nodin"]').each(function() {
                if ((parseFloat($(this).val() || 0)) <= 0) {
                    hasInvalidQty = true;
                }
            });

            if (hasInvalidQty) {
                event.preventDefault();
                Swal.fire('Qty NODIN tidak valid', 'Qty PO usulan pada NODIN harus lebih dari 0.', 'warning');
                return;
            }

            const $form = $(this);
            const isUpdate = $form.data('is-update') === 1 || $form.data('is-update') === '1';
            const approvalProgress = parseInt($form.data('approval-progress') || 0, 10);
            const confirmationMessage = approvalProgress > 0
                ? 'Perubahan NODIN ini akan mengulang proses approval dari awal. Lanjutkan update?'
                : 'Edit NODIN ini akan tetap mengulang proses approval dari awal. Lanjutkan update?';

            if (isUpdate && !$form.data('confirmed')) {
                event.preventDefault();
                Swal.fire({
                    title: 'Update NODIN?',
                    text: confirmationMessage,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Ya, Update NODIN',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $form.data('confirmed', true);
                        $form.trigger('submit');
                    }
                });
            }
        });

        <?php if (!empty($activeNodin)): ?>
        $('#modalNodin').modal('show');
        <?php endif; ?>
    });
</script>
