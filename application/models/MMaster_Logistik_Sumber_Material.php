<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Sumber_Material extends CI_Model
{

    public function getMasterLogistikSumberMaterial()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_sumber_material`')->result_array();
        return $data;
    }
    public function getMasterLogistikSumberMaterialMasuk()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_sumber_material` WHERE status_sumber_material = "IN";')->result_array();
        return $data;
    }

    public function getMasterLogistikSumberMaterialKeluar()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_sumber_material` WHERE status_sumber_material = "OUT";')->result_array();
        return $data;
    }

    public function tambahSumberMaterialMasuk($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_sumber_material", $data_array);
        return $res;
    }

    public function tambahSumberMaterialKeluar($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_sumber_material", $data_array);
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
