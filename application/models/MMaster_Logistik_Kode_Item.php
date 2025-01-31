<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Kode_Item extends CI_Model
{

    public function getMasterLogistikKodeItem()
    {
        $data = $this->db->query('SELECT * FROM tb_master_logistik_kode_item
                                    JOIN tb_master_bowheer 
                                    ON tb_master_logistik_kode_item.id_bowheer_pemilik_item = tb_master_bowheer.id_bowheer;')
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
                                    WHERE nama_bowheer IN ("PT IFORTE","PT TKM","PT EKA MAS REPUBLIK")
                                    ORDER BY id_bowheer DESC;')
                                    ->result_array();
        return $data;
    }

    public function tambahKodeItem($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_kode_item", $data_array);
        return $res;
    }

    public function hapusKodeItem($id_kode_item)
    {
        $res = $this->db->delete("tb_master_logistik_kode_item", $id_kode_item);
        return $res;
    }

    public function editKodeItem($data_array, $id_kode_item)
    {
        $res = $this->db->update("tb_master_logistik_kode_item", $data_array, $id_kode_item);
        return $res;
    }

}
