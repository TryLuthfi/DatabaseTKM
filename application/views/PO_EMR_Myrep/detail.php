<?php
if (!function_exists('poEmrDetailValue')) {
    function poEmrDetailValue($value)
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
?>

<style>
    .emr-detail-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .emr-detail-head h1 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 800;
    }

    .emr-detail-hero {
        margin-bottom: 1.25rem;
        padding: 1.25rem;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(45, 212, 191, .22), transparent 35%),
            linear-gradient(135deg, #0f172a, #1e3a8a 58%, #0f766e);
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
    }

    .emr-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .emr-detail-label {
        color: rgba(255, 255, 255, .72);
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .emr-detail-value {
        margin-top: .2rem;
        font-size: 1.08rem;
        font-weight: 800;
    }

    .emr-po-table th,
    .emr-po-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .emr-po-box {
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }

    .emr-po-box__head {
        padding: .8rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #dbeafe;
    }

    .emr-po-box__body {
        padding: 1rem;
    }
</style>

<div class="emr-detail-head">
    <div>
        <h1>Detail PO EMR MyRep</h1>
        <div class="text-muted font-weight-bold">PO target yang sudah dikunci untuk akses EMR.</div>
    </div>
    <a href="<?= base_url('PO_EMR_Myrep') ?>" class="btn btn-outline-secondary">Kembali</a>
</div>

<div class="emr-detail-hero">
    <div class="h4 font-weight-bold mb-1"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
    <div class="text-white-50"><?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($cluster['city_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
    <div class="emr-detail-grid">
        <div>
            <div class="emr-detail-label">Status Flow</div>
            <div class="emr-detail-value"><?= htmlspecialchars((string) ($cluster['status_current'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
            <div class="emr-detail-label">DRM Date</div>
            <div class="emr-detail-value"><?= !empty($cluster['drm_date']) ? htmlspecialchars((string) $cluster['drm_date'], ENT_QUOTES, 'UTF-8') : '-' ?></div>
        </div>
        <div>
            <div class="emr-detail-label">HP DRM</div>
            <div class="emr-detail-value"><?= poEmrDetailValue((float) ($cluster['homepass_drm'] ?? 0)) ?></div>
        </div>
        <div>
            <div class="emr-detail-label">PO Target</div>
            <div class="emr-detail-value"><?= (int) ($cluster['po_count'] ?? 0) ?></div>
        </div>
        <div>
            <div class="emr-detail-label">Total Nilai PO</div>
            <div class="emr-detail-value"><?= poEmrDetailValue((float) ($cluster['po_total_value'] ?? 0)) ?></div>
        </div>
    </div>
</div>

<?php foreach ($poGroups as $groupKey => $groupRows): ?>
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><?= $groupKey === 'SUBFEEDER' ? 'PO Subfeeder Target' : 'PO Cluster Target' ?></h3>
            <div class="card-tools text-muted font-weight-bold"><?= count($groupRows) ?> PO</div>
        </div>
        <div class="card-body">
            <?php if (empty($groupRows)): ?>
                <div class="text-muted">Belum ada data <?= $groupKey === 'SUBFEEDER' ? 'PO Subfeeder' : 'PO Cluster' ?> dengan status TARGET.</div>
            <?php else: ?>
                <?php foreach ($groupRows as $header): ?>
                    <div class="emr-po-box mb-3">
                        <div class="emr-po-box__head">
                            <strong><?= htmlspecialchars((string) ($header['po_number'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="badge badge-primary ml-2"><?= htmlspecialchars((string) ($header['po_category'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge badge-success ml-1"><?= htmlspecialchars((string) ($header['status_po'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="emr-po-box__body">
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Tanggal PO</strong><div><?= !empty($header['po_date']) ? htmlspecialchars((string) $header['po_date'], ENT_QUOTES, 'UTF-8') : '-' ?></div></div>
                                <div class="col-md-3"><strong>Nilai PO</strong><div><?= poEmrDetailValue((float) ($header['po_value'] ?? 0)) ?></div></div>
                                <div class="col-md-3"><strong>Versi</strong><div><?= !empty($header['po_version_label']) ? htmlspecialchars((string) $header['po_version_label'], ENT_QUOTES, 'UTF-8') : '-' ?></div></div>
                                <div class="col-md-3"><strong>Remark</strong><div><?= !empty($header['remark_po']) ? htmlspecialchars((string) $header['remark_po'], ENT_QUOTES, 'UTF-8') : '-' ?></div></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm emr-po-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Termin</th>
                                            <th>%</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                            <th>Invoice</th>
                                            <th>Tgl Invoice</th>
                                            <th>Tgl BAST</th>
                                            <th>Tgl Payment</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($header['termin_rows'] ?? []) as $termin): ?>
                                            <tr>
                                                <td class="text-center"><?= (int) ($termin['termin_no'] ?? 0) ?></td>
                                                <td class="text-center"><?= poEmrDetailValue((float) ($termin['termin_percent'] ?? 0)) ?>%</td>
                                                <td class="text-right"><?= poEmrDetailValue((float) ($termin['termin_value'] ?? 0)) ?></td>
                                                <td class="text-center"><span class="badge badge-secondary"><?= htmlspecialchars((string) ($termin['status_termin'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><?= !empty($termin['invoice_number']) ? htmlspecialchars((string) $termin['invoice_number'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                                <td class="text-center"><?= !empty($termin['invoice_date']) ? htmlspecialchars((string) $termin['invoice_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                                <td class="text-center"><?= !empty($termin['bast_date']) ? htmlspecialchars((string) $termin['bast_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                                <td class="text-center"><?= !empty($termin['payment_date']) ? htmlspecialchars((string) $termin['payment_date'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                                <td><?= !empty($termin['remark_termin']) ? htmlspecialchars((string) $termin['remark_termin'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($header['termin_rows'])): ?>
                                            <tr><td colspan="9" class="text-center text-muted">Belum ada termin.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
