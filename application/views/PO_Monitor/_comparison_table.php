<?php
$matrix = $matrix ?? [
    'months' => [],
    'rows' => [],
    'totals' => [
        'months' => [],
        'total_target' => 0,
        'total_achieved' => 0,
        'deviasi' => 0,
        'deviasi_by_po' => 0,
        'achieved_percent' => 0,
        'deviasi_percent' => 0
    ]
];
$groupBy = ($groupBy ?? 'month') === 'week' ? 'week' : 'month';
$tableId = $tableId ?? ($groupBy === 'week' ? 'table_po_target_invoice_compare_week' : 'table_po_target_invoice_compare_month');
$comparisonCumulative = !empty($comparisonCumulative);
$weekSingleCumulative = $comparisonCumulative && $groupBy === 'week';
$monthMatrix = is_array($monthMatrix ?? null) ? $monthMatrix : [];

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

if (!function_exists('po_monitor_compare_previous_period_label')) {
    function po_monitor_compare_previous_period_label(array $period, $groupBy)
    {
        $key = (string) ($period['key'] ?? '');
        if ($groupBy === 'week' && preg_match('/^(\d{4})-W(\d{1,2})$/', $key, $matches)) {
            $year = (int) $matches[1];
            $week = (int) $matches[2] - 1;
            if ($week <= 0) {
                $year--;
                $week = 53;
            }

            return 'Kumulatif W' . $week;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $key)) {
            $timestamp = strtotime($key . '-01 -1 month');
            $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
            return 'Kumulatif ' . ($months[(int) date('n', $timestamp)] ?? date('F', $timestamp));
        }

        return 'Kumulatif';
    }
}

if (!function_exists('po_monitor_week_cumulative_label')) {
    function po_monitor_week_cumulative_label(array $monthMatrix)
    {
        $months = $monthMatrix['months'] ?? [];
        if (count($months) === 1) {
            return po_monitor_compare_previous_period_label($months[0], 'month');
        }

        return 'Kumulatif';
    }
}

if (!function_exists('po_monitor_compare_cumulative_periods')) {
    function po_monitor_compare_cumulative_periods(array $period)
    {
        return array_values(array_filter($period['cumulative_periods'] ?? [], function ($item) {
            return (float) ($item['amount'] ?? 0) > 0.000001;
        }));
    }
}

if (!function_exists('po_monitor_compare_cumulative_amount_by_key')) {
    function po_monitor_compare_cumulative_amount_by_key(array $periodData, $periodKey)
    {
        foreach (($periodData['cumulative_breakdown'] ?? []) as $item) {
            if ((string) ($item['key'] ?? '') === (string) $periodKey) {
                return (float) ($item['amount'] ?? 0);
            }
        }

        return 0;
    }
}

if (!function_exists('po_monitor_week_cumulative_periods')) {
    function po_monitor_week_cumulative_periods(array $monthMatrix)
    {
        $periods = [];
        foreach (($monthMatrix['months'] ?? []) as $month) {
            foreach (po_monitor_compare_cumulative_periods($month) as $item) {
                $key = (string) ($item['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                if (!isset($periods[$key])) {
                    $periods[$key] = [
                        'key' => $key,
                        'label' => (string) ($item['label'] ?? $key),
                        'amount' => 0
                    ];
                }
                $periods[$key]['amount'] += (float) ($item['amount'] ?? 0);
            }
        }

        return array_values(array_filter($periods, function ($item) {
            return (float) ($item['amount'] ?? 0) > 0.000001;
        }));
    }
}

if (!function_exists('po_monitor_week_cumulative_amount_by_key')) {
    function po_monitor_week_cumulative_amount_by_key(array $monthMatrix, $idBowheer, $periodKey)
    {
        foreach (($monthMatrix['rows'] ?? []) as $row) {
            if ((int) ($row['id_bowheer'] ?? 0) !== (int) $idBowheer) {
                continue;
            }

            $total = 0;
            foreach (($row['months'] ?? []) as $monthData) {
                $total += po_monitor_compare_cumulative_amount_by_key($monthData, $periodKey);
            }
            return $total;
        }

        return 0;
    }
}

if (!function_exists('po_monitor_week_cumulative_total_by_key')) {
    function po_monitor_week_cumulative_total_by_key(array $monthMatrix, $periodKey)
    {
        $total = 0;
        foreach (($monthMatrix['totals']['months'] ?? []) as $monthData) {
            $total += po_monitor_compare_cumulative_amount_by_key($monthData, $periodKey);
        }

        return $total;
    }
}

if (!function_exists('po_monitor_week_cumulative_period_key')) {
    function po_monitor_week_cumulative_period_key(array $monthMatrix)
    {
        $months = $monthMatrix['months'] ?? [];
        return count($months) === 1 ? (string) ($months[0]['key'] ?? '__total__') : '__total__';
    }
}

if (!function_exists('po_monitor_week_cumulative_amount')) {
    function po_monitor_week_cumulative_amount(array $monthMatrix, $idBowheer)
    {
        $total = 0;
        foreach (($monthMatrix['rows'] ?? []) as $row) {
            if ((int) ($row['id_bowheer'] ?? 0) !== (int) $idBowheer) {
                continue;
            }
            foreach (($row['months'] ?? []) as $month) {
                $total += (float) ($month['cumulative'] ?? 0);
            }
            break;
        }

        return $total;
    }
}

if (!function_exists('po_monitor_week_cumulative_total')) {
    function po_monitor_week_cumulative_total(array $monthMatrix)
    {
        $total = 0;
        foreach (($monthMatrix['totals']['months'] ?? []) as $month) {
            $total += (float) ($month['cumulative'] ?? 0);
        }

        return $total;
    }
}

if (!function_exists('po_monitor_percent')) {
    function po_monitor_percent($value)
    {
        return number_format((float) $value, 0, ',', '.') . '%';
    }
}

if (!function_exists('po_monitor_achieved_percent_badge')) {
    function po_monitor_achieved_percent_badge($value)
    {
        $value = (float) $value;
        $isGood = $value >= 100;
        $className = $isGood ? 'po-compare-achieved-percent--good' : 'po-compare-achieved-percent--bad';
        $icon = $isGood ? 'fa-arrow-up' : 'fa-arrow-down';

        return '<span class="po-compare-achieved-percent ' . $className . '">'
            . htmlspecialchars(po_monitor_percent($value), ENT_QUOTES, 'UTF-8')
            . '<i class="fas ' . $icon . '"></i>'
            . '</span>';
    }
}

if (!function_exists('po_monitor_compare_effective_target_total')) {
    function po_monitor_compare_effective_target_total(array $months)
    {
        $total = 0;
        foreach ($months as $month) {
            $total += array_key_exists('effective_target', $month)
                ? (float) $month['effective_target']
                : (float) ($month['cumulative'] ?? 0) + (float) ($month['target'] ?? 0);
        }

        return $total;
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
    function po_monitor_compare_total_amount_link($value, $idBowheer, $groupBy, $type, $fromMonth, $toMonth, $periodKey = '__total__')
    {
        $value = (float) $value;
        if ($value <= 0) {
            return '-';
        }

        return '<button type="button" class="btn btn-link btn-sm p-0 po-compare-detail-link"'
            . ' data-id-bowheer="' . (int) $idBowheer . '"'
            . ' data-period-key="' . htmlspecialchars((string) $periodKey, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-group-by="' . htmlspecialchars($groupBy, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-from-month="' . htmlspecialchars((string) $fromMonth, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-to-month="' . htmlspecialchars((string) $toMonth, ENT_QUOTES, 'UTF-8') . '">'
            . number_format($value, 0, ',', '.')
            . '</button>';
    }
}
?>
<?php $weekCumulativePeriods = $weekSingleCumulative ? po_monitor_week_cumulative_periods($monthMatrix) : []; ?>
<table id="<?= htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8') ?>" class="table table-bordered table-striped po-compare-table">
    <thead>
        <?php if ($groupBy === 'week'): ?>
            <tr class="po-compare-month">
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">No</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">PIC</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                <?php foreach ($weekCumulativePeriods as $cumulativePeriod): ?>
                    <th rowspan="3" class="po-compare-fixed po-compare-fixed-total po-compare-week-cumulative-lead">Kumulatif <?= htmlspecialchars($cumulativePeriod['label'] ?? $cumulativePeriod['key'], ENT_QUOTES, 'UTF-8') ?></th>
                <?php endforeach; ?>
                <?php foreach (po_monitor_compare_week_groups($matrix) as $group): ?>
                    <th colspan="<?= (int) $group['count'] * 3 ?>" class="po-compare-month-cell"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></th>
                <?php endforeach; ?>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi By Target</th>
                <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi By PO</th>
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
                    <?php $cumulativePeriods = $comparisonCumulative ? po_monitor_compare_cumulative_periods($month) : []; ?>
                    <th colspan="<?= 3 + count($cumulativePeriods) ?>" class="po-compare-month-cell">
                        <?= htmlspecialchars($month['label'], ENT_QUOTES, 'UTF-8') ?><br>
                        <small><?= htmlspecialchars($month['year'], ENT_QUOTES, 'UTF-8') ?></small>
                    </th>
                <?php endforeach; ?>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi By Target</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi By PO</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
            </tr>
        <?php endif; ?>
        <tr>
            <?php foreach ($matrix['months'] as $month): ?>
                <?php if ($comparisonCumulative && !$weekSingleCumulative): ?>
                    <?php foreach (po_monitor_compare_cumulative_periods($month) as $cumulativePeriod): ?>
                        <th class="po-compare-target po-compare-cumulative-column">Kumulatif <?= htmlspecialchars($cumulativePeriod['label'] ?? $cumulativePeriod['key'], ENT_QUOTES, 'UTF-8') ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <th class="po-compare-target">Target</th>
                <th class="po-compare-achieved">Achieved</th>
                <th class="po-compare-percent">%</th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php $compareNo = 1; foreach ($matrix['rows'] as $row): ?>
            <?php
                $weekCumulativeAmount = 0;
                foreach ($weekCumulativePeriods as $cumulativePeriod) {
                    $weekCumulativeAmount += po_monitor_week_cumulative_amount_by_key($monthMatrix, $row['id_bowheer'] ?? 0, $cumulativePeriod['key'] ?? '');
                }
                $rowDisplayTarget = $comparisonCumulative
                    ? ($weekSingleCumulative ? ((float) $row['total_target'] + $weekCumulativeAmount) : po_monitor_compare_effective_target_total($row['months'] ?? []))
                    : (float) $row['total_target'];
                $rowDisplayDeviasi = $comparisonCumulative ? max($rowDisplayTarget - (float) $row['total_achieved'], 0) : (float) $row['deviasi'];
                $rowDisplayDeviasiByPo = $comparisonCumulative ? (float) ($row['total_effective_deviasi_by_po'] ?? $row['deviasi_by_po'] ?? 0) : (float) ($row['deviasi_by_po'] ?? 0);
                $rowDisplayAchievedPercent = $comparisonCumulative ? ($rowDisplayTarget > 0 ? ((float) $row['total_achieved'] / $rowDisplayTarget) * 100 : ((float) $row['total_achieved'] > 0 ? 100 : 0)) : (float) $row['achieved_percent'];
                $rowDisplayDeviasiPercent = $comparisonCumulative ? max(100 - $rowDisplayAchievedPercent, 0) : (float) $row['deviasi_percent'];
            ?>
            <tr data-achieved="<?= (float) $row['total_achieved'] ?>" data-target="<?= (float) $rowDisplayTarget ?>">
                <td><?= $compareNo++ ?></td>
                <td><?= htmlspecialchars($row['pic'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['project'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-po-amount="<?= (float) $rowDisplayTarget ?>"><?= po_monitor_compare_total_amount_link($rowDisplayTarget, $row['id_bowheer'], $groupBy, $comparisonCumulative ? 'effective_target' : 'target', $matrix['from'] ?? '', $matrix['to'] ?? '') ?></td>
                <?php foreach ($weekCumulativePeriods as $cumulativePeriod): ?>
                    <?php $cumulativeAmount = po_monitor_week_cumulative_amount_by_key($monthMatrix, $row['id_bowheer'] ?? 0, $cumulativePeriod['key'] ?? ''); ?>
                    <td data-po-amount="<?= (float) $cumulativeAmount ?>" class="po-compare-cumulative-cell po-compare-week-cumulative-cell"><?= po_monitor_compare_total_amount_link($cumulativeAmount, $row['id_bowheer'], 'month', 'cumulative', $monthMatrix['from'] ?? ($matrix['from'] ?? ''), $monthMatrix['to'] ?? ($matrix['to'] ?? ''), $cumulativePeriod['key'] ?? '') ?></td>
                <?php endforeach; ?>
                <?php foreach ($matrix['months'] as $month): ?>
                    <?php $monthData = $row['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0, 'cumulative' => 0, 'cumulative_breakdown' => [], 'cumulative_percent' => 0]; ?>
                    <?php if ($comparisonCumulative && !$weekSingleCumulative): ?>
                        <?php foreach (po_monitor_compare_cumulative_periods($month) as $cumulativePeriod): ?>
                            <?php $cumulativeAmount = po_monitor_compare_cumulative_amount_by_key($monthData, $cumulativePeriod['key'] ?? ''); ?>
                            <td data-po-amount="<?= (float) $cumulativeAmount ?>" class="po-compare-cumulative-cell"><?= po_monitor_compare_total_amount_link($cumulativeAmount, $row['id_bowheer'], $groupBy, 'cumulative', $matrix['from'] ?? '', $matrix['to'] ?? '', $cumulativePeriod['key'] ?? '') ?></td>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <td data-po-amount="<?= (float) $monthData['target'] ?>" class="po-compare-target-cell"><?= po_monitor_compare_amount_link($monthData['target'], $row['id_bowheer'], $month['key'], $groupBy, 'target') ?></td>
                    <td data-po-amount="<?= (float) $monthData['achieved'] ?>" class="po-compare-achieved-cell"><?= po_monitor_compare_amount_link($monthData['achieved'], $row['id_bowheer'], $month['key'], $groupBy, 'achieved') ?></td>
                    <td class="po-compare-percent-cell"><?= ((float) $monthData['target'] > 0 || (float) $monthData['achieved'] > 0 || (float) ($monthData['cumulative'] ?? 0) > 0) ? po_monitor_percent($weekSingleCumulative ? ($monthData['percent'] ?? 0) : ($comparisonCumulative ? ($monthData['cumulative_percent'] ?? 0) : $monthData['percent'])) : '-' ?></td>
                <?php endforeach; ?>
                <td data-po-amount="<?= (float) $row['total_achieved'] ?>"><?= number_format((float) $row['total_achieved'], 0, ',', '.') ?></td>
                <td data-po-amount="<?= (float) $rowDisplayDeviasi ?>"><?= number_format((float) $rowDisplayDeviasi, 0, ',', '.') ?></td>
                <td data-po-amount="<?= (float) $rowDisplayDeviasiByPo ?>"><?= po_monitor_compare_total_amount_link($rowDisplayDeviasiByPo, $row['id_bowheer'], $groupBy, 'deviasi_by_po', $matrix['from'] ?? '', $matrix['to'] ?? '') ?></td>
                <td data-order="<?= (float) $rowDisplayAchievedPercent ?>"><?= po_monitor_achieved_percent_badge($rowDisplayAchievedPercent) ?></td>
                <td><?= po_monitor_percent($rowDisplayDeviasiPercent) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="po-compare-footer-label">Total</th>
            <?php
                $footerWeekCumulative = 0;
                foreach ($weekCumulativePeriods as $cumulativePeriod) {
                    $footerWeekCumulative += po_monitor_week_cumulative_total_by_key($monthMatrix, $cumulativePeriod['key'] ?? '');
                }
                $footerDisplayTarget = $comparisonCumulative ? ($weekSingleCumulative ? ((float) $matrix['totals']['total_target'] + $footerWeekCumulative) : po_monitor_compare_effective_target_total($matrix['totals']['months'] ?? [])) : (float) $matrix['totals']['total_target'];
                $footerDisplayDeviasi = $comparisonCumulative ? max($footerDisplayTarget - (float) $matrix['totals']['total_achieved'], 0) : (float) $matrix['totals']['deviasi'];
                $footerDisplayAchievedPercent = $comparisonCumulative ? ($footerDisplayTarget > 0 ? ((float) $matrix['totals']['total_achieved'] / $footerDisplayTarget) * 100 : ((float) $matrix['totals']['total_achieved'] > 0 ? 100 : 0)) : (float) $matrix['totals']['achieved_percent'];
                $footerDisplayDeviasiPercent = $comparisonCumulative ? max(100 - $footerDisplayAchievedPercent, 0) : (float) $matrix['totals']['deviasi_percent'];
            ?>
            <th><?= number_format((float) $footerDisplayTarget, 0, ',', '.') ?></th>
            <?php foreach ($weekCumulativePeriods as $cumulativePeriod): ?>
                <?php $cumulativeAmount = po_monitor_week_cumulative_total_by_key($monthMatrix, $cumulativePeriod['key'] ?? ''); ?>
                <th><?= abs((float) $cumulativeAmount) > 0.000001 ? number_format((float) $cumulativeAmount, 0, ',', '.') : '-' ?></th>
            <?php endforeach; ?>
            <?php foreach ($matrix['months'] as $month): ?>
                <?php $monthTotal = $matrix['totals']['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0, 'cumulative' => 0, 'cumulative_breakdown' => [], 'cumulative_percent' => 0]; ?>
                <?php if ($comparisonCumulative && !$weekSingleCumulative): ?>
                    <?php foreach (po_monitor_compare_cumulative_periods($month) as $cumulativePeriod): ?>
                        <?php $cumulativeAmount = po_monitor_compare_cumulative_amount_by_key($monthTotal, $cumulativePeriod['key'] ?? ''); ?>
                        <th><?= abs((float) $cumulativeAmount) > 0.000001 ? number_format((float) $cumulativeAmount, 0, ',', '.') : '-' ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <th><?= number_format((float) $monthTotal['target'], 0, ',', '.') ?></th>
                <th><?= number_format((float) $monthTotal['achieved'], 0, ',', '.') ?></th>
                <th><?= po_monitor_percent($weekSingleCumulative ? ($monthTotal['percent'] ?? 0) : ($comparisonCumulative ? ($monthTotal['cumulative_percent'] ?? 0) : $monthTotal['percent'])) ?></th>
            <?php endforeach; ?>
            <th><?= number_format((float) $matrix['totals']['total_achieved'], 0, ',', '.') ?></th>
            <th><?= number_format((float) $footerDisplayDeviasi, 0, ',', '.') ?></th>
            <th><?= number_format((float) ($comparisonCumulative ? ($matrix['totals']['total_effective_deviasi_by_po'] ?? $matrix['totals']['deviasi_by_po'] ?? 0) : ($matrix['totals']['deviasi_by_po'] ?? 0)), 0, ',', '.') ?></th>
            <th><?= po_monitor_achieved_percent_badge($footerDisplayAchievedPercent) ?></th>
            <th><?= po_monitor_percent($footerDisplayDeviasiPercent) ?></th>
        </tr>
    </tfoot>
</table>
