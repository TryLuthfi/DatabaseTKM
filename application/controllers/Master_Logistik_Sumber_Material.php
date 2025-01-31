<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Sumber_Material extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Sumber_Material');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Sumber Material Logistik';
        $data['judul'] = 'Sumber Material Logistik';
        $data['getMasterLogistikSumberMaterialMasuk'] = $this->MMaster_Logistik_Sumber_Material->getMasterLogistikSumberMaterialMasuk();
        $data['getMasterLogistikSumberMaterialKeluar'] = $this->MMaster_Logistik_Sumber_Material->getMasterLogistikSumberMaterialKeluar();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Sumber_Material/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
}
