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
                                        <h3 class="card-title">TOTAL ASET OFFICE</h3>

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
                                                        <?php foreach ($getCountAsetOfficeAll as $stokOffice): ?>

                                                            <!-- TOTAL MOBIL -->
                                                            <div class="col-lg-3 col-6 mb-3">
                                                                <div class="small-box bg-info">
                                                                    <div class="inner">
                                                                        <h3><?= number_format($stokOffice['total_data'], 0, ",", ".") ?>
                                                                        </h3>
                                                                        <p><?= $stokOffice['ka_jenis_aset'] ?></p>
                                                                    </div>
                                                                    <div class="icon"><i class="ion ion-bag"></i></div>
                                                                    <a href="#" class="small-box-footer"
                                                                        id="<?php echo 'box_detail_office_' . $stokOffice['ka_jenis_aset'] ?>">
                                                                        Lihat Detail <i
                                                                            class="fas fa-arrow-circle-right"></i></a>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="content-header">
                                        <div class="container-fluid">
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                    <h1 class="m-0 mb-2 text-dark" style="text-align: center;">
                                                        DISTRIBUSI
                                                        ALKER KANTOR REGIONAL</h1>
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
                                                                        <th>Laptop</th>
                                                                        <th>Printer</th>
                                                                        <th>Scanner</th>
                                                                        <th>Markom</th>
                                                                        <th>Drafter</th>
                                                                        <th>Hardisk</th>
                                                                        <th>Handphone</th>
                                                                        <th>Cutting Plotter</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetOfficeByRegionTipe as $data):

                                                                        ?>

                                                                        <tr>
                                                                            <td><?= $total++ ?></td>
                                                                            <td><?= $data['ao_regional'] ?></td>
                                                                            <td><?php
                                                                            if ($data['laptop'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['laptop']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['printer'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['printer']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['printer'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['printer']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['markom'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['markom']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['drafter'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['drafter']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['hardisk'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['hardisk']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['handphone'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['handphone']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['cutting_plotter'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['cutting_plotter']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td>
                                                                                <a href="<?php echo site_url('GA_Aset_Office/areaOffice/' . $data['ao_regional']); ?>"
                                                                                    class="btn btn-primary"
                                                                                    style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span id="totalTabelLaptopRegional">0</span></th>
                                                                        <th><span id="totalTabelPrinterRegional">0</span></th>
                                                                        <th><span id="totalTabelScannerRegional">0</span></th>
                                                                        <th><span id="totalTabelMarkomRegional">0</span></th>
                                                                        <th><span id="totalTabelDrafterRegional">0</span></th>
                                                                        <th><span id="totalTabelHardiskRegional">0</span></th>
                                                                        <th><span id="totalTabelHandphoneRegional">0</span></th>
                                                                        <th><span id="totalTabelCuttingPlotterRegional">0</span>
                                                                        </th>
                                                                        <th></th>
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
                                            <div class="row mb-2 mt-3">
                                                <div class="col-sm-12">
                                                    <h1 class="m-0 mb-2 text-dark" style="text-align: center;">
                                                        DISTRIBUSI
                                                        ALKER KANTOR KOTA</h1>
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
                                                                        <th>Laptop</th>
                                                                        <th>Printer</th>
                                                                        <th>Scanner</th>
                                                                        <th>Markom</th>
                                                                        <th>Drafter</th>
                                                                        <th>Hardisk</th>
                                                                        <th>Handphone</th>
                                                                        <th>Cutting Plotter</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetOfficeByCityTipe as $data):

                                                                        ?>

                                                                        <tr>
                                                                            <td><?= $total++ ?></td>
                                                                            <td><?= $data['ao_area'] ?></td>
                                                                            <td><?php
                                                                            if ($data['laptop'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['laptop']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['printer'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['printer']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['printer'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['printer']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['markom'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['markom']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['drafter'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['drafter']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['hardisk'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['hardisk']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['handphone'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['handphone']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['cutting_plotter'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['cutting_plotter']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td>
                                                                                <a href="<?php echo site_url('GA_Aset_Office/areaOffice/' . $data['ao_area']); ?>"
                                                                                    class="btn btn-primary"
                                                                                    style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span id="totalTabelLaptopCity">0</span></th>
                                                                        <th><span id="totalTabelPrinterCity">0</span></th>
                                                                        <th><span id="totalTabelScannerCity">0</span></th>
                                                                        <th><span id="totalTabelMarkomCity">0</span></th>
                                                                        <th><span id="totalTabelDrafterCity">0</span></th>
                                                                        <th><span id="totalTabelHardiskCity">0</span></th>
                                                                        <th><span id="totalTabelHandphoneCity">0</span></th>
                                                                        <th><span id="totalTabelCuttingPlotterCity">0</span>
                                                                        </th>
                                                                        <th></th>
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
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
            ],
            order: [[1, 'asc']] // Urut default kolom Kode Aset
        });

        table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

        // Fungsi untuk menghitung total dari data yang tampil
        function updateTotal() {

            const data = table.rows({
                search: 'applied'
            }).data();

            

            // Hitung total dari kolom Value (index 2)
            let totalTabelLaptop = 0;
            let totalTabelPrinter = 0;
            let totalTabelScanner = 0;
            let totalTabelMarkom = 0;
            let totalTabelDrafter = 0;
            let totalTabelHardisk = 0;
            let totalTabelHandphone = 0;
            let totalTabelCuttingPlotter = 0;


            data.each(function (row) {

                totalTabelLaptop += parseFloat(row[2].replace(/\./g, '')) || 0;
                totalTabelPrinter += parseFloat(row[3].replace(/\./g, '')) || 0;
                totalTabelScanner += parseFloat(row[4].replace(/\./g, '')) || 0;
                totalTabelMarkom += parseFloat(row[5].replace(/\./g, '')) || 0;
                totalTabelDrafter += parseFloat(row[6].replace(/\./g, '')) || 0;
                totalTabelHardisk += parseFloat(row[7].replace(/\./g, '')) || 0;
                totalTabelHandphone += parseFloat(row[8].replace(/\./g, '')) || 0;
                totalTabelCuttingPlotter += parseFloat(row[9].replace(/\./g, '')) || 0;
            });

            document.getElementById('totalTabelLaptopRegional').innerText = totalTabelLaptop.toLocaleString('id-ID');
            document.getElementById('totalTabelPrinterRegional').innerText = totalTabelPrinter.toLocaleString('id-ID');
            document.getElementById('totalTabelScannerRegional').innerText = totalTabelScanner.toLocaleString('id-ID');
            document.getElementById('totalTabelMarkomRegional').innerText = totalTabelMarkom.toLocaleString('id-ID');
            document.getElementById('totalTabelDrafterRegional').innerText = totalTabelDrafter.toLocaleString('id-ID');
            document.getElementById('totalTabelHardiskRegional').innerText = totalTabelHardisk.toLocaleString('id-ID');
            document.getElementById('totalTabelHandphoneRegional').innerText = totalTabelHandphone.toLocaleString('id-ID');
            document.getElementById('totalTabelCuttingPlotterRegional').innerText = totalTabelCuttingPlotter.toLocaleString('id-ID');

            document.getElementById('totalTabelLaptopCity').innerText = totalTabelLaptop.toLocaleString('id-ID');
            document.getElementById('totalTabelPrinterCity').innerText = totalTabelPrinter.toLocaleString('id-ID');
            document.getElementById('totalTabelScannerCity').innerText = totalTabelScanner.toLocaleString('id-ID');
            document.getElementById('totalTabelMarkomCity').innerText = totalTabelMarkom.toLocaleString('id-ID');
            document.getElementById('totalTabelDrafterCity').innerText = totalTabelDrafter.toLocaleString('id-ID');
            document.getElementById('totalTabelHardiskCity').innerText = totalTabelHardisk.toLocaleString('id-ID');
            document.getElementById('totalTabelHandphoneCity').innerText = totalTabelHandphone.toLocaleString('id-ID');
            document.getElementById('totalTabelCuttingPlotterCity').innerText = totalTabelCuttingPlotter.toLocaleString('id-ID');

        }

        // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali saat tabel dimuat
        updateTotal();
    });

    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($getCountAsetOfficeAll as $stokOffice): ?>

            // Untuk kondisi Aktif
            var boxAktif = document.getElementById("box_detail_office_<?= $stokOffice['ka_jenis_aset'] ?>");
            if (boxAktif) {
                boxAktif.addEventListener("click", function () {
                    window.location.href = "<?= base_url('GA_Aset_Kantor/detailOffice/' . $stokOffice['ka_jenis_aset']) ?>";
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