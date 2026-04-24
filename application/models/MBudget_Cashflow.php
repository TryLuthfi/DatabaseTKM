<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_Cashflow extends CI_Model
{
    public function getBowheers()
    {
        return $this->db
            ->order_by('nama_bowheer', 'ASC')
            ->get('tb_master_bowheer')
            ->result_array();
    }

    public function getItems()
    {
        return $this->db
            ->order_by('item_name', 'ASC')
            ->get_where('tb_budget_items', ['is_active' => 1])
            ->result_array();
    }

    public function getHeaders($year, $month = 0)
    {
        $this->db->select('
            h.*,
            b.nama_bowheer,
            COUNT(d.id_cashflow_detail) AS total_items,
            COALESCE(SUM(CASE WHEN d.direction = "DEBIT" THEN d.nominal ELSE 0 END), 0) AS total_debit,
            COALESCE(SUM(CASE WHEN d.direction = "KREDIT" THEN d.nominal ELSE 0 END), 0) AS total_kredit
        ');
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if ((int) $month > 0) {
            $this->db->where('MONTH(h.tanggal_cashflow)', (int) $month);
        }
        $this->db->group_by('h.id_cashflow_header');
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        $this->db->order_by('h.nomor_tec', 'DESC');

        return $this->db->get()->result_array();
    }

    public function getHeaderDetails($headerId)
    {
        return $this->db
            ->select('d.*, i.item_code, i.item_name')
            ->from('tb_budget_cashflow_detail d')
            ->join('tb_budget_items i', 'i.id_budget_item = d.id_budget_item')
            ->where('d.id_cashflow_header', (int) $headerId)
            ->order_by('d.id_cashflow_detail', 'ASC')
            ->get()
            ->result_array();
    }

    public function getHeaderById($headerId)
    {
        return $this->db
            ->select('h.*, b.nama_bowheer')
            ->from('tb_budget_cashflow_header h')
            ->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left')
            ->where('h.id_cashflow_header', (int) $headerId)
            ->get()
            ->row_array();
    }

    public function isDuplicateHeader($nomorTec, $tanggalCashflow, $projectName, $excludeHeaderId = 0)
    {
        $this->db->from('tb_budget_cashflow_header');
        $this->db->where('nomor_tec', trim($nomorTec));
        $this->db->where('tanggal_cashflow', $tanggalCashflow);
        $this->db->where('project_name', trim($projectName));

        if ((int) $excludeHeaderId > 0) {
            $this->db->where('id_cashflow_header !=', (int) $excludeHeaderId);
        }

        return $this->db->count_all_results() > 0;
    }

    public function getBudgetSnapshotByItems($year, $month, array $itemIds)
    {
        if (empty($itemIds)) {
            return [];
        }

        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));

        $this->db->select("
            i.id_budget_item,
            i.item_code,
            i.item_name,
            COALESCE(a.annual_budget, 0) AS annual_budget,
            COALESCE(m.monthly_budget, 0) AS monthly_budget,
            COALESCE(r.real_annual, 0) AS real_annual,
            COALESCE(rm.real_monthly, 0) AS real_monthly
        ");
        $this->db->from('tb_budget_items i');
        $this->db->join(
            'tb_budget_annual a',
            'a.id_budget_item = i.id_budget_item AND a.budget_year = ' . (int) $year,
            'left',
            false
        );
        $this->db->join(
            'tb_budget_monthly m',
            'm.id_budget_annual = a.id_budget_annual AND m.month_no = ' . (int) $month,
            'left',
            false
        );
        $this->db->join(
            "(SELECT d.id_budget_item, SUM(CASE WHEN d.direction = 'DEBIT' THEN d.nominal ELSE -d.nominal END) AS real_annual
              FROM tb_budget_cashflow_detail d
              JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
              WHERE YEAR(h.tanggal_cashflow) = " . (int) $year . "
              GROUP BY d.id_budget_item) r",
            'r.id_budget_item = i.id_budget_item',
            'left',
            false
        );
        $this->db->join(
            "(SELECT d.id_budget_item, SUM(CASE WHEN d.direction = 'DEBIT' THEN d.nominal ELSE -d.nominal END) AS real_monthly
              FROM tb_budget_cashflow_detail d
              JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
              WHERE YEAR(h.tanggal_cashflow) = " . (int) $year . "
                AND MONTH(h.tanggal_cashflow) = " . (int) $month . "
              GROUP BY d.id_budget_item) rm",
            'rm.id_budget_item = i.id_budget_item',
            'left',
            false
        );
        $this->db->where_in('i.id_budget_item', $itemIds);

        $rows = $this->db->get()->result_array();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['id_budget_item']] = $row;
        }

        return $mapped;
    }

    public function saveManualCashflow(array $header, array $details)
    {
        $this->db->trans_start();

        $this->db->insert('tb_budget_cashflow_header', $header);
        $headerId = (int) $this->db->insert_id();

        foreach ($details as $detail) {
            $detail['id_cashflow_header'] = $headerId;
            $this->db->insert('tb_budget_cashflow_detail', $detail);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function updateManualCashflow($headerId, array $header, array $details)
    {
        $this->db->trans_start();

        $this->db
            ->where('id_cashflow_header', (int) $headerId)
            ->update('tb_budget_cashflow_header', $header);

        $this->db->delete('tb_budget_cashflow_detail', ['id_cashflow_header' => (int) $headerId]);

        foreach ($details as $detail) {
            $detail['id_cashflow_header'] = (int) $headerId;
            $this->db->insert('tb_budget_cashflow_detail', $detail);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function deleteHeader($headerId)
    {
        $this->db->delete('tb_budget_cashflow_header', ['id_cashflow_header' => (int) $headerId]);
        return $this->db->affected_rows() > 0;
    }

    public function logImport($fileName, $totalRows, $successRows, $failedRows, $notes, $userId)
    {
        $this->db->insert('tb_budget_import_log', [
            'file_name' => $fileName,
            'total_rows' => (int) $totalRows,
            'success_rows' => (int) $successRows,
            'failed_rows' => (int) $failedRows,
            'notes' => $notes,
            'uploaded_by' => (int) $userId,
        ]);
    }

    public function findItemByCode($itemCode)
    {
        return $this->db
            ->get_where('tb_budget_items', ['item_code' => trim($itemCode)])
            ->row_array();
    }
}
