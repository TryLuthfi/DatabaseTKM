<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MAuth extends CI_Model
{
    private $_table = 'tb_master_user_new';
    // public $username_acc;
    // public $password_acc;

    public function login()
    {
        $_POST = $this->input->post();

        $username = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['pass'] ?? '');

        $akun = $this->db->query("select
                                    a.*,
                                    tl.*,
                                    COALESCE(a.nama_karyawan, '') as nama_user,
                                    COALESCE(a.lokasi_kantor, a.homebase, 'HO') as lokasi_user,
                                    '' as nama_jabatan,
                                    '' as under_sm,
                                    '' as under_pm
                                from
                                    tb_master_user_new a
                                left join tb_level tl  ON a.id_level = tl.id_level
                                WHERE a.username_user = " . $this->db->escape($username) . "
                        ")->row_array();
        if ($akun) {
            if($akun['status_user'] !== 'ACTIVE'){
                $this->session->set_flashdata('error_log', 'tidak_ada');
                redirect('Auth');
            } else if ($akun['password_user'] == $pass) {
                $validationRows = $this->db
                    ->select('validation_user')
                    ->from('tb_master_user_child')
                    ->where('id_master_user', (int) $akun['id'])
                    ->get()
                    ->result_array();

                $validationList = [];

                if (!empty($akun['validation_user'])) {
                    $validationList[] = trim((string) $akun['validation_user']);
                }

                foreach ($validationRows as $row) {
                    $value = trim((string) ($row['validation_user'] ?? ''));
                    if ($value !== '') {
                        $validationList[] = $value;
                    }
                }

                $validationList = array_values(array_unique(array_filter($validationList)));

                $data =
                    [
                        'id_user' => $akun['id'],
                        'id_level' => isset($akun['id_level']) ? (int) $akun['id_level'] : 0,
                        'nama_user' => $akun['nama_user'],
                        'username_user' => $akun['username_user'],
                        'password_user' => $akun['password_user'],
                        'lokasi_user' => $akun['lokasi_user'],
                        'nama_level' => $akun['nama_level'],
                        'validation' => !empty($validationList) ? implode(', ', $validationList) : 'non',
                        'validation_user' => $validationList,
                        'nama_jabatan' => $akun['nama_jabatan'] ?? ''
                    ];
                $this->session->set_userdata($data);
                $this->setMaintenanceBypassCookie($akun);
                
                redirect('Dashboard');
            } else {
                $this->session->set_flashdata('error_log', 'salah');
                redirect('Auth');
            }
        } else {
            $this->session->set_flashdata('error_log', 'tidak_ada');
            redirect('Auth');
        }
        
    }

    private function setMaintenanceBypassCookie(array $akun)
    {
        $maintenanceRaw = strtolower(trim((string) $this->readEnvValue('MAINTENANCE_MODE', 'false')));
        $isMaintenanceOn = in_array($maintenanceRaw, array('1', 'true', 'on', 'yes'), true);
        if (!$isMaintenanceOn) {
            return;
        }

        $allowedRole = trim((string) $this->readEnvValue('MAINTENANCE_ALLOWED_ROLE', 'Super Admin'));
        $allowedLevelRaw = trim((string) $this->readEnvValue('MAINTENANCE_ALLOWED_LEVEL_ID', '1'));
        $allowedLevel = ctype_digit($allowedLevelRaw) ? (int) $allowedLevelRaw : 1;

        $userLevelName = trim((string) ($akun['nama_level'] ?? ''));
        $userLevelId = (int) ($akun['id_level'] ?? 0);

        $isAllowedRole = ($allowedRole !== '' && strcasecmp($userLevelName, $allowedRole) === 0);
        $isAllowedLevel = ($allowedLevel > 0 && $userLevelId === $allowedLevel);
        if (!$isAllowedRole && !$isAllowedLevel) {
            return;
        }

        $userId = (int) ($akun['id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $exp = time() + 7200;
        $secret = (string) $this->readEnvValue('MAINTENANCE_BYPASS_SECRET', config_item('encryption_key'));
        if ($secret === '') {
            $secret = 'database_tkm_maintenance_secret';
        }

        $payload = $userId.'|'.$exp;
        $sig = hash_hmac('sha256', $payload, $secret);
        $value = $payload.'|'.$sig;
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        setcookie('mtk_maintenance_bypass', $value, $exp, '/', '', $secure, true);
    }

    private function readEnvValue($key, $default = '')
    {
        if ($key === '') {
            return $default;
        }

        if (isset($_ENV[$key]) && is_scalar($_ENV[$key])) {
            return (string) $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && is_scalar($_SERVER[$key])) {
            return (string) $_SERVER[$key];
        }

        $envGet = getenv($key);
        if ($envGet !== false && is_scalar($envGet)) {
            return (string) $envGet;
        }

        $envFile = FCPATH.'.env';
        if (!is_file($envFile)) {
            return $default;
        }

        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $default;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            if (trim($parts[0]) !== $key) {
                continue;
            }

            $envVal = trim($parts[1]);
            $first = substr($envVal, 0, 1);
            $last = substr($envVal, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $envVal = substr($envVal, 1, -1);
            }
            return $envVal;
        }

        return $default;
    }
}


