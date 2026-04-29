<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_MasterPic extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_budgeting_access();
        $this->load->model('MBudget_MasterPic');
    }

    public function index()
    {
        $keyword = trim((string) $this->input->get('keyword'));

        $data['title'] = 'Budgeting - Master PIC';
        $data['judul'] = 'Budgeting - Master PIC';
        $data['keyword'] = $keyword;
        $data['pics'] = $this->MBudget_MasterPic->getPics($keyword);
        $data['availableUsers'] = $this->MBudget_MasterPic->getAvailableUsers();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_MasterPic/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        $id = (int) $this->input->post('id_user');
        $namaUser = trim((string) $this->input->post('nama_user'));

        if ($namaUser === '') {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', ['Nama user wajib diisi.']);
            redirect('Budget_MasterPic');
        }

        if ($this->MBudget_MasterPic->existsByName($namaUser, $id)) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', ['Nama user PIC sudah terdaftar di master PIC.']);
            redirect('Budget_MasterPic');
        }

        if ($id > 0) {
            $this->MBudget_MasterPic->updatePic($id, $namaUser);
            $this->session->set_flashdata('status', 'sukses_edit');
        } else {
            $this->MBudget_MasterPic->savePic($namaUser);
            $this->session->set_flashdata('status', 'sukses_tambah');
        }

        redirect('Budget_MasterPic');
    }

    public function delete($id)
    {
        $success = $this->MBudget_MasterPic->deletePic((int) $id);
        $this->session->set_flashdata('status', $success ? 'sukses_hapus' : 'gagal_hapus');
        redirect('Budget_MasterPic');
    }
}
