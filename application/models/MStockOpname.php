<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MStockOpname extends CI_Model
{


    public function getSOPeriode()
    {
        $data = $this->db->query('SELECT * FROM tb_so_periode')->result_array();
        return $data;
    }


    public function tambahPeriode($data_array){
        $res = $this->db->insert("tb_so_periode", $data_array);
        return $res;
    }

    public function hapusPeriode($id_sop)
    {
        $res = $this->db->delete("tb_so_periode", $id_sop);
        return $res;
    }
}