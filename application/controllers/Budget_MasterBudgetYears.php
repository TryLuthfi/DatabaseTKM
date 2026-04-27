<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_MasterBudgetYears extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_budgeting_access();
        $this->load->model('MBudget_MasterBudgetYears');
    }

    public function index()
    {

        $selectedYear = (int) $this->input->get('year');
        if ($selectedYear <= 0) {
            $selectedYear = (int) date('Y');
        }

        $data['title'] = 'Budgeting - Master Budget';
        $data['judul'] = 'Budgeting - Master Budget';
        $data['selectedYear'] = $selectedYear;
        $data['yearOptions'] = $this->MBudget_MasterBudgetYears->getAvailableYears();
        if (!in_array($selectedYear, $data['yearOptions'], true)) {
            $data['yearOptions'][] = $selectedYear;
            rsort($data['yearOptions']);
        }
        $data['items'] = $this->MBudget_MasterBudgetYears->getItems();
        $data['budgets'] = $this->MBudget_MasterBudgetYears->getBudgetRows($selectedYear);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_MasterBudgetYears/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        $payload = $this->input->post(NULL, true);
        $success = $this->MBudget_MasterBudgetYears->saveBudget($payload);

        $this->session->set_flashdata('status', $success ? 'sukses_simpan' : 'gagal_simpan');
        redirect('Budget_MasterBudgetYears?year=' . (int) ($payload['budget_year'] ?? date('Y')));
    }

    public function delete($id)
    {
        $year = (int) $this->input->get('year');
        if ($year <= 0) {
            $year = (int) date('Y');
        }

        $success = $this->MBudget_MasterBudgetYears->deleteBudget((int) $id);
        $this->session->set_flashdata('status', $success ? 'sukses_hapus' : 'gagal_hapus');
        redirect('Budget_MasterBudgetYears?year=' . $year);
    }

    public function getMonthlyDetail()
    {
        $budgetId = (int) $this->input->post('id_budget_annual');
        $budget = $this->MBudget_MasterBudgetYears->getBudgetById($budgetId);

        if (!$budget) {
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Budget tidak ditemukan.',
                ]));
            return;
        }

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status' => true,
                'budget' => $budget,
                'monthly' => $this->MBudget_MasterBudgetYears->getMonthlyRows($budgetId),
            ]));
    }
}
