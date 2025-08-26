<?php $status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
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
                <div class="row clearfix hidden-md-up">
                    <div class="col-md-12">
                        <div class="card">
                            <form action="<?= base_url('StockOpname/inputSO') ?>" method="post" id="form-so"
                                enctype="multipart/form-data">
                                <div class="card-header">
                                    <h3 class="card-title">INPUT STOK OPNAME</h3>
                                </div>
                                <div class="card-body table-scrollable">
                                    <input type="text" name="id_sop" id="" value="<?= $id_sop ?>" hidden>
                                    <input type="text" name="id_lokasi_gudang" id=""
                                        value="<?= $getSOItem[0]['id_lokasi_gudang'] ?>" hidden>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Regional Gudang</label>
                                                <h5><?= $getSOItem[0]['regional_lokasi_gudang'] ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Periode Stock Opname</label>
                                                <h5><?= $getDetailSoPeriode[0]['sop_bulan'] . " - " . $getDetailSoPeriode[0]['sop_tahun'] ?>
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Provinsi Gudang</label>
                                                <h5><?= $getSOItem[0]['provinsi_lokasi_gudang'] ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Lokasi Gudang</label>
                                                <h5><?= $getSOItem[0]['kota_lokasi_gudang'] ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-bordered mt-2" id="table_item_stok">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">No</th>
                                                        <th style="width: 10%;">Project</th>
                                                        <th style="width: 5%;">Kode Item</th>
                                                        <th style="width: 5%;">Kategori</th>
                                                        <th style="width: 15%;">Nama Item</th>
                                                        <th style="width: 8%;">Satuan Item</th>
                                                        <th style="width: 8%;">Stok Aplikasi</th>
                                                        <th style="width: 8%;">Stok SO</th>
                                                        <?php if ($mode == "1bda80f2be4d3658e0baa43fbe7ae8c1"): ?>
                                                            <th style="width: 8%;">Selisih Stok</th>
                                                        <?php endif; ?>
                                                        <th style="width: 18%;">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $number = 1; ?>

                                                    <!-- mode view -->
                                                    <?php if ($mode == "1bda80f2be4d3658e0baa43fbe7ae8c1"): ?>
                                                        <?php foreach ($getDetailSoItem as $index => $data): ?>
                                                            <tr>
                                                                <td><?= $total++ ?></td>
                                                                <td><?= $data['project_item'] ?></td>
                                                                <td>
                                                                    <?= $data['id_kode_item'] ?>
                                                                </td>
                                                                <td><?= $data['kategori_item'] ?></td>
                                                                <td><?= $data['nama_item'] ?></td>
                                                                <td><?= $data['satuan_item'] ?></td>
                                                                <td>
                                                                    <?= number_format(floatval($data['soi_stok_asli']), 0, ",", "."); ?>
                                                                </td>
                                                                <td>
                                                                    <?= number_format(floatval($data['soi_stok_opname']), 0, ",", "."); ?>
                                                                </td>
                                                                <td>
                                                                    <?= number_format(floatval($data['soi_stok_asli'] - $data['soi_stok_opname']), 0, ",", "."); ?>
                                                                </td>
                                                                <td><?= $data['soi_keterangan'] ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <!-- mode input -->
                                                    <?php if ($mode == "a43c1b0aa53a0c908810c06ab1ff3967"): ?>
                                                        <?php foreach ($getSOItem as $index => $data): ?>
                                                            <tr>
                                                                <td><?= $total++ ?></td>
                                                                <td><?= $data['project_item'] ?></td>
                                                                <td>
                                                                    <?= $data['id_kode_item'] ?>
                                                                    <input type="hidden" name="id_kode_item[<?= $index ?>]"
                                                                        value="<?= $data['id_kode_item'] ?>">
                                                                </td>
                                                                <td><?= $data['kategori_item'] ?></td>
                                                                <td><?= $data['nama_item'] ?></td>
                                                                <td><?= $data['satuan_item'] ?></td>
                                                                <td>
                                                                    <?= number_format(floatval($data['total_jumlah_stok']), 0, ",", "."); ?>
                                                                    <input type="hidden" name="total_jumlah_stok[<?= $index ?>]"
                                                                        value="<?= $data['total_jumlah_stok'] ?>">
                                                                </td>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="stok_so[<?= $index ?>]" placeholder="0" min="0">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control"
                                                                        name="keterangan[<?= $index ?>]"
                                                                        placeholder="Keterangan...">
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <!-- mode edit -->
                                                    <?php if ($mode == "de95b43bceeb4b998aed4aed5cef1ae7"): ?>
                                                        <input type="hidden" name="is_edit" value="1" id="">
                                                        <?php foreach ($getDetailSoItem as $index => $data): ?>
                                                            <tr>
                                                                <td><?= $total++ ?></td>
                                                                <td><?= $data['project_item'] ?></td>
                                                                <td>
                                                                    <?= $data['id_kode_item'] ?>
                                                                    <input type="hidden" name="id_kode_item[<?= $index ?>]"
                                                                        value="<?= $data['id_kode_item'] ?>">
                                                                </td>
                                                                <td><?= $data['kategori_item'] ?></td>
                                                                <td><?= $data['nama_item'] ?></td>
                                                                <td><?= $data['satuan_item'] ?></td>
                                                                <td>
                                                                    <?= number_format(floatval($data['soi_stok_asli']), 0, ",", "."); ?>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control"
                                                                        name="soi_stok_opname[<?= $index ?>]"
                                                                        value="<?= $data['soi_stok_opname'] ?>">
                                                                </td>
                                                                <td><input type="text" class="form-control"
                                                                        name="keterangan[<?= $index ?>]"
                                                                        value="<?= $data['soi_keterangan'] ?>"
                                                                        placeholder="Keterangan..."></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3"><b>TOTAL</b></td>
                                                        <td colspan="1"></td>
                                                        <td colspan="1"></td>
                                                        <td colspan="1"></td>
                                                        <?php if ($mode == "1bda80f2be4d3658e0baa43fbe7ae8c1"): ?>
                                                            <td><b><?= number_format(floatval(array_sum(array_column($getDetailSoItem, 'soi_stok_asli'))), 0, ",", "."); ?></b>
                                                            </td>
                                                            <td><b><?= number_format(floatval(array_sum(array_column($getDetailSoItem, 'soi_stok_opname'))), 0, ",", "."); ?></b>
                                                            </td>
                                                            <td><b><?= number_format(floatval(array_sum(array_column($getDetailSoItem, 'soi_stok_asli')) - array_sum(array_column($getDetailSoItem, 'soi_stok_opname'))), 0, ",", "."); ?></b>
                                                            </td>
                                                        <?php endif; ?>
                                                        <?php if ($mode == "a43c1b0aa53a0c908810c06ab1ff3967"): ?>
                                                            <td><b><?= number_format(floatval(array_sum(array_column($getSOItem, 'total_jumlah_stok'))), 0, ",", "."); ?></b>
                                                            </td>
                                                            <td><b id="total_qty_planning">0</b></td>
                                                        <?php endif; ?>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <?php if ($mode == "a43c1b0aa53a0c908810c06ab1ff3967"): ?>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary float-right text-bold">Simpan Stock
                                                Opname</button>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($mode == "de95b43bceeb4b998aed4aed5cef1ae7"): ?>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary float-right text-bold">Simpan Edit Stock
                                                Opname</button>
                                        </div>
                                        <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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