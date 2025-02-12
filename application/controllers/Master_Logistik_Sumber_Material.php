<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Sumber_Material extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Sumber_Material');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Sumber Material Logistik';
        $data['judul'] = 'Sumber Material Logistik';
        $data['getMasterLogistikSumberMaterial'] = $this->MMaster_Logistik_Sumber_Material->getMasterLogistikSumberMaterial();
        $data['getMasterLogistikSumberMaterialMasuk'] = $this->MMaster_Logistik_Sumber_Material->getMasterLogistikSumberMaterialMasuk();
        $data['getMasterLogistikSumberMaterialKeluar'] = $this->MMaster_Logistik_Sumber_Material->getMasterLogistikSumberMaterialKeluar();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Sumber_Material/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahSumberMaterialMasuk(){

        $hasil_data = array(
            'nama_sumber_material' => $_POST['nama_sumber_material'],
            'status_sumber_material' => "IN"
        );

        $res = $this->MMaster_Logistik_Sumber_Material->tambahSumberMaterialMasuk($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Master_Logistik_Sumber_Material");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Master_Logistik_Sumber_Material");
        }
    }

    public function tambahSumberMaterialKeluar(){

        $hasil_data = array(
            'nama_sumber_material' => $_POST['nama_sumber_material'],
            'status_sumber_material' => "OUT"
        );

        $res = $this->MMaster_Logistik_Sumber_Material->tambahSumberMaterialKeluar($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Master_Logistik_Sumber_Material");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Master_Logistik_Sumber_Material");
        }
    }

    public function editSumberMaterial($id_sumber_material)
    {

        $data_array = array(
            'nama_sumber_material' => $_POST['nama_sumber_material'],
            'status_sumber_material' => $_POST['status_sumber_material']
        );

        $where = array('id_sumber_material' => $id_sumber_material);
        $res = $this->MMaster_Logistik_Sumber_Material->editSumberMaterial($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Master_Logistik_Sumber_Material");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Master_Logistik_Sumber_Material");
        }
    }

    public function hapusSumberMaterial($id_sumber_material)
    {
        $id_sumber_material = array('id_sumber_material' => $id_sumber_material);
        $res = $this->MMaster_Logistik_Sumber_Material->hapusSumberMaterial($id_sumber_material);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Master_Logistik_Sumber_Material");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Master_Logistik_Sumber_Material");
        }
    }
}
