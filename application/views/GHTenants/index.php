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
      <h1 class="m-0 text-dark ">List Tenant</h1>
    </div>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <button class="btn btn-primary float-right" id="btnAddTenants">
            <i class="fas fa-plus"></i> Tambah Tenant
          </button>
        </div>

        <div class="card-body">
          <table class="table table-bordered table-striped" id="roomsTable">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>No HP</th>
                <th>NIK</th>
                <th>Alamat</th>
                <th>Tanggal Input</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach ($tenants as $t): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $t['fullname'] ?></td>
                    <td><?= $t['phone'] ?></td>
                    <td><?= $t['nik'] ?></td>
                    <td><?= $t['address'] ?></td>
                    <td><?= $t['created_at'] ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning btnEdit" data-id="<?= $t['id'] ?>">Edit</button>
                        <button class="btn btn-sm btn-danger btnDelete" data-id="<?= $t['id'] ?>">Hapus</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
          </table>
        </div>
      </div>
    </section>


  </section>

  <div class="modal fade" id="modalTenant">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="formTenant">

                <div class="modal-header">
                    <h4 class="modal-title">Form Tenant</h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="tenant_id">

                    <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" id="fullname" class="form-control" required>
                    </div>

                    <div class="form-group">
                    <label>No HP</label>
                    <input type="text" id="phone" class="form-control">
                    </div>

                    <div class="form-group">
                    <label>NIK</label>
                    <input type="text" id="nik" class="form-control">
                    </div>

                    <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" id="address" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
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
</script>
<script type="text/javascript">
  $(document).ready(function () {

    // Format mata uang.
    $('.nilai_po2').mask('000.000.000', { reverse: true });

  })

  $("#btnAddTenants").click(function () {
    $("#formTenant")[0].reset();
    $("#tenant_id").val("");
    $("#modalTenant").modal("show");
});

// === EDIT DATA ===
$(".btnEdit").click(function () {
    let id = $(this).data("id");

    $.get("<?= base_url('GHTenants/get/') ?>" + id, function (data) {
        let t = JSON.parse(data);

        $("#tenant_id").val(t.id);
        $("#fullname").val(t.fullname);
        $("#phone").val(t.phone);
        $("#nik").val(t.nik);
        $("#address").val(t.address);

        $("#modalTenant").modal("show");
    });
});

// === SUBMIT FORM ADD/EDIT ===
$("#formTenant").submit(function (e) {
    e.preventDefault();

    let id = $("#tenant_id").val();
    let url = id === "" 
        ? "<?= base_url('GHTenants/add') ?>"
        : "<?= base_url('GHTenants/update/') ?>" + id;

    $.ajax({
        url: url,
        type: "POST",
        data: {
            fullname: $("#fullname").val(),
            phone: $("#phone").val(),
            nik: $("#nik").val(),
            address: $("#address").val()
        },
        dataType: "json",
        success: function (res) {

            if (res.status === "sukses_tambah") {
                Swal.fire("Berhasil!", "Tenant berhasil ditambahkan.", "success")
                    .then(() => location.reload());
            }

            if (res.status === "sukses_edit") {
                Swal.fire("Berhasil!", "Tenant berhasil diperbarui.", "success")
                    .then(() => location.reload());
            }
        }
    });
});

// === DELETE DATA ===
$(".btnDelete").click(function () {
    let id = $(this).data("id");

    Swal.fire({
        icon: "warning",
        title: "Hapus Tenant?",
        showCancelButton: true,
        confirmButtonText: "Ya",
    }).then(res => {
        if (res.isConfirmed) {

            $.get("<?= base_url('GHTenants/delete/') ?>" + id, function (response) {
                let resJson = JSON.parse(response);

                if (resJson.status === "sukses_hapus") {
                    Swal.fire("Berhasil!", "Tenant telah dihapus.", "success")
                        .then(() => location.reload());
                } else {
                    Swal.fire("Gagal!", "Tidak dapat menghapus data.", "error");
                }
            });
        }
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
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHTenants.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">