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
        $this->session->set_flashdata('status', 'fitur_dipindahkan');
        redirect('Budget_MasterPic');
    }

    public function delete($id)
    {
        $this->session->set_flashdata('status', 'fitur_dipindahkan');
        redirect('Budget_MasterPic');
    }
}
