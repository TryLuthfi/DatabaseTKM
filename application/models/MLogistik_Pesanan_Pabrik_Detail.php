<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Pesanan_Pabrik_Detail extends CI_Model
{

    public function getDetailPesananPabrik()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT pesan.nomor_po_pabrik, pesan.qty_material_pesanan, NULL AS qty_material_pengiriman, pesan.tanggal_po_pabrik, NULL AS tanggal_pengiriman_pabrik, NULL AS kota_lokasi_gudang
FROM tb_logistik_pesanan_pabrik pesan
WHERE pesan.nomor_po_pabrik = "' . $last_segment . '"

UNION ALL

SELECT kirim.nomor_po_pabrik, NULL AS qty_material_pesanan, kirim.qty_material_pengiriman, NULL AS tanggal_po_pabrik, kirim.tanggal_pengiriman_pabrik, tmlg.kota_lokasi_gudang 
FROM tb_logistik_pengiriman_pabrik kirim
LEFT JOIN tb_master_logistik_lokasi_gudang tmlg ON kirim.id_lokasi_gudang = tmlg.id_lokasi_gudang
WHERE kirim.nomor_po_pabrik = "' . $last_segment . '"')
            ->result_array();
        return $data;
    }

    public function getMasterBowheer()
    {
        $data = $this->db->query('SELECT * FROM tb_master_bowheer;')
            ->result_array();
        return $data;
    }

    public function getMasterKepemilikan()
    {
        $data = $this->db->query('SELECT * FROM tb_master_bowheer
                                    WHERE nama_bowheer IN ("PT IFORTE","PT TKM","PT EKA MAS REPUBLIK")
                                    ORDER BY id_bowheer DESC;')
            ->result_array();
        return $data;
    }

    public function tambahKodeItem($data_array)
    {
        $res = $this->db->insert("tb_master_logistik_kode_item", $data_array);
        return $res;
    }

    public function hapusKodeItem($id_kode_item)
    {
        $res = $this->db->delete("tb_master_logistik_kode_item", $id_kode_item);
        return $res;
    }

    public function editKodeItem($data_array, $id_kode_item)
    {
        $res = $this->db->update("tb_master_logistik_kode_item", $data_array, $id_kode_item);
        return $res;
    }

}
