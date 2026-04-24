<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_MasterBudgetYears extends CI_Model
{
    public function getItems()
    {
        return $this->db
            ->order_by('item_name', 'ASC')
            ->get_where('tb_budget_items', ['is_active' => 1])
            ->result_array();
    }

    public function getAvailableYears()
    {
        $years = $this->db
            ->select('budget_year')
            ->distinct()
            ->order_by('budget_year', 'DESC')
            ->get('tb_budget_annual')
            ->result_array();

        return array_map(static function ($row) {
            return (int) $row['budget_year'];
        }, $years);
    }

    public function getBudgetRows($year)
    {
        $this->db->select('
            a.id_budget_annual,
            a.budget_year,
            a.annual_budget,
            a.notes,
            i.id_budget_item,
            i.item_code,
            i.item_name,
            i.item_category,
            i.item_group,
            COALESCE(SUM(m.monthly_budget), 0) AS total_monthly
        ');
        $this->db->from('tb_budget_annual a');
        $this->db->join('tb_budget_items i', 'i.id_budget_item = a.id_budget_item');
        $this->db->join('tb_budget_monthly m', 'm.id_budget_annual = a.id_budget_annual', 'left');
        $this->db->where('a.budget_year', (int) $year);
        $this->db->group_by('a.id_budget_annual');
        $this->db->order_by('i.item_name', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getBudgetById($id)
    {
        return $this->db
            ->select('a.*, i.item_name, i.item_code')
            ->from('tb_budget_annual a')
            ->join('tb_budget_items i', 'i.id_budget_item = a.id_budget_item')
            ->where('a.id_budget_annual', (int) $id)
            ->get()
            ->row_array();
    }

    public function getMonthlyRows($annualId)
    {
        $rows = $this->db
            ->order_by('month_no', 'ASC')
            ->get_where('tb_budget_monthly', ['id_budget_annual' => (int) $annualId])
            ->result_array();

        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['month_no']] = (float) $row['monthly_budget'];
        }

        return $mapped;
    }

    public function saveBudget(array $payload)
    {
        $annualId = (int) ($payload['id_budget_annual'] ?? 0);
        $itemId = (int) ($payload['id_budget_item'] ?? 0);
        $year = (int) ($payload['budget_year'] ?? 0);
        $annualBudget = (float) ($payload['annual_budget'] ?? 0);
        $notes = trim((string) ($payload['notes'] ?? ''));
        $months = $payload['monthly_budget'] ?? [];

        $this->db->trans_start();

        $annualData = [
            'id_budget_item' => $itemId,
            'budget_year' => $year,
            'annual_budget' => $annualBudget,
            'notes' => $notes,
        ];

        if ($annualId > 0) {
            $this->db->where('id_budget_annual', $annualId)->update('tb_budget_annual', $annualData);
        } else {
            $existing = $this->db
                ->get_where('tb_budget_annual', [
                    'id_budget_item' => $itemId,
                    'budget_year' => $year,
                ])
                ->row_array();

            if ($existing) {
                $annualId = (int) $existing['id_budget_annual'];
                $this->db->where('id_budget_annual', $annualId)->update('tb_budget_annual', $annualData);
            } else {
                $this->db->insert('tb_budget_annual', $annualData);
                $annualId = (int) $this->db->insert_id();
            }
        }

        for ($month = 1; $month <= 12; $month++) {
            $value = isset($months[$month]) ? (float) $months[$month] : 0;
            $existingMonthly = $this->db
                ->get_where('tb_budget_monthly', [
                    'id_budget_annual' => $annualId,
                    'month_no' => $month,
                ])
                ->row_array();

            if ($existingMonthly) {
                $this->db
                    ->where('id_budget_monthly', (int) $existingMonthly['id_budget_monthly'])
                    ->update('tb_budget_monthly', ['monthly_budget' => $value]);
            } else {
                $this->db->insert('tb_budget_monthly', [
                    'id_budget_annual' => $annualId,
                    'month_no' => $month,
                    'monthly_budget' => $value,
                ]);
            }
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function deleteBudget($id)
    {
        $this->db->delete('tb_budget_annual', ['id_budget_annual' => (int) $id]);
        return $this->db->affected_rows() > 0;
    }
}
