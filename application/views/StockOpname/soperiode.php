<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$bulan_arr = [
    "JANUARI",
    "FEBRUARI",
    "MARET",
    "APRIL",
    "MEI",
    "JUNI",
    "JULI",
    "AGUSTUS",
    "SEPTEMBER",
    "OKTOBER",
    "NOVEMBER",
    "DESEMBER"
];

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
                <!-- Info boxes -->
                <div class="row">
                    <!-- fix for small devices only -->
                    <div class="clearfix hidden-md-up"></div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h3 class="card-title">List Area Dashboard </h3>
                                    </div>
                                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                                        <div class="col-6">
                                            <a href="#" class="btn btn-success float-right text-bold"
                                                data-target="#modal-lg-tambah-periode" data-toggle="modal">Tambah Periode
                                                &nbsp;<i class="fas fa-plus"></i> </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-scrollable">
                                <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tahun</th>
                                            <th>Bulan</th>
                                            <th>Status</th>
                                            <th>Persentase Kota</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getSOPeriode as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['sop_tahun'] ?></td>
                                                <td><?= $data['sop_bulan'] ?></td>
                                                <td><?= $data['sop_status'] ?></td>
                                                <td><?= $data['persentasi_so_kota'] ?></td>
                                                <td>
                                                    <a href="<?php echo site_url('StockOpname/periode/' . $data['id_sop']); ?>"
                                                        class="btn btn-success btn-sm">
                                                        Detail &nbsp;<i class="fas fa-share"></i>
                                                    </a>
                                                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                                                        <a href="<?php echo site_url('StockOpname/hapusPeriode/' . $data['id_sop']); ?>"
                                                            class="btn btn-danger btn-sm">
                                                            Delete &nbsp;<i class="fas fa-trash"></i></a>
                                                        <!-- <a href="#" class="btn btn-warning btn-sm"
                                                            data-target="#modal-lg-edit<?= $data['id_sop'] ?>"
                                                            data-toggle="modal"> Edit &nbsp; <i class="fas fa-edit"></i></a> -->
                                                    <?php } ?>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="1">Total</th>
                                            <th colspan="5"><?= number_format($total - 1, 0, ',', '.') ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
        </section>

        <!-- MODAL TAMBAH PERIODE SO -->
        <form id="form-tambah-periode" action="<?php echo base_url('StockOpname/tambahPeriode') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-periode">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">TAMBAH PERIODE SO</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-form-label">Pilih Bulan</label>
                                <select class="form-control" id="so_bulan" name="so_bulan">
                                    <option value="">Pilih Bulan</option>
                                    <?php
                                    $bulan_sekarang = date("n") - 1; // Ambil index bulan sekarang (0-11)
                                    
                                    foreach ($bulan_arr as $index => $bulan) {
                                        $selected = ($index == $bulan_sekarang) ? "selected" : "";
                                        echo "<option value='$bulan' $selected>$bulan</option>";
                                    }
                                    ?>
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tahun">Pilih Tahun</label>
                                <select class="form-control" id="so_tahun" name="so_tahun">
                                    <?php
                                    $tahun_sekarang = date("Y") + 1; // Tahun sekarang +1
                                    for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 1; $i--) {
                                        $selected = ($i == $tahun_sekarang - 1) ? "selected" : "";
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                <button type="button" name="btnSubmitPeriode"
                                    class="btn btn-primary btnSubmitPeriode"><i class="fa fa-spinner fa-spin loading"
                                        style="display:none"></i> Tambah</button>
                            </div>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
        </form>

        <!-- MODAL EDIT LOKASI GUDANG LOGISTIK -->
        <?php foreach ($getSOPeriode as $data): ?>
            <form action="<?php echo site_url('Master_Logistik_Lokasi_Gudang/editLokasiGudang/' . $data['id_sop']); ?>"
                method="post">
                <div class="modal fade" id="modal-lg-edit<?= $data['id_sop'] ?>">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Lokasi Gudang</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-form-label">Level</label>
                                    <select name="regional_lokasi_gudang" class="form-control">
                                        <option value="REGIONAL 1" <?php if ($data['regional_lokasi_gudang'] == 'REGIONAL 1') { ?>selected <?php } ?>>REGIONAL 1</option>
                                        <option value="REGIONAL 2" <?php if ($data['regional_lokasi_gudang'] == 'REGIONAL 2') { ?>selected <?php } ?>>REGIONAL 2</option>
                                        <option value="REGIONAL 3" <?php if ($data['regional_lokasi_gudang'] == 'REGIONAL 3') { ?>selected <?php } ?>>REGIONAL 3</option>
                                        <option value="REGIONAL 4" <?php if ($data['regional_lokasi_gudang'] == 'REGIONAL 4') { ?>selected <?php } ?>>REGIONAL 4</option>
                                        <option value="REGIONAL 5" <?php if ($data['regional_lokasi_gudang'] == 'REGIONAL 5') { ?>selected <?php } ?>>REGIONAL 5</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Provinsi</label>
                                    <input type="text" class="form-control" name="provinsi_lokasi_gudang" autocomplete="off"
                                        value="<?= $data['provinsi_lokasi_gudang'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Kota</label>
                                    <input type="text" class="form-control" name="kota_lokasi_gudang" autocomplete="off"
                                        value="<?= $data['kota_lokasi_gudang'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Kecamatan</label>
                                    <input type="text" class="form-control" name="alamat_lokasi_gudang"
                                        autocomplete="off" value="<?= $data['alamat_lokasi_gudang'] ?>">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Person In Control</label>
                                    <select name="id_user" class="form-control">
                                        <?php foreach ($getMasterUser as $data2): ?>
                                            <option value="<?php echo $data2['id_user'] ?>" <?php if ($data2['id_user'] == $data['id_user']) { ?>selected <?php } ?>>
                                                <?php echo $data2['nama_user'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                    <button type="submit" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading"
                                            style="display:none"></i> Edit</button>
                                </div>
                            </div>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
            </form>
        <?php endforeach; ?>

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
        $('[data-toggle="tooltip"]').tooltip();

        var selectedBulan = $("#so_bulan option:selected").text().trim();
        var selectedTahun = $("#so_tahun option:selected").text().trim();

        $("#so_tahun, #so_bulan").on("blur change", function () {
            selectedBulan = $("#so_bulan option:selected").text().trim();
            selectedTahun = $("#so_tahun option:selected").text().trim();

            console.log("Bulan: ", selectedBulan);
            console.log("Tahun: ", selectedTahun);

        });

        $(".btnSubmitPeriode").click(function () {
            if (selectedBulan !== "" && selectedTahun !== "") {
                $.ajax({
                    url: "<?php echo site_url('StockOpname/cekPeriode'); ?>",
                    type: "POST",
                    data: {
                        selectedBulan: selectedBulan,
                        selectedTahun: selectedTahun
                    },
                    dataType: "json",
                    success: function (response) {
                        console.log("AJAX Cuccess : ", response);
                        if (response.status === "exists") {

                            alert("Periode " + selectedBulan + " " + selectedTahun + " sudah ada, silahkan pilih periode lain.");

                        } else if (response.status === "available") {
                            console.log("Form akan disubmit...");
                            $("#form-tambah-periode").submit(); // Submit form dengan ID
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log("AJAX Error:", status, error);
                        alert("Terjadi kesalahan saat mengecek data. Coba lagi.");
                    }
                });
            }
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