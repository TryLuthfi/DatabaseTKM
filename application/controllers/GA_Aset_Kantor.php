<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GA_Aset_Kantor extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGA_Aset_Kantor');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Office';
            $data['judul'] = 'Aset Office';
            $data['getMasterAsetOffice'] = $this->MGA_Aset_Kantor->getMasterAsetOffice();
            $data['getCountAsetOfficeAll'] = $this->MGA_Aset_Kantor->getCountAsetOfficeAll();
            $data['getCountAsetOfficeByRegionTipe'] = $this->MGA_Aset_Kantor->getCountAsetOfficeByRegionTipe();
            $data['getCountAsetOfficeByCityTipe'] = $this->MGA_Aset_Kantor->getCountAsetOfficeByCityTipe();
            $data['getCountAsetOfficeByKota'] = $this->MGA_Aset_Kantor->getCountAsetOfficeByKota();
            $data['getReportStokAsetkantor'] = $this->MGA_Aset_Kantor->getReportStokAsetkantor();

            $regional = $this->input->get('regional');
            $area = $this->input->get('area');
            $tahun = $this->input->get('tahun');
            $kondisi = $this->input->get('kondisi');
            $status = $this->input->get('status');

            $data['getFilteredAsetKantor'] = $this->MGA_Aset_Kantor->getFilteredAsetKantor($regional, $area, $tahun, $kondisi, $status);

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function filterDataProject()
    {
        error_reporting(0);
    ini_set('display_errors', 0);
    
        $regional = $this->input->post('regional');
        $area = $this->input->post('area');
        $tahun = $this->input->post('tahun');
        $status = $this->input->post('status');
        $kondisi = $this->input->post('kondisi');

        // true berarti gunakan versi group by
        $result = $this->MGA_Aset_Kantor->getFilteredAsetKantor($regional, $area, $tahun, $status, $kondisi, true);

        echo json_encode($result);
    }

    public function detailOfficeArea()
    {
        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Office';
            $data['judul'] = 'Aset Office';
            $data['filterURL'] = $decoded_url_area;
            $data['getKategoriOffice'] = strtoupper($decoded_url_area);
            $data['getCountAsetOfficeAll'] = $this->MGA_Aset_Kantor->getCountAsetOfficeAll();
            $data['getMasterAsetOffice'] = $this->MGA_Aset_Kantor->getMasterAsetOffice();
            $data['getMasterAsetOfficeArea'] = $this->MGA_Aset_Kantor->getMasterAsetOfficeArea();
            $data['getCountAsetOfficeByKota'] = $this->MGA_Aset_Kantor->getCountAsetOfficeByKota();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/indexofficearea', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function detailOffice()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Kantor ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Aset Kantor ' . $decoded_url_area;
            $data['getKategoriOffice'] = strtoupper($decoded_url_area);
            $data['filterURL'] = $decoded_url_area;
            $data['getMasterAsetOffice'] = $this->MGA_Aset_Kantor->getMasterAsetOffice();
            $data['getMasterAsetOfficeTipe'] = $this->MGA_Aset_Kantor->getMasterAsetOfficeTipe();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/indexoffice', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function tambahAsetKantor()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'ka_id_kode_aset' => $_POST['ka_id_kode_aset'],
            'ao_merk' => $_POST['ao_merk'],
            'ao_type' => $_POST['ao_type'],
            'ao_serial_number' => $_POST['ao_serial_number'],
            'ao_spesifikasi' => $_POST['ao_spesifikasi'],
            'ao_kondisi_aset' => $_POST['ao_kondisi_aset'],
            'ao_pic' => $_POST['ao_pic'],
            'ao_regional' => $_POST['ao_regional'],
            'ao_area' => $_POST['ao_area'],
            'ao_status_aset' => $_POST['ao_status_aset'],
            'ao_keterangan_aset' => $_POST['ao_keterangan_aset'],
            'ao_tahun_perolehan' => $_POST['ao_tahun_perolehan'],
            'ao_date_last_cek' => $_POST['ao_date_last_cek'],
            'ao_date_input' => $_POST['ao_date_input']
        );

        $res = $this->MGA_Aset_Kantor->tambahAsetKantor($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editAsetKantor($ao_id_list_office)
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'ka_id_kode_aset' => $_POST['ka_id_kode_aset'],
            'ao_merk' => $_POST['ao_merk'],
            'ao_type' => $_POST['ao_type'],
            'ao_serial_number' => $_POST['ao_serial_number'],
            'ao_spesifikasi' => $_POST['ao_spesifikasi'],
            'ao_kondisi_aset' => $_POST['ao_kondisi_aset'],
            'ao_pic' => $_POST['ao_pic'],
            'ao_regional' => $_POST['ao_regional'],
            'ao_area' => $_POST['ao_area'],
            'ao_status_aset' => $_POST['ao_status_aset'],
            'ao_keterangan_aset' => $_POST['ao_keterangan_aset'],
            'ao_tahun_perolehan' => $_POST['ao_tahun_perolehan'],
            'ao_date_last_cek' => $_POST['ao_date_last_cek'],
            'ao_date_input' => $_POST['ao_date_input']
        );

        $where = array('ao_id_list_office' => $ao_id_list_office);
        $res = $this->MGA_Aset_Kantor->editAsetKantor($hasil_data, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect($previousUrl);
        }
    }

    public function hapusAsetKantor($ao_id_list_office)
    {

        $previousUrl = $this->input->server('HTTP_REFERER');

        $ao_id_list_office = array('ao_id_list_office' => $ao_id_list_office);
        $res = $this->MGA_Aset_Kantor->hapusAsetKantor($ao_id_list_office);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect($previousUrl);
        }
    }
}
