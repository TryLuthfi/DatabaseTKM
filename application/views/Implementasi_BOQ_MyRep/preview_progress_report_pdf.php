<style>
    .progress-report-wrap {
        background: #eef2f7;
        min-height: calc(100vh - 57px);
        padding: 1.25rem 0 2rem;
    }

    .progress-report-shell {
        width: 100%;
        max-width: none;
        margin: 0 auto;
        padding: 0 8px;
    }

    .progress-report-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .progress-report-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
    }

    .progress-report-subtitle {
        font-size: .82rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .progress-report-page {
        background: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        padding: 16px;
        width: 100%;
        box-sizing: border-box;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .progress-report-letterhead {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr) 180px;
        align-items: center;
        gap: 1rem;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #111827;
    }

    .progress-report-letterhead__logo {
        height: 42px;
        display: flex;
        align-items: center;
    }

    .progress-report-letterhead__logo--right {
        justify-content: flex-end;
    }

    .progress-report-letterhead__logo img {
        max-width: 170px;
        max-height: 42px;
        object-fit: contain;
    }

    .progress-report-letterhead__brand {
        text-align: center;
        font-size: 1.45rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: .02em;
    }

    .progress-report-meta,
    .progress-report-summary,
    .progress-report-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .progress-report-page .table-responsive {
        overflow-x: visible;
    }

    .progress-report-meta td,
    .progress-report-summary td,
    .progress-report-table th,
    .progress-report-table td {
        border: 1px solid #111827;
        padding: 5px 7px;
        vertical-align: middle;
    }

    .progress-report-meta td,
    .progress-report-summary td {
        font-size: .86rem;
    }

    .progress-report-heading {
        text-align: center;
        margin: 14px 0 12px;
    }

    .progress-report-heading__title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
    }

    .progress-report-heading__note {
        font-size: .85rem;
        color: #475569;
    }

    .progress-report-table th {
        background: #1f2937;
        color: #fff;
        text-align: center;
        white-space: normal;
        font-size: .66rem;
        line-height: 1.25;
        word-break: break-word;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .progress-report-table td {
        text-align: center;
        font-size: .68rem;
        line-height: 1.25;
        word-break: break-word;
    }

    .progress-report-table__remark {
        text-align: left !important;
        min-width: 0;
    }

    .progress-report-table tfoot td {
        background: #f3f4f6;
        font-weight: 800;
    }

    .progress-report-section-title {
        margin: 14px 0 8px;
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
    }

    .progress-report-daily-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .progress-report-daily-table th,
    .progress-report-daily-table td {
        border: 1px solid #111827;
        padding: 5px 7px;
        vertical-align: middle;
        font-size: .68rem;
        line-height: 1.25;
        word-break: break-word;
    }

    .progress-report-daily-table th {
        background: #1f2937;
        color: #fff;
        text-align: center;
        font-weight: 800;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .progress-report-daily-table td {
        text-align: center;
    }

    .progress-report-daily-table__left {
        text-align: left !important;
    }

    .progress-report-empty {
        border: 1px dashed #94a3b8;
        color: #64748b;
        padding: 1rem;
        text-align: center;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        .main-header,
        .main-sidebar,
        .content-header,
        .main-footer,
        .progress-report-toolbar {
            display: none !important;
        }

        .content-wrapper,
        .progress-report-wrap {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            min-height: auto !important;
        }

        .progress-report-shell {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .progress-report-page {
            box-shadow: none;
            border: 0;
            padding: 8px;
            width: 100% !important;
        }

        .progress-report-table th,
        .progress-report-table td,
        .progress-report-daily-table th,
        .progress-report-daily-table td {
            font-size: 6.5pt;
            padding: 3px 4px;
        }

        .progress-report-table th,
        .progress-report-daily-table th {
            background: #1f2937 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<?php
$report = (array) ($report ?? []);
$cluster = (array) ($cluster ?? ($report['cluster'] ?? []));
$scope = (string) ($report['scope'] ?? 'CLUSTER');
$printFileName = trim((string) ($printFileName ?? ('Progress ' . ucfirst(strtolower($scope)) . ' ' . (string) ($cluster['cluster_name'] ?? 'Cluster') . ' - ' . (string) ($cluster['spv'] ?? '-') . ' - PT. TKM')));
$itemTypes = (array) ($report['itemTypes'] ?? []);
$planByType = (array) ($report['planByType'] ?? []);
$historyRows = (array) ($report['historyRows'] ?? []);
$finalAchieve = (array) ($report['finalAchieve'] ?? []);
$dailyActivityGroups = (array) ($report['dailyActivityGroups'] ?? []);
$logoTkm = base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png');
$logoWeb = base_url('assets/dist/img/logoweb.png');
$number = static function ($value, $zeroAsDash = false) {
    $num = (float) $value;
    if ($zeroAsDash && abs($num) < 0.00001) {
        return '-';
    }
    return number_format($num, 0, ',', '.');
};
$percent = static function ($value) {
    $num = (float) $value;
    return rtrim(rtrim(number_format($num, 1, ',', '.'), '0'), ',') . '%';
};
$qty = static function ($value) {
    $num = (float) $value;
    return rtrim(rtrim(number_format($num, 2, ',', '.'), '0'), ',');
};
?>

<div class="content-wrapper">
    <div class="progress-report-wrap">
        <div class="progress-report-shell">
            <div class="progress-report-toolbar">
                <div>
                    <div class="progress-report-title">Preview Progress Report <?= htmlspecialchars($scope) ?></div>
                    <div class="progress-report-subtitle"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></div>
                </div>
                <div class="d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap; justify-content:flex-end;">
                    <a href="<?= base_url('Implementasi_BOQ_MyRep/detail/' . (int) ($cluster['id_myrep_cluster'] ?? 0) . '#impl-history-pane') ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
                    <?php if (!empty($tcpdfAvailable)): ?>
                        <a href="<?= htmlspecialchars((string) ($pdfUrl ?? '#')) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i>Buka PDF
                        </a>
                    <?php endif; ?>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-print-title="<?= htmlspecialchars($printFileName, ENT_QUOTES) ?>"
                        onclick="document.title = this.getAttribute('data-print-title') || document.title; window.print();">
                        <i class="fas fa-print mr-1"></i>Print / Save PDF
                    </button>
                </div>
            </div>

            <div class="progress-report-page">
                <div class="progress-report-letterhead">
                    <div class="progress-report-letterhead__logo">
                        <img src="<?= htmlspecialchars($logoTkm) ?>" alt="TKM Logo">
                    </div>
                    <div class="progress-report-letterhead__brand">DatabaseTKM</div>
                    <div class="progress-report-letterhead__logo progress-report-letterhead__logo--right">
                        <img src="<?= htmlspecialchars($logoWeb) ?>" alt="Logo Web">
                    </div>
                </div>

                <table class="progress-report-meta">
                    <tr>
                        <td><strong>Progress Report</strong></td>
                        <td><?= htmlspecialchars($scope) ?></td>
                        <td><strong>Tanggal Print</strong></td>
                        <td><?= date('d/m/Y H:i') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Cluster</strong></td>
                        <td><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></td>
                        <td><strong>Team</strong></td>
                        <td><?= htmlspecialchars((string) ($cluster['spv'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Regional / Kota</strong></td>
                        <td><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-') . ' / ' . (string) ($cluster['city_name'] ?? '-')) ?></td>
                        <td><strong>OLT</strong></td>
                        <td><?= htmlspecialchars((string) ($cluster['nama_olt'] ?? '-')) ?></td>
                    </tr>
                </table>

                <div class="progress-report-heading">
                    <div class="progress-report-heading__title">History Progress <?= htmlspecialchars($scope) ?></div>
                    <div class="progress-report-heading__note">Dokumen bukti progress untuk penagihan invoice</div>
                </div>

                <table class="progress-report-summary mb-3">
                    <tr>
                        <td><strong>Total Plan</strong><br><?= $number($report['totalPlan'] ?? 0) ?></td>
                        <td><strong>Total Achievement</strong><br><?= $number($report['totalAchieve'] ?? 0) ?></td>
                        <td><strong>Selisih</strong><br><?= $number($report['totalGap'] ?? 0) ?></td>
                        <td><strong>Persentase</strong><br><?= $percent($report['percent'] ?? 0) ?></td>
                    </tr>
                </table>

                <?php if (empty($report['hasPlan'])): ?>
                    <div class="progress-report-empty">BOQ Tracker <?= htmlspecialchars($scope) ?> belum memiliki plan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="progress-report-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">HP DRM</th>
                                    <?php foreach ($itemTypes as $itemType): ?>
                                        <th colspan="2"><?= htmlspecialchars((string) $itemType) ?></th>
                                    <?php endforeach; ?>
                                    <th rowspan="2">Tanggal Progress</th>
                                    <th rowspan="2">Keterangan</th>
                                </tr>
                                <tr>
                                    <?php foreach ($itemTypes as $itemType): ?>
                                        <th>PLAN</th>
                                        <th>ACHIEV</th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historyRows as $index => $row): ?>
                                    <tr>
                                        <td><?= (int) $index + 1 ?></td>
                                        <td><?= $index === 0 ? $number($cluster['homepass_drm'] ?? 0) : '-' ?></td>
                                        <?php foreach ($itemTypes as $itemType): ?>
                                            <td><?= $index === 0 ? $number($planByType[$itemType] ?? 0) : '-' ?></td>
                                            <td><?= $number($row['achieve'][$itemType] ?? 0, true) ?></td>
                                        <?php endforeach; ?>
                                        <td><?= htmlspecialchars((string) ($row['progress_date'] ?? '-')) ?></td>
                                        <td class="progress-report-table__remark"><?= htmlspecialchars((string) ($row['remark'] ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <?php foreach ($itemTypes as $itemType): ?>
                                        <td><?= $number($planByType[$itemType] ?? 0) ?></td>
                                        <td><?= $number($finalAchieve[$itemType] ?? 0, true) ?></td>
                                    <?php endforeach; ?>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2">Selisih</td>
                                    <?php foreach ($itemTypes as $itemType): ?>
                                        <td colspan="2"><?= $number((float) ($planByType[$itemType] ?? 0) - (float) ($finalAchieve[$itemType] ?? 0), true) ?></td>
                                    <?php endforeach; ?>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="progress-report-section-title">Breakdown Daily Progress Aktivitas</div>
                <?php if (empty($dailyActivityGroups)): ?>
                    <div class="progress-report-empty">Belum ada daily progress aktivitas untuk scope ini.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="progress-report-daily-table">
                            <thead>
                                <tr>
                                    <th style="width:4%;">No</th>
                                    <th style="width:10%;">Tanggal</th>
                                    <th style="width:9%;">Team/Orang</th>
                                    <th style="width:14%;">Aktivitas</th>
                                    <th style="width:13%;">Detail</th>
                                    <th style="width:6%;">Qty</th>
                                    <th style="width:7%;">Jenis</th>
                                    <th style="width:9%;">Tipe</th>
                                    <th style="width:7%;">Foto</th>
                                    <th style="width:11%;">PIC</th>
                                    <th style="width:10%;">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $dailyNo = 1; ?>
                                <?php foreach ($dailyActivityGroups as $dailyGroup): ?>
                                    <?php
                                    $dailyRows = array_values((array) ($dailyGroup['rows'] ?? []));
                                    $dailyRowspan = max(count($dailyRows), 1);
                                    ?>
                                    <?php foreach ($dailyRows as $dailyIndex => $dailyRow): ?>
                                        <tr>
                                            <td><?= $dailyNo++ ?></td>
                                            <?php if ($dailyIndex === 0): ?>
                                                <td rowspan="<?= (int) $dailyRowspan ?>"><?= htmlspecialchars((string) ($dailyGroup['date'] ?? '-')) ?></td>
                                            <?php endif; ?>
                                            <td><?= (int) ($dailyRow['team_count'] ?? 0) ?> / <?= (int) ($dailyRow['worker_count'] ?? 0) ?></td>
                                            <td class="progress-report-daily-table__left"><?= htmlspecialchars((string) ($dailyRow['activity_name'] ?? '-')) ?></td>
                                            <td class="progress-report-daily-table__left"><?= htmlspecialchars((string) ($dailyRow['activity_detail'] ?? '-')) ?></td>
                                            <td><?= $qty($dailyRow['qty_activity'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars((string) ($dailyRow['unit_activity'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars((string) ($dailyRow['boq_type'] ?? '-')) ?></td>
                                            <td><?= (int) ($dailyRow['photo_count'] ?? 0) ?></td>
                                            <td class="progress-report-daily-table__left"><?= htmlspecialchars((string) ($dailyRow['pic'] ?? '-')) ?></td>
                                            <td class="progress-report-daily-table__left"><?= htmlspecialchars((string) ($dailyRow['remark_activity'] ?? '-')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
