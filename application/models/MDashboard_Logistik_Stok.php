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
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user
                                        WHERE no_surat_jalan != ""
                                        GROUP BY no_surat_jalan
                                        ORDER BY id_logistik_stok DESC')
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

    public function hapusReportStokLogistik($no_surat_jalan)
    {
        $res = $this->db->delete("tb_logistik_stok", $no_surat_jalan);
        return $res;
    }

    public function getDetailAreaBySJ($no_surat_jalan)
    {
        $data = $this->db->query('SELECT * FROM `tb_logistik_stok` JOIN tb_master_logistik_lokasi_gudang ON tb_logistik_stok.id_lokasi_gudang = tb_master_logistik_lokasi_gudang.id_lokasi_gudang
	                                    JOIN tb_master_bowheer ON tb_logistik_stok.id_bowheer = tb_master_bowheer.id_bowheer
                                        JOIN tb_master_logistik_sumber_material ON tb_logistik_stok.id_sumber_material = tb_master_logistik_sumber_material.id_sumber_material
                                        JOIN tb_master_logistik_kode_item ON tb_logistik_stok.id_kode_item = tb_master_logistik_kode_item.id_kode_item
                                        JOIN tb_master_user ON tb_logistik_stok.id_user = tb_master_user.id_user
                                        WHERE no_surat_jalan = "' . $no_surat_jalan . '"
                                        ORDER BY id_logistik_stok DESC
                                        ')->result_array();
        return $data;
    }

    public function getDashboardFiltered($lokasiArray, $bowheerArray, $itemArray)
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

        // Tambahkan GROUP BY & ORDER BY
        $sql .= " GROUP BY lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFiltered($lokasiArray, $bowheerArray, $itemArray)
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

        $sql .= " GROUP BY ki.nama_item, ki.project_item, lg.kota_lokasi_gudang
ORDER BY lg.regional_lokasi_gudang, lg.kota_lokasi_gudang";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

    public function getRincianDashboardFileteredBowheer($lokasiArray, $bowheerArray, $itemArray)
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

        $sql .= " GROUP BY ki.kategori_item, ki.project_item
ORDER BY ki.kategori_item;";

        $data = $this->db->query($sql)->result_array();

        log_message('error', 'query rincian2 dashboard logistik filter yang dijalankan : ' . $this->db->last_query());

        // Jalankan query
        return $data;

    }

}

