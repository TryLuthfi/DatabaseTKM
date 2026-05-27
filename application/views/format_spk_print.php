<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Print SPK</title>
  <link rel="icon" type="image/png" href="<?= base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png') ?>">
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png') ?>">
  <style>body{font-family:Arial,sans-serif;font-size:13px;padding:32px;}table{width:100%;border-collapse:collapse}td{padding:6px;border:1px solid #ddd}</style>
</head>
<body onload="window.print()">
  <h3>SURAT PERINTAH KERJA (SPK)</h3>
  <p><strong>No SPK:</strong> <?= htmlspecialchars((string) $spk['nomor_spk'], ENT_QUOTES) ?></p>
  <p><strong>No PKS:</strong> <?= htmlspecialchars((string) $spk['nomor_pks'], ENT_QUOTES) ?></p>
  <table>
    <tr><td>PIC PKS</td><td><?= htmlspecialchars((string) $spk['pic_pks'], ENT_QUOTES) ?></td></tr>
    <tr><td>Bowheer</td><td><?= htmlspecialchars((string) $spk['bowheer'], ENT_QUOTES) ?></td></tr>
    <tr><td>Project</td><td><?= htmlspecialchars((string) $spk['project_name'], ENT_QUOTES) ?></td></tr>
    <tr><td>Nilai SPK</td><td><?= number_format((float) $spk['nilai_spk'], 0, ',', '.') ?></td></tr>
    <tr><td>TOC SPK</td><td><?= htmlspecialchars((string) $spk['toc_spk'], ENT_QUOTES) ?></td></tr>
    <tr><td>Tanggal Amandemen 1</td><td><?= htmlspecialchars((string) $spk['tanggal_amandemen_1'], ENT_QUOTES) ?></td></tr>
    <tr><td>Nilai Amandemen 1</td><td><?= number_format((float) $spk['nilai_amandemen_1'], 0, ',', '.') ?></td></tr>
    <tr><td>Tanggal Amandemen 2</td><td><?= htmlspecialchars((string) $spk['tanggal_amandemen_2'], ENT_QUOTES) ?></td></tr>
    <tr><td>Nilai Amandemen 2</td><td><?= number_format((float) $spk['nilai_amandemen_2'], 0, ',', '.') ?></td></tr>
    <tr><td>Status SPK</td><td><?= htmlspecialchars((string) $spk['status_spk'], ENT_QUOTES) ?></td></tr>
  </table>
</body>
</html>
