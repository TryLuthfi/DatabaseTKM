<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MDashboard_Logistik_Stok extends CI_Model
{

    public function getAllStokLogistik()
    {
        $data = $this->db->query('SELECT *, SUM(tb_logistik_stok.jumlah_stok) AS total_jumlah_stok FROM tb_logistik_stok 
JOIN tb_master_logistik_lokasi_gudang 
    ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
JOIN tb_master_bowheer 
    ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
JOIN tb_master_logistik_sumber_material 
    ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
JOIN tb_master_logistik_kode_item 
    ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
JOIN tb_master_user 
    ON tb_logistik_stok.id_user = tb_master_user.id_user
WHERE no_surat_jalan != ""
GROUP BY no_surat_jalan, kota_lokasi_gudang
ORDER BY 
    CASE 
        WHEN no_surat_jalan LIKE "%stock_opname%" THEN 1  -- Letakkan di bawah
        ELSE 0 
    END,
    id_logistik_stok DESC;')
            ->result_array();
        return $data;
    }

    public function getAllStokByKategory()
    {
        $data = $this->db->query('SELECT *, SUM(CASE WHEN tmlsm.status_sumber_material LIKE "IN" THEN tls.jumlah_stok
					WHEN tmlsm.status_sumber_material LIKE "OUT" THEN -tls.jumlah_stok
					ELSE 0 END) AS total_jumlah_stok
	FROM tb_logistik_stok tls 
	LEFT JOIN tb_master_logistik_sumber_material tmlsm USING(id_sumber_material)
	RIGHT JOIN tb_master_logistik_kode_item tmlki USING(id_kode_item)
	GROUP BY tmlki.kategori_item')
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

    public function getAllStokByKategoryFilterCity()
    {
        $data = $this->db->query('SELECT 
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Aksesories" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Closure"
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "FAT" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "FDT" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "HDPE" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Kabel" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "OTB" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Tiang" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
GROUP BY 
    lg.kota_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang, lg.kota_lokasi_gudang;')
            ->result_array();
        return $data;
    }

    public function getAllStokByKategoryFilterRegional()
    {
        $data = $this->db->query('SELECT 
    lg.regional_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Aksesories" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Closure"
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "FAT" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "FDT" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "HDPE" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Kabel" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "OTB" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = "Tiang" 
                    THEN CASE WHEN sm.status_sumber_material = "IN" THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = "OUT" THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
GROUP BY 
    lg.regional_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang;')
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
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang` WHERE id_user = "' . $id_user . '"')->result_array();
        return $data;
    }

    public function getListGudangLokasiUserAll(): mixed
    {
        $data = $this->db->query('SELECT * FROM `tb_master_logistik_lokasi_gudang`')->result_array();
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

    public function getDetailAreaBySJ($no_surat_jalan)
    {
        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user
                                        WHERE surat_jalan = "' . $no_surat_jalan . '"
                                        ORDER BY id_logistik_stok DESC
                                        ')->result_array();
        return $data;
    }

    public function getAllStokByKategoryFilterCityFiltered($tanggal)
    {
        $sql = "SELECT 
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Aksesories' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Closure'
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'FAT' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'FDT' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'HDPE' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Kabel' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'OTB' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
    COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Tiang' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
FROM 
    tb_master_logistik_lokasi_gudang lg
LEFT JOIN 
    tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
LEFT JOIN 
    tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
LEFT JOIN 
    tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
            WHERE 1=1"; // Awal WHERE agar bisa ditambahkan kondisi

        // Tambahkan filter lokasi
        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        // Tambahkan GROUP BY & ORDER BY
        $sql .= " GROUP BY 
    lg.kota_lokasi_gudang
ORDER BY 
    lg.regional_lokasi_gudang, lg.kota_lokasi_gudang;";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query filter city dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getDashboardFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal)
    {
        $sql = "SELECT 
                lg.regional_lokasi_gudang,
                lg.kota_lokasi_gudang,
                ki.nama_item,
    ki.project_item,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Aksesories' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Aksesories,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Closure'
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Closure,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'FAT' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FAT,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'FDT' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_FDT,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'HDPE' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_HDPE,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Kabel' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Kabel,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'OTB' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_OTB,
                COALESCE(SUM(CASE 
                    WHEN ki.kategori_item = 'Tiang' 
                    THEN CASE WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
                              WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
                              ELSE 0 
                         END 
                 END), 0) AS jumlah_Tiang
            FROM 
                tb_master_logistik_lokasi_gudang lg
            LEFT JOIN 
                tb_logistik_stok ls ON lg.id_lokasi_gudang = ls.id_lokasi_gudang
            LEFT JOIN 
                tb_master_logistik_kode_item ki ON ls.id_kode_item = ki.id_kode_item
            LEFT JOIN 
                tb_master_logistik_sumber_material sm ON ls.id_sumber_material = sm.id_sumber_material
            WHERE 1=1"; // Awal WHERE agar bisa ditambahkan kondisi

        // Tambahkan filter lokasi
        if (!empty($lokasiArray)) {
            $sql .= " AND lg.kota_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        // Tambahkan GROUP BY & ORDER BY
        $sql .= " GROUP BY lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal)
    {

        $sql = "SELECT 
    ROW_NUMBER() OVER () AS nomor,
    lg.regional_lokasi_gudang,
    lg.kota_lokasi_gudang,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), '-') AS jumlah_stok,
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
    WHERE 1=1 && jumlah_stok != '0'";

        if (!empty($lokasiArray)) {
            $sql .= " AND lg.kota_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " GROUP BY ki.nama_item, ki.project_item, lg.kota_lokasi_gudang
ORDER BY lg.regional_lokasi_gudang, lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFilteredBowheer($lokasiArray, $bowheerArray, $itemArray, $tanggal)
    {

        $sql = "SELECT 
    ROW_NUMBER() OVER () AS nomor,
    ki.kategori_item, 
    ki.nama_item, 
    ki.project_item,
    tmb.nama_bowheer,
    COALESCE(SUM(
        CASE 
            WHEN sm.status_sumber_material = 'IN' THEN ls.jumlah_stok 
            WHEN sm.status_sumber_material = 'OUT' THEN -ls.jumlah_stok 
            ELSE 0 
        END
    ), '-') AS jumlah_stok,
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
    WHERE 1=1 && jumlah_stok != '0'";

        if (!empty($lokasiArray)) {
            $sql .= " AND lg.kota_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND ki.project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND ki.nama_item IN ($itemArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND ls.tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " GROUP BY ki.kategori_item, ki.project_item
ORDER BY ki.kategori_item;";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian2 dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getInOutHistoryFiltered($lokasiArray, $bowheerArray, $itemArray, $tanggal)
    {

        $sql = "SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user WHERE 1=1";

        if (!empty($lokasiArray)) {
            $sql .= " AND kota_lokasi_gudang IN ($lokasiArray)";
        }

        if (!empty($bowheerArray)) {
            $sql .= " AND project_item IN ($bowheerArray)";
        }

        if (!empty($itemArray)) {
            $sql .= " AND nama_item IN ($itemArray)";
        }

        if (!empty($tanggal)) {
            $sql .= " AND tanggal_upload_stok <= '$tanggal 23:59:59'";
        }

        $sql .= " ORDER by tanggal_upload_stok DESC";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian2 dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getReportInOutMaterial()
    {
        $data = $this->db->query("SELECT 
    ROW_NUMBER() OVER (ORDER BY tls.id_logistik_stok DESC) AS nomor_urut,
    tls.id_logistik_stok,
    tmllg.regional_lokasi_gudang,
    tmllg.kota_lokasi_gudang,
    tls.no_surat_jalan,
    tls.jumlah_stok,
    tmlki.kategori_item,
    tmlki.nama_item,
    tmlki.satuan_item,
    tmb.nama_bowheer,
    tls.merk_stok,
    tls.no_haspel_stok,
    tls.no_ref_stok,
    tls.keterangan_stok,
    tmlsm.nama_sumber_material,
    tmlsm.status_sumber_material,
    tls.tanggal_upload_stok,
    tmu.nama_user
FROM tb_logistik_stok tls
LEFT JOIN tb_master_logistik_lokasi_gudang tmllg ON tls.id_lokasi_gudang = tmllg.id_lokasi_gudang
LEFT JOIN tb_master_bowheer tmb ON tls.id_bowheer = tmb.id_bowheer
LEFT JOIN tb_master_logistik_sumber_material tmlsm ON tls.id_sumber_material = tmlsm.id_sumber_material
LEFT JOIN tb_master_logistik_kode_item tmlki ON tls.id_kode_item = tmlki.id_kode_item
LEFT JOIN tb_master_user tmu ON tls.id_user = tmu.id_user
ORDER BY tls.id_logistik_stok DESC;")->result_array();

        return $data;
    }

    public function getReportStokMaterial()
    {
        $data = $this->db->query("SELECT 
    ROW_NUMBER() OVER (ORDER BY tmllg.kota_lokasi_gudang ASC) AS nomor,
    tls.id_logistik_stok,
    tmllg.regional_lokasi_gudang,
    tmllg.kota_lokasi_gudang,
    tmlki.kategori_item,
    tmlki.nama_item,
    tmlki.satuan_item,
    tmb.nama_bowheer,
    SUM(
        CASE 
            WHEN tmlsm.status_sumber_material LIKE 'IN' THEN tls.jumlah_stok
            WHEN tmlsm.status_sumber_material LIKE 'OUT' THEN -tls.jumlah_stok
            ELSE 0 
        END
    ) AS total_jumlah_stok
FROM tb_logistik_stok tls 
LEFT JOIN tb_master_logistik_sumber_material tmlsm USING(id_sumber_material)
LEFT JOIN tb_master_logistik_kode_item tmlki USING(id_kode_item)
RIGHT JOIN tb_master_bowheer tmb USING(id_bowheer)
RIGHT JOIN tb_master_logistik_lokasi_gudang tmllg USING(id_lokasi_gudang)
GROUP BY tmlki.id_kode_item, tmllg.kota_lokasi_gudang
HAVING total_jumlah_stok <> 0
ORDER BY tmllg.kota_lokasi_gudang ASC;")->result_array();

        return $data;
    }

}

