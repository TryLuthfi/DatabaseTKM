<?php
defined('BASEPATH') or exit('No direct script access allowed');

$cluster = (array) ($cluster ?? []);
$documentRows = (array) ($documentRows ?? []);
$signatures = (array) ($signatures ?? []);
$printStatus = strtoupper(trim((string) ($printStatus ?? 'ON REVIEW')));

if (!function_exists('batchPrintText')) {
    function batchPrintText($value)
    {
        $value = trim((string) $value);
        return $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '-';
    }
}

if (!function_exists('batchPrintDate')) {
    function batchPrintDate($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('d/m/Y', $timestamp) : batchPrintText($value);
    }
}

if (!function_exists('batchPrintDateTime')) {
    function batchPrintDateTime($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('d/m/Y H:i', $timestamp) : batchPrintText($value);
    }
}

if (!function_exists('batchPrintMoney')) {
    function batchPrintMoney($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return '-';
        }
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('batchPrintDocStatus')) {
    function batchPrintDocStatus($status, $default = 'NY')
    {
        $status = strtoupper(trim((string) $status));
        if ($status === '') {
            return $default;
        }
        return $status === 'UPLOADED' ? 'ON REVIEW' : $status;
    }
}

$stampClass = strtolower(str_replace(' ', '-', $printStatus));
$clusterDetails = [
    'ID Cluster' => $cluster['id_myrep_cluster'] ?? '',
    'Nama Cluster' => $cluster['cluster_name'] ?? '',
    'Kota' => $cluster['city_name'] ?? '',
    'Provinsi' => $cluster['province_name'] ?? '',
    'Regional' => $cluster['regional_name'] ?? '',
];

$submissionDetails = [
    'Nomor Batch Astri' => $cluster['astri_batch_number'] ?? '',
    'Tanggal Batch Approval' => batchPrintDate($cluster['astri_batch_approved_at'] ?? ''),
    'Tanggal Submit Finance' => batchPrintDate($cluster['finance_submitted_at'] ?? $cluster['submitted_to_finance_at'] ?? ''),
    'HP Donasi' => number_format((float) ($cluster['hp_donasi'] ?? 0), 0, ',', '.'),
    'Nominal Donasi / Approval EMR' => batchPrintMoney($cluster['nominal_pengajuan_area'] ?? $cluster['nominal_nego_emr'] ?? ''),
    'Nama Penerima Dana' => $cluster['recipient_name'] ?? '',
    'Bank' => $cluster['bank_name'] ?? '',
    'Nomor Rekening' => $cluster['bank_account_number'] ?? '',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= batchPrintText($title ?? 'Print Checklist Pengajuan Donasi') ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.2;
        }

        .print-page {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 8mm;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
        }

        .print-toolbar {
            width: 210mm;
            margin: 16px auto 0;
            text-align: right;
        }

        .print-button {
            border: 1px solid #0f172a;
            border-radius: 4px;
            background: #0f172a;
            color: #fff;
            padding: 8px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 68px;
            gap: 12px;
            align-items: start;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
        }

        .brand-block {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .logo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 30px;
        }

        .logo-row img {
            display: block;
            max-width: 100%;
            object-fit: contain;
        }

        .logo-tkm {
            width: 138px;
            height: 30px;
            object-position: left center;
        }

        .logo-web {
            width: 104px;
            height: 30px;
            object-position: left center;
            padding-left: 12px;
            border-left: 1px solid #cbd5e1;
        }

        .title h1 {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .title p {
            margin: 3px 0 0;
            color: #475569;
            font-size: 8.5px;
            font-weight: 800;
        }

        .stamp {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 68px;
            min-height: 34px;
            padding: 6px 8px;
            border: 3px solid #64748b;
            border-radius: 4px;
            color: #64748b;
            font-size: 8px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .stamp.approved {
            border-color: #15803d;
            color: #15803d;
        }

        .stamp.on-review {
            border-color: #ca8a04;
            color: #ca8a04;
        }

        .stamp.rejected {
            border-color: #b91c1c;
            color: #b91c1c;
        }

        .section {
            margin-top: 7px;
            page-break-inside: avoid;
        }

        .section-title {
            margin: 0 0 4px;
            padding: 4px 6px;
            background: #0f172a;
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 1px solid #cbd5e1;
            border-left: 1px solid #cbd5e1;
        }

        .info-item {
            min-height: 28px;
            padding: 4px 5px;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }

        .info-label {
            display: block;
            color: #64748b;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .info-value {
            display: block;
            margin-top: 2px;
            color: #0f172a;
            font-size: 8px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 3px 4px;
            vertical-align: top;
        }

        th {
            background: #1f2937;
            color: #fff;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            font-size: 7.5px;
        }

        .col-no {
            width: 22px;
            text-align: center;
        }

        .col-status {
            width: 58px;
            text-align: center;
            white-space: nowrap;
        }

        .col-doc-name {
            width: 34mm;
        }

        .col-note {
            width: 33mm;
        }

        .col-remark {
            width: 24mm;
        }

        .status-pill {
            display: inline-block;
            min-width: 46px;
            padding: 2px 4px;
            border-radius: 999px;
            background: #e5e7eb;
            color: #374151;
            font-size: 6.5px;
            font-weight: 900;
            text-align: center;
        }

        .status-approved {
            background: #dcfce7;
            color: #15803d;
        }

        .status-uploaded,
        .status-on-review {
            background: #fef3c7;
            color: #a16207;
        }

        .status-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-ny,
        .status-not-uploaded {
            background: #e5e7eb;
            color: #475569;
        }

        .doc-note {
            color: #64748b;
            font-size: 7px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 7px;
            color: #64748b;
            font-size: 7px;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .signature-box {
            min-height: 68px;
            border: 1px solid #cbd5e1;
            padding: 12px 7px 8px;
            text-align: center;
        }

        .signature-role {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .signature-name {
            display: inline-block;
            min-width: 150px;
            margin-top: 2px;
            padding-top: 0;
            font-size: 8px;
            font-weight: 900;
        }

        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        @media print {
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none;
            }

            .print-page {
                width: 200mm;
                min-height: 287mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" class="print-button" onclick="window.print()">Print</button>
    </div>

    <main class="print-page">
        <header class="header">
            <div class="brand-block">
                <div class="logo-row">
                    <img src="<?= base_url('assets/dist/img/logotkmsolid.png') ?>" alt="TKM" class="logo-tkm">
                    <img src="<?= base_url('assets/dist/img/logoweb.png') ?>" alt="MyRep" class="logo-web">
                </div>
                <div class="title">
                    <h1>Checklist Pengajuan Donasi</h1>
                    <p>Dokumen Tahap 1 Pra-Finance Zeyn</p>
                </div>
            </div>
            <div class="stamp <?= batchPrintText($stampClass) ?>"><?= batchPrintText($printStatus) ?></div>
        </header>

        <section class="section">
            <h2 class="section-title">Detail Cluster</h2>
            <div class="info-grid">
                <?php foreach ($clusterDetails as $label => $value): ?>
                    <div class="info-item">
                        <span class="info-label"><?= batchPrintText($label) ?></span>
                        <span class="info-value"><?= batchPrintText($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Detail Pengajuan</h2>
            <div class="info-grid">
                <?php foreach ($submissionDetails as $label => $value): ?>
                    <div class="info-item">
                        <span class="info-label"><?= batchPrintText($label) ?></span>
                        <span class="info-value"><?= is_string($value) && strpos($value, '<') !== false ? $value : batchPrintText($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Detail Dokumen Tahap 1 Pra-Finance Zeyn</h2>
            <table>
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th class="col-doc-name">Nama Dokumen</th>
                        <th class="col-note">Keterangan</th>
                        <th class="col-status">Status SITAC</th>
                        <th class="col-status">Status Finance</th>
                        <th class="col-remark">Remark SITAC</th>
                        <th class="col-remark">Remark Finance</th>
                        <th class="col-status">Tanggal Upload</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documentRows)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Belum ada master dokumen.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documentRows as $index => $row): ?>
                            <?php
                            $sitacStatus = batchPrintDocStatus($row['status_file'] ?? '', 'NOT UPLOADED');
                            $isDocumentNotRequired = (int) ($row['is_document_not_required'] ?? 0) === 1;
                            $financeStatus = $isDocumentNotRequired ? 'APPROVED' : batchPrintDocStatus($row['finance_status'] ?? '', 'NY');
                            $sitacClass = strtolower(str_replace([' ', '_'], '-', $sitacStatus));
                            $financeClass = strtolower(str_replace([' ', '_'], '-', $financeStatus));
                            ?>
                            <tr>
                                <td class="col-no"><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= batchPrintText($row['doc_name'] ?? '-') ?></strong>
                                    <div class="doc-note"><?= (int) ($row['is_required'] ?? 1) === 1 ? 'Wajib' : 'Opsional' ?></div>
                                </td>
                                <td><?= batchPrintText($row['doc_requirement_note'] ?? '-') ?></td>
                                <td class="col-status">
                                    <span class="status-pill status-<?= batchPrintText($sitacClass) ?>"><?= batchPrintText($sitacStatus) ?></span>
                                </td>
                                <td class="col-status">
                                    <span class="status-pill status-<?= batchPrintText($financeClass) ?>"><?= batchPrintText($financeStatus) ?></span>
                                </td>
                                <td><?= batchPrintText($row['remark'] ?? '-') ?></td>
                                <td><?= batchPrintText($row['finance_remark'] ?? '-') ?></td>
                                <td class="col-status"><?= batchPrintDateTime($row['uploaded_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Sign Approval</h2>
            <div class="signature-grid">
                <?php foreach (['sitac', 'finance'] as $signatureKey): ?>
                    <?php
                    $signature = (array) ($signatures[$signatureKey] ?? []);
                    ?>
                    <div class="signature-box">
                        <div class="signature-role"><?= batchPrintText($signature['label'] ?? '-') ?></div>
                        <div class="signature-name"><?= batchPrintText($signature['name'] ?? '-') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <footer class="footer">
            <span>Dicetak oleh: <?= batchPrintText($printedBy ?? '-') ?></span>
            <span>Waktu cetak: <?= batchPrintDateTime($printedAt ?? '') ?></span>
        </footer>
    </main>
</body>
</html>
