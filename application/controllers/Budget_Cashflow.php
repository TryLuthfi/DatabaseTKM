<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_Cashflow extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MBudget_Cashflow');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN CASHFLOW';
            $data['judul'] = 'RINCIAN CASHFLOW';
            $data['getAllDataCashflow'] = $this->MBudget_Cashflow->getAllDataCashflow();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Budget_Cashflow/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredBudget_CashflowAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $mab_akun_utama = $this->input->post('mab_akun_utama');
        $mab_sub_akun = $this->input->post('mab_sub_akun');
        $mab_nomor_akun = $this->input->post('mab_nomor_akun');
        $mab_deskripsi_akun = $this->input->post('mab_deskripsi_akun');

        $data = $this->MBudget_Cashflow->getFilteredBudget_Cashflow($mab_akun_utama, $mab_sub_akun, $mab_nomor_akun, $mab_deskripsi_akun);

        // Tentukan kolom yang tampil berdasarkan filter
        if (!empty($mab_akun_utama && !empty($mab_sub_akun) && !empty($mab_nomor_akun) && !empty($mab_deskripsi_akun))) {
            $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Divisi', 'PIC', 'ACTION'];
        } elseif (!empty($mab_sub_akun)) {
            $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Divisi', 'PIC', 'ACTION'];
        } elseif (!empty($mab_nomor_akun)) {
            $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Divisi', 'PIC', 'ACTION'];
        } elseif (!empty($mab_deskripsi_akun)) {
            $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Divisi', 'PIC', 'ACTION'];
        } else {
            $columns = ['No', 'Akun Utama', 'Sub Akun', 'Nomor Akun', 'Deskripsi Akun', 'Divisi', 'PIC', 'ACTION'];
        }

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
        $this->load->model('MBudget_Cashflow');

        $result = $this->MBudget_Cashflow->tambahMasterAkun($data);

        echo json_encode($result);
    }

    public function hapusMasterBudget($id_mab)
    {
        $id_mab = array('id_mab' => $id_mab);
        $res = $this->MBudget_Cashflow->deleteMasterAkun($id_mab);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Budget_Cashflow");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Budget_Cashflow");
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

        $res = $this->MBudget_Cashflow->updateMasterAkun($id_mab, $data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
        }

        redirect("Budget_Cashflow");
    }
}
