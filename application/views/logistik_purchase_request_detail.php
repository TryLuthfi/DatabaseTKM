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
                            <h3 class="card-title"><?= $type == 'edit' ? 'Edit' : '' ?> Purchase Request Detail</h3>
                        </div>
                        <div class="card-body table-scrollable">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Nomor PO</label>
                                        <input type="text" class="form-control" name="nomor_po" id="nomor_po" value="TEC.002/TKM-SK/PO/I/2025" <?= $type == 'view' ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Tanggal PO</label>
                                        <input type="date" class="form-control" name="tanggal_upload_po" autocomplete="off" value="<?php echo (new \DateTime())->format('Y-m-d'); ?>" <?= $type == 'view' ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Project</label>
                                        <select name="detail_nama_project" id="detail_nama_project" class="form-control" <?= $type == 'view' ? 'readonly' : '' ?>>
                                            <option value="">Pilih Salah Satu</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Lokasi Project</label>
                                        <input type="text" class="form-control" name="lokasi_project" id="lokasi_project" <?= $type == 'view' ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Nama Material</label>
                                        <select name="nama_material" id="nama_material" class="form-control" <?= $type == 'view' ? 'readonly' : '' ?>>
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
                                                    <button class="btn btn-danger <?= $type == 'view' ? 'disabled' : '' ?>"><i class="fa fa-trash"></i></button>
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
                                                    <button class="btn btn-danger <?= $type == 'view' ? 'disabled' : '' ?>"><i class="fa fa-trash"></i></button>
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
                        <div class="card-footer text-body-secondary">
                            <a href="#" class="btn btn-primary"><i class="fa fa-print"></i> Print</a>
                            <a <?= $type == 'view' ? 'hidden' : '' ?> href="#" class="btn btn-success"><i class="fa fa-save"></i> Upload Document</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>