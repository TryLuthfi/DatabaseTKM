<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGA_Aset_Kendaraan extends CI_Model
{

    public function getMasterAsetKendaraan()
    {
        $data = $this->db->query('select * from tb_aset_kendaraan join tb_kode_aset on tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset')
            ->result_array();
        return $data;
    }

    public function getCountAsetKendaraan()
    {
        $data = $this->db->query('SELECT 
    tb_kode_aset.ka_id_kode_aset,
    tb_kode_aset.ka_nama_kode_aset,
    tb_kode_aset.ka_jenis_aset,
    SUM(CASE WHEN tb_aset_kendaraan.ak_kondisi_aset = "BAIK" THEN 1 ELSE 0 END) AS jml_baik,
    SUM(CASE WHEN tb_aset_kendaraan.ak_kondisi_aset = "RUSAK" THEN 1 ELSE 0 END) AS jml_rusak,
    SUM(CASE WHEN tb_aset_kendaraan.ak_status_aset = "AKTIF" THEN 1 ELSE 0 END) AS jml_aktif,
    SUM(CASE WHEN tb_aset_kendaraan.ak_status_aset = "TERJUAL" THEN 1 ELSE 0 END) AS jml_terjual,
    SUM(CASE WHEN tb_aset_kendaraan.ak_status_aset = "HILANG" THEN 1 ELSE 0 END) AS jml_hilang,
    COUNT(tb_aset_kendaraan.ka_id_kode_aset) AS total_aset_kendaraan
FROM tb_aset_kendaraan
JOIN tb_kode_aset 
    ON tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
GROUP BY 
    tb_kode_aset.ka_id_kode_aset,
    tb_kode_aset.ka_nama_kode_aset,
    tb_kode_aset.ka_jenis_aset
ORDER BY tb_kode_aset.ka_id_kode_aset ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetKendaraanByRegion()
    {
        $data = $this->db->query('SELECT 
    tb_aset_kendaraan.ak_regional,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOBIL" THEN 1 ELSE 0 END) AS mobil,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOTOR" THEN 1 ELSE 0 END) AS motor,
    COUNT(tb_aset_kendaraan.ka_id_kode_aset) AS total_aset
FROM tb_aset_kendaraan
JOIN tb_kode_aset 
    ON tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_kendaraan.ak_status_aset = "AKTIF"
GROUP BY tb_aset_kendaraan.ak_regional
ORDER BY tb_aset_kendaraan.ak_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetKendaraanByKota()
    {
        $data = $this->db->query('SELECT 
    tb_aset_kendaraan.ak_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOBIL" THEN 1 ELSE 0 END) AS mobil,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOTOR" THEN 1 ELSE 0 END) AS motor,
    COUNT(tb_aset_kendaraan.ka_id_kode_aset) AS total_aset
FROM tb_aset_kendaraan
JOIN tb_kode_aset 
    ON tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_kendaraan.ak_status_aset = "AKTIF"
GROUP BY tb_aset_kendaraan.ak_area
ORDER BY tb_aset_kendaraan.ak_area ASC;')
            ->result_array();
        return $data;
    }

    public function getMasterKepemilikan()
    {
        $data = $this->db->query('SELECT * FROM tb_master_bowheer
                                    WHERE nama_bowheer IN ("PT. IFORTE","PT. TKM","PT. EKA MAS REPUBLIK")
                                    ORDER BY id_bowheer DESC;')
            ->result_array();
        return $data;
    }

    public function tambahKodeItem($data_array)
    {
        $res = $this->db->insert("tb_GA_Aset_Kendaraan", $data_array);
        return $res;
    }

    public function hapusKodeItem($id_kode_item)
    {
        $res = $this->db->delete("tb_GA_Aset_Kendaraan", $id_kode_item);
        return $res;
    }

    public function editKodeItem($data_array, $id_kode_item)
    {
        $res = $this->db->update("tb_GA_Aset_Kendaraan", $data_array, $id_kode_item);
        return $res;
    }

}
