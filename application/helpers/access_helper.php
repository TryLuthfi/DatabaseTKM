<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_validation_user_list')) {
    function get_validation_user_list()
    {
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

        return true;
    }
}

if (!function_exists('enforce_bilco_access')) {
    function enforce_bilco_access()
    {
        return enforce_module_access('BILCO', 'Anda tidak memiliki akses ke menu Billing & Collection.');
    }
}
