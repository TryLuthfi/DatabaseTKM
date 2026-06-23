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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi,

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
            ELSE (GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) / SUM(tti.qty_target)) * 100
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi,

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
            ELSE (GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) / SUM(tti.qty_target)) * 100
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi,

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
            ELSE (GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) / SUM(tti.qty_target)) * 100
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi,

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
            ELSE (GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) / SUM(tti.qty_target)) * 100
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi,
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
            ELSE (GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) / SUM(tti.qty_target)) * 100
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi
FROM tb_target_invoice tti
JOIN tb_master_bowheer_invoice tmb
    ON tti.id_bowheer = tmb.id_bowheer
    WHERE tti.area_target = "'.$decoded_url_area.'"
    GROUP BY tti.id_bowheer
    ORDER BY total_target DESC')
            ->result_array();
        return $data;
    }

    public function getRevampOverview()
    {
        $row = $this->db->query('SELECT
            COALESCE(SUM(qty_target), 0) AS total_target,
            COALESCE(SUM(qty_achiev_target), 0) AS total_achieved,
            GREATEST(COALESCE(SUM(qty_target), 0) - COALESCE(SUM(qty_achiev_target), 0), 0) AS outstanding,
            ROUND(
                CASE
                    WHEN COALESCE(SUM(qty_target), 0) = 0 AND COALESCE(SUM(qty_achiev_target), 0) > 0 THEN 100
                    WHEN COALESCE(SUM(qty_target), 0) = 0 THEN 0
                    ELSE (COALESCE(SUM(qty_achiev_target), 0) / COALESCE(SUM(qty_target), 0)) * 100
                END,
                1
            ) AS persen_achieved,
            COUNT(DISTINCT id_bowheer) AS total_project,
            COUNT(DISTINCT area_target) AS total_area,
            COUNT(DISTINCT regional_target) AS total_regional,
            COUNT(DISTINCT pic_target) AS total_pic_area
        FROM tb_target_invoice')
            ->row_array();

        return $row ?: [
            'total_target' => 0,
            'total_achieved' => 0,
            'outstanding' => 0,
            'persen_achieved' => 0,
            'total_project' => 0,
            'total_area' => 0,
            'total_regional' => 0,
            'total_pic_area' => 0,
        ];
    }

    public function getRevampInvoiceRows()
    {
        return $this->db->query('SELECT
            tti.id_target_invoice,
            tti.id_bowheer,
            tmb.nama_bowheer,
            tmb.pic_user,
            tti.regional_target,
            tti.area_target,
            tti.pic_target,
            tti.month_target,
            tti.week_target,
            COALESCE(tti.qty_target, 0) AS qty_target,
            COALESCE(tti.qty_achiev_target, 0) AS qty_achiev_target
        FROM tb_target_invoice tti
        JOIN tb_master_bowheer_invoice tmb ON tti.id_bowheer = tmb.id_bowheer
        ORDER BY tmb.nama_bowheer ASC, tti.area_target ASC, tti.month_target ASC, tti.week_target ASC')
            ->result_array();
    }

    public function getRevampPeriodSummary()
    {
        $monthOrder = 'CASE month_target
            WHEN "OKTOBER" THEN 1
            WHEN "NOVEMBER" THEN 2
            WHEN "DESEMBER" THEN 3
            WHEN "JANUARI" THEN 4
            WHEN "FEBRUARI" THEN 5
            WHEN "MARET" THEN 6
            WHEN "APRIL" THEN 7
            WHEN "MEI" THEN 8
            WHEN "JUNI" THEN 9
            WHEN "JULI" THEN 10
            WHEN "AGUSTUS" THEN 11
            WHEN "SEPTEMBER" THEN 12
            ELSE 99
        END';

        return $this->db->query('SELECT
            month_target,
            week_target,
            SUM(qty_target) AS total_target,
            SUM(qty_achiev_target) AS total_achieved,
            GREATEST(SUM(qty_target) - SUM(qty_achiev_target), 0) AS outstanding,
            ROUND(
                CASE
                    WHEN SUM(qty_target) = 0 AND SUM(qty_achiev_target) > 0 THEN 100
                    WHEN SUM(qty_target) = 0 THEN 0
                    ELSE (SUM(qty_achiev_target) / SUM(qty_target)) * 100
                END,
                1
            ) AS persen_achieved,
            COUNT(DISTINCT id_bowheer) AS total_project,
            COUNT(DISTINCT area_target) AS total_area
        FROM tb_target_invoice
        GROUP BY month_target, week_target
        ORDER BY '.$monthOrder.', week_target')
            ->result_array();
    }

    public function getRevampFilterOptions()
    {
        return [
            'projects' => $this->db->query('SELECT id_bowheer, nama_bowheer
                FROM tb_master_bowheer_invoice
                ORDER BY nama_bowheer ASC')
                ->result_array(),
            'pics' => $this->db->query('SELECT DISTINCT tmb.pic_user
                FROM tb_target_invoice tti
                JOIN tb_master_bowheer_invoice tmb ON tti.id_bowheer = tmb.id_bowheer
                WHERE tmb.pic_user IS NOT NULL AND tmb.pic_user <> ""
                ORDER BY tmb.pic_user ASC')
                ->result_array(),
            'regionals' => $this->db->query('SELECT DISTINCT regional_target
                FROM tb_target_invoice
                WHERE regional_target IS NOT NULL AND regional_target <> ""
                ORDER BY regional_target ASC')
                ->result_array(),
            'areas' => $this->db->query('SELECT DISTINCT area_target
                FROM tb_target_invoice
                WHERE area_target IS NOT NULL AND area_target <> ""
                ORDER BY area_target ASC')
                ->result_array(),
            'months' => $this->db->query('SELECT DISTINCT month_target
                FROM tb_target_invoice
                WHERE month_target IS NOT NULL AND month_target <> ""
                ORDER BY FIELD(month_target, "OKTOBER", "NOVEMBER", "DESEMBER", "JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER")')
                ->result_array(),
            'weeks' => $this->db->query('SELECT DISTINCT week_target
                FROM tb_target_invoice
                WHERE week_target IS NOT NULL AND week_target <> ""
                ORDER BY week_target ASC')
                ->result_array(),
        ];
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
    GREATEST(SUM(tti.qty_target) - SUM(tti.qty_achiev_target), 0) AS deviasi
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

