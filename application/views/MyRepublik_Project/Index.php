<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$nilai_po = 0;
$rkap = 345000000000;
$persentase_po = 0;
$nilai_invoice = 0;
$sisa_invoice = 0;
$total = 1;
$total_nilai_invoice = 0;
$total_sisa_invoice = 0;

$persentase_plan = 0;
$persentase_achiev = 0;
$persentase_total = 0;

$persentase_cleanlist_to_total = 0;
$persentase_bak_to_cleanlist = 0;

$total_hp_plan_regional = 0;
$total_hp_canvasing_regional = 0;
$total_hp_bak_regional = 0;
$total_hp_spk_regional = 0;
$total_hp_hld_regional = 0;
$total_hp_lld_regional = 0;
$total_hp_kom_regional = 0;
$total_hp_pks_regional = 0;
$total_hp_rfs_regional = 0;
$total_hp_atp_regional = 0;
$total_hp_closed_regional = 0;


?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
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
                      <label style="display: flex; justify-content: center; align-items: center;">TIPE PROJECT</label>
                      <select id="filter_typeproject" class="select2" multiple="multiple"
                        data-placeholder="Pilih Tipe Project" style="width: 100%;">
                        <?php foreach ($unique_type_project as $data): ?>
                          <option value="<?php echo $data['type_project'] ?>"> <?php echo $data['type_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">PROVINSI</label>
                      <select id="filter_provinsi" class="select2" multiple="multiple" data-placeholder="Pilih Regional"
                        style="width: 100%;">
                        <?php foreach ($unique_provinsi as $data): ?>
                          <option value="<?php echo $data['provinsi_project'] ?>"> <?php echo $data['provinsi_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">AREA</label>
                      <select id="filter_kota" class="select2" multiple="multiple" data-placeholder="Pilih Area"
                        style="width: 100%;">
                        <?php foreach ($unique_area as $data): ?>
                          <option value="<?php echo $data['kota_project'] ?>"> <?php echo $data['kota_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">Status TKM</label>
                      <select id="filter_status_project_tkm" class="select2" multiple="multiple"
                        data-placeholder="Pilih Status TKM" style="width: 100%;">
                        <?php foreach ($status_project_tkm as $data): ?>
                          <option value="<?php echo $data['status_project_tkm'] ?>">
                            <?php echo $data['status_project_tkm'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form_group">
                      <label style="display: flex; justify-content: center; align-items: center;">RANGE TANGGAL</label>
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
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>

            <div class="card-body" style="margin-top:10px;">
              <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Pengeluaran") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                            class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">TARGET RKAP 2025</span>
                          <span class="info-box-number">
                            345,000,000,000 IDR
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
                          <span class="info-box-text">ACHIEVE PO 2025</span>
                          <span class="info-box-number" id="idtotalDonePO">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['nilai_awal_po']), 0, ".") . " IDR" ?>
                            <?php endforeach ?>
                          </span>
                        </div>
                      </div>
                    </a>
                    <!-- /.info-box -->
                  </div>

                  <div class="col-12 col-sm-6 col-md-3">
                    <a href="<?= base_url("Laporan") ?>">
                      <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">ACHIEVE INVOICE 2025</span>
                          <span class="info-box-number" id="idtotalDoneInvoice">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['total_invoice']), 0, ".") . " IDR" ?>
                            <?php endforeach ?>
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
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">SISA INVOICE 2025</span>
                          <span class="info-box-number" id="idtotalSisaInvoice">
                            <?php foreach ($data_invoice as $dataInvoice): ?>
                              <?= number_format(floatval($dataInvoice['total_sisa_invoice']), 0, ".") . " IDR" ?>
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
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
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
                            <?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ".") . " HP" ?>
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
                            <?= number_format(floatval($totalHpPlan['total_hp_bak']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>DONE BAK</p>
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
                            <?= number_format(floatval($totalHpPlan['total_hp_snd']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>DONE SND</p>
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
                            <?= number_format(floatval($totalHpPlan['total_hp_drm']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>DONE DRM</p>
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
                            <?= number_format(floatval($totalHpPlan['total_hp_rfs']), 0, ".") . " HP" ?>
                          </h3>
                        <?php endforeach ?>

                        <p>DONE RFS</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
                  </div>

                </div>
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
                          <table id="table_detail" class="table table-bordered table-hover">
                            <thead class="bg-info">
                              <tr>
                                <th>No</th>
                                <th>Regional Project</th>
                                <th>HP Plan</th>
                                <th>Canvasing</th>
                                <th>DONE BAK</th>
                                <th>DONE SPK</th>
                                <th>DONE HLD</th>
                                <th>DONE LLD</th>
                                <th>DONE KOM</th>
                                <th>DONE PKS</th>
                                <th>DONE RFS</th>
                                <th>DONE</th>
                                <th>CLOSED</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                              $total = 1;

                              foreach ($stagging_regional as $data):

                                $total_hp_plan_regional += $data['total_hp_plan'];
                                $total_hp_canvasing_regional += $data['total_hp_canvasing'];
                                $total_hp_bak_regional += $data['total_hp_bak'];
                                $total_hp_spk_regional += $data['total_hp_spk'];
                                $total_hp_hld_regional += $data['total_hp_hld'];
                                $total_hp_lld_regional += $data['total_hp_lld'];
                                $total_hp_kom_regional += $data['total_hp_kom'];
                                $total_hp_pks_regional += $data['total_hp_pks'];
                                $total_hp_rfs_regional += $data['total_hp_rfs'];
                                $total_hp_atp_regional += $data['total_hp_atp'];
                                $total_hp_closed_regional += $data['total_hp_closed'];

                                ?>

                                <tr>
                                  <td><?= $total++ ?></td>
                                  <td><?= $data['regional_project'] ?></td>
                                  <td><?= number_format(floatval($data['total_hp_plan']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_canvasing']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_bak']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_spk']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_hld']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_lld']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_kom']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_pks']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_rfs']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_atp']), 0, ".") ?></td>
                                  <td><?= number_format(floatval($data['total_hp_closed']), 0, ".") ?></td>
                                </tr>

                              <?php endforeach; ?>

                            </tbody>
                            <tfoot>
                              <tr>
                                <th colspan="2">Total</th>
                                <th colspan="1"><?= number_format(floatval($total_hp_plan_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_canvasing_regional), 0, ".") ?>
                                </th>
                                <th colspan="1"><?= number_format(floatval($total_hp_bak_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_spk_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_hld_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_lld_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_kom_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_pks_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_rfs_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_atp_regional), 0, ".") ?></th>
                                <th colspan="1"><?= number_format(floatval($total_hp_closed_regional), 0, ".") ?></th>
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

    <?php

    //  $persentase_cleanlist_to_total = ($total_hp_plan_regional / $target_cleanlist_rkap) * 100;
    //  $persentase_bak_to_cleanlist = ($total_hp_bak_regional / $total_hp_plan_regional) * 100;
    
    ?>

    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title">Top Area Cleanlist Cluster</h3>
                <a href="javascript:void(0);">View Report</a>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                  <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                    <span
                      class="text-bold text-lg"><?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ".") . " HP" ?></span>
                  <?php endforeach ?>
                  <span>TOP AREA</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> <?= round($persentase_cleanlist_to_total, 2) . "%" ?>
                  </span>
                  <span class="text-muted">By RKAP ( % )</span>
                </p>
              </div>
              <!-- /.d-flex -->

              <div class="position-relative mb-4">
                <canvas id="fiberstar_chart_bar_cleanlist" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary"></i> Achieved
                </span>
              </div>
            </div>
          </div>

        </div>



        <div class="col-lg-6">
          <div class="card">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title">Top Area BAK</h3>
                <a href="javascript:void(0);">View Report</a>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                  <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                    <span
                      class="text-bold text-lg"><?= number_format(floatval($totalHpPlan['total_hp_bak']), 0, ".") . " HP" ?></span>
                  <?php endforeach ?>
                  <span>TOP AREA</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> <?= round($persentase_bak_to_cleanlist, 2) . "%" ?>
                  </span>
                  <span class="text-muted">By Cleanlist ( % )</span>
                </p>
              </div>
              <!-- /.d-flex -->

              <div class="position-relative mb-4">
                <canvas id="fiberstar_chart_bar_bak" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary"></i> Achieved
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-12">
          <div class="card">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title">Top Area RFS</h3>
                <a href="javascript:void(0);">View Report</a>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                  <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                    <span
                      class="text-bold text-lg"><?= number_format(floatval($totalHpPlan['total_hp_rfs']), 0, ".") . " HP" ?></span>
                  <?php endforeach ?>
                  <span>TOP AREA</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> <?= round($persentase_bak_to_cleanlist, 2) . "%" ?>
                  </span>
                  <span class="text-muted">By Cleanlist ( % )</span>
                </p>
              </div>
              <!-- /.d-flex -->

              <div class="position-relative mb-4">
                <canvas id="fiberstar_chart_bar_cleanlist_2" height="200"></canvas>
              </div>

              <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary"></i> Achieved
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- /.col-md-6 -->
      </div>
    </div>
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
              <div class="row">
                <div class="col-6">
                  <h3 class="card-title">List Cleanlist Cluster</h3>
                </div>
                <div class="col-6">
                  <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah-manual"
                    data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
                </div>
              </div>

            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive text-nowrap">
              <table id="table_data_myrep" class="table table-bordered table-hover">
                <thead class="thead-dark">
                  <tr>
                    <th>No</th> <!-- 0 -->
                    <th>Type Project</th> <!-- 1 -->
                    <th>Provinsi</th> <!-- 2 -->
                    <th>Area</th> <!-- 3 -->
                    <th>Cluster Name</th> <!-- 4 -->
                    <th>Cluster Code</th> <!-- 6 -->
                    <th>NTP Name</th> <!-- 5 -->
                    <th>HP Plan</th> <!-- 9 -->
                    <th>Panjang Plan</th> <!-- 9 -->
                    <th>Status Project EMR</th> <!-- 7 -->
                    <th>Status Project TKM</th> <!-- 8 -->
                    <th>Status ATP</th> <!-- 10 -->
                    <th>Action</th> <!-- 32 -->
                  </tr>
                </thead>
                <tbody>
                  <?php

                  $total = 1;
                  foreach ($progress_implementasi as $data):

                    ?>

                    <tr>
                      <td class="align-middle text-center"><?= $total++ ?></td>
                      <td class="align-middle"><?= $data['type_project'] ?></td>
                      <td class="align-middle"><?= $data['provinsi_project'] ?></td>
                      <td class="align-middle"><?= $data['kota_project'] ?></td>
                      <td class="align-middle"><?= $data['cluster_name'] ?></td>
                      <td class="align-middle"><?= $data['cluster_code'] ?></td>
                      <td class="align-middle"><?= $data['ntp_name'] ?></td>
                      <td class="align-middle text-center"><?= number_format(floatval($data['hp_plan']), 0, ".") ?></td>
                      <td class="align-middle text-center"><?= number_format(floatval($data['panjang_plan']), 0, ".") ?>
                      </td>
                      <td class="align-middle text-center"><?= $data['status_project_emr'] ?></td>
                      <td class="align-middle text-center"><?= $data['status_project_tkm'] ?></td>
                      <td class="align-middle text-center"><?= $data['status_atp'] ?></td>
                      <td>
                        <a href="<?php echo site_url('Fiberstar_Project_Detail/detailImplementasi/' . $data['primary_access_id_project']); ?>"
                          id="tombol_detail" class="btn btn-primary tombol_detail"><i class=" fas fa-share"></i></a>

                      </td>
                    </tr>

                  <?php endforeach; ?>

                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="7">Total</th>
                    <th colspan="1"><span id="totalHPPlan">0</span></th>
                    <th colspan="1"><span id="totalPanjangPlan">0</span></th>
                    <th colspan="1"></th>
                    <th colspan="1"></th>
                    <th colspan="1"></th>
                    <th colspan="1"></th>
                    <!--  27 -->
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

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
<script type="text/javascript">
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

  $(document).ready(function () {

    // Format mata uang.
    $('.nilai_po2').mask('000.000.000', { reverse: true });

  })

  $(document).ready(function () {
    $('.card[data-card-widget="collapse"]').addClass('card-tools');
  });

  document.getElementById('reset_filter').addEventListener('click', function () {
    const selectTypeProject = document.getElementById('filter_typeproject');
    const selectProvinsi = document.getElementById('filter_provinsi');
    const selectKota = document.getElementById('filter_kota');
    const selectStatusProjectTKM = document.getElementById('filter_status_project_tkm');

    const optionsTypeProject = selectTypeProject.options;
    const optionsProvinsi = selectProvinsi.options;
    const optionsKota = selectKota.options;
    const optionsStatusProjectTKM = selectStatusProjectTKM.options;

    // Hapus semua pilihan
    for (let i = 0; i < optionsTypeProject.length; i++) {
      optionsTypeProject[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsProvinsi.length; i++) {
      optionsProvinsi[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsKota.length; i++) {
      optionsKota[i].selected = false; // Hilangkan pilihan
    }

    for (let i = 0; i < optionsStatusProjectTKM.length; i++) {
      optionsStatusProjectTKM[i].selected = false; // Hilangkan pilihan
    }

    // Pilih opsi default (indeks 0)
    selectTypeProject.dispatchEvent(new Event('change'));
    selectProvinsi.dispatchEvent(new Event('change'));
    selectKota.dispatchEvent(new Event('change'));
    selectStatusProjectTKM.dispatchEvent(new Event('change'));
  });

  $(function () {
    'use strict'

    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    }

    var mode = 'index'
    var intersect = true

    var $fiberstarChartBarBak = $('#fiberstar_chart_bar_bak')

    const dataBar = <?php echo json_encode($top_area_bak); ?>;
    const areaAchievBar = dataBar.map(item => item.kota_project);
    const hpAchievBar = dataBar.map(item => item.achiev_bak);


    // eslint-disable-next-line no-unused-vars
    var fiberstarChartBarBak = new Chart($fiberstarChartBarBak, {
      type: 'bar',
      data: {
        labels: areaAchievBar,
        datasets: [
          {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: hpAchievBar
          }
        ]
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
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,

              // Include a dollar sign in the ticks
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
        }
      }
    })

    var $fiberstarChartBarCleanlist = $('#fiberstar_chart_bar_cleanlist')

    const dataBarCleanlist = <?php echo json_encode($top_area_cleanlist); ?>;
    const areaAchievBarCleanlist = dataBarCleanlist.map(item => item.kota_project);
    const hpAchievBarCleanlist = dataBarCleanlist.map(item => item.achiev_cleanlist);


    var fiberstarChartBarCleanlist = new Chart($fiberstarChartBarCleanlist, {
      type: 'bar',
      data: {
        labels: areaAchievBarCleanlist,
        datasets: [
          {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: hpAchievBarCleanlist
          }
        ]
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
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,

              // Include a dollar sign in the ticks
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
        }
      }
    })

    var $fiberstarChartBarCleanlist_2 = $('#fiberstar_chart_bar_cleanlist_2')

    const dataBarCleanlist_2 = <?php echo json_encode($top_area_rfs); ?>;
    const areaAchievBarCleanlist_2 = dataBarCleanlist_2.map(item => item.kota_project);
    const hpAchievBarCleanlist_2 = dataBarCleanlist_2.map(item => item.achiev_rfs);


    var fiberstarChartBarCleanlist_2 = new Chart($fiberstarChartBarCleanlist_2, {
      type: 'bar',
      data: {
        labels: areaAchievBarCleanlist_2,
        datasets: [
          {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: hpAchievBarCleanlist_2
          }
        ]
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
            // display: false,
            gridLines: {
              display: true,
              lineWidth: '4px',
              color: 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks: $.extend({
              beginAtZero: true,

              // Include a dollar sign in the ticks
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
        }
      }
    })

  })

  document.addEventListener("DOMContentLoaded", function () {
    const cards = document.querySelectorAll('[data-card-widget="collapse"]');
    cards.forEach(card => {
      const parentCard = card.closest('.card');
      if (parentCard) {
        parentCard.classList.add('collapsed-card'); // Tambahkan kelas 'collapsed-card'
      }
    });
  });

  $(document).ready(function () {
    $('#table_data_myrep').DataTable({
      responsive: false, // Matikan fitur Responsive
    });
  });

  $(document).ready(function () {
    const table = $('#table_data_myrep').DataTable({
      footerCallback: function () {
        updateTotal();
      }
    });

    // Fungsi untuk menghitung total dari data yang tampil
    function updateTotal() {
      // Ambil semua data yang terlihat

      const data = table.rows({ search: 'applied' }).data();

      // Hitung total dari kolom Value (index 2)
      let totalHPPlan = 0;
      let totalPanjangPlan = 0;
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
        totalHPPlan += parseFloat(row[7].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        totalPanjangPlan += parseFloat(row[8].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        // totalPO += parseFloat(row[9].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        // totalDoneInvoice += parseFloat(row[28].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        // totalSisaInvoice += parseFloat(row[29].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value

        // if (row['10'] != 0) {
        //   totalHpCanvasing += parseFloat(row[6].replace(/,/g, ''))
        // } if (row['11'] == "OK") {
        //   totalHpBAK += parseFloat(row[12].replace(/,/g, ''))
        // } if (row['14'] != 0) {
        //   totalHpSPK += parseFloat(row[15].replace(/,/g, ''))
        // } if (row['16'] == "OK") {
        //   totalHpHLD += parseFloat(row[17].replace(/,/g, ''))
        // } if (row['18'] == "OK") {
        //   totalHpLLD += parseFloat(row[19].replace(/,/g, ''))
        // } if (row['20'] != 0) {
        //   totalHpKOM += parseFloat(row[19].replace(/,/g, ''))
        // } if (row['23'] != 0) {
        //   totalHpRFS += parseFloat(row[24].replace(/,/g, ''))
        // } if (row['25'] != 0) {
        //   totalHpATP += parseFloat(row[26].replace(/,/g, ''))
        // } if (row['27'].includes("close")) {
        //   totalHpClosed += parseFloat(row[26].replace(/,/g, ''))
        // }
      });

      // Update elemen Total
      $('#totalHPPlan').text(totalHPPlan.toLocaleString('id-ID'));
      $('#totalPanjangPlan').text(totalPanjangPlan.toLocaleString('id-ID'));
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
    var table = $('#table_data_myrep').DataTable();

    // Event saat tombol "Cari" diklik
    $('#btnFilterDataProject').on('click', function () {
      // Ambil nilai dari multiple select filter kategori
      var selectedTypeProject = $('#filter_typeproject').val() || []; // Array of selected values
      var selectedProvinsi = $('#filter_provinsi').val() || []; // Array of selected values
      var selectedKota = $('#filter_kota').val() || []; // Array of selected values
      var selectedStatusProjectTKM = $('#filter_status_project_tkm').val() || []; // Array of selected values

      // Gabungkan nilai ke dalam regex untuk pencarian DataTable
      var typeProjectFilter = selectedTypeProject.length > 0 ? selectedTypeProject.join('|') : '';
      var ProvinsiFilter = selectedProvinsi.length > 0 ? selectedProvinsi.join('|') : '';
      var KotaFilter = selectedKota.length > 0 ? selectedKota.join('|') : '';
      var StatusProjectTKMFilter = selectedStatusProjectTKM.length > 0 ? selectedStatusProjectTKM.join('|') : '';

      // Terapkan filter ke DataTable
      table
        .column(1).search(typeProjectFilter, true, false) // Filter kategori (regex search)
        .column(2).search(ProvinsiFilter, true, false) // Filter kategori (regex search)
        .column(3).search(KotaFilter, true, false) // Filter kategori (regex search)
        .column(9).search(StatusProjectTKMFilter, true, false) // Filter kategori (regex search)
        .draw(); // Render ulang tabel

    });

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

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date picker
    $('#reservationdate').datetimepicker({
      format: 'L'
    });

    //Date and time picker
    $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'MM/DD/YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
      {
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
      },
      function (start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
      }
    )

    //Timepicker
    $('#timepicker').datetimepicker({
      format: 'LT'
    })

    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    $('.my-colorpicker2').on('colorpickerChange', function (event) {
      $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
    })

    $("input[data-bootstrap-switch]").each(function () {
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    })

  })
  // BS-Stepper Init
  document.addEventListener('DOMContentLoaded', function () {
    window.stepper = new Stepper(document.querySelector('.bs-stepper'))
  })

  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false

  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template")
  previewNode.id = ""
  var previewTemplate = previewNode.parentNode.innerHTML
  previewNode.parentNode.removeChild(previewNode)

  var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
    url: "/target-url", // Set the url
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  })

  myDropzone.on("addedfile", function (file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function () { myDropzone.enqueueFile(file) }
  })

  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function (progress) {
    document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
  })

  myDropzone.on("sending", function (file) {
    // Show the total progress bar when upload starts
    document.querySelector("#total-progress").style.opacity = "1"
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
  })

  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function (progress) {
    document.querySelector("#total-progress").style.opacity = "0"
  })

  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function () {
    myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
  }
  document.querySelector("#actions .cancel").onclick = function () {
    myDropzone.removeAllFiles(true)
  }
  // DropzoneJS Demo Code End
</script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

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