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
