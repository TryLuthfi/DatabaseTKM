<?php
$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';
$monthColumnCount = count($monthColumns);
$clusterTargetCityMap = [];
$monthlyTargetCityMap = [];
$monthlyTargetPeriodCityMap = [];

if (!empty($targetOptions)) {
    foreach ($targetOptions as $targetOption) {
        $cityKey = strtoupper(trim((string) ($targetOption['city_name'] ?? '')));
        if ($cityKey === '') {
            continue;
        }

        if (
            (int) ($targetOption['year_num'] ?? 0) === (int) $selectedYear &&
            (int) ($targetOption['month_num'] ?? 0) === (int) $selectedEndMonth
        ) {
            $monthlyTargetCityMap[$cityKey] = [
                'city_name' => (string) ($targetOption['city_name'] ?? ''),
                'regional_name' => (string) ($targetOption['regional_name'] ?? ''),
                'province_name' => (string) ($targetOption['province_name'] ?? ''),
                'team_name' => (string) ($targetOption['team_name'] ?? ''),
                'chief' => (string) ($targetOption['chief'] ?? ''),
                'rpm' => (string) ($targetOption['rpm'] ?? ''),
                'sm' => (string) ($targetOption['sm'] ?? ''),
                'spv' => (string) ($targetOption['spv'] ?? ''),
                'target_myrep' => (float) ($targetOption['target_myrep'] ?? 0),
                'realization_myrep' => (float) ($targetOption['realization_myrep'] ?? 0),
                'target_rkap' => (float) ($targetOption['target_rkap'] ?? 0)
            ];
        }

        if (
            !isset($clusterTargetCityMap[$cityKey]) ||
            (int) ($targetOption['month_num'] ?? 0) > (int) ($clusterTargetCityMap[$cityKey]['month_num'] ?? 0)
        ) {
            $clusterTargetCityMap[$cityKey] = [
                'id_target' => (int) ($targetOption['id_target'] ?? 0),
                'city_name' => (string) ($targetOption['city_name'] ?? ''),
                'month_num' => (int) ($targetOption['month_num'] ?? 0),
                'year_num' => (int) ($targetOption['year_num'] ?? 0),
                'rpm' => (string) ($targetOption['rpm'] ?? ''),
                'sm' => (string) ($targetOption['sm'] ?? ''),
                'spv' => (string) ($targetOption['spv'] ?? '')
            ];
        }
    }

    ksort($clusterTargetCityMap);
    ksort($monthlyTargetCityMap);
}

if (!empty($allTargetOptions)) {
    foreach ($allTargetOptions as $targetOption) {
        $cityKey = strtoupper(trim((string) ($targetOption['city_name'] ?? '')));
        $monthKey = (int) ($targetOption['month_num'] ?? 0);

        if ($cityKey === '' || $monthKey <= 0) {
            continue;
        }

        if (!isset($monthlyTargetPeriodCityMap[$monthKey])) {
            $monthlyTargetPeriodCityMap[$monthKey] = [];
        }

        $monthlyTargetPeriodCityMap[$monthKey][$cityKey] = [
            'city_name' => (string) ($targetOption['city_name'] ?? ''),
            'regional_name' => (string) ($targetOption['regional_name'] ?? ''),
            'province_name' => (string) ($targetOption['province_name'] ?? ''),
            'team_name' => (string) ($targetOption['team_name'] ?? ''),
            'chief' => (string) ($targetOption['chief'] ?? ''),
            'rpm' => (string) ($targetOption['rpm'] ?? ''),
            'sm' => (string) ($targetOption['sm'] ?? ''),
            'spv' => (string) ($targetOption['spv'] ?? ''),
            'target_myrep' => (float) ($targetOption['target_myrep'] ?? 0),
            'realization_myrep' => (float) ($targetOption['realization_myrep'] ?? 0),
            'target_rkap' => (float) ($targetOption['target_rkap'] ?? 0)
        ];
    }
}

if (!function_exists('monitoring_rfs_badge_class')) {
    function monitoring_rfs_badge_class($status)
    {
        switch ($status) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'WAITING APPROVAL RPM':
                return 'primary';
            case 'WAITING APPROVAL HO':
                return 'info';
            case 'SKIPPED':
                return 'secondary';
            default:
                return 'warning';
        }
    }
}

if (!function_exists('monitoring_rfs_tkm_percent_class')) {
    function monitoring_rfs_tkm_percent_class($percent)
    {
        $percent = (float) $percent;
        if ($percent <= 30) {
            return 'low';
        }
        if ($percent <= 70) {
            return 'medium';
        }

        return 'high';
    }
}
?>

<style>
    .table thead th[rowspan] {
        vertical-align: middle !important;
    }

    #modal-cluster-baru .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    #modal-target-bulanan .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    #modal-target-bulanan .modal-header {
        background: linear-gradient(135deg, #007bff, #6610f2);
        color: #fff;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    #modal-target-bulanan .modal-body {
        background-color: #f9fafc;
    }

    #modal-target-bulanan .modal-footer {
        background: #f1f3f6;
        border-top: 1px solid #dee2e6;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }

    #modal-cluster-baru .modal-header {
        background: linear-gradient(135deg, #28a745, #138496);
        color: #fff;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    #modal-cluster-baru .modal-body {
        background-color: #f9fafc;
    }

    #modal-cluster-baru .modal-footer {
        background: #f1f3f6;
        border-top: 1px solid #dee2e6;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }

    #table_manual_cluster_input .form-control {
        min-width: 120px;
    }

    #table_manual_city_master .form-control,
    #table_manual_target_batch .form-control {
        min-width: 120px;
    }

    #modal-cluster-baru .nav-tabs .nav-link,
    #modal-target-bulanan .nav-tabs .nav-link {
        font-weight: 600;
    }

    .cluster-dropzone {
        border: 2px dashed #17a2b8;
        border-radius: 12px;
        padding: 36px 24px;
        background: #f7fcfd;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cluster-dropzone.dragover {
        border-color: #007bff;
        background: #eef6ff;
        transform: scale(1.01);
    }

    .claim-rfs-modal .modal-content {
        border-radius: 18px;
        border: none;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
    }

    .claim-rfs-modal .modal-header {
        background: linear-gradient(135deg, #0f766e, #0ea5e9);
        color: #fff;
    }

    .claim-rfs-modal .modal-body {
        background: linear-gradient(180deg, #f8fbff, #f4f7fb);
    }

    .claim-summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        margin-bottom: 16px;
    }

    .claim-summary-card .summary-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .claim-summary-card .summary-value {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
    }

    .claim-photo-dropzone {
        border: 2px dashed #38bdf8;
        border-radius: 14px;
        background: #f0f9ff;
        padding: 24px 18px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .claim-photo-dropzone.dragover {
        border-color: #0284c7;
        background: #e0f2fe;
        transform: translateY(-1px);
    }

    .tkm-percent-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 700;
        white-space: nowrap;
    }

    .tkm-percent-indicator::before {
        content: '';
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }

    .tkm-percent-low {
        background: #fee2e2;
        color: #b91c1c;
    }

    .tkm-percent-low::before {
        background: #dc2626;
    }

    .tkm-percent-medium {
        background: #fef3c7;
        color: #b45309;
    }

    .tkm-percent-medium::before {
        background: #f59e0b;
    }

    .tkm-percent-high {
        background: #dcfce7;
        color: #15803d;
    }

    .tkm-percent-high::before {
        background: #16a34a;
    }

    .city-health-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        padding: 5px 10px;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .city-health-low {
        background: #fee2e2;
        color: #b91c1c;
    }

    .city-health-medium {
        background: #fef3c7;
        color: #b45309;
    }

    .city-health-high {
        background: #dcfce7;
        color: #15803d;
    }

    .rfs-header-myrep {
        background-color: #ede2ff;
        color: #5b3f8c;
    }

    .rfs-header-tkm {
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    .rfs-header-rkap {
        background-color: #fff3cd;
        color: #856404;
    }

    .rfs-header-realistis {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .rfs-header-pencapaian {
        background-color: #f8d7da;
        color: #721c24;
    }

    .rfs-header-fixed {
        background-color: #e2e3e5;
        color: #383d41;
    }

    .btn-gradient-success {
        background: linear-gradient(45deg, #28a745, #5cd65c);
        border: none;
        color: #fff;
        border-radius: 50px;
        transition: all 0.3s ease-in-out;
        padding: 12px 25px;
        letter-spacing: 1px;
    }

    /* Efek hover */
    .btn-gradient-success:hover {
        background: linear-gradient(45deg, #1e7e34, #3cbf3c);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    }

    /* Efek glowing lembut */
    .btn-gradient-success:focus,
    .btn-gradient-success:active {
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        outline: none;
    }

    /* Gradien biru menarik */
    .btn-gradient-primary {
        background: linear-gradient(45deg, #007bff, #00c6ff);
        border: none;
        color: #fff;
        border-radius: 50px;
        transition: all 0.3s ease-in-out;
        padding: 12px 25px;
        letter-spacing: 1px;
    }

    /* Efek hover */
    .btn-gradient-primary:hover {
        background: linear-gradient(45deg, #0056b3, #0099cc);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    /* Efek glowing lembut */
    .btn-gradient-primary:focus,
    .btn-gradient-primary:active {
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        outline: none;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">MONITORING RFS MYREP</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashMessage)) { ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashMessage) ?></div>
            <?php } ?>
            <?php if (!empty($flashError)) { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
            <?php } ?>

            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Filter Periode</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Monitoring_RFS_MyRep') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Tahun</label>
                                <input type="number" class="form-control" name="year" value="<?= (int) $selectedYear ?>"
                                    min="2024" max="2100">
                            </div>
                            <div class="col-md-3">
                                <label>Bulan Awal</label>
                                <select class="form-control" name="start_month">
                                    <?php foreach ($monthLabels as $monthNumber => $monthName) { ?>
                                        <option value="<?= $monthNumber ?>" <?= ((int) $selectedStartMonth === (int) $monthNumber) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($monthName) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Bulan Akhir</label>
                                <select class="form-control" name="end_month">
                                    <?php foreach ($monthLabels as $monthNumber => $monthName) { ?>
                                        <option value="<?= $monthNumber ?>" <?= ((int) $selectedEndMonth === (int) $monthNumber) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($monthName) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Kota</label>
                                <select class="form-control" name="city">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($cityOptions as $city) { ?>
                                        <option value="<?= htmlspecialchars($city) ?>" <?= ($selectedCity === $city) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($city) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="m-0 text-dark text-center">ANNUAL TARGET VS REALISASI</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">1. Annual Target vs Realisasi</h3>
                    <div class="card-tools">
                        <span class="badge badge-light">Januari - Desember | Semua Kota</span>
                        <button type="button" class="btn btn-tool ml-1" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_annual_summary" class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th colspan="4" class="rfs-header-myrep">MYREP</th>
                                <th colspan="4" class="rfs-header-tkm">TKM</th>
                                <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                            </tr>
                            <tr>
                                <th class="rfs-header-myrep">TARGET</th>
                                <th class="rfs-header-myrep">REALISASI</th>
                                <th class="rfs-header-myrep">SELISIH</th>
                                <th class="rfs-header-myrep">%</th>
                                <th class="rfs-header-tkm">TARGET</th>
                                <th class="rfs-header-tkm">REALISASI</th>
                                <th class="rfs-header-tkm">SELISIH</th>
                                <th class="rfs-header-tkm">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= number_format((float) $annualSummary['target_myrep'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['realization_myrep'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['target_myrep'] - (float) $annualSummary['realization_myrep'], 0, ',', '.') ?>
                                </td>
                                <td><?= number_format((float) $annualSummary['pct_myrep'], 2, ',', '.') ?>%</td>
                                <td><?= number_format((float) $annualSummary['target_tkm'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['realization_tkm'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['target_tkm'] - (float) $annualSummary['realization_tkm'], 0, ',', '.') ?>
                                </td>
                                <td><?= number_format((float) $annualSummary['pct_tkm'], 2, ',', '.') ?>%</td>
                                <td><?= number_format((float) $annualSummary['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0%</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0%</th>
                                <th>0%</th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-4">
                        <h5 class="mb-3">Breakdown Per Kota</h5>
                        <table id="table_rfs_annual_city_summary"
                            class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="rfs-header-fixed">NO</th>
                                    <th rowspan="2" class="rfs-header-fixed">KOTA</th>
                                    <th rowspan="2" class="rfs-header-fixed">REGIONAL</th>
                                    <th rowspan="2" class="rfs-header-fixed">SM</th>
                                    <th rowspan="2" class="rfs-header-fixed">TEAM</th>
                                    <th colspan="4" class="rfs-header-myrep">MYREP</th>
                                    <th colspan="4" class="rfs-header-tkm">TKM</th>
                                    <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                                </tr>
                                <tr>
                                    <th class="rfs-header-myrep">TARGET</th>
                                    <th class="rfs-header-myrep">REALISASI</th>
                                    <th class="rfs-header-myrep">SELISIH</th>
                                    <th class="rfs-header-myrep">%</th>
                                    <th class="rfs-header-tkm">TARGET</th>
                                    <th class="rfs-header-tkm">REALISASI</th>
                                    <th class="rfs-header-tkm">SELISIH</th>
                                    <th class="rfs-header-tkm">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($annualCitySummary)) { ?>
                                    <?php foreach ($annualCitySummary as $row) { ?>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <span class="city-health-badge city-health-<?= monitoring_rfs_tkm_percent_class($row['pct_tkm']) ?>">
                                                    <?= htmlspecialchars($row['city_name']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['regional_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['sm'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['team_name'] ?? '-') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'] - (float) $row['realization_myrep'], 0, ',', '.') ?>
                                            </td>
                                            <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['target_tkm'] - (float) $row['realization_tkm'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <span class="tkm-percent-indicator tkm-percent-<?= monitoring_rfs_tkm_percent_class($row['pct_tkm']) ?>">
                                                    <?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%
                                                </span>
                                            </td>
                                            <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="14" class="text-center">Belum ada breakdown annual per kota.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>-</th>
                                    <th>TOTAL</th>
                                    <th>-</th>
                                    <th>-</th>
                                    <th>-</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 justify-content-center">
                        <div class="d-flex gap-3">

                            <button type="button" class="btn btn-gradient-primary btn-lg shadow pulse mr-2"
                                data-toggle="modal" data-target="#modal-target-bulanan">
                                <i class="fas fa-plus-circle mr-2"></i>
                                <strong>ADD TARGET REALISASI</strong>
                            </button>

                            <button type="button" class="btn btn-gradient-success btn-lg shadow pulse mr-2"
                                data-toggle="modal" data-target="#modal-cluster-baru">
                                <i class="fas fa-plus-circle mr-2"></i>
                                <strong>ADD NEW CLUSTER</strong>
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            <datalist id="city_options">
                <?php foreach ($cityOptions as $city) { ?>
                    <option value="<?= htmlspecialchars($city) ?>"></option>
                <?php } ?>
            </datalist>

            <div class="modal fade" id="modal-target-bulanan" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Input Target Bulanan & Realisasi MyRep</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs" id="targetMyrepTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-kota-baru-link" data-toggle="tab"
                                        href="#tab-kota-baru" role="tab">INPUT KOTA BARU</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-input-target-link" data-toggle="tab"
                                        href="#tab-input-target" role="tab">INPUT TARGET</a>
                                </li>
                            </ul>

                            <div class="tab-content pt-3">
                                <div class="tab-pane fade show active" id="tab-kota-baru" role="tabpanel">
                                    <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveCityMaster') ?>" id="formCityMasterBatch">
                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                        <input type="hidden" name="month" value="<?= (int) $selectedEndMonth ?>">
                                        <input type="hidden" name="filter_city" value="<?= htmlspecialchars($selectedCity) ?>">
                                        <input type="hidden" name="filter_start_month" value="<?= (int) $selectedStartMonth ?>">
                                        <input type="hidden" name="filter_end_month" value="<?= (int) $selectedEndMonth ?>">

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped mb-0" id="table_manual_city_master">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th style="width: 5%;">No</th>
                                                        <th style="width: 16%;">Kota</th>
                                                        <th>Regional</th>
                                                        <th>Provinsi</th>
                                                        <th>Team</th>
                                                        <th>Chief</th>
                                                        <th>RPM</th>
                                                        <th>SM</th>
                                                        <th>SPV</th>
                                                        <th style="width: 8%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="manualCityMasterBody">
                                                    <tr class="manual-city-row">
                                                        <td class="text-center city-row-number">1</td>
                                                        <td>
                                                            <input list="city_options" name="city[]" class="form-control city-master-input" placeholder="Contoh: MALANG" required>
                                                            <small class="text-muted d-block mt-2 city-master-info">Jika kota sudah ada, data akan otomatis terisi.</small>
                                                        </td>
                                                        <td><input type="text" name="regional_name[]" class="form-control city-master-regional"></td>
                                                        <td><input type="text" name="province_name[]" class="form-control city-master-province"></td>
                                                        <td><input type="text" name="team_name[]" class="form-control city-master-team"></td>
                                                        <td><input type="text" name="chief[]" class="form-control city-master-chief"></td>
                                                        <td><input type="text" name="rpm[]" class="form-control city-master-rpm"></td>
                                                        <td><input type="text" name="sm[]" class="form-control city-master-sm"></td>
                                                        <td><input type="text" name="spv[]" class="form-control city-master-spv"></td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-city-row">Hapus</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">Jika kota belum ada, sistem akan buat baru. Jika sudah ada, data lama akan tampil dan bisa diedit.</small>
                                            <button type="button" class="btn btn-primary" id="btnAddCityMasterRow">Tambah Baris</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="tab-input-target" role="tabpanel">
                                    <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveMonthlyTarget') ?>" id="formMonthlyTargetBatch">
                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                        <input type="hidden" name="filter_city" value="<?= htmlspecialchars($selectedCity) ?>">
                                        <input type="hidden" name="filter_start_month" value="<?= (int) $selectedStartMonth ?>">
                                        <input type="hidden" name="filter_end_month" value="<?= (int) $selectedEndMonth ?>">

                                        <div class="form-group">
                                            <label>Bulan</label>
                                            <select name="month" id="monthly_target_selected_month" class="form-control">
                                                <?php foreach ($monthLabels as $monthNumber => $monthName) { ?>
                                                    <option value="<?= (int) $monthNumber ?>" <?= ((int) $selectedEndMonth === (int) $monthNumber) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($monthName) ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped mb-0" id="table_manual_target_batch">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th style="width: 5%;">No</th>
                                                        <th style="width: 22%;">Kota</th>
                                                        <th style="width: 18%;">Target MyRep</th>
                                                        <th style="width: 22%;">Realisasi MyRep</th>
                                                        <th style="width: 18%;">Target RKAP</th>
                                                        <th style="width: 8%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="manualTargetBatchBody">
                                                    <tr class="manual-target-row">
                                                        <td class="text-center target-row-number">1</td>
                                                        <td>
                                                            <input list="city_options" name="city[]" class="form-control monthly-target-city-input" placeholder="Contoh: MALANG" required>
                                                            <small class="text-muted d-block mt-2 monthly-target-info">Pilih / ketik kota untuk memunculkan data existing.</small>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" name="target_myrep[]" class="form-control monthly-target-myrep" placeholder="0" readonly required>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" name="realization_myrep[]" class="form-control monthly-target-realization-current mb-2" placeholder="0">
                                                            <div class="monthly-target-additional-wrapper d-none">
                                                                <input type="number" min="0" name="realization_myrep_additional[]" class="form-control monthly-target-realization-additional" placeholder="Realisasi Tambahan">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" name="target_rkap[]" class="form-control monthly-target-rkap" placeholder="0" readonly required>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-target-row">Hapus</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">Jika realisasi sudah ada, isi di kolom <strong>Realisasi Tambahan</strong> agar otomatis dijumlahkan ke data existing.</small>
                                            <button type="button" class="btn btn-primary" id="btnAddMonthlyTargetRow">Tambah Baris</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitCityMasterBatch" form="formCityMasterBatch">Simpan Kota</button>
                            <button type="submit" class="btn btn-success d-none" id="btnSubmitMonthlyTargetBatch" form="formMonthlyTargetBatch">Simpan Target Bulanan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-cluster-baru" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Input Cluster Baru</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs" id="clusterUploadTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-manual-cluster-link" data-toggle="tab"
                                        href="#tab-manual-cluster" role="tab">Upload Manual Batch</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-excel-cluster-link" data-toggle="tab"
                                        href="#tab-excel-cluster" role="tab">Upload Excel Drag & Drop</a>
                                </li>
                            </ul>

                            <div class="tab-content pt-3">
                                <div class="tab-pane fade show active" id="tab-manual-cluster" role="tabpanel">
                                    <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveCluster') ?>" id="formManualClusterBatch">
                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                        <input type="hidden" name="month" value="<?= (int) $selectedEndMonth ?>">
                                        <input type="hidden" name="filter_city" value="<?= htmlspecialchars($selectedCity) ?>">
                                        <input type="hidden" name="filter_start_month" value="<?= (int) $selectedStartMonth ?>">
                                        <input type="hidden" name="filter_end_month" value="<?= (int) $selectedEndMonth ?>">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped mb-0" id="table_manual_cluster_input">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th style="width: 5%;">No</th>
                                                        <th style="width: 35%;">Kota</th>
                                                        <th>Nama Cluster</th>
                                                        <th style="width: 18%;">Homepass</th>
                                                        <th style="width: 12%;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="manualClusterTableBody">
                                                    <tr class="manual-cluster-row">
                                                        <td class="text-center cluster-row-number">1</td>
                                                        <td>
                                                            <input type="hidden" name="id_target[]" class="cluster-id-target" value="">
                                                            <select name="cluster_city[]" class="form-control cluster-city-selector" required>
                                                                <option value="">Pilih Kota</option>
                                                                <?php foreach ($clusterTargetCityMap as $cityKey => $targetCity) { ?>
                                                                    <option value="<?= htmlspecialchars($cityKey) ?>">
                                                                        <?= htmlspecialchars($targetCity['city_name']) ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                            <small class="text-muted d-block mt-2 cluster-target-info">
                                                                Pilih kota terlebih dulu.
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="cluster_name[]" class="form-control"
                                                                placeholder="Contoh: Cluster A" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" name="homepass[]" class="form-control"
                                                                placeholder="0" required>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-cluster-row">
                                                                Hapus
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">Kamu bisa tambah beberapa cluster sekaligus lalu simpan dalam satu proses.</small>
                                            <button type="button" class="btn btn-primary" id="btnAddManualClusterRow">Tambah Baris</button>
                                        </div>

                                        <div class="alert alert-light border mt-3 mb-0">
                                            <strong>Alur input:</strong> pilih kota dulu, lalu isi nama cluster dan jumlah homepass.
                                            Sistem akan otomatis menghubungkan cluster ke target bulanan aktif dari kota tersebut.
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="tab-excel-cluster" role="tabpanel">
                                    <form id="formPreviewClusterImport" enctype="multipart/form-data">
                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                        <input type="hidden" name="month" value="<?= (int) $selectedEndMonth ?>">
                                        <div class="d-flex justify-content-end mb-3">
                                            <a href="<?= base_url('Monitoring_RFS_MyRep/downloadClusterImportTemplate') ?>"
                                                class="btn btn-outline-success">
                                                Download Format CSV
                                            </a>
                                        </div>

                                        <div id="clusterDropzone" class="cluster-dropzone text-center">
                                            <input type="file" id="clusterExcelFile" name="file_excel" accept=".xls,.xlsx,.csv" hidden>
                                            <h5 class="mb-2">Drop file Excel atau CSV di sini</h5>
                                            <p class="text-muted mb-3">atau klik tombol berikut untuk memilih file `.xls`, `.xlsx`, atau `.csv`</p>
                                            <label for="clusterExcelFile" class="btn btn-outline-primary mb-0" id="btnChooseClusterExcel">
                                                Pilih File Excel
                                            </label>
                                        </div>

                                        <div class="alert alert-light border mt-3 mb-3">
                                            <strong>Header yang didukung:</strong>
                                            `city_name` atau `kota`, `cluster_name` atau `nama_cluster`, `homepass`.
                                            <br>
                                            <strong>Catatan:</strong> kota pada file harus sudah punya target bulanan pada periode aktif agar bisa diimport.
                                        </div>
                                    </form>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div id="clusterImportSummary" class="text-muted">Belum ada file dipreview</div>
                                        <button type="button" class="btn btn-success" id="btnSaveImportedCluster" disabled>
                                            Simpan Hasil Import
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="table_cluster_import_preview">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>No</th>
                                                    <th>Kota</th>
                                                    <th>Nama Cluster</th>
                                                    <th>Homepass</th>
                                                    <th>Status</th>
                                                    <th>Pesan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr id="emptyClusterImportRow">
                                                    <td colspan="6" class="text-center text-muted">Belum ada file import yang dipreview</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success" id="btnSubmitManualClusterBatch" form="formManualClusterBatch">Tambah Cluster</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="m-0 text-dark text-center">MONTHLY TARGET VS REALISASI</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">2. Monthly Target vs Realisasi</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_monthly_summary" class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="rfs-header-fixed">NO</th>
                                <th rowspan="2" class="rfs-header-fixed">KOTA</th>
                                <th rowspan="2" class="rfs-header-fixed">REGIONAL</th>
                                <th rowspan="2" class="rfs-header-fixed">SM</th>
                                <th rowspan="2" class="rfs-header-fixed">TEAM</th>
                                <th colspan="3" class="rfs-header-myrep">MYREP</th>
                                <th colspan="3" class="rfs-header-tkm">TKM</th>
                                <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                            </tr>
                            <tr>
                                <th class="rfs-header-myrep">TARGET</th>
                                <th class="rfs-header-myrep">REALISASI</th>
                                <th class="rfs-header-myrep">%</th>
                                <th class="rfs-header-tkm">TARGET</th>
                                <th class="rfs-header-tkm">REALISASI</th>
                                <th class="rfs-header-tkm">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($monthlySummary)) { ?>
                                <?php foreach ($monthlySummary as $row) { ?>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="city-health-badge city-health-<?= monitoring_rfs_tkm_percent_class($row['pct_tkm']) ?>">
                                                <?= htmlspecialchars($row['city_name']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['regional_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['sm'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['team_name'] ?? '-') ?></td>
                                        <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                        <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                        <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                        <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                        <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="tkm-percent-indicator tkm-percent-<?= monitoring_rfs_tkm_percent_class($row['pct_tkm']) ?>">
                                                <?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%
                                            </span>
                                        </td>
                                        <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="12" class="text-center">Belum ada data bulanan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th>TOTAL</th>
                                <th>-</th>
                                <th>-</th>
                                <th>-</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0%</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0%</th>
                                <th>0%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">2A. KPI Summary By Regional / SM / Team</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="mb-4">
                        <h5 class="mb-3">By Regional</h5>
                        <table id="table_rfs_regional_summary" class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="rfs-header-fixed">NO</th>
                                    <th rowspan="2" class="rfs-header-fixed">REGIONAL</th>
                                    <th colspan="3" class="rfs-header-myrep">MYREP</th>
                                    <th colspan="3" class="rfs-header-tkm">TKM</th>
                                    <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                                </tr>
                                <tr>
                                    <th class="rfs-header-myrep">TARGET</th>
                                    <th class="rfs-header-myrep">REALISASI</th>
                                    <th class="rfs-header-myrep">%</th>
                                    <th class="rfs-header-tkm">TARGET</th>
                                    <th class="rfs-header-tkm">REALISASI</th>
                                    <th class="rfs-header-tkm">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($regionalSummary)) { ?>
                                    <?php foreach ($regionalSummary as $row) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars($row['group_name'] ?? '-') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data KPI per regional.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>-</th>
                                    <th>TOTAL</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-4">
                        <h5 class="mb-3">By SM</h5>
                        <table id="table_rfs_sm_summary" class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="rfs-header-fixed">NO</th>
                                    <th rowspan="2" class="rfs-header-fixed">SM</th>
                                    <th colspan="3" class="rfs-header-myrep">MYREP</th>
                                    <th colspan="3" class="rfs-header-tkm">TKM</th>
                                    <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                                </tr>
                                <tr>
                                    <th class="rfs-header-myrep">TARGET</th>
                                    <th class="rfs-header-myrep">REALISASI</th>
                                    <th class="rfs-header-myrep">%</th>
                                    <th class="rfs-header-tkm">TARGET</th>
                                    <th class="rfs-header-tkm">REALISASI</th>
                                    <th class="rfs-header-tkm">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($smSummary)) { ?>
                                    <?php foreach ($smSummary as $row) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars($row['group_name'] ?? '-') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data KPI per SM.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>-</th>
                                    <th>TOTAL</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div>
                        <h5 class="mb-3">By Team</h5>
                        <table id="table_rfs_team_summary" class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="rfs-header-fixed">NO</th>
                                    <th rowspan="2" class="rfs-header-fixed">TEAM</th>
                                    <th colspan="3" class="rfs-header-myrep">MYREP</th>
                                    <th colspan="3" class="rfs-header-tkm">TKM</th>
                                    <th rowspan="2" class="rfs-header-fixed">MYREP VS TKM</th>
                                </tr>
                                <tr>
                                    <th class="rfs-header-myrep">TARGET</th>
                                    <th class="rfs-header-myrep">REALISASI</th>
                                    <th class="rfs-header-myrep">%</th>
                                    <th class="rfs-header-tkm">TARGET</th>
                                    <th class="rfs-header-tkm">REALISASI</th>
                                    <th class="rfs-header-tkm">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($teamSummary)) { ?>
                                    <?php foreach ($teamSummary as $row) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= htmlspecialchars($row['group_name'] ?? '-') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data KPI per team.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>-</th>
                                    <th>TOTAL</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0</th>
                                    <th>0</th>
                                    <th>0%</th>
                                    <th>0%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="m-0 text-dark text-center">RKAP VS REALISTIS VS ACHIEVEMENT</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">3. RKAP vs Realistis vs Realisasi TKM</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_three_month_summary" class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="rfs-header-fixed">NO</th>
                                <th rowspan="2" class="rfs-header-fixed">KOTA</th>
                                <th colspan="<?= $monthColumnCount ?>" class="rfs-header-rkap">RKAP</th>
                                <th colspan="<?= $monthColumnCount ?>" class="rfs-header-realistis">REALISTIS</th>
                                <th colspan="<?= $monthColumnCount ?>" class="rfs-header-pencapaian">PENCAPAIAN</th>
                            </tr>
                            <tr>
                                <?php foreach ($monthColumns as $column) { ?>
                                    <th class="rfs-header-rkap"><?= htmlspecialchars($column['label']) ?></th>
                                <?php } ?>
                                <?php foreach ($monthColumns as $column) { ?>
                                    <th class="rfs-header-realistis"><?= htmlspecialchars($column['label']) ?></th>
                                <?php } ?>
                                <?php foreach ($monthColumns as $column) { ?>
                                    <th class="rfs-header-pencapaian"><?= htmlspecialchars($column['label']) ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($threeMonthSummary)) { ?>
                                <?php foreach ($threeMonthSummary as $row) { ?>
                                    <tr>
                                        <td></td>
                                        <td><?= htmlspecialchars($row['city_name']) ?></td>
                                        <?php foreach ($monthColumns as $column) { ?>
                                            <td><?= number_format((float) ($row['rkap'][$column['month_num']] ?? 0), 0, ',', '.') ?>
                                            </td>
                                        <?php } ?>
                                        <?php foreach ($monthColumns as $column) { ?>
                                            <td><?= number_format((float) ($row['realistis'][$column['month_num']] ?? 0), 0, ',', '.') ?>
                                            </td>
                                        <?php } ?>
                                        <?php foreach ($monthColumns as $column) { ?>
                                            <td><?= number_format((float) ($row['pencapaian'][$column['month_num']] ?? 0), 0, ',', '.') ?>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="<?= 2 + ($monthColumnCount * 3) ?>" class="text-center">Belum ada data
                                        summary 3 bulan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th>TOTAL</th>
                                <?php for ($i = 0; $i < ($monthColumnCount * 3); $i++) { ?>
                                    <th>0</th>
                                <?php } ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="m-0 text-dark text-center">LIST CLUSTER</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">4. List Cluster</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_cluster_list" class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Kota</th>
                                <th>Nama Cluster</th>
                                <th>Status RFS</th>
                                <th>RPM</th>
                                <th>SM</th>
                                <th>SPV</th>
                                <th>HP DRM</th>
                                <th>HP RFS</th>
                                <th>Deviasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clusterList)) { ?>
                                <?php foreach ($clusterList as $cluster) { ?>
                                    <tr>
                                        <td></td>
                                        <td><?= htmlspecialchars($cluster['city_name']) ?></td>
                                        <td><?= htmlspecialchars($cluster['cluster_name']) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $statusRfs = (string) ($cluster['status_rfs'] ?? 'NY RFS');
                                            $hasPendingClaim = (int) ($cluster['pending_claim_count'] ?? 0) > 0;
                                            $displayStatusRfs = $hasPendingClaim ? 'WAITING APPROVAL' : $statusRfs;
                                            $displayBadgeClass = $displayStatusRfs === 'FULL RFS'
                                                ? 'success'
                                                : ($displayStatusRfs === 'PARTIAL'
                                                    ? 'warning'
                                                    : ($displayStatusRfs === 'WAITING APPROVAL'
                                                        ? 'info'
                                                        : ($displayStatusRfs === 'REJECTED' ? 'danger' : 'secondary')));
                                            ?>
                                            <span class="badge badge-<?= $displayBadgeClass ?>">
                                                <?= htmlspecialchars($displayStatusRfs) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($cluster['rpm'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cluster['sm'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cluster['spv'] ?? '') ?></td>
                                        <td class="text-right"><?= number_format((float) $cluster['homepass'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right">
                                            <?= number_format((float) $cluster['claimed_qty'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right">
                                            <?= number_format((float) $cluster['homepass'] - (float) $cluster['claimed_qty'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php if (in_array($displayStatusRfs, ['NY RFS', 'PARTIAL', 'REJECTED'], true)) { ?>
                                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                                    data-target="#claimModal<?= (int) $cluster['id_cluster'] ?>">
                                                    Claim RFS
                                                </button>
                                            <?php } else { ?>
                                                <span class="text-muted small">Tidak tersedia</span>
                                            <?php } ?>
                                        </td>
                                    </tr>

                                    <div class="modal fade claim-rfs-modal" id="claimModal<?= (int) $cluster['id_cluster'] ?>">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/submitClaim') ?>"
                                                    enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Claim RFS -
                                                            <?= htmlspecialchars($cluster['cluster_name'] ?? '') ?>
                                                        </h4>
                                                        <button type="button" class="close"
                                                            data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                        <input type="hidden" name="month"
                                                            value="<?= (int) $selectedEndMonth ?>">
                                                        <input type="hidden" name="filter_city"
                                                            value="<?= htmlspecialchars($selectedCity) ?>">
                                                        <input type="hidden" name="filter_start_month"
                                                            value="<?= (int) $selectedStartMonth ?>">
                                                        <input type="hidden" name="filter_end_month"
                                                            value="<?= (int) $selectedEndMonth ?>">
                                                        <input type="hidden" name="cluster_id"
                                                            value="<?= (int) $cluster['id_cluster'] ?>">
                                                        <div class="claim-summary-card">
                                                            <span class="summary-label">HP DRM</span>
                                                            <div class="summary-value">
                                                                <?= number_format((float) $cluster['homepass'], 0, ',', '.') ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tanggal RFS</label>
                                                            <input type="date" name="claim_date" class="form-control"
                                                                value="<?= date('Y-m-d') ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>HP RFS</label>
                                                            <input type="number" min="1" max="<?= (int) $cluster['homepass'] ?>"
                                                                name="claim_qty" class="form-control claim-rfs-qty-input"
                                                                data-homepass="<?= (int) $cluster['homepass'] ?>"
                                                                data-deviasi-target="#claim_rfs_deviasi_<?= (int) $cluster['id_cluster'] ?>"
                                                                required>
                                                            <small class="text-muted">Isi sesuai HP RFS actual pada cluster ini.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Deviasi</label>
                                                            <input type="text" id="claim_rfs_deviasi_<?= (int) $cluster['id_cluster'] ?>" class="form-control claim-rfs-deviasi-output"
                                                                value="<?= number_format((float) $cluster['homepass'], 0, ',', '.') ?>" readonly>
                                                            <small class="text-muted">Deviasi = HP DRM - HP RFS</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Status RFS</label>
                                                            <select name="status_rfs" class="form-control" required>
                                                                <option value="">Pilih Status RFS</option>
                                                                <option value="PARTIAL" <?= (($cluster['status_rfs'] ?? '') === 'PARTIAL') ? 'selected' : '' ?>>PARTIAL</option>
                                                                <option value="FULL RFS" <?= (($cluster['status_rfs'] ?? '') === 'FULL RFS') ? 'selected' : '' ?>>FULL RFS</option>
                                                            </select>
                                                            <small class="text-muted">Gunakan untuk membedakan partial claim dengan reduce homepass.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Foto Claim</label>
                                                            <div class="claim-photo-dropzone">
                                                                <input type="file" name="claim_photo" class="claim-photo-input" accept=".jpg,.jpeg,.png,.webp" hidden required>
                                                                <h6 class="mb-2">Drop foto claim di sini</h6>
                                                                <p class="text-muted mb-2">atau klik area ini untuk pilih file gambar</p>
                                                                <span class="btn btn-outline-primary btn-sm">Pilih Foto</span>
                                                                <div class="small text-muted mt-2 claim-photo-filename">Belum ada file dipilih</div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Keterangan</label>
                                                            <textarea name="claim_note" class="form-control"
                                                                rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Kirim Claim</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="11" class="text-center">Belum ada master cluster.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th colspan="6" class="text-center">TOTAL</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">5. Claim RFS & Approval HO</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_claim_list" class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Tanggal RFS</th>
                                <th>Kota</th>
                                <th>Cluster</th>
                                <th>Qty Claim</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th>PIC Area</th>
                                <th>Approval RPM</th>
                                <th>Approval HO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($claimList)) { ?>
                                <?php foreach ($claimList as $claim) { ?>
                                    <tr>
                                        <td></td>
                                        <td><?= htmlspecialchars($claim['claim_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($claim['city_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($claim['cluster_name'] ?? '') ?></td>
                                        <td class="text-right"><?= number_format((float) $claim['claim_qty'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($claim['photo_path'])) { ?>
                                                <a href="<?= base_url($claim['photo_path']) ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">Lihat Foto</a>
                                            <?php } ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?= monitoring_rfs_badge_class($claim['status_claim']) ?>">
                                                <?= htmlspecialchars($claim['status_claim'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(trim(($claim['rpm'] ?? '') . ' / ' . ($claim['sm'] ?? '') . ' / ' . ($claim['spv'] ?? ''), ' /')) ?><br>
                                            <small>Submitter: <?= htmlspecialchars($claim['submitted_name'] ?? '') ?></small>
                                        </td>
                                        <td style="min-width: 260px;">
                                            <?php
                                            $claimRpm = trim((string) ($claim['rpm'] ?? ''));
                                            $rpmApprovalStatus = trim((string) ($claim['rpm_approval_status'] ?? ''));
                                            $claimStatus = trim((string) ($claim['status_claim'] ?? ''));
                                            $sessionName = strtoupper(trim((string) $this->session->userdata('nama_user')));
                                            $canApproveRpm = $this->session->userdata('nama_level') === 'Super Admin'
                                                || ($claimRpm !== '' && $sessionName === strtoupper($claimRpm));
                                            ?>
                                            <?php if ($claimRpm === '') { ?>
                                                <span class="badge badge-secondary">SKIPPED</span>
                                                <div><small>Tidak ada RPM untuk area ini</small></div>
                                            <?php } elseif ($claimStatus === 'WAITING APPROVAL RPM' && $canApproveRpm) { ?>
                                                <form method="post"
                                                    action="<?= base_url('Monitoring_RFS_MyRep/updateClaimStatus') ?>">
                                                    <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                    <input type="hidden" name="month" value="<?= (int) $selectedEndMonth ?>">
                                                    <input type="hidden" name="filter_city"
                                                        value="<?= htmlspecialchars($selectedCity) ?>">
                                                    <input type="hidden" name="filter_start_month"
                                                        value="<?= (int) $selectedStartMonth ?>">
                                                    <input type="hidden" name="filter_end_month"
                                                        value="<?= (int) $selectedEndMonth ?>">
                                                    <input type="hidden" name="claim_id" value="<?= (int) $claim['id_claim'] ?>">
                                                    <input type="hidden" name="approver_stage" value="RPM">
                                                    <div class="form-group mb-2">
                                                        <select name="status_claim" class="form-control form-control-sm">
                                                            <option value="APPROVED">APPROVED</option>
                                                            <option value="REJECTED">REJECTED</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <textarea name="rpm_approval_note" class="form-control form-control-sm" rows="2"
                                                            placeholder="Catatan RPM"><?= htmlspecialchars($claim['rpm_approval_note'] ?? '') ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary">Update RPM</button>
                                                </form>
                                            <?php } else { ?>
                                                <span class="badge badge-<?= monitoring_rfs_badge_class($rpmApprovalStatus !== '' ? $rpmApprovalStatus : 'WAITING APPROVAL RPM') ?>">
                                                    <?= htmlspecialchars($rpmApprovalStatus !== '' ? $rpmApprovalStatus : 'WAITING APPROVAL RPM') ?>
                                                </span>
                                                <?php if (!empty($claim['rpm_approved_name'])) { ?>
                                                    <div><small>By: <?= htmlspecialchars($claim['rpm_approved_name'] ?? '') ?> |
                                                            <?= htmlspecialchars($claim['rpm_approved_at'] ?? '') ?></small></div>
                                                <?php } else { ?>
                                                    <div><small>RPM: <?= htmlspecialchars($claimRpm) ?></small></div>
                                                <?php } ?>
                                                <?php if (!empty($claim['rpm_approval_note'])) { ?>
                                                    <div><small><?= htmlspecialchars($claim['rpm_approval_note'] ?? '') ?></small></div>
                                                <?php } ?>
                                            <?php } ?>
                                        </td>
                                        <td style="min-width: 280px;">
                                            <?php if ($canApprove && in_array(($claim['status_claim'] ?? ''), ['WAITING APPROVAL HO', 'APPROVED', 'REJECTED'], true)) { ?>
                                                <form method="post"
                                                    action="<?= base_url('Monitoring_RFS_MyRep/updateClaimStatus') ?>">
                                                    <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                    <input type="hidden" name="month" value="<?= (int) $selectedEndMonth ?>">
                                                    <input type="hidden" name="filter_city"
                                                        value="<?= htmlspecialchars($selectedCity) ?>">
                                                    <input type="hidden" name="filter_start_month"
                                                        value="<?= (int) $selectedStartMonth ?>">
                                                    <input type="hidden" name="filter_end_month"
                                                        value="<?= (int) $selectedEndMonth ?>">
                                                    <input type="hidden" name="claim_id" value="<?= (int) $claim['id_claim'] ?>">
                                                    <input type="hidden" name="approver_stage" value="HO">
                                                    <div class="form-group mb-2">
                                                        <select name="status_claim" class="form-control form-control-sm">
                                                            <option value="APPROVED" <?= ($claim['status_claim'] === 'APPROVED') ? 'selected' : '' ?>>APPROVED</option>
                                                            <option value="REJECTED" <?= ($claim['status_claim'] === 'REJECTED') ? 'selected' : '' ?>>REJECTED</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <textarea name="approval_note" class="form-control form-control-sm" rows="2"
                                                            placeholder="Catatan HO"><?= htmlspecialchars($claim['approval_note'] ?? '') ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-warning">Update Status</button>
                                                    <?php if (!empty($claim['approved_name'])) { ?>
                                                        <div><small>By: <?= htmlspecialchars($claim['approved_name'] ?? '') ?> |
                                                                <?= htmlspecialchars($claim['approved_at'] ?? '') ?></small></div>
                                                    <?php } ?>
                                                </form>
                                            <?php } else { ?>
                                                <small>
                                                    <?= !empty($claim['approved_name']) ? 'By: ' . htmlspecialchars($claim['approved_name'] ?? '') : (($claim['status_claim'] ?? '') === 'WAITING APPROVAL RPM' ? 'Menunggu approval RPM' : 'Menunggu PIC HO') ?><br>
                                                    <?= htmlspecialchars((string) $claim['approval_note']) ?>
                                                </small>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada claim pada periode ini.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th colspan="3" class="text-center">TOTAL</th>
                                <th>0</th>
                                <th>-</th>
                                <th>-</th>
                                <th>-</th>
                                <th>-</th>
                                <th>-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    (function bootstrapMonitoringRfsMyRep() {
        var clusterTargetCityMap = <?= json_encode($clusterTargetCityMap) ?>;
        var monthLabels = <?= json_encode($monthLabels) ?>;
        var monthlyTargetCityMap = <?= json_encode($monthlyTargetCityMap) ?>;
        var monthlyTargetPeriodCityMap = <?= json_encode($monthlyTargetPeriodCityMap) ?>;
        var importedClusterRows = [];

        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
            window.setTimeout(bootstrapMonitoringRfsMyRep, 150);
            return;
        }

        var $ = window.jQuery;

        $.fn.dataTable.ext.type.detect.unshift(function (data) {
            var text = $('<div>').html(data === null || data === undefined ? '' : String(data)).text().trim();

            if (text === '' || text === '-') {
                return 'id-locale-num';
            }

            if (/^-?[\d.,]+%?$/.test(text)) {
                return 'id-locale-num';
            }

            return null;
        });

        $.fn.dataTable.ext.type.order['id-locale-num-pre'] = function (data) {
            var text = $('<div>').html(data === null || data === undefined ? '' : String(data)).text().trim();

            if (text === '' || text === '-') {
                return 0;
            }

            return parseLocaleNumber(text);
        };

        function syncClusterTargetSelection($row) {
            var selectedCity = String($row.find('.cluster-city-selector').val() || '').toUpperCase();
            var targetData = clusterTargetCityMap[selectedCity] || null;
            var $targetInput = $row.find('.cluster-id-target');
            var $info = $row.find('.cluster-target-info');

            if (!targetData || !targetData.id_target) {
                $targetInput.val('');
                $info
                    .text('Target bulanan aktif untuk kota ini belum tersedia pada periode filter.')
                    .removeClass('text-muted')
                    .addClass('text-danger');
                return;
            }

            var picLabel = [targetData.rpm || '', targetData.sm || '', targetData.spv || '']
                .filter(Boolean)
                .join(' / ');
            var periodLabel = (monthLabels[targetData.month_num] || targetData.month_num) + ' ' + targetData.year_num;

            $targetInput.val(targetData.id_target);
            $info
                .text('Target aktif: ' + periodLabel + (picLabel ? ' | ' + picLabel : ''))
                .removeClass('text-danger')
                .addClass('text-muted');
        }

        function refreshManualClusterRowNumbers() {
            $('#manualClusterTableBody').find('.manual-cluster-row').each(function(index) {
                $(this).find('.cluster-row-number').text(index + 1);
            });
        }

        function refreshCityMasterRowNumbers() {
            $('#manualCityMasterBody').find('.manual-city-row').each(function(index) {
                $(this).find('.city-row-number').text(index + 1);
            });
        }

        function refreshMonthlyTargetRowNumbers() {
            $('#manualTargetBatchBody').find('.manual-target-row').each(function(index) {
                $(this).find('.target-row-number').text(index + 1);
            });
        }

        function toggleMonthlyAdditionalInput($row, currentRealization) {
            var $wrapper = $row.find('.monthly-target-additional-wrapper');
            var $input = $row.find('.monthly-target-realization-additional');
            var $currentInput = $row.find('.monthly-target-realization-current');
            var currentValue = Number(currentRealization || 0);

            if (currentValue > 0) {
                $wrapper.removeClass('d-none');
                $currentInput.prop('readonly', true);
            } else {
                $wrapper.addClass('d-none');
                $input.val('');
                $currentInput.prop('readonly', false);
            }
        }

        function buildManualClusterRow() {
            var optionsHtml = '<option value="">Pilih Kota</option>';

            Object.keys(clusterTargetCityMap).forEach(function(cityKey) {
                var targetData = clusterTargetCityMap[cityKey] || {};
                optionsHtml += '<option value="' + cityKey.replace(/"/g, '&quot;') + '">' + (targetData.city_name || cityKey) + '</option>';
            });

            return '' +
                '<tr class="manual-cluster-row">' +
                    '<td class="text-center cluster-row-number"></td>' +
                    '<td>' +
                        '<input type="hidden" name="id_target[]" class="cluster-id-target" value="">' +
                        '<select name="cluster_city[]" class="form-control cluster-city-selector" required>' +
                            optionsHtml +
                        '</select>' +
                        '<small class="text-muted d-block mt-2 cluster-target-info">Pilih kota terlebih dulu.</small>' +
                    '</td>' +
                    '<td>' +
                        '<input type="text" name="cluster_name[]" class="form-control" placeholder="Contoh: Cluster A" required>' +
                    '</td>' +
                    '<td>' +
                        '<input type="number" min="0" name="homepass[]" class="form-control" placeholder="0" required>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-cluster-row">Hapus</button>' +
                    '</td>' +
                '</tr>';
        }

        function buildCityMasterRow() {
            return '' +
                '<tr class="manual-city-row">' +
                    '<td class="text-center city-row-number"></td>' +
                    '<td>' +
                        '<input list="city_options" name="city[]" class="form-control city-master-input" placeholder="Contoh: MALANG" required>' +
                        '<small class="text-muted d-block mt-2 city-master-info">Jika kota sudah ada, data akan otomatis terisi.</small>' +
                    '</td>' +
                    '<td><input type="text" name="regional_name[]" class="form-control city-master-regional"></td>' +
                    '<td><input type="text" name="province_name[]" class="form-control city-master-province"></td>' +
                    '<td><input type="text" name="team_name[]" class="form-control city-master-team"></td>' +
                    '<td><input type="text" name="chief[]" class="form-control city-master-chief"></td>' +
                    '<td><input type="text" name="rpm[]" class="form-control city-master-rpm"></td>' +
                    '<td><input type="text" name="sm[]" class="form-control city-master-sm"></td>' +
                    '<td><input type="text" name="spv[]" class="form-control city-master-spv"></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-city-row">Hapus</button></td>' +
                '</tr>';
        }

        function buildMonthlyTargetRow() {
            return '' +
                '<tr class="manual-target-row">' +
                    '<td class="text-center target-row-number"></td>' +
                    '<td>' +
                        '<input list="city_options" name="city[]" class="form-control monthly-target-city-input" placeholder="Contoh: MALANG" required>' +
                        '<small class="text-muted d-block mt-2 monthly-target-info">Pilih / ketik kota untuk memunculkan data existing.</small>' +
                    '</td>' +
                    '<td><input type="number" min="0" name="target_myrep[]" class="form-control monthly-target-myrep" placeholder="0" readonly required></td>' +
                    '<td>' +
                        '<input type="number" min="0" name="realization_myrep[]" class="form-control monthly-target-realization-current mb-2" placeholder="0">' +
                        '<div class="monthly-target-additional-wrapper d-none">' +
                            '<input type="number" min="0" name="realization_myrep_additional[]" class="form-control monthly-target-realization-additional" placeholder="Realisasi Tambahan">' +
                        '</div>' +
                    '</td>' +
                    '<td><input type="number" min="0" name="target_rkap[]" class="form-control monthly-target-rkap" placeholder="0" readonly required></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-target-row">Hapus</button></td>' +
                '</tr>';
        }

        function syncCityMasterRow($row) {
            var cityKey = String($row.find('.city-master-input').val() || '').trim().toUpperCase();
            var data = monthlyTargetCityMap[cityKey] || null;
            var $info = $row.find('.city-master-info');

            if (!data) {
                $row.find('.city-master-regional, .city-master-province, .city-master-team, .city-master-chief, .city-master-rpm, .city-master-sm, .city-master-spv').each(function() {
                    if (!$(this).data('userEdited')) {
                        $(this).val('');
                    }
                });
                $info.text('Kota baru. Data akan dibuat jika belum ada di database.');
                return;
            }

            $row.find('.city-master-regional').val(data.regional_name || '');
            $row.find('.city-master-province').val(data.province_name || '');
            $row.find('.city-master-team').val(data.team_name || '');
            $row.find('.city-master-chief').val(data.chief || '');
            $row.find('.city-master-rpm').val(data.rpm || '');
            $row.find('.city-master-sm').val(data.sm || '');
            $row.find('.city-master-spv').val(data.spv || '');
            $info.text('Data existing ditemukan. Kamu bisa edit langsung.');
        }

        function syncMonthlyTargetRow($row) {
            var cityKey = String($row.find('.monthly-target-city-input').val() || '').trim().toUpperCase();
            var selectedMonth = Number($('#monthly_target_selected_month').val() || <?= (int) $selectedEndMonth ?>);
            var monthBucket = monthlyTargetPeriodCityMap[selectedMonth] || {};
            var data = monthBucket[cityKey] || null;
            var $info = $row.find('.monthly-target-info');

            if (!data) {
                $row.find('.monthly-target-myrep').val(0);
                $row.find('.monthly-target-realization-current').val(0);
                $row.find('.monthly-target-rkap').val(0);
                $row.find('.monthly-target-realization-additional').val('');
                toggleMonthlyAdditionalInput($row, 0);
                $info.text('Kota belum ada di periode ini. Data target akan dibuat baru.');
                return;
            }

            $row.find('.monthly-target-myrep').val(data.target_myrep || 0);
            $row.find('.monthly-target-realization-current').val(data.realization_myrep || 0);
            $row.find('.monthly-target-rkap').val(data.target_rkap || 0);
            $row.find('.monthly-target-realization-additional').val('');
            toggleMonthlyAdditionalInput($row, data.realization_myrep || 0);
            $info.text('Data existing ditemukan. Realisasi tambahan akan dijumlahkan ke realisasi saat ini.');
        }

        function syncAllMonthlyTargetRows() {
            $('#manualTargetBatchBody').find('.manual-target-row').each(function () {
                syncMonthlyTargetRow($(this));
            });
        }

        function syncTargetModalFooterButtons() {
            var activeTab = $('#targetMyrepTab .nav-link.active').attr('id');
            $('#btnSubmitCityMasterBatch').toggleClass('d-none', activeTab !== 'tab-kota-baru-link');
            $('#btnSubmitMonthlyTargetBatch').toggleClass('d-none', activeTab !== 'tab-input-target-link');
        }

        function syncClusterModalFooterButtons() {
            var activeTab = $('#clusterUploadTab .nav-link.active').attr('id');
            $('#btnSubmitManualClusterBatch').toggleClass('d-none', activeTab !== 'tab-manual-cluster-link');
        }

        function syncClusterTabFormState() {
            var manualActive = $('#tab-manual-cluster').hasClass('active') || $('#tab-manual-cluster').hasClass('show');
            $('#tab-manual-cluster')
                .find('input, select, textarea, button[type="submit"]')
                .prop('disabled', !manualActive);
        }

        function resetClusterImportPreview() {
            importedClusterRows = [];
            $('#clusterImportSummary').text('Belum ada file dipreview');
            $('#btnSaveImportedCluster').prop('disabled', true);
            $('#table_cluster_import_preview tbody').html(
                '<tr id="emptyClusterImportRow"><td colspan="6" class="text-center text-muted">Belum ada file import yang dipreview</td></tr>'
            );
        }

        function renderClusterImportPreview(rows) {
            if (!rows || !rows.length) {
                resetClusterImportPreview();
                return;
            }

            var html = '';
            rows.forEach(function(row, index) {
                var badgeClass = row.status === 'valid' ? 'success' : 'danger';
                html += '' +
                    '<tr>' +
                        '<td class="text-center">' + (index + 1) + '</td>' +
                        '<td>' + (row.city_name || '') + '</td>' +
                        '<td>' + (row.cluster_name || '') + '</td>' +
                        '<td class="text-right">' + formatLocaleNumber(row.homepass || 0, 0) + '</td>' +
                        '<td class="text-center"><span class="badge badge-' + badgeClass + '">' + (row.status || '') + '</span></td>' +
                        '<td>' + (row.message || '') + '</td>' +
                    '</tr>';
            });

            $('#table_cluster_import_preview tbody').html(html);
        }

        function parseLocaleNumber(value) {
            if (value === null || value === undefined) {
                return 0;
            }

            var text = String(value).trim();
            if (text === '') {
                return 0;
            }

            text = text.replace(/[^0-9,.\-]/g, '');
            if (text.indexOf(',') !== -1) {
                text = text.replace(/\./g, '').replace(',', '.');
            } else {
                var parts = text.split('.');
                if (parts.length > 1) {
                    var isThousandsFormat = parts.slice(1).every(function (part) {
                        return /^\d{3}$/.test(part);
                    });

                    if (isThousandsFormat) {
                        text = parts.join('');
                    } else if (parts.length > 2) {
                        var lastPart = parts.pop();
                        text = parts.join('') + '.' + lastPart;
                    }
                }
            }

            var parsed = parseFloat(text);
            return isNaN(parsed) ? 0 : parsed;
        }

        function formatLocaleNumber(value, decimals) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: decimals || 0,
                maximumFractionDigits: decimals || 0
            });
        }

        $.fn.dataTable.ext.type.detect.unshift(function(data) {
            if (data === null || data === undefined) {
                return null;
            }

            var text = String(data)
                .replace(/<[^>]*>/g, ' ')
                .replace(/&nbsp;/g, ' ')
                .trim();

            if (text === '') {
                return null;
            }

            if (/^-?[\d.\-,%\s]+$/.test(text) && /\d/.test(text)) {
                return 'id-locale-num';
            }

            return null;
        });

        $.fn.dataTable.ext.type.order['id-locale-num-pre'] = function(data) {
            if (data === null || data === undefined) {
                return 0;
            }

            var text = String(data).replace(/<[^>]*>/g, ' ').trim();
            return parseLocaleNumber(text);
        };

        function sumColumn(api, columnIndex, useInputValue) {
            return api
                .cells(null, columnIndex, { search: 'applied' })
                .nodes()
                .toArray()
                .reduce(function (total, cell) {
                    var rawValue = '';

                    if (useInputValue) {
                        var input = cell.querySelector('input');
                        rawValue = input ? input.value : cell.textContent;
                    } else {
                        rawValue = cell.textContent;
                    }

                    return total + parseLocaleNumber(rawValue);
                }, 0);
        }

        function setFooterValue(api, columnIndex, value, decimals, suffix) {
            var footerCell = api.column(columnIndex).footer();
            if (!footerCell) {
                return;
            }

            footerCell.innerHTML = formatLocaleNumber(value, decimals) + (suffix || '');
        }

        function safePercent(numerator, denominator) {
            numerator = Number(numerator || 0);
            denominator = Number(denominator || 0);

            if (!denominator) {
                return 0;
            }

            return (numerator / denominator) * 100;
        }

        function initAdminLteTable(selector, orderConfig, footerCallback) {
            if (!$(selector).length || $.fn.DataTable.isDataTable(selector)) {
                return;
            }

            $(selector).DataTable({
                paging: true,
                pageLength: 10,
                info: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,
                responsive: false,
                ordering: true,
                order: orderConfig || [],
                scrollX: true,
                footerCallback: footerCallback || null,
                initComplete: function () {
                    $(this.api().table().container())
                        .find('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
        }

        function addRowNumbers(selector, columnIndex) {
            var table = $(selector).DataTable();

            table.on('order.dt search.dt draw.dt', function () {
                table.column(columnIndex, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
        }

        initAdminLteTable('#table_rfs_annual_summary', [], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 0);
            var totalRealisasiMyrep = sumColumn(api, 1);
            var totalSelisihMyrep = totalTargetMyrep - totalRealisasiMyrep;
            var totalTargetTkm = sumColumn(api, 4);
            var totalRealisasiTkm = sumColumn(api, 5);
            var totalSelisihTkm = totalTargetTkm - totalRealisasiTkm;

            setFooterValue(api, 0, totalTargetMyrep, 0);
            setFooterValue(api, 1, totalRealisasiMyrep, 0);
            setFooterValue(api, 2, totalSelisihMyrep, 0);
            setFooterValue(api, 3, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 4, totalTargetTkm, 0);
            setFooterValue(api, 5, totalRealisasiTkm, 0);
            setFooterValue(api, 6, totalSelisihTkm, 0);
            setFooterValue(api, 7, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 8, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });

        initAdminLteTable('#table_rfs_annual_city_summary', [[1, 'asc']], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 5);
            var totalRealisasiMyrep = sumColumn(api, 6);
            var totalSelisihMyrep = totalTargetMyrep - totalRealisasiMyrep;
            var totalTargetTkm = sumColumn(api, 9);
            var totalRealisasiTkm = sumColumn(api, 10);
            var totalSelisihTkm = totalTargetTkm - totalRealisasiTkm;

            setFooterValue(api, 5, totalTargetMyrep, 0);
            setFooterValue(api, 6, totalRealisasiMyrep, 0);
            setFooterValue(api, 7, totalSelisihMyrep, 0);
            setFooterValue(api, 8, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 9, totalTargetTkm, 0);
            setFooterValue(api, 10, totalRealisasiTkm, 0);
            setFooterValue(api, 11, totalSelisihTkm, 0);
            setFooterValue(api, 12, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 13, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_annual_city_summary', 0);

        initAdminLteTable('#table_rfs_monthly_summary', [[1, 'asc']], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 5);
            var totalRealisasiMyrep = sumColumn(api, 6);
            var totalTargetTkm = sumColumn(api, 8);
            var totalRealisasiTkm = sumColumn(api, 9);

            setFooterValue(api, 5, totalTargetMyrep, 0);
            setFooterValue(api, 6, totalRealisasiMyrep, 0);
            setFooterValue(api, 7, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 8, totalTargetTkm, 0);
            setFooterValue(api, 9, totalRealisasiTkm, 0);
            setFooterValue(api, 10, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 11, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_monthly_summary', 0);

        initAdminLteTable('#table_rfs_regional_summary', [[1, 'asc']], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 2);
            var totalRealisasiMyrep = sumColumn(api, 3);
            var totalTargetTkm = sumColumn(api, 5);
            var totalRealisasiTkm = sumColumn(api, 6);

            setFooterValue(api, 2, totalTargetMyrep, 0);
            setFooterValue(api, 3, totalRealisasiMyrep, 0);
            setFooterValue(api, 4, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 5, totalTargetTkm, 0);
            setFooterValue(api, 6, totalRealisasiTkm, 0);
            setFooterValue(api, 7, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 8, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_regional_summary', 0);

        initAdminLteTable('#table_rfs_sm_summary', [[1, 'asc']], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 2);
            var totalRealisasiMyrep = sumColumn(api, 3);
            var totalTargetTkm = sumColumn(api, 5);
            var totalRealisasiTkm = sumColumn(api, 6);

            setFooterValue(api, 2, totalTargetMyrep, 0);
            setFooterValue(api, 3, totalRealisasiMyrep, 0);
            setFooterValue(api, 4, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 5, totalTargetTkm, 0);
            setFooterValue(api, 6, totalRealisasiTkm, 0);
            setFooterValue(api, 7, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 8, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_sm_summary', 0);

        initAdminLteTable('#table_rfs_team_summary', [[1, 'asc']], function () {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 2);
            var totalRealisasiMyrep = sumColumn(api, 3);
            var totalTargetTkm = sumColumn(api, 5);
            var totalRealisasiTkm = sumColumn(api, 6);

            setFooterValue(api, 2, totalTargetMyrep, 0);
            setFooterValue(api, 3, totalRealisasiMyrep, 0);
            setFooterValue(api, 4, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 5, totalTargetTkm, 0);
            setFooterValue(api, 6, totalRealisasiTkm, 0);
            setFooterValue(api, 7, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 8, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_team_summary', 0);

        initAdminLteTable('#table_rfs_three_month_summary', [[1, 'asc']], function () {
            var api = this.api();
            var lastColumnIndex = api.columns().count() - 1;
            for (var i = 2; i <= lastColumnIndex; i++) {
                setFooterValue(api, i, sumColumn(api, i), 0);
            }
        });
        addRowNumbers('#table_rfs_three_month_summary', 0);

        initAdminLteTable('#table_rfs_cluster_list', [[1, 'asc'], [2, 'asc']], function () {
            var api = this.api();
            setFooterValue(api, 7, sumColumn(api, 7), 0);
            setFooterValue(api, 8, sumColumn(api, 8), 0);
            setFooterValue(api, 9, sumColumn(api, 9), 0);
        });
        addRowNumbers('#table_rfs_cluster_list', 0);

        initAdminLteTable('#table_rfs_claim_list', [[1, 'desc']], function () {
            var api = this.api();
            setFooterValue(api, 4, sumColumn(api, 4), 0);
        });
        addRowNumbers('#table_rfs_claim_list', 0);

        $(document).on('change', '.cluster-city-selector', function () {
            syncClusterTargetSelection($(this).closest('.manual-cluster-row'));
        });

        $('#btnAddManualClusterRow').on('click', function () {
            $('#manualClusterTableBody').append(buildManualClusterRow());
            refreshManualClusterRowNumbers();
        });

        $(document).on('click', '.btn-remove-cluster-row', function () {
            var $rows = $('#manualClusterTableBody').find('.manual-cluster-row');
            if ($rows.length <= 1) {
                var $row = $rows.first();
                $row.find('input[type="hidden"], input[type="text"], input[type="number"]').val('');
                $row.find('.cluster-city-selector').val('');
                syncClusterTargetSelection($row);
                return;
            }

            $(this).closest('.manual-cluster-row').remove();
            refreshManualClusterRowNumbers();
        });

        $(document).on('input change', '.city-master-input', function () {
            syncCityMasterRow($(this).closest('.manual-city-row'));
        });

        $(document).on('input change', '.monthly-target-city-input', function () {
            syncMonthlyTargetRow($(this).closest('.manual-target-row'));
        });

        $('#monthly_target_selected_month').on('change', function () {
            syncAllMonthlyTargetRows();
        });

        $('#btnAddCityMasterRow').on('click', function () {
            $('#manualCityMasterBody').append(buildCityMasterRow());
            refreshCityMasterRowNumbers();
        });

        $('#btnAddMonthlyTargetRow').on('click', function () {
            $('#manualTargetBatchBody').append(buildMonthlyTargetRow());
            var $newRow = $('#manualTargetBatchBody').find('.manual-target-row').last();
            refreshMonthlyTargetRowNumbers();
            syncMonthlyTargetRow($newRow);
        });

        $(document).on('click', '.btn-remove-city-row', function () {
            var $rows = $('#manualCityMasterBody').find('.manual-city-row');
            if ($rows.length <= 1) {
                $rows.first().find('input').val('');
                $rows.first().find('.city-master-info').text('Jika kota sudah ada, data akan otomatis terisi.');
                return;
            }

            $(this).closest('.manual-city-row').remove();
            refreshCityMasterRowNumbers();
        });

        $(document).on('click', '.btn-remove-target-row', function () {
            var $rows = $('#manualTargetBatchBody').find('.manual-target-row');
            if ($rows.length <= 1) {
                $rows.first().find('input').val('');
                $rows.first().find('.monthly-target-realization-current').val(0);
                $rows.first().find('.monthly-target-myrep, .monthly-target-rkap').val(0);
                toggleMonthlyAdditionalInput($rows.first(), 0);
                $rows.first().find('.monthly-target-info').text('Pilih / ketik kota untuk memunculkan data existing.');
                return;
            }

            $(this).closest('.manual-target-row').remove();
            refreshMonthlyTargetRowNumbers();
        });

        $('#modal-cluster-baru').on('shown.bs.modal', function () {
            refreshManualClusterRowNumbers();
            syncClusterModalFooterButtons();
            syncClusterTabFormState();
            $('#manualClusterTableBody').find('.manual-cluster-row').each(function () {
                syncClusterTargetSelection($(this));
            });
        });

        $('#clusterUploadTab a[data-toggle="tab"]').on('shown.bs.tab', function () {
            syncClusterModalFooterButtons();
            syncClusterTabFormState();
        });

        $('#clusterDropzone').on('click', function () {
            $('#clusterExcelFile').trigger('click');
        });

        $('#clusterDropzone').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $('#clusterDropzone').on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $('#clusterDropzone').on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                $('#clusterExcelFile')[0].files = files;
                $('#clusterExcelFile').trigger('change');
            }
        });

        $('#clusterExcelFile').on('change', function () {
            var file = this.files[0];
            if (!file) {
                return;
            }

            var formData = new FormData($('#formPreviewClusterImport')[0]);
            formData.set('file_excel', file);

            $.ajax({
                url: '<?= base_url("Monitoring_RFS_MyRep/previewClusterImport") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (!response.status) {
                        resetClusterImportPreview();
                        alert(response.message || 'Preview import cluster gagal');
                        return;
                    }

                    importedClusterRows = response.valid_rows || [];
                    $('#clusterImportSummary').text(response.message || 'Preview selesai');
                    $('#btnSaveImportedCluster').prop('disabled', !importedClusterRows.length);
                    renderClusterImportPreview(response.rows || []);
                },
                error: function () {
                    resetClusterImportPreview();
                    alert('Terjadi kesalahan saat preview import cluster');
                }
            });
        });

        $('#btnSaveImportedCluster').on('click', function () {
            if (!importedClusterRows.length) {
                alert('Belum ada data valid untuk disimpan');
                return;
            }

            $.ajax({
                url: '<?= base_url("Monitoring_RFS_MyRep/saveImportedClusters") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    rows_json: JSON.stringify(importedClusterRows)
                },
                success: function (response) {
                    if (response.status) {
                        alert(response.message || 'Import cluster berhasil');
                        window.location.reload();
                        return;
                    }

                    alert(response.message || 'Gagal menyimpan import cluster');
                },
                error: function () {
                    alert('Terjadi kesalahan saat menyimpan import cluster');
                }
            });
        });

        function updateClaimRfsDeviasi($input) {
            var homepass = parseLocaleNumber($input.data('homepass') || 0);
            var claimQty = parseLocaleNumber($input.val() || 0);
            var targetSelector = String($input.data('deviasi-target') || '');
            var deviasi = homepass - claimQty;
            if (deviasi < 0) {
                deviasi = 0;
            }

            if (targetSelector) {
                $(targetSelector).val(formatLocaleNumber(deviasi, 0));
                return;
            }

            $input.closest('.modal-content').find('.claim-rfs-deviasi-output').first().val(formatLocaleNumber(deviasi, 0));
        }

        $(document).on('input keyup change paste', '.claim-rfs-qty-input', function () {
            updateClaimRfsDeviasi($(this));
        });

        $(document).on('click', '.claim-photo-dropzone', function (e) {
            if ($(e.target).is('.claim-photo-input')) {
                return;
            }

            $(this).find('.claim-photo-input').trigger('click');
        });

        $(document).on('dragover', '.claim-photo-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $(document).on('dragleave', '.claim-photo-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $(document).on('drop', '.claim-photo-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                $(this).find('.claim-photo-input')[0].files = files;
                $(this).find('.claim-photo-input').trigger('change');
            }
        });

        $(document).on('change', '.claim-photo-input', function () {
            var file = this.files && this.files[0] ? this.files[0] : null;
            var fileName = file ? file.name : 'Belum ada file dipilih';
            $(this).closest('.claim-photo-dropzone').find('.claim-photo-filename').text(fileName);
        });

        $('.claim-rfs-qty-input').each(function () {
            updateClaimRfsDeviasi($(this));
        });

        $(document).on('shown.bs.modal', '.claim-rfs-modal', function () {
            $(this).find('.claim-rfs-qty-input').each(function () {
                updateClaimRfsDeviasi($(this));
            });
        });

        $('#modal-target-bulanan').on('shown.bs.modal', function () {
            refreshCityMasterRowNumbers();
            refreshMonthlyTargetRowNumbers();
            syncTargetModalFooterButtons();

            $('#manualCityMasterBody').find('.manual-city-row').each(function () {
                syncCityMasterRow($(this));
            });

            syncAllMonthlyTargetRows();
        });

        $('#targetMyrepTab a[data-toggle="tab"]').on('shown.bs.tab', function () {
            syncTargetModalFooterButtons();
        });
    })();
</script>
