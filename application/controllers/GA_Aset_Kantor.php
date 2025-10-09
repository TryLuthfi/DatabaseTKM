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

            $data['title'] = 'Aset Terminasi';
            $data['judul'] = 'Aset Terminasi';
            $data['getMasterAsetTerminasi'] = $this->MGA_Aset_Kantor->getMasterAsetTerminasi();
            $data['getCountAsetOfficeAll'] = $this->MGA_Aset_Kantor->getCountAsetOfficeAll();
            $data['getCountAsetOfficeByRegionTipe'] = $this->MGA_Aset_Kantor->getCountAsetOfficeByRegionTipe();
            $data['getCountAsetTerminasiByKota'] = $this->MGA_Aset_Kantor->getCountAsetTerminasiByKota();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function allAsetTerminasi()
    {

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Terminasi';
            $data['judul'] = 'Aset Terminasi';
            $data['getCountAsetTerminasiAll'] = $this->MGA_Aset_Kantor->getCountAsetTerminasiAll();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/indexall', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function detailTerminasi()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Terminasi ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Aset Terminasi ' . $decoded_url_area;
            $data['getKategoriTerminasi'] = strtoupper($decoded_url_area);
            $data['getMasterAsetTerminasi'] = $this->MGA_Aset_Kantor->getMasterAsetTerminasi();
            $data['getMasterAsetTerminasiTipe'] = $this->MGA_Aset_Kantor->getMasterAsetTerminasiTipe();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/indexTerminasi', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function areaTerminasi()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Terminasi ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Aset Terminasi ' . $decoded_url_area;
            $data['filterURL'] = $decoded_url_area;
            $data['getKategoriTerminasi'] = strtoupper($decoded_url_area);
            $data['getMasterAsetTerminasi'] = $this->MGA_Aset_Kantor->getMasterAsetTerminasi();
            $data['getMasterAsetTerminasiFilter'] = $this->MGA_Aset_Kantor->getMasterAsetTerminasiFilter();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kantor/indexTerminasiarea', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function tambahKodeItem()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $hasil_data = array(
            'nama_item' => $_POST['nama_item'],
            'kategori_item' => $_POST['kategori_item'],
            'satuan_item' => $_POST['satuan_item'],
            'id_bowheer_pemilik_item' => $_POST['id_bowheer_pemilik_item'],
            'harga_penjualan' => $_POST['harga_penjualan'],
            'project_item' => $_POST['project_item']
        );

        $res = $this->MGA_Aset_Kantor->tambahKodeItem($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("GA_Aset_Kantor");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("GA_Aset_Kantor");
        }
    }

    public function editKodeItem($id_kode_item)
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        if (!empty($_POST['project_item'])) {
            $kota_string = implode(", ", $_POST['project_item']);
        } else {
            $kota_string = "";
        }

        $data_array = array(
            'nama_item' => $_POST['nama_item'],
            'satuan_item' => $_POST['satuan_item'],
            'kategori_item' => $_POST['kategori_item'],
            'project_item' => $kota_string,
            'id_bowheer_pemilik_item' => $_POST['id_bowheer_pemilik_item']
        );

        $where = array('id_kode_item' => $id_kode_item);
        $res = $this->MGA_Aset_Kantor->editKodeItem($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("GA_Aset_Kantor");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("GA_Aset_Kantor");
        }
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
