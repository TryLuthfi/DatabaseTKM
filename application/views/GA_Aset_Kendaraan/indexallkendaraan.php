<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$option_aktif = ['AKTIF', 'HILANG', 'TERJUAL'];
$jenis_kendaraan = ['MOBIL', 'MOTOR'];

$total = 1;
?>

<script>alert(<?= $getKategoriKendaraan ?>);</script>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                            <h1 class="m-0 text-dark">Detail Kendaraan - <?= $getKategoriKendaraan ?>
                            </h1>
                    </div>
                </div>
            </div>
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
                                            data-target="#modal-tambah-kendaraan" data-toggle="modal">Tambah &nbsp;<i
                                                class="fas fa-plus"></i> </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-scrollable">
                                <table id="tabel_pemasukan" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode</th>
                                            <th>Merk Kendaraan</th>
                                            <th>NOPOL</th>
                                            <th>PIC</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($getMasterAsetKendaraan as $data):
                                                if ($data['ka_jenis_aset'] == $getKategoriKendaraan) { ?>
                                                    <tr>
                                                        <td><?= $total++ ?></td>
                                                        <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ak_sort'] ?></td>
                                                        <td><?= $data['ak_merk'] ?></td>
                                                        <td><?= $data['ak_plat_nomor'] ?></td>
                                                        <td><?= $data['ak_pic'] ?></td>
                                                        <td><?= $data['ak_area'] ?></td>
                                                        <td><?= $data['ak_status_aset'] ?></td>
                                                        <td>
                                                            <a href="<?php echo site_url('GA_Aset_Kendaraan/hapusKendaraan/' . $data['ak_id_list_kendaraan']); ?>"
                                                                id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                                    class=" fas fa-trash"></i></a>
                                                            <a href="#" class="btn btn-warning"
                                                                data-target="#modal-edit-kendaraan<?= $data['ak_id_list_kendaraan'] ?>"
                                                                data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                            <a href="#" class="btn btn-primary"
                                                                data-target="#modal-view-kendaraan<?= $data['ak_id_list_kendaraan'] ?>"
                                                                data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                        </td>
                                                    </tr>

                                                <?php }
                                        endforeach; ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th colspan="1"><span id="totalTabelAset">0</span>
                                            <th colspan="5"></span>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>

                </div>
        </section>

        <!-- VIEW DATA KENDARAAN -->
        <?php $tgl = date('Y-m-d'); ?>
        <?php foreach ($getMasterAsetKendaraan as $data):

            $tanggal_perolehan = isset($data['ak_tahun_perolehan']) ? $data['ak_tahun_perolehan'] : null;
            $tanggal_stnk = isset($data['ak_tanggal_stnk']) ? $data['ak_tanggal_stnk'] : null;
            $tanggal_plat = isset($data['ak_tanggal_plat']) ? $data['ak_tanggal_plat'] : null;

            $remarks_stnk = "";
            $remarks_plat = "";
            $warna_remarks_stnk = '';
            $warna_remarks_plat = '';
            $warna_remarks_kondisi = '';

            // Cek jika tanggal valid
            if (!empty($tanggal_perolehan)) {

                $formatDate = new DateTime(date('Y-m-d'));

                $awal_tanggal_beli = new DateTime($tanggal_perolehan);
                $awal_tanggal_stnk = new DateTime($tanggal_stnk);
                $awal_tanggal_plat = new DateTime($tanggal_plat);
                $selisih_tanggal_beli = $awal_tanggal_beli->diff($formatDate);
                $selisih_tanggal_stnk = $awal_tanggal_stnk->diff($formatDate);
                $selisih_tanggal_plat = $awal_tanggal_plat->diff($formatDate);

                $umur_kendaraan = $selisih_tanggal_beli->y . ' tahun ' . $selisih_tanggal_beli->m . ' bulan ' . $selisih_tanggal_beli->d . ' hari';
                $umur_stnk = $selisih_tanggal_stnk->y . ' tahun ' . $selisih_tanggal_stnk->m . ' bulan ' . $selisih_tanggal_stnk->d . ' hari';
                $umur_plat = $selisih_tanggal_plat->y . ' tahun ' . $selisih_tanggal_plat->m . ' bulan ' . $selisih_tanggal_plat->d . ' hari';
                $umur_kendaraan_hari = $selisih_tanggal_beli->days . ' hari';
                $umur_stnk_hari = $selisih_tanggal_stnk->days . ' hari';
                $umur_plat_hari = $selisih_tanggal_plat->days . ' hari';

                $tanggal_beli_format_indo = date('d F Y', strtotime($data['ak_tahun_perolehan']));
                $tanggal_plat_format_indo = date('d F Y', strtotime($data['ak_tanggal_plat']));
                $tanggal_stnk_format_indo = date('d F Y', strtotime($data['ak_tanggal_stnk']));

                if ($data['ak_kondisi_aset'] == 'BAIK') {
                    $warna_remarks_kondisi = "background-color: #d4edda; color: #155724;";
                } else if ($data['ak_kondisi_aset'] == 'RUSAK') {
                    $warna_remarks_kondisi = "background-color: #f8d7da; color: #721c24;";
                } else {
                    $warna_remarks_kondisi = "";
                }

                if ($umur_stnk_hari > '10') {
                    $remarks_stnk = "AKTIF";
                    $warna_remarks_stnk = "background-color: #d4edda; color: #155724;";
                } else if ($umur_stnk_hari <= '10') {
                    $remarks_stnk = "AKAN MATI";
                    $warna_remarks_stnk = "background-color: #f8d7da; color: #721c24;";
                } else if ($umur_stnk_hari <= days) {
                    $remarks_stnk = "MATI";
                    $warna_remarks_stnk = "background-color: #fff3cd; color: #856404;";
                } else {
                    $remarks_stnk = "";
                }

                if ($umur_plat_hari > '10') {
                    $remarks_plat = "AKTIF";
                    $warna_remarks_plat = "background-color: #d4edda; color: #155724;";
                } else if ($umur_plat_hari <= '10') {
                    $remarks_plat = "AKAN MATI";
                    $warna_remarks_plat = "background-color: #f8d7da; color: #721c24;";
                } else if ($umur_plat_hari <= days) {
                    $remarks_plat = "MATI";
                    $warna_remarks_plat = "background-color: #fff3cd; color: #856404;";
                } else {
                    $remarks_plat = "";
                }

            } else {
                $umur_kendaraan = '-';
            }
            ?>
            <div class="modal fade" id="modal-view-kendaraan<?= $data['ak_id_list_kendaraan'] ?>" tabindex="-1"
                role="dialog" aria-labelledby="modal-tambah-label" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">DETAIL DATA KENDARAAN</h4>
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
                                        <input type="hidden" name="ak_id_list_kendaraan"
                                            value="<?= $data['ak_id_list_kendaraan'] ?>">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h3 class="mx-3"><?= $data['ak_plat_nomor'] ?></h3>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Merk Kendaraan</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ak_merk'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Kondisi Kendaraan</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ak_kondisi_aset'] ?>" readonly
                                                        style="<?= $warna_remarks_kondisi ?> font-weight: bold;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Pajak STNK</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        value="<?= strtoupper($remarks_stnk) ?>" readonly
                                                        style="<?= $warna_remarks_stnk ?> font-weight: bold;">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Pajak Plat</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        value="<?= strtoupper($remarks_plat) ?>" readonly
                                                        style="<?= $warna_remarks_plat ?> font-weight: bold;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mt-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h4 class="mx-3">PIC & LOKASI</h4>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">PIC Kendaraan</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ak_pic'] ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Lokasi Kendaraan</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ak_area'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Regional Kendaraan</label>
                                                    <input type="text" class="form-control" name="access_id_project"
                                                        autocomplete="off" value="<?= $data['ak_regional'] ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal Beli</label>
                                                    <input type="text" class="form-control"
                                                        value="<?= $tanggal_beli_format_indo ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Umur Kendaraan</label>
                                                    <input type="text" class="form-control" value="<?= $umur_kendaraan ?>"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">STATUS STNK</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal STNK</label>
                                                    <input type="text" class="form-control"
                                                        value="<?= $tanggal_stnk_format_indo ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Due Date</label>
                                                    <input type="text" class="form-control" value="<?= $umur_stnk_hari ?>"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">STATUS PLAT NOMOR</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal PLAT</label>
                                                    <input type="text" class="form-control"
                                                        value="<?= $tanggal_plat_format_indo ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Due Date</label>
                                                    <input type="text" class="form-control" value="<?= $umur_plat_hari ?>"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">EVIDENCE KENDARAAN</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Lihat Foto STNK</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
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
                                                    <label>Lihat Foto BPKB</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
                                                                            target="_blank"><u>View
                                                                                Folder</u></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Lihat Foto Kendaraan</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
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
                                                            <option value="<?= $option ?>" <?= isset($data['ak_status_aset']) && $data['ak_status_aset'] == $option ? 'selected' : '' ?>>
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
                                                        name="remarks_status"><?= $data['ak_keterangan_aset'] ?></textarea>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </section>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal"><i
                                    class="fa fa-spinner fa-spin loading" style="display: none;"></i>
                                Oke</button>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>


        <!-- MODAL TAMBAH KENDARAAN -->
        <form action="<?php echo site_url('GA_Aset_Kendaraan/tambahKendaraan/'); ?>" method="post">
            <div class="modal fade" id="modal-tambah-kendaraan" tabindex="-1" role="dialog"
                aria-labelledby="modal-tambah-label" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">TAMBAH DATA KENDARAAN</h4>
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
                                        <input type="hidden" name="ak_id_list_kendaraan" value="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Jenis Kendaraan</label>
                                                    <select name="ka_id_kode_aset" class="form-control">
                                                        <option value="1">MOBIL</option>
                                                        <option value="2">MOTOR</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Nomor Kendaraan</label>
                                                    <input type="text" class="form-control" name="ak_plat_nomor"
                                                        autocomplete="off" placeholder="B **** HHK">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Merk Kendaraan</label>
                                                    <input type="text" class="form-control" name="ak_merk"
                                                        autocomplete="off"
                                                        placeholder="Honda Beat (AT) / Toyota Avanza">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Kondisi Kendaraan</label>
                                                    <select name="ak_kondisi_aset" class="form-control">
                                                        <option value="BAIK">BAIK</option>
                                                        <option value="RUSAK">RUSAK</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mt-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h4 class="mx-3">PIC & LOKASI</h4>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">PIC Kendaraan</label>
                                                    <input type="text" class="form-control" name="ak_pic"
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Lokasi Kendaraan</label>
                                                    <input type="text" class="form-control" name="ak_area"
                                                        autocomplete="off" placeholder="Jakarta / Bandung">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Regional Kendaraan</label>
                                                    <select name="ak_regional" class="form-control">
                                                        <option value="REGIONAL 1">Reg 1</option>
                                                        <option value="REGIONAL 2">Reg 2</option>
                                                        <option value="REGIONAL 3">Reg 3</option>
                                                        <option value="REGIONAL 4">Reg 4</option>
                                                        <option value="REGIONAL 5">Reg 5</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal Beli</label>
                                                    <input type="date" class="form-control" id="tanggal_beli"
                                                        name="ak_tahun_perolehan">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Umur Kendaraan</label>
                                                    <input type="text" class="form-control" value="" id="umur_kendaraan"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">STATUS STNK</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal STNK</label>
                                                    <input type="date" class="form-control" id="tanggal_stnk"
                                                        name="ak_tanggal_stnk">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Due Date</label>
                                                    <input type="text" class="form-control" value="" id="due_date_stnk"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">STATUS PLAT NOMOR</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Tanggal PLAT</label>
                                                    <input type="date" class="form-control" id="tanggal_plat"
                                                        name="ak_tanggal_plat">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label">Due Date</label>
                                                    <input type="text" class="form-control" value="" id="due_date_plat"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-grow-1 border-top"></div>
                                            <h5 class="mx-3">EVIDENCE KENDARAAN</h5>
                                            <div class="flex-grow-1 border-top"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Lihat Foto STNK</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
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
                                                    <label>Lihat Foto BPKB</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
                                                                            target="_blank"><u>View
                                                                                Folder</u></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Lihat Foto Kendaraan</label>
                                                    <div class="card">
                                                        <div class="card-body p-6">
                                                            <div class="">
                                                                <div class="d-flex align-items-center overflow-hidden">

                                                                    <div class="flex-grow-1">
                                                                        <h5 class="font-size-15 mb-1 text-truncate"
                                                                            id="detail_nama_file_sj"></h5>
                                                                        <a href=""
                                                                            class="font-size-14 text-muted text-truncate"
                                                                            id="view_detail_surat_jalan"
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
                                                    <textarea class="form-control" name="ak_keterangan_aset"></textarea>
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

        <!-- MODAL EDIT KENDARAAN -->
        <?php foreach ($getMasterAsetKendaraan as $data): ?>
            <form action="<?php echo site_url('GA_Aset_Kendaraan/editKendaraan/' . $data['ak_id_list_kendaraan']); ?>" method="post">
                <div class="modal fade" id="modal-edit-kendaraan<?= $data['ak_id_list_kendaraan'] ?>" tabindex="-1"
                    role="dialog" aria-labelledby="modal-tambah-label" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">EDIT DATA KENDARAAN</h4>
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
                                            <input type="hidden" name="ak_id_list_kendaraan" value="">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="hidden" name="ak_id_list_kendaraan"
                                                value="<?= $data['ak_id_list_kendaraan'] ?>">
                                                        <label class="col-form-label">Jenis Kendaraan</label>
                                                        <select name="ka_id_kode_aset" class="form-control">
                                                            <option value="1" <?php if ($data['ka_jenis_aset'] == 'MOBIL') { ?>selected <?php } ?>>MOBIL
                                                            </option>
                                                            <option value="2" <?php if ($data['ka_jenis_aset'] == 'MOTOR') { ?>selected <?php } ?>>MOTOR
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Nomor Kendaraan</label>
                                                        <input type="text" class="form-control" name="ak_plat_nomor"
                                                            autocomplete="off" value="<?= $data['ak_plat_nomor'] ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Merk Kendaraan</label>
                                                        <input type="text" class="form-control" name="ak_merk"
                                                            autocomplete="off"
                                                            value="<?= $data['ak_merk'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Kondisi Kendaraan</label>
                                                        <select name="ak_kondisi_aset" class="form-control">
                                                            <option value="BAIK" <?php if ($data['ak_kondisi_aset'] == 'BAIK') { ?>selected <?php } ?>>BAIK
                                                            </option>
                                                            <option value="RUSAK" <?php if ($data['ak_kondisi_aset'] == 'RUSAK') { ?>selected <?php } ?>>RUSAK
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center mt-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h4 class="mx-3">PIC & LOKASI</h4>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">PIC Kendaraan</label>
                                                        <input type="text" class="form-control" name="ak_pic" value="<?= $data['ak_pic'] ?>"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Lokasi Kendaraan</label>
                                                        <input type="text" class="form-control" name="ak_area"
                                                            autocomplete="off" value="<?= $data['ak_area'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Regional Kendaraan</label>
                                                        <select name="ak_regional" class="form-control">
                                                            <option value="" <?php if ($data['ak_regional'] == '') { ?>selected <?php } ?>>Pilih Regional</option>
                                                            <option value="REGIONAL 1" <?php if ($data['ak_regional'] == 'REGIONAL 1') { ?>selected <?php } ?>>REGIONAL  1</option>
                                                            <option value="REGIONAL 2" <?php if ($data['ak_regional'] == 'REGIONAL 2') { ?>selected <?php } ?>>REGIONAL  2</option>
                                                            <option value="REGIONAL 3" <?php if ($data['ak_regional'] == 'REGIONAL 3') { ?>selected <?php } ?>>REGIONAL  3</option>
                                                            <option value="REGIONAL 4" <?php if ($data['ak_regional'] == 'REGIONAL 4') { ?>selected <?php } ?>>REGIONAL  4</option>
                                                            <option value="REGIONAL 5" <?php if ($data['ak_regional'] == 'REGIONAL 5') { ?>selected <?php } ?>>REGIONAL  5</option></div>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Tanggal Beli</label>
                                                        <input type="date" class="form-control" id="tanggal_beli"
                                                            name="ak_tahun_perolehan" value="<?= $data['ak_tahun_perolehan'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Umur Kendaraan</label>
                                                        <input type="text" class="form-control" value="" id="umur_kendaraan"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h5 class="mx-3">STATUS STNK</h5>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Tanggal STNK</label>
                                                        <input type="date" class="form-control" id="tanggal_stnk"
                                                            name="ak_tanggal_stnk" value="<?= $data['ak_tanggal_stnk'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Due Date</label>
                                                        <input type="text" class="form-control" value="" id="due_date_stnk"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h5 class="mx-3">STATUS PLAT NOMOR</h5>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Tanggal PLAT</label>
                                                        <input type="date" class="form-control" id="tanggal_plat"
                                                            name="ak_tanggal_plat" value="<?= $data['ak_tanggal_plat'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Due Date</label>
                                                        <input type="text" class="form-control" value="" id="due_date_plat"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1 border-top"></div>
                                                <h5 class="mx-3">EVIDENCE KENDARAAN</h5>
                                                <div class="flex-grow-1 border-top"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Lihat Foto STNK</label>
                                                        <div class="card">
                                                            <div class="card-body p-6">
                                                                <div class="">
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate"
                                                                                id="detail_nama_file_sj"></h5>
                                                                            <a href=""
                                                                                class="font-size-14 text-muted text-truncate"
                                                                                id="view_detail_surat_jalan"
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
                                                        <label>Lihat Foto BPKB</label>
                                                        <div class="card">
                                                            <div class="card-body p-6">
                                                                <div class="">
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate"
                                                                                id="detail_nama_file_sj"></h5>
                                                                            <a href=""
                                                                                class="font-size-14 text-muted text-truncate"
                                                                                id="view_detail_surat_jalan"
                                                                                target="_blank"><u>View
                                                                                    Folder</u></a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Lihat Foto Kendaraan</label>
                                                        <div class="card">
                                                            <div class="card-body p-6">
                                                                <div class="">
                                                                    <div class="d-flex align-items-center overflow-hidden">

                                                                        <div class="flex-grow-1">
                                                                            <h5 class="font-size-15 mb-1 text-truncate"
                                                                                id="detail_nama_file_sj"></h5>
                                                                            <a href=""
                                                                                class="font-size-14 text-muted text-truncate"
                                                                                id="view_detail_surat_jalan"
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
                                                            <option value="<?= $option ?>" <?= isset($data['ak_status_aset']) && $data['ak_status_aset'] == $option ? 'selected' : '' ?>>
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
                                                        <textarea class="form-control" name="ak_keterangan_aset"><?= $data['ak_keterangan_aset'] ?></textarea>
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
        $('#tabel_pemasukan').DataTable({
            "paging": true, // Tetap gunakan pagination
            "pageLength": 10, // Menampilkan 10 data per halaman
            "info": true, // Menghilangkan "Showing 1 to X of X entries"
            "searching": true, // Menghilangkan search bar
            "lengthChange": true // Menghilangkan dropdown "Show entries"
        });
    });

    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        const table = $('#tabel_pemasukan').DataTable({
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
            let totalTabelAset = data.length;
            document.getElementById('totalTabelAset').innerText = totalTabelAset.toLocaleString('id-ID');
        }

        // Hitung ulang total setiap kali tabel berubah (misalnya, pencarian atau paginasi)
        table.on('draw', function () {
            updateTotal();
        });

        // Hitung total pertama kali saat tabel dimuat
        updateTotal();
    });

    document.getElementById("tanggal_beli").addEventListener("change", function () {
        const tanggalBeli = new Date(this.value);
        const hariIni = new Date();

        if (!this.value) {
            document.getElementById("umur_kendaraan").value = "";
            return;
        }

        // Hitung selisih tahun, bulan, dan hari
        let tahun = hariIni.getFullYear() - tanggalBeli.getFullYear();
        let bulan = hariIni.getMonth() - tanggalBeli.getMonth();
        let hari = hariIni.getDate() - tanggalBeli.getDate();

        // Koreksi jika bulan/hari negatif
        if (hari < 0) {
            bulan--;
            const bulanSebelumnya = new Date(hariIni.getFullYear(), hariIni.getMonth(), 0);
            hari += bulanSebelumnya.getDate();
        }

        if (bulan < 0) {
            tahun--;
            bulan += 12;
        }

        // Tampilkan hasil
        document.getElementById("umur_kendaraan").value =
            `${tahun} tahun ${bulan} bulan ${hari} hari`;
    });

    document.getElementById("tanggal_stnk").addEventListener("change", function () {
        const tglStnk = new Date(this.value);

        if (!this.value) {
            document.getElementById("due_date_stnk").value = "";
            return;
        }

        // Hitung tanggal jatuh tempo 350 hari dari tanggal STNK
        const dueDate = new Date(tglStnk);
        dueDate.setDate(dueDate.getDate());

        // Hitung selisih hari dari hari ini ke dueDate
        const today = new Date();
        const diffTime = dueDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        document.getElementById("due_date_stnk").value = diffDays + " Hari";
    });

    document.getElementById("tanggal_plat").addEventListener("change", function () {
        const tglPlat = new Date(this.value);

        if (!this.value) {
            document.getElementById("due_date_plat").value = "";
            return;
        }

        // Hitung tanggal jatuh tempo 350 hari dari tanggal STNK
        const dueDate = new Date(tglPlat);
        dueDate.setDate(dueDate.getDate());

        // Hitung selisih hari dari hari ini ke dueDate
        const today = new Date();
        const diffTime = dueDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        document.getElementById("due_date_plat").value = diffDays + " Hari";
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