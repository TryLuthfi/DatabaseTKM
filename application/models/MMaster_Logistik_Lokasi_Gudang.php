<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Lokasi_Gudang extends CI_Model
{

    public function getMasterLogistikLokasiGudang()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang`;')->result_array();
        return $data;
    }

}
