<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Lokasi_Gudang extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Lokasi_Gudang');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Kode Item Logistik';
        $data['judul'] = 'Kode Item Logistik';
        $data['getMasterLogistikLokasiGudang'] = $this->MMaster_Logistik_Lokasi_Gudang->getMasterLogistikLokasiGudang();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Lokasi_Gudang/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }
}
