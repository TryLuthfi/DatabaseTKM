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
            $data['assets'] = $this->MGHAssets->getAll();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('GHAssets/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');
        } else {
            redirect('Auth');
        }
    }

    public function get($id)
    {
        echo json_encode($this->MGHAssets->getById($id));
    }

    public function add()
    {
        $data = [
            'code' => $this->input->post('code'),
            'label' => $this->input->post('label')
        ];

        $this->MGHAssets->insert($data);
        echo json_encode(['status' => 'sukses_tambah']);
    }

    public function update($id)
    {
        $data = [
            'code' => $this->input->post('code'),
            'label' => $this->input->post('label')
        ];

        $this->MGHAssets->updateData($id, $data);
        echo json_encode(['status' => 'sukses_edit']);
    }

    public function delete($id)
    {
        $delete = $this->MGHAssets->deleteData($id);
        echo json_encode(['status' => $delete ? 'sukses_hapus' : 'gagal_hapus']);
    }
}
