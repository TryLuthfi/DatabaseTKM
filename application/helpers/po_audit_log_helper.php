<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('po_audit_log_write')) {
    function po_audit_log_write($ci, $module, $action, array $data = [])
    {
        $logDir = APPPATH . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $filePath = $logDir . DIRECTORY_SEPARATOR . 'PO_Audit-' . date('Y-m-d') . '.log';
        $userId = null;
        $username = '-';
        if (isset($ci->session)) {
            $userId = $ci->session->userdata('id_user');
            $username = $ci->session->userdata('nama_user')
                ?: $ci->session->userdata('username')
                ?: $ci->session->userdata('email_user')
                ?: '-';
        }

        $base = [
            'time' => date('Y-m-d H:i:s'),
            'module' => $module,
            'action' => $action,
            'user' => $username,
            'user_id' => $userId ?: '-',
            'ip' => isset($ci->input) ? $ci->input->ip_address() : '-',
        ];

        $parts = [];
        foreach (array_merge($base, $data) as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($value === null || $value === '') {
                $value = '-';
            }
            $parts[] = $key . '=' . str_replace(["\r", "\n"], ' ', (string) $value);
        }

        @file_put_contents($filePath, implode(' | ', $parts) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
