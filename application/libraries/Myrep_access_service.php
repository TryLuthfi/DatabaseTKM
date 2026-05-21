<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Myrep_access_service
{
    /** @var CI_Controller */
    protected $ci;

    /** @var array<string,array<int,string>> */
    protected $roleCache = [];

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

        if (!$this->ci->db->table_exists('tb_myrep_role_permission')) {
            return true;
        }

        $pageKey = trim((string) $pageKey);
        $actionKey = strtoupper(trim((string) $actionKey));
        if ($pageKey === '' || $actionKey === '') {
            return true;
        }

        $roles = $this->getCurrentRoleKeys();
        if (empty($roles)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $row = $this->ci->db
            ->select('id_permission')
            ->from('tb_myrep_role_permission')
            ->where('page_key', $pageKey)
            ->where('action_key', $actionKey)
            ->where('is_allowed', 1)
            ->where('is_active', 1)
            ->where_in('role_key', $roles)
            ->where("(effective_start IS NULL OR effective_start <= " . $this->ci->db->escape($now) . ")", null, false)
            ->where("(effective_end IS NULL OR effective_end >= " . $this->ci->db->escape($now) . ")", null, false)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row);
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
                ->select('id_map')
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

        return true;
    }
}
