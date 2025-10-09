<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];
$kategori_item = ['Tiang', 'OTB ', 'Kabel ', 'HDPE ', 'FDT', 'FAT', 'Closure', 'Aksesories '];

$total = 1;
$totalTabelSplicer = 0;
$totalTabelOtdr = 0;
$totalTabelGps = 0;
$totalTabelCamera360 = 0;
$totalTabelUtg = 0;
$totalTabelTanggateleskopik = 0;
$totalTabelOls = 0;
$totalTabelOpm = 0;
$totalTabelOfi = 0;
$totalTabelLabelit = 0;
$totalTabelPowerinverter = 0;
$totalTabelRollmeter = 0;
$totalTabelToolkits = 0;
$totalTabelCleaver = 0;
$totalTabelStripper = 0;
$totalTabelSlitter = 0;
$totalTabelSenteroptik = 0;
$totalTabelSabuksafety = 0;
$totalTabelImpactdrill = 0;
$totalTabelElektroda = 0;
$totalTabelTestergrounding = 0;
$totalTabelMesinkerja = 0;


foreach ($getCountAsetTerminasiByRegionTipe1 as $data):
    $totalTabelSplicer += intval($data['splicer']);
    $totalTabelOtdr += intval($data['otdr']);
    $totalTabelGps += intval($data['gps']);
    $totalTabelCamera360 += intval($data['camera_360']);
    $totalTabelUtg += intval($data['utg']);
    $totalTabelTanggateleskopik += intval($data['tangga_teleskopik']);
    $totalTabelOls += intval($data['ols']);
    $totalTabelOpm += intval($data['opm']);
endforeach;
foreach ($getCountAsetTerminasiByRegionTipe2 as $data):
    $totalTabelOfi += intval($data['ofi']);
    $totalTabelLabelit += intval($data['label_it']);
    $totalTabelPowerinverter += intval($data['power_inverter']);
    $totalTabelRollmeter += intval($data['roll_meter']);
    $totalTabelToolkits += intval($data['toolkits']);
    $totalTabelCleaver += intval($data['cleaver']);
    $totalTabelStripper += intval($data['stripper']);
    $totalTabelSlitter += intval($data['slitter']);
endforeach;
foreach ($getCountAsetTerminasiByRegionTipe3 as $data):
    $totalTabelSenteroptik += intval($data['senter_optik']);
    $totalTabelSabuksafety += intval($data['sabuk_safety']);
    $totalTabelImpactdrill += intval($data['impact_drill']);
    $totalTabelElektroda += intval($data['elektroda']);
    $totalTabelTestergrounding += intval($data['tester_grounding']);
    $totalTabelMesinkerja += intval($data['mesin_kerja']);
endforeach;
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
                                                    <h1 class="m-0 mb-2 text-dark" style="text-align: center;">
                                                        DISTRIBUSI
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
                                                            <table id="table_detail_kota1"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Regional</th>
                                                                        <th>Splicer</th>
                                                                        <th>OTDR</th>
                                                                        <th>GPS</th>
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
                                                                        <th><span><?= $totalTabelSplicer ?></span></th>
                                                                        <th><span><?= $totalTabelOtdr ?></span></th>
                                                                        <th><span><?= $totalTabelGps ?></span></th>
                                                                        <th><span><?= $totalTabelCamera360 ?></span></th>
                                                                        <th><span><?= $totalTabelUtg ?></span></th>
                                                                        <th><span><?= $totalTabelTanggateleskopik ?></span>
                                                                        </th>
                                                                        <th><span><?= $totalTabelOls ?></span></th>
                                                                        <th><span><?= $totalTabelOpm ?></span></th>
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
                                                            <table id="table_detail_tipe2"
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
                                                                                    class="btn btn-primary"
                                                                                    style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"><i
                                                                                        class=" fas fa-eye"></i></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span><?= $totalTabelOfi ?></span></th>
                                                                        <th><span><?= $totalTabelLabelit ?></span></th>
                                                                        <th><span><?= $totalTabelPowerinverter ?></span></th>
                                                                        <th><span><?= $totalTabelRollmeter ?></span></th>
                                                                        <th><span><?= $totalTabelToolkits ?></span></th>
                                                                        <th><span><?= $totalTabelCleaver ?></span></th>
                                                                        <th><span><?= $totalTabelStripper ?></span></th>
                                                                        <th><span><?= $totalTabelSlitter ?></span></th>
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
                                            <div class="row mb-2">
                                                <div class="col-sm-12">
                                                    <h3 class="m-0 text-dark" style="text-align: center;">TIPE 3</h3>
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
                                                            <table id="table_detail_tipe3"
                                                                class="table table-bordered table-hover">
                                                                <thead class="bg-info">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Regional</th>
                                                                        <th>Senter Optik</th>
                                                                        <th>Sabuk Safety</th>
                                                                        <th>Impact Drill</th>
                                                                        <th>Elektroda</th>
                                                                        <th>Tester Grounding</th>
                                                                        <th>Mesin Kerja</th>
                                                                        <th>Detail</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 1;

                                                                    foreach ($getCountAsetTerminasiByRegionTipe3 as $data):

                                                                        ?>

                                                                        <tr>
                                                                            <td><?= $total++ ?></td>
                                                                            <td><?= $data['at_regional'] ?></td>
                                                                            <td><?php
                                                                            if ($data['senter_optik'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['senter_optik']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['sabuk_safety'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['sabuk_safety']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['impact_drill'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['impact_drill']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['elektroda'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['elektroda']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['tester_grounding'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['tester_grounding']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td><?php
                                                                            if ($data['mesin_kerja'] == "0") {
                                                                                echo "-";
                                                                            } else {
                                                                                echo number_format(floatval($data['mesin_kerja']), 0, ",", ".");
                                                                            }
                                                                            ?></td>
                                                                            <td>
                                                                                <a href="<?php echo site_url('GA_Aset_Terminasi/areaTerminasi/' . $data['at_regional']); ?>"
                                                                                    class="btn btn-primary"
                                                                                    style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"><i
                                                                                        class=" fas fa-eye"></i></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach; ?>

                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th colspan="2">TOTAL</th>
                                                                        <th><span><?= $totalTabelSenteroptik ?></span></th>
                                                                        <th><span><?= $totalTabelSabuksafety ?></span></th>
                                                                        <th><span><?= $totalTabelImpactdrill ?></span></th>
                                                                        <th><span><?= $totalTabelElektroda ?></span></th>
                                                                        <th><span><?= $totalTabelTestergrounding ?></span></th>
                                                                        <th><span><?= $totalTabelMesinkerja ?></span></th>
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
            "paging": false, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": false, // Menghilangkan "Showing 1 to X of X entries"
            "searching": false, // Menghilangkan search bar
            "lengthChange": false // Menghilangkan dropdown "Show entries"
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