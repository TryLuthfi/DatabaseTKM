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
