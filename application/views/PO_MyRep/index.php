<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('poMyRepNumber')) {
    function poMyRepNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
?>

<style>
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

    .po-breakdown-link {
        cursor: pointer;
        color: inherit;
        text-decoration: underline;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">PO MyRep</h1>
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
                <div class="alert alert-warning">Tabel PO MyRep belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Filter PO MyRep</h3>
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
                                    <label>Status PO</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    <a href="<?= base_url('PO_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                                <p>Total Cluster</p>
                            </div>
                            <div class="icon"><i class="fas fa-network-wired"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= (int) ($summary['not_issued'] ?? 0) ?></h3>
                                <p>Not Issued</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-circle-question"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= (int) ($summary['issued'] ?? 0) ?></h3>
                                <p>Issued</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-signature"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= (int) ($summary['partial_payment'] ?? 0) ?></h3>
                                <p>Partial Payment</p>
                            </div>
                            <div class="icon"><i class="fas fa-hand-holding-dollar"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (int) ($summary['fully_paid'] ?? 0) ?></h3>
                                <p>Fully Paid</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-dark">
                            <div class="inner">
                                <h3><?= (int) ($summary['closed'] ?? 0) ?></h3>
                                <p>Closed</p>
                            </div>
                            <div class="icon"><i class="fas fa-box-archive"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Pembagian Termin (Cluster & Subfeeder)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Tipe PO</th>
                                        <th rowspan="2">PO QTY</th>
                                        <th rowspan="2">Total PO</th>
                                        <th colspan="5" class="text-center">Outstanding</th>
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
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars((string) ($row['po_type'] ?? '-')) ?></strong></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="po_qty"><?= poMyRepNumber((float) ($row['total_po_value'] ?? 0)) ?></span></td>
                                            <td class="text-center"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="term_done"><?= (int) ($row['term_done_count'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="1"><?= poMyRepNumber((float) ($row['termin_values'][1] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="2"><?= poMyRepNumber((float) ($row['termin_values'][2] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="3"><?= poMyRepNumber((float) ($row['termin_values'][3] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="4"><?= poMyRepNumber((float) ($row['termin_values'][4] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="5"><?= poMyRepNumber((float) ($row['termin_values'][5] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_invoiced"><?= poMyRepNumber((float) ($row['total_invoiced_value'] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_total"><?= poMyRepNumber((float) ($row['outstanding_value'] ?? 0)) ?></span></td>
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
                                            <th class="text-right"><?= poMyRepNumber($sumTotalPo) ?></th>
                                            <th class="text-center"><?= (int) $sumTermDone ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTermin[1]) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTermin[2]) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTermin[3]) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTermin[4]) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTermin[5]) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTotalInvoiced) ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumOutstanding) ?></th>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Monitoring PO MyRep</h3>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="po-myrep-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="po-monitor-tab" data-toggle="pill" href="#po-monitor-pane" role="tab" aria-controls="po-monitor-pane" aria-selected="true">Monitoring Cluster</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="po-list-tab" data-toggle="pill" href="#po-list-pane" role="tab" aria-controls="po-list-pane" aria-selected="false">List PO</a>
                            </li>
                        </ul>
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="po-monitor-pane" role="tabpanel" aria-labelledby="po-monitor-tab">
                                <div class="table-responsive">
                                    <table id="table_po_myrep" class="table table-bordered table-hover">
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
                                                $statusBadgeClass = 'secondary';
                                                $summaryStatus = strtoupper(trim((string) ($row['po_summary_status'] ?? 'NOT ISSUED')));
                                                if ($summaryStatus === 'FULLY PAID') {
                                                    $statusBadgeClass = 'success';
                                                } elseif ($summaryStatus === 'PARTIAL PAYMENT') {
                                                    $statusBadgeClass = 'warning';
                                                } elseif ($summaryStatus === 'ISSUED') {
                                                    $statusBadgeClass = 'primary';
                                                } elseif ($summaryStatus === 'CLOSED') {
                                                    $statusBadgeClass = 'dark';
                                                }
                                                $terminTotal = (int) ($row['termin_total_count'] ?? 0);
                                                $terminProgress = (int) ($row['termin_progress_count'] ?? $row['termin_paid_count'] ?? 0);
                                                $terminPercent = $terminTotal > 0 ? min(100, round(($terminProgress / $terminTotal) * 100)) : 0;
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars((string) ($row['team_name'] ?? '-')) ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                                    <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current'] ?? '-')) ?></span></td>
                                                    <td>
                                                        <div>Cluster: <?= (int) ($row['po_cluster_count'] ?? 0) ?></div>
                                                        <div>Subfeeder: <?= (int) ($row['po_subfeeder_count'] ?? 0) ?></div>
                                                        <div><span class="badge badge-<?= $statusBadgeClass ?>"><?= htmlspecialchars($summaryStatus) ?></span></div>
                                                    </td>
                                                    <td><?= poMyRepNumber((float) ($row['po_total_value'] ?? 0)) ?></td>
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
                                                    <td><?= !empty($row['last_po_date']) ? htmlspecialchars((string) $row['last_po_date']) : '-' ?></td>
                                                    <td>
                                                        <a href="<?= base_url('PO_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a>
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
                                                    <div>Cluster: <span id="po-footer-cluster-count">0</span></div>
                                                    <div>Subfeeder: <span id="po-footer-subfeeder-count">0</span></div>
                                                </th>
                                                <th class="text-right" id="po-footer-nilai-po">0</th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="po-list-pane" role="tabpanel" aria-labelledby="po-list-tab">
                                <div class="table-responsive">
                                    <table id="table_po_list_only" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tipe PO</th>
                                                <th>No PO</th>
                                                <th>Cluster</th>
                                                <th>Kota</th>
                                                <th>Regional</th>
                                                <th>Status PO</th>
                                                <th>Nilai PO</th>
                                                <th>Termin</th>
                                                <th>Tanggal PO</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($poListRows as $index => $row): ?>
                                                <?php
                                                $tipePo = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
                                                $statusPo = strtoupper(trim((string) ($row['status_po'] ?? 'NOT ISSUED')));
                                                $terminTotal = (int) ($row['termin_total_count'] ?? 0);
                                                $terminProgress = (int) ($row['termin_progress_count'] ?? 0);
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><span class="badge badge-<?= $tipePo === 'SUBFEEDER' ? 'warning' : 'primary' ?>"><?= htmlspecialchars($tipePo) ?></span></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars((string) ($row['po_number'] ?? '-')) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars((string) ($row['po_category'] ?? '-')) ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($statusPo) ?></span></td>
                                                    <td class="text-right"><?= poMyRepNumber((float) ($row['po_value'] ?? 0)) ?></td>
                                                    <td class="text-center"><?= $terminProgress ?>/<?= $terminTotal ?></td>
                                                    <td><?= !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date']) : '-' ?></td>
                                                    <td><a href="<?= base_url('PO_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($poListRows)): ?>
                                                <tr><td colspan="11" class="text-center text-muted">Belum ada data PO.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="7" class="text-right">TOTAL NILAI PO</th>
                                                <th class="text-right" id="po-list-footer-nilai-po">0</th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="modal-breakdown-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="breakdown-detail-title">Detail Pembagian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Cluster</th>
                                <th>Kota</th>
                                <th>Regional</th>
                                <th>PO</th>
                                <th>Tanggal PO</th>
                                <th>Termin</th>
                                <th>Status Termin</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody id="breakdown-detail-body">
                            <tr><td colspan="9" class="text-center text-muted">Belum ada data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var breakdownDetailUrl = "<?= base_url('PO_MyRep/getTerminBreakdownDetail') ?>";
        var selectedCity = "<?= htmlspecialchars((string) $selectedCity, ENT_QUOTES) ?>";
        var selectedStatus = "<?= htmlspecialchars((string) $selectedStatus, ENT_QUOTES) ?>";

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

        $(function () {
            if (!$.fn.DataTable) {
                return;
            }

            var tableMonitor = $('#table_po_myrep').length ? $('#table_po_myrep').DataTable({
                responsive: true,
                autoWidth: false,
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

                    $('#po-footer-cluster-count').text(totalPoCluster.toLocaleString('id-ID'));
                    $('#po-footer-subfeeder-count').text(totalPoSubfeeder.toLocaleString('id-ID'));
                    $('#po-footer-nilai-po').text(totalNilaiPo.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                }
            }) : null;

            var tablePoList = $('#table_po_list_only').length ? $('#table_po_list_only').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [[9, 'desc']],
                footerCallback: function () {
                    var api = this.api();
                    var totalNilaiPo = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                        return parseLocaleNumber(a) + parseLocaleNumber(b);
                    }, 0);
                    $('#po-list-footer-nilai-po').text(totalNilaiPo.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                }
            }) : null;

            $('a[data-toggle="pill"]').on('shown.bs.tab', function () {
                if (tableMonitor) {
                    tableMonitor.columns.adjust().responsive.recalc();
                }
                if (tablePoList) {
                    tablePoList.columns.adjust().responsive.recalc();
                }
            });

            $(document).on('click', '.js-open-breakdown', function () {
                var $btn = $(this);
                var payload = {
                    city: selectedCity,
                    status: selectedStatus,
                    po_type: String($btn.data('po-type') || 'CLUSTER'),
                    metric: String($btn.data('metric') || ''),
                    term_no: Number($btn.data('term-no') || 0)
                };

                $('#breakdown-detail-title').text('Memuat detail...');
                $('#breakdown-detail-body').html('<tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>');
                $('#modal-breakdown-detail').modal('show');

                $.ajax({
                    url: breakdownDetailUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function (response) {
                        if (!response || !response.status) {
                            $('#breakdown-detail-title').text('Detail Pembagian');
                            $('#breakdown-detail-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data.</td></tr>');
                            return;
                        }
                        $('#breakdown-detail-title').text(response.title || 'Detail Pembagian');
                        var rows = response.rows || [];
                        if (!rows.length) {
                            $('#breakdown-detail-body').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada detail untuk nilai ini.</td></tr>');
                            return;
                        }
                        var html = rows.map(function (row, idx) {
                            return '<tr>' +
                                '<td>' + (idx + 1) + '</td>' +
                                '<td>' + (row.cluster_name || '-') + '</td>' +
                                '<td>' + (row.city_name || '-') + '</td>' +
                                '<td>' + (row.regional_name || '-') + '</td>' +
                                '<td>' + (row.po_number || '-') + '</td>' +
                                '<td>' + (row.po_date || '-') + '</td>' +
                                '<td class="text-center">' + (row.termin_no || '-') + '</td>' +
                                '<td class="text-center">' + (row.status_termin || '-') + '</td>' +
                                '<td class="text-right">' + Number(row.amount || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + '</td>' +
                            '</tr>';
                        }).join('');
                        $('#breakdown-detail-body').html(html);
                    },
                    error: function () {
                        $('#breakdown-detail-title').text('Detail Pembagian');
                        $('#breakdown-detail-body').html('<tr><td colspan="9" class="text-center text-danger">Terjadi kesalahan saat memuat detail.</td></tr>');
                    }
                });
            });
        });
    })();
</script>
