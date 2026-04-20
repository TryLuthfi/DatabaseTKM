<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$statusOptions = ['DRAFT', 'SUBMITTED', 'ON REVIEW', 'APPROVED', 'REJECTED', 'DONE', 'RELEASED', 'DRM', 'RFS', 'ATP', 'DONE BATCH APPROVAL', 'DONE'];

if (!function_exists('drmBadgeClass')) {
    function drmBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'DONE':
            case 'APPROVED':
            case 'DRM':
            case 'RFS':
            case 'ATP':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'ON REVIEW':
                return 'warning';
            case 'SUBMITTED':
            case 'RELEASED':
            case 'DONE BATCH APPROVAL':
                return 'info';
            default:
                return 'secondary';
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">DRM MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">Tabel DRM MyRep belum tersedia.</div>
            <?php endif; ?>
            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen DRM belum tersedia. Form header DRM tetap bisa dipakai.</div>
            <?php endif; ?>
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Filter DRM</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('DRM_MyRep') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kota</label>
                                    <select name="city" class="form-control">
                                        <option value="">Semua Kota</option>
                                        <?php foreach ($cityOptions as $cityOption): ?>
                                            <option value="<?= htmlspecialchars($cityOption) ?>" <?= $selectedCity === strtoupper($cityOption) ? 'selected' : '' ?>><?= htmlspecialchars($cityOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (array_unique($statusOptions) as $statusOption): ?>
                                            <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <a href="<?= base_url('DRM_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                    <button type="submit" class="btn btn-primary">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($isReady): ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Input DRM Baru</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= base_url('DRM_MyRep/saveDrm') ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Cluster Eligible</label>
                                        <select name="cluster_id" class="form-control" required>
                                            <option value="">Pilih Cluster</option>
                                            <?php foreach ($eligibleClusterOptions as $option): ?>
                                                <?php $targetLabel = !empty($option['month_num']) && !empty($option['year_num']) ? sprintf('%02d/%04d', (int) $option['month_num'], (int) $option['year_num']) : '-'; ?>
                                                <option value="<?= (int) $option['id_myrep_cluster'] ?>">
                                                    <?= htmlspecialchars((string) $option['cluster_name']) ?> | <?= htmlspecialchars((string) $option['city_name']) ?> | <?= $targetLabel ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tanggal DRM</label>
                                        <input type="date" name="drm_date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Homepass DRM</label>
                                        <input type="number" name="homepass_drm" class="form-control" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Status DRM</label>
                                        <select name="status_drm" class="form-control">
                                            <option value="DRAFT">DRAFT</option>
                                            <option value="SUBMITTED">SUBMITTED</option>
                                            <option value="ON REVIEW">ON REVIEW</option>
                                            <option value="APPROVED">APPROVED</option>
                                            <option value="REJECTED">REJECTED</option>
                                            <option value="DONE">DONE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Remark</label>
                                        <input type="text" name="remark_drm" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan DRM</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Monitoring DRM</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Cluster</th>
                                    <th>Kota</th>
                                    <th>Periode</th>
                                    <th>Released</th>
                                    <th>HP Donasi</th>
                                    <th>HP DRM</th>
                                    <th>Status DRM</th>
                                    <th>Progress Dokumen</th>
                                    <th>Status Flow</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clusterRows as $index => $row): ?>
                                    <?php $targetLabel = !empty($row['month_num']) && !empty($row['year_num']) ? sprintf('%02d/%04d', (int) $row['month_num'], (int) $row['year_num']) : '-'; ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-')) ?></strong>
                                            <div class="text-muted small"><?= htmlspecialchars((string) ($row['regional_name'] ?? '-')) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-')) ?></td>
                                        <td><?= $targetLabel ?></td>
                                        <td><?= !empty($row['released_at']) ? htmlspecialchars((string) $row['released_at']) : '-' ?></td>
                                        <td class="text-right"><?= number_format((float) ($row['hp_donasi'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((float) ($row['homepass_drm'] ?? 0), 0, ',', '.') ?></td>
                                        <td><span class="badge badge-<?= drmBadgeClass($row['status_drm'] ?? 'DRAFT') ?>"><?= htmlspecialchars((string) ($row['status_drm'] ?? 'DRAFT')) ?></span></td>
                                        <td><?= (int) ($row['doc_approved'] ?? 0) ?>/<?= (int) ($row['doc_total'] ?? 0) ?> approved</td>
                                        <td><span class="badge badge-<?= drmBadgeClass($row['status_current'] ?? 'RELEASED') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'RELEASED')) ?></span></td>
                                        <td>
                                            <a href="<?= base_url('DRM_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($clusterRows)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">Belum ada data DRM.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
