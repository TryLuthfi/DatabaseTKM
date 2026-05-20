<?php
$status = $this->session->flashdata('status');
$totalRows = count($getMasterLogistikLokasiGudang ?? []);
$regionalOptions = ['REGIONAL 1', 'REGIONAL 2', 'REGIONAL 3', 'REGIONAL 4', 'REGIONAL 5'];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark"><?= htmlspecialchars((string) ($judul ?? 'Master Lokasi Gudang'), ENT_QUOTES) ?></h1>
                    <p class="text-muted mb-0">Kelola area gudang logistik dengan tampilan yang lebih rapi dan mudah ditelusuri.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm gudang-card">
                <div class="card-header gudang-card__header">
                    <h3 class="card-title">Filter Lokasi Gudang</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="gudang-field-label">Pencarian Cepat</label>
                            <input type="text" class="form-control gudang-input" id="gudang_quick_search"
                                placeholder="Cari regional, provinsi, kota, kecamatan, atau PIC">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap justify-content-md-end gudang-toolbar">
                                <button type="button" class="btn gudang-btn gudang-btn--ghost" id="gudang_reset_search">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn gudang-btn gudang-btn--success" id="btnTambahGudang">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Lokasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm gudang-card">
                <div class="card-header gudang-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Lokasi Gudang</h3>
                    <span class="badge badge-light"><?= number_format((float) $totalRows, 0, ',', '.') ?> lokasi</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="masterGudangTable" class="table table-bordered table-hover table-striped js-gudang-table">
                            <thead class="bg-info">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Regional</th>
                                    <th>Provinsi</th>
                                    <th>Kota</th>
                                    <th>Alamat Lokasi Gudang</th>
                                    <th>PIC</th>
                                    <th style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($getMasterLogistikLokasiGudang)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada data lokasi gudang.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($getMasterLogistikLokasiGudang as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars((string) ($row['regional_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['provinsi_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['kota_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['alamat_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nama_user'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td>
                                                <div class="gudang-action-inline">
                                                    <button type="button"
                                                        class="btn btn-sm gudang-btn gudang-btn--table-primary js-open-gudang-modal"
                                                        data-id="<?= (int) ($row['id_lokasi_gudang'] ?? 0) ?>"
                                                        data-regional="<?= htmlspecialchars((string) ($row['regional_lokasi_gudang'] ?? ''), ENT_QUOTES) ?>"
                                                        data-provinsi="<?= htmlspecialchars((string) ($row['provinsi_lokasi_gudang'] ?? ''), ENT_QUOTES) ?>"
                                                        data-kota="<?= htmlspecialchars((string) ($row['kota_lokasi_gudang'] ?? ''), ENT_QUOTES) ?>"
                                                        data-alamat="<?= htmlspecialchars((string) ($row['alamat_lokasi_gudang'] ?? ''), ENT_QUOTES) ?>"
                                                        data-id-user="<?= (int) ($row['id_user'] ?? 0) ?>">
                                                        <i class="fas fa-pen mr-1"></i> Edit
                                                    </button>
                                                    <a href="<?= site_url('Master_Logistik_Lokasi_Gudang/hapusLokasiGudang/' . (int) ($row['id_lokasi_gudang'] ?? 0)) ?>"
                                                        class="btn btn-sm gudang-btn gudang-btn--table-danger js-delete-gudang"
                                                        data-lokasi="<?= htmlspecialchars((string) ($row['kota_lokasi_gudang'] ?? 'lokasi ini'), ENT_QUOTES) ?>">
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
                                    <th>Regional</th>
                                    <th>Provinsi</th>
                                    <th>Kota</th>
                                    <th>Alamat Lokasi Gudang</th>
                                    <th>PIC</th>
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

<div class="modal fade" id="lokasiGudangModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content gudang-modal">
            <form method="post" action="<?= base_url('Master_Logistik_Lokasi_Gudang/tambahLokasiGudang') ?>" id="lokasiGudangForm">
                <div class="modal-header gudang-modal__header">
                    <div>
                        <span class="gudang-modal__eyebrow">Master Lokasi Gudang</span>
                        <h5 class="modal-title mb-1" id="lokasiGudangModalTitle">Tambah Lokasi Gudang</h5>
                        <p class="mb-0 gudang-modal__subtitle" id="lokasiGudangModalSubtitle">Lengkapi data area gudang untuk kebutuhan distribusi dan pelacakan stok.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_lokasi_gudang" value="">

                    <div class="gudang-form-section">
                        <div class="gudang-form-section__title">Informasi Area</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="gudang-field-label">Regional</label>
                                    <select name="regional_lokasi_gudang" id="regional_lokasi_gudang" class="form-control gudang-input" required>
                                        <?php foreach ($regionalOptions as $regional): ?>
                                            <option value="<?= htmlspecialchars($regional, ENT_QUOTES) ?>"><?= htmlspecialchars($regional, ENT_QUOTES) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="gudang-field-label">Provinsi</label>
                                    <input type="text" class="form-control gudang-input" name="provinsi_lokasi_gudang" id="provinsi_lokasi_gudang" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="gudang-field-label">Kota</label>
                                    <input type="text" class="form-control gudang-input" name="kota_lokasi_gudang" id="kota_lokasi_gudang" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="gudang-field-label">Alamat Lokasi Gudang</label>
                                    <input type="text" class="form-control gudang-input" name="alamat_lokasi_gudang" id="alamat_lokasi_gudang" autocomplete="off" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gudang-form-section mb-0">
                        <div class="gudang-form-section__title">Penanggung Jawab</div>
                        <div class="form-group mb-0">
                            <label class="gudang-field-label">Person In Control</label>
                            <select name="id_user" id="id_user" class="form-control gudang-input js-gudang-pic-select" required>
                                <?php foreach ($getMasterUser as $user): ?>
                                    <option value="<?= (int) ($user['id_user'] ?? 0) ?>">
                                        <?= htmlspecialchars((string) ($user['nama_user'] ?? '-'), ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gudang-modal__footer">
                    <button type="button" class="btn gudang-btn gudang-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn gudang-btn gudang-btn--primary" id="lokasiGudangSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        function openLokasiGudangModal(data) {
            var isEdit = !!(data && data.id);
            var form = document.getElementById('lokasiGudangForm');

            document.getElementById('lokasiGudangModalTitle').textContent = isEdit ? 'Edit Lokasi Gudang' : 'Tambah Lokasi Gudang';
            document.getElementById('lokasiGudangModalSubtitle').textContent = isEdit
                ? 'Perbarui data area gudang, lalu simpan perubahan jika sudah sesuai.'
                : 'Lengkapi data area gudang untuk kebutuhan distribusi dan pelacakan stok.';
            document.getElementById('lokasiGudangSubmitBtn').innerHTML = isEdit
                ? '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                : '<i class="fas fa-save mr-1"></i> Simpan';

            form.setAttribute(
                'action',
                isEdit
                    ? '<?= base_url('Master_Logistik_Lokasi_Gudang/editLokasiGudang/') ?>' + data.id
                    : '<?= base_url('Master_Logistik_Lokasi_Gudang/tambahLokasiGudang') ?>'
            );

            document.getElementById('id_lokasi_gudang').value = isEdit ? data.id : '';
            document.getElementById('regional_lokasi_gudang').value = isEdit ? (data.regional || 'REGIONAL 1') : 'REGIONAL 1';
            document.getElementById('provinsi_lokasi_gudang').value = isEdit ? (data.provinsi || '') : '';
            document.getElementById('kota_lokasi_gudang').value = isEdit ? (data.kota || '') : '';
            document.getElementById('alamat_lokasi_gudang').value = isEdit ? (data.alamat || '') : '';
            document.getElementById('id_user').value = isEdit ? (data.idUser || '') : (document.getElementById('id_user').options[0] ? document.getElementById('id_user').options[0].value : '');
            $('#id_user').trigger('change');

            $('#lokasiGudangModal').modal('show');
        }

        $(function () {
            var table = null;
            if ($.fn.select2) {
                $('.js-gudang-pic-select').select2({
                    width: '100%',
                    dropdownParent: $('#lokasiGudangModal'),
                    placeholder: 'Cari penanggung jawab',
                    allowClear: false
                });
            }

            if ($.fn.DataTable) {
                table = $('#masterGudangTable').DataTable({
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

            $('#btnTambahGudang').on('click', function () {
                openLokasiGudangModal(null);
            });

            $(document).on('click', '.js-open-gudang-modal', function () {
                openLokasiGudangModal({
                    id: $(this).data('id'),
                    regional: $(this).data('regional'),
                    provinsi: $(this).data('provinsi'),
                    kota: $(this).data('kota'),
                    alamat: $(this).data('alamat'),
                    idUser: $(this).data('id-user')
                });
            });

            $('#gudang_quick_search').on('keyup', function () {
                if (table) {
                    table.search($(this).val()).draw();
                }
            });

            $('#gudang_reset_search').on('click', function () {
                $('#gudang_quick_search').val('');
                if (table) {
                    table.search('').draw();
                }
            });

            $(document).on('click', '.js-delete-gudang', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var lokasi = $(this).data('lokasi') || 'lokasi ini';

                Swal.fire({
                    title: 'Hapus lokasi gudang?',
                    text: 'Data "' + lokasi + '" akan dihapus dari master lokasi.',
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
            if (!window.__masterGudangStatusShown) {
                window.__masterGudangStatusShown = true;
                <?php if ($status === 'sukses_tambah'): ?>
                Swal.fire('Success', 'Lokasi gudang berhasil ditambahkan.', 'success');
                <?php elseif ($status === 'sukses_edit'): ?>
                Swal.fire('Success', 'Lokasi gudang berhasil diperbarui.', 'success');
                <?php elseif ($status === 'sukses_hapus'): ?>
                Swal.fire('Success', 'Lokasi gudang berhasil dihapus.', 'success');
                <?php elseif ($status === 'gagal_tambah' || $status === 'gagal_edit' || $status === 'gagal_hapus'): ?>
                Swal.fire('Gagal', 'Proses lokasi gudang gagal dilakukan.', 'error');
                <?php endif; ?>
            }
            <?php endif; ?>
        });
    })();
</script>

<style>
    .gudang-toolbar {
        gap: 10px;
    }

    .gudang-field-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #48657f;
        text-transform: uppercase;
    }

    .gudang-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .gudang-card__header {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
    }

    .gudang-card .card-title {
        font-weight: 700;
    }

    .gudang-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .gudang-btn:hover,
    .gudang-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .gudang-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .gudang-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .gudang-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .gudang-btn--table-primary,
    .gudang-btn--table-danger {
        padding: 0.52rem 0.9rem;
        box-shadow: none;
    }

    .gudang-btn--table-primary {
        background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%);
        color: #1d5f8d;
        border: 1px solid #c9e1f3;
    }

    .gudang-btn--table-danger {
        background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%);
        color: #b93d38;
        border: 1px solid #f5c8c5;
    }

    .gudang-action-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .gudang-btn--table-danger:hover,
    .gudang-btn--table-danger:focus {
        color: #fff;
        background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%);
        border-color: #b93d38;
    }

    .gudang-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .gudang-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .gudang-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .gudang-modal__subtitle {
        max-width: 85%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .gudang-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .gudang-modal__footer {
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .gudang-form-section {
        margin-bottom: 1rem;
        padding: 1rem 1rem 0.2rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .gudang-form-section__title {
        margin-bottom: 0.9rem;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .gudang-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .gudang-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .gudang-modal .select2-container {
        width: 100% !important;
    }

    .gudang-modal .select2-container--default .select2-selection--single {
        min-height: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        display: flex;
        align-items: center;
    }

    .gudang-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 42px;
        padding-left: 12px;
        padding-right: 32px;
    }

    .gudang-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
        right: 6px;
    }

    .gudang-modal .select2-container--default.select2-container--focus .select2-selection--single,
    .gudang-modal .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .select2-dropdown {
        border: 1px solid #cfe0ee;
        border-radius: 10px;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #cfe0ee;
        border-radius: 8px;
    }

    .js-gudang-table thead th,
    .js-gudang-table tfoot th {
        white-space: nowrap;
    }

    .js-gudang-table tfoot th {
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
        .gudang-toolbar {
            margin-top: 1rem;
            justify-content: flex-start !important;
        }

        .gudang-btn {
            width: 100%;
        }

        .gudang-modal__subtitle {
            max-width: 100%;
        }
    }
</style>
