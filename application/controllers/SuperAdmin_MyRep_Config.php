<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SuperAdmin_MyRep_Config extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MSuperAdmin_MyRep_Config');
    }

    public function index()
    {
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            show_404();
            return;
        }

        $data['title'] = 'MyRep Role & Notification';
        $data['judul'] = 'MyRep Role & Notification';
        $data['tablesReady'] = $this->MSuperAdmin_MyRep_Config->checkTablesReady();
        $data['permissionRows'] = $this->MSuperAdmin_MyRep_Config->getRolePermissions();
        $data['notificationRows'] = $this->MSuperAdmin_MyRep_Config->getNotificationRoutes();
        $data['userOptions'] = $this->MSuperAdmin_MyRep_Config->getUserOptions();
        $data['pageOptions'] = $this->MSuperAdmin_MyRep_Config->getPageOptions();
        $data['actionOptions'] = $this->MSuperAdmin_MyRep_Config->getActionOptions();
        $data['roleOptions'] = $this->MSuperAdmin_MyRep_Config->getRoleOptions();
        $data['moduleOptions'] = $this->MSuperAdmin_MyRep_Config->getModuleOptions();
        $data['eventOptions'] = $this->MSuperAdmin_MyRep_Config->getEventOptions();
        $data['targetTypeOptions'] = $this->MSuperAdmin_MyRep_Config->getTargetTypeOptions();
        $data['accessMatrix'] = $this->MSuperAdmin_MyRep_Config->getAccessMatrix($data['pageOptions'], $data['actionOptions'], $data['roleOptions']);

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('SuperAdmin_MyRep_Config/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function savePermission()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $pageKey = trim((string) $this->input->post('page_key'));
        $actionKey = strtoupper(trim((string) $this->input->post('action_key')));
        $roleKey = strtoupper(trim((string) $this->input->post('role_key')));

        if ($pageKey === '' || $actionKey === '' || $roleKey === '') {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'page_key, action_key, dan role_key wajib diisi.');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        $data = [
            'page_key' => $pageKey,
            'action_key' => $actionKey,
            'role_key' => $roleKey,
            'is_allowed' => (int) $this->input->post('is_allowed') === 1 ? 1 : 0,
            'is_active' => (int) $this->input->post('is_active') === 1 ? 1 : 0,
            'effective_start' => $this->normalizeDateTime($this->input->post('effective_start')),
            'effective_end' => $this->normalizeDateTime($this->input->post('effective_end')),
        ];

        $ok = $this->MSuperAdmin_MyRep_Config->upsertRolePermission($data);
        $this->session->set_flashdata('status', $ok ? 'sukses_edit' : 'gagal_edit');
        if (!$ok) {
            $this->session->set_flashdata('error_log', 'Gagal menyimpan role permission MyRep.');
        }
        redirect('SuperAdmin_MyRep_Config');
    }

    public function saveAccessMatrix()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $pages = $this->MSuperAdmin_MyRep_Config->getPageOptions();
        $actions = $this->MSuperAdmin_MyRep_Config->getActionOptions();
        $roles = $this->MSuperAdmin_MyRep_Config->getRoleOptions();
        $postedMatrix = (array) $this->input->post('access_matrix');

        $ok = $this->MSuperAdmin_MyRep_Config->saveAccessMatrix($pages, $actions, $roles, $postedMatrix);
        $this->session->set_flashdata('status', $ok ? 'sukses_edit' : 'gagal_edit');
        if (!$ok) {
            $this->session->set_flashdata('error_log', 'Gagal menyimpan access matrix.');
        }
        redirect('SuperAdmin_MyRep_Config');
    }

    public function deletePermission($id = 0)
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        $ok = $this->MSuperAdmin_MyRep_Config->deleteRolePermission($id);
        $this->session->set_flashdata('status', $ok ? 'sukses_hapus' : 'gagal_hapus');
        redirect('SuperAdmin_MyRep_Config');
    }

    public function saveNotificationRoute()
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $moduleName = trim((string) $this->input->post('module_name'));
        $eventName = trim((string) $this->input->post('event_name'));
        $targetType = strtoupper(trim((string) $this->input->post('target_type')));
        $targetUserId = (int) $this->input->post('target_user_id');
        $targetRole = strtoupper(trim((string) $this->input->post('target_role')));

        if ($moduleName === '' || $eventName === '' || $targetType === '') {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'module_name, event_name, dan target_type wajib diisi.');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        if ($targetType === 'FIXED_USER' && $targetUserId <= 0) {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'Untuk FIXED_USER, target user wajib dipilih.');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        if (in_array($targetType, ['CITY_ROLE', 'CLUSTER_PIC'], true) && $targetRole === '') {
            $this->session->set_flashdata('status', 'gagal_edit');
            $this->session->set_flashdata('error_log', 'Untuk CITY_ROLE/CLUSTER_PIC, target role wajib dipilih.');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        $data = [
            'module_name' => $moduleName,
            'event_name' => $eventName,
            'target_type' => $targetType,
            'target_user_id' => $targetType === 'FIXED_USER' ? $targetUserId : null,
            'target_role' => in_array($targetType, ['CITY_ROLE', 'CLUSTER_PIC'], true) ? $targetRole : null,
            'is_active' => (int) $this->input->post('is_active') === 1 ? 1 : 0,
        ];

        $ok = $this->MSuperAdmin_MyRep_Config->upsertNotificationRoute($data);
        $this->session->set_flashdata('status', $ok ? 'sukses_edit' : 'gagal_edit');
        if (!$ok) {
            $this->session->set_flashdata('error_log', 'Gagal menyimpan notification route MyRep.');
        }
        redirect('SuperAdmin_MyRep_Config');
    }

    public function deleteNotificationRoute($id = 0)
    {
        if (!$this->validateSuperAdminSession()) {
            return;
        }

        $id = (int) $id;
        if ($id <= 0) {
            $this->session->set_flashdata('status', 'gagal_hapus');
            redirect('SuperAdmin_MyRep_Config');
            return;
        }

        $ok = $this->MSuperAdmin_MyRep_Config->deleteNotificationRoute($id);
        $this->session->set_flashdata('status', $ok ? 'sukses_hapus' : 'gagal_hapus');
        redirect('SuperAdmin_MyRep_Config');
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

    private function normalizeDateTime($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $time = strtotime($value);
        if ($time === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $time);
    }
}
