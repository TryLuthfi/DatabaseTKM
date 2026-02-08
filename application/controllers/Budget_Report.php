<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_Report extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MBudget_Report');
    }

    public function index()
    {
        if (!$this->session->userdata('id_user')) {
            redirect('Auth');
        }

        $raw = $this->MBudget_Report->getAllDataReport();
        $report = [];

        foreach ($raw as $row) {

            if (!isset($row['id_mab'])) {
                continue; // skip data rusak
            }

            $id = (int) $row['id_mab'];
            $bulan = (int) $row['bulan'];

            if (!isset($report[$id])) {
                $report[$id] = [
                    'id_mab' => $id,
                    'akun' => $row['mab_nomor_akun'] . ' - ' . $row['mab_deskripsi_akun'],
                    'bulan' => [],
                    'total_budget' => 0,
                    'total_real' => 0
                ];
            }

            $report[$id]['bulan'][$bulan] = [
                'budget' => (int) $row['budget_bulan'],
                'real' => (int) $row['realisasi_bulan']
            ];

            $report[$id]['total_budget'] += (int) $row['budget_bulan'];
            $report[$id]['total_real'] += (int) $row['realisasi_bulan'];
        }
        $data['report'] = $report;
        $data['title'] = 'RINCIAN CASHFLOW';
        $data['judul'] = 'RINCIAN CASHFLOW';

        //         echo '<pre>';
// print_r($report);
// exit;

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_Report/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function getFilteredBudget_ReportAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $mab_akun_utama = $this->input->post('mab_akun_utama');
        $mab_sub_akun = $this->input->post('mab_sub_akun');
        $mab_nomor_akun = $this->input->post('mab_nomor_akun');
        $mab_deskripsi_akun = $this->input->post('mab_deskripsi_akun');

        $data = $this->MBudget_Report->getFilteredBudget_Report($mab_akun_utama, $mab_sub_akun, $mab_nomor_akun, $mab_deskripsi_akun);

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

    public function getDetailCashflow()
    {

    error_reporting(0);
        ini_set('display_errors', 0);

        $id_mab = $this->input->post('id_mab');
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');

        $data = $this->MBudget_Report->getDetailCashflow($id_mab, $bulan, $tahun);

        echo json_encode($data);

        
    }
}
