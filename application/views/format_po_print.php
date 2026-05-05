<?php
$poHeader = $poHeader ?? [];
$poItems = $poItems ?? [];

$formatDate = static function ($dateValue) {
    if (empty($dateValue)) {
        return '-';
    }

    $timestamp = strtotime((string) $dateValue);
    if (!$timestamp) {
        return (string) $dateValue;
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
        12 => 'Desember',
    ];

    return date('d', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
};

$numberToWords = null;
$numberToWords = static function ($number) use (&$numberToWords) {
    $number = (int) round($number);
    $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    if ($number < 12) {
        return $words[$number];
    }
    if ($number < 20) {
        return $numberToWords($number - 10) . ' belas';
    }
    if ($number < 100) {
        return $numberToWords((int) floor($number / 10)) . ' puluh' . (($number % 10 !== 0) ? ' ' . $numberToWords($number % 10) : '');
    }
    if ($number < 200) {
        return 'seratus' . (($number - 100 > 0) ? ' ' . $numberToWords($number - 100) : '');
    }
    if ($number < 1000) {
        return $numberToWords((int) floor($number / 100)) . ' ratus' . (($number % 100 !== 0) ? ' ' . $numberToWords($number % 100) : '');
    }
    if ($number < 2000) {
        return 'seribu' . (($number - 1000 > 0) ? ' ' . $numberToWords($number - 1000) : '');
    }
    if ($number < 1000000) {
        return $numberToWords((int) floor($number / 1000)) . ' ribu' . (($number % 1000 !== 0) ? ' ' . $numberToWords($number % 1000) : '');
    }
    if ($number < 1000000000) {
        return $numberToWords((int) floor($number / 1000000)) . ' juta' . (($number % 1000000 !== 0) ? ' ' . $numberToWords($number % 1000000) : '');
    }
    if ($number < 1000000000000) {
        return $numberToWords((int) floor($number / 1000000000)) . ' miliar' . (($number % 1000000000 !== 0) ? ' ' . $numberToWords($number % 1000000000) : '');
    }

    return $numberToWords((int) floor($number / 1000000000000)) . ' triliun' . (($number % 1000000000000 !== 0) ? ' ' . $numberToWords($number % 1000000000000) : '');
};

$totalNominal = 0;
foreach ($poItems as $item) {
    $totalNominal += (float) ($item['total_nominal_detail'] ?? 0);
}
$dpp = ($totalNominal * 11) / 12;
$ppn = $totalNominal * 0.12;
$grandTotal = $totalNominal;
$vendorName = $poHeader['nama_pabrik'] ?? '-';
$vendorAddress = trim((string) ($poHeader['lokasi_pabrik'] ?? ''));
$vendorPic = trim((string) ($poHeader['pic_pabrik'] ?? ''));
$vendorPhone = trim((string) ($poHeader['tlp_pabrik'] ?? ''));
$sistemPembayaran = trim((string) ($poHeader['harga_system_pembayaran'] ?? ''));
$jenisPembayaran = trim((string) ($poHeader['detail_jenis_pembayaran'] ?? ''));
$waktuPengiriman = trim((string) ($poHeader['waktu_pengiriman_material'] ?? ''));
$keteranganPo = trim((string) ($poHeader['keterangan_po'] ?? ''));
$terbilang = ucfirst(trim($numberToWords($grandTotal))) . ' rupiah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print PO <?= htmlspecialchars((string) ($poHeader['nomor_po_pabrik'] ?? ''), ENT_QUOTES) ?></title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            margin: 0;
            font-size: 12px;
        }
        .po-print {
            max-width: 190mm;
            margin: 0 auto;
        }
        .po-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-bottom: 2px solid #f97316;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .po-header__logo {
            width: 110px;
            flex: 0 0 110px;
        }
        .po-header__logo img {
            max-width: 100%;
            height: auto;
        }
        .po-header__meta h1 {
            margin: 0;
            font-size: 18px;
            color: #f97316;
            font-weight: 800;
            line-height: 1.2;
        }
        .po-header__meta p {
            margin: 2px 0;
            font-size: 10px;
            line-height: 1.45;
        }
        .po-doc-head {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .po-doc-head td {
            padding: 2px 0;
            vertical-align: top;
        }
        .po-doc-head .label { width: 74px; }
        .po-doc-head .separator { width: 10px; }
        .po-section {
            margin-bottom: 12px;
        }
        .po-section p {
            margin: 2px 0;
            line-height: 1.5;
        }
        .po-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .po-items th,
        .po-items td {
            border: 1px solid #374151;
            padding: 6px 5px;
            vertical-align: top;
        }
        .po-items th {
            text-align: center;
            background: #f8fafc;
            font-weight: 700;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .po-summary {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 0;
        }
        .po-summary td {
            border: 1px solid #374151;
            padding: 4px 6px;
        }
        .po-terbilang {
            border: 1px solid #374151;
            border-top: 0;
            padding: 8px 10px;
            font-style: italic;
            font-weight: 700;
        }
        .po-terms {
            margin-top: 10px;
        }
        .po-terms table {
            border-collapse: collapse;
        }
        .po-terms td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .po-signature {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }
        .po-signature td {
            width: 50%;
            vertical-align: top;
            padding-top: 12px;
        }
        .po-sign-space {
            height: 54px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="po-print">
        <div class="po-header">
            <div class="po-header__logo">
                <img src="<?= base_url('assets/dist/img/logotkmsolid.png') ?>" alt="PT TKM">
            </div>
            <div class="po-header__meta">
                <h1>PT. TECHNOLOGY KARYA MANDIRI</h1>
                <p>General Contractor &amp; Supplier</p>
                <p>Rukan Puri Botanical Residence Blok H.9 No. 22-23, Jl. Raya Meruya Selatan - Joglo, Jakarta 11640</p>
                <p>Telp. (021) 58905600, 58905700, 585552 (Hunting) Email: info@tkm.co.id</p>
                <p>@technologykaryamandiriofficial | www.tkm.co.id</p>
            </div>
        </div>

        <table class="po-doc-head">
            <tr>
                <td class="label">Nomor</td>
                <td class="separator">:</td>
                <td><?= htmlspecialchars((string) ($poHeader['nomor_po_pabrik'] ?? '-'), ENT_QUOTES) ?></td>
                <td class="text-right"><?= !empty($poHeader['tanggal_po_pabrik']) ? 'Jakarta, ' . $formatDate($poHeader['tanggal_po_pabrik']) : '' ?></td>
            </tr>
            <tr>
                <td class="label">Klasifikasi</td>
                <td class="separator">:</td>
                <td colspan="2">Penting</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="separator">:</td>
                <td colspan="2"><em>Purchase Order (PO)</em></td>
            </tr>
        </table>

        <div class="po-section">
            <p>Kepada Yth,</p>
            <p><strong><?= htmlspecialchars($vendorName, ENT_QUOTES) ?></strong></p>
            <p><?= nl2br(htmlspecialchars($vendorAddress !== '' ? $vendorAddress : '-', ENT_QUOTES)) ?></p>
            <br>
            <p>PIC&nbsp;&nbsp;&nbsp;&nbsp;: <?= htmlspecialchars($vendorPic !== '' ? $vendorPic : '-', ENT_QUOTES) ?></p>
            <p>Telp&nbsp;&nbsp;&nbsp;: <?= htmlspecialchars($vendorPhone !== '' ? $vendorPhone : '-', ENT_QUOTES) ?></p>
            <br>
            <p>Dengan Hormat,</p>
            <p>Bersama ini kami sampaikan pemesanan barang dengan spesifikasi sebagai berikut :</p>
        </div>

        <table class="po-items">
            <thead>
                <tr>
                    <th style="width:36px;">No</th>
                    <th>Jenis Material</th>
                    <th style="width:72px;">Volume</th>
                    <th style="width:58px;">Satuan</th>
                    <th style="width:90px;">Harga (Rp)</th>
                    <th style="width:110px;">Total Harga (Rp)</th>
                    <th style="width:165px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($poItems as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars((string) ($item['nama_item'] ?? '-'), ENT_QUOTES) ?></td>
                        <td class="text-right"><?= number_format((float) ($item['qty_po'] ?? 0), 0, ',', '.') ?></td>
                        <td class="text-center"><?= htmlspecialchars((string) ($item['satuan_item'] ?? '-'), ENT_QUOTES) ?></td>
                        <td class="text-right"><?= number_format((float) ($item['harga_item'] ?? 0), 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format((float) ($item['total_nominal_detail'] ?? 0), 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars((string) ($item['nomor_purchase_request'] ?? ($item['nomor_nota_dinas'] ?? '-')), ENT_QUOTES) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" style="border:none;padding:0;"></td>
                    <td colspan="3" style="padding:0;">
                        <table class="po-summary">
                            <tr>
                                <td>Sub Total</td>
                                <td class="text-right"><?= number_format($totalNominal, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Dpp</td>
                                <td class="text-right"><?= number_format($dpp, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>PPn 12 %</td>
                                <td class="text-right"><?= number_format($ppn, 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td class="text-right"><strong><?= number_format($grandTotal, 0, ',', '.') ?></strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="po-terbilang">Terbilang : # <?= htmlspecialchars($terbilang, ENT_QUOTES) ?> #</div>

        <div class="po-terms">
            <p>Berikut syarat dan ketentuan :</p>
            <table>
                <tr>
                    <td>1</td>
                    <td>Harga</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($sistemPembayaran !== '' ? $sistemPembayaran : '-', ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Pembayaran</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($jenisPembayaran !== '' ? $jenisPembayaran : '-', ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Kondisi Material</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($keteranganPo !== '' ? $keteranganPo : 'Sesuai dengan spesifikasi dan standar PT. TKM', ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Waktu Pengiriman Material</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($waktuPengiriman !== '' ? $waktuPengiriman : 'Sesuai dengan kesepakatan', ENT_QUOTES) ?></td>
                </tr>
            </table>
        </div>

        <div class="po-section">
            <p>Demikian kami sampaikan atas perhatian dan kerjasamanya diucapkan terima kasih.</p>
        </div>

        <table class="po-signature">
            <tr>
                <td>
                    <p>Hormat kami,</p>
                    <p>PT. Technology Karya Mandiri</p>
                    <div class="po-sign-space"></div>
                    <p><strong>Ida Isnaeni</strong><br>Direktur</p>
                </td>
                <td>
                    <p>Menyetujui,</p>
                    <p><strong><?= htmlspecialchars($vendorName, ENT_QUOTES) ?></strong></p>
                    <div class="po-sign-space"></div>
                    <p>__________________________</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
