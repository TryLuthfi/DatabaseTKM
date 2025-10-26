<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MTargetInvoice extends CI_Model
{

    public function getAllData()
    {
        $data = $this->db->query('SELECT * FROM tb_target_invoice tti JOIN tb_master_bowheer_invoice tmbi ON tti.id_bowheer = tmbi.id_bowheer')
            ->result_array();
        return $data;
    }

    public function getTargetAllPIC()
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY tmb.pic_user
ORDER BY total_target DESC;')
            ->result_array();
        return $data;
    }

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
JOIN tb_master_bowheer_invoice  tmb
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY tti.area_target
ORDER BY total_target DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetAllRegional()
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY tti.regional_target
ORDER BY tti.regional_target ASC;')
            ->result_array();
        return $data;
    }

    public function getTargetBowheerFilterCity()
    {

        $data = $this->db->query('SELECT
	*,
	SUM(tti.qty_target) AS total_target,
    SUM(tti.qty_achiev_target) AS total_achiev,
    (SUM(tti.qty_target) - SUM(tti.qty_achiev_target)) AS deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    GROUP by tti.area_target
    ORDER BY total_target DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetCityFilterBowheer()
    {

        $data = $this->db->query('SELECT
	*,
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    GROUP BY tmb.pic_user
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getDetailTargetCityFilterBowheer()
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.id_bowheer = "'.$decoded_url_area.'"
    GROUP BY tti.area_target
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getDetailTargetBowheerFilterCity()
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.area_target = "'.$decoded_url_area.'"
    GROUP BY tti.id_bowheer
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getTargetCityFilterBowheerDetail()
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
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.id_bowheer = "'.$decoded_url_area.'"
    GROUP BY tti.area_target
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getTargetRincianFilterBowheer()
    {

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`
FROM
    tb_target_invoice tti
    JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    id_bowheer;')
            ->result_array();
        return $data;
    }

    public function getTargetRincianFilterCity()
    {

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    tti.area_target
    ORDER BY `GRAND TOTAL TARGET` DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetRincianFilterPIC()
    {

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tmb.nama_bowheer,
    tmb.pic_user,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    tmb.pic_user
    ORDER BY `GRAND TOTAL TARGET` DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetRincianFilterRegional()
    {

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tti.regional_target,
    tmb.nama_bowheer,
    tmb.pic_user,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    tti.regional_target
    ORDER BY tti.regional_target ASC;')
            ->result_array();
        return $data;
    }

    public function getAllTargetRincianInvoice()
    {

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    tti.area_target
    ORDER BY `GRAND TOTAL TARGET` DESC;')
            ->result_array();
        return $data;
    }

    public function getAllTargetRincianInvoiceDecode()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments);
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.area_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TW5 OKTOBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 OKTOBER`,

    -- NOVEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.id_bowheer = "'.$decoded_url_area.'"
GROUP BY
    tti.area_target
    ORDER BY `GRAND TOTAL TARGET` DESC;')
            ->result_array();
        return $data;
    }

    public function getTargetInvoice($bowheer, $area, $month, $week)
    {

        $id = $this->db->select('id_bowheer')->where('nama_bowheer', $bowheer)->get('tb_master_bowheer_invoice')->row()->id_bowheer;

        $this->db->select('qty_target');
        $this->db->from('tb_target_invoice');
        $this->db->where('id_bowheer', $id);
        $this->db->where('area_target', $area);
        $this->db->where('month_target', $month);
        $this->db->where('week_target', $week);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return ['qty_target' => $query->row()->qty_target];
        } else {
            return ['qty_target' => 0];
        }
    }

}

