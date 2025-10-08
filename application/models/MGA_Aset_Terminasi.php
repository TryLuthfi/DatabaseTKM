<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGA_Aset_Terminasi extends CI_Model
{

    public function getMasterAsetTerminasi()
    {
        $data = $this->db->query('select * from tb_aset_kendaraan join tb_kode_aset on tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset')
            ->result_array();
        return $data;
    }

    public function getMasterAsetKendaraanFilter()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "ak_area";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "regional") !== false) {
            $filter_area = "ak_regional";
        } else {
            $filter_area = "ak_area";
        }

        $data = $this->db->query('select * from tb_aset_kendaraan join tb_kode_aset on tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiLimit()
    {
        $data = $this->db->query('SELECT 
    tka.ka_jenis_aset,
    COUNT(*) AS total_data
FROM 
    tb_aset_terminasi tat
JOIN 
    tb_kode_aset tka 
    ON tat.ka_id_kode_aset = tka.ka_id_kode_aset
WHERE 
    tka.ka_jenis_aset IN ("SPLICER", "OTDR", "OPM", "OFI")
GROUP BY 
    tat.ka_id_kode_aset, tka.ka_jenis_aset
ORDER BY 
    tka.ka_jenis_aset DESC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiAll()
    {
        $data = $this->db->query('SELECT 
    tka.ka_jenis_aset,
    tka.ka_sorting,
    COUNT(*) AS total_data
FROM 
    tb_aset_terminasi tat
JOIN 
    tb_kode_aset tka 
    ON tat.ka_id_kode_aset = tka.ka_id_kode_aset
GROUP BY 
    tat.ka_id_kode_aset
ORDER BY 
    tat.ka_id_kode_aset ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiByRegionTipe1()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SPLICER" THEN 1 ELSE 0 END) AS splicer,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OTDR" THEN 1 ELSE 0 END) AS otdr,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "GPS" THEN 1 ELSE 0 END) AS gps,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CAMERA 360" THEN 1 ELSE 0 END) AS camera_360,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "UTG" THEN 1 ELSE 0 END) AS utg,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TANGGA TELESKOPIK" THEN 1 ELSE 0 END) AS tangga_teleskopik,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OLS" THEN 1 ELSE 0 END) AS ols,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OPM" THEN 1 ELSE 0 END) AS opm
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_terminasi.at_status_aset = "AKTIF"
GROUP BY tb_aset_terminasi.at_regional
ORDER BY tb_aset_terminasi.at_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiByRegionTipe2()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OFI" THEN 1 ELSE 0 END) AS ofi,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "LABEL IT" THEN 1 ELSE 0 END) AS label_it,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "POWER INVERTER" THEN 1 ELSE 0 END) AS power_inverter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "ROLL METER" THEN 1 ELSE 0 END) AS roll_meter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TOOLKITS" THEN 1 ELSE 0 END) AS toolkits,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CLEAVER" THEN 1 ELSE 0 END) AS cleaver,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "STRIPPER" THEN 1 ELSE 0 END) AS stripper,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SLITTER" THEN 1 ELSE 0 END) AS slitter
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_terminasi.at_status_aset = "AKTIF"
GROUP BY tb_aset_terminasi.at_regional
ORDER BY tb_aset_terminasi.at_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiByRegionTipe3()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SENTER OPTIK" THEN 1 ELSE 0 END) AS senter_optik,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SABUK SAFETY" THEN 1 ELSE 0 END) AS sabuk_safety,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "IMPACT DRILL" THEN 1 ELSE 0 END) AS impact_drill,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "ELEKTRODA" THEN 1 ELSE 0 END) AS elektroda,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TESTER GROUNDING" THEN 1 ELSE 0 END) AS tester_grounding,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MESIN KERJA" THEN 1 ELSE 0 END) AS mesin_kerja
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_terminasi.at_status_aset = "AKTIF"
GROUP BY tb_aset_terminasi.at_regional
ORDER BY tb_aset_terminasi.at_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetTerminasiByKota()
    {
        $data = $this->db->query('SELECT 
    tb_aset_kendaraan.ak_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOBIL" THEN 1 ELSE 0 END) AS mobil,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MOTOR" THEN 1 ELSE 0 END) AS motor,
    COUNT(tb_aset_kendaraan.ka_id_kode_aset) AS total_aset
FROM tb_aset_kendaraan
JOIN tb_kode_aset 
    ON tb_aset_kendaraan.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_kendaraan.ak_status_aset = "AKTIF"
GROUP BY tb_aset_kendaraan.ak_area
ORDER BY tb_aset_kendaraan.ak_area ASC;')
            ->result_array();
        return $data;
    }

    public function getMasterKepemilikan()
    {
        $data = $this->db->query('SELECT * FROM tb_master_bowheer
                                    WHERE nama_bowheer IN ("PT. IFORTE","PT. TKM","PT. EKA MAS REPUBLIK")
                                    ORDER BY id_bowheer DESC;')
            ->result_array();
        return $data;
    }

    public function tambahKodeItem($data_array)
    {
        $res = $this->db->insert("tb_GA_Aset_Terminasi", $data_array);
        return $res;
    }

    public function hapusKodeItem($id_kode_item)
    {
        $res = $this->db->delete("tb_GA_Aset_Terminasi", $id_kode_item);
        return $res;
    }

    public function editKodeItem($data_array, $id_kode_item)
    {
        $res = $this->db->update("tb_GA_Aset_Terminasi", $data_array, $id_kode_item);
        return $res;
    }

}
