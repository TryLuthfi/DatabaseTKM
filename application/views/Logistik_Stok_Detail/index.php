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
                                <?= number_format(floatval($stokKategory['total_jumlah_stok']), 0, ".") . " " . $stokKategory['satuan_item'] ?>
                            </h3>


                            <p><?= $stokKategory['kategori_item'] ?></p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
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
                                    <h3 class="card-title">Stok Aksesories</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_aksesories, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok Closure</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_closure, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok FAT</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_fat, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok FDT</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_fdt, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok HDPE</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_hdpe, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok Kabel</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_kabel, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok OTB</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_otb, 0, '.', ',') ?></th>
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
                                    <h3 class="card-title">Stok Tiang</h3>
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
                                                <td><?= number_format($data['jumlah_stok'], 0, '.', ',') ?></td>
                                                <td><?= $data['satuan_item'] ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th colspan="1"></th>
                                        <th colspan="1"><?= number_format($total_tiang, 0, '.', ',') ?></th>
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
                    <h1 class="m-0 text-dark" style="text-align: center;">HISTORY IN OUT MATERIAL <?= "" . $lokasi ?></h1>
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
                                        <h3 class="card-title">List Stok Logistik </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold btn-tambah-data-item"
                                            data-target="#modal-xl-tambah" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
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
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                        $total = 1;
                                        foreach ($getHistoriInOUtLogistik as $data):
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
                                                <td>
                                                    <a href="<?php echo site_url('Dashboard_Logistik_Stok/hapusReportStokLogistik/' . $data['id_logistik_stok']); ?>"
                                                        id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                            class=" fas fa-trash"></i></a>
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



</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<script>
    $(function () {
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

    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($getSummaryDetailArea as $stokKategory): ?>
            var boxElement = document.getElementById("box_detail_area_<?= $stokKategory['kategori_item'] ?>");

            if (boxElement) { // Pastikan elemen ditemukan sebelum menambahkan event
                boxElement.addEventListener("click", function () {
                    console.log("<?= $stokKategory['kategori_item'] ?>");
                    <?php if ($stokKategory['kategori_item'] == 'Aksesories ') { ?>
                        var targetElement = document.getElementById("judul_stok_aksesories");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({ behavior: "smooth" });
                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'HDPE ') { ?>
                            var targetElement = document.getElementById("judul_stok_hdpe");

                            if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                targetElement.scrollIntoView({ behavior: "smooth" });
                            }
                    <?php } else if ($stokKategory['kategori_item'] == 'Kabel ') { ?>
                                var targetElement = document.getElementById("judul_stok_kabel");

                                if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                    targetElement.scrollIntoView({ behavior: "smooth" });
                                }
                    <?php } else if ($stokKategory['kategori_item'] == 'Closure') { ?>
                                    var targetElement = document.getElementById("judul_stok_closure");

                                    if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                        targetElement.scrollIntoView({ behavior: "smooth" });
                                    }
                    <?php } else if ($stokKategory['kategori_item'] == 'FAT') { ?>
                                        var targetElement = document.getElementById("judul_stok_fat");

                                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                            targetElement.scrollIntoView({ behavior: "smooth" });
                                        }
                    <?php } else if ($stokKategory['kategori_item'] == 'FDT') { ?>
                                            var targetElement = document.getElementById("judul_stok_fdt");

                                            if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                                targetElement.scrollIntoView({ behavior: "smooth" });
                                            }
                    <?php } else if ($stokKategory['kategori_item'] == 'OTB ') { ?>
                                                var targetElement = document.getElementById("judul_stok_otb");

                                                if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                                    targetElement.scrollIntoView({ behavior: "smooth" });
                                                }
                    <?php } else if ($stokKategory['kategori_item'] == 'Tiang') { ?>
                                                    var targetElement = document.getElementById("judul_stok_tiang");

                                                    if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                                        targetElement.scrollIntoView({ behavior: "smooth" });
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