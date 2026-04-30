<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MStockOpname extends CI_Model
{
    private $statusDone = ['DONE', 'ADJUSTED', 'CLOSED'];

    public function getSOPeriode()
    {
        $data = $this->db->query('SELECT 
    sp.id_sop, 
    sp.sop_bulan, 
    sp.sop_tahun, 
    sp.sop_status,
    CONCAT(
        COALESCE(COUNT(CASE WHEN sk.id_so_kota IS NOT NULL THEN 1 END), 0), 
        " / ", 
        (SELECT COUNT(*) FROM tb_master_logistik_lokasi_gudang)
    ) AS persentasi_so_kota
FROM tb_so_periode sp
LEFT JOIN tb_so_kota sk ON sp.id_sop = sk.id_so_periode
GROUP BY sp.id_sop, sp.sop_bulan, sp.sop_tahun, sp.sop_status;
')->result_array();
        return $data;
    }

    public function getDataSoKota($id_so_kota)
    {
        return $this->db->get_where("tb_so_kota", ['id_so_kota' => $id_so_kota])->row_array();
    }

    public function getSoKotaByPeriodeLokasi($idSop, $idLokasiGudang)
    {
        return $this->db
            ->get_where('tb_so_kota', [
                'id_so_periode' => (int) $idSop,
                'id_kota' => (int) $idLokasiGudang,
            ])
            ->row_array();
    }

    public function getDetailSoPeriode($id_sop)
    {
        $data = $this->db->query('SELECT * FROM tb_so_periode WHERE id_sop = "' . $id_sop . '"')->result_array();
        return $data;
    }

    public function getSOKota($id_sop)
    {
        return $this->db
            ->select('tmllg.*, tsk.*, tsp.*')
            ->from('tb_master_logistik_lokasi_gudang tmllg')
            ->join(
                'tb_so_kota tsk',
                'tmllg.id_lokasi_gudang = tsk.id_kota AND tsk.id_so_periode = ' . (int) $id_sop,
                'left'
            )
            ->join(
                'tb_so_periode tsp',
                'tsp.id_sop = ' . (int) $id_sop,
                'left'
            )
            ->order_by('tmllg.regional_lokasi_gudang', 'ASC')
            ->order_by('tmllg.kota_lokasi_gudang', 'ASC')
            ->get()
            ->result_array();
    }

    public function getSOItem($id_lokasi_gudang, $tanggal_format)
    {

        $data = $this->db->query('SELECT 
    ROW_NUMBER() OVER (ORDER BY tmllg.kota_lokasi_gudang ASC) AS nomor,
    tls.*,
    tmllg.*,
    tmlki.*,
    tmb.*,
    SUM(
        CASE 
            WHEN tmlsm.status_sumber_material LIKE "IN" THEN tls.jumlah_stok
            WHEN tmlsm.status_sumber_material LIKE "OUT" THEN -tls.jumlah_stok
            ELSE 0 
        END
    ) AS total_jumlah_stok
FROM tb_logistik_stok tls 
LEFT JOIN tb_master_logistik_sumber_material tmlsm USING(id_sumber_material)
LEFT JOIN tb_master_logistik_kode_item tmlki USING(id_kode_item)
RIGHT JOIN tb_master_bowheer tmb USING(id_bowheer)
RIGHT JOIN tb_master_logistik_lokasi_gudang tmllg USING(id_lokasi_gudang)
WHERE tls.id_lokasi_gudang = "' . $id_lokasi_gudang . '" && tanggal_upload_stok <= "' . $tanggal_format . '"
GROUP BY tmlki.id_kode_item, tmllg.kota_lokasi_gudang
HAVING total_jumlah_stok <> 0
ORDER BY tmllg.kota_lokasi_gudang ASC')->result_array();

        log_message('error', 'cek get so item: ' . $this->db->last_query());
        return $data;
    }

    public function getDetailSoItem($id_sop, $id_lokasi_gudang)
    {
        $data = $this->db->query('SELECT * FROM tb_so_item tsi
LEFT JOIN tb_so_periode tsp ON tsi.id_sop = tsp.id_sop
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg ON tsi.id_kota_gudang = tmllg.id_lokasi_gudang
LEFT JOIN tb_master_logistik_kode_item tmlki ON tsi.id_kode_item = tmlki.id_kode_item
WHERE tsi.id_sop = "' . $id_sop . '" AND tsi.id_kota_gudang = "' . $id_lokasi_gudang . '"')->result_array();

        return $data;
    }

    public function insertBatchSOItem($data)
    {
        return $this->db->insert_batch('tb_so_item', $data);
    }

    public function tambahSoKota($data_array)
    {
        $res = $this->db->insert("tb_so_kota", $data_array);
        return $res;
    }

    public function upsertSoKota($idSop, $idLokasiGudang, $payload)
    {
        $existing = $this->getSoKotaByPeriodeLokasi($idSop, $idLokasiGudang);

        if ($existing) {
            $this->db
                ->where('id_so_kota', (int) $existing['id_so_kota'])
                ->update('tb_so_kota', $payload);

            return (int) $existing['id_so_kota'];
        }

        $payload['id_so_periode'] = (int) $idSop;
        $payload['id_kota'] = (int) $idLokasiGudang;
        $this->db->insert('tb_so_kota', $payload);
        return (int) $this->db->insert_id();
    }

    public function updateSoKotaById($idSoKota, $payload)
    {
        return $this->db->where('id_so_kota', (int) $idSoKota)->update('tb_so_kota', $payload);
    }

    public function updateSOItem($id_sop, $id_kode_item, $data)
    {
        $this->db->where('id_sop', $id_sop);
        $this->db->where('id_kode_item', $id_kode_item);
        return $this->db->update('tb_so_item', $data);
    }


    public function tambahPeriode($data_array)
    {
        $res = $this->db->insert("tb_so_periode", $data_array);
        return $res;
    }

    public function hapusPeriode($id_sop)
    {
        $res = $this->db->delete("tb_so_periode", $id_sop);
        return $res;
    }

    public function hapusKotaById($id_so_kota)
    {
        return $this->db->delete("tb_so_kota", ['id_so_kota' => $id_so_kota]);
    }

    public function hapusItemSO($id_sop, $id_lokasi_gudang)
    {
        $this->db->where('id_sop', $id_sop);
        $this->db->where('id_kota_gudang', $id_lokasi_gudang);
        return $this->db->delete("tb_so_item");
    }

    public function hapusApprovalLogBySoKota($idSoKota)
    {
        return $this->db->delete('tb_so_approval_log', ['id_so_kota' => (int) $idSoKota]);
    }

    public function hapusBABySoKota($idSoKota)
    {
        $baRows = $this->db
            ->select('id_so_ba')
            ->from('tb_so_ba')
            ->where('id_so_kota', (int) $idSoKota)
            ->get()
            ->result_array();

        foreach ($baRows as $baRow) {
            $this->db->delete('tb_so_ba_item', ['id_so_ba' => (int) $baRow['id_so_ba']]);
        }

        return $this->db->delete('tb_so_ba', ['id_so_kota' => (int) $idSoKota]);
    }

    public function syncSOItemDiscrepancy($idSop, $idLokasiGudang)
    {
        $items = $this->db
            ->get_where('tb_so_item', [
                'id_sop' => (int) $idSop,
                'id_kota_gudang' => (int) $idLokasiGudang,
            ])
            ->result_array();

        $hasDiscrepancy = false;
        foreach ($items as $item) {
            $stokAsli = (int) ($item['soi_stok_asli'] ?? 0);
            $stokOpname = (int) ($item['soi_stok_opname'] ?? 0);
            $selisih = $stokAsli - $stokOpname;
            $isSelisih = $selisih !== 0 ? 1 : 0;
            $hasDiscrepancy = $hasDiscrepancy || (bool) $isSelisih;
            $remarks = trim((string) (($item['soi_remarks'] ?? '') !== '' ? $item['soi_remarks'] : ($item['soi_keterangan'] ?? '')));

            $this->db
                ->where('id_so_item', (int) $item['id_so_item'])
                ->update('tb_so_item', [
                    'soi_selisih' => $selisih,
                    'soi_is_selisih' => $isSelisih,
                    'soi_remarks' => $remarks,
                    'soi_remarks_required' => $isSelisih,
                    'soi_needs_adjustment' => $isSelisih,
                    'soi_adjustment_status' => $isSelisih ? 'PENDING' : 'SYNC',
                ]);
        }

        return $hasDiscrepancy;
    }

    public function getSOItemDiscrepancies($idSop, $idLokasiGudang)
    {
        return $this->db
            ->select('tsi.*, tmlki.project_item, tmlki.kategori_item, tmlki.nama_item, tmlki.satuan_item')
            ->from('tb_so_item tsi')
            ->join('tb_master_logistik_kode_item tmlki', 'tmlki.id_kode_item = tsi.id_kode_item', 'left')
            ->where('tsi.id_sop', (int) $idSop)
            ->where('tsi.id_kota_gudang', (int) $idLokasiGudang)
            ->where('COALESCE(tsi.soi_is_selisih, 0) = 1', null, false)
            ->order_by('tmlki.project_item', 'ASC')
            ->order_by('tmlki.nama_item', 'ASC')
            ->get()
            ->result_array();
    }

    public function getSOBABySoKota($idSoKota)
    {
        return $this->db
            ->select('ba.*, u_submit.nama_user AS submitted_name, u_approve.nama_user AS approved_name')
            ->from('tb_so_ba ba')
            ->join('tb_master_user u_submit', 'u_submit.id_user = ba.ba_submitted_by', 'left')
            ->join('tb_master_user u_approve', 'u_approve.id_user = ba.ba_approved_by', 'left')
            ->where('ba.id_so_kota', (int) $idSoKota)
            ->order_by('ba.id_so_ba', 'DESC')
            ->get()
            ->row_array();
    }

    public function getSOBAItems($idSoBa)
    {
        return $this->db
            ->select('bai.*, tmlki.project_item, tmlki.kategori_item, tmlki.nama_item, tmlki.satuan_item')
            ->from('tb_so_ba_item bai')
            ->join('tb_master_logistik_kode_item tmlki', 'tmlki.id_kode_item = bai.id_kode_item', 'left')
            ->where('bai.id_so_ba', (int) $idSoBa)
            ->order_by('tmlki.project_item', 'ASC')
            ->order_by('tmlki.nama_item', 'ASC')
            ->get()
            ->result_array();
    }

    public function saveBADraft($payload)
    {
        $existing = $this->getSOBABySoKota((int) $payload['id_so_kota']);

        if ($existing) {
            $this->db->where('id_so_ba', (int) $existing['id_so_ba'])->update('tb_so_ba', $payload);
            return (int) $existing['id_so_ba'];
        }

        $this->db->insert('tb_so_ba', $payload);
        return (int) $this->db->insert_id();
    }

    public function replaceBAItems($idSoBa, $items)
    {
        $this->db->where('id_so_ba', (int) $idSoBa)->delete('tb_so_ba_item');
        if (!empty($items)) {
            $this->db->insert_batch('tb_so_ba_item', $items);
        }
    }

    public function addSOApprovalLog($payload)
    {
        return $this->db->insert('tb_so_approval_log', $payload);
    }

    public function getSOApprovalLogs($idSoKota)
    {
        return $this->db
            ->select('l.*, u.nama_user')
            ->from('tb_so_approval_log l')
            ->join('tb_master_user u', 'u.id_user = l.action_by', 'left')
            ->where('l.id_so_kota', (int) $idSoKota)
            ->order_by('l.action_at', 'DESC')
            ->order_by('l.id_so_approval_log', 'DESC')
            ->get()
            ->result_array();
    }

    public function getMasterSumberMaterialByName($name)
    {
        return $this->db
            ->get_where('tb_master_logistik_sumber_material', ['nama_sumber_material' => $name])
            ->row_array();
    }

    public function getPreferredBowheerId($idLokasiGudang, $idKodeItem)
    {
        $latest = $this->db
            ->select('id_bowheer')
            ->from('tb_logistik_stok')
            ->where('id_lokasi_gudang', (int) $idLokasiGudang)
            ->where('id_kode_item', (int) $idKodeItem)
            ->order_by('tanggal_upload_stok', 'DESC')
            ->order_by('id_logistik_stok', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($latest['id_bowheer'])) {
            return (int) $latest['id_bowheer'];
        }

        $fallback = $this->db->select('id_bowheer')->from('tb_master_bowheer')->order_by('id_bowheer', 'ASC')->limit(1)->get()->row_array();
        return (int) ($fallback['id_bowheer'] ?? 0);
    }

    public function getKodeItemById($idKodeItem)
    {
        return $this->db
            ->get_where('tb_master_logistik_kode_item', ['id_kode_item' => (int) $idKodeItem])
            ->row_array();
    }

    public function getLokasiGudangById($idLokasiGudang)
    {
        return $this->db
            ->get_where('tb_master_logistik_lokasi_gudang', ['id_lokasi_gudang' => (int) $idLokasiGudang])
            ->row_array();
    }

    public function getPeriodeById($idSop)
    {
        return $this->db->get_where('tb_so_periode', ['id_sop' => (int) $idSop])->row_array();
    }

    public function updatePeriodeStatusFromItems($idSop)
    {
        $rows = $this->db
            ->select('sok_status')
            ->from('tb_so_kota')
            ->where('id_so_periode', (int) $idSop)
            ->get()
            ->result_array();

        $status = 'NOT YET';
        if (!empty($rows)) {
            $normalized = array_map(static function ($row) {
                return strtoupper(trim((string) ($row['sok_status'] ?? 'NOT YET')));
            }, $rows);

            $allDone = !empty($normalized) && count(array_diff($normalized, ['DONE', 'ADJUSTED', 'CLOSED'])) === 0;
            $hasProgress = count(array_filter($normalized, static function ($value) {
                return $value !== 'NOT YET' && $value !== '';
            })) > 0;

            if ($allDone) {
                $status = 'DONE';
            } elseif ($hasProgress) {
                $status = 'IN PROGRESS';
            }
        }

        $this->db->where('id_sop', (int) $idSop)->update('tb_so_periode', ['sop_status' => $status]);
        return $status;
    }

}
