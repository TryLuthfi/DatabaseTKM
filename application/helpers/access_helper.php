<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_validation_user_list')) {
    function get_validation_user_list()
    {
        sync_current_user_access_session();

        $CI = &get_instance();
        $raw = $CI->session->userdata('validation_user');

        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = explode(',', (string) $raw);
        }

        $items = array_map('trim', $items);
        $items = array_filter($items, static function ($item) {
            return $item !== '';
        });

        return array_values(array_unique($items));
    }
}

if (!function_exists('sync_current_user_access_session')) {
    function sync_current_user_access_session($force = false)
    {
        static $syncedInRequest = false;
        if ($syncedInRequest && !$force) {
            return;
        }

        $CI = &get_instance();
        if (!isset($CI->session) || !isset($CI->db)) {
            return;
        }

        $userId = (int) $CI->session->userdata('id_user');
        if ($userId <= 0) {
            return;
        }

        $now = time();
        $lastSyncAt = (int) $CI->session->userdata('access_sync_at');
        $syncIntervalSeconds = 60;
        if (!$force && $lastSyncAt > 0 && ($now - $lastSyncAt) < $syncIntervalSeconds) {
            $syncedInRequest = true;
            return;
        }

        $hasValidationUserColumn = $CI->db->field_exists('validation_user', 'tb_master_user_new');
        $selectValidation = $hasValidationUserColumn ? ', a.validation_user' : ', NULL AS validation_user';

        $akun = (array) $CI->db
            ->select('a.id, a.id_level, a.nama_karyawan, a.username_user, a.password_user' . $selectValidation . ', COALESCE(a.lokasi_kantor, a.homebase, \'HO\') as lokasi_user, tl.nama_level', false)
            ->from('tb_master_user_new a')
            ->join('tb_level tl', 'a.id_level = tl.id_level', 'left')
            ->where('a.id', $userId)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($akun)) {
            return;
        }

        $validationRows = $CI->db
            ->select('validation_user')
            ->from('tb_master_user_child')
            ->where('id_master_user', $userId)
            ->get()
            ->result_array();

        $validationList = [];
        if (!empty($akun['validation_user'])) {
            $validationList[] = trim((string) $akun['validation_user']);
        }
        foreach ($validationRows as $row) {
            $value = trim((string) ($row['validation_user'] ?? ''));
            if ($value !== '') {
                $validationList[] = $value;
            }
        }
        $validationList = array_values(array_unique(array_filter($validationList)));

        $CI->session->set_userdata([
            'id_level' => (int) ($akun['id_level'] ?? 0),
            'nama_user' => (string) ($akun['nama_karyawan'] ?? ''),
            'username_user' => (string) ($akun['username_user'] ?? ''),
            'password_user' => (string) ($akun['password_user'] ?? ''),
            'lokasi_user' => (string) ($akun['lokasi_user'] ?? 'HO'),
            'nama_level' => (string) ($akun['nama_level'] ?? ''),
            'validation' => !empty($validationList) ? implode(', ', $validationList) : 'non',
            'validation_user' => $validationList,
            'access_sync_at' => $now,
        ]);

        $syncedInRequest = true;
    }
}

if (!function_exists('render_no_access')) {
    function render_no_access($message = '')
    {
        $CI = &get_instance();

        $data = [
            'message' => $message !== '' ? $message : 'You tried to access a page you did not have prior authorization for.',
        ];

        $html = $CI->load->view('errors/html/no_access', $data, true);

        $CI->output->set_status_header(403);
        $CI->output->set_content_type('text/html', 'utf-8');
        $CI->output->set_output($html);
        $CI->output->_display();

        exit;
    }
}

if (!function_exists('has_validation_access')) {
    function has_validation_access($module)
    {
        $module = trim((string) $module);
        if ($module === '') {
            return false;
        }

        return in_array($module, get_validation_user_list(), true);
    }
}

if (!function_exists('normalize_validation_key')) {
    function normalize_validation_key($value)
    {
        return strtolower(str_replace(' ', '_', trim((string) $value)));
    }
}

if (!function_exists('get_validation_key_list')) {
    function get_validation_key_list()
    {
        $keys = [];
        foreach (get_validation_user_list() as $item) {
            $normalized = normalize_validation_key($item);
            if ($normalized !== '' && !in_array($normalized, $keys, true)) {
                $keys[] = $normalized;
            }
        }

        if (!empty($keys)) {
            return $keys;
        }

        $CI = &get_instance();
        $fallback = normalize_validation_key((string) $CI->session->userdata('validation'));
        return $fallback !== '' ? [$fallback] : [];
    }
}

if (!function_exists('has_validation_key')) {
    function has_validation_key($key)
    {
        $normalized = normalize_validation_key($key);
        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, get_validation_key_list(), true);
    }
}

if (!function_exists('enforce_budgeting_access')) {
    function enforce_budgeting_access()
    {
        $CI = &get_instance();

        if (!$CI->session->userdata('id_user')) {
            redirect('Auth');
        }

        if ($CI->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!has_validation_access('Budgeting')) {
            render_no_access('Anda tidak memiliki akses ke menu Budgeting.');
        }

        return true;
    }
}

if (!function_exists('enforce_module_access')) {
    function enforce_module_access($module, $errorMessage = '')
    {
        $CI = &get_instance();

        if (!$CI->session->userdata('id_user')) {
            redirect('Auth');
        }

        if ($CI->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!has_validation_access($module)) {
            render_no_access(
                $errorMessage !== '' ? $errorMessage : 'Anda tidak memiliki akses ke modul ini.'
            );
        }

        if (function_exists('enforce_current_page_action_access')) {
            enforce_current_page_action_access();
        }

        return true;
    }
}

if (!function_exists('enforce_current_page_action_access')) {
    function enforce_current_page_action_access()
    {
        sync_current_user_access_session();

        $CI = &get_instance();

        if (!$CI->session->userdata('id_user')) {
            redirect('Auth');
        }

        if ((string) $CI->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        if (!function_exists('get_user_page_access_controller_map') || !function_exists('resolve_user_page_access_action') || !function_exists('has_user_page_access')) {
            return true;
        }

        $controller = strtoupper(trim((string) $CI->router->fetch_class()));
        $method = (string) $CI->router->fetch_method();
        $map = (array) get_user_page_access_controller_map();
        if ($controller === '' || !isset($map[$controller])) {
            return true;
        }

        $entry = (array) $map[$controller];
        $moduleKey = trim((string) ($entry['module_key'] ?? ''));
        $pageKey = trim((string) ($entry['page_key'] ?? ''));
        if ($moduleKey === '' || $pageKey === '') {
            return true;
        }

        $actionKey = resolve_user_page_access_action($method);
        if (has_user_page_access($moduleKey, $pageKey, $actionKey)) {
            return true;
        }

        $isAjax = method_exists($CI->input, 'is_ajax_request') && $CI->input->is_ajax_request();
        if ($isAjax) {
            $CI->output
                ->set_status_header(403)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Anda tidak memiliki izin untuk aksi ini.'
                ]));
            $CI->output->_display();
            exit;
        }

        render_no_access('Anda tidak memiliki izin untuk aksi ini.');
        return false;
    }
}

if (!function_exists('enforce_bilco_access')) {
    function enforce_bilco_access()
    {
        return enforce_module_access('BILCO', 'Anda tidak memiliki akses ke menu Billing & Collection.');
    }
}

if (!function_exists('get_user_page_access_registry')) {
    function get_user_page_access_registry()
    {
        static $registry = null;
        if ($registry !== null) {
            return $registry;
        }

        $registry = [
            ['module_key' => 'Dashboard', 'page_key' => 'Dashboard', 'controller' => 'Dashboard', 'actions' => ['VIEW']],
            ['module_key' => 'Kontrak', 'page_key' => 'Kontrak_Payung', 'controller' => 'Kontrak_Payung', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Kontrak', 'page_key' => 'SPK', 'controller' => 'SPK', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Fiberstar', 'page_key' => 'Fiberstar_Project', 'controller' => 'Fiberstar_Project', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Fiberstar', 'page_key' => 'Fiberstar_Project_Detail', 'controller' => 'Fiberstar_Project_Detail', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Fiberstar', 'page_key' => 'Fiberstar_PO', 'controller' => 'Fiberstar_PO', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'MyRepublik_Project', 'controller' => 'MyRepublik_Project', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'BAK_MyRep', 'controller' => 'BAK_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'VALSAL_MyRep', 'controller' => 'VALSAL_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'Batch_Approval_MyRep', 'controller' => 'Batch_Approval_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'DRM_MyRep', 'controller' => 'DRM_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'Implementasi_BOQ_MyRep', 'controller' => 'Implementasi_BOQ_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'PO_MyRep', 'controller' => 'PO_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'Monitoring_RFS_MyRep', 'controller' => 'Monitoring_RFS_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'ATP_MyRep', 'controller' => 'ATP_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'MyRepublik', 'page_key' => 'Checklist_Dokument_MyRep', 'controller' => 'Checklist_Dokument_MyRep', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Master_Logistik_Lokasi_Gudang', 'controller' => 'Master_Logistik_Lokasi_Gudang', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Logistik', 'page_key' => 'Master_Logistik_Kode_Item', 'controller' => 'Master_Logistik_Kode_Item', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Logistik', 'page_key' => 'Master_Logistik_Sumber_Material', 'controller' => 'Master_Logistik_Sumber_Material', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Logistik', 'page_key' => 'Master_Logistik_Pabrik', 'controller' => 'Master_Logistik_Pabrik', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Logistik', 'page_key' => 'Logistik_Purchase_Request', 'controller' => 'Logistik_Purchase_Request', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Logistik_Nota_Dinas_Po', 'controller' => 'Logistik_Nota_Dinas_Po', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Logistik_Pesanan_Pabrik', 'controller' => 'Logistik_Pesanan_Pabrik', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Logistik_Pesanan_Pabrik_Detail', 'controller' => 'Logistik_Pesanan_Pabrik_Detail', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Dashboard_Logistik_Stok', 'controller' => 'Dashboard_Logistik_Stok', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Logistik', 'page_key' => 'Logistik_Stok_Detail', 'controller' => 'Logistik_Stok_Detail', 'actions' => ['VIEW']],
            ['module_key' => 'Logistik', 'page_key' => 'StockOpname', 'controller' => 'StockOpname', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'Master_GA_Aset', 'controller' => 'Master_GA_Aset', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'GA_Aset_Kendaraan', 'controller' => 'GA_Aset_Kendaraan', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'GA_Aset_Kantor', 'controller' => 'GA_Aset_Kantor', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'GA_Alat_Terminasi', 'controller' => 'GA_Alat_Terminasi', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'GA_Sarana_Kerja', 'controller' => 'GA_Sarana_Kerja', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'GeneralAffair', 'page_key' => 'GA_Seragam_Kantor', 'controller' => 'GA_Seragam_Kantor', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Budgeting', 'page_key' => 'Budget_MasterAkunBiaya', 'controller' => 'Budget_MasterAkunBiaya', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Budgeting', 'page_key' => 'Budget_MasterPic', 'controller' => 'Budget_MasterPic', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Budgeting', 'page_key' => 'Budget_MasterBudgetYears', 'controller' => 'Budget_MasterBudgetYears', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'Budgeting', 'page_key' => 'Budget_Cashflow', 'controller' => 'Budget_Cashflow', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'Budgeting', 'page_key' => 'Budget_Report', 'controller' => 'Budget_Report', 'actions' => ['VIEW']],
            ['module_key' => 'Budgeting', 'page_key' => 'ListUser', 'controller' => 'ListUser', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'BILCO', 'page_key' => 'BillingPayment', 'controller' => 'BillingPayment', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'BILCO', 'page_key' => 'PO_Monitor', 'controller' => 'PO_Monitor', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS', 'APPROVAL']],
            ['module_key' => 'TargetInvoice', 'page_key' => 'TargetInvoice', 'controller' => 'TargetInvoice', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'RincianInvoice', 'page_key' => 'RincianInvoice', 'controller' => 'RincianInvoice', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'SuperAdmin', 'page_key' => 'SuperAdmin_MyRep_Config', 'controller' => 'SuperAdmin_MyRep_Config', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'SuperAdmin', 'page_key' => 'SuperAdmin_MyRep_CityMapping', 'controller' => 'SuperAdmin_MyRep_CityMapping', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'SuperAdmin', 'page_key' => 'SuperAdmin_UserAccess', 'controller' => 'SuperAdmin_UserAccess', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'SuperAdmin', 'page_key' => 'ListArea', 'controller' => 'ListArea', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
            ['module_key' => 'SuperAdmin', 'page_key' => 'ListBowheer', 'controller' => 'ListBowheer', 'actions' => ['VIEW', 'TAMBAH', 'EDIT', 'HAPUS']],
        ];

        return $registry;
    }
}

if (!function_exists('get_user_page_access_controller_map')) {
    function get_user_page_access_controller_map()
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }

        $map = [];
        foreach (get_user_page_access_registry() as $entry) {
            $controller = strtoupper(trim((string) ($entry['controller'] ?? '')));
            if ($controller === '') {
                continue;
            }
            $moduleKey = trim((string) ($entry['module_key'] ?? ''));
            $pageKey = trim((string) ($entry['page_key'] ?? ''));
            $actions = (array) ($entry['actions'] ?? []);
            $normalizedActions = [];
            foreach ($actions as $action) {
                $actionKey = strtoupper(trim((string) $action));
                if ($actionKey !== '' && !in_array($actionKey, $normalizedActions, true)) {
                    $normalizedActions[] = $actionKey;
                }
            }
            if ($moduleKey === '' || $pageKey === '') {
                continue;
            }

            $map[$controller] = [
                'module_key' => $moduleKey,
                'page_key' => $pageKey,
                'actions' => $normalizedActions,
            ];
        }

        return $map;
    }
}

if (!function_exists('get_user_page_access_module_options')) {
    function get_user_page_access_module_options()
    {
        $modules = [];
        foreach (get_user_page_access_registry() as $entry) {
            $moduleKey = trim((string) ($entry['module_key'] ?? ''));
            if ($moduleKey !== '' && !in_array($moduleKey, $modules, true)) {
                $modules[] = $moduleKey;
            }
        }
        return $modules;
    }
}

if (!function_exists('get_user_page_access_page_options')) {
    function get_user_page_access_page_options($moduleKey = '')
    {
        $moduleKey = trim((string) $moduleKey);
        $pages = [];
        foreach (get_user_page_access_registry() as $entry) {
            $entryModule = trim((string) ($entry['module_key'] ?? ''));
            $pageKey = trim((string) ($entry['page_key'] ?? ''));
            if ($pageKey === '') {
                continue;
            }
            if ($moduleKey !== '' && strcasecmp($entryModule, $moduleKey) !== 0) {
                continue;
            }
            if (!in_array($pageKey, $pages, true)) {
                $pages[] = $pageKey;
            }
        }
        return $pages;
    }
}

if (!function_exists('resolve_user_page_access_action')) {
    function resolve_user_page_access_action($methodName)
    {
        $method = strtolower(trim((string) $methodName));
        if ($method === '' || $method === 'index' || $method === 'detail' || strpos($method, 'get') === 0 || strpos($method, 'preview') === 0 || strpos($method, 'download') === 0 || strpos($method, 'export') === 0) {
            return 'VIEW';
        }
        if (strpos($method, 'approve') === 0 || strpos($method, 'reject') === 0 || strpos($method, 'submit_approval') === 0) {
            return 'APPROVAL';
        }
        if (strpos($method, 'delete') === 0 || strpos($method, 'remove') === 0) {
            return 'HAPUS';
        }
        if (strpos($method, 'edit') === 0 || strpos($method, 'update') === 0 || strpos($method, 'allocate') === 0) {
            return 'EDIT';
        }
        if (strpos($method, 'save') === 0 || strpos($method, 'add') === 0 || strpos($method, 'create') === 0 || strpos($method, 'upload') === 0 || strpos($method, 'import') === 0 || strpos($method, 'store') === 0) {
            return 'TAMBAH';
        }

        return 'VIEW';
    }
}

if (!function_exists('user_has_module_access')) {
    function user_has_module_access($moduleKey)
    {
        $moduleKey = trim((string) $moduleKey);
        if ($moduleKey === '') {
            return true;
        }
        if ($moduleKey === 'Kontrak') {
            return has_validation_access('Kontrak') || has_validation_access('Project');
        }
        return has_validation_access($moduleKey);
    }
}

if (!function_exists('get_user_page_access_override')) {
    function get_user_page_access_override($userId, $pageKey, $actionKey)
    {
        $userId = (int) $userId;
        $pageKey = trim((string) $pageKey);
        $actionKey = strtoupper(trim((string) $actionKey));
        if ($userId <= 0 || $pageKey === '' || $actionKey === '') {
            return null;
        }

        static $cache = [];
        $cacheKey = $userId . '|' . $pageKey . '|' . $actionKey;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $CI = &get_instance();
        if (!isset($CI->db) || !$CI->db->table_exists('tb_user_page_permission')) {
            $cache[$cacheKey] = null;
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $row = (array) $CI->db
            ->select('is_allowed')
            ->from('tb_user_page_permission')
            ->where('id_user', $userId)
            ->where('page_key', $pageKey)
            ->where('action_key', $actionKey)
            ->where('tb_user_page_permission.is_active', 1)
            ->where("(effective_start IS NULL OR effective_start <= " . $CI->db->escape($now) . ")", null, false)
            ->where("(effective_end IS NULL OR effective_end >= " . $CI->db->escape($now) . ")", null, false)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            $cache[$cacheKey] = null;
            return null;
        }

        $cache[$cacheKey] = (int) ($row['is_allowed'] ?? 0) === 1;
        return $cache[$cacheKey];
    }
}

if (!function_exists('has_user_page_access')) {
    function has_user_page_access($moduleKey, $pageKey, $actionKey = 'VIEW', $userId = 0)
    {
        sync_current_user_access_session();

        $CI = &get_instance();

        if ((string) $CI->session->userdata('nama_level') === 'Super Admin') {
            return true;
        }

        $moduleKey = trim((string) $moduleKey);
        $pageKey = trim((string) $pageKey);
        $actionKey = strtoupper(trim((string) $actionKey));

        if ($pageKey === '') {
            return true;
        }

        $userId = (int) $userId;
        if ($userId <= 0) {
            $userId = (int) $CI->session->userdata('id_user');
        }
        if ($userId <= 0) {
            return true;
        }

        $baselineAllowed = user_has_module_access($moduleKey);
        $override = get_user_page_access_override($userId, $pageKey, $actionKey);
        if ($override !== null) {
            return (bool) $override;
        }

        return $baselineAllowed;
    }
}
