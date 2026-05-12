<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PKS - Kontrak Induk Mitra Pelaksana</title>
  <style>
    @page { size: A4; margin: 20mm; }
    body { font-family: "Times New Roman", serif; font-size: 12pt; color: #111; line-height: 1.45; }
    .center { text-align: center; }
    .title { font-weight: 700; text-transform: uppercase; }
    .mt-12 { margin-top: 12px; }
    .mt-20 { margin-top: 20px; }
    .pasal { text-align: center; font-weight: 700; margin: 14px 0 6px; }
    .indent { margin-left: 26px; }
    table.meta { width: 100%; border-collapse: collapse; }
    table.meta td { vertical-align: top; padding: 2px 0; }
  </style>
</head>
<body onload="window.print()">
<?php
$nomorPks = (string) ($pks['nomor_pks'] ?? '-');
$pic = (string) ($pks['pic_pks'] ?? '-');
$noKtp = (string) ($pks['no_ktp'] ?? '-');
$alamat = (string) ($pks['alamat_ktp'] ?? '-');
$noTelp = (string) ($pks['no_telp'] ?? '-');
$email = (string) ($pks['email_pic'] ?? '-');
$jenis = strtoupper((string) ($pks['jenis_pks'] ?? 'MPL'));
$toc = (string) ($pks['toc_pks'] ?? '-');

$jabatanMap = [
  'MPL' => 'Mitra Pelaksana',
  'MDR' => 'Mandor',
  'PKS' => 'Mitra Kontrak Putus',
];
$jabatan = $jabatanMap[$jenis] ?? 'Mitra Pelaksana';

$ts = !empty($pks['tanggal_pks']) ? strtotime($pks['tanggal_pks']) : time();
$hariMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$bulanMap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

$hari = $hariMap[date('l', $ts)] ?? date('l', $ts);
$bulan = $bulanMap[(int) date('n', $ts)] ?? date('F', $ts);
$dd = (int) date('d', $ts);
$yyyy = (int) date('Y', $ts);

$toWords = function ($n) use (&$toWords) {
  $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
  $n = (int) $n;
  if ($n < 12) return $words[$n];
  if ($n < 20) return $toWords($n - 10) . ' belas';
  if ($n < 100) return $toWords(intdiv($n, 10)) . ' puluh ' . $toWords($n % 10);
  if ($n < 200) return 'seratus ' . $toWords($n - 100);
  if ($n < 1000) return $toWords(intdiv($n, 100)) . ' ratus ' . $toWords($n % 100);
  if ($n < 2000) return 'seribu ' . $toWords($n - 1000);
  if ($n < 1000000) return $toWords(intdiv($n, 1000)) . ' ribu ' . $toWords($n % 1000);
  return (string) $n;
};

$tanggalTerbilang = ucwords(trim(preg_replace('/\s+/', ' ', $toWords($dd))));
$tahunTerbilang = ucwords(trim(preg_replace('/\s+/', ' ', $toWords($yyyy))));
$tglNumeric = date('d-m-Y', $ts);
?>

<div class="center title">Perjanjian Kontrak Kerja</div>
<div class="center title">Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO)</div>
<div class="center title">Antara</div>
<div class="center title">PT. Technology Karya Mandiri</div>
<div class="center title">Dengan</div>
<div class="center title"><?= htmlspecialchars($pic, ENT_QUOTES) ?></div>
<div class="center mt-12">Nomor : <?= htmlspecialchars($nomorPks, ENT_QUOTES) ?></div>

<p class="mt-20">Pada hari ini, <?= htmlspecialchars($hari, ENT_QUOTES) ?> tanggal <?= htmlspecialchars($tanggalTerbilang, ENT_QUOTES) ?> bulan <?= htmlspecialchars($bulan, ENT_QUOTES) ?> tahun <?= htmlspecialchars($tahunTerbilang, ENT_QUOTES) ?> (<?= htmlspecialchars($tglNumeric, ENT_QUOTES) ?>) bertempat di Kantor Pusat PT. Technology Karya Mandiri, antara pihak-pihak:</p>

<p><strong>PT. TECHNOLOGY KARYA MANDIRI</strong>, suatu badan usaha berbentuk Perseroan Terbatas, selanjutnya dalam Perjanjian ini disebut sebagai <strong>TKM</strong>.</p>

<p><strong><?= htmlspecialchars(strtoupper($pic), ENT_QUOTES) ?></strong> sebagai <?= htmlspecialchars(strtoupper($jabatan), ENT_QUOTES) ?> dengan nomor Kartu Tanda Penduduk (KTP) atas nama <?= htmlspecialchars($pic, ENT_QUOTES) ?> nomor <?= htmlspecialchars($noKtp, ENT_QUOTES) ?> yang beralamat di <?= htmlspecialchars($alamat, ENT_QUOTES) ?>, selanjutnya dalam Perjanjian ini disebut <strong>MITRA</strong>.</p>

<p>TKM dan MITRA secara bersama-sama disebut “Para Pihak” dan secara sendiri-sendiri disebut juga “Pihak”.</p>

<p>Dengan terlebih dahulu mempertimbangkan hal-hal sebagai berikut:</p>
<div class="indent">a. Bahwa TKM berkehendak untuk mengadakan Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO).</div>
<div class="indent">b. Bahwa MITRA sanggup untuk melaksanakan pekerjaan dimaksud.</div>
<div class="indent">c. Bahwa Para Pihak telah melakukan pembahasan harga satuan pekerjaan.</div>

<div class="pasal">PASAL 1<br>DEFINISI</div>
<p>Perjanjian adalah kesepakatan kerja sama Pemasangan OSP-FO dengan skema kontrak harga satuan. Pelaksanaan transaksinya dilakukan melalui penerbitan Surat Perintah Kerja (SPK).</p>

<div class="pasal">PASAL 2<br>SPESIFIKASI TEKNIS</div>
<p>MITRA menjamin semua pekerjaan dilaksanakan sesuai spesifikasi teknis yang ditetapkan TKM dan dituangkan dalam dokumen pelaksanaan pekerjaan.</p>

<div class="pasal">PASAL 3<br>RUANG LINGKUP PEKERJAAN</div>
<p>TKM menyerahkan pekerjaan OSP-FO kepada MITRA sesuai ruang lingkup pada SPK, dan MITRA menerima serta sanggup melaksanakan pekerjaan dimaksud sampai selesai.</p>

<div class="pasal">PASAL 4<br>MASA BERLAKU KONTRAK INDUK</div>
<p>Masa berlaku perjanjian ini mengikuti TOC PKS: <strong><?= htmlspecialchars($toc, ENT_QUOTES) ?></strong>.</p>

<div class="pasal">PASAL 5<br>KETENTUAN PENUTUP</div>
<p>Hal-hal yang belum diatur dalam perjanjian ini akan ditetapkan lebih lanjut dalam SPK dan/atau adendum yang disepakati Para Pihak.</p>

<div class="mt-20">
  <table class="meta">
    <tr><td style="width: 80px;">Nama</td><td style="width: 10px;">:</td><td><?= htmlspecialchars(strtoupper($pic), ENT_QUOTES) ?></td></tr>
    <tr><td>Jabatan</td><td>:</td><td><?= htmlspecialchars($jabatan, ENT_QUOTES) ?></td></tr>
    <tr><td>Alamat</td><td>:</td><td><?= htmlspecialchars($alamat, ENT_QUOTES) ?></td></tr>
    <tr><td>No.Telp.</td><td>:</td><td><?= htmlspecialchars($noTelp, ENT_QUOTES) ?></td></tr>
    <tr><td>Email</td><td>:</td><td><?= htmlspecialchars($email, ENT_QUOTES) ?></td></tr>
  </table>
</div>

<br><br>
<table style="width:100%; border-collapse: collapse;">
  <tr>
    <td style="width:50%; text-align:center; vertical-align:top;">
      PT. TECHNOLOGY KARYA MANDIRI<br>
      <br><br><br><br>
      __________________________
    </td>
    <td style="width:50%; text-align:center; vertical-align:top;">
      MITRA PELAKSANA<br>
      <br><br><br><br>
      <?= htmlspecialchars(strtoupper($pic), ENT_QUOTES) ?>
    </td>
  </tr>
</table>

</body>
</html>
