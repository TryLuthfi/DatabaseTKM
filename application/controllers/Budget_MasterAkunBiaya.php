<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Budget_MasterAkunBiaya extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_budgeting_access();
        $this->load->model('MBudget_MasterAkunBiaya');
    }

    public function index()
    {

        $keyword = trim((string) $this->input->get('keyword'));

        $data['title'] = 'Budgeting - Master Item';
        $data['judul'] = 'Budgeting - Master Item';
        $data['keyword'] = $keyword;
        $data['items'] = $this->MBudget_MasterAkunBiaya->getItems($keyword);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Budget_MasterAkunBiaya/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function save()
    {
        $id = (int) $this->input->post('id_budget_item');
        $payload = $this->input->post(NULL, true);

        if (empty($payload['item_code']) || empty($payload['item_name'])) {
            $this->session->set_flashdata('status', 'gagal_simpan');
            redirect('Budget_MasterAkunBiaya');
        }

        if ($id > 0) {
            $this->MBudget_MasterAkunBiaya->updateItem($id, $payload);
            $this->session->set_flashdata('status', 'sukses_edit');
        } else {
            $this->MBudget_MasterAkunBiaya->saveItem($payload);
            $this->session->set_flashdata('status', 'sukses_tambah');
        }

        redirect('Budget_MasterAkunBiaya');
    }

    public function delete($id)
    {
        $success = $this->MBudget_MasterAkunBiaya->deleteItem((int) $id);
        $this->session->set_flashdata('status', $success ? 'sukses_hapus' : 'gagal_hapus');

        redirect('Budget_MasterAkunBiaya');
    }
}
