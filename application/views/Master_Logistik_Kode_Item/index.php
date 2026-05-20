<?php
$status = $this->session->flashdata('status');
$totalRows = count($getMasterLogistikKodeItem ?? []);

$satuanOptions = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];
$kategoriOptions = ['TIANG', 'OTB', 'KABEL', 'HDPE', 'FDT', 'FAT', 'CLOSURE', 'AKSESORIES'];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark"><?= htmlspecialchars((string) ($judul ?? 'Master Kode Item Logistik'), ENT_QUOTES) ?></h1>
                    <p class="text-muted mb-0">Kelola master kode item logistik dengan tampilan yang lebih rapi dan mudah ditelusuri.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm kode-item-card">
                <div class="card-header kode-item-card__header">
                    <h3 class="card-title">Filter Kode Item</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="kode-item-field-label">Pencarian Cepat</label>
                            <input type="text" class="form-control kode-item-input" id="kode_item_quick_search"
                                placeholder="Cari nama item, kategori, designator, project, atau kepemilikan">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap justify-content-md-end kode-item-toolbar">
                                <button type="button" class="btn kode-item-btn kode-item-btn--ghost" id="kode_item_reset_search">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn kode-item-btn kode-item-btn--success" id="btnTambahKodeItem">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm kode-item-card">
                <div class="card-header kode-item-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Master Kode Item</h3>
                    <span class="badge badge-light"><?= number_format((float) $totalRows, 0, ',', '.') ?> item</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="masterKodeItemTable" class="table table-bordered table-hover table-striped js-kode-item-table">
                            <thead class="bg-info">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Nama Item</th>
                                    <th>Kategori</th>
                                    <th>Designator</th>
                                    <th>Satuan</th>
                                    <th>Project Penggunaan</th>
                                    <th>Kepemilikan</th>
                                    <th style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($getMasterLogistikKodeItem)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada data kode item.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($getMasterLogistikKodeItem as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nama_item'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars(strtoupper((string) ($row['kategori_item'] ?? '-')), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['designator'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['satuan_item'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['project_item_names'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nama_bowheer'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td>
                                                <div class="kode-item-action-inline">
                                                    <button type="button"
                                                        class="btn btn-sm kode-item-btn kode-item-btn--table-primary js-open-kode-item-modal"
                                                        data-id="<?= (int) ($row['id_kode_item'] ?? 0) ?>"
                                                        data-nama-item="<?= htmlspecialchars((string) ($row['nama_item'] ?? ''), ENT_QUOTES) ?>"
                                                        data-kategori-item="<?= htmlspecialchars((string) ($row['kategori_item'] ?? ''), ENT_QUOTES) ?>"
                                                        data-designator="<?= htmlspecialchars((string) ($row['designator'] ?? ''), ENT_QUOTES) ?>"
                                                        data-satuan-item="<?= htmlspecialchars((string) ($row['satuan_item'] ?? ''), ENT_QUOTES) ?>"
                                                        data-project-item="<?= htmlspecialchars((string) ($row['project_item'] ?? ''), ENT_QUOTES) ?>"
                                                        data-id-bowheer="<?= (int) ($row['id_bowheer_pemilik_item'] ?? 0) ?>"
                                                        data-harga-penjualan="<?= htmlspecialchars((string) ($row['harga_penjualan'] ?? ''), ENT_QUOTES) ?>">
                                                        <i class="fas fa-pen mr-1"></i> Edit
                                                    </button>
                                                    <a href="<?= site_url('Master_Logistik_Kode_Item/hapusKodeItem/' . (int) ($row['id_kode_item'] ?? 0)) ?>"
                                                        class="btn btn-sm kode-item-btn kode-item-btn--table-danger js-delete-kode-item"
                                                        data-item="<?= htmlspecialchars((string) ($row['nama_item'] ?? 'item ini'), ENT_QUOTES) ?>">
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
                                    <th>Nama Item</th>
                                    <th>Kategori</th>
                                    <th>Designator</th>
                                    <th>Satuan</th>
                                    <th>Project Penggunaan</th>
                                    <th>Kepemilikan</th>
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

<div class="modal fade" id="kodeItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content kode-item-modal">
            <form method="post" action="<?= base_url('Master_Logistik_Kode_Item/tambahKodeItem') ?>" id="kodeItemForm">
                <div class="modal-header kode-item-modal__header">
                    <div>
                        <span class="kode-item-modal__eyebrow">Master Kode Item Logistik</span>
                        <h5 class="modal-title mb-1" id="kodeItemModalTitle">Tambah Kode Item</h5>
                        <p class="mb-0 kode-item-modal__subtitle" id="kodeItemModalSubtitle">Lengkapi data item agar siap dipakai di transaksi logistik.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_kode_item" value="">

                    <div class="kode-item-form-section">
                        <div class="kode-item-form-section__title">Informasi Item</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Nama Item</label>
                                    <input type="text" class="form-control kode-item-input" name="nama_item" id="nama_item" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Kategori Item</label>
                                    <select name="kategori_item" id="kategori_item" class="form-control kode-item-input" required>
                                        <?php foreach ($kategoriOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"><?= htmlspecialchars($option, ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Satuan</label>
                                    <select name="satuan_item" id="satuan_item" class="form-control kode-item-input" required>
                                        <?php foreach ($satuanOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option, ENT_QUOTES) ?>"><?= htmlspecialchars($option, ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Designator</label>
                                    <input type="text" class="form-control kode-item-input" name="designator" id="designator" autocomplete="off" placeholder="A-AS-XXX-001" required>
                                </div>
                            </div>
                            <div class="col-md-6" id="harga_penjualan_wrapper">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Harga Penjualan</label>
                                    <input type="text" class="form-control kode-item-input" name="harga_penjualan" id="harga_penjualan" autocomplete="off" placeholder="Harga">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kode-item-form-section mb-0">
                        <div class="kode-item-form-section__title">Project & Kepemilikan</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="kode-item-field-label">Penggunaan Project</label>
                                    <select id="project_item" class="form-control kode-item-input js-project-select" data-placeholder="Pilih project penggunaan">
                                        <?php foreach ($getMasterBowheer as $project): ?>
                                            <option value="<?= (int) ($project['id_bowheer'] ?? 0) ?>">
                                                <?= htmlspecialchars((string) ($project['nama_bowheer'] ?? '-'), ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="kode-item-field-label">Kepemilikan Item</label>
                                    <select name="id_bowheer_pemilik_item" id="id_bowheer_pemilik_item" class="form-control kode-item-input js-kepemilikan-select" required>
                                        <?php foreach ($getMasterKepemilikan as $kepemilikan): ?>
                                            <option value="<?= (int) ($kepemilikan['id_bowheer'] ?? 0) ?>">
                                                <?= htmlspecialchars((string) ($kepemilikan['nama_bowheer'] ?? '-'), ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer kode-item-modal__footer">
                    <button type="button" class="btn kode-item-btn kode-item-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn kode-item-btn kode-item-btn--primary" id="kodeItemSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        function normalizeOptionValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function setSelectValueByNormalizedMatch(selector, rawValue, fallbackValue) {
            var $select = $(selector);
            var normalizedRaw = normalizeOptionValue(rawValue);
            var matchedValue = null;

            $select.find('option').each(function () {
                var optionValue = $(this).val();
                if (normalizeOptionValue(optionValue) === normalizedRaw) {
                    matchedValue = optionValue;
                    return false;
                }
            });

            $select.val(matchedValue !== null ? matchedValue : fallbackValue);
        }

        function normalizeProjectValue(projectText) {
            if (!projectText) {
                return '';
            }

            var normalized = String(projectText).split(',')[0] || '';
            return $.trim(normalized);
        }

        function configureProjectSelect() {
            var $project = $('#project_item');
            $project.val(null).trigger('change');
            $project.attr('name', 'project_item').removeAttr('multiple');

            if ($.fn.select2) {
                if ($project.hasClass('select2-hidden-accessible')) {
                    $project.select2('destroy');
                }
                $project.select2({
                    width: '100%',
                    dropdownParent: $('#kodeItemModal'),
                    placeholder: 'Pilih project penggunaan',
                    allowClear: false
                });
            }
        }

        function openKodeItemModal(data) {
            var isEdit = !!(data && data.id);
            var form = document.getElementById('kodeItemForm');

            document.getElementById('kodeItemModalTitle').textContent = isEdit ? 'Edit Kode Item' : 'Tambah Kode Item';
            document.getElementById('kodeItemModalSubtitle').textContent = isEdit
                ? 'Perbarui data item, lalu simpan perubahan jika sudah sesuai.'
                : 'Lengkapi data item agar siap dipakai di transaksi logistik.';
            document.getElementById('kodeItemSubmitBtn').innerHTML = isEdit
                ? '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                : '<i class="fas fa-save mr-1"></i> Simpan';

            form.setAttribute(
                'action',
                isEdit
                    ? '<?= base_url('Master_Logistik_Kode_Item/editKodeItem/') ?>' + data.id
                    : '<?= base_url('Master_Logistik_Kode_Item/tambahKodeItem') ?>'
            );

            configureProjectSelect();

            document.getElementById('id_kode_item').value = isEdit ? data.id : '';
            document.getElementById('nama_item').value = isEdit ? (data.namaItem || '') : '';
            setSelectValueByNormalizedMatch('#kategori_item', isEdit ? data.kategoriItem : 'Tiang', 'Tiang');
            setSelectValueByNormalizedMatch('#satuan_item', isEdit ? data.satuanItem : 'Pcs', 'Pcs');
            document.getElementById('designator').value = isEdit ? (data.designator || '') : '';
            document.getElementById('id_bowheer_pemilik_item').value = isEdit ? (data.idBowheer || '') : (document.getElementById('id_bowheer_pemilik_item').options[0] ? document.getElementById('id_bowheer_pemilik_item').options[0].value : '');
            document.getElementById('harga_penjualan').value = isEdit ? (data.hargaPenjualan || '') : '';

            if (isEdit) {
                $('#project_item').val(normalizeProjectValue(data.projectItem)).trigger('change');
            } else {
                var firstProjectValue = $('#project_item option:first').val() || null;
                $('#project_item').val(firstProjectValue).trigger('change');
            }

            $('#id_bowheer_pemilik_item').trigger('change');
            $('#harga_penjualan_wrapper').toggleClass('d-none', isEdit);
            $('#kodeItemModal').modal('show');
        }

        $(function () {
            var table = null;

            if ($.fn.select2) {
                $('.js-kepemilikan-select').select2({
                    width: '100%',
                    dropdownParent: $('#kodeItemModal'),
                    placeholder: 'Pilih kepemilikan item',
                    allowClear: false
                });
            }

            configureProjectSelect();

            if ($.fn.DataTable) {
                table = $('#masterKodeItemTable').DataTable({
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

            $('#btnTambahKodeItem').on('click', function () {
                openKodeItemModal(null);
            });

            $(document).on('click', '.js-open-kode-item-modal', function () {
                openKodeItemModal({
                    id: $(this).data('id'),
                    namaItem: $(this).data('nama-item'),
                    kategoriItem: $(this).data('kategori-item'),
                    designator: $(this).data('designator'),
                    satuanItem: $(this).data('satuan-item'),
                    projectItem: $(this).data('project-item'),
                    idBowheer: $(this).data('id-bowheer'),
                    hargaPenjualan: $(this).data('harga-penjualan')
                });
            });

            $('#kode_item_quick_search').on('keyup', function () {
                if (table) {
                    table.search($(this).val()).draw();
                }
            });

            $('#kode_item_reset_search').on('click', function () {
                $('#kode_item_quick_search').val('');
                if (table) {
                    table.search('').draw();
                }
            });

            $(document).on('click', '.js-delete-kode-item', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var itemName = $(this).data('item') || 'item ini';

                Swal.fire({
                    title: 'Hapus kode item?',
                    text: 'Data "' + itemName + '" akan dihapus dari master item.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d9534f',
                    cancelButtonColor: '#9aa9b8',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.value || result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });

            <?php if (!empty($status)): ?>
            if (!window.__masterKodeItemStatusShown) {
                window.__masterKodeItemStatusShown = true;
                <?php if ($status === 'sukses_tambah'): ?>
                Swal.fire('Success', 'Kode item berhasil ditambahkan.', 'success');
                <?php elseif ($status === 'sukses_edit'): ?>
                Swal.fire('Success', 'Kode item berhasil diperbarui.', 'success');
                <?php elseif ($status === 'sukses_hapus'): ?>
                Swal.fire('Success', 'Kode item berhasil dihapus.', 'success');
                <?php elseif ($status === 'gagal_tambah' || $status === 'gagal_edit' || $status === 'gagal_hapus'): ?>
                Swal.fire('Gagal', 'Proses kode item gagal dilakukan.', 'error');
                <?php endif; ?>
            }
            <?php endif; ?>
        });
    })();
</script>

<style>
    .kode-item-toolbar {
        gap: 10px;
    }

    .kode-item-field-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #48657f;
        text-transform: uppercase;
    }

    .kode-item-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .kode-item-card__header {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
    }

    .kode-item-card .card-title {
        font-weight: 700;
    }

    .kode-item-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .kode-item-btn:hover,
    .kode-item-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .kode-item-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .kode-item-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .kode-item-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .kode-item-btn--table-primary,
    .kode-item-btn--table-danger {
        padding: 0.52rem 0.9rem;
        box-shadow: none;
    }

    .kode-item-btn--table-primary {
        background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%);
        color: #1d5f8d;
        border: 1px solid #c9e1f3;
    }

    .kode-item-btn--table-danger {
        background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%);
        color: #b93d38;
        border: 1px solid #f5c8c5;
    }

    .kode-item-action-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .kode-item-btn--table-danger:hover,
    .kode-item-btn--table-danger:focus {
        color: #fff;
        background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%);
        border-color: #b93d38;
    }

    .kode-item-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .kode-item-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .kode-item-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .kode-item-modal__subtitle {
        max-width: 85%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .kode-item-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .kode-item-modal__footer {
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .kode-item-form-section {
        margin-bottom: 1rem;
        padding: 1rem 1rem 0.2rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .kode-item-form-section__title {
        margin-bottom: 0.9rem;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .kode-item-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .kode-item-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .kode-item-modal .select2-container {
        width: 100% !important;
    }

    .kode-item-modal .select2-container--default .select2-selection--single,
    .kode-item-modal .select2-container--default .select2-selection--multiple {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
    }

    .kode-item-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 12px;
    }

    .kode-item-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .kode-item-modal .select2-container--default .select2-selection--multiple {
        padding: 4px 8px 6px;
    }

    .kode-item-modal .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 4px;
        border-radius: 8px;
        border: 1px solid #c9e1f3;
        background: #eaf4fb;
        color: #1d5f8d;
        padding: 2px 8px;
    }

    .kode-item-modal .select2-container--default.select2-container--focus .select2-selection--single,
    .kode-item-modal .select2-container--default.select2-container--focus .select2-selection--multiple,
    .kode-item-modal .select2-container--default.select2-container--open .select2-selection--single,
    .kode-item-modal .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .js-kode-item-table thead th,
    .js-kode-item-table tfoot th {
        white-space: nowrap;
    }

    .js-kode-item-table tfoot th {
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
        .kode-item-toolbar {
            margin-top: 1rem;
            justify-content: flex-start !important;
        }

        .kode-item-btn {
            width: 100%;
        }

        .kode-item-modal__subtitle {
            max-width: 100%;
        }
    }
</style>
