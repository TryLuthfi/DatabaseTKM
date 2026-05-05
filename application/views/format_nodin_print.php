<?php
$nodin = $nodin ?? [];
$nodinDetails = $nodinDetails ?? [];
$pageLayout = $pageLayout ?? ['size' => 'A4', 'orientation' => 'landscape'];

$formatDate = static function ($value) {
    $timestamp = strtotime((string) $value);
    if (!$timestamp) {
        return '-';
    }

    return date('d F Y', $timestamp);
};

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

        .print-page {
            border: 1px solid #111827;
            min-height: calc(100vh - 20mm);
        }

        .print-title {
            padding: 14px 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            border-bottom: 1px solid #111827;
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
            border: 1px solid #111827;
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
            border: 1px solid #111827;
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
            font-size: 11px;
        }

        .signature-table td {
            border: 1px solid #111827;
            vertical-align: top;
            text-align: center;
            padding: 6px;
            height: 140px;
        }

        .signature-role {
            font-weight: 700;
            margin-bottom: 82px;
        }

        .signature-name {
            display: inline-block;
            min-width: 110px;
            border-top: 1px solid #111827;
            padding-top: 4px;
            font-weight: 700;
            text-decoration: underline;
        }

        .signature-title {
            display: block;
            margin-top: 6px;
            font-weight: 700;
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
                    <td><?= htmlspecialchars((string) ($nodin['nama_user'] ?? $nodin['dibuat_oleh'] ?? '-'), ENT_QUOTES) ?></td>
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
                                <td class="text-right"><?= number_format((float) ($detail['kebutuhan_project'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($detail['outstanding_pr'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($detail['qty_po_nodin'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($detail['qty_po_nodin'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($detail['harga_satuan'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($lineTotal, 0, ',', '.') ?></td>
                                <td class="text-center"><?= htmlspecialchars((string) ($detail['vendor_pabrik'] ?? '-'), ENT_QUOTES) ?></td>
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
                    <td>
                        <div class="signature-role">Membuat,</div>
                        <span class="signature-name">Irfan Musa'ad</span>
                        <span class="signature-title">Admin Logistik</span>
                    </td>
                    <td colspan="4">
                        <div class="signature-role">Mengetahui,</div>
                        <div style="display:flex;justify-content:space-around;gap:10px;">
                            <div>
                                <span class="signature-name">Syarif Hidayat</span>
                                <span class="signature-title">Logistik Manager</span>
                            </div>
                            <div>
                                <span class="signature-name">Satwika VE</span>
                                <span class="signature-title">Purchasing</span>
                            </div>
                            <div>
                                <span class="signature-name">Yaya Sunarya</span>
                                <span class="signature-title">General Manager Project</span>
                            </div>
                            <div>
                                <span class="signature-name">Almaida</span>
                                <span class="signature-title">General Manager Finance</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-role">Menyetujui,</div>
                        <span class="signature-name">Ida Isnaeni</span>
                        <span class="signature-title">Direktur</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
