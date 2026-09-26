<?php
if (!function_exists('rfs_readiness_h')) {
    function rfs_readiness_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('rfs_readiness_status_badge')) {
    function rfs_readiness_status_badge($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'READY' || $status === 'RFS' || $status === 'APPROVED') {
            return 'success';
        }
        if ($status === 'NOT READY' || $status === 'REJECTED' || $status === 'DROPPED' || $status === 'IMPOSSIBLE') {
            return 'danger';
        }
        if (strpos($status, 'WAITING') === 0 || $status === 'CARRY_OVER' || $status === 'SHIFTED_OUT') {
            return 'warning';
        }
        if ($status === 'PRIORITAS 1') {
            return 'primary';
        }
        return 'secondary';
    }
}
if (!function_exists('rfs_readiness_month_label')) {
    function rfs_readiness_month_label($year, $month)
    {
        $date = DateTime::createFromFormat('!Y-n-j', (int) $year . '-' . (int) $month . '-1');
        return $date ? $date->format('F Y') : '-';
    }
}
if (!function_exists('rfs_readiness_num_or_dash')) {
    function rfs_readiness_num_or_dash($value, $decimals = 0)
    {
        $number = (float) $value;
        if (abs($number) < 0.0000001) {
            return '-';
        }

        return number_format($number, (int) $decimals, ',', '.');
    }
}
if (!function_exists('rfs_readiness_summary_url')) {
    function rfs_readiness_summary_url($periodId, $scope = '', $label = '')
    {
        $params = ['period_id' => (int) $periodId];
        $scope = strtolower(trim((string) $scope));
        if ($scope === 'city') {
            $params['scope'] = 'city';
            $params['city'] = (string) $label;
        } elseif ($scope === 'regional') {
            $params['scope'] = 'regional';
            $params['regional'] = (string) $label;
        }
        return base_url('RFS_Readiness_MyRep?' . http_build_query($params));
    }
}

$periodStatus = strtoupper((string) ($period['status_period'] ?? ''));
$isLocked = $periodStatus === 'LOCKED';
$isDraft = $periodStatus === 'DRAFT';
$isClosed = $periodStatus === 'CLOSED';
$statusOptions = ['READY', 'NOT READY'];
$detailTitle = $selectedScope === 'regional' ? $selectedRegional : ($selectedScope === 'city' ? $selectedCity : '');
$nextMonth = !empty($period) ? ((int) $period['month_num'] + 1) : (int) date('n');
$nextYear = !empty($period) ? (int) $period['year_num'] : (int) date('Y');
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>
<style>
    .rfs-readiness-shell { background:#f4f7fb; }
    .rfs-readiness-hero { background:linear-gradient(135deg,#0b3d70,#1565a9); color:#fff; border-radius:8px; padding:22px 24px; box-shadow:0 14px 28px rgba(11,61,112,.18); }
    .rfs-readiness-hero h1 { font-size:24px; margin:0; font-weight:800; letter-spacing:0; }
    .rfs-readiness-hero p { margin:6px 0 0; color:rgba(255,255,255,.82); }
    .rfs-kpi { border:0; border-radius:8px; box-shadow:0 10px 22px rgba(30,54,82,.08); }
    .rfs-kpi .kpi-label { color:#6b7890; font-size:12px; font-weight:700; text-transform:uppercase; }
    .rfs-kpi .kpi-value { color:#17233c; font-size:24px; font-weight:800; line-height:1.1; }
    .rfs-panel { border:0; border-radius:8px; box-shadow:0 10px 22px rgba(30,54,82,.08); }
    .rfs-panel .card-header { background:#fff; border-bottom:1px solid #e9eef5; border-radius:8px 8px 0 0; }
    .rfs-table { font-size:12px; }
    .rfs-table th { background:#f7f9fc; color:#44516b; white-space:nowrap; vertical-align:middle; text-align:center; }
    .rfs-table th.rfs-owner-morep { background:#8b5cf6; color:#fff; }
    .rfs-table th.rfs-owner-tkm { background:#f5a623; color:#2d1d00; }
    .rfs-table th.rfs-aspect-head { background:#edf3fa; color:#26344d; text-align:center; font-weight:800; }
    .rfs-table th.rfs-ready-head { background:#dff6e7; color:#146c3c; text-align:center; font-weight:800; }
    .rfs-table th.rfs-not-ready-head { background:#fde7eb; color:#a61b34; text-align:center; font-weight:800; }
    .rfs-table td.rfs-ready-cell { background:#fbfffc; }
    .rfs-table td.rfs-not-ready-cell { background:#fffafa; }
    .rfs-table td { vertical-align:middle; }
    .rfs-table tfoot th, .rfs-table tfoot td { background:#f1f5f9; color:#17233c; font-weight:900; border-top:2px solid #cbd5e1; vertical-align:middle; }
    .rfs-table tfoot .rfs-footer-label { text-align:right; color:#0f3f93; }
    #table_rfs_readiness, #table_rfs_candidate { width:100% !important; }
    .dataTables_wrapper { width:100%; }
    .rfs-table .form-control-sm { min-width:78px; }
    .rfs-status-chip { display:inline-flex; align-items:center; justify-content:center; min-width:82px; border-radius:999px; padding:3px 9px; font-size:11px; font-weight:800; }
    .rfs-status-chip.success { background:#e8f8ee; color:#11723d; }
    .rfs-status-chip.danger { background:#fdecef; color:#b21f35; }
    .rfs-status-chip.warning { background:#fff4dc; color:#9a6400; }
    .rfs-status-chip.primary { background:#e7f0ff; color:#1457b8; }
    .rfs-status-chip.secondary { background:#eef1f5; color:#546072; }
    .rfs-action-row { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .rfs-approval-card { border:1px solid #e5ebf3; border-radius:8px; padding:14px; background:#fff; }
    .rfs-approval-card + .rfs-approval-card { margin-top:10px; }
    .rfs-compact-input { width:84px; }
    .rfs-owner-input { min-width:116px; }
    .rfs-toast-holder { position:fixed; right:18px; top:78px; z-index:2050; width:min(360px, calc(100vw - 36px)); }
    .rfs-toast-holder .alert { box-shadow:0 14px 30px rgba(21,37,62,.18); border:0; border-radius:8px; }
    .rfs-modal .modal-content { border:0; border-radius:8px; overflow:hidden; box-shadow:0 22px 55px rgba(21,37,62,.28); }
    .rfs-modal .modal-header { border:0; color:#fff; padding:16px 20px; }
    .rfs-modal .modal-title { font-size:16px; font-weight:800; letter-spacing:0; }
    .rfs-modal .close { color:#fff; opacity:.9; text-shadow:none; }
    .rfs-modal .modal-body { background:#f7f9fc; }
    .rfs-modal .modal-footer { background:#fff; border-top:1px solid #e7edf5; }
    .rfs-modal-header-primary { background:linear-gradient(135deg,#1457b8,#1680c2); }
    .rfs-modal-header-warning { background:linear-gradient(135deg,#d97706,#f59e0b); }
    .rfs-modal-header-secondary { background:linear-gradient(135deg,#334155,#64748b); }
    .rfs-cluster-banner { background:#fff; border:1px solid #e6ecf5; border-left:5px solid #1680c2; border-radius:8px; padding:12px 14px; }
    .rfs-form-section { background:#fff; border:1px solid #e6ecf5; border-radius:8px; padding:14px; margin-bottom:14px; }
    .rfs-form-section-title { display:flex; align-items:center; gap:8px; margin-bottom:12px; color:#26344d; font-weight:800; font-size:13px; text-transform:uppercase; }
    .rfs-form-section-title .rfs-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .rfs-dot-morep { background:#8b5cf6; }
    .rfs-dot-tkm { background:#f5a623; }
    .rfs-dot-target { background:#1680c2; }
    .rfs-modal label { color:#46536d; font-size:12px; font-weight:700; margin-bottom:5px; }
    .rfs-modal .form-control { border-color:#d9e1ed; border-radius:6px; }
    .rfs-modal .form-control:focus { border-color:#1680c2; box-shadow:0 0 0 .15rem rgba(22,128,194,.15); }
    .rfs-modal .custom-switch { background:#f7f9fc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px 10px 46px; }
    .rfs-modal .custom-switch .custom-control-label { font-size:13px; color:#26344d; font-weight:800; margin:0; }
    .rfs-weekly-table th { text-align:center; }
    .rfs-weekly-table td { text-align:right; }
    .rfs-weekly-target { background:#edf7ff; color:#1457b8; font-weight:800; }
    .rfs-weekly-actual { background:#e8f8ee; color:#11723d; font-weight:800; }
    .rfs-table th.rfs-weekly-target { background:#dbeafe !important; color:#1457b8 !important; }
    .rfs-table th.rfs-weekly-actual { background:#dcfce7 !important; color:#11723d !important; }
    .rfs-weekly-target-cell { background:#f7fbff; color:#1457b8; }
    .rfs-weekly-actual-cell { background:#fbfffc; color:#11723d; }
    .rfs-weekly-total-target { background:#bfdbfe !important; color:#0f3f93 !important; font-weight:900 !important; }
    .rfs-weekly-total-actual { background:#bbf7d0 !important; color:#0f5f34 !important; font-weight:900 !important; }
    .rfs-weekly-chart { display:grid; grid-template-columns:repeat(auto-fit, minmax(112px, 1fr)); gap:12px; margin:14px 0 18px; }
    .rfs-weekly-chart-item { border:1px solid #e4ebf5; border-radius:8px; padding:10px; background:#fff; min-height:142px; display:flex; flex-direction:column; }
    .rfs-weekly-chart-week { color:#26344d; font-weight:900; text-align:center; margin-bottom:8px; }
    .rfs-weekly-chart-bars { flex:1; display:flex; gap:8px; align-items:flex-end; justify-content:center; min-height:78px; padding:0 4px; border-bottom:1px solid #e4ebf5; }
    .rfs-weekly-chart-bar { width:28px; min-height:4px; border-radius:6px 6px 0 0; position:relative; transition:height .2s ease; }
    .rfs-weekly-chart-bar.target { background:linear-gradient(180deg,#60a5fa,#2563eb); }
    .rfs-weekly-chart-bar.actual { background:linear-gradient(180deg,#4ade80,#16a34a); }
    .rfs-weekly-chart-value { font-size:10px; font-weight:800; text-align:center; line-height:1.2; margin-top:4px; }
    .rfs-weekly-chart-value.target { color:#1457b8; }
    .rfs-weekly-chart-value.actual { color:#11723d; }
    .rfs-weekly-chart-legend { display:flex; justify-content:center; gap:14px; color:#52627a; font-size:11px; font-weight:800; margin-bottom:2px; }
    .rfs-weekly-chart-legend span::before { content:''; display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:5px; vertical-align:-1px; }
    .rfs-weekly-chart-legend .target::before { background:#2563eb; }
    .rfs-weekly-chart-legend .actual::before { background:#16a34a; }
    .rfs-history-change { min-width:320px; }
    .rfs-history-change-item { border-bottom:1px solid #e8edf5; padding:6px 0; }
    .rfs-history-change-item:last-child { border-bottom:0; }
    .rfs-history-change-field { color:#26344d; font-weight:800; font-size:12px; margin-bottom:3px; }
    .rfs-history-change-values { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .rfs-history-pill { display:inline-block; border-radius:6px; padding:3px 7px; font-size:11px; font-weight:700; max-width:260px; white-space:normal; word-break:break-word; }
    .rfs-history-pill.before { background:#f1f5f9; color:#475569; }
    .rfs-history-pill.after { background:#e8f8ee; color:#11723d; }
    .rfs-history-arrow { color:#94a3b8; font-weight:900; }
    .rfs-mini-progress { height:8px; background:#e8edf5; border-radius:999px; overflow:hidden; }
    .rfs-mini-progress span { display:block; height:100%; background:#16a34a; border-radius:999px; }
    @media (max-width: 768px) {
        .rfs-readiness-hero { padding:18px; }
        .rfs-readiness-hero h1 { font-size:20px; }
        .rfs-table { font-size:11px; }
    }
</style>

<div class="content-wrapper rfs-readiness-shell">
    <div class="rfs-toast-holder" id="rfsToastHolder"></div>
    <section class="content-header">
        <div class="container-fluid">
            <div class="rfs-readiness-hero">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h1>RFS Readiness MyRep</h1>
                        <p>Planning bulanan untuk cluster DRM done yang belum RFS, dengan baseline, revision approval, dan carry over.</p>
                    </div>
                    <?php if (!empty($period)): ?>
                        <div class="text-right mt-3 mt-md-0">
                            <div class="small text-uppercase">Periode aktif</div>
                            <div class="h4 mb-1"><?= rfs_readiness_h(rfs_readiness_month_label($period['year_num'], $period['month_num'])) ?></div>
                            <span class="badge badge-light"><?= rfs_readiness_h($periodStatus) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-warning">
                    Tabel RFS Readiness belum tersedia. Jalankan patch <code>db/patch_myrep_rfs_readiness_20260921.sql</code> terlebih dahulu.
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success"><?= rfs_readiness_h($this->session->flashdata('success')) ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= rfs_readiness_h($this->session->flashdata('error')) ?></div>
            <?php endif; ?>

            <?php if ($isReady): ?>
                <?php if ($isSummaryMode): ?>
                <div class="card rfs-panel mb-3">
                    <div class="card-body">
                        <form method="get" action="<?= base_url('RFS_Readiness_MyRep') ?>">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label>Period</label>
                                    <select name="period_id" class="form-control">
                                        <?php foreach ($periodOptions as $option): ?>
                                            <option value="<?= (int) $option['id_period'] ?>" <?= (int) $selectedPeriodId === (int) $option['id_period'] ? 'selected' : '' ?>>
                                                <?= rfs_readiness_h(rfs_readiness_month_label($option['year_num'], $option['month_num']) . ' - ' . $option['status_period']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if (!$isSummaryMode): ?>
                                    <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
                                    <?php if ($selectedScope === 'city'): ?><input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>"><?php endif; ?>
                                    <?php if ($selectedScope === 'regional'): ?><input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>"><?php endif; ?>
                                    <div class="col-md-2">
                                        <label>Area Detail</label>
                                        <div class="form-control bg-light"><?= rfs_readiness_h($detailTitle ?: '-') ?></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Prioritas</label>
                                        <select name="priority" class="form-control">
                                            <option value="">Semua</option>
                                            <option value="PRIORITAS 1" <?= $selectedPriority === 'PRIORITAS 1' ? 'selected' : '' ?>>Prioritas 1</option>
                                            <option value="PRIORITAS 2" <?= $selectedPriority === 'PRIORITAS 2' ? 'selected' : '' ?>>Prioritas 2</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Final</label>
                                        <select name="final_status" class="form-control">
                                            <option value="">Semua</option>
                                            <?php foreach (['OPEN','RFS','CARRY_OVER','SHIFTED_OUT','IMPOSSIBLE','DROPPED','CANCELLED_BY_LATE_RFS'] as $status): ?>
                                                <option value="<?= $status ?>" <?= $selectedFinalStatus === $status ? 'selected' : '' ?>><?= $status ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-3">
                                    <div class="rfs-action-row">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i></button>
                                        <a class="btn btn-outline-secondary" href="<?= rfs_readiness_summary_url($selectedPeriodId) ?>"><i class="fas fa-redo"></i></a>
                                        <?php if ($selectedPeriodId > 0): ?>
                                            <button class="btn btn-outline-success" type="button" data-toggle="modal" data-target="#modalDownloadSummary"><i class="fas fa-download"></i> Download</button>
                                        <?php endif; ?>
                                        <?php if ($selectedPeriodId > 0 && $canHoManage): ?>
                                            <button class="btn btn-outline-primary" type="button" data-toggle="modal" data-target="#modalCandidateClusters"><i class="fas fa-search"></i> Kandidat DRM</button>
                                        <?php endif; ?>
                                        <?php if ($canHoManage): ?>
                                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#modalCreatePeriod"><i class="fas fa-calendar-plus"></i> Period</button>
                                        <?php endif; ?>
                                        <?php if (!$isSummaryMode): ?>
                                            <a class="btn btn-outline-info" href="<?= rfs_readiness_summary_url($selectedPeriodId) ?>"><i class="fas fa-arrow-left"></i> Summary</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($isSummaryMode && !empty($period) && $canHoManage): ?>
                    <div class="card rfs-panel">
                        <div class="card-header">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <strong><i class="fas fa-layer-group mr-1"></i> Control Period</strong>
                                <div class="rfs-action-row">
                                    <form method="post" action="<?= base_url('RFS_Readiness_MyRep/generateCandidates') ?>">
                                        <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
                                        <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
                                        <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
                                        <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-plus"></i> Generate Kandidat <?= $isLocked ? 'Late' : '' ?></button>
                                    </form>
                                    <form method="post" action="<?= base_url('RFS_Readiness_MyRep/syncActualRfs') ?>">
                                        <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
                                        <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
                                        <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
                                        <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
                                        <button class="btn btn-sm btn-outline-info" type="submit"><i class="fas fa-sync"></i> Sync RFS</button>
                                    </form>
                                    <?php if ($isDraft): ?>
                                        <button class="btn btn-sm btn-warning" type="button" data-toggle="modal" data-target="#modalLockPeriod"><i class="fas fa-lock"></i> Lock</button>
                                    <?php endif; ?>
                                    <?php if ($isLocked): ?>
                                        <button class="btn btn-sm btn-outline-warning" type="button" data-toggle="modal" data-target="#modalUnlockPeriod"><i class="fas fa-unlock"></i> Unlock</button>
                                        <button class="btn btn-sm btn-danger" type="button" data-toggle="modal" data-target="#modalClosePeriod"><i class="fas fa-flag-checkered"></i> Closing</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="modal" data-target="#modalCandidateClusters">
                                <i class="fas fa-search"></i> Lihat Kandidat DRM
                            </button>
                            <?php if (!empty($candidateCount)): ?>
                                <span class="badge badge-light ml-2"><?= number_format((float) $candidateCount, 0, ',', '.') ?> kandidat belum masuk period.</span>
                            <?php else: ?>
                                <span class="small text-muted ml-2">Kandidat akan dihitung saat modal dibuka atau generate dijalankan.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Cluster</div><div class="kpi-value"><?= number_format((float) ($summary['total_cluster'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">HP DRM</div><div class="kpi-value"><?= number_format((float) ($summary['total_hp'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Prioritas 1</div><div class="kpi-value"><?= number_format((float) ($summary['priority_1_hp'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Prioritas 2</div><div class="kpi-value"><?= number_format((float) ($summary['priority_2_hp'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Actual RFS</div><div class="kpi-value"><?= number_format((float) ($summary['rfs_hp'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Waiting Change Request</div><div class="kpi-value"><?= number_format((float) ($summary['waiting_approval'] ?? 0), 0, ',', '.') ?></div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Target Geser</div><div class="kpi-value"><?= number_format((float) ($summary['slipped_hp'] ?? 0), 0, ',', '.') ?></div><div class="small text-muted"><?= number_format((float) ($summary['slipped_count'] ?? 0), 0, ',', '.') ?> cluster</div></div></div></div>
                    <div class="col-md-2 col-6"><div class="card rfs-kpi"><div class="card-body"><div class="kpi-label">Shifted Out</div><div class="kpi-value"><?= number_format((float) ($summary['shifted_out_hp'] ?? 0), 0, ',', '.') ?></div><div class="small text-muted"><?= number_format((float) ($summary['shifted_out_count'] ?? 0), 0, ',', '.') ?> cluster</div></div></div></div>
                </div>

                <?php if ($isSummaryMode && $canHoManage && (int) ($summary['total_cluster'] ?? 0) === 0): ?>
                    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <strong>Period ini belum punya cluster readiness.</strong>
                            Klik kandidat DRM untuk generate cluster DRM Done yang belum RFS ke period ini.
                        </div>
                        <button class="btn btn-primary btn-sm mt-2 mt-md-0" type="button" data-toggle="modal" data-target="#modalCandidateClusters">
                            <i class="fas fa-search"></i> Buka Kandidat DRM
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card rfs-panel">
                            <div class="card-header"><strong><i class="fas fa-bell mr-1"></i> Staging Notification</strong></div>
                            <div class="card-body">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-muted text-center py-3">Belum ada notifikasi.</div>
                                <?php endif; ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <?php $isUnread = ($notification['status_notification'] ?? '') === 'UNREAD'; ?>
                                    <div class="rfs-approval-card <?= $isUnread ? 'border-primary' : '' ?>">
                                        <div class="small <?= $isUnread ? 'font-weight-bold text-dark' : 'text-muted' ?>">
                                            <?= rfs_readiness_h($notification['message'] ?? '-') ?>
                                        </div>
                                        <div class="small text-muted mt-1"><?= rfs_readiness_h($notification['created_at'] ?? '-') ?></div>
                                        <?php if ($isUnread): ?>
                                            <form method="post" action="<?= base_url('RFS_Readiness_MyRep/markNotificationRead') ?>" class="mt-2">
                                                <input type="hidden" name="id_notification" value="<?= (int) $notification['id_notification'] ?>">
                                                <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-primary">Mark read</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card rfs-panel">
                            <div class="card-header"><strong><i class="fas fa-user-check mr-1"></i> Approval Queue</strong></div>
                            <div class="card-body">
                                <?php if (empty($pendingRequests)): ?>
                                    <div class="text-muted text-center py-3">Tidak ada change request pending.</div>
                                <?php endif; ?>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <div class="rfs-approval-card">
                                        <div class="d-flex justify-content-between">
                                            <strong><?= rfs_readiness_h($request['cluster_name'] ?? '-') ?></strong>
                                            <span class="badge badge-warning"><?= rfs_readiness_h($request['status_request'] ?? '-') ?></span>
                                        </div>
                                        <div class="small text-muted mb-2"><?= rfs_readiness_h($request['city_name'] ?? '-') ?> | W<?= rfs_readiness_h($request['old_week'] ?? '-') ?> ke W<?= rfs_readiness_h($request['new_week'] ?? '-') ?></div>
                                        <div class="small mb-2"><?= rfs_readiness_h($request['remark'] ?? '-') ?></div>
                                        <?php if (!empty($request['id_evidence'])): ?>
                                            <a href="<?= base_url('RFS_Readiness_MyRep/previewEvidence/' . (int) $request['id_evidence']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary mb-2"><i class="fas fa-file-pdf"></i> Evidence</a>
                                        <?php endif; ?>
                                        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/reviewChangeRequest') ?>">
                                            <input type="hidden" name="id_change_request" value="<?= (int) $request['id_change_request'] ?>">
                                            <textarea name="approval_note" class="form-control form-control-sm mb-2" rows="2" placeholder="Approval note"></textarea>
                                            <div class="btn-group btn-group-sm w-100">
                                                <button class="btn btn-success" name="decision" value="APPROVE" type="submit"><i class="fas fa-check"></i></button>
                                                <button class="btn btn-danger" name="decision" value="REJECT" type="submit"><i class="fas fa-times"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card rfs-panel">
                    <div class="card-header">
                        <strong><i class="fas fa-chart-bar mr-1"></i> Target vs Realisasi RFS Mingguan<?= !$isSummaryMode && $detailTitle !== '' ? ' - ' . rfs_readiness_h($detailTitle) : '' ?></strong>
                    </div>
                    <div class="card-body table-responsive">
                        <?php
                        $targetTotal = (float) ($weeklySummary['target_total'] ?? 0);
                        $actualTotal = (float) ($weeklySummary['actual_total'] ?? 0);
                        $actualPercent = $targetTotal > 0 ? min(100, round(($actualTotal / $targetTotal) * 100, 1)) : 0;
                        $weeklyRows = $weeklySummary['weeks'] ?? [];
                        $weeklyMax = 0;
                        foreach ($weeklyRows as $weekRow) {
                            $weeklyMax = max($weeklyMax, (float) ($weekRow['target_hp'] ?? 0), (float) ($weekRow['actual_hp'] ?? 0));
                        }
                        ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-4">
                                <div class="small text-muted font-weight-bold">Total Target</div>
                                <div class="h5 mb-0 text-primary"><?= number_format($targetTotal, 0, ',', '.') ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted font-weight-bold">Total Realisasi</div>
                                <div class="h5 mb-0 text-success"><?= number_format($actualTotal, 0, ',', '.') ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted font-weight-bold">Progress</div>
                                <div class="rfs-mini-progress"><span style="width:<?= rfs_readiness_h($actualPercent) ?>%"></span></div>
                                <div class="small text-muted mt-1"><?= number_format($actualPercent, 1, ',', '.') ?>%</div>
                            </div>
                        </div>
                        <?php if (!empty($weeklyRows)): ?>
                            <div class="rfs-weekly-chart-legend">
                                <span class="target">Target</span>
                                <span class="actual">Realisasi</span>
                            </div>
                            <div class="rfs-weekly-chart">
                                <?php foreach ($weeklyRows as $weekRow): ?>
                                    <?php
                                    $weekTarget = (float) ($weekRow['target_hp'] ?? 0);
                                    $weekActual = (float) ($weekRow['actual_hp'] ?? 0);
                                    $targetHeight = $weeklyMax > 0 ? max(4, round(($weekTarget / $weeklyMax) * 76)) : 4;
                                    $actualHeight = $weeklyMax > 0 ? max(4, round(($weekActual / $weeklyMax) * 76)) : 4;
                                    ?>
                                    <div class="rfs-weekly-chart-item">
                                        <div class="rfs-weekly-chart-week"><?= rfs_readiness_h($weekRow['week'] ?? '-') ?></div>
                                        <div class="rfs-weekly-chart-bars">
                                            <div>
                                                <div class="rfs-weekly-chart-bar target" style="height:<?= rfs_readiness_h($targetHeight) ?>px"></div>
                                                <div class="rfs-weekly-chart-value target"><?= number_format($weekTarget, 0, ',', '.') ?></div>
                                            </div>
                                            <div>
                                                <div class="rfs-weekly-chart-bar actual" style="height:<?= rfs_readiness_h($actualHeight) ?>px"></div>
                                                <div class="rfs-weekly-chart-value actual"><?= number_format($weekActual, 0, ',', '.') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <table class="table table-bordered rfs-table rfs-weekly-table mb-0">
                            <thead>
                                <tr>
                                    <th></th>
                                    <?php foreach ($weeklyRows as $weekRow): ?>
                                        <th><?= rfs_readiness_h($weekRow['week'] ?? '-') ?></th>
                                    <?php endforeach; ?>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="rfs-weekly-target">Target RFS</th>
                                    <?php foreach ($weeklyRows as $weekRow): ?>
                                        <td><?= rfs_readiness_num_or_dash($weekRow['target_hp'] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td class="font-weight-bold"><?= number_format($targetTotal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <th class="rfs-weekly-actual">Realisasi RFS</th>
                                    <?php foreach ($weeklyRows as $weekRow): ?>
                                        <td><?= rfs_readiness_num_or_dash($weekRow['actual_hp'] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td class="font-weight-bold"><?= number_format($actualTotal, 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($isSummaryMode): ?>
                    <div class="card rfs-panel">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#summaryCity" role="tab"><i class="fas fa-city mr-1"></i> Summary Kota</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#summaryRegional" role="tab"><i class="fas fa-map-marked-alt mr-1"></i> Summary Regional</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <?php
                                $summaryColumns = function ($rows, $scope) use ($selectedPeriodId) {
                                    $summaryTotals = [
                                        'total_hp' => 0,
                                        'priority_1_hp' => 0,
                                        'priority_2_hp' => 0,
                                        'material_ready' => 0,
                                        'material_not_ready' => 0,
                                        'olt_ready' => 0,
                                        'olt_not_ready' => 0,
                                        'tenaga_kerja_ready' => 0,
                                        'tenaga_kerja_not_ready' => 0,
                                        'operasional_ready' => 0,
                                        'operasional_not_ready' => 0,
                                        'accessories_ready' => 0,
                                        'accessories_not_ready' => 0,
                                        'fix_count' => 0,
                                        'belum_count' => 0,
                                        'rfs_count' => 0,
                                        'waiting_cr' => 0,
                                    ];
                                    foreach ($rows as $row) {
                                        $summaryTotals['total_hp'] += (float) ($row['total_hp'] ?? 0);
                                        $summaryTotals['priority_1_hp'] += (float) ($row['priority_1_hp'] ?? 0);
                                        $summaryTotals['priority_2_hp'] += (float) ($row['priority_2_hp'] ?? 0);
                                        $summaryTotals['material_ready'] += (float) ($row['aspects']['material']['ready_hp'] ?? 0);
                                        $summaryTotals['material_not_ready'] += (float) ($row['aspects']['material']['not_ready_hp'] ?? 0);
                                        $summaryTotals['olt_ready'] += (float) ($row['aspects']['olt']['ready_hp'] ?? 0);
                                        $summaryTotals['olt_not_ready'] += (float) ($row['aspects']['olt']['not_ready_hp'] ?? 0);
                                        $summaryTotals['tenaga_kerja_ready'] += (float) ($row['aspects']['tenaga_kerja']['ready_hp'] ?? 0);
                                        $summaryTotals['tenaga_kerja_not_ready'] += (float) ($row['aspects']['tenaga_kerja']['not_ready_hp'] ?? 0);
                                        $summaryTotals['operasional_ready'] += (float) ($row['aspects']['operasional']['ready_hp'] ?? 0);
                                        $summaryTotals['operasional_not_ready'] += (float) ($row['aspects']['operasional']['not_ready_hp'] ?? 0);
                                        $summaryTotals['accessories_ready'] += (float) ($row['aspects']['accessories']['ready_hp'] ?? 0);
                                        $summaryTotals['accessories_not_ready'] += (float) ($row['aspects']['accessories']['not_ready_hp'] ?? 0);
                                        $summaryTotals['fix_count'] += (float) ($row['fix_count'] ?? 0);
                                        $summaryTotals['belum_count'] += (float) ($row['belum_count'] ?? 0);
                                        $summaryTotals['rfs_count'] += (float) ($row['rfs_count'] ?? 0);
                                        $summaryTotals['waiting_cr'] += (float) ($row['waiting_cr'] ?? 0);
                                    }
                                    ob_start();
                                    ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover rfs-table mb-0 js-rfs-summary-table">
                                            <thead>
                                                <tr>
                                                    <th rowspan="3">No</th>
                                                    <?php if ($scope === 'city'): ?><th rowspan="3">Provinsi</th><?php endif; ?>
                                                    <th rowspan="3"><?= $scope === 'regional' ? 'Regional' : 'Kota' ?></th>
                                                    <th rowspan="3">Homepass DRM</th>
                                                    <th colspan="2" rowspan="2" class="text-center">Target RFS</th>
                                                    <th colspan="4" class="text-center rfs-owner-morep">MOREP</th>
                                                    <th colspan="6" class="text-center rfs-owner-tkm">TKM</th>
                                                    <th rowspan="3">CONFIRMED</th>
                                                    <th rowspan="3">BELUM</th>
                                                    <th rowspan="3">RFS</th>
                                                    <th rowspan="3">WAITING CHANGE REQUEST</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="rfs-aspect-head">Material</th>
                                                    <th colspan="2" class="rfs-aspect-head">OLT</th>
                                                    <th colspan="2" class="rfs-aspect-head">Tenaga Kerja</th>
                                                    <th colspan="2" class="rfs-aspect-head">Operasional</th>
                                                    <th colspan="2" class="rfs-aspect-head">Accessories</th>
                                                </tr>
                                                <tr>
                                                    <th>Prioritas 1</th>
                                                    <th>Prioritas 2</th>
                                                    <th class="rfs-ready-head">Ready</th>
                                                    <th class="rfs-not-ready-head">Not Ready</th>
                                                    <th class="rfs-ready-head">Ready</th>
                                                    <th class="rfs-not-ready-head">Not Ready</th>
                                                    <th class="rfs-ready-head">Ready</th>
                                                    <th class="rfs-not-ready-head">Not Ready</th>
                                                    <th class="rfs-ready-head">Ready</th>
                                                    <th class="rfs-not-ready-head">Not Ready</th>
                                                    <th class="rfs-ready-head">Ready</th>
                                                    <th class="rfs-not-ready-head">Not Ready</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($rows)): ?>
                                                    <tr><td colspan="<?= $scope === 'city' ? 20 : 19 ?>" class="text-center text-muted py-4">Belum ada data summary.</td></tr>
                                                <?php endif; ?>
                                                <?php foreach ($rows as $idx => $row): ?>
                                                    <tr>
                                                        <td class="text-right"><?= (int) $idx + 1 ?></td>
                                                        <?php if ($scope === 'city'): ?><td><?= rfs_readiness_h($row['province_name'] ?? '-') ?></td><?php endif; ?>
                                                        <td><a class="font-weight-bold" href="<?= rfs_readiness_summary_url($selectedPeriodId, $scope, (string) ($row['label'] ?? '')) ?>"><?= rfs_readiness_h($row['label'] ?? '-') ?></a></td>
                                                        <td class="text-right"><?= rfs_readiness_num_or_dash($row['total_hp'] ?? 0) ?></td>
                                                        <td class="text-right"><?= rfs_readiness_num_or_dash($row['priority_1_hp'] ?? 0) ?></td>
                                                        <td class="text-right"><?= rfs_readiness_num_or_dash($row['priority_2_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['material']['ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-not-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['material']['not_ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['olt']['ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-not-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['olt']['not_ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['tenaga_kerja']['ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-not-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['tenaga_kerja']['not_ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['operasional']['ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-not-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['operasional']['not_ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['accessories']['ready_hp'] ?? 0) ?></td>
                                                        <td class="text-right rfs-not-ready-cell"><?= rfs_readiness_num_or_dash($row['aspects']['accessories']['not_ready_hp'] ?? 0) ?></td>
                                                        <td><span class="badge badge-success"><?= rfs_readiness_num_or_dash($row['fix_count'] ?? 0) ?></span></td>
                                                        <td><span class="badge badge-secondary"><?= rfs_readiness_num_or_dash($row['belum_count'] ?? 0) ?></span></td>
                                                        <td><span class="badge badge-info"><?= rfs_readiness_num_or_dash($row['rfs_count'] ?? 0) ?></span></td>
                                                        <td><span class="badge badge-danger"><?= rfs_readiness_num_or_dash($row['waiting_cr'] ?? 0) ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <?php if ($scope === 'city'): ?><th></th><?php endif; ?>
                                                    <th class="rfs-footer-label">TOTAL</th>
                                                    <th class="text-right"><?= number_format($summaryTotals['total_hp'], 0, ',', '.') ?></th>
                                                    <th class="text-right"><?= number_format($summaryTotals['priority_1_hp'], 0, ',', '.') ?></th>
                                                    <th class="text-right"><?= number_format($summaryTotals['priority_2_hp'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-ready-cell"><?= number_format($summaryTotals['material_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-not-ready-cell"><?= number_format($summaryTotals['material_not_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-ready-cell"><?= number_format($summaryTotals['olt_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-not-ready-cell"><?= number_format($summaryTotals['olt_not_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-ready-cell"><?= number_format($summaryTotals['tenaga_kerja_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-not-ready-cell"><?= number_format($summaryTotals['tenaga_kerja_not_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-ready-cell"><?= number_format($summaryTotals['operasional_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-not-ready-cell"><?= number_format($summaryTotals['operasional_not_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-ready-cell"><?= number_format($summaryTotals['accessories_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-right rfs-not-ready-cell"><?= number_format($summaryTotals['accessories_not_ready'], 0, ',', '.') ?></th>
                                                    <th class="text-center"><?= number_format($summaryTotals['fix_count'], 0, ',', '.') ?></th>
                                                    <th class="text-center"><?= number_format($summaryTotals['belum_count'], 0, ',', '.') ?></th>
                                                    <th class="text-center"><?= number_format($summaryTotals['rfs_count'], 0, ',', '.') ?></th>
                                                    <th class="text-center"><?= number_format($summaryTotals['waiting_cr'], 0, ',', '.') ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <?php
                                    return ob_get_clean();
                                };
                                ?>
                                <div class="tab-pane fade show active" id="summaryCity" role="tabpanel">
                                    <?= $summaryColumns($citySummaries, 'city') ?>
                                </div>
                                <div class="tab-pane fade" id="summaryRegional" role="tabpanel">
                                    <?= $summaryColumns($regionalSummaries, 'regional') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card rfs-panel">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#weeklyCity" role="tab"><i class="fas fa-city mr-1"></i> Target/Realisasi Kota</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#weeklyRegional" role="tab"><i class="fas fa-map-marked-alt mr-1"></i> Target/Realisasi Regional</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <?php
                            $weeklyAreaTable = function ($rows, $scope, $weeks) use ($selectedPeriodId) {
                                $weeklyTotals = [
                                    'weeks' => [],
                                    'target_total' => 0,
                                    'actual_total' => 0,
                                    'remaining_total' => 0,
                                ];
                                foreach ($weeks as $weekRow) {
                                    $week = (string) ($weekRow['week'] ?? '');
                                    $weeklyTotals['weeks'][$week] = ['target_hp' => 0, 'actual_hp' => 0];
                                }
                                foreach ($rows as $row) {
                                    foreach ($weeks as $weekRow) {
                                        $week = (string) ($weekRow['week'] ?? '');
                                        $weeklyTotals['weeks'][$week]['target_hp'] += (float) ($row['weeks'][$week]['target_hp'] ?? 0);
                                        $weeklyTotals['weeks'][$week]['actual_hp'] += (float) ($row['weeks'][$week]['actual_hp'] ?? 0);
                                    }
                                    $weeklyTotals['target_total'] += (float) ($row['target_total'] ?? 0);
                                    $weeklyTotals['actual_total'] += (float) ($row['actual_total'] ?? 0);
                                    $weeklyTotals['remaining_total'] += (float) ($row['remaining_total'] ?? 0);
                                }
                                $weeklyProgressTotal = $weeklyTotals['target_total'] > 0
                                    ? ($weeklyTotals['actual_total'] / $weeklyTotals['target_total']) * 100
                                    : 0;
                                ob_start();
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover rfs-table mb-0 js-rfs-list-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2">No</th>
                                                <?php if ($scope === 'city'): ?><th rowspan="2">Provinsi</th><?php endif; ?>
                                                <?php if ($scope === 'city'): ?><th rowspan="2">Regional</th><?php endif; ?>
                                                <th rowspan="2"><?= $scope === 'regional' ? 'Regional' : 'Kota' ?></th>
                                                <th colspan="<?= count($weeks) + 1 ?>" class="text-center rfs-weekly-target">Target RFS</th>
                                                <th colspan="<?= count($weeks) + 1 ?>" class="text-center rfs-weekly-actual">Realisasi RFS</th>
                                                <th rowspan="2">Remaining</th>
                                                <th rowspan="2">Progress</th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($weeks as $weekRow): ?><th class="rfs-weekly-target"><?= rfs_readiness_h($weekRow['week'] ?? '-') ?></th><?php endforeach; ?><th class="rfs-weekly-total-target">Total</th>
                                                <?php foreach ($weeks as $weekRow): ?><th class="rfs-weekly-actual"><?= rfs_readiness_h($weekRow['week'] ?? '-') ?></th><?php endforeach; ?><th class="rfs-weekly-total-actual">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rows as $idx => $row): ?>
                                                <tr>
                                                    <td class="text-right"><?= (int) $idx + 1 ?></td>
                                                    <?php if ($scope === 'city'): ?><td><?= rfs_readiness_h($row['province_name'] ?? '-') ?></td><?php endif; ?>
                                                    <?php if ($scope === 'city'): ?><td><?= rfs_readiness_h($row['regional_name'] ?? '-') ?></td><?php endif; ?>
                                                    <td><a class="font-weight-bold" href="<?= rfs_readiness_summary_url($selectedPeriodId, $scope, (string) ($row['label'] ?? '')) ?>"><?= rfs_readiness_h($row['label'] ?? '-') ?></a></td>
                                                    <?php foreach ($weeks as $weekRow): ?>
                                                        <?php $week = (string) ($weekRow['week'] ?? ''); ?>
                                                        <td class="text-right rfs-weekly-target-cell"><?= rfs_readiness_num_or_dash($row['weeks'][$week]['target_hp'] ?? 0) ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="text-right rfs-weekly-total-target"><?= rfs_readiness_num_or_dash($row['target_total'] ?? 0) ?></td>
                                                    <?php foreach ($weeks as $weekRow): ?>
                                                        <?php $week = (string) ($weekRow['week'] ?? ''); ?>
                                                        <td class="text-right rfs-weekly-actual-cell"><?= rfs_readiness_num_or_dash($row['weeks'][$week]['actual_hp'] ?? 0) ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="text-right rfs-weekly-total-actual"><?= rfs_readiness_num_or_dash($row['actual_total'] ?? 0) ?></td>
                                                    <td class="text-right"><?= rfs_readiness_num_or_dash($row['remaining_total'] ?? 0) ?></td>
                                                    <td class="text-right"><?= (float) ($row['progress_percent'] ?? 0) == 0.0 ? '-' : rfs_readiness_num_or_dash($row['progress_percent'] ?? 0, 1) . '%' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <?php if ($scope === 'city'): ?><th></th><?php endif; ?>
                                                <?php if ($scope === 'city'): ?><th></th><?php endif; ?>
                                                <th class="rfs-footer-label">TOTAL</th>
                                                <?php foreach ($weeks as $weekRow): ?>
                                                    <?php $week = (string) ($weekRow['week'] ?? ''); ?>
                                                    <th class="text-right rfs-weekly-target-cell"><?= number_format((float) ($weeklyTotals['weeks'][$week]['target_hp'] ?? 0), 0, ',', '.') ?></th>
                                                <?php endforeach; ?>
                                                <th class="text-right rfs-weekly-total-target"><?= number_format($weeklyTotals['target_total'], 0, ',', '.') ?></th>
                                                <?php foreach ($weeks as $weekRow): ?>
                                                    <?php $week = (string) ($weekRow['week'] ?? ''); ?>
                                                    <th class="text-right rfs-weekly-actual-cell"><?= number_format((float) ($weeklyTotals['weeks'][$week]['actual_hp'] ?? 0), 0, ',', '.') ?></th>
                                                <?php endforeach; ?>
                                                <th class="text-right rfs-weekly-total-actual"><?= number_format($weeklyTotals['actual_total'], 0, ',', '.') ?></th>
                                                <th class="text-right"><?= number_format($weeklyTotals['remaining_total'], 0, ',', '.') ?></th>
                                                <th class="text-right"><?= number_format($weeklyProgressTotal, 1, ',', '.') ?>%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <?php
                                return ob_get_clean();
                            };
                            ?>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="weeklyCity" role="tabpanel">
                                    <?= $weeklyAreaTable($cityWeeklySummaries, 'city', $weeklySummary['weeks'] ?? []) ?>
                                </div>
                                <div class="tab-pane fade" id="weeklyRegional" role="tabpanel">
                                    <?= $weeklyAreaTable($regionalWeeklySummaries, 'regional', $weeklySummary['weeks'] ?? []) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card rfs-panel">
                            <div class="card-header">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <strong><i class="fas fa-tasks mr-1"></i> Checklist Readiness</strong>
                                    <a class="btn btn-sm btn-outline-info" href="<?= rfs_readiness_summary_url($selectedPeriodId) ?>"><i class="fas fa-arrow-left"></i> Summary</a>
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-hover rfs-table mb-0" id="table_rfs_readiness">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Cluster</th>
                                            <th rowspan="2">Regional</th>
                                            <th rowspan="2">Kota</th>
                                            <th rowspan="2">Batch Approval</th>
                                            <th rowspan="2">Checklist</th>
                                            <th rowspan="2">HP</th>
                                            <th rowspan="2">Target</th>
                                            <th colspan="2" class="text-center rfs-owner-morep">MOREP</th>
                                            <th colspan="3" class="text-center rfs-owner-tkm">TKM</th>
                                            <th colspan="4" class="text-center">Status Progress</th>
                                            <th rowspan="2">Priority</th>
                                            <th rowspan="2">Final</th>
                                            <th rowspan="2">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th>Material</th>
                                            <th>OLT</th>
                                            <th>Tenaga Kerja</th>
                                            <th>Operasional</th>
                                            <th>Accessories</th>
                                            <th>Cable</th>
                                            <th>FAT</th>
                                            <th>Tiang</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th class="rfs-footer-label">TOTAL HALAMAN</th>
                                            <th class="text-right" id="rfsDetailTotalHp">0</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="modalCreatePeriod" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/createPeriod') ?>" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Create Readiness Period</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-6"><label>Tahun</label><input type="number" name="year_num" class="form-control" value="<?= date('Y') ?>" required></div>
                    <div class="form-group col-6"><label>Bulan</label><input type="number" name="month_num" min="1" max="12" class="form-control" value="<?= date('n') ?>" required></div>
                </div>
                <div class="form-group"><label>Tanggal Meeting</label><input type="date" name="meeting_date" class="form-control"></div>
                <div class="form-group"><label>Remark</label><textarea name="remark" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="submit">Create</button></div>
        </form>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalDownloadSummary" tabindex="-1">
    <div class="modal-dialog">
        <form method="get" action="<?= base_url('RFS_Readiness_MyRep/exportWeeklySummary') ?>" class="modal-content">
            <div class="modal-header rfs-modal-header-primary">
                <h5 class="modal-title"><i class="fas fa-download mr-2"></i>Download Summary RFS</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="rfs-form-section mb-0">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-target"></span>Filter Export</div>
                    <div class="form-group">
                        <label>Period</label>
                        <select name="period_id" class="form-control" required>
                            <?php foreach ($periodOptions as $option): ?>
                                <option value="<?= (int) $option['id_period'] ?>" <?= (int) $selectedPeriodId === (int) $option['id_period'] ? 'selected' : '' ?>>
                                    <?= rfs_readiness_h(rfs_readiness_month_label($option['year_num'], $option['month_num']) . ' - ' . $option['status_period']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info mb-0">
                        File Excel otomatis berisi sheet <strong>Summary Kota</strong>, <strong>Summary Regional</strong>, dan <strong>Detail Cluster</strong>, lengkap dengan status readiness, target RFS, dan realisasi RFS mingguan.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Batal</button>
                <button class="btn btn-success" type="submit"><i class="fas fa-file-excel mr-1"></i> Download Excel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalChecklistItem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/saveItemBaseline') ?>" class="modal-content js-rfs-ajax-form" data-success-modal="#modalChecklistItem">
            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
            <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
            <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
            <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
            <input type="hidden" name="item_id" id="check_item_id">
            <div class="modal-header rfs-modal-header-primary"><h5 class="modal-title"><i class="fas fa-clipboard-check mr-2"></i>Checklist Readiness Cluster</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="rfs-cluster-banner mb-3">
                    <strong id="check_cluster_name">Cluster</strong>
                    <div class="small text-muted" id="check_cluster_meta"></div>
                </div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-target"></span>Target RFS</div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Week Preview</label><input type="number" id="check_week" min="1" max="53" class="form-control js-week" disabled></div>
                        <div class="form-group col-md-4"><label>Planned Date</label><input type="date" name="planned_rfs_date" id="check_date" class="form-control js-date-week" required></div>
                    </div>
                </div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-morep"></span>MOREP</div>
                    <div class="form-row">
                        <?php foreach (['material' => 'Material', 'olt' => 'OLT'] as $key => $label): ?>
                            <div class="form-group col-md-6">
                                <label><?= $label ?></label>
                                <select name="<?= $key ?>_status" id="check_<?= $key ?>" class="form-control">
                                    <?php foreach ($statusOptions as $status): ?>
                                        <option value="<?= $status ?>"><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-tkm"></span>TKM</div>
                    <div class="form-row">
                        <?php foreach (['tenaga_kerja' => 'Tenaga Kerja', 'operasional' => 'Operasional', 'accessories' => 'Accessories'] as $key => $label): ?>
                            <div class="form-group col-md">
                                <label><?= $label ?></label>
                                <select name="<?= $key ?>_status" id="check_<?= $key ?>" class="form-control">
                                    <?php foreach ($statusOptions as $status): ?>
                                        <option value="<?= $status ?>"><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rfs-form-section mb-0">
                    <div class="form-group mb-2"><label>Remark</label><textarea name="remark" id="check_remark" class="form-control" rows="3"></textarea></div>
                    <input type="hidden" name="checklist_is_fixed" value="0">
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="check_is_fixed" name="checklist_is_fixed" value="1">
                        <label class="custom-control-label" for="check_is_fixed">Tandai checklist ini CONFIRMED</label>
                    </div>
                    <div class="small text-muted">Tanggal wajib diisi dan otomatis dikonversi ke ISO week. Week hanya preview.</div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Simpan Checklist</button></div>
        </form>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalCandidateClusters" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header rfs-modal-header-secondary"><h5 class="modal-title"><i class="fas fa-search mr-2"></i>Kandidat DRM Done Belum RFS</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <?php if (!empty($period) && $canHoManage): ?>
                    <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px;">
                        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/generateCandidates') ?>" class="js-rfs-ajax-form" data-keep-modal="1">
                            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
                            <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
                            <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
                            <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-plus"></i> Generate Semua Kandidat Filter Ini</button>
                        </form>
                        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/generateCandidates') ?>" class="js-rfs-ajax-form js-generate-selected-candidates" data-keep-modal="1">
                            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
                            <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
                            <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
                            <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
                            <input type="hidden" name="selected_only" value="1">
                            <span class="js-selected-candidate-inputs"></span>
                            <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-check-square"></i> Generate Terpilih <span class="badge badge-light js-selected-candidate-count">0</span></button>
                        </form>
                        <button type="button" class="btn btn-outline-secondary btn-sm js-clear-selected-candidates"><i class="fas fa-times"></i> Clear Pilihan</button>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover rfs-table" id="table_rfs_candidate">
                        <thead>
                            <tr>
                                <th style="width:38px;"><input type="checkbox" class="js-candidate-select-page" title="Pilih semua di halaman ini"></th>
                                <th>Cluster</th>
                                <th>Kota</th>
                                <th>HP DRM</th>
                                <th>DRM Date</th>
                                <th>OLT</th>
                                <th>Status RFS</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalItemHistory" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header rfs-modal-header-secondary"><h5 class="modal-title"><i class="fas fa-history mr-2"></i>History Cluster</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="rfs-cluster-banner mb-3">
                    <strong id="history_cluster_name">Cluster</strong>
                    <div class="small text-muted" id="history_cluster_meta"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover rfs-table mb-0">
                        <thead><tr><th>Waktu</th><th>Event</th><th>User</th><th>Remark</th><th>Perubahan</th></tr></thead>
                        <tbody id="history_rows">
                            <tr><td colspan="5" class="text-center text-muted py-4">Memuat history...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalChangeRequest" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/submitChangeRequest') ?>" enctype="multipart/form-data" class="modal-content js-rfs-ajax-form" data-success-modal="#modalChangeRequest">
            <input type="hidden" name="item_id" id="cr_item_id">
            <div class="modal-header rfs-modal-header-warning"><h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>Change Request Readiness</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="rfs-cluster-banner mb-3"><strong id="cr_cluster_name">Cluster</strong><div class="small text-muted" id="cr_cluster_meta"></div></div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-target"></span>Target dan Alasan</div>
                    <div class="form-row">
                        <div class="form-group col-md-3"><label>Week Preview</label><input type="number" id="cr_week" min="1" max="53" class="form-control js-week" disabled></div>
                        <div class="form-group col-md-3"><label>Planned Date</label><input type="date" name="planned_rfs_date" id="cr_date" class="form-control js-date-week" required></div>
                        <div class="form-group col-md-6"><label>Reason Category</label><input type="text" name="reason_category" class="form-control" placeholder="Tenaga Kerja / Material / OLT"></div>
                    </div>
                </div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-morep"></span>MOREP</div>
                    <div class="form-row">
                        <?php foreach (['material' => 'Material', 'olt' => 'OLT'] as $key => $label): ?>
                            <div class="form-group col-md-6">
                                <label><?= $label ?></label>
                                <select name="<?= $key ?>_status" id="cr_<?= $key ?>" class="form-control">
                                    <?php foreach ($statusOptions as $status): ?>
                                        <option value="<?= $status ?>"><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rfs-form-section">
                    <div class="rfs-form-section-title"><span class="rfs-dot rfs-dot-tkm"></span>TKM</div>
                    <div class="form-row">
                        <?php foreach (['tenaga_kerja' => 'Tenaga Kerja', 'operasional' => 'Operasional', 'accessories' => 'Accessories'] as $key => $label): ?>
                            <div class="form-group col-md">
                                <label><?= $label ?></label>
                                <select name="<?= $key ?>_status" id="cr_<?= $key ?>" class="form-control">
                                    <?php foreach ($statusOptions as $status): ?>
                                        <option value="<?= $status ?>"><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rfs-form-section mb-0">
                    <div class="form-group"><label>Remark</label><textarea name="remark" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group"><label>Evidence PDF</label><input type="file" name="evidence_pdf" class="form-control" accept="application/pdf"></div>
                    <div class="small text-muted">Evidence wajib untuk perubahan negatif atau geser week keluar. Perubahan positif akan langsung masuk history.</div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-warning" type="submit">Submit Change</button></div>
        </form>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalLockPeriod" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/lockPeriod') ?>" class="modal-content">
            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
            <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
            <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
            <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
            <div class="modal-header rfs-modal-header-warning"><h5 class="modal-title"><i class="fas fa-lock mr-2"></i>Lock Period</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="rfs-form-section mb-0">
                    <div class="row text-center">
                        <div class="col-4"><div class="small text-muted font-weight-bold">Total</div><div class="h5"><?= number_format((float) ($checklistStatus['total'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-4"><div class="small text-muted font-weight-bold">CONFIRMED</div><div class="h5 text-success"><?= number_format((float) ($checklistStatus['fix'] ?? 0), 0, ',', '.') ?></div></div>
                        <div class="col-4"><div class="small text-muted font-weight-bold">BELUM</div><div class="h5 text-danger"><?= number_format((float) ($checklistStatus['belum'] ?? 0), 0, ',', '.') ?></div></div>
                    </div>
                    <?php if (!empty($checklistStatus['belum'])): ?>
                        <div class="alert alert-warning mt-3 mb-0">Masih ada cluster BELUM CONFIRMED. Period tetap bisa di-lock, tapi setelah lock perubahan harus melalui flow Change Request.</div>
                    <?php else: ?>
                        <div class="alert alert-success mt-3 mb-0">Semua cluster sudah CONFIRMED. Period siap di-lock.</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Batal</button>
                <button class="btn btn-warning" type="submit" onclick="return confirm('Lock baseline period ini?');"><i class="fas fa-lock"></i> Lock Period</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade rfs-modal" id="modalUnlockPeriod" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/unlockPeriod') ?>" class="modal-content">
            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
            <input type="hidden" name="scope" value="<?= rfs_readiness_h($selectedScope) ?>">
            <input type="hidden" name="city" value="<?= rfs_readiness_h($selectedCity) ?>">
            <input type="hidden" name="regional" value="<?= rfs_readiness_h($selectedRegional) ?>">
            <div class="modal-header rfs-modal-header-warning"><h5 class="modal-title"><i class="fas fa-unlock mr-2"></i>Unlock Period</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="rfs-form-section mb-0">
                    <div class="form-group">
                        <label>Remark Unlock</label>
                        <textarea name="unlock_remark" class="form-control" rows="4" required placeholder="Contoh: koreksi hasil meeting, baseline perlu dilengkapi kembali."></textarea>
                    </div>
                    <div class="small text-muted">Unlock hanya bisa jika belum ada change request waiting/approved dan semua item masih OPEN.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-dismiss="modal">Batal</button>
                <button class="btn btn-warning" type="submit" onclick="return confirm('Unlock period ini kembali ke DRAFT?');"><i class="fas fa-unlock"></i> Unlock</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalClosePeriod" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form method="post" action="<?= base_url('RFS_Readiness_MyRep/closePeriod') ?>" class="modal-content">
            <input type="hidden" name="period_id" value="<?= (int) $selectedPeriodId ?>">
            <div class="modal-header"><h5 class="modal-title">Closing Period</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <div class="form-row mb-3">
                    <div class="form-group col-md-2"><label>Next Year</label><input type="number" name="next_year_num" class="form-control" value="<?= (int) $nextYear ?>"></div>
                    <div class="form-group col-md-2"><label>Next Month</label><input type="number" name="next_month_num" min="1" max="12" class="form-control" value="<?= (int) $nextMonth ?>"></div>
                </div>
                <?php
                $closingPreviewCount = 0;
                $closingPreviewHp = 0;
                foreach ($items as $previewItem) {
                    if (($previewItem['final_status'] ?? '') !== 'RFS') {
                        $closingPreviewCount++;
                        $closingPreviewHp += (float) ($previewItem['homepass_drm_snapshot'] ?? 0);
                    }
                }
                ?>
                <div class="alert alert-info">
                    Preview carry over/shift period berikutnya: <strong><?= number_format($closingPreviewCount, 0, ',', '.') ?> cluster</strong>
                    / <strong><?= number_format($closingPreviewHp, 0, ',', '.') ?> HP</strong>. Item dengan status Impossible/Dropped tidak akan dibuat ke period berikutnya.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered rfs-table">
                        <thead><tr><th>Cluster</th><th>Kota</th><th>HP</th><th>Current</th><th>Closing Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php if (($item['final_status'] ?? '') === 'RFS') { continue; } ?>
                                <tr>
                                    <td><?= rfs_readiness_h($item['cluster_name'] ?? '-') ?></td>
                                    <td><?= rfs_readiness_h($item['city_name'] ?? '-') ?></td>
                                    <td class="text-right"><?= rfs_readiness_num_or_dash($item['homepass_drm_snapshot'] ?? 0) ?></td>
                                    <td><?= rfs_readiness_h($item['final_status'] ?? '-') ?></td>
                                    <td>
                                        <select name="final_status[<?= (int) $item['id_item'] ?>]" class="form-control form-control-sm">
                                            <option value="CARRY_OVER">Carry Over</option>
                                            <option value="SHIFTED_OUT">Shifted Out</option>
                                            <option value="IMPOSSIBLE">Impossible</option>
                                            <option value="DROPPED">Dropped</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-danger" type="submit" onclick="return confirm('Tutup period dan buat carry over?');">Close Period</button></div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var readinessRows = {};
    var selectedPeriodId = <?= (int) $selectedPeriodId ?>;
    var selectedCity = <?= json_encode($selectedCity) ?>;
    var selectedRegional = <?= json_encode($selectedRegional) ?>;
    var isSummaryMode = <?= $isSummaryMode ? 'true' : 'false' ?>;
    var selectedPriority = <?= json_encode($selectedPriority) ?>;
    var selectedFinalStatus = <?= json_encode($selectedFinalStatus) ?>;

    function h(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function nf(value) {
        var number = parseFloat(value || 0);
        return number.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function nfRow(value) {
        var number = parseFloat(value || 0);
        return Math.abs(number) < 0.0000001 ? '-' : nf(number);
    }
    function pf(value) {
        var number = parseFloat(value || 0);
        return number.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '%';
    }
    function idNumber(value) {
        var text = $('<div>').html(String(value === null || value === undefined ? '' : value)).text().trim();
        text = text.replace(/\s+/g, '').replace('%', '').replace(/\./g, '').replace(',', '.');
        var number = parseFloat(text);
        return isNaN(number) ? 0 : number;
    }
    function setFooterValue(api, index, value, isPercent) {
        var footer = api.column(index).footer();
        if (!footer) return;
        footer.innerHTML = isPercent ? pf(value) : nf(value);
        footer.classList.add('text-right');
    }
    function updateFooterSums(api, startIndex, endIndex) {
        for (var index = startIndex; index <= endIndex; index++) {
            var total = api.column(index, {search: 'applied'}).data().reduce(function (sum, value) {
                return sum + idNumber(value);
            }, 0);
            setFooterValue(api, index, total, false);
        }
    }
    function updateSummaryFooter(api) {
        var hasProvince = $(api.table().node()).find('thead tr:first th').eq(1).text().trim() === 'Provinsi';
        updateFooterSums(api, hasProvince ? 3 : 2, api.columns().count() - 1);
    }
    function updateWeeklyFooter(api) {
        var hasProvince = $(api.table().node()).find('thead tr:first th').eq(1).text().trim() === 'Provinsi';
        var firstNumeric = hasProvince ? 4 : 2;
        var progressIndex = api.columns().count() - 1;
        var remainingIndex = progressIndex - 1;
        var weeklyColumnCount = remainingIndex - firstNumeric;
        var groupSize = weeklyColumnCount / 2;
        var targetTotalIndex = firstNumeric + groupSize - 1;
        var actualTotalIndex = remainingIndex - 1;

        updateFooterSums(api, firstNumeric, remainingIndex);
        var targetTotal = idNumber(api.column(targetTotalIndex).footer().innerHTML);
        var actualTotal = idNumber(api.column(actualTotalIndex).footer().innerHTML);
        setFooterValue(api, progressIndex, targetTotal > 0 ? (actualTotal / targetTotal) * 100 : 0, true);
    }
    if (window.jQuery && $.fn.DataTable) {
        $.fn.dataTable.ext.type.detect.unshift(function (data) {
            if (typeof data !== 'string' && typeof data !== 'number') return null;
            var text = $('<div>').html(String(data)).text().trim();
            if (text === '-') return 'rfs-id-num';
            if (text === '') return null;
            text = text.replace(/\s+/g, '').replace('%', '');
            return /^-?\d{1,3}(\.\d{3})*(,\d+)?$|^-?\d+(,\d+)?$/.test(text) ? 'rfs-id-num' : null;
        });
        $.fn.dataTable.ext.type.order['rfs-id-num-pre'] = function (data) {
            var text = $('<div>').html(String(data || '')).text().trim();
            text = text.replace(/\s+/g, '').replace('%', '').replace(/\./g, '').replace(',', '.');
            var value = parseFloat(text);
            return isNaN(value) ? 0 : value;
        };
    }
    function badgeClass(status) {
        status = String(status || '').toUpperCase();
        if (['READY', 'RFS', 'APPROVED', 'COMPLETE'].indexOf(status) >= 0) return 'success';
        if (['NOT READY', 'REJECTED', 'DROPPED', 'IMPOSSIBLE'].indexOf(status) >= 0) return 'danger';
        if (status.indexOf('WAITING') === 0 || ['CARRY_OVER', 'SHIFTED_OUT', 'ON PROGRESS'].indexOf(status) >= 0) return 'warning';
        if (status === 'PRIORITAS 1') return 'primary';
        return 'secondary';
    }
    function chip(status) {
        return '<span class="rfs-status-chip ' + badgeClass(status) + '">' + h(status || '-') + '</span>';
    }
    function boqChip(status) {
        return '<span class="rfs-status-chip ' + badgeClass(status || 'NY BOQ') + '">' + h(status || 'NY BOQ') + '</span>';
    }
    var historyFieldLabels = {
        current_week: 'Target Week',
        current_planned_rfs_date: 'Target Date',
        priority_level: 'Priority',
        material_status: 'Material',
        olt_status: 'OLT',
        tenaga_kerja_status: 'Tenaga Kerja',
        operasional_status: 'Operasional',
        accessories_status: 'Accessories',
        morep_owner: 'Owner MOREP',
        tkm_owner: 'Owner TKM',
        final_status: 'Final',
        actual_rfs_date: 'Actual RFS Date',
        baseline_week: 'Baseline Week',
        baseline_planned_rfs_date: 'Baseline Date',
        reason_category: 'Reason',
        status_request: 'Status Request',
        approval_level: 'Approval Level',
        old_week: 'Old Week',
        new_week: 'New Week',
        old_planned_rfs_date: 'Old Target Date',
        new_planned_rfs_date: 'New Target Date'
    };
    function parseHistoryPayload(value) {
        if (!value) return {};
        try {
            var parsed = JSON.parse(value);
            if (parsed && typeof parsed === 'object') {
                return parsed;
            }
        } catch (error) {}
        return {value: value};
    }
    function displayHistoryValue(value) {
        if (value === null || typeof value === 'undefined' || value === '') return '-';
        if (typeof value === 'object') return JSON.stringify(value);
        return String(value);
    }
    function historyFieldLabel(key) {
        return historyFieldLabels[key] || String(key || '').replace(/_/g, ' ').replace(/\b\w/g, function (letter) {
            return letter.toUpperCase();
        });
    }
    function historyDiffHtml(oldPayload, newPayload) {
        var before = parseHistoryPayload(oldPayload);
        var after = parseHistoryPayload(newPayload);
        var keys = {};
        Object.keys(before || {}).forEach(function (key) { keys[key] = true; });
        Object.keys(after || {}).forEach(function (key) { keys[key] = true; });
        var html = [];
        Object.keys(keys).forEach(function (key) {
            var beforeValue = displayHistoryValue(before ? before[key] : '');
            var afterValue = displayHistoryValue(after ? after[key] : '');
            if (beforeValue === afterValue) return;
            html.push(
                '<div class="rfs-history-change-item">' +
                    '<div class="rfs-history-change-field">' + h(historyFieldLabel(key)) + '</div>' +
                    '<div class="rfs-history-change-values">' +
                        '<span class="rfs-history-pill before">' + h(beforeValue) + '</span>' +
                        '<span class="rfs-history-arrow">&rarr;</span>' +
                        '<span class="rfs-history-pill after">' + h(afterValue) + '</span>' +
                    '</div>' +
                '</div>'
            );
        });
        return html.length ? '<div class="rfs-history-change">' + html.join('') + '</div>' : '<span class="text-muted">Tidak ada perubahan field.</span>';
    }
    function showRfsToast(type, message) {
        var holder = document.getElementById('rfsToastHolder');
        if (!holder) return;
        var alertType = type === 'success' ? 'success' : 'danger';
        var node = document.createElement('div');
        node.className = 'alert alert-' + alertType + ' alert-dismissible fade show';
        node.innerHTML = '<button type="button" class="close" data-dismiss="alert">&times;</button>' + h(message || 'Proses selesai.');
        holder.appendChild(node);
        window.setTimeout(function () {
            if (window.jQuery) {
                $(node).alert('close');
            } else if (node.parentNode) {
                node.parentNode.removeChild(node);
            }
        }, 4200);
    }
    function isoWeek(dateValue) {
        if (!dateValue) return '';
        var date = new Date(dateValue + 'T00:00:00');
        if (isNaN(date.getTime())) return '';
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
        var week1 = new Date(date.getFullYear(), 0, 4);
        return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + ((week1.getDay() + 6) % 7)) / 7);
    }

    document.addEventListener('change', function (event) {
        if (!event.target.classList.contains('js-date-week')) return;
        var row = event.target.closest('td') || event.target.closest('.form-row') || event.target.closest('.modal-body');
        var weekInput = row ? row.querySelector('.js-week') : null;
        if (weekInput && event.target.value) {
            weekInput.value = isoWeek(event.target.value);
        }
    });

    function fillReadinessForm(prefix, row) {
        document.getElementById(prefix + '_item_id').value = row.id_item || '';
        document.getElementById(prefix + '_cluster_name').textContent = row.cluster_name || 'Cluster';
        var meta = document.getElementById(prefix + '_cluster_meta');
        if (meta) {
            var checklistText = row.checklist_completed_at ? 'CONFIRMED' : 'BELUM';
            meta.textContent = [row.city_name || '-', row.regional_name || '-', 'HP ' + nf(row.homepass_drm_snapshot), checklistText].join(' / ');
        }
        document.getElementById(prefix + '_week').value = row.current_week || '';
        document.getElementById(prefix + '_date').value = row.current_planned_rfs_date || '';
        ['material', 'olt', 'tenaga_kerja', 'operasional', 'accessories'].forEach(function (key) {
            var el = document.getElementById(prefix + '_' + key);
            if (el) el.value = row[key + '_status'] || 'NOT READY';
        });
        if (prefix === 'check') {
            document.getElementById('check_remark').value = row.remark || '';
            var fixedInput = document.getElementById('check_is_fixed');
            if (fixedInput) {
                fixedInput.checked = !!row.checklist_completed_at;
            }
        }
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.js-checklist, .js-cr, .js-history');
        if (!button) return;
        var row = readinessRows[button.getAttribute('data-item-id')] || {};
        if (button.classList.contains('js-checklist')) {
            fillReadinessForm('check', row);
        } else if (button.classList.contains('js-cr')) {
            fillReadinessForm('cr', row);
        } else if (button.classList.contains('js-history')) {
            loadItemHistory(button.getAttribute('data-item-id'), row);
        }
    });

    function loadItemHistory(itemId, row) {
        document.getElementById('history_cluster_name').textContent = row.cluster_name || 'Cluster';
        document.getElementById('history_cluster_meta').textContent = [row.city_name || '-', row.regional_name || '-'].join(' / ');
        var tbody = document.getElementById('history_rows');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Memuat history...</td></tr>';
        if (!window.jQuery) return;
        $.getJSON('<?= base_url('RFS_Readiness_MyRep/itemHistoryData') ?>', {item_id: itemId}, function (response) {
            if (!response || !response.status) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">' + h(response && response.message ? response.message : 'History gagal dimuat.') + '</td></tr>';
                return;
            }
            var rows = response.data || [];
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada history.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(function (item) {
                return '<tr>' +
                    '<td>' + h(item.created_at || '-') + '</td>' +
                    '<td><span class="badge badge-light">' + h(item.event_type || '-') + '</span></td>' +
                    '<td>' + h(item.created_by_name || item.created_by || '-') + '</td>' +
                    '<td>' + h(item.remark || '-') + '</td>' +
                    '<td>' + historyDiffHtml(item.old_payload, item.new_payload) + '</td>' +
                    '</tr>';
            }).join('');
        }).fail(function () {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">History gagal dimuat.</td></tr>';
        });
    }

    var readinessTable = null;
    function refreshReadinessSurface() {
        if (readinessTable) {
            readinessTable.ajax.reload(null, false);
        }
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_rfs_candidate')) {
            $('#table_rfs_candidate').DataTable().ajax.reload(null, false);
        }
    }

    if (window.jQuery && $.fn.DataTable && selectedPeriodId > 0 && !isSummaryMode) {
        readinessTable = $('#table_rfs_readiness').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ordering: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function () {
                var api = this.api();
                var totalHp = 0;
                api.rows({page: 'current'}).data().each(function (row) {
                    totalHp += parseFloat(row.homepass_drm_snapshot || 0) || 0;
                });
                $('#rfsDetailTotalHp').text(nf(totalHp));
                window.setTimeout(function () {
                    if (readinessTable) {
                        readinessTable.columns.adjust();
                    }
                }, 0);
            },
            ajax: {
                url: '<?= base_url('RFS_Readiness_MyRep/itemTableData') ?>',
                type: 'GET',
                data: function (data) {
                    data.period_id = selectedPeriodId;
                    data.city = selectedCity;
                    data.regional = selectedRegional;
                    data.priority = selectedPriority;
                    data.final_status = selectedFinalStatus;
                },
                dataSrc: function (json) {
                    readinessRows = {};
                    (json.data || []).forEach(function (row) {
                        readinessRows[row.id_item] = row;
                    });
                    return json.data || [];
                }
            },
            columns: [
                { data: null, orderable: false, searchable: false, className: 'text-center', render: function (row, type, data, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }},
                { data: null, render: function (row) {
                    var pending = row.pending_request_id ? '<div><span class="badge badge-warning">Change Request ' + h(row.pending_request_status) + '</span></div>' : '';
                    return '<strong>' + h(row.cluster_name || '-') + '</strong><div class="text-muted small">' + h(row.cluster_code || '-') + '</div>' + pending;
                }},
                { data: 'regional_name', render: function (value) { return h(value || '-'); } },
                { data: 'city_name', render: function (value) { return h(value || '-'); } },
                { data: null, className: 'text-center', render: function (row) {
                    var label = h(row.batch_approval_label || 'NY Batch Approval');
                    var badgeClass = h(row.batch_approval_badge_class || 'secondary');
                    var badge = '<span class="badge badge-' + badgeClass + '">' + label + '</span>';
                    if (row.batch_approval_url) {
                        return '<a href="' + h(row.batch_approval_url) + '" title="Buka detail Batch Approval">' + badge + '</a>';
                    }
                    return badge;
                } },
                { data: null, className: 'text-center', render: function (row) {
                    if (row.checklist_completed_at) {
                        return '<span class="badge badge-success">CONFIRMED</span><div class="small text-muted">' + h(row.checklist_completed_at) + '</div>';
                    }
                    return '<span class="badge badge-secondary">BELUM</span>';
                }},
                { data: 'homepass_drm_snapshot', className: 'text-right', render: nfRow },
                { data: null, render: function (row) {
                    var week = row.current_week ? 'W' + h(row.current_week) : '-';
                    var date = row.current_planned_rfs_date ? '<div class="small text-muted">' + h(row.current_planned_rfs_date) + '</div>' : '';
                    var baseline = row.baseline_week ? '<div class="small text-muted">Baseline W' + h(row.baseline_week) + (row.baseline_planned_rfs_date ? ' / ' + h(row.baseline_planned_rfs_date) : '') + '</div>' : '';
                    var stateClass = row.target_period_state === 'SHIFTED_OUT' || row.target_period_state === 'PAST_TARGET' ? 'warning' : (row.target_period_state === 'SHIFTED_IN' ? 'info' : 'success');
                    var shifted = row.target_slipped ? ' <span class="badge badge-warning">Geser</span>' : '';
                    var state = '<div><span class="badge badge-' + stateClass + '">' + h(row.target_period_label || 'IN PERIOD') + '</span>' + shifted + '</div>';
                    return '<strong>' + week + '</strong>' + date + baseline + state;
                }},
                { data: 'material_status', render: chip },
                { data: 'olt_status', render: chip },
                { data: 'tenaga_kerja_status', render: chip },
                { data: 'operasional_status', render: chip },
                { data: 'accessories_status', render: chip },
                { data: 'boq_cable_status', render: boqChip },
                { data: 'boq_fat_status', render: boqChip },
                { data: 'boq_tiang_status', render: boqChip },
                { data: 'boq_progress_status', render: boqChip },
                { data: 'priority_level', render: chip },
                { data: null, render: function (row) {
                    var actual = row.actual_rfs_date ? '<div class="small text-muted">' + h(row.actual_rfs_date) + '</div>' : '';
                    return chip(row.final_status) + actual;
                }},
                { data: null, orderable: false, searchable: false, render: function (row) {
                    var actions = [];
                    actions.push('<button type="button" class="btn btn-sm btn-outline-secondary js-history" data-toggle="modal" data-target="#modalItemHistory" data-item-id="' + h(row.id_item) + '"><i class="fas fa-history"></i></button>');
                    if (row.can_edit_baseline) {
                        actions.push('<button type="button" class="btn btn-sm btn-outline-primary js-checklist" data-toggle="modal" data-target="#modalChecklistItem" data-item-id="' + h(row.id_item) + '"><i class="fas fa-clipboard-check"></i></button>');
                    }
                    if (row.can_submit_change) {
                        actions.push('<button type="button" class="btn btn-sm btn-outline-warning js-cr" data-toggle="modal" data-target="#modalChangeRequest" data-item-id="' + h(row.id_item) + '"><i class="fas fa-exchange-alt"></i></button>');
                    }
                    return actions.length ? '<div class="btn-group btn-group-sm">' + actions.join('') + '</div>' : '<span class="text-muted">-</span>';
                }}
            ]
        });
        function adjustReadinessTables() {
            if (readinessTable) {
                readinessTable.columns.adjust();
            }
            if ($.fn.DataTable.isDataTable('#table_rfs_candidate')) {
                $('#table_rfs_candidate').DataTable().columns.adjust();
            }
        }
        function scheduleAdjustReadinessTables() {
            window.setTimeout(adjustReadinessTables, 60);
            window.setTimeout(adjustReadinessTables, 280);
            window.setTimeout(adjustReadinessTables, 520);
        }
        $(window).on('resize', scheduleAdjustReadinessTables);
        $(document).on('collapsed.lte.pushmenu shown.lte.pushmenu expanded.lte.pushmenu', scheduleAdjustReadinessTables);
        $('.content-wrapper, body').on('transitionend webkitTransitionEnd', scheduleAdjustReadinessTables);
        scheduleAdjustReadinessTables();

    }

    var candidateReady = false;
    var selectedCandidateIds = {};
    function updateSelectedCandidateUi() {
        var ids = Object.keys(selectedCandidateIds);
        $('.js-selected-candidate-count').text(ids.length);
        var html = ids.map(function (id) {
            return '<input type="hidden" name="cluster_ids[]" value="' + h(id) + '">';
        }).join('');
        $('.js-selected-candidate-inputs').html(html);
    }
    function adjustCandidateTables() {
        if (readinessTable) {
            readinessTable.columns.adjust();
        }
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#table_rfs_candidate')) {
            $('#table_rfs_candidate').DataTable().columns.adjust();
        }
    }
    if (window.jQuery && $.fn.DataTable && selectedPeriodId > 0) {
        $('#modalCandidateClusters').on('shown.bs.modal', function () {
            if (candidateReady) {
                $('#table_rfs_candidate').DataTable().ajax.reload(null, false);
                window.setTimeout(adjustCandidateTables, 80);
                return;
            }
            candidateReady = true;
            $('#table_rfs_candidate').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                drawCallback: function () {
                    var api = this.api();
                    var allChecked = true;
                    var hasRows = false;
                    api.rows({page: 'current'}).data().each(function (row) {
                        hasRows = true;
                        if (!selectedCandidateIds[String(row.id_myrep_cluster || '')]) {
                            allChecked = false;
                        }
                    });
                    $('.js-candidate-select-page').prop('checked', hasRows && allChecked);
                    window.setTimeout(adjustCandidateTables, 80);
                },
                ajax: {
                    url: '<?= base_url('RFS_Readiness_MyRep/candidateTableData') ?>',
                    type: 'GET',
                    data: function (data) {
                        data.period_id = selectedPeriodId;
                        data.city = selectedCity;
                        data.regional = selectedRegional;
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, className: 'text-center', render: function (row) {
                        var id = String(row.id_myrep_cluster || '');
                        var checked = selectedCandidateIds[id] ? ' checked' : '';
                        return '<input type="checkbox" class="js-candidate-check" value="' + h(id) + '"' + checked + '>';
                    }},
                    { data: null, render: function (row) {
                        return '<strong>' + h(row.cluster_name || '-') + '</strong><div class="small text-muted">' + h(row.cluster_code || '-') + '</div>';
                    }},
                    { data: 'city_name', render: function (value) { return h(value || '-'); } },
                    { data: 'homepass_drm', className: 'text-right', render: nfRow },
                    { data: 'drm_date', render: function (value) { return h(value || '-'); } },
                    { data: 'nama_olt', render: function (value) { return h(value || '-'); } },
                    { data: 'status_rfs', render: function (value) { return h(value || 'NY RFS'); } }
                ]
            });
        });
        $(document).on('change', '.js-candidate-check', function () {
            var id = String(this.value || '');
            if (!id) return;
            if (this.checked) {
                selectedCandidateIds[id] = true;
            } else {
                delete selectedCandidateIds[id];
            }
            updateSelectedCandidateUi();
            if ($.fn.DataTable.isDataTable('#table_rfs_candidate')) {
                var table = $('#table_rfs_candidate').DataTable();
                var allChecked = true;
                var hasRows = false;
                table.rows({page: 'current'}).data().each(function (row) {
                    hasRows = true;
                    if (!selectedCandidateIds[String(row.id_myrep_cluster || '')]) {
                        allChecked = false;
                    }
                });
                $('.js-candidate-select-page').prop('checked', hasRows && allChecked);
            }
        });
        $(document).on('change', '.js-candidate-select-page', function () {
            if (!$.fn.DataTable.isDataTable('#table_rfs_candidate')) return;
            var checked = this.checked;
            var table = $('#table_rfs_candidate').DataTable();
            table.rows({page: 'current'}).data().each(function (row) {
                var id = String(row.id_myrep_cluster || '');
                if (!id) return;
                if (checked) {
                    selectedCandidateIds[id] = true;
                } else {
                    delete selectedCandidateIds[id];
                }
            });
            $('#table_rfs_candidate .js-candidate-check').prop('checked', checked);
            updateSelectedCandidateUi();
        });
        $(document).on('click', '.js-clear-selected-candidates', function () {
            selectedCandidateIds = {};
            $('#table_rfs_candidate .js-candidate-check, .js-candidate-select-page').prop('checked', false);
            updateSelectedCandidateUi();
        });
        $(document).on('submit', '.js-generate-selected-candidates', function (event) {
            updateSelectedCandidateUi();
            if (Object.keys(selectedCandidateIds).length === 0) {
                event.preventDefault();
                event.stopImmediatePropagation();
                showRfsToast('error', 'Pilih minimal 1 cluster kandidat dulu.');
            }
        });
    }

    if (window.jQuery && $.fn.DataTable && isSummaryMode) {
        $('.js-rfs-summary-table').each(function () {
            var table = $(this).DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ordering: true,
                searching: true,
                autoWidth: false,
                footerCallback: function () {
                    updateSummaryFooter(this.api());
                },
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false }
                ]
            });
            table.on('order.dt search.dt draw.dt', function () {
                table.column(0, {search: 'applied', order: 'applied', page: 'current'}).nodes().each(function (cell, index) {
                    cell.innerHTML = table.page.info().start + index + 1;
                });
            }).draw();
        });
        $('.js-rfs-list-table').each(function () {
            var table = $(this).DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ordering: true,
                searching: true,
                autoWidth: false,
                footerCallback: function () {
                    updateWeeklyFooter(this.api());
                },
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false }
                ]
            });
            table.on('order.dt search.dt draw.dt', function () {
                table.column(0, {search: 'applied', order: 'applied', page: 'current'}).nodes().each(function (cell, index) {
                    cell.innerHTML = table.page.info().start + index + 1;
                });
            }).draw();
        });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
            $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
        });
    }

    if (window.jQuery) {
        $(document).on('submit', '.js-rfs-ajax-form', function (event) {
            event.preventDefault();
            var form = this;
            var $form = $(form);
            var $submit = $form.find('[type="submit"]').first();
            var defaultHtml = $submit.html();
            var formData = new FormData(form);
            $submit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
            $.ajax({
                url: $form.attr('action'),
                type: ($form.attr('method') || 'post').toUpperCase(),
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).done(function (response) {
                var ok = !!(response && response.status);
                showRfsToast(ok ? 'success' : 'error', response && response.message ? response.message : (ok ? 'Data berhasil disimpan.' : 'Data gagal disimpan.'));
                if (!ok) return;
                if ($form.hasClass('js-generate-selected-candidates')) {
                    selectedCandidateIds = {};
                    updateSelectedCandidateUi();
                }
                refreshReadinessSurface();
                if (!$form.data('keep-modal')) {
                    $($form.data('success-modal') || form.closest('.modal')).modal('hide');
                }
                if ($form.data('keep-modal')) {
                    form.reset();
                }
            }).fail(function () {
                showRfsToast('error', 'Request gagal diproses. Coba ulangi atau cek koneksi aplikasi.');
            }).always(function () {
                $submit.prop('disabled', false).html(defaultHtml);
            });
        });
    }
});
</script>
