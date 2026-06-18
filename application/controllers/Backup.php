<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Backup extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->dbutil();
        $this->load->helper(array('file', 'download', 'url'));
    }

    public function index()
    {
        // Ambil data backup dari database
        $this->db->order_by('backup_date', 'DESC');
        $data['backups'] = $this->db->get('backup_history')->result();
        $data['title'] = 'BACKUP DATABASE MYSQL';
        $data['judul'] = 'BACKUP DATABASE MYSQL';
        $data['canImportVps'] = $this->canRunVpsImport();
        $data['vpsImportAvailable'] = $this->isVpsImportAvailable();

        // Load view
        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('BackupDB/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    public function create_backup()
    {
        // Nama file backup
        $db_name = 'databasetkm_' . date('Y-m-d_H-i-s') . '.sql';
        $save_path = FCPATH . 'backups/' . $db_name;

        // Konfigurasi backup
        $prefs = array(
            'format' => 'txt',
            'filename' => $db_name,
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n"
        );

        // Generate backup
        $backup = $this->dbutil->backup($prefs);

        // Simpan file ke folder
        if (!is_dir(FCPATH . 'backups')) {
            mkdir(FCPATH . 'backups', 0777, true);
        }
        write_file($save_path, $backup);

        // Simpan ke database
        $this->db->insert('backup_history', ['filename' => $db_name]);

        // Redirect kembali ke halaman backup
        redirect('Backup');
    }

    public function download_backup($filename)
    {
        $filepath = FCPATH . 'backups/' . $filename;

        if (file_exists($filepath)) {
            force_download($filename, file_get_contents($filepath));
        } else {
            show_404();
        }
    }

    public function delete_backup($id)
    {
        $this->db->delete('backup_history', ['id' => $id]);
        redirect('Backup');
    }

    public function import_vps_to_local()
    {
        if (!$this->canRunVpsImport()) {
            $this->session->set_flashdata('error', 'Import VPS hanya bisa dijalankan di lokal oleh Super Admin.');
            redirect('Backup');
            return;
        }

        if (!$this->isVpsImportAvailable()) {
            $this->session->set_flashdata('error', 'Script import VPS atau SSH key belum tersedia di lokal.');
            redirect('Backup');
            return;
        }

        if (strtoupper((string) $this->input->method(true)) !== 'POST') {
            redirect('Backup');
            return;
        }

        @set_time_limit(0);

        $repoRoot = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $scriptPath = $repoRoot . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'import_db_vps.ps1';
        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($scriptPath);

        $startedAt = microtime(true);
        [$exitCode, $output] = $this->runShellCommand($command, $repoRoot);
        $duration = max(1, (int) round(microtime(true) - $startedAt));

        $uploadExitCode = 0;
        $uploadOutput = [];
        if ($exitCode === 0) {
            [$uploadExitCode, $uploadOutput] = $this->syncRfsEvidenceUploads($repoRoot);
        }

        $message = $exitCode === 0
            ? 'Import database VPS ke lokal selesai dalam ' . $duration . ' detik.'
            : 'Import database VPS gagal. Cek output proses di bawah.';

        if ($exitCode === 0 && $uploadExitCode !== 0) {
            $message .= ' Database sudah terimport, tapi sinkron evidence RFS gagal.';
        }

        $this->session->set_flashdata($exitCode === 0 ? 'success' : 'error', $message);
        $this->session->set_flashdata('import_vps_output', $this->summarizeImportOutput(array_merge(
            $output,
            $uploadOutput ? array_merge(['', '[Evidence RFS]'], $uploadOutput) : []
        )));

        redirect('Backup');
    }

    private function canRunVpsImport()
    {
        if (empty($this->session->userdata('id_user'))) {
            return false;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            return false;
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        $host = strtolower((string) ($this->input->server('HTTP_HOST') ?: ''));
        $host = preg_replace('/:\d+$/', '', $host);
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $path = strtolower(str_replace('/', '\\', FCPATH));

        return in_array($host, $localHosts, true)
            || strpos($path, 'c:\\xampp\\htdocs\\databasetkm') === 0;
    }

    private function isVpsImportAvailable()
    {
        $repoRoot = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $requiredFiles = [
            $repoRoot . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'import_db_vps.ps1',
            $repoRoot . DIRECTORY_SEPARATOR . 'tmp_codex_vps_ed25519',
            $repoRoot . DIRECTORY_SEPARATOR . 'tmp_vps_known_hosts',
        ];

        foreach ($requiredFiles as $filePath) {
            if (!is_file($filePath)) {
                return false;
            }
        }

        return true;
    }

    private function runShellCommand($command, $cwd)
    {
        if (!function_exists('proc_open')) {
            return [1, ['Fungsi proc_open tidak tersedia di PHP ini.']];
        }

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $cwd);
        if (!is_resource($process)) {
            return [1, ['Gagal menjalankan proses command.']];
        }

        $output = [];
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $status = ['exitcode' => 1];
        while (true) {
            foreach ([1, 2] as $pipeIndex) {
                while (($line = fgets($pipes[$pipeIndex])) !== false) {
                    $output[] = rtrim($line);
                }
            }

            $status = proc_get_status($process);
            if (empty($status['running'])) {
                break;
            }

            usleep(100000);
        }

        foreach ([1, 2] as $pipeIndex) {
            while (($line = fgets($pipes[$pipeIndex])) !== false) {
                $output[] = rtrim($line);
            }
            fclose($pipes[$pipeIndex]);
        }

        $closeCode = proc_close($process);
        $exitCode = isset($status['exitcode']) && (int) $status['exitcode'] >= 0
            ? (int) $status['exitcode']
            : (int) $closeCode;
        return [$exitCode, $output];
    }

    private function syncRfsEvidenceUploads($repoRoot)
    {
        $keyPath = $repoRoot . DIRECTORY_SEPARATOR . 'tmp_codex_vps_ed25519';
        $knownHostsPath = $repoRoot . DIRECTORY_SEPARATOR . 'tmp_vps_known_hosts';
        $destinationPath = $repoRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'monitoring_rfs_myrep';

        if (!is_dir($destinationPath)) {
            @mkdir($destinationPath, 0777, true);
        }

        $command = 'scp -i ' . escapeshellarg($keyPath)
            . ' -o ' . escapeshellarg('UserKnownHostsFile=' . $knownHostsPath)
            . ' -o ' . escapeshellarg('StrictHostKeyChecking=yes')
            . ' -o ' . escapeshellarg('BatchMode=yes')
            . ' -r '
            . escapeshellarg('root@212.85.27.154:/www/wwwroot/databasetkm.com/uploads/monitoring_rfs_myrep/*')
            . ' '
            . escapeshellarg($destinationPath);

        return $this->runShellCommand($command, $repoRoot);
    }

    private function summarizeImportOutput(array $output)
    {
        $output = array_values(array_filter(array_map(static function ($line) {
            return trim((string) $line);
        }, $output), static function ($line) {
            return $line !== '';
        }));

        if (count($output) > 80) {
            $output = array_merge(
                array_slice($output, 0, 30),
                ['... output dipotong ...'],
                array_slice($output, -30)
            );
        }

        return implode("\n", $output);
    }
}
