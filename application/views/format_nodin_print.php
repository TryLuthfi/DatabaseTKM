<?php
$nodin = $nodin ?? [];
$nodinDetails = $nodinDetails ?? [];
$pageLayout = $pageLayout ?? ['size' => 'A4', 'orientation' => 'portrait'];

$formatDate = static function ($value) {
    $timestamp = strtotime((string) $value);
    if (!$timestamp) {
        return '-';
    }

    return date('d F Y', $timestamp);
};

$formatNumber = static function ($value) {
    return ($value === null || $value === '') ? '-' : number_format((float) $value, 0, ',', '.');
};

$buildElectronicQr = static function ($seed, $label) {
    $hash = md5($seed . '|' . $label);
    $size = 21;
    $cell = 3;
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

$signatureMaker = [
    'group' => 'Membuat,',
    'name' => (string) ($nodin['nama_user'] ?? $nodin['dibuat_oleh'] ?? '-'),
    'title' => 'Admin Logistik',
];

$signatureAcknowledgements = [
    [
        'name' => 'Syarif Hidayat',
        'title' => 'Logistik Manager',
    ],
    [
        'name' => 'Satwika VE',
        'title' => 'Purchasing',
    ],
    [
        'name' => 'Yaya Sunarya',
        'title' => 'General Manager Project',
    ],
    [
        'name' => 'Almaida',
        'title' => 'General Manager Finance',
    ],
];

$signatureApprover = [
    'group' => 'Menyetujui,',
    'name' => 'Ida Isnaeni',
    'title' => 'Direktur',
];

$normalizePrintGroupValue = static function ($value) {
    return strtolower(trim((string) $value));
};

$groupedNodinDetails = [];
foreach ($nodinDetails as $detailRow) {
    $groupKey = implode('|', [
        $normalizePrintGroupValue($detailRow['nomor_purchase_request'] ?? ''),
        $normalizePrintGroupValue($detailRow['id_kode_item'] ?? ($detailRow['nama_item'] ?? '')),
        $normalizePrintGroupValue($detailRow['nama_item'] ?? ''),
        $normalizePrintGroupValue($detailRow['satuan_item'] ?? ''),
        $normalizePrintGroupValue($detailRow['vendor_pabrik'] ?? ''),
        preg_replace('/[^0-9.\-]/', '', (string) ($detailRow['harga_satuan'] ?? '0')),
        $normalizePrintGroupValue($detailRow['keterangan'] ?? ''),
    ]);

    if (!isset($groupedNodinDetails[$groupKey])) {
        $groupedNodinDetails[$groupKey] = $detailRow;
    } else {
        $groupedNodinDetails[$groupKey]['kebutuhan_project'] = (float) ($groupedNodinDetails[$groupKey]['kebutuhan_project'] ?? 0) + (float) ($detailRow['kebutuhan_project'] ?? 0);
        $groupedNodinDetails[$groupKey]['outstanding_pr'] = (float) ($groupedNodinDetails[$groupKey]['outstanding_pr'] ?? 0) + (float) ($detailRow['outstanding_pr'] ?? 0);
        $groupedNodinDetails[$groupKey]['qty_po_nodin'] = (float) ($groupedNodinDetails[$groupKey]['qty_po_nodin'] ?? 0) + (float) ($detailRow['qty_po_nodin'] ?? 0);
    }
}
$nodinDetails = array_values($groupedNodinDetails);

usort($nodinDetails, static function ($left, $right) {
    $leftVendor = strtolower(trim((string) ($left['vendor_pabrik'] ?? '')));
    $rightVendor = strtolower(trim((string) ($right['vendor_pabrik'] ?? '')));
    if ($leftVendor !== $rightVendor) {
        return $leftVendor <=> $rightVendor;
    }

    $leftItem = strtolower(trim((string) ($left['nama_item'] ?? '')));
    $rightItem = strtolower(trim((string) ($right['nama_item'] ?? '')));
    if ($leftItem !== $rightItem) {
        return $leftItem <=> $rightItem;
    }

    return strcmp((string) ($left['id_nota_dinas_po_detail'] ?? ''), (string) ($right['id_nota_dinas_po_detail'] ?? ''));
});

$vendorRowspans = [];
$vendorCursor = 0;
$vendorCount = count($nodinDetails);
while ($vendorCursor < $vendorCount) {
    $vendorName = trim((string) ($nodinDetails[$vendorCursor]['vendor_pabrik'] ?? ''));
    $rowspan = 1;
    for ($scan = $vendorCursor + 1; $scan < $vendorCount; $scan++) {
        $candidateVendor = trim((string) ($nodinDetails[$scan]['vendor_pabrik'] ?? ''));
        if (strcasecmp($vendorName, $candidateVendor) !== 0) {
            break;
        }
        $rowspan++;
    }
    $vendorRowspans[$vendorCursor] = $rowspan;
    $vendorCursor += $rowspan;
}

$totalKebutuhan = array_sum(array_map('floatval', array_column($nodinDetails, 'kebutuhan_project')));
$totalOutstanding = array_sum(array_map('floatval', array_column($nodinDetails, 'outstanding_pr')));
$totalQtyPo = array_sum(array_map('floatval', array_column($nodinDetails, 'qty_po_nodin')));
$totalHargaSatuan = array_sum(array_map('floatval', array_column($nodinDetails, 'harga_satuan')));
$totalNominal = array_sum(array_map(static function ($row) {
    return ((float) ($row['qty_po_nodin'] ?? 0)) * ((float) ($row['harga_satuan'] ?? 0));
}, $nodinDetails));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars((string) ($nodin['nomor_nota_dinas'] ?? 'Nota Dinas PO'), ENT_QUOTES) ?></title>
    <style>
        @page { margin: 10mm; size: <?= htmlspecialchars((string) ($pageLayout['size'] ?? 'A4'), ENT_QUOTES) ?> <?= htmlspecialchars((string) ($pageLayout['orientation'] ?? 'landscape'), ENT_QUOTES) ?>; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #ffffff;
        }

        :root {
            --print-border: 1pt solid #111827;
        }

        * {
            box-sizing: border-box;
        }

        .print-page {
            border: var(--print-border);
        }

        .print-title {
            padding: 14px 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            border-bottom: var(--print-border);
            background: #f7f3e7;
        }

        .print-body {
            padding: 0;
        }

        .meta-table,
        .detail-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            font-size: 11px;
            margin-top: 6px;
        }

        .meta-table td {
            border: var(--print-border);
            padding: 6px 8px;
            vertical-align: top;
        }

        .meta-table td:first-child {
            width: 16%;
            font-weight: 700;
        }

        .meta-table td:nth-child(2) {
            width: 2%;
            text-align: center;
            font-weight: 700;
        }

        .detail-shell {
            padding: 18px 0 0;
        }

        .detail-table {
            font-size: 11px;
        }

        .detail-table th,
        .detail-table td {
            border: var(--print-border);
            padding: 6px 6px;
            vertical-align: middle;
        }

        .detail-table thead th {
            background: #f4efdf;
            text-align: center;
            font-weight: 800;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .closing-note {
            padding: 18px 2px 10px;
            font-size: 11px;
            line-height: 1.6;
        }

        .closing-date {
            font-weight: 700;
            margin-top: 10px;
        }

        .signature-table {
            margin-top: 6px;
            table-layout: fixed;
            font-size: 10px;
        }

        .signature-table td {
            border: var(--print-border);
            vertical-align: top;
            text-align: center;
            padding: 4px;
            height: 112px;
        }

        .signature-role {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .signature-shell {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 104px;
        }

        .signature-shell--edge {
            min-height: 150px;
        }

        .signature-qr {
            width: 52px;
            margin: 0 auto 4px;
            padding: 1px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }

        .signature-qr svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .signature-caption {
            display: block;
            margin-bottom: 14px;
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            line-height: 1.2;
        }

        .signature-name {
            display: inline-block;
            min-width: 82px;
            border-top: 1px solid #111827;
            padding-top: 3px;
            font-weight: 700;
            text-decoration: underline;
            font-size: 9px;
        }

        .signature-title {
            display: block;
            margin-top: 4px;
            font-weight: 700;
            font-size: 9px;
            line-height: 1.2;
        }

        .signature-group-cell {
            padding: 0 !important;
        }

        .signature-group-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-group-table td {
            border: 0;
            padding: 0;
            height: auto;
        }

        .signature-group-table tr + tr td {
            border-top: var(--print-border);
        }

        .signature-group-title {
            font-weight: 700;
            padding: 4px 0;
        }

        .signature-group-table .signature-group-item + .signature-group-item {
            border-left: var(--print-border);
        }

        .signature-group-item .signature-shell {
            min-height: 104px;
            padding: 4px 2px;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-page">
        <div class="print-title">NOTA DINAS - PO</div>

        <div class="print-body">
            <table class="meta-table">
                <tr>
                    <td>Nomor Nota Dinas</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($nodin['nomor_nota_dinas'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Tanggal Nota Dinas</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($formatDate($nodin['tanggal_nota_dinas'] ?? ''), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Ditujukan Kepada</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($nodin['ditujukan_kepada'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($nodin['nama_user'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>Tujuan Penerbitan PO</td>
                    <td>:</td>
                    <td><?= htmlspecialchars((string) ($nodin['tujuan_penerbitan_po'] ?? '-'), ENT_QUOTES) ?></td>
                </tr>
            </table>

            <div class="detail-shell">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:4%">No.</th>
                            <th rowspan="2" style="width:28%">Nama Material</th>
                            <th rowspan="2" style="width:5%">Satuan</th>
                            <th rowspan="2" style="width:7%">Kebutuhan Project</th>
                            <th colspan="3" style="width:18%">VOLUME</th>
                            <th rowspan="2" style="width:6%">Harga Satuan</th>
                            <th rowspan="2" style="width:9%">Harga Total</th>
                            <th rowspan="2" style="width:9%">Vendor / Pabrik</th>
                            <th rowspan="2">Keterangan</th>
                        </tr>
                        <tr>
                            <th style="width:6%">Outstanding</th>
                            <th style="width:6%">PO</th>
                            <th style="width:6%">Total Material</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nodinDetails as $index => $detail): ?>
                            <?php $lineTotal = ((float) ($detail['qty_po_nodin'] ?? 0)) * ((float) ($detail['harga_satuan'] ?? 0)); ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars((string) ($detail['nama_item'] ?? '-'), ENT_QUOTES) ?></td>
                                <td class="text-center"><?= htmlspecialchars((string) ($detail['satuan_item'] ?? '-'), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= htmlspecialchars($formatNumber($detail['kebutuhan_project'] ?? null), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= htmlspecialchars($formatNumber($detail['outstanding_pr'] ?? null), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= htmlspecialchars($formatNumber($detail['qty_po_nodin'] ?? null), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= htmlspecialchars($formatNumber($detail['qty_po_nodin'] ?? null), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= htmlspecialchars($formatNumber($detail['harga_satuan'] ?? null), ENT_QUOTES) ?></td>
                                <td class="text-right"><?= number_format($lineTotal, 0, ',', '.') ?></td>
                                <?php if (isset($vendorRowspans[$index])): ?>
                                    <td class="text-center" rowspan="<?= (int) $vendorRowspans[$index] ?>"><?= htmlspecialchars((string) ($detail['vendor_pabrik'] ?? '-'), ENT_QUOTES) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars((string) ($detail['keterangan'] ?? '-'), ENT_QUOTES) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-center"><strong>TOTAL</strong></td>
                            <td class="text-right"><strong><?= number_format($totalKebutuhan, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($totalOutstanding, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($totalQtyPo, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($totalQtyPo, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($totalHargaSatuan, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($totalNominal, 0, ',', '.') ?></strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="closing-note">
                Demikian Nota Dinas ini Kami buat, atas perhatiannya diucapkan terimakasih.
                <div class="closing-date">Jakarta, <?= htmlspecialchars($formatDate($nodin['tanggal_nota_dinas'] ?? ''), ENT_QUOTES) ?></div>
            </div>

            <table class="signature-table">
                <tr>
                    <td style="width:18%;">
                        <?php $makerSeed = (string) ($nodin['nomor_nota_dinas'] ?? 'NODIN') . '|' . $signatureMaker['name'] . '|' . $signatureMaker['title']; ?>
                        <div class="signature-shell signature-shell--edge">
                            <div class="signature-role"><?= htmlspecialchars($signatureMaker['group'], ENT_QUOTES) ?></div>
                            <div class="signature-qr"><?= $buildElectronicQr($makerSeed, $signatureMaker['title']) ?></div>
                            <span class="signature-caption">Tanda Tangan Elektronik</span>
                            <span class="signature-name"><?= htmlspecialchars($signatureMaker['name'], ENT_QUOTES) ?></span>
                            <span class="signature-title"><?= htmlspecialchars($signatureMaker['title'], ENT_QUOTES) ?></span>
                        </div>
                    </td>
                    <td class="signature-group-cell" style="width:64%;">
                        <table class="signature-group-table">
                            <tr>
                                <td colspan="4" class="signature-group-title">Mengetahui,</td>
                            </tr>
                            <tr>
                                <?php foreach ($signatureAcknowledgements as $signature): ?>
                                    <?php $seed = (string) ($nodin['nomor_nota_dinas'] ?? 'NODIN') . '|' . $signature['name'] . '|' . $signature['title']; ?>
                                    <td class="signature-group-item">
                                        <div class="signature-shell">
                                            <div class="signature-qr"><?= $buildElectronicQr($seed, $signature['title']) ?></div>
                                            <span class="signature-caption">Tanda Tangan Elektronik</span>
                                            <span class="signature-name"><?= htmlspecialchars($signature['name'], ENT_QUOTES) ?></span>
                                            <span class="signature-title"><?= htmlspecialchars($signature['title'], ENT_QUOTES) ?></span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </td>
                    <td style="width:18%;">
                        <?php $approverSeed = (string) ($nodin['nomor_nota_dinas'] ?? 'NODIN') . '|' . $signatureApprover['name'] . '|' . $signatureApprover['title']; ?>
                        <div class="signature-shell signature-shell--edge">
                            <div class="signature-role"><?= htmlspecialchars($signatureApprover['group'], ENT_QUOTES) ?></div>
                            <div class="signature-qr"><?= $buildElectronicQr($approverSeed, $signatureApprover['title']) ?></div>
                            <span class="signature-caption">Tanda Tangan Elektronik</span>
                            <span class="signature-name"><?= htmlspecialchars($signatureApprover['name'], ENT_QUOTES) ?></span>
                            <span class="signature-title"><?= htmlspecialchars($signatureApprover['title'], ENT_QUOTES) ?></span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
