<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogistik_Stok_Detail extends CI_Model
{
    public function getStokDetailArea()
    {
        $areaContext = $this->resolveAreaFilterContext();
        $filterArea = $areaContext['is_regional'] ? 'lg.regional_lokasi_gudang' : 'lg.kota_lokasi_gudang';
        $signedQtySql = "CASE 
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok
            ELSE 0
        END";

        $sql = "SELECT
    ROW_NUMBER() OVER (ORDER BY ki.kategori_item, ki.nama_item, ki.project_item) AS nomor,
    ki.kategori_item,
    ki.nama_item,
    ki.project_item,
    tmb.nama_bowheer,
    SUM($signedQtySql) AS jumlah_stok,
    ki.satuan_item,
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang
FROM tb_logistik_stok ls
JOIN tb_master_logistik_lokasi_gudang lg
    ON ls.id_lokasi_gudang = lg.id_lokasi_gudang
JOIN tb_master_logistik_kode_item ki
    ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN tb_master_bowheer tmb
    ON ki.id_bowheer_pemilik_item = tmb.id_bowheer
JOIN tb_master_logistik_sumber_material sm
    ON ls.id_sumber_material = sm.id_sumber_material
WHERE $filterArea = ?
GROUP BY
    ki.id_kode_item,
    ki.kategori_item,
    ki.nama_item,
    ki.project_item,
    tmb.nama_bowheer,
    ki.satuan_item,
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang
HAVING SUM($signedQtySql) <> 0
ORDER BY ki.kategori_item, ki.nama_item, ki.project_item";

        return $this->db->query($sql, [$areaContext['value']])->result_array();
    }

    public function getStokPerBowheer()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT 
    ROW_NUMBER() OVER () AS nomor,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
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
LEFT JOIN tb_master_bowheer tmb 
	ON ki.id_bowheer_pemilik_item = tmb.id_bowheer
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE ki.kategori_item = "' . $last_segment . '" && jumlah_stok != "0"
GROUP BY ki.kategori_item, ki.project_item
ORDER BY ki.kategori_item;')
            ->result_array();
        echo "<script>console.log('" . json_encode($data) . "');</script>";
        return $data;
    }

    public function getDistribusiPerBowheer()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT 
    ROW_NUMBER() OVER () AS nomor,
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
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
LEFT JOIN tb_master_bowheer tmb 
	ON ki.id_bowheer_pemilik_item = tmb.id_bowheer
LEFT JOIN tb_logistik_stok ls 
    ON lg.id_lokasi_gudang = ls.id_lokasi_gudang 
    AND ki.id_kode_item = ls.id_kode_item
LEFT JOIN tb_master_logistik_sumber_material sm 
    ON ls.id_sumber_material = sm.id_sumber_material
    WHERE ki.kategori_item = "' . $last_segment . '" && jumlah_stok != "0"
GROUP BY ki.nama_item, ki.project_item, lg.kota_lokasi_gudang
ORDER BY lg.regional_lokasi_gudang, ki.project_item;')
            ->result_array();
        echo "<script>console.log('" . json_encode($data) . "');</script>";
        return $data;
    }

    public function getHistoriInOUtLogistikArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "kota_lokasi_gudang";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "regional") !== false) {
            $filter_area = "regional_lokasi_gudang";
        } else {
            $filter_area = "kota_lokasi_gudang";
        }

        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user_new ON tb_logistik_stok.id_user = tb_master_user_new.id
                                        WHERE ' . $filter_area . ' = "' . $decoded_url_area . '" ORDER by tanggal_upload_stok DESC;')
            ->result_array();
        return $data;
    }

    public function getHistoriInOUtLogistikKategori()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user_new ON tb_logistik_stok.id_user = tb_master_user_new.id
                                        WHERE kategori_item = "' . $last_segment . '" ORDER by tanggal_upload_stok DESC;')
            ->result_array();
        return $data;
    }

    public function getSummaryDetailArea()
    {
        $areaContext = $this->resolveAreaFilterContext();
        $filterArea = $areaContext['is_regional'] ? 'lg.regional_lokasi_gudang' : 'lg.kota_lokasi_gudang';
        $signedQtySql = "CASE
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok
            ELSE 0
        END";

        $sql = "SELECT
    ki.kategori_item,
    MAX(ki.satuan_item) AS satuan_item,
    COALESCE(SUM(
        CASE
            WHEN $filterArea = ? THEN $signedQtySql
            ELSE 0
        END
    ), 0) AS total_jumlah_stok
FROM tb_master_logistik_kode_item ki
LEFT JOIN tb_logistik_stok ls
    ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN tb_master_logistik_lokasi_gudang lg
    ON ls.id_lokasi_gudang = lg.id_lokasi_gudang
LEFT JOIN tb_master_logistik_sumber_material sm
    ON ls.id_sumber_material = sm.id_sumber_material
GROUP BY ki.kategori_item
ORDER BY ki.kategori_item";

        return $this->db->query($sql, [$areaContext['value']])->result_array();
    }

    private function resolveAreaFilterContext()
    {
        $urlPath = $_SERVER['REQUEST_URI'] ?? '';
        $cleanPath = parse_url($urlPath, PHP_URL_PATH);
        $segments = explode('/', (string) $cleanPath);
        $lastSegment = urldecode((string) end($segments));

        return [
            'value' => $lastSegment,
            'is_regional' => stripos($lastSegment, 'regional') !== false,
        ];
    }
}


