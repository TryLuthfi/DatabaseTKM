<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Pabrik extends CI_Model
{

    public function getMasterLogistikPabrik()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_pabrik`')->result_array();
        return $data;
    }

    public function tambahPabrik($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_pabrik", $data_array);
        return $res;
    }

    public function hapusSumberMaterial($id_sumber_material)
    {
        $res = $this->db->delete("tb_master_logistik_sumber_material", $id_sumber_material);
        return $res;
    }

    public function editSumberMaterial($data_array, $id_sumber_material)
    {
        $res = $this->db->update("tb_master_logistik_sumber_material", $data_array, $id_sumber_material);
        return $res;
    }
}
