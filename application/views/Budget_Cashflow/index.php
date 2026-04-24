<?php
$status = $this->session->flashdata('status');
$importNotes = $this->session->flashdata('import_notes');
$validationErrors = $this->session->flashdata('validation_errors');
$validationWarnings = $this->session->flashdata('validation_warnings');
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">Cashflow TEC</h1>
                    <p class="text-muted mb-0">Kelola transaksi TEC dengan pencarian cepat, pagination, dan tampilan tabel yang lebih modern.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">Filter Cashflow</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Budget_Cashflow') ?>">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="budget-field-label">Tahun</label>
                                <input type="number" class="form-control budget-input" name="year" value="<?= (int) $selectedYear ?>" placeholder="Tahun">
                            </div>
                            <div class="col-md-3">
                                <label class="budget-field-label">Bulan</label>
                                <select class="form-control budget-input" name="month">
                                    <option value="0">Semua Bulan</option>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= (int) $selectedMonth === $m ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap justify-content-md-end budget-toolbar">
                                    <button type="submit" class="btn budget-btn budget-btn--primary">
                                        <i class="fas fa-search mr-1"></i> Tampilkan
                                    </button>
                                    <button type="button" class="btn budget-btn budget-btn--success" onclick="openEntryModal('manual')">
                                        <i class="fas fa-plus-circle mr-1"></i> Input / Import
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($validationErrors)): ?>
                <div class="alert alert-danger">
                    <strong>Validasi gagal:</strong>
                    <ul class="mb-0">
                        <?php foreach ($validationErrors as $message): ?>
                            <li><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($validationWarnings)): ?>
                <div class="alert alert-warning">
                    <strong>Peringatan budget:</strong>
                    <ul class="mb-0">
                        <?php foreach ($validationWarnings as $message): ?>
                            <li><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Cashflow TEC</h3>
                    <span class="badge badge-light"><?= count($headers) ?> header</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="cashflowTecTable" class="table table-bordered table-hover table-striped js-budget-table">
                            <thead class="bg-info">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor TEC</th>
                                    <th>Tanggal</th>
                                    <th>Bowheer</th>
                                    <th>Project</th>
                                    <th>PIC Project</th>
                                    <th>Regional</th>
                                    <th>Kota</th>
                                    <th>Item</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Kredit</th>
                                    <th style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($headers)): ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">Belum ada realisasi cashflow.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($headers as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nomor_tec']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_cashflow']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_bowheer'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['project_name']) ?></td>
                                            <td><?= htmlspecialchars($row['pic_project'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['regional'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['kota'] ?? '-') ?></td>
                                            <td class="text-right"><?= (int) $row['total_items'] ?></td>
                                            <td class="text-right"><?= number_format((float) $row['total_debit'], 0, ',', '.') ?></td>
                                            <td class="text-right"><?= number_format((float) $row['total_kredit'], 0, ',', '.') ?></td>
                                            <td>
                                                <div class="budget-action-inline">
                                                    <button type="button" class="btn btn-sm budget-btn budget-btn--table-primary js-edit-cashflow"
                                                        data-id-cashflow-header="<?= (int) $row['id_cashflow_header'] ?>">
                                                        <i class="fas fa-pen mr-1"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm budget-btn budget-btn--table-info"
                                                        onclick="showDetail(<?= (int) $row['id_cashflow_header'] ?>, '<?= htmlspecialchars($row['nomor_tec'], ENT_QUOTES) ?>')">
                                                        <i class="fas fa-list-ul mr-1"></i> Detail
                                                    </button>
                                                    <a href="<?= base_url('Budget_Cashflow/delete/' . (int) $row['id_cashflow_header']) ?>"
                                                        class="btn btn-sm budget-btn budget-btn--table-danger js-delete-cashflow"
                                                        data-item-name="<?= htmlspecialchars($row['nomor_tec'], ENT_QUOTES) ?>">
                                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-right">Total</th>
                                    <th class="text-right" id="summaryTotalItems">0</th>
                                    <th class="text-right" id="summaryTotalDebit">0</th>
                                    <th class="text-right" id="summaryTotalKredit">0</th>
                                    <th>Aksi</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content budget-modal">
            <div class="modal-header budget-modal__header">
                <div>
                    <span class="budget-modal__eyebrow">Budget Cashflow</span>
                    <h5 class="modal-title mb-1" id="entryModalTitle">Kelola Cashflow TEC</h5>
                    <p class="mb-0 budget-modal__subtitle" id="entryModalSubtitle">Pilih input manual atau import file dalam satu workflow yang lebih rapi.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills budget-nav-tabs mb-4" id="cashflowEntryTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="manual-tab" data-toggle="pill" href="#manual-pane" role="tab">Input Manual</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="import-tab" data-toggle="pill" href="#import-pane" role="tab">Import File</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="manual-pane" role="tabpanel">
                        <form method="post" action="<?= base_url('Budget_Cashflow/save') ?>" id="cashflowForm">
                            <input type="hidden" name="id_cashflow_header" id="cashflow_header_id">

                            <div class="budget-form-section">
                                <div class="budget-form-section__title">Informasi Header</div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="budget-field-label">Nomor TEC</label>
                                            <input type="text" class="form-control budget-input" name="nomor_tec" id="nomor_tec" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="budget-field-label">Tanggal Cashflow</label>
                                            <input type="date" class="form-control budget-input" name="tanggal_cashflow" id="tanggal_cashflow" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="budget-field-label">Bowheer</label>
                                            <select class="form-control budget-input" name="id_bowheer" id="id_bowheer">
                                                <option value="">Pilih Bowheer</option>
                                                <?php foreach ($bowheers as $bowheer): ?>
                                                    <option value="<?= (int) $bowheer['id_bowheer'] ?>">
                                                        <?= htmlspecialchars($bowheer['nama_bowheer']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="budget-field-label">Nama Project</label>
                                            <input type="text" class="form-control budget-input" name="project_name" id="project_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="budget-field-label">PIC Project</label>
                                            <input type="text" class="form-control budget-input" name="pic_project" id="pic_project">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="budget-field-label">Regional</label>
                                            <input type="text" class="form-control budget-input" name="regional" id="regional">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="budget-field-label">Kota</label>
                                            <input type="text" class="form-control budget-input" name="kota" id="kota">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="budget-field-label">Remarks Header</label>
                                    <textarea class="form-control budget-input" name="remarks" id="remarks" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="budget-form-section">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div class="budget-form-section__title mb-0">Detail Item TEC</div>
                                    <div class="d-flex flex-wrap align-items-center budget-toolbar">
                                        <div class="budget-monthly-summary">
                                            <span>Total Debit</span>
                                            <strong id="manualTotalDebit">0</strong>
                                        </div>
                                        <div class="budget-monthly-summary budget-monthly-summary--gap">
                                            <span>Total Kredit</span>
                                            <strong id="manualTotalKredit">0</strong>
                                        </div>
                                        <button type="button" class="btn budget-btn budget-btn--ghost" onclick="addDetailRow()">
                                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped" id="detailTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Debit / Kredit</th>
                                                <th>Qty</th>
                                                <th>Harga Satuan</th>
                                                <th>Nominal</th>
                                                <th>Remarks Item</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">Total</th>
                                                <th class="text-right" id="manualFooterNominal">0</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="budget-modal__footer px-0 pt-0">
                                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn budget-btn budget-btn--primary" id="cashflowSubmitBtn">
                                    <i class="fas fa-save mr-1"></i> Simpan Cashflow
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="import-pane" role="tabpanel">
                        <form method="post" action="<?= base_url('Budget_Cashflow/import') ?>" enctype="multipart/form-data" id="cashflowImportForm">
                            <div class="budget-form-section">
                                <div class="budget-form-section__title">Template Import</div>
                                <div class="mb-3">
                                    <a href="<?= base_url('Budget_Cashflow/downloadCashflowTemplateCsv') ?>" class="btn budget-btn budget-btn--ghost btn-sm mb-2">Template Cashflow CSV</a>
                                    <a href="<?= base_url('Budget_Cashflow/downloadCashflowTemplateXlsx') ?>" class="btn budget-btn budget-btn--ghost btn-sm mb-2">Template Cashflow XLSX</a>
                                    <a href="<?= base_url('Budget_Cashflow/downloadBudgetTemplateCsv') ?>" class="btn budget-btn budget-btn--ghost btn-sm mb-2">Template Budget CSV</a>
                                    <a href="<?= base_url('Budget_Cashflow/downloadBudgetTemplateXlsx') ?>" class="btn budget-btn budget-btn--ghost btn-sm mb-2">Template Budget XLSX</a>
                                </div>
                                <small class="text-muted d-block">
                                    A `nomor_tec`, B `tanggal_cashflow`, C `id_bowheer`, D `project_name`, E `pic_project`,
                                    F `regional`, G `kota`, H `item_code`, I `direction`, J `qty`,
                                    K `unit_price`, L `nominal`, M `remarks_header`, N `remarks_item`
                                </small>
                            </div>

                            <div class="budget-form-section">
                                <div class="budget-form-section__title">Upload File</div>
                                <input type="file" class="d-none" name="import_file" id="import_file" accept=".csv,.xls,.xlsx" required>
                                <div class="budget-dropzone" id="importDropzone">
                                    <div class="budget-dropzone__icon"><i class="fas fa-file-upload"></i></div>
                                    <h5 class="mb-2">Drag & Drop file di sini</h5>
                                    <p class="text-muted mb-2">Atau klik area ini untuk memilih file `.csv`, `.xls`, atau `.xlsx`</p>
                                    <div class="budget-dropzone__filename" id="importFileName">Belum ada file dipilih</div>
                                </div>
                            </div>

                            <div class="budget-form-section">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                    <div class="budget-form-section__title mb-0">Preview Import</div>
                                    <div class="budget-monthly-summary">
                                        <span>Preview Rows</span>
                                        <strong id="importPreviewCount">0</strong>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped" id="importPreviewTable">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nomor TEC</th>
                                                <th>Tanggal</th>
                                                <th>Project</th>
                                                <th>Item Code</th>
                                                <th>Direction</th>
                                                <th>Qty</th>
                                                <th>Unit Price</th>
                                                <th>Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="importPreviewBody">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">Preview file akan muncul di sini.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted d-block mt-2" id="importPreviewHint">Preview browser mendukung CSV dan XLSX. Untuk XLS lama (`.xls`), file tetap bisa diupload, tetapi preview mungkin tidak tersedia.</small>
                            </div>

                            <div class="budget-modal__footer px-0 pt-0">
                                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn budget-btn budget-btn--primary" id="importSubmitBtn">
                                    <i class="fas fa-file-import mr-1"></i> Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content budget-modal">
            <div class="modal-header budget-modal__header">
                <div>
                    <span class="budget-modal__eyebrow">Cashflow Detail</span>
                    <h5 class="modal-title mb-1" id="detailTitle">Detail TEC</h5>
                    <p class="mb-0 budget-modal__subtitle">Lihat rincian item TEC dengan ringkasan nominal yang lebih jelas dan mudah dipindai.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="budget-form-section">
                    <div class="budget-form-section__title">Informasi TEC</div>
                    <div class="detail-summary-grid mb-4">
                        <div class="detail-summary-card">
                            <span class="detail-summary-card__label">Nomor TEC</span>
                            <strong class="detail-summary-card__value" id="detailInfoNomorTec">-</strong>
                        </div>
                        <div class="detail-summary-card">
                            <span class="detail-summary-card__label">Nama Project</span>
                            <strong class="detail-summary-card__value" id="detailInfoProject">-</strong>
                        </div>
                        <div class="detail-summary-card">
                            <span class="detail-summary-card__label">Bowheer</span>
                            <strong class="detail-summary-card__value" id="detailInfoBowheer">-</strong>
                        </div>
                        <div class="detail-summary-card">
                            <span class="detail-summary-card__label">Lokasi Project</span>
                            <strong class="detail-summary-card__value" id="detailInfoLokasi">-</strong>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div class="budget-form-section__title mb-0">Breakdown Rincian Item</div>
                        <div class="d-flex flex-wrap budget-toolbar">
                            <div class="budget-monthly-summary">
                                <span>Total Item</span>
                                <strong id="detailSummaryItems">0</strong>
                            </div>
                            <div class="budget-monthly-summary budget-monthly-summary--gap">
                                <span>Total Nominal</span>
                                <strong id="detailSummaryNominal">0</strong>
                            </div>
                        </div>
                    </div>
                <div class="table-responsive detail-table-shell">
                    <table id="cashflowDetailTable" class="table table-bordered table-sm table-striped js-budget-table-modal">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th>Debit / Kredit</th>
                                <th>Qty</th>
                                <th>Harga Satuan</th>
                                <th>Nominal</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total</th>
                                <th id="detailFooterNominal" class="text-right">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    const itemOptions = <?= json_encode(array_map(static function ($item) {
        return [
            'id' => (int) $item['id_budget_item'],
            'label' => $item['item_code'] . ' - ' . $item['item_name'],
            'direction' => $item['default_direction'],
        ];
    }, $items)) ?>;

    function formatBudgetNumber(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value || 0);
    }

    function parseBudgetNumber(value) {
        if (typeof value === 'number') {
            return value;
        }

        value = String(value || '')
            .replace(/\./g, '')
            .replace(/,/g, '.')
            .replace(/[^\d.-]/g, '');

        const parsed = parseFloat(value);
        return isNaN(parsed) ? 0 : parsed;
    }

    function buildItemOptions() {
        return itemOptions.map(function (item) {
            return '<option value="' + item.id + '" data-direction="' + item.direction + '">' + item.label + '</option>';
        }).join('');
    }

    function updateManualSummary() {
        let totalNominal = 0;
        let totalDebit = 0;
        let totalKredit = 0;

        $('#detailTable tbody tr').each(function () {
            const nominal = parseBudgetNumber($(this).find('.nominal-input').val());
            const direction = ($(this).find('.direction-select').val() || 'DEBIT').toUpperCase();

            totalNominal += nominal;
            if (direction === 'DEBIT') {
                totalDebit += nominal;
            } else {
                totalKredit += nominal;
            }
        });

        $('#manualFooterNominal').text(formatBudgetNumber(totalNominal));
        $('#manualTotalDebit').text(formatBudgetNumber(totalDebit));
        $('#manualTotalKredit').text(formatBudgetNumber(totalKredit));
    }

    function addDetailRow() {
        const tbody = document.querySelector('#detailTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = '' +
            '<td><select class="form-control form-control-sm budget-input item-select" name="detail_item_id[]" required>' +
                '<option value="">Pilih Item</option>' + buildItemOptions() +
            '</select></td>' +
            '<td><select class="form-control form-control-sm budget-input direction-select" name="detail_direction[]">' +
                '<option value="DEBIT">DEBIT</option>' +
                '<option value="KREDIT">KREDIT</option>' +
            '</select></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm budget-input qty-input" name="detail_qty[]" value="1"></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm budget-input unit-price-input" name="detail_unit_price[]" value="0"></td>' +
            '<td><input type="number" step="0.01" class="form-control form-control-sm budget-input nominal-input" name="detail_nominal[]" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm budget-input" name="detail_remarks[]"></td>' +
            '<td><button type="button" class="btn btn-sm budget-btn budget-btn--table-danger js-remove-detail-row">Hapus</button></td>';
        tbody.appendChild(tr);
        updateManualSummary();
    }

    function addDetailRowWithData(detail) {
        addDetailRow();
        const tr = $('#detailTable tbody tr:last');
        tr.find('.item-select').val(detail.id_budget_item);
        tr.find('.direction-select').val(detail.direction || 'DEBIT');
        tr.find('.qty-input').val(detail.qty || 1);
        tr.find('.unit-price-input').val(detail.unit_price || 0);
        tr.find('.nominal-input').val(detail.nominal || 0);
        tr.find('input[name="detail_remarks[]"]').val(detail.remarks_item || '');
        updateManualSummary();
    }

    function resetCashflowForm() {
        $('#entryModalTitle').text('Kelola Cashflow TEC');
        $('#entryModalSubtitle').text('Pilih input manual atau import file dalam satu workflow yang lebih rapi.');
        $('#cashflowForm')[0].dataset.confirmed = 'false';
        $('#cashflow_header_id').val('');
        $('#nomor_tec').val('');
        $('#tanggal_cashflow').val('<?= date('Y-m-d') ?>');
        $('#id_bowheer').val('');
        $('#project_name').val('');
        $('#pic_project').val('');
        $('#regional').val('');
        $('#kota').val('');
        $('#remarks').val('');
        $('#detailTable tbody').html('');
        $('#cashflowSubmitBtn').html('<i class="fas fa-save mr-1"></i> Simpan Cashflow').prop('disabled', false);
        addDetailRow();
        updateManualSummary();
    }

    function openEntryModal(tabName) {
        resetCashflowForm();
        if ($('#cashflowImportForm').length) {
            $('#cashflowImportForm')[0].dataset.confirmed = 'false';
        }
        $('#entryModal').modal('show');
        if (tabName === 'import') {
            $('#import-tab').tab('show');
        } else {
            $('#manual-tab').tab('show');
        }
    }

    function editCashflow(headerId) {
        openEntryModal('manual');
        $('#entryModalTitle').text('Edit Cashflow TEC');
        $('#entryModalSubtitle').text('Memuat detail cashflow untuk diedit...');
        $('#cashflowSubmitBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...').prop('disabled', true);

        $.ajax({
            url: '<?= base_url('Budget_Cashflow/editData/') ?>' + headerId,
            type: 'GET',
            dataType: 'text'
        }).done(function (responseText) {
            let response = null;

            try {
                response = JSON.parse($.trim(responseText));
            } catch (error) {
                $('#entryModal').modal('hide');
                Swal.fire('Gagal', 'Detail cashflow gagal dimuat. Format respons tidak valid.', 'error');
                return;
            }

            if (!response || !response.status) {
                $('#entryModal').modal('hide');
                Swal.fire('Gagal', (response && response.message) || 'Data tidak ditemukan.', 'error');
                return;
            }

            $('#entryModalTitle').text('Edit Cashflow TEC');
            $('#entryModalSubtitle').text('Perbarui data header dan detail item cashflow, lalu simpan perubahan jika semuanya sudah sesuai.');
            $('#cashflow_header_id').val(response.header.id_cashflow_header);
            $('#nomor_tec').val(response.header.nomor_tec || '');
            $('#tanggal_cashflow').val(response.header.tanggal_cashflow || '');
            $('#id_bowheer').val(response.header.id_bowheer || '');
            $('#project_name').val(response.header.project_name || '');
            $('#pic_project').val(response.header.pic_project || '');
            $('#regional').val(response.header.regional || '');
            $('#kota').val(response.header.kota || '');
            $('#remarks').val(response.header.remarks || '');
            $('#detailTable tbody').html('');

            if (response.details && response.details.length) {
                response.details.forEach(function (detail) {
                    addDetailRowWithData(detail);
                });
            } else {
                addDetailRow();
            }

            $('#cashflowSubmitBtn').html('<i class="fas fa-save mr-1"></i> Simpan Perubahan').prop('disabled', false);
            updateManualSummary();
        }).fail(function (xhr) {
            $('#entryModal').modal('hide');
            Swal.fire('Gagal', 'Detail cashflow gagal dimuat. ' + (xhr.status ? 'Kode ' + xhr.status + '.' : 'Silakan coba lagi.'), 'error');
        });
    }

    function showDetail(headerId, nomorTec) {
        $('#detailTitle').text('Detail TEC - ' + nomorTec);
        $('#detailBody').html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
        $('#detailFooterNominal').text('0');
        $('#detailSummaryItems').text('0');
        $('#detailSummaryNominal').text('0');
        $('#detailInfoNomorTec').text(nomorTec || '-');
        $('#detailInfoProject').text('-');
        $('#detailInfoBowheer').text('-');
        $('#detailInfoLokasi').text('-');
        $('#detailModal').modal('show');

        $.ajax({
            url: '<?= base_url('Budget_Cashflow/detail/') ?>' + headerId,
            type: 'GET',
            dataType: 'text'
        }).done(function (responseText) {
            let payload = {};
            let rows = [];
            try {
                payload = JSON.parse($.trim(responseText));
                rows = payload.details || [];
            } catch (error) {
                payload = {};
                rows = [];
            }

            let html = '';
            let totalNominal = 0;
            let totalItems = 0;

            if (payload.header) {
                const lokasi = [payload.header.kota, payload.header.regional].filter(Boolean).join(' / ');
                $('#detailInfoNomorTec').text(payload.header.nomor_tec || nomorTec || '-');
                $('#detailInfoProject').text(payload.header.project_name || '-');
                $('#detailInfoBowheer').text(payload.header.nama_bowheer || '-');
                $('#detailInfoLokasi').text(lokasi || '-');
            }

            if (!rows.length) {
                html = '<tr><td colspan="8" class="text-center text-muted">Belum ada detail item.</td></tr>';
            } else {
                rows.forEach(function (row, index) {
                    totalItems++;
                    totalNominal += Number(row.nominal) || 0;
                    html += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + row.item_code + '</td>' +
                        '<td>' + row.item_name + '</td>' +
                        '<td><span class="budget-direction-badge budget-direction-badge--' + String(row.direction || '').toLowerCase() + '">' + row.direction + '</span></td>' +
                        '<td class="text-right">' + Number(row.qty).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(row.unit_price).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right font-weight-bold">' + Number(row.nominal).toLocaleString('id-ID') + '</td>' +
                        '<td>' + (row.remarks_item || '-') + '</td>' +
                    '</tr>';
                });
            }

            if ($.fn.DataTable.isDataTable('#cashflowDetailTable')) {
                $('#cashflowDetailTable').DataTable().clear().destroy();
            }

            $('#detailBody').html(html);
            $('#detailFooterNominal').text(formatBudgetNumber(totalNominal));
            $('#detailSummaryItems').text(formatBudgetNumber(totalItems));
            $('#detailSummaryNominal').text(formatBudgetNumber(totalNominal));
        });
    }

    function renderImportPreview(rows) {
        const previewRows = rows.slice(0, 20);
        let html = '';

        if (!previewRows.length) {
            html = '<tr><td colspan="9" class="text-center text-muted">Tidak ada data untuk dipreview.</td></tr>';
        } else {
            previewRows.forEach(function (row, index) {
                html += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + (row.A || '') + '</td>' +
                    '<td>' + (row.B || '') + '</td>' +
                    '<td>' + (row.D || '') + '</td>' +
                    '<td>' + (row.H || '') + '</td>' +
                    '<td>' + (row.I || '') + '</td>' +
                    '<td class="text-right">' + (row.J || '') + '</td>' +
                    '<td class="text-right">' + (row.K || '') + '</td>' +
                    '<td class="text-right">' + (row.L || '') + '</td>' +
                '</tr>';
            });
        }

        $('#importPreviewBody').html(html);
        $('#importPreviewCount').text(rows.length);
    }

    function handleImportFile(file) {
        if (!file) {
            return;
        }

        $('#import_file')[0].files = createFileList(file);
        $('#importFileName').text(file.name);
        $('#importPreviewHint').text('Membaca preview file...');

        const extension = (file.name.split('.').pop() || '').toLowerCase();
        const reader = new FileReader();

        if (extension === 'csv') {
            reader.onload = function (e) {
                const text = e.target.result || '';
                const rows = text.split(/\r?\n/).filter(Boolean).slice(1).map(function (line) {
                    const cols = line.split(',');
                    return {
                        A: cols[0] || '',
                        B: cols[1] || '',
                        D: cols[3] || '',
                        H: cols[7] || '',
                        I: cols[8] || '',
                        J: cols[9] || '',
                        K: cols[10] || '',
                        L: cols[11] || ''
                    };
                });
                renderImportPreview(rows);
                $('#importPreviewHint').text('Preview diambil dari 20 baris pertama file.');
            };
            reader.readAsText(file);
            return;
        }

        if (extension === 'xlsx' && typeof XLSX !== 'undefined') {
            reader.onload = function (e) {
                const workbook = XLSX.read(e.target.result, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(firstSheet, {
                    header: 'A',
                    raw: false
                }).slice(1);
                renderImportPreview(rows);
                $('#importPreviewHint').text('Preview diambil dari sheet pertama, maksimal 20 baris.');
            };
            reader.readAsArrayBuffer(file);
            return;
        }

        $('#importPreviewBody').html('<tr><td colspan="9" class="text-center text-muted">Preview tidak tersedia untuk file ini, tetapi file tetap bisa diimport.</td></tr>');
        $('#importPreviewCount').text('0');
        $('#importPreviewHint').text('Preview browser saat ini mendukung CSV dan XLSX. File tetap akan dikirim ke server saat import.');
    }

    function createFileList(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    }

    $(document).on('change', '.item-select', function () {
        const selected = $(this).find(':selected');
        const direction = selected.data('direction');
        if (direction) {
            $(this).closest('tr').find('.direction-select').val(direction);
        }
        updateManualSummary();
    });

    $(document).on('input', '.qty-input, .unit-price-input', function () {
        const tr = $(this).closest('tr');
        const qty = parseFloat(tr.find('.qty-input').val()) || 0;
        const unitPrice = parseFloat(tr.find('.unit-price-input').val()) || 0;
        tr.find('.nominal-input').val((qty * unitPrice).toFixed(2));
        updateManualSummary();
    });

    $(document).on('input change', '.nominal-input, .direction-select', function () {
        updateManualSummary();
    });

    $(document).on('click', '.js-remove-detail-row', function () {
        $(this).closest('tr').remove();
        if (!$('#detailTable tbody tr').length) {
            addDetailRow();
        }
        updateManualSummary();
    });

    $(function () {
        const sumColumn = function (api, index) {
            return api.column(index, { search: 'applied' }).data().reduce(function (a, b) {
                return parseBudgetNumber(a) + parseBudgetNumber(b);
            }, 0);
        };

        $('#importDropzone').on('click', function () {
            $('#import_file').trigger('click');
        });

        $('#import_file').on('change', function () {
            handleImportFile(this.files[0]);
        });

        $('#importDropzone').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('is-dragover');
        });

        $('#importDropzone').on('dragleave dragend drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is-dragover');
        });

        $('#importDropzone').on('drop', function (e) {
            const files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                handleImportFile(files[0]);
            }
        });

        $(document).on('click', '.js-edit-cashflow', function (e) {
            e.preventDefault();
            editCashflow($(this).attr('data-id-cashflow-header'));
        });

        $(document).on('click', '.js-delete-cashflow', function (e) {
            e.preventDefault();

            const href = $(this).attr('href');
            const itemName = $(this).data('item-name') || 'transaksi ini';

            Swal.fire({
                title: 'Hapus cashflow?',
                text: 'Transaksi TEC "' + itemName + '" akan dihapus.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#9aa9b8',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.value) {
                    window.location.href = href;
                }
            });
        });

        $('#cashflowForm').on('submit', function (e) {
            const form = this;
            if (form.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();
            const isEdit = $.trim($('#cashflow_header_id').val()) !== '';

            Swal.fire({
                title: isEdit ? 'Simpan perubahan cashflow?' : 'Simpan cashflow baru?',
                text: isEdit
                    ? 'Perubahan akan langsung memperbarui header dan detail cashflow.'
                    : 'Data cashflow baru akan ditambahkan ke daftar transaksi.',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1f6da1',
                cancelButtonColor: '#9aa9b8',
                confirmButtonText: isEdit ? 'Ya, simpan perubahan' : 'Ya, simpan cashflow',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.value) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        $('#cashflowImportForm').on('submit', function (e) {
            const form = this;
            if (form.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();
            Swal.fire({
                title: 'Import file cashflow?',
                text: 'File yang dipilih akan diproses dan menambah data cashflow ke sistem.',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1f6da1',
                cancelButtonColor: '#9aa9b8',
                confirmButtonText: 'Ya, import file',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.value) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        if ($.fn.DataTable) {
            $('#cashflowTecTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 10,
                footerCallback: function () {
                    const api = this.api();
                    $('#summaryTotalItems').text(formatBudgetNumber(sumColumn(api, 8)));
                    $('#summaryTotalDebit').text(formatBudgetNumber(sumColumn(api, 9)));
                    $('#summaryTotalKredit').text(formatBudgetNumber(sumColumn(api, 10)));
                },
                language: {
                    search: 'Search:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    },
                    zeroRecords: 'Tidak ada data yang cocok'
                }
            });

        }

        resetCashflowForm();

        <?php if (!empty($status)): ?>
        const flashStatus = '<?= htmlspecialchars((string) $status, ENT_QUOTES) ?>';
        const flashStorageKey = 'budget_cashflow_flash_' + flashStatus + '_<?= (int) $selectedYear ?>_<?= (int) $selectedMonth ?>';
        if (!window.__budgetCashflowAlertShown && sessionStorage.getItem(flashStorageKey) !== 'shown') {
            window.__budgetCashflowAlertShown = true;
            sessionStorage.setItem(flashStorageKey, 'shown');
            <?php if ($status === 'sukses_simpan'): ?>
            Swal.fire('Success', 'Cashflow berhasil disimpan.', 'success');
            <?php elseif ($status === 'sukses_edit'): ?>
            Swal.fire('Success', 'Cashflow berhasil diperbarui.', 'success');
            <?php elseif ($status === 'sukses_hapus'): ?>
            Swal.fire('Success', 'Cashflow berhasil dihapus.', 'success');
            <?php elseif ($status === 'sukses_import'): ?>
            Swal.fire('Success', 'Import berhasil diproses.', 'success');
            <?php elseif ($status === 'warning_import'): ?>
            Swal.fire('Warning', 'Import selesai dengan beberapa catatan. <?= htmlspecialchars((string) $importNotes, ENT_QUOTES) ?>', 'warning');
            <?php elseif ($status === 'gagal_import' || $status === 'gagal_simpan' || $status === 'gagal_hapus'): ?>
            Swal.fire('Gagal', 'Proses cashflow gagal dilakukan. <?= htmlspecialchars((string) $importNotes, ENT_QUOTES) ?>', 'error');
            <?php endif; ?>
        }
        <?php endif; ?>

        <?php if (!empty($validationWarnings) && ($status === 'sukses_simpan' || $status === 'sukses_edit')): ?>
        const warningStorageKey = 'budget_cashflow_warning_<?= md5(json_encode($validationWarnings)) ?>';
        if (sessionStorage.getItem(warningStorageKey) !== 'shown') {
            sessionStorage.setItem(warningStorageKey, 'shown');
            Swal.fire('Warning', 'Data tersimpan, tetapi ada item yang melewati budget.', 'warning');
        }
        <?php endif; ?>
    });
</script>

<style>
    .budget-toolbar {
        gap: 10px;
    }

    .budget-field-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #48657f;
        text-transform: uppercase;
    }

    .budget-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .budget-card__header {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
    }

    .budget-card .card-title {
        font-weight: 700;
    }

    .budget-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .budget-btn:hover,
    .budget-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .budget-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .budget-btn--table-primary,
    .budget-btn--table-danger,
    .budget-btn--table-info {
        padding: 0.52rem 0.9rem;
        box-shadow: none;
    }

    .budget-btn--table-primary {
        background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%);
        color: #1d5f8d;
        border: 1px solid #c9e1f3;
    }

    .budget-btn--table-info {
        background: linear-gradient(135deg, #eef7f4 0%, #d7efe7 100%);
        color: #16685c;
        border: 1px solid #bfe0d6;
    }

    .budget-btn--table-danger {
        background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%);
        color: #b93d38;
        border: 1px solid #f5c8c5;
    }

    .budget-btn--table-danger:hover,
    .budget-btn--table-danger:focus {
        color: #fff;
        background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%);
        border-color: #b93d38;
    }

    .budget-action-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .budget-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .budget-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .budget-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .budget-modal__subtitle {
        max-width: 85%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .budget-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .budget-modal__footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-top: 0;
        margin-top: 1rem;
        background: transparent;
    }

    .budget-form-section {
        margin-bottom: 1rem;
        padding: 1rem 1rem 0.9rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .budget-form-section__title {
        margin-bottom: 0.9rem;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .budget-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .budget-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .budget-monthly-summary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0.65rem 0.95rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff7fc 0%, #dfeef8 100%);
        color: #27587c;
        font-weight: 700;
    }

    .budget-monthly-summary--gap {
        background: linear-gradient(135deg, #edf8ef 0%, #d8efdc 100%);
        color: #1d6b3e;
    }

    .detail-table-shell {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #d8e7f2;
        background: #fff;
    }

    .detail-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .detail-summary-card {
        padding: 1rem 1.05rem;
        border-radius: 16px;
        border: 1px solid #d9e8f3;
        background: linear-gradient(180deg, #ffffff 0%, #f4f9fd 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .detail-summary-card__label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #68839a;
    }

    .detail-summary-card__value {
        display: block;
        font-size: 1rem;
        line-height: 1.35;
        color: #163f5d;
        word-break: break-word;
    }

    #cashflowDetailTable thead th {
        background: linear-gradient(180deg, #eef6fb 0%, #dcecf8 100%);
        color: #2e607f;
        border-bottom: 0;
        vertical-align: middle;
    }

    #cashflowDetailTable tbody tr:hover {
        background: rgba(83, 169, 216, 0.08);
    }

    #cashflowDetailTable {
        table-layout: auto;
    }

    #cashflowDetailTable th,
    #cashflowDetailTable td {
        vertical-align: middle;
    }

    .budget-direction-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 86px;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.05em;
    }

    .budget-direction-badge--debit {
        background: linear-gradient(135deg, #e7f8ef 0%, #d2f0df 100%);
        color: #1e7a49;
    }

    .budget-direction-badge--kredit {
        background: linear-gradient(135deg, #fff0ef 0%, #ffd9d6 100%);
        color: #b3413b;
    }

    .budget-nav-tabs {
        gap: 10px;
    }

    .budget-nav-tabs .nav-link {
        border: 0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-weight: 700;
        color: #315d7f;
        background: #e9f3fa;
    }

    .budget-nav-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.18);
    }

    .budget-dropzone {
        border: 2px dashed #9dc4df;
        border-radius: 18px;
        padding: 2rem 1.5rem;
        text-align: center;
        background: linear-gradient(180deg, #fafdff 0%, #eff7fc 100%);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .budget-dropzone.is-dragover {
        border-color: #1f6da1;
        background: linear-gradient(180deg, #eef7fd 0%, #dcecf8 100%);
        transform: translateY(-1px);
    }

    .budget-dropzone__icon {
        font-size: 2rem;
        color: #2d6287;
        margin-bottom: 0.75rem;
    }

    .budget-dropzone__filename {
        font-weight: 700;
        color: #1d5f8d;
    }

    .js-budget-table thead th,
    .js-budget-table tfoot th,
    .js-budget-table-modal thead th,
    .js-budget-table-modal tfoot th,
    #detailTable tfoot th,
    #detailTable thead th {
        white-space: nowrap;
    }

    .js-budget-table tfoot th,
    .js-budget-table-modal tfoot th,
    #detailTable tfoot th {
        background: #eef5fb;
        color: #315d7f;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border-radius: 10px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
    }

    @media (max-width: 767.98px) {
        .budget-toolbar {
            margin-top: 1rem;
            justify-content: flex-start !important;
        }

        .budget-btn {
            width: 100%;
        }

        .budget-action-inline {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }

        .budget-modal__subtitle {
            max-width: 100%;
        }

        .budget-modal__footer {
            flex-direction: column;
        }

        .detail-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
