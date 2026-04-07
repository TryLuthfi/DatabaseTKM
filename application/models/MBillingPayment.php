<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBillingPayment extends CI_Model
{

    public function getAllData()
    {
        $data = $this->db->query('SELECT
    tbp.*,
    tmbi.*,

    tmbi.jt_invoice,

    -- umur invoice
    DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) AS umur_invoice,

    -- 🔥 jatuh tempo dinamis
    DATE_ADD(tbp.tgl_submit_invoice, INTERVAL tmbi.jt_invoice DAY) AS tgl_jatuh_tempo,

    -- priority
    CASE
        WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 75 THEN "P1"
        WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 45 THEN "P2"
        ELSE "P3"
    END AS priority,
    
    CASE 
    WHEN CURDATE() > DATE_ADD(tbp.tgl_submit_invoice, INTERVAL tmbi.jt_invoice DAY)
        THEN "OVERDUE"
    WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= (tmbi.jt_invoice - 7)
        THEN "WARNING"
    ELSE "AMAN"
END AS status_monitor

FROM tb_billingpayment tbp
JOIN tb_master_bowheer_bilco tmbi 
    ON tbp.id_bowheer = tmbi.id_bowheer
    ORDER BY umur_invoice DESC')
            ->result_array();
        return $data;
    }

    public function getAllKoKab()
    {
        $data = $this->db->query('SELECT * FROM md_kokab_indonesia ORDER BY name ASC')
            ->result_array();
        return $data;
    }

    public function getTargetPriorityBowheer()
    {
        $data = $this->db->query('SELECT
	tmbi.nama_bowheer,
    -- TOTAL SEMUA
    SUM(tbp.invoice_price_nett) AS total_all,

    -- P1
    SUM(
        CASE 
            WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 75 
            THEN tbp.invoice_price_nett 
            ELSE 0 
        END
    ) AS total_p1,

    -- P2
    SUM(
        CASE 
            WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 45
             AND DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 75
            THEN tbp.invoice_price_nett 
            ELSE 0 
        END
    ) AS total_p2,

    -- P3
    SUM(
        CASE 
            WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 45
            THEN tbp.invoice_price_nett 
            ELSE 0 
        END
    ) AS total_p3

FROM tb_billingpayment tbp
JOIN tb_master_bowheer_bilco tmbi 
    ON tbp.id_bowheer = tmbi.id_bowheer
WHERE tbp.status_invoice = "open"
    GROUP BY tmbi.id_bowheer
    ORDER BY total_p1 DESC')
            ->result_array();
        return $data;
    }

    public function getFilteredBillingPayment($bowheer, $regional, $city, $priority, $statusInvoice = 'open')
    {
        if ($priority == "P1") {
            $priorityCondition = 'DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 75';
        } elseif ($priority == "P2") {
            $priorityCondition = 'DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 45 AND DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 75';
        } elseif ($priority == "P3") {
            $priorityCondition = 'DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 45';
        } else {
            $priorityCondition = '1=1'; // Jika tidak ada filter priority, tampilkan semua
        }

        $this->db->select('tbp.*,
        tmbi.*,

    tmbi.jt_invoice,

    -- umur invoice
    DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) AS umur_invoice,

    -- 🔥 jatuh tempo dinamis
    DATE_ADD(tbp.tgl_submit_invoice, INTERVAL tmbi.jt_invoice DAY) AS tgl_jatuh_tempo,

    -- priority
    CASE
        WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 75 THEN "P1"
        WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 45 THEN "P2"
        ELSE "P3"
    END AS priority,
    
    CASE 
    WHEN CURDATE() > DATE_ADD(tbp.tgl_submit_invoice, INTERVAL tmbi.jt_invoice DAY)
        THEN "OVERDUE"
    WHEN DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= (tmbi.jt_invoice - 7)
        THEN "WARNING"
    ELSE "AMAN"
END AS status_monitor');
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer');

        // === FILTERS ===
        if (!empty($bowheer))
            $this->db->where_in('nama_bowheer', $bowheer);
        if (!empty($regional))
            $this->db->where_in('regional_payment', $regional);
        if (!empty($city))
            $this->db->where_in('area_payment', $city);
        if (!empty($statusInvoice))
        $this->db->where('tbp.status_invoice', $statusInvoice);
        if (!empty($priority)) {

            $conditions = [];

            foreach ($priority as $p) {
                if ($p == "P1") {
                    $conditions[] = 'DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 75';
                } elseif ($p == "P2") {
                    $conditions[] = '(DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) >= 45 AND DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 75)';
                } elseif ($p == "P3") {
                    $conditions[] = 'DATEDIFF(CURDATE(), tbp.tgl_submit_invoice) < 45';
                }
            }

            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        $this->db->order_by('umur_invoice', 'DESC');

        $query = $this->db->get();

        // untuk debug query
        log_message('debug', 'Last Query billing: ' . $this->db->last_query());

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
            'OKTOBER' => ['W1', 'W2', 'W3', 'W4', 'W5'],
            'NOVEMBER' => ['W1', 'W2', 'W3', 'W4'],
            'DESEMBER' => ['W1', 'W2', 'W3', 'W4'],
            'JANUARI' => ['W1', 'W2', 'W3', 'W4', 'W5'],
            'FEBRUARI' => ['W1', 'W2', 'W3', 'W4', 'W5'],
            'MARET' => ['W1', 'W2', 'W3', 'W4', 'W5']
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

