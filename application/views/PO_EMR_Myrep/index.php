<?php
$flashError = $this->session->flashdata('error');

if (!function_exists('poEmrNumber')) {
    function poEmrNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('poEmrNumberOrDash')) {
    function poEmrNumberOrDash($value)
    {
        return (float) $value == 0.0 ? '-' : poEmrNumber($value);
    }
}

$selectedRegionalValues = is_array($selectedRegional) ? array_values($selectedRegional) : array_filter([strtoupper(trim((string) $selectedRegional))]);
$selectedCityValues = is_array($selectedCity) ? array_values($selectedCity) : array_filter([strtoupper(trim((string) $selectedCity))]);
$selectedStageValues = is_array($selectedStage) ? array_values($selectedStage) : array_filter([strtoupper(trim((string) $selectedStage))]);
$downloadParams = [];
if (!empty($selectedRegionalValues)) {
    $downloadParams['regional'] = $selectedRegionalValues;
}
if (!empty($selectedCityValues)) {
    $downloadParams['city'] = $selectedCityValues;
}
if (!empty($selectedStageValues)) {
    $downloadParams['stage'] = $selectedStageValues;
}
$downloadUrl = base_url('PO_EMR_Myrep/downloadReport') . (!empty($downloadParams) ? '?' . http_build_query($downloadParams) : '');

$currentQueryParams = [];
if (!empty($_SERVER['QUERY_STRING'])) {
    parse_str((string) $_SERVER['QUERY_STRING'], $currentQueryParams);
}
unset($currentQueryParams['back']);
$currentListUrl = base_url('PO_EMR_Myrep') . (!empty($currentQueryParams) ? '?' . http_build_query($currentQueryParams) : '');
$detailBackQuery = '?back=' . rawurlencode($currentListUrl);
?>

<style>
    .emr-page-title {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .emr-page-title h1 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        text-align: left;
    }

    .emr-page-title p {
        margin: .25rem 0 0;
        color: #64748b;
        font-weight: 600;
    }

    .emr-kpi {
        min-height: 112px;
        overflow: hidden;
    }

    #table_po_emr_monitor th,
    #table_po_emr_monitor td,
    #table_po_emr_target th,
    #table_po_emr_target td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table_po_emr_monitor thead th,
    #table_po_emr_target thead th {
        text-align: center;
    }

    .po-mini-progress__head {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: .35rem;
    }

    .po-mini-progress__track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .po-mini-progress__track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #14b8a6);
    }

    .po-mini-progress__meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .78rem;
        color: #64748b;
        margin-top: .35rem;
    }

    .po-progress-cell {
        min-width: 240px;
    }

    .emr-breakdown-table th,
    .emr-breakdown-table td {
        white-space: nowrap;
        vertical-align: middle;
    }

    .emr-breakdown-table thead th {
        text-align: center;
        font-weight: 800;
    }

    .emr-breakdown-table tfoot th {
        font-weight: 800;
        background: #f8fafc;
    }

    .emr-filter-select + .select2-container .select2-selection--multiple {
        min-height: calc(2.25rem + 2px);
    }

    .emr-filter-select + .select2-container .select2-selection__choice {
        margin-top: .28rem;
    }
</style>

<div class="emr-page-title">
    <div>
        <h1>OUTSTANDING TARGET PO EMR</h1>
    </div>
    <a href="<?= $downloadUrl ?>" class="btn btn-success">
        <i class="fas fa-file-excel mr-1"></i> Download Report
    </a>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!$isReady): ?>
    <div class="alert alert-warning">Tabel PO MyRep atau kolom on_target belum tersedia. Jalankan patch database terlebih dahulu.</div>
<?php else: ?>
    <div class="row">
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-info emr-kpi">
                <div class="inner">
                    <h3><?= (int) ($summary['total_po'] ?? 0) ?></h3>
                    <p>Total PO Target</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-primary emr-kpi">
                <div class="inner">
                    <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                    <p>Total Cluster</p>
                </div>
                <div class="icon"><i class="fas fa-network-wired"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-secondary emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.45rem;"><?= poEmrNumber((float) ($summary['total_po_value'] ?? 0)) ?></h3>
                    <p>Total Nilai PO</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.45rem;"><?= poEmrNumber((float) ($summary['total_outstanding'] ?? 0)) ?></h3>
                    <p>Outstanding</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-success emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.35rem;"><?= poEmrNumber((float) ($summary['total_invoiced'] ?? 0)) ?></h3>
                    <p>Total Invoiced</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Data</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row" id="po-emr-filter-form">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Regional</label>
                        <select name="regional[]" id="po-emr-filter-regional" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Regional" multiple>
                            <?php foreach ($regionalOptions as $regional): ?>
                                <option value="<?= htmlspecialchars((string) $regional, ENT_QUOTES, 'UTF-8') ?>" <?= in_array(strtoupper((string) $regional), $selectedRegionalValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $regional, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kota</label>
                        <select name="city[]" id="po-emr-filter-city" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Kota" multiple>
                            <?php foreach ($cityOptions as $city): ?>
                                <option value="<?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>" <?= in_array(strtoupper((string) $city), $selectedCityValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Stage</label>
                        <select name="stage[]" id="po-emr-filter-stage" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Stage" multiple>
                            <?php foreach (['DP', 'ATP CW', 'FULL OPM', 'RFS', 'FAC'] as $stageOption): ?>
                                <option value="<?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($stageOption, $selectedStageValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Pembagian Termin (Cluster &amp; Subfeeder)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 emr-breakdown-table">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tipe PO</th>
                            <th rowspan="2">PO QTY</th>
                            <th rowspan="2">Total PO</th>
                            <th colspan="5">Outstanding</th>
                            <th rowspan="2">Total Invoiced</th>
                            <th rowspan="2">Outstanding Total</th>
                        </tr>
                        <tr>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                            <th>4</th>
                            <th>5</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sumTotalPo = 0;
                        $sumTermDone = 0;
                        $sumTotalInvoiced = 0;
                        $sumOutstanding = 0;
                        $sumTermin = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        ?>
                        <?php foreach ($terminBreakdownRows as $index => $row): ?>
                            <?php
                            $sumTotalPo += (float) ($row['total_po_value'] ?? 0);
                            $sumTermDone += (int) ($row['term_done_count'] ?? 0);
                            $sumTotalInvoiced += (float) ($row['total_invoiced_value'] ?? 0);
                            $sumOutstanding += (float) ($row['outstanding_value'] ?? 0);
                            for ($i = 1; $i <= 5; $i++) {
                                $sumTermin[$i] += (float) ($row['termin_values'][$i] ?? 0);
                            }
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars((string) ($row['po_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['total_po_value'] ?? 0)) ?></td>
                                <td class="text-center"><?= (int) ($row['term_done_count'] ?? 0) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][1] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][2] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][3] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][4] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][5] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['total_invoiced_value'] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['outstanding_value'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($terminBreakdownRows)): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">Belum ada data pembagian termin.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($terminBreakdownRows)): ?>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">TOTAL</th>
                                <th class="text-right"><?= poEmrNumber($sumTotalPo) ?></th>
                                <th class="text-center"><?= (int) $sumTermDone ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[1]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[2]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[3]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[4]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[5]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTotalInvoiced) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumOutstanding) ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Monitoring PO MyRep</h3>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="po-emr-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="po-emr-monitor-tab" data-toggle="pill" href="#po-emr-monitor-pane" role="tab" aria-controls="po-emr-monitor-pane" aria-selected="true">Monitoring Cluster</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="po-emr-list-tab" data-toggle="pill" href="#po-emr-list-pane" role="tab" aria-controls="po-emr-list-pane" aria-selected="false">List PO</a>
                </li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="po-emr-monitor-pane" role="tabpanel" aria-labelledby="po-emr-monitor-tab">
                    <div class="table-responsive">
                        <table id="table_po_emr_monitor" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Cluster</th>
                                    <th>Kota</th>
                                    <th>Regional</th>
                                    <th>Status Flow</th>
                                    <th>PO</th>
                                    <th>Nilai PO</th>
                                    <th>Progress Termin</th>
                                    <th>Last PO</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clusterRows as $index => $row): ?>
                                    <?php
                                    $summaryStatus = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
                                    $statusBadgeClass = $summaryStatus === 'DP' ? 'danger' : ($summaryStatus === 'ATP CW' ? 'warning' : ($summaryStatus === 'FULL OPM' ? 'info' : ($summaryStatus === 'RFS' ? 'primary' : ($summaryStatus === 'FAC' ? 'success' : 'secondary'))));
                                    $terminTotal = (int) ($row['termin_total_count'] ?? 0);
                                    $terminProgress = (int) ($row['termin_progress_count'] ?? $row['termin_paid_count'] ?? 0);
                                    $terminPercent = $terminTotal > 0 ? min(100, round(($terminProgress / $terminTotal) * 100)) : 0;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <strong>
                                                <a href="<?= base_url('PO_EMR_Myrep/detail/' . (int) $row['id_myrep_cluster']) . $detailBackQuery ?>">
                                                    <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            </strong>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($row['team_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <div>Cluster: <?= (int) ($row['po_cluster_count'] ?? 0) ?></div>
                                            <div>Subfeeder: <?= (int) ($row['po_subfeeder_count'] ?? 0) ?></div>
                                            <div><span class="badge badge-<?= $statusBadgeClass ?>"><?= htmlspecialchars($summaryStatus, ENT_QUOTES, 'UTF-8') ?></span></div>
                                        </td>
                                        <td class="text-right"><?= poEmrNumber((float) ($row['po_total_value'] ?? 0)) ?></td>
                                        <td class="po-progress-cell">
                                            <div class="po-mini-progress">
                                                <div class="po-mini-progress__head">
                                                    <span>Termin Billed/Paid</span>
                                                    <span><?= $terminPercent ?>%</span>
                                                </div>
                                                <div class="po-mini-progress__track">
                                                    <span style="width: <?= $terminPercent ?>%;"></span>
                                                </div>
                                                <div class="po-mini-progress__meta">
                                                    <span><?= $terminProgress ?> billed/paid</span>
                                                    <span><?= $terminTotal ?> termin</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= !empty($row['last_po_date']) ? htmlspecialchars((string) $row['last_po_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('PO_EMR_Myrep/detail/' . (int) $row['id_myrep_cluster']) . $detailBackQuery ?>" class="btn btn-sm btn-primary">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($clusterRows)): ?>
                                    <tr><td colspan="10" class="text-center text-muted">Belum ada data PO MyRep.</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">TOTAL</th>
                                    <th>
                                        <div>Cluster: <span id="po-emr-footer-cluster-count">0</span></div>
                                        <div>Subfeeder: <span id="po-emr-footer-subfeeder-count">0</span></div>
                                    </th>
                                    <th class="text-right" id="po-emr-footer-nilai-po">0</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="po-emr-list-pane" role="tabpanel" aria-labelledby="po-emr-list-tab">
                    <div class="table-responsive">
                <table id="table_po_emr_target" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>PO</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Tanggal PO</th>
                            <th>Cluster</th>
                            <th>Kota</th>
                            <th>Regional</th>
                            <th>Stage</th>
                            <th>Nilai PO</th>
                            <th>Progress</th>
                            <th>Outstanding</th>
                            <th>Total Invoiced</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poRows as $index => $row): ?>
                            <?php
                            $tipePo = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
                            $stage = strtoupper(trim((string) ($row['po_stage_status'] ?? '-')));
                            $badgeClass = $stage === 'DP' ? 'danger' : ($stage === 'ATP CW' ? 'warning' : ($stage === 'FULL OPM' ? 'info' : ($stage === 'RFS' ? 'primary' : ($stage === 'FAC' ? 'success' : 'secondary'))));
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <strong>
                                        <a href="<?= base_url('PO_EMR_Myrep/detail/' . (int) $row['id_myrep_cluster']) . $detailBackQuery ?>">
                                            <?= htmlspecialchars((string) ($row['po_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </strong>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['status_po'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $tipePo === 'SUBFEEDER' ? 'warning' : 'primary' ?>">
                                        <?= htmlspecialchars($tipePo, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= htmlspecialchars((string) ($row['po_category'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><?= !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                <td><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($stage, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['po_value'] ?? 0)) ?></td>
                                <td class="text-center"><?= (int) ($row['termin_progress_count'] ?? 0) ?>/<?= (int) ($row['termin_total_count'] ?? 0) ?></td>
                                <td class="text-right"><?= poEmrNumberOrDash((float) ($row['outstanding_total'] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumberOrDash((float) ($row['total_invoiced'] ?? 0)) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('PO_EMR_Myrep/detail/' . (int) $row['id_myrep_cluster']) . $detailBackQuery ?>" class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($poRows)): ?>
                            <tr><td colspan="14" class="text-center text-muted">Belum ada PO yang ditandai on target.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    $(function () {
        var tableMonitor = null;
        var tableTarget = null;
        var cityOptionsByRegional = <?= json_encode($cityOptionsByRegional ?? [], JSON_UNESCAPED_SLASHES) ?> || {};
        var regionalOptionsByCity = <?= json_encode($regionalOptionsByCity ?? [], JSON_UNESCAPED_SLASHES) ?> || {};
        var allCityOptions = <?= json_encode($allCityOptions ?? [], JSON_UNESCAPED_SLASHES) ?> || [];
        var allRegionalOptions = <?= json_encode($regionalOptions ?? [], JSON_UNESCAPED_SLASHES) ?> || [];
        var isUpdatingFilter = false;
        var activeTabStorageKey = 'po_emr_myrep_active_tab';
        var activeTabSelector = '';
        var filterSubmitTimer = null;

        try {
            activeTabSelector = sessionStorage.getItem(activeTabStorageKey) || '';
        } catch (error) {
            activeTabSelector = '';
        }

        function toValueArray(value) {
            if ($.isArray(value)) {
                return value.map(function (item) {
                    return String(item || '');
                }).filter(function (item) {
                    return item !== '';
                });
            }

            value = String(value || '');
            return value ? [value] : [];
        }

        function uniqueOptions(options) {
            var seen = {};
            var unique = [];
            (options || []).forEach(function (value) {
                value = String(value || '');
                if (value && !seen[value]) {
                    seen[value] = true;
                    unique.push(value);
                }
            });
            return unique;
        }

        function optionsFromMap(selectedValues, optionMap, fallbackOptions) {
            selectedValues = toValueArray(selectedValues);
            if (!selectedValues.length) {
                return fallbackOptions || [];
            }

            var options = [];
            selectedValues.forEach(function (selectedValue) {
                options = options.concat(optionMap[selectedValue] || []);
            });

            return uniqueOptions(options);
        }

        function rebuildOptions($select, options, emptyLabel, selectedValue) {
            var selectedValues = toValueArray(selectedValue);
            var isMultiple = $select.prop('multiple');
            $select.empty();
            if (!isMultiple) {
                $select.append($('<option>').attr('value', '').text(emptyLabel));
            }

            (options || []).forEach(function (value) {
                var label = String(value || '');
                if (!label) {
                    return;
                }
                $select.append($('<option>').attr('value', label).text(label));
            });

            selectedValues = selectedValues.filter(function (selected) {
                return (options || []).indexOf(selected) !== -1;
            });

            if (isMultiple) {
                $select.val(selectedValues);
            } else if (selectedValues.length) {
                $select.val(selectedValues[0]);
            } else {
                $select.val('');
            }
            $select.trigger('change.select2');
        }

        function submitFilter() {
            clearTimeout(filterSubmitTimer);
            filterSubmitTimer = setTimeout(function () {
                $('#po-emr-filter-form').trigger('submit');
            }, 650);
        }

        if ($.fn.select2) {
            $('.js-po-emr-searchable').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                closeOnSelect: false,
                placeholder: function () {
                    return $(this).data('placeholder') || '';
                }
            });
        }

        $('#po-emr-filter-regional').on('change', function () {
            if (isUpdatingFilter) {
                return;
            }

            isUpdatingFilter = true;
            var regional = toValueArray($(this).val());
            var currentCity = toValueArray($('#po-emr-filter-city').val());
            var cityOptions = optionsFromMap(regional, cityOptionsByRegional, allCityOptions);
            rebuildOptions($('#po-emr-filter-city'), cityOptions, 'Semua Kota', currentCity);
            isUpdatingFilter = false;
            submitFilter();
        });

        $('#po-emr-filter-city').on('change', function () {
            if (isUpdatingFilter) {
                return;
            }

            isUpdatingFilter = true;
            var city = toValueArray($(this).val());
            var currentRegional = toValueArray($('#po-emr-filter-regional').val());
            var regionalOptions = optionsFromMap(city, regionalOptionsByCity, allRegionalOptions);
            rebuildOptions($('#po-emr-filter-regional'), regionalOptions, 'Semua Regional', currentRegional);
            isUpdatingFilter = false;
            submitFilter();
        });

        $('#po-emr-filter-stage').on('change', function () {
            submitFilter();
        });

        function parseLocaleNumber(value) {
            if (typeof value === 'number') {
                return isNaN(value) ? 0 : value;
            }
            var cleaned = $('<div>').html(value || '').text();
            cleaned = String(cleaned).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.-]/g, '');
            var parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        }

        function extractPoCount(value, label) {
            var text = $('<div>').html(value || '').text();
            var regex = new RegExp(label + '\\s*:\\s*([0-9]+)', 'i');
            var match = text.match(regex);
            return match ? parseInt(match[1], 10) : 0;
        }

        if ($.fn.DataTable && $('#table_po_emr_monitor').length) {
            tableMonitor = $('#table_po_emr_monitor').DataTable({
                responsive: true,
                autoWidth: false,
                stateSave: true,
                stateDuration: -1,
                pageLength: 10,
                order: [[0, 'asc']],
                footerCallback: function () {
                    var api = this.api();
                    var totalNilaiPo = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                        return parseLocaleNumber(a) + parseLocaleNumber(b);
                    }, 0);
                    var totalPoCluster = api.column(5, { page: 'current' }).data().reduce(function (acc, value) {
                        return acc + extractPoCount(value, 'Cluster');
                    }, 0);
                    var totalPoSubfeeder = api.column(5, { page: 'current' }).data().reduce(function (acc, value) {
                        return acc + extractPoCount(value, 'Subfeeder');
                    }, 0);

                    $('#po-emr-footer-cluster-count').text(totalPoCluster.toLocaleString('id-ID'));
                    $('#po-emr-footer-subfeeder-count').text(totalPoSubfeeder.toLocaleString('id-ID'));
                    $('#po-emr-footer-nilai-po').text(totalNilaiPo.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                }
            });
        }

        if ($.fn.DataTable && $('#table_po_emr_target').length) {
            tableTarget = $('#table_po_emr_target').DataTable({
                responsive: false,
                scrollX: true,
                autoWidth: false,
                stateSave: true,
                stateDuration: -1,
                pageLength: 10,
                order: [[4, 'desc']]
            });
        }

        $('a[data-toggle="pill"]').on('shown.bs.tab', function (event) {
            var tabTarget = $(event.target).attr('href') || '';
            if (tabTarget) {
                try {
                    sessionStorage.setItem(activeTabStorageKey, tabTarget);
                } catch (error) {
                    // Browser storage may be disabled.
                }
            }
            if (tableMonitor) {
                tableMonitor.columns.adjust().responsive.recalc();
            }
            if (tableTarget) {
                tableTarget.columns.adjust();
            }
        });

        if (activeTabSelector && $('#po-emr-tabs a[href="' + activeTabSelector + '"]').length) {
            $('#po-emr-tabs a[href="' + activeTabSelector + '"]').tab('show');
        }
    });
</script>
