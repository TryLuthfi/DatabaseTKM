<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Pabrik extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Pabrik');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'List Pabrik';
        $data['judul'] = 'List Pabrik';
        $data['getMasterLogistikPabrik'] = $this->MMaster_Logistik_Pabrik->getMasterLogistikPabrik();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Pabrik/Index', $data);
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

        $res = $this->MMaster_Logistik_Pabrik->tambahPabrik($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Master_Logistik_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Master_Logistik_Pabrik");
        }
    }

    public function editSumberMaterial($id_sumber_material)
    {

        $data_array = array(
            'nama_sumber_material' => $_POST['nama_sumber_material'],
            'status_sumber_material' => $_POST['status_sumber_material']
        );

        $where = array('id_sumber_material' => $id_sumber_material);
        $res = $this->MMaster_Logistik_Pabrik->editSumberMaterial($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Master_Logistik_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Master_Logistik_Pabrik");
        }
    }

    public function hapusSumberMaterial($id_sumber_material)
    {
        $id_sumber_material = array('id_sumber_material' => $id_sumber_material);
        $res = $this->MMaster_Logistik_Pabrik->hapusSumberMaterial($id_sumber_material);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Master_Logistik_Pabrik");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Master_Logistik_Pabrik");
        }
    }
}
