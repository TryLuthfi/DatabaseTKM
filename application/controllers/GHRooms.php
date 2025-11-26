<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHRooms extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHRooms');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['rooms'] = $this->MGHRooms->get_all();
            $data['types'] = $this->MGHRooms->get_types();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHRooms/index', $data);
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

        $res = $this->MGHRooms->addArea($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("GHRooms");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("GHRooms");
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

        $res = $this->MGHRooms->updateArea($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("GHRooms");
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("GHRooms");
        }
    }

    public function delete($id)
    {
        $id_area = array('id_area' => $id);
        $res = $this->MGHRooms->deleteArea($id_area);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("GHRooms");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("GHRooms");
        }
    }
}
