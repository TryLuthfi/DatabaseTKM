<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$option_aktif = ['AKTIF', 'HILANG', 'TERJUAL'];
foreach ($getTargetCityFilterBowheer as $data):
    $judul = "DETAIL TARGET INVOICE - ". $data['nama_bowheer'];
endforeach;

$total = 1;
?>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">
                            <?= $judul ?>
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
                            <div class="card-body table-scrollable">
                                <table id="tabel_targetbowheer_filter_city" class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>REGIONAL</th>
                                            <th>KOTA</th>
                                            <th>PIC AREA</th>
                                            <th>TARGET INVOICE</th>
                                            <th>ACHIEVED INVOICE</th>
                                            <th>OUTSTANDING</th>
                                            <th>DETAIL</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getTargetCityFilterBowheer as $data): ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['regional_target'] ?></td>
                                                <td><?= $data['area_target'] ?></td>
                                                <td><?= $data['pic_target'] ?></td>
                                                <td><?php if ($data['total_target'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                } ?></td>
                                                </td>
                                                <td><?php
                                                if ($data['total_achiev'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_achiev']), 0, ",", ".");
                                                }
                                                ?></td>
                                                <td><?php
                                                if ($data['deviasi'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['deviasi']), 0, ",", ".");
                                                }
                                                ?></td>
                                                <td>
                                                    <a href="#" class="btn btn-primary"
                                                        data-target="#modal-view-Office<?= $data['id_target_invoice'] ?>"
                                                        data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">Total</th>
                                            <th><span id="totalTargetInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalAchievedInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalSisaInvoiceBowheer">0</span>
                                            </th>
                                            <th colspan="1"></span>
                                            </th>
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
            $('#tabel_targetbowheer_filter_city').DataTable({
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