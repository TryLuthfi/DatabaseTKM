<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$isLocalAccess = $isLocalAccess ?? false;
$batchInvoiceRows = is_array($batchInvoiceRows ?? null) ? $batchInvoiceRows : [];
$comparisonMatrix = $comparisonMatrix ?? [
    'from' => date('Y-m'),
    'to' => date('Y-m'),
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
$comparisonWeekMatrix = $comparisonWeekMatrix ?? $comparisonMatrix;
$dashboardSummary = $dashboardSummary ?? [
    'rows' => [],
    'totals' => [
        'data_count' => 0,
        'all_po' => 0,
        'done_inv_2026' => 0,
        'outs_2026_on_target' => 0,
        'ny_po_on_target_2026' => 0,
        'grandtotal_target' => 0,
        'ny_po_total' => 0,
        'co_to_2027' => 0,
        'total_outs' => 0
    ]
];
$comparisonWeekMonthGroups = [];
foreach ($comparisonWeekMatrix['months'] as $period) {
    $groupKey = ($period['month_key'] ?? '') . '|' . ($period['year'] ?? '');
    if (!isset($comparisonWeekMonthGroups[$groupKey])) {
        $comparisonWeekMonthGroups[$groupKey] = [
            'label' => ($period['month_label'] ?? $period['label']) . ' ' . ($period['year'] ?? ''),
            'count' => 0
        ];
    }
    $comparisonWeekMonthGroups[$groupKey]['count']++;
}
$comparisonIsWeek = false;

if (!function_exists('po_monitor_week_month_groups')) {
    function po_monitor_week_month_groups($matrix)
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

if (!function_exists('po_monitor_indonesian_date')) {
    function po_monitor_indonesian_date($date)
    {
        $timestamp = strtotime((string) $date);
        if (!$timestamp) {
            return (string) $date;
        }

        $months = [
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
            12 => 'Desember'
        ];

        return (int) date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

if (!function_exists('po_monitor_indonesian_date_range')) {
    function po_monitor_indonesian_date_range($startDate, $endDate)
    {
        return po_monitor_indonesian_date($startDate) . ' s/d ' . po_monitor_indonesian_date($endDate);
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

if (!function_exists('po_monitor_term_amount_link')) {
    function po_monitor_term_amount_link($value, $idBowheer, $metric, $termIndex = 0)
    {
        $value = (float) $value;
        if ($value <= 0) {
            return '-';
        }

        return '<button type="button" class="btn btn-link btn-sm p-0 po-monitor-term-detail-link"'
            . ' data-id-bowheer="' . (int) $idBowheer . '"'
            . ' data-metric="' . htmlspecialchars($metric, ENT_QUOTES, 'UTF-8') . '"'
            . ' data-term-index="' . (int) $termIndex . '">'
            . number_format($value, 0, ',', '.')
            . '</button>';
    }
}

?>

<style>
    .po-monitor-switch-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        align-items: center;
    }

    .po-monitor-switch {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .po-monitor-switch input {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
    }

    .po-monitor-switch-slider {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 24px;
        flex: 0 0 42px;
        border-radius: 999px;
        background: #cbd5e1;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .po-monitor-switch-slider::before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        transition: transform 0.2s ease;
    }

    .po-monitor-switch input:checked + .po-monitor-switch-slider {
        background: #2563eb;
    }

    .po-monitor-switch input:checked + .po-monitor-switch-slider::before {
        transform: translateX(18px);
    }

    .po-compare-table thead .po-compare-month th,
    .po-compare-table thead .po-compare-month-cell {
        background: #08dede;
        color: #02111a;
        font-weight: 900;
        text-align: center;
        text-transform: uppercase;
        border-color: #dbeafe;
    }

    .po-compare-table {
        border-collapse: collapse !important;
        font-size: 12px;
    }

    .po-compare-table th,
    .po-compare-table td {
        padding: 4px 7px !important;
        vertical-align: middle !important;
        white-space: nowrap;
        line-height: 1.25;
    }

    .po-compare-table thead th {
        font-size: 12px;
        line-height: 1.15;
        vertical-align: middle !important;
        height: auto;
        min-height: 34px;
        box-sizing: border-box;
        overflow: visible;
        white-space: nowrap;
    }

    .po-compare-table thead tr:nth-child(2) th,
    .po-compare-table thead tr:nth-child(3) th {
        height: auto;
        min-height: 28px;
        padding-top: 5px !important;
        padding-bottom: 5px !important;
    }

    .po-compare-table thead .po-compare-month-cell small,
    .po-compare-table thead .po-compare-week-cell small {
        display: block;
        font-size: 10px;
        line-height: 1;
        margin-top: 1px;
    }

    .po-compare-table thead .sorting,
    .po-compare-table thead .sorting_asc,
    .po-compare-table thead .sorting_desc,
    .po-compare-table thead .sorting_asc_disabled,
    .po-compare-table thead .sorting_desc_disabled {
        background-image: none !important;
        padding-right: 7px !important;
    }

    .po-compare-table thead .sorting::before,
    .po-compare-table thead .sorting::after,
    .po-compare-table thead .sorting_asc::before,
    .po-compare-table thead .sorting_asc::after,
    .po-compare-table thead .sorting_desc::before,
    .po-compare-table thead .sorting_desc::after {
        display: none !important;
        content: "" !important;
    }

    .po-compare-table tfoot th {
        padding: 5px 7px !important;
        white-space: nowrap;
    }

    .po-compare-table thead .po-compare-week-cell {
        background: #11e1df;
        color: #02111a;
        font-weight: 900;
        text-align: center;
        text-transform: uppercase;
    }

    .po-compare-table thead .po-compare-fixed {
        background: #294f50;
        color: #fff;
        text-align: center;
        vertical-align: middle;
        font-weight: 900;
        text-transform: uppercase;
    }

    .po-compare-table thead .po-compare-fixed-left {
        background: #1f3f46;
        color: #fff;
    }

    .po-compare-table thead .po-compare-fixed-total {
        background: #3c5558;
        color: #fff;
    }

    .po-compare-table thead .po-compare-month-cell:nth-child(even) {
        background: #08dede;
    }

    .po-compare-table thead .po-compare-month-cell:nth-child(odd) {
        background: #29efe8;
    }

    .po-compare-table thead .po-compare-target {
        background: #d45c5c;
        color: #111827;
        text-align: center;
        font-weight: 900;
    }

    .po-compare-table thead .po-compare-achieved {
        background: #9dc99d;
        color: #111827;
        text-align: center;
        font-weight: 900;
    }

    .po-compare-table thead .po-compare-percent {
        background: #821eed;
        color: #fff;
        text-align: center;
        font-weight: 900;
    }

    .po-compare-panel-hidden {
        display: none;
    }

    #table_po_target_invoice_compare_month_wrapper .dataTables_length,
    #table_po_target_invoice_compare_month_wrapper .dataTables_filter,
    #table_po_target_invoice_compare_month_wrapper .dataTables_info,
    #table_po_target_invoice_compare_month_wrapper .dataTables_paginate,
    #table_po_target_invoice_compare_week_wrapper .dataTables_length,
    #table_po_target_invoice_compare_week_wrapper .dataTables_filter,
    #table_po_target_invoice_compare_week_wrapper .dataTables_info,
    #table_po_target_invoice_compare_week_wrapper .dataTables_paginate {
        font-size: 12px;
    }

    #table_po_target_invoice_compare_month_wrapper .dataTables_length label,
    #table_po_target_invoice_compare_week_wrapper .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    #table_po_target_invoice_compare_month_wrapper .form-control,
    #table_po_target_invoice_compare_week_wrapper .form-control {
        height: 34px;
        min-width: 58px;
        padding: 4px 10px;
        font-size: 12px;
    }

    #table_po_target_invoice_compare_month_wrapper .dataTables_scrollBody thead,
    #table_po_target_invoice_compare_week_wrapper .dataTables_scrollBody thead {
        visibility: collapse !important;
    }

    #table_po_target_invoice_compare_month_wrapper .dataTables_scrollBody thead th,
    #table_po_target_invoice_compare_week_wrapper .dataTables_scrollBody thead th {
        height: 0 !important;
        min-height: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border-top-width: 0 !important;
        border-bottom-width: 0 !important;
        line-height: 0 !important;
    }

    .po-dashboard-excel {
        border-collapse: collapse !important;
        font-size: 12px;
    }

    .po-dashboard-excel th,
    .po-dashboard-excel td {
        border: 1px solid #000 !important;
        padding: 3px 7px !important;
        vertical-align: middle !important;
        color: #000;
        white-space: nowrap;
    }

    .po-dashboard-excel thead th {
        background: #bfbfbf;
        color: #000;
        text-align: center;
        font-weight: 900;
        text-transform: uppercase;
    }

    .po-dashboard-excel thead th.po-dash-head-invoice {
        background: #00b050;
    }

    .po-dashboard-excel thead th.po-dash-head-target,
    .po-dashboard-excel thead th.po-dash-head-ny {
        background: #ffc000;
    }

    .po-dashboard-excel tfoot tr.po-dash-focus-total th {
        background: #ffc000 !important;
        font-weight: 900;
    }

    .po-dashboard-excel .po-dash-pic-z,
    .po-dashboard-excel .po-dash-bow-z {
        background: #8fd3e8;
    }

    .po-dashboard-excel .po-dash-pic-w,
    .po-dashboard-excel .po-dash-bow-w {
        background: #cc66cc;
    }

    .po-dashboard-excel .po-dash-pic-s,
    .po-dashboard-excel .po-dash-bow-s {
        background: #7dde8a;
    }

    .po-dashboard-excel .po-dash-pic-f,
    .po-dashboard-excel .po-dash-bow-f {
        background: #f4a97a;
    }

    .po-dashboard-excel .po-dash-pic-d,
    .po-dashboard-excel .po-dash-bow-d {
        background: #ffc000;
    }

    .po-dashboard-excel .po-dash-pic-su,
    .po-dashboard-excel .po-dash-bow-su {
        background: #f4f7bf;
    }

    .po-dashboard-excel .po-dash-pic-h,
    .po-dashboard-excel .po-dash-bow-h {
        background: #f8c9ae;
    }

    .po-dashboard-excel .po-dash-pic-we,
    .po-dashboard-excel .po-dash-bow-we {
        background: #f4c2a4;
    }

    .po-dashboard-excel .po-dash-pic-log,
    .po-dashboard-excel .po-dash-bow-log {
        background: #df92d8;
    }

    .po-dashboard-excel .po-dash-pic-t,
    .po-dashboard-excel .po-dash-bow-t {
        background: #e7a4df;
    }

    .po-dashboard-excel .po-dash-col-invoice,
    .po-dashboard-excel .po-dash-col-outs,
    .po-dashboard-excel .po-dash-col-ny-target {
        background: #d9ead3;
    }

    .po-dashboard-excel .po-dash-col-ny-total {
        background: #fff9b8;
    }

    .po-dashboard-excel .po-dash-grand {
        background: #fff;
        font-weight: 900;
    }

    .po-dashboard-excel .po-dash-negative,
    .po-dashboard-excel .po-dash-alert {
        background: #ff7070 !important;
        color: #000;
    }

    .po-dashboard-excel tfoot th {
        background: #00b050 !important;
        color: #000;
        font-weight: 900;
        border: 1px solid #000 !important;
    }

    #table_po_dashboard_excel_wrapper .dataTables_scrollBody table#table_po_dashboard_excel tfoot {
        display: none;
    }

    .po-compare-detail-link,
    .po-monitor-term-detail-link {
        color: #0056b3;
        font-weight: 800;
        line-height: 1;
        text-decoration: underline;
        white-space: nowrap;
    }

    #po_compare_detail_modal .modal-dialog {
        max-width: 78vw;
    }

    #po_compare_detail_modal .modal-body {
        max-height: 72vh;
        overflow: auto;
    }

    #po_compare_detail_modal .modal-content,
    #po_monitor_batch_invoice_modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
    }

    #po_compare_detail_modal .modal-header,
    #po_monitor_batch_invoice_modal .modal-header {
        align-items: flex-start;
        border: 0;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 34%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        color: #fff;
        padding: 1rem 1.15rem;
    }

    #po_compare_detail_modal .modal-title,
    #po_monitor_batch_invoice_modal .modal-title {
        color: #fff;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.35;
    }

    #po_compare_detail_modal .close,
    #po_monitor_batch_invoice_modal .close {
        color: #fff;
        opacity: 0.92;
        text-shadow: none;
    }

    .po-monitor-modal-eyebrow {
        display: block;
        margin-bottom: 0.25rem;
        color: rgba(226, 232, 240, 0.78);
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .po-monitor-modal-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .po-monitor-modal-stat {
        padding: 0.78rem 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-top: 4px solid #2563eb;
        border-radius: 8px;
        background: #f8fafc;
    }

    .po-monitor-modal-stat--green {
        border-top-color: #16a34a;
    }

    .po-monitor-modal-stat--amber {
        border-top-color: #f59e0b;
    }

    .po-monitor-modal-stat__label {
        display: block;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-monitor-modal-stat__value {
        display: block;
        margin-top: 0.25rem;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .po-monitor-detail-table {
        border-collapse: collapse !important;
        font-size: 12px;
    }

    .po-monitor-detail-table th {
        background: #e2e8f0;
        color: #0f172a;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .po-monitor-detail-table th,
    .po-monitor-detail-table td {
        padding: 5px 8px !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .po-monitor-detail-table tfoot th {
        background: #0bb35f !important;
        color: #000;
        font-weight: 900;
    }

    .po-monitor-batch-toolbar {
        display: grid;
        grid-template-columns: minmax(180px, 1.4fr) 120px minmax(160px, 1fr) auto;
        gap: 10px;
        align-items: end;
    }

    .po-monitor-batch-paste {
        min-height: 116px;
        font-family: Consolas, monospace;
        font-size: 0.86rem;
    }

    .po-monitor-batch-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .po-monitor-batch-summary-card {
        min-height: 82px;
        padding: 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 8px;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
    }

    .po-monitor-batch-summary-card--total {
        grid-column: 1 / -1;
        min-height: 68px;
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        border-color: rgba(37, 99, 235, 0.22);
    }

    .po-monitor-batch-summary-card__label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-monitor-batch-summary-card__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 0.42rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .po-monitor-batch-summary-card__value {
        display: block;
        margin-top: 0.65rem;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    #po-monitor-batch-invoice-table th,
    #po-monitor-batch-invoice-table td {
        vertical-align: middle;
    }

    .po-monitor-revamp {
        --po-monitor-ink: #0f172a;
        --po-monitor-muted: #64748b;
        --po-monitor-line: rgba(148, 163, 184, 0.22);
        --po-monitor-surface: rgba(255, 255, 255, 0.96);
        --po-monitor-soft: rgba(248, 250, 252, 0.94);
        --po-monitor-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        --po-monitor-blue: #2563eb;
        --po-monitor-green: #16a34a;
        --po-monitor-amber: #f59e0b;
        --po-monitor-slate: #475569;
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }

    .po-monitor-revamp > .content-header {
        display: none;
    }

    .po-monitor-shell {
        padding: 1rem;
    }

    .po-monitor-hero {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.18);
        color: #f8fafc;
    }

    .po-monitor-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.2rem;
        padding: 1.25rem;
    }

    .po-monitor-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .po-monitor-hero h1 {
        margin: 0.9rem 0 0.55rem;
        color: #fff;
        font-size: 1.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .po-monitor-hero p {
        max-width: 48rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.9);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .po-monitor-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.05rem;
    }

    .po-monitor-hero__stats,
    .po-monitor-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .po-monitor-hero-stat {
        min-height: 90px;
        padding: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.11);
        backdrop-filter: blur(8px);
    }

    .po-monitor-hero-stat__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-monitor-hero-stat__value {
        display: block;
        margin-top: 0.3rem;
        color: #fff;
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1;
        overflow-wrap: anywhere;
    }

    .po-monitor-hero-stat__hint {
        display: block;
        margin-top: 0.5rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .po-monitor-panel {
        margin-top: 1rem;
        border: 1px solid var(--po-monitor-line);
        border-radius: 12px;
        background: var(--po-monitor-surface);
        box-shadow: var(--po-monitor-shadow);
        overflow: hidden;
    }

    .po-monitor-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.05rem 1.15rem 0;
    }

    .po-monitor-panel__title {
        margin: 0;
        color: var(--po-monitor-ink);
        font-size: 1rem;
        font-weight: 900;
    }

    .po-monitor-panel__subtitle {
        margin: 0.25rem 0 0;
        color: var(--po-monitor-muted);
        font-size: 0.86rem;
    }

    .po-monitor-panel__body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .po-monitor-filter-grid,
    .po-monitor-import-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        align-items: end;
    }

    .po-monitor-import-grid {
        grid-template-columns: minmax(220px, 1fr) minmax(260px, 1.2fr) 150px;
    }

    .po-monitor-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.55rem;
        margin-top: 0.95rem;
    }

    .po-monitor-field label {
        color: #334155;
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-monitor-kpi-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 1rem;
        gap: 0.9rem;
    }

    .po-monitor-kpi-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 104px;
        padding: 1.05rem 1.1rem;
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-top: 5px solid var(--po-monitor-blue);
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.92));
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .po-monitor-kpi-card--green {
        border-color: rgba(187, 247, 208, 0.95);
        border-top-color: var(--po-monitor-green);
    }

    .po-monitor-kpi-card--amber {
        border-color: rgba(253, 230, 138, 0.95);
        border-top-color: var(--po-monitor-amber);
    }

    .po-monitor-kpi-card--slate {
        border-color: rgba(203, 213, 225, 0.95);
        border-top-color: var(--po-monitor-slate);
    }

    .po-monitor-kpi-card__icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 58px;
        border-radius: 6px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        font-size: 1.45rem;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
    }

    .po-monitor-kpi-card--green .po-monitor-kpi-card__icon {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 12px 24px rgba(22, 163, 74, 0.22);
    }

    .po-monitor-kpi-card--amber .po-monitor-kpi-card__icon {
        background: linear-gradient(135deg, #fbbf24, #d97706);
        box-shadow: 0 12px 24px rgba(217, 119, 6, 0.18);
    }

    .po-monitor-kpi-card--slate .po-monitor-kpi-card__icon {
        background: linear-gradient(135deg, #64748b, #334155);
        box-shadow: 0 12px 24px rgba(51, 65, 85, 0.16);
    }

    .po-monitor-kpi-card__label {
        display: block;
        margin-bottom: 0.45rem;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-monitor-kpi-card__value {
        display: block;
        color: var(--po-monitor-ink);
        font-size: 1.18rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .po-monitor-table-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.55rem;
    }

    @media (max-width: 1199.98px) {
        .po-monitor-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .po-monitor-hero__grid,
        .po-monitor-filter-grid,
        .po-monitor-import-grid,
        .po-monitor-batch-toolbar {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .po-monitor-shell {
            padding: 0.75rem;
        }

        .po-monitor-hero__grid {
            padding: 1rem;
        }

        .po-monitor-hero__stats,
        .po-monitor-kpi-grid,
        .po-monitor-batch-summary {
            grid-template-columns: 1fr;
        }

        #po_compare_detail_modal .modal-dialog {
            max-width: calc(100vw - 1rem);
        }
    }
</style>

<div class="content-wrapper po-monitor-revamp">
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
            <div class="container-fluid po-monitor-shell">
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
                if (empty($uniqueBowheer)) {
                    foreach (($dashboardSummary['rows'] ?? []) as $dashboardRow) {
                        if (!empty($dashboardRow['bowheer'])) {
                            $uniqueBowheer[$dashboardRow['bowheer']] = $dashboardRow['bowheer'];
                        }
                    }
                }
                ksort($uniqueBowheer);

                $dashboardTotals = $dashboardSummary['totals'];
                $dashboardDoneInvoice = (float) ($dashboardTotals['done_inv_2026'] ?? 0);
                $dashboardOutsOnTarget = (float) ($dashboardTotals['outs_2026_on_target'] ?? 0);
                $dashboardNyPoTarget = (float) ($dashboardTotals['ny_po_on_target_2026'] ?? 0);
                $dashboardInitialCombinedTargetInvoice = (float) ($dashboardInitialTotals['done_outs_ny_2026'] ?? ($dashboardDoneInvoice + $dashboardOutsOnTarget + $dashboardNyPoTarget));

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

                <div class="po-monitor-hero">
                    <div class="po-monitor-hero__grid">
                        <div>
                            <span class="po-monitor-hero__eyebrow">
                                <i class="fas fa-chart-line"></i>
                                PO Monitoring
                            </span>
                            <h1>Dashboard Target PO</h1>
                            <p>Monitor target invoice 2026, outstanding by PO, NY PO, dan realisasi invoice per project dalam satu layar operasional.</p>
                            <div class="po-monitor-hero__actions">
                                <a href="<?= site_url('PO_Monitor/create') ?>" class="btn btn-light btn-sm font-weight-bold">
                                    <i class="fas fa-plus mr-1"></i> Tambah PO
                                </a>
                                <button type="button" class="btn btn-light btn-sm font-weight-bold" data-toggle="modal" data-target="#po_monitor_batch_invoice_modal">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Batch Input Invoice Termin
                                </button>
                                <a href="#table_po_dashboard_excel" class="btn btn-outline-light btn-sm font-weight-bold">
                                    <i class="fas fa-table mr-1"></i> Dashboard
                                </a>
                                <a href="#table_po_target_invoice_compare_month" class="btn btn-outline-light btn-sm font-weight-bold">
                                    <i class="fas fa-balance-scale mr-1"></i> Perbandingan
                                </a>
                            </div>
                        </div>
                        <div class="po-monitor-hero__stats">
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Target Invoice 2026</span>
                                <span class="po-monitor-hero-stat__value"><?= number_format($dashboardInitialCombinedTargetInvoice, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Data awal target invoice</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Done Invoice</span>
                                <span class="po-monitor-hero-stat__value"><?= number_format($dashboardDoneInvoice, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Invoice selesai di 2026</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Outs By PO</span>
                                <span class="po-monitor-hero-stat__value"><?= number_format($dashboardOutsOnTarget, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Outstanding target berjalan</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Outstanding NY PO</span>
                                <span class="po-monitor-hero-stat__value"><?= number_format($dashboardNyPoTarget, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Estimasi target tanpa PO</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($error_log): ?>
                    <div class="alert alert-<?= $status ? 'success' : 'danger' ?> alert-dismissible fade show">
                        <?= htmlspecialchars($error_log) ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (false): ?>
                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Filter Data</h3>
                            <p class="po-monitor-panel__subtitle">Saring data PO Monitor berdasarkan project dan status SLA.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body">
                        <form method="get" action="<?= site_url('PO_Monitor') ?>">
                            <div class="po-monitor-filter-grid">
                                <div class="po-monitor-field">
                                    <label>Project / Bowheer</label>
                                    <select id="filter_bowheer_up" name="bowheer[]" class="select2" multiple="multiple" data-placeholder="Pilih bowheer" style="width: 100%;">
                                        <?php foreach ($uniqueBowheer as $bowheerName): ?>
                                            <option value="<?= htmlspecialchars($bowheerName) ?>" <?= in_array($bowheerName, $selectedBowheer ?? [], true) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($bowheerName) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="po-monitor-field">
                                    <label>SLA Status</label>
                                    <select id="filter_sla_up" name="sla[]" class="select2" multiple="multiple" data-placeholder="Pilih SLA status" style="width: 100%;">
                                        <option value="AMAN" <?= in_array('AMAN', $selectedSla ?? [], true) ? 'selected' : '' ?>>AMAN</option>
                                        <option value="WARNING" <?= in_array('WARNING', $selectedSla ?? [], true) ? 'selected' : '' ?>>WARNING</option>
                                        <option value="OVERDUE" <?= in_array('OVERDUE', $selectedSla ?? [], true) ? 'selected' : '' ?>>OVERDUE</option>
                                    </select>
                                </div>
                            </div>
                            <div class="po-monitor-actions">
                                <a href="<?= site_url('PO_Monitor') ?>" id="reset_filter_po_monitor" class="btn btn-danger">Delete</a>
                                <button type="submit" id="btnFilterPOMonitor" class="btn btn-primary">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Import Database PO CSV</h3>
                            <p class="po-monitor-panel__subtitle">Import ulang data master PO Monitor standalone dari CSV.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body">
                        <form method="post" action="<?= site_url('PO_Monitor/import_csv') ?>" enctype="multipart/form-data">
                            <div class="po-monitor-import-grid">
                                <div class="po-monitor-field">
                                    <label>File CSV</label>
                                    <input type="file" name="file_csv" class="form-control" accept=".csv,text/csv">
                                </div>
                                <?php if (!empty($isLocalAccess)): ?>
                                    <div class="po-monitor-field">
                                        <label>Server Path</label>
                                        <input type="text" name="server_path" class="form-control" value="D:\ZEYN\DATABASE PO TKM\DATABASE PO CSV - ZEYN - PT. TKM.csv">
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block"><?= !empty($isLocalAccess) ? 'Import' : 'Import CSV' ?></button>
                                </div>
                            </div>
                        </form>
                        <?php if (!empty($isLocalAccess)): ?>
                            <div class="border-top mt-3 pt-3 d-flex flex-wrap align-items-center justify-content-between">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Hapus semua data PO Monitor standalone. Data PO_MyRep tidak ikut terhapus.
                                </div>
                                <form method="post" action="<?= site_url('PO_Monitor/purge_all') ?>" class="mb-0 js-po-purge-form">
                                    <input type="hidden" name="confirm_delete" value="">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Data PO
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="po-monitor-kpi-grid">
                    <div class="po-monitor-kpi-card">
                        <div class="po-monitor-kpi-card__icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <span class="po-monitor-kpi-card__label">Target Invoice 2026</span>
                            <span class="po-monitor-kpi-card__value" id="summary_total_po"><?= number_format($dashboardInitialCombinedTargetInvoice, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="po-monitor-kpi-card po-monitor-kpi-card--green">
                        <div class="po-monitor-kpi-card__icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <span class="po-monitor-kpi-card__label">Done Invoice 2026</span>
                            <span class="po-monitor-kpi-card__value" id="summary_done_invoice"><?= number_format($dashboardDoneInvoice, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="po-monitor-kpi-card po-monitor-kpi-card--amber">
                        <div class="po-monitor-kpi-card__icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <span class="po-monitor-kpi-card__label">Outstanding By PO</span>
                            <span class="po-monitor-kpi-card__value" id="summary_target_week"><?= number_format($dashboardOutsOnTarget, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div class="po-monitor-kpi-card po-monitor-kpi-card--slate">
                        <div class="po-monitor-kpi-card__icon">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div>
                            <span class="po-monitor-kpi-card__label">Outstanding NY PO</span>
                            <span class="po-monitor-kpi-card__value" id="summary_carry_over"><?= number_format($dashboardNyPoTarget, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Dashboard Target PO</h3>
                            <p class="po-monitor-panel__subtitle">Ringkasan target dan outstanding dengan warna mengikuti dashboard Excel.</p>
                        </div>
                        <div class="po-monitor-table-actions">
                            <label class="po-monitor-switch mb-0">
                                <input type="checkbox" id="dashboard_initial_toggle">
                                <span class="po-monitor-switch-slider"></span>
                                <span>Data awal</span>
                            </label>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body table-responsive">
                        <table id="table_po_dashboard_excel" class="table table-bordered table-sm po-dashboard-excel">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>PIC</th>
                                    <th>Bowheer</th>
                                    <th>PO 2026</th>
                                    <th>Done Inv 2026</th>
                                    <th class="po-dash-head-target">Outs 2026<br>On Target</th>
                                    <th>NY PO<br>On Target 2026</th>
                                    <th>Grandtotal<br>Target</th>
                                    <th class="po-dash-head-ny">NY PO Total</th>
                                    <th>CO To 2027</th>
                                    <th>Total Outs</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <th class="text-center">TOTAL</th>
                                    <th></th>
                                    <th></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['all_po'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['done_inv_2026'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['outs_2026_on_target'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['ny_po_on_target_2026'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['grandtotal_target'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['ny_po_total'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['co_to_2027'] ?? 0), 0, ',', '.') ?></th>
                                    <th class="text-right"><?= number_format((float) ($dashboardTotals['total_outs'] ?? 0), 0, ',', '.') ?></th>
                                </tr>
                                <tr class="font-weight-bold po-dash-focus-total">
                                    <th class="text-center" colspan="4">DONE INV 2026 + OUTS 2026 ON TARGET + NY PO ON TARGET 2026</th>
                                    <th class="text-right" colspan="3"><?= number_format((float) ($dashboardTotals['done_inv_2026'] ?? 0) + (float) ($dashboardTotals['outs_2026_on_target'] ?? 0) + (float) ($dashboardTotals['ny_po_on_target_2026'] ?? 0), 0, ',', '.') ?></th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Perbandingan Target dan Invoice</h3>
                            <p class="po-monitor-panel__subtitle">Bandingkan target paten dan realisasi invoice per bulan atau per week.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body">
                        <form method="get" action="<?= site_url('PO_Monitor') ?>" class="mb-3">
                            <div class="po-monitor-import-grid">
                                <div class="po-monitor-field">
                                    <label>From Month</label>
                                    <input type="month" name="from_month" class="form-control" value="<?= htmlspecialchars($comparisonMatrix['from']) ?>">
                                </div>
                                <div class="po-monitor-field">
                                    <label>To Month</label>
                                    <input type="month" name="to_month" class="form-control" value="<?= htmlspecialchars($comparisonMatrix['to']) ?>">
                                </div>
                                <div class="po-monitor-field">
                                    <label>Options</label>
                                    <div class="po-monitor-switch-row">
                                        <label class="po-monitor-switch mb-0">
                                            <input type="checkbox" id="po_compare_data_only" value="1">
                                            <span class="po-monitor-switch-slider"></span>
                                            <span>Target / Invoice only</span>
                                        </label>
                                        <label class="po-monitor-switch mb-0">
                                            <input type="checkbox" id="po_compare_week_mode" value="1">
                                            <span class="po-monitor-switch-slider"></span>
                                            <span>Weeks column</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="po-monitor-actions">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="<?= site_url('PO_Monitor') ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>

                        <div id="po_compare_month_panel" class="table-responsive">
                            <table id="table_po_target_invoice_compare_month" class="table table-bordered table-striped po-compare-table">
                                <thead>
                                    <?php if ($comparisonIsWeek): ?>
                                        <tr class="po-compare-month">
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">No</th>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                                            <?php foreach ($comparisonWeekMonthGroups as $group): ?>
                                                <th colspan="<?= (int) $group['count'] * 3 ?>" class="po-compare-month-cell"><?= htmlspecialchars($group['label']) ?></th>
                                            <?php endforeach; ?>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi</th>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                                            <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
                                        </tr>
                                        <tr>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <th colspan="3" class="po-compare-week-cell">
                                                    <?= htmlspecialchars($month['label']) ?><br>
                                                    <small><?= htmlspecialchars($month['period']) ?></small>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <th class="po-compare-target">Target</th>
                                                <th class="po-compare-achieved">Achieved</th>
                                                <th class="po-compare-percent">%</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php else: ?>
                                        <tr class="po-compare-month">
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-left">No</th>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <th colspan="3" class="po-compare-month-cell">
                                                    <?= htmlspecialchars($month['label']) ?><br>
                                                    <small><?= htmlspecialchars($month['year']) ?></small>
                                                </th>
                                            <?php endforeach; ?>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi</th>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                                            <th rowspan="2" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
                                        </tr>
                                        <tr>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <th class="po-compare-target">Target</th>
                                                <th class="po-compare-achieved">Achieved</th>
                                                <th class="po-compare-percent">%</th>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endif; ?>
                                </thead>
                                <tbody>
                                    <?php $compareNo = 1; foreach ($comparisonMatrix['rows'] as $row): ?>
                                        <tr data-achieved="<?= (float) $row['total_achieved'] ?>" data-target="<?= (float) $row['total_target'] ?>">
                                            <td><?= $compareNo++ ?></td>
                                            <td><?= htmlspecialchars($row['project']) ?></td>
                                            <td><?= number_format((float) $row['total_target'], 0, ',', '.') ?></td>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <?php $monthData = $row['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                                                <td><?= po_monitor_compare_amount_link($monthData['target'], $row['id_bowheer'], $month['key'], 'month', 'target') ?></td>
                                                <td><?= po_monitor_compare_amount_link($monthData['achieved'], $row['id_bowheer'], $month['key'], 'month', 'achieved') ?></td>
                                                <td><?= ((float) $monthData['target'] > 0 || (float) $monthData['achieved'] > 0) ? po_monitor_percent($monthData['percent']) : '-' ?></td>
                                            <?php endforeach; ?>
                                            <td><?= number_format((float) $row['total_achieved'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['deviasi'], 0, ',', '.') ?></td>
                                            <td><?= po_monitor_percent($row['achieved_percent']) ?></td>
                                            <td><?= po_monitor_percent($row['deviasi_percent']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th><?= number_format((float) $comparisonMatrix['totals']['total_target'], 0, ',', '.') ?></th>
                                        <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                            <?php $monthTotal = $comparisonMatrix['totals']['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                                            <th><?= number_format((float) $monthTotal['target'], 0, ',', '.') ?></th>
                                            <th><?= number_format((float) $monthTotal['achieved'], 0, ',', '.') ?></th>
                                            <th><?= po_monitor_percent($monthTotal['percent']) ?></th>
                                        <?php endforeach; ?>
                                        <th><?= number_format((float) $comparisonMatrix['totals']['total_achieved'], 0, ',', '.') ?></th>
                                        <th><?= number_format((float) $comparisonMatrix['totals']['deviasi'], 0, ',', '.') ?></th>
                                        <th><?= po_monitor_percent($comparisonMatrix['totals']['achieved_percent']) ?></th>
                                        <th><?= po_monitor_percent($comparisonMatrix['totals']['deviasi_percent']) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="po_compare_week_panel" class="table-responsive po-compare-panel-hidden">
                            <table id="table_po_target_invoice_compare_week" class="table table-bordered table-striped po-compare-table">
                                <thead>
                                    <tr class="po-compare-month">
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-left">No</th>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-left" style="min-width: 220px;">Project</th>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Target</th>
                                        <?php foreach ($comparisonWeekMonthGroups as $group): ?>
                                            <th colspan="<?= (int) $group['count'] * 3 ?>" class="po-compare-month-cell"><?= htmlspecialchars($group['label']) ?></th>
                                        <?php endforeach; ?>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Total Achieved</th>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi</th>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Achieved (%)</th>
                                        <th rowspan="3" class="po-compare-fixed po-compare-fixed-total">Deviasi (%)</th>
                                    </tr>
                                    <tr>
                                        <?php foreach ($comparisonWeekMatrix['months'] as $month): ?>
                                            <th colspan="3" class="po-compare-week-cell">
                                                <?= htmlspecialchars($month['label']) ?><br>
                                                <small><?= htmlspecialchars($month['period'] ?? '') ?></small>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <?php foreach ($comparisonWeekMatrix['months'] as $month): ?>
                                            <th class="po-compare-target">Target</th>
                                            <th class="po-compare-achieved">Achieved</th>
                                            <th class="po-compare-percent">%</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $compareWeekNo = 1; foreach ($comparisonWeekMatrix['rows'] as $row): ?>
                                        <tr data-achieved="<?= (float) $row['total_achieved'] ?>" data-target="<?= (float) $row['total_target'] ?>">
                                            <td><?= $compareWeekNo++ ?></td>
                                            <td><?= htmlspecialchars($row['project']) ?></td>
                                            <td><?= number_format((float) $row['total_target'], 0, ',', '.') ?></td>
                                            <?php foreach ($comparisonWeekMatrix['months'] as $month): ?>
                                                <?php $monthData = $row['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                                                <td><?= po_monitor_compare_amount_link($monthData['target'], $row['id_bowheer'], $month['key'], 'week', 'target') ?></td>
                                                <td><?= po_monitor_compare_amount_link($monthData['achieved'], $row['id_bowheer'], $month['key'], 'week', 'achieved') ?></td>
                                                <td><?= ((float) $monthData['target'] > 0 || (float) $monthData['achieved'] > 0) ? po_monitor_percent($monthData['percent']) : '-' ?></td>
                                            <?php endforeach; ?>
                                            <td><?= number_format((float) $row['total_achieved'], 0, ',', '.') ?></td>
                                            <td><?= number_format((float) $row['deviasi'], 0, ',', '.') ?></td>
                                            <td><?= po_monitor_percent($row['achieved_percent']) ?></td>
                                            <td><?= po_monitor_percent($row['deviasi_percent']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th><?= number_format((float) $comparisonWeekMatrix['totals']['total_target'], 0, ',', '.') ?></th>
                                        <?php foreach ($comparisonWeekMatrix['months'] as $month): ?>
                                            <?php $monthTotal = $comparisonWeekMatrix['totals']['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                                            <th><?= number_format((float) $monthTotal['target'], 0, ',', '.') ?></th>
                                            <th><?= number_format((float) $monthTotal['achieved'], 0, ',', '.') ?></th>
                                            <th><?= po_monitor_percent($monthTotal['percent']) ?></th>
                                        <?php endforeach; ?>
                                        <th><?= number_format((float) $comparisonWeekMatrix['totals']['total_achieved'], 0, ',', '.') ?></th>
                                        <th><?= number_format((float) $comparisonWeekMatrix['totals']['deviasi'], 0, ',', '.') ?></th>
                                        <th><?= po_monitor_percent($comparisonWeekMatrix['totals']['achieved_percent']) ?></th>
                                        <th><?= po_monitor_percent($comparisonWeekMatrix['totals']['deviasi_percent']) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">List Bowheer dan Tagihan Term</h3>
                            <p class="po-monitor-panel__subtitle">Breakdown outstanding term per bowheer.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body table-responsive">
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
                                            <td><?= po_monitor_term_amount_link($totalPo, $bowheer['id_bowheer'], 'total_po') ?></td>
                                            <td><?= po_monitor_term_amount_link($termDone, $bowheer['id_bowheer'], 'term_done') ?></td>
                                            <?php for ($termIndex = 1; $termIndex <= 5; $termIndex++): ?>
                                                <?php $termValue = isset($termRemainingMap[$termIndex]) ? $termRemainingMap[$termIndex] : 0; ?>
                                                <td class="text-center">
                                                    <?= po_monitor_term_amount_link($termValue, $bowheer['id_bowheer'], 'term_remaining', $termIndex) ?>
                                                </td>
                                            <?php endfor; ?>
                                            <td><?= po_monitor_term_amount_link($outstandingTerm, $bowheer['id_bowheer'], 'outstanding_term') ?></td>
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
                                                                        <td><?= po_monitor_term_amount_link($term['term_value'], $bowheer['id_bowheer'], 'term_value', $term['term_index']) ?></td>
                                                                        <td><?= po_monitor_term_amount_link($term['invoiced_amount'], $bowheer['id_bowheer'], 'term_done', $term['term_index']) ?></td>
                                                                        <td><?= po_monitor_term_amount_link($term['remaining'], $bowheer['id_bowheer'], 'term_remaining', $term['term_index']) ?></td>
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

                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">List PO Monitor</h3>
                            <p class="po-monitor-panel__subtitle">Daftar PO standalone yang digunakan halaman PO Monitor.</p>
                        </div>
                        <div class="po-monitor-table-actions">
                            <a href="<?= site_url('PO_Monitor/create') ?>" class="btn btn-success">Tambah PO</a>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body table-responsive">
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
                            <tbody></tbody>
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

<div class="modal fade" id="po_compare_detail_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span class="po-monitor-modal-eyebrow">Detail</span>Detail PO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="po_monitor_batch_invoice_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('PO_Monitor/batch_invoice_termin') ?>" id="po-monitor-batch-invoice-form">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="po-monitor-modal-eyebrow">Batch Claim</span>Batch Input Invoice Termin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="po-monitor-filter-grid mb-3">
                        <div class="po-monitor-field">
                            <label>Tanggal Invoice General</label>
                            <input type="date" name="invoice_date" id="po-monitor-batch-invoice-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <datalist id="po-monitor-batch-po-options">
                        <?php
                        $poMonitorBatchSeen = [];
                        foreach ($batchInvoiceRows as $poMonitorBatchRow):
                            $poMonitorBatchNumber = trim((string) ($poMonitorBatchRow['po_number'] ?? ''));
                            if ($poMonitorBatchNumber === '' || isset($poMonitorBatchSeen[strtoupper($poMonitorBatchNumber)])) {
                                continue;
                            }
                            $poMonitorBatchSeen[strtoupper($poMonitorBatchNumber)] = true;
                        ?>
                            <option value="<?= htmlspecialchars($poMonitorBatchNumber, ENT_QUOTES) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>

                    <ul class="nav nav-tabs" id="po-monitor-batch-invoice-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="po-monitor-batch-manual-tab" data-toggle="pill" href="#po-monitor-batch-manual-pane" role="tab">Input Manual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="po-monitor-batch-paste-tab" data-toggle="pill" href="#po-monitor-batch-paste-pane" role="tab">Paste dari Excel</a>
                        </li>
                    </ul>

                    <div class="tab-content border-left border-right border-bottom p-3 mb-3">
                        <div class="tab-pane fade show active" id="po-monitor-batch-manual-pane" role="tabpanel">
                            <div class="po-monitor-batch-toolbar">
                                <div>
                                    <label class="mb-1">Nomor PO</label>
                                    <input type="text" id="po-monitor-batch-po-number" class="form-control" list="po-monitor-batch-po-options" placeholder="Pilih / ketik nomor PO">
                                </div>
                                <div>
                                    <label class="mb-1">Term</label>
                                    <select id="po-monitor-batch-term-no" class="form-control">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1">Nilai Invoice</label>
                                    <input type="text" id="po-monitor-batch-invoice-value" class="form-control" placeholder="Kosong = sisa term">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-outline-primary" id="po-monitor-batch-add-row">Tambah Row</button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="po-monitor-batch-paste-pane" role="tabpanel">
                            <div class="form-group">
                                <label>Data Invoice</label>
                                <textarea id="po-monitor-batch-paste" class="form-control po-monitor-batch-paste" placeholder="PO Number[TAB]Term[TAB]Nilai Invoice&#10;PO. 8000138637[TAB]1[TAB]1000000&#10;PO. 8000138638[TAB]Term 2"></textarea>
                                <small class="form-text text-muted">Nilai invoice boleh kosong untuk memakai sisa term.</small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                <button type="button" class="btn btn-outline-secondary" id="po-monitor-batch-parse-paste">Cek PO</button>
                                <button type="button" class="btn btn-outline-danger" id="po-monitor-batch-clear-list" disabled>Hapus List</button>
                            </div>
                        </div>
                    </div>

                    <div class="po-monitor-batch-summary">
                        <?php for ($poMonitorBatchTerm = 1; $poMonitorBatchTerm <= 5; $poMonitorBatchTerm++): ?>
                            <div class="po-monitor-batch-summary-card">
                                <span class="po-monitor-batch-summary-card__label">
                                    Termin <?= $poMonitorBatchTerm ?>
                                    <span class="po-monitor-batch-summary-card__count" id="po-monitor-batch-summary-count-<?= $poMonitorBatchTerm ?>">0</span>
                                </span>
                                <span class="po-monitor-batch-summary-card__value" id="po-monitor-batch-summary-term-<?= $poMonitorBatchTerm ?>">0</span>
                            </div>
                        <?php endfor; ?>
                        <div class="po-monitor-batch-summary-card po-monitor-batch-summary-card--total">
                            <span class="po-monitor-batch-summary-card__label">
                                Total Invoice
                                <span class="po-monitor-batch-summary-card__count" id="po-monitor-batch-summary-total-count">0</span>
                            </span>
                            <span class="po-monitor-batch-summary-card__value" id="po-monitor-batch-summary-total-value">0</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="po-monitor-batch-invoice-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:60px;">No</th>
                                    <th>Nomor PO</th>
                                    <th style="width:90px;">Term</th>
                                    <th style="width:160px;">Nilai Invoice</th>
                                    <th style="width:150px;">Sisa Term</th>
                                    <th style="width:190px;">Status</th>
                                    <th style="width:80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="po-monitor-batch-empty-row">
                                    <td colspan="7" class="text-center text-muted">Belum ada row invoice.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="text-muted small">Submit akan membuat claim invoice manual memakai tanggal invoice general.</div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-dark" id="po-monitor-batch-submit" disabled>Simpan Batch Invoice</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        var poMonitorBatchTerminLookup = <?php
            $poMonitorBatchLookup = [];
            foreach ($batchInvoiceRows as $poMonitorBatchRow) {
                $poNumber = trim((string) ($poMonitorBatchRow['po_number'] ?? ''));
                $termNo = (int) ($poMonitorBatchRow['term_index'] ?? 0);
                if ($poNumber === '' || $termNo <= 0) {
                    continue;
                }
                $poKey = strtoupper($poNumber);
                if (!isset($poMonitorBatchLookup[$poKey])) {
                    $poMonitorBatchLookup[$poKey] = [
                        'po_number' => $poNumber,
                        'nama_bowheer' => (string) ($poMonitorBatchRow['nama_bowheer'] ?? ''),
                        'terms' => []
                    ];
                }
                $poMonitorBatchLookup[$poKey]['terms'][$termNo] = [
                    'id_term' => (int) ($poMonitorBatchRow['id_term'] ?? 0),
                    'term_value' => (float) ($poMonitorBatchRow['term_value'] ?? 0),
                    'invoiced_amount' => (float) ($poMonitorBatchRow['invoiced_amount'] ?? 0),
                    'remaining' => (float) ($poMonitorBatchRow['remaining'] ?? 0),
                    'invoice_date' => (string) ($poMonitorBatchRow['invoice_date'] ?? '')
                ];
            }
            echo json_encode($poMonitorBatchLookup, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        ?>;

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

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
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

        function initServerSideTable($, selector, ajaxUrl, orderConfig, pageLength) {
            if (!$.fn.DataTable || !$(selector).length || $.fn.DataTable.isDataTable(selector)) {
                return;
            }

            function updateDashboardFooter(json) {
                if (!json || !json.filteredTotals) {
                    return;
                }

                var totals = json.filteredTotals;
                var values = [
                    totals.all_po || '-',
                    totals.done_inv_2026 || '-',
                    totals.outs_2026_on_target || '-',
                    totals.ny_po_on_target_2026 || '-',
                    totals.grandtotal_target || '-',
                    totals.ny_po_total || '-',
                    totals.co_to_2027 || '-',
                    totals.total_outs || '-'
                ];

                var $wrapper = $(selector).closest('.dataTables_wrapper');
                var $footers = $(selector).find('tfoot');
                if ($wrapper.length) {
                    $footers = $footers.add($wrapper.find('.dataTables_scrollFoot tfoot'));
                }

                $footers.each(function() {
                    var $rows = $(this).find('tr');
                    var $cells = $rows.eq(0).find('th');
                    $cells.eq(0).html('TOTAL');
                    $cells.eq(1).html('');
                    $cells.eq(2).html('');
                    for (var i = 0; i < values.length; i++) {
                        $cells.eq(i + 3).html(values[i]);
                    }

                    var $focusCells = $rows.eq(1).find('th');
                    if ($focusCells.length >= 2) {
                        $focusCells.eq(1).html(totals.done_outs_ny_2026 || '-');
                    }
                });
            }

            var columnDefs = [];
            if (selector === '#table_po_dashboard_excel') {
                $(selector)
                    .off('xhr.dt.poDashboardFooter')
                    .on('xhr.dt.poDashboardFooter', function(e, settings, json) {
                        window.setTimeout(function() {
                            updateDashboardFooter(json);
                        }, 0);
                    });

                columnDefs.push({
                    targets: '_all',
                    createdCell: function(td) {
                        var $marker = $(td).find('[class*="po-dash-"]').first();
                        if ($marker.length) {
                            var classes = String($marker.attr('class') || '').split(/\s+/).filter(function(item) {
                                return item.indexOf('po-dash-') === 0;
                            });
                            if (classes.length) {
                                $(td).addClass(classes.join(' '));
                            }
                        }
                    }
                });
            }

            var latestJson = null;
            var tableOptions = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: ajaxUrl,
                    type: 'POST',
                    data: function(d) {
                        if (selector === '#table_po_dashboard_excel') {
                            d.dashboard_mode = $('#dashboard_initial_toggle').is(':checked') ? 'initial' : 'current';
                        }
                    },
                    dataSrc: function(json) {
                        latestJson = json;
                        if (selector === '#table_po_dashboard_excel') {
                            updateDashboardFooter(json);
                        }
                        return json.data || [];
                    }
                },
                paging: true,
                pageLength: pageLength || 25,
                searching: true,
                info: true,
                lengthChange: true,
                autoWidth: false,
                responsive: false,
                scrollX: selector !== '#table_po_dashboard_excel',
                ordering: true,
                order: orderConfig || [],
                columnDefs: columnDefs
            };

            if (selector === '#table_po_dashboard_excel') {
                tableOptions.drawCallback = function() {
                    updateDashboardFooter(latestJson);
                };
            }

            $(selector).DataTable(tableOptions);
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

            $('.js-po-purge-form').off('submit.poPurge').on('submit.poPurge', function(e) {
                var confirmText = window.prompt('Ketik HAPUS PO untuk menghapus semua data PO Monitor. Data PO_MyRep tidak akan terpengaruh.');
                if (confirmText !== 'HAPUS PO') {
                    e.preventDefault();
                    return false;
                }
                $(this).find('input[name="confirm_delete"]').val(confirmText);
                return true;
            });

            $('#dashboard_initial_toggle').off('change.poDashboardMode').on('change.poDashboardMode', function() {
                var table = $('#table_po_dashboard_excel').DataTable();
                table.ajax.reload(null, false);
            });

            function normalizeBatchTerm(value) {
                var match = String(value || '').match(/\d+/);
                return match ? parseInt(match[0], 10) : 0;
            }

            function getBatchCurrentUsed(key) {
                var used = 0;
                $('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row[data-valid="1"]').each(function() {
                    if (String($(this).data('key') || '') === key) {
                        used += Number($(this).data('invoice-value') || 0);
                    }
                });
                return used;
            }

            function getBatchInvoiceCheck(poNumber, termNo, invoiceValue) {
                var poKey = String(poNumber || '').trim().toUpperCase();
                var lookup = poMonitorBatchTerminLookup[poKey] || null;
                var amount = parseLocaleNumber(invoiceValue);

                if (!lookup) {
                    return { valid: false, code: 'invalid', label: 'PO tidak ditemukan', amount: amount, remaining: 0, poNumber: String(poNumber || '').trim() };
                }

                var term = lookup.terms && lookup.terms[String(termNo)] ? lookup.terms[String(termNo)] : null;
                if (!term) {
                    return { valid: false, code: 'invalid', label: 'Term tidak ditemukan', amount: amount, remaining: 0, poNumber: lookup.po_number };
                }

                var key = poKey + '|' + termNo;
                var remaining = Number(term.remaining || 0) - getBatchCurrentUsed(key);
                if (!invoiceValue || String(invoiceValue).trim() === '') {
                    amount = remaining;
                }

                if (remaining <= 0) {
                    return { valid: false, code: 'invalid', label: 'Term sudah penuh', amount: amount, remaining: remaining, poNumber: lookup.po_number };
                }

                if (amount <= 0) {
                    return { valid: false, code: 'invalid', label: 'Nilai invoice kosong', amount: amount, remaining: remaining, poNumber: lookup.po_number };
                }

                if (amount > remaining + 0.000001) {
                    return { valid: false, code: 'invalid', label: 'Melebihi sisa term', amount: amount, remaining: remaining, poNumber: lookup.po_number };
                }

                return { valid: true, code: 'success', label: 'Valid', amount: amount, remaining: remaining, poNumber: lookup.po_number, key: key };
            }

            function updateBatchInvoiceState() {
                var totals = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
                var counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
                var validCount = 0;
                var validTotal = 0;
                var rowNo = 1;

                $('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row').each(function() {
                    var $row = $(this);
                    $row.find('td:first').text(rowNo++);
                    if (String($row.data('valid')) === '1') {
                        var termNo = Number($row.data('term-no') || 0);
                        var amount = Number($row.data('invoice-value') || 0);
                        if (termNo >= 1 && termNo <= 5) {
                            totals[termNo] += amount;
                            counts[termNo]++;
                        }
                        validCount++;
                        validTotal += amount;
                    }
                });

                for (var term = 1; term <= 5; term++) {
                    $('#po-monitor-batch-summary-count-' + term).text(counts[term]);
                    $('#po-monitor-batch-summary-term-' + term).text(formatLocaleNumber(totals[term]));
                }

                $('#po-monitor-batch-summary-total-count').text(validCount);
                $('#po-monitor-batch-summary-total-value').text(formatLocaleNumber(validTotal));
                $('#po-monitor-batch-submit').prop('disabled', validCount === 0);
                $('#po-monitor-batch-clear-list').prop('disabled', rowNo === 1);
                $('.po-monitor-batch-empty-row').toggle(rowNo === 1);
            }

            function addBatchInvoiceRow(poNumber, termValue, invoiceValue) {
                var termNo = normalizeBatchTerm(termValue);
                var check = getBatchInvoiceCheck(poNumber, termNo, invoiceValue);
                var rowClass = check.valid ? 'table-success' : 'table-danger';
                var hiddenInputs = '';

                if (check.valid) {
                    hiddenInputs =
                        '<input type="hidden" name="po_number[]" value="' + escapeHtml(check.poNumber) + '">' +
                        '<input type="hidden" name="term_no[]" value="' + escapeHtml(termNo) + '">' +
                        '<input type="hidden" name="invoice_amount[]" value="' + escapeHtml(check.amount) + '">';
                }

                var html = '<tr class="po-monitor-batch-row ' + rowClass + '" data-valid="' + (check.valid ? '1' : '0') + '" data-key="' + escapeHtml(check.key || '') + '" data-term-no="' + escapeHtml(termNo) + '" data-invoice-value="' + escapeHtml(check.amount || 0) + '">' +
                    '<td></td>' +
                    '<td>' + escapeHtml(check.poNumber || poNumber) + hiddenInputs + '</td>' +
                    '<td>Term ' + escapeHtml(termNo || '-') + '</td>' +
                    '<td class="text-right">' + formatLocaleNumber(check.amount || 0) + '</td>' +
                    '<td class="text-right">' + formatLocaleNumber(Math.max(Number(check.remaining || 0), 0)) + '</td>' +
                    '<td><span class="badge badge-' + (check.valid ? 'success' : 'danger') + '">' + escapeHtml(check.label) + '</span></td>' +
                    '<td><button type="button" class="btn btn-sm btn-outline-danger po-monitor-batch-remove-row">Hapus</button></td>' +
                    '</tr>';

                $('#po-monitor-batch-invoice-table tbody').append(html);
                updateBatchInvoiceState();
                return check.valid;
            }

            $('#po-monitor-batch-add-row').off('click.poMonitorBatch').on('click.poMonitorBatch', function() {
                var added = addBatchInvoiceRow(
                    $('#po-monitor-batch-po-number').val(),
                    $('#po-monitor-batch-term-no').val(),
                    $('#po-monitor-batch-invoice-value').val()
                );
                if (added) {
                    $('#po-monitor-batch-po-number').val('').focus();
                    $('#po-monitor-batch-invoice-value').val('');
                }
            });

            $('#po-monitor-batch-invoice-value').off('input.poMonitorBatchFormat').on('input.poMonitorBatchFormat', function() {
                var value = $(this).val();
                if (value === '') {
                    return;
                }
                $(this).val(formatLocaleNumber(parseLocaleNumber(value)));
            });

            $('#po-monitor-batch-parse-paste').off('click.poMonitorBatchPaste').on('click.poMonitorBatchPaste', function() {
                var text = $('#po-monitor-batch-paste').val();
                String(text || '').split(/\r?\n/).forEach(function(line) {
                    if (!line.trim()) {
                        return;
                    }
                    var columns = line.split(/\t/);
                    if (columns.length < 2) {
                        columns = line.split(/[;,]/);
                    }
                    addBatchInvoiceRow(columns[0] || '', columns[1] || '', columns.length >= 3 ? columns.slice(2).join(' ') : '');
                });
                updateBatchInvoiceState();
            });

            $('#po-monitor-batch-clear-list').off('click.poMonitorBatchClear').on('click.poMonitorBatchClear', function() {
                $('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row').remove();
                updateBatchInvoiceState();
            });

            $(document).off('click.poMonitorBatchRemove', '.po-monitor-batch-remove-row').on('click.poMonitorBatchRemove', '.po-monitor-batch-remove-row', function() {
                $(this).closest('tr').remove();
                updateBatchInvoiceState();
            });

            $('#po-monitor-batch-invoice-form').off('submit.poMonitorBatchSubmit').on('submit.poMonitorBatchSubmit', function(e) {
                if ($('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row[data-valid="1"]').length === 0) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });

            updateBatchInvoiceState();

            initServerSideTable($, '#table_po_dashboard_excel', '<?= site_url('PO_Monitor/dashboard_datatable') ?>', [[0, 'asc']], 25);

            var compareTables = {};
            function initCompareTable(selector) {
                if (!$.fn.DataTable || !$(selector).length) {
                    return null;
                }

                if ($.fn.DataTable.isDataTable(selector)) {
                    return $(selector).DataTable();
                }

                return $(selector).DataTable({
                    paging: true,
                    pageLength: 25,
                    searching: true,
                    info: true,
                    lengthChange: true,
                    autoWidth: false,
                    responsive: false,
                    scrollX: true,
                    ordering: false
                });
            }

            compareTables.month = initCompareTable('#table_po_target_invoice_compare_month');
            compareTables.week = initCompareTable('#table_po_target_invoice_compare_week');

            $(document)
                .off('click.poCompareDetail', '.po-compare-detail-link')
                .on('click.poCompareDetail', '.po-compare-detail-link', function() {
                    var $button = $(this);
                    var $modal = $('#po_compare_detail_modal');

                    $modal.find('.modal-title').html('<span class="po-monitor-modal-eyebrow">Detail Perbandingan</span>Detail PO');
                    $modal.find('.modal-body').html('<div class="text-muted">Loading...</div>');
                    $modal.modal('show');

                    $.ajax({
                        url: '<?= site_url('PO_Monitor/comparison_detail') ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_bowheer: $button.data('id-bowheer'),
                            period_key: $button.data('period-key'),
                            group_by: $button.data('group-by'),
                            type: $button.data('type')
                        }
                    }).done(function(response) {
                        $modal.find('.modal-title').html(response && response.title ? response.title : '<span class="po-monitor-modal-eyebrow">Detail Perbandingan</span>Detail PO');
                        $modal.find('.modal-body').html(response && response.html ? response.html : '<div class="alert alert-warning mb-0">Detail tidak tersedia.</div>');
                    }).fail(function() {
                        $modal.find('.modal-body').html('<div class="alert alert-danger mb-0">Gagal mengambil detail PO.</div>');
                    });
                });

            $(document)
                .off('click.poTermDetail', '.po-monitor-term-detail-link')
                .on('click.poTermDetail', '.po-monitor-term-detail-link', function() {
                    var $button = $(this);
                    var $modal = $('#po_compare_detail_modal');

                    $modal.find('.modal-title').html('<span class="po-monitor-modal-eyebrow">Detail Term</span>Detail PO');
                    $modal.find('.modal-body').html('<div class="text-muted">Loading...</div>');
                    $modal.modal('show');

                    $.ajax({
                        url: '<?= site_url('PO_Monitor/term_detail') ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_bowheer: $button.data('id-bowheer'),
                            metric: $button.data('metric'),
                            term_index: $button.data('term-index')
                        }
                    }).done(function(response) {
                        $modal.find('.modal-title').html(response && response.title ? response.title : '<span class="po-monitor-modal-eyebrow">Detail Term</span>Detail PO');
                        $modal.find('.modal-body').html(response && response.html ? response.html : '<div class="alert alert-warning mb-0">Detail tidak tersedia.</div>');
                    }).fail(function() {
                        $modal.find('.modal-body').html('<div class="alert alert-danger mb-0">Gagal mengambil detail PO.</div>');
                    });
                });

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'table_po_target_invoice_compare_month' && settings.nTable.id !== 'table_po_target_invoice_compare_week') {
                    return true;
                }

                var dataOnly = $('#po_compare_data_only').is(':checked');
                if (!dataOnly) {
                    return true;
                }

                var row = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
                var achieved = Number($(row).data('achieved') || 0);
                var target = Number($(row).data('target') || 0);

                return achieved > 0 || target > 0;
            });

            function syncCompareSwitches() {
                var weekMode = $('#po_compare_week_mode').is(':checked');
                $('#po_compare_month_panel').toggleClass('po-compare-panel-hidden', weekMode);
                $('#po_compare_week_panel').toggleClass('po-compare-panel-hidden', !weekMode);

                if (compareTables.month) {
                    compareTables.month.draw();
                    if (!weekMode) {
                        window.setTimeout(function() {
                            compareTables.month.columns.adjust().draw(false);
                        }, 80);
                    }
                }

                if (compareTables.week) {
                    compareTables.week.draw();
                    if (weekMode) {
                        window.setTimeout(function() {
                            compareTables.week.columns.adjust().draw(false);
                        }, 80);
                    }
                }
            }

            $('#po_compare_data_only, #po_compare_week_mode')
                .off('change.poCompareSwitch')
                .on('change.poCompareSwitch', syncCompareSwitches);

            syncCompareSwitches();

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

            initServerSideTable($, '#table_po_monitor_list', '<?= site_url('PO_Monitor/po_datatable') ?>', [[2, 'desc']], 25);

        }

        bootstrapPOMonitor();
    })();
</script>
