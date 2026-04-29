<?php
$monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0 text-dark">Dashboard Budget vs Realisasi</h1>
                    <p class="text-muted mb-0">Dashboard analitik dengan cards ringkas, tabel interaktif, dan drilldown transaksi.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">Filter Dashboard</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Budget_Report') ?>">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="budget-field-label">Tahun</label>
                                <select class="form-control budget-input" name="year">
                                    <?php foreach ($yearOptions as $year): ?>
                                        <option value="<?= (int) $year ?>" <?= (int) $year === (int) $selectedYear ? 'selected' : '' ?>>
                                            <?= (int) $year ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="budget-field-label">Bulan Awal</label>
                                <select class="form-control budget-input" name="start_month">
                                    <?php foreach ($monthNames as $monthNo => $monthLabel): ?>
                                        <option value="<?= (int) $monthNo ?>" <?= (int) $startMonth === (int) $monthNo ? 'selected' : '' ?>>
                                            <?= $monthLabel ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="budget-field-label">Bulan Akhir</label>
                                <select class="form-control budget-input" name="end_month">
                                    <?php foreach ($monthNames as $monthNo => $monthLabel): ?>
                                        <option value="<?= (int) $monthNo ?>" <?= (int) $endMonth === (int) $monthNo ? 'selected' : '' ?>>
                                            <?= $monthLabel ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-wrap justify-content-md-end budget-toolbar">
                            <button type="submit" class="btn budget-btn budget-btn--primary">
                                <i class="fas fa-search mr-1"></i> Tampilkan Dashboard
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--success js-budget-report-export" data-export-type="excel">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn budget-btn budget-btn--ghost js-budget-report-export" data-export-type="csv">
                                <i class="fas fa-file-csv mr-1"></i> Export CSV
                            </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row summary-dashboard-grid">
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['total_annual_budget'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Total Annual Budget</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['total_annual_realisasi'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Total Realisasi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['total_annual_sisa'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Total Sisa</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['total_tec'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Total Operasional</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['total_project'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Total Project</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['annual_overbudget_items'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Item Annual Overbudget</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= number_format((float) ($summaryCards['monthly_overbudget_cells'] ?? 0), 0, ',', '.') ?></h3>
                            <p>Cell Monthly Overbudget</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3><?= number_format((float) (($summaryCards['total_debit'] ?? 0) - ($summaryCards['total_kredit'] ?? 0)), 0, ',', '.') ?></h3>
                            <p>Net Cash In - Cash Out</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">1. List Cash In vs Cash Out</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="debitKreditTable" class="table table-bordered table-hover table-striped js-budget-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;">Kategori</th>
                                <th colspan="2" class="text-center">Cash In</th>
                                <th colspan="2" class="text-center">Cash Out</th>
                            </tr>
                            <tr>
                                <th class="text-right">Budget</th>
                                <th class="text-right">Realisasi</th>
                                <th class="text-right">Budget</th>
                                <th class="text-right">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($debitKreditComparison)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Belum ada transaksi.</td></tr>
                            <?php else: ?>
                                <?php
                                $debitRow = $debitKreditComparison[0] ?? ['budget' => 0, 'realisasi' => 0];
                                $kreditRow = $debitKreditComparison[1] ?? ['budget' => 0, 'realisasi' => 0];
                                ?>
                                <tr>
                                    <td>Budget vs Realisasi</td>
                                    <td class="text-right"><?= number_format((float) $debitRow['budget'], 0, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format((float) $debitRow['realisasi'], 0, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format((float) $kreditRow['budget'], 0, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format((float) $kreditRow['realisasi'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="text-right">0</th>
                                <th class="text-right">0</th>
                                <th class="text-right">0</th>
                                <th class="text-right">0</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">2. Budget Annual vs Realisasi</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="annualComparisonTable" class="table table-bordered table-hover table-striped js-budget-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th class="text-right">Budget Tahunan</th>
                                <th class="text-right">Realisasi</th>
                                <th class="text-right">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($annualComparison)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($annualComparison as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-left drill-item"
                                                data-id-budget-item="<?= (int) $row['id_budget_item'] ?>"
                                                data-label="<?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($row['item_code']) ?>
                                            </button>
                                        </td>
                                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                                        <td class="text-right"><?= number_format((float) $row['annual_budget'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                        <td class="text-right <?= (float) $row['sisa'] < 0 ? 'text-danger font-weight-bold' : '' ?>">
                                            <?= number_format((float) $row['sisa'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>No</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th class="text-right">Budget Tahunan</th>
                                <th class="text-right">Realisasi</th>
                                <th class="text-right">Sisa</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">3. Budget Bulanan vs Realisasi</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="monthlyMatrixTable" class="table table-bordered table-sm table-hover table-striped js-budget-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align: middle;">Item</th>
                                <?php foreach ($selectedMonths as $monthNo): ?>
                                    <th colspan="3" class="text-center"><?= $monthNames[$monthNo] ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($selectedMonths as $monthNo): ?>
                                    <th class="text-center">Budget</th>
                                    <th class="text-center">Realisasi</th>
                                    <th class="text-center">Sisa</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthlyMatrix)): ?>
                                <tr><td colspan="<?= 1 + (count($selectedMonths) * 3) ?>" class="text-center text-muted">Belum ada data matrix bulanan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($monthlyMatrix as $row): ?>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-left drill-item"
                                                data-item-code="<?= htmlspecialchars($row['item_code'], ENT_QUOTES) ?>"
                                                data-label="<?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name']) ?>
                                            </button>
                                        </td>
                                        <?php foreach ($selectedMonths as $monthNo): ?>
                                            <?php $monthData = $row['months'][$monthNo] ?? ['budget' => 0, 'realisasi' => 0, 'sisa' => 0]; ?>
                                            <td class="text-right"><?= number_format((float) $monthData['budget'], 0, ',', '.') ?></td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-link p-0 drill-item-month"
                                                    data-item-code="<?= htmlspecialchars($row['item_code'], ENT_QUOTES) ?>"
                                                    data-label="<?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name'], ENT_QUOTES) ?>"
                                                    data-month-no="<?= (int) $monthNo ?>">
                                                    <?= number_format((float) $monthData['realisasi'], 0, ',', '.') ?>
                                                </button>
                                            </td>
                                            <td class="text-right <?= (float) $monthData['sisa'] < 0 ? 'text-danger font-weight-bold' : '' ?>">
                                                <?= number_format((float) $monthData['sisa'], 0, ',', '.') ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Item</th>
                                <?php foreach ($selectedMonths as $monthNo): ?>
                                    <th class="text-right">Budget</th>
                                    <th class="text-right">Realisasi</th>
                                    <th class="text-right">Sisa</th>
                                <?php endforeach; ?>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm budget-card">
                        <div class="card-header budget-card__header">
                            <h3 class="card-title">4. Detail Realisasi per Item</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="itemDetailsTable" class="table table-bordered table-hover table-striped js-budget-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-right">Nomor Pengajuan</th>
                                        <th class="text-right">Project</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($itemDetails)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Belum ada data item.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($itemDetails as $row): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-link p-0 text-left drill-item"
                                                        data-id-budget-item="<?= (int) $row['id_budget_item'] ?>"
                                                        data-label="<?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name'], ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($row['item_code'] . ' - ' . $row['item_name']) ?>
                                                    </button>
                                                </td>
                                                <td class="text-right"><?= number_format((float) $row['total_tec'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_project'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-right">Nomor Pengajuan</th>
                                        <th class="text-right">Project</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">5. Detail per Nomor Pengajuan</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="tecDetailsTable" class="table table-bordered table-hover table-sm table-striped js-budget-table">
                        <thead>
                            <tr>
                                <th>Nomor Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Bowheer</th>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th class="text-right">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tecDetails)): ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada data TEC.</td></tr>
                            <?php else: ?>
                                <?php foreach ($tecDetails as $row): ?>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-left drill-tec"
                                                data-nomor-tec="<?= htmlspecialchars($row['nomor_tec'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($row['nomor_tec']) ?>
                                            </button>
                                        </td>
                                        <td><?= htmlspecialchars($row['tanggal_cashflow']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_bowheer'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['project_name']) ?></td>
                                        <td><?= htmlspecialchars($row['pic_project'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['regional'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['kota'] ?? '-') ?></td>
                                        <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nomor Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Bowheer</th>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th class="text-right">Realisasi</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm budget-card">
                        <div class="card-header budget-card__header">
                            <h3 class="card-title">6. Detail per Nama Project</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="projectDetailsTable" class="table table-bordered table-hover table-sm table-striped js-budget-table">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th class="text-right">Pengajuan</th>
                                        <th class="text-right">PIC</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($projectDetails)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Belum ada data project.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($projectDetails as $row): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-link p-0 text-left drill-project"
                                                        data-project-name="<?= htmlspecialchars($row['project_name'], ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($row['project_name']) ?>
                                                    </button>
                                                </td>
                                                <td class="text-right"><?= number_format((float) $row['total_tec'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_pic'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Project</th>
                                        <th class="text-right">Pengajuan</th>
                                        <th class="text-right">PIC</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm budget-card">
                        <div class="card-header budget-card__header">
                            <h3 class="card-title">7. Detail per PIC Project</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="picDetailsTable" class="table table-bordered table-hover table-sm table-striped js-budget-table">
                                <thead>
                                    <tr>
                                        <th>PIC Project</th>
                                        <th class="text-right">Project</th>
                                        <th class="text-right">Pengajuan</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($picDetails)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Belum ada data PIC.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($picDetails as $row): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-link p-0 text-left drill-pic"
                                                        data-pic-project="<?= htmlspecialchars((string) ($row['pic_project'] ?: ''), ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($row['pic_project'] ?: '-') ?>
                                                    </button>
                                                </td>
                                                <td class="text-right"><?= number_format((float) $row['total_project'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_tec'], 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>PIC Project</th>
                                        <th class="text-right">Project</th>
                                        <th class="text-right">TEC</th>
                                        <th class="text-right">Realisasi</th>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm budget-card">
                <div class="card-header budget-card__header">
                    <h3 class="card-title">8. Detail per Regional / Kota</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="areaDetailsTable" class="table table-bordered table-hover table-sm table-striped js-budget-table">
                        <thead>
                            <tr>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th class="text-right">Project</th>
                                <th class="text-right">Pengajuan</th>
                                <th class="text-right">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($areaDetails)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Belum ada data area.</td></tr>
                            <?php else: ?>
                                <?php foreach ($areaDetails as $row): ?>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link p-0 text-left drill-area"
                                                data-regional="<?= htmlspecialchars((string) ($row['regional'] ?: ''), ENT_QUOTES) ?>"
                                                data-kota="<?= htmlspecialchars((string) ($row['kota'] ?: ''), ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($row['regional'] ?: '-') ?>
                                            </button>
                                        </td>
                                        <td><?= htmlspecialchars($row['kota'] ?: '-') ?></td>
                                        <td class="text-right"><?= number_format((float) $row['total_project'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((float) $row['total_tec'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((float) $row['total_realisasi'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th class="text-right">Project</th>
                                <th class="text-right">Pengajuan</th>
                                <th class="text-right">Realisasi</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="drilldownModal" tabindex="-1">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content budget-modal">
            <div class="modal-header budget-modal__header">
                <div>
                    <span class="budget-modal__eyebrow">Budget Report</span>
                    <h5 class="modal-title mb-1" id="drilldownTitle">Detail Transaksi</h5>
                    <p class="mb-0 budget-modal__subtitle">Drilldown transaksi berdasarkan item, project, PIC, area, atau Nomor Pengajuan.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="budget-form-section">
                <div class="drilldown-summary-grid mb-4">
                    <div class="claim-summary-card">
                        <span class="summary-label">Kategori</span>
                        <span class="summary-value" id="drilldownSummaryTitle">-</span>
                    </div>
                    <div class="claim-summary-card">
                        <span class="summary-label">Periode</span>
                        <span class="summary-value" id="drilldownSummaryPeriod">-</span>
                    </div>
                    <div class="claim-summary-card">
                        <span class="summary-label">Total Transaksi</span>
                        <span class="summary-value" id="drilldownSummaryCount">0</span>
                    </div>
                    <div class="claim-summary-card">
                        <span class="summary-label">Total Nominal</span>
                        <span class="summary-value" id="drilldownSummaryNominal">0</span>
                    </div>
                </div>
                <div class="drilldown-note mb-4">
                    <div class="drilldown-note__title">Keterangan Detail Report</div>
                    <p class="drilldown-note__text mb-0" id="drilldownDescription">
                        Tabel di bawah menampilkan rincian transaksi sesuai kategori yang dipilih, lengkap dengan identitas TEC, project, area, kuantitas, harga satuan, dan nominal transaksi.
                    </p>
                </div>
                <div class="table-responsive detail-table-shell">
                    <table id="drilldownTransactionsTable" class="table table-bordered table-sm table-striped js-budget-table-modal">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Nomor Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Bowheer</th>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th>Item</th>
                                <th>Direction</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Nominal</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="drilldownBody"></tbody>
                        <tfoot>
                            <tr>
                                <th>No</th>
                                <th>Nomor Pengajuan</th>
                                <th>Tanggal</th>
                                <th>Bowheer</th>
                                <th>Project</th>
                                <th>PIC</th>
                                <th>Regional</th>
                                <th>Kota</th>
                                <th>Item</th>
                                <th>Direction</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Nominal</th>
                                <th>Remarks</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
            <div class="modal-footer budget-modal__footer">
                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn budget-btn budget-btn--success" id="downloadDrilldownExcelBtn">
                    <i class="fas fa-file-excel mr-1"></i> Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    let currentDrilldownRows = [];
    let currentDrilldownTitle = 'Detail Transaksi';

    function formatBudgetNumber(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value || 0);
    }

    $(function() {
        $('.budget-card > .card-header').each(function () {
            if ($(this).find('[data-card-widget="collapse"]').length) {
                return;
            }

            $(this).append(
                '<div class="card-tools">' +
                    '<button type="button" class="btn btn-tool text-white" data-card-widget="collapse">' +
                        '<i class="fas fa-minus"></i>' +
                    '</button>' +
                '</div>'
            );
        });

        if ($.fn.DataTable) {
            const sumColumn = function (api, index) {
                return api.column(index, { search: 'applied' }).data().reduce(function (a, b) {
                    const parseNumber = function (value) {
                        if (typeof value === 'string') {
                            const cleaned = value.replace(/<[^>]*>/g, '').replace(/\./g, '').replace(/,/g, '.').replace(/[^\d.-]/g, '');
                            return parseFloat(cleaned) || 0;
                        }
                        return typeof value === 'number' ? value : 0;
                    };
                    return parseNumber(a) + parseNumber(b);
                }, 0);
            };

            const initBudgetTable = function (selector, footerCallback) {
                $(selector).DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: false,
                    autoWidth: false,
                    scrollX: true,
                    pageLength: 10,
                    footerCallback: footerCallback || null,
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        paginate: {
                            previous: 'Prev',
                            next: 'Next'
                        },
                        zeroRecords: 'Tidak ada data yang cocok'
                    }
                });
            };

            initBudgetTable('#annualComparisonTable', function () {
                const api = this.api();
                $(api.column(2).footer()).html('Total');
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
                $(api.column(4).footer()).html(sumColumn(api, 4).toLocaleString('id-ID'));
                $(api.column(5).footer()).html(sumColumn(api, 5).toLocaleString('id-ID'));
            });

            initBudgetTable('#monthlyMatrixTable', function () {
                const api = this.api();
                $(api.column(0).footer()).html('Total');
                for (let i = 1; i < api.columns().count(); i++) {
                    $(api.column(i).footer()).html(sumColumn(api, i).toLocaleString('id-ID'));
                }
            });

            initBudgetTable('#debitKreditTable', function () {
                const api = this.api();
                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(sumColumn(api, 1).toLocaleString('id-ID'));
                $(api.column(2).footer()).html(sumColumn(api, 2).toLocaleString('id-ID'));
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
                $(api.column(4).footer()).html(sumColumn(api, 4).toLocaleString('id-ID'));
            });

            initBudgetTable('#itemDetailsTable', function () {
                const api = this.api();
                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(sumColumn(api, 1).toLocaleString('id-ID'));
                $(api.column(2).footer()).html(sumColumn(api, 2).toLocaleString('id-ID'));
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
            });

            initBudgetTable('#tecDetailsTable', function () {
                const api = this.api();
                $(api.column(6).footer()).html('Total');
                $(api.column(7).footer()).html(sumColumn(api, 7).toLocaleString('id-ID'));
            });

            initBudgetTable('#projectDetailsTable', function () {
                const api = this.api();
                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(sumColumn(api, 1).toLocaleString('id-ID'));
                $(api.column(2).footer()).html(sumColumn(api, 2).toLocaleString('id-ID'));
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
            });

            initBudgetTable('#picDetailsTable', function () {
                const api = this.api();
                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(sumColumn(api, 1).toLocaleString('id-ID'));
                $(api.column(2).footer()).html(sumColumn(api, 2).toLocaleString('id-ID'));
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
            });

            initBudgetTable('#areaDetailsTable', function () {
                const api = this.api();
                $(api.column(1).footer()).html('Total');
                $(api.column(2).footer()).html(sumColumn(api, 2).toLocaleString('id-ID'));
                $(api.column(3).footer()).html(sumColumn(api, 3).toLocaleString('id-ID'));
                $(api.column(4).footer()).html(sumColumn(api, 4).toLocaleString('id-ID'));
            });
        }
    });

    const reportYear = <?= (int) $selectedYear ?>;
    const reportMonths = <?= json_encode(array_values($selectedMonths)) ?>;
    const itemIdMap = <?= json_encode(array_column($itemDetails, 'id_budget_item', 'item_code')) ?>;

    function openDrilldown(title, params) {
        params.year = reportYear;
        params.months = reportMonths;
        currentDrilldownTitle = title;
        currentDrilldownRows = [];
        $('#drilldownTitle').text(title);
        $('#drilldownSummaryTitle').text(title.replace('Detail ', ''));
        $('#drilldownSummaryPeriod').text('<?= htmlspecialchars($monthNames[$startMonth]) ?> - <?= htmlspecialchars($monthNames[$endMonth]) ?> <?= (int) $selectedYear ?>');
        $('#drilldownSummaryCount').text('0');
        $('#drilldownSummaryNominal').text('0');
        $('#drilldownDescription').text('Tabel di bawah menampilkan rincian transaksi untuk ' + title.replace('Detail ', '').toLowerCase() + ', lengkap dengan identitas TEC, lokasi, item, kuantitas, harga satuan, dan nominal transaksi.');

        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#drilldownTransactionsTable')) {
            $('#drilldownTransactionsTable').DataTable().clear().destroy();
        }

        $('#drilldownTransactionsTable').removeAttr('style');
        $('#drilldownTransactionsTable colgroup').remove();
        $('#drilldownTransactionsTable thead th').removeAttr('style');
        $('#drilldownTransactionsTable tbody td').removeAttr('style');
        $('#drilldownTransactionsTable tfoot th').removeAttr('style');

        $('#drilldownBody').html('<tr><td colspan="14" class="text-center">Loading...</td></tr>');
        $('#drilldownModal').modal('show');

        $.ajax({
            url: '<?= base_url('Budget_Report/drilldown') ?>',
            type: 'GET',
            dataType: 'text',
            data: params
        }).done(function(responseText) {
            let response = { rows: [] };
            try {
                response = JSON.parse($.trim(responseText));
            } catch (error) {
                $('#drilldownBody').html('<tr><td colspan="14" class="text-center text-danger">Data drilldown gagal dibaca.</td></tr>');
                return;
            }

            let html = '';
            const rows = response.rows || [];
            currentDrilldownRows = rows;
            let totalNominal = 0;

            if (!rows.length) {
                html = '<tr><td colspan="14" class="text-center text-muted">Tidak ada transaksi.</td></tr>';
            } else {
                rows.forEach(function(row, index) {
                    totalNominal += Number(row.nominal || 0);
                    html += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + (row.nomor_tec || '-') + '</td>' +
                        '<td>' + (row.tanggal_cashflow || '-') + '</td>' +
                        '<td>' + (row.nama_bowheer || '-') + '</td>' +
                        '<td>' + (row.project_name || '-') + '</td>' +
                        '<td>' + (row.pic_project || '-') + '</td>' +
                        '<td>' + (row.regional || '-') + '</td>' +
                        '<td>' + (row.kota || '-') + '</td>' +
                        '<td>' + ((row.item_code || '-') + ' - ' + (row.item_name || '-')) + '</td>' +
                        '<td>' + (row.direction || '-') + '</td>' +
                        '<td class="text-right">' + Number(row.qty || 0).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(row.unit_price || 0).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(row.nominal || 0).toLocaleString('id-ID') + '</td>' +
                        '<td>' + (row.remarks_item || '-') + '</td>' +
                    '</tr>';
                });
            }

            $('#drilldownBody').html(html);
            $('#drilldownSummaryCount').text(formatBudgetNumber(rows.length));
            $('#drilldownSummaryNominal').text(formatBudgetNumber(totalNominal));
            $('#drilldownTransactionsTable tfoot th').html('');
            $('#drilldownTransactionsTable tfoot th').eq(0).html('Total');
            $('#drilldownTransactionsTable tfoot th').eq(10).html(formatBudgetNumber(rows.reduce(function(total, row) {
                return total + (Number(row.qty || 0));
            }, 0)));
            $('#drilldownTransactionsTable tfoot th').eq(11).html(formatBudgetNumber(rows.reduce(function(total, row) {
                return total + (Number(row.unit_price || 0));
            }, 0)));
            $('#drilldownTransactionsTable tfoot th').eq(12).html(formatBudgetNumber(totalNominal));
        }).fail(function () {
            $('#drilldownBody').html('<tr><td colspan="14" class="text-center text-danger">Data drilldown gagal dimuat.</td></tr>');
        });
    }

    $('#drilldownModal').on('hidden.bs.modal', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#drilldownTransactionsTable')) {
            $('#drilldownTransactionsTable').DataTable().clear().destroy();
        }

        currentDrilldownRows = [];
        currentDrilldownTitle = 'Detail Transaksi';
        $('#drilldownTransactionsTable').removeAttr('style');
        $('#drilldownTransactionsTable colgroup').remove();
        $('#drilldownTransactionsTable thead th').removeAttr('style');
        $('#drilldownTransactionsTable tbody td').removeAttr('style');
        $('#drilldownTransactionsTable tfoot th').removeAttr('style').html('');
        $('#drilldownBody').html('');
        $('#drilldownSummaryCount').text('0');
        $('#drilldownSummaryNominal').text('0');
        $('#drilldownDescription').text('Tabel di bawah menampilkan rincian transaksi sesuai kategori yang dipilih, lengkap dengan identitas TEC, project, area, kuantitas, harga satuan, dan nominal transaksi.');
    });

    $(document).on('click', '.drill-item', function() {
        const itemCode = $(this).data('item-code') || ($(this).text().trim());
        const label = $(this).data('label') || itemCode;
        const itemId = $(this).data('id-budget-item') || itemIdMap[itemCode] || 0;
        if (!itemId) return;
        openDrilldown('Detail Item - ' + label, { type: 'item', id_budget_item: itemId });
    });

    $(document).on('click', '.drill-item-month', function() {
        const itemCode = $(this).data('item-code');
        const label = $(this).data('label');
        const monthNo = $(this).data('month-no');
        const itemId = itemIdMap[itemCode] || 0;
        if (!itemId) return;
        openDrilldown('Detail Item Bulanan - ' + label, { type: 'item', id_budget_item: itemId, month_no: monthNo });
    });

    $(document).on('click', '.drill-project', function() {
        const projectName = $(this).data('project-name');
        openDrilldown('Detail Project - ' + projectName, { type: 'project', project_name: projectName });
    });

    $(document).on('click', '.drill-pic', function() {
        const picProject = $(this).data('pic-project') || '';
        openDrilldown('Detail PIC Project - ' + (picProject || '-'), { type: 'pic', pic_project: picProject });
    });

    $(document).on('click', '.drill-area', function() {
        const regional = $(this).data('regional') || '';
        const kota = $(this).data('kota') || '';
        openDrilldown('Detail Area - ' + (regional || '-') + ' / ' + (kota || '-'), { type: 'area', regional: regional, kota: kota });
    });

    $(document).on('click', '.drill-tec', function() {
        const nomorTec = $(this).data('nomor-tec');
        openDrilldown('Detail TEC - ' + nomorTec, { type: 'tec', nomor_tec: nomorTec });
    });

    function buildBudgetReportExportUrl(type) {
        const params = new URLSearchParams();
        params.set('year', $('select[name="year"]').val() || '<?= (int) $selectedYear ?>');
        params.set('start_month', $('select[name="start_month"]').val() || '<?= (int) $startMonth ?>');
        params.set('end_month', $('select[name="end_month"]').val() || '<?= (int) $endMonth ?>');

        if (type === 'excel') {
            return '<?= base_url('Budget_Report/exportExcel') ?>?' + params.toString();
        }

        return '<?= base_url('Budget_Report/exportCsv') ?>?' + params.toString();
    }

    $(document).on('click', '.js-budget-report-export', function() {
        const type = ($(this).data('export-type') || '').toString().toLowerCase();
        if (!type) {
            return;
        }

        window.location.href = buildBudgetReportExportUrl(type);
    });

    $('#downloadDrilldownExcelBtn').on('click', function() {
        if (typeof XLSX === 'undefined') {
            Swal.fire('Gagal', 'Library export Excel belum tersedia.', 'error');
            return;
        }

        if (!currentDrilldownRows.length) {
            Swal.fire('Info', 'Belum ada data detail untuk di-download.', 'info');
            return;
        }

        const exportRows = currentDrilldownRows.map(function(row, index) {
            return {
                No: index + 1,
                'Nomor Pengajuan': row.nomor_tec || '-',
                Tanggal: row.tanggal_cashflow || '-',
                Bowheer: row.nama_bowheer || '-',
                Project: row.project_name || '-',
                PIC: row.pic_project || '-',
                Regional: row.regional || '-',
                Kota: row.kota || '-',
                Item: ((row.item_code || '-') + ' - ' + (row.item_name || '-')),
                Direction: row.direction || '-',
                Qty: Number(row.qty || 0),
                'Unit Price': Number(row.unit_price || 0),
                Nominal: Number(row.nominal || 0),
                Remarks: row.remarks_item || '-'
            };
        });

        const worksheet = XLSX.utils.json_to_sheet(exportRows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Detail Report');
        XLSX.writeFile(workbook, (currentDrilldownTitle || 'detail_report').replace(/[^\w\s-]/g, '').replace(/\s+/g, '_') + '.xlsx');
    });
</script>

<style>
    .budget-toolbar {
        gap: 10px;
    }

    .budget-field-label {
        display: inline-block;
        margin-bottom: 8px;
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #48657f;
        text-transform: uppercase;
    }

    .budget-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 48px rgba(14, 41, 64, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    }

    .budget-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 30%),
            linear-gradient(135deg, #123d5d, #1b78ab 54%, #5ab7db);
        color: #fff;
    }

    .budget-card__header .card-tools {
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .budget-card .card-title {
        font-weight: 700;
    }

    .budget-btn {
        border: 0;
        border-radius: 12px;
        padding: 0.68rem 1.15rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        transition: all 0.2s ease;
        box-shadow: 0 12px 22px rgba(16, 59, 90, 0.12);
    }

    .budget-btn:hover,
    .budget-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(16, 59, 90, 0.16);
    }

    .budget-btn--primary {
        background: linear-gradient(135deg, #103b5a 0%, #1f6da1 100%);
        color: #fff;
    }

    .budget-btn--success {
        background: linear-gradient(135deg, #0f8b72 0%, #24b18f 100%);
        color: #fff;
    }

    .budget-btn--ghost {
        background: #fff;
        color: #315d7f;
        border: 1px solid #d7e6f2;
        box-shadow: 0 10px 22px rgba(112, 141, 165, 0.12);
    }

    .budget-input {
        border-radius: 12px;
        border: 1px solid #cfe0ee;
        min-height: 44px;
        box-shadow: none;
    }

    .budget-input:focus {
        border-color: #55a7d5;
        box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
    }

    .small-box {
        border-radius: 20px;
        box-shadow: 0 18px 36px rgba(15, 46, 77, 0.08);
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.32);
    }

    .small-box::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.24), transparent 48%);
        pointer-events: none;
    }

    .small-box .inner {
        position: relative;
        z-index: 1;
        padding: 22px 20px;
    }

    .summary-dashboard-grid .small-box h3 {
        margin-bottom: 6px;
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .summary-dashboard-grid .small-box p {
        margin-bottom: 0;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.92;
    }

    .summary-dashboard-grid .bg-info {
        background: linear-gradient(135deg, #164e63 0%, #0ea5e9 100%) !important;
    }

    .summary-dashboard-grid .bg-success {
        background: linear-gradient(135deg, #166534 0%, #22c55e 100%) !important;
    }

    .summary-dashboard-grid .bg-primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #60a5fa 100%) !important;
    }

    .summary-dashboard-grid .bg-warning {
        background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important;
        color: #fff !important;
    }

    .summary-dashboard-grid .bg-secondary {
        background: linear-gradient(135deg, #475569 0%, #94a3b8 100%) !important;
    }

    .summary-dashboard-grid .bg-danger {
        background: linear-gradient(135deg, #b91c1c 0%, #f87171 100%) !important;
    }

    .summary-dashboard-grid .bg-dark {
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%) !important;
    }

    .budget-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 50px rgba(8, 35, 55, 0.22);
    }

    .budget-modal__header {
        border-bottom: 0;
        padding: 1.4rem 1.5rem 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 30%),
            linear-gradient(135deg, #103b5a 0%, #1f6da1 55%, #53a9d8 100%);
        color: #fff;
    }

    .budget-modal__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.76);
    }

    .budget-modal__subtitle {
        max-width: 85%;
        color: rgba(255, 255, 255, 0.84);
        font-size: 0.92rem;
    }

    .budget-form-section {
        margin-bottom: 0;
        padding: 1rem 1rem 0.9rem;
        border: 1px solid #dbe9f4;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .detail-table-shell {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #d8e7f2;
        background: #fff;
    }

    .modal-xxl {
        max-width: 86vw;
    }

    .drilldown-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .claim-summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
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
        line-height: 1.2;
        word-break: break-word;
    }

    .drilldown-note {
        padding: 1rem 1.1rem;
        border-radius: 16px;
        border: 1px solid #d9e8f3;
        background: linear-gradient(180deg, #fdfefe 0%, #f2f8fc 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .drilldown-note__title {
        margin-bottom: 0.35rem;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2e607f;
    }

    .drilldown-note__text {
        color: #587389;
        line-height: 1.6;
        font-size: 0.94rem;
    }

    .js-budget-table thead th,
    .js-budget-table tfoot th,
    .js-budget-table-modal thead th,
    .js-budget-table-modal tfoot th {
        white-space: nowrap;
    }

    .js-budget-table thead th,
    .js-budget-table-modal thead th,
    #drilldownTransactionsTable thead th {
        text-align: center;
        vertical-align: middle;
    }

    .js-budget-table tfoot th,
    .js-budget-table-modal tfoot th {
        background: #eef5fb;
        color: #315d7f;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border-radius: 10px;
        border: 1px solid #cfe0ee;
        box-shadow: none;
    }

    @media (max-width: 767.98px) {
        .budget-toolbar {
            margin-top: 1rem;
            justify-content: flex-start !important;
        }

        .budget-btn {
            width: 100%;
        }

        .budget-modal__subtitle {
            max-width: 100%;
        }

        .drilldown-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
