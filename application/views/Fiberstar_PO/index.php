<?php
      $status = $this->session->flashdata('status');
      $error_log = $this->session->flashdata('error_log');
      $nilai_po = 0;
      $target_rkap = 0;
      $persentase_po = 0;
      $nilai_invoice = 0;
      $sisa_invoice = 0;
      $total = 1;
      $total_nilai_invoice = 0;

      foreach ($list_bowheer as $data_list_bowheer) :
        if ($data_list_bowheer['nama_bowheer'] == 'PT. FIBERSTAR') {
          $target_rkap += $data_list_bowheer['target_bowheer'];
        }
      endforeach;

      foreach ($list_po as $data_list_po) :
          $nilai_po += $data_list_po['nilai_po_project_fiberstar_po'];
      endforeach;

      $sisa_invoice = $nilai_po - $nilai_invoice;
      $persentase_po_to_rkap = ( $nilai_po / $target_rkap) * 100;
      $persentase_invoice_to_po = ( $nilai_invoice / $nilai_po ) * 100;
      $persentase_sisa_invoice_to_po = ( $sisa_invoice / $nilai_po ) * 100;

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
                  <span style="color:blue" class="info-box-text">TARGET RKAP 2024</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($target_rkap,0,".")  ?>
                  </span>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                   <span style="color:blue" class="info-box-text">ACHIEVED PO 2024</span>
                   <span style="color:blue" class="info-box-number">
                     <?= "Rp. ".number_format($nilai_po,0,".")." ( ".round($persentase_po_to_rkap,2)."% ) " ?>
                   </span>
                </div>
              </div>
            </a>
          </div>
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">ACHIEVE INVOICE 2025</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($total_nilai_invoice,0,".")." ( ".round($persentase_invoice_to_po,2)."% ) "  ?>
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <a>
              <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-search-dollar"></i></span>
                <div class="info-box-content">
                  <span style="color:blue" class="info-box-text">SISA INVOICE 2025</span>
                  <span style="color:blue" class="info-box-number">
                    <?= "Rp. ".number_format($sisa_invoice,0,".")." ( ".round($persentase_sisa_invoice_to_po,2)."% ) "  ?>
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
                                <h3 class="card-title">List Cleanlist Cluster</h3>
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
                                        <th>Kota</th>
                                        <th>PO</th>
                                        <th>ID Cluster</th>
                                        <th>Nama Cluster</th>
                                        <th>HP</th>
                                        <th>Nilai PO</th>
                                        <th>Done Invoice</th>
                                        <th>Sisa Invoice</th>
                                        <th>Stagging</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php


                                    foreach ($list_po as $data) :
                                     ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['kota_area'] ?></td>
                                            <td><?= $data['id_po_project_fiberstar_po'] ?></td>
                                            <td><?= $data['id_cluster_fiberstar'] ?></td>
                                            <td><?= $data['nama_cluster_fiberstar'] ?></td>
                                            <td><?= $data['homepass_cluster_fiberstar'] ?></td>
                                            <td><?= number_format($data['nilai_po_project_fiberstar_po'], 0, ',', '.')?></td>
                                            <td><?= $nilai_invoice ?></td>
                                            <td><?= number_format($sisa_invoice, 0, ',', '.')?></td>
                                            <td><?= $data['stagging_project_fiberstar_stagging'] ?></td>
                                            <td>
                                                <a href="<?php echo site_url('Fiberstar/delete/' . $data['id_project_fiberstar']); ?>" id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                                                <a href="#" class="btn btn-warning" data-target="#modal-lg-edit<?= $data['id_project_fiberstar'] ?>" data-toggle="modal"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6">Total</th>
                                        <th colspan="1"><?= number_format($nilai_po, 0, ',', '.') ?></th>
                                        <th colspan="1"><?= number_format($nilai_invoice, 0, ',', '.') ?></th>
                                        <th colspan="1"><?= number_format($sisa_invoice, 0, ',', '.') ?></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

            <!-- modal untuk tambah data -->
            <form action=" <?php echo base_url('Fiberstar/add') ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Tambah Purcase Order</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Provider</label>
                                    <input type="text" class="form-control" name="kode_provider" autocomplete="off" value="PT. Fiberstar">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Nomor PO</label>
                                    <input type="text" class="form-control" name="nomor_po" autocomplete="off" placeholder="00000000">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Nilai PO</label>
                                    <input type="number" class="form-control" name="nilai_po" id="nilai_po2" autocomplete="off" placeholder="Rp. 000.000.000">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Tanggal PO</label>
                                    <input type="date" class="form-control" name="tanggal_po" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d');?>">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Term Of Payment</label>
                                    <select name="versi_po" class="form-control" >
                                      <option value="100%">100%</option>
                                      <option value="50% ; 50%">50% ; 50%</option>
                                      <option value="20% ; 20% ; 25% ; 25% ; 10%">20% ; 20% ; 25% ; 25% ; 10%</option>
                                      <option value="top4">20% ; 20% ; 25% ; 25% ; 10%</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Versi PO</label>
                                    <select name="kode_po" class="form-control" >
                                      <option value="NEW">NEW PO</option>
                                      <option value="FINAL">FINAL PO</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">STATUS PO</label>
                                    <select name="status_po" class="form-control" >
                                      <option value="ACTIVE">AKTIF</option>
                                      <option value="NONACTIVE">NONAKTIF</option>
                                    </select>
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
            <?php foreach ($list_po as $data) :
            ?>
                <form action="<?php echo site_url('Kodeakun/edit'); ?>" method="post">
                    <div class="modal fade" id="modal-lg-edit<?= $data['id_project_fiberstar'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Rincian <?= $data['id_kode'] ?></h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="col-form-label">Kode Akun</label>
                                        <input type="text" class="form-control" name="kode_akun" autocomplete="off" value="<?= $data['kode_akun'] ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Nama Akun</label>
                                        <input type="text" class="form-control" name="nama_kode" autocomplete="off" value="<?= $data['nama_kode'] ?>">
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
