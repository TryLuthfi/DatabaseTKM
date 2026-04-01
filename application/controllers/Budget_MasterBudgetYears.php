<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_MasterBudgetYears extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MBudget_MasterBudgetYears');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN INVOICE';
            $data['judul'] = 'RINCIAN INVOICE';
            $data['getAllData'] = $this->MBudget_MasterBudgetYears->getAllData();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Budget_MasterBudgetYears/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredBudget_MasterBudgetYearsAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $mab_akun_utama = $this->input->post('mab_akun_utama');
        $mab_sub_akun = $this->input->post('mab_sub_akun');
        $mab_nomor_akun = $this->input->post('mab_nomor_akun');
        $mab_deskripsi_akun = $this->input->post('mab_deskripsi_akun');

        $data = $this->MBudget_MasterBudgetYears->getFilteredBudget_MasterBudgetYears($mab_akun_utama, $mab_sub_akun, $mab_nomor_akun, $mab_deskripsi_akun);

        $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Budget Tahunan','Budget Monthly','Selisih','ACTION'];

        echo json_encode([
            'columns' => $columns,
            'data' => $data
        ]);

        log_message('debug', 'Last Query ab: ' . $this->db->last_query());
    }

    public function addMasterBudget()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $data = $this->input->post();

        //     echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit;
        $this->load->model('MBudget_MasterBudgetYears');

        $result = $this->MBudget_MasterBudgetYears->tambahMasterAkun($data);

        echo json_encode($result);
    }

    public function hapusMasterBudget($id_mab)
    {
        $id_mab = array('id_mab' => $id_mab);
        $res = $this->MBudget_MasterBudgetYears->deleteMasterAkun($id_mab);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Budget_MasterBudgetYears");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Budget_MasterBudgetYears");
        }
    }

    public function editMasterBudget()
    {
        $id_mab = $this->input->post('id_mab');

        $data = [
            'mab_akun_utama' => $this->input->post('addfilter_akun_utama'),
            'mab_sub_akun' => $this->input->post('addfilter_sub_akun'),
            'mab_nomor_akun' => $this->input->post('inputNomorAkunBaru'),
            'mab_deskripsi_akun' => $this->input->post('inputDeskripsiAkunBaru'),
            'mab_divisi' => $this->input->post('addfilter_divisi'),
            'mab_pic' => $this->input->post('addfilter_pic'),
        ];

        $res = $this->MBudget_MasterBudgetYears->updateMasterAkun($id_mab, $data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
        }

        redirect("Budget_MasterBudgetYears");
    }

    public function getMonthlyDetail()
    {

        error_reporting(0);
        ini_set('display_errors', 0);
        $id = $this->input->post('id_budget_years');

        $data = $this->db->query("
        SELECT *
        FROM budget_monthly
        WHERE id_budget_years = ?
        ORDER BY bulan
    ", [$id])->result_array();

        echo json_encode($data);

        log_message('debug', 'Last Query Cek Bulan: ' . $this->db->last_query());
    }

    public function updateMonthly()
    {
        error_reporting(0);
        ini_set('display_errors', 0);
        $data = $this->input->post('data');

        foreach ($data as $row) {
            $this->db->where('id_budget_monthly', $row['id_budget_monthly']);
            $this->db->update('budget_monthly', [
                'budget_bulan' => $row['budget_bulan']
            ]);
        }

        echo json_encode(['status' => 'success']);

        log_message('debug', 'Last Query update month: ' . $this->db->last_query());
    }
}
