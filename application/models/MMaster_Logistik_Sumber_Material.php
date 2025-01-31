<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Sumber_Material extends CI_Model
{

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
}
