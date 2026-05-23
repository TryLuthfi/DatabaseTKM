<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ListUser extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        enforce_budgeting_access();

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
            $data['user'] = $this->db->get('tb_master_user_new')->result_array();

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
        $nik = trim((string) $this->input->post('nik'));
        $namaUser = trim((string) $this->input->post('nama_user'));
        $isSuperAdmin = $this->isSuperAdmin();

        if ($nik === '' || $namaUser === '') {
            $this->session->set_flashdata('status', 'gagal_tambah');
            $this->session->set_flashdata('validation_errors', ['NIK dan Nama User wajib diisi.']);
            redirect("ListUser");
            return;
        }

        $existingNik = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($existingNik)) {
            $this->session->set_flashdata('status', 'gagal_tambah');
            $this->session->set_flashdata('validation_errors', ['NIK sudah terdaftar.']);
            redirect("ListUser");
            return;
        }

        if (!$isSuperAdmin) {
            $minimalData = [
                'nik' => $nik,
                'nama_karyawan' => $namaUser,
                'id_level' => 3,
                'status_user' => 'ACTIVE',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $res = $this->MListUser->addUser($minimalData);
            if ($res >= 1) {
                $this->session->set_flashdata('status', 'sukses_tambah');
            } else {
                $this->session->set_flashdata('status', 'gagal_tambah');
            }
            redirect("ListUser");
            return;
        }

        $jabatanName = (string) $this->input->post('jabatan_name');
        $statusUser = strtoupper((string) $this->input->post('status_user'));
        if ($statusUser !== 'ACTIVE' && $statusUser !== 'INACTIVE') {
            $statusUser = 'ACTIVE';
        }

        $hasil_data = array(
            'nik' => $nik,
            'nama_karyawan' => $namaUser,
            'username_user' => trim((string) $this->input->post('username_user')),
            'password_user' => (string) $this->input->post('password_user'),
            'id_level' => (int) $this->input->post('id_level'),
            'jabatan' => trim($jabatanName),
            'status_user' => $statusUser,
            'jenis_kelamin' => trim((string) $this->input->post('jenis_kelamin')),
            'divisi' => trim((string) $this->input->post('divisi')),
            'departemen' => trim((string) $this->input->post('departemen'))
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
        if (!$this->isSuperAdmin()) {
            $this->session->set_flashdata('status', 'akses_ditolak');
            redirect("ListUser");
            return;
        }

        $jabatanName = (string) $this->input->post('jabatan_name');
        $statusUser = strtoupper((string) $this->input->post('status_user'));
        if ($statusUser !== 'ACTIVE' && $statusUser !== 'INACTIVE') {
            $statusUser = 'ACTIVE';
        }

        $data_array = array(
            'nik' => trim((string) $this->input->post('nik')),
            'nama_karyawan' => trim((string) $this->input->post('nama_user')),
            'username_user' => trim((string) $this->input->post('username_user')),
            'password_user' => (string) $this->input->post('password_user'),
            'id_level' => (int) $this->input->post('id_level'),
            'jabatan' => trim($jabatanName),
            'status_user' => $statusUser,
            'jenis_kelamin' => trim((string) $this->input->post('jenis_kelamin')),
            'divisi' => trim((string) $this->input->post('divisi')),
            'departemen' => trim((string) $this->input->post('departemen'))
        );

        $where = array('id' => $id);

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
        if (!$this->isSuperAdmin()) {
            $this->session->set_flashdata('status', 'akses_ditolak');
            redirect("ListUser");
            return;
        }

        $id_user = array('id' => $id);
        $res = $this->MListUser->deleteUser($id_user);

        if ($res >= 1) {
            $this->session->set_flashdata('status', 'sukses_hapus');
            redirect("ListUser");
        } else {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect("ListUser");
        }
    }

    private function isSuperAdmin()
    {
        return (string) $this->session->userdata('nama_level') === 'Super Admin';
    }
}

