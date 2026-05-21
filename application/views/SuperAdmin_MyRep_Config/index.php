<?php
$status = (string) $this->session->flashdata('status');
$errorLog = (string) $this->session->flashdata('error_log');
$isAreaRole = static function ($roleKey) {
    return substr((string) $roleKey, -5) === '_AREA';
};
$isHoRole = static function ($roleKey) {
    return substr((string) $roleKey, -3) === '_HO';
};
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Super Admin - MyRep Config</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($status === 'sukses_tambah' || $status === 'sukses_edit' || $status === 'sukses_hapus'): ?>
                <div class="alert alert-success">Perubahan berhasil disimpan.</div>
            <?php elseif ($status === 'gagal_tambah' || $status === 'gagal_edit' || $status === 'gagal_hapus'): ?>
                <div class="alert alert-danger">
                    Perubahan gagal diproses.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tablesReady['role_permission']) || empty($tablesReady['notification_route'])): ?>
                <div class="alert alert-warning">
                    Tabel MyRep Config belum lengkap.
                    <ul class="mb-0 mt-2">
                        <?php if (empty($tablesReady['role_permission'])): ?><li>`tb_myrep_role_permission` belum ada.</li><?php endif; ?>
                        <?php if (empty($tablesReady['notification_route'])): ?><li>`tb_myrep_notification_route` belum ada.</li><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Access Matrix MyRep (Per Page)</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2 text-muted small">Centang sesuai akses role per page: <strong>VIEW / TAMBAH / EDIT / HAPUS / APPROVAL</strong> dan action khusus lain jika ada.</div>
                    <div class="mb-2 small">
                        <span class="badge mr-2" style="background:#e8f5e9; color:#1b5e20;">AREA</span>
                        <span class="badge" style="background:#e3f2fd; color:#0d47a1;">HO</span>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Filter Page</label>
                            <select id="filter_matrix_page" class="form-control form-control-sm">
                                <option value="">Semua Page</option>
                                <?php foreach ($pageOptions as $pageOption): ?>
                                    <option value="<?= htmlspecialchars((string) $pageOption) ?>"><?= htmlspecialchars((string) $pageOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <form method="post" action="<?= base_url('SuperAdmin_MyRep_Config/saveAccessMatrix') ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th style="min-width:210px;">Page</th>
                                        <th style="min-width:160px;">Action</th>
                                        <?php foreach ($roleOptions as $roleOption): ?>
                                            <?php
                                            $roleClass = '';
                                            if ($isAreaRole($roleOption)) {
                                                $roleClass = 'myrep-role-area';
                                            } elseif ($isHoRole($roleOption)) {
                                                $roleClass = 'myrep-role-ho';
                                            }
                                            ?>
                                            <th class="text-center <?= $roleClass ?>" style="min-width:90px;"><?= htmlspecialchars((string) $roleOption) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <?php foreach ($pageOptions as $pageOption): ?>
                                    <tbody class="js-page-group" data-page="<?= htmlspecialchars((string) $pageOption, ENT_QUOTES) ?>">
                                        <?php foreach ($actionOptions as $actionIndex => $actionOption): ?>
                                            <tr>
                                                <?php if ($actionIndex === 0): ?>
                                                    <td rowspan="<?= count($actionOptions) ?>" class="align-middle"><strong><?= htmlspecialchars((string) $pageOption) ?></strong></td>
                                                <?php endif; ?>
                                                <td><strong><?= htmlspecialchars((string) $actionOption) ?></strong></td>
                                                <?php foreach ($roleOptions as $roleOption): ?>
                                                    <?php
                                                    $matrixPage = (string) $pageOption;
                                                    $matrixAction = strtoupper((string) $actionOption);
                                                    $matrixRole = (string) $roleOption;
                                                    $isChecked = !empty($accessMatrix[$matrixPage][$matrixAction][$matrixRole]);
                                                    $fieldName = 'access_matrix[' . $matrixPage . '][' . $matrixAction . '][' . $matrixRole . ']';
                                                    $roleCellClass = '';
                                                    if ($isAreaRole($roleOption)) {
                                                        $roleCellClass = 'myrep-role-area-cell';
                                                    } elseif ($isHoRole($roleOption)) {
                                                        $roleCellClass = 'myrep-role-ho-cell';
                                                    }
                                                    ?>
                                                    <td class="text-center <?= $roleCellClass ?>">
                                                        <input type="checkbox" name="<?= htmlspecialchars($fieldName) ?>" value="1" <?= $isChecked ? 'checked' : '' ?>>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">Simpan Access Matrix</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Mapping Notification Route MyRep</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2 text-muted small">Atur tujuan notifikasi per <strong>module + event</strong>. `FIXED_USER` wajib pilih user, `CITY_ROLE/CLUSTER_PIC` wajib pilih role.</div>
                    <div class="mb-2 small">
                        <span class="badge mr-1" style="background:#fff3cd;color:#7c5400;">FIXED_USER</span>
                        <span class="badge mr-1" style="background:#d1ecf1;color:#0c5460;">CITY_ROLE</span>
                        <span class="badge" style="background:#f8d7da;color:#721c24;">CLUSTER_PIC</span>
                    </div>

                    <form method="post" action="<?= base_url('SuperAdmin_MyRep_Config/saveNotificationRoute') ?>" class="mb-3" id="form_create_notification_route">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Module</label>
                                <select name="module_name" class="form-control" required>
                                    <option value="">Pilih Module</option>
                                    <?php foreach ($moduleOptions as $option): ?>
                                        <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Event</label>
                                <select name="event_name" class="form-control" required>
                                    <option value="">Pilih Event</option>
                                    <?php foreach ($eventOptions as $option): ?>
                                        <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Target Type</label>
                                <select name="target_type" class="form-control" required id="create_target_type">
                                    <?php foreach ($targetTypeOptions as $option): ?>
                                        <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Target Role</label>
                                <select name="target_role" class="form-control" id="create_target_role">
                                    <option value="">-</option>
                                    <?php foreach ($roleOptions as $option): ?>
                                        <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Target User (FIXED_USER)</label>
                                <select name="target_user_id" class="form-control" id="create_target_user">
                                    <option value="0">-</option>
                                    <?php foreach ($userOptions as $user): ?>
                                        <option value="<?= (int) ($user['id'] ?? 0) ?>">
                                            <?= htmlspecialchars((string) ($user['nama_karyawan'] ?? '-')) ?> [<?= htmlspecialchars((string) ($user['nik'] ?? '-')) ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label>Active</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">1</option>
                                    <option value="0">0</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">Simpan Route</button>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Filter Module</label>
                            <select id="filter_notif_module" class="form-control form-control-sm">
                                <option value="">Semua Module</option>
                                <?php foreach ($moduleOptions as $option): ?>
                                    <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Filter Event</label>
                            <select id="filter_notif_event" class="form-control form-control-sm">
                                <option value="">Semua Event</option>
                                <?php foreach ($eventOptions as $option): ?>
                                    <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Filter Target Type</label>
                            <select id="filter_notif_type" class="form-control form-control-sm">
                                <option value="">Semua Type</option>
                                <?php foreach ($targetTypeOptions as $option): ?>
                                    <option value="<?= htmlspecialchars((string) $option) ?>"><?= htmlspecialchars((string) $option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Filter Active</label>
                            <select id="filter_notif_active" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="table_myrep_notification">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Module</th>
                                    <th>Event</th>
                                    <th>Target Type</th>
                                    <th>Target Role</th>
                                    <th>Target User</th>
                                    <th>Telegram ID</th>
                                    <th>Active</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notificationRows as $row): ?>
                                    <tr>
                                        <form method="post" action="<?= base_url('SuperAdmin_MyRep_Config/saveNotificationRoute') ?>" class="js-notif-row-form">
                                            <td><?= (int) ($row['id_route'] ?? 0) ?></td>
                                            <td>
                                                <input type="hidden" name="module_name" value="<?= htmlspecialchars((string) ($row['module_name'] ?? '')) ?>">
                                                <?= htmlspecialchars((string) ($row['module_name'] ?? '')) ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="event_name" value="<?= htmlspecialchars((string) ($row['event_name'] ?? '')) ?>">
                                                <?= htmlspecialchars((string) ($row['event_name'] ?? '')) ?>
                                            </td>
                                            <td>
                                                <select name="target_type" class="form-control form-control-sm js-target-type">
                                                    <?php foreach ($targetTypeOptions as $option): ?>
                                                        <option value="<?= htmlspecialchars((string) $option) ?>" <?= strtoupper((string) ($row['target_type'] ?? '')) === strtoupper((string) $option) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) $option) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <?php
                                                $rowTargetRole = strtoupper((string) ($row['target_role'] ?? ''));
                                                $rowRoleClass = '';
                                                if ($isAreaRole($rowTargetRole)) {
                                                    $rowRoleClass = 'myrep-role-area-cell';
                                                } elseif ($isHoRole($rowTargetRole)) {
                                                    $rowRoleClass = 'myrep-role-ho-cell';
                                                }
                                                ?>
                                                <select name="target_role" class="form-control form-control-sm js-target-role <?= $rowRoleClass ?>">
                                                    <option value="">-</option>
                                                    <?php foreach ($roleOptions as $option): ?>
                                                        <option value="<?= htmlspecialchars((string) $option) ?>" <?= strtoupper((string) ($row['target_role'] ?? '')) === strtoupper((string) $option) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) $option) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="target_user_id" class="form-control form-control-sm js-target-user">
                                                    <option value="0">-</option>
                                                    <?php foreach ($userOptions as $user): ?>
                                                        <?php $idUser = (int) ($user['id'] ?? 0); ?>
                                                        <option value="<?= $idUser ?>" <?= (int) ($row['target_user_id'] ?? 0) === $idUser ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars((string) ($user['nama_karyawan'] ?? '-')) ?> [<?= htmlspecialchars((string) ($user['nik'] ?? '-')) ?>]
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><?= !empty($row['target_user_telegram']) ? htmlspecialchars((string) $row['target_user_telegram']) : '-' ?></td>
                                            <td>
                                                <select name="is_active" class="form-control form-control-sm">
                                                    <option value="1" <?= (int) ($row['is_active'] ?? 0) === 1 ? 'selected' : '' ?>>1</option>
                                                    <option value="0" <?= (int) ($row['is_active'] ?? 0) !== 1 ? 'selected' : '' ?>>0</option>
                                                </select>
                                            </td>
                                            <td style="white-space:nowrap;">
                                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                                                <a href="<?= base_url('SuperAdmin_MyRep_Config/deleteNotificationRoute/' . (int) ($row['id_route'] ?? 0)) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus route notifikasi ini?')">Hapus</a>
                                            </td>
                                        </form>
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

<script>
    (function () {
        function setRoleClass($select) {
            if (!$select || !$select.length) {
                return;
            }
            var value = String($select.val() || '').toUpperCase();
            $select.removeClass('myrep-role-area-cell myrep-role-ho-cell');
            if (/_AREA$/.test(value)) {
                $select.addClass('myrep-role-area-cell');
            } else if (/_HO$/.test(value)) {
                $select.addClass('myrep-role-ho-cell');
            }
        }

        function syncTargetFields($typeSelect, $roleSelect, $userSelect) {
            var targetType = String($typeSelect.val() || '').toUpperCase();
            var isFixedUser = targetType === 'FIXED_USER';
            $roleSelect.prop('disabled', isFixedUser);
            $userSelect.prop('disabled', !isFixedUser);
            if (isFixedUser) {
                $roleSelect.val('');
            } else {
                $userSelect.val('0');
            }
            setRoleClass($roleSelect);
        }

        function applyPageFilter() {
            var selectedPage = $('#filter_matrix_page').val() || '';
            $('.js-page-group').each(function () {
                var pageName = ($(this).data('page') || '').toString();
                var shouldShow = selectedPage === '' || pageName === selectedPage;
                $(this).toggle(shouldShow);
            });
        }

        $('#filter_matrix_page').on('change', applyPageFilter);
        applyPageFilter();

        syncTargetFields($('#create_target_type'), $('#create_target_role'), $('#create_target_user'));
        $('#create_target_type').on('change', function () {
            syncTargetFields($('#create_target_type'), $('#create_target_role'), $('#create_target_user'));
        });
        $('#create_target_role').on('change', function () {
            setRoleClass($('#create_target_role'));
        });
        setRoleClass($('#create_target_role'));

        $('.js-notif-row-form').each(function () {
            var $form = $(this);
            var $typeSelect = $form.find('.js-target-type');
            var $roleSelect = $form.find('.js-target-role');
            var $userSelect = $form.find('.js-target-user');
            syncTargetFields($typeSelect, $roleSelect, $userSelect);
            $typeSelect.on('change', function () {
                syncTargetFields($typeSelect, $roleSelect, $userSelect);
            });
            $roleSelect.on('change', function () {
                setRoleClass($roleSelect);
            });
        });

        if (window.jQuery && $.fn.DataTable) {
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (!settings || settings.nTable.id !== 'table_myrep_notification') {
                    return true;
                }

                var rowNode = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                if (!rowNode) {
                    return true;
                }

                var $row = $(rowNode);
                var moduleValue = String($row.find('input[name="module_name"]').val() || '');
                var eventValue = String($row.find('input[name="event_name"]').val() || '');
                var typeValue = String($row.find('select[name="target_type"]').val() || '').toUpperCase();
                var activeValue = String($row.find('select[name="is_active"]').val() || '');

                var moduleFilter = String($('#filter_notif_module').val() || '');
                var eventFilter = String($('#filter_notif_event').val() || '');
                var typeFilter = String($('#filter_notif_type').val() || '').toUpperCase();
                var activeFilter = String($('#filter_notif_active').val() || '');

                if (moduleFilter !== '' && moduleValue !== moduleFilter) return false;
                if (eventFilter !== '' && eventValue !== eventFilter) return false;
                if (typeFilter !== '' && typeValue !== typeFilter) return false;
                if (activeFilter !== '' && activeValue !== activeFilter) return false;

                return true;
            });

            var notifTable = $('#table_myrep_notification').DataTable({
                pageLength: 25,
                order: [[1, 'asc'], [2, 'asc']]
            });

            $('#filter_notif_module, #filter_notif_event, #filter_notif_type, #filter_notif_active').on('change', function () {
                notifTable.draw();
            });
        }
    })();
</script>

<style>
    .myrep-role-area {
        background: #e8f5e9 !important;
        color: #1b5e20;
    }

    .myrep-role-ho {
        background: #e3f2fd !important;
        color: #0d47a1;
    }

    .myrep-role-area-cell {
        background: #f3fbf4;
    }

    .myrep-role-ho-cell {
        background: #f2f8ff;
    }
</style>
