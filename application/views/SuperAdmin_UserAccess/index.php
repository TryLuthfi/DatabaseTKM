<?php
$status = (string) $this->session->flashdata('status');
$errorLog = (string) $this->session->flashdata('error_log');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1 class="m-0 text-dark">Super Admin - User Access Matrix MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($status === 'sukses_edit'): ?>
                <div class="alert alert-success">
                    Perubahan akses berhasil disimpan.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php elseif ($status === 'gagal_edit'): ?>
                <div class="alert alert-danger">
                    Perubahan akses gagal diproses.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tablesReady['master_user_new']) || empty($tablesReady['myrep_role_permission'])): ?>
                <div class="alert alert-warning">
                    Struktur tabel belum siap penuh.
                    <ul class="mb-0 mt-2">
                        <?php if (empty($tablesReady['master_user_new'])): ?><li>`tb_master_user_new` belum ada.</li><?php endif; ?>
                        <?php if (empty($tablesReady['myrep_role_permission'])): ?><li>`tb_myrep_role_permission` belum ada.</li><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (empty($tablesReady['myrep_user_permission'])): ?>
                <div class="alert alert-info">
                    Tabel override user belum ada: `tb_myrep_user_permission`.
                    Jalankan patch SQL dulu agar override per-user bisa disimpan.
                </div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar User & Status Override Akses</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Filter Status</label>
                            <select id="filter_user_status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Filter Override</label>
                            <select id="filter_user_override" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                <option value="CUSTOM">CUSTOM</option>
                                <option value="DEFAULT">DEFAULT</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="table_super_admin_user_access">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Override</th>
                                    <th style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ((array) $userRows as $row): ?>
                                    <?php $customRulesCount = (int) ($row['custom_rules_count'] ?? 0); ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars((string) ($row['nik'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['nama_karyawan'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['username_user'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['nama_level'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['status_user'] ?? '-')) ?></td>
                                        <td>
                                            <?php if ($customRulesCount > 0): ?>
                                                <span class="badge badge-warning">CUSTOM (<?= $customRulesCount ?>)</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">DEFAULT</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-warning btn-sm js-open-access-matrix"
                                                data-id="<?= (int) ($row['id'] ?? 0) ?>"
                                                data-name="<?= htmlspecialchars((string) ($row['nama_karyawan'] ?? '-'), ENT_QUOTES) ?>"
                                                data-nik="<?= htmlspecialchars((string) ($row['nik'] ?? '-'), ENT_QUOTES) ?>">
                                                Atur Akses
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal_user_access_matrix" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('SuperAdmin_UserAccess/saveMatrix') ?>" id="form_user_access_matrix">
                <div class="modal-header">
                    <h5 class="modal-title">User Access Matrix</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_master_user" id="matrix_user_id">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong id="matrix_user_identity">-</strong>
                            <div class="small text-muted" id="matrix_user_role_info">Role key: -</div>
                            <div class="small text-muted" id="matrix_mode_info">Mode: role default</div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn_matrix_check_all">Check All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_matrix_uncheck_all">Uncheck All</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btn_matrix_reset_default">Reset Default</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table_matrix_detail">
                            <thead>
                                <tr>
                                    <th style="min-width: 220px;">Page</th>
                                    <?php foreach ((array) $actionOptions as $actionOption): ?>
                                        <th class="text-center" style="min-width: 120px;"><?= htmlspecialchars((string) $actionOption) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ((array) $pageOptions as $pageOption): ?>
                                    <tr data-page="<?= htmlspecialchars((string) $pageOption, ENT_QUOTES) ?>">
                                        <td><strong><?= htmlspecialchars((string) $pageOption) ?></strong></td>
                                        <?php foreach ((array) $actionOptions as $actionOption): ?>
                                            <?php
                                            $pageKey = (string) $pageOption;
                                            $actionKey = strtoupper((string) $actionOption);
                                            $fieldName = 'user_matrix[' . $pageKey . '][' . $actionKey . ']';
                                            ?>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    class="js-matrix-checkbox"
                                                    name="<?= htmlspecialchars($fieldName) ?>"
                                                    data-page="<?= htmlspecialchars($pageKey, ENT_QUOTES) ?>"
                                                    data-action="<?= htmlspecialchars($actionKey, ENT_QUOTES) ?>"
                                                    value="1">
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Matrix</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        var matrixTable = null;
        var activeUserId = 0;
        var matrixUrlBase = '<?= base_url('SuperAdmin_UserAccess/getUserMatrix/') ?>';
        var resetUrlBase = '<?= base_url('SuperAdmin_UserAccess/resetMatrix/') ?>';

        if ($.fn.DataTable) {
            matrixTable = $('#table_super_admin_user_access').DataTable({
                pageLength: 10,
                order: [[2, 'asc']],
                scrollX: true,
                autoWidth: false
            });
        }

        $('#filter_user_status').on('change', function () {
            if (!matrixTable) {
                return;
            }
            matrixTable.column(5).search(String($(this).val() || ''), false, false).draw();
        });

        $('#filter_user_override').on('change', function () {
            if (!matrixTable) {
                return;
            }
            var val = String($(this).val() || '');
            matrixTable.column(6).search(val, false, false).draw();
        });

        function setMatrixLoading(isLoading) {
            $('.js-matrix-checkbox, #btn_matrix_check_all, #btn_matrix_uncheck_all, #btn_matrix_reset_default, #form_user_access_matrix button[type="submit"]')
                .prop('disabled', !!isLoading);
            if (isLoading) {
                $('#matrix_mode_info').text('Mode: memuat data...');
            }
        }

        function applyMatrixData(matrix) {
            $('.js-matrix-checkbox').each(function () {
                var pageKey = String($(this).data('page') || '');
                var actionKey = String($(this).data('action') || '');
                var checked = !!(matrix[pageKey] && matrix[pageKey][actionKey] && parseInt(matrix[pageKey][actionKey], 10) === 1);
                $(this).prop('checked', checked);
            });
        }

        $(document).on('click', '.js-open-access-matrix', function () {
            activeUserId = parseInt($(this).attr('data-id') || '0', 10);
            var userName = String($(this).attr('data-name') || '-');
            var userNik = String($(this).attr('data-nik') || '-');

            $('#matrix_user_id').val(activeUserId);
            $('#matrix_user_identity').text(userName + ' [' + userNik + ']');
            $('#matrix_user_role_info').text('Role key: -');
            $('#matrix_mode_info').text('Mode: role default');
            $('.js-matrix-checkbox').prop('checked', false);
            $('#modal_user_access_matrix').modal('show');

            if (activeUserId <= 0) {
                return;
            }

            setMatrixLoading(true);
            $.ajax({
                url: matrixUrlBase + activeUserId,
                method: 'GET',
                dataType: 'json'
            }).done(function (resp) {
                if (!resp || !resp.status) {
                    Swal.fire('Gagal', resp && resp.message ? resp.message : 'Gagal mengambil matrix user.', 'error');
                    return;
                }

                var data = resp.data || {};
                applyMatrixData(data.matrix || {});

                var roleKeys = Array.isArray(data.role_keys) ? data.role_keys : [];
                $('#matrix_user_role_info').text('Role key: ' + (roleKeys.length ? roleKeys.join(', ') : '-'));
                $('#matrix_mode_info').text(data.has_custom ? 'Mode: custom override aktif' : 'Mode: role default');
            }).fail(function () {
                Swal.fire('Gagal', 'Tidak bisa mengambil matrix user.', 'error');
            }).always(function () {
                setMatrixLoading(false);
            });
        });

        $('#btn_matrix_check_all').on('click', function () {
            $('.js-matrix-checkbox').prop('checked', true);
        });

        $('#btn_matrix_uncheck_all').on('click', function () {
            $('.js-matrix-checkbox').prop('checked', false);
        });

        $('#btn_matrix_reset_default').on('click', function () {
            if (activeUserId <= 0) {
                return;
            }

            Swal.fire({
                title: 'Reset ke role default?',
                text: 'Custom override user akan dihapus.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, reset',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.value) {
                    return;
                }

                setMatrixLoading(true);
                $.ajax({
                    url: resetUrlBase + activeUserId,
                    method: 'POST',
                    dataType: 'json'
                }).done(function (resp) {
                    if (!resp || !resp.status) {
                        Swal.fire('Gagal', resp && resp.message ? resp.message : 'Gagal reset override.', 'error');
                        return;
                    }
                    $('#modal_user_access_matrix').modal('hide');
                    window.location.reload();
                }).fail(function () {
                    Swal.fire('Gagal', 'Tidak bisa reset override user.', 'error');
                }).always(function () {
                    setMatrixLoading(false);
                });
            });
        });
    });
</script>

