<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMaster_Logistik_Lokasi_Gudang extends CI_Model
{

    public function getMasterLogistikLokasiGudang()
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang` LEFT JOIN tb_master_user ON tb_master_logistik_lokasi_gudang.id_user = tb_master_user.id_user;')->result_array();
        return $data;
    }

    public function getMasterUser()
    {
        $data = $this->db->query('SELECT * FROM tb_master_user ORDER BY nama_user ASC;')->result_array();
        return $data;
    }

    public function tambahLokasiGudang($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_lokasi_gudang", $data_array);
        return $res;
    }

    public function hapusLokasiGudang($id_lokasi_gudang)
    {
        $res = $this->db->delete("tb_master_logistik_lokasi_gudang", $id_lokasi_gudang);
        return $res;
    }

    public function editLokasiGudang($data_array, $id_lokasi_gudang)
    {
        $res = $this->db->update("tb_master_logistik_lokasi_gudang", $data_array, $id_lokasi_gudang);
        return $res;
    }

}
