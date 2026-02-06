<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_Report extends CI_Model
{

    public function getAllDataReport()
    {
        $data = $this->db->query('SELECT
    mab.id_mab,
    mab.mab_akun_utama,
    mab.mab_sub_akun,
    mab.mab_nomor_akun,
    mab.mab_deskripsi_akun,

    bm.bulan,
    bm.budget_bulan,

    COALESCE(SUM(bc.jumlah_cashflow), 0) AS realisasi_bulan

FROM budget_masterakunbiaya mab

JOIN budget_years bdy
    ON bdy.id_mab = mab.id_mab
    AND bdy.tahun = "2026"

JOIN budget_monthly bm
    ON bm.id_budget_years = bdy.id_budget_years

LEFT JOIN budget_cashflow bc
    ON bc.id_mab = mab.id_mab
    AND MONTH(bc.date_cashflow) = bm.bulan
    AND YEAR(bc.date_cashflow) = bdy.tahun

GROUP BY
    mab.id_mab,
    bm.bulan
ORDER BY
    mab.mab_nomor_akun,
    bm.bulan;')
            ->result_array();
        return $data;
    }


    public function getFilteredBudget_Report($akun_utama, $sub_akun, $nomor_akun, $deskripsi_akun)
    {
        $this->db->select('id_mab, mab_akun_utama, mab_sub_akun, mab_nomor_akun, mab_deskripsi_akun, mab_divisi, mab_pic
    ');
        $this->db->from('Budget_Report');

        // === FILTERS ===
        if (!empty($akun_utama))
            $this->db->where_in('mab_akun_utama', $akun_utama);
        if (!empty($sub_akun))
            $this->db->where_in('mab_sub_akun', $sub_akun);
        if (!empty($nomor_akun))
            $this->db->where_in('mab_nomor_akun', $nomor_akun);
        if (!empty($deskripsi_akun))
            $this->db->where_in('mab_deskripsi_akun', $deskripsi_akun);

        $query = $this->db->get();

        // untuk debug query
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

        $this->db->insert('Budget_Report', $hasil_data);
        $nilai_update = $this->db->affected_rows();

        if ($this->db->affected_rows() > 0) {
            return ['status' => true, 'message' => 'Update berhasil', 'nilai_update' => $nilai_update];
        } else {
            return ['status' => false, 'message' => 'Tidak ada data yang diubah'];
        }
    }

    public function deleteMasterAkun($id_mab)
    {
        $res = $this->db->delete("Budget_Report", $id_mab);
        return $res;
    }

    public function updateMasterAkun($id_mab, $data)
    {
        $this->db->where('id_mab', $id_mab);
        $this->db->update('master_akun_biaya', $data);

        return $this->db->affected_rows();
    }
}

