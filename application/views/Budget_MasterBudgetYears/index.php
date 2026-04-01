<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

$total = 1;

$unique_akun_utama = array_unique(array_column($getAllData, 'mab_akun_utama'));
$unique_sub_akun = array_unique(array_column($getAllData, 'mab_sub_akun'));
$unique_nomor_akun = array_unique(array_column($getAllData, 'mab_nomor_akun'));
$unique_deskripsi_akun = array_unique(array_column($getAllData, 'mab_deskripsi_akun'));
$unique_divisi = array_unique(array_column($getAllData, 'mab_divisi'));
$unique_pic = array_unique(array_column($getAllData, 'mab_pic'));

// sort($unique_akun_utama, SORT_STRING | SORT_FLAG_CASE);


?>


<meta name="format-detection" content="telephone=no">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">MASTER ANNUAL BUDGET</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <!-- DIRECT CHAT DANGER -->
                <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title">FILTER DATA</h3>

                        <div class="card-tools">
                            <button id="cardfiltercollapse" type="button" class="btn btn-tool"
                                data-card-widget="collapse">
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
                                        <label style="display: flex; justify-content: center; align-items: center;">AKUN
                                            UTAMA
                                        </label>
                                        <select id="filter_akun_utama" class="select2" multiple="multiple"
                                            data-placeholder="Pilih akun utama" style="width: 100%;">
                                            <?php foreach ($unique_akun_utama as $akun_utama): ?>
                                                <option value="<?= $akun_utama ?>"><?= $akun_utama ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label style="display: flex; justify-content: center; align-items: center;">SUB
                                            AKUN
                                        </label>
                                        <select id="filter_sub_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih sub akun" style="width: 100%;">
                                            <?php foreach ($unique_sub_akun as $sub_akun): ?>
                                                <option value="<?= $sub_akun ?>"><?= $sub_akun ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">NOMOR
                                            AKUN
                                        </label>
                                        <select id="filter_nomor_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih nomor akun" style="width: 100%;">
                                            <?php foreach ($unique_nomor_akun as $nomor_akun): ?>
                                                <option value="<?= $nomor_akun ?>"><?= $nomor_akun ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label
                                            style="display: flex; justify-content: center; align-items: center;">DETAIL
                                            AKUN</label>
                                        <select id="filter_deskripsi_akun" class="select2" multiple="multiple"
                                            data-placeholder="Pilih deskripsi akun" style="width: 100%;">
                                            <?php foreach ($unique_deskripsi_akun as $deskripsi_akun): ?>
                                                <option value="<?= $deskripsi_akun ?>"><?= $deskripsi_akun ?></option>
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

    <div class="content">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 justify-content-center">
                    <div class="col-sm-6 text-center">
                        <button type="button" class="btn btn-gradient-primary btn-lg shadow pulse" data-toggle="modal"
                            data-target="#modal-lg-tambah-masterakun">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <strong>TAMBAH MASTER</strong>
                        </button>
                    </div>
                </div>
            </div>
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
                            <div class="card-body table-responsive text-nowrap">
                                <table id="tabel_budget_monthly" class="table table-bordered table-hover">
                                    <thead class="bg-info">
                                        <tr>
                                            <th>No</th>
                                            <th>Akun Utama</th>
                                            <th>Sub Akun</th>
                                            <th>Nomor Akun</th>
                                            <th>Deskripsi Akun</th>
                                            <th>Budget Tahunan</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" style="text-align:right">TOTAL :</th>
                                            <th id="total_tahunan"></th>
                                            <th id="total_monthly"></th>
                                            <th id="total_selisih"></th>
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

        <div class="modal fade" id="modalMonthly" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">

                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">
                            Detail Budget Bulanan - <span id="judulTahun"></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <table class="table table-bordered table-striped">
                            <thead class="bg-info">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Bulan</th>
                                    <th width="25%">Budget</th>
                                </tr>
                            </thead>
                            <tbody id="tableMonthlyBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="2" class="text-right">TOTAL</th>
                                    <th id="totalBudget" class="text-right">0</th>
                                </tr>
                            </tfoot>
                        </table>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="btnSaveMonthly">
                            <i class="fa fa-save"></i> Save All
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <form action="<?php echo site_url('Budget_MasterBudgetYears/addMasterBudget'); ?>" method="post">
            <div class="modal fade" id="modal-lg-tambah-masterakun">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">🧾 TAMBAH MASTER AKUN BIAYA</h4>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Akun Utama</label>
                                    <select id="addfilter_akun_utama" name="addfilter_akun_utama" class="form-control"
                                        style="width:100%;">
                                        <option value="" selected disabled hidden>Pilih Akun Utama</option>
                                        <?php foreach ($unique_akun_utama as $akun_utama): ?>
                                            <option value="<?= $akun_utama ?>"><?= $akun_utama ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="text-right mt-2">
                                        <button type="button" id="btnTambahAkunUtamaBaru"
                                            class="btn btn-link text-primary p-0" style="font-weight:600;">
                                            + Tambah Akun Utama Baru
                                        </button>
                                    </div>
                                    <div id="inputAkunUtamaBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputAkunUtamaBaru" name="inputAkunUtamaBaru"
                                            class="form-control" placeholder="Ketik Akun Utama baru..."
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Sub Akun</label>
                                    <select id="addfilter_sub_akun" name="addfilter_sub_akun"
                                        class="form-control area-dropdown" data-placeholder="Pilih Sub Akun"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Sub Akun</option>
                                        <?php foreach ($unique_sub_akun as $sub_akun): ?>
                                            <option value="<?= $sub_akun ?>"><?= $sub_akun ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Tombol tambah kota -->
                                    <div class="text-right mt-2">
                                        <button type="button" id="btnTambahSubAkunBaru"
                                            class="btn btn-link text-primary p-0" style="font-weight:600;">
                                            + Tambah Sub Akun Baru
                                        </button>
                                    </div>

                                    <div id="inputSubAkunBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputSubAkunBaru" name="inputSubAkunBaru"
                                            class="form-control" placeholder="Ketik Sub Akun baru..."
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Nomor Akun</label>
                                    <div id="inputNomorAkunBaruContainer">
                                        <input type="text" id="inputNomorAkunBaru" name="inputNomorAkunBaru"
                                            class="form-control" placeholder="Ketik Nomor Akun baru..."
                                            autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Deskripsi Akun</label>
                                    <div id="inputDeskripsiAkunBaruContainer">
                                        <input type="text" id="inputDeskripsiAkunBaru" name="inputDeskripsiAkunBaru"
                                            class="form-control" placeholder="Ketik Deskripsi Akun baru..."
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Divisi</label>
                                    <select id="addfilter_divisi" name="addfilter_divisi"
                                        class="form-control area-dropdown" data-placeholder="Pilih Area"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Divisi</option>
                                        <?php foreach ($unique_divisi as $divisi): ?>
                                            <option value="<?= $divisi ?>"><?= $divisi ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Tombol tambah kota -->
                                    <div class="text-right mt-2">
                                        <button type="button" id="btnTambahDivisiBaru"
                                            class="btn btn-link text-primary p-0" style="font-weight:600;">
                                            + Tambah Divisi Baru
                                        </button>
                                    </div>

                                    <div id="inputDivisiBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputDivisiBaru" name="inputDivisiBaru"
                                            class="form-control" placeholder="Ketik Divisi baru..." autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">PIC</label>
                                    <select id="addfilter_pic" name="addfilter_pic" class="form-control area-dropdown"
                                        data-placeholder="Pilih Area" style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih PIC</option>
                                        <?php foreach ($unique_pic as $pic): ?>
                                            <option value="<?= $pic ?>"><?= $pic ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <!-- Tombol tambah kota -->
                                    <div class="text-right mt-2">
                                        <button type="button" id="btnTambahPICBaru"
                                            class="btn btn-link text-primary p-0" style="font-weight:600;">
                                            + Tambah PIC Baru
                                        </button>
                                    </div>

                                    <div id="inputPICBaruContainer" style="display:none; margin-top:10px;">
                                        <input type="text" id="inputPICBaru" name="inputPICBaru" class="form-control"
                                            placeholder="Ketik PIC baru..." autocomplete="off">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                            <button type="submit" name="btnEdit" class="btn btn-primary">
                                <i class="fa fa-spinner fa-spin loading" style="display:none"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form action="<?php echo site_url('Budget_MasterBudgetYears/editMasterBudget'); ?>" method="post">
            <div class="modal fade" id="modal-lg-edit-masterakun">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">🧾 EDIT MASTER AKUN BIAYA</h4>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id_mab" id="edit_id_mab">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Akun Utama</label>
                                    <select id="editfilter_akun_utama" name="editfilter_akun_utama" class="form-control"
                                        style="width:100%;">
                                        <option value="" selected disabled hidden>Pilih Akun Utama</option>
                                        <?php foreach ($unique_akun_utama as $akun_utama): ?>
                                            <option value="<?= $akun_utama ?>"><?= $akun_utama ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Sub Akun</label>
                                    <select id="editfilter_sub_akun" name="editfilter_sub_akun"
                                        class="form-control area-dropdown" data-placeholder="Pilih Sub Akun"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Sub Akun</option>
                                        <?php foreach ($unique_sub_akun as $sub_akun): ?>
                                            <option value="<?= $sub_akun ?>"><?= $sub_akun ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Nomor Akun</label>
                                    <div id="editNomorAkunBaruContainer">
                                        <input type="text" id="editNomorAkunBaru" name="editNomorAkunBaru"
                                            class="form-control" placeholder="Ketik Nomor Akun baru..."
                                            autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Deskripsi Akun</label>
                                    <div id="editDeskripsiAkunBaruContainer">
                                        <input type="text" id="editDeskripsiAkunBaru" name="editDeskripsiAkunBaru"
                                            class="form-control" placeholder="Ketik Deskripsi Akun baru..."
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">Divisi</label>
                                    <select id="editfilter_divisi" name="editfilter_divisi"
                                        class="form-control area-dropdown" data-placeholder="Pilih Area"
                                        style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih Divisi</option>
                                        <?php foreach ($unique_divisi as $divisi): ?>
                                            <option value="<?= $divisi ?>"><?= $divisi ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label class="col-form-label">PIC</label>
                                    <select id="editfilter_pic" name="editfilter_pic" class="form-control area-dropdown"
                                        data-placeholder="Pilih Area" style="width: 100%;">
                                        <option value="" selected disabled hidden>Pilih PIC</option>
                                        <?php foreach ($unique_pic as $pic): ?>
                                            <option value="<?= $pic ?>"><?= $pic ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                                <button type="submit" name="btnEdit" class="btn btn-primary">
                                    <i class="fa fa-spinner fa-spin loading" style="display:none"></i>
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </form>



        <!-- /.content-wrapper -->



        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>

    </div>

    <script>
        const userLevel = "<?= $this->session->userdata('nama_level'); ?>";
    </script>

    <script>

        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            <?php if ($status == 'sukses_tambah') { ?>
                swal.fire("Success!", "Berhasil Ditambah!", "success");
            <?php } else if ($status == 'sukses_hapus') { ?>
                    swal.fire("Success!", "Berhasil Dihapus!", "success");
            <?php } else if ($status == 'sukses_edit') { ?>
                        swal.fire("Success!", "Berhasil Edit Data!", "success");
            <?php } else if ($status == 'gagal_tambah') { ?>
                            swal.fire("Gagal!", "Gagal Menambah Data!", "warning");
            <?php } else if ($status == 'gagal_edit') { ?>
                                swal.fire("Gagal!", "Gagal Mengedit Data!", "warning");
            <?php } else if ($status == 'gagal_hapus') { ?>
                                    swal.fire("Gagal!", "Gagal Menghapus Data!", "warning");
            <?php } else { ?>
            <?php } ?>

            <?php $this->session->set_flashdata('status', 'kosong'); ?>

        })

        $(document).ready(function () {
            $('#tabel_budget_monthly').DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,     // aktifkan scroll horizontal otomatis
                responsive: false,   // matikan agar kolom tetap sejajar
                ordering: true,
                initComplete: function () {
                    // pastikan wrapper scroll ikut lebar layar
                    $('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
        });

        $(document).ready(function () {
            $('.card[data-card-widget="collapsed"]').addClass('card-tools');
        });

        // document.addEventListener("DOMContentLoaded", function () {
        //     let cardfilter = document.getElementById("cardfiltercollapse").closest(".card");
        //     cardfilter.classList.add("collapsed-card");
        // });

    </script>

    <script>
        $(document).on('click', '.btn-edit-akun', function (e) {
            e.preventDefault();

            const btn = $(this);

            $('#edit_id_mab').val(btn.data('id'));

            $('#editfilter_akun_utama').val(btn.data('akun-utama')).trigger('change');
            $('#editfilter_sub_akun').val(btn.data('sub-akun')).trigger('change');
            $('#editfilter_divisi').val(btn.data('divisi')).trigger('change');
            $('#editfilter_pic').val(btn.data('pic')).trigger('change');

            $('#editNomorAkunBaru').val(btn.data('nomor-akun'));
            $('#editDeskripsiAkunBaru').val(btn.data('deskripsi'));

            $('#modal-lg-edit-masterakun').modal('show');
        });
    </script>

    <script>
        var allData = <?= json_encode($getAllData) ?>;

        $(document).ready(function () {
            // === PETA KOLOM DAN FILTER ===
            const colMap = {
                filter_akun_utama: 'mab_akun_utama',
                filter_sub_akun: 'mab_sub_akun',
                filter_nomor_akun: 'mab_nomor_akun',
                filter_deskripsi_akun: 'mab_deskripsi_akun'
            };

            const currentSelections = {
                filter_akun_utama: [],
                filter_sub_akun: [],
                filter_nomor_akun: [],
                filter_deskripsi_akun: []
            };

            $('.select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: function () {
                    return $(this).data('placeholder') || 'Pilih';
                }
            });

            // === Helper Filter Antar-Select ===
            function getFilteredDataByAllSelections(excludeId = null) {
                return allData.filter(item => {
                    for (const selId in currentSelections) {
                        if (selId === excludeId) continue;
                        const selectedVals = currentSelections[selId] || [];
                        if (selectedVals.length === 0) continue;
                        const col = colMap[selId];
                        if (!selectedVals.map(String).includes(String(item[col]))) return false;
                    }
                    return true;
                });
            }

            function populateSelectFromFilteredData(selectId) {
                const $sel = $('#' + selectId);
                const col = colMap[selectId];
                const filtered = getFilteredDataByAllSelections(selectId);
                const unique = [...new Set(filtered.map(it => it[col]).filter(v => v !== null && v !== undefined && v !== ''))];
                const prevSelected = ($sel.val() || []).map(String);

                $sel.empty();
                unique.forEach(v => $sel.append(`<option value="${v}">${v}</option>`));

                const toSelect = unique.filter(u => prevSelected.includes(String(u)));
                $sel.val(toSelect.length ? toSelect : null);
                $sel.trigger('change.select2');
            }

            function onSelectChange(changedId) {
                currentSelections[changedId] = ($('#' + changedId).val() || []).map(String);
                for (const selId in colMap) populateSelectFromFilteredData(selId);
            }

            Object.keys(colMap).forEach(selId => {
                $('#' + selId).on('change', function () {
                    onSelectChange(selId);
                });
            });

            Object.keys(colMap).forEach(populateSelectFromFilteredData);

            // === EVENT: FILTER DATA ===
            $('#btnFilterDataProject').on('click', function (e) {
                e.preventDefault();

                const filters = {
                    mab_akun_utama: $('#filter_akun_utama').val(),
                    mab_sub_akun: $('#filter_sub_akun').val(),
                    mab_nomor_akun: $('#filter_nomor_akun').val(),
                    mab_deskripsi_akun: $('#filter_deskripsi_akun').val(),
                };

                $.ajax({
                    url: '<?= base_url("Budget_MasterBudgetYears/getFilteredBudget_MasterBudgetYearsAjax") ?>',
                    type: 'POST',
                    data: filters,
                    dataType: 'json',
                    beforeSend: function () {
                        $('#btnFilterDataProject i.loading').show();
                    },
                    success: function (response) {
                        if ($.fn.DataTable.isDataTable('#tabel_budget_monthly')) {
                            $('#tabel_budget_monthly').DataTable().clear().destroy();
                        }

                        // === HEADER ===
                        let theadHtml = '<tr>';
                        response.columns.forEach(col => theadHtml += `<th>${col}</th>`);
                        theadHtml += '</tr>';
                        $('#tabel_budget_monthly thead').html(theadHtml);

                        // === FOOTER ===
                        let tfootHtml = '<tr>';
                        response.columns.forEach((col, index) => {

                            if (col === 'Budget Tahunan') {
                                tfootHtml += `<th id="total_tahunan">0</th>`;
                            }
                            else if (col === 'Budget Monthly') {
                                tfootHtml += `<th id="total_monthly">0</th>`;
                            }
                            else if (col === 'Selisih') {
                                tfootHtml += `<th id="total_selisih">0</th>`;
                            }
                            else if (index === 0) {
                                tfootHtml += `<th style="text-align:right">Total :</th>`;
                            }
                            else {
                                tfootHtml += `<th></th>`;
                            }

                        });

                        tfootHtml += '</tr>';
                        $('#tabel_budget_monthly tfoot').html(tfootHtml);

                        // === BODY ===
                        if (!response.data || response.data.length === 0) {
                            $('#tabel_budget_monthly tbody').html(
                                `<tr><td colspan="${response.columns.length}" class="text-center">No data available</td></tr>`
                            );
                        } else {
                            let tbodyHtml = '';

                            response.data.forEach((row, i) => {
                                tbodyHtml += `<tr>`;

                                response.columns.forEach(col => {
                                    // console.log(response.columns);
                                    switch (col) {
                                        case 'No':
                                            tbodyHtml += `<td>${i + 1}</td>`;
                                            break;

                                        case 'Akun Utama':
                                            tbodyHtml += `<td>${row.mab_akun_utama || '-'}</td>`;
                                            break;

                                        case 'Sub Akun':
                                            tbodyHtml += `<td>${row.mab_sub_akun || '-'}</td>`;
                                            break;

                                        case 'Nomor Akun':
                                            tbodyHtml += `<td>${row.mab_nomor_akun || '-'}</td>`;
                                            break;

                                        case 'Deskripsi Akun':
                                            tbodyHtml += `<td>${row.mab_deskripsi_akun || '-'}</td>`;
                                            break;

                                        case 'Budget Tahunan':
                                            tbodyHtml += `<td>${row.total_budget
                                                ? Number(row.total_budget).toLocaleString('id-ID')
                                                : '-'}</td>`;
                                            break;
                                        case 'Budget Monthly':
                                            tbodyHtml += `<td>${row.total_monthly
                                                ? Number(row.total_monthly).toLocaleString('id-ID')
                                                : '0'
                                                }</td>`;
                                            break;

                                        case 'Selisih':

                                            let selisih = Number(row.selisih || 0);

                                            let warna = selisih == 0
                                                ? 'text-success'
                                                : 'text-danger';

                                            tbodyHtml += `<td class="${warna}">
        ${selisih.toLocaleString('id-ID')}
    </td>`;
                                            break;
                                    }
                                });

                                if (userLevel === 'Super Admin') {
                                    tbodyHtml += `
                        <td class="text-center">
                        <a 
                               class="btn btn-primary btn-sm tombol_detail" data-id="${row.id_budget_years}">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="<?= site_url('Budget_MasterBudgetYears/hapusMasterBudget/') ?>${row.id_mab}"
                               class="btn btn-danger btn-sm tombol_hapus">
                                <i class="fas fa-trash"></i>
                            </a>

                            <a href="#"
   class="btn btn-warning btn-sm btn-edit-akun"
   data-id="${row.id_mab}"
   data-akun-utama="${row.mab_akun_utama}"
   data-sub-akun="${row.mab_sub_akun}"
   data-nomor-akun="${row.mab_nomor_akun}"
   data-deskripsi="${row.mab_deskripsi_akun}"
   data-divisi="${row.mab_divisi}"
   data-pic="${row.mab_pic}">
    <i class="fas fa-edit"></i>
</a>
                        </td>
                    `;
                                } else {
                                    tbodyHtml += `<td>-</td>`;
                                }

                                tbodyHtml += `</tr>`;

                                tbodyHtml += `</tr>`;
                            });

                            $('#tabel_budget_monthly tbody').html(tbodyHtml);
                            console.log('userLevel =', userLevel);
                        }

                        // === DATATABLE ===
                        const table = $('#tabel_budget_monthly').DataTable({
                            responsive: true,
                            autoWidth: false,
                            pageLength: 10,
                            ordering: true,
                            order: [[1, 'desc']],
                            footerCallback: function (row, data, start, end, display) {

                                let api = this.api();

                                function convertAngka(i) {
                                    return typeof i === 'string'
                                        ? Number(i.replace(/\./g, ''))
                                        : typeof i === 'number'
                                            ? i
                                            : 0;
                                }

                                // ===== TOTAL BUDGET TAHUNAN =====
                                let totalTahunan = api
                                    .column(5, { search: 'applied' })
                                    .data()
                                    .reduce(function (a, b) {
                                        return convertAngka(a) + convertAngka(b);
                                    }, 0);

                                // ===== TOTAL MONTHLY =====
                                let totalMonthly = api
                                    .column(6, { search: 'applied' })
                                    .data()
                                    .reduce(function (a, b) {
                                        return convertAngka(a) + convertAngka(b);
                                    }, 0);

                                // ===== TOTAL SELISIH =====
                                let totalSelisih = api
                                    .column(7, { search: 'applied' })
                                    .data()
                                    .reduce(function (a, b) {
                                        return convertAngka(a) + convertAngka(b);
                                    }, 0);

                                // ===== TAMPILKAN DI FOOTER =====
                                $('#total_tahunan').html(totalTahunan.toLocaleString('id-ID'));
                                $('#total_monthly').html(totalMonthly.toLocaleString('id-ID'));
                                $('#total_selisih').html(totalSelisih.toLocaleString('id-ID'));

                            }

                        });

                        // === Nomor Otomatis ===
                        table.on('order.dt search.dt', function () {
                            table.column(0, { search: 'applied', order: 'applied' })
                                .nodes().each(function (cell, i) {
                                    cell.innerHTML = i + 1;
                                });
                        }).draw();
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                    },
                    complete: function () {
                        $('#btnFilterDataProject i.loading').hide();
                    }
                });
            });

            // === AUTO CLICK SAAT AWAL ===
            setTimeout(() => {
                $('#btnFilterDataProject').trigger('click');
            }, 500);

            // === RESET FILTER ===
            $('#reset_filter').on('click', function () {
                $('#filter_akun_utama, #filter_sub_akun, #filter_nomor_akun, #filter_deskripsi_akun').val(null).trigger('change');
                setTimeout(() => $('#btnFilterDataProject').trigger('click'), 300);
            });
        });

        $(document).on('click', '.tombol_hapus', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');

            Swal.fire({
                icon: 'warning',
                title: 'Hapus data?',
                text: 'Data yang dihapus tidak bisa dikembalikan',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = url;
                }
            });
        });

        const namaBulan = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $(document).on('click', '.tombol_detail', function () {

            let id = $(this).data('id');

            $('#modalMonthly').modal('show');
            $('#tableMonthlyBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

            $.ajax({
                url: "<?= site_url('Budget_MasterBudgetYears/getMonthlyDetail') ?>",
                type: "POST",
                data: { id_budget_years: id },
                dataType: "json",
                success: function (res) {

                    let html = '';
                    let totalBudget = 0;

                    res.forEach((row, index) => {

                        totalBudget += Number(row.budget_bulan || 0);

                        html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${namaBulan[row.bulan]}</td>
                        <td>
        <input type="text"
            class="form-control budget-input"
            data-id_budget_monthly="${row.id_budget_monthly}"
            value="${Number(row.budget_bulan).toLocaleString('id-ID')}">
    </td>
                    </tr>
                `;
                    });

                    $('#tableMonthlyBody').html(html);

                    $('#totalBudget').text(
                        totalBudget.toLocaleString('id-ID')
                    );

                    $('#id_budget_years').val(id);


                }
            });
        });

        $(document).on('input', '.budget-input', function () {

            let posisi = this.selectionStart;

            let angka = $(this).val().replace(/\D/g, '');
            let formatted = Number(angka).toLocaleString('id-ID');

            $(this).val(formatted);

            this.setSelectionRange(posisi, posisi);

            hitungTotal();
        });

        function hitungTotal() {

            let total = 0;

            $('.budget-input').each(function () {

                let angka = $(this).val().replace(/\./g, '');
                total += parseInt(angka || 0);

            });

            $('#totalBudget').text(
                total.toLocaleString('id-ID')
            );
        }

        $('#btnSaveMonthly').click(function () {

            let data = [];

            $('.budget-input').each(function () {

                let rawValue = $(this).val().replace(/\./g, '');

                data.push({
                    id_budget_monthly: $(this).data('id_budget_monthly'),
                    budget_bulan: parseInt(rawValue || 0)
                });

            });

            console.log("Data yang dikirim:", data);

            $.ajax({
                url: "<?= site_url('Budget_MasterBudgetYears/updateMonthly') ?>",
                type: "POST",
                data: { data: data },
                success: function () {
                    alert('Budget berhasil diupdate!');
                    $('#modalMonthly').modal('hide');
                    location.reload();
                }
            });

        });

        $(document).ready(function () {
            // === VALIDASI SAAT SIMPAN ===
            $("form").on("submit", function (e) {
                e.preventDefault();

                // 🔹 Validasi tetap dipertahankan (jangan dihapus)

                const inputAkunUtamaBaru = $('#inputAkunUtamaBaru');
                const inputSubAkunBaru = $('#inputSubAkunBaru');
                const inputDivisiBaru = $('#inputDivisiBaru');
                const inputPICBaru = $('#inputPICBaru');

                const akunutamaDropdown = $('#addfilter_akun_utama');
                const subakunDropdown = $('#addfilter_sub_akun');
                const divisiDropdown = $('#addfilter_divisi');
                const picDropdown = $('#addfilter_pic');

                let akunutama = akunutamaDropdown.val(); // ✅ Tambahkan ini
                let subakun = subakunDropdown.val(); // ✅ Tambahkan ini
                let divisi = divisiDropdown.val(); // ✅ Tambahkan ini
                let pic = picDropdown.val(); // ✅ Tambahkan ini

                let nomorakun = $("#inputNomorAkunBaru").val();
                let deskripsiakun = $("#inputDeskripsiAkunBaru").val();

                // 🔹 Jika tambah kota baru aktif → pakai nilai input kota baru
                if (inputAkunUtamaBaru.is(':visible') && inputAkunUtamaBaru.val().trim() !== '') {
                    akunutama = inputAkunUtamaBaru.val().trim(); // ✅ overwrite nilai akun utama
                    akunutamaDropdown.val(akunutama); // agar ikut terkirim via serialize
                }

                if (inputSubAkunBaru.is(':visible') && inputSubAkunBaru.val().trim() !== '') {
                    subakun = inputSubAkunBaru.val().trim(); // ✅ overwrite nilai sub akun
                    subakunDropdown.val(subakun); // agar ikut terkirim via serialize
                }

                if (inputDivisiBaru.is(':visible') && inputDivisiBaru.val().trim() !== '') {
                    divisi = inputDivisiBaru.val().trim(); // ✅ overwrite nilai divisi
                    divisiDropdown.val(divisi); // agar ikut terkirim via serialize
                }

                if (inputPICBaru.is(':visible') && inputPICBaru.val().trim() !== '') {
                    pic = inputPICBaru.val().trim(); // ✅ overwrite nilai PIC
                    picDropdown.val(pic); // agar ikut terkirim via serialize
                }

                if (!akunutama) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi a.'
                    });
                    return;
                }

                if (!subakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi b.'
                    });
                    return;
                }

                if (!divisi) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi c.'
                    });
                    return;
                }

                if (!pic) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi d.'
                    });
                    return;
                }

                if (!nomorakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi e.'
                    });
                    return;
                }

                if (!deskripsiakun) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap!',
                        text: 'Pastikan semua dropdown dan input sudah diisi f.'
                    });
                    return;
                }

                // 🔹 Konfirmasi simpan
                Swal.fire({
                    icon: 'question',
                    title: 'Simpan Data?',
                    text: 'Pastikan semua data sudah benar sebelum disimpan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = $("form").serialize();

                        $.ajax({
                            url: "<?= base_url('Budget_MasterBudgetYears/addMasterBudget') ?>",
                            type: "POST",
                            data: formData,
                            dataType: "json",
                            success: function (res) {
                                console.log(res);

                                if (res.status === 'not_found') {
                                    Swal.fire({
                                        icon: 'question',
                                        title: 'Area belum terdaftar',
                                        text: 'Project ini tidak memiliki area ini. Tambahkan area dan invoice?',
                                        showCancelButton: true,
                                        confirmButtonText: 'Ya, Tambahkan!',
                                        cancelButtonText: 'Batal'
                                    }).then((r) => {
                                        if (r.isConfirmed) {
                                            $.ajax({
                                                url: "<?= base_url('Budget_MasterBudgetYears/createNewTargetInvoice') ?>",
                                                type: "POST",
                                                dataType: "json",
                                                data: res,

                                                success: function (res2) {
                                                    Swal.fire({
                                                        icon: res2.status ? 'success' : 'error',
                                                        title: res2.status ? 'Berhasil' : 'Gagal',
                                                        text: res2.message
                                                    }).then(() => location.reload());
                                                }
                                            });
                                        }
                                    });
                                } else if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Invoice berhasil disimpan.'
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: res.message || 'Terjadi kesalahan.'
                                    });
                                }
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Invoice berhasil disimpan.'
                                }).then(() => location.reload());
                            }
                        });
                    }
                });
            });

            // Reset form ketika modal dibuka
            $('#modal-lg-tambah-masterakun').on('show.bs.modal', function () {
                $(this).find('form')[0].reset();
                $("[name='achiev_invoice'], [name='tambahan_invoice'], [name='total_invoice']").val("");
                $("[name='tambahan_invoice']").closest('.form-group').hide();
                $("[name='total_invoice']").closest('.form-group').hide();
            });

        });

        $(document).ready(function () {
            let isTambahAkunUtamaActive = false;
            let isTambahSubAkunActive = false;
            let isTambahDivisiActive = false;
            let isTambahPicActive = false;

            $('#btnTambahAkunUtamaBaru').on('click', function () {
                isTambahAkunUtamaActive = !isTambahAkunUtamaActive;
                if (isTambahAkunUtamaActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputAkunUtamaBaruContainer').slideDown();
                    $('#addfilter_akun_utama').prop('disabled', true);
                    $('#addfilter_akun_utama').val('');
                    $(this).text('× Batalkan Tambah Akun Utama');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputAkunUtamaBaruContainer').slideUp();
                    $('#addfilter_akun_utama').prop('disabled', false);
                    $('#inputAkunUtamaBaru').val('');
                    $(this).text('+ Tambah Akun Utama Baru');

                }
            });

            $('#btnTambahSubAkunBaru').on('click', function () {
                isTambahSubAkunActive = !isTambahSubAkunActive;
                if (isTambahSubAkunActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputSubAkunBaruContainer').slideDown();
                    $('#addfilter_sub_akun').prop('disabled', true);
                    $('#addfilter_sub_akun').val('');
                    $(this).text('× Batalkan Tambah Sub Akun');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputSubAkunBaruContainer').slideUp();
                    $('#addfilter_sub_akun').prop('disabled', false);
                    $('#inputSubAkunBaru').val('');
                    $(this).text('+ Tambah Sub Akun Baru');
                }
            });

            $('#btnTambahDivisiBaru').on('click', function () {
                isTambahDivisiActive = !isTambahDivisiActive;
                if (isTambahDivisiActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputDivisiBaruContainer').slideDown();
                    $('#addfilter_divisi').prop('disabled', true);
                    $('#addfilter_divisi').val('');
                    $(this).text('× Batalkan Tambah Divisi');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputDivisiBaruContainer').slideUp();
                    $('#addfilter_divisi').prop('disabled', false);
                    $('#inputDivisiBaru').val('');
                    $(this).text('+ Tambah Divisi Baru');
                }
            });

            $('#btnTambahPICBaru').on('click', function () {
                isTambahPicActive = !isTambahPicActive;
                if (isTambahPicActive) {
                    // Saat aktif → tampilkan input, disable dropdown
                    $('#inputPICBaruContainer').slideDown();
                    $('#addfilter_pic').prop('disabled', true);
                    $('#addfilter_pic').val('');
                    $(this).text('× Batalkan Tambah PIC');

                } else {
                    // Saat nonaktif → sembunyikan input, enable dropdown
                    $('#inputPICBaruContainer').slideUp();
                    $('#addfilter_pic').prop('disabled', false);
                    $('#inputPICBaru').val('');
                    $(this).text('+ Tambah PIC Baru');
                }
            });

            // Pastikan saat submit form, area_target diambil sesuai yang aktif
            $('form').on('submit', function (e) {
                const isTambahAkunUtamaBaru = isTambahAkunUtamaActive;
                const isTambahSubAkunBaru = isTambahSubAkunActive;
                const isTambahDivisiBaru = isTambahDivisiActive;
                const isTambahPicBaru = isTambahPicActive;

                const akunutamaDropdown = $('#addfilter_akun_utama').val();
                const akunutamaBaru = $('#inputAkunUtamaBaru').val().trim();

                const subakunDropdown = $('#addfilter_sub_akun').val();
                const subakunBaru = $('#inputSubAkunBaru').val().trim();

                const divisiDropdown = $('#addfilter_divisi').val();
                const divisiBaru = $('#inputDivisiBaru').val().trim();

                const picDropdown = $('#addfilter_pic').val();
                const picBaru = $('#inputPICBaru').val().trim();

                const nomorAkunBaru = $('#inputNomorAkunBaru').val().trim();
                const deskripsiAkunBaru = $('#inputDeskripsiAkunBaru').val().trim();
                if (isTambahAkunUtamaBaru && akunutamaBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akun Utama baru belum diisi!',
                        text: 'Silakan isi nomor akun utama baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahSubAkunBaru && subakunBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sub Akun baru belum diisi!',
                        text: 'Silakan isi nama sub akun baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahDivisiBaru && divisiBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Divisi baru belum diisi!',
                        text: 'Silakan isi nama divisi baru sebelum melanjutkan.'
                    });
                    return false;
                }

                if (isTambahPicBaru && picBaru === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'PIC baru belum diisi!',
                        text: 'Silakan isi nama PIC sebelum melanjutkan.'
                    });
                    return false;
                }

            });
        });
    </script>



    <style>
        #tabel_budget_monthly tfoot th {
            text-align: right;
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* upgrade warna header merah */

        .glow-red {
            border: 1px solid rgba(255, 0, 0, 0.7);
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-red:hover {
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.9);
        }

        /* upgrade warna header hijau */
        .glow-green {
            border: 1px solid rgba(0, 255, 0, 0.7);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.6);
            transition: box-shadow 0.3s ease-in-out;
            border-radius: 10px;
        }

        .glow-green:hover {
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.9);
        }


        /* upgrade warna table */

        .cell-red {
            font-color: #ffb3b3 !important;
            color: #eb0000ff !important;
            font-weight: bold;
        }

        /* 🟢 Hijau muda untuk = 100% */
        .cell-green-light {
            color: #b3ffb3 !important;
            font-weight: bold;
        }

        /* 🟩 Hijau tua untuk > 100% */
        .cell-green-dark {
            color: #33cc33 !important;
            font-weight: bold;
            font-weight: bold;
        }

        /* Gradien biru menarik */
        .btn-gradient-primary {
            background: linear-gradient(45deg, #007bff, #00c6ff);
            border: none;
            color: #fff;
            border-radius: 50px;
            transition: all 0.3s ease-in-out;
            padding: 12px 25px;
            letter-spacing: 1px;
        }

        /* Efek hover */
        .btn-gradient-primary:hover {
            background: linear-gradient(45deg, #0056b3, #0099cc);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        /* Efek glowing lembut */
        .btn-gradient-primary:focus,
        .btn-gradient-primary:active {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        /* Animasi berdenyut (pulse effect) */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4);
            }

            70% {
                box-shadow: 0 0 0 15px rgba(0, 123, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
            }
        }

        /* ======== TAMBAHAN STYLE UNTUK MODAL TAMBAH INVOICE ======== */
        #modal-lg-tambah-masterakun .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        #modal-lg-tambah-masterakun .modal-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1rem 1.5rem;
        }

        #modal-lg-tambah-masterakun .modal-header h4 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        #modal-lg-tambah-masterakun .modal-body {
            background-color: #f9fafc;
            padding: 1.5rem;
            border-radius: 0 0 15px 15px;
        }

        #modal-lg-tambah-masterakun .form-group label {
            font-weight: 600;
            color: #495057;
        }

        #modal-lg-tambah-masterakun .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        #modal-lg-tambah-masterakun .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #modal-lg-tambah-masterakun .modal-footer {
            background: #f1f3f6;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        #modal-lg-tambah-masterakun .btn-primary {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #modal-lg-tambah-masterakun .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        #modal-lg-tambah-masterakun .btn-danger {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
        }

        /* ======== editAN STYLE UNTUK MODAL edit INVOICE ======== */
        #modal-lg-edit-masterakun .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            animation: fadeInUp 0.3s ease-out;
        }

        #modal-lg-edit-masterakun .modal-header {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1rem 1.5rem;
        }

        #modal-lg-edit-masterakun .modal-header h4 {
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        #modal-lg-edit-masterakun .modal-body {
            background-color: #f9fafc;
            padding: 1.5rem;
            border-radius: 0 0 15px 15px;
        }

        #modal-lg-edit-masterakun .form-group label {
            font-weight: 600;
            color: #495057;
        }

        #modal-lg-edit-masterakun .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: all 0.2s ease-in-out;
        }

        #modal-lg-edit-masterakun .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #modal-lg-edit-masterakun .modal-footer {
            background: #f1f3f6;
            border-top: 1px solid #dee2e6;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        #modal-lg-edit-masterakun .btn-primary {
            background: linear-gradient(135deg, #007bff, #6610f2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #modal-lg-edit-masterakun .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        #modal-lg-edit-masterakun .btn-danger {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
        }



        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tombol pemicu modal (judul TAMBAH INVOICE) */
        .text-primary.font-weight-bold {
            background: linear-gradient(90deg, #007bff, #6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .text-primary.font-weight-bold:hover {
            transform: scale(1.05);
            text-shadow: 0 0 10px rgba(102, 16, 242, 0.4);
        }

        #btnTambahAkunUtamaBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahAkunUtamaBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahSubAkunBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahSubAkunBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahDivisiBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahDivisiBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }

        #btnTambahPICBaru {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        #btnTambahPICBaru:hover {
            text-decoration: underline;
            transform: scale(1.05);
        }
    </style>