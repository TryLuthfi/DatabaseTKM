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

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
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

        echo ("<pre>");
        print_r($_POST);
        echo ("</pre>");

        // if (!empty($_POST['project_item'])) {
        //     $kota_string = implode(", ", $_POST['project_item']);
        // } else {
        //     $kota_string = "";
        // }

        // $data_array = array(
        //     'nama_item' => $_POST['nama_item'],
        //     'satuan_item' => $_POST['satuan_item'],
        //     'kategori_item' => $_POST['kategori_item'],
        //     'project_item' => $kota_string,
        //     'id_bowheer_pemilik_item' => $_POST['id_bowheer_pemilik_item']
        // );

        // $where = array('ao_id_list_office' => $ao_id_list_office);
        // $res = $this->MGA_Aset_Kantor->editAsetKantor($data_array, $where);

        // if ($res >= 1) {
        //     $this->session->set_flashdata('status', 'sukses_edit');
        //     redirect("GA_Aset_Kantor");
        // } else {
        //     $this->session->set_flashdata('status', 'gagal_edit');
        //     redirect("GA_Aset_Kantor");
        // }
    }

    public function hapusKodeItem($id_kode_item)
    {
        $id_kode_item = array('id_kode_item' => $id_kode_item);
        $res = $this->MGA_Aset_Kantor->hapusKodeItem($id_kode_item);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("GA_Aset_Kantor");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("GA_Aset_Kantor");
        }
    }
}
