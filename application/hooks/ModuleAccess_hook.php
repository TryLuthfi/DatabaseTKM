<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ModuleAccess_hook
{
    private function denyAccess($message)
    {
        $CI =& get_instance();
        $isAjax = isset($CI->input) && method_exists($CI->input, 'is_ajax_request') && $CI->input->is_ajax_request();
        if ($isAjax && isset($CI->output)) {
            $CI->output
                ->set_status_header(403)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => (string) $message
                ]));
            $CI->output->_display();
            exit;
        }

        render_no_access((string) $message);
    }

    public function enforce()
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $CI =& get_instance();
        if (!isset($CI->router) || !isset($CI->session)) {
            return;
        }

        $controller = strtoupper(trim((string) $CI->router->fetch_class()));
        if ($controller === '') {
            return;
        }

        $userId = (int) $CI->session->userdata('id_user');
        if ($userId <= 0) {
            return;
        }

        $levelName = strtolower(trim((string) $CI->session->userdata('nama_level')));
        if ($levelName === 'super admin') {
            return;
        }

        $CI->load->helper('access');

        $kontrakControllers = [
            'KONTRAK_PAYUNG',
            'SPK',
        ];

        if (in_array($controller, $kontrakControllers, true) && !has_validation_access('Kontrak') && !has_validation_access('Project')) {
            $this->denyAccess('Anda tidak memiliki akses ke modul Kontrak.');
            return;
        }

        $myRepControllers = [
            'MYREPUBLIK_PROJECT',
            'MYREPUBLIK_PO',
            'POST_DONASI_MYREP',
            'BAK_MYREP',
            'VALSAL_MYREP',
            'BATCH_APPROVAL_MYREP',
            'DRM_MYREP',
            'IMPLEMENTASI_BOQ_MYREP',
            'PO_MYREP',
            'MONITORING_RFS_MYREP',
            'ATP_MYREP',
            'CHECKLIST_DOKUMENT_MYREP',
        ];

        if (in_array($controller, $myRepControllers, true) && !has_validation_access('MyRepublik')) {
            $this->denyAccess('Anda tidak memiliki akses ke modul MyRepublik.');
            return;
        }

        $fiberstarControllers = [
            'FIBERSTAR_PROJECT',
            'FIBERSTAR_PROJECT_DETAIL',
            'FIBERSTAR_PO',
            'FIBERSTAR_KOMPENSASI',
        ];

        if (in_array($controller, $fiberstarControllers, true) && !has_validation_access('Fiberstar')) {
            $this->denyAccess('Anda tidak memiliki akses ke modul Fiberstar.');
            return;
        }

        $controllerMap = get_user_page_access_controller_map();
        if (!isset($controllerMap[$controller])) {
            return;
        }

        $entry = (array) $controllerMap[$controller];
        $moduleKey = trim((string) ($entry['module_key'] ?? ''));
        $pageKey = trim((string) ($entry['page_key'] ?? ''));
        $allowedActions = array_map('strtoupper', (array) ($entry['actions'] ?? []));
        $methodName = (string) $CI->router->fetch_method();
        $actionKey = resolve_user_page_access_action($methodName);
        if (!in_array($actionKey, $allowedActions, true)) {
            $actionKey = 'VIEW';
        }

        if (!has_user_page_access($moduleKey, $pageKey, $actionKey, $userId)) {
            $this->denyAccess('Anda tidak memiliki akses ke halaman ini.');
            return;
        }
    }
}
