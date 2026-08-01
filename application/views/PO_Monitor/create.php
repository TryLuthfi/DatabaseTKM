<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 ">
                    <h1 class="m-0 text-dark" style="text-align: center;">Tambah PO</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="<?= site_url('PO_Monitor/store') ?>">
                            <div class="form-group">
                                <label>Status PO</label>
                                <select name="status_po" id="po_monitor_create_status" class="form-control">
                                    <option value="ON PO">ON PO</option>
                                    <option value="NY PO">NY PO</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>PO Number</label>
                                <input type="text" name="po_number" id="po_monitor_create_po_number" class="form-control" required />
                                <small class="form-text text-muted po-monitor-ny-help d-none">Boleh kosong untuk status NY PO.</small>
                            </div>

                            <div class="form-group">
                                <label>PO Date</label>
                                <input type="date" name="po_date" class="form-control" />
                                <small class="form-text text-muted po-monitor-ny-help d-none">Boleh kosong untuk status NY PO.</small>
                            </div>

                            <div class="form-group">
                                <label>Total Value (contoh: 100000000)</label>
                                <input type="text" name="total_value" class="form-control" required />
                            </div>

                            <div class="form-group">
                                <label>Bowheer (optional)</label>
                                <select name="id_bowheer" class="form-control">
                                    <option value="0">-- pilih (opsional) --</option>
                                    <?php foreach ($bowheers as $b): ?>
                                        <option value="<?= $b['id_bowheer'] ?>"><?= htmlspecialchars($b['nama_bowheer'] . (!empty($b['pic']) ? ' - ' . $b['pic'] : '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Type Project</label>
                                <input type="text" name="type_project" class="form-control" />
                            </div>

                            <div class="form-group po-monitor-ny-field d-none">
                                <label>Week Target NY PO 2026 (optional, contoh: W34)</label>
                                <input type="text" name="target_week" class="form-control" placeholder="W34" />
                            </div>

                            <div class="form-row po-monitor-ny-field d-none">
                                <div class="form-group col-md-6">
                                    <label>Regional</label>
                                    <input type="text" name="regional" class="form-control" />
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Kota PO</label>
                                    <input type="text" name="kota_po" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group po-monitor-ny-field d-none">
                                <label>Detail PO</label>
                                <textarea name="detail_po" class="form-control"></textarea>
                            </div>

                            <div class="form-group po-monitor-ny-field d-none">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Master Term (pilih preset)</label>
                                <select name="master_id" class="form-control">
                                    <option value="0">Custom / Single Term (100%)</option>
                                    <?php foreach ($masters as $m): ?>
                                        <option value="<?= $m['id_master'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control"></textarea>
                            </div>

                            <button class="btn btn-success">Simpan PO</button>
                            <a href="<?= site_url('PO_Monitor') ?>" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    (function() {
        function syncPoMonitorCreateStatus() {
            var isNyPo = $('#po_monitor_create_status').val() === 'NY PO';
            $('#po_monitor_create_po_number').prop('required', !isNyPo);
            $('.po-monitor-ny-field, .po-monitor-ny-help').toggleClass('d-none', !isNyPo);
        }

        $(document).on('change', '#po_monitor_create_status', syncPoMonitorCreateStatus);
        syncPoMonitorCreateStatus();
    })();
</script>
