<?php
$status = $this->session->flashdata('status');
$validationErrors = (array) $this->session->flashdata('validation_errors');
$totalUsers = count($rincian_user ?? []);
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
$homebaseOptions = [];
$levelOptions = [];
foreach (($rincian_user ?? []) as $userRow) {
    $homebase = trim((string) ($userRow['homebase'] ?? ''));
    $level = trim((string) ($userRow['nama_level'] ?? ''));
    if ($homebase !== '') {
        $homebaseOptions[strtoupper($homebase)] = $homebase;
    }
    if ($level !== '') {
        $levelOptions[strtoupper($level)] = $level;
    }
}
ksort($homebaseOptions);
ksort($levelOptions);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark"><?= htmlspecialchars((string) ($judul ?? 'List User TKM'), ENT_QUOTES) ?></h1>
                    <p class="text-muted mb-0">Kelola data pengguna sesuai struktur database terbaru.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm user-card mb-3">
                <div class="card-header user-card__header">
                    <h3 class="card-title mb-0">Filter List User</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="user-field-label" for="user_filter_homebase">Homebase</label>
                            <select class="form-control user-input" id="user_filter_homebase">
                                <option value="">Semua Homebase</option>
                                <?php foreach ($homebaseOptions as $homebaseOption): ?>
                                    <option value="<?= htmlspecialchars((string) $homebaseOption, ENT_QUOTES) ?>">
                                        <?= htmlspecialchars((string) $homebaseOption, ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="user-field-label" for="user_filter_level">Level</label>
                            <select class="form-control user-input" id="user_filter_level">
                                <option value="">Semua Level</option>
                                <?php foreach ($levelOptions as $levelOption): ?>
                                    <option value="<?= htmlspecialchars((string) $levelOption, ENT_QUOTES) ?>">
                                        <?= htmlspecialchars((string) $levelOption, ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap justify-content-md-end user-toolbar">
                                <button type="button" class="btn user-btn user-btn--ghost" id="user_reset_search">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </button>
                                <a href="<?= base_url('ListUser/downloadReport') ?>" class="btn user-btn user-btn--primary" id="user_download_report">
                                    <i class="fas fa-file-excel mr-1"></i> Download Report
                                </a>
                                <button type="button" class="btn user-btn user-btn--success" id="btnTambahUser">
                                    <i class="fas fa-plus-circle mr-1"></i> Tambah <?= $isSuperAdmin ? 'User' : 'NIK + Nama' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm user-card">
                <div class="card-header user-card__header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Daftar User</h3>
                    <span class="badge badge-light"><?= number_format((float) $totalUsers, 0, ',', '.') ?> user</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="listUserTable" class="table table-bordered table-hover table-striped js-user-table">
                            <thead class="bg-info">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Level</th>
                                    <th>Jabatan</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Homebase</th>
                                    <th>Divisi</th>
                                    <th>Departemen</th>
                                    <th>Status Login</th>
                                    <th>Status User</th>
                                    <th style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rincian_user)): ?>
                                    <tr>
                                        <td colspan="13" class="text-center text-muted">Belum ada data user.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($rincian_user as $data): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars((string) ($data['nik'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['nama_user'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['username_user'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['nama_level'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['nama_jabatan'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['jenis_kelamin'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['homebase'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['divisi'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= htmlspecialchars((string) ($data['departemen'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td>
                                                <?php
                                                $statusLogin = strtoupper(trim((string) ($data['status_login'] ?? 'NY LOGIN')));
                                                $statusLoginBadge = $statusLogin === 'DONE' ? 'success' : 'warning';
                                                ?>
                                                <span class="badge badge-<?= $statusLoginBadge ?>">
                                                    <?= htmlspecialchars($statusLogin, ENT_QUOTES) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($data['status_user'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td>
                                                <?php if ($isSuperAdmin): ?>
                                                    <div class="user-action-inline">
                                                        <button type="button"
                                                            class="btn btn-sm user-btn user-btn--table-primary js-open-user-modal"
                                                            data-id="<?= (int) ($data['id_user'] ?? 0) ?>"
                                                            data-nik="<?= htmlspecialchars((string) ($data['nik'] ?? ''), ENT_QUOTES) ?>"
                                                            data-nama="<?= htmlspecialchars((string) ($data['nama_user'] ?? ''), ENT_QUOTES) ?>"
                                                            data-username="<?= htmlspecialchars((string) ($data['username_user'] ?? ''), ENT_QUOTES) ?>"
                                                            data-id-level="<?= (int) ($data['id_level'] ?? 3) ?>"
                                                            data-jabatan-name="<?= htmlspecialchars((string) ($data['nama_jabatan'] ?? ''), ENT_QUOTES) ?>"
                                                            data-jenis-kelamin="<?= htmlspecialchars((string) ($data['jenis_kelamin'] ?? ''), ENT_QUOTES) ?>"
                                                            data-divisi="<?= htmlspecialchars((string) ($data['divisi'] ?? ''), ENT_QUOTES) ?>"
                                                            data-departemen="<?= htmlspecialchars((string) ($data['departemen'] ?? ''), ENT_QUOTES) ?>"
                                                            data-status="<?= htmlspecialchars((string) ($data['status_user'] ?? 'ACTIVE'), ENT_QUOTES) ?>">
                                                            <i class="fas fa-pen mr-1"></i> Edit
                                                        </button>
                                                        <a href="<?= site_url('ListUser/delete/' . (int) ($data['id_user'] ?? 0)) ?>"
                                                            class="btn btn-sm user-btn user-btn--table-danger js-delete-user"
                                                            data-user="<?= htmlspecialchars((string) ($data['nama_user'] ?? 'user ini'), ENT_QUOTES) ?>">
                                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Tidak tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="listUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content user-modal">
            <form method="post" action="<?= base_url('ListUser/add') ?>" id="listUserForm">
                <div class="modal-header user-modal__header">
                    <div>
                        <span class="user-modal__eyebrow">List User</span>
                        <h5 class="modal-title mb-1" id="listUserModalTitle">Tambah User</h5>
                        <p class="mb-0 user-modal__subtitle" id="listUserModalSubtitle">
                            <?= $isSuperAdmin ? 'Lengkapi data user sesuai master terbaru.' : 'Isi NIK dan Nama. Data lain akan mengikuti default sistem.' ?>
                        </p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="user-form-section">
                        <div class="user-form-section__title">Informasi User</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">NIK</label>
                                    <input type="text" class="form-control user-input" name="nik" id="nik" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Nama User</label>
                                    <input type="text" class="form-control user-input" name="nama_user" id="nama_user" autocomplete="off" required>
                                </div>
                            </div>
                            <?php if ($isSuperAdmin): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Username</label>
                                    <input type="text" class="form-control user-input" name="username_user" id="username_user" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6" id="passwordFieldGroup">
                                <div class="form-group">
                                    <label class="user-field-label">Password</label>
                                    <input type="text" class="form-control user-input" name="password_user" id="password_user" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control user-input">
                                        <option value="">- Pilih -</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Status User</label>
                                    <select name="status_user" id="status_user" class="form-control user-input" required>
                                        <option value="ACTIVE">ACTIVE</option>
                                        <option value="INACTIVE">INACTIVE</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($isSuperAdmin): ?>
                    <div class="user-form-section mb-0">
                        <div class="user-form-section__title">Hak Akses & Struktur</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Level</label>
                                    <select name="id_level" id="id_level" class="form-control user-input" required>
                                        <?php foreach ($rincian_level as $level): ?>
                                            <option value="<?= (int) ($level['id_level'] ?? 0) ?>">
                                                <?= htmlspecialchars((string) ($level['nama_level'] ?? '-'), ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="user-field-label">Jabatan</label>
                                    <select name="jabatan_name" id="jabatan_name" class="form-control user-input" required>
                                        <?php foreach ($rincian_jabatan as $jabatan): ?>
                                            <option value="<?= htmlspecialchars((string) ($jabatan['nama_jabatan'] ?? ''), ENT_QUOTES) ?>">
                                                <?= htmlspecialchars((string) ($jabatan['nama_jabatan'] ?? '-'), ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-md-0">
                                    <label class="user-field-label">Divisi</label>
                                    <input type="text" class="form-control user-input" name="divisi" id="divisi" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="user-field-label">Departemen</label>
                                    <input type="text" class="form-control user-input" name="departemen" id="departemen" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer user-modal__footer">
                    <button type="button" class="btn user-btn user-btn--ghost" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn user-btn user-btn--primary" id="listUserSubmitBtn">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var isSuperAdmin = <?= $isSuperAdmin ? 'true' : 'false' ?>;

        function openListUserModal(data) {
            var isEdit = isSuperAdmin && !!(data && data.id);
            var form = document.getElementById('listUserForm');

            document.getElementById('listUserModalTitle').textContent = isEdit ? 'Edit User' : 'Tambah User';
            document.getElementById('listUserModalSubtitle').textContent = isEdit
                ? 'Perbarui data user, lalu simpan perubahan jika sudah sesuai.'
                : (isSuperAdmin ? 'Lengkapi data user sesuai master terbaru.' : 'Isi NIK dan Nama. Data lain akan mengikuti default sistem.');
            document.getElementById('listUserSubmitBtn').innerHTML = isEdit
                ? '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                : '<i class="fas fa-save mr-1"></i> Simpan';

            form.setAttribute('action', isEdit ? '<?= base_url('ListUser/edit/') ?>' + data.id : '<?= base_url('ListUser/add') ?>');

            document.getElementById('nik').value = isEdit ? (data.nik || '') : '';
            document.getElementById('nama_user').value = isEdit ? (data.nama || '') : '';

            if (isSuperAdmin) {
                var usernameInput = document.getElementById('username_user');
                var passwordInput = document.getElementById('password_user');
                var passwordFieldGroup = document.getElementById('passwordFieldGroup');
                var levelSelect = document.getElementById('id_level');
                var genderSelect = document.getElementById('jenis_kelamin');
                var divisionInput = document.getElementById('divisi');
                var departementInput = document.getElementById('departemen');
                var statusSelect = document.getElementById('status_user');
                var jabatanSelect = document.getElementById('jabatan_name');

                if (usernameInput) {
                    usernameInput.value = isEdit ? (data.username || '') : '';
                }
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.required = !isEdit;
                    passwordInput.disabled = isEdit;
                }
                if (passwordFieldGroup) {
                    passwordFieldGroup.classList.toggle('d-none', isEdit);
                }
                if (levelSelect) {
                    levelSelect.value = isEdit ? (data.idLevel || '') : (levelSelect.options[0] ? levelSelect.options[0].value : '');
                }
                if (genderSelect) {
                    genderSelect.value = isEdit ? (data.jenisKelamin || '') : '';
                }
                if (divisionInput) {
                    divisionInput.value = isEdit ? (data.divisi || '') : '';
                }
                if (departementInput) {
                    departementInput.value = isEdit ? (data.departemen || '') : '';
                }
                if (statusSelect) {
                    statusSelect.value = isEdit ? (data.status || 'ACTIVE') : 'ACTIVE';
                }

                if (jabatanSelect) {
                    if (isEdit && data.jabatanName) {
                        var selected = jabatanSelect.options[0] ? jabatanSelect.options[0].value : '';
                        for (var i = 0; i < jabatanSelect.options.length; i++) {
                            if (String(jabatanSelect.options[i].value).toLowerCase() === String(data.jabatanName).toLowerCase()) {
                                selected = jabatanSelect.options[i].value;
                                break;
                            }
                        }
                        jabatanSelect.value = selected;
                    } else {
                        jabatanSelect.value = jabatanSelect.options[0] ? jabatanSelect.options[0].value : '';
                    }
                }
            }

            $('#listUserModal').modal('show');
        }

        $(function () {
            var table = null;
            if ($.fn.DataTable) {
                table = $('#listUserTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: false,
                    autoWidth: false,
                    scrollX: true,
                    dom: 'lrtip',
                    pageLength: 10,
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        paginate: { previous: 'Prev', next: 'Next' },
                        zeroRecords: 'Tidak ada data yang cocok'
                    }
                });
            }

            $('#btnTambahUser').on('click', function () { openListUserModal(null); });
            $(document).on('click', '.js-open-user-modal', function () {
                if (!isSuperAdmin) {
                    return;
                }

                openListUserModal({
                    id: $(this).data('id'),
                    nik: $(this).data('nik'),
                    nama: $(this).data('nama'),
                    username: $(this).data('username'),
                    idLevel: $(this).data('id-level'),
                    jabatanName: $(this).data('jabatan-name'),
                    jenisKelamin: $(this).data('jenis-kelamin'),
                    divisi: $(this).data('divisi'),
                    departemen: $(this).data('departemen'),
                    status: $(this).data('status')
                });
            });

            function applyUserTableFilters() {
                if (!table) {
                    return;
                }

                var homebase = $('#user_filter_homebase').val() || '';
                var level = $('#user_filter_level').val() || '';
                var escapeRegex = $.fn.dataTable.util.escapeRegex;

                table
                    .column(7).search(homebase ? '^' + escapeRegex(homebase) + '$' : '', true, false)
                    .column(4).search(level ? '^' + escapeRegex(level) + '$' : '', true, false)
                    .draw();
            }

            $('#user_filter_homebase, #user_filter_level').on('change', applyUserTableFilters);
            $('#user_reset_search').on('click', function () {
                $('#user_filter_homebase').val('');
                $('#user_filter_level').val('');
                if (table) {
                    table.search('');
                    table.columns().search('');
                    table.draw();
                }
            });
            $('#user_download_report').on('click', function (e) {
                e.preventDefault();
                var params = $.param({
                    homebase: $('#user_filter_homebase').val() || '',
                    level: $('#user_filter_level').val() || ''
                });
                var url = $(this).attr('href');
                if (params !== 'homebase=&level=') {
                    url += '?' + params;
                }
                window.location.href = url;
            });

            $(document).on('click', '.js-delete-user', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var user = $(this).data('user') || 'user ini';
                Swal.fire({
                    title: 'Hapus user?',
                    text: 'Data "' + user + '" akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d9534f',
                    cancelButtonColor: '#9aa9b8',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then(function (result) { if (result.value || result.isConfirmed) { window.location.href = href; } });
            });

            <?php if (!empty($status)): ?>
            if (!window.__listUserStatusShown) {
                window.__listUserStatusShown = true;
                <?php if ($status === 'sukses_tambah'): ?>
                Swal.fire('Success', 'User berhasil ditambahkan.', 'success');
                <?php elseif ($status === 'sukses_edit'): ?>
                Swal.fire('Success', 'User berhasil diperbarui.', 'success');
                <?php elseif ($status === 'sukses_hapus'): ?>
                Swal.fire('Success', 'User berhasil dihapus.', 'success');
                <?php elseif ($status === 'akses_ditolak'): ?>
                Swal.fire('Akses ditolak', 'Hanya Super Admin yang dapat edit atau hapus user.', 'warning');
                <?php elseif ($status === 'gagal_tambah' || $status === 'gagal_edit' || $status === 'gagal_hapus'): ?>
                Swal.fire('Gagal', 'Proses user gagal dilakukan.', 'error');
                <?php endif; ?>
            }
            <?php endif; ?>

            <?php if (!empty($validationErrors)): ?>
            if (!window.__listUserValidationShown) {
                window.__listUserValidationShown = true;
                Swal.fire({
                    title: 'Validasi gagal',
                    html: '<?= htmlspecialchars(implode("<br>", $validationErrors), ENT_QUOTES) ?>',
                    icon: 'error'
                });
            }
            <?php endif; ?>
        });
    })();
</script>

<style>
    .user-toolbar { gap: 10px; }
    .user-field-label { display: inline-block; margin-bottom: 8px; font-size: 0.84rem; font-weight: 700; letter-spacing: 0.04em; color: #48657f; text-transform: uppercase; }
    .user-card { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 42px rgba(14, 41, 64, 0.08); background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%); }
    .user-card__header { background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%), linear-gradient(135deg, #103b5a, #1f6da1 55%, #53a9d8); color: #fff; }
    .user-card .card-title { font-weight: 700; }
    .user-btn { border: 0; border-radius: 12px; padding: 0.68rem 1.15rem; font-weight: 700; letter-spacing: 0.01em; transition: all 0.2s ease; box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12); }
    .user-btn:hover, .user-btn:focus { transform: translateY(-1px); box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16); }
    .user-btn--primary { background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%); color: #fff; }
    .user-btn--success { background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%); color: #fff; }
    .user-btn--ghost { background: #fff; color: #315d7f; border: 1px solid #d7e6f2; box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12); }
    .user-btn--table-primary, .user-btn--table-danger { padding: 0.52rem 0.9rem; box-shadow: none; }
    .user-btn--table-primary { background: linear-gradient(135deg, #eaf4fb 0%, #d8ecfa 100%); color: #1d5f8d; border: 1px solid #c9e1f3; }
    .user-btn--table-danger { background: linear-gradient(135deg, #fff1f0 0%, #ffdedd 100%); color: #b93d38; border: 1px solid #f5c8c5; }
    .user-action-inline { display: inline-flex; align-items: center; gap: 8px; }
    .user-btn--table-danger:hover, .user-btn--table-danger:focus { color: #fff; background: linear-gradient(135deg, #d9534f 0%, #b93d38 100%); border-color: #b93d38; }
    .user-modal { border: 0; border-radius: 24px; overflow: hidden; box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22); }
    .user-modal__header { border-bottom: 0; padding: 1.4rem 1.5rem 1.1rem; background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%), linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%); color: #fff; }
    .user-modal__eyebrow { display: inline-block; margin-bottom: 6px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255, 255, 255, 0.76); }
    .user-modal__subtitle { max-width: 85%; color: rgba(255, 255, 255, 0.84); font-size: 0.92rem; }
    .user-modal .modal-body { padding: 1.5rem; background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%); }
    .user-modal__footer { border-top: 0; padding: 0 1.5rem 1.5rem; background: linear-gradient(180deg, #fbfdff 0%, #f2f8fc 100%); }
    .user-form-section { margin-bottom: 1rem; padding: 1rem 1rem 0.2rem; border: 1px solid #dbe9f4; border-radius: 18px; background: rgba(255, 255, 255, 0.92); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7); }
    .user-form-section__title { margin-bottom: 0.9rem; font-size: 0.86rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #2d6287; }
    .user-input { border-radius: 12px; border: 1px solid #cfe0ee; min-height: 44px; box-shadow: none; }
    .user-input:focus { border-color: #55a7d5; box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18); }
    .js-user-table thead th { white-space: nowrap; }
    .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select { border-radius: 10px; border: 1px solid #cfe0ee; box-shadow: none; }
    @media (max-width: 767.98px) {
        .user-toolbar { margin-top: 1rem; justify-content: flex-start !important; }
        .user-btn { width: 100%; }
        .user-modal__subtitle { max-width: 100%; }
    }
</style>
