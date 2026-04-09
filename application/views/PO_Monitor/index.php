<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');

?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">PO MONITORING</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <?php
                $bowheerSummaryMap = [];
                foreach ($bowheerSummary as $summary) {
                    $bowheerSummaryMap[(string) $summary['id_bowheer']] = $summary;
                }

                $uniqueBowheer = [];
                foreach ($poList as $po) {
                    $bowheerName = !empty($po['nama_bowheer']) ? $po['nama_bowheer'] : 'Tanpa Bowheer';
                    $uniqueBowheer[$bowheerName] = $bowheerName;
                }
                ksort($uniqueBowheer);

                $dashboardTotalPo = 0;
                $dashboardDoneInvoice = 0;
                $dashboardNyInvoice = 0;

                foreach ($poList as $po) {
                    $dashboardTotalPo += (float) $po['current_release_value'];
                    $dashboardDoneInvoice += (float) $po['total_invoiced'];
                }
                $dashboardNyInvoice = $dashboardTotalPo - $dashboardDoneInvoice;
                if ($dashboardNyInvoice < 0) {
                    $dashboardNyInvoice = 0;
                }

                $summaryTotals = [
                    'total_po_count' => 0,
                    'current_release_value' => 0,
                    'total_invoiced' => 0,
                    'remaining' => 0
                ];

                foreach ($bowheerSummary as $summary) {
                    $summaryTotals['total_po_count'] += (float) $summary['total_po'];
                    $summaryTotals['current_release_value'] += (float) $summary['current_release_value'];
                    $summaryTotals['total_invoiced'] += (float) $summary['total_invoiced'];
                    $summaryTotals['remaining'] += (float) $summary['remaining'];
                }

                $matrixTotals = [
                    'total_po' => 0,
                    'term_done' => 0,
                    'outstanding_term' => 0,
                    'termint' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
                ];

                $detailTermTotals = [
                    'term_value' => 0,
                    'invoiced_amount' => 0,
                    'remaining' => 0
                ];

                $poTableTotals = [
                    'current_release_value' => 0,
                    'total_invoiced' => 0,
                    'remaining' => 0
                ];

                foreach ($poList as $po) {
                    $poRemaining = (float) $po['current_release_value'] - (float) $po['total_invoiced'];
                    if ($poRemaining < 0) {
                        $poRemaining = 0;
                    }

                    $poTableTotals['current_release_value'] += (float) $po['current_release_value'];
                    $poTableTotals['total_invoiced'] += (float) $po['total_invoiced'];
                    $poTableTotals['remaining'] += $poRemaining;
                }
                ?>

                <form method="get" action="<?= site_url('PO_Monitor') ?>">
                <div class="card card-primary direct-chat direct-chat-primary shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title">FILTER DATA</h3>
                        <div class="card-tools">
                            <button id="cardfiltercollapse" type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row p-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="display: flex; justify-content: center; align-items: center;">PROJECT / BOWHEER</label>
                                    <select id="filter_bowheer_up" name="bowheer[]" class="select2" multiple="multiple" data-placeholder="Pilih bowheer" style="width: 100%;">
                                        <?php foreach ($uniqueBowheer as $bowheerName): ?>
                                            <option value="<?= htmlspecialchars($bowheerName) ?>" <?= in_array($bowheerName, $selectedBowheer ?? [], true) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($bowheerName) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="display: flex; justify-content: center; align-items: center;">SLA STATUS</label>
                                    <select id="filter_sla_up" name="sla[]" class="select2" multiple="multiple" data-placeholder="Pilih SLA status" style="width: 100%;">
                                        <option value="AMAN" <?= in_array('AMAN', $selectedSla ?? [], true) ? 'selected' : '' ?>>AMAN</option>
                                        <option value="WARNING" <?= in_array('WARNING', $selectedSla ?? [], true) ? 'selected' : '' ?>>WARNING</option>
                                        <option value="OVERDUE" <?= in_array('OVERDUE', $selectedSla ?? [], true) ? 'selected' : '' ?>>OVERDUE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 text-right">
                                <a href="<?= site_url('PO_Monitor') ?>" id="reset_filter_po_monitor" class="btn btn-danger">Delete</a>
                                <button type="submit" id="btnFilterPOMonitor" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
                </form>

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3 id="summary_total_po"><?= number_format($dashboardTotalPo, 0, ',', '.') ?></h3>
                                <p>Total PO</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="summary_done_invoice"><?= number_format($dashboardDoneInvoice, 0, ',', '.') ?></h3>
                                <p>Done Invoice</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="summary_ny_invoice"><?= number_format($dashboardNyInvoice, 0, ',', '.') ?></h3>
                                <p>NY Invoice</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">List Bedah PO Group By Bowheer</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_po_bowheer_summary" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bowheer</th>
                                    <th>Total PO</th>
                                    <th>Current Release</th>
                                    <th>Total Invoiced</th>
                                    <th>Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bowheerSummary)): ?>
                                    <?php $noBowheer = 1; foreach ($bowheerSummary as $summary): ?>
                                        <tr data-bowheer="<?= htmlspecialchars($summary['nama_bowheer']) ?>">
                                            <td><?= $noBowheer++ ?></td>
                                            <td><?= htmlspecialchars($summary['nama_bowheer']) ?></td>
                                            <td><?= number_format((float) $summary['total_po'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['current_release_value'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['total_invoiced'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['remaining'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr data-bowheer="<?= htmlspecialchars(!empty($po['nama_bowheer']) ? $po['nama_bowheer'] : 'Tanpa Bowheer') ?>"
                                        data-sla="<?= $sla ?>">
                                        <td colspan="6" class="text-center">Belum ada data PO per bowheer.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-center">TOTAL</th>
                                    <th><?= number_format($summaryTotals['total_po_count'], 0, ',', '.') ?></th>
                                    <th><?= number_format($summaryTotals['current_release_value'], 0, ',', '.') ?></th>
                                    <th><?= number_format($summaryTotals['total_invoiced'], 0, ',', '.') ?></th>
                                    <th><?= number_format($summaryTotals['remaining'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">List Bowheer dan Tagihan Term</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="table_po_bowheer_matrix" class="table table-bordered table-striped mb-4">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle; width: 35px;">No</th>
                                    <th rowspan="2" style="vertical-align: middle;">Bowheer</th>
                                    <th rowspan="2" style="vertical-align: middle;">TOTAL PO</th>
                                    <th rowspan="2" style="vertical-align: middle;">TERM DONE</th>
                                    <th colspan="5" class="text-center">TERMINT</th>
                                    <th rowspan="2" style="vertical-align: middle;">OUSTANDING TERM</th>
                                </tr>
                                <tr>
                                    <th class="text-center">1</th>
                                    <th class="text-center">2</th>
                                    <th class="text-center">3</th>
                                    <th class="text-center">4</th>
                                    <th class="text-center">5</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bowheerTermBreakdown)): ?>
                                    <?php $noMatrix = 1; foreach ($bowheerTermBreakdown as $bowheer): ?>
                                        <?php
                                        $summary = isset($bowheerSummaryMap[(string) $bowheer['id_bowheer']]) ? $bowheerSummaryMap[(string) $bowheer['id_bowheer']] : null;
                                        $termRemainingMap = [];

                                        foreach ($bowheer['terms'] as $term) {
                                            $termRemainingMap[(int) $term['term_index']] = (float) $term['remaining'];
                                        }

                                        $totalPo = $summary ? (float) $summary['current_release_value'] : 0;
                                        $termDone = $summary ? (float) $summary['total_invoiced'] : 0;
                                        $outstandingTerm = $summary ? (float) $summary['remaining'] : 0;

                                        $matrixTotals['total_po'] += $totalPo;
                                        $matrixTotals['term_done'] += $termDone;
                                        $matrixTotals['outstanding_term'] += $outstandingTerm;

                                        for ($termIndex = 1; $termIndex <= 5; $termIndex++) {
                                            $matrixTotals['termint'][$termIndex] += isset($termRemainingMap[$termIndex]) ? $termRemainingMap[$termIndex] : 0;
                                        }
                                        ?>
                                        <tr data-bowheer="<?= htmlspecialchars($bowheer['nama_bowheer']) ?>">
                                            <td><?= $noMatrix++ ?></td>
                                            <td><?= htmlspecialchars($bowheer['nama_bowheer']) ?></td>
                                            <td><?= number_format($totalPo, 0, ',', '.') ?></td>
                                            <td><?= number_format($termDone, 0, ',', '.') ?></td>
                                            <?php for ($termIndex = 1; $termIndex <= 5; $termIndex++): ?>
                                                <?php $termValue = isset($termRemainingMap[$termIndex]) ? $termRemainingMap[$termIndex] : 0; ?>
                                                <td class="text-center">
                                                    <?= $termValue > 0 ? number_format($termValue, 0, ',', '.') : '-' ?>
                                                </td>
                                            <?php endfor; ?>
                                            <td><?= number_format($outstandingTerm, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">Belum ada data bowheer dan term.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-center">TOTAL</th>
                                    <th><?= number_format($matrixTotals['total_po'], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['term_done'], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['termint'][1], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['termint'][2], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['termint'][3], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['termint'][4], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['termint'][5], 0, ',', '.') ?></th>
                                    <th><?= number_format($matrixTotals['outstanding_term'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>

                        <table id="table_po_bowheer_term_detail" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 35px;">No</th>
                                    <th style="min-width: 240px;">Bowheer</th>
                                    <th>Tagihan Term</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bowheerTermBreakdown)): ?>
                                    <?php $noBreakdown = 1; foreach ($bowheerTermBreakdown as $bowheer): ?>
                                        <tr data-bowheer="<?= htmlspecialchars($bowheer['nama_bowheer']) ?>">
                                            <td><?= $noBreakdown++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($bowheer['nama_bowheer']) ?></strong>
                                                <br>
                                                <small>Total PO: <?= number_format((float) $bowheer['total_po'], 0, ',', '.') ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($bowheer['terms'])): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Term</th>
                                                                    <th>Nilai Term</th>
                                                                    <th>Sudah Ditagihkan</th>
                                                                    <th>Sisa</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($bowheer['terms'] as $term): ?>
                                                                    <?php
                                                                    $detailTermTotals['term_value'] += (float) $term['term_value'];
                                                                    $detailTermTotals['invoiced_amount'] += (float) $term['invoiced_amount'];
                                                                    $detailTermTotals['remaining'] += (float) $term['remaining'];
                                                                    ?>
                                                                    <tr>
                                                                        <td>Term <?= (int) $term['term_index'] ?></td>
                                                                        <td><?= number_format((float) $term['term_value'], 0, ',', '.') ?></td>
                                                                        <td><?= number_format((float) $term['invoiced_amount'], 0, ',', '.') ?></td>
                                                                        <td><?= number_format((float) $term['remaining'], 0, ',', '.') ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th>TOTAL</th>
                                                                    <th><?= number_format(array_sum(array_column($bowheer['terms'], 'term_value')), 0, ',', '.') ?></th>
                                                                    <th><?= number_format(array_sum(array_column($bowheer['terms'], 'invoiced_amount')), 0, ',', '.') ?></th>
                                                                    <th><?= number_format(array_sum(array_column($bowheer['terms'], 'remaining')), 0, ',', '.') ?></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ada tagihan term.</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada data bowheer dan term.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-center">TOTAL</th>
                                    <th>
                                        Nilai Term: <?= number_format($detailTermTotals['term_value'], 0, ',', '.') ?> |
                                        Sudah Ditagihkan: <?= number_format($detailTermTotals['invoiced_amount'], 0, ',', '.') ?> |
                                        Sisa: <?= number_format($detailTermTotals['remaining'], 0, ',', '.') ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body table-responsive">
                        <div class="mb-3">
                            <a href="<?= site_url('PO_Monitor/create') ?>" class="btn btn-success">Tambah PO</a>
                        </div>
                        <table id="table_po_monitor_list" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>PO Number</th>
                                    <th>PO Date</th>
                                    <th>Current Release</th>
                                    <th>Total Invoiced</th>
                                    <th>Remaining</th>
                                    <th>SLA Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($poList as $po):
                                    $remaining = floatval($po['current_release_value']) - floatval($po['total_invoiced']);
                                    if ($remaining < 0) $remaining = 0;
                                    $sla = !empty($po['sla']) ? $po['sla'] : 'AMAN';
                                    ?>
                                    <tr data-bowheer="<?= htmlspecialchars(!empty($po['nama_bowheer']) ? $po['nama_bowheer'] : 'Tanpa Bowheer') ?>"
                                        data-sla="<?= htmlspecialchars($sla) ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($po['po_number']) ?></td>
                                        <td><?= $po['po_date'] ?></td>
                                        <td><?= number_format(floatval($po['current_release_value']),0,',','.') ?></td>
                                        <td><?= number_format(floatval($po['total_invoiced']),0,',','.') ?></td>
                                        <td><?= number_format($remaining,0,',','.') ?></td>
                                        <td><?= $sla ?></td>
                                        <td>
                                            <a href="<?= site_url('PO_Monitor/detail/'.$po['id_po']) ?>" class="btn btn-sm btn-primary">Detail</a>
                                            <button class="btn btn-sm btn-warning btn-allocate" data-id="<?= $po['id_po'] ?>">Auto-allocate</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-center">TOTAL</th>
                                    <th><?= number_format($poTableTotals['current_release_value'], 0, ',', '.') ?></th>
                                    <th><?= number_format($poTableTotals['total_invoiced'], 0, ',', '.') ?></th>
                                    <th><?= number_format($poTableTotals['remaining'], 0, ',', '.') ?></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    (function() {
        function parseLocaleNumber(value) {
            if (typeof value === 'number') {
                return value;
            }

            if (value === null || value === undefined) {
                return 0;
            }

            var normalized = String(value).replace(/<[^>]*>/g, '').trim();
            if (normalized === '' || normalized === '-') {
                return 0;
            }

            normalized = normalized.replace(/\./g, '').replace(/,/g, '.').replace(/[^\d.-]/g, '');
            var parsed = parseFloat(normalized);
            return isNaN(parsed) ? 0 : parsed;
        }

        function formatLocaleNumber(value) {
            return Number(value || 0).toLocaleString('id-ID');
        }

        function sumColumn(api, columnIndex) {
            return api
                .column(columnIndex, { search: 'applied' })
                .data()
                .reduce(function(total, value) {
                    return total + parseLocaleNumber(value);
                }, 0);
        }

        function initAdminLteTable($, selector, orderConfig, footerCallback) {
            if (!$.fn.DataTable || !$(selector).length || $.fn.DataTable.isDataTable(selector)) {
                return;
            }

            $(selector).DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                info: true,
                lengthChange: true,
                autoWidth: false,
                responsive: false,
                ordering: true,
                order: orderConfig || [],
                footerCallback: footerCallback || null
            });
        }

        function bootstrapPOMonitor() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable || !window.jQuery.fn.select2) {
                window.setTimeout(bootstrapPOMonitor, 150);
                return;
            }

            var $ = window.jQuery;

            $('#filter_bowheer_up, #filter_sla_up').select2({
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });

            initAdminLteTable($, '#table_po_bowheer_summary', [[1, 'asc']], function() {
                var api = this.api();
                $(api.column(2).footer()).html(formatLocaleNumber(sumColumn(api, 2)));
                $(api.column(3).footer()).html(formatLocaleNumber(sumColumn(api, 3)));
                $(api.column(4).footer()).html(formatLocaleNumber(sumColumn(api, 4)));
                $(api.column(5).footer()).html(formatLocaleNumber(sumColumn(api, 5)));
            });

            initAdminLteTable($, '#table_po_bowheer_matrix', [[1, 'asc']], function() {
                var api = this.api();
                $(api.column(2).footer()).html(formatLocaleNumber(sumColumn(api, 2)));
                $(api.column(3).footer()).html(formatLocaleNumber(sumColumn(api, 3)));
                $(api.column(4).footer()).html(formatLocaleNumber(sumColumn(api, 4)));
                $(api.column(5).footer()).html(formatLocaleNumber(sumColumn(api, 5)));
                $(api.column(6).footer()).html(formatLocaleNumber(sumColumn(api, 6)));
                $(api.column(7).footer()).html(formatLocaleNumber(sumColumn(api, 7)));
                $(api.column(8).footer()).html(formatLocaleNumber(sumColumn(api, 8)));
                $(api.column(9).footer()).html(formatLocaleNumber(sumColumn(api, 9)));
            });

            initAdminLteTable($, '#table_po_bowheer_term_detail', [[1, 'asc']], function() {
                var api = this.api();
                var totalTermValue = 0;
                var totalInvoiced = 0;
                var totalRemaining = 0;

                api.rows({ search: 'applied' }).nodes().to$().each(function() {
                    $(this).find('table tbody tr').each(function() {
                        var $cells = $(this).find('td');
                        totalTermValue += parseLocaleNumber($cells.eq(1).text());
                        totalInvoiced += parseLocaleNumber($cells.eq(2).text());
                        totalRemaining += parseLocaleNumber($cells.eq(3).text());
                    });
                });

                $(api.column(2).footer()).html(
                    'Nilai Term: ' + formatLocaleNumber(totalTermValue) +
                    ' | Sudah Ditagihkan: ' + formatLocaleNumber(totalInvoiced) +
                    ' | Sisa: ' + formatLocaleNumber(totalRemaining)
                );
            });

            initAdminLteTable($, '#table_po_monitor_list', [[2, 'desc']], function() {
                var api = this.api();
                $(api.column(3).footer()).html(formatLocaleNumber(sumColumn(api, 3)));
                $(api.column(4).footer()).html(formatLocaleNumber(sumColumn(api, 4)));
                $(api.column(5).footer()).html(formatLocaleNumber(sumColumn(api, 5)));
            });

            $(document)
                .off('click.poMonitorAllocate')
                .on('click.poMonitorAllocate', '.btn-allocate', function() {
                    var id = $(this).data('id');
                    if (!confirm('Auto-allocate invoices to terms for this PO?')) return;
                    $.post('<?= site_url('PO_Monitor/allocate') ?>', { id_po: id }, function(resp) {
                        try { resp = JSON.parse(resp); } catch (e) {}
                        if (resp && resp.status) {
                            alert('Allocation completed. Inserted: ' + (resp.allocations_inserted || 0));
                            location.reload();
                        } else {
                            alert('Error: ' + ((resp && resp.message) || 'unknown'));
                        }
                    });
                });
        }

        bootstrapPOMonitor();
    })();
</script>
