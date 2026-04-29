<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MBillingPayment');
        $this->load->model('MBudget_Report');
        $this->load->model('MBatch_Approval_MyRep');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
        $data['title'] = 'Dashboard';
        $data['dashboardYear'] = (int) date('Y');
        $data['invoiceTarget'] = 110000000;
        $data['bilcoSummary'] = $this->buildBilcoSummary();
        $data['budgetSummary'] = $this->buildBudgetSummary((int) date('Y'));
        $data['emrSummary'] = $this->buildEmrSummary();
        $data['portfolioSummary'] = $this->buildPortfolioSummary($data['invoiceTarget'], $data['bilcoSummary'], $data['budgetSummary'], $data['emrSummary']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Dashboard/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    private function buildBilcoSummary()
    {
        $summary = $this->MBillingPayment->getOutstandingSummary([], [], [], []);
        $totalInvoice = (float) ($summary['total_all'] ?? 0);
        $p1 = (float) ($summary['total_p1'] ?? 0);
        $p2 = (float) ($summary['total_p2'] ?? 0);
        $p3 = (float) ($summary['total_p3'] ?? 0);
        $bjt = (float) ($summary['total_bjt'] ?? 0);

        return [
            'total_invoice' => $totalInvoice,
            'p1' => $p1,
            'p2' => $p2,
            'p3' => $p3,
            'bjt' => $bjt,
            'urgent_total' => $p1 + $p2,
            'healthy_total' => $p3 + $bjt,
        ];
    }

    private function buildBudgetSummary($year)
    {
        $summary = $this->MBudget_Report->getSummaryCards($year, []);

        return [
            'annual_budget' => (float) ($summary['total_annual_budget'] ?? 0),
            'realisasi' => (float) ($summary['total_annual_realisasi'] ?? 0),
            'sisa' => (float) ($summary['total_annual_sisa'] ?? 0),
            'total_project' => (int) ($summary['total_project'] ?? 0),
            'total_tec' => (int) ($summary['total_tec'] ?? 0),
            'overbudget_items' => (int) ($summary['annual_overbudget_items'] ?? 0),
        ];
    }

    private function buildEmrSummary()
    {
        if (!$this->MBatch_Approval_MyRep->batchTablesReady()) {
            return [
                'clusters' => 0,
                'nominal_emr' => 0,
                'nominal_release' => 0,
                'hp_donasi' => 0,
                'waiting' => 0,
                'released' => 0,
            ];
        }

        $rows = $this->MBatch_Approval_MyRep->getBatchRows('', '');
        $nominalEmr = 0;
        $nominalRelease = 0;
        $hpDonasi = 0;
        $waiting = 0;
        $released = 0;

        foreach ($rows as $row) {
            $nominalEmr += (float) ($row['nominal_nego_emr'] ?? 0);
            $nominalRelease += (float) ($row['nominal_release_finance'] ?? 0);
            $hpDonasi += (float) ($row['hp_donasi'] ?? 0);

            $status = strtoupper(trim((string) ($row['staging_status'] ?? $row['status_current'] ?? '')));
            if (in_array($status, ['WAITING HO', 'WAITING MYREP', 'WAITING FINANCE'], true)) {
                $waiting++;
            }
            if (in_array($status, ['RELEASED', 'DONE BATCH APPROVAL'], true)) {
                $released++;
            }
        }

        return [
            'clusters' => count($rows),
            'nominal_emr' => $nominalEmr,
            'nominal_release' => $nominalRelease,
            'hp_donasi' => $hpDonasi,
            'waiting' => $waiting,
            'released' => $released,
        ];
    }

    private function buildPortfolioSummary($invoiceTarget, array $bilcoSummary, array $budgetSummary, array $emrSummary)
    {
        $invoiceActual = (float) ($bilcoSummary['total_invoice'] ?? 0);
        $progress = $invoiceTarget > 0 ? min(($invoiceActual / $invoiceTarget) * 100, 100) : 0;

        return [
            'invoice_actual' => $invoiceActual,
            'invoice_gap' => max($invoiceTarget - $invoiceActual, 0),
            'invoice_progress' => $progress,
            'budget_absorption' => ($budgetSummary['annual_budget'] ?? 0) > 0
                ? min(((float) $budgetSummary['realisasi'] / (float) $budgetSummary['annual_budget']) * 100, 100)
                : 0,
            'emr_release_ratio' => ($emrSummary['nominal_emr'] ?? 0) > 0
                ? min(((float) $emrSummary['nominal_release'] / (float) $emrSummary['nominal_emr']) * 100, 100)
                : 0,
        ];
    }
}
