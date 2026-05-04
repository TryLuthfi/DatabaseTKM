<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

$formatStatus = function ($row) {
    $statusPo = strtoupper((string) ($row['status_po'] ?? 'APPROVED'));
    if ($statusPo === 'COMPLETED') {
        return ['label' => 'Completed', 'tone' => 'approved'];
    }
    if ($statusPo === 'CLOSED') {
        return ['label' => 'Closed', 'tone' => 'slate'];
    }
    if ((float) ($row['total_outstanding'] ?? 0) <= 0) {
        return ['label' => 'Completed', 'tone' => 'approved'];
    }
    if ((float) ($row['total_qty_terkirim'] ?? 0) > 0) {
        return ['label' => 'Partial Delivery', 'tone' => 'waiting'];
    }

    return ['label' => 'Approved', 'tone' => 'blue'];
};
?>

<style>
    .po-revamp {
        --po-ink: #0f172a;
        --po-muted: #64748b;
        --po-line: rgba(148, 163, 184, 0.22);
        --po-surface: rgba(255, 255, 255, 0.96);
        --po-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
    }

    .po-revamp .content-header {
        padding-bottom: 0;
    }

    .po-shell {
        padding: 1.15rem;
    }

    .po-hero {
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

    .po-hero__grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 1.2rem;
        padding: 1.5rem;
    }

    .po-hero__eyebrow {
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

    .po-hero h1 {
        margin: 1rem 0 0.75rem;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
    }

    .po-hero p {
        max-width: 44rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.86);
        line-height: 1.7;
    }

    .po-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-content: start;
    }

    .po-metric {
        border-radius: 20px;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
    }

    .po-metric__label {
        display: block;
        font-size: 0.82rem;
        color: rgba(226, 232, 240, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.45rem;
    }

    .po-metric__value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .po-metric__hint {
        display: block;
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.66);
        font-size: 0.88rem;
    }

    .po-alert {
        margin-top: 1rem;
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1rem;
    }

    .po-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .po-panel,
    .po-table-shell {
        border: 1px solid var(--po-line);
        border-radius: 24px;
        background: var(--po-surface);
        box-shadow: var(--po-shadow);
        overflow: hidden;
    }

    .po-panel__head,
    .po-table-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.25rem 0;
    }

    .po-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--po-ink);
    }

    .po-panel__subtitle {
        margin: 0.28rem 0 0;
        color: var(--po-muted);
        font-size: 0.92rem;
    }

    .po-panel__body,
    .po-table-wrap {
        padding: 1.25rem;
    }

    .po-flow {
        display: grid;
        gap: 0.9rem;
    }

    .po-flow-card {
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
    }

    .po-flow-card__step {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .po-flow-card__title {
        margin: 0.55rem 0 0.35rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--po-ink);
    }

    .po-flow-card__text {
        margin: 0;
        color: var(--po-muted);
        line-height: 1.65;
        font-size: 0.92rem;
    }

    .po-chip {
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

    .po-chip--blue { background: rgba(59, 130, 246, 0.1); color: #1d4ed8; }
    .po-chip--approved { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .po-chip--waiting { background: rgba(245, 158, 11, 0.12); color: #b45309; }
    .po-chip--slate { background: rgba(15, 23, 42, 0.06); color: #334155; }

    .po-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .po-table-wrap .dataTables_wrapper,
    .po-table-wrap table.dataTable {
        width: 100% !important;
    }

    .po-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .po-table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom: 1px solid rgba(191, 219, 254, 0.8);
    }

    .po-table th,
    .po-table td {
        padding: 0.8rem 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(226, 232, 240, 0.7);
    }

    .po-table tbody tr:hover {
        background: rgba(239, 246, 255, 0.7);
    }

    .po-doc-id {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .po-doc-id strong {
        color: var(--po-ink);
    }

    .po-doc-id span {
        color: var(--po-muted);
        font-size: 0.85rem;
    }

    .po-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .po-action-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: 0;
    }

    .po-action-btn--view {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    .po-empty {
        padding: 1rem;
        border-radius: 16px;
        text-align: center;
        background: rgba(248, 250, 252, 0.8);
        border: 1px dashed rgba(148, 163, 184, 0.36);
        color: #64748b;
    }

    @media (max-width: 1199.98px) {
        .po-hero__grid,
        .po-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .po-shell {
            padding: 0.8rem;
        }

        .po-hero__grid {
            padding: 1rem;
        }

        .po-hero h1 {
            font-size: 1.5rem;
        }

        .po-metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper po-revamp">
    <div class="content-header">
        <div class="container-fluid po-shell">
            <section class="po-hero">
                <div class="po-hero__grid">
                    <div>
                        <span class="po-hero__eyebrow">
                            <i class="fas fa-file-invoice"></i>
                            PO Factory Monitoring
                        </span>
                        <h1>Dashboard purchase order pabrik untuk memantau outstanding dan progres pengiriman.</h1>
                        <p>
                            Modul ini diposisikan sebagai kelanjutan dari PR yang sudah selesai approval. Fokus utamanya adalah memantau header PO,
                            total item, total kuantitas yang sudah dipesan, kuantitas yang sudah terkirim, dan outstanding yang masih perlu ditindaklanjuti.
                        </p>
                        <div style="margin-top:1.25rem;">
                            <button type="button" class="btn btn-light font-weight-bold" data-toggle="modal" data-target="#modalCreatePoFromPr">
                                <i class="fas fa-plus-circle mr-1"></i> Buat PO dari NODIN Approved
                            </button>
                        </div>
                    </div>

                    <div class="po-metric-grid">
                        <div class="po-metric">
                            <span class="po-metric__label">Total PO</span>
                            <span class="po-metric__value"><?= number_format($poStats['total_po'] ?? 0, 0, ',', '.') ?></span>
                            <span class="po-metric__hint">Jumlah dokumen PO yang sudah tercatat.</span>
                        </div>
                        <div class="po-metric">
                            <span class="po-metric__label">Total Qty PO</span>
                            <span class="po-metric__value"><?= number_format($poStats['total_qty_po'] ?? 0, 0, ',', '.') ?></span>
                            <span class="po-metric__hint">Akumulasi kuantitas seluruh item yang dipesan.</span>
                        </div>
                        <div class="po-metric">
                            <span class="po-metric__label">Total Terkirim</span>
                            <span class="po-metric__value"><?= number_format($poStats['total_qty_terkirim'] ?? 0, 0, ',', '.') ?></span>
                            <span class="po-metric__hint">Volume pengiriman yang sudah berjalan dari PO aktif.</span>
                        </div>
                        <div class="po-metric">
                            <span class="po-metric__label">Outstanding</span>
                            <span class="po-metric__value"><?= number_format($poStats['total_outstanding'] ?? 0, 0, ',', '.') ?></span>
                            <span class="po-metric__hint">Sisa volume yang belum tertutup oleh pengiriman atau close manual.</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success po-alert"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger po-alert"><?= $flashError ?></div>
            <?php endif; ?>


            <section class="po-table-shell" style="margin-top: 1.2rem;">
                <div class="po-table-head">
                    <div>
                        <h2 class="po-panel__title">Daftar Purchase Order Pabrik</h2>
                        <p class="po-panel__subtitle">List utama untuk memantau nilai PO, qty terkirim, dan outstanding per dokumen.</p>
                    </div>
                    <span class="po-chip po-chip--blue"><i class="fas fa-table"></i> Monitoring PO</span>
                </div>
                <div class="po-table-wrap">
                    <?php if (!empty($poRows)): ?>
                        <table id="tabel_pesanan_pabrik" class="table po-table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Dokumen PO</th>
                                    <th>Pabrik</th>
                                    <th>Referensi PR</th>
                                    <th>Item</th>
                                    <th>Qty PO</th>
                                    <th>Qty Terkirim</th>
                                    <th>Outstanding</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $number = 1; ?>
                                <?php foreach ($poRows as $row): ?>
                                    <?php $status = $formatStatus($row); ?>
                                    <tr>
                                        <td><?= $number++ ?></td>
                                        <td>
                                            <div class="po-doc-id">
                                                <strong><?= $row['nomor_po_pabrik'] ?></strong>
                                                <span><?= $row['tanggal_po_pabrik'] ?></span>
                                            </div>
                                        </td>
                                        <td><?= $row['nama_pabrik'] ?: '-' ?></td>
                                        <td><?= $row['nomor_purchase_request'] ?: '-' ?></td>
                                        <td><?= number_format($row['total_item'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($row['total_qty_po'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($row['total_qty_terkirim'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($row['total_outstanding'] ?? 0, 0, ',', '.') ?></td>
                                        <td><?= number_format($row['total_nominal_po'] ?? 0, 0, ',', '.') ?></td>
                                        <td><span class="po-chip po-chip--<?= $status['tone'] ?>"><?= $status['label'] ?></span></td>
                                        <td>
                                            <div class="po-actions">
                                                <a class="po-action-btn po-action-btn--view" href="<?= site_url('Logistik_Pesanan_Pabrik_Detail/detailPesanan/' . $row['nomor_po_pabrik']) ?>" title="Lihat detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="po-empty">Belum ada data PO yang bisa ditampilkan.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreatePoFromPr" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius:24px;overflow:hidden;">
            <form action="<?= base_url('Logistik_Pesanan_Pabrik/create_po_from_pr') ?>" method="post" id="form-create-po">
                <div class="modal-header text-white" style="border-bottom:0;background:linear-gradient(135deg, #0f172a, #1d4ed8);">
                    <div>
                        <h4 class="modal-title mb-1">Buat PO dari PR Approved</h4>
                        <small>Pilih PR yang NODIN-nya sudah approved penuh, lalu tentukan item mana yang akan diteruskan menjadi PO.</small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="background:linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>PR + NODIN Approved</label>
                                <select name="id_purchase_request" id="po_id_purchase_request" class="form-control" required>
                                    <option value="">Pilih Purchase Request</option>
                                    <?php foreach ($approvedPurchaseRequests as $pr): ?>
                                        <option value="<?= $pr['id_purchase_request'] ?>" data-nomor-pr="<?= htmlspecialchars($pr['nomor_purchase_request'], ENT_QUOTES) ?>">
                                            <?= $pr['nomor_purchase_request'] ?> | <?= $pr['nama_project'] ?: '-' ?> | <?= $pr['kota_lokasi_gudang'] ?: '-' ?> | Outstanding <?= number_format($pr['total_qty_outstanding_pr'] ?? 0, 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="nomor_purchase_request" id="po_nomor_purchase_request">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pabrik</label>
                                <select name="id_pabrik" id="po_id_pabrik" class="form-control" required>
                                    <option value="">Pilih Pabrik</option>
                                    <?php foreach ($masterPabrik as $pabrik): ?>
                                        <option value="<?= $pabrik['id_pabrik'] ?>">
                                            <?= $pabrik['nama_pabrik'] ?> | <?= $pabrik['jenis_pabrik'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor PO Pabrik</label>
                                <input type="text" name="nomor_po_pabrik" id="po_nomor_po_pabrik" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal PO</label>
                                <input type="date" name="tanggal_po_pabrik" id="po_tanggal_po_pabrik" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="table_create_po_items">
                            <thead>
                                <tr>
                                    <th>Pilih</th>
                                    <th>Nama Item</th>
                                    <th>Satuan</th>
                                    <th>Qty Request</th>
                                    <th>Volume Planning</th>
                                    <th>Qty Sudah PO</th>
                                    <th>Outstanding PR</th>
                                    <th>Qty PO</th>
                                    <th>Harga Item</th>
                                    <th>Keterangan Planning</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Pilih PR approved untuk memuat item.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('#tabel_pesanan_pabrik').length) {
            $('#tabel_pesanan_pabrik').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                }
            });
        }

        $('#po_id_purchase_request, #po_id_pabrik').select2({
            width: '100%',
            dropdownParent: $('#modalCreatePoFromPr')
        });

        function renderPoItems(rows) {
            const tbody = $('#table_create_po_items tbody');
            tbody.empty();

            if (!rows.length) {
                tbody.append('<tr><td colspan="10" class="text-center text-muted">Tidak ada item outstanding yang bisa diteruskan ke PO.</td></tr>');
                return;
            }

            rows.forEach(function(item, index) {
                const volumePlanning = parseFloat(item.volume_planning_final || 0);
                const qtySudahPo = parseFloat(item.qty_po_teralokasi || 0);
                const qtyOutstanding = parseFloat(item.qty_outstanding_pr || 0);
                tbody.append(`
                    <tr>
                        <td>
                            <input type="checkbox" name="selected_item[]" value="${index}" checked>
                            <input type="hidden" name="id_purchase_request_detail[${index}]" value="${item.id_purchase_request_detail}">
                            <input type="hidden" name="id_kode_item[${index}]" value="${item.id_kode_item}">
                            <input type="hidden" name="volume_planning_snapshot[${index}]" value="${volumePlanning}">
                        </td>
                        <td>${item.nama_item || '-'}</td>
                        <td>${item.satuan_item || '-'}</td>
                        <td>${item.qty_request || 0}</td>
                        <td>${volumePlanning}</td>
                        <td>${qtySudahPo}</td>
                        <td><strong>${qtyOutstanding}</strong></td>
                        <td><input type="number" class="form-control" name="qty_item[${index}]" value="${qtyOutstanding}" min="0" max="${qtyOutstanding}" step="1"></td>
                        <td><input type="number" class="form-control" name="harga_item[${index}]" value="0" min="0" step="1"></td>
                        <td>${item.keterangan_planning || item.keterangan || '-'}</td>
                    </tr>
                `);
            });
        }

        $('#po_id_purchase_request').on('change', function() {
            const idPurchaseRequest = $(this).val();
            const nomorPr = $('#po_id_purchase_request option:selected').data('nomor-pr') || '';
            $('#po_nomor_purchase_request').val(nomorPr);

            if (!idPurchaseRequest) {
                renderPoItems([]);
                return;
            }

            $.ajax({
                url: "<?= base_url('Logistik_Pesanan_Pabrik/get_purchase_request_items') ?>",
                type: "GET",
                dataType: "json",
                data: {
                    id_purchase_request: idPurchaseRequest
                },
                success: function(response) {
                    renderPoItems(response || []);
                },
                error: function() {
                    renderPoItems([]);
                    Swal.fire('Gagal', 'Tidak bisa memuat item PR.', 'error');
                }
            });
        });

        $('#form-create-po').on('submit', function(e) {
            const checkedItems = $('#table_create_po_items tbody input[type="checkbox"]:checked').length;
            if (!checkedItems) {
                e.preventDefault();
                Swal.fire('Item belum dipilih', 'Pilih minimal satu item untuk dibuatkan PO.', 'warning');
                return;
            }

            let hasInvalidQty = false;
            $('#table_create_po_items tbody tr').each(function() {
                const checkbox = $(this).find('input[type="checkbox"]');
                if (!checkbox.is(':checked')) {
                    return;
                }

                const qtyInput = $(this).find('input[name^="qty_item["]');
                const qtyValue = parseFloat(qtyInput.val() || 0);
                const maxOutstanding = parseFloat(qtyInput.attr('max') || 0);

                if (qtyValue <= 0 || qtyValue > maxOutstanding) {
                    hasInvalidQty = true;
                }
            });

            if (hasInvalidQty) {
                e.preventDefault();
                Swal.fire('Qty PO tidak valid', 'Qty PO tiap item harus lebih dari 0 dan tidak boleh melebihi outstanding PR.', 'warning');
            }
        });
    });
</script>
