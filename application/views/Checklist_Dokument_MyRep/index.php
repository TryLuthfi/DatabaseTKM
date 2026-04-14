<?php
if (!function_exists('checklist_doc_format_date')) {
    function checklist_doc_format_date($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}

if (!function_exists('checklist_doc_aging_badge')) {
    function checklist_doc_aging_badge($aging)
    {
        if ($aging === null) {
            return 'secondary';
        }

        if ((int) $aging <= 0) {
            return 'success';
        }

        if ((int) $aging <= 3) {
            return 'warning';
        }

        return 'danger';
    }
}

$totalCluster = count($clusterList);
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">CHECKLIST DOKUMENT</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalCluster ?></h3>
                            <p>Cluster Full RFS</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Filter Cluster</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Checklist_Dokument_MyRep') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Kota</label>
                                <select name="city" class="form-control">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= $cityOption ?>" <?= ($selectedCity === $cityOption) ? 'selected' : '' ?>>
                                            <?= $cityOption ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                                <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">List Cluster FULL RFS</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Kota</th>
                                <th>Cluster</th>
                                <th>HP</th>
                                <th>Tanggal RFS</th>
                                <th>Plan ATP</th>
                                <th>Realisasi ATP</th>
                                <th>Aging ATP</th>
                                <th>Plan Dokument</th>
                                <th>Realisasi Dokument</th>
                                <th>Aging Dokument</th>
                                <th>Doc ATP CW</th>
                                <th>Doc Full OPM</th>
                                <th>Doc RFS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clusterList)): ?>
                                <tr>
                                    <td colspan="15" class="text-center">Belum ada cluster FULL RFS.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($clusterList as $cluster): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $cluster['city_name'] ?></td>
                                        <td><?= $cluster['cluster_name'] ?></td>
                                        <td><?= number_format((float) $cluster['homepass'], 0, ',', '.') ?></td>
                                        <td><?= checklist_doc_format_date($cluster['tanggal_rfs']) ?></td>
                                        <td><?= checklist_doc_format_date($cluster['plan_atp_date']) ?></td>
                                        <td><?= checklist_doc_format_date($cluster['actual_atp_date']) ?></td>
                                        <td>
                                            <?php if ($cluster['aging_atp_days'] === null): ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php else: ?>
                                                <span class="badge badge-<?= checklist_doc_aging_badge($cluster['aging_atp_days']) ?>">
                                                    <?= (int) $cluster['aging_atp_days'] ?> hari
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= checklist_doc_format_date($cluster['plan_submit_doc_date']) ?></td>
                                        <td><?= checklist_doc_format_date($cluster['actual_submit_doc_date']) ?></td>
                                        <td>
                                            <?php if ($cluster['aging_doc_days'] === null): ?>
                                                <span class="badge badge-secondary">-</span>
                                            <?php else: ?>
                                                <span class="badge badge-<?= checklist_doc_aging_badge($cluster['aging_doc_days']) ?>">
                                                    <?= (int) $cluster['aging_doc_days'] ?> hari
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= (int) $cluster['doc_cw_atp_uploaded'] ?>/<?= (int) $cluster['doc_cw_atp_required'] ?></td>
                                        <td><?= (int) $cluster['doc_full_opm_uploaded'] ?>/<?= (int) $cluster['doc_full_opm_required'] ?></td>
                                        <td><?= (int) $cluster['doc_rfs_uploaded'] ?>/<?= (int) $cluster['doc_rfs_required'] ?></td>
                                        <td>
                                            <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . (int) $cluster['id_cluster']) ?>"
                                                class="btn btn-primary btn-sm">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
