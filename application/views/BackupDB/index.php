<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark" style="text-align: center;"><?= "" . $judul ?></h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">List Data</h3>
                                </div>
                                <div class="col-6">
                                        <a href="<?= base_url('backup/create_backup') ?>" class="btn btn-primary float-right text-bold btn-tambah-data-item">Backup DB &nbsp;<i
                                                class="fas fa-download"></i> </a>
                                    </div>
                            </div>
                        </div>
                        <div class="card-body table-scrollable">
                            <table id="tabel_histori_backup" class="table table-bordered table-striped">
                            <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>File name</th>
                                        <th>Tanggal Backup</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <?php if (!empty($backups)): ?>
                                    <?php $no = 1;
                                    foreach ($backups as $backup): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= $backup->filename ?></td>
                                            <td><?= $backup->backup_date ?></td>
                                            <td>
                                                <a href="<?= base_url('backup/download_backup/' . $backup->filename) ?>"
                                                    class="btn btn-success btn-sm">
                                                    Download &nbsp;<i
                                                    class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada backup</td>
                                    </tr>
                                <?php endif; ?>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th colspan="2"></th>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function () {

        $("#tabel_histori_backup").DataTable({
            "responsive": true,
        });
    })


</script>