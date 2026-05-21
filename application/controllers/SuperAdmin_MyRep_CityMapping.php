<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SuperAdmin_MyRep_CityMapping extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MSuperAdmin_MyRep_Config');
    }

    public function index()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $data['title'] = 'MyRep City PIC Mapping';
        $data['judul'] = 'MyRep City PIC Mapping';
        $data['tablesReady'] = $this->MSuperAdmin_MyRep_Config->checkTablesReady();
        $data['cityPicRows'] = $this->MSuperAdmin_MyRep_Config->getCityPicMappings();
        $data['userOptions'] = $this->MSuperAdmin_MyRep_Config->getUserOptions();
        $data['roleColumns'] = $this->MSuperAdmin_MyRep_Config->getCityPicRoleColumns();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('SuperAdmin_MyRep_CityMapping/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function saveBulk()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $rows = (array) $this->input->post('rows');
        if (empty($rows)) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'Tidak ada perubahan yang dikirim.');
            redirect('SuperAdmin_MyRep_CityMapping');
            return;
        }

        $result = $this->MSuperAdmin_MyRep_Config->saveCityPicMappingsBulk($rows);
        if (!empty($result['ok'])) {
            $this->session->set_flashdata('status', 'sukses_edit');
            $this->session->set_flashdata('error_log', (int) ($result['updated'] ?? 0) . ' kota berhasil diperbarui.');
            redirect('SuperAdmin_MyRep_CityMapping');
            return;
        }

        $failed = (array) ($result['failed'] ?? []);
        $this->session->set_flashdata('status', 'gagal_edit');
        $this->session->set_flashdata(
            'error_log',
            'Sebagian gagal disimpan. Baris bermasalah: ' . (empty($failed) ? '-' : implode(', ', $failed))
        );
        redirect('SuperAdmin_MyRep_CityMapping');
    }

    private function validateSuperAdminSession()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return false;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            show_404();
            return false;
        }

        return true;
    }
}

