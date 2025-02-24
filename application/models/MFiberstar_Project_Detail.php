<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MFiberstar_Project_Detail extends CI_Model
{

    public function getMasterDataDokument(): mixed
    {
        $data = $this->db->query('SELECT * FROM md_document_support_fiberstar')->result_array();
        return $data;
    }

    public function getDataDocumentSupportApproval($string): mixed 
    {
        $data = $this->db->query('SELECT * FROM tb_ds_approval_cbn WHERE primary_access_id_project = "' . $string . '"')->result_array();
        return $data;
    }

    public function getCountDocumentSupportApproval($string): mixed 
    {
        try {
            $data = $this->db->query("
                SELECT
                    mdsf.stagging_document_support,
                    COUNT(*) AS total,
                    MAX(tdac.last_update) AS last_update
                FROM
                    tb_ds_approval_cbn tdac
                LEFT JOIN md_document_support_fiberstar mdsf 
                    ON
                    mdsf.id_document_support = tdac.id_document_support_fiberstar
                WHERE
                    mdsf.stagging_document_support IN ('APPROVAL CBN', 'ATP', 'RFS', 'BAST')
                    AND tdac.primary_access_id_project = '".$string."'
                GROUP BY
                    mdsf.stagging_document_support;
            ")->result_array();
            return $data;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return [];
        }
    }

    public function getProgressImplementasi(): mixed
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

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
                                    WHERE tb_project_progress_fiberstar.access_id_project = "' . $last_segment . '"
                                    GROUP BY tb_project_progress_fiberstar.primary_access_id_project')
            ->result_array();
        return $data;
    }

    public function getDetailProgressImplementasi(): mixed
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $data = $this->db->query('SELECT tb_project_progress_fiberstar.*, tb_project_implementasi_fiberstar.*, tb_master_user.* 
                                  FROM tb_project_implementasi_fiberstar 
                                  LEFT JOIN tb_project_progress_fiberstar 
                                  ON tb_project_implementasi_fiberstar.access_id_project = tb_project_progress_fiberstar.access_id_project 
                                  LEFT JOIN tb_master_user 
                                  ON tb_project_implementasi_fiberstar.id_user = tb_master_user.id_user
                                  WHERE tb_project_implementasi_fiberstar.access_id_project = "' . $last_segment . '"
                                  ')
            ->result_array();
        return $data;
    }

    public function getDokumentSupportApprovalCBN(): mixed
    {
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if (preg_match('/\/(\d+)$/', $currentUrl, $matches)) {
            $number = $matches[1]; // Angka setelah slash terakhir
        } else {
            $number = "Tidak ada angka di belakang URL";
        }

        $data = $this->db->query('SELECT tb_project_progress_fiberstar.*, tb_ds_approval_cbn.* 
                                    FROM tb_project_progress_fiberstar 
                                    LEFT JOIN tb_ds_approval_cbn 
                                    ON tb_project_progress_fiberstar.primary_access_id_project = tb_ds_approval_cbn.primary_access_id_project 
                                    WHERE tb_project_progress_fiberstar.access_id_project = "' . $number . '";
                                  ')
            ->result_array();
        return $data;
    }

    public function addProgressRAB($data_array)
    {
        $res = $this->db->insert("tb_project_implementasi_fiberstar", $data_array);
        return $res;
    }

    public function addProgressImplementasi($data_array)
    {
        $res = $this->db->insert("tb_project_implementasi_fiberstar", $data_array);
        return $res;
    }

    public function editStatusImplementasi($data)
    {
        $res = $this->db->query('UPDATE tb_project_progress_fiberstar SET status_implementasi = "OK" WHERE primary_access_id_project ="' . $data . '"');
        return $res;
    }

    public function editStatusImplementasiBack($data)
    {
        $res = $this->db->query('UPDATE tb_project_progress_fiberstar SET status_implementasi = "NOT OK" WHERE primary_access_id_project ="' . $data . '"');
        return $res;
    }

    public function insert_file($data)
    {
        $res = $this->db->insert('tb_ds_approval_cbn', $data);
        return $res;
    }

    public function getMasterUser(){
        $data = $this->db->query('SELECT * FROM tb_master_user')->result_array();
        return $data;
    }
}
