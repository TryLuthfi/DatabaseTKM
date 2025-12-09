<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHTenants extends CI_Model
{
    public function getAll()
    {
        return $this->db->order_by('id', 'ASC')->get('tenants')->result_array();
    }

    public function getById($id)
    {
        return $this->db->get_where('tenants', ['id' => $id])->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert('tenants', $data);
    }

    public function updateData($id, $data)
    {
        return $this->db->where('id', $id)->update('tenants', $data);
    }

    public function deleteData($id)
    {
        return $this->db->delete('tenants', ['id' => $id]);
    }
}
