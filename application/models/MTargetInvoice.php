<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTargetInvoice extends CI_Model
{

    public function getTargetAllBowheer()
    {
        $data = $this->db->query('SELECT
	tmb.id_bowheer,
    tmb.nama_bowheer,
    tmb.pic_user,
    SUM(tti.qty_target) AS total_target,
    SUM(tti.qty_achiev_target) AS total_achiev,
    (SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) AS deviasi,

    -- Persentase Achiev (berapa % dari target yang sudah tercapai)
    ROUND(
        CASE 
            WHEN SUM(tti.qty_target) = 0 THEN 0
            ELSE (SUM(tti.qty_achiev_target) / SUM(tti.qty_target)) * 100
        END, 2
    ) AS persen_achiev,

    -- Persentase Deviasi (berapa % sisa dari target yang belum tercapai)
    ROUND(
        CASE 
            WHEN SUM(tti.qty_target) = 0 THEN 0
            ELSE ((SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) / SUM(tti.qty_target)) * 100
        END, 2
    ) AS persen_deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY tti.id_bowheer')
            ->result_array();
        return $data;
    }

    public function getTargetAllCity()
    {
        $data = $this->db->query('SELECT
	tti.regional_target,
	tti.area_target,
	tmb.id_bowheer,
    tmb.nama_bowheer,
    tmb.pic_user,
    SUM(tti.qty_target) AS total_target,
    SUM(tti.qty_achiev_target) AS total_achiev,
    (SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) AS deviasi,

    ROUND(
        CASE 
            WHEN SUM(tti.qty_target) = 0 THEN 0
            ELSE (SUM(tti.qty_achiev_target) / SUM(tti.qty_target)) * 100
        END, 2
    ) AS persen_achiev,

    -- Persentase Deviasi (berapa % sisa dari target yang belum tercapai)
    ROUND(
        CASE 
            WHEN SUM(tti.qty_target) = 0 THEN 0
            ELSE ((SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) / SUM(tti.qty_target)) * 100
        END, 2
    ) AS persen_deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY tti.area_target
ORDER BY total_target DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetBowheerFilterCity()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments);
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('SELECT
	*,
	SUM(tti.qty_target) AS total_target,
    SUM(tti.qty_achiev_target) AS total_achiev,
    (SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) AS deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.area_target = "' . $decoded_url_area . '"
    GROUP by tti.id_bowheer
    ORDER BY total_target DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetCityFilterBowheer()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments);
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('SELECT
	*,
	SUM(tti.qty_target) AS total_target,
    SUM(tti.qty_achiev_target) AS total_achiev,
    (SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) AS deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.id_bowheer = "' . $decoded_url_area . '"
    GROUP BY tti.area_target
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }
}

