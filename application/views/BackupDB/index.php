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
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars((string) $this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('import_vps_output')): ?>
                <div class="card border-info">
                    <div class="card-header bg-info">
                        <h3 class="card-title">Output Import VPS Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0" style="white-space: pre-wrap; max-height: 360px; overflow: auto;"><?= htmlspecialchars((string) $this->session->flashdata('import_vps_output'), ENT_QUOTES, 'UTF-8') ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12">
                    <?php if (!empty($canImportVps)): ?>
                        <div class="card border-danger">
                            <div class="card-header bg-danger">
                                <h3 class="card-title">Import Database VPS ke Lokal</h3>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    Tombol ini akan mengambil dump terbaru dari VPS, membuat backup database lokal, lalu mengganti isi database lokal dengan data VPS.
                                </p>
                                <p class="text-danger text-bold mb-3">
                                    Gunakan hanya di XAMPP lokal. Proses bisa berjalan beberapa menit.
                                </p>
                                <form method="post" action="<?= base_url('Backup/import_vps_to_local') ?>" id="form-import-vps">
                                    <button type="submit" class="btn btn-danger" <?= empty($vpsImportAvailable) ? 'disabled' : '' ?>>
                                        Import VPS ke Lokal &nbsp;<i class="fas fa-cloud-download-alt"></i>
                                    </button>
                                    <?php if (empty($vpsImportAvailable)): ?>
                                        <small class="text-muted ml-2">Script import atau SSH key belum tersedia.</small>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <h3 class="card-title">Import Backup SQL ke Lokal</h3>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    Pakai ini kalau laptop lokal tidak punya SSH key VPS. Upload file <code>.sql</code>, sistem akan backup database lokal dulu, lalu restore dari file tersebut.
                                </p>
                                <form method="post" action="<?= base_url('Backup/upload_local_backup_import') ?>" enctype="multipart/form-data" id="form-import-local-upload">
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="sql_file" name="sql_file" accept=".sql" required <?= empty($localImportAvailable) ? 'disabled' : '' ?>>
                                            <label class="custom-file-label" for="sql_file">Pilih file .sql</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-warning text-bold" <?= empty($localImportAvailable) ? 'disabled' : '' ?>>
                                                Upload & Import &nbsp;<i class="fas fa-file-import"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if (empty($localImportAvailable)): ?>
                                        <small class="text-muted">Script import lokal atau mysql.exe XAMPP belum tersedia.</small>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3 class="card-title">List Data</h3>
                                </div>
                                <div class="col-6">
                                    <a href="<?= base_url('backup/create_backup') ?>"
                                        class="btn btn-primary float-right text-bold btn-tambah-data-item">Backup DB
                                        &nbsp;<i class="fas fa-download"></i> </a>
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
                                                <a href="<?= base_url('Backup/download_backup/' . $backup->filename) ?>"
                                                    class="btn btn-success btn-sm">
                                                    Download &nbsp;<i class="fas fa-download"></i>
                                                </a>
                                                <a href="<?= base_url('Backup/delete_backup/' . $backup->id) ?>"
                                                    class="btn btn-danger btn-sm">
                                                    Delete &nbsp;<i class="fas fa-trash"></i>
                                                </a>
                                                <?php if (!empty($canImportVps)): ?>
                                                    <form method="post" action="<?= base_url('Backup/import_local_backup') ?>" class="d-inline form-import-local-existing">
                                                        <input type="hidden" name="filename" value="<?= htmlspecialchars((string) $backup->filename, ENT_QUOTES, 'UTF-8') ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" <?= empty($localImportAvailable) ? 'disabled' : '' ?>>
                                                            Import &nbsp;<i class="fas fa-file-import"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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

        $('#form-import-vps').on('submit', function(e) {
            var ok = window.confirm('Import VPS ke lokal sekarang? Database lokal akan dibackup lalu diganti dengan data VPS terbaru.');
            if (!ok) {
                e.preventDefault();
                return;
            }

            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('Sedang import... &nbsp;<i class="fas fa-spinner fa-spin"></i>');
        });

        $('#sql_file').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Pilih file .sql');
        });

        $('#form-import-local-upload').on('submit', function(e) {
            var ok = window.confirm('Import file SQL ini ke database lokal sekarang? Database lokal akan dibackup lalu diganti dengan isi file.');
            if (!ok) {
                e.preventDefault();
                return;
            }

            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('Sedang import... &nbsp;<i class="fas fa-spinner fa-spin"></i>');
        });

        $('.form-import-local-existing').on('submit', function(e) {
            var ok = window.confirm('Import backup ini ke database lokal sekarang? Database lokal akan dibackup lalu diganti dengan isi file.');
            if (!ok) {
                e.preventDefault();
                return;
            }

            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('Importing... &nbsp;<i class="fas fa-spinner fa-spin"></i>');
        });
    })


</script>
