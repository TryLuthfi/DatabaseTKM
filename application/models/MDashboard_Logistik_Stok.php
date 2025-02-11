<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MDashboard_Logistik_Stok extends CI_Model
{

    public function getAllStokLogistik()
    {
        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user')
                                        ->result_array();
        return $data;
    }
    public function getUniqueKotaGudang()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_lokasi_gudang, tb_master_logistik_lokasi_gudang.kota_lokasi_gudang 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
                                        GROUP BY tb_master_logistik_lokasi_gudang.kota_lokasi_gudang;')
                                        ->result_array();
        return $data;
    }

    public function getUniqueProjectLogistik()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_bowheer, tb_master_bowheer.nama_bowheer 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        GROUP BY tb_master_bowheer.nama_bowheer;')
                                        ->result_array();
        return $data;
    }
    public function getUniqueItemLogistik()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_kode_item, tb_master_logistik_kode_item.nama_item 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        GROUP BY tb_master_logistik_kode_item.nama_item;')
                                        ->result_array();
        return $data;
    }
    public function getUniqueSumberMaterial()
    {
        $data = $this->db->query('SELECT tb_logistik_stok.id_sumber_material, tb_master_logistik_sumber_material.nama_sumber_material 
                                        FROM tb_logistik_stok 
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        GROUP BY tb_master_logistik_sumber_material.nama_sumber_material;')
                                        ->result_array();
        return $data;
    }

    public function getListGudangLokasiUser(): mixed
    {
        $id_user = $this->session->userdata('id_user');
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang` WHERE id_user = "'.$id_user.'"')->result_array();
        return $data;
    }
    public function getMasterProject(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_bowheer`')->result_array();
        return $data;
    }
    public function getMasterSumberMaterial(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_sumber_material`')->result_array();
        return $data;
    }
    public function getMasterKodeItem(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_kode_item`')->result_array();
        return $data;
    }

    public function tambahReportStokLogistik($data_array)
    {
        $res = $this->db->insert("tb_logistik_stok", $data_array);
        return $res;
    }

    public function hapusReportStokLogistik($id_logistik_stok)
    {
        $res = $this->db->delete("tb_logistik_stok", $id_logistik_stok);
        return $res;
    }
}

