<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MFiberstar_PO extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query('SELECT * FROM `tb_project_fiberstar` join tb_project_fiberstar_progress ON tb_project_fiberstar.id_project_fiberstar = tb_project_fiberstar_progress.id_project_fiberstar join tb_project_fiberstar_stagging ON tb_project_fiberstar_progress.stagging_pekerjaan_project_fiberstar_progress = tb_project_fiberstar_stagging.id_project_fiberstar_stagging JOIN tb_area ON tb_project_fiberstar.id_area = tb_area.id_area;')->result_array();
        return $data;
    }

    public function addPO($data_array)
    {
        $res = $this->db->insert("tb_kode", $data_array);
        return $res;
    }

    public function deleteData($id)
    {
        $res = $this->db->delete("tb_kode", $id);
        return $res;
    }

    public function updateData($data_array, $id)
    {
        $res = $this->db->update("tb_kode", $data_array, $id);
        return $res;
    }
}
