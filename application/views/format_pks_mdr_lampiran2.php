<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PKS - MDR Lampiran 2</title>
  <style>
    @page { size: A4; margin: 20mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5pt; color: #111; line-height: 1.5; margin: 0; padding: 0; }
    p { margin: 0 0 6px; text-align: justify; }
    .center { text-align: center; }
    .title { font-weight: 700; text-transform: uppercase; font-size: 13pt; text-decoration: underline; margin-bottom: 14px; }
    .meta { width: 100%; border-collapse: collapse; margin: 4px 0 10px; }
    .meta td { padding: 1px 0; vertical-align: top; line-height: 1.3; }
    .meta .label { width: 130px; white-space: nowrap; }
    .meta .sep { width: 12px; }
    .points { margin: 4px 0 10px; }
    .point { display: flex; align-items: flex-start; margin: 0 0 8px; }
    .point-no { min-width: 20px; font-weight: normal; }
    .point-body { flex: 1; text-align: justify; }
    .sign-wrap { margin-top: 24px; display: flex; justify-content: flex-end; }
    .sign-right { width: 46%; text-align: center; }
    .sign-right p { text-align: center; margin-bottom: 4px; }
    .materai-box { border: 1px dashed #aaa; width: 80px; height: 80px; margin: 8px auto 4px; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #888; }
    .spacer { height: 30px; }
    .name { font-weight: 700; text-decoration: underline; text-transform: uppercase; }
    .role { font-weight: 700; }
    b { font-weight: 700; }
  </style>
</head>
<body onload="window.print()">
<?php
$pic         = trim((string) ($pks['pic_pks']      ?? '-'));
$picUpper    = strtoupper($pic);
$alamat      = trim((string) ($pks['alamat_ktp']   ?? '-'));
$noTelp      = trim((string) ($pks['no_telp']      ?? '-'));
$noKtp       = trim((string) ($pks['no_ktp']       ?? '-'));
$ttl         = trim((string) ($pks['ttl']          ?? '-'));   // Tempat, Tanggal Lahir
$nomorKontrak = trim((string) ($pks['nomor_pks']   ?? '-'));

$ts       = !empty($pks['tanggal_pks']) ? strtotime($pks['tanggal_pks']) : time();
$bulanMap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
             7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$tgl = (int) date('j', $ts);
$bln = $bulanMap[(int) date('n', $ts)] ?? date('F', $ts);
$thn = (int) date('Y', $ts);
?>

<p class="center title">SURAT PERNYATAAN</p>

<p>Dalam rangka penugasan saudara bekerja sebagai Pelaksana sebagai Mitra/Mandor di Proyek OSP-FO PT. Technology Karya Mandiri, saudara perlu menandatangani suatu surat pernyataan dalam bentuk seperti dibawah ini. Surat pernyataan ini akan disimpan untuk mengingatkan saudara pada kewajiban yang berkaitan dengan pernyataan yang saudara tandatangani ini.</p>

<p>Yang bertanda tangan dibawah ini :</p>

<table class="meta">
  <tr>
    <td class="label">Nama</td>
    <td class="sep">:</td>
    <td><b><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></b></td>
  </tr>
  <tr>
    <td class="label">TTL</td>
    <td class="sep">:</td>
    <td><?= htmlspecialchars($ttl, ENT_QUOTES) ?></td>
  </tr>
  <tr>
    <td class="label">Alamat</td>
    <td class="sep">:</td>
    <td><?= htmlspecialchars($alamat, ENT_QUOTES) ?></td>
  </tr>
  <tr>
    <td class="label">Nomor Telepon</td>
    <td class="sep">:</td>
    <td><?= htmlspecialchars($noTelp, ENT_QUOTES) ?></td>
  </tr>
  <tr>
    <td class="label">Nomor Kontrak</td>
    <td class="sep">:</td>
    <td><?= htmlspecialchars($nomorKontrak, ENT_QUOTES) ?></td>
  </tr>
</table>

<p>Dengan ini menyatakan dan bertanggung jawab penuh atas hal-hal sebagai berikut :</p>

<div class="points">
  <div class="point">
    <div class="point-no">1.</div>
    <div class="point-body">
      Bahwa, sehubungan dengan <b>Perjanjian Kontrak Kerja Nomor : <?= htmlspecialchars($nomorKontrak, ENT_QUOTES) ?>
      Tanggal <?= $tgl ?> <?= htmlspecialchars($bln, ENT_QUOTES) ?> <?= $thn ?></b> antara saya sebagai pelaksana Mitra/Mandor Proyek OSP-FO dengan
      <b>PT. Technology Karya Mandiri</b> selanjutnya disebut &ldquo;Perusahaan&rdquo;, saya berjanji untuk merahasiakan
      dan tidak mengungkapkan informasi rahasia perusahaan kepada orang lain, termasuk rencana-rencana usaha,
      metode-metode dan strategi-strategi proyek, biaya dan hak milik lainnya menyangkut para pihak, klien dan
      vendor perusahaan, baik selama masa penugasan saya pada perusahaan maupun sesudah berakhirnya masa
      penugasan saya pada perusahaan.
    </div>
  </div>
  <div class="point">
    <div class="point-no">2.</div>
    <div class="point-body">Seluruh tenaga kerja yang ada di lokasi project menjadi tanggung jawab Mitra/ Mandor.</div>
  </div>
  <div class="point">
    <div class="point-no">3.</div>
    <div class="point-body">
      Seluruh tenaga kerja yang ada di bawah naungan Mitra/ Mandor apabila terjadi kecelakaan kerja, 100% menjadi
      <b>resiko</b> dan <b>tanggungan</b> Mitra/ Mandor dan tidak ada tuntutan apapun terhadap perusahaan.
    </div>
  </div>
  <div class="point">
    <div class="point-no">4.</div>
    <div class="point-body">Apabila ternyata saya melanggar surat pernyataan ini, baik secara langsung maupun tidak langsung, maka saya bersedia untuk mempertanggungjawabkannya dan menempuh prosedur hukum yang berlaku.</div>
  </div>
</div>

<p>Demikian Surat Pernyataan ini saya buat dalam keadaan sadar, tanpa paksaan dari pihak manapun dan untuk dipergunakan sebagaimana mestinya.</p>

<div class="sign-wrap">
  <div class="sign-right">
    <p>Jakarta, <?= $tgl ?> <?= htmlspecialchars($bln, ENT_QUOTES) ?> <?= $thn ?></p>
    <p>&nbsp;</p>
    <p>Materai - 10000</p>
    <div class="spacer"></div>
    <p class="name"><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></p>
    <p class="role">Mandor</p>
  </div>
</div>

</body>
</html>