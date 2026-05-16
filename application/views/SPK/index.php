<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$lampSpkDownload = $this->session->flashdata('lamp_spk_download');
$pksRows = $pksRows ?? [];
$bowheerRows = $bowheerRows ?? [];
$spkRows = $spkRows ?? [];
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0 text-dark">SPK</h1></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?><div class="alert alert-success"><?= $flashSuccess ?></div><?php endif; ?>
            <?php if (!empty($flashError)): ?><div class="alert alert-danger"><?= $flashError ?></div><?php endif; ?>
            <?php if (!empty($lampSpkDownload)): ?>
                <div class="alert alert-info d-flex align-items-center justify-content-between">
                    <span>File Lamp SPK siap diunduh.</span>
                    <a class="btn btn-sm btn-primary" href="<?= base_url('SPK/download_lamp_spk/' . rawurlencode((string) $lampSpkDownload)) ?>">Download File</a>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="drm-toolbar">
                        <button type="button" class="btn budget-btn budget-btn--primary" data-toggle="modal" data-target="#modal-spk-create">
                            <i class="fas fa-plus mr-1"></i> Tambah SPK
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm drm-table-card">
                        <div class="card-header drm-section-header">
                            <div><h3 class="card-title mb-1">Daftar SPK</h3></div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_spk" class="table table-bordered table-hover drm-monitor-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No SPK</th>
                                            <th>No PKS</th>
                                            <th>PIC</th>
                                            <th>Bowheer</th>
                                            <th>Cluster</th>
                                            <th>Status Current</th>
                                            <th>Nilai</th>
                                            <th>Nilai AMD 1</th>
                                            <th>Nilai AMD 2</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($spkRows as $i => $row): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($row['nomor_spk'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['nomor_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['pic_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['bowheer'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['cluster_name_ref'] ?? $row['cluster_ref'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><span class="badge badge-primary"><?= htmlspecialchars((string) ($row['cluster_status_current'] ?? '-'), ENT_QUOTES) ?></span></td>
                                                <td><?= number_format((float) ($row['nilai_spk'] ?? 0), 0, ',', '.') ?></td>
                                                <td><?= !empty($row['nilai_amandemen_1']) ? number_format((float) $row['nilai_amandemen_1'], 0, ',', '.') : '-' ?></td>
                                                <td><?= !empty($row['nilai_amandemen_2']) ? number_format((float) $row['nilai_amandemen_2'], 0, ',', '.') : '-' ?></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-open-spk-detail"
                                                        data-toggle="modal"
                                                        data-target="#modal-spk-detail"
                                                        data-id-spk="<?= (int) ($row['id_spk'] ?? 0) ?>"
                                                        data-nomor-spk="<?= htmlspecialchars((string) ($row['nomor_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nomor-pks="<?= htmlspecialchars((string) ($row['nomor_pks'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-pic="<?= htmlspecialchars((string) ($row['pic_pks'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-tanggal-spk="<?= htmlspecialchars((string) ($row['tanggal_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-bowheer="<?= htmlspecialchars((string) ($row['bowheer'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-cluster="<?= htmlspecialchars((string) ($row['cluster_ref'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-hp-drm="<?= number_format((float) ($row['homepass_drm'] ?? 0), 0, ',', '.') ?>"
                                                        data-project="<?= htmlspecialchars((string) ($row['project_name'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-spk="<?= number_format((float) ($row['nilai_spk'] ?? 0), 0, ',', '.') ?>"
                                                        data-toc-spk="<?= htmlspecialchars((string) ($row['toc_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-akhir-kontrak="<?= htmlspecialchars((string) ($row['akhir_kontrak'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-tanggal-amandemen-1="<?= htmlspecialchars((string) ($row['tanggal_amandemen_1'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-amandemen-1="<?= number_format((float) ($row['nilai_amandemen_1'] ?? 0), 0, ',', '.') ?>"
                                                        data-nilai-amandemen-1-raw="<?= htmlspecialchars((string) ($row['nilai_amandemen_1'] ?? ''), ENT_QUOTES) ?>"
                                                        data-nomor-amandemen-1="<?= htmlspecialchars((string) ($row['nomor_amandemen_1'] ?? $row['nomor_amandement_1'] ?? ''), ENT_QUOTES) ?>"
                                                        data-tanggal-amandemen-2="<?= htmlspecialchars((string) ($row['tanggal_amandemen_2'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-amandemen-2="<?= number_format((float) ($row['nilai_amandemen_2'] ?? 0), 0, ',', '.') ?>"
                                                        data-nilai-amandemen-2-raw="<?= htmlspecialchars((string) ($row['nilai_amandemen_2'] ?? ''), ENT_QUOTES) ?>"
                                                        data-nomor-amandemen-2="<?= htmlspecialchars((string) ($row['nomor_amandemen_2'] ?? $row['nomor_amandement_2'] ?? ''), ENT_QUOTES) ?>"
                                                        data-status="<?= htmlspecialchars((string) ($row['status_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                    >Detail</button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-warning js-open-spk-amandement"
                                                        data-toggle="modal"
                                                        data-target="#modal-spk-amandement"
                                                        data-id-spk="<?= (int) ($row['id_spk'] ?? 0) ?>"
                                                        data-nomor-spk="<?= htmlspecialchars((string) ($row['nomor_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nomor-pks="<?= htmlspecialchars((string) ($row['nomor_pks'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-pic="<?= htmlspecialchars((string) ($row['pic_pks'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-bowheer="<?= htmlspecialchars((string) ($row['bowheer'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-cluster="<?= htmlspecialchars((string) ($row['cluster_ref'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-hp-drm="<?= number_format((float) ($row['homepass_drm'] ?? 0), 0, ',', '.') ?>"
                                                        data-project="<?= htmlspecialchars((string) ($row['project_name'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-spk="<?= number_format((float) ($row['nilai_spk'] ?? 0), 0, ',', '.') ?>"
                                                        data-tanggal-spk="<?= htmlspecialchars((string) ($row['tanggal_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-toc-spk="<?= htmlspecialchars((string) ($row['toc_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-akhir-kontrak="<?= htmlspecialchars((string) ($row['akhir_kontrak'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-status="<?= htmlspecialchars((string) ($row['status_spk'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-tanggal-amandemen-1="<?= htmlspecialchars((string) ($row['tanggal_amandemen_1'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-amandemen-1-raw="<?= htmlspecialchars((string) ($row['nilai_amandemen_1'] ?? ''), ENT_QUOTES) ?>"
                                                        data-nomor-amandemen-1="<?= htmlspecialchars((string) ($row['nomor_amandemen_1'] ?? $row['nomor_amandement_1'] ?? ''), ENT_QUOTES) ?>"
                                                        data-tanggal-amandemen-2="<?= htmlspecialchars((string) ($row['tanggal_amandemen_2'] ?? '-'), ENT_QUOTES) ?>"
                                                        data-nilai-amandemen-2-raw="<?= htmlspecialchars((string) ($row['nilai_amandemen_2'] ?? ''), ENT_QUOTES) ?>"
                                                        data-nomor-amandemen-2="<?= htmlspecialchars((string) ($row['nomor_amandemen_2'] ?? $row['nomor_amandement_2'] ?? ''), ENT_QUOTES) ?>"
                                                    >Amandement</button>
                                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= base_url('SPK/print_doc/' . (int) $row['id_spk']) ?>">Print</a>
                                                    <form method="post" action="<?= base_url('SPK/delete/' . (int) $row['id_spk']) ?>" class="d-inline" onsubmit="return confirm('Hapus data SPK ini?');">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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

<div class="modal fade" id="modal-spk-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content budget-modal drm-modal-shell">
            <form method="post" action="<?= base_url('SPK/save') ?>">
                <div class="modal-header budget-modal__header">
                    <div><div class="budget-modal__eyebrow">SPK</div><h5 class="modal-title mb-1">Input SPK Baru</h5></div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-section">
                        <div class="spk-detail-heading"><span>Detail Project</span></div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Bowheer</label>
                                <select class="form-control js-spk-select2-bowheer" name="bowheer" required>
                                    <option value="">Pilih Bowheer</option>
                                    <?php foreach ($bowheerRows as $bowheer): ?>
                                        <option value="<?= htmlspecialchars((string) ($bowheer['nama_bowheer'] ?? ''), ENT_QUOTES) ?>">
                                            <?= htmlspecialchars((string) ($bowheer['nama_bowheer'] ?? ''), ENT_QUOTES) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8 form-group">
                                <label>Cluster</label>
                                <select class="form-control js-spk-select2-cluster" name="cluster_ref">
                                    <option value="">Pilih Bowheer dulu</option>
                                </select>
                                <small class="text-muted js-cluster-hint">Jika Bowheer = PT. EKA MAS REPUBLIK, cluster otomatis menampilkan status DRM ke atas.</small>
                            </div>
                            <div class="col-md-12 form-group d-none js-hp-drm-wrap">
                                <label>HP DRM</label>
                                <input type="text" class="form-control js-cluster-hp-drm" value="-" readonly>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Nama Project</label>
                                <textarea name="project_name" rows="3" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <div class="spk-detail-heading"><span>Detail SPK</span></div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>PKS</label>
                                <select class="form-control js-spk-select2-pks" name="id_pks" required>
                                    <option value="">Pilih PKS</option>
                                    <?php foreach ($pksRows as $pks): ?>
                                        <option value="<?= (int) $pks['id_pks'] ?>"><?= htmlspecialchars((string) $pks['nomor_pks'] . ' | ' . $pks['pic_pks'], ENT_QUOTES) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 form-group"><label>Tanggal SPK</label><input type="date" name="tanggal_spk" value="<?= date('Y-m-d') ?>" class="form-control" required></div>
                            <div class="col-md-3 form-group">
                                <label>Nilai SPK</label>
                                <input type="text" class="form-control js-nilai-spk-display" inputmode="numeric" autocomplete="off">
                                <input type="hidden" name="nilai_spk" class="js-nilai-spk-raw">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>TOC SPK</label>
                                <input type="number" min="0" step="1" name="toc_spk" class="form-control" placeholder="30">
                                <small class="text-muted">30 Hari</small>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Akhir Kontrak</label>
                                <input type="text" class="form-control js-akhir-kontrak-display" value="-" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary">Simpan SPK</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-spk-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content budget-modal drm-modal-shell">
            <div class="modal-header budget-modal__header">
                <div><div class="budget-modal__eyebrow">SPK</div><h5 class="modal-title mb-1 js-spk-detail-title">Detail SPK</h5></div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="drm-form-section">
                    <div class="spk-detail-heading"><span>DETAIL PROJECT</span></div>
                    <div class="row spk-read-grid">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Bowheer</div><div class="spk-read-item__value js-d-bowheer">-</div></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Cluster</div><div class="spk-read-item__value js-d-cluster">-</div></div>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">HP DRM</div><div class="spk-read-item__value js-d-hp-drm">-</div></div>
                        </div>
                        <div class="col-md-12 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nama Project</div><div class="spk-read-item__value spk-read-item__value--multiline js-d-project">-</div></div>
                        </div>
                    </div>
                </div>
                <div class="drm-form-section mb-0">
                    <div class="spk-detail-heading"><span>DETAIL SPK</span></div>
                    <div class="row spk-read-grid">
                        <div class="col-md-6 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">PKS</div><div class="spk-read-item__value js-d-nomor-pks">-</div></div>
                        </div>
                        <div class="col-md-6 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nama PIC</div><div class="spk-read-item__value js-d-pic">-</div></div>
                        </div>
                    </div>
                    <div class="row spk-read-grid">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Tanggal SPK</div><div class="spk-read-item__value js-d-tanggal-spk">-</div></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">TOC SPK</div><div class="spk-read-item__value js-d-toc-spk">-</div></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Akhir Kontrak</div><div class="spk-read-item__value js-d-akhir-kontrak">-</div></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nilai SPK</div><div class="spk-read-item__value js-d-nilai-spk">-</div></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Status SPK</div><div class="spk-read-item__value"><span class="badge badge-info js-d-status">-</span></div></div>
                        </div>
                    </div>
                </div>
                <div class="drm-form-section mb-0 mt-3">
                    <div class="spk-detail-heading"><span>AMANDEMENT</span></div>
                    <div class="row spk-read-grid">
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nomor AMD 1</div><div class="spk-read-item__value">1</div></div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Tanggal AMD 1</div><div class="spk-read-item__value js-d-tanggal-amandemen-1">-</div></div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nilai AMD 1</div><div class="spk-read-item__value js-d-nilai-amandemen-1">-</div></div>
                        </div>
                    </div>
                    <div class="row spk-read-grid mt-3">
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nomor AMD 2</div><div class="spk-read-item__value">2</div></div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Tanggal AMD 2</div><div class="spk-read-item__value js-d-tanggal-amandemen-2">-</div></div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-0">
                            <div class="spk-read-item"><div class="spk-read-item__label">Nilai AMD 2</div><div class="spk-read-item__value js-d-nilai-amandemen-2">-</div></div>
                        </div>
                    </div>
                </div>
                <div class="drm-form-section mb-0 mt-3">
                    <div class="spk-detail-heading"><span>IMPORT BOQ KE LAMP SPK</span></div>
                    <form method="post" action="<?= base_url('SPK/generate_lamp_spk') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="id_spk" class="js-detail-id-spk" value="0">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Target Warehouse di Lamp SPK</label>
                                <select name="warehouse_target" class="form-control" required>
                                    <option value="jawa_sumatera">HANDLING WAREHOUSE (JAWA-SUMATERA)</option>
                                    <option value="kalsul">HANDLING WAREHOUSE (KALSUL)</option>
                                </select>
                                <small class="text-muted">Qty warehouse dari BOQ akan diisikan hanya ke target yang dipilih.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Upload BOQ Cluster (Sheet: BoQ NRO Cluster)</label>
                                <input type="file" name="boq_cluster_file" class="form-control" accept=".xls,.xlsx" required>
                                <small class="text-muted">Mapping target: Item kolom C, QTY Material ke G, QTY Service ke H.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Upload BOQ Subfeeder (Sheet: BoQ NRO All Feeder)</label>
                                <input type="file" name="boq_subfeeder_file" class="form-control" accept=".xls,.xlsx" required>
                                <small class="text-muted">Mapping target: Item kolom C, QTY Material ke I, QTY Service ke J.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn budget-btn budget-btn--primary">Generate Lamp SPK</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer budget-modal__footer">
                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-spk-amandement" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content budget-modal drm-modal-shell">
            <form method="post" action="<?= base_url('SPK/update_amandement') ?>">
                <div class="modal-header budget-modal__header">
                    <div><div class="budget-modal__eyebrow">SPK</div><h5 class="modal-title mb-1 js-amandement-title">Input Amandement SPK</h5></div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_spk" class="js-am-id-spk" value="0">
                    <div class="drm-form-section">
                        <div class="spk-detail-heading"><span>DETAIL PROJECT</span></div>
                        <div class="row spk-read-grid">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="spk-read-item"><div class="spk-read-item__label">Bowheer</div><div class="spk-read-item__value js-am-bowheer">-</div></div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="spk-read-item"><div class="spk-read-item__label">Cluster</div><div class="spk-read-item__value js-am-cluster">-</div></div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <div class="spk-read-item"><div class="spk-read-item__label">HP DRM</div><div class="spk-read-item__value js-am-hp-drm">-</div></div>
                            </div>
                            <div class="col-md-12 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nama Project</div><div class="spk-read-item__value spk-read-item__value--multiline js-am-project">-</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="drm-form-section">
                        <div class="spk-detail-heading"><span>DETAIL SPK</span></div>
                        <div class="row spk-read-grid">
                            <div class="col-md-6 col-sm-6 mb-3">
                                <div class="spk-read-item"><div class="spk-read-item__label">PKS</div><div class="spk-read-item__value js-am-nomor-pks">-</div></div>
                            </div>
                            <div class="col-md-6 col-sm-6 mb-3">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nama PIC</div><div class="spk-read-item__value js-am-pic">-</div></div>
                            </div>
                        </div>
                        <div class="row spk-read-grid">
                            <div class="col-md-3 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Tanggal SPK</div><div class="spk-read-item__value js-am-tanggal-spk">-</div></div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">TOC SPK</div><div class="spk-read-item__value js-am-toc-spk">-</div></div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Akhir Kontrak</div><div class="spk-read-item__value js-am-akhir-kontrak">-</div></div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nilai SPK</div><div class="spk-read-item__value js-am-nilai-spk">-</div></div>
                            </div>
                        </div>
                        <div class="row spk-read-grid mt-3">
                            <div class="col-md-3 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Status SPK</div><div class="spk-read-item__value"><span class="badge badge-info js-am-status">-</span></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="drm-form-section mb-0">
                        <div class="spk-detail-heading"><span>AMANDEMENT</span></div>
                        <div class="row spk-read-grid">
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nomor AMD 1</div><div class="spk-read-item__value js-am-nomor-amd-1">1</div></div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Tanggal AMD 1</div><div class="spk-read-item__value js-am-tanggal-amd-1">-</div></div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nilai AMD 1</div><div class="spk-read-item__value js-am-nilai-amd-1">-</div></div>
                            </div>
                        </div>
                        <div class="row spk-read-grid mt-3">
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nomor AMD 2</div><div class="spk-read-item__value js-am-nomor-amd-2">2</div></div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Tanggal AMD 2</div><div class="spk-read-item__value js-am-tanggal-amd-2">-</div></div>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-0">
                                <div class="spk-read-item"><div class="spk-read-item__label">Nilai AMD 2</div><div class="spk-read-item__value js-am-nilai-amd-2">-</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="drm-form-section mb-0 mt-3">
                        <div class="spk-detail-heading"><span>INPUT AMANDEMENT</span></div>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Pilih AMD</label>
                                <select name="target_amandement" class="form-control js-am-target" required>
                                    <option value="1">Amandement 1</option>
                                    <option value="2">Amandement 2</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Nomor Amandement</label>
                                <input type="text" name="nomor_amandement" class="form-control js-am-nomor-text" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Tanggal Amandement</label>
                                <input type="date" name="tanggal_amandement" class="form-control js-am-tanggal" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Nilai Amandement</label>
                                <input type="text" class="form-control js-am-nilai-display" inputmode="numeric" autocomplete="off" required>
                                <input type="hidden" name="nilai_amandement" class="js-am-nilai-raw" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary">Simpan Amandement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="spk-loading-overlay d-none js-spk-loading-overlay" aria-hidden="true">
    <div class="spk-loading-overlay__box">
        <div class="spk-loading-overlay__label">Memuat nama cluster...</div>
        <div class="spk-loading-overlay__track">
            <div class="spk-loading-overlay__bar"></div>
        </div>
    </div>
</div>

<style>
.drm-table-card { border:1px solid rgba(148,163,184,.22); border-radius:24px; overflow:hidden; box-shadow:0 22px 48px rgba(15,23,42,.08); background:#fff; }
.drm-table-card .card-header { background:linear-gradient(135deg,#f8fbff,#eef6ff); border-bottom:1px solid #dbeafe; padding:1.15rem 1.35rem; }
.drm-table-card .card-body { padding:1.35rem; }
.drm-section-header { display:flex; justify-content:space-between; align-items:flex-start; }
.drm-monitor-table thead th { background:linear-gradient(180deg,#eef6fb 0%,#dcecf8 100%); color:#1f5e8a; font-size:.8rem; font-weight:800; text-transform:uppercase; }
.drm-toolbar { display:flex; justify-content:flex-end; margin-bottom:.85rem; }
.budget-btn { border:0; border-radius:999px; padding:.72rem 1.2rem; font-weight:700; }
.budget-btn--primary { background:linear-gradient(135deg,#0f4c81,#1d7ed6); color:#fff; }
.budget-btn--ghost { background:#fff; color:#334155; border:1px solid #d7e0ea; }
.budget-modal__header { background:linear-gradient(135deg,#0f4c81,#1d7ed6); color:#fff; border-bottom:0; }
.budget-modal__eyebrow { font-size:.74rem; text-transform:uppercase; letter-spacing:.14em; font-weight:800; margin-bottom:.35rem; }
.budget-modal__footer { border-top:1px solid #e7ecf3; background:#fff; }
.drm-modal-shell .modal-content { border:0; border-radius:18px; overflow:hidden; }
.drm-modal-shell .modal-body { background:#f6f8fb; padding:1.25rem; }
.drm-form-section { background:#fff; border:1px solid #e7ecf3; border-radius:14px; padding:1rem 1.1rem; margin-bottom:1rem; }
.drm-form-section__title { font-size:1rem; font-weight:700; color:#1f2937; margin-bottom:.9rem; }
.modal-xxl { max-width:78vw; }
.spk-detail-heading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .75rem;
    margin: 25px 0 25px;
    font-weight: 700;
    color: #374151;
    letter-spacing: .02em;
    text-transform: uppercase;
}
.spk-detail-heading:before,
.spk-detail-heading:after {
    content: '';
    flex: 1;
    border-top: 1px solid #cfd8e3;
}
.spk-detail-heading span {
    white-space: nowrap;
    padding: 0 .35rem;
}
.drm-modal-shell .form-control {
    min-height: 44px;
    border-radius: 12px;
    border: 1px solid #cfe0ee;
    box-shadow: none;
    background: #fff;
}
.drm-modal-shell .form-control:focus {
    border-color: #55a7d5;
    box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
}
.drm-modal-shell .form-control[readonly],
.drm-modal-shell .form-control:disabled {
    background: linear-gradient(180deg, #eef4f8 0%, #e3edf5 100%);
    border-color: #c8d9e7;
    color: #5f7488;
    cursor: not-allowed;
}
.drm-modal-shell textarea.form-control[readonly],
.drm-modal-shell textarea.form-control:disabled {
    background: linear-gradient(180deg, #eef4f8 0%, #e3edf5 100%);
}
.drm-modal-shell .select2-container {
    width: 100% !important;
}
.drm-modal-shell .select2-container .select2-selection--single {
    min-height: 44px;
    border-radius: 12px;
    border: 1px solid #cfe0ee;
    background: #fff;
    display: flex;
    align-items: center;
    padding: 0 .9rem;
    box-shadow: none;
}
.drm-modal-shell .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1f2937;
    line-height: 1.4;
    padding-left: 0;
    padding-right: 1.8rem;
}
.drm-modal-shell .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #7b8794;
}
.drm-modal-shell .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
    right: 10px;
}
.drm-modal-shell .select2-container--default.select2-container--focus .select2-selection--single,
.drm-modal-shell .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #55a7d5;
    box-shadow: 0 0 0 0.18rem rgba(85, 167, 213, 0.18);
}
.drm-modal-shell .select2-container--default.select2-container--disabled .select2-selection--single {
    background: linear-gradient(180deg, #eef4f8 0%, #e3edf5 100%);
    border-color: #c8d9e7;
    color: #5f7488;
    cursor: not-allowed;
}
.drm-modal-shell .select2-dropdown {
    border: 1px solid #cfe0ee;
    border-radius: 12px;
}
.spk-read-item {
    border: 1px solid #e5edf6;
    border-radius: 12px;
    background: #f8fbff;
    padding: .65rem .8rem;
    min-height: 78px;
}
.spk-read-item__label {
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    font-weight: 700;
    margin-bottom: .3rem;
}
.spk-read-item__value {
    font-size: .95rem;
    color: #0f172a;
    font-weight: 700;
    line-height: 1.35;
    word-break: break-word;
}
.spk-read-item__value--multiline {
    white-space: pre-wrap;
    font-weight: 600;
}
.spk-loading-overlay {
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: rgba(15, 23, 42, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
}
.spk-loading-overlay__box {
    width: min(520px, 88vw);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #d8e2ef;
    border-radius: 12px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
    padding: 14px 16px;
}
.spk-loading-overlay__label {
    font-size: .92rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
}
.spk-loading-overlay__track {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #e5edf6;
    position: relative;
}
.spk-loading-overlay__bar {
    position: absolute;
    left: -35%;
    top: 0;
    height: 100%;
    width: 35%;
    border-radius: 999px;
    background: linear-gradient(90deg, #0f4c81, #1d7ed6);
    animation: spkLoadingBar 1.1s ease-in-out infinite;
}
@keyframes spkLoadingBar {
    0% { left: -35%; }
    100% { left: 100%; }
}
</style>

<script>
(function(){
    function fillText(selector, value) {
        $(selector).text(value && String(value).trim() !== '' ? value : '-');
    }

    function formatDateDisplay(value) {
        var raw = String(value || '').trim();
        if (!raw) {
            return '-';
        }
        var parts = raw.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return raw;
    }

    function formatTocDisplay(value) {
        var raw = String(value || '').trim();
        if (!raw || raw === '-') {
            return '-';
        }
        return /^\d+$/.test(raw) ? raw + ' Hari' : raw;
    }

    function normalizeNumberRaw(value) {
        var raw = String(value || '').replace(/[^\d]/g, '');
        return raw === '' ? '' : String(parseInt(raw, 10));
    }

    function formatNumberID(value) {
        var raw = normalizeNumberRaw(value);
        return raw === '' ? '-' : new Intl.NumberFormat('id-ID').format(parseInt(raw, 10));
    }

    function applyStatusBadge($target, status) {
        var safeStatus = String(status || '-').trim();
        $target.removeClass('badge-info badge-secondary badge-success badge-danger')
            .addClass(safeStatus.toLowerCase() === 'active' ? 'badge-success' : (safeStatus.toLowerCase() === 'non aktif' ? 'badge-danger' : 'badge-secondary'))
            .text(safeStatus || '-');
    }

    function calculateAkhirKontrakDate(tanggalSpk, tocDays) {
        var tgl = String(tanggalSpk || '').trim();
        var toc = String(tocDays || '').trim();
        if (!tgl || !/^\d{4}-\d{2}-\d{2}$/.test(tgl) || !/^\d+$/.test(toc)) {
            return '';
        }
        var date = new Date(tgl + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return '';
        }
        date.setDate(date.getDate() + parseInt(toc, 10));
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    $(function(){
        if ($.fn.DataTable) {
            $('#table_spk').DataTable({ responsive: true, autoWidth: false });
        }

        if ($.fn.select2) {
            $('.js-spk-select2-pks').select2({
                width: '100%',
                dropdownParent: $('#modal-spk-create'),
                placeholder: 'Pilih PKS'
            });

            $('.js-spk-select2-bowheer').select2({
                width: '100%',
                dropdownParent: $('#modal-spk-create'),
                placeholder: 'Pilih Bowheer'
            });

            $('.js-spk-select2-cluster').select2({
                width: '100%',
                dropdownParent: $('#modal-spk-create'),
                placeholder: 'Pilih Cluster'
            });
        }

        function resetClusterOptions(message) {
            var $cluster = $('.js-spk-select2-cluster');
            $cluster.empty().append('<option value="">' + message + '</option>').val('');
            $('.js-cluster-hp-drm').val('-');
            $('.js-hp-drm-wrap').addClass('d-none');
            if ($.fn.select2) {
                $cluster.trigger('change.select2');
            }
        }

        function setClusterLoading(isLoading) {
            var $overlay = $('.js-spk-loading-overlay');
            if (isLoading) {
                $overlay.removeClass('d-none').attr('aria-hidden', 'false');
            } else {
                $overlay.addClass('d-none').attr('aria-hidden', 'true');
            }
        }

        function loadClusterOptionsByBowheer(bowheerName) {
            var normalized = String(bowheerName || '').trim().toUpperCase();
            if (normalized !== 'PT. EKA MAS REPUBLIK') {
                resetClusterOptions('Cluster tersedia khusus Bowheer PT. EKA MAS REPUBLIK');
                return;
            }

            resetClusterOptions('Memuat data cluster...');
            setClusterLoading(true);
            $.getJSON('<?= base_url('SPK/cluster_options') ?>', { bowheer: bowheerName })
                .done(function(resp){
                    var rows = (resp && resp.rows) ? resp.rows : [];
                    var $cluster = $('.js-spk-select2-cluster');
                    $cluster.empty().append('<option value="">Pilih Cluster</option>');
                    if (!rows.length) {
                        $cluster.append('<option value="">Tidak ada cluster DRM ke atas</option>');
                    } else {
                        rows.forEach(function(row){
                            var status = row.status_current ? ' [' + row.status_current + ']' : '';
                            var hpDrm = row.homepass_drm !== null && row.homepass_drm !== undefined ? row.homepass_drm : '';
                            $cluster.append('<option value="' + (row.cluster_name || '') + '" data-hp-drm="' + hpDrm + '">' + (row.cluster_name || '-') + status + '</option>');
                        });
                    }
                    $cluster.val('');
                    if ($.fn.select2) {
                        $cluster.trigger('change.select2');
                    }
                })
                .fail(function(){
                    resetClusterOptions('Gagal memuat cluster');
                })
                .always(function(){
                    setClusterLoading(false);
                });
        }

        $(document).on('change', '.js-spk-select2-bowheer', function(){
            loadClusterOptionsByBowheer($(this).val());
        });

        $(document).on('change', '.js-spk-select2-cluster', function(){
            var hpDrm = $(this).find(':selected').data('hp-drm');
            var text = '-';
            if (hpDrm !== undefined && hpDrm !== null && String(hpDrm).trim() !== '') {
                text = new Intl.NumberFormat('id-ID').format(parseFloat(hpDrm) || 0);
            }
            $('.js-cluster-hp-drm').val(text);
            if ($(this).val()) {
                $('.js-hp-drm-wrap').removeClass('d-none');
            } else {
                $('.js-hp-drm-wrap').addClass('d-none');
            }
        });

        $(document).on('input', '.js-nilai-spk-display', function(){
            var normalized = normalizeNumberRaw($(this).val());
            $('.js-nilai-spk-raw').val(normalized);
            $(this).val(normalized === '' ? '' : new Intl.NumberFormat('id-ID').format(parseInt(normalized, 10)));
        });

        $(document).on('input', '.js-am-nilai-display', function(){
            var normalized = normalizeNumberRaw($(this).val());
            $('.js-am-nilai-raw').val(normalized);
            $(this).val(normalized === '' ? '' : new Intl.NumberFormat('id-ID').format(parseInt(normalized, 10)));
        });

        function syncAkhirKontrakDisplay() {
            var tanggalSpk = $('input[name="tanggal_spk"]').val();
            var toc = $('input[name="toc_spk"]').val();
            var akhir = calculateAkhirKontrakDate(tanggalSpk, toc);
            $('.js-akhir-kontrak-display').val(akhir ? formatDateDisplay(akhir) : '-');
        }

        $(document).on('change input', 'input[name="tanggal_spk"], input[name="toc_spk"]', syncAkhirKontrakDisplay);
        syncAkhirKontrakDisplay();

        $(document).on('click', '.js-open-spk-detail', function(){
            var $btn = $(this);
            var nomorSpk = $btn.data('nomor-spk') || '-';
            $('.js-detail-id-spk').val($btn.data('id-spk') || 0);

            $('.js-spk-detail-title').text('Detail SPK - ' + nomorSpk);
            fillText('.js-d-nomor-spk', nomorSpk);
            fillText('.js-d-nomor-pks', $btn.data('nomor-pks'));
            fillText('.js-d-pic', $btn.data('pic'));
            fillText('.js-d-tanggal-spk', formatDateDisplay($btn.data('tanggal-spk')));
            fillText('.js-d-bowheer', $btn.data('bowheer'));
            fillText('.js-d-cluster', $btn.data('cluster'));
            fillText('.js-d-hp-drm', $btn.data('hp-drm'));
            fillText('.js-d-project', $btn.data('project'));
            fillText('.js-d-nilai-spk', $btn.data('nilai-spk'));
            fillText('.js-d-toc-spk', formatTocDisplay($btn.data('toc-spk')));
            var akhirKontrak = String($btn.data('akhir-kontrak') || '').trim();
            if (!akhirKontrak || akhirKontrak === '-') {
                akhirKontrak = calculateAkhirKontrakDate($btn.data('tanggal-spk'), $btn.data('toc-spk'));
            }
            fillText('.js-d-akhir-kontrak', akhirKontrak ? formatDateDisplay(akhirKontrak) : '-');
            fillText('.js-d-tanggal-amandemen-1', formatDateDisplay($btn.data('tanggal-amandemen-1')));
            fillText('.js-d-nilai-amandemen-1', $btn.data('nilai-amandemen-1'));
            fillText('.js-d-tanggal-amandemen-2', formatDateDisplay($btn.data('tanggal-amandemen-2')));
            fillText('.js-d-nilai-amandemen-2', $btn.data('nilai-amandemen-2'));
            var status = String($btn.data('status') || '-').trim();
            applyStatusBadge($('.js-d-status'), status);
        });

        function fillAmandementByNomor() {
            var nomor = String($('.js-am-target').val() || '1');
            var cache = $('#modal-spk-amandement').data('amData') || {};
            var tanggal = nomor === '1' ? (cache.tanggal1 || '') : (cache.tanggal2 || '');
            var nilaiRaw = nomor === '1' ? (cache.nilai1Raw || '') : (cache.nilai2Raw || '');
            var nomorText = nomor === '1' ? (cache.nomor1 || '') : (cache.nomor2 || '');
            if (!tanggal || tanggal === '-') {
                var now = new Date();
                var y = now.getFullYear();
                var m = String(now.getMonth() + 1).padStart(2, '0');
                var d = String(now.getDate()).padStart(2, '0');
                tanggal = y + '-' + m + '-' + d;
            }
            $('.js-am-tanggal').val(tanggal);
            $('.js-am-nomor-text').val(nomorText);
            var normalized = normalizeNumberRaw(nilaiRaw);
            $('.js-am-nilai-raw').val(normalized);
            $('.js-am-nilai-display').val(normalized === '' ? '' : new Intl.NumberFormat('id-ID').format(parseInt(normalized, 10)));
        }

        $(document).on('change', '.js-am-target', function(){
            fillAmandementByNomor();
        });

        $(document).on('click', '.js-open-spk-amandement', function(){
            var $btn = $(this);
            var nomorSpk = $btn.data('nomor-spk') || '-';
            var status = String($btn.data('status') || '-').trim();
            var akhirKontrak = String($btn.data('akhir-kontrak') || '').trim();
            if (!akhirKontrak || akhirKontrak === '-') {
                akhirKontrak = calculateAkhirKontrakDate($btn.data('tanggal-spk'), $btn.data('toc-spk'));
            }

            $('.js-amandement-title').text('Input Amandement SPK - ' + nomorSpk);
            $('.js-am-id-spk').val($btn.data('id-spk') || 0);
            fillText('.js-am-bowheer', $btn.data('bowheer'));
            fillText('.js-am-cluster', $btn.data('cluster'));
            fillText('.js-am-hp-drm', $btn.data('hp-drm'));
            fillText('.js-am-project', $btn.data('project'));
            fillText('.js-am-nomor-pks', $btn.data('nomor-pks'));
            fillText('.js-am-pic', $btn.data('pic'));
            fillText('.js-am-tanggal-spk', formatDateDisplay($btn.data('tanggal-spk')));
            fillText('.js-am-toc-spk', formatTocDisplay($btn.data('toc-spk')));
            fillText('.js-am-akhir-kontrak', akhirKontrak ? formatDateDisplay(akhirKontrak) : '-');
            fillText('.js-am-nilai-spk', $btn.data('nilai-spk'));
            fillText('.js-am-tanggal-amd-1', formatDateDisplay($btn.data('tanggal-amandemen-1')));
            fillText('.js-am-nilai-amd-1', formatNumberID($btn.data('nilai-amandemen-1-raw')));
            fillText('.js-am-tanggal-amd-2', formatDateDisplay($btn.data('tanggal-amandemen-2')));
            fillText('.js-am-nilai-amd-2', formatNumberID($btn.data('nilai-amandemen-2-raw')));
            applyStatusBadge($('.js-am-status'), status);

            $('#modal-spk-amandement').data('amData', {
                tanggal1: String($btn.data('tanggal-amandemen-1') || '').trim(),
                nilai1Raw: String($btn.data('nilai-amandemen-1-raw') || '').trim(),
                nomor1: String($btn.data('nomor-amandemen-1') || '').trim(),
                tanggal2: String($btn.data('tanggal-amandemen-2') || '').trim(),
                nilai2Raw: String($btn.data('nilai-amandemen-2-raw') || '').trim(),
                nomor2: String($btn.data('nomor-amandemen-2') || '').trim()
            });

            $('.js-am-target').val('1');
            fillAmandementByNomor();
        });
    });
})();
</script>
