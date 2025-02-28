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
                        <div class="card-header d-flex p-0">
                            <h3 class="card-title p-3">List Purchase Request Area</h3>
                            <ul class="nav nav-pills ml-auto p-2 pr-3">
                                <li class="nav-item"><a href="#" class="btn btn-success text-bold btn_tambah_pr" data-target="#modal_tambah_pr" data-toggle="modal">Tambah Purchase Request</i></a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <table id="tabel_purchase_request_area" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nomor PR</th>
                                        <th>Tanggal</th>
                                        <th>Nama Bowheer</th>
                                        <th>Lokasi Project</th>
                                        <th>Nama Project</th>
                                        <th>Pembuat</th>
                                        <th>Status</th>
                                        <!-- <th>Document</th> -->
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php $numbering = 1 ?>
                                    <?php foreach ($list_purchase_request_area as $key => $value) { ?>
                                        <tr>
                                            <td><?= $numbering++ ?></td>
                                            <td><?= $value['nomor_purchase_request'] ?></td>
                                            <td><?= $value['tanggal_pembuatan'] ?></td>
                                            <td><?= $value['nama_project'] ?></td>
                                            <td><?= $value['kota_lokasi_gudang'] ?></td>
                                            <td><?= $value['nama_projects'] ?></td>
                                            <td><?= $value['nama_pembuat'] ?></td>
                                            <?php if ($value['approved_planning'] == 1 && $value['approved_finance'] == 1 && $value['approved_direktur'] == 1) { ?>
                                                <td><span class="badge badge-success">Approved</span></td>
                                            <?php } else { ?>
                                                <td><span class="badge badge-warning">Waiting Approval <?= rtrim(($value['approved_planning'] != 1 ? 'Planning, ' : '') .  ($value['approved_finance'] != 1 ? 'Finance, ' : '') .  ($value['approved_direktur'] != 1 ? 'Direktur, ' : ''),  ', ') ?></span></td>
                                            <?php } ?>
                                            <!-- <td>Belum Upload Dokumen</td> -->
                                            <td>
                                                <a class="btn btn-danger btn-delete-purchase-request" href="javascript:void(0);" data-id="<?= $value['id_purchase_request'] ?>"><i class="fa fa-trash"></i></a>
                                                <a class="btn btn-primary ml-2" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') . '/' . $value['id_purchase_request'] ?>"><i class="fa fa-eye"></i></a>
                                                <a class="btn btn-warning ml-2"><i class="fa fa-print"></i></a>
                                                <a class="btn btn-warning ml-2" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') . '/' . $value['id_purchase_request'] ?>" <?= $this->session->userdata('validation') != 'Planning' ? 'hidden' : '' ?>>Planning <i class="fa fa-edit ml-1"></i></a>
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- START MODAL TAMBAH PO -->

                            <div class="modal fade" id="modal_tambah_pr" data-backdrop="static">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <form action="<?= base_url('Logistik_Purchase_Request/add_purchase_request') ?>" method="post" id="tambah_purchase_reqeust" enctype="multipart/form-data">
                                            <div class="modal-header">
                                                <div class="modal-title">
                                                    <h4>Tambah Purchase Request</h4>
                                                </div>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Nomor PR</label>
                                                            <input type="text" class="form-control" name="nomor_pr" id="nomor_pr" placeholder="TEC.001/TKM-SK/PR/I/2025" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Tanggal PR</label>
                                                            <input type="date" class="form-control" name="tanggal_upload_pr" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Bowheer</label>
                                                            <select name="nama_bowher" id="nama_bowher" class="form-control">
                                                                <option value="">Pilih Salah Satu</option>
                                                                <?php foreach ($get_master_project as $key => $value) { ?>
                                                                    <option value="<?= $value['nama_bowheer'] ?>" data-id-bowheer="<?php echo $value['nama_bowheer'] ?>"><?= $value['nama_bowheer'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Lokasi Project</label>
                                                            <select name="lokasi_project" id="lokasi_project" class="form-control">
                                                                <option value="">Pilih Salah Satu</option>
                                                                <?php foreach ($list_master_gudang as $key => $value) { ?>
                                                                    <option value="<?= $value['id_lokasi_gudang'] ?>"><?= $value['kota_lokasi_gudang'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr> <!-- GARIS PEMBATAS -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Nama Project</label>
                                                            <input type="text" class="form-control" name="nama_project" id="nama_project" placeholder="Fiberisasi">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Nomor SP</label>
                                                            <input type="text" class="form-control" name="nomor_sp" id="nomor_sp" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Tanggal SP</label>
                                                            <input type="date" class="form-control" name="tanggal_sp" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Tanggal Estimasi Pengiriman</label>
                                                            <input type="date" class="form-control" name="tanggal_pengiriman" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Nama Material</label>
                                                            <select name="nama_material" id="nama_material" class="form-control">
                                                                <option value="">Pilih Salah Satu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr> <!-- GARIS PEMBATAS -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <table class="table table-bordered" id="table_item_purchase_request">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 5%;">No</th>
                                                                    <th style="width: 30%;">Nama</th>
                                                                    <th>Satuan</th>
                                                                    <th style="width: 15%;">Stock Area</th>
                                                                    <th style="width: 15%;">Stok Request</th>
                                                                    <th>Keterangan</th>
                                                                    <th>Hapus</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="4"><b>TOTAL</b></td>
                                                                    <td><b>0</b></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary">Tambah Purchase Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- END MODAL TAMBAH PO -->
                        </div>
                    </div>
                </div>
                <?php if ($this->session->userdata('lokasi_user') == 'HO') { ?>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex p-0">
                            <h3 class="card-title p-3">Purchase Request HO</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabel_purchase_request_ho" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor PR</th>
                                            <th>Tanggal</th>
                                            <th>Nama Bowheer</th>
                                            <th>Lokasi Project</th>
                                            <th>Nama Project</th>
                                            <th>Pembuat</th>
                                            <th>Status</th>
                                            <!-- <th>Document</th> -->
                                            <th>Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php $numbering = 1 ?>
                                        <?php foreach ($list_purchase_request_ho as $key => $value) { ?>
                                            <tr>
                                                <td><?= $numbering++ ?></td>
                                                <td><?= $value['nomor_purchase_request'] ?></td>
                                                <td><?= $value['tanggal_pembuatan'] ?></td>
                                                <td><?= $value['nama_project'] ?></td>
                                                <td><?= $value['kota_lokasi_gudang'] ?></td>
                                                <td><?= $value['nama_projects'] ?></td>
                                                <td><?= $value['nama_pembuat'] ?></td>
                                                <?php if ($value['approved_planning'] == 1 && $value['approved_finance'] == 1 && $value['approved_direktur'] == 1) { ?>
                                                    <td><span class="badge badge-success">Approved</span></td>
                                                <?php } else { ?>
                                                    <td><span class="badge badge-warning">Waiting Approval <?= rtrim(($value['approved_planning'] != 1 ? 'Planning, ' : '') .  ($value['approved_finance'] != 1 ? 'Finance, ' : '') .  ($value['approved_direktur'] != 1 ? 'Direktur, ' : ''),  ', ') ?></span></td>
                                                <?php } ?>
                                                <!-- <td>Belum Upload Dokumen</td> -->
                                                <td>
                                                    <a class="btn btn-danger btn-delete-purchase-request" href="javascript:void(0);" data-id="<?= $value['id_purchase_request'] ?>"><i class="fa fa-trash"></i></a>
                                                    <a class="btn btn-primary ml-2" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') . '/' . $value['id_purchase_request'] ?>"><i class="fa fa-eye"></i></a>
                                                    <a class="btn btn-warning ml-2"><i class="fa fa-print"></i></a>
                                                    <a class="btn btn-warning ml-2" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') . '/' . $value['id_purchase_request'] ?>" <?= $this->session->userdata('validation') != 'Planning' ? 'hidden' : '' ?>>Planning <i class="fa fa-edit ml-1"></i></a>
                                                </td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {

        var counter = 1;

        // INISIALISASI DATATABLES
        $('#tabel_purchase_request_area').DataTable();
        $('#tabel_purchase_request_ho').DataTable();

        // INISIALISASI SELECT2
        $('#nama_bowher').select2({
            placeholder: 'Pilih Salah Satu',
        });

        $('#nama_material').select2({
            placeholder: 'Pilih Salah Satu',
        });

        $('#lokasi_project').select2({
            placeholder: 'Pilih Salah Satu',
        });

        // KETIKA NAMA PROJECT DIPILIH
        $('#nama_bowher').change(function() {
            let idBowheer = $(this).find(':selected').data('id-bowheer');

            if (idBowheer === "") {
                $('#nama_material').empty().append('<option value="">Pilih Jenis Material</option>').trigger('change');
                return;
            }

            $.ajax({
                url: "<?= base_url() . 'Dashboard_Logistik_Stok/getProjectByBowheer' ?>",
                type: "GET",
                data: {
                    id_bowheer: idBowheer.toString()
                },
                dataType: "json",
                success: function(response) {

                    $('#table_item_purchase_request tbody').empty();
                    counter = 1;

                    $('#nama_material').empty().append('<option value="">Pilih Jenis Material</option>');

                    $.each(response, function(index, project) {
                        $('#nama_material').append(
                            `<option value="${project.id_kode_item}" data-satuan-item="${project.satuan_item}">${project.nama_item}</option>`
                        );
                    });

                    $('#nama_material').trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // KETIKA NAMA MATERIAL DIPILIH
        $('#nama_material').on('change', function() {
            if ($(this).val() === "") {
                return;
            }
            var selectedValue = $('#nama_material').val();
            var selectedText = $('#nama_material option:selected').text();
            var selectedSatuan = $('#nama_material option:selected').data('satuan-item');

            if (selectedValue === "") {
                alert("Pilih item terlebih dahulu!");
                return;
            }

            $('#table_item_purchase_request tbody').append(`
                <tr>
                    <td>${counter}</td>
                    <td hidden><input type="hidden" name="id_kode_item_[${counter}]" value="${selectedValue}">${selectedValue}</td>
                    <td>${selectedText}</td>
                    <td>${selectedSatuan}</td>
                    <td><input type="number" class="form-control" name="stok_area_[${counter}]" autocomplete="off" placeholder="1.000"></td>
                    <td><input type="number" class="form-control" name="stok_request_[${counter}]" autocomplete="off" placeholder="5.000" required></td>
                    <td><input type="text" class="form-control" name="keterangan_[${counter}]" autocomplete="off" placeholder="Keterangan"></td>
                    <td><button class="btn btn-danger hapus-item"><i class="fa fa-trash"></i></button></td>
                </tr>
            `);
            counter++;

            $('#nama_material').val("").trigger('change');
        });

        // KETIKA HAPUS ITEM 
        $(document).on('click', '.hapus-item', function() {
            $(this).closest('tr').remove();

            $('#table_item_purchase_request tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });

            counter--;
        });

        // KETIKA TUTUP MODAL 

        $('#modal_tambah_pr').on('hidden.bs.modal', function() {
            $('#table_item_purchase_request tbody').empty();
            $('#nama_project').val('').trigger('change');
            $('#nama_bowher').val('').trigger('change');
            updateTotalKeseluruhan();

            counter = 1;
        });

        // KETIKA BUKA MODAL

        $('.btn_tambah_pr').on('click', function() {
            $('#nomor_pr').val('');
            $('#nomor_sp').val('');
            $('#lokasi_project').val('').trigger('change');
            updateTotalKeseluruhan();
        })

        // HITUNG TOTAL ITEM 
        $(document).on('input', '[name^="stok_request_["]', function() {
            let row = $(this).closest('tr');
            let jumlah = parseFloat(row.find('[name^="stok_request_["]').val()) || 0;
            let total = jumlah

            console.log(total);

            row.find('[name^="total_"]').val(total);

            updateTotalKeseluruhan();
        });

        function updateTotalKeseluruhan() {
            let totalSemua = 0;

            console.log(totalSemua);

            // Loop setiap row dan jumlahkan totalnya dengan parsing yang benar
            $('[name^="stok_request_"]').each(function() {
                let totalText = $(this).val();

                // Hilangkan simbol mata uang dan ubah format desimal dari Rupiah (menggunakan koma) ke format angka JS (pakai titik)
                let total = parseFloat(totalText.replace(/[^0-9,]/g, '').replace(',', '.')) || 0;

                totalSemua += total;
            });

            // Masukkan ke dalam <tfoot>
            $('tfoot tr td b').eq(1).text(totalSemua);

            // Jika semua item dihapus, tampilkan kembali angka 0
            if (totalSemua === 0) {
                $('tfoot tr td b').eq(1).text('0');
            }
        }

        // KETIKA SUBMIT FORM 
        $("#tambah_purchase_reqeust").submit(function(event) {
            let isValid = true;
            let errorMessage = [];

            // Cek input tanggal harus valid
            let tanggalUpload = $("input[name='tanggal_upload_pr']").val();
            if (!tanggalUpload) {
                errorMessage.push("Tanggal upload stok harus diisi.");
            }

            // Cek setiap input yang harus memiliki nilai
            let requiredFields = {
                "#nama_bowher": "Nama Bowheer",
                "#lokasi_project": "Lokasi Proyek",
            };

            $.each(requiredFields, function(selector, fieldName) {
                if ($(selector).val() === "") {
                    errorMessage.push(fieldName + " harus diisi.");
                }
            });

            // Cek apakah tabel memiliki minimal 1 row
            if ($("#table_item_purchase_request tbody tr").length === 0) {
                errorMessage.push("Minimal harus ada satu item stok dalam tabel.");
            }

            // Jika ada error, tampilkan alert sekaligus
            if (errorMessage.length > 0) {
                alert(errorMessage.join("\n"));
                event.preventDefault();
            }
        });

        // SWEET ALERT DELETE 
        $(".btn-delete-purchase-request").click(function() {
            let id = $(this).data("id");

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "<?= base_url('Logistik_Purchase_Request/delete_purchase_request') ?>/" + id;
                }
            });
        });

    });
</script>