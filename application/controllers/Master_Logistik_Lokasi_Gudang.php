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

            $data['title'] = 'Lokasi Gudang Logistik';
            $data['judul'] = 'Lokasi Gudang Logistik';
            $data['getMasterUser'] = $this->MMaster_Logistik_Lokasi_Gudang->getMasterUser();
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

    public function tambahLokasiGudang()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $hasil_data = array(
            'regional_lokasi_gudang' => $_POST['regional_lokasi_gudang'],
            'provinsi_lokasi_gudang' => $_POST['provinsi_lokasi_gudang'],
            'kota_lokasi_gudang' => $_POST['kota_lokasi_gudang'],
            'kecamatan_lokasi_gudang' => $_POST['kecamatan_lokasi_gudang'],
            'id_user' => $_POST['id_user']
        );

        $res = $this->MMaster_Logistik_Lokasi_Gudang->tambahLokasiGudang($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Master_Logistik_Lokasi_Gudang");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Master_Logistik_Lokasi_Gudang");
        }
    }

    public function hapusLokasiGudang($id_lokasi_gudang)
    {
        $id_lokasi_gudang = array('id_lokasi_gudang' => $id_lokasi_gudang);
        $res = $this->MMaster_Logistik_Lokasi_Gudang->hapusLokasiGudang($id_lokasi_gudang);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Master_Logistik_Lokasi_Gudang");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Master_Logistik_Lokasi_Gudang");
        }
    }

    public function editLokasiGudang($id_lokasi_gudang)
    {
        $data_array = array(
            'regional_lokasi_gudang' => $_POST['regional_lokasi_gudang'],
            'provinsi_lokasi_gudang' => $_POST['provinsi_lokasi_gudang'],
            'kota_lokasi_gudang' => $_POST['kota_lokasi_gudang'],
            'kecamatan_lokasi_gudang' => $_POST['kecamatan_lokasi_gudang'],
            'id_user' => $_POST['id_user']
        );

        $where = array('id_lokasi_gudang' => $id_lokasi_gudang);
        $res = $this->MMaster_Logistik_Lokasi_Gudang->editLokasiGudang($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Master_Logistik_Lokasi_Gudang");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Master_Logistik_Lokasi_Gudang");
        }
    }
}
