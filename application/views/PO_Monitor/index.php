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

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= number_format($dashboardTotalPo, 0, ',', '.') ?></h3>
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
                                <h3><?= number_format($dashboardDoneInvoice, 0, ',', '.') ?></h3>
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
                                <h3><?= number_format($dashboardNyInvoice, 0, ',', '.') ?></h3>
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
                                        <tr>
                                            <td><?= $noBowheer++ ?></td>
                                            <td><?= htmlspecialchars($summary['nama_bowheer']) ?></td>
                                            <td><?= number_format((float) $summary['total_po'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['current_release_value'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['total_invoiced'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $summary['remaining'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
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
                                        <tr>
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
                                        <tr>
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
                                    // basic SLA: if any term overdue -> OVERDUE else if any term within 7 days -> WARNING else AMAN
                                    $terms = $this->MPO_Monitor->getPOTerms($po['id_po']);
                                    $sla = 'AMAN';
                                    foreach ($terms as $t) {
                                        $termRemain = floatval($t['value']) - floatval($t['invoiced_amount']);
                                        if ($termRemain > 0) {
                                            if (!empty($t['due_date']) && strtotime($t['due_date']) < strtotime(date('Y-m-d'))) {
                                                $sla = 'OVERDUE';
                                                break;
                                            }
                                            if (!empty($t['due_date']) && strtotime($t['due_date']) <= strtotime('+7 days')) {
                                                $sla = 'WARNING';
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
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
        function initAdminLteTable($, selector, orderConfig) {
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
                order: orderConfig || []
            });
        }

        function bootstrapPOMonitor() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
                window.setTimeout(bootstrapPOMonitor, 150);
                return;
            }

            var $ = window.jQuery;

            initAdminLteTable($, '#table_po_bowheer_summary', [[1, 'asc']]);
            initAdminLteTable($, '#table_po_bowheer_matrix', [[1, 'asc']]);
            initAdminLteTable($, '#table_po_bowheer_term_detail', [[1, 'asc']]);
            initAdminLteTable($, '#table_po_monitor_list', [[2, 'desc']]);

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
