<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MFiberstar_Project extends CI_Model
{

    // ambil seluruh total nilai po 
    public function getInvoice()
    {
        $data = $this->db->query('SELECT primary_access_id_project,SUM(po_estimasi) as po_estimasi, SUM(nilai_awal_po) as nilai_awal_po, SUM(total_invoice) as total_invoice, SUM(total_sisa_invoice) as total_sisa_invoice from tb_project_progress_fiberstar;')->result_array();
        return $data;
    }

    // ambil seluruh total homepass semua area ( lokasi user = HO )
    public function getTotalHpPlanAll()
    {
        $data = $this->db->query('SELECT SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN main_status = "01. CLEANLIST" THEN hpplan_project ELSE 0 END) as stagging_cleanlist, 
        SUM(CASE WHEN main_status = "02. CANVASING" THEN hpplan_project ELSE 0 END) as stagging_canvasing, 
        SUM(CASE WHEN main_status = "03. BAK" THEN hp_bak ELSE 0 END) as stagging_bak, 
        SUM(CASE WHEN main_status = "04. HLD" THEN hp_hld ELSE 0 END) as stagging_hld,
        SUM(CASE WHEN main_status = "05. SPK" THEN spk_hp ELSE 0 END) as stagging_spk,
        SUM(CASE WHEN main_status = "06. LLD" THEN hp_lld ELSE 0 END) as stagging_lld,
        SUM(CASE WHEN main_status = "07. IMPLEMENTASI" THEN hp_lld ELSE 0 END) as stagging_implementasi,
        SUM(CASE WHEN main_status = "08. RFS" THEN hp_rfs ELSE 0 END) as stagging_rfs,
        SUM(CASE WHEN main_status = "09. ATP" THEN hp_rfs ELSE 0 END) as stagging_atp,
        SUM(CASE WHEN main_status = "10. BAST" THEN hp_rfs ELSE 0 END) as stagging_bast,
        SUM(CASE WHEN main_status = "11. CLOSED" THEN hp_rfs ELSE 0 END) as stagging_closed,
        SUM(CASE WHEN main_status = "12. HOLD" THEN hpplan_project ELSE 0 END) as stagging_hold,
        SUM(CASE WHEN main_status = "13. DROP" THEN hpplan_project ELSE 0 END) as stagging_drop
        FROM tb_project_progress_fiberstar;')
            ->result_array();
        return $data;
    }

    // ambil seluruh total homepass semua area ( lokasi user = kota )
    public function getTotalHpPlanArea()
    {
        $sessionLevel = $this->session->userdata('lokasi_user');

        $data = $this->db->query('SELECT SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN main_status = "01. CLEANLIST" THEN hpplan_project ELSE 0 END) as stagging_cleanlist, 
        SUM(CASE WHEN main_status = "02. CANVASING" THEN hpplan_project ELSE 0 END) as stagging_canvasing, 
        SUM(CASE WHEN main_status = "03. BAK" THEN hp_bak ELSE 0 END) as stagging_bak, 
        SUM(CASE WHEN main_status = "04. HLD" THEN hp_hld ELSE 0 END) as stagging_hld,
        SUM(CASE WHEN main_status = "05. SPK" THEN spk_hp ELSE 0 END) as stagging_spk,
        SUM(CASE WHEN main_status = "06. LLD" THEN hp_lld ELSE 0 END) as stagging_lld,
        SUM(CASE WHEN main_status = "07. IMPLEMENTASI" THEN hp_lld ELSE 0 END) as stagging_implementasi,
        SUM(CASE WHEN main_status = "08. RFS" THEN hp_rfs ELSE 0 END) as stagging_rfs,
        SUM(CASE WHEN main_status = "09. ATP" THEN hp_rfs ELSE 0 END) as stagging_atp,
        SUM(CASE WHEN main_status = "10. BAST" THEN hp_rfs ELSE 0 END) as stagging_bast,
        SUM(CASE WHEN main_status = "11. CLOSED" THEN hp_rfs ELSE 0 END) as stagging_closed,
        SUM(CASE WHEN main_status = "12. HOLD" THEN hpplan_project ELSE 0 END) as stagging_hold,
        SUM(CASE WHEN main_status = "13. DROP" THEN hpplan_project ELSE 0 END) as stagging_drop
        FROM tb_project_progress_fiberstar
        WHERE tb_project_progress_fiberstar.pic_project = "' . $sessionLevel . '";')
            ->result_array();
        return $data;
    }

    // ambil detail list semua cluster ( lokasi user = HO )
    public function getProgressImplementasiAll(): mixed
    {
        $data = $this->db->query("SELECT tb_project_progress_fiberstar.*, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_tiang), 0) as plan_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_tiang), 0) as achiev_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_24), 0) as plan_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_24), 0) as achiev_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_48), 0) as plan_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_48), 0) as achiev_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_fat), 0) as plan_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_fat), 0) as achiev_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_closure), 0) as plan_closure,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_closure), 0) as achiev_closure
                                    FROM tb_project_progress_fiberstar 
                                    LEFT JOIN tb_project_implementasi_fiberstar 
                                    ON tb_project_implementasi_fiberstar.access_id_project = tb_project_progress_fiberstar.access_id_project  
                                    GROUP BY tb_project_progress_fiberstar.primary_access_id_project 
                                    ORDER BY CASE WHEN tb_project_progress_fiberstar.main_status 
                                    LIKE '%DROP%' OR tb_project_progress_fiberstar.main_status 
                                    LIKE '%HOLD%' THEN 1 ELSE 0 END, tb_project_progress_fiberstar.main_status DESC;")
            ->result_array();
        return $data;
    }

    public function getFilterData(): mixed
    {
        $data = $this->db->query("SELECT regional_project, pic_project, area_project, main_status
                                    FROM tb_project_progress_fiberstar
                                    GROUP BY tb_project_progress_fiberstar.primary_access_id_project 
                                    ORDER BY CASE WHEN tb_project_progress_fiberstar.main_status 
                                    LIKE '%DROP%' OR tb_project_progress_fiberstar.main_status 
                                    LIKE '%HOLD%' THEN 1 ELSE 0 END, tb_project_progress_fiberstar.main_status DESC;")
            ->result_array();
        return $data;
    }

    // ambil detail list semua cluster ( lokasi user = kota )
    public function getProgressImplementasiArea(): mixed
    {
        $sessionLevel = $this->session->userdata('username_user');
        $data = $this->db->query('SELECT tb_project_progress_fiberstar.*, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_tiang), 0) as plan_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_tiang), 0) as achiev_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_24), 0) as plan_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_24), 0) as achiev_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_48), 0) as plan_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_48), 0) as achiev_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_fat), 0) as plan_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_fat), 0) as achiev_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_closure), 0) as plan_closure,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_closure), 0) as achiev_closure
                                    FROM tb_project_progress_fiberstar 
                                    LEFT JOIN tb_project_implementasi_fiberstar 
                                    ON tb_project_implementasi_fiberstar.access_id_project = tb_project_progress_fiberstar.access_id_project 
                                    WHERE tb_project_progress_fiberstar.pic_project = "' . $sessionLevel . '"
                                    GROUP BY tb_project_progress_fiberstar.primary_access_id_project 
                                    ORDER BY CASE WHEN tb_project_progress_fiberstar.main_status 
                                    LIKE "%DROP%" OR tb_project_progress_fiberstar.main_status 
                                    LIKE "%HOLD%" THEN 1 ELSE 0 END, tb_project_progress_fiberstar.main_status DESC;')
            ->result_array();
        return $data;
    }

    // ambil seluruh total homepass semua area ( group by regional )
    public function getStaggingRegional()
    {
        $data = $this->db->query('SELECT regional_project,
		SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN main_status = "01. CLEANLIST" THEN hpplan_project ELSE 0 END) as stagging_cleanlist, 
        SUM(CASE WHEN main_status = "02. CANVASING" THEN hpplan_project ELSE 0 END) as stagging_canvasing, 
        SUM(CASE WHEN main_status = "03. BAK" THEN hp_bak ELSE 0 END) as stagging_bak, 
        SUM(CASE WHEN main_status = "04. HLD" THEN hp_hld ELSE 0 END) as stagging_hld,
        SUM(CASE WHEN main_status = "05. SPK" THEN spk_hp ELSE 0 END) as stagging_spk,
        SUM(CASE WHEN main_status = "06. LLD" THEN hp_lld ELSE 0 END) as stagging_lld,
        SUM(CASE WHEN main_status = "07. IMPLEMENTASI" THEN hp_lld ELSE 0 END) as stagging_implementasi,
        SUM(CASE WHEN main_status = "08. RFS" THEN hp_rfs ELSE 0 END) as stagging_rfs,
        SUM(CASE WHEN main_status = "09. ATP" THEN hp_rfs ELSE 0 END) as stagging_atp,
        SUM(CASE WHEN main_status = "10. BAST" THEN hp_rfs ELSE 0 END) as stagging_bast,
        SUM(CASE WHEN main_status = "11. CLOSED" THEN hp_rfs ELSE 0 END) as stagging_closed,
        SUM(CASE WHEN main_status = "12. HOLD" THEN hpplan_project ELSE 0 END) as stagging_hold,
        SUM(CASE WHEN main_status = "13. DROP" THEN hpplan_project ELSE 0 END) as stagging_drop
        FROM tb_project_progress_fiberstar
        GROUP BY regional_project;')
            ->result_array();
        return $data;
    }

    // ambil seluruh total homepass semua area ( group by kota )
    public function getStaggingArea()
    {
        $data = $this->db->query('SELECT regional_project,area_project,
        SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN main_status = "01. CLEANLIST" THEN hpplan_project ELSE 0 END) as stagging_cleanlist, 
        SUM(CASE WHEN main_status = "02. CANVASING" THEN hpplan_project ELSE 0 END) as stagging_canvasing, 
        SUM(CASE WHEN main_status = "03. BAK" THEN hp_bak ELSE 0 END) as stagging_bak, 
        SUM(CASE WHEN main_status = "04. HLD" THEN hp_hld ELSE 0 END) as stagging_hld,
        SUM(CASE WHEN main_status = "05. SPK" THEN spk_hp ELSE 0 END) as stagging_spk,
        SUM(CASE WHEN main_status = "06. LLD" THEN hp_lld ELSE 0 END) as stagging_lld,
        SUM(CASE WHEN main_status = "07. IMPLEMENTASI" THEN hp_lld ELSE 0 END) as stagging_implementasi,
        SUM(CASE WHEN main_status = "08. RFS" THEN hp_rfs ELSE 0 END) as stagging_rfs,
        SUM(CASE WHEN main_status = "09. ATP" THEN hp_rfs ELSE 0 END) as stagging_atp,
        SUM(CASE WHEN main_status = "10. BAST" THEN hp_rfs ELSE 0 END) as stagging_bast,
        SUM(CASE WHEN main_status = "11. CLOSED" THEN hp_rfs ELSE 0 END) as stagging_closed,
        SUM(CASE WHEN main_status = "12. HOLD" THEN hpplan_project ELSE 0 END) as stagging_hold,
        SUM(CASE WHEN main_status = "13. DROP" THEN hpplan_project ELSE 0 END) as stagging_drop
        FROM tb_project_progress_fiberstar
        GROUP BY area_project
        ORDER BY regional_project ASC;')
            ->result_array();
        return $data;
    }

    public function getTopChartAllStaggingKota()
    {
        $data = $this->db->query('SELECT regional_project,area_project,
        SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN tgl_canvasing IS NOT NULL AND tgl_canvasing != "" THEN hpplan_project ELSE 0 END) as total_hp_canvasing, 
        SUM(CASE WHEN status_bak IS NOT NULL AND status_bak = "OK" THEN hp_bak ELSE 0 END) as total_hp_bak, 
        SUM(CASE WHEN spk_nomor IS NOT NULL AND spk_nomor != "" THEN spk_hp ELSE 0 END) as total_hp_spk, 
        SUM(CASE WHEN status_hld IS NOT NULL AND status_hld = "OK" THEN hp_hld ELSE 0 END) as total_hp_hld,
        SUM(CASE WHEN status_lld IS NOT NULL AND status_lld = "OK" THEN hp_lld ELSE 0 END) as total_hp_lld,
        SUM(CASE WHEN tgl_kom IS NOT NULL AND tgl_kom != "" THEN hp_lld ELSE 0 END) as total_hp_kom,
        SUM(CASE WHEN tgl_pks IS NOT NULL AND tgl_pks != "" THEN hp_lld ELSE 0 END) as total_hp_pks,
        SUM(CASE WHEN status_implementasi IS NOT NULL AND status_implementasi = "OK" THEN hp_rfs ELSE 0 END) as total_hp_rfs,
        SUM(CASE WHEN tanggal_atp IS NOT NULL AND tanggal_atp != "" THEN hp_atp ELSE 0 END) as total_hp_atp,
        SUM(CASE WHEN main_status IS NOT NULL AND main_status = "CLOSED" THEN hp_atp ELSE 0 END) as total_hp_closed
        FROM tb_project_progress_fiberstar
        GROUP BY area_project
        ORDER BY regional_project ASC;')
            ->result_array();
        return $data;
    }


    // ambil seluruh total homepass filter kota di detail ( regional / kota )
    public function getTotalHpPlanFilter()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "lg.kota_lokasi_gudang";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "REGIONAL") !== false) {
            $filter_area = "regional_project";
        } else {
            $filter_area = "area_project";
        }

        $data = $this->db->query('SELECT SUM(hpplan_project) as total_hp_plan, 
        SUM(CASE WHEN tgl_canvasing IS NOT NULL AND tgl_canvasing != "" THEN hpplan_project ELSE 0 END) as total_hp_canvasing, 
        SUM(CASE WHEN status_bak IS NOT NULL AND status_bak = "OK" THEN hp_bak ELSE 0 END) as total_hp_bak, 
        SUM(CASE WHEN spk_nomor IS NOT NULL AND spk_nomor != "" THEN spk_hp ELSE 0 END) as total_hp_spk, 
        SUM(CASE WHEN status_hld IS NOT NULL AND status_hld = "OK" THEN hp_hld ELSE 0 END) as total_hp_hld,
        SUM(CASE WHEN status_lld IS NOT NULL AND status_lld = "OK" THEN hp_lld ELSE 0 END) as total_hp_lld,
        SUM(CASE WHEN tgl_kom IS NOT NULL AND tgl_kom != "" THEN hp_lld ELSE 0 END) as total_hp_kom,
        SUM(CASE WHEN tgl_pks IS NOT NULL AND tgl_pks != "" THEN hp_lld ELSE 0 END) as total_hp_pks,
        SUM(CASE WHEN status_implementasi IS NOT NULL AND status_implementasi = "OK" THEN hp_rfs ELSE 0 END) as total_hp_rfs,
        SUM(CASE WHEN tanggal_atp IS NOT NULL AND tanggal_atp != "" THEN hp_atp ELSE 0 END) as total_hp_atp,
        SUM(CASE WHEN main_status IS NOT NULL AND main_status = "CLOSED" THEN hp_atp ELSE 0 END) as total_hp_closed
        FROM tb_project_progress_fiberstar
        WHERE ' . $filter_area . ' = "' . $decoded_url_area . '";')
            ->result_array();
        return $data;
    }

    // ambil uniq regional untuk filter
    public function getUniqueRegional(): mixed
    {
        $data = $this->db->query('SELECT regional_project from tb_project_progress_fiberstar WHERE regional_project IS NOT NULL AND regional_project != "" GROUP By regional_project;')
            ->result_array();
        return $data;
    }

    // ambil uniq pic untuk filter
    public function getUniquePic(): mixed
    {
        $data = $this->db->query('SELECT pic_project from tb_project_progress_fiberstar WHERE pic_project IS NOT NULL AND pic_project != "" GROUP By pic_project;')
            ->result_array();
        return $data;
    }

    // ambil uniq kota untuk filter
    public function getUniqueArea(): mixed
    {
        $data = $this->db->query('SELECT area_project from tb_project_progress_fiberstar WHERE area_project IS NOT NULL AND area_project != "" GROUP By area_project;')
            ->result_array();
        return $data;
    }

    // ambil uniq stagging untuk filter
    public function getUniqueStagging(): mixed
    {
        $data = $this->db->query('SELECT main_status from tb_project_progress_fiberstar WHERE main_status IS NOT NULL AND main_status != "" GROUP By main_status;')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah bak ( group by kota ) ( sebelum filter tanggal )
    public function gettopAreaBAK(): mixed
    {
        $data = $this->db->query('SELECT 
    *, 
    COUNT(*) AS total_cluster_bak,
    COALESCE(SUM(hp_bak), 0) AS achiev_bak 
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_bak IS NOT NULL AND hp_bak > "0"
GROUP BY 
    area_project 
HAVING 
    achiev_bak != 0 
ORDER BY 
    achiev_bak DESC;')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah spk ( group by kota ) ( sebelum filter tanggal )
    public function gettopAreaSPK(): mixed
    {
        $data = $this->db->query('SELECT
	*,
	COUNT(*) AS total_cluster_spk,
	COALESCE(SUM(spk_hp)) AS achiev_spk
FROM
	tb_project_progress_fiberstar
WHERE
	spk_hp IS NOT NULL AND spk_hp > "0" AND spk_tanggal IS NOT NULL
GROUP BY
	area_project
HAVING
	achiev_spk > "0"
ORDER BY
	achiev_spk DESC;')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah rfs ( group by kota ) ( sebelum filter tanggal )
    public function gettopAreaRFS(): mixed
    {
        $data = $this->db->query('SELECT *, 
    ROUND(SUM(CASE 
        WHEN tanggal_rfs IS NOT NULL AND tanggal_rfs != "" THEN hp_rfs 
        ELSE 0 
    END)) AS achiev_rfs,
    COUNT(*) AS total_cluster_rfs
FROM tb_project_progress_fiberstar 
WHERE tanggal_rfs IS NOT NULL AND tanggal_rfs != "" 
GROUP BY area_project 
ORDER BY achiev_rfs DESC')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah bak ( detail ) ( sebelum filter tanggal )
    public function gettopAreaBAKDetail(): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_bak IS NOT NULL AND hp_bak > "0"
ORDER BY hp_bak ASC')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah spk ( detail ) ( sebelum filter tanggal )
    public function gettopAreaSPKDetail(): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    spk_hp IS NOT NULL AND spk_hp > "0"
ORDER BY hp_bak ASC')
            ->result_array();
        return $data;
    }

    // load awal untuk chart yang sudah rfs ( detail ) ( sebelum filter tanggal )
    public function gettopAreaRFSDetail(): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_rfs IS NOT NULL AND hp_rfs > "0"
ORDER BY hp_rfs ASC')
            ->result_array();
        return $data;
    }
    
    // load awal untuk chart yang sudah atp ( detail ) ( sebelum filter tanggal )
    public function gettopAreaATPDetail(): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_atp IS NOT NULL AND hp_atp > "0"
ORDER BY hp_atp ASC')
            ->result_array();
        return $data;
    }

    // menghitung jumlah row BAK untuk chart sesuai filter tanggal ( group by kota )
    public function gettopAreaBAKFilter($filterTanggalAwal, $filterTanggalAkhir): mixed
    {
        $data = $this->db->query('SELECT 
    *, 
    COUNT(*) AS total_cluster_bak,
    COALESCE(SUM(hp_bak), 0) AS achiev_bak 
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_bak IS NOT NULL AND hp_bak > "0" AND tanggal_bak >= "' . $filterTanggalAwal . '" && tanggal_bak <= "' . $filterTanggalAkhir . '"
GROUP BY 
    area_project 
HAVING 
    achiev_bak != 0 
ORDER BY 
    achiev_bak DESC;')
            ->result_array();
        return $data;
    }

    // melihat isi cluster BAK untuk detail chart sesuai filter tanggal ( tidak group by )
    public function gettopAreaBAKFilterDetail($filterTanggalAwal, $filterTanggalAkhir): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    hp_bak IS NOT NULL AND hp_bak > "0" AND tanggal_bak >= "' . $filterTanggalAwal . '" && tanggal_bak <= "' . $filterTanggalAkhir . '"
ORDER BY hp_bak ASC')
            ->result_array();
        return $data;
    }

    public function gettopAreaSPKFilterDetail($filterTanggalAwal, $filterTanggalAkhir): mixed
    {
        $data = $this->db->query('SELECT 
    *
FROM 
    tb_project_progress_fiberstar 
WHERE 
    spk_hp IS NOT NULL AND spk_hp > "0" AND spk_tanggal >= "' . $filterTanggalAwal . '" && spk_tanggal <= "' . $filterTanggalAkhir . '"
ORDER BY hp_bak ASC')
            ->result_array();
        return $data;
    }

    public function getProgressImplementasiFilter(): mixed
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $filter_area = "lg.kota_lokasi_gudang";
        $decoded_url_area = urldecode($last_segment);

        if (stripos($decoded_url_area, "REGIONAL") !== false) {
            $filter_area = "regional_project";
        } else {
            $filter_area = "area_project";
        }

        $data = $this->db->query("SELECT tb_project_progress_fiberstar.*, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_tiang), 0) as plan_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_tiang), 0) as achiev_tiang, 
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_24), 0) as plan_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_24), 0) as achiev_kabel_24,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_kabel_48), 0) as plan_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_kabel_48), 0) as achiev_kabel_48,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_fat), 0) as plan_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_fat), 0) as achiev_fat,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.plan_closure), 0) as plan_closure,
                                    COALESCE(SUM(tb_project_implementasi_fiberstar.achiev_closure), 0) as achiev_closure
                                    FROM tb_project_progress_fiberstar 
                                    LEFT JOIN tb_project_implementasi_fiberstar 
                                    ON tb_project_implementasi_fiberstar.access_id_project = tb_project_progress_fiberstar.access_id_project
                                    WHERE " . $filter_area . " = '" . $decoded_url_area . "'
                                    GROUP BY tb_project_progress_fiberstar.primary_access_id_project 
                                    ORDER BY CASE WHEN tb_project_progress_fiberstar.main_status 
                                    LIKE '%DROP%' OR tb_project_progress_fiberstar.main_status 
                                    LIKE '%HOLD%' THEN 1 ELSE 0 END, tb_project_progress_fiberstar.main_status DESC;")
            ->result_array();

        log_message('error', 'query filter implementasi yang dijalankan : ' . $this->db->last_query());
        return $data;
    }

    public function deleteData($id)
    {
        $res = $this->db->delete("tb_kode", $id);
        return $res;
    }

    public function updateData($data_array, $id)
    {
        $res = $this->db->update("tb_kode", $data_array, $id);
        return $res;
    }

    public function getFilterTanggalTopAreaAchievBAK($filterTanggalAwal, $filterTanggalAkhir)
    {
        $data = $this->db->query('SELECT
	*,
	COALESCE(SUM(CASE WHEN tanggal_bak >= "' . $filterTanggalAwal . '" && tanggal_bak <= "' . $filterTanggalAkhir . '" && tanggal_bak IS NOT NULL THEN hp_bak ELSE 0 END), 0) AS achiev_bak,
	SUM(CASE WHEN tanggal_bak >= "' . $filterTanggalAwal . '" && tanggal_bak <= "' . $filterTanggalAkhir . '" && tanggal_bak IS NOT NULL THEN 1 ELSE 0 END) AS total_cluster_bak
FROM
	tb_project_progress_fiberstar
GROUP BY
	area_project
HAVING
	achiev_bak != "0"
ORDER BY
	achiev_bak DESC;')
            ->result_array();

        log_message('error', 'query filter tanggal yang dijalankan : ' . $this->db->last_query());
        return $data;
    }

    public function getFilterTanggalTopAreaAchievSPK($filterTanggalAwal, $filterTanggalAkhir)
    {
        $data = $this->db->query('SELECT
	*,
	COALESCE(SUM(CASE WHEN spk_tanggal >= "' . $filterTanggalAwal . '" && spk_tanggal <= "' . $filterTanggalAkhir . '" && spk_tanggal IS NOT NULL THEN spk_hp ELSE 0 END), 0) AS achiev_spk,
	SUM(CASE WHEN spk_tanggal >= "' . $filterTanggalAwal . '" && spk_tanggal <= "' . $filterTanggalAkhir . '" && spk_tanggal IS NOT NULL THEN 1 ELSE 0 END) AS total_cluster_spk
FROM
	tb_project_progress_fiberstar
GROUP BY
	area_project
HAVING
	achiev_spk != "0"
ORDER BY
	achiev_spk DESC;')
            ->result_array();

        log_message('error', 'query filter tanggal yang dijalankan : ' . $this->db->last_query());
        return $data;
    }

    public function insertBatch($data): void {
        $this->db->insert_batch('tb_belumada', $data);
    }
}
