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

            $data['title'] = 'Detail Stok ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Detail Stok ' . strtoupper($decoded_url_area);
            $data['lokasi'] = strtoupper($decoded_url_area);
            $data['getStokDetailArea'] = $this->MLogistik_Stok_Detail->getStokDetailArea();
            $data['getSummaryDetailArea'] = $this->MLogistik_Stok_Detail->getSummaryDetailArea();
            $data['getHistoriInOUtLogistikArea'] = $this->MLogistik_Stok_Detail->getHistoriInOUtLogistikArea();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Logistik_Stok_Detail/index', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function detail_kategori($kategori_item)
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Detail Stok ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Detail Stok ' . $decoded_url_area;
            $data['kategori_item'] = strtoupper($decoded_url_area);
            $data['getStokPerBowheer'] = $this->MLogistik_Stok_Detail->getStokPerBowheer();
            $data['getDistribusiPerBowheer'] = $this->MLogistik_Stok_Detail->getDistribusiPerBowheer();
            $data['getHistoriInOUtLogistikKategori'] = $this->MLogistik_Stok_Detail->getHistoriInOUtLogistikKategori();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Logistik_Stok_Detail/indexkategori', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function filter_kategori()
    {
        if (!empty($this->session->userdata('id_user'))) {
            $rincianData = json_decode($this->input->post('rincianData'), true);
            $rincianData2 = json_decode($this->input->post('rincianData2'), true);
            $kategoriItem = $this->input->post('kategori_item');

            if (!empty($rincianData)) {
                $this->session->set_userdata('rincianFilterDashboardLogistik', $rincianData);
                $this->session->set_userdata('getRincianDashboardFileteredBowheer', $rincianData2);
                $this->session->set_userdata('kategori_item', $kategoriItem);
            }

            // Redirect manual dengan JavaScript di AJAX
        } else {
            redirect('Auth');
        }
    }

    public function filter_kategori_rdr()
    {

        if (!empty($this->session->userdata('id_user'))) {
            $data['rincianFilterDashboardLogistik'] = $this->session->userdata('rincianFilterDashboardLogistik');
            $data['getRincianDashboardFileteredBowheer'] = $this->session->userdata('getRincianDashboardFileteredBowheer');
            $data['kategori_item'] = $this->input->get('kategori'); // Ambil kategori dari query string
    
            $data['title'] = 'Detail Stok Filter' . strtoupper($data['kategori_item']);
            $data['judul'] = 'Detail Stok Filter' . $data['kategori_item'];
    
            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('Logistik_Stok_Detail/indexfilter', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }


}
