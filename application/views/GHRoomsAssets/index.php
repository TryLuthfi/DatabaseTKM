<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;
?>

<!-- <?php $now = date('Y-m-d') . " 00:00:00"; ?> -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <section class="content">

    <div class="content-header">
      <h1 class="m-0 text-dark ">List Assets Kamar</h1>
    </div>

    <section class="content">

        <!-- PILIH KAMAR -->
        <div class="card">
            <div class="card-body">
                <label>Pilih Kamar</label>
                <select id="selectRoom" class="form-control">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= $r['code'] ?> - <?= $r['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- LIST ASET -->
        <div class="card">
            <div class="card-header">
                <button class="btn btn-primary" id="btnAdd" disabled>
                    + Tambah Aset
                </button>
            </div>

            <div class="card-body">
                <table class="table table-bordered" id="tabelAset">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Aset</th>
                            <th>Deskripsi</th>
                            <th>Kondisi</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </section>
  </section>

  <div class="modal fade" id="modalAset">
    <div class="modal-dialog">
        <form id="formAset">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">Form Aset Kamar</h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="aset_id">

                    <label>Jenis Aset</label>
                    <select id="asset_type_id" class="form-control" required>
                        <option value="">-- Pilih Aset --</option>
                        <?php foreach ($asset_types as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= $a['label'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="mt-2">Deskripsi</label>
                    <input type="text" id="description" class="form-control">

                    <label class="mt-2">Kondisi</label>
                    <select id="asset_condition" class="form-control">
                        <option value="good">Baik</option>
                        <option value="broken">Rusak</option>
                        <option value="missing">Hilang</option>
                    </select>

                    <label class="mt-2">Catatan</label>
                    <textarea id="note" class="form-control"></textarea>

                </div>

                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>

            </div>
        </form>
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

    let selectedRoom = null;

    $("#selectRoom").change(function () {
        selectedRoom = $(this).val();
        $("#btnAdd").prop("disabled", !selectedRoom);
        loadData();
    });

    function loadData() {
        if (!selectedRoom) return;

        $.get("<?= base_url('GHRoomsAssets/list_by_room/') ?>" + selectedRoom, function (data) {
            const row = JSON.parse(data);
            let html = "";
            let no = 1;

            row.forEach(r => {
                html += `
                    <tr>
                        <td>${no++}</td>
                        <td>${r.asset_name}</td>
                        <td>${r.description ?? '-'}</td>
                        <td>${r.asset_condition}</td>
                        <td>${r.note ?? '-'}</td>
                        <td>
                            <button class="btn btn-warning btnEdit" data-id="${r.id}">Edit</button>
                            <button class="btn btn-danger btnDelete" data-id="${r.id}">Hapus</button>
                        </td>
                    </tr>
                `;
            });

            $("#tabelAset tbody").html(html);
        });
    }

    // === ADD ===
    $("#btnAdd").click(function () {
        $("#aset_id").val("");
        $("#formAset")[0].reset();
        $("#modalAset").modal("show");
    });

    // === EDIT ===
    $(document).on("click", ".btnEdit", function () {
        let id = $(this).data("id");

        $.get("<?= base_url('GHRoomsAssets/get/') ?>" + id, function (data) {
            let d = JSON.parse(data);

            $("#aset_id").val(d.id);
            $("#asset_type_id").val(d.asset_type_id);
            $("#description").val(d.description);
            $("#asset_condition").val(d.asset_condition);
            $("#note").val(d.note);

            $("#modalAset").modal("show");
        });
    });

    // === SAVE ===
    $("#formAset").submit(function (e) {
        e.preventDefault();

        let id = $("#aset_id").val();
        let url = id
            ? "<?= base_url('GHRoomsAssets/update/') ?>" + id
            : "<?= base_url('GHRoomsAssets/add') ?>";

        $.post(url, {
            room_id: selectedRoom,
            asset_type_id: $("#asset_type_id").val(),
            description: $("#description").val(),
            asset_condition: $("#asset_condition").val(),
            note: $("#note").val()
        }, function (res) {
            let r = JSON.parse(res);

            Swal.fire("Success!", "Data berhasil disimpan", "success");
            $("#modalAset").modal("hide");
            loadData();
        });
    });

    // === DELETE ===
    $(document).on("click", ".btnDelete", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Hapus aset ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya"
        }).then(res => {
            if (res.isConfirmed) {
                $.get("<?= base_url('GHRoomsAssets/delete/') ?>" + id, function () {
                    Swal.fire("Success!", "Aset berhasil dihapus", "success");
                    loadData();
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
<script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHRoomsAssets.js"></script>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">