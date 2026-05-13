<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBillingPayment extends CI_Model
{
    private $agingInvoiceExpr = 'DATEDIFF(CURDATE(), DATE(tbp.tgl_create_invoice))';
    private $agingDueExpr = 'DATEDIFF(CURDATE(), DATE_ADD(tbp.tgl_create_invoice, INTERVAL tmbi.jt_invoice DAY))';

    public function getAllData()
    {
        $agingInvoiceExpr = $this->agingInvoiceExpr;
        $agingDueExpr = $this->agingDueExpr;
        $data = $this->db->query('SELECT
    tbp.*,
    tmbi.*,

    tmbi.jt_invoice,

    -- aging dihitung terhadap tanggal invoice
    ' . $agingInvoiceExpr . ' AS umur_invoice,

    -- aging dihitung terhadap due date
    ' . $agingDueExpr . ' AS umur_due_date,

    -- 🔥 jatuh tempo dinamis
    DATE_ADD(tbp.tgl_create_invoice, INTERVAL tmbi.jt_invoice DAY) AS tgl_jatuh_tempo,

    -- priority
    CASE
        WHEN ' . $agingDueExpr . ' > 45 THEN "P1"
        WHEN ' . $agingDueExpr . ' BETWEEN 31 AND 45 THEN "P2"
        WHEN ' . $agingDueExpr . ' BETWEEN 0 AND 30 THEN "P3"
        ELSE "BJT"
    END AS priority,
    
    CASE 
    WHEN CURDATE() > DATE_ADD(tbp.tgl_create_invoice, INTERVAL tmbi.jt_invoice DAY)
        THEN "OVERDUE"
    WHEN ' . $agingDueExpr . ' >= -7
        THEN "WARNING"
    ELSE "AMAN"
END AS status_monitor

FROM tb_billingpayment tbp
JOIN tb_master_bowheer_bilco tmbi 
    ON tbp.id_bowheer = tmbi.id_bowheer
    ORDER BY umur_due_date DESC')
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
        $agingDueExpr = $this->agingDueExpr;
        $data = $this->db->query('SELECT
	tmbi.nama_bowheer,
    -- TOTAL SEMUA OUTSTANDING
    SUM(
        CASE
            WHEN tbp.status_invoice = "partial" THEN GREATEST(
                tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                0
            )
            ELSE tbp.invoice_price_nett
        END
    ) AS total_all,

    -- BJT
    SUM(
        CASE 
            WHEN ' . $agingDueExpr . ' < 0
            THEN CASE
                WHEN tbp.status_invoice = "partial" THEN GREATEST(
                    tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                    0
                )
                ELSE tbp.invoice_price_nett
            END
            ELSE 0 
        END
    ) AS total_bjt,

    -- P1
    SUM(
        CASE 
            WHEN ' . $agingDueExpr . ' > 45 
            THEN CASE
                WHEN tbp.status_invoice = "partial" THEN GREATEST(
                    tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                    0
                )
                ELSE tbp.invoice_price_nett
            END
            ELSE 0 
        END
    ) AS total_p1,

    -- P2
    SUM(
        CASE 
            WHEN ' . $agingDueExpr . ' BETWEEN 31 AND 45
            THEN CASE
                WHEN tbp.status_invoice = "partial" THEN GREATEST(
                    tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                    0
                )
                ELSE tbp.invoice_price_nett
            END
            ELSE 0 
        END
    ) AS total_p2,

    -- P3
    SUM(
        CASE 
            WHEN ' . $agingDueExpr . ' BETWEEN 0 AND 30
            THEN CASE
                WHEN tbp.status_invoice = "partial" THEN GREATEST(
                    tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                    0
                )
                ELSE tbp.invoice_price_nett
            END
            ELSE 0 
        END
    ) AS total_p3

FROM tb_billingpayment tbp
JOIN tb_master_bowheer_bilco tmbi 
    ON tbp.id_bowheer = tmbi.id_bowheer
WHERE tbp.status_invoice IN ("open", "partial")
    GROUP BY tmbi.id_bowheer
    ORDER BY total_p1 DESC')
            ->result_array();
        return $data;
    }

    public function getTargetPriorityBowheerFiltered($bowheer, $regional, $city, $priority)
    {
        $agingDueExpr = $this->agingDueExpr;
        $outstandingSql = 'CASE
            WHEN tbp.status_invoice = "partial" THEN GREATEST(
                tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                0
            )
            ELSE tbp.invoice_price_nett
        END';

        $this->db->select("
            tmbi.nama_bowheer,
            SUM($outstandingSql) AS total_all,
            SUM(CASE WHEN $agingDueExpr < 0 THEN $outstandingSql ELSE 0 END) AS total_bjt,
            SUM(CASE WHEN $agingDueExpr > 45 THEN $outstandingSql ELSE 0 END) AS total_p1,
            SUM(CASE WHEN $agingDueExpr BETWEEN 31 AND 45 THEN $outstandingSql ELSE 0 END) AS total_p2,
            SUM(CASE WHEN $agingDueExpr BETWEEN 0 AND 30 THEN $outstandingSql ELSE 0 END) AS total_p3
        ", false);
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer');
        $this->db->where_in('tbp.status_invoice', ['open', 'partial']);

        if (!empty($bowheer)) {
            $this->db->where_in('nama_bowheer', $bowheer);
        }
        if (!empty($regional)) {
            $this->db->where_in('regional_payment', $regional);
        }
        if (!empty($city)) {
            $this->db->where_in('area_payment', $city);
        }
        if (!empty($priority)) {
            $conditions = [];
            foreach ($priority as $p) {
                if ($p == "P1") {
                    $conditions[] = $agingDueExpr . ' > 45';
                } elseif ($p == "P2") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 31 AND 45)';
                } elseif ($p == "P3") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 0 AND 30)';
                } elseif ($p == "BJT") {
                    $conditions[] = $agingDueExpr . ' < 0';
                }
            }
            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        $this->db->group_by('tmbi.id_bowheer');
        $this->db->order_by('total_p1', 'DESC');

        return $this->db->get()->result_array();
    }

    public function getOutstandingSummary($bowheer, $regional, $city, $priority)
    {
        $agingDueExpr = $this->agingDueExpr;
        $outstandingSql = 'CASE
            WHEN tbp.status_invoice = "partial" THEN GREATEST(
                tbp.invoice_price_nett - CAST(COALESCE(NULLIF(tbp.invoice_price_payment, ""), "0") AS DECIMAL(18,2)),
                0
            )
            ELSE tbp.invoice_price_nett
        END';

        $this->db->select("
            SUM($outstandingSql) AS total_all,
            SUM(CASE WHEN $agingDueExpr > 45 THEN $outstandingSql ELSE 0 END) AS total_p1,
            SUM(CASE WHEN $agingDueExpr BETWEEN 31 AND 45 THEN $outstandingSql ELSE 0 END) AS total_p2,
            SUM(CASE WHEN $agingDueExpr BETWEEN 0 AND 30 THEN $outstandingSql ELSE 0 END) AS total_p3,
            SUM(CASE WHEN $agingDueExpr < 0 THEN $outstandingSql ELSE 0 END) AS total_bjt
        ", false);
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer');
        $this->db->where_in('tbp.status_invoice', ['open', 'partial']);

        if (!empty($bowheer))
            $this->db->where_in('nama_bowheer', $bowheer);
        if (!empty($regional))
            $this->db->where_in('regional_payment', $regional);
        if (!empty($city))
            $this->db->where_in('area_payment', $city);
        if (!empty($priority)) {
            $conditions = [];

            foreach ($priority as $p) {
                if ($p == "P1") {
                    $conditions[] = $agingDueExpr . ' > 45';
                } elseif ($p == "P2") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 31 AND 45)';
                } elseif ($p == "P3") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 0 AND 30)';
                } elseif ($p == "BJT") {
                    $conditions[] = $agingDueExpr . ' < 0';
                }
            }

            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        return $this->db->get()->row_array();
    }

    public function getFilteredBillingPayment($bowheer, $regional, $city, $priority, $statusInvoice = 'open')
    {
        $agingInvoiceExpr = $this->agingInvoiceExpr;
        $agingDueExpr = $this->agingDueExpr;
        if ($priority == "P1") {
            $priorityCondition = $agingDueExpr . ' > 45';
        } elseif ($priority == "P2") {
            $priorityCondition = $agingDueExpr . ' BETWEEN 31 AND 45';
        } elseif ($priority == "P3") {
            $priorityCondition = $agingDueExpr . ' BETWEEN 0 AND 30';
        } elseif ($priority == "BJT") {
            $priorityCondition = $agingDueExpr . ' < 0';
        } else {
            $priorityCondition = '1=1'; // Jika tidak ada filter priority, tampilkan semua
        }

        $this->db->select('tbp.*,
    tmbi.*,

    tmbi.jt_invoice,

    -- aging dihitung terhadap tanggal invoice
    ' . $agingInvoiceExpr . ' AS umur_invoice,

    -- aging dihitung terhadap due date
    ' . $agingDueExpr . ' AS umur_due_date,

    -- 🔥 jatuh tempo dinamis
    DATE_ADD(tbp.tgl_create_invoice, INTERVAL tmbi.jt_invoice DAY) AS tgl_jatuh_tempo,

    -- priority
    CASE
        WHEN ' . $agingDueExpr . ' > 45 THEN "P1"
        WHEN ' . $agingDueExpr . ' BETWEEN 31 AND 45 THEN "P2"
        WHEN ' . $agingDueExpr . ' BETWEEN 0 AND 30 THEN "P3"
        ELSE "BJT"
    END AS priority,
    
    CASE 
    WHEN CURDATE() > DATE_ADD(tbp.tgl_create_invoice, INTERVAL tmbi.jt_invoice DAY)
        THEN "OVERDUE"
    WHEN ' . $agingDueExpr . ' >= -7
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
        if (is_array($statusInvoice)) {
            $statusInvoice = array_values(array_filter(array_map('trim', $statusInvoice), function ($value) {
                return $value !== '' && $value !== 'all';
            }));
            if (!empty($statusInvoice)) {
                $this->db->where_in('tbp.status_invoice', $statusInvoice);
            }
        } elseif (!empty($statusInvoice) && $statusInvoice !== 'all') {
            $this->db->where('tbp.status_invoice', $statusInvoice);
        }
        if (!empty($priority)) {

            $conditions = [];

            foreach ($priority as $p) {
                if ($p == "P1") {
                    $conditions[] = $agingDueExpr . ' > 45';
                } elseif ($p == "P2") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 31 AND 45)';
                } elseif ($p == "P3") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 0 AND 30)';
                } elseif ($p == "BJT") {
                    $conditions[] = $agingDueExpr . ' < 0';
                }
            }

            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        $this->db->order_by('umur_due_date', 'DESC');

        $query = $this->db->get();

        // untuk debug query
        log_message('debug', 'Last Query billing: ' . $this->db->last_query());

        return $query->result_array();
    }

    public function getBillingStatusCounts($bowheer, $regional, $city, $priority)
    {
        $agingDueExpr = $this->agingDueExpr;

        $this->db->select('tbp.status_invoice, COUNT(*) AS total_count', false);
        $this->db->from('tb_billingpayment tbp');
        $this->db->join('tb_master_bowheer_bilco tmbi', 'tbp.id_bowheer = tmbi.id_bowheer');

        if (!empty($bowheer)) {
            $this->db->where_in('nama_bowheer', $bowheer);
        }
        if (!empty($regional)) {
            $this->db->where_in('regional_payment', $regional);
        }
        if (!empty($city)) {
            $this->db->where_in('area_payment', $city);
        }
        if (!empty($priority)) {
            $conditions = [];
            foreach ($priority as $p) {
                if ($p == "P1") {
                    $conditions[] = $agingDueExpr . ' > 45';
                } elseif ($p == "P2") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 31 AND 45)';
                } elseif ($p == "P3") {
                    $conditions[] = '(' . $agingDueExpr . ' BETWEEN 0 AND 30)';
                } elseif ($p == "BJT") {
                    $conditions[] = $agingDueExpr . ' < 0';
                }
            }
            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        $this->db->group_by('tbp.status_invoice');
        $rows = $this->db->get()->result_array();

        $counts = [
            'open' => 0,
            'partial' => 0,
            'paid' => 0,
            'reject' => 0,
            'all' => 0
        ];

        foreach ($rows as $row) {
            $status = strtolower((string) ($row['status_invoice'] ?? ''));
            $total = (int) ($row['total_count'] ?? 0);
            if (array_key_exists($status, $counts)) {
                $counts[$status] = $total;
                $counts['all'] += $total;
            }
        }

        return $counts;
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

