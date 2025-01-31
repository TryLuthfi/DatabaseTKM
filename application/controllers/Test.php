<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {
        $data['title'] = 'Test';

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Test/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function testAjax() {
        echo json_encode(["status" => "success", "message" => "AJAX Berjalan"]);
    }
}
