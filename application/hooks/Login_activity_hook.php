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
        $historyId = (int) $CI->session->userdata('login_history_id');
        if ($historyId <= 0) {
            $historyId = (int) $CI->MLogin_History->buildWebSessionFromCurrentUser();
            if ($historyId <= 0) {
                return;
            }

            $CI->session->set_userdata([
                'login_history_id' => $historyId,
                'login_history_seen_at' => time(),
            ]);
            return;
        }

        $lastSeenAt = (int) $CI->session->userdata('login_history_seen_at');
        $throttleSeconds = (int) $CI->MLogin_History->getSeenThrottleSeconds();
        if ($lastSeenAt > 0 && (time() - $lastSeenAt) < $throttleSeconds) {
            return;
        }

        if ($CI->MLogin_History->touchWebSession($historyId, $userId)) {
            $CI->session->set_userdata('login_history_seen_at', time());
        }
    }
}
