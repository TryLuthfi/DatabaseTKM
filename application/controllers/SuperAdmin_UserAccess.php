<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SuperAdmin_UserAccess extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MSuperAdmin_UserAccess');
    }

    public function index()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $data['title'] = 'Super Admin - User Role Access';
        $data['judul'] = 'Super Admin - User Role Access';
        $data['tablesReady'] = $this->MSuperAdmin_UserAccess->checkTablesReady();
        $data['pageOptions'] = $this->MSuperAdmin_UserAccess->getPageOptions();
        $data['actionOptions'] = $this->MSuperAdmin_UserAccess->getActionOptions();
        $data['userRows'] = $this->MSuperAdmin_UserAccess->getUserRows();

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('SuperAdmin_UserAccess/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function getUserMatrix($userId = 0)
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $userId = (int) $userId;
        $pageOptions = $this->MSuperAdmin_UserAccess->getPageOptions();
        $actionOptions = $this->MSuperAdmin_UserAccess->getActionOptions();
        $result = $this->MSuperAdmin_UserAccess->getUserPermissionMatrix($userId, $pageOptions, $actionOptions);

        $payload = [
            'status' => !empty($result['ok']),
            'message' => !empty($result['ok']) ? 'OK' : (string) ($result['message'] ?? 'Gagal mengambil matrix user.'),
            'data' => [
                'has_custom' => !empty($result['has_custom']) ? 1 : 0,
                'role_keys' => (array) ($result['role_keys'] ?? []),
                'matrix' => (array) ($result['matrix'] ?? []),
            ],
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
    }

    public function saveMatrix()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $userId = (int) $this->input->post('id_master_user');
        $postedMatrix = (array) $this->input->post('user_matrix');

        if ($userId <= 0) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'User tidak valid.');
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $pageOptions = $this->MSuperAdmin_UserAccess->getPageOptions();
        $actionOptions = $this->MSuperAdmin_UserAccess->getActionOptions();
        $result = $this->MSuperAdmin_UserAccess->saveUserPermissionMatrix(
            $userId,
            $pageOptions,
            $actionOptions,
            $postedMatrix
        );

        if (!empty($result['ok'])) {
            $customCount = (int) ($result['custom_count'] ?? 0);
            $this->session->set_flashdata('status', 'sukses_edit');
            $this->session->set_flashdata('error_log', 'Akses user berhasil diperbarui. Custom override tersimpan: ' . $customCount . '.');
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $this->session->set_flashdata('status', 'gagal_edit');
        $this->session->set_flashdata('error_log', (string) ($result['message'] ?? 'Gagal menyimpan akses user.'));
        redirect('SuperAdmin_UserAccess');
    }

    public function resetMatrix($userId = 0)
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $userId = (int) $userId;
        $result = $this->MSuperAdmin_UserAccess->resetUserPermissionMatrix($userId);

        $payload = [
            'status' => !empty($result['ok']),
            'message' => !empty($result['ok']) ? 'Custom override user berhasil direset ke role default.' : (string) ($result['message'] ?? 'Gagal reset override user.'),
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
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

