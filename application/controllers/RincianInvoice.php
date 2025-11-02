<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RincianInvoice extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MRincianInvoice');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN INVOICE';
            $data['judul'] = 'RINCIAN INVOICE';
            $data['getTargetAllPIC'] = $this->MRincianInvoice->getTargetAllPIC();
            $data['getAllData'] = $this->MRincianInvoice->getAllData();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('RincianInvoice/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredRincianInvoiceAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $pic = $this->input->post('pic');
        $bowheer = $this->input->post('bowheer');
        $regional = $this->input->post('regional');
        $city = $this->input->post('city');
        $month = $this->input->post('month');
        $week = $this->input->post('week');

        $data = $this->MRincianInvoice->getFilteredRincianInvoice($pic, $bowheer, $regional, $city, $month, $week);

        // Tentukan kolom yang tampil berdasarkan filter
        if (!empty($pic && !empty($bowheer) && !empty($regional) && !empty($city))) {
            $columns = ['No', 'PIC', 'Bowheer', 'Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'];
        } elseif (!empty($bowheer)) {
            $columns = ['No', 'PIC', 'Bowheer', 'Regional', 'Kota', 'Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'];
        } elseif (!empty($regional)) {
            $columns = ['No', 'PIC', 'Bowheer', 'Regional', 'Kota', 'Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'];
        } elseif (!empty($city)) {
            $columns = ['No', 'PIC', 'Bowheer', 'Regional', 'Kota', 'Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'];
        } else {
            $columns = ['No', 'PIC', 'Bowheer', 'Target', 'Achieved', 'Sisa', 'Target %', 'Achieved %'];
        }

        echo json_encode([
            'columns' => $columns,
            'data' => $data
        ]);

        log_message('debug', 'Last Query: ' . $this->db->last_query());
    }

    public function get_target_invoice()
    {
        $bowheer = $this->input->post('bowheer');
        $area = $this->input->post('area');
        $month = $this->input->post('month');
        $week = $this->input->post('week');

        log_message('debug', 'FILTER BOWHEER: ' . print_r($bowheer, true));

        // Ambil data dari model
        $this->load->model('MRincianInvoice');
        $result = $this->MRincianInvoice->getTargetInvoice($bowheer, $area, $month, $week);
        echo json_encode($result);
        exit;
    }

    public function addInvoice()
    {
        $data = $this->input->post();

        $this->load->model('MRincianInvoice');
        $result = $this->MRincianInvoice->updateAchievInvoice($data);

        if ($result['status']) {
            $this->session->set_flashdata('success', 'sukses_tambah' . number_format($result['nilai_update'], 0, ',', '.'));
        } else {
            $this->session->set_flashdata('error', 'gagal_tambah' . $result['message']);
        }

        redirect('RincianInvoice');
    }

}
