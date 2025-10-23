<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$option_aktif = ['AKTIF', 'HILANG', 'TERJUAL'];

$total = 1;
?>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">
                            TARGET INVOICE OKTOBER
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="clearfix hidden-md-up"></div>


                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h3 class="card-title">LIST DASHBOARD </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                            data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body table-responsive">
                                <table id="tabel_targetbowheer_filter_city1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th rowspan="3"
                                                style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                No</th>
                                            <th rowspan="3"
                                                style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                PROJECT</th>
                                            <th rowspan="3"
                                                style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL TARGET</th>
                                            <th colspan="10" style="text-align:center; background-color: aqua;">OKTOBER
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL ACHIEVED
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                DEVIASI
                                            </th>
                                        </tr>
                                        <tr>
                                            <!-- OKTOBER -->
                                            <th colspan="2" style="text-align:center; background-color: aqua;">WEEK 1
                                            </th>
                                            <th colspan="2" style="text-align:center; background-color: aqua;">WEEK 2
                                            </th>
                                            <th colspan="2" style="text-align:center; background-color: aqua;">WEEK 3
                                            </th>
                                            <th colspan="2" style="text-align:center; background-color: aqua;">WEEK 4
                                            </th>
                                            <th colspan="2" style="text-align:center; background-color: aqua;">WEEK 5
                                            </th>
                                        </tr>
                                        <tr>
                                            <!-- OKTOBER -->
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                <th style="text-align:center; background-color: darkseagreen;">ACHIEVED</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($getTargetWeekFilterBowheer as $data):
                                            $target = $data['TOTAL TARGET OKTOBER'];
                                            $achiev = $data['TOTAL ACHIEVED OKTOBER'];
                                            $deviasi = $target - $achiev;
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                                <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format($data['TOTAL TARGET OKTOBER']) : '-') ?>

                                                    <!-- OKTOBER -->
                                                <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format($data['TW1 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format($data['RW1 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format($data['TW2 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format($data['RW2 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format($data['TW3 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format($data['RW3 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format($data['TW4 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format($data['RW4 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format($data['TW5 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format($data['RW5 OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format($data['TOTAL ACHIEVED OKTOBER']) : '-') ?>
                                                </td>
                                                <td><?= ($deviasi != 0 ? number_format($deviasi) : '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <?php for ($i = 0; $i < 13; $i++): ?>
                                                <th>0</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">
                            TARGET INVOICE NOVEMBER
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="clearfix hidden-md-up"></div>


                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h3 class="card-title">LIST DASHBOARD </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                            data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body table-responsive">
                                <table id="tabel_targetbowheer_filter_city2" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th rowspan="3"
                                                style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                No
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                PROJECT
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL TARGET
                                            </th>
                                            <th colspan="8"
                                                style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                NOVEMBER
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL ACHIEVED
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                DEVIASI
                                            </th>
                                        </tr>
                                        </tr>

                                        <tr>
                                            <!-- NOVEMBER -->
                                            <th colspan="2"
                                                style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                WEEK 1</th>
                                            <th colspan="2"
                                                style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                WEEK 2</th>
                                            <th colspan="2"
                                                style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                WEEK 3</th>
                                            <th colspan="2"
                                                style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                WEEK 4</th>
                                        </tr>

                                        <tr>
                                            <!-- Subheader TARGET & ACHIEVED -->
                                            <?php for ($i = 0; $i < 4; $i++): ?>
                                                <th style="text-align:center; background-color: indianred; color: #ffffff;">
                                                    TARGET</th>
                                                <th
                                                    style="text-align:center; background-color: darkseagreen; color: #000000;">
                                                    ACHIEVED</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($getTargetWeekFilterBowheer as $data):
                                            $target = $data['TOTAL TARGET NOVEMBER'];
                                            $achiev = $data['TOTAL ACHIEVED NOVEMBER'];
                                            $deviasi = $target - $achiev;

                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                                <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format($data['TOTAL TARGET NOVEMBER']) : '-') ?>
                                                </td>

                                                <!-- NOVEMBER -->
                                                <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format($data['TW1 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format($data['RW1 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format($data['TW2 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format($data['RW2 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format($data['TW3 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format($data['RW3 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format($data['TW4 NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format($data['RW4 NOVEMBER']) : '-') ?>
                                                </td>

                                                <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format($data['TOTAL ACHIEVED NOVEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($deviasi != 0 ? number_format($deviasi) : '-') ?></td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <?php for ($i = 0; $i < 11; $i++): ?>
                                                <th>0</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">
                            TARGET INVOICE DESEMBER
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="clearfix hidden-md-up"></div>


                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h3 class="card-title">LIST DASHBOARD </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                            data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body table-responsive">
                                <table id="tabel_targetbowheer_filter_city3" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th rowspan="3"
                                                style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                No</th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                PROJECT</th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL TARGET</th>
                                            <th colspan="4" style="text-align:center; background-color: aquamarine;">
                                                DESEMBER</th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                TOTAL ACHIEVED
                                            </th>
                                            <th rowspan="3"
                                                style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                DEVIASI
                                            </th>
                                        </tr>
                                        <tr>
                                            <!-- DESEMBER -->
                                            <th colspan="2" style="text-align:center; background-color: aquamarine;">
                                                WEEK 1</th>
                                            <th colspan="2" style="text-align:center; background-color: aquamarine;">
                                                WEEK 2</th>
                                        </tr>
                                        <tr>
                                            <!-- DESEMBER -->
                                            <?php for ($i = 0; $i < 2; $i++): ?>
                                                <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                <th style="text-align:center; background-color: darkseagreen;">ACHIEVED</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        foreach ($getTargetWeekFilterBowheer as $data):
                                            $target = $data['TOTAL TARGET DESEMBER'];
                                            $achiev = $data['TOTAL ACHIEVED DESEMBER'];
                                            $deviasi = $target - $achiev;
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                                <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format($data['TOTAL TARGET DESEMBER']) : '-') ?>
                                                    <!-- DESEMBER -->
                                                <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format($data['TW1 DESEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format($data['RW1 DESEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format($data['TW2 DESEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format($data['RW2 DESEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format($data['TOTAL ACHIEVED DESEMBER']) : '-') ?>
                                                </td>
                                                <td><?= ($deviasi != 0 ? number_format($deviasi) : '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <?php for ($i = 0; $i < 7; $i++): ?>
                                                <th>0</th>
                                            <?php endfor; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>


                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
        </section>

    </div>
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
        $('#tabel_targetbowheer_filter_city1').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_targetbowheer_filter_city2').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_targetbowheer_filter_city3').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
    });

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        const table = $('#tabel_targetbowheer_filter_city').DataTable({
            footerCallback: function () {
                updateTotal();
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
            ],
            order: [[1, 'asc']] // Urut default kolom Kode Aset
        });

        table.on('order.dt search.dt', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        // Fungsi untuk menghitung total dari data yang tampil
        function updateTotal() {

            const data = table.rows({
                search: 'applied'
            }).data();



            // Hitung total dari kolom Value (index 2)
            let totalTargetInvoiceBowheer = 0;
            let totalAchievedInvoiceBowheer = 0;
            let totalSisaInvoiceBowheer = 0;

            data.each(function (row) {

                totalTargetInvoiceBowheer += parseFloat(row[4].replace(/\./g, '')) || 0;
                totalAchievedInvoiceBowheer += parseFloat(row[5].replace(/\./g, '')) || 0;
                totalSisaInvoiceBowheer += parseFloat(row[6].replace(/\./g, '')) || 0;
            });

            document.getElementById('totalTargetInvoiceBowheer').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
            document.getElementById('totalAchievedInvoiceBowheer').innerText = totalAchievedInvoiceBowheer.toLocaleString('id-ID');
            document.getElementById('totalSisaInvoiceBowheer').innerText = totalSisaInvoiceBowheer.toLocaleString('id-ID');
        }

        // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali saat tabel dimuat
        updateTotal();
    });


</script>




<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardlistarea.js"></script>
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/sparklines/sparkline.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/jquery.vmap.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/maps/jquery.vmap.usa.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-knob/jquery.knob.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/moment/moment.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js">
<link rel="stylesheet"
    href="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/summernote/summernote-bs4.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/demo.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/pages/dashboard.js">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">