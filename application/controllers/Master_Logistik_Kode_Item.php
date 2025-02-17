<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_Logistik_Kode_Item extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MMaster_Logistik_Kode_Item');
    }
    
    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

        $data['title'] = 'Kode Item Logistik';
        $data['judul'] = 'Kode Item Logistik';
        $data['getMasterLogistikKodeItem'] = $this->MMaster_Logistik_Kode_Item->getMasterLogistikKodeItem();
        $data['getMasterBowheer'] = $this->MMaster_Logistik_Kode_Item->getMasterBowheer();
        $data['getMasterKepemilikan'] = $this->MMaster_Logistik_Kode_Item->getMasterKepemilikan();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Master_Logistik_Kode_Item/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahKodeItem(){

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        if (!empty($_POST['project_item'])) {
            $kota_string = implode(", ", $_POST['project_item']);
        } else {
            $kota_string = "";
        }

        $hasil_data = array(
            'nama_item' => $_POST['nama_item'],
            'kategori_item' => $_POST['kategori_item'],
            'satuan_item' => $_POST['satuan_item'],
            'id_bowheer_pemilik_item' => $_POST['id_bowheer_pemilik_item'],
            'project_item' => $kota_string
        );

        $res = $this->MMaster_Logistik_Kode_Item->tambahKodeItem($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("Master_Logistik_Kode_Item");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("Master_Logistik_Kode_Item");
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
            'project_item' => $kota_string,
            'id_bowheer_pemilik_item' => $_POST['id_bowheer_pemilik_item']
        );

        $where = array('id_kode_item' => $id_kode_item);
        $res = $this->MMaster_Logistik_Kode_Item->editKodeItem($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("Master_Logistik_Kode_Item");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("Master_Logistik_Kode_Item");
        }
    }

    public function hapusKodeItem($id_kode_item)
    {
        $id_kode_item = array('id_kode_item' => $id_kode_item);
        $res = $this->MMaster_Logistik_Kode_Item->hapusKodeItem($id_kode_item);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("Master_Logistik_Kode_Item");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("Master_Logistik_Kode_Item");
        }
    }
}
