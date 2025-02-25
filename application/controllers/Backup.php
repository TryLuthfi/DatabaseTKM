<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Backup extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->dbutil();
        $this->load->helper(array('file', 'download', 'url'));
    }

    public function index()
    {
        // Ambil data backup dari database
        $this->db->order_by('backup_date', 'DESC');
        $data['backups'] = $this->db->get('backup_history')->result();
        $data['title'] = 'Backup Database MySql';
        $data['judul'] = 'Backup Database MySql';

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
        $db_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
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
        redirect('backup');
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
}