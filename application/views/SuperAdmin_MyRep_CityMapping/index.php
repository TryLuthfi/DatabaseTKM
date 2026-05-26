<?php
$status = (string) $this->session->flashdata('status');
$errorLog = (string) $this->session->flashdata('error_log');
$roleHeader = [
    'rpm_area' => 'RPM AREA',
    'sm_area' => 'SM AREA',
    'spv_area' => 'SPV AREA',
    'snd_area' => 'SND AREA',
    'admin_area' => 'ADMIN AREA',
    'snd_ho' => 'SND HO',
    'atp_ho' => 'ATP HO',
    'rfs_ho' => 'RFS HO',
    'sitac_ho' => 'SITAC HO',
    'dc_ho' => 'DC HO',
    'qa_ho' => 'QA HO',
];
$roleNameField = [
    'rpm_area' => 'rpm_area_name',
    'sm_area' => 'sm_area_name',
    'spv_area' => 'spv_area_name',
    'snd_area' => 'snd_area_name',
    'admin_area' => 'admin_area_name',
    'snd_ho' => 'snd_ho_name',
    'atp_ho' => 'atp_ho_name',
    'rfs_ho' => 'rfs_ho_name',
    'sitac_ho' => 'sitac_ho_name',
    'dc_ho' => 'dc_ho_name',
    'qa_ho' => 'qa_ho_name',
];
$regionalOptions = [];
$provinceOptions = [];
foreach ((array) ($cityPicRows ?? []) as $cityRow) {
    $regionalName = trim((string) ($cityRow['regional_name'] ?? ''));
    $provinceName = trim((string) ($cityRow['province_name'] ?? ''));
    if ($regionalName !== '' && !in_array($regionalName, $regionalOptions, true)) {
        $regionalOptions[] = $regionalName;
    }
    if ($provinceName !== '' && !in_array($provinceName, $provinceOptions, true)) {
        $provinceOptions[] = $provinceName;
    }
}
sort($regionalOptions);
sort($provinceOptions);
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Super Admin - Mapping Kota PIC MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($status === 'sukses_edit'): ?>
                <div class="alert alert-success">
                    Perubahan berhasil disimpan.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php elseif ($status === 'gagal_edit'): ?>
                <div class="alert alert-danger">
                    Perubahan gagal diproses.
                    <?php if ($errorLog !== ''): ?>
                        <div class="small mt-1"><?= htmlspecialchars($errorLog) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tablesReady['city_mapping'])): ?>
                <div class="alert alert-warning">
                    Tabel `tb_myrep_pic_mapping_city` belum ada.
                </div>
            <?php endif; ?>

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Mapping Kota PIC MyRep</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2 text-muted small">
                        Default mode hanya view (nama PIC). Klik <strong>Update Data</strong> untuk masuk mode edit.
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-warning btn-sm" id="btn_enable_edit_city_mapping">Update Data</button>
                        <button type="button" class="btn btn-primary btn-sm d-none" id="btn_save_city_mapping">Save All (Changed Only)</button>
                        <button type="button" class="btn btn-secondary btn-sm d-none" id="btn_cancel_edit_city_mapping">Batal Edit</button>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Filter Regional</label>
                            <select id="filter_city_regional" class="form-control form-control-sm">
                                <option value="">Semua Regional</option>
                                <?php foreach ($regionalOptions as $regionalOption): ?>
                                    <option value="<?= htmlspecialchars($regionalOption) ?>"><?= htmlspecialchars($regionalOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Filter Provinsi</label>
                            <select id="filter_city_province" class="form-control form-control-sm">
                                <option value="">Semua Provinsi</option>
                                <?php foreach ($provinceOptions as $provinceOption): ?>
                                    <option value="<?= htmlspecialchars($provinceOption) ?>"><?= htmlspecialchars($provinceOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <form method="post" action="<?= base_url('SuperAdmin_MyRep_CityMapping/saveBulk') ?>" id="form_city_mapping_bulk" style="display:none;"></form>

                    <div class="table-responsive city-map-scroll">
                        <table class="table table-bordered table-striped table-sm" id="table_myrep_city_mapping_edit">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Regional</th>
                                    <th>Provinsi</th>
                                    <th>Kota</th>
                                    <th>Team</th>
                                    <th>Chief</th>
                                    <?php foreach ($roleColumns as $roleCol): ?>
                                        <th><?= htmlspecialchars((string) ($roleHeader[$roleCol] ?? strtoupper($roleCol))) ?></th>
                                    <?php endforeach; ?>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($cityPicRows as $row): ?>
                                    <tr data-row-id="<?= (int) ($row['id'] ?? 0) ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['province_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['team_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['chief'] ?? '-')) ?></td>
                                        <?php foreach ($roleColumns as $roleCol): ?>
                                            <?php $selectedNik = trim((string) ($row[$roleCol] ?? '')); ?>
                                            <td>
                                                <?php
                                                $nameField = (string) ($roleNameField[$roleCol] ?? '');
                                                $currentName = trim((string) ($nameField !== '' ? ($row[$nameField] ?? '') : ''));
                                                ?>
                                                <div class="js-view-only"><?= htmlspecialchars($currentName !== '' ? $currentName : '-') ?></div>
                                                <div class="js-edit-only d-none">
                                                    <select class="form-control form-control-sm js-city-pic-select" name="<?= htmlspecialchars($roleCol) ?>" data-original="<?= htmlspecialchars($selectedNik, ENT_QUOTES) ?>">
                                                        <option value="">-</option>
                                                        <?php if ($selectedNik !== ''): ?>
                                                            <option value="<?= htmlspecialchars($selectedNik) ?>" selected>
                                                                <?= htmlspecialchars($currentName !== '' ? $currentName : $selectedNik) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                        <td><?= (int) ($row['is_active'] ?? 0) === 1 ? '1' : '0' ?></td>
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
    $(function () {
        var roleColumns = <?= json_encode(array_values($roleColumns)) ?>;
        var userOptionsUrl = <?= json_encode(base_url('SuperAdmin_MyRep_CityMapping/userOptions')) ?>;
        var isEditMode = false;
        var cityTable = null;

        function syncTableLayout() {
            if (!cityTable || !$.fn.DataTable) {
                return;
            }
            setTimeout(function () {
                cityTable.columns.adjust();
            }, 60);
        }

        function initPicSelect($scope) {
            if (!window.jQuery || !$.fn.select2) {
                return;
            }
            var $targets = $scope && $scope.length ? $scope.find('.js-city-pic-select') : $('.js-city-pic-select');
            $targets.each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }
                $select.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Pilih user',
                    allowClear: true,
                    ajax: {
                        url: userOptionsUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results || [],
                                pagination: {
                                    more: !!(data.pagination && data.pagination.more)
                                }
                            };
                        },
                        cache: true
                    }
                });
            });
        }

        function applyModeToVisibleRows() {
            var $table = $('#table_myrep_city_mapping_edit');
            if (isEditMode) {
                $table.find('.js-view-only').addClass('d-none');
                $table.find('.js-edit-only').removeClass('d-none');
            } else {
                $table.find('.js-edit-only').addClass('d-none');
                $table.find('.js-view-only').removeClass('d-none');
            }
        }

        if (window.jQuery && $.fn.DataTable) {
            cityTable = $('#table_myrep_city_mapping_edit').DataTable({
                pageLength: 10,
                order: [[1, 'asc'], [2, 'asc'], [3, 'asc']],
                scrollX: true,
                autoWidth: false
            });
            $('#table_myrep_city_mapping_edit').on('draw.dt', function () {
                applyModeToVisibleRows();
                if (isEditMode) {
                    initPicSelect($('#table_myrep_city_mapping_edit'));
                }
                syncTableLayout();
            });

            $('#filter_city_regional').on('change', function () {
                var value = String($(this).val() || '');
                cityTable.column(1).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
            });

            $('#filter_city_province').on('change', function () {
                var value = String($(this).val() || '');
                cityTable.column(2).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
            });

            $(window).on('resize', syncTableLayout);
        }

        function setEditMode(enabled) {
            isEditMode = !!enabled;
            if (isEditMode) {
                applyModeToVisibleRows();
                $('#btn_enable_edit_city_mapping').addClass('d-none');
                $('#btn_save_city_mapping, #btn_cancel_edit_city_mapping').removeClass('d-none');
                initPicSelect($('#table_myrep_city_mapping_edit'));
                syncTableLayout();
                return;
            }

            applyModeToVisibleRows();
            $('#btn_save_city_mapping, #btn_cancel_edit_city_mapping').addClass('d-none');
            $('#btn_enable_edit_city_mapping').removeClass('d-none');
            syncTableLayout();
        }

        setEditMode(false);

        $('#btn_enable_edit_city_mapping').on('click', function () {
            setEditMode(true);
        });

        $('#btn_cancel_edit_city_mapping').on('click', function () {
            $('#table_myrep_city_mapping_edit .js-city-pic-select').each(function () {
                var $select = $(this);
                $select.val(String($select.data('original') || '')).trigger('change.select2');
            });
            setEditMode(false);
        });

        $('#btn_save_city_mapping').on('click', function () {
            var changedRows = [];
            var rowNodes = cityTable ? cityTable.rows().nodes().toArray() : $('#table_myrep_city_mapping_edit tbody tr').toArray();

            $(rowNodes).each(function () {
                var $row = $(this);
                var rowId = parseInt($row.data('row-id'), 10) || 0;
                if (rowId <= 0) {
                    return;
                }

                var payload = { id: rowId };
                var isChanged = false;

                roleColumns.forEach(function (col) {
                    var $select = $row.find('select[name="' + col + '"]');
                    var currentVal = String($select.val() || '');
                    var originalVal = String($select.data('original') || '');
                    payload[col] = currentVal;
                    if (currentVal !== originalVal) {
                        isChanged = true;
                    }
                });

                if (isChanged) {
                    changedRows.push(payload);
                }
            });

            if (changedRows.length === 0) {
                alert('Tidak ada perubahan untuk disimpan.');
                return;
            }

            var html = '';
            changedRows.forEach(function (row, idx) {
                html += '<input type="hidden" name="rows[' + idx + '][id]" value="' + row.id + '">';
                roleColumns.forEach(function (col) {
                    var val = row[col] || '';
                    html += '<input type="hidden" name="rows[' + idx + '][' + col + ']" value="' + $('<div>').text(val).html() + '">';
                });
            });

            $('#form_city_mapping_bulk').html(html).trigger('submit');
        });
    });
</script>

<style>
    .city-map-scroll {
        overflow-x: auto;
    }

    #table_myrep_city_mapping_edit {
        min-width: 2200px;
    }

    #table_myrep_city_mapping_edit th,
    #table_myrep_city_mapping_edit td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table_myrep_city_mapping_edit .js-edit-only .select2-container {
        width: 100% !important;
        min-width: 120px;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--single {
        min-height: 34px;
        height: 34px;
        padding: 0 28px 0 8px;
        display: flex;
        align-items: center;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-left: 0;
        padding-right: 0;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 32px;
        top: 0;
        right: 4px;
    }
</style>
