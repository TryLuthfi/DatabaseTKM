<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$statusOptions = ['WAITING DOC', 'WAITING APPROVE', 'COMPLETE', 'REJECTED', 'RELEASED', 'DRM', 'RFS', 'ATP', 'DONE BATCH APPROVAL'];
$today = date('Y-m-d');
$summaryNyDrm = 0;
$summaryOnProses = 0;
$summaryDone = 0;
$summaryRejected = 0;
$summaryNyDrmHp = 0;
$summaryOnProsesHp = 0;
$summaryDoneHp = 0;
$summaryRejectedHp = 0;
$createCityOptions = [];
$postDrmStatuses = ['RFS', 'ATP', 'DONE'];

foreach ($eligibleClusterOptions as $clusterOption) {
    $cityName = trim((string) ($clusterOption['city_name'] ?? ''));
    if ($cityName !== '') {
        $createCityOptions[strtoupper($cityName)] = $cityName;
    }
}

asort($createCityOptions);

foreach ($clusterRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? 'RELEASED')));
    $drmStatus = strtoupper(trim((string) ($row['display_status_drm'] ?? $row['status_drm'] ?? 'DRAFT')));
    $hasDrm = (int) ($row['id_drm'] ?? 0) > 0;
    $homepassBase = (float) ($row['hp_donasi'] ?? 0);
    $homepassDrm = (float) ($row['homepass_drm'] ?? 0);
    $summaryHomepass = $homepassDrm > 0 ? $homepassDrm : $homepassBase;

    if (!$hasDrm && in_array($currentStatus, ['RELEASED', 'DONE BATCH APPROVAL'], true)) {
        $summaryNyDrm++;
        $summaryNyDrmHp += $homepassBase;
    }

    if ($hasDrm && !in_array($drmStatus, ['COMPLETE', 'REJECTED'], true)) {
        $summaryOnProses++;
        $summaryOnProsesHp += $summaryHomepass;
    }

    if ($hasDrm && $drmStatus === 'COMPLETE' && !in_array($currentStatus, $postDrmStatuses, true)) {
        $summaryDone++;
        $summaryDoneHp += $summaryHomepass;
    }

    if ($hasDrm && ($drmStatus === 'REJECTED' || $currentStatus === 'REJECTED')) {
        $summaryRejected++;
        $summaryRejectedHp += $summaryHomepass;
    }
}

if (!function_exists('drmBadgeClass')) {
    function drmBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'DONE':
            case 'COMPLETE':
            case 'DRM':
            case 'RFS':
            case 'ATP':
                return 'success';
            case 'WAITING APPROVE':
                return 'warning';
            case 'WAITING DOC':
            case 'WAITING INPUT':
                return 'info';
            case 'REJECTED':
                return 'danger';
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
                return 'info';
            default:
                return 'secondary';
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">DRM MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">Tabel DRM MyRep belum tersedia.</div>
            <?php endif; ?>

            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen DRM belum tersedia. Form DRM tetap bisa dipakai, tetapi workflow dokumen belum aktif penuh.</div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm drm-filter-card">
                        <div class="card-header drm-section-header">
                            <div>
                                <h3 class="card-title mb-1">Filter Data DRM</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('DRM_MyRep') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="drm-field-label">Kota</label>
                                            <select name="city" class="form-control drm-input">
                                                <option value="">Semua Kota</option>
                                                <?php foreach ($cityOptions as $cityOption): ?>
                                                    <option value="<?= htmlspecialchars($cityOption) ?>" <?= $selectedCity === strtoupper($cityOption) ? 'selected' : '' ?>><?= htmlspecialchars($cityOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="drm-field-label">Status</label>
                                            <select name="status" class="form-control drm-input">
                                                <option value="">Semua Status</option>
                                                <?php foreach (array_unique($statusOptions) as $statusOption): ?>
                                                    <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group mb-0 w-100 d-flex justify-content-between drm-filter-actions">
                                            <a href="<?= base_url('DRM_MyRep') ?>" class="btn budget-btn budget-btn--ghost">Reset</a>
                                            <?php if ($isReady): ?>
                                                <button type="submit" class="btn budget-btn budget-btn--primary">Terapkan Filter</button>
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
                    <div class="small-box bg-info shadow-sm drm-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryNyDrm, 0, ',', '.') ?></h3>
                            <p>NY DRM</p>
                            <p class="drm-summary-box__meta mb-0">HP <?= number_format($summaryNyDrmHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary shadow-sm drm-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryOnProses, 0, ',', '.') ?></h3>
                            <p>On Proses</p>
                            <p class="drm-summary-box__meta mb-0">HP <?= number_format($summaryOnProsesHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-folder-open"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success shadow-sm drm-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryDone, 0, ',', '.') ?></h3>
                            <p>Done DRM</p>
                            <p class="drm-summary-box__meta mb-0">HP <?= number_format($summaryDoneHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger shadow-sm drm-summary-box">
                        <div class="inner">
                            <h3><?= number_format($summaryRejected, 0, ',', '.') ?></h3>
                            <p>Rejected</p>
                            <p class="drm-summary-box__meta mb-0">HP <?= number_format($summaryRejectedHp, 0, ',', '.') ?></p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="drm-toolbar">
                        <?php if ($isReady): ?>
                            <button type="button" class="btn budget-btn budget-btn--primary" data-toggle="modal" data-target="#modal-drm-create">
                                <i class="fas fa-plus mr-1"></i> Input DRM
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--ghost ml-2" data-toggle="modal" data-target="#modal-drm-import">
                                <i class="fas fa-file-import mr-1"></i> Import DRM
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm drm-table-card">
                        <div class="card-header drm-section-header">
                            <div>
                                <h3 class="card-title mb-1">Monitoring DRM</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_drm_myrep" class="table table-bordered table-hover drm-monitor-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Cluster</th>
                                            <th>Kota</th>
                                            <th>Periode</th>
                                            <th>Released</th>
                                            <th>HP Donasi</th>
                                            <th>HP DRM</th>
                                            <th>Status DRM</th>
                                            <th>Progress Dokumen</th>
                                            <th>Status Flow</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clusterRows as $index => $row): ?>
                                            <?php
                                            $targetLabel = !empty($row['month_num']) && !empty($row['year_num']) ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num']) : '-';
                                            $hasDrm = (int) ($row['id_drm'] ?? 0) > 0;
                                            $statusDrmLabel = $hasDrm ? (string) ($row['display_status_drm'] ?? $row['status_drm'] ?? 'WAITING DOC') : 'WAITING INPUT';
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                                    <div class="text-muted small"><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                                <td><?= $targetLabel ?></td>
                                                <td><?= !empty($row['released_at']) ? htmlspecialchars((string) $row['released_at']) : '-' ?></td>
                                                <td class="text-right"><?= number_format((float) ($row['hp_donasi'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) ($row['homepass_drm'] ?? 0), 0, ',', '.') ?></td>
                                                <td><span class="badge badge-<?= drmBadgeClass($statusDrmLabel) ?>"><?= htmlspecialchars($statusDrmLabel) ?></span></td>
                                                <td><?= (int) ($row['doc_approved'] ?? 0) ?>/<?= (int) ($row['doc_total'] ?? 0) ?> approved</td>
                                                <td><span class="badge badge-<?= drmBadgeClass($row['status_current'] ?? 'RELEASED') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'RELEASED')) ?></span></td>
                                                <td>
                                                    <?php if ($hasDrm): ?>
                                                        <a href="<?= base_url('DRM_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                                        <form method="post" action="<?= base_url('DRM_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini beserta DRM dan seluruh flow MyRep terkait?');">
                                                            <input type="hidden" name="cluster_id" value="<?= (int) $row['id_myrep_cluster'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger mt-1">Hapus Cluster</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary js-start-drm"
                                                            data-toggle="modal"
                                                            data-target="#modal-drm-create"
                                                            data-cluster_id="<?= (int) $row['id_myrep_cluster'] ?>"
                                                            data-city_name="<?= htmlspecialchars((string) ($row['city_name'] ?? ''), ENT_QUOTES) ?>">
                                                            Input DRM
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($clusterRows)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">Belum ada data DRM.</td>
                                            </tr>
                                        <?php endif; ?>
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
    <div class="modal fade" id="modal-drm-import" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content budget-modal drm-modal-shell">
                <form id="drm-import-preview-form" enctype="multipart/form-data">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <div class="budget-modal__eyebrow">DRM MyRep</div>
                            <h5 class="modal-title mb-1">Import DRM (Excel/CSV)</h5>
                            <p class="budget-modal__subtitle mb-0">Auto-create cluster jika belum ada, auto BAK DONE, auto VALSAL DONE, auto BATCH RELEASED, lalu input DRM.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-section">
                            <a href="<?= base_url('DRM_MyRep/downloadDrmImportTemplate') ?>" class="btn budget-btn budget-btn--success">
                                <i class="fas fa-download mr-1"></i> Download Format CSV
                            </a>
                        </div>
                        <div class="drm-form-section">
                            <div class="batch-dropzone" id="drm-import-dropzone">
                                <input type="file" id="drm-import-file-input" name="file_excel" accept=".xls,.xlsx,.csv">
                                <div class="batch-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="batch-dropzone-title">Drag & drop file import di sini</div>
                                <div class="batch-dropzone-text">Atau klik area ini untuk memilih file</div>
                                <div class="batch-dropzone-file" id="drm-import-file-name">Belum ada file dipilih</div>
                            </div>
                        </div>
                        <div class="drm-form-section mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="drm-form-section__title mb-0">Preview Import</div>
                                <small id="drm-import-summary" class="text-muted">Belum ada file dipreview</small>
                            </div>
                            <div class="table-responsive" style="max-height:320px;">
                                <table class="table table-bordered table-sm mb-0" id="table_drm_import_preview">
                                    <thead>
                                        <tr>
                                            <th>Row</th><th>Cluster</th><th>Kota</th><th>HP DRM</th><th>Tanggal DRM</th><th>Status</th><th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="7" class="text-center text-muted">Belum ada data preview</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn budget-btn budget-btn--primary" id="drm-save-import-btn" disabled>Simpan Hasil Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-drm-create" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content budget-modal drm-modal-shell">
                <form method="post" action="<?= base_url('DRM_MyRep/saveDrm') ?>">
                    <div class="modal-header budget-modal__header">
                        <div>
                            <div class="budget-modal__eyebrow">DRM MyRep</div>
                            <h5 class="modal-title mb-1">Input DRM Baru</h5>
                            <p class="budget-modal__subtitle mb-0">Pilih cluster released yang siap masuk proses DRM, lalu lengkapi header DRM. Status dokumen akan bergerak otomatis dari waiting doc sampai complete.</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="drm-form-section">
                            <div class="drm-form-section__title">Pilih Cluster</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <select class="form-control js-drm-city-selector">
                                            <option value="">Pilih kota</option>
                                            <?php foreach ($createCityOptions as $cityValue => $cityLabel): ?>
                                                <option value="<?= htmlspecialchars($cityValue, ENT_QUOTES) ?>"><?= htmlspecialchars($cityLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label>Cluster Eligible</label>
                                        <select name="cluster_id" class="form-control js-drm-cluster-selector js-drm-cluster-select" required>
                                            <option value="">Pilih cluster released</option>
                                            <?php foreach ($eligibleClusterOptions as $option): ?>
                                                <?php $targetLabel = !empty($option['month_num']) && !empty($option['year_num']) ? sprintf('%02d/%04d', (int) $option['month_num'], (int) $option['year_num']) : '-'; ?>
                                                <option
                                                    value="<?= (int) $option['id_myrep_cluster'] ?>"
                                                    data-city-filter="<?= htmlspecialchars(strtoupper((string) ($option['city_name'] ?? '')), ENT_QUOTES) ?>"
                                                    data-cluster-name="<?= htmlspecialchars((string) ($option['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-regional-name="<?= htmlspecialchars((string) ($option['regional_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-city-name="<?= htmlspecialchars((string) ($option['city_name'] ?? ''), ENT_QUOTES) ?>"
                                                    data-released-at="<?= htmlspecialchars((string) ($option['released_at'] ?? ''), ENT_QUOTES) ?>"
                                                    data-hp-donasi="<?= (int) ($option['hp_donasi'] ?? 0) ?>"
                                                    data-hp-valsal="<?= (int) ($option['homepass_valsal'] ?? 0) ?>"
                                                    data-period-label="<?= htmlspecialchars((string) $targetLabel, ENT_QUOTES) ?>">
                                                    <?= htmlspecialchars((string) ($option['cluster_name'] ?? '-')) ?> | <?= htmlspecialchars((string) ($option['city_name'] ?? '-')) ?> | <?= htmlspecialchars((string) $targetLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="drm-form-section">
                            <div class="drm-form-section__title">Informasi Cluster</div>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label>Cluster</label><input type="text" class="form-control js-cluster-name" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Regional</label><input type="text" class="form-control js-cluster-regional" readonly></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Kota</label><input type="text" class="form-control js-cluster-city" readonly></div></div>
                                <div class="col-md-3"><div class="form-group mb-md-0"><label>Periode</label><input type="text" class="form-control js-period-label" readonly></div></div>
                                <div class="col-md-3"><div class="form-group mb-md-0"><label>Tanggal Released</label><input type="text" class="form-control js-released-at" readonly></div></div>
                                <div class="col-md-3"><div class="form-group mb-md-0"><label>HP Valsal</label><input type="text" class="form-control js-hp-valsal js-number-format" data-decimals="0" readonly></div></div>
                                <div class="col-md-3"><div class="form-group mb-0"><label>HP Donasi</label><input type="text" class="form-control js-hp-donasi js-number-format" data-decimals="0" readonly></div></div>
                            </div>
                        </div>

                        <div class="drm-form-section drm-form-section--last">
                            <div class="drm-form-section__title">Data DRM</div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal DRM</label>
                                        <input type="date" name="drm_date" class="form-control" value="<?= $today ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Homepass DRM</label>
                                        <input type="text" name="homepass_drm" class="form-control js-homepass-drm js-number-format" data-decimals="0" inputmode="numeric" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status DRM</label>
                                        <input type="text" name="status_drm" class="form-control" value="WAITING DOC" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nama OLT</label>
                                        <input type="text" name="nama_olt" class="form-control" placeholder="Isi nama OLT">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Remark</label>
                                        <input type="text" name="remark_drm" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer budget-modal__footer">
                        <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn budget-btn budget-btn--primary">Simpan DRM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .drm-filter-card,
    .drm-table-card {
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
        background: #fff;
    }

    .drm-filter-card .card-header,
    .drm-table-card .card-header {
        background: linear-gradient(135deg, #f8fbff, #eef6ff);
        border-bottom: 1px solid #dbeafe;
        padding: 1.15rem 1.35rem;
    }

    .drm-filter-card .card-body,
    .drm-table-card .card-body {
        padding: 1.35rem;
    }

    .drm-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .drm-section-subtitle {
        color: #64748b;
        font-size: .92rem;
        margin-top: .2rem;
    }

    .drm-field-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
    }

    .drm-input,
    .drm-modal-shell .form-control,
    .drm-modal-shell select.form-control {
        min-height: 44px;
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .drm-input:focus,
    .drm-modal-shell .form-control:focus,
    .drm-modal-shell select.form-control:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 .2rem rgba(96, 165, 250, 0.16);
    }

    .drm-modal-shell .form-control[readonly],
    .drm-modal-shell .form-control:disabled {
        background: #eef4fb;
        border-color: #d7e3f1;
        color: #64748b;
        cursor: not-allowed;
    }

    .drm-summary-box {
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1);
    }

    .drm-summary-box .inner h3 {
        font-weight: 800;
    }

    .drm-summary-box__meta {
        font-size: .88rem;
        font-weight: 600;
        opacity: .92;
    }

    .drm-toolbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: .85rem;
    }

    .budget-btn {
        border: 0;
        border-radius: 999px;
        padding: .72rem 1.2rem;
        font-weight: 700;
        letter-spacing: .01em;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .budget-btn:hover {
        transform: translateY(-1px);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #334155;
        border: 1px solid #d7e0ea;
        box-shadow: none;
    }

    .drm-monitor-table thead th {
        background: linear-gradient(180deg, #eef6fb 0%, #dcecf8 100%);
        color: #1f5e8a;
        font-size: .8rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
        border-top: 0;
    }

    .drm-monitor-table tbody tr:hover {
        background: rgba(219, 236, 247, 0.22);
    }

    .budget-modal__header {
        background: linear-gradient(135deg, #0f4c81, #1d7ed6);
        color: #fff;
        padding: 1.25rem 1.35rem;
        border-bottom: 0;
    }

    .budget-modal__eyebrow {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .14em;
        font-weight: 800;
        opacity: .82;
        margin-bottom: .35rem;
    }

    .budget-modal__subtitle {
        color: rgba(255, 255, 255, 0.86);
        font-size: .92rem;
        line-height: 1.5;
    }

    .budget-modal__footer {
        border-top: 1px solid #e7ecf3;
        background: #fff;
        padding: 1rem 1.25rem 1.15rem;
        gap: .75rem;
    }

    .drm-modal-shell .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .drm-modal-shell .modal-body {
        background: #f6f8fb;
        padding: 1.25rem;
    }

    .drm-form-section {
        background: #fff;
        border: 1px solid #e7ecf3;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
    }

    .drm-form-section--last {
        margin-bottom: 0;
    }

    .drm-form-section__title {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .9rem;
    }

    .modal-xxl {
        max-width: 78vw;
    }

    .batch-dropzone {
        border: 2px dashed #94c8ff;
        border-radius: 14px;
        background: linear-gradient(180deg, #f6fbff, #edf6ff);
        padding: 1.2rem;
        text-align: center;
        position: relative;
        transition: border-color .2s ease, background .2s ease;
    }

    .batch-dropzone.dragover {
        border-color: #1d7ed6;
        background: #e8f3ff;
    }

    .batch-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
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

    .select2-container--open {
        z-index: 1065;
    }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 44px;
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        padding: .35rem .55rem;
    }

    .select2-dropdown {
        border-radius: 14px;
        border: 1px solid #d7e0ea;
        overflow: hidden;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
    }

    @media (max-width: 767.98px) {
        .drm-toolbar {
            justify-content: stretch;
        }

        .drm-toolbar .budget-btn {
            width: 100%;
        }

        .modal-xxl {
            max-width: calc(100vw - 1rem);
            margin: .5rem auto;
        }
    }
</style>

<script>
    (function () {
        var drmPreviewImportUrl = '<?= base_url('DRM_MyRep/previewDrmImport') ?>';
        var drmSaveImportUrl = '<?= base_url('DRM_MyRep/saveImportedDrm') ?>';
        var importedDrmRows = [];

        function initDrmSelects() {
            var $modal = $('#modal-drm-create');
            if (!$.fn.select2 || !$modal.length) {
                return;
            }

            $modal.find('.js-drm-city-selector, .js-drm-cluster-select').each(function () {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    width: '100%',
                    placeholder: $select.hasClass('js-drm-city-selector') ? 'Pilih kota' : 'Pilih cluster',
                    allowClear: true,
                    dropdownParent: $modal
                });
            });
        }

        function normalizeFormattedNumber(value) {
            var normalized = String(value || '').replace(/[^\d,.\-]/g, '');
            if (normalized === '') {
                return 0;
            }

            normalized = normalized.replace(/[.,]/g, '');
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
            return decimals > 0 ? parts[0] + ',' + (parts[1] || '') : parts[0];
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

        function syncClusterMeta($container) {
            var $select = $container.find('.js-drm-cluster-selector').first();
            var select = $select.get(0);
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

            $container.find('.js-cluster-name').val(optionData('cluster-name'));
            $container.find('.js-cluster-regional').val(optionData('regional-name'));
            $container.find('.js-cluster-city').val(optionData('city-name'));
            $container.find('.js-released-at').val(optionData('released-at'));
            $container.find('.js-period-label').val(optionData('period-label'));
            $container.find('.js-hp-valsal').val(optionData('hp-valsal'));
            $container.find('.js-hp-donasi').val(optionData('hp-donasi'));
            $container.find('.js-homepass-drm').val(optionData('hp-donasi'));
            $container.find('.js-number-format').each(function () {
                applyNumberFormatting($(this));
            });
        }

        function syncCityFromCluster($container) {
            var $clusterSelect = $container.find('.js-drm-cluster-selector').first();
            var $citySelect = $container.find('.js-drm-city-selector').first();
            var select = $clusterSelect.get(0);
            var selectedOption = select && select.selectedOptions && select.selectedOptions.length
                ? select.selectedOptions[0]
                : (select ? select.options[select.selectedIndex] : null);

            if (!$clusterSelect.length || !$citySelect.length || !selectedOption || !selectedOption.value) {
                return;
            }

            var clusterCity = ((selectedOption.getAttribute('data-city-filter') || '') + '').toUpperCase();
            if (!clusterCity) {
                return;
            }

            if (($citySelect.val() || '').toString().toUpperCase() !== clusterCity) {
                $citySelect.val(clusterCity).trigger('change.select2');
            }
        }

        function filterDrmClusterOptions($modal) {
            var selectedCity = ($modal.find('.js-drm-city-selector').val() || '').toUpperCase();
            var $clusterSelect = $modal.find('.js-drm-cluster-selector');

            $clusterSelect.find('option').each(function () {
                var $option = $(this);
                var optionValue = $option.attr('value');

                if (!optionValue) {
                    $option.prop('hidden', false).prop('disabled', false);
                    return;
                }

                var optionCity = (($option.attr('data-city-filter') || '') + '').toUpperCase();
                var shouldShow = selectedCity === '' || optionCity === selectedCity;
                $option.prop('hidden', !shouldShow).prop('disabled', !shouldShow);
            });

            if (selectedCity !== '') {
                var currentOption = $clusterSelect.find('option:selected');
                var currentCity = ((currentOption.attr('data-city-filter') || '') + '').toUpperCase();
                if (currentCity !== selectedCity) {
                    $clusterSelect.val('');
                }
            }

            $clusterSelect.trigger('change.select2');
            syncClusterMeta($modal);
        }

        function applyCreateDrmPreset($modal) {
            var presetClusterId = ($modal.attr('data-preset-cluster-id') || '').toString();
            var presetCity = ($modal.attr('data-preset-city') || '').toString().toUpperCase();

            if (presetCity !== '') {
                $modal.find('.js-drm-city-selector').val(presetCity).trigger('change.select2');
            }

            if (presetClusterId !== '') {
                $modal.find('.js-drm-cluster-selector').val(presetClusterId).trigger('change');
            }
        }

        function bindDropzone(dropzoneSelector, inputSelector, labelSelector) {
            var dropzone = document.querySelector(dropzoneSelector);
            var input = document.querySelector(inputSelector);
            var label = document.querySelector(labelSelector);
            if (!dropzone || !input || !label) { return; }

            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault(); e.stopPropagation(); dropzone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('dragover');
                });
            });
            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    label.textContent = e.dataTransfer.files[0].name;
                }
            });
            input.addEventListener('change', function () {
                label.textContent = (input.files && input.files.length > 0) ? input.files[0].name : 'Belum ada file dipilih';
            });
        }

        function resetDrmImportPreview() {
            importedDrmRows = [];
            $('#drm-import-summary').text('Belum ada file dipreview');
            $('#drm-save-import-btn').prop('disabled', true);
            $('#table_drm_import_preview tbody').html('<tr><td colspan="7" class="text-center text-muted">Belum ada data preview</td></tr>');
        }

        function renderDrmImportPreview(rows) {
            if (!rows || !rows.length) {
                $('#table_drm_import_preview tbody').html('<tr><td colspan="7" class="text-center text-muted">Belum ada data preview</td></tr>');
                return;
            }
            var html = rows.map(function (row) {
                var badgeClass = String(row.status || '').toLowerCase() === 'valid' ? 'success' : 'danger';
                return '<tr>' +
                    '<td>' + Number(row.row_number || 0) + '</td>' +
                    '<td>' + (row.cluster_name || '-') + '</td>' +
                    '<td>' + (row.city_name || '-') + '</td>' +
                    '<td class="text-right">' + Number(row.homepass_drm || 0).toLocaleString('id-ID') + '</td>' +
                    '<td>' + (row.drm_date || '-') + '</td>' +
                    '<td><span class="badge badge-' + badgeClass + '">' + (row.status || '-') + '</span></td>' +
                    '<td>' + (row.message || '-') + '</td>' +
                '</tr>';
            }).join('');
            $('#table_drm_import_preview tbody').html(html);
        }

        $(function () {
            if ($.fn.DataTable) {
                $('#table_drm_myrep').DataTable({
                    responsive: true,
                    autoWidth: false,
                    order: [[0, 'asc']]
                });
            }

            $(document).on('change', '.js-drm-cluster-selector', function () {
                var $container = $(this).closest('.modal-body, .modal-content');
                syncCityFromCluster($container);
                filterDrmClusterOptions($container);
                syncClusterMeta($container);
            });

            $(document).on('change', '.js-drm-city-selector', function () {
                filterDrmClusterOptions($(this).closest('.modal-body, .modal-content'));
            });

            $('#modal-drm-create').on('shown.bs.modal', function () {
                initDrmSelects();
                $(this).find('.js-drm-city-selector').val('').trigger('change');
                $(this).find('.js-drm-cluster-selector').val('').trigger('change');
                filterDrmClusterOptions($(this));
                applyCreateDrmPreset($(this));
                syncClusterMeta($(this));
            }).on('hidden.bs.modal', function () {
                this.querySelector('form').reset();
                $(this).removeAttr('data-preset-cluster-id').removeAttr('data-preset-city');
                var $citySelect = $(this).find('.js-drm-city-selector');
                var $clusterSelect = $(this).find('.js-drm-cluster-select');
                if ($citySelect.hasClass('select2-hidden-accessible')) {
                    $citySelect.select2('close');
                }
                if ($clusterSelect.hasClass('select2-hidden-accessible')) {
                    $clusterSelect.select2('close');
                }
                syncClusterMeta($(this));
            });

            $(document).on('click', '.js-start-drm', function () {
                var $button = $(this);
                $('#modal-drm-create')
                    .attr('data-preset-cluster-id', ($button.data('cluster_id') || '').toString())
                    .attr('data-preset-city', ($button.data('city_name') || '').toString().toUpperCase());
            });

            $(document).on('input blur', '.js-number-format', function () {
                applyNumberFormatting($(this));
            });

            $('#modal-drm-import').on('shown.bs.modal', function () {
                resetDrmImportPreview();
                $('#drm-import-file-input').val('');
                $('#drm-import-file-name').text('Belum ada file dipilih');
            });

            $('#drm-import-file-input').on('change', function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) { return; }
                var formData = new FormData($('#drm-import-preview-form')[0]);
                formData.set('file_excel', file);
                $('#drm-import-summary').text('Memproses preview...');
                $.ajax({
                    url: drmPreviewImportUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (!response || !response.status) {
                            resetDrmImportPreview();
                            alert(response && response.message ? response.message : 'Preview import DRM gagal.');
                            return;
                        }
                        importedDrmRows = response.valid_rows || [];
                        $('#drm-import-summary').text(response.message || 'Preview selesai');
                        $('#drm-save-import-btn').prop('disabled', !importedDrmRows.length);
                        renderDrmImportPreview(response.rows || []);
                    },
                    error: function () {
                        resetDrmImportPreview();
                        alert('Terjadi kesalahan saat preview import DRM.');
                    }
                });
            });

            $('#drm-save-import-btn').on('click', function () {
                if (!importedDrmRows.length) {
                    alert('Belum ada data valid untuk disimpan.');
                    return;
                }
                $.ajax({
                    url: drmSaveImportUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { rows_json: JSON.stringify(importedDrmRows) },
                    success: function (response) {
                        if (response && response.status) {
                            alert(response.message || 'Import DRM berhasil.');
                            window.location.reload();
                            return;
                        }
                        alert(response && response.message ? response.message : 'Gagal menyimpan import DRM.');
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat menyimpan import DRM.');
                    }
                });
            });

            bindDropzone('#drm-import-dropzone', '#drm-import-file-input', '#drm-import-file-name');
        });
    })();
</script>
