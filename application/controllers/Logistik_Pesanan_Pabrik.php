<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logistik_Pesanan_Pabrik extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MLogistik_Pesanan_Pabrik');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'List PO Pabrik';
        $data['judul'] = 'List PO Pabrik';
        $data['getMasterLogistikPesananPabrik'] = $this->MLogistik_Pesanan_Pabrik->getMasterLogistikPesananPabrik();
        $data['getOustandingPesananPabrik'] = $this->MLogistik_Pesanan_Pabrik->getOustandingPesananPabrik();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Logistik_Pesanan_Pabrik/Index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahPabrik()
    {

        $hasil_data = array(
            'nama_pabrik' => $_POST['nama_pabrik'],
            'lokasi_pabrik' => $_POST['lokasi_pabrik'],
            'jenis_pabrik' => $_POST['jenis_pabrik'],
            'status_pabrik' => $_POST['status_pabrik']
        );

        $res = $this->MLogistik_Pesanan_Pabrik->tambahPabrik($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Logistik_Pesanan_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Logistik_Pesanan_Pabrik");
        }
    }

    public function editPabrik($id_pabrik)
    {

        $data_array = array(
            'nama_pabrik' => $_POST['nama_pabrik'],
            'lokasi_pabrik' => $_POST['lokasi_pabrik'],
            'jenis_pabrik' => $_POST['jenis_pabrik'],
            'status_pabrik' => $_POST['status_pabrik']
        );

        $where = array('id_pabrik' => $id_pabrik);
        $res = $this->MLogistik_Pesanan_Pabrik->editPabrik($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Logistik_Pesanan_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Logistik_Pesanan_Pabrik");
        }
    }

    public function hapusPabrik($id_pabrik)
    {
        $id_pabrik = array('id_pabrik' => $id_pabrik);
        $res = $this->MLogistik_Pesanan_Pabrik->hapusPabrik($id_pabrik);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Logistik_Pesanan_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Logistik_Pesanan_Pabrik");
        }
    }
}
