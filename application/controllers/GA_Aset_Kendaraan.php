<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GA_Aset_Kendaraan extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGA_Aset_Kendaraan');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Kendaraan';
            $data['judul'] = 'Aset Kendaraan';
            $data['getMasterAsetKendaraan'] = $this->MGA_Aset_Kendaraan->getMasterAsetKendaraan();
            $data['getCountAsetKendaraan'] = $this->MGA_Aset_Kendaraan->getCountAsetKendaraan();
            $data['getCountAsetKendaraanByKota'] = $this->MGA_Aset_Kendaraan->getCountAsetKendaraanByKota();
            $data['getCountAsetKendaraanByRegion'] = $this->MGA_Aset_Kendaraan->getCountAsetKendaraanByRegion();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kendaraan/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function detailKendaraan()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Kendaraan ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Aset Kendaraan ' . $decoded_url_area;
            $data['getKategoriKendaraan'] = strtoupper($decoded_url_area);
            $data['getMasterAsetKendaraan'] = $this->MGA_Aset_Kendaraan->getMasterAsetKendaraan();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kendaraan/indexkendaraan', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function areaKendaraan()
    {

        $url_path = $_SERVER['REQUEST_URI']; // Ambil seluruh URL setelah domain
        $segments = explode("/", $url_path); // Pecah berdasarkan "/"
        $last_segment = end($segments); // Ambil bagian terakhir dari URL

        $decoded_url_area = urldecode($last_segment);

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'Aset Kendaraan ' . strtoupper($decoded_url_area);
            $data['judul'] = 'Aset Kendaraan ' . $decoded_url_area;
            $data['filterURL'] = $decoded_url_area;
            $data['getKategoriKendaraan'] = strtoupper($decoded_url_area);
            $data['getMasterAsetKendaraan'] = $this->MGA_Aset_Kendaraan->getMasterAsetKendaraan();
            $data['getMasterAsetKendaraanFilter'] = $this->MGA_Aset_Kendaraan->getMasterAsetKendaraanFilter();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GA_Aset_Kendaraan/indexkendaraanarea', $data);
            // $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function tambahKendaraan()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $hasil_data = array(
            'ka_id_kode_aset' => $_POST['ka_id_kode_aset'],
            'ak_plat_nomor' => $_POST['ak_plat_nomor'],
            'ak_merk' => $_POST['ak_merk'],
            'ak_kondisi_aset' => $_POST['ak_kondisi_aset'],
            'ak_pic' => $_POST['ak_pic'],
            'ak_area' => $_POST['ak_area'],
            'ak_regional' => $_POST['ak_regional'],
            'ak_tahun_perolehan' => $_POST['ak_tahun_perolehan'],
            'ak_tanggal_stnk' => $_POST['ak_tanggal_stnk'],
            'ak_tanggal_plat' => $_POST['ak_tanggal_plat'],
            'ak_status_aset' => $_POST['ak_status_aset'],
            'ak_keterangan_aset' => $_POST['ak_keterangan_aset']
        );

        $res = $this->MGA_Aset_Kendaraan->tambahKendaraan($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("GA_Aset_Kendaraan");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("GA_Aset_Kendaraan");
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
        $res = $this->MGA_Aset_Kendaraan->editKodeItem($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("GA_Aset_Kendaraan");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("GA_Aset_Kendaraan");
        }
    }

    public function hapusKendaraan($ak_id_list_kendaraan)
    {
        $ak_id_list_kendaraan = array('ak_id_list_kendaraan' => $ak_id_list_kendaraan);
        $res = $this->MGA_Aset_Kendaraan->hapusKendaraan($ak_id_list_kendaraan);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("GA_Aset_Kendaraan");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("GA_Aset_Kendaraan");
        }
    }
}
