<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MMyRepublik_Project extends CI_Model
{

    public function getInvoice()
    {
        $data = $this->db->query('SELECT primary_access_id_project, SUM(nilai_awal_po) as nilai_awal_po, SUM(total_invoice) as total_invoice, SUM(total_sisa_invoice) as total_sisa_invoice from tb_project_progress_fiberstar;')->result_array();
        return $data;
    }

    public function getTotalHpPlanAll()
    {
        $data = $this->db->query('SELECT SUM(hp_plan) as total_hp_plan,
	    SUM(panjang_plan) as total_panjang_plan,
        SUM(CASE WHEN tanggal_bak IS NOT NULL AND tanggal_bak != "" THEN hp_bak ELSE 0 END) as total_hp_bak,
        SUM(CASE WHEN status_bap_snd IS NOT NULL and status_bap_snd != "" THEN hp_snd ELSE 0 END) as total_hp_snd,
        SUM(CASE WHEN tanggal_drm_sf IS NOT NULL and tanggal_drm_sf = "DONE" THEN hp_drm ELSE 0 END) as total_hp_drm,
        SUM(CASE WHEN tanggal_rfs IS NOT NULL and tanggal_rfs != "" THEN hp_rfs ELSE 0 END) as total_hp_rfs
        FROM tb_project_progress_myrep;')
            ->result_array();
        return $data;
    }

    public function getTotalHpPlanArea()
    {
        $sessionLevel = $this->session->userdata('tim_project');

        $data = $this->db->query('SELECT SUM(hp_plan) as total_hp_plan,
	    SUM(panjang_plan) as total_panjang_plan,
        SUM(CASE WHEN tanggal_bak IS NOT NULL AND tanggal_bak != "" THEN hp_bak ELSE 0 END) as total_hp_bak,
        SUM(CASE WHEN status_bap_snd IS NOT NULL and status_bap_snd != "" THEN hp_snd ELSE 0 END) as total_hp_snd,
        SUM(CASE WHEN tanggal_drm_sf IS NOT NULL and tanggal_drm_sf = "DONE" THEN hp_drm ELSE 0 END) as total_hp_drm,
        SUM(CASE WHEN tanggal_rfs IS NOT NULL and tanggal_rfs != "" THEN hp_rfs ELSE 0 END) as total_hp_rfs
        FROM tb_project_progress_myrep
        WHERE tb_project_progress_myrep.pic_project = "' . $sessionLevel . '";')
            ->result_array();
        return $data;
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

    

    public function getUniqueProvinsi(): mixed
    {
        $data = $this->db->query('SELECT provinsi_project from tb_project_progress_myrep WHERE provinsi_project IS NOT NULL AND provinsi_project != "" GROUP By provinsi_project;')
            ->result_array();
        return $data;
    }

    public function getUniqueTipeProject(): mixed
    {
        $data = $this->db->query('SELECT type_project from tb_project_progress_myrep WHERE type_project IS NOT NULL AND type_project != "" GROUP By type_project;')
            ->result_array();
        return $data;
    }

    public function getUniqueStatusProjectTKM(): mixed
    {
        $data = $this->db->query('SELECT status_project_tkm from tb_project_progress_myrep WHERE status_project_tkm IS NOT NULL AND status_project_tkm != "" GROUP By status_project_tkm;')
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
        $data = $this->db->query('SELECT kota_project from tb_project_progress_myrep WHERE kota_project IS NOT NULL AND kota_project != "" GROUP By kota_project;')
            ->result_array();
        return $data;
    }

    public function getUniqueStagging(): mixed
    {
        $data = $this->db->query('SELECT main_status from tb_project_progress_fiberstar WHERE main_status IS NOT NULL AND main_status != "" GROUP By main_status;')
            ->result_array();
        return $data;
    }

    public function gettopAreaCleanlist(): mixed
    {
        $data = $this->db->query('SELECT * , ROUND(SUM(hp_plan)) as achiev_cleanlist FROM tb_project_progress_myrep GROUP BY kota_project ORDER BY achiev_cleanlist DESC LIMIT 5;')
            ->result_array();
        return $data;
    }

    public function gettopAreaBAK(): mixed
    {
        $data = $this->db->query('SELECT * , ROUND(SUM(CASE WHEN tanggal_bak IS NOT NULL AND tanggal_bak != "" THEN hp_bak ELSE 0 END)) as achiev_bak FROM tb_project_progress_myrep GROUP BY kota_project ORDER BY achiev_bak DESC LIMIT 5;')
            ->result_array();
        return $data;
    }

    public function gettopAreaRFS(): mixed
    {
        $data = $this->db->query('SELECT * , ROUND(SUM(CASE WHEN tanggal_rfs IS NOT NULL and tanggal_rfs != "" THEN hp_rfs ELSE 0 END)) as achiev_rfs FROM tb_project_progress_myrep GROUP BY kota_project ORDER BY achiev_rfs DESC LIMIT 10;')
            ->result_array();
        return $data;
    }

    public function getProgressImplementasiAll(): mixed
    {
        $data = $this->db->query("SELECT * FROM tb_project_progress_myrep")
            ->result_array();
        return $data;
    }

    public function getProgressImplementasiArea(): mixed
    {
        $sessionLevel = $this->session->userdata('tim_project');
        $data = $this->db->query('SELECT * FROM tb_project_progress_myrep')
            ->result_array();
        return $data;
    }

    public function addProgressImplementasi($data_array)
    {
        $res = $this->db->insert("tb_project_implementasi_fiberstar", $data_array);
        return $res;
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
}
