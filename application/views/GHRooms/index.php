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
      <h1 class="m-0 text-dark ">List Kamar</h1>
    </div>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <button class="btn btn-primary float-right" id="btnAddRoom">
            <i class="fas fa-plus"></i> Tambah Kamar
          </button>
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
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rooms as $r): ?>
                <tr>
                  <td><?= $total++ ?></td>
                  <td><?= $r['code'] ?></td>
                  <td><?= $r['name'] ?></td>
                  <td><?= $r['type_name'] ?></td>
                  <td>Rp <?= number_format($r['price_default'], 0, ",", ".") ?></td>
                  <td><span class="badge badge-info"><?= strtoupper($r['status']) ?></span></td>
                  <td>
                    <button class="btn btn-sm btn-warning btnEdit" data-id="<?= $r['id'] ?>">
                      <i class="fas fa-edit"></i>
                    </button>

                    <button class="btn btn-sm btn-danger btnDelete" data-id="<?= $r['id'] ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </section>

  <div class="modal fade" id="modalRoom">
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
              <label>Kode</label>
              <input type="text" class="form-control" id="code" required>
            </div>

            <div class="form-group">
              <label>Nama Kamar</label>
              <input type="text" class="form-control" id="name">
            </div>

            <div class="form-group">
              <label>Tipe Kamar</label>
              <select class="form-control" id="room_type_id">
                <option value="">-- Pilih --</option>
                <?php foreach ($types as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Harga</label>
              <input type="text" class="form-control" id="price">
            </div>

            <div class="form-group">
              <label>Status</label>
              <select class="form-control" id="status">
                <option value="available">Tersedia</option>
                <option value="occupied">Terisi</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>

            <div class="form-group">
              <label>Catatan</label>
              <textarea id="notes" class="form-control"></textarea>
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

</div>

<?php $this->session->set_flashdata('status', 'kosong'); ?>


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
<script type="text/javascript">
  $(document).ready(function () {

    // Format mata uang.
    $('.nilai_po2').mask('000.000.000', { reverse: true });

  })

  $(document).ready(function () {

    $("#roomsTable").DataTable();

    $("#btnAddRoom").click(function () {
      $("#formRoom")[0].reset();
      $("#room_id").val("");
      $("#modalRoom .modal-title").text("Tambah Kamar");
      $("#modalRoom").modal("show");
    });

    // Simpan
    $("#formRoom").submit(function (e) {
      e.preventDefault();

      let id = $("#room_id").val();
      let url = id === ""
        ? "<?= base_url('GHRooms/add') ?>"
        : "<?= base_url('GHRooms/update/') ?>" + id;

      $.ajax({
        url: url,
        type: "POST",
        data: {
          room_type_id: $("#room_type_id").val(),
          code: $("#code").val(),
          name: $("#name").val(),
          price: $("#price").val(),
          status: $("#status").val(),
          notes: $("#notes").val()
        },
        dataType: "json",
        success: function (res) {

          if (res.status === "sukses_tambah") {
            swal("Success!", "Berhasil menambah DATA!", "success");
          }
          else if (res.status === "sukses_edit") {
            swal("Success!", "Berhasil mengubah DATA!", "success");
          }
          else if (res.status === "gagal_tambah") {
            swal("Gagal!", "Gagal menambah DATA!", "error");
          }
          else if (res.status === "gagal_edit") {
            swal("Gagal!", "Gagal mengubah DATA!", "error");
          }

          setTimeout(() => location.reload(), 1500);
        }
      });
    });

    // Edit
    $(".btnEdit").click(function () {
      let id = $(this).data("id");

      $.get("<?= base_url('GHRooms/edit/') ?>" + id, function (res) {
        $("#room_id").val(res.id);
        $("#code").val(res.code);
        $("#name").val(res.name);
        $("#room_type_id").val(res.room_type_id);
        $("#price").val(res.price);
        $("#status").val(res.status);
        $("#notes").val(res.notes);

        $("#modalRoom .modal-title").text("Edit Kamar " + id);
        $("#modalRoom").modal("show");
      }, "json");
    });

    // Hapus
    $(".btnDelete").click(function () {
      let id = $(this).data("id");

      Swal.fire({
        icon: "warning",
        title: "Hapus kamar?",
        text: "Data kamar akan dihapus permanen!",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal"
      }).then(res => {

        if (res.isConfirmed) {

          $.ajax({
            url: "<?= base_url('GHRooms/delete/') ?>" + id,
            type: "GET",
            dataType: "json",
            success: function (res) {

              if (res.status === "sukses_hapus") {
                Swal.fire("Berhasil!", "Data berhasil dihapus.", "success")
                  .then(() => location.reload());
              }
              else if (res.status === "gagal_hapus") {
                Swal.fire("Gagal!", "Gagal menghapus data.", "error");
              }
            },
            error: function () {
              Swal.fire("Error!", "Terjadi kesalahan server.", "error");
            }
          });

        }
      });
    });

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