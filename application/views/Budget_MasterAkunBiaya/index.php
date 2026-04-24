<?php $status = $this->session->flashdata('status'); ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">Master Item Budget</h1>
                    <p class="text-muted mb-0">Kelola item budget dengan tampilan yang lebih rapi, searchable, dan mudah dipantau.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">Filter Item</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Budget_MasterAkunBiaya') ?>">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="budget-field-label">Pencarian Cepat</label>
                                <input type="text" class="form-control budget-input" name="keyword"
                                    placeholder="Cari kode item, nama item, kategori, atau group"
                                    value="<?= htmlspecialchars($keyword ?? '', ENT_QUOTES) ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap justify-content-md-end budget-toolbar">
                                <button type="submit" class="btn budget-btn budget-btn--primary">
                                    <i class="fas fa-search mr-1"></i> Cari
                                </button>
                                <a href="<?= base_url('Budget_MasterAkunBiaya') ?>" class="btn budget-btn budget-btn--ghost">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </a>
                                <button type="button" class="btn budget-btn budget-btn--success" data-toggle="modal"
                                    data-target="#itemModal" onclick="openItemModal()">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Item
                                </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Master Item</h3>
                    <span class="badge badge-light"><?= count($items) ?> item</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="masterItemTable" class="table table-bordered table-hover table-striped js-budget-table">
                        <thead class="bg-info">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th>Kategori</th>
                                <th>Group</th>
                                <th>UoM</th>
                                <th>Default</th>
                                <th>Status</th>
                                <th style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada master item budget.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($item['item_code']) ?></td>
                                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                                        <td><?= htmlspecialchars($item['item_category'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['item_group'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['uom'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($item['default_direction']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= (int) $item['is_active'] === 1 ? 'success' : 'secondary' ?>">
                                                <?= (int) $item['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="budget-action-inline">
                                                <button type="button" class="btn btn-sm budget-btn budget-btn--table-primary js-open-item-modal"
                                                    data-item='<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
                                                    <i class="fas fa-pen mr-1"></i> Edit
                                                </button>
                                                <a href="<?= base_url('Budget_MasterAkunBiaya/delete/' . (int) $item['id_budget_item']) ?>"
                                                    class="btn btn-sm budget-btn budget-btn--table-danger js-delete-item"
                                                    data-item-name="<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>">
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
                                <th>No</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th>Kategori</th>
                                <th>Group</th>
                                <th>UoM</th>
                                <th>Default</th>
                                <th>Status</th>
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

<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content budget-modal">
            <form method="post" action="<?= base_url('Budget_MasterAkunBiaya/save') ?>" id="budgetItemForm">
                <div class="modal-header budget-modal__header">
                    <div>
                        <span class="budget-modal__eyebrow">Budget MasterAkunBiaya</span>
                        <h5 class="modal-title mb-1" id="itemModalTitle">Tambah Item Budget</h5>
                        <p class="mb-0 budget-modal__subtitle" id="itemModalSubtitle">Lengkapi data master item agar siap dipakai di proses budgeting.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_budget_item" id="id_budget_item">
                    <div class="budget-form-section">
                        <div class="budget-form-section__title">Informasi Utama</div>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="budget-field-label">Kode Item</label>
                                <input type="text" class="form-control budget-input" name="item_code" id="item_code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="budget-field-label">Nama Item</label>
                                <input type="text" class="form-control budget-input" name="item_name" id="item_name" required>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="budget-form-section">
                        <div class="budget-form-section__title">Klasifikasi</div>
                        <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="budget-field-label">Kategori</label>
                                <input type="text" class="form-control budget-input" name="item_category" id="item_category">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="budget-field-label">Group</label>
                                <input type="text" class="form-control budget-input" name="item_group" id="item_group">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="budget-field-label">UoM</label>
                                <input type="text" class="form-control budget-input" name="uom" id="uom" placeholder="pcs, lot, meter">
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="budget-form-section">
                        <div class="budget-form-section__title">Pengaturan</div>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="budget-field-label">Default Debit / Kredit</label>
                                <select class="form-control budget-input" name="default_direction" id="default_direction">
                                    <option value="DEBIT">DEBIT</option>
                                    <option value="KREDIT">KREDIT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="budget-field-label">Status</label>
                                <select class="form-control budget-input" name="is_active" id="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary" id="budgetItemSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openItemModal(item) {
        item = item || null;

        document.getElementById('itemModalTitle').textContent = item ? 'Edit Item Budget' : 'Tambah Item Budget';
        document.getElementById('itemModalSubtitle').textContent = item
            ? 'Perbarui data item budget, lalu simpan perubahan setelah Anda yakin semuanya sudah sesuai.'
            : 'Lengkapi data master item agar siap dipakai di proses budgeting.';
        document.getElementById('id_budget_item').value = item ? item.id_budget_item : '';
        document.getElementById('item_code').value = item ? item.item_code : '';
        document.getElementById('item_name').value = item ? item.item_name : '';
        document.getElementById('item_category').value = item ? (item.item_category || '') : '';
        document.getElementById('item_group').value = item ? (item.item_group || '') : '';
        document.getElementById('uom').value = item ? (item.uom || '') : '';
        document.getElementById('default_direction').value = item ? item.default_direction : 'DEBIT';
        document.getElementById('is_active').value = item ? item.is_active : '1';
        document.getElementById('budgetItemSubmitBtn').innerHTML = item
            ? '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
            : '<i class="fas fa-save mr-1"></i> Simpan';

        $('#itemModal').modal('show');
    }

    $(function () {
        if ($.fn.DataTable) {
            $('#masterItemTable').DataTable({
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
                }
            });
        }

        var $budgetItemForm = $('#budgetItemForm');

        $(document).on('click', '.js-open-item-modal', function (e) {
            e.preventDefault();

            var rawItem = $(this).attr('data-item');
            var item = null;

            if (rawItem) {
                try {
                    item = JSON.parse(rawItem);
                } catch (error) {
                    item = null;
                }
            }

            $('.budget-action-dropdown').removeClass('is-open');
            openItemModal(item);
        });

        $budgetItemForm.on('submit', function (e) {
            var form = this;
            if (form.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();

            var isEdit = $.trim($('#id_budget_item').val()) !== '';
            Swal.fire({
                title: isEdit ? 'Simpan perubahan item?' : 'Tambah item baru?',
                text: isEdit
                    ? 'Perubahan data akan langsung memperbarui master item budget.'
                    : 'Item baru akan ditambahkan ke daftar master budget.',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1f6da1',
                cancelButtonColor: '#9aa9b8',
                confirmButtonText: isEdit ? 'Ya, simpan perubahan' : 'Ya, simpan item',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.value) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        });

        $(document).on('click', '.js-delete-item', function (e) {
            e.preventDefault();

            var href = $(this).attr('href');
            var itemName = $(this).data('item-name') || 'item ini';

            Swal.fire({
                title: 'Hapus item budget?',
                text: 'Data "' + itemName + '" akan dihapus dari master item.',
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

        <?php if (!empty($status)): ?>
        if (!window.__budgetMasterAkunBiayaAlertShown) {
            window.__budgetMasterAkunBiayaAlertShown = true;
            <?php if ($status === 'sukses_tambah'): ?>
            Swal.fire('Success', 'Item budget berhasil ditambahkan.', 'success');
            <?php elseif ($status === 'sukses_edit'): ?>
            Swal.fire('Success', 'Item budget berhasil diperbarui.', 'success');
            <?php elseif ($status === 'sukses_hapus'): ?>
            Swal.fire('Success', 'Item budget berhasil dihapus.', 'success');
            <?php elseif ($status === 'gagal_hapus' || $status === 'gagal_simpan'): ?>
            Swal.fire('Gagal', 'Proses item budget gagal dilakukan.', 'error');
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

    .budget-action-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .budget-btn--table-danger:hover,
    .budget-btn--table-danger:focus {
        color: #fff;
        background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%);
        border-color: #b93d38;
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
        padding: 1rem 1rem 0.2rem;
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

    .js-budget-table thead th,
    .js-budget-table tfoot th {
        white-space: nowrap;
    }

    .js-budget-table tfoot th {
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

        .budget-modal__subtitle {
            max-width: 100%;
        }
    }
</style>
