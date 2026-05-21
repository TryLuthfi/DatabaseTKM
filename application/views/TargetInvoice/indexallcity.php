<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;


// Ambil hanya yang unik
$unique_bowheer = array_unique(array_column($getAllData, 'nama_bowheer'));
$unique_area = array_unique(array_column($getAllData, 'area_target'));
$unique_month = array_unique(array_column($getAllData, 'month_target'));
$unique_week = array_unique(array_column($getAllData, 'week_target'));
?>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark" style="text-align: center;">
                            <?= $judul ?>
                        </h1>
                    </div>
                </div>
            </div>
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
                                    href="#custom-tabs-satu" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">SUMMARY TARGET</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-dua" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">MONTHLY</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-tiga" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">OKTOBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-empat" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">NOVEMBER</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-lima" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">DESEMBER</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabs-satu" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="tabel_targetbowheer_filter_summary"
                                    class="table table-bordered table-striped">
                                    <thead style="text-align: center;">
                                        <tr>
                                            <th>No</th>
                                            <th>KOTA</th>
                                            <th>TARGET INVOICE</th>
                                            <th>ACHIEVED INVOICE</th>
                                            <th>OUTSTANDING</th>
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
                                            </tr>

                                            <?php
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th><span id="totalTargetInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalAchievedInvoiceBowheer">0</span>
                                            </th>
                                            <th><span id="totalSisaInvoiceBowheer">0</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="modal-footer">
                                    <a href="#" class="btn btn-success float-right text-bold"
                                        data-target="#modal-lg-tambah_boq" data-toggle="modal">Tambah Invoice &nbsp;</a>
                                </div>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabs-dua" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetbowheer_filter_month"
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
                                                        $deviasi = max(0, $target - $achiev);
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
                            <div class="tab-pane fade" id="custom-tabs-tiga" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="container-fluid px-0">
                                    <!-- Hilangkan card atau minimal hilangkan padding-nya -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-body p-0">
                                            <table id="tabel_targetbowheer_filter_city1"
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
                                                        $deviasi = max(0, $target - $achiev);
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
                            <div class="tab-pane fade" id="custom-tabs-empat" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_filter_city2"
                                        class="table table-bordered table-striped">
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
                                                $deviasi = max(0, $target - $achiev);

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
                            <div class="tab-pane fade" id="custom-tabs-lima" role="tabpanel"
                                aria-labelledby="custom-tabs-three-profile-tab">

                                <div class="card-body table-responsive" style="width:100%;">
                                    <table id="tabel_targetbowheer_filter_city3"
                                        class="table table-bordered table-striped">
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
                                                $deviasi = max(0, $target - $achiev);
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

        <form action="<?php echo site_url('MTargetInvoice/addInvoice'); ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah_boq">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Invoice</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Bowheer / Project</label>
                                    <select id="filter_bowheer" class="form-control" data-placeholder="Pilih Project"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Bowheer</option>
                                        <?php foreach ($unique_bowheer as $bowheer): ?>
                                            <option value="<?= $bowheer ?>"><?= $bowheer ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Area</label>
                                    <select id="filter_area" class="form-control" data-placeholder="Pilih Area"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Area</option>
                                        <?php foreach ($unique_area as $area): ?>
                                            <option value="<?= $area ?>"><?= $area ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Bulan</label>
                                    <select id="filter_month" class="form-control" data-placeholder="Pilih Bulan"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Bulan Invoice</option>
                                        <?php foreach ($unique_month as $month): ?>
                                            <option value="<?= $month ?>"><?= $month ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Week</label>
                                    <select id="filter_week" class="form-control" data-placeholder="Pilih Minggu"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Minggu Invoice</option>
                                        <?php foreach ($unique_week as $week): ?>
                                            <option value="<?= $week ?>"><?= $week ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Target Invoice</label>
                                <input type="text" class="form-control" name="target_invoice" autocomplete="off"
                                    placeholder="0" disabled>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Realisasi Invoice</label>
                                <input type="text" class="form-control" name="achiev_invoice" autocomplete="off"
                                    placeholder="0">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                                <button type="submit" name="btnEdit" class="btn btn-primary"><i
                                        class="fa fa-spinner fa-spin loading" style="display:none"></i>
                                    Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
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
        $('#tabel_targetbowheer_filter_summary').DataTable({
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
        $('#tabel_targetbowheer_filter_month').DataTable({
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
        $('#tabel_targetbowheer_filter_city1').DataTable({
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
        $('#tabel_targetbowheer_filter_city2').DataTable({
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
        $('#tabel_targetbowheer_filter_city3').DataTable({
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
        const table = $('#tabel_targetbowheer_filter_summary').DataTable({
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

            document.getElementById('totalTargetInvoiceBowheer').innerText = totalTargetInvoiceBowheer.toLocaleString('id-ID');
            document.getElementById('totalAchievedInvoiceBowheer').innerText = totalAchievedInvoiceBowheer.toLocaleString('id-ID');
            document.getElementById('totalSisaInvoiceBowheer').innerText = totalSisaInvoiceBowheer.toLocaleString('id-ID');
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

        const table = $('#tabel_targetbowheer_filter_month').DataTable({
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

        const table = $('#tabel_targetbowheer_filter_city1').DataTable({
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

        const table = $('#tabel_targetbowheer_filter_city2').DataTable({
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

        const table = $('#tabel_targetbowheer_filter_city3').DataTable({
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
        function loadTargetInvoice() {
            const bowheer = $('#filter_bowheer').val();
            const area = $('#filter_area').val();
            const month = $('#filter_month').val();
            const week = $('#filter_week').val();

            // Pastikan semua filter sudah dipilih
            if (bowheer && area && month && week) {
                $.ajax({
                    url: "<?= base_url('TargetInvoice/get_target_invoice') ?>",
                    type: "POST",
                    dataType: "json",
                    data: {
                        bowheer: $('#filter_bowheer').val(),
                        area: $('#filter_area').val(),
                        month: $('#filter_month').val(),
                        week: $('#filter_week').val()
                    },
                    success: function (res) {
                        console.log(res);
                        let qty = res.qty_target ? parseFloat(res.qty_target) : 0;

                        // Format ke bentuk rupiah dengan titik
                        let formatted = "Rp " + qty.toLocaleString('id-ID');

                        // Masukkan ke input field
                        $('[name="target_invoice"]').val(formatted);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error:", error);
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        // Jalankan ketika salah satu dropdown berubah
        $('#filter_bowheer, #filter_area, #filter_month, #filter_week').on('change', function () {
            loadTargetInvoice();
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