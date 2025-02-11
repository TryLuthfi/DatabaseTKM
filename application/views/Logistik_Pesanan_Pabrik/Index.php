<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
$satuan_options = ['Kabel', 'Tiang', 'HDPE', 'Closure', 'OTB','FAT', 'FDT', 'Aksesoris'];
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
                                        <h3 class="card-title">List Pabrik</h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-lg-tambah-pabrik" data-toggle="modal">Tambah
                                            &nbsp;<i class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-scrollable">
                                <table id="tabel_pesanan_pabrik" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor PO</th>
                                            <th>Pabrik</th>
                                            <th>Item</th>
                                            <th>QTY Pesanan</th>
                                            <th>QTY Pengiriman</th>
                                            <th>QTY Sisa</th>
                                            <th>Nominal PO</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getOustandingPesananPabrik as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nomor_po_pabrik'] ?></td>
                                                <td><?= $data['nama_pabrik'] ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= number_format($data['qty_material_pesanan'], 0, '.', ',') ?></td>
                                                <td><?= number_format($data['total_qty_material_pengiriman'], 0, '.', ',') ?></td>
                                                <td><?= number_format($data['sisa_qty_material_pengiriman'], 0, '.', ',') ?></td>
                                                <td><?= number_format($data['harga_total_po_pabrik'], 0, '.', ',') ?></td>
                                                <td>
                                                    <a href="<?php echo site_url('Master_Logistik_Pabrik/hapusPabrik/' . $data['id_pabrik']); ?>"
                                                        id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                            class=" fas fa-trash"></i></a>
                                                    <a href="#" class="btn btn-warning"
                                                        data-target="#modal-lg-edit<?= $data['id_pabrik'] ?>"
                                                        data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="1">Total</th>
                                            <th colspan="3"></th>
                                            <th colspan="1"><span id="totalQTYPesanan"></span></th>
                                            <th colspan="1"><span id="totalQTYPengiriman"></span></th>
                                            <th colspan="1"><span id="totalQTYSisa"></span></th>
                                            <th colspan="1"><span id="totalQTYHargaPO"></span></th>
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

    </section>
</div>
<!-- /.content-wrapper -->

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
    setTimeout(function () {
        const table = $('#tabel_pesanan_pabrik').DataTable();

        function updateTotal() {
            let totalQTYPesanan = 0;
            let totalQTYPengiriman = 0;
            let totalQTYSisa = 0;
            let totalQTYHargaPO = 0;

            table.rows({ search: 'applied' }).data().each(function (row) {

                if (row['4'] != 0) {
                    totalQTYPesanan += parseFloat(row[4].replace(/,/g, ''))
                } if (row['5'] != 0) {
                    totalQTYPengiriman += parseFloat(row[5].replace(/,/g, ''))
                } if (row['6'] != 0) {
                    totalQTYSisa += parseFloat(row[6].replace(/,/g, ''))
                } if (row['7'] != 0) {
                    totalQTYHargaPO += parseFloat(row[7].replace(/,/g, ''))
                }

            });
            $('#totalQTYPesanan').text(totalQTYPesanan.toLocaleString('id-ID'));
            $('#totalQTYPengiriman').text(totalQTYPengiriman.toLocaleString('id-ID'));
            $('#totalQTYSisa').text(totalQTYSisa.toLocaleString('id-ID'));
            $('#totalQTYHargaPO').text(totalQTYHargaPO.toLocaleString('id-ID'));
        }

        table.on('search.dt draw.dt', updateTotal);
        updateTotal();
    }, 1);
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