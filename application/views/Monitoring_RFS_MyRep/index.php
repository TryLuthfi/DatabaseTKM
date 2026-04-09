<?php
$canApprove = $this->session->userdata('lokasi_user') === 'HO' || $this->session->userdata('nama_level') === 'Super Admin';

if (!function_exists('monitoring_rfs_badge_class')) {
    function monitoring_rfs_badge_class($status)
    {
        switch ($status) {
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            default:
                return 'warning';
        }
    }
}
?>

<style>
    .table thead th[rowspan] {
        vertical-align: middle !important;
    }

    .rfs-header-myrep {
        background-color: #d9edf7;
        color: #0c5460;
    }

    .rfs-header-tkm {
        background-color: #d4edda;
        color: #155724;
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
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Monitoring_RFS_MyRep') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Tahun</label>
                                <input type="number" class="form-control" name="year" value="<?= (int) $selectedYear ?>"
                                    min="2024" max="2100">
                            </div>
                            <div class="col-md-4">
                                <label>Bulan</label>
                                <select class="form-control" name="month">
                                    <?php foreach ($monthLabels as $monthNumber => $monthName) { ?>
                                        <option value="<?= $monthNumber ?>" <?= ((int) $selectedMonth === (int) $monthNumber) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($monthName) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
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
                                <td><?= number_format((float) $annualSummary['target_myrep'] - (float) $annualSummary['realization_myrep'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['pct_myrep'], 2, ',', '.') ?>%</td>
                                <td><?= number_format((float) $annualSummary['target_tkm'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['realization_tkm'], 0, ',', '.') ?></td>
                                <td><?= number_format((float) $annualSummary['target_tkm'] - (float) $annualSummary['realization_tkm'], 0, ',', '.') ?></td>
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
                        <table id="table_rfs_annual_city_summary" class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="rfs-header-fixed">NO</th>
                                    <th rowspan="2" class="rfs-header-fixed">KOTA</th>
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
                                            <td><?= htmlspecialchars($row['city_name']) ?></td>
                                            <td><?= number_format((float) $row['target_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['target_myrep'] - (float) $row['realization_myrep'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_myrep'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['target_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['target_tkm'] - (float) $row['realization_tkm'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['pct_tkm'], 2, ',', '.') ?>%</td>
                                            <td><?= number_format((float) $row['myrep_vs_tkm'], 2, ',', '.') ?>%</td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="11" class="text-center">Belum ada breakdown annual per kota.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>-</th>
                                    <th>TOTAL</th>
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

            <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
                    <button type="button" class="btn btn-gradient-primary btn-lg shadow btn-block" data-toggle="modal"
                        data-target="#modal-target-bulanan">
                        Input Target Bulanan & Realisasi MyRep
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-gradient-success btn-lg shadow btn-block" data-toggle="modal"
                        data-target="#modal-cluster-baru">
                        Input Cluster Baru
                    </button>
                </div>
            </div>

            <datalist id="city_options">
                <?php foreach ($cityOptions as $city) { ?>
                    <option value="<?= htmlspecialchars($city) ?>"></option>
                <?php } ?>
            </datalist>

            <div class="modal fade" id="modal-target-bulanan" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveMonthlyTarget') ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Input Target Bulanan & Realisasi MyRep</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                <input type="hidden" name="month" value="<?= (int) $selectedMonth ?>">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <input list="city_options" name="city" class="form-control"
                                        placeholder="Contoh: MALANG" required>
                                </div>
                                <div class="form-group">
                                    <label>Target MyRep</label>
                                    <input type="number" min="0" name="target_myrep" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Realisasi MyRep</label>
                                    <input type="number" min="0" name="realization_myrep" class="form-control" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Target RKAP / TKM</label>
                                    <input type="number" min="0" name="target_rkap" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Simpan Target Bulanan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-cluster-baru" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveCluster') ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Input Cluster Baru</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                <input type="hidden" name="month" value="<?= (int) $selectedMonth ?>">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <input list="city_options" name="city" class="form-control"
                                        placeholder="Contoh: BLITAR" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Cluster</label>
                                    <input type="text" name="cluster_name" class="form-control" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>PIC 1</label>
                                        <input type="text" name="pic_1" class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>PIC 2</label>
                                        <input type="text" name="pic_2" class="form-control">
                                    </div>
                                </div>
                                <div class="form-row mb-0">
                                    <div class="form-group col-md-6">
                                        <label>Homepass</label>
                                        <input type="number" min="0" name="homepass" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Target Realistis Bulan Ini</label>
                                        <input type="number" min="0" name="optimistic_target" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success">Tambah Cluster</button>
                            </div>
                        </form>
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
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_monthly_summary" class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="rfs-header-fixed">NO</th>
                                <th rowspan="2" class="rfs-header-fixed">KOTA</th>
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
                                        <td><?= htmlspecialchars($row['city_name']) ?></td>
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
                                    <td colspan="9" class="text-center">Belum ada data bulanan.</td>
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
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_three_month_summary" class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="rfs-header-fixed">NO</th>
                                <th rowspan="2" class="rfs-header-fixed">KOTA</th>
                                <th colspan="3" class="rfs-header-rkap">RKAP</th>
                                <th colspan="3" class="rfs-header-realistis">REALISTIS</th>
                                <th colspan="3" class="rfs-header-pencapaian">PENCAPAIAN</th>
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
                                    <td colspan="11" class="text-center">Belum ada data summary 3 bulan.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th>TOTAL</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
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
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_cluster_list" class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Kota</th>
                                <th>Nama Cluster</th>
                                <th>PIC 1</th>
                                <th>PIC 2</th>
                                <th>Homepass</th>
                                <th>Target Realistis</th>
                                <th>Claim Masuk</th>
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
                                        <td><?= htmlspecialchars($cluster['pic_1'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($cluster['pic_2'] ?? '') ?></td>
                                        <td class="text-right"><?= number_format((float) $cluster['homepass'], 0, ',', '.') ?>
                                        </td>
                                        <td style="min-width: 220px;">
                                            <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/saveClusterPlan') ?>"
                                                class="form-inline">
                                                <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                <input type="hidden" name="month" value="<?= (int) $selectedMonth ?>">
                                                <input type="hidden" name="cluster_id"
                                                    value="<?= (int) $cluster['id_cluster'] ?>">
                                                <input type="number" min="0" name="optimistic_target"
                                                    value="<?= (float) $cluster['optimistic_target'] ?>"
                                                    class="form-control form-control-sm mr-2" style="width: 110px;">
                                                <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                            </form>
                                        </td>
                                        <td class="text-right">
                                            <?= number_format((float) $cluster['claimed_qty'], 0, ',', '.') ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                                data-target="#claimModal<?= (int) $cluster['id_cluster'] ?>">
                                                Claim RFS
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="claimModal<?= (int) $cluster['id_cluster'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url('Monitoring_RFS_MyRep/submitClaim') ?>"
                                                    enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Claim RFS -
                                                            <?= htmlspecialchars($cluster['cluster_name'] ?? '') ?></h4>
                                                        <button type="button" class="close"
                                                            data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                        <input type="hidden" name="month" value="<?= (int) $selectedMonth ?>">
                                                        <input type="hidden" name="cluster_id"
                                                            value="<?= (int) $cluster['id_cluster'] ?>">
                                                        <div class="form-group">
                                                            <label>Qty Claim</label>
                                                            <input type="number" min="1" max="<?= (int) $cluster['homepass'] ?>"
                                                                name="claim_qty" class="form-control" required>
                                                            <small class="text-muted">Bisa partial claim, total maksimal
                                                                mengikuti homepass cluster.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Foto Claim</label>
                                                            <input type="file" name="claim_photo" class="form-control"
                                                                accept=".jpg,.jpeg,.png,.webp" required>
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
                                    <td colspan="9" class="text-center">Belum ada master cluster.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>-</th>
                                <th colspan="4" class="text-center">TOTAL</th>
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
                </div>
                <div class="card-body table-responsive">
                    <table id="table_rfs_claim_list" class="table table-bordered table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kota</th>
                                <th>Cluster</th>
                                <th>Qty Claim</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th>PIC Area</th>
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
                                            <?= htmlspecialchars(trim(($claim['pic_1'] ?? '') . ' / ' . ($claim['pic_2'] ?? ''), ' /')) ?><br>
                                            <small>Submitter: <?= htmlspecialchars($claim['submitted_name'] ?? '') ?></small>
                                        </td>
                                        <td style="min-width: 280px;">
                                            <?php if ($canApprove) { ?>
                                                <form method="post"
                                                    action="<?= base_url('Monitoring_RFS_MyRep/updateClaimStatus') ?>">
                                                    <input type="hidden" name="year" value="<?= (int) $selectedYear ?>">
                                                    <input type="hidden" name="month" value="<?= (int) $selectedMonth ?>">
                                                    <input type="hidden" name="claim_id" value="<?= (int) $claim['id_claim'] ?>">
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
                                                    <?= !empty($claim['approved_name']) ? 'By: ' . htmlspecialchars($claim['approved_name'] ?? '') : 'Menunggu PIC HO' ?><br>
                                                    <?= htmlspecialchars((string) $claim['approval_note']) ?>
                                                </small>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada claim pada periode ini.</td>
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
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
            window.setTimeout(bootstrapMonitoringRfsMyRep, 150);
            return;
        }

        var $ = window.jQuery;

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
                    var isThousandsFormat = parts.slice(1).every(function(part) {
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

        function sumColumn(api, columnIndex, useInputValue) {
            return api
                .cells(null, columnIndex, { search: 'applied' })
                .nodes()
                .toArray()
                .reduce(function(total, cell) {
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
                initComplete: function() {
                    $(this.api().table().container())
                        .find('.dataTables_scrollHead, .dataTables_scrollBody')
                        .css('width', '100%');
                }
            });
        }

        function addRowNumbers(selector, columnIndex) {
            var table = $(selector).DataTable();

            table.on('order.dt search.dt draw.dt', function() {
                table.column(columnIndex, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();
        }

        initAdminLteTable('#table_rfs_annual_summary', [], function() {
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

        initAdminLteTable('#table_rfs_annual_city_summary', [[1, 'asc']], function() {
            var api = this.api();
            var totalTargetMyrep = sumColumn(api, 2);
            var totalRealisasiMyrep = sumColumn(api, 3);
            var totalSelisihMyrep = totalTargetMyrep - totalRealisasiMyrep;
            var totalTargetTkm = sumColumn(api, 6);
            var totalRealisasiTkm = sumColumn(api, 7);
            var totalSelisihTkm = totalTargetTkm - totalRealisasiTkm;

            setFooterValue(api, 2, totalTargetMyrep, 0);
            setFooterValue(api, 3, totalRealisasiMyrep, 0);
            setFooterValue(api, 4, totalSelisihMyrep, 0);
            setFooterValue(api, 5, safePercent(totalRealisasiMyrep, totalTargetMyrep), 2, '%');
            setFooterValue(api, 6, totalTargetTkm, 0);
            setFooterValue(api, 7, totalRealisasiTkm, 0);
            setFooterValue(api, 8, totalSelisihTkm, 0);
            setFooterValue(api, 9, safePercent(totalRealisasiTkm, totalTargetTkm), 2, '%');
            setFooterValue(api, 10, safePercent(totalRealisasiTkm, totalRealisasiMyrep), 2, '%');
        });
        addRowNumbers('#table_rfs_annual_city_summary', 0);

        initAdminLteTable('#table_rfs_monthly_summary', [[1, 'asc']], function() {
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
        addRowNumbers('#table_rfs_monthly_summary', 0);

        initAdminLteTable('#table_rfs_three_month_summary', [[1, 'asc']], function() {
            var api = this.api();
            for (var i = 2; i <= 10; i++) {
                setFooterValue(api, i, sumColumn(api, i), 0);
            }
        });
        addRowNumbers('#table_rfs_three_month_summary', 0);

        initAdminLteTable('#table_rfs_cluster_list', [[1, 'asc'], [2, 'asc']], function() {
            var api = this.api();
            setFooterValue(api, 5, sumColumn(api, 5), 0);
            setFooterValue(api, 6, sumColumn(api, 6, true), 0);
            setFooterValue(api, 7, sumColumn(api, 7), 0);
        });
        addRowNumbers('#table_rfs_cluster_list', 0);

        initAdminLteTable('#table_rfs_claim_list', [[1, 'desc']], function() {
            var api = this.api();
            setFooterValue(api, 4, sumColumn(api, 4), 0);
        });
        addRowNumbers('#table_rfs_claim_list', 0);
    })();
</script>
