<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">PO DETAIL - <?= htmlspecialchars($po['po_number']) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <?php
                $status = $this->session->flashdata('status');
                $error_log = $this->session->flashdata('error_log');
                ?>
                <?php if ($error_log): ?>
                    <div class="alert alert-<?= $status ? 'success' : 'danger' ?> alert-dismissible fade show">
                        <?= htmlspecialchars($error_log) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <tr><th>PO Number</th><td><?= htmlspecialchars($po['po_number']) ?></td></tr>
                            <tr><th>PO Date</th><td><?= $po['po_date'] ?></td></tr>
                            <tr><th>Total Value</th><td><?= number_format(floatval($po['total_value']),0,',','.') ?></td></tr>
                        </table>

                        <h4 class="mt-4">Terms</h4>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Term #</th>
                                    <th>Percent</th>
                                    <th>Value</th>
                                    <th>Invoiced</th>
                                    <th>Remaining</th>
                                    <th>Target</th>
                                    <th>SLA</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($terms as $t):
                                    $value = floatval($t['value']);
                                    $invoiced = floatval($t['invoiced_amount']);
                                    $remaining = $value - $invoiced;
                                    if ($remaining < 0) $remaining = 0;
                                    $sla = 'AMAN';
                                    $dueDate = !empty($t['target_week_end']) ? $t['target_week_end'] : $t['due_date'];
                                    if ($remaining > 0) {
                                        if (!empty($dueDate) && strtotime($dueDate) < strtotime(date('Y-m-d'))) {
                                            $sla = 'OVERDUE';
                                        } elseif (!empty($dueDate) && strtotime($dueDate) <= strtotime('+7 days')) {
                                            $sla = 'WARNING';
                                        }
                                    }
                                    $targetText = '-';
                                    if (($t['target_status'] ?? '') === 'TARGET_WEEK') {
                                        $targetText = 'W' . (int) $t['target_week'] . ' / ' . (int) $t['target_year'] . '<br><small>' . htmlspecialchars($t['target_week_start'] . ' s/d ' . $t['target_week_end']) . '</small>';
                                    } elseif (($t['target_status'] ?? '') === 'CARRY_OVER') {
                                        $targetText = 'Carry Over ' . htmlspecialchars($t['target_year'] ?: '2027');
                                    } elseif (($t['target_status'] ?? '') === 'INVOICED') {
                                        $targetText = 'Invoiced<br><small>' . htmlspecialchars($t['invoice_date'] ?: '') . '</small>';
                                    } elseif (!empty($t['due_date'])) {
                                        $targetText = htmlspecialchars($t['due_date']);
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['term_index']) ?></td>
                                        <td><?= number_format(floatval($t['percent']),2,',','.') ?>%</td>
                                        <td><?= number_format($value,0,',','.') ?></td>
                                        <td><?= number_format($invoiced,0,',','.') ?></td>
                                        <td><?= number_format($remaining,0,',','.') ?></td>
                                        <td><?= $targetText ?></td>
                                        <td><?= $sla ?></td>
                                        <td style="min-width: 260px;">
                                            <?php if ($remaining > 0): ?>
                                                <form method="post" action="<?= site_url('PO_Monitor/claim_term') ?>" class="form-inline">
                                                    <input type="hidden" name="id_po" value="<?= (int) $po['id_po'] ?>">
                                                    <input type="hidden" name="id_term" value="<?= (int) $t['id_term'] ?>">
                                                    <input type="date" name="invoice_date" class="form-control form-control-sm mr-1 mb-1" required>
                                                    <input type="text" name="invoice_amount" class="form-control form-control-sm mr-1 mb-1" value="<?= number_format($remaining,0,',','.') ?>" style="width: 110px;" required>
                                                    <button type="submit" class="btn btn-sm btn-success mb-1">Claim</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Done</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($allocationMap[(int) $t['id_term']])): ?>
                                        <tr>
                                            <td colspan="8" class="p-2 bg-light">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>NO PO SUB</th>
                                                            <th>Regional</th>
                                                            <th>Kota</th>
                                                            <th>Detail</th>
                                                            <th>Remarks</th>
                                                            <th>Value</th>
                                                            <th>Invoiced</th>
                                                            <th>Outstanding</th>
                                                            <th>Target</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($allocationMap[(int) $t['id_term']] as $allocation):
                                                            $allocationTarget = '-';
                                                            if (($allocation['target_status'] ?? '') === 'TARGET_WEEK') {
                                                                $allocationTarget = 'W' . (int) $allocation['target_week'] . ' / ' . (int) $allocation['target_year'] . '<br><small>' . htmlspecialchars($allocation['target_week_start'] . ' s/d ' . $allocation['target_week_end']) . '</small>';
                                                            } elseif (($allocation['target_status'] ?? '') === 'CARRY_OVER') {
                                                                $allocationTarget = 'Carry Over ' . htmlspecialchars($allocation['target_year'] ?: '2027');
                                                            } elseif (($allocation['target_status'] ?? '') === 'INVOICED') {
                                                                $allocationTarget = 'Invoiced<br><small>' . htmlspecialchars($allocation['invoice_date'] ?: '') . '</small>';
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($allocation['no_po_sub'] ?: '-') ?></td>
                                                                <td><?= htmlspecialchars($allocation['regional'] ?: '-') ?></td>
                                                                <td><?= htmlspecialchars($allocation['kota_po'] ?: '-') ?></td>
                                                                <td><?= htmlspecialchars($allocation['detail_po'] ?: '-') ?></td>
                                                                <td><?= htmlspecialchars($allocation['remarks'] ?: '-') ?></td>
                                                                <td><?= number_format(floatval($allocation['allocation_value']),0,',','.') ?></td>
                                                                <td><?= number_format(floatval($allocation['invoiced_amount']),0,',','.') ?></td>
                                                                <td><?= number_format(floatval($allocation['outstanding_amount']),0,',','.') ?></td>
                                                                <td><?= $allocationTarget ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
