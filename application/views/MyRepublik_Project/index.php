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
                        <div class="myrep-toggle">
                            <a href="<?= base_url('MyRepublik_Project?' . http_build_query(array_merge($baseQuery, ['metric' => 'HP']))) ?>" class="<?= $metricMode === 'HP' ? 'active' : '' ?>">Angka Homepass</a>
                            <a href="<?= base_url('MyRepublik_Project?' . http_build_query(array_merge($baseQuery, ['metric' => 'PO']))) ?>" class="<?= $metricMode === 'PO' ? 'active' : '' ?>">Angka PO</a>
                        </div>
                    </div>

                    <div class="myrep-overview">
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total Cluster</div>
                            <div class="myrep-overview__value"><?= (int) ($overview['total_cluster'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total Homepass</div>
                            <div class="myrep-overview__value"><?= myrepDashNumber((float) ($overview['total_hp'] ?? 0)) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Total PO</div>
                            <div class="myrep-overview__value"><?= myrepDashNumber((float) ($overview['total_po'] ?? 0)) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">Released</div>
                            <div class="myrep-overview__value"><?= (int) ($overview['total_released'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">RFS</div>
                            <div class="myrep-overview__value"><?= (int) ($overview['total_rfs'] ?? 0) ?></div>
                        </div>
                        <div class="myrep-overview__box">
                            <div class="myrep-overview__label">ATP</div>
                            <div class="myrep-overview__value"><?= (int) ($overview['total_atp'] ?? 0) ?></div>
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
                        <div class="myrep-status-grid">
                            <?php foreach ($statusCards as $card): ?>
                                <div class="myrep-status-card">
                                    <div class="myrep-status-card__status"><?= htmlspecialchars((string) ($card['status'] ?? '-')) ?></div>
                                    <div class="myrep-status-card__value">
                                        <?= $metricMode === 'PO' ? myrepDashNumber((float) ($card['metric_total'] ?? 0)) : myrepDashNumber((float) ($card['metric_total'] ?? 0)) . ' HP' ?>
                                    </div>
                                    <div class="myrep-status-card__sub"><?= (int) ($card['cluster_count'] ?? 0) ?> cluster</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Daftar Cluster</h3>
                        <div class="myrep-table-note">Angka utama saat ini: <?= $metricMode === 'PO' ? 'PO Value' : 'Homepass' ?></div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
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
                                            <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current'] ?? '-')) ?></span></td>
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
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($clusterRows)): ?>
                                        <tr><td colspan="10" class="text-center text-muted">Belum ada data cluster MyRep.</td></tr>
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
