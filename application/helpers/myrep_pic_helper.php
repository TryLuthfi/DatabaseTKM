<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('myrep_pic_nik_list')) {
    function myrep_pic_nik_list($value)
    {
        $parts = preg_split('/[,;|]+/', (string) $value);
        $items = [];
        foreach ($parts as $part) {
            $nik = trim((string) $part);
            if ($nik !== '') {
                $items[$nik] = true;
            }
        }

        return array_keys($items);
    }
}

if (!function_exists('myrep_pic_nik_csv')) {
    function myrep_pic_nik_csv($value)
    {
        return implode(',', myrep_pic_nik_list($value));
    }
}

if (!function_exists('myrep_pic_column_contains_sql')) {
    function myrep_pic_column_contains_sql($db, $columnSql, $nik)
    {
        $columnSql = trim((string) $columnSql);
        $nik = trim((string) $nik);
        if ($columnSql === '' || $nik === '') {
            return '0 = 1';
        }

        return 'FIND_IN_SET(' . $db->escape($nik) . ", REPLACE(COALESCE({$columnSql}, ''), ' ', '')) > 0";
    }
}

if (!function_exists('myrep_pic_table_exists')) {
    function myrep_pic_table_exists($db, $tableName)
    {
        $tableName = trim((string) $tableName);
        if ($tableName === '') {
            return false;
        }

        static $cache = [];
        if (array_key_exists($tableName, $cache)) {
            return $cache[$tableName];
        }

        $row = (array) $db
            ->query(
                'SELECT 1 AS hit FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1',
                [$tableName]
            )
            ->row_array();

        $cache[$tableName] = !empty($row);
        return $cache[$tableName];
    }
}

if (!function_exists('myrep_pic_field_exists')) {
    function myrep_pic_field_exists($db, $fieldName, $tableName)
    {
        $fieldName = trim((string) $fieldName);
        $tableName = trim((string) $tableName);
        if ($fieldName === '' || $tableName === '') {
            return false;
        }

        static $cache = [];
        $cacheKey = $tableName . '.' . $fieldName;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $row = (array) $db
            ->query(
                'SELECT 1 AS hit FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
                [$tableName, $fieldName]
            )
            ->row_array();

        $cache[$cacheKey] = !empty($row);
        return $cache[$cacheKey];
    }
}

if (!function_exists('myrep_pic_name_list')) {
    function myrep_pic_name_list($value)
    {
        $parts = preg_split('/[,;|\/]+/', (string) $value);
        $items = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '') {
                $items[$name] = true;
            }
        }

        return array_keys($items);
    }
}

if (!function_exists('myrep_normalize_identity_name')) {
    function myrep_normalize_identity_name($value)
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return '';
        }

        return preg_replace('/\s+/', ' ', $value);
    }
}

if (!function_exists('myrep_identity_matches')) {
    function myrep_identity_matches($currentNik, $candidateNiks, $currentName = '', $candidateNames = '')
    {
        $currentNik = trim((string) $currentNik);
        $currentName = myrep_normalize_identity_name($currentName);

        $nikList = is_array($candidateNiks) ? $candidateNiks : myrep_pic_nik_list($candidateNiks);
        foreach ($nikList as $candidateNik) {
            if ($currentNik !== '' && trim((string) $candidateNik) === $currentNik) {
                return true;
            }
        }

        if ($currentName === '') {
            return false;
        }

        $nameList = is_array($candidateNames) ? $candidateNames : myrep_pic_name_list($candidateNames);
        foreach ($nameList as $candidateName) {
            if (myrep_normalize_identity_name($candidateName) === $currentName) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('myrep_master_user_name_by_nik')) {
    function myrep_master_user_name_by_nik($db, $nik, array &$cache = [])
    {
        $nik = trim((string) $nik);
        if ($nik === '' || !myrep_pic_table_exists($db, 'tb_master_user_new')) {
            return '';
        }

        if (array_key_exists($nik, $cache)) {
            return $cache[$nik];
        }

        $row = (array) $db
            ->select('nama_karyawan')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();

        $cache[$nik] = trim((string) ($row['nama_karyawan'] ?? ''));
        return $cache[$nik];
    }
}

if (!function_exists('myrep_city_project_team_mapping')) {
    function myrep_city_project_team_mapping($db, array $row, array &$cache = [])
    {
        $cityName = strtoupper(trim((string) ($row['city_name'] ?? '')));
        $provinceName = strtoupper(trim((string) ($row['province_name'] ?? '')));
        $regionalName = strtoupper(trim((string) ($row['regional_name'] ?? '')));
        if ($cityName === '' || !myrep_pic_table_exists($db, 'tb_myrep_pic_mapping_city') || !myrep_pic_field_exists($db, 'city_name', 'tb_myrep_pic_mapping_city')) {
            return [];
        }

        $cacheKey = $cityName . '|' . $provinceName . '|' . $regionalName;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $selectFields = [];
        foreach (['team_name', 'chief', 'rpm_area', 'sm_area', 'spv_area', 'snd_area'] as $columnName) {
            if (myrep_pic_field_exists($db, $columnName, 'tb_myrep_pic_mapping_city')) {
                $selectFields[] = $columnName;
            }
        }
        if (empty($selectFields)) {
            $cache[$cacheKey] = [];
            return [];
        }

        $mapping = [];
        foreach ([true, false] as $useLocationDetail) {
            $db
                ->select(implode(', ', $selectFields))
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $cityName);
            if ($useLocationDetail && $provinceName !== '' && myrep_pic_field_exists($db, 'province_name', 'tb_myrep_pic_mapping_city')) {
                $db->where('UPPER(province_name)', $provinceName);
            }
            if ($useLocationDetail && $regionalName !== '' && myrep_pic_field_exists($db, 'regional_name', 'tb_myrep_pic_mapping_city')) {
                $db->where('UPPER(regional_name)', $regionalName);
            }
            if (myrep_pic_field_exists($db, 'is_active', 'tb_myrep_pic_mapping_city')) {
                $db->where('is_active', 1);
            }

            $mapping = (array) $db->limit(1)->get()->row_array();
            if (!empty($mapping) || !$useLocationDetail) {
                break;
            }
        }

        $cache[$cacheKey] = $mapping;
        return $mapping;
    }
}

if (!function_exists('myrep_resolve_city_mapping_pic_names')) {
    function myrep_resolve_city_mapping_pic_names($db, array $mapping, $columnName, array &$userCache = [])
    {
        if (!myrep_pic_table_exists($db, 'tb_myrep_pic_mapping_city') || !myrep_pic_field_exists($db, $columnName, 'tb_myrep_pic_mapping_city')) {
            return '';
        }

        $names = [];
        foreach (myrep_pic_nik_list($mapping[$columnName] ?? '') as $nik) {
            $mappedName = myrep_master_user_name_by_nik($db, $nik, $userCache);
            $names[] = $mappedName !== '' ? $mappedName : $nik;
        }

        return implode(', ', $names);
    }
}

if (!function_exists('myrep_apply_city_project_team')) {
    function myrep_apply_city_project_team($db, array $row, array &$mappingCache = [], array &$userCache = [])
    {
        $mapping = myrep_city_project_team_mapping($db, $row, $mappingCache);
        if (empty($mapping)) {
            return $row;
        }

        if (array_key_exists('team_name', $mapping)) {
            $row['team_name'] = (string) $mapping['team_name'];
        }
        if (array_key_exists('chief', $mapping)) {
            $row['chief'] = (string) $mapping['chief'];
        }

        $roleMap = [
            'rpm' => 'rpm_area',
            'sm' => 'sm_area',
            'spv' => 'spv_area',
            'pic_project' => 'snd_area',
        ];
        foreach ($roleMap as $targetColumn => $mappingColumn) {
            $names = myrep_resolve_city_mapping_pic_names($db, $mapping, $mappingColumn, $userCache);
            if ($names !== '' || array_key_exists($targetColumn, $row)) {
                $row[$targetColumn] = $names;
            }
        }

        return $row;
    }
}

if (!function_exists('myrep_apply_city_project_team_rows')) {
    function myrep_apply_city_project_team_rows($db, array $rows, array &$mappingCache = [], array &$userCache = [])
    {
        foreach ($rows as &$row) {
            if (is_array($row)) {
                $row = myrep_apply_city_project_team($db, $row, $mappingCache, $userCache);
            }
        }
        unset($row);

        return $rows;
    }
}
