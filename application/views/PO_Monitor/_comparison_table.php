<?php
$matrix = $matrix ?? [
    'months' => [],
    'rows' => [],
    'totals' => [
        'months' => [],
        'total_target' => 0,
        'total_achieved' => 0,
        'deviasi' => 0,
        'achieved_percent' => 0,
        'deviasi_percent' => 0
    ]
];
$groupBy = ($groupBy ?? 'month') === 'week' ? 'week' : 'month';
$tableId = $tableId ?? ($groupBy === 'week' ? 'table_po_target_invoice_compare_week' : 'table_po_target_invoice_compare_month');

if (!function_exists('po_monitor_compare_week_groups')) {
    function po_monitor_compare_week_groups($matrix)
    {
        $groups = [];
        foreach (($matrix['months'] ?? []) as $period) {
            $groupKey = ($period['month_key'] ?? '') . '|' . ($period['year'] ?? '');
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'label' => ($period['month_label'] ?? $period['label']) . ' ' . ($period['year'] ?? ''),
                    'count' => 0
                ];
            }
            $groups[$groupKey]['count']++;
        }

        return $groups;
    }
}

if (!function_exists('po_monitor_percent')) {
    function po_monitor_percent($value)
    {
        return number_format((float) $value, 0, ',', '.') . '%';
    }
}

if (!function_exists('po_monitor_compare_amount_link')) {
    function po_monitor_compare_amount_link($value, $idBowheer, $periodKey, $groupBy, $type)
    {
        $value = (float) $value;
        if ($value <= 0) {
            return '-';
        }

        return '<button type="button" class="btn btn-link btn-sm p-0 po-compare-detail-link"'
            . ' data-id-bowheer="' . (int) $idBowheer . '"'
            . ' data-period-key="' . htmlspecialchars($periodKey, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-group-by="' . htmlspecialchars($groupBy, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">'
            . number_format($value, 0, ',', '.')
            . '</button>';
    }
}

if (!function_exists('po_monitor_compare_total_amount_link')) {
    function po_monitor_compare_total_amount_link($value, $idBowheer, $groupBy, $type, $fromMonth, $toMonth)
    {
        $value = (float) $value;
        if ($value <= 0) {
            return '-';
        }

        return '<button type="button" class="btn btn-link btn-sm p-0 po-compare-detail-link"'
            . ' data-id-bowheer="' . (int) $idBowheer . '"'
            . ' data-period-key="__total__"'
            . ' data-group-by="' . htmlspecialchars($groupBy, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-from-month="' . htmlspecialchars((string) $fromMonth, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-to-month="' . htmlspecialchars((string) $toMonth, ENT_QUOTES, 'UTF-8') . '">'
            . number_format($value, 0, ',', '.')
            . '</button>';
    }
}
?>
<table id="<?= htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8') ?>" class="table table-bordered table-striped po-compare-table">
    <thead>
        <?php if ($groupBy === 'week'): ?>
            <tr class="po-compare-month">
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">No</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">PIC</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                <?php foreach (po_monitor_compare_week_groups($matrix) as $group): ?>
                    <th colspan="<?= (int) $group['count'] * 3 ?>" class="po-compare-month-cell"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></th>
                <?php endforeach; ?>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
            </tr>
            <tr>
                <?php foreach ($matrix['months'] as $month): ?>
                    <th colspan="3" class="po-compare-week-cell">
                        <?= htmlspecialchars($month['label'], ENT_QUOTES, 'UTF-8') ?><br>
                        <small><?= htmlspecialchars($month['period'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                    </th>
                <?php endforeach; ?>
            </tr>
        <?php else: ?>
            <tr class="po-compare-month">
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-left">No</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-left">PIC</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                <?php foreach ($matrix['months'] as $month): ?>
                    <th colspan="3" class="po-compare-month-cell">
                        <?= htmlspecialchars($month['label'], ENT_QUOTES, 'UTF-8') ?><br>
                        <small><?= htmlspecialchars($month['year'], ENT_QUOTES, 'UTF-8') ?></small>
                    </th>
                <?php endforeach; ?>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
            </tr>
        <?php endif; ?>
        <tr>
            <?php foreach ($matrix['months'] as $month): ?>
                <th class="po-compare-target">Target</th>
                <th class="po-compare-achieved">Achieved</th>
                <th class="po-compare-percent">%</th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php $compareNo = 1; foreach ($matrix['rows'] as $row): ?>
            <tr data-achieved="<?= (float) $row['total_achieved'] ?>" data-target="<?= (float) $row['total_target'] ?>">
                <td><?= $compareNo++ ?></td>
                <td><?= htmlspecialchars($row['pic'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['project'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-po-amount="<?= (float) $row['total_target'] ?>"><?= po_monitor_compare_total_amount_link($row['total_target'], $row['id_bowheer'], $groupBy, 'target', $matrix['from'] ?? '', $matrix['to'] ?? '') ?></td>
                <?php foreach ($matrix['months'] as $month): ?>
                    <?php $monthData = $row['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                    <td data-po-amount="<?= (float) $monthData['target'] ?>"><?= po_monitor_compare_amount_link($monthData['target'], $row['id_bowheer'], $month['key'], $groupBy, 'target') ?></td>
                    <td data-po-amount="<?= (float) $monthData['achieved'] ?>"><?= po_monitor_compare_amount_link($monthData['achieved'], $row['id_bowheer'], $month['key'], $groupBy, 'achieved') ?></td>
                    <td><?= ((float) $monthData['target'] > 0 || (float) $monthData['achieved'] > 0) ? po_monitor_percent($monthData['percent']) : '-' ?></td>
                <?php endforeach; ?>
                <td data-po-amount="<?= (float) $row['total_achieved'] ?>"><?= number_format((float) $row['total_achieved'], 0, ',', '.') ?></td>
                <td data-po-amount="<?= (float) $row['deviasi'] ?>"><?= number_format((float) $row['deviasi'], 0, ',', '.') ?></td>
                <td><?= po_monitor_percent($row['achieved_percent']) ?></td>
                <td><?= po_monitor_percent($row['deviasi_percent']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="po-compare-footer-label">Total</th>
            <th><?= number_format((float) $matrix['totals']['total_target'], 0, ',', '.') ?></th>
            <?php foreach ($matrix['months'] as $month): ?>
                <?php $monthTotal = $matrix['totals']['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                <th><?= number_format((float) $monthTotal['target'], 0, ',', '.') ?></th>
                <th><?= number_format((float) $monthTotal['achieved'], 0, ',', '.') ?></th>
                <th><?= po_monitor_percent($monthTotal['percent']) ?></th>
            <?php endforeach; ?>
            <th><?= number_format((float) $matrix['totals']['total_achieved'], 0, ',', '.') ?></th>
            <th><?= number_format((float) $matrix['totals']['deviasi'], 0, ',', '.') ?></th>
            <th><?= po_monitor_percent($matrix['totals']['achieved_percent']) ?></th>
            <th><?= po_monitor_percent($matrix['totals']['deviasi_percent']) ?></th>
        </tr>
    </tfoot>
</table>
