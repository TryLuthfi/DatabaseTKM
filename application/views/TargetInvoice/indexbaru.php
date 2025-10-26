<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;

?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">DASHBOARD TARGET INVOICE ( ALL PROJECT )</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="content">
        <div class="content-header">
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- /.col -->

                    <div class="col-12 col-sm-6 col-md-3">
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
                    <div class="content-header">
                        <div class="container-fluid">
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <h1 class="m-0 text-dark" style="text-align: center;">CHART STAGGING</h1>
                                </div><!-- /.col -->
                            </div><!-- /.row -->
                        </div><!-- /.container-fluid -->
                    </div>

                    
                    <div class="row">
                        <div class="col-6">
                            <div class="card-body">
                                <div class="chart" style="height: 300px;">
                                    <canvas id="invoice_chart_bar_pic"
                                        style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                                </div>
                                <div id="paginationControlsPIC" class="mt-3 d-flex justify-content-center"></div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card-body">
                                <div class="chart" style="height: 300px;">
                                    <canvas id="invoice_chart_bar_area"
                                        style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                                </div>
                                <div id="paginationControlsArea" class="mt-3 d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">REPORT PIC</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <section class="content">
            <div class="col-12 col-sm-12">
                <div class="card card-dark card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="pt-2 px-3">
                                <h3 class="card-title">DETAIL</h3>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-first-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-satu" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">SUMMARY TARGET</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-dua" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-tiga" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-empat" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-lima" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabspic-satu" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="tabel_targetpic_summary" class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>PIC</th>
                                            <th>TARGET INVOICE</th>
                                            <th>ACHIEVED INVOICE</th>
                                            <th>OUTSTANDING</th>
                                            <th>ACHIEVED ( % )</th>
                                            <th>OUTSTANDING ( % )</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getTargetAllPIC as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['pic_user'] ?></td>
                                                <td><?php if ($data['total_target'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                } ?></td>
                                                </td>
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
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th><span id="totalTargetInvoicePIC">0</span>
                                            </th>
                                            <th><span id="totalAchievInvoicePIC">0</span>
                                            </th>
                                            <th><span id="totalDeviasiInvoicePIC">0</span>
                                            </th>
                                            <th><span id="totalPersentaseTargetInvoicePIC">0</span>
                                            </th>
                                            <th><span id="totalPersentaseDeviasiTargetInvoicePIC">0</span>
                                            </th>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabspic-dua" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetpic_month"
                                                class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2"
                                                            style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            PIC</th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            NOVEMBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            DESEMBER
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                            <th style="text-align:center; background-color: blueviolet;">
                                                                %
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterPIC as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['pic_user'] ?></td>
                                                            <td><?= ($data['GRAND TOTAL TARGET'] != 0 ? number_format(floatval($data['GRAND TOTAL TARGET']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET OKTOBER'] > 0 && $data['TOTAL ACHIEVED OKTOBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED OKTOBER'] / $data['TOTAL TARGET OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET NOVEMBER'] > 0 && $data['TOTAL ACHIEVED NOVEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED NOVEMBER'] / $data['TOTAL TARGET NOVEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET DESEMBER'] > 0 && $data['TOTAL ACHIEVED DESEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED DESEMBER'] / $data['TOTAL TARGET DESEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['GRAND TOTAL ACHIEVED'] != 0 ? number_format(floatval($data['GRAND TOTAL ACHIEVED']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['GRAND TOTAL ACHIEVED'] > 0 && $data['GRAND TOTAL TARGET'] > 0) {
                                                                    $persentase = ($data['GRAND TOTAL ACHIEVED'] / $data['GRAND TOTAL TARGET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= number_format(($deviasi / $data['GRAND TOTAL TARGET'] * 100), 0, ",", ".") . '%' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 14; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KETIGA -->
                            <div class="tab-pane fade" id="custom-tabspic-tiga" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetpic_oktober"
                                                class="table table-bordered table-striped nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            PIC</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="10"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterPIC as $data):
                                                        $target = $data['TOTAL TARGET OKTOBER'];
                                                        $achiev = $data['TOTAL ACHIEVED OKTOBER'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['pic_user'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format(floatval($data['TW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format(floatval($data['RW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 13; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KEEMPAT -->
                            <div class="tab-pane fade" id="custom-tabspic-empat" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetpic_november" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    PIC
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET
                                                </th>
                                                <th colspan="8"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    NOVEMBER
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 4</th>
                                            </tr>

                                            <tr>
                                                <!-- Subheader TARGET & ACHIEVED -->
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <th
                                                        style="text-align:center; background-color: indianred; color: #ffffff;">
                                                        TARGET</th>
                                                    <th
                                                        style="text-align:center; background-color: darkseagreen; color: #000000;">
                                                        ACHIEVED</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterPIC as $data):
                                                $target = $data['TOTAL TARGET NOVEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED NOVEMBER'];
                                                $deviasi = $target - $achiev;

                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['pic_user'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <!-- NOVEMBER -->
                                                    <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format(floatval($data['TW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format(floatval($data['RW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabspic-lima" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetpic_desember" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    PIC</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET</th>
                                                <th colspan="4"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    DESEMBER</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterPIC as $data):
                                                $target = $data['TOTAL TARGET DESEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED DESEMBER'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['pic_user'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                        <!-- DESEMBER -->
                                                    <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format(floatval($data['TW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format(floatval($data['RW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">REPORT PROJECT / BOWHEER</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <section class="content">
            <div class="col-12 col-sm-12">
                <div class="card card-dark card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="pt-2 px-3">
                                <h3 class="card-title">DETAIL</h3>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-first-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-satu" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">SUMMARY TARGET</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-dua" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-tiga" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-empat" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-lima" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabsbowheer-satu" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="tabel_targetbowheer_summary" class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>PROJECT</th>
                                            <th>PIC</th>
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
                                        foreach ($getTargetCityFilterBowheer as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                                <td><?= $data['pic_user'] ?></td>
                                                <td><?php if ($data['total_target'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                } ?></td>
                                                </td>
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
                                                        class="btn btn-primary"><i class=" fas fa-eye"></i></a>
                                                </td>
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Total</th>
                                            <th><span id="totalTargetInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalAchievedInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalSisaInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalPersentaseTargetInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalPersentaseDeviasiTargetInvoiceBowheer">0</span>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-dua" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetbowheer_month"
                                                class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2"
                                                            style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            PROJECT</th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            NOVEMBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            DESEMBER
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                            <th style="text-align:center; background-color: blueviolet;">
                                                                %
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterBowheer as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['nama_bowheer'] ?></td>
                                                            <td><?= ($data['GRAND TOTAL TARGET'] != 0 ? number_format(floatval($data['GRAND TOTAL TARGET']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET OKTOBER'] > 0 && $data['TOTAL ACHIEVED OKTOBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED OKTOBER'] / $data['TOTAL TARGET OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET NOVEMBER'] > 0 && $data['TOTAL ACHIEVED NOVEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED NOVEMBER'] / $data['TOTAL TARGET NOVEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET DESEMBER'] > 0 && $data['TOTAL ACHIEVED DESEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED DESEMBER'] / $data['TOTAL TARGET DESEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['GRAND TOTAL ACHIEVED'] != 0 ? number_format(floatval($data['GRAND TOTAL ACHIEVED']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['GRAND TOTAL ACHIEVED'] > 0 && $data['GRAND TOTAL TARGET'] > 0) {
                                                                    $persentase = ($data['GRAND TOTAL ACHIEVED'] / $data['GRAND TOTAL TARGET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= number_format(($deviasi / $data['GRAND TOTAL TARGET'] * 100), 0, ",", ".") . '%' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 14; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KETIGA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-tiga" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetbowheer_oktober"
                                                class="table table-bordered table-striped nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            PROJECT</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="10"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterBowheer as $data):
                                                        $target = $data['TOTAL TARGET OKTOBER'];
                                                        $achiev = $data['TOTAL ACHIEVED OKTOBER'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['nama_bowheer'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format(floatval($data['TW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format(floatval($data['RW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 13; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KEEMPAT -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-empat" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_november" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    PROJECT
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET
                                                </th>
                                                <th colspan="8"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    NOVEMBER
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 4</th>
                                            </tr>

                                            <tr>
                                                <!-- Subheader TARGET & ACHIEVED -->
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <th
                                                        style="text-align:center; background-color: indianred; color: #ffffff;">
                                                        TARGET</th>
                                                    <th
                                                        style="text-align:center; background-color: darkseagreen; color: #000000;">
                                                        ACHIEVED</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterBowheer as $data):
                                                $target = $data['TOTAL TARGET NOVEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED NOVEMBER'];
                                                $deviasi = $target - $achiev;

                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['nama_bowheer'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <!-- NOVEMBER -->
                                                    <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format(floatval($data['TW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format(floatval($data['RW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-lima" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_desember" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    PROJECT</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET</th>
                                                <th colspan="4"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    DESEMBER</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterBowheer as $data):
                                                $target = $data['TOTAL TARGET DESEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED DESEMBER'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['nama_bowheer'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                        <!-- DESEMBER -->
                                                    <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format(floatval($data['TW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format(floatval($data['RW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">REPORT REGIONAL</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <section class="content">
            <div class="col-12 col-sm-12">
                <div class="card card-dark card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="pt-2 px-3">
                                <h3 class="card-title">DETAIL</h3>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-first-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-satu" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">SUMMARY TARGET</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-dua" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-tiga" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-empat" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-lima" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabsregional-satu" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="tabel_targetregional_summary" class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>REGIONAL</th>
                                            <th>TARGET INVOICE</th>
                                            <th>ACHIEVED INVOICE</th>
                                            <th>OUTSTANDING</th>
                                            <th>ACHIEVED ( % )</th>
                                            <th>OUTSTANDING ( % )</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getTargetAllRegional as $data):
                                            ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['regional_target'] ?></td>
                                                <td><?php if ($data['total_target'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                } ?></td>
                                                </td>
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
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th><span id="totalTargetInvoiceRegional">0</span>
                                            </th>
                                            <th><span id="totalAchievInvoiceRegional">0</span>
                                            </th>
                                            <th><span id="totalDeviasiInvoiceRegional">0</span>
                                            </th>
                                            <th><span id="totalPersentaseTargetInvoiceRegional">0</span>
                                            </th>
                                            <th><span id="totalPersentaseDeviasiTargetInvoiceRegional">0</span>
                                            </th>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabsregional-dua" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetregional_month"
                                                class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2"
                                                            style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            REGIONAL</th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            NOVEMBER
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            DESEMBER
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="2"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                            <th style="text-align:center; background-color: blueviolet;">
                                                                %
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterRegional as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['GRAND TOTAL TARGET'] != 0 ? number_format(floatval($data['GRAND TOTAL TARGET']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET OKTOBER'] > 0 && $data['TOTAL ACHIEVED OKTOBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED OKTOBER'] / $data['TOTAL TARGET OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET NOVEMBER'] > 0 && $data['TOTAL ACHIEVED NOVEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED NOVEMBER'] / $data['TOTAL TARGET NOVEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET DESEMBER'] > 0 && $data['TOTAL ACHIEVED DESEMBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED DESEMBER'] / $data['TOTAL TARGET DESEMBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['GRAND TOTAL ACHIEVED'] != 0 ? number_format(floatval($data['GRAND TOTAL ACHIEVED']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['GRAND TOTAL ACHIEVED'] > 0 && $data['GRAND TOTAL TARGET'] > 0) {
                                                                    $persentase = ($data['GRAND TOTAL ACHIEVED'] / $data['GRAND TOTAL TARGET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= number_format(($deviasi / $data['GRAND TOTAL TARGET'] * 100), 0, ",", ".") . '%' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 14; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KETIGA -->
                            <div class="tab-pane fade" id="custom-tabsregional-tiga" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetregional_oktober"
                                                class="table table-bordered table-striped nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            REGIONAL</th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="10"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterRegional as $data):
                                                        $target = $data['TOTAL TARGET OKTOBER'];
                                                        $achiev = $data['TOTAL ACHIEVED OKTOBER'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format(floatval($data['TW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format(floatval($data['RW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 13; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KEEMPAT -->
                            <div class="tab-pane fade" id="custom-tabsregional-empat" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetregional_november"
                                        class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    REGIONAL
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET
                                                </th>
                                                <th colspan="8"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    NOVEMBER
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 4</th>
                                            </tr>

                                            <tr>
                                                <!-- Subheader TARGET & ACHIEVED -->
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <th
                                                        style="text-align:center; background-color: indianred; color: #ffffff;">
                                                        TARGET</th>
                                                    <th
                                                        style="text-align:center; background-color: darkseagreen; color: #000000;">
                                                        ACHIEVED</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterRegional as $data):
                                                $target = $data['TOTAL TARGET NOVEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED NOVEMBER'];
                                                $deviasi = $target - $achiev;

                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['regional_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <!-- NOVEMBER -->
                                                    <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format(floatval($data['TW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format(floatval($data['RW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabsregional-lima" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetregional_desember"
                                        class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    REGIONAL</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET</th>
                                                <th colspan="4"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    DESEMBER</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterRegional as $data):
                                                $target = $data['TOTAL TARGET DESEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED DESEMBER'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['regional_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                        <!-- DESEMBER -->
                                                    <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format(floatval($data['TW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format(floatval($data['RW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">REPORT KOTA / AREA</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <section class="content">
            <div class="col-12 col-sm-12">
                <div class="card card-dark card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="pt-2 px-3">
                                <h3 class="card-title">DETAIL</h3>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-first-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-satu" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">SUMMARY TARGET</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-dua" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-tiga" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-empat" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-lima" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabscity-satu" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="tabel_targetcity_summary" class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>KOTA</th>
                                            <th>TARGET INVOICE</th>
                                            <th>ACHIEVED INVOICE</th>
                                            <th>OUTSTANDING</th>
                                            <th>DETAIL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getTargetBowheerFilterCity as $data): ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['area_target'] ?></td>
                                                <td><?php if ($data['total_target'] == "0") {
                                                    echo "-";
                                                } else {
                                                    echo number_format(floatval($data['total_target']), 0, ",", ".");
                                                } ?></td>
                                                </td>
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
                                                <td>
                                                    <a href="<?php echo site_url('TargetInvoice/detailKota/' . $data['area_target']); ?>"
                                                        class="btn btn-primary"><i class=" fas fa-eye"></i></a>
                                                </td>
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th><span id="totalTargetInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalAchievedInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalSisaInvoiceCity">0</span>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabscity-dua" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetcity_month"
                                                class="table table-bordered table-striped nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2"
                                                            style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            KOTA</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">
                                                            NOVEMBER
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">
                                                            DESEMBER
                                                        </th>
                                                        <th rowspan="2"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= ($data['GRAND TOTAL TARGET'] != 0 ? number_format(floatval($data['GRAND TOTAL TARGET']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['GRAND TOTAL ACHIEVED'] != 0 ? number_format(floatval($data['GRAND TOTAL ACHIEVED']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 9; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KETIGA -->
                            <div class="tab-pane fade" id="custom-tabscity-tiga" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetcity_oktober"
                                                class="table table-bordered table-striped nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="3"
                                                            style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            No</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            KOTA</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="10"
                                                            style="text-align:center; background-color: aqua;">
                                                            OKTOBER
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="2"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <th style="text-align:center; background-color: indianred;">
                                                                TARGET</th>
                                                            <th style="text-align:center; background-color: darkseagreen;">
                                                                ACHIEVED
                                                            </th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['TOTAL TARGET OKTOBER'];
                                                        $achiev = $data['TOTAL ACHIEVED OKTOBER'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format(floatval($data['TW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format(floatval($data['RW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 13; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV KEEMPAT -->
                            <div class="tab-pane fade" id="custom-tabscity-empat" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetcity_november" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    KOTA
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET
                                                </th>
                                                <th colspan="8"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    NOVEMBER
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 4</th>
                                            </tr>

                                            <tr>
                                                <!-- Subheader TARGET & ACHIEVED -->
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <th
                                                        style="text-align:center; background-color: indianred; color: #ffffff;">
                                                        TARGET</th>
                                                    <th
                                                        style="text-align:center; background-color: darkseagreen; color: #000000;">
                                                        ACHIEVED</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterCity as $data):
                                                $target = $data['TOTAL TARGET NOVEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED NOVEMBER'];
                                                $deviasi = $target - $achiev;

                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['area_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <!-- NOVEMBER -->
                                                    <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format(floatval($data['TW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format(floatval($data['RW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabscity-lima" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetcity_desember" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th rowspan="3"
                                                    style="width: 60px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    No</th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    KOTA</th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET</th>
                                                <th colspan="4"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    DESEMBER</th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterCity as $data):
                                                $target = $data['TOTAL TARGET DESEMBER'];
                                                $achiev = $data['TOTAL ACHIEVED DESEMBER'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['area_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                        <!-- DESEMBER -->
                                                    <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format(floatval($data['TW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format(floatval($data['RW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>

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
            $('#tabel_targetbowheer_summary').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetbowheer_month').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetbowheer_oktober').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetbowheer_november').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetbowheer_desember').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetcity_summary').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetcity_month').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetcity_oktober').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetcity_november').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetcity_desember').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetpic_summary').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetpic_month').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetpic_oktober').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetpic_november').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetpic_desember').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });

            $('#tabel_targetregional_summary').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetregional_month').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetregional_oktober').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetregional_november').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
            $('#tabel_targetregional_desember').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#tabel_targetbowheer_summary').DataTable({
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
                let totalAchievedInvoiceBowheer = 0;
                let totalSisaInvoiceBowheer = 0;
                let totalPersentaseTargetInvoiceBowheer = 0;
                let totalPersentaseDeviasiTargetInvoiceBowheer = 0;

                data.each(function (row) {

                    totalTargetInvoiceBowheer += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalAchievedInvoiceBowheer += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalSisaInvoiceBowheer += parseFloat(row[5].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceBowheer').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalAchievedInvoiceBowheer').innerText = totalAchievedInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalSisaInvoiceBowheer').innerText = totalSisaInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceBowheer').innerText = (totalAchievedInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(2) + " %";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceBowheer').innerText = (totalSisaInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(2) + " %";

                document.getElementById('dashboardTargetInvoice').innerText = "RP. " + totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardAchievInvoice').innerText = "RP. " + totalAchievedInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardSisaInvoice').innerText = "RP. " + totalSisaInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardPersentaseInvoice').innerText = (totalAchievedInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(2) + " %";
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

            const table = $('#tabel_targetbowheer_month').DataTable({
                footerCallback: function () {
                    updateTotalDanPersentase();
                },
                columnDefs: [
                    { orderable: false, targets: 0 }
                ],
                order: [[1, 'asc']]
            });

            // Nomor urut otomatis
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi total + persentase
            function updateTotalDanPersentase() {
                const data = table.rows({ search: 'applied' }).data();

                // jumlah kolom numerik (Total target sampai deviasi)
                let totalKolom = Array(12).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 14; i++) { // mulai dari kolom ke-2 (Total Target)
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik ribuan
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                // Tulis total ke baris footer
                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

                // === Hitung persentase ===
                // urutan: [1]=Okt Target, [2]=Okt Achieved, [4]=Nov Target, [5]=Nov Achieved, [7]=Des Target, [8]=Des Achieved
                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenOktober = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenNovember = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenDesember = hitungPersen(totalKolom[7], totalKolom[8]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[10]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[11]);

                $(table.column(5).footer()).text(persenOktober.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenNovember.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenDesember.toFixed(0) + ' %');
                $(table.column(14).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(15).footer()).text(persenDeviasi.toFixed(0) + ' %');
            }

            table.on('draw', function () {
                updateTotalDanPersentase();
            });

            updateTotalDanPersentase();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetbowheer_oktober').DataTable({
                footerCallback: function () {
                    updateTotalDanPersentase();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotalDanPersentase() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(13).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 15; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;   // ada hasil tapi target kosong
                    if (target === 0 && achieved === 0) return 0;   // dua-duanya kosong
                    return (achieved / target) * 100;               // normal
                }

                const persenOktoW1 = hitungPersen(totalKolom[3 - 2], totalKolom[4 - 2]);
                const persenOktoW2 = hitungPersen(totalKolom[5 - 2], totalKolom[6 - 2]);
                const persenOktoW3 = hitungPersen(totalKolom[7 - 2], totalKolom[8 - 2]);
                const persenOktoW4 = hitungPersen(totalKolom[9 - 2], totalKolom[10 - 2]);
                const persenOktoW5 = hitungPersen(totalKolom[11 - 2], totalKolom[12 - 2]);
                const persenGrand = hitungPersen(totalKolom[2 - 2], totalKolom[13 - 2]);
                const persenDeviasi = hitungPersen(totalKolom[2 - 2], totalKolom[14 - 2]);

                let footerRows = $('#tabel_targetbowheer_filter_city1 tfoot tr');
                let barisPersen = $(footerRows[1]).find('th');

                barisPersen.eq(1).text(persenOktoW1.toFixed(2) + '%');
                barisPersen.eq(2).text(persenOktoW2.toFixed(2) + '%');
                barisPersen.eq(3).text(persenOktoW3.toFixed(2) + '%');
                barisPersen.eq(4).text(persenOktoW4.toFixed(2) + '%');
                barisPersen.eq(5).text(persenOktoW5.toFixed(2) + '%');
                barisPersen.eq(6).text(persenGrand.toFixed(2) + '%');
                barisPersen.eq(7).text(persenDeviasi.toFixed(2) + '%');

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotalDanPersentase();
            });

            // Hitung total pertama kali
            updateTotalDanPersentase();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetbowheer_november').DataTable({
                footerCallback: function () {
                    updateTotalDanPersentase();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotalDanPersentase() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(11).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 13; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;   // ada hasil tapi target kosong
                    if (target === 0 && achieved === 0) return 0;   // dua-duanya kosong
                    return (achieved / target) * 100;               // normal
                }

                const persenSeptW1 = hitungPersen(totalKolom[3 - 2], totalKolom[4 - 2]);
                const persenSeptW2 = hitungPersen(totalKolom[5 - 2], totalKolom[6 - 2]);
                const persenSeptW3 = hitungPersen(totalKolom[7 - 2], totalKolom[8 - 2]);
                const persenSeptW4 = hitungPersen(totalKolom[9 - 2], totalKolom[10 - 2]);
                const persenGrand = hitungPersen(totalKolom[2 - 2], totalKolom[11 - 2]);
                const persenDeviasi = hitungPersen(totalKolom[2 - 2], totalKolom[12 - 2]);

                let footerRows = $('#tabel_targetbowheer_filter_city2 tfoot tr');
                let barisPersen = $(footerRows[1]).find('th');

                barisPersen.eq(1).text(persenSeptW1.toFixed(2) + '%');
                barisPersen.eq(2).text(persenSeptW2.toFixed(2) + '%');
                barisPersen.eq(3).text(persenSeptW3.toFixed(2) + '%');
                barisPersen.eq(4).text(persenSeptW4.toFixed(2) + '%');
                barisPersen.eq(6).text(persenGrand.toFixed(2) + '%');
                barisPersen.eq(7).text(persenDeviasi.toFixed(2) + '%');

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotalDanPersentase();
            });

            // Hitung total pertama kali
            updateTotalDanPersentase();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetbowheer_desember').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(7).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 9; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#tabel_targetcity_summary').DataTable({
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
                let totalAchievedInvoiceBowheer = 0;
                let totalSisaInvoiceBowheer = 0;

                data.each(function (row) {

                    totalTargetInvoiceBowheer += parseFloat(row[2].replace(/\./g, '')) || 0;
                    totalAchievedInvoiceBowheer += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalSisaInvoiceBowheer += parseFloat(row[4].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceCity').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalAchievedInvoiceCity').innerText = totalAchievedInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalSisaInvoiceCity').innerText = totalSisaInvoiceBowheer.toLocaleString('id-ID');
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

            const table = $('#tabel_targetcity_month').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(9).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 11; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetcity_oktober').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(13).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 15; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetcity_november').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(11).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 13; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetcity_desember').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(7).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 9; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#tabel_targetpic_summary').DataTable({
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
                let totalTargetInvoicePIC = 0;
                let totalAchievInvoicePIC = 0;
                let totalDeviasiInvoicePIC = 0;
                let totalPersentaseTargetInvoicePIC = 0;
                let totalPersentaseDeviasiTargetInvoicePIC = 0;

                data.each(function (row) {

                    totalTargetInvoicePIC += parseFloat(row[2].replace(/\./g, '')) || 0;
                    totalAchievInvoicePIC += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalDeviasiInvoicePIC += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalPersentaseTargetInvoicePIC += parseFloat(row[5].replace(/\./g, '')) || 0;
                    totalPersentaseDeviasiTargetInvoicePIC += parseFloat(row[6].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoicePIC').innerText = totalTargetInvoicePIC.toLocaleString('id-ID');
                document.getElementById('totalAchievInvoicePIC').innerText = totalAchievInvoicePIC.toLocaleString('id-ID');
                document.getElementById('totalDeviasiInvoicePIC').innerText = totalDeviasiInvoicePIC.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoicePIC').innerText = (totalAchievInvoicePIC / totalTargetInvoicePIC * 100).toFixed(2) + " %";
                document.getElementById('totalPersentaseDeviasiTargetInvoicePIC').innerText = (totalDeviasiInvoicePIC / totalTargetInvoicePIC * 100).toFixed(2) + " %";
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

            const table = $('#tabel_targetpic_month').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(14).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 16; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetpic_oktober').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(13).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 15; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetpic_november').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(11).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 13; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetpic_desember').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(7).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 9; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            const table = $('#tabel_targetregional_summary').DataTable({
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
                let totalTargetInvoiceRegional = 0;
                let totalAchievInvoiceRegional = 0;
                let totalDeviasiInvoiceRegional = 0;
                let totalPersentaseTargetInvoicRegionalC = 0;
                let totalPersentaseDeviasiTargetInvoiceRegional = 0;

                data.each(function (row) {

                    totalTargetInvoiceRegional += parseFloat(row[2].replace(/\./g, '')) || 0;
                    totalAchievInvoiceRegional += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalDeviasiInvoiceRegional += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalPersentaseTargetInvoiceRegional += parseFloat(row[5].replace(/\./g, '')) || 0;
                    totalPersentaseDeviasiTargetInvoiceRegional += parseFloat(row[6].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceRegional').innerText = totalTargetInvoiceRegional.toLocaleString('id-ID');
                document.getElementById('totalAchievInvoiceRegional').innerText = totalAchievInvoiceRegional.toLocaleString('id-ID');
                document.getElementById('totalDeviasiInvoiceRegional').innerText = totalDeviasiInvoiceRegional.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceRegional').innerText = (totalAchievInvoiceRegional / totalTargetInvoiceRegional * 100).toFixed(2) + " %";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceRegional').innerText = (totalDeviasiInvoiceRegional / totalTargetInvoiceRegional * 100).toFixed(2) + " %";
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

            const table = $('#tabel_targetregional_month').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(14).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 16; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetregional_oktober').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(13).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 15; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetregional_november').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(11).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 13; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetregional_desember').DataTable({
                footerCallback: function () {
                    updateTotal();
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Kolom No tidak bisa di-sort manual
                ],
                order: [[1, 'asc']]
            });

            // Tambah nomor otomatis di kolom pertama
            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // Fungsi utama untuk hitung total otomatis
            function updateTotal() {
                const data = table.rows({ search: 'applied' }).data();

                let totalKolom = Array(7).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 9; i++) { // misal kolom angka mulai dari index ke-2
                        // Hapus titik dan koma dulu
                        let value = row[i]
                            .toString()
                            .replace(/\./g, '')   // hapus titik
                            .replace(/,/g, '')    // hapus koma
                            .replace(/[^0-9-]/g, ''); // hapus selain angka
                        totalKolom[i - 2] += parseFloat(value) || 0;
                    }
                });

                for (let i = 0; i < totalKolom.length; i++) {
                    let totalFormatted = totalKolom[i].toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    $(table.column(i + 2).footer()).text(totalFormatted);
                }

            }

            // Jalankan ulang total setiap kali tabel berubah
            table.on('draw', function () {
                updateTotal();
            });

            // Hitung total pertama kali
            updateTotal();
        });

        $(function () {


            const dataBarInvoicePIC = <?php echo json_encode($getTargetAllPIC); ?>;
            const invoicePICBar = dataBarInvoicePIC.map(item => item.pic_user);
            const totalTargetBar = dataBarInvoicePIC.map(item => item.total_target);
            const achievTargetBar = dataBarInvoicePIC.map(item => item.total_achiev);
            const deviasiTargetBar = dataBarInvoicePIC.map(item => item.deviasi);

            const originalData = {
                labels: invoicePICBar, // Semua label asli
                datasets: [
                    {
                        label: 'TARGET',
                        backgroundColor: '#007bff',
                        borderColor: '#007bff',
                        data: totalTargetBar
                    },
                    {
                        label: 'ACHIEVED',
                        backgroundColor: '#d2d6de',
                        borderColor: '#d2d6de',
                        data: achievTargetBar
                    },
                    {
                        label: 'OUTSTANDING',
                        backgroundColor: '#FD7E14',
                        borderColor: '#FD7E14',
                        data: deviasiTargetBar
                    }
                ]
            };

            const itemsPerPage = 10; // Tampilkan 5 data per halaman
            let currentPagePIC = 1; // Halaman aktif
            let filterStatePIC = []; // Menyimpan state filter legend

            function getPagedDataPIC(page) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                // Filter data berdasarkan halaman
                const pagedLabels = originalData.labels.slice(startIndex, endIndex);
                const pagedDatasets = originalData.datasets.map((dataset, index) => ({
                    ...dataset,
                    data: dataset.data.slice(startIndex, endIndex)
                }));

                return { labels: pagedLabels, datasets: pagedDatasets };
            }

            const barChartCanvas = $('#invoice_chart_bar_pic').get(0).getContext('2d');
            let barChart; // Variabel untuk menyimpan instance Chart.js

            function renderChartPIC(page) {
                const pagedData = getPagedDataPIC(page);

                if (barChart) {
                    barChart.destroy(); // Hancurkan chart lama sebelum membuat baru
                }

                barChart = new Chart(barChartCanvas, {
                    type: 'bar',
                    data: pagedData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        datasetFill: false,
                        plugins: {
                            legend: {
                                onClick: function (e, legendItem) {
                                    const datasetIndex = legendItem.datasetIndex;
                                    const chart = legendItem.chart;

                                    // Toggle visibility dataset
                                    const meta = chart.getDatasetMeta(datasetIndex);
                                    meta.hidden = meta.hidden === null ? !chart.data.datasets[datasetIndex].hidden : null;

                                    // Simpan status filter ke filterStatePIC
                                    filterStatePIC[datasetIndex] = meta.hidden;

                                    chart.update(); // Update chart setelah perubahan
                                }
                            }
                        }
                    }
                });

                // Terapkan filter yang disimpan
                applyFilterStatePICPIC(barChart);
            }

            function applyFilterStatePICPIC(chart) {
                if (filterStatePIC.length > 0) {
                    chart.data.datasets.forEach((dataset, index) => {
                        const meta = chart.getDatasetMeta(index);
                        meta.hidden = filterStatePIC[index] || null;
                    });
                    chart.update();
                }
            }

            function createPaginationControlsPICPIC(totalPages) {
                const paginationContainer = $('#paginationControlsPIC');
                paginationContainer.empty(); // Hapus tombol lama

                // Tombol Previous
                const prevButton = $(`<button class="btn btn-sm btn-secondary m-1">Previous</button>`);
                prevButton.prop('disabled', currentPagePIC === 1);
                prevButton.on('click', function () {
                    if (currentPagePIC > 1) {
                        currentPagePIC--;
                        renderChartPIC(currentPagePIC);
                        highlightActivePagePIC(totalPages);
                    }
                });
                paginationContainer.append(prevButton);

                // Tombol untuk setiap halaman
                for (let i = 1; i <= totalPages; i++) {
                    const button = $(`<button class="btn btn-sm btn-primary m-1">${i}</button>`);
                    if (i === currentPagePIC) {
                        button.addClass('active'); // Tambahkan class aktif pada halaman saat ini
                    }
                    button.on('click', function () {
                        currentPagePIC = i;
                        renderChartPIC(currentPagePIC); // Render chart untuk halaman baru
                        highlightActivePagePIC(totalPages);
                    });
                    paginationContainer.append(button);
                }

                // Tombol Next
                const nextButton = $(`<button class="btn btn-sm btn-secondary m-1">Next</button>`);
                nextButton.prop('disabled', currentPagePIC === totalPages);
                nextButton.on('click', function () {
                    if (currentPagePIC < totalPages) {
                        currentPagePIC++;
                        renderChartPIC(currentPagePIC);
                        highlightActivePagePIC(totalPages);
                    }
                });
                paginationContainer.append(nextButton);
            }

            function highlightActivePagePIC(totalPages) {
                const paginationContainer = $('#paginationControlsPIC');
                paginationContainer.find('button').removeClass('active'); // Hapus highlight dari semua tombol

                // Highlight tombol aktif
                paginationContainer
                    .find('button')
                    .filter(function () {
                        return $(this).text() == currentPagePIC || $(this).text() == "Next" || $(this).text() == "Previous";
                    })
                    .addClass('active');

                // Perbarui tombol Previous dan Next
                paginationContainer.find('button:contains("Previous")').prop('disabled', currentPagePIC === 1);
                paginationContainer.find('button:contains("Next")').prop('disabled', currentPagePIC === totalPages);
            }

            const totalPages = Math.ceil(originalData.labels.length / itemsPerPage);

            // Inisialisasi
            renderChartPIC(currentPagePIC);
            createPaginationControlsPICPIC(totalPages);


            //BAR BIASA TANPA SELECTED

            'use strict'

        })

        $(function () {


            const dataBarInvoiceArea = <?php echo json_encode($getTargetAllCity); ?>;
            const invoiceAreaBar = dataBarInvoiceArea.map(item => item.area_target);
            const totalTargetAreaBar = dataBarInvoiceArea.map(item => item.total_target);
            const achievTargetAreaBar = dataBarInvoiceArea.map(item => item.total_achiev);
            const deviasiTargetAreaBar = dataBarInvoiceArea.map(item => item.deviasi);

            const originalData = {
                labels: invoiceAreaBar, // Semua label asli
                datasets: [
                    {
                        label: 'TARGET',
                        backgroundColor: '#007bff',
                        borderColor: '#007bff',
                        data: totalTargetAreaBar
                    },
                    {
                        label: 'ACHIEVED',
                        backgroundColor: '#d2d6de',
                        borderColor: '#d2d6de',
                        data: achievTargetAreaBar
                    },
                    {
                        label: 'OUTSTANDING',
                        backgroundColor: '#FD7E14',
                        borderColor: '#FD7E14',
                        data: deviasiTargetAreaBar
                    }
                ]
            };

            const itemsPerPage = 5; // Tampilkan 5 data per halaman
            let currentPageArea = 1; // Halaman aktif
            let filterStateArea = []; // Menyimpan state filter legend

            function getPagedDataArea(page) {
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                // Filter data berdasarkan halaman
                const pagedLabels = originalData.labels.slice(startIndex, endIndex);
                const pagedDatasets = originalData.datasets.map((dataset, index) => ({
                    ...dataset,
                    data: dataset.data.slice(startIndex, endIndex)
                }));

                return { labels: pagedLabels, datasets: pagedDatasets };
            }

            const barChartCanvas = $('#invoice_chart_bar_area').get(0).getContext('2d');
            let barChart; // Variabel untuk menyimpan instance Chart.js

            function renderChartArea(page) {
                const pagedData = getPagedDataArea(page);

                if (barChart) {
                    barChart.destroy(); // Hancurkan chart lama sebelum membuat baru
                }

                barChart = new Chart(barChartCanvas, {
                    type: 'bar',
                    data: pagedData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        datasetFill: false,
                        plugins: {
                            legend: {
                                onClick: function (e, legendItem) {
                                    const datasetIndex = legendItem.datasetIndex;
                                    const chart = legendItem.chart;

                                    // Toggle visibility dataset
                                    const meta = chart.getDatasetMeta(datasetIndex);
                                    meta.hidden = meta.hidden === null ? !chart.data.datasets[datasetIndex].hidden : null;

                                    // Simpan status filter ke filterStateArea
                                    filterStateArea[datasetIndex] = meta.hidden;

                                    chart.update(); // Update chart setelah perubahan
                                }
                            }
                        }
                    }
                });

                // Terapkan filter yang disimpan
                applyFilterStateAreaArea(barChart);
            }

            function applyFilterStateAreaArea(chart) {
                if (filterStateArea.length > 0) {
                    chart.data.datasets.forEach((dataset, index) => {
                        const meta = chart.getDatasetMeta(index);
                        meta.hidden = filterStateArea[index] || null;
                    });
                    chart.update();
                }
            }

            function createPaginationControlsAreaArea(totalPages) {
                const paginationContainer = $('#paginationControlsArea');
                paginationContainer.empty(); // Hapus tombol lama

                // Tombol Previous
                const prevButton = $(`<button class="btn btn-sm btn-secondary m-1">Previous</button>`);
                prevButton.prop('disabled', currentPageArea === 1);
                prevButton.on('click', function () {
                    if (currentPageArea > 1) {
                        currentPageArea--;
                        renderChartArea(currentPageArea);
                        highlightActivePageArea(totalPages);
                    }
                });
                paginationContainer.append(prevButton);

                // Tombol untuk setiap halaman
                for (let i = 1; i <= totalPages; i++) {
                    const button = $(`<button class="btn btn-sm btn-primary m-1">${i}</button>`);
                    if (i === currentPageArea) {
                        button.addClass('active'); // Tambahkan class aktif pada halaman saat ini
                    }
                    button.on('click', function () {
                        currentPageArea = i;
                        renderChartArea(currentPageArea); // Render chart untuk halaman baru
                        highlightActivePageArea(totalPages);
                    });
                    paginationContainer.append(button);
                }

                // Tombol Next
                const nextButton = $(`<button class="btn btn-sm btn-secondary m-1">Next</button>`);
                nextButton.prop('disabled', currentPageArea === totalPages);
                nextButton.on('click', function () {
                    if (currentPageArea < totalPages) {
                        currentPageArea++;
                        renderChartArea(currentPageArea);
                        highlightActivePageArea(totalPages);
                    }
                });
                paginationContainer.append(nextButton);
            }

            function highlightActivePageArea(totalPages) {
                const paginationContainer = $('#paginationControlsArea');
                paginationContainer.find('button').removeClass('active'); // Hapus highlight dari semua tombol

                // Highlight tombol aktif
                paginationContainer
                    .find('button')
                    .filter(function () {
                        return $(this).text() == currentPageArea || $(this).text() == "Next" || $(this).text() == "Previous";
                    })
                    .addClass('active');

                // Perbarui tombol Previous dan Next
                paginationContainer.find('button:contains("Previous")').prop('disabled', currentPageArea === 1);
                paginationContainer.find('button:contains("Next")').prop('disabled', currentPageArea === totalPages);
            }

            const totalPages = Math.ceil(originalData.labels.length / itemsPerPage);

            // Inisialisasi
            renderChartArea(currentPageArea);
            createPaginationControlsAreaArea(totalPages);


            //BAR BIASA TANPA SELECTED

            'use strict'

        })

        $(document).ready(function () {
            $('.card[data-card-widget="collapse"]').addClass('card-tools');
        });

        document.addEventListener("DOMContentLoaded", function () {
            let cardfilter = document.getElementById("cardfiltercollapse").closest(".card");
            let cardpo = document.getElementById("cardpocollapse").closest(".card");
            let cardstagging = document.getElementById("cardstaggingcollapse").closest(".card");
            let cardsummary = document.getElementById("cardsummarycollapse").closest(".card");
            // cardfilter.classList.add("collapsed-card");
            cardpo.classList.add("collapsed-card");
            cardstagging.classList.add("collapsed-card");
            cardsummary.classList.add("collapsed-card");
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