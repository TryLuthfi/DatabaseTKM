<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MGA_Alat_Terminasi extends CI_Model
{

    public function getMasterAsetOffice()
    {
        $data = $this->db->query('SELECT * FROM tb_aset_office JOIN tb_kode_aset ON tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset')
            ->result_array();
        return $data;
    }

    public function getFilteredAlatTerminasi($regional = null, $area = null, $status = null, $kondisi = null, $grouped = false)
    {
        if ($grouped) {
            // === GROUP BY versi (saat filter diklik) ===
            $this->db->select('tka.ka_jenis_aset, COUNT(*) AS total, GROUP_CONCAT(tat.at_id_list_terminasi) AS list_id');
        } else {
            // === Non-GROUP BY versi (tampilan awal) ===
            $this->db->select('tat.*, tka.ka_jenis_aset');
        }

        $this->db->from('tb_aset_terminasi tat');
        $this->db->join('tb_kode_aset tka', 'tat.ka_id_kode_aset = tka.ka_id_kode_aset', 'left');

        // ===== Filter dinamis =====
        if (!empty($regional))
            $this->db->where_in('tat.at_regional', (array) $regional);
        if (!empty($area))
            $this->db->where_in('tat.at_area', (array) $area);
        if (!empty($tahun))
            $this->db->where_in('tat.at_tahun_perolehan', (array) $tahun);
        if (!empty($status))
            $this->db->where_in('tat.at_status_aset', (array) $status);
        if (!empty($kondisi))
            $this->db->where_in('tat.at_kondisi_aset', (array) $kondisi);

        // ===== Jika grouped aktif, tambahkan GROUP BY =====
        if ($grouped) {
            $this->db->group_by('tka.ka_jenis_aset');
            $this->db->order_by('tka.ka_jenis_aset', 'ASC');
        } else {
            $this->db->order_by('tat.at_id_list_terminasi', 'ASC');
        }

        

        $data =  $this->db->get()->result_array();
        log_message('error', 'query satu : ' . $this->db->last_query());
        return $data;
    }


    public function getMasterAsetOfficeArea()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "ka_jenis_aset";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "regional") !== false) {
            $filter_area = "ao_regional";
        } else {
            $filter_area = "ao_area";
        }

        $data = $this->db->query('SELECT * FROM tb_aset_terminasi tat  JOIN tb_kode_aset tka ON tat.ka_id_kode_aset = tka.ka_id_kode_aset WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"')
            ->result_array();
        return $data;
    }

    public function getMasterAsetOfficeTipe()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "tka.ka_jenis_aset";
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('SELECT * FROM tb_aset_terminasi tat  JOIN tb_kode_aset tka ON tat.ka_id_kode_aset = tka.ka_id_kode_aset WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"')
            ->result_array();
        return $data;
    }

    public function getCountAlatTerminasiAll()
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
    WHERE tka.ka_tipe_kode_aset = "ALAT TERMINASI" 
GROUP BY 
    tat.ka_id_kode_aset
ORDER BY 
    CAST(tka.ka_sorting AS UNSIGNED) ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetOfficeByKota()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "ka_jenis_aset";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "regional") !== false) {
            $filter_area = "ao_regional";
        } else {
            $filter_area = "ao_area";
        }

        $data = $this->db->query('SELECT 
    tka.ka_jenis_aset,
    tka.ka_sorting,
    COUNT(*) AS total_data
FROM 
    tb_aset_office tao
JOIN 
    tb_kode_aset tka 
    ON tao.ka_id_kode_aset = tka.ka_id_kode_aset
    WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"
GROUP BY 
    tao.ka_id_kode_aset
ORDER BY 
    tao.ka_id_kode_aset ASC')
            ->result_array();
        return $data;
    }

    public function getCountAlatTerminasiByRegionTipe1()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,tb_aset_terminasi.at_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SPLICER" THEN 1 ELSE 0 END) AS splicer,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OTDR" THEN 1 ELSE 0 END) AS otdr,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TANGGA TELESKOPIK" THEN 1 ELSE 0 END) AS tangga_teleskopik,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OLS" THEN 1 ELSE 0 END) AS ols,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OPM" THEN 1 ELSE 0 END) AS opm,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OFI" THEN 1 ELSE 0 END) AS ofi
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
GROUP BY tb_aset_terminasi.at_regional
ORDER BY tb_aset_terminasi.at_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAlatTerminasiByRegionTipe2()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,tb_aset_terminasi.at_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "LABEL IT" THEN 1 ELSE 0 END) AS label_it,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TOOLKITS" THEN 1 ELSE 0 END) AS toolkits,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CLEAVER" THEN 1 ELSE 0 END) AS cleaver,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "STRIPPER" THEN 1 ELSE 0 END) AS stripper,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SLITTER" THEN 1 ELSE 0 END) AS slitter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SENTER OPTIK" THEN 1 ELSE 0 END) AS senter_optik
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
GROUP BY tb_aset_terminasi.at_regional
ORDER BY tb_aset_terminasi.at_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAlatTerminasiByCityTipe1()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,tb_aset_terminasi.at_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SPLICER" THEN 1 ELSE 0 END) AS splicer,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OTDR" THEN 1 ELSE 0 END) AS otdr,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TANGGA TELESKOPIK" THEN 1 ELSE 0 END) AS tangga_teleskopik,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OLS" THEN 1 ELSE 0 END) AS ols,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OPM" THEN 1 ELSE 0 END) AS opm,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "OFI" THEN 1 ELSE 0 END) AS ofi
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
GROUP BY tb_aset_terminasi.at_area
ORDER BY tb_aset_terminasi.at_area ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAlatTerminasiByCityTipe2()
    {
        $data = $this->db->query('SELECT 
    tb_aset_terminasi.at_regional,tb_aset_terminasi.at_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "LABEL IT" THEN 1 ELSE 0 END) AS label_it,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "TOOLKITS" THEN 1 ELSE 0 END) AS toolkits,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CLEAVER" THEN 1 ELSE 0 END) AS cleaver,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "STRIPPER" THEN 1 ELSE 0 END) AS stripper,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SLITTER" THEN 1 ELSE 0 END) AS slitter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SENTER OPTIK" THEN 1 ELSE 0 END) AS senter_optik
FROM tb_aset_terminasi
JOIN tb_kode_aset 
    ON tb_aset_terminasi.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
GROUP BY tb_aset_terminasi.at_area
ORDER BY tb_aset_terminasi.at_area ASC;')
            ->result_array();
        return $data;
    }


    public function tambahAsetKantor($data_array)
    {
        $res = $this->db->insert("tb_aset_office", $data_array);
        return $res;
    }

    public function hapusAsetKantor($ao_id_list_office)
    {
        $res = $this->db->delete("tb_aset_office", $ao_id_list_office);
        return $res;
    }

    public function editAsetKantor($hasil_data, $ao_id_list_office)
    {
        $res = $this->db->update("tb_aset_office", $hasil_data, $ao_id_list_office);
        return $res;
    }

    public function getReportStokAsetkantor()
    {
        $data = $this->db->query("SELECT
	*
FROM
	tb_aset_office
JOIN tb_kode_aset ON
	tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
	ORDER BY tb_aset_office.ka_id_kode_aset, tb_aset_office.ao_regional, tb_aset_office.ao_area;")->result_array();

        return $data;
    }

}
