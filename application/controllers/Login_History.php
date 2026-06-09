<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login_History extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty($this->session->userdata('id_user'))) {
            redirect('Auth');
            return;
        }

        if ((string) $this->session->userdata('nama_level') !== 'Super Admin') {
            show_error('Akses hanya untuk Super Admin.', 403);
            return;
        }

        $this->load->model('MLogin_History');
    }

    public function index()
    {
        $filters = $this->MLogin_History->getDefaultFilters();
        foreach (['date_from', 'date_to', 'keyword', 'activity_status'] as $key) {
            $value = trim((string) $this->input->get($key));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }

        $filters['date_from'] = $this->normalizeDate($filters['date_from']);
        $filters['date_to'] = $this->normalizeDate($filters['date_to']);
        $filters['activity_status'] = strtolower(trim((string) $filters['activity_status']));
        if (!in_array($filters['activity_status'], ['', 'online', 'idle', 'offline'], true)) {
            $filters['activity_status'] = '';
        }

        $data = [
            'title' => 'Login History',
            'judul' => 'Login History',
            'filters' => $filters,
            'summary' => $this->MLogin_History->getSummary($filters),
            'rows' => $this->MLogin_History->getRows($filters),
            'retentionDays' => $this->MLogin_History->getRetentionDays(),
            'onlineMinutes' => $this->MLogin_History->getOnlineMinutes(),
            'idleMinutes' => $this->MLogin_History->getIdleMinutes(),
        ];

        $this->load->view('Templates/01_Header', $data);
        $this->load->view('Templates/02_Menu');
        $this->load->view('Login_History/index', $data);
        $this->load->view('Templates/03_Footer');
        $this->load->view('Templates/99_JS');
    }

    private function normalizeDate($date)
    {
        $date = trim((string) $date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        $time = strtotime($date);
        if ($time === false) {
            return '';
        }

        return date('Y-m-d', $time);
    }
}
