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
                                    <th>Due Date</th>
                                    <th>SLA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($terms as $t):
                                    $value = floatval($t['value']);
                                    $invoiced = floatval($t['invoiced_amount']);
                                    $remaining = $value - $invoiced;
                                    if ($remaining < 0) $remaining = 0;
                                    $sla = 'AMAN';
                                    if ($remaining > 0) {
                                        if (!empty($t['due_date']) && strtotime($t['due_date']) < strtotime(date('Y-m-d'))) {
                                            $sla = 'OVERDUE';
                                        } elseif (!empty($t['due_date']) && strtotime($t['due_date']) <= strtotime('+7 days')) {
                                            $sla = 'WARNING';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['term_index']) ?></td>
                                        <td><?= number_format(floatval($t['percent']),2,',','.') ?>%</td>
                                        <td><?= number_format($value,0,',','.') ?></td>
                                        <td><?= number_format($invoiced,0,',','.') ?></td>
                                        <td><?= number_format($remaining,0,',','.') ?></td>
                                        <td><?= $t['due_date'] ?></td>
                                        <td><?= $sla ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
