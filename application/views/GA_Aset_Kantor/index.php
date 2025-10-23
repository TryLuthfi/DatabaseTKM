<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];
$ao_kondisi_aset = ['Tiang', 'OTB ', 'Kabel ', 'HDPE ', 'FDT', 'FAT', 'Closure', 'Aksesories '];

$total = 1;

$unique_regional = array_unique(array_column($getFilteredAsetKantor, 'ao_regional'));
$unique_area = array_unique(array_column($getFilteredAsetKantor, 'ao_area'));
$unique_kondisi = array_unique(array_column($getFilteredAsetKantor, 'ao_kondisi_aset'));
$unique_status = array_unique(array_column($getFilteredAsetKantor, 'ao_status_aset'));
$unique_tahunperolehan = array_unique(array_column($getFilteredAsetKantor, 'ao_tahun_perolehan'));

$progressJSON = json_encode($getFilteredAsetKantor);

?>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
        </div>

        <section class="content">
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
                                                    style="display: flex; justify-content: center; align-items: center;">REGIONAL
                                                    ASET</label>
                                                <select id="filter_regional" class="select2" multiple="multiple"
                                                    data-placeholder="Pilih Regional" style="width: 100%;">
                                                    <?php foreach ($unique_regional as $regional): ?>
                                                        <option value="<?= $regional ?>"><?= $regional ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label
                                                    style="display: flex; justify-content: center; align-items: center;">LOKASI
                                                    ASET</label>
                                                <select id="filter_area" class="select2" multiple="multiple"
                                                    data-placeholder="Pilih Area" style="width: 100%;">
                                                    <?php foreach ($unique_area as $area): ?>
                                                        <option value="<?= $area ?>"><?= $area ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label
                                                    style="display: flex; justify-content: center; align-items: center;">KONDISI
                                                    ASET</label>
                                                <select id="filter_kondisi" class="select2" multiple="multiple"
                                                    data-placeholder="Pilih Kondisi" style="width: 100%;">
                                                    <?php foreach ($unique_kondisi as $kondisi): ?>
                                                        <option value="<?= $kondisi ?>"><?= $kondisi ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label
                                                    style="display: flex; justify-content: center; align-items: center;">STATUS
                                                    ASET</label>
                                                <select id="filter_status" class="select2" multiple="multiple"
                                                    data-placeholder="Pilih Status" style="width: 100%;">
                                                    <?php foreach ($unique_status as $status): ?>
                                                        <option value="<?= $status ?>"><?= $status ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label
                                                    style="display: flex; justify-content: center; align-items: center;">TAHUN
                                                    PEROLEHAN
                                                    STOK</label>
                                                <select id="filter_tahun_perolehan" class="select2" multiple="multiple"
                                                    data-placeholder="Pilih Tahun Perolehan" style="width: 100%;">
                                                    <?php foreach ($unique_tahunperolehan as $tahunperolehan): ?>
                                                        <option value="<?= $tahunperolehan ?>"><?= $tahunperolehan ?>
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
                                            <button type="button" class="btn btn-success"
                                                data-target="#modal-download-report" data-toggle="modal">
                                                Download Report &nbsp; <i class="fas fa-print"></i>
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
                                                                <div class="small-box bg-info box-aset"
                                                                    data-jenis="<?= $stokOffice['ka_jenis_aset'] ?>">
                                                                    <div class="inner">
                                                                        <h3 class="count">
                                                                            <?= number_format($stokOffice['total_data'], 0, ",", ".") ?>
                                                                        </h3>
                                                                        <p><?= $stokOffice['ka_jenis_aset'] ?></p>
                                                                    </div>
                                                                    <div class="icon"><i class="ion ion-bag"></i></div>
                                                                    <a href="#" class="small-box-footer"
                                                                        id="<?php echo 'box_detail_office_' . $stokOffice['ka_jenis_aset'] ?>">
                                                                        Lihat Detail <i
                                                                            class="fas fa-arrow-circle-right"></i>
                                                                    </a>
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
                                                        <div class="card-body table-responsive text-nowrap">
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
                                                                                <a href="<?php echo site_url('GA_Aset_Kantor/detailOfficeArea/' . $data['ao_regional']); ?>"
                                                                                    class="btn btn-primary"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span id="totalTabelLaptopRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelPrinterRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelScannerRegional">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelMarkomRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelDrafterRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelHardiskRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelHandphoneRegional">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelCuttingPlotterRegional">0</span>
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
                                                                                <a href="<?php echo site_url('GA_Aset_Kantor/detailOfficeArea/' . $data['ao_area']); ?>"
                                                                                    class="btn btn-primary"><i
                                                                                        class=" fas fa-eye"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span id="totalTabelLaptopCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelPrinterCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelScannerCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelMarkomCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelDrafterCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelHardiskCity">0</span>
                                                                        </th>
                                                                        <th><span id="totalTabelHandphoneCity">0</span>
                                                                        </th>
                                                                        <th><span
                                                                                id="totalTabelCuttingPlotterCity">0</span>
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

        $(document).ready(function () {
            let progressData = <?= $progressJSON ?>; // Data JSON dari PHP

            function populateDropdown(selector, data, selectedValues = []) {
                let options = `<option value="">Pilih Data</option>`; // Opsi default
                data.forEach(item => {
                    let isSelected = selectedValues.includes(item) ? "selected" : "";
                    options += `<option value="${item}" ${isSelected}>${item}</option>`;
                });

                $(selector).select2('destroy').html(options).select2(); // Reset & reinit
            }

            function filterData(changedFilter) {
                setTimeout(() => { // Gunakan setTimeout agar tidak freeze
                    let selectedRegional = $('#filter_regional').val() || [];
                    let selectedArea = $('#filter_area').val() || [];
                    let selectedKondisi = $('#filter_kondisi').val() || [];
                    let selectedStatus = $('#filter_status').val() || [];
                    let selectedTahunPerolehan = $('#filter_tahun_perolehan').val() || [];

                    // Filter berdasarkan pilihan
                    let filteredData = progressData.filter(item =>
                        (selectedRegional.length === 0 || selectedRegional.includes(item.ao_regional)) &&
                        (selectedArea.length === 0 || selectedArea.includes(item.ao_area)) &&
                        (selectedKondisi.length === 0 || selectedKondisi.includes(item.ao_kondisi_aset)) &&
                        (selectedStatus.length === 0 || selectedStatus.includes(item.ao_status_aset)) &&
                        (selectedTahunPerolehan.length === 0 || selectedTahunPerolehan.includes(item.ao_tahun_perolehan))
                    );

                    let uniqueRegional = [...new Set(filteredData.map(item => item.ao_regional))];
                    let uniqueArea = [...new Set(filteredData.map(item => item.ao_area))];
                    let uniqueKondisi = [...new Set(filteredData.map(item => item.ao_kondisi_aset))];
                    let uniqueStatus = [...new Set(filteredData.map(item => item.ao_status_aset))];
                    let uniqueTahunPerolehan = [...new Set(filteredData.map(item => item.ao_tahun_perolehan))];

                    if (changedFilter !== '#filter_regional') populateDropdown('#filter_regional', uniqueRegional, selectedRegional);
                    if (changedFilter !== '#filter_area') populateDropdown('#filter_area', uniqueArea, selectedArea);
                    if (changedFilter !== '#filter_kondisi') populateDropdown('#filter_kondisi', uniqueKondisi, selectedKondisi);
                    if (changedFilter !== '#filter_status') populateDropdown('#filter_status', uniqueStatus, selectedStatus);
                    if (changedFilter !== '#filter_tahun_perolehan') populateDropdown('#filter_tahun_perolehan', uniqueTahunPerolehan, selectedTahunPerolehan);
                }, 50); // Delay kecil untuk mencegah UI freeze
            }

            // Menggunakan passive: true untuk meningkatkan performa
            document.addEventListener('scroll', function () { }, { passive: true });
            document.addEventListener('touchstart', function () { }, { passive: true });
            document.addEventListener('wheel', function () { }, { passive: true });

            $('#filter_regional, #filter_area, #filter_kondisi, #filter_status, #filter_tahun_perolehan').on('change', function () {
                let changedFilter = `#${$(this).attr('id')}`;
                filterData(changedFilter);
            });

            // Inisialisasi dropdown pertama kali
            let uniqueRegional = [...new Set(progressData.map(item => item.ao_regional))];
            let uniqueArea = [...new Set(progressData.map(item => item.ao_area))];
            let uniqueKondisi = [...new Set(progressData.map(item => item.ao_kondisi_aset))];
            let uniqueStatus = [...new Set(progressData.map(item => item.ao_status_aset))];
            let uniqueTahunPerolehan = [...new Set(progressData.map(item => item.ao_tahun_perolehan))];

            populateDropdown('#filter_regional', uniqueRegional);
            populateDropdown('#filter_area', uniqueArea);
            populateDropdown('#filter_kondisi', uniqueKondisi);
            populateDropdown('#filter_status', uniqueStatus);
            populateDropdown('#filter_tahun_perolehan', uniqueTahunPerolehan);

            $('.select2').select2();
        });

        document.getElementById('reset_filter').addEventListener('click', function () {
            const selectedRegional = document.getElementById('filter_regional');
            const selectedArea = document.getElementById('filter_area');
            const selectedKondisi = document.getElementById('filter_kondisi');
            const selectedStatus = document.getElementById('filter_status');
            const selectedTahunPerolehan = document.getElementById('filter_tahun_perolehan');

            const optionsRegional = selectedRegional.options;
            const optionsArea = selectedArea.options;
            const optionsKondisi = selectedKondisi.options;
            const optionsStatus = selectedStatus.options;
            const optionsTahunPerolehan = selectedTahunPerolehan.options;

            // Hapus semua pilihan
            for (let i = 0; i < optionsRegional.length; i++) {
                optionsRegional[i].selected = false; // Hilangkan pilihan
            }

            for (let i = 0; i < optionsArea.length; i++) {
                optionsArea[i].selected = false; // Hilangkan pilihan
            }

            for (let i = 0; i < optionsKondisi.length; i++) {
                optionsKondisi[i].selected = false; // Hilangkan pilihan
            }

            for (let i = 0; i < optionsStatus.length; i++) {
                optionsStatus[i].selected = false; // Hilangkan pilihan
            }

            for (let i = 0; i < optionsTahunPerolehan.length; i++) {
                optionsTahunPerolehan[i].selected = false; // Hilangkan pilihan
            }

            // Pilih opsi default (indeks 0)
            selectedRegional.dispatchEvent(new Event('change'));
            selectedArea.dispatchEvent(new Event('change'));
            selectedKondisi.dispatchEvent(new Event('change'));
            selectedStatus.dispatchEvent(new Event('change'));
            selectedTahunPerolehan.dispatchEvent(new Event('change'));;

            document.getElementById('btnFilterDataProject').click();
        });

        $('#btnFilterDataProject').on('click', function () {
            let filterData = {
                regional: $('#filter_regional').val(),
                area: $('#filter_area').val(),
                tahun: $('#filter_tahun_perolehan').val(),
                status: $('#filter_status').val(),
                kondisi: $('#filter_kondisi').val(),
            };

            $.ajax({
                url: '<?php echo site_url("GA_Aset_Kantor/filterDataProject"); ?>',
                type: 'POST',
                data: filterData,
                dataType: 'json',
                success: function (response) {
                    console.log("Hasil Grouping by ka_jenis_aset:", response);

                    // Reset semua box ke 0
                    $('.box-aset .count').text('0');

                    // Update box sesuai ka_jenis_aset
                    response.forEach(function (item) {
                        const jenis = item.ka_jenis_aset;
                        const total = item.total;
                        const box = $('.box-aset[data-jenis="' + jenis + '"] .count');

                        if (box.length > 0) {
                            box.text(total.toLocaleString('id-ID'));
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                }
            });
        });

    </script>

    <!-- FUNCTION FILTER UNTUK DOWNLOAD EXCEL LAPORAN STOK ASET KANTOR -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var stokAsetKantor = <?= json_encode($getReportStokAsetkantor, JSON_PRETTY_PRINT); ?>;

            // Elemen dropdown
            let selectRegional = document.getElementById("report_stok_regional");
            let selectKota = document.getElementById("report_stok_kota");
            let selectJenis = document.getElementById("report_stok_jenis");
            let selectKondisi = document.getElementById("report_stok_kondisi");
            let selectStatus = document.getElementById("report_stok_status");

            let allSelects = [selectRegional, selectKota, selectJenis, selectKondisi, selectStatus];

            // Fungsi nilai unik
            function getUniqueValues(array, key) {
                return [...new Set(array.map(item => item[key]).filter(Boolean))];
            }

            // Fungsi isi dropdown
            function populateDropdown(selectElement, data, placeholder = "Pilih") {
                let current = selectElement.value;
                selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                data.forEach(value => {
                    let option = document.createElement("option");
                    option.value = value;
                    option.textContent = value;
                    selectElement.appendChild(option);
                });
                // Pertahankan nilai jika masih valid
                if (data.includes(current)) selectElement.value = current;
            }

            // Inisialisasi awal
            populateDropdown(selectRegional, getUniqueValues(stokAsetKantor, "ao_regional"), "Pilih Regional");
            populateDropdown(selectKota, getUniqueValues(stokAsetKantor, "ao_area"), "Pilih Kota");
            populateDropdown(selectJenis, getUniqueValues(stokAsetKantor, "ka_jenis_aset"), "Pilih Jenis");
            populateDropdown(selectKondisi, getUniqueValues(stokAsetKantor, "ao_kondisi_aset"), "Pilih Kondisi");
            populateDropdown(selectStatus, getUniqueValues(stokAsetKantor, "ao_status_aset"), "Pilih Status");

            // Fungsi filter data berdasarkan semua pilihan aktif
            function getFilteredData() {
                let regional = selectRegional.value;
                let kota = selectKota.value;
                let jenis = selectJenis.value;
                let kondisi = selectKondisi.value;
                let status = selectStatus.value;

                return stokAsetKantor.filter(item =>
                    (regional === "" || item.ao_regional === regional) &&
                    (kota === "" || item.ao_area === kota) &&
                    (jenis === "" || item.ka_jenis_aset === jenis) &&
                    (kondisi === "" || item.ao_kondisi_aset === kondisi) &&
                    (status === "" || item.ao_status_aset === status)
                );
            }

            // Fungsi perbarui semua dropdown agar saling sinkron
            function updateAllDropdowns() {
                let filtered = getFilteredData();

                populateDropdown(selectRegional, getUniqueValues(filtered, "ao_regional"), "Pilih Regional");
                populateDropdown(selectKota, getUniqueValues(filtered, "ao_area"), "Pilih Kota");
                populateDropdown(selectJenis, getUniqueValues(filtered, "ka_jenis_aset"), "Pilih Jenis");
                populateDropdown(selectKondisi, getUniqueValues(filtered, "ao_kondisi_aset"), "Pilih Kondisi");
                populateDropdown(selectStatus, getUniqueValues(filtered, "ao_status_aset"), "Pilih Status");
            }

            // Setiap kali salah satu filter berubah, semua ikut update
            allSelects.forEach(select => {
                select.addEventListener("change", updateAllDropdowns);
            });

            // Fungsi format tanggal
            function getFormattedDate() {
                let now = new Date();
                return now.toISOString().replace(/[:T]/g, "-").split(".")[0];
            }

            // Format header
            function formatHeader(header) {
                return header.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
            }

            // Download Excel
            function downloadExcel() {
                let filteredData = getFilteredData();
                if (filteredData.length === 0) {
                    alert("Tidak ada data sesuai filter!");
                    return;
                }

                let formattedData = filteredData.map(row => {
                    let newRow = {};
                    for (let key in row) {
                        newRow[formatHeader(key)] = row[key];
                    }
                    return newRow;
                });

                let worksheet = XLSX.utils.json_to_sheet(formattedData);
                let workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Report");

                let filename = `Report STOK Aset Kantor ${getFormattedDate()}.xlsx`;
                XLSX.writeFile(workbook, filename);
            }

            function resetFilter() {
                const selectedRegional = document.getElementById('report_stok_regional');
                const selectedKota = document.getElementById('report_stok_kota');
                const selectedJenis = document.getElementById('report_stok_jenis');
                const selectedKondisi = document.getElementById('report_stok_kondisi');
                const selectedStatus = document.getElementById('report_stok_status');

                const optionsRegional = selectedRegional.options;
                const optionsKota = selectedKota.options;
                const optionsJenis = selectedJenis.options;
                const optionsKondisi = selectedKondisi.options;
                const optionsStatus = selectedStatus.options;

                // Hapus semua pilihan
                for (let i = 0; i < optionsRegional.length; i++) {
                    optionsRegional[i].selected = false; // Hilangkan pilihan
                }

                for (let i = 0; i < optionsKota.length; i++) {
                    optionsKota[i].selected = false; // Hilangkan pilihan
                }

                for (let i = 0; i < optionsJenis.length; i++) {
                    optionsJenis[i].selected = false; // Hilangkan pilihan
                }

                for (let i = 0; i < optionsKondisi.length; i++) {
                    optionsKondisi[i].selected = false; // Hilangkan pilihan
                }

                for (let i = 0; i < optionsStatus.length; i++) {
                    optionsStatus[i].selected = false; // Hilangkan pilihan
                }

                // Pilih opsi default (indeks 0)
                selectedRegional.dispatchEvent(new Event('change'));
                selectedKota.dispatchEvent(new Event('change'));
                selectedJenis.dispatchEvent(new Event('change'));
                selectedKondisi.dispatchEvent(new Event('change'));
                selectedStatus.dispatchEvent(new Event('change'));
            }

            document.getElementById("downloadReportStokAsetKantor").addEventListener("click", downloadExcel);
            document.getElementById("hapusReportStokAsetKantor").addEventListener("click", resetFilter);
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