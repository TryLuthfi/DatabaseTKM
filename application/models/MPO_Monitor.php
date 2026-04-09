<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MPO_Monitor extends CI_Model
{
    public function getPOsSummary()
    {
        $sql = "SELECT
            p.id_po,
            p.po_number,
            p.po_date,
            p.id_bowheer,
            COALESCE(b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            p.total_value,
            COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value) AS current_release_value,
            COALESCE((SELECT SUM(ti.invoice_amount) FROM tb_po_term_invoice ti JOIN tb_po_term t ON ti.id_term = t.id_term WHERE t.id_po = p.id_po), 0) AS total_invoiced
        FROM tb_po p
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        ORDER BY p.po_date DESC";

        return $this->db->query($sql)->result_array();
    }

    public function getPOSummaryByBowheer()
    {
        $sql = "SELECT
            COALESCE(b.id_bowheer, 0) AS id_bowheer,
            COALESCE(b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            COUNT(p.id_po) AS total_po,
            SUM(COALESCE((SELECT release_value FROM tb_po_amend a WHERE a.id_po = p.id_po ORDER BY a.amend_no DESC LIMIT 1), p.total_value)) AS current_release_value,
            SUM(COALESCE((SELECT SUM(ti.invoice_amount) FROM tb_po_term_invoice ti JOIN tb_po_term t ON ti.id_term = t.id_term WHERE t.id_po = p.id_po), 0)) AS total_invoiced
        FROM tb_po p
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        GROUP BY COALESCE(b.id_bowheer, 0), COALESCE(b.nama_bowheer, 'Tanpa Bowheer')
        ORDER BY current_release_value DESC, nama_bowheer ASC";

        $rows = $this->db->query($sql)->result_array();

        foreach ($rows as &$row) {
            $release = (float) $row['current_release_value'];
            $invoiced = (float) $row['total_invoiced'];
            $remaining = $release - $invoiced;

            $row['remaining'] = $remaining > 0 ? $remaining : 0;
        }
        unset($row);

        return $rows;
    }

    public function getBowheerTermBreakdown()
    {
        $sql = "SELECT
            COALESCE(b.id_bowheer, 0) AS id_bowheer,
            COALESCE(b.nama_bowheer, 'Tanpa Bowheer') AS nama_bowheer,
            t.term_index,
            COUNT(DISTINCT p.id_po) AS total_po,
            SUM(COALESCE(t.value, 0)) AS term_value,
            SUM(COALESCE(ti.invoice_amount, 0)) AS invoiced_amount
        FROM tb_po p
        LEFT JOIN tb_master_bowheer_bilco b ON p.id_bowheer = b.id_bowheer
        LEFT JOIN tb_po_term t ON p.id_po = t.id_po
            AND (
                t.id_amend = (
                    SELECT a.id_amend
                    FROM tb_po_amend a
                    WHERE a.id_po = p.id_po
                    ORDER BY a.amend_no DESC
                    LIMIT 1
                )
                OR (
                    t.id_amend IS NULL
                    AND NOT EXISTS (
                        SELECT 1
                        FROM tb_po_amend a2
                        WHERE a2.id_po = p.id_po
                    )
                )
            )
        LEFT JOIN tb_po_term_invoice ti ON t.id_term = ti.id_term
        GROUP BY COALESCE(b.id_bowheer, 0), COALESCE(b.nama_bowheer, 'Tanpa Bowheer'), t.term_index
        ORDER BY nama_bowheer ASC, t.term_index ASC";

        $rows = $this->db->query($sql)->result_array();
        $result = [];

        foreach ($rows as $row) {
            $bowheerKey = (string) $row['id_bowheer'];

            if (!isset($result[$bowheerKey])) {
                $result[$bowheerKey] = [
                    'id_bowheer' => $row['id_bowheer'],
                    'nama_bowheer' => $row['nama_bowheer'],
                    'total_po' => (int) $row['total_po'],
                    'terms' => []
                ];
            }

            if ($row['term_index'] === null) {
                continue;
            }

            $termValue = (float) $row['term_value'];
            $invoiced = (float) $row['invoiced_amount'];
            $remaining = $termValue - $invoiced;

            $result[$bowheerKey]['terms'][] = [
                'term_index' => (int) $row['term_index'],
                'term_value' => $termValue,
                'invoiced_amount' => $invoiced,
                'remaining' => $remaining > 0 ? $remaining : 0
            ];
        }

        return array_values($result);
    }

    public function getPOByNumber($po_number)
    {
        return $this->db->get_where('tb_po', ['po_number' => $po_number])->row_array();
    }

    public function getPOById($id_po)
    {
        return $this->db->get_where('tb_po', ['id_po' => (int) $id_po])->row_array();
    }

    public function getPOTerms($id_po)
    {
        $latestAmend = $this->db
            ->select('id_amend')
            ->from('tb_po_amend')
            ->where('id_po', (int) $id_po)
            ->order_by('amend_no', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();

        $this->db->select('t.*, COALESCE(SUM(ti.invoice_amount),0) AS invoiced_amount', false);
        $this->db->from('tb_po_term t');
        $this->db->join('tb_po_term_invoice ti', 't.id_term = ti.id_term', 'left');
        $this->db->where('t.id_po', (int) $id_po);

        if (!empty($latestAmend['id_amend'])) {
            $this->db->where('t.id_amend', (int) $latestAmend['id_amend']);
        } else {
            $this->db->where('t.id_amend IS NULL', null, false);
        }

        $this->db->group_by('t.id_term');
        $this->db->order_by('t.term_index', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Auto-allocate invoices (from tb_billingpayment) for a PO to its terms.
     * It will create rows in tb_po_term_invoice splitting invoices across terms sequentially.
     * Returns summary array with counts and remaining totals.
     */
    public function allocateInvoicesToTermsByPoNumber($po_number)
    {
        $this->db->trans_begin();

        $po = $this->getPOByNumber($po_number);
        if (!$po) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'PO not found'];
        }

        // load terms with already allocated sum
        $terms = $this->db->select('t.*, COALESCE(SUM(ti.invoice_amount),0) AS allocated_sum', false)
            ->from('tb_po_term t')
            ->join('tb_po_term_invoice ti', 't.id_term = ti.id_term', 'left')
            ->where('t.id_po', $po['id_po'])
            ->group_by('t.id_term')
            ->order_by('t.term_index', 'ASC')
            ->get()
            ->result_array();

        if (empty($terms)) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'No terms defined for PO'];
        }

        // Prepare term remaining array
        $termRemaining = [];
        foreach ($terms as $t) {
            $remaining = (float) $t['value'] - (float) $t['allocated_sum'];
            if ($remaining < 0) $remaining = 0.0;
            $termRemaining[] = [
                'id_term' => (int) $t['id_term'],
                'remaining' => $remaining,
                'term_index' => (int) $t['term_index']
            ];
        }

        // Fetch invoices for this PO and how much of each is already allocated
        $invoices = $this->db->select('b.id_billing, COALESCE(b.invoice_price_nett,0) AS invoice_price_nett, COALESCE(SUM(ti.invoice_amount),0) AS allocated_amount', false)
            ->from('tb_billingpayment b')
            ->join('tb_po_term_invoice ti', 'b.id_billing = ti.id_billing', 'left')
            ->where('b.po_number', $po_number)
            ->group_by('b.id_billing')
            ->order_by('b.tgl_submit_invoice', 'ASC')
            ->get()
            ->result_array();

        $inserted = 0;

        // index pointer for terms
        $termPtr = 0;
        $termCount = count($termRemaining);

        foreach ($invoices as $inv) {
            $invId = (int) $inv['id_billing'];
            $invTotal = (float) $inv['invoice_price_nett'];
            $invAllocated = (float) $inv['allocated_amount'];
            $invRemain = $invTotal - $invAllocated;
            if ($invRemain <= 0) continue; // already fully allocated

            // move termPtr to the next term with remaining > 0
            while ($termPtr < $termCount && $termRemaining[$termPtr]['remaining'] <= 0.000001) {
                $termPtr++;
            }

            while ($invRemain > 0.000001 && $termPtr < $termCount) {
                $term = &$termRemaining[$termPtr];
                $alloc = min($invRemain, $term['remaining']);
                if ($alloc > 0.000001) {
                    $this->db->insert('tb_po_term_invoice', [
                        'id_term' => $term['id_term'],
                        'id_billing' => $invId,
                        'invoice_amount' => $alloc,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $inserted++;
                    $invRemain -= $alloc;
                    $term['remaining'] -= $alloc;
                }

                if ($term['remaining'] <= 0.000001) {
                    $termPtr++;
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Database error while allocating'];
        }

        $this->db->trans_commit();

        // compute post-allocation totals
        $totalInvoiced = (float) $this->db->select('COALESCE(SUM(invoice_amount),0) AS s')->get_where('tb_po_term_invoice', ['id_term IN (SELECT id_term FROM tb_po_term WHERE id_po = ' . (int) $po['id_po'] . ') ' => null])->row()->s;

        return [
            'status' => true,
            'message' => 'Allocation completed',
            'allocations_inserted' => $inserted,
            'po_id' => $po['id_po']
        ];
    }
}
