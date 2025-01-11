<?php
      $status = $this->session->flashdata('status');
      $error_log = $this->session->flashdata('error_log');

      $total = 1;

      $total_reg_kota_1 = 0; $total_reg_kota_2 = 0; $total_reg_kota_3 = 0; $total_reg_kota_4 = 0; $total_reg_kota_5 = 0;
      $total_reg_kec_1 = 0; $total_reg_kec_2 = 0; $total_reg_kec_3 = 0; $total_reg_kec_4 = 0; $total_reg_kec_5 = 0;

      $filter_reg_1 = array_filter($area, function($item) {
        return $item['regional_area'] == 1;
      });
      $filter_reg_2 = array_filter($area, function($item) {
        return $item['regional_area'] == 2;
      });
      $filter_reg_3 = array_filter($area, function($item) {
        return $item['regional_area'] == 3;
      });
      $filter_reg_4 = array_filter($area, function($item) {
        return $item['regional_area'] == 4;
      });
      $filter_reg_5 = array_filter($area, function($item) {
        return $item['regional_area'] == 5;
      });

      $provinsi_reg_1 = array_column($filter_reg_1, 'provinsi_area');
      $provinsi_reg_2 = array_column($filter_reg_2, 'provinsi_area');
      $provinsi_reg_3 = array_column($filter_reg_3, 'provinsi_area');
      $provinsi_reg_4 = array_column($filter_reg_4, 'provinsi_area');
      $provinsi_reg_5 = array_column($filter_reg_5, 'provinsi_area');

      $kota_reg_1 = array_column($filter_reg_1, 'kota_area');
      $kota_reg_2 = array_column($filter_reg_2, 'kota_area');
      $kota_reg_3 = array_column($filter_reg_3, 'kota_area');
      $kota_reg_4 = array_column($filter_reg_4, 'kota_area');
      $kota_reg_5 = array_column($filter_reg_5, 'kota_area');

      $kecamatan_reg_1 = array_column($filter_reg_1, 'kecamatan_area');
      $kecamatan_reg_2 = array_column($filter_reg_2, 'kecamatan_area');
      $kecamatan_reg_3 = array_column($filter_reg_3, 'kecamatan_area');
      $kecamatan_reg_4 = array_column($filter_reg_4, 'kecamatan_area');
      $kecamatan_reg_5 = array_column($filter_reg_5, 'kecamatan_area');

      $unique_provinsi_reg_1 = array_unique($provinsi_reg_1);
      $unique_provinsi_reg_2 = array_unique($provinsi_reg_2);
      $unique_provinsi_reg_3 = array_unique($provinsi_reg_3);
      $unique_provinsi_reg_4 = array_unique($provinsi_reg_4);
      $unique_provinsi_reg_5 = array_unique($provinsi_reg_5);

      $unique_kota_reg_1 = array_unique($kota_reg_1);
      $unique_kota_reg_2 = array_unique($kota_reg_2);
      $unique_kota_reg_3 = array_unique($kota_reg_3);
      $unique_kota_reg_4 = array_unique($kota_reg_4);
      $unique_kota_reg_5 = array_unique($kota_reg_5);

      $unique_kecamatan_reg_1 = array_unique($kecamatan_reg_1);
      $unique_kecamatan_reg_2 = array_unique($kecamatan_reg_2);
      $unique_kecamatan_reg_3 = array_unique($kecamatan_reg_3);
      $unique_kecamatan_reg_4 = array_unique($kecamatan_reg_4);
      $unique_kecamatan_reg_5 = array_unique($kecamatan_reg_5);

      $jumlah_provinsi_reg_1 = count($unique_provinsi_reg_1);
      $jumlah_provinsi_reg_2 = count($unique_provinsi_reg_2);
      $jumlah_provinsi_reg_3 = count($unique_provinsi_reg_3);
      $jumlah_provinsi_reg_4 = count($unique_provinsi_reg_4);
      $jumlah_provinsi_reg_5 = count($unique_provinsi_reg_5);

      $jumlah_kota_reg_1 = count($unique_kota_reg_1);
      $jumlah_kota_reg_2 = count($unique_kota_reg_2);
      $jumlah_kota_reg_3 = count($unique_kota_reg_3);
      $jumlah_kota_reg_4 = count($unique_kota_reg_4);
      $jumlah_kota_reg_5 = count($unique_kota_reg_5);

      $jumlah_kecamatan_reg_1 = count($unique_kecamatan_reg_1);
      $jumlah_kecamatan_reg_2 = count($unique_kecamatan_reg_2);
      $jumlah_kecamatan_reg_3 = count($unique_kecamatan_reg_3);
      $jumlah_kecamatan_reg_4 = count($unique_kecamatan_reg_4);
      $jumlah_kecamatan_reg_5 = count($unique_kecamatan_reg_5);
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
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">REGIONAL I</span>
                  <span style="color:blue" class="info-box-number">
                    <?= number_format($jumlah_provinsi_reg_1,0,".")." Provinsi |" ?>
                    <?= number_format($jumlah_kota_reg_1,0,".")." Kota |" ?>
                    <?= number_format($jumlah_kecamatan_reg_1,0,".")." Kecamatan" ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">REGIONAL II</span>
                  <span style="color:blue" class="info-box-number">
                    <?= number_format($jumlah_provinsi_reg_2,0,".")." Provinsi |" ?>
                    <?= number_format($jumlah_kota_reg_2,0,".")." Kota |" ?>
                    <?= number_format($jumlah_kecamatan_reg_2,0,".")." Kecamatan" ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-search-dollar"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">REGIONAL III</span>
                  <span style="color:blue" class="info-box-number">
                    <?= number_format($jumlah_provinsi_reg_3,0,".")." Provinsi |" ?>
                    <?= number_format($jumlah_kota_reg_3,0,".")." Kota |" ?>
                    <?= number_format($jumlah_kecamatan_reg_3,0,".")." Kecamatan" ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-search-dollar"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">REGIONAL IV</span>
                  <span style="color:blue" class="info-box-number">
                    <?= number_format($jumlah_provinsi_reg_4,0,".")." Provinsi |" ?>
                    <?= number_format($jumlah_kota_reg_4,0,".")." Kota |" ?>
                    <?= number_format($jumlah_kecamatan_reg_4,0,".")." Kecamatan" ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-search-dollar"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">REGIONAL V</span>
                  <span style="color:blue" class="info-box-number">
                    <?= number_format($jumlah_provinsi_reg_5,0,".")." Provinsi |" ?>
                    <?= number_format($jumlah_kota_reg_5,0,".")." Kota |" ?>
                    <?= number_format($jumlah_kecamatan_reg_5,0,".")." Kecamatan" ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="container-fluid">
          <!-- Info boxes -->
          <div class="row">
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header border-0">
                  <div class="d-flex justify-content-between">
                    <h3 class="card-title">LIST KECAMATAN / REGIONAL </h3>
                    <a href="javascript:void(0);">View Report</a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="d-flex">
                    <p class="d-flex flex-column">
                      <span class="text-bold text-lg">29 Kecamatan</span>
                    </p>
                  </div>
                  <!-- /.d-flex -->

                  <div class="position-relative mb-4">
                    <canvas id="area_chart_bar" height="200"></canvas>
                  </div>

                  <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                      <i class="fas fa-square text-primary"></i> Jumlah Kecamatan
                    </span>
                  </div>
                </div>
              </div>

            </div>
          </div>
      </div>

      <div class="content-header">
          <div class="container-fluid">
              <div class="row mb-2">
                  <div class="col-sm-6">
                      <h1 class="m-0 text-dark">Distribusi Area</h1>
                  </div>
              </div>
          </div>
      </div>

        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                            <div  class="col-6">
                                <h3 class="card-title">List Area Dashboard </h3>
                            </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
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
                                        <th>Provinsi</th>
                                        <th>Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($area as $data) :
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td>Regional <?= $data['regional_area'] ?></td>
                                            <td><?= $data['provinsi_area']?></td>
                                            <td><?= $data['kota_area'] ?></td>
                                            <td><?= $data['kecamatan_area'] ?></td>
                                            <td>
                                                <a href="<?php echo site_url('ListArea/delete/' . $data['id_area']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_area'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        <th colspan="2"><?= number_format($total-1, 0, ',', '.') ?></th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('ListArea/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah List User</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Regional</label>
                                    <select name="regional_area" class="form-control" >
                                      <option value="1">Regional I</option>
                                      <option value="2">Regional II</option>
                                      <option value="3">Regional III</option>
                                      <option value="4">Regional IV</option>
                                      <option value="5">Regional V</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Provinsi</label>
                                    <input type="text" class="form-control" name="provinsi_area" autocomplete="off" placeholder="Nama Provinsi">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Kota</label>
                                    <input type="text" class="form-control" name="kota_area" autocomplete="off" placeholder="Nama Kota">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Kecamatan</label>
                                    <input type="text" class="form-control" name="kecamatan_area" autocomplete="off" placeholder="Nama Kecamatan">
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                    <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </form>

            <!-- modal untuk edit data -->
            <?php $tgl = date('Y-m-d'); ?>
            <?php foreach ($area as $data) :
            ?>
                <form action="<?php echo site_url('ListArea/edit/'.$data['id_area']); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_area'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Area</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                  <div class="form-group">
                                      <label class="col-form-label">Level</label>
                                      <select name="regional_area" class="form-control" >
                                        <option value="1" <?php if ($data['regional_area'] == '1') { ?>selected <?php } ?> >Regional I</option>
                                        <option value="2" <?php if ($data['regional_area'] == '2') { ?>selected <?php } ?> >Regional II</option>
                                        <option value="3" <?php if ($data['regional_area'] == '3') { ?>selected <?php } ?> >Regional III</option>
                                        <option value="4" <?php if ($data['regional_area'] == '3') { ?>selected <?php } ?> >Regional IV</option>
                                        <option value="5" <?php if ($data['regional_area'] == '3') { ?>selected <?php } ?> >Regional V</option>
                                      </select>
                                  </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Provinsi</label>
                                        <input type="text" class="form-control" name="provinsi_area" autocomplete="off" value="<?= $data['provinsi_area'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Kota</label>
                                        <input type="text" class="form-control" name="kota_area" autocomplete="off" value="<?= $data['kota_area'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Kecamatan</label>
                                        <input type="text" class="form-control" name="kecamatan_area" autocomplete="off" value="<?= $data['kecamatan_area'] ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                        <button type="submit" name="btnEdit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>



            <!-- COBA PANGGIL DATA MSQL -->
            <div class="row">
                <!-- ISI -->
            </div>
        </div>
    </section>
</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>
<!-- /.content-wrapper -->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->


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
    $(function() {

        // format angka rupiah
        $('[data-mask]').inputmask("currency", {
            prefix: " Rp. ",
            digitsOptional: true
        })

        // notifikasi allert sukses atau tidak
        <?php if ($status == 'sukses_tambah') { ?>
            swal("Success!", "Berhasil menambah PO!", "success");
        <?php } else if ($status == 'sukses_hapus') { ?>
            swal("Success!", "Berhasil menghapus PO!", "success");
        <?php } else if ($status == 'PO sudah ada') { ?>
            swal("Gagal!", "PO Sudah ada", "warning");
        <?php } else if ($status == 'sukses_hapus') { ?>
            swal("Success!", "Berhasil menghapus PO!", "success");>
        <?php } else if ($status == 'gagal_hapus') { ?>
            swal("Gagal!", "Gagal menghapus PO!", "warning");>
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
            $(document).ready(function(){

                // Format mata uang.
                $( '.nilai_po2' ).mask('000.000.000', {reverse: true});

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
        step: function(state, circle) {
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
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.js"></script>
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
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js">
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
