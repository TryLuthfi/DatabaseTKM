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
                      <label style="display: flex; justify-content: center; align-items: center;">REGIONAL</label>
                      <select id="filter_regional" class="select2" multiple="multiple" data-placeholder="Pilih Regional"
                        style="width: 100%;">
                        <?php foreach ($unique_regional as $data): ?>
                          <option value="<?php echo $data['regional_project'] ?>"> <?php echo $data['regional_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">PIC</label>
                      <select id="filter_pic" class="select2" multiple="multiple" data-placeholder="Pilih PIC"
                        style="width: 100%;">
                        <?php foreach ($unique_pic as $data): ?>
                          <option value="<?php echo $data['pic_project'] ?>"> <?php echo $data['pic_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">AREA</label>
                      <select id="filter_area" class="select2" multiple="multiple" data-placeholder="Pilih Area"
                        style="width: 100%;">
                        <?php foreach ($unique_area as $data): ?>
                          <option value="<?php echo $data['area_project'] ?>"> <?php echo $data['area_project'] ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="form-group">
                      <label style="display: flex; justify-content: center; align-items: center;">STAGGING</label>
                      <select id="filter_stagging" class="select2" multiple="multiple" data-placeholder="Pilih Stagging"
                        style="width: 100%;">
                        <?php foreach ($unique_stagging as $data): ?>
                          <option value="<?php echo $data['main_status'] ?>"> <?php echo $data['main_status'] ?>
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
                          <h3 id="idtotal_hp_plan"><?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ".") . " HP" ?></h3>
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
                          <h3 id="idtotal_hp_canvasing"><?= number_format(floatval($totalHpPlan['total_hp_canvasing']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>DONE CANVASING</p>
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
                          <h3 id="idtotal_hp_bak"><?= number_format(floatval($totalHpPlan['total_hp_bak']), 0, ".") . " HP" ?></h3>
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
                          <h3 id="idtotal_hp_spk"><?= number_format(floatval($totalHpPlan['total_hp_spk']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>SPK RELEASED</p>
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
                          <h3 id="idtotal_hp_hld"><?= number_format(floatval($totalHpPlan['total_hp_hld']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>DONE HLD</p>
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
                          <h3 id="idtotal_hp_lld"><?= number_format(floatval($totalHpPlan['total_hp_lld']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>DONE LLD</p>
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
                          <h3 id="idtotal_hp_kom"><?= number_format(floatval($totalHpPlan['total_hp_kom']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>ON PROGRESS IMPLEMENTASI</p>
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
                          <h3 id="idtotal_hp_rfs"><?= number_format(floatval($totalHpPlan['total_hp_rfs']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>DONE RFS</p>
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
                          <h3 id="idtotal_hp_atp"><?= number_format(floatval($totalHpPlan['total_hp_atp']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>DONE ATP</p>
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
                          <h3 id="idtotal_hp_closed"><?= number_format(floatval($totalHpPlan['total_hp_closed']), 0, ".") . " HP" ?></h3>
                        <?php endforeach ?>

                        <p>CLOSED STAGGING</p>
                      </div>
                      <div class="icon">
                        <i class="ion ion-bag"></i>
                      </div>
                    </div>
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
      <div class="col-lg-6">
          <div class="card">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title">Top Area Cleanlist</h3>
                <a href="javascript:void(0);">View Report</a>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                <?php foreach ($total_hp_plan as $totalHpPlan): ?>
                  <span class="text-bold text-lg"><?= number_format(floatval($totalHpPlan['total_hp_plan']), 0, ".") . " HP" ?></span>
                <?php endforeach ?>
                  <span>TOP AREA</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> 57.0%
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
                  <span class="text-bold text-lg"><?= number_format(floatval($totalHpPlan['total_hp_bak']), 0, ".") . " HP" ?></span>
                <?php endforeach ?>
                  <span>TOP AREA</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> 17.0%
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
                    <th>Status CBN</th> <!-- 12 -->
                    <th>Nomor SPK</th> <!-- 13 -->
                    <th>Status HLD</th> <!-- 14 -->
                    <th>Status LLD</th> <!-- 15 -->
                    <th>KOM</th> <!-- 16 -->
                    <th>PKS</th> <!-- 17 -->
                    <th>Status Implementasi</th> <!-- 18 -->
                    <th>RFS</th> <!-- 19 -->
                    <th>ATP</th> <!-- 20 -->
                    <th>Stagging</th> <!-- 21 -->
                    <th>Done Invoice</th> <!-- 22 -->
                    <th>Sisa Invoice</th> <!-- 23 -->
                    <th>Progress</th> <!-- 24 -->
                    <th>Label</th> <!-- 25 -->
                    <th>Action</th> <!-- 26 -->
                  </tr>
                </thead>
                <tbody>
                  <?php


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
                      <td class="align-middle text-center"><?= number_format(floatval($data['hpplan_project']), 0, ".") ?></td>
                      <td class="align-middle text-center"><?= $data['number_po'] ?></td>
                      <td class="align-middle text-center"><?= $data['tanggal_po'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['nilai_awal_po']), 0, ".") ?></td>
                      <td class="align-middle"><?= $data['tgl_canvasing'] ?></td>
                      <td class="align-middle"><?= $data['status_bak'] ?></td>
                      <td class="align-middle"><?= $data['status_cbn'] ?></td>
                      <td class="align-middle"><?= $data['spk_nomor'] ?></td>
                      <td class="align-middle"><?= $data['status_hld'] ?></td>
                      <td class="align-middle"><?= $data['status_lld'] ?></td>
                      <td class="align-middle"><?= $data['tgl_kom'] ?></td>
                      <td class="align-middle"><?= $data['tgl_pks'] ?></td>
                      <td class="align-middle text-center"><?= $data['status_implementasi'] ?></td>
                      <td class="align-middle"><?= $data['tanggal_rfs'] ?></td>
                      <td class="align-middle"><?= $data['tanggal_atp'] ?></td>
                      <td class="align-middle text-center"><?= $data['main_status'] ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_invoice']), 0, ".") ?></td>
                      <td class="align-middle"><?= number_format(floatval($data['total_sisa_invoice']), 0, ".") ?></td>
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
                        <?php if ($data['status_lld'] == "OK") { ?>
                          <a href="<?php echo site_url('Fiberstar_Project_Detail/detailImplementasi/' . $data['primary_access_id_project']); ?>"
                            id="tombol_detail" class="btn btn-primary tombol_detail"><i class=" fas fa-share"></i></a>
                        <?php } ?>

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
                        value="PT. Fiberstar">
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

          <div class="modal fade" id="modal-lg-tambah-file">
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
                    <label for="exampleInputFile">File input</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                      </div>
                      <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>

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
    const areaAchievBar = dataBar.map(item => item.area_project);
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
    const areaAchievBarCleanlist = dataBarCleanlist.map(item => item.area_project);
    const hpAchievBarCleanlist = dataBarCleanlist.map(item => item.achiev_cleanlist);


    // eslint-disable-next-line no-unused-vars
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
    $('#table_data').DataTable({
      responsive: false, // Matikan fitur Responsive
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

      let totalHpCanvasing = 0 ;
      let totalHpBAK = 0 ;
      let totalHpSPK = 0 ;
      let totalHpHLD = 0 ;
      let totalHpLLD = 0 ;
      let totalHpKOM = 0 ;
      let totalHpRFS = 0 ;
      let totalHpATP = 0 ;
      let totalHpClosed = 0 ;

      data.each(function (row) {
        totalHP += parseFloat(row[6].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        totalPO += parseFloat(row[9].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        totalDoneInvoice += parseFloat(row[22].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value
        totalSisaInvoice += parseFloat(row[23].replace(/,/g, '')) || 0; // Index 2 adalah kolom Value

        if (row['10'] != 0 ){
          totalHpCanvasing += parseFloat(row[6].replace(/,/g, ''))
        } if (row['11'] == "OK" ){
          totalHpBAK += parseFloat(row[6].replace(/,/g, ''))
        } if (row['13'] != 0 ){
          totalHpSPK += parseFloat(row[6].replace(/,/g, ''))
        } if (row['14'] == "OK" ){
          totalHpHLD += parseFloat(row[6].replace(/,/g, ''))
        } if (row['15'] == "OK" ){
          totalHpLLD += parseFloat(row[6].replace(/,/g, ''))
        } if (row['16'] != 0 ){
          totalHpKOM += parseFloat(row[6].replace(/,/g, ''))
        } if (row['19'] != 0 ){
          totalHpRFS += parseFloat(row[6].replace(/,/g, ''))
        } if (row['20'] != 0 ){
          totalHpATP += parseFloat(row[6].replace(/,/g, ''))
        } if (row['21'].includes("close")){
          totalHpClosed += parseFloat(row[6].replace(/,/g, ''))
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
        .column(21).search(staggingFilter, true, false) // Filter kategori (regex search)
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