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
GROUP BY tti.id_bowheer ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getTargetAllCity()
    {
        $data = $this->db->query('SELECT
	tti.regional_target,
	tti.area_target,
    tti.pic_target,
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
    GROUP BY tti.id_bowheer
    ORDER BY tmb.pic_user ASC')
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

    public function getDetailTargetCityFilterBowheerRegional()
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
    GROUP BY tti.regional_target
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
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`
    
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
    tti.pic_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`

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
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`

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
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`

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
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`
FROM
    tb_target_invoice tti 
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
GROUP BY
    tti.area_target, tmb.nama_bowheer 
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
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`

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

    public function getAllTargetRincianInvoiceDecodeRegional()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments);
        $decoded_url_area = urldecode($last_segment);

        $data = $this->db->query('SELECT
    tti.id_bowheer,
    tti.regional_target,
    tmb.nama_bowheer,

    -- Total per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TOTAL TARGET DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_target_ghost ELSE 0 END) AS `TOTAL TARGET SEPTEMBER`,

    -- Total achieved per bulan
    SUM(CASE WHEN tti.month_target = "OKTOBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED OKTOBER`,
    SUM(CASE WHEN tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED NOVEMBER`,
    SUM(CASE WHEN tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED DESEMBER`,
    SUM(CASE WHEN tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JANUARI`,
    SUM(CASE WHEN tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED FEBRUARI`,
    SUM(CASE WHEN tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MARET`,
    SUM(CASE WHEN tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED APRIL`,
    SUM(CASE WHEN tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED MEI`,
    SUM(CASE WHEN tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JUNI`,
    SUM(CASE WHEN tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED JULI`,
    SUM(CASE WHEN tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED AGUSTUS`,
    SUM(CASE WHEN tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `TOTAL ACHIEVED SEPTEMBER`,
    
    -- Grand Total tahun 2025
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET 2025`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2025`,
    
    -- Grand Total tahun 2026 TW 1
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW1`,
    SUM(CASE WHEN tti.month_target IN ("JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW1`,
    
    -- Grand Total tahun 2026 TW 2
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_target_ghost ELSE 0 END) AS `GRAND TOTAL TARGET 2026 TW2`,
    SUM(CASE WHEN tti.month_target IN ("APRIL", "MEI", "JUNI") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED 2026 TW2`,

    -- Grand Total seluruh bulan
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_target ELSE 0 END) AS `GRAND TOTAL TARGET`,
    SUM(CASE WHEN tti.month_target IN ("OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET") THEN tti.qty_achiev_target ELSE 0 END) AS `GRAND TOTAL ACHIEVED`,

    -- OKTOBER 2025
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

    -- NOVEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 NOVEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "NOVEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 NOVEMBER`,

    -- DESEMBER 2025
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_target ELSE 0 END) AS `TW4 DESEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "DESEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 DESEMBER`,

    -- JANUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JANUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JANUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JANUARI`,

    -- FEBRUARI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN qty_target_ghost ELSE 0 END) AS `TW5 FEBRUARI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "FEBRUARI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 FEBRUARI`,

    -- MARET 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN qty_target_ghost ELSE 0 END) AS `TW5 MARET`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MARET" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MARET`,
    
    -- APRIL 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN qty_target_ghost ELSE 0 END) AS `TW5 APRIL`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "APRIL" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 APRIL`,
    
    -- MEI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN qty_target_ghost ELSE 0 END) AS `TW5 MEI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "MEI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 MEI`,
    
    -- JUNI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JUNI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JUNI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JUNI`,
    
    -- JULI 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN qty_target_ghost ELSE 0 END) AS `TW5 JULI`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "JULI" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 JULI`,
    
    -- AGUSTUS 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN qty_target_ghost ELSE 0 END) AS `TW5 AGUSTUS`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "AGUSTUS" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 AGUSTUS`,
    
    -- SEPTEMBER 2026
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W1" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW1 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W2" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW2 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W3" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW3 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W4" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW4 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN qty_target_ghost ELSE 0 END) AS `TW5 SEPTEMBER`,
    SUM(CASE WHEN tti.week_target = "W5" AND tti.month_target = "SEPTEMBER" THEN tti.qty_achiev_target ELSE 0 END) AS `RW5 SEPTEMBER`

FROM
    tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb 
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.id_bowheer = "'.$decoded_url_area.'"
GROUP BY
    tti.regional_target
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

