<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
$satuan_options = ['Batang', 'Meter', 'Pc(s)', 'Unit', 'Roll', 'Pcs'];

$jumlah_Aksesories = 0;
$jumlah_Closure = 0;
$jumlah_FAT = 0;
$jumlah_FDT = 0;
$jumlah_HDPE = 0;
$jumlah_Kabel = 0;
$jumlah_OTB = 0;
$jumlah_Tiang = 0;

?>



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
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">LOKASI
                                                GUDANG</label>
                                            <select id="filter_lokasi" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Gudang" style="width: 100%;">
                                                <?php foreach ($getUniqueKotaGudang as $data): ?>
                                                    <option value="<?php echo $data['kota_lokasi_gudang'] ?>">
                                                        <?php echo $data['kota_lokasi_gudang'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">PROJECT
                                                BOWHEER</label>
                                            <select id="filter_bowheer" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Bowheer" style="width: 100%;">
                                                <?php foreach ($getUniqueProjectLogistik as $data): ?>
                                                    <option value="<?php echo $data['nama_bowheer'] ?>">
                                                        <?php echo $data['nama_bowheer'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">JENIS
                                                ITEM</label>
                                            <select id="filter_item" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Item" style="width: 100%;">
                                                <?php foreach ($getUniqueItemLogistik as $data): ?>
                                                    <option value="<?php echo $data['nama_item'] ?>">
                                                        <?php echo $data['nama_item'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label
                                                style="display: flex; justify-content: center; align-items: center;">STATUS
                                                STOK</label>
                                            <select id="filter_status" class="select2" multiple="multiple"
                                                data-placeholder="Pilih Status Stok" style="width: 100%;">
                                                <?php foreach ($getUniqueSumberMaterial as $data): ?>
                                                    <option value="<?php echo $data['nama_sumber_material'] ?>">
                                                        <?php echo $data['nama_sumber_material'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer col-sm-12">
                                        <button type="button" id="reset_filter" class="btn btn-danger"
                                            data-dismiss="modal">Hapus</button>
                                        <button id="btnFilterDataProject" class="btn btn-primary"><i
                                                class="fa fa-spinner fa-spin loading" style="display:none"></i> Cari
                                        </button>
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
                            <h3 class="card-title">TOTAL MATERIAL AREA</h3>

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
                                    <?php foreach ($getAllStokByKategory as $stokKategory): ?>
                                        <div class="col-lg-3 col-6">
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
                                        <div class="col-sm-12">
                                            <h1 class="m-0 text-dark" style="text-align: center;">LOGISTIK REGIONAL</h1>
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
                                                    <table id="table_detail_regional"
                                                        class="table table-bordered table-hover">
                                                        <thead class="bg-info">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Lokasi Gudang</th>
                                                                <th>Aksesoris</th>
                                                                <th>Closure</th>
                                                                <th>FAT</th>
                                                                <th>FDT</th>
                                                                <th>HPDE</th>
                                                                <th>Kabel</th>
                                                                <th>OTB</th>
                                                                <th>Tiang</th>
                                                                <th>Detail</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $total = 1;

                                                            foreach ($getAllStokByKategoryFilterRegional as $data):

                                                                $jumlah_Aksesories += $data['jumlah_Aksesories'];
                                                                $jumlah_Closure += $data['jumlah_Closure'];
                                                                $jumlah_FAT += $data['jumlah_FAT'];
                                                                $jumlah_FDT += $data['jumlah_FDT'];
                                                                $jumlah_HDPE += $data['jumlah_HDPE'];
                                                                $jumlah_Kabel += $data['jumlah_Kabel'];
                                                                $jumlah_OTB += $data['jumlah_OTB'];
                                                                $jumlah_Tiang += $data['jumlah_Tiang'];

                                                            ?>

                                                                <tr>
                                                                    <td><?= $total++ ?></td>
                                                                    <td><?= $data['regional_lokasi_gudang'] ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Aksesories'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Aksesories']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Closure'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Closure']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_FAT'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_FAT']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_FDT'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_FDT']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_HDPE'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_HDPE']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Kabel'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Kabel']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_OTB'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_OTB']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                    if ($data['jumlah_Tiang'] == "0") {
                                                                        echo "-";
                                                                    } else {
                                                                        echo number_format(floatval($data['jumlah_Tiang']), 0, ".");
                                                                    }
                                                                    ?></td>
                                                                    <td>
                                                                        <a href="<?php echo site_url('Logistik_Stok_Detail/detail/' . $data['regional_lokasi_gudang']); ?>"
                                                                            id="tombol_hapus"
                                                                            class="btn btn-primary tombol_hapus"><i
                                                                                class=" fas fa-eye"></i></a>
                                                                    </td>
                                                                </tr>

                                                            <?php endforeach; ?>

                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="2">Total</th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Aksesories), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Closure), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_FAT), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_FDT), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_HDPE), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Kabel), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_OTB), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Tiang), 0, ".") ?>
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
                                            <h1 class="m-0 text-dark" style="text-align: center;">LOGISTIK AREA</h1>
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
                                                    <table id="table_detail_kota"
                                                        class="table table-bordered table-hover">
                                                        <thead class="bg-info">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Lokasi Gudang</th>
                                                                <th>Aksesoris</th>
                                                                <th>Closure</th>
                                                                <th>FAT</th>
                                                                <th>FDT</th>
                                                                <th>HPDE</th>
                                                                <th>Kabel</th>
                                                                <th>OTB</th>
                                                                <th>Tiang</th>
                                                                <th>Detail</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $total = 1;

                                                            $jumlah_Aksesories = 0;
                                                            $jumlah_Closure = 0;
                                                            $jumlah_FAT = 0;
                                                            $jumlah_FDT = 0;
                                                            $jumlah_HDPE = 0;
                                                            $jumlah_Kabel = 0;
                                                            $jumlah_OTB = 0;
                                                            $jumlah_Tiang = 0;

                                                            foreach ($getAllStokByKategoryFilterCity as $data):


                                                                $jumlah_Aksesories += $data['jumlah_Aksesories'];
                                                                $jumlah_Closure += $data['jumlah_Closure'];
                                                                $jumlah_FAT += $data['jumlah_FAT'];
                                                                $jumlah_FDT += $data['jumlah_FDT'];
                                                                $jumlah_HDPE += $data['jumlah_HDPE'];
                                                                $jumlah_Kabel += $data['jumlah_Kabel'];
                                                                $jumlah_OTB += $data['jumlah_OTB'];
                                                                $jumlah_Tiang += $data['jumlah_Tiang'];

                                                            ?>

                                                                <tr>
                                                                    <td><?= $total++ ?></td>
                                                                    <td><?= $data['kota_lokasi_gudang'] ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Aksesories'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Aksesories']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Closure'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Closure']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_FAT'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_FAT']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_FDT'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_FDT']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_HDPE'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_HDPE']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Kabel'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Kabel']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_OTB'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_OTB']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td><?php
                                                                        if ($data['jumlah_Tiang'] == "0") {
                                                                            echo "-";
                                                                        } else {
                                                                            echo number_format(floatval($data['jumlah_Tiang']), 0, ".");
                                                                        }
                                                                        ?></td>
                                                                    <td>
                                                                        <a href="<?php echo site_url('Logistik_Stok_Detail/detail/' . $data['kota_lokasi_gudang']); ?>"
                                                                            id="tombol_hapus"
                                                                            class="btn btn-primary tombol_hapus"><i
                                                                                class=" fas fa-eye"></i></a>
                                                                    </td>
                                                                </tr>

                                                            <?php endforeach; ?>

                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="2">Total</th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Aksesories), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Closure), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_FAT), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_FDT), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_HDPE), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Kabel), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_OTB), 0, ".") ?>
                                                                </th>
                                                                <th colspan="1">
                                                                    <?= number_format(floatval($jumlah_Tiang), 0, ".") ?>
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

                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                        foreach ($getAllStokLogistik as $data):
                                        ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
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

        <!-- MODAL TAMBAH DATA -->

        <form method="post" id="form_tambah_stok" action="<?php echo site_url('Dashboard_Logistik_Stok/tambahReportStokLogistik'); ?>" enctype="multipart/form-data">
            <div class="modal fade" id="modal-xl-tambah" data-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Report Stok Logistik</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Nomor Surat Jalan</label>
                                        <input type="text" class="form-control" name="nomor_surat_jalan" id="nomor_surat_jalan" autocomplete="off"
                                            value="" placeholder="tec.005/TKM-01/II/2025" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Input Date</label>
                                        <input type="date" class="form-control" name="tanggal_upload_stok" autocomplete="off"
                                            value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Area Gudang</label>
                                        <select name="id_lokasi_gudang" id="id_lokasi_gudang" class="form-control">
                                            <option value="">Pilih Salah Satu</option>
                                            <?php foreach ($getListGudangLokasiUser as $data2): ?>
                                                <option value="<?php echo $data2['id_lokasi_gudang'] ?>"><?php echo $data2['kota_lokasi_gudang'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Project</label>
                                        <select name="id_bowheer" id="id_project" class="form-control">
                                            <option value="">Pilih Salah Satu</option>
                                            <?php foreach ($getMasterProject as $data2): ?>
                                                <option value="<?php echo $data2['id_bowheer'] ?>" data-id-bowheer="<?php echo $data2['nama_bowheer'] ?>">
                                                    <?php echo $data2['nama_bowheer'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Sumber Material</label>
                                        <select name="id_sumber_material" id="id_sumber_material" class="form-control">
                                            <option value="">Pilih Salah Satu</option>
                                            <?php foreach ($getMasterSumberMaterial as $data2): ?>
                                                <option value="<?php echo $data2['id_sumber_material'] ?>">
                                                    <?php echo $data2['status_sumber_material'] ?> -
                                                    <?php echo $data2['nama_sumber_material'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Jenis Material</label>
                                        <select class="form-control select2" id="id_kode_item" name="id_kode_item">
                                            <option value="">Pilih Jenis Material</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="table_item_stok">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th>Nama</th>
                                                <th>Qty</th>
                                                <th>Satuan Item</th>
                                                <th>Merk Item</th>
                                                <th>No Haspel</th>
                                                <th>No Ref</th>
                                                <th>Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Keterangan</label>
                                        <textarea type="text" class="form-control" name="keterangan_stok" id="keterangan_stok_item"
                                            autocomplete="Keterangan"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Upload Surat Jalan</label>
                                        <label>Upload Dokument</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <label class="custom-file-label" for="file-sj">Choose file</label>
                                                <input type="file" name="file" id="file-sj" class="custom-file-input" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- MODAL TAMBAH LOKASI GUDANG LOGISTIK -->
        <form action=" <?php echo base_url('Dashboard_Logistik_Stok/tambahReportStokLogistik') ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-old">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Report Stok</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-form-label">Input Date</label>
                                <input type="date" class="form-control" name="tanggal_upload_stok" autocomplete="off"
                                    value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Area Gudang</label>
                                <select name="id_lokasi_gudang" class="form-control">
                                    <?php foreach ($getListGudangLokasiUser as $data2): ?>
                                        <option value="<?php echo $data2['id_lokasi_gudang'] ?>">
                                            <?php echo $data2['kota_lokasi_gudang'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Project</label>
                                <select name="id_bowheer" class="form-control">
                                    <?php foreach ($getMasterProject as $data2): ?>
                                        <option value="<?php echo $data2['id_bowheer'] ?>">
                                            <?php echo $data2['nama_bowheer'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Sumber Material</label>
                                <select name="id_sumber_material" class="form-control">
                                    <?php foreach ($getMasterSumberMaterial as $data2): ?>
                                        <option value="<?php echo $data2['id_sumber_material'] ?>">
                                            <?php echo $data2['status_sumber_material'] ?> -
                                            <?php echo $data2['nama_sumber_material'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Jenis Material</label>
                                <select class="form-control select2" name="id_kode_item">
                                    <?php foreach ($getMasterKodeItem as $data): ?>
                                        <option value="<?php echo $data['id_kode_item'] ?>">
                                            <?php echo $data['nama_item'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Qty Item</label>
                                <input type="number" class="form-control" name="jumlah_stok" autocomplete="off"
                                    placeholder="1.000">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Jenis Satuan Item</label>
                                <select name="satuan_stok" class="form-control">
                                    <?php foreach ($satuan_options as $option): ?>
                                        <option value="<?= $option ?>" <?= isset($data['satuan_item']) && $data['satuan_item'] == $option ? 'selected' : '' ?>>
                                            <?= $option ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Merk Item</label>
                                <input type="text" class="form-control" name="merk_stok" autocomplete="off"
                                    placeholder="' Furukawa ' / ' ZTT ' / ' CCSI '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">No Haspel</label>
                                <input type="text" class="form-control" name="no_haspel_stok" autocomplete="off"
                                    placeholder="' D11-11*** '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">No Ref</label>
                                <input type="text" class="form-control" name="no_ref_stok" autocomplete="off"
                                    placeholder="' 1***/EMR/NRO-GDR/02/2025 '">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Keterangan</label>
                                <input type="text" class="form-control" name="keterangan_stok"
                                    autocomplete="Keterangan">
                            </div>
                            <div class="form-group">
                                <label>Upload Surat Jalan</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <label class="custom-file-label" for="evidence_stok">Choose file</label>
                                        <input type="file" name="evidence_stok" id="evidence_stok"
                                            class="custom-file-input" required>
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

</div>
<!-- /.content-wrapper -->

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<script type="text/javascript">
    // START LOGIC TAMBAH STOK ITEM

    // AJAX SELECT 2 JENIS MATERIAL

    $(document).ready(function() {
        $('#id_kode_item').select2({
            placeholder: "Pilih Jenis Material",
            allowClear: true
        });

        $('#id_kode_item').on('change', function() {
            if ($(this).val() === "") {
                return;
            }
            var selectedValue = $('#id_kode_item').val();
            var selectedText = $('#id_kode_item option:selected').text();
            var selectedSatuan = $('#id_kode_item option:selected').data('satuan-item');

            if (selectedValue === "") {
                alert("Pilih item terlebih dahulu!");
                return;
            }

            $('#table_item_stok tbody').append(`
                <tr>
                    <td>${counter}</td>
                    <td hidden><input type="hidden" name="id_kode_item[${counter}]" value="${selectedValue}">${selectedValue}</td>
                    <td>${selectedText}</td>
                    <td><input type="number" class="form-control" name="jumlah_stok[${counter}]" autocomplete="off" placeholder="1.000" required></td>
                    <td><input type="text" class="form-control disabled" name="satuan_stok[${counter}]" autocomplete="off" value="${selectedSatuan}" readonly></td>
                    <td><input type="text" class="form-control" name="merk_item[${counter}]" autocomplete="off" placeholder="' Furukawa ' / ' ZTT ' / ' CCSI '" required></td>
                    <td><input type="text" class="form-control" name="no_haspel_item[${counter}]" autocomplete="off" placeholder="' D11-11*** '" required></td>
                    <td><input type="text" class="form-control" name="no_ref_item[${counter}]" autocomplete="off" placeholder="' 1***/EMR/NRO-GDR/02/2025 '" required></td>
                    <td><button class="btn btn-danger hapus-item"><i class="fa fa-trash"></i></button></td>
                </tr>
            `);
            counter++;

            $('#id_kode_item').val("").trigger('change');
        });

        $('#id_project').change(function() {
            $('#table_item_stok tbody').empty();
            counter = 1;
            let idBowheer = $(this).find(':selected').data('id-bowheer');

            if (idBowheer === "") {
                $('#id_kode_item').empty().append('<option value="">Pilih Jenis Material</option>').trigger('change');
                return;
            }

            $.ajax({
                url: "<?= base_url() . 'Dashboard_Logistik_Stok/getProjectByBowheer' ?>",
                type: "GET",
                data: {
                    id_bowheer: idBowheer.toString()
                },
                dataType: "json",
                success: function(response) {

                    $('#id_kode_item').empty().append('<option value="">Pilih Jenis Material</option>');

                    $.each(response, function(index, project) {
                        $('#id_kode_item').append(
                            `<option value="${project.id_kode_item}" data-satuan-item="${project.satuan_item}">${project.nama_item}</option>`
                        );
                    });

                    $('#id_kode_item').trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        var counter = 1;

        $(document).on('click', '.hapus-item', function() {
            $(this).closest('tr').remove();

            $('#table_item_stok tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });

            counter--;
        });

        $('#modal-xl-tambah').on('hidden.bs.modal', function() {
            $('#table_item_stok tbody').empty();
            $('#nomor_surat_jalan').val('');
            $('#id_project').val('').trigger('change');
            $('#id_lokasi_gudang').val('').trigger('change');
            $('#id_sumber_material').val('').trigger('change');
            counter = 1;
        });

        $('.btn-tambah-data-item').on('click', function() {
            $('#file-sj').val('');
            $('.custom-file-label').text('Choose file');
            $('#keterangan_stok_item').val('');
        })

        $('#file-sj').on('change', function(e) {
            var fileName = e.target.files.length > 0 ? e.target.files[0].name : "Choose file";
            $(this).siblings('.custom-file-label').text(fileName);
        });

        $("#file-sj").change(function() {
            let file = this.files[0];
            let allowedExtensions = /(\.pdf|\.docx|\.xlsx)$/i;
            let maxSize = 5120 * 1024;

            if (file) {
                if (!allowedExtensions.test(file.name)) {
                    alert("File harus berupa PDF, DOCX, atau XLSX!");
                    $(this).val("");
                    $('.custom-file-label').text('Choose file');
                    return;
                }

                if (file.size > maxSize) {
                    alert("Ukuran file tidak boleh lebih dari 5MB!");
                    $(this).val("");
                    $('.custom-file-label').text('Choose file');
                    return;
                }
            }
        });

        $("#form_tambah_stok").submit(function(event) {
            let isValid = true;
            let errorMessage = [];

            // Cek input tanggal harus valid
            let tanggalUpload = $("input[name='tanggal_upload_stok']").val();
            if (!tanggalUpload) {
                errorMessage.push("Tanggal upload stok harus diisi.");
            }

            // Cek setiap input yang harus memiliki nilai
            let requiredFields = {
                "#id_lokasi_gudang": "Lokasi gudang",
                "#id_project": "Proyek",
                "#id_sumber_material": "Sumber material"
            };

            $.each(requiredFields, function(selector, fieldName) {
                if ($(selector).val() === "") {
                    errorMessage.push(fieldName + " harus dipilih.");
                }
            });

            // Cek apakah tabel memiliki minimal 1 row
            if ($("#table_item_stok tbody tr").length === 0) {
                errorMessage.push("Minimal harus ada satu item stok dalam tabel.");
            }

            // Jika ada error, tampilkan alert sekaligus
            if (errorMessage.length > 0) {
                alert(errorMessage.join("\n"));
                event.preventDefault();
            }
        });
    });

    // END LOGIC OF MODAL TAMBAH STOK 

    $(function() {

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

        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    })

    $(document).ready(function () {
        $('#table_detail_kota').DataTable({
            "paging": true,          // Tetap gunakan pagination
            "pageLength": 10,        // Menampilkan 10 data per halaman
            "info": false,           // Menghilangkan "Showing 1 to X of X entries"
            "searching": true,      // Menghilangkan search bar
            "lengthChange": false    // Menghilangkan dropdown "Show entries"
        });
    });


    // HAPUS FILTER DATA SELECT2
    document.getElementById('reset_filter').addEventListener('click', function() {
        const selectLokasi = document.getElementById('filter_lokasi');
        const selectBowheer = document.getElementById('filter_bowheer');
        const selectItem = document.getElementById('filter_item');
        const selectStatus = document.getElementById('filter_status');

        const optionsLokasi = selectLokasi.options;
        const optionsBowheer = selectBowheer.options;
        const optionsItem = selectItem.options;
        const optionsStatus = selectStatus.options;

        // Hapus semua pilihan
        for (let i = 0; i < optionsLokasi.length; i++) {
            optionsLokasi[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsBowheer.length; i++) {
            optionsBowheer[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsItem.length; i++) {
            optionsItem[i].selected = false; // Hilangkan pilihan
        }

        for (let i = 0; i < optionsStatus.length; i++) {
            optionsStatus[i].selected = false; // Hilangkan pilihan
        }

        // Pilih opsi default (indeks 0)
        selectLokasi.dispatchEvent(new Event('change'));
        selectBowheer.dispatchEvent(new Event('change'));
        selectItem.dispatchEvent(new Event('change'));
        selectStatus.dispatchEvent(new Event('change'));
    });

    // FUNCTION UPDATE DATA TABLE MENGGUNAKAN FILTER DATA
    $(document).ready(function() {
        $('#btnFilterDataProject').on('click', function() {
            var table = $('#table_data').DataTable();
            // Ambil nilai dari multiple select filter kategori
            var selectLokasi = $('#filter_lokasi').val() || []; // Array of selected values
            var selectBowheer = $('#filter_bowheer').val() || []; // Array of selected values
            var selectItem = $('#filter_item').val() || []; // Array of selected values
            var selectStatus = $('#filter_status').val() || []; // Array of selected values

            // Gabungkan nilai ke dalam regex untuk pencarian DataTable
            var lokasiFilter = selectLokasi.length > 0 ? selectLokasi.join('|') : '';
            var bowheerFilter = selectBowheer.length > 0 ? selectBowheer.join('|') : '';
            var itemFilter = selectItem.length > 0 ? selectItem.join('|') : '';
            var statusFilter = selectStatus.length > 0 ? selectStatus.join('|') : '';

            // Terapkan filter ke DataTable
            table
                .column(1).search(lokasiFilter, true, false) // Filter kategori (regex search)
                .column(2).search(bowheerFilter, true, false) // Filter kategori (regex search)
                .column(4).search(itemFilter, true, false) // Filter kategori (regex search)
                .column(5).search(statusFilter, true, false) // Filter kategori (regex search)
                .draw(); // Render ulang tabel

        });
    });

    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';
        $('#table_data').DataTable({
            responsive: false // Matikan fitur Responsive
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