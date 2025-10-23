<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');


?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">DASHBOARD RKAP 2025 ( ALL PROJECT )</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="content">
        <div class="content-header">
        </div>

        <section class="content">
            <div class="container-fluid">
                <h5 class="mb-12" style="text-align: center; margin-top:-10px; margin-bottom:30px;">REKAP INVOICE & RFS
                </h5>
            </div>
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- /.col -->

                    <div class="col-12 col-sm-6 col-md-3">
                        <a href="<?= base_url("Pengeluaran") ?>">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger elevation-1"><i
                                        class="fas fa-file-invoice-dollar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">TARGET INVOICE TW - 4</span>
                                    <span class="info-box-number" id="dashboardTargetInvoice">
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
                                    <span class="info-box-text">ACHIEVE INVOICE TW - 4</span>
                                    <span class="info-box-number" id="dashboardAchievInvoice">
                                        Rp. 0
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
                                <span class="info-box-icon bg-success elevation-1"><i
                                        class="fas fa-money-check-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">SISA INVOICE TW - 4</span>
                                    <span class="info-box-number" id="dashboardSisaInvoice">
                                        Rp. 0
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
                                <span class="info-box-icon bg-success elevation-1"><i
                                        class="fas fa-money-check-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">PERSENTASE INVOICE TW - 4</span>
                                    <span class="info-box-number" id="dashboardPersentaseInvoice">
                                        Rp. 0
                                    </span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                        </a>
                        <!-- /.info-box -->
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header border-0">
                                    <div class="d-flex justify-content-between">
                                        <h3 class="card-title">Achieved Invoice TW - 4</h3>
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
                                        <canvas id="rkap_chart_line" height="200"></canvas>
                                    </div>

                                    <div class="d-flex flex-row justify-content-end">
                                        <span class="mr-3">
                                            <i class="fas fa-square text-primary"></i> Target RKAP
                                        </span>

                                        <span class="mr-3">
                                            <i class="fas fa-square text-green"></i> Achieved PO
                                        </span>

                                        <span class="mr-3">
                                            <i class="fas fa-square text-orange"></i> Achieved Invoice
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
                                        <h3 class="card-title">Achieved Invoice TW - 4</h3>
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
                                        <canvas id="rkap_chart_bar" height="200"></canvas>
                                    </div>

                                    <div class="d-flex flex-row justify-content-end">
                                        <span class="mr-3">
                                            <i class="fas fa-square text-primary"></i> Target RKAP
                                        </span>

                                        <span class="mr-3">
                                            <i class="fas fa-square text-green"></i> Achieved PO
                                        </span>

                                        <span class="mr-3">
                                            <i class="fas fa-square text-orange"></i> Achieved Invoice
                                        </span>

                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

        </section>

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


                                    <div class="content-header">
                                        <div class="container-fluid">
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                    <h1 class="m-0 mb-2 text-dark" style="text-align: center;">
                                                        LIST BOWHEER</h1>
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
                                                            <table id="table_target_bowheer"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info" style="text-align: center;">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>PROJECT</th>
                                                                        <th>PIC TKM</th>
                                                                        <th>TARGET INVOICE</th>
                                                                        <th>ACHIEVED INVOICE</th>
                                                                        <th>OUTSTANDING</th>
                                                                        <th>ACHIEVED ( % )</th>
                                                                        <th>OUTSTANDING ( % )</th>
                                                                        <th>DETAIL</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getTargetAllBowheer as $data):

                                                                        ?>

                                                                        <tr>
                                                                            <td><?= $total++ ?></td>
                                                                            <td><?= $data['nama_bowheer'] ?></td>
                                                                            <td><?= $data['pic_user'] ?></td>
                                                                            <td><?php
                                                                            if ($data['total_target'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['total_achiev'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['total_achiev']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['deviasi'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['deviasi']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?= rtrim(rtrim(number_format($data['persen_achiev'], 2, '.', ''), '0'), '.') ?>%
                                                                            </td>
                                                                            <td><?= rtrim(rtrim(number_format($data['persen_deviasi'], 2, '.', ''), '0'), '.') ?>%
                                                                            </td>
                                                                            <td>
                                                                                <a href="<?php echo site_url('TargetInvoice/detailBowheer/' . $data['id_bowheer']); ?>"
                                                                                    class="btn btn-primary"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="3" style="text-align: center;">
                                                                            TOTAL</th>
                                                                        <th><span
                                                                                id="totalTargetInvoiceBowheer">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalAchievInvoiceBowheer">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalDeviasiInvoiceBowheer">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalPersentaseTargetInvoiceBowheer">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalPersentaseDeviasiTargetInvoiceBowheer">0</span>
                                                                        </th>
                                                                        </th>
                                                                        <th></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                        <!-- /.card-body -->
                                                    </div>
                                                    <a href="<?= base_url('TargetInvoice/weekBowheer/') ?>"
                                                            class="text-decoration-none">
                                                            <h5 class="text-center mb-4 font-weight-bold text-primary"
                                                                style="text-decoration: underline;">
                                                                Lihat Rincian Mingguan &#8594;
                                                            </h5>
                                                        </a>
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
                                                        LIST KOTA</h1>
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
                                                            <table id="table_target_city"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info" style="text-align: center;">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>REGIONAL</th>
                                                                        <th>KOTA</th>
                                                                        <th>PIC AREA</th>
                                                                        <th>TARGET INVOICE</th>
                                                                        <th>ACHIEVED INVOICE</th>
                                                                        <th>OUTSTANDING</th>
                                                                        <th>ACHIEVED ( % )</th>
                                                                        <th>OUTSTANDING ( % )</th>
                                                                        <th>DETAIL</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getTargetAllCity as $data):

                                                                        ?>

                                                                        <tr>
                                                                            <td><?= $total++ ?></td>
                                                                            <td><?= $data['regional_target'] ?></td>
                                                                            <td><?= $data['area_target'] ?></td>
                                                                            <td><?= $data['pic_user'] ?></td>
                                                                            <td><?php
                                                                            if ($data['total_target'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['total_achiev'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['total_achiev']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['deviasi'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['deviasi']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?= rtrim(rtrim(number_format($data['persen_achiev'], 2, '.', ''), '0'), '.') ?>%
                                                                            </td>
                                                                            <td><?= rtrim(rtrim(number_format($data['persen_deviasi'], 2, '.', ''), '0'), '.') ?>%
                                                                            </td>
                                                                            <td>
                                                                                <a href="<?php echo site_url('TargetInvoice/detailKota/' . $data['area_target']); ?>"
                                                                                    class="btn btn-primary"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="4" style="text-align: center;">
                                                                            TOTAL</th>
                                                                        <th><span id="totalTargetInvoiceCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalAchievInvoiceCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalDeviasiInvoiceCity">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalPersentaseTargetInvoiceCity">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalPersentaseDeviasiTargetInvoiceCity">0</span>
                                                                        </th>
                                                                        </th>
                                                                        <th></th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                        <a href="<?= base_url('TargetInvoice/weekCity/') ?>"
                                                            class="text-decoration-none">
                                                            <h5 class="text-center mb-4 font-weight-bold text-primary"
                                                                style="text-decoration: underline;">
                                                                Lihat Rincian Mingguan &#8594;
                                                            </h5>
                                                        </a>
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
                                <select name="ao_kondisi_aset" class="form-control">
                                    <?php foreach ($ao_kondisi_aset as $option): ?>
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
                                <select name="project_item" class="form-control" data-placeholder="Pilih jenis"
                                    style="width: 100%;">
                                    <?php foreach ($getMasterjenis as $data): ?>
                                        <option value="<?php echo $data['nama_jenis'] ?>">
                                            <?php echo $data['nama_jenis'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Kepemilikan Item</label>
                                <select name="id_jenis_pemilik_item" class="form-control">
                                    <?php foreach ($getMasterjenis as $data): ?>
                                        <option value="<?php echo $data['id_jenis'] ?>">
                                            <?php echo $data['nama_jenis'] ?>
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

        <div class="modal fade" id="modal-download-report" data-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLongTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">DOWNLOAD REPORT STOK ASET KANTOR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="isi_report_in_out_logistik" class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Regional Gudang</label>
                                        <select id="report_stok_regional" class="form-control">
                                            <option value="">Pilih Regional</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Lokasi (Kota)</label>
                                        <select id="report_stok_kota" class="form-control">
                                            <option value="">Pilih Kota</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Jenis Aset</label>
                                        <select id="report_stok_jenis" class="form-control">
                                            <option value="">Pilih jenis</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Kondisi Aset</label>
                                        <select id="report_stok_kondisi" class="form-control">
                                            <option value="">Pilih Kategori</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Status Aset</label>
                                        <select id="report_stok_status" class="form-control">
                                            <option value="">Pilih Item</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3 d-flex justify-content-end">
                                    <button id="hapusReportStokAsetKantor" class="btn btn-secondary mt-2 mr-2">
                                        Hapus Filter
                                    </button>
                                    <button id="downloadReportStokAsetKantor" class="btn btn-primary mt-2">
                                        Download Excel 🚀
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
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
            $('#table_target_bowheer').DataTable({
                "paging": false, // Tetap gunakan pagination
                "pageLength": 10, // Menampilkan 10 data per halaman
                "info": false, // Menghilangkan "Showing 1 to X of X entries"
                "searching": true, // Menghilangkan search bar
                "lengthChange": false // Menghilangkan dropdown "Show entries"
            });
        });

        $(document).ready(function () {
            $('#table_target_city').DataTable({
                "paging": true, // Tetap gunakan pagination
                "pageLength": 10, // Menampilkan 10 data per halaman
                "info": false, // Menghilangkan "Showing 1 to X of X entries"
                "searching": true, // Menghilangkan search bar
                "lengthChange": false // Menghilangkan dropdown "Show entries"
            });
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#table_target_bowheer').DataTable({
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
                let totalTargetInvoiceBowheer = 0;
                let totalAchievInvoiceBowheer = 0;
                let totalDeviasiInvoiceBowheer = 0;
                let totalPersentaseTargetInvoiceBowheer = 0;
                let totalPersentaseDeviasiTargetInvoiceBowheer = 0;

                data.each(function (row) {

                    totalTargetInvoiceBowheer += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalAchievInvoiceBowheer += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalDeviasiInvoiceBowheer += parseFloat(row[5].replace(/\./g, '')) || 0;
                    totalPersentaseTargetInvoiceBowheer += parseFloat(row[6].replace(/\./g, '')) || 0;
                    totalPersentaseDeviasiTargetInvoiceBowheer += parseFloat(row[7].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceBowheer').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalAchievInvoiceBowheer').innerText = totalAchievInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalDeviasiInvoiceBowheer').innerText = totalDeviasiInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceBowheer').innerText = (totalAchievInvoiceBowheer / totalTargetInvoiceBowheer * 100) + "%";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceBowheer').innerText = (totalDeviasiInvoiceBowheer / totalTargetInvoiceBowheer * 100) + "%";

                document.getElementById('dashboardTargetInvoice').innerText = "RP. " + totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardAchievInvoice').innerText = "RP. " + totalAchievInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardSisaInvoice').innerText = "RP. " + totalDeviasiInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardPersentaseInvoice').innerText = (totalAchievInvoiceBowheer / totalTargetInvoiceBowheer * 100) + "%";
            }

            // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali saat tabel dimuat
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#table_target_city').DataTable({
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

                let totalTargetInvoiceCity = 0;
                let totalAchievInvoiceCity = 0;
                let totalDeviasiInvoiceCity = 0;
                let totalPersentaseTargetInvoiceCity = 0;
                let totalPersentaseDeviasiTargetInvoiceCity = 0;


                data.each(function (row) {
                    totalTargetInvoiceCity += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalAchievInvoiceCity += parseFloat(row[5].replace(/\./g, '')) || 0;
                    totalDeviasiInvoiceCity += parseFloat(row[6].replace(/\./g, '')) || 0;
                    totalPersentaseTargetInvoiceCity += parseFloat(row[7].replace(/\./g, '')) || 0;
                    totalPersentaseDeviasiTargetInvoiceCity += parseFloat(row[8].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceCity').innerText = totalTargetInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalAchievInvoiceCity').innerText = totalAchievInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalDeviasiInvoiceCity').innerText = totalDeviasiInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceCity').innerText = (totalAchievInvoiceCity / totalTargetInvoiceCity * 100) + "%";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceCity').innerText = (totalDeviasiInvoiceCity / totalTargetInvoiceCity * 100) + "%";

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
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
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
    <link rel="stylesheet"
        href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
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
        href="<?= base_url('assets') ?>/plugins/tempusdominus-b ootstrap-4/js/tempusdominus-bootstrap-4.min.js">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/summernote/summernote-bs4.min.js">
    <link rel="stylesheet"
        href="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/demo.js">
    <link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/pages/dashboard.js">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartfibertstar.js"></script>
    <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartmyrep.js"></script>
    <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardrkap.js"></script>