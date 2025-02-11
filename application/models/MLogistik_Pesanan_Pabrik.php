<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Pesanan_Pabrik extends CI_Model
{

    public function getMasterLogistikPesananPabrik()
    {
        $data = $this->db->query('SELECT * FROM `tb_logistik_pesanan_pabrik`')->result_array();
        return $data;
    }

    public function getOustandingPesananPabrik()
    {
        $data = $this->db->query('SELECT *,
	                                    COALESCE(SUM(tb_logistik_pengiriman_pabrik.qty_material_pengiriman), 0) AS total_qty_material_pengiriman ,
                                        (tb_logistik_pesanan_pabrik.qty_material_pesanan - COALESCE(SUM(tb_logistik_pengiriman_pabrik.qty_material_pengiriman), 0)) AS sisa_qty_material_pengiriman 
	                                    FROM tb_logistik_pesanan_pabrik 
                                        LEFT JOIN tb_master_logistik_pabrik ON tb_logistik_pesanan_pabrik.id_pabrik = tb_master_logistik_pabrik.id_pabrik
                                        LEFT JOIN tb_master_logistik_kode_item ON tb_logistik_pesanan_pabrik.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        LEFT JOIN tb_master_user ON tb_logistik_pesanan_pabrik.id_user = tb_master_user.id_user
                                        LEFT JOIN tb_logistik_pengiriman_pabrik ON tb_logistik_pesanan_pabrik.nomor_po_pabrik = tb_logistik_pengiriman_pabrik.nomor_po_pabrik 
                                        GROUP BY tb_logistik_pesanan_pabrik.nomor_po_pabrik, tb_logistik_pesanan_pabrik.qty_material_pesanan;')
            ->result_array();
        return $data;
    }

    public function tambahPabrik($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_pabrik", $data_array);
        return $res;
    }

    public function hapusPabrik($id_pabrik)
    {
        $res = $this->db->delete("tb_master_logistik_pabrik", $id_pabrik);
        return $res;
    }

    public function editPabrik($data_array, $id_pabrik)
    {
        $res = $this->db->update("tb_master_logistik_pabrik", $data_array, $id_pabrik);
        return $res;
    }
}
