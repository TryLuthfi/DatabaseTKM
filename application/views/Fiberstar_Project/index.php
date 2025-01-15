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
$jumlah_hp = 0;
$jumlah_tiang = 0;
$jumlah_kabel = 0;
$jumlah_fat = 0;

foreach ($rincian as $data):

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

      <div class="row">
        <div class="col-md-12" style="margin_botttom:10px;">
          <!-- DIRECT CHAT DANGER -->
          <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
            <div class="card-header">
              <h3 class="card-title">ACHIEVED PO & INVOICE</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>

            <div class="card-body" style="margin-top:10px;">
              <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Pengeluaran") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                            class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">TARGET RKAP 2025</span>
                          <span class="info-box-number">
                            Rp. 345.000.000.000
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Pengeluaran") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                            class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">ACHIEVE PO 2025</span>
                          <span class="info-box-number">
                            Rp. 4.773.699.535 ( 1.38% )
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Laporan") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">ACHIEVE INVOICE 2025</span>
                          <span class="info-box-number">
                            Rp. 0 ( 0% )
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Laporan") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">SISA INVOICE 2025</span>
                          <span class="info-box-number">
                            Rp. 4.773.699.535 ( 100% )
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                </div>


              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="container-fluid">

      <div class="row">
        <div class="col-md-12" style="margin_botttom:10px;">
          <!-- DIRECT CHAT DANGER -->
          <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
            <div class="card-header">
              <h3 class="card-title">STAGGING PROJECT</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>

            <div class="card-body" style="margin-top:10px;">
              <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <h3>820 HP</h3>

                        <p>ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                      <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <div class="container-fluid">
      <h5 class="mb-12" style="text-align: center; margin-bottom:15px; margin-top:10px;">RFS GRAFIK</h5>
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
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
                <canvas id="fiberstar_chart_line" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary"></i> Achieved RFS
                </span>

                <span>
                  <i class="fas fa-square text-gray"></i> Target RFS
                </span>
              </div>
            </div>
          </div>
          <!-- /.card -->
        </div>

        <div class="col-lg-6">
          <div class="card">
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
                  <span>TOP AREA</span>
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
                <canvas id="fiberstar_chart_bar" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary"></i> Achieved RFS
                </span>

                <span>
                  <i class="fas fa-square text-gray"></i> Target RFS
                </span>
              </div>
            </div>
          </div>

        </div>
        <!-- /.col-md-6 -->
      </div>
    </div>
  </section>


  <div class="container-fluid">
    <h5 class="mb-12" style="text-align: center; margin-bottom:15px; margin-top:10px;">TABEL DATA</h5>
  </div>



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
                  <h3 class="card-title">List Cleanlist Cluster</h3>
                </div>
                <div class="col-5">
                  <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah-manual"
                    data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
                </div>
                <div class="col-1">
                  <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah-file"
                    data-toggle="modal">File &nbsp;<i class="fas fa-plus"></i> </a>
                </div>
              </div>

            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive text-nowrap ">
              <table id="tabel_pemasukan" class="table table-bordered">
                <thead class="thead-dark">
                  <tr>
                    <th>No</th>
                    <th>Regional</th>
                    <th>Area</th>
                    <th>PIC</th>
                    <th>Access ID</th>
                    <th>Access Name</th>
                    <th>HP Plan</th>
                    <th>Nomor PO</th>
                    <th>Tanggal PO</th>
                    <th>Nilai PO</th>
                    <th>Canvasing</th>
                    <th>Status BAK</th>
                    <th>Status CBN</th>
                    <th>Nomor SPK</th>
                    <th>HP SPK</th>
                    <th>Status HLD</th>
                    <th>Status LLD</th>
                    <th>KOM</th>
                    <th>PKS</th>
                    <th>Status Implementasi</th>
                    <th>RFS</th>
                    <th>ATP</th>
                    <th>Stagging</th>
                    <th>Done Invoice</th>
                    <th>Sisa Invoice</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  foreach ($main_data as $data):

                    // $jumlah_hp += $data['homepass_cluster_fiberstar'];
                    // $jumlah_tiang += $data['realisasi_tiang_project_fiberstar_progress'];
                    // $jumlah_kabel += $data['realisasi_kabel_project_fiberstar_progress'];
                    // $jumlah_fat += $data['realisasi_fat_project_fiberstar_progress'];
                  
                    ?>

                    <tr>
                      <td><?= $total++ ?></td>
                      <td><?= $data['regional_project'] ?></td>
                      <td><?= $data['area_project'] ?></td>
                      <td><?= $data['pic_project'] ?></td>
                      <td><?= $data['access_id_project'] ?></td>
                      <td><?= $data['access_name_project'] ?></td>
                      <td><?= $data['hpplan_project'] ?></td>
                      <td><?= $data['number_po'] ?></td>
                      <td><?= $data['tanggal_po'] ?></td>
                      <td><?= $data['nilai_awal_po'] ?></td>
                      <td><?= $data['tgl_canvasing'] ?></td>
                      <td><?= $data['status_bak'] ?></td>
                      <td><?= $data['status_cbn'] ?></td>
                      <td><?= $data['spk_nomor'] ?></td>
                      <td><?= $data['spk_hp'] ?></td>
                      <td><?= $data['status_hld'] ?></td>
                      <td><?= $data['status_lld'] ?></td>
                      <td><?= $data['tgl_kom'] ?></td>
                      <td><?= $data['tgl_pks'] ?></td>
                      <td><?= $data['status_implementasi'] ?></td>
                      <td><?= $data['tanggal_rfs'] ?></td>
                      <td><?= $data['tanggal_atp'] ?></td>
                      <td><?= $data['main_status'] ?></td>
                      <td><?= $data['total_invoice'] ?></td>
                      <td><?= $data['total_sisa_invoice'] ?></td>

                      <td>
                        <a href="<?php echo site_url('Fiberstar/delete/' . $data['primary_access_id_project']); ?>"
                          id="tombol_hapus" class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                        <a href="#" class="btn btn-warning"
                          data-target="#modal-lg-edit<?= $data['primary_access_id_project'] ?>" data-toggle="modal"><i
                            class="fas fa-edit"></i></a>
                      </td>
                    </tr>

                  <?php endforeach; ?>

                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="4">Total</th>
                    <th colspan="25"></th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

          <!-- modal untuk tambah data -->
          <form action=" <?php echo base_url('Fiberstar/add') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-manual">
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
                      <input type="text" class="form-control" name="kode_provider" autocomplete="off"
                        value="PT. Fiberstar">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Nomor PO</label>
                      <input type="text" class="form-control" name="nomor_po" autocomplete="off" placeholder="00000000">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Nilai PO</label>
                      <input type="number" class="form-control" name="nilai_po" id="nilai_po2" autocomplete="off"
                        placeholder="Rp. 000.000.000">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Tanggal PO</label>
                      <input type="date" class="form-control" name="tanggal_po" autocomplete="off"
                        value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Term Of Payment</label>
                      <select name="versi_po" class="form-control">
                        <option value="100%">100%</option>
                        <option value="50% ; 50%">50% ; 50%</option>
                        <option value="20% ; 20% ; 25% ; 25% ; 10%">20% ; 20% ; 25% ; 25% ; 10%</option>
                        <option value="top4">20% ; 20% ; 25% ; 25% ; 10%</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Versi PO</label>
                      <select name="kode_po" class="form-control">
                        <option value="NEW">NEW PO</option>
                        <option value="FINAL">FINAL PO</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">STATUS PO</label>
                      <select name="status_po" class="form-control">
                        <option value="ACTIVE">AKTIF</option>
                        <option value="NONACTIVE">NONAKTIF</option>
                      </select>
                    </div>


                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                      <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i
                          class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                    </div>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
          </form>

          <div class="modal fade" id="modal-lg-tambah-file">
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
                    <label for="exampleInputFile">File input</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                      </div>
                      <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>

          <!-- modal untuk edit data -->
          <?php $tgl = date('Y-m-d'); ?>
          <?php foreach ($rincian as $data):
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
                        <input type="text" class="form-control" name="kode_akun" autocomplete="off"
                          value="<?= $data['kode_akun'] ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Nama Akun</label>
                        <input type="text" class="form-control" name="nama_kode" autocomplete="off"
                          value="<?= $data['nama_kode'] ?>">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                        <button type="submit" name="btnEdit" class="btn btn-primary"><i
                            class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
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
<script type="text/javascript"
  src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/bsCustomFileInput/bsCustomFileInput.min.js"></script>
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
<script>
  $(function () {
    bsCustomFileInput.init();
  });
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

<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartfibertstar.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartmyrep.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardrkap.js"></script>