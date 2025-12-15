<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHTenants extends CI_Controller
{

    public function __construct()
    {

        // error_reporting(0);
        // ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHTenants');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['getAllTenants'] = $this->MGHTenants->getAllTenants();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHTenants/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function tambahTenant()
    {

        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'fullname' => $_POST['fullname'],
            'phone' => $_POST['phone'],
            'nik' => $_POST['nik'],
            'bank' => $_POST['bank'],
            'no_rekening' => $_POST['no_rekening'],
            'address' => $_POST['address']
        );

        $res = $this->MGHTenants->tambahTenant($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect($previousUrl);
        }
    }

    public function editTenant($id)
    {
        $previousUrl = $this->input->server('HTTP_REFERER');

        $hasil_data = array(
            'fullname' => $_POST['fullname'],
            'phone' => $_POST['phone'],
            'nik' => $_POST['nik'],
            'bank' => $_POST['bank'],
            'no_rekening' => $_POST['no_rekening'],
            'address' => $_POST['address']
        );

        $res = $this->MGHTenants->editTenant($hasil_data, array('id' => $id));

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect($previousUrl);
        }
    }

    public function hapusTenant($id)
    {
        $previousUrl = $this->input->server('HTTP_REFERER');

        $res = $this->MGHTenants->hapusTenant(array('id' => $id));

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect($previousUrl);
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect($previousUrl);
        }
    }   

}
