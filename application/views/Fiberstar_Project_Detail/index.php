<?php
$status = $this->session->flashdata('status');
$status == 'sukses_tambah';
$error_log = $this->session->flashdata('error_log');
$total = 1;
$total_hp = 0;
$total_plan_tiang = 0;
$total_achiev_tiang = 0;
$total_plan_kabel_24C = 0;
$total_achiev_kabel_24C = 0;
$total_plan_kabel_48C = 0;
$total_achiev_kabel_48C = 0;
$total_plan_fat = 0;
$total_achiev_fat = 0;
$total_plan_closure = 0;
$total_achiev_closure = 0;

$persentase_plan = 0;
$persentase_achiev = 0;
$persentase_total = 0;

$total_upload_approval_cbn = 0;
$row_status_implementasi;
$row_primary_access_id_project;

foreach ($progress_implementasi as $data):
    $row_status_implementasi = $data['status_implementasi'];
    $row_primary_access_id_project = $data['primary_access_id_project'];
endforeach;

function formatTanggalIndonesia($date)
{
    $months = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $datetime = new DateTime($date);
    $day = $datetime->format('d');
    $month = (int) $datetime->format('m');
    $year = $datetime->format('Y');

    return $day . ' ' . $months[$month] . ' ' . $year;
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= $judul ?></h1>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="col-md-12">

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= $this->session->flashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php
                $this->session->unset_userdata('success');
                $this->session->unset_userdata('error');
                ?>


                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Cleanlist Deskripsi
                        </h3>
                    </div>

                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php foreach ($progress_implementasi as $data):

                            $persentase_plan = $data['plan_tiang'] + $data['plan_kabel_24'] + $data['plan_kabel_48'] + $data['plan_fat'] + $data['plan_closure'];
                            $persentase_achiev = $data['achiev_tiang'] + $data['achiev_kabel_24'] + $data['achiev_kabel_48'] + $data['achiev_fat'] + $data['achiev_closure'];

                            if ($persentase_achiev == 0 || $persentase_plan == 0) {
                                $persentase_total = 0;
                            } else {
                                $persentase_total = ($persentase_achiev / $persentase_plan) * 100;
                            }

                            if ($data['number_po'] == '') {
                                $data['number_po'] = "-";
                            }

                            if ($data['tgl_kom'] == '' || empty($data['tgl_kom'])) {
                                $hasil30hari = "-";
                                $data['tgl_kom'] = "-";
                            } else {
                                $tanggal = $data['tgl_kom'];
                                $parts = explode('/', $tanggal);
                                $newFormat = $parts[1] . '/' . $parts[0] . '/' . $parts[2];
                                $newTimestamp = strtotime($newFormat . ' +30 days');
                                $hasil30hari = date('d/m/Y', $newTimestamp);
                            }



                        ?>


                            <dl>
                                <dt>Regional</dt>
                                <dd> <?= $data['regional_project'] ?></dd>
                                <dt>Area</dt>
                                <dd> <?= $data['area_project'] ?></dd>
                                <dt>PIC</dt>
                                <dd> <?= $data['pic_project'] ?></dd>
                                <dt>Access ID</dt>
                                <dd> <?= $data['access_id_project'] ?></dd>
                                <dt>Access Name</dt>
                                <dd> <?= $data['access_name_project'] ?></dd>
                                <dt>Home Pass</dt>
                                <dd>
                                    <?php if ($data['hp_hld'] == '') { ?>
                                        0
                                    <?php } else { ?>
                                        <?= number_format(floatval($data['hp_hld']), 0, ".") ?>
                                    <?php } ?>

                                </dd>
                                <dt>Nomor PO</dt>
                                <dd><?= $data['number_po'] ?></dd>
                                <dt>Tanggal KOM</dt>
                                <dd><?= $data['tgl_kom'] ?></dd>
                                <dt>Target RFS</dt>
                                <dd><?= $hasil30hari ?></dd>
                                <dt>Persentase Progress ( % )</dt>
                                <dt>
                                    <?php if ($persentase_total < '25') { ?>
                                        <div class="row" style="margin-top:0.5%">
                                            <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                                                <div class="progress-bar bg-danger" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                                            </div>
                                            <span class="badge bg-danger" style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                                        </div>
                                    <?php } else if ($persentase_total >= '25' && $persentase_total < '70') { ?>
                                        <div class="row" style="margin-top:0.5%">
                                            <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                                                <div class="progress-bar bg-warning" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                                            </div>
                                            <span class="badge bg-warning"
                                                style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                                        </div>
                                    <?php } else if ($persentase_total >= '70' && $persentase_total < '100') { ?>
                                        <div class="row" style="margin-top:0.5%">
                                            <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                                                <div class="progress-bar bg-primary" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                                            </div>
                                            <span class="badge bg-primary"
                                                style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                                        </div>
                                    <?php } else { ?>
                                        <div class="row" style="margin-top:0.5%">
                                            <div class="col-1 progress progress-xs" style="margin-top: 5px;">
                                                <div class="progress-bar bg-success" style="width: <?= round($persentase_total, 1) . "%" ?>"></div>
                                            </div>
                                            <span class="badge bg-success"
                                                style="margin-left: 2%;"><?= round($persentase_total, 1) . "%" ?></span>
                                        </div>
                                    <?php } ?>

                                    </dd>
                            </dl>
                        <?php endforeach ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </section>

        <section class="content">
            <div class="col-12 col-sm-12">
                <div class="card card-dark card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                            <li class="pt-2 px-3">
                                <h3 class="card-title">Detail Progress</h3>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill"
                                    href="#custom-tabs-two-poinvoice" role="tab" aria-controls="custom-tabs-two-home"
                                    aria-selected="true">PO & Invoice</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-two-implementasi" role="tab" aria-controls="custom-tabs-two-profile"
                                    aria-selected="false">Implementasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-messages-tab" data-toggle="pill"
                                    href="#custom-tabs-two-messages" role="tab" aria-controls="custom-tabs-two-messages"
                                    aria-selected="false">Checklist Dokument</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-two-settings-tab" data-toggle="pill"
                                    href="#custom-tabs-two-settings" role="tab" aria-controls="custom-tabs-two-settings"
                                    aria-selected="false">Dokumentasi Lapangan</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-two-tabContent">

                            <!-- TAB NAV PERTAMA -->
                            <div class="tab-pane show active" id="custom-tabs-two-poinvoice" role="tabpanel"
                                aria-labelledby="custom-tabs-two-profile-tab">
                                <table id="table_detail" class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal PO</th>
                                            <th>Nilai PO</th>
                                            <th>Canvasing</th>
                                            <th>Status BAK</th>
                                            <th>Status CBN</th>
                                            <th>Nomor SPK</th>
                                            <th>Status HLD</th>
                                            <th>Status LLD</th>
                                            <th>Status Implementasi</th>
                                            <th>RFS</th>
                                            <th>ATP</th>
                                            <th>Stagging</th>
                                            <th>Done Invoice</th>
                                            <th>Sisa Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        foreach ($progress_implementasi as $data):
                                        ?>

                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['tanggal_po'] ?></td>
                                                <td><?= number_format(floatval($data['nilai_awal_po']), 0, ".") ?></td>
                                                <td><?= $data['tgl_canvasing'] ?></td>
                                                <td><?= $data['status_bak'] ?></td>
                                                <td><?= $data['status_cbn'] ?></td>
                                                <td><?= $data['spk_nomor'] ?></td>
                                                <td><?= $data['status_hld'] ?></td>
                                                <td><?= $data['status_lld'] ?></td>
                                                <td><?= $data['status_implementasi'] ?></td>
                                                <td><?= $data['tanggal_rfs'] ?></td>
                                                <td><?= $data['tanggal_atp'] ?></td>
                                                <td><?= $data['main_status'] ?></td>
                                                <td><?= number_format(floatval($data['total_invoice']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['total_sisa_invoice']), 0, ".") ?></td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- TAB NAV KEDUA -->
                            <div class="tab-pane fade" id="custom-tabs-two-implementasi" role="tabpanel"
                                aria-labelledby="custom-tabs-two-home-tab">
                                <table id="table_detail" class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>HP HLD</th>
                                            <th>Plan Tiang</th>
                                            <th>Achiev Tiang</th>
                                            <th>Plan Kabel 24C</th>
                                            <th>Achiev Kabel 24C</th>
                                            <th>Plan Kabel 48C</th>
                                            <th>Achiev Kabel 48C</th>
                                            <th>Plan FAT</th>
                                            <th>Achiev FAT</th>
                                            <th>Plan Closure</th>
                                            <th>Achiev Closure</th>
                                            <th>Tanggal Progress</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;

                                        foreach ($detail_progress_implementasi as $data):

                                            $total_hp += $data['hp_hld'];
                                            $total_plan_tiang += $data['plan_tiang'];
                                            $total_achiev_tiang += $data['achiev_tiang'];
                                            $total_plan_kabel_24C += $data['plan_kabel_24'];
                                            $total_achiev_kabel_24C += $data['achiev_kabel_24'];
                                            $total_plan_kabel_48C += $data['achiev_kabel_48'];
                                            $total_achiev_kabel_48C += $data['plan_fat'];
                                            $total_plan_fat += $data['achiev_fat'];
                                            $total_achiev_fat += $data['plan_closure'];
                                            $total_plan_closure += $data['achiev_closure'];
                                            $total_achiev_closure += $data['achiev_closure'];

                                        ?>
                                            <tr <?php if (str_contains($data['keterangan_progress'], "RAB") || str_contains($data['keterangan_progress'], "BOQ")) { ?>
                                                style="background-color: lightblue;" <?php } else if (str_contains($data['keterangan_progress'], "Done") || str_contains($data['keterangan_progress'], "DONE")) { ?>
                                                style="background-color: yellow;" <?php } else { ?> <?php } ?>>
                                                <td><?= $total++ ?></td>
                                                <td><?= number_format(floatval($data['hp_hld']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['plan_tiang']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['achiev_tiang']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['plan_kabel_24']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['achiev_kabel_24']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['plan_kabel_48']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['achiev_kabel_48']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['plan_fat']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['achiev_fat']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['plan_closure']), 0, ".") ?></td>
                                                <td><?= number_format(floatval($data['achiev_closure']), 0, ".") ?></td>
                                                <td><?= $data['data_created'] ?></td>
                                                <td><?= $data['keterangan_progress'] ?></td>
                                            </tr>


                                        <?php endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><?= number_format(floatval($total_plan_tiang), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_achiev_tiang), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_plan_kabel_24C), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_achiev_kabel_24C), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_plan_kabel_48C), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_achiev_kabel_48C), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_plan_fat), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_achiev_fat), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_plan_closure), 0, ".") ?></th>
                                            <th colspan="1"><?= number_format(floatval($total_achiev_closure), 0, ".") ?></th>
                                            <th colspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th colspan="2">Selisih</th>
                                            <th colspan="2"><?= number_format(floatval($total_plan_tiang - $total_achiev_tiang), 0, ".") ?>
                                            </th>
                                            <th colspan="2">
                                                <?= number_format(floatval($total_plan_kabel_24C - $total_achiev_kabel_24C), 0, ".") ?></th>
                                            <th colspan="2">
                                                <?= number_format(floatval($total_plan_kabel_48C - $total_achiev_kabel_48C), 0, ".") ?></th>
                                            <th colspan="2"><?= number_format(floatval($total_plan_fat - $total_achiev_fat), 0, ".") ?></th>
                                            <th colspan="2">
                                                <?= number_format(floatval($total_plan_closure - $total_achiev_closure), 0, ".") ?></th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="modal-footer">
                                    <?php if ($this->session->userdata('tim_project') == "HO") { ?>
                                        <?php if ($row_status_implementasi == "OK") { ?>
                                            <a href="<?php echo site_url('Fiberstar_Project_Detail/editStatusImplementasiBack/' . $row_primary_access_id_project); ?>" class="btn btn-success float-right text-bold">Tambah Implementasi</a>
                                        <?php } else { ?>
                                            <a href="<?php echo site_url('Fiberstar_Project_Detail/editStatusImplementasi/' . $row_primary_access_id_project); ?>" class="btn btn-secondary float-right text-bold">Close Implementasi</a>
                                            <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah_boq"
                                                data-toggle="modal">Tambah BOQ &nbsp;</a>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php if ($row_status_implementasi == "OK") { ?>

                                        <?php } else { ?>
                                            <a href="<?php echo site_url('Fiberstar_Project_Detail/editStatusImplementasi/' . $row_primary_access_id_project); ?>" class="btn btn-secondary float-right text-bold">Close Implementasi</i> </a>
                                            <a href="#" class="btn btn-success float-right text-bold" data-target="#modal-lg-tambah_implementasi"
                                                data-toggle="modal">Tambah Progess &nbsp;</a>
                                        <?php } ?>

                                    <?php } ?>
                                </div>
                            </div>

                            <!-- TAB NAV KETIGA -->
                            <div class="tab-pane fade" id="custom-tabs-two-messages" role="tabpanel"
                                aria-labelledby="custom-tabs-two-messages-tab">

                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Stagging</th>
                                            <th>Doc Uploaded</th>
                                            <th>Last Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $keys = 1 ?>
                                        <?php foreach ($master_data_stagging as $index => $value) { ?>
                                            <tr data-widget="expandable-table" aria-expanded="false">
                                                <?php
                                                $total_count = count(array_filter($master_data_dokument, function ($item) use ($value) {
                                                    return $item['stagging_document_support'] == $value;
                                                }));

                                                $filtered = array_values(array_filter($count_document_support_approval, function ($item) use ($value) {
                                                    return $item['stagging_document_support'] == $value;
                                                }));
                                                ?>
                                                <td><?= $keys++ ?></td>
                                                <td><?= $value ?></td>
                                                <td><?= isset($filtered[0]['total']) ? $filtered[0]['total'] : 0 ?>/<?= $total_count ?></td>
                                                <td><?= isset($filtered[0]['last_update']) ? $formatted_date = date('d-m-Y', strtotime($filtered[0]['last_update'])) : "Belum Ada Berkas" ?></td>
                                            </tr>
                                            <tr class="expandable-body">
                                                <td colspan="5">
                                                    <div class="card-body">
                                                        <table id="table_detail" class="table table-bordered table-hover">
                                                            <thead class="bg-primary">
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Dokument Support Approval CBN</th>
                                                                    <th>Status</th>
                                                                    <th>Remark</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $keys_child = 1 ?>
                                                                <?php foreach ($master_data_dokument as $key => $values) { ?>
                                                                    <?php if ($values['stagging_document_support'] == $value) { ?>
                                                                        <?php
                                                                        $result = array_filter($data_document_support_approval, function ($item) use ($values) {
                                                                            return $item['id_document_support_fiberstar'] == $values['id_document_support'];
                                                                        });
                                                                        $first = reset($result);
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo $keys_child++ ?></td>
                                                                            <td><?php echo $values['nama_document_support'] ?></td>
                                                                            <td>
                                                                                <?php
                                                                                if (!isset($first) || !is_array($first) || !isset($first['status_document_support'])) {
                                                                                    echo '<span class="badge badge-secondary">Not Yet Uploaded</span>';
                                                                                } else {
                                                                                    if ($first['status_document_support'] == 1) {
                                                                                        echo '<span class="badge badge-warning">On Review</span>';
                                                                                    } elseif ($first['status_document_support'] == 2) {
                                                                                        echo '<span class="badge badge-success">Approved</span>';
                                                                                    } elseif ($first['status_document_support'] == 3) {
                                                                                        echo '<span class="badge badge-danger">Rejected</span>';
                                                                                    } else {
                                                                                        echo '<span class="badge badge-secondary">Not Yet Uploaded</span>';
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td>
                                                                                <?php
                                                                                if (!isset($first) || !is_array($first) || !isset($first['remark'])) {
                                                                                    echo 'belum ada remark';
                                                                                } else {
                                                                                    echo $first['remark'];
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" data-toggle="modal" data-target="#modal-upload-document" class="btn btn-success btn-upload-document" data-id-ds-approval="<?= isset($first['id_document_support_approval']) ? $first['id_document_support_approval'] : '' ?>" data-id-document="<?= $values['id_document_support'] ?>" data-nama-document="<?= $values['nama_document_support'] ?>"><i class="fas fa-plus"></i></a>
                                                                                <a href="<?= base_url(isset($first['document_support_location']) ? $first['document_support_location'] : '') ?>" target="_blank" class="btn btn-warning <?= empty($first['document_support_location']) ? 'disabled' : '' ?>">
                                                                                    <i class="fas fa-solid fa-eye"></i>
                                                                                </a>
                                                                                <?php if ($this->session->userdata('tim_project') === 'HO') { ?>
                                                                                    <a href="#" class="btn btn-success <?= empty($first['document_support_location']) ? 'disabled' : '' ?>"><i class="fas fa-check"></i></a>
                                                                                    <a href="#" class="btn btn-danger <?= empty($first['document_support_location']) ? 'disabled' : '' ?>"><i class="fas fa-times"></i></a>
                                                                                <?php } ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php }; ?>
                                                                <?php }; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php }; ?>
                                    </tbody>
                                </table>
                                <br>

                                <!-- START MODAL UPLOAD DOKUMEN -->

                                <div class="modal fade" id="modal-upload-document">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">UPLOAD <span id="name_document_support"></span></h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">

                                                <form method="post" action="<?= base_url('Fiberstar_Project_Detail/upload_document') ?>" enctype="multipart/form-data">
                                                    <input type="hidden" name="id_document_support_approval" id="id_document_support_approval" value="">
                                                    <input type="hidden" name="id_document_support" id="id_document_support_input" value="">
                                                    <input type="hidden" name="name_document_support" id="name_document_support_input" value="">
                                                    <input type="hidden" name="primary_access_id_project" value="<?= $data['primary_access_id_project'] ?>">
                                                    <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_user') ?>">

                                                    <div class="form-group">
                                                        <label class="col-form-label">Access ID Project</label>
                                                        <input readonly type="text" class="form-control" name="access_id_project" autocomplete="off" value="<?= $data['access_id_project'] ?>">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Upload Dokument</label>
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <label class="custom-file-label" for="file-ds">Choose file</label>
                                                                <input type="file" name="file" id="file-ds" class="custom-file-input" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                                                        <button type="submit" name="btnSubmitPOFiberstar" class="btn btn-primary"><i class="fa fa-spinner fa-spin loading" style="display:none"></i> Tambah</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- END MODAL UPLOAD DOKUMEN -->

                            </div>

                            <!-- TAB NAV KEEMPAT -->
                            <div class="tab-pane fade" id="custom-tabs-two-settings" role="tabpanel"
                                aria-labelledby="custom-tabs-two-settings-tab">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-widget="expandable-table" aria-expanded="false">
                                            <td>183</td>
                                            <td>John Doe</td>
                                            <td>11-7-2014</td>
                                            <td>Approved</td>
                                            <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
                                        </tr>
                                        <tr class="expandable-body">
                                            <td colspan="5">
                                                <p>
                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has
                                                    been the industry's standard dummy text ever since the 1500s, when an unknown printer took a
                                                    galley of type and scrambled it to make a type specimen book. It has survived not only five
                                                    centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It
                                                    was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                                    passages, and more recently with desktop publishing software like Aldus PageMaker including
                                                    versions of Lorem Ipsum.
                                                </p>
                                            </td>
                                        </tr>
                                        <tr data-widget="expandable-table" aria-expanded="false">
                                            <td>219</td>
                                            <td>Alexander Pierce</td>
                                            <td>11-7-2014</td>
                                            <td>Pending</td>
                                            <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
                                        </tr>
                                        <tr class="expandable-body">
                                            <td colspan="5">
                                                <p>
                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has
                                                    been the industry's standard dummy text ever since the 1500s, when an unknown printer took a
                                                    galley of type and scrambled it to make a type specimen book. It has survived not only five
                                                    centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It
                                                    was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum
                                                    passages, and more recently with desktop publishing software like Aldus PageMaker including
                                                    versions of Lorem Ipsum.
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <!-- fix for small devices only -->
                    <div class="clearfix hidden-md-up"></div>

                    <div class="col-12">

                        <?php $tgl = date(format: 'Y-m-d'); ?>
                        <?php foreach ($progress_implementasi as $data):
                        ?>
                            <form action="<?php echo site_url('Fiberstar_Project_Detail/addImplementasi'); ?>" method="post">
                                <div class="modal fade" id="modal-lg-tambah_implementasi">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Tambah Implementasi</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="access_id_project"
                                                    value="<?= $data['access_id_project'] ?>">
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
                                                    <input type="text" class="form-control" name="achiev_kabel_24" autocomplete="off"
                                                        placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan Kabel 48C / Achiev Kabel 48C / Deviasi</label>
                                                    <input readonly type="text" class="form-control" name="plan_kabel_48" autocomplete="off"
                                                        value="<?= $data['plan_kabel_48'] . " / " . $data['achiev_kabel_48'] . " / " . ($data['plan_kabel_48'] - $data['achiev_kabel_48']) ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Daily Progress Kabel 48C</label>
                                                    <input type="text" class="form-control" name="achiev_kabel_48" autocomplete="off"
                                                        placeholder="0">
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
                                                    <input type="text" class="form-control" name="achiev_closure" autocomplete="off"
                                                        placeholder="0">
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

                        <?php $tgl = date(format: 'Y-m-d'); ?>
                        <?php foreach ($progress_implementasi as $data):
                        ?>
                            <form action="<?php echo site_url('Fiberstar_Project_Detail/addBOQ'); ?>" method="post">
                                <div class="modal fade" id="modal-lg-tambah_boq">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Tambah BOQ</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="access_id_project"
                                                    value="<?= $data['access_id_project'] ?>">
                                                <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_user') ?>">
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
                                                    <label class="col-form-label">Homepass HLD</label>
                                                    <input readonly type="text" class="form-control" name="hp_hld" autocomplete="off"
                                                        value="<?= $data['hp_hld'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan Tiang</label>
                                                    <input type="text" class="form-control" name="plan_tiang" autocomplete="off" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan Kabel 24C</label>
                                                    <input type="text" class="form-control" name="plan_kabel_24" autocomplete="off" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan Kabel 48C</label>
                                                    <input type="text" class="form-control" name="plan_kabel_48" autocomplete="off" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan FAT</label>
                                                    <input type="text" class="form-control" name="plan_fat" autocomplete="off" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Plan Closure</label>
                                                    <input type="text" class="form-control" name="plan_closure" autocomplete="off" placeholder="0">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label">Catatan</label>
                                                    <input type="text" class="form-control" name="keterangan_progress" autocomplete="off"
                                                        value="BOQ Awal">
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

                        <!-- modal untuk tambah data -->
                        <?php foreach ($progress_implementasi as $data): ?>
                            <form id="uploadForm" action="<?php echo base_url('Fiberstar_Project_Detail/upload_file'); ?>" method="post"
                                enctype="multipart/form-data">
                                <div class="modal fade" id="modal-approval-cbn-suratijinsurvey">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Surat Ijin Survey</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="primary_access_id_project"
                                                    value="<?= $data['primary_access_id_project'] ?>">
                                                <input type="hidden" name="id_user" value="<?= $this->session->userdata('id_user') ?>">
                                                <div class="form-group">
                                                    <label class="col-form-label">Access ID Project</label>
                                                    <input readonly type="text" class="form-control" name="access_id_project" autocomplete="off"
                                                        value="<?= $data['access_id_project'] ?> || <?= $data['access_name_project'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Upload Dokument</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <label class="custom-file-label" for="file">Choose file</label>
                                                            <input type="file" name="file" id="file-lama" class="custom-file-input" required>
                                                        </div>
                                                    </div>
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


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const table = document.getElementById("table_data_dokument");
        let selectedRows = []; // Array untuk menyimpan baris yang dipilih
        let lastSelectedIndex = null; // Index baris terakhir yang dipilih

        // Event listener untuk menangani klik pada tabel
        table.addEventListener("click", function(event) {
            const row = event.target.closest("tr");

            // Pastikan hanya baris di <tbody> yang bisa dipilih
            if (row && row.parentElement.tagName === "TBODY") {
                const rows = Array.from(table.querySelectorAll("tbody tr"));
                const rowIndex = rows.indexOf(row);

                if (row.classList.contains("selected-row")) {
                    // Jika baris sudah dipilih, hapus status "selected-row"
                    row.classList.remove("selected-row");
                    selectedRows = selectedRows.filter(r => r !== row); // Hapus dari array
                } else {
                    // Pilih hanya satu baris tanpa tombol modifier
                    // rows.forEach(r => r.classList.remove("selected-row"));
                    selectedRows = [row];
                    row.classList.add("selected-row");
                }

                // Update index baris terakhir yang dipilih
                lastSelectedIndex = rowIndex;
            }
        });
    });

    // PASSING DATA DARI ROW KE MODAL
    $('.btn-upload-document').on('click', function() {
        var id_document_support = $(this).data('id-document');
        var name_document_support = $(this).data('nama-document');
        var id_document_support_approval = $(this).data('id-ds-approval');
        $('#file-ds').val('');
        $('.custom-file-label').text('Choose file');

        console.log(id_document_support);
        console.log(name_document_support);

        $('#name_document_support').text(name_document_support);
        $('#id_document_support_input').val(id_document_support);
        $('#name_document_support_input').val(name_document_support);
        $('#id_document_support_approval').val(id_document_support_approval);
    });

    $(document).ready(function() {
        $('#file-ds').on('change', function(e) {
            var fileName = e.target.files.length > 0 ? e.target.files[0].name : "Choose file";
            $(this).siblings('.custom-file-label').text(fileName);
        });
    });

    var total_upload_approval_cbn = <?= $total_upload_approval_cbn ?>;
    document.getElementById('total_upload_approval_cbn').innerText = total_upload_approval_cbn + " / 3";


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
    $(function() {

        // format angka rupiah
        $('[data-mask]').inputmask("currency", {
            prefix: " Rp. ",
            digitsOptional: true
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

    });

    $('.swalDefaultError').click(function() {
        swal("Gagal!", "Dokument Belum Di Upload!", "warning");
    });

    $('.tombol_hapus').on('click', function(e) {
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
        clickable: ".custom-file-input" // Define the element that should be used as click trigger to select files.
    })

    myDropzone.on("addedfile", function(file) {
        // Hookup the start button
        file.previewElement.querySelector(".start").onclick = function() {
            myDropzone.enqueueFile(file)
        }
    })

    // Update the total progress bar
    myDropzone.on("totaluploadprogress", function(progress) {
        document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
    })

    myDropzone.on("sending", function(file) {
        // Show the total progress bar when upload starts
        document.querySelector("#total-progress").style.opacity = "1"
        // And disable the start button
        file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
    })

    // Hide the total progress bar when nothing's uploading anymore
    myDropzone.on("queuecomplete", function(progress) {
        document.querySelector("#total-progress").style.opacity = "0"
    })

    // Setup the buttons for all transfers
    // The "add files" button doesn't need to be setup because the config
    // `clickable` has already been specified.
    document.querySelector("#actions .start").onclick = function() {
        myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
    }
    document.querySelector("#actions .cancel").onclick = function() {
        myDropzone.removeAllFiles(true)
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {

        // Format mata uang.
        $('.nilai_po2').mask('000.000.000', {
            reverse: true
        });

    })
</script>

<script>
    $(function() {
        bsCustomFileInput.init();
    });

    // Membuat circle progress bar
    var bar = new ProgressBar.Circle('#progress-bar-container', {
        color: '#FF5733', // Warna progress bar
        strokeWidth: 10, // Ketebalan garis
        trailWidth: 10, // Ketebalan garis latar belakang
        easing: 'easeInOut', // Animasi progress bar
        duration: 1400, // Durasi animasi dalam milidetik
        from: {
            color: '#ddd',
            width: 10
        },
        to: {
            color: '#FF5733',
            width: 10
        },
        step: function(state, circle) {
            circle.path.setAttribute('stroke', state.color);
            circle.path.setAttribute('stroke-width', state.width);
            var value = Math.round(circle.value() * 100);
            circle.setText(value + '%');
        }
    });

    // Mengatur nilai progress bar
    bar.animate(<?= $persentase_po ?>); // Nilai antara 0.0 hingga 1.0 (70% dalam contoh ini)
</script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/fontawesome-free/css/all.min.css">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="<?= base_url('assets') ?>/dist/css/adminlte.min.css">
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/bs-custom-file-input/bs-custom-file-input.min.js">
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
<link rel="stylesheet" href="<?= base_url('assets') ?>/plugins/toastr/toastr.min.css">
<script src="<?= base_url('assets') ?>/plugins/toastr/toastr.min.css"></script>

<style>
    .selected-row {
        background-color: lightblue !important;
        /* Warna biru terang */
    }
</style>