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

        $decoded_url_area = urldecode($last_segment);
        
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Detail Stok '.$decoded_url_area;
        $data['judul'] = 'Detail Stok '.$decoded_url_area;
        $data['lokasi'] = $decoded_url_area;
        $data['getStokDetailArea'] = $this->MLogistik_Stok_Detail->getStokDetailArea();
        $data['getSummaryDetailArea'] = $this->MLogistik_Stok_Detail->getSummaryDetailArea();
        $data['getHistoriInOUtLogistik'] = $this->MLogistik_Stok_Detail->getHistoriInOUtLogistik();

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
