<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('poMonitorDetailHtml')) {
    function poMonitorDetailHtml($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('poMonitorDetailNum')) {
    function poMonitorDetailNum($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
if (!function_exists('poMonitorDetailDate')) {
    function poMonitorDetailDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        if (preg_match('/^200(\d)-07-26$/', $value, $match)) {
            return sprintf('%02d/07/2026', (int) $match[1]);
        }

        return date('d/m/Y', strtotime($value));
    }
}
if (!function_exists('poMonitorTargetText')) {
    function poMonitorTargetText($row)
    {
        $status = strtoupper(trim((string) ($row['target_status'] ?? '')));
        if (!empty($row['invoice_date'])) {
            return 'Invoiced<div class="small text-muted">' . poMonitorDetailDate($row['invoice_date'] ?? '') . '</div>';
        }
        if ($status === 'TARGET_WEEK') {
            return 'W' . (int) ($row['target_week'] ?? 0) . ' / ' . (int) ($row['target_year'] ?? 0)
                . '<div class="small text-muted">' . poMonitorDetailHtml(($row['target_week_start'] ?? '') . ' s/d ' . ($row['target_week_end'] ?? '')) . '</div>';
        }
        if ($status === 'CARRY_OVER') {
            return 'Carry Over ' . poMonitorDetailHtml(($row['target_year'] ?? '') ?: '2027');
        }
        if ($status === 'INVOICED') {
            return 'Invoiced<div class="small text-muted">-</div>';
        }
        if (!empty($row['due_date'])) {
            return poMonitorDetailDate($row['due_date']);
        }

        return '-';
    }
}
if (!function_exists('poMonitorSlaMeta')) {
    function poMonitorSlaMeta($remaining, $dueDate)
    {
        $remaining = (float) $remaining;
        $dueDate = trim((string) $dueDate);
        if ($remaining <= 0) {
            return ['label' => 'DONE', 'class' => 'success'];
        }
        if ($dueDate !== '' && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
            return ['label' => 'OVERDUE', 'class' => 'danger'];
        }
        if ($dueDate !== '' && strtotime($dueDate) <= strtotime('+7 days')) {
            return ['label' => 'WARNING', 'class' => 'warning'];
        }

        return ['label' => 'AMAN', 'class' => 'secondary'];
    }
}

$terms = is_array($terms ?? null) ? $terms : [];
$allocationMap = is_array($allocationMap ?? null) ? $allocationMap : [];
$bowheerOptions = is_array($bowheerOptions ?? null) ? $bowheerOptions : [];
$poValue = (float) ($po['total_value'] ?? 0);
$poBowheer = trim((string) (($po['nama_bowheer'] ?? '') ?: ($po['dashboard_bowheer'] ?? '')));
$poPic = trim((string) ($po['pic_bowheer'] ?? ''));
$poDateInput = !empty($po['po_date']) && $po['po_date'] !== '0000-00-00' ? date('Y-m-d', strtotime($po['po_date'])) : '';
$totalTermValue = 0;
$totalInvoiced = 0;
$totalRemaining = 0;
$doneTerms = 0;
foreach ($terms as $termRow) {
    $termValue = (float) ($termRow['value'] ?? 0);
    $invoiced = (float) ($termRow['invoiced_amount'] ?? 0);
    $hasInvoice = abs($invoiced) > 0.000001 || !empty($termRow['invoice_date']);
    $remaining = max($termValue - $invoiced, 0);
    $totalTermValue += $termValue;
    $totalInvoiced += $invoiced;
    $totalRemaining += $remaining;
    if ($hasInvoice && ($remaining <= 0 || abs($termValue) <= 0.000001)) {
        $doneTerms++;
    }
}
$progressPercent = $totalTermValue > 0 ? min(100, round(($totalInvoiced / $totalTermValue) * 100)) : 0;
$flashStatus = $this->session->flashdata('status');
$flashMessage = $this->session->flashdata('error_log');
?>

<style>
    .po-monitor-detail {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }
    .po-monitor-shell {
        padding: 1rem;
    }
    .po-monitor-hero {
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 14px;
        background: linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        color: #fff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.16);
        padding: 1.1rem;
    }
    .po-monitor-hero__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .po-monitor-hero__eyebrow,
    .po-monitor-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .po-monitor-hero__eyebrow {
        margin-bottom: 0.45rem;
        padding: 0.3rem 0.65rem;
        background: rgba(255, 255, 255, 0.13);
    }
    .po-monitor-hero h1 {
        margin: 0;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 900;
        letter-spacing: 0;
    }
    .po-monitor-hero__meta {
        margin-top: 0.45rem;
        color: rgba(226, 232, 240, 0.86);
        font-size: 0.86rem;
    }
    .po-monitor-hero__project {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-items: center;
        margin-top: 0.62rem;
    }
    .po-monitor-hero__project span {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.34rem 0.62rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 850;
    }
    .po-monitor-hero__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .po-monitor-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .po-monitor-stat {
        min-height: 86px;
        border: 1px solid rgba(255, 255, 255, 0.13);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.11);
        padding: 0.85rem;
    }
    .po-monitor-stat span {
        display: block;
    }
    .po-monitor-stat__label {
        color: rgba(226, 232, 240, 0.75);
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .po-monitor-stat__value {
        margin-top: 0.28rem;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1.1;
    }
    .po-monitor-panel {
        margin-top: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .po-monitor-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem 0;
    }
    .po-monitor-panel__title {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }
    .po-monitor-panel__body {
        padding: 1rem 1.1rem 1.1rem;
    }
    .po-monitor-progress {
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .po-monitor-progress span {
        display: block;
        height: 100%;
        background: #16a34a;
    }
    .po-monitor-term-card {
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-left: 6px solid #f59e0b;
        border-radius: 10px;
        margin-bottom: 0.8rem;
        overflow: hidden;
    }
    .po-monitor-term-card.is-invoiced {
        border-left-color: #16a34a;
    }
    .po-monitor-term-card.is-invoiced .po-monitor-term-card__head {
        background: #f0fdf4;
    }
    .po-monitor-term-card.is-pending .po-monitor-term-card__head {
        background: #fffbeb;
    }
    .po-monitor-term-card__head {
        display: grid;
        grid-template-columns: 90px 1fr auto;
        gap: 0.85rem;
        align-items: center;
        padding: 0.85rem;
        background: #f8fafc;
    }
    .po-monitor-term-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 10px;
        background: #0f172a;
        color: #fff;
        font-size: 1.15rem;
        font-weight: 900;
    }
    .po-monitor-term-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
    }
    .po-monitor-metric {
        min-width: 0;
    }
    .po-monitor-metric__label {
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 850;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .po-monitor-metric__value {
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 900;
    }
    .po-monitor-term-card__body {
        padding: 0.85rem;
        border-top: 1px solid rgba(148, 163, 184, 0.18);
    }
    .po-monitor-claim-form {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        justify-content: flex-end;
    }
    .po-monitor-allocation-table th {
        background: #111827;
        color: #fff;
        border-color: #111827;
        font-size: 0.74rem;
        white-space: nowrap;
    }
    .po-monitor-allocation-table td {
        vertical-align: middle;
        font-size: 0.82rem;
    }
    .po-monitor-allocation-row.is-invoiced td {
        background: #f0fdf4;
    }
    .po-monitor-allocation-row.is-pending td {
        background: #fffbeb;
    }
    @media (max-width: 992px) {
        .po-monitor-stat-grid,
        .po-monitor-term-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .po-monitor-term-card__head {
            grid-template-columns: 1fr;
        }
        .po-monitor-hero__top {
            flex-direction: column;
        }
        .po-monitor-hero__actions,
        .po-monitor-claim-form {
            justify-content: flex-start;
        }
    }
    @media (max-width: 576px) {
        .po-monitor-stat-grid,
        .po-monitor-term-metrics {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper po-monitor-detail">
    <section class="content-header">
        <div class="container-fluid po-monitor-shell">
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= $flashStatus ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?= poMonitorDetailHtml($flashMessage) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <section class="po-monitor-hero">
                <div class="po-monitor-hero__top">
                    <div>
                        <span class="po-monitor-hero__eyebrow"><i class="fas fa-file-invoice-dollar"></i> PO Monitor Detail</span>
                        <h1><?= poMonitorDetailHtml($po['po_number'] ?? '-') ?></h1>
                        <div class="po-monitor-hero__meta">
                            <?= poMonitorDetailHtml($po['type_project'] ?? '-') ?> &middot; <?= poMonitorDetailDate($po['po_date'] ?? '') ?>
                        </div>
                        <div class="po-monitor-hero__project">
                            <span><i class="fas fa-building"></i> <?= poMonitorDetailHtml($poBowheer !== '' ? $poBowheer : '-') ?></span>
                            <?php if ($poPic !== ''): ?>
                                <span><i class="fas fa-user"></i> <?= poMonitorDetailHtml($poPic) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="po-monitor-hero__actions">
                        <button type="button" class="btn btn-outline-light btn-sm" data-toggle="modal" data-target="#po_monitor_edit_header_modal">
                            <i class="fas fa-edit mr-1"></i> Edit Header
                        </button>
                        <a href="<?= site_url('PO_Monitor') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                    </div>
                </div>

                <div class="po-monitor-stat-grid">
                    <div class="po-monitor-stat">
                        <span class="po-monitor-stat__label">Total PO</span>
                        <span class="po-monitor-stat__value"><?= poMonitorDetailNum($poValue) ?></span>
                    </div>
                    <div class="po-monitor-stat">
                        <span class="po-monitor-stat__label">Term Value</span>
                        <span class="po-monitor-stat__value"><?= poMonitorDetailNum($totalTermValue) ?></span>
                    </div>
                    <div class="po-monitor-stat">
                        <span class="po-monitor-stat__label">Invoiced</span>
                        <span class="po-monitor-stat__value"><?= poMonitorDetailNum($totalInvoiced) ?></span>
                    </div>
                    <div class="po-monitor-stat">
                        <span class="po-monitor-stat__label">Outstanding</span>
                        <span class="po-monitor-stat__value"><?= poMonitorDetailNum($totalRemaining) ?></span>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid po-monitor-shell pt-0">
            <section class="po-monitor-panel">
                <div class="po-monitor-panel__head">
                    <div>
                        <h2 class="po-monitor-panel__title">Progress Termin</h2>
                        <div class="small text-muted"><?= $doneTerms ?>/<?= count($terms) ?> term selesai &middot; <?= (int) $progressPercent ?>% invoiced</div>
                    </div>
                    <span class="badge badge-info"><?= count($terms) ?> Terms</span>
                </div>
                <div class="po-monitor-panel__body">
                    <div class="po-monitor-progress mb-3"><span style="width: <?= (int) $progressPercent ?>%;"></span></div>

                    <?php if (empty($terms)): ?>
                        <div class="text-center text-muted py-4">Belum ada term untuk PO ini.</div>
                    <?php endif; ?>

                    <?php foreach ($terms as $term): ?>
                        <?php
                        $idTerm = (int) ($term['id_term'] ?? 0);
                        $value = (float) ($term['value'] ?? 0);
                        $invoiced = (float) ($term['invoiced_amount'] ?? 0);
                        $remaining = max($value - $invoiced, 0);
                        $hasInvoice = abs($invoiced) > 0.000001 || !empty($term['invoice_date']);
                        $dueDate = !empty($term['target_week_end']) ? $term['target_week_end'] : ($term['due_date'] ?? '');
                        $sla = poMonitorSlaMeta($remaining, $dueDate);
                        $allocations = $allocationMap[$idTerm] ?? [];
                        $collapseId = 'po-monitor-term-' . $idTerm;
                        $termStatusClass = $hasInvoice ? 'is-invoiced' : 'is-pending';
                        $termInvoiceDate = !empty($term['invoice_date']) ? date('Y-m-d', strtotime($term['invoice_date'])) : date('Y-m-d');
                        ?>
                        <article class="po-monitor-term-card <?= $termStatusClass ?>">
                            <div class="po-monitor-term-card__head">
                                <div><span class="po-monitor-term-no">T<?= (int) ($term['term_index'] ?? 0) ?></span></div>
                                <div>
                                    <div class="po-monitor-term-metrics">
                                        <div class="po-monitor-metric"><div class="po-monitor-metric__label">Percent</div><div class="po-monitor-metric__value"><?= number_format((float) ($term['percent'] ?? 0), 2, ',', '.') ?>%</div></div>
                                        <div class="po-monitor-metric"><div class="po-monitor-metric__label">Value</div><div class="po-monitor-metric__value"><?= poMonitorDetailNum($value) ?></div></div>
                                        <div class="po-monitor-metric"><div class="po-monitor-metric__label">Invoiced</div><div class="po-monitor-metric__value text-success"><?= poMonitorDetailNum($invoiced) ?></div></div>
                                        <div class="po-monitor-metric"><div class="po-monitor-metric__label">Remaining</div><div class="po-monitor-metric__value text-<?= $remaining > 0 ? 'danger' : 'success' ?>"><?= poMonitorDetailNum($remaining) ?></div></div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge badge-<?= $sla['class'] ?>"><?= poMonitorDetailHtml($sla['label']) ?></span>
                                        <span class="small text-muted ml-2"><?= poMonitorTargetText($term) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <?php if (!empty($allocations)): ?>
                                        <?php if ($hasInvoice): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary js-po-monitor-edit-invoice mb-1"
                                                data-toggle="modal"
                                                data-target="#po_monitor_edit_invoice_modal"
                                                data-id-po="<?= (int) ($po['id_po'] ?? 0) ?>"
                                                data-id-term="<?= $idTerm ?>"
                                                data-id-allocation="0"
                                                data-title="Term <?= (int) ($term['term_index'] ?? 0) ?>"
                                                data-invoice-date="<?= poMonitorDetailHtml($termInvoiceDate) ?>"
                                                data-invoice-amount="<?= poMonitorDetailHtml($invoiced) ?>">
                                                Edit Invoice
                                            </button>
                                        <?php endif; ?>
                                        <div><span class="badge badge-info">Claim per Sub PO</span></div>
                                    <?php else: ?>
                                        <?php if ($hasInvoice): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary js-po-monitor-edit-invoice mb-1"
                                                data-toggle="modal"
                                                data-target="#po_monitor_edit_invoice_modal"
                                                data-id-po="<?= (int) ($po['id_po'] ?? 0) ?>"
                                                data-id-term="<?= $idTerm ?>"
                                                data-id-allocation="0"
                                                data-title="Term <?= (int) ($term['term_index'] ?? 0) ?>"
                                                data-invoice-date="<?= poMonitorDetailHtml($termInvoiceDate) ?>"
                                                data-invoice-amount="<?= poMonitorDetailHtml($invoiced) ?>">
                                                Edit Invoice
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!$hasInvoice && $remaining > 0): ?>
                                        <form method="post" action="<?= site_url('PO_Monitor/claim_term') ?>" class="po-monitor-claim-form">
                                            <input type="hidden" name="id_po" value="<?= (int) ($po['id_po'] ?? 0) ?>">
                                            <input type="hidden" name="id_term" value="<?= $idTerm ?>">
                                            <input type="date" name="invoice_date" class="form-control form-control-sm" required>
                                            <input type="text" name="invoice_amount" class="form-control form-control-sm" value="<?= poMonitorDetailNum($remaining) ?>" style="width: 120px;" required>
                                            <button type="submit" class="btn btn-sm btn-success">Claim</button>
                                        </form>
                                        <?php elseif (!$hasInvoice): ?>
                                        <span class="badge badge-success">Done</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="po-monitor-term-card__body">
                                <button class="btn btn-sm btn-outline-secondary mb-2" type="button" data-toggle="collapse" data-target="#<?= poMonitorDetailHtml($collapseId) ?>">
                                    Detail Allocation <span class="badge badge-light border"><?= count($allocations) ?></span>
                                </button>
                                <div class="collapse show" id="<?= poMonitorDetailHtml($collapseId) ?>">
                                    <?php if (empty($allocations)): ?>
                                        <div class="text-muted">Tidak ada allocation detail untuk term ini.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 po-monitor-allocation-table">
                                                <thead>
                                                    <tr>
                                                        <th>No PO Sub</th>
                                                        <th>Regional</th>
                                                        <th>Kota</th>
                                                        <th>Detail</th>
                                                        <th>Remarks</th>
                                                        <th>Value</th>
                                                        <th>Invoiced</th>
                                                        <th>Outstanding</th>
                                                        <th>Target</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($allocations as $allocation): ?>
                                                        <?php
                                                        $idAllocation = (int) ($allocation['id_allocation'] ?? 0);
                                                        $allocationValue = (float) (($allocation['plan_amount'] ?? 0) ?: ($allocation['allocation_value'] ?? 0));
                                                        $allocationInvoiced = (float) ($allocation['invoiced_amount'] ?? 0);
                                                        $allocationHasInvoice = abs($allocationInvoiced) > 0.000001 || !empty($allocation['invoice_date']);
                                                        $allocationOutstanding = max((float) ($allocation['outstanding_amount'] ?? ($allocationValue - $allocationInvoiced)), 0);
                                                        $allocationStatusClass = $allocationHasInvoice ? 'is-invoiced' : 'is-pending';
                                                        $allocationInvoiceDate = !empty($allocation['invoice_date']) ? date('Y-m-d', strtotime($allocation['invoice_date'])) : date('Y-m-d');
                                                        ?>
                                                        <tr class="po-monitor-allocation-row <?= $allocationStatusClass ?>">
                                                            <td><?= poMonitorDetailHtml(($allocation['no_po_sub'] ?? '') ?: '-') ?></td>
                                                            <td><?= poMonitorDetailHtml(($allocation['regional'] ?? '') ?: '-') ?></td>
                                                            <td><?= poMonitorDetailHtml(($allocation['kota_po'] ?? '') ?: '-') ?></td>
                                                            <td><?= poMonitorDetailHtml(($allocation['detail_po'] ?? '') ?: '-') ?></td>
                                                            <td><?= poMonitorDetailHtml(($allocation['remarks'] ?? '') ?: '-') ?></td>
                                                            <td class="text-right"><?= poMonitorDetailNum($allocationValue) ?></td>
                                                            <td class="text-right"><?= poMonitorDetailNum($allocationInvoiced) ?></td>
                                                            <td class="text-right"><?= poMonitorDetailNum($allocationOutstanding) ?></td>
                                                            <td><?= poMonitorTargetText($allocation) ?></td>
                                                            <td>
                                                                <?php if ($allocationHasInvoice): ?>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-primary js-po-monitor-edit-invoice mb-1"
                                                                        data-toggle="modal"
                                                                        data-target="#po_monitor_edit_invoice_modal"
                                                                        data-id-po="<?= (int) ($po['id_po'] ?? 0) ?>"
                                                                        data-id-term="<?= $idTerm ?>"
                                                                        data-id-allocation="<?= $idAllocation ?>"
                                                                        data-title="<?= poMonitorDetailHtml(($allocation['no_po_sub'] ?? '') ?: ('Term ' . (int) ($term['term_index'] ?? 0))) ?>"
                                                                        data-invoice-date="<?= poMonitorDetailHtml($allocationInvoiceDate) ?>"
                                                                        data-invoice-amount="<?= poMonitorDetailHtml($allocationInvoiced) ?>">
                                                                        Edit
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if (!$allocationHasInvoice && $allocationOutstanding > 0): ?>
                                                                    <form method="post" action="<?= site_url('PO_Monitor/claim_term') ?>" class="po-monitor-claim-form">
                                                                        <input type="hidden" name="id_po" value="<?= (int) ($po['id_po'] ?? 0) ?>">
                                                                        <input type="hidden" name="id_term" value="<?= $idTerm ?>">
                                                                        <input type="hidden" name="id_allocation" value="<?= $idAllocation ?>">
                                                                        <input type="date" name="invoice_date" class="form-control form-control-sm" required>
                                                                        <input type="text" name="invoice_amount" class="form-control form-control-sm" value="<?= poMonitorDetailNum($allocationOutstanding) ?>" style="width: 120px;" required>
                                                                        <button type="submit" class="btn btn-sm btn-success">Claim</button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <span class="badge badge-success">Done</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </section>
</div>

<div class="modal fade" id="po_monitor_edit_header_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('PO_Monitor/update_header/' . (int) ($po['id_po'] ?? 0)) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Header PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="po-monitor-header-po-number">No PO</label>
                                <input type="text" name="po_number" id="po-monitor-header-po-number" class="form-control" value="<?= poMonitorDetailHtml($po['po_number'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="po-monitor-header-po-date">Tanggal PO</label>
                                <input type="date" name="po_date" id="po-monitor-header-po-date" class="form-control" value="<?= poMonitorDetailHtml($poDateInput) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="po-monitor-header-bowheer">Project / Bowheer</label>
                                <select name="id_bowheer" id="po-monitor-header-bowheer" class="form-control">
                                    <option value="">Tanpa master bowheer</option>
                                    <?php foreach ($bowheerOptions as $bowheer): ?>
                                        <?php
                                        $bowheerId = (int) ($bowheer['id_bowheer'] ?? 0);
                                        $selected = $bowheerId === (int) ($po['id_bowheer'] ?? 0) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $bowheerId ?>"
                                            data-bowheer="<?= poMonitorDetailHtml($bowheer['bowheer'] ?? '') ?>"
                                            data-pic="<?= poMonitorDetailHtml($bowheer['pic'] ?? '') ?>"
                                            <?= $selected ?>>
                                            <?= poMonitorDetailHtml($bowheer['bowheer'] ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="dashboard_bowheer" id="po-monitor-header-dashboard-bowheer" value="<?= poMonitorDetailHtml($poBowheer) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="po-monitor-header-pic">PIC</label>
                                <input type="text" name="pic_bowheer" id="po-monitor-header-pic" class="form-control" value="<?= poMonitorDetailHtml($poPic) ?>">
                                <small class="form-text text-muted">PIC mengikuti master bowheer, jadi perubahan ini ikut tampil di PO lain dengan bowheer yang sama.</small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label for="po-monitor-header-type-project">Type Project</label>
                                <input type="text" name="type_project" id="po-monitor-header-type-project" class="form-control" value="<?= poMonitorDetailHtml($po['type_project'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="po-monitor-header-status">Status PO</label>
                                <select name="status_po" id="po-monitor-header-status" class="form-control">
                                    <?php foreach (['ON PO', 'NY PO', 'CLOSED', 'CANCELLED'] as $statusOption): ?>
                                        <option value="<?= poMonitorDetailHtml($statusOption) ?>" <?= strtoupper((string) ($po['status_po'] ?? 'ON PO')) === $statusOption ? 'selected' : '' ?>>
                                            <?= poMonitorDetailHtml($statusOption) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Header</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="po_monitor_edit_invoice_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('PO_Monitor/update_invoice_claim') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Invoice <span id="po-monitor-edit-invoice-title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_po" id="po-monitor-edit-id-po" value="<?= (int) ($po['id_po'] ?? 0) ?>">
                    <input type="hidden" name="id_term" id="po-monitor-edit-id-term">
                    <input type="hidden" name="id_allocation" id="po-monitor-edit-id-allocation">
                    <div class="form-group">
                        <label for="po-monitor-edit-invoice-date">Tanggal Invoice</label>
                        <input type="date" name="invoice_date" id="po-monitor-edit-invoice-date" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="po-monitor-edit-invoice-amount">Nilai Invoice</label>
                        <input type="text" name="invoice_amount" id="po-monitor-edit-invoice-amount" class="form-control" required>
                        <small class="form-text text-muted">Boleh pakai format 3000000, 3.000.000, 3,000,000, atau -1.459.800.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"
                        class="btn btn-outline-danger mr-auto"
                        formaction="<?= site_url('PO_Monitor/reset_invoice_claim') ?>"
                        formmethod="post"
                        onclick="return confirm('Reset invoice ini ke status belum invoice? Claim invoice akan dihapus.');">
                        Reset Invoice
                    </button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        function formatPOMonitorDetailNumber(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        $(document).on('click', '.js-po-monitor-edit-invoice', function() {
            var $button = $(this);
            $('#po-monitor-edit-invoice-title').text($button.data('title') ? '- ' + $button.data('title') : '');
            $('#po-monitor-edit-id-po').val($button.data('id-po') || <?= (int) ($po['id_po'] ?? 0) ?>);
            $('#po-monitor-edit-id-term').val($button.data('id-term') || '');
            $('#po-monitor-edit-id-allocation').val($button.data('id-allocation') || 0);
            $('#po-monitor-edit-invoice-date').val($button.data('invoice-date') || '<?= date('Y-m-d') ?>');
            $('#po-monitor-edit-invoice-amount').val(formatPOMonitorDetailNumber($button.data('invoice-amount') || 0));
        });

        $('#po-monitor-header-bowheer').on('change', function() {
            var $option = $(this).find('option:selected');
            $('#po-monitor-header-dashboard-bowheer').val($option.data('bowheer') || '');
            $('#po-monitor-header-pic').val($option.data('pic') || '');
        });
    })();
</script>
