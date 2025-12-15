<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHAssets extends CI_Controller
{

    public function __construct()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHAssets');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Assets Kamar';
            $data['judul'] = 'List Assets Kamar';
            $data['getAllAssets'] = $this->MGHAssets->getAllAssets();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHAssets/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahAsset()
    {

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'code' => $_POST['code'],
            'label' => $_POST['label']
        );

        $res = $this->MGHAssets->tambahAsset($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editAsset($id)
    {

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'code' => $_POST['code'],
            'label' => $_POST['label']
        );

        $where = array('id' => $id);
        $res = $this->MGHAssets->editAsset($hasil_data, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect($previousUrl);
        }
        
    }

    public function hapusAsset($id)
    {
        $previousUrl = $this->input->server('HTTP_REFERER');

        $id = array('id' => $id);
        $res = $this->MGHAssets->hapusAsset($id);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect($previousUrl);
        }
    }


    
}
