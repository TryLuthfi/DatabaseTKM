<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$bulan_order = [
    'JANUARI',
    'FEBRUARI',
    'MARET',
    'APRIL',
    'MEI',
    'JUNI',
    'JULI',
    'AGUSTUS',
    'SEPTEMBER',
    'OKTOBER',
    'NOVEMBER',
    'DESEMBER'
];

$total = 1;

$unique_pic = array_unique(array_column($getAllData, 'pic_user'));
$unique_bowheer = array_unique(array_column($getAllData, 'nama_bowheer'));
$unique_regional = array_unique(array_column($getAllData, 'regional_target'));
$unique_city = array_unique(array_column($getAllData, 'area_target'));
$unique_month = array_unique(array_column($getAllData, 'month_target'));
$unique_week = array_unique(array_column($getAllData, 'week_target'));

sort($unique_city, SORT_STRING | SORT_FLAG_CASE);
usort($unique_month, function ($a, $b) use ($bulan_order) {
    return array_search($a, $bulan_order) - array_search($b, $bulan_order);
});

?>

<meta name="format-detection" content="telephone=no">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">RINCIAN INVOICE ( ALL PROJECT )</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <!-- DIRECT CHAT DANGER -->
                <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title">FILTER DATA</h3>

                        <div class="card-tools">
                            <button id="cardfiltercollapse" type="button" class="btn btn-tool"
                                data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="margin-top:10px;">
                        <div class="container-fluid">
                            <!-- Info boxes -->
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label style="display: flex; justify-content: center; align-items: center;">PIC
                                            HO
                                        </label>
                                        <select id="filter_pic" class="select2" multiple="multiple"
                                            data-placeholder="Pilih pic" style="width: 100%;">
                                            <?php foreach ($unique_pic as $pic): ?>
                                                <option value="<?= $pic ?>"><?= $pic ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">PROJECT
                                            / BOWHEER
                                        </label>
                                        <select id="filter_bowheer" class="select2" multiple="multiple"
                                            data-placeholder="Pilih bowheer" style="width: 100%;">
                                            <?php foreach ($unique_bowheer as $bowheer): ?>
                                                <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">REGIONAL
                                        </label>
                                        <select id="filter_regional" class="select2" multiple="multiple"
                                            data-placeholder="Pilih regional" style="width: 100%;">
                                            <?php foreach ($unique_regional as $regional): ?>
                                                <option value="<?= $regional ?>"><?= $regional ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">KOTA</label>
                                        <select id="filter_city" class="select2" multiple="multiple"
                                            data-placeholder="Pilih city" style="width: 100%;">
                                            <?php foreach ($unique_city as $city): ?>
                                                <option value="<?= $city ?>"><?= $city ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">BULAN</label>
                                        <select id="filter_month" class="select2" multiple="multiple"
                                            data-placeholder="Pilih month" style="width: 100%;">
                                            <?php foreach ($unique_month as $month): ?>
                                                <option value="<?= $month ?>"><?= $month ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">WEEK</label>
                                        <select id="filter_week" class="select2" multiple="multiple"
                                            data-placeholder="Pilih week" style="width: 100%;">
                                            <?php foreach ($unique_week as $week): ?>
                                                <option value="<?= $week ?>"><?= $week ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer col-sm-12">
                                    <button type="button" id="reset_filter" class="btn btn-danger"
                                        data-dismiss="modal">Hapus</button>
                                    <button id="btnFilterDataProject" class="btn btn-primary"><i
                                            class="fa fa-spinner fa-spin loading" style="display:none"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- /.col -->
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">TARGET INVOICE</span>
                                <span class="info-box-number" id="dashboardRincianInvoice">Rp. 0
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-green">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">ACHIEVED INVOICE</span>
                                <h4 class="info-box-number" style="color: #33cc33;" id="dashboardAchievInvoice">
                                    Rp. 0
                                </h4>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-red">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SISA INVOICE</span>
                                <h4 class="info-box-number" style="color: #ce0808ff;" id="dashboardSisaInvoice">
                                    Rp. 0
                                </h4>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">PERSENTASE INVOICE</span>
                                <span class="info-box-number" id="dashboardPersentaseInvoice">
                                    0%
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>
                </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 justify-content-center">
                    <div class="col-sm-6 text-center">
                        <button type="button" class="btn btn-gradient-primary btn-lg shadow pulse" data-toggle="modal"
                            data-target="#modal-lg-tambah-invoice">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <strong>TAMBAH INVOICE</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">

            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- fix for small devices only -->
                    <div class="clearfix hidden-md-up"></div>

                    <div class="col-12">
                        <div class="card">
                            <!-- /.card-header -->
                            <div class="card-body table-responsive text-nowrap">
                                <table id="tabel_targetpic_summary" class="table table-bordered table-hover">
                                    <thead class="bg-info">
                                        <tr>
                                            <th>No</th>
                                            <th>PIC</th>
                                            <th>Bowheer</th>
                                            <th>Regional</th>
                                            <th>Kota</th>
                                            <th>Target</th>
                                            <th>Achieved</th>
                                            <th>Sisa</th>
                                            <th>Target %</th>
                                            <th>Achieved %</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" style="text-align:right">Total:</th>
                                            <th id="totalTarget">0</th>
                                            <th id="totalAchieved">0</th>
                                            <th id="totalSisa">0</th>
                                            <th id="totalTargetPercent">0%</th>
                                            <th id="totalAchievedPercent">0%</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <div class="row">
                            <!-- ISI -->
                        </div>
                    </div>
        </section>

        <form id="formTambahInvoice" action="<?php echo site_url('RincianInvoice/addInvoice'); ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-invoice">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">🧾 TAMBAH INVOICE</h4>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Bowheer / Project</label>
                                    <select id="addfilter_bowheer" name="addfilter_bowheer"
                                        class="form-control js-add-invoice-select"
                                        data-placeholder="Pilih Bowheer" style="width:100%;">
                                        <option value="" selected disabled hidden>Pilih Bowheer</option>
                                        <?php foreach ($unique_bowheer as $bowheer): ?>
                                            <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Area</label>
                                    <select id="addfilter_area" name="addfilter_area"
                                        class="form-control area-dropdown js-add-invoice-select"
                                        data-placeholder="Pilih Area" style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Area</option>
                                        <?php foreach ($unique_city as $area): ?>
                                            <option value="<?= $area ?>"><?= $area ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Tombol tambah kota -->
                                    <div class="text-right mt-2">
                                        <button type="button" id="btnTambahKota" class="btn btn-link text-primary p-0"
                                            style="font-weight:600;">
                                            + Tambah Kota Baru
                                        </button>
                                    </div>

                                    <!-- Input kota baru (disembunyikan dulu) -->

                                    <select id="inputRegionalBaru" name="inputRegionalBaru"
                                        class="form-control area-dropdown" data-placeholder="Pilih Regional Baru"
                                        style="width: 100%; display:none; margin-top:10px;">
                                        <option value="" selected disabled hidden>Pilih Regional Baru</option>
                                        <?php foreach ($unique_regional as $regional): ?>
                                            <option value="<?= $regional ?>"><?= $regional ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <div id="inputKotaBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputKotaBaru" name="inputKotaBaru" class="form-control"
                                            placeholder="Ketik nama kota baru..." autocomplete="off">
                                    </div>

                                    <div id="inputPICBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputPICBaru" name="inputPICBaru" class="form-control"
                                            placeholder="Ketik PIC baru..." autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Bulan</label>
                                    <select id="addfilter_month" name="addfilter_month" class="form-control"
                                        style="width:100%;">
                                        <option value="" selected disabled hidden>Pilih Bulan Invoice</option>
                                        <?php foreach ($unique_month as $month): ?>
                                            <option value="<?= $month ?>"><?= $month ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Week</label>
                                    <select id="addfilter_week" name="addfilter_week" class="form-control"
                                        style="width:100%;">
                                        <option value="" selected disabled hidden>Pilih Minggu Invoice</option>
                                        <?php foreach ($unique_week as $week): ?>
                                            <option value="<?= $week ?>"><?= $week ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Target Invoice</label>
                                <input type="text" class="form-control" name="target_invoice" autocomplete="off"
                                    placeholder="0" readonly>
                            </div>

                            <div class="form-group">
                                <label>Realisasi Invoice</label>
                                <input type="text" class="form-control" name="achiev_invoice" autocomplete="off"
                                    placeholder="0">
                            </div>

                            <div class="form-group" style="display:none">
                                <label>Tambahan Invoice</label>
                                <input type="text" class="form-control" name="tambahan_invoice" autocomplete="off"
                                    placeholder="0">
                            </div>

                            <div class="form-group" style="display:none">
                                <label>Total Invoice</label>
                                <input type="text" class="form-control" name="total_invoice" autocomplete="off"
                                    placeholder="0" readonly>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                            <button type="submit" name="btnEdit" class="btn btn-primary">
                                <i class="fa fa-spinner fa-spin loading" style="display:none"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>



        <!-- /.content-wrapper -->

        <?php $this->session->set_flashdata('status', 'kosong'); ?>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>

    </div>

    <script>

        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            function initTambahInvoiceSelect2() {
                if (!$.fn.select2) {
                    return;
                }

                $('.js-add-invoice-select').each(function () {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $select.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#modal-lg-tambah-invoice'),
                        placeholder: $select.data('placeholder') || 'Pilih',
                        allowClear: false
                    });
                });
            }

            $('#modal-lg-tambah-invoice').on('shown.bs.modal', initTambahInvoiceSelect2);

            // notifikasi allert sukses atau tidak
            <?php if ($status == 'sukses_tambah') { ?>
                swal("Success!", "Berhasil Ditambah!", "success");
            <?php } else if ($status == 'sukses_hapus') { ?>
                    swal("Success!", "Berhasil Dihapus!", "success");
            <?php } else if ($status == 'sukses_edit') { ?>
                        swal("Success!", "Berhasil Edit Data!", "success");
            <?php } else if ($status == 'gagal_tambah') { ?>
                            swal("Gagal!", "Gagal Menambah Data!", "warning");
            <?php } else if ($status == 'gagal_edit') { ?>
                                swal("Gagal!", "Gagal Mengedit Data!", "warning");
            <?php } else if ($status == 'gagal_hapus') { ?>
                                    swal("Gagal!", "Gagal Menghapus Data!", "warning");
            <?php } else { ?>
            <?php } ?>
        })

        $(document).ready(function () {
            $('#tabel_targetpic_summary').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
        });

        $(document).ready(function () {
            $('.card[data-card-widget="collapsed"]').addClass('card-tools');
        });

        // document.addEventListener("DOMContentLoaded", function () {
        //     let cardfilter = document.getElementById("cardfiltercollapse").closest(".card");
        //     cardfilter.classList.add("collapsed-card");
        // });

    </script>

    <script>
        var allData = <?= json_encode($getAllData) ?>;

        $(document).ready(function () {
            // === PETA KOLOM DAN FILTER ===
            const colMap = {
                filter_pic: 'pic_user',
                filter_bowheer: 'nama_bowheer',
                filter_regional: 'regional_target',
                filter_city: 'area_target',
                filter_month: 'month_target',
                filter_week: 'week_target'
            };

            const currentSelections = {
                filter_pic: [],
                filter_bowheer: [],
                filter_regional: [],
                filter_city: [],
                filter_month: [],
                filter_week: []
            };

            $('.select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: function () {
                    return $(this).data('placeholder') || 'Pilih';
                }
            });

            // === Helper Filter Antar-Select ===
            function getFilteredDataByAllSelections(excludeId = null) {
                return allData.filter(item => {
                    for (const selId in currentSelections) {
                        if (selId === excludeId) continue;
                        const selectedVals = currentSelections[selId] || [];
                        if (selectedVals.length === 0) continue;
                        const col = colMap[selId];
                        if (!selectedVals.map(String).includes(String(item[col]))) return false;
                    }
                    return true;
                });
            }

            function populateSelectFromFilteredData(selectId) {
                const $sel = $('#' + selectId);
                const col = colMap[selectId];
                const filtered = getFilteredDataByAllSelections(selectId);
                const unique = [...new Set(filtered.map(it => it[col]).filter(v => v !== null && v !== undefined && v !== ''))];
                const prevSelected = ($sel.val() || []).map(String);

                $sel.empty();
                unique.forEach(v => $sel.append(`<option value="${v}">${v}</option>`));

                const toSelect = unique.filter(u => prevSelected.includes(String(u)));
                $sel.val(toSelect.length ? toSelect : null);
                $sel.trigger('change.select2');
            }

            function onSelectChange(changedId) {
                currentSelections[changedId] = ($('#' + changedId).val() || []).map(String);
                for (const selId in colMap) populateSelectFromFilteredData(selId);
            }

            Object.keys(colMap).forEach(selId => {
                $('#' + selId).on('change', function () {
                    onSelectChange(selId);
                });
            });

            Object.keys(colMap).forEach(populateSelectFromFilteredData);

            // === EVENT: FILTER DATA ===
            $('#btnFilterDataProject').on('click', function (e) {
                e.preventDefault();

                const filters = {
                    pic: $('#filter_pic').val(),
                    bowheer: $('#filter_bowheer').val(),
                    regional: $('#filter_regional').val(),
                    city: $('#filter_city').val(),
                    month: $('#filter_month').val(),
                    week: $('#filter_week').val(),
                };

                $.ajax({
                    url: '<?= base_url("RincianInvoice/getFilteredRincianInvoiceAjax") ?>',
                    type: 'POST',
                    data: filters,
                    dataType: 'json',
                    beforeSend: function () {
                        $('#btnFilterDataProject i.loading').show();
                    },
                    success: function (response) {
                        if ($.fn.DataTable.isDataTable('#tabel_targetpic_summary')) {
                            $('#tabel_targetpic_summary').DataTable().clear().destroy();
                        }

                        // === HEADER ===
                        let theadHtml = '<tr>';
                        response.columns.forEach(col => theadHtml += `<th>${col}</th>`);
                        theadHtml += '</tr>';
                        $('#tabel_targetpic_summary thead').html(theadHtml);

                        // === FOOTER ===
                        let tfootHtml = '<tr>';
                        response.columns.forEach((col, index) => {
                            if (['Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'].includes(col)) {
                                tfootHtml += `<th id="footer_${col.replace(/\s+/g, '_')}">0</th>`;
                            } else if (index === 0) {
                                tfootHtml += `<th style="text-align:right">Total:</th>`;
                            } else {
                                tfootHtml += `<th></th>`;
                            }
                        });
                        tfootHtml += '</tr>';
                        $('#tabel_targetpic_summary tfoot').html(tfootHtml);

                        // === BODY ===
                        if (!response.data || response.data.length === 0) {
                            $('#tabel_targetpic_summary tbody').html(
                                `<tr><td colspan="${response.columns.length}" class="text-center">No data available</td></tr>`
                            );
                        } else {
                            let tbodyHtml = '';
                            response.data.forEach((row, i) => {
                                tbodyHtml += `<tr>`;
                                response.columns.forEach(col => {
                                    switch (col) {
                                        case 'No': tbodyHtml += `<td>${i + 1}</td>`; break;
                                        case 'PIC': tbodyHtml += `<td>${row.pic_user || '-'}</td>`; break;
                                        case 'Bowheer': tbodyHtml += `<td>${row.nama_bowheer || '-'}</td>`; break;
                                        case 'Regional': tbodyHtml += `<td>${row.regional_target || '-'}</td>`; break;
                                        case 'Kota': tbodyHtml += `<td>${row.area_target || '-'}</td>`; break;
                                        case 'Target': tbodyHtml += `<td>${row.total_target ? formatTitik(parseInt(row.total_target)) : '-'}</td>`; break;
                                        case 'Achieved': tbodyHtml += `<td>${row.total_achieved ? formatTitik(parseInt(row.total_achieved)) : '-'}</td>`; break;
                                        case 'Sisa': tbodyHtml += `<td>${row.sisa ? formatTitik(parseInt(row.sisa)) : '-'}</td>`; break;
                                        case 'Target %': tbodyHtml += `<td>${row.persen_sisa ? Number(row.persen_sisa).toFixed(0) + '%' : '-'}</td>`; break;
                                        case 'Achieved %': tbodyHtml += `<td>${row.persen_achieved ? Number(row.persen_achieved).toFixed(0) + '%' : '-'}</td>`; break;
                                        default: tbodyHtml += `<td>-</td>`;
                                    }
                                });
                                tbodyHtml += `</tr>`;
                            });
                            $('#tabel_targetpic_summary tbody').html(tbodyHtml);
                        }

                        // === DATATABLE ===

                        let table = null;

                        if (response.data && response.data.length > 0) {
                            table = $('#tabel_targetpic_summary').DataTable({
                                responsive: true,
                                autoWidth: false,
                                pageLength: 10,
                                ordering: true,
                                order: [[1, 'asc']],
                                footerCallback: function (row, data, start, end, display) {
                                    const api = this.api();

                                    const parseValue = val => {
                                        return typeof val === 'string'
                                            ? parseFloat(val.replace(/\./g, '').replace(/[^0-9.-]/g, '')) || 0
                                            : typeof val === 'number' ? val : 0;
                                    };

                                    const colIndex = name => response.columns.indexOf(name);

                                    let totalTarget = 0, totalAchieved = 0, totalSisa = 0;

                                    if (colIndex('Target') > -1)
                                        totalTarget = api.column(colIndex('Target'), { search: 'applied' }).data().reduce((a, b) => a + parseValue(b), 0);

                                    if (colIndex('Achieved') > -1)
                                        totalAchieved = api.column(colIndex('Achieved'), { search: 'applied' }).data().reduce((a, b) => a + parseValue(b), 0);

                                    if (colIndex('Sisa') > -1)
                                        totalSisa = api.column(colIndex('Sisa'), { search: 'applied' }).data().reduce((a, b) => a + parseValue(b), 0);

                                    const persenAchieved = totalTarget > 0 ? (totalAchieved / totalTarget) * 100 : 0;
                                    const persenSisa = totalTarget > 0 ? (totalSisa / totalTarget) * 100 : 0;

                                    if (colIndex('Target') > -1)
                                        $(api.column(colIndex('Target')).footer()).html(formatTitik(totalTarget));

                                    if (colIndex('Achieved') > -1)
                                        $(api.column(colIndex('Achieved')).footer()).html(formatTitik(totalAchieved));

                                    if (colIndex('Sisa') > -1)
                                        $(api.column(colIndex('Sisa')).footer()).html(formatTitik(totalSisa));

                                    if (colIndex('Target %') > -1)
                                        $(api.column(colIndex('Target %')).footer()).html(persenSisa.toFixed(0) + '%');

                                    if (colIndex('Achieved %') > -1)
                                        $(api.column(colIndex('Achieved %')).footer()).html(persenAchieved.toFixed(0) + '%');

                                    updateDashboardFromFooter();
                                    highlightCells();
                                }
                            });
                        }

                        // === Nomor Otomatis ===
                        if (table) {
                            table.on('order.dt search.dt', function () {
                                table.column(0, { search: 'applied', order: 'applied' })
                                    .nodes().each(function (cell, i) {
                                        cell.innerHTML = i + 1;
                                    });
                            }).draw();
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                    },
                    complete: function () {
                        $('#btnFilterDataProject i.loading').hide();
                    }
                });
            });

            // === AUTO CLICK SAAT AWAL ===
            setTimeout(() => {
                $('#btnFilterDataProject').trigger('click');
            }, 500);

            // === RESET FILTER ===
            $('#reset_filter').on('click', function () {
                $('#filter_pic, #filter_bowheer, #filter_regional, #filter_city, #filter_month, #filter_week').val(null).trigger('change');
                setTimeout(() => $('#btnFilterDataProject').trigger('click'), 300);
            });
        });

        // === FORMAT ANGKA PAKAI TITIK ===
        function formatTitik(value) {
            if (isNaN(value) || value === null) return '0';
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatRupiah(value) {
            let num = parseFloat(value.toString().replace(/[^\d]/g, '')) || 0;
            return '' + formatTitik(num);
        }

        // === ANIMASI ANGKA DASHBOARD ===
        function animateValue(id, start, end, duration, isRupiah = false) {
            const element = document.getElementById(id);
            if (!element) return;
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const currentValue = Math.floor(progress * (end - start) + start);
                element.innerText = isRupiah
                    ? formatRupiah(currentValue)
                    : formatTitik(currentValue) + (id === 'dashboardPersentaseInvoice' ? '%' : '');
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        // === UPDATE DASHBOARD DARI FOOTER ===
        function updateDashboardFromFooter() {
            const targetEl = $('#tabel_targetpic_summary tfoot th#footer_Target');
            if (!targetEl.length) return;

            const totalTarget = parseFloat(targetEl.text().replace(/[^\d]/g, '')) || 0;
            const totalAchieved = parseFloat($('#footer_Achieved').text().replace(/[^\d]/g, '')) || 0;
            const totalSisa = parseFloat($('#footer_Sisa').text().replace(/[^\d]/g, '')) || 0;

            const totalPersen = totalTarget > 0 ? (totalAchieved / totalTarget) * 100 : 0;

            animateValue('dashboardRincianInvoice', 0, totalTarget, 600, true);
            animateValue('dashboardAchievInvoice', 0, totalAchieved, 600, true);
            animateValue('dashboardSisaInvoice', 0, totalSisa, 600, true);
            animateValue('dashboardPersentaseInvoice', 0, totalPersen, 600, false);
        }

        function highlightCells() {
            $('#tabel_targetpic_summary tbody tr').each(function () {
                const cell = $(this).find('td:contains("%")').last();
                if (!cell.length) return;

                let persenText = cell.text().trim();
                persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

                const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

                let icon = '';
                if (persen < 100) {
                    icon = ' <i class="fas fa-arrow-down text-danger"></i>';
                    cell.addClass('cell-red');
                } else if (persen === 100) {
                    icon = ' <i class="fas fa-check-circle text-success"></i>';
                    cell.addClass('cell-green-light');
                } else {
                    icon = ' <i class="fas fa-arrow-up text-success"></i>';
                    cell.addClass('cell-green-dark');
                }

                cell.html(`${persenText}${icon}`);
            });
        }

        $(document).ready(function () {

            // === Format rupiah mendukung angka negatif ===
            function formatRupiah(angka) {
                angka = angka.toString().replace(/[^0-9\-]/g, ""); // izinkan minus

                if (angka === "" || angka === "-") return angka; // biar bisa ketik "-" dulu

                let num = parseFloat(angka);
                if (isNaN(num)) num = 0;

                const formatted = (num < 0 ? "-" : "") + Math.abs(num).toLocaleString('id-ID');
                return formatted;
            }

            // === Hitung total otomatis ===
            function updateTotalInvoice() {
                let achiev = parseFloat($("[name='achiev_invoice']").val().replace(/[^0-9\-]/g, "")) || 0;
                let tambahan = parseFloat($("[name='tambahan_invoice']").val().replace(/[^0-9\-]/g, "")) || 0;
                let total = achiev + tambahan;

                const formattedTotal = (total < 0 ? "-" : "") + Math.abs(total).toLocaleString('id-ID');
                $("[name='total_invoice']").val(formattedTotal);
            }

            function countDigitsBeforeCaret(value, caretPosition) {
                return value.slice(0, caretPosition).replace(/\D/g, "").length;
            }

            function getCaretPositionByDigitCount(value, digitCount) {
                if (digitCount <= 0) {
                    return value.charAt(0) === '-' ? 1 : 0;
                }

                let seenDigits = 0;
                for (let i = 0; i < value.length; i++) {
                    if (/\d/.test(value.charAt(i))) {
                        seenDigits++;
                    }
                    if (seenDigits >= digitCount) {
                        return i + 1;
                    }
                }

                return value.length;
            }

            // === Event gabungan, tidak duplikat lagi ===
            $(document).on("input", "[name='achiev_invoice'], [name='tambahan_invoice']", function (e) {
                const input = e.target;
                const digitCount = countDigitsBeforeCaret(input.value, input.selectionStart || 0);
                const formattedValue = formatRupiah(input.value);

                $(input).val(formattedValue);
                const caretPos = getCaretPositionByDigitCount(formattedValue, digitCount);
                input.setSelectionRange(caretPos, caretPos);
                updateTotalInvoice();
            });

            // === Fungsi ambil target invoice ===
            function loadTargetInvoice() {
                const bowheer = $('#addfilter_bowheer').val();
                const area = $('#addfilter_area').val();
                const month = $('#addfilter_month').val();
                const week = $('#addfilter_week').val();

                if (bowheer && area && month && week) {
                    $.ajax({
                        url: "<?= base_url('RincianInvoice/get_target_invoice') ?>",
                        type: "POST",
                        dataType: "json",
                        data: { bowheer, area, month, week },
                        success: function (res) {
                            console.log(res);

                            let qtyTarget = res.qty_target ? parseFloat(res.qty_target) : 0;
                            let qtyAchiev = res.qty_achiev_target ? parseFloat(res.qty_achiev_target) : 0;

                            $('[name="target_invoice"]').val(formatRupiah(qtyTarget));
                            $('[name="achiev_invoice"]').val(formatRupiah(qtyAchiev));

                            if (qtyAchiev && qtyAchiev !== 0) {
                                $('[name="achiev_invoice"]').prop('disabled', true);
                                $('[name="tambahan_invoice"]').closest('.form-group').slideDown();
                                $('[name="total_invoice"]').closest('.form-group').slideDown();
                                $('[name="total_invoice"]').val(formatRupiah(qtyAchiev));
                            } else {
                                $('[name="achiev_invoice"]').prop('disabled', false);
                                $('[name="tambahan_invoice"]').closest('.form-group').slideUp();
                                $('[name="total_invoice"]').closest('.form-group').slideUp();
                                $('[name="tambahan_invoice"]').val('');
                                $('[name="total_invoice"]').val('');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error:", error);
                            console.log(xhr.responseText);
                        }
                    });
                }
            }

            // Jalankan ketika dropdown berubah
            $('#addfilter_bowheer, #addfilter_area, #addfilter_month, #addfilter_week').on('change', loadTargetInvoice);

            // === VALIDASI SAAT SIMPAN ===
            $("#formTambahInvoice").on("submit", function (e) {
                e.preventDefault();
                const $form = $(this);

                // 🔹 Validasi tetap dipertahankan (jangan dihapus)
                let bowheer = $("#addfilter_bowheer").val();
                const inputKotaBaru = $('#inputKotaBaru');
                const areaDropdown = $('#addfilter_area');
                let area = areaDropdown.val(); // ✅ Tambahkan ini
                let regional = $("#inputRegionalBaru").val();
                let pic = $("#inputPICBaru").val();
                let month = $("#addfilter_month").val();
                let week = $("#addfilter_week").val();
                let achiev = $("[name='achiev_invoice']").val().trim();
                let tambahanVisible = $("[name='tambahan_invoice']").closest('.form-group').is(":visible");
                let tambahan = $("[name='tambahan_invoice']").val().trim();
                const isInputKotaBaru = inputKotaBaru.is(':visible');
                const areaBaru = inputKotaBaru.val().trim();

                // 🔹 Jika tambah kota baru aktif → pakai nilai input kota baru
                $form.find('input[type="hidden"][name="addfilter_area"]').remove();
                if (inputKotaBaru.is(':visible') && inputKotaBaru.val().trim() !== '') {
                    area = inputKotaBaru.val().trim(); // ✅ overwrite nilai area
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'addfilter_area',
                        value: area
                    }).appendTo($form);
                }

                // 🔹 Validasi dropdown dan input wajib
                if (!bowheer || !area || !month || !week) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown sudah dipilih.'
                    });
                    return;
                }
                if (isInputKotaBaru && (!regional || !pic)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data kota baru belum lengkap!',
                        text: 'Regional baru dan PIC baru wajib diisi.'
                    });
                    return;
                }
                if (achiev === "" || achiev === "0") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input belum diisi!',
                        text: 'Nilai Realisasi Invoice harus diisi.'
                    });
                    return;
                }
                if (tambahanVisible && (tambahan === "" || tambahan === "0")) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input belum diisi!',
                        text: 'Tambahan Invoice wajib diisi jika tampil.'
                    });
                    return;
                }

                // 🔹 Konfirmasi simpan
                Swal.fire({
                    icon: 'question',
                    title: 'Simpan Invoice?',
                    text: 'Pastikan semua data sudah benar sebelum disimpan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = $form.serialize();

                        $.ajax({
                            url: "<?= base_url('RincianInvoice/addInvoice') ?>",
                            type: "POST",
                            data: formData,
                            dataType: "json",
                            success: function (res) {
                                console.log(res);

                                if (res.status === 'not_found') {
                                    Swal.fire({
                                        icon: 'question',
                                        title: 'Area belum terdaftar',
                                        text: (res.message || 'Project ini tidak memiliki area ini') + '. Tambahkan/lengkapi area dan invoice?',
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, Tambahkan!',
                                        cancelButtonText: 'Batal'
                                    }).then((r) => {
                                        if (r.isConfirmed) {
                                            $.ajax({
                                                url: "<?= base_url('RincianInvoice/createNewTargetInvoice') ?>",
                                                type: "POST",
                                                dataType: "json",
                                                data: res,

                                                success: function (res2) {
                                                    Swal.fire({
                                                        icon: res2.status ? 'success' : 'error',
                                                        title: res2.status ? 'Berhasil' : 'Gagal',
                                                        text: res2.message
                                                    }).then(() => location.reload());
                                                }
                                            });
                                        }
                                    });
                                } else if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Invoice berhasil disimpan.'
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: res.message || 'Terjadi kesalahan.'
                                    });
                                }
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Invoice berhasil disimpan.'
                                }).then(() => location.reload());
                            }
                        });
                    }
                });
            });

            // Reset form ketika modal dibuka
            $('#modal-lg-tambah-invoice').on('show.bs.modal', function () {
                const $form = $('#formTambahInvoice');

                if ($form.length && $form[0]) {
                    $form[0].reset();
                }

                $form.find('input[type="hidden"][name="addfilter_area"]').remove();
                $('#addfilter_bowheer, #addfilter_area, #addfilter_month, #addfilter_week').prop('disabled', false).val('').trigger('change.select2');
                $('#inputRegionalBaru').hide().val('').trigger('change.select2');
                $('#inputKotaBaruContainer, #inputPICBaruContainer').hide();
                $('#inputKotaBaru, #inputPICBaru').val('');
                $('#btnTambahKota').text('+ Tambah Kota Baru');
                $("[name='target_invoice'], [name='achiev_invoice'], [name='tambahan_invoice'], [name='total_invoice']").val("");
                $("[name='achiev_invoice']").prop('disabled', false);
                $("[name='tambahan_invoice']").closest('.form-group').hide();
                $("[name='total_invoice']").closest('.form-group').hide();
            });

        });

        $(document).ready(function () {
            let isTambahKotaActive = false;

            $('#modal-lg-tambah-invoice').on('show.bs.modal', function () {
                isTambahKotaActive = false;
            });

            // Toggle form tambah kota
            $('#btnTambahKota').on('click', function () {
                isTambahKotaActive = !isTambahKotaActive;

                if (isTambahKotaActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputKotaBaruContainer').slideDown();
                    $('#addfilter_area').prop('disabled', true);
                    $('#addfilter_area').val('').trigger('change.select2');
                    $(this).text('× Batalkan Tambah Kota');

                    $('#inputRegionalBaru').slideDown().trigger('change.select2');

                    $('#inputPICBaruContainer').slideDown();
                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputKotaBaruContainer').slideUp();
                    $('#addfilter_area').prop('disabled', false).trigger('change.select2');
                    $('#inputKotaBaru').val('');
                    $(this).text('+ Tambah Kota Baru');

                    $('#inputRegionalBaru').slideUp();
                    $('#inputRegionalBaru').val('').trigger('change.select2');

                    $('#inputPICBaruContainer').slideUp();
                    $('#inputPICBaru').val('');
                }
            });

            // Pastikan saat submit form, area_target diambil sesuai yang aktif
            $('#formTambahInvoice').on('submit', function (e) {
                const $form = $(this);
                const isInputBaru = isTambahKotaActive;
                const areaDropdown = $('#addfilter_area').val();
                const areaBaru = $('#inputKotaBaru').val().trim();
                const regionalBaru = $('#inputRegionalBaru').val().trim();
                const picBaru = $('#inputPICBaru').val().trim();

                if (isInputBaru && areaBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kota baru belum diisi!',
                        text: 'Silakan isi nama kota sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isInputBaru && regionalBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Regional baru belum diisi!',
                        text: 'Silakan isi nama regional sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isInputBaru && picBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'PIC baru belum diisi!',
                        text: 'Silakan isi nama PIC sebelum melanjutkan.'
                    });
                    return false;
                }

                // Jika kota baru aktif, gunakan nilainya sebagai area_target
                if (isInputBaru) {
                    $form.find('input[type="hidden"][name="addfilter_area"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'addfilter_area',
                        value: areaBaru
                    }).appendTo($form);
                } else if (!areaDropdown) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Area belum dipilih!',
                        text: 'Pilih area dari daftar atau tambahkan area baru.'
                    });
                    return false;
                }
            });
        });
    </script>



    <style>
        #tabel_targetpic_summary tfoot th {
            text-align: right;
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* upgrade warna header merah */

        .glow-red {
            border: 1px solid rgba(255, 0, 0, 0.7);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-red:hover {
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.9);
        }

        /* upgrade warna header hijau */
        .glow-green {
            border: 1px solid rgba(0, 255, 0, 0.7);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-green:hover {
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.9);
        }


        /* upgrade warna table */

        .cell-red {
            font-color: #ffb3b3 !important;
            color: #eb0000ff !important;
            font-weight: bold;
        }

        /* 🟢 Hijau muda untuk = 100% */
        .cell-green-light {
            color: #b3ffb3 !important;
            font-weight: bold;
        }

        /* 🟩 Hijau tua untuk > 100% */
        .cell-green-dark {
            color: #33cc33 !important;
            font-weight: bold;
            font-weight: bold;
        }

        /* Gradien biru menarik */
        .btn-gradient-primary {
            background: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            color: #fff;
            border-radius: 50px;
            transition: all 0.3s ease-in-out;
            padding: 12px 25px;
            letter-spacing: 1px;
        }

        /* Efek hover */
        .btn-gradient-primary:hover {
            background: linear-gradient(45deg, #0056b3, #0099cc);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        /* Efek glowing lembut */
        .btn-gradient-primary:focus,
        .btn-gradient-primary:active {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        /* Animasi berdenyut (pulse effect) */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(0, 123, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
            }
        }

        /* ======== TAMBAHAN STYLE UNTUK MODAL TAMBAH INVOICE ======== */
        #modal-lg-tambah-invoice .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        #modal-lg-tambah-invoice .modal-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1rem 1.5rem;
        }

        #modal-lg-tambah-invoice .modal-header h4 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        #modal-lg-tambah-invoice .modal-body {
            background-color: #f9fafc;
            padding: 1.5rem;
            border-radius: 0 0 15px 15px;
        }

        #modal-lg-tambah-invoice .form-group label {
            font-weight: 600;
            color: #495057;
        }

        #modal-lg-tambah-invoice .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        #modal-lg-tambah-invoice .select2-container {
            width: 100% !important;
        }

        #modal-lg-tambah-invoice .js-add-invoice-select {
            display: none !important;
        }

        #modal-lg-tambah-invoice .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #ced4da;
            border-radius: 10px;
            min-height: calc(2.25rem + 2px);
        }

        #modal-lg-tambah-invoice select.select2-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            height: 1px !important;
            margin: -1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
        }

        #modal-lg-tambah-invoice .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #modal-lg-tambah-invoice .modal-footer {
            background: #f1f3f6;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        #modal-lg-tambah-invoice .btn-primary {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #modal-lg-tambah-invoice .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        #modal-lg-tambah-invoice .btn-danger {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tombol pemicu modal (judul TAMBAH INVOICE) */
        .text-primary.font-weight-bold {
            background: linear-gradient(90deg, #007bff, #6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .text-primary.font-weight-bold:hover {
            transform: scale(1.05);
            text-shadow: 0 0 10px rgba(102, 16, 242, 0.4);
        }

        #btnTambahKota {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahKota:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }
    </style>
