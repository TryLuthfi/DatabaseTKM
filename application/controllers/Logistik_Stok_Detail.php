<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Stok_Detail extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Stok_Detail');
    }
    
    public function detail()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL
        
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Detail Stok '.$last_segment;
        $data['judul'] = 'Detail Stok '.$last_segment;
        $data['lokasi'] = $last_segment;
        $data['getStokDetailArea'] = $this->MLogistik_Stok_Detail->getStokDetailArea();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Stok_Detail/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    
}
