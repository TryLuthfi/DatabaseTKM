<style>
    .comply-preview-wrap {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        min-height: calc(100vh - 57px);
        padding: 1.5rem 0 2rem;
    }

    .comply-preview-shell {
        width: min(1120px, calc(100% - 24px));
        margin: 0 auto;
    }

    .comply-preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .comply-preview-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .comply-preview-subtitle {
        font-size: .82rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        margin-top: .25rem;
    }

    .comply-print-page {
        background: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        padding: 16px;
        margin-bottom: 18px;
    }

    .comply-print-header,
    .comply-print-section-head {
        width: 100%;
        border-collapse: collapse;
    }

    .comply-print-header td,
    .comply-print-section-head td,
    .comply-print-tile {
        border: 1px solid #111827;
    }

    .comply-print-header__logos {
        width: 36%;
        text-align: center;
        vertical-align: middle;
        padding: 8px;
    }

    .comply-print-header__logos img {
        max-height: 44px;
        max-width: 150px;
        object-fit: contain;
        vertical-align: middle;
    }

    .comply-print-header__project {
        margin-top: 6px;
        font-size: 1.7rem;
        font-weight: 800;
        color: #0f172a;
    }

    .comply-print-header__center {
        width: 14%;
        font-size: 1.35rem;
        font-weight: 800;
        text-align: center;
        vertical-align: middle;
        padding: 8px;
    }

    .comply-print-header__meta {
        width: 50%;
        padding: 0;
    }

    .comply-print-header__meta table {
        width: 100%;
        border-collapse: collapse;
    }

    .comply-print-header__meta td {
        border: 1px solid #111827;
        padding: 4px 6px;
        font-size: .9rem;
    }

    .comply-print-section-head {
        margin-top: 10px;
    }

    .comply-print-section-head td {
        padding: 6px 8px;
    }

    .comply-print-section-head__title {
        width: 18%;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .comply-print-section-head__info {
        font-size: .92rem;
    }

    .comply-print-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 10px;
        margin-top: 12px;
    }

    .comply-print-tile {
        padding: 0;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .comply-print-tile__image {
        height: 220px;
        border-bottom: 1px solid #111827;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        background: #fff;
    }

    .comply-print-tile__image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .comply-print-tile__desc,
    .comply-print-tile__caption {
        text-align: center;
        padding: 4px 6px;
        font-size: .8rem;
    }

    .comply-print-tile__desc {
        font-weight: 800;
        border-bottom: 1px solid #111827;
    }

    .comply-print-empty {
        color: #64748b;
        font-size: .95rem;
        text-align: center;
        padding: 1.5rem;
        border: 1px dashed #cbd5e1;
    }

    @media print {
        .main-header,
        .main-sidebar,
        .content-header,
        .main-footer,
        .comply-preview-toolbar {
            display: none !important;
        }

        .content-wrapper,
        .comply-preview-wrap {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            min-height: auto !important;
        }

        .comply-preview-shell {
            width: 100% !important;
            margin: 0 !important;
        }

        .comply-print-page {
            box-shadow: none;
            border: 0;
            margin: 0 0 10px;
            padding: 8px;
            page-break-after: always;
        }

        .comply-print-page:last-child {
            page-break-after: auto;
        }
    }
</style>

<?php
$logoTkm = base_url('assets/dist/img/solid%20logo%20tkm%20landscape%20transparent.png');
$logoMyrep = base_url('assets/dist/img/logoweb.png');
$clusterRegion = (string) ($cluster['regional_name'] ?? '-');
$clusterOlt = (string) ($cluster['nama_olt'] ?? '-');
$clusterName = (string) ($cluster['cluster_name'] ?? '-');
$clusterCode = (string) (!empty($cluster['cluster_code']) ? $cluster['cluster_code'] : ($cluster['id_myrep_cluster'] ?? '-'));
$printSections = [
    [
        'type' => 'daily',
        'info_title' => 'Foto Daily Progress',
        'groups' => (array) ($dailyGroups ?? []),
    ],
    [
        'type' => 'comply',
        'info_title' => 'Foto Comply Approved',
        'groups' => (array) ($complyGroups ?? []),
    ],
];

$resolvePreviewScopeTitle = static function ($fallbackScope, array $photos = []) {
    $fallbackScope = strtoupper(trim((string) $fallbackScope));
    if ($fallbackScope === 'SUBFEEDER') {
        return 'SUBFEEDER';
    }

    foreach ($photos as $photo) {
        $text = strtoupper(trim(implode(' ', [
            (string) ($photo['caption'] ?? ''),
            (string) ($photo['meta_line'] ?? ''),
            (string) ($photo['comply_label'] ?? ''),
            (string) ($photo['description'] ?? ''),
        ])));
        if (strpos($text, 'SUBFEEDER') !== false) {
            return 'SUBFEEDER';
        }
    }

    return 'CLUSTER';
};

$appendPreviewPhotoItemToRemark = static function ($remark, array $photo) {
    $remark = trim((string) $remark);
    $itemName = trim((string) ($photo['item_name'] ?? ''));
    if ($remark === '' || $itemName === '') {
        return $remark;
    }

    if (stripos($remark, $itemName) !== false) {
        return $remark;
    }

    return $remark . ' - ' . $itemName;
};
?>

<div class="content-wrapper">
    <div class="comply-preview-wrap">
        <div class="comply-preview-shell">
            <div class="comply-preview-toolbar">
                <div>
                    <div class="comply-preview-title">Preview Daily Progress & Foto Comply</div>
                    <div class="comply-preview-subtitle"><?= htmlspecialchars($clusterName) ?></div>
                </div>
                <div class="d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap; justify-content:flex-end;">
                    <?php if (!empty($allCategoryTitles)): ?>
                        <form method="get" action="<?= base_url('Implementasi_BOQ_MyRep/previewComplyPdf/' . (int) ($cluster['id_myrep_cluster'] ?? 0)) ?>" class="d-flex align-items-center" style="gap:.4rem;">
                            <label for="comply-category-select" class="mb-0 font-weight-bold">Kategori:</label>
                            <select id="comply-category-select" name="category" class="form-control form-control-sm" style="min-width:260px;">
                                <option value="">Semua Kategori</option>
                                <?php foreach ((array) $allCategoryTitles as $title): ?>
                                    <option value="<?= htmlspecialchars((string) $title) ?>" <?= strcasecmp((string) $title, (string) ($selectedCategory ?? '')) === 0 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $title) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Terapkan</button>
                        </form>
                    <?php endif; ?>
                    <?php if (!empty($tcpdfAvailable)): ?>
                        <a href="<?= htmlspecialchars((string) ($pdfUrl ?? '#')) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i>Buka PDF
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary btn-sm js-comply-print">
                        <i class="fas fa-print mr-1"></i>Print / Save PDF
                    </button>
                </div>
            </div>

            <?php foreach ($printSections as $printSection): ?>
                <?php foreach ((array) $printSection['groups'] as $sectionTitle => $photos): ?>
                    <?php $photoChunks = array_chunk(array_values($photos), 8); ?>
                    <?php foreach ($photoChunks as $photoChunk): ?>
                        <?php
                        $scopeTitle = $resolvePreviewScopeTitle((string) $sectionTitle, (array) $photoChunk);
                        $documentTitle = (string) ($printSection['type'] ?? '') === 'daily'
                            ? ('IMPLE ' . $scopeTitle)
                            : (string) $sectionTitle;
                        ?>
                        <div class="comply-print-page">
                            <table class="comply-print-header">
                                <tr>
                                    <td class="comply-print-header__logos">
                                        <img src="<?= htmlspecialchars($logoTkm) ?>" alt="TKM Logo">
                                        <img src="<?= htmlspecialchars($logoMyrep) ?>" alt="MyRep Logo">
                                        <div class="comply-print-header__project">EMR FTTH PROJECT</div>
                                    </td>
                                    <td class="comply-print-header__center"><?= nl2br(htmlspecialchars(str_replace(' ', "\n", $documentTitle))) ?></td>
                                    <td class="comply-print-header__meta">
                                        <table>
                                            <tr><td><strong>Region</strong></td><td><?= htmlspecialchars($clusterRegion) ?></td></tr>
                                            <tr><td><strong>OLT Name</strong></td><td><?= htmlspecialchars($clusterOlt !== '' ? $clusterOlt : '-') ?></td></tr>
                                            <tr><td><strong>Cluster Name</strong></td><td><?= htmlspecialchars($clusterName) ?></td></tr>
                                            <tr><td><strong>Cluster ID</strong></td><td><?= htmlspecialchars($clusterCode) ?></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table class="comply-print-section-head">
                                <tr>
                                    <td class="comply-print-section-head__title"><?= htmlspecialchars($scopeTitle) ?></td>
                                    <td class="comply-print-section-head__info">
                                        <strong><?= htmlspecialchars((string) $printSection['info_title']) ?></strong><br>
                                        Cluster: <?= htmlspecialchars($clusterName) ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="comply-print-grid">
                                <?php foreach ($photoChunk as $photo): ?>
                                    <?php
                                    $photoUrl = trim((string) ($photo['image_url'] ?? ''));
                                    if ($photoUrl === '') {
                                        $relativePhotoPath = ltrim(str_replace('\\', '/', (string) ($photo['file_path'] ?? '')), '/');
                                        $photoUrl = base_url($relativePhotoPath);
                                        if ($relativePhotoPath !== '' && strpos($relativePhotoPath, '..') === false) {
                                            $fullPhotoPath = FCPATH . $relativePhotoPath;
                                            if (is_file($fullPhotoPath)) {
                                                $photoUrl .= '?v=' . rawurlencode((string) filemtime($fullPhotoPath) . '-' . (string) filesize($fullPhotoPath));
                                            }
                                        }
                                    }
                                    $description = trim((string) ($photo['description'] ?? '')) !== ''
                                        ? (string) $photo['description']
                                        : (trim((string) ($photo['comply_label'] ?? '')) !== '' ? (string) $photo['comply_label'] : (string) ($photo['file_name'] ?? 'Foto'));
                                    $caption = trim((string) ($photo['caption'] ?? ''));
                                    $metaLine = trim((string) ($photo['meta_line'] ?? '')) !== ''
                                        ? (string) $photo['meta_line']
                                        : ($caption !== '' ? $caption : ((string) $printSection['info_title'] . ' - ' . $description));
                                    $metaLine = $appendPreviewPhotoItemToRemark($metaLine, (array) $photo);
                                    ?>
                                    <div class="comply-print-tile">
                                        <div class="comply-print-tile__image">
                                            <img src="<?= htmlspecialchars($photoUrl) ?>" alt="<?= htmlspecialchars($description) ?>" loading="lazy" decoding="async" fetchpriority="low">
                                        </div>
                                        <div class="comply-print-tile__desc">Description: <?= htmlspecialchars($description) ?></div>
                                        <div class="comply-print-tile__caption"><?= htmlspecialchars($metaLine) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?php if (empty($dailyGroups) && empty($complyGroups)): ?>
                <div class="comply-print-empty">Belum ada foto daily progress atau foto comply APPROVED yang bisa dipreview.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function () {
        function waitForPreviewImages() {
            var images = Array.prototype.slice.call(document.querySelectorAll('.comply-print-tile__image img'));
            if (!images.length) {
                return Promise.resolve();
            }

            var waiters = images.map(function (img) {
                img.loading = 'eager';
                if (img.complete && img.naturalWidth > 0) {
                    return Promise.resolve();
                }

                return new Promise(function (resolve) {
                    var done = function () {
                        img.removeEventListener('load', done);
                        img.removeEventListener('error', done);
                        resolve();
                    };
                    img.addEventListener('load', done, { once: true });
                    img.addEventListener('error', done, { once: true });
                });
            });

            return Promise.all(waiters);
        }

        document.addEventListener('beforeprint', function () {
            Array.prototype.forEach.call(document.querySelectorAll('.comply-print-tile__image img'), function (img) {
                img.loading = 'eager';
            });
        });

        document.addEventListener('click', function (event) {
            var printButton = event.target.closest('.js-comply-print');
            if (!printButton) {
                return;
            }

            printButton.disabled = true;
            var originalHtml = printButton.innerHTML;
            printButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyiapkan foto...';
            waitForPreviewImages().then(function () {
                printButton.disabled = false;
                printButton.innerHTML = originalHtml;
                window.print();
            });
        });
    })();
</script>
