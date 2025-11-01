<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;

$unique_pic = array_unique(array_column($getAllData, 'pic_user'));
$unique_bowheer = array_unique(array_column($getAllData, 'nama_bowheer'));
$unique_regional = array_unique(array_column($getAllData, 'regional_target'));
$unique_city = array_unique(array_column($getAllData, 'area_target'));
$unique_month = array_unique(array_column($getAllData, 'month_target'));
$unique_week = array_unique(array_column($getAllData, 'week_target'));


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
                        const table = $('#tabel_targetpic_summary').DataTable({
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

                        // === Nomor Otomatis ===
                        table.on('order.dt search.dt', function () {
                            table.column(0, { search: 'applied', order: 'applied' })
                                .nodes().each(function (cell, i) {
                                    cell.innerHTML = i + 1;
                                });
                        }).draw();
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
            return 'Rp. ' + formatTitik(num);
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
            const totalTarget = parseFloat($('#tabel_targetpic_summary tfoot th#footer_Target').text().replace(/[^\d]/g, '')) || 0;
            const totalAchieved = parseFloat($('#tabel_targetpic_summary tfoot th#footer_Achieved').text().replace(/[^\d]/g, '')) || 0;
            const totalSisa = parseFloat($('#tabel_targetpic_summary tfoot th#footer_Sisa').text().replace(/[^\d]/g, '')) || 0;
            const totalPersen = totalTarget > 0 ? (totalAchieved / totalTarget) * 100 : 0;

            animateValue('dashboardRincianInvoice', 0, totalTarget, 600, true);
            animateValue('dashboardAchievInvoice', 0, totalAchieved, 600, true);
            animateValue('dashboardSisaInvoice', 0, totalSisa, 600, true);
            animateValue('dashboardPersentaseInvoice', 0, totalPersen, 600, false);
        }

        function highlightCells() {
        $('#tabel_targetpic_summary tbody tr').each(function () {
            const cell = $(this).find('td:contains("%")').last();
            let persenText = cell.text().trim();
            persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');
            const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

            let icon = '';
                    if (persen < 100) {
                        icon = ' <i class="fas fa-arrow-down text-danger"></i>'; // merah turun
                        cell.addClass('cell-red');
                    } else if (persen === 100) {
                        icon = ' <i class="fas fa-check-circle text-success"></i>'; // hijau centang
                        cell.addClass('cell-green-light');
                    } else if (persen > 100) {
                        icon = ' <i class="fas fa-arrow-up text-success"></i>'; // hijau naik
                        cell.addClass('cell-green-dark');
                    }

            cell.html(`${persenText}${icon}`);
        });
    }
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
    </style>