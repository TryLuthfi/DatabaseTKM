<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('poMyRepNumber')) {
    function poMyRepNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
?>

<style>
    .po-mini-progress__head {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: .35rem;
    }

    .po-mini-progress__track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .po-mini-progress__track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #14b8a6);
    }

    .po-mini-progress__meta {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .78rem;
        color: #64748b;
        margin-top: .35rem;
    }

    .po-progress-cell {
        min-width: 240px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">PO MyRep</h1>
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
                <div class="alert alert-warning">Tabel PO MyRep belum tersedia.</div>
            <?php else: ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Filter PO MyRep</h3>
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
                                    <label>Status PO</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (['NOT ISSUED', 'ISSUED', 'PARTIAL PAYMENT', 'FULLY PAID', 'CLOSED'] as $statusOption): ?>
                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= strtoupper((string) $selectedStatus) === $statusOption ? 'selected' : '' ?>><?= htmlspecialchars($statusOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                                    <a href="<?= base_url('PO_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                                <p>Total Cluster</p>
                            </div>
                            <div class="icon"><i class="fas fa-network-wired"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= (int) ($summary['not_issued'] ?? 0) ?></h3>
                                <p>Not Issued</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-circle-question"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= (int) ($summary['issued'] ?? 0) ?></h3>
                                <p>Issued</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-signature"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= (int) ($summary['partial_payment'] ?? 0) ?></h3>
                                <p>Partial Payment</p>
                            </div>
                            <div class="icon"><i class="fas fa-hand-holding-dollar"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (int) ($summary['fully_paid'] ?? 0) ?></h3>
                                <p>Fully Paid</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box bg-dark">
                            <div class="inner">
                                <h3><?= (int) ($summary['closed'] ?? 0) ?></h3>
                                <p>Closed</p>
                            </div>
                            <div class="icon"><i class="fas fa-box-archive"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Monitoring PO MyRep</h3>
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
                                        <th>Status Flow</th>
                                        <th>PO</th>
                                        <th>Nilai PO</th>
                                        <th>Progress Termin</th>
                                        <th>Last PO</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clusterRows as $index => $row): ?>
                                        <?php
                                        $statusBadgeClass = 'secondary';
                                        $summaryStatus = strtoupper(trim((string) ($row['po_summary_status'] ?? 'NOT ISSUED')));
                                        if ($summaryStatus === 'FULLY PAID') {
                                            $statusBadgeClass = 'success';
                                        } elseif ($summaryStatus === 'PARTIAL PAYMENT') {
                                            $statusBadgeClass = 'warning';
                                        } elseif ($summaryStatus === 'ISSUED') {
                                            $statusBadgeClass = 'primary';
                                        } elseif ($summaryStatus === 'CLOSED') {
                                            $statusBadgeClass = 'dark';
                                        }
                                        $terminTotal = (int) ($row['termin_total_count'] ?? 0);
                                        $terminPaid = (int) ($row['termin_paid_count'] ?? 0);
                                        $terminPercent = $terminTotal > 0 ? min(100, round(($terminPaid / $terminTotal) * 100)) : 0;
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                                <div class="small text-muted"><?= htmlspecialchars((string) ($row['team_name'] ?? '-')) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_current'] ?? '-')) ?></span></td>
                                            <td>
                                                <div>Cluster: <?= (int) ($row['po_cluster_count'] ?? 0) ?></div>
                                                <div>Subfeeder: <?= (int) ($row['po_subfeeder_count'] ?? 0) ?></div>
                                                <div><span class="badge badge-<?= $statusBadgeClass ?>"><?= htmlspecialchars($summaryStatus) ?></span></div>
                                            </td>
                                            <td><?= poMyRepNumber((float) ($row['po_total_value'] ?? 0)) ?></td>
                                            <td class="po-progress-cell">
                                                <div class="po-mini-progress">
                                                    <div class="po-mini-progress__head">
                                                        <span>Termin Paid</span>
                                                        <span><?= $terminPercent ?>%</span>
                                                    </div>
                                                    <div class="po-mini-progress__track">
                                                        <span style="width: <?= $terminPercent ?>%;"></span>
                                                    </div>
                                                    <div class="po-mini-progress__meta">
                                                        <span><?= $terminPaid ?> paid</span>
                                                        <span><?= $terminTotal ?> termin</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= !empty($row['last_po_date']) ? htmlspecialchars((string) $row['last_po_date']) : '-' ?></td>
                                            <td>
                                                <a href="<?= base_url('PO_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($clusterRows)): ?>
                                        <tr><td colspan="10" class="text-center text-muted">Belum ada data PO MyRep.</td></tr>
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
