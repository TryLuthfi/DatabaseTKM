<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/myrep_pic_helper.php';

class Myrep_notification_service
{
    private $ci;
    private $config;
    private $cityPicMappingCache = [];

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->config = $this->loadConfig();
    }

    public function notify($moduleName, $eventName, array $payload = [])
    {
        if (empty($this->config['enabled']) || empty($this->config['bot_token']) || empty($this->config['chat_id'])) {
            return false;
        }

        $route = $this->getRoute($moduleName, $eventName);
        if (empty($route)) {
            return false;
        }

        $recipient = $this->resolveRecipient($route, $payload);
        if (empty($recipient['name'])) {
            return false;
        }

        $message = $this->buildMessage($moduleName, $eventName, $payload, $recipient);
        return $this->sendTelegramMessage($message);
    }

    private function getRoute($moduleName, $eventName)
    {
        if (!$this->ci->db->table_exists('tb_myrep_notification_route')) {
            return [];
        }

        $route = $this->ci->db
            ->from('tb_myrep_notification_route')
            ->where('module_name', (string) $moduleName)
            ->where('event_name', (string) $eventName)
            ->where('is_active', 1)
            ->get()
            ->row_array();

        if (!empty($route)) {
            return $route;
        }

        $normalizedModule = strtolower(trim((string) $moduleName));
        $normalizedEvent = strtolower(trim((string) $eventName));

        if ($normalizedEvent === 'cluster_masuk' && in_array($normalizedModule, ['bak_myrep', 'valsal_myrep', 'batch_approval_myrep'], true)) {
            return $this->ci->db
                ->from('tb_myrep_notification_route')
                ->where('module_name', (string) $moduleName)
                ->where('event_name', 'document_masuk')
                ->where('is_active', 1)
                ->get()
                ->row_array();
        }

        if (in_array($normalizedEvent, ['full_upload', 'batch_revised', 'propose_donation'], true) && $normalizedModule === 'batch_approval_myrep') {
            return $this->ci->db
                ->from('tb_myrep_notification_route')
                ->where('module_name', (string) $moduleName)
                ->where('event_name', 'document_masuk')
                ->where('is_active', 1)
                ->get()
                ->row_array();
        }

        if ($normalizedEvent === 'full_upload' && $normalizedModule === 'drm_myrep') {
            return $this->ci->db
                ->from('tb_myrep_notification_route')
                ->where('module_name', (string) $moduleName)
                ->where('event_name', 'document_masuk')
                ->where('is_active', 1)
                ->get()
                ->row_array();
        }

        return [];
    }

    private function resolveRecipient(array $route, array $payload)
    {
        $targetType = strtoupper(trim((string) ($route['target_type'] ?? '')));
        if ($targetType === 'FIXED_USER') {
            return $this->getUserById((int) ($route['target_user_id'] ?? 0));
        }

        if ($targetType === 'CLUSTER_PIC') {
            $targetRole = strtoupper(trim((string) ($route['target_role'] ?? '')));
            if ($targetRole !== '') {
                $byRole = $this->getUserByCityRole($targetRole, $payload);
                if (!empty($byRole['name'])) {
                    return $byRole;
                }
            }

            return [
                'id_user' => (int) ($payload['target_user_id'] ?? 0),
                'name' => (string) ($payload['target_name'] ?? ''),
                'telegram_user_id' => (string) ($payload['target_telegram_user_id'] ?? ''),
            ];
        }

        if ($targetType === 'CITY_ROLE') {
            $targetRole = strtoupper(trim((string) ($route['target_role'] ?? '')));
            return $this->getUserByCityRole($targetRole, $payload);
        }

        return [];
    }

    private function getUserById($userId)
    {
        if ($userId <= 0) {
            return [];
        }

        $row = $this->ci->db
            ->select('id AS id_user, nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('id', (int) $userId)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return [
            'id_user' => (int) ($row['id_user'] ?? 0),
            'name' => (string) ($row['nama_user'] ?? ''),
            'telegram_user_id' => (string) ($row['telegram_user_id'] ?? ''),
        ];
    }

    private function getUserByNik($nik)
    {
        $nik = trim((string) $nik);
        if ($nik === '') {
            return [];
        }

        $row = $this->ci->db
            ->select('id AS id_user, nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where('nik', $nik)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return [];
        }

        return [
            'id_user' => (int) ($row['id_user'] ?? 0),
            'name' => (string) ($row['nama_user'] ?? ''),
            'telegram_user_id' => (string) ($row['telegram_user_id'] ?? ''),
        ];
    }

    private function getUsersByNikList(array $nikList)
    {
        $nikList = array_values(array_unique(array_filter(array_map(static function ($nik) {
            return trim((string) $nik);
        }, $nikList))));
        if (empty($nikList)) {
            return [];
        }

        $rows = (array) $this->ci->db
            ->select('id AS id_user, nik, nama_karyawan AS nama_user, telegram_user_id')
            ->from('tb_master_user_new')
            ->where_in('nik', $nikList)
            ->get()
            ->result_array();

        $byNik = [];
        foreach ($rows as $row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            if ($nik !== '') {
                $byNik[$nik] = [
                    'id_user' => (int) ($row['id_user'] ?? 0),
                    'name' => (string) ($row['nama_user'] ?? $nik),
                    'telegram_user_id' => (string) ($row['telegram_user_id'] ?? ''),
                ];
            }
        }

        $users = [];
        foreach ($nikList as $nik) {
            $users[] = $byNik[$nik] ?? [
                'id_user' => 0,
                'name' => $nik,
                'telegram_user_id' => '',
            ];
        }

        return $users;
    }

    private function combineRecipients(array $users)
    {
        if (empty($users)) {
            return [];
        }

        return [
            'id_user' => (int) ($users[0]['id_user'] ?? 0),
            'name' => implode(', ', array_filter(array_map(static function ($user) {
                return trim((string) ($user['name'] ?? ''));
            }, $users))),
            'telegram_user_id' => (string) ($users[0]['telegram_user_id'] ?? ''),
            'recipients' => $users,
        ];
    }

    private function getUserByCityRole($targetRole, array $payload = [])
    {
        $targetRole = strtoupper(trim((string) $targetRole));
        if ($targetRole === '') {
            return [];
        }

        $roleColumnMap = [
            'RPM_AREA' => 'rpm_area',
            'SM_AREA' => 'sm_area',
            'SPV_AREA' => 'spv_area',
            'SND_AREA' => 'snd_area',
            'ADMIN_AREA' => 'admin_area',
            'SND_HO' => 'snd_ho',
            'ATP_HO' => 'atp_ho',
            'RFS_HO' => 'rfs_ho',
            'SITAC_HO' => 'sitac_ho',
            'DC_HO' => 'dc_ho',
            'QA_HO' => 'qa_ho',
        ];

        if (!isset($roleColumnMap[$targetRole])) {
            return [];
        }

        $mapping = $this->getCityPicMapping($payload);
        if (empty($mapping)) {
            return [];
        }

        $nikList = myrep_pic_nik_list($mapping[$roleColumnMap[$targetRole]] ?? '');
        if (empty($nikList)) {
            return [];
        }

        return $this->combineRecipients($this->getUsersByNikList($nikList));
    }

    private function getCityPicMapping(array $payload = [])
    {
        if (!$this->ci->db->table_exists('tb_myrep_pic_mapping_city')) {
            return [];
        }

        $city = strtoupper(trim((string) ($payload['city_name'] ?? '')));
        $province = strtoupper(trim((string) ($payload['province_name'] ?? '')));
        $regional = strtoupper(trim((string) ($payload['regional_name'] ?? '')));
        if ($city === '') {
            return [];
        }

        $cacheKey = $regional . '|' . $province . '|' . $city;
        if (isset($this->cityPicMappingCache[$cacheKey])) {
            return $this->cityPicMappingCache[$cacheKey];
        }

        $this->ci->db->from('tb_myrep_pic_mapping_city');
        $this->ci->db->where('UPPER(city_name)', $city);
        if ($province !== '') {
            $this->ci->db->where('UPPER(province_name)', $province);
        }
        if ($regional !== '') {
            $this->ci->db->where('UPPER(regional_name)', $regional);
        }
        $row = $this->ci->db->limit(1)->get()->row_array();

        if (empty($row)) {
            $row = $this->ci->db
                ->from('tb_myrep_pic_mapping_city')
                ->where('UPPER(city_name)', $city)
                ->limit(1)
                ->get()
                ->row_array();
        }

        $this->cityPicMappingCache[$cacheKey] = !empty($row) ? $row : [];
        return $this->cityPicMappingCache[$cacheKey];
    }

    private function buildMessage($moduleName, $eventName, array $payload, array $recipient)
    {
        if (strtolower(trim((string) $moduleName)) === 'batch_approval_myrep' && strtolower(trim((string) $eventName)) === 'propose_donation') {
            return $this->buildProposeDonationMessage($payload, $recipient);
        }

        $title = $this->resolveModuleAwareTitle($moduleName, $eventName, $payload);
        $docLine = $this->resolveDocumentLine($moduleName, $eventName, $payload);
        $metaLine = $this->resolveMetaLine($payload);
        $supplementalLines = $this->resolveSupplementalLines($moduleName, $eventName, $payload);
        $senderLine = $this->resolveSenderLine($payload, $recipient);
        $timeLine = $this->formatIndonesianDateTime($payload['timestamp'] ?? date('Y-m-d H:i:s'));
        $detailUrl = trim((string) ($payload['detail_url'] ?? ''));

        $lines = [
            $title,
            '',
            '📄 ' . $this->escapeTelegramText($docLine),
            '🏗 ' . $this->escapeTelegramText($metaLine),
            '',
            '👤 ' . $senderLine,
            '🕒 ' . $this->escapeTelegramText($timeLine),
        ];

        if (!empty($supplementalLines)) {
            array_splice($lines, 4, 0, $supplementalLines);
        }

        if ($detailUrl !== '') {
            $lines[] = '';
            $lines[] = $this->escapeTelegramText($detailUrl);
        }

        return implode("\n", $lines);
    }

    private function buildProposeDonationMessage(array $payload, array $recipient)
    {
        $city = strtoupper(trim((string) ($payload['city_name'] ?? '')));
        $cluster = strtoupper(trim((string) ($payload['cluster_name'] ?? '')));
        $locationLine = trim($city . ' | ' . $cluster, " |\t\n\r\0\x0B");
        if ($locationLine === '') {
            $locationLine = '-';
        }

        $homepass = (int) ($payload['homepass'] ?? 0);
        $nominalPerHomepass = (float) ($payload['nominal_per_homepass'] ?? 0);
        $donationTotal = (float) ($payload['donation_total'] ?? 0);
        $senderLine = $this->resolveSenderLine($payload, $recipient);
        $timeLine = $this->formatIndonesianDateTime($payload['timestamp'] ?? date('Y-m-d H:i:s'));
        $detailUrl = trim((string) ($payload['detail_url'] ?? ''));

        $lines = [
            '<b>PROPOSE DONATION - EMR</b>',
            $this->escapeTelegramText($locationLine),
            '',
            $this->escapeTelegramText('HP : ' . number_format($homepass, 0, ',', '.') . ' HP'),
            $this->escapeTelegramText('CPH : ' . $this->formatRupiahWithDot($nominalPerHomepass)),
            $this->escapeTelegramText('Total : ' . $this->formatRupiahWithDot($donationTotal)),
            '',
            '👤 ' . $senderLine,
            '🕒 ' . $this->escapeTelegramText($timeLine),
        ];

        if ($detailUrl !== '') {
            $lines[] = '';
            $lines[] = $this->escapeTelegramText($detailUrl);
        }

        return implode("\n", $lines);
    }

    private function resolveTitle($eventName)
    {
        switch (strtolower(trim((string) $eventName))) {
            case 'cluster_masuk':
                return '✅ <b>NEW CLUSTER</b>';
            case 'document_revised':
                return '🔵 <b>REVISED DOCUMENT</b>';
            case 'claim_rfs_approved':
                return '🚀 <b>CLAIM RFS</b>';
            case 'document_masuk':
            default:
                return '📍 <b>NEW DOCUMENT</b>';
        }
    }

    private function resolveModuleAwareTitle($moduleName, $eventName, array $payload)
    {
        $normalizedEvent = strtolower(trim((string) $eventName));
        $normalizedModule = strtolower(trim((string) $moduleName));
        $documentLabel = trim((string) ($payload['document_label'] ?? ''));
        $moduleLabel = trim((string) ($payload['module_label'] ?? $moduleName));

        if ($normalizedEvent === 'daily_progress_masuk' && $normalizedModule === 'implementasi_boq_myrep') {
            return '<b>DAILY PROGRESS - ' . $this->escapeTelegramText($moduleLabel) . '</b>';
        }

        if ($normalizedEvent === 'full_upload' && in_array($normalizedModule, ['batch_approval_myrep', 'drm_myrep'], true)) {
            return '✅ <b>FULL UPLOAD - ' . $this->escapeTelegramText($moduleLabel) . '</b>';
        }

        if ($normalizedEvent === 'batch_revised' && $normalizedModule === 'batch_approval_myrep') {
            return '🔵 <b>REVISED - ' . $this->escapeTelegramText($moduleLabel) . '</b>';
        }

        if ($normalizedEvent === 'cluster_masuk' && in_array($normalizedModule, ['bak_myrep', 'valsal_myrep', 'batch_approval_myrep'], true)) {
            return '🏠 <b>NEW CLUSTER - ' . $this->escapeTelegramText($moduleLabel) . '</b>';
        }

        if ($normalizedEvent === 'document_revised' && in_array($normalizedModule, ['bak_myrep', 'valsal_myrep'], true) && $documentLabel !== '') {
            return '🔵 <b>REVISED - ' . $this->escapeTelegramText($moduleLabel) . '</b>';
        }

        return $this->resolveTitle($eventName);
    }

    private function resolveDocumentLine($moduleName, $eventName, array $payload)
    {
        $moduleLabel = trim((string) ($payload['module_label'] ?? $moduleName));
        $documentLabel = trim((string) ($payload['document_label'] ?? ''));

        if (strtolower(trim((string) $eventName)) === 'daily_progress_masuk') {
            return $documentLabel !== '' ? ($moduleLabel . ' - ' . $documentLabel) : $moduleLabel;
        }

        if (strtolower(trim((string) $eventName)) === 'claim_rfs_approved') {
            return $documentLabel !== '' ? $documentLabel : 'RFS';
        }

        if (strtolower(trim((string) $eventName)) === 'batch_revised') {
            return $documentLabel !== '' ? ($moduleLabel . ' - ' . $documentLabel) : $moduleLabel;
        }

        if (strtolower(trim((string) $eventName)) === 'full_upload') {
            return $moduleLabel;
        }

        if (strtolower(trim((string) $eventName)) === 'cluster_masuk') {
            return $moduleLabel;
        }

        return $documentLabel !== '' ? ($moduleLabel . ' - ' . $documentLabel) : $moduleLabel;
    }

    private function resolveMetaLine(array $payload)
    {
        $regional = strtoupper(trim((string) ($payload['regional_name'] ?? '-')));
        $city = strtoupper(trim((string) ($payload['city_name'] ?? '-')));
        $cluster = trim((string) ($payload['cluster_name'] ?? '-'));
        $homepass = (int) ($payload['homepass'] ?? 0);

        if ($homepass > 0) {
            $cluster .= ' ( ' . number_format($homepass, 0, ',', '.') . ' HP )';
        }

        return $regional . ' | ' . $city . ' | ' . $cluster;
    }

    private function resolveSenderLine(array $payload, array $recipient)
    {
        $senderName = trim((string) ($payload['sender_name'] ?? 'System'));
        $recipientMention = $this->buildRecipientMention($recipient);

        return $this->escapeTelegramText($senderName) . ' -> HO (' . $recipientMention . ')';
    }

    private function resolveSupplementalLines($moduleName, $eventName, array $payload)
    {
        $normalizedModule = strtolower(trim((string) $moduleName));
        $normalizedEvent = strtolower(trim((string) $eventName));

        if ($normalizedModule === 'implementasi_boq_myrep' && $normalizedEvent === 'daily_progress_masuk') {
            $activityDate = trim((string) ($payload['activity_date'] ?? ''));
            $scopeType = trim((string) ($payload['scope_type'] ?? ''));
            $teamCount = (int) ($payload['team_count'] ?? 0);
            $workerCount = (int) ($payload['worker_count'] ?? 0);
            $createdCount = (int) ($payload['created_count'] ?? 0);
            $allocatedQtyTotal = (float) ($payload['allocated_qty_total'] ?? 0);
            $activityRows = (array) ($payload['daily_activity_summary'] ?? []);
            $activities = array_slice($activityRows, 0, 6);
            $lines = [];

            if ($activityDate !== '') {
                $lines[] = $this->escapeTelegramText('Tanggal: ' . $activityDate);
            }
            if ($scopeType !== '') {
                $lines[] = $this->escapeTelegramText('Scope: ' . $scopeType);
            }

            $lines[] = $this->escapeTelegramText('Tim/Orang: ' . $teamCount . ' / ' . $workerCount);
            $lines[] = $this->escapeTelegramText('Jumlah Aktivitas: ' . $createdCount);

            if (abs($allocatedQtyTotal) > 0.00001) {
                $allocatedText = rtrim(rtrim(number_format($allocatedQtyTotal, 2, ',', '.'), '0'), ',');
                $lines[] = $this->escapeTelegramText('Auto BOQ: ' . $allocatedText . ' teralokasi');
            }

            foreach ($activities as $activity) {
                $activity = (array) $activity;
                $name = trim((string) ($activity['activity_name'] ?? 'Aktivitas'));
                $detail = trim((string) ($activity['activity_detail'] ?? '-'));
                $qty = (float) ($activity['qty_activity'] ?? 0);
                $unit = trim((string) ($activity['unit_activity'] ?? ''));
                $photoCount = (int) ($activity['photo_count'] ?? 0);
                $qtyText = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');

                $lines[] = $this->escapeTelegramText('- ' . $name . ' | ' . $detail . ' | ' . $qtyText . ' ' . $unit . ' | ' . $photoCount . ' foto');
            }

            if (count($activityRows) > count($activities)) {
                $lines[] = $this->escapeTelegramText('... +' . (count($activityRows) - count($activities)) . ' aktivitas lain');
            }

            if (!empty($lines)) {
                $lines[] = '';
            }

            return $lines;
        }

        if ($normalizedModule === 'batch_approval_myrep' && $normalizedEvent === 'cluster_masuk') {
            $donationTotal = (float) ($payload['donation_total'] ?? 0);
            $nominalPerHomepass = (float) ($payload['nominal_per_homepass'] ?? 0);

            return [
                '💲 ' . $this->escapeTelegramText('Total Pengajuan Donasi: ' . $this->formatRupiah($donationTotal)),
                '🏠 ' . $this->escapeTelegramText('Nominal per Homepass: ' . $this->formatRupiah($nominalPerHomepass)),
                '',
            ];
        }

        return [];
    }

    private function buildRecipientMention(array $recipient)
    {
        if (!empty($recipient['recipients']) && is_array($recipient['recipients'])) {
            $mentions = [];
            foreach ($recipient['recipients'] as $recipientRow) {
                $mentions[] = $this->buildRecipientMention((array) $recipientRow);
            }

            return implode(', ', array_filter($mentions));
        }

        $name = $this->escapeTelegramText((string) ($recipient['name'] ?? 'PIC'));
        $telegramUserId = trim((string) ($recipient['telegram_user_id'] ?? ''));
        if ($telegramUserId === '') {
            return $name;
        }

        return '<a href="tg://user?id=' . rawurlencode($telegramUserId) . '">' . $name . '</a>';
    }

    private function sendTelegramMessage($message)
    {
        $endpoint = 'https://api.telegram.org/bot' . $this->config['bot_token'] . '/sendMessage';
        $payload = http_build_query([
            'chat_id' => $this->config['chat_id'],
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => 'true',
        ]);

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $isSent = $response !== false && $httpCode >= 200 && $httpCode < 300;
            if (!$isSent) {
                log_message('error', 'MyRep notification failed via cURL. HTTP: ' . $httpCode . ' Error: ' . $curlError);
            }

            return $isSent;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);
        $response = @file_get_contents($endpoint, false, $context);
        if ($response === false) {
            log_message('error', 'MyRep notification failed via file_get_contents.');
            return false;
        }

        return true;
    }

    private function loadConfig()
    {
        $envPath = APPPATH . '../.env';
        $env = is_file($envPath) ? parse_ini_file($envPath) : [];

        return [
            'enabled' => $this->normalizeBooleanEnv($env['TELEGRAM_MYREP_NOTIFICATION_ENABLED'] ?? ($env['TELEGRAM_CHECKLIST_MYREP_ENABLED'] ?? true)),
            'bot_token' => trim((string) ($env['TELEGRAM_BOT_TOKEN'] ?? '')),
            'chat_id' => trim((string) ($env['TELEGRAM_CHAT_ID_CHECKLIST_MYREP'] ?? ($env['TELEGRAM_CHAT_ID'] ?? ''))),
        ];
    }

    private function normalizeBooleanEnv($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function formatIndonesianDateTime($dateTime)
    {
        $timestamp = strtotime((string) $dateTime);
        if ($timestamp === false) {
            return (string) $dateTime;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $monthIndex = (int) date('n', $timestamp);
        return date('d', $timestamp) . ' ' . ($months[$monthIndex] ?? date('m', $timestamp)) . ' ' . date('Y H:i', $timestamp) . ' WIB';
    }

    private function formatRupiah($value)
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function formatRupiahWithDot($value)
    {
        return 'Rp. ' . number_format((float) $value, 0, ',', '.');
    }

    private function escapeTelegramText($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
