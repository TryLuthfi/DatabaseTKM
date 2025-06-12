<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];


$total_aksesories = 0;
$total_closure = 0;
$total_fat = 0;
$total_fdt = 0;
$total_hdpe = 0;
$total_kabel = 0;
$total_otb = 0;
$total_tiang = 0;
?>

<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">SUMMARY STOK <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <?php foreach ($getSummaryDetailArea as $stokKategory): ?>
                <div class="col-lg-3 col-6" id="<?php echo 'box_detail_area_' . $stokKategory['kategori_item'] ?>">
                    <div class="small-box bg-info">
                        <div class="inner">

                            <h3 id="idtotal_hp_plan">
                                <?= number_format(floatval($stokKategory['total_jumlah_stok']), 0, ",", ".") . " " . $stokKategory['satuan_item'] ?>
                            </h3>


                            <p><?= $stokKategory['kategori_item'] ?></p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a class="small-box-footer" style="cursor: default;">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_aksesories">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK AKSESORIES <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK AKSESORIES</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_aksesories" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "Aksesories "):
                                            $total_aksesories += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelAksesories">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_closure">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK CLOSURE <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK CLOSURE</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_closure" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "Closure"):
                                            $total_closure += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelClosure">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_fat">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK FAT <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK FAT</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_fat" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "FAT"):
                                            $total_fat += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelFat">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_fdt">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK FDT <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK FDT</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_fdt" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "FDT"):
                                            $total_fdt += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelFdt">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_hdpe">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK HDPE <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK HDPE</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_hdpe" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "HDPE "):
                                            $total_hdpe += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelHdpe">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_kabel">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK KABEL <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK KABEL</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_kabel" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "Kabel "):
                                            $total_kabel += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelKabel">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_otb">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK OTB <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK OTB</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_otb" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "OTB "):
                                            $total_otb += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelOtb">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12" id="judul_stok_tiang">
                    <h1 class="m-0 text-dark" style="text-align: center;">STOK TIANG <?= "" . $lokasi ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST STOK TIANG</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_stok_tiang" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Item</th>
                                        <th>Project Item</th>
                                        <th>Stok</th>
                                        <th>Satuan Item</th>
                                        <th>Pemilik Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 1;
                                    foreach ($getStokDetailArea as $data):
                                        if ($data['kategori_item'] == "Tiang"):
                                            $total_tiang += $data['jumlah_stok'];
                                    ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['nama_item'] ?></td>
                                                <td><?= $data['project_item'] ?></td>
                                                <td><?= number_format($data['jumlah_stok'], 0, ",", ".") ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                                <td><?= $data['nama_bowheer'] ?></td>
                                            </tr>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><span id="totalTabelTiang">0</span></th>
                                        <th colspan="1"></th>
                                        <th colspan="1"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;">HISTORY IN OUT MATERIAL <?= "" . $lokasi ?>
                    </h1>
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
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">LIST HISTORY IN OUT MATERIAL</h3>
                                </div>
                                <div class="col-6">
                                    <!-- <a href="#" class="btn btn-success float-right text-bold btn-tambah-data-item"
                                            data-target="#modal-xl-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a> -->
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-scrollable">
                            <table id="table_data" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Regional</th>
                                        <th>Lokasi</th>
                                        <th>Project</th>
                                        <th>Kategori</th>
                                        <th>Item</th>
                                        <th>Status</th>
                                        <th>QTY</th>
                                        <th>PIC</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    $total = 1;
                                    foreach ($getHistoriInOUtLogistikArea as $data):
                                    ?>
                                        <tr>
                                            <td><?= $total++ ?></td>
                                            <td><?= $data['regional_lokasi_gudang'] ?></td>
                                            <td><?= $data['kota_lokasi_gudang'] ?></td>
                                            <td><?= $data['nama_bowheer'] ?></td>
                                            <td><?= $data['kategori_item'] ?></td>
                                            <td><?= $data['nama_item'] ?></td>
                                            <td><?= $data['nama_sumber_material'] ?></td>
                                            <td><?= $data['jumlah_stok'] ?></td>
                                            <td><?= $data['nama_user'] ?></td>
                                            <td class="d-flex">
                                                <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                                                    <a href="<?php echo site_url('Dashboard_Logistik_Stok/hapusReportStokLogistik/' . urlencode($data['no_surat_jalan']) . '?id_lokasi_gudang=' . urlencode($data['id_lokasi_gudang']) . '&lokasi=' . urlencode(get_instance()->uri->segment(get_instance()->uri->total_segments())) ); ?>"
                                                        id="tombol_hapus_rincian"
                                                        class="btn btn-danger tombol_hapus_rincian">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php } ?>

                                                <a href="" data-suratjalan="<?= $data['no_surat_jalan']; ?>"
                                                    data-id-logistik-stok-unique="<?= $data['surat_jalan'] ?>"
                                                    data-target="#form_detail_surat_jalan" data-toggle="modal"
                                                    class="btn btn-primary tombol_detail ml-1"><i
                                                        class=" fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>


    <div class="modal fade" id="form_detail_surat_jalan" data-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detail Report Stok Logistik</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" name="nomor_surat_jalan"
                                        id="detail_no_surat_jalan" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Input Date</label>
                                    <input type="date" class="form-control" name="tanggal_upload_stok"
                                        id="tanggal_upload_stok" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Area Gudang</label>
                                    <input type="text" class="form-control" name="area_gudang" id="detail_area_gudang"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Project</label>
                                    <input type="text" class="form-control" name="nama_project" id="detail_nama_project"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Sumber Material</label>
                                    <input type="text" class="form-control" name="sumber_material"
                                        id="detail_sumber_material" disabled>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="table_item_stok">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">No</th>
                                            <th>Kategori</th>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Satuan Item</th>
                                            <th>Merk Item</th>
                                            <th>No Haspel</th>
                                            <th>No Ref</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hasilDetailDataSJ">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3">Total</th>
                                            <th colspan="1"><span id="detail_total_qty">0</span></th>
                                            <th colspan="4"></th>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-form-label">Keterangan</label>
                                    <textarea type="text" class="form-control" name="keterangan_stok"
                                        id="detail_keterangan_stok_item" disabled rows="4"
                                        style="height: 100px;"></textarea>
                                </div>

                            </div>
                            <div class="col-md-6 d-none" id="detail_ho_in_nomor_po">
                                <div class="form-group">
                                    <label class="col-form-label">Nomor PO</label>
                                    <input type="text" class="form-control" name="detail_no_po_logistik"
                                        id="detail_no_po_logistik" disabled>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="detail_ho_out_nomor_pr">
                                <div class="form-group">
                                    <label class="col-form-label">Nomor PR</label>
                                    <input type="text" class="form-control" name="detail_no_pr_logistik"
                                        id="detail_no_pr_logistik" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 d-none" id="detail_ho_out_blank"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lihat Surat Jalan</label>
                                    <div class="card">
                                        <div class="card-body p-6">
                                            <div class="">
                                                <div class="d-flex align-items-center overflow-hidden">

                                                    <div class="flex-grow-1">
                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                            id="detail_nama_file_sj"></h5>
                                                        <a href="" class="font-size-14 text-muted text-truncate"
                                                            id="view_detail_surat_jalan" target="_blank"><u>View
                                                                Folder</u></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="container-detail-evidence">
                                <div class="form-group">
                                    <label>Lihat Bukti Evidence</label>
                                    <div class="card">
                                        <div class="card-body p-6">
                                            <div class="">
                                                <div class="d-flex align-items-center overflow-hidden">
                                                    <div class="flex-grow-1">
                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                            id="detail_nama_file_evidence"></h5>
                                                        <a href="" class="font-size-14 text-muted text-truncate"
                                                            id="view_detail_evidence" target="_blank"><u>View
                                                                Folder</u></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Oke</button>
                    </div>
                </div>
            </div>
        </div>

</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<script>
    $(function() {
        $("#tabel_aksesories").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_closure").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_fat").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_fdt").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_hdpe").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_kabel").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_otb").DataTable({
            "responsive": true,
        });
        $("#tabel_stok_tiang").DataTable({
            "responsive": true,
        });
        $("#table_data").DataTable({
            "responsive": true,
        });

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

    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';

        const tableAksesories = $('#tabel_aksesories').DataTable();
        const tableClosure = $('#tabel_stok_closure').DataTable();
        const tableFat = $('#tabel_stok_fat').DataTable();
        const tableFdt = $('#tabel_stok_fdt').DataTable();
        const tableHdpe = $('#tabel_stok_hdpe').DataTable();
        const tableKabel = $('#tabel_stok_kabel').DataTable();
        const tableOtb = $('#tabel_stok_otb').DataTable();
        const tableTiang = $('#tabel_stok_tiang').DataTable();

        function updateTotalTabel() {
            let totalTabelAksesories = 0,
                totalTabelClosure = 0,
                totalTabelFat = 0;
            let totalTabelFdt = 0,
                totalTabelHdpe = 0,
                totalTabelKabel = 0;
            let totalTabelOtb = 0,
                totalTabelTiang = 0;

            tableAksesories.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelAksesories += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableClosure.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelClosure += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableFat.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelFat += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableFdt.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelFdt += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableHdpe.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelHdpe += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableKabel.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelKabel += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableOtb.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelOtb += parseFloat(row[3].replace(/\./g, '')) || 0;
            });
            tableTiang.rows({
                search: 'applied'
            }).data().each(row => {
                totalTabelTiang += parseFloat(row[3].replace(/\./g, '')) || 0;
            });

            document.getElementById('totalTabelAksesories').innerText = totalTabelAksesories.toLocaleString('id-ID');
            document.getElementById('totalTabelClosure').innerText = totalTabelClosure.toLocaleString('id-ID');
            document.getElementById('totalTabelFat').innerText = totalTabelFat.toLocaleString('id-ID');
            document.getElementById('totalTabelFdt').innerText = totalTabelFdt.toLocaleString('id-ID');
            document.getElementById('totalTabelHdpe').innerText = totalTabelHdpe.toLocaleString('id-ID');
            document.getElementById('totalTabelKabel').innerText = totalTabelKabel.toLocaleString('id-ID');
            document.getElementById('totalTabelOtb').innerText = totalTabelOtb.toLocaleString('id-ID');
            document.getElementById('totalTabelTiang').innerText = totalTabelTiang.toLocaleString('id-ID');
        }

        // Tunggu sebentar agar DataTables siap sebelum memanggil updateTotalTabel()
        setTimeout(() => {
            updateTotalTabel();
        }, 500); // Tunggu 0.5 detik agar DataTables selesai inisialisasi

        // Update total saat tabel di-refresh (misalnya setelah search, filter, atau navigasi halaman)
        $('.dataTable').on('draw.dt', function() {
            updateTotalTabel();
        });

        $(".tombol_detail").click(function () {
            var selectedSJ = $(this).data("suratjalan"); // Ambil ID dari tombol yang ditekan
            var selectedunique = $(this).data("id-logistik-stok-unique"); // Ambil ID dari tombol yang ditekan
            // console.log(selectedunique);

            if (!selectedSJ || selectedSJ === "") {
                var tbody = $("#hasilDetailDataSJ");
                tbody.empty();
                document.getElementById("detail_no_surat_jalan").value = "";
                document.getElementById("detail_total_qty").innerText = "";
                document.getElementById("detail_area_gudang").value = "";
                document.getElementById("detail_nama_project").value = "";
                document.getElementById("detail_sumber_material").value = "";
                document.getElementById("detail_keterangan_stok_item").value = "";
                document.getElementById("detail_nama_file_sj").innerText = "No File Uploaded";
                document.getElementById("detail_nama_file_evidence").innerText = "No File Uploaded";
                document.getElementById("view_detail_surat_jalan").href = "#";
                document.getElementById("view_detail_surat_jalan").style.display = "none";
                document.getElementById("view_detail_evidence").href = "#";
                document.getElementById("view_detail_evidence").style.display = "none";

            } else {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Mohon tunggu, data sedang diambil...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: "<?= base_url('Dashboard_Logistik_Stok/filterDetailSuratJalan') ?>", // Arahkan ke file PHP yang menangani filtering
                    method: "POST",
                    data: {
                        no_surat_jalan: selectedunique
                    },
                    dataType: "json",
                    success: function (response) {
                        console.log("Response:", response);

                        Swal.close();
                        var tbody = $("#hasilDetailDataSJ");
                        tbody.empty();

                        var nomor = 1;
                        let baseUrl = "<?= base_url() ?>"
                        let lokasiUrl_sj = response.getDetailAreaBySJ[0].surat_jalan;
                        let lokasiUrl_evidence = response.getDetailAreaBySJ[0].evidence;
                        var totalStok = 0;

                        $.each(response.getDetailAreaBySJ, function (index, getDetailAreaBySJ) {
                            var jumlahStok = parseFloat(getDetailAreaBySJ.jumlah_stok) || 0; // Pastikan jumlahStok berupa angka
                            totalStok += jumlahStok;

                            var row = "<tr>" +
                                "<td>" + nomor++ + "</td>" +
                                "<td>" + getDetailAreaBySJ.kategori_item + "</td>" +
                                "<td>" + getDetailAreaBySJ.nama_item + "</td>" +
                                "<td>" + parseFloat(getDetailAreaBySJ.jumlah_stok).toLocaleString('id-ID') + "</td>" + // Format angka
                                "<td>" + getDetailAreaBySJ.satuan_item + "</td>" +
                                "<td>" + getDetailAreaBySJ.merk_stok + "</td>" +
                                "<td>" + getDetailAreaBySJ.no_haspel_stok + "</td>" +
                                "<td>" + getDetailAreaBySJ.no_ref_stok + "</td>" +
                                "</tr>";
                            tbody.append(row);
                        });

                        let tanggalFormatted = response.getDetailAreaBySJ[0].tanggal_upload_stok.split(" ")[0];

                        document.getElementById("detail_total_qty").innerText = totalStok.toLocaleString('id-ID');
                        document.getElementById("detail_no_surat_jalan").value = response.getDetailAreaBySJ[0].no_surat_jalan;
                        document.getElementById("detail_area_gudang").value = response.getDetailAreaBySJ[0].kota_lokasi_gudang;
                        document.getElementById("detail_nama_project").value = response.getDetailAreaBySJ[0].project_item;
                        document.getElementById("detail_sumber_material").value = response.getDetailAreaBySJ[0].nama_sumber_material;
                        document.getElementById("detail_keterangan_stok_item").value = response.getDetailAreaBySJ[0].keterangan_stok;
                        document.getElementById("detail_no_po_logistik").value = response.getDetailAreaBySJ[0].no_po_logistik;
                        document.getElementById("detail_no_pr_logistik").value = response.getDetailAreaBySJ[0].no_pr_logistik;
                        document.getElementById("tanggal_upload_stok").value = tanggalFormatted;
                        var filePath_sj = response.getDetailAreaBySJ[0].surat_jalan;
                        var filePath_evidence = response.getDetailAreaBySJ[0].evidence;

                        if (response.getDetailAreaBySJ[0].kota_lokasi_gudang === "HO") {
                            if (response.getDetailAreaBySJ[0].status_sumber_material.includes("IN")) {
                                $("#detail_ho_in_nomor_po, #detail_ho_out_blank").removeClass("d-none");
                                $("#detail_ho_out_nomor_pr").addClass("d-none");

                                $("#detail_no_pr_logistik").val("");

                            } else if (response.getDetailAreaBySJ[0].status_sumber_material.includes("OUT")) {
                                $("#detail_ho_out_nomor_pr, #detail_ho_out_blank").removeClass("d-none");
                                $("#detail_ho_in_nomor_po").addClass("d-none");

                                $("#no_po_logistik").val("");
                            } else {
                                $("#ho_in_nomor_po, #ho_out_nomor_pr, #detail_ho_out_blank").addClass("d-none");
                                $("#no_po_logistik, #no_pr_logistik").val("");
                            }
                        } else {
                            $("#detail_ho_in_nomor_po, #detail_ho_out_nomor_pr, #detail_ho_out_blank").addClass("d-none");
                            $("#detail_no_po_logistik, #detail_no_pr_logistik").val("");
                        }

                        console.log('file sj', filePath_sj);
                        console.log('file evidence', filePath_evidence);

                        var fileName_sj = filePath_sj.replace(/^.*[\\/]/, ''); // Hapus semua sebelum last "/"

                        if (filePath_evidence == "" || filePath_evidence == null) {
                            $('#container-detail-evidence').attr('hidden', true);
                        } else {
                            $('#container-detail-evidence').attr('hidden', false);
                            var fileName_evidence = filePath_evidence.replace(/^.*[\\/]/, ''); // Hapus semua sebelum last "/"
                            document.getElementById("view_detail_evidence").style.display = "block";
                            document.getElementById("detail_nama_file_evidence").innerText = fileName_evidence;
                            document.getElementById("view_detail_evidence").href = lokasiUrl_evidence != "./uploads/" ? baseUrl + lokasiUrl_evidence : "#";
                        }

                        document.getElementById("detail_nama_file_sj").innerText = fileName_sj;
                        document.getElementById("view_detail_surat_jalan").style.display = "block";
                        document.getElementById("view_detail_surat_jalan").href = baseUrl + lokasiUrl_sj;

                        // console.log("Response:", response);
                    },
                    error: function (xhr, status, error) {

                        Swal.close();

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan saat mengambil data!',
                        });
                        console.error("Error:", error);
                        console.error("Response Text:", xhr.responseText);
                    }
                });
            }
        });

        $('.tombol_hapus_rincian').on('click', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        swal({
            title: 'Apakah anda yakin',
            text: "data akan dihapus!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'DELETE',
            cancelButtonText: 'CANCEL'
        }).then((result) => {
            if (result.value) {
                document.location.href = href;
            }
        })

    });
    });

    document.addEventListener("DOMContentLoaded", function() {
        <?php foreach ($getSummaryDetailArea as $stokKategory): ?>
            var boxElement = document.getElementById("box_detail_area_<?= $stokKategory['kategori_item'] ?>");

            if (boxElement) { // Pastikan elemen ditemukan sebelum menambahkan event
                boxElement.addEventListener("click", function() {
                    console.log("<?= $stokKategory['kategori_item'] ?>");
                    <?php if ($stokKategory['kategori_item'] == 'Aksesories ') { ?>
                        var targetElement = document.getElementById("judul_stok_aksesories");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'HDPE ') { ?>
                        var targetElement = document.getElementById("judul_stok_hdpe");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'Kabel ') { ?>
                        var targetElement = document.getElementById("judul_stok_kabel");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'Closure') { ?>
                        var targetElement = document.getElementById("judul_stok_closure");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'FAT') { ?>
                        var targetElement = document.getElementById("judul_stok_fat");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'FDT') { ?>
                        var targetElement = document.getElementById("judul_stok_fdt");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'OTB ') { ?>
                        var targetElement = document.getElementById("judul_stok_otb");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'Tiang') { ?>
                        var targetElement = document.getElementById("judul_stok_tiang");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } ?>
                });
            }
        <?php endforeach; ?>
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