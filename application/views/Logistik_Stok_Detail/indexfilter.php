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
                    <h1 class="m-0 text-dark" style="text-align: center;">SUMMARY STOK <?= "" . $title."" ?> BOWHEER FILTERED</h1>
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
                                    <h3 class="card-title">LIST STOK <?= "" . $title ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_summary_stok" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kategori Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getRincianDashboardFilteredBowheer as $data):
                                        if ($data['kategori_item'] == $kategori_item) {
                                            $total_item += $data['jumlah_stok'];
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['kategori_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ',', '.') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                            <?php
                                        }
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalSummaryStok">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">RINCIAN DETAIL STOK <?= "" . $title."" ?> FILTERED
                    </h1>
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
                                    <h3 class="card-title">LIST STOK <?= "" . $title ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_distribusi_stok" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Kota</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    echo ("<script>console.log('PHP: " . $kategori_item . "');</script>");

                                    $total = 1;
                                    foreach ($rincianFilterDashboardLogistik as $data):
                                        if ($data['kategori_item'] == $kategori_item) {
                                            $total_item_inner += $data['jumlah_stok'];
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['regional_lokasi_gudang'] ?></td>
                                                <td><?= $data['kota_lokasi_gudang'] ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ',', '.') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                            <?php
                                        }
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalDistribusiStok">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">HISTORY IN OUT MATERIAL
                        <?= "" . $title."" ?> FILTERED</h1>
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
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST HISTORY IN OUT MATERIAL</h3>
                                </div>
                                <div class="col-6">
                                    <!-- <a href="#" class="btn btn-success float-right text-bold btn-tambah-data-item"
                                            data-target="#modal-xl-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a> -->
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="tabel_inout_material" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Lokasi</th>
                                        <th>Project</th>
                                        <th>Kategori</th>
                                        <th>Item</th>
                                        <th>Status</th>
                                        <th>Tipe</th>
                                        <th>QTY</th>
                                        <th>PIC</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $total = 1;
                                    foreach ($getInOutHistoryFiltered as $data):
                                        if ($data['kategori_item'] == $kategori_item) {
                                        ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['regional_lokasi_gudang'] ?></td>
                                            <td><?= $data['kota_lokasi_gudang'] ?></td>
                                            <td><?= $data['nama_bowheer'] ?></td>
                                            <td><?= $data['kategori_item'] ?></td>
                                            <td><?= $data['nama_item'] ?></td>
                                            <td><?= $data['nama_sumber_material'] ?></td>
                                            <td><?= $data['status_sumber_material'] ?></td>
                                            <td><?= number_format($data['jumlah_stok'], 0, ',', '.') ?></td>
                                            <td><?= htmlspecialchars((string) ($data['nama_user'] ?? '-'), ENT_QUOTES) ?></td>
                                            <td><?= $data['tanggal_upload_stok'] ?></td>
                                        </tr>
                                    <?php }
                                endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
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

        $("#tabel_summary_stok").DataTable({
            "responsive": true,
        });
        $("#tabel_distribusi_stok").DataTable({
            "responsive": true,
        });
        $("#tabel_inout_material").DataTable({
            "responsive": true,
        });
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
        $.fn.dataTable.ext.errMode = 'none';

        const tabelSummaryStok = $('#tabel_summary_stok').DataTable();
        const tabelDistribusiStok = $('#tabel_distribusi_stok').DataTable();

        function updateTotalTabel() {
            let totalSummaryStok = 0, totalDistribusiStok = 0;

            tabelSummaryStok.rows({ search: 'applied' }).data().each(row => {
                totalSummaryStok += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tabelDistribusiStok.rows({ search: 'applied' }).data().each(row => {
                totalDistribusiStok += parseFloat(row[5].replace(/\./g, '')) || 0;
            });

            document.getElementById('totalSummaryStok').innerText = totalSummaryStok.toLocaleString('id-ID');
            document.getElementById('totalDistribusiStok').innerText = totalDistribusiStok.toLocaleString('id-ID');
        }

        // Tunggu sebentar agar DataTables siap sebelum memanggil updateTotalTabel()
        setTimeout(() => {
            updateTotalTabel();
        }, 500); // Tunggu 0.5 detik agar DataTables selesai inisialisasi

        // Update total saat tabel di-refresh (misalnya setelah search, filter, atau navigasi halaman)
        $('.dataTable').on('draw.dt', function () {
            updateTotalTabel();
        });
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
