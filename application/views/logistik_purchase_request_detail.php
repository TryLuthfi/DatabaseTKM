<div class="content-wrapper">

    <section class="content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= $title ?></h1>
                    </div>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success">
                    <?= $this->session->flashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php
            $this->session->unset_userdata('success');
            $this->session->unset_userdata('error');
            ?>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix hidden-md-up">
                <div class="col-md-12">
                    <div class="card">
                        <form action="<?= base_url('Logistik_Purchase_Request/edit_purchase_request_by_planning') ?>" method="post" id="form-planning" enctype="multipart/form-data">
                            <!-- ID PURCHASE REQUEST -->
                            <input type="text" name="id_purchase_request" id="" value="<?= $detail_purchase_request[0]['id_purchase_request'] ?>" hidden>
                            <div class="card-header">
                                <h3 class="card-title"><?= $type == 'edit' ? 'Edit' : '' ?> Purchase Request Detail <?= $type == 'edit' ? 'By Planning' : '' ?></h3>
                            </div>
                            <div class="card-body table-scrollable">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Nomor PR</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['nomor_purchase_request'] ?></h5>
                                            <?php else : ?>
                                                <input type="text" class="form-control" name="nomor_pr" id="nomor_pr" value="<?= $detail_purchase_request[0]['nomor_purchase_request'] ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Tanggal PR</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['tanggal_pembuatan'] ?></h5>
                                            <?php else : ?>
                                                <input type="date" class="form-control" name="tanggal_upload_pr" autocomplete="off" value="<?= date('Y-m-d', strtotime($detail_purchase_request[0]['tanggal_pembuatan'])); ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Bowheer</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['id_project'] ?></h5>
                                            <?php else : ?>
                                                <select name="id_project" id="id_project" class="form-control" disabled>
                                                    <option value="<?= $detail_purchase_request[0]['id_project'] ?>"><?= $detail_purchase_request[0]['id_project'] ?></option>
                                                </select>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Lokasi Project</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['kota_lokasi_gudang'] ?></h5>
                                            <?php else : ?>
                                                <input type="text" class="form-control" name="lokasi_project" id="lokasi_project" value="<?= $detail_purchase_request[0]['kota_lokasi_gudang'] ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                                <hr> <!-- GARIS PEMBATAS -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Nama Project</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['nama_project'] ?></h5>
                                            <?php else : ?>
                                                <input type="text" class="form-control" name="nama_project" id="nama_project" value="<?= $detail_purchase_request[0]['nama_project'] ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Nomor SP</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['nomer_sp'] ?></h5>
                                            <?php else : ?>
                                                <input type="text" class="form-control" name="nomer_sp" id="nomer_sp" value="<?= $detail_purchase_request[0]['nomer_sp'] ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Tanggal SP</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['tanggal_sp'] ?></h5>
                                            <?php else : ?>
                                                <input type="date" class="form-control" name="tanggal_sp" autocomplete="off" value="<?= date('Y-m-d', strtotime($detail_purchase_request[0]['tanggal_sp'])); ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Tanggal Estimasi Pengiriman</label>
                                            <?php if ($type == 'view') : ?>
                                                <h5><?= $detail_purchase_request[0]['tanggal_estimasi_pengiriman'] ?></h5>
                                            <?php else : ?>
                                                <input type="date" class="form-control" name="tanggal_estimasi_pengiriman" autocomplete="off" value="<?= date('Y-m-d', strtotime($detail_purchase_request[0]['tanggal_estimasi_pengiriman'])); ?>" disabled>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                                <hr> <!-- GARIS PEMBATAS -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Status Approval</label>
                                            <h5>
                                                <?php echo ($detail_purchase_request[0]['approved_planning'] == 0)
                                                    ? '<span class="badge badge-warning" style="font-size:1rem!important">Belum Diapprove Manager Planning</span>'
                                                    : '<span class="badge badge-success" style="font-size:1rem!important">Sudah Diapprove Manager Planning</span>'; ?>

                                                <?php echo ($detail_purchase_request[0]['approved_finance'] == 0)
                                                    ? '<span class="badge badge-warning" style="font-size:1rem!important">Belum Diapprove Finance</span>'
                                                    : '<span class="badge badge-success" style="font-size:1rem!important">Sudah Diapprove Finance</span>'; ?>

                                                <?php echo ($detail_purchase_request[0]['approved_direktur'] == 0)
                                                    ? '<span class="badge badge-warning" style="font-size:1rem!important">Belum Diapprove Direktur</span>'
                                                    : '<span class="badge badge-success" style="font-size:1rem!important">Sudah Diapprove Direktur</span>'; ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-form-label">Dokumen Hardcopy PR</label>
                                            <div class="card">
                                                <div class="card-body">
                                                    <?php if ($detail_purchase_request[0]['hardcopy_file'] == null) : ?>
                                                        <span class="badge badge-warning">Belum Upload</span>
                                                    <?php else : ?>
                                                        <span class="badge badge-success">Sudah Upload</span>&nbsp; <a href="<?= base_url() ?>./uploads/<?= $detail_purchase_request[0]['hardcopy_file'] ?>" class="font-size-14 text-muted text-truncate" id="view_detail_surat_jalan" target="_blank"><u>View Document</u></a>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($type != 'view') : ?>
                                        <div class="col-md-6" hidden>
                                            <div class="form-group">
                                                <label class="col-form-label">Nama Material</label>
                                                <select name="nama_material" id="nama_material" class="form-control" <?= $type == 'view' ? 'readonly' : '' ?>>
                                                    <option value="">Pilih Salah Satu</option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                    <div class="col-md-12">
                                        <table class="table table-bordered mt-2" id="table_item_stok">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th>Nama</th>
                                                    <th style="width: 10%;">Satuan</th>
                                                    <th style="width: 8%;">Boq</th>
                                                    <th style="width: 8%;">Stock Area</th>
                                                    <th style="width: 8%;">Stok Request</th>
                                                    <th style="width: 8%;">Planning</th>
                                                    <th style="width: 18%;">Keterangan</th>
                                                    <th style="width: 18%;">Keterangan Planning</th>
                                                    <?php if ($type != 'view') : ?>
                                                        <th hidden>Hapus</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $number = 1; ?>
                                                <?php foreach ($detail_purchase_request as $key => $value) : ?>
                                                    <tr>
                                                        <td><?= $number++ ?> <input type="text" name="id_purchase_request_detail_[<?= $key ?>]" value="<?= $value['id_purchase_request_detail'] ?>" hidden></td>
                                                        <td><?= $value['nama_item'] ?></td>
                                                        <td><?= $value['satuan_item'] ?></td>
                                                        <?php if ($type != 'view') : ?>
                                                            <td> <input type="number" class="form-control" name="boq_[<?= $key ?>]" autocomplete="off" value="<?= $value['boq'] ?>"></td>
                                                        <?php else : ?>
                                                            <td><?= $value['boq'] ?></td>
                                                        <?php endif; ?>
                                                        <td class="stok_area"><?= $value['stok_area'] ?></td>
                                                        <td class="qty_request"><?= $value['qty_request'] ?></td>
                                                        <?php if ($type != 'view') : ?>
                                                            <td><input type="number" class="form-control qty_planning" name="qty_planning_[<?= $key ?>]" autocomplete="off" value="<?= $value['qty_planning'] ?>"></td>
                                                        <?php else : ?>
                                                            <td><?= $value['qty_planning'] ?></td>
                                                        <?php endif; ?>
                                                        <td><?= $value['keterangan'] ?></td>
                                                        <?php if ($type != 'view') : ?>
                                                            <td><input type="text" class="form-control" name="keterangan_planning_[<?= $key ?>]" autocomplete="off" value="<?= $value['keterangan_planning'] ?>"></td>
                                                        <?php else : ?>
                                                            <td><?= $value['keterangan_planning'] ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($type != 'view') : ?>
                                                            <td hidden>
                                                                <a href="#" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <?php if ($type == 'view') : ?>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3"><b>TOTAL</b></td>
                                                        <td><b><?= array_sum(array_column($detail_purchase_request, 'boq')) ?></b></td>
                                                        <td><b><?= array_sum(array_column($detail_purchase_request, 'stok_area')) ?></b></td>
                                                        <td><b><?= array_sum(array_column($detail_purchase_request, 'qty_request')) ?></b></td>
                                                        <td><b><?= array_sum(array_column($detail_purchase_request, 'qty_planning')) ?></b></td>
                                                    </tr>
                                                </tfoot>
                                            <?php else : ?>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3"><b>TOTAL</b></td>
                                                        <td><b id="total_boq">0</b></td>
                                                        <td><b id="total_stok_area">0</b></td>
                                                        <td><b id="total_qty_request">0</b></td>
                                                        <td><b id="total_qty_planning">0</b></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-body-secondary">
                                <a href="#" class="btn btn-primary"><i class="fa fa-print mr-1"></i> Print</a>
                                <?php if ($type == 'view') { ?>
                                    <a href="#" class="btn btn-success btn-approve  <?= ($this->session->userdata('validation') != 'Finance' || $detail_purchase_request[0]['approved_planning'] == 0) ? 'disabled' : '' ?>" data-id="<?= $detail_purchase_request[0]['id_purchase_request'] ?>" data-tipe="Finance"> <i class="fa fa-check mr-1"></i> Approve By Finance</a>
                                    <a href="#" class="btn btn-success btn-approve  <?= ($this->session->userdata('validation') != 'Direktur' || $detail_purchase_request[0]['approved_finance'] == 0) ? 'disabled' : '' ?>" data-id="<?= $detail_purchase_request[0]['id_purchase_request'] ?>" data-tipe="Direktur"> <i class="fa fa-check mr-1"></i> Approve By Direktur</a>
                                <?php } ?>
                                <a href="#" class="btn btn-primary btn-upload-hardcopy" data-target="#modal-upload-hardcopy" data-toggle="modal"><i class="fas fa-upload"></i>&nbsp; Upload Hardcopy</a>
                                <a href="#" class="btn btn-success btn-save-planning <?= $this->session->userdata('validation') != 'Planning' ? 'disabled' : '' ?>" <?= ($type == 'view') ? 'hidden' : '' ?>><i class="fa fa-envelope mr-1"></i> Simpan</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-upload-hardcopy" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('Logistik_Purchase_Request/upload_hardcopy') ?>" method="post" id="form-upload-hardcopy" enctype="multipart/form-data">
            <div class="modal-header">
                <h4 class="modal-title">Upload Hardcopy</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Upload Document</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <label class="custom-file-label" for="file-hardcopy">Choose file</label>
                                    <input type="file" name="file-hardcopy" id="file-hardcopy" class="custom-file-input" required>
                                    <input type="hidden" name="id_purchase_request" value="<?= $detail_purchase_request[0]['id_purchase_request'] ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    function hitungTotal() {
        let totalBoq = 0,
            totalStokArea = 0,
            totalQtyRequest = 0,
            totalQtyPlanning = 0;

        $("input[name^='boq_']").each(function() {
            totalBoq += parseFloat($(this).val()) || 0;
        });

        $(".stok_area").each(function() {
            totalStokArea += parseFloat($(this).text()) || 0;
        });

        $(".qty_request").each(function() {
            totalQtyRequest += parseFloat($(this).text()) || 0;
        });

        $(".qty_planning").each(function() {
            totalQtyPlanning += parseFloat($(this).val()) || 0;
        });

        $("#total_boq").text(totalBoq);
        $("#total_stok_area").text(totalStokArea);
        $("#total_qty_request").text(totalQtyRequest);
        $("#total_qty_planning").text(totalQtyPlanning);
    }

    $(document).ready(function() {
        hitungTotal();
        $(".qty_planning, input[name^='boq_']").on("input", function() {
            hitungTotal();
        });

        $(".btn-save-planning").click(function() {
            Swal.fire({
                title: "Apakah Anda yakin ingin menyimpan?",
                text: "Pastikan semua data sudah benar sebelum menyimpan!",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#form-planning").submit(); // Ganti dengan form ID yang sesuai
                }
            });
        });

        $(".btn-approve").click(function(e) {
            e.preventDefault(); // Mencegah default action dari <a> href

            let id = $(this).data("id"); // Ambil ID purchase request
            let tipe = $(this).data("tipe"); // Ambil tipe approval (finance)

            // Konfirmasi Swal
            Swal.fire({
                title: "Apakah Anda yakin ingin menyetujui?",
                text: "Setelah disetujui, data tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Ya, Setujui!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "<?= base_url('Logistik_Purchase_Request/approve_purchase_request') ?>",
                        type: "POST",
                        data: {
                            id_purchase_request: id,
                            tipe: tipe
                        },
                        success: function(response) {
                            Swal.fire("Berhasil!", "Purchase Request telah disetujui.", "success")
                                .then(() => location.reload()); // Reload halaman setelah sukses
                        },
                        error: function() {
                            Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi.", "error");
                        }
                    });
                }
            });
        });

        $('.custom-file-input').on('change', function(e) {
            let file = this.files[0];
            let allowedExtensions = /\.pdf$/i; // Hanya PDF
            let maxSize = 5120 * 1024; // 5MB
            let fileName = file ? file.name : "Choose file";

            // Update label
            $(this).siblings('.custom-file-label').text(fileName);

            // Validasi ekstensi
            if (file && !allowedExtensions.test(file.name)) {
                alert("File harus berupa PDF!");
                $(this).val("");
                $(this).siblings('.custom-file-label').text("Choose file");
                return;
            }

            // Validasi ukuran file
            if (file && file.size > maxSize) {
                alert("Ukuran file tidak boleh lebih dari 5MB!");
                $(this).val("");
                $(this).siblings('.custom-file-label').text("Choose file");
                return;
            }
        });

    });
</script>