<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('implDashboardNumber')) {
    function implDashboardNumber($value, $zeroAsDash = false)
    {
        $number = (float) $value;
        if ($zeroAsDash && abs($number) < 0.00001) {
            return '-';
        }

        return number_format($number, 0, ',', '.');
    }
}
?>

<style>
    .impl-mini-progress__head {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: .35rem;
    }

    .impl-mini-progress__track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .impl-mini-progress__track span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .impl-mini-progress__track--qty span {
        background: linear-gradient(90deg, #38bdf8, #3b82f6);
    }

    .impl-mini-progress__track--photo span {
        background: linear-gradient(90deg, #34d399, #22c55e);
    }

    .impl-mini-progress__track--item span {
        background: linear-gradient(90deg, #f59e0b, #f97316);
    }

    .impl-mini-progress__track--overall span {
        background: linear-gradient(90deg, #2563eb, #14b8a6);
    }

    .impl-mini-progress__meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .78rem;
        color: #64748b;
        margin-top: .35rem;
    }

    .impl-progress-cell {
        min-width: 260px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Implementasi BOQ MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <?php if (!$isReady): ?>
                <div class="alert alert-warning">Tabel implementasi BOQ MyRep belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Filter Implementasi BOQ</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row">
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
                                        <?php foreach (['NOT STARTED', 'ON PROGRESS', 'DONE'] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    <a href="<?= base_url('Implementasi_BOQ_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                                <p>Total Cluster</p>
                            </div>
                            <div class="icon"><i class="fas fa-network-wired"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= (int) ($summary['not_started'] ?? 0) ?></h3>
                                <p>Belum Mulai</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-start"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= (int) ($summary['on_progress'] ?? 0) ?></h3>
                                <p>On Progress</p>
                            </div>
                            <div class="icon"><i class="fas fa-tools"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (int) ($summary['done'] ?? 0) ?></h3>
                                <p>Done</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Monitoring Implementasi BOQ</h3>
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
                                        <th>Tanggal DRM</th>
                                        <th>Status Implementasi</th>
                                        <th>Qty BOQ</th>
                                        <th>Qty Actual</th>
                                        <th>Foto</th>
                                        <th>Progress</th>
                                        <th>Last Progress</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clusterRows as $index => $row): ?>
                                        <?php
                                        $status = strtoupper(trim((string) ($row['implementation_status'] ?? 'NOT STARTED')));
                                        $badgeClass = $status === 'DONE' ? 'success' : ($status === 'ON PROGRESS' ? 'warning' : 'secondary');
                                        $qtyTarget = (float) ($row['target_qty_total'] ?? 0);
                                        $qtyActual = (float) ($row['actual_qty_total'] ?? 0);
                                        $photoTarget = (int) ($row['target_photo_total'] ?? 0);
                                        $photoUploaded = (int) ($row['uploaded_photo_total'] ?? 0);
                                        $itemTotal = (int) ($row['total_item'] ?? 0);
                                        $itemDone = (int) (($row['done_item'] ?? $row['done_item_count'] ?? 0));
                                        $qtyPercent = $qtyTarget > 0 ? min(100, round(($qtyActual / $qtyTarget) * 100)) : 0;
                                        $photoPercent = $photoTarget > 0 ? min(100, round(($photoUploaded / $photoTarget) * 100)) : 0;
                                        $itemPercent = $itemTotal > 0 ? min(100, round(($itemDone / $itemTotal) * 100)) : 0;
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                            <td><?= !empty($row['drm_date']) ? htmlspecialchars((string) $row['drm_date']) : '-' ?></td>
                                            <td><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                                            <td><?= implDashboardNumber((float) ($row['target_qty_total'] ?? 0)) ?></td>
                                            <td><?= implDashboardNumber((float) ($row['actual_qty_total'] ?? 0)) ?></td>
                                            <td><?= implDashboardNumber((int) ($row['uploaded_photo_total'] ?? 0)) ?> / <?= implDashboardNumber((int) ($row['target_photo_total'] ?? 0)) ?></td>
                                            <td class="impl-progress-cell">
                                                <div class="impl-mini-progress">
                                                    <div class="impl-mini-progress__head">
                                                        <span>Overall Progress</span>
                                                        <span><?= (int) round(($qtyPercent + $photoPercent + $itemPercent) / 3) ?>%</span>
                                                    </div>
                                                    <div class="impl-mini-progress__track impl-mini-progress__track--overall">
                                                        <span style="width: <?= (int) round(($qtyPercent + $photoPercent + $itemPercent) / 3) ?>%;"></span>
                                                    </div>
                                                    <div class="impl-mini-progress__meta">
                                                        <span>Qty <?= $qtyPercent ?>%</span>
                                                        <span>Foto <?= $photoPercent ?>%</span>
                                                        <span>Item <?= $itemPercent ?>%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= !empty($row['last_progress_date']) ? htmlspecialchars((string) $row['last_progress_date']) : '-' ?></td>
                                            <td>
                                                <a href="<?= base_url('Implementasi_BOQ_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($clusterRows)): ?>
                                        <tr><td colspan="12" class="text-center text-muted">Belum ada baseline BOQ aktif untuk implementasi.</td></tr>
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
