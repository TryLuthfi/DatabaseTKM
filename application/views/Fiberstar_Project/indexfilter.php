<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');


$total = 1;
$total_item = 0;
$total_item_inner = 0;

?>

<div class="content-wrapper">

  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-12">
          <h1 class="m-0 text-dark" style="text-align: center;"><?php echo $judul ?></h1>
          <?php if ($periode_tanggal != "kosong"){?>
          <h1 class="m-0 text-dark" style="text-align: center;">PERIODE ( <?php echo $periode_tanggal ?> )</h1>
          <?php } else {}?>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
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
                  <h3 class="card-title">LIST CLEANLIST CLUSTER</h3>
                </div>
                <div class="col-6">
                  <div class="input-group-prepend float-right mr-2">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      Download Report &nbsp; <i class="fas fa-print"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item">Excel</a>
                      <a class="dropdown-item">CSV</a>
                      <a class="dropdown-item">PDF</a>
                      <a class="dropdown-item">Print</a>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive text-nowrap ">
              <table id="table_data" class="table table-bordered table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th>No</th> <!-- 0 -->
                    <th>Regional</th> <!-- 1 -->
                    <th>Area</th> <!-- 2 -->
                    <th>PIC</th> <!-- 3 -->
                    <th>Access ID</th> <!-- 4 -->
                    <th>Access Name</th> <!-- 5 -->
                    <th>HP Plan</th> <!-- 6 -->
                    <th>Nomor PO</th> <!-- 7 -->
                    <th>Tanggal PO</th> <!-- 8 -->
                    <th>Nilai PO</th> <!-- 9 -->
                    <th>Canvasing</th> <!-- 10 -->
                    <th>Status BAK</th> <!-- 11 -->
                    <th style="display: none;">HP BAK</th> <!-- 12 -->
                    <th>Status CBN</th> <!-- 13 -->
                    <th>Nomor SPK</th> <!-- 14 -->
                    <th style="display: none;">HP SPK</th> <!-- 15 -->
                    <th>Status HLD</th> <!-- 16 -->
                    <th style="display: none;">HP HLD</th> <!-- 17 -->
                    <th>Status LLD</th> <!-- 18 -->
                    <th style="display: none;">HP LLD</th> <!-- 19 -->
                    <th>KOM</th> <!-- 20 -->
                    <th>PKS</th> <!-- 21 -->
                    <th>Status Implementasi</th> <!-- 22 -->
                    <th>RFS</th> <!-- 23 -->
                    <th style="display: none;">HP RFS</th> <!-- 24 -->
                    <th>ATP</th> <!-- 25 -->
                    <th style="display: none;">HP ATP</th> <!-- 26 -->
                    <th>Stagging</th> <!-- 27 -->
                    <th>Done Invoice</th> <!-- 28 -->
                    <th>Sisa Invoice</th> <!-- 29 -->
                    <th>Action</th> <!-- 32 -->
                  </tr>
                </thead>
                <tbody>
                  <?php

                  $total = 1;
                  foreach ($gettopAreaBAKDetail as $data):

                    ?>

                    <tr class="text-nowrap">
                      <td class="align-middle text-center"><?= $total++ ?></td>
                      <td class="align-middle"><?= $data['regional_project'] ?></td>
                      <td class="align-middle"><?= $data['area_project'] ?></td>
                      <td class="align-middle"><?= $data['pic_project'] ?></td>
                      <td class="align-middle"><?= $data['access_id_project'] ?></td>
                      <td class="align-middle"><?= $data['access_name_project'] ?></td>
                      <td class="align-middle text-center">
                        <?= number_format(floatval($data['hpplan_project']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle text-center"><?= $data['number_po'] ?></td>
                      <td class="align-middle text-center"><?= $data['tanggal_po'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['nilai_awal_po']), 0, ",", ".") ?></td>
                      <td class="align-middle"><?= $data['tgl_canvasing'] ?></td>
                      <td class="align-middle"><?= $data['status_bak'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_bak']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_cbn'] ?></td>
                      <td class="align-middle"><?= $data['spk_nomor'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['spk_hp']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_hld'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_hld']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_lld'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_lld']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['tgl_kom'] ?></td>
                      <td class="align-middle"><?= $data['tgl_pks'] ?></td>
                      <td class="align-middle text-center"><?= $data['status_implementasi'] ?></td>
                      <td class="align-middle"><?= $data['tanggal_rfs'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_rfs']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['tanggal_atp'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_atp']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle text-center"><?= $data['main_status'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_invoice']), 0, ",", ".") ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_sisa_invoice']), 0, ",", ".") ?>
                      </td>

                      <td>
                        <a href="<?php echo site_url('Fiberstar_Project_Detail/detailImplementasi/' . $data['access_id_project']); ?>"
                          id="tombol_detail" class="btn btn-primary tombol_detail"><i class=" fas fa-share"></i></a>

                      </td>
                    </tr>

                  <?php endforeach; ?>

                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="6">Total</th>
                    <th colspan="1"><span id="totalHP">0</span></th>
                    <th colspan="2"></th>
                    <th colspan="1"><span id="totalPO">0</span></th>
                    <th colspan="12"></th>
                    <th colspan="1"><span id="totalDoneInvoice">0</span></th>
                    <th colspan="1"><span id="totalSisaInvoice">0</span></th>
                    <th colspan="3"></th>
                    <!--  27 -->
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
        </div>

      </div>
    </div>
  </section>

</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>

<script>
  $(function () {
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
    $(".dropdown-menu .dropdown-item").on("click", function () {
      var selectedItem = $(this).text().trim();

      if (selectedItem === "Excel") {
        exportToExcel();
      } else {
        alert("Anda memilih: " + selectedItem);
      }
    });
  });

  function exportToExcel() {
    var table = document.getElementById("table_data"); // Ambil tabel
    var wb = XLSX.utils.book_new(); // Buat workbook baru
    var ws = XLSX.utils.table_to_sheet(table); // Konversi tabel ke sheet

    XLSX.utils.book_append_sheet(wb, ws, "Data Table"); // Tambahkan sheet ke workbook

    // Simpan file Excel
    XLSX.writeFile(wb, "Laporan_Cluster.xlsx");
  }

  $(document).ready(function () {
    $.fn.dataTable.ext.errMode = 'none';
    $('#table_data').DataTable({
      responsive: false // Matikan fitur Responsive
    });
  });

  $(document).ready(function () {
    const table = $('#table_data').DataTable({
      footerCallback: function () {
        updateTotal();
      }
    });

    // Fungsi untuk menghitung total dari data yang tampil
    function updateTotal() {
      // Ambil semua data yang terlihat

      const data = table.rows({ search: 'applied' }).data();

      // Hitung total dari kolom Value (index 2)
      let totalHP = 0;
      let totalPO = 0;
      let totalDoneInvoice = 0;
      let totalSisaInvoice = 0;

      let totalHpCanvasing = 0;
      let totalHpBAK = 0;
      let totalHpSPK = 0;
      let totalHpHLD = 0;
      let totalHpLLD = 0;
      let totalHpKOM = 0;
      let totalHpRFS = 0;
      let totalHpATP = 0;
      let totalHpClosed = 0;

      data.each(function (row) {
        totalHP += parseFloat(row[6].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalPO += parseFloat(row[9].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalDoneInvoice += parseFloat(row[28].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalSisaInvoice += parseFloat(row[29].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value

        if (row['10'] != 0) {
          totalHpCanvasing += parseFloat(row[6].replace(/\./g, ''))
        } if (row['11'] == "OK") {
          totalHpBAK += parseFloat(row[12].replace(/\./g, ''))
        } if (row['14'] != 0) {
          totalHpSPK += parseFloat(row[15].replace(/\./g, ''))
        } if (row['16'] == "OK") {
          totalHpHLD += parseFloat(row[17].replace(/\./g, ''))
        } if (row['18'] == "OK") {
          totalHpLLD += parseFloat(row[19].replace(/\./g, ''))
        } if (row['20'] != 0) {
          totalHpKOM += parseFloat(row[19].replace(/\./g, ''))
        } if (row['23'] != 0) {
          totalHpRFS += parseFloat(row[24].replace(/\./g, ''))
        } if (row['25'] != 0) {
          totalHpATP += parseFloat(row[26].replace(/\./g, ''))
        } if (row['27'].includes("close")) {
          totalHpClosed += parseFloat(row[26].replace(/\./g, ''))
        }
      });

      // Update elemen Total
      $('#totalHP').text(totalHP.toLocaleString('id-ID'));
      $('#totalPO').text(totalPO.toLocaleString('id-ID'));
      $('#totalDoneInvoice').text(totalDoneInvoice.toLocaleString('id-ID'));
      $('#totalSisaInvoice').text(totalSisaInvoice.toLocaleString('id-ID'));

      document.getElementById('idtotalDonePO').innerText = totalPO.toLocaleString('id-ID') + ' IDR';
      document.getElementById('idtotalDoneInvoice').innerText = totalDoneInvoice.toLocaleString('id-ID') + ' IDR';
      document.getElementById('idtotalSisaInvoice').innerText = totalSisaInvoice.toLocaleString('id-ID') + ' IDR';

      document.getElementById('idtotal_hp_plan').innerText = totalHP.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_canvasing').innerText = totalHpCanvasing.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_bak').innerText = totalHpBAK.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_spk').innerText = totalHpSPK.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_hld').innerText = totalHpHLD.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_lld').innerText = totalHpLLD.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_kom').innerText = totalHpKOM.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_rfs').innerText = totalHpRFS.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_atp').innerText = totalHpATP.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_closed').innerText = totalHpClosed.toLocaleString('id-ID') + ' HP';
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>