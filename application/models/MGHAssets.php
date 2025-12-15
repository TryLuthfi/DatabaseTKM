<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGHAssets extends CI_Model
{
    private $table = "asset_types";

    public function getAllAssets()
    {

        $data = $this->db->query('SELECT * FROM asset_types')->result_array();
        return $data;
    }

    public function tambahAsset($data_array)
    {
        $res = $this->db->insert("asset_types", $data_array);
        return $res;
    }

    public function editAsset($data_array, $id)
    {
        $res = $this->db->update("asset_types", $data_array, $id);
        return $res;
    }

    public function hapusAsset($id)
    {
        $res = $this->db->delete("asset_types", $id);
        return $res;
    }
}
