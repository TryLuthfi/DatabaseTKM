<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canBatchInvoice = isset($canBatchInvoice) ? (bool) $canBatchInvoice : false;

if (!function_exists('poMyRepNumber')) {
    function poMyRepNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('poMyRepNumberOrDash')) {
    function poMyRepNumberOrDash($value)
    {
        return (float) $value == 0.0 ? '-' : poMyRepNumber($value);
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

    #table_po_list_only th,
    #table_po_list_only td {
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
    }

    #table_po_list_only thead th,
    #table_po_list_only.dataTable thead th,
    #table_po_list_only.dataTable thead td {
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: 700;
    }

    #table_po_list_only.dataTable thead th.sorting,
    #table_po_list_only.dataTable thead th.sorting_asc,
    #table_po_list_only.dataTable thead th.sorting_desc {
        text-align: center !important;
    }

    #table_po_list_only .text-right {
        text-align: right !important;
    }

    .po-list-inline-filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .po-list-inline-filters .form-group {
        margin-bottom: 0;
        min-width: 270px;
    }

    .po-list-inline-filters label {
        display: block;
        margin-bottom: 4px;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        color: #475569;
    }

    .po-list-inline-filters .form-control {
        height: 40px;
        border-radius: 12px;
        border: 1px solid #d5dee8;
        box-shadow: none;
        padding: 0 12px;
        background: #fff;
    }

    .po-list-inline-filters .form-control:focus {
        border-color: #9db8d6;
        box-shadow: 0 0 0 0.16rem rgba(29, 126, 214, 0.12);
    }

    .po-batch-invoice__toolbar {
        display: grid;
        grid-template-columns: minmax(180px, 1.4fr) 120px minmax(160px, 1fr) auto;
        gap: 10px;
        align-items: end;
    }

    .po-batch-invoice__paste {
        min-height: 116px;
        font-family: Consolas, monospace;
        font-size: .86rem;
    }

    #po-batch-invoice-table th,
    #po-batch-invoice-table td {
        vertical-align: middle;
    }

    @media (max-width: 768px) {
        .po-batch-invoice__toolbar {
            grid-template-columns: 1fr;
        }
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
                                        <?php foreach (['DP', 'ATP CW', 'FULL OPM', 'RFS', 'FAC'] as $statusOption): ?>
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

                <?php if ($canBatchInvoice): ?>
                    <div class="card card-outline card-dark shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">Batch Input Invoice Termin</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('PO_MyRep/batchInvoiceTermin') ?>" id="po-batch-invoice-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Invoice General</label>
                                            <input type="date" name="invoice_date" id="po-batch-invoice-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label>Paste dari Excel</label>
                                            <textarea id="po-batch-paste" class="form-control po-batch-invoice__paste" placeholder="PO Number[TAB]Term[TAB]Nilai Invoice&#10;PO-001[TAB]1[TAB]1000000&#10;PO-002[TAB]Term 2[TAB]2500000"></textarea>
                                            <small class="form-text text-muted">Term cukup isi angka 1-5. Variasi seperti Term 1, Termin 1, atau T1 juga terbaca.</small>
                                        </div>
                                    </div>
                                </div>

                                <datalist id="po-batch-po-options">
                                    <?php
                                    $poBatchSeen = [];
                                    foreach (($poListRows ?? []) as $poBatchRow):
                                        $poBatchNumber = trim((string) ($poBatchRow['po_number'] ?? ''));
                                        if ($poBatchNumber === '' || isset($poBatchSeen[strtoupper($poBatchNumber)])) {
                                            continue;
                                        }
                                        $poBatchSeen[strtoupper($poBatchNumber)] = true;
                                    ?>
                                        <option value="<?= htmlspecialchars($poBatchNumber, ENT_QUOTES) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>

                                <div class="po-batch-invoice__toolbar mb-3">
                                    <div>
                                        <label class="mb-1">Nomor PO</label>
                                        <input type="text" id="po-batch-po-number" class="form-control" list="po-batch-po-options" placeholder="Pilih / ketik nomor PO">
                                    </div>
                                    <div>
                                        <label class="mb-1">Term</label>
                                        <select id="po-batch-term-no" class="form-control">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1">Nilai Invoice</label>
                                        <input type="text" id="po-batch-invoice-value" class="form-control" placeholder="Contoh: 1.000.000">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" id="po-batch-add-row">Tambah Row</button>
                                        <button type="button" class="btn btn-outline-secondary" id="po-batch-parse-paste">Tambah dari Paste</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" id="po-batch-invoice-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:60px;">No</th>
                                                <th>Nomor PO</th>
                                                <th style="width:110px;">Term</th>
                                                <th style="width:220px;">Nilai Invoice</th>
                                                <th style="width:90px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="po-batch-empty-row">
                                                <td colspan="5" class="text-center text-muted">Belum ada row invoice.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small">Submit akan mengubah term menjadi BILLED dan memakai tanggal invoice general.</div>
                                    <button type="submit" class="btn btn-dark" id="po-batch-submit" disabled>Simpan Batch Invoice</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

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
                                        <th rowspan="2">Total PO Value</th>
                                        <th rowspan="2">Term Done</th>
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
                                    $sumPoQty = 0;
                                    $sumTotalPo = 0;
                                    $sumTermDone = 0;
                                    $sumTotalInvoiced = 0;
                                    $sumOutstanding = 0;
                                    $sumTermin = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                                    ?>
                                    <?php foreach ($terminBreakdownRows as $index => $row): ?>
                                        <?php
                                        $sumPoQty += (int) ($row['total_po_count'] ?? 0);
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
                                            <td class="text-center"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="po_qty"><?= (int) ($row['total_po_count'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_po"><?= poMyRepNumber((float) ($row['total_po_value'] ?? 0)) ?></span></td>
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
                                            <td colspan="12" class="text-center text-muted">Belum ada data pembagian termin.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($terminBreakdownRows)): ?>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">TOTAL</th>
                                            <th class="text-center"><?= (int) $sumPoQty ?></th>
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

                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Summary Sertifikat Claim Invoice</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tipe PO</th>
                                        <th>Term</th>
                                        <th>Total</th>
                                        <th>Released</th>
                                        <th>Ready Release</th>
                                        <th>Waiting ASTRI</th>
                                        <th>Waiting FAC/BJT</th>
                                        <th>Blocked Billing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $certificateTotals = [
                                        'total_count' => 0,
                                        'released_count' => 0,
                                        'ready_count' => 0,
                                        'waiting_astri_count' => 0,
                                        'waiting_fac_count' => 0,
                                        'blocked_billing_count' => 0,
                                    ];
                                    ?>
                                    <?php foreach (($certificateSummaryRows ?? []) as $index => $row): ?>
                                        <?php
                                        foreach ($certificateTotals as $key => $value) {
                                            $certificateTotals[$key] += (int) ($row[$key] ?? 0);
                                        }
                                        $certificatePoType = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
                                        $certificateTermNo = (int) ($row['termin_no'] ?? 0);
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><span class="badge badge-<?= $certificatePoType === 'SUBFEEDER' ? 'warning' : 'primary' ?>"><?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars((string) ($row['term_label'] ?? ('Term ' . $certificateTermNo)), ENT_QUOTES) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="ALL"><?= (int) ($row['total_count'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="RELEASED"><?= (int) ($row['released_count'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="READY"><?= (int) ($row['ready_count'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="WAITING_ASTRI"><?= (int) ($row['waiting_astri_count'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="WAITING_FAC"><?= (int) ($row['waiting_fac_count'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="po-breakdown-link js-open-certificate-detail" data-po-type="<?= htmlspecialchars($certificatePoType, ENT_QUOTES) ?>" data-term-no="<?= $certificateTermNo ?>" data-certificate-status="BLOCKED_BILLING"><?= (int) ($row['blocked_billing_count'] ?? 0) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($certificateSummaryRows)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Belum ada data sertifikat claim invoice.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($certificateSummaryRows)): ?>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">TOTAL</th>
                                            <th class="text-center"><?= (int) $certificateTotals['total_count'] ?></th>
                                            <th class="text-center"><?= (int) $certificateTotals['released_count'] ?></th>
                                            <th class="text-center"><?= (int) $certificateTotals['ready_count'] ?></th>
                                            <th class="text-center"><?= (int) $certificateTotals['waiting_astri_count'] ?></th>
                                            <th class="text-center"><?= (int) $certificateTotals['waiting_fac_count'] ?></th>
                                            <th class="text-center"><?= (int) $certificateTotals['blocked_billing_count'] ?></th>
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
                                                $summaryStatus = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
                                                if ($summaryStatus === 'DP') {
                                                    $statusBadgeClass = 'danger';
                                                } elseif ($summaryStatus === 'ATP CW') {
                                                    $statusBadgeClass = 'warning';
                                                } elseif ($summaryStatus === 'FULL OPM') {
                                                    $statusBadgeClass = 'info';
                                                } elseif ($summaryStatus === 'RFS') {
                                                    $statusBadgeClass = 'primary';
                                                } elseif ($summaryStatus === 'FAC') {
                                                    $statusBadgeClass = 'success';
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
                                <div class="po-list-inline-filters" id="po-list-inline-filters">
                                    <div class="form-group">
                                        <label for="po-list-filter-type">Filter Tipe PO</label>
                                        <select id="po-list-filter-type" class="form-control">
                                            <option value="">Semua Tipe</option>
                                            <option value="CLUSTER">CLUSTER</option>
                                            <option value="SUBFEEDER">SUBFEEDER</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="po-list-filter-status">Filter Status PO</label>
                                        <select id="po-list-filter-status" class="form-control">
                                            <option value="">Semua Status</option>
                                            <option value="DP">DP</option>
                                            <option value="ATP CW">ATP CW</option>
                                            <option value="FULL OPM">FULL OPM</option>
                                            <option value="RFS">RFS</option>
                                            <option value="FAC">FAC</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="table_po_list_only" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th rowspan="3">No</th>
                                                <th rowspan="3">Tipe PO</th>
                                                <th rowspan="3">No PO</th>
                                                <th rowspan="3">Tanggal PO</th>
                                                <th rowspan="3">Cluster</th>
                                                <th rowspan="3">Kota</th>
                                                <th rowspan="3">Regional</th>
                                                <th rowspan="3">Status PO</th>
                                                <th rowspan="3">Nilai PO</th>
                                                <th rowspan="3">Termin</th>
                                                <th colspan="10" class="text-center">PROGRESS INVOICE</th>
                                                <th rowspan="3">Total Invoiced</th>
                                                <th rowspan="3">Outstanding Total</th>
                                                <th rowspan="3">Aksi</th>
                                            </tr>
                                            <tr>
                                                <th colspan="2" class="text-center">TOP 1<br>20%(DP)</th>
                                                <th colspan="2" class="text-center">TOP 2<br>25%(CW)</th>
                                                <th colspan="2" class="text-center">TOP 3<br>15%(FULL OPM)</th>
                                                <th colspan="2" class="text-center">TOP 4<br>30%(RFS)</th>
                                                <th colspan="2" class="text-center">TOP 5<br>10%(FAC)</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">PLAN INV</th>
                                                <th class="text-center">NILAI</th>
                                                <th class="text-center">PLAN INV</th>
                                                <th class="text-center">NILAI</th>
                                                <th class="text-center">PLAN INV</th>
                                                <th class="text-center">NILAI</th>
                                                <th class="text-center">PLAN INV</th>
                                                <th class="text-center">NILAI</th>
                                                <th class="text-center">PLAN INV</th>
                                                <th class="text-center">NILAI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($poListRows as $index => $row): ?>
                                                <?php
                                                $tipePo = strtoupper(trim((string) ($row['po_type'] ?? 'CLUSTER')));
                                                $statusPo = strtoupper(trim((string) ($row['po_stage_status'] ?? 'NOT ISSUED')));
                                                $terminTotal = (int) ($row['termin_total_count'] ?? 0);
                                                $terminProgress = (int) ($row['termin_progress_count'] ?? 0);
                                                ?>
                                                <tr>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><span class="badge badge-<?= $tipePo === 'SUBFEEDER' ? 'warning' : 'primary' ?>"><?= htmlspecialchars($tipePo) ?></span></td>
                                                    <td>
                                                        <strong>
                                                            <a href="<?= base_url('PO_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>">
                                                                <?= htmlspecialchars((string) ($row['po_number'] ?? '-')) ?>
                                                            </a>
                                                        </strong>
                                                        <div class="small text-muted"><?= htmlspecialchars((string) ($row['po_category'] ?? '-')) ?></div>
                                                    </td>
                                                    <td><?= !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date']) : '-' ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $statusPo === 'DP' ? 'danger' : ($statusPo === 'ATP CW' ? 'warning' : ($statusPo === 'FULL OPM' ? 'info' : ($statusPo === 'RFS' ? 'primary' : ($statusPo === 'FAC' ? 'success' : 'secondary')))) ?>">
                                                            <?= htmlspecialchars($statusPo) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-right"><?= poMyRepNumber((float) ($row['po_value'] ?? 0)) ?></td>
                                                    <td class="text-center"><?= $terminProgress ?>/<?= $terminTotal ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['plan_invoice_per_termin'][1] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['done_invoice_per_termin'][1] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['plan_invoice_per_termin'][2] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['done_invoice_per_termin'][2] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['plan_invoice_per_termin'][3] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['done_invoice_per_termin'][3] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['plan_invoice_per_termin'][4] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['done_invoice_per_termin'][4] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['plan_invoice_per_termin'][5] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) (($row['done_invoice_per_termin'][5] ?? 0))) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) ($row['total_invoiced'] ?? 0)) ?></td>
                                                    <td class="text-right"><?= poMyRepNumberOrDash((float) ($row['outstanding_total'] ?? 0)) ?></td>
                                                    <td><a href="<?= base_url('PO_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($poListRows)): ?>
                                                <tr><td colspan="25" class="text-center text-muted">Belum ada data PO.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="8" class="text-right">TOTAL NILAI PO</th>
                                                <th class="text-right po-list-footer-nilai-po" id="po-list-footer-nilai-po">0</th>
                                                <th></th>
                                                <th class="text-right po-list-footer-plan-1" id="po-list-footer-plan-1">-</th>
                                                <th class="text-right po-list-footer-done-1" id="po-list-footer-done-1">-</th>
                                                <th class="text-right po-list-footer-plan-2" id="po-list-footer-plan-2">-</th>
                                                <th class="text-right po-list-footer-done-2" id="po-list-footer-done-2">-</th>
                                                <th class="text-right po-list-footer-plan-3" id="po-list-footer-plan-3">-</th>
                                                <th class="text-right po-list-footer-done-3" id="po-list-footer-done-3">-</th>
                                                <th class="text-right po-list-footer-plan-4" id="po-list-footer-plan-4">-</th>
                                                <th class="text-right po-list-footer-done-4" id="po-list-footer-done-4">-</th>
                                                <th class="text-right po-list-footer-plan-5" id="po-list-footer-plan-5">-</th>
                                                <th class="text-right po-list-footer-done-5" id="po-list-footer-done-5">-</th>
                                                <th class="text-right po-list-footer-total-invoiced" id="po-list-footer-total-invoiced">-</th>
                                                <th class="text-right po-list-footer-outstanding-total" id="po-list-footer-outstanding-total">-</th>
                                                <th></th>
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

<div class="modal fade" id="modal-certificate-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="certificate-detail-title">Detail Sertifikat</h5>
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
                                <th>PO</th>
                                <th>Term</th>
                                <th>Syarat</th>
                                <th>Status</th>
                                <th>Sertifikat</th>
                                <th>Invoice</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="certificate-detail-body">
                            <tr><td colspan="10" class="text-center text-muted">Belum ada data.</td></tr>
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
        var certificateDetailUrl = "<?= base_url('PO_MyRep/getCertificateSummaryDetail') ?>";
        var certificateSaveUrl = "<?= base_url('PO_MyRep/saveTerminCertificate') ?>";
        var poDetailBaseUrl = "<?= base_url('PO_MyRep/detail/') ?>";
        var selectedCity = "<?= htmlspecialchars((string) $selectedCity, ENT_QUOTES) ?>";
        var selectedStatus = "<?= htmlspecialchars((string) $selectedStatus, ENT_QUOTES) ?>";

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function parseLocaleNumber(value) {
            if (typeof value === 'number') {
                return isNaN(value) ? 0 : value;
            }
            var cleaned = $('<div>').html(value || '').text();
            cleaned = String(cleaned).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.-]/g, '');
            var parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        }

        function normalizeTermInput(value) {
            var match = String(value || '').toUpperCase().match(/[1-5]/);
            return match ? match[0] : '';
        }

        function formatBatchValue(value) {
            var parsed = parseLocaleNumber(value);
            return parsed > 0 ? parsed.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '';
        }

        function updateBatchInvoiceState() {
            var $rows = $('#po-batch-invoice-table tbody tr.po-batch-row');
            $('#po-batch-invoice-table tbody .po-batch-empty-row').toggle($rows.length === 0);
            $('#po-batch-submit').prop('disabled', $rows.length === 0);
            $rows.each(function (idx) {
                $(this).find('.po-batch-row-no').text(idx + 1);
            });
        }

        function addBatchInvoiceRow(poNumber, termNo, invoiceValue) {
            poNumber = String(poNumber || '').trim();
            termNo = normalizeTermInput(termNo);
            invoiceValue = formatBatchValue(invoiceValue);

            if (!poNumber || !termNo || !invoiceValue) {
                return false;
            }

            var key = poNumber.toUpperCase() + '|' + termNo;
            var exists = false;
            $('#po-batch-invoice-table tbody tr.po-batch-row').each(function () {
                if ($(this).data('key') === key) {
                    exists = true;
                    return false;
                }
            });
            if (exists) {
                return false;
            }

            var html = '<tr class="po-batch-row" data-key="' + escapeHtml(key) + '">' +
                '<td class="text-center po-batch-row-no"></td>' +
                '<td>' + escapeHtml(poNumber) + '<input type="hidden" name="po_number[]" value="' + escapeHtml(poNumber) + '"></td>' +
                '<td class="text-center">' + escapeHtml(termNo) + '<input type="hidden" name="term_no[]" value="' + escapeHtml(termNo) + '"></td>' +
                '<td class="text-right">' + escapeHtml(invoiceValue) + '<input type="hidden" name="invoice_value[]" value="' + escapeHtml(invoiceValue) + '"></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger po-batch-remove-row">Hapus</button></td>' +
            '</tr>';

            $('#po-batch-invoice-table tbody').append(html);
            updateBatchInvoiceState();
            return true;
        }

        $('#po-batch-add-row').on('click', function () {
            var added = addBatchInvoiceRow(
                $('#po-batch-po-number').val(),
                $('#po-batch-term-no').val(),
                $('#po-batch-invoice-value').val()
            );
            if (added) {
                $('#po-batch-po-number').val('').focus();
                $('#po-batch-invoice-value').val('');
            }
        });

        $('#po-batch-parse-paste').on('click', function () {
            var lines = String($('#po-batch-paste').val() || '').split(/\r?\n/);
            var addedCount = 0;
            lines.forEach(function (line) {
                line = String(line || '').trim();
                if (!line) {
                    return;
                }

                var columns = line.split('\t');
                if (columns.length < 3) {
                    columns = line.split(';');
                }
                if (columns.length < 3) {
                    columns = line.split(',');
                }
                if (columns.length < 3) {
                    columns = line.split(/\s{2,}/);
                }
                if (columns.length < 3) {
                    return;
                }

                if (addBatchInvoiceRow(columns[0], columns[1], columns.slice(2).join(' '))) {
                    addedCount++;
                }
            });
            if (addedCount > 0) {
                $('#po-batch-paste').val('');
            }
        });

        $(document).on('click', '.po-batch-remove-row', function () {
            $(this).closest('tr').remove();
            updateBatchInvoiceState();
        });

        $('#po-batch-invoice-form').on('submit', function (e) {
            if ($('#po-batch-invoice-table tbody tr.po-batch-row').length === 0) {
                e.preventDefault();
                alert('Belum ada row invoice.');
            }
        });

        function extractPoCount(value, label) {
            var text = $('<div>').html(value || '').text();
            var regex = new RegExp(label + '\\s*:\\s*([0-9]+)', 'i');
            var match = text.match(regex);
            return match ? parseInt(match[1], 10) : 0;
        }

        function certificateBadge(status) {
            status = String(status || '').toUpperCase();
            if (status === 'RELEASED') {
                return 'success';
            }
            if (status === 'READY') {
                return 'primary';
            }
            if (status === 'WAITING_FAC') {
                return 'warning';
            }
            return 'secondary';
        }

        function renderCertificateAction(row, canReleaseCertificate) {
            var detailUrl = poDetailBaseUrl + encodeURIComponent(row.id_myrep_cluster || 0);
            var detailButton = '<a href="' + detailUrl + '" class="btn btn-sm btn-outline-primary mb-1">Detail PO</a>';
            if (!canReleaseCertificate || !row.can_update_certificate) {
                return detailButton + '<div class="small text-muted">Read only</div>';
            }

            return '<form method="post" action="' + certificateSaveUrl + '" class="mb-0">' +
                '<input type="hidden" name="cluster_id" value="' + escapeHtml(row.id_myrep_cluster || '') + '">' +
                '<input type="hidden" name="id_po_termin" value="' + escapeHtml(row.id_po_termin || '') + '">' +
                '<input type="hidden" name="redirect_scope" value="dashboard">' +
                '<input type="hidden" name="selected_city" value="' + escapeHtml(selectedCity) + '">' +
                '<input type="hidden" name="selected_status" value="' + escapeHtml(selectedStatus) + '">' +
                '<div class="input-group input-group-sm mb-1">' +
                    '<input type="text" name="sertifikat_invoice" class="form-control" value="' + escapeHtml(row.sertifikat_invoice_date || '') + '" placeholder="No/tanggal sertifikat">' +
                    '<div class="input-group-append"><button type="submit" class="btn btn-success">Simpan</button></div>' +
                '</div>' +
                detailButton +
            '</form>';
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
                responsive: false,
                scrollX: true,
                autoWidth: false,
                pageLength: 10,
                order: [[3, 'desc']],
                footerCallback: function () {
                    var api = this.api();
                    var totalNilaiPo = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                        return parseLocaleNumber(a) + parseLocaleNumber(b);
                    }, 0);
                    var totalPlan1 = api.column(10, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone1 = api.column(11, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalPlan2 = api.column(12, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone2 = api.column(13, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalPlan3 = api.column(14, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone3 = api.column(15, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalPlan4 = api.column(16, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone4 = api.column(17, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalPlan5 = api.column(18, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone5 = api.column(19, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalInvoiced = api.column(20, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding = api.column(21, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    $('.po-list-footer-nilai-po').text(totalNilaiPo.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-plan-1').text(totalPlan1 === 0 ? '-' : totalPlan1.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-1').text(totalDone1 === 0 ? '-' : totalDone1.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-plan-2').text(totalPlan2 === 0 ? '-' : totalPlan2.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-2').text(totalDone2 === 0 ? '-' : totalDone2.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-plan-3').text(totalPlan3 === 0 ? '-' : totalPlan3.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-3').text(totalDone3 === 0 ? '-' : totalDone3.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-plan-4').text(totalPlan4 === 0 ? '-' : totalPlan4.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-4').text(totalDone4 === 0 ? '-' : totalDone4.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-plan-5').text(totalPlan5 === 0 ? '-' : totalPlan5.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-5').text(totalDone5 === 0 ? '-' : totalDone5.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-total-invoiced').text(totalInvoiced === 0 ? '-' : totalInvoiced.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-total').text(totalOutstanding === 0 ? '-' : totalOutstanding.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                }
            }) : null;

            if (tablePoList) {
                var $filters = $('#po-list-inline-filters');
                var $wrapper = $('#table_po_list_only_wrapper');
                var $search = $wrapper.find('.dataTables_filter');
                if ($filters.length && $search.length) {
                    $filters.insertBefore($search);
                }

                $('#po-list-filter-type').on('change', function () {
                    var val = String($(this).val() || '');
                    tablePoList.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
                });

                $('#po-list-filter-status').on('change', function () {
                    var val = String($(this).val() || '');
                    tablePoList.column(7).search(val ? '^' + val + '$' : '', true, false).draw();
                });
            }

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

            $(document).on('click', '.js-open-certificate-detail', function () {
                var $btn = $(this);
                var payload = {
                    city: selectedCity,
                    status: selectedStatus,
                    po_type: String($btn.data('po-type') || 'CLUSTER'),
                    term_no: Number($btn.data('term-no') || 0),
                    certificate_status: String($btn.data('certificate-status') || 'ALL')
                };

                $('#certificate-detail-title').text('Memuat detail sertifikat...');
                $('#certificate-detail-body').html('<tr><td colspan="10" class="text-center text-muted">Loading...</td></tr>');
                $('#modal-certificate-detail').modal('show');

                $.ajax({
                    url: certificateDetailUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function (response) {
                        if (!response || !response.status) {
                            $('#certificate-detail-title').text('Detail Sertifikat');
                            $('#certificate-detail-body').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data.</td></tr>');
                            return;
                        }
                        $('#certificate-detail-title').text(response.title || 'Detail Sertifikat');
                        var rows = response.rows || [];
                        if (!rows.length) {
                            $('#certificate-detail-body').html('<tr><td colspan="10" class="text-center text-muted">Tidak ada detail untuk status ini.</td></tr>');
                            return;
                        }
                        var canReleaseCertificate = !!response.can_release_certificate;
                        var html = rows.map(function (row, idx) {
                            var requirement = row.termin_no === 5
                                ? escapeHtml(row.release_note || '-')
                                : 'Submit ' + Number(row.astri_submitted_docs || 0) + '/' + Number(row.required_docs || 0) +
                                    '<br>Approved ' + Number(row.astri_approved_docs || 0) + '/' + Number(row.required_docs || 0) +
                                    '<div class="small text-muted">' + escapeHtml(row.release_note || '') + '</div>';
                            return '<tr>' +
                                '<td>' + (idx + 1) + '</td>' +
                                '<td><strong>' + escapeHtml(row.cluster_name || '-') + '</strong><div class="small text-muted">' + escapeHtml(row.regional_name || '-') + '</div></td>' +
                                '<td>' + escapeHtml(row.city_name || '-') + '</td>' +
                                '<td><strong>' + escapeHtml(row.po_number || '-') + '</strong><div class="small text-muted">' + escapeHtml(row.po_type || '-') + ' / ' + escapeHtml(row.po_category || '-') + '</div></td>' +
                                '<td><strong>' + escapeHtml(row.term_label || '-') + '</strong><div class="small text-muted">' + Number(row.termin_value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + '</div></td>' +
                                '<td>' + requirement + '</td>' +
                                '<td><span class="badge badge-' + certificateBadge(row.certificate_status) + '">' + escapeHtml(row.certificate_status_label || '-') + '</span>' + (row.is_blocked_billing ? '<div class="small text-danger">Blocked billing</div>' : '') + '</td>' +
                                '<td>' + escapeHtml(row.sertifikat_invoice_date || '-') + '</td>' +
                                '<td><span class="badge badge-secondary">' + escapeHtml(row.status_termin || '-') + '</span><div class="small text-muted">' + escapeHtml(row.invoice_date || '-') + '</div></td>' +
                                '<td>' + renderCertificateAction(row, canReleaseCertificate) + '</td>' +
                            '</tr>';
                        }).join('');
                        $('#certificate-detail-body').html(html);
                    },
                    error: function () {
                        $('#certificate-detail-title').text('Detail Sertifikat');
                        $('#certificate-detail-body').html('<tr><td colspan="10" class="text-center text-danger">Terjadi kesalahan saat memuat detail.</td></tr>');
                    }
                });
            });
        });
    })();
</script>
