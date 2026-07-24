<?php
$status = $this->session->flashdata('status');
$error_log = $this->session->flashdata('error_log');
$isLocalAccess = $isLocalAccess ?? false;
$canManagePoImport = !empty($canManagePoImport);
$batchInvoiceRows = is_array($batchInvoiceRows ?? null) ? $batchInvoiceRows : [];
$breakdownFilterOptions = is_array($breakdownFilterOptions ?? null) ? $breakdownFilterOptions : [
    'projects' => [],
    'pics' => [],
    'regionals' => [],
    'areas' => [],
    'months' => [],
    'weeks' => []
];
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
        background: #f8fafc !important;
        color: #0f172a;
        font-weight: 900;
        text-align: right;
        border-top: 2px solid #cbd5e1 !important;
    }

    .po-compare-table tfoot th.po-compare-footer-label {
        text-align: left;
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

    .po-monitor-compare-body,
    .po-monitor-compare-body .dataTables_wrapper,
    .po-monitor-compare-body .dataTables_scroll,
    .po-monitor-compare-body .dataTables_scrollHead,
    .po-monitor-compare-body .dataTables_scrollBody {
        width: 100% !important;
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
        margin-top: 1rem;
    }

    #po_breakdown_detail_modal .modal-dialog {
        max-width: min(1180px, calc(100vw - 1.5rem));
        margin-top: 1rem;
    }

    #po_compare_detail_modal .modal-body {
        max-height: 76vh;
        overflow: auto;
    }

    #po_breakdown_detail_modal .modal-body {
        max-height: 76vh;
        overflow: auto;
        background: #f8fafc;
    }

    #po_compare_detail_modal .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid rgba(148, 163, 184, 0.22);
        background: #f8fafc;
        padding: 0.75rem 1rem;
    }

    .po-monitor-modal-download-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        justify-content: flex-end;
    }

    .po-monitor-modal-filter-actions {
        display: flex;
        align-items: center;
        min-width: 220px;
    }

    .po-monitor-modal-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    #po_compare_detail_modal .modal-content,
    #po_breakdown_detail_modal .modal-content,
    #po_monitor_batch_invoice_modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
    }

    #po_compare_detail_modal .modal-header,
    #po_breakdown_detail_modal .modal-header,
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
    #po_breakdown_detail_modal .modal-title,
    #po_monitor_batch_invoice_modal .modal-title {
        color: #fff;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.35;
    }

    #po_compare_detail_modal .close,
    #po_breakdown_detail_modal .close,
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
        margin-bottom: 1rem;
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

    .po-monitor-summary-band {
        position: relative;
        margin: 0 0 1.05rem;
        padding: 0.86rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 10px;
        background: #ffffff;
    }

    .po-monitor-summary-band::before {
        content: "";
        position: absolute;
        left: 0.85rem;
        right: 0.85rem;
        top: -0.52rem;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.72), transparent);
    }

    .po-monitor-summary-band--regional {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .po-monitor-summary-band--term {
        margin-top: 1.15rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfffb 100%);
    }

    .po-monitor-summary-band__header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .po-monitor-summary-band__header span {
        color: #0f2c49;
        font-size: 0.76rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .po-monitor-summary-band__header b {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
    }

    .po-monitor-regional-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.7rem;
        margin: 0;
    }

    .po-monitor-regional-card {
        display: block;
        width: 100%;
        min-height: 82px;
        padding: 0.72rem 0.78rem;
        border: 1px solid rgba(148, 163, 184, 0.26);
        border-left: 5px solid #2563eb;
        border-radius: 8px;
        background: linear-gradient(135deg, #fff, #f8fafc);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        cursor: pointer;
        text-align: left;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
    }

    .po-monitor-regional-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.11);
    }

    .po-monitor-regional-card:focus {
        outline: 3px solid rgba(37, 99, 235, 0.24);
        outline-offset: 2px;
    }

    .po-monitor-regional-card.is-active {
        border-color: rgba(37, 99, 235, 0.62);
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.18),
            0 0 28px rgba(37, 99, 235, 0.24),
            0 18px 34px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .po-monitor-regional-card--r2 {
        border-left-color: #16a34a;
    }

    .po-monitor-regional-card--r3 {
        border-left-color: #f59e0b;
    }

    .po-monitor-regional-card--r4 {
        border-left-color: #8b5cf6;
    }

    .po-monitor-regional-card--r5 {
        border-left-color: #0f766e;
    }

    .po-monitor-regional-card__label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.45rem;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-monitor-regional-card__label b {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 0.4rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.72rem;
        letter-spacing: 0;
    }

    .po-monitor-regional-card__value {
        display: block;
        margin-top: 0.48rem;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .po-monitor-regional-card__caption {
        display: block;
        margin-top: 0.22rem;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .po-monitor-regional-extra {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0 0 0.85rem;
        padding: 0.5rem 0.7rem;
        border: 1px solid rgba(245, 158, 11, 0.32);
        border-radius: 8px;
        background: #fffbeb;
        color: #78350f;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .po-monitor-term-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.7rem;
        margin: 0;
    }

    .po-monitor-term-card {
        display: block;
        width: 100%;
        min-height: 76px;
        padding: 0.68rem 0.76rem;
        border: 1px solid rgba(148, 163, 184, 0.26);
        border-bottom: 5px solid #2563eb;
        border-radius: 8px;
        background: linear-gradient(135deg, #fff, #f8fafc);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        cursor: pointer;
        text-align: left;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
    }

    .po-monitor-term-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.11);
    }

    .po-monitor-term-card:focus {
        outline: 3px solid rgba(37, 99, 235, 0.24);
        outline-offset: 2px;
    }

    .po-monitor-term-card--t2 {
        border-bottom-color: #16a34a;
    }

    .po-monitor-term-card--t3 {
        border-bottom-color: #f59e0b;
    }

    .po-monitor-term-card--t4 {
        border-bottom-color: #8b5cf6;
    }

    .po-monitor-term-card--t5 {
        border-bottom-color: #0f766e;
    }

    .po-monitor-term-card.is-active {
        border-color: rgba(37, 99, 235, 0.62);
        background: linear-gradient(135deg, #eef6ff, #ffffff);
        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.16),
            0 0 28px rgba(37, 99, 235, 0.22),
            0 18px 34px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .po-monitor-term-card__label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.45rem;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-monitor-term-card__label b {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 0.4rem;
        border-radius: 999px;
        background: rgba(15, 118, 110, 0.1);
        color: #0f766e;
        font-size: 0.72rem;
        letter-spacing: 0;
    }

    .po-monitor-term-card__value {
        display: block;
        margin-top: 0.44rem;
        color: #0f172a;
        font-size: 0.96rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .po-monitor-term-card__caption {
        display: block;
        margin-top: 0.22rem;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .po-monitor-regional-section {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) 120px minmax(180px, 0.7fr);
        gap: 0.75rem;
        align-items: center;
        margin: 1rem 0 0.45rem;
        padding: 0.68rem 0.78rem;
        border: 1px solid rgba(15, 44, 73, 0.12);
        border-radius: 8px;
        background: linear-gradient(135deg, #102f50, #27588d);
        color: #fff;
    }

    .po-monitor-regional-group.is-hidden {
        display: none;
    }

    .po-monitor-detail-table tr.is-hidden {
        display: none;
    }

    .po-monitor-regional-section span {
        display: block;
        color: rgba(226, 232, 240, 0.76);
        font-size: 0.65rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .po-monitor-regional-section strong {
        display: block;
        margin-top: 0.16rem;
        color: #fff;
        font-size: 0.92rem;
        font-weight: 900;
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

    .po-monitor-detail-table tbody tr.po-monitor-detail-row-invoiced td {
        background: #86efac !important;
        color: #052e16 !important;
        font-weight: 800;
    }

    .po-monitor-detail-table tbody tr.po-monitor-detail-row-invoiced td:first-child {
        box-shadow: inset 4px 0 0 #16a34a;
    }

    @media (max-width: 1199.98px) {
        .po-monitor-regional-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .po-monitor-term-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .po-monitor-regional-section {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .po-monitor-regional-summary {
            grid-template-columns: 1fr;
        }

        .po-monitor-term-summary {
            grid-template-columns: 1fr;
        }
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

    .po-monitor-list-panel {
        border-color: rgba(203, 213, 225, 0.8);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.09);
    }

    .po-monitor-list-panel .po-monitor-panel__head {
        align-items: flex-start;
        border-bottom: 0;
        padding-bottom: 0.75rem;
    }

    .po-monitor-list-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
        padding: 0.32rem 0.58rem;
        border-radius: 999px;
        background: #eaf0ff;
        color: #1d4ed8;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .po-monitor-list-panel .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 0.7rem;
    }

    .po-monitor-list-panel .dataTables_filter,
    .po-monitor-list-panel .dataTables_length {
        width: 100%;
    }

    .po-monitor-list-panel .dataTables_filter label,
    .po-monitor-list-panel .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        width: 100%;
        margin-bottom: 0;
        color: transparent;
        font-size: 0;
    }

    .po-monitor-list-panel .dataTables_filter label::before,
    .po-monitor-list-panel .dataTables_length label::before {
        color: #64748b;
        font-family: "Font Awesome 5 Free";
        font-size: 0.84rem;
        font-weight: 900;
    }

    .po-monitor-list-panel .dataTables_filter label::before {
        content: "\f002";
        margin-right: -2rem;
        position: relative;
        z-index: 1;
    }

    .po-monitor-list-panel .dataTables_length label::before {
        content: "\f03a";
        margin-right: -2rem;
        position: relative;
        z-index: 1;
    }

    .po-monitor-list-panel .dataTables_filter input,
    .po-monitor-list-panel .dataTables_length select {
        height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: #fff;
        color: #0f172a;
        font-size: 0.82rem;
        box-shadow: none;
    }

    .po-monitor-list-panel .dataTables_filter input {
        width: 100% !important;
        margin-left: 0;
        padding-left: 2.25rem;
    }

    .po-monitor-list-panel .dataTables_length select {
        width: 128px;
        padding-left: 2.15rem;
    }

    .po-monitor-list-table {
        border: 0 !important;
        color: #020617;
    }

    .po-monitor-list-table thead th {
        border: 0 !important;
        border-bottom: 1px solid #bfdbfe !important;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .po-monitor-list-table tbody td {
        border-top: 0 !important;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.72rem 0.6rem;
        font-size: 0.8rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .po-monitor-list-table tfoot th {
        border: 0 !important;
        background: #f8fafc;
        color: #020617;
        font-size: 0.82rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .po-monitor-list-table .po-monitor-money {
        font-weight: 800;
    }

    .po-monitor-sla-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 0.28rem 0.5rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 900;
    }

    .po-monitor-sla-pill--aman {
        background: #dcfce7;
        color: #15803d;
    }

    .po-monitor-sla-pill--warning {
        background: #ffedd5;
        color: #c2410c;
    }

    .po-monitor-sla-pill--overdue {
        background: #fee2e2;
        color: #dc2626;
    }

    .po-monitor-list-detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #dbeafe;
        color: #1d4ed8;
        text-decoration: none;
    }

    .po-monitor-list-detail-btn:hover {
        background: #2563eb;
        color: #fff;
    }

    .po-monitor-list-panel .dataTables_info {
        padding-top: 1rem;
        color: #64748b;
        font-size: 0.78rem;
    }

    .po-monitor-list-panel .pagination {
        margin-top: 0.8rem;
    }

    .po-monitor-list-panel .page-link {
        border-color: #dbe3ef;
        color: #2563eb;
        font-size: 0.82rem;
    }

    .po-monitor-list-panel .page-item.active .page-link {
        border-color: #2563eb;
        background: #2563eb;
    }

    .po-breakdown-filter-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    #po_breakdown_filter_modal .po-breakdown-filter-grid {
        align-items: start;
    }

    .po-breakdown-filter-grid .po-monitor-field label {
        font-size: 0.68rem;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-breakdown-toolbar {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0.7rem 0 1rem;
    }

    .po-breakdown-toolbar .po-monitor-list-panel .dataTables_filter {
        flex: 1;
    }

    .po-breakdown-active-filters {
        display: none;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem;
        margin: -0.25rem 0 1rem;
    }

    .po-breakdown-active-filters.has-filter {
        display: flex;
    }

    .po-breakdown-filter-chip {
        display: inline-flex;
        align-items: center;
        max-width: min(360px, 100%);
        gap: 0.35rem;
        padding: 0.42rem 0.68rem;
        border-radius: 999px;
        background: #f3f4f6;
        color: #0f172a;
        font-size: 0.76rem;
        font-weight: 900;
        line-height: 1.1;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.16);
    }

    .po-breakdown-filter-chip i {
        flex: 0 0 auto;
        color: #1d4ed8;
        font-size: 0.72rem;
    }

    .po-breakdown-filter-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .po-breakdown-tabs {
        gap: 0.3rem;
        margin-bottom: 1rem;
        padding: 0.35rem;
        border-radius: 8px;
        background: #eef2f7;
    }

    .po-breakdown-tabs .nav-link {
        border-radius: 7px;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 900;
    }

    .po-breakdown-tabs .nav-link.active {
        background: #1d4ed8;
        color: #fff;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .po-breakdown-progress {
        min-width: 150px;
    }

    .po-breakdown-progress__track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .po-breakdown-progress__bar {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #22c55e);
    }

    .po-breakdown-progress__text {
        display: block;
        margin-top: 0.25rem;
        color: #0f172a;
        font-size: 0.72rem;
        font-weight: 900;
    }

    .po-breakdown-empty {
        padding: 1rem;
        color: #64748b;
        text-align: center;
    }

    #po_breakdown_filter_modal .modal-dialog {
        max-width: min(1120px, calc(100vw - 1.5rem));
        margin-top: 1.1rem;
    }

    #po_breakdown_filter_modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 12px;
        background: #f8fafc;
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.26);
    }

    #po_breakdown_filter_modal .modal-header {
        align-items: flex-start;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
        padding: 1.15rem 1.35rem 0.95rem;
    }

    #po_breakdown_filter_modal .modal-title {
        color: #0f172a;
        font-size: 1.14rem;
        font-weight: 900;
        line-height: 1.25;
    }

    #po_breakdown_filter_modal .close {
        width: 34px;
        height: 34px;
        margin: -0.15rem -0.25rem 0 0;
        border-radius: 999px;
        color: #64748b;
        opacity: 1;
    }

    #po_breakdown_filter_modal .close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .po-breakdown-filter-subtitle {
        margin: 0.25rem 0 0;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 500;
    }

    #po_breakdown_filter_modal .modal-body {
        padding: 1.25rem 1.35rem 1.4rem;
    }

    #po_breakdown_filter_modal .modal-footer {
        display: flex;
        gap: 0.6rem;
        justify-content: flex-end;
        border-top: 1px solid #e2e8f0;
        background: #fff;
        padding: 0.9rem 1.35rem;
    }

    #po_breakdown_filter_modal .modal-footer .btn {
        min-width: 116px;
        border-radius: 8px;
        font-weight: 900;
    }

    #po_breakdown_filter_modal .modal-footer .btn-light {
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        color: #334155;
    }

    #po_breakdown_filter_modal .po-monitor-field {
        min-width: 0;
        padding: 0.85rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
    }

    #po_breakdown_filter_modal .po-monitor-field label {
        display: block;
        margin-bottom: 0.45rem;
        color: #1e3a8a;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    #po_breakdown_filter_modal .select2-container {
        width: 100% !important;
    }

    #po_breakdown_filter_modal .select2-container--bootstrap4 .select2-selection {
        min-height: 44px;
        border: 1px solid #d7e0ec;
        border-radius: 10px;
        background: #fff;
        box-shadow: none;
    }

    #po_breakdown_filter_modal .select2-container--bootstrap4.select2-container--focus .select2-selection,
    #po_breakdown_filter_modal .select2-container--bootstrap4.select2-container--open .select2-selection {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    #po_breakdown_filter_modal .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        background: #e0ebff;
        color: #1d4ed8;
        font-size: 0.76rem;
        font-weight: 800;
        padding: 0.2rem 0.45rem;
    }

    #po_breakdown_filter_modal .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        min-width: 8rem;
        margin-top: 0.45rem;
        color: #0f172a;
    }

    .select2-dropdown.po-breakdown-select2-dropdown {
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
    }

    .select2-dropdown.po-breakdown-select2-dropdown .select2-search {
        padding: 0.55rem;
        border-bottom: 1px solid #eef2f7;
    }

    .select2-dropdown.po-breakdown-select2-dropdown .select2-search__field {
        height: 34px;
        border: 1px solid #d7e0ec !important;
        border-radius: 8px;
        padding: 0.35rem 0.55rem;
        color: #0f172a;
        font-size: 0.82rem;
        outline: 0;
    }

    .select2-dropdown.po-breakdown-select2-dropdown .select2-results__options {
        max-height: 220px;
        padding: 0.35rem;
    }

    .po-breakdown-select2-dropdown .select2-results__option {
        border-radius: 7px;
        margin-bottom: 0.15rem;
        padding: 0.52rem 0.72rem;
        color: #0f172a;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .po-breakdown-select2-dropdown .select2-results__option--highlighted {
        background: #2563eb !important;
        color: #fff !important;
    }

    .po-breakdown-select2-dropdown .select2-results__option[aria-selected=true] {
        background: #e0ebff;
        color: #1d4ed8;
        font-weight: 900;
    }

    @media (max-width: 1199.98px) {
        .po-breakdown-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
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
        .po-breakdown-filter-grid,
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
                $dashboardPo2026 = (float) ($dashboardTotals['all_po'] ?? 0);
                $dashboardAccelerationTarget = $dashboardPo2026 + $dashboardDoneInvoice + $dashboardOutsOnTarget + $dashboardNyPoTarget;

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
                                <button type="button" class="btn btn-light btn-sm font-weight-bold" data-toggle="modal" data-target="#po_monitor_batch_po_modal">
                                    <i class="fas fa-plus mr-1"></i> Tambah PO
                                </button>
                                <button type="button" class="btn btn-light btn-sm font-weight-bold" data-toggle="modal" data-target="#po_monitor_batch_invoice_modal">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Batch Input Invoice Termin
                                </button>
                            </div>
                        </div>
                        <div class="po-monitor-hero__stats">
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Target Akselerasi 2026</span>
                                <span class="po-monitor-hero-stat__value" id="summary_total_po_hero"><?= number_format($dashboardAccelerationTarget, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">PO 2026 + done invoice + outs target + NY PO target</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Done Invoice</span>
                                <span class="po-monitor-hero-stat__value" id="summary_done_invoice_hero"><?= number_format($dashboardDoneInvoice, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Invoice selesai di 2026</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Outs By PO</span>
                                <span class="po-monitor-hero-stat__value" id="summary_target_week_hero"><?= number_format($dashboardOutsOnTarget, 0, ',', '.') ?></span>
                                <span class="po-monitor-hero-stat__hint">Outstanding target berjalan</span>
                            </div>
                            <div class="po-monitor-hero-stat">
                                <span class="po-monitor-hero-stat__label">Outstanding NY PO</span>
                                <span class="po-monitor-hero-stat__value" id="summary_carry_over_hero"><?= number_format($dashboardNyPoTarget, 0, ',', '.') ?></span>
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

                <?php if ($canManagePoImport): ?>
                <div class="po-monitor-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Import Database PO CSV</h3>
                            <p class="po-monitor-panel__subtitle">Import ulang data master PO Monitor standalone dari CSV.</p>
                        </div>
                        <div class="po-monitor-table-actions">
                            <a href="<?= site_url('PO_Monitor/rebuild_dashboard_metrics') ?>" class="btn btn-outline-primary btn-sm font-weight-bold">
                                <i class="fas fa-sync-alt mr-1"></i> Rebuild Dashboard
                            </a>
                            <a href="<?= site_url('PO_Monitor/download_import_report') ?>" class="btn btn-success btn-sm font-weight-bold">
                                <i class="fas fa-file-excel mr-1"></i> Download Excel Report
                            </a>
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
                        <?php if ($canManagePoImport): ?>
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
                            <span class="po-monitor-kpi-card__label">Target Akselerasi 2026</span>
                            <span class="po-monitor-kpi-card__value" id="summary_total_po"><?= number_format($dashboardAccelerationTarget, 0, ',', '.') ?></span>
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

                <div class="po-monitor-panel" id="po_compare_panel" style="display: none;">
                    <div class="po-monitor-panel__head">
                        <div>
                            <h3 class="po-monitor-panel__title">Perbandingan Target dan Invoice</h3>
                            <p class="po-monitor-panel__subtitle">Bandingkan target paten dan realisasi invoice per bulan atau per week.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body po-monitor-compare-body" id="po-monitor-compare-body">
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
                                    <tr class="po-compare-month">
                                        <th rowspan="2" class="po-compare-fixed po-compare-fixed-left">No</th>
                                        <th rowspan="2" class="po-compare-fixed po-compare-fixed-left">PIC</th>
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
                                </thead>
                                <tbody>
                                    <?php $compareNo = 1; foreach ($comparisonMatrix['rows'] as $row): ?>
                                        <tr data-achieved="<?= (float) $row['total_achieved'] ?>" data-target="<?= (float) $row['total_target'] ?>">
                                            <td><?= $compareNo++ ?></td>
                                            <td><?= htmlspecialchars($row['pic'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($row['project']) ?></td>
                                            <td data-po-amount="<?= (float) $row['total_target'] ?>"><?= number_format((float) $row['total_target'], 0, ',', '.') ?></td>
                                            <?php foreach ($comparisonMatrix['months'] as $month): ?>
                                                <?php $monthData = $row['months'][$month['key']] ?? ['target' => 0, 'achieved' => 0, 'percent' => 0]; ?>
                                                <td data-po-amount="<?= (float) $monthData['target'] ?>"><?= po_monitor_compare_amount_link($monthData['target'], $row['id_bowheer'], $month['key'], 'month', 'target') ?></td>
                                                <td data-po-amount="<?= (float) $monthData['achieved'] ?>"><?= po_monitor_compare_amount_link($monthData['achieved'], $row['id_bowheer'], $month['key'], 'month', 'achieved') ?></td>
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

                        <div id="po_compare_week_panel" class="table-responsive po-compare-panel-hidden" data-loaded="0">
                            <div class="text-muted py-3">Aktifkan mode week untuk memuat data mingguan.</div>
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

                <div class="modal fade" id="po_breakdown_filter_modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title"><span class="po-monitor-modal-eyebrow">Kontrol Data</span>Filter Data</h5>
                                    <p class="po-breakdown-filter-subtitle">Pilih satu atau beberapa opsi. Daftar pilihan akan menyesuaikan filter lain.</p>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="po-breakdown-filter-grid">
                                    <div class="po-monitor-field">
                                        <label>Project</label>
                                        <select id="po_breakdown_filter_project" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua project">
                                            <?php foreach (($breakdownFilterOptions['projects'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="po-monitor-field">
                                        <label>PIC</label>
                                        <select id="po_breakdown_filter_pic" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua PIC">
                                            <?php foreach (($breakdownFilterOptions['pics'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="po-monitor-field">
                                        <label>Regional</label>
                                        <select id="po_breakdown_filter_regional" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua regional">
                                            <?php foreach (($breakdownFilterOptions['regionals'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="po-monitor-field">
                                        <label>Area</label>
                                        <select id="po_breakdown_filter_area" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua area">
                                            <?php foreach (($breakdownFilterOptions['areas'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="po-monitor-field">
                                        <label>Tahun - Bulan</label>
                                        <select id="po_breakdown_filter_month" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua tahun - bulan">
                                            <?php foreach (($breakdownFilterOptions['months'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="po-monitor-field">
                                        <label>Week</label>
                                        <select id="po_breakdown_filter_week" class="form-control po-breakdown-filter-select" multiple="multiple" data-placeholder="Semua week">
                                            <?php foreach (($breakdownFilterOptions['weeks'] ?? []) as $option): ?>
                                                <option value="<?= htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" id="po_breakdown_reset">
                                    <i class="fas fa-undo-alt mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-primary" id="po_breakdown_apply">
                                    <i class="fas fa-search mr-1"></i> Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="po-monitor-panel po-monitor-list-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <span class="po-monitor-list-eyebrow"><i class="fas fa-layer-group"></i> Report Detail</span>
                            <h3 class="po-monitor-panel__title">Breakdown Target Invoice</h3>
                            <p class="po-monitor-panel__subtitle">Gunakan tab untuk melihat sudut pandang report yang berbeda.</p>
                        </div>
                        <div class="po-monitor-table-actions">
                            <button type="button" class="btn btn-light btn-sm font-weight-bold" data-toggle="modal" data-target="#po_breakdown_filter_modal">
                                <i class="fas fa-sliders-h mr-1"></i> Filter Data
                            </button>
                            <label class="po-monitor-switch mb-0">
                                <input type="checkbox" id="po_breakdown_invoiced_only">
                                <span class="po-monitor-switch-slider"></span>
                                <span>Invoiced Only</span>
                            </label>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body">
                        <div class="po-breakdown-toolbar">
                            <div class="po-monitor-list-panel flex-fill">
                                <div class="dataTables_filter">
                                    <label>
                                        <input type="search" id="po_breakdown_search" class="form-control" placeholder="Cari breakdown invoice">
                                    </label>
                                </div>
                            </div>
                            <div class="po-monitor-list-panel">
                                <div class="dataTables_length">
                                    <label>
                                        <select id="po_breakdown_limit" class="form-control">
                                            <option value="10">10 row</option>
                                            <option value="25">25 row</option>
                                            <option value="50">50 row</option>
                                            <option value="-1">Semua row</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="po-breakdown-active-filters" id="po_breakdown_active_filters" aria-live="polite"></div>

                        <ul class="nav po-breakdown-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#po_breakdown_tab_project" role="tab" data-breakdown-mode="project">Project</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#po_breakdown_tab_pic" role="tab" data-breakdown-mode="pic">PIC</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#po_breakdown_tab_regional" role="tab" data-breakdown-mode="regional">Regional</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#po_breakdown_tab_area" role="tab" data-breakdown-mode="area">Kota / Area</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#po_breakdown_tab_period" role="tab" data-breakdown-mode="period">Periode</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#po_breakdown_tab_date" role="tab" data-breakdown-mode="date">Tanggal</a></li>
                        </ul>

                        <div class="tab-content">
                            <?php foreach (['project', 'pic', 'regional', 'area', 'period', 'date'] as $mode): ?>
                                <div class="tab-pane fade <?= $mode === 'project' ? 'show active' : '' ?>" id="po_breakdown_tab_<?= $mode ?>" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table po-monitor-list-table po-breakdown-table" data-breakdown-table="<?= $mode ?>">
                                            <thead></thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="po-monitor-panel po-monitor-list-panel">
                    <div class="po-monitor-panel__head">
                        <div>
                            <span class="po-monitor-list-eyebrow"><i class="fas fa-database"></i> Report Detail</span>
                            <h3 class="po-monitor-panel__title">List PO Monitor</h3>
                            <p class="po-monitor-panel__subtitle">Daftar PO standalone yang digunakan halaman PO Monitor.</p>
                        </div>
                    </div>
                    <div class="po-monitor-panel__body table-responsive">
                        <table id="table_po_monitor_list" class="table po-monitor-list-table">
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

<div class="modal fade" id="po_breakdown_detail_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span class="po-monitor-modal-eyebrow">Breakdown Detail</span>Detail Target Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-muted">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="po_compare_detail_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
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
            <div class="modal-footer">
                <div class="po-monitor-modal-filter-actions">
                    <label class="po-monitor-switch mb-0 d-none" id="po-monitor-detail-uninvoiced-wrap">
                        <input type="checkbox" id="po-monitor-detail-uninvoiced-only">
                        <span class="po-monitor-switch-slider"></span>
                        <span>Belum invoice saja</span>
                    </label>
                </div>
                <div class="po-monitor-modal-download-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="po-monitor-detail-capture">
                        <i class="fas fa-camera mr-1"></i> Copy Image
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="po-monitor-detail-excel">
                        <i class="fas fa-file-excel mr-1"></i> Download Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="po_monitor_batch_po_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= site_url('PO_Monitor/batch_add_po') ?>" id="po-monitor-batch-po-form">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="po-monitor-modal-eyebrow">PO Monitor</span>Tambah PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <datalist id="po-monitor-batch-po-bowheer-options">
                        <?php foreach ($uniqueBowheer as $bowheerName): ?>
                            <option value="<?= htmlspecialchars($bowheerName, ENT_QUOTES) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>

                    <ul class="nav nav-tabs" id="po-monitor-batch-po-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="po-monitor-batch-po-manual-tab" data-toggle="pill" href="#po-monitor-batch-po-manual-pane" role="tab">Input Manual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="po-monitor-batch-po-paste-tab" data-toggle="pill" href="#po-monitor-batch-po-paste-pane" role="tab">Paste dari Excel</a>
                        </li>
                    </ul>

                    <div class="tab-content border-left border-right border-bottom p-3 mb-3">
                        <div class="tab-pane fade show active" id="po-monitor-batch-po-manual-pane" role="tabpanel">
                            <div class="po-monitor-filter-grid">
                                <div class="po-monitor-field">
                                    <label>BOWHEER</label>
                                    <input type="text" id="po-monitor-batch-po-bowheer" class="form-control" list="po-monitor-batch-po-bowheer-options">
                                </div>
                                <div class="po-monitor-field">
                                    <label>STATUS PO</label>
                                    <select id="po-monitor-batch-po-status" class="form-control">
                                        <option value="ON PO">ON PO</option>
                                        <option value="NY PO">NY PO</option>
                                    </select>
                                </div>
                                <div class="po-monitor-field">
                                    <label>NY PO REF</label>
                                    <input type="text" id="po-monitor-batch-po-ny-ref" class="form-control" placeholder="Opsional, contoh NY-123">
                                </div>
                                <div class="po-monitor-field">
                                    <label>NO PO</label>
                                    <input type="text" id="po-monitor-batch-po-number-new" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>TGL PO</label>
                                    <input type="date" id="po-monitor-batch-po-date" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>PO VALUE</label>
                                    <input type="text" id="po-monitor-batch-po-value" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>PO FINAL VALUE</label>
                                    <input type="text" id="po-monitor-batch-po-final-value" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>PO TERM</label>
                                    <input type="text" id="po-monitor-batch-po-term" class="form-control" placeholder="100 atau 50:50 atau 30:30:40">
                                </div>
                                <div class="po-monitor-field">
                                    <label>TYPE PROJECT</label>
                                    <input type="text" id="po-monitor-batch-po-type-project" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>NO PO SUB</label>
                                    <input type="text" id="po-monitor-batch-po-sub" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>REGIONAL</label>
                                    <input type="text" id="po-monitor-batch-po-regional" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>KOTA PO</label>
                                    <input type="text" id="po-monitor-batch-po-city" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>DETAIL PO</label>
                                    <input type="text" id="po-monitor-batch-po-detail" class="form-control">
                                </div>
                                <div class="po-monitor-field">
                                    <label>REMARKS</label>
                                    <input type="text" id="po-monitor-batch-po-remarks" class="form-control">
                                </div>
                                <div class="po-monitor-field d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary btn-block" id="po-monitor-batch-po-add-row">Tambah Row</button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="po-monitor-batch-po-paste-pane" role="tabpanel">
                            <div class="form-group">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:8px;">
                                    <label class="mb-0">Data PO</label>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        <a href="<?= site_url('PO_Monitor/download_ny_po_reference') ?>" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Download NY PO Reference
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" id="po-monitor-batch-po-copy-example">Copy Contoh</button>
                                    </div>
                                </div>
                                <textarea id="po-monitor-batch-po-paste" class="form-control po-monitor-batch-paste" placeholder="NY PO REF[TAB]BOWHEER[TAB]STATUS PO[TAB]NO PO[TAB]NO PO SUB[TAB]REGIONAL[TAB]KOTA PO[TAB]DETAIL PO[TAB]REMARKS[TAB]TYPE PROJECT[TAB]TGL PO[TAB]PO VALUE[TAB]PO FINAL VALUE[TAB]PO TERM&#10;[TAB]PT BANGTELINDO[TAB]ON PO[TAB]PO. 123[TAB]-[TAB]JABODETABEK[TAB]Jakarta[TAB]Detail pekerjaan[TAB]Catatan[TAB]-[TAB]2026-07-10[TAB]100000000[TAB][TAB]50:50"></textarea>
                                <small class="form-text text-muted">Bisa paste dengan header CSV/Excel. NY PO REF opsional; jika ref sudah terhubung ke PO lain, submit akan replace ke PO tujuan.</small>
                            </div>
                            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                <button type="button" class="btn btn-outline-secondary" id="po-monitor-batch-po-parse-paste">Preview PO</button>
                                <button type="button" class="btn btn-outline-danger" id="po-monitor-batch-po-clear-list" disabled>Hapus List</button>
                            </div>
                        </div>
                    </div>

                    <div class="po-monitor-batch-summary">
                        <div class="po-monitor-batch-summary-card">
                            <span class="po-monitor-batch-summary-card__label">Total Row <span class="po-monitor-batch-summary-card__count" id="po-monitor-batch-po-summary-count">0</span></span>
                            <span class="po-monitor-batch-summary-card__value" id="po-monitor-batch-po-summary-value">0</span>
                        </div>
                        <div class="po-monitor-batch-summary-card po-monitor-batch-summary-card--total">
                            <span class="po-monitor-batch-summary-card__label">Total Term <span class="po-monitor-batch-summary-card__count" id="po-monitor-batch-po-summary-term-count">0</span></span>
                            <span class="po-monitor-batch-summary-card__value">OPEN</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="po-monitor-batch-po-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>NY REF</th>
                                    <th>BOWHEER</th>
                                    <th>NO PO</th>
                                    <th>TGL PO</th>
                                    <th>VALUE</th>
                                    <th>TERM</th>
                                    <th>PREVIEW TERM</th>
                                    <th>REGIONAL/LOKASI/DETAIL</th>
                                    <th>STATUS</th>
                                    <th style="width:80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="po-monitor-batch-po-empty-row">
                                    <td colspan="11" class="text-center text-muted">Belum ada row PO.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="text-muted small">Jika match ke NY PO, target otomatis pindah dari NY PO ke ON PO.</div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-dark" id="po-monitor-batch-po-submit" disabled>Simpan Batch PO</button>
                    </div>
                </div>
            </form>
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
                            <input type="date" name="invoice_date" id="po-monitor-batch-invoice-date" class="form-control">
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
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap:8px;">
                                    <label class="mb-0">Data Invoice</label>
                                    <button type="button" class="btn btn-sm btn-outline-info" id="po-monitor-batch-copy-example">Copy Contoh</button>
                                </div>
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
        var poMonitorBatchTerminLookup = {};
        var poMonitorBatchLookupUrl = <?= json_encode(site_url('PO_Monitor/batch_invoice_lookup'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        var poMonitorBatchInvoiceExampleText = <?php
            $poMonitorBatchInvoiceExampleRows = [
                ['PO Number', 'Term', 'Nilai Invoice'],
                ['PO. 8000138637', '1', '1000000'],
                ['PO. 8000138638', '2', ''],
            ];
            echo json_encode(implode("\n", array_map(static function ($row) {
                return implode("\t", $row);
            }, $poMonitorBatchInvoiceExampleRows)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        ?>;
        var poMonitorBatchPoExampleText = <?php
            $poMonitorExampleBowheers = array_values($uniqueBowheer ?? []);
            $poMonitorBatchPoExampleRows = [
                ['NY PO REF', 'BOWHEER', 'STATUS PO', 'NO PO', 'NO PO SUB', 'REGIONAL', 'KOTA PO', 'DETAIL PO', 'REMARKS', 'TYPE PROJECT', 'TGL PO', 'PO VALUE', 'PO FINAL VALUE', 'PO TERM'],
            ];
            for ($poMonitorExampleIndex = 1; $poMonitorExampleIndex <= 5; $poMonitorExampleIndex++) {
                $poMonitorExampleBowheer = $poMonitorExampleBowheers[$poMonitorExampleIndex - 1] ?? 'PT CONTOH BOWHEER';
                $poMonitorBatchPoExampleRows[] = [
                    '',
                    $poMonitorExampleBowheer,
                    $poMonitorExampleIndex % 2 === 0 ? 'NY PO' : 'ON PO',
                    'CONTOH-PO-' . date('Ymd') . '-' . str_pad((string) $poMonitorExampleIndex, 2, '0', STR_PAD_LEFT),
                    $poMonitorExampleIndex % 2 === 0 ? 'SUB-' . $poMonitorExampleIndex : '-',
                    'REGIONAL ' . (($poMonitorExampleIndex % 5) + 1),
                    ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar'][$poMonitorExampleIndex - 1],
                    'Detail pekerjaan contoh ' . $poMonitorExampleIndex,
                    'Catatan contoh',
                    $poMonitorExampleIndex % 2 === 0 ? 'FIBERIZATION' : 'NRO',
                    date('Y-m-d'),
                    (string) (100000000 + (($poMonitorExampleIndex - 1) * 25000000)),
                    $poMonitorExampleIndex % 2 === 0 ? (string) (120000000 + (($poMonitorExampleIndex - 1) * 25000000)) : '',
                    $poMonitorExampleIndex % 2 === 0 ? '50:50' : '100',
                ];
            }
            echo json_encode(implode("\n", array_map(static function ($row) {
                return implode("\t", $row);
            }, $poMonitorBatchPoExampleRows)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
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

            normalized = normalized.replace(/\s+/g, '').replace(/[^\d,.-]/g, '');
            var lastDot = normalized.lastIndexOf('.');
            var lastComma = normalized.lastIndexOf(',');
            if (lastDot >= 0 && lastComma >= 0) {
                var lastSeparator = Math.max(lastDot, lastComma);
                var decimalDigits = normalized.length - lastSeparator - 1;
                if (decimalDigits > 0 && decimalDigits <= 2) {
                    normalized = lastDot > lastComma
                        ? normalized.replace(/,/g, '')
                        : normalized.replace(/\./g, '').replace(/,/g, '.');
                } else {
                    normalized = normalized.replace(/[,.]/g, '');
                }
            } else if (lastComma >= 0) {
                var commaParts = normalized.split(',');
                var commaLast = commaParts[commaParts.length - 1] || '';
                normalized = commaParts.length > 2 || commaLast.length === 3
                    ? normalized.replace(/,/g, '')
                    : normalized.replace(/,/g, '.');
            } else if (lastDot >= 0) {
                var dotParts = normalized.split('.');
                var dotLast = dotParts[dotParts.length - 1] || '';
                if (dotParts.length > 2 || dotLast.length === 3) {
                    normalized = normalized.replace(/\./g, '');
                }
            }
            normalized = normalized.replace(/[^\d.-]/g, '');
            var parsed = parseFloat(normalized);
            return isNaN(parsed) ? 0 : parsed;
        }

        function formatLocaleNumber(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatLocalePercent(value) {
            return formatLocaleNumber(value) + '%';
        }

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showCopyButtonCheck($button) {
            var originalHtml = $button.data('original-html');
            if (!originalHtml) {
                originalHtml = $button.html();
                $button.data('original-html', originalHtml);
            }
            window.clearTimeout($button.data('copy-reset-timer'));
            $button
                .removeClass('btn-outline-info')
                .addClass('btn-success')
                .html('<i class="fas fa-check mr-1"></i> Copied');
            $button.data('copy-reset-timer', window.setTimeout(function() {
                $button
                    .removeClass('btn-success')
                    .addClass('btn-outline-info')
                    .html(originalHtml);
            }, 1200));
        }

        function copyTextWithFallback(text) {
            text = String(text || '');
            function fallbackCopy() {
                var $temp = $('<textarea readonly></textarea>').css({
                    position: 'fixed',
                    left: '-9999px',
                    top: '0'
                }).val(text);
                $('body').append($temp);
                $temp[0].select();
                var copied = document.execCommand('copy');
                $temp.remove();
                return copied ? Promise.resolve() : Promise.reject(new Error('Copy failed'));
            }

            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text).catch(fallbackCopy);
            }

            return fallbackCopy();
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

                $('#summary_total_po, #summary_total_po_hero').text(totals.acceleration_target_2026 || '-');
                $('#summary_done_invoice, #summary_done_invoice_hero').text(totals.done_inv_2026 || '-');
                $('#summary_target_week, #summary_target_week_hero').text(totals.outs_2026_on_target || '-');
                $('#summary_carry_over, #summary_carry_over_hero').text(totals.ny_po_on_target_2026 || '-');

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

            if (selector === '#table_po_monitor_list') {
                columnDefs.push(
                    { targets: [0, 6, 7], className: 'text-center' },
                    { targets: [3, 4, 5], className: 'text-right' },
                    { targets: [7], orderable: false, searchable: false }
                );
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
                            d.start = 0;
                            d.length = -1;
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
                tableOptions.paging = false;
                tableOptions.info = false;
                tableOptions.lengthChange = false;
            }

            if (selector === '#table_po_monitor_list') {
                tableOptions.pageLength = 10;
                tableOptions.lengthMenu = [[10], ['10 row']];
                tableOptions.language = {
                    search: '',
                    searchPlaceholder: 'Cari PO monitor',
                    lengthMenu: '_MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: {
                        previous: 'Previous',
                        next: 'Next'
                    },
                    processing: 'Loading...'
                };
                tableOptions.initComplete = function() {
                    var $wrapper = $(selector).closest('.dataTables_wrapper');
                    $wrapper.find('.dataTables_filter input').attr('placeholder', 'Cari PO monitor');
                    $wrapper.find('.dataTables_length select').attr('aria-label', 'Jumlah row');
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
            $('#po_compare_panel')
                .insertAfter($('#table_po_dashboard_excel').closest('.po-monitor-panel'))
                .show();

            var poBreakdownState = {
                mode: 'project',
                tables: {}
            };
            var poBreakdownUrls = {
                datatable: '<?= site_url('PO_Monitor/breakdown_datatable') ?>',
                options: '<?= site_url('PO_Monitor/breakdown_options') ?>',
                detail: '<?= site_url('PO_Monitor/breakdown_detail') ?>'
            };
            var poBreakdownFilterConfig = [
                { id: 'project', selector: '#po_breakdown_filter_project', optionKey: 'projects', label: 'Project' },
                { id: 'pic', selector: '#po_breakdown_filter_pic', optionKey: 'pics', label: 'PIC' },
                { id: 'regional', selector: '#po_breakdown_filter_regional', optionKey: 'regionals', label: 'Regional' },
                { id: 'area', selector: '#po_breakdown_filter_area', optionKey: 'areas', label: 'Kota' },
                { id: 'month', selector: '#po_breakdown_filter_month', optionKey: 'months', label: 'Tahun - Bulan' },
                { id: 'week', selector: '#po_breakdown_filter_week', optionKey: 'weeks', label: 'Week' }
            ];

            function poBreakdownSelectValues(selector) {
                var value = $(selector).val() || [];
                return Array.isArray(value) ? value : (value ? [value] : []);
            }

            function poBreakdownFilters() {
                return {
                    project: poBreakdownSelectValues('#po_breakdown_filter_project'),
                    pic: poBreakdownSelectValues('#po_breakdown_filter_pic'),
                    regional: poBreakdownSelectValues('#po_breakdown_filter_regional'),
                    area: poBreakdownSelectValues('#po_breakdown_filter_area'),
                    month: poBreakdownSelectValues('#po_breakdown_filter_month'),
                    week: poBreakdownSelectValues('#po_breakdown_filter_week'),
                    search: String($('#po_breakdown_search').val() || '').toLowerCase(),
                    limit: parseInt($('#po_breakdown_limit').val() || '10', 10),
                    invoicedOnly: $('#po_breakdown_invoiced_only').is(':checked')
                };
            }

            function poBreakdownSelectedLabels(selector) {
                return $(selector).find('option:selected').map(function() {
                    return $.trim($(this).text() || $(this).val() || '');
                }).get().filter(function(label) {
                    return label !== '';
                });
            }

            function poBreakdownChipHtml(label, value) {
                return '<span class="po-breakdown-filter-chip" title="' + escapeHtml(label + ': ' + value) + '">' +
                    '<i class="fas fa-check-circle"></i><span>' + escapeHtml(label) + ': ' + escapeHtml(value) + '</span></span>';
            }

            function poBreakdownRenderActiveFilters() {
                var chips = [];
                poBreakdownFilterConfig.forEach(function(config) {
                    poBreakdownSelectedLabels(config.selector).forEach(function(value) {
                        chips.push(poBreakdownChipHtml(config.label, value));
                    });
                });

                var searchText = $.trim(String($('#po_breakdown_search').val() || ''));
                if (searchText !== '') {
                    chips.push(poBreakdownChipHtml('Search', searchText));
                }
                if ($('#po_breakdown_invoiced_only').is(':checked')) {
                    chips.push(poBreakdownChipHtml('Status', 'Invoiced Only'));
                }

                $('#po_breakdown_active_filters')
                    .toggleClass('has-filter', chips.length > 0)
                    .html(chips.join(''));
            }

            function poBreakdownColumns(mode) {
                if (mode === 'project') {
                    return ['No', 'Project', 'PIC', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Status', 'Detail'];
                }
                if (mode === 'pic') {
                    return ['No', 'PIC', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Status', 'Detail'];
                }
                if (mode === 'regional') {
                    return ['No', 'Regional', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Status', 'Detail'];
                }
                if (mode === 'area') {
                    return ['No', 'Regional', 'Kota / Area', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Status', 'Detail'];
                }
                if (mode === 'date') {
                    return ['No', 'Tanggal', 'Bulan', 'Week', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Project', 'Area', 'Status', 'Detail'];
                }
                return ['No', 'Bulan', 'Week', 'Target', 'Achieved', 'Outstanding', 'Achieved %', 'Project', 'Area', 'Status', 'Detail'];
            }

            function poBreakdownRequestData(extra) {
                var filters = poBreakdownFilters();
                return $.extend({
                    project: filters.project,
                    pic: filters.pic,
                    regional: filters.regional,
                    area: filters.area,
                    month: filters.month,
                    week: filters.week,
                    breakdown_search: filters.search,
                    invoiced_only: filters.invoicedOnly ? 1 : 0
                }, extra || {});
            }

            function poBreakdownEnsureHeader(mode) {
                var $table = $('.po-breakdown-table[data-breakdown-table="' + mode + '"]');
                if (!$table.length) return;

                var columns = poBreakdownColumns(mode);
                $table.find('thead').html('<tr>' + columns.map(function(column) {
                    return '<th>' + escapeHtml(column) + '</th>';
                }).join('') + '</tr>');
            }

            function poBreakdownFooter(mode, totals) {
                totals = totals || { label: 'Total', target: 'RP. 0', achieved: 'RP. 0', outstanding: 'RP. 0', percent: '0,0 %' };
                if (mode === 'project') {
                    return '<tr><th colspan="3">' + totals.label + '</th><th>' + totals.target + '</th><th>' + totals.achieved + '</th><th>' + totals.outstanding + '</th><th>' + totals.percent + '</th><th></th><th></th></tr>';
                }
                if (mode === 'area') {
                    return '<tr><th colspan="3">' + totals.label + '</th><th>' + totals.target + '</th><th>' + totals.achieved + '</th><th>' + totals.outstanding + '</th><th>' + totals.percent + '</th><th></th><th></th></tr>';
                }
                if (mode === 'period') {
                    return '<tr><th colspan="3">' + totals.label + '</th><th>' + totals.target + '</th><th>' + totals.achieved + '</th><th>' + totals.outstanding + '</th><th>' + totals.percent + '</th><th></th><th></th><th></th><th></th></tr>';
                }
                if (mode === 'date') {
                    return '<tr><th colspan="4">' + totals.label + '</th><th>' + totals.target + '</th><th>' + totals.achieved + '</th><th>' + totals.outstanding + '</th><th>' + totals.percent + '</th><th></th><th></th><th></th><th></th></tr>';
                }
                return '<tr><th colspan="2">' + totals.label + '</th><th>' + totals.target + '</th><th>' + totals.achieved + '</th><th>' + totals.outstanding + '</th><th>' + totals.percent + '</th><th></th><th></th></tr>';
            }

            function poBreakdownTable(mode) {
                if (poBreakdownState.tables[mode]) {
                    return poBreakdownState.tables[mode];
                }

                poBreakdownEnsureHeader(mode);
                var selector = '.po-breakdown-table[data-breakdown-table="' + mode + '"]';
                var table = $(selector).DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ordering: false,
                    lengthChange: false,
                    autoWidth: false,
                    pageLength: poBreakdownFilters().limit,
                    ajax: {
                        url: poBreakdownUrls.datatable,
                        type: 'POST',
                        data: function(data) {
                            return $.extend(data, poBreakdownRequestData({ mode: mode }));
                        }
                    },
                    language: {
                        emptyTable: 'Tidak ada data.',
                        processing: 'Memuat breakdown...',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        infoEmpty: 'Showing 0 to 0 of 0 entries',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        }
                    },
                    drawCallback: function() {
                        var json = this.api().ajax.json() || {};
                        $(selector).find('tfoot').html(poBreakdownFooter(mode, json.totals));
                    }
                });

                poBreakdownState.tables[mode] = table;
                return table;
            }

            function poBreakdownReload(activeOnly) {
                poBreakdownRenderActiveFilters();
                var limit = poBreakdownFilters().limit;
                if (activeOnly) {
                    poBreakdownTable(poBreakdownState.mode).page.len(limit).draw();
                    return;
                }

                Object.keys(poBreakdownState.tables).forEach(function(mode) {
                    poBreakdownState.tables[mode].page.len(limit).ajax.reload(null, true);
                });
                if (!poBreakdownState.tables[poBreakdownState.mode]) {
                    poBreakdownTable(poBreakdownState.mode);
                }
            }

            function poBreakdownUpdateOptions(activeSelector) {
                var except = '';
                poBreakdownFilterConfig.some(function(config) {
                    if (config.selector === activeSelector) {
                        except = config.id;
                        return true;
                    }
                    return false;
                });

                $.ajax({
                    url: poBreakdownUrls.options,
                    type: 'POST',
                    dataType: 'json',
                    data: poBreakdownRequestData({ except: except }),
                    success: function(response) {
                        var options = response && response.options ? response.options : {};
                        poBreakdownFilterConfig.forEach(function(config) {
                            if (activeSelector && config.selector === activeSelector) {
                                return;
                            }
                            var $select = $(config.selector);
                            var selected = poBreakdownSelectValues(config.selector);
                            var validValues = {};
                            var html = (options[config.optionKey] || []).map(function(option) {
                                validValues[String(option.value)] = true;
                                return '<option value="' + escapeHtml(option.value) + '">' + escapeHtml(option.label) + '</option>';
                            }).join('');
                            var validSelected = selected.filter(function(value) {
                                return validValues[String(value)];
                            });
                            $select.html(html).val(validSelected).trigger('change.select2');
                        });
                    }
                });
            }

            function poBreakdownRenderDetail(mode, key) {
                var $modal = $('#po_breakdown_detail_modal');
                $modal.find('.modal-title').html('<span class="po-monitor-modal-eyebrow">Breakdown Detail</span>Memuat detail...');
                $modal.find('.modal-body').html('<div class="text-muted">Memuat detail...</div>');
                $modal.modal('show');

                $.ajax({
                    url: poBreakdownUrls.detail,
                    type: 'POST',
                    dataType: 'json',
                    data: poBreakdownRequestData({ mode: mode, key: key }),
                    success: function(response) {
                        $modal.find('.modal-title').html(response && response.title ? response.title : '<span class="po-monitor-modal-eyebrow">Breakdown Detail</span>Detail Target Invoice');
                        $modal.find('.modal-body').html(response && response.html ? response.html : '<div class="alert alert-info mb-0">Tidak ada detail.</div>');
                    },
                    error: function() {
                        $modal.find('.modal-body').html('<div class="alert alert-danger mb-0">Gagal memuat detail breakdown.</div>');
                    }
                });
            }

            $('.po-breakdown-filter-select').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#po_breakdown_filter_modal'),
                dropdownCssClass: 'po-breakdown-select2-dropdown',
                allowClear: true,
                closeOnSelect: false
            });

            $('#po_breakdown_apply').off('click.poBreakdown').on('click.poBreakdown', function() {
                poBreakdownReload(false);
                $('#po_breakdown_filter_modal').modal('hide');
            });
            $('#po_breakdown_reset').off('click.poBreakdown').on('click.poBreakdown', function() {
                $('#po_breakdown_filter_project, #po_breakdown_filter_pic, #po_breakdown_filter_regional, #po_breakdown_filter_area, #po_breakdown_filter_month, #po_breakdown_filter_week')
                    .val([])
                    .trigger('change.select2');
                $('#po_breakdown_search').val('');
                $('#po_breakdown_invoiced_only').prop('checked', false);
                $('#po_breakdown_limit').val('10');
                poBreakdownUpdateOptions('');
                poBreakdownReload(false);
            });
            $('#po_breakdown_filter_project, #po_breakdown_filter_pic, #po_breakdown_filter_regional, #po_breakdown_filter_area, #po_breakdown_filter_month, #po_breakdown_filter_week').off('change.poBreakdown').on('change.poBreakdown', function(event) {
                poBreakdownRenderActiveFilters();
                poBreakdownUpdateOptions('#' + event.currentTarget.id);
            });
            $('#po_breakdown_limit, #po_breakdown_invoiced_only').off('change.poBreakdown').on('change.poBreakdown', function() {
                poBreakdownReload(false);
            });
            var poBreakdownSearchTimer = null;
            $('#po_breakdown_search').off('input.poBreakdown').on('input.poBreakdown', function() {
                poBreakdownRenderActiveFilters();
                window.clearTimeout(poBreakdownSearchTimer);
                poBreakdownSearchTimer = window.setTimeout(function() {
                    poBreakdownReload(false);
                }, 250);
            });
            $('.po-breakdown-tabs .nav-link').off('shown.bs.tab.poBreakdown').on('shown.bs.tab.poBreakdown', function() {
                poBreakdownState.mode = $(this).data('breakdown-mode') || 'project';
                poBreakdownTable(poBreakdownState.mode).columns.adjust().draw(false);
            });
            $(document).off('click.poBreakdownDetail', '.js-po-breakdown-detail').on('click.poBreakdownDetail', '.js-po-breakdown-detail', function() {
                poBreakdownRenderDetail($(this).data('breakdown-mode'), $(this).data('breakdown-key'));
            });
            $('#po_breakdown_filter_modal').off('shown.bs.modal.poBreakdown').on('shown.bs.modal.poBreakdown', function() {
                poBreakdownUpdateOptions('');
            });
            poBreakdownUpdateOptions('');
            poBreakdownRenderActiveFilters();
            poBreakdownTable('project');

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

            function ensureBatchInvoiceLookup(poNumbers) {
                var pending = [];
                var seen = {};
                (poNumbers || []).forEach(function(poNumber) {
                    var cleanPo = String(poNumber || '').trim();
                    var key = cleanPo.toUpperCase();
                    if (cleanPo === '' || seen[key] || poMonitorBatchTerminLookup[key]) {
                        return;
                    }
                    seen[key] = true;
                    pending.push(cleanPo);
                });

                if (!pending.length) {
                    return $.Deferred().resolve().promise();
                }

                return $.ajax({
                    url: poMonitorBatchLookupUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { po_numbers: pending }
                }).then(function(response) {
                    var lookup = response && response.lookup ? response.lookup : {};
                    Object.keys(lookup).forEach(function(key) {
                        poMonitorBatchTerminLookup[String(key).toUpperCase()] = lookup[key];
                    });
                });
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

            function getBatchInvoiceRemaining(poNumber, termNo) {
                var poKey = String(poNumber || '').trim().toUpperCase();
                var lookup = poMonitorBatchTerminLookup[poKey] || null;
                if (!lookup || !lookup.terms || !lookup.terms[String(termNo)]) {
                    return null;
                }

                var key = poKey + '|' + termNo;
                var remaining = Number(lookup.terms[String(termNo)].remaining || 0) - getBatchCurrentUsed(key);
                return remaining > 0 ? remaining : 0;
            }

            function syncBatchInvoiceAutoAmount(force) {
                var $amount = $('#po-monitor-batch-invoice-value');
                if (!force && String($amount.val() || '').trim() !== '') {
                    return;
                }

                var termNo = normalizeBatchTerm($('#po-monitor-batch-term-no').val());
                var remaining = getBatchInvoiceRemaining($('#po-monitor-batch-po-number').val(), termNo);
                if (remaining === null) {
                    if (force) {
                        $amount.val('');
                    }
                    return;
                }

                $amount.val(remaining > 0 ? formatLocaleNumber(remaining) : '');
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
                var $button = $(this);
                var poNumber = $('#po-monitor-batch-po-number').val();
                var termNo = $('#po-monitor-batch-term-no').val();
                var invoiceValue = $('#po-monitor-batch-invoice-value').val();
                $button.prop('disabled', true);
                syncBatchInvoiceAutoAmount(false);
                ensureBatchInvoiceLookup([poNumber]).always(function() {
                    var added = addBatchInvoiceRow(poNumber, termNo, invoiceValue);
                    if (added) {
                        $('#po-monitor-batch-po-number').val('').focus();
                        $('#po-monitor-batch-invoice-value').val('');
                    }
                    $button.prop('disabled', false);
                });
            });

            $('#po-monitor-batch-po-number, #po-monitor-batch-term-no')
                .off('input.poMonitorBatchAuto change.poMonitorBatchAuto blur.poMonitorBatchAuto')
                .on('input.poMonitorBatchAuto change.poMonitorBatchAuto blur.poMonitorBatchAuto', function() {
                    syncBatchInvoiceAutoAmount(true);
                });

            $('#po-monitor-batch-invoice-value').off('input.poMonitorBatchFormat').on('input.poMonitorBatchFormat', function() {
                var value = $(this).val();
                if (value === '') {
                    return;
                }
                $(this).val(formatLocaleNumber(parseLocaleNumber(value)));
            });

            $('#po-monitor-batch-copy-example').off('click.poMonitorBatchCopyExample').on('click.poMonitorBatchCopyExample', function() {
                var $button = $(this);
                copyTextWithFallback(poMonitorBatchInvoiceExampleText).then(function() {
                    showCopyButtonCheck($button);
                });
            });

            $('#po-monitor-batch-parse-paste').off('click.poMonitorBatchPaste').on('click.poMonitorBatchPaste', function() {
                var $button = $(this);
                var text = $('#po-monitor-batch-paste').val();
                var rows = [];
                var poNumbers = [];
                String(text || '').split(/\r?\n/).forEach(function(line, index) {
                    if (!line.trim()) {
                        return;
                    }
                    var columns = line.split(/\t/);
                    if (columns.length < 2) {
                        columns = line.split(/[;,]/);
                    }
                    if (index === 0 && String(columns[0] || '').trim().toUpperCase() === 'PO NUMBER') {
                        return;
                    }
                    rows.push([columns[0] || '', columns[1] || '', columns.length >= 3 ? columns.slice(2).join(' ') : '']);
                    poNumbers.push(columns[0] || '');
                });

                $button.prop('disabled', true);
                ensureBatchInvoiceLookup(poNumbers).always(function() {
                    rows.forEach(function(row) {
                        addBatchInvoiceRow(row[0], row[1], row[2]);
                    });
                    updateBatchInvoiceState();
                    $button.prop('disabled', false);
                });
            });

            $('#po-monitor-batch-clear-list').off('click.poMonitorBatchClear').on('click.poMonitorBatchClear', function() {
                $('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row').remove();
                updateBatchInvoiceState();
                syncBatchInvoiceAutoAmount(true);
            });

            $(document).off('click.poMonitorBatchRemove', '.po-monitor-batch-remove-row').on('click.poMonitorBatchRemove', '.po-monitor-batch-remove-row', function() {
                $(this).closest('tr').remove();
                updateBatchInvoiceState();
                syncBatchInvoiceAutoAmount(true);
            });

            $('#po-monitor-batch-invoice-form').off('submit.poMonitorBatchSubmit').on('submit.poMonitorBatchSubmit', function(e) {
                if (!String($('#po-monitor-batch-invoice-date').val() || '').trim()) {
                    e.preventDefault();
                    alert('Tanggal Invoice General wajib dipilih.');
                    $('#po-monitor-batch-invoice-date').focus();
                    return false;
                }

                if ($('#po-monitor-batch-invoice-table tbody tr.po-monitor-batch-row[data-valid="1"]').length === 0) {
                    e.preventDefault();
                    alert('Belum ada row invoice yang valid.');
                    return false;
                }
                return true;
            });

            updateBatchInvoiceState();

            function normalizeBatchPoHeader(value) {
                return String(value || '').toUpperCase().replace(/\s+/g, ' ').trim();
            }

            function normalizeBatchPoTerm(term) {
                var parts = String(term || '').split(/\s*:\s*/).map(function(part) {
                    var match = String(part || '').match(/-?\d+(?:[.,]\d+)?/);
                    if (!match) {
                        return 0;
                    }
                    return Math.max(0, Math.min(100, parseFloat(match[0].replace(',', '.')) || 0));
                }).filter(function(value) {
                    return value > 0;
                }).slice(0, 5);

                return parts.length ? parts : [100];
            }

            function buildBatchPoTermPreview(term, value) {
                var percents = normalizeBatchPoTerm(term);
                var sum = percents.reduce(function(total, item) { return total + item; }, 0) || 100;
                var remaining = Number(value || 0);
                return percents.map(function(percent, index) {
                    var normalizedPercent = (percent / sum) * 100;
                    var amount = index === percents.length - 1 ? remaining : Math.round(((Number(value || 0) * normalizedPercent) / 100) * 100) / 100;
                    remaining -= amount;
                    return 'T' + (index + 1) + ' ' + Math.round(normalizedPercent) + '%: ' + formatLocaleNumber(amount);
                }).join('<br>');
            }

            function updateBatchPoState() {
                var count = 0;
                var totalValue = 0;
                var totalTerms = 0;
                var rowNo = 1;
                $('#po-monitor-batch-po-table tbody tr.po-monitor-batch-po-row').each(function() {
                    var $row = $(this);
                    $row.find('td:first').text(rowNo++);
                    count++;
                    totalValue += Number($row.data('effective-value') || 0);
                    totalTerms += Number($row.data('term-count') || 0);
                });

                $('#po-monitor-batch-po-summary-count').text(count);
                $('#po-monitor-batch-po-summary-value').text(formatLocaleNumber(totalValue));
                $('#po-monitor-batch-po-summary-term-count').text(totalTerms);
                $('#po-monitor-batch-po-submit').prop('disabled', count === 0);
                $('#po-monitor-batch-po-clear-list').prop('disabled', count === 0);
                $('.po-monitor-batch-po-empty-row').toggle(count === 0);
            }

            function addBatchPoRow(row) {
                row = row || {};
                var bowheer = String(row.bowheer || '').trim();
                var poNumber = String(row.po_number || '').trim();
                var poValue = parseLocaleNumber(row.po_value || 0);
                var poFinalValue = parseLocaleNumber(row.po_final_value || 0);
                var effectiveValue = poFinalValue > 0 ? poFinalValue : poValue;
                var term = String(row.po_term || '').trim() || '100';
                var termCount = normalizeBatchPoTerm(term).length;
                var isValid = bowheer !== '' && poNumber !== '' && effectiveValue > 0;

                var fields = ['ny_po_ref', 'bowheer', 'status_po', 'po_number', 'no_po_sub', 'regional', 'kota_po', 'detail_po', 'remarks', 'type_project', 'po_date', 'po_value', 'po_final_value', 'po_term'];
                var hiddenInputs = '';
                if (isValid) {
                    fields.forEach(function(field) {
                        hiddenInputs += '<input type="hidden" name="' + field + '[]" value="' + escapeHtml(row[field] || '') + '">';
                    });
                }

                var html = '<tr class="po-monitor-batch-po-row ' + (isValid ? 'table-success' : 'table-danger') + '" data-effective-value="' + escapeHtml(effectiveValue) + '" data-term-count="' + escapeHtml(termCount) + '">' +
                    '<td></td>' +
                    '<td>' + escapeHtml(row.ny_po_ref || '-') + '</td>' +
                    '<td>' + escapeHtml(bowheer) + hiddenInputs + '</td>' +
                    '<td>' + escapeHtml(poNumber) + '</td>' +
                    '<td>' + escapeHtml(row.po_date || '-') + '</td>' +
                    '<td class="text-right">' + formatLocaleNumber(effectiveValue) + '</td>' +
                    '<td>' + escapeHtml(term) + '</td>' +
                    '<td>' + buildBatchPoTermPreview(term, effectiveValue) + '</td>' +
                    '<td>' + escapeHtml([row.no_po_sub, row.regional, row.kota_po, row.detail_po, row.remarks].filter(Boolean).join(' | ') || '-') + '</td>' +
                    '<td><span class="badge badge-' + (isValid ? 'success' : 'danger') + '">' + (isValid ? 'Valid' : 'Wajib Bowheer, No PO, Value') + '</span></td>' +
                    '<td><button type="button" class="btn btn-sm btn-outline-danger po-monitor-batch-po-remove-row">Hapus</button></td>' +
                    '</tr>';

                $('#po-monitor-batch-po-table tbody').append(html);
                updateBatchPoState();
                return isValid;
            }

            function readBatchPoManualRow() {
                return {
                    ny_po_ref: $('#po-monitor-batch-po-ny-ref').val(),
                    bowheer: $('#po-monitor-batch-po-bowheer').val(),
                    status_po: $('#po-monitor-batch-po-status').val(),
                    po_number: $('#po-monitor-batch-po-number-new').val(),
                    no_po_sub: $('#po-monitor-batch-po-sub').val(),
                    regional: $('#po-monitor-batch-po-regional').val(),
                    kota_po: $('#po-monitor-batch-po-city').val(),
                    detail_po: $('#po-monitor-batch-po-detail').val(),
                    remarks: $('#po-monitor-batch-po-remarks').val(),
                    type_project: $('#po-monitor-batch-po-type-project').val(),
                    po_date: $('#po-monitor-batch-po-date').val(),
                    po_value: $('#po-monitor-batch-po-value').val(),
                    po_final_value: $('#po-monitor-batch-po-final-value').val(),
                    po_term: $('#po-monitor-batch-po-term').val()
                };
            }

            $('#po-monitor-batch-po-add-row').off('click.poMonitorBatchPo').on('click.poMonitorBatchPo', function() {
                if (addBatchPoRow(readBatchPoManualRow())) {
                    $('#po-monitor-batch-po-ny-ref, #po-monitor-batch-po-number-new, #po-monitor-batch-po-sub, #po-monitor-batch-po-regional, #po-monitor-batch-po-city, #po-monitor-batch-po-detail, #po-monitor-batch-po-remarks, #po-monitor-batch-po-value, #po-monitor-batch-po-final-value').val('');
                    $('#po-monitor-batch-po-number-new').focus();
                }
            });

            $('#po-monitor-batch-po-value, #po-monitor-batch-po-final-value').off('input.poMonitorBatchPoFormat').on('input.poMonitorBatchPoFormat', function() {
                var value = $(this).val();
                if (value !== '') {
                    $(this).val(formatLocaleNumber(parseLocaleNumber(value)));
                }
            });

            $('#po-monitor-batch-po-copy-example').off('click.poMonitorBatchPoCopyExample').on('click.poMonitorBatchPoCopyExample', function() {
                var $button = $(this);
                copyTextWithFallback(poMonitorBatchPoExampleText).then(function() {
                    showCopyButtonCheck($button);
                });
            });

            $('#po-monitor-batch-po-parse-paste').off('click.poMonitorBatchPoPaste').on('click.poMonitorBatchPoPaste', function() {
                var lines = String($('#po-monitor-batch-po-paste').val() || '').split(/\r?\n/).filter(function(line) {
                    return line.trim() !== '';
                });
                if (!lines.length) {
                    return;
                }

                var defaultOrder = ['bowheer', 'status_po', 'po_number', 'no_po_sub', 'regional', 'kota_po', 'detail_po', 'remarks', 'type_project', 'po_date', 'po_value', 'po_final_value', 'po_term'];
                var defaultOrderWithRef = ['ny_po_ref', 'bowheer', 'status_po', 'po_number', 'no_po_sub', 'regional', 'kota_po', 'detail_po', 'remarks', 'type_project', 'po_date', 'po_value', 'po_final_value', 'po_term'];
                var headerMap = {
                    'NY PO REF': 'ny_po_ref',
                    'NY REF': 'ny_po_ref',
                    'NO REF': 'ny_po_ref',
                    'BOWHEER': 'bowheer',
                    'STATUS PO': 'status_po',
                    'NO PO': 'po_number',
                    'NO PO SUB': 'no_po_sub',
                    'REGIONAL': 'regional',
                    'KOTA PO': 'kota_po',
                    'DETAIL PO': 'detail_po',
                    'REMARKS': 'remarks',
                    'TYPE PROJECT': 'type_project',
                    'TGL PO': 'po_date',
                    'PO VALUE': 'po_value',
                    'PO FINAL VALUE': 'po_final_value',
                    'PO TERM': 'po_term'
                };
                var firstColumns = lines[0].split(/\t/);
                var firstHeader = firstColumns.map(normalizeBatchPoHeader);
                var hasHeader = firstHeader.some(function(item) { return headerMap[item]; });
                var columnOrder = defaultOrder;
                var startIndex = 0;

                if (hasHeader) {
                    columnOrder = firstHeader.map(function(header) {
                        return headerMap[header] || '';
                    });
                    startIndex = 1;
                } else if (firstColumns.length >= defaultOrderWithRef.length && (/^NY[\s-]*\d+$/i.test(String(firstColumns[0] || '').trim()) || String(firstColumns[0] || '').trim() === '')) {
                    columnOrder = defaultOrderWithRef;
                }

                for (var i = startIndex; i < lines.length; i++) {
                    var columns = lines[i].split(/\t/);
                    var row = {};
                    columnOrder.forEach(function(field, index) {
                        if (field) {
                            row[field] = columns[index] || '';
                        }
                    });
                    addBatchPoRow(row);
                }
            });

            $('#po-monitor-batch-po-clear-list').off('click.poMonitorBatchPoClear').on('click.poMonitorBatchPoClear', function() {
                $('#po-monitor-batch-po-table tbody tr.po-monitor-batch-po-row').remove();
                updateBatchPoState();
            });

            $(document).off('click.poMonitorBatchPoRemove', '.po-monitor-batch-po-remove-row').on('click.poMonitorBatchPoRemove', '.po-monitor-batch-po-remove-row', function() {
                $(this).closest('tr').remove();
                updateBatchPoState();
            });

            $('#po-monitor-batch-po-form').off('submit.poMonitorBatchPoSubmit').on('submit.poMonitorBatchPoSubmit', function(e) {
                if ($('#po-monitor-batch-po-table tbody tr.po-monitor-batch-po-row.table-success').length === 0) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });

            updateBatchPoState();

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
                    ordering: false,
                    footerCallback: function() {
                        updateCompareFooter(this.api());
                    }
                });
            }

            function compareCellAmount($cells, index) {
                var $cell = $cells.eq(index);
                var raw = $cell.attr('data-po-amount');
                return raw !== undefined ? parseLocaleNumber(raw) : parseLocaleNumber($cell.html());
            }

            function updateCompareFooter(api) {
                var columnCount = api.columns().count();
                var periodCount = Math.max(0, Math.floor((columnCount - 8) / 3));
                var monthTotals = [];
                var totalTarget = 0;
                var totalAchieved = 0;
                var rowCount = 0;

                for (var i = 0; i < periodCount; i++) {
                    monthTotals.push({ target: 0, achieved: 0 });
                }

                api.rows({ search: 'applied' }).every(function() {
                    var $cells = $(this.node()).children('td');
                    rowCount++;
                    totalTarget += compareCellAmount($cells, 3);

                    for (var i = 0; i < periodCount; i++) {
                        var targetIndex = 4 + (i * 3);
                        var achievedIndex = targetIndex + 1;
                        monthTotals[i].target += compareCellAmount($cells, targetIndex);
                        monthTotals[i].achieved += compareCellAmount($cells, achievedIndex);
                    }

                    totalAchieved += compareCellAmount($cells, 4 + (periodCount * 3));
                });

                var deviasi = Math.max(totalTarget - totalAchieved, 0);
                var achievedPercent = totalTarget > 0 ? (totalAchieved / totalTarget) * 100 : (totalAchieved > 0 ? 100 : 0);
                var deviasiPercent = Math.max(100 - achievedPercent, 0);
                var html = '<th colspan="3" class="po-compare-footer-label">Total (' + rowCount + ' row)</th>';
                html += '<th>' + formatLocaleNumber(totalTarget) + '</th>';

                monthTotals.forEach(function(total) {
                    var percent = total.target > 0 ? (total.achieved / total.target) * 100 : (total.achieved > 0 ? 100 : 0);
                    html += '<th>' + formatLocaleNumber(total.target) + '</th>';
                    html += '<th>' + formatLocaleNumber(total.achieved) + '</th>';
                    html += '<th>' + formatLocalePercent(percent) + '</th>';
                });

                html += '<th>' + formatLocaleNumber(totalAchieved) + '</th>';
                html += '<th>' + formatLocaleNumber(deviasi) + '</th>';
                html += '<th>' + formatLocalePercent(achievedPercent) + '</th>';
                html += '<th>' + formatLocalePercent(deviasiPercent) + '</th>';

                var $wrapper = $(api.table().container());
                var $footers = $(api.table().footer());
                $footers = $footers.add($wrapper.find('.dataTables_scrollFoot tfoot'));
                $footers.find('tr').html(html);
            }

            compareTables.month = initCompareTable('#table_po_target_invoice_compare_month');
            compareTables.week = initCompareTable('#table_po_target_invoice_compare_week');

            function loadWeekCompareTable(callback) {
                var $panel = $('#po_compare_week_panel');
                if (!$panel.length) {
                    if (typeof callback === 'function') {
                        callback(false);
                    }
                    return;
                }

                if ($panel.data('loaded') === 1) {
                    if (!compareTables.week) {
                        compareTables.week = initCompareTable('#table_po_target_invoice_compare_week');
                    }
                    if (typeof callback === 'function') {
                        callback(true);
                    }
                    return;
                }

                if ($panel.data('loading') === 1) {
                    return;
                }

                $panel.data('loading', 1).html('<div class="text-muted py-3">Memuat data week...</div>');
                $.ajax({
                    url: '<?= site_url('PO_Monitor/comparison_week_table') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        from_month: $('input[name="from_month"]').val(),
                        to_month: $('input[name="to_month"]').val()
                    }
                }).done(function(response) {
                    if (response && response.status && response.html) {
                        $panel.html(response.html).data('loaded', 1);
                        compareTables.week = initCompareTable('#table_po_target_invoice_compare_week');
                        if (compareTables.week) {
                            compareTables.week.draw();
                        }
                        if (typeof callback === 'function') {
                            callback(true);
                        }
                        return;
                    }

                    $panel.html('<div class="alert alert-warning mb-0">Data week tidak tersedia.</div>');
                    if (typeof callback === 'function') {
                        callback(false);
                    }
                }).fail(function() {
                    $panel.html('<div class="alert alert-danger mb-0">Gagal memuat data week.</div>');
                    if (typeof callback === 'function') {
                        callback(false);
                    }
                }).always(function() {
                    $panel.data('loading', 0);
                    scheduleCompareTableAdjust();
                });
            }

            function adjustCompareTables() {
                var weekMode = $('#po_compare_week_mode').is(':checked');
                var activeTable = weekMode ? compareTables.week : compareTables.month;
                if (!activeTable) {
                    return;
                }

                activeTable.columns.adjust().draw(false);
            }

            function scheduleCompareTableAdjust() {
                [0, 80, 180, 360].forEach(function(delay) {
                    window.setTimeout(adjustCompareTables, delay);
                });
            }

            $(window)
                .off('resize.poMonitorCompareAdjust')
                .on('resize.poMonitorCompareAdjust', scheduleCompareTableAdjust);

            $(document)
                .off('collapsed.lte.pushmenu.poMonitorCompareAdjust shown.lte.pushmenu.poMonitorCompareAdjust expanded.lte.pushmenu.poMonitorCompareAdjust')
                .on('collapsed.lte.pushmenu.poMonitorCompareAdjust shown.lte.pushmenu.poMonitorCompareAdjust expanded.lte.pushmenu.poMonitorCompareAdjust', scheduleCompareTableAdjust);

            if (window.ResizeObserver && document.getElementById('po-monitor-compare-body')) {
                var compareResizeObserver = new ResizeObserver(function() {
                    scheduleCompareTableAdjust();
                });
                compareResizeObserver.observe(document.getElementById('po-monitor-compare-body'));
            }

            function applyDetailFilters($modal) {
                var activeRegional = String($modal.data('active-regional-key') || '');
                var activeTerm = String($modal.data('active-term-index') || '');
                var uninvoicedOnly = $('#po-monitor-detail-uninvoiced-only').is(':checked');
                var $regionalCards = $modal.find('.po-monitor-regional-card');
                var $termCards = $modal.find('.po-monitor-term-card');
                var $groups = $modal.find('.po-monitor-regional-group');
                var regionalSummary = {};
                var termSummary = {};
                var visibleCount = 0;
                var visibleAmount = 0;

                $regionalCards.each(function() {
                    regionalSummary[String($(this).data('regional-key') || '')] = {
                        count: 0,
                        amount: 0
                    };
                });

                $termCards.each(function() {
                    termSummary[String($(this).data('term-index') || '')] = {
                        count: 0,
                        amount: 0
                    };
                });

                $regionalCards.removeClass('is-active');
                $termCards.removeClass('is-active');
                if (activeRegional !== '') {
                    $regionalCards.filter('[data-regional-key="' + activeRegional + '"]').addClass('is-active');
                }
                if (activeTerm !== '') {
                    $termCards.filter('[data-term-index="' + activeTerm + '"]').addClass('is-active');
                }

                $groups.each(function() {
                    var $group = $(this);
                    var regionalMatches = activeRegional === '' || String($group.data('regional-key')) === activeRegional;
                    var hasVisibleRows = false;
                    var visibleRowNo = 1;
                    var groupVisibleCount = 0;
                    var groupVisibleAmount = 0;

                    $group.find('.po-monitor-detail-table tbody tr').each(function() {
                        var $row = $(this);
                        var rowRegional = String($group.data('regional-key') || '');
                        var rowTerm = String($row.data('term-index') || '');
                        var rowInvoiced = String($row.data('invoiced') || '') === '1';
                        var amount = Number($row.data('filter-amount') || 0);
                        var termMatches = activeTerm === '' || rowTerm === activeTerm;
                        var invoiceMatches = !uninvoicedOnly || !rowInvoiced;
                        var visible = regionalMatches && termMatches && invoiceMatches;
                        $row.toggleClass('is-hidden', !visible);

                        if (termMatches && invoiceMatches && regionalSummary[rowRegional]) {
                            regionalSummary[rowRegional].count++;
                            regionalSummary[rowRegional].amount += amount;
                        }

                        if (regionalMatches && invoiceMatches && termSummary[rowTerm]) {
                            termSummary[rowTerm].count++;
                            termSummary[rowTerm].amount += amount;
                        }

                        if (visible) {
                            hasVisibleRows = true;
                            groupVisibleCount++;
                            groupVisibleAmount += amount;
                            visibleCount++;
                            visibleAmount += amount;
                            $row.children('td').eq(0).text(visibleRowNo++);
                        }
                    });

                    $group.find('.js-po-regional-section-row').text(formatLocaleNumber(groupVisibleCount));
                    $group.find('.js-po-regional-section-total').text(formatLocaleNumber(groupVisibleAmount));
                    var $footerTotals = $group.find('.po-monitor-detail-table tfoot th');
                    if ($footerTotals.length === 2) {
                        $footerTotals.last().text(formatLocaleNumber(groupVisibleAmount));
                    }
                    $group.toggleClass('is-hidden', !regionalMatches || !hasVisibleRows);
                });

                $regionalCards.each(function() {
                    var $card = $(this);
                    var key = String($card.data('regional-key') || '');
                    var item = regionalSummary[key] || { count: 0, amount: 0 };
                    $card.find('.po-monitor-regional-card__label b').text(formatLocaleNumber(item.count));
                    $card.find('.po-monitor-regional-card__value').text(formatLocaleNumber(item.amount));
                });

                $termCards.each(function() {
                    var $card = $(this);
                    var key = String($card.data('term-index') || '');
                    var item = termSummary[key] || { count: 0, amount: 0 };
                    $card.find('.po-monitor-term-card__label b').text(formatLocaleNumber(item.count));
                    $card.find('.po-monitor-term-card__value').text(formatLocaleNumber(item.amount));
                });

                $modal.find('.js-po-detail-total-row').text(formatLocaleNumber(visibleCount));
                $modal.find('.js-po-detail-total-amount').text(formatLocaleNumber(visibleAmount));
            }

            function resetDetailFilters($modal) {
                $modal.data('active-regional-key', '');
                $modal.data('active-term-index', '');
                var hasInvoicedTargetRows = $modal.find('.po-monitor-detail-table tbody tr[data-invoiced="1"]').length > 0;
                $('#po-monitor-detail-uninvoiced-only').prop('checked', false);
                $('#po-monitor-detail-uninvoiced-wrap').toggleClass('d-none', !hasInvoicedTargetRows);
                applyDetailFilters($modal);
            }

            function cleanDetailText(value) {
                return String(value || '').replace(/\s+/g, ' ').trim();
            }

            function detailActiveLabel($modal, selector, fallback) {
                var $active = $modal.find(selector + '.is-active').first();
                if (!$active.length) {
                    return fallback || '';
                }

                var cloned = $active.clone();
                cloned.find('b, .po-monitor-regional-card__value, .po-monitor-regional-card__caption, .po-monitor-term-card__value, .po-monitor-term-card__caption').remove();
                return cleanDetailText(cloned.text())
                    .replace(/\bTERM\b/g, 'Term')
                    .replace(/\bREGIONAL\b/g, 'Regional');
            }

            function detailExportTitle($modal) {
                var regional = detailActiveLabel($modal, '.po-monitor-regional-card', '');
                var term = detailActiveLabel($modal, '.po-monitor-term-card', '');

                if (term && regional) {
                    return 'Detail ' + term + ' - ' + regional;
                }
                if (term) {
                    return 'Detail ' + term;
                }
                if (regional) {
                    return 'Detail ' + regional;
                }

                var title = $modal.find('.modal-title').clone();
                title.find('.po-monitor-modal-eyebrow').remove();
                return cleanDetailText(title.text()) || 'Detail PO';
            }

            function firstVisibleDetailColumnValue($modal, columnName) {
                var targetIndex = -1;
                columnName = String(columnName || '').toUpperCase();

                $modal.find('.po-monitor-regional-group:not(.is-hidden) .po-monitor-detail-table').each(function() {
                    if (targetIndex >= 0) {
                        return false;
                    }

                    $(this).find('thead th').each(function(index) {
                        if (cleanDetailText($(this).text()).toUpperCase() === columnName) {
                            targetIndex = index;
                            return false;
                        }
                    });
                });

                if (targetIndex < 0) {
                    return '';
                }

                var value = '';
                $modal.find('.po-monitor-regional-group:not(.is-hidden) .po-monitor-detail-table tbody tr:not(.is-hidden)').each(function() {
                    value = cleanDetailText($(this).children('td').eq(targetIndex).text());
                    return value === '';
                });

                return value;
            }

            function detailCaptureMonthLabel($modal, fallbackPeriod) {
                var periodText = firstVisibleDetailColumnValue($modal, 'TARGET PERIOD') || fallbackPeriod || '';
                var monthNames = [
                    'JANUARI',
                    'FEBRUARI',
                    'MARET',
                    'APRIL',
                    'MEI',
                    'JUNI',
                    'JULI',
                    'AGUSTUS',
                    'SEPTEMBER',
                    'OKTOBER',
                    'NOVEMBER',
                    'DESEMBER'
                ];
                var upper = String(periodText).toUpperCase();

                for (var i = 0; i < monthNames.length; i++) {
                    if (upper.indexOf(monthNames[i]) >= 0) {
                        return monthNames[i];
                    }
                }

                return cleanDetailText(fallbackPeriod).replace(/\d{4}/g, '').trim().toUpperCase() || 'PERIODE';
            }

            function detailCaptureHeaderInfo($modal) {
                var title = $modal.find('.modal-title').clone();
                title.find('.po-monitor-modal-eyebrow').remove();
                var rawTitle = cleanDetailText(title.text()) || detailExportTitle($modal);
                var parts = rawTitle.split(/\s+-\s+/);
                var type = parts.length > 1 ? parts.shift() : 'Target';
                var period = parts.length > 1 ? parts.pop() : '';
                var project = parts.length ? parts.join(' - ') : rawTitle;
                var weekMatch = String(period).match(/Week\s+(\d+)/i);
                var monthLabel = detailCaptureMonthLabel($modal, period);
                var periodLabel = weekMatch ? (monthLabel + ' W' + parseInt(weekMatch[1], 10)) : monthLabel;
                var typeLabel = String(type).toUpperCase() === 'ACHIEVED' ? 'ACHIEVED INVOICE 2026' : 'TARGET INVOICE 2026';
                var projectLabel = cleanDetailText(project)
                    .replace(/^PT\.?\s+/i, '')
                    .replace(/\s+-\s+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .toUpperCase();
                var regionalLabel = (detailActiveLabel($modal, '.po-monitor-regional-card', 'All Regional') || 'All Regional').toUpperCase();
                var termLabel = (detailActiveLabel($modal, '.po-monitor-term-card', 'All Term') || 'All Term').toUpperCase();

                return {
                    title: typeLabel + ' - ' + periodLabel,
                    project: 'PROJECT ' + (projectLabel || 'PO'),
                    regional: regionalLabel,
                    term: termLabel,
                    generatedAt: new Date().toLocaleString('id-ID')
                };
            }

            function safeDownloadName(value, extension) {
                var base = cleanDetailText(value)
                    .replace(/[\\/:*?"<>|]+/g, '-')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '')
                    .toLowerCase();
                return (base || 'detail-po') + extension;
            }

            function cloneVisibleDetailContent($modal) {
                var $clone = $('<div class="po-monitor-export-surface"></div>');
                var title = detailExportTitle($modal);
                $clone.append('<div class="po-monitor-export-title">' + escapeHtml(title) + '</div>');
                $clone.append('<div class="po-monitor-export-subtitle">' + escapeHtml(new Date().toLocaleString('id-ID')) + '</div>');

                var bodyClone = $modal.find('.modal-body').clone();
                bodyClone.find('.is-hidden').remove();
                bodyClone.find('.po-monitor-regional-card:not(.is-active), .po-monitor-term-card:not(.is-active)').each(function() {
                    if ($(this).closest('.po-monitor-summary-band').find('.is-active').length) {
                        $(this).remove();
                    }
                });
                $clone.append(bodyClone.children());
                return $clone;
            }

            function downloadBlob(content, mimeType, fileName) {
                var blob = content instanceof Blob ? content : new Blob([content], { type: mimeType });
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 500);
            }

            function setCaptureButtonState(message, isBusy) {
                var $button = $('#po-monitor-detail-capture');
                if (!$button.data('original-html')) {
                    $button.data('original-html', $button.html());
                }

                if (message) {
                    $button.html(message);
                } else {
                    $button.html($button.data('original-html'));
                }
                $button.prop('disabled', !!isBusy);
            }

            function openImagePreview(blob) {
                var url = URL.createObjectURL(blob);
                var preview = window.open('', '_blank');
                if (preview) {
                    preview.document.write('<!doctype html><title>Preview Capture</title><body style="margin:0;background:#f8fafc;display:flex;align-items:flex-start;justify-content:center;padding:20px;"><img src="' + url + '" style="max-width:100%;height:auto;border:1px solid #cbd5e1;box-shadow:0 20px 50px rgba(15,23,42,.18);"></body>');
                    preview.document.close();
                }
                window.setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 60000);
            }

            function copyImageBlobToClipboard(blob) {
                if (navigator.clipboard && window.ClipboardItem) {
                    return navigator.clipboard.write([
                        new ClipboardItem({
                            'image/png': blob
                        })
                    ]);
                }

                openImagePreview(blob);
                return Promise.reject(new Error('Browser belum support copy image otomatis.'));
            }


            function escapeXml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&apos;');
            }

            function truncateForImage(value, maxLength) {
                var text = cleanDetailText(value);
                if (text.length <= maxLength) {
                    return text;
                }
                return text.substring(0, Math.max(0, maxLength - 1)) + '...';
            }

            function svgText(x, y, text, options) {
                options = options || {};
                return '<text x="' + x + '" y="' + y + '" font-size="' + (options.size || 12) + '" fill="' + (options.fill || '#0f172a') + '" font-weight="' + (options.weight || 400) + '" text-anchor="' + (options.anchor || 'start') + '">' + escapeXml(text) + '</text>';
            }

            var poMonitorCaptureLogos = {
                zeyn: '<?= file_exists(FCPATH . 'assets/dist/img/zeyn-logo.png') ? 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . 'assets/dist/img/zeyn-logo.png')) : '' ?>',
                tkm: '<?= file_exists(FCPATH . 'assets/dist/img/logotkmsolid.png') ? 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . 'assets/dist/img/logotkmsolid.png')) : '' ?>'
            };

            function captureDetailImage() {
                var $modal = $('#po_compare_detail_modal');
                var title = detailExportTitle($modal);
                var headerInfo = detailCaptureHeaderInfo($modal);
                setCaptureButtonState('<i class="fas fa-spinner fa-spin mr-1"></i> Copying...', true);
                var width = 2200;
                var margin = 34;
                var y = 32;
                var parts = [];
                var rowHeight = 40;
                var headerHeight = 38;
                var tableWidth = width - (margin * 2);
                var now = new Date().toLocaleString('id-ID');

                parts.push('<rect width="100%" height="100%" fill="#ffffff"/>');
                parts.push('<rect x="' + margin + '" y="' + y + '" width="' + tableWidth + '" height="112" rx="14" fill="#ffffff" stroke="#e2e8f0"/>');
                if (poMonitorCaptureLogos.zeyn) {
                    parts.push('<image href="' + poMonitorCaptureLogos.zeyn + '" x="' + (margin + 18) + '" y="' + (y + 24) + '" width="150" height="56" preserveAspectRatio="xMinYMid meet"/>');
                }
                if (poMonitorCaptureLogos.tkm) {
                    parts.push('<image href="' + poMonitorCaptureLogos.tkm + '" x="' + (width - margin - 178) + '" y="' + (y + 24) + '" width="160" height="56" preserveAspectRatio="xMaxYMid meet"/>');
                }
                parts.push(svgText(width / 2, y + 34, headerInfo.title, { size: 32, weight: 900, anchor: 'middle' }));
                parts.push(svgText(width / 2, y + 64, headerInfo.project, { size: 22, fill: '#163d66', weight: 900, anchor: 'middle' }));
                parts.push(svgText(width / 2, y + 90, headerInfo.regional + ' | ' + headerInfo.term, { size: 18, fill: '#334155', weight: 900, anchor: 'middle' }));
                y += 136;

                var totalRow = $modal.find('.js-po-detail-total-row').first().text();
                var totalAmount = $modal.find('.js-po-detail-total-amount').first().text();
                var summaryCards = [
                    ['Total Row', totalRow],
                    ['Total Amount', totalAmount],
                    ['Filter', title]
                ];
                var cardGap = 12;
                var cardWidth = (tableWidth - (cardGap * (summaryCards.length - 1))) / summaryCards.length;
                summaryCards.forEach(function(card, index) {
                    var x = margin + (index * (cardWidth + cardGap));
                    parts.push('<rect x="' + x + '" y="' + y + '" width="' + cardWidth + '" height="72" rx="10" fill="#f8fafc" stroke="#cbd5e1"/>');
                    parts.push(svgText(x + 16, y + 25, card[0], { size: 14, fill: '#64748b', weight: 900 }));
                    parts.push(svgText(x + 16, y + 54, truncateForImage(card[1], index === 2 ? 62 : 34), { size: 20, fill: '#0f172a', weight: 900 }));
                });
                y += 100;

                $modal.find('.po-monitor-regional-group:not(.is-hidden)').each(function() {
                    var $group = $(this);
                    var $section = $group.find('.po-monitor-regional-section').first();
                    var sectionValues = [];
                    $section.find('strong').each(function() {
                        sectionValues.push(cleanDetailText($(this).text()));
                    });
                    var regionalName = sectionValues[0] || 'Regional';
                    var regionalRows = sectionValues[1] || '';
                    var regionalTotal = sectionValues[2] || '';
                    var $table = $group.find('.po-monitor-detail-table').first();
                    var headers = [];
                    var rows = [];

                    $table.find('thead th').each(function() {
                        headers.push(cleanDetailText($(this).text()));
                    });

                    $table.find('tbody tr:not(.is-hidden)').each(function() {
                        var row = [];
                        $(this).children('td').each(function() {
                            row.push(cleanDetailText($(this).text()));
                        });
                        rows.push(row);
                    });

                    if (!rows.length) {
                        return;
                    }

                    var cols = Math.max(headers.length, rows[0] ? rows[0].length : 0);
                    var defaultWidths = cols === 13
                        ? [60, 145, 145, 135, 125, 140, 360, 130, 90, 145, 145, 155, 155]
                        : [60, 145, 145, 135, 125, 140, 430, 130, 90, 210, 160];
                    var widthSum = defaultWidths.slice(0, cols).reduce(function(total, item) {
                        return total + item;
                    }, 0);
                    var scale = tableWidth / widthSum;
                    var colWidths = defaultWidths.slice(0, cols).map(function(item) {
                        return item * scale;
                    });

                    parts.push('<rect x="' + margin + '" y="' + y + '" width="' + tableWidth + '" height="58" rx="10" fill="#163d66"/>');
                    parts.push(svgText(margin + 16, y + 24, 'Regional', { size: 13, fill: '#cbd5e1', weight: 900 }));
                    parts.push(svgText(margin + 16, y + 46, truncateForImage(regionalName, 42), { size: 20, fill: '#ffffff', weight: 900 }));
                    parts.push(svgText(margin + tableWidth - 430, y + 24, 'Row', { size: 13, fill: '#cbd5e1', weight: 900 }));
                    parts.push(svgText(margin + tableWidth - 430, y + 46, regionalRows, { size: 20, fill: '#ffffff', weight: 900 }));
                    parts.push(svgText(margin + tableWidth - 260, y + 24, 'Total', { size: 13, fill: '#cbd5e1', weight: 900 }));
                    parts.push(svgText(margin + tableWidth - 260, y + 46, regionalTotal, { size: 20, fill: '#ffffff', weight: 900 }));
                    y += 70;

                    parts.push('<rect x="' + margin + '" y="' + y + '" width="' + tableWidth + '" height="' + headerHeight + '" fill="#e2e8f0" stroke="#cbd5e1"/>');
                    var currentX = margin;
                    headers.forEach(function(header, index) {
                        var x = currentX;
                        var colWidth = colWidths[index] || (tableWidth / cols);
                        parts.push('<line x1="' + x + '" y1="' + y + '" x2="' + x + '" y2="' + (y + headerHeight) + '" stroke="#cbd5e1"/>');
                        parts.push(svgText(x + 8, y + 25, truncateForImage(header, Math.max(8, Math.floor(colWidth / 9))), { size: 14, fill: '#0f172a', weight: 900 }));
                        currentX += colWidth;
                    });
                    parts.push('<line x1="' + (margin + tableWidth) + '" y1="' + y + '" x2="' + (margin + tableWidth) + '" y2="' + (y + headerHeight) + '" stroke="#cbd5e1"/>');
                    y += headerHeight;

                    rows.forEach(function(row, rowIndex) {
                        var fill = rowIndex % 2 === 0 ? '#ffffff' : '#f8fafc';
                        parts.push('<rect x="' + margin + '" y="' + y + '" width="' + tableWidth + '" height="' + rowHeight + '" fill="' + fill + '" stroke="#e2e8f0"/>');
                        var currentX = margin;
                        for (var index = 0; index < cols; index++) {
                            var x = currentX;
                            var colWidth = colWidths[index] || (tableWidth / cols);
                            parts.push('<line x1="' + x + '" y1="' + y + '" x2="' + x + '" y2="' + (y + rowHeight) + '" stroke="#e2e8f0"/>');
                            parts.push(svgText(x + 8, y + 25, truncateForImage(row[index] || '', Math.max(8, Math.floor(colWidth / 9))), { size: 14, fill: '#0f172a', weight: 500 }));
                            currentX += colWidth;
                        }
                        parts.push('<line x1="' + (margin + tableWidth) + '" y1="' + y + '" x2="' + (margin + tableWidth) + '" y2="' + (y + rowHeight) + '" stroke="#e2e8f0"/>');
                        y += rowHeight;
                    });

                    y += 20;
                });

                y += 8;
                parts.push(svgText(width / 2, y + 15, 'Supported by aplication Zeyn', { size: 11, fill: 'rgba(100,116,139,0.45)', weight: 700, anchor: 'middle' }));

                var exportHeight = Math.min(18000, y + 34);
                var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + width + '" height="' + exportHeight + '" viewBox="0 0 ' + width + ' ' + exportHeight + '"><style>text{font-family:Arial,Helvetica,sans-serif;dominant-baseline:auto;}</style>' + parts.join('') + '</svg>';
                var image = new Image();
                var svgBlob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                var url = URL.createObjectURL(svgBlob);

                image.onload = function() {
                    var canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = exportHeight;
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(image, 0, 0);
                    URL.revokeObjectURL(url);
                    try {
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                setCaptureButtonState(null, false);
                                window.alert('Capture image gagal dibuat.');
                                return;
                            }

                            copyImageBlobToClipboard(blob)
                                .then(function() {
                                    setCaptureButtonState('<i class="fas fa-check mr-1"></i> Copied', false);
                                    window.setTimeout(function() {
                                        setCaptureButtonState(null, false);
                                    }, 1400);
                                })
                                .catch(function() {
                                    setCaptureButtonState(null, false);
                                    window.alert('Browser belum mengizinkan copy image otomatis. Preview gambar dibuka, bisa copy manual dari sana.');
                                });
                        }, 'image/png');
                    } catch (error) {
                        setCaptureButtonState(null, false);
                        var svgBlob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                        openImagePreview(svgBlob);
                        window.alert('Browser belum mengizinkan copy image otomatis. Preview gambar dibuka, bisa copy manual dari sana.');
                    }
                };
                image.onerror = function() {
                    URL.revokeObjectURL(url);
                    setCaptureButtonState(null, false);
                    window.alert('Capture image gagal dirender oleh browser.');
                };
                image.src = url;
            }

            function downloadDetailExcel() {
                var $modal = $('#po_compare_detail_modal');
                var title = detailExportTitle($modal);
                var totalRow = $modal.find('.js-po-detail-total-row').first().text();
                var totalAmount = $modal.find('.js-po-detail-total-amount').first().text();
                var generatedAt = new Date().toLocaleString('id-ID');
                var activeRegional = detailActiveLabel($modal, '.po-monitor-regional-card', 'All Regional') || 'All Regional';
                var activeTerm = detailActiveLabel($modal, '.po-monitor-term-card', 'All Term') || 'All Term';
                var html = '';
                var textCellStyle = ' style="mso-number-format:\\@;"';

                html += '<html><head><meta charset="utf-8"><style>';
                html += 'body{font-family:Arial,sans-serif;}';
                html += 'table{border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;}';
                html += 'th,td{border:1px solid #999;padding:5px 7px;font-size:11pt;vertical-align:top;}';
                html += '.title{font-size:18pt;font-weight:bold;border:0;padding:0 0 4px 0;}';
                html += '.subtitle{font-size:10pt;color:#666;border:0;padding:0 0 12px 0;}';
                html += '.summary-label{background:#d9eaf7;font-weight:bold;text-transform:uppercase;}';
                html += '.summary-value{font-weight:bold;}';
                html += '.section{background:#163d66;color:#fff;font-weight:bold;font-size:12pt;}';
                html += '.head{background:#d9e2f3;font-weight:bold;text-align:center;}';
                html += '.total{background:#00b050;color:#000;font-weight:bold;}';
                html += '.right{text-align:right;}';
                html += '.center{text-align:center;}';
                html += '</style></head><body>';

                html += '<table>';
                html += '<tr><td colspan="11" class="title">' + escapeHtml(title) + '</td></tr>';
                html += '<tr><td colspan="11" class="subtitle">' + escapeHtml(generatedAt) + '</td></tr>';
                html += '<tr>';
                html += '<td class="summary-label">Total Row</td><td class="summary-value right"' + textCellStyle + '>' + escapeHtml(totalRow) + '</td>';
                html += '<td class="summary-label">Total Amount</td><td class="summary-value right"' + textCellStyle + '>' + escapeHtml(totalAmount) + '</td>';
                html += '<td class="summary-label">Regional</td><td class="summary-value">' + escapeHtml(activeRegional) + '</td>';
                html += '<td class="summary-label">Term</td><td class="summary-value">' + escapeHtml(activeTerm) + '</td>';
                html += '<td colspan="3"></td>';
                html += '</tr>';
                html += '<tr><td colspan="11" style="border:0;height:10px;"></td></tr>';

                $modal.find('.po-monitor-regional-group:not(.is-hidden)').each(function() {
                    var $group = $(this);
                    var $section = $group.find('.po-monitor-regional-section').first();
                    var sectionValues = [];
                    $section.find('strong').each(function() {
                        sectionValues.push(cleanDetailText($(this).text()));
                    });

                    var regionalName = sectionValues[0] || 'Regional';
                    var regionalRows = sectionValues[1] || '0';
                    var regionalTotal = sectionValues[2] || '0';
                    var $table = $group.find('.po-monitor-detail-table').first();
                    var headers = [];
                    var rows = [];
                    var footerCells = [];

                    $table.find('thead th').each(function() {
                        headers.push(cleanDetailText($(this).text()));
                    });

                    $table.find('tbody tr:not(.is-hidden)').each(function() {
                        var row = [];
                        $(this).children('td').each(function() {
                            row.push(cleanDetailText($(this).text()));
                        });
                        rows.push(row);
                    });

                    $table.find('tfoot th').each(function() {
                        footerCells.push(cleanDetailText($(this).text()));
                    });

                    if (!rows.length) {
                        return;
                    }

                    html += '<tr><td colspan="' + headers.length + '" class="section">Regional: ' + escapeHtml(regionalName) + ' | Row: ' + escapeHtml(regionalRows) + ' | Total: ' + escapeHtml(regionalTotal) + '</td></tr>';
                    html += '<tr>';
                    headers.forEach(function(header) {
                        html += '<th class="head">' + escapeHtml(header) + '</th>';
                    });
                    html += '</tr>';

                    rows.forEach(function(row) {
                        html += '<tr>';
                        headers.forEach(function(header, index) {
                            var value = row[index] || '';
                            var className = index === headers.length - 1 ? ' class="right"' : '';
                            html += '<td' + className + textCellStyle + '>' + escapeHtml(value) + '</td>';
                        });
                        html += '</tr>';
                    });

                    if (footerCells.length) {
                        html += '<tr>';
                        if (footerCells.length === 2) {
                            html += '<td colspan="' + Math.max(1, headers.length - 1) + '" class="total right">' + escapeHtml(footerCells[0]) + '</td>';
                            html += '<td class="total right"' + textCellStyle + '>' + escapeHtml(footerCells[1]) + '</td>';
                        } else {
                            footerCells.forEach(function(value) {
                                html += '<td class="total"' + textCellStyle + '>' + escapeHtml(value) + '</td>';
                            });
                        }
                        html += '</tr>';
                    }

                    html += '<tr><td colspan="' + headers.length + '" style="border:0;height:12px;"></td></tr>';
                });

                html += '</table></body></html>';
                downloadBlob(html, 'application/vnd.ms-excel;charset=utf-8', safeDownloadName(title, '.xls'));
            }

            $(document)
                .off('click.poMonitorDetailCapture', '#po-monitor-detail-capture')
                .on('click.poMonitorDetailCapture', '#po-monitor-detail-capture', captureDetailImage);

            $(document)
                .off('click.poMonitorDetailExcel', '#po-monitor-detail-excel')
                .on('click.poMonitorDetailExcel', '#po-monitor-detail-excel', downloadDetailExcel);

            $(document)
                .off('change.poMonitorDetailUninvoiced', '#po-monitor-detail-uninvoiced-only')
                .on('change.poMonitorDetailUninvoiced', '#po-monitor-detail-uninvoiced-only', function() {
                    applyDetailFilters($('#po_compare_detail_modal'));
                });

            $(document)
                .off('click.poMonitorRegionalFilter', '#po_compare_detail_modal .po-monitor-regional-card')
                .on('click.poMonitorRegionalFilter', '#po_compare_detail_modal .po-monitor-regional-card', function() {
                    var $button = $(this);
                    var $modal = $('#po_compare_detail_modal');
                    var key = String($button.data('regional-key') || '');
                    var isActive = $button.hasClass('is-active');
                    $modal.data('active-regional-key', isActive ? '' : key);
                    applyDetailFilters($modal);
                });

            $(document)
                .off('click.poMonitorTermFilter', '#po_compare_detail_modal .po-monitor-term-card')
                .on('click.poMonitorTermFilter', '#po_compare_detail_modal .po-monitor-term-card', function() {
                    var $button = $(this);
                    var $modal = $('#po_compare_detail_modal');
                    var termIndex = String($button.data('term-index') || '');
                    var isActive = $button.hasClass('is-active');
                    $modal.data('active-term-index', isActive ? '' : termIndex);
                    applyDetailFilters($modal);
                });

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
                        resetDetailFilters($modal);
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
                        resetDetailFilters($modal);
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

                if (weekMode && !compareTables.week) {
                    loadWeekCompareTable(function(loaded) {
                        if (loaded && compareTables.week) {
                            compareTables.week.columns.adjust().draw(false);
                        }
                    });
                } else if (compareTables.week) {
                    compareTables.week.draw();
                    if (weekMode) {
                        window.setTimeout(function() {
                            compareTables.week.columns.adjust().draw(false);
                        }, 80);
                    }
                }

                scheduleCompareTableAdjust();
            }

            $('#po_compare_data_only, #po_compare_week_mode')
                .off('change.poCompareSwitch')
                .on('change.poCompareSwitch', syncCompareSwitches);

            syncCompareSwitches();

            initServerSideTable($, '#table_po_monitor_list', '<?= site_url('PO_Monitor/po_datatable') ?>', [[2, 'desc']], 25);

        }

        bootstrapPOMonitor();
    })();
</script>
