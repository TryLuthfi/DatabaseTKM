<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <section class="content-header">
    <h1>DASHBOARD KOS</h1>
  </section>

  <div class="container-fluid">
    <section class="content">

      <div class="row">
        <!-- Kamar Terisi -->
        <div class="col-md-3">
          <div class="small-box" style="background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff;">
            <div class="inner">
              <p>Kamar Terisi</p>
              <h3 style="margin-top: -2.5%;"><?= $rooms_occupied ?></h3>
              <p style="margin-top: -1.5%;">Jumlah Penghuni <?= $total_active_tenants ?></p>
            </div>
            <div class="icon">
              <i class="fas fa-bed"></i>
            </div>
          </div>
        </div>

        <!-- Kamar Kosong -->
        <div class="col-md-3">
          <div class="small-box" style="background: linear-gradient(135deg, #f7971e, #ffd200); color: #fff;">
            <div class="inner">
              <p>Kamar Kosong</p>
              <h3 style="margin-top: -2.5%;"><?= $rooms_empty ?></h3>
              <p style="margin-top: -1.5%;">dari <?= $total_rooms ?> Kamar</p>
            </div>
            <div class="icon">
              <i class="fas fa-door-open"></i>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>

  <?php $this->session->set_flashdata('status', 'kosong'); ?>


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
  <link rel="stylesheet"
    href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
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
  <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHTenantsRooms.js"></script>

  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">