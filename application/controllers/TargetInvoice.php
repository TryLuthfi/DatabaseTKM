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

            $data['title'] = 'TARGET INVOICE';
            $data['judul'] = 'TARGET INVOICE';
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

            $data['title'] = 'TARGET INVOICE';
            $data['judul'] = 'TARGET INVOICE';
            $data['filterURL'] = $decoded_url_area;
            $data['getDetailTargetBowheerFilterCity'] = $this->MTargetInvoice->getDetailTargetBowheerFilterCity();
            $data['getAllTargetRincianInvoice'] = $this->MTargetInvoice->getAllTargetRincianInvoice();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexdetailcity', $data);
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

            $data['title'] = 'TARGET INVOICE';
            $data['judul'] = 'TARGET INVOICE';
            $data['filterURL'] = $decoded_url_area;
            $data['getDetailTargetCityFilterBowheer'] = $this->MTargetInvoice->getDetailTargetCityFilterBowheer();
            $data['getAllTargetRincianInvoiceDecode'] = $this->MTargetInvoice->getAllTargetRincianInvoiceDecode();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexdetailbowheer', $data);
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function allBowheer()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'TARGET INVOICE - PROJECT';
            $data['judul'] = 'TARGET INVOICE - PROJECT';
            $data['getTargetCityFilterBowheer'] = $this->MTargetInvoice->getTargetCityFilterBowheer();
            $data['getTargetRincianFilterBowheer'] = $this->MTargetInvoice->getTargetRincianFilterBowheer();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexallbowheer', $data);
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function allCity()
    {

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'TARGET INVOICE - KOTA';
            $data['judul'] = 'TARGET INVOICE - KOTA';
            $data['getAllData'] = $this->MTargetInvoice->getAllData();
            $data['getTargetBowheerFilterCity'] = $this->MTargetInvoice->getTargetBowheerFilterCity();
            $data['getTargetRincianFilterCity'] = $this->MTargetInvoice->getTargetRincianFilterCity();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('TargetInvoice/indexallcity', $data);
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function get_target_invoice()
    {
        $bowheer = $this->input->post('bowheer');
        $area = $this->input->post('area');
        $month = $this->input->post('month');
        $week = $this->input->post('week');

        // Ambil data dari model
        $this->load->model('MTargetInvoice');
        $result = $this->MTargetInvoice->getTargetInvoice($bowheer, $area, $month, $week);
        echo json_encode($result);
        exit;
    }

}
