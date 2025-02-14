<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Stok_Detail extends CI_Model
{
    public function getStokDetailArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT 
    ROW_NUMBER() OVER () AS nomor, -- Menambahkan nomor urut
    lg.kota_lokasi_gudang, 
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), "-") AS jumlah_stok
FROM tb_master_logistik_lokasi_gudang lg
CROSS JOIN tb_master_logistik_kode_item ki -- Menghasilkan semua kombinasi kota & kategori
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE lg.kota_lokasi_gudang = "'.$last_segment.'"
GROUP BY lg.kota_lokasi_gudang, ki.kategori_item, ki.project_item
ORDER BY lg.kota_lokasi_gudang, ki.kategori_item;')
            ->result_array();
            echo "<script>console.log('" . json_encode($data) . "');</script>";
        return $data;
    }

}
