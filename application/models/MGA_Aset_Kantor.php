<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGA_Aset_Kantor extends CI_Model
{

    public function getMasterAsetOffice()
    {
        $data = $this->db->query('SELECT * FROM tb_aset_office JOIN tb_kode_aset ON tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset')
            ->result_array();
        return $data;
    }

    public function getFilteredAsetKantor($regional = null, $area = null, $tahun = null, $status = null, $kondisi = null, $grouped = false)
    {
        if ($grouped) {
            // === GROUP BY versi (saat filter diklik) ===
            $this->db->select('tka.ka_jenis_aset, COUNT(*) AS total, GROUP_CONCAT(tao.ao_id_list_office) AS list_id');
        } else {
            // === Non-GROUP BY versi (tampilan awal) ===
            $this->db->select('tao.*, tka.ka_jenis_aset');
        }

        $this->db->from('tb_aset_office tao');
        $this->db->join('tb_kode_aset tka', 'tao.ka_id_kode_aset = tka.ka_id_kode_aset', 'left');

        // ===== Filter dinamis =====
        if (!empty($regional))
            $this->db->where_in('tao.ao_regional', (array) $regional);
        if (!empty($area))
            $this->db->where_in('tao.ao_area', (array) $area);
        if (!empty($tahun))
            $this->db->where_in('tao.ao_tahun_perolehan', (array) $tahun);
        if (!empty($status))
            $this->db->where_in('tao.ao_status_aset', (array) $status);
        if (!empty($kondisi))
            $this->db->where_in('tao.ao_kondisi_aset', (array) $kondisi);

        // ===== Jika grouped aktif, tambahkan GROUP BY =====
        if ($grouped) {
            $this->db->group_by('tka.ka_jenis_aset');
            $this->db->order_by('tka.ka_jenis_aset', 'ASC');
        } else {
            $this->db->order_by('tao.ao_id_list_office', 'ASC');
        }

        return $this->db->get()->result_array();
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

        $data = $this->db->query('SELECT * FROM tb_aset_office JOIN tb_kode_aset ON tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"')
            ->result_array();
        return $data;
    }

    public function getMasterAsetOfficeTipe()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "ka_jenis_aset";
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('select * from tb_aset_office join tb_kode_aset on tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset WHERE ' . $filter_area . ' = "' . $decoded_url_area . '"')
            ->result_array();
        return $data;
    }

    public function getCountAsetOfficeAll()
    {
        $data = $this->db->query('SELECT 
    tka.ka_jenis_aset,
    tka.ka_sorting,
    COUNT(*) AS total_data
FROM 
    tb_aset_office tao
JOIN 
    tb_kode_aset tka 
    ON tao.ka_id_kode_aset = tka.ka_id_kode_aset
GROUP BY 
    tao.ka_id_kode_aset
ORDER BY 
    tao.ka_id_kode_aset ASC;')
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

    public function getCountAsetOfficeByRegionTipe()
    {
        $data = $this->db->query('SELECT 
    tb_aset_office.ao_regional,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "LAPTOP" THEN 1 ELSE 0 END) AS laptop,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "PRINTER" THEN 1 ELSE 0 END) AS printer,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SCANNER" THEN 1 ELSE 0 END) AS scanner,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MARKOM" THEN 1 ELSE 0 END) AS markom,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "DRAFTER" THEN 1 ELSE 0 END) AS drafter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "HARDISK" THEN 1 ELSE 0 END) AS hardisk,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "HANDPHONE" THEN 1 ELSE 0 END) AS handphone,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CUTTING PLOTTER" THEN 1 ELSE 0 END) AS cutting_plotter
FROM tb_aset_office
JOIN tb_kode_aset 
    ON tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_office.ao_status_aset = "AKTIF"
GROUP BY tb_aset_office.ao_regional
ORDER BY tb_aset_office.ao_regional ASC;')
            ->result_array();
        return $data;
    }

    public function getCountAsetOfficeByCityTipe()
    {
        $data = $this->db->query('SELECT 
    tb_aset_office.ao_area,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "LAPTOP" THEN 1 ELSE 0 END) AS laptop,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "PRINTER" THEN 1 ELSE 0 END) AS printer,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "SCANNER" THEN 1 ELSE 0 END) AS scanner,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "MARKOM" THEN 1 ELSE 0 END) AS markom,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "DRAFTER" THEN 1 ELSE 0 END) AS drafter,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "HARDISK" THEN 1 ELSE 0 END) AS hardisk,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "HANDPHONE" THEN 1 ELSE 0 END) AS handphone,
    SUM(CASE WHEN tb_kode_aset.ka_jenis_aset = "CUTTING PLOTTER" THEN 1 ELSE 0 END) AS cutting_plotter
FROM tb_aset_office
JOIN tb_kode_aset 
    ON tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset
    WHERE tb_aset_office.ao_status_aset = "AKTIF"
GROUP BY tb_aset_office.ao_area
ORDER BY tb_aset_office.ao_area ASC;')
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
