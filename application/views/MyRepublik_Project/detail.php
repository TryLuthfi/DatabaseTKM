<?php
if (!function_exists('myrepDetailNumber')) {
    function myrepDetailNumber($value, $suffix = '')
    {
        $formatted = number_format((float) $value, 0, ',', '.');
        return $suffix !== '' ? $formatted . ' ' . $suffix : $formatted;
    }
}

if (!function_exists('myrepDetailCurrency')) {
    function myrepDetailCurrency($value)
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('myrepDetailDate')) {
    function myrepDetailDate($value)
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? date('d M Y H:i', $timestamp) : htmlspecialchars((string) $value);
    }
}

if (!function_exists('myrepStatusBadgeClass')) {
    function myrepStatusBadgeClass($status)
    {
        $status = strtoupper(trim((string) $status));
        if (in_array($status, ['DONE', 'APPROVED', 'RELEASED', 'FULL RFS', 'PAID', 'CLOSED'], true)) {
            return 'badge-success';
        }
        if (in_array($status, ['REJECTED', 'HOLD', 'DROP'], true)) {
            return 'badge-danger';
        }
        if (in_array($status, ['ON REVIEW', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'WAITING APPROVAL RPM', 'WAITING APPROVAL HO'], true)) {
            return 'badge-warning';
        }
        return 'badge-info';
    }
}

if (!function_exists('myrepQuickValue')) {
    function myrepQuickValue($row, $key)
    {
        return htmlspecialchars((string) ($row[$key] ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('myrepQuickDateValue')) {
    function myrepQuickDateValue($row, $key)
    {
        $value = (string) ($row[$key] ?? '');
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('myrepQuickDateTimeValue')) {
    function myrepQuickDateTimeValue($row, $key)
    {
        $value = (string) ($row[$key] ?? '');
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d\TH:i', $timestamp) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('myrepQuickSelected')) {
    function myrepQuickSelected($row, $key, $value)
    {
        if ($key === 'status_rfs') {
            $current = strtoupper(trim((string) ($row[$key] ?? '')));
            $expected = strtoupper(trim((string) $value));
            $current = $current === 'PARTIAL RFS' ? 'PARTIAL' : $current;
            $expected = $expected === 'PARTIAL RFS' ? 'PARTIAL' : $expected;
            return $current === $expected ? 'selected' : '';
        }
        return strtoupper(trim((string) ($row[$key] ?? ''))) === strtoupper(trim((string) $value)) ? 'selected' : '';
    }
}

$myrepClusterId = (int) ($cluster['id_myrep_cluster'] ?? 0);
$rfsClusterId = (int) ($cluster['rfs_cluster_id'] ?? $cluster['legacy_rfs_cluster_id'] ?? 0);
$quickUpdateData = $quickUpdateData ?? [];
$canQuickUpdate = !empty($canQuickUpdate);
?>

<style>
    .myrep-detail-hero {
        background:
            radial-gradient(circle at top right, rgba(45, 212, 191, .18), transparent 28%),
            linear-gradient(145deg, #0f172a, #1d4ed8 52%, #0f766e);
        color: #fff;
        border-radius: 22px;
        padding: 1.4rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
    }

    .myrep-detail-hero__meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .85rem;
        margin-top: 1rem;
    }

    .myrep-detail-hero__box {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 16px;
        padding: .9rem 1rem;
    }

    .myrep-detail-hero__label {
        font-size: .76rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: rgba(255,255,255,.72);
        margin-bottom: .25rem;
    }

    .myrep-detail-hero__value {
        font-size: 1.1rem;
        font-weight: 800;
    }

    .myrep-stage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .myrep-stage-card {
        border-radius: 18px;
        border: 1px solid #dbeafe;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 1rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        height: 100%;
    }

    .myrep-stage-card__title {
        font-size: .82rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: .05em;
        font-weight: 700;
        margin-bottom: .5rem;
    }

    .myrep-stage-card__date {
        font-size: .8rem;
        color: #64748b;
        margin-top: .45rem;
    }

    .myrep-key-list dt {
        font-size: .78rem;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: .2rem;
    }

    .myrep-key-list dd {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: .75rem;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Detail Project MyRep</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <?php if ($canQuickUpdate): ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-myrep-quick-update">Update Cluster</button>
                    <?php endif; ?>
                    <a href="<?= base_url('MyRepublik_Project') ?>" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="myrep-detail-hero">
                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:1rem;">
                    <div>
                        <div class="small text-uppercase font-weight-bold text-white-50">Cluster Detail</div>
                        <h2 class="mb-1"><?= htmlspecialchars((string) ($cluster['cluster_name'] ?? '-')) ?></h2>
                        <div class="text-white-50">
                            <?= htmlspecialchars((string) ($cluster['regional_name'] ?? '-')) ?>
                            | <?= htmlspecialchars((string) ($cluster['province_name'] ?? '-')) ?>
                            | <?= htmlspecialchars((string) ($cluster['city_name'] ?? '-')) ?>
                            | <?= htmlspecialchars((string) ($cluster['team_name'] ?? '-')) ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="mb-2">
                            <span class="badge badge-light px-3 py-2"><?= htmlspecialchars((string) ($cluster['status_current'] ?? ($cluster['status_rfs'] ?? '-'))) ?></span>
                        </div>
                        <div class="small text-white-50">
                            <?= $isLegacy ? 'Sumber: Legacy RFS / ATP' : 'Sumber: Flow MyRep Baru' ?>
                        </div>
                    </div>
                </div>

                <div class="myrep-detail-hero__meta">
                    <div class="myrep-detail-hero__box">
                        <div class="myrep-detail-hero__label">Homepass Plan</div>
                        <div class="myrep-detail-hero__value"><?= myrepDetailNumber((float) ($cluster['hp_plan'] ?? 0), 'HP') ?></div>
                    </div>
                    <div class="myrep-detail-hero__box">
                        <div class="myrep-detail-hero__label">PO Total</div>
                        <div class="myrep-detail-hero__value"><?= myrepDetailCurrency((float) ($cluster['po_total_value'] ?? 0)) ?></div>
                    </div>
                    <div class="myrep-detail-hero__box">
                        <div class="myrep-detail-hero__label">PO Count</div>
                        <div class="myrep-detail-hero__value"><?= (int) ($cluster['po_count'] ?? 0) ?></div>
                    </div>
                    <div class="myrep-detail-hero__box">
                        <div class="myrep-detail-hero__label">Claim RFS</div>
                        <div class="myrep-detail-hero__value"><?= myrepDetailNumber((float) ($cluster['claim_total_hp'] ?? 0), 'HP') ?></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Timeline Awal Sampai Akhir</h3>
                        </div>
                        <div class="card-body">
                            <div class="myrep-stage-grid">
                                <?php foreach ($stageTimeline as $stage): ?>
                                    <div class="myrep-stage-card">
                                        <div class="myrep-stage-card__title"><?= htmlspecialchars((string) ($stage['stage'] ?? '-')) ?></div>
                                        <div class="mb-2">
                                            <span class="badge <?= myrepStatusBadgeClass($stage['status'] ?? '') ?>">
                                                <?= htmlspecialchars((string) ($stage['status'] ?? '-')) ?>
                                            </span>
                                        </div>
                                        <div><?= htmlspecialchars((string) ($stage['summary'] ?? '-')) ?></div>
                                        <div class="myrep-stage-card__date"><?= myrepDetailDate($stage['date'] ?? null) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Informasi Utama</h3>
                        </div>
                        <div class="card-body">
                            <dl class="myrep-key-list mb-0">
                                <dt>Target Periode</dt>
                                <dd><?= htmlspecialchars((string) (($cluster['month_num'] ?? '-') . ' / ' . ($cluster['year_num'] ?? '-'))) ?></dd>

                                <dt>Chief / RPM / SM / SPV</dt>
                                <dd><?= htmlspecialchars((string) ($cluster['chief'] ?? '-')) ?> / <?= htmlspecialchars((string) ($cluster['rpm'] ?? '-')) ?> / <?= htmlspecialchars((string) ($cluster['sm'] ?? '-')) ?> / <?= htmlspecialchars((string) ($cluster['spv'] ?? '-')) ?></dd>

                                <dt>Cluster Code</dt>
                                <dd><?= htmlspecialchars((string) ($cluster['cluster_code'] ?? '-')) ?></dd>

                                <dt>RFS Cluster ID</dt>
                                <dd><?= $rfsClusterId > 0 ? $rfsClusterId : '-' ?></dd>

                                <dt>Target MyRep / RKAP</dt>
                                <dd><?= myrepDetailNumber((float) ($cluster['target_myrep'] ?? 0), 'HP') ?> / <?= myrepDetailNumber((float) ($cluster['target_rkap'] ?? 0), 'HP') ?></dd>

                                <dt>Realisasi MyRep</dt>
                                <dd><?= myrepDetailNumber((float) ($cluster['realization_myrep'] ?? 0), 'HP') ?></dd>

                                <dt>Remark Umum</dt>
                                <dd><?= htmlspecialchars((string) ($cluster['remark_general'] ?? '-')) ?></dd>
                            </dl>
                        </div>
                    </div>

                    <?php if (!$isLegacy): ?>
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Quick Access Modul</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap" style="gap:.5rem;">
                                    <?php if ($myrepClusterId > 0): ?>
                                        <a href="<?= base_url('PO_MyRep/detail/' . $myrepClusterId) ?>" class="btn btn-sm btn-outline-primary">Detail PO</a>
                                        <a href="<?= base_url('Batch_Approval_MyRep/detail/' . $myrepClusterId) ?>" class="btn btn-sm btn-outline-primary">Detail Batch</a>
                                        <a href="<?= base_url('DRM_MyRep/detail/' . $myrepClusterId) ?>" class="btn btn-sm btn-outline-primary">Detail DRM</a>
                                    <?php endif; ?>
                                    <?php if ($rfsClusterId > 0): ?>
                                        <a href="<?= base_url('Checklist_Dokument_MyRep/detail/' . $rfsClusterId) ?>" class="btn btn-sm btn-outline-primary">Checklist ATP</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$isLegacy): ?>
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Ringkasan Progress per Flow</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($flowSummaries as $summary): ?>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="small text-muted text-uppercase font-weight-bold mb-2"><?= htmlspecialchars((string) ($summary['label'] ?? '-')) ?></div>
                                        <div class="font-weight-bold text-dark mb-2"><?= (int) ($summary['uploaded_doc'] ?? 0) ?> / <?= (int) ($summary['total_doc'] ?? 0) ?> uploaded</div>
                                        <div class="small text-success">Approved: <?= (int) ($summary['approved_doc'] ?? 0) ?></div>
                                        <div class="small text-danger">Rejected: <?= (int) ($summary['rejected_doc'] ?? 0) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Batch Approval Detail</h3>
                            </div>
                            <div class="card-body">
                                <dl class="myrep-key-list">
                                    <dt>Submission / Staging</dt>
                                    <dd><?= myrepDetailDate($cluster['submission_date'] ?? null) ?> / <?= htmlspecialchars((string) ($cluster['staging_status'] ?? '-')) ?></dd>

                                    <dt>HP Donasi</dt>
                                    <dd><?= myrepDetailNumber((float) ($cluster['hp_donasi'] ?? 0), 'HP') ?></dd>

                                    <dt>Nominal Pengajuan / Release</dt>
                                    <dd><?= myrepDetailCurrency((float) ($cluster['nominal_pengajuan_area'] ?? 0)) ?> / <?= myrepDetailCurrency((float) ($cluster['nominal_release_finance'] ?? 0)) ?></dd>

                                    <dt>Nominal per Homepass</dt>
                                    <dd><?= myrepDetailCurrency((float) ($cluster['nominal_per_homepass'] ?? 0)) ?></dd>

                                    <dt>Bank / Penerima</dt>
                                    <dd><?= htmlspecialchars((string) ($cluster['bank_name'] ?? '-')) ?> - <?= htmlspecialchars((string) ($cluster['recipient_name'] ?? '-')) ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title mb-0">PIC Batch Approval</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama PIC</th>
                                                <th>Phone</th>
                                                <th>Posisi</th>
                                                <th>Periode</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($batchPics as $pic): ?>
                                                <tr>
                                                    <td><?= (int) ($pic['pic_no'] ?? 0) ?></td>
                                                    <td><?= htmlspecialchars((string) ($pic['pic_name'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($pic['pic_phone'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($pic['pic_position'] ?? '-')) ?></td>
                                                    <td><?= htmlspecialchars((string) ($pic['pic_period'] ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($batchPics)): ?>
                                                <tr><td colspan="5" class="text-center text-muted">Belum ada PIC batch.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">PO dan Termin</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($poHeaders as $header): ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:1rem;">
                                    <div>
                                        <div class="font-weight-bold"><?= htmlspecialchars((string) ($header['po_number'] ?? '-')) ?></div>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars((string) ($header['po_type'] ?? '-')) ?> | <?= htmlspecialchars((string) ($header['po_category'] ?? '-')) ?> | <?= myrepDetailDate($header['po_date'] ?? null) ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge <?= myrepStatusBadgeClass($header['status_po'] ?? '') ?>"><?= htmlspecialchars((string) ($header['status_po'] ?? '-')) ?></span>
                                        <div class="font-weight-bold mt-2"><?= myrepDetailCurrency((float) ($header['po_value'] ?? 0)) ?></div>
                                    </div>
                                </div>
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Termin</th>
                                                <th>Persen</th>
                                                <th>Nilai</th>
                                                <th>Status</th>
                                                <th>Invoice</th>
                                                <th>Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (($header['termin_rows'] ?? []) as $termin): ?>
                                                <tr>
                                                    <td><?= (int) ($termin['termin_no'] ?? 0) ?></td>
                                                    <td><?= myrepDetailNumber((float) ($termin['termin_percent'] ?? 0)) ?>%</td>
                                                    <td><?= myrepDetailCurrency((float) ($termin['termin_value'] ?? 0)) ?></td>
                                                    <td><span class="badge <?= myrepStatusBadgeClass($termin['status_termin'] ?? '') ?>"><?= htmlspecialchars((string) ($termin['status_termin'] ?? '-')) ?></span></td>
                                                    <td><?= myrepDetailDate($termin['invoice_date'] ?? null) ?></td>
                                                    <td><?= myrepDetailDate($termin['paid_date'] ?? null) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($header['termin_rows'])): ?>
                                                <tr><td colspan="6" class="text-center text-muted">Belum ada termin PO.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($poHeaders)): ?>
                            <div class="text-muted">Belum ada data PO untuk cluster ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Claim RFS</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Approval RPM</th>
                                            <th>Tanggal Claim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($claimRows as $claim): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) (($claim['claim_month'] ?? '-') . '/' . ($claim['claim_year'] ?? '-'))) ?></td>
                                                <td><?= myrepDetailNumber((float) ($claim['claim_qty'] ?? 0), 'HP') ?></td>
                                                <td><span class="badge <?= myrepStatusBadgeClass($claim['status_claim'] ?? '') ?>"><?= htmlspecialchars((string) ($claim['status_claim'] ?? '-')) ?></span></td>
                                                <td><?= htmlspecialchars((string) ($claim['rpm_approval_status'] ?? '-')) ?></td>
                                                <td><?= myrepDetailDate($claim['claim_date'] ?? null) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($claimRows)): ?>
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada claim RFS.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Checklist ATP / Paket RFS</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Group</th>
                                            <th>Status</th>
                                            <th>Tgl RFS</th>
                                            <th>Plan ATP</th>
                                            <th>Actual ATP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($packageRows as $package): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string) ($package['group_label'] ?? '-')) ?></td>
                                                <td><span class="badge <?= myrepStatusBadgeClass($package['status_package'] ?? '') ?>"><?= htmlspecialchars((string) ($package['status_package'] ?? '-')) ?></span></td>
                                                <td><?= myrepDetailDate($package['tanggal_rfs'] ?? null) ?></td>
                                                <td><?= myrepDetailDate($package['plan_atp_date'] ?? null) ?></td>
                                                <td><?= myrepDetailDate($package['actual_atp_date'] ?? null) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($packageRows)): ?>
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada package ATP / checklist RFS.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($canQuickUpdate): ?>
<?php
$quickStatusOptions = ['DRAFT', 'BA OPEN', 'BAK', 'VALSAL', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE BATCH APPROVAL', 'DRM', 'RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE', 'REJECTED', 'HOLD'];
$quickStagingOptions = ['', 'DRAFT', 'WAITING HO', 'WAITING MYREP', 'WAITING FINANCE', 'RELEASED', 'DONE', 'COMPLETED', 'REJECTED'];
$quickRfsOptions = [
    '' => '-',
    'FULL RFS' => 'FULL RFS',
    'PARTIAL' => 'PARTIAL RFS',
    'NY RFS' => 'NY RFS',
];
$quickAtpOptions = ['', 'DONE', 'PUNCLIST'];
$quickChecklistOptions = ['', 'AREA', 'HO', 'EMR', 'NRO', 'CLOSED'];
?>
<div class="modal fade" id="modal-myrep-quick-update" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('MyRepublik_Project/updateQuick/' . $myrepClusterId) ?>" id="form-myrep-quick-update">
                <div class="modal-header">
                    <h5 class="modal-title">Update Cluster MyRep</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#quick-tab-cluster" role="tab">Cluster</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quick-tab-bak" role="tab">BAK</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quick-tab-batch" role="tab">Batch</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quick-tab-drm" role="tab">DRM</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quick-tab-rfs" role="tab">RFS / ATP</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quick-tab-checklist" role="tab">Checklist</a></li>
                    </ul>

                    <div class="tab-content border-left border-right border-bottom p-3">
                        <div class="tab-pane fade show active" id="quick-tab-cluster" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status Current</label>
                                        <select name="status_current" class="form-control" required>
                                            <?php foreach ($quickStatusOptions as $option): ?>
                                                <option value="<?= htmlspecialchars($option) ?>" <?= myrepQuickSelected($quickUpdateData, 'status_current', $option) ?>><?= htmlspecialchars($option) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>City Name</label>
                                        <input type="text" name="city_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'city_name') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Cluster Code</label>
                                        <input type="text" name="cluster_code" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'cluster_code') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cluster Name</label>
                                        <input type="text" name="cluster_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'cluster_name') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>District Name</label>
                                        <input type="text" name="district_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'district_name') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Village Name</label>
                                        <input type="text" name="village_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'village_name') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>HP Plan</label>
                                        <input type="number" name="hp_plan" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'hp_plan') ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nomor NTP</label>
                                        <input type="text" name="nomor_ntp" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nomor_ntp') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal NTP</label>
                                        <input type="date" name="tanggal_ntp" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'tanggal_ntp') ?>">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Remark General</label>
                                        <textarea name="remark_general" class="form-control" rows="2"><?= myrepQuickValue($quickUpdateData, 'remark_general') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="quick-tab-bak" role="tabpanel">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Homepass BAK</label>
                                        <input type="number" name="homepass_bak" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'homepass_bak') ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>BA Open Date</label>
                                        <input type="date" name="ba_open_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'ba_open_date') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>BAK Date</label>
                                        <input type="date" name="bak_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'bak_date') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Homepass VALSAL</label>
                                        <input type="number" name="homepass_valsal" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'homepass_valsal') ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>VALSAL Date</label>
                                        <input type="date" name="valsal_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'valsal_date') ?>">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label>Remark VALSAL</label>
                                        <input type="text" name="remark_valsal" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'remark_valsal') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="quick-tab-batch" role="tabpanel">
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>HP Donasi</label><input type="number" name="hp_donasi" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'hp_donasi') ?>" min="0"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Submission Date</label><input type="date" name="submission_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'submission_date') ?>"></div></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Staging Status</label>
                                        <select name="staging_status" class="form-control">
                                            <?php foreach ($quickStagingOptions as $option): ?>
                                                <option value="<?= htmlspecialchars($option) ?>" <?= myrepQuickSelected($quickUpdateData, 'staging_status', $option) ?>><?= $option === '' ? '-' : htmlspecialchars($option) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3"><div class="form-group"><label>Released At</label><input type="datetime-local" name="released_at" class="form-control" value="<?= myrepQuickDateTimeValue($quickUpdateData, 'released_at') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Nominal Pengajuan Area</label><input type="number" name="nominal_pengajuan_area" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nominal_pengajuan_area') ?>" step="0.01"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Nominal Nego EMR</label><input type="number" name="nominal_nego_emr" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nominal_nego_emr') ?>" step="0.01"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Nominal Release Finance</label><input type="number" name="nominal_release_finance" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nominal_release_finance') ?>" step="0.01"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Nominal per HP</label><input type="number" name="nominal_per_homepass" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nominal_per_homepass') ?>" step="0.01"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'bank_name') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Bank Account Number</label><input type="text" name="bank_account_number" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'bank_account_number') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Recipient Name</label><input type="text" name="recipient_name" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'recipient_name') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Recipient Phone</label><input type="text" name="recipient_phone" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'recipient_phone') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Recipient Position</label><input type="text" name="recipient_position" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'recipient_position') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Recipient Period</label><input type="text" name="recipient_period" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'recipient_period') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Free Wifi Qty</label><input type="number" name="free_wifi_qty" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'free_wifi_qty') ?>" min="0"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Free Wifi Period Month</label><input type="number" name="free_wifi_period_month" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'free_wifi_period_month') ?>" min="0"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>ASTRI Batch Number</label><input type="text" name="astri_batch_number" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'astri_batch_number') ?>"></div></div>
                                <div class="col-md-9"><div class="form-group"><label>Remark Batch Approval</label><input type="text" name="remark_batch_approval" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'remark_batch_approval') ?>"></div></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="quick-tab-drm" role="tabpanel">
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>Homepass DRM</label><input type="number" name="homepass_drm" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'homepass_drm') ?>" min="0"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>DRM Date</label><input type="date" name="drm_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'drm_date') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Nama OLT</label><input type="text" name="nama_olt" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'nama_olt') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Remark DRM</label><input type="text" name="remark_drm" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'remark_drm') ?>"></div></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="quick-tab-rfs" role="tabpanel">
                            <div class="row">
                                <div class="col-md-3"><div class="form-group"><label>RFS Date</label><input type="date" name="rfs_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'rfs_date') ?>"></div></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status RFS</label>
                                        <select name="status_rfs" class="form-control">
                                            <?php foreach ($quickRfsOptions as $option => $label): ?>
                                                <option value="<?= htmlspecialchars($option) ?>" <?= myrepQuickSelected($quickUpdateData, 'status_rfs', $option) ?>><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3"><div class="form-group"><label>Email ATP Date</label><input type="date" name="email_atp_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'email_atp_date') ?>"></div></div>
                                <div class="col-md-3"><div class="form-group"><label>Actual ATP Date</label><input type="date" name="actual_atp_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'actual_atp_date') ?>"></div></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status ATP</label>
                                        <select name="status_atp" class="form-control">
                                            <?php foreach ($quickAtpOptions as $option): ?>
                                                <option value="<?= htmlspecialchars($option) ?>" <?= myrepQuickSelected($quickUpdateData, 'status_atp', $option) ?>><?= $option === '' ? '-' : htmlspecialchars($option) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="col-md-3"><div class="form-group"><label>RFS <?= $i ?> Date</label><input type="date" name="rfs_<?= $i ?>_date" class="form-control" value="<?= myrepQuickDateValue($quickUpdateData, 'rfs_' . $i . '_date') ?>"></div></div>
                                    <div class="col-md-3"><div class="form-group"><label>RFS <?= $i ?> Qty</label><input type="number" name="rfs_<?= $i ?>_qty" class="form-control" value="<?= myrepQuickValue($quickUpdateData, 'rfs_' . $i . '_qty') ?>" min="0"></div></div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="quick-tab-checklist" role="tabpanel">
                            <div class="row">
                                <?php foreach ([
                                    'cluster_cwatp' => 'Cluster CW ATP',
                                    'cluster_fullopm' => 'Cluster Full OPM',
                                    'cluster_rfs' => 'Cluster RFS',
                                    'subfeeder_cwatp' => 'Subfeeder CW ATP',
                                    'subfeeder_fullopm' => 'Subfeeder Full OPM',
                                    'subfeeder_rfs' => 'Subfeeder RFS',
                                ] as $key => $label): ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?= htmlspecialchars($label) ?></label>
                                            <select name="<?= htmlspecialchars($key) ?>" class="form-control">
                                                <?php foreach ($quickChecklistOptions as $option): ?>
                                                    <?php if ($option === 'NRO' && $key !== 'cluster_rfs') continue; ?>
                                                    <option value="<?= htmlspecialchars($option) ?>" <?= myrepQuickSelected($quickUpdateData, $key, $option) ?>><?= $option === '' ? '-' : htmlspecialchars($option) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('form-myrep-quick-update');
        if (!form) {
            return;
        }
        form.addEventListener('submit', function (event) {
            var status = (form.querySelector('[name="status_current"]') || {}).value || '';
            var isRfsOrAfter = ['RFS', 'ATP', 'CHECKLIST DOKUMENT', 'DONE'].indexOf(status.toUpperCase()) !== -1;
            var isAtpOrAfter = ['ATP', 'CHECKLIST DOKUMENT', 'DONE'].indexOf(status.toUpperCase()) !== -1;
            var rfsDate = (form.querySelector('[name="rfs_date"]') || {}).value || '';
            var statusRfs = (form.querySelector('[name="status_rfs"]') || {}).value || '';
            var actualAtp = (form.querySelector('[name="actual_atp_date"]') || {}).value || '';
            if (isRfsOrAfter && (!rfsDate || !statusRfs)) {
                event.preventDefault();
                alert('Kalau status sudah RFS/ATP/CHECKLIST/DONE, tanggal RFS dan status RFS wajib diisi.');
                return false;
            }
            if (isAtpOrAfter && !actualAtp) {
                event.preventDefault();
                alert('Kalau status sudah ATP/CHECKLIST/DONE, actual ATP date wajib diisi.');
                return false;
            }
            if (!confirm('Simpan quick update untuk cluster ini?')) {
                event.preventDefault();
                return false;
            }
        });
    })();
</script>
<?php endif; ?>
