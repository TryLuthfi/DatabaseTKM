<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$allDrmRows = isset($clusterRows) && is_array($clusterRows) ? $clusterRows : [];
$nyRfsRows = [];
$nyAtpRows = [];

foreach ($allDrmRows as $row) {
    $currentStatus = strtoupper(trim((string) ($row['status_current'] ?? '')));
    if (!in_array($currentStatus, ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'], true)) {
        $nyRfsRows[] = $row;
    }

    if ($currentStatus === 'RFS') {
        $nyAtpRows[] = $row;
    }
}

$implFlowTabs = [
    [
        'key' => 'ny_rfs',
        'id' => 'impl-ny-rfs',
        'table_id' => 'table_impl_ny_rfs',
        'label' => 'NY RFS',
        'rows' => $nyRfsRows,
    ],
    [
        'key' => 'ny_atp',
        'id' => 'impl-ny-atp',
        'table_id' => 'table_impl_ny_atp',
        'label' => 'NY ATP',
        'rows' => $nyAtpRows,
    ],
    [
        'key' => 'all_drm',
        'id' => 'impl-all-drm',
        'table_id' => 'table_impl_all_drm',
        'label' => 'All DRM',
        'rows' => $allDrmRows,
    ],
];

$buildImplStatusSummary = static function (array $rows) {
    $summary = [
        'onProgressCount' => 0,
        'notStartedCount' => 0,
    ];

    foreach ($rows as $row) {
        $status = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
        if ($status === 'ON PROGRESS') {
            $summary['onProgressCount']++;
        } elseif ($status === 'NOT STARTED') {
            $summary['notStartedCount']++;
        }
    }

    return $summary;
};

$implStatusSummaryByTab = [];
foreach ($implFlowTabs as $tab) {
    $implStatusSummaryByTab[$tab['key']] = $buildImplStatusSummary((array) $tab['rows']);
}

$implActiveStatusSummary = $implStatusSummaryByTab['ny_rfs'] ?? [];
$implOnProgressCount = (int) ($implActiveStatusSummary['onProgressCount'] ?? 0);
$implNotStartedCount = (int) ($implActiveStatusSummary['notStartedCount'] ?? 0);

if (!function_exists('implDashboardNumber')) {
    function implDashboardNumber($value, $zeroAsDash = false)
    {
        $number = (float) $value;
        if ($zeroAsDash && abs($number) < 0.00001) {
            return '-';
        }

        return number_format($number, 0, ',', '.');
    }
}

if (!function_exists('implStatusBadgeClass')) {
    function implStatusBadgeClass($status)
    {
        $status = strtoupper(trim((string) $status));
        if ($status === 'DONE') {
            return 'success';
        }
        if ($status === 'ON PROGRESS') {
            return 'warning';
        }
        return 'secondary';
    }
}

$renderImplTableRows = static function (array $rows) {
    foreach ($rows as $index => $row) {
        $status = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
        $badgeClass = implStatusBadgeClass($status);
        $qtyTarget = (float) ($row['target_qty_total'] ?? 0);
        $qtyActual = (float) ($row['actual_qty_total'] ?? 0);
        $photoTarget = (int) ($row['target_photo_total'] ?? 0);
        $photoUploaded = (int) ($row['uploaded_photo_total'] ?? 0);
        $itemTotal = (int) ($row['total_item'] ?? 0);
        $itemDone = (int) (($row['done_item'] ?? $row['done_item_count'] ?? 0));
        $qtyPercent = $qtyTarget > 0 ? min(100, round(($qtyActual / $qtyTarget) * 100)) : 0;
        $photoPercent = $photoTarget > 0 ? min(100, round(($photoUploaded / $photoTarget) * 100)) : 0;
        $itemPercent = $itemTotal > 0 ? min(100, round(($itemDone / $itemTotal) * 100)) : 0;
        $overallPercent = (int) round(($qtyPercent + $photoPercent + $itemPercent) / 3);
        ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td>
                <?php if (!empty($row['id_myrep_cluster'])): ?>
                    <a href="<?= base_url('Implementasi_BOQ_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="font-weight-bold">
                        <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?>
                    </a>
                <?php else: ?>
                    <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                <?php endif; ?>
                <?php if (!empty($row['cluster_code'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars((string) $row['cluster_code']) ?></div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
            <td><?= !empty($row['drm_date']) ? htmlspecialchars((string) $row['drm_date']) : '-' ?></td>
            <td><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
            <td class="text-right"><?= implDashboardNumber($qtyTarget) ?></td>
            <td class="text-right"><?= implDashboardNumber($qtyActual) ?></td>
            <td class="text-right"><?= implDashboardNumber($photoUploaded) ?> / <?= implDashboardNumber($photoTarget) ?></td>
            <td class="impl-progress-cell">
                <div class="impl-mini-progress">
                    <div class="impl-mini-progress__head">
                        <span>Overall Progress</span>
                        <span><?= $overallPercent ?>%</span>
                    </div>
                    <div class="impl-mini-progress__track impl-mini-progress__track--overall">
                        <span style="width: <?= $overallPercent ?>%;"></span>
                    </div>
                    <div class="impl-mini-progress__meta">
                        <span>Qty <?= $qtyPercent ?>%</span>
                        <span>Foto <?= $photoPercent ?>%</span>
                        <span>Item <?= $itemPercent ?>%</span>
                    </div>
                </div>
            </td>
            <td><?= !empty($row['last_progress_date']) ? htmlspecialchars((string) $row['last_progress_date']) : '-' ?></td>
            <td>
                <a href="<?= base_url('Implementasi_BOQ_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">
                    Detail
                </a>
            </td>
        </tr>
        <?php
    }
};
?>

<style>
    .impl-table-card {
        border: 0;
        overflow: hidden;
    }

    .impl-section-header {
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.04), rgba(37, 99, 235, 0.08));
    }

    .impl-section-header .card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .impl-tab-stack {
        display: grid;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .impl-tab-section {
        display: grid;
        gap: .4rem;
    }

    .impl-tab-section__label {
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b7f90;
    }

    .impl-monitor-tabs {
        border-bottom: 0;
        gap: .75rem;
        margin-bottom: 0;
    }

    .impl-monitor-tabs .nav-link {
        border: 1px solid #d9e6f2;
        border-radius: 999px;
        color: #45627b;
        font-weight: 700;
        padding: .65rem 1rem;
        background: #f7fbff;
    }

    .impl-monitor-tabs .nav-link.active {
        color: #fff;
        background: #2277a8;
        border-color: #2277a8;
        box-shadow: 0 12px 28px rgba(34, 119, 168, 0.22);
    }

    .impl-monitor-tabs__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        margin-left: .45rem;
        padding: .15rem .5rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        font-size: .8rem;
    }

    .impl-monitor-tabs .nav-link:not(.active) .impl-monitor-tabs__count {
        background: #e2edf7;
        color: #2d6287;
    }

    .impl-status-filter-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .65rem .85rem;
        padding: .7rem .85rem;
        border: 1px solid #e3edf6;
        border-radius: 12px;
        background: #f8fbfe;
    }

    .impl-status-filter-row .impl-tab-section__label {
        margin-right: .15rem;
        color: #51697e;
    }

    .impl-status-pillbar {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .impl-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-height: 32px;
        border: 1px solid #d7e3ee;
        border-radius: 999px;
        padding: .38rem .68rem;
        background: #fff;
        color: #2f5f84;
        font-size: .76rem;
        font-weight: 800;
        box-shadow: none;
        transition: all .16s ease;
    }

    .impl-status-pill:hover,
    .impl-status-pill:focus {
        border-color: #9bc8eb;
        background: #fafdff;
        outline: none;
    }

    .impl-status-pill.is-active {
        color: #fff;
        background: #2277a8;
        border-color: #2277a8;
        box-shadow: 0 8px 18px rgba(34, 119, 168, 0.18);
    }

    .impl-status-pill__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 18px;
        padding: 0 .4rem;
        border-radius: 999px;
        background: #eef4fa;
        color: #315f84;
        font-size: .68rem;
        font-weight: 900;
    }

    .impl-status-pill.is-active .impl-status-pill__count {
        background: rgba(255, 255, 255, .24);
        color: #fff;
    }

    .impl-monitor-table thead th {
        background: #0f172a;
        color: #f8fafc;
        border-color: #0f172a;
        font-size: .78rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .impl-monitor-table tbody tr:hover {
        background: rgba(37, 99, 235, .04);
    }

    .impl-monitor-table .dataTables_empty {
        color: #64748b;
        text-align: center;
    }

    .impl-table-card .dataTables_wrapper .dataTables_filter input,
    .impl-table-card .dataTables_wrapper .dataTables_length select {
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: none;
    }

    .impl-mini-progress__head {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: .35rem;
    }

    .impl-mini-progress__track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .impl-mini-progress__track span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .impl-mini-progress__track--overall span {
        background: linear-gradient(90deg, #2563eb, #14b8a6);
    }

    .impl-mini-progress__meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .78rem;
        color: #64748b;
        margin-top: .35rem;
    }

    .impl-progress-cell {
        min-width: 260px;
    }

    @media (max-width: 767.98px) {
        .impl-status-filter-row {
            align-items: stretch;
        }

        .impl-status-filter-row .impl-tab-section__label {
            width: 100%;
        }

        .impl-status-pillbar,
        .impl-status-pill {
            width: 100%;
        }

        .impl-status-pill {
            justify-content: space-between;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Implementasi BOQ MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('Implementasi_BOQ_MyRep/mainfeeder') ?>" class="btn btn-dark">Implementasi Mainfeeder</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <?php if (!$isReady): ?>
                <div class="alert alert-warning">Tabel implementasi BOQ MyRep belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Filter Implementasi BOQ</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select name="city" class="form-control">
                                        <option value="">Semua Kota</option>
                                        <?php foreach ($cityOptions as $city): ?>
                                            <option value="<?= htmlspecialchars($city) ?>" <?= strtoupper((string) $selectedCity) === strtoupper((string) $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (['NOT STARTED', 'ON PROGRESS', 'DONE'] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    <a href="<?= base_url('Implementasi_BOQ_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                                <p>Total Cluster</p>
                            </div>
                            <div class="icon"><i class="fas fa-network-wired"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= (int) ($summary['not_started'] ?? 0) ?></h3>
                                <p>Belum Mulai</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-start"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= (int) ($summary['on_progress'] ?? 0) ?></h3>
                                <p>On Progress</p>
                            </div>
                            <div class="icon"><i class="fas fa-tools"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (int) ($summary['done'] ?? 0) ?></h3>
                                <p>Done</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm impl-table-card">
                    <div class="card-header impl-section-header d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title mb-1">Monitoring Implementasi BOQ</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="impl-tab-stack">
                            <div class="impl-tab-section">
                                <div class="impl-tab-section__label">Flow</div>
                                <ul class="nav nav-tabs impl-monitor-tabs" id="impl-monitor-tab" role="tablist">
                                    <?php foreach ($implFlowTabs as $tabIndex => $tab): ?>
                                        <li class="nav-item">
                                            <a
                                                class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>"
                                                id="<?= $tab['id'] ?>-tab"
                                                data-toggle="tab"
                                                href="#<?= $tab['id'] ?>-pane"
                                                role="tab"
                                                aria-controls="<?= $tab['id'] ?>-pane"
                                                aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"
                                                data-flow-key="<?= htmlspecialchars($tab['key'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($tab['label']) ?>
                                                <span class="impl-monitor-tabs__count"><?= number_format(count($tab['rows']), 0, ',', '.') ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="impl-status-filter-row">
                                <div class="impl-tab-section__label">Filter Status</div>
                                <div class="impl-status-pillbar" aria-label="Filter status implementasi">
                                    <button type="button" class="impl-status-pill js-impl-status-filter" data-impl-status="on_progress">
                                        <span>On Progress</span>
                                        <span class="impl-status-pill__count" data-impl-status-count="onProgressCount"><?= number_format($implOnProgressCount, 0, ',', '.') ?></span>
                                    </button>
                                    <button type="button" class="impl-status-pill js-impl-status-filter" data-impl-status="not_started">
                                        <span>Not Started</span>
                                        <span class="impl-status-pill__count" data-impl-status-count="notStartedCount"><?= number_format($implNotStartedCount, 0, ',', '.') ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content" id="impl-monitor-tab-content">
                            <?php foreach ($implFlowTabs as $tabIndex => $tab): ?>
                                <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" id="<?= $tab['id'] ?>-pane" role="tabpanel" aria-labelledby="<?= $tab['id'] ?>-tab">
                                    <div class="table-responsive">
                                        <table id="<?= $tab['table_id'] ?>" class="table table-bordered table-hover impl-monitor-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Cluster</th>
                                                    <th>Kota</th>
                                                    <th>Regional</th>
                                                    <th>Tanggal DRM</th>
                                                    <th>Status Implementasi</th>
                                                    <th>Qty BOQ</th>
                                                    <th>Qty Actual</th>
                                                    <th>Foto</th>
                                                    <th>Progress</th>
                                                    <th>Last Progress</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $renderImplTableRows((array) $tab['rows']); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    $(function () {
        var implTables = {};
        var implStatusFilter = '';
        var implStatusSummaryByTab = <?= json_encode($implStatusSummaryByTab, JSON_UNESCAPED_UNICODE) ?>;
        var implTableConfigs = {
            '#table_impl_ny_rfs': { tab: 'ny_rfs' },
            '#table_impl_ny_atp': { tab: 'ny_atp' },
            '#table_impl_all_drm': { tab: 'all_drm' }
        };

        function getActiveImplTableSelector() {
            var href = $('#impl-monitor-tab .nav-link.active').attr('href') || '#impl-ny-rfs-pane';
            if (href === '#impl-ny-atp-pane') {
                return '#table_impl_ny_atp';
            }
            if (href === '#impl-all-drm-pane') {
                return '#table_impl_all_drm';
            }
            return '#table_impl_ny_rfs';
        }

        function updateImplStatusCounts(tab) {
            var summary = implStatusSummaryByTab[tab] || {};
            $('[data-impl-status-count]').each(function () {
                var key = String($(this).data('impl-status-count') || '');
                var value = Number(summary[key] || 0);
                $(this).text(value.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
            });
        }

        function applyImplStatusFilterToTable(table) {
            if (!table) {
                return;
            }

            var keyword = '';
            if (implStatusFilter === 'on_progress') {
                keyword = 'ON PROGRESS';
            } else if (implStatusFilter === 'not_started') {
                keyword = 'NOT STARTED';
            }

            table.column(5).search(keyword, false, false).draw();
        }

        function syncImplStatusFilterButtons() {
            $('.js-impl-status-filter').each(function () {
                $(this).toggleClass('is-active', String($(this).data('impl-status') || '').trim() === implStatusFilter);
            });
        }

        if ($.fn.DataTable) {
            Object.keys(implTableConfigs).forEach(function (selector) {
                if (!$(selector).length) {
                    return;
                }

                try {
                    implTables[selector] = $(selector).DataTable({
                        responsive: false,
                        scrollX: true,
                        scrollCollapse: true,
                        autoWidth: false,
                        order: [[0, 'asc']],
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        language: {
                            emptyTable: 'Belum ada data untuk tab ini.',
                            search: 'Search:',
                            lengthMenu: 'Tampilkan _MENU_ data',
                            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                            infoEmpty: 'Menampilkan 0 data',
                            paginate: {
                                previous: 'Previous',
                                next: 'Next'
                            }
                        }
                    });
                } catch (err) {
                    console.error('DataTable Implementasi BOQ gagal diinisialisasi:', selector, err);
                }
            });

            $('a[data-toggle="tab"][href^="#impl-"]').on('shown.bs.tab', function () {
                var tableSelector = getActiveImplTableSelector();
                var tabKey = implTableConfigs[tableSelector] ? implTableConfigs[tableSelector].tab : 'ny_rfs';
                updateImplStatusCounts(tabKey);

                Object.keys(implTables).forEach(function (selector) {
                    implTables[selector].columns.adjust();
                });
                applyImplStatusFilterToTable(implTables[tableSelector]);
            });

            $('.js-impl-status-filter').on('click', function () {
                var nextStatus = String($(this).data('impl-status') || '').trim();
                implStatusFilter = implStatusFilter === nextStatus ? '' : nextStatus;
                syncImplStatusFilterButtons();
                applyImplStatusFilterToTable(implTables[getActiveImplTableSelector()]);
            });
        }
    });
</script>
