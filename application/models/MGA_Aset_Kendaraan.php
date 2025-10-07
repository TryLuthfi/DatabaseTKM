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

    public function getMasterBowheer()
    {
        $data = $this->db->query('SELECT * FROM tb_master_bowheer;')
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
