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
        $data['menuModuleOptions'] = $this->MSuperAdmin_UserAccess->getMenuModuleOptions();
        $data['generalPageModuleOptions'] = $this->MSuperAdmin_UserAccess->getGeneralPageModuleOptions();
        $data['generalPageOptions'] = $this->MSuperAdmin_UserAccess->getGeneralPageOptions();
        $data['userRows'] = $this->MSuperAdmin_UserAccess->getUserRows();
        $userIds = array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['id'] ?? 0);
        }, (array) $data['userRows'])));
        $data['accessMatrix'] = $this->MSuperAdmin_UserAccess->getUserMenuModuleMatrix($userIds, (array) $data['menuModuleOptions']);

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
        $result = $this->MSuperAdmin_UserAccess->getUserMenuModulesByUser($userId);

        $payload = [
            'status' => !empty($result['ok']),
            'message' => !empty($result['ok']) ? 'OK' : (string) ($result['message'] ?? 'Gagal mengambil module access user.'),
            'data' => [
                'menu_modules' => (array) ($result['menu_modules'] ?? []),
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
        $postedModules = (array) $this->input->post('menu_modules');

        if ($userId <= 0) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'User tidak valid.');
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $moduleOptions = $this->MSuperAdmin_UserAccess->getMenuModuleOptions();
        $moduleResult = $this->MSuperAdmin_UserAccess->saveUserMenuModules($userId, $postedModules, $moduleOptions);
        if (empty($moduleResult['ok'])) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', (string) ($moduleResult['message'] ?? 'Gagal menyimpan module access user.'));
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $moduleCount = (int) ($moduleResult['module_count'] ?? 0);
        $message = 'Module access user berhasil diperbarui. Total module aktif: ' . $moduleCount . '.';

        $this->session->set_flashdata('status', 'sukses_edit');
        $this->session->set_flashdata('error_log', $message);
        redirect('SuperAdmin_UserAccess');
    }

    public function saveMatrixBulk()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $isAjax = $this->input->is_ajax_request();
        $postedMatrix = (array) $this->input->post('matrix');
        $moduleOptions = (array) $this->MSuperAdmin_UserAccess->getMenuModuleOptions();

        $result = $this->MSuperAdmin_UserAccess->saveUserMenuModulesBulk($postedMatrix, $moduleOptions);
        if (empty($result['ok'])) {
            if ($isAjax) {
                $payload = [
                    'status' => false,
                    'message' => (string) ($result['message'] ?? 'Gagal menyimpan matrix akses user.'),
                ];
                $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode($payload));
                return;
            }

            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', (string) ($result['message'] ?? 'Gagal menyimpan matrix akses user.'));
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $userCount = (int) ($result['user_count'] ?? 0);
        $grantCount = (int) ($result['grant_count'] ?? 0);
        $successMessage = 'Matrix akses berhasil disimpan. User: ' . $userCount . ', Total centang aktif: ' . $grantCount . '.';

        if ($isAjax) {
            $payload = [
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'user_count' => $userCount,
                    'grant_count' => $grantCount,
                ],
            ];
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($payload));
            return;
        }

        $this->session->set_flashdata('status', 'sukses_edit');
        $this->session->set_flashdata('error_log', $successMessage);
        redirect('SuperAdmin_UserAccess');
    }

    public function syncMyRepConfig()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $result = $this->MSuperAdmin_UserAccess->syncMyRepConfigToUserAccess();
        if (empty($result['ok'])) {
            $this->session->set_flashdata('status', 'gagal_sync');
            $this->session->set_flashdata('error_log', (string) ($result['message'] ?? 'Sinkronisasi MyRep gagal.'));
            redirect('SuperAdmin_UserAccess');
            return;
        }

        $this->session->set_flashdata('status', 'sukses_sync');
        $this->session->set_flashdata(
            'error_log',
            'Sync selesai. Role aktif: ' . (int) ($result['role_count'] ?? 0) .
            ', NIK mapping: ' . (int) ($result['nik_count'] ?? 0) .
            ', User aktif total: ' . (int) ($result['user_count'] ?? 0) .
            ' (mapping: ' . (int) ($result['mapped_user_count'] ?? 0) .
            ', manual: ' . (int) ($result['manual_user_count'] ?? 0) . ')' .
            ', Hapus MyRep lama: ' . (int) ($result['deleted_myrep'] ?? 0) .
            ', Insert MyRep: ' . (int) ($result['inserted_myrep'] ?? 0) . '.'
        );
        redirect('SuperAdmin_UserAccess');
    }

    public function getGeneralPageOptions()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $moduleKey = trim((string) $this->input->get('module_key'));
        $options = $this->MSuperAdmin_UserAccess->getGeneralPageOptions($moduleKey);
        $payload = [
            'status' => true,
            'message' => 'OK',
            'data' => [
                'page_options' => (array) $options,
            ],
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
    }

    public function getGeneralPageMatrix()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $userId = (int) $this->input->get('id_user');
        $moduleKey = trim((string) $this->input->get('module_key'));
        $pageKey = trim((string) $this->input->get('page_key'));

        $result = $this->MSuperAdmin_UserAccess->getUserGeneralPageAccessMatrix($userId, $moduleKey, $pageKey);
        $payload = [
            'status' => !empty($result['ok']),
            'message' => !empty($result['ok']) ? 'OK' : (string) ($result['message'] ?? 'Gagal mengambil detail page access.'),
            'data' => [
                'rows' => (array) ($result['rows'] ?? []),
            ],
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
    }

    public function saveGeneralPageMatrix()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $userId = (int) $this->input->post('id_user');
        $moduleKey = trim((string) $this->input->post('module_key'));
        $pageKey = trim((string) $this->input->post('page_key'));
        $matrix = (array) $this->input->post('matrix');

        $result = $this->MSuperAdmin_UserAccess->saveUserGeneralPageAccessMatrix($userId, $moduleKey, $pageKey, $matrix);
        $payload = [
            'status' => !empty($result['ok']),
            'message' => !empty($result['ok'])
                ? 'Detail page access berhasil disimpan. Total override: ' . (int) ($result['override_count'] ?? 0) . '.'
                : (string) ($result['message'] ?? 'Gagal menyimpan detail page access.'),
            'data' => [
                'override_count' => (int) ($result['override_count'] ?? 0),
            ],
        ];

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
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
