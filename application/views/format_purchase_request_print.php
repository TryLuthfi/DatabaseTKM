<?php
$purchaseRequest = $purchaseRequest ?? [];
$detailPurchaseRequest = $detailPurchaseRequest ?? [];
$approvalStages = $purchaseRequest['workflow_stages'] ?? [];

$formatDate = static function ($value) {
    $timestamp = strtotime((string) $value);
    if (!$timestamp) {
        return '-';
    }

    return date('d F Y', $timestamp);
};

$companyName = 'PT. TECHNOLOGY KARYA MANDIRI';
$lokasiTitle = strtoupper((string) ($purchaseRequest['kota_lokasi_gudang'] ?? ''));
$documentTitle = 'PURCHASE REQUEST';
if ($lokasiTitle !== '') {
    $documentTitle .= ' - LOKASI ' . $lokasiTitle;
}

$signerNames = [
    'Planning' => 'Yaya Sunarya',
    'Finance' => 'Almaida',
];

$formatVolume = static function ($value) {
    return ($value === null || $value === '') ? '-' : number_format((float) $value, 0, ',', '.');
};

$buildElectronicQr = static function ($seed, $label) {
    $hash = md5($seed . '|' . $label);
    $size = 21;
    $cell = 4;
    $svg = '';

    for ($row = 0; $row < $size; $row++) {
        for ($col = 0; $col < $size; $col++) {
            $index = ($row * $size + $col) % strlen($hash);
            $value = hexdec($hash[$index]);
            $isFinder =
                ($row < 7 && $col < 7) ||
                ($row < 7 && $col >= $size - 7) ||
                ($row >= $size - 7 && $col < 7);

            if ($isFinder) {
                $localRow = $row % 7;
                $localCol = $col % 7;
                $isDark = $localRow === 0 || $localRow === 6 || $localCol === 0 || $localCol === 6
                    || (($localRow >= 2 && $localRow <= 4) && ($localCol >= 2 && $localCol <= 4));
            } else {
                $isDark = (($value + $row + $col) % 2) === 0;
            }

            if ($isDark) {
                $svg .= '<rect x="' . (($col + 1) * $cell) . '" y="' . (($row + 1) * $cell) . '" width="' . $cell . '" height="' . $cell . '" fill="#111827"></rect>';
            }
        }
    }

    $dimension = ($size + 2) * $cell;

    return '<svg viewBox="0 0 ' . $dimension . ' ' . $dimension . '" xmlns="http://www.w3.org/2000/svg" aria-label="QR code elektronik">'
        . '<rect x="0" y="0" width="' . $dimension . '" height="' . $dimension . '" fill="#ffffff"/>'
        . $svg
        . '</svg>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars((string) ($purchaseRequest['nomor_purchase_request'] ?? 'Purchase Request'), ENT_QUOTES) ?></title>
    <style>
        @page { margin: 10mm; size: A4 landscape; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #ffffff;
        }

        .print-page {
            border: 1px solid #374151;
            min-height: calc(100vh - 24mm);
        }

        .print-header {
            border-bottom: 1px solid #374151;
            text-align: center;
        }

        .print-header__brand {
            padding: 10px 16px 6px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .print-header__title {
            background: #f7e5d6;
            border-top: 1px solid #374151;
            padding: 8px 16px;
            font-size: 18px;
            font-weight: 800;
        }

        .print-body {
            padding: 12px;
        }

        .intro-row {
            margin-bottom: 10px;
        }

        .intro-badge__box {
            width: 360px;
            max-width: 100%;
            background: #8fb5df;
            border-radius: 14px 0 14px 0;
            color: #0f172a;
            padding: 10px 14px;
            text-align: center;
        }

        .intro-badge__title {
            display: block;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .intro-badge__subtitle {
            display: block;
            font-size: 10px;
            margin-top: 2px;
        }

        .meta-table,
        .item-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            margin-bottom: 8px;
            font-size: 11px;
        }

        .meta-table td {
            border: 1px solid #374151;
            padding: 2px 6px;
            vertical-align: top;
        }

        .meta-table td:first-child {
            width: 24%;
            font-weight: 700;
        }

        .meta-table td:nth-child(2) {
            width: 2%;
            text-align: center;
            font-weight: 700;
        }

        .item-table {
            margin-top: 6px;
            font-size: 10px;
        }

        .item-table th,
        .item-table td {
            border: 1px solid #374151;
            padding: 3px 5px;
        }

        .item-table thead th {
            background: #8fb5df;
            text-transform: uppercase;
            font-weight: 800;
            text-align: center;
        }

        .item-table th[colspan] {
            background: #8fb5df;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .notes {
            margin-top: 14px;
            font-size: 10px;
            line-height: 1.5;
        }

        .notes__title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .signature-table {
            margin-top: 14px;
            table-layout: fixed;
            font-size: 9px;
        }

        .signature-table td {
            width: 25%;
            vertical-align: top;
            text-align: center;
            padding: 0 4px;
        }

        .signature-role {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .signature-barcode {
            width: 72px;
            margin: 0 auto 4px;
            border: 1px solid #94a3b8;
            padding: 2px;
            background: #ffffff;
        }

        .signature-barcode svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .signature-caption {
            display: block;
            margin-bottom: 16px;
            font-size: 7px;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #475569;
        }

        .signature-name {
            display: inline-block;
            min-width: 92px;
            border-top: 1px solid #111827;
            padding-top: 3px;
            font-weight: 700;
            font-size: 9px;
        }

        .signature-stage {
            display: block;
            margin-top: 3px;
            font-size: 8px;
            text-transform: uppercase;
        }

        .page-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #475569;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-page">
        <div class="print-header">
            <div class="print-header__brand"><?= htmlspecialchars($companyName, ENT_QUOTES) ?></div>
            <div class="print-header__title"><?= htmlspecialchars($documentTitle, ENT_QUOTES) ?></div>
        </div>

        <div class="print-body">
            <div class="intro-row">
                <div class="intro-badge__box">
                    <span class="intro-badge__title">Kebutuhan Material</span>
                    <span class="intro-badge__subtitle"><?= htmlspecialchars($companyName, ENT_QUOTES) ?></span>
                </div>
            </div>

            <table class="meta-table">
                <tr>
                    <td>Nomor PR</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($purchaseRequest['nomor_purchase_request'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($formatDate($purchaseRequest['tanggal_pembuatan'] ?? ''), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Nomor SP</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($purchaseRequest['nomer_sp'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Tanggal SP</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($formatDate($purchaseRequest['tanggal_sp'] ?? ''), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Nama Project</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($purchaseRequest['nama_project'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Lokasi Project</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($purchaseRequest['kota_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
            </table>

            <table class="item-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:4%">No</th>
                        <th rowspan="2" style="width:12%">Kode Material</th>
                        <th rowspan="2">Uraian Material</th>
                        <th rowspan="2" style="width:8%">Satuan</th>
                        <th colspan="4" style="width:24%">Volume</th>
                        <th rowspan="2" style="width:18%">Keterangan</th>
                    </tr>
                    <tr>
                        <th style="width:6%">BoQ</th>
                        <th style="width:6%">Stock Area</th>
                        <th style="width:6%">PR</th>
                        <th style="width:6%">Realisasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailPurchaseRequest as $index => $row): ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars((string) ($row['id_kode_item'] ?? '-'), ENT_QUOTES) ?></td>
                            <td><?= htmlspecialchars((string) ($row['nama_item'] ?? '-'), ENT_QUOTES) ?></td>
                            <td class="text-center"><?= htmlspecialchars((string) ($row['satuan_item'] ?? '-'), ENT_QUOTES) ?></td>
                            <td class="text-right"><?= htmlspecialchars($formatVolume($row['boq'] ?? null), ENT_QUOTES) ?></td>
                            <td class="text-right"><?= htmlspecialchars($formatVolume($row['stok_area'] ?? null), ENT_QUOTES) ?></td>
                            <td class="text-right"><?= htmlspecialchars($formatVolume($row['qty_request'] ?? null), ENT_QUOTES) ?></td>
                            <td class="text-right"><?= htmlspecialchars($formatVolume($row['qty_planning'] ?? null), ENT_QUOTES) ?></td>
                            <td><?= htmlspecialchars((string) ($row['keterangan'] ?? '-'), ENT_QUOTES) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="notes">
                <div class="notes__title">Berikut syarat dan ketentuan :</div>
                <div>1. Waktu Pengiriman paling lambat : <?= htmlspecialchars($formatDate($purchaseRequest['tanggal_estimasi_pengiriman'] ?? ''), ENT_QUOTES) ?></div>
                <div>2. Provider : <?= htmlspecialchars((string) ($purchaseRequest['id_project'] ?? '-'), ENT_QUOTES) ?></div>
                <div>3. Alamat Pengiriman : <?= htmlspecialchars((string) ($purchaseRequest['kota_lokasi_gudang'] ?? '-'), ENT_QUOTES) ?></div>
                <div>4. PIC Penerima Material : <?= htmlspecialchars((string) ($purchaseRequest['nama_pembuat'] ?? '-'), ENT_QUOTES) ?></div>
            </div>

            <table class="signature-table">
                <tr>
                    <?php if (!empty($approvalStages)): ?>
                        <?php foreach ($approvalStages as $stage): ?>
                            <?php
                            $stageLabel = (string) ($stage['label'] ?? '-');
                            $stageColumn = (string) ($stage['column'] ?? '');
                            $isApproved = $stageColumn !== '' && !empty($purchaseRequest[$stageColumn]);
                            $signerName = $signerNames[$stageLabel] ?? $stageLabel;
                            $barcodeSeed = (string) ($purchaseRequest['nomor_purchase_request'] ?? 'PR') . '|' . $stageLabel . '|' . $signerName;
                            ?>
                            <td>
                                <div class="signature-role"><?= htmlspecialchars($stageLabel, ENT_QUOTES) ?></div>
                                <div class="signature-barcode">
                                    <?= $isApproved ? $buildElectronicQr($barcodeSeed, $stageLabel) : '' ?>
                                </div>
                                <span class="signature-caption">Tanda Tangan Elektronik</span>
                                <span class="signature-name"><?= htmlspecialchars($isApproved ? $signerName : 'Pending', ENT_QUOTES) ?></span>
                                <span class="signature-stage"><?= htmlspecialchars($stageLabel, ENT_QUOTES) ?></span>
                            </td>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <td>
                            <div class="signature-role">Approval</div>
                            <span class="signature-name">Approved</span>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>

            <div class="page-footer">LEMBAR 1</div>
        </div>
    </div>
</body>
</html>
