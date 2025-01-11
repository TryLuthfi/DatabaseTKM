<!-- LIST BOWHEER -->

<?php
      $status = $this->session->flashdata('status');
      $error_log = $this->session->flashdata('error_log');
      $nilai_po = 0;
      $rkap = 345000000000;
      $persentase_po = 0;
      $nilai_invoice = 0;
      $sisa_invoice = 0;
      $total = 1;
      $total_nilai_invoice = 0;
      $total_sisa_invoice = 0;
      $total_target = 0;
      $total_adi = 0;
      $total_wardani = 0;
      $total_yaya = 0;
      $total_zaenul = 0;

      foreach ($rincian_bowheer as $data) :
        if ($data['nama_user'] == 'ADHI') {
          $total_adi += $data['target_bowheer'];
        } else if ($data['nama_user'] == 'WARDANI SETIAWAN') {
          $total_wardani += $data['target_bowheer'];
        } else if ($data['nama_user'] == 'YAYA SUNARYA') {
          $total_yaya += $data['target_bowheer'];
        } else if ($data['nama_user'] == 'ZAENUL ARIFIN') {
          $total_zaenul += $data['target_bowheer'];
        }
      endforeach;
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
                <span class="info-box-icon bg-info elevation-1"><img src="<?= base_url('assets') ?>/dist/img/user8-128x128.jpg" class="img-round elevation-2" alt="User Image"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">BP. ADHI</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($total_adi,0,".") ?>
                  </span>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><img src="<?= base_url('assets') ?>/dist/img/user6-128x128.jpg" class="img-round elevation-2" alt="User Image"></i></span>
                <div class="info-box-content">
                   <span style="color:blue" class="info-box-text">BP. WARDANI SETIAWAN</span>
                   <span style="color:blue" class="info-box-number">
                     <?= "Rp. ".number_format($total_wardani,0,".") ?>
                   </span>
                </div>
              </div>
            </a>
          </div>
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><img src="<?= base_url('assets') ?>/dist/img/user1-128x128.jpg" class="img-round elevation-2" alt="User Image"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">BP. YAYA SUNARYA</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($total_yaya,0,".") ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><img src="<?= base_url('assets') ?>/dist/img/user2-160x160.jpg" class="img-round elevation-2" alt="User Image"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">BP. ZAENUL ARIFIN</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($total_zaenul,0,".") ?>
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
                            <div  class="col-6">
                                <h3 class="card-title">List Bowheer PT. TKM</h3>
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
                                        <th>Bowheer</th>
                                        <th>PIC</th>
                                        <th>Target</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($rincian_bowheer as $data) :
                                      $total_target += $data['target_bowheer']
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['nama_bowheer'] ?></td>
                                            <?php if($data['nama_user'] == null) { ?>
                                              <td> - </td>
                                            <?php } else { ?>
                                              <td><?= $data['nama_user']?></td>
                                            <?php } ?>
                                            <td><?= "Rp. ".number_format($data['target_bowheer'],0,".") ?></td>
                                            <td>
                                                <a href="<?php echo site_url('ListBowheer/delete/' . $data['id_bowheer']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_bowheer'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                  <tr>
                                      <th colspan="1">Total</th>
                                      <th colspan="2"><?= number_format($total-1, 0, ',', '.') ?></th>
                                      <th colspan="2"><?= "Rp. ".number_format($total_target,0,".") ?></th>
                                  </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('ListBowheer/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah List Bowheer</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Nama Bowheer</label>
                                    <input type="text" class="form-control" name="nama_bowheer" autocomplete="off" placeholder="Nama Bowheer">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Person In Control</label>
                                    <select name="id_user" class="form-control" >
                                      <?php foreach ($list_user as $data2) : ?>
                                        <option value="<?php echo $data2['id_user']?>" > <?php echo $data2['nama_user']?></option>
                                      <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Target</label>
                                    <input type="text" class="form-control" name="target_bowheer" oninput="formatRupiah(event)" autocomplete="off" placeholder="Rp. 000.000.000">
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
            <?php foreach ($rincian_bowheer as $data) :
            ?>
                <form action="<?php echo site_url('ListBowheer/edit/'.$data['id_bowheer']); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_bowheer'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Rincian</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-form-label">Nama Bowheer</label>
                                        <input type="text" class="form-control" name="nama_bowheer" autocomplete="off" value="<?= $data['nama_bowheer'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Person In Control</label>
                                        <select name="id_user" class="form-control" >
                                          <?php foreach ($list_user as $data2) : ?>
                                            <option value="<?php echo $data2['id_user']?>" <?php if ($data2['nama_user'] == $data['nama_user']) { ?>selected <?php } ?> > <?php echo $data2['nama_user']?></option>
                                          <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Target</label>
                                        <input type="text" class="form-control" name="target_bowheer" oninput="formatRupiah(event)" autocomplete="off" value="<?= "Rp. ".number_format($data['target_bowheer'],0,".") ?>">
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
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
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
            swal("Gagal!", "".$this->session->flashdata('status'));>
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
<script>
        // Fungsi untuk format Rupiah
        function formatRupiah(event) {
            var input = event.target;
            var value = input.value.replace(/[^\d]/g, '');  // Menghapus karakter non-numeric
            var formattedValue = '';

            // Format angka untuk ribuan
            for (var i = value.length - 1; i >= 0; i--) {
                formattedValue = value.charAt(i) + formattedValue;
                if ((value.length - i) % 3 === 0 && i !== 0) {
                    formattedValue = '.' + formattedValue;
                }
            }

            // Menambahkan simbol "Rp"
            input.value = 'Rp ' + formattedValue;
        }
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
