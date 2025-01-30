<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$nilai_po = 0;
$target_rkap = 345000000000;
$persentase_po = 0;
$nilai_invoice = 0;
$sisa_invoice = 0;
$total = 1;
$totalHpFiberstar = 0;
$totalPoFiberstar = 0;
$totalDoneInvoicePoFiberstar = 0;
$totalSisaInvoicePoFiberstar = 0;

foreach ($list_po as $data):
    $totalHpFiberstar += $data['hpplan_project'];
    $totalPoFiberstar += $data['nilai_awal_po'];
    $totalDoneInvoicePoFiberstar += $data['total_invoice'];
    $totalSisaInvoicePoFiberstar += $data['total_sisa_invoice'];
endforeach;

$persentase_po_to_rkap = ($totalPoFiberstar / $target_rkap) * 100;
$persentase_invoice_to_po = ($totalDoneInvoicePoFiberstar / $totalPoFiberstar) * 100;
$persentase_sisa_invoice_to_po = ($totalSisaInvoicePoFiberstar / $totalPoFiberstar) * 100;

?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= $judul ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <a>
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i
                                    class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span style="color:blue" class="info-box-text">TARGET RKAP 2024</span>
                                <span style="color:blue" class="info-box-number">
                                    <?= "Rp. " . number_format($target_rkap, 0, ".") ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span style="color:blue" class="info-box-text">ACHIEVED PO 2024</span>
                                <span style="color:blue" class="info-box-number">
                                    <?= "Rp. " . number_format($totalPoFiberstar, 0, ".") . " ( " . round($persentase_po_to_rkap, 2) . "% ) " ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12 col-sm-6 col-md-3">
                    <a>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span style="color:blue" class="info-box-text">ACHIEVE INVOICE 2025</span>
                                <span style="color:blue" class="info-box-number">
                                    <?= "Rp. " . number_format($totalDoneInvoicePoFiberstar, 0, ".") . " ( " . round($persentase_invoice_to_po, 2) . "% ) " ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <a>
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-danger elevation-1"><i
                                    class="fas fa-search-dollar"></i></span>
                            <div class="info-box-content">
                                <span style="color:blue" class="info-box-text">SISA INVOICE 2025</span>
                                <span style="color:blue" class="info-box-number">
                                    <?= "Rp. " . number_format($totalSisaInvoicePoFiberstar, 0, ".") . " ( " . round($persentase_sisa_invoice_to_po, 2) . "% ) " ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
            <div class="row">
            </div>
        </div>
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">List PO Fiberstar</h3>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Kota</th>
                                        <th>ID Cluster</th>
                                        <th>Nama Cluster</th>
                                        <th>HP</th>
                                        <th>Nomor PO</th>
                                        <th>Nilai PO</th>
                                        <th>Done Invoice</th>
                                        <th>Sisa Invoice</th>
                                        <th>Stagging</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php


                                    foreach ($list_po as $data):
                                        ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['regional_project'] ?></td>
                                            <td><?= $data['area_project'] ?></td>
                                            <td><?= $data['access_id_project'] ?></td>
                                            <td><?= $data['access_name_project'] ?></td>
                                            <td><?= number_format($data['hpplan_project'], 0, ',', '.') ?></td>
                                            <td><?= $data['number_po'] ?></td>
                                            <td><?= number_format($data['nilai_awal_po'], 0, ',', '.') ?></td>
                                            <td><?= number_format($data['total_invoice'], 0, ',', '.') ?></td>
                                            <td><?= number_format($data['total_sisa_invoice'], 0, ',', '.') ?></td>
                                            <td><?= $data['main_status'] ?></td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5">Total</th>
                                        <th colspan="1"><?= number_format($totalHpFiberstar, 0, ',', '.') ?></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($totalPoFiberstar, 0, ',', '.') ?></th>
                                        <th colspan="1"><?= number_format($totalDoneInvoicePoFiberstar, 0, ',', '.') ?>
                                        </th>
                                        <th colspan="1"><?= number_format($totalSisaInvoicePoFiberstar, 0, ',', '.') ?>
                                        </th>
                                        <th colspan="1"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->


                    <!-- COBA PANGGIL DATA MSQL -->
                    <div class="row">
                        <!-- ISI -->
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
<!-- /.control-sidebar -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript"
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>


<script>
    $('#1').datepicker({
        inputs: $('input[name=tanggal_berangkat]'),
        format: 'dd/mm/yyyy'
    })
    $('#2').datepicker({
        inputs: $('input[name=utanggal_berangkat]'),
        format: 'dd/mm/yyyy'
    })
</script>
<script type="text/javascript">
    $(function () {

        // format angka rupiah
        $('[data-mask]').inputmask("currency", {
            prefix: " Rp. ",
            digitsOptional: true
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

    });

    $('.tombol_hapus').on('click', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        swal({
            title: 'Apakah anda yakin',
            text: "data akan dihapus!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.value) {
                document.location.href = href;
            }
        })

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {

        // Format mata uang.
        $('.nilai_po2').mask('000.000.000', { reverse: true });

    })
</script>

<script>
    // Membuat circle progress bar
    var bar = new ProgressBar.Circle('#progress-bar-container', {
        color: '#FF5733', // Warna progress bar
        strokeWidth: 10, // Ketebalan garis
        trailWidth: 10,  // Ketebalan garis latar belakang
        easing: 'easeInOut',  // Animasi progress bar
        duration: 1400,  // Durasi animasi dalam milidetik
        from: { color: '#ddd', width: 10 },
        to: { color: '#FF5733', width: 10 },
        step: function (state, circle) {
            circle.path.setAttribute('stroke', state.color);
            circle.path.setAttribute('stroke-width', state.width);
            var value = Math.round(circle.value() * 100);
            circle.setText(value + '%');
        }
    });

    // Mengatur nilai progress bar
    bar.animate(<?= $persentase_po ?>);  // Nilai antara 0.0 hingga 1.0 (70% dalam contoh ini)
</script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- daterange picker -->
<!-- <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js">
</script>
<!-- jQuery UI 1.11.4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.js">
</script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js">
<!-- ChartJS -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js">
<!-- Sparkline -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/sparklines/sparkline.js">
<!-- JQVMap -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/jquery.vmap.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/maps/jquery.vmap.usa.js">
<!-- jQuery Knob Chart -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-knob/jquery.knob.min.js">
<!-- daterangepicker -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/moment/moment.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet"
    href="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js">
<!-- Summernote -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/summernote/summernote-bs4.min.js">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js">
<!-- AdminLTE for demo purposes -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/demo.js">
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/pages/dashboard.js">

<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardlistarea.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">