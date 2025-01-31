<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Kode_Item extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Kode_Item');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Kode Item Logistik';
        $data['judul'] = 'Kode Item Logistik';
        $data['getMasterLogistikKodeItem'] = $this->MMaster_Logistik_Kode_Item->getMasterLogistikKodeItem();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Kode_Item/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
}
