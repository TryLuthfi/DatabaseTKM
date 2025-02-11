<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];
?>

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
                            <h3 class="card-title">FILTER DATA</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body" style="margin-top:10px;">
                            <div class="container-fluid">
                                <!-- Info boxes -->
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">LOKASI
                                                GUDANG</label>
                                            <select id="filter_lokasi" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Gudang" style="width: 100%;">
                                                <?php foreach ($getUniqueKotaGudang as $data): ?>
                                                    <option value="<?php echo $data['kota_lokasi_gudang'] ?>">
                                                        <?php echo $data['kota_lokasi_gudang'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">PROJECT
                                                BOWHEER</label>
                                            <select id="filter_bowheer" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Bowheer" style="width: 100%;">
                                                <?php foreach ($getUniqueProjectLogistik as $data): ?>
                                                    <option value="<?php echo $data['nama_bowheer'] ?>">
                                                        <?php echo $data['nama_bowheer'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">JENIS
                                                ITEM</label>
                                            <select id="filter_item" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Item" style="width: 100%;">
                                                <?php foreach ($getUniqueItemLogistik as $data): ?>
                                                    <option value="<?php echo $data['nama_item'] ?>">
                                                        <?php echo $data['nama_item'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">STATUS
                                                STOK</label>
                                            <select id="filter_status" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Status Stok" style="width: 100%;">
                                                <?php foreach ($getUniqueSumberMaterial as $data): ?>
                                                    <option value="<?php echo $data['nama_sumber_material'] ?>">
                                                        <?php echo $data['nama_sumber_material'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer col-sm-12">
                                        <button type="button" id="reset_filter" class="btn btn-danger"
                                            data-dismiss="modal">Hapus</button>
                                        <button id="btnFilterDataProject" class="btn btn-primary"><i
                                                class="fa fa-spinner fa-spin loading" style="display:none"></i> Cari
                                        </button>
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
              <h3 class="card-title">TOTAL MATERIAL</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-plus"></i>
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
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_plan">
                            <?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>KABEL</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_canvasing">
                            <?= number_format(floatval($totalHpPlan['total_hp_canvasing']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>TIANG</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_bak">
                            <?= number_format(floatval($totalHpPlan['total_hp_bak']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>HDPE</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_spk">
                            <?= number_format(floatval($totalHpPlan['total_hp_spk']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>CLOSURE</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_hld">
                            <?= number_format(floatval($totalHpPlan['total_hp_hld']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>OTB</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_lld">
                            <?= number_format(floatval($totalHpPlan['total_hp_lld']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>FAT</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_kom">
                            <?= number_format(floatval($totalHpPlan['total_hp_kom']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>FDT</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_rfs">
                            <?= number_format(floatval($totalHpPlan['total_hp_rfs']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>AKSESORIS</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

                <div class="card-body">
                  <div class="chart" style="height: 300px;">
                    <canvas id="barChart"
                      style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                  </div>
                  <div id="paginationControls" class="mt-3"></div>
                </div>
            </div>
          </div>
        </div>
      </div>
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
                                        <h3 class="card-title">List Stok Logistik </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-scrollable">
                                <table id="table_data" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Lokasi</th>
                                            <th>Project</th>
                                            <th>Item</th>
                                            <th>Status</th>
                                            <th>QTY</th>
                                            <th>PIC</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getAllStokLogistik as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['kota_lokasi_gudang'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['nama_sumber_material'] ?></td>
                                                <td><?= $data['jumlah_stok'] ?></td>
                                                <td><?= $data['nama_user'] ?></td>
                                                <td>
                                                    <a href="<?php echo site_url('Dashboard_Logistik_Stok/hapusReportStokLogistik/' . $data['id_logistik_stok']); ?>"
                                                        id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                            class=" fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="1">Total</th>
                                            <th colspan="4"></th>
                                            <th colspan="1"><span id="totalQTY"></span></th>
                                            <th colspan="1"></th>
                                            <th colspan="1"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
        </section>

        <!-- MODAL TAMBAH LOKASI GUDANG LOGISTIK -->
        <form action=" <?php echo base_url('Dashboard_Logistik_Stok/tambahReportStokLogistik') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Report Stok</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-form-label">Input Date</label>
                                <input type="date" class="form-control" name="tanggal_upload_stok" autocomplete="off"
                                    value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Area Gudang</label>
                                <select name="id_lokasi_gudang" class="form-control">
                                    <?php foreach ($getListGudangLokasiUser as $data2): ?>
                                        <option value="<?php echo $data2['id_lokasi_gudang'] ?>">
                                            <?php echo $data2['kota_lokasi_gudang'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Project</label>
                                <select name="id_bowheer" class="form-control">
                                    <?php foreach ($getMasterProject as $data2): ?>
                                        <option value="<?php echo $data2['id_bowheer'] ?>">
                                            <?php echo $data2['nama_bowheer'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Sumber Material</label>
                                <select name="id_sumber_material" class="form-control">
                                    <?php foreach ($getMasterSumberMaterial as $data2): ?>
                                        <option value="<?php echo $data2['id_sumber_material'] ?>">
                                            <?php echo $data2['status_sumber_material'] ?> -
                                            <?php echo $data2['nama_sumber_material'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Jenis Material</label>
                                <select class="form-control select2" name="id_kode_item">
                                    <?php foreach ($getMasterKodeItem as $data): ?>
                                        <option value="<?php echo $data['id_kode_item'] ?>">
                                            <?php echo $data['nama_item'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Qty Item</label>
                                <input type="number" class="form-control" name="jumlah_stok" autocomplete="off"
                                    placeholder="1.000">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Jenis Satuan Item</label>
                                <select name="satuan_stok" class="form-control">
                                    <?php foreach ($satuan_options as $option): ?>
                                        <option value="<?= $option ?>" <?= isset($data['satuan_item']) && $data['satuan_item'] == $option ? 'selected' : '' ?>>
                                            <?= $option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Merk Item</label>
                                <input type="text" class="form-control" name="merk_stok" autocomplete="off"
                                    placeholder="' Furukawa ' / ' ZTT ' / ' CCSI '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">No Haspel</label>
                                <input type="text" class="form-control" name="no_haspel_stok" autocomplete="off"
                                    placeholder="' D11-11*** '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">No Ref</label>
                                <input type="text" class="form-control" name="no_ref_stok" autocomplete="off"
                                    placeholder="' 1***/EMR/NRO-GDR/02/2025 '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Keterangan</label>
                                <input type="text" class="form-control" name="keterangan_stok"
                                    autocomplete="Keterangan">
                            </div>
                            <div class="form-group">
                                <label>Upload Surat Jalan</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <label class="custom-file-label" for="evidence_stok">Choose file</label>
                                        <input type="file" name="evidence_stok" id="evidence_stok"
                                            class="custom-file-input" required>
                                    </div>
                                </div>
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

</div>
<!-- /.content-wrapper -->

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<script type="text/javascript">
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

        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    })

    // HAPUS FILTER DATA SELECT2
    document.getElementById('reset_filter').addEventListener('click', function () {
        const selectLokasi = document.getElementById('filter_lokasi');
        const selectBowheer = document.getElementById('filter_bowheer');
        const selectItem = document.getElementById('filter_item');
        const selectStatus = document.getElementById('filter_status');

        const optionsLokasi = selectLokasi.options;
        const optionsBowheer = selectBowheer.options;
        const optionsItem = selectItem.options;
        const optionsStatus = selectStatus.options;

        // Hapus semua pilihan
        for (let i = 0; i < optionsLokasi.length; i++) {
            optionsLokasi[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsBowheer.length; i++) {
            optionsBowheer[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsItem.length; i++) {
            optionsItem[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsStatus.length; i++) {
            optionsStatus[i].selected = false; // Hilangkan pilihan
        }

        // Pilih opsi default (indeks 0)
        selectLokasi.dispatchEvent(new Event('change'));
        selectBowheer.dispatchEvent(new Event('change'));
        selectItem.dispatchEvent(new Event('change'));
        selectStatus.dispatchEvent(new Event('change'));
    });

    // FUNCTION UPDATE DATA TABLE MENGGUNAKAN FILTER DATA
    $(document).ready(function () {
        $('#btnFilterDataProject').on('click', function () {
            var table = $('#table_data').DataTable();
            // Ambil nilai dari multiple select filter kategori
            var selectLokasi = $('#filter_lokasi').val() || []; // Array of selected values
            var selectBowheer = $('#filter_bowheer').val() || []; // Array of selected values
            var selectItem = $('#filter_item').val() || []; // Array of selected values
            var selectStatus = $('#filter_status').val() || []; // Array of selected values

            // Gabungkan nilai ke dalam regex untuk pencarian DataTable
            var lokasiFilter = selectLokasi.length > 0 ? selectLokasi.join('|') : '';
            var bowheerFilter = selectBowheer.length > 0 ? selectBowheer.join('|') : '';
            var itemFilter = selectItem.length > 0 ? selectItem.join('|') : '';
            var statusFilter = selectStatus.length > 0 ? selectStatus.join('|') : '';

            // Terapkan filter ke DataTable
            table
                .column(1).search(lokasiFilter, true, false) // Filter kategori (regex search)
                .column(2).search(bowheerFilter, true, false) // Filter kategori (regex search)
                .column(3).search(itemFilter, true, false) // Filter kategori (regex search)
                .column(4).search(statusFilter, true, false) // Filter kategori (regex search)
                .draw(); // Render ulang tabel

        });
    });

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
            let totalQTY = 0;

            data.each(function (row) {

                if (row['5'] != 0) {
                    totalQTY += parseFloat(row[5].replace(/,/g, ''))
                }
            });

            $('#totalQTY').text(totalQTY.toLocaleString('id-ID'));
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
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/js/select2.full.min.js">
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