<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyRep_Email_Queue extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Myrep_reject_email_service', null, 'myrepRejectEmail');
        $this->load->library('Myrep_notification_service', null, 'myrepNotifier');
    }

    public function processRejectQueue()
    {
        if (!$this->isAllowedRunner()) {
            show_404();
            return;
        }

        ignore_user_abort(true);
        @set_time_limit(300);

        $limit = (int) $this->input->get('limit');
        $result = $this->myrepRejectEmail->processDueQueues($limit > 0 ? $limit : null);
        $telegramResult = $this->myrepNotifier->processDueQueues($limit > 0 ? $limit : null);

        if ($this->input->is_cli_request()) {
            echo 'processed=' . (int) $result['processed']
                . ' sent=' . (int) $result['sent']
                . ' failed=' . (int) $result['failed']
                . ' skipped=' . (int) $result['skipped']
                . ' telegram_processed=' . (int) $telegramResult['processed']
                . ' telegram_sent=' . (int) $telegramResult['sent']
                . ' telegram_failed=' . (int) $telegramResult['failed']
                . ' telegram_skipped=' . (int) $telegramResult['skipped'] . PHP_EOL;
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $result,
                'telegram' => $telegramResult,
            ]));
    }

    private function isAllowedRunner()
    {
        if ($this->input->is_cli_request()) {
            return true;
        }

        $token = trim((string) $this->readEnvValue('MYREP_REJECT_EMAIL_QUEUE_TOKEN', ''));
        if ($token === '') {
            return false;
        }

        return hash_equals($token, trim((string) $this->input->get('token')));
    }

    private function readEnvValue($key, $default = '')
    {
        $envPath = APPPATH . '../.env';
        if (!is_file($envPath)) {
            return $default;
        }

        $env = @parse_ini_file($envPath);
        if (!is_array($env) || !array_key_exists($key, $env)) {
            return $default;
        }

        return trim((string) $env[$key]);
    }
}
