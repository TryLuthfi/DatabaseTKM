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
        $id = (int) $this->input->post('id_budget_pic');
        if ($id <= 0) {
            // compatibility jika hidden lama masih id_user
            $id = (int) $this->input->post('id_user');
        }
        $namaPic = trim((string) $this->input->post('nama_user'));

        if ($namaPic === '') {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', ['Nama PIC wajib diisi.']);
            redirect('Budget_MasterPic');
            return;
        }

        if ($this->MBudget_MasterPic->existsByName($namaPic, $id)) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            $this->session->set_flashdata('validation_errors', ['Nama PIC sudah ada di master PIC.']);
            redirect('Budget_MasterPic');
            return;
        }

        if ($id > 0) {
            $ok = $this->MBudget_MasterPic->updatePic($id, $namaPic);
            $this->session->set_flashdata('status', $ok ? 'sukses_edit' : 'gagal_simpan');
            redirect('Budget_MasterPic');
            return;
        }

        $insertId = (int) $this->MBudget_MasterPic->savePic($namaPic);
        $this->session->set_flashdata('status', $insertId > 0 ? 'sukses_tambah' : 'gagal_simpan');
        redirect('Budget_MasterPic');
    }

    public function delete($id)
    {
        $success = $this->MBudget_MasterPic->deletePic((int) $id);
        $this->session->set_flashdata('status', $success ? 'sukses_hapus' : 'gagal_hapus');
        redirect('Budget_MasterPic');
    }
}

