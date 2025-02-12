<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ListArea extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MListArea');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Area';
            $data['judul'] = 'List Area TKM';
            $data['area'] = $this->db->get('tb_area')->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('ListArea/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function add()
    {
        $hasil_data = array(
            'regional_area' => $_POST['regional_area'],
            'provinsi_area' => $_POST['provinsi_area'],
            'kota_area' => $_POST['kota_area'],
            'kecamatan_area' => $_POST['kecamatan_area']
        );

        $res = $this->MListArea->addArea($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("ListArea");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("ListArea");
        }

    }

    public function edit($id)
    {

        $data_array = array(
            'regional_area' => $_POST['regional_area'],
            'provinsi_area' => $_POST['provinsi_area'],
            'kota_area' => $_POST['kota_area'],
            'kecamatan_area' => $_POST['kecamatan_area']
        );

        $where = array('id_area' => $id);

        $res = $this->MListArea->updateArea($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("ListArea");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("ListArea");
        }
    }

    public function delete($id)
    {
        $id_area = array('id_area' => $id);
        $res = $this->MListArea->deleteArea($id_area);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("ListArea");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("ListArea");
        }
    }
}
