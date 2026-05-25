<?php
$status = (string) $this->session->flashdata('status');
$errorLog = (string) $this->session->flashdata('error_log');
$menuModuleOptions = (array) ($menuModuleOptions ?? []);
$userRows = (array) ($userRows ?? []);
$accessMatrix = (array) ($accessMatrix ?? []);
$generalPageModuleOptions = (array) ($generalPageModuleOptions ?? []);
$generalPageOptions = (array) ($generalPageOptions ?? []);
$generalActionOptions = ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL'];
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1 class="m-0 text-dark">Super Admin - User Access Matrix</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($status === 'sukses_edit' || $status === 'sukses_sync'): ?>
                <div class="alert alert-success">
                    Perubahan akses berhasil disimpan.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php elseif ($status === 'gagal_edit' || $status === 'gagal_sync'): ?>
                <div class="alert alert-danger">
                    Perubahan akses gagal diproses.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tablesReady['master_user_new']) || empty($tablesReady['master_user_child']) || empty($tablesReady['user_page_permission'])): ?>
                <div class="alert alert-warning">
                    Struktur tabel belum siap.
                    <ul class="mb-0 mt-2">
                        <?php if (empty($tablesReady['master_user_new'])): ?><li>`tb_master_user_new` belum ada.</li><?php endif; ?>
                        <?php if (empty($tablesReady['master_user_child'])): ?><li>`tb_master_user_child` belum ada.</li><?php endif; ?>
                        <?php if (empty($tablesReady['user_page_permission'])): ?><li>`tb_user_page_permission` belum ada. Jalankan `db/patch_user_page_permission_20260523.sql`.</li><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card card-warning">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-2 mb-md-0">User Access Matrix (Per User)</h3>
                    <div class="d-flex align-items-center">
                        <form method="post" action="<?= base_url('SuperAdmin_UserAccess/syncMyRepConfig') ?>" id="form_sync_myrep_access" class="mr-2 mb-0">
                            <button type="submit" class="btn btn-sm btn-info">Sync MyRep Config + City Mapping</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="btn_check_all_visible">Check Semua (Visible)</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_uncheck_all_visible">Uncheck Semua (Visible)</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border py-2 px-3 mb-3">
                        Sinkronisasi akan menyesuaikan akses modul dari data <strong>SuperAdmin_MyRep_Config</strong> (role VIEW aktif) dan <strong>SuperAdmin_MyRep_CityMapping</strong> (NIK PIC per role).
                        Modul <code>Kontrak</code>, <code>MyRepublik</code>, dan <code>Fiberstar</code> dikelola terpisah.
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Filter Status</label>
                            <select id="filter_user_status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                    </div>

                    <form method="post" action="<?= base_url('SuperAdmin_UserAccess/saveMatrixBulk') ?>" id="form_user_access_bulk">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="table_super_admin_user_access_matrix">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th style="min-width: 120px;">NIK</th>
                                        <th style="min-width: 220px;">Nama</th>
                                        <th style="min-width: 120px;">Status</th>
                                        <?php foreach ($menuModuleOptions as $moduleOption): ?>
                                            <?php $moduleKey = (string) $moduleOption; ?>
                                            <th class="text-center" style="min-width: 140px;" data-module-col="<?= htmlspecialchars($moduleKey, ENT_QUOTES) ?>">
                                                <div class="font-weight-bold"><?= htmlspecialchars($moduleKey) ?></div>
                                                <label class="mb-0 small mt-1 d-inline-flex align-items-center">
                                                    <input type="checkbox" class="mr-1 js-toggle-module-col" data-module="<?= htmlspecialchars($moduleKey, ENT_QUOTES) ?>">
                                                    all
                                                </label>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php foreach ($userRows as $row): ?>
                                        <?php $userId = (int) ($row['id'] ?? 0); ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nik'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['nama_karyawan'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['status_user'] ?? '-')) ?></td>
                                            <?php foreach ($menuModuleOptions as $moduleOption): ?>
                                                <?php
                                                $moduleKey = (string) $moduleOption;
                                                $isChecked = !empty($accessMatrix[$userId][$moduleKey]);
                                                ?>
                                                <td class="text-center align-middle">
                                                    <input
                                                        type="checkbox"
                                                        class="js-module-cell"
                                                        data-module="<?= htmlspecialchars($moduleKey, ENT_QUOTES) ?>"
                                                        name="matrix[<?= $userId ?>][<?= htmlspecialchars($moduleKey, ENT_QUOTES) ?>]"
                                                        value="1"
                                                        <?= $isChecked ? 'checked' : '' ?>>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="submit" class="btn btn-primary">Simpan Matrix Access</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-2 mb-md-0">User Detail Access (Per Page)</h3>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="btn_general_check_all">Check Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="btn_general_uncheck_all">Uncheck Semua</button>
                        <button type="button" class="btn btn-sm btn-primary" id="btn_general_save">Simpan Detail Access</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border py-2 px-3 mb-3">
                        Matrix ini untuk modul umum (di luar konfigurasi MyRep). Default akses mengikuti centang modul user.
                        Jika diubah di sini, sistem menyimpan override khusus per user + per page.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>User</label>
                            <select id="general_access_user_id" class="form-control form-control-sm">
                                <option value="">Pilih User</option>
                                <?php foreach ($userRows as $userRow): ?>
                                    <?php $uId = (int) ($userRow['id'] ?? 0); ?>
                                    <?php if ($uId <= 0) { continue; } ?>
                                    <option value="<?= $uId ?>">
                                        <?= htmlspecialchars((string) ($userRow['nama_karyawan'] ?? '-')) ?>
                                        [<?= htmlspecialchars((string) ($userRow['nik'] ?? '-')) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Filter Modul</label>
                            <select id="general_access_module_key" class="form-control form-control-sm">
                                <option value="">Semua Modul</option>
                                <?php foreach ($generalPageModuleOptions as $moduleKey): ?>
                                    <option value="<?= htmlspecialchars((string) $moduleKey, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $moduleKey) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Filter Halaman</label>
                            <select id="general_access_page_key" class="form-control form-control-sm">
                                <option value="">Semua Halaman</option>
                                <?php foreach ($generalPageOptions as $pageKey): ?>
                                    <option value="<?= htmlspecialchars((string) $pageKey, ENT_QUOTES) ?>"><?= htmlspecialchars((string) $pageKey) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 mb-2">
                        <button type="button" class="btn btn-sm btn-info" id="btn_general_load">Load Matrix</button>
                    </div>

                    <form id="form_general_page_access" method="post">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="table_general_page_access">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th style="min-width: 140px;">Modul</th>
                                        <th style="min-width: 220px;">Halaman</th>
                                        <?php foreach ($generalActionOptions as $actionKey): ?>
                                            <th class="text-center" style="min-width: 100px;"><?= htmlspecialchars((string) $actionKey) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="<?= 3 + count($generalActionOptions) ?>" class="text-center text-muted">
                                            Pilih user lalu klik "Load Matrix".
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function () {
        var matrixTable = null;
        var generalTable = null;
        var generalActionKeys = <?= json_encode(array_values($generalActionOptions)) ?>;
        var generalRows = [];
        var currentGeneralModuleFilter = '';
        var currentGeneralPageFilter = '';

        if ($.fn.DataTable) {
            matrixTable = $('#table_super_admin_user_access_matrix').DataTable({
                pageLength: 10,
                order: [[2, 'asc']],
                scrollX: true,
                autoWidth: false
            });
        }

        function initGeneralSelectSearch() {
            if (!$.fn.select2) {
                return false;
            }

            var $targets = $('#general_access_user_id, #general_access_module_key, #general_access_page_key');
            $targets.each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    return;
                }
                $el.select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });

            return true;
        }

        var select2Retry = 0;
        (function waitSelect2() {
            if (initGeneralSelectSearch()) {
                return;
            }
            if (select2Retry >= 30) {
                return;
            }
            select2Retry++;
            setTimeout(waitSelect2, 120);
        })();

        $('#filter_user_status').on('change', function () {
            if (!matrixTable) {
                return;
            }
            matrixTable.column(3).search(String($(this).val() || ''), false, false).draw();
        });

        function getVisibleRowNodes() {
            if (!matrixTable) {
                return $('#table_super_admin_user_access_matrix tbody tr');
            }
            return $(matrixTable.rows({ search: 'applied' }).nodes());
        }

        $('#btn_check_all_visible').on('click', function () {
            getVisibleRowNodes().find('.js-module-cell').prop('checked', true);
        });

        $('#btn_uncheck_all_visible').on('click', function () {
            getVisibleRowNodes().find('.js-module-cell').prop('checked', false);
        });

        $(document).on('change', '.js-toggle-module-col', function () {
            var moduleKey = String($(this).attr('data-module') || '');
            var isChecked = $(this).is(':checked');
            if (moduleKey === '') {
                return;
            }
            getVisibleRowNodes()
                .find('.js-module-cell[data-module="' + moduleKey + '"]')
                .prop('checked', isChecked);
        });

        $('#form_sync_myrep_access').on('submit', function (event) {
            event.preventDefault();
            var formEl = this;
            Swal.fire({
                title: 'Sinkronkan akses MyRep?',
                text: 'Akses MyRep di UserAccess akan disesuaikan dari MyRep Config + City Mapping.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, sinkronkan',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.value) {
                    return;
                }
                formEl.submit();
            });
        });

        $('#form_user_access_bulk').on('submit', function (event) {
            event.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var defaultBtnText = $submitBtn.text();

            $submitBtn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                dataType: 'json',
                data: $form.serialize()
            }).done(function (response) {
                if (!response || !response.status) {
                    Swal.fire('Gagal', (response && response.message) ? response.message : 'Gagal menyimpan matrix akses user.', 'error');
                    return;
                }

                Swal.fire('Success', response.message || 'Matrix akses berhasil disimpan.', 'success');
            }).fail(function () {
                Swal.fire('Error', 'Request simpan matrix akses gagal diproses.', 'error');
            }).always(function () {
                $submitBtn.prop('disabled', false).text(defaultBtnText);
            });
        });

        function getGeneralVisibleRows() {
            if (!generalTable) {
                return $('#table_general_page_access tbody tr');
            }
            return $(generalTable.rows({ search: 'applied' }).nodes());
        }

        function resetGeneralTableInstance() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#table_general_page_access')) {
                $('#table_general_page_access').DataTable().clear().destroy();
            }
            generalTable = null;
        }

        function renderGeneralMatrixRows(rows) {
            var html = '';
            var rowNo = 1;
            resetGeneralTableInstance();

            if (!Array.isArray(rows) || rows.length === 0) {
                html = '<tr><td colspan="' + (3 + generalActionKeys.length) + '" class="text-center text-muted">Data tidak ditemukan untuk filter ini.</td></tr>';
                $('#table_general_page_access tbody').html(html);
                return;
            }

            rows.forEach(function (row) {
                var moduleKey = String(row.module_key || '');
                var pageKey = String(row.page_key || '');
                var actions = Array.isArray(row.actions) ? row.actions : [];
                var state = row.state || {};
                html += '<tr>';
                html += '<td>' + (rowNo++) + '</td>';
                html += '<td>' + $('<div>').text(moduleKey).html() + '</td>';
                html += '<td>' + $('<div>').text(pageKey).html() + '</td>';

                generalActionKeys.forEach(function (actionKey) {
                    if (actions.indexOf(actionKey) === -1) {
                        html += '<td class="text-center text-muted">-</td>';
                        return;
                    }

                    var actionState = state[actionKey] || {};
                    var checked = Number(actionState.value || 0) === 1 ? ' checked' : '';
                    var source = String(actionState.source || 'default');
                    var badgeClass = source === 'override' ? 'badge-warning' : 'badge-light';
                    var badgeText = source === 'override' ? 'OVR' : 'DEF';
                    html += '<td class="text-center align-middle">';
                    html += '<div class="d-flex flex-column align-items-center">';
                    html += '<input type="checkbox" class="js-general-action-cell" name="matrix[' + pageKey + '][' + actionKey + ']" value="1"' + checked + '>';
                    html += '<span class="badge ' + badgeClass + ' mt-1" style="font-size:10px;">' + badgeText + '</span>';
                    html += '</div>';
                    html += '</td>';
                });

                html += '</tr>';
            });

            $('#table_general_page_access tbody').html(html);
            generalTable = $('#table_general_page_access').DataTable({
                destroy: true,
                pageLength: 10,
                order: [[1, 'asc'], [2, 'asc']],
                scrollX: true,
                autoWidth: false,
                searching: true
            });
        }

        function applyGeneralClientFilter(rows, moduleKey, pageKey) {
            var list = Array.isArray(rows) ? rows : [];
            var moduleFilter = String(moduleKey || '').trim().toLowerCase();
            var pageFilter = String(pageKey || '').trim().toLowerCase();

            return list.filter(function (row) {
                var rowModule = String(row && row.module_key ? row.module_key : '').trim().toLowerCase();
                var rowPage = String(row && row.page_key ? row.page_key : '').trim().toLowerCase();

                if (moduleFilter !== '' && rowModule !== moduleFilter) {
                    return false;
                }
                if (pageFilter !== '' && rowPage !== pageFilter) {
                    return false;
                }

                return true;
            });
        }

        function reloadGeneralPageOptions(moduleKey) {
            return $.ajax({
                url: '<?= base_url('SuperAdmin_UserAccess/getGeneralPageOptions') ?>',
                method: 'GET',
                dataType: 'json',
                data: { module_key: moduleKey || '' }
            }).done(function (response) {
                var options = (response && response.data && Array.isArray(response.data.page_options)) ? response.data.page_options : [];
                var html = '<option value="">Semua Halaman</option>';
                options.forEach(function (pageKey) {
                    var safeVal = $('<div>').text(String(pageKey)).html();
                    html += '<option value="' + safeVal + '">' + safeVal + '</option>';
                });
                $('#general_access_page_key').html(html);
                $('#general_access_page_key').val('');
                if ($.fn.select2 && $('#general_access_page_key').hasClass('select2-hidden-accessible')) {
                    $('#general_access_page_key').trigger('change.select2');
                }
            });
        }

        function getSelectedFilterValue($select) {
            if (!$select || !$select.length) {
                return '';
            }

            var val = $select.val();
            if (val !== null && val !== undefined && String(val).trim() !== '') {
                return String(val).trim();
            }

            var $selected = $select.find('option:selected');
            if ($selected.length) {
                var selectedVal = String($selected.attr('value') || '').trim();
                if (selectedVal !== '') {
                    return selectedVal;
                }
            }

            return '';
        }

        function normalizeFilterValue(value) {
            return String(value || '').replace(/\s+/g, ' ').trim();
        }

        function loadGeneralMatrix() {
            var userId = Number($('#general_access_user_id').val() || 0);
            if (userId <= 0) {
                Swal.fire('Info', 'Pilih user terlebih dahulu.', 'info');
                return;
            }

            var moduleKey = normalizeFilterValue(currentGeneralModuleFilter || getSelectedFilterValue($('#general_access_module_key')));
            var pageKey = normalizeFilterValue(currentGeneralPageFilter || getSelectedFilterValue($('#general_access_page_key')));

            $.ajax({
                url: '<?= base_url('SuperAdmin_UserAccess/getGeneralPageMatrix') ?>',
                method: 'GET',
                cache: false,
                dataType: 'json',
                data: {
                    id_user: userId,
                    module_key: moduleKey,
                    page_key: pageKey
                }
            }).done(function (response) {
                if (!response || !response.status) {
                    Swal.fire('Gagal', (response && response.message) ? response.message : 'Gagal load matrix detail access.', 'error');
                    return;
                }

                generalRows = (response.data && Array.isArray(response.data.rows)) ? response.data.rows : [];
                renderGeneralMatrixRows(applyGeneralClientFilter(generalRows, moduleKey, pageKey));
            }).fail(function () {
                Swal.fire('Error', 'Request detail access gagal diproses.', 'error');
            });
        }

        $('#general_access_module_key').on('change', function () {
            var moduleKey = normalizeFilterValue(getSelectedFilterValue($('#general_access_module_key')));
            currentGeneralModuleFilter = moduleKey;
            currentGeneralPageFilter = '';
            reloadGeneralPageOptions(moduleKey);
        });

        $('#general_access_page_key').on('change', function () {
            currentGeneralPageFilter = normalizeFilterValue(getSelectedFilterValue($('#general_access_page_key')));
        });

        $('#btn_general_load').on('click', function () {
            loadGeneralMatrix();
        });

        $('#btn_general_check_all').on('click', function () {
            getGeneralVisibleRows().find('.js-general-action-cell').prop('checked', true);
        });

        $('#btn_general_uncheck_all').on('click', function () {
            getGeneralVisibleRows().find('.js-general-action-cell').prop('checked', false);
        });

        $('#btn_general_save').on('click', function () {
            var userId = Number($('#general_access_user_id').val() || 0);
            if (userId <= 0) {
                Swal.fire('Info', 'Pilih user terlebih dahulu.', 'info');
                return;
            }

            if (!Array.isArray(generalRows) || generalRows.length === 0) {
                Swal.fire('Info', 'Load matrix detail access terlebih dahulu.', 'info');
                return;
            }

            var moduleKey = normalizeFilterValue(currentGeneralModuleFilter || getSelectedFilterValue($('#general_access_module_key')));
            var pageKey = normalizeFilterValue(currentGeneralPageFilter || getSelectedFilterValue($('#general_access_page_key')));
            var formData = $('#form_general_page_access').serializeArray();
            formData.push({ name: 'id_user', value: userId });
            formData.push({ name: 'module_key', value: moduleKey });
            formData.push({ name: 'page_key', value: pageKey });

            $.ajax({
                url: '<?= base_url('SuperAdmin_UserAccess/saveGeneralPageMatrix') ?>',
                method: 'POST',
                dataType: 'json',
                data: $.param(formData)
            }).done(function (response) {
                if (!response || !response.status) {
                    Swal.fire('Gagal', (response && response.message) ? response.message : 'Gagal menyimpan detail access.', 'error');
                    return;
                }

                Swal.fire('Success', response.message || 'Detail access tersimpan.', 'success');
                loadGeneralMatrix();
            }).fail(function () {
                Swal.fire('Error', 'Request simpan detail access gagal diproses.', 'error');
            });
        });
    });
</script>
