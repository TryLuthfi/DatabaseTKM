<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHTenants extends CI_Model
{
    public function getAllTenants()
    {
        $data = $this->db->query('SELECT * FROM tenants')->result_array();
        return $data;
    }

    public function tambahTenant($data_array)
    {
        $res = $this->db->insert("tenants", $data_array);
        return $res;
    }

    public function editTenant($data_array, $id)
    {
        $res = $this->db->update("tenants", $data_array, $id);
        return $res;
    }


    public function hapusTenant($id)
    {
        $res = $this->db->delete("tenants", $id);
        return $res;
    }
}
