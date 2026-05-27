<?php
$periodeData = $periode ?? ($getDetailSoPeriode[0] ?? []);
$lokasiData = $lokasi ?? [];
$baData = $existingBA ?? [];
$baItems = $existingBAItems ?? $discrepancyItems ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BA Stock Opname</title>
  <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png') ?>">
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png') ?>">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            margin: 24px;
        }

        .print-wrap {
            max-width: 1100px;
            margin: 0 auto;
        }

        .title {
            text-align: center;
            margin-bottom: 24px;
        }

        .title h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .title p {
            margin: 6px 0 0;
            color: #4b5563;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .meta td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 220px;
            font-weight: 700;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        table.report th,
        table.report td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
        }

        table.report th {
            background: #e2e8f0;
            text-transform: uppercase;
        }

        .closing {
            line-height: 1.65;
            margin-bottom: 40px;
        }

        .sign-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .sign-box {
            text-align: center;
            font-size: 12px;
        }

        .sign-space {
            height: 86px;
        }

        .print-action {
            margin-bottom: 18px;
        }

        @media print {
            .print-action {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="print-action">
            <button onclick="window.print()">Print BA</button>
        </div>

        <div class="title">
            <h1>Berita Acara Kronologi Stock Opname</h1>
            <p>Nomor BA: <?= htmlspecialchars((string) ($baData['nomor_ba'] ?? '-')) ?></p>
        </div>

        <table class="meta">
            <tr>
                <td>Periode Stock Opname</td>
                <td>: <?= htmlspecialchars(trim((string) (($periodeData['sop_bulan'] ?? '-') . ' ' . ($periodeData['sop_tahun'] ?? '-')))) ?></td>
            </tr>
            <tr>
                <td>Tanggal BA</td>
                <td>: <?= !empty($baData['ba_tanggal']) ? date('d M Y', strtotime($baData['ba_tanggal'])) : '-' ?></td>
            </tr>
            <tr>
                <td>Regional</td>
                <td>: <?= htmlspecialchars((string) ($lokasiData['regional_lokasi_gudang'] ?? '-')) ?></td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>: <?= htmlspecialchars((string) ($lokasiData['provinsi_lokasi_gudang'] ?? '-')) ?></td>
            </tr>
            <tr>
                <td>Lokasi Gudang / Area</td>
                <td>: <?= htmlspecialchars((string) ($lokasiData['kota_lokasi_gudang'] ?? '-')) ?></td>
            </tr>
        </table>

        <div class="closing">
            Pada periode stock opname di atas, telah dilakukan pengecekan stok aktual di area dan ditemukan beberapa item yang memiliki selisih dibandingkan stok aplikasi. Rincian item selisih berikut digunakan sebagai dasar kronologi dan tindak lanjut approval adjustment material.
        </div>

        <table class="report">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Project</th>
                    <th>Kode Item</th>
                    <th>Nama Item</th>
                    <th>Satuan</th>
                    <th>Stok Aplikasi</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                    <th>Remarks / Kronologi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($baItems as $index => $item) { ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars((string) ($item['project_item'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['id_kode_item'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['nama_item'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['satuan_item'] ?? '-')) ?></td>
                        <td><?= number_format((float) ($item['stok_aplikasi'] ?? 0), 0, ',', '.') ?></td>
                        <td><?= number_format((float) ($item['stok_fisik'] ?? 0), 0, ',', '.') ?></td>
                        <td><?= number_format((float) ($item['selisih'] ?? 0), 0, ',', '.') ?></td>
                        <td><?= nl2br(htmlspecialchars((string) (($item['kronologi'] ?? '') !== '' ? $item['kronologi'] : ($item['remarks'] ?? '-')))) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="closing">
            Demikian berita acara ini dibuat sebagai dokumen pendukung proses stock opname dan sinkronisasi material di aplikasi. Setelah dokumen ini ditandatangani basah, file signed akan diupload ke sistem untuk proses approval dan adjustment.
        </div>

        <div class="sign-grid">
            <div class="sign-box">
                Mengetahui,<br>Area / PIC Gudang
                <div class="sign-space"></div>
                (........................................)
            </div>
            <div class="sign-box">
                Diperiksa,<br>Supervisor / Koordinator
                <div class="sign-space"></div>
                (........................................)
            </div>
            <div class="sign-box">
                Disetujui,<br>HO Logistik
                <div class="sign-space"></div>
                (........................................)
            </div>
        </div>
    </div>
</body>
</html>
