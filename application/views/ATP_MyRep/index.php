<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$summaryTotal = count($clusterList);
$summaryTotalHp = 0;
$stageSummary = [];

foreach ($stageOptions as $stageOption) {
    $stageSummary[$stageOption] = [
        'count' => 0,
        'hp' => 0,
    ];
}

foreach ($clusterList as $cluster) {
    $homepass = (float) ($cluster['homepass'] ?? 0);
    $stage = (string) ($cluster['stage_atp'] ?? '');

    $summaryTotalHp += $homepass;

    if (!isset($stageSummary[$stage])) {
        $stageSummary[$stage] = [
            'count' => 0,
            'hp' => 0,
        ];
    }

    $stageSummary[$stage]['count']++;
    $stageSummary[$stage]['hp'] += $homepass;
}

if (!function_exists('atp_myrep_format_date')) {
    function atp_myrep_format_date($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}

if (!function_exists('atpMyrepBadgeClass')) {
    function atpMyrepBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'ATP DONE':
            case 'DONE':
                return 'success';
            case 'ATP PUNCLIST':
            case 'PUNCLIST':
                return 'danger';
            case 'PROSES ATP':
                return 'primary';
            case 'WAITING ATP':
                return 'info';
            case 'WAITING JADWAL ATP':
                return 'warning';
            case 'WAITING EMAIL':
                return 'secondary';
            default:
                return 'dark';
        }
    }
}

$summaryCards = [
    [
        'label' => 'Total Cluster ATP',
        'count' => $summaryTotal,
        'hp' => $summaryTotalHp,
        'box' => 'bg-info',
        'class' => 'atp-summary-box--info',
        'icon' => 'fas fa-layer-group',
    ],
    [
        'label' => 'Waiting Email',
        'count' => (int) ($stageSummary['Waiting Email']['count'] ?? 0),
        'hp' => (float) ($stageSummary['Waiting Email']['hp'] ?? 0),
        'box' => 'bg-secondary',
        'class' => 'atp-summary-box--secondary',
        'icon' => 'fas fa-envelope-open-text',
    ],
    [
        'label' => 'Waiting / Proses ATP',
        'count' => (int) (($stageSummary['Waiting Jadwal ATP']['count'] ?? 0) + ($stageSummary['Waiting ATP']['count'] ?? 0) + ($stageSummary['PROSES ATP']['count'] ?? 0)),
        'hp' => (float) (($stageSummary['Waiting Jadwal ATP']['hp'] ?? 0) + ($stageSummary['Waiting ATP']['hp'] ?? 0) + ($stageSummary['PROSES ATP']['hp'] ?? 0)),
        'box' => 'bg-primary',
        'class' => 'atp-summary-box--primary',
        'icon' => 'fas fa-calendar-check',
    ],
    [
        'label' => 'ATP PUNCLIST',
        'count' => (int) ($stageSummary['ATP PUNCLIST']['count'] ?? 0),
        'hp' => (float) ($stageSummary['ATP PUNCLIST']['hp'] ?? 0),
        'box' => 'bg-danger',
        'class' => 'atp-summary-box--danger',
        'icon' => 'fas fa-tools',
    ],
    [
        'label' => 'ATP DONE',
        'count' => (int) ($stageSummary['ATP DONE']['count'] ?? 0),
        'hp' => (float) ($stageSummary['ATP DONE']['hp'] ?? 0),
        'box' => 'bg-success',
        'class' => 'atp-summary-box--success',
        'icon' => 'fas fa-check-circle',
    ],
];
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">ATP MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$schemaReady): ?>
                <div class="alert alert-warning">
                    Tabel ATP MyRep belum siap. Jalankan query di file <code>db/patch_myrep_atp_20260508.sql</code> terlebih dahulu.
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $flashSuccess ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $flashError ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm atp-filter-card">
                        <div class="card-header atp-section-header">
                            <div>
                                <h3 class="card-title mb-1">Filter Data ATP</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('ATP_MyRep') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="atp-field-label">Regional</label>
                                            <select name="regional" class="form-control atp-input">
                                                <option value="">Semua Regional</option>
                                                <?php foreach ($regionalOptions as $regionalOption): ?>
                                                    <option value="<?= htmlspecialchars($regionalOption) ?>" <?= $selectedRegional === $regionalOption ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($regionalOption) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="atp-field-label">Kota</label>
                                            <select name="city" class="form-control atp-input">
                                                <option value="">Semua Kota</option>
                                                <?php foreach ($cityOptions as $cityOption): ?>
                                                    <option value="<?= htmlspecialchars($cityOption) ?>" <?= $selectedCity === $cityOption ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cityOption) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="atp-field-label">Stage ATP</label>
                                            <select name="stage" class="form-control atp-input">
                                                <option value="">Semua Stage</option>
                                                <?php foreach ($stageOptions as $stageOption): ?>
                                                    <option value="<?= htmlspecialchars($stageOption) ?>" <?= $selectedStage === $stageOption ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($stageOption) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-between atp-filter-actions">
                                        <a href="<?= base_url('ATP_MyRep') ?>" class="btn budget-btn budget-btn--ghost">Reset</a>
                                        <button type="submit" class="btn budget-btn budget-btn--primary">Terapkan Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php foreach ($summaryCards as $summaryCard): ?>
                    <div class="col-md">
                        <div class="small-box <?= $summaryCard['box'] ?> shadow-sm atp-summary-box <?= $summaryCard['class'] ?>">
                            <div class="inner">
                                <h3><?= number_format((float) $summaryCard['count'], 0, ',', '.') ?></h3>
                                <p><?= $summaryCard['label'] ?></p>
                                <p class="atp-summary-box__meta mb-0">HP <?= number_format((float) $summaryCard['hp'], 0, ',', '.') ?></p>
                            </div>
                            <div class="icon"><i class="<?= $summaryCard['icon'] ?>"></i></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="atp-toolbar">
                <div class="atp-toolbar__title">
                    <h3 class="mb-1">Monitoring ATP Cluster</h3>
                    <p class="mb-0 text-muted">Pantau timeline ATP, evidence punclist, dan kesiapan cluster menuju checklist dokument.</p>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm atp-table-card">
                <div class="card-header atp-section-header">
                    <div>
                        <h3 class="card-title mb-1">List Cluster ATP</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-atp-myrep" class="table table-bordered table-hover atp-monitor-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Cluster</th>
                                    <th>Regional</th>
                                    <th>Kota</th>
                                    <th>HP RFS</th>
                                    <th>Tanggal RFS</th>
                                    <th>Email ATP</th>
                                    <th>Tanggal ATP</th>
                                    <th>Status ATP</th>
                                    <th>Stage ATP</th>
                                    <th>Evidence</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clusterList as $index => $cluster): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></strong></td>
                                        <td><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?></td>
                                        <td class="text-right"><?= number_format((float) ($cluster['homepass'] ?? 0), 0, ',', '.') ?></td>
                                        <td><?= atp_myrep_format_date($cluster['tanggal_rfs'] ?? null) ?></td>
                                        <td><?= atp_myrep_format_date($cluster['email_atp_date'] ?? null) ?></td>
                                        <td><?= atp_myrep_format_date($cluster['actual_atp_date'] ?? null) ?></td>
                                        <td>
                                            <span class="badge badge-<?= atpMyrepBadgeClass($cluster['status_atp'] ?? '') ?>">
                                                <?= htmlspecialchars((string) (!empty($cluster['status_atp']) ? $cluster['status_atp'] : 'BELUM DISET')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= atpMyrepBadgeClass($cluster['stage_atp'] ?? '') ?>">
                                                <?= htmlspecialchars((string) ($cluster['stage_atp'] ?? '-')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="mb-2">
                                                <div class="small font-weight-bold text-dark">Record Punclist</div>
                                                <?php if (!empty($cluster['record_punclist_file_name'])): ?>
                                                    <div class="small text-muted"><?= htmlspecialchars((string) $cluster['record_punclist_file_name']) ?></div>
                                                    <a href="<?= base_url('ATP_MyRep/previewFile/' . (int) $cluster['record_punclist_file_id']) ?>" class="btn btn-xs btn-outline-dark mt-1" target="_blank">Preview</a>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">BELUM UPLOAD</span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="small font-weight-bold text-dark">BA Rectification</div>
                                                <?php if (!empty($cluster['ba_rectification_file_name'])): ?>
                                                    <div class="small text-muted"><?= htmlspecialchars((string) $cluster['ba_rectification_file_name']) ?></div>
                                                    <a href="<?= base_url('ATP_MyRep/previewFile/' . (int) $cluster['ba_rectification_file_id']) ?>" class="btn btn-xs btn-outline-dark mt-1" target="_blank">Preview</a>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">BELUM UPLOAD</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary js-edit-atp"
                                                data-toggle="modal"
                                                data-target="#modal-atp-update"
                                                data-cluster-id="<?= (int) $cluster['id_cluster'] ?>"
                                                data-cluster-name="<?= htmlspecialchars((string) ($cluster['cluster_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-email-atp-date="<?= htmlspecialchars((string) ($cluster['email_atp_date'] ?? ''), ENT_QUOTES) ?>"
                                                data-actual-atp-date="<?= htmlspecialchars((string) ($cluster['actual_atp_date'] ?? ''), ENT_QUOTES) ?>"
                                                data-status-atp="<?= htmlspecialchars((string) ($cluster['status_atp'] ?? ''), ENT_QUOTES) ?>"
                                                data-record-punclist-file="<?= htmlspecialchars((string) ($cluster['record_punclist_file_name'] ?? ''), ENT_QUOTES) ?>"
                                                data-ba-rectification-file="<?= htmlspecialchars((string) ($cluster['ba_rectification_file_name'] ?? ''), ENT_QUOTES) ?>">
                                                Update
                                            </button>
                                            <?php if (($cluster['stage_atp'] ?? '') === 'ATP DONE'): ?>
                                                <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . (int) $cluster['id_cluster']) ?>" class="btn btn-sm btn-outline-success mt-1">
                                                    Checklist
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">TOTAL HP RFS</th>
                                    <th class="text-right" id="atp-total-hp-rfs">0</th>
                                    <th colspan="7"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-atp-update" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content budget-modal atp-modal-shell">
            <form method="post" action="<?= base_url('ATP_MyRep/save') ?>" enctype="multipart/form-data">
                <div class="modal-header budget-modal__header">
                    <div>
                        <span class="budget-modal__eyebrow">ATP MyRep</span>
                        <h5 class="modal-title mb-1">Update ATP Cluster</h5>
                        <p class="mb-0 budget-modal__subtitle">Perbarui timeline ATP, status punclist atau done, serta upload evidence ATP dengan tampilan yang seragam.</p>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cluster_id" id="atp-cluster-id">
                    <input type="hidden" name="filter_city" value="<?= $selectedCity ?>">
                    <input type="hidden" name="filter_regional" value="<?= $selectedRegional ?>">
                    <input type="hidden" name="filter_stage" value="<?= $selectedStage ?>">

                    <div class="budget-form-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cluster</label>
                                    <input type="text" class="form-control" id="atp-cluster-name" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Email ATP</label>
                                    <input type="date" name="email_atp_date" id="atp-email-date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal ATP</label>
                                    <input type="date" name="actual_atp_date" id="atp-actual-date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status ATP</label>
                                    <select name="status_atp" id="atp-status" class="form-control">
                                        <option value="">Belum Dipilih</option>
                                        <option value="PUNCLIST">PUNCLIST</option>
                                        <option value="DONE">DONE</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="doc-modal-panel">
                            <div class="doc-modal-title">Record Punclist</div>
                            <p class="doc-modal-subtitle mb-2">Wajib tersedia jika status ATP diubah menjadi <code>PUNCLIST</code>.</p>
                            <div class="form-group">
                                <label>Upload File</label>
                                <input type="file" name="record_punclist_file" id="record-punclist-file" class="form-control">
                                <small class="text-muted d-block mt-1" id="record-punclist-existing">Belum ada file.</small>
                            </div>
                            <div class="form-group mb-0">
                                <label>Remark Record Punclist</label>
                                <textarea name="record_punclist_remark" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="doc-modal-panel">
                            <div class="doc-modal-title">BA Rectification</div>
                            <p class="doc-modal-subtitle mb-2">Wajib tersedia jika cluster berubah dari <code>PUNCLIST</code> ke <code>DONE</code>. Untuk direct <code>DONE</code>, file ini tidak wajib.</p>
                            <div class="form-group">
                                <label>Upload File</label>
                                <input type="file" name="ba_rectification_file" id="ba-rectification-file" class="form-control">
                                <small class="text-muted d-block mt-1" id="ba-rectification-existing">Belum ada file.</small>
                            </div>
                            <div class="form-group mb-0">
                                <label>Remark BA Rectification</label>
                                <textarea name="ba_rectification_remark" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary">Simpan ATP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .atp-filter-card,
    .atp-table-card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
    }

    .atp-section-header {
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.04), rgba(37, 99, 235, 0.08));
    }

    .atp-section-header .card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .atp-field-label {
        margin-bottom: 0.45rem;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }

    .atp-input {
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        min-height: 46px;
        padding: 0.7rem 0.9rem;
    }

    .atp-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .atp-filter-actions {
        gap: 12px;
    }

    .budget-btn {
        border-radius: 14px;
        padding: 0.7rem 1.1rem;
        font-weight: 700;
        border: 0;
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .budget-btn:hover,
    .budget-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #e2e8f0;
        color: #0f172a;
    }

    .atp-summary-box {
        border-radius: 20px;
        overflow: hidden;
        min-height: 170px;
    }

    .atp-summary-box .inner h3 {
        font-weight: 800;
    }

    .atp-summary-box__meta {
        opacity: 0.85;
        font-size: 0.82rem;
    }

    .atp-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .atp-monitor-table thead th {
        background: #0f172a;
        color: #f8fafc;
        border-color: #0f172a;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        vertical-align: middle;
    }

    .atp-monitor-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .budget-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
    }

    .budget-modal__header {
        border-bottom: 0;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
        color: #fff;
    }

    .budget-modal__eyebrow {
        display: inline-block;
        margin-bottom: 0.4rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.72);
    }

    .budget-modal__subtitle {
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.9rem;
    }

    .budget-modal .modal-body {
        padding: 1.5rem;
        background: #f8fafc;
    }

    .budget-modal__footer {
        border-top: 1px solid rgba(148, 163, 184, 0.16);
        padding: 1rem 1.5rem 1.35rem;
        background: #fff;
    }

    .budget-form-section {
        padding: 1.25rem;
        border-radius: 20px;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.14);
    }

    .atp-modal-shell .form-group label {
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }

    .atp-modal-shell .form-control,
    .atp-modal-shell textarea.form-control {
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        min-height: 46px;
    }

    .atp-modal-shell textarea.form-control {
        min-height: 96px;
    }

    .atp-modal-shell .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
    }

    .atp-modal-shell .form-control[readonly],
    .atp-modal-shell .form-control:disabled {
        background: #e2e8f0;
        color: #475569;
    }

    .doc-modal-panel {
        margin-top: 1rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));
    }

    .doc-modal-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
    }

    .doc-modal-subtitle {
        color: #64748b;
        font-size: 0.86rem;
    }

    .badge {
        padding: 0.45em 0.7em;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    @media (max-width: 991.98px) {
        .atp-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .atp-filter-actions {
            flex-direction: column;
        }

        .atp-filter-actions .btn,
        .budget-modal__footer .btn {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#table-atp-myrep').DataTable({
            responsive: true,
            autoWidth: false,
            order: [],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var parseNumber = function (value) {
                    if (typeof value === 'string') {
                        value = value.replace(/<[^>]*>/g, '');
                        value = value.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
                    }
                    var parsed = parseFloat(value);
                    return isNaN(parsed) ? 0 : parsed;
                };

                var totalHp = api
                    .column(4, { search: 'applied' })
                    .data()
                    .reduce(function (sum, val) {
                        return sum + parseNumber(val);
                    }, 0);

                $(api.column(4).footer()).html(
                    totalHp.toLocaleString('id-ID', { maximumFractionDigits: 0 })
                );
            }
        });

        function normalizeDateValue(value) {
            value = String(value || '').trim();
            if (value === '') {
                return '';
            }

            // Keep date input value valid: YYYY-MM-DD
            var isoMatch = value.match(/^(\d{4}-\d{2}-\d{2})/);
            if (isoMatch) {
                return isoMatch[1];
            }

            var slashMatch = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (slashMatch) {
                return slashMatch[3] + '-' + slashMatch[1] + '-' + slashMatch[2];
            }

            return value;
        }

        function readButtonData($button, key) {
            var dashed = 'data-' + key;
            var value = $button.attr(dashed);
            return typeof value === 'undefined' ? '' : value;
        }

        function fillAtpModal($button) {
            var clusterId = readButtonData($button, 'cluster-id');
            var clusterName = readButtonData($button, 'cluster-name');
            var emailAtpDate = normalizeDateValue(readButtonData($button, 'email-atp-date'));
            var actualAtpDate = normalizeDateValue(readButtonData($button, 'actual-atp-date'));
            var statusAtp = readButtonData($button, 'status-atp');
            var recordPunclistFile = readButtonData($button, 'record-punclist-file');
            var baRectificationFile = readButtonData($button, 'ba-rectification-file');

            $('#atp-cluster-id').val(clusterId);
            $('#atp-cluster-name').val(clusterName);
            $('#atp-email-date').val(emailAtpDate);
            $('#atp-actual-date').val(actualAtpDate);
            $('#atp-status').val(statusAtp);
            $('#record-punclist-existing').text(recordPunclistFile ? 'Existing file: ' + recordPunclistFile : 'Belum ada file.');
            $('#ba-rectification-existing').text(baRectificationFile ? 'Existing file: ' + baRectificationFile : 'Belum ada file.');
            $('#record-punclist-file').val('');
            $('#ba-rectification-file').val('');
        }

        // Works for static rows and rows redrawn by DataTables.
        $(document).on('click', '.js-edit-atp', function () {
            fillAtpModal($(this));
        });

        // Safety net when modal is shown via data-toggle / responsive child rows.
        $('#modal-atp-update').on('show.bs.modal', function (event) {
            var trigger = $(event.relatedTarget);
            if (trigger && trigger.length) {
                fillAtpModal(trigger);
            }
        });
    });
</script>
