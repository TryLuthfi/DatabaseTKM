<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
?>


<meta name="format-detection" content="telephone=no">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">REPORT BUDGET VS CASHFLOW</h1>
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
                                        <label style="display: flex; justify-content: center; align-items: center;">AKUN
                                            UTAMA
                                        </label>
                                        <select id="filter_akun_utama" class="select2" multiple="multiple"
                                            data-placeholder="Pilih akun utama" style="width: 100%;">
                                            <?php foreach ($unique_akun_utama as $akun_utama): ?>
                                                <option value="<?= $akun_utama ?>"><?= $akun_utama ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label style="display: flex; justify-content: center; align-items: center;">SUB
                                            AKUN
                                        </label>
                                        <select id="filter_sub_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih sub akun" style="width: 100%;">
                                            <?php foreach ($unique_sub_akun as $sub_akun): ?>
                                                <option value="<?= $sub_akun ?>"><?= $sub_akun ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">NOMOR
                                            AKUN
                                        </label>
                                        <select id="filter_nomor_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih nomor akun" style="width: 100%;">
                                            <?php foreach ($unique_nomor_akun as $nomor_akun): ?>
                                                <option value="<?= $nomor_akun ?>"><?= $nomor_akun ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">DETAIL
                                            AKUN</label>
                                        <select id="filter_deskripsi_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih deskripsi akun" style="width: 100%;">
                                            <?php foreach ($unique_deskripsi_akun as $deskripsi_akun): ?>
                                                <option value="<?= $deskripsi_akun ?>"><?= $deskripsi_akun ?></option>
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

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">ANNUAL REPORT</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="content">
        <section class="content">

            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- fix for small devices only -->
                    <div class="clearfix hidden-md-up"></div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-responsive text-nowrap">
                                <table id="table_cashflow" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Akun</th>
                                            <?php for ($b = 1; $b <= 12; $b++): ?>
                                                <th>
                                                    <?= date('M', mktime(0, 0, 0, $b, 1)) ?><br>Budget
                                                </th>
                                                <th>
                                                    <?= date('M', mktime(0, 0, 0, $b, 1)) ?><br>Real
                                                </th>
                                            <?php endfor; ?>
                                            <th>Total Budget</th>
                                            <th>Total Real</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($report as $row): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $row['akun'] ?></td>

                                                <?php for ($b = 1; $b <= 12; $b++):
                                                    $budget = $row['bulan'][$b]['budget'] ?? 0;
                                                    $real = $row['bulan'][$b]['real'] ?? 0;
                                                    $class = $real > $budget ? 'bg-danger' : 'bg-success';
                                                    ?>
                                                    <td><?= number_format($budget) ?></td>
                                                    <td class="<?= $class ?>"><?= number_format($real) ?></td>
                                                <?php endfor; ?>

                                                <td><b><?= number_format($row['total_budget']) ?></b></td>
                                                <td><b><?= number_format($row['total_real']) ?></b></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">TOTAL</th>

                                            <?php for ($b = 1; $b <= 12; $b++): ?>
                                                <th class="text-right total-budget-bulan">0</th>
                                                <th class="text-right total-real-bulan">0</th>
                                            <?php endfor; ?>

                                            <th class="text-right" id="totalBudgetAll">0</th>
                                            <th class="text-right" id="totalRealAll">0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <!-- ISI -->
                        </div>
                    </div>
        </section>

        <!-- /.content-wrapper -->



        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>

    </div>

    <script>
        const userLevel = "<?= $this->session->userdata('nama_level'); ?>";
    </script>

    <script>
        $(document).ready(function () {

            let table = $('#table_cashflow').DataTable({
                scrollX: false,
                paging: true,
                searching: true,
                ordering: false,
                autoWidth: false,
                responsive: false,

                initComplete: function () {
                    let api = this.api();
                    setTimeout(() => {
                        api.columns.adjust().draw(false);
                    }, 100);
                },

                footerCallback: function (row, data, start, end, display) {

                    let api = this.api();

                    // helper convert string → number
                    let intVal = function (i) {
                        if (typeof i === 'string') {
                            return parseInt(i.replace(/[^\d]/g, '')) || 0;
                        }
                        return typeof i === 'number' ? i : 0;
                    };

                    let totalBudgetAll = 0;
                    let totalRealAll = 0;

                    // kolom mulai dari index ke-2 (No, Akun)
                    let colIndex = 2;

                    for (let b = 1; b <= 12; b++) {

                        let budgetCol = colIndex;
                        let realCol = colIndex + 1;

                        let totalBudgetBulan = api
                            .column(budgetCol, { search: 'applied' })
                            .data()
                            .reduce((a, b) => intVal(a) + intVal(b), 0);

                        let totalRealBulan = api
                            .column(realCol, { search: 'applied' })
                            .data()
                            .reduce((a, b) => intVal(a) + intVal(b), 0);

                        $(api.column(budgetCol).footer())
                            .html(totalBudgetBulan.toLocaleString());

                        $(api.column(realCol).footer())
                            .html(totalRealBulan.toLocaleString());

                        totalBudgetAll += totalBudgetBulan;
                        totalRealAll += totalRealBulan;

                        colIndex += 2;
                    }

                    $('#totalBudgetAll').html(totalBudgetAll.toLocaleString());
                    $('#totalRealAll').html(totalRealAll.toLocaleString());
                }
            });

        });
    </script>

    <script>

        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            <?php if ($status == 'sukses_tambah') { ?>
                swal.fire("Success!", "Berhasil Ditambah!", "success");
            <?php } else if ($status == 'sukses_hapus') { ?>
                    swal.fire("Success!", "Berhasil Dihapus!", "success");
            <?php } else if ($status == 'sukses_edit') { ?>
                        swal.fire("Success!", "Berhasil Edit Data!", "success");
            <?php } else if ($status == 'gagal_tambah') { ?>
                            swal.fire("Gagal!", "Gagal Menambah Data!", "warning");
            <?php } else if ($status == 'gagal_edit') { ?>
                                swal.fire("Gagal!", "Gagal Mengedit Data!", "warning");
            <?php } else if ($status == 'gagal_hapus') { ?>
                                    swal.fire("Gagal!", "Gagal Menghapus Data!", "warning");
            <?php } else { ?>
            <?php } ?>

            <?php $this->session->set_flashdata('status', 'kosong'); ?>

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
        $(document).on('click', '.btn-edit-akun', function (e) {
            e.preventDefault();

            const btn = $(this);

            $('#edit_id_mab').val(btn.data('id'));

            $('#editfilter_akun_utama').val(btn.data('akun-utama')).trigger('change');
            $('#editfilter_sub_akun').val(btn.data('sub-akun')).trigger('change');
            $('#editfilter_divisi').val(btn.data('divisi')).trigger('change');
            $('#editfilter_pic').val(btn.data('pic')).trigger('change');

            $('#editNomorAkunBaru').val(btn.data('nomor-akun'));
            $('#editDeskripsiAkunBaru').val(btn.data('deskripsi'));

            $('#modal-lg-edit-masterakun').modal('show');
        });
    </script>

    <script>
        var allData = <?= json_encode($getAllData) ?>;

        $(document).ready(function () {
            // === PETA KOLOM DAN FILTER ===
            const colMap = {
                filter_akun_utama: 'mab_akun_utama',
                filter_sub_akun: 'mab_sub_akun',
                filter_nomor_akun: 'mab_nomor_akun',
                filter_deskripsi_akun: 'mab_deskripsi_akun'
            };

            const currentSelections = {
                filter_akun_utama: [],
                filter_sub_akun: [],
                filter_nomor_akun: [],
                filter_deskripsi_akun: []
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
                    mab_akun_utama: $('#filter_akun_utama').val(),
                    mab_sub_akun: $('#filter_sub_akun').val(),
                    mab_nomor_akun: $('#filter_nomor_akun').val(),
                    mab_deskripsi_akun: $('#filter_deskripsi_akun').val(),
                };

                $.ajax({
                    url: '<?= base_url("Budget_Report/getFilteredBudget_ReportAjax") ?>',
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
                                    // console.log(response.columns);
                                    switch (col) {
                                        case 'No':
                                            tbodyHtml += `<td>${i + 1}</td>`;
                                            break;

                                        case 'Akun Utama':
                                            tbodyHtml += `<td>${row.mab_akun_utama || '-'}</td>`;
                                            break;

                                        case 'Sub Akun':
                                            tbodyHtml += `<td>${row.mab_sub_akun || '-'}</td>`;
                                            break;

                                        case 'Nomor Akun':
                                            tbodyHtml += `<td>${row.mab_nomor_akun || '-'}</td>`;
                                            break;

                                        case 'Deskripsi Akun':
                                            tbodyHtml += `<td>${row.mab_deskripsi_akun || '-'}</td>`;
                                            break;

                                        case 'Divisi':
                                            tbodyHtml += `<td>${row.mab_divisi || '-'}</td>`;
                                            break;

                                        case 'PIC':
                                            tbodyHtml += `<td>${row.mab_pic || '-'}</td>`;
                                            break;
                                    }
                                });

                                if (userLevel === 'Super Admin') {
                                    tbodyHtml += `
                        <td class="text-center">
                            <a href="<?= site_url('Budget_Report/hapusMasterBudget/') ?>${row.id_mab}"
                               class="btn btn-danger btn-sm tombol_hapus">
                                <i class="fas fa-trash"></i>
                            </a>

                            <a href="#"
   class="btn btn-warning btn-sm btn-edit-akun"
   data-id="${row.id_mab}"
   data-akun-utama="${row.mab_akun_utama}"
   data-sub-akun="${row.mab_sub_akun}"
   data-nomor-akun="${row.mab_nomor_akun}"
   data-deskripsi="${row.mab_deskripsi_akun}"
   data-divisi="${row.mab_divisi}"
   data-pic="${row.mab_pic}">
    <i class="fas fa-edit"></i>
</a>
                        </td>
                    `;
                                } else {
                                    tbodyHtml += `<td>-</td>`;
                                }

                                tbodyHtml += `</tr>`;

                                tbodyHtml += `</tr>`;
                            });

                            $('#tabel_targetpic_summary tbody').html(tbodyHtml);
                            console.log('userLevel =', userLevel);
                        }

                        // === DATATABLE ===
                        const table = $('#tabel_targetpic_summary').DataTable({
                            responsive: true,
                            autoWidth: false,
                            pageLength: 10,
                            ordering: true,
                            order: [[1, 'desc']],
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
                $('#filter_akun_utama, #filter_sub_akun, #filter_nomor_akun, #filter_deskripsi_akun').val(null).trigger('change');
                setTimeout(() => $('#btnFilterDataProject').trigger('click'), 300);
            });
        });

        $(document).on('click', '.tombol_hapus', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');

            Swal.fire({
                icon: 'warning',
                title: 'Hapus data?',
                text: 'Data yang dihapus tidak bisa dikembalikan',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = url;
                }
            });
        });


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

            animateValue('dashboardBudget_Report', 0, totalTarget, 600, true);
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


        $(document).ready(function () {
            // === VALIDASI SAAT SIMPAN ===
            $("form").on("submit", function (e) {
                e.preventDefault();

                // 🔹 Validasi tetap dipertahankan (jangan dihapus)

                const inputAkunUtamaBaru = $('#inputAkunUtamaBaru');
                const inputSubAkunBaru = $('#inputSubAkunBaru');
                const inputDivisiBaru = $('#inputDivisiBaru');
                const inputPICBaru = $('#inputPICBaru');

                const akunutamaDropdown = $('#addfilter_akun_utama');
                const subakunDropdown = $('#addfilter_sub_akun');
                const divisiDropdown = $('#addfilter_divisi');
                const picDropdown = $('#addfilter_pic');

                let akunutama = akunutamaDropdown.val(); // ✅ Tambahkan ini
                let subakun = subakunDropdown.val(); // ✅ Tambahkan ini
                let divisi = divisiDropdown.val(); // ✅ Tambahkan ini
                let pic = picDropdown.val(); // ✅ Tambahkan ini

                let nomorakun = $("#inputNomorAkunBaru").val();
                let deskripsiakun = $("#inputDeskripsiAkunBaru").val();

                // 🔹 Jika tambah kota baru aktif → pakai nilai input kota baru
                if (inputAkunUtamaBaru.is(':visible') && inputAkunUtamaBaru.val().trim() !== '') {
                    akunutama = inputAkunUtamaBaru.val().trim(); // ✅ overwrite nilai akun utama
                    akunutamaDropdown.val(akunutama); // agar ikut terkirim via serialize
                }

                if (inputSubAkunBaru.is(':visible') && inputSubAkunBaru.val().trim() !== '') {
                    subakun = inputSubAkunBaru.val().trim(); // ✅ overwrite nilai sub akun
                    subakunDropdown.val(subakun); // agar ikut terkirim via serialize
                }

                if (inputDivisiBaru.is(':visible') && inputDivisiBaru.val().trim() !== '') {
                    divisi = inputDivisiBaru.val().trim(); // ✅ overwrite nilai divisi
                    divisiDropdown.val(divisi); // agar ikut terkirim via serialize
                }

                if (inputPICBaru.is(':visible') && inputPICBaru.val().trim() !== '') {
                    pic = inputPICBaru.val().trim(); // ✅ overwrite nilai PIC
                    picDropdown.val(pic); // agar ikut terkirim via serialize
                }

                if (!akunutama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi a.'
                    });
                    return;
                }

                if (!subakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi b.'
                    });
                    return;
                }

                if (!divisi) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi c.'
                    });
                    return;
                }

                if (!pic) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi d.'
                    });
                    return;
                }

                if (!nomorakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi e.'
                    });
                    return;
                }

                if (!deskripsiakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi f.'
                    });
                    return;
                }

                // 🔹 Konfirmasi simpan
                Swal.fire({
                    icon: 'question',
                    title: 'Simpan Data?',
                    text: 'Pastikan semua data sudah benar sebelum disimpan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = $("form").serialize();

                        $.ajax({
                            url: "<?= base_url('Budget_Report/addMasterBudget') ?>",
                            type: "POST",
                            data: formData,
                            dataType: "json",
                            success: function (res) {
                                console.log(res);

                                if (res.status === 'not_found') {
                                    Swal.fire({
                                        icon: 'question',
                                        title: 'Area belum terdaftar',
                                        text: 'Project ini tidak memiliki area ini. Tambahkan area dan invoice?',
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, Tambahkan!',
                                        cancelButtonText: 'Batal'
                                    }).then((r) => {
                                        if (r.isConfirmed) {
                                            $.ajax({
                                                url: "<?= base_url('Budget_Report/createNewTargetInvoice') ?>",
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
            $('#modal-lg-tambah-masterakun').on('show.bs.modal', function () {
                $(this).find('form')[0].reset();
                $("[name='achiev_invoice'], [name='tambahan_invoice'], [name='total_invoice']").val("");
                $("[name='tambahan_invoice']").closest('.form-group').hide();
                $("[name='total_invoice']").closest('.form-group').hide();
            });

        });

        $(document).ready(function () {
            let isTambahAkunUtamaActive = false;
            let isTambahSubAkunActive = false;
            let isTambahDivisiActive = false;
            let isTambahPicActive = false;

            $('#btnTambahAkunUtamaBaru').on('click', function () {
                isTambahAkunUtamaActive = !isTambahAkunUtamaActive;
                if (isTambahAkunUtamaActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputAkunUtamaBaruContainer').slideDown();
                    $('#addfilter_akun_utama').prop('disabled', true);
                    $('#addfilter_akun_utama').val('');
                    $(this).text('× Batalkan Tambah Akun Utama');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputAkunUtamaBaruContainer').slideUp();
                    $('#addfilter_akun_utama').prop('disabled', false);
                    $('#inputAkunUtamaBaru').val('');
                    $(this).text('+ Tambah Akun Utama Baru');

                }
            });

            $('#btnTambahSubAkunBaru').on('click', function () {
                isTambahSubAkunActive = !isTambahSubAkunActive;
                if (isTambahSubAkunActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputSubAkunBaruContainer').slideDown();
                    $('#addfilter_sub_akun').prop('disabled', true);
                    $('#addfilter_sub_akun').val('');
                    $(this).text('× Batalkan Tambah Sub Akun');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputSubAkunBaruContainer').slideUp();
                    $('#addfilter_sub_akun').prop('disabled', false);
                    $('#inputSubAkunBaru').val('');
                    $(this).text('+ Tambah Sub Akun Baru');
                }
            });

            $('#btnTambahDivisiBaru').on('click', function () {
                isTambahDivisiActive = !isTambahDivisiActive;
                if (isTambahDivisiActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputDivisiBaruContainer').slideDown();
                    $('#addfilter_divisi').prop('disabled', true);
                    $('#addfilter_divisi').val('');
                    $(this).text('× Batalkan Tambah Divisi');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputDivisiBaruContainer').slideUp();
                    $('#addfilter_divisi').prop('disabled', false);
                    $('#inputDivisiBaru').val('');
                    $(this).text('+ Tambah Divisi Baru');
                }
            });

            $('#btnTambahPICBaru').on('click', function () {
                isTambahPicActive = !isTambahPicActive;
                if (isTambahPicActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputPICBaruContainer').slideDown();
                    $('#addfilter_pic').prop('disabled', true);
                    $('#addfilter_pic').val('');
                    $(this).text('× Batalkan Tambah PIC');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputPICBaruContainer').slideUp();
                    $('#addfilter_pic').prop('disabled', false);
                    $('#inputPICBaru').val('');
                    $(this).text('+ Tambah PIC Baru');
                }
            });

            // Pastikan saat submit form, area_target diambil sesuai yang aktif
            $('form').on('submit', function (e) {
                const isTambahAkunUtamaBaru = isTambahAkunUtamaActive;
                const isTambahSubAkunBaru = isTambahSubAkunActive;
                const isTambahDivisiBaru = isTambahDivisiActive;
                const isTambahPicBaru = isTambahPicActive;

                const akunutamaDropdown = $('#addfilter_akun_utama').val();
                const akunutamaBaru = $('#inputAkunUtamaBaru').val().trim();

                const subakunDropdown = $('#addfilter_sub_akun').val();
                const subakunBaru = $('#inputSubAkunBaru').val().trim();

                const divisiDropdown = $('#addfilter_divisi').val();
                const divisiBaru = $('#inputDivisiBaru').val().trim();

                const picDropdown = $('#addfilter_pic').val();
                const picBaru = $('#inputPICBaru').val().trim();

                const nomorAkunBaru = $('#inputNomorAkunBaru').val().trim();
                const deskripsiAkunBaru = $('#inputDeskripsiAkunBaru').val().trim();
                if (isTambahAkunUtamaBaru && akunutamaBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akun Utama baru belum diisi!',
                        text: 'Silakan isi nomor akun utama baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahSubAkunBaru && subakunBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sub Akun baru belum diisi!',
                        text: 'Silakan isi nama sub akun baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahDivisiBaru && divisiBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Divisi baru belum diisi!',
                        text: 'Silakan isi nama divisi baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahPicBaru && picBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'PIC baru belum diisi!',
                        text: 'Silakan isi nama PIC sebelum melanjutkan.'
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
        #modal-lg-tambah-masterakun .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        #modal-lg-tambah-masterakun .modal-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1rem 1.5rem;
        }

        #modal-lg-tambah-masterakun .modal-header h4 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        #modal-lg-tambah-masterakun .modal-body {
            background-color: #f9fafc;
            padding: 1.5rem;
            border-radius: 0 0 15px 15px;
        }

        #modal-lg-tambah-masterakun .form-group label {
            font-weight: 600;
            color: #495057;
        }

        #modal-lg-tambah-masterakun .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        #modal-lg-tambah-masterakun .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #modal-lg-tambah-masterakun .modal-footer {
            background: #f1f3f6;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        #modal-lg-tambah-masterakun .btn-primary {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #modal-lg-tambah-masterakun .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        #modal-lg-tambah-masterakun .btn-danger {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
        }

        /* ======== editAN STYLE UNTUK MODAL edit INVOICE ======== */
        #modal-lg-edit-masterakun .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        #modal-lg-edit-masterakun .modal-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1rem 1.5rem;
        }

        #modal-lg-edit-masterakun .modal-header h4 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        #modal-lg-edit-masterakun .modal-body {
            background-color: #f9fafc;
            padding: 1.5rem;
            border-radius: 0 0 15px 15px;
        }

        #modal-lg-edit-masterakun .form-group label {
            font-weight: 600;
            color: #495057;
        }

        #modal-lg-edit-masterakun .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        #modal-lg-edit-masterakun .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #modal-lg-edit-masterakun .modal-footer {
            background: #f1f3f6;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        #modal-lg-edit-masterakun .btn-primary {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #modal-lg-edit-masterakun .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        #modal-lg-edit-masterakun .btn-danger {
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

        #btnTambahAkunUtamaBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahAkunUtamaBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahSubAkunBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahSubAkunBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahDivisiBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahDivisiBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahPICBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahPICBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }
    </style>