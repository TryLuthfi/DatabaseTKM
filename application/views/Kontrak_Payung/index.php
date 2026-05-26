<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$pksRows = $pksRows ?? [];
$openDetailId = (int) ($openDetailId ?? 0);
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0 text-dark">Kontrak Payung (PKS)</h1></div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($flashSuccess)): ?><div class="alert alert-success"><?= $flashSuccess ?></div><?php endif; ?>
            <?php if (!empty($flashError)): ?><div class="alert alert-danger"><?= $flashError ?></div><?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="drm-toolbar">
                        <button type="button" class="btn budget-btn budget-btn--primary" data-toggle="modal" data-target="#modal-pks-create">
                            <i class="fas fa-plus mr-1"></i> Tambah PKS
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary shadow-sm drm-table-card">
                        <div class="card-header drm-section-header">
                            <div><h3 class="card-title mb-1">Monitoring PKS</h3></div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_pks" class="table table-bordered table-hover drm-monitor-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor PKS</th>
                                            <th>Tanggal</th>
                                            <th>PIC</th>
                                            <th>Jenis</th>
                                            <th>Status PKS</th>
                                            <th>Workflow</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pksRows as $i => $row): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars((string) ($row['nomor_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['tanggal_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['pic_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><?= htmlspecialchars((string) ($row['jenis_pks'] ?? '-'), ENT_QUOTES) ?></td>
                                                <td><span class="badge badge-info"><?= htmlspecialchars((string) ($row['status_pks'] ?? '-'), ENT_QUOTES) ?></span></td>
                                                <td><span class="badge badge-primary"><?= htmlspecialchars((string) ($row['workflow_status'] ?? '-'), ENT_QUOTES) ?></span></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary js-open-detail" data-id="<?= (int) $row['id_pks'] ?>">Detail</button>
                                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= base_url('Kontrak_Payung/print_doc/' . (int) $row['id_pks'] . '/doc1') ?>">Print Kontrak</a>
                                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= base_url('Kontrak_Payung/print_doc/' . (int) $row['id_pks'] . '/doc2') ?>">Print Surat Pernyataan</a>
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

<div class="modal fade" id="modal-pks-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content budget-modal drm-modal-shell">
            <form method="post" action="<?= base_url('Kontrak_Payung/save') ?>">
                <div class="modal-header budget-modal__header">
                    <div><div class="budget-modal__eyebrow">PKS</div><h5 class="modal-title mb-1">Tambah PKS Baru</h5></div>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="drm-form-section">
                        <div class="drm-form-section__title">Data PKS</div>
                        <div class="row">
                            <div class="col-md-3 form-group"><label>Tanggal PKS</label><input type="date" name="tanggal_pks" class="form-control" required></div>
                            <div class="col-md-3 form-group"><label>PIC PKS</label><input type="text" name="pic_pks" class="form-control" required></div>
                            <div class="col-md-3 form-group"><label>Jenis PKS</label>
                                <select name="jenis_pks" class="form-control" required>
                                    <option value="MDR">PKS Mandor (MDR)</option>
                                    <option value="MPL">PKS Mitra Pelaksana (MPL)</option>
                                    <option value="PKS">PKS Kontrak Putus (PKS)</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group"><label>Status</label><select name="status_pks" class="form-control"><option value="active">active</option><option value="non aktif">non aktif</option></select></div>
                            <div class="col-md-3 form-group"><label>No Telp</label><input type="text" name="no_telp" class="form-control"></div>
                            <div class="col-md-3 form-group"><label>Email PIC</label><input type="email" name="email_pic" class="form-control"></div>
                            <div class="col-md-3 form-group"><label>TTL</label><input type="text" name="ttl" class="form-control" placeholder="Tempat, Tanggal Lahir"></div>
                            <div class="col-md-3 form-group"><label>No KTP</label><input type="text" name="no_ktp" class="form-control"></div>
                            <div class="col-md-3 form-group"><label>Grade PIC</label><input type="text" name="grade_pic" class="form-control"></div>
                            <div class="col-md-3 form-group"><label>Area PIC</label><input type="text" name="area_pic" class="form-control"></div>
                            <div class="col-md-6 form-group mb-0"><label>Alamat KTP</label><input type="text" name="alamat_ktp" class="form-control"></div>
                            <div class="col-md-6 form-group mb-0">
                                <label>TOC PKS (Otomatis +1 Tahun)</label>
                                <input type="text" name="toc_pks" class="form-control js-toc-pks" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer budget-modal__footer">
                    <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn budget-btn budget-btn--primary">Simpan PKS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-pks-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content budget-modal drm-modal-shell">
            <div class="modal-header budget-modal__header">
                <div><div class="budget-modal__eyebrow">PKS</div><h5 class="modal-title mb-1 js-detail-title">Detail PKS</h5></div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="drm-form-section">
                    <div class="row">
                        <div class="col-md-3"><strong>Nomor PKS</strong><div class="js-d-nomor">-</div></div>
                        <div class="col-md-3"><strong>PIC</strong><div class="js-d-pic">-</div></div>
                        <div class="col-md-2"><strong>TTL</strong><div class="js-d-ttl">-</div></div>
                        <div class="col-md-2"><strong>Jenis</strong><div class="js-d-jenis">-</div></div>
                        <div class="col-md-2"><strong>Status PKS</strong><div class="js-d-status-pks">-</div></div>
                        <div class="col-md-2"><strong>Workflow</strong><div class="js-d-workflow">-</div></div>
                    </div>
                </div>
                <div class="drm-form-section">
                    <div class="drm-form-section__title">Upload Dokumen TTD Basah</div>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="js-upload-doc1">
                                <input type="hidden" name="doc_type" value="doc1">
                                <input type="file" name="signed_file" class="form-control mb-2" required>
                                <button class="btn btn-outline-primary btn-sm">Upload Doc 1</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="js-upload-doc2">
                                <input type="hidden" name="doc_type" value="doc2">
                                <input type="file" name="signed_file" class="form-control mb-2" required>
                                <button class="btn btn-outline-primary btn-sm">Upload Doc 2</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="drm-form-section">
                    <div class="drm-form-section__title">Approval 1 Level</div>
                    <div class="mb-2">
                        <a href="#" class="btn btn-warning js-btn-submit-approval">Kirim ke Approval</a>
                        <a href="#" class="btn btn-success js-btn-approve">Approve</a>
                    </div>
                    <form method="post" class="js-form-reject">
                        <label>Catatan Reject</label>
                        <textarea name="approval_note" class="form-control" required></textarea>
                        <button class="btn btn-danger mt-2">Reject</button>
                    </form>
                </div>
                <div class="drm-form-section">
                    <div class="drm-form-section__title">SPK Terkait</div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-spk-rel">
                            <thead><tr><th>No SPK</th><th>Bowheer</th><th>Project</th><th>Nilai</th><th>Status</th></tr></thead>
                            <tbody><tr><td colspan="5" class="text-center text-muted">-</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer budget-modal__footer">
                <button type="button" class="btn budget-btn budget-btn--ghost" data-dismiss="modal">Tutup</button>
            </div>
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
</style>

<script>
(function(){
    function renderSpkRows(rows) {
        var $tbody = $('#table-spk-rel tbody');
        $tbody.empty();
        if (!rows || !rows.length) {
            $tbody.append('<tr><td colspan="5" class="text-center text-muted">Belum ada SPK.</td></tr>');
            return;
        }
        rows.forEach(function(r){
            $tbody.append('<tr>' +
                '<td>' + (r.nomor_spk || '-') + '</td>' +
                '<td>' + (r.bowheer || '-') + '</td>' +
                '<td>' + (r.project_name || '-') + '</td>' +
                '<td class="text-right">' + (new Intl.NumberFormat('id-ID').format(parseFloat(r.nilai_spk || 0))) + '</td>' +
                '<td>' + (r.status_spk || '-') + '</td>' +
            '</tr>');
        });
    }

    function openDetailModal(id) {
        $.getJSON('<?= base_url('Kontrak_Payung/detail_json/') ?>' + id, function(resp){
            if (!resp || resp.status !== 'success') { return; }
            var p = resp.pks || {};
            $('.js-detail-title').text('Detail PKS - ' + (p.nomor_pks || '-'));
            $('.js-d-nomor').text(p.nomor_pks || '-');
            $('.js-d-pic').text(p.pic_pks || '-');
            $('.js-d-ttl').text(p.ttl || '-');
            $('.js-d-jenis').text(p.jenis_pks || '-');
            $('.js-d-status-pks').text(p.status_pks || '-');
            $('.js-d-workflow').text(p.workflow_status || '-');

            $('.js-upload-doc1').attr('action', '<?= base_url('Kontrak_Payung/upload_signed/') ?>' + id);
            $('.js-upload-doc2').attr('action', '<?= base_url('Kontrak_Payung/upload_signed/') ?>' + id);
            $('.js-btn-submit-approval').attr('href', '<?= base_url('Kontrak_Payung/submit_approval/') ?>' + id);
            $('.js-btn-approve').attr('href', '<?= base_url('Kontrak_Payung/approve/') ?>' + id);
            $('.js-form-reject').attr('action', '<?= base_url('Kontrak_Payung/reject/') ?>' + id);

            renderSpkRows(resp.spk_rows || []);

            var wf = String(p.workflow_status || '').toLowerCase();
            $('.js-btn-submit-approval').toggle(wf === 'draft' || wf === 'rejected');
            $('.js-btn-approve, .js-form-reject').toggle(wf === 'waiting_approval');

            $('#modal-pks-detail').modal('show');
        });
    }

    $(function(){
        if ($.fn.DataTable) {
            $('#table_pks').DataTable({responsive:true, autoWidth:false});
        }

        $(document).on('click', '.js-open-detail', function(){
            openDetailModal($(this).data('id'));
        });

        function updateTocFromTanggal() {
            var tanggal = $('input[name=\"tanggal_pks\"]').val();
            if (!tanggal) {
                $('.js-toc-pks').val('');
                return;
            }

            var d = new Date(tanggal + 'T00:00:00');
            if (isNaN(d.getTime())) {
                $('.js-toc-pks').val('');
                return;
            }

            d.setFullYear(d.getFullYear() + 1);
            var dd = String(d.getDate()).padStart(2, '0');
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var yyyy = d.getFullYear();
            $('.js-toc-pks').val(dd + '-' + mm + '-' + yyyy);
        }

        $(document).on('change', 'input[name=\"tanggal_pks\"]', updateTocFromTanggal);
        updateTocFromTanggal();

        var openDetailId = <?= (int) $openDetailId ?>;
        if (openDetailId > 0) {
            openDetailModal(openDetailId);
        }
    });
})();
</script>
