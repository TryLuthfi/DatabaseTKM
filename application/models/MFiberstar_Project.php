<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MFiberstar_Project extends CI_Model
{

    public function getData()
    {
        $data = $this->db->query('SELECT * FROM `tb_project_fiberstar` LEFT JOIN tb_project_fiberstar_progress ON tb_project_fiberstar.id_cluster_fiberstar = tb_project_fiberstar_progress.id_cluster_fiberstar_po LEFT JOIN tb_project_fiberstar_stagging ON tb_project_fiberstar_progress.stagging_pekerjaan_project_fiberstar_progress = tb_project_fiberstar_stagging.id_project_fiberstar_stagging LEFT JOIN tb_area ON tb_project_fiberstar.id_area = tb_area.id_area;')->result_array();
        return $data;
    }

    public function getMainData()
    {
        $data = $this->db->query('SELECT * FROM `tb_project_progress_fiberstar` ORDER BY `primary_access_id_project` ASC;')->result_array();
        return $data;
    }

    public function getInvoice()
    {
        $data = $this->db->query('SELECT primary_access_id_project,SUM(po_estimasi) as po_estimasi, SUM(nilai_awal_po) as nilai_awal_po, SUM(total_invoice) as total_invoice, SUM(total_sisa_invoice) as total_sisa_invoice from tb_project_progress_fiberstar;')->result_array();
        return $data;
    }

    public function getTotalHpPlanAll()
    {
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
        FROM tb_project_progress_fiberstar;')
            ->result_array();
        return $data;
    }

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

    public function getStaggingRegional()
    {
        $data = $this->db->query('SELECT regional_project,
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
        GROUP BY regional_project;')
            ->result_array();
        return $data;
    }

    public function getStaggingArea()
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

    public function getTotalHpPlanArea()
    {
        $sessionLevel = $this->session->userdata('lokasi_user');

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
        WHERE tb_project_progress_fiberstar.pic_project = "' . $sessionLevel . '";')
            ->result_array();
        return $data;
    }

    public function getUniqueRegional(): mixed
    {
        $data = $this->db->query('SELECT regional_project from tb_project_progress_fiberstar WHERE regional_project IS NOT NULL AND regional_project != "" GROUP By regional_project;')
            ->result_array();
        return $data;
    }

    public function getUniquePic(): mixed
    {
        $data = $this->db->query('SELECT pic_project from tb_project_progress_fiberstar WHERE pic_project IS NOT NULL AND pic_project != "" GROUP By pic_project;')
            ->result_array();
        return $data;
    }

    public function getUniqueArea(): mixed
    {
        $data = $this->db->query('SELECT area_project from tb_project_progress_fiberstar WHERE area_project IS NOT NULL AND area_project != "" GROUP By area_project;')
            ->result_array();
        return $data;
    }

    public function getUniqueStagging(): mixed
    {
        $data = $this->db->query('SELECT main_status from tb_project_progress_fiberstar WHERE main_status IS NOT NULL AND main_status != "" GROUP By main_status;')
            ->result_array();
        return $data;
    }

    public function gettopAreaRFS(): mixed
    {
        $data = $this->db->query('SELECT *, 
    ROUND(SUM(CASE 
        WHEN tanggal_rfs IS NOT NULL AND tanggal_rfs != "" THEN hp_rfs 
        ELSE 0 
    END)) AS achiev_rfs 
FROM tb_project_progress_fiberstar 
WHERE tanggal_rfs IS NOT NULL AND tanggal_rfs != "" 
GROUP BY area_project 
ORDER BY achiev_rfs DESC 
LIMIT 5;')
            ->result_array();
        return $data;
    }

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

    public function getGrafikByKota()
    {
        $data = $this->db->query('SELECT kota_project,
		SUM(hp_plan) as total_hp_plan,
	    SUM(panjang_plan) as total_panjang_plan,
        SUM(CASE WHEN tanggal_bak IS NOT NULL AND tanggal_bak != "" THEN hp_bak ELSE 0 END) as total_hp_bak,
        SUM(CASE WHEN status_bap_snd IS NOT NULL and status_bap_snd != "" THEN hp_snd ELSE 0 END) as total_hp_snd,
        SUM(CASE WHEN tanggal_drm_sf IS NOT NULL and tanggal_drm_sf = "DONE" THEN hp_drm ELSE 0 END) as total_hp_drm,
        SUM(CASE WHEN tanggal_rfs IS NOT NULL and tanggal_rfs != "" THEN hp_rfs ELSE 0 END) as total_hp_rfs
        FROM tb_project_progress_myrep
        GROUP BY kota_project
        ORDER BY total_hp_plan DESC;')
            ->result_array();
        return $data;
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
}
