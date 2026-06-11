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

if (!function_exists('checklist_doc_focus_progress_class')) {
    function checklist_doc_focus_progress_class($percent)
    {
        $percent = (int) $percent;
        if ($percent >= 100) {
            return 'is-complete';
        }

        if ($percent > 0) {
            return 'is-progress';
        }

        return 'is-empty';
    }
}

if (!function_exists('checklist_doc_focus_progress_cell')) {
    function checklist_doc_focus_progress_cell(array $cluster, $doneKey, $requiredKey)
    {
        $done = (int) ($cluster[$doneKey] ?? 0);
        $required = (int) ($cluster[$requiredKey] ?? 0);
        $percent = checklist_doc_progress_percent($done, $required);
        $className = checklist_doc_focus_progress_class($percent);

        return '<div class="focus-progress ' . $className . '">'
            . '<div class="focus-progress-head">'
            . '<span>' . $percent . '%</span>'
            . '<small>' . $done . '/' . $required . '</small>'
            . '</div>'
            . '<div class="focus-progress-track"><span style="width: ' . $percent . '%;"></span></div>'
            . '</div>';
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

        if ($status === 'NY') {
            return 'NOT UPLOADED';
        }

        return $status !== '' ? $status : '-';
    }
}

$summary = isset($dashboardSummary) && is_array($dashboardSummary) ? $dashboardSummary : [];
$canHapus = isset($this->myrepAccess) ? $this->myrepAccess->hasPermission('Checklist_Dokument_MyRep', 'HAPUS') : true;
$isClusterTableFocus = !empty($isClusterTableFocus);
$totalCluster = (int) ($summary['totalCluster'] ?? count($clusterList));
$clusterDoneRfsBelumAtp = (int) ($summary['clusterDoneRfsBelumAtp'] ?? 0);
$clusterDoneAtpBelumDokument = (int) ($summary['clusterDoneAtpBelumDokument'] ?? 0);
$clusterNyAstri = (int) ($summary['clusterNyAstri'] ?? 0);
$internalStatusSummary = isset($summary['internalStatusSummary']) && is_array($summary['internalStatusSummary']) ? $summary['internalStatusSummary'] : ['NY' => 0, 'ON REVIEW' => 0, 'REJECTED' => 0, 'APPROVED' => 0];
$astriStatusSummary = isset($summary['astriStatusSummary']) && is_array($summary['astriStatusSummary']) ? $summary['astriStatusSummary'] : ['NY' => 0, 'ON REVIEW' => 0, 'REJECTED' => 0, 'APPROVED' => 0];
$projectOpnameFlowSummary = isset($summary['projectOpnameFlowSummary']) && is_array($summary['projectOpnameFlowSummary']) ? $summary['projectOpnameFlowSummary'] : ['WAITING WASPANG' => 0, 'WAITING PLANNING' => 0, 'WAITING TL' => 0, 'WAITING LOGISTIK' => 0];
?>

<style>
    #globalLoader {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.92);
        display: none !important;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loader-content {
        text-align: center;
        color: #1f2937;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #e5e7eb;
        border-top: 5px solid #2563eb;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .checklist-page-content {
        visibility: visible;
    }

    .checklist-page-content.is-ready {
        visibility: visible;
        animation: checklistFadeIn .25s ease-in-out;
    }

    @keyframes checklistFadeIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

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

    .checklist-board .cluster-focus-table {
        min-width: 1040px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .checklist-board .cluster-focus-table thead th {
        background-color: #f4f2ed;
        color: #111827;
        border-color: #d8d8d8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
    }

    .checklist-board .cluster-focus-table td {
        background: #fff;
        border-color: #e5e7eb;
        color: #111827;
        vertical-align: middle;
    }

    .cluster-focus-table .focus-no {
        width: 46px;
        text-align: center;
    }

    .cluster-focus-table .focus-cluster-cell {
        min-width: 190px;
        max-width: 230px;
    }

    .cluster-focus-table .focus-timeline-cell {
        min-width: 110px;
    }

    .cluster-focus-table .focus-progress-cell {
        min-width: 130px;
    }

    .cluster-focus-table .cluster-name-link {
        display: inline-block;
        color: #111827;
        font-weight: 700;
        line-height: 1.22;
        white-space: normal;
        word-break: break-word;
    }

    .cluster-focus-table .focus-cluster-meta {
        margin-top: 4px;
        color: #111827;
        font-size: 12px;
        line-height: 1.25;
    }

    .cluster-focus-table .focus-timeline {
        color: #111827;
        font-size: 12px;
        line-height: 1.25;
    }

    .cluster-focus-table .focus-timeline strong {
        display: inline-block;
        min-width: 56px;
        font-weight: 700;
    }

    .focus-progress {
        width: 100%;
        min-width: 105px;
    }

    .focus-progress-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .focus-progress-head small {
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
    }

    .focus-progress-track {
        height: 5px;
        width: 100%;
        background: #f1f0eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .focus-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .focus-progress.is-complete .focus-progress-head {
        color: #059669;
    }

    .focus-progress.is-complete .focus-progress-track span {
        background: #1f9d6d;
    }

    .focus-progress.is-progress .focus-progress-head {
        color: #f59e0b;
    }

    .focus-progress.is-progress .focus-progress-track span {
        background: #f59e0b;
    }

    .focus-progress.is-empty .focus-progress-head {
        color: #111827;
    }

    .focus-progress.is-empty .focus-progress-track span {
        background: #d1d5db;
    }

    .cluster-identity {
        min-width: 260px;
    }

    .cluster-name {
        font-weight: 700;
        color: #111827;
        font-size: 16px;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .cluster-name-link {
        color: #111827;
        text-decoration: none;
        transition: color .15s ease;
    }

    .cluster-name-link:hover {
        color: #2563eb;
        text-decoration: underline;
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
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 10px;
        padding: 8px 9px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        border-left-width: 4px;
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

    .doc-status-grid .doc-status-item:nth-child(1) {
        border-left-color: #94a3b8;
    }

    .doc-status-grid .doc-status-item:nth-child(1) .doc-status-label,
    .doc-status-grid .doc-status-item:nth-child(1) .doc-status-value {
        color: #475569;
    }

    .doc-status-grid .doc-status-item:nth-child(2) {
        border-left-color: #f59e0b;
    }

    .doc-status-grid .doc-status-item:nth-child(2) .doc-status-label,
    .doc-status-grid .doc-status-item:nth-child(2) .doc-status-value {
        color: #9a3412;
    }

    .doc-status-grid .doc-status-item:nth-child(3) {
        border-left-color: #ef4444;
    }

    .doc-status-grid .doc-status-item:nth-child(3) .doc-status-label,
    .doc-status-grid .doc-status-item:nth-child(3) .doc-status-value {
        color: #b91c1c;
    }

    .doc-status-grid .doc-status-item:nth-child(4) {
        border-left-color: #22c55e;
    }

    .doc-status-grid .doc-status-item:nth-child(4) .doc-status-label,
    .doc-status-grid .doc-status-item:nth-child(4) .doc-status-value {
        color: #166534;
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

    .sla-separator-left {
        border-left: 4px solid #1f2937 !important;
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

    .cluster-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    .cluster-filter-group {
        min-width: 220px;
    }

    .cluster-filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }

    .cluster-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        margin-left: auto;
    }

    .status-summary-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .status-summary-card .card-header {
        color: #fff;
        font-weight: 700;
        border-bottom: 0;
    }

    .status-summary-card .card-body {
        padding: 0;
    }

    .status-summary-card.internal .card-header {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
    }

    .status-summary-card.astri .card-header {
        background: linear-gradient(135deg, #0f766e, #0d9488);
    }

    .status-summary-card.opname .card-header {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    }

    .status-summary-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .status-summary-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid #edf2f7;
        font-size: 13px;
    }

    .status-summary-list li.is-clickable {
        cursor: pointer;
        transition: background-color .15s ease;
    }

    .status-summary-list li.is-clickable:hover {
        background: linear-gradient(135deg, #eef4ff 0%, #dbeafe 100%);
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.18);
    }

    .status-summary-list li:last-child {
        border-bottom: 0;
    }

    .status-summary-label {
        color: #1f2937;
        font-weight: 600;
    }

    .status-summary-count {
        min-width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        padding: 0 8px;
    }

    .status-summary-count.primary {
        background: #2563eb;
    }

    .status-summary-count.success {
        background: #16a34a;
    }

    .status-summary-count.warning {
        background: #f59e0b;
    }

    .status-summary-count.danger {
        background: #dc2626;
    }

    .status-summary-count.dark {
        background: #475569;
    }
</style>

<div id="globalLoader">
    <div class="loader-content">
        <div class="spinner"></div>
        <div>Loading checklist dokument...</div>
    </div>
</div>

<div id="checklistPageContent" class="checklist-page-content">
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
                    <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn <?= $isClusterTableFocus ? 'btn-dark' : 'btn-outline-dark' ?>">Monitoring Cluster</a>
                    <a href="<?= base_url('Checklist_Dokument_MyRep/old') ?>" class="btn <?= $isClusterTableFocus ? 'btn-outline-dark' : 'btn-dark' ?>">Tampilan Lama</a>
                    <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-outline-dark">Monitoring Mainfeeder</a>
                </div>
            </div>
            <?php if (empty($atpSchemaReady)): ?>
                <div class="alert alert-warning">
                    Mode checklist ATP DONE akan aktif setelah query di file <code>db/patch_myrep_atp_20260508.sql</code> dijalankan.
                </div>
            <?php endif; ?>
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
                            <h3 data-dashboard-count="totalCluster"><?= $totalCluster ?></h3>
                            <p>ATP DONE</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 data-dashboard-count="clusterDoneAtpBelumDokument"><?= $clusterDoneAtpBelumDokument ?></h3>
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
                            <h3 data-dashboard-count="clusterNyAstri"><?= $clusterNyAstri ?></h3>
                            <p>NY ASTRI</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-share-square"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 data-dashboard-count="astriDone"><?= max(0, $totalCluster - $clusterNyAstri) ?></h3>
                            <p>ASTRI DONE</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card status-summary-card internal">
                        <div class="card-header">Status Internal</div>
                        <div class="card-body">
                            <ul class="status-summary-list">
                                <li class="is-clickable quick-item-filter" data-filter-type="internal" data-filter-value="NOT UPLOADED">
                                    <span class="status-summary-label">Not Uploaded</span>
                                    <span class="status-summary-count dark" data-summary-section="internalStatusSummary" data-summary-key="NY"><?= (int) $internalStatusSummary['NY'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="internal" data-filter-value="ON REVIEW">
                                    <span class="status-summary-label">On Review</span>
                                    <span class="status-summary-count warning" data-summary-section="internalStatusSummary" data-summary-key="ON REVIEW"><?= (int) $internalStatusSummary['ON REVIEW'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="internal" data-filter-value="REJECTED">
                                    <span class="status-summary-label">Rejected</span>
                                    <span class="status-summary-count danger" data-summary-section="internalStatusSummary" data-summary-key="REJECTED"><?= (int) $internalStatusSummary['REJECTED'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="internal" data-filter-value="APPROVED">
                                    <span class="status-summary-label">Approved</span>
                                    <span class="status-summary-count success" data-summary-section="internalStatusSummary" data-summary-key="APPROVED"><?= (int) $internalStatusSummary['APPROVED'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card status-summary-card astri">
                        <div class="card-header">Status ASTRI</div>
                        <div class="card-body">
                            <ul class="status-summary-list">
                                <li class="is-clickable quick-item-filter" data-filter-type="astri" data-filter-value="NOT UPLOADED">
                                    <span class="status-summary-label">Not Uploaded</span>
                                    <span class="status-summary-count dark" data-summary-section="astriStatusSummary" data-summary-key="NY"><?= (int) $astriStatusSummary['NY'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="astri" data-filter-value="ON REVIEW">
                                    <span class="status-summary-label">On Review</span>
                                    <span class="status-summary-count warning" data-summary-section="astriStatusSummary" data-summary-key="ON REVIEW"><?= (int) $astriStatusSummary['ON REVIEW'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="astri" data-filter-value="REJECTED">
                                    <span class="status-summary-label">Rejected</span>
                                    <span class="status-summary-count danger" data-summary-section="astriStatusSummary" data-summary-key="REJECTED"><?= (int) $astriStatusSummary['REJECTED'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="astri" data-filter-value="APPROVED">
                                    <span class="status-summary-label">Approved</span>
                                    <span class="status-summary-count success" data-summary-section="astriStatusSummary" data-summary-key="APPROVED"><?= (int) $astriStatusSummary['APPROVED'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card status-summary-card opname">
                        <div class="card-header">Project Opname Flow</div>
                        <div class="card-body">
                            <ul class="status-summary-list">
                                <li class="is-clickable quick-item-filter" data-filter-type="project-opname" data-filter-value="WAITING WASPANG">
                                    <span class="status-summary-label">Waiting Waspang</span>
                                    <span class="status-summary-count warning" data-summary-section="projectOpnameFlowSummary" data-summary-key="WAITING WASPANG"><?= (int) $projectOpnameFlowSummary['WAITING WASPANG'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="project-opname" data-filter-value="WAITING PLANNING">
                                    <span class="status-summary-label">Waiting Planning</span>
                                    <span class="status-summary-count primary" data-summary-section="projectOpnameFlowSummary" data-summary-key="WAITING PLANNING"><?= (int) $projectOpnameFlowSummary['WAITING PLANNING'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="project-opname" data-filter-value="WAITING TL">
                                    <span class="status-summary-label">Waiting TL</span>
                                    <span class="status-summary-count primary" data-summary-section="projectOpnameFlowSummary" data-summary-key="WAITING TL"><?= (int) $projectOpnameFlowSummary['WAITING TL'] ?></span>
                                </li>
                                <li class="is-clickable quick-item-filter" data-filter-type="project-opname" data-filter-value="WAITING LOGISTIK">
                                    <span class="status-summary-label">Waiting Logistik</span>
                                    <span class="status-summary-count primary" data-summary-section="projectOpnameFlowSummary" data-summary-key="WAITING LOGISTIK"><?= (int) $projectOpnameFlowSummary['WAITING LOGISTIK'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card table-card" id="cluster-monitor-card">
                <div class="card-header">
                    <h3 class="card-title">List Cluster ATP DONE</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url($isClusterTableFocus ? 'Checklist_Dokument_MyRep' : 'Checklist_Dokument_MyRep/old') ?>" id="cluster-filter-form">
                        <div class="cluster-filter-bar">
                            <div class="cluster-filter-group">
                                <label>Regional</label>
                                <select name="regional" id="cluster-filter-regional" class="form-control form-control-sm">
                                    <option value="">Semua Regional</option>
                                    <?php foreach ($regionalOptions as $regionalOption): ?>
                                        <option value="<?= $regionalOption ?>" <?= ($selectedRegional === $regionalOption) ? 'selected' : '' ?>>
                                            <?= $regionalOption ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cluster-filter-group">
                                <label>Kota</label>
                                <select name="city" id="cluster-filter-city" class="form-control form-control-sm">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= $cityOption ?>" <?= ($selectedCity === $cityOption) ? 'selected' : '' ?>>
                                            <?= $cityOption ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cluster-filter-actions">
                                <a href="<?= base_url($isClusterTableFocus ? 'Checklist_Dokument_MyRep' : 'Checklist_Dokument_MyRep/old') ?>" class="btn btn-default btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>
                    <?php if ($isClusterTableFocus): ?>
                        <table id="table-checklist-dokument-focus" class="table table-bordered table-hover cluster-focus-table">
                            <thead>
                                <tr>
                                    <th class="focus-no">No</th>
                                    <th>Cluster</th>
                                    <th>Timeline ATP</th>
                                    <th>CW ATP</th>
                                    <th>Full OPM</th>
                                    <th>Full RFS</th>
                                    <th>ASTRI CW ATP</th>
                                    <th>ASTRI Full OPM</th>
                                    <th>ASTRI Full RFS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($clusterList)): ?>
                                    <?php foreach ($clusterList as $index => $cluster): ?>
                                        <?php
                                        $clusterId = (int) ($cluster['id_cluster'] ?? 0);
                                        $homepass = number_format((float) ($cluster['homepass'] ?? 0), 0, ',', '.');
                                        ?>
                                        <tr>
                                            <td class="focus-no"><?= $index + 1 ?></td>
                                            <td class="focus-cluster-cell">
                                                <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . $clusterId) ?>" class="cluster-name-link">
                                                    <?= html_escape($cluster['cluster_name'] ?? '-') ?>
                                                </a>
                                                <div class="focus-cluster-meta">
                                                    <?= html_escape($cluster['city_name'] ?? '-') ?> - HP <?= html_escape($homepass) ?><br>
                                                    RFS <?= html_escape(checklist_doc_format_date($cluster['tanggal_rfs'] ?? null)) ?>
                                                </div>
                                            </td>
                                            <td class="focus-timeline-cell">
                                                <div class="focus-timeline">
                                                    <div><strong>Plan:</strong> <?= html_escape(checklist_doc_format_date($cluster['plan_atp_date'] ?? null)) ?></div>
                                                    <div><strong>Realisasi:</strong> <?= html_escape(checklist_doc_format_date($cluster['actual_atp_date'] ?? null)) ?></div>
                                                </div>
                                            </td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'doc_cw_atp_uploaded', 'doc_cw_atp_required') ?></td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'doc_full_opm_uploaded', 'doc_full_opm_required') ?></td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'doc_rfs_uploaded', 'doc_rfs_required') ?></td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'astri_doc_cw_atp_submitted', 'doc_cw_atp_required') ?></td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'astri_doc_full_opm_submitted', 'doc_full_opm_required') ?></td>
                                            <td class="focus-progress-cell"><?= checklist_doc_focus_progress_cell($cluster, 'astri_doc_rfs_submitted', 'doc_rfs_required') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
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
                            <?php if (!empty($clusterList) && !empty($renderClusterRows)): ?>
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
                                                <div class="cluster-name">
                                                    <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . (int) $cluster['id_cluster']) ?>" class="cluster-name-link">
                                                        <?= $cluster['cluster_name'] ?>
                                                    </a>
                                                </div>
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
                                        <td class="sla-separator-left">
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
                                                <?php if ($canHapus): ?>
                                                    <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini dari ATP/RFS beserta seluruh flow MyRep sebelumnya?');">
                                                        <input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_cluster'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card table-card" id="item-monitor-card">
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
                                <?php foreach ($regionalOptions as $regionalOption): ?>
                                    <option value="<?= htmlspecialchars($regionalOption, ENT_QUOTES) ?>"><?= htmlspecialchars($regionalOption, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-city">Kota</label>
                            <select id="item-filter-city" class="form-control form-control-sm">
                                <option value="">Semua Kota</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-cluster">Cluster</label>
                            <select id="item-filter-cluster" class="form-control form-control-sm">
                                <option value="">Semua Cluster</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-scope">Scope</label>
                            <select id="item-filter-scope" class="form-control form-control-sm">
                                <option value="">Semua Scope</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-sow">SOW</label>
                            <select id="item-filter-sow" class="form-control form-control-sm">
                                <option value="">Semua SOW</option>
                            </select>
                        </div>
                        <div class="item-filter-group">
                            <label for="item-filter-doc">Dokumen</label>
                            <select id="item-filter-doc" class="form-control form-control-sm">
                                <option value="">Semua Dokumen</option>
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
                                <option value="NOT UPLOADED">NOT UPLOADED</option>
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
                                <th>Verification By</th>
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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Failsafe: pastikan loader tidak pernah menggantung permanen.
    (function() {
        function forceShowChecklistContent() {
            try {
                $('#checklistPageContent').addClass('is-ready');
                $('#globalLoader').fadeOut(200);
            } catch (e) {
                var content = document.getElementById('checklistPageContent');
                var loader = document.getElementById('globalLoader');
                if (content) {
                    content.classList.add('is-ready');
                    content.style.visibility = 'visible';
                }
                if (loader) {
                    loader.style.display = 'none';
                }
            }
        }

        window.setTimeout(forceShowChecklistContent, 1200);
        window.addEventListener('load', forceShowChecklistContent);
    })();

    $(function() {
        function showChecklistContent() {
            $('#checklistPageContent').addClass('is-ready');
            $('#globalLoader').fadeOut(200);
        }

        var activeQuickFilter = {
            type: '',
            value: ''
        };
        var itemFilterOptionRows = <?= json_encode(isset($itemFilterOptions) && is_array($itemFilterOptions) ? $itemFilterOptions : [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        var itemCascadeLevels = ['regional', 'city', 'cluster', 'scope', 'sow', 'doc'];
        var itemCascadeSelects = {
            regional: $('#item-filter-regional'),
            city: $('#item-filter-city'),
            cluster: $('#item-filter-cluster'),
            scope: $('#item-filter-scope'),
            sow: $('#item-filter-sow'),
            doc: $('#item-filter-doc')
        };
        var itemCascadePlaceholders = {
            regional: 'Semua Regional',
            city: 'Semua Kota',
            cluster: 'Semua Cluster',
            scope: 'Semua Scope',
            sow: 'Semua SOW',
            doc: 'Semua Dokumen'
        };

        function itemCascadeValue(value) {
            return (value || '').toString().trim().toUpperCase();
        }

        function populateItemCascadeSelect(level, values, selectedValue) {
            var select = itemCascadeSelects[level];
            if (!select || !select.length) {
                return;
            }

            selectedValue = itemCascadeValue(selectedValue);
            select.empty().append($('<option>', {
                value: '',
                text: itemCascadePlaceholders[level] || 'Semua'
            }));

            values.forEach(function(value) {
                select.append($('<option>', {
                    value: value,
                    text: value
                }));
            });

            select.val(values.indexOf(selectedValue) !== -1 ? selectedValue : '');
            select.prop('disabled', values.length === 0);
        }

        function collectItemCascadeOptions(level, selected) {
            var values = {};
            var levelIndex = itemCascadeLevels.indexOf(level);

            itemFilterOptionRows.forEach(function(row) {
                var match = true;
                for (var i = 0; i < levelIndex; i++) {
                    var parentLevel = itemCascadeLevels[i];
                    if (selected[parentLevel] && itemCascadeValue(row[parentLevel]) !== selected[parentLevel]) {
                        match = false;
                        break;
                    }
                }

                if (!match) {
                    return;
                }

                var value = itemCascadeValue(row[level]);
                if (value) {
                    values[value] = true;
                }
            });

            return Object.keys(values).sort();
        }

        function refreshItemCascadingFilters(changedLevel) {
            var selected = {};
            itemCascadeLevels.forEach(function(level) {
                selected[level] = itemCascadeValue(itemCascadeSelects[level].val());
            });

            if (changedLevel) {
                var changedIndex = itemCascadeLevels.indexOf(changedLevel);
                itemCascadeLevels.forEach(function(level, index) {
                    if (index > changedIndex) {
                        selected[level] = '';
                    }
                });
            }

            itemCascadeLevels.forEach(function(level) {
                var values = collectItemCascadeOptions(level, selected);
                populateItemCascadeSelect(level, values, selected[level]);
                selected[level] = itemCascadeValue(itemCascadeSelects[level].val());
            });
        }

        function dashboardNumber(value) {
            value = parseInt(value, 10);
            return isNaN(value) ? 0 : value;
        }

        function updateDashboardSummary(summary) {
            summary = summary || {};

            var totalCluster = dashboardNumber(summary.totalCluster);
            var clusterNyAstri = dashboardNumber(summary.clusterNyAstri);
            var counts = {
                totalCluster: totalCluster,
                clusterDoneAtpBelumDokument: dashboardNumber(summary.clusterDoneAtpBelumDokument),
                clusterNyAstri: clusterNyAstri,
                astriDone: Math.max(0, totalCluster - clusterNyAstri)
            };

            Object.keys(counts).forEach(function(key) {
                $('[data-dashboard-count="' + key + '"]').text(counts[key]);
            });

            $('[data-summary-section][data-summary-key]').each(function() {
                var el = $(this);
                var section = el.data('summary-section');
                var key = el.data('summary-key');
                var sectionData = summary[section] || {};
                el.text(dashboardNumber(sectionData[key]));
            });
        }

        function loadDashboardData() {
            $.ajax({
                url: "<?= base_url('Checklist_Dokument_MyRep/dashboardData') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    selected_city: "<?= htmlspecialchars($selectedCity, ENT_QUOTES) ?>",
                    selected_regional: "<?= htmlspecialchars($selectedRegional, ENT_QUOTES) ?>"
                }
            }).done(function(response) {
                if (!response || response.status === false) {
                    return;
                }

                updateDashboardSummary(response.dashboardSummary || {});
                if ($.isArray(response.itemFilterOptions)) {
                    itemFilterOptionRows = response.itemFilterOptions;
                    refreshItemCascadingFilters();
                }
            });
        }

        function setCardCollapsed(cardSelector, collapsed) {
            var card = $(cardSelector);
            var body = card.children('.card-body');
            var icon = card.find('[data-card-widget="collapse"] i');

            if (!card.length || !body.length) {
                return;
            }

            if (collapsed) {
                card.addClass('collapsed-card');
                body.slideUp(150);
                icon.removeClass('fa-minus').addClass('fa-plus');
            } else {
                card.removeClass('collapsed-card');
                body.slideDown(150);
                icon.removeClass('fa-plus').addClass('fa-minus');
            }
        }

        $('#cluster-filter-regional, #cluster-filter-city').on('change', function() {
            $('#cluster-filter-form').trigger('submit');
        });

        if (!$.fn.DataTable) {
            showChecklistContent();
            return;
        }

        if ($('#table-checklist-dokument-focus').length) {
            $('#table-checklist-dokument-focus').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": false,
                "scrollX": true,
                "order": [],
                "pageLength": 5,
                "lengthMenu": [
                    [5, 10, 25, 50, 100],
                    [5, 10, 25, 50, 100]
                ],
                "columnDefs": [
                    {
                        "targets": [2, 3, 4, 5, 6, 7, 8],
                        "orderable": false
                    }
                ],
                "language": {
                    "emptyTable": "Belum ada cluster ATP DONE."
                }
            });
        }

        if ($('#table-checklist-dokument').length) {
            $('#table-checklist-dokument').DataTable({
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": false,
                "scrollX": true,
                "order": [],
                "pageLength": 5,
                "lengthMenu": [
                    [5, 10, 25, 50, 100],
                    [5, 10, 25, 50, 100]
                ],
                "ajax": {
                    "url": "<?= base_url('Checklist_Dokument_MyRep/clusterTableData') ?>",
                    "type": "POST",
                    "data": function(d) {
                        d.selected_city = "<?= htmlspecialchars($selectedCity, ENT_QUOTES) ?>";
                        d.selected_regional = "<?= htmlspecialchars($selectedRegional, ENT_QUOTES) ?>";
                    }
                },
                "columnDefs": [
                    {
                        "targets": [0, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        "orderable": false
                    },
                    {
                        "targets": 8,
                        "className": "sla-separator-left"
                    }
                ],
                "language": {
                    "emptyTable": "Belum ada cluster ATP DONE."
                }
            });
        }

        var itemTable = null;
        if ($('#table-checklist-item').length) {
            itemTable = $('#table-checklist-item').DataTable({
                "processing": true,
                "serverSide": true,
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": false,
                "info": true,
                "autoWidth": false,
                "responsive": false,
                "scrollX": true,
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                "ajax": {
                    "url": "<?= base_url('Checklist_Dokument_MyRep/itemTableData') ?>",
                    "type": "POST",
                    "data": function(d) {
                        d.selected_city = "<?= htmlspecialchars($selectedCity, ENT_QUOTES) ?>";
                        d.selected_regional = "<?= htmlspecialchars($selectedRegional, ENT_QUOTES) ?>";
                        d.item_regional = $('#item-filter-regional').val() || '';
                        d.item_city = $('#item-filter-city').val() || '';
                        d.item_cluster = $('#item-filter-cluster').val() || '';
                        d.item_scope = $('#item-filter-scope').val() || '';
                        d.item_sow = $('#item-filter-sow').val() || '';
                        d.item_doc = $('#item-filter-doc').val() || '';
                        d.internal_status = $('#item-filter-internal-status').val() || '';
                        d.astri_status = $('#item-filter-astri-status').val() || '';
                        d.quick_type = activeQuickFilter.type || '';
                        d.quick_value = activeQuickFilter.value || '';
                    }
                },
                "language": {
                    "emptyTable": "Belum ada item dokumen."
                }
            });
        }

        if (!itemTable) {
            showChecklistContent();
            return;
        }

        refreshItemCascadingFilters();
        loadDashboardData();

        $('#item-filter-regional').on('change', function() {
            refreshItemCascadingFilters('regional');
            itemTable.draw();
        });

        $('#item-filter-city').on('change', function() {
            refreshItemCascadingFilters('city');
            itemTable.draw();
        });

        $('#item-filter-cluster').on('change', function() {
            refreshItemCascadingFilters('cluster');
            itemTable.draw();
        });

        $('#item-filter-scope').on('change', function() {
            refreshItemCascadingFilters('scope');
            itemTable.draw();
        });

        $('#item-filter-sow').on('change', function() {
            refreshItemCascadingFilters('sow');
            itemTable.draw();
        });

        $('#item-filter-doc').on('change', function() {
            refreshItemCascadingFilters('doc');
            itemTable.draw();
        });

        $('#item-filter-internal-status').on('change', function() {
            activeQuickFilter.type = '';
            activeQuickFilter.value = '';
            itemTable.draw();
        });

        $('#item-filter-astri-status').on('change', function() {
            activeQuickFilter.type = '';
            activeQuickFilter.value = '';
            itemTable.draw();
        });

        $('.quick-item-filter').on('click', function() {
            var filterType = $(this).data('filter-type') || '';
            var filterValue = ($(this).data('filter-value') || '').toString().toUpperCase();

            $('#item-filter-regional').val('');
            $('#item-filter-city').val('');
            $('#item-filter-cluster').val('');
            $('#item-filter-scope').val('');
            $('#item-filter-sow').val('');
            $('#item-filter-doc').val('');
            $('#item-filter-internal-status').val('');
            $('#item-filter-astri-status').val('');
            refreshItemCascadingFilters();

            activeQuickFilter.type = filterType;
            activeQuickFilter.value = filterValue;
            if (filterType === 'internal') {
                $('#item-filter-internal-status').val(filterValue);
            } else if (filterType === 'astri' || filterType === 'project-opname') {
                $('#item-filter-astri-status').val(filterValue);
            }

            setCardCollapsed('#cluster-monitor-card', true);
            setCardCollapsed('#item-monitor-card', false);
            itemTable.draw();

            $('html, body').animate({
                scrollTop: $('#item-monitor-card').offset().top - 20
            }, 250);
        });

        $('#btn-export-item-excel').on('click', function() {
            var params = $.param({
                selected_city: "<?= htmlspecialchars($selectedCity, ENT_QUOTES) ?>",
                selected_regional: "<?= htmlspecialchars($selectedRegional, ENT_QUOTES) ?>",
                item_regional: $('#item-filter-regional').val() || '',
                item_city: $('#item-filter-city').val() || '',
                item_cluster: $('#item-filter-cluster').val() || '',
                item_scope: $('#item-filter-scope').val() || '',
                item_sow: $('#item-filter-sow').val() || '',
                item_doc: $('#item-filter-doc').val() || '',
                internal_status: $('#item-filter-internal-status').val() || '',
                astri_status: $('#item-filter-astri-status').val() || '',
                quick_type: activeQuickFilter.type || '',
                quick_value: activeQuickFilter.value || '',
                search: itemTable.search() || ''
            });
            window.location.href = "<?= base_url('Checklist_Dokument_MyRep/exportItemExcel') ?>?" + params;
        });

        setTimeout(showChecklistContent, 150);
    });
</script>
