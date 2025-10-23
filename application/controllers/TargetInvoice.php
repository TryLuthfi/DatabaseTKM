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

            $data['title'] = 'Target Invoice';
            $data['judul'] = 'Target Invoice';
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

    public function detailKota()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Target Invoice';
            $data['judul'] = 'Target Invoice';
            $data['filterURL'] = $decoded_url_area;
            $data['getTargetAllBowheer'] = $this->MTargetInvoice->getTargetAllBowheer();
            $data['getTargetAllCity'] = $this->MTargetInvoice->getTargetAllCity();
            $data['getTargetBowheerFilterCity'] = $this->MTargetInvoice->getTargetBowheerFilterCity();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexkota', $data);
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function detailBowheer()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Target Invoice';
            $data['judul'] = 'Target Invoice';
            $data['filterURL'] = $decoded_url_area;
            $data['getTargetAllBowheer'] = $this->MTargetInvoice->getTargetAllBowheer();
            $data['getTargetAllCity'] = $this->MTargetInvoice->getTargetAllCity();
            $data['getTargetCityFilterBowheer'] = $this->MTargetInvoice->getTargetCityFilterBowheer();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexbowheer', $data);
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }
    
}
