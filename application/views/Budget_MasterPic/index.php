<?php
$status = $this->session->flashdata('status');
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">Master PIC Budget</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm budget-card">
                <div class="card-header budget-card__header d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title mb-1">Master PIC Workbench</h3>
                    </div>
                    <span class="badge badge-light"><?= count($pics ?? []) ?> User Active</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap justify-content-md-end budget-toolbar">
                                <a href="<?= base_url('ListUser') ?>" class="btn budget-btn budget-btn--success">
                                    <i class="fas fa-users-cog mr-1"></i> Kelola User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar Master PIC</h3>
                    <span class="badge badge-light"><?= count($pics ?? []) ?> user aktif</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="masterPicTable" class="table table-bordered table-hover table-striped js-budget-table">
                            <thead class="bg-info">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Nama User</th>
                                    <th>Status Master User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pics)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada master PIC budget.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($pics as $pic): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($pic['nama_user'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= htmlspecialchars($pic['status_user'] ?? 'ACTIVE') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Nama User</th>
                                    <th>Status Master User</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function () {
        if ($.fn.DataTable) {
            $('#masterPicTable').DataTable({
                dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    'rt' +
                    '<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                paging: true,
                lengthChange: true,
                searching: true,
                info: true,
                ordering: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Semua']
                ],
                order: [[1, 'asc']],
                columnDefs: [
                    { targets: [0], orderable: false }
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    },
                    zeroRecords: 'Tidak ada data yang cocok'
                }
            });
        }
        <?php if (!empty($status)): ?>
        <?php if ($status === 'fitur_dipindahkan'): ?>
        Swal.fire('Info', 'Edit/hapus user dipindahkan ke menu List User dan hanya untuk Super Admin.', 'info');
        <?php endif; ?>
        <?php endif; ?>
    });
</script>

<style>
    .budget-toolbar {
        gap: 10px;
    }

    .budget-header-subtitle {
        margin: 0;
        font-size: 0.92rem;
        color: rgba(255, 255, 255, 0.84);
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

    .budget-note-card {
        padding: 1rem 1.1rem;
        border-radius: 18px;
        border: 1px solid #d9e7f3;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.96), rgba(236, 246, 253, 0.95));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .budget-note-card__eyebrow {
        margin-bottom: 0.35rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5b7d98;
    }

    .budget-note-card__title {
        margin-bottom: 0.4rem;
        font-size: 1.02rem;
        font-weight: 800;
        color: #163f5f;
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
