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

if (!function_exists('checklist_doc_progress_percent')) {
    function checklist_doc_progress_percent($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;
        if ($required <= 0) {
            return 0;
        }
        return min(100, (int) round(($uploaded / $required) * 100));
    }
}

if (!function_exists('checklist_doc_progress_theme')) {
    function checklist_doc_progress_theme($uploaded, $required)
    {
        $required = (int) $required;
        $uploaded = (int) $uploaded;
        if ($required <= 0 || $uploaded <= 0) {
            return ['box' => 'bg-light', 'bar' => 'bg-secondary', 'tone' => 'text-muted'];
        }
        if ($uploaded >= $required) {
            return ['box' => 'bg-success-light', 'bar' => 'bg-success-strong', 'tone' => 'text-success-dark'];
        }
        return ['box' => 'bg-warning-light', 'bar' => 'bg-warning', 'tone' => 'text-warning-dark'];
    }
}

$totalMainfeeder = count($mainfeederList);
?>

<style>
    .checklist-board .small-box { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 24px rgba(31, 41, 55, 0.08); }
    .checklist-board .filter-card, .checklist-board .table-card { border: 0; border-radius: 16px; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08); }
    .checklist-board .filter-card .card-header, .checklist-board .table-card .card-header { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); border-bottom: 1px solid #e5e7eb; }
    .checklist-board .table thead th { background-color: #1f2937; color: #fff; border-color: #111827; font-size: 12px; white-space: nowrap; vertical-align: middle; }
    .checklist-board .table td { vertical-align: top; font-size: 13px; }
    .cluster-identity { min-width: 280px; }
    .cluster-name { font-weight: 700; color: #111827; line-height: 1.4; margin-bottom: 6px; }
    .cluster-meta { display: flex; flex-wrap: wrap; gap: 6px; }
    .cluster-chip { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #eff6ff; color: #1d4ed8; }
    .timeline-stack { min-width: 170px; display: grid; gap: 8px; }
    .timeline-item { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; background: #fff; }
    .timeline-label { display: block; font-size: 11px; text-transform: uppercase; color: #6b7280; margin-bottom: 4px; font-weight: 700; }
    .timeline-value { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .doc-card { min-width: 220px; border-radius: 14px; padding: 12px; border: 1px solid #dbe4f0; }
    .doc-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px; }
    .doc-card-title { font-size: 12px; text-transform: uppercase; font-weight: 800; color: #0f172a; margin-bottom: 2px; }
    .doc-card-progress { font-size: 18px; font-weight: 800; line-height: 1; }
    .doc-card-subtitle { font-size: 11px; color: #64748b; }
    .doc-progress-track { width: 100%; height: 8px; background: rgba(148,163,184,.2); border-radius: 999px; overflow: hidden; margin-bottom: 10px; }
    .doc-progress-bar { height: 100%; border-radius: 999px; }
    .doc-status-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .doc-status-item { background: rgba(255,255,255,.75); border: 1px solid rgba(148,163,184,.25); border-radius: 10px; padding: 8px 9px; }
    .doc-status-label { display: block; font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px; }
    .doc-status-value { font-size: 16px; font-weight: 800; color: #0f172a; line-height: 1; }
    .bg-light { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); }
    .bg-warning-light { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); }
    .bg-success-light { background: linear-gradient(135deg, #e7f8ef 0%, #d0f1df 100%); }
    .bg-success-strong { background: linear-gradient(90deg, #15803d 0%, #16a34a 100%); }
    .text-success-dark { color: #166534; }
    .text-warning-dark { color: #9a3412; }
    .text-muted { color: #6b7280; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid checklist-board">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">CHECKLIST DOKUMENT MAINFEEDER</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="btn-group">
                    <a href="<?= base_url('Checklist_Dokument_MyRep') ?>" class="btn btn-outline-dark">Monitoring Cluster</a>
                    <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-dark">Monitoring Mainfeeder</a>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAddMainfeeder">Tambah Mainfeeder</button>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $totalMainfeeder ?></h3>
                            <p>Mainfeeder</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline filter-card">
                <div class="card-header">
                    <h3 class="card-title">Filter Mainfeeder</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Regional</label>
                                <select name="regional" class="form-control">
                                    <option value="">Semua Regional</option>
                                    <?php foreach ($regionalOptions as $regionalOption): ?>
                                        <option value="<?= $regionalOption ?>" <?= ($selectedRegional === $regionalOption) ? 'selected' : '' ?>><?= $regionalOption ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Kota</label>
                                <select name="city" class="form-control">
                                    <option value="">Semua Kota</option>
                                    <?php foreach ($cityOptions as $cityOption): ?>
                                        <option value="<?= $cityOption ?>" <?= ($selectedCity === $cityOption) ? 'selected' : '' ?>><?= $cityOption ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                                <a href="<?= base_url('Checklist_Dokument_MyRep/mainfeeder') ?>" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">List Mainfeeder</h3>
                </div>
                <div class="card-body">
                    <table id="table-mainfeeder" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Regional</th>
                                <th>Mainfeeder</th>
                                <th>Timeline ATP</th>
                                <th>Timeline Dokument</th>
                                <th>CW ATP</th>
                                <th>FULL OPM</th>
                                <th>RFS</th>
                                <th>Timeline Astri</th>
                                <th>ASTRI CW ATP</th>
                                <th>ASTRI FULL OPM</th>
                                <th>ASTRI RFS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mainfeederList)): ?>
                                <tr>
                                    <td colspan="13" class="text-center">Belum ada data mainfeeder.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($mainfeederList as $row): ?>
                                    <?php
                                    $cwPercent = checklist_doc_progress_percent($row['doc_cw_atp_uploaded'], $row['doc_cw_atp_required']);
                                    $cwTheme = checklist_doc_progress_theme($row['doc_cw_atp_uploaded'], $row['doc_cw_atp_required']);
                                    $opmPercent = checklist_doc_progress_percent($row['doc_full_opm_uploaded'], $row['doc_full_opm_required']);
                                    $opmTheme = checklist_doc_progress_theme($row['doc_full_opm_uploaded'], $row['doc_full_opm_required']);
                                    $rfsPercent = checklist_doc_progress_percent($row['doc_rfs_uploaded'], $row['doc_rfs_required']);
                                    $rfsTheme = checklist_doc_progress_theme($row['doc_rfs_uploaded'], $row['doc_rfs_required']);
                                    $astriCwPercent = checklist_doc_progress_percent($row['astri_doc_cw_atp_submitted'], $row['doc_cw_atp_required']);
                                    $astriCwTheme = checklist_doc_progress_theme($row['astri_doc_cw_atp_submitted'], $row['doc_cw_atp_required']);
                                    $astriOpmPercent = checklist_doc_progress_percent($row['astri_doc_full_opm_submitted'], $row['doc_full_opm_required']);
                                    $astriOpmTheme = checklist_doc_progress_theme($row['astri_doc_full_opm_submitted'], $row['doc_full_opm_required']);
                                    $astriRfsPercent = checklist_doc_progress_percent($row['astri_doc_rfs_submitted'], $row['doc_rfs_required']);
                                    $astriRfsTheme = checklist_doc_progress_theme($row['astri_doc_rfs_submitted'], $row['doc_rfs_required']);
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['regional_name'] ?></td>
                                        <td>
                                            <div class="cluster-identity">
                                                <div class="cluster-name"><?= $row['mainfeeder_name'] ?></div>
                                                <div class="cluster-meta">
                                                    <span class="cluster-chip"><?= $row['city_name'] ?></span>
                                                    <span class="cluster-chip">Pjg <?= number_format((float) $row['length_meter'], 0, ',', '.') ?> m</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Tanggal ATP</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($row['atp_date']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Plan Dokument</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($row['plan_submit_doc_date']) ?></div>
                                                    <?php if ($row['aging_doc_days'] === null): ?>
                                                        <span class="badge badge-secondary">Aging -</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-<?= checklist_doc_aging_badge($row['aging_doc_days']) ?>">
                                                            Aging <?= (int) $row['aging_doc_days'] ?> hari
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Realisasi Dokument</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($row['actual_submit_doc_date']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $cwTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">CW ATP</div><div class="doc-card-subtitle">Realisasi <?= (int) $row['doc_cw_atp_uploaded'] ?>/<?= (int) $row['doc_cw_atp_required'] ?></div></div><div class="doc-card-progress <?= $cwTheme['tone'] ?>"><?= $cwPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $cwTheme['bar'] ?>" style="width: <?= $cwPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['doc_cw_atp_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['doc_cw_atp_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['doc_cw_atp_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['doc_cw_atp_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $opmTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">FULL OPM</div><div class="doc-card-subtitle">Realisasi <?= (int) $row['doc_full_opm_uploaded'] ?>/<?= (int) $row['doc_full_opm_required'] ?></div></div><div class="doc-card-progress <?= $opmTheme['tone'] ?>"><?= $opmPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $opmTheme['bar'] ?>" style="width: <?= $opmPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['doc_full_opm_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['doc_full_opm_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['doc_full_opm_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['doc_full_opm_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $rfsTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">RFS</div><div class="doc-card-subtitle">Realisasi <?= (int) $row['doc_rfs_uploaded'] ?>/<?= (int) $row['doc_rfs_required'] ?></div></div><div class="doc-card-progress <?= $rfsTheme['tone'] ?>"><?= $rfsPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $rfsTheme['bar'] ?>" style="width: <?= $rfsPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['doc_rfs_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['doc_rfs_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['doc_rfs_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['doc_rfs_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="timeline-stack">
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Submit Astri</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($row['submit_astri_date']) ?></div>
                                                </div>
                                                <div class="timeline-item">
                                                    <span class="timeline-label">Approved Astri</span>
                                                    <div class="timeline-value"><?= checklist_doc_format_date($row['approved_astri_date']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriCwTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">ASTRI CW ATP</div><div class="doc-card-subtitle">Submit <?= (int) $row['astri_doc_cw_atp_submitted'] ?>/<?= (int) $row['doc_cw_atp_required'] ?></div></div><div class="doc-card-progress <?= $astriCwTheme['tone'] ?>"><?= $astriCwPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $astriCwTheme['bar'] ?>" style="width: <?= $astriCwPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['astri_doc_cw_atp_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['astri_doc_cw_atp_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['astri_doc_cw_atp_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['astri_doc_cw_atp_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriOpmTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">ASTRI FULL OPM</div><div class="doc-card-subtitle">Submit <?= (int) $row['astri_doc_full_opm_submitted'] ?>/<?= (int) $row['doc_full_opm_required'] ?></div></div><div class="doc-card-progress <?= $astriOpmTheme['tone'] ?>"><?= $astriOpmPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $astriOpmTheme['bar'] ?>" style="width: <?= $astriOpmPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['astri_doc_full_opm_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['astri_doc_full_opm_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['astri_doc_full_opm_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['astri_doc_full_opm_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="doc-card <?= $astriRfsTheme['box'] ?>">
                                                <div class="doc-card-head"><div><div class="doc-card-title">ASTRI RFS</div><div class="doc-card-subtitle">Submit <?= (int) $row['astri_doc_rfs_submitted'] ?>/<?= (int) $row['doc_rfs_required'] ?></div></div><div class="doc-card-progress <?= $astriRfsTheme['tone'] ?>"><?= $astriRfsPercent ?>%</div></div>
                                                <div class="doc-progress-track"><div class="doc-progress-bar <?= $astriRfsTheme['bar'] ?>" style="width: <?= $astriRfsPercent ?>%;"></div></div>
                                                <div class="doc-status-grid">
                                                    <div class="doc-status-item"><span class="doc-status-label">NY</span><span class="doc-status-value"><?= (int) $row['astri_doc_rfs_ny'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">On Review</span><span class="doc-status-value"><?= (int) $row['astri_doc_rfs_on_review'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Reject</span><span class="doc-status-value"><?= (int) $row['astri_doc_rfs_rejected'] ?></span></div>
                                                    <div class="doc-status-item"><span class="doc-status-label">Approved</span><span class="doc-status-value"><?= (int) $row['astri_doc_rfs_approved'] ?></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><a href="<?= base_url('Checklist_Dokument_MyRep/detailMainfeeder/' . (int) $row['id_mainfeeder']) ?>" class="btn btn-primary btn-sm">Detail</a></td>
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

<div class="modal fade" id="modalAddMainfeeder">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= base_url('Checklist_Dokument_MyRep/saveMainfeeder') ?>">
                <div class="modal-header bg-dark">
                    <h4 class="modal-title">Tambah Mainfeeder</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Target / Kota</label>
                        <select name="id_target" class="form-control" required>
                            <option value="">Pilih Kota</option>
                            <?php foreach ($targetOptions as $target): ?>
                                <option value="<?= (int) $target['id_target'] ?>">
                                    <?= $target['city_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Mainfeeder</label>
                        <input type="text" name="mainfeeder_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Panjang (meter)</label>
                        <input type="number" step="0.01" min="0" name="length_meter" class="form-control" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal ATP</label>
                        <input type="date" name="atp_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#table-mainfeeder').DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: false,
            scrollX: true,
            pageLength: 10
        });
    });
</script>
