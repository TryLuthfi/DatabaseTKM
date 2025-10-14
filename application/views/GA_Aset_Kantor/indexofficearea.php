<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$option_aktif = ['AKTIF', 'HILANG', 'TERJUAL'];


$total = 1;
?>

<div class="content-wrapper">

    <div class="container-fluid">
        <div class="row">

            <!-- ====================== MOBIL (KIRI) ====================== -->
            <div class="col-md-12 mt-4">
                <div class="p-3 mb-4 shadow-sm rounded" style="background-color: #bbc1c754;">
                    <div class="row">
                        <?php foreach ($getCountAsetOfficeByKota as $stokOffice): ?>

                            <!-- TOTAL MOBIL -->
                            <div class="col-lg-3 col-6 mb-3"
                                id="<?php echo 'box_detail_aset_office_' . $stokOffice['ka_jenis_aset'] ?>">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= number_format($stokOffice['total_data'], 0, ",", ".") ?>
                                        </h3>
                                        <p><?= $stokOffice['ka_jenis_aset'] ?></p>
                                    </div>
                                    <div class="icon"><i class="ion ion-bag"></i></div>
                                    <a href="#" class="small-box-footer"
                                        id="<?php echo 'box_detail_office_' . $stokOffice['ka_jenis_aset'] ?>">
                                        Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="content">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12" id="judul_aset_laptop">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            LAPTOP AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_laptop" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'LAPTOP'):

                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelLaptop">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_printer">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            PRINTER AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_printer" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'PRINTER'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelPrinter">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_scanner">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            SCANNER AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_scanner" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'SCANNER'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelScanner">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_markom">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            MARKOM AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_markom" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'MARKOM'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelMarkom">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_drafter">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            DRAFTER AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_drafter" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'DRAFTER'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelDrafter">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_hardisk">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            HARDISK AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_hardisk" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'HARDISK'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelHardisk">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_handphone">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            HANDPHONE AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_handphone" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'HANDPHONE'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelHandphone">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
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
                    <div class="col-sm-12" id="judul_aset_cuttingplotter">
                        <h1 class="m-0 text-dark" style="text-align: center;">DISTRIBUSI
                            CUTTING PLOTTER AREA - <?= strtoupper($filterURL) ?></h1>
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
                                        <h3 class="card-title">List Dashboard Kode Aset </h3>
                                    </div>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-success float-right text-bold"
                                            data-target="#modal-tambah-aset-kantor" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_cuttinplotter" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Aset</th>
                                            <th>Jenis Aset</th>
                                            <th>Merk</th>
                                            <th>Type</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 1;
                                        foreach ($getMasterAsetOfficeArea as $data):
                                            if ($data['ka_jenis_aset'] == 'CUTTING PLOTTER'):
                                                ?>
                                                <tr>
                                                    <td><?= $total++ ?></td>
                                                    <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?></td>
                                                    <td><?= $data['ka_jenis_aset'] ?></td>
                                                    <td><?= $data['ao_merk'] ?></td>
                                                    <td><?= $data['ao_type'] ?></td>
                                                    <td><?= $data['ao_pic'] ?></td>
                                                    <td><?= $data['ao_area'] ?></td>
                                                    <td>
                                                        <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                            id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                class=" fas fa-trash"></i></a>
                                                        <a href="#" class="btn btn-warning"
                                                            data-target="#modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                        <a href="#" class="btn btn-primary"
                                                            data-target="#modal-view-Office<?= $data['ao_id_list_office'] ?>"
                                                            data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>

                                                <?php
                                            endif;
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelCuttingPlotter">0</span>
                                            <th colspan="5"></span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
        </section>

        <!-- MODAL VIEW DETAIL ASET KANTOR -->
        <?php $tgl = date('Y-m-d'); ?>
        <?php foreach ($getMasterAsetOfficeArea as $data):

            if (!empty($data['ao_date_last_cek'])) {
                $tanggal_cek_formao_indo = date('d F Y', strtotime($data['ao_date_last_cek']));
            } else {
                $tanggal_cek_formao_indo = '';
            }

            if (!empty($data['ao_date_input'])) {
                $tanggal_input_formao_indo = date('d F Y', strtotime($data['ao_date_input']));
            } else {
                $tanggal_input_formao_indo = '';
            }

            ?>
            <div class="modal fade" id="modal-view-Office<?= $data['ao_id_list_office'] ?>" tabindex="-1" role="dialog"
                aria-labelledby="modal-tambah-label" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">DETAIL DATA ASET KANTOR</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <?php
                        ?>
                        <div class="modal-body" style="background-color:rgb(247, 243, 243);">
                            <section class="content">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h3 class="mx-3">
                                                <?= $data['ka_jenis_aset'] . ' ' . $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?>
                                            </h3>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <input type="hidden" name="ao_id_list_office"
                                            value="<?= $data['ao_id_list_office'] ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Merk</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_merk'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Type</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_type'] ?>" readonly
                                                        style="font-weight: bold;">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Serial Number</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_serial_number'] ?>" readonly
                                                        style="font-weight: bold;">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Spesifikasi</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_spesifikasi'] ?>" readonly
                                                        style="font-weight: bold;">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Kondisi</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_kondisi_aset'] ?>" readonly
                                                        style="font-weight: bold;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3 mt-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">PIC & LOKASI</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">PIC Aset</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_pic'] ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Regional</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_regional'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Area</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ao_area'] ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">EVIDENCE & FILE PENDUKUNG</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Lihat Foto Alat</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surao_jalan"
                                                                            target="_blank"><u>View
                                                                                Folder</u></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>BA / FIle Lain</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surao_jalan"
                                                                            target="_blank"><u>View
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
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">INFORMASI LAIN</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-form-label">Status Aset</label>
                                                    <select name="ak_status_aset" class="form-control">
                                                        <?php foreach ($option_aktif as $option): ?>
                                                            <option value="<?= $option ?>" <?= isset($data['ao_status_aset']) && $data['ao_status_aset'] == $option ? 'selected' : '' ?>>
                                                                <?= $option ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-form-label">Keterangan</label>
                                                    <textarea class="form-control"
                                                        name="ao_keterangan_aset"><?= $data['ao_keterangan_aset'] ?></textarea>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">TANGGAL UPDATE</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tahun Perolehan</label>
                                                    <input type="text" class="form-control" name="ao_tahun_perolehan"
                                                        autocomplete="off" value="<?= $data['ao_tahun_perolehan'] ?>"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal Opname Terakhir</label>
                                                    <input type="text" class="form-control" name="ao_date_last_cek"
                                                        value="<?= $tanggal_cek_formao_indo ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal Input Aplikasi</label>
                                                    <input type="text" class="form-control" name="ao_date_input"
                                                        value="<?= $tanggal_input_formao_indo ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </section>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Oke</button>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- MODAL EDIT DETAIL ASET KANTOR -->
        <?php $tgl = date('Y-m-d'); ?>
        <?php foreach ($getMasterAsetOfficeArea as $data):

            if (!empty($data['ao_date_last_cek'])) {
                $tanggal_cek_formao_indo = date('d F Y', strtotime($data['ao_date_last_cek']));
            } else {
                $tanggal_cek_formao_indo = '';
            }

            if (!empty($data['ao_date_input'])) {
                $tanggal_input_formao_indo = date('d F Y', strtotime($data['ao_date_input']));
            } else {
                $tanggal_input_formao_indo = '';
            }

            ?>
            <form action="<?php echo site_url('GA_Aset_Kantor/editAsetKantor/' . $data['ao_id_list_office']); ?>"
                method="post">
                <div class="modal fade" id="modal-edit-aset_kantor<?= $data['ao_id_list_office'] ?>" tabindex="-1"
                    role="dialog" aria-labelledby="modal-tambah-label" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">EDIT DATA ASET KANTOR</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <?php
                            ?>
                            <div class="modal-body" style="background-color:rgb(247, 243, 243);">
                                <section class="content">
                                    <div class="card">
                                        <div class="card-body">
                                            <input type="hidden" name="ao_id_list_office"
                                                value="<?= $data['ao_id_list_office'] ?>">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h3 class="mx-3">
                                                    <?= $data['ka_jenis_aset'] . ' ' . $data['ka_nama_kode_aset'] . '-' . $data['ao_sort'] ?>
                                                </h3>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="hidden" name="ao_id_list_office"
                                                            value="<?= $data['ao_id_list_office'] ?>">
                                                        <label class="col-form-label">Jenis Aset</label>
                                                        <select name="ka_id_kode_aset" class="form-control">
                                                            <option value="25" <?php if ($data['ka_jenis_aset'] == 'LAPTOP') { ?>selected <?php } ?>>LAPTOP</option>
                                                            <option value="26" <?php if ($data['ka_jenis_aset'] == 'PRINTER') { ?>selected <?php } ?>>PRINTER</option>
                                                            <option value="27" <?php if ($data['ka_jenis_aset'] == 'SCANNER') { ?>selected <?php } ?>>SCANNER</option>
                                                            <option value="28" <?php if ($data['ka_jenis_aset'] == 'MARKOM') { ?>selected <?php } ?>>MARKOM</option>
                                                            <option value="29" <?php if ($data['ka_jenis_aset'] == 'DRAFTER') { ?>selected <?php } ?>>DRAFTER</option>
                                                            <option value="30" <?php if ($data['ka_jenis_aset'] == 'HARDISK') { ?>selected <?php } ?>>HARDISK</option>
                                                            <option value="31" <?php if ($data['ka_jenis_aset'] == 'HANDPHONE') { ?>selected <?php } ?>>HANDPHONE</option>
                                                            <option value="32" <?php if ($data['ka_jenis_aset'] == 'CUTTING PLOTTER') { ?>selected <?php } ?>>CUTTING PLOTTER</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Merk Aset</label>
                                                        <input type="text" class="form-control" name="ao_merk"
                                                            autocomplete="off" value="<?= $data['ao_merk'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Type</label>
                                                        <input type="text" class="form-control" name="ao_type"
                                                            autocomplete="off" value="<?= $data['ao_type'] ?>"
                                                            style="font-weight: bold;">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Serial Number</label>
                                                        <input type="text" class="form-control" name="ao_serial_number"
                                                            autocomplete="off" value="<?= $data['ao_serial_number'] ?>"
                                                            style="font-weight: bold;">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Spesifikasi</label>
                                                        <input type="text" class="form-control" name="ao_spesifikasi"
                                                            autocomplete="off" value="<?= $data['ao_spesifikasi'] ?>"
                                                            style="font-weight: bold;">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Kondisi Aset</label>
                                                        <select name="ao_kondisi_aset" class="form-control">
                                                            <option value="BAIK" <?php if ($data['ao_kondisi_aset'] == 'BAIK') { ?>selected <?php } ?>>BAIK
                                                            </option>
                                                            <option value="RUSAK" <?php if ($data['ao_kondisi_aset'] == 'RUSAK') { ?>selected <?php } ?>>
                                                                RUSAK
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center mb-3 mt-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h5 class="mx-3">PIC & LOKASI</h5>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">PIC Aset</label>
                                                        <input type="text" class="form-control" name="ao_pic"
                                                            autocomplete="off" value="<?= $data['ao_pic'] ?>" s>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Regional Kendaraan</label>
                                                        <select name="ao_regional" class="form-control">
                                                            <option value="" <?php if ($data['ao_regional'] == '') { ?>selected <?php } ?>>Pilih Regional</option>
                                                            <option value="REGIONAL 1" <?php if ($data['ao_regional'] == 'REGIONAL 1') { ?>selected <?php } ?>>REGIONAL 1</option>
                                                            <option value="REGIONAL 2" <?php if ($data['ao_regional'] == 'REGIONAL 2') { ?>selected <?php } ?>>REGIONAL 2</option>
                                                            <option value="REGIONAL 3" <?php if ($data['ao_regional'] == 'REGIONAL 3') { ?>selected <?php } ?>>REGIONAL 3</option>
                                                            <option value="REGIONAL 4" <?php if ($data['ao_regional'] == 'REGIONAL 4') { ?>selected <?php } ?>>REGIONAL 4</option>
                                                            <option value="REGIONAL 5" <?php if ($data['ao_regional'] == 'REGIONAL 5') { ?>selected <?php } ?>>REGIONAL 5</option>
                                                    </div>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Area</label>
                                                    <input type="text" class="form-control" name="ao_area"
                                                        autocomplete="off" value="<?= $data['ao_area'] ?>" s>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-grow-1 border-top"></div>
                                        <h5 class="mx-3">EVIDENCE & FILE PENDUKUNG</h5>
                                        <div class="flex-grow-1 border-top"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Lihat Foto Alat</label>
                                                <div class="card">
                                                    <div class="card-body p-6">
                                                        <div class="">
                                                            <div class="d-flex align-items-center overflow-hidden">

                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-15 mb-1 text-truncate"
                                                                        id="detail_nama_file_sj"></h5>
                                                                    <a href="" class="font-size-14 text-muted text-truncate"
                                                                        id="view_detail_surao_jalan" target="_blank"><u>View
                                                                            Folder</u></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>BA / FIle Lain</label>
                                                <div class="card">
                                                    <div class="card-body p-6">
                                                        <div class="">
                                                            <div class="d-flex align-items-center overflow-hidden">

                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-15 mb-1 text-truncate"
                                                                        id="detail_nama_file_sj"></h5>
                                                                    <a href="" class="font-size-14 text-muted text-truncate"
                                                                        id="view_detail_surao_jalan" target="_blank"><u>View
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
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-grow-1 border-top"></div>
                                        <h5 class="mx-3">INFORMASI LAIN</h5>
                                        <div class="flex-grow-1 border-top"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-form-label">Status Aset</label>
                                                <select name="ak_status_aset" class="form-control">
                                                    <?php foreach ($option_aktif as $option): ?>
                                                        <option value="<?= $option ?>" <?= isset($data['ao_status_aset']) && $data['ao_status_aset'] == $option ? 'selected' : '' ?>>
                                                            <?= $option ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-form-label">Keterangan</label>
                                                <textarea class="form-control"
                                                    name="ao_keterangan_aset"><?= $data['ao_keterangan_aset'] ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-grow-1 border-top"></div>
                                        <h5 class="mx-3">TANGGAL UPDATE</h5>
                                        <div class="flex-grow-1 border-top"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Tahun Perolehan</label>
                                                <input type="text" class="form-control" name="ao_tahun_perolehan"
                                                    autocomplete="off" value="<?= $data['ao_tahun_perolehan'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Tanggal Opname Terakhir</label>
                                                <input type="text" class="form-control" name="ao_date_last_cek"
                                                    value="<?= $tanggal_cek_formao_indo ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Tanggal Input Aplikasi</label>
                                                <input type="text" class="form-control" name="ao_date_input"
                                                    value="<?= $tanggal_input_formao_indo ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </section>
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
        </form>
    <?php endforeach; ?>

    <!-- MODAL TAMBAH ASET KANTOR -->
    <form action="<?php echo site_url('GA_Aset_Kantor/tambahAsetKantor/') ?>" method="post">
        <div class="modal fade" id="modal-tambah-aset-kantor" tabindex="-1" role="dialog"
            aria-labelledby="modal-tambah-label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">EDIT DATA ASET KANTOR</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <?php
                    ?>
                    <div class="modal-body" style="background-color:rgb(247, 243, 243);">
                        <section class="content">

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="hidden" name="ao_id_list_office"
                                                    value="<?= $data['ao_id_list_office'] ?>">
                                                <label class="col-form-label">Jenis Aset</label>
                                                <select name="ka_id_kode_aset" class="form-control">
                                                    <option value="25">LAPTOP</option>
                                                    <option value="26">PRINTER</option>
                                                    <option value="27">SCANNER</option>
                                                    <option value="28">MARKOM</option>
                                                    <option value="29">DRAFTER</option>
                                                    <option value="30">HARDISK</option>
                                                    <option value="31">HANDPHONE</option>
                                                    <option value="32">CUTTING PLOTTER</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Merk Aset</label>
                                                <input type="text" class="form-control" name="ao_merk"
                                                    autocomplete="off" placeholder="Redmi Note 13">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Type</label>
                                                <input type="text" class="form-control" name="ao_type"
                                                    autocomplete="off" placeholder="Xiaomi Hyper OS 2.0.201.0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Serial Number</label>
                                                <input type="text" class="form-control" name="ao_serial_number"
                                                    autocomplete="off" placeholder="1234ABCD5678EFGH">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Spesifikasi</label>
                                                <input type="text" class="form-control" name="ao_spesifikasi"
                                                    autocomplete="off" placeholder="8 Gb - 128 GB">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Kondisi Aset</label>
                                                <select name="ao_kondisi_aset" class="form-control">
                                                    <option value="BAIK" <?php if ($data['ao_kondisi_aset'] == 'BAIK') { ?>selected <?php } ?>>BAIK
                                                    </option>
                                                    <option value="RUSAK" <?php if ($data['ao_kondisi_aset'] == 'RUSAK') { ?>selected <?php } ?>>
                                                        RUSAK
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3 mt-3">
                                        <div class="flex-grow-1 border-top"></div>
                                        <h5 class="mx-3">PIC & LOKASI</h5>
                                        <div class="flex-grow-1 border-top"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">PIC Aset</label>
                                                <input type="text" class="form-control" name="ao_pic" autocomplete="off"
                                                    placeholder="Nama Pemegang Aset">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Regional Kendaraan</label>
                                                <select name="ao_regional" class="form-control">
                                                    <option value="" <?php if ($data['ao_regional'] == '') { ?>selected
                                                        <?php } ?>>Pilih Regional</option>
                                                    <option value="REGIONAL 1">REGIONAL 1</option>
                                                    <option value="REGIONAL 2">REGIONAL 2</option>
                                                    <option value="REGIONAL 3">REGIONAL 3</option>
                                                    <option value="REGIONAL 4">REGIONAL 4</option>
                                                    <option value="REGIONAL 5">REGIONAL 5</option>
                                            </div>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Area</label>
                                            <input type="text" class="form-control" name="ao_area" autocomplete="off"
                                                placeholder="Pekanbaru / Lampung">
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1 border-top"></div>
                                <h5 class="mx-3">EVIDENCE & FILE PENDUKUNG</h5>
                                <div class="flex-grow-1 border-top"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lihat Foto Alat</label>
                                        <div class="card">
                                            <div class="card-body p-6">
                                                <div class="">
                                                    <div class="d-flex align-items-center overflow-hidden">

                                                        <div class="flex-grow-1">
                                                            <h5 class="font-size-15 mb-1 text-truncate"
                                                                id="detail_nama_file_sj"></h5>
                                                            <a href="" class="font-size-14 text-muted text-truncate"
                                                                id="view_detail_surao_jalan" target="_blank"><u>View
                                                                    Folder</u></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>BA / FIle Lain</label>
                                        <div class="card">
                                            <div class="card-body p-6">
                                                <div class="">
                                                    <div class="d-flex align-items-center overflow-hidden">

                                                        <div class="flex-grow-1">
                                                            <h5 class="font-size-15 mb-1 text-truncate"
                                                                id="detail_nama_file_sj"></h5>
                                                            <a href="" class="font-size-14 text-muted text-truncate"
                                                                id="view_detail_surao_jalan" target="_blank"><u>View
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
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1 border-top"></div>
                                <h5 class="mx-3">INFORMASI LAIN</h5>
                                <div class="flex-grow-1 border-top"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Status Aset</label>
                                        <select name="ak_status_aset" class="form-control">
                                            <?php foreach ($option_aktif as $option): ?>
                                                <option value="<?= $option ?>">
                                                    <?= $option ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Keterangan</label>
                                        <textarea class="form-control" name="ao_keterangan_aset"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1 border-top"></div>
                                <h5 class="mx-3">TANGGAL UPDATE</h5>
                                <div class="flex-grow-1 border-top"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Tahun Perolehan</label>
                                        <input type="text" class="form-control" name="ao_tahun_perolehan"
                                            autocomplete="off" placeholder="2022">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Tanggal Opname Terakhir</label>
                                        <input type="date" class="form-control" name="ao_date_last_cek">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Tanggal Input Aplikasi</label>
                                        <input type="date" class="form-control" name="ao_date_input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" name="btnEdit" class="btn btn-primary"><i
                            class="fa fa-spinner fa-spin loading" style="display:none"></i>
                        Simpan</button>
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
        $('#tabel_laptop').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_printer').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_scanner').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_markom').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_drafter').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_hardisk').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_handphone').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
        $('#tabel_cuttingplotter').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
    });

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        const table = $('#tabel_laptop').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelLaptop = data.length;
            document.getElementById('totalTabelLaptop').innerText = totalTabelLaptop.toLocaleString('id-ID');
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
        const table = $('#tabel_printer').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelPrinter = data.length;
            document.getElementById('totalTabelPrinter').innerText = totalTabelPrinter.toLocaleString('id-ID');
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
        const table = $('#tabel_scanner').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelScanner = data.length;
            document.getElementById('totalTabelScanner').innerText = totalTabelScanner.toLocaleString('id-ID');
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
        const table = $('#tabel_markom').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelMarkom = data.length;
            document.getElementById('totalTabelMarkom').innerText = totalTabelMarkom.toLocaleString('id-ID');
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
        const table = $('#tabel_drafter').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelDrafter = data.length;
            document.getElementById('totalTabelDrafter').innerText = totalTabelDrafter.toLocaleString('id-ID');
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
        const table = $('#tabel_hardisk').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelHardisk = data.length;
            document.getElementById('totalTabelHardisk').innerText = totalTabelHardisk.toLocaleString('id-ID');
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
        const table = $('#tabel_handphone').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelHandphone = data.length;
            document.getElementById('totalTabelHandphone').innerText = totalTabelHandphone.toLocaleString('id-ID');
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
        const table = $('#tabel_cuttingplotter').DataTable({
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
            const data = table.rows({ search: 'applied' }).data();
            let totalTabelCuttingPlotter = data.length;
            document.getElementById('totalTabelCuttingPlotter').innerText = totalTabelCuttingPlotter.toLocaleString('id-ID');
        }

        // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali saat tabel dimuat
        updateTotal();
    });

    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($getCountAsetOfficeByKota as $stokKategory): ?>
            var boxElement = document.getElementById("box_detail_aset_office_<?= $stokKategory['ka_jenis_aset'] ?>");

            if (boxElement) { // Pastikan elemen ditemukan sebelum menambahkan event
                boxElement.addEventListener("click", function () {
                    console.log("<?= $stokKategory['ka_jenis_aset'] ?>");
                    <?php if ($stokKategory['ka_jenis_aset'] == 'LAPTOP') { ?>
                        var targetElement = document.getElementById("judul_aset_laptop");

                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                            targetElement.scrollIntoView({
                                behavior: "smooth"
                            });
                        }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'PRINTER') { ?>
                            var targetElement = document.getElementById("judul_aset_printer");

                            if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                targetElement.scrollIntoView({
                                    behavior: "smooth"
                                });
                            }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'SCANNER') { ?>
                                var targetElement = document.getElementById("judul_aset_scanner");

                                if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                    targetElement.scrollIntoView({
                                        behavior: "smooth"
                                    });
                                }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'MARKOM') { ?>
                                    var targetElement = document.getElementById("judul_aset_markom");

                                    if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                        targetElement.scrollIntoView({
                                            behavior: "smooth"
                                        });
                                    }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'DRAFTER') { ?>
                                        var targetElement = document.getElementById("judul_aset_drafter");

                                        if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                            targetElement.scrollIntoView({
                                                behavior: "smooth"
                                            });
                                        }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'HARDISK') { ?>
                                            var targetElement = document.getElementById("judul_aset_hardisk");

                                            if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                                targetElement.scrollIntoView({
                                                    behavior: "smooth"
                                                });
                                            }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'HANDPHONE') { ?>
                                                var targetElement = document.getElementById("judul_aset_handphone");

                                                if (targetElement) { // Pastikan elemen tujuan ada sebelum scrolling
                                                    targetElement.scrollIntoView({
                                                        behavior: "smooth"
                                                    });
                                                }
                    <?php } else if ($stokKategory['ka_jenis_aset'] == 'CUTTING PLOTTER') { ?>
                                                    var targetElement = document.getElementById("judul_aset_tiang");

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