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

}

