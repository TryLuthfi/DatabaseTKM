<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'batch_approval_manual';
$screenshotDir = $outputDir . DIRECTORY_SEPARATOR . 'screenshots';
$snapshotDir = $outputDir . DIRECTORY_SEPARATOR . 'snapshots';

foreach ([$outputDir, $screenshotDir, $snapshotDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$env = parse_ini_file($root . DIRECTORY_SEPARATOR . '.env') ?: [];
$db = new mysqli(
    (string) ($env['HOSTNAME'] ?? 'localhost'),
    (string) ($env['USERNAME'] ?? 'root'),
    (string) ($env['PASSWORD'] ?? ''),
    (string) ($env['DATABASE'] ?? 'databasetkm_com')
);
$db->set_charset('utf8mb4');

$user = $db->query("
    SELECT username_user, password_user
    FROM tb_master_user_new
    WHERE username_user = 'admin'
       OR id_level = 1
    ORDER BY username_user = 'admin' DESC, id ASC
    LIMIT 1
")->fetch_assoc();
if (!$user) {
    fwrite(STDERR, "User admin lokal tidak ditemukan.\n");
    exit(1);
}

$baseUrl = 'http://localhost/DatabaseTKM/';
$cookie = $outputDir . DIRECTORY_SEPARATOR . 'manual_cookie.txt';
file_put_contents($cookie, '');

function http_request(string $method, string $url, string $cookie, array $post = []): string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => '',
        CURLOPT_USERAGENT => 'BatchApprovalManualGenerator/1.0',
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($status >= 400 || $body === '') {
        throw new RuntimeException('HTTP request gagal: ' . $url . ' status=' . $status . ' error=' . $error);
    }
    return $body;
}

function inject_snapshot_helpers(string $html, string $baseUrl, string $mode = ''): string
{
    $helpers = <<<HTML
<base href="{$baseUrl}">
<style>
    body { background: #eef2f7 !important; }
    .main-sidebar, .main-header, .control-sidebar, .content-header .breadcrumb { display: none !important; }
    .content-wrapper, .main-footer { margin-left: 0 !important; }
    .content-wrapper { padding-top: 8px !important; }
    .card, .donation-upload-panel { box-shadow: 0 12px 30px rgba(15,23,42,.10) !important; }
    .modal-backdrop { display: none !important; }
</style>
HTML;

    $modalScript = '';
    if ($mode !== '') {
        $modalScript = <<<HTML
<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        var selector = '';
        if ('{$mode}' === 'upload') selector = '#modal-donation-upload';
        if ('{$mode}' === 'bulk-astri') selector = '[id^="modal-bulk-astri-"]';
        if ('{$mode}' === 'reject') selector = '#modal-donation-reject';
        if ('{$mode}' === 'po') selector = '#modal-donation-po-invoice';
        var modal = selector ? document.querySelector(selector) : null;
        if (!modal) return;
        modal.classList.add('show');
        modal.style.display = 'block';
        modal.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        if ('{$mode}' === 'upload') {
            var title = document.querySelector('#donation_upload_doc_name');
            if (title) title.textContent = 'Screenshot Evidence Upload DRM di Astri';
        }
        if ('{$mode}' === 'reject') {
            var titleReject = document.querySelector('#donation_reject_title');
            var docReject = document.querySelector('#donation_reject_doc_name');
            var remarkReject = document.querySelector('#donation_reject_remark');
            if (titleReject) titleReject.textContent = 'Reject Dokumen';
            if (docReject) docReject.textContent = 'Form Free Wifi & KTP';
            if (remarkReject) remarkReject.value = 'Contoh: dokumen perlu revisi dari Astri.';
        }
    }, 700);
});
</script>
HTML;
    }

    if (stripos($html, '<head>') !== false) {
        $html = preg_replace('/<head>/i', '<head>' . $helpers, $html, 1) ?: $html;
    } else {
        $html = $helpers . $html;
    }

    if ($modalScript !== '') {
        $html = str_ireplace('</body>', $modalScript . '</body>', $html);
    }
    return $html;
}

function chrome_path(): string
{
    $paths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    throw new RuntimeException('Chrome/Edge tidak ditemukan.');
}

function run_command(array $parts): void
{
    $command = implode(' ', array_map(static fn($part) => '"' . str_replace('"', '\"', (string) $part) . '"', $parts));
    exec($command . ' 2>&1', $output, $code);
    if ($code !== 0) {
        throw new RuntimeException("Command gagal ({$code}): {$command}\n" . implode("\n", $output));
    }
}

function screenshot(string $chrome, string $htmlFile, string $pngFile, int $width = 1440, int $height = 980): void
{
    run_command([
        $chrome,
        '--headless=new',
        '--disable-gpu',
        '--hide-scrollbars',
        '--no-first-run',
        '--disable-extensions',
        '--window-size=' . $width . ',' . $height,
        '--screenshot=' . $pngFile,
        'file:///' . str_replace('\\', '/', $htmlFile),
    ]);
}

http_request('GET', $baseUrl . 'Auth', $cookie);
http_request('POST', $baseUrl . 'Auth', $cookie, [
    'username' => (string) $user['username_user'],
    'pass' => (string) $user['password_user'],
]);

$indexHtml = http_request('GET', $baseUrl . 'Batch_Approval_MyRep', $cookie);
$detailHtml = http_request('GET', $baseUrl . 'Batch_Approval_MyRep/detail/14432', $cookie);

$snapshots = [
    '01_index.html' => inject_snapshot_helpers($indexHtml, $baseUrl),
    '02_detail.html' => inject_snapshot_helpers($detailHtml, $baseUrl),
    '03_upload_modal.html' => inject_snapshot_helpers($detailHtml, $baseUrl, 'upload'),
    '04_bulk_astri.html' => inject_snapshot_helpers($detailHtml, $baseUrl, 'bulk-astri'),
    '05_reject_modal.html' => inject_snapshot_helpers($detailHtml, $baseUrl, 'reject'),
    '06_po_modal.html' => inject_snapshot_helpers($detailHtml, $baseUrl, 'po'),
];

foreach ($snapshots as $name => $html) {
    file_put_contents($snapshotDir . DIRECTORY_SEPARATOR . $name, $html);
}

$chrome = chrome_path();
$screenshots = [
    ['01_index.html', '01_index.png', 'Daftar Batch Approval'],
    ['02_detail.html', '02_detail.png', 'Detail Batch Approval'],
    ['03_upload_modal.html', '03_upload_modal.png', 'Modal Upload Dokumen'],
    ['04_bulk_astri.html', '04_bulk_astri.png', 'Bulk Astri'],
    ['05_reject_modal.html', '05_reject_modal.png', 'Modal Reject'],
    ['06_po_modal.html', '06_po_modal.png', 'Modal PO/Invoice'],
];
foreach ($screenshots as [$htmlName, $pngName]) {
    screenshot($chrome, $snapshotDir . DIRECTORY_SEPARATOR . $htmlName, $screenshotDir . DIRECTORY_SEPARATOR . $pngName);
}

$today = date('d F Y');
$manualHtml = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Manual Book Batch Approval MyRep</title>
<style>
    @page { size: A4; margin: 14mm 12mm; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #172033; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 1.45; background: #fff; }
    h1, h2, h3 { margin: 0 0 8px; color: #0f172a; }
    h1 { font-size: 28px; line-height: 1.12; }
    h2 { margin-top: 18px; padding-bottom: 5px; border-bottom: 2px solid #1787b7; font-size: 18px; }
    h3 { margin-top: 12px; font-size: 14px; }
    p { margin: 0 0 8px; }
    ul, ol { margin: 6px 0 10px 20px; padding: 0; }
    li { margin: 3px 0; }
    table { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
    th, td { border: 1px solid #d9e2ec; padding: 6px 7px; vertical-align: top; }
    th { background: #e8f4f8; color: #0f3f52; text-align: left; }
    .cover { min-height: 245mm; display: flex; flex-direction: column; justify-content: center; padding: 20mm 12mm; background: linear-gradient(135deg, #eef9f7, #f8fbff); }
    .eyebrow { color: #138aa7; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
    .subtitle { margin-top: 10px; max-width: 600px; color: #475569; font-size: 14px; }
    .meta { margin-top: 24px; color: #334155; }
    .page-break { page-break-before: always; }
    .flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 10px 0 14px; }
    .step { min-height: 58px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; }
    .step b { display: block; color: #0f566b; }
    .note { padding: 9px 10px; border-left: 4px solid #14a37f; background: #eefcf8; margin: 10px 0; }
    .warning { border-left-color: #dc3545; background: #fff1f2; }
    .shot { margin: 10px 0 16px; page-break-inside: avoid; }
    .shot img { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 8px 18px rgba(15, 23, 42, .12); }
    .caption { margin-top: 5px; color: #475569; font-size: 11px; }
    .pill { display: inline-block; padding: 2px 6px; border-radius: 10px; background: #e0f2fe; color: #075985; font-weight: 700; }
    .small { color: #64748b; font-size: 11px; }
</style>
</head>
<body>
<section class="cover">
    <div class="eyebrow">Manual Book MyRep</div>
    <h1>Batch Approval Donasi Astri & Zeyn</h1>
    <p class="subtitle">Panduan operasional modul Batch Approval terbaru, termasuk flow kerja, staging, dokumen, approval SITAC/Finance, update Astri, PO, Invoice, dan aturan revisi dokumen.</p>
    <div class="meta">
        <p><b>Modul:</b> Batch_Approval_MyRep</p>
        <p><b>Versi dokumen:</b> {$today}</p>
        <p><b>Tujuan utama:</b> mengunci proses finance agar dana tidak cair sebelum dokumen pendukung lengkap dan meminimalisir keterlambatan input Astri serta invoice.</p>
    </div>
</section>

<section class="page-break">
    <h2>1. Ringkasan Modul</h2>
    <p>Batch Approval MyRep mengatur pengajuan donasi sejak input batch dari Area/SITAC HO, verifikasi dokumen Zeyn, approval internal SITAC dan Finance, pencairan donasi, upload dokumen pasca bayar, submit Astri, PO Donasi, hingga Invoice Donasi.</p>
    <div class="note">Prinsip kontrol utama: finance baru dapat mencairkan dana setelah dokumen pra-finance lengkap dan approved. Setelah pembayaran, dokumen pasca bayar juga dikunci melalui approval sebelum proses final Astri, PO, dan invoice.</div>

    <h2>2. Flow Kerja Utama</h2>
    <div class="flow">
        <div class="step"><b>1. Input Batch</b>Area atau SITAC HO input batch, nomor batch Astri, tanggal approval, nominal donasi, dan data penerima.</div>
        <div class="step"><b>2. Dokumen Tahap 1</b>Area upload dokumen pra-finance Zeyn. Screenshot memakai upload gambar.</div>
        <div class="step"><b>3. Approval SITAC</b>SITAC HO approve/reject per dokumen atau approve all.</div>
        <div class="step"><b>4. Approval Finance</b>Finance HO approve/reject dokumen wajib setelah SITAC approved.</div>
        <div class="step"><b>5. Ajukan Finance</b>SITAC HO mengajukan pencairan memakai Nominal Approval EMR.</div>
        <div class="step"><b>6. Set Released</b>SITAC HO isi tanggal release, nominal release, dan bukti transfer gambar.</div>
        <div class="step"><b>7. Dokumen Tahap 2</b>Area upload dokumen setelah pembayaran, lalu SITAC dan Finance approve.</div>
        <div class="step"><b>8. Astri, PO, Invoice</b>SITAC update Astri per dokumen/bulk. Jika semua Astri approved, lanjut PO dan Invoice.</div>
    </div>

    <h2>3. Screenshot Menu</h2>
    <div class="shot"><img src="screenshots/01_index.png"><div class="caption">Daftar Batch Approval dan ringkasan staging.</div></div>
    <div class="shot"><img src="screenshots/02_detail.png"><div class="caption">Halaman detail Batch Approval dengan ringkasan, pencairan, SLA, dan dokumen.</div></div>
</section>

<section class="page-break">
    <h2>4. Staging dan Kondisi</h2>
    <table>
        <tr><th>Staging</th><th>Kondisi Pemakaian</th><th>Aksi Berikutnya</th></tr>
        <tr><td>NY Dokumen Tahap 1</td><td>Batch sudah dibuat dan menunggu upload dokumen pra-finance.</td><td>Area upload dokumen tahap 1.</td></tr>
        <tr><td>On Review Dokumen Tahap 1</td><td>Dokumen tahap 1 sudah full upload, menunggu review SITAC.</td><td>SITAC approve/reject.</td></tr>
        <tr><td>On Review Finance Dokumen Tahap 1</td><td>Dokumen tahap 1 sudah approved SITAC, menunggu approval Finance.</td><td>Finance approve/reject.</td></tr>
        <tr><td>Approved Finance Dokumen Tahap 1</td><td>Dokumen tahap 1 sudah approved SITAC dan Finance.</td><td>SITAC klik Ajukan ke Finance.</td></tr>
        <tr><td>Menunggu Pembayaran Finance</td><td>Pengajuan finance sudah dilakukan.</td><td>Set Released setelah dana cair.</td></tr>
        <tr><td>Donasi Dibayarkan</td><td>Finance sudah release pembayaran.</td><td>Area upload dokumen tahap 2.</td></tr>
        <tr><td>On Review Dokumen Tahap 2</td><td>Dokumen tahap 2 sudah full upload, menunggu review SITAC.</td><td>SITAC approve/reject.</td></tr>
        <tr><td>On Review Finance Dokumen Tahap 2</td><td>Dokumen tahap 2 sudah approved SITAC, menunggu approval Finance.</td><td>Finance approve/reject.</td></tr>
        <tr><td>Menunggu Submit Astri</td><td>Dokumen tahap 2 sudah approved Finance dan siap update Astri.</td><td>Update Astri per dokumen atau bulk.</td></tr>
        <tr><td>On Review Astri</td><td>Ada dokumen dikirim/review Astri atau ada dokumen Astri rejected.</td><td>Jika rejected, Area upload revisi dan SITAC review ulang.</td></tr>
        <tr><td>Approved Astri</td><td>Semua dokumen wajib tahap 1 dan tahap 2 sudah approved Astri, tanpa rejected.</td><td>Tambah PO Donasi.</td></tr>
        <tr><td>PO Donasi</td><td>PO Donasi sudah dibuat.</td><td>Lengkapi invoice.</td></tr>
        <tr><td>Invoice</td><td>Invoice donasi sudah dibuat.</td><td>Monitoring selesai untuk flow donasi.</td></tr>
        <tr><td>Hold / Rejected</td><td>Batch ditahan atau ditolak pada level batch.</td><td>Perlu tindak lanjut manual sesuai remark.</td></tr>
    </table>
    <div class="warning note">Jika salah satu dokumen Astri berstatus rejected, staging tidak boleh menjadi Approved Astri walaupun dokumen lain sudah approved.</div>
</section>

<section class="page-break">
    <h2>5. Dokumen Wajib</h2>
    <h3>Dokumen Tahap 1 Pra-Finance Zeyn</h3>
    <ol>
        <li>Screenshot Evidence Upload DRM di Astri <span class="pill">gambar</span></li>
        <li>Screenshot Buku Rekening Penerima Dana <span class="pill">gambar</span></li>
        <li>Surat Ijin RT/RW</li>
        <li>Form Cluster Survey</li>
        <li>BAP Open</li>
        <li>BAP SND & SND Kasar</li>
        <li>Cluster Approval</li>
        <li>Perjanjian Donasi & Pemberian Izin</li>
        <li>KTP Penerima Donasi</li>
        <li>Dokumentasi CSR Banner</li>
        <li>Dokumentasi Banner Pre Sales</li>
        <li>Dokumentasi Sosialisasi Warga</li>
        <li>Form Free Wifi & KTP <span class="pill">opsional</span></li>
    </ol>
    <h3>Dokumen Tahap 2 Setelah Pembayaran</h3>
    <ol>
        <li>Kwitansi</li>
        <li>Bukti Transfer</li>
        <li>Bukti Penyerahan Dana</li>
    </ol>
    <p class="small">Upload dokumen maksimal 20 MB. Screenshot hanya menerima JPG, JPEG, atau PNG. Dokumen tahap 2 menggunakan PDF.</p>

    <h2>6. Upload dan Review Dokumen</h2>
    <div class="shot"><img src="screenshots/03_upload_modal.png"><div class="caption">Modal upload dokumen satuan dengan drag and drop.</div></div>
    <div class="shot"><img src="screenshots/05_reject_modal.png"><div class="caption">Reject dokumen memakai modal remark wajib.</div></div>
    <ul>
        <li>Area dan Super Admin dapat upload dokumen sesuai akses.</li>
        <li>Dokumen approved hanya dapat replace oleh SITAC HO, kecuali revisi akibat Astri rejected.</li>
        <li>Reject SITAC dan Finance wajib remark.</li>
        <li>History dokumen menyimpan upload, reupload, approve, reject, finance approval, dan update Astri.</li>
    </ul>
</section>

<section class="page-break">
    <h2>7. Bulk Astri dan Revisi Astri</h2>
    <div class="shot"><img src="screenshots/04_bulk_astri.png"><div class="caption">Bulk Astri untuk update tanggal submit, status, tanggal approved, dan remark.</div></div>
    <ul>
        <li>Bulk Astri muncul untuk dokumen yang sudah approved internal.</li>
        <li>Jika status Astri dipilih REJECTED, remark wajib diisi.</li>
        <li>Jika Astri reject dokumen tahap 1 atau tahap 2, status cluster tetap On Review Astri.</li>
        <li>Area upload revisi hanya pada dokumen rejected Astri.</li>
        <li>Setelah upload revisi Astri, dokumen cukup direview ulang oleh SITAC. Approval Finance lama tetap dipertahankan.</li>
        <li>Astri status tetap REJECTED sampai SITAC memperbarui status Astri berikutnya, agar jejak history tetap jelas.</li>
    </ul>

    <h2>8. Pencairan, PO, dan Invoice</h2>
    <ul>
        <li>Ajukan ke Finance memakai Nominal Approval EMR.</li>
        <li>Tanggal pengajuan finance diambil dari waktu tombol diklik.</li>
        <li>Set Released memakai tanggal release, nominal release, dan bukti transfer gambar.</li>
        <li>PO Donasi terhubung dengan bowheer PT EMR - DONASI, 1 term 100%.</li>
        <li>Form PO/Invoice dibuka lewat modal, bukan inline di halaman.</li>
    </ul>
    <div class="shot"><img src="screenshots/06_po_modal.png"><div class="caption">Modal PO/Invoice Donasi.</div></div>
</section>

<section class="page-break">
    <h2>9. Hak Akses</h2>
    <table>
        <tr><th>Role</th><th>Akses Utama</th></tr>
        <tr><td>Admin Area</td><td>Input batch dan upload dokumen/revisi sesuai city mapping.</td></tr>
        <tr><td>SITAC HO</td><td>Input batch, edit batch, approve/reject SITAC, update Astri, set hold/rejected, set released, PO/Invoice.</td></tr>
        <tr><td>Finance HO</td><td>Approve/reject dokumen finance. Mapping dapat diatur di SuperAdmin_MyRep_CityMapping dan fallback ke semua Finance HO.</td></tr>
        <tr><td>Super Admin</td><td>Akses penuh untuk konfigurasi, monitoring, dan tindakan administratif.</td></tr>
    </table>

    <h2>10. Checklist Operasional</h2>
    <ol>
        <li>Pastikan data penerima donasi lengkap.</li>
        <li>Pastikan nomor batch Astri, tanggal approval, dan nominal donasi sudah benar saat input awal.</li>
        <li>Upload seluruh dokumen tahap 1, lalu SITAC dan Finance approve.</li>
        <li>Klik Ajukan ke Finance, lalu Set Released setelah pembayaran cair.</li>
        <li>Upload seluruh dokumen tahap 2, lalu SITAC dan Finance approve.</li>
        <li>Update Astri untuk semua dokumen tahap 1 dan tahap 2.</li>
        <li>Jika ada rejected Astri, Area upload revisi dan SITAC review ulang dokumen tersebut.</li>
        <li>Jika semua Astri approved, lock Approved Astri dan input PO/Invoice.</li>
    </ol>
</section>
</body>
</html>
HTML;

$manualHtmlPath = $outputDir . DIRECTORY_SEPARATOR . 'manual_book_batch_approval_myrep.html';
$manualPdfPath = $outputDir . DIRECTORY_SEPARATOR . 'manual_book_batch_approval_myrep.pdf';
file_put_contents($manualHtmlPath, $manualHtml);

run_command([
    $chrome,
    '--headless=new',
    '--disable-gpu',
    '--no-first-run',
    '--disable-extensions',
    '--print-to-pdf=' . $manualPdfPath,
    'file:///' . str_replace('\\', '/', $manualHtmlPath),
]);

echo "Manual HTML: {$manualHtmlPath}\n";
echo "Manual PDF : {$manualPdfPath}\n";
echo "Screenshots: {$screenshotDir}\n";
