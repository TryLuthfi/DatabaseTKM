<?php
if (!function_exists('mfModuleHtml')) {
    function mfModuleHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$section = strtolower((string) ($section ?? ''));
$rows = is_array($rows ?? null) ? $rows : [];
$cityOptions = is_array($cityOptions ?? null) ? $cityOptions : [];
$statusOptions = is_array($statusOptions ?? null) ? $statusOptions : [];
$selectedCity = strtoupper(trim((string) ($selectedCity ?? '')));
$selectedStatus = strtoupper(trim((string) ($selectedStatus ?? '')));
$detailBase = (string) ($detailBase ?? 'Mainfeeder_MyRep/detail');
$moduleTitle = (string) ($moduleTitle ?? 'Mainfeeder');
$sectionMeta = [
    'drm' => ['icon' => 'fas fa-clipboard-check', 'label' => 'DRM', 'accent' => 'primary'],
    'implementasi' => ['icon' => 'fas fa-tools', 'label' => 'Implementasi', 'accent' => 'success'],
    'atp' => ['icon' => 'fas fa-check-double', 'label' => 'ATP', 'accent' => 'info'],
    'po' => ['icon' => 'fas fa-file-invoice-dollar', 'label' => 'PO', 'accent' => 'dark'],
];
$meta = $sectionMeta[$section] ?? ['icon' => 'fas fa-project-diagram', 'label' => 'Mainfeeder', 'accent' => 'primary'];
$statusCount = ['DRM' => 0, 'IMPLEMENTASI' => 0, 'ATP' => 0, 'CHECKLIST' => 0, 'DONE' => 0];
$regionalSeen = [];
foreach ($rows as $row) {
    $status = strtoupper(trim((string) ($row['current_status'] ?? '')));
    if (isset($statusCount[$status])) {
        $statusCount[$status]++;
    }
    $regional = strtoupper(trim((string) ($row['regional_name'] ?? '')));
    if ($regional !== '') {
        $regionalSeen[$regional] = true;
    }
}
$summaryCards = [
    ['label' => 'Total Mainfeeder', 'value' => count($rows), 'box' => 'bg-info', 'icon' => 'fas fa-project-diagram'],
    ['label' => 'DRM', 'value' => $statusCount['DRM'], 'box' => 'bg-primary', 'icon' => 'fas fa-clipboard-check'],
    ['label' => 'Implementasi', 'value' => $statusCount['IMPLEMENTASI'], 'box' => 'bg-success', 'icon' => 'fas fa-tools'],
    ['label' => 'ATP / Checklist', 'value' => $statusCount['ATP'] + $statusCount['CHECKLIST'], 'box' => 'bg-warning', 'icon' => 'fas fa-check-double'],
    ['label' => 'Done', 'value' => $statusCount['DONE'], 'box' => 'bg-secondary', 'icon' => 'fas fa-flag-checkered'],
];
?>

<style>
    .mf-module-page .content-header {
        padding-bottom: 0;
    }

    .mf-module-shell {
        padding: 0 0.5rem 1rem;
    }

    .mf-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .mf-section-header .card-title {
        font-weight: 800;
        color: #0f172a;
    }

    .mf-field-label {
        color: #475569;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .mf-input {
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: none;
    }

    .mf-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.12);
    }

    .mf-summary-box {
        border-radius: 12px;
        overflow: hidden;
    }

    .mf-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin: 0.75rem 0;
    }

    .mf-toolbar h3 {
        color: #0f172a;
        font-size: 1.12rem;
        font-weight: 850;
        margin: 0;
    }

    .mf-toolbar p {
        color: #64748b;
        margin: 0.2rem 0 0;
    }

    .mf-monitor-table thead th {
        background: #0f172a;
        border-color: #0f172a;
        color: #f8fafc;
        font-size: 0.78rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .mf-monitor-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .mf-action-btn {
        border-radius: 10px;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        .mf-toolbar,
        .mf-section-header {
            align-items: stretch;
            flex-direction: column;
        }

        .mf-section-header .text-right {
            text-align: left !important;
        }
    }
</style>

<div class="content-wrapper mf-module-page">
    <section class="content-header">
        <div class="container-fluid mf-module-shell">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?= mfModuleHtml($moduleTitle) ?></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('MyRepublik_Project') ?>" class="btn btn-dark">
                        <i class="fas fa-project-diagram mr-1"></i> List Project
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid mf-module-shell">
            <?php if (!$isReady): ?>
                <div class="alert alert-warning">Struktur mainfeeder full flow belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-<?= mfModuleHtml($meta['accent']) ?> shadow-sm">
                    <div class="card-header mf-section-header">
                        <div>
                            <h3 class="card-title mb-1">Filter Data <?= mfModuleHtml($meta['label']) ?> Mainfeeder</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url($detailBase) ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="mf-field-label">Kota</label>
                                        <select name="city" class="form-control mf-input">
                                            <option value="">Semua Kota</option>
                                            <?php foreach ($cityOptions as $cityOption): ?>
                                                <option value="<?= mfModuleHtml($cityOption) ?>" <?= $selectedCity === strtoupper((string) $cityOption) ? 'selected' : '' ?>><?= mfModuleHtml($cityOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="mf-field-label">Status Flow</label>
                                        <select name="status" class="form-control mf-input">
                                            <option value="">Semua Status</option>
                                            <?php foreach ($statusOptions as $statusOption): ?>
                                                <option value="<?= mfModuleHtml($statusOption) ?>" <?= $selectedStatus === strtoupper((string) $statusOption) ? 'selected' : '' ?>><?= mfModuleHtml($statusOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-group mb-0 w-100 d-flex justify-content-between">
                                        <a href="<?= base_url($detailBase) ?>" class="btn btn-outline-secondary">Reset</a>
                                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($summaryCards as $summaryCard): ?>
                        <div class="col-md">
                            <div class="small-box <?= mfModuleHtml($summaryCard['box']) ?> shadow-sm mf-summary-box">
                                <div class="inner">
                                    <h3><?= number_format((int) $summaryCard['value'], 0, ',', '.') ?></h3>
                                    <p><?= mfModuleHtml($summaryCard['label']) ?></p>
                                </div>
                                <div class="icon"><i class="<?= mfModuleHtml($summaryCard['icon']) ?>"></i></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mf-toolbar">
                    <div>
                        <h3>Monitoring <?= mfModuleHtml($meta['label']) ?> Mainfeeder</h3>
                        <p><?= number_format(count($regionalSeen), 0, ',', '.') ?> regional dalam hasil filter.</p>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header mf-section-header">
                        <div>
                            <h3 class="card-title mb-1">List Mainfeeder</h3>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover mf-monitor-table" id="table-mainfeeder-module">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Project Type</th>
                                    <th>Cluster Code</th>
                                    <th>Mainfeeder</th>
                                    <th>Kota</th>
                                    <th>Regional</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Belum ada data mainfeeder.</td></tr>
                                <?php else: $no = 1; foreach ($rows as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><span class="badge badge-dark">MAINFEEDER</span></td>
                                        <td><?= mfModuleHtml($row['cluster_code'] ?? '-') ?></td>
                                        <td><strong><?= mfModuleHtml($row['mainfeeder_name'] ?? '-') ?></strong></td>
                                        <td><?= mfModuleHtml($row['city_name'] ?? '-') ?></td>
                                        <td><?= mfModuleHtml($row['regional_name'] ?? '-') ?></td>
                                        <td><span class="badge badge-info"><?= mfModuleHtml($row['current_status'] ?? '-') ?></span></td>
                                        <td>
                                            <a href="<?= base_url($detailBase . '/' . (int) $row['id_mainfeeder']) ?>" class="btn btn-sm btn-primary mf-action-btn">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
$(function () {
    if ($.fn.DataTable) {
        $('#table-mainfeeder-module').DataTable({ pageLength: 25 });
    }
});
</script>
