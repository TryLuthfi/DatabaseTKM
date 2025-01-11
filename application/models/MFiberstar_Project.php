<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MFiberstar_Project extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query('SELECT * FROM `tb_project_fiberstar` LEFT JOIN tb_project_fiberstar_progress ON tb_project_fiberstar.id_cluster_fiberstar = tb_project_fiberstar_progress.id_cluster_fiberstar_po LEFT JOIN tb_project_fiberstar_stagging ON tb_project_fiberstar_progress.stagging_pekerjaan_project_fiberstar_progress = tb_project_fiberstar_stagging.id_project_fiberstar_stagging LEFT JOIN tb_area ON tb_project_fiberstar.id_area = tb_area.id_area;')->result_array();
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
