<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MGA_Aset_Kantor extends CI_Model
{

    public function getMasterAsetOffice()
    {
        $data = $this->db->query('select * from tb_aset_office join tb_kode_aset on tb_aset_office.ka_id_kode_aset = tb_kode_aset.ka_id_kode_aset')
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

    public function getCountAsetOfficeByKota()
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
        $res = $this->db->insert("tb_GA_Aset_Kantor", $data_array);
        return $res;
    }

    public function hapusKodeItem($id_kode_item)
    {
        $res = $this->db->delete("tb_GA_Aset_Kantor", $id_kode_item);
        return $res;
    }

    public function editKodeItem($data_array, $id_kode_item)
    {
        $res = $this->db->update("tb_GA_Aset_Kantor", $data_array, $id_kode_item);
        return $res;
    }

}
