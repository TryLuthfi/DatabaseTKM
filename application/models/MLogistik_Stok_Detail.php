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
    ROW_NUMBER() OVER () AS nomor,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), "-") AS jumlah_stok,
    ki.satuan_item
FROM tb_master_logistik_lokasi_gudang lg
CROSS JOIN tb_master_logistik_kode_item ki
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE lg.kota_lokasi_gudang = "' . $last_segment . '"
GROUP BY lg.kota_lokasi_gudang, ki.kategori_item, ki.project_item
ORDER BY lg.kota_lokasi_gudang, ki.kategori_item;')
            ->result_array();
        echo "<script>console.log('" . json_encode($data) . "');</script>";
        return $data;
    }

    public function getSummaryDetailArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT 
    tmlki.*, 
    tmllg.kota_lokasi_gudang, 
    COALESCE(SUM(
        CASE 
            WHEN tmlsm.status_sumber_material = "IN" THEN tls.jumlah_stok
            WHEN tmlsm.status_sumber_material = "OUT" THEN -tls.jumlah_stok
            ELSE 0 
        END
    ), 0) AS total_jumlah_stok
FROM tb_master_logistik_kode_item tmlki
LEFT JOIN tb_logistik_stok tls ON tmlki.id_kode_item = tls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material tmlsm ON tls.id_sumber_material = tmlsm.id_sumber_material
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg ON tls.id_lokasi_gudang = tmllg.id_lokasi_gudang
WHERE (tmllg.kota_lokasi_gudang = "' . $last_segment . '" OR tmllg.kota_lokasi_gudang IS NULL)
GROUP BY tmlki.kategori_item
ORDER BY tmlki.kategori_item;')
            ->result_array();
        return $data;
    }

}
