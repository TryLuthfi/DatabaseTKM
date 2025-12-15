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
        <h1 class="text-center">LIST TENANTS - ROOMS</h1>
      </div>

      <section class="content">

        <div class="row mb-3">

          <div class="col-md-4">
            <label>Filter Tenants</label>
            <select id="filterTenant" class="form-control select2" multiple>
              <?php foreach ($tenants as $t): ?>
                <option value="<?= $t['id'] ?>"><?= $t['fullname'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label>Filter Rooms</label>
            <select id="filterRoom" class="form-control select2" multiple>
              <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>"><?= $r['name'] ?> (<?= $r['code'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label>Status</label>
            <select id="filterStatus" class="form-control select2" multiple>
              <option value="1">Active</option>
              <option value="0">Ended</option>
            </select>
          </div>

        </div>

        <!-- LIST PENGHUNI -->
        <div class="card">

          <div class="card-body">
            <table id="tableTenantsRooms" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Kamar</th>
                  <th>Nama Penghuni</th>
                  <th>Contract Start</th>
                  <th>Contract End</th>
                  <th>Billing Day</th>
                  <th>Status</th>
                  <th>#</th>
                </tr>
              </thead>
              <tbody id="bodyTenantsRooms">
                <?php $no = 1;
                foreach ($getAllTenantsRooms as $row): ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['code']; ?> (<?= $row['name']; ?>)</td>
                    <td><?= $row['fullname']; ?></td>
                    <td><?= $row['contract_start']; ?></td>
                    <td><?= $row['contract_ends']; ?></td>
                    <td><?= $row['billing_day']; ?></td>
                    <td>
                    <?php
                    $active = strtolower($row['active']);
                    $status_rooms_tenants = strtolower($row['active']);
                    switch ($active) {
                      case '1':
                        $badgeClass = 'badge-success'; // Warna hijau untuk available
                        $status_rooms_tenants = 'ACTIVE';
                        break;
                      case '0':
                        $badgeClass = 'badge-danger'; // Warna merah untuk occupied
                        $status_rooms_tenants = 'ENDED';
                        break;
                      case null:
                        $badgeClass = 'badge-secondary'; // Warna abu-abu untuk unknown
                        $status_rooms_tenants = 'EMPTY';
                        break;
                    }
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= strtoupper($status_rooms_tenants) ?></span>
                  </td>
                    <td>
                    <?php
                    if ($status_rooms_tenants == 'ACTIVE') {
                      ?> <button class="btn btn-success tombol_detail ml-1" disabled><i class=" fas fa-plus"></i></a></button> <?php
                      ?> <button class="btn btn-primary tombol_detail ml-1"><i class=" fas fa-eye"></i></a></button> <?php
                    } else if ($status_rooms_tenants == 'ENDED') {
                      ?> <button class="btn btn-success tombol_detail ml-1"><i class=" fas fa-plus"></i></a></button> <?php
                      ?> <button class="btn btn-primary tombol_detail ml-1"><i class=" fas fa-eye"></i></a></button> <?php
                    } else if ($status_rooms_tenants == 'EMPTY') {
                      ?> <button class="btn btn-success tombol_detail ml-1"><i class=" fas fa-plus"></i></a></button> <?php
                      ?> <button class="btn btn-primary tombol_detail ml-1"><i class=" fas fa-eye"></i></a></button> <?php
                    } else {
                      echo "-";
                    }
                    ?>
                  </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </section>


    </section>

    <div class="modal fade" id="modalStay">
      <div class="modal-dialog modal-lg">
        <form id="formStay">
          <div class="modal-content">
            <div class="modal-header bg-info">
              <h4 class="modal-title">Form Tenants Stay</h4>
            </div>

            <div class="modal-body">

              <input type="hidden" id="stay_id">

              <label>Rooms</label>
              <select id="room_id" class="form-control" required>
                <option value="">-- Pilih Kamar --</option>
                <?php foreach ($rooms as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= $t['name'] ?> (<?= $t['code'] ?>)</option>
                <?php endforeach; ?>
              </select>

              <label>Tenant</label>
              <select id="tenant_id" class="form-control" required>
                <option value="">-- Pilih Tenant --</option>
                <?php foreach ($tenants as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= $t['fullname'] ?></option>
                <?php endforeach; ?>
              </select>

              <label class="mt-2">Contract Start</label>
              <input type="date" id="contract_start" class="form-control">

              <label class="mt-2">Contract End</label>
              <input type="date" id="contract_end" class="form-control">

              <label class="mt-2">Billing Day (1–30)</label>
              <input type="number" min="1" max="30" id="billing_day" class="form-control">

              <label class="mt-2">Status</label>
              <select id="active" class="form-control">
                <option value="1">Aktif</option>
                <option value="0">Selesai</option>
              </select>

              <label class="mt-2">Catatan</label>
              <textarea id="notes" class="form-control"></textarea>

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

      $(".select2").select2({
        width: '100%',
        placeholder: "Pilih filter",
        allowClear: true
      });

      $('#filterTenant, #filterRoom, #filterStatus').on('change.select2', function () {
        console.log("Filter berubah → loadFilteredData terpanggil");
        loadFilteredData();
      });

    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {

      // Format mata uang.
      $('.nilai_po2').mask('000.000.000', { reverse: true });



    });



    let selectedRoom = null;

    $("#btnAdd").click(function () {
      $("#stay_id").val("");
      $("#formStay")[0].reset();
      $("#tenant_id").prop("disabled", false);
      $("#modalStay").modal("show");
    });

    $(document).on("click", ".btnEdit", function () {
      let id = $(this).data("id");

      $.get("<?= base_url('GHTenantsRooms/get/') ?>" + id, function (res) {
        let d = JSON.parse(res);

        $("#stay_id").val(d.id);
        $("#tenant_id").val(d.tenant_id).prop("disabled", true);
        $("#contract_start").val(d.contract_start);
        $("#contract_end").val(d.contract_end);
        $("#billing_day").val(d.billing_day);
        $("#active").val(d.active);
        $("#notes").val(d.notes);

        $("#modalStay").modal("show");
      });
    });

    $("#formStay").submit(function (e) {
      e.preventDefault();

      let id = $("#stay_id").val();
      let url = id
        ? "<?= base_url('GHTenantsRooms/update/') ?>" + id
        : "<?= base_url('GHTenantsRooms/add') ?>";

      $.post(url, {
        tenant_id: $("#tenant_id").val(),
        room_id: selectedRoom,
        contract_start: $("#contract_start").val(),
        contract_end: $("#contract_end").val(),
        billing_day: $("#billing_day").val(),
        active: $("#active").val(),
        notes: $("#notes").val()
      }, function () {
        Swal.fire("Success!", "Data berhasil disimpan", "success");
        $("#modalStay").modal("hide");
        loadData();
      });
    });

    $(document).on("click", ".btnDelete", function () {
      let id = $(this).data("id");

      Swal.fire({
        icon: "warning",
        title: "Hapus data ini?",
        showCancelButton: true,
        confirmButtonText: "Ya",
      }).then(res => {
        if (res.isConfirmed) {
          $.get("<?= base_url('GHTenantsRooms/delete/') ?>" + id, function () {
            Swal.fire("Success!", "Data berhasil dihapus", "success");
            loadData();
          });
        }
      });
    });

    function loadFilteredData() {

      Swal.fire({
        title: 'Loading...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: "<?= base_url('GHTenantsRooms/filter') ?>",
        type: "POST",
        data: {
          tenants: $("#filterTenant").val(),
          rooms: $("#filterRoom").val(),
          status: $("#filterStatus").val()
        },
        dataType: "json",
        success: function (res) {

          // Update Table
          let tbody = "";
          let no = 1;

          res.rows.forEach(r => {
            tbody += `
                      <tr>
                          <td>${no++}</td>
                          <td>${r.fullname}</td>
                          <td>${r.room_name} (${r.room_code})</td>
                          <td>${r.contract_start}</td>
                          <td>${r.contract_end}</td>
                          <td>${r.billing_day}</td>
                          <td>${r.active == 1 ? 'Active' : 'Ended'}</td>
                          <td>
                      <button class="btn btn-warning btnEdit" data-id="<?= $row['id']; ?>">Edit</button>
                      <button class="btn btn-danger btnDelete" data-id="<?= $row['id']; ?>">Delete</button>
                    </td>
                      </tr>`;
          });

          $("#bodyTenantsRooms").html(tbody);

          // Update Filter Dropdown
          updateFilterOptions("#filterTenant", res.filters.tenants);
          updateFilterOptions("#filterRoom", res.filters.rooms);
          updateFilterOptions("#filterStatus", res.filters.status);

          Swal.close(); // ← HILANGKAN LOADING
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat memuat data'
          });
        }
      });
    }

    // Helper update dropdown
    function updateFilterOptions(selector, availableValues) {
      $(selector + " option").each(function () {
        let val = $(this).val();
        if (val === "") return;

        if (availableValues.includes(val) || availableValues.includes(parseInt(val))) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }

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
  <script src="<?= base_url('assets') ?>/dist/js/pages/dashboardGHTenantsRooms.js"></script>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">