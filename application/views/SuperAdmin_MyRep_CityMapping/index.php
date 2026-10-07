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
    'planning_ho' => 'PLANNING HO',
    'finance_ho' => 'FINANCE HO',
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
    'planning_ho' => 'planning_ho_name',
    'finance_ho' => 'finance_ho_name',
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
                        <button type="button" class="btn btn-success btn-sm" id="btn_add_city_mapping">Tambah Kota</button>
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
                                    <th>Area</th>
                                    <th>Provinsi</th>
                                    <th>Kota</th>
                                    <th>Team</th>
                                    <th>Chief</th>
                                    <?php foreach ($roleColumns as $roleCol): ?>
                                        <th><?= htmlspecialchars((string) ($roleHeader[$roleCol] ?? strtoupper($roleCol))) ?></th>
                                    <?php endforeach; ?>
                                    <th>Active</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($cityPicRows as $row): ?>
                                    <tr data-row-id="<?= (int) ($row['id'] ?? 0) ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['area'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['province_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['team_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['chief'] ?? '-')) ?></td>
                                        <?php foreach ($roleColumns as $roleCol): ?>
                                            <?php
                                            $selectedNiks = preg_split('/[,;|]+/', (string) ($row[$roleCol] ?? ''));
                                            $selectedNiks = array_values(array_unique(array_filter(array_map('trim', (array) $selectedNiks))));
                                            $selectedNikCsv = implode(',', $selectedNiks);
                                            ?>
                                            <td>
                                                <?php
                                                $nameField = (string) ($roleNameField[$roleCol] ?? '');
                                                $currentName = trim((string) ($nameField !== '' ? ($row[$nameField] ?? '') : ''));
                                                $currentNames = array_values(array_filter(array_map('trim', explode(',', $currentName))));
                                                ?>
                                                <div class="js-view-only"><?= htmlspecialchars($currentName !== '' ? $currentName : '-') ?></div>
                                                <div class="js-edit-only d-none">
                                                    <select class="form-control form-control-sm js-city-pic-select" name="<?= htmlspecialchars($roleCol) ?>" data-original="<?= htmlspecialchars($selectedNikCsv, ENT_QUOTES) ?>" multiple>
                                                        <?php foreach ($selectedNiks as $selectedIndex => $selectedNik): ?>
                                                            <option value="<?= htmlspecialchars($selectedNik) ?>" selected>
                                                                <?= htmlspecialchars($currentNames[$selectedIndex] ?? $selectedNik) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                        <td><?= (int) ($row['is_active'] ?? 0) === 1 ? '1' : '0' ?></td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-info btn-xs js-edit-city-mapping"
                                                data-id="<?= (int) ($row['id'] ?? 0) ?>"
                                                data-regional_name="<?= htmlspecialchars((string) ($row['regional_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-area="<?= htmlspecialchars((string) ($row['area'] ?? ''), ENT_QUOTES) ?>"
                                                data-province_name="<?= htmlspecialchars((string) ($row['province_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-city_name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-team_name="<?= htmlspecialchars((string) ($row['team_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-chief="<?= htmlspecialchars((string) ($row['chief'] ?? ''), ENT_QUOTES) ?>"
                                                data-is_active="<?= (int) ($row['is_active'] ?? 0) === 1 ? '1' : '0' ?>"
                                            >Edit</button>
                                            <form method="post" action="<?= base_url('SuperAdmin_MyRep_CityMapping/deleteCity/' . (int) ($row['id'] ?? 0)) ?>" class="d-inline js-delete-city-mapping">
                                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                                            </form>
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

<div class="modal fade" id="modal_city_mapping" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" action="<?= base_url('SuperAdmin_MyRep_CityMapping/saveCity') ?>" class="modal-content" id="form_city_mapping">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_city_mapping_title">Tambah Kota Mapping</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="city_mapping_id" value="">
                <div class="form-group js-create-only">
                    <label>Kota/Kabupaten dari ListArea</label>
                    <select id="city_mapping_regency" class="form-control" style="width:100%"></select>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Regional</label>
                            <input type="text" name="regional_name" id="city_mapping_regional" class="form-control" list="city_mapping_regional_options" required>
                            <datalist id="city_mapping_regional_options">
                                <?php foreach ($regionalOptions as $regionalOption): ?>
                                    <option value="<?= htmlspecialchars($regionalOption) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Area</label>
                            <input type="number" min="1" max="255" name="area" id="city_mapping_area" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Provinsi Mapping</label>
                            <input type="text" name="province_name" id="city_mapping_province" class="form-control" list="city_mapping_province_options" required>
                            <datalist id="city_mapping_province_options">
                                <?php foreach ($provinceOptions as $provinceOption): ?>
                                    <option value="<?= htmlspecialchars($provinceOption) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kota Mapping</label>
                    <input type="text" name="city_name" id="city_mapping_city" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Team</label>
                            <input type="text" name="team_name" id="city_mapping_team" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Chief</label>
                            <input type="text" name="chief" id="city_mapping_chief" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group js-create-only">
                    <label>Copy PIC Dari Kota</label>
                    <select name="copy_from_id" id="city_mapping_copy_from" class="form-control" style="width:100%"></select>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" id="city_mapping_active" class="form-check-input" checked>
                    <label class="form-check-label" for="city_mapping_active">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(function () {
        var roleColumns = <?= json_encode(array_values($roleColumns)) ?>;
        var userOptionsUrl = <?= json_encode(base_url('SuperAdmin_MyRep_CityMapping/userOptions')) ?>;
        var regencyOptionsUrl = <?= json_encode(base_url('SuperAdmin_MyRep_CityMapping/regencyOptions')) ?>;
        var cityMappingOptionsUrl = <?= json_encode(base_url('SuperAdmin_MyRep_CityMapping/cityMappingOptions')) ?>;
        var isEditMode = false;
        var cityTable = null;

        function normalizePicCsv(value) {
            var rawValues = Array.isArray(value) ? value : String(value || '').split(/[;,|]+/);
            var seen = {};
            var values = [];
            rawValues.forEach(function (item) {
                var nik = String(item || '').trim();
                if (nik !== '' && !seen[nik]) {
                    seen[nik] = true;
                    values.push(nik);
                }
            });
            return values.join(',');
        }

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
                    placeholder: 'Pilih PIC',
                    allowClear: true,
                    multiple: true,
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
                order: [[1, 'asc'], [3, 'asc'], [4, 'asc']],
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
                cityTable.column(3).search(value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '', true, false).draw();
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
                var originalCsv = normalizePicCsv($select.data('original') || '');
                $select.val(originalCsv === '' ? [] : originalCsv.split(',')).trigger('change.select2');
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
                    var currentVal = normalizePicCsv($select.val() || []);
                    var originalVal = normalizePicCsv($select.data('original') || '');
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

        function initCityCrudSelects() {
            if (!window.jQuery || !$.fn.select2) {
                return;
            }

            $('#city_mapping_regency').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modal_city_mapping'),
                placeholder: 'Cari kota/kabupaten',
                allowClear: true,
                ajax: {
                    url: regencyOptionsUrl,
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
            }).on('select2:select', function (event) {
                var data = event.params && event.params.data ? event.params.data : {};
                $('#city_mapping_city').val(String(data.city_name || '').toUpperCase());
                $('#city_mapping_province').val(String(data.province_alias || data.province_name || '').toUpperCase());
                if (data.default_regional_name) {
                    $('#city_mapping_regional').val(String(data.default_regional_name).toUpperCase());
                }
                if (data.default_area) {
                    $('#city_mapping_area').val(data.default_area);
                }
                if (data.default_copy_from_id && data.default_copy_from_text) {
                    var option = new Option(data.default_copy_from_text, data.default_copy_from_id, true, true);
                    $('#city_mapping_copy_from').append(option).trigger('change');
                }
            });

            $('#city_mapping_copy_from').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modal_city_mapping'),
                placeholder: 'Opsional',
                allowClear: true,
                ajax: {
                    url: cityMappingOptionsUrl,
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
        }

        function resetCityForm() {
            $('#form_city_mapping')[0].reset();
            $('#city_mapping_id').val('');
            $('#city_mapping_regency').val(null).trigger('change');
            $('#city_mapping_copy_from').val(null).trigger('change');
            $('#city_mapping_active').prop('checked', true);
            $('.js-create-only').removeClass('d-none');
            $('#modal_city_mapping_title').text('Tambah Kota Mapping');
        }

        initCityCrudSelects();

        $('#btn_add_city_mapping').on('click', function () {
            resetCityForm();
            $('#modal_city_mapping').modal('show');
        });

        $('#table_myrep_city_mapping_edit').on('click', '.js-edit-city-mapping', function () {
            var $button = $(this);
            resetCityForm();
            $('#modal_city_mapping_title').text('Edit Kota Mapping');
            $('.js-create-only').addClass('d-none');
            $('#city_mapping_id').val($button.data('id') || '');
            $('#city_mapping_regional').val($button.data('regional_name') || '');
            $('#city_mapping_area').val($button.data('area') || '');
            $('#city_mapping_province').val($button.data('province_name') || '');
            $('#city_mapping_city').val($button.data('city_name') || '');
            $('#city_mapping_team').val($button.data('team_name') || '');
            $('#city_mapping_chief').val($button.data('chief') || '');
            $('#city_mapping_active').prop('checked', String($button.data('is_active') || '0') === '1');
            $('#modal_city_mapping').modal('show');
        });

        $('#table_myrep_city_mapping_edit').on('submit', '.js-delete-city-mapping', function (event) {
            if (!confirm('Hapus mapping kota ini?')) {
                event.preventDefault();
            }
        });
    });
</script>

<style>
    .city-map-scroll {
        overflow-x: auto;
    }

    #table_myrep_city_mapping_edit {
        min-width: 2350px;
    }

    #table_myrep_city_mapping_edit th,
    #table_myrep_city_mapping_edit td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table_myrep_city_mapping_edit .js-edit-only .select2-container {
        width: 100% !important;
        min-width: 180px;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 34px;
        padding: 2px 6px;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        max-width: 160px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #table_myrep_city_mapping_edit .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
