<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ListUser extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MListUser');
    }

    public function index()
    {

        if (!empty($this->session->userdata('id_user'))) {

            $data['title'] = 'List User';
            $data['judul'] = 'List User TKM';
            $data['rincian_user'] = $this->MListUser->getData();
            $data['rincian_jabatan'] = $this->MListUser->getJabatan();
            $data['rincian_level'] = $this->MListUser->getLevel();
            $data['count_jabatan'] = $this->MListUser->getCountJabatan();
            $data['count_active_user'] = $this->MListUser->getCountActiveUser();
            $data['user'] = $this->db->get('tb_master_user')->result_array();

            $this->load->view('Templates/01_Header', $data);
            $this->load->view('Templates/02_Menu');
            $this->load->view('ListUser/index', $data);
            $this->load->view('Templates/03_Footer');
            $this->load->view('Templates/99_JS');

        } else {
            redirect('Auth');
        }
    }

    public function add()
    {
        // echo ("<pre>");
        // print_r($_POST);
        // echo ("</pre>");

        $rincian_user = $this->db->get('tb_master_user')->row_array();

        $hasil_data = array(
            'nama_user' => $_POST['nama_user'],
            'username_user' => $_POST['username_user'],
            'password_user' => $_POST['password_user'],
            'id_level' => $_POST['id_level'],
            'id_jabatan' => $_POST['id_jabatan'],
            'status_user' => $_POST['status_user']
        );

        $res = $this->MListUser->addUser($hasil_data);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_tambah');
            redirect("ListUser");
        } else {
            $this->session->set_flashdata('status', 'gagal_tambah');
            redirect("ListUser");
        }

    }

    public function edit($id)
    {

        $data_array = array(
            'nama_user' => $_POST['nama_user'],
            'username_user' => $_POST['username_user'],
            'password_user' => $_POST['password_user'],
            'id_level' => $_POST['id_level'],
            'id_jabatan' => $_POST['id_jabatan'],
            'status_user' => $_POST['status_user']
        );

        $where = array('id_user' => $id);

        $res = $this->MListUser->updateUser($data_array, $where);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_edit');
            redirect("ListUser");
            $status = $this->session->flashdata('destroy');
        } else {
            $this->session->set_flashdata('status', 'gagal_edit');
            redirect("ListUser");
            $status = $this->session->flashdata('destroy');
        }
    }

    public function delete($id)
    {
        $id_user = array('id_user' => $id);
        $res = $this->MListUser->deleteUser($id_user);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("ListUser");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("ListUser");
        }
    }
}
