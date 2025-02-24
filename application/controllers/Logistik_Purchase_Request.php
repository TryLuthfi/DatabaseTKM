<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Purchase_Request extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        // $this->load->model('MLogistik_Pesanan_Pabrik');
        // $this->load->model('MDashboard_Logistik_Stok');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function view_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['type'] = 'view';

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function edit_purchase_request()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Purchase Request';
            $data['type'] = 'edit';

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('logistik_purchase_request_detail', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
}
