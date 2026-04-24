<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MBudget_Report extends CI_Model
{
    private function applyMonthFilter($months)
    {
        if (!empty($months)) {
            $months = array_map('intval', $months);
            $this->db->where_in('month_no', $months);
        }
    }

    private function applyCashflowMonthFilter($months)
    {
        if (!empty($months)) {
            $months = array_map('intval', $months);
            $this->db->where_in('MONTH(h.tanggal_cashflow)', $months, false);
        }
    }

    public function getAvailableYears()
    {
        $years = $this->db->select('budget_year')->distinct()->order_by('budget_year', 'DESC')->get('tb_budget_annual')->result_array();
        return array_map(static function ($row) {
            return (int) $row['budget_year'];
        }, $years);
    }

    public function getAnnualComparison($year)
    {
        $sql = "
            SELECT
                i.id_budget_item,
                i.item_code,
                i.item_name,
                COALESCE(a.annual_budget, 0) AS annual_budget,
                COALESCE(r.total_realisasi, 0) AS total_realisasi,
                COALESCE(a.annual_budget, 0) - COALESCE(r.total_realisasi, 0) AS sisa
            FROM tb_budget_items i
            LEFT JOIN tb_budget_annual a
                ON a.id_budget_item = i.id_budget_item
                AND a.budget_year = ?
            LEFT JOIN (
                SELECT
                    d.id_budget_item,
                    SUM(d.nominal) AS total_realisasi
                FROM tb_budget_cashflow_detail d
                JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
                WHERE YEAR(h.tanggal_cashflow) = ?
                GROUP BY d.id_budget_item
            ) r ON r.id_budget_item = i.id_budget_item
            WHERE i.is_active = 1
            ORDER BY i.item_name ASC
        ";

        return $this->db->query($sql, [$year, $year])->result_array();
    }

    public function getMonthlyMatrix($year, array $months)
    {
        $items = $this->db
            ->select('id_budget_item, item_code, item_name')
            ->from('tb_budget_items')
            ->where('is_active', 1)
            ->order_by('item_name', 'ASC')
            ->get()
            ->result_array();

        $this->db->select('a.id_budget_item, m.month_no, m.monthly_budget');
        $this->db->from('tb_budget_monthly m');
        $this->db->join('tb_budget_annual a', 'a.id_budget_annual = m.id_budget_annual');
        $this->db->where('a.budget_year', (int) $year);
        $this->applyMonthFilter($months);
        $budgetRows = $this->db->get()->result_array();

        $this->db->select("
            d.id_budget_item,
            MONTH(h.tanggal_cashflow) AS month_no,
            SUM(d.nominal) AS realisasi
        ", false);
        $this->db->from('tb_budget_cashflow_detail d');
        $this->db->join('tb_budget_cashflow_header h', 'h.id_cashflow_header = d.id_cashflow_header');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by(['d.id_budget_item', 'MONTH(h.tanggal_cashflow)']);
        $realRows = $this->db->get()->result_array();

        $budgetMap = [];
        foreach ($budgetRows as $row) {
            $budgetMap[(int) $row['id_budget_item']][(int) $row['month_no']] = (float) $row['monthly_budget'];
        }

        $realMap = [];
        foreach ($realRows as $row) {
            $realMap[(int) $row['id_budget_item']][(int) $row['month_no']] = (float) $row['realisasi'];
        }

        $result = [];
        foreach ($items as $item) {
            $itemId = (int) $item['id_budget_item'];
            $result[$itemId] = [
                'item_code' => $item['item_code'],
                'item_name' => $item['item_name'],
                'months' => [],
            ];

            foreach ($months as $monthNo) {
                $budget = $budgetMap[$itemId][$monthNo] ?? 0;
                $real = $realMap[$itemId][$monthNo] ?? 0;
                $result[$itemId]['months'][$monthNo] = [
                    'budget' => $budget,
                    'realisasi' => $real,
                    'sisa' => $budget - $real,
                ];
            }
        }

        return $result;
    }

    public function getDebitKreditSummary($year, array $months)
    {
        $this->db->select("
            d.direction,
            SUM(d.nominal) AS total_nominal
        ");
        $this->db->from('tb_budget_cashflow_detail d');
        $this->db->join('tb_budget_cashflow_header h', 'h.id_cashflow_header = d.id_cashflow_header');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by('d.direction');

        return $this->db->get()->result_array();
    }

    public function getDebitKreditComparison($year, array $months)
    {
        $budgetMap = [
            'DEBIT' => 0,
            'KREDIT' => 0,
        ];

        $this->db->select("
            i.default_direction,
            SUM(COALESCE(m.monthly_budget, 0)) AS total_budget
        ");
        $this->db->from('tb_budget_items i');
        $this->db->join('tb_budget_annual a', 'a.id_budget_item = i.id_budget_item AND a.budget_year = ' . (int) $year, 'left', false);
        $this->db->join('tb_budget_monthly m', 'm.id_budget_annual = a.id_budget_annual', 'left');
        if (!empty($months)) {
            $this->db->where_in('m.month_no', array_map('intval', $months));
        }
        $this->db->where('i.is_active', 1);
        $this->db->group_by('i.default_direction');
        $budgetRows = $this->db->get()->result_array();

        foreach ($budgetRows as $row) {
            $direction = strtoupper((string) ($row['default_direction'] ?? ''));
            if (isset($budgetMap[$direction])) {
                $budgetMap[$direction] = (float) $row['total_budget'];
            }
        }

        $realMap = [
            'DEBIT' => 0,
            'KREDIT' => 0,
        ];
        foreach ($this->getDebitKreditSummary($year, $months) as $row) {
            $direction = strtoupper((string) ($row['direction'] ?? ''));
            if (isset($realMap[$direction])) {
                $realMap[$direction] = (float) $row['total_nominal'];
            }
        }

        return [
            [
                'label' => 'Debit',
                'budget' => $budgetMap['DEBIT'],
                'realisasi' => $realMap['DEBIT'],
            ],
            [
                'label' => 'Kredit',
                'budget' => $budgetMap['KREDIT'],
                'realisasi' => $realMap['KREDIT'],
            ],
        ];
    }

    public function getItemDetails($year, array $months)
    {
        $sql = "
            SELECT
                i.id_budget_item,
                i.item_code,
                i.item_name,
                COUNT(DISTINCT h.id_cashflow_header) AS total_tec,
                COUNT(DISTINCT h.project_name) AS total_project,
                COALESCE(SUM(d.nominal), 0) AS total_realisasi
            FROM tb_budget_items i
            LEFT JOIN tb_budget_cashflow_detail d ON d.id_budget_item = i.id_budget_item
            LEFT JOIN tb_budget_cashflow_header h ON h.id_cashflow_header = d.id_cashflow_header
                AND YEAR(h.tanggal_cashflow) = ?
            WHERE i.is_active = 1
        ";

        if (!empty($months)) {
            $monthList = implode(',', array_map('intval', $months));
            $sql .= " AND (h.id_cashflow_header IS NULL OR MONTH(h.tanggal_cashflow) IN ({$monthList})) ";
        }

        $sql .= " GROUP BY i.id_budget_item ORDER BY i.item_name ASC ";

        return $this->db->query($sql, [$year])->result_array();
    }

    public function getTecDetails($year, array $months)
    {
        $this->db->select("
            h.nomor_tec,
            h.tanggal_cashflow,
            b.nama_bowheer,
            h.project_name,
            h.pic_project,
            h.regional,
            h.kota,
            COALESCE(SUM(d.nominal), 0) AS total_realisasi
        ");
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by('h.id_cashflow_header');
        $this->db->order_by('h.tanggal_cashflow', 'DESC');

        return $this->db->get()->result_array();
    }

    public function getProjectDetails($year, array $months)
    {
        $this->db->select("
            h.project_name,
            COUNT(DISTINCT h.nomor_tec) AS total_tec,
            COUNT(DISTINCT h.pic_project) AS total_pic,
            COALESCE(SUM(d.nominal), 0) AS total_realisasi
        ");
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by('h.project_name');
        $this->db->order_by('total_realisasi', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getPicDetails($year, array $months)
    {
        $this->db->select("
            h.pic_project,
            COUNT(DISTINCT h.project_name) AS total_project,
            COUNT(DISTINCT h.nomor_tec) AS total_tec,
            COALESCE(SUM(d.nominal), 0) AS total_realisasi
        ");
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by('h.pic_project');
        $this->db->order_by('total_realisasi', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getAreaDetails($year, array $months)
    {
        $this->db->select("
            h.regional,
            h.kota,
            COUNT(DISTINCT h.project_name) AS total_project,
            COUNT(DISTINCT h.nomor_tec) AS total_tec,
            COALESCE(SUM(d.nominal), 0) AS total_realisasi
        ");
        $this->db->from('tb_budget_cashflow_header h');
        $this->db->join('tb_budget_cashflow_detail d', 'd.id_cashflow_header = h.id_cashflow_header', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
        $this->db->group_by(['h.regional', 'h.kota']);
        $this->db->order_by('h.regional', 'ASC');
        $this->db->order_by('h.kota', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getSummaryCards($year, array $months)
    {
        $annualRows = $this->getAnnualComparison($year);
        $monthlyMatrix = $this->getMonthlyMatrix($year, $months);
        $debitKredit = $this->getDebitKreditSummary($year, $months);
        $tecRows = $this->getTecDetails($year, $months);
        $projectRows = $this->getProjectDetails($year, $months);

        $totalAnnualBudget = 0;
        $totalAnnualRealisasi = 0;
        $annualOverbudgetItems = 0;
        foreach ($annualRows as $row) {
            $totalAnnualBudget += (float) $row['annual_budget'];
            $totalAnnualRealisasi += (float) $row['total_realisasi'];
            if ((float) $row['sisa'] < 0) {
                $annualOverbudgetItems++;
            }
        }

        $monthlyOverbudgetCells = 0;
        foreach ($monthlyMatrix as $row) {
            foreach ($row['months'] as $monthData) {
                if ((float) $monthData['sisa'] < 0) {
                    $monthlyOverbudgetCells++;
                }
            }
        }

        $totalDebit = 0;
        $totalKredit = 0;
        foreach ($debitKredit as $row) {
            if (($row['direction'] ?? '') === 'DEBIT') {
                $totalDebit = (float) $row['total_nominal'];
            } elseif (($row['direction'] ?? '') === 'KREDIT') {
                $totalKredit = (float) $row['total_nominal'];
            }
        }

        return [
            'total_annual_budget' => $totalAnnualBudget,
            'total_annual_realisasi' => $totalAnnualRealisasi,
            'total_annual_sisa' => $totalAnnualBudget - $totalAnnualRealisasi,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'total_tec' => count($tecRows),
            'total_project' => count($projectRows),
            'annual_overbudget_items' => $annualOverbudgetItems,
            'monthly_overbudget_cells' => $monthlyOverbudgetCells,
        ];
    }

    public function getDrilldownRows($type, $year, array $months, array $filters = [])
    {
        switch ($type) {
            case 'item':
                return $this->getTransactionsByItem($year, $months, (int) ($filters['id_budget_item'] ?? 0), $filters['month_no'] ?? null);
            case 'project':
                return $this->getTransactionsByProject($year, $months, (string) ($filters['project_name'] ?? ''));
            case 'pic':
                return $this->getTransactionsByPic($year, $months, (string) ($filters['pic_project'] ?? ''));
            case 'area':
                return $this->getTransactionsByArea($year, $months, (string) ($filters['regional'] ?? ''), (string) ($filters['kota'] ?? ''));
            case 'tec':
                return $this->getTransactionsByTec($year, $months, (string) ($filters['nomor_tec'] ?? ''));
            default:
                return [];
        }
    }

    private function getTransactionsBase($year, array $months)
    {
        $this->db->select("
            h.nomor_tec,
            h.tanggal_cashflow,
            b.nama_bowheer,
            h.project_name,
            h.pic_project,
            h.regional,
            h.kota,
            i.item_code,
            i.item_name,
            d.direction,
            d.qty,
            d.unit_price,
            d.nominal,
            d.remarks_item
        ");
        $this->db->from('tb_budget_cashflow_detail d');
        $this->db->join('tb_budget_cashflow_header h', 'h.id_cashflow_header = d.id_cashflow_header');
        $this->db->join('tb_budget_items i', 'i.id_budget_item = d.id_budget_item');
        $this->db->join('tb_master_bowheer b', 'b.id_bowheer = h.id_bowheer', 'left');
        $this->db->where('YEAR(h.tanggal_cashflow)', (int) $year);
        if (!empty($months)) {
            $this->db->where_in('MONTH(h.tanggal_cashflow)', array_map('intval', $months), false);
        }
    }

    private function getTransactionsByItem($year, array $months, $itemId, $monthNo = null)
    {
        $this->getTransactionsBase($year, $months);
        $this->db->where('d.id_budget_item', (int) $itemId);
        if ($monthNo !== null && (int) $monthNo > 0) {
            $this->db->where('MONTH(h.tanggal_cashflow)', (int) $monthNo, false);
        }
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        return $this->db->get()->result_array();
    }

    private function getTransactionsByProject($year, array $months, $projectName)
    {
        $this->getTransactionsBase($year, $months);
        $this->db->where('h.project_name', $projectName);
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        return $this->db->get()->result_array();
    }

    private function getTransactionsByPic($year, array $months, $picProject)
    {
        $this->getTransactionsBase($year, $months);
        if ($picProject === '') {
            $this->db->group_start()
                ->where('h.pic_project IS NULL', null, false)
                ->or_where('h.pic_project', '')
                ->group_end();
        } else {
            $this->db->where('h.pic_project', $picProject);
        }
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        return $this->db->get()->result_array();
    }

    private function getTransactionsByArea($year, array $months, $regional, $kota)
    {
        $this->getTransactionsBase($year, $months);
        if ($regional === '') {
            $this->db->group_start()
                ->where('h.regional IS NULL', null, false)
                ->or_where('h.regional', '')
                ->group_end();
        } else {
            $this->db->where('h.regional', $regional);
        }
        if ($kota === '') {
            $this->db->group_start()
                ->where('h.kota IS NULL', null, false)
                ->or_where('h.kota', '')
                ->group_end();
        } else {
            $this->db->where('h.kota', $kota);
        }
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        return $this->db->get()->result_array();
    }

    private function getTransactionsByTec($year, array $months, $nomorTec)
    {
        $this->getTransactionsBase($year, $months);
        $this->db->where('h.nomor_tec', $nomorTec);
        $this->db->order_by('h.tanggal_cashflow', 'DESC');
        return $this->db->get()->result_array();
    }
}
