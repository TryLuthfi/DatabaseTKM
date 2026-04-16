<?php
if (!function_exists('checklist_doc_format_date')) {
    function checklist_doc_format_date($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}

if (!function_exists('checklist_doc_aging_badge')) {
    function checklist_doc_aging_badge($aging)
    {
        if ($aging === null) {
            return 'secondary';
        }

        if ((int) $aging <= 0) {
            return 'success';
        }

        if ((int) $aging <= 3) {
            return 'warning';
        }

        return 'danger';
    }
}

if (!function_exists('checklist_doc_progress_percent')) {
    function checklist_doc_progress_percent($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;

        if ($required <= 0) {
            return 0;
        }

        return min(100, (int) round(($uploaded / $required) * 100));
    }
}

if (!function_exists('checklist_doc_progress_theme')) {
    function checklist_doc_progress_theme($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;

        if ($required <= 0 || $uploaded <= 0) {
            return [
                'box' => 'bg-light',
                'bar' => 'bg-secondary',
                'tone' => 'text-muted',
            ];
        }

        if ($uploaded >= $required) {
            return [
                'box' => 'bg-success-light',
                'bar' => 'bg-success-strong',
                'tone' => 'text-success-dark',
            ];
        }

        return [
            'box' => 'bg-warning-light',
            'bar' => 'bg-warning',
            'tone' => 'text-warning-dark',
        ];
    }
}

if (!function_exists('checklist_doc_status_badge')) {
    function checklist_doc_status_badge($status)
    {
        $status = strtoupper(trim((string) $status));

        switch ($status) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'UPLOADED':
            case 'WAITING WASPANG':
            case 'WAITING PLANNING':
            case 'WAITING TL':
            case 'WAITING LOGISTIK':
                return 'warning';
            case 'NOT UPLOADED':
            case 'NY':
                return 'secondary';
            default:
                return 'info';
        }
    }
}

if (!function_exists('checklist_doc_status_label')) {
    function checklist_doc_status_label($status)
    {
        $status = strtoupper(trim((string) $status));

        if ($status === 'UPLOADED') {
            return 'ON REVIEW';
        }

        return $status !== '' ? $status : '-';
    }
}

$totalCluster = count($clusterList);
$clusterDoneRfsBelumAtp = 0;
$clusterDoneAtpBelumDokument = 0;
$clusterNyAstri = 0;

foreach ($clusterList as $cluster) {
    if (!empty($cluster['tanggal_rfs']) && empty($cluster['actual_atp_date'])) {
        $clusterDoneRfsBelumAtp++;
    }

    if (!empty($cluster['actual_atp_date']) && empty($cluster['actual_submit_doc_date'])) {
        $clusterDoneAtpBelumDokument++;
    }

    if (empty($cluster['approved_astri_date'])) {
        $clusterNyAstri++;
    }
}
?>

<style>
    .checklist-board .small-box {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(31, 41, 55, 0.08);
    }

    .checklist-board .filter-card,
    .checklist-board .table-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .checklist-board .filter-card .card-header,
    .checklist-board .table-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border-bottom: 1px solid #e5e7eb;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .checklist-board .table thead th {
        background-color: #1f2937;
        color: #fff;
        border-color: #111827;
        font-size: 12px;
        letter-spacing: 0.02em;
        white-space: nowrap;
        vertical-align: middle;
    }

    .checklist-board .table td {
        vertical-align: top;
        font-size: 13px;
    }

    .cluster-identity {
        min-width: 260px;
    }

    .cluster-name {
        font-weight: 700;
        color: #111827;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .cluster-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cluster-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .timeline-stack {
        min-width: 170px;
        display: grid;
        gap: 8px;
    }

    .timeline-item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fff;
    }

    .timeline-label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .timeline-value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .doc-card {
        min-width: 220px;
        border-radius: 14px;
        padding: 12px;
        border: 1px solid #dbe4f0;
    }

    .doc-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
    }

    .doc-card-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 2px;
    }

    .doc-card-progress {
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
    }

    .doc-card-subtitle {
        font-size: 11px;
        color: #64748b;
    }

    .doc-progress-track {
        width: 100%;
        height: 8px;
        background: rgba(148, 163, 184, 0.2);
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .doc-progress-bar {
        height: 100%;
        border-radius: 999px;
    }

    .doc-status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .doc-status-item {
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 10px;
        padding: 8px 9px;
    }

    .doc-status-label {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 2px;
    }

    .doc-status-value {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .bg-light {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
    }

    .bg-warning-light {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    }

    .bg-success-light {
        background: linear-gradient(135deg, #e7f8ef 0%, #d0f1df 100%);
    }

    .bg-success-strong {
        background: linear-gradient(90deg, #15803d 0%, #16a34a 100%);
    }

    .text-success-dark {
        color: #166534;
    }

    .text-warning-dark {
        color: #9a3412;
    }

    .text-muted {
        color: #6b7280;
    }

    .action-stack .btn {
        min-width: 92px;
        margin-bottom: 6px;
    }

    .flat-cluster-name {
        font-weight: 700;
        color: #111827;
        min-width: 220px;
    }

    .item-note {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        color: #92400e;
        line-height: 1.4;
    }

    .remark-cell {
        min-width: 220px;
        white-space: normal;
    }

    .table-card .card-tools .btn-tool {
        color: #475569;
    }

    .item-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    .item-filter-group {
        min-width: 180px;
    }

    .item-filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }

    .item-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        margin-left: auto;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">CHECKLIST DOKUMENT</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid checklist-board">
            <div class="mb-3">
                <div class="btn-group">
                    <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-dark">Monitoring Cluster</a>
                    <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-outline-dark">Monitoring Mainfeeder</a>
                </div>
            </div>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalCluster ?></h3>
                            <p>FULL RFS</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $clusterDoneRfsBelumAtp ?></h3>
                            <p>NY ATP</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $clusterDoneAtpBelumDokument ?></h3>
                            <p>NY DOKUMENT</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $clusterNyAstri ?></h3>
                            <p>NY ASTRI</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-share-square"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline filter-card">
                <div class="card-header">
                    <h3 class="card-title">Filter Cluster</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Checklist_Dokument_MyRep') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Regional</label>
                                <select name="regional" class="form-control">
                                    <option value="">Semua Regional</option>
                                    <?php foreach ($regionalOptions as $regionalOption): ?>
                                        <option value="<?= $regionalOption ?>" <?= ($selectedRegional === $regionalOption) ? 'selected' : '' ?>>
                                            <?= $regionalOption ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Kota</label>
                                <select name="city" class="form-control">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= $cityOption ?>" <?= ($selectedCity === $cityOption) ? 'selected' : '' ?>>
                                            <?= $cityOption ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                                <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">List Cluster FULL RFS</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table-checklist-dokument" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Regional</th>
                                <th>Cluster</th>
                                <th>Timeline ATP</th>
                                <th>Timeline Dokument</th>
                                <th>CW ATP</th>
                                <th>FULL OPM</th>
                                <th>FULL RFS</th>
                                <th>Timeline Astri</th>
                                <th>ASTRI CW ATP</th>
                                <th>ASTRI FULL OPM</th>
                                <th>ASTRI FULL RFS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clusterList)): ?>
                                <tr>
                                    <td colspan="13" class="text-center">Belum ada cluster FULL RFS.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($clusterList as $cluster): ?>
                                    <?php
                                    $cwPercent = checklist_doc_progress_percent($cluster['doc_cw_atp_uploaded'], $cluster['doc_cw_atp_required']);
                                    $cwTheme = checklist_doc_progress_theme($cluster['doc_cw_atp_uploaded'], $cluster['doc_cw_atp_required']);
                                    $opmPercent = checklist_doc_progress_percent($cluster['doc_full_opm_uploaded'], $cluster['doc_full_opm_required']);
                                    $opmTheme = checklist_doc_progress_theme($cluster['doc_full_opm_uploaded'], $cluster['doc_full_opm_required']);
                                    $rfsPercent = checklist_doc_progress_percent($cluster['doc_rfs_uploaded'], $cluster['doc_rfs_required']);
                                    $rfsTheme = checklist_doc_progress_theme($cluster['doc_rfs_uploaded'], $cluster['doc_rfs_required']);
                                    $astriCwPercent = checklist_doc_progress_percent($cluster['astri_doc_cw_atp_submitted'], $cluster['doc_cw_atp_required']);
                                    $astriCwTheme = checklist_doc_progress_theme($cluster['astri_doc_cw_atp_submitted'], $cluster['doc_cw_atp_required']);
                                    $astriOpmPercent = checklist_doc_progress_percent($cluster['astri_doc_full_opm_submitted'], $cluster['doc_full_opm_required']);
                                    $astriOpmTheme = checklist_doc_progress_theme($cluster['astri_doc_full_opm_submitted'], $cluster['doc_full_opm_required']);
                                    $astriRfsPercent = checklist_doc_progress_percent($cluster['astri_doc_rfs_submitted'], $cluster['doc_rfs_required']);
                                    $astriRfsTheme = checklist_doc_progress_theme($cluster['astri_doc_rfs_submitted'], $cluster['doc_rfs_required']);
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= !empty($cluster['regional_name']) ? $cluster['regional_name'] : '-' ?></td>
                                        <td>
                                            <div class="cluster-identity">
                                                <div class="cluster-name"><?= $cluster['cluster_name'] ?></div>
                                                <div class="cluster-meta">
                                                    <span class="cluster-chip"><?= $cluster['city_name'] ?></span>
                                                    <span class="cluster-chip">HP <?= number_format((float) $cluster['homepass'], 0, ',', '.') ?></span>
                                                    <span class="cluster-chip">RFS <?= checklist_doc_format_date($cluster['tanggal_rfs']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Plan ATP</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['plan_atp_date']) ?></div>
                                                    <?php if ($cluster['aging_atp_days'] === null): ?>
                                                        <span class="badge badge-secondary">Aging -</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-<?= checklist_doc_aging_badge($cluster['aging_atp_days']) ?>">
                                                            Aging <?= (int) $cluster['aging_atp_days'] ?> hari
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Realisasi ATP</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['actual_atp_date']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Plan Dokument</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['plan_submit_doc_date']) ?></div>
                                                    <?php if ($cluster['aging_doc_days'] === null): ?>
                                                        <span class="badge badge-secondary">Aging -</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-<?= checklist_doc_aging_badge($cluster['aging_doc_days']) ?>">
                                                            Aging <?= (int) $cluster['aging_doc_days'] ?> hari
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Realisasi Dokument</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['actual_submit_doc_date']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $cwTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">CW ATP</div>
                                                        <div class="doc-card-subtitle">Realisasi <?= (int) $cluster['doc_cw_atp_uploaded'] ?>/<?= (int) $cluster['doc_cw_atp_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $cwTheme['tone'] ?>"><?= $cwPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $cwTheme['bar'] ?>" style="width: <?= $cwPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_cw_atp_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_cw_atp_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_cw_atp_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_cw_atp_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $opmTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">FULL OPM</div>
                                                        <div class="doc-card-subtitle">Realisasi <?= (int) $cluster['doc_full_opm_uploaded'] ?>/<?= (int) $cluster['doc_full_opm_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $opmTheme['tone'] ?>"><?= $opmPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $opmTheme['bar'] ?>" style="width: <?= $opmPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_full_opm_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_full_opm_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_full_opm_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_full_opm_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $rfsTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">FULL RFS</div>
                                                        <div class="doc-card-subtitle">Realisasi <?= (int) $cluster['doc_rfs_uploaded'] ?>/<?= (int) $cluster['doc_rfs_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $rfsTheme['tone'] ?>"><?= $rfsPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $rfsTheme['bar'] ?>" style="width: <?= $rfsPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_rfs_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_rfs_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_rfs_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['doc_rfs_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Submit Astri</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['submit_astri_date']) ?></div>
                                                </div>
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Approved Astri</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($cluster['approved_astri_date']) ?></div>
                                                    <?php
                                                    $astriAgingDays = null;
                                                    if (!empty($cluster['submit_astri_date'])) {
                                                        $start = new DateTime($cluster['submit_astri_date']);
                                                        $end = new DateTime(!empty($cluster['approved_astri_date']) ? $cluster['approved_astri_date'] : date('Y-m-d'));
                                                        $invert = $start > $end ? -1 : 1;
                                                        $diff = $start->diff($end);
                                                        $astriAgingDays = $diff->days * $invert;
                                                    }
                                                    ?>
                                                    <?php if ($astriAgingDays === null): ?>
                                                        <span class="badge badge-secondary">Aging -</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-<?= checklist_doc_aging_badge($astriAgingDays) ?>">
                                                            Aging <?= (int) $astriAgingDays ?> hari
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriCwTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">ASTRI CW ATP</div>
                                                        <div class="doc-card-subtitle">Submit <?= (int) $cluster['astri_doc_cw_atp_submitted'] ?>/<?= (int) $cluster['doc_cw_atp_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $astriCwTheme['tone'] ?>"><?= $astriCwPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $astriCwTheme['bar'] ?>" style="width: <?= $astriCwPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_cw_atp_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_cw_atp_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_cw_atp_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_cw_atp_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriOpmTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">ASTRI FULL OPM</div>
                                                        <div class="doc-card-subtitle">Submit <?= (int) $cluster['astri_doc_full_opm_submitted'] ?>/<?= (int) $cluster['doc_full_opm_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $astriOpmTheme['tone'] ?>"><?= $astriOpmPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $astriOpmTheme['bar'] ?>" style="width: <?= $astriOpmPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_full_opm_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_full_opm_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_full_opm_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_full_opm_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriRfsTheme['box'] ?>">
                                                <div class="doc-card-head">
                                                    <div>
                                                        <div class="doc-card-title">ASTRI FULL RFS</div>
                                                        <div class="doc-card-subtitle">Submit <?= (int) $cluster['astri_doc_rfs_submitted'] ?>/<?= (int) $cluster['doc_rfs_required'] ?></div>
                                                    </div>
                                                    <div class="doc-card-progress <?= $astriRfsTheme['tone'] ?>"><?= $astriRfsPercent ?>%</div>
                                                </div>
                                                <div class="doc-progress-track">
                                                    <div class="doc-progress-bar <?= $astriRfsTheme['bar'] ?>" style="width: <?= $astriRfsPercent ?>%;"></div>
                                                </div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">NY</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_rfs_ny'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">On Review</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_rfs_on_review'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Reject</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_rfs_rejected'] ?></span>
                                                    </div>
                                                    <div class="doc-status-item">
                                                        <span class="doc-status-label">Approved</span>
                                                        <span class="doc-status-value"><?= (int) $cluster['astri_doc_rfs_approved'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-stack">
                                                <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . (int) $cluster['id_cluster']) ?>"
                                                    class="btn btn-primary btn-sm">Detail</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">Monitoring Item Dokumen</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="item-filter-bar">
                        <div class="item-filter-group">
                            <label for="item-filter-regional">Regional</label>
                            <select id="item-filter-regional" class="form-control form-control-sm">
                                <option value="">Semua Regional</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-city">Kota</label>
                            <select id="item-filter-city" class="form-control form-control-sm">
                                <option value="">Semua Kota</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-internal-status">Status Internal</label>
                            <select id="item-filter-internal-status" class="form-control form-control-sm">
                                <option value="">Semua Status Internal</option>
                                <option value="NOT UPLOADED">NOT UPLOADED</option>
                                <option value="ON REVIEW">ON REVIEW</option>
                                <option value="REJECTED">REJECTED</option>
                                <option value="APPROVED">APPROVED</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-astri-status">Status Astri</label>
                            <select id="item-filter-astri-status" class="form-control form-control-sm">
                                <option value="">Semua Status Astri</option>
                                <option value="NY">NY</option>
                                <option value="ON REVIEW">ON REVIEW</option>
                                <option value="WAITING WASPANG">WAITING WASPANG</option>
                                <option value="WAITING PLANNING">WAITING PLANNING</option>
                                <option value="WAITING TL">WAITING TL</option>
                                <option value="WAITING LOGISTIK">WAITING LOGISTIK</option>
                                <option value="REJECTED">REJECTED</option>
                                <option value="APPROVED">APPROVED</option>
                            </select>
                        </div>
                        <div class="item-filter-actions">
                            <button type="button" id="btn-export-item-excel" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel mr-1"></i> Download Excel
                            </button>
                        </div>
                    </div>
                    <table id="table-checklist-item" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th>Cluster</th>
                                <th>Scope</th>
                                <th>SOW</th>
                                <th>Dokumen</th>
                                <th>Status Internal</th>
                                <th>Remark Internal</th>
                                <th>Status Astri</th>
                                <th>Remark Astri</th>
                                <th>Uploaded At</th>
                                <th>Reviewed At</th>
                                <th>Approved At</th>
                                <th>Submit Astri</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documentItemList)): ?>
                                <tr>
                                    <td colspan="16" class="text-center">Belum ada item dokumen.</td>
                                </tr>
                            <?php else: ?>
                                <?php $itemNo = 1; ?>
                                <?php foreach ($documentItemList as $item): ?>
                                    <tr>
                                        <td><?= $itemNo++ ?></td>
                                        <td><?= !empty($item['regional_name']) ? $item['regional_name'] : '-' ?></td>
                                        <td><?= !empty($item['city_name']) ? $item['city_name'] : '-' ?></td>
                                        <td>
                                            <div class="flat-cluster-name"><?= !empty($item['cluster_name']) ? $item['cluster_name'] : '-' ?></div>
                                        </td>
                                        <td><?= !empty($item['scope_type']) ? $item['scope_type'] : '-' ?></td>
                                        <td><?= !empty($item['sow_type']) ? $item['sow_type'] : '-' ?></td>
                                        <td>
                                            <strong><?= !empty($item['doc_name']) ? $item['doc_name'] : '-' ?></strong>
                                            <?php if (!empty($item['doc_requirement_note'])): ?>
                                                <span class="item-note"><?= $item['doc_requirement_note'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $internalStatus = checklist_doc_status_label($item['status_file'] ?? 'NOT UPLOADED'); ?>
                                            <span class="badge badge-<?= checklist_doc_status_badge($item['status_file'] ?? 'NOT UPLOADED') ?>"><?= $internalStatus ?></span>
                                        </td>
                                        <td class="remark-cell"><?= !empty($item['remark']) ? nl2br(htmlspecialchars($item['remark'])) : '-' ?></td>
                                        <td>
                                            <?php $astriStatus = checklist_doc_status_label($item['astri_status'] ?? 'NY'); ?>
                                            <span class="badge badge-<?= checklist_doc_status_badge($item['astri_status'] ?? 'NY') ?>"><?= $astriStatus ?></span>
                                        </td>
                                        <td class="remark-cell"><?= !empty($item['astri_remark']) ? nl2br(htmlspecialchars($item['astri_remark'])) : '-' ?></td>
                                        <td><?= checklist_doc_format_date($item['uploaded_at'] ?? null) ?></td>
                                        <td><?= checklist_doc_format_date($item['reviewed_at'] ?? null) ?></td>
                                        <td><?= checklist_doc_format_date($item['approved_at'] ?? null) ?></td>
                                        <td><?= checklist_doc_format_date($item['astri_submitted_date'] ?? null) ?></td>
                                        <td>
                                            <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . (int) $item['id_cluster']) ?>"
                                                class="btn btn-primary btn-sm">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function() {
        $('#table-checklist-dokument').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "scrollX": true,
            "pageLength": 10,
            "lengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ]
        });

        var itemTable = $('#table-checklist-item').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "scrollX": true,
            "pageLength": 10,
            "lengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ]
        });

        function escapeRegex(value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function populateFilterOptions(columnIndex, selector) {
            var select = $(selector);
            var values = [];

            itemTable.column(columnIndex).data().each(function(value) {
                var text = $('<div>').html(value).text().trim();
                if (text !== '' && values.indexOf(text) === -1) {
                    values.push(text);
                }
            });

            values.sort();
            $.each(values, function(_, value) {
                select.append($('<option>', {
                    value: value,
                    text: value
                }));
            });
        }

        populateFilterOptions(1, '#item-filter-regional');
        populateFilterOptions(2, '#item-filter-city');

        $('#item-filter-regional').on('change', function() {
            var value = $.fn.dataTable.util.escapeRegex($(this).val());
            itemTable.column(1).search(value ? '^' + value + '$' : '', true, false).draw();
        });

        $('#item-filter-city').on('change', function() {
            var value = $.fn.dataTable.util.escapeRegex($(this).val());
            itemTable.column(2).search(value ? '^' + value + '$' : '', true, false).draw();
        });

        $('#item-filter-internal-status').on('change', function() {
            var value = $(this).val();
            itemTable.column(7).search(value ? escapeRegex(value) : '', true, false).draw();
        });

        $('#item-filter-astri-status').on('change', function() {
            var value = $(this).val();
            itemTable.column(9).search(value ? escapeRegex(value) : '', true, false).draw();
        });

        $('#btn-export-item-excel').on('click', function() {
            var exportColumnIndexes = [];
            var headers = [];
            $('#table-checklist-item thead th').each(function(index) {
                var headerText = $(this).text().trim();
                if (headerText !== 'Aksi') {
                    exportColumnIndexes.push(index);
                    headers.push(headerText);
                }
            });

            var rows = itemTable.rows({
                search: 'applied',
                order: 'applied'
            }).nodes();

            var html = '<table border="1"><thead><tr>';
            $.each(headers, function(_, header) {
                html += '<th>' + $('<div>').text(header).html() + '</th>';
            });
            html += '</tr></thead><tbody>';

            if (!rows.length) {
                html += '<tr><td colspan="' + headers.length + '">Tidak ada data.</td></tr>';
            } else {
                $(rows).each(function() {
                    html += '<tr>';
                    var cells = $(this).find('td');
                    $.each(exportColumnIndexes, function(_, columnIndex) {
                        var cell = cells.eq(columnIndex).clone();
                        cell.find('.item-note').remove();
                        var cellText = cell.text().trim().replace(/\s+/g, ' ');
                        html += '<td>' + $('<div>').text(cellText).html() + '</td>';
                    });
                    html += '</tr>';
                });
            }

            html += '</tbody></table>';

            var blob = new Blob(
                ['\ufeff' + html], {
                    type: 'application/vnd.ms-excel'
                }
            );

            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'monitoring_item_dokumen_' + new Date().toISOString().slice(0, 10) + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });
    });
</script>
