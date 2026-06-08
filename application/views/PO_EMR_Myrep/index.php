<?php
$flashError = $this->session->flashdata('error');

if (!function_exists('poEmrNumber')) {
    function poEmrNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('poEmrNumberOrDash')) {
    function poEmrNumberOrDash($value)
    {
        return (float) $value == 0.0 ? '-' : poEmrNumber($value);
    }
}

$selectedRegionalValues = is_array($selectedRegional) ? array_values($selectedRegional) : array_filter([strtoupper(trim((string) $selectedRegional))]);
$selectedCityValues = is_array($selectedCity) ? array_values($selectedCity) : array_filter([strtoupper(trim((string) $selectedCity))]);
$selectedStageValues = is_array($selectedStage) ? array_values($selectedStage) : array_filter([strtoupper(trim((string) $selectedStage))]);
$downloadParams = [];
if (!empty($selectedRegionalValues)) {
    $downloadParams['regional'] = $selectedRegionalValues;
}
if (!empty($selectedCityValues)) {
    $downloadParams['city'] = $selectedCityValues;
}
if (!empty($selectedStageValues)) {
    $downloadParams['stage'] = $selectedStageValues;
}
$downloadUrl = base_url('PO_EMR_Myrep/downloadReport') . (!empty($downloadParams) ? '?' . http_build_query($downloadParams) : '');

$currentQueryParams = [];
if (!empty($_SERVER['QUERY_STRING'])) {
    parse_str((string) $_SERVER['QUERY_STRING'], $currentQueryParams);
}
unset($currentQueryParams['back']);
$currentListUrl = base_url('PO_EMR_Myrep') . (!empty($currentQueryParams) ? '?' . http_build_query($currentQueryParams) : '');
$detailBackQuery = '?back=' . rawurlencode($currentListUrl);
?>

<style>
    .emr-page-title {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .emr-page-title h1 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        text-align: left;
    }

    .emr-page-title p {
        margin: .25rem 0 0;
        color: #64748b;
        font-weight: 600;
    }

    .emr-executive-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }

    .emr-exec-card {
        position: relative;
        min-height: 88px;
        padding: .85rem .9rem .8rem;
        border: 1px solid #dbe4ef;
        border-left: 4px solid var(--emr-accent, #2563eb);
        border-radius: 8px;
        background: var(--emr-bg, #f8fbff);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .emr-exec-card__label {
        margin-bottom: .32rem;
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .emr-exec-card__value {
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1.08;
        overflow-wrap: anywhere;
    }

    .emr-exec-card__value--compact {
        font-size: 1.08rem;
    }

    .emr-exec-card__meta {
        margin-top: .28rem;
        color: #475569;
        font-size: .73rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .emr-exec-card--blue {
        --emr-accent: #2563eb;
        --emr-bg: #f8fbff;
    }

    .emr-exec-card--green {
        --emr-accent: #16a34a;
        --emr-bg: #f6fff9;
    }

    .emr-exec-card--cyan {
        --emr-accent: #0891b2;
        --emr-bg: #f2fdff;
    }

    .emr-exec-card--amber {
        --emr-accent: #f59e0b;
        --emr-bg: #fffaf0;
    }

    .emr-exec-card--mint {
        --emr-accent: #10b981;
        --emr-bg: #f3fff8;
    }

    .emr-exec-card--red {
        --emr-accent: #dc2626;
        --emr-bg: #fff7f7;
    }

    @media (max-width: 1400px) {
        .emr-executive-summary {
            grid-template-columns: repeat(3, minmax(170px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .emr-executive-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .emr-executive-summary {
            grid-template-columns: 1fr;
        }
    }

    .emr-pic-matrix-wrap {
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        overflow: auto;
    }

    .emr-pic-summary-table {
        min-width: 1120px;
        table-layout: fixed;
        font-variant-numeric: tabular-nums;
    }

    .emr-pic-summary-table th,
    .emr-pic-summary-table td {
        border-color: #dbe3ec;
        padding: 0;
        white-space: nowrap;
        vertical-align: middle;
    }

    .emr-pic-summary-table thead th {
        height: 52px;
        background: #f8fafc;
        color: #0f172a;
        text-align: center;
        font-weight: 800;
    }

    .emr-pic-summary-table tfoot th,
    .emr-pic-total-cell {
        background: #f8fafc;
        font-weight: 800;
    }

    .emr-pic-summary-table__pic-head,
    .emr-pic-row-head {
        width: 128px;
    }

    .emr-pic-row-head {
        padding: .55rem .65rem !important;
        color: #0f172a;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .emr-term-head {
        width: 166px;
        padding: .45rem .55rem !important;
    }

    .emr-term-head__no,
    .emr-term-head__stage {
        display: block;
        line-height: 1.15;
    }

    .emr-term-head__no {
        color: #475569;
        font-size: .72rem;
        font-weight: 800;
    }

    .emr-term-head__stage {
        margin-top: .18rem;
        color: #020617;
        font-size: .76rem;
        font-weight: 900;
    }

    .emr-pic-cell {
        width: 166px;
        text-align: right;
    }

    .emr-pic-total-col {
        width: 150px;
    }

    .emr-pic-cell__count {
        display: block;
        margin-top: .12rem;
        color: #64748b;
        font-size: .66rem;
        font-weight: 700;
        line-height: 1.15;
    }

    .emr-pic-cell__value {
        display: block;
        color: #0f172a;
        font-size: .88rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .emr-pic-drilldown {
        display: block;
        width: 100%;
        min-height: 42px;
        padding: .42rem .55rem;
        border: 1px solid transparent;
        border-radius: 7px;
        background: transparent;
        text-align: right;
        cursor: pointer;
    }

    .emr-pic-drilldown:not(:disabled) {
        background: #f8fbff;
    }

    .emr-pic-drilldown:hover,
    .emr-pic-drilldown.is-active {
        border-color: #93c5fd;
        background: #eff6ff;
        box-shadow: inset 0 0 0 1px rgba(59, 130, 246, .15);
    }

    .emr-pic-drilldown:disabled {
        cursor: default;
        opacity: 1;
    }

    .emr-pic-drilldown:disabled:hover {
        border-color: transparent;
        background: transparent;
    }

    .emr-pic-drilldown.is-zero .emr-pic-cell__count,
    .emr-pic-drilldown.is-zero .emr-pic-cell__value {
        color: #a3afbf;
        font-weight: 700;
    }

    .emr-pic-total-cell {
        padding: .42rem .55rem !important;
    }

    .emr-pic-total-cell .emr-pic-cell__count {
        color: #475569;
    }

    .emr-pic-total-cell .emr-pic-cell__value {
        color: #020617;
    }

    .emr-term-head--focus,
    .emr-pic-cell--focus {
        border-left-color: #bfdbfe !important;
        border-left-width: 2px !important;
    }

    .emr-pic-row-badge {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: .13rem .38rem;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .emr-pic-row-badge--area,
    .emr-pic-row-badge--tkm {
        background: #dff8ed;
        color: #047857;
    }

    .emr-pic-row-badge--ho {
        background: #eee7ff;
        color: #6d28d9;
    }

    .emr-pic-row-badge--dc {
        background: #fff1d6;
        color: #92400e;
    }

    .emr-pic-row-badge--flow {
        background: #e0f2fe;
        color: #0369a1;
    }

    .emr-pic-row-badge--closed {
        background: #fef3c7;
        color: #92400e;
    }

    .emr-nro-stack {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: .32rem;
        width: 100%;
        margin-top: .18rem;
    }

    .emr-nro-stack__item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        column-gap: .45rem;
        row-gap: .08rem;
        width: 100%;
        padding: .34rem .42rem;
        border-radius: 7px;
        background: #fbfaf4;
        cursor: pointer;
    }

    .emr-nro-stack__item:hover,
    .emr-nro-stack__item.is-active {
        background: #fff7ed;
        box-shadow: inset 0 0 0 1px #fed7aa;
    }

    .emr-nro-stack__label,
    .emr-nro-stack__value,
    .emr-nro-stack__count {
        display: block;
        line-height: 1.12;
    }

    .emr-nro-stack__label {
        color: #44403c;
        font-size: .62rem;
        font-weight: 700;
        text-align: left;
    }

    .emr-nro-stack__value {
        color: #0f172a;
        font-size: .68rem;
        font-weight: 900;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .emr-nro-stack__count {
        grid-column: 2;
        color: #78716c;
        font-size: .58rem;
        font-weight: 700;
        text-align: right;
    }

    .emr-drilldown-state {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
        padding: .65rem .75rem;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
    }

    .emr-drilldown-state.is-visible {
        display: flex;
    }

    #table_po_emr_target th,
    #table_po_emr_target td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table_po_emr_target thead th {
        text-align: center;
    }

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

    .emr-breakdown-table th,
    .emr-breakdown-table td {
        white-space: nowrap;
        vertical-align: middle;
    }

    .emr-breakdown-table thead th {
        text-align: center;
        font-weight: 800;
    }

    .emr-breakdown-table tfoot th {
        font-weight: 800;
        background: #f8fafc;
    }

    .emr-filter-select + .select2-container .select2-selection--multiple {
        min-height: calc(2.25rem + 2px);
    }

    .emr-filter-select + .select2-container .select2-selection__choice {
        margin-top: .28rem;
    }
</style>

<div class="emr-page-title">
    <div>
        <h1>OUTSTANDING TARGET PO EMR</h1>
    </div>
    <a href="<?= $downloadUrl ?>" class="btn btn-success">
        <i class="fas fa-file-excel mr-1"></i> Download Report
    </a>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!$isReady): ?>
    <div class="alert alert-warning">Tabel PO MyRep atau kolom on_target belum tersedia. Jalankan patch database terlebih dahulu.</div>
<?php else: ?>
    <?php
    $picRows = ['AREA', 'HO', 'DC EMR', 'NRO'];
    $nroFlowLabels = [
        'WAITING WASPANG' => 'Waiting Waspang',
        'WAITING PLANNING' => 'Waiting Planning',
        'WAITING TL' => 'Waiting TL',
        'WAITING LOGISTIK' => 'Waiting Logistik',
    ];
    $picLabelClasses = [
        'AREA' => 'area',
        'HO' => 'ho',
        'DC EMR' => 'dc',
        'NRO' => 'flow',
    ];
    $picDisplayLabels = [
        'AREA' => 'TKM - AREA',
        'HO' => 'TKM - HO',
        'DC EMR' => 'EMR - DC',
        'NRO' => 'NRO',
    ];
    $executivePicTotals = [];
    foreach ($picRows as $picRow) {
        $executivePicTotals[$picRow] = ['count' => 0, 'value' => 0];
    }
    foreach (($terminPicSummaryRows ?? []) as $termRow) {
        foreach ($picRows as $picRow) {
            $picData = (array) (($termRow['pic'][$picRow] ?? ['count' => 0, 'value' => 0]));
            $executivePicTotals[$picRow]['count'] += (int) ($picData['count'] ?? 0);
            $executivePicTotals[$picRow]['value'] += (float) ($picData['value'] ?? 0);
        }
    }
    $activeOutstandingCount = 0;
    foreach ($executivePicTotals as $picTotal) {
        $activeOutstandingCount += (int) ($picTotal['count'] ?? 0);
    }
    ?>
    <div class="emr-executive-summary">
        <div class="emr-exec-card emr-exec-card--amber">
            <div class="emr-exec-card__label">Total Outstanding</div>
            <div class="emr-exec-card__value emr-exec-card__value--compact"><?= poEmrNumber((float) ($summary['total_outstanding'] ?? 0)) ?></div>
            <div class="emr-exec-card__meta"><?= $activeOutstandingCount ?> tagihan aktif</div>
        </div>
        <div class="emr-exec-card emr-exec-card--green">
            <div class="emr-exec-card__label"><?= htmlspecialchars($picDisplayLabels['AREA'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="emr-exec-card__value emr-exec-card__value--compact"><?= poEmrNumber((float) ($executivePicTotals['AREA']['value'] ?? 0)) ?></div>
            <div class="emr-exec-card__meta"><?= (int) ($executivePicTotals['AREA']['count'] ?? 0) ?> tagihan</div>
        </div>
        <div class="emr-exec-card emr-exec-card--blue">
            <div class="emr-exec-card__label"><?= htmlspecialchars($picDisplayLabels['HO'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="emr-exec-card__value emr-exec-card__value--compact"><?= poEmrNumber((float) ($executivePicTotals['HO']['value'] ?? 0)) ?></div>
            <div class="emr-exec-card__meta"><?= (int) ($executivePicTotals['HO']['count'] ?? 0) ?> tagihan</div>
        </div>
        <div class="emr-exec-card emr-exec-card--cyan">
            <div class="emr-exec-card__label"><?= htmlspecialchars($picDisplayLabels['DC EMR'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="emr-exec-card__value emr-exec-card__value--compact"><?= poEmrNumber((float) ($executivePicTotals['DC EMR']['value'] ?? 0)) ?></div>
            <div class="emr-exec-card__meta"><?= (int) ($executivePicTotals['DC EMR']['count'] ?? 0) ?> tagihan</div>
        </div>
        <div class="emr-exec-card emr-exec-card--red">
            <div class="emr-exec-card__label">NRO</div>
            <div class="emr-exec-card__value emr-exec-card__value--compact"><?= poEmrNumber((float) ($executivePicTotals['NRO']['value'] ?? 0)) ?></div>
            <div class="emr-exec-card__meta"><?= (int) ($executivePicTotals['NRO']['count'] ?? 0) ?> tagihan</div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Summary PIC per Termin</h3>
        </div>
        <div class="card-body">
            <?php
            $termColumns = [];
            $termSummaryByStage = [];
            foreach (($terminPicSummaryRows ?? []) as $termRow) {
                $stageLabel = (string) ($termRow['stage'] ?? '');
                if ($stageLabel === '') {
                    continue;
                }
                $termColumns[] = [
                    'termin_no' => (int) ($termRow['termin_no'] ?? 0),
                    'stage' => $stageLabel,
                ];
                $termSummaryByStage[$stageLabel] = $termRow;
            }
            $picRowTotals = [];
            $visibleColumnTotals = [];
            foreach ($picRows as $picRow) {
                $picRowTotals[$picRow] = ['count' => 0, 'value' => 0, 'nro_flow' => []];
                foreach ($nroFlowLabels as $nroStatus => $nroLabel) {
                    $picRowTotals[$picRow]['nro_flow'][$nroStatus] = ['count' => 0, 'value' => 0];
                }
            }
            foreach ($termColumns as $termColumn) {
                $visibleColumnTotals[(string) ($termColumn['stage'] ?? '')] = ['count' => 0, 'value' => 0];
            }
            ?>
            <div class="table-responsive emr-pic-matrix-wrap">
                <table class="table table-bordered table-hover mb-0 emr-pic-summary-table">
                    <thead>
                        <tr>
                            <th class="emr-pic-summary-table__pic-head">PIC</th>
                            <?php foreach ($termColumns as $termColumn): ?>
                                <?php $isRfsColumn = strtoupper((string) ($termColumn['stage'] ?? '')) === 'RFS'; ?>
                                <th class="emr-term-head <?= $isRfsColumn ? 'emr-term-head--focus' : '' ?>">
                                    <span class="emr-term-head__no">Termin <?= (int) ($termColumn['termin_no'] ?? 0) ?></span>
                                    <span class="emr-term-head__stage"><?= htmlspecialchars((string) ($termColumn['stage'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                </th>
                            <?php endforeach; ?>
                            <th class="emr-pic-total-col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($picRows as $picRow): ?>
                            <tr>
                                <td class="emr-pic-row-head">
                                    <span class="emr-pic-row-badge emr-pic-row-badge--<?= htmlspecialchars((string) ($picLabelClasses[$picRow] ?? 'area'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) ($picDisplayLabels[$picRow] ?? $picRow), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <?php foreach ($termColumns as $termColumn): ?>
                                    <?php
                                    $stageLabel = (string) ($termColumn['stage'] ?? '');
                                    $isRfsColumn = strtoupper($stageLabel) === 'RFS';
                                    $termRow = (array) ($termSummaryByStage[$stageLabel] ?? []);
                                    $picData = (array) (($termRow['pic'][$picRow] ?? ['count' => 0, 'value' => 0]));
                                    $picCount = (int) ($picData['count'] ?? 0);
                                    $picValue = (float) ($picData['value'] ?? 0);
                                    $nroFlowData = (array) ($picData['nro_flow'] ?? []);
                                    $picRowTotals[$picRow]['count'] += $picCount;
                                    $picRowTotals[$picRow]['value'] += $picValue;
                                    if (!isset($visibleColumnTotals[$stageLabel])) {
                                        $visibleColumnTotals[$stageLabel] = ['count' => 0, 'value' => 0];
                                    }
                                    $visibleColumnTotals[$stageLabel]['count'] += $picCount;
                                    $visibleColumnTotals[$stageLabel]['value'] += $picValue;
                                    foreach ($nroFlowLabels as $nroStatus => $nroLabel) {
                                        $nroStatusData = (array) ($nroFlowData[$nroStatus] ?? ['count' => 0, 'value' => 0]);
                                        $picRowTotals[$picRow]['nro_flow'][$nroStatus]['count'] += (int) ($nroStatusData['count'] ?? 0);
                                        $picRowTotals[$picRow]['nro_flow'][$nroStatus]['value'] += (float) ($nroStatusData['value'] ?? 0);
                                    }
                                    ?>
                                    <td class="emr-pic-cell <?= $isRfsColumn ? 'emr-pic-cell--focus' : '' ?>">
                                        <button
                                            type="button"
                                            class="emr-pic-drilldown js-po-pic-drilldown <?= $picCount <= 0 ? 'is-zero' : '' ?>"
                                            data-pic="<?= htmlspecialchars($picRow, ENT_QUOTES, 'UTF-8') ?>"
                                            data-pic-label="<?= htmlspecialchars((string) ($picDisplayLabels[$picRow] ?? $picRow), ENT_QUOTES, 'UTF-8') ?>"
                                            data-stage="<?= htmlspecialchars($stageLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $picCount <= 0 ? 'disabled' : '' ?>
                                        >
                                            <?php if (!($picRow === 'NRO' && $isRfsColumn)): ?>
                                                <span class="emr-pic-cell__value"><?= $picValue > 0 ? poEmrNumber($picValue) : '&mdash;' ?></span>
                                                <span class="emr-pic-cell__count"><?= $picCount ?> PO</span>
                                            <?php endif; ?>
                                            <?php if ($picRow === 'NRO' && $isRfsColumn): ?>
                                                <span class="emr-nro-stack">
                                                    <?php foreach ($nroFlowLabels as $nroStatus => $nroLabel): ?>
                                                        <?php $nroStatusData = (array) ($nroFlowData[$nroStatus] ?? ['count' => 0, 'value' => 0]); ?>
                                                        <?php if ((int) ($nroStatusData['count'] ?? 0) > 0): ?>
                                                            <span
                                                                class="emr-nro-stack__item js-po-nro-drilldown"
                                                                data-pic="<?= htmlspecialchars($picRow, ENT_QUOTES, 'UTF-8') ?>"
                                                                data-pic-label="<?= htmlspecialchars((string) ($picDisplayLabels[$picRow] ?? $picRow), ENT_QUOTES, 'UTF-8') ?>"
                                                                data-stage="<?= htmlspecialchars($stageLabel, ENT_QUOTES, 'UTF-8') ?>"
                                                                data-nro-status="<?= htmlspecialchars($nroStatus, ENT_QUOTES, 'UTF-8') ?>"
                                                                data-nro-label="<?= htmlspecialchars($nroLabel, ENT_QUOTES, 'UTF-8') ?>"
                                                            >
                                                                <span class="emr-nro-stack__label"><?= htmlspecialchars($nroLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                                                <span class="emr-nro-stack__value"><?= poEmrNumber((float) ($nroStatusData['value'] ?? 0)) ?></span>
                                                                <span class="emr-nro-stack__count"><?= (int) ($nroStatusData['count'] ?? 0) ?> PO</span>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </span>
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                <?php endforeach; ?>
                                <td class="emr-pic-cell emr-pic-total-cell">
                                    <div class="emr-pic-cell__value"><?= (float) ($picRowTotals[$picRow]['value'] ?? 0) > 0 ? poEmrNumber((float) ($picRowTotals[$picRow]['value'] ?? 0)) : '&mdash;' ?></div>
                                    <div class="emr-pic-cell__count"><?= (int) ($picRowTotals[$picRow]['count'] ?? 0) ?> PO</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($terminPicSummaryRows)): ?>
                            <tr>
                                <td colspan="<?= 2 + count($termColumns) ?>" class="text-center text-muted">Belum ada data PIC per termin.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($terminPicSummaryRows)): ?>
                        <tfoot>
                            <tr>
                                <th class="emr-pic-row-head text-right">TOTAL</th>
                                <?php
                                $grandTotalCount = 0;
                                $grandTotalValue = 0;
                                ?>
                                <?php foreach ($termColumns as $termColumn): ?>
                                    <?php
                                    $stageLabel = (string) ($termColumn['stage'] ?? '');
                                    $isRfsColumn = strtoupper($stageLabel) === 'RFS';
                                    $columnTotal = (array) ($visibleColumnTotals[$stageLabel] ?? ['count' => 0, 'value' => 0]);
                                    $columnTotalCount = (int) ($columnTotal['count'] ?? 0);
                                    $columnTotalValue = (float) ($columnTotal['value'] ?? 0);
                                    $grandTotalCount += $columnTotalCount;
                                    $grandTotalValue += $columnTotalValue;
                                    ?>
                                    <th class="emr-pic-cell emr-pic-total-cell <?= $isRfsColumn ? 'emr-pic-cell--focus' : '' ?>">
                                        <div class="emr-pic-cell__value"><?= $columnTotalValue > 0 ? poEmrNumber($columnTotalValue) : '&mdash;' ?></div>
                                        <div class="emr-pic-cell__count"><?= $columnTotalCount ?> PO</div>
                                    </th>
                                <?php endforeach; ?>
                                <th class="emr-pic-cell emr-pic-total-cell">
                                    <div class="emr-pic-cell__value"><?= $grandTotalValue > 0 ? poEmrNumber($grandTotalValue) : '&mdash;' ?></div>
                                    <div class="emr-pic-cell__count"><?= $grandTotalCount ?> PO</div>
                                </th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Data</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row" id="po-emr-filter-form">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Regional</label>
                        <select name="regional[]" id="po-emr-filter-regional" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Regional" multiple>
                            <?php foreach ($regionalOptions as $regional): ?>
                                <option value="<?= htmlspecialchars((string) $regional, ENT_QUOTES, 'UTF-8') ?>" <?= in_array(strtoupper((string) $regional), $selectedRegionalValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $regional, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kota</label>
                        <select name="city[]" id="po-emr-filter-city" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Kota" multiple>
                            <?php foreach ($cityOptions as $city): ?>
                                <option value="<?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>" <?= in_array(strtoupper((string) $city), $selectedCityValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Stage</label>
                        <select name="stage[]" id="po-emr-filter-stage" class="form-control js-po-emr-searchable emr-filter-select" data-placeholder="Semua Stage" multiple>
                            <?php foreach (['DP', 'ATP CW', 'FULL OPM', 'RFS', 'FAC', 'CLOSED'] as $stageOption): ?>
                                <option value="<?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($stageOption, $selectedStageValues, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Pembagian Termin (Cluster &amp; Subfeeder)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 emr-breakdown-table">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Tipe PO</th>
                            <th rowspan="2">PO QTY</th>
                            <th rowspan="2">Total PO</th>
                            <th colspan="5">Outstanding</th>
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
                        $sumPoCount = 0;
                        $sumTermDone = 0;
                        $sumOutstanding = 0;
                        $sumTermin = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        ?>
                        <?php foreach ($terminBreakdownRows as $index => $row): ?>
                            <?php
                            $sumTotalPo += (float) ($row['total_po_value'] ?? 0);
                            $sumPoCount += (int) ($row['po_count'] ?? 0);
                            $sumTermDone += (int) ($row['term_done_count'] ?? 0);
                            $sumOutstanding += (float) ($row['outstanding_value'] ?? 0);
                            for ($i = 1; $i <= 5; $i++) {
                                $sumTermin[$i] += (float) ($row['termin_values'][$i] ?? 0);
                            }
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars((string) ($row['po_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td class="text-center"><?= (int) ($row['po_count'] ?? 0) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['total_po_value'] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][1] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][2] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][3] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][4] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['termin_values'][5] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['outstanding_value'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($terminBreakdownRows)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data pembagian termin.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($terminBreakdownRows)): ?>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">TOTAL</th>
                                <th class="text-center"><?= (int) $sumPoCount ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTotalPo) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[1]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[2]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[3]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[4]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumTermin[5]) ?></th>
                                <th class="text-right"><?= poEmrNumber($sumOutstanding) ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">List PO</h3>
        </div>
        <div class="card-body">
            <div id="po-emr-drilldown-state" class="emr-drilldown-state">
                <span id="po-emr-drilldown-text"></span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="po-emr-drilldown-reset">Reset</button>
            </div>
            <div class="table-responsive">
                <table id="table_po_emr_target" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>PO</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Tanggal PO</th>
                            <th>Cluster</th>
                            <th>Kota</th>
                            <th>Regional</th>
                            <th>Status Current</th>
                            <th>TERM PO</th>
                            <th>PIC</th>
                            <th>Nilai Tagihan</th>
                            <th>Nilai PO</th>
                            <th>Progress</th>
                            <th>Outstanding</th>
                            <th>Total Invoiced</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    $(function () {
        var tableTarget = null;
        var cityOptionsByRegional = <?= json_encode($cityOptionsByRegional ?? [], JSON_UNESCAPED_SLASHES) ?> || {};
        var regionalOptionsByCity = <?= json_encode($regionalOptionsByCity ?? [], JSON_UNESCAPED_SLASHES) ?> || {};
        var allCityOptions = <?= json_encode($allCityOptions ?? [], JSON_UNESCAPED_SLASHES) ?> || [];
        var allRegionalOptions = <?= json_encode($regionalOptions ?? [], JSON_UNESCAPED_SLASHES) ?> || [];
        var targetAjaxUrl = '<?= base_url('PO_EMR_Myrep/datatablePo') ?>';
        var isUpdatingFilter = false;
        var filterSubmitTimer = null;
        var activeDrilldownPic = '';
        var activeDrilldownPicLabel = '';
        var activeDrilldownStage = '';
        var activeDrilldownNroStatus = '';
        var activeDrilldownNroLabel = '';

        function toValueArray(value) {
            if ($.isArray(value)) {
                return value.map(function (item) {
                    return String(item || '');
                }).filter(function (item) {
                    return item !== '';
                });
            }

            value = String(value || '');
            return value ? [value] : [];
        }

        function uniqueOptions(options) {
            var seen = {};
            var unique = [];
            (options || []).forEach(function (value) {
                value = String(value || '');
                if (value && !seen[value]) {
                    seen[value] = true;
                    unique.push(value);
                }
            });
            return unique;
        }

        function optionsFromMap(selectedValues, optionMap, fallbackOptions) {
            selectedValues = toValueArray(selectedValues);
            if (!selectedValues.length) {
                return fallbackOptions || [];
            }

            var options = [];
            selectedValues.forEach(function (selectedValue) {
                options = options.concat(optionMap[selectedValue] || []);
            });

            return uniqueOptions(options);
        }

        function rebuildOptions($select, options, emptyLabel, selectedValue) {
            var selectedValues = toValueArray(selectedValue);
            var isMultiple = $select.prop('multiple');
            $select.empty();
            if (!isMultiple) {
                $select.append($('<option>').attr('value', '').text(emptyLabel));
            }

            (options || []).forEach(function (value) {
                var label = String(value || '');
                if (!label) {
                    return;
                }
                $select.append($('<option>').attr('value', label).text(label));
            });

            selectedValues = selectedValues.filter(function (selected) {
                return (options || []).indexOf(selected) !== -1;
            });

            if (isMultiple) {
                $select.val(selectedValues);
            } else if (selectedValues.length) {
                $select.val(selectedValues[0]);
            } else {
                $select.val('');
            }
            $select.trigger('change.select2');
        }

        function submitFilter() {
            clearTimeout(filterSubmitTimer);
            filterSubmitTimer = setTimeout(function () {
                $('#po-emr-filter-form').trigger('submit');
            }, 650);
        }

        function currentFilterPayload() {
            return {
                regional: $('#po-emr-filter-regional').val() || [],
                city: $('#po-emr-filter-city').val() || [],
                stage: $('#po-emr-filter-stage').val() || [],
                pic: activeDrilldownPic ? [activeDrilldownPic] : [],
                term_stage: activeDrilldownStage ? [activeDrilldownStage] : [],
                nro_status: activeDrilldownNroStatus ? [activeDrilldownNroStatus] : [],
                back_url: window.location.href.split('#')[0]
            };
        }

        function updateDrilldownState() {
            var hasDrilldown = activeDrilldownPic && activeDrilldownStage;
            $('#po-emr-drilldown-state').toggleClass('is-visible', !!hasDrilldown);
            if (hasDrilldown) {
                var drilldownText = 'List PO difilter: ' + (activeDrilldownPicLabel || activeDrilldownPic) + ' / ' + activeDrilldownStage;
                if (activeDrilldownNroLabel) {
                    drilldownText += ' / ' + activeDrilldownNroLabel;
                }
                $('#po-emr-drilldown-text').text(drilldownText);
            } else {
                $('#po-emr-drilldown-text').text('');
            }

            $('.js-po-pic-drilldown').removeClass('is-active');
            $('.js-po-nro-drilldown').removeClass('is-active');
            if (hasDrilldown) {
                $('.js-po-pic-drilldown').filter(function () {
                    return String($(this).data('pic') || '') === activeDrilldownPic
                        && String($(this).data('stage') || '') === activeDrilldownStage;
                }).addClass('is-active');

                if (activeDrilldownNroStatus) {
                    $('.js-po-nro-drilldown').filter(function () {
                        return String($(this).data('pic') || '') === activeDrilldownPic
                            && String($(this).data('stage') || '') === activeDrilldownStage
                            && String($(this).data('nro-status') || '') === activeDrilldownNroStatus;
                    }).addClass('is-active');
                }
            }
        }

        function reloadTargetTableForDrilldown() {
            updateDrilldownState();
            if (tableTarget) {
                tableTarget.ajax.reload();
            }
        }

        if ($.fn.select2) {
            $('.js-po-emr-searchable').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                closeOnSelect: false,
                placeholder: function () {
                    return $(this).data('placeholder') || '';
                }
            });
        }

        $('#po-emr-filter-regional').on('change', function () {
            if (isUpdatingFilter) {
                return;
            }

            isUpdatingFilter = true;
            var regional = toValueArray($(this).val());
            var currentCity = toValueArray($('#po-emr-filter-city').val());
            var cityOptions = optionsFromMap(regional, cityOptionsByRegional, allCityOptions);
            rebuildOptions($('#po-emr-filter-city'), cityOptions, 'Semua Kota', currentCity);
            isUpdatingFilter = false;
            submitFilter();
        });

        $('#po-emr-filter-city').on('change', function () {
            if (isUpdatingFilter) {
                return;
            }

            isUpdatingFilter = true;
            var city = toValueArray($(this).val());
            var currentRegional = toValueArray($('#po-emr-filter-regional').val());
            var regionalOptions = optionsFromMap(city, regionalOptionsByCity, allRegionalOptions);
            rebuildOptions($('#po-emr-filter-regional'), regionalOptions, 'Semua Regional', currentRegional);
            isUpdatingFilter = false;
            submitFilter();
        });

        $('#po-emr-filter-stage').on('change', function () {
            submitFilter();
        });

        $('.js-po-pic-drilldown').on('click', function () {
            if ($(this).prop('disabled')) {
                return;
            }

            activeDrilldownPic = String($(this).data('pic') || '');
            activeDrilldownPicLabel = String($(this).data('pic-label') || activeDrilldownPic);
            activeDrilldownStage = String($(this).data('stage') || '');
            activeDrilldownNroStatus = '';
            activeDrilldownNroLabel = '';
            reloadTargetTableForDrilldown();
            var $target = $('#table_po_emr_target');
            if ($target.length) {
                $('html, body').animate({ scrollTop: Math.max(0, $target.offset().top - 140) }, 250);
            }
        });

        $('.js-po-nro-drilldown').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            activeDrilldownPic = String($(this).data('pic') || 'NRO');
            activeDrilldownPicLabel = String($(this).data('pic-label') || activeDrilldownPic);
            activeDrilldownStage = String($(this).data('stage') || 'RFS');
            activeDrilldownNroStatus = String($(this).data('nro-status') || '');
            activeDrilldownNroLabel = String($(this).data('nro-label') || activeDrilldownNroStatus);
            reloadTargetTableForDrilldown();
            var $target = $('#table_po_emr_target');
            if ($target.length) {
                $('html, body').animate({ scrollTop: Math.max(0, $target.offset().top - 140) }, 250);
            }
        });

        $('#po-emr-drilldown-reset').on('click', function () {
            activeDrilldownPic = '';
            activeDrilldownPicLabel = '';
            activeDrilldownStage = '';
            activeDrilldownNroStatus = '';
            activeDrilldownNroLabel = '';
            reloadTargetTableForDrilldown();
        });

        function initTargetTable() {
            if (tableTarget || !$.fn.DataTable || !$('#table_po_emr_target').length) {
                return;
            }

            tableTarget = $('#table_po_emr_target').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                responsive: false,
                scrollX: true,
                autoWidth: false,
                stateSave: true,
                stateDuration: -1,
                stateLoadParams: function (settings, data) {
                    return data && data.columns && data.columns.length === 17;
                },
                searchDelay: 500,
                pageLength: 10,
                order: [[4, 'desc']],
                ajax: {
                    url: targetAjaxUrl,
                    type: 'POST',
                    data: function (data) {
                        return $.extend(data, currentFilterPayload());
                    }
                },
                columnDefs: [
                    { targets: [0, 2, 3, 4, 8, 9, 10, 13, 16], className: 'text-center' },
                    { targets: [11, 12, 14, 15], className: 'text-right' },
                    { targets: [0, 10, 11, 16], orderable: false }
                ]
            });
        }

        initTargetTable();
    });
</script>
