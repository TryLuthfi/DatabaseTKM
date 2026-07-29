<?php
$termBreakdown = is_array($termBreakdown ?? null) ? $termBreakdown : [];
$summary = is_array($summary ?? null) ? $summary : [];

if (!function_exists('po_breakdown_money')) {
    function po_breakdown_money($value)
    {
        return 'RP. ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('po_breakdown_number')) {
    function po_breakdown_number($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('po_breakdown_percent')) {
    function po_breakdown_percent($target, $achieved)
    {
        $target = (float) $target;
        if ($target <= 0) {
            return '0,0 %';
        }

        return number_format(((float) $achieved / $target) * 100, 1, ',', '.') . ' %';
    }
}
?>

<style>
    .po-breakdown-page {
        --po-breakdown-ink: #0f172a;
        --po-breakdown-muted: #64748b;
        --po-breakdown-line: rgba(148, 163, 184, 0.24);
        --po-breakdown-blue: #2563eb;
        --po-breakdown-green: #16a34a;
        --po-breakdown-amber: #f59e0b;
        --po-breakdown-red: #dc2626;
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }

    .po-breakdown-page > .content-header {
        display: none;
    }

    .po-breakdown-shell {
        padding: 1rem;
    }

    .po-breakdown-hero {
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.18);
        color: #f8fafc;
        overflow: hidden;
    }

    .po-breakdown-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.2rem;
        padding: 1.25rem;
    }

    .po-breakdown-eyebrow {
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

    .po-breakdown-hero h1 {
        margin: 0.9rem 0 0.55rem;
        color: #fff;
        font-size: 1.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .po-breakdown-hero p {
        max-width: 48rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.9);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .po-breakdown-hero__stats,
    .po-breakdown-kpi-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .po-breakdown-hero-stat {
        min-height: 90px;
        padding: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.11);
    }

    .po-breakdown-hero-stat__label,
    .po-breakdown-kpi__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-breakdown-hero-stat__value {
        display: block;
        margin-top: 0.3rem;
        color: #fff;
        font-size: 1.35rem;
        font-weight: 900;
        line-height: 1.15;
        overflow-wrap: anywhere;
    }

    .po-breakdown-kpi-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 1rem;
        gap: 0.9rem;
    }

    .po-breakdown-kpi {
        min-height: 104px;
        padding: 1.05rem 1.1rem;
        border: 1px solid rgba(191, 219, 254, 0.9);
        border-top: 5px solid var(--po-breakdown-blue);
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.92));
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .po-breakdown-kpi--green {
        border-color: rgba(187, 247, 208, 0.95);
        border-top-color: var(--po-breakdown-green);
    }

    .po-breakdown-kpi--amber {
        border-color: rgba(253, 230, 138, 0.95);
        border-top-color: var(--po-breakdown-amber);
    }

    .po-breakdown-kpi--red {
        border-color: rgba(254, 202, 202, 0.95);
        border-top-color: var(--po-breakdown-red);
    }

    .po-breakdown-kpi__label {
        color: #475569;
    }

    .po-breakdown-kpi__value {
        display: block;
        margin-top: 0.48rem;
        color: var(--po-breakdown-ink);
        font-size: 1.16rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .po-breakdown-panel {
        margin-top: 1rem;
        border: 1px solid var(--po-breakdown-line);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        overflow: hidden;
    }

    .po-breakdown-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.05rem 1.15rem 0;
    }

    .po-breakdown-panel__title {
        margin: 0;
        color: var(--po-breakdown-ink);
        font-size: 1rem;
        font-weight: 900;
    }

    .po-breakdown-panel__subtitle {
        margin: 0.25rem 0 0;
        color: var(--po-breakdown-muted);
        font-size: 0.86rem;
    }

    .po-breakdown-panel__body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .po-breakdown-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0;
        color: var(--po-breakdown-ink);
        font-size: 0.84rem;
    }

    .po-breakdown-table thead th {
        border-bottom: 1px solid var(--po-breakdown-line) !important;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .po-breakdown-table th,
    .po-breakdown-table td {
        padding: 0.72rem 0.75rem !important;
        vertical-align: middle !important;
        border-top: 0 !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.95);
    }

    .po-breakdown-table tbody tr:hover td {
        background: #f8fafc;
    }

    .po-breakdown-table tfoot th {
        background: #e2e8f0;
        color: var(--po-breakdown-ink);
        font-weight: 900;
        white-space: nowrap;
    }

    .po-breakdown-money,
    .po-monitor-money {
        color: #0f172a;
        font-weight: 900;
        white-space: nowrap;
    }

    .po-breakdown-detail-btn,
    .po-monitor-list-detail-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(37, 99, 235, 0.20);
        border-radius: 8px;
        background: rgba(37, 99, 235, 0.08);
        color: #1d4ed8;
    }

    .po-breakdown-sla-pill,
    .po-monitor-sla-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 0.28rem 0.58rem;
        border-radius: 999px;
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.04em;
    }

    .po-breakdown-sla-pill--warning,
    .po-monitor-sla-pill--warning {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }

    .po-breakdown-sla-pill--overdue,
    .po-monitor-sla-pill--overdue {
        background: rgba(220, 38, 38, 0.12);
        color: #991b1b;
    }

    @media (max-width: 1199.98px) {
        .po-breakdown-hero__grid,
        .po-breakdown-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .po-breakdown-hero__grid,
        .po-breakdown-hero__stats,
        .po-breakdown-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper po-breakdown-page">
    <div class="content">
        <section class="content">
            <div class="container-fluid po-breakdown-shell">
                <div class="po-breakdown-hero">
                    <div class="po-breakdown-hero__grid">
                        <div>
                            <span class="po-breakdown-eyebrow">
                                <i class="fas fa-layer-group"></i>
                                Database All PO
                            </span>
                            <h1>PO Breakdown</h1>
                            <p>Halaman khusus untuk membaca list PO monitor dan breakdown invoice per term tanpa tercampur dengan dashboard utama.</p>
                        </div>
                        <div class="po-breakdown-hero__stats">
                            <div class="po-breakdown-hero-stat">
                                <span class="po-breakdown-hero-stat__label">Project</span>
                                <span class="po-breakdown-hero-stat__value"><?= po_breakdown_number($summary['project_count'] ?? 0) ?></span>
                            </div>
                            <div class="po-breakdown-hero-stat">
                                <span class="po-breakdown-hero-stat__label">Total PO</span>
                                <span class="po-breakdown-hero-stat__value"><?= po_breakdown_number($summary['total_po'] ?? 0) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="po-breakdown-kpi-grid" aria-label="Ringkasan PO Breakdown">
                    <div class="po-breakdown-kpi">
                        <span class="po-breakdown-kpi__label">Total Term</span>
                        <span class="po-breakdown-kpi__value"><?= po_breakdown_money($summary['term_value'] ?? 0) ?></span>
                    </div>
                    <div class="po-breakdown-kpi po-breakdown-kpi--green">
                        <span class="po-breakdown-kpi__label">Done Invoice</span>
                        <span class="po-breakdown-kpi__value"><?= po_breakdown_money($summary['invoiced_amount'] ?? 0) ?></span>
                    </div>
                    <div class="po-breakdown-kpi po-breakdown-kpi--red">
                        <span class="po-breakdown-kpi__label">Outstanding Term</span>
                        <span class="po-breakdown-kpi__value"><?= po_breakdown_money($summary['remaining'] ?? 0) ?></span>
                    </div>
                    <div class="po-breakdown-kpi po-breakdown-kpi--amber">
                        <span class="po-breakdown-kpi__label">Achievement</span>
                        <span class="po-breakdown-kpi__value"><?= po_breakdown_percent($summary['term_value'] ?? 0, $summary['invoiced_amount'] ?? 0) ?></span>
                    </div>
                </section>

                <div class="po-breakdown-panel">
                    <div class="po-breakdown-panel__head">
                        <div>
                            <h3 class="po-breakdown-panel__title">List PO Monitor</h3>
                            <p class="po-breakdown-panel__subtitle">Data list PO diambil dari modul PO_Monitor.</p>
                        </div>
                    </div>
                    <div class="po-breakdown-panel__body table-responsive">
                        <table id="table_po_breakdown_monitor_list" class="table po-breakdown-table">
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
                        </table>
                    </div>
                </div>

                <div class="po-breakdown-panel">
                    <div class="po-breakdown-panel__head">
                        <div>
                            <h3 class="po-breakdown-panel__title">Breakdown per Term</h3>
                            <p class="po-breakdown-panel__subtitle">Ringkasan nilai term, invoice, dan outstanding per project.</p>
                        </div>
                    </div>
                    <div class="po-breakdown-panel__body table-responsive">
                        <table id="table_po_breakdown_term" class="table po-breakdown-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Project</th>
                                    <th>Total PO</th>
                                    <th>Term</th>
                                    <th>Nilai Term</th>
                                    <th>Done Invoice</th>
                                    <th>Outstanding</th>
                                    <th>Achievement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rowNumber = 1; ?>
                                <?php foreach ($termBreakdown as $row): ?>
                                    <?php foreach (($row['terms'] ?? []) as $term): ?>
                                        <tr>
                                            <td class="text-center"><?= $rowNumber++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_bowheer'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="text-center"><?= po_breakdown_number($row['total_po'] ?? 0) ?></td>
                                            <td class="text-center">Term <?= po_breakdown_number($term['term_index'] ?? 0) ?></td>
                                            <td class="text-right"><span class="po-breakdown-money"><?= po_breakdown_money($term['term_value'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-money"><?= po_breakdown_money($term['invoiced_amount'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-money"><?= po_breakdown_money($term['remaining'] ?? 0) ?></span></td>
                                            <td class="text-center"><?= po_breakdown_percent($term['term_value'] ?? 0, $term['invoiced_amount'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-center">TOTAL</th>
                                    <th class="text-right"><?= po_breakdown_money($summary['term_value'] ?? 0) ?></th>
                                    <th class="text-right"><?= po_breakdown_money($summary['invoiced_amount'] ?? 0) ?></th>
                                    <th class="text-right"><?= po_breakdown_money($summary['remaining'] ?? 0) ?></th>
                                    <th class="text-center"><?= po_breakdown_percent($summary['term_value'] ?? 0, $summary['invoiced_amount'] ?? 0) ?></th>
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
    (function () {
        function bootstrapPOBreakdown() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
                window.setTimeout(bootstrapPOBreakdown, 150);
                return;
            }

            var $ = window.jQuery;
            var monitorSelector = '#table_po_breakdown_monitor_list';
            var termSelector = '#table_po_breakdown_term';

            if ($(monitorSelector).length && !$.fn.DataTable.isDataTable(monitorSelector)) {
                $(monitorSelector).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '<?= site_url('PO_Monitor/po_datatable') ?>',
                        type: 'POST'
                    },
                    paging: true,
                    pageLength: 10,
                    lengthMenu: [[10], ['10 row']],
                    searching: true,
                    info: true,
                    lengthChange: true,
                    autoWidth: false,
                    responsive: false,
                    scrollX: true,
                    ordering: true,
                    order: [[2, 'desc']],
                    columnDefs: [
                        { targets: [0, 6, 7], className: 'text-center' },
                        { targets: [3, 4, 5], className: 'text-right' },
                        { targets: [7], orderable: false, searchable: false }
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Cari PO monitor',
                        lengthMenu: '_MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        },
                        processing: 'Loading...'
                    }
                });
            }

            if ($(termSelector).length && !$.fn.DataTable.isDataTable(termSelector)) {
                $(termSelector).DataTable({
                    paging: true,
                    pageLength: 10,
                    searching: true,
                    info: true,
                    lengthChange: true,
                    autoWidth: false,
                    responsive: false,
                    scrollX: true,
                    ordering: true,
                    order: [[1, 'asc'], [3, 'asc']],
                    columnDefs: [
                        { targets: [0, 2, 3, 7], className: 'text-center' },
                        { targets: [4, 5, 6], className: 'text-right' }
                    ],
                    language: {
                        search: '',
                        searchPlaceholder: 'Cari breakdown term',
                        lengthMenu: '_MENU_ row',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            previous: 'Previous',
                            next: 'Next'
                        }
                    }
                });
            }
        }

        bootstrapPOBreakdown();
    })();
</script>
