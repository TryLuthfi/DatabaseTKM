<?php
defined('BASEPATH') or exit('No direct script access allowed');

class First_login_guard_hook
{
    public function enforce()
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $CI =& get_instance();
        if (!isset($CI->router) || !isset($CI->session)) {
            return;
        }

        $userId = (int) $CI->session->userdata('id_user');
        $firstLoginRequired = (int) $CI->session->userdata('first_login_required') === 1;
        if ($userId <= 0 || !$firstLoginRequired) {
            return;
        }

        $directory = strtolower(trim((string) $CI->router->fetch_directory(), '/\\'));
        if ($directory === 'api') {
            return;
        }

        $class = strtolower((string) $CI->router->fetch_class());
        $method = strtolower((string) $CI->router->fetch_method());

        $allowedAuthMethods = [
            'firstloginemail' => true,
            'sendfirstloginlink' => true,
            'resetpassword' => true,
            'logout' => true,
        ];

        if ($class === 'auth' && isset($allowedAuthMethods[$method])) {
            return;
        }

        $redirectUrl = site_url('Auth/firstLoginEmail');
        $isAjax = isset($CI->input) && method_exists($CI->input, 'is_ajax_request') && $CI->input->is_ajax_request();
        if ($isAjax && isset($CI->output)) {
            $CI->output
                ->set_status_header(403)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Silakan verifikasi email kantor dan ganti password terlebih dahulu.',
                    'redirect_url' => $redirectUrl,
                ]));
            $CI->output->_display();
            exit;
        }

        if (isset($CI->output)) {
            $CI->output
                ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
                ->set_header('Cache-Control: post-check=0, pre-check=0', false)
                ->set_header('Pragma: no-cache')
                ->set_header('Expires: 0');
        }

        redirect($redirectUrl);
        exit;
    }
}
