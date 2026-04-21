<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$statusOptions = [
    'DRAFT' => 'DRAFT',
    'WAITING HO' => 'WAITING HO',
    'WAITING MYREP' => 'WAITING EMR',
    'WAITING FINANCE' => 'WAITING FINANCE',
    'RELEASED' => 'RELEASED',
    'DONE BATCH APPROVAL' => 'DONE BATCH APPROVAL',
    'REJECTED' => 'REJECTED',
];
$summaryTotal = count($clusterRows);
$summaryWaiting = 0;
$summaryDone = 0;
$summaryRejected = 0;

foreach ($clusterRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'DRAFT')));
    $batchStatus = strtoupper(trim((string) ($row['staging_status'] ?? 'DRAFT')));

    if ($batchStatus === 'WAITING HO' || $currentStatus === 'WAITING HO') {
        $summaryWaiting++;
    }

    if ($batchStatus === 'DONE BATCH APPROVAL' || $currentStatus === 'DONE BATCH APPROVAL') {
        $summaryDone++;
    }

    if ($batchStatus === 'REJECTED' || $currentStatus === 'REJECTED') {
        $summaryRejected++;
    }
}

if (!function_exists('batchBadgeClass')) {
    function batchBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'APPROVED':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'WAITING HO':
            case 'WAITING MYREP':
                return 'info';
            case 'WAITING FINANCE':
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('batchDocLabel')) {
    function batchDocLabel($row)
    {
        if ((int) ($row['batch_doc_not_required'] ?? 0) === 1) {
            return 'TIDAK BUTUH DOKUMENT';
        }

        $status = strtoupper(trim((string) ($row['batch_doc_status'] ?? '')));
        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        if ($status !== '') {
            return $status;
        }

        return !empty($row['batch_doc_file_name']) ? 'UPLOADED' : 'BELUM UPLOAD';
    }
}

if (!function_exists('batchStatusLabel')) {
    function batchStatusLabel($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'WAITING MYREP') {
            return 'WAITING EMR';
        }

        return $status !== '' ? $status : 'DRAFT';
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Batch Approval MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">
                    Tabel flow baru MyRep belum tersedia. Jalankan query database `tb_myrep_*` terlebih dahulu sebelum memakai modul Batch Approval.
                </div>
            <?php endif; ?>

            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">
                    Tabel dokumen flow Batch Approval belum tersedia. Form Batch Approval tetap bisa dipakai, tetapi upload `RAR` belum aktif.
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary shadow-sm valsal-filter-card">
                        <div class="card-header">
                            <h3 class="card-title">Filter Data Batch Approval</h3>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('Batch_Approval_MyRep') ?>">
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
                                                <?php foreach ($statusOptions as $statusValue => $statusLabel): ?>
                                                    <option value="<?= $statusValue ?>" <?= $selectedStatus === $statusValue ? 'selected' : '' ?>>
                                                        <?= $statusLabel ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group mb-0 w-100 d-flex justify-content-between">
                                            <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
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
                            <p>Total Batch Approval</p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryWaiting, 0, ',', '.') ?></h3>
                            <p>Waiting HO</p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($summaryDone, 0, ',', '.') ?></h3>
                            <p>Done Batch</p>
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
                    <div class="card card-outline card-primary shadow-sm valsal-table-card">
                        <div class="card-header">
                            <h3 class="card-title">Monitoring Batch Approval</h3>
                            <div class="card-tools">
                                <?php if ($isReady): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-batch-create">
                                        <i class="fas fa-plus"></i> Input Batch Approval
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_batch_myrep" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Cluster</th>
                                            <th>Regional</th>
                                            <th>Kota</th>
                                            <th>Periode Target</th>
                                            <th>HP Donasi</th>
                                            <th>Nominal Donasi</th>
                                            <th>Nominal / Homepass</th>
                                            <th>Nominal Approval EMR</th>
                                            <th>Nominal Release</th>
                                            <th>Staging</th>
                                            <th>Dokumen RAR</th>
                                            <th>Review Dokumen</th>
                                            <th>Status Flow</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clusterRows as $index => $row): ?>
                                            <?php $targetLabel = !empty($row['year_num']) && !empty($row['month_num']) ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num']) : '-'; ?>
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
                                                <td class="text-right"><?= number_format((float) ($row['hp_donasi'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) ($row['nominal_pengajuan_area'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= !is_null($row['nominal_per_homepass'] ?? null) ? number_format((float) $row['nominal_per_homepass'], 0, ',', '.') : '-' ?></td>
                                                <td class="text-right"><?= !is_null($row['nominal_nego_emr'] ?? null) ? number_format((float) $row['nominal_nego_emr'], 0, ',', '.') : '-' ?></td>
                                                <td class="text-right"><?= !is_null($row['nominal_release_finance'] ?? null) ? number_format((float) $row['nominal_release_finance'], 0, ',', '.') : '-' ?></td>
                                                <td><span class="badge badge-<?= batchBadgeClass($row['staging_status'] ?? 'DRAFT') ?>"><?= htmlspecialchars(batchStatusLabel($row['staging_status'] ?? 'DRAFT')) ?></span></td>
                                                <td>
                                                    <span class="badge badge-<?= batchBadgeClass(batchDocLabel($row)) ?>"><?= htmlspecialchars(batchDocLabel($row)) ?></span>
                                                    <?php if (!empty($row['batch_doc_file_name'])): ?>
                                                        <div class="small text-muted mt-1"><?= htmlspecialchars((string) $row['batch_doc_file_name']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['batch_doc_reviewed_at'])): ?>
                                                        <div class="small text-muted">Reviewed</div>
                                                        <div><?= htmlspecialchars((string) $row['batch_doc_reviewed_at']) ?></div>
                                                    <?php elseif (!empty($row['batch_doc_file_id'])): ?>
                                                        <span class="text-warning small font-weight-bold">Waiting Review</span>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada review</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge badge-<?= batchBadgeClass($row['status_current'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'DRAFT')) ?></span></td>
                                                <td>
                                                    <a href="<?= base_url('Batch_Approval_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-secondary">
                                                        Detail
                                                    </a>
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
    <div class="modal fade" id="modal-batch-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Batch_Approval_MyRep/saveBatchApproval') ?>" enctype="multipart/form-data" id="create-batch-approval-form">
                    <div class="modal-header valsal-modal-header">
                        <h5 class="modal-title">Input Batch Approval</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Pilih Cluster</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label>Cluster VALSAL</label>
                                        <select name="cluster_id" class="form-control js-batch-cluster-selector" required>
                                            <option value=""><?= empty($eligibleClusterOptions) ? 'BELUM ADA CLUSTER YANG DONE VALSAL' : 'Pilih cluster yang sudah VALSAL' ?></option>
                                            <?php foreach ($eligibleClusterOptions as $clusterOption): ?>
                                                <option
                                                    value="<?= (int) $clusterOption['id_myrep_cluster'] ?>"
                                                    data-cluster-name="<?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-regional-name="<?= htmlspecialchars((string) ($clusterOption['regional_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-province-name="<?= htmlspecialchars((string) ($clusterOption['province_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-city-name="<?= htmlspecialchars((string) ($clusterOption['city_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-homepass-valsal="<?= (int) ($clusterOption['homepass_valsal'] ?? 0) ?>"
                                                    data-valsal-date="<?= htmlspecialchars((string) ($clusterOption['valsal_date'] ?? ''), ENT_QUOTES) ?>">
                                                    <?= htmlspecialchars((string) ($clusterOption['cluster_name'] ?? '-')) ?> | <?= htmlspecialchars((string) ($clusterOption['city_name'] ?? '-')) ?> | <?= sprintf('%02d/%04d', (int) ($clusterOption['month_num'] ?? 0), (int) ($clusterOption['year_num'] ?? 0)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-cluster-regional" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" class="form-control js-cluster-province" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" class="form-control js-cluster-city" readonly></div></div>
                                <div class="col-md-8"><div class="form-group mb-md-0"><label>Nama Cluster</label><input type="text" class="form-control js-cluster-name" readonly></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" class="form-control js-valsal-date" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Data Pengajuan</div>
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>HP VALSAL</label><input type="text" class="form-control js-homepass-valsal js-number-format" data-decimals="0" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="text" name="hp_donasi" id="create_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan</label><input type="date" name="submission_date" id="create_submission_date" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Staging</label>
                                        <input type="hidden" name="staging_status" id="create_staging_status" value="WAITING HO">
                                        <input type="text" class="form-control" value="WAITING HO" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6"><div class="form-group"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="create_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="create_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-freewifi-section">
                            <div class="batch-form-section__title">Free Wifi</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0" placeholder="12"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" class="form-control" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Nama Penerima Dana</label><input type="text" name="recipient_name" id="create_recipient_name" class="form-control js-recipient-source" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="create_recipient_phone" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Jabatan Penerima</label><input type="text" name="recipient_position" id="create_recipient_position" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="create_recipient_period" class="form-control js-recipient-source" placeholder="2023 - 2026"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-emr-fields" data-stage-scope="create" style="display:none;">
                            <div class="batch-form-section__title">Approval EMR</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Approval EMR</label><input type="text" name="nominal_nego_emr" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-finance-fields" data-stage-scope="create" style="display:none;">
                            <div class="batch-form-section__title">Release Finance</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__head">
                                <div>
                                    <div class="batch-form-section__title mb-1">PIC Approval</div>
                                    <p class="batch-form-section__subtitle mb-0">PIC 1 mengikuti data penerima dana. Tambahkan baris jika ada PIC lanjutan.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm js-add-pic-row" data-target="#create_pic_rows" data-prefix="create">Tambah PIC</button>
                            </div>
                            <div class="batch-pic-list" id="create_pic_rows"></div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Upload RAR</div>
                            <div class="alert alert-light border mb-3" style="border-radius: 14px; border-color: #dbeafe !important; background: linear-gradient(135deg, #f8fbff, #eef6ff);">
                                <div class="font-weight-bold text-primary mb-2">Isi dokumen RAR yang harus diupload</div>
                                <div class="small text-muted mb-1">1. Foto Lingkungan : 2 foto</div>
                                <div class="small text-muted mb-1">2. Foto Jalan : 2 foto</div>
                                <div class="small text-muted mb-0">3. Foto Rumah : 2 foto</div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="batch-dropzone js-dropzone">
                                    <input type="file" name="batch_rar_file" id="create_batch_rar_file" class="js-dropzone-input">
                                    <div class="batch-dropzone-content">
                                        <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="batch-dropzone-title">Drag & drop file RAR di sini</div>
                                        <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                        <div class="batch-dropzone-file js-dropzone-label">Belum ada file dipilih</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="create_rar_not_required" name="is_document_not_required" value="1">
                                    <label class="custom-control-label" for="create_rar_not_required">Tidak membutuhkan dokument</label>
                                </div>
                                <small class="text-muted d-block mt-2">Jika dicentang, batch tetap bisa disubmit tanpa melampirkan file RAR.</small>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Remark</div>
                            <div class="form-group mb-0"><textarea name="remark_batch_approval" rows="3" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Batch Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-batch-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('Batch_Approval_MyRep/updateBatchApproval') ?>">
                    <input type="hidden" name="cluster_id" id="edit_id_myrep_cluster">
                    <input type="hidden" name="id_batch_approval" id="edit_id_batch_approval">
                    <div class="modal-header valsal-modal-header">
                        <h5 class="modal-title">Edit Batch Approval</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group"><label>Cluster</label><input type="text" id="edit_cluster_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Regional</label><input type="text" id="edit_regional_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Provinsi</label><input type="text" id="edit_province_name" class="form-control" readonly></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Kota</label><input type="text" id="edit_city_name" class="form-control" readonly></div></div>
                                <div class="col-md-12"><div class="form-group mb-0"><label>Tanggal VALSAL</label><input type="text" id="edit_valsal_date" class="form-control" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Data Pengajuan</div>
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>HP VALSAL</label><input type="text" id="edit_homepass_valsal" class="form-control js-number-format" data-decimals="0" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="text" name="hp_donasi" id="edit_hp_donasi" inputmode="numeric" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Tanggal Pengajuan</label><input type="date" name="submission_date" id="edit_submission_date" class="form-control"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Staging</label><select name="staging_status" id="edit_staging_status" class="form-control"><?php foreach ($statusOptions as $statusValue => $statusLabel): ?><option value="<?= $statusValue ?>"><?= $statusLabel ?></option><?php endforeach; ?></select></div></div>
                                <div class="col-md-6"><div class="form-group"><label>Nominal Donasi</label><input type="text" name="nominal_pengajuan_area" id="edit_nominal_pengajuan_area" inputmode="decimal" class="form-control js-number-format" data-decimals="0" required></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Nominal / Homepass</label><input type="text" id="edit_nominal_per_homepass" class="form-control js-number-format" data-decimals="2" readonly></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-freewifi-section">
                            <div class="batch-form-section__title">Free Wifi</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group mb-md-0"><label>Jumlah Free Wifi</label><input type="text" name="free_wifi_qty" id="edit_free_wifi_qty" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                                <div class="col-md-6"><div class="form-group mb-0"><label>Periode Free Wifi</label><input type="text" name="free_wifi_period_month" id="edit_free_wifi_period_month" inputmode="numeric" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section">
                            <div class="batch-form-section__title">Penerima Dana dan Bank</div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Nama Bank</label><input type="text" name="bank_name" id="edit_bank_name" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No Rekening</label><input type="text" name="bank_account_number" id="edit_bank_account_number" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Penerima Dana</label><input type="text" name="recipient_name" id="edit_recipient_name" class="form-control js-recipient-source" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>No HP Penerima</label><input type="text" name="recipient_phone" id="edit_recipient_phone" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Jabatan</label><input type="text" name="recipient_position" id="edit_recipient_position" class="form-control js-recipient-source"></div></div>
                                <div class="col-md-4"><div class="form-group mb-0"><label>Masa Jabatan</label><input type="text" name="recipient_period" id="edit_recipient_period" class="form-control js-recipient-source"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-emr-fields" data-stage-scope="edit" style="display:none;">
                            <div class="batch-form-section__title">Approval EMR</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Approval EMR</label><input type="text" name="nominal_nego_emr" id="edit_nominal_nego_emr" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section js-finance-fields" data-stage-scope="edit" style="display:none;">
                            <div class="batch-form-section__title">Release Finance</div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group mb-0"><label>Nominal Release Finance</label><input type="text" name="nominal_release_finance" id="edit_nominal_release_finance" inputmode="decimal" class="form-control js-number-format" data-decimals="0"></div></div>
                            </div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__head">
                                <div>
                                    <div class="batch-form-section__title mb-1">PIC Approval</div>
                                    <p class="batch-form-section__subtitle mb-0">PIC 1 mengikuti data penerima dana. Tambahkan PIC lain bila diperlukan.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm js-add-pic-row" data-target="#edit_pic_rows" data-prefix="edit">Tambah PIC</button>
                            </div>
                            <div class="batch-pic-list" id="edit_pic_rows"></div>
                        </div>

                        <div class="batch-form-section batch-form-section--last">
                            <div class="batch-form-section__title">Remark</div>
                            <div class="form-group mb-0"><textarea name="remark_batch_approval" id="edit_remark_batch_approval" rows="3" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Update Batch Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($docReady): ?>
        <div class="modal fade doc-modal" id="modal-batch-upload-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadDocument') ?>" enctype="multipart/form-data" id="batch-upload-document-form">
                        <input type="hidden" name="cluster_id" id="upload_cluster_id">
                        <div class="modal-header" style="background: linear-gradient(135deg, #198754, #34c38f);">
                            <div>
                                <h4 class="modal-title mb-1">Upload Dokumen RAR</h4>
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
                                <label class="font-weight-bold d-block">File RAR</label>
                                <div class="alert alert-light border mb-3" style="border-radius: 14px; border-color: #dbeafe !important; background: linear-gradient(135deg, #f8fbff, #eef6ff);">
                                    <div class="font-weight-bold text-primary mb-2">Isi dokumen RAR yang harus diupload</div>
                                    <div class="small text-muted mb-1">1. Foto Lingkungan: 2 foto</div>
                                    <div class="small text-muted mb-1">2. Foto Jalan: 2 foto</div>
                                    <div class="small text-muted mb-0">3. Foto Rumah: 2 foto</div>
                                </div>
                                <div class="upload-dropzone" id="batch-upload-dropzone">
                                    <input type="file" name="file" id="batch-upload-file-input">
                                    <div class="upload-dropzone-content">
                                        <div class="upload-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <div class="upload-dropzone-title">Drag & drop file di sini</div>
                                        <div class="upload-dropzone-text">Atau klik area ini untuk memilih file dari komputer</div>
                                        <div class="upload-dropzone-file" id="batch-upload-file-name">Belum ada file dipilih</div>
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
                            <div class="upload-progress-panel" id="batch-upload-progress-panel">
                                <div class="upload-progress-meta">
                                    <span>Upload Progress</span>
                                    <span id="batch-upload-progress-percent">0%</span>
                                </div>
                                <div class="upload-progress-bar-wrap">
                                    <div class="upload-progress-bar" id="batch-upload-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success" id="batch-upload-document-submit">Upload Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($canApprove): ?>
            <div class="modal fade doc-modal" id="modal-batch-approve-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/approveDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="approve_id_doc_file">
                            <div class="modal-header" style="background: linear-gradient(135deg, #15803d, #16a34a);">
                                <div>
                                    <h4 class="modal-title mb-1">Approve Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="approve_doc_name">RAR</p>
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

            <div class="modal fade doc-modal" id="modal-batch-reject-doc" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Batch_Approval_MyRep/rejectDocument') ?>">
                            <input type="hidden" name="id_doc_file" id="reject_id_doc_file">
                            <div class="modal-header" style="background: linear-gradient(135deg, #b91c1c, #dc2626);">
                                <div>
                                    <h4 class="modal-title mb-1">Reject Dokumen</h4>
                                    <p class="mb-0" style="opacity:.9;" id="reject_doc_name">RAR</p>
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

        <div class="modal fade doc-modal" id="modal-batch-history-doc" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                        <div>
                            <h4 class="modal-title mb-1">History Dokumen</h4>
                            <p class="mb-0" style="opacity:.9;" id="history_doc_name">RAR</p>
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

        <div class="modal fade doc-modal" id="modal-batch-transfer" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/uploadTransferProof') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="cluster_id" id="transfer_cluster_id">
                        <input type="hidden" name="id_batch_approval" id="transfer_batch_id">
                        <div class="modal-header" style="background: linear-gradient(135deg, #111827, #374151);">
                            <div>
                                <h4 class="modal-title mb-1">Upload Bukti Transfer</h4>
                                <p class="mb-0" style="opacity:.9;" id="transfer_cluster_name_caption"></p>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="doc-modal-panel">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">File Bukti Transfer</label>
                                    <input type="file" name="transfer_proof" class="form-control-file" required>
                                    <small class="text-muted d-block mt-2">Format: pdf, jpg, jpeg, png.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-dark">Upload Bukti Transfer</button>
                        </div>
                    </form>
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

    .valsal-filter-card .card-header,
    .valsal-table-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
    }

    .valsal-modal-header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    #modal-batch-create .modal-content,
    #modal-batch-edit .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    #modal-batch-create .modal-body,
    #modal-batch-edit .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .batch-form-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .batch-form-section--last {
        margin-bottom: 0;
    }

    .batch-form-section__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: .9rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-form-section__title {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .9rem;
    }

    .batch-form-section__subtitle {
        color: #6b7280;
        font-size: .9rem;
    }

    .batch-pic-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .batch-pic-card {
        border: 1px dashed #cdd8e5;
        border-radius: 12px;
        padding: 1rem;
        background: #fbfdff;
    }

    .batch-pic-card--primary {
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
        border-style: solid;
    }

    .batch-pic-card__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #e7ecf3;
    }

    .batch-pic-card__title {
        font-weight: 700;
        color: #1f2937;
    }

    .batch-pic-card__note {
        color: #6b7280;
        font-size: .85rem;
    }

    .batch-dropzone {
        position: relative;
        background: linear-gradient(135deg, #f0fdf4, #ecfeff);
        border: 2px dashed #60c7a0;
        border-radius: 16px;
        padding: 1rem;
        transition: all .2s ease;
        cursor: pointer;
    }

    .batch-dropzone.dragover {
        border-color: #198754;
        background: linear-gradient(135deg, #dcfce7, #d1fae5);
        transform: scale(1.01);
    }

    .batch-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .batch-dropzone-content {
        pointer-events: none;
        text-align: center;
    }

    .batch-dropzone-icon {
        font-size: 1.8rem;
        color: #198754;
        margin-bottom: .5rem;
    }

    .batch-dropzone-title {
        font-weight: 700;
        color: #166534;
        margin-bottom: .2rem;
    }

    .batch-dropzone-text {
        color: #4b5563;
        font-size: .9rem;
        margin-bottom: .3rem;
    }

    .batch-dropzone-file {
        color: #0f766e;
        font-weight: 600;
        font-size: .88rem;
    }

    .js-add-pic-row {
        position: relative;
        z-index: 2;
        cursor: pointer;
    }

    .valsal-table-card .table thead th {
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

    @media (max-width: 767.98px) {
        .batch-form-section__head,
        .batch-pic-card__head {
            flex-direction: column;
        }
    }
</style>

<script>
    (function () {
        var MAX_PIC_ROWS = 5;

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

        function bindInlineDropzones() {
            $('.js-dropzone').each(function () {
                var dropzone = this;
                var input = dropzone.querySelector('.js-dropzone-input');
                var label = dropzone.querySelector('.js-dropzone-label');

                if (!input || !label || dropzone.dataset.bound === '1') {
                    return;
                }

                dropzone.dataset.bound = '1';

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
            });
        }

        function syncClusterMeta(target) {
            var $container = $(target).closest('#modal-batch-create, #modal-batch-edit');
            if (!$container.length) {
                $container = $(target);
            }

            var select = $container.find('.js-batch-cluster-selector').get(0);
            if (!select) {
                return;
            }

            var selectedOption = select.selectedOptions && select.selectedOptions.length
                ? select.selectedOptions[0]
                : select.options[select.selectedIndex];
            var hasValue = !!(selectedOption && selectedOption.value);

            function optionData(name) {
                if (!selectedOption || !hasValue) {
                    return '';
                }

                return selectedOption.getAttribute('data-' + name) || '';
            }

            $container.find('.js-cluster-regional').val(optionData('regional-name'));
            $container.find('.js-cluster-province').val(optionData('province-name'));
            $container.find('.js-cluster-city').val(optionData('city-name'));
            $container.find('.js-cluster-name').val(optionData('cluster-name'));
            $container.find('.js-valsal-date').val(optionData('valsal-date'));
            if ($container.find('.js-homepass-valsal').length) {
                $container.find('.js-homepass-valsal').val(optionData('homepass-valsal'));
                $container.find('.js-homepass-valsal').each(function () {
                    applyNumberFormatting($(this));
                });
            }

            if ($container.attr('id') === 'modal-batch-create') {
                $('#create_hp_donasi').val(optionData('homepass-valsal'));
                applyNumberFormatting($('#create_hp_donasi'));
                updateNominalPerHomepass('create');
            }
        }

        function toggleStageFields(prefix) {
            var stageValue = $('#' + prefix + '_staging_status').val() || 'WAITING HO';
            var showEmr = ['WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;
            var showFinance = ['WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL'].indexOf(stageValue) !== -1;

            $('[data-stage-scope="' + prefix + '"].js-emr-fields').toggle(showEmr);
            $('[data-stage-scope="' + prefix + '"].js-finance-fields').toggle(showFinance);
        }

        function normalizeFormattedNumber(value) {
            var normalized = String(value || '').replace(/[^\d,.\-]/g, '');
            if (normalized === '') {
                return 0;
            }

            var hasComma = normalized.indexOf(',') !== -1;
            var dotCount = (normalized.match(/\./g) || []).length;

            if (hasComma) {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            } else if (dotCount > 1) {
                normalized = normalized.replace(/\./g, '');
            } else if (dotCount === 1) {
                var parts = normalized.split('.');
                var decimalLength = parts[1] ? parts[1].length : 0;
                if (decimalLength === 3) {
                    normalized = parts[0] + parts[1];
                }
            }

            var number = parseFloat(normalized);
            return isNaN(number) ? 0 : number;
        }

        function formatNumberValue(value, decimals) {
            var number = typeof value === 'number' ? value : normalizeFormattedNumber(value);
            if (!isFinite(number)) {
                number = 0;
            }

            var fixed = Number(number).toFixed(decimals);
            var parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            if (decimals > 0) {
                parts[1] = (parts[1] || '').replace(/0+$/, '');
                return parts[1] ? parts[0] + ',' + parts[1] : parts[0];
            }

            return parts[0];
        }

        function applyNumberFormatting($input) {
            var decimals = parseInt($input.data('decimals'), 10);
            if (isNaN(decimals)) {
                decimals = 0;
            }

            if ($input.val() === '') {
                return;
            }

            $input.val(formatNumberValue($input.val(), decimals));
        }

        function updateNominalPerHomepass(prefix) {
            var hpDonasi = normalizeFormattedNumber($('#' + prefix + '_hp_donasi').val());
            var nominalDonasi = normalizeFormattedNumber($('#' + prefix + '_nominal_pengajuan_area').val());
            var result = hpDonasi > 0 ? (nominalDonasi / hpDonasi) : 0;
            $('#' + prefix + '_nominal_per_homepass').val(result > 0 ? formatNumberValue(result, 2) : '');
        }

        function fillCreateDefaults() {
            var today = new Date();
            var month = String(today.getMonth() + 1).padStart(2, '0');
            var day = String(today.getDate()).padStart(2, '0');
            $('#create_submission_date').val(today.getFullYear() + '-' + month + '-' + day);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildPicRow(prefix, index, pic) {
            var isPrimary = index === 1;
            var rowClass = isPrimary ? 'batch-pic-card batch-pic-card--primary' : 'batch-pic-card';
            var note = isPrimary
                ? '<div class="batch-pic-card__note">Otomatis mengikuti penerima dana di atas.</div>'
                : '';
            var removeButton = isPrimary
                ? ''
                : '<button type="button" class="btn btn-outline-danger btn-sm js-remove-pic-row">Hapus</button>';
            var readOnly = isPrimary ? 'readonly' : '';

            return '' +
                '<div class="' + rowClass + '" data-pic-index="' + index + '">' +
                    '<div class="batch-pic-card__head">' +
                        '<div>' +
                            '<div class="batch-pic-card__title">PIC ' + index + '</div>' +
                            note +
                        '</div>' +
                        removeButton +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label>Nama PIC</label><input type="text" name="pic_name[]" class="form-control js-pic-name" value="' + escapeHtml(pic.pic_name || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>No HP PIC</label><input type="text" name="pic_phone[]" class="form-control js-pic-phone" value="' + escapeHtml(pic.pic_phone || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label>Jabatan PIC</label><input type="text" name="pic_position[]" class="form-control js-pic-position" value="' + escapeHtml(pic.pic_position || '') + '" ' + readOnly + '></div></div>' +
                        '<div class="col-md-3"><div class="form-group mb-0"><label>Periode PIC</label><input type="text" name="pic_period[]" class="form-control js-pic-period" value="' + escapeHtml(pic.pic_period || '') + '" ' + readOnly + '></div></div>' +
                    '</div>' +
                '</div>';
        }

        function getRecipientValues(prefix) {
            return {
                pic_name: $('#' + prefix + '_recipient_name').val() || '',
                pic_phone: $('#' + prefix + '_recipient_phone').val() || '',
                pic_position: $('#' + prefix + '_recipient_position').val() || '',
                pic_period: $('#' + prefix + '_recipient_period').val() || ''
            };
        }

        function normalizePicRows($target) {
            $target.children('.batch-pic-card').each(function (idx) {
                var index = idx + 1;
                $(this).attr('data-pic-index', index);
                $(this).find('.batch-pic-card__title').text('PIC ' + index);
            });
        }

        function syncPrimaryPic(prefix) {
            var $primary = $('#' + prefix + '_pic_rows').find('.batch-pic-card').first();
            if (!$primary.length) {
                return;
            }

            var values = getRecipientValues(prefix);
            $primary.find('.js-pic-name').val(values.pic_name);
            $primary.find('.js-pic-phone').val(values.pic_phone);
            $primary.find('.js-pic-position').val(values.pic_position);
            $primary.find('.js-pic-period').val(values.pic_period);
        }

        function renderPicRows(prefix, pics) {
            var $target = $('#' + prefix + '_pic_rows');
            var rows = Array.isArray(pics) ? pics.slice(0, MAX_PIC_ROWS) : [];
            var primaryValues = getRecipientValues(prefix);
            var primary = rows.length ? rows[0] : primaryValues;
            var html = buildPicRow(prefix, 1, {
                pic_name: primary.pic_name || primaryValues.pic_name,
                pic_phone: primary.pic_phone || primaryValues.pic_phone,
                pic_position: primary.pic_position || primaryValues.pic_position,
                pic_period: primary.pic_period || primaryValues.pic_period
            });

            for (var i = 1; i < rows.length && i < MAX_PIC_ROWS; i++) {
                html += buildPicRow(prefix, i + 1, rows[i]);
            }

            $target.html(html);
            syncPrimaryPic(prefix);
        }

        function addPicRow(prefix) {
            var $target = $('#' + prefix + '_pic_rows');
            var currentCount = $target.children('.batch-pic-card').length;
            if (currentCount >= MAX_PIC_ROWS) {
                return;
            }

            $target.append(buildPicRow(prefix, currentCount + 1, {}));
            normalizePicRows($target);
        }

        $(function () {
            $(document).on('change', '.js-batch-cluster-selector', function () {
                syncClusterMeta(this);
            });

            $('#modal-batch-create').on('shown.bs.modal', function () {
                syncClusterMeta(this);
                renderPicRows('create', []);
                toggleStageFields('create');
                fillCreateDefaults();
                $(this).find('.js-dropzone-label').text('Belum ada file dipilih');
                $(this).find('.js-number-format').each(function () {
                    applyNumberFormatting($(this));
                });
                updateNominalPerHomepass('create');
            });

            $('#modal-batch-create').on('hidden.bs.modal', function () {
                this.querySelector('form').reset();
                syncClusterMeta(this);
                renderPicRows('create', []);
                toggleStageFields('create');
                fillCreateDefaults();
                $(this).find('.js-dropzone-label').text('Belum ada file dipilih');
                updateNominalPerHomepass('create');
            });

            $(document).on('change', '#create_rar_not_required', function () {
                var isChecked = $(this).is(':checked');
                var fileInput = $('#create_batch_rar_file').get(0);
                var dropzone = $('#modal-batch-create .js-dropzone').get(0);

                if (isChecked && fileInput) {
                    fileInput.value = '';
                }

                if (dropzone) {
                    var label = dropzone.querySelector('.js-dropzone-label');
                    if (label && isChecked) {
                        label.textContent = 'Dokumen ditandai tidak dibutuhkan';
                    } else if (label && fileInput && (!fileInput.files || !fileInput.files.length)) {
                        label.textContent = 'Belum ada file dipilih';
                    }
                }
            });

            $('#create-batch-approval-form').on('submit', function (e) {
                var noDocumentRequired = $('#create_rar_not_required').is(':checked');
                var fileInput = $('#create_batch_rar_file').get(0);
                var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);

                if (!noDocumentRequired && !hasFile) {
                    e.preventDefault();
                    alert('Upload RAR wajib diisi. Centang "Tidak membutuhkan dokument" jika dokumen memang tidak diperlukan.');
                }
            });

            $(document).on('click', '.js-edit-batch', function () {
                var $button = $(this);
                var $modal = $('#modal-batch-edit');

                $modal.find('#edit_id_myrep_cluster').val($button.data('id_myrep_cluster'));
                $modal.find('#edit_id_batch_approval').val($button.data('id_batch_approval'));
                $modal.find('#edit_cluster_name').val($button.data('cluster_name'));
                $modal.find('#edit_regional_name').val($button.data('regional_name'));
                $modal.find('#edit_province_name').val($button.data('province_name'));
                $modal.find('#edit_city_name').val($button.data('city_name'));
                $modal.find('#edit_submission_date').val($button.data('submission_date'));
                $modal.find('#edit_hp_donasi').val($button.data('hp_donasi'));
                $modal.find('#edit_nominal_pengajuan_area').val($button.data('nominal_pengajuan_area'));
                $modal.find('#edit_nominal_nego_emr').val($button.data('nominal_nego_emr'));
                $modal.find('#edit_nominal_release_finance').val($button.data('nominal_release_finance'));
                $modal.find('#edit_bank_name').val($button.data('bank_name'));
                $modal.find('#edit_bank_account_number').val($button.data('bank_account_number'));
                $modal.find('#edit_recipient_name').val($button.data('recipient_name'));
                $modal.find('#edit_recipient_phone').val($button.data('recipient_phone'));
                $modal.find('#edit_recipient_position').val($button.data('recipient_position'));
                $modal.find('#edit_recipient_period').val($button.data('recipient_period'));
                $modal.find('#edit_free_wifi_qty').val($button.data('free_wifi_qty'));
                $modal.find('#edit_free_wifi_period_month').val($button.data('free_wifi_period_month'));
                $modal.find('#edit_astri_batch_number').val($button.data('astri_batch_number'));
                $modal.find('#edit_staging_status').val($button.data('staging_status'));
                $modal.find('#edit_homepass_valsal').val($button.data('homepass_valsal'));
                $modal.find('#edit_valsal_date').val($button.data('valsal_date'));
                $modal.find('#edit_remark_batch_approval').val($button.data('remark_batch_approval'));
                var pics = [];
                try {
                    pics = $button.attr('data-pics') ? JSON.parse($button.attr('data-pics')) : [];
                } catch (e) {
                    pics = [];
                }
                renderPicRows('edit', pics);
                toggleStageFields('edit');
                $modal.find('.js-number-format').each(function () {
                    applyNumberFormatting($(this));
                });
                updateNominalPerHomepass('edit');
            });

            $(document).on('click', '.js-add-pic-row', function (e) {
                e.preventDefault();
                addPicRow($(this).data('prefix'));
            });

            $(document).on('click', '.js-remove-pic-row', function () {
                var $target = $(this).closest('.batch-pic-list');
                $(this).closest('.batch-pic-card').remove();
                normalizePicRows($target);
            });

            $(document).on('input change', '#create_recipient_name, #create_recipient_phone, #create_recipient_position, #create_recipient_period', function () {
                syncPrimaryPic('create');
            });

            $(document).on('input change', '#edit_recipient_name, #edit_recipient_phone, #edit_recipient_position, #edit_recipient_period', function () {
                syncPrimaryPic('edit');
            });

            $(document).on('change', '#edit_staging_status', function () {
                toggleStageFields('edit');
            });

            $(document).on('input', '#create_hp_donasi, #create_nominal_pengajuan_area', function () {
                updateNominalPerHomepass('create');
            });

            $(document).on('input', '#edit_hp_donasi, #edit_nominal_pengajuan_area', function () {
                updateNominalPerHomepass('edit');
            });

            $(document).on('focus', '.js-number-format', function () {
                var value = $(this).val();
                if (value !== '') {
                    $(this).val(String(value).replace(/\./g, '').replace(',', '.'));
                }
            });

            $(document).on('blur', '.js-number-format', function () {
                applyNumberFormatting($(this));
                updateNominalPerHomepass('create');
                updateNominalPerHomepass('edit');
            });

            $(document).on('click', '.js-upload-doc', function () {
                var $button = $(this);
                $('#upload_cluster_id').val($button.data('cluster_id'));
                $('#upload_cluster_name').val($button.data('cluster_name'));
                $('#upload_doc_cluster_caption').text($button.data('cluster_name'));
                $('#upload_doc_status').val($button.data('doc_status'));
                $('#upload_doc_remark').val($button.data('doc_remark'));
                $('#upload_doc_not_required').prop('checked', false);
                $('#batch-upload-file-input').val('').prop('disabled', false).prop('required', true);
                $('#batch-upload-file-name').text('Belum ada file dipilih');
                $('#batch-upload-progress-panel').hide();
                $('#batch-upload-progress-bar').removeClass('success').css('width', '0%');
                $('#batch-upload-progress-percent').text('0%');
                $('#batch-upload-document-submit').prop('disabled', false).text('Upload Dokumen');
            });

            $(document).on('click', '.js-approve-doc', function () {
                var $button = $(this);
                $('#approve_id_doc_file').val($button.data('id_doc_file'));
                $('#approve_cluster_name').val($button.data('cluster_name'));
                $('#approve_doc_name').text('RAR');
            });

            $(document).on('click', '.js-reject-doc', function () {
                var $button = $(this);
                $('#reject_id_doc_file').val($button.data('id_doc_file'));
                $('#reject_cluster_name').val($button.data('cluster_name'));
                $('#reject_doc_name').text('RAR');
            });

            $(document).on('click', '.js-transfer-proof', function () {
                var $button = $(this);
                $('#transfer_cluster_id').val($button.data('cluster_id'));
                $('#transfer_batch_id').val($button.data('id_batch_approval'));
                $('#transfer_cluster_name_caption').text($button.data('cluster_name'));
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
                $('#batch-upload-file-input').prop('disabled', checked).prop('required', !checked);
                if (checked) {
                    $('#batch-upload-file-input').val('');
                    $('#batch-upload-file-name').text('File tidak diperlukan untuk item ini');
                } else {
                    $('#batch-upload-file-name').text('Belum ada file dipilih');
                }
            });

            $('#batch-upload-document-form').on('submit', function (e) {
                e.preventDefault();

                var form = this;
                var submitButton = $('#batch-upload-document-submit');
                var progressPanel = $('#batch-upload-progress-panel');
                var progressBar = $('#batch-upload-progress-bar');
                var progressPercent = $('#batch-upload-progress-percent');
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

            bindDropzone('#batch-upload-dropzone', '#batch-upload-file-input', '#batch-upload-file-name');
            bindInlineDropzones();
            renderPicRows('create', []);
            toggleStageFields('create');
            fillCreateDefaults();
            $('.js-number-format').each(function () {
                applyNumberFormatting($(this));
            });

            if ($.fn.DataTable) {
                try {
                    $('#table_batch_myrep').DataTable({
                        responsive: true,
                        autoWidth: false,
                        order: [[0, 'asc']],
                        language: {
                            emptyTable: 'Belum ada pengajuan Batch Approval.'
                        }
                    });
                } catch (error) {
                    console.error('DataTable Batch Approval gagal diinisialisasi:', error);
                }
            }
        });
    })();
</script>

