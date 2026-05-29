<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MSuperAdmin_UserAccess extends CI_Model
{
    private $defaultMenuModuleOptions = [
        'Dashboard',
        'Kontrak',
        'MyRepublik',
        'Fiberstar',
        'Logistik',
        'GeneralAffair',
        'Budgeting',
        'BILCO',
        'TargetInvoice',
        'RincianInvoice',
        'SuperAdmin',
    ];

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
            'master_user_child' => $this->db->table_exists('tb_master_user_child'),
            'level' => $this->db->table_exists('tb_level'),
            'user_page_permission' => $this->db->table_exists('tb_user_page_permission'),
        ];
    }

    public function getMenuModuleOptions()
    {
        $options = [];
        foreach ($this->defaultMenuModuleOptions as $module) {
            $value = trim((string) $module);
            if ($value !== '' && !in_array($value, $options, true)) {
                $options[] = $value;
            }
        }

        return array_values($options);
    }

    public function getPageOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'page_key', $this->defaultPageOptions);
    }

    public function getActionOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'action_key', $this->defaultActionOptions);
    }

    public function getGeneralPageModuleOptions()
    {
        if (!function_exists('get_user_page_access_module_options')) {
            return [];
        }
        return (array) get_user_page_access_module_options();
    }

    public function getGeneralPageOptions($moduleKey = '')
    {
        if (!function_exists('get_user_page_access_page_options')) {
            return [];
        }
        return (array) get_user_page_access_page_options((string) $moduleKey);
    }

    public function getUserGeneralPageAccessMatrix($userId, $moduleFilter = '', $pageFilter = '')
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.', 'rows' => []];
        }
        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.', 'rows' => []];
        }

        $user = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($user)) {
            return ['ok' => false, 'message' => 'User tidak ditemukan.', 'rows' => []];
        }

        $registryRows = $this->getGeneralPageRegistryRows($moduleFilter, $pageFilter);
        if (empty($registryRows)) {
            return ['ok' => true, 'rows' => []];
        }

        $isSuperAdmin = !empty($this->getSuperAdminUserIdMap([$userId])[$userId]);
        $userModules = $this->getUserMenuModules($userId);
        $moduleMap = [];
        foreach ($userModules as $userModule) {
            $moduleMap[trim((string) $userModule)] = true;
        }
        if (!empty($moduleMap['Project']) && empty($moduleMap['Kontrak'])) {
            $moduleMap['Kontrak'] = true;
        }
        $myRepBaselineMatrix = $this->buildMyRepBaselineMatrixForGeneralRows($userId, $registryRows);

        $overrideMap = [];
        if ($this->db->table_exists('tb_user_page_permission')) {
            $now = date('Y-m-d H:i:s');
            $pageKeys = array_values(array_unique(array_map(static function ($row) {
                return (string) ($row['page_key'] ?? '');
            }, $registryRows)));
            $pageKeys = array_values(array_filter($pageKeys, static function ($pageKey) {
                return $pageKey !== '';
            }));

            if (!empty($pageKeys)) {
                $overrideRows = (array) $this->db
                    ->select('page_key, action_key, is_allowed')
                    ->from('tb_user_page_permission')
                    ->where('id_user', $userId)
                    ->where_in('page_key', $pageKeys)
                    ->where('is_active', 1)
                    ->where("(effective_start IS NULL OR effective_start <= " . $this->db->escape($now) . ")", null, false)
                    ->where("(effective_end IS NULL OR effective_end >= " . $this->db->escape($now) . ")", null, false)
                    ->get()
                    ->result_array();

                foreach ($overrideRows as $overrideRow) {
                    $pageKey = (string) ($overrideRow['page_key'] ?? '');
                    $actionKey = strtoupper((string) ($overrideRow['action_key'] ?? ''));
                    if ($pageKey === '' || $actionKey === '') {
                        continue;
                    }
                    if (!isset($overrideMap[$pageKey])) {
                        $overrideMap[$pageKey] = [];
                    }
                    $overrideMap[$pageKey][$actionKey] = (int) ($overrideRow['is_allowed'] ?? 0) === 1 ? 1 : 0;
                }
            }
        }

        $rows = [];
        foreach ($registryRows as $registryRow) {
            $moduleKey = (string) ($registryRow['module_key'] ?? '');
            $pageKey = (string) ($registryRow['page_key'] ?? '');
            $actions = (array) ($registryRow['actions'] ?? []);
            if ($moduleKey === '' || $pageKey === '' || empty($actions)) {
                continue;
            }

            $isModuleAllowed = $isSuperAdmin || !empty($moduleMap[$moduleKey]);
            $actionState = [];
            foreach ($actions as $action) {
                $actionKey = strtoupper(trim((string) $action));
                if ($actionKey === '') {
                    continue;
                }
                $baseline = $this->resolveGeneralActionBaseline(
                    $moduleKey,
                    $pageKey,
                    $actionKey,
                    $isSuperAdmin,
                    $isModuleAllowed,
                    $myRepBaselineMatrix
                );
                $value = $baseline;
                $source = 'default';
                if (isset($overrideMap[$pageKey][$actionKey])) {
                    $value = (int) $overrideMap[$pageKey][$actionKey];
                    $source = 'override';
                }

                $actionState[$actionKey] = [
                    'value' => $value,
                    'baseline' => $baseline,
                    'source' => $source,
                ];
            }

            $rows[] = [
                'module_key' => $moduleKey,
                'page_key' => $pageKey,
                'actions' => array_values(array_unique(array_map('strtoupper', $actions))),
                'state' => $actionState,
            ];
        }

        return ['ok' => true, 'rows' => $rows];
    }

    public function saveUserGeneralPageAccessMatrix($userId, $moduleFilter, $pageFilter, array $postedMatrix)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.'];
        }
        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.'];
        }
        if (!$this->db->table_exists('tb_user_page_permission')) {
            return ['ok' => false, 'message' => 'Tabel tb_user_page_permission belum ada. Jalankan patch SQL terlebih dahulu.'];
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

        $registryRows = $this->getGeneralPageRegistryRows($moduleFilter, $pageFilter);
        if (empty($registryRows)) {
            return ['ok' => false, 'message' => 'Tidak ada page yang dipilih untuk disimpan.'];
        }

        $isSuperAdmin = !empty($this->getSuperAdminUserIdMap([$userId])[$userId]);
        $userModules = $this->getUserMenuModules($userId);
        $moduleMap = [];
        foreach ($userModules as $userModule) {
            $moduleMap[trim((string) $userModule)] = true;
        }
        if (!empty($moduleMap['Project']) && empty($moduleMap['Kontrak'])) {
            $moduleMap['Kontrak'] = true;
        }
        $myRepBaselineMatrix = $this->buildMyRepBaselineMatrixForGeneralRows($userId, $registryRows);

        $allActions = [];
        foreach ($registryRows as $registryRow) {
            $actions = (array) ($registryRow['actions'] ?? []);
            foreach ($actions as $action) {
                $actionKey = strtoupper(trim((string) $action));
                if ($actionKey !== '' && !in_array($actionKey, $allActions, true)) {
                    $allActions[] = $actionKey;
                }
            }
        }

        $pageKeys = array_values(array_unique(array_map(static function ($row) {
            return (string) ($row['page_key'] ?? '');
        }, $registryRows)));
        $pageKeys = array_values(array_filter($pageKeys, static function ($pageKey) {
            return $pageKey !== '';
        }));

        if (empty($pageKeys) || empty($allActions)) {
            return ['ok' => false, 'message' => 'Data page/action tidak valid.'];
        }

        $this->db->trans_begin();
        $this->db
            ->where('id_user', $userId)
            ->where_in('page_key', $pageKeys)
            ->where_in('action_key', $allActions)
            ->delete('tb_user_page_permission');

        $insertCount = 0;
        $modulesToGrant = [];
        foreach ($registryRows as $registryRow) {
            $moduleKey = (string) ($registryRow['module_key'] ?? '');
            $pageKey = (string) ($registryRow['page_key'] ?? '');
            $actions = array_values(array_unique(array_map('strtoupper', (array) ($registryRow['actions'] ?? []))));
            if ($moduleKey === '' || $pageKey === '' || empty($actions)) {
                continue;
            }

            $isModuleAllowed = $isSuperAdmin || !empty($moduleMap[$moduleKey]);
            foreach ($actions as $actionKey) {
                if ($actionKey === '') {
                    continue;
                }
                $baseline = $this->resolveGeneralActionBaseline(
                    $moduleKey,
                    $pageKey,
                    $actionKey,
                    $isSuperAdmin,
                    $isModuleAllowed,
                    $myRepBaselineMatrix
                );
                $newValue = !empty($postedMatrix[$pageKey][$actionKey]) ? 1 : 0;
                if ($actionKey === 'VIEW' && $newValue === 1) {
                    $modulesToGrant[$moduleKey] = true;
                }
                if ($newValue === $baseline) {
                    continue;
                }

                $this->db->insert('tb_user_page_permission', [
                    'id_user' => $userId,
                    'module_key' => $moduleKey,
                    'page_key' => $pageKey,
                    'action_key' => $actionKey,
                    'is_allowed' => $newValue,
                    'is_active' => 1,
                    'effective_start' => null,
                    'effective_end' => null,
                ]);
                $insertCount++;
            }
        }

        // Sinkronisasi UX: bila VIEW detail page aktif, modul induk otomatis ikut aktif di matrix per-user.
        if (!empty($modulesToGrant) && $this->db->table_exists('tb_master_user_child')) {
            $allowedMenuModules = array_values(array_filter(array_map(static function ($module) {
                return trim((string) $module);
            }, $this->getMenuModuleOptions()), static function ($module) {
                return $module !== '';
            }));

            foreach (array_keys($modulesToGrant) as $moduleKey) {
                $moduleKey = trim((string) $moduleKey);
                if ($moduleKey === '' || !in_array($moduleKey, $allowedMenuModules, true)) {
                    continue;
                }

                $labelsToCheck = [$moduleKey];
                if ($moduleKey === 'Kontrak') {
                    $labelsToCheck[] = 'Project';
                }

                $existing = (array) $this->db
                    ->select('id_master_user')
                    ->from('tb_master_user_child')
                    ->where('id_master_user', $userId)
                    ->where_in('validation_user', $labelsToCheck)
                    ->limit(1)
                    ->get()
                    ->row_array();

                if (!empty($existing)) {
                    continue;
                }

                $this->db->insert('tb_master_user_child', [
                    'id_master_user' => $userId,
                    'validation_user' => $moduleKey,
                ]);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Gagal menyimpan detail page access.'];
        }

        $this->db->trans_commit();
        return ['ok' => true, 'override_count' => $insertCount];
    }

    private function buildMyRepBaselineMatrixForGeneralRows($userId, array $registryRows)
    {
        $pages = [];
        $actions = [];
        foreach ($registryRows as $registryRow) {
            $moduleKey = trim((string) ($registryRow['module_key'] ?? ''));
            if (strcasecmp($moduleKey, 'MyRepublik') !== 0) {
                continue;
            }

            $pageKey = trim((string) ($registryRow['page_key'] ?? ''));
            if ($pageKey !== '' && !in_array($pageKey, $pages, true)) {
                $pages[] = $pageKey;
            }

            foreach ((array) ($registryRow['actions'] ?? []) as $action) {
                $actionKey = strtoupper(trim((string) $action));
                if ($actionKey !== '' && !in_array($actionKey, $actions, true)) {
                    $actions[] = $actionKey;
                }
            }
        }

        if (empty($pages) || empty($actions)) {
            return [];
        }

        $result = $this->buildRoleBasedMatrixForUser((int) $userId, $pages, $actions);
        return (array) ($result['matrix'] ?? []);
    }

    private function resolveGeneralActionBaseline($moduleKey, $pageKey, $actionKey, $isSuperAdmin, $isModuleAllowed, array $myRepBaselineMatrix)
    {
        if ($isSuperAdmin) {
            return 1;
        }
        if (!$isModuleAllowed) {
            return 0;
        }
        if (strcasecmp((string) $moduleKey, 'MyRepublik') === 0) {
            if (in_array(strtoupper((string) $actionKey), ['VIEW', 'TAMBAH'], true)) {
                return 1;
            }
            return !empty($myRepBaselineMatrix[(string) $pageKey][strtoupper((string) $actionKey)]) ? 1 : 0;
        }

        return 1;
    }

    public function getUserRows()
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $hasLevelTable = $this->db->table_exists('tb_level');

        $this->db
            ->from('tb_master_user_new u')
            ->select('u.id, u.nik, u.nama_karyawan, u.homebase, u.username_user, u.status_user, u.id_level');

        if ($hasLevelTable) {
            $this->db
                ->select('l.nama_level')
                ->join('tb_level l', 'l.id_level = u.id_level', 'left');
        } else {
            $this->db->select('NULL AS nama_level', false);
        }

        $this->db
            ->group_by('u.id')
            ->group_by('u.nik')
            ->group_by('u.nama_karyawan')
            ->group_by('u.homebase')
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

    public function getUserMenuModulesByUser($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.', 'menu_modules' => []];
        }

        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.', 'menu_modules' => []];
        }

        $user = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($user)) {
            return ['ok' => false, 'message' => 'User tidak ditemukan.', 'menu_modules' => []];
        }

        return [
            'ok' => true,
            'menu_modules' => $this->getUserMenuModules($userId),
        ];
    }

    public function getUserMenuModuleMatrix(array $userIds, array $moduleOptions)
    {
        $matrix = [];

        if (empty($userIds) || empty($moduleOptions)) {
            return $matrix;
        }

        if (!$this->db->table_exists('tb_master_user_child')) {
            return $matrix;
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $userIds = array_values(array_filter($userIds, static function ($userId) {
            return $userId > 0;
        }));
        if (empty($userIds)) {
            return $matrix;
        }

        $allowedModules = [];
        foreach ($moduleOptions as $moduleOption) {
            $moduleKey = trim((string) $moduleOption);
            if ($moduleKey === '') {
                continue;
            }
            if (!in_array($moduleKey, $allowedModules, true)) {
                $allowedModules[] = $moduleKey;
            }
        }
        if (empty($allowedModules)) {
            return $matrix;
        }

        $queryModules = $allowedModules;
        if (in_array('Kontrak', $allowedModules, true) && !in_array('Project', $queryModules, true)) {
            // Backward compatibility: Project lama diperlakukan sebagai Kontrak.
            $queryModules[] = 'Project';
        }

        $rows = (array) $this->db
            ->select('id_master_user, validation_user')
            ->from('tb_master_user_child')
            ->where_in('id_master_user', $userIds)
            ->where_in('validation_user', $queryModules)
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            $idMasterUser = (int) ($row['id_master_user'] ?? 0);
            $validationUser = trim((string) ($row['validation_user'] ?? ''));
            if ($validationUser === 'Project' && in_array('Kontrak', $allowedModules, true)) {
                $validationUser = 'Kontrak';
            }

            if ($idMasterUser <= 0 || $validationUser === '' || !in_array($validationUser, $allowedModules, true)) {
                continue;
            }
            if (!isset($matrix[$idMasterUser])) {
                $matrix[$idMasterUser] = [];
            }
            $matrix[$idMasterUser][$validationUser] = 1;
        }

        $superAdminUserMap = $this->getSuperAdminUserIdMap($userIds);
        if (!empty($superAdminUserMap)) {
            foreach (array_keys($superAdminUserMap) as $superAdminId) {
                if (!isset($matrix[$superAdminId])) {
                    $matrix[$superAdminId] = [];
                }
                foreach ($allowedModules as $moduleKey) {
                    $matrix[$superAdminId][$moduleKey] = 1;
                }
            }
        }

        return $matrix;
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
        $menuModules = $this->getUserMenuModules($userId);

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
            'menu_modules' => $menuModules,
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

    public function saveUserMenuModules($userId, array $selectedModules, array $moduleOptions)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return ['ok' => false, 'message' => 'User tidak valid.'];
        }

        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.'];
        }

        if (!$this->db->table_exists('tb_master_user_child')) {
            return ['ok' => false, 'message' => 'Tabel tb_master_user_child belum ada.'];
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

        $allowedModules = [];
        foreach ($moduleOptions as $module) {
            $normalizedModule = trim((string) $module);
            if ($normalizedModule !== '' && !in_array($normalizedModule, $allowedModules, true)) {
                $allowedModules[] = $normalizedModule;
            }
        }

        $selectedNormalized = [];
        foreach ($selectedModules as $module) {
            $normalizedModule = trim((string) $module);
            if ($normalizedModule === '') {
                continue;
            }
            if (!in_array($normalizedModule, $allowedModules, true)) {
                continue;
            }
            if (!in_array($normalizedModule, $selectedNormalized, true)) {
                $selectedNormalized[] = $normalizedModule;
            }
        }
        $superAdminUserMap = $this->getSuperAdminUserIdMap([$userId]);
        if (!empty($superAdminUserMap[$userId])) {
            $selectedNormalized = $allowedModules;
        }

        $this->db->trans_begin();

        if (!empty($allowedModules)) {
            $this->db
                ->where('id_master_user', $userId)
                ->where_in('validation_user', $allowedModules)
                ->delete('tb_master_user_child');
        }
        if (in_array('Kontrak', $allowedModules, true)) {
            // Bersihkan label lama agar tidak dobel.
            $this->db
                ->where('id_master_user', $userId)
                ->where('validation_user', 'Project')
                ->delete('tb_master_user_child');
        }

        foreach ($selectedNormalized as $module) {
            $this->db->insert('tb_master_user_child', [
                'id_master_user' => $userId,
                'validation_user' => $module,
            ]);
        }

        // Sinkronisasi UX (non-MyRep): jika modul dicentang, VIEW detail page ikut baseline modul.
        // Caranya: hapus override VIEW=0 pada page-page modul terkait agar kembali mengikuti default (checked).
        $this->syncDetailViewOverridesAfterModuleGrant([$userId => $selectedNormalized], $allowedModules);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Gagal menyimpan module access user.'];
        }

        $this->db->trans_commit();

        return [
            'ok' => true,
            'module_count' => count($selectedNormalized),
        ];
    }

    public function saveUserMenuModulesBulk(array $postedMatrix, array $moduleOptions)
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return ['ok' => false, 'message' => 'Tabel user tidak ditemukan.'];
        }

        if (!$this->db->table_exists('tb_master_user_child')) {
            return ['ok' => false, 'message' => 'Tabel tb_master_user_child belum ada.'];
        }

        $allowedModules = [];
        foreach ($moduleOptions as $moduleOption) {
            $moduleKey = trim((string) $moduleOption);
            if ($moduleKey === '') {
                continue;
            }
            if (!in_array($moduleKey, $allowedModules, true)) {
                $allowedModules[] = $moduleKey;
            }
        }
        if (empty($allowedModules)) {
            return ['ok' => false, 'message' => 'Daftar modul kosong.'];
        }

        $candidateUserIds = [];
        foreach ($postedMatrix as $userIdKey => $rowModules) {
            $idUser = (int) $userIdKey;
            if ($idUser > 0) {
                $candidateUserIds[] = $idUser;
            }
        }
        $candidateUserIds = array_values(array_unique($candidateUserIds));
        if (empty($candidateUserIds)) {
            return ['ok' => false, 'message' => 'Tidak ada data user yang dikirim.'];
        }

        $validUserRows = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where_in('id', $candidateUserIds)
            ->get()
            ->result_array();

        $validUserIds = [];
        foreach ($validUserRows as $validUserRow) {
            $idUser = (int) ($validUserRow['id'] ?? 0);
            if ($idUser > 0) {
                $validUserIds[$idUser] = true;
            }
        }

        if (empty($validUserIds)) {
            return ['ok' => false, 'message' => 'Data user valid tidak ditemukan.'];
        }

        $superAdminUserMap = $this->getSuperAdminUserIdMap(array_keys($validUserIds));

        $this->db->trans_begin();

        $this->db
            ->where_in('id_master_user', array_keys($validUserIds))
            ->where_in('validation_user', $allowedModules)
            ->delete('tb_master_user_child');
        if (in_array('Kontrak', $allowedModules, true)) {
            $this->db
                ->where_in('id_master_user', array_keys($validUserIds))
                ->where('validation_user', 'Project')
                ->delete('tb_master_user_child');
        }

        $insertCount = 0;
        $grantedModulesByUser = [];
        foreach ($postedMatrix as $userIdKey => $rowModules) {
            $idUser = (int) $userIdKey;
            if ($idUser <= 0 || empty($validUserIds[$idUser])) {
                continue;
            }

            $rowModules = is_array($rowModules) ? $rowModules : [];
            if (!empty($superAdminUserMap[$idUser])) {
                $rowModules = [];
                foreach ($allowedModules as $moduleKey) {
                    $rowModules[$moduleKey] = 1;
                }
            }

            $grantedModules = [];
            foreach ($allowedModules as $moduleKey) {
                if (empty($rowModules[$moduleKey])) {
                    continue;
                }
                $grantedModules[] = $moduleKey;

                $this->db->insert('tb_master_user_child', [
                    'id_master_user' => $idUser,
                    'validation_user' => $moduleKey,
                ]);
                $insertCount++;
            }
            $grantedModulesByUser[$idUser] = array_values(array_unique($grantedModules));
        }

        // Sinkronisasi UX (non-MyRep): modul dicentang -> VIEW detail page ikut centang (baseline),
        // tanpa mengubah action lain (TAMBAH/EDIT/HAPUS/APPROVAL) yang tetap manual.
        $this->syncDetailViewOverridesAfterModuleGrant($grantedModulesByUser, $allowedModules);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Gagal menyimpan matrix akses user.'];
        }

        $this->db->trans_commit();

        return [
            'ok' => true,
            'user_count' => count($validUserIds),
            'grant_count' => $insertCount,
        ];
    }

    public function syncMyRepConfigToUserAccess()
    {
        if (
            !$this->db->table_exists('tb_master_user_new') ||
            !$this->db->table_exists('tb_master_user_child') ||
            !$this->db->table_exists('tb_myrep_role_permission') ||
            !$this->db->table_exists('tb_myrep_pic_mapping_city')
        ) {
            return [
                'ok' => false,
                'message' => 'Tabel wajib sync belum lengkap (tb_master_user_new, tb_master_user_child, tb_myrep_role_permission, tb_myrep_pic_mapping_city).',
            ];
        }

        $now = date('Y-m-d H:i:s');
        $roleKeys = [];
        $roleRows = (array) $this->db
            ->distinct()
            ->select('role_key')
            ->from('tb_myrep_role_permission')
            ->where('action_key', 'VIEW')
            ->where('is_allowed', 1)
            ->where('is_active', 1)
            ->where("(effective_start IS NULL OR effective_start <= " . $this->db->escape($now) . ")", null, false)
            ->where("(effective_end IS NULL OR effective_end >= " . $this->db->escape($now) . ")", null, false)
            ->get()
            ->result_array();

        foreach ($roleRows as $roleRow) {
            $roleKey = strtoupper(trim((string) ($roleRow['role_key'] ?? '')));
            if ($roleKey === '' || !isset($this->roleColumnMap[$roleKey])) {
                continue;
            }
            if (!in_array($roleKey, $roleKeys, true)) {
                $roleKeys[] = $roleKey;
            }
        }

        if (empty($roleKeys)) {
            return [
                'ok' => false,
                'message' => 'Tidak ada role VIEW aktif pada SuperAdmin_MyRep_Config.',
            ];
        }

        $mappedNiks = [];
        foreach ($roleKeys as $roleKey) {
            $columnName = (string) ($this->roleColumnMap[$roleKey] ?? '');
            if ($columnName === '' || !$this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                continue;
            }

            $nikRows = (array) $this->db
                ->distinct()
                ->select($columnName . ' AS nik')
                ->from('tb_myrep_pic_mapping_city')
                ->where($columnName . ' IS NOT NULL', null, false)
                ->where($columnName . " <> ''", null, false)
                ->get()
                ->result_array();

            foreach ($nikRows as $nikRow) {
                foreach (myrep_pic_nik_list($nikRow['nik'] ?? '') as $nik) {
                    $mappedNiks[$nik] = true;
                }
            }
        }

        if (empty($mappedNiks)) {
            return [
                'ok' => false,
                'message' => 'Tidak ada NIK yang terpetakan pada SuperAdmin_MyRep_CityMapping untuk role aktif.',
            ];
        }

        $userRows = (array) $this->db
            ->select('id')
            ->from('tb_master_user_new')
            ->where_in('nik', array_keys($mappedNiks))
            ->where('status_user', 'ACTIVE')
            ->get()
            ->result_array();

        $mappedUserIds = [];
        foreach ($userRows as $userRow) {
            $idUser = (int) ($userRow['id'] ?? 0);
            if ($idUser > 0 && !in_array($idUser, $mappedUserIds, true)) {
                $mappedUserIds[] = $idUser;
            }
        }

        // Manual exception:
        // user yang sudah dipatenkan via detail access MyRep (override VIEW=1) tetap dipertahankan saat sync.
        $manualUserIds = [];
        if ($this->db->table_exists('tb_user_page_permission')) {
            $now = date('Y-m-d H:i:s');
            $manualRows = (array) $this->db
                ->distinct()
                ->select('id_user')
                ->from('tb_user_page_permission')
                ->where('module_key', 'MyRepublik')
                ->where('action_key', 'VIEW')
                ->where('is_allowed', 1)
                ->where('is_active', 1)
                ->where("(effective_start IS NULL OR effective_start <= " . $this->db->escape($now) . ")", null, false)
                ->where("(effective_end IS NULL OR effective_end >= " . $this->db->escape($now) . ")", null, false)
                ->get()
                ->result_array();

            $candidateManualIds = [];
            foreach ($manualRows as $manualRow) {
                $manualId = (int) ($manualRow['id_user'] ?? 0);
                if ($manualId > 0) {
                    $candidateManualIds[] = $manualId;
                }
            }
            $candidateManualIds = array_values(array_unique($candidateManualIds));

            if (!empty($candidateManualIds)) {
                $activeManualRows = (array) $this->db
                    ->select('id')
                    ->from('tb_master_user_new')
                    ->where_in('id', $candidateManualIds)
                    ->where('status_user', 'ACTIVE')
                    ->get()
                    ->result_array();

                foreach ($activeManualRows as $activeManualRow) {
                    $manualId = (int) ($activeManualRow['id'] ?? 0);
                    if ($manualId > 0 && !in_array($manualId, $manualUserIds, true)) {
                        $manualUserIds[] = $manualId;
                    }
                }
            }
        }

        $targetUserIds = array_values(array_unique(array_merge($mappedUserIds, $manualUserIds)));

        $this->db->trans_begin();

        $this->db
            ->where('validation_user', 'MyRepublik')
            ->delete('tb_master_user_child');
        $deletedMyRep = (int) $this->db->affected_rows();

        $insertedMyRep = 0;

        foreach ($targetUserIds as $idUser) {
            $this->db->insert('tb_master_user_child', [
                'id_master_user' => (int) $idUser,
                'validation_user' => 'MyRepublik',
            ]);
            $insertedMyRep++;
        }

        $allSuperAdminMap = $this->getSuperAdminUserIdMap();
        if (!empty($allSuperAdminMap)) {
            $superAdminIds = array_keys($allSuperAdminMap);
            $allModules = $this->getMenuModuleOptions();

            if (!empty($allModules)) {
                $this->db
                    ->where_in('id_master_user', $superAdminIds)
                    ->where_in('validation_user', $allModules)
                    ->delete('tb_master_user_child');

                foreach ($superAdminIds as $superAdminId) {
                    foreach ($allModules as $moduleKey) {
                        $this->db->insert('tb_master_user_child', [
                            'id_master_user' => (int) $superAdminId,
                            'validation_user' => (string) $moduleKey,
                        ]);
                    }
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Transaksi sync gagal diproses.'];
        }

        $this->db->trans_commit();

        return [
            'ok' => true,
            'role_count' => count($roleKeys),
            'nik_count' => count($mappedNiks),
            'user_count' => count($targetUserIds),
            'mapped_user_count' => count($mappedUserIds),
            'manual_user_count' => count($manualUserIds),
            'deleted_myrep' => $deletedMyRep,
            'inserted_myrep' => $insertedMyRep,
        ];
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

    private function getUserMenuModules($userId)
    {
        $userId = (int) $userId;
        if ($userId <= 0 || !$this->db->table_exists('tb_master_user_child')) {
            return [];
        }

        $rows = (array) $this->db
            ->distinct()
            ->select('validation_user')
            ->from('tb_master_user_child')
            ->where('id_master_user', $userId)
            ->order_by('validation_user', 'ASC')
            ->get()
            ->result_array();

        $modules = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row['validation_user'] ?? ''));
            if ($value === '') {
                continue;
            }
            if ($value === 'Project') {
                $value = 'Kontrak';
            }
            $modules[] = $value;
        }

        return array_values(array_unique($modules));
    }

    private function syncDetailViewOverridesAfterModuleGrant(array $grantedModulesByUser, array $allModuleOptions = [])
    {
        if (empty($grantedModulesByUser)) {
            return;
        }
        if (!function_exists('get_user_page_access_registry')) {
            return;
        }
        if (!$this->db->table_exists('tb_user_page_permission')) {
            return;
        }

        $registry = (array) get_user_page_access_registry();
        if (empty($registry)) {
            return;
        }

        $pageKeysByModule = [];
        foreach ($registry as $entry) {
            $moduleKey = trim((string) ($entry['module_key'] ?? ''));
            $pageKey = trim((string) ($entry['page_key'] ?? ''));
            if ($moduleKey === '' || $pageKey === '') {
                continue;
            }

            // Dikecualikan: MyRep tetap dikelola oleh mapping/role flow.
            if (strcasecmp($moduleKey, 'MyRepublik') === 0) {
                continue;
            }

            if (!isset($pageKeysByModule[$moduleKey])) {
                $pageKeysByModule[$moduleKey] = [];
            }
            if (!in_array($pageKey, $pageKeysByModule[$moduleKey], true)) {
                $pageKeysByModule[$moduleKey][] = $pageKey;
            }
        }

        if (empty($pageKeysByModule)) {
            return;
        }

        $controlledModules = [];
        foreach ($allModuleOptions as $moduleKeyRaw) {
            $moduleKey = trim((string) $moduleKeyRaw);
            if ($moduleKey === '' || strcasecmp($moduleKey, 'MyRepublik') === 0) {
                continue;
            }
            if (!in_array($moduleKey, $controlledModules, true)) {
                $controlledModules[] = $moduleKey;
            }
        }
        if (empty($controlledModules)) {
            $controlledModules = array_keys($pageKeysByModule);
        }

        foreach ($grantedModulesByUser as $userId => $modules) {
            $idUser = (int) $userId;
            if ($idUser <= 0 || !is_array($modules)) {
                continue;
            }

            $selectedModuleMap = [];
            foreach ($modules as $moduleKeyRaw) {
                $moduleKey = trim((string) $moduleKeyRaw);
                if ($moduleKey === '') {
                    continue;
                }
                $selectedModuleMap[$moduleKey] = true;
            }

            $grantPages = [];
            $revokePages = [];
            foreach ($controlledModules as $controlledModule) {
                if (!isset($pageKeysByModule[$controlledModule])) {
                    continue;
                }
                if (!empty($selectedModuleMap[$controlledModule])) {
                    $grantPages = array_merge($grantPages, $pageKeysByModule[$controlledModule]);
                    continue;
                }
                $revokePages = array_merge($revokePages, $pageKeysByModule[$controlledModule]);
            }

            $grantPages = array_values(array_unique(array_filter($grantPages, static function ($pageKey) {
                return trim((string) $pageKey) !== '';
            })));
            $revokePages = array_values(array_unique(array_filter($revokePages, static function ($pageKey) {
                return trim((string) $pageKey) !== '';
            })));

            if (!empty($grantPages)) {
                // Modul dicentang => buang override deny VIEW agar kembali baseline modul (checked).
                $this->db
                    ->where('id_user', $idUser)
                    ->where_in('page_key', $grantPages)
                    ->where('action_key', 'VIEW')
                    ->where('is_allowed', 0)
                    ->delete('tb_user_page_permission');
            }

            if (!empty($revokePages)) {
                // Modul di-uncheck => hapus seluruh override VIEW pada page modul tsb agar baseline jadi unchecked.
                $this->db
                    ->where('id_user', $idUser)
                    ->where_in('page_key', $revokePages)
                    ->where('action_key', 'VIEW')
                    ->delete('tb_user_page_permission');
            }
        }
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
                ->where(myrep_pic_column_contains_sql($this->db, '`' . $columnName . '`', $nik), null, false)
                ->limit(1)
                ->get()
                ->row_array();

            if (!empty($found)) {
                $roles[] = $roleKey;
            }
        }

        return array_values(array_unique($roles));
    }

    private function getGeneralPageRegistryRows($moduleFilter = '', $pageFilter = '')
    {
        if (!function_exists('get_user_page_access_registry')) {
            return [];
        }

        $moduleFilter = trim((string) $moduleFilter);
        $pageFilter = trim((string) $pageFilter);
        $rows = [];
        foreach ((array) get_user_page_access_registry() as $entry) {
            $moduleKey = trim((string) ($entry['module_key'] ?? ''));
            $pageKey = trim((string) ($entry['page_key'] ?? ''));
            $actionsRaw = (array) ($entry['actions'] ?? []);
            $actions = [];
            foreach ($actionsRaw as $action) {
                $actionKey = strtoupper(trim((string) $action));
                if ($actionKey !== '' && !in_array($actionKey, $actions, true)) {
                    $actions[] = $actionKey;
                }
            }

            if ($moduleKey === '' || $pageKey === '' || empty($actions)) {
                continue;
            }
            if ($moduleFilter !== '' && strcasecmp($moduleKey, $moduleFilter) !== 0) {
                continue;
            }
            if ($pageFilter !== '' && strcasecmp($pageKey, $pageFilter) !== 0) {
                continue;
            }

            $rows[] = [
                'module_key' => $moduleKey,
                'page_key' => $pageKey,
                'actions' => $actions,
            ];
        }

        return $rows;
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

    private function getSuperAdminUserIdMap(array $userIds = [])
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $hasLevelTable = $this->db->table_exists('tb_level');
        $this->db
            ->select('u.id')
            ->from('tb_master_user_new u');

        if (!empty($userIds)) {
            $userIds = array_values(array_unique(array_map('intval', $userIds)));
            $userIds = array_values(array_filter($userIds, static function ($userId) {
                return $userId > 0;
            }));
            if (empty($userIds)) {
                return [];
            }
            $this->db->where_in('u.id', $userIds);
        }

        if ($hasLevelTable) {
            $this->db->join('tb_level l', 'l.id_level = u.id_level', 'left');
            $this->db->where("LOWER(TRIM(COALESCE(l.nama_level, ''))) = 'super admin'", null, false);
        } else {
            $this->db->where('u.id_level', 1);
        }

        $rows = (array) $this->db->get()->result_array();
        $map = [];
        foreach ($rows as $row) {
            $idUser = (int) ($row['id'] ?? 0);
            if ($idUser > 0) {
                $map[$idUser] = true;
            }
        }

        return $map;
    }
}
