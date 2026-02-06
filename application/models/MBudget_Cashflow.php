<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_Cashflow extends CI_Model
{

    public function getAllDataCashflow()
    {
        $data = $this->db->query('SELECT
	bc.id_cashflow,
	bc.date_cashflow,
	mab.mab_nomor_akun,
	mab.mab_akun_utama,
	mab.mab_sub_akun,
	mab.mab_deskripsi_akun,
	bc.area_cashflow,
	bc.project_cashflow,
	tmbi.nama_bowheer,
	bc.remarks_cashflow,
	mab.mab_status,
	CASE
		WHEN mab.mab_status = "IN" THEN bc.jumlah_cashflow
		ELSE 0
	END AS cashflow_in,
	CASE
		WHEN mab.mab_status = "OUT" THEN bc.jumlah_cashflow
		ELSE 0
	END AS cashflow_out
FROM
	budget_cashflow bc
JOIN budget_masterakunbiaya mab 
    ON
	mab.id_mab = bc.id_mab
JOIN tb_master_bowheer_invoice tmbi ON
	tmbi.id_bowheer = bc.project_cashflow
WHERE
	mab.is_active = 1
ORDER BY
	bc.date_cashflow DESC')
            ->result_array();
        return $data;
    }


    public function getFilteredBudget_Cashflow($akun_utama, $sub_akun, $nomor_akun, $deskripsi_akun)
    {
        $this->db->select('id_mab, mab_akun_utama, mab_sub_akun, mab_nomor_akun, mab_deskripsi_akun, mab_divisi, mab_pic
    ');
        $this->db->from('budget_cashflow');

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

        $this->db->insert('Budget_Cashflow', $hasil_data);
        $nilai_update = $this->db->affected_rows();

        if ($this->db->affected_rows() > 0) {
            return ['status' => true, 'message' => 'Update berhasil', 'nilai_update' => $nilai_update];
        } else {
            return ['status' => false, 'message' => 'Tidak ada data yang diubah'];
        }
    }

    public function deleteMasterAkun($id_mab)
    {
        $res = $this->db->delete("Budget_Cashflow", $id_mab);
        return $res;
    }

    public function updateMasterAkun($id_mab, $data)
    {
        $this->db->where('id_mab', $id_mab);
        $this->db->update('master_akun_biaya', $data);

        return $this->db->affected_rows();
    }
}

