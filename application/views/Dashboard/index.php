<?php
if (!function_exists('dashboard_number')) {
    function dashboard_number($value, $decimal = 0)
    {
        return number_format((float) $value, (int) $decimal, ',', '.');
    }
}

if (!function_exists('dashboard_money')) {
    function dashboard_money($value)
    {
        return 'Rp ' . dashboard_number($value);
    }
}

if (!function_exists('dashboard_compact_money')) {
    function dashboard_compact_money($value)
    {
        $value = (float) $value;
        $abs = abs($value);
        if ($abs >= 1000000000) {
            return 'Rp ' . dashboard_number($value / 1000000000, 1) . ' M';
        }
        if ($abs >= 1000000) {
            return 'Rp ' . dashboard_number($value / 1000000, 1) . ' Jt';
        }
        return dashboard_money($value);
    }
}

if (!function_exists('dashboard_percent')) {
    function dashboard_percent($value)
    {
        return dashboard_number($value, 1) . '%';
    }
}

$overview = $overview ?? [];
$stageSummary = $stageSummary ?? [];
$annualSummary = $annualSummary ?? [];
$batchSummary = $batchSummary ?? [];
$topCities = $topCities ?? [];
$citySummary = $citySummary ?? [];
$chartPayload = $chartPayload ?? [];
$achievementMyRep = (float) ($annualSummary['pct_tkm'] ?? 0);
$batchReleaseRatio = (float) ($batchSummary['release_ratio'] ?? 0);
$chartJson = json_encode($chartPayload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>

<div class="content-wrapper myrep-dashboard">
    <div class="content-header">
        <div class="container-fluid">
            <div class="myrep-hero">
                <div>
                    <span class="myrep-eyebrow">Dashboard Project</span>
                    <h1>My Republik</h1>
                    <p>Monitoring cluster, HP, PO, batch approval, dan target RFS tahun <?= (int) ($dashboardYear ?? date('Y')) ?>.</p>
                </div>
                <div class="myrep-hero__metric">
                    <span>Pencapaian MyRep</span>
                    <strong><?= dashboard_percent($achievementMyRep) ?></strong>
                    <div class="myrep-progress" aria-hidden="true">
                        <div style="width: <?= max(0, min($achievementMyRep, 100)) ?>%;"></div>
                    </div>
                    <small><?= dashboard_number($annualSummary['realization_tkm'] ?? 0) ?> dari <?= dashboard_number($annualSummary['target_tkm'] ?? 0) ?> HP TKM</small>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (empty($isReady)) : ?>
                <div class="alert alert-warning">Tabel dashboard My Republik belum tersedia.</div>
            <?php endif; ?>

            <div class="myrep-kpi-grid">
                <a href="<?= base_url('MyRepublik_Project') ?>" class="myrep-kpi myrep-kpi--teal">
                    <span>Total Cluster</span>
                    <strong><?= dashboard_number($overview['total_cluster'] ?? 0) ?></strong>
                    <small><?= dashboard_number($overview['total_hp'] ?? 0) ?> HP pipeline</small>
                </a>
                <a href="<?= base_url('PO_MyRep') ?>" class="myrep-kpi myrep-kpi--indigo">
                    <span>Total PO</span>
                    <strong><?= dashboard_compact_money($overview['total_po'] ?? 0) ?></strong>
                    <small><?= dashboard_number($batchSummary['clusters'] ?? 0) ?> cluster batch</small>
                </a>
                <a href="<?= base_url('Monitoring_RFS_MyRep') ?>" class="myrep-kpi myrep-kpi--amber">
                    <span>RFS / ATP</span>
                    <strong><?= dashboard_number($overview['total_rfs'] ?? 0) ?> / <?= dashboard_number($overview['total_atp'] ?? 0) ?></strong>
                    <small><?= dashboard_number($annualSummary['realization_tkm'] ?? 0) ?> HP claim TKM</small>
                </a>
                <a href="<?= base_url('Batch_Approval_MyRep') ?>" class="myrep-kpi myrep-kpi--rose">
                    <span>Release Batch</span>
                    <strong><?= dashboard_percent($batchReleaseRatio) ?></strong>
                    <small><?= dashboard_compact_money($batchSummary['nominal_release'] ?? 0) ?> released</small>
                </a>
            </div>

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Target vs Realisasi</span>
                                <h3>Tren Bulanan MyRep</h3>
                            </div>
                            <a href="<?= base_url('Monitoring_RFS_MyRep') ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-chart-line"></i> Monitoring RFS
                            </a>
                        </div>
                        <div class="myrep-chart myrep-chart--large">
                            <canvas id="myrepMonthlyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Status Cluster</span>
                                <h3>Distribusi Flow</h3>
                            </div>
                        </div>
                        <div class="myrep-chart">
                            <canvas id="myrepStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-xl-7">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Homepass</span>
                                <h3>HP per Stage</h3>
                            </div>
                        </div>
                        <div class="myrep-chart">
                            <canvas id="myrepStageChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Kota</span>
                                <h3>Top HP RFS</h3>
                            </div>
                        </div>
                        <div class="myrep-chart">
                            <canvas id="myrepCityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-xl-4">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Batch Approval</span>
                                <h3>Released vs Waiting</h3>
                            </div>
                        </div>
                        <div class="myrep-chart myrep-chart--small">
                            <canvas id="myrepBatchChart"></canvas>
                        </div>
                        <div class="myrep-split">
                            <div>
                                <span>Nominal Nego</span>
                                <strong><?= dashboard_compact_money($batchSummary['nominal_emr'] ?? 0) ?></strong>
                            </div>
                            <div>
                                <span>HP Donasi</span>
                                <strong><?= dashboard_number($batchSummary['hp_donasi'] ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-8">
                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Akses Cepat</span>
                                <h3>Modul My Republik</h3>
                            </div>
                        </div>
                        <div class="myrep-link-grid">
                            <a href="<?= base_url('MyRepublik_Project') ?>"><i class="fas fa-project-diagram"></i><span>Project MyRep</span></a>
                            <a href="<?= base_url('BAK_MyRep') ?>"><i class="fas fa-file-signature"></i><span>BAK</span></a>
                            <a href="<?= base_url('VALSAL_MyRep') ?>"><i class="fas fa-check-circle"></i><span>VALSAL</span></a>
                            <a href="<?= base_url('Batch_Approval_MyRep') ?>"><i class="fas fa-layer-group"></i><span>Batch Approval</span></a>
                            <a href="<?= base_url('DRM_MyRep') ?>"><i class="fas fa-network-wired"></i><span>DRM</span></a>
                            <a href="<?= base_url('Implementasi_BOQ_MyRep') ?>"><i class="fas fa-tools"></i><span>Implementasi BOQ</span></a>
                            <a href="<?= base_url('Monitoring_RFS_MyRep') ?>"><i class="fas fa-signal"></i><span>Monitoring RFS</span></a>
                            <a href="<?= base_url('Checklist_Dokument_MyRep') ?>"><i class="fas fa-clipboard-check"></i><span>Checklist Dokument</span></a>
                        </div>
                    </div>

                    <div class="myrep-panel">
                        <div class="myrep-panel__head">
                            <div>
                                <span class="myrep-eyebrow">Ringkasan Kota</span>
                                <h3>Top 8 Berdasarkan HP RFS</h3>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm myrep-table">
                                <thead>
                                    <tr>
                                        <th>Kota</th>
                                        <th class="text-right">Cluster</th>
                                        <th class="text-right">HP RFS</th>
                                        <th class="text-right">PO</th>
                                        <th class="text-right">RFS</th>
                                        <th class="text-right">ATP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topCities)) : ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada data MyRep.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($topCities as $cityRow) : ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars((string) ($cityRow['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td class="text-right"><?= dashboard_number($cityRow['cluster_count'] ?? 0) ?></td>
                                            <td class="text-right"><?= dashboard_number($cityRow['hp_rfs_total'] ?? 0) ?></td>
                                            <td class="text-right"><?= dashboard_compact_money($cityRow['po_total'] ?? 0) ?></td>
                                            <td class="text-right"><?= dashboard_number($cityRow['rfs_count'] ?? 0) ?></td>
                                            <td class="text-right"><?= dashboard_number($cityRow['atp_count'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .myrep-dashboard {
        background: #f4f7fb;
        color: #172033;
    }

    .myrep-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
        gap: 16px;
        align-items: stretch;
        margin: 10px 0 4px;
        padding: 22px;
        border-radius: 8px;
        background: linear-gradient(135deg, #123049 0%, #146f7a 58%, #e3a52f 100%);
        color: #fff;
        box-shadow: 0 16px 34px rgba(18, 48, 73, 0.16);
    }

    .myrep-hero h1 {
        margin: 4px 0 8px;
        font-size: 2.1rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .myrep-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.86);
        font-size: 1rem;
    }

    .myrep-eyebrow {
        display: block;
        color: #66758d;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .myrep-hero .myrep-eyebrow {
        color: rgba(255, 255, 255, 0.76);
    }

    .myrep-hero__metric {
        padding: 16px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.24);
    }

    .myrep-hero__metric span,
    .myrep-hero__metric small {
        display: block;
        color: rgba(255, 255, 255, 0.82);
        font-weight: 700;
    }

    .myrep-hero__metric strong {
        display: block;
        margin: 6px 0 10px;
        font-size: 2rem;
        line-height: 1;
    }

    .myrep-progress {
        height: 10px;
        margin-bottom: 9px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.22);
        overflow: hidden;
    }

    .myrep-progress div {
        height: 100%;
        border-radius: inherit;
        background: #f6c557;
    }

    .myrep-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .myrep-kpi,
    .myrep-panel {
        border-radius: 8px;
        border: 1px solid #dfe7ef;
        background: #fff;
        box-shadow: 0 10px 24px rgba(33, 45, 70, 0.06);
    }

    .myrep-kpi {
        display: block;
        padding: 16px;
        color: #182236;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .myrep-kpi:hover,
    .myrep-link-grid a:hover {
        color: #182236;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(33, 45, 70, 0.1);
    }

    .myrep-kpi span {
        display: block;
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .myrep-kpi strong {
        display: block;
        margin-top: 7px;
        font-size: 1.55rem;
        line-height: 1.1;
        font-weight: 800;
    }

    .myrep-kpi small {
        display: block;
        margin-top: 8px;
        color: #64748b;
        font-weight: 600;
    }

    .myrep-kpi--teal { border-top: 4px solid #0f8b72; }
    .myrep-kpi--indigo { border-top: 4px solid #3554d1; }
    .myrep-kpi--amber { border-top: 4px solid #d58b16; }
    .myrep-kpi--rose { border-top: 4px solid #c8465c; }

    .myrep-panel {
        margin-bottom: 16px;
        padding: 16px;
    }

    .myrep-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .myrep-panel h3 {
        margin: 3px 0 0;
        color: #172033;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .myrep-chart {
        position: relative;
        height: 300px;
        min-height: 300px;
    }

    .myrep-chart--large {
        height: 340px;
    }

    .myrep-chart--small {
        height: 230px;
        min-height: 230px;
    }

    .myrep-split {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .myrep-split div,
    .myrep-link-grid a {
        border-radius: 8px;
        border: 1px solid #e3eaf2;
        background: #f8fafc;
    }

    .myrep-split div {
        padding: 12px;
    }

    .myrep-split span {
        display: block;
        color: #64748b;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .myrep-split strong {
        display: block;
        margin-top: 4px;
        color: #182236;
        font-size: 1rem;
    }

    .myrep-link-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .myrep-link-grid a {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 58px;
        padding: 12px;
        color: #172033;
        font-weight: 800;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .myrep-link-grid i {
        width: 28px;
        min-width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        color: #fff;
        background: #146f7a;
        font-size: 0.84rem;
    }

    .myrep-table {
        margin-bottom: 0;
    }

    .myrep-table thead th {
        border-top: 0;
        border-bottom: 1px solid #dfe7ef;
        color: #64748b;
        font-size: 0.78rem;
        text-transform: uppercase;
    }

    .myrep-table td {
        vertical-align: middle;
        border-color: #eef2f6;
    }

    @media (max-width: 1199.98px) {
        .myrep-kpi-grid,
        .myrep-link-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .myrep-hero,
        .myrep-kpi-grid,
        .myrep-link-grid,
        .myrep-split {
            grid-template-columns: 1fr;
        }

        .myrep-hero {
            padding: 16px;
        }

        .myrep-hero h1 {
            font-size: 1.65rem;
        }

        .myrep-panel__head {
            flex-direction: column;
        }

        .myrep-chart,
        .myrep-chart--large {
            height: 280px;
            min-height: 280px;
        }
    }
</style>

<script>
    $(function() {
        var payload = <?= $chartJson ?: '{}' ?> || {};
        var palette = ['#146f7a', '#3554d1', '#d58b16', '#c8465c', '#6f7b8f', '#0f8b72', '#7c4dff', '#c4681d', '#287c9b', '#9b4d68'];

        function valuesOrFallback(labels, values) {
            labels = Array.isArray(labels) && labels.length ? labels : ['Belum ada data'];
            values = Array.isArray(values) && values.length ? values : [0];
            return { labels: labels, values: values };
        }

        function axisNumber(value) {
            value = Number(value || 0);
            return value.toLocaleString('id-ID');
        }

        function makeChart(id, config) {
            var canvas = document.getElementById(id);
            if (!canvas || typeof Chart === 'undefined') {
                return null;
            }
            return new Chart(canvas.getContext('2d'), config);
        }

        var monthly = payload.monthly || {};
        makeChart('myrepMonthlyChart', {
            type: 'bar',
            data: {
                labels: monthly.labels || [],
                datasets: [{
                    label: 'Target MyRep',
                    backgroundColor: 'rgba(20, 111, 122, 0.22)',
                    borderColor: '#146f7a',
                    borderWidth: 1,
                    data: monthly.target_myrep || []
                }, {
                    label: 'Realisasi MyRep',
                    backgroundColor: '#d58b16',
                    borderColor: '#d58b16',
                    borderWidth: 1,
                    data: monthly.realization_myrep || []
                }, {
                    label: 'Realisasi TKM',
                    type: 'line',
                    fill: false,
                    borderColor: '#3554d1',
                    pointBackgroundColor: '#3554d1',
                    lineTension: 0.25,
                    data: monthly.realization_tkm || []
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { position: 'bottom' },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, callback: axisNumber }, gridLines: { color: '#edf2f7' } }],
                    xAxes: [{ gridLines: { display: false } }]
                },
                tooltips: { callbacks: { label: function(item, data) { return data.datasets[item.datasetIndex].label + ': ' + axisNumber(item.yLabel); } } }
            }
        });

        var status = payload.status || {};
        var statusData = valuesOrFallback(status.labels, status.clusters);
        makeChart('myrepStatusChart', {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{ data: statusData.values, backgroundColor: palette }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { position: 'bottom' },
                cutoutPercentage: 62,
                tooltips: { callbacks: { label: function(item, data) { return data.labels[item.index] + ': ' + axisNumber(data.datasets[0].data[item.index]) + ' cluster'; } } }
            }
        });

        var stage = payload.stage || {};
        makeChart('myrepStageChart', {
            type: 'bar',
            data: {
                labels: stage.labels || [],
                datasets: [{ label: 'HP', backgroundColor: palette, data: stage.hp || [] }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, callback: axisNumber }, gridLines: { color: '#edf2f7' } }],
                    xAxes: [{ gridLines: { display: false } }]
                },
                tooltips: { callbacks: { label: function(item) { return axisNumber(item.yLabel) + ' HP'; } } }
            }
        });

        var city = payload.city || {};
        var cityData = valuesOrFallback(city.labels, city.hp);
        makeChart('myrepCityChart', {
            type: 'horizontalBar',
            data: {
                labels: cityData.labels,
                datasets: [{ label: 'HP', backgroundColor: '#146f7a', data: cityData.values }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { display: false },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true, callback: axisNumber }, gridLines: { color: '#edf2f7' } }],
                    yAxes: [{ gridLines: { display: false } }]
                },
                tooltips: { callbacks: { label: function(item) { return axisNumber(item.xLabel) + ' HP'; } } }
            }
        });

        var batch = payload.batch || {};
        var batchData = valuesOrFallback(batch.labels, batch.clusters);
        makeChart('myrepBatchChart', {
            type: 'doughnut',
            data: {
                labels: batchData.labels,
                datasets: [{ data: batchData.values, backgroundColor: ['#0f8b72', '#c8465c'] }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: { position: 'bottom' },
                cutoutPercentage: 66,
                tooltips: { callbacks: { label: function(item, data) { return data.labels[item.index] + ': ' + axisNumber(data.datasets[0].data[item.index]) + ' cluster'; } } }
            }
        });
    });
</script>
