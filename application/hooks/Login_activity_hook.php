<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login_activity_hook
{
    public function track()
    {
        $CI =& get_instance();
        if (!isset($CI->session) || !isset($CI->db) || !isset($CI->router)) {
            return;
        }

        $userId = (int) $CI->session->userdata('id_user');
        if ($userId <= 0) {
            return;
        }

        $class = strtolower((string) $CI->router->fetch_class());
        $directory = strtolower(trim((string) $CI->router->fetch_directory(), '/\\'));
        if ($class === 'api' || $directory === 'api') {
            return;
        }

        $CI->load->model('MLogin_History');
        $method = strtolower((string) $CI->router->fetch_method());
        $pageContext = $CI->MLogin_History->buildPageContext($class, $method, $directory);
        $pageKey = (string) ($pageContext['key'] ?? '');
        $historyId = (int) $CI->session->userdata('login_history_id');
        if ($historyId <= 0) {
            $historyId = (int) $CI->MLogin_History->buildWebSessionFromCurrentUser();
            if ($historyId <= 0) {
                return;
            }

            if (!empty($pageContext)) {
                $CI->MLogin_History->touchWebSession($historyId, $userId, $pageContext);
            }

            $CI->session->set_userdata([
                'login_history_id' => $historyId,
                'login_history_seen_at' => time(),
                'login_history_page_key' => $pageKey,
            ]);
            return;
        }

        $lastSeenAt = (int) $CI->session->userdata('login_history_seen_at');
        $lastPageKey = (string) $CI->session->userdata('login_history_page_key');
        $throttleSeconds = (int) $CI->MLogin_History->getSeenThrottleSeconds();
        if ($lastSeenAt > 0 && (time() - $lastSeenAt) < $throttleSeconds && ($pageKey === '' || $pageKey === $lastPageKey)) {
            return;
        }

        if ($CI->MLogin_History->touchWebSession($historyId, $userId, $pageContext)) {
            $CI->session->set_userdata([
                'login_history_seen_at' => time(),
                'login_history_page_key' => $pageKey,
            ]);
        }
    }
}
