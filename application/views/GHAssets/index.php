<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper"> <!-- Main content -->
  <section class="content">

    <div class="content-header">
      <h1 class="m-0 text-dark ">List Assets</h1>
    </div>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-6">
              <h3 class="card-title">List Assets Kamar</h3>
            </div>
            <div class="col-6">
              <a href="#" class="btn btn-primary float-right text-bold" data-target="#modalTambahAsset"
                data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table class="table table-bordered table-striped" id="assetsTable">
            <thead>
              <tr>
                <th>No</th>
                <th>Aset</th>
                <th>Kategori</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1;
              foreach ($getAllAssets as $assets): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= $assets['code'] ?></td>
                  <td><?= $assets['label'] ?></td>
                  <td>
                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                      <a href="<?php echo site_url('GHAssets/hapusAsset/' . $assets['id']); ?>" id="tombol_hapus"
                        class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                      <a href="#" class="btn btn-warning" data-target="#modalEditAsset<?= $assets['id'] ?>"
                        data-toggle="modal"><i class="fas fa-edit"></i></a>
                    <?php } ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </section>

  <form action="<?php echo site_url('GHAssets/tambahAsset/'); ?>" method="post">
    <div class="modal fade" id="modalTambahAsset">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="formAsset">
            <div class="modal-header">
              <h4 class="modal-title">Form Asset</h4>
              <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body">

              <div class="form-group">
                <label>Code Asset</label>
                <input type="text" class="form-control" id="code" name="code">
              </div>

              <div class="form-group">
                <label>Jenis Asset</label>
                <input type="text" class="form-control" id="label" name="label">
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </form>

  <?php foreach ($getAllAssets as $assets): ?>
    <form action="<?php echo site_url('GHAssets/editAsset/' . $assets['id']); ?>" method="post">
      <div class="modal fade" id="modalEditAsset<?= $assets['id'] ?>">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form id="formAsset">
              <div class="modal-header">
                <h4 class="modal-title">Form Asset</h4>
                <button type="button" class="close" data-dismiss="modal">
                  <span>&times;</span>
                </button>
              </div>

              <div class="modal-body">

                <div class="form-group">
                  <label>Code Asset</label>
                  <input type="text" class="form-control" id="code" name="code" value="<?= $assets['code'] ?>">
                </div>

                <div class="form-group">
                  <label>Jenis Asset</label>
                  <input type="text" class="form-control" id="label" name="label" value="<?= $assets['label'] ?>">
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </form>
  <?php endforeach; ?>

</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>

<script>

  $(function () {


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
    $('#assetsTable').DataTable({
      "paging": true, // Tetap gunakan pagination
      "pageLength": 10, // Menampilkan 10 data per halaman
      "info": true, // Menghilangkan "Showing 1 to X of X entries"
      "searching": true, // Menghilangkan search bar
      "lengthChange": true // Menghilangkan dropdown "Show entries"
    });
  });

  $(document).ready(function () {
    $.fn.dataTable.ext.errMode = 'none';
    const table = $('#assetsTable').DataTable({
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
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHAssets.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">