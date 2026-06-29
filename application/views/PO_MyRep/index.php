<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$canBatchInvoice = isset($canBatchInvoice) ? (bool) $canBatchInvoice : false;
$canBatchCertificate = isset($canBatchCertificate) ? (bool) $canBatchCertificate : false;

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

if (!function_exists('poMyRepTermValueMap')) {
    function poMyRepTermValueMap($values)
    {
        $map = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        if (!is_array($values)) {
            return $map;
        }

        for ($termNo = 1; $termNo <= 5; $termNo++) {
            $map[$termNo] = (float) ($values[$termNo] ?? 0);
        }

        return $map;
    }
}

$poTotalRows = is_array($poListRows ?? null) ? count($poListRows) : 0;
$poRegionalSeen = [];
foreach (($poListRows ?? []) as $poStatRow) {
    $poRegional = strtoupper(trim((string) ($poStatRow['regional_name'] ?? '')));
    if ($poRegional !== '') {
        $poRegionalSeen[$poRegional] = true;
    }
}
$poTotalRegional = count($poRegionalSeen);
$poTotalCity = is_array($cityOptions ?? null) ? count($cityOptions) : 0;
$certificateReleasedUninvoicedSummary = is_array($certificateReleasedUninvoicedSummary ?? null) ? $certificateReleasedUninvoicedSummary : [];
$certificateSummaryEmptyBucket = ['total' => ['count' => 0, 'value' => 0], 'terms' => []];
for ($certificateSummaryTermNo = 2; $certificateSummaryTermNo <= 5; $certificateSummaryTermNo++) {
    $certificateSummaryEmptyBucket['terms'][$certificateSummaryTermNo] = ['count' => 0, 'value' => 0];
}
foreach (['all', 'ready', 'blocked'] as $certificateSummaryBucket) {
    if (empty($certificateReleasedUninvoicedSummary[$certificateSummaryBucket]) || !is_array($certificateReleasedUninvoicedSummary[$certificateSummaryBucket])) {
        $certificateReleasedUninvoicedSummary[$certificateSummaryBucket] = $certificateSummaryEmptyBucket;
    }
}
if (empty($certificateReleasedUninvoicedSummary['blocked_reasons']) || !is_array($certificateReleasedUninvoicedSummary['blocked_reasons'])) {
    $certificateReleasedUninvoicedSummary['blocked_reasons'] = [];
}
$certificateReleasedUninvoicedCards = [
    [
        'label' => 'Total',
        'hint' => 'Semua termin ready invoice',
        'term_no' => 0,
        'all_count' => (int) ($certificateReleasedUninvoicedSummary['all']['total']['count'] ?? 0),
        'all_value' => (float) ($certificateReleasedUninvoicedSummary['all']['total']['value'] ?? 0),
        'ready_count' => (int) ($certificateReleasedUninvoicedSummary['ready']['total']['count'] ?? 0),
        'ready_value' => (float) ($certificateReleasedUninvoicedSummary['ready']['total']['value'] ?? 0),
        'blocked_count' => (int) ($certificateReleasedUninvoicedSummary['blocked']['total']['count'] ?? 0),
        'blocked_value' => (float) ($certificateReleasedUninvoicedSummary['blocked']['total']['value'] ?? 0),
        'icon' => 'fas fa-file-invoice-dollar',
        'accent' => 'teal',
    ],
];
foreach ([2, 3, 4, 5] as $certificateSummaryTermNo) {
    $certificateReleasedUninvoicedCards[] = [
        'label' => 'Term ' . $certificateSummaryTermNo,
        'hint' => 'Sertifikat rilis, invoice kosong',
        'term_no' => $certificateSummaryTermNo,
        'all_count' => (int) ($certificateReleasedUninvoicedSummary['all']['terms'][$certificateSummaryTermNo]['count'] ?? 0),
        'all_value' => (float) ($certificateReleasedUninvoicedSummary['all']['terms'][$certificateSummaryTermNo]['value'] ?? 0),
        'ready_count' => (int) ($certificateReleasedUninvoicedSummary['ready']['terms'][$certificateSummaryTermNo]['count'] ?? 0),
        'ready_value' => (float) ($certificateReleasedUninvoicedSummary['ready']['terms'][$certificateSummaryTermNo]['value'] ?? 0),
        'blocked_count' => (int) ($certificateReleasedUninvoicedSummary['blocked']['terms'][$certificateSummaryTermNo]['count'] ?? 0),
        'blocked_value' => (float) ($certificateReleasedUninvoicedSummary['blocked']['terms'][$certificateSummaryTermNo]['value'] ?? 0),
        'icon' => 'fas fa-certificate',
        'accent' => ['slate', 'blue', 'amber', 'green'][$certificateSummaryTermNo - 2],
    ];
}
$certificateReadyDefaultCopy = 'Menampilkan termin yang sudah rilis sertifikat, belum invoice, dan invoice term sebelumnya sudah lengkap.';
$certificateAllReleasedCopy = 'Menampilkan semua termin yang sudah rilis sertifikat namun belum invoice, termasuk yang masih tertahan invoice term sebelumnya.';
$poTerminBreakdownConsoleRows = [];
if (is_array($terminBreakdownRows ?? null)) {
    foreach ($terminBreakdownRows as $debugIndex => &$terminBreakdownRow) {
        if (!is_array($terminBreakdownRow)) {
            $poTerminBreakdownConsoleRows[] = ['index' => $debugIndex, 'issue' => 'row_not_array'];
            $terminBreakdownRow = [];
        }

        $missingKeys = [];
        if (!array_key_exists('termin_values', $terminBreakdownRow) || !is_array($terminBreakdownRow['termin_values'])) {
            $missingKeys[] = 'termin_values';
            $terminBreakdownRow['termin_values'] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }
        if (!array_key_exists('done_invoice_values', $terminBreakdownRow) || !is_array($terminBreakdownRow['done_invoice_values'])) {
            $missingKeys[] = 'done_invoice_values';
            $terminBreakdownRow['done_invoice_values'] = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }

        $terminBreakdownRow['termin_values'] = poMyRepTermValueMap($terminBreakdownRow['termin_values']);
        $terminBreakdownRow['done_invoice_values'] = poMyRepTermValueMap($terminBreakdownRow['done_invoice_values']);

        if (!empty($missingKeys)) {
            $poTerminBreakdownConsoleRows[] = [
                'index' => $debugIndex,
                'po_type' => (string) ($terminBreakdownRow['po_type'] ?? '-'),
                'missing_keys' => $missingKeys,
            ];
        }
    }
    unset($terminBreakdownRow);
}
?>

<style>
    .po-myrep-revamp {
        --po-ink: #0f172a;
        --po-muted: #64748b;
        --po-line: rgba(148, 163, 184, 0.22);
        --po-surface: rgba(255, 255, 255, 0.96);
        --po-shadow: 0 24px 48px rgba(15, 23, 42, 0.10);
        --po-blue: #2563eb;
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
    }

    .po-myrep-revamp .content-header {
        padding-bottom: 0;
    }

    .po-shell {
        padding: 1rem;
    }

    .po-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 30%),
            linear-gradient(135deg, #0f2c49 0%, #102f50 48%, #27588d 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.18);
        color: #f8fafc;
    }

    .po-hero__grid {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.2rem;
        padding: 1.25rem;
    }

    .po-hero__eyebrow,
    .po-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-hero__eyebrow {
        padding: 0.35rem 0.7rem;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .po-hero h1 {
        margin: 0.9rem 0 0.55rem;
        color: #fff;
        font-size: 1.72rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .po-hero p {
        max-width: 48rem;
        margin: 0;
        color: rgba(226, 232, 240, 0.9);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .po-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.05rem;
    }

    .po-hero__stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        align-content: start;
    }

    .po-hero-stat {
        min-height: 90px;
        padding: 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.11);
        backdrop-filter: blur(8px);
    }

    .po-hero-stat__label {
        display: block;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.07em;
        text-transform: uppercase;
    }

    .po-hero-stat__value {
        display: block;
        margin-top: 0.3rem;
        color: #fff;
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1;
    }

    .po-hero-stat__hint {
        display: block;
        margin-top: 0.5rem;
        color: rgba(226, 232, 240, 0.72);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .po-panel {
        margin-top: 1rem;
        border: 1px solid var(--po-line);
        border-radius: 12px;
        background: var(--po-surface);
        box-shadow: var(--po-shadow);
        overflow: hidden;
    }

    .po-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.05rem 1.15rem 0;
    }

    .po-panel__title {
        margin: 0;
        color: var(--po-ink);
        font-size: 1rem;
        font-weight: 900;
    }

    .po-panel__subtitle {
        margin: 0.25rem 0 0;
        color: var(--po-muted);
        font-size: 0.88rem;
    }

    .po-panel__body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .po-chip {
        margin-bottom: 0.45rem;
        padding: 0.33rem 0.68rem;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-weight: 800;
    }

    .po-filter-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .po-filter-grid .form-group {
        margin-bottom: 0;
    }

    .po-filter-grid label {
        margin-bottom: 0.42rem;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        margin-top: 0.85rem;
    }

    .po-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 38px;
        padding: 0.6rem 0.9rem;
        border: 0;
        border-radius: 8px;
        font-weight: 800;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .po-btn:hover,
    .po-btn:focus {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .po-btn--primary {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
    }

    .po-btn--primary:hover,
    .po-btn--primary:focus {
        color: #fff;
        background: #1d4ed8;
    }

    .po-btn--light {
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.24);
    }

    .po-btn--light:hover,
    .po-btn--light:focus {
        color: #0f172a;
        background: #fff;
        border-color: rgba(148, 163, 184, 0.38);
    }

    .po-cert-release-panel {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
    }

    .po-cert-release-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }

    .po-cert-release-panel__tools {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.55rem;
    }

    .po-cert-release-panel__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.35rem;
        padding: 0.26rem 0.55rem;
        border-radius: 999px;
        background: rgba(20, 184, 166, 0.1);
        color: #0f766e;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-cert-release-panel__title {
        margin: 0;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .po-cert-release-panel__copy {
        max-width: 760px;
        margin: 0.28rem 0 0;
        color: #64748b;
        font-size: 0.86rem;
        line-height: 1.55;
    }

    .po-cert-release-panel__note {
        padding: 0.5rem 0.65rem;
        border: 1px solid rgba(37, 99, 235, 0.16);
        border-radius: 8px;
        background: #eff6ff;
        color: #1e40af;
        font-size: 0.76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .po-cert-release-toggle {
        display: inline-flex;
        padding: 0.22rem;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 999px;
        background: #f8fafc;
    }

    .po-cert-release-toggle__btn {
        min-width: 108px;
        padding: 0.42rem 0.72rem;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
        transition: background .16s ease, color .16s ease, box-shadow .16s ease;
    }

    .po-cert-release-toggle__btn:focus {
        outline: none;
    }

    .po-cert-release-toggle__btn.is-active {
        background: #0f172a;
        color: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
    }

    .po-table-mode-toggle {
        display: inline-flex;
        margin-left: auto;
        padding: 0.22rem;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 999px;
        background: #f8fafc;
    }

    .po-termin-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 52px;
    }

    .po-termin-card-header .card-title {
        float: none;
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .po-table-mode-toggle__btn {
        min-width: 104px;
        padding: 0.42rem 0.72rem;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
        transition: background .16s ease, color .16s ease, box-shadow .16s ease;
    }

    .po-table-mode-toggle__btn:focus {
        outline: none;
    }

    .po-table-mode-toggle__btn.is-active {
        background: #0f172a;
        color: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
    }

    .po-termin-table-pane.is-hidden {
        display: none;
    }

    .po-cert-release-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .po-cert-release-card {
        position: relative;
        min-height: 132px;
        overflow: hidden;
        padding: 0.95rem;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 8px;
        background: #f8fafc;
    }

    .po-cert-release-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--cert-accent, #0f766e);
    }

    .po-cert-release-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .po-cert-release-card__actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .po-cert-release-card__label {
        color: #475569;
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-cert-release-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: #fff;
        color: var(--cert-accent, #0f766e);
    }

    .po-cert-release-card__detail {
        border: 0;
        background: transparent;
        color: var(--cert-accent, #0f766e);
        font-size: 0.72rem;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
        padding: 0.25rem 0;
    }

    .po-cert-release-card__detail:hover,
    .po-cert-release-card__detail:focus {
        color: var(--cert-accent, #0f766e);
        text-decoration: underline;
        outline: none;
    }

    .po-cert-release-card__count {
        display: block;
        margin-top: 0.72rem;
        color: #0f172a;
        font-size: 1.85rem;
        font-weight: 900;
        line-height: 1;
    }

    .po-cert-release-card__sum {
        display: block;
        margin-top: 0.5rem;
        color: var(--cert-accent, #0f766e);
        font-size: 0.86rem;
        font-weight: 900;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .po-cert-release-card__hint {
        display: block;
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .po-cert-release-card__blocked {
        display: block;
        margin-top: 0.45rem;
        padding-top: 0.45rem;
        border-top: 1px solid rgba(148, 163, 184, 0.24);
        color: #b45309;
        font-size: 0.74rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .po-cert-release-blocked {
        margin-top: 0.85rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid rgba(245, 158, 11, 0.24);
        border-radius: 8px;
        background: #fffbeb;
        color: #92400e;
        font-size: 0.82rem;
        font-weight: 800;
        line-height: 1.5;
    }

    .po-cert-release-blocked.is-hidden,
    .po-cert-release-card__blocked.is-hidden {
        display: none;
    }

    .po-modal-xxl {
        max-width: 78vw;
    }

    .po-cert-release-detail-section + .po-cert-release-detail-section {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(148, 163, 184, 0.24);
    }

    .po-cert-release-detail-section.is-hidden {
        display: none;
    }

    .po-cert-release-detail-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.65rem;
    }

    .po-cert-release-detail-section__title {
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 900;
    }

    .po-cert-release-detail-section__meta {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .po-cert-release-card--teal {
        --cert-accent: #0f766e;
        background: linear-gradient(135deg, #f0fdfa, #ffffff);
    }

    .po-cert-release-card--slate {
        --cert-accent: #475569;
    }

    .po-cert-release-card--blue {
        --cert-accent: #2563eb;
        background: linear-gradient(135deg, #eff6ff, #ffffff);
    }

    .po-cert-release-card--amber {
        --cert-accent: #b45309;
        background: linear-gradient(135deg, #fffbeb, #ffffff);
    }

    .po-cert-release-card--green {
        --cert-accent: #16a34a;
        background: linear-gradient(135deg, #f0fdf4, #ffffff);
    }

    @media (max-width: 1199px) {
        .po-cert-release-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .po-cert-release-panel__head {
            flex-direction: column;
        }

        .po-cert-release-panel__tools {
            width: 100%;
            align-items: stretch;
        }

        .po-cert-release-panel__note {
            width: 100%;
            text-align: center;
            white-space: normal;
        }

        .po-cert-release-grid {
            grid-template-columns: 1fr;
        }

        .po-modal-xxl {
            max-width: calc(100vw - 1rem);
        }

        .po-termin-card-header {
            align-items: stretch;
            flex-direction: column;
        }

        .po-table-mode-toggle {
            width: 100%;
        }

        .po-table-mode-toggle__btn {
            flex: 1 1 0;
            min-width: 0;
        }
    }

    .po-myrep-revamp .form-control,
    .po-myrep-revamp .custom-select {
        min-height: 42px;
        border-radius: 10px;
        border-color: rgba(148, 163, 184, 0.34);
        background-color: rgba(255, 255, 255, 0.94);
        color: #0f172a;
        font-size: 0.9rem;
        box-shadow: none;
    }

    .po-myrep-revamp .form-control:focus,
    .po-myrep-revamp .custom-select:focus {
        border-color: rgba(37, 99, 235, 0.46);
        box-shadow: 0 0 0 0.18rem rgba(37, 99, 235, 0.12) !important;
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

    .po-batch-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .po-batch-summary-card {
        min-height: 88px;
        padding: 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 8px;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
    }

    .po-batch-summary-card__label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .po-batch-summary-card__count {
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

    .po-batch-summary-card__value {
        display: block;
        margin-top: 0.65rem;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .po-batch-summary-card--total {
        grid-column: 1 / -1;
        min-height: 72px;
        background: linear-gradient(135deg, #eff6ff, #ffffff);
        border-color: rgba(37, 99, 235, 0.22);
    }

    .po-batch-status-filters {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .po-batch-status-filter,
    .po-cert-status-filter {
        width: 100%;
        text-align: left;
        cursor: pointer;
        font: inherit;
        appearance: none;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .po-batch-status-filter:focus,
    .po-cert-status-filter:focus {
        outline: none;
    }

    .po-batch-status-filter.is-active,
    .po-cert-status-filter.is-active {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14), 0 12px 26px rgba(15, 23, 42, .1);
        transform: translateY(-1px);
    }

    .po-batch-filter-active-badge {
        display: none;
        margin-left: auto;
        padding: 0.15rem 0.42rem;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        font-size: 0.64rem;
        font-weight: 900;
        letter-spacing: .04em;
    }

    .po-batch-status-filter.is-active .po-batch-filter-active-badge,
    .po-cert-status-filter.is-active .po-batch-filter-active-badge {
        display: inline-flex;
        align-items: center;
    }

    .po-cert-table-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .po-cert-table-tools__filter {
        min-width: 260px;
    }

    .po-cert-table-tools__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .po-batch-invoice__toolbar {
            grid-template-columns: 1fr;
        }

        .po-batch-summary,
        .po-batch-status-filters {
            grid-template-columns: 1fr;
        }

        .po-shell {
            padding: 0.75rem;
        }

        .po-hero__grid,
        .po-hero__stats,
        .po-filter-grid {
            grid-template-columns: 1fr;
        }

        .po-panel__head,
        .po-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="content-wrapper po-myrep-revamp">
    <section class="content-header">
        <div class="container-fluid po-shell">
            <section class="po-hero">
                <div class="po-hero__grid">
                    <div>
                        <span class="po-hero__eyebrow">
                            <i class="fas fa-file-invoice-dollar"></i>
                            PO MyRep Intelligence
                        </span>
                        <h1>Dashboard monitoring PO dan invoice termin MyRep</h1>
                        <p>
                            Pantau status PO cluster dan subfeeder, progress invoice termin, nilai outstanding, serta kesiapan
                            sertifikat billing dalam satu dashboard operasional.
                        </p>
                        <div class="po-hero__actions">
                            <?php if ($canBatchInvoice): ?>
                                <button type="button" class="po-btn po-btn--primary" data-toggle="modal" data-target="#modal-batch-invoice-termin">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    Batch Input Invoice Termin
                                </button>
                                <?php if ($canBatchCertificate): ?>
                                    <button type="button" class="po-btn po-btn--light" data-toggle="modal" data-target="#modal-batch-certificate-termin">
                                        <i class="fas fa-certificate"></i>
                                        Batch Status/Tanggal Sertifikat
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="<?= base_url('PO_MyRep/mainfeeder') ?>" class="po-btn po-btn--light">
                                <i class="fas fa-project-diagram"></i>
                                PO Mainfeeder
                            </a>
                        </div>
                    </div>

                    <div class="po-hero__stats">
                        <div class="po-hero-stat">
                            <span class="po-hero-stat__label">Total Cluster</span>
                            <span class="po-hero-stat__value"><?= poMyRepNumber((int) ($summary['total_cluster'] ?? 0)) ?></span>
                            <span class="po-hero-stat__hint">Cluster MyRep dalam cakupan filter.</span>
                        </div>
                        <div class="po-hero-stat">
                            <span class="po-hero-stat__label">PO Aktif</span>
                            <span class="po-hero-stat__value"><?= poMyRepNumber($poTotalRows) ?></span>
                            <span class="po-hero-stat__hint">Baris PO cluster dan subfeeder tersedia.</span>
                        </div>
                        <div class="po-hero-stat">
                            <span class="po-hero-stat__label">Regional</span>
                            <span class="po-hero-stat__value"><?= poMyRepNumber($poTotalRegional) ?></span>
                            <span class="po-hero-stat__hint">Regional yang masuk pemantauan PO.</span>
                        </div>
                        <div class="po-hero-stat">
                            <span class="po-hero-stat__label">Area / Kota</span>
                            <span class="po-hero-stat__value"><?= poMyRepNumber($poTotalCity) ?></span>
                            <span class="po-hero-stat__hint">Kota yang tersedia untuk filter PO.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid po-shell pt-0">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <?php if (!$isReady): ?>
                <div class="alert alert-warning">Tabel PO MyRep belum tersedia.</div>
            <?php else: ?>
                <section class="po-panel">
                    <div class="po-panel__head">
                        <div>
                            <span class="po-chip"><i class="fas fa-sliders-h"></i> Kontrol Data</span>
                            <h1 class="po-panel__title">Filter Data</h1>
                            <p class="po-panel__subtitle">Pilih kota dan status PO untuk membaca report sesuai kebutuhan.</p>
                        </div>
                    </div>
                    <div class="po-panel__body">
                        <form method="get">
                            <div class="po-filter-grid">
                                <div class="form-group">
                                    <label for="po_filter_city">Kota</label>
                                    <select name="city" id="po_filter_city" class="form-control">
                                        <option value="">Semua Kota</option>
                                        <?php foreach ($cityOptions as $city): ?>
                                            <option value="<?= htmlspecialchars($city) ?>" <?= strtoupper((string) $selectedCity) === strtoupper((string) $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="po_filter_status">Status PO</label>
                                    <select name="status" id="po_filter_status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (['DP', 'ATP CW', 'FULL OPM', 'RFS', 'FAC'] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="po-actions">
                                <a href="<?= base_url('PO_MyRep') ?>" class="po-btn po-btn--light">
                                    <i class="fas fa-undo-alt"></i>
                                    Reset
                                </a>
                                <button type="submit" class="po-btn po-btn--primary">
                                    <i class="fas fa-search"></i>
                                    Terapkan
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="po-cert-release-panel">
                    <div class="po-cert-release-panel__head">
                        <div>
                            <span class="po-cert-release-panel__eyebrow">
                                <i class="fas fa-certificate"></i>
                                Ready To Invoice
                            </span>
                            <h2 class="po-cert-release-panel__title">Sertifikat sudah rilis, invoice belum masuk</h2>
                            <p class="po-cert-release-panel__copy" id="po-cert-release-copy"
                                data-ready-copy="<?= htmlspecialchars($certificateReadyDefaultCopy, ENT_QUOTES) ?>"
                                data-all-copy="<?= htmlspecialchars($certificateAllReleasedCopy, ENT_QUOTES) ?>">
                                <?= htmlspecialchars($certificateReadyDefaultCopy) ?>
                            </p>
                        </div>
                        <div class="po-cert-release-panel__tools">
                            <div class="po-cert-release-toggle" role="group" aria-label="Mode summary sertifikat">
                                <button type="button" class="po-cert-release-toggle__btn" data-cert-summary-mode="all">All Released</button>
                                <button type="button" class="po-cert-release-toggle__btn is-active" data-cert-summary-mode="ready">Ready Invoice</button>
                            </div>
                            <div class="po-cert-release-panel__note">
                                Count PO-Term
                            </div>
                        </div>
                    </div>
                    <div class="po-cert-release-grid">
                        <?php foreach ($certificateReleasedUninvoicedCards as $certificateSummaryCard): ?>
                            <article class="po-cert-release-card po-cert-release-card--<?= htmlspecialchars((string) ($certificateSummaryCard['accent'] ?? 'teal'), ENT_QUOTES) ?>">
                                <div class="po-cert-release-card__top">
                                    <span class="po-cert-release-card__label"><?= htmlspecialchars((string) ($certificateSummaryCard['label'] ?? '-')) ?></span>
                                    <span class="po-cert-release-card__actions">
                                        <button type="button" class="po-cert-release-card__detail js-open-cert-release-detail"
                                            data-term-no="<?= (int) ($certificateSummaryCard['term_no'] ?? 0) ?>"
                                            data-label="<?= htmlspecialchars((string) ($certificateSummaryCard['label'] ?? '-'), ENT_QUOTES) ?>">
                                            Lihat detail
                                        </button>
                                        <span class="po-cert-release-card__icon">
                                            <i class="<?= htmlspecialchars((string) ($certificateSummaryCard['icon'] ?? 'fas fa-certificate'), ENT_QUOTES) ?>"></i>
                                        </span>
                                    </span>
                                </div>
                                <span class="po-cert-release-card__count"
                                    data-ready-count="<?= (int) ($certificateSummaryCard['ready_count'] ?? 0) ?>"
                                    data-all-count="<?= (int) ($certificateSummaryCard['all_count'] ?? 0) ?>">
                                    <?= poMyRepNumber((int) ($certificateSummaryCard['ready_count'] ?? 0)) ?>
                                </span>
                                <span class="po-cert-release-card__sum"
                                    data-ready-value="<?= (float) ($certificateSummaryCard['ready_value'] ?? 0) ?>"
                                    data-all-value="<?= (float) ($certificateSummaryCard['all_value'] ?? 0) ?>">
                                    Nilai <?= poMyRepNumber((float) ($certificateSummaryCard['ready_value'] ?? 0)) ?>
                                </span>
                                <span class="po-cert-release-card__hint"><?= htmlspecialchars((string) ($certificateSummaryCard['hint'] ?? '')) ?></span>
                                <span class="po-cert-release-card__blocked is-hidden">
                                    Blocked <?= poMyRepNumber((int) ($certificateSummaryCard['blocked_count'] ?? 0)) ?> | Nilai <?= poMyRepNumber((float) ($certificateSummaryCard['blocked_value'] ?? 0)) ?>
                                </span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="po-cert-release-blocked is-hidden" id="po-cert-release-blocked-note">
                        <i class="fas fa-info-circle mr-1"></i>
                        Blocked by previous term:
                        <?php
                        $certificateBlockedReasonTexts = [];
                        foreach (($certificateReleasedUninvoicedSummary['blocked_reasons'] ?? []) as $certificateBlockedReason) {
                            $certificateBlockedCount = (int) ($certificateBlockedReason['count'] ?? 0);
                            if ($certificateBlockedCount <= 0) {
                                continue;
                            }
                            $certificateBlockedReasonTexts[] = htmlspecialchars((string) ($certificateBlockedReason['label'] ?? '-')) . ': ' . poMyRepNumber($certificateBlockedCount) . ' | Nilai ' . poMyRepNumber((float) ($certificateBlockedReason['value'] ?? 0));
                        }
                        ?>
                        <?= !empty($certificateBlockedReasonTexts) ? implode(' · ', $certificateBlockedReasonTexts) : 'Tidak ada data blocked.' ?>
                    </div>
                </section>

                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header po-termin-card-header">
                        <h3 class="card-title">Pembagian Termin (Cluster & Subfeeder)</h3>
                        <div class="po-table-mode-toggle" role="group" aria-label="Mode pembagian termin">
                            <button type="button" class="po-table-mode-toggle__btn is-active" data-termin-table-mode="outstanding">Outstanding</button>
                            <button type="button" class="po-table-mode-toggle__btn" data-termin-table-mode="summary">Summary</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        $sumPoQty = 0;
                        $sumTotalPo = 0;
                        $sumTotalInvoiced = 0;
                        $sumOutstanding = 0;
                        $sumTermin = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        $sumInvoiceTermin = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        foreach ($terminBreakdownRows as $row) {
                            $sumPoQty += (int) ($row['total_po_count'] ?? 0);
                            $sumTotalPo += (float) ($row['total_po_value'] ?? 0);
                            $sumTotalInvoiced += (float) ($row['total_invoiced_value'] ?? 0);
                            $sumOutstanding += (float) ($row['outstanding_value'] ?? 0);
                            $rowOutstandingValues = poMyRepTermValueMap(array_key_exists('termin_values', $row) ? $row['termin_values'] : []);
                            $rowInvoiceValues = poMyRepTermValueMap(array_key_exists('done_invoice_values', $row) ? $row['done_invoice_values'] : []);
                            for ($i = 1; $i <= 5; $i++) {
                                $sumTermin[$i] += (float) ($rowOutstandingValues[$i] ?? 0);
                                $sumInvoiceTermin[$i] += (float) ($rowInvoiceValues[$i] ?? 0);
                            }
                        }
                        ?>
                        <div class="table-responsive po-termin-table-pane" data-termin-table-pane="outstanding">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Tipe PO</th>
                                        <th rowspan="2">PO QTY</th>
                                        <th rowspan="2">Total PO Value</th>
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
                                    <?php foreach ($terminBreakdownRows as $index => $row): ?>
                                        <?php $rowOutstandingValues = poMyRepTermValueMap(array_key_exists('termin_values', $row) ? $row['termin_values'] : []); ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars((string) ($row['po_type'] ?? '-')) ?></strong></td>
                                            <td class="text-center"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="po_qty"><?= (int) ($row['total_po_count'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_po"><?= poMyRepNumber((float) ($row['total_po_value'] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="1"><?= poMyRepNumber((float) ($rowOutstandingValues[1] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="2"><?= poMyRepNumber((float) ($rowOutstandingValues[2] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="3"><?= poMyRepNumber((float) ($rowOutstandingValues[3] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="4"><?= poMyRepNumber((float) ($rowOutstandingValues[4] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="5"><?= poMyRepNumber((float) ($rowOutstandingValues[5] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_invoiced"><?= poMyRepNumber((float) ($row['total_invoiced_value'] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_total"><?= poMyRepNumber((float) ($row['outstanding_value'] ?? 0)) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($terminBreakdownRows)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">Belum ada data pembagian termin.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($terminBreakdownRows)): ?>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">TOTAL</th>
                                            <th class="text-center"><?= (int) $sumPoQty ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTotalPo) ?></th>
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
                        <div class="table-responsive po-termin-table-pane is-hidden" data-termin-table-pane="summary">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Tipe PO</th>
                                        <th rowspan="2">PO QTY</th>
                                        <th rowspan="2">Total PO Value</th>
                                        <th colspan="2" class="text-center">Term 1</th>
                                        <th colspan="2" class="text-center">Term 2</th>
                                        <th colspan="2" class="text-center">Term 3</th>
                                        <th colspan="2" class="text-center">Term 4</th>
                                        <th colspan="2" class="text-center">Term 5</th>
                                        <th rowspan="2">Total Invoice</th>
                                        <th rowspan="2">Outstanding Total</th>
                                    </tr>
                                    <tr>
                                        <?php for ($terminSummaryTerm = 1; $terminSummaryTerm <= 5; $terminSummaryTerm++): ?>
                                            <th>Invoice</th>
                                            <th>Outstanding</th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($terminBreakdownRows as $index => $row): ?>
                                        <?php
                                        $rowOutstandingValues = poMyRepTermValueMap(array_key_exists('termin_values', $row) ? $row['termin_values'] : []);
                                        $rowInvoiceValues = poMyRepTermValueMap(array_key_exists('done_invoice_values', $row) ? $row['done_invoice_values'] : []);
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars((string) ($row['po_type'] ?? '-')) ?></strong></td>
                                            <td class="text-center"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="po_qty"><?= (int) ($row['total_po_count'] ?? 0) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_po"><?= poMyRepNumber((float) ($row['total_po_value'] ?? 0)) ?></span></td>
                                            <?php for ($terminSummaryTerm = 1; $terminSummaryTerm <= 5; $terminSummaryTerm++): ?>
                                                <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="invoice_term" data-term-no="<?= $terminSummaryTerm ?>"><?= poMyRepNumber((float) $rowInvoiceValues[$terminSummaryTerm]) ?></span></td>
                                                <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_term" data-term-no="<?= $terminSummaryTerm ?>"><?= poMyRepNumber((float) $rowOutstandingValues[$terminSummaryTerm]) ?></span></td>
                                            <?php endfor; ?>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="total_invoiced"><?= poMyRepNumber((float) ($row['total_invoiced_value'] ?? 0)) ?></span></td>
                                            <td class="text-right"><span class="po-breakdown-link js-open-breakdown" data-po-type="<?= htmlspecialchars((string) ($row['po_type'] ?? 'CLUSTER'), ENT_QUOTES) ?>" data-metric="outstanding_total"><?= poMyRepNumber((float) ($row['outstanding_value'] ?? 0)) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($terminBreakdownRows)): ?>
                                        <tr>
                                            <td colspan="16" class="text-center text-muted">Belum ada data pembagian termin.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($terminBreakdownRows)): ?>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">TOTAL</th>
                                            <th class="text-center"><?= (int) $sumPoQty ?></th>
                                            <th class="text-right"><?= poMyRepNumber($sumTotalPo) ?></th>
                                            <?php for ($terminSummaryTerm = 1; $terminSummaryTerm <= 5; $terminSummaryTerm++): ?>
                                                <th class="text-right"><?= poMyRepNumber($sumInvoiceTermin[$terminSummaryTerm]) ?></th>
                                                <th class="text-right"><?= poMyRepNumber($sumTermin[$terminSummaryTerm]) ?></th>
                                            <?php endfor; ?>
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
                                            <tr><td colspan="10" class="text-center text-muted">Memuat data...</td></tr>
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
                                                <th class="text-center">INVOICE</th>
                                                <th class="text-center">OUTSTANDING</th>
                                                <th class="text-center">INVOICE</th>
                                                <th class="text-center">OUTSTANDING</th>
                                                <th class="text-center">INVOICE</th>
                                                <th class="text-center">OUTSTANDING</th>
                                                <th class="text-center">INVOICE</th>
                                                <th class="text-center">OUTSTANDING</th>
                                                <th class="text-center">INVOICE</th>
                                                <th class="text-center">OUTSTANDING</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="23" class="text-center text-muted">Memuat data...</td></tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="8" class="text-right">TOTAL NILAI PO</th>
                                                <th class="text-right po-list-footer-nilai-po" id="po-list-footer-nilai-po">0</th>
                                                <th></th>
                                                <th class="text-right po-list-footer-done-1" id="po-list-footer-done-1">-</th>
                                                <th class="text-right po-list-footer-outstanding-1" id="po-list-footer-outstanding-1">-</th>
                                                <th class="text-right po-list-footer-done-2" id="po-list-footer-done-2">-</th>
                                                <th class="text-right po-list-footer-outstanding-2" id="po-list-footer-outstanding-2">-</th>
                                                <th class="text-right po-list-footer-done-3" id="po-list-footer-done-3">-</th>
                                                <th class="text-right po-list-footer-outstanding-3" id="po-list-footer-outstanding-3">-</th>
                                                <th class="text-right po-list-footer-done-4" id="po-list-footer-done-4">-</th>
                                                <th class="text-right po-list-footer-outstanding-4" id="po-list-footer-outstanding-4">-</th>
                                                <th class="text-right po-list-footer-done-5" id="po-list-footer-done-5">-</th>
                                                <th class="text-right po-list-footer-outstanding-5" id="po-list-footer-outstanding-5">-</th>
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

<?php if ($isReady && $canBatchInvoice): ?>
    <div class="modal fade" id="modal-batch-invoice-termin" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('PO_MyRep/batchInvoiceTermin') ?>" id="po-batch-invoice-form">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">Batch Input Invoice Termin</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Invoice General</label>
                                    <input type="date" name="invoice_date" id="po-batch-invoice-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
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

                        <ul class="nav nav-tabs" id="po-batch-invoice-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="po-batch-manual-tab" data-toggle="pill" href="#po-batch-manual-pane" role="tab" aria-controls="po-batch-manual-pane" aria-selected="true">Input Manual</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="po-batch-paste-tab" data-toggle="pill" href="#po-batch-paste-pane" role="tab" aria-controls="po-batch-paste-pane" aria-selected="false">Paste dari Excel</a>
                            </li>
                        </ul>

                        <div class="tab-content border-left border-right border-bottom p-3 mb-3" id="po-batch-invoice-tab-content">
                            <div class="tab-pane fade show active" id="po-batch-manual-pane" role="tabpanel" aria-labelledby="po-batch-manual-tab">
                                <div class="po-batch-invoice__toolbar">
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
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="po-batch-paste-pane" role="tabpanel" aria-labelledby="po-batch-paste-tab">
                                <div class="form-group">
                                    <label>Data Invoice</label>
                                    <textarea id="po-batch-paste" class="form-control po-batch-invoice__paste" placeholder="PO Number[TAB]Term[TAB]Nilai Invoice&#10;PO-001[TAB]1[TAB]1000000&#10;PO-002[TAB]Term 2"></textarea>
                                    <small class="form-text text-muted">Nilai invoice boleh kosong jika memakai estimasi PO dan term.</small>
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                    <button type="button" class="btn btn-outline-secondary" id="po-batch-parse-paste">Cek PO</button>
                                    <button type="button" class="btn btn-outline-danger" id="po-batch-clear-list" disabled>Hapus List</button>
                                </div>
                            </div>
                        </div>

                        <div class="po-batch-summary" id="po-batch-summary">
                            <?php for ($poBatchTermSummary = 1; $poBatchTermSummary <= 5; $poBatchTermSummary++): ?>
                                <div class="po-batch-summary-card">
                                    <span class="po-batch-summary-card__label">
                                        Termin <?= $poBatchTermSummary ?>
                                        <span class="po-batch-summary-card__count" id="po-batch-summary-count-<?= $poBatchTermSummary ?>">0</span>
                                    </span>
                                    <span class="po-batch-summary-card__value" id="po-batch-summary-term-<?= $poBatchTermSummary ?>">0</span>
                                </div>
                            <?php endfor; ?>
                            <div class="po-batch-summary-card po-batch-summary-card--total">
                                <span class="po-batch-summary-card__label">
                                    Total Invoice
                                    <span class="po-batch-summary-card__count" id="po-batch-summary-total-count">0</span>
                                </span>
                                <span class="po-batch-summary-card__value" id="po-batch-summary-total-value">0</span>
                            </div>
                            <div class="po-batch-status-filters">
                                <button type="button" class="po-batch-summary-card po-batch-status-filter" data-batch-status-filter="success">
                                    <span class="po-batch-summary-card__label">
                                        Valid
                                        <span class="po-batch-summary-card__count" id="po-batch-status-valid-count">0</span>
                                    </span>
                                    <span class="po-batch-summary-card__value text-success" id="po-batch-status-valid-value">0</span>
                                </button>
                                <button type="button" class="po-batch-summary-card po-batch-status-filter" data-batch-status-filter="invalid">
                                    <span class="po-batch-summary-card__label">
                                        Invalid
                                        <span class="po-batch-summary-card__count" id="po-batch-status-invalid-count">0</span>
                                    </span>
                                    <span class="po-batch-summary-card__value text-danger" id="po-batch-status-invalid-value">0</span>
                                </button>
                                <button type="button" class="po-batch-summary-card po-batch-status-filter" data-batch-status-filter="need_certif">
                                    <span class="po-batch-summary-card__label">
                                        Need Certif
                                        <span class="po-batch-summary-card__count" id="po-batch-status-need-certif-count">0</span>
                                    </span>
                                    <span class="po-batch-summary-card__value text-warning" id="po-batch-status-need-certif-value">0</span>
                                </button>
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
                                        <th style="width:190px;">Status</th>
                                        <th style="width:90px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="po-batch-empty-row">
                                        <td colspan="6" class="text-center text-muted">Belum ada row invoice.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="text-muted small">Submit akan mengubah term menjadi BILLED dan memakai tanggal invoice general.</div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-dark" id="po-batch-submit" disabled>Simpan Batch Invoice</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($isReady && $canBatchCertificate): ?>
    <div class="modal fade" id="modal-batch-certificate-termin" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog po-modal-xxl" role="document">
            <div class="modal-content">
                <form method="post" action="<?= base_url('PO_MyRep/batchTerminCertificate') ?>" id="po-batch-certificate-form">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Batch Update Status/Tanggal Sertifikat Termin</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <datalist id="po-cert-po-options">
                            <?php
                            $poCertSeen = [];
                            foreach (($poListRows ?? []) as $poCertRow):
                                $poCertNumber = trim((string) ($poCertRow['po_number'] ?? ''));
                                if ($poCertNumber === '' || isset($poCertSeen[strtoupper($poCertNumber)])) {
                                    continue;
                                }
                                $poCertSeen[strtoupper($poCertNumber)] = true;
                            ?>
                                <option value="<?= htmlspecialchars($poCertNumber, ENT_QUOTES) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>

                        <ul class="nav nav-tabs" id="po-batch-certificate-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="po-cert-manual-tab" data-toggle="pill" href="#po-cert-manual-pane" role="tab" aria-controls="po-cert-manual-pane" aria-selected="true">Input Manual</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="po-cert-paste-tab" data-toggle="pill" href="#po-cert-paste-pane" role="tab" aria-controls="po-cert-paste-pane" aria-selected="false">Paste dari Excel</a>
                            </li>
                        </ul>

                        <div class="tab-content border-left border-right border-bottom p-3 mb-3" id="po-batch-certificate-tab-content">
                            <div class="tab-pane fade show active" id="po-cert-manual-pane" role="tabpanel" aria-labelledby="po-cert-manual-tab">
                                <div class="po-batch-invoice__toolbar">
                                    <div>
                                        <label class="mb-1">Nomor PO</label>
                                        <input type="text" id="po-cert-po-number" class="form-control" list="po-cert-po-options" placeholder="Pilih / ketik nomor PO">
                                    </div>
                                    <div>
                                        <label class="mb-1">Term</label>
                                        <select id="po-cert-term-no" class="form-control">
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1">Status/Tanggal Sertifikat</label>
                                        <input type="text" id="po-cert-value" class="form-control" placeholder="Contoh: FULL UPLOAD / APPROVED 1 / 2026-06-18">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" id="po-cert-add-row">Tambah Row</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="po-cert-paste-pane" role="tabpanel" aria-labelledby="po-cert-paste-tab">
                                <div class="form-group">
                                    <label>Data Sertifikat</label>
                                    <textarea id="po-cert-paste" class="form-control po-batch-invoice__paste" placeholder="PO Number[TAB]Term[TAB]Status/Tanggal Sertifikat&#10;7400127996[TAB]2[TAB]FULL UPLOAD&#10;7400127996[TAB]4[TAB]2026-06-18"></textarea>
                                    <small class="form-text text-muted">Tanggal berarti claim sertifikat. Jika dokumen ASTRI belum full approved, row masuk Need Full Approve.</small>
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                    <button type="button" class="btn btn-outline-secondary" id="po-cert-parse-paste">Cek Status/Tanggal</button>
                                    <button type="button" class="btn btn-outline-danger" id="po-cert-clear-list" disabled>Hapus List</button>
                                </div>
                            </div>
                        </div>

                        <div class="po-batch-summary">
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Tanggal <span class="po-batch-summary-card__count" id="po-cert-summary-date-count">0</span></span>
                                <span class="po-batch-summary-card__value" id="po-cert-summary-date">0</span>
                            </div>
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Status <span class="po-batch-summary-card__count" id="po-cert-summary-status-count">0</span></span>
                                <span class="po-batch-summary-card__value" id="po-cert-summary-status">0</span>
                            </div>
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Valid <span class="po-batch-summary-card__count" id="po-cert-summary-valid-count">0</span></span>
                                <span class="po-batch-summary-card__value" id="po-cert-summary-valid">0</span>
                            </div>
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Invalid <span class="po-batch-summary-card__count" id="po-cert-summary-invalid-count">0</span></span>
                                <span class="po-batch-summary-card__value" id="po-cert-summary-invalid">0</span>
                            </div>
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Need Full Approve <span class="po-batch-summary-card__count" id="po-cert-summary-need-approve-count">0</span></span>
                                <span class="po-batch-summary-card__value text-warning" id="po-cert-summary-need-approve">0</span>
                            </div>
                            <div class="po-batch-summary-card">
                                <span class="po-batch-summary-card__label">Total Row <span class="po-batch-summary-card__count" id="po-cert-summary-total-count">0</span></span>
                                <span class="po-batch-summary-card__value" id="po-cert-summary-total">0</span>
                            </div>
                            <div class="po-batch-status-filters">
                                <button type="button" class="po-batch-summary-card po-cert-status-filter" data-cert-status-filter="success">
                                    <span class="po-batch-summary-card__label">Valid <span class="po-batch-filter-active-badge">AKTIF</span> <span class="po-batch-summary-card__count" id="po-cert-filter-valid-count">0</span></span>
                                    <span class="po-batch-summary-card__value text-success" id="po-cert-filter-valid">0</span>
                                </button>
                                <button type="button" class="po-batch-summary-card po-cert-status-filter" data-cert-status-filter="invalid">
                                    <span class="po-batch-summary-card__label">Invalid <span class="po-batch-filter-active-badge">AKTIF</span> <span class="po-batch-summary-card__count" id="po-cert-filter-invalid-count">0</span></span>
                                    <span class="po-batch-summary-card__value text-danger" id="po-cert-filter-invalid">0</span>
                                </button>
                                <button type="button" class="po-batch-summary-card po-cert-status-filter" data-cert-status-filter="need_full_approve">
                                    <span class="po-batch-summary-card__label">Need Full Approve <span class="po-batch-filter-active-badge">AKTIF</span> <span class="po-batch-summary-card__count" id="po-cert-filter-need-approve-count">0</span></span>
                                    <span class="po-batch-summary-card__value text-warning" id="po-cert-filter-need-approve">0</span>
                                </button>
                            </div>
                        </div>

                        <div class="po-cert-table-tools">
                            <div class="po-cert-table-tools__filter">
                                <label class="mb-1">Filter Regional</label>
                                <select id="po-cert-regional-filter" class="form-control">
                                    <option value="">Semua Regional</option>
                                    <?php
                                    $poCertRegionalOptions = [];
                                    foreach (($poListRows ?? []) as $poCertRegionalRow) {
                                        $poCertRegionalName = strtoupper(trim((string) ($poCertRegionalRow['regional_name'] ?? '')));
                                        if ($poCertRegionalName !== '') {
                                            $poCertRegionalOptions[$poCertRegionalName] = true;
                                        }
                                    }
                                    ksort($poCertRegionalOptions);
                                    foreach (array_keys($poCertRegionalOptions) as $poCertRegionalName):
                                    ?>
                                        <option value="<?= htmlspecialchars($poCertRegionalName, ENT_QUOTES) ?>"><?= htmlspecialchars($poCertRegionalName) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="po-cert-table-tools__actions">
                                <button type="button" class="btn btn-outline-secondary" id="po-cert-copy-text" disabled>Copy Excel/Text</button>
                                <button type="button" class="btn btn-outline-secondary" id="po-cert-copy-image" disabled>Copy Image WA</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="po-batch-certificate-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:60px;">No</th>
                                        <th>Nomor PO</th>
                                        <th>Project</th>
                                        <th>Regional</th>
                                        <th>Cluster</th>
                                        <th style="width:110px;">Term</th>
                                        <th>Status/Tanggal</th>
                                        <th style="width:220px;">Status Cek</th>
                                        <th style="width:90px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="po-cert-empty-row">
                                        <td colspan="9" class="text-center text-muted">Belum ada row status/tanggal sertifikat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <div class="text-muted small">Tanggal valid akan dianggap release date. Status teks hanya status proses sertifikat.</div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" id="po-cert-submit" disabled>Simpan Batch Sertifikat</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

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

<div class="modal fade" id="modal-cert-release-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog po-modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title mb-1" id="cert-release-detail-title">Detail Ready Invoice</h5>
                    <div class="small text-white-50" id="cert-release-detail-subtitle">Sertifikat sudah rilis, invoice belum masuk.</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <section class="po-cert-release-detail-section" id="cert-release-ready-section">
                    <div class="po-cert-release-detail-section__head">
                        <h6 class="po-cert-release-detail-section__title">Ready Invoice</h6>
                        <span class="po-cert-release-detail-section__meta" id="cert-release-ready-meta">0 data</span>
                    </div>
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
                                    <th>Term</th>
                                    <th>Sertifikat</th>
                                    <th>Prev Invoice</th>
                                    <th>Nilai</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody id="cert-release-ready-body">
                                <tr><td colspan="11" class="text-center text-muted">Belum ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="po-cert-release-detail-section is-hidden" id="cert-release-blocked-section">
                    <div class="po-cert-release-detail-section__head">
                        <h6 class="po-cert-release-detail-section__title">Blocked Previous Term</h6>
                        <span class="po-cert-release-detail-section__meta" id="cert-release-blocked-meta">0 data</span>
                    </div>
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
                                    <th>Term</th>
                                    <th>Sertifikat</th>
                                    <th>Blocked By</th>
                                    <th>Nilai</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody id="cert-release-blocked-body">
                                <tr><td colspan="11" class="text-center text-muted">Belum ada data.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var breakdownDetailUrl = "<?= base_url('PO_MyRep/getTerminBreakdownDetail') ?>";
        var certificateDetailUrl = "<?= base_url('PO_MyRep/getCertificateSummaryDetail') ?>";
        var certificateReleasedDetailUrl = "<?= base_url('PO_MyRep/getCertificateReleasedUninvoicedDetail') ?>";
        var certificateSaveUrl = "<?= base_url('PO_MyRep/saveTerminCertificate') ?>";
        var poDetailBaseUrl = "<?= base_url('PO_MyRep/detail/') ?>";
        var selectedCity = "<?= htmlspecialchars((string) $selectedCity, ENT_QUOTES) ?>";
        var selectedStatus = "<?= htmlspecialchars((string) $selectedStatus, ENT_QUOTES) ?>";
        var poMonitorDataUrl = "<?= base_url('PO_MyRep/datatableMonitor') ?>";
        var poListDataUrl = "<?= base_url('PO_MyRep/datatablePo') ?>";
        var poTerminBreakdownDebugRows = <?= json_encode($poTerminBreakdownConsoleRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        if (poTerminBreakdownDebugRows.length) {
            console.warn('PO_MyRep Pembagian Termin rows missing expected keys', poTerminBreakdownDebugRows);
        }
        var poBatchTerminLookup = <?php
            $poBatchTerminLookup = [];
            foreach (($poListRows ?? []) as $poBatchRow) {
                $poBatchNumber = trim((string) ($poBatchRow['po_number'] ?? ''));
                if ($poBatchNumber === '') {
                    continue;
                }

                $poBatchTerminLookup[strtoupper($poBatchNumber)] = [
                    'po_number' => $poBatchNumber,
                    'po_type' => (string) ($poBatchRow['po_type'] ?? ''),
                    'regional_name' => (string) ($poBatchRow['regional_name'] ?? ''),
                    'cluster_name' => (string) ($poBatchRow['cluster_name'] ?? ''),
                    'termin_status' => $poBatchRow['termin_status_per_termin'] ?? [],
                    'termin_invoice_date' => $poBatchRow['termin_invoice_date_per_termin'] ?? [],
                    'termin_certificate' => $poBatchRow['termin_certificate_per_termin'] ?? [],
                    'plan_invoice' => $poBatchRow['plan_invoice_per_termin'] ?? [],
                ];
            }
            echo json_encode($poBatchTerminLookup, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        ?>;
        var poBatchCertificateReleaseLookup = <?php
            $poBatchCertificateReleaseLookup = [];
            foreach (($certificateBatchRows ?? []) as $certificateBatchRow) {
                $certificatePoNumber = strtoupper(trim((string) ($certificateBatchRow['po_number'] ?? '')));
                $certificateTermNo = (int) ($certificateBatchRow['termin_no'] ?? 0);
                if ($certificatePoNumber === '' || $certificateTermNo < 2 || $certificateTermNo > 5) {
                    continue;
                }

                $poBatchCertificateReleaseLookup[$certificatePoNumber . '|' . $certificateTermNo] = [
                    'is_release_ready' => !empty($certificateBatchRow['is_release_ready']),
                    'is_certificate_released' => !empty($certificateBatchRow['is_certificate_released']),
                    'release_note' => (string) ($certificateBatchRow['release_note'] ?? ''),
                    'certificate_status' => (string) ($certificateBatchRow['certificate_status'] ?? ''),
                    'certificate_status_label' => (string) ($certificateBatchRow['certificate_status_label'] ?? ''),
                    'required_docs' => (int) ($certificateBatchRow['required_docs'] ?? 0),
                    'astri_submitted_docs' => (int) ($certificateBatchRow['astri_submitted_docs'] ?? 0),
                    'astri_approved_docs' => (int) ($certificateBatchRow['astri_approved_docs'] ?? 0),
                ];
            }
            echo json_encode($poBatchCertificateReleaseLookup, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        ?>;
        var poAllowedCertificateStatuses = ['REVISI', 'FULL UPLOAD', 'APPROVED 1', 'LOGISTIK', 'PLANNING', 'TEAM LEADER', 'WASPANG', 'PERMIT'];
        var poBatchActiveStatusFilter = '';
        var poCertActiveStatusFilter = '';
        var poCertRegionalFilter = '';

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

        function formatBatchInvoiceInput($input) {
            var raw = String($input.val() || '');
            var digits = raw.replace(/\D/g, '');
            var selectionStart = $input[0] && typeof $input[0].selectionStart === 'number'
                ? $input[0].selectionStart
                : raw.length;
            var digitsBeforeCaret = raw.slice(0, selectionStart).replace(/\D/g, '').length;

            if (digits === '') {
                $input.val('');
                return;
            }

            var formatted = Number(digits).toLocaleString('id-ID', { maximumFractionDigits: 0 });
            $input.val(formatted);

            if (!$input[0] || typeof $input[0].setSelectionRange !== 'function') {
                return;
            }

            var caret = formatted.length;
            var digitCount = 0;
            for (var i = 0; i < formatted.length; i++) {
                if (/\d/.test(formatted.charAt(i))) {
                    digitCount++;
                }
                if (digitCount >= digitsBeforeCaret) {
                    caret = i + 1;
                    break;
                }
            }
            $input[0].setSelectionRange(caret, caret);
        }

        function getBatchPlanInvoiceValue(poNumber, termNo) {
            var poKey = String(poNumber || '').trim().toUpperCase();
            var lookup = poBatchTerminLookup[poKey] || null;
            if (!lookup) {
                return 0;
            }

            var planMap = lookup.plan_invoice || {};
            return parseLocaleNumber(planMap[termNo] || 0);
        }

        function updateBatchEstimateHint() {
            var poNumber = $('#po-batch-po-number').val();
            var termNo = normalizeTermInput($('#po-batch-term-no').val());
            var estimate = getBatchPlanInvoiceValue(poNumber, termNo);
            var formatted = formatBatchValue(estimate);
            var $input = $('#po-batch-invoice-value');
            var currentValue = String($input.val() || '').trim();
            var previousAutoValue = String($input.data('auto-estimate-value') || '');

            if (estimate > 0 && (currentValue === '' || currentValue === previousAutoValue)) {
                $input.val(formatted);
                $input.data('auto-estimate-value', formatted);
            } else if (estimate <= 0 && currentValue === previousAutoValue) {
                $input.val('');
                $input.data('auto-estimate-value', '');
            }

        }

        function updateBatchInvoiceState() {
            var $rows = $('#po-batch-invoice-table tbody tr.po-batch-row');
            var $validRows = $('#po-batch-invoice-table tbody tr.po-batch-row[data-valid="1"]');
            var $needCertifRows = $('#po-batch-invoice-table tbody tr.po-batch-row[data-status-code="need_certif"]');
            var $invalidRows = $('#po-batch-invoice-table tbody tr.po-batch-row[data-valid="0"]').not('[data-status-code="need_certif"]');
            var totalValue = 0;
            var validValue = 0;
            var needCertifValue = 0;
            var invalidValue = 0;
            var visibleIndex = 0;
            var termSummary = {
                1: { count: 0, total: 0 },
                2: { count: 0, total: 0 },
                3: { count: 0, total: 0 },
                4: { count: 0, total: 0 },
                5: { count: 0, total: 0 }
            };

            $validRows.each(function () {
                var invoiceValue = parseLocaleNumber($(this).data('invoice-value'));
                var termNo = Number($(this).data('term-no') || 0);
                totalValue += invoiceValue;
                validValue += invoiceValue;
                if (termSummary[termNo]) {
                    termSummary[termNo].count++;
                    termSummary[termNo].total += invoiceValue;
                }
            });

            $needCertifRows.each(function () {
                needCertifValue += parseLocaleNumber($(this).data('invoice-value'));
            });

            $invalidRows.each(function () {
                invalidValue += parseLocaleNumber($(this).data('invoice-value'));
            });

            $rows.each(function () {
                var $row = $(this);
                var matchesFilter = !poBatchActiveStatusFilter || String($row.data('status-code') || '') === poBatchActiveStatusFilter;
                $row.toggle(matchesFilter);
                if (matchesFilter) {
                    $row.find('.po-batch-row-no').text(++visibleIndex);
                }
            });

            $('#po-batch-invoice-table tbody .po-batch-empty-row')
                .toggle($rows.length === 0 || visibleIndex === 0)
                .find('td')
                .text($rows.length === 0 ? 'Belum ada row invoice.' : 'Tidak ada row sesuai filter.');
            $('#po-batch-submit').prop('disabled', $validRows.length === 0);
            $('#po-batch-clear-list').prop('disabled', $rows.length === 0);
            $('#po-batch-summary-total-count').text($validRows.length);
            $('#po-batch-summary-total-value').text(totalValue > 0 ? totalValue.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '0');
            $('#po-batch-status-valid-count').text($validRows.length);
            $('#po-batch-status-need-certif-count').text($needCertifRows.length);
            $('#po-batch-status-invalid-count').text($invalidRows.length);
            $('#po-batch-status-valid-value').text(validValue > 0 ? validValue.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '0');
            $('#po-batch-status-need-certif-value').text(needCertifValue > 0 ? needCertifValue.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '0');
            $('#po-batch-status-invalid-value').text(invalidValue > 0 ? invalidValue.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '0');
            $('.po-batch-status-filter').toggleClass('is-active', false);
            if (poBatchActiveStatusFilter) {
                $('.po-batch-status-filter[data-batch-status-filter="' + poBatchActiveStatusFilter + '"]').addClass('is-active');
            }
            Object.keys(termSummary).forEach(function (termNo) {
                var summary = termSummary[termNo];
                $('#po-batch-summary-count-' + termNo).text(summary.count);
                $('#po-batch-summary-term-' + termNo).text(summary.total > 0 ? summary.total.toLocaleString('id-ID', { maximumFractionDigits: 0 }) : '0');
            });
        }

        function hasBatchInvoiceDate(value) {
            var text = String(value || '').trim();
            return text !== '' && text !== '0000-00-00' && text !== '0000-00-00 00:00:00';
        }

        function getBatchInvoiceCheck(poNumber, termNo, invoiceValue) {
            var poKey = String(poNumber || '').trim().toUpperCase();
            var lookup = poBatchTerminLookup[poKey] || null;

            if (!poKey || !termNo || !invoiceValue) {
                return {
                    valid: false,
                    label: 'Invalid',
                    statusCode: 'invalid',
                    message: 'Nomor PO, term, dan nilai invoice wajib diisi.'
                };
            }

            if (!lookup) {
                return {
                    valid: false,
                    label: 'Invalid',
                    statusCode: 'invalid',
                    message: 'PO tidak ditemukan.'
                };
            }

            var statusMap = lookup.termin_status || {};
            var invoiceDateMap = lookup.termin_invoice_date || {};
            var certificateMap = lookup.termin_certificate || {};
            var status = String(statusMap[termNo] || 'NOT READY').toUpperCase();
            var invoiceDate = invoiceDateMap[termNo] || '';
            var certificateValue = certificateMap[termNo] || '';

            if (status === 'BILLED' || status === 'PAID' || hasBatchInvoiceDate(invoiceDate)) {
                return {
                    valid: false,
                    label: 'Invalid',
                    statusCode: 'invalid',
                    message: 'Termin sudah ditagih' + (status ? ' (' + status + ')' : '') + '.'
                };
            }

            if (Number(termNo) >= 2 && Number(termNo) <= 5 && normalizeCertificateDateInput(certificateValue) === '') {
                return {
                    valid: false,
                    label: 'Need Certif',
                    statusCode: 'need_certif',
                    badgeClass: 'badge-warning',
                    rowClass: 'table-warning',
                    message: 'Sertifikat belum release.'
                };
            }

            return {
                valid: true,
                label: 'Sukses',
                statusCode: 'success',
                message: 'PO ditemukan dan termin belum ditagih.',
                poNumber: lookup.po_number || poNumber
            };
        }

        function addBatchInvoiceRow(poNumber, termNo, invoiceValue) {
            poNumber = String(poNumber || '').trim();
            termNo = normalizeTermInput(termNo);
            if (String(invoiceValue || '').trim() === '') {
                invoiceValue = getBatchPlanInvoiceValue(poNumber, termNo);
            }
            var invoiceValueRaw = parseLocaleNumber(invoiceValue);
            invoiceValue = formatBatchValue(invoiceValueRaw);

            var key = poNumber.toUpperCase() + '|' + termNo;
            var exists = false;
            $('#po-batch-invoice-table tbody tr.po-batch-row').each(function () {
                if ($(this).data('key') === key) {
                    exists = true;
                    return false;
                }
            });
            if (exists) {
                var duplicateHtml = '<tr class="po-batch-row table-danger" data-valid="0" data-status-code="invalid" data-key="' + escapeHtml(key) + '">' +
                    '<td class="text-center po-batch-row-no"></td>' +
                    '<td>' + escapeHtml(poNumber || '-') + '</td>' +
                    '<td class="text-center">' + escapeHtml(termNo || '-') + '</td>' +
                    '<td class="text-right">' + escapeHtml(invoiceValue || '-') + '</td>' +
                    '<td><span class="badge badge-danger">Invalid</span><div class="small text-muted">Duplikat dalam batch.</div></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger po-batch-remove-row">Hapus</button></td>' +
                '</tr>';
                $('#po-batch-invoice-table tbody').append(duplicateHtml);
                updateBatchInvoiceState();
                return false;
            }

            var check = getBatchInvoiceCheck(poNumber, termNo, invoiceValue);
            var rowClass = check.rowClass || (check.valid ? 'table-success' : 'table-danger');
            var badgeClass = check.badgeClass || (check.valid ? 'badge-success' : 'badge-danger');
            var statusCode = check.statusCode || (check.valid ? 'success' : 'invalid');
            var effectivePoNumber = check.poNumber || poNumber;
            var hiddenInputs = check.valid
                ? '<input type="hidden" name="po_number[]" value="' + escapeHtml(effectivePoNumber) + '">' +
                  '<input type="hidden" name="term_no[]" value="' + escapeHtml(termNo) + '">' +
                  '<input type="hidden" name="invoice_value[]" value="' + escapeHtml(invoiceValue) + '">'
                : '';

            var html = '<tr class="po-batch-row ' + rowClass + '" data-valid="' + (check.valid ? '1' : '0') + '" data-status-code="' + escapeHtml(statusCode) + '" data-key="' + escapeHtml(key) + '" data-term-no="' + escapeHtml(termNo) + '" data-invoice-value="' + escapeHtml(invoiceValueRaw) + '">' +
                '<td class="text-center po-batch-row-no"></td>' +
                '<td>' + escapeHtml(effectivePoNumber || '-') + hiddenInputs + '</td>' +
                '<td class="text-center">' + escapeHtml(termNo || '-') + '</td>' +
                '<td class="text-right">' + escapeHtml(invoiceValue || '-') + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + escapeHtml(check.label) + '</span><div class="small text-muted">' + escapeHtml(check.message) + '</div></td>' +
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

        $('#po-batch-po-number, #po-batch-term-no').on('input change', function () {
            updateBatchEstimateHint();
        });

        $('#po-batch-invoice-value').on('input', function () {
            formatBatchInvoiceInput($(this));
            if (String($(this).val() || '').trim() !== String($(this).data('auto-estimate-value') || '')) {
                $(this).data('auto-estimate-value', '');
            }
        });

        $('#po-batch-parse-paste').on('click', function () {
            var lines = String($('#po-batch-paste').val() || '').split(/\r?\n/);
            var checkedCount = 0;
            lines.forEach(function (line) {
                line = String(line || '').trim();
                if (!line) {
                    return;
                }

                var columns = line.split('\t');
                if (columns.length < 2) {
                    columns = line.split(';');
                }
                if (columns.length < 2) {
                    columns = line.split(',');
                }
                if (columns.length < 2) {
                    columns = line.split(/\s{2,}/);
                }
                if (columns.length < 2) {
                    columns = line.split(/\s+/);
                }
                if (columns.length < 2) {
                    return;
                }

                addBatchInvoiceRow(columns[0], columns[1], columns.length >= 3 ? columns.slice(2).join(' ') : '');
                checkedCount++;
            });
            if (checkedCount > 0) {
                $('#po-batch-paste').val('');
            }
        });

        $(document).on('click', '.po-batch-remove-row', function () {
            $(this).closest('tr').remove();
            updateBatchInvoiceState();
        });

        $('#po-batch-clear-list').on('click', function () {
            $('#po-batch-invoice-table tbody tr.po-batch-row').remove();
            poBatchActiveStatusFilter = '';
            updateBatchInvoiceState();
        });

        $('.po-batch-status-filter').on('click', function () {
            var filter = String($(this).data('batch-status-filter') || '');
            poBatchActiveStatusFilter = poBatchActiveStatusFilter === filter ? '' : filter;
            updateBatchInvoiceState();
        });

        $('#po-batch-invoice-form').on('submit', function (e) {
            if ($('#po-batch-invoice-table tbody tr.po-batch-row[data-valid="1"]').length === 0) {
                e.preventDefault();
                alert('Belum ada row invoice yang valid.');
            }
        });

        function normalizeCertificateStatus(value) {
            return String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
        }

        function normalizeCertificateDateInput(value) {
            var text = String(value || '').trim();
            var match;
            var year;
            var month;
            var day;
            var date;

            match = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (match) {
                year = Number(match[1]);
                month = Number(match[2]);
                day = Number(match[3]);
            } else {
                match = text.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$/);
                if (!match) {
                    return '';
                }
                var first = Number(match[1]);
                var second = Number(match[2]);
                year = Number(match[3]);
                if (year < 100) {
                    year += 2000;
                }
                if (first > 12 && second <= 12) {
                    day = first;
                    month = second;
                } else {
                    month = first;
                    day = second;
                }
            }

            date = new Date(year, month - 1, day);
            if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
                return '';
            }

            return String(year).padStart(4, '0') + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        }

        function classifyCertificateValue(value) {
            var normalizedDate = normalizeCertificateDateInput(value);
            var normalizedStatus = normalizeCertificateStatus(value);

            if (normalizedDate) {
                return {
                    valid: true,
                    type: 'Tanggal',
                    value: normalizedDate,
                    message: 'Tanggal valid dan syarat release terpenuhi.'
                };
            }

            if (String(value || '').trim() !== '') {
                return {
                    valid: true,
                    type: 'Status',
                    value: normalizedStatus || String(value || '').trim(),
                    message: 'Status text akan disimpan. Tanggal release tetap wajib memenuhi syarat.'
                };
            }

            return {
                valid: false,
                type: 'Invalid',
                value: String(value || '').trim(),
                message: 'Status/tanggal wajib diisi.'
            };
        }

        function updateCertificateBatchState() {
            var $rows = $('#po-batch-certificate-table tbody tr.po-cert-row');
            var $validRows = $('#po-batch-certificate-table tbody tr.po-cert-row[data-status-code="success"]');
            var $needApproveRows = $('#po-batch-certificate-table tbody tr.po-cert-row[data-status-code="need_full_approve"]');
            var $invalidRows = $('#po-batch-certificate-table tbody tr.po-cert-row[data-status-code="invalid"]');
            var dateCount = $('#po-batch-certificate-table tbody tr.po-cert-row[data-valid="1"][data-cert-type="Tanggal"]').length;
            var statusCount = $('#po-batch-certificate-table tbody tr.po-cert-row[data-valid="1"][data-cert-type="Status"]').length;
            var visibleIndex = 0;

            $rows.each(function () {
                var $row = $(this);
                var rowRegional = String($row.data('regional') || '').toUpperCase();
                var matchesStatusFilter = !poCertActiveStatusFilter || String($row.data('status-code') || '') === poCertActiveStatusFilter;
                var matchesRegionalFilter = !poCertRegionalFilter || rowRegional === poCertRegionalFilter;
                var matchesFilter = matchesStatusFilter && matchesRegionalFilter;
                $row.toggle(matchesFilter);
                if (matchesFilter) {
                    $row.find('.po-cert-row-no').text(++visibleIndex);
                }
            });

            $('#po-batch-certificate-table tbody .po-cert-empty-row')
                .toggle($rows.length === 0 || visibleIndex === 0)
                .find('td')
                .text($rows.length === 0 ? 'Belum ada row status/tanggal sertifikat.' : 'Tidak ada row sesuai filter.');
            $('#po-cert-submit').prop('disabled', $validRows.length === 0);
            $('#po-cert-clear-list').prop('disabled', $rows.length === 0);
            $('#po-cert-copy-text, #po-cert-copy-image').prop('disabled', visibleIndex === 0);
            $('#po-cert-summary-date-count, #po-cert-summary-date').text(dateCount);
            $('#po-cert-summary-status-count, #po-cert-summary-status').text(statusCount);
            $('#po-cert-summary-valid-count, #po-cert-summary-valid').text($validRows.length);
            $('#po-cert-summary-invalid-count, #po-cert-summary-invalid').text($invalidRows.length);
            $('#po-cert-summary-need-approve-count, #po-cert-summary-need-approve').text($needApproveRows.length);
            $('#po-cert-summary-total-count, #po-cert-summary-total').text($rows.length);
            $('#po-cert-filter-valid-count, #po-cert-filter-valid').text($validRows.length);
            $('#po-cert-filter-invalid-count, #po-cert-filter-invalid').text($invalidRows.length);
            $('#po-cert-filter-need-approve-count, #po-cert-filter-need-approve').text($needApproveRows.length);
            $('.po-cert-status-filter').toggleClass('is-active', false);
            if (poCertActiveStatusFilter) {
                $('.po-cert-status-filter[data-cert-status-filter="' + poCertActiveStatusFilter + '"]').addClass('is-active');
            }
        }

        function addCertificateBatchRow(poNumber, termNo, certificateValue) {
            poNumber = String(poNumber || '').trim();
            termNo = normalizeTermInput(termNo);
            certificateValue = String(certificateValue || '').trim();

            var key = poNumber.toUpperCase() + '|' + termNo;
            var exists = false;
            var $existingRow = $();
            $('#po-batch-certificate-table tbody tr.po-cert-row').each(function () {
                if ($(this).data('key') === key) {
                    exists = true;
                    $existingRow = $(this);
                    return false;
                }
            });

            var lookup = poBatchTerminLookup[String(poNumber || '').trim().toUpperCase()] || null;
            var certClass = classifyCertificateValue(certificateValue);
            var valid = true;
            var message = certClass.message;
            var type = certClass.type;
            var saveValue = certClass.value;
            var statusCode = 'success';
            var statusLabel = 'Sukses';

            if (!poNumber || !termNo || termNo < 2 || termNo > 5 || !certificateValue) {
                valid = false;
                statusCode = 'invalid';
                statusLabel = 'Invalid';
                message = 'Nomor PO, term 2-5, dan status/tanggal wajib diisi.';
                type = 'Invalid';
            } else if (exists) {
                poCertActiveStatusFilter = '';
                updateCertificateBatchState();
                $existingRow.addClass('table-info');
                setTimeout(function () {
                    $existingRow.removeClass('table-info');
                }, 900);
                alert('PO ' + poNumber + ' term ' + termNo + ' sudah ada di table batch.');
                return false;
            } else if (!lookup) {
                valid = false;
                statusCode = 'invalid';
                statusLabel = 'Invalid';
                message = 'PO tidak ditemukan.';
                type = 'Invalid';
            } else if (!certClass.valid) {
                valid = false;
                statusCode = 'invalid';
                statusLabel = 'Invalid';
            } else if (certClass.type === 'Tanggal') {
                var releaseLookup = poBatchCertificateReleaseLookup[String(poNumber || '').trim().toUpperCase() + '|' + termNo] || null;
                if (!releaseLookup || (!releaseLookup.is_release_ready && !releaseLookup.is_certificate_released)) {
                    valid = false;
                    statusCode = 'need_full_approve';
                    statusLabel = 'Need Full Approve';
                    message = releaseLookup && releaseLookup.release_note
                        ? releaseLookup.release_note
                        : 'Dokumen ASTRI belum full approved.';
                    type = 'Tanggal';
                }
            }

            var releaseInfo = poBatchCertificateReleaseLookup[String(poNumber || '').trim().toUpperCase() + '|' + termNo] || null;
            var copyStatus = statusLabel;
            if (statusCode === 'need_full_approve') {
                copyStatus = 'need approve ' + Number((releaseInfo && releaseInfo.astri_approved_docs) || 0) + '/' + Number((releaseInfo && releaseInfo.required_docs) || 0);
            }

            var rowClass = statusCode === 'success' ? 'table-success' : (statusCode === 'need_full_approve' ? 'table-warning' : 'table-danger');
            var badgeClass = statusCode === 'success' ? 'badge-success' : (statusCode === 'need_full_approve' ? 'badge-warning' : 'badge-danger');
            var hiddenInputs = valid
                ? '<input type="hidden" name="certificate_po_number[]" value="' + escapeHtml(lookup.po_number || poNumber) + '">' +
                  '<input type="hidden" name="certificate_term_no[]" value="' + escapeHtml(termNo) + '">' +
                  '<input type="hidden" name="certificate_value[]" value="' + escapeHtml(saveValue) + '">'
                : '';
            var regionalName = (lookup && lookup.regional_name) || '';
            var projectType = String((lookup && lookup.po_type) || '').toUpperCase() === 'SUBFEEDER' ? 'SUBFEEDER' : 'CLUSTER';
            var html = '<tr class="po-cert-row ' + rowClass + '" data-valid="' + (valid ? '1' : '0') + '" data-status-code="' + escapeHtml(statusCode) + '" data-copy-status="' + escapeHtml(copyStatus) + '" data-key="' + escapeHtml(key) + '" data-cert-type="' + escapeHtml(type) + '" data-regional="' + escapeHtml(String(regionalName || '').toUpperCase()) + '">' +
                '<td class="text-center po-cert-row-no"></td>' +
                '<td>' + escapeHtml((lookup && lookup.po_number) || poNumber || '-') + hiddenInputs + '</td>' +
                '<td>' + escapeHtml(projectType) + '</td>' +
                '<td>' + escapeHtml(regionalName || '-') + '</td>' +
                '<td>' + escapeHtml((lookup && lookup.cluster_name) || '-') + '</td>' +
                '<td class="text-center">' + escapeHtml(termNo || '-') + '</td>' +
                '<td>' + escapeHtml(certificateValue || '-') + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + escapeHtml(statusLabel) + '</span><div class="small text-muted">' + escapeHtml(message) + '</div></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger po-cert-remove-row">Hapus</button></td>' +
            '</tr>';

            $('#po-batch-certificate-table tbody').append(html);
            updateCertificateBatchState();
            return valid;
        }

        $('#po-cert-add-row').on('click', function () {
            var added = addCertificateBatchRow(
                $('#po-cert-po-number').val(),
                $('#po-cert-term-no').val(),
                $('#po-cert-value').val()
            );
            if (added) {
                $('#po-cert-po-number').val('').focus();
                $('#po-cert-value').val('');
            }
        });

        $('#po-cert-parse-paste').on('click', function () {
            var lines = String($('#po-cert-paste').val() || '').split(/\r?\n/);
            var checkedCount = 0;
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

                addCertificateBatchRow(columns[0], columns[1], columns.slice(2).join(' '));
                checkedCount++;
            });
            if (checkedCount > 0) {
                $('#po-cert-paste').val('');
            }
        });

        $(document).on('click', '.po-cert-remove-row', function () {
            $(this).closest('tr').remove();
            updateCertificateBatchState();
        });

        $('#po-cert-clear-list').on('click', function () {
            $('#po-batch-certificate-table tbody tr.po-cert-row').remove();
            poCertActiveStatusFilter = '';
            updateCertificateBatchState();
        });

        $('.po-cert-status-filter').on('click', function () {
            var filter = String($(this).data('cert-status-filter') || '');
            poCertActiveStatusFilter = poCertActiveStatusFilter === filter ? '' : filter;
            updateCertificateBatchState();
        });

        $('#po-cert-regional-filter').on('change', function () {
            poCertRegionalFilter = String($(this).val() || '').toUpperCase();
            updateCertificateBatchState();
        });

        if ($.fn.select2) {
            $('#po-cert-regional-filter').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modal-batch-certificate-termin'),
                placeholder: 'Semua Regional'
            });
        }

        function getVisibleCertificateTableData(copyMode) {
            var isImageMode = copyMode === 'image';
            var headers = isImageMode
                ? ['No', 'Nomor PO', 'Project', 'Regional', 'Cluster', 'Term', 'Tanggal', 'Status Cek']
                : ['No', 'Nomor PO', 'Project', 'Regional', 'Cluster', 'Term', 'Status/Tanggal', 'Status Cek'];
            var rows = [];
            $('#po-batch-certificate-table tbody tr.po-cert-row:visible').each(function () {
                var cells = [];
                var $row = $(this);
                $row.children('td').slice(0, 8).each(function (index) {
                    if (isImageMode && index === 7) {
                        cells.push(String($row.data('copy-status') || '').replace(/\s+/g, ' ').trim());
                        return;
                    }
                    cells.push(String($(this).text() || '').replace(/\s+/g, ' ').trim());
                });
                rows.push(cells);
            });

            return {
                headers: headers,
                rows: rows
            };
        }

        function copyTextToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            }

            var $textarea = $('<textarea>')
                .val(text)
                .css({ position: 'fixed', top: '-9999px', left: '-9999px' })
                .appendTo('body');
            $textarea[0].focus();
            $textarea[0].select();
            var successful = document.execCommand('copy');
            $textarea.remove();
            return successful ? Promise.resolve() : Promise.reject(new Error('Clipboard tidak tersedia.'));
        }

        function buildCertificateTableText(tableData) {
            var lines = [tableData.headers.join('\t')];
            tableData.rows.forEach(function (row) {
                lines.push(row.join('\t'));
            });
            return lines.join('\n');
        }

        function drawCertificateTableImage(tableData) {
            var columnWidths = [46, 125, 90, 105, 230, 54, 135, 145];
            var rowHeight = 34;
            var headerHeight = 38;
            var padding = 18;
            var titleHeight = 42;
            var width = columnWidths.reduce(function (sum, current) { return sum + current; }, 0) + (padding * 2);
            var height = padding + titleHeight + headerHeight + (Math.max(tableData.rows.length, 1) * rowHeight) + padding;
            var canvas = document.createElement('canvas');
            var scale = Math.max(1, window.devicePixelRatio || 1);
            canvas.width = width * scale;
            canvas.height = height * scale;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';

            var ctx = canvas.getContext('2d');
            ctx.scale(scale, scale);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#0f172a';
            ctx.font = '700 18px Arial, sans-serif';
            ctx.fillText('Batch Update Status/Tanggal Sertifikat Termin', padding, padding + 22);
            ctx.font = '12px Arial, sans-serif';
            ctx.fillStyle = '#64748b';
            var subtitle = poCertRegionalFilter ? ('Regional: ' + poCertRegionalFilter) : 'Regional: Semua';
            ctx.fillText(subtitle + ' • Row: ' + tableData.rows.length, padding, padding + 40);

            var y = padding + titleHeight;
            var x = padding;
            ctx.font = '700 12px Arial, sans-serif';
            tableData.headers.forEach(function (header, index) {
                ctx.fillStyle = '#e2e8f0';
                ctx.fillRect(x, y, columnWidths[index], headerHeight);
                ctx.strokeStyle = '#cbd5e1';
                ctx.strokeRect(x, y, columnWidths[index], headerHeight);
                ctx.fillStyle = '#0f172a';
                ctx.fillText(header, x + 7, y + 24);
                x += columnWidths[index];
            });

            y += headerHeight;
            ctx.font = '12px Arial, sans-serif';
            tableData.rows.forEach(function (row, rowIndex) {
                x = padding;
                row.forEach(function (cell, index) {
                    ctx.fillStyle = rowIndex % 2 === 0 ? '#ffffff' : '#f8fafc';
                    ctx.fillRect(x, y, columnWidths[index], rowHeight);
                    ctx.strokeStyle = '#e2e8f0';
                    ctx.strokeRect(x, y, columnWidths[index], rowHeight);
                    ctx.fillStyle = '#0f172a';
                    var text = String(cell || '-');
                    var maxWidth = columnWidths[index] - 14;
                    while (ctx.measureText(text).width > maxWidth && text.length > 3) {
                        text = text.slice(0, -4) + '...';
                    }
                    ctx.fillText(text, x + 7, y + 22);
                    x += columnWidths[index];
                });
                y += rowHeight;
            });

            return canvas;
        }

        $('#po-cert-copy-text').on('click', function () {
            var tableData = getVisibleCertificateTableData('text');
            if (!tableData.rows.length) {
                alert('Tidak ada row yang bisa dicopy.');
                return;
            }

            copyTextToClipboard(buildCertificateTableText(tableData))
                .then(function () {
                    alert('Table berhasil dicopy. Bisa paste ke Excel atau WA sebagai text.');
                })
                .catch(function () {
                    alert('Gagal copy table.');
                });
        });

        $('#po-cert-copy-image').on('click', function () {
            var tableData = getVisibleCertificateTableData('image');
            if (!tableData.rows.length) {
                alert('Tidak ada row yang bisa dicopy.');
                return;
            }

            var canvas = drawCertificateTableImage(tableData);
            if (!navigator.clipboard || !navigator.clipboard.write || typeof ClipboardItem === 'undefined') {
                copyTextToClipboard(buildCertificateTableText(tableData)).then(function () {
                    alert('Browser belum support copy image. Table dicopy sebagai text.');
                });
                return;
            }

            canvas.toBlob(function (blob) {
                if (!blob) {
                    alert('Gagal membuat image table.');
                    return;
                }

                navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })])
                    .then(function () {
                        alert('Image table berhasil dicopy. Bisa langsung paste ke WhatsApp Web.');
                    })
                    .catch(function () {
                        copyTextToClipboard(buildCertificateTableText(tableData)).then(function () {
                            alert('Copy image ditolak browser. Table dicopy sebagai text.');
                        });
                    });
            }, 'image/png');
        });

        $('#po-batch-certificate-form').on('submit', function (e) {
            if ($('#po-batch-certificate-table tbody tr.po-cert-row[data-valid="1"]').length === 0) {
                e.preventDefault();
                alert('Belum ada row status/tanggal sertifikat yang valid.');
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
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [[1, 'asc']],
                ajax: {
                    url: poMonitorDataUrl,
                    type: 'POST',
                    data: function (payload) {
                        payload.city = selectedCity;
                        payload.status = selectedStatus;
                    }
                },
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
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                autoWidth: false,
                pageLength: 10,
                order: [[3, 'desc']],
                ajax: {
                    url: poListDataUrl,
                    type: 'POST',
                    data: function (payload) {
                        payload.city = selectedCity;
                        payload.status = selectedStatus;
                        payload.po_type_filter = String($('#po-list-filter-type').val() || '');
                        payload.stage_filter = String($('#po-list-filter-status').val() || '');
                    }
                },
                footerCallback: function () {
                    var api = this.api();
                    var totalNilaiPo = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                        return parseLocaleNumber(a) + parseLocaleNumber(b);
                    }, 0);
                    var totalDone1 = api.column(10, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding1 = api.column(11, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone2 = api.column(12, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding2 = api.column(13, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone3 = api.column(14, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding3 = api.column(15, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone4 = api.column(16, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding4 = api.column(17, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalDone5 = api.column(18, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding5 = api.column(19, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalInvoiced = api.column(20, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    var totalOutstanding = api.column(21, { page: 'current' }).data().reduce(function (a, b) { return parseLocaleNumber(a) + parseLocaleNumber(b); }, 0);
                    $('.po-list-footer-nilai-po').text(totalNilaiPo.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-1').text(totalDone1 === 0 ? '-' : totalDone1.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-1').text(totalOutstanding1 === 0 ? '-' : totalOutstanding1.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-2').text(totalDone2 === 0 ? '-' : totalDone2.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-2').text(totalOutstanding2 === 0 ? '-' : totalOutstanding2.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-3').text(totalDone3 === 0 ? '-' : totalDone3.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-3').text(totalOutstanding3 === 0 ? '-' : totalOutstanding3.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-4').text(totalDone4 === 0 ? '-' : totalDone4.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-4').text(totalOutstanding4 === 0 ? '-' : totalOutstanding4.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-done-5').text(totalDone5 === 0 ? '-' : totalDone5.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $('.po-list-footer-outstanding-5').text(totalOutstanding5 === 0 ? '-' : totalOutstanding5.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
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
                    tablePoList.ajax.reload();
                });

                $('#po-list-filter-status').on('change', function () {
                    tablePoList.ajax.reload();
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

            function applyCertificateReleaseSummaryMode(mode) {
                mode = mode === 'all' ? 'all' : 'ready';
                $('.po-cert-release-toggle__btn').removeClass('is-active');
                $('.po-cert-release-toggle__btn[data-cert-summary-mode="' + mode + '"]').addClass('is-active');

                $('.po-cert-release-card').each(function () {
                    var $card = $(this);
                    var $count = $card.find('.po-cert-release-card__count');
                    var $sum = $card.find('.po-cert-release-card__sum');
                    var count = Number($count.data(mode + '-count') || 0);
                    var value = Number($sum.data(mode + '-value') || 0);
                    $count.text(count.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $sum.text('Nilai ' + value.toLocaleString('id-ID', { maximumFractionDigits: 0 }));
                    $card.find('.po-cert-release-card__blocked').toggleClass('is-hidden', mode !== 'all');
                });

                var $copy = $('#po-cert-release-copy');
                $copy.text(mode === 'all' ? String($copy.data('all-copy') || '') : String($copy.data('ready-copy') || ''));
                $('#po-cert-release-blocked-note').toggleClass('is-hidden', mode !== 'all');
            }

            $('.po-cert-release-toggle__btn').on('click', function () {
                applyCertificateReleaseSummaryMode(String($(this).data('cert-summary-mode') || 'ready'));
            });

            applyCertificateReleaseSummaryMode('ready');

            $('.po-table-mode-toggle__btn').on('click', function () {
                var mode = String($(this).data('termin-table-mode') || 'outstanding');
                $('.po-table-mode-toggle__btn').removeClass('is-active');
                $(this).addClass('is-active');
                $('.po-termin-table-pane').addClass('is-hidden');
                $('.po-termin-table-pane[data-termin-table-pane="' + mode + '"]').removeClass('is-hidden');
            });

            function renderCertificateReleaseDetailRows(rows, type) {
                rows = rows || [];
                if (!rows.length) {
                    return '<tr><td colspan="11" class="text-center text-muted">Tidak ada data.</td></tr>';
                }

                return rows.map(function (row, idx) {
                    var previousInfo = type === 'blocked'
                        ? escapeHtml(row.block_reason || '-')
                        : 'Term ' + escapeHtml(row.previous_term_no || '-') + '<div class="small text-muted">' + escapeHtml(row.previous_invoice_date || '-') + '</div>';
                    return '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td><strong>' + escapeHtml(row.cluster_name || '-') + '</strong></td>' +
                        '<td>' + escapeHtml(row.city_name || '-') + '</td>' +
                        '<td>' + escapeHtml(row.regional_name || '-') + '</td>' +
                        '<td><strong>' + escapeHtml(row.po_number || '-') + '</strong><div class="small text-muted">' + escapeHtml(row.po_type || '-') + ' / ' + escapeHtml(row.po_category || '-') + '</div></td>' +
                        '<td>' + escapeHtml(row.po_date || '-') + '</td>' +
                        '<td class="text-center">' + escapeHtml(row.termin_no || '-') + '</td>' +
                        '<td>' + escapeHtml(row.certificate_date || '-') + '</td>' +
                        '<td>' + previousInfo + '</td>' +
                        '<td class="text-right">' + Number(row.plan_invoice_value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + '</td>' +
                        '<td><a href="' + poDetailBaseUrl + Number(row.id_myrep_cluster || 0) + '" class="btn btn-sm btn-outline-primary">Detail</a></td>' +
                    '</tr>';
                }).join('');
            }

            $(document).on('click', '.js-open-cert-release-detail', function () {
                var $btn = $(this);
                var activeMode = $('.po-cert-release-toggle__btn.is-active').data('cert-summary-mode') === 'all' ? 'all' : 'ready';
                var label = String($btn.data('label') || 'Total');
                var termNo = Number($btn.data('term-no') || 0);

                $('#cert-release-detail-title').text('Detail ' + label);
                $('#cert-release-detail-subtitle').text(activeMode === 'all'
                    ? 'All Released: detail atas adalah Ready Invoice, detail bawah adalah Blocked Previous Term.'
                    : 'Ready Invoice: sertifikat sudah rilis, invoice term ini kosong, dan previous term sudah invoice.');
                $('#cert-release-ready-meta').text('Memuat...');
                $('#cert-release-blocked-meta').text('Memuat...');
                $('#cert-release-ready-body').html('<tr><td colspan="11" class="text-center text-muted">Loading...</td></tr>');
                $('#cert-release-blocked-body').html('<tr><td colspan="11" class="text-center text-muted">Loading...</td></tr>');
                $('#cert-release-blocked-section').toggleClass('is-hidden', activeMode !== 'all');
                $('#modal-cert-release-detail').modal('show');

                $.ajax({
                    url: certificateReleasedDetailUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        city: selectedCity,
                        status: selectedStatus,
                        term_no: termNo
                    },
                    success: function (response) {
                        if (!response || !response.status) {
                            $('#cert-release-ready-meta').text('0 data');
                            $('#cert-release-ready-body').html('<tr><td colspan="11" class="text-center text-danger">Gagal memuat data.</td></tr>');
                            return;
                        }

                        var readyRows = response.ready_rows || [];
                        var blockedRows = response.blocked_rows || [];
                        $('#cert-release-detail-title').text(response.title || ('Detail ' + label));
                        $('#cert-release-ready-meta').text(readyRows.length.toLocaleString('id-ID') + ' data');
                        $('#cert-release-ready-body').html(renderCertificateReleaseDetailRows(readyRows, 'ready'));

                        if (activeMode === 'all') {
                            $('#cert-release-blocked-section').removeClass('is-hidden');
                            $('#cert-release-blocked-meta').text(blockedRows.length.toLocaleString('id-ID') + ' data');
                            $('#cert-release-blocked-body').html(renderCertificateReleaseDetailRows(blockedRows, 'blocked'));
                        } else {
                            $('#cert-release-blocked-section').addClass('is-hidden');
                        }
                    },
                    error: function () {
                        $('#cert-release-ready-meta').text('0 data');
                        $('#cert-release-ready-body').html('<tr><td colspan="11" class="text-center text-danger">Terjadi kesalahan saat memuat detail.</td></tr>');
                        $('#cert-release-blocked-section').addClass('is-hidden');
                    }
                });
            });
        });
    })();
</script>
