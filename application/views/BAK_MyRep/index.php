<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$statusOptions = ['DRAFT', 'ON REVIEW', 'REJECTED', 'DONE'];
$today = date('Y-m-d');
$summaryTotal = count($clusterRows);
$summaryBaOpen = 0;
$summaryDone = 0;
$summaryRejected = 0;

foreach ($clusterRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
    $bakStatus = strtoupper(trim((string) ($row['status_bak'] ?? 'DRAFT')));

    if ($currentStatus === 'BA OPEN') {
        $summaryBaOpen++;
    }

    if ($bakStatus === 'DONE' || $currentStatus === 'BAK') {
        $summaryDone++;
    }

    if ($bakStatus === 'REJECTED' || $currentStatus === 'REJECTED') {
        $summaryRejected++;
    }
}

if (!function_exists('bakBadgeClass')) {
    function bakBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'DONE':
            case 'APPROVED':
            case 'BAK':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'BA OPEN':
                return 'info';
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('bakDocLabel')) {
    function bakDocLabel($row)
    {
        if ((int) ($row['bak_doc_not_required'] ?? 0) === 1) {
            return 'Tidak Dibutuhkan';
        }

        $status = strtoupper(trim((string) ($row['bak_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($row['bak_doc_file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">BAK MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">
                    Tabel flow baru MyRep belum tersedia. Jalankan query database `tb_myrep_*` terlebih dahulu sebelum memakai modul BAK.
                </div>
            <?php endif; ?>

            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">
                    Tabel dokumen flow BAK belum tersedia. Form BAK tetap bisa dipakai, tetapi upload `BA OPEN` belum aktif.
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show js-bak-flash-alert" role="alert" data-flash-key="<?= htmlspecialchars('bak_flash_success_' . md5((string) $flashSuccess), ENT_QUOTES) ?>">
                    <?= $flashSuccess ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show js-bak-flash-alert" role="alert" data-flash-key="<?= htmlspecialchars('bak_flash_error_' . md5((string) $flashError), ENT_QUOTES) ?>">
                    <?= $flashError ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary shadow-sm bak-filter-card">
                        <div class="card-header">
                            <h3 class="card-title">Filter Data BAK</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('BAK_MyRep') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Kota</label>
                                            <select name="city" class="form-control">
                                                <option value="">Semua Kota</option>
                                                <?php foreach ($cityOptions as $cityOption): ?>
                                                    <option value="<?= htmlspecialchars($cityOption) ?>" <?= $selectedCity === strtoupper($cityOption) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cityOption) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <option value="">Semua Status</option>
                                                <option value="BA OPEN" <?= $selectedStatus === 'BA OPEN' ? 'selected' : '' ?>>BA OPEN</option>
                                                <?php foreach ($statusOptions as $statusOption): ?>
                                                    <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>>
                                                        <?= $statusOption ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group mb-0 w-100 d-flex justify-content-between">
                                            <a href="<?= base_url('BAK_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                            <?php if ($isReady): ?>
                                                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryTotal, 0, ',', '.') ?></h3>
                            <p>Total Cluster BAK</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryBaOpen, 0, ',', '.') ?></h3>
                            <p>Stage BA OPEN</p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryDone, 0, ',', '.') ?></h3>
                            <p>Done BAK</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryRejected, 0, ',', '.') ?></h3>
                            <p>Rejected</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm bak-table-card">
                        <div class="card-header">
                            <h3 class="card-title">Monitoring BAK Cluster</h3>
                            <div class="card-tools">
                                <?php if ($isReady): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-bak-create">
                                        <i class="fas fa-plus"></i> Input BAK
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_bak_myrep" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Cluster</th>
                                            <th>Regional</th>
                                            <th>Kota</th>
                                            <th>Periode Target</th>
                                            <th>HP BAK</th>
                                            <th>Tanggal BA OPEN</th>
                                            <th>Tanggal BAK</th>
                                            <th>Status BAK</th>
                                            <th>Dokumen BA OPEN</th>
                                            <th>Review Dokumen</th>
                                            <th>Status Flow</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clusterRows as $index => $row): ?>
                                            <?php
                                            $targetLabel = !empty($row['year_num']) && !empty($row['month_num']) ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num']) : '-';
                                            $bakDocStatusRaw = strtoupper(trim((string) ($row['bak_doc_status'] ?? '')));
                                            $showUploadButton = $docReady && in_array($bakDocStatusRaw, ['', 'REJECTED'], true);
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                                    <?php if (!empty($row['cluster_code'])): ?>
                                                        <div class="text-muted small"><?= htmlspecialchars((string) $row['cluster_code']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                                <td><?= $targetLabel ?></td>
                                                <td class="text-right"><?= number_format((float) ($row['homepass_bak'] ?? 0), 0, ',', '.') ?></td>
                                                <td><?= !empty($row['ba_open_date']) ? htmlspecialchars((string) $row['ba_open_date']) : '-' ?></td>
                                                <td><?= !empty($row['bak_date']) ? htmlspecialchars((string) $row['bak_date']) : '-' ?></td>
                                                <td><span class="badge badge-<?= bakBadgeClass($row['status_bak'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_bak'] ?? 'DRAFT')) ?></span></td>
                                                <td>
                                                    <span class="badge badge-<?= bakBadgeClass(bakDocLabel($row)) ?>"><?= htmlspecialchars(bakDocLabel($row)) ?></span>
                                                    <?php if (!empty($row['bak_doc_file_name'])): ?>
                                                        <div class="small text-muted mt-1"><?= htmlspecialchars((string) $row['bak_doc_file_name']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['bak_doc_reviewed_at'])): ?>
                                                        <div class="small text-muted">Reviewed</div>
                                                        <div><?= htmlspecialchars((string) $row['bak_doc_reviewed_at']) ?></div>
                                                    <?php elseif (!empty($row['bak_doc_file_id'])): ?>
                                                        <span class="text-warning small font-weight-bold">Waiting Review</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada review</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge badge-<?= bakBadgeClass($row['status_current'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'DRAFT')) ?></span></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-edit-bak"
                                                        data-toggle="modal"
                                                        data-target="#modal-bak-edit"
                                                        data-id_myrep_cluster="<?= (int) $row['id_myrep_cluster'] ?>"
                                                        data-id_target="<?= (int) ($row['id_target'] ?? 0) ?>"
                                                        data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                        data-cluster_code="<?= htmlspecialchars((string) ($row['cluster_code'] ?? ''), ENT_QUOTES) ?>"
                                                        data-homepass_bak="<?= (int) ($row['homepass_bak'] ?? 0) ?>"
                                                        data-ba_open_date="<?= htmlspecialchars((string) ($row['ba_open_date'] ?? ''), ENT_QUOTES) ?>"
                                                        data-bak_date="<?= htmlspecialchars((string) ($row['bak_date'] ?? ''), ENT_QUOTES) ?>"
                                                        data-status_bak="<?= htmlspecialchars((string) ($row['status_bak'] ?? 'DRAFT'), ENT_QUOTES) ?>"
                                                        data-remark_bak="<?= htmlspecialchars((string) ($row['remark_bak'] ?? ''), ENT_QUOTES) ?>">
                                                        Edit
                                                    </button>
                                                    <?php if ($docReady): ?>
                                                        <?php if ($showUploadButton): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-info js-upload-doc mt-1"
                                                                data-toggle="modal"
                                                                data-target="#modal-bak-upload-doc"
                                                                data-cluster_id="<?= (int) $row['id_myrep_cluster'] ?>"
                                                                data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                                data-id_doc_file="<?= (int) ($row['bak_doc_file_id'] ?? 0) ?>"
                                                                data-doc_status="<?= htmlspecialchars((string) bakDocLabel($row), ENT_QUOTES) ?>"
                                                                data-doc_remark="<?= htmlspecialchars((string) ($row['bak_doc_remark'] ?? ''), ENT_QUOTES) ?>">
                                                                <?= $bakDocStatusRaw === 'REJECTED' ? 'Re-Upload Doc' : 'Upload Doc' ?>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (!empty($row['bak_doc_file_id']) && !empty($row['bak_doc_file_path'])): ?>
                                                            <a href="<?= base_url('BAK_MyRep/previewDocument/' . (int) $row['bak_doc_file_id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                                                Preview
                                                            </a>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-dark js-history-doc mt-1"
                                                                data-toggle="modal"
                                                                data-target="#modal-bak-history-doc"
                                                                data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                                data-doc_name="BA OPEN"
                                                                data-history='<?= htmlspecialchars(json_encode($this->MBAK_MyRep->getBakFileLogs((int) $row['bak_doc_file_id'])), ENT_QUOTES) ?>'>
                                                                History
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($canApprove && !empty($row['bak_doc_file_id']) && in_array(strtoupper((string) ($row['bak_doc_status'] ?? '')), ['UPLOADED', 'REJECTED'], true)): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-success js-approve-doc mt-1"
                                                                data-toggle="modal"
                                                                data-target="#modal-bak-approve-doc"
                                                                data-id_doc_file="<?= (int) $row['bak_doc_file_id'] ?>"
                                                                data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>">
                                                                Approve
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-danger js-reject-doc mt-1"
                                                                data-toggle="modal"
                                                                data-target="#modal-bak-reject-doc"
                                                                data-id_doc_file="<?= (int) $row['bak_doc_file_id'] ?>"
                                                                data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>">
                                                                Reject
                                                            </button>
                                                        <?php elseif ($canApprove && !empty($row['bak_doc_file_id']) && strtoupper((string) ($row['bak_doc_status'] ?? '')) === 'APPROVED'): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-danger js-reject-doc mt-1"
                                                                data-toggle="modal"
                                                                data-target="#modal-bak-reject-doc"
                                                                data-id_doc_file="<?= (int) $row['bak_doc_file_id'] ?>"
                                                                data-cluster_name="<?= htmlspecialchars((string) ($row['cluster_name'] ?? ''), ENT_QUOTES) ?>">
                                                                Reject
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($isReady): ?>
    <div class="modal fade" id="modal-bak-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('BAK_MyRep/saveCluster') ?>" enctype="multipart/form-data">
                    <div class="modal-header bak-modal-header">
                        <h5 class="modal-title">Input Cluster BAK Baru</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select name="id_target" class="form-control js-bak-target-selector js-bak-city-select" required>
                                        <option value="">Pilih target kota</option>
                                        <?php foreach ($createTargetOptions as $targetOption): ?>
                                            <option value="<?= (int) $targetOption['id_target'] ?>" data-regional_name="<?= htmlspecialchars((string) ($targetOption['regional_name'] ?? ''), ENT_QUOTES) ?>" data-province_name="<?= htmlspecialchars((string) ($targetOption['province_name'] ?? ''), ENT_QUOTES) ?>" data-city_name="<?= htmlspecialchars((string) ($targetOption['city_name'] ?? ''), ENT_QUOTES) ?>">
                                                <?= htmlspecialchars((string) ($targetOption['city_name'] ?? '-')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-target-regional" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control js-target-province" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" class="form-control js-target-city" readonly></div></div>
                            <div class="col-md-8"><div class="form-group"><label>Nama Cluster</label><input type="text" name="cluster_name" class="form-control" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kode Cluster</label><input type="text" name="cluster_code" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass BAK</label><input type="number" name="homepass_bak" min="1" class="form-control" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BA OPEN</label><input type="date" name="ba_open_date" class="form-control" value="<?= $today ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BAK</label><input type="date" name="bak_date" class="form-control" value="<?= $today ?>"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Status BAK</label><input type="text" class="form-control" value="ON REVIEW" readonly></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Remark</label><textarea name="remark_bak" rows="3" class="form-control"></textarea></div></div>
                            <?php if ($docReady): ?>
                                <div class="col-md-12">
                                    <div class="doc-modal-panel">
                                        <div class="doc-modal-title">Dokumen BA OPEN</div>
                                        <p class="doc-modal-subtitle">Upload dokumen BA OPEN saat create cluster. Setelah disimpan, status BAK tetap `ON REVIEW` sampai dokumen di-approve HO.</p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="doc-modal-panel">
                                        <div class="form-group form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="create_doc_not_required" name="create_is_document_not_required" value="1">
                                            <label class="form-check-label" for="create_doc_not_required">Dokumen kosong / tidak dibutuhkan</label>
                                        </div>
                                        <label class="font-weight-bold d-block">File BA OPEN</label>
                                        <div class="upload-dropzone" id="bak-create-dropzone">
                                            <input type="file" name="create_file" id="bak-create-file-input">
                                            <div class="upload-dropzone-content">
                                                <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                                <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                                <div class="upload-dropzone-file" id="bak-create-file-name">Belum ada file dipilih</div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">Format: pdf, doc, docx, xls, xlsx, jpg, jpeg, png. Maksimal 30 MB.</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="doc-modal-panel">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Remark Dokumen</label>
                                            <textarea name="create_doc_remark" rows="3" class="form-control" placeholder="Catatan upload jika diperlukan"></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan BAK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-bak-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('BAK_MyRep/updateCluster') ?>">
                    <input type="hidden" name="id_myrep_cluster" id="edit_id_myrep_cluster">
                    <div class="modal-header bak-modal-header">
                        <h5 class="modal-title">Edit Data BAK</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select name="id_target" id="edit_id_target" class="form-control js-bak-target-selector js-bak-edit-city-select" required>
                                        <option value="">Pilih target kota</option>
                                        <?php foreach ($targetOptions as $targetOption): ?>
                                            <option value="<?= (int) $targetOption['id_target'] ?>" data-regional_name="<?= htmlspecialchars((string) ($targetOption['regional_name'] ?? ''), ENT_QUOTES) ?>" data-province_name="<?= htmlspecialchars((string) ($targetOption['province_name'] ?? ''), ENT_QUOTES) ?>" data-city_name="<?= htmlspecialchars((string) ($targetOption['city_name'] ?? ''), ENT_QUOTES) ?>">
                                                <?= htmlspecialchars((string) ($targetOption['city_name'] ?? '-')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-target-regional" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control js-target-province" readonly></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" class="form-control js-target-city" readonly></div></div>
                            <div class="col-md-8"><div class="form-group"><label>Nama Cluster</label><input type="text" name="cluster_name" id="edit_cluster_name" class="form-control" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Kode Cluster</label><input type="text" name="cluster_code" id="edit_cluster_code" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Homepass BAK</label><input type="number" name="homepass_bak" id="edit_homepass_bak" min="1" class="form-control" required></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BA OPEN</label><input type="date" name="ba_open_date" id="edit_ba_open_date" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Tanggal BAK</label><input type="date" name="bak_date" id="edit_bak_date" class="form-control"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>Status BAK</label><input type="text" id="edit_status_bak" class="form-control" readonly></div></div>
                            <div class="col-md-12"><div class="form-group"><label>Remark</label><textarea name="remark_bak" id="edit_remark_bak" rows="3" class="form-control"></textarea></div></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update BAK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($docReady): ?>
        <div class="modal fade doc-modal" id="modal-bak-upload-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('BAK_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="bak-upload-document-form">
                        <input type="hidden" name="cluster_id" id="upload_cluster_id">
                        <div class="modal-header" style="background: linear-gradient(135deg, #198754, #34c38f);">
                            <div>
                                <h4 class="modal-title mb-1">Upload Dokumen BA OPEN</h4>
                                <p class="mb-0" style="opacity:.9;" id="upload_doc_cluster_caption"></p>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="doc-modal-panel">
                                <div class="doc-modal-title">Panduan Upload</div>
                                <p class="doc-modal-subtitle">Gunakan drag-and-drop atau klik area upload. Setelah file masuk, status dokumen akan menjadi `ON REVIEW` sampai HO melakukan approval.</p>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Cluster</label>
                                            <input type="text" id="upload_cluster_name" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">Status Saat Ini</label>
                                            <input type="text" id="upload_doc_status" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-modal-panel">
                                <label class="font-weight-bold d-block">File BA OPEN</label>
                                <div class="upload-dropzone" id="bak-upload-dropzone">
                                    <input type="file" name="file" id="bak-upload-file-input">
                                    <div class="upload-dropzone-content">
                                        <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                        <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                        <div class="upload-dropzone-file" id="bak-upload-file-name">Belum ada file dipilih</div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Format: pdf, doc, docx, xls, xlsx, jpg, jpeg, png. Maksimal 30 MB.</small>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Remark Upload</label>
                                    <textarea name="remark" id="upload_doc_remark" rows="3" class="form-control" placeholder="Catatan upload jika diperlukan"></textarea>
                                </div>
                            </div>
                            <div class="doc-modal-panel">
                                <div class="form-group form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="upload_doc_not_required" name="is_document_not_required" value="1">
                                    <label class="form-check-label" for="upload_doc_not_required">Tandai dokumen tidak dibutuhkan</label>
                                </div>
                            </div>
                            <div class="upload-progress-panel" id="bak-upload-progress-panel">
                                <div class="upload-progress-meta">
                                    <span>Upload Progress</span>
                                    <span id="bak-upload-progress-percent">0%</span>
                                </div>
                                <div class="upload-progress-bar-wrap">
                                    <div class="upload-progress-bar" id="bak-upload-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success" id="bak-upload-document-submit">Upload Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($canApprove): ?>
            <div class="modal fade doc-modal" id="modal-bak-approve-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('BAK_MyRep/approveDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="approve_id_doc_file">
                            <div class="modal-header" style="background: linear-gradient(135deg, #15803d, #16a34a);">
                                <div>
                                    <h4 class="modal-title mb-1">Approve Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="approve_doc_name">BA OPEN</p>
                                </div>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="doc-modal-panel">
                                    <div class="doc-modal-title">Konfirmasi Approval</div>
                                    <p class="doc-modal-subtitle">Remarks bersifat opsional. Bisa diisi jika ingin memberi catatan saat approve.</p>
                                </div>
                                <div class="doc-modal-panel mb-0">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Cluster</label>
                                        <input type="text" id="approve_cluster_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Remarks</label>
                                        <textarea name="remark" rows="3" class="form-control" placeholder="Isi remarks approval jika diperlukan"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success">Approve Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade doc-modal" id="modal-bak-reject-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('BAK_MyRep/rejectDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="reject_id_doc_file">
                            <div class="modal-header" style="background: linear-gradient(135deg, #b91c1c, #dc2626);">
                                <div>
                                    <h4 class="modal-title mb-1">Reject Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="reject_doc_name">BA OPEN</p>
                                </div>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="doc-modal-panel">
                                    <div class="doc-modal-title">Konfirmasi Reject</div>
                                    <p class="doc-modal-subtitle">Isi alasan reject agar area bisa tahu apa yang perlu diperbaiki sebelum upload ulang.</p>
                                </div>
                                <div class="doc-modal-panel mb-0">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Cluster</label>
                                        <input type="text" id="reject_cluster_name" class="form-control" readonly>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Alasan Reject</label>
                                        <textarea name="remark" rows="3" class="form-control" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-danger">Reject Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="modal fade doc-modal" id="modal-bak-history-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                        <div>
                            <h4 class="modal-title mb-1">History Dokumen</h4>
                            <p class="mb-0" style="opacity:.9;" id="history_doc_name">BA OPEN</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="doc-modal-panel">
                            <div class="doc-modal-title mb-1">Cluster</div>
                            <p class="doc-modal-subtitle mb-0" id="history_cluster_name">-</p>
                        </div>
                        <div class="doc-modal-panel mb-0">
                            <ul class="doc-history-list" id="history_doc_list">
                                <li class="text-muted">Belum ada history.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<style>
    .doc-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .doc-modal .modal-header {
        border-bottom: 0;
        padding: 1rem 1.25rem;
        color: #fff;
    }

    .doc-modal .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .doc-modal .modal-footer {
        border-top: 0;
        background: #eef2f7;
    }

    .doc-modal-panel {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .doc-modal-panel:last-child {
        margin-bottom: 0;
    }

    .doc-modal-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: .35rem;
        color: #1f2937;
    }

    .doc-modal-subtitle {
        margin: 0;
        color: #6b7280;
        font-size: .9rem;
    }

    .upload-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1.1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .upload-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
        transform: scale(1.01);
    }

    .upload-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .upload-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .upload-dropzone-icon {
        font-size: 2rem;
        color: #198754;
        margin-bottom: .5rem;
    }

    .upload-dropzone-title {
        font-weight: 700;
        color: #166534;
        margin-bottom: .25rem;
    }

    .upload-dropzone-text {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: .35rem;
    }

    .upload-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .upload-progress-panel {
        display: none;
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 1rem;
    }

    .upload-progress-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .5rem;
        font-weight: 700;
        color: #1e3a8a;
    }

    .upload-progress-bar-wrap {
        width: 100%;
        height: 12px;
        background: #e9eef5;
        border-radius: 999px;
        overflow: hidden;
    }

    .upload-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        width: 0%;
    }

    .upload-progress-bar.success {
        background: linear-gradient(90deg, #065f46, #10b981);
    }

    .doc-history-list {
        position: relative;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .doc-history-item {
        position: relative;
        padding-left: 1.5rem;
        padding-bottom: 1rem;
        border-left: 2px solid #d8e3ee;
        margin-left: .5rem;
    }

    .doc-history-item:last-child {
        padding-bottom: 0;
    }

    .doc-history-dot {
        position: absolute;
        left: -8px;
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 999px;
        background: #17a2b8;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #d8e3ee;
    }

    .doc-history-title {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .2rem;
    }

    .doc-history-meta {
        color: #6b7280;
        font-size: .86rem;
        margin-bottom: .25rem;
    }

    .doc-history-note {
        color: #374151;
        font-size: .9rem;
        margin-bottom: 0;
    }

    .bak-filter-card .card-header,
    .bak-table-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .bak-modal-header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    .bak-table-card .table thead th {
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
        white-space: nowrap;
    }

    .small-box.shadow-sm {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        border-radius: 14px;
    }

    .badge {
        font-size: 11px;
        padding: 6px 10px;
        border-radius: 999px;
    }

    .select2-container--open {
        z-index: 1065;
    }
</style>

<script>
    (function () {
        function initBakCitySelect(modalSelector, selectSelector) {
            var $modal = $(modalSelector);
            var $select = $modal.find(selectSelector);

            if (!$select.length || !$.fn.select2) {
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                width: '100%',
                placeholder: 'Pilih kota',
                allowClear: true,
                dropdownParent: $modal
            });
        }

        function handleBakFlashAlerts() {
            $('.js-bak-flash-alert').each(function () {
                var $alert = $(this);
                var flashKey = $alert.data('flash-key');

                if (!flashKey || !window.sessionStorage) {
                    return;
                }

                if (window.sessionStorage.getItem(flashKey) === '1') {
                    $alert.remove();
                    return;
                }

                window.sessionStorage.setItem(flashKey, '1');
            });

            window.setTimeout(function () {
                $('.js-bak-flash-alert').alert('close');
            }, 4000);
        }

        function bindDropzone(dropzoneSelector, inputSelector, labelSelector) {
            var dropzone = document.querySelector(dropzoneSelector);
            var input = document.querySelector(inputSelector);
            var label = document.querySelector(labelSelector);

            if (!dropzone || !input || !label) {
                return;
            }

            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    label.textContent = e.dataTransfer.files[0].name;
                }
            });

            input.addEventListener('change', function () {
                label.textContent = (input.files && input.files.length > 0)
                    ? input.files[0].name
                    : 'Belum ada file dipilih';
            });
        }

        function syncTargetMeta($container) {
            var $select = $container.find('.js-bak-target-selector').first();
            var $selected = $select.find('option:selected');
            $container.find('.js-target-regional').val($selected.data('regional_name') || '');
            $container.find('.js-target-province').val($selected.data('province_name') || '');
            $container.find('.js-target-city').val($selected.data('city_name') || '');
        }

        $(function () {
            handleBakFlashAlerts();

            if ($.fn.DataTable) {
                $('#table_bak_myrep').DataTable({
                    responsive: true,
                    autoWidth: false,
                    order: [[0, 'asc']]
                });
            }

            initBakCitySelect('#modal-bak-create', '.js-bak-city-select');
            initBakCitySelect('#modal-bak-edit', '.js-bak-edit-city-select');

            $(document).on('change', '.js-bak-target-selector', function () {
                syncTargetMeta($(this).closest('.modal-body, .modal-content'));
            });

            $('#modal-bak-create').on('shown.bs.modal', function () {
                initBakCitySelect('#modal-bak-create', '.js-bak-city-select');
                syncTargetMeta($(this));
                $('#bak-create-file-input').val('');
                $('#bak-create-file-name').text('Belum ada file dipilih');
                $('#create_doc_not_required').prop('checked', false);
                $('#bak-create-file-input').prop('disabled', false).prop('required', true);

                window.setTimeout(function () {
                    $('#modal-bak-create').find('.js-bak-city-select').select2('open');
                }, 120);
            }).on('hidden.bs.modal', function () {
                var $select = $(this).find('.js-bak-city-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('close');
                }
            });

            $('#modal-bak-edit').on('shown.bs.modal', function () {
                initBakCitySelect('#modal-bak-edit', '.js-bak-edit-city-select');
            }).on('hidden.bs.modal', function () {
                var $select = $(this).find('.js-bak-edit-city-select');
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('close');
                }
            });

            $(document).on('click', '.js-edit-bak', function () {
                var $button = $(this);
                var $modal = $('#modal-bak-edit');

                $modal.find('#edit_id_myrep_cluster').val($button.data('id_myrep_cluster'));
                $modal.find('#edit_id_target').val($button.data('id_target')).trigger('change.select2');
                $modal.find('#edit_cluster_name').val($button.data('cluster_name'));
                $modal.find('#edit_cluster_code').val($button.data('cluster_code'));
                $modal.find('#edit_homepass_bak').val($button.data('homepass_bak'));
                $modal.find('#edit_ba_open_date').val($button.data('ba_open_date'));
                $modal.find('#edit_bak_date').val($button.data('bak_date'));
                $modal.find('#edit_status_bak').val($button.data('status_bak'));
                $modal.find('#edit_remark_bak').val($button.data('remark_bak'));

                syncTargetMeta($modal);
            });

            $(document).on('click', '.js-upload-doc', function () {
                var $button = $(this);
                $('#upload_cluster_id').val($button.data('cluster_id'));
                $('#upload_cluster_name').val($button.data('cluster_name'));
                $('#upload_doc_cluster_caption').text($button.data('cluster_name'));
                $('#upload_doc_status').val($button.data('doc_status'));
                $('#upload_doc_remark').val($button.data('doc_remark'));
                $('#upload_doc_not_required').prop('checked', false);
                $('#bak-upload-file-input').val('').prop('disabled', false).prop('required', true);
                $('#bak-upload-file-name').text('Belum ada file dipilih');
                $('#bak-upload-progress-panel').hide();
                $('#bak-upload-progress-bar').removeClass('success').css('width', '0%');
                $('#bak-upload-progress-percent').text('0%');
                $('#bak-upload-document-submit').prop('disabled', false).text('Upload Dokumen');
            });

            $(document).on('click', '.js-approve-doc', function () {
                var $button = $(this);
                $('#approve_id_doc_file').val($button.data('id_doc_file'));
                $('#approve_cluster_name').val($button.data('cluster_name'));
                $('#approve_doc_name').text('BA OPEN');
            });

            $(document).on('click', '.js-reject-doc', function () {
                var $button = $(this);
                $('#reject_id_doc_file').val($button.data('id_doc_file'));
                $('#reject_cluster_name').val($button.data('cluster_name'));
                $('#reject_doc_name').text('BA OPEN');
            });

            $(document).on('click', '.js-history-doc', function () {
                var $button = $(this);
                var rawHistory = $button.attr('data-history');
                var history = [];

                try {
                    history = rawHistory ? JSON.parse(rawHistory) : [];
                } catch (e) {
                    history = [];
                }

                $('#history_doc_name').text($button.data('doc_name'));
                $('#history_cluster_name').text($button.data('cluster_name'));

                if (!history.length) {
                    $('#history_doc_list').html('<li class="text-muted">Belum ada history.</li>');
                    return;
                }

                var html = '';
                history.forEach(function (entry) {
                    var actionLabel = entry.action_type || '-';
                    if (actionLabel === 'UPLOAD') actionLabel = 'Uploaded';
                    if (actionLabel === 'REUPLOAD') actionLabel = 'Re-uploaded';
                    if (actionLabel === 'APPROVE') actionLabel = 'Approved';
                    if (actionLabel === 'REJECT') actionLabel = 'Rejected';

                    html += '<li class="doc-history-item">' +
                        '<span class="doc-history-dot"></span>' +
                        '<div class="doc-history-title">' + actionLabel + '</div>' +
                        '<div class="doc-history-meta">' + (entry.action_at || '-') + ' | ' + (entry.nama_user || 'System') + '</div>' +
                        '<p class="doc-history-note"><strong>File:</strong> ' + (entry.file_name || '-') + '</p>' +
                        '<p class="doc-history-note"><strong>Remark:</strong> ' + (entry.remark || '-') + '</p>' +
                        '</li>';
                });

                $('#history_doc_list').html(html);
            });

            $(document).on('change', '#upload_doc_not_required', function () {
                var checked = $(this).is(':checked');
                $('#bak-upload-file-input').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#bak-upload-file-input').val('');
                    $('#bak-upload-file-name').text('File tidak diperlukan untuk item ini');
                } else {
                    $('#bak-upload-file-name').text('Belum ada file dipilih');
                }
            });

            $(document).on('change', '#create_doc_not_required', function () {
                var checked = $(this).is(':checked');
                $('#bak-create-file-input').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#bak-create-file-input').val('');
                    $('#bak-create-file-name').text('File tidak diperlukan untuk item ini');
                } else {
                    $('#bak-create-file-name').text('Belum ada file dipilih');
                }
            });

            $('#modal-bak-create form').on('submit', function (e) {
                var isDocNotRequired = $('#create_doc_not_required').is(':checked');
                var fileInput = $('#bak-create-file-input').get(0);
                var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                if (!isDocNotRequired && !hasFile) {
                    e.preventDefault();
                    alert('File BA OPEN tidak boleh kosong jika checkbox dokumen kosong tidak dicentang.');
                }
            });

            $('#bak-upload-document-form').on('submit', function (e) {
                e.preventDefault();

                var form = this;
                var submitButton = $('#bak-upload-document-submit');
                var progressPanel = $('#bak-upload-progress-panel');
                var progressBar = $('#bak-upload-progress-bar');
                var progressPercent = $('#bak-upload-progress-percent');
                var formData = new FormData(form);

                submitButton.prop('disabled', true).text('Uploading...');
                progressPanel.show();
                progressBar.removeClass('success').css('width', '0%');
                progressPercent.text('0%');

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (evt) {
                                if (evt.lengthComputable) {
                                    var percent = Math.round((evt.loaded / evt.total) * 100);
                                    progressBar.css('width', percent + '%');
                                    progressPercent.text(percent + '%');
                                }
                            }, false);
                        }
                        return xhr;
                    },
                    success: function (response) {
                        progressBar.addClass('success').css('width', '100%');
                        progressPercent.text('100%');

                        if (response && response.status) {
                            window.location.href = response.redirect_url || window.location.href;
                            return;
                        }

                        alert(response && response.message ? response.message : 'Upload gagal.');
                        submitButton.prop('disabled', false).text('Upload Dokumen');
                    },
                    error: function () {
                        alert('Upload gagal. Silakan coba lagi.');
                        submitButton.prop('disabled', false).text('Upload Dokumen');
                    }
                });
            });

            bindDropzone('#bak-create-dropzone', '#bak-create-file-input', '#bak-create-file-name');
            bindDropzone('#bak-upload-dropzone', '#bak-upload-file-input', '#bak-upload-file-name');
        });
    })();
</script>
