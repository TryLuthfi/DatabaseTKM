<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BillingPayment extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MBillingPayment');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'RINCIAN INVOICE';
            $data['judul'] = 'RINCIAN INVOICE';
            $data['getTargetAllPIC'] = $this->MBillingPayment->getTargetAllPIC();
            $data['getAllData'] = $this->MBillingPayment->getAllData();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('BillingPayment/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function getFilteredBillingPaymentAjax()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $bowheer = $this->input->post('bowheer');
        $regional = $this->input->post('regional');
        $city = $this->input->post('city');
        $month = $this->input->post('priority');

        $data = $this->MBillingPayment->getFilteredBillingPayment($bowheer, $regional, $city, $month);

        // Tentukan kolom yang tampil berdasarkan filter
            $columns = ['No', 'Bowheer', 'Invoice', 'Price', 'Regional', 'Area', 'Date Submit', 'Due Date', 'Agging', "Priority", "PO Number"];

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
        $this->load->model('MBillingPayment');
        $result = $this->MBillingPayment->getTargetInvoice($bowheer, $area, $month, $week);
        echo json_encode($result);
        exit;
    }

    public function addInvoice()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $data = $this->input->post();
        $this->load->model('MBillingPayment');

        $result = $this->MBillingPayment->updateAchievInvoice($data);

        // Jika area belum ada
        if ($result['status'] === 'not_found') {
            echo json_encode([
                'status' => 'not_found',
                'message' => 'Project tidak memiliki area ini',
                'id_bowheer' => $result['id_bowheer'],
                'area_target' => $result['area_target'],
                'month' => $result['month'],
                'week' => $result['week'],
                'regional' => $result['regional'],
                'pic' => $result['pic'],
                'nilai_update' => $result['nilai_update']
            ]);
            return;
        }

        echo json_encode($result);
    }


    public function createNewTargetInvoice()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        $data = $this->input->post();
        $this->load->model('MBillingPayment');
        $result = $this->MBillingPayment->createNewTargetInvoice($data);
        echo json_encode($result);
    }

}
