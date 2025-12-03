<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-12 ">
          <h1 class="m-0 text-dark" style="text-align: center;">DASHBOARD GULL HOUSE</h1>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Info boxes -->
      <div class="row">
        <!-- /.col -->

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

        <div class="col-md-3">
          <div class="small-box" style="background: linear-gradient(135deg, #357e14ff, #00ff00ff); color: #fff;">
            <div class="inner">
              <p>Pemasukan</p>
              <h3 style="margin-top: -2.5%;">Rp. 3.000.000,-</h3>
              <p style="margin-top: -1.5%;">Desember 2022</p>
            </div>
            <div class="icon">
              <i class="fas fa-door-open"></i>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="small-box" style="background: linear-gradient(135deg, #f10510ff, #be1717ff); color: #fff;">
            <div class="inner">
              <p>Pengeluaran</p>
              <h3 style="margin-top: -2.5%;">Rp. 1.000.000,-</h3>
              <p style="margin-top: -1.5%;">Desember 2022</p>
            </div>
            <div class="icon">
              <i class="fas fa-door-open"></i>
            </div>
          </div>
        </div>


      </div>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12" >
          <div class="card" style="margin-bottom: 0 !important;">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title">Achieved RFS Week - 01 </h3>
                <a href="javascript:void(0);">View Report</a>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                  <span class="text-bold text-lg">18.280 HP</span>
                  <span>TOP Area</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> 80.1%
                  </span>
                  <span class="text-muted">Week - 01</span>
                </p>
              </div>
              <!-- /.d-flex -->

              <div class="position-relative mb-4">
                <canvas id="rkap_chart_line" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-3">
                  <i class="fas fa-square text-primary"></i> Target RKAP
                </span>

                <span class="mr-3">
                  <i class="fas fa-square text-green"></i> Achieved PO
                </span>

                <span class="mr-3">
                  <i class="fas fa-square text-orange"></i> Achieved Invoice
                </span>

              </div>
            </div>
          </div>
          <!-- /.card -->
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 nowrapright">
          <div class="small-box noradiusright" style="background: #0e87acff; color: #fff;">
            <div class="inner" style="text">
              <p style="visibility: hidden;">hidden text</p>
              <h3 style="margin-top: -2.5%; text-align: center;">8</h3>
              <p style="margin-top: -1.5%; text-align: center;">Penghuni Masuk Tahun Ini</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 nowrap">
          <div class="small-box noradius" style="background: #b6b387ff; color: #fff;">
            <div class="inner" style="text">
              <p style="visibility: hidden;">hidden text</p>
              <h3 style="margin-top: -2.5%; text-align: center;">Rp. 1,-</h3>
              <p style="margin-top: -1.5%; text-align: center;">Penghuni Keluar Tahun Ini</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 nowrap">
          <div class="small-box noradius" style="background: #84af84ff; color: #fff;">
            <div class="inner" style="text">
              <p style="visibility: hidden;">hidden text</p>
              <h3 style="margin-top: -2.5%; text-align: center;">Rp. 3.000.000,-</h3>
              <p style="margin-top: -1.5%; text-align: center;">Pemasukan Tahun Ini</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 nowrapleft">
          <div class="small-box noradiusleft" style="background: #9e5682ff; color: #fff;">
            <div class="inner" style="text">
              <p style="visibility: hidden;">hidden text</p>
              <h3 style="margin-top: -2.5%; text-align: center;">Rp. 31.000.000,-</h3>
              <p style="margin-top: -1.5%; text-align: center;">Pengeluaran Tahun Ini</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>
  <!-- /.content -->



</div>
<!-- /.content-wrapper -->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Theme style -->
<script src="<?= base_url('assets') ?>dist/js/adminlte.js"></script>
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
<!-- AdminLTE App -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/adminlte.js">
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
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartfibertstar.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartmyrep.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardrkap.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

<style>

.col-md-3.nowrap {
    padding-left: 0 !important;
    padding-right: 0 !important;
}

.col-md-3.nowrapright {
    padding-right: 0 !important;
}

.col-md-3.nowrapleft {
    padding-left: 0 !important;
}

.small-box.noradius {
    border-radius: 0 !important;
}

.small-box.noradiusright {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

.small-box.noradiusleft {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
</style>
