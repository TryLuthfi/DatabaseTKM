<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Akselerasi_2026 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        enforce_database_all_po_access();
        if (!has_user_page_access('Database All PO', 'PO_Monitor', 'VIEW', (int) $this->session->userdata('id_user'))) {
            render_no_access('Anda tidak memiliki akses ke halaman ini.');
            exit;
        }
        $this->load->model('MAkselerasi_2026');
    }

    public function index()
    {
        $today = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
        $endDate = $this->input->get('end_date');
        if ($endDate === null || $endDate === '') {
            $endDate = max(MAkselerasi_2026::START_DATE, $today);
        }
        $data = ['title' => 'TARGET INVOICE AKSELERASI 2026', 'error' => '',
            'dashboard' => ['rows' => [], 'unmapped' => [], 'from' => MAkselerasi_2026::START_DATE,
                'to' => is_string($endDate) ? $endDate : '', 'source_total' => 0]];
        if (!MAkselerasi_2026::validEndDate($endDate)) {
            $data['error'] = 'Tanggal akhir harus tanggal valid pada atau setelah 16 November 2026.';
            $this->output->set_status_header(422);
        } else {
            $previousDebug = $this->db->db_debug;
            $this->db->db_debug = false;
            try {
                $data['dashboard'] = $this->MAkselerasi_2026->loadDashboard($endDate);
            } catch (Exception $e) {
                $data['error'] = 'Data Achievement belum dapat dimuat. Silakan coba kembali.';
                $this->output->set_status_header(503);
                log_message('error', 'Akselerasi 2026: gagal memuat proyeksi claim.');
            } finally {
                $this->db->db_debug = $previousDebug;
            }
        }
        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Akselerasi_2026/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }
}
