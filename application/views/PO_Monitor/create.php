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
                                <label>PO Number</label>
                                <input type="text" name="po_number" class="form-control" required />
                            </div>

                            <div class="form-group">
                                <label>PO Date</label>
                                <input type="date" name="po_date" class="form-control" />
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
                                        <option value="<?= $b['id_bowheer'] ?>"><?= htmlspecialchars($b['nama_bowheer']) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
