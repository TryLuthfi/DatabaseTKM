<?php
$escapeEdit = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$editLabels = [
    'PENDING_SITAC' => 'Menunggu review SITAC HO', 'PENDING_FINANCE' => 'Menunggu review Finance', 'APPLIED' => 'Perubahan diterapkan', 'REJECTED' => 'Ditolak', 'STALE' => 'Data berubah — perlu pengajuan ulang',
    'DIRECT_EDIT' => 'Edit langsung SITAC HO', 'SUBMITTED' => 'Perubahan diajukan', 'SITAC_APPROVED' => 'Disetujui SITAC HO', 'FINANCE_APPROVED' => 'Disetujui Finance', 'SITAC_REJECTED' => 'Ditolak SITAC HO', 'FINANCE_REJECTED' => 'Ditolak Finance',
    'submission_date' => 'Tanggal Pengajuan Astri', 'hp_donasi' => 'HP Donasi Astri', 'nominal_pengajuan_area' => 'Nominal Donasi', 'nominal_nego_emr' => 'Nominal Nego EMR', 'nominal_per_homepass' => 'Nominal per Homepass',
    'bank_name' => 'Nama Bank', 'bank_account_number' => 'Nomor Rekening', 'recipient_name' => 'Nama Penerima', 'recipient_phone' => 'Telepon Penerima', 'recipient_position' => 'Jabatan Penerima', 'recipient_period' => 'Masa Jabatan Penerima',
    'free_wifi_qty' => 'Jumlah Free Wifi', 'free_wifi_period_month' => 'Periode Free Wifi (bulan)', 'astri_batch_number' => 'Nomor Batch Astri', 'astri_batch_approved_at' => 'Tanggal Batch Approval', 'remark_batch_approval' => 'Catatan Batch',
    'regency_id' => 'ID Kabupaten/Kota', 'district_id' => 'ID Kecamatan', 'district_name' => 'Kecamatan', 'village_id' => 'ID Desa/Kelurahan', 'village_name' => 'Desa/Kelurahan',
    'pic_no' => 'Urutan', 'pic_name' => 'Nama PIC', 'pic_phone' => 'Telepon', 'pic_position' => 'Jabatan', 'pic_period' => 'Masa Jabatan', 'is_primary' => 'PIC Utama',
];
$editLabel = static function ($key) use ($editLabels) { return $editLabels[$key] ?? $key; };
?>
<section class="card my-3" aria-labelledby="batch-edit-history-title">
    <div class="card-header"><h5 id="batch-edit-history-title" class="mb-0">Approval Perubahan &amp; History Data Batch</h5></div>
    <div class="card-body">
        <p>Draft, Waiting Batch Approval dan Reject Data Batch dapat diedit langsung tanpa history. Pada status lainnya, perubahan memerlukan approval SITAC HO sesuai wilayah; SITAC HO dapat edit langsung dengan alasan dan history. Koreksi data pembayaran setelah release memerlukan review Finance. Nilai pembayaran aktual tetap tersimpan.</p>
        <?php if (empty($batchEditReady)): ?>
            <div class="alert alert-warning">Fitur perubahan belum siap. Hubungi administrator.</div>
        <?php elseif (empty($batchEditHistory)): ?>
            <p class="text-muted mb-0">Belum ada pengajuan perubahan atau history edit pada status yang dilindungi.</p>
        <?php else: foreach ($batchEditHistory as $edit): ?>
            <details class="border rounded p-3 mb-3" <?= in_array($edit['status'], ['PENDING_SITAC', 'PENDING_FINANCE'], true) ? 'open' : '' ?>>
                <summary><strong><?= $escapeEdit($editLabel($edit['status'])) ?></strong> · <?= $escapeEdit($edit['submitted_at']) ?> · Pemohon #<?= (int) $edit['submitted_by'] ?><?= !empty($edit['finance_required']) ? ' · KOREKSI PASCA BAYAR / REVIEW FINANCE' : '' ?></summary>
                <p class="mt-2 mb-1"><strong>Alasan:</strong> <?= nl2br($escapeEdit($edit['reason'])) ?></p>
                <p><strong>Staging pengajuan:</strong> <?= $escapeEdit($edit['staging_at_submit']) ?></p>
                <div class="table-responsive"><table class="table table-sm table-bordered">
                    <thead><tr><th scope="col">Field</th><th scope="col">Sebelum</th><th scope="col">Usulan / Sesudah</th></tr></thead>
                    <tbody>
                    <?php foreach (['batch' => 'Data', 'location' => 'Lokasi'] as $group => $label): foreach ($edit['after'][$group] as $field => $value):
                        $oldValue = $edit['before'][$group][$field] ?? null; if ($oldValue === $value) { continue; } ?>
                        <tr><th scope="row"><?= $escapeEdit($label . ' · ' . $editLabel($field)) ?></th><td style="white-space:pre-wrap;overflow-wrap:anywhere"><?= $escapeEdit($oldValue ?? '—') ?></td><td style="white-space:pre-wrap;overflow-wrap:anywhere"><?= $escapeEdit($value ?? '—') ?></td></tr>
                    <?php endforeach; endforeach; ?>
                    <?php if ($edit['before']['pics'] !== $edit['after']['pics']): ?>
                        <tr><th scope="row">PIC</th><?php foreach (['before', 'after'] as $side): ?><td><?php foreach ($edit[$side]['pics'] as $pic): ?><div class="mb-2"><?php foreach ($pic as $key => $value): ?><?= $escapeEdit($editLabel($key)) ?>: <?= $escapeEdit($value ?? '—') ?><br><?php endforeach; ?></div><?php endforeach; ?></td><?php endforeach; ?></tr>
                    <?php endif; ?>
                    </tbody>
                </table></div>
                <ul><?php foreach ($edit['events'] as $event): ?><li><strong><?= $escapeEdit($editLabel($event['event_type'])) ?></strong> · <?= $escapeEdit($event['actor_name']) ?> (#<?= (int) $event['actor_id'] ?>) · <?= $escapeEdit($event['created_at']) ?> · staging <?= $escapeEdit(function_exists('batchDetailStatusLabel') ? batchDetailStatusLabel($event['staging_status']) : $event['staging_status']) ?><br><?= nl2br($escapeEdit($event['note'])) ?></li><?php endforeach; ?></ul>
                <?php $canReviewEdit = ($edit['status'] === 'PENDING_SITAC' && !empty($batchEditSitac)) || ($edit['status'] === 'PENDING_FINANCE' && !empty($batchEditFinance));
                $currentEditUser = (int) $this->session->userdata('id_user');
                $canReviewEdit = $canReviewEdit && $currentEditUser !== (int) $edit['submitted_by'] && ($edit['status'] !== 'PENDING_FINANCE' || $currentEditUser !== (int) $edit['sitac_reviewed_by']);
                if ($canReviewEdit): ?>
                    <form method="post" action="<?= base_url('Batch_Approval_MyRep/reviewBatchEdit') ?>" data-role-guard-exempt="1">
                        <input type="hidden" name="batch_edit_csrf" value="<?= $escapeEdit($batchEditCsrf ?? '') ?>"><input type="hidden" name="cluster_id" value="<?= (int) $cluster['id_myrep_cluster'] ?>"><input type="hidden" name="id_request" value="<?= (int) $edit['id_request'] ?>">
                        <div class="form-group"><label for="edit-review-note-<?= (int) $edit['id_request'] ?>">Catatan review (wajib)</label><textarea id="edit-review-note-<?= (int) $edit['id_request'] ?>" name="review_note" class="form-control" required rows="2" maxlength="4000"></textarea></div>
                        <button type="submit" name="action" value="APPROVE" class="btn btn-success">Setujui <?= $edit['status'] === 'PENDING_FINANCE' ? 'koreksi Finance' : 'perubahan' ?></button>
                        <button type="submit" name="action" value="REJECT" class="btn btn-outline-danger">Tolak</button>
                    </form>
                <?php elseif (in_array($edit['status'], ['PENDING_SITAC', 'PENDING_FINANCE'], true)): ?><p class="text-muted mb-0">Menunggu reviewer <?= $edit['status'] === 'PENDING_FINANCE' ? 'Finance HO' : 'SITAC HO' ?> wilayah yang berbeda dari pemohon.</p><?php endif; ?>
            </details>
        <?php endforeach; endif; ?>
    </div>
</section>
