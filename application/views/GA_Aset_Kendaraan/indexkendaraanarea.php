<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$option_aktif = ['AKTIF', 'HILANG', 'TERJUAL'];


$total = 1;
?>

<script>alert(<?= $getKategoriKendaraan ?>);</script>

<div class="content-wrapper">

    <div class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= $judul ?></h1>
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
                                            style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                            data-target="#modal-lg-tambah" data-toggle="modal">Tambah &nbsp;<i
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
                                        foreach ($getMasterAsetKendaraanFilter as $data): ?>
                                            <tr>
                                                <td><?= $total++ ?></td>
                                                <td><?= $data['ka_nama_kode_aset'] . '-' . $data['ak_sort'] ?></td>
                                                <td><?= $data['ak_merk'] ?></td>
                                                <td><?= $data['ak_plat_nomor'] ?></td>
                                                <td><?= $data['ak_pic'] ?></td>
                                                <td><?= $data['ak_area'] ?></td>
                                                <td><?= $data['ak_status_aset'] ?></td>
                                                <td>
                                                    <a href="<?php echo site_url('Master_GA_Aset/hapusKodeAset/' . $data['ka_id_kode_aset']); ?>"
                                                        style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                        id="tombol_hapus" class="btn btn-danger tombol_hapus"><i
                                                            class=" fas fa-trash"></i></a>
                                                    <a href="#" class="btn btn-warning"
                                                        style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"
                                                        data-target="#modal-lg-edit<?= $data['ka_id_kode_aset'] ?>"
                                                        data-toggle="modal"><i class="fas fa-edit"></i></a>
                                                    <a href="#" class="btn btn-primary"
                                                        data-target="#modal-view-kendaraan<?= $data['ak_id_list_kendaraan'] ?>"
                                                        data-toggle="modal"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

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
            <form action="<?php echo site_url('ListArea/edit/' . $data['ak_id_list_kendaraan']); ?>" method="post"></form>
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