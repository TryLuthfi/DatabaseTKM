<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Myrep_access_service
{
    /** @var CI_Controller */
    protected $ci;

    /** @var array<string,array<int,string>> */
    protected $roleCache = [];
    /** @var array<string,mixed> */
    protected $userPermissionCache = [];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->database();
        $this->ci->load->helper('access');
    }

    public function enforceView($pageKey, $message = '')
    {
        return $this->enforcePermission($pageKey, 'VIEW', $message);
    }

    public function enforcePermission($pageKey, $actionKey, $message = '')
    {
        if (!$this->hasPermission($pageKey, $actionKey)) {
            $normalizedAction = strtoupper(trim((string) $actionKey));
            if ($normalizedAction === 'VIEW') {
                if (isset($this->ci->session)) {
                    $this->ci->session->set_flashdata('error', 'Akses modul My Republik sudah dinonaktifkan.');
                }
                redirect('Dashboard');
                return false;
            }
            render_no_access($message !== '' ? $message : 'Anda tidak memiliki akses ke menu ini.');
            return false;
        }

        return true;
    }

    public function hasPermission($pageKey, $actionKey)
    {
        if (!$this->ci->session->userdata('id_user')) {
            return true;
        }

        if ((string) $this->ci->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        $pageKey = trim((string) $pageKey);
        $actionKey = strtoupper(trim((string) $actionKey));
        if ($pageKey === '' || $actionKey === '') {
            return true;
        }

        $userId = (int) $this->ci->session->userdata('id_user');
        if (function_exists('has_user_page_access')) {
            return (bool) has_user_page_access('MyRepublik', $pageKey, $actionKey, $userId);
        }

        return true;
    }

    private function getUserPermissionOverride($userId, $pageKey, $actionKey)
    {
        $userId = (int) $userId;
        if ($userId <= 0 || !$this->ci->db->table_exists('tb_myrep_user_permission')) {
            return null;
        }

        $cacheKey = $userId . '|' . $pageKey . '|' . $actionKey;
        if (array_key_exists($cacheKey, $this->userPermissionCache)) {
            return $this->userPermissionCache[$cacheKey];
        }

        $now = date('Y-m-d H:i:s');
        $row = (array) $this->ci->db
            ->select('is_allowed')
            ->from('tb_myrep_user_permission')
            ->where('id_user', $userId)
            ->where('page_key', $pageKey)
            ->where('action_key', $actionKey)
            ->where('is_active', 1)
            ->where("(effective_start IS NULL OR effective_start <= " . $this->ci->db->escape($now) . ")", null, false)
            ->where("(effective_end IS NULL OR effective_end >= " . $this->ci->db->escape($now) . ")", null, false)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            $this->userPermissionCache[$cacheKey] = null;
            return null;
        }

        $allowed = (int) ($row['is_allowed'] ?? 0) === 1;
        $this->userPermissionCache[$cacheKey] = $allowed;
        return $allowed;
    }

    public function getCurrentRoleKeys()
    {
        $userId = (int) $this->ci->session->userdata('id_user');
        if ($userId <= 0) {
            return [];
        }

        if (isset($this->roleCache[$userId])) {
            return $this->roleCache[$userId];
        }

        $user = $this->ci->db
            ->select('id, nik')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();

        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '' || !$this->ci->db->table_exists('tb_myrep_pic_mapping_city')) {
            $this->roleCache[$userId] = [];
            return [];
        }

        $checks = [
            'RPM_AREA' => 'rpm_area',
            'SM_AREA' => 'sm_area',
            'SPV_AREA' => 'spv_area',
            'SND_AREA' => 'snd_area',
            'ADMIN_AREA' => 'admin_area',
            'SND_HO' => 'snd_ho',
            'ATP_HO' => 'atp_ho',
            'RFS_HO' => 'rfs_ho',
            'SITAC_HO' => 'sitac_ho',
            'DC_HO' => 'dc_ho',
            'QA_HO' => 'qa_ho',
        ];

        $roles = [];
        foreach ($checks as $roleKey => $columnName) {
            if (!$this->ci->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                continue;
            }

            $found = (array) $this->ci->db
                ->select('1 AS hit', false)
                ->from('tb_myrep_pic_mapping_city')
                ->where($columnName, $nik)
                ->limit(1)
                ->get()
                ->row_array();

            if (!empty($found)) {
                $roles[] = $roleKey;
            }
        }

        $roles = array_values(array_unique($roles));
        $this->roleCache[$userId] = $roles;
        return $roles;
    }

    public function enforceByMethod($pageKey, $methodName, array $methodActionMap = [])
    {
        $methodName = trim((string) $methodName);
        if ($methodName === '') {
            return true;
        }

        if (isset($methodActionMap[$methodName])) {
            return $this->enforcePermission($pageKey, (string) $methodActionMap[$methodName]);
        }

        $lower = strtolower($methodName);
        if (strpos($lower, 'approve') === 0 || strpos($lower, 'reject') === 0) {
            return $this->enforcePermission($pageKey, 'APPROVAL');
        }
        if (strpos($lower, 'delete') === 0 || strpos($lower, 'remove') === 0) {
            return $this->enforcePermission($pageKey, 'HAPUS');
        }
        if (strpos($lower, 'update') === 0 || strpos($lower, 'edit') === 0) {
            return $this->enforcePermission($pageKey, 'EDIT');
        }
        if (strpos($lower, 'save') === 0 || strpos($lower, 'create') === 0 || strpos($lower, 'upload') === 0 || strpos($lower, 'import') === 0) {
            return $this->enforcePermission($pageKey, 'TAMBAH');
        }
        if (strpos($lower, 'preview') === 0 && strpos($lower, 'import') !== false) {
            return $this->enforcePermission($pageKey, 'TAMBAH');
        }

        return true;
    }
}
