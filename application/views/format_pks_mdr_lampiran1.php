<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>PKS MDR Lampiran 1</title>
  <style>
    @page { size: A4; margin: 16mm 14mm 14mm 14mm; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; line-height: 1.15; margin: 0; padding: 0; }
    .page { display: flex; flex-direction: column; justify-content: space-between; min-height: 257mm; box-sizing: border-box; page-break-after: always; break-after: page; }
    .page:last-child { page-break-after: auto; break-after: auto; }
    .page-content { flex: 1; }
    .title { text-align: center; font-weight: 700; font-size: 12pt; text-transform: uppercase; line-height: 1.15; }
    .line { border-top: 1px solid #000; margin: 8px 0 6px; }
    .nomor { text-align: center; font-weight: 700; font-size: 10pt; margin-bottom: 6px; }
    p { margin: 0 0 4px; text-align: justify; }
    .lead { margin-bottom: 6px; }
    .bi { font-weight: 700; font-style: italic; }
    .b { font-weight: 700; }
    .roman { width: 100%; margin: 0 0 5px; display: flex; align-items: flex-start; }
    .roman-no { width: 24px; min-width: 24px; font-weight: 700; }
    .roman-body { flex: 1; text-align: justify; }
    .alpha { width: 100%; margin: 0 0 2px; display: flex; align-items: flex-start; }
    .alpha-no { width: 22px; min-width: 22px; }
    .alpha-body { flex: 1; text-align: justify; }
    .section-center { text-align: center; font-weight: 700; margin: 6px 0 0; }
    .section-center + .section-center { margin-top: 0; margin-bottom: 0px; }
    .info-table { border-collapse: collapse; width: 100%; margin: 0 0 2px; font-size: 10pt; }
    .info-table td { padding: 0 2px 1px 0; vertical-align: top; line-height: 1.15; }
    .info-label { width: 90px; white-space: nowrap; }
    .info-sep { width: 10px; }

    .footer {
      font-size: 9px;
      margin-top: 8px;
      page-break-inside: avoid;
      break-inside: avoid;
    }
    .footer-line { border-top: 1px solid #000; padding-top: 3px; width: 100%; min-height: 27px; box-sizing: border-box; display: table; table-layout: fixed; page-break-inside: avoid; break-inside: avoid; }
    .footer-page { display: table-cell; width: 16px; vertical-align: top; padding-right: 4px; }
    .page-box { width: 11px; height: 11px; border: 1px solid #000; text-align: center; line-height: 11px; font-size: 9px; }
    .footer-text { display: table-cell; font-style: italic; vertical-align: top; white-space: nowrap; line-height: 1; padding-top: 2px; }
    .footer-sign { display: table-cell; width: 106px; height: 24px; text-align: right; vertical-align: top; padding-left: 8px; }
    .sign-box { display: inline-table; width: 104px; height: 34px; border: 1px solid #000; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; break-inside: avoid; }
    .sign-box div { display: table-cell; border-left: 1px solid #000; text-align: center; font-weight: 700; font-size: 8px; vertical-align: top; padding-top: 2px; }
    .sign-box div:first-child { border-left: 0; }
  </style>
</head>
<body onload="window.print()">>
<?php
$nomorPks = (string) ($pks['nomor_pks'] ?? '-');
$pic = trim((string) ($pks['pic_pks'] ?? '-'));
$picUpper = strtoupper($pic);
$noKtp = trim((string) ($pks['no_ktp'] ?? '-'));
$alamat = trim((string) ($pks['alamat_ktp'] ?? '-'));
$noTelp = trim((string) ($pks['no_telp'] ?? '-'));
$emailPic = trim((string) ($pks['email_pic'] ?? '-'));

$ts = !empty($pks['tanggal_pks']) ? strtotime($pks['tanggal_pks']) : time();
$hariMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$bulanMap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$hari = $hariMap[date('l', $ts)] ?? date('l', $ts);
$bulan = $bulanMap[(int) date('n', $ts)] ?? date('F', $ts);
$dd = (int) date('d', $ts);
$yyyy = (int) date('Y', $ts);
$tglNumeric = date('d-m-Y', $ts);

$toWords = function ($n) use (&$toWords) {
    $w = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    $n = (int) $n;
    if ($n < 12) return $w[$n];
    if ($n < 20) return $toWords($n - 10) . ' belas';
    if ($n < 100) return $toWords(intdiv($n, 10)) . ' puluh ' . $toWords($n % 10);
    if ($n < 200) return 'seratus ' . $toWords($n - 100);
    if ($n < 1000) return $toWords(intdiv($n, 100)) . ' ratus ' . $toWords($n % 100);
    if ($n < 2000) return 'seribu ' . $toWords($n - 1000);
    if ($n < 1000000) return $toWords(intdiv($n, 1000)) . ' ribu ' . $toWords($n % 1000);
    if ($n < 1000000000) return $toWords(intdiv($n, 1000000)) . ' juta ' . $toWords($n % 1000000);
    return (string) $n;
};

$tanggalTerbilang = ucwords(trim(preg_replace('/\s+/', ' ', $toWords($dd))));
$tahunTerbilang = ucwords(trim(preg_replace('/\s+/', ' ', $toWords($yyyy))));
?>

<div class="page">
  <div class="title">PERJANJIAN KONTRAK KERJA</div>
  <div class="title">PEKERJAAN PEMASANGAN OUTSIDE PLAN FIBER OPTIC (OSP-FO)</div>
  <div class="title">ANTARA</div>
  <div class="title">PT. TECHNOLOGY KARYA MANDIRI</div>
  <div class="title">DENGAN</div>
  <div class="title"><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></div>

  <div class="line"></div>
  <div class="nomor">Nomor : <?= htmlspecialchars($nomorPks, ENT_QUOTES) ?></div>

  <p class="lead">Pada hari ini, <span class="bi"><?= htmlspecialchars($hari, ENT_QUOTES) ?></span> tanggal <span class="bi"><?= htmlspecialchars($tanggalTerbilang, ENT_QUOTES) ?></span> bulan <span class="bi"><?= htmlspecialchars($bulan, ENT_QUOTES) ?></span> tahun <span class="bi"><?= htmlspecialchars($tahunTerbilang, ENT_QUOTES) ?></span> <span class="b">(<?= htmlspecialchars($tglNumeric, ENT_QUOTES) ?>)</span> bertempat di Kantor Pusat PT. Technology Karya Mandiri, Rukan Puri Botanical Residence Blok H.9 No.22/23 RT 007 RW.001, Joglo - Jakarta Barat, antara pihak-pihak:</p>

  <div class="roman">
    <div class="roman-no">I.</div>
    <div class="roman-body"><span class="bi">PT. TECHNOLOGY KARYA MANDIRI</span>, suatu badan usaha berbentuk Perseroan Terbatas yang didirikan berdasarkan Akta Notaris Periasman Effendi, S.H., di Tangerang Nomor 8, tanggal 18 Maret 2009, yang telah mengalami perubahan dengan Akta Notaris Melyani Noor Shandra, S.H., di Jakarta dalam Berita Acara Nomor 18 tanggal 2 Juni 2010 di Jakarta, beralamat di Rukan Puri Botanical Residence Blok H.8 No.22 RT 007 RW.001, Joglo - Jakarta Barat, dalam hal ini diwakili secara sah oleh <span class="b">IDA ISNAENI</span> jabatan Direktur yang selanjutnya dalam Perjanjian ini disebut sebagai <span class="b">TKM</span>.</div>
  </div>

  <div class="roman">
    <div class="roman-no">II.</div>
    <div class="roman-body"><span class="bi"><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></span> sebagai Pelaksana (MITRA PELAKSANA) dengan nomor Kartu Tanda Penduduk (KTP) atas nama <span class="bi"><?= htmlspecialchars($pic, ENT_QUOTES) ?></span> nomor <span class="bi"><?= htmlspecialchars($noKtp, ENT_QUOTES) ?></span> yang beralamat di <span class="bi"><?= htmlspecialchars($alamat, ENT_QUOTES) ?></span>, dalam hal ini mewakili diri sendiri secara sah sebagai Pelaksana (<span class="b">MITRA PELAKSANA</span>) yang selanjutnya dalam Perjanjian ini disebut <span class="b">MITRA</span>.</div>
  </div>

  <p><span class="b">TKM</span> dan <span class="b">MITRA</span> secara bersama-sama disebut <span class="b">Para Pihak</span> dan secara sendiri-sendiri disebut juga <span class="b">Pihak</span></p>

  <p>Dengan terlebih dahulu mempertimbangkan hal-hal sebagai berikut :</p>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Bahwa <span class="b">TKM</span> berkehendak untuk mengadakan Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO);</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Bahwa <span class="b">MITRA</span> adalah (Pelaksana) kerja TKM dan sanggup untuk melaksanakan Pekerjaan dimaksud;</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Bahwa Para Pihak telah melakukan pembahasan Harga Satuan Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO).</div></div>

  <p>Berdasarkan pertimbangan tersebut di atas telah dicapai kata sepakat, dengan ini <span class="b">TKM</span> dan <span class="b">MITRA</span> menyatakan saling mengikatkan diri dalam Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO) dengan ketentuan dan syarat-syarat sebagai berikut:</p>

  <div class="section-center">PASAL 1</div>
  <div class="section-center">DEFINISI</div>

  <p>Kecuali ditentukan lain dalam hubungan kalimat pada Pasal yang bersangkutan dalam Perjanjian ini, yang dimaksud dengan hal-hal sebagai berikut:</p>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body"><span class="b">Perjanjian</span> adalah Kesepakatan kerja sama Pemasangan Outside Plan Fiber Optic (OSP FO) dengan skema Kontrak Harga Satuan tanpa adanya total nilai perjanjian, yang pelaksanaan transaksinya dilakukan dengan penerbitan Surat Perintah Kerja.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body"><span class="b">Surat Perintah Kerja (SPK)</span> adalah perintah tertulis yang diterbitkan dan ditandatangani oleh <span class="b">TKM</span> dan <span class="b">MITRA</span>, sebagai dasar pelaksanaan pekerjaan dan pembayarannya.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">1</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body"><span class="b">Pekerjaan</span> adalah Pemasangan Outside Plan Fiber Optic (OSP-FO) yang dilaksanakan oleh <span class="b">MITRA</span> atas dasar Tanggung Jawab Tunggal.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body"><span class="b">Supervisor</span> adalah petugas yang ditunjuk sebagai pengawas pelaksanaan Pekerjaan di lapangan, dan bertanggung jawab kepada Site Manager dan Regional Project Manager.</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body"><span class="b">Lokasi</span> adalah tempat dimana Pekerjaan harus dilaksanakan, diselesaikan dan diserahkan kepada <span class="b">TKM</span> dalam keadaan selesai seluruhnya.</div></div>
  <div class="alpha"><div class="alpha-no">f.</div><div class="alpha-body"><span class="b">Spesifikasi Teknis</span> adalah persyaratan teknis yang dikeluarkan dan ditetapkan oleh <span class="b">TKM</span> yang harus dipenuhi oleh <span class="b">MITRA</span> sesuai dengan Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">g.</div><div class="alpha-body"><span class="b">Penyerahan Pekerjaan</span> adalah waktu penyerahan pekerjaan berdasarkan Perjanjian dari <span class="b">MITRA</span> kepada <span class="b">TKM</span> yang dituangkan dalam BAST-I.</div></div>
  <div class="alpha"><div class="alpha-no">h.</div><div class="alpha-body"><span class="b">Jangka Waktu Penyelesaian Pekerjaan</span> adalah jangka waktu penyelesaian pekerjaan terhitung sejak SPK ditandatangani sampai ditandatanganinya BAUT-I, dengan ketentuan seluruh pekerjaan sudah dilaksanakan dengan baik dan dapat diterima <span class="b">TKM</span>.</div></div>
  <div class="alpha"><div class="alpha-no">i.</div><div class="alpha-body"><span class="b">Berita Acara Uji Terima (BAUT/BA-ATP)</span> adalah Berita Acara yang menyatakan bahwa Pekerjaan yang bertalian secara fisik telah selesai 100% tanpa adanya major maupun minor pending item serta telah diuji terima dan dinyatakan baik sesuai Spesifikasi Teknis yang ditetapkan dalam Perjanjian ini. Berita Acara ini ditandatangani oleh <span class="b">TKM</span> dan <span class="b">MITRA</span>.</div></div>
  <div class="alpha"><div class="alpha-no">j.</div><div class="alpha-body"><span class="b">Berita Acara Opname</span> adalah berita acara hasil perhitungan nilai akhir pelaksanaan Pekerjaan yang bertalian dan ditandatangani oleh <span class="b">TKM</span> c.q. Supervisor, Site Manager dan Regional Project Manager dan <span class="b">MITRA</span>.</div></div>
  <div class="alpha"><div class="alpha-no">k.</div><div class="alpha-body"><span class="b">Berita Acara Rekon Material</span> adalah berita acara hasil perhitungan penggunaan material pelaksanaan Pekerjaan yang bertalian dan ditandatangani oleh <span class="b">TKM</span> c.q. Supervisor, Site Manager dan Regional Logistik Manager dan <span class="b">MITRA</span>.</div></div>
  <div class="alpha"><div class="alpha-no">l.</div><div class="alpha-body"><span class="b">Harga Satuan</span> adalah harga satuan dari masing-masing item/unsur Pekerjaan dan merupakan dasar untuk menghitung/menetapkan besarnya Harga Borongan dalam setiap Surat Perintah Kerja dan dinyatakan dalam mata uang Rupiah.</div></div>

  <div class="section-center">PASAL 2</div>
  <div class="section-center">SPESIFIKASI TEKNIS</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">MITRA</span> menjamin bahwa semua pekerjaan yang sudah disepakati dikerjakan sesuai spesifikasi teknis yang dituangkan dalam BAST yang telah disepakati dalam Perjanjian Kontrak Kerja ini.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Dari waktu ke waktu, Para Pihak dapat melakukan perubahan atas spesifikasi teknis termasuk BAST dari setiap pekerjaan dengan memperhatikan kondisi-kondisi yang mempengaruhi pelaksanaan pekerjaan yang akan dituangkan ke dalam suatu kesepakatan bersama atau Perjanjian tambahan yang akan ditandatangani oleh Para Pihak.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">2</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="section-center">PASAL 3</div>
  <div class="section-center">RUANG LINGKUP PEKERJAAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">TKM</span> menyerahkan Pekerjaan Pemasangan Outside Plan Fiber Optic (OSP-FO), selanjutnya disebut sebagai Pekerjaan, kepada <span class="b">MITRA</span>, sebagaimana <span class="b">MITRA</span> menerima penyerahan Pekerjaan tersebut dari <span class="b">TKM</span> dan sanggup untuk melaksanakan Pekerjaan dimaksud, sesuai dengan Ruang Lingkup Pekerjaan, Spesifikasi Teknis sebagaimana diuraikan dalam Lampiran Perjanjian ini dan Hasil Rapat Pembahasan Design (DRM) bila ada, serta menyerahkan kepada <span class="b">TKM</span> dengan Jangka Waktu Pelaksanaan yang ditetapkan dalam Surat Perintah Kerja serta siap untuk dipergunakan oleh <span class="b">TKM</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Pekerjaan-Pekerjaan lain yang tidak dapat dirinci satu persatu namun menurut sifatnya merupakan tanggung jawab <span class="b">MITRA</span> untuk melaksanakannya sehingga Pekerjaan dapat diselesaikan menurut kuantitas dan kualitas serta dalam Jangka Waktu Pelaksanaan yang telah ditetapkan dalam Perjanjian ini.</div></div>

  <div class="section-center">PASAL 4</div>
  <div class="section-center">SYARAT PELAKSANAAN</div>

  <p>Dalam melaksanakan Pekerjaan menurut Perjanjian ini, <span class="b">MITRA</span> harus mentaati hal-hal sebagai berikut:</p>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Spesifikasi Teknis, gambar rencana dan detailnya termasuk perubahan-perubahannya yang disepakati oleh Para Pihak sepanjang sesuai dengan Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Menyediakan tenaga ahli yang memenuhi klasifikasi dan kualifikasi tenaga ahli sesuai jenis Pekerjaan, modal dan peralatan kerja dalam jumlah yang cukup dan memadai dan fasilitas lain yang diperlukan, sehingga Pekerjaan dapat diselesaikan tepat mutu, tepat kuantitas dan tepat waktu.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Segala petunjuk dan instruksi berdasarkan Perjanjian ini yang diberikan oleh <span class="b">TKM</span>.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Memperhatikan tata lingkungan setempat dan pengelolaan lingkungan hidup sesuai dengan peraturan perundangan yang berlaku.</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body">Peraturan-peraturan dan ketentuan-ketentuan lain yang terkait dengan pelaksanaan Perjanjian ini, baik yang dikeluarkan oleh <span class="b">TKM</span>, Pemerintah Pusat maupun Daerah atau Instansi Pemerintah yang berwenang.</div></div>

  <div class="section-center">PASAL 5</div>
  <div class="section-center">TANGGUNG JAWAB DAN KEWAJIBAN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Disamping tanggung jawab dan kewajiban yang telah diatur dalam pasal-pasal Perjanjian ini, hal-hal dibawah ini menjadi tanggung jawab dan kewajiban <span class="b">MITRA</span>, sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Bertanggung jawab terhadap semua risiko yang timbul dalam pelaksanaan Pekerjaan sampai dengan diterimanya hasil Pekerjaan, serta bertanggung jawab selama Masa Pemeliharaan.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Berkewajiban untuk menggunakan tenaga-tenaga yang mempunyai kemampuan/keahlian dan pengalaman profesional yang memadai.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Berkewajiban menyediakan alat-alat (sarana/prasarana), metode, teknik, dan prosedur pemasangan dalam keadaan cukup dan berkualitas baik untuk melaksanakan Pekerjaan.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Berkewajiban selalu memperhatikan kebersihan lingkungan tempatnya bekerja dan membersihkan kembali lokasi Pekerjaan dari sisa-sisa barang sehubungan dengan pelaksanaan Pekerjaan.</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body">Berkewajiban mentaati segala peraturan serta ketentuan yang berlaku guna menjamin keamanan perangkat TKM, kesehatan dan keselamatan kerja orang-orang yang bekerja untuknya, serta kesehatan dan keselamatan umum di sekitarnya.</div></div>
  <div class="alpha"><div class="alpha-no">f.</div><div class="alpha-body">Bertanggung jawab untuk mengawasi pekerjaan-pekerjaan pegawainya dan harus segera mengatasi segala pelanggaran yang dilaporkan kepadanya.</div></div>
  <div class="alpha"><div class="alpha-no">g.</div><div class="alpha-body">Berkewajiban memberikan perlindungan kepada pegawai yang ada di bawah kendali <span class="b">MITRA</span> atas keselamatan dan kesehatan kerja dari kemungkinan terjadinya kecelakaan dan/atau sakit akibat kerja.</div></div>
  <div class="alpha"><div class="alpha-no">h.</div><div class="alpha-body">Pegawai <span class="b">MITRA</span> diwajibkan mengenakan identifikasi sebagai pegawai dari <span class="b">TKM</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Disamping tanggung jawab dan kewajiban yang telah jelas diatur dalam pasal-pasal Perjanjian ini, hal-hal dibawah ini menjadi tanggung jawab dan kewajiban TKM :</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">3</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Menugaskan dan Menempatkan :</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">1.&nbsp;&nbsp;&nbsp;&nbsp;Regional Project Manager</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">2.&nbsp;&nbsp;&nbsp;&nbsp;Regional Logistik Manager</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">3.&nbsp;&nbsp;&nbsp;&nbsp;Site Manager</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">4.&nbsp;&nbsp;&nbsp;&nbsp;Supervisor</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">5.&nbsp;&nbsp;&nbsp;&nbsp;Team Logistik</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Menjamin kesiapan Lokasi dan perijinan Instansi untuk pelaksanaan Pekerjaan.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Menerbitkan BAUT/BA ATP, Berita Acara Opname, Berita Acara Rekon Material, BAST dan berita acara lainnya terkait pekerjaan sesuai dengan ketentuan dalam Perjanjian ini tepat pada waktunya.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Melakukan pembayaran kepada <span class="b">MITRA</span> tepat pada waktunya.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Para Pihak akan melaksanakan tanggung jawab dalam Jangka Waktu Pelaksanaan Pekerjaan, sehingga Pekerjaan dapat diselesaikan sesuai dengan jadwal.</div></div>

  <div class="section-center">PASAL 6</div>
  <div class="section-center">MASA BERLAKU KONTRAK INDUK</div>
  <p>Masa berlaku Perjanjian Kontrak Kerja Pekerjaan Pemasangan Outside Plan Fiber Optik (OSP-FO) ini berlaku sejak di tandatangani nya kontrak ini sampai dengan tanggal <span class="b">31 Desember 2026</span></p>

  <div class="section-center">PASAL 7</div>
  <div class="section-center">KANTOR DAN GUDANG MATERIAL</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">MITRA</span> wajib menyediakan Kantor Project yang layak sesuai standar yang sudah ditetapkan <span class="b">TKM</span></div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Gudang material project yang memenuhi SOP yang sudah ditetapkan <span class="b">TKM</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body"><span class="b">MITRA</span> wajib membuat laporan material secara berkala (Harian, Mingguan dan Bulanan).</div></div>
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Segala kerusakan dan kehilangan material yang disebabkan kelalaian <span class="b">MITRA</span> menjadi tanggung jawab mitra pelaksana.</div></div>
  <div class="alpha"><div class="alpha-no">(5)</div><div class="alpha-body">Jika dalam perhitungan rekonsiliasi material terdapat perbedaan penggunaan material, maka nilai selisih kekurangan akan dipotongkan dari tagihan mitra pelaksana.</div></div>
  <div class="alpha"><div class="alpha-no">(6)</div><div class="alpha-body">Jika Gudang dinyatakan tidak layak, maka penyediaan gudang diambil alih oleh <span class="b">TKM</span>.</div></div>

  <div class="section-center">PASAL 8</div>
  <div class="section-center">SURAT PERINTAH KERJA</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Pelaksanaan Pekerjaan akan dilakukan dengan penerbitan Surat Perintah Kerja (selanjutnya disebut dengan SPK), yang ditandatangani oleh TKM dan MITRA.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Periode penerbitan SPK dilakukan dalam kurun waktu sesuai masa laku Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Dalam SPK akan dicantumkan antara lain data atau informasi sebagai berikut.</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Lokasi Pekerjaan</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Pekerjaan yang diperintahkan</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Volume Pekerjaan</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Jangka Waktu Pelaksanaan Pekerjaan</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body">Harga Borongan</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">4</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Tanggal SPK akan dihitung sebagai tanggal awal Jangka Waktu Pelaksanaan.</div></div>

  <div class="section-center">PASAL 9</div>
  <div class="section-center">JANGKA WAKTU PELAKSANAAN PEKERJAAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Jangka Waktu Pelaksanaan Pekerjaan akan ditentukan di dalam SPK yang ditandatangani <span class="b">TKM</span> dan MITRA.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Jangka waktu dimaksud ayat (1) di atas, dihitung sampai dengan diterbitkannya Berita Acara Pekerjaan 100%, dengan lampiran kelengkapan dokumen BA ATP/BAUT yang disetujui <span class="b">Bouwheer</span>.</div></div>

  <div class="section-center">PASAL 10</div>
  <div class="section-center">PERPANJANGAN WAKTU PELAKSANAAN PEKERJAAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Jangka Waktu Pelaksanaan/Penyelesaian Pekerjaan dimaksud Pasal 5 Perjanjian ini dapat diperpanjang, apabila :</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Fasilitas dan kewajiban yang menjadi tanggung jawab <span class="b">TKM</span> belum tersedia yang dibuktikan dengan Berita Acara yang ditandatangani oleh TKM dan MITRA; atau</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Ada perintah tertulis dari TKM untuk menunda waktu pelaksanaan Pekerjaan; atau</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Terjadinya peristiwa/kejadian Force Majeure yang menyebabkan terlambatnya penyelesaian Pekerjaan, sebagaimana dimaksud Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Perpanjangan Jangka Waktu Pelaksanaan Pekerjaan yang bukan disebabkan oleh hal-hal dimaksud ayat (1) di atas, hanya dapat diberikan apabila <span class="b">MITRA</span> menyampaikan permohonan tertulis kepada <span class="b">TKM</span> dengan mengemukakan alasan yang dapat diterima <span class="b">TKM</span>, dan sudah harus diterima TKM dalam waktu 7 (tujuh) Hari Kalender sebelum berakhirnya Waktu Pelaksanaan.</div></div>

  <div class="section-center">PASAL 11</div>
  <div class="section-center">HARGA BORONGAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Harga Borongan adalah harga total seluruh Pekerjaan sebagaimana tercantum dalam <span class="b">Surat Perintah Kerja (SPK)</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Harga Borongan dapat berubah apabila terdapat Pekerjaan Tambah Kurang yang dibuktikan dengan Berita Acara Opname.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Apabila terdapat item baru yang belum ada harga satuannya dalam Kontrak (<span class="ital">new item</span>) maka terhadap harga satuan untuk Pengadaan tambahan (<span class="ital">new item</span>) harus dinegosiasikan dan disepakati oleh Para Pihak. Harga satuan (<span class="ital">new item</span>) yang telah disepakati oleh Para Pihak akan dituangkan dalam Amandemen Perjanjian ini. Perubahan Harga Borongan akibat <span class="ital">new item</span> harus dibuktikan dengan Berita Acara Opname.</div></div>

  <div class="section-center">PASAL 12</div>
  <div class="section-center">PEKERJAAN TAMBAH/KURANG</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Setiap penambahan atau pengurangan terhadap Volume/BoQ yang telah ditetapkan dalam Perjanjian ini baru dapat dilaksanakan setelah ada persetujuan secara tertulis dari TKM c.q. Supervisor, Site Manager dan diketahui oleh Regional Project Manager.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body"><span class="b">TKM</span> berhak melakukan penambahan atau pengurangan pekerjaan yang tercantum dalam perjanjian atau diluar perjanjian disertai dengan pemberitahuan secara tertulis kepada MITRA. <span class="b">MITRA</span> berkewajiban melaksanakan penambahan dan/atau pengurangan pekerjaan tersebut.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">5</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Apabila harga satuan untuk Pekerjaan tambahan tidak terdapat dalam Perjanjian ini, maka akan dilakukan negosiasi untuk mencapai kesepakatan harga antara Para Pihak.</div></div>
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Apabila persetujuan pekerjaan tambah atau kurang diberikan dalam bentuk lisan sebagaimana dimaksud ayat (1) Pasal ini, maka harus diikuti dengan persetujuan tertulis dari <span class="b">TKM</span> c.q. Supervisor, Site Manager dan diketahui oleh Regional Project Manager paling lambat 3 (tiga) hari kerja sejak tanggal persetujuan lisan diberikan.</div></div>
  <div class="alpha"><div class="alpha-no">(5)</div><div class="alpha-body">Apabila <span class="b">MITRA</span> melakukan tambahan Pekerjaan tanpa adanya persetujuan tertulis dari <span class="b">TKM</span> c.q. Supervisor, Site Manager dan diketahui oleh Regional Project Manager, maka harus dianggap suatu pelepasan oleh <span class="b">MITRA</span> atas setiap dan semua klaim untuk pembayaran atas Pekerjaan tambahan dimaksud.</div></div>

  <div class="section-center">PASAL 13</div>
  <div class="section-center">PENYELESAIAN PERHITUNGAN AKHIR PEKERJAAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Pada akhir pelaksanaan Pekerjaan dan setelah diterbitkannya Berita Acara Uji Terima (BAUT/BA-ATP) akan dilakukan Perhitungan Akhir Pekerjaan (Opname) oleh TKM dan MITRA yang hasilnya dituangkan dalam Berita Acara Opname dan selanjutnya akan dipergunakan sebagai dasar penerbitan Berita Acara Serah Terima Pertama (BAST-I) dan pembayaran, dengan ketentuan sebagai berikut:</div></div>

  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Perhitungan Akhir Pekerjaan (Opname) dilakukan berdasarkan data-data yang dapat dipertanggungjawabkan yaitu :</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">1)&nbsp;&nbsp;&nbsp;Apabila terdapat pekerjaan tambah-kurang, maka harus didukung dokumen persetujuan tertulis adanya pekerjaan tambah-kurang.</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">2)&nbsp;&nbsp;&nbsp;Apabila terdapat perubahan jangka waktu penyelesaian/pelaksanaan Pekerjaan dari yang telah ditetapkan, maka harus didukung dokumen persetujuan tertulis tentang perubahan jangka waktu.</div></div>

  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Berdasarkan Berita Acara Uji Terima (BAUT) dan Berita Acara Opname, akan diterbitkan Berita Acara Serah Terima Pertama (BAST-I), dengan ketentuan apabila Harga Borongan yang bertalian hasil perhitungan akhir Pekerjaan (Opname) lebih tinggi atau lebih rendah dari Harga Borongan, maka Harga Borongan yang dicantumkan dalam Berita Acara Serah Terima Pertama (BAST-I) sebesar <span class="b">Harga Borongan yang tercantum dalam Berita Acara Opname</span>.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Apabila nilai pelaksanaan Pekerjaan yang tercantum dalam Berita Acara Serah Terima Pertama (BAST-I) lebih tinggi dari Harga Borongan yang bertalian, maka Harga Borongan yang akan dibayarkan maksimum sebesar <span class="b">Harga Borongan yang telah ditetapkan dalam SPK</span> yang bertalian terlebih dahulu tanpa menunggu diterbitkannya Amandemen terhadap Perjanjian ini, sedangkan sisanya akan dibayarkan setelah Amandemen terhadap SPK yang bertalian.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Apabila nilai pelaksanaan Pekerjaan yang tercantum dalam Berita Acara Serah Terima Pertama (BAST-I) lebih rendah dari Harga Borongan yang bertalian, maka <span class="b">Harga Borongan yang akan dibayarkan maksimum sebesar Harga Borongan yang bertalian yang tercantum dalam Berita Acara Serah Terima Pertama (BAST-I)</span> tanpa menunggu diterbitkannya Amandemen terhadap SPK yang bertalian.</div></div>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Perubahan Harga Borongan dari Harga Borongan yang telah ditetapkan dalam SPK, setelah diterbitkannya Berita Acara Opname, selanjutnya akan dituangkan dalam Amandemen terhadap SPK yang bertalian.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">6</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="section-center">PASAL 14</div>
  <div class="section-center">TATA CARA PEMBAYARAN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Pembayaran akan dilakukan langsung oleh <span class="b">TKM</span> kepada <span class="b">MITRA</span> dengan cara giral melalui BANK atas nama <span class="b">MITRA</span> dengan biaya transfer dibebankan kepada MITRA dan dipotong langsung dari jumlah pembayaran, dengan sistem pembayaran setelah pekerjaan selesai dengan mengajukan permohonan pembayaran yang dilengkapi syarat penagihan sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Copy Surat Perintah Kerja (SPK).</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Berita Acara Opname.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Berita Acara Rekon Material.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Permohonan Pembayaran.</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body">Kwitansi bermaterai.</div></div>
  <div class="alpha"><div class="alpha-no">f.</div><div class="alpha-body">Invoice.</div></div>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Pembayaran dari masing-masing Surat Pesanan akan dilakukan sebagai ketentuan sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Pembayaran Pertama sebagai <span class="b">Uang Muka sebesar 15%</span> (lima belas persen) dari harga Borongan dalam Surat Pesanan yang bertalian, akan dibayarkan oleh TKM kepada MITRA setelah dipenuhi syarat-syarat sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">a.1&nbsp;&nbsp;Setelah perjanjian ditandatangani oleh kedua Belah Pihak</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">a.2&nbsp;&nbsp;Setelah diterbitkannya Surat Perintah Kerja yang bertalian.</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">a.3&nbsp;&nbsp;Setelah dilakukan Kick Of Meeting antara TKM dan <span class="b">MITRA</span>, sebagai dasar pembuatan RAB (Rencana Anggaran Biaya) Proyek.</div></div>
  <div class="alpha"><div class="alpha-no"></div><div class="alpha-body">a.4&nbsp;&nbsp;Setelah perjanjian ditandatangani oleh kedua Belah Pihak</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Pembayaran Kedua sebesar <span class="b">35%</span> (tiga puluh lima persen) dari Harga Borongan Sub Sistem yang terkait dari Surat Pesanan yang bertalian, akan dibayarkan oleh TKM kepada MITRA setelah Bobot Pekerjaan mencapai lebih dari 75% (Tujuh puluh lima persen).</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Pembayaran Ketiga sebesar <span class="b">25%</span> (dua puluh lima persen) dari Harga Borongan Sub Sistem yang terkait dari Surat Pesanan yang bertalian, akan dibayarkan oleh TKM kepada MITRA setelah Bobot Pekerjaan mencapai 100% (seratus persen) dan <span class="b">Amandemen SPK</span>.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Pembayaran Keempat sebesar <span class="b">15%</span> (lima belas persen) dari Harga Borongan Sub Sistem yang terkait dari Surat Pesanan yang bertalian, akan dibayarkan setelah terbit <span class="b">Berita Acara Penyelesaian Pekerjaan (BAPP)</span>.</div></div>
  <div class="alpha"><div class="alpha-no">e.</div><div class="alpha-body">Pembayaran Kelima sebesar <span class="b">10%</span> (lima persen) dari Harga Borongan Sub Sistem yang terkait dari Surat Pesanan yang bertalian, akan dibayarkan setelah masa pemeliharaan berakhir selama 90 (Sembilan puluh) Hari Kalender Sejak tanggal Amandemen SPK di tandatangani dan apabila tidak ada Amandemen SPK acuannya <span class="b">3 Bulan</span> setelah tagihan Term Of Payment (TOP) sebelumnya.</div></div>

  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Pembayaran kepada Mitra Pelaksana akan dibayarkan <span class="b">14 Hari Kerja</span> setelah dokumen dinyatakan Lengkap dan memenuhi persyaratan.</div></div>

  <div class="section-center">PASAL 15</div>
  <div class="section-center">DENDA</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Toleransi Keterlambatan adalah <span class="b">7 Hari Kalender</span> dari tanggal Batas Waktu Pelaksanaan (Time Of Contract) Surat Perintah Kerja.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Jika setelah diberikan Toleransi Keterlambatan dan pekerjaan masih belum selesai, maka akan dikenakan Denda sebesar <span class="b">1%</span> perhari setelah selesai masa toleransi dari Nilai Surat Perintah Kerja (SPK).</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Batas maksimum Keterlambatan adalah selama <span class="b">10 Hari Kalender</span> atau Denda sebesar 10% dari Nilai SPK atau Amandemen SPK.</div></div>
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Jika setelah dikenakan Denda 10% pekerjaan masih belum selesai, maka <span class="b">TKM</span> akan melakukan <span class="b">Take Over SPK</span> tersebut.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">7</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">(5)</div><div class="alpha-body">Jatuh Tempo Pembayaran Denda, <span class="b">MITRA</span> harus membayar denda kepada <span class="b">TKM</span> selambat-lambatnya 30 (Tiga Puluh) hari kalender sejak penerimaan pemberitahuan tertulis dari TKM.</div></div>
  <div class="alpha"><div class="alpha-no">(6)</div><div class="alpha-body">Kegagalan dalam Membayar Penalti. Apabila <span class="b">MITRA</span> gagal untuk langsung melakukan pembayaran penalti tersebut, <span class="b">TKM</span> dapat segera memberlakukan dan melakukan perjumpaan utang atas penalti tersebut.</div></div>

  <div class="section-center">PASAL 16</div>
  <div class="section-center">PEMBEBASAN DENDA</div>
  <p><span class="b">MITRA</span> dapat dibebaskan dari denda dimaksud Pasal 15 Perjanjian ini apabila terpenuhinya salah satu ketentuan sebagai berikut:</p>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Apabila <span class="b">MITRA</span> dapat membuktikan secara sah dengan surat resmi dari Pejabat Pemerintah yang berwenang bahwa keterlambatan dimaksud terjadi akibat Force Majeure sebagaimana dimaksud dalam Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Apabila Fasilitas dan kewajiban-kewajiban yang menjadi tanggung jawab <span class="b">TKM</span> berdasarkan Perjanjian ini belum tersedia tepat pada waktunya yang dibuktikan dengan Berita Acara yang ditandatangani oleh TKM c.q. Supervisor, Site Manager dan Regional Project Manager dan MITRA.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Apabila keterlambatan dimaksud disebabkan karena perintah tertulis dari <span class="b">TKM</span> kepada <span class="b">MITRA</span> untuk menunda atau menghentikan untuk sementara waktu pelaksanaan Pekerjaan.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Apabila permintaan perpanjangan waktu penyelesaian Pekerjaan dari MITRA telah disetujui secara tertulis oleh TKM c.q. Supervisor, Site Manager dan Regional Project Manager.</div></div>

  <div class="section-center">PASAL 17</div>
  <div class="section-center">PENGAWASAN PELAKSANAAN PEKERJAAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Pengawasan pelaksanaan Pekerjaan akan dilakukan oleh Supervisor, Site Manager dan Regional Project Manager yang akan ditunjuk oleh TKM.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body"><span class="b">MITRA</span> wajib mematuhi petunjuk atau perintah dari Supervisor, Site Manager atau Regional Project Manager sepanjang petunjuk atau perintah tersebut mengenai Ruang Lingkup Pekerjaan yang harus dilaksanakan oleh <span class="b">MITRA</span> menurut Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body"><span class="b">MITRA</span> wajib melaporkan jurnal logistik ke Regional Logistik Manager.</div></div>

  <div class="section-center">PASAL 18</div>
  <div class="section-center">KERUSAKAN DAN KERUGIAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">MITRA</span> bertanggung jawab untuk mengganti semua kerusakan dan/atau kerugian baik langsung maupun tidak langsung (<span class="ital">loss of opportunity</span>) maksimum sebesar 100% (seratus persen) dari Total Harga Borongan atau hasil Opname (apabila ada) yang diterbitkan berdasarkan SPK yang bertalian, terhadap barang-barang atau kepentingan TKM yang timbul akibat kesengajaan atau kelalaian MITRA, pegawai-pegawainya, pekerja-pekerjanya ataupun orang-orang yang bekerja untuknya, paling lambat 30 (tiga puluh) Hari Kalender terhitung sejak tanggal pemberitahuan dari <span class="b">TKM</span>. Apabila <span class="b">MITRA</span> lalai atau tidak melaksanakannya, maka <span class="b">TKM</span> berhak secara sepihak memotong langsung dari jumlah tagihan <span class="b">MITRA</span> yang belum dibayarkan TKM, senilai kerugian dimaksud.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Segala kerusakan dan kerugian yang diderita oleh pihak ketiga akibat kesengajaan/kelalaian/kesalahan <span class="b">MITRA</span>, pegawai-pegawainya, pekerja-pekerjanya ataupun orang-orang yang bekerja untuknya sepenuhnya menjadi tanggung jawab <span class="b">MITRA</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Bilamana kerusakan atau kerugian dimaksud ayat (1) Pasal ini dapat dibuktikan oleh <span class="b">MITRA</span> bukan sebagai akibat kesengajaan atau kelalaian MITRA, pegawai-pegawainya, pekerja-pekerjanya</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">8</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <p>ataupun orang-orang yang bekerja untuknya maka <span class="b">MITRA</span> dibebaskan dari tanggung jawab tersebut ayat (1) Pasal ini.</p>

  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Kerusakan dan kerugian yang menjadi tanggung jawab MITRA dimaksud ayat (1) Pasal ini adalah semua kerugian fisik yang terkait dengan pelaksanaan Pekerjaan.</div></div>

  <div class="section-center">PASAL 19</div>
  <div class="section-center">PENGGANTIAN KERUGIAN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Tanpa mengurangi ketentuan lain Perjanjian ini, <span class="b">MITRA</span> berjanji untuk memberikan ganti kerugian kepada TKM atau pihak lainnya dan membebaskan TKM dari semua kerugian, biaya dan pengeluaran yang timbul dari setiap klaim/tuntutan, termasuk biaya arbitrase, biaya pengadilan, biaya pengacara serta ganti rugi yang ditetapkan pengadilan maupun lembaga lain yang berwenang menyelesaikan klaim/tuntutan, apabila klaim/ tuntutan dimaksud timbul karena hal-hal sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Sebagai akibat dari kelalaian <span class="b">MITRA</span> dalam melaksanakan kewajibannya sesuai dengan Perjanjian ini atau perjanjian khusus lain terkait dengan Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Sehubungan dengan setiap klaim/tuntutan termasuk denda atau sanksi lainnya yang diderita <span class="b">TKM</span> sebagai akibat dari pelanggaran oleh <span class="b">MITRA</span> atau salah satu karyawannya terhadap hukum dan peraturan yang berlaku.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Atas cidera pribadi yang menimpa dan/atau kematian seseorang dan kerusakan yang terjadi pada harta benda akibat tindakan atau kelalaian untuk melakukan suatu tindakan baik karena kelalaian atau bukan dari MITRA, para karyawannya, agen atau sub-kontraktornya.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body">Adanya sengketa yang timbul sehubungan dengan perjanjian yang telah ditandatangani <span class="b">MITRA</span> dengan pihak ketiga sebelum maupun selama pelaksanaan Perjanjian ini. Jika <span class="b">TKM</span> ikut digugat dalam sengketa ini, maka <span class="b">MITRA</span> akan bertanggung jawab penuh untuk menanggung biaya Pengacara yang ditunjuk sendiri oleh TKM untuk menghadapi gugatan tersebut. Jika atas permohonan dari lawan sengketa MITRA pengadilan menjatuhkan putusan provisiuni yang melarang pelaksanaan proyek lebih lanjut, maka <span class="b">MITRA</span> sepakat bahwa TKM berhak untuk mengalihkan proyek tersebut kepada pihak lain agar kepentingan TKM atas proyek tersebut tidak terganggu atau terhenti.</div></div>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Apabila terjadi hal-hal dimaksud ayat (1) Pasal ini, maka <span class="b">TKM</span> harus :</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Menyampaikan segera pemberitahuan tertulis kepada <span class="b">MITRA</span>, jika ada klaim gugatan dari pihak ketiga.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Mengijinkan <span class="b">MITRA</span> untuk menyelesaikan klaim dan/atau gugatan dimaksud atas permintaan MITRA dan atas biaya <span class="b">MITRA</span>.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Memberikan informasi dan bantuan yang wajar bila diperlukan <span class="b">MITRA</span> dan atas permintaan tertulis dari <span class="b">MITRA</span> dalam upaya menyangkal atau menyelesaikan klaim dan atau gugatan dimaksud.</div></div>

  <div class="section-center">PASAL 20</div>
  <div class="section-center">LAPORAN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Selama Jangka Waktu Pelaksanaan/Penyelesaian Pekerjaan berdasarkan Perjanjian ini, MITRA harus membuat rencana kerja dan menyampaikan laporan Harian, Mingguan dan Bulanan secara tertulis kepada TKM mengenai kemajuan pelaksanaan Pekerjaan dan serta permasalahan utama yang timbul di Lokasi dilengkapi dokumen pendukung.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body"><span class="b">MITRA</span> harus memperhatikan secara langsung untuk mempersiapkan laporan kemajuan dalam waktu dan format dimana <span class="b">TKM</span> dengan mudah dapat memeriksa pelaksanaan Pekerjaan yang telah dilaksanakan.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Laporan tersebut ayat (1) Pasal ini ditujukan kepada Supervisor, Site Manager dan Regional Project Manager <span class="b">TKM</span>.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">9</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="section-center">PASAL 21</div>
  <div class="section-center">DOKUMENTASI</div>

  <p>Semua dokumentasi yang berhubungan dengan Pekerjaan dimaksud dalam Perjanjian ini seperti gambar akhir pelaksanaan (<span class="ital">as built drawing</span>) dan informasi lainnya menurut Perjanjian ini harus diserahkan oleh <span class="b">MITRA</span> kepada <span class="b">TKM</span> dan merupakan salah satu persyaratan pembayaran. <span class="b">MITRA</span> harus bertanggung jawab terhadap setiap keterlambatan yang diakibatkan oleh ketidakmampuannya untuk menyerahkan dokumentasi tersebut sehingga tidak dapat dilakukannya pembayaran tepat pada waktunya.</p>

  <div class="section-center">PASAL 22</div>
  <div class="section-center">KERAHASIAAN DAN PERLINDUNGAN DATA PRIBADI</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">Kerahasiaan</span></div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Para Pihak sepakat untuk menjaga kerahasiaan semua informasi, data, dokumen, pengetahuan dalam bentuk apapun yang timbul dan diperoleh dalam pelaksanaan perjanjian ini (Informasi Rahasia) dan tidak akan mengungkapkannya kepada pihak mana pun tanpa persetujuan tertulis terlebih dahulu dari pihak lainnya, kecuali pengungkapan tersebut dilakukan kepada (i) instansi pemerintah yang berwenang sesuai dengan ketentuan Hukum dan Peraturan Perundang-undangan yang berlaku, (ii) konsultan hukum, atau (iii) Lembaga keuangan yang tugasnya memerlukan informasi rahasia tersebut, dengan ketentuan bahwa konsultan hukum dan Lembaga keuangan tersebut telah menyetujui untuk tidak akan dipublikasikan kepada pihak luar dengan alasan apa pun dan telah membuat kesepakatan tertulis untuk tidak mengungkapkan informasi rahasia tersebut kepada pihak lain untuk maksud apa pun.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Pembatasan tersebut pada ayat 1 Pasal ini tidak diterapkan atas informasi rahasia dalam hal sebagai berikut: (i) Informasi rahasia tersebut telah menjadi public domain yang tidak disebabkan oleh pelanggaran ayat (1) Pasal ini, (ii) Informasi rahasia tersebut berada pada pihak yang menerimanya secara sah sebelum pengungkapan informasi rahasia dilakukan, (iii) informasi rahasia tersebut diperoleh dengan itikad baik dari pihak yang berhak untuk mengungkapkannya.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Pembatasan dimaksud pada Perjanjian ini akan tetap berlaku sekalipun Perjanjian ini telah berakhir.</div></div>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body"><span class="b">Perlindungan Data Pribadi</span></div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Para Pihak sepakat bahwa masing-masing Pihak akan memproses, menerapkan, melihat dan menggunakan Data Pribadi yang terlibat dalam proyek ini hanya sejauh yang diperlukan untuk melaksanakan Perjanjian ini. Tidak ada Pihak yang boleh melakukan pengalihan, transfer atau mengizinkan penggunaan Data Pribadi yang terlibat dalam project ini kecuali secara tegas dan mengikat diinstruksikan atau diizinkan oleh Para Pihak. Para Pihak harus mematuhi hukum yang berlaku dan praktik terbaik terkait Privasi Data dan Keamanan Data.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Para Pihak yang selanjutnya terlibat dalam pemrosesan dan penggunaan Data Pribadi untuk kebutuhan proses bisnis, yang didalamnya terdapat proses pengaturan hak dan kewajiban tentang pengendali Data Pribadi, selanjutnya akan berpedoman dan mengambil referensi dari Undang-undang No. 27 tahun 2022 tentang perlindungan Data Pribadi beserta peraturan pelaksananya (termasuk perubahan sewaktu-waktu) yang berlaku di Republik Indonesia.</div></div>

  <div class="section-center">PASAL 23</div>
  <div class="section-center">LARANGAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Larangan untuk Memberi atau Menerima Hadiah. <span class="b">MITRA</span> dilarang menawarkan, memberikan, menerima atau setuju untuk memberi/menerima pemberian hadiah, komisi, rabat atau bentuk-bentuk hadiah atau yang lainnya kepada atau dari pegawai <span class="b">TKM</span> atau pihak ketiga sehubungan dengan pelaksanaan Jasa, atau sehubungan dengan segala bentuk transaksi sebagaimana yang tercantum dalam Kontrak dan atau dalam Pelaksanaannya.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">10</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Larangan terhadap Tindakan yang Tidak Jujur atau Menipu. <span class="b">MITRA</span> tidak akan memberikan pernyataan atau janji palsu dan informasi tidak benar atau tidak akurat, atau salah penggambaran fakta atau otoritas, atau menyesatkan pihak ketiga terkait pelaksanaan Jasa.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Pelanggaran. Setiap pelanggaran terhadap ketentuan Pasal 23 oleh <span class="b">MITRA</span>, karyawannya atau Subkontraktornya atau orang lain yang bekerja untuk <span class="b">TKM</span> akan mengakibatkan diakhirinya Kontrak ini atau SPK oleh <span class="b">TKM</span> dengan tidak mengurangi hak untuk tuntutan pidana.</div></div>

  <div class="section-center">PASAL 24</div>
  <div class="section-center">FORCE MAJEURE</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Yang dimaksud dengan Force Majeure dalam Perjanjian ini adalah keadaan yang terjadi di luar kekuasaan salah satu Pihak dan tidak dapat diperkirakan sebelumnya, yang mengakibatkan Pihak dimaksud tidak dapat memenuhi kewajiban yang telah ditetapkan dalam Perjanjian, sebagai berikut:</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Bencana alam yaitu gempa bumi besar, tsunami, angin topan, gunung meletus, banjir besar, kebakaran besar, hujan deras terus menerus lebih dari 10 (sepuluh) Hari Kalender dan tanah longsor.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Bencana non alam yaitu epidemi dan wabah penyakit.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Konflik sosial antar kelompok atau antar komunitas, pemogokan umum, huru-hara, perang, sabotase dan pemberontakan.</div></div>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Dalam hal terjadi Force Majeure, Pihak yang mengalami Force Majeure wajib memberitahukan secara tertulis kepada Pihak lainnya maksimal dalam waktu 14 (empat belas) Hari Kalender sejak saat terjadinya.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Kelalaian atau kelambatan dalam memenuhi kewajiban pemberitahuan dimaksud ayat (2) Pasal ini, mengakibatkan tidak diakuinya peristiwa dimaksud ayat (1) Pasal ini sebagai <span class="ital">Force Majeure</span>.</div></div>
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Kejadian-kejadian tersebut ayat (1) Pasal ini dapat diperhitungkan sebagai perpanjangan waktu pelaksanaan kewajiban Para Pihak, apabila ketentuan ayat (2) Pasal ini dipenuhi.</div></div>
  <div class="alpha"><div class="alpha-no">(5)</div><div class="alpha-body">Semua kerugian yang timbul atau diderita salah satu pihak karena terjadinya <span class="ital">Force Majeure</span> bukan merupakan tanggung jawab pihak lain.</div></div>

  <div class="section-center">PASAL 25</div>
  <div class="section-center">PEMUTUSAN / PEMBATALAN PERJANJIAN</div>
  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body"><span class="b">TKM</span> berhak secara sepihak tanpa adanya tuntutan apapun dari <span class="b">MITRA</span>, untuk memutuskan sebagian atau seluruh Pekerjaan menurut Perjanjian ini, apabila salah satu diantara sebab-sebab tersebut dibawah ini terjadi :</div></div>
  <div class="alpha"><div class="alpha-no">a.</div><div class="alpha-body">Apabila dalam waktu 20 (dua puluh) Hari Kalender terhitung sejak SPK diterbitkan, <span class="b">MITRA</span> ternyata belum memulai pelaksanaan Pekerjaan.</div></div>
  <div class="alpha"><div class="alpha-no">b.</div><div class="alpha-body">Apabila jumlah denda telah mencapai jumlah denda maksimum.</div></div>
  <div class="alpha"><div class="alpha-no">c.</div><div class="alpha-body">Apabila <span class="b">MITRA</span> mengundurkan diri setelah menandatangani Perjanjian ini dan/atau selama pelaksanaan ini.</div></div>
  <div class="alpha"><div class="alpha-no">d.</div><div class="alpha-body"><span class="b">TKM</span> menilai kinerja <span class="b">MITRA</span> tidak baik atau tidak sesuai spesifikasi teknis dalam proses pelaksanaan project yang telah disepakati.</div></div>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">11</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="section-center">PASAL 26</div>
  <div class="section-center">WAKIL PARA PIHAK</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Untuk kelancaran pelaksanaan Pekerjaan menurut Perjanjian ini, <span class="b">TKM</span> dan <span class="b">MITRA</span> menunjuk wakilnya masing-masing berkaitan dengan pelaksanaan Perjanjian ini, sebagai berikut:</div></div>

  <p class="b">Perwakilan TKM:</p>
  <table class="info-table">
    <tr><td class="info-label">Nama</td><td class="info-sep">:</td><td><span class="b">YAYA SUNARYA</span></td></tr>
    <tr><td class="info-label">Jabatan</td><td class="info-sep">:</td><td>General Manager Project</td></tr>
    <tr><td class="info-label">Alamat</td><td class="info-sep">:</td><td>Rukan Puri Botanical Residence Blok H.8 No.22, Joglo - Jakarta Barat</td></tr>
    <tr><td class="info-label">No.Telp.</td><td class="info-sep">:</td><td>021 - 5855552, 58905600, 08131637204, 08112777831</td></tr>
    <tr><td class="info-label">Email</td><td class="info-sep">:</td><td>info@tkm.co.id, yaya@tkm.co.id</td></tr>
    <tr><td class="info-label">Tanda Tangan</td><td class="info-sep">:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stempel :</td></tr>
    <tr><td></td><td></td><td style="height:16px;"></td></tr>
    <tr><td></td><td></td><td style="height:16px;"></td></tr>
    <tr><td></td><td></td><td>..................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...................................</td></tr>
  </table>

  <p class="b" style="margin-top:6px;">Perwakilan MANDOR :</p>
  <table class="info-table">
    <tr><td class="info-label">Nama</td><td class="info-sep">:</td><td><span class="b"><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></span></td></tr>
    <tr><td class="info-label">Jabatan</td><td class="info-sep">:</td><td>Mandor</td></tr>
    <tr><td class="info-label">Alamat</td><td class="info-sep">:</td><td><?= htmlspecialchars($alamat, ENT_QUOTES) ?></td></tr>
    <tr><td class="info-label">No.Telp.</td><td class="info-sep">:</td><td><?= htmlspecialchars($noTelp, ENT_QUOTES) ?></td></tr>
    <tr><td class="info-label">Email</td><td class="info-sep">:</td><td><?= htmlspecialchars($emailPic, ENT_QUOTES) ?></td></tr>
    <tr><td class="info-label">Tanda Tangan</td><td class="info-sep">:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stempel :</td></tr>
    <tr><td></td><td></td><td style="height:16px;"></td></tr>
    <tr><td></td><td></td><td style="height:16px;"></td></tr>
    <tr><td></td><td></td><td>..................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...................................</td></tr>
  </table>

  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Perubahan terhadap wakil dan alamat dimaksud ayat (1) pasal ini harus disampaikan secara tertulis oleh pihak yang mengusulkan perubahan kepada pihak lainnya.</div></div>

  <div class="section-center">PASAL 27</div>
  <div class="section-center">KONFLIK KEPENTINGAN</div>
  <p>Pembatalan Kontrak karena Konflik Kepentingan. Jika <span class="b">MITRA</span> mempunyai konflik kepentingan kapan pun selama Jangka Waktu Kontrak ini, MITRA wajib untuk memberitahukan konflik tersebut kepada <span class="b">TKM</span> dan <span class="b">TKM</span> berhak untuk mengakhiri Kontrak dan/atau SPK tanpa adanya kewajiban untuk membayar atau mengganti biaya, pengeluaran atau harga akibat pengakhiran tersebut, selain untuk pelaksanaan Jasa yang sesuai dengan ketentuan-ketentuan dari Kontrak dan pembayaran atau penggantian biaya tersebut bertentangan dengan hukum atau peraturan perusahaan atau merugikan <span class="b">TKM</span>.</p>

  <div class="section-center">PASAL 28</div>
  <div class="section-center">PERBEDAAN-PERBEDAAN</div>
  <p>Apabila terdapat perbedaan antara lampiran-lampiran Perjanjian ini dengan pasal-pasal Perjanjian ini yang mengatur hal yang sama, maka yang berlaku dan mengikat adalah Pasal-pasal dalam Perjanjian ini.</p>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">12</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

<div class="page">
  <div class="section-center">PASAL 29</div>
  <div class="section-center">PENYELESAIAN PERSELISIHAN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Apabila di kemudian hari terjadi perselisihan dalam penafsiran atau pelaksanaan ketentuan-ketentuan dari Perjanjian, Para Pihak sepakat untuk terlebih dahulu menyelesaikan secara musyawarah untuk mufakat.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Bilamana musyawarah tersebut ayat (1) Pasal ini tidak menghasilkan mufakat, maka Para Pihak sepakat untuk menyerahkan semua sengketa yang timbul dari Perjanjian ini kepada Badan Arbitrase Nasional Indonesia (BANI) untuk diselesaikan pada tingkat pertama dan terakhir menurut peraturan dan prosedur BANI serta Undang-Undang Arbitrase, dan keputusan BANI bersifat final dan mengikat.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Selama perselisihan dalam proses penyelesaian, <span class="b">TKM</span> dan <span class="b">MITRA</span> wajib tetap melaksanakan Pekerjaan dan kewajiban lainnya menurut Perjanjian ini.</div></div>

  <div class="section-center">PASAL 30</div>
  <div class="section-center">LAIN - LAIN</div>

  <div class="alpha"><div class="alpha-no">(1)</div><div class="alpha-body">Perjanjian ini tidak bertentangan dengan Anggaran Dasar masing-masing pihak serta tidak melanggar peraturan yang mengikat masing-masing pihak. Masing-masing pihak telah mengambil semua tindakan yang diperlukan dan memperoleh semua persetujuan/ijin sesuai dengan ketentuan Anggaran Dasar masing-masing pihak dan/atau peraturan yang berlaku untuk menandatangani dan melaksanakan Perjanjian ini dan pihak yang menandatangani Perjanjian ini untuk Para Pihak memiliki wewenang untuk menandatangani Perjanjian ini dan mengikat masing masing Pihak.</div></div>
  <div class="alpha"><div class="alpha-no">(2)</div><div class="alpha-body">Setiap perubahan isi Perjanjian ini termasuk lampirannya akan mengikat apabila dinyatakan secara tertulis dan disetujui oleh <span class="b">TKM</span> dan <span class="b">MITRA</span> dengan membuat dan menandatangani Amandemen atau Side Letter terhadap Perjanjian ini, serta akan merupakan bagian yang tidak dapat dipisahkan dari Perjanjian ini.</div></div>
  <div class="alpha"><div class="alpha-no">(3)</div><div class="alpha-body">Segala ketentuan dan syarat-syarat dalam Perjanjian ini berlaku serta mengikat bagi pihak-pihak yang menandatangani, pengganti-penggantinya dan mereka yang memperoleh keuntungan dari padanya.</div></div>
  <div class="alpha"><div class="alpha-no">(4)</div><div class="alpha-body">Perjanjian ini dibuat dalam rangkap 2 (dua) asli masing-masing sama bunyinya diatas kertas bermeterai cukup serta mempunyai kekuatan hukum yang sama setelah ditandatangani kedua belah pihak.</div></div>

  <p style="margin-top:10px;">Demikian Perjanjian ini dibuat dengan itikad baik dan telah disepakati oleh Para Pihak.</p>

  <table style="width:100%; margin-top:20px; border-collapse:collapse; text-align:center; page-break-inside:avoid; break-inside:avoid;">
    <tr>
      <td style="width:50%; font-weight:700; padding-bottom:6px;">TKM</td>
      <td style="width:50%; font-weight:700; padding-bottom:6px;">MANDOR</td>
    </tr>
    <tr>
      <td style="height:80px;"></td>
      <td></td>
    </tr>
    <tr>
      <td style="font-weight:700; text-decoration:underline; padding-top:4px;">IDA ISNAENI</td>
      <td style="font-weight:700; text-decoration:underline; padding-top:4px;"><?= htmlspecialchars($picUpper, ENT_QUOTES) ?></td>
    </tr>
    <tr>
      <td style="font-weight:700;">DIREKTUR</td>
      <td style="font-weight:700;">MANDOR</td>
    </tr>
  </table>

  <div class="footer">
    <div class="footer-line">
      <div class="footer-page"><div class="page-box">13</div></div>
      <div class="footer-text">Kontrak OSP-FO TKM 2026</div>
      <div class="footer-sign"><div class="sign-box"><div>TKM</div><div>MITRA</div></div></div>
    </div>
  </div>
</div>

</body>
</html>

