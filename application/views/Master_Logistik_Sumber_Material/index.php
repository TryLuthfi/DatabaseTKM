<?php
$status = $this->session->flashdata('status');
$masukRows = $getMasterLogistikSumberMaterialMasuk ?? [];
$keluarRows = $getMasterLogistikSumberMaterialKeluar ?? [];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark"><?= htmlspecialchars((string) ($judul ?? 'Master Sumber Material'), ENT_QUOTES) ?></h1>
                    <p class="text-muted mb-0">Kelola sumber material masuk dan keluar dengan tampilan yang konsisten dan mudah diaudit.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm sumber-card">
                <div class="card-header sumber-card__header">
                    <h3 class="card-title">Filter Sumber Material</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="sumber-field-label">Pencarian Cepat</label>
                            <input type="text" class="form-control sumber-input" id="sumber_quick_search"
                                placeholder="Cari nama sumber material di tabel masuk dan keluar">
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap justify-content-md-end sumber-toolbar">
                                <button type="button" class="btn sumber-btn sumber-btn--ghost" id="sumber_reset_search">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn sumber-btn sumber-btn--success js-open-sumber-modal" data-add-status="IN">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Masuk
                                </button>
                                <button type="button" class="btn sumber-btn sumber-btn--primary js-open-sumber-modal" data-add-status="OUT">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah Keluar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm sumber-card h-100">
                        <div class="card-header sumber-card__header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">Sumber Material Masuk (IN)</h3>
                            <span class="badge badge-light"><?= number_format((float) count($masukRows), 0, ',', '.') ?> data</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="masterSumberMaterialInTable" class="table table-bordered table-hover table-striped js-sumber-table">
                                    <thead class="bg-info">
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th>Nama Sumber Material</th>
                                            <th style="width: 160px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($masukRows)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Belum ada data sumber material masuk.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($masukRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? '-'), ENT_QUOTES) ?></td>
                                                    <td>
                                                        <div class="sumber-action-inline">
                                                            <button type="button"
                                                                class="btn btn-sm sumber-btn sumber-btn--table-primary js-open-sumber-modal"
                                                                data-id="<?= (int) ($row['id_sumber_material'] ?? 0) ?>"
                                                                data-nama="<?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? ''), ENT_QUOTES) ?>"
                                                                data-status="<?= htmlspecialchars((string) ($row['status_sumber_material'] ?? 'IN'), ENT_QUOTES) ?>">
                                                                <i class="fas fa-pen mr-1"></i> Edit
                                                            </button>
                                                            <a href="<?= site_url('Master_Logistik_Sumber_Material/hapusSumberMaterial/' . (int) ($row['id_sumber_material'] ?? 0)) ?>"
                                                                class="btn btn-sm sumber-btn sumber-btn--table-danger js-delete-sumber-material"
                                                                data-nama="<?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? 'item ini'), ENT_QUOTES) ?>">
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
                                            <th>Nama Sumber Material</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm sumber-card h-100">
                        <div class="card-header sumber-card__header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">Sumber Material Keluar (OUT)</h3>
                            <span class="badge badge-light"><?= number_format((float) count($keluarRows), 0, ',', '.') ?> data</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="masterSumberMaterialOutTable" class="table table-bordered table-hover table-striped js-sumber-table">
                                    <thead class="bg-info">
                                        <tr>
                                            <th style="width: 60px;">No</th>
                                            <th>Nama Sumber Material</th>
                                            <th style="width: 160px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($keluarRows)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Belum ada data sumber material keluar.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($keluarRows as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? '-'), ENT_QUOTES) ?></td>
                                                    <td>
                                                        <div class="sumber-action-inline">
                                                            <button type="button"
                                                                class="btn btn-sm sumber-btn sumber-btn--table-primary js-open-sumber-modal"
                                                                data-id="<?= (int) ($row['id_sumber_material'] ?? 0) ?>"
                                                                data-nama="<?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? ''), ENT_QUOTES) ?>"
                                                                data-status="<?= htmlspecialchars((string) ($row['status_sumber_material'] ?? 'OUT'), ENT_QUOTES) ?>">
                                                                <i class="fas fa-pen mr-1"></i> Edit
                                                            </button>
                                                            <a href="<?= site_url('Master_Logistik_Sumber_Material/hapusSumberMaterial/' . (int) ($row['id_sumber_material'] ?? 0)) ?>"
                                                                class="btn btn-sm sumber-btn sumber-btn--table-danger js-delete-sumber-material"
                                                                data-nama="<?= htmlspecialchars((string) ($row['nama_sumber_material'] ?? 'item ini'), ENT_QUOTES) ?>">
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
                                            <th>Nama Sumber Material</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="sumberMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content sumber-modal">
            <form method="post" action="<?= base_url('Master_Logistik_Sumber_Material/tambahSumberMaterialMasuk') ?>" id="sumberMaterialForm">
                <div class="modal-header sumber-modal__header">
                    <div>
                        <span class="sumber-modal__eyebrow">Master Sumber Material</span>
                        <h5 class="modal-title mb-1" id="sumberMaterialModalTitle">Tambah Sumber Material</h5>
                        <p class="mb-0 sumber-modal__subtitle" id="sumberMaterialModalSubtitle">Lengkapi nama dan status sumber material, lalu simpan.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_sumber_material" value="">
                    <div class="sumber-form-section mb-0">
                        <div class="sumber-form-section__title">Informasi Sumber Material</div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="sumber-field-label">Nama Sumber Material</label>
                                    <input type="text" class="form-control sumber-input" name="nama_sumber_material" id="nama_sumber_material" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="sumber-field-label">Status</label>
                                    <select name="status_sumber_material" id="status_sumber_material" class="form-control sumber-input" required>
                                        <option value="IN">IN</option>
                                        <option value="OUT">OUT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer sumber-modal__footer">
                    <button type="button" class="btn sumber-btn sumber-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn sumber-btn sumber-btn--primary" id="sumberMaterialSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        function openSumberMaterialModal(payload) {
            var data = payload || {};
            var isEdit = !!data.id;
            var form = document.getElementById('sumberMaterialForm');
            var statusValue = (data.status || data.addStatus || 'IN').toUpperCase();
            if (statusValue !== 'OUT') {
                statusValue = 'IN';
            }

            document.getElementById('sumberMaterialModalTitle').textContent = isEdit ? 'Edit Sumber Material' : 'Tambah Sumber Material';
            document.getElementById('sumberMaterialModalSubtitle').textContent = isEdit
                ? 'Perbarui data sumber material, lalu simpan perubahan jika sudah sesuai.'
                : 'Lengkapi nama dan status sumber material, lalu simpan.';
            document.getElementById('sumberMaterialSubmitBtn').innerHTML = isEdit
                ? '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                : '<i class="fas fa-save mr-1"></i> Simpan';

            if (isEdit) {
                form.setAttribute('action', '<?= base_url('Master_Logistik_Sumber_Material/editSumberMaterial/') ?>' + data.id);
            } else {
                form.setAttribute('action', statusValue === 'OUT'
                    ? '<?= base_url('Master_Logistik_Sumber_Material/tambahSumberMaterialKeluar') ?>'
                    : '<?= base_url('Master_Logistik_Sumber_Material/tambahSumberMaterialMasuk') ?>');
            }

            document.getElementById('id_sumber_material').value = isEdit ? data.id : '';
            document.getElementById('nama_sumber_material').value = isEdit ? (data.nama || '') : '';
            document.getElementById('status_sumber_material').value = statusValue;
            document.getElementById('status_sumber_material').disabled = !isEdit && !!data.addStatus;

            $('#sumberMaterialModal').modal('show');
        }

        $(function () {
            var inTable = null;
            var outTable = null;
            var dtLang = {
                search: 'Search:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                paginate: { previous: 'Prev', next: 'Next' },
                zeroRecords: 'Tidak ada data yang cocok'
            };

            if ($.fn.DataTable) {
                inTable = $('#masterSumberMaterialInTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    language: dtLang
                });

                outTable = $('#masterSumberMaterialOutTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: false,
                    autoWidth: false,
                    pageLength: 10,
                    language: dtLang
                });
            }

            $('.js-open-sumber-modal').on('click', function () {
                openSumberMaterialModal({
                    id: $(this).data('id') || '',
                    nama: $(this).data('nama') || '',
                    status: $(this).data('status') || '',
                    addStatus: $(this).data('add-status') || ''
                });
            });

            $('#sumber_quick_search').on('keyup', function () {
                var keyword = $(this).val();
                if (inTable) {
                    inTable.search(keyword).draw();
                }
                if (outTable) {
                    outTable.search(keyword).draw();
                }
            });

            $('#sumber_reset_search').on('click', function () {
                $('#sumber_quick_search').val('');
                if (inTable) {
                    inTable.search('').draw();
                }
                if (outTable) {
                    outTable.search('').draw();
                }
            });

            $(document).on('click', '.js-delete-sumber-material', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var nama = $(this).data('nama') || 'item ini';

                Swal.fire({
                    title: 'Hapus sumber material?',
                    text: 'Data "' + nama + '" akan dihapus dari master.',
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
            if (!window.__masterSumberMaterialStatusShown) {
                window.__masterSumberMaterialStatusShown = true;
                <?php if ($status === 'sukses_tambah'): ?>
                Swal.fire('Success', 'Sumber material berhasil ditambahkan.', 'success');
                <?php elseif ($status === 'sukses_edit'): ?>
                Swal.fire('Success', 'Sumber material berhasil diperbarui.', 'success');
                <?php elseif ($status === 'sukses_hapus'): ?>
                Swal.fire('Success', 'Sumber material berhasil dihapus.', 'success');
                <?php elseif ($status === 'gagal_tambah' || $status === 'gagal_edit' || $status === 'gagal_hapus'): ?>
                Swal.fire('Gagal', 'Proses sumber material gagal dilakukan.', 'error');
                <?php endif; ?>
            }
            <?php endif; ?>
        });
    })();
</script>

<style>
    .sumber-toolbar {
        gap: 10px;
    }

    .sumber-field-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #48657f;
        text-transform: uppercase;
    }

    .sumber-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }

    .sumber-card__header {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8);
        color: #fff;
    }

    .sumber-card .card-title {
        font-weight: 700;
    }

    .sumber-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .sumber-btn:hover,
    .sumber-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .sumber-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .sumber-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .sumber-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .sumber-btn--table-primary,
    .sumber-btn--table-danger {
        padding: 0.52rem 0.9rem;
        box-shadow: none;
    }

    .sumber-btn--table-primary {
        background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%);
        color: #1d5f8d;
        border: 1px solid #c9e1f3;
    }

    .sumber-btn--table-danger {
        background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%);
        color: #b93d38;
        border: 1px solid #f5c8c5;
    }

    .sumber-action-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .sumber-btn--table-danger:hover,
    .sumber-btn--table-danger:focus {
        color: #fff;
        background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%);
        border-color: #b93d38;
    }

    .sumber-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .sumber-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .sumber-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .sumber-modal__subtitle {
        max-width: 85%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .sumber-modal .modal-body {
        padding: 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .sumber-modal__footer {
        border-top: 0;
        padding: 0 1.5rem 1.5rem;
        background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%);
    }

    .sumber-form-section {
        margin-bottom: 1rem;
        padding: 1rem 1rem 0.2rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .sumber-form-section__title {
        margin-bottom: 0.9rem;
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2d6287;
    }

    .sumber-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .sumber-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .js-sumber-table thead th,
    .js-sumber-table tfoot th {
        white-space: nowrap;
    }

    .js-sumber-table tfoot th {
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
        .sumber-toolbar {
            margin-top: 1rem;
            justify-content: flex-start !important;
        }

        .sumber-btn {
            width: 100%;
        }

        .sumber-modal__subtitle {
            max-width: 100%;
        }
    }
</style>
