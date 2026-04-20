<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');

if (!function_exists('postDonasiBadgeClass')) {
    function postDonasiBadgeClass($status)
    {
        switch (strtoupper(trim((string) $status))) {
            case 'DONE':
            case 'ATP':
            case 'RFS':
            case 'DRM':
                return 'success';
            case 'REJECTED':
                return 'danger';
            case 'ON REVIEW':
                return 'warning';
            default:
                return 'info';
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Post Donasi MyRep</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!$isReady): ?>
                <div class="alert alert-danger">Tabel flow post donasi MyRep belum tersedia.</div>
            <?php endif; ?>
            <?php if ($isReady && !$docReady): ?>
                <div class="alert alert-warning">Tabel dokumen post donasi belum tersedia.</div>
            <?php endif; ?>
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?= $flashSuccess ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?= $flashError ?></div>
            <?php endif; ?>

            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Filter Post Donasi</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Post_Donasi_MyRep') ?>">
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
                                    <label>Status Flow</label>
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <?php foreach (['RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'DONE'] as $statusOption): ?>
                                            <option value="<?= $statusOption ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>><?= $statusOption ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <a href="<?= base_url('Post_Donasi_MyRep') ?>" class="btn btn-outline-secondary">Reset</a>
                                    <button type="submit" class="btn btn-primary">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Monitoring 12 Dokumen Post Donasi</h3>
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
                                        <td><?= (int) ($row['doc_approved'] ?? 0) ?>/<?= (int) ($row['doc_total'] ?? 0) ?> approved</td>
                                        <td><span class="badge badge-<?= postDonasiBadgeClass($row['status_current'] ?? 'RELEASED') ?>"><?= htmlspecialchars((string) ($row['status_current'] ?? 'RELEASED')) ?></span></td>
                                        <td><a href="<?= base_url('Post_Donasi_MyRep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($clusterRows)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Belum ada data post donasi.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
