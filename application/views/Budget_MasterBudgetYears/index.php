<?php
$status = $this->session->flashdata('status');
$monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">Master Budget Tahunan & Bulanan</h1>
                    <p class="text-muted mb-0">Pantau struktur budget tahunan dan breakdown bulanan dengan tampilan tabel yang lebih nyaman dibaca.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">Filter Tahun Budget</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Budget_MasterBudgetYears') ?>">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="budget-field-label">Tahun Budget</label>
                                <select class="form-control budget-input" name="year">
                                    <?php foreach ($yearOptions as $year): ?>
                                        <option value="<?= (int) $year ?>" <?= (int) $year === (int) $selectedYear ? 'selected' : '' ?>>
                                            <?= (int) $year ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex flex-wrap justify-content-md-end budget-toolbar">
                                    <button type="submit" class="btn budget-btn budget-btn--primary">
                                        <i class="fas fa-search mr-1"></i> Tampilkan
                                    </button>
                                    <button type="button" class="btn budget-btn budget-btn--success" onclick="openBudgetModal()">
                                        <i class="fas fa-plus-circle mr-1"></i> Tambah Budget
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Budget</h3>
                    <span class="badge badge-light"><?= count($budgets) ?> item</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="masterBudgetTable" class="table table-bordered table-hover table-striped js-budget-table">
                            <thead class="bg-info">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Item</th>
                                    <th>Nama Item</th>
                                    <th>Kategori</th>
                                    <th>Budget Tahunan</th>
                                    <th>Total Budget Bulanan</th>
                                    <th>Selisih</th>
                                    <th style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($budgets)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada budget untuk tahun <?= (int) $selectedYear ?>.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($budgets as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['item_code']) ?></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= htmlspecialchars($row['item_category'] ?? '-') ?></td>
                                            <td class="text-right"><?= number_format((float) $row['annual_budget'], 0, ',', '.') ?></td>
                                            <td class="text-right"><?= number_format((float) $row['total_monthly'], 0, ',', '.') ?></td>
                                            <td class="text-right <?= ((float) $row['total_monthly'] - (float) $row['annual_budget']) !== 0.0 ? 'text-danger font-weight-bold' : '' ?>">
                                                <?= number_format((float) $row['annual_budget'] - (float) $row['total_monthly'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <div class="budget-action-inline">
                                                    <button type="button" class="btn btn-sm budget-btn budget-btn--table-primary js-edit-budget"
                                                        data-id-budget-annual="<?= (int) $row['id_budget_annual'] ?>">
                                                        <i class="fas fa-pen mr-1"></i> Edit
                                                    </button>
                                                    <a href="<?= base_url('Budget_MasterBudgetYears/delete/' . (int) $row['id_budget_annual'] . '?year=' . (int) $selectedYear) ?>"
                                                        class="btn btn-sm budget-btn budget-btn--table-danger js-delete-budget"
                                                        data-item-name="<?= htmlspecialchars($row['item_name'], ENT_QUOTES) ?>">
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
                                    <th colspan="4" class="text-right">Total</th>
                                    <th class="text-right" id="summaryAnnualBudget">0</th>
                                    <th class="text-right" id="summaryMonthlyBudget">0</th>
                                    <th class="text-right" id="summaryBudgetGap">0</th>
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

<div class="modal fade" id="budgetModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content budget-modal">
            <form method="post" action="<?= base_url('Budget_MasterBudgetYears/save') ?>" id="budgetYearForm">
                <div class="modal-header budget-modal__header">
                    <div>
                        <span class="budget-modal__eyebrow">Budget MasterBudgetYears</span>
                        <h5 class="modal-title mb-1" id="budgetModalTitle">Tambah Budget</h5>
                        <p class="mb-0 budget-modal__subtitle" id="budgetModalSubtitle">Lengkapi budget tahunan dan breakdown bulanan untuk menjaga planning tetap presisi.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_budget_annual" id="id_budget_annual">

                    <div class="budget-form-section">
                        <div class="budget-form-section__title">Informasi Utama</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="budget-field-label">Tahun Budget</label>
                                    <input type="number" class="form-control budget-input" name="budget_year" id="budget_year"
                                        value="<?= (int) $selectedYear ?>" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="budget-field-label">Item Budget</label>
                                    <select class="form-control budget-input" name="id_budget_item" id="id_budget_item" required>
                                        <option value="">Pilih Item</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?= (int) $item['id_budget_item'] ?>">
                                                <?= htmlspecialchars($item['item_code'] . ' - ' . $item['item_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="budget-form-section">
                        <div class="budget-form-section__title">Nilai Budget</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="budget-field-label">Budget Tahunan</label>
                                    <input type="number" step="0.01" class="form-control budget-input" name="annual_budget" id="annual_budget" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="budget-field-label">Catatan</label>
                                    <input type="text" class="form-control budget-input" name="notes" id="notes">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="budget-form-section">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div class="budget-form-section__title mb-0">Breakdown Bulanan</div>
                            <div class="budget-summary-stack">
                                <div class="budget-monthly-summary">
                                    <span>Total Bulanan</span>
                                    <strong id="modalMonthlyTotal">0</strong>
                                </div>
                                <div class="budget-monthly-summary budget-monthly-summary--gap" id="modalBudgetGapCard">
                                    <span>Selisih vs Tahunan</span>
                                    <strong id="modalBudgetGap">0</strong>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="monthlyBreakdownTable" class="table table-bordered table-sm table-striped js-budget-table-modal">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Budget Bulanan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthNames as $monthNo => $monthLabel): ?>
                                        <tr>
                                            <td><?= $monthLabel ?></td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control budget-input js-monthly-budget-input"
                                                    name="monthly_budget[<?= (int) $monthNo ?>]"
                                                    id="monthly_budget_<?= (int) $monthNo ?>" value="0">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-right">Total Bulanan</th>
                                        <th class="text-right" id="monthlyBreakdownFooterTotal">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted">Total budget bulanan boleh kurang atau lebih dari budget tahunan, sesuai kebutuhan planning.</small>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary" id="budgetSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const emptyMonthly = {
        1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0,
        7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0
    };

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

        var parsed = parseFloat(value);
        return isNaN(parsed) ? 0 : parsed;
    }

    function updateMonthlyTotalSummary() {
        var total = 0;
        var annualBudget = parseBudgetNumber($('#annual_budget').val());

        $('.js-monthly-budget-input').each(function () {
            total += parseBudgetNumber($(this).val());
        });

        var gap = annualBudget - total;

        $('#modalMonthlyTotal').text(formatBudgetNumber(total));
        $('#monthlyBreakdownFooterTotal').text(formatBudgetNumber(total));
        $('#modalBudgetGap').text(formatBudgetNumber(gap));
        $('#modalBudgetGapCard')
            .toggleClass('is-negative', gap < 0)
            .toggleClass('is-positive', gap > 0)
            .toggleClass('is-balanced', gap === 0);
    }

    function openBudgetModal() {
        document.getElementById('budgetModalTitle').textContent = 'Tambah Budget';
        document.getElementById('budgetModalSubtitle').textContent = 'Lengkapi budget tahunan dan breakdown bulanan untuk menjaga planning tetap presisi.';
        document.getElementById('id_budget_annual').value = '';
        document.getElementById('budget_year').value = '<?= (int) $selectedYear ?>';
        document.getElementById('id_budget_item').value = '';
        document.getElementById('annual_budget').value = '';
        document.getElementById('notes').value = '';
        document.getElementById('budgetSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Simpan';
        $('#id_budget_item').prop('disabled', false);
        $('#budgetSubmitBtn').prop('disabled', false);

        Object.keys(emptyMonthly).forEach(function (month) {
            document.getElementById('monthly_budget_' + month).value = 0;
        });

        updateMonthlyTotalSummary();
        $('#budgetModal').modal('show');
    }

    function editBudget(id) {
        openBudgetModal();

        document.getElementById('budgetModalTitle').textContent = 'Edit Budget';
        document.getElementById('budgetModalSubtitle').textContent = 'Memuat detail budget untuk diedit...';
        document.getElementById('budgetSubmitBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...';
        $('#budgetSubmitBtn').prop('disabled', true);
        $('#id_budget_item').prop('disabled', true);

        $.ajax({
            url: '<?= base_url('Budget_MasterBudgetYears/getMonthlyDetail') ?>',
            type: 'POST',
            dataType: 'text',
            data: {
                id_budget_annual: id
            }
        }).done(function (responseText) {
            var response = null;

            try {
                response = JSON.parse($.trim(responseText));
            } catch (error) {
                $('#budgetModal').modal('hide');
                Swal.fire('Gagal', 'Detail budget gagal dimuat. Format respons tidak valid.', 'error');
                return;
            }

            if (!response || !response.status) {
                $('#budgetModal').modal('hide');
                Swal.fire('Gagal', (response && response.message) || 'Budget tidak ditemukan.', 'error');
                return;
            }

            document.getElementById('budgetModalTitle').textContent = 'Edit Budget';
            document.getElementById('budgetModalSubtitle').textContent = 'Perbarui budget tahunan dan detail bulanan, lalu simpan perubahan jika semua angka sudah sesuai.';
            document.getElementById('id_budget_annual').value = response.budget.id_budget_annual;
            document.getElementById('budget_year').value = response.budget.budget_year;
            document.getElementById('id_budget_item').value = response.budget.id_budget_item;
            document.getElementById('annual_budget').value = response.budget.annual_budget;
            document.getElementById('notes').value = response.budget.notes || '';
            document.getElementById('budgetSubmitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Perubahan';
            $('#budgetSubmitBtn').prop('disabled', false);
            $('#id_budget_item').prop('disabled', false);

            Object.keys(emptyMonthly).forEach(function (month) {
                document.getElementById('monthly_budget_' + month).value = response.monthly[month] || 0;
            });

            updateMonthlyTotalSummary();
        }).fail(function (xhr) {
            $('#budgetModal').modal('hide');
            Swal.fire('Gagal', 'Detail budget gagal dimuat. ' + (xhr.status ? 'Kode ' + xhr.status + '.' : 'Silakan coba lagi.'), 'error');
        });
    }

    $(function () {
        $(document).on('click', '.js-edit-budget', function (e) {
            e.preventDefault();
            editBudget($(this).attr('data-id-budget-annual'));
        });

        $(document).on('click', '.js-delete-budget', function (e) {
            e.preventDefault();

            var href = $(this).attr('href');
            var itemName = $(this).data('item-name') || 'budget ini';

            Swal.fire({
                title: 'Hapus budget?',
                text: 'Data budget untuk "' + itemName + '" akan dihapus.',
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

        $(document).on('input', '.js-monthly-budget-input', function () {
            updateMonthlyTotalSummary();
        });

        $(document).on('input', '#annual_budget', function () {
            updateMonthlyTotalSummary();
        });

        $('#budgetYearForm').on('submit', function (e) {
            var form = this;
            if (form.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();

            var isEdit = $.trim($('#id_budget_annual').val()) !== '';
            Swal.fire({
                title: isEdit ? 'Simpan perubahan budget?' : 'Simpan budget baru?',
                text: isEdit
                    ? 'Perubahan akan langsung memperbarui budget tahunan dan breakdown bulanannya.'
                    : 'Budget baru akan ditambahkan ke daftar budget tahunan.',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1f6da1',
                cancelButtonColor: '#9aa9b8',
                confirmButtonText: isEdit ? 'Ya, simpan perubahan' : 'Ya, simpan budget',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.value) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        if ($.fn.DataTable) {
            $('#masterBudgetTable').DataTable({
                paging: true,
                searching: true,
                info: true,
                ordering: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 10,
                language: {
                    search: 'Search:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    },
                    zeroRecords: 'Tidak ada data yang cocok'
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var annualTotal = 0;
                    var monthlyTotal = 0;
                    var gapTotal = 0;

                    display.forEach(function (rowIndex) {
                        annualTotal += parseBudgetNumber(api.cell(rowIndex, 4).data());
                        monthlyTotal += parseBudgetNumber(api.cell(rowIndex, 5).data());
                        gapTotal += parseBudgetNumber(api.cell(rowIndex, 6).data());
                    });

                    $('#summaryAnnualBudget').text(formatBudgetNumber(annualTotal));
                    $('#summaryMonthlyBudget').text(formatBudgetNumber(monthlyTotal));
                    $('#summaryBudgetGap').text(formatBudgetNumber(gapTotal));
                }
            });

            $('#budgetModal').on('shown.bs.modal', function () {
                if ($.fn.DataTable.isDataTable('#monthlyBreakdownTable')) {
                    $('#monthlyBreakdownTable').DataTable().columns.adjust().draw(false);
                    return;
                }

                $('#monthlyBreakdownTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: false,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 12,
                    lengthChange: false,
                    language: {
                        search: 'Search:',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        paginate: {
                            previous: 'Prev',
                            next: 'Next'
                        },
                        zeroRecords: 'Tidak ada data yang cocok'
                    }
                });
            });
        }

        updateMonthlyTotalSummary();

        <?php if (!empty($status)): ?>
        var flashStatus = '<?= htmlspecialchars((string) $status, ENT_QUOTES) ?>';
        var flashStorageKey = 'budget_master_budget_years_flash_' + flashStatus + '_<?= (int) $selectedYear ?>';
        if (!window.__budgetMasterBudgetYearsAlertShown && sessionStorage.getItem(flashStorageKey) !== 'shown') {
            window.__budgetMasterBudgetYearsAlertShown = true;
            sessionStorage.setItem(flashStorageKey, 'shown');
            <?php if ($status === 'sukses_simpan'): ?>
            Swal.fire('Success', 'Budget berhasil disimpan.', 'success');
            <?php elseif ($status === 'sukses_hapus'): ?>
            Swal.fire('Success', 'Budget berhasil dihapus.', 'success');
            <?php elseif ($status === 'gagal_simpan' || $status === 'gagal_hapus'): ?>
            Swal.fire('Gagal', 'Proses budget gagal dilakukan.', 'error');
            <?php endif; ?>
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
    .budget-btn--table-danger {
        padding: 0.52rem 0.9rem;
        box-shadow: none;
    }

    .budget-btn--table-primary {
        background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%);
        color: #1d5f8d;
        border: 1px solid #c9e1f3;
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
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
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

    .budget-summary-stack {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 10px;
    }

    .budget-monthly-summary--gap {
        background: linear-gradient(135deg, #f7f3ea 0%, #efe1c7 100%);
        color: #7a5a1e;
    }

    .budget-monthly-summary--gap.is-balanced {
        background: linear-gradient(135deg, #edf8ef 0%, #d8efdc 100%);
        color: #1d6b3e;
    }

    .budget-monthly-summary--gap.is-negative {
        background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%);
        color: #b93d38;
    }

    .budget-monthly-summary--gap.is-positive {
        background: linear-gradient(135deg, #fff8e9 0%, #f8ebc3 100%);
        color: #8a6617;
    }

    .js-budget-table thead th,
    .js-budget-table tfoot th,
    .js-budget-table-modal thead th,
    .js-budget-table-modal tfoot th {
        white-space: nowrap;
    }

    .js-budget-table tfoot th,
    .js-budget-table-modal tfoot th {
        background: #eef5fb;
        color: #315d7f;
    }

    #masterBudgetTable tfoot th {
        font-weight: 800;
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

        .budget-summary-stack {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
