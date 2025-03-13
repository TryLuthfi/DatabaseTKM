<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MStockOpname extends CI_Model
{


    public function getSOPeriode()
    {
        $data = $this->db->query('SELECT 
    sp.id_sop, 
    sp.sop_bulan, 
    sp.sop_tahun, 
    sp.sop_status,
    CONCAT(
        COALESCE(COUNT(sk.id_so_kota), 0), 
        " / ", 
        (SELECT COUNT(*) FROM tb_master_logistik_lokasi_gudang)
    ) AS persentasi_so_kota
FROM tb_so_periode sp
LEFT JOIN tb_so_kota sk ON sp.id_sop = sk.id_so_periode AND sk.sok_status = "DONE"
GROUP BY sp.id_sop, sp.sop_bulan, sp.sop_tahun, sp.sop_status;
')->result_array();
        return $data;
    }

    public function getDataSoKota($id_so_kota)
    {
        return $this->db->get_where("tb_so_kota", ['id_so_kota' => $id_so_kota])->row_array();
    }

    public function getDetailSoPeriode($id_sop)
    {
        $data = $this->db->query('SELECT * FROM tb_so_periode WHERE id_sop = "' . $id_sop . '"')->result_array();
        return $data;
    }

    public function getSOKota($id_sop)
    {

        $data = $this->db->query('SELECT
    tmllg.*,
    tsk.*,
    tsp.*
FROM
    tb_master_logistik_lokasi_gudang tmllg
LEFT JOIN tb_so_kota tsk 
    ON tmllg.id_lokasi_gudang = tsk.id_kota 
    AND (tsk.id_so_periode = "'.$id_sop.'" OR tsk.id_so_periode IS NULL)
LEFT JOIN tb_so_periode tsp 
    ON tsk.id_so_periode = tsp.id_sop
GROUP BY 
    tmllg.id_lokasi_gudang')->result_array();
        return $data;
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

}