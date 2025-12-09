<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GHTenants extends CI_Controller
{

    public function __construct()
    {

        error_reporting(0);
        ini_set('display_errors', 0);

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MGHTenants');
    }

    public function index()
    {
        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List Kamar GH';
            $data['judul'] = 'List Kamar GH';
            $data['tenants'] = $this->MGHTenants->getAll();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHTenants/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function get($id)
    {
        echo json_encode($this->MGHTenants->getById($id));
    }

    public function add()
    {
        $data = [
            'fullname' => $this->input->post('fullname'),
            'phone' => $this->input->post('phone'),
            'nik' => $this->input->post('nik'),
            'address' => $this->input->post('address')
        ];

        $this->MGHTenants->insert($data);

        echo json_encode(['status' => 'sukses_tambah']);
    }

    public function update($id)
    {
        $data = [
            'fullname' => $this->input->post('fullname'),
            'phone' => $this->input->post('phone'),
            'nik' => $this->input->post('nik'),
            'address' => $this->input->post('address')
        ];

        $this->MGHTenants->updateData($id, $data);

        echo json_encode(['status' => 'sukses_edit']);
    }

    public function delete($id)
    {
        $delete = $this->MGHTenants->deleteData($id);
        echo json_encode(['status' => $delete ? 'sukses_hapus' : 'gagal_hapus']);
    }

}
