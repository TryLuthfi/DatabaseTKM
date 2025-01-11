<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MListBowheer extends CI_Model
{

    public function getData()
    {
        $this->db->from('tb_bowheer');
        $this->db->join('tb_user','tb_bowheer.id_user = tb_user.id_user', 'left');
        $this->db->order_by("target_bowheer", "desc");
        $data = $this->db->get();
        return $data->result_array();
    }

    public function addBowheer($data_array)
    {
        $res = $this->db->insert("tb_bowheer", $data_array);
        return $res;
    }

    public function deleteBowheer($id)
    {
        $res = $this->db->delete("tb_bowheer", $id);
        return $res;
    }

    public function updateBowheer($data_array, $id)
    {
        $res = $this->db->update("tb_bowheer", $data_array, $id);
        return $res;
    }
}
