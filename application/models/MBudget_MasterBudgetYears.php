<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterBudgetYears extends CI_Model
{

    public function getAllData()
    {
        $data = $this->db->query('SELECT * FROM budget_years JOIN `budget_masterakunbiaya` ON `budget_years`.`id_mab` = `budget_masterakunbiaya`.`id_mab`')
            ->result_array();
        return $data;
    }


    public function getFilteredBudget_MasterBudgetYears($akun_utama, $sub_akun, $nomor_akun, $deskripsi_akun)
    {
        $this->db->select("
        budget_years.id_budget_years,
        budget_years.tahun,
        budget_years.total_budget,

        budget_masterakunbiaya.id_mab,
        budget_masterakunbiaya.mab_akun_utama,
        budget_masterakunbiaya.mab_sub_akun,
        budget_masterakunbiaya.mab_nomor_akun,
        budget_masterakunbiaya.mab_deskripsi_akun,

        COALESCE(SUM(budget_monthly.budget_bulan),0) as total_monthly,
        (budget_years.total_budget - COALESCE(SUM(budget_monthly.budget_bulan),0)) as selisih
    ");

        $this->db->from('budget_years');

        $this->db->join(
            'budget_masterakunbiaya',
            'budget_years.id_mab = budget_masterakunbiaya.id_mab'
        );

        $this->db->join(
            'budget_monthly',
            'budget_monthly.id_budget_years = budget_years.id_budget_years',
            'left'
        );

        // FILTER
        if (!empty($akun_utama))
            $this->db->where_in('mab_akun_utama', $akun_utama);

        if (!empty($sub_akun))
            $this->db->where_in('mab_sub_akun', $sub_akun);

        if (!empty($nomor_akun))
            $this->db->where_in('mab_nomor_akun', $nomor_akun);

        if (!empty($deskripsi_akun))
            $this->db->where_in('mab_deskripsi_akun', $deskripsi_akun);

        $this->db->group_by('budget_years.id_budget_years');

        $query = $this->db->get();

        log_message('debug', 'Last Query aa: ' . $this->db->last_query());

        return $query->result_array();
    }


    public function tambahMasterAkun($data)
    {

        $akun_utama = !empty($data['addfilter_akun_utama'])
            ? $data['addfilter_akun_utama']
            : $data['inputAkunUtamaBaru'];

        $sub_akun = !empty($data['addfilter_sub_akun'])
            ? $data['addfilter_sub_akun']
            : $data['inputSubAkunBaru'];

        $divisi = !empty($data['addfilter_divisi'])
            ? $data['addfilter_divisi']
            : $data['inputDivisiBaru'];

        $pic = !empty($data['addfilter_pic'])
            ? $data['addfilter_pic']
            : $data['inputPICBaru'];

        $nomorakun = $data['inputNomorAkunBaru'];
        $deskripsiakun = $data['inputDeskripsiAkunBaru'];

        $hasil_data = array(
            'mab_akun_utama' => $akun_utama,
            'mab_sub_akun' => $sub_akun,
            'mab_divisi' => $divisi,
            'mab_pic' => $pic,
            'mab_nomor_akun' => $nomorakun,
            'mab_deskripsi_akun' => $deskripsiakun
        );

        $this->db->insert('budget_years', $hasil_data);
        $nilai_update = $this->db->affected_rows();

        if ($this->db->affected_rows() > 0) {
            return ['status' => true, 'message' => 'Update berhasil', 'nilai_update' => $nilai_update];
        } else {
            return ['status' => false, 'message' => 'Tidak ada data yang diubah'];
        }
    }

    public function deleteMasterAkun($id_mab)
    {
        $res = $this->db->delete("budget_years", $id_mab);
        return $res;
    }

    public function updateMasterAkun($id_mab, $data)
    {
        $this->db->where('id_mab', $id_mab);
        $this->db->update('master_akun_biaya', $data);

        return $this->db->affected_rows();
    }
}

