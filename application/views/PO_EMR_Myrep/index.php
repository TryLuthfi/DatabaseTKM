<?php
$flashError = $this->session->flashdata('error');

if (!function_exists('poEmrNumber')) {
    function poEmrNumber($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('poEmrNumberOrDash')) {
    function poEmrNumberOrDash($value)
    {
        return (float) $value == 0.0 ? '-' : poEmrNumber($value);
    }
}

$downloadParams = [];
if ((string) $selectedCity !== '') {
    $downloadParams['city'] = (string) $selectedCity;
}
if ((string) $selectedStage !== '') {
    $downloadParams['stage'] = (string) $selectedStage;
}
$downloadUrl = base_url('PO_EMR_Myrep/downloadReport') . (!empty($downloadParams) ? '?' . http_build_query($downloadParams) : '');
?>

<style>
    .emr-page-title {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .emr-page-title h1 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
    }

    .emr-page-title p {
        margin: .25rem 0 0;
        color: #64748b;
        font-weight: 600;
    }

    .emr-kpi {
        min-height: 112px;
        overflow: hidden;
    }

    #table_po_emr_target th,
    #table_po_emr_target td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table_po_emr_target thead th {
        text-align: center;
    }
</style>

<div class="emr-page-title">
    <div>
        <h1>PO EMR MyRep</h1>
        <p>Daftar PO dengan status target.</p>
    </div>
    <a href="<?= $downloadUrl ?>" class="btn btn-success">
        <i class="fas fa-file-excel mr-1"></i> Download Report
    </a>
</div>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!$isReady): ?>
    <div class="alert alert-warning">Tabel PO MyRep belum tersedia.</div>
<?php else: ?>
    <div class="row">
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-info emr-kpi">
                <div class="inner">
                    <h3><?= (int) ($summary['total_po'] ?? 0) ?></h3>
                    <p>Total PO Target</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-primary emr-kpi">
                <div class="inner">
                    <h3><?= (int) ($summary['total_cluster'] ?? 0) ?></h3>
                    <p>Total Cluster</p>
                </div>
                <div class="icon"><i class="fas fa-network-wired"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-secondary emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.45rem;"><?= poEmrNumber((float) ($summary['total_po_value'] ?? 0)) ?></h3>
                    <p>Total Nilai PO</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.45rem;"><?= poEmrNumber((float) ($summary['total_outstanding'] ?? 0)) ?></h3>
                    <p>Outstanding</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="small-box bg-success emr-kpi">
                <div class="inner">
                    <h3 style="font-size:1.35rem;"><?= poEmrNumber((float) ($summary['total_invoiced'] ?? 0)) ?></h3>
                    <p>Total Invoiced</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Data</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kota</label>
                        <select name="city" class="form-control">
                            <option value="">Semua Kota</option>
                            <?php foreach ($cityOptions as $city): ?>
                                <option value="<?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>" <?= strtoupper((string) $selectedCity) === strtoupper((string) $city) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $city, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Stage</label>
                        <select name="stage" class="form-control">
                            <option value="">Semua Stage</option>
                            <?php foreach (['DP', 'ATP CW', 'FULL OPM', 'RFS', 'FAC'] as $stageOption): ?>
                                <option value="<?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>" <?= strtoupper((string) $selectedStage) === $stageOption ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stageOption, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        <a href="<?= base_url('PO_EMR_Myrep') ?>" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">List PO Target</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_po_emr_target" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>PO</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Tanggal PO</th>
                            <th>Cluster</th>
                            <th>Kota</th>
                            <th>Regional</th>
                            <th>Stage</th>
                            <th>Nilai PO</th>
                            <th>Progress</th>
                            <th>Outstanding</th>
                            <th>Total Invoiced</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poRows as $index => $row): ?>
                            <?php
                            $stage = strtoupper(trim((string) ($row['po_stage_status'] ?? '-')));
                            $badgeClass = $stage === 'DP' ? 'danger' : ($stage === 'ATP CW' ? 'warning' : ($stage === 'FULL OPM' ? 'info' : ($stage === 'RFS' ? 'primary' : ($stage === 'FAC' ? 'success' : 'secondary'))));
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars((string) ($row['po_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['status_po'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="text-center"><?= htmlspecialchars((string) ($row['po_type'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><?= htmlspecialchars((string) ($row['po_category'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><?= !empty($row['po_date']) ? htmlspecialchars((string) $row['po_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                <td><?= htmlspecialchars((string) ($row['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($stage, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-right"><?= poEmrNumber((float) ($row['po_value'] ?? 0)) ?></td>
                                <td class="text-center"><?= (int) ($row['termin_progress_count'] ?? 0) ?>/<?= (int) ($row['termin_total_count'] ?? 0) ?></td>
                                <td class="text-right"><?= poEmrNumberOrDash((float) ($row['outstanding_total'] ?? 0)) ?></td>
                                <td class="text-right"><?= poEmrNumberOrDash((float) ($row['total_invoiced'] ?? 0)) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('PO_EMR_Myrep/detail/' . (int) $row['id_myrep_cluster']) ?>" class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($poRows)): ?>
                            <tr><td colspan="14" class="text-center text-muted">Belum ada PO dengan status TARGET.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    $(function () {
        if ($.fn.DataTable && $('#table_po_emr_target').length) {
            $('#table_po_emr_target').DataTable({
                responsive: false,
                scrollX: true,
                autoWidth: false,
                pageLength: 10,
                order: [[4, 'desc']]
            });
        }
    });
</script>
