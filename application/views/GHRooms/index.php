<?php
$statusAlert = $this->session->flashdata('statusAlert');
$error_log = $this->session->flashdata('error_log');


$total = 1;
?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper"> <!-- Main content -->
  <section class="content">

    <div class="content-header">
      <h1 class="m-0 text-dark ">List Kamar</h1>
    </div>

    <section class="content">

      <div class="row">

        <!-- BOX TOTAL KAMAR -->
        <div class="col-md-3">
          <div class="small-box bg-primary box-filter" data-filter="all">
            <div class="inner">
              <h3><?= $total_rooms ?></h3>
              <p>Total Kamar</p>
            </div>
            <div class="icon">
              <i class="fas fa-door-closed"></i>
            </div>
          </div>
        </div>

        <!-- BOX PER TYPE -->
        <?php foreach ($room_types_summary as $rt): ?>
          <div class="col-md-3">
            <div class="small-box bg-success box-filter" data-filter="<?= $rt['type_name'] ?>">
              <div class="inner">
                <h3><?= $rt['total'] ?></h3>
                <p><?= $rt['type_name'] ?></p>
              </div>
              <div class="icon">
                <i class="fas fa-tag"></i>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-6">
              <h3 class="card-title">List Kamar</h3>
            </div>
            <div class="col-6">
              <a href="#" class="btn btn-primary float-right text-bold" data-target="#modalTambahRoom"
                data-toggle="modal">Tambah &nbsp;<i class="fas fa-plus"></i> </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table class="table table-bordered table-striped" id="roomsTable">
            <thead>
              <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Catatan</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($getAllRooms as $rooms): ?>
                <tr>
                  <td><?= $total++ ?></td>
                  <td><?= $rooms['code'] ?></td>
                  <td><?= $rooms['name'] ?></td>
                  <td><?= $rooms['type_name'] ?></td>
                  <td>Rp <?= number_format($rooms['price'], 0, ",", ".") ?></td>
                  <td>
                    <?php
                    $status = strtolower($rooms['status']); // Pastikan status dalam huruf kecil untuk perbandingan
                    switch ($status) {
                      case 'available':
                        $badgeClass = 'badge-success'; // Warna hijau untuk available
                        break;
                      case 'occupied':
                        $badgeClass = 'badge-danger'; // Warna merah untuk occupied
                        break;
                      case 'maintenance':
                        $badgeClass = 'badge-warning'; // Warna kuning untuk maintenance
                        break;
                      default:
                        $badgeClass = 'badge-secondary'; // Warna default untuk status lain
                        break;
                    }
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= strtoupper($rooms['status']) ?></span>
                  </td>
                  <td><?= $rooms['notes'] ?></td>
                  <td>
                    <?php if ($this->session->userdata('nama_level') == "Super Admin") { ?>
                      <a href="<?php echo site_url('GHRooms/hapusKamar/' . $rooms['id']); ?>" id="tombol_hapus"
                        class="btn btn-danger tombol_hapus"><i class=" fas fa-trash"></i></a>
                      <a href="#" class="btn btn-warning" data-target="#modalEditRoom<?= $rooms['id'] ?>"
                        data-toggle="modal"><i class="fas fa-edit"></i></a>
                    <?php } ?>
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
      </div>
    </section>
  </section>

  <form action="<?php echo site_url('GHRooms/tambahKamar/'); ?>" method="post">
    <div class="modal fade" id="modalTambahRoom">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="formRoom">
            <div class="modal-header">
              <h4 class="modal-title">Form Kamar</h4>
              <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
              </button>
            </div>

            <div class="modal-body">

              <input type="hidden" id="room_id">

              <div class="form-group">
                <label>Lokasi</label>
                <select class="form-control" id="status" name="code">
                  <option value="GH 1">GH 1</option>
                  <option value="GH 2">GH 2</option>
                </select>
              </div>

              <div class="form-group">
                <label>Nama Kamar</label>
                <input type="text" class="form-control" id="name" name="name">
              </div>

              <div class="form-group">
                <label>Tipe Kamar</label>
                <select class="form-control" id="room_type_id" name="room_type_id">
                  <option value="">-- Pilih --</option>
                  <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Harga</label>
                <input type="text" class="form-control" id="price" name="price">
              </div>

              <div class="form-group">
                <label>Status</label>
                <select class="form-control" id="status" name="status">
                  <option value="available">Tersedia</option>
                  <option value="occupied">Terisi</option>
                  <option value="maintenance">Maintenance</option>
                </select>
              </div>

              <div class="form-group">
                <label>Catatan</label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
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

  <?php foreach ($getAllRooms as $rooms): ?>
    <form action="<?php echo site_url('GHRooms/editKamar/' . $rooms['id']); ?>" method="post">
      <div class="modal fade" id="modalEditRoom<?= $rooms['id'] ?>">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form id="formRoom">
              <div class="modal-header">
                <h4 class="modal-title">Form Kamar</h4>
                <button type="button" class="close" data-dismiss="modal">
                  <span>&times;</span>
                </button>
              </div>

              <div class="modal-body">

                <input type="hidden" id="id" name="id" value="<?= $rooms['id'] ?>">

                <div class="form-group">
                  <label>Lokasi</label>
                  <select class="form-control" id="status" name="code">
                    <option value="GH 1" <?php if ($rooms['code'] == 'GH 1') { ?>selected <?php } ?>>GH 1
                    </option>
                    <option value="GH 2" <?php if ($rooms['code'] == 'GH 2') { ?>selected <?php } ?>>GH 2
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Nama Kamar</label>
                  <input type="text" class="form-control" id="name" name="name" value="<?= $rooms['name'] ?>">
                </div>

                <div class="form-group">
                  <label>Tipe Kamar</label>
                  <select class="form-control" id="room_type_id" name="room_type_id">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($types as $t): ?>
                      <option value="<?= $t['id'] ?>" <?= isset($rooms['room_type_id']) && $rooms['room_type_id'] == $t['id'] ? 'selected' : '' ?>>
                        <?= $t['name'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label>Harga</label>
                  <input type="text" class="form-control" id="price" name="price" value="<?= $rooms['price'] ?>">
                </div>

                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control" id="status" name="status">
                    <option value="available" <?php if ($rooms['status'] == 'available') { ?>selected <?php } ?>>Tersedia
                    </option>
                    <option value="occupied" <?php if ($rooms['status'] == 'occupied') { ?>selected <?php } ?>>Terisi
                    </option>
                    <option value="maintenance" <?php if ($rooms['status'] == 'maintenance') { ?>selected <?php } ?>>
                      Maintenance</option>
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Catatan</label>
                  <textarea id="notes" name="notes" class="form-control"><?= $rooms['notes'] ?></textarea>
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

<?php $this->session->set_flashdata('statusAlert', 'kosong'); ?>

<script type="text/javascript">

  $(function () {
    <?php if ($statusAlert == 'sukses_tambah') { ?>
      swal("Success!", "Berhasil Ditambah!", "success");
    <?php } else if ($statusAlert == 'sukses_hapus') { ?>
        swal("Success!", "Berhasil Dihapus!", "success");
    <?php } else if ($statusAlert == 'sukses_edit') { ?>
          swal("Success!", "Berhasil Edit Data!", "success");
    <?php } else if ($statusAlert == 'gagal_tambah') { ?>
            swal("Gagal!", "Gagal Menambah Data!", "warning");
    <?php } else if ($statusAlert == 'gagal_edit') { ?>
              swal("Gagal!", "Gagal Mengedit Data!", "warning");
    <?php } else if ($statusAlert == 'gagal_hapus') { ?>
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
    $('#roomsTable').DataTable({
      "paging": true, // Tetap gunakan pagination
      "pageLength": 10, // Menampilkan 10 data per halaman
      "info": true, // Menghilangkan "Showing 1 to X of X entries"
      "searching": true, // Menghilangkan search bar
      "lengthChange": true // Menghilangkan dropdown "Show entries"
    });
  });

  $(document).ready(function () {
    $.fn.dataTable.ext.errMode = 'none';
    const table = $('#roomsTable').DataTable({
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
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHRooms.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">