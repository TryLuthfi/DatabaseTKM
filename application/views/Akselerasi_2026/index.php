<?php
defined('BASEPATH') or exit('No direct script access allowed');
$dashboard = is_array($dashboard ?? null) ? $dashboard : [];
$error = (string) ($error ?? '');
$escape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$from = (string) ($dashboard['from'] ?? '2026-11-16');
$to = (string) ($dashboard['to'] ?? date('Y-m-d'));
?>
<link rel="stylesheet" href="<?= base_url('assets/dist/css/akselerasi-2026.css') ?>">
<div class="content-wrapper aks-page" id="akselerasi-dashboard">
    <section class="content-header aks-heading">
        <div><span class="aks-eyebrow">DATABASE ALL PO</span><h1>TARGET INVOICE AKSELERASI 2026</h1><p>Monitoring target November–Desember 2026</p></div>
        <span class="aks-lock"><i class="fas fa-lock" aria-hidden="true"></i> Target terkunci</span>
    </section>
    <section class="content aks-shell">
        <form class="aks-datebar" method="get" action="<?= site_url('Akselerasi_2026') ?>" id="aks-date-form">
            <div class="aks-date-field"><span>Tanggal invoice mulai</span><strong>16 November 2026</strong></div>
            <div class="aks-date-field"><label for="aks-end-date">Sampai tanggal</label><input class="form-control" id="aks-end-date" name="end_date" type="date" min="2026-11-16" value="<?= $escape($to) ?>" required></div>
            <button class="btn aks-button" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Terapkan periode</button>
            <a class="aks-source" href="<?= site_url('PO_Monitor') ?>"><i class="fas fa-external-link-alt" aria-hidden="true"></i> Claim invoice di Target 250 M</a>
        </form>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= $escape($error) ?></div>
        <?php endif; ?>
        <noscript><div class="alert alert-warning">Aktifkan JavaScript untuk melihat angka, pencarian, dan ringkasan dashboard.</div></noscript>
        <div id="aks-runtime-error" class="alert alert-danger" role="alert" hidden>Dashboard belum dapat ditampilkan. Muat ulang halaman untuk mencoba kembali.</div>
        <div class="aks-kpis" aria-label="Ringkasan target">
            <?php foreach ([['target', 'TOTAL TARGET', 'fa-bullseye', 'blue'], ['achievement', 'ACHIEVEMENT', 'fa-file-invoice-dollar', 'teal'], ['remaining', 'SISA TARGET', 'fa-layer-group', 'blue'], ['percentage', 'CAPAIAN TOTAL', 'fa-chart-line', 'teal']] as $card): ?>
                <article class="aks-kpi aks-kpi--<?= $card[3] ?>"><span class="aks-kpi-icon"><i class="fas <?= $card[2] ?>" aria-hidden="true"></i></span><div><h2><?= $card[1] ?></h2><strong data-aks-total="<?= $card[0] ?>">—</strong><?php if ($card[0] === 'achievement'): ?><small id="aks-period"></small><?php endif; ?></div></article>
            <?php endforeach; ?>
        </div>
        <div class="aks-progress-grid">
            <?php foreach ([['regular', 'Target Reguler', 'fa-bullseye'], ['acceleration', 'Akselerasi CO', 'fa-bolt']] as $panel): ?>
                <article class="aks-progress-panel aks-progress-panel--<?= $panel[0] ?>">
                    <h2><i class="fas <?= $panel[2] ?>" aria-hidden="true"></i> <?= $panel[1] ?></h2>
                    <div class="aks-progress-stats"><div><span>Target</span><strong data-aks-total="<?= $panel[0] ?>">—</strong></div><div><span>Tercapai</span><strong data-aks-total="<?= $panel[0] ?>_achievement">—</strong></div><div><span>Sisa</span><strong data-aks-total="<?= $panel[0] ?>_remaining">—</strong></div></div>
                    <div class="aks-progress-bottom"><div class="aks-track" id="aks-<?= $panel[0] ?>-progress" role="progressbar" aria-label="Capaian <?= $panel[1] ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div><strong data-aks-total="<?= $panel[0] ?>_percentage">—</strong></div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="aks-info"><i class="fas fa-info-circle" aria-hidden="true"></i> Achievement neto memenuhi target reguler terlebih dahulu, kemudian Akselerasi CO, pada bowheer dan type yang sama. Koreksi negatif tetap diperhitungkan.</p>
        <section class="aks-panel" aria-labelledby="aks-summary-heading">
            <header class="aks-panel-head"><h2 id="aks-summary-heading"><i class="fas fa-users" aria-hidden="true"></i> Summary per PIC</h2><span class="aks-pic-note">PIC penanggung jawab target</span></header>
            <div class="aks-table-scroll" role="region" aria-label="Summary per PIC" tabindex="0"><table class="aks-table aks-summary-table"><thead><tr><th scope="col">PIC</th><th scope="col" class="aks-number">Total Target</th><th scope="col" class="aks-number">Achievement</th><th scope="col" class="aks-number">Sisa Target</th><th scope="col" class="aks-number">Capaian</th></tr></thead><tbody id="aks-summary-body"></tbody><tfoot id="aks-summary-footer"></tfoot></table></div>
        </section>
        <section class="aks-panel" aria-labelledby="aks-detail-heading">
            <header class="aks-panel-head"><h2 id="aks-detail-heading"><i class="fas fa-table" aria-hidden="true"></i> Rincian per Bowheer</h2><div class="aks-controls"><div class="aks-search"><label class="sr-only" for="aks-search">Cari PIC, bowheer, atau type</label><i class="fas fa-search" aria-hidden="true"></i><input id="aks-search" class="form-control" type="search" placeholder="Cari PIC / bowheer / type…" autocomplete="off"></div><label class="sr-only" for="aks-pic">Filter PIC</label><select id="aks-pic" class="form-control"><option value="">Semua PIC</option></select><button id="aks-reset" class="btn aks-button-light" type="button"><i class="fas fa-undo" aria-hidden="true"></i> Reset</button><button id="aks-export" class="btn aks-button-light" type="button" disabled><i class="fas fa-download" aria-hidden="true"></i> CSV</button></div></header>
            <div class="aks-resultbar"><span id="aks-count" role="status" aria-live="polite">Memuat data…</span><span>Nomor urut dan total seluruh hasil diperbarui otomatis saat pencarian.</span></div>
            <div class="aks-table-scroll" role="region" aria-label="Rincian target per bowheer" tabindex="0"><table class="aks-table aks-detail-table"><thead><tr><th scope="col">No.</th><th scope="col">PIC</th><th scope="col">Bowheer</th><th scope="col">Type</th><th scope="col" class="aks-number">Target Reguler</th><th scope="col" class="aks-number">Akselerasi CO</th><th scope="col" class="aks-number">Achievement</th><th scope="col" class="aks-number">Sisa Total</th><th scope="col" class="aks-number">Capaian</th></tr></thead><tbody id="aks-detail-body"></tbody><tfoot id="aks-detail-footer"></tfoot></table></div>
        </section>
        <section class="aks-panel aks-unmapped" id="aks-unmapped" hidden aria-labelledby="aks-unmapped-heading"><header class="aks-panel-head"><div><h2 id="aks-unmapped-heading"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Achievement belum dipetakan</h2><p>Invoice berikut belum masuk target di atas. Periksa bowheer dan type pada sumber Target 250 M.</p></div><strong id="aks-unmapped-total"></strong></header><div class="aks-table-scroll" role="region" aria-label="Achievement belum dipetakan" tabindex="0"><table class="aks-table"><thead><tr><th scope="col">Bowheer</th><th scope="col">Type</th><th scope="col" class="aks-number">Achievement</th></tr></thead><tbody id="aks-unmapped-body"></tbody></table></div></section>
        <footer class="aks-footnote"><span id="aks-source-total"></span><span>Achievement neto · Sumber invoice: Target 250 M · Target terkunci</span></footer>
    </section>
</div>
<script id="aks-dashboard-data" type="application/json"><?= json_encode(['dashboard' => $dashboard, 'error' => $error], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script src="<?= base_url('assets/dist/js/akselerasi-2026.js') ?>"></script>
