<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$total = 1;
$total_hp = 0;
$total_plan_tiang = 0;
$total_achiev_tiang = 0;
$total_plan_kabel_24C = 0;
$total_achiev_kabel_24C = 0;
$total_plan_kabel_48C = 0;
$total_achiev_kabel_48C = 0;
$total_plan_fat = 0;
$total_achiev_fat = 0;
$total_plan_closure = 0;
$total_achiev_closure = 0;

$persentase_plan = 0;
$persentase_achiev = 0;
$persentase_total = 0;

?>

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

    <section class="content">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              Cleanlist Deskripsi
            </h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <?php foreach ($progress_implementasi as $data):

              $persentase_plan = $data['plan_tiang'] + $data['plan_kabel_24'] + $data['plan_kabel_48'] + $data['plan_fat'] + $data['plan_closure'];
              $persentase_achiev = $data['achiev_tiang'] + $data['achiev_kabel_24'] + $data['achiev_kabel_48'] + $data['achiev_fat'] + $data['achiev_closure'];

              if ($persentase_achiev == 0 || $persentase_plan == 0) {
                $persentase_total = 0;
              } else {
                $persentase_total = ($persentase_achiev / $persentase_plan) * 100;
              }

              ?>


              <dl>
                <dt>Regional</dt>
                <dd>  <?= $data['regional_project'] ?></dd>
                <dt>Area</dt>
                <dd> - <?= $data['area_project'] ?></dd>
                <dt>PIC</dt>
                <dd>  <?= $data['pic_project'] ?></dd>
                <dt>Access ID</dt>
                <dd>  <?= $data['access_id_project'] ?></dd>
                <dt>Access Name</dt>
                <dd>  <?= $data['access_name_project'] ?></dd>
                <dt>Home Pass</dt>
                <dd>  <?= number_format($data['hp_hld'], 0, ".") ?></dd>
                <dt>Nomor PO</dt>
                <dd><?= $data['number_po'] ?></dd>
                <dt>Persentase Progress ( % )</dt>
                <dt>
                  <?php if ($persentase_total < '25') { ?>
                    <div class="row" style="margin-top:0.5%">
                      <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                        <div class="progress-bar bg-danger" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                      </div>
                      <span class="badge bg-danger" style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                    </div>
                  <?php } else if ($persentase_total >= '25' && $persentase_total < '70') { ?>
                      <div class="row" style="margin-top:0.5%">
                        <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                          <div class="progress-bar bg-warning" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                        </div>
                        <span class="badge bg-warning"
                          style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                      </div>
                  <?php } else if ($persentase_total >= '70' && $persentase_total < '100') { ?>
                        <div class="row" style="margin-top:0.5%">
                          <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                            <div class="progress-bar bg-primary" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                          </div>
                          <span class="badge bg-primary"
                            style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                        </div>
                  <?php } else { ?>
                        <div class="row" style="margin-top:0.5%">
                          <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                            <div class="progress-bar bg-success" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                          </div>
                          <span class="badge bg-success"
                            style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                        </div>
                  <?php } ?>

                  </dd>
              </dl>
            <?php endforeach ?>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
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
                    <h3 class="card-title">List Cleanlist Cluster</h3>
                  </div>
                </div>

              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive text-nowrap ">
                <table id="table_detail" class="table table-bordered table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th>No</th>
                      <th>Tanggal PO</th>
                      <th>Nilai PO</th>
                      <th>Canvasing</th>
                      <th>Status BAK</th>
                      <th>Status CBN</th>
                      <th>Nomor SPK</th>
                      <th>Status HLD</th>
                      <th>Status LLD</th>
                      <th>Status Implementasi</th>
                      <th>RFS</th>
                      <th>ATP</th>
                      <th>Stagging</th>
                      <th>Done Invoice</th>
                      <th>Sisa Invoice</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php

                    foreach ($progress_implementasi as $data):
                      ?>

                      <tr>
                        <td><?= $total++ ?></td>
                        <td><?= $data['tanggal_po'] ?></td>
                        <td><?= $data['nilai_awal_po'] ?></td>
                        <td><?= $data['tgl_canvasing'] ?></td>
                        <td><?= $data['status_bak'] ?></td>
                        <td><?= $data['status_cbn'] ?></td>
                        <td><?= $data['spk_nomor'] ?></td>
                        <td><?= $data['status_hld'] ?></td>
                        <td><?= $data['status_lld'] ?></td>
                        <td><?= $data['status_implementasi'] ?></td>
                        <td><?= $data['tanggal_rfs'] ?></td>
                        <td><?= $data['tanggal_atp'] ?></td>
                        <td><?= $data['main_status'] ?></td>
                        <td><?= $data['total_invoice'] ?></td>
                        <td><?= $data['total_sisa_invoice'] ?></td>

                      </tr>

                    <?php endforeach; ?>

                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>
              <!-- /.card-body -->
            </div>

            <!-- COBA PANGGIL DATA MSQL -->
            <div class="row">
              <!-- ISI -->
            </div>


          </div>
    </section>

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
                    <h3 class="card-title">Detail Implementasi</h3>
                  </div>
                  <div class="col-6">
                    <?php if( $this->session->userdata('tim_project') == "HO" ){ ?>
                      <a href="#" class="btn btn-success float-right text-bold"
                      data-target="#modal-lg-tambah_rab" data-toggle="modal">Tambah RAB &nbsp;</a>
                    <?php } else {?>
                      <a href="#" class="btn btn-success float-right text-bold"
                      data-target="#modal-lg-tambah_implementasi" data-toggle="modal">Tambah Progress &nbsp;</i> </a>
                    <?php } ?>
                  </div>
                </div>

              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive text-nowrap ">
                <table id="table_detail" class="table table-bordered table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th>No</th>
                      <th>HP HLD</th>
                      <th>Plan Tiang</th>
                      <th>Achiev Tiang</th>
                      <th>Plan Kabel 24C</th>
                      <th>Achiev Kabel 24C</th>
                      <th>Plan Kabel 48C</th>
                      <th>Achiev Kabel 48C</th>
                      <th>Plan FAT</th>
                      <th>Achiev FAT</th>
                      <th>Plan Closure</th>
                      <th>Achiev Closure</th>
                      <th>Tanggal Progress</th>
                      <th>Keterangan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $total = 1;

                    foreach ($detail_progress_implementasi as $data):

                      $total_hp += $data['hp_hld'];
                      $total_plan_tiang += $data['plan_tiang'];
                      $total_achiev_tiang += $data['achiev_tiang'];
                      $total_plan_kabel_24C += $data['plan_kabel_24'];
                      $total_achiev_kabel_24C += $data['achiev_kabel_24'];
                      $total_plan_kabel_48C += $data['achiev_kabel_48'];
                      $total_achiev_kabel_48C += $data['plan_fat'];
                      $total_plan_fat += $data['achiev_fat'];
                      $total_achiev_fat += $data['plan_closure'];
                      $total_plan_closure += $data['achiev_closure'];
                      $total_achiev_closure += $data['achiev_closure'];

                      // $jumlah_hp += $data['homepass_cluster_fiberstar'];
                      // $jumlah_tiang += $data['realisasi_tiang_project_fiberstar_progress'];
                      // $jumlah_kabel += $data['realisasi_kabel_project_fiberstar_progress'];
                      // $jumlah_fat += $data['realisasi_fat_project_fiberstar_progress'];
                    
                      ?>

                      <tr>
                        <td><?= $total++ ?></td>
                        <td><?= number_format($data['hp_hld'], 0, ".") ?></td>
                        <td><?= $data['plan_tiang'] ?></td>
                        <td><?= $data['achiev_tiang'] ?></td>
                        <td><?= $data['plan_kabel_24'] ?></td>
                        <td><?= $data['achiev_kabel_24'] ?></td>
                        <td><?= $data['plan_kabel_48'] ?></td>
                        <td><?= $data['achiev_kabel_48'] ?></td>
                        <td><?= $data['plan_fat'] ?></td>
                        <td><?= $data['achiev_fat'] ?></td>
                        <td><?= $data['plan_closure'] ?></td>
                        <td><?= $data['achiev_closure'] ?></td>
                        <td><?= $data['data_created'] ?></td>
                        <td><?= $data['keterangan_progress'] ?></td>
                      </tr>

                    <?php endforeach; ?>

                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="1">Total</th>
                      <th colspan="1"><?= number_format($data['hp_hld'], 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_plan_tiang, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_achiev_tiang, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_plan_kabel_24C, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_achiev_kabel_24C, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_plan_kabel_48C, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_achiev_kabel_48C, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_plan_fat, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_achiev_fat, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_plan_closure, 0, ".") ?></th>
                      <th colspan="1"><?= number_format($total_achiev_closure, 0, ".") ?></th>
                      <th colspan="1"></th>
                      <th colspan="1"></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <!-- /.card-body -->
            </div>

            <?php $tgl = date(format: 'Y-m-d'); ?>
            <?php foreach ($progress_implementasi as $data):
              ?>
              <form action="<?php echo site_url('Fiberstar_Project/add'); ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah_implementasi">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">Tambah Implementasi</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="primary_access_id_project"
                          value="<?= $data['primary_access_id_project'] ?>">
                        <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_akun') ?>">
                        <div class="form-group">
                          <label class="col-form-label">Access ID Project</label>
                          <input readonly type="text" class="form-control" name="access_id_project" autocomplete="off"
                            value="<?= $data['access_id_project'] ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Access Name Project</label>
                          <input readonly type="text" class="form-control" name="access_name_project" autocomplete="off"
                            value="<?= $data['access_name_project'] ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Input Date</label>
                          <input type="date" class="form-control" name="data_created" autocomplete="off"
                            value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Tiang / Achiev Tiang / Deviasi</label>
                          <input readonly type="text" class="form-control" name="plan_tiang" autocomplete="off"
                            value="<?php echo $data['plan_tiang'] . " / " . $data['achiev_tiang'] . " / " . ($data['plan_tiang'] - $data['achiev_tiang']) ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Daily Progress Tiang</label>
                          <input type="text" class="form-control" name="achiev_tiang" autocomplete="off" placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Kabel 24C / Achiev Kabel 24C / Deviasi</label>
                          <input readonly type="text" class="form-control" name="plan_kabel_24" autocomplete="off"
                            value="<?= $data['plan_kabel_24'] . " / " . $data['achiev_kabel_24'] . " / " . ($data['plan_kabel_24'] - $data['achiev_kabel_24']) ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Daily Progress Kabel 24C</label>
                          <input type="text" class="form-control" name="achiev_kabel_24" autocomplete="off"
                            placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Kabel 48C / Achiev Kabel 48C / Deviasi</label>
                          <input readonly type="text" class="form-control" name="plan_kabel_48" autocomplete="off"
                            value="<?= $data['plan_kabel_48'] . " / " . $data['achiev_kabel_48'] . " / " . ($data['plan_kabel_48'] - $data['achiev_kabel_48']) ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Daily Progress Kabel 48C</label>
                          <input type="text" class="form-control" name="achiev_kabel_48" autocomplete="off"
                            placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan FAT / Achiev FAT / Deviasi</label>
                          <input readonly type="text" class="form-control" name="plan_fat" autocomplete="off"
                            value="<?= $data['plan_fat'] . " / " . $data['achiev_fat'] . " / " . ($data['plan_fat'] - $data['achiev_fat']) ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Daily Progress FAT</label>
                          <input type="text" class="form-control" name="achiev_fat" autocomplete="off" placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Closure / Achiev Closure / Deviasi</label>
                          <input readonly type="text" class="form-control" name="plan_closure" autocomplete="off"
                            value="<?= $data['plan_closure'] . " / " . $data['achiev_closure'] . " / " . ($data['plan_closure'] - $data['achiev_closure']) ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Daily Progress Closure</label>
                          <input type="text" class="form-control" name="achiev_closure" autocomplete="off"
                            placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Catatan</label>
                          <input type="text" class="form-control" name="keterangan_progress" autocomplete="off"
                            placeholder="0">
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

            <?php $tgl = date(format: 'Y-m-d'); ?>
            <?php foreach ($progress_implementasi as $data):
              ?>
              <form action="<?php echo site_url('Fiberstar_Project_Detail/addRAB'); ?>" method="post">
                <div class="modal fade" id="modal-lg-tambah_rab">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">Tambah RAB</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="primary_access_id_project"
                          value="<?= $data['primary_access_id_project'] ?>">
                        <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_user') ?>">
                        <div class="form-group">
                          <label class="col-form-label">Access ID Project</label>
                          <input readonly type="text" class="form-control" name="access_id_project" autocomplete="off"
                            value="<?= $data['access_id_project'] ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Access Name Project</label>
                          <input readonly type="text" class="form-control" name="access_name_project" autocomplete="off"
                            value="<?= $data['access_name_project'] ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Input Date</label>
                          <input type="date" class="form-control" name="data_created" autocomplete="off"
                            placeholder="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Homepass HLD</label>
                          <input readonly type="text" class="form-control" name="hp_hld" autocomplete="off"
                            value="<?= $data['hp_hld'] ?>">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Tiang</label>
                          <input type="text" class="form-control" name="plan_tiang" autocomplete="off"
                          placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Kabel 24C</label>
                          <input type="text" class="form-control" name="plan_kabel_24" autocomplete="off"
                          placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Kabel 48C</label>
                          <input type="text" class="form-control" name="plan_kabel_48" autocomplete="off"
                          placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan FAT</label>
                          <input type="text" class="form-control" name="plan_fat" autocomplete="off"
                          placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Plan Closure</label>
                          <input type="text" class="form-control" name="plan_closure" autocomplete="off"
                          placeholder="0">
                        </div>
                        <div class="form-group">
                          <label class="col-form-label">Catatan</label>
                          <input type="text" class="form-control" name="keterangan_progress" autocomplete="off"
                            value="RAB Awal">
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
  console.log("Hello World!")


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

  $(document).ready(function () {

    // Format mata uang.
    $('.nilai_po2').mask('000.000.000', { reverse: true });

  })

  $(document).ready(function () {
    $('.card[data-card-widget="collapse"]').addClass('card-tools');
  });

  document.getElementById('reset_filter').addEventListener('click', function () {
    const selectRegional = document.getElementById('filter_regional');
    const selectPic = document.getElementById('filter_pic');
    const selectArea = document.getElementById('filter_area');
    const selectStagging = document.getElementById('filter_stagging');

    const optionsRegional = selectRegional.options;
    const optionsPic = selectPic.options;
    const optionsArea = selectArea.options;
    const optionsStagging = selectStagging.options;

    // Hapus semua pilihan
    for (let i = 0; i < optionsRegional.length; i++) {
      optionsRegional[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsPic.length; i++) {
      optionsPic[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsArea.length; i++) {
      optionsArea[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsStagging.length; i++) {
      optionsStagging[i].selected = false; // Hilangkan pilihan
    }

    // Pilih opsi default (indeks 0)
    selectRegional.dispatchEvent(new Event('change'));
    selectPic.dispatchEvent(new Event('change'));
    selectArea.dispatchEvent(new Event('change'));
    selectStagging.dispatchEvent(new Event('change'));
  });

  $(function () {
    'use strict'

    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    }

    var mode = 'index'
    var intersect = true

    var $fiberstarChartBar = $('#fiberstar_chart_bar')

    const dataBar = <?php echo json_encode($top_area_bak); ?>;
    const areaAchievBar = dataBar.map(item => item.area_project);
    const hpAchievBar = dataBar.map(item => item.achiev_bak);


    // eslint-disable-next-line no-unused-vars
    var fiberstarChartBar = new Chart($fiberstarChartBar, {
      type: 'bar',
      data: {
        labels: areaAchievBar,
        datasets: [
          {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: hpAchievBar
          }
        ]
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect
        },
        hover: {
          mode: mode,
          intersect: intersect
        },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,

              // Include a dollar sign in the ticks
              callback: function (value) {
                return `${value.toLocaleString('id-ID')} Hp`;
              }
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        }
      }
    })

    const dataLine = <?php echo json_encode($top_area_bak); ?>;
    const areaAchievLine = dataLine.map(item => item.area_project);
    const hpAchievLine = dataLine.map(item => item.achiev_bak);

    var $fiberstarChartLine = $('#fiberstar_chart_line')
    // eslint-disable-next-line no-unused-vars
    var fiberstarChartLine = new Chart($fiberstarChartLine, {
      data: {
        labels: areaAchievLine,
        datasets: [{
          type: 'line',
          data: hpAchievLine,
          backgroundColor: 'transparent',
          borderColor: '#007bff',
          pointBorderColor: '#007bff',
          pointBackgroundColor: '#007bff',
          fill: false
          // pointHoverBackgroundColor: '#007bff',
          // pointHoverBorderColor    : '#007bff'
        }]
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect
        },
        hover: {
          mode: mode,
          intersect: intersect
        },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,

              // Include a dollar sign in the ticks
              callback: function (value, index, ticks) {
                // Format nilai ke Rupiah
                return `${value.toLocaleString('id-ID')} Hp`;
              }
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        }
      }
    })
  })

  document.addEventListener("DOMContentLoaded", function () {
    const cards = document.querySelectorAll('[data-card-widget="collapse"]');
    cards.forEach(card => {
      const parentCard = card.closest('.card');
      if (parentCard) {
        parentCard.classList.add('collapsed-card'); // Tambahkan kelas 'collapsed-card'
      }
    });

  });

  $(document).ready(function () {
    // Tambahkan event click ke setiap row
    $('#tabel_pemasukan tbody').on('click', 'tr', function () {
      const url = $(this).data('url');
      if (url) {
        window.location.href = url; // Pindah ke halaman detail
      }
    });

    // Tambahkan gaya kursor pointer
    $('#tabel_pemasukan tbody tr').css('cursor', 'pointer');
  });

  $(document).ready(function () {
    $('#table_detail').DataTable({
      searching: false // Nonaktifkan fitur pencarian
    });
  });



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
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date picker
    $('#reservationdate').datetimepicker({
      format: 'L'
    });

    //Date and time picker
    $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'MM/DD/YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
      {
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
      },
      function (start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
      }
    )

    //Timepicker
    $('#timepicker').datetimepicker({
      format: 'LT'
    })

    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    $('.my-colorpicker2').on('colorpickerChange', function (event) {
      $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
    })

    $("input[data-bootstrap-switch]").each(function () {
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    })

  })
  // BS-Stepper Init
  document.addEventListener('DOMContentLoaded', function () {
    window.stepper = new Stepper(document.querySelector('.bs-stepper'))
  })

  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false

  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template")
  previewNode.id = ""
  var previewTemplate = previewNode.parentNode.innerHTML
  previewNode.parentNode.removeChild(previewNode)

  var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
    url: "/target-url", // Set the url
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  })

  myDropzone.on("addedfile", function (file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function () { myDropzone.enqueueFile(file) }
  })

  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function (progress) {
    document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
  })

  myDropzone.on("sending", function (file) {
    // Show the total progress bar when upload starts
    document.querySelector("#total-progress").style.opacity = "1"
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
  })

  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function (progress) {
    document.querySelector("#total-progress").style.opacity = "0"
  })

  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function () {
    myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
  }
  document.querySelector("#actions .cancel").onclick = function () {
    myDropzone.removeAllFiles(true)
  }
  // DropzoneJS Demo Code End
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

<!-- <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartfibertstar.js"></script> -->
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartmyrep.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardrkap.js"></script>