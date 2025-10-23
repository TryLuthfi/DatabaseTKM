<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TargetInvoice extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MTargetInvoice');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Office';
            $data['judul'] = 'Aset Office';
            $data['getTargetAllBowheer'] = $this->MTargetInvoice->getTargetAllBowheer();
            $data['getTargetAllCity'] = $this->MTargetInvoice->getTargetAllCity();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
}
