<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$nilai_po = 0;
$rkap = 345000000000;
$target_cleanlist_rkap = 345000;
$persentase_po = 0;
$nilai_invoice = 0;
$sisa_invoice = 0;
$total = 1;
$total_nilai_invoice = 0;
$total_sisa_invoice = 0;

$persentase_plan = 0;
$persentase_achiev = 0;
$persentase_total = 0;

$total_cluster_bak = 0;
$total_cluster_spk = 0;
$total_cluster_rfs = 0;

foreach ($top_area_bak as $data):
  $total_cluster_bak += $data['total_cluster_bak'];
endforeach;

foreach ($gettopAreaSPK as $data):
  $total_cluster_spk += $data['total_cluster_spk'];
endforeach;

foreach ($top_area_rfs as $data):
  $total_cluster_rfs += $data['total_cluster_rfs'];
endforeach;

// Ambil data unik
$unique_regional = array_unique(array_column($getFilterData, 'regional_project'));
$unique_pic = array_unique(array_column($getFilterData, 'pic_project'));
$unique_area = array_unique(array_column($getFilterData, 'area_project'));
$unique_stagging = array_unique(array_column($getFilterData, 'main_status'));

// Encode ke JSON agar bisa digunakan di JavaScript
$progressJSON = json_encode($getFilterData);

$total_hp_plan_regional = 0;
$stagging_cleanlist_regional = 0;
$stagging_canvasing_regional = 0;
$stagging_bak_regional = 0;
$stagging_hld_regional = 0;
$stagging_spk_regional = 0;
$stagging_lld_regional = 0;
$stagging_implementasi_regional = 0;
$stagging_rfs_regional = 0;
$stagging_atp_regional = 0;
$stagging_closed_regional = 0;
$stagging_hold_regional = 0;
$stagging_drop_regional = 0;

?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 class="m-0 text-dark" style="text-align: center;"><?= $judul ?></h1>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">

      <div class="row">
        <div class="col-md-12" style="margin_botttom:10px;">
          <!-- DIRECT CHAT DANGER -->
          <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
            <div class="card-header">
              <h3 class="card-title">FILTER DATA</h3>

              <div class="card-tools">
                <button id="cardfiltercollapse" type="button" class="btn btn-tool" data-card-widget="collapse">
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
                      <label>REGIONAL</label>
                      <select id="filter_regional" class="select2" multiple data-placeholder="Pilih Regional"
                        style="width: 100%;">
                        <?php foreach ($unique_regional as $regional): ?>
                          <option value="<?= $regional ?>"><?= $regional ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-group">
                      <label>PIC</label>
                      <select id="filter_pic" class="select2" multiple data-placeholder="Pilih PIC"
                        style="width: 100%;">
                        <?php foreach ($unique_pic as $pic): ?>
                          <option value="<?= $pic ?>"><?= $pic ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-group">
                      <label>AREA</label>
                      <select id="filter_area" class="select2" multiple data-placeholder="Pilih Area"
                        style="width: 100%;">
                        <?php foreach ($unique_area as $area): ?>
                          <option value="<?= $area ?>"><?= $area ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-sm-3">
                    <div class="form-group">
                      <label>STAGGING</label>
                      <select id="filter_stagging" class="select2" multiple data-placeholder="Pilih Stagging"
                        style="width: 100%;">
                        <?php foreach ($unique_stagging as $stagging): ?>
                          <option value="<?= $stagging ?>"><?= $stagging ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="modal-footer col-sm-12">
                    <button type="button" id="reset_filter" class="btn btn-danger" data-dismiss="modal">Hapus</button>
                    <button id="btnFilterDataProject" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading"
                        style="display:none"></i> Cari </button>
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
        <div class="col-md-12" style="margin_botttom:10px;">
          <!-- DIRECT CHAT DANGER -->
          <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
            <div class="card-header">
              <h3 class="card-title">ACHIEVED PO & INVOICE</h3>

              <div class="card-tools">
                <button id="cardpocollapse" type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>

            <div class="card-body" style="margin-top:10px;">
              <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="#">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                            class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">ACHIEVE PO 2025</span>
                          <span class="info-box-number" id="idtotalDonePO">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['nilai_awal_po']), 0, ",", ".") . " IDR" ?>
                            <?php endforeach ?>
                          </span>
                        </div>
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="#">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">ACHIEVE INVOICE 2025</span>
                          <span class="info-box-number" id="idtotalDoneInvoice">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['total_invoice']), 0, ",", ".") . " IDR" ?>
                            <?php endforeach ?>
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="#">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">SISA INVOICE 2025</span>
                          <span class="info-box-number" id="idtotalSisaInvoice">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['total_sisa_invoice']), 0, ",", ".") . " IDR" ?>
                            <?php endforeach ?>
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="#">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                            class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">NY RELEASE PO</span>
                          <span class="info-box-number">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['po_estimasi']), 0, ",", ".") . " IDR" ?>
                            <?php endforeach ?>
                          </span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                    </a>
                    <!-- /.info-box -->
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
        <div class="col-md-12" style="margin_botttom:10px;">
          <!-- DIRECT CHAT DANGER -->
          <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
            <div class="card-header">
              <h3 class="card-title">STAGGING PROJECT</h3>

              <div class="card-tools">
                <button id="cardstaggingcollapse" type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>

            <div class="card-body" style="margin-top:10px;">
              <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_plan">
                            <?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>TOTAL CLEANLIST</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_canvasing">
                            <?= number_format(floatval($totalHpPlan['stagging_cleanlist']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>01. CLEANLIST</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_bak">
                            <?= number_format(floatval($totalHpPlan['stagging_canvasing']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>02. CANVASING</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_spk">
                            <?= number_format(floatval($totalHpPlan['stagging_bak']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>03. BAK</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_hld">
                            <?= number_format(floatval($totalHpPlan['stagging_hld']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>04. HLD</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_lld">
                            <?= number_format(floatval($totalHpPlan['stagging_spk']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>05. SPK</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_kom">
                            <?= number_format(floatval($totalHpPlan['stagging_lld']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>06. LLD</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_rfs">
                            <?= number_format(floatval($totalHpPlan['stagging_implementasi']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>07. IMPLEMENTASI</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_atp">
                            <?= number_format(floatval($totalHpPlan['stagging_rfs']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>08. RFS</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_closed">
                            <?= number_format(floatval($totalHpPlan['stagging_atp']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>09. ATP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_closed">
                            <?= number_format(floatval($totalHpPlan['stagging_bast']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>10. BAST</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_closed">
                            <?= number_format(floatval($totalHpPlan['stagging_closed']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>11. CLOSED</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_closed">
                            <?= number_format(floatval($totalHpPlan['stagging_hold']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>12. HOLD</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                          <h3 id="idtotal_hp_closed">
                            <?= number_format(floatval($totalHpPlan['stagging_drop']), 0, ",", ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>13. DROP</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div class="content-header">
                <div class="container-fluid">
                  <div class="row mb-2">
                    <div class="col-sm-12">
                      <h1 class="m-0 text-dark" style="text-align: center;">STAGGING REGIONAL</h1>
                    </div><!-- /.col -->
                  </div><!-- /.row -->
                </div><!-- /.container-fluid -->
              </div>

              <?php if ($this->session->userdata('lokasi_user') == "HO") { ?>
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
                            <table id="table_detail" class="table table-bordered table-hover">
                              <thead class="bg-info">
                                <tr>
                                  <th>No</th>
                                  <th>REGIONAL</th>
                                  <th>HP PLAN</th>
                                  <th>CLEANLIST</th>
                                  <th>CANVASING</th>
                                  <th>BAK</th>
                                  <th>HLD</th>
                                  <th>SPK</th>
                                  <th>LLD</th>
                                  <th>IMPLEMENTASI</th>
                                  <th>RFS</th>
                                  <th>ATP</th>
                                  <th>BAST</th>
                                  <th>CLOSED</th>
                                  <th>HOLD</th>
                                  <th>DROP</th>
                                  <th>DETAIL</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $total = 1;

                                foreach ($stagging_regional as $data):

                                  $total_hp_plan_regional += $data['total_hp_plan'];
                                  $stagging_cleanlist_regional += $data['stagging_cleanlist'];
                                  $stagging_canvasing_regional += $data['stagging_canvasing'];
                                  $stagging_bak_regional += $data['stagging_bak'];
                                  $stagging_hld_regional += $data['stagging_hld'];
                                  $stagging_spk_regional += $data['stagging_spk'];
                                  $stagging_lld_regional += $data['stagging_lld'];
                                  $stagging_implementasi_regional += $data['stagging_implementasi'];
                                  $stagging_rfs_regional += $data['stagging_rfs'];
                                  $stagging_atp_regional += $data['stagging_atp'];
                                  $stagging_closed_regional += $data['stagging_closed'];
                                  $stagging_hold_regional += $data['stagging_hold'];
                                  $stagging_drop_regional += $data['stagging_drop'];

                                  ?>

                                  <tr>
                                    <td><?= $total++ ?></td>
                                    <td><?= $data['regional_project'] ?></td>
                                    <td><?php
                                    if ($data['total_hp_plan'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['total_hp_plan']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_cleanlist'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_cleanlist']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_canvasing'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_canvasing']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_bak'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_bak']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_hld'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_hld']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_spk'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_spk']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_lld'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_lld']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_implementasi'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_implementasi']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_rfs'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_rfs']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_atp'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_atp']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_bast'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_bast']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_closed'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_closed']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_hold'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_hold']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_drop'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_drop']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td>
                                      <a href="<?= site_url('Fiberstar_Project/Detail/' . $data['regional_project']); ?>"
                                        class="btn btn-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                  </tr>

                                <?php endforeach; ?>

                              </tbody>
                              <tfoot>
                                <tr>
                                  <th colspan="2">Total</th>
                                  <th colspan="1"><?= number_format(floatval($total_hp_plan_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_cleanlist_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_canvasing_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"><?= number_format(floatval($stagging_bak_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_hld_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_spk_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_lld_regional), 0, ",", ".") ?></th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_implementasi_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"><?= number_format(floatval($stagging_rfs_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_atp_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_closed_regional), 0, ",", ".") ?>
                                  <th colspan="1"><?= number_format(floatval($stagging_hold_regional), 0, ",", ".") ?>
                                  <th colspan="1"><?= number_format(floatval($stagging_drop_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"></th>
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
                        <h1 class="m-0 text-dark" style="text-align: center;">STAGGING AREA</h1>
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
                            <table id="table_detail_area" class="table table-bordered table-hover">
                              <thead class="bg-info">
                                <tr>
                                  <th>No</th>
                                  <th>REGIONAL</th>
                                  <th>KOTA</th>
                                  <th>HP PLAN</th>
                                  <th>CLEANLIST</th>
                                  <th>CANVASING</th>
                                  <th>BAK</th>
                                  <th>HLD</th>
                                  <th>SPK</th>
                                  <th>LLD</th>
                                  <th>IMPLEMENTASI</th>
                                  <th>RFS</th>
                                  <th>ATP</th>
                                  <th>BAST</th>
                                  <th>CLOSED</th>
                                  <th>HOLD</th>
                                  <th>DROP</th>
                                  <th>DETAIL</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $total = 1;

                                $total_hp_plan_regional = 0;
                                $stagging_cleanlist_regional = 0;
                                $stagging_canvasing_regional = 0;
                                $stagging_bak_regional = 0;
                                $stagging_hld_regional = 0;
                                $stagging_spk_regional = 0;
                                $stagging_lld_regional = 0;
                                $stagging_implementasi_regional = 0;
                                $stagging_rfs_regional = 0;
                                $stagging_atp_regional = 0;
                                $stagging_closed_regional = 0;
                                $stagging_hold_regional = 0;
                                $stagging_drop_regional = 0;

                                foreach ($stagging_area as $data):

                                  $total_hp_plan_regional += $data['total_hp_plan'];
                                  $stagging_cleanlist_regional += $data['stagging_cleanlist'];
                                  $stagging_canvasing_regional += $data['stagging_canvasing'];
                                  $stagging_bak_regional += $data['stagging_bak'];
                                  $stagging_hld_regional += $data['stagging_hld'];
                                  $stagging_spk_regional += $data['stagging_spk'];
                                  $stagging_lld_regional += $data['stagging_lld'];
                                  $stagging_implementasi_regional += $data['stagging_implementasi'];
                                  $stagging_rfs_regional += $data['stagging_rfs'];
                                  $stagging_atp_regional += $data['stagging_atp'];
                                  $stagging_closed_regional += $data['stagging_closed'];
                                  $stagging_hold_regional += $data['stagging_hold'];
                                  $stagging_drop_regional += $data['stagging_drop'];

                                  ?>

                                  <tr>
                                    <td><?= $total++ ?></td>
                                    <td><?= $data['regional_project'] ?></td>
                                    <td><?= $data['area_project'] ?></td>
                                    <td><?php
                                    if ($data['total_hp_plan'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['total_hp_plan']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_cleanlist'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_cleanlist']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_canvasing'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_canvasing']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_bak'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_bak']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_hld'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_hld']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_spk'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_spk']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_lld'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_lld']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_implementasi'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_implementasi']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_rfs'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_rfs']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_atp'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_atp']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_bast'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_bast']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_closed'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_closed']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_hold'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_hold']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td><?php
                                    if ($data['stagging_drop'] == "0") {
                                      echo "-";
                                    } else {
                                      echo number_format(floatval($data['stagging_drop']), 0, ",", ".");
                                    }
                                    ?></td>
                                    <td>
                                      <a href="<?= site_url('Fiberstar_Project/Detail/' . $data['area_project']); ?>"
                                        class="btn btn-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                  </tr>

                                <?php endforeach; ?>

                              </tbody>
                              <tfoot>
                                <tr>
                                  <th colspan="3">Total</th>
                                  <th colspan="1"><?= number_format(floatval($total_hp_plan_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_cleanlist_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_canvasing_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"><?= number_format(floatval($stagging_bak_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_hld_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_spk_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_lld_regional), 0, ",", ".") ?></th>
                                  <th colspan="1">
                                    <?= number_format(floatval($stagging_implementasi_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"><?= number_format(floatval($stagging_rfs_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_atp_regional), 0, ",", ".") ?></th>
                                  <th colspan="1"><?= number_format(floatval($stagging_closed_regional), 0, ",", ".") ?>
                                  <th colspan="1"><?= number_format(floatval($stagging_hold_regional), 0, ",", ".") ?>
                                  <th colspan="1"><?= number_format(floatval($stagging_drop_regional), 0, ",", ".") ?>
                                  </th>
                                  <th colspan="1"></th>
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
                        <h1 class="m-0 text-dark" style="text-align: center;">CHART STAGGING</h1>
                      </div><!-- /.col -->
                    </div><!-- /.row -->
                  </div><!-- /.container-fluid -->
                </div>

                <div class="card-body">
                  <div class="chart" style="height: 300px;">
                    <canvas id="barChart"
                      style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                  </div>
                  <div id="paginationControls" class="mt-3"></div>
                </div>

              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- /.col (LEFT) -->
          <div class="col-md-12">
            <!-- LINE CHART -->
            <!-- /.card -->

            <!-- BAR CHART -->
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">SUMMARY WEEKLY ACHIEVEMENT</h3>

                <div class="card-tools">
                  <button id="cardsummarycollapse" type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>

              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-sm-12">
                    <h1 class="m-0 text-dark mb-3" style="text-align: center;">FILTER RANGE TANGGAL</h1>
                  </div>
                </div>
                <div class="col-12">
                  <div class="row">
                    <div class="col-6">
                      <div class="form-group">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="far fa-calendar-alt"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control float-right" id="date-range" name="date"
                            value="<?= date('m/d/Y') ?> - <?= date('m/d/Y') ?>">
                        </div>
                      </div>
                    </div>
                    <div class="col-6">
                      <button type="button" class="btn btn-info" id="filter_range_tanggal">Cari</button>
                    </div>
                    </form>
                  </div>
                </div>

                <div class="row">
                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                          <h3 class="card-title">TOP AREA DONE BAK</h3>
                          <a href="javascript:void(0);" id="lihatDetailBAK">Lihat Detail</a>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex">
                          <p class="d-flex flex-column">
                            <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                              <span class="text-bold text-lg" id="jumlah_hp_done_bak"></span>
                            <?php endforeach ?>
                            <span>TOP AREA</span>
                          </p>
                          <p class="ml-auto d-flex flex-column text-right">
                            <span class="text-success">
                              <i class="text-bold text-lg"
                                id="jumlah_cl_done_bak"><?php echo $total_cluster_bak . " Cluster" ?></i>
                            </span>
                            <span class="text-muted">By Cleanlist ( % )</span>
                          </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4" style="min-height: 200px;">
                          <canvas id="fiberstar_chart_bar_bak" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                          <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Achieved
                          </span>
                        </div>
                        <div id="paginationControlsbak" class="mt-3 text-center"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                          <h3 class="card-title">TOP AREA DONE SPK</h3>
                          <a href="javascript:void(0);" id="lihatDetailSPK">Lihat Detail</a>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex">
                          <p class="d-flex flex-column">
                            <?php foreach ($getTopChartAllStaggingKota as $totalHpPlan): ?>
                              <span class="text-bold text-lg" id="jumlah_hp_done_spk"></span>
                            <?php endforeach ?>
                            <span>TOP AREA</span>
                          </p>
                          <p class="ml-auto d-flex flex-column text-right">
                            <span class="text-success">
                              <i class="text-bold text-lg"
                                id="jumlah_cl_done_spk"><?php echo $total_cluster_spk . " Cluster" ?></i>
                            </span>
                            <span class="text-muted">By Cleanlist ( % )</span>
                          </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4" style="min-height: 200px;">
                          <canvas id="fiberstar_chart_bar_spk" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                          <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Achieved
                          </span>
                        </div>
                        <div id="paginationControlsspk" class="mt-3 text-center"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                          <h3 class="card-title">TOP AREA DONE RFS</h3>
                          <a href="javascript:void(0);" id="lihatDetailRFS">Lihat Detail</a>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex">
                          <p class="d-flex flex-column">
                            <?php foreach ($getTopChartAllStaggingKota as $totalHpPlan): ?>
                              <span class="text-bold text-lg" id="jumlah_hp_done_rfs"></span>
                            <?php endforeach ?>
                            <span>TOP AREA</span>
                          </p>
                          <p class="ml-auto d-flex flex-column text-right">
                            <span class="text-success">
                              <i class="text-bold text-lg"
                                id="jumlah_cl_done_rfs"><?php echo $total_cluster_rfs . " Cluster" ?></i>
                            </span>
                            <span class="text-muted">By Cleanlist ( % )</span>
                          </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4" style="min-height: 200px;">
                          <canvas id="fiberstar_chart_bar_rfs" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                          <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Achieved
                          </span>
                        </div>
                        <div id="paginationControlsRFS" class="mt-3 text-center"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                          <h3 class="card-title">TOP AREA DONE ATP</h3>
                          <a href="javascript:void(0);">Lihat Detail</a>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex">
                          <p class="d-flex flex-column">
                            <?php foreach ($getTopChartAllStaggingKota as $totalHpPlan): ?>
                              <span class="text-bold text-lg" id="jumlah_hp_done_atp"></span>
                            <?php endforeach ?>
                            <span>TOP AREA</span>
                          </p>
                          <p class="ml-auto d-flex flex-column text-right">
                            <span class="text-success">
                              <i class="fas fa-arrow-up"></i>
                            </span>
                            <span class="text-muted">By Cleanlist ( % )</span>
                          </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4" style="min-height: 200px;">
                          <canvas id="fiberstar_chart_bar_atp" height="200"></canvas>
                        </div>

                        <div class="d-flex flex-row justify-content-end">
                          <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Achieved
                          </span>
                        </div>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->

          </div>
          <!-- /.col (RIGHT) -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>

  </section>


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
              <div class="row align-items-center">
                <div class="col-md-6 col-12 mb-2">
                  <h3 class="card-title">List Cleanlist Cluster</h3>
                </div>
                <div class="col-md-6 col-12 d-flex flex-wrap justify-content-end">
                  <div class="dropdown mr-2">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                      Download Report &nbsp; <i class="fas fa-print"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item">Excel</a>
                      <a class="dropdown-item">CSV</a>
                      <a class="dropdown-item">PDF</a>
                      <a class="dropdown-item">Print</a>
                    </div>
                  </div>
                  <a href="#" class="btn btn-success text-bold" data-toggle="modal"
                    data-target="#modal_tambah_cluster">Tambah Cluster &nbsp;<i class="fas fa-plus"></i></a>
                </div>
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive text-nowrap ">
              <table id="table_data" class="table table-bordered table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th>No</th> <!-- 0 -->
                    <th>Regional</th> <!-- 1 -->
                    <th>Area</th> <!-- 2 -->
                    <th>PIC</th> <!-- 3 -->
                    <th>Access ID</th> <!-- 4 -->
                    <th>Access Name</th> <!-- 5 -->
                    <th>HP Plan</th> <!-- 6 -->
                    <th>Nomor PO</th> <!-- 7 -->
                    <th>Tanggal PO</th> <!-- 8 -->
                    <th>Nilai PO</th> <!-- 9 -->
                    <th>Canvasing</th> <!-- 10 -->
                    <th>Status BAK</th> <!-- 11 -->
                    <th style="display: none;">HP BAK</th> <!-- 12 -->
                    <th>Status CBN</th> <!-- 13 -->
                    <th>Nomor SPK</th> <!-- 14 -->
                    <th style="display: none;">HP SPK</th> <!-- 15 -->
                    <th>Status HLD</th> <!-- 16 -->
                    <th style="display: none;">HP HLD</th> <!-- 17 -->
                    <th>Status LLD</th> <!-- 18 -->
                    <th style="display: none;">HP LLD</th> <!-- 19 -->
                    <th>KOM</th> <!-- 20 -->
                    <th>PKS</th> <!-- 21 -->
                    <th>Status Implementasi</th> <!-- 22 -->
                    <th>RFS</th> <!-- 23 -->
                    <th style="display: none;">HP RFS</th> <!-- 24 -->
                    <th>ATP</th> <!-- 25 -->
                    <th style="display: none;">HP ATP</th> <!-- 26 -->
                    <th>Stagging</th> <!-- 27 -->
                    <th>Done Invoice</th> <!-- 28 -->
                    <th>Sisa Invoice</th> <!-- 29 -->
                    <th>Progress</th> <!-- 30 -->
                    <th>Label</th> <!-- 31 -->
                    <th>Action</th> <!-- 32 -->
                  </tr>
                </thead>
                <tbody>
                  <?php

                  $total = 1;
                  foreach ($progress_implementasi as $data):

                    $persentase_plan = $data['plan_tiang'] + $data['plan_kabel_24'] + $data['plan_kabel_48'] + $data['plan_fat'] + $data['plan_closure'];
                    $persentase_achiev = $data['achiev_tiang'] + $data['achiev_kabel_24'] + $data['achiev_kabel_48'] + $data['achiev_fat'] + $data['achiev_closure'];

                    if ($persentase_achiev == 0 || $persentase_plan == 0) {
                      $persentase_total = 0;
                    } else {
                      $persentase_total = ($persentase_achiev / $persentase_plan) * 100;
                    }

                    ?>

                    <tr>
                      <td class="align-middle text-center"><?= $total++ ?></td>
                      <td class="align-middle"><?= $data['regional_project'] ?></td>
                      <td class="align-middle"><?= $data['area_project'] ?></td>
                      <td class="align-middle"><?= $data['pic_project'] ?></td>
                      <td class="align-middle"><?= $data['access_id_project'] ?></td>
                      <td class="align-middle"><?= $data['access_name_project'] ?></td>
                      <td class="align-middle text-center">
                        <?= number_format(floatval($data['hpplan_project']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle text-center"><?= $data['number_po'] ?></td>
                      <td class="align-middle text-center"><?= $data['tanggal_po'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['nilai_awal_po']), 0, ",", ".") ?></td>
                      <td class="align-middle"><?= $data['tgl_canvasing'] ?></td>
                      <td class="align-middle"><?= $data['status_bak'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_bak']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_cbn'] ?></td>
                      <td class="align-middle"><?= $data['spk_nomor'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['spk_hp']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_hld'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_hld']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['status_lld'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_lld']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['tgl_kom'] ?></td>
                      <td class="align-middle"><?= $data['tgl_pks'] ?></td>
                      <td class="align-middle text-center"><?= $data['status_implementasi'] ?></td>
                      <td class="align-middle"><?= $data['tanggal_rfs'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_rfs']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle"><?= $data['tanggal_atp'] ?></td>
                      <td class="align-middle" style="display: none;">
                        <?= number_format(floatval($data['hp_atp']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle text-center"><?= $data['main_status'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_invoice']), 0, ",", ".") ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_sisa_invoice']), 0, ",", ".") ?>
                      </td>
                      <td class="align-middle">
                        <div class="progress progress-xs">
                          <?php if ($persentase_total < '25') { ?>
                            <div class="progress-bar bg-danger" style="width: <?= round($persentase_total, 1) . "%" ?>">
                            </div>
                          <?php } else if ($persentase_total >= '25' && $persentase_total < '70') { ?>
                              <div class="progress-bar bg-warning" style="width: <?= round($persentase_total, 1) . "%" ?>">
                              </div>
                          <?php } else if ($persentase_total >= '70' && $persentase_total < '100') { ?>
                                <div class="progress-bar bg-primary" style="width: <?= round($persentase_total, 1) . "%" ?>">
                                </div>
                          <?php } else { ?>
                                <div class="progress-bar bg-success" style="width: <?= round($persentase_total, 1) . "%" ?>">
                                </div>
                          <?php } ?>
                        </div>
                      </td>
                      <?php if ($persentase_total < '25') { ?>
                        <td class="align-middle text-center"><span
                            class="badge bg-danger"><?= round($persentase_total, 1) . "%" ?></span></td>
                      <?php } else if ($persentase_total >= '25' && $persentase_total < '70') { ?>
                          <td class="align-middle text-center"><span
                              class="badge bg-warning"><?= round($persentase_total, 1) . "%" ?></span></td>
                      <?php } else if ($persentase_total >= '70' && $persentase_total < '100') { ?>
                            <td class="align-middle text-center"><span
                                class="badge bg-primary"><?= round($persentase_total, 1) . "%" ?></span></td>
                      <?php } else { ?>
                            <td class="align-middle text-center"><span
                                class="badge bg-success"><?= round($persentase_total, 1) . "%" ?></span></td>
                      <?php } ?>

                      <td>
                        <a href="<?php echo site_url('Fiberstar_Project_Detail/detailImplementasi/' . $data['primary_access_id_project']); ?>"
                          id="tombol_detail" class="btn btn-primary tombol_detail"><i class=" fas fa-share"></i></a>

                      </td>
                    </tr>

                  <?php endforeach; ?>

                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="6">Total</th>
                    <th colspan="1"><span id="totalHP">0</span></th>
                    <th colspan="2"></th>
                    <th colspan="1"><span id="totalPO">0</span></th>
                    <th colspan="12"></th>
                    <th colspan="1"><span id="totalDoneInvoice">0</span></th>
                    <th colspan="1"><span id="totalSisaInvoice">0</span></th>
                    <th colspan="3"></th>
                    <!--  27 -->
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

          <!-- modal untuk tambah data -->
          <form action=" <?php echo base_url('Fiberstar/add') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-manual">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Tambah Purcase Order</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label class="col-form-label">Provider</label>
                      <input type="text" class="form-control" name="kode_provider" autocomplete="off"
                        value="PT. FIBERSTAR">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Nomor PO</label>
                      <input type="text" class="form-control" name="nomor_po" autocomplete="off" placeholder="00000000">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Nilai PO</label>
                      <input type="number" class="form-control" name="nilai_po" id="nilai_po2" autocomplete="off"
                        placeholder="Rp. 000.000.000">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Tanggal PO</label>
                      <input type="date" class="form-control" name="tanggal_po" autocomplete="off"
                        value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Term Of Payment</label>
                      <select name="versi_po" class="form-control">
                        <option value="100%">100%</option>
                        <option value="50% ; 50%">50% ; 50%</option>
                        <option value="20% ; 20% ; 25% ; 25% ; 10%">20% ; 20% ; 25% ; 25% ; 10%</option>
                        <option value="top4">20% ; 20% ; 25% ; 25% ; 10%</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">Versi PO</label>
                      <select name="kode_po" class="form-control">
                        <option value="NEW">NEW PO</option>
                        <option value="FINAL">FINAL PO</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="col-form-label">STATUS PO</label>
                      <select name="status_po" class="form-control">
                        <option value="ACTIVE">AKTIF</option>
                        <option value="NONACTIVE">NONAKTIF</option>
                      </select>
                    </div>


                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>

                      <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i
                          class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                    </div>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
          </form>


          <!-- modal untuk edit data -->
          <?php $tgl = date('Y-m-d'); ?>
          <?php foreach ($progress_implementasi as $data):
            ?>
            <form action="<?php echo site_url('Fiberstar_Project/add'); ?>" method="post">
              <div class="modal fade" id="modal-lg-tambah_implementasi<?= $data['primary_access_id_project'] ?>">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Tambah Implementasi</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="primary_access_id_project"
                        value="<?= $data['primary_access_id_project'] ?>">
                      <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_akun') ?>">
                      <div class="form-group">
                        <label class="col-form-label">Access ID Project</label>
                        <input readonly type="text" class="form-control" name="access_id_project" autocomplete="off"
                          value="<?= $data['access_id_project'] ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Access Name Project</label>
                        <input readonly type="text" class="form-control" name="access_name_project" autocomplete="off"
                          value="<?= $data['access_name_project'] ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Input Date</label>
                        <input type="date" class="form-control" name="data_created" autocomplete="off"
                          value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Plan Tiang / Achiev Tiang / Deviasi</label>
                        <input readonly type="text" class="form-control" name="plan_tiang" autocomplete="off"
                          value="<?php echo $data['plan_tiang'] . " / " . $data['achiev_tiang'] . " / " . ($data['plan_tiang'] - $data['achiev_tiang']) ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Daily Progress Tiang</label>
                        <input type="text" class="form-control" name="achiev_tiang" autocomplete="off" placeholder="0">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Plan Kabel 24C / Achiev Kabel 24C / Deviasi</label>
                        <input readonly type="text" class="form-control" name="plan_kabel_24" autocomplete="off"
                          value="<?= $data['plan_kabel_24'] . " / " . $data['achiev_kabel_24'] . " / " . ($data['plan_kabel_24'] - $data['achiev_kabel_24']) ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Daily Progress Kabel 24C</label>
                        <input type="text" class="form-control" name="achiev_kabel_24" autocomplete="off" placeholder="0">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Plan Kabel 48C / Achiev Kabel 48C / Deviasi</label>
                        <input readonly type="text" class="form-control" name="plan_kabel_48" autocomplete="off"
                          value="<?= $data['plan_kabel_48'] . " / " . $data['achiev_kabel_48'] . " / " . ($data['plan_kabel_48'] - $data['achiev_kabel_48']) ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Daily Progress Kabel 48C</label>
                        <input type="text" class="form-control" name="achiev_kabel_48" autocomplete="off" placeholder="0">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Plan FAT / Achiev FAT / Deviasi</label>
                        <input readonly type="text" class="form-control" name="plan_fat" autocomplete="off"
                          value="<?= $data['plan_fat'] . " / " . $data['achiev_fat'] . " / " . ($data['plan_fat'] - $data['achiev_fat']) ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Daily Progress FAT</label>
                        <input type="text" class="form-control" name="achiev_fat" autocomplete="off" placeholder="0">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Plan Closure / Achiev Closure / Deviasi</label>
                        <input readonly type="text" class="form-control" name="plan_closure" autocomplete="off"
                          value="<?= $data['plan_closure'] . " / " . $data['achiev_closure'] . " / " . ($data['plan_closure'] - $data['achiev_closure']) ?>">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Daily Progress Closure</label>
                        <input type="text" class="form-control" name="achiev_closure" autocomplete="off" placeholder="0">
                      </div>
                      <div class="form-group">
                        <label class="col-form-label">Catatan</label>
                        <input type="text" class="form-control" name="keterangan_progress" autocomplete="off"
                          placeholder="0">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" name="btnEdit" class="btn btn-primary"><i
                            class="fa fa-spinner fa-spin loading" style="display:none"></i> Simpan</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          <?php endforeach; ?>

          <!-- modal untuk upload batch list cluster -->
          <div class="modal fade" id="modal_tambah_cluster" tabindex="-1" aria-labelledby="modalTitle"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalTitle">Tambah Data Logistik</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="d-flex justify-content-between">
                    <button class="btn btn-secondary" id="inputManual">📋 Input Manual</button>
                    <button class="btn btn-warning" id="inputBatch">📂 Input Batch</button>
                    <a href="uploads/formatuploadbatchclusterfiberstar.xlsx" class="btn btn-info">📥 Download Format</a>
                  </div>

                  <!-- Form Input Batch (Hidden by Default) -->
                  <div id="uploadSection" class="mt-3 d-none">
                    <form action="<?= base_url('Logistik/uploadExcel') ?>" class="dropzone" id="batchUpload">
                      <div class="dz-message">
                        <h6>Drag & Drop File Excel di sini</h6>
                        <p>Atau klik untuk memilih file</p>
                      </div>
                      <div id="filename-preview"></div>
                    </form>
                    <button id="submitUpload" class="btn btn-primary mt-2">🚀 Upload Sekarang</button>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
              </div>
            </div>
          </div>

          <!-- COBA PANGGIL DATA MSQL -->
          <div class="row">
            <!-- ISI -->
          </div>
        </div>
  </section>
</div>
<!-- /.content-wrapper -->


<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript"
  src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/bsCustomFileInput/bsCustomFileInput.min.js"></script>
<script>
  $('#1').datepicker({
    inputs: $('input[name=tanggal_berangkat]'),
    format: 'dd/mm/yyyy'
  })
  $('#2').datepicker({
    inputs: $('input[name=utanggal_berangkat]'),
    format: 'dd/mm/yyyy'
  })
</script>
<script>
  $(document).ready(function () {
    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    };

    var mode = 'index';
    var intersect = true;

    var $fiberstarChartBarBak = $('#fiberstar_chart_bar_bak');
    var $fiberstarChartBarSpk = $('#fiberstar_chart_bar_spk');
    var $fiberstarChartBarRfs = $('#fiberstar_chart_bar_rfs');

    const dataBarBAK = <?php echo json_encode($top_area_bak); ?>;
    const areaAchievBarBak = dataBarBAK.map(item => item.area_project);
    const hpAchievBarBak = dataBarBAK.map(item => parseInt(item.achiev_bak)); // Pastikan dalam bentuk angka

    const dataBarSPK = <?php echo json_encode($gettopAreaSPK); ?>;
    const areaAchievBarSpk = dataBarSPK.map(item => item.area_project);
    const hpAchievBarSpk = dataBarSPK.map(item => parseInt(item.achiev_spk));

    const dataBarRFS = <?php echo json_encode($top_area_rfs); ?>;
    const areaAchievBarRfs = dataBarRFS.map(item => item.area_project);
    const hpAchievBarRfs = dataBarRFS.map(item => parseInt(item.achiev_rfs));

    let currentPageBak = 0; // Halaman saat ini
    const itemsPerPageBak = 5; // Batas data per halaman
    let currentPageSpk = 0; // Halaman saat ini
    const itemsPerPageSpk = 5; // Batas data per halaman
    let currentPageRfs = 0; // Halaman saat ini
    const itemsPerPageRfs = 5; // Batas data per halaman

    let gettopAreaBAKDetail = <?php echo json_encode($gettopAreaBAKDetail); ?>;
    let gettopAreaSPKDetail = <?php echo json_encode($gettopAreaSPKDetail); ?>;
    let gettopAreaRFSDetail = <?php echo json_encode($gettopAreaRFSDetail); ?>;
    let gettopAreaATPDetail = <?php echo json_encode($gettopAreaSPKDetail); ?>;

    // Fungsi untuk memperbarui chart berdasarkan halaman
    function updateChartBAK() {
      let startBak = currentPageBak * itemsPerPageBak;
      let endBak = startBak + itemsPerPageBak;

      let paginatedLabelsBak = areaAchievBarBak.slice(startBak, endBak);
      let paginatedDataBak = hpAchievBarBak.slice(startBak, endBak);

      fiberstarChartBarBak.data.labels = paginatedLabelsBak;
      fiberstarChartBarBak.data.datasets[0].data = paginatedDataBak;
      fiberstarChartBarBak.update();

      // 🔥 Hitung total semua data (tidak terpengaruh pagination)
      let totalAchievBAK = hpAchievBarBak.reduce((total, num) => total + num, 0);

      // 🔥 Tampilkan alert dengan total keseluruhan data
      document.getElementById('jumlah_hp_done_bak').innerText = totalAchievBAK.toLocaleString('id-ID') + ' HP';

      // Perbarui tombol pagination
      updatePaginationControlsBAK();
    }

    function updateChartSPK() {
      let startSpk = currentPageSpk * itemsPerPageSpk;
      let endSpk = startSpk + itemsPerPageSpk;

      let paginatedLabelsSpk = areaAchievBarSpk.slice(startSpk, endSpk);
      let paginatedDataSpk = hpAchievBarSpk.slice(startSpk, endSpk);

      fiberstarChartBarSpk.data.labels = paginatedLabelsSpk;
      fiberstarChartBarSpk.data.datasets[0].data = paginatedDataSpk;
      fiberstarChartBarSpk.update();

      let totalAchievSPK = hpAchievBarSpk.reduce((total, num) => total + num, 0);

      // 🔥 Tampilkan alert dengan total keseluruhan data
      document.getElementById('jumlah_hp_done_spk').innerText = totalAchievSPK.toLocaleString('id-ID') + ' HP';

      // Perbarui tombol pagination
      updatePaginationControlsSPK();
    }

    function updateChartRFS() {
      let startRfs = currentPageRfs * itemsPerPageRfs;
      let endRfs = startRfs + itemsPerPageRfs;

      let paginatedLabelsRfs = areaAchievBarRfs.slice(startRfs, endRfs);
      let paginatedDataRfs = hpAchievBarRfs.slice(startRfs, endRfs);

      fiberstarChartBarRfs.data.labels = paginatedLabelsRfs;
      fiberstarChartBarRfs.data.datasets[0].data = paginatedDataRfs;
      fiberstarChartBarRfs.update();

      let totalAchievRFS = hpAchievBarRfs.reduce((total, num) => total + num, 0);

      // 🔥 Tampilkan alert dengan total keseluruhan data
      document.getElementById('jumlah_hp_done_rfs').innerText = totalAchievRFS.toLocaleString('id-ID') + ' HP';

      // Perbarui tombol pagination
      updatePaginationControlsRFS();
    }

    // Fungsi untuk memperbarui tombol pagination
    function updatePaginationControlsBAK() {
      $("#paginationControlsbak").html(`
            <button id="prevPageBak" class="btn btn-secondary btn-sm" ${currentPageBak === 0 ? 'disabled' : ''}>Previous</button>
            <span class="mx-2">Page ${currentPageBak + 1} of ${Math.ceil(areaAchievBarBak.length / itemsPerPageBak)}</span>
            <button id="nextPageBak" class="btn btn-secondary btn-sm" ${currentPageBak >= Math.ceil(areaAchievBarBak.length / itemsPerPageBak) - 1 ? 'disabled' : ''}>Next</button>
        `);

      // Event listener untuk tombol prev & next
      $("#prevPageBak").on("click", function () {
        if (currentPageBak > 0) {
          currentPageBak--;
          updateChartBAK();
        }
      });

      $("#nextPageBak").on("click", function () {
        if (currentPageBak < Math.ceil(areaAchievBarBak.length / itemsPerPageBak) - 1) {
          currentPageBak++;
          updateChartBAK();
        }
      });
    }

    function updatePaginationControlsSPK() {
      $("#paginationControlsspk").html(`
    <button id="prevPageSpk" class="btn btn-secondary btn-sm" ${currentPageSpk === 0 ? 'disabled' : ''}>Previous</button>
    <span class="mx-2">Page ${currentPageSpk + 1} of ${Math.ceil(areaAchievBarSpk.length / itemsPerPageSpk)}</span>
    <button id="nextPageSpk" class="btn btn-secondary btn-sm" ${currentPageSpk >= Math.ceil(areaAchievBarSpk.length / itemsPerPageSpk) - 1 ? 'disabled' : ''}>Next</button>
  `);

      // Event listener untuk tombol prev & next
      $("#prevPageSpk").on("click", function () {
        if (currentPageSpk > 0) {
          currentPageSpk--;
          updateChartSPK();
        }
      });

      $("#nextPageSpk").on("click", function () {
        if (currentPageSpk < Math.ceil(areaAchievBarSpk.length / itemsPerPageSpk) - 1) {
          currentPageSpk++;
          updateChartSPK();
        }
      });
    }

    function updatePaginationControlsRFS() {
      $("#paginationControlsrfs").html(`
    <button id="prevPageRfs" class="btn btn-secondary btn-sm" ${currentPageRfs === 0 ? 'disabled' : ''}>Previous</button>
    <span class="mx-2">Page ${currentPageRfs + 1} of ${Math.ceil(areaAchievBarRfs.length / itemsPerPageRfs)}</span>
    <button id="nextPageRfs" class="btn btn-secondary btn-sm" ${currentPageRfs >= Math.ceil(areaAchievBarRfs.length / itemsPerPageRfs) - 1 ? 'disabled' : ''}>Next</button>
  `);

      // Event listener untuk tombol prev & next
      $("#prevPageRfs").on("click", function () {
        if (currentPageRfs > 0) {
          currentPageRfs--;
          updateChartRFS();
        }
      });

      $("#nextPageRfs").on("click", function () {
        if (currentPageRfs < Math.ceil(areaAchievBarRfs.length / itemsPerPageRfs) - 1) {
          currentPageRfs++;
          updateChartRFS();
        }
      });
    }

    // Inisialisasi Chart.js
    let fiberstarChartBarBak = new Chart($fiberstarChartBarBak, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: []
        }]
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect
        },
        hover: {
          mode: mode,
          intersect: intersect
        },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,
              callback: function (value) {
                return `${value.toLocaleString('id-ID')} Hp`;
              }
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        },
        // Tambahkan event onClick untuk menangkap klik pada bar chart
        onClick: function (event, elements) {
          if (elements.length > 0) {
            var datasetIndex = elements[0]._datasetIndex;
            var dataIndex = elements[0]._index;

            var label = this.data.labels[dataIndex];
            var value = this.data.datasets[datasetIndex].data[dataIndex];

            alert(`Anda mengklik bar:\nLabel: ${label}\nNilai: ${value.toLocaleString('id-ID')} Hp`);
          }
        }
      }
    });

    let fiberstarChartBarSpk = new Chart($fiberstarChartBarSpk, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: []
        }]
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect
        },
        hover: {
          mode: mode,
          intersect: intersect
        },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,
              callback: function (value) {
                return `${value.toLocaleString('id-ID')} Hp`;
              }
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        },
        // Tambahkan event onClick untuk menangkap klik pada bar chart
        onClick: function (event, elements) {
          if (elements.length > 0) {
            var datasetIndex = elements[0]._datasetIndex;
            var dataIndex = elements[0]._index;

            var label = this.data.labels[dataIndex];
            var value = this.data.datasets[datasetIndex].data[dataIndex];

            alert(`Anda mengklik bar:\nLabel: ${label}\nNilai: ${value.toLocaleString('id-ID')} Hp`);
          }
        }
      }
    });

    let fiberstarChartBarRfs = new Chart($fiberstarChartBarRfs, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: []
        }]
      },
      options: {
        maintainAspectRatio: false,
        tooltips: {
          mode: mode,
          intersect: intersect
        },
        hover: {
          mode: mode,
          intersect: intersect
        },
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,
              callback: function (value) {
                return `${value.toLocaleString('id-ID')} Hp`;
              }
            }, ticksStyle)
          }],
          xAxes: [{
            display: true,
            gridLines: {
              display: false
            },
            ticks: ticksStyle
          }]
        },
        // Tambahkan event onClick untuk menangkap klik pada bar chart
        onClick: function (event, elements) {
          if (elements.length > 0) {
            var datasetIndex = elements[0]._datasetIndex;
            var dataIndex = elements[0]._index;

            var label = this.data.labels[dataIndex];
            var value = this.data.datasets[datasetIndex].data[dataIndex];

            alert(`Anda mengklik bar:\nLabel: ${label}\nNilai: ${value.toLocaleString('id-ID')} Hp`);
          }
        }
      }
    });

    // Tampilkan halaman pertama dan pagination
    let totalClusterBAK = 0;
    let totalClusterSPK = 0;
    let totalClusterRFS = 0;
    let periode_tanggal = 0;

    updateChartBAK();
    updateChartSPK();
    updateChartRFS();

    // AJAX Filter Tanggal
    $("#filter_range_tanggal").click(function () {
      var dateRange = $('#date-range').val();
      console.log($("#date-range").val());

      periode_tanggal = $("#date-range").val();

      $.ajax({
        url: "<?= base_url('Fiberstar_Project/filterTanggalChart') ?>",
        type: "POST",
        data: { date_range: dateRange },
        dataType: "json",
        success: function (response) {
          if (response.status === "success") {
            console.log("Data berhasil diterima", response);

            gettopAreaBAKDetail = response.gettopAreaBAKFilterDetail
            gettopAreaSPKDetail = response.gettopAreaSPKFilterDetail
            gettopAreaRFSDetail = response.gettopAreaRFSFilterDetail

            console.log("isi data filter", gettopAreaBAKDetail);

            // Update data chart BAK
            areaAchievBarBak.length = 0;
            hpAchievBarBak.length = 0;
            totalClusterBAK = response.bak.total_cluster_bak
              .map(Number)  // Konversi string ke number
              .reduce((total, num) => total + num, 0);


            document.getElementById('jumlah_cl_done_bak').innerText = totalClusterBAK.toLocaleString('id-ID') + ' Cluster';

            response.bak.labels.forEach((label, index) => {
              areaAchievBarBak.push(label);
              hpAchievBarBak.push(parseInt(response.bak.data[index]));
            });

            totalClusterSPK = response.spk.total_cluster_spk
              .map(Number)  // Konversi string ke number
              .reduce((total, num) => total + num, 0);

            document.getElementById('jumlah_cl_done_spk').innerText = totalClusterSPK.toLocaleString('id-ID') + ' Cluster';

            // Update data chart SPK
            areaAchievBarSpk.length = 0;
            hpAchievBarSpk.length = 0;
            response.spk.labels.forEach((label, index) => {
              areaAchievBarSpk.push(label);
              hpAchievBarSpk.push(parseInt(response.spk.data[index]));
            });

            totalClusterRFS = response.rfs.total_cluster_rfs
              .map(Number)  // Konversi string ke number
              .reduce((total, num) => total + num, 0);

            document.getElementById('jumlah_cl_done_rfs').innerText = totalClusterRFS.toLocaleString('id-ID') + ' Cluster';

            // Update data chart SPK
            areaAchievBarRfs.length = 0;
            hpAchievBarRfs.length = 0;
            response.rfs.labels.forEach((label, index) => {
              areaAchievBarRfs.push(label);
              hpAchievBarRfs.push(parseInt(response.rfs.data[index]));
            });

            // Reset halaman ke awal setelah filter
            currentPageBak = 0;
            currentPageSpk = 0;
            currentPageRfs = 0;

            updateChartBAK(); // Update chart BAK dengan data baru
            updateChartSPK(); // Update chart SPK dengan data baru
            updateChartRFS(); // Update chart SPK dengan data baru
          } else {
            alert("Data tidak ditemukan!");
          }
        },
        error: function (xhr, status, error) {
          console.error(xhr.responseText);
          alert("Terjadi kesalahan saat mengambil data.");
        }
      });
    });

    $("#lihatDetailBAK").click(function () {
      $.ajax({
        url: "<?= base_url('Fiberstar_Project/saveDetailToSession') ?>",
        type: "POST",
        data: {
          data: JSON.stringify(gettopAreaBAKDetail),
          judul: "BAK",
          periode_tanggal: periode_tanggal

        }, // Kirim data ke session
        success: function () {
          // Redirect setelah data tersimpan di session
          window.open("<?= base_url('Fiberstar_Project/FilterDetail') ?>", "_blank");
        },
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
          alert("Terjadi kesalahan saat menyimpan data ke session!");
        }
      });
    });

    $("#lihatDetailSPK").click(function () {
      $.ajax({
        url: "<?= base_url('Fiberstar_Project/saveDetailToSession') ?>",
        type: "POST",
        data: {
          data: JSON.stringify(gettopAreaSPKDetail),
          judul: "SPK",
          periode_tanggal: periode_tanggal

        }, // Kirim data ke session
        success: function () {
          // Redirect setelah data tersimpan di session
          window.open("<?= base_url('Fiberstar_Project/FilterDetail') ?>", "_blank");
        },
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
          alert("Terjadi kesalahan saat menyimpan data ke session!");
        }
      });
    });

    $("#lihatDetailRFS").click(function () {
      $.ajax({
        url: "<?= base_url('Fiberstar_Project/saveDetailToSession') ?>",
        type: "POST",
        data: {
          data: JSON.stringify(gettopAreaRFSDetail),
          judul: "RFS",
          periode_tanggal: periode_tanggal

        }, // Kirim data ke session
        success: function () {
          // Redirect setelah data tersimpan di session
          window.open("<?= base_url('Fiberstar_Project/FilterDetail') ?>", "_blank");
        },
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
          alert("Terjadi kesalahan saat menyimpan data ke session!");
        }
      });
    });

  });

</script>
<script type="text/javascript">

  //download report 


  $(function () {

    // format angka rupiah
    $('[data-mask]').inputmask("currency", {
      prefix: " Rp. ",
      digitsOptional: true
    })

    // notifikasi allert sukses atau tidak
    <?php if ($status == 'sukses_tambah') { ?>
      swal("Success!", "Berhasil menambah PO!", "success");
    <?php } else if ($status == 'sukses_hapus') { ?>
        swal("Success!", "Berhasil menghapus PO!", "success");
    <?php } else if ($status == 'PO sudah ada') { ?>
          swal("Gagal!", "PO Sudah ada", "warning");
    <?php } else if ($status == 'sukses_hapus') { ?>
            swal("Success!", "Berhasil menghapus PO!", "success");>
      <?php } else if ($status == 'gagal_hapus') { ?>
              swal("Gagal!", "Gagal menghapus PO!", "warning");>
      <?php } else { ?>
    <?php } ?>

  });

  $('.tombol_hapus').on('click', function (e) {
    e.preventDefault();
    const href = $(this).attr('href');
    swal({
      title: 'Apakah anda yakin',
      text: "data akan dihapus!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Delete'
    }).then((result) => {
      if (result.value) {
        document.location.href = href;
      }
    })

  });

  $(function () {


    const dataBarCleanlist_4 = <?php echo json_encode($stagging_area); ?>;
    const areaAchievBarCleanlist_4 = dataBarCleanlist_4.map(item => item.area_project);
    const hpAchievBarBAK_4 = dataBarCleanlist_4.map(item => item.stagging_bak);
    const hpAchievBarLLD_4 = dataBarCleanlist_4.map(item => item.stagging_lld);
    const hpAchievBarImplementasi_4 = dataBarCleanlist_4.map(item => item.stagging_implementasi);
    const hpAchievBarRFS_4 = dataBarCleanlist_4.map(item => item.stagging_rfs);
    const hpAchievBarATP_4 = dataBarCleanlist_4.map(item => item.stagging_atp);

    const originalData = {
      labels: areaAchievBarCleanlist_4, // Semua label asli
      datasets: [
        {
          label: 'BAK',
          backgroundColor: '#007bff',
          borderColor: '#007bff',
          data: hpAchievBarBAK_4
        },
        {
          label: 'LLD',
          backgroundColor: '#d2d6de',
          borderColor: '#d2d6de',
          data: hpAchievBarLLD_4
        },
        {
          label: 'IMPLEMENTASI',
          backgroundColor: '#FD7E14',
          borderColor: '#FD7E14',
          data: hpAchievBarImplementasi_4
        },
        {
          label: 'RFS',
          backgroundColor: '#6610F2',
          borderColor: '#6610F2',
          data: hpAchievBarRFS_4
        },
        {
          label: 'ATP',
          backgroundColor: '#28A745',
          borderColor: '#28A745',
          data: hpAchievBarATP_4
        }
      ]
    };

    const itemsPerPage = 10; // Tampilkan 5 data per halaman
    let currentPage = 1; // Halaman aktif
    let filterState = []; // Menyimpan state filter legend

    function getPagedData(page) {
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

    const barChartCanvas = $('#barChart').get(0).getContext('2d');
    let barChart; // Variabel untuk menyimpan instance Chart.js

    function renderChart(page) {
      const pagedData = getPagedData(page);

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

                // Simpan status filter ke filterState
                filterState[datasetIndex] = meta.hidden;

                chart.update(); // Update chart setelah perubahan
              }
            }
          }
        }
      });

      // Terapkan filter yang disimpan
      applyFilterState(barChart);
    }

    function applyFilterState(chart) {
      if (filterState.length > 0) {
        chart.data.datasets.forEach((dataset, index) => {
          const meta = chart.getDatasetMeta(index);
          meta.hidden = filterState[index] || null;
        });
        chart.update();
      }
    }

    function createPaginationControls(totalPages) {
      const paginationContainer = $('#paginationControls');
      paginationContainer.empty(); // Hapus tombol lama

      // Tombol Previous
      const prevButton = $(`<button class="btn btn-sm btn-secondary m-1">Previous</button>`);
      prevButton.prop('disabled', currentPage === 1);
      prevButton.on('click', function () {
        if (currentPage > 1) {
          currentPage--;
          renderChart(currentPage);
          highlightActivePage(totalPages);
        }
      });
      paginationContainer.append(prevButton);

      // Tombol untuk setiap halaman
      for (let i = 1; i <= totalPages; i++) {
        const button = $(`<button class="btn btn-sm btn-primary m-1">${i}</button>`);
        if (i === currentPage) {
          button.addClass('active'); // Tambahkan class aktif pada halaman saat ini
        }
        button.on('click', function () {
          currentPage = i;
          renderChart(currentPage); // Render chart untuk halaman baru
          highlightActivePage(totalPages);
        });
        paginationContainer.append(button);
      }

      // Tombol Next
      const nextButton = $(`<button class="btn btn-sm btn-secondary m-1">Next</button>`);
      nextButton.prop('disabled', currentPage === totalPages);
      nextButton.on('click', function () {
        if (currentPage < totalPages) {
          currentPage++;
          renderChart(currentPage);
          highlightActivePage(totalPages);
        }
      });
      paginationContainer.append(nextButton);
    }

    function highlightActivePage(totalPages) {
      const paginationContainer = $('#paginationControls');
      paginationContainer.find('button').removeClass('active'); // Hapus highlight dari semua tombol

      // Highlight tombol aktif
      paginationContainer
        .find('button')
        .filter(function () {
          return $(this).text() == currentPage || $(this).text() == "Next" || $(this).text() == "Previous";
        })
        .addClass('active');

      // Perbarui tombol Previous dan Next
      paginationContainer.find('button:contains("Previous")').prop('disabled', currentPage === 1);
      paginationContainer.find('button:contains("Next")').prop('disabled', currentPage === totalPages);
    }

    const totalPages = Math.ceil(originalData.labels.length / itemsPerPage);

    // Inisialisasi
    renderChart(currentPage);
    createPaginationControls(totalPages);


    //BAR BIASA TANPA SELECTED

    'use strict'

  })

  $(document).ready(function () {

    // Format mata uang.
    $('.nilai_po2').mask('000.000.000', { reverse: true });

  })

  $(document).ready(function () {
    $('.card[data-card-widget="collapse"]').addClass('card-tools');
  });

  document.getElementById('reset_filter').addEventListener('click', function () {
    const selectRegional = document.getElementById('filter_regional');
    const selectPic = document.getElementById('filter_pic');
    const selectArea = document.getElementById('filter_area');
    const selectStagging = document.getElementById('filter_stagging');

    const optionsRegional = selectRegional.options;
    const optionsPic = selectPic.options;
    const optionsArea = selectArea.options;
    const optionsStagging = selectStagging.options;

    // Hapus semua pilihan
    for (let i = 0; i < optionsRegional.length; i++) {
      optionsRegional[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsPic.length; i++) {
      optionsPic[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsArea.length; i++) {
      optionsArea[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsStagging.length; i++) {
      optionsStagging[i].selected = false; // Hilangkan pilihan
    }

    // Pilih opsi default (indeks 0)
    selectRegional.dispatchEvent(new Event('change'));
    selectPic.dispatchEvent(new Event('change'));
    selectArea.dispatchEvent(new Event('change'));
    selectStagging.dispatchEvent(new Event('change'));
  });


  // JAVA SCRIPT UNTUK MENUTUP SEMUA COLLAPSE CARD
  // document.addEventListener("DOMContentLoaded", function () {
  //   const cards = document.querySelectorAll('[data-card-widget="collapse"]');
  //   cards.forEach(card => {
  //     const parentCard = card.closest('.card');
  //     if (parentCard) {
  //       parentCard.classList.add('collapsed-card'); // Tambahkan kelas 'collapsed-card'
  //     }
  //   });
  // });
  // ENDING UNTUK MENUTUP SEMUA COLLAPSE CARD

  // JAVA SCRIPT UNTUK MENUTUP COLLAPSE CARD BY ID
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
  // ENDING UNTUK MENUTUP COLLAPSE CARD BY ID

  $(document).ready(function () {
    $.fn.dataTable.ext.errMode = 'none';
    $('#table_data').DataTable({
      responsive: false // Matikan fitur Responsive
    });
  });

  $(document).ready(function () {
    $('#table_detail_area').DataTable({
      "paging": true,          // Tetap gunakan pagination
      "pageLength": 10,        // Menampilkan 10 data per halaman
      "info": false,           // Menghilangkan "Showing 1 to X of X entries"
      "searching": true,      // Menghilangkan search bar
      "lengthChange": false    // Menghilangkan dropdown "Show entries"
    });
  });


  $(document).ready(function () {
    const table = $('#table_data').DataTable({
      footerCallback: function () {
        updateTotal();
      }
    });

    // Fungsi untuk menghitung total dari data yang tampil
    function updateTotal() {
      // Ambil semua data yang terlihat

      const data = table.rows({ search: 'applied' }).data();

      // Hitung total dari kolom Value (index 2)
      let totalHP = 0;
      let totalPO = 0;
      let totalDoneInvoice = 0;
      let totalSisaInvoice = 0;

      let totalHpCanvasing = 0;
      let totalHpBAK = 0;
      let totalHpSPK = 0;
      let totalHpHLD = 0;
      let totalHpLLD = 0;
      let totalHpKOM = 0;
      let totalHpRFS = 0;
      let totalHpATP = 0;
      let totalHpClosed = 0;

      data.each(function (row) {
        totalHP += parseFloat(row[6].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalPO += parseFloat(row[9].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalDoneInvoice += parseFloat(row[28].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value
        totalSisaInvoice += parseFloat(row[29].replace(/\./g, '')) || 0; // Index 2 adalah kolom Value

        if (row['10'] != 0) {
          totalHpCanvasing += parseFloat(row[6].replace(/\./g, ''))
        } if (row['11'] == "OK") {
          totalHpBAK += parseFloat(row[12].replace(/\./g, ''))
        } if (row['14'] != 0) {
          totalHpSPK += parseFloat(row[15].replace(/\./g, ''))
        } if (row['16'] == "OK") {
          totalHpHLD += parseFloat(row[17].replace(/\./g, ''))
        } if (row['18'] == "OK") {
          totalHpLLD += parseFloat(row[19].replace(/\./g, ''))
        } if (row['20'] != 0) {
          totalHpKOM += parseFloat(row[19].replace(/\./g, ''))
        } if (row['23'] != 0) {
          totalHpRFS += parseFloat(row[24].replace(/\./g, ''))
        } if (row['25'] != 0) {
          totalHpATP += parseFloat(row[26].replace(/\./g, ''))
        } if (row['27'].includes("close")) {
          totalHpClosed += parseFloat(row[26].replace(/\./g, ''))
        }
      });

      // Update elemen Total
      $('#totalHP').text(totalHP.toLocaleString('id-ID'));
      $('#totalPO').text(totalPO.toLocaleString('id-ID'));
      $('#totalDoneInvoice').text(totalDoneInvoice.toLocaleString('id-ID'));
      $('#totalSisaInvoice').text(totalSisaInvoice.toLocaleString('id-ID'));

      document.getElementById('idtotalDonePO').innerText = totalPO.toLocaleString('id-ID') + ' IDR';
      document.getElementById('idtotalDoneInvoice').innerText = totalDoneInvoice.toLocaleString('id-ID') + ' IDR';
      document.getElementById('idtotalSisaInvoice').innerText = totalSisaInvoice.toLocaleString('id-ID') + ' IDR';

      document.getElementById('idtotal_hp_plan').innerText = totalHP.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_canvasing').innerText = totalHpCanvasing.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_bak').innerText = totalHpBAK.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_spk').innerText = totalHpSPK.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_hld').innerText = totalHpHLD.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_lld').innerText = totalHpLLD.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_kom').innerText = totalHpKOM.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_rfs').innerText = totalHpRFS.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_atp').innerText = totalHpATP.toLocaleString('id-ID') + ' HP';
      document.getElementById('idtotal_hp_closed').innerText = totalHpClosed.toLocaleString('id-ID') + ' HP';
    }

    // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
    table.on('draw', function () {
      updateTotal();
    });

    // Hitung total pertama kali saat tabel dimuat
    updateTotal();
  });

  $(document).ready(function () {
    // Inisialisasi DataTable
    var table = $('#table_data').DataTable();

    // Event saat tombol "Cari" diklik
    $('#btnFilterDataProject').on('click', function () {
      // Ambil nilai dari multiple select filter kategori
      var selectedRegional = $('#filter_regional').val() || []; // Array of selected values
      var selectedPIC = $('#filter_pic').val() || []; // Array of selected values
      var selectedArea = $('#filter_area').val() || []; // Array of selected values
      var selectedStagging = $('#filter_stagging').val() || []; // Array of selected values

      // Gabungkan nilai ke dalam regex untuk pencarian DataTable
      var regionalFilter = selectedRegional.length > 0 ? selectedRegional.join('|') : '';
      var picFilter = selectedPIC.length > 0 ? selectedPIC.join('|') : '';
      var areaFilter = selectedArea.length > 0 ? selectedArea.join('|') : '';
      var staggingFilter = selectedStagging.length > 0 ? selectedStagging.join('|') : '';

      // Terapkan filter ke DataTable
      table
        .column(1).search(regionalFilter, true, false) // Filter kategori (regex search)
        .column(3).search(picFilter, true, false) // Filter kategori (regex search)
        .column(2).search(areaFilter, true, false) // Filter kategori (regex search)
        .column(27).search(staggingFilter, true, false) // Filter kategori (regex search)
        .draw(); // Render ulang tabel

    });
  });

  document.getElementById("inputBatch").addEventListener("click", function () {
    document.getElementById("uploadSection").classList.remove("d-none");
  });

  Dropzone.options.batchUpload = {
    maxFilesize: 5, // Maksimal 5MB
    acceptedFiles: ".xls,.xlsx",
    previewsContainer: "#filename-preview", // Menentukan tempat tampilan nama file
    clickable: true,
    autoProcessQueue: false, // Hindari auto-upload sebelum submit
    init: function () {
      let myDropzone = this;

      this.on("addedfile", function (file) {
        // Hapus preview bawaan Dropzone
        setTimeout(() => {
          if (file.previewElement) {
            file.previewElement.remove();
          }
        }, 100);

        // Tampilkan nama file yang dipilih
        document.getElementById("filename-preview").innerHTML =
          "<p class='text-success font-weight-bold'>File diunggah: " + file.name + "</p>";
      });

      this.on("success", function (file, response) {
        alert("Upload Berhasil! " + response);
        location.reload();
      });

      this.on("error", function (file, errorMessage) {
        alert("Upload Gagal: " + errorMessage);
      });

      // Tombol Submit untuk Proses Upload
      document.getElementById("submitUpload").addEventListener("click", function () {
        myDropzone.processQueue();
      });
    }
  };

</script>


<script>
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
            let selectedPIC = $('#filter_pic').val() || [];
            let selectedArea = $('#filter_area').val() || [];
            let selectedStagging = $('#filter_stagging').val() || [];

            // Filter berdasarkan pilihan
            let filteredData = progressData.filter(item =>
                (selectedRegional.length === 0 || selectedRegional.includes(item.regional_project)) &&
                (selectedPIC.length === 0 || selectedPIC.includes(item.pic_project)) &&
                (selectedArea.length === 0 || selectedArea.includes(item.area_project)) &&
                (selectedStagging.length === 0 || selectedStagging.includes(item.main_status))
            );

            let uniqueRegional = [...new Set(filteredData.map(item => item.regional_project))];
            let uniquePIC = [...new Set(filteredData.map(item => item.pic_project))];
            let uniqueArea = [...new Set(filteredData.map(item => item.area_project))];
            let uniqueStagging = [...new Set(filteredData.map(item => item.main_status))];

            if (changedFilter !== '#filter_regional') populateDropdown('#filter_regional', uniqueRegional, selectedRegional);
            if (changedFilter !== '#filter_pic') populateDropdown('#filter_pic', uniquePIC, selectedPIC);
            if (changedFilter !== '#filter_area') populateDropdown('#filter_area', uniqueArea, selectedArea);
            if (changedFilter !== '#filter_stagging') populateDropdown('#filter_stagging', uniqueStagging, selectedStagging);
        }, 50); // Delay kecil untuk mencegah UI freeze
    }

    // Menggunakan passive: true untuk meningkatkan performa
    document.addEventListener('scroll', function () {}, { passive: true });
    document.addEventListener('touchstart', function () {}, { passive: true });
    document.addEventListener('wheel', function () {}, { passive: true });

    $('#filter_regional, #filter_pic, #filter_area, #filter_stagging').on('change', function () {
        let changedFilter = `#${$(this).attr('id')}`;
        filterData(changedFilter);
    });

    // Inisialisasi dropdown pertama kali
    let uniqueRegional = [...new Set(progressData.map(item => item.regional_project))];
    let uniquePIC = [...new Set(progressData.map(item => item.pic_project))];
    let uniqueArea = [...new Set(progressData.map(item => item.area_project))];
    let uniqueStagging = [...new Set(progressData.map(item => item.main_status))];

    populateDropdown('#filter_regional', uniqueRegional);
    populateDropdown('#filter_pic', uniquePIC);
    populateDropdown('#filter_area', uniqueArea);
    populateDropdown('#filter_stagging', uniqueStagging);

    $('.select2').select2();
});
</script>







<script>
  // Membuat circle progress bar
  var bar = new ProgressBar.Circle('#progress-bar-container', {
    color: '#FF5733', // Warna progress bar
    strokeWidth: 10, // Ketebalan garis
    trailWidth: 10,  // Ketebalan garis latar belakang
    easing: 'easeInOut',  // Animasi progress bar
    duration: 1400,  // Durasi animasi dalam milidetik
    from: { color: '#ddd', width: 10 },
    to: { color: '#FF5733', width: 10 },
    step: function (state, circle) {
      circle.path.setAttribute('stroke', state.color);
      circle.path.setAttribute('stroke-width', state.width);
      var value = Math.round(circle.value() * 100);
      circle.setText(value + '%');
    }
  });

  // Mengatur nilai progress bar
  bar.animate(<?= $persentase_po ?>);  // Nilai antara 0.0 hingga 1.0 (70% dalam contoh ini)
</script>
<script>
  $(function () {
    bsCustomFileInput.init();
  });
</script>
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  })
  // DropzoneJS Demo Code End
</script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>



<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
<!-- Google Font: Source Sans Pro -->
<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- daterange picker -->
<!-- <link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js">
</script>
<!-- jQuery UI 1.11.4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-ui/jquery-ui.min.js">
</script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js">
<!-- ChartJS -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js">
<!-- Sparkline -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/sparklines/sparkline.js">
<!-- JQVMap -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/jquery.vmap.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jqvmap/maps/jquery.vmap.usa.js">
<!-- jQuery Knob Chart -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/jquery-knob/jquery.knob.min.js">
<!-- daterangepicker -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/moment/moment.min.js">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet"
  href="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js">
<!-- Summernote -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/summernote/summernote-bs4.min.js">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js">
<!-- AdminLTE for demo purposes -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/demo.js">
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/js/pages/dashboard.js">

<script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- OPTIONAL SCRIPTS -->
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?= base_url('assets') ?>/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardlistarea.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

<!-- <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartfibertstar.js"></script> -->
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardchartmyrep.js"></script>
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardrkap.js"></script>