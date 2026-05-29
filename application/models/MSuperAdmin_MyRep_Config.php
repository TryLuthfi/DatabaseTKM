<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class MSuperAdmin_MyRep_Config extends CI_Model
{
    private $cityPicRoleColumns = [
        'rpm_area',
        'sm_area',
        'spv_area',
        'snd_area',
        'admin_area',
        'snd_ho',
        'atp_ho',
        'rfs_ho',
        'sitac_ho',
        'dc_ho',
        'qa_ho',
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

    private $defaultRoleOptions = [
        'RPM_AREA',
        'SM_AREA',
        'SPV_AREA',
        'SND_AREA',
        'ADMIN_AREA',
        'SND_HO',
        'ATP_HO',
        'RFS_HO',
        'SITAC_HO',
        'DC_HO',
        'QA_HO',
    ];

    private $defaultModuleOptions = [
        'BAK_MyRep',
        'VALSAL_MyRep',
        'Batch_Approval_MyRep',
        'DRM_MyRep',
        'Monitoring_RFS_MyRep',
        'Checklist_Dokument_MyRep',
    ];

    private $defaultEventOptions = [
        'cluster_masuk',
        'document_masuk',
        'document_revised',
        'full_upload',
        'batch_revised',
        'claim_rfs_approved',
    ];

    private $defaultTargetTypeOptions = [
        'FIXED_USER',
        'CLUSTER_PIC',
        'CITY_ROLE',
    ];

    public function checkTablesReady()
    {
        return [
            'role_permission' => $this->db->table_exists('tb_myrep_role_permission'),
            'notification_route' => $this->db->table_exists('tb_myrep_notification_route'),
            'master_user' => $this->db->table_exists('tb_master_user_new'),
            'city_mapping' => $this->db->table_exists('tb_myrep_pic_mapping_city'),
        ];
    }

    public function getRolePermissions()
    {
        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return [];
        }

        return (array) $this->db
            ->from('tb_myrep_role_permission')
            ->order_by('page_key', 'ASC')
            ->order_by('action_key', 'ASC')
            ->order_by('role_key', 'ASC')
            ->get()
            ->result_array();
    }

    public function getAccessMatrix(array $pages, array $actions, array $roles)
    {
        $matrix = [];
        foreach ($pages as $page) {
            $pageKey = (string) $page;
            $matrix[$pageKey] = [];
            foreach ($actions as $action) {
                $actionKey = strtoupper((string) $action);
                $matrix[$pageKey][$actionKey] = [];
                foreach ($roles as $role) {
                    $matrix[$pageKey][$actionKey][(string) $role] = 0;
                }
            }
        }

        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return $matrix;
        }

        $rows = (array) $this->db
            ->select('page_key, action_key, role_key, is_allowed, is_active')
            ->from('tb_myrep_role_permission')
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            $pageKey = (string) ($row['page_key'] ?? '');
            $actionKey = strtoupper((string) ($row['action_key'] ?? ''));
            $roleKey = (string) ($row['role_key'] ?? '');
            if (!isset($matrix[$pageKey][$actionKey][$roleKey])) {
                continue;
            }
            $matrix[$pageKey][$actionKey][$roleKey] = ((int) ($row['is_allowed'] ?? 0) === 1 && (int) ($row['is_active'] ?? 0) === 1) ? 1 : 0;
        }

        return $matrix;
    }

    public function getNotificationRoutes()
    {
        if (!$this->db->table_exists('tb_myrep_notification_route')) {
            return [];
        }

        $idColumn = $this->db->field_exists('id_route', 'tb_myrep_notification_route') ? 'id_route' : 'id';
        $targetRoleColumn = $this->db->field_exists('target_role', 'tb_myrep_notification_route') ? 'target_role' : 'NULL AS target_role';

        return (array) $this->db->query(
            "SELECT r." . $idColumn . " AS id_route, r.module_name, r.event_name, r.target_type, r.target_user_id, " . $targetRoleColumn . ", r.is_active,
                    u.nama_karyawan AS target_user_name, u.telegram_user_id AS target_user_telegram
             FROM tb_myrep_notification_route r
             LEFT JOIN tb_master_user_new u ON u.id = r.target_user_id
             ORDER BY FIELD(
                    r.module_name,
                    'BAK_MyRep',
                    'VALSAL_MyRep',
                    'Batch_Approval_MyRep',
                    'DRM_MyRep',
                    'Monitoring_RFS_MyRep',
                    'Checklist_Dokument_MyRep'
                ) ASC,
                r.event_name ASC,
                r." . $idColumn . " ASC"
        )->result_array();
    }

    public function getCityPicMappings()
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $tableName = 'tb_myrep_pic_mapping_city';

        $this->db
            ->from($tableName . ' m')
            ->select('m.id, m.regional_name, m.province_name, m.city_name, m.team_name, m.chief');

        if ($this->db->field_exists('is_active', $tableName)) {
            $this->db->select('m.is_active');
        } else {
            $this->db->select('1 AS is_active', false);
        }

        foreach ($this->cityPicRoleColumns as $columnName) {
            $nameFieldAlias = $columnName . '_name';
            if ($this->db->field_exists($columnName, $tableName)) {
                $this->db->select('m.' . $columnName);
            } else {
                $this->db->select('NULL AS ' . $columnName, false);
            }
            $this->db->select('NULL AS ' . $nameFieldAlias, false);
        }

        $rows = (array) $this->db
            ->order_by('m.regional_name', 'ASC')
            ->order_by('m.province_name', 'ASC')
            ->order_by('m.city_name', 'ASC')
            ->get()
            ->result_array();

        return $this->decorateCityPicMappingNames($rows);
    }

    private function decorateCityPicMappingNames(array $rows)
    {
        if (empty($rows) || !$this->db->table_exists('tb_master_user_new')) {
            return $rows;
        }

        $nikSet = [];
        foreach ($rows as $row) {
            foreach ($this->cityPicRoleColumns as $columnName) {
                foreach (myrep_pic_nik_list($row[$columnName] ?? '') as $nik) {
                    $nikSet[$nik] = true;
                }
            }
        }

        if (empty($nikSet)) {
            return $rows;
        }

        $userRows = (array) $this->db
            ->select('nik, nama_karyawan')
            ->from('tb_master_user_new')
            ->where_in('nik', array_keys($nikSet))
            ->get()
            ->result_array();

        $nameByNik = [];
        foreach ($userRows as $userRow) {
            $nik = trim((string) ($userRow['nik'] ?? ''));
            if ($nik !== '') {
                $nameByNik[$nik] = trim((string) ($userRow['nama_karyawan'] ?? ''));
            }
        }

        foreach ($rows as &$row) {
            foreach ($this->cityPicRoleColumns as $columnName) {
                $names = [];
                foreach (myrep_pic_nik_list($row[$columnName] ?? '') as $nik) {
                    $names[] = ($nameByNik[$nik] ?? '') !== '' ? $nameByNik[$nik] : $nik;
                }
                $row[$columnName . '_name'] = implode(', ', $names);
            }
        }
        unset($row);

        return $rows;
    }

    public function getCityPicRoleColumns()
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return $this->cityPicRoleColumns;
        }

        $availableColumns = [];
        foreach ($this->cityPicRoleColumns as $columnName) {
            if ($this->db->field_exists($columnName, 'tb_myrep_pic_mapping_city')) {
                $availableColumns[] = $columnName;
            }
        }

        return empty($availableColumns) ? $this->cityPicRoleColumns : $availableColumns;
    }

    public function saveCityPicMappingsBulk(array $rows)
    {
        if (!$this->db->table_exists('tb_myrep_pic_mapping_city')) {
            return ['ok' => false, 'updated' => 0, 'failed' => []];
        }

        $this->db->trans_begin();
        $updated = 0;
        $failed = [];
        $roleColumns = $this->getCityPicRoleColumns();

        foreach ($rows as $idx => $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                $failed[] = (int) $idx + 1;
                continue;
            }

            $data = [];
            foreach ($roleColumns as $column) {
                $value = myrep_pic_nik_csv($row[$column] ?? '');
                $data[$column] = $value === '' ? null : $value;
            }

            $ok = (bool) $this->db
                ->where('id', $id)
                ->update('tb_myrep_pic_mapping_city', $data);

            if ($ok) {
                $updated++;
            } else {
                $failed[] = (int) $idx + 1;
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['ok' => false, 'updated' => 0, 'failed' => $failed];
        }

        $this->db->trans_commit();
        return ['ok' => empty($failed), 'updated' => $updated, 'failed' => $failed];
    }

    public function getUserOptions()
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        return (array) $this->db
            ->select('id, nik, nama_karyawan, telegram_user_id, status_user')
            ->from('tb_master_user_new')
            ->order_by('status_user', 'DESC')
            ->order_by('nama_karyawan', 'ASC')
            ->get()
            ->result_array();
    }

    public function searchUserOptions($term = '', $limit = 20, $offset = 0)
    {
        if (!$this->db->table_exists('tb_master_user_new')) {
            return [];
        }

        $term = trim((string) $term);
        $limit = max(1, min(100, (int) $limit));
        $offset = max(0, (int) $offset);

        $this->db
            ->select('nik, nama_karyawan, status_user')
            ->from('tb_master_user_new')
            ->where('nik IS NOT NULL', null, false)
            ->where("TRIM(nik) <> ''", null, false);

        if ($term !== '') {
            $this->db->group_start()
                ->like('nama_karyawan', $term)
                ->or_like('nik', $term)
                ->group_end();
        }

        return (array) $this->db
            ->order_by('status_user', 'DESC')
            ->order_by('nama_karyawan', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    public function getPageOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'page_key', $this->defaultPageOptions);
    }

    public function getActionOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'action_key', $this->defaultActionOptions);
    }

    public function getRoleOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_role_permission', 'role_key', $this->defaultRoleOptions);
    }

    public function getModuleOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_notification_route', 'module_name', $this->defaultModuleOptions);
    }

    public function getEventOptions()
    {
        return $this->mergeDistinctColumnOptions('tb_myrep_notification_route', 'event_name', $this->defaultEventOptions);
    }

    public function getTargetTypeOptions()
    {
        return $this->defaultTargetTypeOptions;
    }

    public function upsertRolePermission(array $data)
    {
        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return false;
        }

        $sql = "INSERT INTO tb_myrep_role_permission
                (page_key, action_key, role_key, is_allowed, is_active, effective_start, effective_end)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    is_allowed = VALUES(is_allowed),
                    is_active = VALUES(is_active),
                    effective_start = VALUES(effective_start),
                    effective_end = VALUES(effective_end),
                    updated_at = CURRENT_TIMESTAMP";

        return (bool) $this->db->query($sql, [
            (string) ($data['page_key'] ?? ''),
            strtoupper((string) ($data['action_key'] ?? '')),
            strtoupper((string) ($data['role_key'] ?? '')),
            (int) ($data['is_allowed'] ?? 0),
            (int) ($data['is_active'] ?? 0),
            $data['effective_start'] ?? null,
            $data['effective_end'] ?? null,
        ]);
    }

    public function deleteRolePermission($idPermission)
    {
        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return false;
        }

        return (bool) $this->db
            ->where('id_permission', (int) $idPermission)
            ->delete('tb_myrep_role_permission');
    }

    public function saveAccessMatrix(array $pages, array $actions, array $roles, array $postedMatrix)
    {
        if (!$this->db->table_exists('tb_myrep_role_permission')) {
            return false;
        }

        $this->db->trans_begin();

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

                foreach ($roles as $role) {
                    $roleKey = strtoupper((string) $role);
                    if ($roleKey === '') {
                        continue;
                    }

                    $isChecked = !empty($postedMatrix[$pageKey][$actionKey][$roleKey]) ? 1 : 0;
                    $sql = "INSERT INTO tb_myrep_role_permission
                            (page_key, action_key, role_key, is_allowed, is_active, effective_start, effective_end)
                            VALUES (?, ?, ?, ?, 1, NULL, NULL)
                            ON DUPLICATE KEY UPDATE
                                is_allowed = VALUES(is_allowed),
                                is_active = 1,
                                updated_at = CURRENT_TIMESTAMP";
                    $this->db->query($sql, [$pageKey, $actionKey, $roleKey, $isChecked]);
                }
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    public function upsertNotificationRoute(array $data)
    {
        if (!$this->db->table_exists('tb_myrep_notification_route')) {
            return false;
        }

        $hasTargetRole = $this->db->field_exists('target_role', 'tb_myrep_notification_route');
        if ($hasTargetRole) {
            $sql = "INSERT INTO tb_myrep_notification_route
                    (module_name, event_name, target_type, target_user_id, target_role, is_active)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        target_type = VALUES(target_type),
                        target_user_id = VALUES(target_user_id),
                        target_role = VALUES(target_role),
                        is_active = VALUES(is_active),
                        updated_at = CURRENT_TIMESTAMP";

            return (bool) $this->db->query($sql, [
                (string) ($data['module_name'] ?? ''),
                (string) ($data['event_name'] ?? ''),
                strtoupper((string) ($data['target_type'] ?? 'FIXED_USER')),
                isset($data['target_user_id']) ? (int) $data['target_user_id'] : null,
                isset($data['target_role']) ? strtoupper((string) $data['target_role']) : null,
                (int) ($data['is_active'] ?? 0),
            ]);
        }

        $sql = "INSERT INTO tb_myrep_notification_route
                (module_name, event_name, target_type, target_user_id, is_active)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    target_type = VALUES(target_type),
                    target_user_id = VALUES(target_user_id),
                    is_active = VALUES(is_active),
                    updated_at = CURRENT_TIMESTAMP";

        return (bool) $this->db->query($sql, [
            (string) ($data['module_name'] ?? ''),
            (string) ($data['event_name'] ?? ''),
            strtoupper((string) ($data['target_type'] ?? 'FIXED_USER')),
            isset($data['target_user_id']) ? (int) $data['target_user_id'] : null,
            (int) ($data['is_active'] ?? 0),
        ]);
    }

    public function deleteNotificationRoute($idRoute)
    {
        if (!$this->db->table_exists('tb_myrep_notification_route')) {
            return false;
        }

        $idColumn = $this->db->field_exists('id_route', 'tb_myrep_notification_route') ? 'id_route' : 'id';
        return (bool) $this->db
            ->where($idColumn, (int) $idRoute)
            ->delete('tb_myrep_notification_route');
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
