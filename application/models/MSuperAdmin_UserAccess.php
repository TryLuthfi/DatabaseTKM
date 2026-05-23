<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MSuperAdmin_UserAccess extends CI_Model
{
    private $defaultPageOptions = [
        'BAK_MyRep',
        'VALSAL_MyRep',
        'Batch_Approval_MyRep',
        'DRM_MyRep',
        'Implementasi_BOQ_MyRep',
        'PO_MyRep',
        'Monitoring_RFS_MyRep',
        'ATP_MyRep',
        'Checklist_Dokument_MyRep',
    ];

    private $defaultActionOptions = [
        'VIEW',
        'TAMBAH',
        'EDIT',
        'HAPUS',
        'APPROVAL',
        'APPROVAL_DAILY',
        'APPROVAL_FOTO_COMPLY',
    ];

    private $roleColumnMap = [
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

    public function checkTablesReady()
    {
        return [
            'master_user_new' => $this->db->table_exists('tb_master_user_new'),
            'myrep_role_permission' => $this->db->table_exists('tb_myrep_role_permission'),
            'myrep_user_permission' => $this->db->table_exists('tb_myrep_user_permission'),
            'city_mapping' => $this->db->table_exists('tb_myrep_pic_mapping_city'),
            'level' => $this->db->table_exists('tb_level'),
        ];
    }

    public function getPageOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'page_key', $this->defaultPageOptions);
    }

    public function getActionOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'action_key', $this->defaultActionOptions);
    }

    public function getUserRows()
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $hasLevelTable = $this->db->table_exists('tb_level');
        $hasUserPermissionTable = $this->db->table_exists('tb_myrep_user_permission');

        $this->db
            ->from('tb_master_user_new u')
            ->select('u.id, u.nik, u.nama_karyawan, u.username_user, u.status_user, u.id_level');

        if ($hasLevelTable) {
            $this->db
                ->select('l.nama_level')
                ->join('tb_level l', 'l.id_level = u.id_level', 'left');
        } else {
            $this->db->select('NULL AS nama_level', false);
        }

        if ($hasUserPermissionTable) {
            $now = date('Y-m-d H:i:s');
            $this->db
                ->select('COUNT(up.id_user_permission) AS custom_rules_count', false)
                ->join(
                    'tb_myrep_user_permission up',
                    "up.id_user = u.id
                     AND up.is_active = 1
                     AND (up.effective_start IS NULL OR up.effective_start <= " . $this->db->escape($now) . ")
                     AND (up.effective_end IS NULL OR up.effective_end >= " . $this->db->escape($now) . ")",
                    'left',
                    false
                );
        } else {
            $this->db->select('0 AS custom_rules_count', false);
        }

        $this->db
            ->group_by('u.id')
            ->group_by('u.nik')
            ->group_by('u.nama_karyawan')
            ->group_by('u.username_user')
            ->group_by('u.status_user')
            ->group_by('u.id_level');

        if ($hasLevelTable) {
            $this->db->group_by('l.nama_level');
        }

        return (array) $this->db
            ->order_by('u.nama_karyawan', 'ASC')
            ->get()
            ->result_array();
    }

    public function getUserPermissionMatrix($userId, array $pages, array $actions)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.'];
        }

        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.'];
        }

        $user = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($user)) {
            return ['ok' => false, 'message' => 'User tidak ditemukan.'];
        }

        $baseResult = $this->buildRoleBasedMatrixForUser($userId, $pages, $actions);
        $matrix = $baseResult['matrix'];
        $roleKeys = $baseResult['role_keys'];
        $hasCustom = false;

        if ($this->db->table_exists('tb_myrep_user_permission')) {
            $now = date('Y-m-d H:i:s');
            $rows = (array) $this->db
                ->select('page_key, action_key, is_allowed')
                ->from('tb_myrep_user_permission')
                ->where('id_user', $userId)
                ->where('is_active', 1)
                ->where("(effective_start IS NULL OR effective_start <= " . $this->db->escape($now) . ")", null, false)
                ->where("(effective_end IS NULL OR effective_end >= " . $this->db->escape($now) . ")", null, false)
                ->get()
                ->result_array();

            if (!empty($rows)) {
                $hasCustom = true;
            }

            foreach ($rows as $row) {
                $pageKey = (string) ($row['page_key'] ?? '');
                $actionKey = strtoupper((string) ($row['action_key'] ?? ''));
                if (!isset($matrix[$pageKey][$actionKey])) {
                    continue;
                }
                $matrix[$pageKey][$actionKey] = (int) ($row['is_allowed'] ?? 0) === 1 ? 1 : 0;
            }
        }

        return [
            'ok' => true,
            'has_custom' => $hasCustom,
            'role_keys' => $roleKeys,
            'matrix' => $matrix,
        ];
    }

    public function saveUserPermissionMatrix($userId, array $pages, array $actions, array $postedMatrix)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.'];
        }

        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.'];
        }

        if (!$this->db->table_exists('tb_myrep_user_permission')) {
            return ['ok' => false, 'message' => 'Tabel tb_myrep_user_permission belum ada. Jalankan patch SQL terlebih dahulu.'];
        }

        $user = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($user)) {
            return ['ok' => false, 'message' => 'User tidak ditemukan.'];
        }

        $baseResult = $this->buildRoleBasedMatrixForUser($userId, $pages, $actions);
        $baseMatrix = (array) ($baseResult['matrix'] ?? []);

        $this->db->trans_begin();
        $this->db->where('id_user', $userId)->delete('tb_myrep_user_permission');

        $customCount = 0;
        foreach ($pages as $page) {
            $pageKey = (string) $page;
            if ($pageKey === '') {
                continue;
            }

            foreach ($actions as $action) {
                $actionKey = strtoupper((string) $action);
                if ($actionKey === '') {
                    continue;
                }

                $newValue = !empty($postedMatrix[$pageKey][$actionKey]) ? 1 : 0;
                $baseValue = !empty($baseMatrix[$pageKey][$actionKey]) ? 1 : 0;
                if ($newValue === $baseValue) {
                    continue;
                }

                $this->db->insert('tb_myrep_user_permission', [
                    'id_user' => $userId,
                    'page_key' => $pageKey,
                    'action_key' => $actionKey,
                    'is_allowed' => $newValue,
                    'is_active' => 1,
                    'effective_start' => null,
                    'effective_end' => null,
                ]);
                $customCount++;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Transaksi database gagal.'];
        }

        $this->db->trans_commit();
        return ['ok' => true, 'custom_count' => $customCount];
    }

    public function resetUserPermissionMatrix($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.'];
        }

        if (!$this->db->table_exists('tb_myrep_user_permission')) {
            return ['ok' => false, 'message' => 'Tabel tb_myrep_user_permission belum ada.'];
        }

        $this->db->where('id_user', $userId)->delete('tb_myrep_user_permission');
        return ['ok' => true];
    }

    private function buildRoleBasedMatrixForUser($userId, array $pages, array $actions)
    {
        $matrix = [];
        foreach ($pages as $page) {
            $pageKey = (string) $page;
            if ($pageKey === '') {
                continue;
            }
            $matrix[$pageKey] = [];
            foreach ($actions as $action) {
                $actionKey = strtoupper((string) $action);
                if ($actionKey === '') {
                    continue;
                }
                $matrix[$pageKey][$actionKey] = 0;
            }
        }

        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return ['matrix' => $matrix, 'role_keys' => []];
        }

        $roleKeys = $this->getUserRoleKeys($userId);
        if (empty($roleKeys)) {
            return ['matrix' => $matrix, 'role_keys' => []];
        }

        $now = date('Y-m-d H:i:s');
        $rows = (array) $this->db
            ->select('page_key, action_key')
            ->from('tb_myrep_role_permission')
            ->where_in('page_key', $pages)
            ->where_in('action_key', array_map('strtoupper', $actions))
            ->where_in('role_key', $roleKeys)
            ->where('is_allowed', 1)
            ->where('is_active', 1)
            ->where("(effective_start IS NULL OR effective_start <= " . $this->db->escape($now) . ")", null, false)
            ->where("(effective_end IS NULL OR effective_end >= " . $this->db->escape($now) . ")", null, false)
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            $pageKey = (string) ($row['page_key'] ?? '');
            $actionKey = strtoupper((string) ($row['action_key'] ?? ''));
            if (!isset($matrix[$pageKey][$actionKey])) {
                continue;
            }
            $matrix[$pageKey][$actionKey] = 1;
        }

        return ['matrix' => $matrix, 'role_keys' => $roleKeys];
    }

    private function getUserRoleKeys($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0 || !$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $user = (array) $this->db
            ->select('nik')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        $nik = trim((string) ($user['nik'] ?? ''));
        if ($nik === '' || !$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $roles = [];
        foreach ($this->roleColumnMap as $roleKey => $columnName) {
            if (!$this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                continue;
            }

            $found = (array) $this->db
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

        return array_values(array_unique($roles));
    }

    private function mergeDistinctColumnOptions($tableName, $columnName, array $defaults)
    {
        $options = [];
        foreach ($defaults as $defaultValue) {
            $value = trim((string) $defaultValue);
            if ($value !== '' && !in_array($value, $options, true)) {
                $options[] = $value;
            }
        }

        if ($this->db->table_exists($tableName) && $this->db->field_exists($columnName, $tableName)) {
            $rows = (array) $this->db
                ->distinct()
                ->select($columnName)
                ->from($tableName)
                ->order_by($columnName, 'ASC')
                ->get()
                ->result_array();

            foreach ($rows as $row) {
                $value = trim((string) ($row[$columnName] ?? ''));
                if ($value !== '' && !in_array($value, $options, true)) {
                    $options[] = $value;
                }
            }
        }

        return array_values($options);
    }
}

