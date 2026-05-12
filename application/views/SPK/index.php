<?php
$flashSuccess = $this->session->flashdata('success');
$flashError = $this->session->flashdata('error');
$pksRows = $pksRows ?? [];
$spkRows = $spkRows ?? [];
?>
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1>SPK</h1>
      <?php if ($flashSuccess): ?><div class="alert alert-success"><?= $flashSuccess ?></div><?php endif; ?>
      <?php if ($flashError): ?><div class="alert alert-danger"><?= $flashError ?></div><?php endif; ?>

      <div class="card">
        <div class="card-header"><strong>Form SPK</strong></div>
        <div class="card-body">
          <form method="post" action="<?= base_url('SPK/save') ?>" class="row">
            <div class="col-md-4 form-group"><label>PKS</label>
              <select class="form-control" name="id_pks" required>
                <option value="">Pilih PKS</option>
                <?php foreach ($pksRows as $pks): ?>
                  <option value="<?= (int) $pks['id_pks'] ?>"><?= htmlspecialchars((string) $pks['nomor_pks'] . ' | ' . $pks['pic_pks'], ENT_QUOTES) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 form-group"><label>Tanggal SPK</label><input type="date" name="tanggal_spk" class="form-control" required></div>
            <div class="col-md-3 form-group"><label>Bowheer</label><input type="text" name="bowheer" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Project</label><input type="text" name="project_name" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Nilai SPK</label><input type="number" step="0.01" name="nilai_spk" class="form-control"></div>
            <div class="col-md-3 form-group"><label>TOC SPK</label><input type="text" name="toc_spk" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Tgl Amandemen 1</label><input type="date" name="tanggal_amandemen_1" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Nilai Amandemen 1</label><input type="number" step="0.01" name="nilai_amandemen_1" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Tgl Amandemen 2</label><input type="date" name="tanggal_amandemen_2" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Nilai Amandemen 2</label><input type="number" step="0.01" name="nilai_amandemen_2" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Status</label>
              <select name="status_spk" class="form-control"><option value="active">active</option><option value="non aktif">non aktif</option></select>
            </div>
            <div class="col-12"><button class="btn btn-primary">Simpan SPK</button></div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><strong>Daftar SPK</strong></div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-striped" id="table_spk">
            <thead><tr><th>No</th><th>No SPK</th><th>No PKS</th><th>PIC</th><th>Bowheer</th><th>Project</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php $no = 1; foreach ($spkRows as $row): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars((string) $row['nomor_spk'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars((string) $row['nomor_pks'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars((string) $row['pic_pks'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars((string) $row['bowheer'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars((string) $row['project_name'], ENT_QUOTES) ?></td>
                  <td><?= number_format((float) $row['nilai_spk'], 0, ',', '.') ?></td>
                  <td><?= htmlspecialchars((string) $row['status_spk'], ENT_QUOTES) ?></td>
                  <td><a class="btn btn-sm btn-secondary" target="_blank" href="<?= base_url('SPK/print_doc/' . (int) $row['id_spk']) ?>">Print</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){ if ($('#table_spk').length) { $('#table_spk').DataTable(); } });
</script>
