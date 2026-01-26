<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;

?>

<meta name="format-detection" content="telephone=no">

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
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">TARGET INVOICE</span>
                                <span class="info-box-number" id="dashboardTargetInvoice">
                                </span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-green">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">ACHIEVED INVOICE</span>
                                <h4 class="info-box-number" style="color: #33cc33;" id="dashboardAchievInvoice">
                                    Rp. 0
                                </h4>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-red">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">SISA INVOICE</span>
                                <h4 class="info-box-number" style="color: #ce0808ff;" id="dashboardSisaInvoice">
                                    Rp. 0
                                </h4>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">PERSENTASE INVOICE</span>
                                <h4 class="info-box-number" id="dashboardPersentaseInvoice">
                                    Rp. 0
                                </h4>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        </a>
                        <!-- /.info-box -->
                    </div>
                </div>

                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12 ">
                                <h3 class="m-0 text-dark" style="text-align: center;">DAILY TARGET TO ACHIEV INVOICE
                                </h3>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>

                <div class="row">
                    <!-- /.col -->

                    <div class="col-12 col-sm-6 col-md-3" style="visibility: hidden;">
                        <div class="info-box mb-3">
                        </div>
                        <!-- /.info-box -->
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-yellow">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">DAILY TARGET INVOICE</span>
                                <h4 class="info-box-number" style="color: #33cc33;" id="dashboardTargetInvoiceHarian">
                                    Rp. 0
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 glow-yellow">
                            <span class="info-box-icon bg-grey elevation-1"><i
                                    class="fas fa-money-check-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">PERSENTASE TARGET INVOICE</span>
                                <h4 class="info-box-number" style="color: #ce0808ff;"
                                    id="dashboardPersentaseTargetHarian">
                                    Rp. 0
                                </h4>
                            </div>
                        </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3" style="visibility: hidden;">
                        <div class="info-box mb-3">
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>

                <div class="container-fluid py-4 bg-light rounded shadow-sm">

                    <div class="row g-4">
                        <!-- Chart by PIC -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-lg rounded-4 h-100">
                                <div class="card-header bg-primary text-white text-center rounded-top-4">
                                    <h5 class="mb-0 fw-semibold"><i class="fas fa-user-tie me-2"></i>Chart Berdasarkan
                                        PIC</h5>
                                </div>
                                <div class="card-body bg-white rounded-bottom-4">
                                    <canvas id="invoice_chart_bar_pic"
                                        style="min-height: 250px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                                </div>
                                <div id="paginationControlsPIC" class="my-3 d-flex justify-content-center"></div>
                            </div>
                        </div>

                        <!-- Chart by AREA -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-lg rounded-4 h-100">
                                <div class="card-header bg-success text-white text-center rounded-top-4">
                                    <h5 class="mb-0 fw-semibold"><i class="fas fa-map-marked-alt me-2"></i>Chart
                                        Berdasarkan Project</h5>
                                </div>
                                <div class="card-body bg-white rounded-bottom-4">
                                    <canvas id="invoice_chart_bar_area"
                                        style="min-height: 250px; height: 400px; max-height: 400px; width: 100%;"></canvas>
                                </div>
                                <div id="paginationControlsArea" class="my-3 d-flex justify-content-center"></div>
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
                                    href="#custom-tabspic-month2025" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2025</a>
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

                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-month2026" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2026</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-januari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">JANUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-februari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">FEBRUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabspic-maret" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MARET</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active table-responsive" id="custom-tabspic-satu" role="tabpanel"
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
                                                <td><?php
                                                if ($data['persen_achiev'] > 0 && $data['persen_achiev'] > 0) {
                                                    echo number_format($data['persen_achiev'], 1, ",", ".") . '%';
                                                } else {
                                                    echo '0%';
                                                }
                                                ?>
                                                </td>
                                                <td><?php
                                                if ($data['persen_deviasi'] > 0 && $data['persen_deviasi'] > 0) {
                                                    echo number_format($data['persen_deviasi'], 0, ",", ".") . '%';
                                                } else {
                                                    echo '0%';
                                                }
                                                ?>
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
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV MONTHLY 2025 -->
                            <div class="tab-pane fade" id="custom-tabspic-month2025" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetpic_month2025"
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
                                                    <tr>
                                                        <th colspan="3">Deviasi</th>
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th colspan="3" style="text-align: end; font-">0</th>
                                                        <?php endfor; ?>
                                                        <th colspan="4"></th>
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
                                        <div class="card-body p-0 table-responsive">
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
                                                        <th colspan="15"
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
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
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
                                                            <th style="text-align:center; background-color: blueviolet;">
                                                                %
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
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 OKTOBER'] > 0 && $data['RW1 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW1 OKTOBER'] / $data['TW1 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 OKTOBER'] > 0 && $data['RW2 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW2 OKTOBER'] / $data['TW2 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 OKTOBER'] > 0 && $data['RW3 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW3 OKTOBER'] / $data['TW3 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 OKTOBER'] > 0 && $data['RW4 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW4 OKTOBER'] / $data['TW4 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 OKTOBER'] > 0 && $data['RW5 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW5 OKTOBER'] / $data['TW5 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED OKTOBER'] > 0 && $data['TOTAL TARGET OKTOBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED OKTOBER'] / $data['TOTAL TARGET OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= number_format(($deviasi / $data['TOTAL TARGET OKTOBER'] * 100), 0, ",", ".") . '%' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
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
                                                <th colspan="12"
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
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    ACHIEVED ( % )
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI ( % )
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="3"
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
                                                    <th style="text-align:center; background-color: blueviolet;">
                                                        %
                                                    </th>
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
                                                    <td>
                                                        <?php
                                                        if ($data['TW1 NOVEMBER'] > 0 && $data['RW1 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW1 NOVEMBER'] / $data['TW1 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW2 NOVEMBER'] > 0 && $data['RW2 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW2 NOVEMBER'] / $data['TW2 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW3 NOVEMBER'] > 0 && $data['RW3 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW3 NOVEMBER'] / $data['TW3 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW4 NOVEMBER'] > 0 && $data['RW4 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW4 NOVEMBER'] / $data['TW4 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TOTAL ACHIEVED NOVEMBER'] > 0 && $data['TOTAL TARGET NOVEMBER'] > 0) {
                                                            $persentase = ($data['TOTAL ACHIEVED NOVEMBER'] / $data['TOTAL TARGET NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= number_format(($deviasi / $data['TOTAL TARGET NOVEMBER'] * 100), 0, ",", ".") . '%' ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <?php for ($i = 0; $i < 17; $i++): ?>
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
                                                <th colspan="6"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    DESEMBER</th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL ACHIEVED
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:cente r; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    ACHIEVED ( % )
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI ( % )
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="3"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
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
                                                    <td>
                                                        <?php
                                                        if ($data['TW1 DESEMBER'] > 0 && $data['RW1 DESEMBER'] > 0) {
                                                            $persentase = ($data['RW1 DESEMBER'] / $data['TW1 DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW2 DESEMBER'] > 0 && $data['RW2 DESEMBER'] > 0) {
                                                            $persentase = ($data['RW2 DESEMBER'] / $data['TW2 DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TOTAL ACHIEVED DESEMBER'] > 0 && $data['TOTAL TARGET DESEMBER'] > 0) {
                                                            $persentase = ($data['TOTAL ACHIEVED DESEMBER'] / $data['TOTAL TARGET DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($data['TOTAL TARGET DESEMBER']) && $data['TOTAL TARGET DESEMBER'] != 0) {
                                                            echo number_format(($deviasi / $data['TOTAL TARGET DESEMBER'] * 100), 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
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
                            <!-- TAB NAV MONTHLY 2025 -->
                            <div class="tab-pane fade" id="custom-tabspic-month2026" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetpic_month2026"
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
                                                            JANUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            FEBRUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            MARET
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
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET JANUARI'] > 0 && $data['TOTAL ACHIEVED JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET FEBRUARI'] > 0 && $data['TOTAL ACHIEVED FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET MARET'] > 0 && $data['TOTAL ACHIEVED MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
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
                                                    <tr>
                                                        <th colspan="3">Deviasi</th>
                                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                                            <th colspan="3" style="text-align: end; font-">0</th>
                                                        <?php endfor; ?>
                                                        <th colspan="4"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV JANUARI -->
                            <div class="tab-pane fade" id="custom-tabspic-januari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive">
                                            <table id="tabel_targetpic_januari"
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
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: aqua;">
                                                            JANUARI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- JANUARI -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- JANUARI -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                        $target = $data['TOTAL TARGET JANUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED JANUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['pic_user'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- JANUARI -->
                                                            <td><?= ($data['TW1 JANUARI'] != 0 ? number_format(floatval($data['TW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 JANUARI'] != 0 ? number_format(floatval($data['RW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 JANUARI'] > 0 && $data['RW1 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW1 JANUARI'] / $data['TW1 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 JANUARI'] != 0 ? number_format(floatval($data['TW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 JANUARI'] != 0 ? number_format(floatval($data['RW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 JANUARI'] > 0 && $data['RW2 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW2 JANUARI'] / $data['TW2 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 JANUARI'] != 0 ? number_format(floatval($data['TW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 JANUARI'] != 0 ? number_format(floatval($data['RW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 JANUARI'] > 0 && $data['RW3 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW3 JANUARI'] / $data['TW3 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 JANUARI'] != 0 ? number_format(floatval($data['TW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 JANUARI'] != 0 ? number_format(floatval($data['RW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 JANUARI'] > 0 && $data['RW4 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW4 JANUARI'] / $data['TW4 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 JANUARI'] != 0 ? number_format(floatval($data['TW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 JANUARI'] != 0 ? number_format(floatval($data['RW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 JANUARI'] > 0 && $data['RW5 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW5 JANUARI'] / $data['TW5 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED JANUARI'] > 0 && $data['TOTAL TARGET JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED JANUARI'] > 0 && $data['TOTAL TARGET JANUARI'] > 0) {
                                                                    number_format(($deviasi / $data['TOTAL TARGET JANUARI'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV FEBRUARI -->
                            <div class="tab-pane fade" id="custom-tabspic-februari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive">
                                            <table id="tabel_targetpic_februari"
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
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: blueviolet;">
                                                            FEBRUARI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- FEBRUARI -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- FEBRUARI -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                        $target = $data['TOTAL TARGET FEBRUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED FEBRUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['pic_user'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- FEBRUARI -->
                                                            <td><?= ($data['TW1 FEBRUARI'] != 0 ? number_format(floatval($data['TW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 FEBRUARI'] != 0 ? number_format(floatval($data['RW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 FEBRUARI'] > 0 && $data['RW1 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW1 FEBRUARI'] / $data['TW1 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 FEBRUARI'] != 0 ? number_format(floatval($data['TW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 FEBRUARI'] != 0 ? number_format(floatval($data['RW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 FEBRUARI'] > 0 && $data['RW2 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW2 FEBRUARI'] / $data['TW2 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 FEBRUARI'] != 0 ? number_format(floatval($data['TW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 FEBRUARI'] != 0 ? number_format(floatval($data['RW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 FEBRUARI'] > 0 && $data['RW3 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW3 FEBRUARI'] / $data['TW3 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 FEBRUARI'] != 0 ? number_format(floatval($data['TW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 FEBRUARI'] != 0 ? number_format(floatval($data['RW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 FEBRUARI'] > 0 && $data['RW4 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW4 FEBRUARI'] / $data['TW4 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 FEBRUARI'] != 0 ? number_format(floatval($data['TW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 FEBRUARI'] != 0 ? number_format(floatval($data['RW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 FEBRUARI'] > 0 && $data['RW5 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW5 FEBRUARI'] / $data['TW5 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED FEBRUARI'] > 0 && $data['TOTAL TARGET FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED FEBRUARI'] > 0 && $data['TOTAL TARGET FEBRUARI'] > 0) {
                                                                    number_format(($deviasi / $data['TOTAL TARGET FEBRUARI'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV FEBRUARI -->
                            <div class="tab-pane fade" id="custom-tabspic-maret" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive">
                                            <table id="tabel_targetpic_maret"
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
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: aquamarine;">
                                                            MARET
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- MARET -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- MARET -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                        $target = $data['TOTAL TARGET MARET'];
                                                        $achiev = $data['TOTAL ACHIEVED MARET'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['pic_user'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>

                                                                <!-- MARET -->
                                                            <td><?= ($data['TW1 MARET'] != 0 ? number_format(floatval($data['TW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 MARET'] != 0 ? number_format(floatval($data['RW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 MARET'] > 0 && $data['RW1 MARET'] > 0) {
                                                                    $persentase = ($data['RW1 MARET'] / $data['TW1 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 MARET'] != 0 ? number_format(floatval($data['TW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 MARET'] != 0 ? number_format(floatval($data['RW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 MARET'] > 0 && $data['RW2 MARET'] > 0) {
                                                                    $persentase = ($data['RW2 MARET'] / $data['TW2 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 MARET'] != 0 ? number_format(floatval($data['TW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 MARET'] != 0 ? number_format(floatval($data['RW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 MARET'] > 0 && $data['RW3 MARET'] > 0) {
                                                                    $persentase = ($data['RW3 MARET'] / $data['TW3 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 MARET'] != 0 ? number_format(floatval($data['TW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 MARET'] != 0 ? number_format(floatval($data['RW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 MARET'] > 0 && $data['RW4 MARET'] > 0) {
                                                                    $persentase = ($data['RW4 MARET'] / $data['TW4 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 MARET'] != 0 ? number_format(floatval($data['TW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 MARET'] != 0 ? number_format(floatval($data['RW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 MARET'] > 0 && $data['RW5 MARET'] > 0) {
                                                                    $persentase = ($data['RW5 MARET'] / $data['TW5 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED MARET'] > 0 && $data['TOTAL TARGET MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED MARET'] > 0 && $data['TOTAL TARGET MARET'] > 0) {
                                                                    number_format(($deviasi / $data['TOTAL TARGET MARET'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
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
                                    href="#custom-tabsbowheer-month2025" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2025</a>
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
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-month2026" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2026</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-enam" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">JANUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-tujuh" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">FEBRUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsbowheer-delapan" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MARET</a>
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
                                                <td><?php
                                                if ($data['persen_achiev'] > 0 && $data['persen_achiev'] > 0) {
                                                    echo number_format($data['persen_achiev'], 1, ",", ".") . '%';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                                </td>
                                                <td><?php
                                                if ($data['persen_deviasi'] > 0 && $data['persen_deviasi'] > 0) {
                                                    echo number_format($data['persen_deviasi'], 0, ",", ".") . '%';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
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
                            <div class="tab-pane fade" id="custom-tabsbowheer-month2025" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetbowheer_month2025"
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
                                                <th colspan="8"
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
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 4</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 4; $i++): ?>
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
                                                    <td><?= ($data['TW3 DESEMBER'] != 0 ? number_format(floatval($data['TW3 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 DESEMBER'] != 0 ? number_format(floatval($data['RW3 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 DESEMBER'] != 0 ? number_format(floatval($data['TW4 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 DESEMBER'] != 0 ? number_format(floatval($data['RW4 DESEMBER']), 0, ",", ".") : '-') ?>
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
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV MONTH 2026 -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-month2026" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetbowheer_month2026"
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
                                                            JANUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            FEBRUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            MARET
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

                                                                <!-- JANUARI -->
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET JANUARI'] > 0 && $data['TOTAL ACHIEVED JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET FEBRUARI'] > 0 && $data['TOTAL ACHIEVED FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET MARET'] > 0 && $data['TOTAL ACHIEVED MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
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

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-enam" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_januari" class="table table-bordered table-striped">
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
                                                    JANUARI</th>
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
                                                <!-- JANUARI -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aqua;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aqua;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aqua;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aqua;">
                                                    WEEK 4</th>
                                                    <th colspan="2"
                                                    style="text-align:center; background-color: aqua;">
                                                    WEEK 5</th>
                                            </tr>
                                            <tr>
                                                <!-- JANUARI -->
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterBowheer as $data):
                                                $target = $data['TOTAL TARGET JANUARI'];
                                                $achiev = $data['TOTAL ACHIEVED JANUARI'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['nama_bowheer'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>
                                                        <!-- JANUARI -->
                                                    <td><?= ($data['TW1 JANUARI'] != 0 ? number_format(floatval($data['TW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 JANUARI'] != 0 ? number_format(floatval($data['RW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 JANUARI'] != 0 ? number_format(floatval($data['TW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 JANUARI'] != 0 ? number_format(floatval($data['RW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 JANUARI'] != 0 ? number_format(floatval($data['TW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 JANUARI'] != 0 ? number_format(floatval($data['RW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 JANUARI'] != 0 ? number_format(floatval($data['TW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 JANUARI'] != 0 ? number_format(floatval($data['RW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW5 JANUARI'] != 0 ? number_format(floatval($data['TW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW5 JANUARI'] != 0 ? number_format(floatval($data['RW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
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

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-tujuh" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_februari" class="table table-bordered table-striped">
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
                                                    style="text-align:center; background-color: blueviolet;">
                                                    FEBRUARI</th>
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
                                                <!-- FEBRUARI -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet;">
                                                    WEEK 4</th>
                                                    <th colspan="2"
                                                    style="text-align:center; background-color: blueviolet;">
                                                    WEEK 5</th>
                                            </tr>
                                            <tr>
                                                <!-- FEBRUARI -->
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterBowheer as $data):
                                                $target = $data['TOTAL TARGET FEBRUARI'];
                                                $achiev = $data['TOTAL ACHIEVED FEBRUARI'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['nama_bowheer'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>
                                                        <!-- FEBRUARI -->
                                                    <td><?= ($data['TW1 FEBRUARI'] != 0 ? number_format(floatval($data['TW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 FEBRUARI'] != 0 ? number_format(floatval($data['RW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 FEBRUARI'] != 0 ? number_format(floatval($data['TW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 FEBRUARI'] != 0 ? number_format(floatval($data['RW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 FEBRUARI'] != 0 ? number_format(floatval($data['TW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 FEBRUARI'] != 0 ? number_format(floatval($data['RW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 FEBRUARI'] != 0 ? number_format(floatval($data['TW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 FEBRUARI'] != 0 ? number_format(floatval($data['RW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW5 FEBRUARI'] != 0 ? number_format(floatval($data['TW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW5 FEBRUARI'] != 0 ? number_format(floatval($data['RW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
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

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabsbowheer-delapan" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_maret" class="table table-bordered table-striped">
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
                                                    style="text-align:center; background-color: aquamarine;">
                                                    MARET</th>
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
                                                <!-- MARET -->
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 3</th>
                                                <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 4</th>
                                                    <th colspan="2"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 5</th>
                                            </tr>
                                            <tr>
                                                <!-- MARET -->
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                <?php endfor; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($getTargetRincianFilterBowheer as $data):
                                                $target = $data['TOTAL TARGET MARET'];
                                                $achiev = $data['TOTAL ACHIEVED MARET'];
                                                $deviasi = $target - $achiev;
                                                ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $data['nama_bowheer'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>
                                                        <!-- MARET -->
                                                    <td><?= ($data['TW1 MARET'] != 0 ? number_format(floatval($data['TW1 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 MARET'] != 0 ? number_format(floatval($data['RW1 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW2 MARET'] != 0 ? number_format(floatval($data['TW2 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 MARET'] != 0 ? number_format(floatval($data['RW2 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW3 MARET'] != 0 ? number_format(floatval($data['TW3 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 MARET'] != 0 ? number_format(floatval($data['RW3 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW4 MARET'] != 0 ? number_format(floatval($data['TW4 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 MARET'] != 0 ? number_format(floatval($data['RW4 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TW5 MARET'] != 0 ? number_format(floatval($data['TW5 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW5 MARET'] != 0 ? number_format(floatval($data['RW5 MARET']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
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
                                    href="#custom-tabsregional-month2025" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2025</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-oktober" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-november" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-desember" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-month2026" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY 2026</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-januari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">JANUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-februari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">FEBRUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabsregional-maret" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MARET</a>
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
                                                <td><?= rtrim(rtrim(number_format($data['persen_achiev'], 1, '.', ''), '0'), '.') ?>%
                                                </td>
                                                <td><?= rtrim(rtrim(number_format($data['persen_deviasi'], 1, '.', ''), '0'), '.') ?>%
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

                            <!-- TAB NAV MONTH 2025 -->
                            <div class="tab-pane fade" id="custom-tabsregional-month2025" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetregional_month2025"
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
                                                            <td><?php if ($data['GRAND TOTAL TARGET'] > 0) {
                                                                echo number_format((($deviasi / $data['GRAND TOTAL TARGET']) * 100), 0, ",", ".") . '%';
                                                            } else {
                                                                echo '-';
                                                            } ?>
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
                            <div class="tab-pane fade" id="custom-tabsregional-oktober" role="tabpanel"
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
                            <div class="tab-pane fade" id="custom-tabsregional-november" role="tabpanel"
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

                            <!-- TAB NAV DESEMBER -->
                            <div class="tab-pane fade" id="custom-tabsregional-desember" role="tabpanel"
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

                            <!-- TAB NAV MONTH 2026 -->
                            <div class="tab-pane fade" id="custom-tabsregional-month2026" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none w-full">
                                        <div class="card-body table-responsive w-full" style="width:100%;">
                                            <table id="tabel_targetregional_month2026"
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
                                                            JANUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            FEBRUARI
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">
                                                            MARET
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
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET JANUARI'] > 0 && $data['TOTAL ACHIEVED JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET FEBRUARI'] > 0 && $data['TOTAL ACHIEVED FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET MARET'] > 0 && $data['TOTAL ACHIEVED MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
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
                                                            <td><?php if ($data['GRAND TOTAL TARGET'] > 0) {
                                                                echo number_format((($deviasi / $data['GRAND TOTAL TARGET']) * 100), 0, ",", ".") . '%';
                                                            } else {
                                                                echo '-';
                                                            } ?>
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

                            <!-- TAB NAV JANUARI -->
                            <div class="tab-pane fade" id="custom-tabsregional-januari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetregional_januari"
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
                                                            JANUARI
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
                                                        <!-- JANUARI -->
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
                                                        <!-- JANUARI -->
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
                                                        $target = $data['TOTAL TARGET JANUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED JANUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- JANUARI -->
                                                            <td><?= ($data['TW1 JANUARI'] != 0 ? number_format(floatval($data['TW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 JANUARI'] != 0 ? number_format(floatval($data['RW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 JANUARI'] != 0 ? number_format(floatval($data['TW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 JANUARI'] != 0 ? number_format(floatval($data['RW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 JANUARI'] != 0 ? number_format(floatval($data['TW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 JANUARI'] != 0 ? number_format(floatval($data['RW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 JANUARI'] != 0 ? number_format(floatval($data['TW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 JANUARI'] != 0 ? number_format(floatval($data['RW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 JANUARI'] != 0 ? number_format(floatval($data['TW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 JANUARI'] != 0 ? number_format(floatval($data['RW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
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

                            <!-- TAB NAV FEBRUARI -->
                            <div class="tab-pane fade" id="custom-tabsregional-februari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetregional_februari"
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
                                                            FEBRUARI
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
                                                        <!-- FEBRUARI -->
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
                                                        <!-- FEBRUARI -->
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
                                                        $target = $data['TOTAL TARGET FEBRUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED FEBRUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- FEBRUARI -->
                                                            <td><?= ($data['TW1 FEBRUARI'] != 0 ? number_format(floatval($data['TW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 FEBRUARI'] != 0 ? number_format(floatval($data['RW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 FEBRUARI'] != 0 ? number_format(floatval($data['TW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 FEBRUARI'] != 0 ? number_format(floatval($data['RW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 FEBRUARI'] != 0 ? number_format(floatval($data['TW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 FEBRUARI'] != 0 ? number_format(floatval($data['RW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 FEBRUARI'] != 0 ? number_format(floatval($data['TW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 FEBRUARI'] != 0 ? number_format(floatval($data['RW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 FEBRUARI'] != 0 ? number_format(floatval($data['TW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 FEBRUARI'] != 0 ? number_format(floatval($data['RW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
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

                            <!-- TAB NAV FEBRUARI -->
                            <div class="tab-pane fade" id="custom-tabsregional-februari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetregional_februari"
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
                                                            FEBRUARI
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
                                                        <!-- FEBRUARI -->
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
                                                        <!-- FEBRUARI -->
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
                                                        $target = $data['TOTAL TARGET FEBRUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED FEBRUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- FEBRUARI -->
                                                            <td><?= ($data['TW1 FEBRUARI'] != 0 ? number_format(floatval($data['TW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 FEBRUARI'] != 0 ? number_format(floatval($data['RW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 FEBRUARI'] != 0 ? number_format(floatval($data['TW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 FEBRUARI'] != 0 ? number_format(floatval($data['RW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 FEBRUARI'] != 0 ? number_format(floatval($data['TW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 FEBRUARI'] != 0 ? number_format(floatval($data['RW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 FEBRUARI'] != 0 ? number_format(floatval($data['TW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 FEBRUARI'] != 0 ? number_format(floatval($data['RW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 FEBRUARI'] != 0 ? number_format(floatval($data['TW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 FEBRUARI'] != 0 ? number_format(floatval($data['RW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
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

                            <!-- TAB NAV MARET -->
                            <div class="tab-pane fade" id="custom-tabsregional-maret" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetregional_maret"
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
                                                            MARET
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
                                                        <!-- MARET -->
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
                                                        <!-- MARET -->
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
                                                        $target = $data['TOTAL TARGET MARET'];
                                                        $achiev = $data['TOTAL ACHIEVED MARET'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['regional_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>

                                                                <!-- MARET -->
                                                            <td><?= ($data['TW1 MARET'] != 0 ? number_format(floatval($data['TW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 MARET'] != 0 ? number_format(floatval($data['RW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW2 MARET'] != 0 ? number_format(floatval($data['TW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 MARET'] != 0 ? number_format(floatval($data['RW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW3 MARET'] != 0 ? number_format(floatval($data['TW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 MARET'] != 0 ? number_format(floatval($data['RW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW4 MARET'] != 0 ? number_format(floatval($data['TW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 MARET'] != 0 ? number_format(floatval($data['RW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TW5 MARET'] != 0 ? number_format(floatval($data['TW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 MARET'] != 0 ? number_format(floatval($data['RW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
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
                                    href="#custom-tabscity-month2025" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-oktober" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-november" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-desember" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-month2026" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-januari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">JANUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-februari" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">FEBRUARI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabscity-maret" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MARET</a>
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
                                        foreach ($getTargetAllCity as $data): ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['area_target'] ?></td>
                                                <td><?= $data['pic_target'] ?></td>
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
                                                <td><?= rtrim(rtrim(number_format($data['persen_achiev'], 1, '.', ''), '0'), '.') ?>%
                                                </td>
                                                <td><?= rtrim(rtrim(number_format($data['persen_deviasi'], 1, '.', ''), '0'), '.') ?>%
                                                </td>
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
                                            <th colspan="3">Total</th>
                                            <th><span id="totalTargetInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalAchievedInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalSisaInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalPersentaseTargetInvoiceCity">0</span>
                                            </th>
                                            <th><span id="totalPersentaseDeviasiTargetInvoiceCity">0</span>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabscity-month2025" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive">
                                            <table id="tabel_targetcity_month2025"
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
                                                            PIC AREA</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
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
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
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
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= $data['pic_target'] ?></td>
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
                                                            <td>
                                                                <?php
                                                                if (!empty($data['GRAND TOTAL TARGET']) && $data['GRAND TOTAL TARGET'] != 0) {
                                                                    echo number_format(($deviasi / $data['GRAND TOTAL TARGET'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
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
                            <div class="tab-pane fade" id="custom-tabscity-oktober" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive md:w-auto">
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
                                                            PIC AREA</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="15"
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
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- OKTOBER -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
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
                                                            <th style="text-align:center; background-color: blueviolet;">
                                                                %
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
                                                            <td><?= $data['pic_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET OKTOBER'] != 0 ? number_format(floatval($data['TOTAL TARGET OKTOBER']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TW1 OKTOBER'] != 0 ? number_format(floatval($data['TW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 OKTOBER'] != 0 ? number_format(floatval($data['RW1 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 OKTOBER'] > 0 && $data['RW1 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW1 OKTOBER'] / $data['TW1 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 OKTOBER'] != 0 ? number_format(floatval($data['TW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 OKTOBER'] != 0 ? number_format(floatval($data['RW2 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 OKTOBER'] > 0 && $data['RW2 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW2 OKTOBER'] / $data['TW2 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 OKTOBER'] != 0 ? number_format(floatval($data['TW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 OKTOBER'] != 0 ? number_format(floatval($data['RW3 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 OKTOBER'] > 0 && $data['RW3 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW3 OKTOBER'] / $data['TW3 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 OKTOBER'] != 0 ? number_format(floatval($data['TW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 OKTOBER'] != 0 ? number_format(floatval($data['RW4 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 OKTOBER'] > 0 && $data['RW4 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW4 OKTOBER'] / $data['TW4 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 OKTOBER'] != 0 ? number_format(floatval($data['TW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 OKTOBER'] != 0 ? number_format(floatval($data['RW5 OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 OKTOBER'] > 0 && $data['RW5 OKTOBER'] > 0) {
                                                                    $persentase = ($data['RW5 OKTOBER'] / $data['TW5 OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED OKTOBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED OKTOBER']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED OKTOBER'] > 0 && $data['TOTAL TARGET OKTOBER'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED OKTOBER'] / $data['TOTAL TARGET OKTOBER']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if (!empty($data['TOTAL TARGET OKTOBER']) && $data['TOTAL TARGET OKTOBER'] != 0) {
                                                                    echo number_format(($deviasi / $data['TOTAL TARGET OKTOBER'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
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
                            <div class="tab-pane fade" id="custom-tabscity-november" role="tabpanel"
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
                                                    PIC AREA
                                                </th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET
                                                </th>
                                                <th colspan="12"
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
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    ACHIEVED ( % )
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI ( % )
                                                </th>
                                            </tr>
                                            </tr>

                                            <tr>
                                                <!-- NOVEMBER -->
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 1</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 2</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: blueviolet; color: #ffffff;">
                                                    WEEK 3</th>
                                                <th colspan="3"
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
                                                    <th style="text-align:center; background-color: blueviolet;">
                                                        %
                                                    </th>
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
                                                    <td><?= $data['pic_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>

                                                    <!-- NOVEMBER -->
                                                    <td><?= ($data['TW1 NOVEMBER'] != 0 ? number_format(floatval($data['TW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 NOVEMBER'] != 0 ? number_format(floatval($data['RW1 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW1 NOVEMBER'] > 0 && $data['RW1 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW1 NOVEMBER'] / $data['TW1 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW2 NOVEMBER'] != 0 ? number_format(floatval($data['TW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 NOVEMBER'] != 0 ? number_format(floatval($data['RW2 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW2 NOVEMBER'] > 0 && $data['RW2 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW2 NOVEMBER'] / $data['TW2 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW3 NOVEMBER'] != 0 ? number_format(floatval($data['TW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW3 NOVEMBER'] != 0 ? number_format(floatval($data['RW3 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW3 NOVEMBER'] > 0 && $data['RW3 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW3 NOVEMBER'] / $data['TW3 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW4 NOVEMBER'] != 0 ? number_format(floatval($data['TW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW4 NOVEMBER'] != 0 ? number_format(floatval($data['RW4 NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW4 NOVEMBER'] > 0 && $data['RW4 NOVEMBER'] > 0) {
                                                            $persentase = ($data['RW4 NOVEMBER'] / $data['TW4 NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>

                                                    <td><?= ($data['TOTAL ACHIEVED NOVEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED NOVEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TOTAL ACHIEVED NOVEMBER'] > 0 && $data['TOTAL TARGET NOVEMBER'] > 0) {
                                                            $persentase = ($data['TOTAL ACHIEVED NOVEMBER'] / $data['TOTAL TARGET NOVEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($data['TOTAL TARGET NOVEMBER']) && $data['TOTAL TARGET NOVEMBER'] != 0) {
                                                            echo number_format(($deviasi / $data['TOTAL TARGET NOVEMBER'] * 100), 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total</th>
                                                <?php for ($i = 0; $i < 17; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV KELIMA -->
                            <div class="tab-pane fade" id="custom-tabscity-desember" role="tabpanel"
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
                                                    PIC AREA</th>
                                                <th rowspan="3"
                                                    style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    TOTAL TARGET</th>
                                                <th colspan="6"
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
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    ACHIEVED ( % )
                                                </th>
                                                <th rowspan="3"
                                                    style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                    DEVIASI ( % )
                                                </th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <th colspan="3"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 1</th>
                                                <th colspan="3"
                                                    style="text-align:center; background-color: aquamarine;">
                                                    WEEK 2</th>
                                            </tr>
                                            <tr>
                                                <!-- DESEMBER -->
                                                <?php for ($i = 0; $i < 2; $i++): ?>
                                                    <th style="text-align:center; background-color: indianred;">TARGET</th>
                                                    <th style="text-align:center; background-color: darkseagreen;">ACHIEVED
                                                    </th>
                                                    <th style="text-align:center; background-color: blueviolet;">
                                                        %
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
                                                    <td><?= $data['pic_target'] ?></td>
                                                    <td><?= ($data['TOTAL TARGET DESEMBER'] != 0 ? number_format(floatval($data['TOTAL TARGET DESEMBER']), 0, ",", ".") : '-') ?>
                                                        <!-- DESEMBER -->
                                                    <td><?= ($data['TW1 DESEMBER'] != 0 ? number_format(floatval($data['TW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW1 DESEMBER'] != 0 ? number_format(floatval($data['RW1 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW1 DESEMBER'] > 0 && $data['RW1 DESEMBER'] > 0) {
                                                            $persentase = ($data['RW1 DESEMBER'] / $data['TW1 DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TW2 DESEMBER'] != 0 ? number_format(floatval($data['TW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($data['RW2 DESEMBER'] != 0 ? number_format(floatval($data['RW2 DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TW2 DESEMBER'] > 0 && $data['RW2 DESEMBER'] > 0) {
                                                            $persentase = ($data['RW2 DESEMBER'] / $data['TW2 DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= ($data['TOTAL ACHIEVED DESEMBER'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED DESEMBER']), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($data['TOTAL ACHIEVED DESEMBER'] > 0 && $data['TOTAL TARGET DESEMBER'] > 0) {
                                                            $persentase = ($data['TOTAL ACHIEVED DESEMBER'] / $data['TOTAL TARGET DESEMBER']) * 100;
                                                            echo number_format($persentase, 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($data['TOTAL TARGET DESEMBER']) && $data['TOTAL TARGET DESEMBER'] != 0) {
                                                            echo number_format(($deviasi / $data['TOTAL TARGET DESEMBER'] * 100), 0, ",", ".") . '%';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total</th>
                                                <?php for ($i = 0; $i < 11; $i++): ?>
                                                    <th>0</th>
                                                <?php endfor; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB NAV MONTH 2025 -->
                            <div class="tab-pane fade" id="custom-tabscity-month2026" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive">
                                            <table id="tabel_targetcity_month2026"
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
                                                            PIC AREA</th>
                                                        <th rowspan="2"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
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
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="2"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
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
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['GRAND TOTAL TARGET'];
                                                        $achiev = $data['GRAND TOTAL ACHIEVED'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= $data['pic_target'] ?></td>
                                                            <td><?= ($data['GRAND TOTAL TARGET'] != 0 ? number_format(floatval($data['GRAND TOTAL TARGET']), 0, ",", ".") : '-') ?>

                                                                <!-- OKTOBER -->
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET JANUARI'] > 0 && $data['TOTAL ACHIEVED JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET FEBRUARI'] > 0 && $data['TOTAL ACHIEVED FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL TARGET MARET'] > 0 && $data['TOTAL ACHIEVED MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
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
                                                            <td>
                                                                <?php
                                                                if (!empty($data['GRAND TOTAL TARGET']) && $data['GRAND TOTAL TARGET'] != 0) {
                                                                    echo number_format(($deviasi / $data['GRAND TOTAL TARGET'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
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

                            <!-- TAB NAV JANUARI -->
                            <div class="tab-pane fade" id="custom-tabscity-januari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive md:w-auto">
                                            <table id="tabel_targetcity_januari"
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
                                                            PIC AREA</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: aqua;">
                                                            JANUARI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- JANUARI -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aqua;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- JANUARI -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['TOTAL TARGET JANUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED JANUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= $data['pic_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET JANUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET JANUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- JANUARI -->
                                                            <td><?= ($data['TW1 JANUARI'] != 0 ? number_format(floatval($data['TW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 JANUARI'] != 0 ? number_format(floatval($data['RW1 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 JANUARI'] > 0 && $data['RW1 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW1 JANUARI'] / $data['TW1 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 JANUARI'] != 0 ? number_format(floatval($data['TW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 JANUARI'] != 0 ? number_format(floatval($data['RW2 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 JANUARI'] > 0 && $data['RW2 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW2 JANUARI'] / $data['TW2 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 JANUARI'] != 0 ? number_format(floatval($data['TW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 JANUARI'] != 0 ? number_format(floatval($data['RW3 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 JANUARI'] > 0 && $data['RW3 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW3 JANUARI'] / $data['TW3 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 JANUARI'] != 0 ? number_format(floatval($data['TW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 JANUARI'] != 0 ? number_format(floatval($data['RW4 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 JANUARI'] > 0 && $data['RW4 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW4 JANUARI'] / $data['TW4 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 JANUARI'] != 0 ? number_format(floatval($data['TW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 JANUARI'] != 0 ? number_format(floatval($data['RW5 JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 JANUARI'] > 0 && $data['RW5 JANUARI'] > 0) {
                                                                    $persentase = ($data['RW5 JANUARI'] / $data['TW5 JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED JANUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED JANUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED JANUARI'] > 0 && $data['TOTAL TARGET JANUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED JANUARI'] / $data['TOTAL TARGET JANUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if (!empty($data['TOTAL TARGET JANUARI']) && $data['TOTAL TARGET JANUARI'] != 0) {
                                                                    echo number_format(($deviasi / $data['TOTAL TARGET JANUARI'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV FEBRUARI -->
                            <div class="tab-pane fade" id="custom-tabscity-februari" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive md:w-auto">
                                            <table id="tabel_targetcity_februari"
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
                                                            PIC AREA</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: blueviolet;">
                                                            FEBRUARI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- FEBRUARI -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: blueviolet;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- FEBRUARI -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['TOTAL TARGET FEBRUARI'];
                                                        $achiev = $data['TOTAL ACHIEVED FEBRUARI'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= $data['pic_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL TARGET FEBRUARI']), 0, ",", ".") : '-') ?>

                                                                <!-- FEBRUARI -->
                                                            <td><?= ($data['TW1 FEBRUARI'] != 0 ? number_format(floatval($data['TW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 FEBRUARI'] != 0 ? number_format(floatval($data['RW1 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 FEBRUARI'] > 0 && $data['RW1 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW1 FEBRUARI'] / $data['TW1 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 FEBRUARI'] != 0 ? number_format(floatval($data['TW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 FEBRUARI'] != 0 ? number_format(floatval($data['RW2 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 FEBRUARI'] > 0 && $data['RW2 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW2 FEBRUARI'] / $data['TW2 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 FEBRUARI'] != 0 ? number_format(floatval($data['TW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 FEBRUARI'] != 0 ? number_format(floatval($data['RW3 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 FEBRUARI'] > 0 && $data['RW3 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW3 FEBRUARI'] / $data['TW3 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 FEBRUARI'] != 0 ? number_format(floatval($data['TW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 FEBRUARI'] != 0 ? number_format(floatval($data['RW4 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 FEBRUARI'] > 0 && $data['RW4 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW4 FEBRUARI'] / $data['TW4 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 FEBRUARI'] != 0 ? number_format(floatval($data['TW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 FEBRUARI'] != 0 ? number_format(floatval($data['RW5 FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 FEBRUARI'] > 0 && $data['RW5 FEBRUARI'] > 0) {
                                                                    $persentase = ($data['RW5 FEBRUARI'] / $data['TW5 FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED FEBRUARI'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED FEBRUARI']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED FEBRUARI'] > 0 && $data['TOTAL TARGET FEBRUARI'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED FEBRUARI'] / $data['TOTAL TARGET FEBRUARI']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if (!empty($data['TOTAL TARGET FEBRUARI']) && $data['TOTAL TARGET FEBRUARI'] != 0) {
                                                                    echo number_format(($deviasi / $data['TOTAL TARGET FEBRUARI'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB NAV MARET -->
                            <div class="tab-pane fade" id="custom-tabscity-maret" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0 table-responsive md:w-auto">
                                            <table id="tabel_targetcity_maret"
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
                                                            PIC AREA</th>
                                                        <th rowspan="3"
                                                            style="min-width: 150px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL TARGET</th>
                                                        <th colspan="15"
                                                            style="text-align:center; background-color: aquamarine;">
                                                            MARET
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            TOTAL ACHIEVED
                                                        </th>
                                                        <th rowspan="3"
                                                            style="min-width: 200px; text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            ACHIEVED ( % )
                                                        </th>
                                                        <th rowspan="3"
                                                            style="text-align:center; vertical-align: middle; background-color: darkslategray; color: #ffffff;">
                                                            DEVIASI ( % )
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- MARET -->
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            1
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            2
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            3
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            4
                                                        </th>
                                                        <th colspan="3"
                                                            style="text-align:center; background-color: aquamarine;">WEEK
                                                            5
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <!-- MARET -->
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
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
                                                    foreach ($getTargetRincianFilterCity as $data):
                                                        $target = $data['TOTAL TARGET MARET'];
                                                        $achiev = $data['TOTAL ACHIEVED MARET'];
                                                        $deviasi = $target - $achiev;
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><?= $data['area_target'] ?></td>
                                                            <td><?= $data['pic_target'] ?></td>
                                                            <td><?= ($data['TOTAL TARGET MARET'] != 0 ? number_format(floatval($data['TOTAL TARGET MARET']), 0, ",", ".") : '-') ?>

                                                                <!-- MARET -->
                                                            <td><?= ($data['TW1 MARET'] != 0 ? number_format(floatval($data['TW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW1 MARET'] != 0 ? number_format(floatval($data['RW1 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW1 MARET'] > 0 && $data['RW1 MARET'] > 0) {
                                                                    $persentase = ($data['RW1 MARET'] / $data['TW1 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW2 MARET'] != 0 ? number_format(floatval($data['TW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW2 MARET'] != 0 ? number_format(floatval($data['RW2 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW2 MARET'] > 0 && $data['RW2 MARET'] > 0) {
                                                                    $persentase = ($data['RW2 MARET'] / $data['TW2 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW3 MARET'] != 0 ? number_format(floatval($data['TW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW3 MARET'] != 0 ? number_format(floatval($data['RW3 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW3 MARET'] > 0 && $data['RW3 MARET'] > 0) {
                                                                    $persentase = ($data['RW3 MARET'] / $data['TW3 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW4 MARET'] != 0 ? number_format(floatval($data['TW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW4 MARET'] != 0 ? number_format(floatval($data['RW4 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW4 MARET'] > 0 && $data['RW4 MARET'] > 0) {
                                                                    $persentase = ($data['RW4 MARET'] / $data['TW4 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TW5 MARET'] != 0 ? number_format(floatval($data['TW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($data['RW5 MARET'] != 0 ? number_format(floatval($data['RW5 MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TW5 MARET'] > 0 && $data['RW5 MARET'] > 0) {
                                                                    $persentase = ($data['RW5 MARET'] / $data['TW5 MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?= ($data['TOTAL ACHIEVED MARET'] != 0 ? number_format(floatval($data['TOTAL ACHIEVED MARET']), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td><?= ($deviasi != 0 ? number_format(floatval($deviasi), 0, ",", ".") : '-') ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if ($data['TOTAL ACHIEVED MARET'] > 0 && $data['TOTAL TARGET MARET'] > 0) {
                                                                    $persentase = ($data['TOTAL ACHIEVED MARET'] / $data['TOTAL TARGET MARET']) * 100;
                                                                    echo number_format($persentase, 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if (!empty($data['TOTAL TARGET MARET']) && $data['TOTAL TARGET MARET'] != 0) {
                                                                    echo number_format(($deviasi / $data['TOTAL TARGET MARET'] * 100), 0, ",", ".") . '%';
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3">Total</th>
                                                        <?php for ($i = 0; $i < 20; $i++): ?>
                                                            <th>0</th>
                                                        <?php endfor; ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
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
            $('#tabel_targetbowheer_month2025').DataTable({
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
            $('#tabel_targetbowheer_month2026').DataTable({
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
            $('#tabel_targetbowheer_januari').DataTable({
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
            $('#tabel_targetbowheer_februari').DataTable({
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
            $('#tabel_targetbowheer_maret').DataTable({
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
            $('#tabel_targetcity_month2025').DataTable({
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
            $('#tabel_targetcity_month2026').DataTable({
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
            $('#tabel_targetcity_januari').DataTable({
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
            $('#tabel_targetcity_februari').DataTable({
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
            $('#tabel_targetcity_maret').DataTable({
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
            $('#tabel_targetpic_month2025').DataTable({
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
            $('#tabel_targetpic_month2026').DataTable({
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
                autoWidth: true,     // aktifkan scroll horizontal otomatis
                responsive: true,   // matikan agar kolom tetap sejajar
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
            $('#tabel_targetpic_januari').DataTable({
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
            $('#tabel_targetpic_februari').DataTable({
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
            $('#tabel_targetpic_maret').DataTable({
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
            $('#tabel_targetregional_month2025').DataTable({
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
            $('#tabel_targetregional_month2026').DataTable({
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
            $('#tabel_targetregional_januari').DataTable({
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
            $('#tabel_targetregional_februari').DataTable({
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
            $('#tabel_targetregional_maret').DataTable({
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

                let persen = (totalAchievedInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(1);

                document.getElementById('totalTargetInvoiceBowheer').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalAchievedInvoiceBowheer').innerText = totalAchievedInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalSisaInvoiceBowheer').innerText = totalSisaInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceBowheer').innerText = (totalAchievedInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(0) + " %";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceBowheer').innerText = (totalSisaInvoiceBowheer / totalTargetInvoiceBowheer * 100).toFixed(0) + " %";

                document.getElementById('dashboardTargetInvoice').innerText = "RP. " + totalTargetInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardAchievInvoice').innerText = "RP. " + totalAchievedInvoiceBowheer.toLocaleString('id-ID');
                document.getElementById('dashboardSisaInvoice').innerText = "RP. " + totalSisaInvoiceBowheer.toLocaleString('id-ID');

                let dashboardInvoice = document.getElementById('dashboardPersentaseInvoice');

                let icon = '';
                let colorClass = '';
                let textValue = persen + ' %';

                // Tentukan kondisi tampilan
                if (persen < 100) {
                    icon = '<i class="fas fa-arrow-down text-danger ms-2"></i>';
                    colorClass = 'text-danger fw-bold';
                } else if (persen == 100) {
                    icon = '<i class="fas fa-check-circle text-success ms-2"></i>';
                    colorClass = 'text-success fw-bold';
                } else if (persen > 100) {
                    icon = '<i class="fas fa-arrow-up text-success ms-2"></i>';
                    colorClass = 'text-success fw-bold';
                }

                dashboardInvoice.innerHTML = `
    <span class="${colorClass}" style="font-weight:600;">
        ${textValue} ${icon}
    </span>
`;

                function hitungSisaHariKerja() {
                    const today = new Date();
                    const batasAkhir = new Date(today.getFullYear(), 11, 12); // 12 Desember 2025 (bulan 11)

                    // Jika sudah melewati batas → return 0 agar tidak Infinity
                    if (today > batasAkhir) return 0;

                    let sisaHari = 0;
                    let current = new Date(today);

                    while (current <= batasAkhir) {
                        const day = current.getDay(); // 0 = Minggu, 6 = Sabtu
                        if (day !== 0) sisaHari++; // Hitung hari kerja Senin–Sabtu
                        current.setDate(current.getDate() + 1);
                    }

                    return sisaHari;
                }

                const sisaHariKerja = hitungSisaHariKerja();

                if (sisaHariKerja === 0 || totalSisaInvoiceBowheer <= 0) {
                    document.getElementById('dashboardTargetInvoiceHarian').innerText = "-";
                    document.getElementById('dashboardPersentaseTargetHarian').innerText = "-";
                } else {
                    const targetHarian = totalSisaInvoiceBowheer / sisaHariKerja;
                    const persentaseHarian = ((100 - persen) / sisaHariKerja).toFixed(1);

                    document.getElementById('dashboardTargetInvoiceHarian').innerText =
                        "Rp. " + Math.round(targetHarian).toLocaleString('id-ID');

                    document.getElementById('dashboardPersentaseTargetHarian').innerText =
                        persentaseHarian + " %";
                }
            }

            function highlightCells() {
                $('#tabel_targetbowheer_summary tbody tr').each(function () {
                    const cell = $(this).find('td:eq(6)'); // kolom ke-6 (ACHIEVED %)
                    let persenText = cell.text().trim();

                    // Bersihkan simbol lama (agar tidak dobel)
                    persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

                    const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

                    // Default: hapus isi lalu tambahkan kembali dengan ikon
                    let icon = '';
                    if (persen < 100) {
                        icon = ' <i class="fas fa-arrow-down text-danger"></i>'; // merah turun
                        cell.addClass('cell-red');
                    } else if (persen === 100) {
                        icon = ' <i class="fas fa-check-circle text-success"></i>'; // hijau centang
                        cell.addClass('cell-green-light');
                    } else if (persen > 100) {
                        icon = ' <i class="fas fa-arrow-up text-success"></i>'; // hijau naik
                        cell.addClass('cell-green-dark');
                    }

                    // Update isi cell
                    cell.html(`${persenText}${icon}`);
                });
            }

            // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
            table.on('draw', function () {
                updateTotal();
                highlightCells();
            });

            // Hitung total pertama kali saat tabel dimuat
            updateTotal();
            highlightCells();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetbowheer_month2025').DataTable({
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

            const table = $('#tabel_targetbowheer_month2026').DataTable({
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

            const table = $('#tabel_targetbowheer_januari').DataTable({
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

            const table = $('#tabel_targetbowheer_maret').DataTable({
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
                let totalTargetInvoiceCity = 0;
                let totalAchievedInvoiceCity = 0;
                let totalSisaInvoiceCity = 0;
                let totalPersentaseTargetInvoiceCity = 0;
                let totalPersentaseDeviasiTargetInvoiceCity = 0;

                data.each(function (row) {

                    totalTargetInvoiceCity += parseFloat(row[3].replace(/\./g, '')) || 0;
                    totalAchievedInvoiceCity += parseFloat(row[4].replace(/\./g, '')) || 0;
                    totalSisaInvoiceCity += parseFloat(row[5].replace(/\./g, '')) || 0;
                    totalPersentaseTargetInvoiceCity += parseFloat(row[6].replace(/\./g, '')) || 0;
                    totalPersentaseDeviasiTargetInvoiceCity += parseFloat(row[7].replace(/\./g, '')) || 0;
                });

                document.getElementById('totalTargetInvoiceCity').innerText = totalTargetInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalAchievedInvoiceCity').innerText = totalAchievedInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalSisaInvoiceCity').innerText = totalSisaInvoiceCity.toLocaleString('id-ID');
                document.getElementById('totalPersentaseTargetInvoiceCity').innerText = (totalAchievedInvoiceCity / totalTargetInvoiceCity * 100).toFixed(0) + " %";
                document.getElementById('totalPersentaseDeviasiTargetInvoiceCity').innerText = (totalSisaInvoiceCity / totalTargetInvoiceCity * 100).toFixed(0) + " %";
            }

            function highlightCells() {
                $('#tabel_targetcity_summary tbody tr').each(function () {
                    const cell = $(this).find('td:eq(5)'); // kolom ke-6 (ACHIEVED %)
                    let persenText = cell.text().trim();

                    // Bersihkan simbol lama (agar tidak dobel)
                    persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

                    const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

                    // Default: hapus isi lalu tambahkan kembali dengan ikon
                    let icon = '';
                    if (persen < 100) {
                        icon = ' <i class="fas fa-arrow-down text-danger"></i>'; // merah turun
                        cell.addClass('cell-red');
                    } else if (persen === 100) {
                        icon = ' <i class="fas fa-check-circle text-success"></i>'; // hijau centang
                        cell.addClass('cell-green-light');
                    } else if (persen > 100) {
                        icon = ' <i class="fas fa-arrow-up text-success"></i>'; // hijau naik
                        cell.addClass('cell-green-dark');
                    }

                    // Update isi cell
                    cell.html(`${persenText}${icon}`);
                });
            }

            // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
            table.on('draw', function () {
                updateTotal();
                highlightCells();
            });

            // Hitung total pertama kali saat tabel dimuat
            updateTotal();
            highlightCells();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetcity_month2025').DataTable({
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

                let totalKolom = Array(15).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 17; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }


                const persenOktober = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenNovember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenDesember = hitungPersen(totalKolom[8], totalKolom[9]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[11]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[12]);

                $(table.column(6).footer()).text(persenOktober.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenNovember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDesember.toFixed(0) + ' %');
                $(table.column(15).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(16).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetcity_month2026').DataTable({
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

                let totalKolom = Array(15).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 17; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }


                const persenOktober = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenNovember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenDesember = hitungPersen(totalKolom[8], totalKolom[9]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[11]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[12]);

                $(table.column(6).footer()).text(persenOktober.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenNovember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDesember.toFixed(0) + ' %');
                $(table.column(15).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(16).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

                let totalKolom = Array(19).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 21; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Oktober = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2Oktober = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenW3Oktober = hitungPersen(totalKolom[8], totalKolom[9]);
                const persenW4Oktober = hitungPersen(totalKolom[11], totalKolom[12]);
                const persenW5Oktober = hitungPersen(totalKolom[14], totalKolom[15]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[17]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[18]);

                $(table.column(6).footer()).text(persenW1Oktober.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2Oktober.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenW3Oktober.toFixed(0) + ' %');
                $(table.column(15).footer()).text(persenW4Oktober.toFixed(0) + ' %');
                $(table.column(18).footer()).text(persenW5Oktober.toFixed(0) + ' %');
                $(table.column(21).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(22).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

                let totalKolom = Array(16).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 18; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1November = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2November = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenW3November = hitungPersen(totalKolom[8], totalKolom[9]);
                const persenW4November = hitungPersen(totalKolom[11], totalKolom[12]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[14]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[15]);

                $(table.column(6).footer()).text(persenW1November.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2November.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenW3November.toFixed(0) + ' %');
                $(table.column(15).footer()).text(persenW4November.toFixed(0) + ' %');
                $(table.column(18).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(19).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

                let totalKolom = Array(10).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 12; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2Desember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[8]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[9]);

                $(table.column(6).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(13).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetcity_januari').DataTable({
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

                let totalKolom = Array(10).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 12; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2Desember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[8]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[9]);

                $(table.column(6).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(13).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetcity_februari').DataTable({
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

                let totalKolom = Array(10).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 12; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2Desember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[8]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[9]);

                $(table.column(6).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(13).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetcity_maret').DataTable({
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

                let totalKolom = Array(10).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 12; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[2], totalKolom[3]);
                const persenW2Desember = hitungPersen(totalKolom[5], totalKolom[6]);
                const persenGrand = hitungPersen(totalKolom[1], totalKolom[8]);
                const persenDeviasi = hitungPersen(totalKolom[1], totalKolom[9]);

                $(table.column(6).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(9).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(13).footer()).text(persenDeviasi.toFixed(0) + ' %');

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
                let totalAchievInvoicePIC = 0;
                let totalTargetInvoicePIC = 0;
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
                const achieved = (totalTargetInvoicePIC > 0 && totalAchievInvoicePIC > 0)
                    ? ((totalAchievInvoicePIC / totalTargetInvoicePIC) * 100).toFixed(0) + " %"
                    : "0";

                const outstanding = (totalDeviasiInvoicePIC > 0 && totalTargetInvoicePIC > 0)
                    ? ((totalDeviasiInvoicePIC / totalTargetInvoicePIC) * 100).toFixed(0) + " %"
                    : "0";

                document.getElementById('totalPersentaseTargetInvoicePIC').innerText = achieved;
                document.getElementById('totalPersentaseDeviasiTargetInvoicePIC').innerText = outstanding;
            }

            function highlightCells() {
                $('#tabel_targetpic_summary tbody tr').each(function () {
                    const cell = $(this).find('td:eq(5)'); // kolom ke-6 (ACHIEVED %)
                    let persenText = cell.text().trim();

                    // Bersihkan simbol lama (agar tidak dobel)
                    persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

                    const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

                    // Default: hapus isi lalu tambahkan kembali dengan ikon
                    let icon = '';
                    if (persen < 100) {
                        icon = ' <i class="fas fa-arrow-down text-danger"></i>'; // merah turun
                        cell.addClass('cell-red');
                    } else if (persen === 100) {
                        icon = ' <i class="fas fa-check-circle text-success"></i>'; // hijau centang
                        cell.addClass('cell-green-light');
                    } else if (persen > 100) {
                        icon = ' <i class="fas fa-arrow-up text-success"></i>'; // hijau naik
                        cell.addClass('cell-green-dark');
                    }

                    // Update isi cell
                    cell.html(`${persenText}${icon}`);
                });
            }

            // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
            table.on('draw', function () {
                updateTotal();
                highlightCells();
            });

            // Hitung total pertama kali saat tabel dimuat
            updateTotal();
            highlightCells();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetpic_month2025').DataTable({
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

                const deviasiOktNov = totalKolom[1] - totalKolom[2];
                const deviasiNovDes = totalKolom[4] - totalKolom[5];
                const deviasiDesTotal = totalKolom[7] - totalKolom[8];

                const footerRows = $('#tabel_targetpic_month2025 tfoot tr');
                const barisDeviasi = $(footerRows[1]).find('th');

                barisDeviasi.eq(1).text(deviasiOktNov.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                barisDeviasi.eq(2).text(deviasiNovDes.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                barisDeviasi.eq(3).text(deviasiDesTotal.toLocaleString('id-ID', { maximumFractionDigits: 0 }));

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

            const table = $('#tabel_targetpic_month2026').DataTable({
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

                const deviasiOktNov = totalKolom[1] - totalKolom[2];
                const deviasiNovDes = totalKolom[4] - totalKolom[5];
                const deviasiDesTotal = totalKolom[7] - totalKolom[8];

                const footerRows = $('#tabel_targetpic_month2026 tfoot tr');
                const barisDeviasi = $(footerRows[1]).find('th');

                barisDeviasi.eq(1).text(deviasiOktNov.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                barisDeviasi.eq(2).text(deviasiNovDes.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                barisDeviasi.eq(3).text(deviasiDesTotal.toLocaleString('id-ID', { maximumFractionDigits: 0 }));

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

                let totalKolom = Array(18).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 20; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Oktober = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2Oktober = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenW3Oktober = hitungPersen(totalKolom[7], totalKolom[8]);
                const persenW4Oktober = hitungPersen(totalKolom[10], totalKolom[11]);
                const persenW5Oktober = hitungPersen(totalKolom[13], totalKolom[14]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[16]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[17]);

                $(table.column(5).footer()).text(persenW1Oktober.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2Oktober.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenW3Oktober.toFixed(0) + ' %');
                $(table.column(14).footer()).text(persenW4Oktober.toFixed(0) + ' %');
                $(table.column(17).footer()).text(persenW5Oktober.toFixed(0) + ' %');
                $(table.column(20).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(21).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

                let totalKolom = Array(15).fill(0);

                data.each(function (row) {
                    for (let i = 2; i < 17; i++) { // misal kolom angka mulai dari index ke-2
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
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1November = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2November = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenW3November = hitungPersen(totalKolom[7], totalKolom[8]);
                const persenW4November = hitungPersen(totalKolom[10], totalKolom[11]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[13]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[14]);

                $(table.column(5).footer()).text(persenW1November.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2November.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenW3November.toFixed(0) + ' %');
                $(table.column(14).footer()).text(persenW4November.toFixed(0) + ' %');
                $(table.column(17).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(18).footer()).text(persenDeviasi.toFixed(0) + ' %');


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

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2Desember = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[7]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[8]);

                $(table.column(5).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetpic_januari').DataTable({
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

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2Desember = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[7]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[8]);

                $(table.column(5).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetpic_maret').DataTable({
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

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2Desember = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[7]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[8]);

                $(table.column(5).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            const table = $('#tabel_targetpic_februari').DataTable({
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

                function hitungPersen(target, achieved) {
                    if (target === 0 && achieved > 0) return 100;
                    if (target === 0 && achieved === 0) return 0;
                    return (achieved / target) * 100;
                }

                const persenW1Desember = hitungPersen(totalKolom[1], totalKolom[2]);
                const persenW2Desember = hitungPersen(totalKolom[4], totalKolom[5]);
                const persenGrand = hitungPersen(totalKolom[0], totalKolom[7]);
                const persenDeviasi = hitungPersen(totalKolom[0], totalKolom[8]);

                $(table.column(5).footer()).text(persenW1Desember.toFixed(0) + ' %');
                $(table.column(8).footer()).text(persenW2Desember.toFixed(0) + ' %');
                $(table.column(11).footer()).text(persenGrand.toFixed(0) + ' %');
                $(table.column(12).footer()).text(persenDeviasi.toFixed(0) + ' %');

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

            function highlightCells() {
                $('#tabel_targetregional_summary tbody tr').each(function () {
                    const cell = $(this).find('td:eq(5)'); // kolom ke-6 (ACHIEVED %)
                    let persenText = cell.text().trim();

                    // Bersihkan simbol lama (agar tidak dobel)
                    persenText = persenText.replace(/<[^>]+>/g, '').replace(/[\u2191\u2193✅❌]/g, '');

                    const persen = parseFloat(persenText.replace('%', '').replace(',', '.')) || 0;

                    // Default: hapus isi lalu tambahkan kembali dengan ikon
                    let icon = '';
                    if (persen < 100) {
                        icon = ' <i class="fas fa-arrow-down text-danger"></i>'; // merah turun
                        cell.addClass('cell-red');
                    } else if (persen === 100) {
                        icon = ' <i class="fas fa-check-circle text-success"></i>'; // hijau centang
                        cell.addClass('cell-green-light');
                    } else if (persen > 100) {
                        icon = ' <i class="fas fa-arrow-up text-success"></i>'; // hijau naik
                        cell.addClass('cell-green-dark');
                    }

                    // Update isi cell
                    cell.html(`${persenText}${icon}`);
                });
            }

            // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
            table.on('draw', function () {
                updateTotal();
                highlightCells();
            });

            // Hitung total pertama kali saat tabel dimuat
            updateTotal();
            highlightCells();
        });

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetregional_month2025').DataTable({
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

            const table = $('#tabel_targetregional_month2026').DataTable({
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

        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            const table = $('#tabel_targetregional_januari').DataTable({
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

            const table = $('#tabel_targetregional_februari').DataTable({
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

            const table = $('#tabel_targetregional_maret').DataTable({
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


            const dataBarInvoiceArea = <?php echo json_encode($getTargetAllBowheer); ?>;
            const invoiceAreaBar = dataBarInvoiceArea.map(item => item.nama_bowheer);
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
    <style>
        #tabel_targetpic_summary tfoot th {
            text-align: right;
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* upgrade warna header merah */

        .glow-red {
            border: 1px solid rgba(255, 0, 0, 0.7);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-red:hover {
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.9);
        }

        /* upgrade warna header hijau */
        .glow-green {
            border: 1px solid rgba(0, 255, 0, 0.7);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-green:hover {
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.9);
        }

        /* upgrade warna header kuning */
        .glow-yellow {
            border: 1px solid rgba(196, 218, 4, 0.4);
            box-shadow: 0 6px 12px rgba(196, 218, 4, 0.6);
            /* arah bayangan ke bawah */
            transition: box-shadow 0.3s ease-in-out, transform 0.2s;
            border-radius: 10px;
        }

        .glow-yellow:hover {
            box-shadow: 0 10px 18px rgba(196, 218, 4, 0.8);
            /* glow lebih tegas ke bawah saat hover */
            transform: translateY(-2px);
            /* efek sedikit mengangkat saat hover */
        }


        /* upgrade warna table */

        .cell-red {
            font-color: #ffb3b3 !important;
            color: #eb0000ff !important;
            font-weight: bold;
        }

        /* 🟢 Hijau muda untuk = 100% */
        .cell-green-light {
            color: #b3ffb3 !important;
            font-weight: bold;
        }

        /* 🟩 Hijau tua untuk > 100% */
        .cell-green-dark {
            color: #33cc33 !important;
            font-weight: bold;
            font-weight: bold;
        }

        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        #paginationControlsPIC button,
        #paginationControlsArea button {
            margin: 0 4px;
            border-radius: 50%;
            padding: 6px 12px;
            transition: all 0.2s;
        }

        #paginationControlsPIC button.active,
        #paginationControlsArea button.active {
            background-color: #007bff !important;
            color: white !important;
            box-shadow: 0 0 6px rgba(0, 123, 255, 0.6);
        }

        #paginationControlsPIC button:hover,
        #paginationControlsArea button:hover {
            transform: scale(1.1);
        }
    </style>