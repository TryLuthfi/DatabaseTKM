<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];
$kategori_item = ['Tiang', 'OTB ', 'Kabel ', 'HDPE ', 'FDT', 'FAT', 'Closure', 'Aksesories '];

$total = 1;
?>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="clearfix hidden-md-up"></div>

                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-md-12" style="margin_botttom:10px;">
                                <!-- DIRECT CHAT DANGER -->
                                <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
                                    <div class="card-header">
                                        <h3 class="card-title">TOTAL ASET TERMINASI</h3>

                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="container-fluid">
                                        <div class="row">

                                            <!-- ====================== MOBIL (KIRI) ====================== -->
                                            <div class="col-md-12 mt-4">
                                                <div class="p-3 mb-4 shadow-sm rounded"
                                                    style="background-color: #bbc1c754;">
                                                    <div class="row">
                                                        <?php foreach ($getCountAsetTerminasiLimit as $stokTerminasi): ?>

                                                            <!-- TOTAL MOBIL -->
                                                            <div class="col-lg-3 col-6 mb-3">
                                                                <div class="small-box bg-info">
                                                                    <div class="inner">
                                                                        <h3><?= number_format($stokTerminasi['total_data'], 0, ",", ".") ?>
                                                                        </h3>
                                                                        <p><?= $stokTerminasi['ka_jenis_aset'] ?></p>
                                                                    </div>
                                                                    <div class="icon"><i class="ion ion-bag"></i></div>
                                                                    <a href="#" class="small-box-footer"
                                                                        id="<?php echo 'box_detail_terminasi' . $stokTerminasi['ka_jenis_aset'] ?>">
                                                                        Lihat Detail <i
                                                                            class="fas fa-arrow-circle-right"></i></a>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <a href="<?= base_url('GA_Aset_Terminasi/allAsetTerminasi') ?>"
                                                        class="text-decoration-none">
                                                        <h5 class="text-center mb-4 font-weight-bold text-primary"
                                                            style="text-decoration: underline;">
                                                            Lihat Semua &#8594;
                                                        </h5>
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="content-header">
                                        <div class="container-fluid">
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                    <h1 class="m-0 mb-2 text-dark" style="text-align: center;">DISTRIBUSI
                                                        TERMINASI REGIONAL</h1>
                                                        <h3 class="m-0 text-dark" style="text-align: center;">TIPE 1</h3>
                                                </div><!-- /.col -->
                                            </div><!-- /.row -->
                                        </div><!-- /.container-fluid -->
                                    </div>

                                    <section class="content">

                                        <div class="container-fluid">
                                            <!-- Info boxes -->
                                            <div class="row">
                                                <!-- fix for small devices only -->
                                                <div class="clearfix hidden-md-up"></div>

                                                <div class="col-12">
                                                    <div class="card">
                                                        <!-- /.card-header -->
                                                        <div class="card-body table-responsive text-nowrap ">
                                                            <table id="table_detail_regional"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Regional</th>
                                                                        <th>Splicer</th>
                                                                        <th>GPS</th>
                                                                        <th>OTDR</th>
                                                                        <th>Camera 360</th>
                                                                        <th>UTG</th>
                                                                        <th>Tangga Teleskopik</th>
                                                                        <th>OLS</th>
                                                                        <th>OPM</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetTerminasiByRegionTipe1 as $data):

                                                                            ?>

                                                                            <tr>
                                                                                <td><?= $total++ ?></td>
                                                                                <td><?= $data['at_regional'] ?></td>
                                                                                <td><?php
                                                                                if ($data['splicer'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['splicer']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['otdr'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['otdr']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['gps'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['gps']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['camera_360'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['camera_360']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['utg'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['utg']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['tangga_teleskopik'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['tangga_teleskopik']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['ols'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['ols']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['opm'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['opm']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td>
                                                                                    <a href="<?php echo site_url('GA_Aset_Terminasi/areaTerminasi/' . $data['at_regional']); ?>"
                                                                                        class="btn btn-primary"><i
                                                                                            class=" fas fa-eye"></i></a>
                                                                                </td>
                                                                            </tr>

                                                                        <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMobilRegional">0</span>
                                                                        </th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMotorRegional">0</span>
                                                                        </th>
                                                                        <th colspan="1"></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                        <!-- /.card-body -->
                                                    </div>
                                                    <div class="row">
                                                        <!-- ISI -->
                                                    </div>
                                                </div>
                                    </section>

                                    <div class="content-header">
                                        <div class="container-fluid">
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                        <h3 class="m-0 text-dark" style="text-align: center;">TIPE 2</h3>
                                                </div><!-- /.col -->
                                            </div><!-- /.row -->
                                        </div><!-- /.container-fluid -->
                                    </div>

                                    <section class="content">

                                        <div class="container-fluid">
                                            <!-- Info boxes -->
                                            <div class="row">
                                                <!-- fix for small devices only -->
                                                <div class="clearfix hidden-md-up"></div>

                                                <div class="col-12">
                                                    <div class="card">
                                                        <!-- /.card-header -->
                                                        <div class="card-body table-responsive text-nowrap ">
                                                            <table id="table_detail_regional"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Regional</th>
                                                                        <th>OFI</th>
                                                                        <th>Label It</th>
                                                                        <th>Power Inverter</th>
                                                                        <th>Roll Meter</th>
                                                                        <th>Toolkits</th>
                                                                        <th>Cleaver</th>
                                                                        <th>Stripper</th>
                                                                        <th>Slitter</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetTerminasiByRegionTipe2 as $data):

                                                                            ?>

                                                                            <tr>
                                                                                <td><?= $total++ ?></td>
                                                                                <td><?= $data['at_regional'] ?></td>
                                                                                <td><?php
                                                                                if ($data['ofi'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['ofi']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['label_it'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['label_it']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['power_inverter'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['power_inverter']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['roll_meter'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['roll_meter']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['toolkits'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['toolkits']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['cleaver'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['cleaver']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['stripper'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['stripper']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['slitter'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['slitter']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td>
                                                                                    <a href="<?php echo site_url('GA_Aset_Terminasi/areaTerminasi/' . $data['at_regional']); ?>"
                                                                                        class="btn btn-primary"><i
                                                                                            class=" fas fa-eye"></i></a>
                                                                                </td>
                                                                            </tr>

                                                                        <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMobilRegional">0</span>
                                                                        </th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMotorRegional">0</span>
                                                                        </th>
                                                                        <th colspan="1"></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                        <!-- /.card-body -->
                                                    </div>
                                                    <div class="row">
                                                        <!-- ISI -->
                                                    </div>
                                                </div>
                                    </section>

                                    <div class="content-header">
                                        <div class="container-fluid">
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                    <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                                                        KENDARAAN AREA</h1>
                                                </div><!-- /.col -->
                                            </div><!-- /.row -->
                                        </div><!-- /.container-fluid -->
                                    </div>

                                    <section class="content">

                                        <div class="container-fluid">
                                            <!-- Info boxes -->
                                            <div class="row">
                                                <!-- fix for small devices only -->
                                                <div class="clearfix hidden-md-up"></div>

                                                <div class="col-12">
                                                    <div class="card">
                                                        <!-- /.card-header -->
                                                        <div class="card-body table-responsive text-nowrap ">
                                                            <table id="table_detail_kota"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Regional</th>
                                                                        <th>Mobil</th>
                                                                        <th>Motor</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetKendaraanByKota as $data):
                                                                        if (!in_array($data['ak_area'], ['Terjual', 'Hilang'])):

                                                                            ?>

                                                                            <tr>
                                                                                <td><?= $total++ ?></td>
                                                                                <td><?= $data['ak_area'] ?></td>
                                                                                <td><?php
                                                                                if ($data['mobil'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['mobil']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td><?php
                                                                                if ($data['motor'] == "0") {
                                                                                    echo "-";
                                                                                } else {
                                                                                    echo number_format(floatval($data['motor']), 0, ",", ".");
                                                                                }
                                                                                ?></td>
                                                                                <td>
                                                                                    <a href="<?php echo site_url('GA_Aset_Kendaraan/areaKendaraan/' . $data['ak_area']) ?>"
                                                                                        class="btn btn-primary"><i
                                                                                            class=" fas fa-eye"></i></a>
                                                                                </td>
                                                                            </tr>

                                                                        <?php endif; endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMobilKota">0</span>
                                                                        </th>
                                                                        <th colspan="1"><span
                                                                                id="totalTabelMotorKota">0</span>
                                                                        </th>
                                                                        <th colspan="1"></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                        <!-- /.card-body -->
                                                    </div>
                                                    <div class="row">
                                                        <!-- ISI -->
                                                    </div>
                                                </div>
                                    </section>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </section>

        <!-- MODAL TAMBAH KODE ITEM LOGISTIK -->
        <form action=" <?php echo base_url('Master_Logistik_Kode_Item/tambahKodeItem') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Kode Item</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-form-label">Nama Item</label>
                                <input type="text" class="form-control" name="nama_item" autocomplete="off"
                                    placeholder="Nama Item">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Kategori Item</label>
                                <select name="kategori_item" class="form-control">
                                    <?php foreach ($kategori_item as $option): ?>
                                        <option value="<?= $option ?>" <?= isset($data['satuan_item']) && $data['satuan_item'] == $option ? 'selected' : '' ?>>
                                            <?= $option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Jumlah Satuan</label>
                                <select name="satuan_item" class="form-control">
                                    <?php foreach ($satuan_options as $option): ?>
                                        <option value="<?= $option ?>" <?= isset($data['satuan_item']) && $data['satuan_item'] == $option ? 'selected' : '' ?>>
                                            <?= $option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Penggunaan Project</label>
                                <select name="project_item" class="form-control" data-placeholder="Pilih Bowheer"
                                    style="width: 100%;">
                                    <?php foreach ($getMasterBowheer as $data): ?>
                                        <option value="<?php echo $data['nama_bowheer'] ?>">
                                            <?php echo $data['nama_bowheer'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Kepemilikan Item</label>
                                <select name="id_bowheer_pemilik_item" class="form-control">
                                    <?php foreach ($getMasterBowheer as $data): ?>
                                        <option value="<?php echo $data['id_bowheer'] ?>">
                                            <?php echo $data['nama_bowheer'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Harga Penjualan</label>
                                <input type="text" class="form-control" name="harga_penjualan" autocomplete="off"
                                    placeholder="Harga">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                <button type="submit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading"
                                        style="display:none"></i> Tambah</button>
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

</div>

<script>
    $(function () {

        //Initialize Select2 Elements
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
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
    })

    $(document).ready(function () {
        $('#table_detail_kota').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": false, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": false // Menghilangkan dropdown "Show entries"
        });
    });

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        const table = $('#table_detail_kota').DataTable({
            footerCallback: function () {
                updateTotal();
            }
        });

        // Fungsi untuk menghitung total dari data yang tampil
        function updateTotal() {
            // Ambil semua data yang terlihat
            const table = $('#table_detail_kota').DataTable({
                footerCallback: function () {
                    updateTotal();
                }
            });

            const data = table.rows({
                search: 'applied'
            }).data();

            // Hitung total dari kolom Value (index 2)
            let totalTabelMobilRegional = 0;
            let totalTabelMobilKota = 0;
            let totalTabelMotorRegional = 0;
            let totalTabelMotorKota = 0;


            data.each(function (row) {

                totalTabelMobilRegional += parseFloat(row[2].replace(/\./g, '')) || 0;
                totalTabelMobilKota += parseFloat(row[2].replace(/\./g, '')) || 0;
                totalTabelMotorRegional += parseFloat(row[3].replace(/\./g, '')) || 0;
                totalTabelMotorKota += parseFloat(row[3].replace(/\./g, '')) || 0;
            });

            document.getElementById('totalTabelMobilRegional').innerText = totalTabelMobilRegional.toLocaleString('id-ID');
            document.getElementById('totalTabelMobilKota').innerText = totalTabelMobilKota.toLocaleString('id-ID');
            document.getElementById('totalTabelMotorRegional').innerText = totalTabelMotorRegional.toLocaleString('id-ID');
            document.getElementById('totalTabelMotorKota').innerText = totalTabelMotorKota.toLocaleString('id-ID');
        }

        // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali saat tabel dimuat
        updateTotal();
    });

    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($getCountAsetKendaraan as $stokKendaraan): ?>

            // Untuk kondisi Aktif
            var boxAktif = document.getElementById("box_detail_kendaraan_A<?= $stokKendaraan['ka_jenis_aset'] ?>");
            if (boxAktif) {
                boxAktif.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kendaraan/detailKendaraan/A' . $stokKendaraan['ka_jenis_aset']) ?>";
                });
            }

            // Untuk kondisi Baik
            var boxBaik = document.getElementById("box_detail_kendaraan_B<?= $stokKendaraan['ka_jenis_aset'] ?>");
            if (boxBaik) {
                boxBaik.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kendaraan/detailKendaraan/B' . $stokKendaraan['ka_jenis_aset']) ?>";
                });
            }

            // Untuk kondisi Rusak
            var boxRusak = document.getElementById("box_detail_kendaraan_R<?= $stokKendaraan['ka_jenis_aset'] ?>");
            if (boxRusak) {
                boxRusak.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kendaraan/detailKendaraan/R' . $stokKendaraan['ka_jenis_aset']) ?>";
                });
            }

            // Untuk kondisi Terjual
            var boxTerjual = document.getElementById("box_detail_kendaraan_T<?= $stokKendaraan['ka_jenis_aset'] ?>");
            if (boxTerjual) {
                boxTerjual.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kendaraan/detailKendaraan/T' . $stokKendaraan['ka_jenis_aset']) ?>";
                });
            }

            // Untuk kondisi Hilang
            var boxHilang = document.getElementById("box_detail_kendaraan_H<?= $stokKendaraan['ka_jenis_aset'] ?>");
            if (boxHilang) {
                boxHilang.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kendaraan/detailKendaraan/H' . $stokKendaraan['ka_jenis_aset']) ?>";
                });
            }

        <?php endforeach; ?>
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