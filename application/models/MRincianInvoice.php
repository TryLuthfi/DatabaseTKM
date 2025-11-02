<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MRincianInvoice extends CI_Model
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

    public function getFilteredRincianInvoice($pic, $bowheer, $regional, $city, $month, $week)
    {
        $this->db->select('
        pic_user,
        nama_bowheer,
        regional_target,
        area_target,
        SUM(qty_target) as total_target,
        SUM(qty_achiev_target) as total_achieved,
        (SUM(qty_target) - SUM(qty_achiev_target)) as sisa,
        CASE WHEN SUM(qty_target) > 0 THEN (SUM(qty_achiev_target)/SUM(qty_target))*100 ELSE 0 END as persen_achieved,
        CASE WHEN SUM(qty_target) > 0 THEN ((SUM(qty_target) - SUM(qty_achiev_target))/SUM(qty_target))*100 ELSE 0 END as persen_sisa
    ');
        $this->db->from('tb_target_invoice');
        $this->db->join('tb_master_bowheer_invoice', 'tb_target_invoice.id_bowheer = tb_master_bowheer_invoice.id_bowheer');

        // === FILTERS ===
        if (!empty($pic))
            $this->db->where_in('pic_user', $pic);
        if (!empty($bowheer))
            $this->db->where_in('nama_bowheer', $bowheer);
        if (!empty($regional))
            $this->db->where_in('regional_target', $regional);
        if (!empty($city))
            $this->db->where_in('area_target', $city);
        if (!empty($month))
            $this->db->where_in('month_target', $month);
        if (!empty($week))
            $this->db->where_in('week_target', $week);

        $this->db->having('SUM(qty_achiev_target) >', 0);
        // === GROUP BY DYNAMIC ===
        if (!empty($pic) && !empty($bowheer) && !empty($regional) && !empty($city)) {
            $this->db->group_by(['pic_user', 'nama_bowheer']);
        } elseif (empty($pic) && !empty($bowheer)) {
            $this->db->group_by(['pic_user', 'nama_bowheer', 'regional_target', 'area_target']);
        } elseif (empty($pic) && empty($bowheer) && !empty($regional)) {
            $this->db->group_by(['pic_user', 'nama_bowheer', 'regional_target', 'area_target']);
        } elseif (empty($pic) && empty($bowheer) && empty($regional) && !empty($city)) {
            $this->db->group_by(['pic_user', 'nama_bowheer', 'regional_target', 'area_target']);
        } else {
            $this->db->group_by(['pic_user']);
        }

        $query = $this->db->get();

        // untuk debug query
        log_message('debug', 'Last Query: ' . $this->db->last_query());

        return $query->result_array();
    }

    public function getTargetInvoice($bowheer, $area, $month, $week)
    {
        // Ambil data id_bowheer
        $row = $this->db->select('id_bowheer')
            ->where('nama_bowheer', $bowheer)
            ->get('tb_master_bowheer_invoice')
            ->row();

        if (!$row) {
            // Jika tidak ditemukan, langsung return 0 agar tidak error
            return ['qty_target' => 0];
        }

        $id = $row->id_bowheer;

        $this->db->select('qty_target, qty_achiev_target');
        $this->db->from('tb_target_invoice');
        $this->db->where('id_bowheer', $id);
        $this->db->where('area_target', $area);
        $this->db->where('month_target', $month);
        $this->db->where('week_target', $week);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return ['qty_target' => $query->row()->qty_target, 'qty_achiev_target' => $query->row()->qty_achiev_target];
        } else {
            return ['qty_target' => 0, 'qty_achiev_target' => 0];
        }
    }

    public function updateAchievInvoice($data)
    {
        $bowheer = $data['addfilter_bowheer'];
        $area = $data['addfilter_area'];
        $month = $data['addfilter_month'];
        $week = $data['addfilter_week'];

        // Cari ID Bowheer
        $row = $this->db->select('id_bowheer')
            ->where('nama_bowheer', $bowheer)
            ->get('tb_master_bowheer_invoice')
            ->row();

        if (!$row) {
            return ['status' => false, 'message' => 'Bowheer tidak ditemukan'];
        }

        $id_bowheer = $row->id_bowheer;

        // Tentukan nilai update
        $total_invoice = str_replace(['Rp', ' ', '.'], '', $data['total_invoice']);
        $tambahan_invoice = str_replace(['Rp', ' ', '.'], '', $data['tambahan_invoice']);
        $achiev_invoice = str_replace(['Rp', ' ', '.'], '', $data['achiev_invoice']);

        // Default value
        $nilai_update = 0;

        if (!empty($total_invoice) && $total_invoice > 0) {
            $nilai_update = $total_invoice;
        } elseif (!empty($tambahan_invoice) && $tambahan_invoice > 0) {
            $nilai_update = $achiev_invoice + $tambahan_invoice;
        } elseif (!empty($achiev_invoice) && $achiev_invoice > 0) {
            $nilai_update = $achiev_invoice;
        }

        // Update data di tb_target_invoice
        $this->db->where('id_bowheer', $id_bowheer);
        $this->db->where('area_target', $area);
        $this->db->where('month_target', $month);
        $this->db->where('week_target', $week);

        $this->db->update('tb_target_invoice', ['qty_achiev_target' => $nilai_update]);

        if ($this->db->affected_rows() > 0) {
            return ['status' => true, 'message' => 'Update berhasil', 'nilai_update' => $nilai_update];
        } else {
            return ['status' => false, 'message' => 'Tidak ada data yang diubah'];
        }
    }

}

