<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Kode_Item extends CI_Model
{

    public function getMasterLogistikKodeItem()
    {
        $data = $this->db->query('SELECT
                                    tmlki.*,
                                    tmb.nama_bowheer,
                                    COALESCE((
                                        SELECT GROUP_CONCAT(b.nama_bowheer ORDER BY b.nama_bowheer SEPARATOR ", ")
                                        FROM tb_master_bowheer b
                                        WHERE FIND_IN_SET(CAST(b.id_bowheer AS CHAR), REPLACE(COALESCE(tmlki.project_item, ""), " ", "")) > 0
                                    ), "") AS project_item_names
                                  FROM tb_master_logistik_kode_item tmlki
                                  LEFT JOIN tb_master_bowheer tmb
                                    ON tmlki.id_bowheer_pemilik_item = tmb.id_bowheer;')
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
