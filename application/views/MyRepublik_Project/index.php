<?php
if (!function_exists('myrepDashNumber')) {
    function myrepDashNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('myrepClusterDetailUrl')) {
    function myrepClusterDetailUrl($row)
    {
        $myrepClusterId = (int) ($row['id_myrep_cluster'] ?? 0);
        if ($myrepClusterId > 0) {
            return base_url('MyRepublik_Project/detail/' . $myrepClusterId);
        }

        $legacyClusterId = (int) ($row['legacy_rfs_cluster_id'] ?? $row['rfs_cluster_id'] ?? 0);
        if ($legacyClusterId > 0) {
            return base_url('MyRepublik_Project/detailLegacy/' . $legacyClusterId);
        }

        return '#';
    }
}
?>

<style>
    .myrep-hero {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, .18), transparent 32%),
            linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0f766e);
        border-radius: 20px;
        padding: 1.25rem;
        color: #fff;
        margin-bottom: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }

    .myrep-hero__top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .myrep-toggle {
        display: inline-flex;
        gap: .35rem;
        padding: .3rem;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.14);
    }

    .myrep-toggle a {
        padding: .42rem .85rem;
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        font-size: .85rem;
    }

    .myrep-toggle a.active {
        background: #fff;
        color: #0f172a;
    }

    .myrep-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .myrep-overview__box {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 16px;
        padding: 1rem;
        backdrop-filter: blur(8px);
    }

    .myrep-overview__label {
        color: rgba(255,255,255,.72);
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .25rem;
    }

    .myrep-overview__value {
        font-size: 1.25rem;
        font-weight: 800;
    }

    .myrep-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .myrep-status-card {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #dbeafe;
        border-radius: 18px;
        padding: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        position: relative;
        overflow: hidden;
    }

    .myrep-status-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #2563eb, #14b8a6);
    }

    .myrep-status-card__status {
        font-size: .82rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .45rem;
        font-weight: 700;
    }

    .myrep-status-card__value {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .myrep-status-card__sub {
        margin-top: .35rem;
        color: #64748b;
        font-size: .82rem;
    }

    .myrep-table-note {
        font-size: .78rem;
        color: #64748b;
    }
</style>

<?php
$summaryRows = $clusterStageSummaryRows ?? [];
$deleteClusterRows = $deleteClusterRows ?? [];
$renderClusterRows = !empty($renderClusterRows);
$summaryFooter = null;
$isSuperAdmin = (string) $this->session->userdata('nama_level') === 'Super Admin';
if (!empty($summaryRows)) {
    $lastSummaryRow = end($summaryRows);
    if (strtoupper((string) ($lastSummaryRow['city_name'] ?? '')) === 'TOTAL') {
        $summaryFooter = $lastSummaryRow;
        array_pop($summaryRows);
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Dashboard MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-warning">Tabel flow MyRep baru belum tersedia.</div>
            <?php else: ?>
                <?php if (!empty($this->session->flashdata('success'))): ?>
                    <div class="alert alert-success"><?= htmlspecialchars((string) $this->session->flashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (!empty($this->session->flashdata('error'))): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars((string) $this->session->flashdata('error')) ?></div>
                <?php endif; ?>
                <?php
                $baseQuery = [];
                if ($selectedCity !== '') {
                    $baseQuery['city'] = $selectedCity;
                }
                if ($selectedStatus !== '') {
                    $baseQuery['status'] = $selectedStatus;
                }
                ?>

                <div class="myrep-hero">
                    <div class="myrep-hero__top">
                        <div>
                            <div class="h4 font-weight-bold mb-1">Status List Project MyRep</div>
                            <div class="text-white-50">Dashboard utama untuk memantau flow project dari BAK sampai ATP.</div>
                        </div>
                        <div class="d-flex align-items-center" style="gap:.5rem; flex-wrap:wrap;">
                            <div class="myrep-toggle">
                                <a href="<?= base_url('MyRepublik_Project?' . http_build_query(array_merge($baseQuery, ['metric' => 'HP']))) ?>" class="<?= $metricMode === 'HP' ? 'active' : '' ?>">Angka Homepass</a>
                                <a href="<?= base_url('MyRepublik_Project?' . http_build_query(array_merge($baseQuery, ['metric' => 'PO']))) ?>" class="<?= $metricMode === 'PO' ? 'active' : '' ?>">Angka PO</a>
                            </div>
                            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#modal-import-cutoff-myrep">
                                Import Cutoff CSV
                            </button>
                        </div>
                    </div>

                    <div class="myrep-overview">
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total Cluster</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_cluster"><?= (int) ($overview['total_cluster'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total Homepass</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_hp"><?= myrepDashNumber((float) ($overview['total_hp'] ?? 0)) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total PO</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_po"><?= myrepDashNumber((float) ($overview['total_po'] ?? 0)) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Released</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_released"><?= (int) ($overview['total_released'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">RFS</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_rfs"><?= (int) ($overview['total_rfs'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">ATP</div>
                            <div class="myrep-overview__value" data-myrep-overview="total_atp"><?= (int) ($overview['total_atp'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Filter Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row">
                            <input type="hidden" name="metric" value="<?= htmlspecialchars($metricMode) ?>">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select name="city" class="form-control">
                                        <option value="">Semua Kota</option>
                                        <?php foreach ($cityOptions as $city): ?>
                                            <option value="<?= htmlspecialchars($city) ?>" <?= strtoupper((string) $selectedCity) === strtoupper((string) $city) ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach ($statusOptions as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    <a href="<?= base_url('MyRepublik_Project?metric=' . urlencode($metricMode)) ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Status List Project</h3>
                    </div>
                    <div class="card-body">
                        <div class="myrep-status-grid" id="myrep-status-card-grid">
                            <?php foreach ($statusCards as $card): ?>
                                <div class="myrep-status-card">
                                    <div class="myrep-status-card__status"><?= htmlspecialchars((string) ($card['status'] ?? '-')) ?></div>
                                    <div class="myrep-status-card__value">
                                        <?= $metricMode === 'PO' ? myrepDashNumber((float) ($card['metric_total'] ?? 0)) : myrepDashNumber((float) ($card['metric_total'] ?? 0)) . ' HP' ?>
                                    </div>
                                    <div class="myrep-status-card__sub"><?= (int) ($card['cluster_count'] ?? 0) ?> cluster</div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($statusCards)): ?>
                                <div class="text-muted small" id="myrep-status-card-loading">Memuat status...</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Rekap Cluster per Kota</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center" id="table_myrep_city_stage_summary">
                                <thead>
                                    <tr>
                                        <th>KOTA</th>
                                        <th>BAK</th>
                                        <th>VALSAL</th>
                                        <th>BATCH</th>
                                        <th>DRM</th>
                                        <th>IMPLEMENTASI</th>
                                        <th>RFS</th>
                                        <th>ATP</th>
                                        <th>DOKUMENT</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody id="myrep-city-stage-summary-body">
                                    <?php foreach ($summaryRows as $summaryRow): ?>
                                        <tr>
                                            <td class="text-left"><?= htmlspecialchars((string) ($summaryRow['city_name'] ?? '-')) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['bak'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['valsal'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['batch'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['drm'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['implementasi'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['rfs'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['atp'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['dokument'] ?? 0)) ?></td>
                                            <td><?= myrepDashNumber((float) ($summaryRow['total'] ?? 0)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($clusterStageSummaryRows)): ?>
                                        <tr><td colspan="10" class="text-center text-muted">Memuat rekap cluster...</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot id="myrep-city-stage-summary-foot" class="<?= empty($summaryFooter) ? 'd-none' : '' ?>">
                                        <tr class="font-weight-bold">
                                            <th class="text-left">TOTAL</th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['bak'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['valsal'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['batch'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['drm'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['implementasi'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['rfs'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['atp'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['dokument'] ?? 0)) ?></th>
                                            <th><?= myrepDashNumber((float) ($summaryFooter['total'] ?? 0)) ?></th>
                                        </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Daftar Cluster</h3>
                        <div class="d-flex align-items-center">
                            <div class="myrep-table-note mr-2">Angka utama saat ini: <?= $metricMode === 'PO' ? 'PO Value' : 'Homepass' ?></div>
                            <?php if ($isSuperAdmin): ?>
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal-delete-myrep-clusters">Hapus All</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="table_myrep_cluster_list">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Cluster</th>
                                        <th>Kota</th>
                                        <th>Regional</th>
                                        <th>Status</th>
                                        <th><?= $metricMode === 'PO' ? 'Nilai PO' : 'Homepass' ?></th>
                                        <th>PO Count</th>
                                        <th>RPM / SPV</th>
                                        <th>DRM Date</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($renderClusterRows): ?>
                                    <?php foreach ($clusterRows as $index => $row): ?>
                                        <?php $detailUrl = myrepClusterDetailUrl($row); ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong>
                                                    <?php if ($detailUrl !== '#'): ?>
                                                        <a href="<?= htmlspecialchars($detailUrl) ?>" class="text-primary">
                                                            <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?>
                                                    <?php endif; ?>
                                                </strong>
                                                <div class="small text-muted"><?= htmlspecialchars((string) ($row['team_name'] ?? '-')) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current_display'] ?? $row['status_current'] ?? '-')) ?></span></td>
                                            <td><?= $metricMode === 'PO' ? myrepDashNumber((float) ($row['metric_value'] ?? 0)) : myrepDashNumber((float) ($row['metric_value'] ?? 0)) . ' HP' ?></td>
                                            <td><?= (int) ($row['po_count'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['rpm'] ?? '-')) ?> / <?= htmlspecialchars((string) ($row['spv'] ?? '-')) ?></td>
                                            <td><?= !empty($row['drm_date']) ? htmlspecialchars((string) $row['drm_date']) : '-' ?></td>
                                            <td>
                                                <?php if ($detailUrl !== '#'): ?>
                                                    <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn btn-sm btn-primary">
                                                        Detail
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                                <?php if ($isSuperAdmin && (int) ($row['id_myrep_cluster'] ?? 0) > 0): ?>
                                                    <form method="post" action="<?= base_url('MyRepublik_Project/deleteCluster') ?>" class="d-inline" onsubmit="return confirm('Hapus cluster ini? Seluruh flow MyRep dari BAK sampai Checklist Dokument akan ikut terhapus.');">
                                                        <input type="hidden" name="cluster_id" value="<?= (int) $row['id_myrep_cluster'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Hapus Cluster</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if (empty($clusterRows)): ?>
                                        <?php if ($renderClusterRows): ?>
                                            <tr><td colspan="10" class="text-center text-muted">Belum ada data cluster MyRep.</td></tr>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($isSuperAdmin): ?>
<div class="modal fade" id="modal-delete-myrep-clusters" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('MyRepublik_Project/deleteAllClusters') ?>" id="myrep-delete-selected-form">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Cluster MyRep</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong><span id="myrep-delete-selected-count">0</span> cluster dipilih</strong>
                            <div class="text-muted small">Hanya cluster yang tetap dicentang yang akan dihapus.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-myrep-delete-check-all">Centang Semua</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="table_myrep_delete_cluster_list">
                            <thead>
                                <tr>
                                    <th style="width:42px;">
                                        <input type="checkbox" id="myrep-delete-toggle-all" checked>
                                    </th>
                                    <th>Cluster</th>
                                    <th>Kota</th>
                                    <th>Regional</th>
                                    <th>Status</th>
                                    <th><?= $metricMode === 'PO' ? 'Nilai PO' : 'Homepass' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deleteClusterRows as $row): ?>
                                    <?php $deleteClusterId = (int) ($row['id_myrep_cluster'] ?? 0); ?>
                                    <?php if ($deleteClusterId <= 0) continue; ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="myrep-delete-cluster-check" name="cluster_ids[]" value="<?= $deleteClusterId ?>" checked>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($row['cluster_code'] ?? $row['team_name'] ?? '-')) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                        <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current_display'] ?? $row['status_current'] ?? '-')) ?></span></td>
                                        <td><?= $metricMode === 'PO' ? myrepDashNumber((float) ($row['metric_value'] ?? 0)) : myrepDashNumber((float) ($row['metric_value'] ?? 0)) . ' HP' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btn-myrep-delete-selected">Hapus Cluster Tercentang</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-import-cutoff-myrep" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Cutoff MyRep (Tahap BAK)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="upload-dropzone" id="myrep-cutoff-dropzone" style="border:2px dashed #cbd5e1;border-radius:14px;padding:1.25rem;text-align:center;cursor:pointer;background:#f8fafc;">
                    <input type="file" id="myrep-cutoff-file-input" name="file_excel" accept=".xls,.xlsx,.csv" style="display:none;">
                    <div><strong>Drag & drop file CSV/XLSX di sini</strong></div>
                    <div class="text-muted small">Atau klik area ini untuk pilih file template CSV khusus BAK, termasuk 6 status checklist dan flow NRO.</div>
                    <div id="myrep-cutoff-file-name" class="mt-2 text-primary">Belum ada file dipilih</div>
                </div>
                <div class="mt-3">
                    <a href="<?= base_url('MyRepublik_Project/downloadCutoffImportTemplate') ?>" class="btn btn-outline-secondary btn-sm">
                        Download Contoh CSV
                    </a>
                    <a href="<?= base_url('MyRepublik_Project/downloadCutoffCurrentSnapshot?' . http_build_query(['city' => $selectedCity, 'status' => $selectedStatus])) ?>" class="btn btn-outline-info btn-sm">
                        Download Update Sekarang
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-preview-cutoff-import">Preview Data</button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-save-cutoff-import" disabled>Import Semua Data Valid</button>
                </div>
                <div class="mt-3 table-responsive">
                    <table class="table table-bordered table-sm" id="table-preview-cutoff-import">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function initMyrepClusterTable() {
        var importedValidRows = [];
        var myrepMetricMode = <?= json_encode($metricMode) ?>;
        var myrepSelectedCity = <?= json_encode($selectedCity) ?>;
        var myrepSelectedStatus = <?= json_encode($selectedStatus) ?>;

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatNumber(value) {
            var number = Number(value || 0);
            return number.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        function updateOverview(overview) {
            overview = overview || {};
            $('[data-myrep-overview="total_cluster"]').text(Number(overview.total_cluster || 0));
            $('[data-myrep-overview="total_hp"]').text(formatNumber(overview.total_hp || 0));
            $('[data-myrep-overview="total_po"]').text(formatNumber(overview.total_po || 0));
            $('[data-myrep-overview="total_released"]').text(Number(overview.total_released || 0));
            $('[data-myrep-overview="total_rfs"]').text(Number(overview.total_rfs || 0));
            $('[data-myrep-overview="total_atp"]').text(Number(overview.total_atp || 0));
        }

        function renderStatusCards(cards) {
            var $grid = $('#myrep-status-card-grid');
            var html = '';
            (cards || []).forEach(function (card) {
                var metric = formatNumber(card.metric_total || 0);
                if (myrepMetricMode !== 'PO') {
                    metric += ' HP';
                }
                html += '<div class="myrep-status-card">'
                    + '<div class="myrep-status-card__status">' + escapeHtml(card.status || '-') + '</div>'
                    + '<div class="myrep-status-card__value">' + metric + '</div>'
                    + '<div class="myrep-status-card__sub">' + Number(card.cluster_count || 0) + ' cluster</div>'
                    + '</div>';
            });
            $grid.html(html || '<div class="text-muted small">Belum ada data status.</div>');
        }

        function renderStageSummary(rows) {
            var tableSelector = '#table_myrep_city_stage_summary';
            if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableSelector)) {
                $(tableSelector).DataTable().clear().destroy();
            }

            var bodyRows = (rows || []).slice();
            var footer = null;
            if (bodyRows.length && String(bodyRows[bodyRows.length - 1].city_name || '').toUpperCase() === 'TOTAL') {
                footer = bodyRows.pop();
            }

            var bodyHtml = '';
            bodyRows.forEach(function (row) {
                bodyHtml += '<tr>'
                    + '<td class="text-left">' + escapeHtml(row.city_name || '-') + '</td>'
                    + '<td>' + formatNumber(row.bak || 0) + '</td>'
                    + '<td>' + formatNumber(row.valsal || 0) + '</td>'
                    + '<td>' + formatNumber(row.batch || 0) + '</td>'
                    + '<td>' + formatNumber(row.drm || 0) + '</td>'
                    + '<td>' + formatNumber(row.implementasi || 0) + '</td>'
                    + '<td>' + formatNumber(row.rfs || 0) + '</td>'
                    + '<td>' + formatNumber(row.atp || 0) + '</td>'
                    + '<td>' + formatNumber(row.dokument || 0) + '</td>'
                    + '<td>' + formatNumber(row.total || 0) + '</td>'
                    + '</tr>';
            });
            $('#myrep-city-stage-summary-body').html(bodyHtml || '<tr><td colspan="10" class="text-center text-muted">Belum ada data rekap cluster.</td></tr>');

            if (footer) {
                $('#myrep-city-stage-summary-foot')
                    .removeClass('d-none')
                    .html('<tr class="font-weight-bold">'
                        + '<th class="text-left">TOTAL</th>'
                        + '<th>' + formatNumber(footer.bak || 0) + '</th>'
                        + '<th>' + formatNumber(footer.valsal || 0) + '</th>'
                        + '<th>' + formatNumber(footer.batch || 0) + '</th>'
                        + '<th>' + formatNumber(footer.drm || 0) + '</th>'
                        + '<th>' + formatNumber(footer.implementasi || 0) + '</th>'
                        + '<th>' + formatNumber(footer.rfs || 0) + '</th>'
                        + '<th>' + formatNumber(footer.atp || 0) + '</th>'
                        + '<th>' + formatNumber(footer.dokument || 0) + '</th>'
                        + '<th>' + formatNumber(footer.total || 0) + '</th>'
                        + '</tr>');
            } else {
                $('#myrep-city-stage-summary-foot').addClass('d-none').empty();
            }

            if ($.fn.DataTable && bodyRows.length) {
                $(tableSelector).DataTable({
                    order: [[0, 'asc']],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    responsive: false,
                    autoWidth: false
                });
            }
        }

        function loadDashboardData() {
            if (!window.jQuery) {
                window.setTimeout(loadDashboardData, 200);
                return;
            }

            $.ajax({
                url: '<?= base_url("MyRepublik_Project/dashboardData") ?>',
                method: 'POST',
                dataType: 'json',
                data: {
                    city: myrepSelectedCity,
                    status: myrepSelectedStatus,
                    metric: myrepMetricMode
                }
            }).done(function (response) {
                if (!response || !response.status) {
                    renderStatusCards([]);
                    renderStageSummary([]);
                    return;
                }
                updateOverview(response.overview || {});
                renderStatusCards(response.statusCards || []);
                renderStageSummary(response.clusterStageSummaryRows || []);
            }).fail(function () {
                renderStatusCards([]);
                renderStageSummary([]);
            });
        }

        function bindDropzone(dropzoneSelector, inputSelector, labelSelector) {
            var dropzone = document.querySelector(dropzoneSelector);
            var input = document.querySelector(inputSelector);
            var label = document.querySelector(labelSelector);
            if (!dropzone || !input || !label) {
                return;
            }

            dropzone.addEventListener('click', function () { input.click(); });
            input.addEventListener('change', function () {
                label.textContent = input.files && input.files[0] ? input.files[0].name : 'Belum ada file dipilih';
            });
            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#0ea5e9';
                });
            });
            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                });
            });
            dropzone.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    label.textContent = e.dataTransfer.files[0].name;
                }
            });
        }

        function renderPreviewTable(headers, rows) {
            var thead = document.querySelector('#table-preview-cutoff-import thead');
            var tbody = document.querySelector('#table-preview-cutoff-import tbody');
            if (!thead || !tbody) {
                return;
            }

            var headHtml = '<tr><th>No</th><th>Status</th><th>Message</th>';
            headers.forEach(function (h) { headHtml += '<th>' + h + '</th>'; });
            headHtml += '</tr>';
            thead.innerHTML = headHtml;

            var bodyHtml = '';
            rows.forEach(function (row, index) {
                var badge = row.status === 'valid' ? 'success' : 'danger';
                bodyHtml += '<tr>';
                bodyHtml += '<td>' + (index + 1) + '</td>';
                bodyHtml += '<td><span class="badge badge-' + badge + '">' + row.status + '</span></td>';
                bodyHtml += '<td>' + (row.message || '') + '</td>';
                headers.forEach(function (h) {
                    var value = row.raw && row.raw[h] ? row.raw[h] : '';
                    bodyHtml += '<td>' + value + '</td>';
                });
                bodyHtml += '</tr>';
            });
            tbody.innerHTML = bodyHtml;
        }

        function bootDataTable(tryCount) {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
                if (tryCount < 30) {
                    window.setTimeout(function () {
                        bootDataTable(tryCount + 1);
                    }, 200);
                }
                return;
            }

            var $ = window.jQuery;
            if ($('#table_myrep_cluster_list').length && !$.fn.DataTable.isDataTable('#table_myrep_cluster_list')) {
                $('#table_myrep_cluster_list').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '<?= base_url("MyRepublik_Project/clusterTableData") ?>',
                        method: 'POST',
                        data: function (payload) {
                            payload.city = myrepSelectedCity;
                            payload.status = myrepSelectedStatus;
                            payload.metric = myrepMetricMode;
                        }
                    },
                    order: [[2, 'asc'], [1, 'asc']],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    responsive: false,
                    autoWidth: false,
                    columnDefs: [
                        { orderable: false, targets: [0, 7, 9] },
                        { className: 'text-right', targets: [5, 6] }
                    ]
                });
            }

            if ($('#table_myrep_delete_cluster_list').length && !$.fn.DataTable.isDataTable('#table_myrep_delete_cluster_list')) {
                $('#table_myrep_delete_cluster_list').DataTable({
                    paging: false,
                    searching: true,
                    info: true,
                    order: [[1, 'asc']],
                    responsive: false,
                    autoWidth: false,
                    scrollY: '55vh',
                    scrollCollapse: true,
                    columnDefs: [
                        { orderable: false, targets: 0 }
                    ]
                });
            }
        }

        function updateDeleteSelectedCount() {
            var checkedCount = $('.myrep-delete-cluster-check:checked').length;
            var totalCount = $('.myrep-delete-cluster-check').length;
            $('#myrep-delete-selected-count').text(checkedCount);
            $('#btn-myrep-delete-selected').prop('disabled', checkedCount <= 0);
            $('#myrep-delete-toggle-all').prop('checked', totalCount > 0 && checkedCount === totalCount);
        }

        bindDropzone('#myrep-cutoff-dropzone', '#myrep-cutoff-file-input', '#myrep-cutoff-file-name');

        $(document).on('shown.bs.modal', '#modal-delete-myrep-clusters', function () {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#table_myrep_delete_cluster_list')) {
                $('#table_myrep_delete_cluster_list').DataTable().columns.adjust();
            }
            updateDeleteSelectedCount();
        });

        $(document).on('change', '#myrep-delete-toggle-all', function () {
            $('.myrep-delete-cluster-check').prop('checked', this.checked);
            updateDeleteSelectedCount();
        });

        $(document).on('click', '#btn-myrep-delete-check-all', function () {
            $('.myrep-delete-cluster-check').prop('checked', true);
            updateDeleteSelectedCount();
        });

        $(document).on('change', '.myrep-delete-cluster-check', updateDeleteSelectedCount);

        $(document).on('submit', '#myrep-delete-selected-form', function (event) {
            var checkedCount = $('.myrep-delete-cluster-check:checked').length;
            if (checkedCount <= 0) {
                event.preventDefault();
                alert('Pilih minimal satu cluster yang akan dihapus.');
                return false;
            }

            if (!confirm('Hapus ' + checkedCount + ' cluster MyRep yang tercentang? Seluruh flow dari BAK sampai Checklist Dokument ikut terhapus.')) {
                event.preventDefault();
                return false;
            }
        });

        $(document).on('click', '#btn-preview-cutoff-import', function () {
            var fileInput = document.getElementById('myrep-cutoff-file-input');
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                alert('Pilih file import terlebih dahulu.');
                return;
            }

            var formData = new FormData();
            formData.append('file_excel', fileInput.files[0]);

            $.ajax({
                url: '<?= base_url("MyRepublik_Project/previewCutoffImport") ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                if (!response || !response.status) {
                    alert(response && response.message ? response.message : 'Preview import gagal.');
                    return;
                }
                importedValidRows = response.valid_rows || [];
                renderPreviewTable(response.headers || [], response.rows || []);
                $('#btn-save-cutoff-import').prop('disabled', importedValidRows.length === 0);
            }).fail(function () {
                alert('Preview import gagal dijalankan.');
            });
        });

        $(document).on('click', '#btn-save-cutoff-import', function () {
            if (!importedValidRows.length) {
                alert('Tidak ada data valid untuk diimport.');
                return;
            }
            if (!confirm('Lanjut import semua data valid? Cluster existing akan dioverwrite mengikuti isi file dan status_current.')) {
                return;
            }

            $.ajax({
                url: '<?= base_url("MyRepublik_Project/saveCutoffImport") ?>',
                method: 'POST',
                data: { rows_json: JSON.stringify(importedValidRows) },
                dataType: 'json'
            }).done(function (response) {
                if (!response || !response.status) {
                    alert(response && response.message ? response.message : 'Import gagal.');
                    return;
                }
                alert(response.message || 'Import selesai.');
                window.location.reload();
            }).fail(function () {
                alert('Import gagal dijalankan.');
            });
        });

        bootDataTable(0);
        loadDashboardData();
    })();
</script>
