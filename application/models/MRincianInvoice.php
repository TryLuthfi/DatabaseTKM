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

    public function getAllKoKab()
    {
        $data = $this->db->query('SELECT * FROM md_kokab_indonesia ORDER BY name ASC')
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
        if (!empty($pic) && empty($bowheer) && empty($regional) && empty($city) && empty($month) && empty($week)) { // FILTER PIC ONLY
            $this->db->group_by(['pic_user', 'nama_bowheer']);
        } else if (empty($pic) && !empty($bowheer) && empty($regional) && empty($city) && empty($month) && empty($week)) { // FILTER BOWHEER ONLY
            $this->db->group_by(['nama_bowheer','area_target']);
        } else if (empty($pic) && empty($bowheer) && !empty($regional) && empty($city) && empty($month) && empty($week)) { // FILTER REGIONAL ONLY
            $this->db->group_by(['pic_user', 'nama_bowheer', 'regional_target', 'area_target']);
        } else if (empty($pic) && empty($bowheer) && empty($regional) && !empty($city) && empty($month) && empty($week)) { // FILTER CITY ONLY
            $this->db->group_by(['pic_user', 'nama_bowheer', 'regional_target', 'area_target']);
        } else if (empty($pic) && empty($bowheer) && empty($regional) && empty($city) && !empty($month) && empty($week)) { // FILTER MONTH ONLY
            $this->db->group_by(['pic_user', 'nama_bowheer']);
        } else if (empty($pic) && empty($bowheer) && empty($regional) && empty($city) && empty($month) && !empty($week)) { // FILTER WEEK ONLY
            $this->db->group_by(['pic_user', 'nama_bowheer']);
        } else if(!empty($bowheer)){
            $this->db->group_by(['nama_bowheer', 'area_target']);
        } else {
            $this->db->group_by(['nama_bowheer']);
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
        $regional = $data['inputRegionalBaru'];
        $pic = $data['inputPICBaru'];

        // Cari ID Bowheer
        $row = $this->db->select('id_bowheer')
            ->where('nama_bowheer', $bowheer)
            ->get('tb_master_bowheer_invoice')
            ->row();

        if (!$row) {
            return ['status' => false, 'message' => 'Bowheer tidak ditemukan'];
        }

        $id_bowheer = $row->id_bowheer;

        // Cek apakah kombinasi sudah ada
        $exists = $this->db->get_where('tb_target_invoice', [
            'id_bowheer' => $id_bowheer,
            'area_target' => $area
        ])->num_rows() > 0;

        $cleanRupiah = function ($val) {
            if ($val === null || $val === '')
                return 0;
            $val = trim(str_replace(['Rp', ' ', '.'], '', $val));
            $val = str_replace(',', '', $val);
            if (strpos($val, '-') === 0) {
                return -1 * (float) str_replace('-', '', $val);
            }
            return (float) $val;
        };

        if (!$exists) {
            return [
                'status' => 'not_found',
                'message' => 'Project tidak memiliki area ini',
                'id_bowheer' => $id_bowheer,
                'area_target' => $area,
                'month' => $month,
                'week' => $week,
                'regional' => $regional,
                'pic' => $pic,
                'nilai_update' => $cleanRupiah($data['achiev_invoice'])
            ];
        }

        $total_invoice = $cleanRupiah($data['total_invoice']);
        $tambahan_invoice = $cleanRupiah($data['tambahan_invoice']);
        $achiev_invoice = $cleanRupiah($data['achiev_invoice']);

        $nilai_update = 0;

        if ($total_invoice !== 0) {
            $nilai_update = $total_invoice;
        } elseif ($tambahan_invoice !== 0) {
            $nilai_update = $achiev_invoice + $tambahan_invoice;
        } elseif ($achiev_invoice !== 0) {
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

    public function createNewTargetInvoice($data)
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $id_bowheer = $data['id_bowheer'];
        $area = $data['area_target'];
        $nilai_update = $data['nilai_update'];
        $regional = $data['regional'];
        $pic = $data['pic'];
        $month_selected = strtoupper($data['month']);
        $week_selected = strtoupper($data['week']);

        // Struktur minggu per bulan
        $weeks_by_month = [
            'OKTOBER' => ['W1', 'W2', 'W3', 'W4', 'W4'],
            'NOVEMBER' => ['W1', 'W2', 'W3', 'W4'],
            'DESEMBER' => ['W1', 'W2', 'W3', 'W4']
        ];

        $data_insert = [];

        // Loop seluruh bulan & minggu → total 11 kombinasi
        foreach ($weeks_by_month as $month => $weeks) {
            foreach ($weeks as $week) {
                $data_insert[] = [
                    'id_bowheer' => $id_bowheer,
                    'regional_target' => $regional,
                    'area_target' => $area,
                    'pic_target' => $pic,
                    'week_target' => $week,
                    'month_target' => $month,
                    'qty_target' => '',
                    'qty_achiev_target' => ($month === $month_selected && $week === $week_selected)
                        ? $nilai_update
                        : ''
                ];
            }
        }

        // Masukkan semua baris ke database
        $this->db->insert_batch('tb_target_invoice', $data_insert);

        if ($this->db->affected_rows() > 0) {
            return [
                'status' => true,
                'message' => 'Area baru berhasil ditambahkan beserta seluruh kombinasi bulan & minggu.'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Gagal menambahkan area baru.'
            ];
        }
    }


}

