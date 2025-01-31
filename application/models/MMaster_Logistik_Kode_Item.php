<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Kode_Item extends CI_Model
{

    public function getMasterLogistikKodeItem()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_kode_item`;')->result_array();
        return $data;
    }

}
