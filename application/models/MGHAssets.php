<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHAssets extends CI_Model
{
    private $table = "asset_types";

    public function getAll()
    {
        return $this->db->order_by("id","ASC")->get($this->table)->result_array();
    }

    public function getById($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function updateData($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function deleteData($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
