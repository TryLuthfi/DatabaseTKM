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
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix hidden-md-up">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">List Purchase Request</h3>
                                </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-success float-right text-bold btn-tambah-data-item"
                                        data-target="#modal_tambah_po" data-toggle="modal">Tambah
                                        &nbsp;<i class="fas fa-plus"></i> </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_pesanan_pabrik" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nomor PO</th>
                                        <th>Tanggal</th>
                                        <th>Nama Project</th>
                                        <th>Lokasi Project</th>
                                        <th>Pembuat</th>
                                        <th>Status</th>
                                        <th>Document</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>TEC.002/TKM-SK/PO/I/2025</td>
                                        <td>17-01-2025</td>
                                        <td>IFORTE</td>
                                        <td>Padang Sidempuan</td>
                                        <td>HO</td>
                                        <td><span class="badge badge-warning">Waiting Approval</span></td>
                                        <td>Belum Upload Dokumen</td>
                                        <td>
                                            <a class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                            <a class="btn btn-warning ml-2" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') ?>"><i class="fa fa-edit"></i></a>
                                            <a class="btn btn-primary ml-2" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') ?>"><i class="fa fa-eye"></i></a>
                                            <a class="btn btn-warning ml-2"><i class="fa fa-print"></i></a>
                                            <a class="btn btn-warning ml-2">Planning <i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>TEC.002/TKM-SK/PO/II/2025</td>
                                        <td>17-01-2025</td>
                                        <td>IFORTE</td>
                                        <td>Padang Sidempuan</td>
                                        <td>Area</td>
                                        <td><span class="badge badge-success">Approved</span></td>
                                        <td><a href="">Purchase_Request.pdf</a></td>
                                        <td>
                                            <a class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                            <a class="btn btn-warning ml-2" href="<?= base_url('Logistik_Purchase_Request/edit_purchase_request') ?>" ><i class="fa fa-edit"></i></a>
                                            <a class="btn btn-primary ml-2" href="<?= base_url('Logistik_Purchase_Request/view_purchase_request') ?>" ><i class="fa fa-eye"></i></a>
                                            <a class="btn btn-warning ml-2"><i class="fa fa-print"></i></a>
                                            <a class="btn btn-warning ml-2">Planning <i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- START MODAL TAMBAH PO -->

                            <div class="modal fade" id="modal_tambah_po" data-backdrop="static">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <form action="" method="post" enctype="multipart/form-data">
                                            <div class="modal-header">
                                                <div class="modal-title">Tambah Purchase Request</div>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Nomor PO</label>
                                                            <input type="text" class="form-control" name="nomor_po" id="nomor_po">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Tanggal PO</label>
                                                            <input type="date" class="form-control" name="tanggal_upload_po" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Project</label>
                                                            <select name="detail_nama_project" id="detail_nama_project" class="form-control">
                                                                <option value="">Pilih Salah Satu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="col-form-label">Lokasi Project</label>
                                                            <input type="text" class="form-control" name="lokasi_project" id="lokasi_project">
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
                                                    <div class="col-md-12">
                                                        <table class="table table-bordered" id="table_item_stok">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 5%;">No</th>
                                                                    <th>Nama</th>
                                                                    <th>Satuan</th>
                                                                    <th>Boq</th>
                                                                    <th>Stock Area</th>
                                                                    <th>PO</th>
                                                                    <th>Keterangan</th>
                                                                    <th>Hapus</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>Tiang 7 meter</td>
                                                                    <td>Batang</td>
                                                                    <td>3000</td>
                                                                    <td>1500</td>
                                                                    <td>5000</td>
                                                                    <td>Untuk Project IFORTE</td>
                                                                    <td>
                                                                        <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>2</td>
                                                                    <td>Stopping</td>
                                                                    <td>PCS</td>
                                                                    <td>3000</td>
                                                                    <td>9690</td>
                                                                    <td>5000</td>
                                                                    <td> </td>
                                                                    <td>
                                                                        <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="5"><b>TOTAL</b></td>
                                                                    <td><b>10000</b></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary">Tambah PO</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- END MODAL TAMBAH PO -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>