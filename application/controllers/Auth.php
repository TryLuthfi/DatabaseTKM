<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('MAuth');
    }

    public function index()
    {
        if ($this->session->userdata('kode') != null) {
            redirect('Dashboard');
        } else {
            $this->form_validation->set_rules('username', 'Username', 'required|trim');
            $this->form_validation->set_rules('pass', 'Password', 'required|trim');

            if ($this->form_validation->run() == false) {
                $this->load->view('Auth/Login');
            } else {
                $this->MAuth->login();
                //asdasdasd
            }
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        setcookie('mtk_maintenance_bypass', '', time() - 3600, '/');
        redirect('Auth');
    }

    public function firstLoginEmail()
    {
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0) {
            redirect('Auth');
        }

        if ((int) $this->session->userdata('first_login_required') !== 1) {
            redirect('Dashboard');
        }

        $user = $this->db
            ->select('id, nama_karyawan, email_kantor')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->get()
            ->row_array();

        if (!$user) {
            $this->session->set_flashdata('error_log', 'tidak_ada');
            redirect('Auth/logout');
        }

        $data = [
            'nama_karyawan' => (string) ($user['nama_karyawan'] ?? ''),
            'email_kantor' => strtolower(trim((string) ($user['email_kantor'] ?? ''))),
            'reset_link_success' => (string) $this->session->userdata('reset_link_success_once'),
            'reset_link_error' => (string) $this->session->userdata('reset_link_error_once')
        ];

        $this->session->unset_userdata('reset_link_success_once');
        $this->session->unset_userdata('reset_link_error_once');

        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Cache-Control: post-check=0, pre-check=0', false)
            ->set_header('Pragma: no-cache');

        $this->load->view('Auth/FirstLoginEmail', $data);
    }

    public function sendFirstLoginLink()
    {
        $userId = (int) $this->session->userdata('id_user');
        if ($userId <= 0 || (int) $this->session->userdata('first_login_required') !== 1) {
            show_error('Akses tidak valid.', 403);
            return;
        }

        $inputEmail = strtolower(trim((string) $this->input->post('email_kantor')));
        $user = $this->db
            ->select('id, nama_karyawan, email_kantor')
            ->from('tb_master_user_new')
            ->where('id', $userId)
            ->get()
            ->row_array();

        if (!$user) {
            show_error('User tidak ditemukan.', 404);
            return;
        }

        $dbEmail = strtolower(trim((string) ($user['email_kantor'] ?? '')));
        if ($dbEmail === '' || $inputEmail !== $dbEmail) {
            $this->session->set_userdata('reset_link_error_once', 'Email kantor tidak sesuai dengan data akun.');
            redirect('Auth/firstLoginEmail');
            return;
        }

        if (!$this->db->table_exists('tb_user_password_reset')) {
            $this->session->set_userdata('reset_link_error_once', 'Tabel reset password belum tersedia. Jalankan patch SQL terlebih dahulu.');
            redirect('Auth/firstLoginEmail');
            return;
        }
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 1800);

        $this->db->where('user_id', $userId)->update('tb_user_password_reset', [
            'used_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->insert('tb_user_password_reset', [
            'user_id' => $userId,
            'email' => $dbEmail,
            'token' => $token,
            'expires_at' => $expiresAt,
            'used_at' => null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $resetLink = site_url('Auth/resetPassword?token=' . urlencode($token));
        $subject = 'Change Your Password Account - Database Project TKM';
        $logoUrl = base_url('assets/dist/img/logotkmsolid.png');

        $messageHtml = ''
            . '<div style="font-family:Arial,Helvetica,sans-serif;color:#111827;line-height:1.6;">'
            . '<p>Halo <strong>' . htmlspecialchars((string) ($user['nama_karyawan'] ?? ''), ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
            . '<p>Klik link berikut untuk ganti password akun Anda:</p>'
            . '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>'
            . '<p>Link berlaku sampai: <strong>' . htmlspecialchars($expiresAt, ENT_QUOTES, 'UTF-8') . ' WIB</strong>.</p>'
            . '<p>Jika Anda tidak meminta ini, abaikan email ini.</p>'
            . '<br>'
            . '<p style="margin:0;">Thanks &amp; Regards</p>'
            . '<p style="margin:0;"><strong><em>Try Luthfi Sasmito</em></strong></p>'
            . '<p style="margin:10px 0 8px 0;">'
            . '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Logo TKM" style="max-width:220px;height:auto;">'
            . '</p>'
            . '<p style="margin:0;"><strong>PT. TECHNOLOGY KARYA MANDIRI</strong></p>'
            . '<p style="margin:0;">Rukan Puri Botanical Blok H.9 / No.22-23</p>'
            . '<p style="margin:0;">Jl. Raya Meruya Selatan - Joglo</p>'
            . '<p style="margin:0;">Jakarta 11640</p>'
            . '<p style="margin:0;"><strong>Telp</strong>: 021 - 5855552 (Hunting) / <strong>Fax</strong>: 021 - 5852896</p>'
            . '<p style="margin:0;"><strong>Mobile</strong>: +62 8953 3675 0905</p>'
            . '<p style="margin:0;"><a href="mailto:lutfi@tkm.co.id" style="color:#2563eb;">Email : lutfi@tkm.co.id</a></p>'
            . '<p style="margin:0;"><a href="https://www.tkm.co.id" style="color:#2563eb;">www.tkm.co.id</a></p>'
            . '</div>';

        $sent = $this->sendEmail($dbEmail, $subject, $messageHtml);
        if (!$sent) {
            $this->session->set_userdata('reset_link_error_once', 'Gagal mengirim email. Periksa konfigurasi email server.');
            redirect('Auth/firstLoginEmail');
            return;
        }

        $this->session->set_userdata('reset_link_success_once', 'Link ganti password sudah dikirim ke email kantor Anda.');
        redirect('Auth/firstLoginEmail');
    }

    public function resetPassword()
    {
        $token = trim((string) $this->input->get('token'));
        if ($token === '') {
            show_404();
            return;
        }

        if (!$this->db->table_exists('tb_user_password_reset')) {
            show_error('Tabel reset password belum tersedia. Jalankan patch SQL terlebih dahulu.', 500);
            return;
        }
        $row = $this->db
            ->select('*')
            ->from('tb_user_password_reset')
            ->where('token', $token)
            ->where('used_at IS NULL', null, false)
            ->get()
            ->row_array();

        if (!$row) {
            show_404();
            return;
        }

        if (strtotime((string) $row['expires_at']) < time()) {
            show_error('Link sudah kedaluwarsa.', 400);
            return;
        }

        if (strtoupper($this->input->method()) === 'POST') {
            $newPassword = (string) $this->input->post('new_password');
            $confirmPassword = (string) $this->input->post('confirm_password');

            if (strlen($newPassword) < 8) {
                $this->session->set_flashdata('reset_password_error', 'Password minimal 8 karakter.');
                redirect('Auth/resetPassword?token=' . urlencode($token));
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->session->set_flashdata('reset_password_error', 'Konfirmasi password tidak sama.');
                redirect('Auth/resetPassword?token=' . urlencode($token));
                return;
            }

            $this->db->where('id', (int) $row['user_id'])->update('tb_master_user_new', [
                'password_user' => $newPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->where('id', (int) $row['id'])->update('tb_user_password_reset', [
                'used_at' => date('Y-m-d H:i:s')
            ]);

            if ((int) $this->session->userdata('id_user') === (int) $row['user_id']) {
                $this->session->set_userdata('password_user', $newPassword);
                $this->session->unset_userdata('first_login_required');
            }

            $this->session->set_flashdata('reset_password_success', 'Password berhasil diubah. Silakan login kembali.');
            redirect('Auth/logout');
            return;
        }

        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Cache-Control: post-check=0, pre-check=0', false)
            ->set_header('Pragma: no-cache');

        $this->load->view('Auth/ResetPassword', ['token' => $token]);
    }

    private function sendEmail($to, $subject, $messageHtml)
    {
        $this->load->library('email');
        $smtpHost = $this->readEnvValue('SMTP_HOST', '');
        $smtpPort = (int) $this->readEnvValue('SMTP_PORT', '587');
        $smtpUser = $this->readEnvValue('SMTP_USER', '');
        $smtpPass = $this->readEnvValue('SMTP_PASS', '');
        $smtpCrypto = strtolower(trim($this->readEnvValue('SMTP_CRYPTO', 'tls')));
        $fromEmail = $this->readEnvValue('SMTP_FROM_EMAIL', $smtpUser !== '' ? $smtpUser : 'no-reply@tkm.co.id');
        $fromName = $this->readEnvValue('SMTP_FROM_NAME', 'Database Project TKM');

        if ($smtpHost !== '' && $smtpUser !== '') {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => $smtpHost,
                'smtp_port' => $smtpPort > 0 ? $smtpPort : 587,
                'smtp_user' => $smtpUser,
                'smtp_pass' => $smtpPass,
                'smtp_crypto' => in_array($smtpCrypto, ['tls', 'ssl'], true) ? $smtpCrypto : 'tls',
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'crlf' => "\r\n",
                'smtp_timeout' => 15
            ];
            $this->email->initialize($config);
        }

        $this->email->clear(true);
        $this->email->from($fromEmail, $fromName);
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($messageHtml);
        $this->email->set_mailtype('html');
        $sent = (bool) $this->email->send();
        if (!$sent) {
            log_message('error', 'Auth sendEmail failed: ' . strip_tags((string) $this->email->print_debugger(['headers'])));
        }
        return $sent;
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

        $envFile = FCPATH . '.env';
        if (!is_file($envFile)) {
            return $default;
        }

        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $default;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
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
