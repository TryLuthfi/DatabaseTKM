<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'libraries/Batch_edit_policy.php';
require_once APPPATH . 'libraries/Batch_workflow_policy.php';
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MBatch_edit_approval extends CI_Model
{
    public function ready()
    {
        foreach (['tb_myrep_batch_edit_request', 'tb_myrep_batch_edit_event', 'tb_myrep_batch_approval', 'tb_myrep_cluster', 'tb_myrep_batch_approval_pic'] as $table) {
            if (!$this->db->table_exists($table)) { return false; }
            $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table])->row_array();
            if (strtoupper((string) ($engine['ENGINE'] ?? '')) !== 'INNODB') { return false; }
        }
        return true;
    }

    /** Exact active city/province/regional mapping, not a global role shortcut. */
    public function mapped(array $cluster, $column)
    {
        if (!in_array($column, ['sitac_ho', 'finance_ho'], true)) { return false; }
        if (!$this->db->table_exists('tb_master_user_new') || !$this->db->table_exists('tb_myrep_pic_mapping_city')) { return false; }
        $user = $this->db->get_where('tb_master_user_new', ['id' => (int) $this->session->userdata('id_user')])->row_array();
        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '' || empty($cluster['city_name'])) { return false; }
        $this->db->from('tb_myrep_pic_mapping_city')->where('UPPER(city_name)', strtoupper(trim($cluster['city_name'])));
        foreach (['province_name', 'regional_name'] as $key) {
            if (!empty($cluster[$key])) { $this->db->where('UPPER(' . $key . ')', strtoupper(trim($cluster[$key]))); }
        }
        if ($this->db->field_exists('is_active', 'tb_myrep_pic_mapping_city')) { $this->db->where('is_active', 1); }
        $this->db->where(myrep_pic_column_contains_sql($this->db, '`' . $column . '`', $nik), null, false);
        return !empty($this->db->select('1 AS hit', false)->limit(1)->get()->row_array());
    }

    public function context($clusterId)
    {
        $cluster = $this->db->get_where('tb_myrep_cluster', ['id_myrep_cluster' => (int) $clusterId])->row_array();
        $batch = $this->db->get_where('tb_myrep_batch_approval', ['id_myrep_cluster' => (int) $clusterId])->row_array();
        if (!$cluster || !$batch) { return []; }
        $pics = $this->db->order_by('pic_no', 'ASC')->get_where('tb_myrep_batch_approval_pic', ['id_batch_approval' => (int) $batch['id_batch_approval']])->result_array();
        return ['batch' => $batch, 'cluster' => $cluster, 'pics' => $pics];
    }

    public function token(array $context)
    {
        return $context ? Batch_edit_policy::fingerprint($context['batch'], $context['cluster'], $context['pics']) : '';
    }

    private function locked($clusterId, $batchId)
    {
        // All writers lock cluster first, batch second; payment/status writes also lock these rows.
        $cluster = $this->db->query('SELECT * FROM tb_myrep_cluster WHERE id_myrep_cluster = ? FOR UPDATE', [(int) $clusterId])->row_array();
        $batch = $this->db->query('SELECT * FROM tb_myrep_batch_approval WHERE id_myrep_cluster = ? AND id_batch_approval = ? FOR UPDATE', [(int) $clusterId, (int) $batchId])->row_array();
        if (!$cluster || !$batch) { throw new RuntimeException('Batch tidak ditemukan atau ID batch tidak sesuai cluster.'); }
        $pics = $this->db->order_by('pic_no', 'ASC')->get_where('tb_myrep_batch_approval_pic', ['id_batch_approval' => (int) $batchId])->result_array();
        return ['batch' => $batch, 'cluster' => $cluster, 'pics' => $pics];
    }

    private function event($requestId, $type, array $batch, $note)
    {
        $this->db->insert('tb_myrep_batch_edit_event', [
            'id_request' => $requestId, 'event_type' => $type,
            'actor_id' => (int) $this->session->userdata('id_user'),
            'actor_name' => (string) ($this->session->userdata('nama_user') ?: $this->session->userdata('username_user') ?: 'User #' . (int) $this->session->userdata('id_user')),
            'staging_status' => Batch_edit_policy::effectiveStage($batch), 'note' => $note, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function apply(array $context, array $after, $resubmit = false)
    {
        $actor = (int) $this->session->userdata('id_user');
        $batch = $after['batch'];
        // Actual release amount, timestamp and transfer proof are intentionally absent.
        $batch['updated_by'] = $actor;
        if ($resubmit) { $batch['staging_status'] = 'WAITING_BATCH_APPROVAL'; $batch['submitted_to_ho_at'] = date('Y-m-d H:i:s'); }
        $location = $after['location']; $location['updated_by'] = $actor;
        if ($resubmit) { $location['status_current'] = 'WAITING HO'; }
        $this->db->where('id_myrep_cluster', $context['cluster']['id_myrep_cluster'])->update('tb_myrep_cluster', $location);
        $this->db->where('id_batch_approval', $context['batch']['id_batch_approval'])->update('tb_myrep_batch_approval', $batch);
        $this->db->where('id_batch_approval', $context['batch']['id_batch_approval'])->delete('tb_myrep_batch_approval_pic');
        foreach ($after['pics'] as $pic) {
            $pic['id_batch_approval'] = $context['batch']['id_batch_approval'];
            $this->db->insert('tb_myrep_batch_approval_pic', $pic);
        }
    }

    private function finish()
    {
        if (!$this->db->trans_status()) { throw new RuntimeException('Transaksi gagal. Tidak ada perubahan disimpan.'); }
        $this->db->trans_commit();
    }

    public function submit($clusterId, $batchId, array $batch, array $location, array $pics, $reason, $token, $resubmit = false)
    {
        if (!$this->ready()) { return ['ok' => false, 'message' => 'Migrasi approval edit belum tersedia atau tabel belum InnoDB. Hubungi administrator.']; }
        $this->db->trans_begin();
        try {
            $context = $this->locked($clusterId, $batchId);
            if (!hash_equals($this->token($context), (string) $token)) { throw new RuntimeException('Data atau staging telah berubah. Muat ulang halaman sebelum mengedit.'); }
            $pending = $this->db->get_where('tb_myrep_batch_edit_request', ['pending_batch_id' => (int) $batchId])->row_array();
            if ($pending) { throw new RuntimeException('Masih ada pengajuan perubahan pending. Selesaikan pengajuan tersebut terlebih dahulu.'); }
            $before = Batch_edit_policy::snapshot($context['batch'], $context['cluster'], $context['pics']);
            $after = Batch_edit_policy::snapshot($batch, $location, $pics);
            if ($before === $after && !$resubmit) { throw new RuntimeException('Tidak ada perubahan data.'); }
            $protected = Batch_edit_policy::protectedStage($context['batch']);
            if ($protected && !Batch_edit_policy::protectedStage(array_replace($context['batch'], $after['batch']))) {
                throw new RuntimeException('Nomor dan tanggal approval tidak boleh dikosongkan bila menyebabkan status batch turun. Gunakan alur staging yang berlaku.');
            }
            if (!$protected) {
                if ($resubmit && $context['batch']['staging_status'] !== 'NEED_REVISE') { throw new RuntimeException('Status revisi telah berubah.'); }
                $this->apply($context, $after, $resubmit); $this->finish();
                return ['ok' => true, 'message' => $resubmit ? 'Revisi berhasil disubmit ulang ke SITAC HO.' : 'Data batch berhasil diperbarui.'];
            }
            $reason = trim((string) $reason);
            if ($reason === '') { throw new RuntimeException('Alasan perubahan wajib diisi untuk status di atas draft / waiting approval / reject data batch.'); }
            $sitac = $this->mapped($context['cluster'], 'sitac_ho');
            $finance = Batch_edit_policy::financeRequired($context['batch'], $before, $after);
            $direct = $sitac && !$finance;
            $status = $direct ? 'APPLIED' : ($sitac ? 'PENDING_FINANCE' : 'PENDING_SITAC');
            $this->db->insert('tb_myrep_batch_edit_request', [
                'id_batch_approval' => (int) $batchId, 'id_myrep_cluster' => (int) $clusterId,
                'pending_batch_id' => $direct ? null : (int) $batchId, 'status' => $status, 'reason' => $reason,
                'before_json' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'baseline_hash' => $this->token($context), 'staging_at_submit' => Batch_edit_policy::effectiveStage($context['batch']),
                'finance_required' => $finance ? 1 : 0, 'submitted_by' => (int) $this->session->userdata('id_user'),
                'submitted_at' => date('Y-m-d H:i:s'), 'sitac_reviewed_by' => $sitac ? (int) $this->session->userdata('id_user') : null,
                'reviewed_at' => $direct ? date('Y-m-d H:i:s') : null,
            ]);
            $id = (int) $this->db->insert_id();
            $this->event($id, $direct ? 'DIRECT_EDIT' : 'SUBMITTED', $context['batch'], $reason);
            if ($direct) { $this->apply($context, $after); }
            $this->finish();
            return ['ok' => true, 'message' => $direct ? 'Perubahan SITAC HO disimpan dengan history.' : ($finance && $sitac ? 'Koreksi pasca bayar diajukan ke Finance. Data aktif tetap.' : 'Perubahan diajukan ke SITAC HO. Data aktif tetap.')];
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Batch edit submission failed: ' . get_class($e));
            return ['ok' => false, 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Gagal menyimpan perubahan batch.'];
        }
    }

    public function review($clusterId, $batchId, $requestId, $action, $note)
    {
        if (!$this->ready()) { return ['ok' => false, 'message' => 'Migrasi approval edit belum tersedia.']; }
        if (!in_array($action, ['APPROVE', 'REJECT'], true) || trim((string) $note) === '') { return ['ok' => false, 'message' => 'Aksi dan catatan review wajib diisi.']; }
        $this->db->trans_begin();
        try {
            $context = $this->locked($clusterId, $batchId);
            $request = $this->db->query('SELECT * FROM tb_myrep_batch_edit_request WHERE id_request = ? AND id_batch_approval = ? AND id_myrep_cluster = ? FOR UPDATE', [(int) $requestId, (int) $batchId, (int) $clusterId])->row_array();
            if (!$request || !in_array($request['status'], ['PENDING_SITAC', 'PENDING_FINANCE'], true)) { throw new RuntimeException('Pengajuan tidak lagi pending.'); }
            $finance = $request['status'] === 'PENDING_FINANCE';
            if (!$this->mapped($context['cluster'], $finance ? 'finance_ho' : 'sitac_ho')) { throw new RuntimeException('Review hanya oleh ' . ($finance ? 'Finance HO' : 'SITAC HO') . ' sesuai mapping wilayah.'); }
            $actor = (int) $this->session->userdata('id_user');
            if ($actor === (int) $request['submitted_by'] || ($finance && $actor === (int) $request['sitac_reviewed_by'])) { throw new RuntimeException('Review harus oleh petugas lain; pemohon / reviewer SITAC tidak dapat mereview tahap ini.'); }
            if ($action === 'REJECT') {
                $this->db->where('id_request', $requestId)->update('tb_myrep_batch_edit_request', ['status' => 'REJECTED', 'pending_batch_id' => null, 'reviewed_at' => date('Y-m-d H:i:s')]);
                $this->event($requestId, $finance ? 'FINANCE_REJECTED' : 'SITAC_REJECTED', $context['batch'], trim($note));
                $this->finish(); return ['ok' => true, 'message' => 'Pengajuan ditolak; data aktif tetap.'];
            }
            if (!hash_equals($request['baseline_hash'], $this->token($context))) {
                $this->db->where('id_request', $requestId)->update('tb_myrep_batch_edit_request', ['status' => 'STALE', 'pending_batch_id' => null, 'reviewed_at' => date('Y-m-d H:i:s')]);
                $this->event($requestId, 'STALE', $context['batch'], 'Data / staging berubah sejak pengajuan. ' . trim($note));
                $this->finish(); return ['ok' => false, 'message' => 'Data / staging berubah sejak pengajuan. Pengajuan ditutup STALE; ajukan ulang dari data terbaru.'];
            }
            $after = json_decode($request['after_json'], true);
            $next = !$finance && (int) $request['finance_required'] === 1 ? 'PENDING_FINANCE' : 'APPLIED';
            $this->db->where('id_request', $requestId)->update('tb_myrep_batch_edit_request', [
                'status' => $next, 'pending_batch_id' => $next === 'APPLIED' ? null : (int) $batchId,
                $finance ? 'finance_reviewed_by' : 'sitac_reviewed_by' => $actor,
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->event($requestId, $finance ? 'FINANCE_APPROVED' : 'SITAC_APPROVED', $context['batch'], trim($note));
            if ($next === 'APPLIED') { $this->apply($context, $after); }
            $this->finish();
            return ['ok' => true, 'message' => $next === 'APPLIED' ? 'Perubahan disetujui dan diterapkan dengan history.' : 'SITAC menyetujui; menunggu Finance. Data aktif tetap.'];
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Batch edit review failed: ' . get_class($e));
            return ['ok' => false, 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Gagal mereview perubahan batch.'];
        }
    }

    /** Dedicated correction, separate from ordinary data edit and Saku review. */
    public function correctWorkflow($clusterId, $batchId, $targetStage, $sakuAction, $reason, $evidence, $token, callable $validate)
    {
        $actor = (int) $this->session->userdata('id_user');
        $role = strtoupper(trim((string) $this->session->userdata('nama_level')));
        if ($actor <= 0 || $role !== 'SUPER ADMIN') { return ['ok' => false, 'message' => 'Koreksi workflow hanya untuk Super Admin yang login.']; }
        $reason = trim((string) $reason); $evidence = trim((string) $evidence);
        $action = strtoupper(trim((string) $sakuAction));
        if ($reason === '' || strlen($reason) > 4000) { return ['ok' => false, 'message' => 'Alasan koreksi wajib diisi, maksimal 4000 byte.']; }
        if (strlen($evidence) > 4000 || ($action !== 'KEEP' && $evidence === '')) { return ['ok' => false, 'message' => 'Referensi bukti wajib diisi untuk koreksi Saku, maksimal 4000 byte.']; }
        if (!$this->ready()) { return ['ok' => false, 'message' => 'Tabel history approval belum siap. Hubungi administrator.']; }
        if (!$this->db->trans_begin()) { return ['ok' => false, 'message' => 'Tidak dapat memulai transaksi koreksi workflow.']; }
        try {
            $context = $this->locked($clusterId, $batchId);
            if (!hash_equals($this->token($context), (string) $token)) { throw new RuntimeException('Data atau staging telah berubah. Muat ulang halaman sebelum melakukan koreksi.'); }
            if ($this->db->get_where('tb_myrep_batch_edit_request', ['pending_batch_id' => (int) $batchId])->row_array()) { throw new RuntimeException('Masih ada pengajuan perubahan pending. Selesaikan pengajuan tersebut sebelum koreksi workflow.'); }
            $now = date('Y-m-d H:i:s');
            $payload = Batch_workflow_policy::plan($context['batch'], $targetStage, $action, $actor, $now);
            $note = $reason . ($evidence !== '' ? "\nReferensi bukti: " . $evidence : '');
            if ($action === 'REQUEST') { $payload['saku_finance_request_remark'] = $note; }
            elseif (in_array($action, ['APPROVE', 'REJECT'], true)) { $payload['saku_finance_review_remark'] = $note; }
            $candidate = array_replace($context['batch'], $payload);
            $error = $validate($context, $candidate);
            if (!is_string($error)) { throw new RuntimeException('Validasi workflow tidak memberikan hasil yang valid.'); }
            if ($error !== '') { throw new RuntimeException($error); }
            $before = Batch_edit_policy::snapshot($context['batch'], $context['cluster'], $context['pics']);
            $before['workflow'] = Batch_workflow_policy::snapshot($context['batch']);
            $after = Batch_edit_policy::snapshot($candidate, $context['cluster'], $context['pics']);
            $after['workflow'] = Batch_workflow_policy::snapshot($candidate);
            $this->db->insert('tb_myrep_batch_edit_request', [
                'id_batch_approval' => (int) $batchId, 'id_myrep_cluster' => (int) $clusterId,
                'pending_batch_id' => null, 'status' => 'APPLIED', 'reason' => $note,
                'before_json' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                'baseline_hash' => $this->token($context), 'staging_at_submit' => Batch_edit_policy::effectiveStage($context['batch']),
                'finance_required' => 0, 'submitted_by' => $actor, 'submitted_at' => $now, 'reviewed_at' => $now,
            ]);
            $requestId = (int) $this->db->insert_id();
            if ($requestId <= 0) { throw new RuntimeException('Gagal membuat history koreksi workflow.'); }
            $payload['updated_by'] = $actor;
            // Preserve cluster status, recipients, PICs and actual payment evidence in full.
            $this->db->where('id_batch_approval', (int) $batchId)->update('tb_myrep_batch_approval', $payload);
            $this->event($requestId, 'WORKFLOW_CORRECTED', $candidate, $note);
            $this->finish();
            return ['ok' => true, 'message' => 'Koreksi workflow disimpan beserta alasan, referensi bukti dan history.'];
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Batch workflow correction failed: ' . get_class($e));
            return ['ok' => false, 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Gagal menyimpan koreksi workflow.'];
        }
    }

    public function history($batchId)
    {
        if (!$this->ready()) { return []; }
        $rows = $this->db->order_by('id_request', 'DESC')->get_where('tb_myrep_batch_edit_request', ['id_batch_approval' => (int) $batchId])->result_array();
        foreach ($rows as &$row) {
            $row['before'] = json_decode($row['before_json'], true);
            $row['after'] = json_decode($row['after_json'], true);
            $row['events'] = $this->db->order_by('id_event', 'ASC')->get_where('tb_myrep_batch_edit_event', ['id_request' => $row['id_request']])->result_array();
        }
        unset($row); return $rows;
    }
}
