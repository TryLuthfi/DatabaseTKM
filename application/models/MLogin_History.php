<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MLogin_History extends CI_Model
{
    private $table = 'tb_user_login_history';
    private $onlineSeconds = 600;
    private $idleSeconds = 1800;
    private $retentionDays = 90;
    private $seenThrottleSeconds = 60;

    public function ensureTable()
    {
        if ($this->db->table_exists($this->table)) {
            return;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id_login_history` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `nik` VARCHAR(80) DEFAULT NULL,
            `username_user` VARCHAR(120) DEFAULT NULL,
            `nama_user` VARCHAR(180) DEFAULT NULL,
            `nama_level` VARCHAR(100) DEFAULT NULL,
            `session_id` VARCHAR(128) DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(255) DEFAULT NULL,
            `platform` VARCHAR(20) NOT NULL DEFAULT 'web',
            `login_at` DATETIME NOT NULL,
            `last_seen_at` DATETIME NOT NULL,
            `logout_at` DATETIME DEFAULT NULL,
            `logout_reason` VARCHAR(40) DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_login_history`),
            KEY `idx_login_history_user` (`user_id`, `login_at`),
            KEY `idx_login_history_seen` (`last_seen_at`),
            KEY `idx_login_history_logout` (`logout_at`),
            KEY `idx_login_history_platform` (`platform`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function startWebSession(array $user)
    {
        $this->ensureTable();
        $this->cleanupOldRows();

        $now = date('Y-m-d H:i:s');
        $this->db->insert($this->table, [
            'user_id' => (int) ($user['id'] ?? $user['id_user'] ?? 0),
            'nik' => substr((string) ($user['nik'] ?? ''), 0, 80),
            'username_user' => substr((string) ($user['username_user'] ?? ''), 0, 120),
            'nama_user' => substr((string) ($user['nama_user'] ?? $user['nama_karyawan'] ?? ''), 0, 180),
            'nama_level' => substr((string) ($user['nama_level'] ?? ''), 0, 100),
            'session_id' => substr($this->getCurrentSessionId(), 0, 128),
            'ip_address' => substr((string) $this->input->ip_address(), 0, 45),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
            'platform' => 'web',
            'login_at' => $now,
            'last_seen_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->insert_id();
    }

    public function touchWebSession($historyId, $userId)
    {
        $historyId = (int) $historyId;
        $userId = (int) $userId;
        if ($historyId <= 0 || $userId <= 0) {
            return false;
        }

        $this->ensureTable();
        $now = date('Y-m-d H:i:s');
        $updated = $this->db
            ->where('id_login_history', $historyId)
            ->where('user_id', $userId)
            ->where('platform', 'web')
            ->where('logout_at IS NULL', null, false)
            ->update($this->table, [
                'last_seen_at' => $now,
                'ip_address' => substr((string) $this->input->ip_address(), 0, 45),
                'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
                'updated_at' => $now,
            ]);

        return (bool) $updated;
    }

    public function finishWebSession($historyId, $userId, $reason = 'manual_logout')
    {
        $historyId = (int) $historyId;
        $userId = (int) $userId;
        if ($historyId <= 0 || $userId <= 0) {
            return false;
        }

        $this->ensureTable();
        $now = date('Y-m-d H:i:s');
        return (bool) $this->db
            ->where('id_login_history', $historyId)
            ->where('user_id', $userId)
            ->where('platform', 'web')
            ->where('logout_at IS NULL', null, false)
            ->update($this->table, [
                'last_seen_at' => $now,
                'logout_at' => $now,
                'logout_reason' => substr((string) $reason, 0, 40),
                'updated_at' => $now,
            ]);
    }

    public function buildWebSessionFromCurrentUser()
    {
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0) {
            return 0;
        }

        $user = [
            'id' => $userId,
            'nik' => '',
            'username_user' => (string) $this->session->userdata('username_user'),
            'nama_user' => (string) $this->session->userdata('nama_user'),
            'nama_level' => (string) $this->session->userdata('nama_level'),
        ];

        if ($this->db->table_exists('tb_master_user_new')) {
            $row = $this->db
                ->select('id, nik, username_user, nama_karyawan AS nama_user')
                ->from('tb_master_user_new')
                ->where('id', $userId)
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($row)) {
                $user = array_merge($user, $row);
                $user['nama_level'] = (string) $this->session->userdata('nama_level');
            }
        }

        return $this->startWebSession($user);
    }

    public function getRows(array $filters = [])
    {
        $this->ensureTable();
        $this->cleanupOldRows();

        $now = time();
        $rows = $this->baseFilteredQuery($filters)
            ->select('h.*')
            ->select('u.nik AS current_nik, u.nama_karyawan AS current_nama_user, u.username_user AS current_username_user, u.homebase, u.lokasi_kantor, u.status_user, l.nama_level AS current_nama_level', false)
            ->order_by('h.login_at', 'DESC')
            ->limit(500)
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $lastSeenAt = strtotime((string) ($row['last_seen_at'] ?? ''));
            $logoutAt = trim((string) ($row['logout_at'] ?? ''));
            $secondsAgo = $lastSeenAt !== false ? max($now - $lastSeenAt, 0) : null;
            $row['activity_status'] = $this->resolveActivityStatus($secondsAgo, $logoutAt);
            $row['seconds_since_seen'] = $secondsAgo;
            $row['is_night_login'] = $this->isNightLogin((string) ($row['login_at'] ?? ''));
        }
        unset($row);

        $activityStatus = strtolower(trim((string) ($filters['activity_status'] ?? '')));
        if (in_array($activityStatus, ['online', 'idle', 'offline'], true)) {
            $rows = array_values(array_filter($rows, static function ($row) use ($activityStatus) {
                return strtolower((string) ($row['activity_status'] ?? '')) === $activityStatus;
            }));
        }

        return $rows;
    }

    public function getSummary(array $filters = [])
    {
        $rows = $this->getRows($filters);
        $summary = [
            'total' => count($rows),
            'online' => 0,
            'idle' => 0,
            'offline' => 0,
            'night' => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['activity_status'] ?? 'offline');
            if (isset($summary[$status])) {
                $summary[$status]++;
            } else {
                $summary['offline']++;
            }

            if (!empty($row['is_night_login'])) {
                $summary['night']++;
            }
        }

        return $summary;
    }

    public function getDefaultFilters()
    {
        return [
            'date_from' => date('Y-m-d', strtotime('-6 days')),
            'date_to' => date('Y-m-d'),
            'keyword' => '',
            'activity_status' => '',
        ];
    }

    public function getRetentionDays()
    {
        return $this->retentionDays;
    }

    public function getOnlineMinutes()
    {
        return (int) floor($this->onlineSeconds / 60);
    }

    public function getIdleMinutes()
    {
        return (int) floor($this->idleSeconds / 60);
    }

    public function getSeenThrottleSeconds()
    {
        return $this->seenThrottleSeconds;
    }

    private function baseFilteredQuery(array $filters)
    {
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        $keyword = trim((string) ($filters['keyword'] ?? ''));

        $query = $this->db
            ->from($this->table . ' h')
            ->join('tb_master_user_new u', 'u.id = h.user_id', 'left')
            ->join('tb_level l', 'u.id_level = l.id_level', 'left')
            ->where('h.platform', 'web');

        if ($dateFrom !== '') {
            $query->where('h.login_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo !== '') {
            $query->where('h.login_at <=', $dateTo . ' 23:59:59');
        }
        if ($keyword !== '') {
            $query->group_start()
                ->like('h.nik', $keyword)
                ->or_like('h.username_user', $keyword)
                ->or_like('h.nama_user', $keyword)
                ->or_like('u.nik', $keyword)
                ->or_like('u.nama_karyawan', $keyword)
                ->or_like('u.username_user', $keyword)
                ->or_like('h.ip_address', $keyword)
                ->group_end();
        }

        return $query;
    }

    private function cleanupOldRows()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $this->retentionDays . ' days'));
        $this->db->where('login_at <', $cutoff)->delete($this->table);
    }

    private function resolveActivityStatus($secondsAgo, $logoutAt)
    {
        if ($logoutAt !== '') {
            return 'offline';
        }
        if ($secondsAgo === null) {
            return 'offline';
        }
        if ($secondsAgo <= $this->onlineSeconds) {
            return 'online';
        }
        if ($secondsAgo <= $this->idleSeconds) {
            return 'idle';
        }
        return 'offline';
    }

    private function isNightLogin($loginAt)
    {
        $time = strtotime($loginAt);
        if ($time === false) {
            return false;
        }

        $hour = (int) date('G', $time);
        return $hour >= 0 && $hour < 5;
    }

    private function getCurrentSessionId()
    {
        $cookieName = (string) config_item('sess_cookie_name');
        if ($cookieName !== '') {
            $cookieValue = (string) $this->input->cookie($cookieName, true);
            if ($cookieValue !== '') {
                return $cookieValue;
            }
        }

        return substr(hash('sha256', uniqid('', true) . (string) mt_rand()), 0, 64);
    }
}
