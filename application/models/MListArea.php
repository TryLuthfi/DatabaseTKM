<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListArea extends CI_Model
{

    public function getData()
    {
        $this->db->from('tb_area');
        $data = $this->db->get();
        return $data->result_array();
    }

    public function addArea($data_array)
    {
        $res = $this->db->insert("tb_area", $data_array);
        return $res;
    }

    public function deleteArea($id)
    {
        $res = $this->db->delete("tb_area", $id);
        return $res;
    }

    public function updateArea($data_array, $id)
    {
        $res = $this->db->update("tb_area", $data_array, $id);
        return $res;
    }
}
